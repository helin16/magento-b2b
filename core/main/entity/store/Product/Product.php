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
		FactoryAbastract::dao(get_called_class())->save($product);
		return $product;
	}
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
			$where[] = 'pro.manufacturerId in (' . str_repeat('?', count($manufacturerIds)) . ')';
			$params = array_merge($params, $manufacturerIds);
		}
		if(count($statusIds) > 0)
		{
			$where[] = 'pro.statusId in (' . str_repeat('?', count($statusIds)) . ')';
			$params = array_merge($params, $statusIds);
		}
		if(count($supplierIds) > 0)
		{
			$where[] = ' = ?';
			$params[] = $active;
		}
		if(count($categoryIds) > 0)
		{
			$where[] = ' = ?';
			$params[] = $active;
		}
		return Product::getAllByCriteria(implode(' AND ', $where), $params, false, $pageNo, $pageSize, $orderBy, $stats);
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
		DaoMap::setIntType('stockOnHand');
		DaoMap::setIntType('stockOnOrder');
		DaoMap::setBoolType('isFromB2B');
		DaoMap::setBoolType('sellOnWeb');
		DaoMap::setManyToOne('status', 'ProductStatus', 'pro_status', true);
		DaoMap::setManyToOne('manufacturer', 'Manufacturer', 'pro_man', true);
		DaoMap::setDateType('asNewFromDate', 'datetime', true);
		DaoMap::setDateType('asNewToDate', 'datetime', true);
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
		DaoMap::createIndex('sellOnWeb');
		DaoMap::createIndex('asNewFromDate');
		DaoMap::createIndex('asNewToDate');
		DaoMap::commit();
	}
}