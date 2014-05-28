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
			$className = get_called_class();
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
	/**
	 * Creating a manifest for the delivery
	 * 
	 * @param string $userId The id of the user(from the courier) is making the manifest
	 * 
	 * @throws Exception
	 */
	public function createManifest($userId = '')
	{
		throw new Exception("This function(=" . __FUNCTION__ . ") should be overloaded!");
	}
	/**
	 * Creating a consignment note for the delivery
	 * 
	 * @param Shippment $shippment  The Shippment
	 * @param string    $manifestId The manifest id from the courier
	 * 
	 * @throws Exception
	 */
	public function createConsignment(Shippment &$shippment, $manifestId = '')
	{
		throw new Exception("This function(=" . __FUNCTION__ . ") should be overloaded!");
	}
	/**
	 * Closing a manifest
	 * 
	 * @param string $manifestId The ID of a manifest
	 * 
	 * @throws Exception
	 */
	public function closeManifest($manifestId)
	{
		throw new Exception("This function(=" . __FUNCTION__ . ") should be overloaded!");
	}
	/**
	 * Getting the tracking url for a label
	 * 
	 * @param string $label The lable that we are trying to track
	 * 
	 * @return string
	 */
	public function getTrackingURL($label)
	{
		throw new Exception("This function(=" . __FUNCTION__ . ") should be overloaded!");
	}
}