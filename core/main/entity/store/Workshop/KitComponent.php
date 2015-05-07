<?php
class KitComponent extends BaseEntityAbstract
{
	/**
	 * The kit this component installed into
	 *
	 * @var Kit
	 */
	protected $kit;
	/**
	 * The component
	 *
	 * @var Product
	 */
	protected $component;
	/**
	 * The qty of the components that installed
	 *
	 * @var int
	 */
	protected $qty = 0;
	/**
	 * The unit cost of the component
	 *
	 * @var double
	 */
	protected $unitCost = 0;
	/**
	 * The unit Price of the component
	 *
	 * @var double
	 */
	protected $unitPrice = 0;
	/**
	 * Getter for kit
	 *
	 * @return Kit
	 */
	public function getKit()
	{
		$this->loadManyToOne('kit');
	    return $this->kit;
	}
	/**
	 * Setter for kit
	 *
	 * @param Kit $value The kit
	 *
	 * @return KitComponent
	 */
	public function setKit(Kit $value)
	{
	    $this->kit = $value;
	    return $this;
	}
	/**
	 * Getter for component
	 *
	 * @return Product
	 */
	public function getComponent()
	{
		$this->loadManyToOne('component');
	    return $this->component;
	}
	/**
	 * Setter for component
	 *
	 * @param Product $value The component
	 *
	 * @return KitComponent
	 */
	public function setComponent(Product $value)
	{
	    $this->component = $value;
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
	 * @return KitComponent
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
	 * @return KitComponent
	 */
	public function setUnitCost($value)
	{
	    $this->unitCost = $value;
	    return $this;
	}
	/**
	 * Getter for unitPrice
	 *
	 * @return Double
	 */
	public function getUnitPrice()
	{
	    return $this->unitPrice;
	}
	/**
	 * Setter for unitPrice
	 *
	 * @param unkown $value The unitPrice
	 *
	 * @return KitComponent
	 */
	public function setUnitPrice($value)
	{
	    $this->unitPrice = $value;
	    return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::preSave()
	 */
	public function preSave()
	{
		if(!$this->getComponent() instanceof Product)
			throw new EntityException('You have to provide a product to install into a kit');
		if($this->getComponent()->getIsKit() ===  true)
			throw new EntityException('You can NOT install a kit into another kit, please use the move kit function instead.');
		if(intval($this->getQty()) === 0)
			throw new EntityException('You can NOT install a component into a kit with quantity: 0');
		if(ceil($this->getUnitCost()) === 0 || ceil($this->getUnitPrice()) === 0) {
			$this->setUnitCost($this->getComponent()->getUnitCost())
				->setUnitPrice($this->getComponent()->getUnitPrice());
		}
		if(trim($this->getId()) === '') { //when we are creating a new one
			$this->getComponent()->installedIntoKit($this->getUnitCost(), $this->getQty(), $this);
		} else {
			if(intval($this->getActive()) === 0 && Self::countByCriteria('id = ? and active = 1') > 0) { //trying to deactivate the kitcomponent
				$this->getComponent()->installedIntoKit($this->getUnitCost(), 0 - $this->getQty(), $this);
			}
		}
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'kcom');

		DaoMap::setManyToOne('kit', 'Kit', 'kcom_kit');
		DaoMap::setManyToOne('component', 'Product', 'kcom_com');
		DaoMap::setIntType('qty');
		DaoMap::setIntType('unitCost', 'double', '10,4');
		DaoMap::setIntType('unitPrice', 'double', '10,4');

		parent::__loadDaoMap();

		DaoMap::commit();
	}
	/**
	 * Creating a kitcomponent
	 *
	 * @param Kit     $kit
	 * @param Product $component
	 * @param number  $qty
	 *
	 * @return KitComponent
	 */
	public static function create(Kit &$kit, Product $component, $qty)
	{
		$kitComponent = new KitComponent();
		$kitComponent->setKit($kit)
			->setComponent($component)
			->setQty(intval($qty))
			->save()
			->getKit()
				->reCalPriceNCost()
				->addComment($qty . ' Component(SKU=' . $component->getSku() . ') has been added into this kit (' . $kit->getBarcode() . ')', Comments::TYPE_WORKSHOP);
		return $kitComponent;
	}

}