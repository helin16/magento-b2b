<?php
/**
 * Entity for ProductPrice
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class ProductPrice extends BaseEntityAbstract
{
	/**
	 * The product this price is for
	 * 
	 * @var Product
	 */
	protected $product;
	/**
	 * What type of price this price is
	 * 
	 * @var ProductPriceType
	 */
	protected $type;
	/**
	 * The actual price
	 * 
	 * @var double
	 */
	private $price;
	/**
	 * The start of this price
	 * 
	 * @var UDate
	 */
	private $start;
	/**
	 * The end of this price
	 * 
	 * @var UDate
	 */
	private $end;
	/**
	 * constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->start = UDate::zeroDate();
		$this->end = UDate::maxDate();
	}
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
	 * Setter for the product
	 * 
	 * @param Product $product
	 * 
	 * @return ProductPrice
	 */
	public function setProduct(Product $product)
	{
		$this->product = $product;
		return $this;
	}
	/**
	 * Getter for type
	 * 
	 * @return ProductPriceType
	 */
	public function getType()
	{
		$this->loadManyToOne('type');
		return $this->type;
	}
	/**
	 * Setter for the type
	 * 
	 * @param ProductPriceType $value
	 * 
	 * @return ProductPrice
	 */
	public function setType(ProductPriceType $value)
	{
		$this->type = $value;
		return $this;
	}
	/**
	 * Getter for price
	 * 
	 * @return double
	 */
	public function getPrice()
	{
		return $this->price;
	}
	/**
	 * Setter for the price
	 * 
	 * @param double $value
	 * 
	 * @return ProductPrice
	 */
	public function setPrice($value)
	{
		$this->price = $value;
		return $this;
	}
	/**
	 * Getter for start
	 * 
	 * @return start
	 */
	public function getStart()
	{
		return new UDate(trim($this->start));
	}
	/**
	 * Setter for the start
	 * 
	 * @param mixed $value
	 * 
	 * @return ProductPrice
	 */
	public function setStart($value)
	{
		$this->start = new UDate(trim($value));
		return $this;
	}
	/**
	 * Getter for end
	 * 
	 * @return UDate
	 */
	public function getEnd()
	{
		return $this->end;
	}
	/**
	 * Setter for the end
	 * 
	 * @param string $value
	 * 
	 * @return ProductPrice
	 */
	public function setEnd($value)
	{
		$this->end = new UDate(trim($value));
		return $this;
	}
	/**
	 * Creating the product based on sku
	 * 
	 * @param string $name        The name of the productpricetype
	 * @param string $description The decription of the productpricetype
	 * 
	 * @return Ambigous <Product, Ambigous, NULL, BaseEntityAbstract>
	 */
	public static function create(Product $product, ProductPriceType $type, $price, $start = null, $end = null)
	{
		$class = __CLASS__;
		$obj = new $class();
		$obj->setProduct($product)
			->setType($type)
			->setPrice(trim($price));
		if (($start = trim($start)) !== '')
			$obj->setStart($start);
		if (($end = trim($end)) !== '')
			$obj->setEnd($end);
		return FactoryAbastract::dao($class)->save($obj);
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'pro_price');
		DaoMap::setManyToOne('product', 'Product', 'pro_price_pro');
		DaoMap::setManyToOne('type', 'ProductPriceType', 'pro_price_type');
		DaoMap::setIntType('price', 'double', '10,4');
		DaoMap::setDateType('start');
		DaoMap::setDateType('end', 'datetime', false, trim(UDate::maxDate()));
		parent::__loadDaoMap();
		
		DaoMap::createIndex('price');
		DaoMap::createIndex('start');
		DaoMap::createIndex('end');
		DaoMap::commit();
	}
}