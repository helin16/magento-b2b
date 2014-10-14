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
			$publishedPath = $this->publishFilePath(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR);
			
			$clientScript->registerScriptFile('richTextEditor.js', $publishedPath . '/tiny.editor.packed.js');
			// Add fancyBox Thumbnail helper (this is optional)
			$clientScript->registerStyleSheetFile('richTextEditor.css', $publishedPath . '/tinyeditor.css');
		}
	}
}