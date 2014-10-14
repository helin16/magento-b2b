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
	 * Getting The end javascript
	 *
	 * @return string
	 */
	protected function _getEndJs()
	{
		$js = parent::_getEndJs();
		$js .= 'pageJs.setCallbackId("changePwd", "' . $this->changePwdBtn->getUniqueID(). '");';
		$js .= 'pageJs.setCallbackId("changePersonInfo", "' . $this->changePersonInfoBtn->getUniqueID(). '");';
		return $js;
	}
	public function changePwd($sender, $param)
	{
		$results = $errors = array();
		try
		{
			if(!isset($param->CallbackParameter->oldPwd) || (($oldPwd = trim($param->CallbackParameter->oldPwd)) === '') || (sha1($oldPwd) !== Core::getUser()->getPassword()))
				throw new Exception("Invalid old password!");
			if(!isset($param->CallbackParameter->newPwd) || (($newPwd = trim($param->CallbackParameter->newPwd)) === ''))
				throw new Exception("No new password provided!");
			if(!isset($param->CallbackParameter->confirmNewPwd) || (($confirmNewPwd = trim($param->CallbackParameter->confirmNewPwd)) === ''))
				throw new Exception("No confirmNewPwd provided!");
			if($confirmNewPwd !== $newPwd)
				throw new Exception("New passwrod and confirm password NOT match!");
			Core::getUser()->setPassword(sha1($newPwd))
				->save();
			Core::setUser(UserAccount::get(Core::getUser()->getId()), Core::getRole());
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
			Core::getUser()->getPerson()
				->setFirstName($firstName)
				->setLastName($lastName)
				->save();
			Core::setUser(UserAccount::get(Core::getUser()->getId()), Core::getRole());
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