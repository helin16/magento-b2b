<?php
/**
 * Entity for OrderInfoType
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class OrderInfoType extends InfoTypeAbstract
{
	const ID_CUS_NAME = 1;
	const ID_CUS_SHIP_ADDR = 2;
	const ID_CUS_BILL_ADDR = 3;
	const ID_CUS_SHIP_PC = 4;
	const ID_CUS_CONTACT_NO = 5;
	const ID_CUS_EMAIL = 6;
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'oinfo_type');
		parent::__loadDaoMap();
		DaoMap::commit();
	}
}