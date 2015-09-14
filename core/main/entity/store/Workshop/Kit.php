<?php
class Kit extends BaseEntityAbstract
{
	const BARCODE_PREFIX = 'BPCB';
	/**
	 * Task of the kit
	 *
	 * @var Task
	 */
	protected $task =  null;
	/**
	 * The product
	 *
	 * @var Product
	 */
	protected $product;
	/**
	 * The barcode of the kit
	 *
	 * @var string
	 */
	private $barcode = '';
	/**
	 * The customer that this kit is sold to
	 *
	 * @var Customer
	 */
	protected $soldToCustomer;
	/**
	 * The sold Date
	 *
	 * @var UDate
	 */
	private $soldDate;
	/**
	 * it's been sold on Order
	 *
	 * @var Order
	 */
	protected $soldOnOrder;
	/**
	 * it's been shipped on shippment
	 *
	 * @var Shippment
	 */
	protected $shippment;
	/**
	 * The unit Cost of the kit, a sum of all children kits
	 *
	 * @var string
	 */
	private $cost = 0;
	/**
	 * The unit price of the kit
	 *
	 * @var string
	 */
	private $price = 0;
	/**
	 * Getter for task
	 *
	 * @return Task
	 */
	public function getTask()
	{
		$this->loadManyToOne('task');
	    return $this->task;
	}
	/**
	 * Setter for task
	 *
	 * @param Task $value The task
	 *
	 * @return Kit
	 */
	public function setTask(Task $value = null)
	{
	    $this->task = $value;
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
	 * @return Kit
	 */
	public function setProduct(Product $value)
	{
	    $this->product = $value;
	    return $this;
	}
	/**
	 * Getter for barcode
	 *
	 * @return string
	 */
	public function getBarcode()
	{
	    return $this->barcode;
	}
	/**
	 * Setter for barcode
	 *
	 * @param string $value The barcode
	 *
	 * @return Kit
	 */
	public function setBarcode($value)
	{
	    $this->barcode = $value;
	    return $this;
	}
	/**
	 * Getter for soldToCustomer
	 *
	 * @return Customer
	 */
	public function getSoldToCustomer()
	{
		$this->loadManyToOne('soldToCustomer');
	    return $this->soldToCustomer;
	}
	/**
	 * Setter for soldToCustomer
	 *
	 * @param Customer $value The soldToCustomer
	 *
	 * @return Kit
	 */
	public function setSoldToCustomer(Customer $value = null)
	{
	    $this->soldToCustomer = $value;
	    return $this;
	}
	/**
	 * Getter for soldDate
	 *
	 * @return UDate
	 */
	public function getSoldDate()
	{
	    return new UDate(trim($this->soldDate));
	}
	/**
	 * Setter for soldDate
	 *
	 * @param unkown $value The soldDate
	 *
	 * @return Kit
	 */
	public function setSoldDate($value)
	{
	    $this->soldDate = $value;
	    return $this;
	}
	/**
	 * Getter for soldOnOrder
	 *
	 * @return Order
	 */
	public function getSoldOnOrder()
	{
		$this->loadManyToOne('soldOnOrder');
	    return $this->soldOnOrder;
	}
	/**
	 * Setter for soldOnOrder
	 *
	 * @param Order $value The soldOnOrder
	 *
	 * @return Kit
	 */
	public function setSoldOnOrder(Order $value = null)
	{
	    $this->soldOnOrder = $value;
	    return $this;
	}
	/**
	 * Getter for shippment
	 *
	 * @return Shippment
	 */
	public function getShippment()
	{
		$this->loadManyToOne('shippment');
	    return $this->shippment;
	}
	/**
	 * Setter for shippment
	 *
	 * @param Shippment $value The shippment
	 *
	 * @return Kit
	 */
	public function setShippment(Shippment $value = null)
	{
	    $this->shippment = $value;
	    return $this;
	}
	/**
	 * Getter for cost
	 *
	 * @return double
	 */
	public function getCost()
	{
	    return $this->cost;
	}
	/**
	 * Setter for cost
	 *
	 * @param unkown $value The cost
	 *
	 * @return Kit
	 */
	public function setCost($value)
	{
	    $this->cost = $value;
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
	 * Setter for price
	 *
	 * @param unkown $value The price
	 *
	 * @return Kit
	 */
	public function setPrice($value)
	{
	    $this->price = $value;
	    return $this;
	}
	/**
	 * Adding a component to the kit
	 *
	 * @param Product      $component
	 * @param int          $qty
	 * @param KitComponent $newKitComponent
	 *
	 * @return Kit
	 */
	public function addComponent(Product $component, $qty, $unitPrice = '', KitComponent &$newKitComponent = null)
	{
		$newKitComponent = KitComponent::create($this, $component, $qty, $unitPrice);
		return $this;
	}
	/**
	 * finished adding all components to this kit
	 *
	 * @return Kit
	 */
	public function finishedAddingComponents()
	{
		$this->getProduct()->createAsAKit('', $this);
		return $this;
	}
	/**
	 * recalculate the price and cost
	 *
	 * @return Kit
	 */
	public function reCalPriceNCost()
	{
		$price = $cost = 0;
		$components = KitComponent::getAllByCriteria('kitId = ?', array($this->getId()));
		if(count($components) > 0) {
			foreach($components as $component) {
				$price += $component->getUnitPrice() * $component->getQty();
				$cost += $component->getUnitCost() * $component->getQty();
			}
		}
		return $this->setPrice($price)
			->setCost($cost)
			->save();
	}
	/**
	 * recalulate the whole product with kits' value and qty
	 *
	 * @return Kit
	 */
	public function reCalProductValue()
	{
		$this->getProduct()->reCalKitsValue();
		return $this;
	}
	/**
	 * Log the changes for the kit
	 *
	 * @param unknown $field
	 * @param unknown $fieldEntity
	 *
	 * @return Kit
	 */
	private function _changeLog($field, $toStringFunc, $origKit, $fieldEntityClass)
	{
		$getter = 'get'. ucfirst($field);
		$orginalValue = $origKit->$getter();
		$newValue = $this->$getter();
		$comments = '';
		if($fieldEntityClass !== '') {
			if (($orginalValue instanceof $fieldEntityClass && !$newValue instanceof $fieldEntityClass) || (!$orginalValue instanceof $fieldEntityClass && $newValue instanceof $fieldEntityClass) || (($orginalValue instanceof $fieldEntityClass && $newValue instanceof $fieldEntityClass && $orginalValue->getId() !== $newValue)))
				$comments = 'The ' . $field . ' for Kit [' . $this->getBarcode() . '] changed from ( ' . ($orginalValue instanceof $fieldEntityClass ? $orginalValue->$toStringFunc() : '') . ' ) to ( ' . ($newValue instanceof $fieldEntityClass ? $newValue->$toStringFunc() : '') . ' ).';
		} else if (trim($orginalValue) !== trim($newValue)) {
			$comments = 'The ' . $field . ' for Kit [' . $this->getBarcode() . '] changed from ( ' . trim($orginalValue) . ' ) to ( ' . trim($newValue) . ' ).';
		}
		//something changed.
		if(trim($comments) !== '') {
			$this->addComment($comments, Comments::TYPE_SYSTEM);
			if($orginalValue instanceof BaseEntityAbstract)
				$orginalValue->addComment($comments, Comments::TYPE_SYSTEM);
			if($newValue instanceof BaseEntityAbstract)
				$newValue->addComment($comments, Comments::TYPE_SYSTEM);
		}
		return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see TreeEntityAbstract::preSave()
	 */
	public function preSave()
	{
		if(!$this->getProduct() instanceof Product)
			throw new EntityException('A product needed to create a kit!');
		if(trim($this->soldDate) === '')
			$this->setSoldDate(UDate::zeroDate());
		if($this->getProduct()->getIsKit() !== true)
			throw new EntityException('The product of the kit needs to have the flag IsKit ticked.');
		if(trim($this->getId()) !== '') {
			if(self::countByCriteria('id = ? and productId != ?', array($this->getId(), $this->getProduct()->getId())) > 0 )
				throw new EntityException('You can NOT change the product of the KIT[' . $this->getBarcode() . '] once it is created.');
			$origKit = self::get($this->getId());
			$this->_changeLog('soldToCustomer', 'getName', $origKit, 'Customer')
				->_changeLog('soldDate', '__toString', $origKit, '')
				->_changeLog('soldOnOrder', 'getOrderNo', $origKit, 'Order')
				->_changeLog('shippment', 'getId', $origKit, 'Shippment');
		}
	}
	/**
	 * (non-PHPdoc)
	 * @see TreeEntityAbstract::postSave()
	 */
	public function postSave()
	{
		if(trim($this->getBarcode()) === '') {
			$this->setBarcode(self::BARCODE_PREFIX .str_pad($this->getId(), 8, '0', STR_PAD_LEFT))
				->save()
				->addComment('A Kit [' . $this->getBarcode() . '] created.' . ($this->getTask() instanceof Task ? ' from Task(ID=' . $this->getTask()->getId() . ')' : ''));
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
			$array['task'] = $this->getTask() instanceof Task ? $this->getTask()->getJson() : null;
			$array['product'] = $this->getProduct() instanceof Product ? $this->getProduct()->getJson() : null;
			$array['soldToCustomer'] = $this->getSoldToCustomer() instanceof Customer ? $this->getSoldToCustomer()->getJson() : null;
			$array['soldOnOrder'] = $this->getSoldOnOrder() instanceof Order ? $this->getSoldOnOrder()->getJson() : null;
			$array['shippment'] = $this->getShippment() instanceof Shippment ? $this->getShippment()->getJson() : null;
			$array['components'] = array_map(create_function('$a', 'return $a->getJson();'), KitComponent::getAllByCriteria('kitId = ?', array($this->getId())));
		}
		return parent::getJson($array, $reset);
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'kit');

		DaoMap::setManyToOne('task', 'Task', 'kit_t', true);
		DaoMap::setManyToOne('product', 'Product', 'kit_pro', true);
		DaoMap::setStringType('barcode');
		DaoMap::setManyToOne('soldToCustomer', 'Customer', 'kt_cust', true);
		DaoMap::setDateType('soldDate');
		DaoMap::setManyToOne('soldOnOrder', 'Order', 'kit_ord', true);
		DaoMap::setManyToOne('shippment', 'Shippment', 'kit_ship', true);
		DaoMap::setIntType('cost', 'Double', '10,4');
		DaoMap::setIntType('price', 'Double', '10,4');

		parent::__loadDaoMap();

		DaoMap::createUniqueIndex('barcode');
		DaoMap::createIndex('soldDate');
		DaoMap::commit();
	}
	/**
	 * Created a kit
	 *
	 * @param Product $product
	 * @param Task    $task
	 * @param string  $comments
	 *
	 * @return Kit
	 */
	public static function create(Product $product, Task $task = null, $comments = '')
	{
		$kit = new Kit();
		$kit->setProduct($product)
			->setTask($task)
			->save();
		if(($comments = trim($comments)) === '')
			$kit->addComment($comments);
		return $kit;
	}
	/**
	 * getting the kits by barcode
	 *
	 * @param string $barcode The barcode fo the kits
	 *
	 * @return Ambigous <NULL, BaseEntityAbstract>
	 */
	public static function getByBarcode($barcode)
	{
		$kits = self::getAllByCriteria('barcode = ?', array($barcode), false, 1, 1);
		return count($kits) === 0 ? null : $kits[0];
	}
}