<?php
/**
 * Entity for PurchaseOrderItem
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class PurchaseOrderItem extends BaseEntityAbstract
{
	/**
	 * The product
	 * 
	 * @var Product
	 */
	protected $product;
	/**
	 * The purchaseorder
	 * 
	 * @var PurchaseOrder
	 */
	protected $purchaseOrder;
	/**
	 * The unitprice of each item
	 * 
	 * @var double
	 */
	private $unitPrice;
	/**
	 * The quantity of the item
	 * 
	 * @var int
	 */
	private $qty;
	/**
	 * The receivedQty of the item
	 *
	 * @var int
	 */
	private $receivedQty = 0;
	/**
	 * The total price of the whole lot
	 * 
	 * @var double
	 */
	private $totalPrice;
	/**
	 * Whether we have calculated all the stock for this item
	 * 
	 * @var bool
	 */
	private $stockCalculated = false;
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
	 * @return PurchaseOrderItem
	 */
	public function setProduct(Product $value) 
	{
	    $this->product = $value;
	    return $this;
	}
	/**
	 * Getter for purchaseOrder
	 *
	 * @return PurchaseOrder
	 */
	public function getPurchaseOrder() 
	{
		$this->loadManyToOne('purchaseOrder');
	    return $this->purchaseOrder;
	}
	/**
	 * Setter for purchaseOrder
	 *
	 * @param PurchaseOrder $value The purchaseOrder
	 *
	 * @return PurchaseOrderItem
	 */
	public function setPurchaseOrder(PurchaseOrder $value) 
	{
	    $this->purchaseOrder = $value;
	    return $this;
	}
	/**
	 * Getter for qty
	 *
	 * @return int
	 */
	public function getQty() 
	{
	    return $this->qty;
	}
	/**
	 * Setter for qty
	 *
	 * @param int $value The qty
	 *
	 * @return PurchaseOrderItem
	 */
	public function setQty($value) 
	{
	    $this->qty = $value;
	    return $this;
	}
	/**
	 * Getter for stockCalculated
	 *
	 * @return bool
	 */
	public function getStockCalculated() 
	{
	    return $this->stockCalculated;
	}
	/**
	 * Setter for stockCalculated
	 *
	 * @param int $value The stockCalculated
	 *
	 * @return PurchaseOrderItem
	 */
	public function setStockCalculated($value) 
	{
	    $this->stockCalculated = $value;
	    return $this;
	}
	/**
	 * Getter for receivedQty
	 *
	 * @return int
	 */
	public function getReceivedQty() 
	{
	    return $this->receivedQty;
	}
	/**
	 * Setter for receivedQty
	 *
	 * @param int $value The receivedQty
	 *
	 * @return PurchaseOrderItem
	 */
	public function setReceivedQty($value) 
	{
	    $this->receivedQty = $value;
	    return $this;
	}
	/**
	 * Getter for unitPrice
	 *
	 * @return double
	 */
	public function getUnitPrice() 
	{
	    return $this->unitPrice;
	}
	/**
	 * Setter for unitPrice
	 *
	 * @param double $value The unitPrice
	 *
	 * @return PurchaseOrderItem
	 */
	public function setUnitPrice($value) 
	{
	    $this->unitPrice = $value;
	    return $this;
	}
	/**
	 * Getter for totalPrice
	 *
	 * @return double
	 */
	public function getTotalPrice() 
	{
	    return $this->totalPrice;
	}
	/**
	 * Setter for totalPrice
	 *
	 * @param double $value The totalPrice
	 *
	 * @return PurchaseOrderItem
	 */
	public function setTotalPrice($value) 
	{
	    $this->totalPrice = $value;
	    return $this;
	}
	/**
	 * (non-PHPdoc)
	 * 
	 * @see BaseEntityAbstract::preSave()
	 */
	public function preSave()
	{
		if(trim($this->getQty()) === '0')
			throw new EntityException('You can NOT save a purchaseorder item with Qty = 0.');
	}
	/**
	 * (non-PHPdoc)
	 * @see HydraEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'po_item');
	
		DaoMap::setManyToOne('purchaseOrder', 'PurchaseOrder', 'po_item_po');
		DaoMap::setManyToOne('product', 'Product', 'po_item_pro');
		DaoMap::setIntType('qty');
		DaoMap::setIntType('receivedQty');
		DaoMap::setIntType('unitPrice', 'double', '10,4');
		DaoMap::setIntType('totalPrice', 'double', '10,4');
		DaoMap::setBoolType('stockCalculated');
		
		parent::__loadDaoMap();
		DaoMap::createIndex('qty');
		DaoMap::createIndex('receivedQty');
		DaoMap::createIndex('stockCalculated');
		DaoMap::commit();
	}
	/**
	 * creating a PO Item
	 * 
	 * @param PurchaseOrder $po
	 * @param Product       $product
	 * @param double        $unitPrice
	 * @param int           $qty
	 * @param string        $supplierItemCode
	 * @param string        $supplierId
	 * @param string        $description
	 * @param double        $totalPrice
	 * 
	 * @return PurchaseOrderItem
	 */
	public static function create(PurchaseOrder $po, Product $product, $unitPrice = '0.0000', $qty = 1, $totalPrice = null, $receivedQty = 0)
	{
		$entity = new PurchaseOrderItem();
		$msg = 'created POI for PO(' . $po->getPurchaseOrderNo() . ') with Product(SKU=' . $product->getSku() . ') unitPrice=' . $unitPrice . ', qty=' . $qty;
		$entity->setPurchaseOrder($po)
			->setProduct($product)
			->setUnitPrice($unitPrice)
			->setQty($qty)
			->setReceivedQty($receivedQty)
			->setTotalPrice(trim($totalPrice) !== '' ? $totalPrice : ($unitPrice * $qty))
			->save()
			->addLog($msg, Log::TYPE_SYSTEM, 'Auto Log', __CLASS__ . '::' . __FUNCTION__);
		$po->addLog($msg, Log::TYPE_SYSTEM, 'Auto Log', __CLASS__ . '::' . __FUNCTION__);
		return $entity;
	}
}