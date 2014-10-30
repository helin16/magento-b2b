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
		
		$js = parent::_getEndJs();
		$js .= 'pageJs';
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
            
            $stats = array();
            /*$supplierIds = (!isset($serachCriteria['po.supplierIds']) || is_null($serachCriteria['po.supplierIds'])) ? array() : $serachCriteria['po.supplierIds'];*/
            
            $where = array(1);
            $params = array();
            PurchaseOrder::getAll(false,$pageNo,$pageSize,array('po.id' => 'desc'), $stats);
            $objects = array();
            /*PurchaseOrder::getAllByCriteria(implode(', ', $where), $params, true, $pageNo, $pageSize, array('po.id' => 'desc'), $stats);*/
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
}
?>
