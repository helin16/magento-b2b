<?php
/**
 * The SocialBtns Loader
 *
 * @package    web
 * @subpackage controls
 * @author     lhe<helin16@gmail.com>
 */
class FancyBox extends TClientScript
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
			// Add jQuery library
			$clientScript->registerScriptFile('jquery', $this->publishAsset('lib/lib/jquery-1.10.1.min.js'));
			// Add mousewheel plugin (this is optional)
			$clientScript->registerScriptFile('jquery.mousewheel', $this->publishAsset('lib/lib/jquery.mousewheel-3.0.6.pack.js'));
			// Add fancyBox main JS and CSS files
			$clientScript->registerScriptFile('jquery.fancybox', $this->publishAsset('lib/source/jquery.fancybox.js'));
			$clientScript->registerStyleSheetFile('jquery.fancybox.css', $this->publishAsset('lib/source/jquery.fancybox.css'), 'screen');
			// Add fancyBox Button helper
			$clientScript->registerStyleSheetFile('jquery.fancybox.btn.css', $this->publishAsset('lib/source/helpers/jquery.fancybox-buttons.css'));
			$clientScript->registerScriptFile('jquery.fancybox.btn', $this->publishAsset('lib/source/helpers/jquery.fancybox-buttons.js'));
			// Add fancyBox Thumbnail helper (this is optional)
			$clientScript->registerStyleSheetFile('jquery.fancybox.thumb.css', $this->publishAsset('lib/source/helpers/jquery.fancybox-thumbs.css'));
			$clientScript->registerScriptFile('jquery.fancybox.thumb', $this->publishAsset('lib/source/helpers/jquery.fancybox-thumbs.js'));
			// Add fancyBox Media helper (this is optional) -->
			$clientScript->registerScriptFile('jquery.fancybox.media', $this->publishAsset('lib/source/helpers/jquery.fancybox-media.js'));
			
			$clientScript->registerBeginScript('jquery.noConflict', 'jQuery.noConflict();');
			$this->_publishSource($clientScript);
		}
	}
	private function _publishSource()
	{
		$rootDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'source' . DIRECTORY_SEPARATOR;
		$dirs = array('', DIRECTORY_SEPARATOR . 'helpers');
		foreach($dirs as $dir)
		{
			$images = glob($rootDir . $dir . DIRECTORY_SEPARATOR ."*.{jpg,jpeg,png,gif}", GLOB_BRACE);
			foreach($images as $index => $image) 
				 $this->publishFilePath($image);
		}
	}
}