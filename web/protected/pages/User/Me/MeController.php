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
		
	}
	public function changePersonInfo($sender, $param)
	{
		
	}
}
?>