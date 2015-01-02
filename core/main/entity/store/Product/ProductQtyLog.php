<?php
/**
 * Entity for ProductQtyLog
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class ProductQtyLog extends InfoEntityAbstract
{
	/**
	 * Product
	 * @var Product
	 */
	protected $product;
	/**
	 * log for stock on PO
	 * @var int
	 */
	private $stockOnPO;
	/**
	 * log for stock on hand
	 * @var int
	 */
	private $stockOnHand;
	/**
	 * log for stock on order
	 * @var int
	 */
	private $stockOnOrder;
	/**
	 * The total value for all stock on hand units
	 * 
	 * @var double
	 */
	private $totalOnHandValue = 0;
	/**
	 * comments
	 * @var string
	 */
	private $comments;
	/**
	 * entity name
	 * @var string
	 */
	private $entityName;
	/**
	 * entity id
	 * @var int
	 */
	private $entityId;
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
	 * Getter for stockOnPO
	 * 
	 * @return int
	 */
	public function getStockOnPO()
	{
		return $this->stockOnPO;
	}
	/**
	 * Setter for the stockOnPO
	 * 
	 * @param int $value
	 * 
	 * @return ProductQtyLog
	 */
	public function setStockOnPO($value)
	{
		$this->stockOnPO = $value;
		return $this;
	}
	/**
	 * Getter for stockOnHand
	 * 
	 * @return int
	 */
	public function getStockOnHand()
	{
		return $this->stockOnHand;
	}
	/**
	 * Setter for the stockOnHand
	 * 
	 * @param mixed $value
	 * 
	 * @return ProductQtyLog
	 */
	public function setStockOnHand($value)
	{
		$this->stockOnHand = $value;
		return $this;
	}
	/**
	 * Getter for stockOnOrder
	 * 
	 * @return int
	 */
	public function getStockOnOrder()
	{
		return $this->stockOnOrder;
	}
	/**
	 * Setter for the stockOnOrder
	 * 
	 * @param mixed $value
	 * 
	 * @return ProductQtyLog
	 */
	public function setStockOnOrder($value)
	{
		$this->stockOnOrder = $value;
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
	/**
	 * Getter for entityName
	 * 
	 * @return string
	 */
	public function getEntityName()
	{
		return $this->entityName;
	}
	/**
	 * Setter for the entityName
	 * 
	 * @param mixed $value
	 * 
	 * @return ProductQtyLog
	 */
	public function setEntityName($value)
	{
		$this->entityName = $value;
		return $this;
	}
	/**
	 * Getter for entityId
	 * 
	 * @return int
	 */
	public function getEntityId()
	{
		return $this->entityId;
	}
	/**
	 * Setter for the entityId
	 * 
	 * @param int $value
	 * 
	 * @return ProductQtyLog
	 */
	public function setEntityId($value)
	{
		$this->entityId = $value;
		return $this;
	}
	/**
	 * Getter for totalOnHandValue
	 *
	 * @return double
	 */
	public function getTotalOnHandValue  ()
	{
		return $this->totalOnHandValue ;
	}
	/**
	 * Setter for totalOnHandValue
	 *
	 * @param double $value
	 *
	 * @return Product
	 */
	public function setTotalOnHandValue ($value )
	{
		$this->totalOnHandValue = $value;
		return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'pql');
		DaoMap::setManyToOne('product', 'Product', 'pql_pro', true);
		DaoMap::setIntType('stockOnHand', 'int', 10, false);
		DaoMap::setIntType('totalOnHandValue', 'double', '10,4');
		DaoMap::setIntType('totalInPartsValue', 'double', '10,4');
		DaoMap::setIntType('stockOnOrder', 'int', 10, false);
		DaoMap::setIntType('stockOnPO', 'int', 10, false);
		DaoMap::setIntType('stockInParts', 'int', 10, false);
		DaoMap::setIntType('stockInRMA', 'int', 10, false);
		DaoMap::setStringType('comments', 'varchar', 255);
		DaoMap::setStringType('entityName', 'varchar', 100);
		DaoMap::setIntType('entityId', 'int', 10, true, 0);
		parent::__loadDaoMap();
		
		DaoMap::createIndex('name');
		DaoMap::createIndex('entityName');
		DaoMap::commit();
	}
	/**
	 * 
	 * @param Product $product
	 * @param BaseEntityAbstract $entity
	 * @param string $comments
	 * @return Ambigous <BaseEntityAbstract, GenericDAO>
	 */
	public static function create(Product $product, BaseEntityAbstract $entity = null, $comments = '')
	{
		$log = new ProductQtyLog();
		$log->setProduct($product)
			->setStockOnHand($product->getStockOnHand())
			->setStockOnOrder($product->getStockOnOrder())
			->setStockOnPO($product->getstockOnPO())
			->setComments($comments);
		if($entity instanceof BaseEntityAbstract) {
			$log->setEntityName(get_class($entity))
				->setEntityId($entity->getId());
		}
		return $log->save();
	}
}