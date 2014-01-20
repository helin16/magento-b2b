<?php
Prado::using('System.Security.TUser');

class WebUser extends TUser
{
    /**
	 * @var userAccount Instance
	 */
	private $userAccount;
	
	/**
	 * Get the userAccount Instance
	 *
	 * @return UserAccount
	 */
	public function getUserAccount()
	{
		return $this->userAccount;
	}
	
	/**
	 * Set the userAccount
	 *
	 * @param UserAccount $userAccount
	 */
	public function setUserAccount(UserAccount $userAccount)
	{
		$this->userAccount = $userAccount;
	}
	
	public function saveToString()
	{	
		$a=array(Core::serialize(),parent::saveToString());
		return serialize($a);
	}
	
	/**
	 * Load the userAccount from the session
	 *
	 * @param unknown_type $data
	 * @return unknown
	 */
	public function loadFromString($data)
	{		
		if(!empty($data))
		{
//			var_dump(unserialize($data));
			list($coreStuff, $str) = unserialize($data);

			Core::unserialize($coreStuff);
			
			$this->userAccount = Core::getUser();
			return parent::loadFromString($str);
		}
		else
			return $this;
	}
}
?>