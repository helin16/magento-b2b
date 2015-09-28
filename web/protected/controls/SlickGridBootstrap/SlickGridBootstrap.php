<?php
/**
 * The SlickGridBootstrap Loader
 *
 * @package    web
 * @subpackage controls
 * @author     lhe<helin16@gmail.com>
 */
class SlickGridBootstrap extends TTemplateControl
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
			$folder = $this->publishFilePath(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR);
			// Add jQuery library
			// Add mousewheel plugin (this is optional)
			$clientScript->registerHeadScriptFile('SlickGrid-bootstrap.js', $folder . '/slick-bootstrap.js');
			$clientScript->registerStyleSheetFile('SlickGrid-bootstrap.css', $folder . '/slick-bootstrap.css', 'screen');
		}
	}
}