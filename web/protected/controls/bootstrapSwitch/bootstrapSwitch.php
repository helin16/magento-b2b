<?php
/**
 * The select2 Loader
 *
 * @package    web
 * @subpackage controls
 * @author     lhe<helin16@gmail.com>
 */
class bootstrapSwitch extends TClientScript
{
	/**
	 * (non-PHPdoc)
	 * @see TControl::onLoad()
	 */
	public function onLoad($param)
	{
		if(!$this->getPage()->IsPostBack || !$this->getPage()->IsCallback)
		{
			$clientScript = $this->getPage()->getClientScript();
			$folder = $this->publishFilePath(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'src/dist' . DIRECTORY_SEPARATOR);
			// Add jQuery library
			// Add mousewheel plugin (this is optional)
			$clientScript->registerHeadScriptFile('bootstrapSwitch.js', $folder . '/js/bootstrap-switch.js');
			$clientScript->registerStyleSheetFile('bootstrapSwitch.css', $folder . '/css/bootstrap3/bootstrap-switch.css', 'screen');
		}
	}
}