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
	public $menuItem = 'accounting.rma';
	protected $_focusEntity = 'RMA';
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
		$statusOptions = RMA::getAllStatuses();
		
		$js = parent::_getEndJs();
		$js .= "pageJs._statusOptions=" . json_encode($statusOptions) . ";";
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
            		case 'ra.raNo': 
					{
						$where[] = 'ra.raNo = ?';
            			$params[] = $value;
						break;
					}
					case 'ra.status': 
					{
						$where[] = 'ra.status IN ('.implode(", ", array_fill(0, count($value), "?")).')';
						$params = array_merge($params, $value);
						break;
					}
					case 'ra.description':
					{
						$where[] =  'cn.description like ?';
						$params[] = "%" . $value . "%";
						break;
					}
					case 'ord.orderNo':
					{
						$query->eagerLoad("RMA.order", 'inner join', 'ord', '');
						$where[] = 'ord.orderNo = ?';
						$params[] = trim($value);
						break;
					}
					case 'cust.id':
					{
						$value = explode(',', $value);
						$where[] = 'ra.customerId IN ('.implode(", ", array_fill(0, count($value), "?")).')';
						$params = array_merge($params, $value);
						break;
					}
            		case 'pro.ids':
					{
						$value = explode(',', $value);
						$query->eagerLoad("RMA.items", 'inner join', 'ra_item', 'ra_item.RMAId = ra.id and ra_item.active = 1');
						$where[] = 'ra_item.productId in ('.implode(", ", array_fill(0, count($value), "?")).')';
						$params = array_merge($params, $value);
						break;
					}
            	}
            }

            $stats = array();

            $objects = $class::getAllByCriteria(implode(' AND ', $where), $params, true, $pageNo, $pageSize, array('ra.raNo' => 'desc'), $stats);
			
            $results['pageStats'] = $stats;
            $results['items'] = array();
            foreach($objects as $obj)
            {
            	$order = $obj->getOrder();
            	$customer = $obj->getCustomer();
            	$raItems = $obj->getRMAItems();
                $results['items'][] = $obj->getJson(array('order'=> empty($order) ? '' : $order->getJson(), 'customer'=> $customer->getJson(), 'raItems'=> $raItems ? array_map(create_function('$a', 'return $a->getJson();'), $raItems) : ''));
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
			
			$obj = $class::get($id);
			
			if(!$obj instanceof $class)
				throw new Exception('Invalid ' . $class . ' passed in');
			$obj->setActive(false)->save();
			
			$order = $obj->getOrder();
			$customer = $obj->getCustomer();
			$raItems = $obj->getRMAItems();
			$results['item'] = $obj->getJson(array('order'=> $order instanceof Order ? $order->getJson() : '', 'customer'=> $customer->getJson(), 'raItems'=> $raItems ? array_map(create_function('$a', 'return $a->getJson();'), $raItems) : ''));
		}
        catch(Exception $ex)
        {
            $errors[] = $ex->getMessage() . $ex->getTraceAsString();
        }
        $param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
}
?>
