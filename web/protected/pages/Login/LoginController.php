<?php
/**
 * The login page
 * 
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class LoginController extends BPCPageAbstract
{
	public function onLoad($param)
	{
		parent::onLoad($param);
		if(Core::getUser() instanceof UserAccount)
			$this->getResponse()->redirect('/');
		$cScripts = BPCPageAbstract::getLastestJS(get_class($this));
	    if (isset($cScripts['js']) && ($lastestJs = trim($cScripts['js'])) !== '')
	        $this->getPage()->getClientScript()->registerScriptFile('pageJs', $this->publishAsset($lastestJs));
	    if (isset($cScripts['css']) && ($lastestCss = trim($cScripts['css'])) !== '')
	        $this->getPage()->getClientScript()->registerStyleSheetFile('pageCss', $this->publishAsset($lastestCss));
	    
	    if(!$this->IsPostBack)
	    {
	    	$this->username->focus();
	    	$this->errorDiv->visible = false;
	    }
	}
	/**
	 * Login action
	 * 
	 * @param unknown $sender
	 * @param unknown $params
	 * 
	 * @throws Exception
	 */
    public function login($sender, $params)
    {
    	$this->errorMsg->Text = "";
    	$this->errorDiv->visible = false;
        if( ($username = trim($this->username->Text)) === '')
        {
        	$this->errorMsg->setText('username not provided!');
        	$this->errorDiv->visible = true;
        	return;
        }
        if(($password = trim($this->password->Text)) === '')
        {
        	$this->errorMsg->setText('password not provided!');
        	$this->errorDiv->visible = true;
        	return;
        }
        try
        {
	        $authManager=$this->getApplication()->getModule('auth');
	        if(!$authManager->login($username, $password))
	        {
	        	$this->errorMsg->setText('Invalid username and password!');
	        	$this->errorDiv->visible = true;
	        	return;
	        }
	        $this->getResponse()->redirect('/');
        }
        catch(Exception $ex)
        {
        	$this->errorMsg->setText($ex->getMessage());
        	$this->errorDiv->visible = true;
        	return;
        }
    }
}