<?php
/**
 * Entity for Courier
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class Courier extends InfoEntityAbstract
{
	const ID_LOCAL_PICKUP = 5;
	/**
	 * The name of the courier
	 * 
	 * @var string
	 */
	private $name;
	/**
	 * The connector script's name
	 * 
	 * @var string
	 */
	private $connector;
	/**
	 * The courier code for magento to use
	 * 
	 * @var string
	 */
	private $code = 'custom';
	/**
	 * The shippment of the courier
	 * 
	 * @var Multiple:Shippment
	 */
	protected $shippments;
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
	 * @return Courier
	 */
	public function setName($value) 
	{
	    $this->name = $value;
	    return $this;
	}
	/**
	 * Getter for connector
	 *
	 * @return string
	 */
	public function getConnector() 
	{
		if(!class_exists($this->connector))
			throw new CoreException("System Error: " . $this->connector . " does NOT exsits!");
	    return $this->connector;
	}
	/**
	 * Setter for connector
	 *
	 * @param string $value The connector
	 *
	 * @return Courier
	 */
	public function setConnector($value) 
	{
	    $this->connector = $value;
	    return $this;
	}
	/**
	 * Getter for shippments
	 *
	 * @return Multiple:Shippment
	 */
	public function getShippments() 
	{
		$this->loadOneToMany('shippments');
	    return $this->shippments;
	}
	/**
	 * Setter for shippments
	 *
	 * @param array $value The shippments
	 *
	 * @return Courier
	 */
	public function setShippments(array $value) 
	{
	    $this->shippments = $value;
	    return $this;
	}
	/**
	 * Getter for code
	 *
	 * @return string
	 */
	public function getCode() 
	{
	    return $this->code;
	}
	/**
	 * Setter for code
	 *
	 * @param string $value The code
	 *
	 * @return Courier
	 */
	public function setCode($value) 
	{
	    $this->code = $value;
	    return $this;
	}
	/**
	 * get all Couriers
	 *
	 * @param bool  $searchActiveOnly
	 * @param int   $pageNo
	 * @param int   $pageSize
	 * @param array $orderBy
	 *
	 * @return Ambigous <multitype:, multitype:BaseEntityAbstract >
	 */
	public static function findAll($searchActiveOnly = true, $pageNo = null, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE, $orderBy = array(), &$stats = array())
	{
		return self::getAll($searchActiveOnly, $pageNo, $pageSize, $orderBy, $stats);
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'courier');
		DaoMap::setStringType('name');
		DaoMap::setStringType('connector');
		DaoMap::setStringType('code');
		DaoMap::setOneToMany('shippments', 'Shippment', 'c_shippments');
		parent::__loadDaoMap();
		
		DaoMap::createIndex('name');
		DaoMap::createIndex('code');
		DaoMap::commit();
	}
	/**
	 * Creating a courier
	 * 
	 * @param string $name
	 * @param string $connector
	 * @param string $code
	 * 
	 * @return Courier
	 */
	public static function create($name, $connector = 'CourierConn_Manual', $code = 'custom')
	{
		$item = new Courier();
		return $item->setName(trim($name))
			->setConnector(trim($connector))
			->setCode(trim($code))
			->save();
	}
}