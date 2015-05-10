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
	public $menuItem = 'report.productAgeing';
	protected $_focusEntity = 'ProductAgeingLog';
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
// 		$js .= "._loadDataPicker()";
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
            		case 'pro.id': 
					{
						$where[] = 'pal.productId = ?';
            			$params[] = $value;
						break;
					}
					case 'po.id': 
					{
						ProductAgeingLog::getQuery()->eagerLoad('ProductAgeingLog.purchaseOrderItem', 'inner join', 'pal_po');
						$where[] = '(pal_po.id = ? )';
						$params[] = $value;
						break;
					}
					case 'pal.lastPurchaseDate_from':
					{
						$where[] = 'pal.lastPurchaseTime >= ?';
						$params[] = $value;
						break;
					}
					case 'pal.lastPurchaseDate_to':
					{
						$where[] = 'pal.lastPurchaseTime <= ?';
						$params[] = str_replace(' 00:00:00', ' 23:59:59', $value);
						break;
					}
            	}
            }

            $stats = array();

            $objects = $class::getAllByCriteria(implode(' AND ', $where), $params, false, $pageNo, $pageSize, array('pal.lastPurchaseTime' => 'desc'), $stats);

            $results['pageStats'] = $stats;
            $results['items'] = array();
            foreach($objects as $obj)
                $results['items'][] = $obj->getJson(array('NOW'=> UDate::now()->__toString()));
        }
        catch(Exception $ex)
        {
            $errors[] = $ex->getMessage() . $ex->getTraceAsString();
        }
        $param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
}
?>
