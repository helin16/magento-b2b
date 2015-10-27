<?php
class API
{
	public static function autoload($className)
	{
		$autoloadPaths = array(
			dirname(__FILE__) . '/',
			dirname(__FILE__) . '/classes/'
		);
		foreach ($autoloadPaths as $path)
		{
		    $filePath = $path . $className . '.php';
			if (file_exists($filePath))
			{
				require_once $filePath;
				return true;
			}
		}
		return false;
	}
}

spl_autoload_register(array('API','autoload'));

// Bootstrap the core for its autoloader settings
require_once (dirname(__FILE__) . '/../../core/main/bootstrap.php');

?>