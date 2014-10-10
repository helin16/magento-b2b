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
	 * The contact person of the supplier for this order
	 * 
	 * @var Person
	 */
	protected $supplierContact;
	private $orderDate;
	private $totalAmount;
	private $totalPaid;
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
	    $this->status = trim($status);
	    return $this;
	}
	/**
	 * Getter for supplierContact
	 *
	 * @return Person
	 */
	public function getSupplierContact() 
	{
		$this->loadManyToOne('supplierContact');
	    return $this->supplierContact;
	}
	/**
	 * Setter for supplierContact
	 *
	 * @param Person $value The supplierContact
	 *
	 * @return PurchaseOrder
	 */
	public function setSupplierContact(Person $value = null) 
	{
	    $this->supplierContact = $value;
	    return $this;
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
			if(strpos($name, 'STATUS_') === 0 && $value === $status)
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
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::postSave()
	 */
	public function postSave()
	{
		if(trim($this->getPurchaseOrderNo()) === '')
		{
			$this->setPurchaseOrderNo(self::PO_NO_PRE . str_pad($this->getId(), 6, '0', STR_PAD_LEFT))
				->save();
		}
	}
	/**
	 * pushing the status of the status
	 * 
	 * @param string $status The new status of the PO
	 * 
	 * @throws EntityException
	 * @return PurchaseOrder
	 */
	public function pushStatus($status)
	{
		if(!$this->_validateStatus($status))
			throw new EntityException('Invalid status(=' . $status . ').');
		if($status === ($oldStatus = $this->getStatus())) //no change of the status
			return $this;
		$this->setStatus($status);
		if(trim($this->getId()) !== '')
		{
			$msg = 'Changed status from "' . $oldStatus . '" to "' . $status . '"';
			$this->addComment($msg, Comments::TYPE_SYSTEM);
			Log::LogEntity($this, $msg, Log::TYPE_SYSTEM);
		}
		return $this;
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
	 * 
	 * @return PurchaseOrder
	 */
	public function addItem(Product $product, $unitPrice = '0.0000', $qty = 1, $supplierItemCode = '', $description = '', $totalPrice = null)
	{
		PurchaseOrderItem::create($this, $product, $unitPrice, $qty, $supplierItemCode, $description, $totalPrice);
		return $this;
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
		DaoMap::setManyToOne('supplierContact', 'Person', 'po_p', true);
		DaoMap::setDateType('orderDate');
		DaoMap::setIntType('totalAmount', 'Double', '10,4');
		DaoMap::setIntType('totalPaid', 'Double', '10,4');
		parent::__loadDaoMap();
	
		DaoMap::createUniqueIndex('purchaseOrderNo');
		DaoMap::createIndex('supplierRefNo');
		DaoMap::createIndex('status');
		DaoMap::createIndex('orderDate');
		DaoMap::createIndex('totalAmount');
		DaoMap::createIndex('totalPaid');
	
		DaoMap::commit();
	}
	/**
	 * creating a PO
	 * 
	 * @param Supplier $supplier
	 * @param string   $supplierRefNo
	 * @param Person   $supplierContact
	 * 
	 * @return PurchaseOrder
	 */
	public function create(Supplier $supplier, $supplierRefNo = '', Person $supplierContact = null)
	{
		$class = get_called_class();
		$entity = new $class();
		return $entity->setSupplier($supplier)
			->setSupplierRefNo(trim($supplierRefNo))
			->setSupplierContact($supplierContact)
			->save();
	}
}