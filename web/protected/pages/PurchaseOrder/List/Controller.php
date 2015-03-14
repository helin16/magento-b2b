<?php
/**
 * This is the PurchaseOrder List
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
	public $menuItem = 'purchaseorders';
	/**
	 * (non-PHPdoc)
	 * @see CRUDPageAbstract::$_focusEntity
	 */
	protected $_focusEntity = 'PurchaseOrder';
	/**
	 * constructor
	 */
	public function __construct()
	{
		parent::__construct();
// 		if(!AccessControl::canAccessPurcahseOrdersPage(Core::getRole()))
// 			die('You do NOT have access to this page');
	}
	/**
	 * (non-PHPdoc)
	 * @see CRUDPageAbstract::_getEndJs()
	 */
	protected function _getEndJs()
	{
		$suppliersArray = array();
		foreach(Supplier::getAll() as $os)
			$suppliersArray[] = $os->getJson();
		$statusOptions = PurchaseOrder::getStatusOptions();
		$js = parent::_getEndJs();
		$js .= 'pageJs';
		$js .= ".setCallbackId('deactivateItems', '" . $this->deactivateItemBtn->getUniqueID() . "')";
		$js .= "._bindSearchKey()";
		$js .= "._loadSuppliers(" . json_encode($suppliersArray) . ")";
		$js .= "._setStatusOptions(" . json_encode($statusOptions) . ")";
		$js .= "._loadChosen()";
		$js .= "._loadDataPicker()";
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
            $pageNo = 1;
            $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE;
            if(isset($param->CallbackParameter->pagination))
            {
                $pageNo = $param->CallbackParameter->pagination->pageNo;
                $pageSize = $param->CallbackParameter->pagination->pageSize;
            }

            $serachCriteria = isset($param->CallbackParameter->searchCriteria) ? json_decode(json_encode($param->CallbackParameter->searchCriteria), true) : array();
            $stats = array();
            $where = array(1);
            $params = array();
            $noSearch = true;

            foreach($serachCriteria as $field => $value)
            {
            	if((is_array($value) && count($value) === 0) || (is_string($value) && ($value = trim($value)) === ''))
            		continue;
				
            	switch ($field)
            	{
            		case 'po.purchaseOrderNo':
            			{
            				$where[] =  $field . " like ? ";
            				$params[] = '%' . $value . '%';
            				break;
            			}
            		case 'po.supplierRefNo':
            			{
            				$where[] =  $field . " like ? ";
            				$params[] = '%' . $value . '%';
            				break;
            			}
            		case 'po.orderDate_from':
            			{
            				$where[] =  'po.orderDate >= ?';
            				$params[] = $value;
            				break;
            			}
            		case 'po.orderDate_to':
            			{
            				$where[] =  'po.orderDate <= ?';
            				$params[] = str_replace(' 00:00:00', ' 23:59:59', $value);
            				break;
            			}
            		case 'po.supplierIds':
            			{
            				if(count($value) > 0)
            				{
            					$where[] = 'po.supplierId IN ('.implode(", ", array_fill(0, count($value), "?")).')';
            					$params = array_merge($params, $value);
            				}
            				break;
            			}
            		case 'po.status':
            			{
            				if(count($value) > 0) {
	            				$where[] = 'po.status IN ('.implode(", ", array_fill(0, count($value), "?")).')';
	            				$params = array_merge($params, $value);
            				}
            				break;
            			}
            		case 'po.active':
            			{
            				if(trim($value) !== '')
            				{
            					$where[] = 'po.active = ?';
	            				$params[] = trim($value);
            				}
            				break;
            			}
					case 'rec_item.invoiceNo':
            			{
            				if(trim($value) !== '')
            				{
            					$where[] = 'id in (select purchaseOrderId from receivingItem rec_item where po.id = rec_item.purchaseOrderId and rec_item.active =1 and rec_item.invoiceNo like ?)';
            					$params[] = '%' . trim($value) . '%';
            				}
            				break;
            			}
            	}
            	$noSearch = false;
            }

            $objects = PurchaseOrder::getAllByCriteria(implode(' AND ', $where), $params, false, $pageNo, $pageSize, array('po.id' => 'desc'), $stats);
            $results['pageStats'] = $stats;
            $results['items'] = array();
            foreach($objects as $obj){
            	$PoId = $obj->getId();
            	$results['items'][] = array('totalProdcutCount' => $obj->getTotalProductCount(), 'item' => $obj->getJson());
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

    		$item = PurchaseOrder::get($id);

    		if(!$item instanceof PurchaseOrder)
    			throw new Exception();
    		$item->setActive(false)
    			->save();
    		$results['item'] = $item->getJson();
    	}
    	catch(Exception $ex)
    	{
    		$errors[] = $ex->getMessage() . $ex->getTraceAsString();
    	}
    	$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
    }
}
?>
