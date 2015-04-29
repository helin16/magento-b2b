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
		$preloadData = array('supplierId' => trim($this->Request['supplierId']), 'invoiceNo' => trim($this->Request['invoiceNo']));
		$js .= "pageJs";
		$js .= ".setPreloadData(" . json_encode($preloadData) .")";
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
			if(!isset($param->CallbackParameter->searchCriteria->supplierId) || !($supplier = Supplier::get(trim($param->CallbackParameter->searchCriteria->supplierId))) instanceof Supplier)
				throw new Exception('Invalid Supplier provided');
			if(!isset($param->CallbackParameter->searchCriteria->invoiceNo) || ($invoiceNo = trim($param->CallbackParameter->searchCriteria->invoiceNo)) === '')
				throw new Exception('Invalid Invoice number provided');
			ReceivingItem::getQuery()->eagerLoad('ReceivingItem.purchaseOrder', 'inner join', 'rec_item_po', 'rec_item_po.id = rec_item.purchaseOrderId');
			if(ReceivingItem::countByCriteria('rec_item_po.supplierId = ? and rec_item.invoiceNo = ?', array($supplier->getId(), $invoiceNo)) === 0)
				throw new Exception('There is no such a invoice(invoice No=' . $invoiceNo . ') for supplier:' . $supplier->getName());

			$sql = 'select ri.productId,
						sum(ri.qty) `qty`,
						sum(ri.unitPrice) `price`,
						group_concat(distinct ri.id) `itemIds`,
						group_concat(distinct po.id) `poIds`
					from receivingitem ri
					inner join purchaseorder po on (po.id = ri.purchaseOrderId)
					where ri.active = 1 and ri.invoiceNo = ? and po.supplierId = ?
					group by ri.productId,  ri.unitPrice';
			$params = array($invoiceNo, $supplier->getId());
			$rows = Dao::getResultsNative($sql, $params);
			$results['supplier'] = $supplier->getJson();
			$results['items'] = array();
			foreach($rows as $row)
			{
				$items = count($itemIds = explode(',', $row['itemIds'])) === 0 ? array() : ReceivingItem::getAllByCriteria('id in (' . implode(',', array_fill(0, count($itemIds), '?')) . ')', $itemIds);
				$pos = count($poIds = explode(',', $row['poIds'])) === 0 ? array() : PurchaseOrder::getAllByCriteria('id in (' . implode(',', array_fill(0, count($poIds), '?')) . ')', $poIds);
				$results['items'][] = array(
					'product' => Product::get($row['productId'])->getJson(),
					'totalQty' => $row['qty'],
					'totalPrice' => $row['price'],
					'items' => array_map(create_function('$a', 'return $a->getJson();'), $items),
					'purchaseOrders' => array_map(create_function('$a', 'return $a->getJson();'), $pos)
				);
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
