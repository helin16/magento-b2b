<?php
/**
 * Entity for ProductAgeingLog
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class ProductAgeingLog extends InfoEntityAbstract
{
	/**
	 * Product
	 * @var Product
	 */
	protected $product;
	/**
	 * lastPurchaseTime
	 * @var UDate
	 */
	protected $lastPurchaseTime;
	/**
	 * ReceivingItem
	 * @var ReceivingItem
	 */
	protected $receivingItem = null;
	/**
	 * ProductQtyLog
	 * @var ProductQtyLog
	 */
	protected $productQtyLog;
	/**
	 * comments
	 * @var string
	 */
	private $comments;
	/**
	 * Getter for product
	 *
	 * @return product
	 */
	public function getproduct()
	{
		$this->loadManyToOne('product');
		return $this->product;
	}
	/**
	 * Setter for the product
	 *
	 * @param Product $value
	 *
	 * @return ProductQtyLog
	 */
	public function setProduct($value)
	{
		$this->product = $value;
		return $this;
	}
	/**
	 * getter for lastPurchaseTime
	 *
	 * @return UDate
	 */
	public function getLastPurchaseTime()
	{
		return $this->lastPurchaseTime;
	}
	/**
	 * Setter for lastPurchaseTime
	 *
	 * @return ProductAgeingLog
	 */
	public function setLastPurchaseTime($lastPurchaseTime)
	{
		$this->lastPurchaseTime = $lastPurchaseTime;
		return $this;
	}
	/**
	 * getter for receivingItem
	 *
	 * @return ReceivingItem
	 */
	public function getReceivingItem()
	{
		$this->loadManyToOne('receivingItem');
		return $this->receivingItem;
	}
	/**
	 * Setter for receivingItem
	 *
	 * @return ProductAgeingLog
	 */
	public function setReceivingItem($receivingItem)
	{
		$this->receivingItem = $receivingItem;
		return $this;
	}
	/**
	 * getter for productQtyLog
	 *
	 * @return ProductQtyLog
	 */
	public function getProductQtyLog()
	{
		$this->loadManyToOne('productQtyLog');
		return $this->productQtyLog;
	}
	/**
	 * Setter for productQtyLog
	 *
	 * @return ProductAgeingLog
	 */
	public function setProductQtyLog($productQtyLog)
	{
		$this->productQtyLog = $productQtyLog;
		return $this;
	}
	/**
	 * Getter for comments
	 *
	 * @return string
	 */
	public function getComments()
	{
		return $this->comments;
	}
	/**
	 * Setter for the comments
	 *
	 * @param mixed $value
	 *
	 * @return ProductQtyLog
	 */
	public function setComments($value)
	{
		$this->comments = $value;
		return $this;
	}
	/* (non-PHPdoc)
	 * @see BaseEntityAbstract::preSave()
	 */
	public function preSave()
	{
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
			$array['receivingItem'] = $this->getReceivingItem() instanceof ReceivingItem ? $this->getReceivingItem()->getJson() : '';
			$array['productQtyLog'] = $this->getProductQtyLog() instanceof ProductQtyLog ? $this->getProductQtyLog()->getJson() : '';
		}
		return parent::getJson($array, $reset);
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'pal');
		DaoMap::setManyToOne('product', 'Product', 'pal_pro');
		DaoMap::setDateType('lastPurchaseTime');
		DaoMap::setManyToOne('receivingItem', 'ReceivingItem', 'pal_pro', true);
		DaoMap::setManyToOne('productQtyLog', 'ProductQtyLog', 'pal_pql');
		DaoMap::setStringType('comments', 'varchar', 255);
		parent::__loadDaoMap();

		DaoMap::commit();
	}
	/**
	 *
	 * @param Product				$product
	 * @param UDate					$lastPurchaseTime
	 * @param ReceivingItem			$receivingItem
	 * @param ProductQtyLog			$productQtyLog
	 * @param string				$comments
	 * @return Ambigous <BaseEntityAbstract, GenericDAO>
	 */
	public static function create(ProductQtyLog $productQtyLog, $comments = '')
	{
		$log = new ProductAgeingLog();
		$log->setProduct($product)
			->setLastPurchaseTime($lastPurchaseTime)
			->setReceivingItem($receivingItem)
			->setProductQtyLog($productQtyLog)
			->setComments($comments);
		return $log->save();
	}
}