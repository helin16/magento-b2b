<?php
/**
 * The jQueryUI Loader
 *
 * @package    web
 * @subpackage controls
 * @author     lhe<helin16@gmail.com>
 */
class jQueryUI extends TClientScript
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
			$folder = $this->publishFilePath(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR);
			// Add jQuery library
			// Add mousewheel plugin (this is optional)
			$clientScript->registerHeadScriptFile('jQuery.ui.core.js', $folder . '/jQuery.ui.core.js');
			$clientScript->registerHeadScriptFile('jQuery.ui.widget.js', $folder . '/jQuery.ui.widget.js');
			$clientScript->registerHeadScriptFile('jQuery.ui.mouse.js', $folder . '/jQuery.ui.mouse.js');
			$clientScript->registerHeadScriptFile('jQuery.ui.resizable.js', $folder . '/jQuery.ui.resizable.js');
			$clientScript->registerHeadScriptFile('jQuery.ui.sortable.js', $folder . '/jQuery.ui.sortable.js');
		}
	}
}