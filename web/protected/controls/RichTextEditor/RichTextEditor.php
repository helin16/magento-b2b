<?php
/**
 * The RichTextEditor
 *
 * @package    web
 * @subpackage controls
 * @author     lhe<helin16@gmail.com>
 */
class RichTextEditor extends TClientScript
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
			$clientScript->registerScriptFile('richTextEditor.js', $this->publishAsset('lib/tiny.editor.packed.js'));
			// Add fancyBox Thumbnail helper (this is optional)
			$clientScript->registerStyleSheetFile('richTextEditor.css', $this->publishAsset('lib/tinyeditor.css'));
			$this->_publishSource();
		}
	}
	private function _publishSource()
	{
		$rootDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
		$images = glob($rootDir . "*.{jpg,jpeg,png,gif}", GLOB_BRACE);
		foreach($images as $index => $image) 
			 $this->publishFilePath($image);
	}
}