<?php
class UsersController extends BPCPageAbstract
{
	public $menuItem = 'users';
	/**
	 * constructor
	 */
	public function __construct()
	{
		if(!AccessControl::canAccessUsersPage(Core::getRole()))
			die(BPCPageAbstract::show404Page('Access Denied', 'You have no access to this page!'));
		parent::__construct();
	}
	/**
	 * Getting The end javascript
	 *
	 * @return string
	 */
	protected function _getEndJs()
	{
		$js = parent::_getEndJs();
		$js .= 'pageJs.setHtmlIDs("savePanel")';
			$js .= '.setCallbackId("saveUser", "' . $this->saveUserBtn->getUniqueId() . '")';
			$js .= '.setEditUrl("/useraccount/edit/{uid}.html")';
			$js .= '.load(' . json_encode($this->_getUser()) . ', ' . json_encode($this->_getRoles()). ')';
			$js .= ';';
		return $js;
	}
	private function _getUser()
	{
		$userAccount = null;
		if(!isset($this->Request['action']) || ($method = trim($this->Request['action'])) === 'edit' && !($userAccount = FactoryAbastract::service('UserAccount')->get($this->Request['id'])) instanceof UserAccount)
			throw new Exception('Invalid params!');
		return $userAccount instanceof UserAccount ? $userAccount->getJson() : null;
	}
	private function _getRoles()
	{
		$roles = array();
		foreach(FactoryAbastract::service('Role')->findAll(true, null, DaoQuery::DEFAUTL_PAGE_SIZE, array('r.name' => 'asc')) as $role)
			$roles[] = $role->getJson();
		return $roles;
	}
	public function saveUser($sender, $params)
	{
		$results = $errors = array();
		try
		{
			Dao::beginTransaction();
			if(!isset($params->CallbackParameter->firstName) || ($firstName = trim($params->CallbackParameter->firstName)) === '')
				throw new Exception('System Error: firstName is mandatory!');
			if(!isset($params->CallbackParameter->lastName) || ($lastName = trim($params->CallbackParameter->lastName)) === '')
				throw new Exception('System Error: lastName is mandatory!');
			if(!isset($params->CallbackParameter->userName) || ($userName = trim($params->CallbackParameter->userName)) === '')
				throw new Exception('System Error: userName is mandatory!');
			if(!isset($params->CallbackParameter->roleid) || !($role = FactoryAbastract::service('Role')->get($params->CallbackParameter->roleid)) instanceof Role)
				throw new Exception('System Error: role is mandatory!');
			
			$newpassword = trim($params->CallbackParameter->newpassword);
			if(!isset($params->CallbackParameter->userid) || !($userAccount = FactoryAbastract::service('UserAccount')->get($params->CallbackParameter->userid)) instanceof UserAccount)
			{
				$userAccount = new UserAccount();
				$person = new Person();
				if($newpassword === '')
					throw new Exception('System Error: new password is mandatory!');
				$newpassword = sha1($newpassword);
			}
			else
			{
				$person = $userAccount->getPerson();
				if($newpassword === '')
					$newpassword = $userAccount->getPassword();
				else
					$newpassword = sha1($newpassword);
			}
			
			//double check whether the username has been used
			$users = FactoryAbastract::service('UserAccount')->findByCriteria('username=? and id!=?', array($userName, $userAccount->getId()), false, 1, 1);
			if(count($users) > 0)
				throw new Exception('Username(=' . $userName . ') has been used by another user, please choose another one!');
			
			$person->setFirstName($firstName)
				->setLastName($lastName);
			FactoryAbastract::service('Person')->save($person);
			
			$userAccount->setUserName($userName)
				->setPassword($newpassword)
				->setPerson($person);
			FactoryAbastract::service('UserAccount')->save($userAccount);
			
			$results = $userAccount->clearRoles()
				->addRole($role)
				->getJson();
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}
		$params->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
}
