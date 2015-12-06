<?php
/**
 * Entity for ProductCategory
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class ProductCategory extends BaseEntityAbstract
{
	const POSITION_SEPARATOR = '/';
	/**
	 * has the parent of the category changed
	 * 
	 * @var bool
	 */
	private $_isParentChanged = false;
	/**
	 * The cache of the name path
	 * 
	 * @var string
	 */
	private $_namePathCache = '';
	/**
	 * The name of the product
	 * 
	 * @var string
	 */
	private $name;
	/**
	 * The parent category of this category
	 * 
	 * @var ProductCategory
	 */
	protected $parent = null;
	/**
	 * The root category of this category
	 * 
	 * @var ProductCategory
	 */
	protected $root = null;
	/**
	 * The position of the category in the root category
	 * 
	 * @var string
	 */
	private $position = '';
	/**
	 * The description of this category
	 * 
	 * @var string
	 */
	private $description;
	/**
	 * The id of the customer in magento
	 *
	 * @var int
	 */
	private $mageId = 0;
	/**
	 * Whether this order is imported from B2B
	 *
	 * @var bool
	 */
	private $isFromB2B = false;
	/**
	 * Magento:: is_anchor
	 * 
	 * @var bool
	 */
	private $isAnchor = false;
	/**
	 * Magento:: include_in_menu
	 * 
	 * @var bool
	 */
	private $includeInMenu = false;
	/**
	 * Mangento:: url_key
	 * 
	 * @var string
	 */
	private $urlKey = '';
	/**
	 * the product attribute set
	 *
	 * @var ProductAttributeSet
	 */
	protected $productAttributeSet = null;
	/**
	 * Getter for isAnchor
	 *
	 * @return bool
	 */
	public function getIsAnchor() 
	{
	    return $this->isAnchor;
	}
	/**
	 * Setter for isAnchor
	 *
	 * @param bool $value The isAnchor
	 *
	 * @return ProductCategory
	 */
	public function setIsAnchor($value) 
	{
	    $this->isAnchor = $value;
	    return $this;
	}
	/**
	 * Getter for includeInMenu
	 *
	 * @return bool
	 */
	public function getIncludeInMenu() 
	{
	    return $this->includeInMenu;
	}
	/**
	 * Setter for includeInMenu
	 *
	 * @param bool $value The includeInMenu
	 *
	 * @return ProductCategory
	 */
	public function setIncludeInMenu($value) 
	{
	    $this->includeInMenu = $value;
	    return $this;
	}
	/**
	 * Getter for urlKey
	 *
	 * @return 
	 */
	public function getUrlKey() 
	{
	    return $this->urlKey;
	}
	/**
	 * Setter for urlKey
	 *
	 * @param string $value The urlKey
	 *
	 * @return ProductCategory
	 */
	public function setUrlKey($value) 
	{
	    $this->urlKey = $value;
	    return $this;
	}
	/**
	 * Getter for name
	 *
	 * @return ProductCategory
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
	 * @return ProductCategory
	 */
	public function setName($value) 
	{
	    $this->name = $value;
	    return $this;
	}
	/**
	 * Getter for description
	 *
	 * @return ProductCategory
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
	 * @return ProductCategory
	 */
	public function setDescription($value) 
	{
	    $this->description = $value;
	    return $this;
	}
	/**
	 * Getter for parent
	 *
	 * @return ProductCategory
	 */
	public function getParent() 
	{
		$this->loadManyToOne('parent');
	    return $this->parent;
	}
	/**
	 * Setter for parent
	 *
	 * @param ProductCategory $value The parent
	 *
	 * @return ProductCategory
	 */
	public function setParent(ProductCategory $value = null) 
	{
	    $this->_isParentChanged = !(($value === null && $this->getParent() === null) || ($value instanceof ProductCategory && $this->getParent() instanceof ProductCategory && $value->getId() === $this->getParent()->getId()));
	    $this->parent = $value;
	    return $this;
	}
	/**
	 * Getter for root
	 *
	 * @return ProductCategory
	 */
	public function getRoot() 
	{
		$this->loadManyToOne('root');
	    return $this->root;
	}
	/**
	 * Getter for position
	 *
	 * @return ProductCategory
	 */
	public function getPosition() 
	{
	    return $this->position;
	}
	/**
	 * Setter for position
	 *
	 * @param string $value The position
	 *
	 * @return ProductCategory
	 */
	public function setPosition($value) 
	{
	    $this->position = $value;
	    return $this;
	}
	/**
	 * Setter for root
	 *
	 * @param ProductCategory $value The root
	 *
	 * @return ProductCategory
	 */
	public function setRoot(ProductCategory $value) 
	{
	    $this->root = $value;
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
	 * Getter for mageId
	 *
	 * @return string
	 */
	public function getMageId()
	{
		return $this->mageId;
	}
	/**
	 * Setter for mageId
	 *
	 * @param int $value The mageId
	 *
	 * @return Customer
	 */
	public function setMageId($value)
	{
		$this->mageId = $value;
		return $this;
	}
	/**
	 * Getter for attributeSet
	 *
	 * @return ProductAttributeSet
	 */
	public function getProductAttributeSet()
	{
		$this->loadManyToOne('productAttributeSet');
		return $this->productAttributeSet;
	}
	/**
	 * Setter for productAttributeSet
	 *
	 * @param ProductAttributeSet $value The attributeSet
	 *
	 * @return Product
	 */
	public function setProductAttributeSet(ProductAttributeSet $value = null)
	{
		$this->productAttributeSet = $value;
		return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::postSave()
	 */
	public function postSave() 
	{
		$class = get_class($this);
		$root = (!$this->getParent() instanceof $class ? $this : $this->getParent()->getRoot());
		$position = (!$this->getParent() instanceof $class ? $this->getId() : $this->getParent()->getPosition() . self::POSITION_SEPARATOR . $this->getId());
		$this->setRoot($root)
			->setPosition($position);
		self::updateByCriteria('rootId = ?, position = ? ', 'id = ?', array($root->getId(), trim($position), $this->getId()));
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
	 * Getting the name path of the category
	 * 
	 * @return string
	 */
	public function getNamePath()
	{
		if($this->_isParentChanged === true || trim($this->_namePathCache) === '')
		{
			$ids = explode(self::POSITION_SEPARATOR, trim($this->getPosition()));
			$names = array();
			foreach($ids as $id)
				$names[] =  self::get($id)->getName();
			$this->_namePathCache = implode(self::POSITION_SEPARATOR, $names);
		}
		return trim($this->_namePathCache);
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::preSave()
	 */
	public function preSave()
	{
		if(($mageId = trim($this->getMageId())) !== '' && self::countByCriteria('mageId = ? and id != ?', array($mageId, trim($this->getId()))) > 0)
			throw new Exception('mageId(=' . $mageId . ') has existed already!');
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::getJson()
	 */
	public function getJson($extra = array(), $reset = false)
	{
		$class = __CLASS__;
		$array = $extra;
		if(!$this->isJsonLoaded($reset))
		{
			$array['parent'] = $this->getParent() instanceof $class ? array('id'=> $this->getParent()->getId()) : null;
			$array['root'] = array('id'=> $this->getRoot()->getId());
			$array['namePath'] = $this->getNamePath();
			$array['noOfChildren'] = self::countByCriteria('parentId = ? and active = 1', array($this->getId()));
			$array['noOfProducts'] = Product_Category::countByCriteria('categoryId = ? and active = 1', array($this->getId()));
			$array['productAttributeSet'] = $this->getProductAttributeSet() instanceof ProductAttributeSet ? $this->getProductAttributeSet()->getJson() : null;
		}
		return parent::getJson($array, $reset);
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'pro_cate');
		DaoMap::setStringType('name', 'varchar', 100);
		DaoMap::setStringType('description', 'varchar', 255);
		DaoMap::setManyToOne('parent', __CLASS__, 'pro_cate_parent', true);
		DaoMap::setManyToOne('root', __CLASS__, 'pro_cate_root', true);
		DaoMap::setStringType('position', 'varchar', 255);
		DaoMap::setIntType('mageId');
		DaoMap::setManyToOne('productAttributeSet', 'ProductAttributeSet', 'pro_cate_as', true);
		DaoMap::setBoolType('isFromB2B');
		DaoMap::setBoolType('isAnchor');
		DaoMap::setBoolType('includeInMenu');
		DaoMap::setStringType('urlKey', 'varchar', 50);
		parent::__loadDaoMap();
		
		DaoMap::createUniqueIndex('name');
		DaoMap::createIndex('position');
		DaoMap::createIndex('isFromB2B');
		DaoMap::createIndex('mageId');
		DaoMap::createIndex('isAnchor');
		DaoMap::createIndex('includeInMenu');
		DaoMap::createIndex('urlKey');
		DaoMap::commit();
	}
	/**
	 * Creating a category
	 *
	 * @param string          $name
	 * @param string          $description
	 * @param ProductCategory $parent
	 *
	 * @return Ambigous <GenericDAO, BaseEntityAbstract>
	 */
	public static function create($name, $description, ProductCategory $parent = null, $isFromB2B = false, $mageId = 0)
	{
		$class = __CLASS__;
		$category = new $class();
		$category->setName(trim($name))
			->setDescription(trim($description))
			->setParent($parent)
			->setIsFromB2B($isFromB2B)
			->setMageId($mageId)
			->save();
		return $category;
	}
	/**
	 * Getting the product category by mage id
	 * 
	 * @param unknown $mageId
	 * 
	 * @return Ambigous <NULL, BaseEntityAbstract>
	 */
	public static function getByMageId($mageId)
	{
		$categories = self::getAllByCriteria('mageId = ?', array(trim($mageId)), false, 1, 1);
		return count($categories) === 0 ? null : $categories[0];
	}
	/**
	 * Get all children for a Product Category, including grand children, grand grand children etc
	 * 
	 * @return array ProductCategory
	 */
	public function getAllChildrenIds(&$result = null, $starting = 0)
	{
		if($starting === 0) // initiate recursive function
		{
			$starting = $this->getId();
			$result = array();
		}
		else $result[] = $this->getId();
		
		$children = ProductCategory::getAllByCriteria('parentId = ?', array($this->getId()));
		foreach ($children as $child)
			$child->getAllChildrenIds($result, $starting);
		
		return array_unique($result);
	}
}
