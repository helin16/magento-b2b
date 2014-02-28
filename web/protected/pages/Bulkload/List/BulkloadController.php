<?php
/**
 * This is the OrderController
 * 
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class BulkloadController extends BPCPageAbstract
{
	public $orderPageSize = 10;
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$menuItem
	 */
	public $menuItem = 'bulkload';
	
	private $_validOptions;
	
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::onLoad()
	 */
	public function onLoad($param)
	{
		parent::onLoad($param);
	}
	
	private function _getAllBulkloadOptions()
	{
		$this->_validOptions = array();
		$this->_validOptions[] = array('value' => 'price', 'url' => '/bulkload/price.html', 'display' => 'Price');
		return $this->_validOptions;
	}
	
	/**
	 * Getting The end javascript
	 *
	 * @return string
	 */
	protected function _getEndJs()
	{
		$js = parent::_getEndJs();
		
		$validOptions = json_encode($this->_getAllBulkloadOptions());
		$js .= 'pageJs.mainContentDiv = "mainContent";';
		$js .= 'pageJs.validOptions = '.$validOptions.';';
		$js .= 'pageJs.loadBulkloadOptions();';
		return $js;
	}
}
?>