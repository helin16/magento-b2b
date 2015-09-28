<?php
/**
 * This is the listing page for customer
 *
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class ReportController extends BPCPageAbstract
{
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$menuItem
	 */
	public $menuItem = 'report.runrate';
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
		$js .= "pageJs.init()";
		$js .= ".setHTMLID('resultDiv', 'result-div')";
		$js .= ".setCallbackId('deactivateItems', '" . $this->deactivateItemBtn->getUniqueID() . "')";
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
            $where = array(1);
            $params = array();
            foreach($serachCriteria as $field => $value)
            {
            	if((is_array($value) && count($value) === 0) || (is_string($value) && ($value = trim($value)) === ''))
            		continue;

            	$query = $class::getQuery();
            	switch ($field)
            	{
            		case 'pro.ids':
					{
						$value = explode(',', $value);
						$where[] = 'pal.productId in ('.implode(", ", array_fill(0, count($value), "?")).')';
            			$params = array_merge($params, $value);
						break;
					}
					case 'pro.categories':
					{
						$value = array_map(create_function('$a', 'return trim($a);'), explode(',', $value));
						$query->eagerLoad("ProductAgeingLog.product", 'inner join', 'pro', 'pro.id = pal.productId and pro.active = 1')
							->eagerLoad('Product.categories', 'inner join', 'pro_cate', 'pro_cate.active = 1 and pro.id = pro_cate.productId and pro_cate.categoryId in (' . implode(", ", array_fill(0, count($value), "?")) .')');
						$params = array_merge($value, $params);
						break;
					}
					case 'po.id':
					{
						ProductAgeingLog::getQuery()->eagerLoad('ProductAgeingLog.purchaseOrderItem', 'inner join', 'pal_po');
						$where[] = '(pal_po.id = ? )';
						$params[] = $value;
						break;
					}
					case 'aged-days':
					{
						$where[] = 'date_add(pal.lastPurchaseTime, INTERVAL ' . intval($value) . ' DAY) < NOW()';
						break;
					}

            	}
            }

            $stats = array();
            $objects = $class::getAllByCriteria(implode(' AND ', $where), $params, false, $pageNo, $pageSize, array('pal.lastPurchaseTime' => 'asc'), $stats);

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
