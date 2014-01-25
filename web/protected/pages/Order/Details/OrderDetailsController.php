<?php
/**
 * This is the OrderDetailsController
 * 
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class OrderDetailsController extends BPCPageAbstract
{
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$menuItem
	 */
	public $menuItem = 'order';
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::onLoad()
	 */
	public function onLoad($param)
	{
		parent::onLoad($param);
		if(!$this->isPostBack)
		{
		}
	}
	/**
	 * Getting The end javascript
	 *
	 * @return string
	 */
	protected function _getEndJs()
	{
		$js = parent::_getEndJs();
		$order = FactoryAbastract::service('Order')->get($this->Request['orderId']);
		if(!$order instanceof Order)
			die('Invalid Order!');
		$js .= "pageJs.init();";
		return $js;
	}
}
?>