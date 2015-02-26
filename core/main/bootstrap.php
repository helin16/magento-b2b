<?php
date_default_timezone_set('UTC');
/**
 * Boostrapper for the Core module
 *
 * @package Core
 * @author  lhe
 */
abstract class SystemCoreAbstract
{
    /**
     * autoloading function
     *
     * @param string $className The class that we are trying to autoloading
     *
     * @return boolean Whether we loaded the class
     */
	public static function autoload($className)
	{
		$base = dirname(__FILE__);
		$autoloadPaths = array(
			$base . '/conf/',
			$base . '/db/',
			$base . '/entity/',
			$base . '/entity/asset/',
			$base . '/entity/store/',
			$base . '/entity/store/Accounting/',
			$base . '/entity/store/Logistics/',
			$base . '/entity/store/Order/',
			$base . '/entity/store/Product/',
			$base . '/entity/store/Purchase/',
			$base . '/entity/store/Shipment/',
			$base . '/entity/store/Tools/',
			$base . '/entity/system/',
			$base . '/exception/',
			$base . '/utils/',
			$base . '/utils/connector/',
			$base . '/utils/connector/courier/',
			$base . '/utils/connector/magento/',
			$base . '/utils/connector/magento/Order/',
			$base . '/utils/connector/xero/',
			$base . '/utils/connector/xero/class/',
			$base . '/utils/html_parser/',
			$base . '/utils/PDF/',
		);
		foreach ($autoloadPaths as $path)
		{
			if (file_exists($file = trim($path . $className . '.php')))
			{
				require_once $file;
				return true;
			}
		}
		return false;
	}
}
spl_autoload_register(array('SystemCoreAbstract','autoload'));
// Bootstrap the Prado framework
require_once dirname(__FILE__) . '/../3rdParty/PHPExcel/Classes/PHPExcel.php';
require_once dirname(__FILE__) . '/../3rdParty/PHPMailer/PHPMailerAutoload.php';
<<<<<<< HEAD
// require_once dirname(__FILE__) . '/../3rdParty/HTML2PDF/html2pdf.class.php';
require_once dirname(__FILE__) . '/../3rdParty/XeroOAuth-PHP/XeroOAuth.php';
=======
>>>>>>> refs/heads/master
require_once dirname(__FILE__) . '/../3rdParty/framework/prado.php';

?>
