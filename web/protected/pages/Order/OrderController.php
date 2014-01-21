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
		$js = parent::_getEndJs();
		$js .= 'pageJs.resultDivId = "resultDiv";';
		$js .= 'pageJs.searchDivId = "searchDiv";';
		$js .= 'pageJs.totalNoOfItemsId = "total_no_of_items";';
		$js .= 'pageJs.setCallbackId("getOrders", "' . $this->getOrdersBtn->getUniqueID(). '");';
		$js .= 'pageJs._infoTypes = {"custName": ' . OrderInfoType::ID_CUS_NAME. ', "custEmail" : ' . OrderInfoType::ID_CUS_EMAIL . ', "qty": ' . OrderInfoType::ID_QTY_ORDERED . '};';
		return $js;
	}
	
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
				if(($value = trim($value)) === '')
					continue;
				
				$query = FactoryAbastract::service('Order')->getDao()->getQuery();
				switch ($field)
				{
					case 'ord.orderNo': 
					case 'ord.invNo': 
					{
						$where[] =  $field . " like ? ";
						$params[] = trim($value) . '%';
						break;
					}
					case 'ord.status': 
					{
						$query->eagerLoad("Order.status", 'inner join', 'st', 'st.id = ord.statusId and st.name like ?');
						$params[] = trim($value) . '%';
						break;
					}
					case 'ord.infos.' . OrderInfoType::ID_CUS_NAME:
					{
						$query->eagerLoad("Order.infos", 'inner join', 'x', 'x.orderId = ord.id and x.active = 1 and x.typeId = ' . OrderInfoType::ID_CUS_NAME . ' AND x.value like ?');
						$params[] = '%' . trim($value) . '%';
						break;
					} 
				}
				$noSearch = false;
			}
			if($noSearch === true)
				throw new Exception("Nothing to search!");
			
			$orders = FactoryAbastract::service('Order')->findByCriteria(implode(' AND ', $where), $params, true, $pageNo, $pageSize);
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