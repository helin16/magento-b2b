<?php
/**
 * The StaticsPage Page Abstract
 * 
 * @package    Web
 * @subpackage Class
 * @author     lhe<helin16@gmail.com>
 */
abstract class StaticsPageAbstract extends BPCPageAbstract 
{
	/**
	 * @var TCallback
	 */
	private $_getDataBtn;
	/**
	 * loading the page js class files
	 */
	protected function _loadPageJsClass()
	{
		parent::_loadPageJsClass();
		$thisClass = __CLASS__;
		$cScripts = self::getLastestJS(__CLASS__);
		if (isset($cScripts['js']) && ($lastestJs = trim($cScripts['js'])) !== '')
			$this->getPage()->getClientScript()->registerScriptFile($thisClass . 'Js', $this->publishFilePath(dirname(__FILE__) . DIRECTORY_SEPARATOR . $lastestJs));
		if (isset($cScripts['css']) && ($lastestCss = trim($cScripts['css'])) !== '')
			$this->getPage()->getClientScript()->registerStyleSheetFile($thisClass . 'Css', $this->publishFilePath(dirname(__FILE__) . DIRECTORY_SEPARATOR . $lastestCss));
	    return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see TControl::onInit()
	 */
	public function onInit($param)
	{
		parent::onInit($param);
	
		$this->_getDataBtn = new TCallback();
		$this->_getDataBtn->ID = 'getDataBtn';
		$this->_getDataBtn->OnCallback = 'Page.getData';
		$this->getControls()->add($this->_getDataBtn);
	}
	/**
	 * Getting The end javascript
	 *
	 * @return string
	 */
	protected function _getEndJs()
	{
		$js = parent::_getEndJs();
		$js .= "pageJs";
		$js .= ".setCallbackId('getData', '" . $this->_getDataBtn->getUniqueID() . "')";
		$js .= ".setHTMLIDs('statics-div');";
		return $js;
	}
	/**
	 * getData
	 *
	 * @param unknown $sender
	 * @param unknown $param
	 * @throws Exception
	 *
	 */
	public function getData($sender, $param){}
}
?>