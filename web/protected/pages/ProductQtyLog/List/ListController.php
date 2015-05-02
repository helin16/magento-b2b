<?php
/**
 * This is the listing page for manufacturer
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
	public $menuItem = 'product.qtylog';
	protected $_focusEntity = 'ProductQtyLog';
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
		$from = isset($this->Request['from']) ? $this->Request['from'] : '';
		$to = isset($this->Request['to']) ? $this->Request['to'] : '';
		$productId = isset($this->Request['productid']) ? $this->Request['productid'] : '';
		$js = parent::_getEndJs();
		$js .= "pageJs";
		$js .= "._bindSearchKey()";
		$js .= "._loadDataPicker()";
		$js .= ".setPreData(" . json_encode($from) . ", " . json_encode($to) . ", " . json_encode($productId) . ")";
		$js .= ";";
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
			if(isset($serachCriteria['pql.product']) && ($skuORid = trim($serachCriteria['pql.product'])) !== '')
			{
				ProductQtyLog::getQuery()->eagerLoad('ProductQtyLog.product', 'inner join', 'pql_pro');
				$where[] = '(pql_pro.sku = ? or pql_pro.id = ?)';
				$params[] = $skuORid;
				$params[] = $skuORid;
			}
			if(isset($serachCriteria['pro.id']) && ($productId = trim($serachCriteria['pro.id'])) !== '')
			{
				ProductQtyLog::getQuery()->eagerLoad('ProductQtyLog.product', 'inner join', 'pql_pro');
				$where[] = '(pql_pro.id = ? )';
				$params[] = $productId;
			}
			if(isset($serachCriteria['pql.createdDate_from']) && ($from = trim($serachCriteria['pql.createdDate_from'])) !== '')
			{
				$where[] = 'pql.created >= ?';
				$params[] = $from;
			}
			if(isset($serachCriteria['pql.createdDate_to']) && ($to = trim($serachCriteria['pql.createdDate_to'])) !== '')
			{
				$where[] = 'pql.created <= ?';
				$params[] = str_replace(' 00:00:00', ' 23:59:59', $to);
			}
			$stats = array();
			$objects = $class::getAllByCriteria(implode(' AND ', $where), $params, false, $pageNo, $pageSize, array('pql.id' => 'desc'), $stats);
			$results['pageStats'] = $stats;
			$results['items'] = array();
			foreach($objects as $obj)
			{
				$order = ($obj->getEntity() instanceof OrderItem ? $obj->getEntity()->getOrder() : ($obj->getEntity() instanceof Order ? $obj->getEntity() : null));
				$purchaseOrder = ($obj->getEntity() instanceof PurchaseOrderItem ? $obj->getEntity()->getPurchaseOrder() : ($obj->getEntity() instanceof PurchaseOrder ? $obj->getEntity() : null));
				$extra = array(
					'product'=> $obj->getproduct()->getJson()
					,'order' => $order instanceof Order ? $order->getJson() : null
					,'purchaseOrder' => $purchaseOrder instanceof PurchaseOrder ? $purchaseOrder->getJson() : null
				);
				$results['items'][] = $obj->getJson($extra);
			}
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
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
		$results = $errors = array();
		try
		{
			$class = trim($this->_focusEntity);
			if(!isset($param->CallbackParameter->item))
				throw new Exception("System Error: no item information passed in!");
			$item = (isset($param->CallbackParameter->item->id) && ($item = $class::get($param->CallbackParameter->item->id)) instanceof $class) ? $item : null;
			$name = trim($param->CallbackParameter->item->name);
			$description = trim($param->CallbackParameter->item->description);
			if($item instanceof $class)
			{
				$item->setName($name)
					->setDescription($description)
					->save();
			}
			else
			{
				$item = $class::create($name, $description, false);
			}
			$results['item'] = $item->getJson();
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
}
?>
