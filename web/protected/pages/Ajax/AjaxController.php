<?php
/**
 * Ajax Controller
 *
 * @package	web
 * @subpackage	Controller-Page
 *
 * @version	1.0
 *
 * @todo :NOTE If anyone copies this controller, then you require this method to profile ajax requests
 */
class AjaxController extends TService
{
  	/**
  	 * Run
  	 */
  	public function run()
  	{
//   		if(!($this->getUser()->getUserAccount() instanceof UserAccount))
//   			die (BPCPageAbstract::show404Page('Invalid request',"No defined access."));

  		$results = $errors = array();
		try
		{
  			$method = '_' . ((isset($this->Request['method']) && trim($this->Request['method']) !== '') ? trim($this->Request['method']) : '');
            if(!method_exists($this, $method))
                throw new Exception('No such a method: ' . $method . '!');
			$results = $this->$method($_REQUEST);
		}
		catch (Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$this->getResponse()->flush();
        $this->getResponse()->appendHeader('Content-Type: application/json');
        $this->getResponse()->write(StringUtilsAbstract::getJson($results, $errors));
  	}
	/**
	 * Getting the comments for an entity
	 *
	 * @param array $params
	 *
	 * @return string The json string
	 */
  	private function _getComments(Array $params)
  	{
  		if(!isset($params['entityId']) || !isset($params['entity']) || ($entityId = trim($params['entityId'])) === '' || ($entity = trim($params['entity'])) === '')
  			throw  new Exception('SYSTEM ERROR: INCOMPLETE DATA PROVIDED');

  		$pageSize = (isset($params['pageSize']) && ($pageSize = trim($params['pageSize'])) !== '' ? $pageSize : DaoQuery::DEFAUTL_PAGE_SIZE);
  		$pageNo = (isset($params['pageNo']) && ($pageNo = trim($params['pageNo'])) !== '' ? $pageNo : 1);
  		$orderBy = (isset($params['orderBy']) ? $params['orderBy'] : array('created' => 'desc'));

  		$where ='entityName = ? and entityId = ?';
  		$sqlParams = array($entity, $entityId);
  		if(isset($params['type']) && ($commentType = trim($params['type'])) !== '')
  		{
  			$where .= 'and type = ?';
  			$sqlParams[] = trim($commentType);
  		}
  		$returnArray = json_encode(array());
  		$stats = array();
  		$commentsArray = Comments::getAllByCriteria($where, $sqlParams, true, $pageNo, $pageSize, $orderBy, $stats);
  		$results = array();
  		$results['items'] = array_map(create_function('$a', 'return $a->getJson();'), $commentsArray);
  		$results['pageStats'] = $stats;
  		return $results;
  	}
  	private function _getCustomers(Array $params)
  	{
  		$searchTxt = trim(isset($params['searchTxt']) ? $params['searchTxt'] : '');
  		if($searchTxt === '')
  			throw new Exception('SearchTxt is needed');
  		$pageSize = (isset($params['pageSize']) && ($pageSize = trim($params['pageSize'])) !== '' ? $pageSize : DaoQuery::DEFAUTL_PAGE_SIZE);
  		$pageNo = (isset($params['pageNo']) && ($pageNo = trim($params['pageNo'])) !== '' ? $pageNo : null);
  		$orderBy = (isset($params['orderBy']) ? $params['orderBy'] : array());

  		$where = array('name like :searchTxt or email like :searchTxt or contactNo like :searchTxt');
  		$sqlParams = array('searchTxt' => '%' . $searchTxt . '%');
  		$stats = array();
  		$items = Customer::getAllByCriteria(implode(' AND ', $where), $sqlParams, true, $pageNo, $pageSize, $orderBy, $stats);
  		$results = array();
  		$results['items'] = array_map(create_function('$a', 'return $a->getJson();'), $items);
  		$results['pageStats'] = $stats;
  		return $results;
  	}
  	private function _getSuppliers(Array $params)
  	{
  		$searchTxt = trim(isset($params['searchTxt']) ? $params['searchTxt'] : '');
  		if($searchTxt === '')
  			throw new Exception('SearchTxt is needed');
  		$pageSize = (isset($params['pageSize']) && ($pageSize = trim($params['pageSize'])) !== '' ? $pageSize : DaoQuery::DEFAUTL_PAGE_SIZE);
  		$pageNo = (isset($params['pageNo']) && ($pageNo = trim($params['pageNo'])) !== '' ? $pageNo : null);
  		$orderBy = (isset($params['orderBy']) ? $params['orderBy'] : array());

  		$where = array('name like :searchTxt or description like :searchTxt');
  		$sqlParams = array('searchTxt' => '%' . $searchTxt . '%');
  		$stats = array();
  		$items = Supplier::getAllByCriteria(implode(' AND ', $where), $sqlParams, true, $pageNo, $pageSize, $orderBy, $stats);
  		$results = array();
  		$results['items'] = array_map(create_function('$a', 'return $a->getJson();'), $items);
  		$results['pageStats'] = $stats;
  		return $results;
  	}
  	private function _getProducts(Array $params)
  	{
  		$searchTxt = trim(isset($params['searchTxt']) ? $params['searchTxt'] : '');
  		if($searchTxt === '')
  			throw new Exception('SearchTxt is needed');
  		$pageSize = (isset($params['pageSize']) && ($pageSize = trim($params['pageSize'])) !== '' ? $pageSize : DaoQuery::DEFAUTL_PAGE_SIZE);
  		$pageNo = (isset($params['pageNo']) && ($pageNo = trim($params['pageNo'])) !== '' ? $pageNo : null);
  		$orderBy = (isset($params['orderBy']) ? $params['orderBy'] : array());

  		$where = array('name like :searchTxt or mageId = :searchTxtExact or sku = :searchTxtExact');
  		$sqlParams = array('searchTxt' => '%' . $searchTxt . '%', 'searchTxtExact' => $searchTxt);
  		$stats = array();
  		$items = Product::getAllByCriteria(implode(' AND ', $where), $sqlParams, true, $pageNo, $pageSize, $orderBy, $stats);
  		$results = array();
  		$results['items'] = array_map(create_function('$a', 'return $a->getJson();'), $items);
  		$results['pageStats'] = $stats;
  		return $results;
  	}
  	private function _getPurchaseOrders(Array $params)
  	{
  		$searchTxt = trim(isset($params['searchTxt']) ? $params['searchTxt'] : '');
  		if($searchTxt === '')
  			throw new Exception('SearchTxt is needed');
  		$pageSize = (isset($params['pageSize']) && ($pageSize = trim($params['pageSize'])) !== '' ? $pageSize : DaoQuery::DEFAUTL_PAGE_SIZE);
  		$pageNo = (isset($params['pageNo']) && ($pageNo = trim($params['pageNo'])) !== '' ? $pageNo : null);
  		$orderBy = (isset($params['orderBy']) ? $params['orderBy'] : array());

  		$where = array('purchaseOrderNo like :searchTxt');
  		$sqlParams = array('searchTxt' => '%' . $searchTxt . '%'/*, 'searchTxtExact' => $searchTxt*/);
  		$stats = array();
  		$items = PurchaseOrder::getAllByCriteria(implode(' AND ', $where), $sqlParams, true, $pageNo, $pageSize, $orderBy, $stats);
  		$results = array();
  		$results['items'] = array_map(create_function('$a', 'return $a->getJson();'), $items);
  		$results['pageStats'] = $stats;
  		return $results;
  	}
  	private function _getInsufficientStockOrders($params)
  	{
  		$pageNo = isset($params['pageNo']) ? trim($params['pageNo']) : 1;
  		$pageSize = isset($params['pageSize']) ? trim($params['pageSize']) : DaoQuery::DEFAUTL_PAGE_SIZE;
  		$sql = "select distinct pro.id, sum(ord_item.qtyOrdered) `orderedQty`, pro.stockOnHand, pro.stockOnPO
  				from product pro
  				inner join orderitem ord_item on (ord_item.productId = pro.id and ord_item.active = 1)
  				inner join `order` ord on (ord.id = ord_item.orderId and ord.active = 1 and ord.type in (:ordType1, :ordType2) and ord.statusId in (:ordStatusId1, :ordStatusId2))
  				where pro.active = 1
  				group by pro.id
  				having `orderedQty` > (pro.stockOnHand + pro.stockOnPO)
  				order by ord.id";
  		$result = Dao::getResultsNative($sql, array('ordType1' => Order::TYPE_ORDER, 'ordType2' => Order::TYPE_INVOICE, 'ordStatusId1' => OrderStatus::ID_NEW, 'ordStatusId2' => OrderStatus::ID_INSUFFICIENT_STOCK), PDO::FETCH_ASSOC);
  		if(count($result) === 0)
  			return array();

		$productMap = array();
		foreach($result as $row) {
			$productMap[$row['id']] = $row;
		}

  		OrderItem::getQuery()->eagerLoad('OrderItem.order', 'inner join', 'ord', 'ord.id = ord_item.orderId and ord.active = 1 and ord.type in (?,?) and ord.statusId in (?,?)');
  		$sqlParams = array(Order::TYPE_ORDER, Order::TYPE_INVOICE, OrderStatus::ID_NEW, OrderStatus::ID_INSUFFICIENT_STOCK);
  		$where = 'ord_item.active = 1 and ord_item.productId in (' . implode(', ', array_fill(0, count(array_keys($productMap)), '?')) . ')';
  		$sqlParams = array_merge($sqlParams, array_keys($productMap));
  		$items = OrderItem::getAllByCriteria($where, $sqlParams, true, $pageNo, $pageSize, array('ord_item.id' => 'desc'));
  		$return = array();
  		foreach($items as $item) {
  			$extra = array('totalOrderedQty' => isset($productMap[$item->getProduct()->getId()]) ? $productMap[$item->getProduct()->getId()]['orderedQty'] : 0);
			$return[] = $item->getJson($extra);
  		}
  		return array('items' => $return);
  	}
  	/**
  	 * Getting all the delivery methods
  	 *
  	 * @param unknown $params
  	 *
  	 * @return array
  	 */
  	private function _getDeliveryMethods($params)
  	{
  		$searchTxt = (isset($params['searchTxt']) && ($searchTxt = trim($params['searchTxt'])) !== '' ? $searchTxt : '');
  		$sql = 'select distinct value from orderinfo where value like ? and active = 1 and typeId = ' . OrderInfoType::ID_MAGE_ORDER_SHIPPING_METHOD;
  		$results = array();
  		$results['items'] = array_map(create_function('$a', 'return $a["value"];'), Dao::getResultsNative($sql, array('%' . trim($searchTxt) . '%'), PDO::FETCH_ASSOC));
  		return $results;
  	}
  	/**
  	 * Getting an entity
  	 *
  	 * @param unknown $params
  	 *
  	 * @throws Exception
  	 * @return multitype:
  	 */
  	private function _get($params)
  	{
  		if(!isset($params['entityName']) || ($entityName = trim($params['entityName'])) === '')
  			throw new Exception('What are we going to get?');
  		if(!isset($params['entityId']) || ($entityId = trim($params['entityId'])) === '')
  			throw new Exception('What are we going to get with?');
  		return ($entity = $entityName::get($entityId)) instanceof BaseEntityAbstract ? $entity->getJson() : array();
  	}
  	/**
  	 * Getting All for entity
  	 *
  	 * @param unknown $params
  	 *
  	 * @throws Exception
  	 * @return multitype:multitype:
  	 */
  	private function _getAll($params)
  	{
  		if(!isset($params['entityName']) || ($entityName = trim($params['entityName'])) === '')
  			throw new Exception('What are we going to get?');
  		$searchTxt = trim(isset($params['searchTxt']) ? trim($params['searchTxt']) : '');
  		$searchParams = isset($params['searchParams']) ? $params['searchParams'] : array();
  		$pageNo = isset($params['pageNo']) ? trim($params['pageNo']) : null;
  		$pageSize = isset($params['pageSize']) ? trim($params['pageSize']) : DaoQuery::DEFAUTL_PAGE_SIZE;
  		$active = isset($params['active']) ? intval($params['active']) : 1;
  		$orderBy = isset($params['orderBy']) ? trim($params['orderBy']) : array();

  		$stats = array();
  		$items = $entityName::getAllByCriteria($searchTxt, $searchParams, $active, $pageNo, $pageSize, $orderBy, $stats);
  		return array('items' => array_map(create_function('$a', 'return $a->getJson();'), $items), 'pagination' => $stats);
  	}
}
?>