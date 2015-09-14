<?php
/**
 * SystemSettings
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class SystemSettings extends BaseEntityAbstract
{
	const TYPE_B2B_SOAP_WSDL = 'b2b_soap_wsdl';
	const TYPE_B2B_SOAP_USER = 'b2b_soap_user';
	const TYPE_B2B_SOAP_KEY = 'b2b_soap_key';
	const TYPE_B2B_SOAP_TIMEZONE = 'b2b_soap_timezone';
	const TYPE_B2B_SOAP_LAST_IMPORT_TIME = 'b2b_soap_last_import_time';
	const TYPE_SYSTEM_TIMEZONE = 'system_timezone';
	const TYPE_ASSET_ROOT_DIR = 'asset_root_dir';
	const TYPE_PRODUCT_LAST_UPDATED = 'product_last_updated';
	const TYPE_EMAIL_SENDING_SERVER = 'sending_server_conf';
	const TYPE_EMAIL_DEFAULT_SYSTEM_EMAIL = 'sys_email_addr';
	const TYPE_ALLOW_NEGTIVE_STOCK = 'allow_neg_stock';
	const TYPE_LAST_NEW_PRODUCT_PULL = 'last_new_product_pull';
	const TYPE_LAST_NEW_PRODUCT_PUSH = 'last_new_price_push';
	const TYPE_LAST_PRODUCT_PULL_ID = 'last_product_pull_id';
	const TYPE_SYSTEM_BUILD_PRODUCTS_ID = 'system_build_product_ids';
	/**
	 * The value of the setting
	 * 
	 * @var string
	 */
	private $value;
	/**
	 * The type of the setting
	 * 
	 * @var string
	 */
	private $type;
	/**
	 * The description
	 * 
	 * @var string
	 */
	private $description;
	/**
	 * The cache
	 * 
	 * @var array
	 */
	private static $_cache = array();
	/**
	 * Getting Settings Object
	 * 
	 * @param string $type The type string
	 * 
	 * @return String
	 */
	public static function getSettings($type)
	{
		if(!isset(self::$_cache[$type]))
		{
			$settings = self::getAllByCriteria('type = ?', array($type), false, 1, 1);
			self::$_cache[$type] = trim(count($settings) === 0 ? '' : $settings[0]->getValue());
		}
		return self::$_cache[$type];
	}
	
	/**
	 * adding a new Settings Object
	 * 
	 * @param string $type The type string
	 */
	public static function addSettings($type, $value)
	{
		$class = __CLASS__;
		$settings = self::getAllByCriteria('type=?', array($type), false, 1, 1);
		$setting = ((count($settings) === 0 ? new $class() : $settings[0]));
		$setting->setType($type)
			->setValue($value)
			->setActive(true)
			->save();
		self::$_cache[$type] = $value;
	}
	/**
	 * Removing Settings Object
	 * 
	 * @param string $type The type string
	 */
	public static function removeSettings($type)
	{
		self::updateByCriteria('set active = 0', 'type = ?', array($type));
		self::$_cache[$type] = null;
		array_filter(self::$_cache);
	}
	/**
	 * Getter for value
	 *
	 * @return int
	 */
	public function getValue() 
	{
	    return $this->value;
	}
	/**
	 * Setter for value
	 *
	 * @param sting $value The value
	 *
	 * @return SystemSettings
	 */
	public function setValue($value) 
	{
	    $this->value = $value;
	    return $this;
	}
	/**
	 * Getter for type
	 *
	 * @return int
	 */
	public function getType()
	{
		return $this->type;
	}
	/**
	 * Setter for type
	 *
	 * @param sting $type The type
	 *
	 * @return SystemSettings
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}
	/**
	 * Getter for description
	 *
	 * @return string
	 */
	public function getDescription() 
	{
	    return $this->description;
	}
	/**
	 * Setter for description
	 *
	 * @param string $value The description
	 *
	 * @return SystemSettings
	 */
	public function setDescription($value) 
	{
	    $this->description = $value;
	    return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'syssettings');
	
		DaoMap::setStringType('type','varchar', 50);
		DaoMap::setStringType('value','varchar', 255);
		DaoMap::setStringType('description','varchar', 100);
	
		parent::__loadDaoMap();
	
		DaoMap::createUniqueIndex('type');
		DaoMap::commit();
	}
	public static function getByType($type)
	{
		return count($objs = self::getAllByCriteria('type = ?', array(trim($type)), true, 1, 1, array('id'=> 'desc'))) > 0 ? $objs[0] : null;
	}
}