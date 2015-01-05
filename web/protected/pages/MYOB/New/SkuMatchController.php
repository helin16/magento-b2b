<?php
/**
 * This is the PriceMatchController
 * 
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class SkuMatchController extends BPCPageAbstract
{
	public $PageSize = 10;
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$menuItem
	 */
	public $menuItem = 'skuMatch';
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::onLoad()
	 */
	public function onLoad($param)
	{
		parent::onLoad($param);
		if(!AccessControl::canAccessPriceMatchPage(Core::getRole()))
			die(BPCPageAbstract::show404Page('Access Denied', 'You do NOT have the access to this page!'));
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
		$js .= 'pageJs';
		$js .= '.load("sku_match_div");';
		return $js;
	}
	
}
?>