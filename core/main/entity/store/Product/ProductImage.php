<?php
/**
 * Entity for ProductImage
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class ProductImage extends BaseEntityAbstract
{
	/**
	 * The asset id of the image
	 * 
	 * @var string
	 */
	private $imageAssetId;
	/**
	 * The product of this image
	 * 
	 * @var Product
	 */
	protected $product;
	/**
	 * Getter for imageAssetId
	 *
	 * @return 
	 */
	public function getImageAssetId() 
	{
	    return $this->imageAssetId;
	}
	/**
	 * Setter for imageAssetId
	 *
	 * @param string $value The imageAssetId
	 *
	 * @return ProductImage
	 */
	public function setImageAssetId($value) 
	{
	    $this->imageAssetId = $value;
	    return $this;
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
	 * Setter for product
	 *
	 * @param Product $value The product
	 *
	 * @return ProductImage
	 */
	public function setProduct(Product $value) 
	{
	    $this->product = $value;
	    return $this;
	}
	/**
	 * Creating a product image 
	 * 
	 * @param Product $product The product
	 * @param Asset   $asset   The asset of the image file
	 * 
	 * @return ProductImage
	 */
	public static function create(Product $product, Asset $asset)
	{
		$class = __CLASS__;
		$obj = new $class;
		$obj->setProduct($product)
			->setImageAssetId(trim($asset->getAssetId()));
		return FactoryAbastract::dao($class)->save($obj);
	}
	/**
	 * delete a product image 
	 * 
	 * @param Product $product The product
	 * @param Asset   $asset   The asset of the image file
	 * 
	 * @return ProductImage
	 */
	public static function remove(Product $product, Asset $asset)
	{
		$class = __CLASS__;
		FactoryAbastract::dao($class)->deleteByCriteria('productId = ? and imageAssetId = ?', array(trim($product->getId()), trim($asset->getAssetId())));
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
			$array['path'] = (($asset = Asset::getAsset($this->getImageAssetId())) instanceof Asset) ? $asset->getUrl() : null;
		}
		return parent::getJson($array, $reset);
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::__toString()
	 */
	public function __toString()
	{
		return trim($this->getImageAssetId());
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'pro_img');
		DaoMap::setManyToOne('product', 'Product', 'pro_img_pro');
		DaoMap::setStringType('imageAssetId', 'varchar', 20);
		parent::__loadDaoMap();
	
		DaoMap::createIndex('imageAssetId');
		DaoMap::commit();
	}
}