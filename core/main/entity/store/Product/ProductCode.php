<?php
/**
 * Entity for Product
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class ProductCode extends BaseEntityAbstract
{
	/**
	 * The product of the code
	 * 
	 * @var Product
	 */
	protected $product;
	/**
	 * The type of the code
	 * 
	 * @var ProductCodeType
	 */
	protected $type;
	/**
	 * The code of the product
	 * 
	 * @var string
	 */
	private $code;
	/**
	 * Getter for product
	 *
	 * @return Product
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
	 * @return ProductCode
	 */
	public function setProduct(Product $value)
	{
	    $this->product = $value;
	    return $this;
	}
	/**
	 * Getter for type
	 *
	 * @return ProductCodeType
	 */
	public function getType() 
	{
		$this->loadManyToOne('type');
	    return $this->type;
	}
	/**
	 * Setter for type
	 *
	 * @param ProductCodeType $value The type
	 *
	 * @return ProductCode
	 */
	public function setType(ProductCodeType $value)
	{
	    $this->type = $value;
	    return $this;
	}
	/** 
	 * Getter for code
	 * 
	 * @return string
	 */
	public function getCode ()
	{
		return $this->code;
	}
	/** 
	 * Setter for code
	 * 
	 * @param string $value
	 * 
	 * @return ProductCode
	 */
	public function setCode($value)
	{
		$this->code = $value;
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
			$array['type'] = $this->getType()->getJson();
		}
		return parent::getJson($array, $reset);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'pro_code');
		DaoMap::setManyToOne('product', 'Product', 'pro_code_pro');
		DaoMap::setManyToOne('type', 'ProductCodeType', 'pro_code_type');
		DaoMap::setStringType('code', 'varchar', 100);
		parent::__loadDaoMap();
		
		DaoMap::createIndex('code');
		DaoMap::commit();
	}
	/**
	 * Creating a product code for a product and type
	 *
	 * @param Product         $product
	 * @param ProductCodeType $type
	 * @param string          $code
	 *
	 * @return ProductCode
	 */
	public static function create(Product $product, ProductCodeType $type, $code)
	{
		$class = __CLASS__;
		$objects = self::getCodes($product, $type, true , 1, 1);
		if($type->getAllowMultiple() !== true && count($objects) > 0)
			throw new EntityException('Code Type(=' . $type->getName() . ') NOT allow multiple and there is one for this product already!');
		$obj = new $class();
		$obj->setProduct($product)
		->setType($type)
		->setCode(trim($code))
		->save();
		return $obj;
	}
	/**
	 * Getting the productcodes
	 *
	 * @param Product         $product
	 * @param ProductCodeType $type
	 * @param string          $activeOnly
	 * @param string          $pageNo
	 * @param array           $pageSize
	 * @param array           $orderBy
	 *
	 * @return Ambigous <multitype:, multitype:BaseEntityAbstract >
	 */
	public static function getCodes(Product $product, ProductCodeType $type, $activeOnly = true, $pageNo = null, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE, $orderBy = array(), &$stats = array())
	{
		return self::getAllByCriteria('productId = ? and typeId = ?', array($product->getId(), $type->getId()), $activeOnly , $pageNo, $pageSize, $orderBy, $stats);
	}
}