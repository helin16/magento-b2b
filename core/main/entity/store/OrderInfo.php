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