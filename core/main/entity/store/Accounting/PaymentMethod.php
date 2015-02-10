<?php
/**
 * Entity for PaymentMethod
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class PaymentMethod extends BaseEntityAbstract
{
	/**
	 * The name of the payment method
	 * 
	 * @var string
	 */
	private $name;
	/**
	 * The description of the payment method
	 * 
	 * @var string
	 */
	private $description;
	/**
	 * The cache
	 * 
	 * @var array
	 */
	private static $_cache;
	/**
	 * Getting the PaymentMethod
	 * 
	 * @param int $id The id of the paymentmethod
	 * 
	 * @return PaymentMethod|null
	 */
	public static function getFromCache($id)
	{
		if(!isset(self::$_cache[$id]))
		{
			$entityClassName = trim(get_called_class());
			self::$_cache[$id] = self::get($id);
		}
		return self::$_cache[$id];
	}
	/**
	 * Getter for name
	 *
	 * @return PaymentMethod
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
	 * @return PaymentMethod
	 */
	public function setName($value) 
	{
	    $this->name = $value;
	    return $this;
	}
	/**
	 * Getter for description
	 *
	 * @return PaymentMethod
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
	 * @return PaymentMethod
	 */
	public function setDescription($value) 
	{
	    $this->description = $value;
	    return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::__toString()
	 */
	public function __toString()
	{
		return trim($this->getName());
	}
	
	/**
	 * get all PaymentMethods
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
	 * @see HydraEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'py_method');
	
		DaoMap::setStringType('name', 'varchar', 100);
		DaoMap::setStringType('description', 'varchar', 255);
		parent::__loadDaoMap();
	
		DaoMap::createUniqueIndex('name');
		DaoMap::commit();
	}
	/**
	 * Getting payment methods from the name
	 * 
	 * @param string $name
	 * 
	 * @return Ambigous <NULL, BaseEntityAbstract>
	 */
	public static function getByName($name)
	{
		$entities = self::getAllByCriteria('name = ?', array(trim($name)), true, 1,1);
		return count($entities) > 0 ? $entities[0] : null;
	}
	/**
	 * Creating the PaymentMethod
	 * 
	 * @param string $name
	 * @param string $description
	 * 
	 * @return PaymentMethod
	 */
	public static function create($name, $description = '')
	{
		if(($entity = self::getByName($name)) instanceof PaymentMethod)
			throw new Exception('The payment method already exsits: ' . $name);
		$entity = new PaymentMethod();
		return $entity->setName(trim($name))
			->setDescription(trim($description))
			->save();
	}
}