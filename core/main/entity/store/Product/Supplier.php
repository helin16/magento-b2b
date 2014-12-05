<?php
/**
 * Entity for Supplier
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class Supplier extends BaseEntityAbstract
{
	/**
	 * The name of this Manufacturer
	 *
	 * @var string
	 */
	private $name;
	/**
	 * The description of this Manufacturer
	 *
	 * @var string
	 */
	private $description = '';
	/**
	 * The contact person's name
	 * 
	 * @var string
	 */
	private $contactName = ''; 
	/**
	 * The contact number
	 * 
	 * @var string
	 */
	private $contactNo = '';
	/**
	 * The email address of the supplier
	 * 
	 * @var string
	 */
	private $email = '';
	/**
	 * The address
	 * 
	 * @var Address
	 */
	protected $address = null;
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
	 * Getter for contactName
	 * 
	 * @return string
	 */
	public function getContactName ()
	{
		return $this->contactName;
	}
	/** 
	 * Setter for contactName
	 * 
	 * @param string $value
	 * 
	 * @return Supplier
	 */
	public function setContactName($value)
	{
		$this->contactName = $value;
		return $this;
	}
	/** 
	 * Getter for contactNo
	 * 
	 * @return string
	 */
	public function getContactNo ()
	{
		return $this->contactNo;
	}
	/** 
	 * Setter for contactNo
	 * 
	 * @param string $value
	 * 
	 * @return Supplier
	 */
	public function setContactNo($value)
	{
		$this->contactNo = $value;
		return $this;
	}
	/** 
	 * Getter for contactNo
	 * 
	 * @return string
	 */
	public function getEmail ()
	{
		return $this->email;
	}
	/** 
	 * Setter for email
	 * 
	 * @param string $value
	 * 
	 * @return Supplier
	 */
	public function setEmail($value)
	{
		$this->email = $value;
		return $this;
	}
	/** 
	 * Getter for address
	 * 
	 * @return Address
	 */
	public function getAddress ()
	{
		$this->loadManyToOne('address');
		return $this->address;
	}
	/** 
	 * Setter for address
	 * 
	 * @param Address $value
	 * 
	 * @return Supplier
	 */
	public function setAddress(Address $value = null)
	{
		$this->address = $value;
		return $this;
	}
	/**
	 * Creating a instance of this
	 *
	 * @param string  $name
	 * @param string  $description  The description of this customer
	 * @param bool    $isFromB2B    Whether this is imported via B2B
	 * @param int     $mageId       The id of the customer in Magento
	 *
	 * @return Ambigous <GenericDAO, BaseEntityAbstract>
	 */
	public static function create($name, $description = '', $isFromB2B = false, $mageId = 0, $contactName = '', $contactNo = '', Address $addr = null)
	{
		$name = trim($name);
		$description = trim($description);
		$isFromB2B = ($isFromB2B === true);
		$class =__CLASS__;
		$objects = self::getAllByCriteria('name = ?', array($name), true, 1, 1);
		if(count($objects) > 0 && $name !== '')
			$obj = $objects[0];
		else
		{
			$obj = new $class();
			$obj->setIsFromB2B($isFromB2B)
				->setContactName(trim($contactName))
				->setContactNo(trim($contactNo))
				->setAddress($addr);
		}
		$obj->setName($name)
			->setDescription(trim($description))
			->setMageId($mageId)
			->save();
		$comments = $class  . '(ID=' . $obj->getId() . ')' . (count($objects) > 0 ? 'updated' : 'created') . ($isFromB2B === true ? ' via B2B' : '') . ' with (name=' . $name . ', mageId=' . $mageId . ')';
		if($isFromB2B === true)
			Comments::addComments($obj, $comments, Comments::TYPE_SYSTEM);
		Log::LogEntity($obj, $comments, Log::TYPE_SYSTEM, '', $class . '::' . __FUNCTION__);
		return $obj;
	}
	/**
	 * (non-PHPdoc)
	 * @see HydraEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'sup');
	
		DaoMap::setStringType('name', 'varchar', 100);
		DaoMap::setStringType('description', 'varchar', 255);
		DaoMap::setStringType('contactName', 'varchar', 100);
		DaoMap::setStringType('contactNo', 'varchar', 100);
		DaoMap::setStringType('email', 'varchar', 100);
		DaoMap::setManyToOne('address', 'Address', 'sup_addr', true);
		DaoMap::setIntType('mageId');
		DaoMap::setBoolType('isFromB2B');
		parent::__loadDaoMap();
	
		DaoMap::createIndex('name');
		DaoMap::createIndex('isFromB2B');
		DaoMap::createIndex('mageId');
		DaoMap::createIndex('contactNo');
		DaoMap::createIndex('contactName');
		DaoMap::createIndex('email');
	
		DaoMap::commit();
	}
}