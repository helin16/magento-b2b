<?php
/**
 * Entity for SupplierDatefeedRule
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class SupplierDatefeedRule extends BaseEntityAbstract
{
	/**
	 * The supplier of the SupplierDatefeedRule
	 * 
	 * @var Supplier
	 */
	protected $supplier;
	/**
	 * The Manufacturer of the SupplierDatefeedRule
	 * 
	 * @var Manufacturer
	 */
	protected $manufacturer;
	/**
	 * The ProductCategory of the SupplierDatefeedRule
	 * 
	 * @var ProductCategory
	 */
	protected $category;
	/**
	 * The ProductPriceMatchRule of SupplierDatefeedRule
	 * 
	 * @var ProductPriceMatchRule
	 */
	protected $priceMatchRule;
	
	/**
	 * getter for supplier
	 *
	 * @return Supplier
	 */
	public function getSupplier()
	{
		$this->loadManyToOne('supplier');
	    return $this->supplier;
	}
	/**
	 * Setter for supplier
	 *
	 * @return SupplierDatefeedRule
	 */
	public function setSupplier($supplier)
	{
	    $this->supplier = $supplier;
	    return $this;
	}
	/**
	 * getter for manufacturer
	 *
	 * @return Manufacturer
	 */
	public function getManufacturer()
	{
		$this->loadManyToOne('manufacturer');
	    return $this->manufacturer;
	}
	/**
	 * Setter for manufacturer
	 *
	 * @return SupplierDatefeedRule
	 */
	public function setmanufacturer($manufacturer)
	{
	    $this->manufacturer = $manufacturer;
	    return $this;
	}
	/**
	 * getter for category
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
	 * @return SupplierDatefeedRule
	 */
	public function setCategory($category)
	{
	    $this->category = $category;
	    return $this;
	}
	/**
	 * getter for priceMatchRule
	 *
	 * @return ProductPriceMatchRule
	 */
	public function getPriceMatchRule()
	{
		$this->loadManyToOne('priceMatchRule');
	    return $this->priceMatchRule;
	}
	/**
	 * Setter for priceMatchRule
	 *
	 * @return SupplierDatefeedRule
	 */
	public function setpriceMatchRule($priceMatchRule)
	{
	    $this->priceMatchRule = $priceMatchRule;
	    return $this;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'sup_fd_rule');
		DaoMap::setManyToOne('supplier', 'Supplier', 'sup_fd_rule_sup');
		DaoMap::setManyToOne('manufacturer', 'Manufacturer', 'sup_fd_rule_man', true);
		DaoMap::setManyToOne('category', 'ProductCategory', 'sup_fd_rule_pro_cate', true);
		DaoMap::setManyToOne('priceMatchRule', 'ProductPriceMatchRule', 'sup_fd_rule_pro_price_rule', true);
		
		DaoMap::createIndex('supplier');
		
		parent::__loadDaoMap();
		
		DaoMap::commit();
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
			$array['supplier'] = $this->getSupplier() instanceof Supplier ? $this->getSupplier()->getJson() : '';
			$array['manufacturer'] = $this->getManufacturer() instanceof Manufacturer ? $this->getManufacturer()->getJson() : '';
			$array['category'] = $this->getCategory() instanceof ProductCategory ? $this->getCategory()->getJson() : '';
			$array['priceMatchRule'] = $this->getPriceMatchRule() instanceof ProductPriceMatchRule ? $this->getPriceMatchRule()->getJson() : '';
		}
		return parent::getJson($array, $reset);
	}
	public static function create(Supplier $supplier, $manufacturer = null, $category = null, $priceMatchRule = null)
	{
		if($manufacturer !== null && !$manufacturer instanceof Manufacturer)
			throw new Exception('Invalid manufacture passed in. It must be a null or instance of Manufacturer. "' . $manufacturer . '" passed in.');
		if($category !== null && !$category instanceof ProductCategory)
			throw new Exception('Invalid category passed in. It must be a null or instance of ProductCategory. "' . $category . '" passed in.');
		if($priceMatchRule !== null && !$priceMatchRule instanceof ProductPriceMatchRule)
			throw new Exception('Invalid priceMatchRule passed in. It must be a null or instance of ProductPriceMatchRule. "' . $priceMatchRule . '" passed in.');
		
		$existObj = self::getAllByCriteria('supplierId = ? and manufacturerId = ? and categoryId = ?', array($supplier->getId(), $manufacturer instanceof Manufacturer ? $manufacturer->getId() : null, $priceMatchRule instanceof ProductPriceMatchRule ? $priceMatchRule->getId() : null), false, 1, 1, array('id'=>'desc'));
		
		$obj = $existObj > 0 ? $existObj[0] : new self();
		$obj->setSupplier($supplier)
			->setmanufacturer($manufacturer)
			->setCategory($category)
			->setpriceMatchRule($priceMatchRule)
			->setActive(true) // inactive obj which meet criteria will be also an exist obj
			->save();
		
		return $obj;
	}
}