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
	 * @var string
	 */
	protected $_focusEntity = null;
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
		$class = trim($this->_focusEntity);
		if($class === '' || !isset($this->Request['id']) )
			die('System Error: no id or class passed in');
		if(!($entity = $class::get($this->Request['id'])) instanceof $class)
			die('invalid item!');
		
		$js .= "pageJs.setHTMLIDs('item-div')";
		$js .= ".setItem(" . json_encode($entity->getJson()) . ");";
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