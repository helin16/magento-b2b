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
	 * The quantity that we are ordering from supplier
	 * 
	 * @var int
	 */
	private $stockOnOrder = 0;
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
	 * The short description
	 * 
	 * @var string
	 */
	private $shortDescription = '';
	/**
	 * The asset id of the full description
	 * 
	 * @var string
	 */
	private $fullDescAssetId = '';
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
	 * Getter for stockOnOrder
	 *
	 * @return 
	 */
	public function getStockOnOrder() 
	{
	    return $this->stockOnOrder;
	}
	/**
	 * Setter for stockOnOrder
	 *
	 * @param double $value The stockOnOrder
	 *
	 * @return Product
	 */
	public function setStockOnOrder($value) 
	{
	    $this->stockOnOrder = $value;
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
	 * Getter for shortDescription
	 *
	 * @return 
	 */
	public function getShortDescription() 
	{
	    return $this->shortDescription;
	}
	/**
	 * Setter for shortDescription
	 *
	 * @param string $value The shortDescription
	 *
	 * @return Product
	 */
	public function setShortDescription($value) 
	{
	    $this->shortDescription = $value;
	    return $this;
	}
	/**
	 * Getter for fullDescAssetId
	 *
	 * @return 
	 */
	public function getFullDescAssetId() 
	{
	    return $this->fullDescAssetId;
	}
	/**
	 * Setter for fullDescAssetId
	 *
	 * @param string $value The fullDescAssetId
	 *
	 * @return Product
	 */
	public function setFullDescAssetId($value) 
	{
	    $this->fullDescAssetId = $value;
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
	public static function create($sku, $name, $mageProductId = '', $stockOnHand = null, $stockOnOrder = null, $isFromB2B = false, $shortDescr = '', $fullDescr = '')
	{
		if(!($product = self::get($sku)) instanceof Product)
			$product = new Product();
		$product->setSku(trim($sku))
			->setName($name);
		if(($mageProductId = trim($mageProductId)) !== "")
			$product->setMageId($mageProductId);
		
		if(trim($product->getId()) === '')
		{
			$product->setIsFromB2B($isFromB2B)
				->setShortDescription($shortDescr);
			if($stockOnOrder !== null && is_numeric($stockOnOrder))
				$product->setStockOnOrder(intval($stockOnOrder));
			if($stockOnHand !== null && is_numeric($stockOnHand))
				$product->setStockOnHand(intval($stockOnHand));
			if (($$fullDescr = trim($fullDescr)) !== '')
			{
				$asset = Asset::registerAsset('full_desc_' . $sku, $fullDescr);
				$product->setFullDescAssetId(trim($asset->getAssetId()));
			}
		}
		FactoryAbastract::dao(get_called_class())->save($product);
		return $product;
	}
	/**
	 * Adding a product image to the product
	 * 
	 * @param Asset $asset The asset object that reprents the image
	 * 
	 * @return Product
	 */
	public function addImage(Asset $asset)
	{
		ProductImage::create($this, $asset);
		return $this;
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
		DaoMap::setStringType('sku', 'varchar', 50);
		DaoMap::setStringType('name', 'varchar', 100);
		DaoMap::setStringType('mageId', 'varchar', 10);
		DaoMap::setIntType('stockOnHand');
		DaoMap::setIntType('stockOnOrder');
		DaoMap::setBoolType('isFromB2B');
		DaoMap::setStringType('shortDescription', 'varchar', 255);
		DaoMap::setStringType('fullDescAssetId', 'varchar', 100);
		parent::__loadDaoMap();
		
		DaoMap::createUniqueIndex('sku');
		DaoMap::createIndex('name');
		DaoMap::createIndex('mageId');
		DaoMap::createIndex('stockOnHand');
		DaoMap::createIndex('stockOnOrder');
		DaoMap::createIndex('isFromB2B');
		DaoMap::createIndex('shortDescription');
		DaoMap::createIndex('fullDescAssetId');
		DaoMap::commit();
	}
}