<?php
/**
 * The Core Exception
 * 
 * @package    Core
 * @subpackage Exception
 * @author     lhe<helin16@gmail.com>
 */
class CoreException extends Exception
{
	public function __construct($message)
	{
		parent::__construct($message, 0);
	}
}

?>