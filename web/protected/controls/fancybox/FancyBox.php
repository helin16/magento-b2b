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
			$folder = $this->publishFilePath(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR);
			// Add jQuery library
			// Add mousewheel plugin (this is optional)
			$clientScript->registerHeadScriptFile('jquery.mousewheel', $folder . '/lib/jquery.mousewheel-3.0.6.pack.js');
			// Add fancyBox main JS and CSS files
			$clientScript->registerHeadScriptFile('jquery.fancybox',  $folder . '/source/jquery.fancybox.js');
			$clientScript->registerStyleSheetFile('jquery.fancybox.css', $folder . '/source/jquery.fancybox.css', 'screen');
			
			// Add fancyBox Button helper
			$clientScript->registerStyleSheetFile('jquery.fancybox.btn.css', $folder . '/source/helpers/jquery.fancybox-buttons.css');
			$clientScript->registerHeadScriptFile('jquery.fancybox.btn', $folder . '/source/helpers/jquery.fancybox-buttons.js');
			// Add fancyBox Thumbnail helper (this is optional)
			$clientScript->registerStyleSheetFile('jquery.fancybox.thumb.css', $folder . '/source/helpers/jquery.fancybox-thumbs.css');
			$clientScript->registerHeadScriptFile('jquery.fancybox.thumb', $folder . '/source/helpers/jquery.fancybox-thumbs.js');
			// Add fancyBox Media helper (this is optional) -->
			$clientScript->registerHeadScriptFile('jquery.fancybox.media', $folder . '/source/helpers/jquery.fancybox-media.js');
			
			$clientScript->registerBeginScript('jquery.noConflict', 'jQuery.noConflict();');
		}
	}
}