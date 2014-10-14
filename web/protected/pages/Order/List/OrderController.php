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
	public $orderPageSize = 30;
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
		$js .= 'pageJs._loadStatuses('.json_encode($orderStatusArray).');';
		$js .= 'pageJs.totalNoOfItemsId = "total_no_of_items";';
		$js .= 'pageJs._infoTypes = {"custName": ' . OrderInfoType::ID_CUS_NAME. ', "custEmail" : ' . OrderInfoType::ID_CUS_EMAIL . ', "qty": ' . OrderInfoType::ID_QTY_ORDERED . '};';
		$js .= 'pageJs.setCallbackId("getOrders", "' . $this->getOrdersBtn->getUniqueID(). '")';
			$js .= '.setSearchCriteria(' . json_encode($this->getViewPreference()) . ')';
			$js .= ';';
		$js .= '$("searchBtn").click();';
		return $js;
	}
	public function getViewPreference()
	{
		$preferences = array();
		$preferences['ord.status'] = AccessControl::canAccessOrderStatusIds(Core::getRole());
		if(($index = array_search(OrderStatus::ID_CANCELLED, $preferences['ord.status'])) !== false)
			array_splice($preferences['ord.status'], $index, 1);
		if(($index = array_search(OrderStatus::ID_SHIPPED, $preferences['ord.status'])) !== false)
			array_splice($preferences['ord.status'], $index, 1);
		switch(Core::getRole()->getId())
		{
			case Role::ID_ACCOUNTING:
			{
				$preferences['ord.passPaymentCheck'] =  array(0);
				break;
			}
		}
		return $preferences;
	}
	
	/**
	 * Getting the orders
	 * 
	 * @param unknown $sender
	 * @param unknown $param
	 * @throws Exception
	 * 
	 */
	public function getOrders($sender, $param)
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
				
				$query = Order::getQuery();
				switch ($field)
				{
					case 'ord.orderNo': 
					case 'ord.invNo': 
					{
						$where[] =  $field . " like ? ";
						$params[] = $value.'%';
						break;
					}
					case 'ord.passPaymentCheck': 
					{
						$where[] =  $field . " = ? ";
						$params[] = $value;
						break;
					}
					case 'ord.status': 
					{
						$query->eagerLoad("Order.status", 'inner join', 'st', 'st.id = ord.statusId');
						$where[] = 'st.id IN ('.implode(", ", array_fill(0, count($value), "?")).')';
						$params = array_merge($params, $value);
						break;
					}
					case 'ord.infos.' . OrderInfoType::ID_CUS_NAME:
					{
						$query->eagerLoad("Order.infos", 'inner join', 'x', 'x.orderId = ord.id and x.active = 1 and x.typeId = ' . OrderInfoType::ID_CUS_NAME);
						$where[] = 'x.value like ?';
						$params[] = $value.'%';
						break;
					} 
				}
				$noSearch = false;
			}
			if($noSearch === true)
				throw new Exception("Nothing to search!");
			$stats = array();
			$orders = Order::getAllByCriteria(implode(' AND ', $where), $params, true, $pageNo, $pageSize, array('ord.id' => 'desc'), $stats);
			$results['pageStats'] = $stats;
			$results['items'] = array();
			foreach($orders as $order)
			{
				var_dump($order->getJson());
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