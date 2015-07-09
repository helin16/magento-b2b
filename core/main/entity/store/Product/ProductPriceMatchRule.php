<?php
/**
 * Entity for ProductPriceMatchRule
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class ProductPriceMatchRule extends BaseEntityAbstract
{
	/**
	 * The product of the code
	 * 
	 * @var Product
	 */
	protected $product;
	/**
	 * The lower limit of price match limit
	 * 
	 * @var string
	 */
	private $price_from;
	/**
	 * The upper limit of price match limit
	 * 
	 * @var string
	 */
	private $price_to;
	/**
	 * The offset after match price
	 * 
	 * @var string
	 */
	private $offset;
	/**
	 * The PriceMatchCompany of price match limit
	 * 
	 * @var PriceMatchCompany
	 */
	protected $company;
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
	 * @return ProductPriceMatchRule
	 */
	public function setProduct(Product $value)
	{
	    $this->product = $value;
	    return $this;
	}
	/**
	 * getter for price_from
	 *
	 * @return double
	 */
	public function getPrice_from()
	{
	    return $this->price_from;
	}
	/**
	 * Setter for price_from
	 *
	 * @return ProductPriceMatchRule
	 */
	public function setPrice_from($price_from)
	{
	    $this->price_from = $price_from;
	    return $this;
	}
	/**
	 * getter for price_to
	 *
	 * @return double
	 */
	public function getPrice_to()
	{
	    return $this->price_to;
	}
	/**
	 * Setter for price_to
	 *
	 * @return ProductPriceMatchRule
	 */
	public function setPrice_to($price_to)
	{
	    $this->price_to = $price_to;
	    return $this;
	}
	/**
	 * getter for offset
	 *
	 * @return string
	 */
	public function getOffset()
	{
	    return $this->offset;
	}
	/**
	 * Setter for offset
	 *
	 * @return ProductPriceMatchRule
	 */
	public function setOffset($offset)
	{
	    $this->offset = $offset;
	    return $this;
	}
	/**
	 * getter for company
	 *
	 * @return PriceMatchCompany
	 */
	public function getCompany()
	{
		$this->loadManyToOne('company');
	    return $this->company;
	}
	/**
	 * Setter for company
	 *
	 * @return ProductPriceMatchRule
	 */
	public function setcompany($company)
	{
	    $this->company = $company;
	    return $this;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'pro_price_rule');
		DaoMap::setManyToOne('product', 'Product', 'pro_rule_pro');
		DaoMap::setStringType('price_from', 'varchar', '16', true, null);
		DaoMap::setStringType('price_to', 'varchar', '16', true, null);
		DaoMap::setStringType('offset', 'varchar', '16', true, null);
		DaoMap::setManyToOne('company', 'PriceMatchCompany', 'pro_rule_company');
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
			$array['productId'] = $this->getProduct()->getId();
			$array['priceMatchCompany'] = $this->getCompany()->getJson();
		}
		return parent::getJson($array, $reset);
	}
	public static function create(Product $product, PriceMatchCompany $company, $price_from = null, $price_to = null, $offset = null)
	{
		if(trim($price_from) === '')
			$price_from = null;
		if(trim($price_to) === '')
			$price_to = null;
		if(trim($offset) === '')
			$offset = null;
		if(doubleval(str_replace('%', '', $price_from)) < doubleval(0) || doubleval(str_replace('%', '', $price_to)) < doubleval(0))
			throw new Exception('price range limits must be greater or equal than 0, "' . $price_from . '" and "' . $price_to . '" given');
		if(($price_from !== null && !is_numeric(trim(str_replace('%', '', $price_from)))) || ($price_to !== null && !is_numeric(trim(str_replace('%', '', $price_to)))) || ($offset !== null && !is_numeric(trim(str_replace('%', '', $offset)))))
			throw new Exception('price range limits and offset must be percentage or number, "' . $price_from . '" and "' . $price_to . '" and "' . $offset . ' given');
		
		$obj = ($existObj = self::getByProduct($product)) instanceof self ? $existObj : new self();
		$obj->setProduct($product)
			->setcompany($company)
			->setPrice_from($price_from === null ? null : trim($price_from))
			->setPrice_to($price_to === null ? null : trim($price_to))
			->setOffset($offset === null ? null : trim($offset))
			->setActive(true)
			->save();
		
		return $obj;
	}
	public static function getByProduct(Product $product)
	{
		$rules = self::getAllByCriteria('productId = ?', array($product->getId()), false, 1, 1, array('id'=> 'desc'));
		if(count($rules) > 0)
			return $rules[0];
		else return null;
	}
}