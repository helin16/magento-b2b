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
	public $menuItem = 'customer';
	protected $_focusEntity = 'Customer';
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
		$js .= "pageJs.getResults(true, " . $this->pageSize . ");";
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
            
            $stats = array();
            $id = '2';
            //var_dump(Customer::get($id));die;
            //var_dump(Customer::getAll(true,$pageNo,$pageSize,array('cust.name' => 'asc'),$stats));die;
            //var_dump(array('cust.name' => 'asc'));die;
            
            //$objects = Product::getProducts(trim($serachCriteria['pro.sku']), trim($serachCriteria['pro.name']), $supplierIds, $manufacturerIds, $categoryIds, $productStatusIds, trim($serachCriteria['pro.active']), $pageNo, $pageSize, array('pro.name' => 'asc'), $stats);
            $objects = Customer::getAll(true,$pageNo,$pageSize,array('cust.name' => 'asc'),$stats);
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
	public function saveItem($sender, $param)
	{

	}
}
?>
