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
	protected $qty;
	/**
	 * The unit cost of the component
	 *
	 * @var double
	 */
	protected $unitCost;
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

}