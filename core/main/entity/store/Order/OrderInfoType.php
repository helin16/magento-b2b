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
	const ID_CUS_EMAIL = 2;
	const ID_QTY_ORDERED = 3;
	const ID_MAGE_ORDER_STATUS = 4;
	const ID_MAGE_ORDER_STATE = 5;
	const ID_MAGE_ORDER_PAYMENT_METHOD = 6;
	const ID_MAGE_ORDER_TOTAL_AMOUNT = 7;
	const ID_MAGE_ORDER_PAID_AMOUNT = 8;
	const ID_MAGE_ORDER_SHIPPING_METHOD = 9;
	const ID_MAGE_ORDER_STATUS_BEFORE_CHANGE = 10;
	const ID_SHIPPING_EST_COST = 11;
	const ID_HANDLING_EST_COST = 12;
	const ID_CLONED_FROM_ORDER_NO = 13;
	const ID_MAGE_ORDER_SHIPPING_COST = 14;
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