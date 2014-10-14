<?php
/**
 * The Fancy Select Box
 *
 * @package    web
 * @subpackage controls
 * @author     lhe<helin16@gmail.com>
 */
class Chosen extends TClientScript
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
			// Add chosen main JS and CSS files
			$folder = $this->publishFilePath(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'source' . DIRECTORY_SEPARATOR);
			
			$clientScript->registerStyleSheetFile('chosen.css', $folder . '/chosen.css', 'screen');
			$clientScript->registerScriptFile('chosen.jquery', $folder . '/chosen.jquery.min.js');
			$this->_publishSource();
		}
	}
	
	private function _publishSource()
	{
		$rootDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'source'.DIRECTORY_SEPARATOR;
		$dirs = array('', 'docsupport'.DIRECTORY_SEPARATOR);
		foreach($dirs as $dir)
		{
			$images = glob($rootDir . $dir ."*.{jpg,jpeg,png,gif}", GLOB_BRACE);
			foreach($images as $index => $image) 
				 $this->publishFilePath($image);
		}
	}
}