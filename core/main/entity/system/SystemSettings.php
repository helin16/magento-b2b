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
	 * Getting Settings Object
	 * 
	 * @param string $type The type string
	 * 
	 * @return String
	 */
	public static function getSettings($type)
	{
		$settings = FactoryAbastract::dao(__CLASS__)->findByCriteria('type=?', array($type), 1, 1);
		return trim(count($settings) === 0 ? '' : $settings[0]->getValue());
	}
	/**
	 * adding a new Settings Object
	 * 
	 * @param string $type The type string
	 */
	public static function addSettings($type, $value)
	{
		$class = __CLASS__;
		$settings = FactoryAbastract::dao($class)->findByCriteria('type=?', array($type), 1, 1);
		$setting = ((count($settings) === 0 ? new $class() : $settings[0]));
		$setting->setType($type);
		$setting->setValue($value);
		$setting->setActive(true);
		FactoryAbastract::dao($class)->save($setting);
	}
	/**
	 * Removing Settings Object
	 * 
	 * @param string $type The type string
	 */
	public static function removeSettings($type)
	{
		FactoryAbastract::dao(__CLASS__)->updateByCriteria('set active = 0', 'type = ?', array($type));
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
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'syssettings');
	
		DaoMap::setStringType('type','varchar', 50);
		DaoMap::setStringType('value','varchar', 255);
	
		parent::__loadDaoMap();
	
		DaoMap::createUniqueIndex('type');
		DaoMap::commit();
	}
}