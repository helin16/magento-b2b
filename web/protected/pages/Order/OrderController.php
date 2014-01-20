<?php
/**
 * This is the OrderController
 * 
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class OrderController extends BPCPageAbstract
{
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
		$js .= 'pageJs.resultDivId = "resultDiv";';
		$js .= 'pageJs.setCallbackId("getOrders", "' . $this->getOrdersBtn->getUniqueID(). '");';
		$js .= 'pageJs.getResults(true);';
		return $js;
	}
	
	public function getOrders($sender, $param)
	{
		$results = $errors = array();
		try
		{
			$orders = FactoryAbastract::service('Order')->findAll(true, 1, DaoQuery::DEFAUTL_PAGE_SIZE);
			$results['items'] = array();
			foreach($orders as $order)
			{
				$results['items'][] = $order->getJson();
			}
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
}
?>