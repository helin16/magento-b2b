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
	 * The quantity we have
	 * 
	 * @var int
	 */
	private $stockOnPO = 0;
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
	 * Marking the product as new from which date
	 * 
	 * @var UDate|NULL
	 */
	private $asNewFromDate = null;
	/**
	 * Marking the product as new to which date
	 * 
	 * @var UDate|NULL
	 */
	private $asNewToDate = null;
	/**
	 * Whether we will sell this product on Web
	 * 
	 * @var bool
	 */
	private $sellOnWeb = false;
	/**
	 * Product status
	 * 
	 * @var ProductStatus
	 */
	protected $status = null;
	/**
	 * The manufacture /brand of this product
	 * 
	 * @var Manufacturer
	 */
	protected $manufacturer = null;
	/**
	 * the supplier codes
	 * 
	 * @var array
	 */
	protected $supplierCodes = array();
	/**
	 * The product_categories
	 * 
	 * @var array
	 */
	protected $categories = array();
	/**
	/**
	 * The productCodes
	 * 
	 * @var array
	 */
	protected $codes = array();
	/**
	 * Getter for categories
	 *
	 * @return array()
	 */
	public function getCategories() 
	{
		$this->loadOneToMany('categories');
	    return $this->categories;
	}
	/**
	 * Setter for categories
	 *
	 * @param unkown $value The categories
	 *
	 * @return Product
	 */
	public function setCategories($value) 
	{
	    $this->categories = $value;
	    return $this;
	}
	/**
	 * Getter for codes
	 *
	 * @return array()
	 */
	public function getCodes() 
	{
		$this->loadOneToMany('codes');
	    return $this->codes;
	}
	/**
	 * Setter for codes
	 *
	 * @param array $value The codes
	 *
	 * @return Product
	 */
	public function setCodes($value) 
	{
	    $this->codes = $value;
	    return $this;
	}
	/**
	 * Getter for supplierCodes
	 *
	 * @return array
	 */
	public function getSupplierCodes() 
	{
		$this->loadOneToMany('supplierCodes');
	    return $this->supplierCodes;
	}
	/**
	 * Setter for supplierCodes
	 *
	 * @param unkown $value The supplierCodes
	 *
	 * @return Product
	 */
	public function setSupplierCodes($value) 
	{
	    $this->supplierCodes = $value;
	    return $this;
	}
	/** 
	 * Getter for asNewFromDate
	 * 
	 * @return Ambigous <UDate, NULL>
	 */
	public function getAsNewFromDate ()
	{
		return $this->asNewFromDate;
	}
	/** 
	 * Setter for asNewFromDate
	 * 
	 * @param string $value
	 * 
	 * @return Product
	 */
	public function setAsNewFromDate($value)
	{
		$this->asNewFromDate = $value;
		return $this;
	}
	/** 
	 * Getter for asNewToDate
	 * 
	 * @return Ambigous <UDate, NULL>
	 */
	public function getAsNewToDate ()
	{
		return $this->asNewToDate;
	}
	/** 
	 * Setter for asNewToDate
	 * 
	 * @param string $value
	 * 
	 * @return Product
	 */
	public function setAsNewToDate($value)
	{
		$this->asNewToDate = $value;
		return $this;
	}
	/** 
	 * Getter for sellOnWeb
	 * 
	 * @return boolean
	 */
	public function getSellOnWeb ()
	{
		return $this->sellOnWeb;
	}
	/** 
	 * Setter for sellOnWeb
	 * 
	 * @param bool $value
	 * 
	 * @return Product
	 */
	public function setSellOnWeb($value)
	{
		$this->sellOnWeb = $value;
		return $this;
	}
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
	 * Getter for stockOnPO
	 *
	 * @return 
	 */
	public function getstockOnPO() 
	{
	    return $this->stockOnPO;
	}
	/**
	 * Setter for stockOnPO
	 *
	 * @param int $value The stockOnHand
	 *
	 * @return Product
	 */
	public function setStockOnPO($value) 
	{
	    $this->stockOnPO = $value;
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
	 * Getter for status
	 * 
	 * @return ProductStatus
	 */
	public function getStatus ()
	{
		$this->loadManyToOne('status');
		return $this->status;
	}
	/** 
	 * Setter for status
	 * 
	 * @param ProductStatus $value
	 * 
	 * @return Product
	 */
	public function setStatus($value)
	{
		$this->status = $value;
		return $this;
	}
	/** 
	 * Getter for manufacturer
	 * 
	 * @return Manufacturer
	 */
	public function getManufacturer ()
	{
		$this->loadManyToOne('manufacturer');
		return $this->manufacturer;
	}
	/** 
	 * Setter for manufacturer
	 * 
	 * @param Manufacturer $value
	 * 
	 * @return Product
	 */
	public function setManufacturer(Manufacturer $value = null)
	{
		$this->manufacturer = $value;
		return $this;
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
	 * Getting the prices
	 * 
	 * @return Ambigous <Ambigous, multitype:, multitype:BaseEntityAbstract >
	 */
	public function getPrices()
	{
		if(!isset($this->_cache['prices']))
		{
			$this->_cache['prices'] = ProductPrice::getPrices($this);
		}
		return $this->_cache['prices'];
	}
	/**
	 * Getting all the images
	 * 
	 * @return multitype:
	 */
	public function getImages()
	{
		if(!isset($this->_cache['images']))
		{
			$this->_cache['images'] = ProductImage::getAllByCriteria('productId = ? ', array($this->getId()));
		}
		return $this->_cache['images'];
	}
	/**
	 * adding the category to this product
	 * 
	 * @param ProductCategory $category
	 * 
	 * @return Product
	 */
	public function addCategory(ProductCategory $category)
	{
		Product_Category::create($this, $category);
		return $this;
	}
	/**
	 * removing the category to this product
	 *
	 * @param ProductCategory $category
	 *
	 * @return Product
	 */
	public function removeCategory(ProductCategory $category)
	{
		Product_Category::remove($this, $category);
		return $this;
	}
	/**
	 * clearing all the categories to this product
	 *
	 * @param ProductCategory $category
	 *
	 * @return Product
	 */
	public function clearAllCategory()
	{
		Product_Category::deleteByCriteria('productId = ?', array($this->getId()));
		return $this;
	}
	/**
	 * Adding a price to a product
	 * 
	 * @param ProductPriceType $type
	 * @param number           $value
	 * @param string           $fromDate
	 * @param string           $toDate
	 * 
	 * @return Product
	 */
	public function addPrice(ProductPriceType $type, $value, $fromDate = null, $toDate = null)
	{
		ProductPrice::create($this, $type, $value, $fromDate, $toDate);
		return $this;
	}
	/**
	 * removing the price
	 * 
	 * @param ProductPriceType $type
	 * 
	 * @return Product
	 */
	public function removePrice(ProductPriceType $type)
	{
		ProductPrice::updateByCriteria('active = 0', 'productId = ? and typeId = ?', array($this->getId(), $type->getId()));
		return $this;
	}
	/**
	 * removing the prices
	 *
	 * @param ProductPriceType $type
	 *
	 * @return Product
	 */
	public function clearAllPrice()
	{
		ProductPrice::updateByCriteria('active = 0', 'productId = ?', array($this->getId()));
		return $this;
	}
	/**
	 * Adding a supplier
	 * 
	 * @param Supplier $supplier
	 * @param string   $supplierCode
	 * 
	 * @return Product
	 */
	public function addSupplier(Supplier $supplier, $supplierCode = 'NA')
	{
		SupplierCode::create($this, $supplier, $supplierCode);
		return $this;
	}
	/**
	 * removing the suppler
	 *
	 * @param ProductPriceType $type
	 *
	 * @return Product
	 */
	public function removeSupplier(Supplier $supplier, $supplierCode = '')
	{
		$where = 'productId = ? and suplierId = ?';
		$params = array($this->getId(), $supplier->getId());
		if(trim($supplierCode) !== '')
		{
			$where .= ' AND code like = ?';
			$params[] = trim($supplierCode);
		}
		SupplierCode::updateByCriteria('active = 0', $where, $params);
		return $this;
	}
	/**
	 * removing all the suppliers
	 * 
	 * @return Product
	 */
	public function clearSuppliers()
	{
		SupplierCode::updateByCriteria('active = 0', 'productId = ?', array($this->getId()));
		return $this;
	}
	public function getLocations(PreferredLocationType $type = null, $activeOnly = true, $pageNo = null, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE, $orderBy = array(), &$stats = array())
	{
		return PreferredLocation::getPreferredLocations($this, $type, $activeOnly, $pageNo, $pageSize, $orderBy, $stats);
	}
	public function addLocation(PreferredLocationType $type, Location $location)
	{
		PreferredLocation::create($location, $this, $type);
		return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::getJson()
	 */
	public function getJson($extra = '', $reset = false)
	{
		$array = array();
		if(!$this->isJsonLoaded($reset))
		{
			$array['prices'] = array_map(create_function('$a', 'return $a->getJson();'), $this->getPrices());
			$array['manufacturer'] = $this->getManufacturer() instanceof Manufacturer ? $this->getManufacturer()->getJson() : null;
			$array['supplierCodes'] = array_map(create_function('$a', 'return $a->getJson();'), SupplierCode::getAllByCriteria('productId = ?', array($this->getId())));
			$array['productCodes'] = array_map(create_function('$a', 'return $a->getJson();'), ProductCode::getAllByCriteria('productId = ?', array($this->getId())));
			$array['images'] = array_map(create_function('$a', 'return $a->getJson();'), $this->getImages());
			$array['categories'] = array_map(create_function('$a', '$json = $a->getJson(); return $json["category"];'), Product_Category::getCategories($this));
			$array['fullDescriptionAsset'] = (($asset = Asset::getAsset($this->getFullDescAssetId())) instanceof Asset ? $asset->getJson() : null) ;
			$array['locations'] = array_map(create_function('$a', 'return $a->getJson();'), PreferredLocation::getPreferredLocations($this));
		}
		return parent::getJson($array, $reset);
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::preSave()
	 */
	public function preSave()
	{
		$sku = trim($this->getSku());
		$where = array('sku = ? ');
		$params = array($sku);
		if(($id = trim($this->getId())) !== '')
		{
			$where[] = 'id != ?';
			$params[] = $id;
		}
		$exsitingSKU = Product::countByCriteria(implode(' AND ', $where), $params);
		if($exsitingSKU > 0)
			throw new EntityException('The SKU(=' . $sku . ') is already exists!' );
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
		DaoMap::setIntType('stockOnHand', 'int', 10, false);
		DaoMap::setIntType('stockOnOrder', 'int', 10, false);
		DaoMap::setIntType('stockOnPO', 'int', 10, false);
		DaoMap::setBoolType('isFromB2B');
		DaoMap::setBoolType('sellOnWeb');
		DaoMap::setManyToOne('status', 'ProductStatus', 'pro_status', true);
		DaoMap::setManyToOne('manufacturer', 'Manufacturer', 'pro_man', true);
		DaoMap::setDateType('asNewFromDate', 'datetime', true);
		DaoMap::setDateType('asNewToDate', 'datetime', true);
		DaoMap::setStringType('shortDescription', 'varchar', 255);
		DaoMap::setStringType('fullDescAssetId', 'varchar', 100);
		DaoMap::setOneToMany('supplierCodes', 'SupplierCode', 'pro_sup_code');
		DaoMap::setOneToMany('categories', 'Product_Category', 'pro_cate');
		DaoMap::setOneToMany('codes', 'ProductCode', 'pro_pro_code');
		parent::__loadDaoMap();
		
		DaoMap::createUniqueIndex('sku');
		DaoMap::createIndex('name');
		DaoMap::createIndex('mageId');
		DaoMap::createIndex('stockOnHand');
		DaoMap::createIndex('stockOnOrder');
		DaoMap::createIndex('stockOnPO');
		DaoMap::createIndex('isFromB2B');
		DaoMap::createIndex('shortDescription');
		DaoMap::createIndex('fullDescAssetId');
		DaoMap::createIndex('sellOnWeb');
		DaoMap::createIndex('asNewFromDate');
		DaoMap::createIndex('asNewToDate');
		DaoMap::commit();
	}
	/**
	 * Getting the product via sku
	 *
	 * @param string $sku The sku of the product
	 *
	 * @return Ambigous <NULL, BaseEntityAbstract>
	 */
	public static function getBySku($sku)
	{
		$products = self::getAllByCriteria('sku = ? ', array(trim($sku)), false, 1, 1);
		return (count($products) === 0 ? null : $products[0]);
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
	public static function create($sku, $name, $mageProductId = '', $stockOnHand = null, $stockOnOrder = null, $isFromB2B = false, $shortDescr = '', $fullDescr = '', Manufacturer $manufacturer = null)
	{
		if(!($product = self::getBySku($sku)) instanceof Product)
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
			if ($manufacturer instanceof Manufacturer)
			{
				$product->setManufacturer($manufacturer);
			}
		}
		return $product->save();
	}
	/**
	 * Finding the products with different params
	 * 
	 * @param unknown $sku
	 * @param unknown $name
	 * @param array $supplierIds
	 * @param array $manufacturerIds
	 * @param array $categoryIds
	 * @param array $statusIds
	 * @param string $active
	 * @param string $pageNo
	 * @param unknown $pageSize
	 * @param unknown $orderBy
	 * @param unknown $stats
	 * 
	 * @return Ambigous <Ambigous, multitype:, multitype:BaseEntityAbstract >
	 */
	public static function getProducts($sku, $name, array $supplierIds = array(), array $manufacturerIds = array(), array $categoryIds = array(), array $statusIds = array(), $active = null, $pageNo = null, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE, $orderBy = array(), &$stats = array())
	{
		$where = array(1);
		$params = array();
		if(($sku = trim($sku)) !== '')
		{
			$where[] = 'pro.sku like ?';
			$params[] = '%' . $sku . '%';
		}
		if(($name = trim($name)) !== '')
		{
			$where[] = 'pro.name like ?';
			$params[] = '%' . $name . '%';
		}
		if(($active = trim($active)) !== '')
		{
			$where[] = 'pro.active = ?';
			$params[] = intval($active);
		}
		if(count($manufacturerIds) > 0)
		{
			$where[] = 'pro.manufacturerId in (' . implode(',', array_fill(0, count($manufacturerIds), '?')) . ')';
			$params = array_merge($params, $manufacturerIds);
		}
		if(count($statusIds) > 0)
		{
			$where[] = 'pro.statusId in (' . implode(',', array_fill(0, count($statusIds), '?')) . ')';
			$params = array_merge($params, $statusIds);
		}
		if(count($supplierIds) > 0)
		{
			self::getQuery()->eagerLoad('Product.supplierCodes', 'inner join', 'pro_sup_code', 'pro.id = pro_sup_code.productId and pro_sup_code.supplierId in (' . implode(',', array_fill(0, count($supplierIds), '?')) . ')');
			$params = array_merge($supplierIds, $params);
		}
		if(count($categoryIds) > 0)
		{
			self::getQuery()->eagerLoad('Product.categories', 'inner join', 'pro_cate', 'pro.id = pro_cate.productId and pro_cate.categoryId in (' . implode(',', array_fill(0, count($categoryIds), '?')) . ')');
			$params = array_merge($categoryIds, $params);
		}
		return Product::getAllByCriteria(implode(' AND ', $where), $params, false, $pageNo, $pageSize, $orderBy, $stats);
	}
}