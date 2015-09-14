<?php
/**
 * Entity for ProductAttribute
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class ProductAttribute extends BaseEntityAbstract
{
	/**
	 * The magento attribute set id of this ProductAttribute from magento
	 *
	 * @var int
	 */
	private $attributeSetMageId;
	/**
	 * The code of this ProductAttribute from magento
	 *
	 * @var string
	 */
	private $code;
	/**
	 * The type of this ProductAttribute from magento
	 *
	 * @var string
	 */
	private $type;
	/**
	 * The required of this ProductAttribute from magento
	 *
	 * @var bool
	 */
	private $required;
	/**
	 * The scope of this ProductAttribute from magento
	 *
	 * @var string
	 */
	private $scope;
	/**
	 * The description of this ProductAttribute
	 *
	 * @var string
	 */
	private $description = '';
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
	 * getter for attributeSetMageId
	 *
	 * @return int
	 */
	public function getAttributeSetMageId()
	{
	    return $this->attributeSetMageId;
	}
	/**
	 * Setter for attributeSetMageId
	 *
	 * @return ProductAttribute
	 */
	public function setAttributeSetMageId($attributeSetMageId)
	{
	    $this->attributeSetMageId = $attributeSetMageId;
	    return $this;
	}
	/**
	 * getter for code
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
	 * @return ProductAttribute
	 */
	public function setCode($code)
	{
	    $this->code = $code;
	    return $this;
	}
	/**
	 * getter for type
	 *
	 * @return string
	 */
	public function getType()
	{
	    return $this->type;
	}
	/**
	 * Setter for type
	 *
	 * @return ProductAttribute
	 */
	public function setType($type)
	{
	    $this->type = $type;
	    return $this;
	}
	/**
	 * getter for required
	 *
	 * @return bool
	 */
	public function getRequired()
	{
	    return $this->required;
	}
	/**
	 * Setter for required
	 *
	 * @return ProductAttribute
	 */
	public function setRequired($required)
	{
	    $this->required = $required;
	    return $this;
	}
	/**
	 * getter for scope
	 *
	 * @return string
	 */
	public function getScope()
	{
	    return $this->scope;
	}
	/**
	 * Setter for scope
	 *
	 * @return ProductAttribute
	 */
	public function setScope($scope)
	{
	    $this->scope = $scope;
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
	 * @return ProductAttribute
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
	 * @return ProductAttribute
	 */
	public function setIsFromB2B($value)
	{
		$this->isFromB2B = $value;
		return $this;
	}
	/**
	 * Getter for mageId
	 *
	 * @return ProductAttribute
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
	 * @return ProductAttribute
	 */
	public function setMageId($value)
	{
		$this->mageId = $value;
		return $this;
	}
	/**
	 * Creating a instance of this
	 * 
	 * @param string $code
	 * @param string $type
	 * @param bool $required
	 * @param string $scope
	 * @param string $description
	 * @param string $isFromB2B
	 * @param number $mageId
	 * @return ProductAttribute
	 */
	public static function create($code, $type, $required, $scope, $description = '', $isFromB2B = false, $mageId = 0, $attributeSetMageId = 0)
	{
		$code = trim($code);
		$type = trim($type);
		$required = (trim($required) === '1' || $required === true || trim($required) === 'true') ? true : false;
		$scope = trim($scope);
		$description = trim($description);
		$isFromB2B = ($isFromB2B === true);
		$class =__CLASS__;
		$objects = self::getAllByCriteria('code = ?', array($code), true, 1, 1);
		if(count($objects) > 0)
			$obj = $objects[0];
		else
		{
			$obj = new $class();
			$obj->setIsFromB2B($isFromB2B);
		}
		$obj->setCode($code)
			->setType($type)
			->setRequired($required)
			->setScope($scope)
			->setDescription(trim($description))
			->setMageId($mageId)
			->setAttributeSetMageId($attributeSetMageId)
			->save();
		$comments = $class . '(ID=' . $obj->getId() . ')' . (count($objects) > 0 ? 'updated' : 'created') . ($isFromB2B === true ? ' via B2B' : '') . ' with (code=' . $code . ', type="' . $type . '", required="' . $required . '", scope="' . $scope . ', mageId=' . $mageId . ', attributeSetMageId=' . $attributeSetMageId . ')';
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
		DaoMap::begin($this, 'pro_att');
	
		DaoMap::setIntType('attributeSetMageId');
		DaoMap::setStringType('code', 'varchar', 100);
		DaoMap::setStringType('type', 'varchar', 100);
		DaoMap::setBoolType('required');
		DaoMap::setStringType('scope', 'varchar', 100);
		DaoMap::setStringType('description', 'varchar', 255);
		DaoMap::setIntType('mageId');
		DaoMap::setBoolType('isFromB2B');
		parent::__loadDaoMap();
	
		DaoMap::createUniqueIndex('code');
		DaoMap::createIndex('attributeSetMageId');
		DaoMap::createIndex('isFromB2B');
		DaoMap::createIndex('mageId');
	
		DaoMap::commit();
	}
	/**
	* Getting the product attribute by mage id
	*
	* @param string $mageId
	*
	* @return Ambigous <NULL, ProductAttribute>
	*/
	public static function getByMageId($mageId)
	{
		$entities = self::getAllByCriteria('mageId = ?', array(trim($mageId)), false, 1, 1);
		return count($entities) === 0 ? null : $entities[0];
	}
}