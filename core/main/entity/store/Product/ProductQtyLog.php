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
	const TYPE_PO = 'P';
	const TYPE_SALES_ORDER = 'S';
	const TYPE_STOCK_ADJ = 'AD';
	const TYPE_STOCK_MOVE_INTERNAL = 'SI';
	const TYPE_RMA = 'RMA';
	/**
	 * Product
	 * @var Product
	 */
	protected $product;
	/**
	 * log for stock on PO
	 * @var int
	 */
	private $stockOnPO = 0;
	/**
	 * log for stock on PO Variation
	 * @var int
	 */
	private $stockOnPOVar = 0;
	/**
	 * log for stock on hand
	 * @var int
	 */
	private $stockOnHand = 0;
	/**
	 * log for stock on hand Variation
	 * @var int
	 */
	private $stockOnHandVar = 0;
	/**
	 * log for stock on order
	 * @var int
	 */
	private $stockOnOrder = 0;
	/**
	 * log for stock on order Variation
	 * @var int
	 */
	private $stockOnOrderVar = 0;
	/**
	 * log for stock in parts for build
	 * @var int
	 */
	private $stockInParts = 0;
	/**
	 * log for stock in parts for build Variation
	 * @var int
	 */
	private $stockInPartsVar = 0;
	/**
	 * log for stock in RMA
	 * @var int
	 */
	private $stockInRMA = 0;
	/**
	 * log for stock in RMA Variation
	 * @var int
	 */
	private $stockInRMAVar = 0;
	/**
	 * The total value for all stock on hand units
	 *
	 * @var double
	 */
	private $totalOnHandValue = 0;
	/**
	 * The total value for all stock on hand units Variation
	 *
	 * @var double
	 */
	private $totalOnHandValueVar = 0;
	/**
	 * The total value for all stock on parts units
	 *
	 * @var double
	 */
	private $totalInPartsValue = 0;
	/**
	 * The total value for all stock on parts units Variation
	 *
	 * @var double
	 */
	private $totalInPartsValueVar = 0;
	/**
	 * The total value for all stock on RMA units
	 *
	 * @var double
	 */
	private $totalRMAValue = 0;
	/**
	 * The total value for all stock on parts RMA Variation
	 *
	 * @var double
	 */
	private $totalRMAValueVar = 0;
	/**
	 * comments
	 * @var string
	 */
	private $comments;
	/**
	 * entity name
	 * @var string
	 */
	private $entityName = '';
	/**
	 * entity id
	 * @var int
	 */
	private $entityId = '';
	/**
	 * type
	 * @var string
	 */
	private $type;
	/**
	 * Getter for totalRMAValue
	 *
	 * @return double
	 */
	public function getTotalRMAValue()
	{
	    return $this->totalRMAValue;
	}
	/**
	 * Setter for totalRMAValue
	 *
	 * @param double $value The totalRMAValue
	 *
	 * @return ProductQtyLog
	 */
	public function setTotalRMAValue($value)
	{
	    $this->totalRMAValue = $value;
	    return $this;
	}
	/**
	 * Getter for totalRMAValueVar
	 *
	 * @return double
	 */
	public function getTotalRMAValueVar()
	{
	    return $this->totalRMAValueVar;
	}
	/**
	 * Setter for totalRMAValueVar
	 *
	 * @param unkown $value The totalRMAValueVar
	 *
	 * @return ProductQtyLog
	 */
	public function settotalRMAValueVar($value)
	{
	    $this->totalRMAValueVar = $value;
	    return $this;
	}
	/**
	 * Getter for product
	 *
	 * @return Product
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
	 * getter for stockOnPOVar
	 *
	 * @return
	 */
	public function getStockOnPOVar()
	{
		return $this->stockOnPOVar;
	}
	/**
	 * Setter for stockOnPOVar
	 *
	 * @return ProductQtyLog
	 */
	public function setStockOnPOVar($stockOnPOVar)
	{
		$this->stockOnPOVar = $stockOnPOVar;
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
	 * getter for stockOnHandVar
	 *
	 * @return
	 */
	public function getStockOnHandVar()
	{
		return $this->stockOnHandVar;
	}
	/**
	 * Setter for stockOnHandVar
	 *
	 * @return ProductQtyLog
	 */
	public function setStockOnHandVar($stockOnHandVar)
	{
		$this->stockOnHandVar = $stockOnHandVar;
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
	 * getter for stockOnOrderVar
	 *
	 * @return
	 */
	public function getStockOnOrderVar()
	{
		return $this->stockOnOrderVar;
	}
	/**
	 * Setter for stockOnOrderVar
	 *
	 * @return ProductQtyLog
	 */
	public function setStockOnOrderVar($stockOnOrderVar)
	{
		$this->stockOnOrderVar = $stockOnOrderVar;
		return $this;
	}
	/**
	 * Getter for stockInParts
	 *
	 * @return int
	 */
	public function getStockInParts()
	{
		return $this->stockInParts;
	}
	/**
	 * Setter for the stockInParts
	 *
	 * @param mixed $value
	 *
	 * @return ProductQtyLog
	 */
	public function setStockInParts($value)
	{
		$this->stockInParts = $value;
		return $this;
	}
	/**
	 * getter for stockInPartsVar
	 *
	 * @return
	 */
	public function getStockInPartsVar()
	{
		return $this->stockInPartsVar;
	}
	/**
	 * Setter for stockInPartsVar
	 *
	 * @return ProductQtyLog
	 */
	public function setStockInPartsVar($stockInPartsVar)
	{
		$this->stockInPartsVar = $stockInPartsVar;
		return $this;
	}
	/**
	 * Getter for stockInRMA
	 *
	 * @return int
	 */
	public function getStockInRMA()
	{
		return $this->stockInRMA;
	}
	/**
	 * Setter for the stockInRMA
	 *
	 * @param mixed $value
	 *
	 * @return ProductQtyLog
	 */
	public function setStockInRMA($value)
	{
		$this->stockInRMA = $value;
		return $this;
	}
	/**
	 * getter for stockInRMAVar
	 *
	 * @return
	 */
	public function getStockInRMAVar()
	{
		return $this->stockInRMAVar;
	}
	/**
	 * Setter for stockInRMAVar
	 *
	 * @return ProductQtyLog
	 */
	public function setStockInRMAVar($stockInRMAVar)
	{
		$this->stockInRMAVar = $stockInRMAVar;
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
	 * getter for totalOnHandValueVar
	 *
	 * @return double
	 */
	public function getTotalOnHandValueVar()
	{
		return $this->totalOnHandValueVar;
	}
	/**
	 * Setter for totalOnHandValueVar
	 *
	 * @return ProductQtyLog
	 */
	public function setTotalOnHandValueVar($totalOnHandValueVar)
	{
		$this->totalOnHandValueVar = $totalOnHandValueVar;
		return $this;
	}
	/**
	 * getter for totalInPartsValue
	 *
	 * @return double
	 */
	public function getTotalInPartsValue()
	{
		return $this->totalInPartsValue;
	}
	/**
	 * Setter for totalInPartsValue
	 *
	 * @return ProductQtyLog
	 */
	public function setTotalInPartsValue($totalInPartsValue)
	{
		$this->totalInPartsValue = $totalInPartsValue;
		return $this;
	}
	/**
	 * getter for totalInPartsValueVar
	 *
	 * @return double
	 */
	public function getTotalInPartsValueVar()
	{
		return $this->totalInPartsValueVar;
	}
	/**
	 * Setter for totalInPartsValueVar
	 *
	 * @return ProductQtyLog
	 */
	public function setTotalInPartsValueVar($totalInPartsValueVar)
	{
		$this->totalInPartsValueVar = $totalInPartsValueVar;
		return $this;
	}
	/**
	 * getter for type
	 *
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}
	/**
	 * Setter for type
	 *
	 * @return ProductQtyLog
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}
	/**
	 * getting the entity
	 *
	 * @return BaseEntityAbstract|NULL
	 */
	public function getEntity()
	{
		if(($class = trim($this->getEntityName())) === '')
			return '';
		return $class::get(trim($this->getEntityId()));
	}
	/* (non-PHPdoc)
	 * @see BaseEntityAbstract::preSave()
	 */
	public function preSave()
	{
		if(trim($this->getId()) !== '')
			throw new EntityException('You can NOT change the details of a ' . get_class($this));
		$lastRecords = self::getAllByCriteria('productId = ?', array($this->getproduct()->getId()), true, 1, 1, array('id' => 'desc'));
		if(count($lastRecords) > 0 && ($lastRecord = $lastRecords[0]) instanceof self) {
			$this->setStockOnPOVar($this->getStockOnPO() - $lastRecord->getStockOnPO())
				->setStockOnHandVar($this->getStockOnHand() - $lastRecord->getStockOnHand())
				->setStockOnOrderVar($this->getStockOnOrder() - $lastRecord->getStockOnOrder())
				->setStockInPartsVar($this->getStockInParts() - $lastRecord->getStockInParts())
				->setStockInRMAVar($this->getStockInRMA() - $lastRecord->getStockInRMA())
				->setTotalInPartsValueVar($this->getTotalInPartsValue() - $lastRecord->getTotalInPartsValue())
				->setTotalOnHandValueVar($this->getTotalOnHandValue() - $lastRecord->getTotalOnHandValue())
				->settotalRMAValueVar($this->getTotalRMAValue() - $lastRecord->getTotalRMAValue());
		} else {
			$this->setStockOnPOVar($this->getStockOnPO())
				->setStockOnHandVar($this->getStockOnHand())
				->setStockOnOrderVar($this->getStockOnOrder())
				->setStockInPartsVar($this->getStockInParts())
				->setStockInRMAVar($this->getStockInRMA())
				->setTotalInPartsValueVar($this->getTotalInPartsValue())
				->setTotalOnHandValueVar($this->getTotalOnHandValue())
				->settotalRMAValueVar($this->getTotalRMAValue());
		}
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
		DaoMap::setIntType('stockOnHandVar', 'int', 10, false);
		DaoMap::setIntType('totalOnHandValue', 'double', '10,4');
		DaoMap::setIntType('totalOnHandValueVar', 'double', '10,4');
		DaoMap::setIntType('totalInPartsValue', 'double', '10,4');
		DaoMap::setIntType('totalInPartsValueVar', 'double', '10,4');
		DaoMap::setIntType('stockOnOrder', 'int', 10, false);
		DaoMap::setIntType('stockOnOrderVar', 'int', 10, false);
		DaoMap::setIntType('stockOnPO', 'int', 10, false);
		DaoMap::setIntType('stockOnPOVar', 'int', 10, false);
		DaoMap::setIntType('stockInParts', 'int', 10, false);
		DaoMap::setIntType('stockInPartsVar', 'int', 10, false);
		DaoMap::setIntType('stockInRMA', 'int', 10, false);
		DaoMap::setIntType('stockInRMAVar', 'int', 10, false);
		DaoMap::setIntType('totalRMAValue', 'double', '10,4', false);
		DaoMap::setIntType('totalRMAValueVar', 'double', '10,4', false);
		DaoMap::setStringType('comments', 'varchar', 255);
		DaoMap::setStringType('entityName', 'varchar', 100);
		DaoMap::setIntType('entityId', 'int', 10, true, 0);
		DaoMap::setStringType('type', 'varchar', 2);
		parent::__loadDaoMap();

		DaoMap::createIndex('entityName');
		DaoMap::createIndex('type');
		DaoMap::commit();
	}
	/**
	 *
	 * @param Product $product
	 * @param BaseEntityAbstract $entity
	 * @param string $comments
	 * @return Ambigous <BaseEntityAbstract, GenericDAO>
	 */
	public static function create(Product $product, $type = '' , BaseEntityAbstract $entity = null, $comments = '')
	{
		$log = new ProductQtyLog();
		$log->setProduct($product)
			->setType($type)
			->setStockOnHand($product->getStockOnHand())
			->setTotalOnHandValue($product->getTotalOnHandValue())
			->setStockOnOrder($product->getStockOnOrder())
			->setStockOnPO($product->getstockOnPO())
			->setStockInParts($product->getStockInParts())
			->setTotalInPartsValue($product->getTotalInPartsValue())
			->setStockInRMA($product->getStockInRMA())
			->setTotalRMAValue($product->getTotalRMAValue())
			->setComments($comments);
		if($entity instanceof BaseEntityAbstract) {
			$log->setEntityName(get_class($entity))
				->setEntityId($entity->getId());
		}
		return $log->save();
	}
}