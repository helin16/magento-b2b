<?php
/**
 * This is the Controller
 *
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class Controller extends CRUDPageAbstract
{
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$menuItem
	 */
	public $menuItem = 'bills';
	protected $_focusEntity = 'ReceivingItem';
	/**
	 * constructor
	 */
	public function __construct()
	{
		parent::__construct();
	}
	/**
	 * (non-PHPdoc)
	 * @see CRUDPageAbstract::_getEndJs()
	 */
	protected function _getEndJs()
	{
		$js = parent::_getEndJs();
		$js .= "pageJs";
		$js .= ".init()";
		$js .= ".getResults(true, " . $this->pageSize . ");";
		return $js;
	}
	/**
	 * Getting the items
	 *
	 * @param unknown $sender
	 * @param unknown $param
	 * @throws Exception
	 *
	 */
	public function getItems($sender, $param)
	{
		$results = $errors = array();
		try
		{
			$class = trim($this->_focusEntity);
			$pageNo = 1;
			$pageSize = DaoQuery::DEFAUTL_PAGE_SIZE;
			if(isset($param->CallbackParameter->pagination))
			{
				$pageNo = $param->CallbackParameter->pagination->pageNo;
				$pageSize = $param->CallbackParameter->pagination->pageSize;
			}
			$where = array('ri.active = :active');
			$params = array('active' => 1);
			if(isset($param->CallbackParameter->searchCriteria)) {
				$criteria = $param->CallbackParameter->searchCriteria;
				if(isset($criteria->invoiceNo) && ($invNo = trim($criteria->invoiceNo)) !== '') {
					$where[] = 'ri.invoiceNo like :invNo';
					$params['invNo'] = '%' . $invNo . '%';
				}
				if(isset($criteria->purchaseOrderIds) && count($purchaseOrderIds = array_filter(explode(',', trim($criteria->purchaseOrderIds)))) > 0) {
					$poWhere = array();
					foreach($purchaseOrderIds as $index => $purchaseOrderId){
						$key = ('purchaseOrderId' . $index);
						$poWhere[] = ':' . $key;
						$params[$key] = $purchaseOrderId;
					}
					$where[] = 'ri.purchaseOrderId in(' . implode(', ', $poWhere) . ')';
				}
				if(isset($criteria->supplierIds) && count($supplierIds = array_filter(explode(',', trim($criteria->supplierIds)))) > 0) {
					$suppWhere = array();
					foreach($supplierIds as $index => $supplierId){
						$key = ('supplierId' . $index);
						$suppWhere[] = ':' . $key;
						$params[$key] = $supplierId;
					}
					$where[] = 'po.supplierId in(' . implode(', ', $suppWhere) . ')';
				}
			}
			$sql = 'select sql_calc_found_rows ri.invoiceNo,
						po.supplierId,
						sum(ri.qty) `qty`,
						sum(ri.unitPrice) `price`,
						group_concat(distinct po.id) `poIds`,
						group_concat(distinct ri.id) `itemIds`
					from receivingitem ri
					inner join purchaseorder po on (po.id = ri.purchaseOrderId)
					where ' . implode(' AND ', $where) . '
					group by po.supplierId,  ri.invoiceNo
					limit ' . ($pageNo - 1) * $pageSize . ', ' . $pageSize;
			var_dump($sql);
			var_dump($params);
			$rows = Dao::getResultsNative($sql, $params);

			$stats = array();
			$statsResult = Dao::getSingleResultNative('select found_rows()', array(), PDO::FETCH_NUM);
			$stats['totalRows'] = intval($statsResult[0]);
			$stats['pageSize'] = $pageSize;
			$stats['pageNo'] = $pageNo;
			$stats['totalPages'] = intval(ceil($stats['totalRows'] / $stats['pageSize']));

			$results['items'] = array();
			foreach($rows as $row)
			{
				$pos = count($poIds = explode(',', $row['poIds'])) === 0 ? array() : PurchaseOrder::getAllByCriteria('id in (' . implode(',', array_fill(0, count($poIds), '?')) . ')', $poIds);
				$results['items'][] = array(
					'invoiceNo' => $row['invoiceNo'],
					'supplier' => Supplier::get($row['supplierId'])->getJson(),
					'totalQty' => $row['qty'],
					'totalPrice' => $row['price'],
					'purchaseOrders' => array_map(create_function('$a', 'return $a->getJson();'), $pos),
					'poIds' => explode(',', $row['poIds']),
					'itemIds' => explode(',', $row['itemIds'])
				);
			}
			$results['pageStats'] = $stats;
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
}
?>
