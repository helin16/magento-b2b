<?php
/**
 * Entity for Customer
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class Customer extends BaseEntityAbstract
{
	/**
	 * The name of this customer
	 * 
	 * @var string
	 */
	private $name;
	/**
	 * The description of this customer
	 * 
	 * @var string
	 */
	private $description = '';
	/**
	 * The contact  of this customer
	 * 
	 * @var string
	 */
	private $contactNo;
	/**
	 * The email of this customer
	 * 
	 * @var string
	 */
	private $email;
	/**
	 * The billing of this customer
	 * 
	 * @var Address
	 */
	protected $billingAddress;
	/**
	 * The shipping of this customer
	 * 
	 * @var Address
	 */
	protected $shippingAddress = null;
	/**
	 * The id of the customer in magento
	 * 
	 * @var int
	 */
	private $mageId = 0;
	/**
	 * Whether this order is imported from B2B
	 *
	 * @var bool
	 */
	private $isFromB2B = false;
	/**
	 * Getter for name
	 *
	 * @return string
	 */
	public function getName() 
	{
	    return $this->name;
	}
	/**
	 * Setter for name
	 *
	 * @param string $value The name
	 *
	 * @return Customer
	 */
	public function setName($value)
	{
	    $this->name = $value;
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
	 * Setter for description
	 *
	 * @param string $value The description
	 *
	 * @return Customer
	 */
	public function setDescription($value) 
	{
	    $this->description = $value;
	    return $this;
	}
	/**
	 * Getter for contactNo
	 *
	 * @return 
	 */
	public function getContactNo() 
	{
	    return $this->contactNo;
	}
	/**
	 * Setter for contactNo
	 *
	 * @param string $value The contactNo
	 *
	 * @return Customer
	 */
	public function setContactNo($value) 
	{
	    $this->contactNo = $value;
	    return $this;
	}
	/**
	 * Getter for email
	 *
	 * @return string
	 */
	public function getEmail() 
	{
	    return $this->email;
	}
	/**
	 * Setter for email
	 *
	 * @param string $value The email
	 *
	 * @return Customer
	 */
	public function setEmail($value) 
	{
	    $this->email = $value;
	    return $this;
	}
	/**
	 * Getter for billingAddress
	 *
	 * @return Address
	 */
	public function getBillingAddress()
	{
		$this->loadManyToOne('billingAddress');
	    return $this->billingAddress;
	}
	/**
	 * Setter for billingAddress
	 *
	 * @param Address $value The addresses
	 *
	 * @return Customer
	 */
	public function setBillingAddress(Address $value)
	{
	    $this->billingAddress = $value;
	    return $this;
	}
	/**
	 * Getter for shippingAddress
	 *
	 * @return 
	 */
	public function getShippingAddress() 
	{
		$this->loadManyToOne('shippingAddress');
	    return $this->shippingAddress;
	}
	/**
	 * Setter for shippingAddress
	 *
	 * @param Address $value The shippingAddress
	 *
	 * @return Customer
	 */
	public function setShippingAddress($value) 
	{
	    $this->shippingAddress = $value;
	    return $this;
	}
	/**
	 * Getter for isFromB2B
	 *
	 * @return bool
	 */
	public function getIsFromB2B()
	{
		return (trim($this->isFromB2B) === '1');
	}
	/**
	 * Setter for isFromB2B
	 *
	 * @param unkown $value The isFromB2B
	 *
	 * @return Order
	 */
	public function setIsFromB2B($value)
	{
		$this->isFromB2B = $value;
		return $this;
	}
	/**
	 * Getter for mageId
	 *
	 * @return 
	 */
	public function getMageId() 
	{
	    return $this->mageId;
	}
	/**
	 * Setter for mageId
	 *
	 * @param int $value The mageId
	 *
	 * @return Customer
	 */
	public function setMageId($value) 
	{
	    $this->mageId = $value;
	    return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::preSave()
	 */
	public function preSave()
	{
		if(!$this->getShippingAddress() instanceof Address)
			$this->setShippingAddress($this->getBillingAddress());
	}
	/**
	 * Creating a instance of this
	 * 
	 * @param string  $name
	 * @param string  $contactNo
	 * @param string  $email
	 * @param Address $billingAddr
	 * @param bool    $isFromB2B    Whether this is imported via B2B
	 * @param string  $description  The description of this customer
	 * @param Address $shippingAddr The shiping address
	 * @param int     $mageId       The id of the customer in Magento
	 * 
	 * @return Ambigous <GenericDAO, BaseEntityAbstract>
	 */
	public static function create($name, $contactNo, $email, Address $billingAddr, $isFromB2B = false, $description = '', Address $shippingAddr = null, $mageId = 0)
	{
		$name = trim($name);
		$contactNo = trim($contactNo);
		$email = trim($email);
		$isFromB2B = ($isFromB2B === true);
		$class =__CLASS__;
		$objects = self::getAllByCriteria('email = ?', array($email), true, 1, 1);
		if(count($objects) > 0 && $email !== '')
			$obj = $objects[0];
		else
		{
			$obj = new $class();
			$obj->setIsFromB2B($isFromB2B);
		}
		$obj->setName($name)
			->setDescription(trim($description))
			->setContactNo($contactNo)
			->setEmail($email)
			->setBillingAddress($billingAddr)
			->setShippingAddress($shippingAddr)
			->setMageId($mageId)
			->save();
		$comments = 'Customer(ID=' . $obj->getId() . ')' . (count($objects) > 0 ? 'updated' : 'created') . ' via B2B with (name=' . $name . ', contactNo=' . $contactNo . ', email=' . $email .')';
		if($isFromB2B === true)
			Comments::addComments($obj, $comments, Comments::TYPE_SYSTEM);
		Log::LogEntity($obj, $comments, Log::TYPE_SYSTEM, '', $class . '::' . __FUNCTION__);
		return $obj;
	}
	/**
	 * Getting all the orders for a customer
	 * 
	 * @param int   $pageNo
	 * @param int   $pageSize
	 * @param array $orderBy
	 * 
	 * @return multitype:|Ambigous <multitype:, multitype:BaseEntityAbstract>
	 */
	public function getOrders($pageNo = null, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE, $orderBy = array(), &$stats = array())
	{
		if(($id = trim($this->getId())) === '')
			return array();
		return self::getAllByCriteria('customerId = ?', array($id), true, $pageNo, $pageSize, $orderBy, $stats);
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
			$array['address']['shipping'] = $this->getShippingAddress() instanceof Address ? $this->getShippingAddress()->getJson() : array();
			$array['address']['billing'] = $this->getBillingAddress() instanceof Address ? $this->getBillingAddress()->getJson() : array();
		}
		return parent::getJson($array, $reset);
	}
	/**
	 * (non-PHPdoc)
	 * @see HydraEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'cust');
	
		DaoMap::setStringType('name', 'varchar', 100);
		DaoMap::setStringType('description', 'varchar', 255);
		DaoMap::setStringType('contactNo', 'varchar', 50);
		DaoMap::setStringType('email', 'varchar', 100);
		DaoMap::setManyToOne('billingAddress', 'Address');
		DaoMap::setManyToOne('shippingAddress', 'Address');
		DaoMap::setIntType('mageId');
		DaoMap::setBoolType('isFromB2B');
		parent::__loadDaoMap();
		
		DaoMap::createIndex('name');
		DaoMap::createIndex('contactNo');
		DaoMap::createIndex('email');
		DaoMap::createIndex('isFromB2B');
		DaoMap::createIndex('mageId');
	
		DaoMap::commit();
	}
}