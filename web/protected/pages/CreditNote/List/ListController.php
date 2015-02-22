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
		if(!AccessControl::canAccessProductsPage(Core::getRole()))
			die('You do NOT have access to this page');
	}
	/**
	 * (non-PHPdoc)
	 * @see CRUDPageAbstract::_getEndJs()
	 */
	protected function _getEndJs()
	{
		$js = parent::_getEndJs();
		$js .= "pageJs._bindSearchKey()";
		$js .= ".setCallbackId('deactivateItems', '" . $this->deactivateItemBtn->getUniqueID() . "')";
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
                $pageSize = $param->CallbackParameter->pagination->pageSize * 3;
            }
            
            $serachCriteria = isset($param->CallbackParameter->searchCriteria) ? json_decode(json_encode($param->CallbackParameter->searchCriteria), true) : array();

            $where = array(1);
            $params = array();
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
						$where[] = 'cn.applyTo = ?';
						$params[] = $value;
						break;
					}
					case 'cn.description':
					{
						$where[] =  'cn.description = ?';
						$params[] = $value;
						break;
					}
					case 'ord.orderNo':
					{
						$query->eagerLoad("cn.order", 'inner join', 'ord', 'ord.id = cn.orderId');
						$where[] = 'ord.id = ?';
						$params[] = $value;
						break;
					}
					case 'cust.id':
					{
						$query->eagerLoad("cn.customer", 'inner join', 'cust', 'cust.id = cn.customerId');
						$where[] = 'cust.id = ?';
						$params[] = $value;
						break;
					}
					case 'pro.nameOrSku':
					{
						// TODO: ask lin
					}
            	}
            }

            $stats = array();

            $objects = $class::getAllByCriteria(implode(' AND ', $where), $params, false, $pageNo, $pageSize, array('cn.creditNoteNo' => 'desc'), $stats);

            $results['pageStats'] = $stats;
            $results['items'] = array();
            foreach($objects as $obj)
            {
            	$order = $obj->getOrder();
            	$customer = $obj->getCustomer();
            	$creditNoteItems = $obj->getCreditNoteItems();
                $results['items'][] = $obj->getJson(array('order'=> empty($order) ? '' : $order->getJson(), 'customer'=> $customer->getJson(), 'creditNoteItems'=> $creditNoteItems ? array_map(create_function('$a', 'return $a->getJson();'), $creditNoteItems) : ''));
            }
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
			
			$customer = Customer::get($id);
			
			if(!$customer instanceof Customer)
				throw new Exception();
			$customer->setActive(false)
				->save();
			$results['item'] = $customer->getJson();
		}
        catch(Exception $ex)
        {
            $errors[] = $ex->getMessage() . $ex->getTraceAsString();
        }
        $param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
}
?>
