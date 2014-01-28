<?php
/**
 * Entity for Product
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class Product extends InfoEntityAbstract
{
	/**
	 * The default qty of the product
	 * 
	 * @var int
	 */
	const DEFAULT_QTY = 1;
	/**
	 * The sku of the product
	 * 
	 * @var string
	 */
	private $sku;
	/**
	 * The name of the product
	 * 
	 * @var string
	 */
	private $name;
	/**
	 * Getter for sku
	 *
	 * @return string
	 */
	public function getSku() 
	{
	    return $this->sku;
	}
	/**
	 * Setter for sku
	 *
	 * @param string $value The sku
	 *
	 * @return Product
	 */
	public function setSku($value) 
	{
	    $this->sku = $value;
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
	 * @return Product
	 */
	public function setName($value) 
	{
	    $this->name = $value;
	    return $this;
	}
	/**
	 * Creating the product based on sku
	 * 
	 * @param string $sku
	 * @param string $name
	 * 
	 * @return Ambigous <Product, Ambigous, NULL, BaseEntityAbstract>
	 */
	public static function create($sku, $name)
	{
		if(!($product = self::get($sku)) instanceof Product)
			$product = new Product();
		$product->setSku($sku)
			->setName($name);
		FactoryAbastract::dao(get_called_class())->save($product);
		return $product;
	}
	/**
	 * Getting the product via sku
	 * 
	 * @param string $sku The sku of the product
	 * 
	 * @return Ambigous <NULL, BaseEntityAbstract>
	 */
	public static function get($sku)
	{
		$products = FactoryAbastract::dao(get_called_class())->findByCriteria('sku = ? ', array($sku), false, 1, 1);
		return (count($products) === 0 ? null : $products[0]);
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
		DaoMap::begin($this, 'pro');
		DaoMap::setStringType('sku');
		DaoMap::setStringType('name', 'varchar', 255);
		DaoMap::setIntType('qty', 'int', 10, false);
		parent::__loadDaoMap();
		
		DaoMap::createUniqueIndex('sku');
		DaoMap::createIndex('name');
		DaoMap::commit();
	}
}