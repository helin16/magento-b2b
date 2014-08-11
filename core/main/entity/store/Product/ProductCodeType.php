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
	 * @param string $mageProductId The magento id of the product
	 * @param int    $stockOnHand   The total quantity on hand for this product
	 * @param int    $stockOnOrder  The total quantity on order from supplier for this product
	 * @param bool   $isFromB2B     Whether this product is created via B2B?
	 * @param string $shortDescr    The short description of the product
	 * @param string $fullDescr     The assetId of the full description asset of the product
	 * 
	 * @return Ambigous <Product, Ambigous, NULL, BaseEntityAbstract>
	 */
	public static function create($name, $description = '')
	{
		$class = __CLASS__;
		$obj = new $class();
		$obj->setName(trim($name))
			->setDescription(trim($description));
		FactoryAbastract::dao($class)->save($obj);
		return $obj;
	}
	/**
	 * Getting the type via id
	 * 
	 * @param string $sku The sku of the product
	 * 
	 * @return Ambigous <NULL, BaseEntityAbstract>
	 */
	public static function get($id)
	{
		return FactoryAbastract::dao(get_called_class())->findById($id);
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