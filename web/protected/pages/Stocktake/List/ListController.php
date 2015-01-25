<?php
/**
 * This is the OrderController
 * 
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class ListController extends BPCPageAbstract
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
			$js .= ".setHTMLIDs('detailswrapper','search_panel','products_table','barcode_input')";
			$js .= ".setCallbackId('searchProduct', '" . $this->searchProductBtn->getUniqueID() . "')";
			$js .= ".setCallbackId('saveQuantities', '" . $this->saveQuantitiesBtn->getUniqueID() . "')";
			$js .= ".init();";
		return $js;
	}
	public function searchProduct($sender, $param)
	{
		$results = $errors = array();
		try
		{
			$items = array();
			$searchTxt = isset($param->CallbackParameter->searchTxt) ? trim($param->CallbackParameter->searchTxt) : '';
			
			$where = 'pro_pro_code.code = :searchExact or pro.name like :searchTxt OR sku like :searchTxt';
			$params = array('searchExact' => $searchTxt , 'searchTxt' => '%' . $searchTxt . '%');
				
			$searchTxtArray = StringUtilsAbstract::getAllPossibleCombo(StringUtilsAbstract::tokenize($searchTxt));
			if(count($searchTxtArray) > 1)
			{
				foreach($searchTxtArray as $index => $comboArray)
				{
					$key = 'combo' . $index;
					$where .= ' OR pro.name like :' . $key;
					$params[$key] = '%' . implode('%', $comboArray) . '%';
				}
			}
			Product::getQuery()->eagerLoad('Product.codes', 'left join');
			$products = Product::getAllByCriteria($where, $params, true, 1, DaoQuery::DEFAUTL_PAGE_SIZE, array('pro.sku' => 'asc'));
			
			foreach($products as $product)
			{
				if(!$product instanceof Product)
					throw new Exception('Invalid Product passed in!');
				$EANcodes = ProductCode::getAllByCriteria('pro_code.productId = :productId and pro_code.typeId = :typeId', array('productId'=> $product->getId(), 'typeId'=> ProductCodeType::ID_EAN), true, 1, 1);
				$EANcodes = count($EANcodes) > 0 ? $EANcodes[0]->getCode() : '';
				
				$UPCcodes = ProductCode::getAllByCriteria('pro_code.productId = :productId and pro_code.typeId = :typeId', array('productId'=> $product->getId(), 'typeId'=> ProductCodeType::ID_UPC), true, 1, 1);
				$UPCcodes = count($UPCcodes) > 0 ? $UPCcodes[0]->getCode() : '';
				
				$array = $product->getJson();
				$array['codes'] = array('EAN'=>$EANcodes, 'UPC'=>$UPCcodes);
				$items[] = $array;
			}
			$results['items'] = $items;
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
	/**
	 * saveOrder
	 *
	 * @param unknown $sender
	 * @param unknown $param
	 *
	 * @throws Exception
	 *
	 */
	public function saveQuantities($sender, $param)
	{
		$results = $errors = array();
		try
		{
			Dao::beginTransaction();
			$items = array();
			foreach ($param->CallbackParameter->products as $item)
			{
				if(!($product = Product::get(trim($item->id))) instanceof Product)
					throw new Exception('Invalid Product passed in!');
				if(($stockOnPO = trim($item->stockOnPO)) !== $product->getstockOnPO())
					$product->setStockOnPO($stockOnPO);
				if(($stockOnHand = trim($item->stockOnHand)) !== $product->getStockOnHand())
					$product->setStockOnHand($stockOnHand);
				if(($stockOnOrder = trim($item->stockOnOrder)) !== $product->getStockOnOrder())
					$product->setStockOnOrder($stockOnOrder);
				if(($stockInRMA = trim($item->stockInRMA)) !== $product->getStockInRMA())
					$product->setStockInRMA($stockInRMA);
				if(($stockInParts = trim($item->stockInParts)) !== $product->getStockInParts())
					$product->setStockInParts($stockInParts);
				if(($totalInPartsValue = trim($item->totalInPartsValue)) !== $product->getTotalInPartsValue())
					$product->setTotalInPartsValue($totalInPartsValue);
				if(($totalOnHandValue = trim($item->totalOnHandValue)) !== $product->getTotalOnHandValue())
					$product->setTotalOnHandValue($totalOnHandValue);
			}
			$product->save();
			
			$results['item'] = $product->getJson();
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
}
?>