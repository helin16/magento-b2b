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
	public function setdeliveryInstructions($value) 
	{
	    $this->deliveryInstructions = $value;
	    return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'shippment');
		
		DaoMap::setIntType('noOfCartons');
		DaoMap::setStringType('receiver');
		DaoMap::setStringType('address');
		DaoMap::setStringType('contact');
		DaoMap::setDateType('shippingDate');
		DaoMap::setStringType('conNoteNo');
		DaoMap::setIntType('estShippingCost', 'Double', '10,4');
		DaoMap::setStringType('deliveryInstructions', 'varchar', 255);
		
		DaoMap::setManyToOne('courier', 'Courier', 'sh_courier');
		
		parent::__loadDaoMap();
		DaoMap::commit();
	}
}