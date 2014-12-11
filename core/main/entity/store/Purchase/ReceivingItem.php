<?php
/**
 * Entity for ReceivingItem
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class ReceivingItem extends BaseEntityAbstract
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
	 * The serial number
	 * 
	 * @var string
	 */
	private $serialNo;
	/**
	 * The invoiceNo
	 * 
	 * @var string
	 */
	private $invoiceNo;
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
	 * @return ReceivingItem
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
	 * @return ReceivingItem
	 */
	public function setPurchaseOrder(PurchaseOrder $value)
	{
		$this->purchaseOrder = $value;
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
	 * @return ReceivingItem
	 */
	public function setUnitPrice($value) 
	{
	    $this->unitPrice = $value;
	    return $this;
	}
	/**
	 * Getter for invoiceNo
	 *
	 * @return string
	 */
	public function getInvoiceNo() 
	{
	    return $this->invoiceNo;
	}
	/**
	 * Setter for invoiceNo
	 *
	 * @param string $value The invoiceNo
	 *
	 * @return ReceivingItem
	 */
	public function setInvoiceNo($value) 
	{
	    $this->invoiceNo = $value;
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
	 * @return ReceivingItem
	 */
	public function setSerialNo($value) 
	{
	    $this->serialNo = $value;
	    return $this;
	}
	public function postSave()
	{
		//if a receiving item gets deactivate, then stockonHand needs to be changed
		if(trim($this->getId()) !== '' && intval($this->getActive()) === 0 ) {
			$msg = 'ReceivedIem for Product(SKU=' . $product . '), unitPrice=' . $unitPrice . ', serialNo=' . $serialNo . ', invoiceNo=' . $invoiceNo . ' has now been deactivated.';
			$po->addLog($msg, Log::TYPE_SYSTEM, Log::TYPE_SYSTEM, '', __CLASS__ . '::' . __FUNCTION__)
				->addComment($msg, Comments::TYPE_WAREHOUSE);
			$product->setStockOnPO(($origStockOnPO = $product->getStockOnPO()) + 1)
				->setStockOnHand(($origStockOnHand = $product->getStockOnHand()) - 1)
				->snapshotQty($this, 'Stock received')
				->save()
				->addLog('stockOnPO(' . $product->getStockOnPO() . ' => ' .$product->getStockOnPO() . ', stockOnHand (' . $origStockOnHand . ' => ' . $product->getStockOnHand() . ')', Log::TYPE_SYSTEM, 'STOCK_QTY_CHG', __CLASS__ . '::' . __FUNCTION__);
		}
	}
	/**
	 * (non-PHPdoc)
	 * @see HydraEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'rec_item');
	
		DaoMap::setManyToOne('purchaseOrder', 'PurchaseOrder', 'rec_item_po');
		DaoMap::setManyToOne('product', 'Product', 'rec_item_pro');
		DaoMap::setIntType('unitPrice', 'double', '10,4');
		DaoMap::setStringType('serialNo', 'varchar', '10');
		DaoMap::setStringType('invoiceNo', 'varchar', '10');
		
		parent::__loadDaoMap();
		DaoMap::createIndex('serialNo');
		DaoMap::createIndex('unitPrice');
		DaoMap::createIndex('invoiceNo');
		DaoMap::commit();
	}
	/**
	 * creating a receiving Item
	 * 
	 * @param PurchaseOrder $po
	 * @param Product       $product
	 * @param double        $unitPrice
	 * @param string        $serialNo
	 * @param string        $invoiceNo
	 * 
	 * @return PurchaseOrderItem
	 */
	public static function create(PurchaseOrder $po, Product $product, $unitPrice = '0.0000', $serialNo = '', $invoiceNo = '', $comments = "")
	{
		$entity = new ReceivingItem();
		$msg = 'Received a Product(SKU=' . $product . ') with unitPrice=' . $unitPrice . ', serialNo=' . $serialNo . ', invoiceNo=' . $invoiceNo;
		$entity->setPurchaseOrder($po)
			->setProduct($product)
			->setUnitPrice($unitPrice)
			->setInvoiceNo($invoiceNo)
			->setSerialNo($serialNo)
			->save()
			->addComment($comments, Comments::TYPE_WAREHOUSE)
			->addLog($msg, Log::TYPE_SYSTEM, Log::TYPE_SYSTEM, '', __CLASS__ . '::' . __FUNCTION__);
		$po->addLog($msg, Log::TYPE_SYSTEM, Log::TYPE_SYSTEM, '', __CLASS__ . '::' . __FUNCTION__)
			->addComment($msg, Comments::TYPE_WAREHOUSE);
		$product->setStockOnPO(($origStockOnPO = $product->getStockOnPO()) - 1)
			->setStockOnHand(($origStockOnHand = $product->getStockOnHand()) + 1)
			->save()
			->addLog('stockOnPO(' . $product->getStockOnPO() . ' => ' .$product->getStockOnPO() . ', stockOnHand (' . $origStockOnHand . ' => ' . $product->getStockOnHand() . ')', Log::TYPE_SYSTEM, 'STOCK_QTY_CHG', __CLASS__ . '::' . __FUNCTION__);
		return $entity;
	}
}