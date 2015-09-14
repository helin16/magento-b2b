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
		$js .= 'pageJs.totalSumId = "total_amount";';
		$js .= 'pageJs.totalDueId = "total_due";';
		$js .= 'pageJs._infoTypes = {"custName": ' . OrderInfoType::ID_CUS_NAME. ', "custEmail" : ' . OrderInfoType::ID_CUS_EMAIL . ', "qty": ' . OrderInfoType::ID_QTY_ORDERED . '};';
		$js .= 'pageJs.setCallbackId("getOrders", "' . $this->getOrdersBtn->getUniqueID(). '")';
		$js .= '.init()';
		$js .= '.setSearchCriteria(' . json_encode($this->getViewPreference()) . ');';
		$js .= '$("searchBtn").click();';
		return $js;
	}
	public function getViewPreference()
	{
		$preferences = array();
		$preferences['ord.status'] = AccessControl::canAccessOrderStatusIds(Core::getRole());
		if(intval(Core::getRole()->getId()) === Role::ID_WAREHOUSE) {
			$preferences['ord.status'][] = OrderStatus::ID_INSUFFICIENT_STOCK;
			$preferences['ord.passPaymentCheck'][] = 1;
		}
		if(($index = array_search(OrderStatus::ID_CANCELLED, $preferences['ord.status'])) !== false)
			array_splice($preferences['ord.status'], $index, 1);
		if(($index = array_search(OrderStatus::ID_SHIPPED, $preferences['ord.status'])) !== false)
			array_splice($preferences['ord.status'], $index, 1);
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
			$innerJoinStrings = array();
			$sort = isset($serachCriteria['ord.sort']) ? (trim($serachCriteria['ord.sort']) === 'asc' ? 'asc' : 'desc') : 'desc';
			foreach($serachCriteria as $field => $value)
			{
				if((is_array($value) && count($value) === 0) || (is_string($value) && ($value = trim($value)) === ''))
					continue;

				$query = Order::getQuery();
				switch ($field)
				{
					case 'ord.orderNo':
					case 'ord.invNo':
					case 'ord.pONo':
					{
						$where[] =  $field . " like ? ";
						$params[] = '%' . $value . '%';
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
						$innerJoinStrings[] = 'inner join orderstatus st on (st.id = ord.statusId)';
						$where[] = 'st.id IN ('.implode(", ", array_fill(0, count($value), "?")).')';
						$params = array_merge($params, $value);
						break;
					}
					case 'ord.type':
					{
						$where[] =  $field . " = ? ";
						$params[] = $value;
						break;
					}
					case 'orderDate_from':
					case 'orderDate_to':
					{
						$where[] =  "orderDate " . ($field === 'orderDate_from' ? '>' : '<') . "= ? ";
						$params[] = trim(new UDate($value));
						break;
					}
					case 'invDate_from':
					case 'invDate_to':
					{
						$where[] =  "invDate " . ($field === 'invDate_from' ? '>' : '<') . "= ? ";
						$params[] = trim(new UDate($value));
						break;
					}
					case 'ord.infos.' . OrderInfoType::ID_CUS_NAME:
					{
						$query->eagerLoad("Order.customer", 'inner join', 'x', 'x.id = ord.customerId and x.active = 1');
						$innerJoinStrings[] = 'inner join customer x on (x.id = ord.customerId and x.active = 1)';
						$where[] = 'x.name like ?';
						$params[] = '%' . $value.'%';
						break;
					}
					case 'delivery_method':
					{
						$values = explode('{|}', $value);
						$vs = array();
						foreach($values as $v) {
							if(($v = trim($v)) === '')
								continue;
							$vs[] = preg_replace('/,/', '', $v, 1);
						}
						if(count($vs) > 0) {
							$query->eagerLoad("Order.infos", 'inner join', 'x1', 'x1.orderId = ord.id and x1.active = 1');
							$innerJoinStrings[] = 'inner join orderinfo x1 on (x1.orderId = ord.id and x1.active = 1)';
							$where[] = 'x1.value in (' . implode(', ', array_fill(0, count($vs), '?')) . ')';
							$params = array_merge($params, $vs);
						}
						break;
					}
					case 'extraSearchCriteria':
					{
						$where[] = $value;
						break;
					}
				}
				$noSearch = false;
			}
			if($noSearch === true)
				throw new Exception("Nothing to search!");
			$stats = array();
			$orders = Order::getAllByCriteria(implode(' AND ', $where), $params, true, $pageNo, $pageSize, array('ord.id' => $sort), $stats);
			$results['pageStats'] = $stats;
			$results['items'] = array();
			foreach($orders as $order)
			{
				$results['items'][] = $order->getJson();
			}
			$sql = 'select sum(`ord`.totalAmount) `totalAmount`, sum(`ord`.totalPaid) `totalPaid`, sum(`ord`.totalCreditNoteValue) `totalCreditNoteValue`, sum(pay.value) `paidViaCredit` from `order` ord ' . implode(' ', $innerJoinStrings) . ' left join payment pay on (pay.active = 1 and pay.orderId = ord.id and methodId = ' . PaymentMethod::ID_STORE_CREDIT. ')  where ord.active = 1 AND (' . implode(' AND ', $where) . ')';
			$sumResult = Dao::getResultsNative($sql, $params);
			$results['totalAmount'] = count($sumResult) > 0 ? $sumResult[0]['totalAmount'] : 0;
			$results['totalPaid'] = count($sumResult) > 0 ? $sumResult[0]['totalPaid'] : 0;
			$results['totalCreditNoteValue'] = count($sumResult) > 0 ? $sumResult[0]['totalCreditNoteValue'] : 0;
			$results['paidViaCredit'] = count($sumResult) > 0 ? $sumResult[0]['paidViaCredit'] : 0;
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
}
?>