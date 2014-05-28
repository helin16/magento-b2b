<?php
abstract class CourierConnector
{
	public static $_cache = array();
	/**
	 * The courier this connector is for
	 * 
	 * @var Courier
	 */
	protected $_courier = null;
	/**
	 * Getting the connector
	 * 
	 * @param Courier $courier The courier this connector is for
	 * 
	 * @return multitype:
	 */
	public static function getConnector(Courier $courier)
	{
		if(!isset(self::$_cache[$courier->getId()]))
		{
			$className = trim($courier->getConnector());
			self::$_cache[$courier->getId()] = new $className($courier);
		}
		return self::$_cache[$courier->getId()];
	}
	/**
	 * construct
	 * 
	 * @param Courier $courier The courier this connector is for
	 */
	public function __construct(Courier $courier)
	{
		$this->_courier = $courier;
	}
}