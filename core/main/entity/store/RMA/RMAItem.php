<?php
class RMAItem extends BaseEntityAbstract
{
	/**
	 * The RMA
	 *
	 * @var RMA
	 */
	protected $RMA;
	/**
	 * the order item this RMA is for
	 * @var unknown
	 */
	protected $orderItem = null;
	/**
	 * The product of this credit item
	 *
	 * @var Product
	 */
	protected $product;
	/**
	 * The Qty that we are crediting
	 *
	 * @var int
	 */
	private $qty;
	/**
	 * the unitCost, If an orderItem is linked, then it will take the orderitem's unitCost; Otherwise, it will take the current unitCost of the product
	 *
	 * @var double
	 */
	private $unitCost;
	/**
	 * The item description
	 *
	 * @var string
	 */
	private $itemDescription;
	/**
	 * The receivedDate
	 * 
	 * @var UDate
	 */
	private $receivedDate;
	/**
	 * Getter for RMA
	 *
	 * @return RMA
	 */
	public function getRMA()
	{
	    return $this->RMA;
	}
	/**
	 * Setter for RMA
	 *
	 * @param RMA $value The RMA
	 *
	 * @return RMAItem
	 */
	public function setRMA(RMA $value)
	{
	    $this->RMA = $value;
	    return $this;
	}
	/**
	 * Getter for orderItem
	 *
	 * @return OrderItem
	 */
	public function getOrderItem()
	{
		$this->loadManyToOne('orderItem');
		return $this->orderItem;
	}
	/**
	 * Setter for orderItem
	 *
	 * @param unkown $value The orderItem
	 *
	 * @return RMAItem
	 */
	public function setOrderItem(OrderItem $value = null)
	{
		$this->orderItem = $value;
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
	 * @param unkown $value The product
	 *
	 * @return RMAItem
	 */
	public function setProduct(Product $value)
	{
		$this->product = $value;
		return $this;
	}
	/**
	 * Getter for qty
	 *
	 * @return int
	 */
	public function getQty()
	{
		return $this->qty;
	}
	/**
	 * Setter for qty
	 *
	 * @param int $value The qty
	 *
	 * @return RMAItem
	 */
	public function setQty($value)
	{
		$this->qty = $value;
		return $this;
	}
	/**
	 * Getter for unitCost
	 *
	 * @return double
	 */
	public function getUnitCost()
	{
		return $this->unitCost;
	}
	/**
	 * Setter for unitCost
	 *
	 * @param double $value The unitCost
	 *
	 * @return RMAItem
	 */
	public function setUnitCost($value)
	{
		$this->unitCost = $value;
		return $this;
	}
	/**
	 * Getter for itemDescription
	 *
	 * @return string
	 */
	public function getItemDescription()
	{
		return $this->itemDescription;
	}
	/**
	 * Setter for itemDescription
	 *
	 * @param string $value The itemDescription
	 *
	 * @return RMAItem
	 */
	public function setItemDescription($value)
	{
		$this->itemDescription = $value;
		return $this;
	}
	/**
	 * The getter for receivedDate
	 *
	 * @return UDate
	 */
	public function getReceivedDate ()
	{
	    return new UDate(trim($this->receivedDate));
	}
	/**
	 * Setter for receivedDate
	 * 
	 * @param mixed $value The new value of receivedDate
	 *
	 * @return RMAItem
	 */
	public function setReceivedDate ($value)
	{
	    $this->receivedDate = $value;
	    return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::preSave()
	 */
	public function preSave()
	{
		if(!is_numeric($this->getQty()))
			throw new EntityException('Qty of the RMAItem needs to be a integer.');
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::postSave()
	 */
	public function postSave()
	{
		if(trim($this->getReceivedDate()) !== trim(UDate::zeroDate())) {
			if(self::countByCriteria('RMAId = ? and receivedDate = ?', array($this->getRMA()->getId(), trim(UDate::zeroDate()))) > 0)
				$this->getRMA()
					->setStatus(RMA::STATUS_RECEIVING)
					->save()
					->addComment('Setting Status to "' . RMA::STATUS_RECEIVING . '", as received one of items');
			else
				$this->getRMA()
					->setStatus(RMA::STATUS_RECEIVED)
					->save()
					->addComment('Setting Status to "' . RMA::STATUS_RECEIVED . '", as no more item to receive');
		}
	}
	/**
	 * (non-PHPdoc)
	 * @see HydraEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'ra_item');

		DaoMap::setManyToOne('RMA', 'RMA', 'ra_item_ra');
		DaoMap::setManyToOne('orderItem', 'OrderItem', 'ra_item_ord_item', true);
		DaoMap::setManyToOne('product', 'Product', 'ra_pro');
		DaoMap::setIntType('qty');
		DaoMap::setIntType('unitCost', 'double', '10,4');
		DaoMap::setStringType('itemDescription', 'varchar', '255');
		DaoMap::setDateType('receivedDate');

		parent::__loadDaoMap();

		DaoMap::createIndex('qty');
		DaoMap::createIndex('unitCost');
		DaoMap::createIndex('receivedDate');

		DaoMap::commit();
	}
	/**
	 * Creating a RMA Item
	 *
	 * @param RMA     $rma
	 * @param Product $product
	 * @param int     $qty
	 * @param double  $itemDescription
	 * @param duble   $unitCost
	 *
	 * @return RMAItem
	 */
	public static function create(RMA $rma, Product $product, $qty, $itemDescription = '', $unitCost = null)
	{
		$item = new RMAItem();
		$item->setRMA($rma)
			->setProduct($product)
			->setQty($qty)
			->setItemDescription(trim($itemDescription))
			->setUnitCost($unitCost !== null ? $unitCost : $product->getUnitCost())
			->save();
		$msg = 'A RMAItem has been created with ' . $qty . 'Product(s) (SKU=' . $product->getSku() . ', ID=' . $product->getId() . '), unitCost=' . StringUtilsAbstract::getCurrency($item->getUnitCost()) ;
		$rma->addComment($msg, Comments::TYPE_SYSTEM)
			->addLog($msg, Comments::TYPE_SYSTEM);
		return $item;
	}
	/**
	 * Creating a RMA Item
	 *
	 * @param RMA       $rma
	 * @param OrderItem $product
	 * @param int       $qty
	 * @param double    $itemDescription
	 * @param duble     $unitCost
	 *
	 * @return RMAItem
	 */
	public static function createFromOrderItem(RMA $rma, OrderItem $orderItem, $qty, $itemDescription = '', $unitCost = null)
	{
		$item = new RMAItem();
		$item->setRMA($rma)
			->setOrderItem($orderItem)
			->setProduct($orderItem->getProduct())
			->setQty($qty)
			->setItemDescription(trim($itemDescription))
			->setUnitCost($unitCost !== null ? $unitCost : $orderItem->getUnitCost())
			->save();
		$msg = 'A RMAItem has been created based on OrderItem(ID=' . $orderItem->getId() . ', OrderNo=' . $orderItem->getOrder()->getOrderNo() . ') with ' . $qty . 'Product(s) (SKU=' . $product->getSku() . ', ID=' . $product->getId() . '), unitCost=' . StringUtilsAbstract::getCurrency($item->getUnitCost()) ;
		$rma->addComment($msg, Comments::TYPE_SYSTEM)
			->addLog($msg, Comments::TYPE_SYSTEM);
		return $item;
	}
}