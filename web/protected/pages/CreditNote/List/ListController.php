<?php
/**
 * This is the listing page for customer
 *
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class ListController extends CRUDPageAbstract
{
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$menuItem
	 */
	public $menuItem = 'accounting.creditnote';
	protected $_focusEntity = 'CreditNote';
	/**
	 * constructor
	 */
	public function __construct()
	{
		parent::__construct();
		if(!AccessControl::canAccessAccountsPage(Core::getRole()))
			die('You do NOT have access to this page');
	}
	/**
	 * (non-PHPdoc)
	 * @see CRUDPageAbstract::_getEndJs()
	 */
	protected function _getEndJs()
	{
		$applyToOptions = CreditNote::getApplyToTypes();


		$js = parent::_getEndJs();
		if(isset($_REQUEST['orderid']) && ($order = Order::get(trim($_REQUEST['orderid'])))instanceof Order)
			$js .= "pageJs._order=" . json_encode($order->getJson()) . ";";
		$js .= "pageJs._applyToOptions=" . json_encode($applyToOptions) . ";";
		$js .= "pageJs._bindSearchKey()";
		$js .= ".setCallbackId('deactivateItems', '" . $this->deactivateItemBtn->getUniqueID() . "')";
		$js .= ".init(true, " . $this->pageSize . ");";
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

            $serachCriteria = isset($param->CallbackParameter->searchCriteria) ? json_decode(json_encode($param->CallbackParameter->searchCriteria), true) : array();
            $where = array('cn.active = 1');
            $params = array();
            $innerJoinStrings = array();
            foreach($serachCriteria as $field => $value)
            {
            	if((is_array($value) && count($value) === 0) || (is_string($value) && ($value = trim($value)) === ''))
            		continue;

            	$query = $class::getQuery();
            	switch ($field)
            	{
            		case 'cn.creditNoteNo':
					{
						$where[] = 'cn.creditNoteNo = ?';
            			$params[] = $value;
						break;
					}
					case 'cn.applyTo':
					{
						$where[] = 'cn.applyTo IN ('.implode(", ", array_fill(0, count($value), "?")).')';
						$params = array_merge($params, $value);
						break;
					}
					case 'cn.description':
					{
						$where[] =  'cn.description like ?';
						$params[] = "%" . $value . "%";
						break;
					}
					case 'ord.orderNo':
					{
						$query->eagerLoad("CreditNote.order", 'inner join', 'ord', '');
						$where[] = 'ord.orderNo = ?';
						$params[] = trim($value);
						$innerJoinStrings[] = 'inner join `order` ord on (ord.id = cn.orderId)';
						break;
					}
					case 'cust.id':
					{
						$value = explode(',', $value);
						$where[] = 'cn.customerId IN ('.implode(", ", array_fill(0, count($value), "?")).')';
						$params = array_merge($params, $value);
						break;
					}
					case 'pro.ids':
					{
						$value = explode(',', $value);
						$query->eagerLoad("CreditNote.items", 'inner join', 'cn_item', 'cn_item.creditNoteId = cn.id and cn_item.active = 1');
						$where[] = 'cn_item.productId in ('.implode(", ", array_fill(0, count($value), "?")).')';
						$innerJoinStrings[] = 'inner join `creditnoteitem` cn_item on (cn_item.creditNoteId = cn.id and cn_item.active = 1)';
						$params = array_merge($params, $value);
						break;
					}
            	}
            }

            $stats = array();
            $objects = $class::getAllByCriteria(implode(' AND ', $where), $params, true, $pageNo, $pageSize, array('cn.creditNoteNo' => 'desc'), $stats);
            $results['pageStats'] = $stats;
            $results['items'] = array();
            foreach($objects as $obj)
            {
            	$order = $obj->getOrder();
            	$customer = $obj->getCustomer();
            	$creditNoteItems = $obj->getCreditNoteItems();
                $results['items'][] = $obj->getJson(array('order'=> empty($order) ? '' : $order->getJson(), 'customer'=> $customer->getJson(), 'creditNoteItems'=> $creditNoteItems ? array_map(create_function('$a', 'return $a->getJson();'), $creditNoteItems) : ''));
            }
            $sql = 'select sum(`cn`.totalValue) `totalValue`, sum(`cn`.totalPaid) `totalPaid` from `creditnote` cn ' . implode(' ', $innerJoinStrings) . ' where cn.active = 1 AND (' . implode(' AND ', $where) . ')';
            $sumResult = Dao::getResultsNative($sql, $params);
            $results['totalValue'] = count($sumResult) > 0 ? $sumResult[0]['totalValue'] : 0;
            $results['totalPaid'] = count($sumResult) > 0 ? $sumResult[0]['totalPaid'] : 0;
        }
        catch(Exception $ex)
        {
            $errors[] = $ex->getMessage() . $ex->getTraceAsString();
        }
        $param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
	/**
	 * save the items
	 *
	 * @param unknown $sender
	 * @param unknown $param
	 * @throws Exception
	 *
	 */
	public function deactivateItems($sender, $param)
	{
		$results = $errors = array();
		try
		{
			$class = trim($this->_focusEntity);
			$id = isset($param->CallbackParameter->item_id) ? $param->CallbackParameter->item_id : array();

			$creditNote = CreditNote::get($id);

			if(!$creditNote instanceof CreditNote)
				throw new Exception('Invalid Credit Note passed in');
			$creditNote->setActive(false)->save();
			$results['item'] = $creditNote->getJson(array('order'=> $creditNote->getOrder() instanceof Order ? '' : $creditNote->getOrder()->getJson(), 'customer'=> $creditNote->getCustomer()->getJson()));
		}
        catch(Exception $ex)
        {
            $errors[] = $ex->getMessage() . $ex->getTraceAsString();
        }
        $param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
}
?>
