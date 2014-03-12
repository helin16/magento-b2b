<?php
/**
 * This is the me page
 * 
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class MeController extends BPCPageAbstract
{
	/**
	 * The user account that we are editing!
	 * 
	 * @var UserAccount
	 */
	public $userAccount = null;
	/**
	 * Constructor
	 */
	public function __construct()
	{
		if(!AccessControl::canAccessUsersPage(Core::getRole()) && ($this->Request['id'] !== 'me'))
			die(BPCPageAbstract::show404Page('Access Denied', 'You have no access to this page!'));
		parent::__construct();
		
		$this->userAccount = ($this->Request['id'] === 'me') ? Core::getUser() : FactoryAbastract::service('UserAccount')->get($this->Request['id']);
	}
	/**
	 * (non-PHPdoc)
	 * @see TControl::onPreInit()
	 */
	public function onPreInit($param)
	{
		parent::onPreInit($param);
		if($this->Request['id'] !== 'me')
			$this->getPage()->setMasterClass('Application.layout.BlankLayout');
	}
	/**
	 * Getting The end javascript
	 *
	 * @return string
	 */
	protected function _getEndJs()
	{
		$js = parent::_getEndJs();
		$js .= 'pageJs.setCallbackId("changePwd", "' . $this->changePwdBtn->getUniqueID(). '")';
			$js .= '.setCallbackId("changePersonInfo", "' . $this->changePersonInfoBtn->getUniqueID(). '")';
			$js .= '.userAccount=' . json_encode($this->userAccount->getJson()) . ';';
		return $js;
	}
	/**
	 * refresh core user
	 * 
	 * @return MeController
	 */
	private function _refreshCoreUser()
	{
		Core::setUser(FactoryAbastract::service('UserAccount')->get(Core::getUser()->getId()), Core::getRole());
		return $this;
	}
	public function changePwd($sender, $param)
	{
		$results = $errors = array();
		try
		{
			if(!isset($param->CallbackParameter->oldPwd) || (($oldPwd = trim($param->CallbackParameter->oldPwd)) === '') || (sha1($oldPwd) !== $this->userAccount->getPassword()))
				throw new Exception("Invalid old password!");
			if(!isset($param->CallbackParameter->newPwd) || (($newPwd = trim($param->CallbackParameter->newPwd)) === ''))
				throw new Exception("No new password provided!");
			if(!isset($param->CallbackParameter->confirmNewPwd) || (($confirmNewPwd = trim($param->CallbackParameter->confirmNewPwd)) === ''))
				throw new Exception("No confirmNewPwd provided!");
			if($confirmNewPwd !== $newPwd)
				throw new Exception("New passwrod and confirm password NOT match!");
			$this->userAccount->setPassword(sha1($newPwd));
			FactoryAbastract::service('UserAccount')->save($this->userAccount);
			$this->_refreshCoreUser();
			$results['succ'] = true;
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
	public function changePersonInfo($sender, $param)
	{
		$results = $errors = array();
		try
		{
			if(!isset($param->CallbackParameter->firstName) || (($firstName = trim($param->CallbackParameter->firstName)) === '') )
				throw new Exception("Invalid firstName!");
			if(!isset($param->CallbackParameter->lastName) || (($lastName= trim($param->CallbackParameter->lastName)) === '') )
				throw new Exception("Invalid lastName!");
			FactoryAbastract::service('Person')->save(Core::getUser()->getPerson()->setFirstName($firstName)->setLastName($lastName));
			$this->_refreshCoreUser();
			$results['succ'] = true;
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
}
?>