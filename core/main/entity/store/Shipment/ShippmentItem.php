<?php
/**
 * Entity for ShippmentItem
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class ShippmentItem extends BaseEntityAbstract
{
	/**
	 * The shippment
	 * 
	 * @var Shippment
	 */
	protected $shippment;
	private $refNo = '';
	private $length;
	private $width;
	private $height;
	private $weight;
	private $comments = '';
	/** 
	 * Getter for shippment
	 * 
	 * @return Shippment
	 */
	public function getShippment ()
	{
		$this->loadManyToOne('shippment');
		return $this->shippment;
	}
	/** 
	 * Setter for shippment
	 * 
	 * @param Shippment $value
	 * 
	 * @return ShippmentItem
	 */
	public function setshippment(Shippment $value)
	{
		$this->shippment = $value;
		return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::getJson()
	 */
	public function getJson($extra = '', $reset = false)
	{
		$array = array();
		if(!$this->isJsonLoaded($reset))
			$array['courier'] = $this->getCourier()->getJson();
		
		return parent::getJson($array, $reset);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'shippmentitem');
		
		DaoMap::setManyToOne('shippment', 'Shippment', 'shitem_Shippment');
		
		parent::__loadDaoMap();
		DaoMap::commit();
	}
}