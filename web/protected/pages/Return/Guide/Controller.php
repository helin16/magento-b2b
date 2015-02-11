<?php
class Controller extends BPCPageAbstract
{
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$menuItem
	 */
	public $menuItem = 'return.guide';
	/**
	 * Getting The end javascript
	 *
	 * @return string
	 */
	protected function _getEndJs()
	{
		$js = parent::_getEndJs();
		$js .= "pageJs";
		$js .= ".setCallbackId('searchOrders', '" . $this->searchOrdersBtn->getUniqueID() . "')";
		$js .= ".init('item-div');";
		return $js;
	}
	public function searchOrders($sender, $param)
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
					case 'ord.type':
						{
							$where[] =  $field . " = ? ";
							$params[] = $value;
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
			}
			if(count($params) === 0)
				throw new Exception("Nothing to search!");
			$stats = array();
			$orders = Order::getAllByCriteria(implode(' AND ', $where), $params, true, $pageNo, $pageSize, array('ord.id' => 'desc'), $stats);
			$results['pageStats'] = $stats;
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