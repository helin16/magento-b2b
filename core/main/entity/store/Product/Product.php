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
	 * The id of magento for this product
	 * 
	 * @var string
	 */
	private $mageId = '';
	/**
	 * The unit price for this product exclude GST
	 * 
	 * @var double
	 */
	private $price = '0.0000';
	/**
	 * The quantity we have
	 * 
	 * @var int
	 */
	private $stockOnHand = 0;
	/**
	 * Whether this order is imported from B2B
	 * 
	 * @var bool
	 */
	private $isFromB2B = false;
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
	 * @param unkown $value The mageId
	 *
	 * @return Product
	 */
	public function setMageId($value) 
	{
	    $this->mageId = $value;
	    return $this;
	}
	/**
	 * Getter for price
	 *
	 * @return 
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
	 * @return Product
	 */
	public function setPrice($value) 
	{
	    $this->price = $value;
	    return $this;
	}
	/**
	 * Getter for stockOnHand
	 *
	 * @return 
	 */
	public function getStockOnHand() 
	{
	    return $this->stockOnHand;
	}
	/**
	 * Setter for stockOnHand
	 *
	 * @param int $value The stockOnHand
	 *
	 * @return Product
	 */
	public function setStockOnHand($value) 
	{
	    $this->stockOnHand = $value;
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
	 * Creating the product based on sku
	 * 
	 * @param string $sku           The sku of the product
	 * @param string $name          The name of the product
	 * @param string $mageProductId The magento id of the product
	 * @param double $price         The unit price of the product
	 * @param int    $stockOnHand   The total quantity on hand for this product
	 * @param bool   $isFromB2B     Whether this product is created via B2B?
	 * 
	 * @return Ambigous <Product, Ambigous, NULL, BaseEntityAbstract>
	 */
	public static function create($sku, $name, $mageProductId = '', $price = null, $stockOnHand = null, $isFromB2B = false)
	{
		if(!($product = self::get($sku)) instanceof Product)
			$product = new Product();
		$product->setSku(trim($sku))
			->setName($name);
		if(($mageProductId = trim($mageProductId)) !== "")
			$product->setMageId($mageProductId);
		
		if(trim($product->getId()) === '')
		{
			$product->setIsFromB2B($isFromB2B);
			if($price !== null && is_numeric($price))
				$product->setPrice($price);
			if($stockOnHand !== null)
				$product->setStockOnHand(intval($stockOnHand));
		}
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
		$products = FactoryAbastract::dao(get_called_class())->findByCriteria('sku = ? ', array(trim($sku)), false, 1, 1);
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
		DaoMap::setStringType('sku', 'varchar', 100);
		DaoMap::setStringType('name', 'varchar', 255);
		DaoMap::setStringType('mageId', 'varchar', 20);
		DaoMap::setIntType('price', 'double', '10,4');
		DaoMap::setIntType('stockOnHand');
		DaoMap::setBoolType('isFromB2B');
		parent::__loadDaoMap();
		
		DaoMap::createUniqueIndex('sku');
		DaoMap::createIndex('name');
		DaoMap::createIndex('mageId');
		DaoMap::createIndex('price');
		DaoMap::createIndex('stockOnHand');
		DaoMap::createIndex('isFromB2B');
		DaoMap::commit();
	}
}