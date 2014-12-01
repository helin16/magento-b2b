<?php
/**
 * This is the OrderController
 * 
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class ReceivingController extends BPCPageAbstract
{
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$menuItem
	 */
	public $menuItem = 'purchaseorders.receiving';
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::onLoad()
	 */
	public function onLoad($param)
	{
		parent::onLoad($param);
	}
	/**
	 * Getting The end javascript
	 *
	 * @return string
	 */
	protected function _getEndJs()
	{
		$js = parent::_getEndJs();
		$js .= "pageJs";
			$js .= ".setHTMLIDs('detailswrapper','search_panel','payment_panel','supplier_info_panel','order_change_details_table','barcode_input')";
			$js .= ".setCallbackId('searchPO', '" . $this->searchPOBtn->getUniqueID() . "')";
			$js .= ".setCallbackId('searchProduct', '" . $this->searchProductBtn->getUniqueID() . "')";
			$js .= ".init();";
		return $js;
	}
	/**
	 * Searching PO
	 *
	 * @param unknown $sender
	 * @param unknown $param
	 *
	 * @throws Exception
	 *
	 */
	public function searchPO($sender, $param)
	{
		$results = $errors = array();
		try
		{
			$items = array();
			$searchTxt = isset($param->CallbackParameter->searchTxt) ? trim($param->CallbackParameter->searchTxt) : '';
			if($searchTxt === '')
				$results['items'] = '';
			else {
				foreach(PurchaseOrder::getAllByCriteria('(purchaseOrderNo like :searchTxt || supplierRefNo like :searchTxt) && (status = :statusReceiving || status = :statusOrdered)', array('searchTxt' => $searchTxt . '%', 'statusReceiving' => PurchaseOrder::STATUS_RECEIVING, 'statusOrdered' => PurchaseOrder::STATUS_ORDERED), true, null, DaoQuery::DEFAUTL_PAGE_SIZE, array('id'=> 'desc')) as $po)
				{
					$array = $po->getJson();
					$array['totalProdcutCount'] = $po->gettotalProdcutCount();
					$items[] = $array;
				}
				$results['items'] = $items;
			}
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
	/**
	 * Searching searchProduct
	 *
	 * @param unknown $sender
	 * @param unknown $param
	 *
	 * @throws Exception
	 *
	 */
	public function searchProduct($sender, $param)
	{
		$results = $errors = array();
		try
		{
			$items = array();
			$searchTxt = isset($param->CallbackParameter->searchTxt) ? trim($param->CallbackParameter->searchTxt) : '';
			$productIdsFromBarcode = array_map(create_function('$a', 'return $a->getProduct()->getId();'), ProductCode::getAllByCriteria('code = ?', array($searchTxt)));
			$where = (count($productIdsFromBarcode) === 0 ? '' : 'id in (' . implode(',', $productIdsFromBarcode) . ')');
			foreach(Product::getAllByCriteria($where, array(), true, 1, DaoQuery::DEFAUTL_PAGE_SIZE * 3) as $product)
			{
				$items[] = $product->getJson();
			}
			$results['items'] = $items;
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
}
?>