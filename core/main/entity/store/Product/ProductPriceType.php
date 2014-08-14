<?php
/**
 * Entity for ProductPriceType
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class ProductPriceType extends BaseEntityAbstract
{
	const ID_RRP = 1;
	const ID_CASUAL_SPECIAL = 2;
	CONST ID_SPECIAL_GROUP_1 = 3;
	CONST ID_SPECIAL_GROUP_2 = 4;
	/**
	 * The name of the product
	 * 
	 * @var string
	 */
	private $name;
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
	 * Creating the product based on sku
	 * 
	 * @param string $name        The name of the productpricetype
	 * @param string $description The decription of the productpricetype
	 * 
	 * @return Ambigous <Product, Ambigous, NULL, BaseEntityAbstract>
	 */
	public static function create($name, $description = '')
	{
		$class = __CLASS__;
		$obj = new $class();
		$obj->setName(trim($name))
			->setDescription(trim($description));
		return FactoryAbastract::dao($class)->save($obj);
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
		DaoMap::begin($this, 'pro_price_type');
		DaoMap::setStringType('name', 'varchar', 100);
		DaoMap::setStringType('description', 'varchar', 255);
		parent::__loadDaoMap();
		
		DaoMap::createUniqueIndex('name');
		DaoMap::commit();
	}
}