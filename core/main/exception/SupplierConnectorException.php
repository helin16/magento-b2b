<?php
/**
 * The SupplierConnector Exception
 * 
 * @package    Core
 * @subpackage Exception
 * @author     lhe<helin16@gmail.com>
 */
class SupplierConnectorException extends Exception
{
	public function __construct($message, $code = 100, $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}

?>