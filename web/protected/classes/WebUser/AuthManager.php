<?php
Prado::using('System.Security.TAuthManager');
/**
 * customized AuthManager
 * 
 * @package    Hydra-web
 * @subpackage Classes
 * @author     lhe<lhe@bytecraft.com.au>
 *
 */
class AuthManager extends TAuthManager
{
    /**
     * (non-PHPdoc)
     * @see TAuthManager::onAuthorize()
     */
	public function onAuthorize($param)
	{
	    $application = $this->getApplication();
	    //if this is a call back function and its session timed out/invalid, then redirect the page to homepage
	    if($this->getRequest()->contains(TPage::FIELD_CALLBACK_TARGET) && (!$application->getAuthorizationRules()->isUserAllowed($application->getUser(),$application->getRequest()->getRequestType(),$application->getRequest()->getUserHostAddress())))
	    {
	        // Create a callback adapter which counstructor will set up TCallbackReponseAdapter in the HttpResponse class adapter property
	        $callbackAdapter = new TActivePageAdapter(new TPage());
	        // Redirect (now the adapter is not null)
	        $this->Response->redirect('/');
	        // Create a html writer
	        $writer = $this->Response->createHtmlWriter();
	        // Render the response
	        $callbackAdapter->renderCallbackResponse($writer);
	        //Flush the output
	        $application->flushOutput();
	        //exit application do not process the futher part
	        exit;
	    }
        parent::onAuthorize($param);
        $u = Core::getUser();
        if ($u instanceof UserAccount)
        {
            $r = Core::getRole();
            Core::setUser($u, $r);
        }
	}
}
?>