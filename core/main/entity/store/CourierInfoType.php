<?php
/**
 * Entity for CourierInfoType
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class CourierInfoType extends InfoTypeAbstract
{
	const ID_URL = 1;
	
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'courierinfotype');
		parent::__loadDaoMap();
		DaoMap::commit();
	}
}