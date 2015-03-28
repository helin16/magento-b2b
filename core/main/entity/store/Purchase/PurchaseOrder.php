<?php
/**
 * Entity for PurchaseOrder
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class PurchaseOrder extends BaseEntityAbstract
{
	const PO_NO_PRE = 'BPO_';
	const STATUS_NEW = 'NEW';
	const STATUS_ORDERED = 'ORDERED';
	const STATUS_RECEIVING = 'RECEIVING';
	const STATUS_RECEIVED = 'RECEIVED';
	const STATUS_CANCELED = 'CANCELED';
	const STATUS_CLOSED = 'CLOSED';
	/**
	 * The purchase order number
	 *
	 * @var string
	 */
	private $purchaseOrderNo = '';
	/**
	 * The supplier
	 *
	 * @var Supplier
	 */
	protected $supplier;
	/**
	 * The supplier Reference No
	 *
	 * @var string
	 */
	private $supplierRefNo;
	/**
	 * status of this purchase order
	 *
	 * @var string
	 */
	private $status = self::STATUS_NEW;
	/**
	 * The contact person's name of the supplier for this order
	 *
	 * @var string
	 */
	private $supplierContact = '';
	/**
	 * The contact person's contact number of the supplier for this order
	 *
	 * @var string
	 */
	private $supplierContactNumber = '';
	/**
	 * The the shipping cost for this order
	 *
	 * @var string
	 */
	private $shippingCost = 0;
	/**
	 * The the handling cost for this order
	 *
	 * @var string
	 */
	private $handlingCost = 0;
	/**
	 * The orderdate
	 * @var UDate
	 */
	private $orderDate;
	/**
	 * The eta of the po
	 *
	 * @var UDate
	 */
	private $eta = '';

	private $totalAmount = 0;
	private $totalPaid = 0;
	/**
	 * Getter for purchaseOrderNo
	 *
	 * @return string
	 */
	public function getPurchaseOrderNo()
	{
	    return $this->purchaseOrderNo;
	}
	/**
	 * Setter for purchaseOrderNo
	 *
	 * @param string $value The purchaseOrderNo
	 *
	 * @return PurchaseOrder
	 */
	public function setPurchaseOrderNo($value)
	{
	    $this->purchaseOrderNo = $value;
	    return $this;
	}
	/**
	 * Getter for supplier
	 *
	 * @return Supplier
	 */
	public function getSupplier()
	{
		$this->loadManyToOne('supplier');
	    return $this->supplier;
	}
	/**
	 * Setter for supplier
	 *
	 * @param Supplier $value The supplier
	 *
	 * @return PurchaseOrder
	 */
	public function setSupplier(Supplier $value)
	{
	    $this->supplier = $value;
	    return $this;
	}
	/**
	 * Getter for supplierRefNo
	 *
	 * @return string
	 */
	public function getSupplierRefNo()
	{
	    return $this->supplierRefNo;
	}
	/**
	 * Setter for supplierRefNo
	 *
	 * @param string $value The supplierRefNo
	 *
	 * @return PurchaseOrder
	 */
	public function setSupplierRefNo($value)
	{
	    $this->supplierRefNo = $value;
	    return $this;
	}
	/**
	 * Getter for status
	 *
	 * @return string
	 */
	public function getStatus()
	{
	    return $this->status;
	}
	/**
	 * Setter for status
	 *
	 * @param string $value The status
	 *
	 * @return PurchaseOrder
	 */
	public function setStatus($value)
	{
    	$this->status = trim($value);
	    return $this;
	}
	/**
	 * Getter for supplierContact
	 *
	 * @return string
	 */
	public function getSupplierContact()
	{
	    return $this->supplierContact;
	}
	/**
	 * Setter for supplierContact
	 *
	 * @param string $value The supplierContact
	 *
	 * @return PurchaseOrder
	 */
	public function setSupplierContact($value)
	{
	    $this->supplierContact = $value;
	    return $this;
	}
	/**
	 * Getter for supplierContactNumber
	 *
	 * @return string
	 */
	public function getSupplierContactNumber()
	{
		return $this->supplierContactNumber;
	}
	/**
	 * Setter for supplierContactNumber
	 *
	 * @param string $value The supplierContactNumber
	 *
	 * @return PurchaseOrder
	 */
	public function setSupplierContactNumber($value)
	{
		$this->supplierContactNumber = $value;
		return $this;
	}
	/**
	 * Getter for PO shipping cost
	 *
	 * @return string
	 */
	public function getShippingCost()
	{
		return $this->shippingCost;
	}
	/**
	 * Setter for PO shipping cost
	 *
	 * @param string $value The shippingCost
	 *
	 * @return string
	 */
	public function setShippingCost($value)
	{
		$this->shippingCost = $value;
		return $this;
	}
	/**
	 * Getter for PO handlingCost
	 *
	 * @return string
	 */
	public function getHandlingCost()
	{
		return $this->handlingCost;
	}
	/**
	 * Setter for PO handlingCost
	 *
	 * @param string $value The handlingCost
	 *
	 * @return string
	 */
	public function setHandlingCost($value)
	{
		$this->handlingCost = $value;
		return $this;
	}
	/**
	 * Getter for totalProdcutCount
	 *
	 * @return int
	 */
	public function getTotalProductCount()
	{
		$sql = "select sum(qty) `qty` from purchaseorderitem where active = 1 and purchaseOrderId = ? ";
		$result = Dao::getSingleResultNative($sql, array($this->getId()));
		return intval($result['qty']);
	}
	/**
	 * Getter for totalReceivedCount
	 *
	 * @return int
	 */
	public function getTotalReceivedCount()
	{
		$sql = "select sum(qty) `qty` from receivingitem where active = 1 and purchaseOrderId = ? ";
		$result = Dao::getSingleResultNative($sql, array($this->getId()));
		return intval($result['qty']);
	}
	/**
	 * Getter for orderDate
	 *
	 * @return UDate
	 */
	public function getOrderDate()
	{
		$this->orderDate = new UDate(trim($this->orderDate));
	    return $this->orderDate;
	}
	/**
	 * Setter for orderDate
	 *
	 * @param string $value The orderDate
	 *
	 * @return PurchaseOrder
	 */
	public function setOrderDate($value)
	{
	    $this->orderDate = $value;
	    return $this;
	}
	/**
	 * Getter for eta
	 *
	 * @return UDate
	 */
	public function getEta()
	{
		$this->eta = new UDate(trim($this->eta));
	    return $this->eta;
	}
	/**
	 * Setter for eta
	 *
	 * @param string $value The eta
	 *
	 * @return PurchaseOrder
	 */
	public function setEta($value)
	{
	    $this->eta = $value;
	    return $this;
	}
	/**
	 * validating the status
	 *
	 * @param string $status The status that we are validating
	 *
	 * @return boolean
	 */
	private function _validateStatus($status)
	{
		$oClass = new ReflectionClass (get_class($this));
		foreach($oClass->getConstants() as $name => $value)
		{
			if(strpos($name, 'STATUS_') === 0 && trim($value) === trim($status))
				return true;
		}
		return false;
	}
	/**
	 * Getter for totalAmount
	 *
	 * @return number
	 */
	public function getTotalAmount()
	{
		return $this->totalAmount;
	}
	/**
	 * Setter for totalAmount
	 *
	 * @param number $value The totalAmount
	 *
	 * @return PurchaseOrder
	 */
	public function setTotalAmount($value)
	{
		$this->totalAmount = $value;
		return $this;
	}
	/**
	 * Getter for totalPaid
	 *
	 * @return number
	 */
	public function getTotalPaid()
	{
		return $this->totalPaid;
	}
	/**
	 * Setter for totalPaid
	 *
	 * @param number $value The totalPaid
	 *
	 * @return PurchaseOrder
	 */
	public function setTotalPaid($value)
	{
		$this->totalPaid = $value;
		return $this;
	}
	/**
	 * Getting the total recieved value for this PO
	 * @return number
	 */
	public function getTotalRecievedValue()
	{
		$receivedItems = ReceivingItem::getAllByCriteria('purchaseOrderId = ?', array($this->getId()));
		if(count($receivedItems) === 0)
			return 0;
		$totalValue = 0;
		foreach($receivedItems as $item)
		{
			$totalValue = $totalValue + ($item->getUnitPrice() * $item->getQty());
		}
		return $totalValue;
	}
	/**
	 * Getting the supplier invoices
	 *
	 * @return array
	 */
	public function getSupplierInvoices()
	{
		$result = Dao::getResultsNative('select distinct invoiceNo `invoiceNo` from receivingitem where purchaseOrderId = ?', array($this->getId()), PDO::FETCH_ASSOC);
		return array_map(create_function('$a', 'return $a["invoiceNo"];'), $result);
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::postSave()
	 */
	public function postSave()
	{
		if(trim($this->getPurchaseOrderNo()) === '') {
			$this->setPurchaseOrderNo(self::PO_NO_PRE . str_pad($this->getId(), 6, '0', STR_PAD_LEFT))
				->save();
		}
		//if the order status is ordered, then calculated the
		if(intval($this->getActive()) === 0 || trim($this->getStatus()) === PurchaseOrder::STATUS_CANCELED) {
			$items = PurchaseOrderItem::getAllByCriteria('purchaseOrderId = ? and stockCalculated = 1', array($this->getId()));
			foreach($items as $item) {
				$item->getProduct()->ordered(0 - $item->getQty(), 'PO(' . $this->getPurchaseOrderNo() . ') is CANCELLED or Deactivated', $item);
				$item->setStockCalculated(false)
					->save()
					->addLog('UNMarked this item for StockOnPO and stockCalculated', Log::TYPE_SYSTEM, 'STOCK_QTY_CHG', __CLASS__ . '::' . __FUNCTION__);
			}
			$this->addComment(count($items) . ' POItem(s) are reverted, as this PO is now deactivated or CANCELLED');

			$receivedItems = ReceivingItem::getAllByCriteria('purchaseOrderId = ?', array($this->getId()));
			foreach($receivedItems as $item) {
				$item->setActive(false)
					->save();
			}
		}
		else if(trim($this->getStatus()) === PurchaseOrder::STATUS_ORDERED) {
			$items = PurchaseOrderItem::getAllByCriteria('purchaseOrderId = ? and stockCalculated = 0', array($this->getId()));
			foreach($items as $item) {
				$item->getProduct()->ordered($item->getQty(), 'PO(' . $this->getPurchaseOrderNo() . ') is ordered now.', $item);
				$item->setStockCalculated(true)
					->save()
					->addLog('Marked this item for StockOnPO and stockCalculated', Log::TYPE_SYSTEM, 'STOCK_QTY_CHG', __CLASS__ . '::' . __FUNCTION__);
			}
		}
	}
	/**
	 * adding a item onto the purchase order
	 *
	 * @param Product $product
	 * @param double  $unitPrice
	 * @param int     $qty
	 * @param string  $supplierItemCode
	 * @param string  $description
	 * @param double  $totalPrice
	 * @param mixed   $newItem           The new PurhcaseOrderItem
	 *
	 * @return PurchaseOrder
	 */
	public function addItem(Product $product, $unitPrice = '0.0000', $qty = 1, $supplierItemCode = '', $description = '', $totalPrice = null, &$newItem = null)
	{
		$newItem = PurchaseOrderItem::create($this, $product, $unitPrice, $qty, $supplierItemCode, $description, $totalPrice);
		return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::preSave()
	 */
	public function preSave()
	{
		if(trim($this->getId()) !== '') {
			$oldStatuses = Dao::getResultsNative('select status from purchaseorder where id = ?', array($this->getId()));
			if(count($oldStatuses) > 0 && ($oldStatus = trim($oldStatuses[0]['status'])) !== ($status = trim($this->getStatus()))) {
				$msg = 'Changed status from "' . $oldStatus . '" to "' . $status . '"';
				$this->addComment($msg, Comments::TYPE_SYSTEM)
					->addLog($msg, Log::TYPE_SYSTEM, 'PO_STATUS_CHANGE', __CLASS__ . '::' . __FUNCTION__);
			}
		}
	}
	/**
	 * (non-PHPdoc)
	 * @see HydraEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'po');

		DaoMap::setStringType('purchaseOrderNo', 'varchar', 100);
		DaoMap::setManyToOne('supplier', 'Supplier', 'po_sup');
		DaoMap::setStringType('supplierRefNo', 'varchar', 100);
		DaoMap::setStringType('status', 'varchar', 20);
		DaoMap::setStringType('supplierContact', 'varchar', 100);
		DaoMap::setStringType('supplierContactNumber', 'varchar', 100);
		DaoMap::setStringType('shippingCost', 'Double', '10,4');
		DaoMap::setStringType('handlingCost', 'Double', '10,4');
		DaoMap::setDateType('orderDate');
		DaoMap::setDateType('eta');
		DaoMap::setIntType('totalAmount', 'Double', '10,4');
		DaoMap::setIntType('totalPaid', 'Double', '10,4');
		parent::__loadDaoMap();

		DaoMap::createUniqueIndex('purchaseOrderNo');
		DaoMap::createIndex('supplierRefNo');
		DaoMap::createIndex('status');
		DaoMap::createIndex('orderDate');
		DaoMap::createIndex('eta');
		DaoMap::createIndex('totalAmount');
		DaoMap::createIndex('totalPaid');
		DaoMap::createIndex('supplierContact');
		DaoMap::createIndex('supplierContactNumber');
		DaoMap::createIndex('shippingCost');
		DaoMap::createIndex('handlingCost');

		DaoMap::commit();
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
			$array['supplier'] = $this->getSupplier() instanceof Supplier ? $this->getSupplier()->getJson() : array();
			$array['totalProductCount'] = $this->getTotalProductCount();
			$array['totalReceivedCount'] = $this->getTotalReceivedCount();
			$array['totalReceivedValue'] = $this->getTotalRecievedValue();
			$array['supplierInvoices'] = $this->getSupplierInvoices();
		}
		return parent::getJson($array, $reset);
	}
	/**
	 * creating a PO
	 *
	 * @param Supplier $supplier
	 * @param string   $supplierRefNo
	 * @param string   $supplierContact
	 * @param string   $supplierContactNumber
	 * @param string   $shippingCost
	 * @param string   $handlingCost
	 *
	 * @return PurchaseOrder
	 */
	public static function create(Supplier $supplier, $supplierRefNo = '', $supplierContact = '', $supplierContactNumber = '',$shippingCost = 0, $handlingCost = 0)
	{
		$class = get_called_class();
		$entity = new $class();
		return $entity->setSupplier($supplier)
			->setSupplierRefNo(trim($supplierRefNo))
			->setSupplierContact($supplierContact)
			->setSupplierContactNumber($supplierContactNumber)
			->setshippingCost($shippingCost)
			->sethandlingCost($handlingCost)
			->save();
	}
	/**
	 * Getter for status options
	 *
	 * @return array
	 */
	public static function getStatusOptions()
	{
		return array(self::STATUS_NEW, self::STATUS_ORDERED, self::STATUS_RECEIVING, self::STATUS_CANCELED, self::STATUS_CLOSED, self::STATUS_RECEIVED);
	}
}