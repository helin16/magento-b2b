<?php
/** Address Entity
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class Address extends BaseEntityAbstract
{
	/**
	 * The contact name of the address
	 * 
	 * @var string
	 */
	private $contactName = '';
	/**
	 * The contact name of the address
	 * 
	 * @var string
	 */
	private $contactNo = '';
	/**
	 * The street of the address
	 * 
	 * @var string
	 */
	private $street;
	/**
	 * The city name of the address
	 * 
	 * @var string
	 */
	private $city;
	/**
	 * The contact name of the address
	 * 
	 * @var string
	 */
	private $region;
	/**
	 * The postCode of the address
	 * 
	 * @var string
	 */
	private $postCode;
	/**
	 * The country of the address
	 * 
	 * @var string
	 */
	private $country;
	/**
	 * screct key
	 * 
	 * @var string
	 */
	private $sKey;
	/**
	 * Getter for contactName
	 *
	 * @return string
	 */
	public function getContactName() 
	{
	    return $this->contactName;
	}
	/**
	 * Setter for contactName
	 *
	 * @param string $value The contactName
	 *
	 * @return Address
	 */
	public function setContactName($value) 
	{
	    $this->contactName = $value;
	    return $this;
	}
	/**
	 * Getter for sKey
	 *
	 * @return string
	 */
	public function getSKey() 
	{
	    return $this->sKey;
	}
	/**
	 * Setter for sKey
	 *
	 * @param string $value The sKey
	 *
	 * @return Address
	 */
	public function setSKey($value) 
	{
	    $this->sKey = $value;
	    return $this;
	}
	/**
	 * Getter for contactNo
	 *
	 * @return string
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
	 * @return Address
	 */
	public function setContactNo($value) 
	{
	    $this->contactNo = $value;
	    return $this;
	}
	/**
	 * Getter for street
	 *
	 * @return string
	 */
	public function getStreet() 
	{
	    return $this->street;
	}
	/**
	 * Setter for street
	 *
	 * @param string $value The street
	 *
	 * @return Address
	 */
	public function setStreet($value) 
	{
	    $this->street = $value;
	    return $this;
	}
	/**
	 * Getter for city
	 *
	 * @return string
	 */
	public function getCity() 
	{
	    return $this->city;
	}
	/**
	 * Setter for city
	 *
	 * @param string $value The city
	 *
	 * @return Address
	 */
	public function setCity($value) 
	{
	    $this->city = $value;
	    return $this;
	}
	/**
	 * Getter for region
	 *
	 * @return string
	 */
	public function getRegion() 
	{
	    return $this->region;
	}
	/**
	 * Setter for region
	 *
	 * @param string $value The region
	 *
	 * @return Address
	 */
	public function setRegion($value) 
	{
	    $this->region = $value;
	    return $this;
	}
	/**
	 * Getter for country
	 *
	 * @return 
	 */
	public function getCountry() 
	{
	    return $this->country;
	}
	/**
	 * Setter for country
	 *
	 * @param string $value The country
	 *
	 * @return Address
	 */
	public function setCountry($value) 
	{
	    $this->country = $value;
	    return $this;
	}
	/**
	 * Getter for postCode
	 *
	 * @return string
	 */
	public function getPostCode() 
	{
	    return $this->postCode;
	}
	/**
	 * Setter for postCode
	 *
	 * @param string $value The postCode
	 *
	 * @return Address
	 */
	public function setPostCode($value) 
	{
	    $this->postCode = $value;
	    return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::__toString()
	 */
	public function __toString()
	{
		return trim($this->getStreet() . ', ' . $this->getCity() . ' ' . $this->getRegion() . ' ' . $this->getCountry() . ' ' . $this->getPostCode() );
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::preSave()
	 */
	public function preSave()
	{
		if(trim($this->getSKey()) === '')
			$this->setSKey(self::genSKey($this->getStreet(), $this->getCity(), $this->getRegion(), $this->getCountry(), $this->getPostCode(), $this->getContactName(), $this->getContactNo()));
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
			$array['full'] = trim($this);
		}
		return parent::getJson($array, $reset);
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'addr');
	
		DaoMap::setStringType('contactName','varchar', 50);
		DaoMap::setStringType('contactNo','varchar', 50);
		DaoMap::setStringType('street','varchar', 50);
		DaoMap::setStringType('city','varchar', 20);
		DaoMap::setStringType('region','varchar', 20);
		DaoMap::setStringType('country','varchar', 20);
		DaoMap::setStringType('postCode','varchar', 10);
		DaoMap::setStringType('sKey','varchar', 32);
	
		parent::__loadDaoMap();
	
		DaoMap::createIndex('sKey');
		DaoMap::createIndex('contactName');
		DaoMap::createIndex('contactNo');
		DaoMap::createIndex('city');
		DaoMap::createIndex('region');
		DaoMap::createIndex('country');
		DaoMap::createIndex('postCode');
	
		DaoMap::commit();
	}
	/**
	 * Creating a address object
	 *
	 * @param string $street       The street line of the address
	 * @param string $city         The city of the address
	 * @param string $region       The region/state of the address
	 * @param string $country      The country of the address
	 * @param string $postCode     The postCode of the address
	 * @param string $contactName  The contact name of the address
	 * @param string $contactNo    The contact no of the address
	 *
	 * @return Address
	 */
	public static function create($street, $city, $region, $country, $postCode, $contactName = '', $contactNo = '', Address &$exsitAddr = null)
	{
		$obj = new Address();
		if($exsitAddr instanceof Address)
			$obj = $exsitAddr;
		else if(count($sameAddresses = self::getAllByCriteria('`sKey` = ?', array(self::genSKey($street, $city, $region, $country, $postCode, $contactName, $contactNo)), true, 1, 1)) > 0)
			return $sameAddresses[0];
		return $obj->setStreet($street)
			->setCity($city)
			->setRegion($region)
			->setCountry($country)
			->setPostCode($postCode)
			->setContactName($contactName)
			->setContactNo($contactNo)
			->save();
	}
	/**
	 * Generating the skey for address
	 * 
	 * @param string $street
	 * @param string $city
	 * @param string $region
	 * @param string $country
	 * @param string $postCode
	 * @param string $contactName
	 * @param string $contactNo
	 * 
	 * @return string
	 */
	public static function genSKey($street, $city, $region, $country, $postCode, $contactName = '', $contactNo = '')
	{
		return md5(trim(trim($street) . '|' . trim($city) . '|' . trim($region) . '|' . trim($country) . '|' . trim($postCode) . '|' . trim($contactName) . '|' . trim($contactNo)));
	}
}