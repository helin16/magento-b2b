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
	 * @return UDate
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
		return $obj->save();
	}
	/**
	 * Getting the price object via product or type
	 * 
	 * @param Product          $product
	 * @param ProductPriceType $type
	 * @param string           $startS
	 * @param string           $startE
	 * @param string           $endS
	 * @param string           $endE
	 * @param int              $pageNo
	 * @param int              $pageSize
	 * @param array            $orderBy
	 * @throws EntityException
	 * @return Ambigous <multitype:, multitype:BaseEntityAbstract >
	 */
	public static function getPrices(Product $product = null, ProductPriceType $type = null, $startS = '', $startE = '', $endS = '', $endE = '', $pageNo = null, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE, $orderBy = array(), &$stats = array())
	{
		$class = __CLASS__;
		if(!$product instanceof Product && !$type instanceof ProductPriceType)
			throw new EntityException('At least one of them is required for getting the prices: Product or PriceType');
		$where = array('active = 1');
		$params = array();
		if($product instanceof Product)
		{
			$where[] = 'productId = ?';
			$params[] = $product->getId();
		}
		if($type instanceof ProductPriceType)
		{
			$where[] = 'typeId = ?';
			$params[] = $type->getId();
		}
		if(($startS = trim($startS)) !== '')
		{
			$where[] = 'start >= ?';
			$params[] = $startS;
		}
		if(($startE = trim($startE)) !== '')
		{
			$where[] = 'start <= ?';
			$params[] = $startE;
		}
		if(($endS = trim($endS)) !== '')
		{
			$where[] = 'end >= ?';
			$params[] = $endS;
		}
		if(($endE = trim($endE)) !== '')
		{
			$where[] = 'end <= ?';
			$params[] = $endE;
		}
		return self::getAllByCriteria(implode(' AND ', $where), $params, true, $pageNo, $pageSize, $orderBy, $stats);
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
			$array['type'] = $this->getType()->getJson();
		}
		return parent::getJson($array, $reset);
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::preSave()
	 */
	public function preSave()
	{
		if($this->getStart()->afterOrEqualTo($this->getEnd()))
			throw new EntityException('Error: Price(ID=' . $this->getId() . ') start(=' . $this->getStart() . ') must be earlier than end(=' . $this->getEnd() . ')!');
		
		$where = 'active = 1 AND productId = :pId AND typeId = :typeId AND ( (start <= :start and :end <= end) or (:start <= start and :end <= end) or (start <= :start and end <= :end) or (:start <= start and end <= :end))';
		$params = array('pId'=> $this->getProduct()->getId() , 'typeId' => $this->getType()->getId(), 'start' => trim($this->getStart()), 'end' => trim($this->getEnd()));
		if(($id = trim($this->getId())) !== '')
		{
			$where .= ' AND id != :id';
			$params['id'] = $id;
		}
		if(ProductPrice::countByCriteria($where, $params) > 0)
			throw new EntityException('There is already a price for this product that covers time from "' . $this->getStart() . '" to "' . $this->getEnd() . '"');
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
		DaoMap::setDateType('start', 'datetime', false, trim(UDate::zeroDate()));
		DaoMap::setDateType('end', 'datetime', false, trim(UDate::maxDate()));
		parent::__loadDaoMap();
		
		DaoMap::createIndex('price');
		DaoMap::createIndex('start');
		DaoMap::createIndex('end');
		DaoMap::commit();
	}
}