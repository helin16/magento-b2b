<?php
/** PreferredLocation Entity
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class PreferredLocation extends BaseEntityAbstract
{
	/**
	 * The location of the preferred
	 * 
	 * @var Location
	 */
	protected $location;
	/**
	 * The product
	 * 
	 * @var Product
	 */
	protected $product;
	/**
	 * The type of the preference
	 * 
	 * @var PreferredLocationType
	 */
	protected $type;
	/**
	 * Getter for location
	 * 
	 * @return location
	 */
	public function getLocation()
	{
		$this->loadManyToOne('location');
		return $this->location;
	}
	/**
	 * Setter for the location
	 * 
	 * @param mixed $value
	 * 
	 * @return PreferredLocation
	 */
	public function setLocation(Location $value)
	{
		$this->location = $value;
		return $this;
	}
	/**
	 * Getter for product
	 * 
	 * @return product
	 */
	public function getProduct()
	{
		$this->loadManyToOne('product');
		return $this->product;
	}
	/**
	 * Setter for the product
	 * 
	 * @param mixed $value
	 * 
	 * @return PreferredLocation
	 */
	public function setProduct(Product $value)
	{
		$this->product = $value;
		return $this;
	}
	/**
	 * Getter for type
	 * 
	 * @return PreferredLocationType
	 */
	public function getType()
	{
		$this->loadManyToOne('type');
		return $this->type;
	}
	/**
	 * Setter for the type
	 * 
	 * @param mixed $value
	 * 
	 * @return PreferredLocation
	 */
	public function setType($value)
	{
		$this->type = $value;
		return $this;
	}
	/**
	 * Creating the Location based on sku
	 * 
	 * @param Location $location The sku of the Location
	 * @param Product  $product  The product
	 * 
	 * @return Ambigous <Location, Ambigous, NULL, BaseEntityAbstract>
	 */
	public static function create(Location $location, Product $product, PreferredLocationType $type)
	{
		$obj = new PreferredLocation();
		$obj->setLocation($location)
			->setProduct($product)
			->setType($type)
			->save();
		return $obj;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'preloc');
		
		DaoMap::setManyToOne('location', 'Location', 'preloc_loc');
		DaoMap::setManyToOne('product', 'Product', 'preloc_pro');
		DaoMap::setManyToOne('type', 'PreferredLocationType', 'preloc_loc_type');
		parent::__loadDaoMap();
		
		DaoMap::commit();
	}
	/**
	 * Getting all the preferred locations
	 * 
	 * @param Product $product
	 * @param PreferredLocationType $type
	 * @param string $activeOnly
	 * @param string $pageNo
	 * @param unknown $pageSize
	 * @param unknown $orderBy
	 * @param unknown $stats
	 * @return Ambigous <Ambigous, multitype:, multitype:BaseEntityAbstract >
	 */
	public static function getPreferredLocations(Product $product, PreferredLocationType $type = null, $activeOnly = true, $pageNo = null, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE, $orderBy = array(), &$stats = array())
	{
		$where = array('productId = ? ');
		$params = array($product->getId());
		if($type instanceof PreferredLocationType)
		{
			$where[] = 'typeId = ?';
			$params[] = $type->getId();
		}
		return self::getAllByCriteria(implode(' AND ', $where), $params, $activeOnly, $pageNo , $pageSize, $orderBy, $stats);
	}
}