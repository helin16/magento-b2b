<?php
/**
 * The SlickGrid Loader
 *
 * @package    web
 * @subpackage controls
 * @author     lhe<helin16@gmail.com>
 */
class SlickGrid extends TClientScript
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
			$clientScript->registerHeadScriptFile('SlickGrid.jQuery.drag.js', $folder . '/lib/jquery.event.drag-2.2.js');
			$clientScript->registerHeadScriptFile('SlickGrid.jQuery.drop.js', $folder . '/lib/jquery.event.drop-2.2.js');
			$clientScript->registerHeadScriptFile('SlickGrid.core.js', $folder . '/slick.core.js');
			$clientScript->registerHeadScriptFile('SlickGrid.js', $folder . '/slick.grid.js');
			$clientScript->registerStyleSheetFile('SlickGrid.css', $folder . '/slick.grid.css', 'screen');
		}
	}
}