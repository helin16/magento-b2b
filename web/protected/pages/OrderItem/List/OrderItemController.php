<?php
/**
 * This is the OrderItemController
 * 
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class OrderItemController extends BPCPageAbstract
{
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$menuItem
	 */
	public $menuItem = 'orderitems';
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::onLoad()
	 */
	public function onLoad($param)
	{
		parent::onLoad($param);
		if(!AccessControl::canAccessOrderItemsPage(Core::getRole()))
			die(BPCPageAbstract::show404Page('Access Denied', 'You do NOT have the access to this page!'));
	}
	/**
	 * Getting The end javascript
	 *
	 * @return string
	 */
	protected function _getEndJs()
	{
		$orderStatusArray = array();
		foreach((OrderStatus::findAll()) as $os)
			$orderStatusArray[] = $os->getJson();
		
		$js = parent::_getEndJs();
		$js .= 'pageJs.resultDivId = "resultDiv";';
		$js .= 'pageJs.searchDivId = "searchDiv";';
		$js .= 'pageJs.totalNoOfItemsId = "total_no_of_items";';
		$js .= 'pageJs._infoTypes = {"custName": ' . OrderInfoType::ID_CUS_NAME. ', "custEmail" : ' . OrderInfoType::ID_CUS_EMAIL . ', "qty": ' . OrderInfoType::ID_QTY_ORDERED . '};';
		$js .= 'pageJs.setCallbackId("getOrderitems", "' . $this->getOrderItemsBtn->getUniqueID(). '")';
			$js .= '.setSearchCriteria(' . json_encode($this->_getViewPreference()) . ')';
			$js .= '.init();';
		$js .= '$("searchBtn").click();';
		return $js;
	}
	private function _getViewPreference()
	{
		return array();
	}
	/**
	 * Getting the orders
	 * 
	 * @param unknown $sender
	 * @param unknown $param
	 * @throws Exception
	 * 
	 */
	public function getOrderItems($sender, $param)
	{
		$results = $errors = array();
		try
		{
			if(!isset($param->CallbackParameter->searchCriteria) || count($serachCriteria = json_decode(json_encode($param->CallbackParameter->searchCriteria), true)) === 0)
				throw new Exception('System Error: search criteria not provided!');
			
			$pageNo = 1;
			$pageSize = DaoQuery::DEFAUTL_PAGE_SIZE;
			if(isset($param->CallbackParameter->pagination))
			{
				$pageNo = $param->CallbackParameter->pagination->pageNo;
				$pageSize = $param->CallbackParameter->pagination->pageSize;
			}
			
			$noSearch = true;
			$where = array(1);
			$params = array();
			foreach($serachCriteria as $field => $value)
			{
				if((is_array($value) && count($value) === 0) || (is_string($value) && ($value = trim($value)) === ''))
					continue;
				
				$query = FactoryAbastract::service('OrderItem')->getDao()->getQuery();
				switch ($field)
				{
					case 'ord.orderNo': 
					case 'ord.invNo': 
					{
						$query->eagerLoad("OrderItem.order", 'inner join', 'ord', 'ord.id = ord_item.orderId');
						$where[] =  $field . " like ? ";
						$params[] = $value.'%';
						break;
					}
					case 'ord_item.isOrdered': 
					{
						$where[] =  $field . " = ? ";
						$params[] = $value;
						break;
					}
					case 'ord.eta.from':
					{
						$where[] = $field . ' <= ?';
						$params[] = $value;
						break;
					}
					case 'ord.eta.to': 
					{
						$where[] = $field . ' >= ?';
						$params[] = $value;
						break;
					}
				}
				$noSearch = false;
			}
			if($noSearch === true)
				throw new Exception("Nothing to search!");
			
			$orders = FactoryAbastract::service('OrderItem')->findByCriteria(implode(' AND ', $where), $params, true, $pageNo, $pageSize, array('ord_item.eta' => 'asc'));
			$results['pageStats'] = FactoryAbastract::service('Order')->getPageStats();
			$results['items'] = array();
			foreach($orders as $order)
				$results['items'][] = $order->getJson();
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
}
?>