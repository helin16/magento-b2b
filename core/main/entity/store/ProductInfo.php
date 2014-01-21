<?php
/**
 * Entity for ProductInfo
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class ProductInfo extends InfoAbstract
{
	/**
	 * The product this info is for
	 * @var Product
	 */
	protected $product;
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
	 * @return ProductInfo
	 */
	public function setProduct($value) 
	{
	    $this->product = $value;
	    return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'pro_info');
		parent::__loadDaoMap();
		DaoMap::commit();
	}
}