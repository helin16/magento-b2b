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
		$js .= 'pageJs.totalNoOfItemsId = "total_no_of_items";';
		$js .= 'pageJs.setCallbackId("getOrders", "' . $this->getOrdersBtn->getUniqueID(). '");';
		$js .= 'pageJs.getResults(true, ' . DaoQuery::DEFAUTL_PAGE_SIZE. ');';
		return $js;
	}
	
	public function getOrders($sender, $params)
	{
		$results = $errors = array();
		try
		{
			$pageNo = 1;
			$pageSize = DaoQuery::DEFAUTL_PAGE_SIZE;
			if(isset($params->CallbackParameter->pagination))
			{
				$pageNo = $params->CallbackParameter->pagination->pageNo;
				$pageSize = $params->CallbackParameter->pagination->pageSize;
			}
			
			$orders = FactoryAbastract::service('Order')->findAll(true, $pageNo, $pageSize);
			$results['pageStats'] = FactoryAbastract::service('Order')->getPageStats();
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
		$params->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
}
?>