<?php
/**
 * The JQueryEasyUI Loader
 *
 * @package    web
 * @subpackage controls
 * @author     lhe<helin16@gmail.com>
 */
class jQueryEasyUI extends TClientScript
{
	/**
	 * (non-PHPdoc)
	 * @see TControl::onLoad()
	 */
	public function onLoad($param)
	{
		$clientScript = $this->getPage()->getClientScript();
		if(!$this->getPage()->IsPostBack || !$this->getPage()->IsCallback)
		{
			$folder = $this->publishFilePath(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR);
			$clientScript->registerStyleSheetFile('jquery.easyui.css', $folder . '/themes/default/easyui.css');
			$clientScript->registerStyleSheetFile('jquery.easyui.icon.css', $folder . '/themes/icon.css');
			$clientScript->registerScriptFile('jquery.easyui.js', $folder . '/jquery.easyui.min.js');
		}
	}
}