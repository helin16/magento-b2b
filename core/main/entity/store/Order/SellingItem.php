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
	 * The kit that we sold
	 *
	 * @var Kit
	 */
	protected $kit = null;
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
	 * Getter for kit
	 *
	 * @return Kit
	 */
	public function getKit()
	{
		$this->loadManyToOne('kit');
	    return $this->kit;
	}
	/**
	 * Setter for kit
	 *
	 * @param Kit $value The kit
	 *
	 * @return SellingItem
	 */
	public function setKit(Kit $value = null)
	{
	    $this->kit = $value;
	    return $this;
	}
	/**
	 * clear the kit
	 *
	 * @param string $kit The Kit that cleared
	 *
	 * @return SellingItem
	 */
	private function _clearKit(&$kit = null)
	{
		if(!$this->getKit() instanceof Kit)
			return $this;
		if($this->getKit()->getShippment() instanceof Shippment)
			throw new EntityException('You can NOT clear/change this KIT[' . $this->getKit()->getBarcode() . '], as it has been shipped by:' . $this->getKit()->getShippment()->getCourier()->getName() . ( $this->getKit()->getSoldOnOrder() instanceof Order ? ' On order(' . $this->getKit()->getSoldOnOrder()->getOrderNo() . ')' : ''));
		$kit = $this->getKit()->setSoldDate(UDate::zeroDate())
			->setSoldToCustomer(null)
			->setSoldOnOrder(null)
			->setShippment(null)
			->save();
		return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::preSave()
	 */
	public function preSave()
	{
		if(trim($this->getSerialNo()) === '')
			throw new Exception('You can NOT save a selling Item without a serial number.');
		if(trim($this->getId()) !== '') {
			if(intval($this->getActive()) === 0 && self::countByCriteria('id = ? and active != ?', array($this->getId(), $this->getActive())) > 0) { //trying to deactivated
				$this->_clearKit();
			} else if(intval($this->getActive()) === 1 && self::countByCriteria('id = ? and serialNo != ?', array($this->getId(), trim($this->getSerialNo()))) > 0) { //trying to changed serialno
				$this->_clearKit()->setKit(null);
			}
		}
		if($this->getOrderItem() instanceof OrderItem) {
			$this->setProduct($this->getOrderItem()->getProduct())
				->setOrder($this->getOrderItem()->getOrder());
		}
		if(!$this->getKit() instanceof Kit && strpos(trim($this->getSerialNo()), Kit::BARCODE_PREFIX) === 0) {
			if(($kit = Kit::getByBarcode(trim($this->getSerialNo()))) instanceof Kit)
				$this->setKit($kit);
		}
		if($this->getKit() instanceof Kit) {
			$kitProduct = $this->getKit()->getProduct();
			$orderItemProduct = ($this->getOrderItem() instanceof OrderItem ? $this->getOrderItem()->getProduct() : null);
			if (
					(!$kitProduct instanceof Product && $orderItemProduct instanceof Product )
					|| ($kitProduct instanceof Product && !$orderItemProduct instanceof Product )
					|| ($kitProduct instanceof Product && $orderItemProduct instanceof Product && $kitProduct->getId() !== $orderItemProduct->getId())
			)
				throw new Exception('The Kit [' . $this->getKit()->getBarcode() . ', SKU: ' . ($kitProduct instanceof Product ? $kitProduct->getSku() : '') . '] is not the same product on this OrderItem[SKU:' . ($orderItemProduct instanceof Product ? $orderItemProduct->getSku() : '') . '].');
		}
		if($this->getProduct() instanceof Product && intval($this->getProduct()->getIsKit()) === 1 ) {
			if(!$this->getKit() instanceof Kit)
				throw new Exception('The Product(SKU: ' . $this->getProduct()->getSku() . ') is a KIT, but no valid Kit barcode provided(Provided: ' . $this->getSerialNo() . ').');
			if($this->getOrderItem()->getOrder() instanceof Order) {
				$where = array('kitId = :kitId and orderId = :orderId');
				$params = array('kitId' => $this->getKit()->getId(), 'orderId' => $this->getOrderItem()->getOrder()->getId());
				if(($id = trim($this->getId())) !== '') {
					$where[] = 'id != :id';
					$params['id'] = $id;
				}
				if(self::countByCriteria(implode(' AND ', $where), $params) > 0)
					throw new Exception('The KIT[' .$this->getKit()->getBarcode() . '] has been scanned onto this Order(' . $this->getOrderItem()->getOrder()->getOrderNo() . ') already!');
			}
		}
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::postSave()
	 */
	public function postSave()
	{
		if($this->getOrderItem() instanceof OrderItem) {
			if(count($serialItems = self::getAllByCriteria('orderItemId = ?', array($this->getOrderItem()->getId()))) > 0) {
				$totalUnitCostForOrderItem = 0;
				foreach($serialItems as $serialItem) {
					if(($kit = $serialItem->getKit()) instanceof Kit) {
						$totalUnitCostForOrderItem  = $totalUnitCostForOrderItem + $kit->getCost();
						if(!$kit->getSoldOnOrder() instanceof Order)
							$kit->setSoldOnOrder($this->getOrderItem()->getOrder());
						if(!$kit->getSoldToCustomer() instanceof Customer)
							$kit->setSoldToCustomer($this->getOrderItem()->getOrder()->getCustomer());
						if(trim($kit->getSoldDate()) === trim(UDate::zeroDate()))
							$kit->setSoldDate(new UDate());
						if(!$kit->getShippment() instanceof Shippment && count($shippments = $this->getOrderItem()->getOrder()->getShippments()) > 0)
							$kit->setShippment($shippments[0]);
						$kit->save();
					}
				}
				$this->getOrderItem()->setUnitCost($totalUnitCostForOrderItem / (count($serialItems)))
					->reCalMargin();
			}
		}
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::getJson()
	 */
	public function getJson($extra = array(), $reset = false)
	{
		$array = $extra;
		if(!$this->isJsonLoaded($reset))
		{
			$array['kit'] = $this->getKit() instanceof Kit ? $this->getKit()->getJson() : array();
		}
		return parent::getJson($array, $reset);
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
		DaoMap::setManyToOne('kit', 'Kit', 'sell_item_kit', true);
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
	 * @param Kit       $kit
	 *
	 * @return Ambigous <SellingItem, SellingItem>
	 */
	public static function create(OrderItem &$orderItem, $serialNo, $description = '', Kit $kit = null)
	{
		$sellingItem = new SellingItem();
		return $sellingItem->setOrderItem($orderItem)
			->setSerialNo(trim($serialNo))
			->setDescription(trim($description))
			->setKit($kit)
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
		$results =  SellingItem::getAllByCriteria(implode(' AND ', $where), $params, $activeOnly, $pageNo, $pageSize, $orderBy, $stats);
		return $results;
	}
}