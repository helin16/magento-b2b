<?php
/**
 * Entity for PriceMatchRecord
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class PriceMatchRecord extends BaseEntityAbstract
{
	/**
	 * The min for PriceMatchRecord
	 * 
	 * @var PriceMatchMin
	 */
	protected $min;
	/**
	 * The company for PriceMatchRecord
	 * 
	 * @var PriceMatchCompany
	 */
	protected $company;
	/**
	 * The url of the PriceMatchRecord
	 * 
	 * @var string
	 */
	private $url;
	/**
	 * The price of the PriceMatchRecord
	 * 
	 * @var double
	 */
	private $price;
	/**
	 * The name of the PriceMatchRecord
	 * 
	 * @var string
	 */
	private $name;
	
	/**
	 * Getter for min
	 *
	 * @return PriceMatchMin
	 */
	public function getMin()
	{
		$this->loadManyToOne('min');
	    return $this->min;
	}
	/**
	 * Setter for min
	 *
	 * @param PriceMatchMin $value The min
	 *
	 * @return PriceMatchRecord
	 */
	public function setMin(PriceMatchMin $value)
	{
	    $this->min= $value;
	    return $this;
	}
	/**
	 * Getter for company
	 *
	 * @return PriceMatchCompany
	 */
	public function getCompany()
	{
		$this->loadManyToOne('company');
	    return $this->company;
	}
	/**
	 * Setter for company
	 *
	 * @param PriceMatchCompany $value The company
	 *
	 * @return PriceMatchRecord
	 */
	public function setCompany(PriceMatchCompany $value)
	{
	    $this->company = $value;
	    return $this;
	}
	/**
	 * Getter for url
	 *
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}
	/**
	 * Setter for url
	 *
	 * @param string $value The url
	 *
	 * @return PriceMatchRecord
	 */
	public function setUrl($value)
	{
		$this->url = $value;
		return $this;
	}
	/**
	 * Getter for price
	 *
	 * @return double
	 */
	public function getPrice()
	{
		return $this->price;
	}
	/**
	 * Setter for price
	 *
	 * @param double $value The price
	 *
	 * @return PriceMatchRecord
	 */
	public function setPrice($value)
	{
		$this->price = $value;
		return $this;
	}
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
	 * @return PriceMatchRecord
	 */
	public function setName($value)
	{
		$this->name = $value;
		return $this;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'price_match_record');
		DaoMap::setManyToOne('min', 'PriceMatchMin', 'price_match_record_min');
		DaoMap::setManyToOne('company', 'PriceMatchCompany', 'price_match_record_company');
		DaoMap::setStringType('url', 'varchar', 255);
		DaoMap::setIntType('price', 'double', '10,4');
		DaoMap::setStringType('name', 'varchar', 100);
		parent::__loadDaoMap();
		
		DaoMap::createIndex('price');
		DaoMap::commit();
	}
	
	/**
	 * create for PriceMatchRecord
	 * 
	 * @param PriceMatchCompany $company
	 * @param PriceMatchMin $min
	 * @param string $price
	 * @param string $url
	 * @param string $name
	 * @throws Exception
	 */
	public static function create(PriceMatchCompany $company, PriceMatchMin $min, $price, $url = '', $name = '')
	{
		if(abs(doubleval($price)) === 0.0 || doubleval($price) < 0.0 || trim($price) === '')
			throw new Exception('price must be positive, "' . $price . '" given');
		$price = doubleval($price);
		
		$from_date = UDate::now('Australia/Melbourne')->setTime(0, 0, 0)->setTimeZone('UTC');
		$to_date = UDate::now('Australia/Melbourne')->setTime(23, 59, 59)->setTimeZone('UTC');
		if(count($i = self::getAllByCriteria('companyId = ? and minId = ? and created >= ? and created <= ?', array($company->getId(), $min->getId(), $from_date, $to_date), true, 1, 1, array('id'=> 'desc'))) > 0)
			$entity = $i[0];
		else $entity = new self;
		$entity->setCompany($company)->setMin($min)->setPrice($price)->setUrl(trim($url))->setName(trim($name))->save();
		
		return $entity;
	}
}