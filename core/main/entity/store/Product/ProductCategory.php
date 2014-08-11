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
	protected $root;
	/**
	 * The position of the category in the root category
	 * 
	 * @var string
	 */
	private $position;
	/**
	 * The description of this category
	 * 
	 * @var string
	 */
	private $description;
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
	public function setParent(ProductCategory $value) 
	{
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
		DaoMap::begin($this, 'pro_cate');
		DaoMap::setStringType('name', 'varchar', 100);
		DaoMap::setStringType('description', 'varchar', 255);
		DaoMap::setManyToOne('parent', __CLASS__, 'pro_cate_parent');
		DaoMap::setManyToOne('root', __CLASS__, 'pro_cate_root');
		DaoMap::setStringType('position', 'varchar', 255);
		parent::__loadDaoMap();
		
		DaoMap::createUniqueIndex('name');
		DaoMap::createIndex('position');
		DaoMap::commit();
	}
}