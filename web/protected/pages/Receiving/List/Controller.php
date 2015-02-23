<?php
/**
 * This is the serial numbers Controller
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
	public $menuItem = 'serialno';
	protected $_focusEntity = 'ReceivingItem';
	/**
	 * (non-PHPdoc)
	 * @see CRUDPageAbstract::_getEndJs()
	 */
	protected function _getEndJs()
	{
		$js = parent::_getEndJs();
		if(isset($_REQUEST['productid'])) {
			if(!($product = Product::get(trim($_REQUEST['productid']))) instanceof Product)
				die('Invalid Product Provided');
			$js .= "$('searchBtn').up('.panel').down('.panel-body').insert({'bottom': new Element('input', {'type': 'hidden', 'search_field': 'productid', 'value': '" . $product->getId() . "'}) });";
			$js .= "$('searchBtn').click();";
		}
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
			$serachCriteria = isset($param->CallbackParameter->searchCriteria) ? json_decode(json_encode($param->CallbackParameter->searchCriteria), true) : array();
			$pageNo = 1;
			$pageSize = DaoQuery::DEFAUTL_PAGE_SIZE;
			if(isset($param->CallbackParameter->pagination)) {
				$pageNo = $param->CallbackParameter->pagination->pageNo;
				$pageSize = $param->CallbackParameter->pagination->pageSize;
			}
			
			$where = $params = $stats = array();
			if(isset($serachCriteria['serialno']) && ($serialno = trim($serachCriteria['serialno'])) !== '') {
				$where[] = 'serialNo like ?';
				$params[] = trim($serialno);
			}
			if(isset($serachCriteria['productid']) && ($productid = trim($serachCriteria['productid'])) !== '') {
				$where[] = 'productId = ?';
				$params[] = trim($productid);
			}
			
			if(count($where) > 0)
				$objects = ReceivingItem::getAllByCriteria(implode(' AND ', $where), $params, true, $pageNo, $pageSize, array('rec_item.id' => 'desc'), $stats);
			$results['pageStats'] = $stats;
			$results['items'] = array();
			foreach($objects as $obj)
				$results['items'][] = $obj->getJson();
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
}
?>