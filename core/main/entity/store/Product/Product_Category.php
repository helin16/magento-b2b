<?php
/**
 * Entity for Product_Category
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class Product_Category extends BaseEntityAbstract
{
	/**
	 * The category
	 * 
	 * @var ProductCategory
	 */
	protected $category;
	/**
	 * The product
	 * 
	 * @var Product
	 */
	protected $product;
	/**
	 * Getter for category
	 *
	 * @return ProductCategory
	 */
	public function getCategory() 
	{
		$this->loadManyToOne('category');
	    return $this->category;
	}
	/**
	 * Setter for category
	 *
	 * @param ProductCategory $value The category
	 *
	 * @return Product_Category
	 */
	public function setCategory(ProductCategory $value) 
	{
	    $this->category = $value;
	    return $this;
	}
	/**
	 * Getter for product
	 *
	 * @return 
	 */
	public function getProduct() 
	{
		$this->loadManyToOne('product');
	    return $this->product;
	}
	/**
	 * Setter for product
	 *
	 * @param Product $value The product
	 *
	 * @return SupplierCode
	 */
	public function setProduct(Product $value) 
	{
	    $this->product = $value;
	    return $this;
	}
	/**
	 * Creating a supplier code
	 * 
	 * @param Product  $product
	 * @param Supplier $supplier
	 * 
	 * @return SupplierCode
	 */
	public static function create(Product $product, ProductCategory $category)
	{
		$class = __CLASS__;
		self::remove($product, $category);
		$obj = new $class();
		$obj->setProduct($product)
			->setCategory($category)
			->save();
		return $obj;
	}
	/**
	 * Getting all products from a category
	 * 
	 * @param ProductCategory $category
	 * @param string          $activeOnly
	 * @param int             $pageNo
	 * @param int             $pageSize
	 * @param array           $orderBy
	 * @param array           $stats
	 * 
	 * @return Ambigous <multitype:, multitype:BaseEntityAbstract >
	 */
	public static function getProducts(ProductCategory $category, $activeOnly = true, $pageNo = null, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE, $orderBy = array(), &$stats = array())
	{
		return self::getAllByCriteria('categoryId = ?', array($category->getId()), $activeOnly, $pageNo, $pageSize, $orderBy, $stats);
	}
	/**
	 * Getting all products from a category
	 * 
	 * @param Product $category
	 * @param string  $activeOnly
	 * @param int     $pageNo
	 * @param int     $pageSize
	 * @param array   $orderBy
	 * @param array   $stats
	 * 
	 * @return Ambigous <multitype:, multitype:BaseEntityAbstract >
	 */
	public static function getCategories(Product $product, $activeOnly = true, $pageNo = null, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE, $orderBy = array(), &$stats = array())
	{
		return self::getAllByCriteria('productId = ?', array($product->getId()), $activeOnly, $pageNo, $pageSize, $orderBy, $stats);
	}
	/**
	 * removing the relationship
	 * 
	 * @param Product         $product
	 * @param ProductCategory $category
	 */
	public static function remove(Product $product, ProductCategory $category)
	{
		self::deleteByCriteria('productId = ? and categoryId = ?', array($product->getId(), $category->getId()));
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
			$array['product'] = $this->getProduct() instanceof Product ? array('id' => $this->getProduct()->getId(), 'name' => $this->getProduct()->getName()) : null;
			$array['category'] = $this->getCategory() instanceof ProductCategory ? array('id' => $this->getCategory()->getId(), 'name' => $this->getCategory()->getName()) : null;
		}
		return parent::getJson($array, $reset);
	}
	/**
	 * (non-PHPdoc)
	 * @see HydraEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'pro_cat');
	
		DaoMap::setManyToOne('category', 'ProductCategory', 'pro_cat_cate');
		DaoMap::setManyToOne('product', 'Product', 'pro_cat_pro');
		parent::__loadDaoMap();
		DaoMap::createIndex('category');
		DaoMap::createIndex('product');
		DaoMap::commit();
	}
}