<?php
/**
 * Entity for SupplierCode
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class SupplierCode extends BaseEntityAbstract
{
	/**
	 * The supplier
	 *
	 * @var Supplier
	 */
	protected $supplier;
	/**
	 * The product
	 *
	 * @var Product
	 */
	protected $product;
	/**
	 * The code
	 *
	 * @var string
	 */
	private $code;
	/**
	 * The qty from the suppliers data-feed
	 *
	 * @var int
	 */
	private $canSupplyQty = 0;
	/**
	 * Getter for supplier
	 *
	 * @return
	 */
	public function getSupplier()
	{
		$this->loadManyToOne('supplier');
	    return $this->supplier;
	}
	/**
	 * Setter for supplier
	 *
	 * @param Supplier $value The supplier
	 *
	 * @return SupplierCode
	 */
	public function setSupplier(Supplier $value)
	{
	    $this->supplier = $value;
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
	 * Getter for code
	 *
	 * @return
	 */
	public function getCode()
	{
	    return $this->code;
	}
	/**
	 * Setter for code
	 *
	 * @param string $value The code
	 *
	 * @return SupplierCode
	 */
	public function setCode($value)
	{
	    $this->code = $value;
	    return $this;
	}
	/**
	 * Getter for canSupplyQty
	 *
	 * @return int
	 */
	public function getCanSupplyQty()
	{
	    return $this->canSupplyQty;
	}
	/**
	 * Setter for canSupplyQty
	 *
	 * @param int $value The canSupplyQty
	 *
	 * @return ProductCode
	 */
	public function setCanSupplyQty($value)
	{
	    $this->canSupplyQty = $value;
	    return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::getJson()
	 */
	public function getJson($extra = array(), $reset = false)
	{
		$array = $extra;
		if (!$this->isJsonLoaded($reset)) {
			$array['product'] = $this->getProduct() instanceof Product ? array('id'=>$this->getProduct()->getId()) : null;
			$array['supplier'] = $this->getSupplier() instanceof Supplier ? $this->getSupplier()->getJson() : null;
		}
		return parent::getJson($array, $reset);
	}
	/**
	 * (non-PHPdoc)
	 * @see HydraEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'sup_code');

		DaoMap::setManyToOne('supplier', 'Supplier', 'scode_sup');
		DaoMap::setManyToOne('product', 'Product', 'scode_pro');
		DaoMap::setStringType('code', 'varchar', 100);
		DaoMap::setIntType('canSupplyQty', 'int', 10);

		parent::__loadDaoMap();

		DaoMap::createIndex('code');
		DaoMap::createIndex('canSupplyQty');
		DaoMap::commit();
	}
	/**
	 * Creating a supplier code
	 *
	 * @param Product  $product
	 * @param Supplier $supplier
	 * @param string   $code
	 *
	 * @return SupplierCode
	 */
	public static function create(Product $product, Supplier $supplier, $code)
	{
		$class = __CLASS__;
		$objects = self::getAllByCriteria('productId = ? and supplierId = ? and code like ?', array($product->getId(), $supplier->getId(), trim($code)), true, 1, 1);
		$obj = (count($objects) > 0 ? $objects[0] : new $class());
		return $obj->setProduct($product)
		->setSupplier($supplier)
		->setCode(trim($code))
		->save();
	}
}