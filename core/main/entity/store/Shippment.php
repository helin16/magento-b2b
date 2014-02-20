<?php
/**
 * Entity for Shippment
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class Shippment extends BaseEntityAbstract
{
	/**
	 * The courier of shipping
	 * 
	 * @var Courier
	 */
	protected $courier;
	/**
	 * The order this shippment is for
	 * 
	 * @var Order
	 */
	protected $order;
	/**
	 * The number of cartons on the shipping
	 * 
	 * @var int
	 */
	private $noOfCartons;
	/**
	 * The receiver's name
	 * 
	 * @var string
	 */
	private $receiver;
	/**
	 * The address of the shipping
	 * 
	 * @var string
	 */
	private $address;
	/**
	 * The contact number of the receiver
	 * 
	 * @var string
	 */
	private $contact;
	/**
	 * The date of the shippment
	 * 
	 * @var UDate
	 */
	private $shippingDate;
	/**
	 * The consignment note number
	 * 
	 * @var string
	 */
	private $conNoteNo;
	/**
	 * The estimated shipping cost from courier
	 * 
	 * @var number
	 */
	private $estShippingCost;
	/**
	 * The delivery instruction of the shipping
	 * 
	 * @var string
	 */
	private $deliveryInstructions;
	/**
	 * The shipment id from magento
	 * 
	 * @var string
	 */
	private $mageShipmentId = '';
	/**
	 * Getter of the courier
	 * 
	 * @return Courier
	 */ 
	public function getCourier() 
	{
		$this->loadManyToOne('courier');
	    return $this->courier;
	}
	/**
	 * Setter of the courier
	 * 
	 * @param Courier $value The Courier of the shippment
	 * 
	 * @return Shippment
	 */
	public function setCourier($value) 
	{
	    $this->courier = $value;
	    return $this;
	}
	/**
	 * Getter for order
	 *
	 * @return 
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
	 * @return Shippment
	 */
	public function setOrder(Order $value) 
	{
	    $this->order = $value;
	    return $this;
	}
	/**
	 * Getter of noOfCartons
	 * 
	 * @return int
	 */
	public function getNoOfCartons() 
	{
	    return $this->noOfCartons;
	}
	/**
	 * Setter of noOfCartons
	 * 
	 * @param int $value The noOfCartons
	 * 
	 * @return Shippment
	 */
	public function setNoOfCartons($value) 
	{
	    $this->noOfCartons = $value;
	    return $this;
	}
	/**
	 * Getter of the receiver
	 * 
	 * @return string
	 */
	public function getReceiver() 
	{
	    return $this->receiver;
	}
	/**
	 * Setter of the receiver
	 * 
	 * @param string $value The receiver
	 * 
	 * @return Shippment
	 */
	public function setReceiver($value) 
	{
	    $this->receiver = $value;
	    return $this;
	}
	/**
	 * Getter for address
	 *
	 * @return string
	 */
	public function getAddress() 
	{
	    return $this->address;
	}
	/**
	 * Setter for address
	 *
	 * @param string $value The address
	 *
	 * @return Shippment
	 */
	public function setAddress($value) 
	{
	    $this->address = $value;
	    return $this;
	}
	/**
	 * Getter for contact
	 *
	 * @return string
	 */
	public function getContact() 
	{
	    return $this->contact;
	}
	/**
	 * Setter for contact
	 *
	 * @param unkown $value The contact
	 *
	 * @return Shippment
	 */
	public function setContact($value) 
	{
	    $this->contact = $value;
	    return $this;
	}
	/**
	 * Getter for shippingDate
	 *
	 * @return UDate
	 */
	public function getShippingDate() 
	{
	    return $this->shippingDate;
	}
	/**
	 * Setter for shippingDate
	 *
	 * @param string $value The shippingDate
	 *
	 * @return Shippment
	 */
	public function setShippingDate($value) 
	{
	    $this->shippingDate = $value;
	    return $this;
	}
	/**
	 * Getter for conNoteNo
	 *
	 * @return string
	 */
	public function getConNoteNo() 
	{
	    return $this->conNoteNo;
	}
	/**
	 * Setter for conNoteNo
	 *
	 * @param string $value The conNoteNo
	 *
	 * @return Shippment
	 */
	public function setConNoteNo($value) 
	{
	    $this->conNoteNo = $value;
	    return $this;
	}
	/**
	 * Getter for estShippingCost
	 *
	 * @return Double
	 */
	public function getEstShippingCost() 
	{
	    return $this->estShippingCost;
	}
	/**
	 * Setter for estShippingCost
	 *
	 * @param Double $value The estShippingCost
	 *
	 * @return Shippment
	 */
	public function setEstShippingCost($value) 
	{
	    $this->estShippingCost = $value;
	    return $this;
	}
	/**
	 * Getter for deliveryInstructions
	 *
	 * @return string
	 */
	public function getDeliveryInstructions() 
	{
	    return $this->deliveryInstructions;
	}
	/**
	 * Setter for deliveryInstructions
	 *
	 * @param string $value The deliveryInstructions
	 *
	 * @return Shippment
	 */
	public function setDeliveryInstructions($value) 
	{
	    $this->deliveryInstructions = $value;
	    return $this;
	}
	/**
	 * Getter for mageShipmentId
	 *
	 * @return string
	 */
	public function getMageShipmentId() 
	{
	    return $this->mageShipmentId;
	}
	/**
	 * Setter for mageShipmentId
	 *
	 * @param string $value The mageShipmentId
	 *
	 * @return Shippment
	 */
	public function setMageShipmentId($value) 
	{
	    $this->mageShipmentId = $value;
	    return $this;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::getJson()
	 */
	public function getJson($extra = '', $reset = false)
	{
		$array = array();
		if(!$this->isJsonLoaded($reset))
			$array['courier'] = $this->getCourier()->getJson();
		
		return parent::getJson($array, $reset);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'shippment');
		
		DaoMap::setManyToOne('order', 'Order', 'sh_order');
		DaoMap::setManyToOne('courier', 'Courier', 'sh_courier');
		DaoMap::setIntType('noOfCartons');
		DaoMap::setStringType('receiver', 'varchar', 100);
		DaoMap::setStringType('address', 'varchar', 200);
		DaoMap::setStringType('contact', 'varchar', 100);
		DaoMap::setDateType('shippingDate');
		DaoMap::setStringType('conNoteNo', 'varchar', 100);
		DaoMap::setIntType('estShippingCost', 'Double', '10,4');
		DaoMap::setStringType('deliveryInstructions', 'varchar', 255);
		DaoMap::setStringType('mageShipmentId', 'varchar', 100);
		
		
		parent::__loadDaoMap();
		DaoMap::createIndex('receiver');
		DaoMap::createIndex('conNoteNo');
		DaoMap::createIndex('shippingDate');
		DaoMap::createIndex('mageShipmentId');
		DaoMap::commit();
	}
}