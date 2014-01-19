<?php
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