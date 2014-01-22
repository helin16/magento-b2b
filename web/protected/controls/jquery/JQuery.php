<?php
/**
 * The SocialBtns Loader
 *
 * @package    web
 * @subpackage controls
 * @author     lhe<helin16@gmail.com>
 */
class JQuery extends TClientScript
{
	/**
	 * (non-PHPdoc)
	 * @see TControl::onLoad()
	 */
	public function onLoad($param)
	{
		$page = $this->getPage();
		if(!$page->IsPostBack || !$page->IsCallback)
		{
			$page->getClientScript()->registerHeadScriptFile('jquery', $this->publishAsset('jquery-1.10.1.min.js'));
			$page->getClientScript()->registerBeginScript('jquery.noConflict', 'jQuery.noConflict();');
		}
	}
}