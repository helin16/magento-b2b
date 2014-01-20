<?php
/**
 * The abstract class
 * 
 * @author lhe
 *
 */
abstract class FactoryAbastract
{
	// Define service holders
	private static $_elements = array();
	/**
	 * Getting the singleton service object
	 * 
	 * @param string $entityClassName The entity class name of the service
	 * @throws HydraDaoException
	 * 
	 * @return BaseServiceAbastract
	 */
	public static function service($entityClassName)
	{
		$entityClassName = trim($entityClassName).'Service';
		if(!isset(self::$_elements[$entityClassName]))
		{
			if(!class_exists($entityClassName))
				throw new HydraDaoException("Invalid class : ".$entityClassName);
			
			self::$_elements[$entityClassName] = new $entityClassName();
		}
		return self::$_elements[$entityClassName];
	}
    /**
     * Getting the singleton dao object
     * 
	 * @param string $entityClassName The entity class name of the service
	 * @throws HydraDaoException
     * 
     * @return EntityDao
     */
	public static function dao($entityClassName)
	{
		return self::_getDao(trim($entityClassName), 'EntityDao');
	}
    /**
     * Getting the treedao
     * 
	 * @param string $entityClassName The entity class name of the service
	 * @throws HydraDaoException
	 * 
     * @return TreeDAO
     */	
	public static function tree($entityClassName)
	{
		return self::_getDao(trim($entityClassName), 'TreeDAO');
	}
	/**
	 * getting the treedao, genericdao
	 *  
	 * @param string $entityClassName The entity class name of the service
	 * @param string $type            The type of the dao: GenericDAO, TreeDAO
	 *  
	 * @throws HydraDaoException
	 * 
	 * @return GenericDAO|TreeDAO
	 */
	private static function _getDao($entityClassName, $type)
	{
		$holderName = $entityClassName . $type;
		if(!isset(self::$_elements[$holderName]))
		{	
			if(!class_exists($entityClassName))
				throw new DaoException("Invalid class : ".$entityClassName);
			self::$_elements[$holderName] = new $type($entityClassName);
		}
		return self::$_elements[$holderName];
	}
}