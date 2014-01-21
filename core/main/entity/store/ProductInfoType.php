<?php
/**
 * Entity for ProductInfoType
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class ProductInfoType extends InfoTypeAbstract
{
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'pro_info_type');
		parent::__loadDaoMap();
		DaoMap::commit();
	}
}