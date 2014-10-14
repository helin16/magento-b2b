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
	 * The magento product id
	 * 
	 * @var int
	 */
	const ID_MAGE_PRODUCT_ID = 1;
	/**
	 * The product weight
	 * 
	 * @var int
	 */
	const ID_WEIGHT = 2;
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