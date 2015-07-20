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
	 * The quantity in parts for build
	 *
	 * @var int
	 */
	private $stockInParts = 0;
	/**
	 * The quantity in RMA for build
	 *
	 * @var int
	 */
	private $stockInRMA = 0;
	/**
	 * The minimum stock level
	 *
	 * @var int
	 */
	private $stockMinLevel = null;
	/**
	 * The reorder stock lelvel
	 *
	 * @var int
	 */
	private $stockReorderLevel = null;
	/**
	 * The total value for RMA stock
	 *
	 * @var double
	 */
	private $totalRMAValue = 0;
	/**
	 * The total value for all stock on hand units
	 *
	 * @var double
	 */
	private $totalOnHandValue = 0;
	/**
	 * The total value for all stock on parts for build
	 *
	 * @var double
	 */
	private $totalInPartsValue = 0;
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
	 * the asset number for accounting purpose
	 * @var string
	 */
	private $assetAccNo = '';
	/**
	 * the revenue number for accounting purpose
	 * @var string
	 */
	private $revenueAccNo = '';
	/**
	 * the cost number for accounting purpose
	 * @var string
	 */
	private $costAccNo = '';
	/**
	 * whether this product is a kit
	 *
	 * @var bool
	 */
	private $isKit = false;
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
	 * @return int
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
	 * @return int
	 */
	public function getStockOnPO()
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
	 * Getter for stockInParts
	 *
	 * @return int
	 */
	public function getStockInParts()
	{
	    return $this->stockInParts;
	}
	/**
	 * Setter for stockInParts
	 *
	 * @param int $value The stockInParts
	 *
	 * @return Product
	 */
	public function setStockInParts($value)
	{
	    $this->stockInParts = $value;
	    return $this;
	}
	/**
	 * Getter for stockInRMA
	 *
	 * @return int
	 */
	public function getStockInRMA()
	{
	    return $this->stockInRMA;
	}
	/**
	 * Setter for stockInRMA
	 *
	 * @param int $value The stockInRMA
	 *
	 * @return Product
	 */
	public function setStockInRMA($value)
	{
	    $this->stockInRMA = $value;
	    return $this;
	}
	/**
	 * getter for stockMinLevel
	 *
	 * @return int|null
	 */
	public function getStockMinLevel()
	{
		return $this->stockMinLevel;
	}
	/**
	 * Setter for stockMinLevel
	 *
	 * @return Product
	 */
	public function setStockMinLevel($stockMinLevel)
	{
		$this->stockMinLevel = $stockMinLevel;
		return $this;
	}
	/**
	 * getter for stockReorderLevel
	 *
	 * @return int|null
	 */
	public function getStockReorderLevel()
	{
		return $this->stockReorderLevel;
	}
	/**
	 * Setter for stockReorderLevel
	 *
	 * @return Product
	 */
	public function setStockReorderLevel($stockReorderLevel)
	{
		$this->stockReorderLevel = $stockReorderLevel;
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
	 * Getter for totalOnHandValue
	 *
	 * @return double
	 */
	public function getTotalOnHandValue  ()
	{
		return $this->totalOnHandValue ;
	}
	/**
	 * Setter for totalOnHandValue
	 *
	 * @param double $value
	 *
	 * @return Product
	 */
	public function setTotalOnHandValue ($value )
	{
		$this->totalOnHandValue = $value;
		return $this;
	}
	/**
	 * Getter for totalInPartsValue
	 *
	 * @return double
	 */
	public function getTotalInPartsValue  ()
	{
		return $this->totalInPartsValue ;
	}
	/**
	 * Setter for totalInPartsValue
	 *
	 * @param double $value
	 *
	 * @return Product
	 */
	public function setTotalInPartsValue ($value )
	{
		$this->totalInPartsValue = $value;
		return $this;
	}
	/**
	 * Getter for assetAccNo
	 *
	 * @return string
	 */
	public function getAssetAccNo  ()
	{
		return $this->assetAccNo ;
	}
	/**
	 * Setter for assetAccNo
	 *
	 * @param string $value
	 *
	 * @return Product
	 */
	public function setAssetAccNo ($value )
	{
		$this->assetAccNo = $value;
		return $this;
	}
	/**
	 * Getter for revenueAccNo
	 *
	 * @return string
	 */
	public function getRevenueAccNo  ()
	{
		return $this->revenueAccNo ;
	}
	/**
	 * Setter for revenueAccNo
	 *
	 * @param string $value
	 *
	 * @return Product
	 */
	public function setRevenueAccNo ($value )
	{
		$this->revenueAccNo = $value;
		return $this;
	}
	/**
	 * Getter for costAccNo
	 *
	 * @return string
	 */
	public function getCostAccNo  ()
	{
		return $this->costAccNo ;
	}
	/**
	 * Setter for costAccNo
	 *
	 * @param string $value
	 *
	 * @return Product
	 */
	public function setCostAccNo ($value )
	{
		$this->costAccNo = $value;
		return $this;
	}
	/**
	 * Getter for totalRMAValue
	 *
	 * @return double
	 */
	public function getTotalRMAValue()
	{
	    return $this->totalRMAValue;
	}
	/**
	 * Setter for totalRMAValue
	 *
	 * @param double $value The totalRMAValue
	 *
	 * @return Product
	 */
	public function setTotalRMAValue($value)
	{
	    $this->totalRMAValue = $value;
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
	/**
	 * Getting the locations
	 *
	 * @param PreferredLocationType $type
	 * @param string $activeOnly
	 * @param string $pageNo
	 * @param unknown $pageSize
	 * @param unknown $orderBy
	 * @param unknown $stats
	 *
	 * @return Ambigous <Ambigous, multitype:, multitype:BaseEntityAbstract >
	 */
	public function getLocations(PreferredLocationType $type = null, $activeOnly = true, $pageNo = null, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE, $orderBy = array(), &$stats = array())
	{
		return PreferredLocation::getPreferredLocations($this, $type, $activeOnly, $pageNo, $pageSize, $orderBy, $stats);
	}
	/**
	 * adding the location
	 *
	 * @param PreferredLocationType $type
	 * @param Location $location
	 * @return Product
	 */
	public function addLocation(PreferredLocationType $type, Location $location)
	{
		PreferredLocation::create($location, $this, $type);
		return $this;
	}
	/**
	 * snapshot of the product qty
	 *
	 * @param BaseEntityAbstract $entity
	 * @param string             $type
	 * @param string             $comments
	 *
	 * @return Product
	 */
	public function snapshotQty(BaseEntityAbstract $entity = null, $type = '', $comments = '')
	{
		ProductQtyLog::create($this, $type, $entity, trim($comments));
		return $this;
	}
	/**
	 * Getter for isKit
	 *
	 * @return bool
	 */
	public function getIsKit()
	{
	    return intval($this->isKit) === 1;
	}
	/**
	 * Setter for isKit
	 *
	 * @param bool $value The isKit
	 *
	 * @return Product
	 */
	public function setIsKit($value)
	{
	    $this->isKit = $value;
	    return $this;
	}
	/**
	 * recalculating the stockOnHand and stockOnHandValue of this product, if it's a kit
	 */
	public function reCalKitsValue()
	{
		if($this->getIsKit() !== true || trim($this->getId()) === '')
			return $this;
		$sql = 'select sum(kit.cost) `totalValue`, count(distinct kit.id) `totalCount` from kit kit where kit.active = 1 and kit.soldDate = ? and kit.productId = ?';
		$result = Dao::getResultsNative($sql, array(trim(UDate::zeroDate()), $this->getId()));
		if(count($result) > 0) {
			$totalValue = (trim($result[0]['totalValue']) === '' ? '0.0000' : trim($result[0]['totalValue']));
			$totalCount = (trim($result[0]['totalCount']) === '' ? '0' : trim($result[0]['totalCount']));
			if(($originalTotalOnHandValue = trim($this->getTotalInPartsValue())) !== $totalValue || ($originalStockOnHand = trim($this->getStockOnHand())) !== $totalCount) { //if not matched, then we need to adjust the qty
				$this->setStockOnHand($totalCount)
					->setTotalOnHandValue($totalValue)
					->snapshotQty($this, ProductQtyLog::TYPE_STOCK_ADJ, 'Realigning the TotalInPartsValue to ' . StringUtilsAbstract::getCurrency($totalValue) . ' and StockOnHand to ' . $totalCount)
					->save()
					->addLog('StockOnHand(' . $originalStockOnHand . ' => ' . $this->getStockOnHand() . ')', Log::TYPE_SYSTEM, 'STOCK_QTY_CHG', __CLASS__ . '::' . __FUNCTION__)
					->addLog('TotalOnHandValue(' . $originalTotalOnHandValue . ' => ' .$this->getTotalOnHandValue() . ')', Log::TYPE_SYSTEM, 'STOCK_VALUE_CHG', __CLASS__ . '::' . __FUNCTION__);
			}
		}
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::getJson()
	 */
	public function getJson($extra = array(), $reset = false)
	{
		$array = $extra;
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
			$array['unitCost'] = $this->getUnitCost();
			$array['priceMatchRule'] = ($i=ProductPriceMatchRule::getByProduct($this)) instanceof ProductPriceMatchRule ? $i->getJson() : null;
		}
		return parent::getJson($array, $reset);
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::preSave()
	 */
	public function preSave()
	{
		if(intVal($this->getActive()) === 1) {
			$sku = trim($this->getSku());
			$where = array('sku = ?');
			$params = array($sku);
			if(($id = trim($this->getId())) !== '')
			{
				$where[] = 'id != ?';
				$params[] = $id;
			}
			$exsitingSKU = self::countByCriteria(implode(' AND ', $where), $params);
			if($exsitingSKU > 0)
				throw new EntityException('The SKU(=' . $sku . ') is already exists!' );
		}
		if(($id = trim($this->getId())) !== '') {
			if(self::countByCriteria('id = ? and isKit = 1 and isKit != ?', array($id, $this->getIsKit())) > 0) {//changing isKit flag to be not a KIT
				if(count($kits = Kit::getAllByCriteria('productId = ?', array($id), true, 1, 1)) > 0 )
					throw new EntityException('Can NOT change the flag IsKit, as there are kits like [' . $kits[0]->getBarcode() . '] for this product: ' . $this->getSku());
			}
		}
	}
	/**
	 * Getting the unit cost based on the total value and stock on Hand
	 *
	 * @return number
	 */
	public function getUnitCost()
	{
		return intval($this->getStockOnHand()) === 0 ? 0 : round(abs($this->getTotalOnHandValue()) / abs(intval($this->getStockOnHand())), 2);
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
	 * A product is picked
	 *
	 * @param int                $qty
	 * @param string             $comments
	 * @param BaseEntityAbstract $entity
	 *
	 * @return Product
	 */
	public function picked($qty, $comments = '', BaseEntityAbstract $entity = null)
	{
		$order = ($entity instanceof Order ? $entity : ($entity instanceof OrderItem ? $entity->getOrder() : null));
		$unitCost = $this->getUnitCost();
		$totalCost = ($qty * $unitCost);
		$action = (intval($qty) > 0 ? 'Stock picked' : 'stock UNPICKED');
		$newQty = (($originStockOnHand = $this->getStockOnHand()) - $qty);
		if($newQty < 0 && intval($qty) > 0 && intval(SystemSettings::getSettings(SystemSettings::TYPE_ALLOW_NEGTIVE_STOCK)) !== 1) 
			throw new Exception('Product (SKU:' . $this->getSKU() . ') can NOT be ' . $action . ' , as there is not enough stock: stock on hand will fall below zero');
		if($entity instanceof OrderItem) {
			$kits = array_map(create_function('$a', 'return $a->getKit();'), SellingItem::getAllByCriteria('orderItemId = ? and kitId is not null', array($entity->getId())));
			$kits = array_unique($kits);
			if(count($kits) > 0) {
				$totalCost = 0;
				$barcodes = array();
				foreach($kits as $kit) {
					$totalCost = $kit->getCost();
					$barcodes[] = $kit->getBarcode();
				}
				$comments .= ' ' . $action . ' KITS[' . implode(',', $barcodes) . '] with total cost value:' . StringUtilsAbstract::getCurrency($totalCost);
			}
		}
		$newStockOnOrder = ($originStockOnOrder = $this->getStockOnOrder()) + $qty;
		if($newStockOnOrder < 0  && intval(SystemSettings::getSettings(SystemSettings::TYPE_ALLOW_NEGTIVE_STOCK)) !== 1)
			throw new Exception('Product (SKU:' . $this->getSKU() . ') can NOT be ' . $action . ' , as there is not enough stock: new stock on order will fall below zero');
		return $this->setStockOnHand($newQty)
			->setStockOnOrder($newStockOnOrder)
			->setTotalOnHandValue(($origTotalOnHandValue = $this->getTotalOnHandValue()) - $totalCost)
			->snapshotQty($entity instanceof BaseEntityAbstract ? $entity : $this, ProductQtyLog::TYPE_SALES_ORDER, $action . ': ' . ($order instanceof Order ? '[' . $order->getOrderNo() . ']' : '') . $comments)
			->save()
			->addLog('StockOnHand(' . $originStockOnHand . ' => ' . $this->getStockOnHand() . ')', Log::TYPE_SYSTEM, 'STOCK_QTY_CHG', __CLASS__ . '::' . __FUNCTION__)
			->addLog('StockOnOrder(' . $originStockOnOrder . ' => ' . $this->getStockOnOrder() . ')', Log::TYPE_SYSTEM, 'STOCK_QTY_CHG', __CLASS__ . '::' . __FUNCTION__)
			->addLog('TotalOnHandValue(' . $origTotalOnHandValue . ' => ' .$this->getTotalOnHandValue() . ')', Log::TYPE_SYSTEM, 'STOCK_VALUE_CHG', __CLASS__ . '::' . __FUNCTION__);
	}
	/**
	 * A product is received
	 *
	 * @param int                $qty
	 * @param double             $unitCost
	 * @param string             $comments
	 * @param BaseEntityAbstract $entity
	 *
	 * @throws EntityException
	 * @return Product
	 */
	public function received($qty, $unitCost, $comments = '', BaseEntityAbstract $entity = null)
	{
		if(!is_numeric($unitCost))
			throw new EntityException('Unitcost of receiving product(SKU=' . $this->getSku() . ') is not a number!');

		$origStockOnHand = $this->getStockOnHand();
		$newStockOnHand = ($origStockOnHand + $qty);
		$origTotalOnHandValue = $this->getTotalOnHandValue();
		if($origStockOnHand < 0) {
			$newStockOnHandValue = $newStockOnHand * $unitCost;
		} else {
			$newStockOnHandValue = ($origTotalOnHandValue + $qty * $unitCost);
		}

		return $this->setStockOnPO(($origStockOnPO = $this->getStockOnPO()) - $qty)
			->setStockOnHand($newStockOnHand)
			->setTotalOnHandValue($newStockOnHandValue)
			->snapshotQty($entity instanceof BaseEntityAbstract ? $entity : $this, ProductQtyLog::TYPE_PO, 'Stock received: ' . $comments)
			->save()
			->addLog('StockOnPO(' . $origStockOnPO . ' => ' .$this->getStockOnPO() . ')', Log::TYPE_SYSTEM, 'STOCK_QTY_CHG', __CLASS__ . '::' . __FUNCTION__)
			->addLog('StockOnHand(' . $origStockOnHand . ' => ' .$this->getStockOnHand() . ')', Log::TYPE_SYSTEM, 'STOCK_QTY_CHG', __CLASS__ . '::' . __FUNCTION__)
			->addLog('TotalOnHandValue(' . $origTotalOnHandValue . ' => ' .$this->getTotalOnHandValue() . ')', Log::TYPE_SYSTEM, 'STOCK_VALUE_CHG', __CLASS__ . '::' . __FUNCTION__);
	}
	/**
	 * A product is ordered from supplier
	 *
	 * @param int                $qty
	 * @param string             $comments
	 * @param BaseEntityAbstract $entity
	 *
	 * @throws EntityException
	 * @return Product
	 */
	public function ordered($qty, $comments = '', BaseEntityAbstract $entity = null)
	{
		return $this->setStockOnPO(($origStockOnPO = $this->getStockOnPO()) + $qty)
			->snapshotQty($entity instanceof BaseEntityAbstract ? $entity : $this, ProductQtyLog::TYPE_STOCK_MOVE_INTERNAL, 'Stock ordered from supplier: ' . $comments)
			->save()
			->addLog('StockOnPO(' . $origStockOnPO . ' => ' .$this->getStockOnPO() . ')', Log::TYPE_SYSTEM, 'STOCK_QTY_CHG', __CLASS__ . '::' . __FUNCTION__);
	}
	/**
	 * A product is shipped
	 *
	 * @param unknown            $qty
	 * @param string             $comments
	 * @param BaseEntityAbstract $entity
	 *
	 * @return Product
	 */
	public function shipped($qty, $comments = '', BaseEntityAbstract $entity = null)
	{
		$order = ($entity instanceof Order ? $entity : ($entity instanceof OrderItem ? $entity->getOrder() : null));
		$newQty = (($originStockOnOrder = $this->getStockOnOrder()) - $qty);
		if($newQty < 0 && intval($qty) >0  && intval(SystemSettings::getSettings(SystemSettings::TYPE_ALLOW_NEGTIVE_STOCK)) !== 1)
			throw new Exception('Product (SKU:' . $this->getSKU() . ') can NOT be pick, as there is not enough stock.');
		return $this->setStockOnOrder($newQty)
			->snapshotQty($entity instanceof BaseEntityAbstract ? $entity : $this, ProductQtyLog::TYPE_STOCK_MOVE_INTERNAL, 'Stock shipped. ' . ($order instanceof Order ? '[' . $order->getOrderNo() . ']' : ''))
			->save()
			->addLog('StockOnOrder(' . $originStockOnOrder . ' => ' . $this->getStockOnOrder() . ')', Log::TYPE_SYSTEM, 'STOCK_QTY_CHG', __CLASS__ . '::' . __FUNCTION__);
	}
	/**
	 * a product is returned for RMA
	 *
	 * @param unknown            $qty
	 * @param dobuble            $unitCost
	 * @param string             $comments
	 * @param BaseEntityAbstract $entity
	 */
	public function returnedIntoRMA($qty, $unitCost, $comments, BaseEntityAbstract $entity = null)
	{
		$rma = ($entity instanceof RMA ? $entity : ($entity instanceof RMAItem ? $entity->getRMA() : null));
		$order = ($rma instanceof RMA ? $rma->getOrder() : null);
		return $this->setStockInRMA(($originalStockOnRMA = $this->getStockInRMA()) + $qty)
			->setTotalRMAValue(($originalTotalRMAValue = $this->getTotalRMAValue()) + ($qty * $unitCost))
			->snapshotQty($entity instanceof BaseEntityAbstract ? $entity : $this, ProductQtyLog::TYPE_RMA, 'Stock RMAed from ' . ($rma instanceof RMA ? 'RMA[' . $rma->getRaNo() . ']' : '') . ($order instanceof Order ? ' generated from Order[' . $order->getOrderNo() . ']' : '') . (trim($comments) === '' ? '.' : ': ' . $comments))
			->save()
			->addLog('StockInRMA(' . $originalStockOnRMA . ' => ' . $this->getStockInRMA() . '), TotalRMAValue(' . $originalTotalRMAValue . ' => ' . $this->getTotalRMAValue() . ')' . (trim($comments) === '' ? '.' : ': ' . $comments),
					Log::TYPE_SYSTEM,
					'STOCK_QTY_CHG',
					__CLASS__ . '::' . __FUNCTION__);
	}
	/**
	 * a product is created as a kit
	 *
	 * @param string             $comments
	 * @param BaseEntityAbstract $entity
	 */
	public function createAsAKit($comments, Kit $entity)
	{
		$task = ($entity instanceof Kit ? $entity->getTask() : null);
		return $this->setStockOnHand(($originalStockOnHand = $this->getStockOnHand()) + 1)
			->setTotalOnHandValue(($originalTotalOnHandValue = $this->getTotalOnHandValue()) + $entity->getCost())
			->snapshotQty($entity instanceof BaseEntityAbstract ? $entity : $this, ProductQtyLog::TYPE_WORKSHOP,
					'Created a Kit[' . $entity->getBarcode() . '] with value(cost=' . StringUtilsAbstract::getCurrency($entity->getCost())  . ')' . ($task instanceof Task ? ' generated from Task[' . $task->getId() . ']' : '') . (trim($comments) === '' ? '.' : ': ' . $comments))
			->save()
			->addLog('StockOnHand(' . $originalStockOnHand . ' => ' . $this->getStockOnHand() . '), StockOnHandValue(' . $originalTotalOnHandValue . ' => ' . $this->getTotalOnHandValue() . ')' . (trim($comments) === '' ? '.' : ': ' . $comments),
					Log::TYPE_SYSTEM,
					'STOCK_QTY_CHG',
					__CLASS__ . '::' . __FUNCTION__);
	}
	/**
	 * a product is installed into a kit
	 *
	 * @param unknown            $qty
	 * @param dobuble            $unitCost
	 * @param string             $comments
	 * @param BaseEntityAbstract $entity
	 */
	public function installedIntoKit($qty, $unitCost, $comments, BaseEntityAbstract $entity = null)
	{
		$kitComponent = $entity instanceof KitComponent ? $entity : null;
		$kit = ($kitComponent instanceof KitComponent ? $kitComponent->getKit() : ($entity instanceof Kit ? $entity : null));
		$task = ($kit instanceof Kit ? $kit->getTask() : null);
		return $this->setStockOnHand(($originalStockOnHand = $this->getStockOnHand()) - $qty)
			->setTotalOnHandValue(($originalTotalOnHandValue = $this->getTotalOnHandValue()) - ($qty * $unitCost))
			->setStockInParts(($originalStockInParts = $this->getStockInParts()) + $qty)
			->setTotalInPartsValue(($originalTotalInPartValue = $this->getTotalInPartsValue()) + ($qty * $unitCost))
			->snapshotQty($entity instanceof BaseEntityAbstract ? $entity : $this, ProductQtyLog::TYPE_WORKSHOP,
					'Stock ' . (intval($qty) <= 0 ? 'uninstalled from' : 'installed into') . ($kit instanceof Kit ? ' Kit [' . $kit->getBarcode() . ']' : '') . ($task instanceof Task ? ' generated from Task [' . $task->getId() . ']' : '') . (trim($comments) === '' ? '.' : ': ' . $comments))
			->save()
			->addLog('StockOnHand(' . $originalStockOnHand . ' => ' . $this->getStockOnHand() . '), TotalOnHandValue(' . $originalTotalOnHandValue . ' => ' . $this->getTotalOnHandValue() . '), StockInParts(' . $originalStockInParts . ' => ' . $this->getStockInParts() . '), TotalInPartsValue(' . $originalTotalInPartValue . ' => ' . $this->getTotalInPartsValue() . ')' . (trim($comments) === '' ? '.' : ': ' . $comments),
					Log::TYPE_SYSTEM,
					'STOCK_QTY_CHG',
					__CLASS__ . '::' . __FUNCTION__);
	}
	/**
	 * a product is returned for into stock on hand
	 *
	 * @param unknown            $qty
	 * @param dobuble            $unitCost
	 * @param string             $comments
	 * @param BaseEntityAbstract $entity
	 */
	public function returnedIntoSOH($qty, $unitCost, $comments, BaseEntityAbstract $entity = null)
	{
		$creditNote = ($entity instanceof CreditNote ? $entity : ($entity instanceof CreditNoteItem ? $entity->getCreditNote() : null));
		$order = ($creditNote instanceof CreditNote ? $creditNote->getOrder() : null);
		return $this->setStockOnHand(($originalStockOnHand = $this->getStockOnHand()) + $qty)
			->setTotalOnHandValue(($originalTotalOnHandValue = $this->getTotalOnHandValue()) + ($qty * $unitCost))
			->snapshotQty($entity instanceof BaseEntityAbstract ? $entity : $this, ProductQtyLog::TYPE_RMA, 'Return StockOnHand ' . ($creditNote instanceof CreditNote ? ' from CreditNote[' . $creditNote->getCreditNoteNo() . ']' : '') . ($order instanceof Order ? ' generated from Order[' . $order->getOrderNo() . ']' : '') . (trim($comments) === '' ? '.' : ': ' . $comments))
			->save()
			->addLog('StockOnHand(' . $originalStockOnHand . ' => ' . $this->getStockOnHand() . '), TotalRMAValue(' . $originalTotalOnHandValue . ' => ' . $this->getTotalOnHandValue() . ')' . (trim($comments) === '' ? '.' : ': ' . $comments),
					Log::TYPE_SYSTEM,
					'STOCK_QTY_CHG',
					__CLASS__ . '::' . __FUNCTION__);
	}
	/**
	 * a product is fixed from RMA
	 *
	 * @param unknown            $qty
	 * @param string             $comments
	 * @param BaseEntityAbstract $entity
	 */
	public function fixedFromRMA($qty, $comments, BaseEntityAbstract $entity = null)
	{
		$rma = ($entity instanceof RMA ? $entity : ($entity instanceof RMAItem ? $entity->getRMA() : null));
		$order = ($rma instanceof RMA ? $rma->getOrder() : null);
		$unitCostFromRMA = intval($this->getStockInRMA()) === 0 ? 0 : ($this->getTotalRMAValue() /  $this->getStockInRMA());
		return $this->setStockInRMA(($originalStockOnRMA = $this->getStockInRMA()) - $qty)
			->setTotalRMAValue(($originalTotalRMAValue = $this->getTotalRMAValue()) - ($qty * $unitCostFromRMA))
			->setStockOnHand(($originStockOnHand = $this->getStockOnHand()) + $qty)
			->setTotalOnHandValue(($originalTotalOnHandValue = $this->getTotalOnHandValue()) + ($qty * $unitCostFromRMA))
			->snapshotQty($entity instanceof BaseEntityAbstract ? $entity : $this, ProductQtyLog::TYPE_RMA, 'Stock Fixed from: ' . ($creditNote instanceof CreditNote ? 'RMA[' . $rma->getRaNo() . ']' : '') . ($order instanceof Order ? ' generated from Order[' . $order->getOrderNo() . ']' : ''). (trim($comments) === '' ? '.' : ': ' . $comments))
			->save()
			->addLog('StockInRMA(' . $originalStockOnRMA . ' => ' . $this->getStockInRMA() . '), TotalRMAValue(' . $originalTotalRMAValue . ' => ' . $this->getTotalRMAValue() . '), StockOnHand(' . $originStockOnHand . ' => ' . $this->getStockOnHand() . '), TotalOnHandValue(' . $originalTotalOnHandValue . ' => ' . $this->getTotalOnHandValue() . ')' . (trim($comments) === '' ? '.' : ': ' . $comments)
				, Log::TYPE_SYSTEM
				, 'STOCK_QTY_CHG'
				, __CLASS__ . '::' . __FUNCTION__);
	}
	/**
	 * A product is stocktake
	 *
	 * @param int $stockOnHand
	 * @param int $stockOnOrder
	 * @param int $stockInParts
	 * @param int $stockInRMA
	 */
	public function stockChanged($stockOnHand = null, $stockOnOrder = null, $stockInParts = null, $stockInRMA = null, $stockOnPO = null)
	{
		if(is_numeric($stockOnHand) || is_numeric($stockOnOrder) || is_numeric($stockInParts) || is_numeric($stockInRMA))
			throw new Exception('At least one of these quuanties needed: stockOnHand, stockOnOrder, stockInParts or stockInRMA');
		$unitCost = $this->getUnitCost();
		$originalProduct = self::get($this->getId());
		if($stockOnHand != null && ($stockOnHand = trim($stockOnHand)) !== trim($origStockOnHand = $originalProduct->getStockOnHand())) {
			$this->setTotalOnHandValue($stockOnHand * $unitCost)
				->setStockOnHand($stockOnHand);
		}
		if($stockOnOrder != null && ($stockOnOrder = trim($stockOnOrder)) !== trim($origStockOnOrder = $originalProduct->getStockOnOrder())) {
			$this->setStockOnOrder($stockOnOrder);
		}
		if($stockInParts != null && ($stockInParts = trim($stockInParts)) !== trim($origStockInParts = $originalProduct->getStockInParts())) {
			$this->setTotalInPartsValue($stockInParts * $unitCost)
				->setStockInParts($stockInParts);
		}
		if($stockInRMA != null && ($stockInRMA = trim($stockInRMA)) !== trim($origStockInRMA = $originalProduct->getStockInRMA())) {
			$this->setStockInRMA($stockInRMA);
		}
		if($stockOnPO != null && ($stockOnPO = trim($stockOnPO)) !== trim($origStockOnPO = $originalProduct->getStockOnPO())) {
			$this->setStockOnPO($stockOnPO);
		}
		$msg = 'Stock changed: StockOnHand [' . $origStockOnHand . ' => ' . $this->getStockOnHand() . '], '
					. 'StockOnOrder [' . $origStockOnOrder . ' => ' . $this->getStockOnOrder() . '], '
					. 'StockInParts [' . $origStockInParts . ' => ' . $this->getStockInParts() . '], '
					. 'StockInRMA [' . $origStockInRMA . ' => ' . $this->getStockInRMA() . '], '
					. 'StockOnPO [' . $origStockOnPO . ' => ' . $this->getStockOnPO() . ']';
		return $this->snapshotQty($this,  ProductQtyLog::TYPE_STOCK_ADJ, 'Stock Changed')
			->save()
			->addComment($msg, Comments::TYPE_SYSTEM)
			->addLog($msg, Log::TYPE_SYSTEM, 'STOCK_CHANGED', __CLASS__ . "::" . __FUNCTION__);
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
		DaoMap::setIntType('totalOnHandValue', 'double', '10,4', false);
		DaoMap::setIntType('totalInPartsValue', 'double', '10,4', false);
		DaoMap::setIntType('stockOnHand', 'int', 10, false);
		DaoMap::setIntType('stockOnOrder', 'int', 10, false);
		DaoMap::setIntType('stockOnPO', 'int', 10, false);
		DaoMap::setIntType('stockInParts', 'int', 10, false);
		DaoMap::setIntType('stockInRMA', 'int', 10, false);
		DaoMap::setIntType('stockMinLevel', 'int', 10, true, true);
		DaoMap::setIntType('stockReorderLevel', 'int', 10, true, true);
		DaoMap::setIntType('totalRMAValue', 'double', '10,4', false);
		DaoMap::setStringType('assetAccNo', 'varchar', 10);
		DaoMap::setStringType('revenueAccNo', 'varchar', 10);
		DaoMap::setStringType('costAccNo', 'varchar', 10);
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
		DaoMap::setBoolType('isKit');
		parent::__loadDaoMap();

		DaoMap::createUniqueIndex('sku');
		DaoMap::createIndex('name');
		DaoMap::createIndex('mageId');
		DaoMap::createIndex('stockOnHand');
		DaoMap::createIndex('stockOnOrder');
		DaoMap::createIndex('stockOnPO');
		DaoMap::createIndex('stockInParts');
		DaoMap::createIndex('stockInRMA');
		DaoMap::createIndex('isFromB2B');
		DaoMap::createIndex('shortDescription');
		DaoMap::createIndex('fullDescAssetId');
		DaoMap::createIndex('sellOnWeb');
		DaoMap::createIndex('asNewFromDate');
		DaoMap::createIndex('asNewToDate');
		DaoMap::createIndex('assetAccNo');
		DaoMap::createIndex('revenueAccNo');
		DaoMap::createIndex('costAccNo');
		DaoMap::createIndex('isKit');
		DaoMap::commit();
	}
	/**
	 * Getting the product via sku
	 *
	 * @param string $sku The sku of the product
	 *
	 * @return null|Product
	 */
	public static function getBySku($sku)
	{
		$products = self::getAllByCriteria('sku = ? ', array(trim($sku)), false, 1, 1);
		return (count($products) === 0 ? null : $products[0]);
	}
	/**
	 * Creating the product based on sku
	 *
	 * @param string $sku           	The sku of the product
	 * @param string $name          	The name of the product
	 * @param string $mageProductId 	The magento id of the product
	 * @param int    $stockOnHand   	The total quantity on hand for this product
	 * @param int    $stockOnOrder  	The total quantity on order from supplier for this product
	 * @param int    $stockMinLevel 	The minimum stock level for this product
	 * @param int    $stockReorderLevel	The reorder stock level for this product
	 * @param bool   $isFromB2B     	Whether this product is created via B2B?
	 * @param string $shortDescr    	The short description of the product
	 * @param string $fullDescr     	The assetId of the full description asset of the product
	 *
	 * @return Product
	 */
	public static function create($sku, $name, $mageProductId = '', $stockOnHand = null, $stockOnOrder = null, $isFromB2B = false, $shortDescr = '', $fullDescr = '', Manufacturer $manufacturer = null, $assetAccNo = null, $revenueAccNo = null, $costAccNo = null, $stockMinLevel = null, $stockReorderLevel = null)
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
			if($stockMinLevel !== null && is_numeric($stockMinLevel))
				$product->setStockMinLevel(intval($stockMinLevel));
			if($stockReorderLevel !== null && is_numeric($stockReorderLevel))
				$product->setStockReorderLevel(intval($stockReorderLevel));
			if($assetAccNo !== null && is_string($assetAccNo))
				$product->setAssetAccNo(trim($assetAccNo));
			if($revenueAccNo !== null && is_string($revenueAccNo))
				$product->setRevenueAccNo(trim($revenueAccNo));
			if($costAccNo !== null && is_string($costAccNo))
				$product->setCostAccNo(trim($costAccNo));
			if (($$fullDescr = trim($fullDescr)) !== '') {
				$asset = Asset::registerAsset('full_desc_' . $sku, $fullDescr, Asset::TYPE_PRODUCT_DEC);
				$product->setFullDescAssetId(trim($asset->getAssetId()));
			}
			if ($manufacturer instanceof Manufacturer) {
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
	public static function getProducts($sku, $name, array $supplierIds = array(), array $manufacturerIds = array(), array $categoryIds = array(), array $statusIds = array(), $active = null, $pageNo = null, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE, $orderBy = array(), &$stats = array(), $stockLevel = null, &$sumValues = null, $sh_from = null, $sh_to = null)
	{
		$where = array(1);
		$params = array();
		if(is_array($sumValues)) {
			$innerJoins = array();
		}
		if(is_array($sku)) {
			$where[] = 'pro.sku in (' . implode(',', array_fill(0, count($sku), '?')) . ')';
			$params = array_merge($params, $sku);
		} else if(($sku = trim($sku)) !== '') {
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
			if(is_array($sumValues)) {
				$innerJoins[] = 'inner join suppliercodes pro_sup_code on (pro.id = pro_sup_code.productId and pro_sup_code.supplierId in (' . implode(',', array_fill(0, count($supplierIds), '?')) . '))';
			}
			$params = array_merge($supplierIds, $params);
		}
		if(count($categoryIds) > 0)
		{
			self::getQuery()->eagerLoad('Product.categories', 'inner join', 'pro_cate', 'pro.id = pro_cate.productId and pro_cate.categoryId in (' . implode(',', array_fill(0, count($categoryIds), '?')) . ')');
			if(is_array($sumValues)) {
				$innerJoins[] = 'inner join product_category pro_cate on (pro.id = pro_cate.productId and pro_cate.categoryId in (' . implode(',', array_fill(0, count($categoryIds), '?')) . '))';
			}
			$params = array_merge($categoryIds, $params);
		}
		if(($stockLevel = trim($stockLevel)) !== '')
		{
			$where[] = 'pro.stockOnHand <= pro.' . $stockLevel. ' and pro.' . $stockLevel . ' is not null';
		}
		if(($sh_from = trim($sh_from)) !== '')
		{
			$where[] = 'pro.stockOnHand >= ?';
			$params[] = intval($sh_from);
		}
		if(($sh_to = trim($sh_to)) !== '')
		{
			$where[] = 'pro.stockOnHand <= ?';
			$params[] = intval($sh_to);
		}

		if(is_array($sumValues)) {
			$sql = 'select sum(pro.stockOnHand) `totalStockOnHand`, sum(pro.totalOnHandValue) `totalOnHandValue` from product pro ' . implode(' ', $innerJoins) . ' where pro.active = 1 and (' . implode(' AND ', $where) . ')';
			$sumResult = Dao::getResultsNative($sql, $params);
			if(count($sumResult) > 0 ){
				$sumValues['totalStockOnHand'] = $sumResult[0]['totalStockOnHand'];
				$sumValues['totalOnHandValue'] = $sumResult[0]['totalOnHandValue'];
			}
		}
		return Product::getAllByCriteria(implode(' AND ', $where), $params, false, $pageNo, $pageSize, $orderBy, $stats);
	}
}
