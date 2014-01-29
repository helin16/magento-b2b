<?php
/**
 * Entity for OrderItem
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class OrderItem extends BaseEntityAbstract
{
	/**
	 * The order 
	 * 
	 * @var Order
	 */
	protected $order;
	/**
	 * The product 
	 * 
	 * @var Product
	 */
	protected $product;
	/**
	 * The quantity that orderred
	 * 
	 * @var int
	 */
	private $qtyOrdered;
	/**
	 * The unit price for that product
	 * 
	 * @var number
	 */
	private $unitPrice;
	/**
	 * The total price for that product
	 * 
	 * @var number
	 */
	private $totalPrice;
	/**
	 * The ETA of the product
	 * 
	 * @var UDate
	 */
	private $eta = null;
	/**
	 * Whether the warehouse has picked this item for shipping
	 * 
	 * @var bool
	 */
	private $isPicked = false;
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
	 * @param Order $value The order
	 *
	 * @return OrderItem
	 */
	public function setOrder($value) 
	{
	    $this->order = $value;
	    return $this;
	}
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
	 * @return OrderItem
	 */
	public function setProduct($value) 
	{
	    $this->product = $value;
	    return $this;
	}
	/**
	 * Getter for qtyOrdered
	 *
	 * @return int
	 */
	public function getQtyOrdered() 
	{
	    return $this->qtyOrdered;
	}
	/**
	 * Setter for qtyOrdered
	 *
	 * @param int $value The qtyOrdered
	 *
	 * @return OrderItem
	 */
	public function setQtyOrdered($value) 
	{
	    $this->qtyOrdered = $value;
	    return $this;
	}
	/**
	 * Getter for unitPrice
	 *
	 * @return number
	 */
	public function getUnitPrice() 
	{
	    return $this->unitPrice;
	}
	/**
	 * Setter for unitPrice
	 *
	 * @param number $value The unitPrice
	 *
	 * @return OrderItem
	 */
	public function setUnitPrice($value) 
	{
	    $this->unitPrice = $value;
	    return $this;
	}
	/**
	 * Getter for totalPrice
	 *
	 * @return number
	 */
	public function getTotalPrice() 
	{
	    return $this->totalPrice;
	}
	/**
	 * Setter for totalPrice
	 *
	 * @param number $value The totalPrice
	 *
	 * @return OrderItem
	 */
	public function setTotalPrice($value) 
	{
	    $this->totalPrice = $value;
	    return $this;
	}
	/**
	 * Getter for eta
	 *
	 * @return UDate
	 */
	public function getEta() 
	{
		if($this->eta === null || $this->eta === '' )
			return null;
		if(is_string($this->eta))
			$this->eta = new UDate($this->eta);
	    return $this->eta;
	}
	/**
	 * Setter for eta
	 *
	 * @param string $value The eta
	 *
	 * @return OrderItem
	 */
	public function setEta($value) 
	{
	    $this->eta = $value;
	    return $this;
	}
	/**
	 * Getter for isPicked
	 *
	 * @return Bool
	 */
	public function getIsPicked() 
	{
	    return trim($this->isPicked) === '1';
	}
	/**
	 * Setter for isPicked
	 *
	 * @param string $value The isPicked
	 *
	 * @return OrderItem
	 */
	public function setIsPicked($value) 
	{
	    $this->isPicked = $value;
	    return $this;
	}
	/**
	 * creating the orderitem object
	 * 
	 * @param Order   $order
	 * @param Product $product
	 * @param number  $unitPrice
	 * @param number  $qty
	 * @param number  $totalPrice
	 * @param string  $eta
	 * 
	 * @return Ambigous <OrderItem, BaseEntityAbstract>
	 */
	public static function create(Order $order, Product $product, $unitPrice, $qty, $totalPrice, $eta = null)
	{
		if(count($items = self::getItems($order, $product)) === 0)
			$item = new OrderItem();
		else
			$item = $items[0];
		$item->setOrder($order)
			->setProduct($product)
			->setUnitPrice($unitPrice)
			->setQtyOrdered($qty)
			->setTotalPrice($totalPrice)
			->setEta($eta);
		FactoryAbastract::dao(get_called_class())->save($item);
		return $item;
	}
	/**
	 * Getting the order item via order and product
	 * 
	 * @param Order   $order
	 * @param Product $product
	 * 
	 * @return array
	 */
	public static function getItems(Order $order, Product $product = null)
	{
		$where = 'orderId = ?';
		$params = array($order->getId());
		if($product instanceof Product)
		{
			$where .=' AND productId = ?';
			$params[] = $product->getId();
		}
		return FactoryAbastract::dao(get_called_class())->findByCriteria($where, $params, true, 1, 1);
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::getJson()
	 */
	public function getJson($extra = '', $reset = false)
	{
		$array = array();
	    if(!$this->isJsonLoaded($reset))
	    {
	    	$array['product'] = $this->getProduct()->getJson();
	    }
	    return parent::getJson($array, $reset);
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'ord_item');
		
		DaoMap::setManyToOne('order', 'Order', 'ord');
		DaoMap::setManyToOne('product', 'Product', 'pro');
		DaoMap::setIntType('qtyOrdered');
		DaoMap::setIntType('unitPrice', 'double', '10,4');
		DaoMap::setIntType('totalPrice', 'double', '10,4');
		DaoMap::setDateType('eta', 'datetime', true, null);
		DaoMap::setBoolType('isPicked');
		
		parent::__loadDaoMap();
		
		DaoMap::createIndex('isPicked');
		DaoMap::commit();
	}
}