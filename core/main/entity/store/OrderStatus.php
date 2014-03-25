<?php
/**
 * Entity for OrderStatus
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class OrderStatus extends InfoEntityAbstract
{
	const ID_NEW = 1;
	const ID_CANCELLED = 2;
	const ID_ON_HOLD = 3;
	const ID_ETA = 4;
	const ID_STOCK_CHECKED_BY_PURCHASING = 5;
	const ID_INSUFFICIENT_STOCK = 6;
	const ID_PICKED = 7;
	const ID_SHIPPED = 8;
	/**
	 * The name of the status
	 * 
	 * @var string
	 */
	private $name;
	/**
	 * The Magento order status
	 * 
	 * @var string
	 */
	private $mageStatus = '';
	/**
	 * Getter for the name
	 */
	public function getName() 
	{
	    return $this->name;
	}
	/**
	 * Setter for the name
	 * 
	 * @param string $value The name of the status
	 * 
	 * @return OrderStatus
	 */
	public function setName($value) 
	{
	    $this->name = $value;
	    return $this;
	}
	/**
	 * Getter for mageStatus
	 *
	 * @return string
	 */
	public function getMageStatus() 
	{
	    return $this->mageStatus;
	}
	/**
	 * Setter for mageStatus
	 *
	 * @param string $value The mageStatus
	 *
	 * @return OrderStatus
	 */
	public function setMageStatus($value) 
	{
	    $this->mageStatus = $value;
	    return $this;
	}
	/**
	 * createStatus
	 * 
	 * @param string $status     The name of the status
	 * @param string $mageStatus The magento order status
	 * 
	 * @return Ambigous <OrderStatus, BaseEntityAbstract>
	 */
	public static function createStatus($status, $mageStatus = '')
	{
		$items = FactoryAbastract::dao(__CLASS__)->findByCriteria('name=?', array($status), false, 1, 1);
		$st = (count($items) === 0 ? new OrderStatus() : $items[0]);
		$st->setName($status);
		$st->setMageStatus($mageStatus);
		FactoryAbastract::dao(__CLASS__)->save($st);
		return $st;
	}
	/**
	 * getStatus
	 * 
	 * @param string $status The name of the status
	 * 
	 * @return Ambigous <OrderStatus, BaseEntityAbstract>
	 */
	public static function get($statusId)
	{
		return FactoryAbastract::dao(__CLASS__)->findById($statusId);
	}
	/**
	 * get all statuses
	 * 
	 * @param bool  $searchActiveOnly
	 * @param int   $pageNo
	 * @param int   $pageSize
	 * @param array $orderBy
	 * 
	 * @return Ambigous <multitype:, multitype:BaseEntityAbstract >
	 */
	public static function findAll($searchActiveOnly = true, $pageNo = null, $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE, $orderBy = array())
	{
		return FactoryAbastract::dao(__CLASS__)->findAll($searchActiveOnly, $pageNo, $pageSize, $orderBy);
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::__toString()
	 */
	public function __toString()
	{
		return trim($this->getName());
	}
	/**
	 * (non-PHPdoc)
	 * @see HydraEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'ost');
	
		DaoMap::setStringType('name', 'varchar', 32);
		DaoMap::setStringType('mageStatus', 'varchar', 50);
		parent::__loadDaoMap();
	
		DaoMap::createUniqueIndex('name');
		DaoMap::createIndex('mageStatus');
		DaoMap::commit();
	}
}