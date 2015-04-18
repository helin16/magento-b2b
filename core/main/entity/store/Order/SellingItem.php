<?php
/**
 * Entity for SellingItem
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class SellingItem extends BaseEntityAbstract
{
	/**
	 * The product
	 *
	 * @var Product
	 */
	protected $product = null;
	/**
	 * The order
	 *
	 * @var Order
	 */
	protected $order = null;
	/**
	 * The orderItem
	 *
	 * @var OrderItem
	 */
	protected $orderItem = null;
	/**
	 * The serial number
	 * 
	 * @var string
	 */
	private $serialNo;
	/**
	 * The description
	 * 
	 * @var string
	 */
	private $description;
	/**
	 * Getter for product
	 *
	 * @return Product
	 */
	public function getProduct()
	{
		$this->loadManyToOne('product');
		return $this->product;
	}
	/**
	 * Setter for product
	 *
	 * @param Product $value The product
	 *
	 * @return SellingItem
	 */
	public function setProduct(Product $value)
	{
		$this->product = $value;
		return $this;
	}
	/**
	 * Getter for order
	 *
	 * @return Order
	 */
	public function getOrder()
	{
		$this->loadManyToOne('order');
		return $this->order;
	}
	/**
	 * Setter for order
	 *
	 * @param Order $value The product
	 *
	 * @return SellingItem
	 */
	public function setOrder(Order $value)
	{
		$this->order = $value;
		return $this;
	}
	/**
	 * Getter for order
	 *
	 * @return OrderItem
	 */
	public function getOrderItem()
	{
		$this->loadManyToOne('orderItem');
		return $this->orderItem;
	}
	/**
	 * Setter for order
	 *
	 * @param Order $value The product
	 *
	 * @return SellingItem
	 */
	public function setOrderItem(OrderItem $value)
	{
		$this->orderItem = $value;
		return $this;
	}
	/**
	 * Getter for serialNo
	 *
	 * @return string
	 */
	public function getSerialNo() 
	{
	    return $this->serialNo;
	}
	/**
	 * Setter for serialNo
	 *
	 * @param string $value The serialNo
	 *
	 * @return SellingItem
	 */
	public function setSerialNo($value) 
	{
	    $this->serialNo = $value;
	    return $this;
	}
	/**
	 * Getter for description
	 *
	 * @return string
	 */
	public function getDescription() 
	{
	    return $this->description;
	}
	/**
	 * Setter for serialNo
	 *
	 * @param string $value The serialNo
	 *
	 * @return SellingItem
	 */
	public function setDescription($value) 
	{
	    $this->description = $value;
	    return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::preSave()
	 */
	public function preSave()
	{
		if($this->getOrderItem() instanceof OrderItem) {
			$this->setProduct($this->getOrderItem()->getProduct())
				->setOrder($this->getOrderItem()->getOrder());
		}
	}
	/**
	 * (non-PHPdoc)
	 * @see HydraEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'sell_item');
	
		DaoMap::setManyToOne('orderItem', 'OrderItem', 'rec_item_oi');
		DaoMap::setManyToOne('order', 'Order', 'sell_item_or');
		DaoMap::setManyToOne('product', 'Product', 'rec_item_pro');
		DaoMap::setStringType('serialNo', 'varchar', '100');
		DaoMap::setStringType('description', 'varchar', '255');
		
		parent::__loadDaoMap();
		DaoMap::createIndex('serialNo');
		DaoMap::createIndex('description');
		DaoMap::commit();
	}
	/**
	 * creating a orderitem
	 * 
	 * @param OrderItem $orderItem
	 * @param string    $serialNo
	 * @param string    $description
	 * 
	 * @return Ambigous <SellingItem, SellingItem>
	 */
	public static function create(OrderItem $orderItem, $serialNo, $description = '')
	{
		$sellingItem = new SellingItem();
		return $sellingItem->setOrderItem($orderItem)
			->setSerialNo(trim($serialNo))
			->setDescription(trim($description))
			->save();
	}
	/**
	 * Getting the selling item
	 * 
	 * @param OrderItem $orderItem
	 * @param string    $serialNo
	 * @param string    $description
	 * @param Order     $order
	 * @param Product   $product
	 * @param string    $activeOnly
	 * @param string    $pageNo
	 * @param unknown   $pageSize
	 * @param unknown   $orderBy
	 * @param unknown   $stats
	 * @return Ambigous <Ambigous, multitype:, multitype:BaseEntityAbstract >
	 */
	public static function getSellingItems(OrderItem $orderItem = null, $serialNo = '', $description = '', Order $order = null, Product $product = null, $activeOnly = true, $pageNo = null, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE, $orderBy = array(), &$stats = array())
	{
		$where = $params = array();
		if(trim($serialNo) !== '') {
			$where[] = 'serialNo like ?';
			$params[] = $serialNo;
		}
		if(trim($description) !== '') {
			$where[] = 'description like ?';
			$params[] = '%' . $description . '%';
		}
		if($orderItem instanceof OrderItem) {
			$where[] = 'orderItemId = ?';
			$params[] = $orderItem->getId();
		}
		if($order instanceof Order) {
			$where[] = 'orderId = ?';
			$params[] = $order->getId();
		}
		if($product instanceof Product) {
			$where[] = 'productId = ?';
			$params[] = $product->getId();
		}
		if(count($where) === 0)
			return SellingItem::getAll($activeOnly, $pageNo, $pageSize, $orderBy, $stats);
		return $results =  SellingItem::getAllByCriteria(implode(' AND ', $where), $params, $activeOnly, $pageNo, $pageSize, $orderBy, $stats);
	}
}