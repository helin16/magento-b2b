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
	protected $unitPrice;
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
		if(intval($this->getQty()) === 0)
			throw new EntityException('You can NOT install a component into a kit with quantity: 0');
		if(intval(ceil($this->getUnitCost())) === 0)
			$this->setUnitCost($this->getComponent()->getUnitCost());
		if(trim($this->getUnitPrice()) === '') {
			$unitPrices = $this->getComponent()->getPrices();
			$this->setUnitPrice(count($unitPrices) === 0 ? 0 : $unitPrices[0]->getPrice());
		}

		if(trim($this->getId()) === '') { //when we are creating a new one
			$this->getComponent()->installedIntoKit($this->getQty(), $this->getUnitCost(), 'KITCOMPONENT CREATED', $this->getKit());
			$this->getKit()->addComment('A KitComponent(SKU=' . $this->getComponent()->getSku() . ', UnitPrice=' . StringUtilsAbstract::getCurrency($this->getUnitPrice()) . ', qty=' . $this->getQty() . ', UnitCost=' . StringUtilsAbstract::getCurrency($this->getUnitCost()) . ') has been ADDED INTO  this kit (' . $this->getKit()->getBarcode() . ')', Comments::TYPE_WORKSHOP);
		} else if(intval($this->getActive()) === 0 && self::countByCriteria('id = ? and active = 1', array($this->getId())) > 0) { //trying to deactivate the kitcomponent
				$this->getComponent()->installedIntoKit(0 - $this->getQty(), $this->getUnitCost(), 'DEACTIVATED KITCOMPONENT',  $this);
				$this->getKit()->addComment('A KitComponent(SKU=' . $this->getComponent()->getSku() . ', UnitPrice=' . StringUtilsAbstract::getCurrency($this->getUnitPrice()) . ', qty=' . $this->getQty() . ', UnitCost=' . StringUtilsAbstract::getCurrency($this->getUnitCost()) . ') has been REMOVED FROM this kit (' . $this->getKit()->getBarcode() . ')', Comments::TYPE_WORKSHOP);
		} else if(intval($this->getActive()) === 1) {
			$orignal = self::get($this->getId());
			if(intval($orignal->getQty()) !== intval($this->getQty())) {
				$this->getComponent()->installedIntoKit(($this->getQty() - $orignal->getQty()), $this->getUnitCost(), 'KITCOMPONENT CHANGED QTY', $this);
				$this->getKit()->addComment('A KitComponent(SKU=' . $this->getComponent()->getSku() . ', UnitPrice=' . StringUtilsAbstract::getCurrency($this->getUnitPrice()) . ', qty=' . $this->getQty() . ', UnitCost=' . StringUtilsAbstract::getCurrency($this->getUnitCost()) . ') has been QTY CHANGED( ' . $orignal->getQty() . ' => ' . $this->getQty() .' ) in kit (' . $this->getKit()->getBarcode() . ')', Comments::TYPE_WORKSHOP);
			}
		} else {
			throw new EntityException('CANT update KitComponents once created.');
		}
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::postSave()
	 */
	public function postSave()
	{
		$this->getKit()
			->reCalPriceNCost();
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
			$array['component'] = $this->getComponent() instanceof Product ? $this->getComponent()->getJson() : null;
			$array['product'] = $array['component'];
		}
		return parent::getJson($array, $reset);
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
	public static function create(Kit &$kit, Product $component, $qty, $unitPrice = '')
	{
		$kitComponent = new KitComponent();
		return $kitComponent->setKit($kit)
			->setComponent($component)
			->setQty(intval($qty))
			->setUnitPrice($unitPrice)
			->save();
	}

}