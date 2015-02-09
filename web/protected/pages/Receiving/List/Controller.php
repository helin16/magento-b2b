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
// 		$js .= "pageJs";
// 		$js .= ".getResults(true, " . $this->pageSize . ");";
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
			$objects = array();
			$stats = array();
			if(isset($serachCriteria['serialno']) && ($serialno = trim($serachCriteria['serialno'])) !== '') {
				$class = trim($this->_focusEntity);
				$pageNo = 1;
				$pageSize = DaoQuery::DEFAUTL_PAGE_SIZE;
				if(isset($param->CallbackParameter->pagination)) {
					$pageNo = $param->CallbackParameter->pagination->pageNo;
					$pageSize = $param->CallbackParameter->pagination->pageSize;
				}
				$objects = $class::getAllByCriteria('serialNo like ?', array(trim($serialno)), true, $pageNo, $pageSize, array('rec_item.id' => 'desc'), $stats);
			}
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