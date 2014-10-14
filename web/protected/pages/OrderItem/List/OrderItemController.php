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
		$js = parent::_getEndJs();
// 		$js .= 'pageJs._infoTypes = {"custName": ' . OrderInfoType::ID_CUS_NAME. ', "custEmail" : ' . OrderInfoType::ID_CUS_EMAIL . ', "qty": ' . OrderInfoType::ID_QTY_ORDERED . '};';
		$js .= 'pageJs.setCallbackId("getOrderitems", "' . $this->getOrderItemsBtn->getUniqueID(). '")';
			$js .= '.init("resultDiv", "searchDiv")';
			$js .= '.setSearchCriteria(' . json_encode($this->_getViewPreference()) . ')';
			$js .= '.getSearchCriteria()';
			$js .= '.getResults(true, ' . DaoQuery::DEFAUTL_PAGE_SIZE . ');';
// 		$js .= '$("searchBtn").click();';
		return $js;
	}
	/**
	 * Getting the view preferences
	 * 
	 * @return multitype:
	 */
	private function _getViewPreference()
	{
		$now = new UDate('now', SystemSettings::getSettings(SystemSettings::TYPE_SYSTEM_TIMEZONE));
		return array('ord_item.eta.from' => $now->format('Y-m-d 00:00:00'),
				'ord_item.eta.to' => $now->format('Y-m-d 23:59:59'));
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
				
				OrderItem::getQuery()->eagerLoad("OrderItem.order", 'inner join', 'ord', 'ord.id = ord_item.orderId');
				switch ($field)
				{
					case 'ord.orderNo': 
					case 'ord.invNo': 
					{
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
					case 'ord_item.eta.from':
					{
						$where[] = 'ord_item.eta >= ?';
						$params[] = $value;
						break;
					}
					case 'ord_item.eta.to': 
					{
						$where[] = 'ord_item.eta <= ?';
						$params[] = $value;
						break;
					}
				}
				$noSearch = false;
			}
			if($noSearch === true)
				throw new Exception("Nothing to search!");
			$stats = array();
			$orderItems = OrderItem::getAllByCriteria(implode(' AND ', $where), $params, true, $pageNo, $pageSize, array('ord_item.eta' => 'asc', 'ord.orderNo' => 'asc'), $stats);
			$results['pageStats'] = $stats;
			$results['items'] = array();
			foreach($orderItems as $item)
			{
				$orderItemArray = $item->getJson();
				$comments = Comments::getAllByCriteria('entityName = ? and entityId = ?', array('OrderItem', $item->getId()), true, null, DaoQuery::DEFAUTL_PAGE_SIZE, array('created' => 'desc'));
				$orderItemArray['comments'] = array_map(create_function('$a', 'return $a->getJson();'), $comments); 
				$results['items'][] = $orderItemArray;
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