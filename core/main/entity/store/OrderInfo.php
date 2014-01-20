<?php
/**
 * Entity for OrderInfo
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class OrderInfo extends InfoAbstract
{
	/**
	 * The order entity
	 * @var Order
	 */
	protected $order;
	/**
	 * Getter for the order
	 */
	public function getOrder()
	{
		$this->loadManyToOne('order');
		return $this->order;
	}
	/**
	 * Setter for the order
	 *
	 * @param string $value The order of the status
	 *
	 * @return OrderStatus
	 */
	public function setOrder($value)
	{
		$this->order = $value;
		return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'oinfo');
		parent::__loadDaoMap();
		DaoMap::commit();
	}
}