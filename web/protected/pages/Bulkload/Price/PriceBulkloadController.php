<?php
/**
 * This is the OrderController
 * 
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class PriceBulkloadController extends BPCPageAbstract
{
	public $orderPageSize = 10;
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$menuItem
	 */
	public $menuItem = 'bulkload';
	
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::onLoad()
	 */
	public function onLoad($param)
	{
		parent::onLoad($param);
	}
	
	
	/**
	 * Getting The end javascript
	 *
	 * @return string
	 */
	protected function _getEndJs()
	{
		$js = parent::_getEndJs();
		
		// Setup the dnd listeners.
		$js .= 'pageJs.dropShowDiv.dropDiv = "drop_file";';
		$js .= 'pageJs.dropShowDiv.showDiv = "show_file";';
		$js .= 'pageJs.intializeFileReader();';
		$js .= 'pageJs.initializeFileHandler();';
		return $js;
	}
}
?>