<?php
/**
 * The QTip tool tip
 *
 * @package    web
 * @subpackage controls
 * @author     lhe<helin16@gmail.com>
 */
class QTip extends TClientScript
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
			$clientScript->registerStyleSheetFile('qtip.css', $this->publishAsset('source/jquery.qtip.min.css'), 'screen');
			$clientScript->registerScriptFile('qtip.js', $this->publishAsset('source/jquery.qtip.min.js'));
			$this->_publishSource();
		}
	}
	
	private function _publishSource()
	{
		$rootDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'source'.DIRECTORY_SEPARATOR;
		$dirs = array('');
		foreach($dirs as $dir)
		{
			$images = glob($rootDir . $dir ."*.{jpg,jpeg,png,gif}", GLOB_BRACE);
			foreach($images as $index => $image) 
				 $this->publishFilePath($image);
		}
	}
}