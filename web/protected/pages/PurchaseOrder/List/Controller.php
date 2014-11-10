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
		if(!AccessControl::canAccessPurcahseOrdersPage(Core::getRole()))
			die('You do NOT have access to this page');
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
		$js = parent::_getEndJs();
		$js .= 'pageJs';
		$js .= ".setCallbackId('deactivateItems', '" . $this->deactivateItemBtn->getUniqueID() . "')";
		$js .= "._loadSuppliers(" . json_encode($suppliersArray) . ")";
		$js .= "._loadChosen()";
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
//             if(!isset($param->CallbackParameter->searchCriteria) || count($serachCriteria = json_decode(json_encode($param->CallbackParameter->searchCriteria), true)) === 0)
//                 throw new Exception('System Error: search criteria not provided!');
            $pageNo = 1;
            $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE;
            
            if(isset($param->CallbackParameter->pagination))
            {
                $pageNo = $param->CallbackParameter->pagination->pageNo;
                $pageSize = $param->CallbackParameter->pagination->pageSize * 3;
            }
            $serachCriteria = isset($param->CallbackParameter->searchCriteria) ? json_decode(json_encode($param->CallbackParameter->searchCriteria), true) : array();
            $stats = array();
            $where = array(1);
            $params = array();
            if(isset($serachCriteria['po.purchaseOrderNo']) && $serachCriteria['po.purchaseOrderNo'] !== '')
            {
            	$where[] = 'po.purchaseOrderNo = ?';
            	$params[] = $serachCriteria['po.purchaseOrderNo'];
            }
            if(isset($serachCriteria['po.supplierRefNo']) && $serachCriteria['po.supplierRefNo'] !== '')
            {
            	$where[] = 'po.supplierRefNo = ?';
            	$params[] = $serachCriteria['po.supplierRefNo'];
            }
            $objects = PurchaseOrder::getAllByCriteria(implode(' AND ', $where), $params, false, $pageNo, $pageSize, array('po.id' => 'desc'), $stats);
            $results['pageStats'] = $stats;
            $results['items'] = array();
            foreach($objects as $obj)
                $results['items'][] = $obj->getJson();
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
