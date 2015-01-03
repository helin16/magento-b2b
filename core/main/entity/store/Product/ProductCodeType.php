<?php
/**
 * Entity for ProductCodeType
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class ProductCodeType extends BaseEntityAbstract
{
	const ID_UPC = 1;
	const ID_EAN = 2;
	const ID_MYOB = 3;
	/**
	 * The name of the product
	 * 
	 * @var string
	 */
	private $name;
	/**
	 * Whether the product can have multiple of this code
	 * 
	 * @var bool
	 */
	private $allowMultiple;
	/**
	 * The Description of the type
	 * 
	 * @var string
	 */
	private $description = '';
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
	 * @return Product
	 */
	public function setName($value) 
	{
	    $this->name = $value;
	    return $this;
	}
	/**
	 * Getter for description
	 *
	 * @return 
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
	 * @return ProductCodeType
	 */
	public function setDescription($value) 
	{
	    $this->description = $value;
	    return $this;
	}
	/**
	 * Getter for allowMultiple
	 *
	 * @return 
	 */
	public function getAllowMultiple() 
	{
	    return trim($this->allowMultiple) === '1';
	}
	/**
	 * Setter for allowMultiple
	 *
	 * @param bool $value The allowMultiple
	 *
	 * @return ProductCodeType
	 */
	public function setAllowMultiple($value) 
	{
	    $this->allowMultiple = intval($value);
	    return $this;
	}
	/**
	 * Creating the product based on sku
	 * 
	 * @param string $sku           The sku of the product
	 * @param string $name          The name of the product
	 * @param bool   $allowMultiple Whether this product allow to have multiple code of this type
	 * 
	 * @return Ambigous <Product, Ambigous, NULL, BaseEntityAbstract>
	 */
	public static function create($name, $description = '', $allowMultiple = true)
	{
		$class = __CLASS__;
		$obj = new $class();
		$obj->setName(trim($name))
			->setDescription(trim($description))
			->setAllowMultiple($allowMultiple)
			->save();
		return $obj;
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
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'pro_code_type');
		DaoMap::setStringType('name', 'varchar', 100);
		DaoMap::setStringType('description', 'varchar', 255);
		DaoMap::setBoolType('allowMultiple');
		parent::__loadDaoMap();
		
		DaoMap::createUniqueIndex('name');
		DaoMap::createIndex('allowMultiple');
		DaoMap::createIndex('description');
		DaoMap::commit();
	}
}