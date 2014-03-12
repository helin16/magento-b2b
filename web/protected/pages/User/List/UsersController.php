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
		$js .= 'pageJs.setResultDiv("resultDiv")';
			$js .= '.setTotalUserCountDiv("totalUsers")';
			$js .= '.setCallbackId("getUsers", "' . $this->getUsersBtn->getUniqueId() . '")';
			$js .= '.setCallbackId("deleteUser", "' . $this->deleteUserBtn->getUniqueId() . '")';
			$js .= '.setCallbackId("getRoles", "' . $this->getRolesBtn->getUniqueId() . '")';
			$js .= '.getUsers($("searchBtn"), true);';
		return $js;
	}
	/**
	 * Getting the list of users
	 * 
	 * @param unknown $sender
	 * @param unknown $params
	 * 
	 * @throws Exception
	 */
	public function getUsers($sender, $param)
	{
		$results = $errors = array();
		try
		{
			if(!isset($param->CallbackParameter->searchCriteria) || ($serachCriteria = trim($param->CallbackParameter->searchCriteria)) === '')
				$serachCriteria = '';
				
			$pageNo = 1;
			$pageSize = DaoQuery::DEFAUTL_PAGE_SIZE;
			if(isset($param->CallbackParameter->pagination))
			{
				$pageNo = $param->CallbackParameter->pagination->pageNo;
				$pageSize = $param->CallbackParameter->pagination->pageSize;
			}
			
			$where = '`ua`.id != :sysId';
			$params = array('sysId' => UserAccount::ID_SYSTEM_ACCOUNT);
			if($serachCriteria !== '')
			{
				$query = FactoryAbastract::service('UserAccount')->getDao()->getQuery();
				$query->eagerLoad("UserAccount.person", 'inner join', 'ord', '`p`.id = `ua`.personId and (`p`.firstName like :firstName and `p`.lastName like :lastName)');
				$params['firstName'] = $serachCriteria . '%';
				$params['lastName'] = $serachCriteria . '%';
				$where .= ' OR `ua`.username like :username';
				$params['username'] = $serachCriteria . '%';
			}
			
			$users = FactoryAbastract::service('UserAccount')->findByCriteria($where, $params, true, $pageNo, $pageSize);
			$results['pageStats'] = FactoryAbastract::service('UserAccount')->getPageStats();
			$results['items'] = array();
			foreach($users as $item)
				$results['items'][] = $item->getJson();
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
	/**
	 * deactive a user
	 * 
	 * @param unknown $sender
	 * @param unknown $params
	 * 
	 * @throws Exception
	 */
	public function deleteUser($sender, $param)
	{
		$results = $errors = array();
		try
		{
			if(!isset($param->CallbackParameter->userId) || !($userAccount = FactoryAbastract::service('UserAccount')->get(trim($param->CallbackParameter->userId))) instanceof UserAccount)
				throw new Exception("Invalid user account passed for deletion!");
			$userAccount->setActive(false);
			FactoryAbastract::service('UserAccount')->save($userAccount);
			$results = $userAccount->getJson();
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
	/**
	 * get all roles
	 * 
	 * @param unknown $sender
	 * @param unknown $params
	 * 
	 * @throws Exception
	 */
	public function getRoles($sender, $param)
	{
		$results = $errors = array();
		try
		{
			$items = FactoryAbastract::service('Role')->findAll();
			$results['items'] = array();
			foreach($items as $item)
				$results['items'][] = $item->getJson();
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
}
