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
	protected $product = null;
	/**
	 * lastPurchaseTime
	 * @var UDate
	 */
	protected $lastPurchaseTime = null;
	/**
	 * ReceivingItem
	 * @var ReceivingItem
	 */
	protected $receivingItem = null;
	/**
	 * PurchaseOrderItem
	 * @var PurchaseOrderItem
	 */
	protected $purchaseOrderItem = null;
	/**
	 * OrderItem
	 * @var OrderItem
	 */
	protected $orderItem = null;
	/**
	 * CreditNoteItem
	 * @var CreditNoteItem
	 */
	protected $creditNoteItem = null;
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
	public function getProduct()
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
	 * getter for purchaseOrderItem
	 *
	 * @return PurchaseOrderItem
	 */
	public function getPurchaseOrderItem()
	{
		$this->loadManyToOne('purchaseOrderItem');
		return $this->purchaseOrderItem;
	}
	/**
	 * Setter for purchaseOrderItem
	 *
	 * @return ProductAgeingLog
	 */
	public function setPurchaseOrderItem($purchaseOrderItem)
	{
		$this->purchaseOrderItem = $purchaseOrderItem;
		return $this;
	}
	/**
	 * getter for orderItem
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
	 * @return ProductAgeingLog
	 */
	public function setOrderItem($orderItem)
	{
		$this->orderItem = $orderItem;
		return $this;
	}
	/**
	 * getter for creditNoteItem
	 *
	 * @return CreditNoteItem
	 */
	public function getCreditNoteItem()
	{
		$this->loadManyToOne('creditNoteItem');
		return $this->creditNoteItem;
	}
	/**
	 * Setter for creditNoteItem
	 *
	 * @return ProductAgeingLog
	 */
	public function setCreditNoteItem($creditNoteItem)
	{
		$this->creditNoteItem = $creditNoteItem;
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
		$this->setProduct($this->getProductQtyLog()->getproduct())
			->setLastPurchaseTime($this->getProductQtyLog()->getCreated());
		switch($this->getProductQtyLog()->getEntityName()) {
			case 'ReceivingItem': {
				if(($receivingItem = ReceivingItem::get($this->getProductQtyLog()->getEntityId()))  instanceof ReceivingItem)
					$this->setReceivingItem($receivingItem);
				break;
			}
			case 'PurchaseOrderItem': {
				if(($purchaseOrderItem = PurchaseOrderItem::get($this->getProductQtyLog()->getEntityId()))  instanceof PurchaseOrderItem)
					$this->setPurchaseOrderItem($purchaseOrderItem);
				break;
			}
			case 'OrderItem': {
				if(($orderItem = OrderItem::get($this->getProductQtyLog()->getEntityId()))  instanceof OrderItem)
					$this->setOrderItem($orderItem);
				break;
			}
			case 'CreditNoteItem': {
				if(($creditNoteItem = CreditNoteItem::get($this->getProductQtyLog()->getEntityId()))  instanceof CreditNoteItem)
					$this->setCreditNoteItem($creditNoteItem);
				break;
			}
		}
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
			$array['product'] = $this->getProduct()->getJson();
			$array['productQtyLog'] = $this->getProductQtyLog()->getJson();
			$array['receivingItem'] = ($receivingItem = $this->getReceivingItem()) instanceof ReceivingItem ? $receivingItem->getJson() : '';
			$array['purchaseOrderItem'] = ($purchaseOrderItem = $this->getPurchaseOrderItem()) instanceof PurchaseOrderItem ? $purchaseOrderItem->getJson() : '';
			$array['orderItem'] = ($orderItem = $this->getOrderItem()) instanceof OrderItem ? $orderItem->getJson() : '';
			$array['creditNoteItem'] = ($creditNoteItem = $this->getCreditNoteItem()) instanceof CreditNoteItem ? $creditNoteItem->getJson() : '';
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
		DaoMap::setManyToOne('receivingItem', 'ReceivingItem', 'pal_rec_item', true);
		DaoMap::setManyToOne('purchaseOrderItem', 'PurchaseOrderItem', 'pal_po_item', true);
		DaoMap::setManyToOne('orderItem', 'OrderItem', 'pal_ord_item', true);
		DaoMap::setManyToOne('creditNoteItem', 'CreditNoteItem', 'pal_cn_item', true);
		DaoMap::setManyToOne('productQtyLog', 'ProductQtyLog', 'pal_pql');
		DaoMap::setStringType('comments', 'varchar', 255);
		parent::__loadDaoMap();

		DaoMap::commit();
	}
	/**
	 *
	 * @param ProductQtyLog			$productQtyLog
	 * @param string				$comments
	 * @return Ambigous <BaseEntityAbstract, GenericDAO>
	 */
	public static function create(ProductQtyLog $productQtyLog, $comments = '')
	{
		$log = new ProductAgeingLog();
		$log->setProductQtyLog($productQtyLog)
			->setComments($comments);
		return $log->save();
	}
}