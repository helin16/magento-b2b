<?php
/**
 * The DetailsPage Page Abstract
 * 
 * @package    Web
 * @subpackage Class
 * @author     lhe<helin16@gmail.com>
 */
abstract class DetailsPageAbstract extends BPCPageAbstract 
{
	/**
	 * The focusing entity
	 * 
	 * @var BaseEntityAbstract
	 */
	protected $_focusEntity = null;
	/**
	 * The name of the focus entity
	 * 
	 * @var string
	 */
	protected $_focusEntityName = '';
	/**
	 * (non-PHPdoc)
	 * @see TControl::onInit()
	 */
	public function onInit($param)
	{
		parent::onInit($param);
		$class = trim($this->_focusEntityName);
		if($class === '' || !isset($this->Request['id']) )
			die('System Error: no id or class passed in');
		if(!($this->_focusEntity = $class::get($this->Request['id'])) instanceof $class)
			die('invalid item!');
	}
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
	 * Getting The end javascript
	 *
	 * @return string
	 */
	protected function _getEndJs()
	{
		$js = parent::_getEndJs();
		$js .= "pageJs.setHTMLIDs('item-div')";
		$js .= ".setItem(" . json_encode($this->_focusEntity->getJson()) . ");";
		return $js;
	}
	/**
	 * getting the focus entity
	 * 
	 * @return string
	 */
	public function getFocusEntity()
	{
		return trim($this->_focusEntity);
	}
}
?>