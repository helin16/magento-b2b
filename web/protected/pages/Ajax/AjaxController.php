<?php
/**
 * Ajax Controller
 * 
 * @package	web
 * @subpackage	Controller-Page
 * 
 * @version	1.0
 * 
 * @todo :NOTE If anyone copies this controller, then you require this method to profile ajax requests
 */
class AjaxController extends TService 
{
	// NOTE If anyone copies this controller, then you require this method to profile ajax requests
	public function __construct()
	{
		// Services dont have constructors Apparently
		//parent::__construct();
	}
	
	/**
	 * Init
	 *
	 * @param unknown_type $config
	 */
  	public function init($config) 
  	{
		
  	}
  	
  	/**
  	 * Run
  	 *
  	 */
  	public function run() 
  	{
  		if(!(Core::getUser() instanceof UserAccount) || !(Core::getRole() instanceof Role))
  			throw new Exception("No defined access.");
  		
  		if(sizeof($_REQUEST) > 0)
			$this->_processRequest($_REQUEST);
  	}

  	private function _processRequest(Array $params)
  	{
  		var_dump($params);
  		
  	}

}




?>