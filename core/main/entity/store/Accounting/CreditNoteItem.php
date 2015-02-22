<?php
class CreditNoteItem extends BaseEntityAbstract
{
	/**
	 * The credit note of this item
	 *
	 * @var CreditNote
	 */
	protected $creditNote;
	/**
	 * Order item for this credit item
	 *
	 * @var OrderItem
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
	 * the unit price for the refund
	 *
	 * @var double
	 */
	private $unitPrice;
	/**
	 * The item description
	 *
	 * @var string
	 */
	private $itemDescription;
	/**
	 * Getter for creditNote
	 *
	 * @return CreditNote
	 */
	public function getCreditNote()
	{
		$this->loadManyToOne('creditNote');
	    return $this->creditNote;
	}
	/**
	 * Setter for creditNote
	 *
	 * @param CreditNote $value The creditNote
	 *
	 * @return CreditNoteItem
	 */
	public function setCreditNote(CreditNote $value)
	{
	    $this->creditNote = $value;
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
	 * @return CreditNoteItem
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
	 * @return CreditNoteItem
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
	 * @return CreditNoteItem
	 */
	public function setQty($value)
	{
	    $this->qty = $value;
	    return $this;
	}
	/**
	 * Getter for unitPrice
	 *
	 * @return double
	 */
	public function getUnitPrice()
	{
	    return $this->unitPrice;
	}
	/**
	 * Setter for unitPrice
	 *
	 * @param double $value The unitPrice
	 *
	 * @return CreditNoteItem
	 */
	public function setUnitPrice($value)
	{
	    $this->unitPrice = $value;
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
	 * @return CreditNoteItem
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
	 * @return CreditNoteItem
	 */
	public function setItemDescription($value)
	{
	    $this->itemDescription = $value;
	    return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::preSave()
	 */
	public function preSave()
	{
		if(!is_numeric($this->getQty()))
			throw new EntityException('Qty of the CreditNoteItem needs to be a integer.');
	}
	/**
	 * (non-PHPdoc)
	 * @see HydraEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'cn_item');

		DaoMap::setManyToOne('creditNote', 'CreditNote', 'cn_item_cn');
		DaoMap::setManyToOne('orderItem', 'OrderItem', 'cn_it', true);
		DaoMap::setManyToOne('product', 'Product', 'cn_pro');
		DaoMap::setIntType('qty');
		DaoMap::setIntType('unitPrice', 'double', '10,4');
		DaoMap::setIntType('unitCost', 'double', '10,4');
		DaoMap::setStringType('itemDescription', 'varchar', '255');

		parent::__loadDaoMap();

		DaoMap::createIndex('qty');
		DaoMap::createIndex('unitPrice');
		DaoMap::createIndex('unitCost');

		DaoMap::commit();
	}
	/**
	 * Creating a credit note
	 *
	 * @param CreditNote $creditNote
	 * @param Product    $product
	 * @param int        $qty
	 * @param double     $unitPrice
	 * @param double     $itemDescription
	 * @param duble      $unitCost
	 *
	 * @return CreditNoteItem
	 */
	public static function create(CreditNote $creditNote, Product $product, $qty, $unitPrice, $itemDescription = '', $unitCost = null)
	{
		$item = new CreditNoteItem();
		$item->setCreditNote($creditNote)
			->setProduct($product)
			->setQty($qty)
			->setUnitPrice($unitPrice)
			->setItemDescription(trim($itemDescription))
			->setUnitCost($unitCost !== null ? $unitCost : $product->getUnitCost())
			->save();
		$msg = 'A credit item has been created with ' . $qty . 'Product(s) (SKU=' . $product->getSku() . ', ID=' . $product->getId() . '), unitPrice=' . StringUtilsAbstract::getCurrency($unitPrice) . ', unitCost=' . StringUtilsAbstract::getCurrency($item->getUnitCost()) ;
		$creditNote->addComment($msg, Comments::TYPE_SYSTEM)
			->addLog($msg, Comments::TYPE_SYSTEM);
		return $item;
	}
	/**
	 * Creating a credit note from an orderitem
	 *
	 * @param CreditNote $creditNote
	 * @param OrderItem  $orderItem
	 * @param int        $qty
	 * @param double     $unitPrice
	 * @param double     $itemDescription
	 * @param duble      $unitCost
	 *
	 * @return CreditNoteItem
	 */
	public static function createFromOrderItem(CreditNote $creditNote, OrderItem $orderItem, $qty, $unitPrice = null, $itemDescription = '', $unitCost = null)
	{
		$item = new CreditNoteItem();
		$item->setCreditNote($creditNote)
			->setOrderItem($orderItem)
			->setProduct($orderItem->getProduct())
			->setQty($qty)
			->setUnitPrice($unitPrice === null ? $orderItem->getUnitPrice() : $unitPrice)
			->setItemDescription(trim($itemDescription))
			->setUnitCost($unitCost !== null ? $unitCost : $orderItem->getUnitCost())
			->save();
		$msg = 'A credit item has been created based on OrderItem(ID=' . $orderItem->getId() . ', OrderNo=' . $orderItem->getOrder()->getOrderNo() . ') with ' . $qty . 'Product(s) (SKU=' . $item->getProduct()->getSku() . ', ID=' . $item->getProduct()->getId() . '), unitPrice=' . StringUtilsAbstract::getCurrency($unitPrice) . ', unitCost=' . StringUtilsAbstract::getCurrency($item->getUnitCost()) ;
		$creditNote->addComment($msg, Comments::TYPE_SYSTEM)
			->addLog($msg, Comments::TYPE_SYSTEM);
		return $item;
	}
	/**
	 * get Credit Note Items by credit note
	 * 
	 * @param CreditNote|string $creditNote
	 * @return Ambigous <NULL, unknown>
	 */
	public static function getByCreditNote($creditNote)
	{
		$creditNote = $creditNote instanceof CreditNote ? $creditNote : CreditNote::get(trim($creditNote));
		$creditNote = $creditNote instanceof CreditNote ? $creditNote : (count($creditNotes = CreditNote::getAllByCriteria('creditNoteNo = ?', array(trim($creditNote)), true, 1, 1)) > 0 ? $creditNotes[0] : null);
		return $creditNote instanceof CreditNote ? (count($items = self::getAllByCriteria('creditNoteId = ?', array($creditNote->getId()), true)) > 0 ? $items : null) : null;
	}
}