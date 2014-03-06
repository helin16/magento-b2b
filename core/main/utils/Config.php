<?php
/**
 * Config loader
 * 
 * @package    Core
 * @subpackage Config
 * @author     lhe<helin16@gmail.com>
 */
abstract class Config
{
    const DEFAULT_CONF_FILE = 'default.conf';
    
    /**
     * The values in the config file
     * 
     * @var array
     */
	private static $_values = null;
	/**
	 * Which config file we are loading
	 * 
	 * @var string
	 */
	private static $_conf_file = null;
	/**
	 * Setting the conf file to load
	 */
	public static function setConfFile($fileName = null)
	{
		$path = self::_getConfDir($fileName);
		if(!is_file($path))
			throw new CoreException('config file NOT found: ' . $fileName);
		self::$_conf_file = $path;
	}
	/**
	 * Getting the value from the config file
	 * 
	 * @param string $service The section name that we are trying to load
	 * @param string $name    The item name that we are trying to load
	 * 
	 * @return Mixed
	 * @throws Exception
	 */
	public static function get($service, $name)
	{
	    if(self::$_values === null)
    		self::$_values = require_once(trim(self::$_conf_file) === '' ? self::_getConfDir('') : trim(self::$_conf_file));
		if(isset(self::$_values[$service]) && isset(self::$_values[$service][$name]))
			return self::$_values[$service][$name];
		throw new Exception("Service($service)/Name($name) not defined in config.");
	}
	/**
	 * Getting the path of the config file
	 * 
	 * @param string $fileName The config file name
	 * 
	 * @return string
	 */
	private static function _getConfDir($fileName)
	{
		return self::getConfigDir() . (trim($fileName) === '' ? self::DEFAULT_CONF_FILE : trim($fileName)) . '.php';
	}
	/**
	 * Getting the directory of the config files
	 * 
	 * @return string
	 */
	public static function getConfigDir()
	{
		return dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'conf' . DIRECTORY_SEPARATOR;
	}
}

?>