<?php
/**
 * Entity for CourierInfo
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class CourierInfo extends InfoAbstract
{
	/**
	 * The courier of the info
	 * 
	 * @var Courier
	 */
	protected $courier;
	/**
	 * Getter for Courier
	 *
	 * @return Courier
	 */
	public function getCourier() 
	{
		$this->loadManyToOne('courier');
	    return $this->courier;
	}
	/**
	 * Setter for courier
	 *
	 * @param array $value The courier
	 *
	 * @return Courier
	 */
	public function setCourier(Courier $courier) 
	{
	    $this->courier = $courier;
	    return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'courierinfo');
		DaoMap::setManyToOne('courier', 'Courier');
		parent::__loadDaoMap();
		DaoMap::commit();
	}
}