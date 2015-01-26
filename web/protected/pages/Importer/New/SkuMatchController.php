<?php
/**
 * This is the PriceMatchController
 * 
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class SkuMatchController extends BPCPageAbstract
{
	public $PageSize = 10;
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$menuItem
	 */
	public $menuItem = 'skuMatch';
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::onLoad()
	 */
	public function onLoad($param)
	{
		parent::onLoad($param);
		if(!AccessControl::canAccessPriceMatchPage(Core::getRole()))
			die(BPCPageAbstract::show404Page('Access Denied', 'You do NOT have the access to this page!'));
	}
	/**
	 * Getting The end javascript
	 *
	 * @return string
	 */
	protected function _getEndJs()
	{
		$importDataTypes = array('myob_ean'=> 'MYOB EAN', 'myob_upc'=> 'MYOB UPC', 'stocktake' => 'Stocktack');
		
		$js = parent::_getEndJs();
		$js .= 'pageJs';
		$js .= ".setHTMLIDs('importer_div', 'import_type_dropdown')";
		$js .= '.setCallbackId("getAllCodeForProduct", "' . $this->getAllCodeForProductBtn->getUniqueID() . '")';
		$js .= '.load(' . json_encode($importDataTypes) . ');';
		return $js;
	}
	public function getAllCodeForProduct($sender, $param)
	{
		$result = $errors = $item = array();
		try
		{
			if(!isset($param->CallbackParameter->importDataTypes) || ($type = trim($param->CallbackParameter->importDataTypes)) === '' || ($type = trim($param->CallbackParameter->importDataTypes)) === 'Select a Import Type')
				throw new Exception('Invalid upload type passed in!');

			switch ($type)
			{
				case 'myob_ean':
					$index = $param->CallbackParameter->index;
					if(!isset($param->CallbackParameter->sku) || ($sku = trim($param->CallbackParameter->sku)) === '' || !($product = Product::getBySku($sku)) instanceof Product)
						throw new Exception('Invalid sku passed in! (line ' . $index .')');
					if(!isset($param->CallbackParameter->itemNo) || ($itemNo = trim($param->CallbackParameter->itemNo)) === '')
						throw new Exception('Invalid itemNo passed in! (line ' . $index .')');
					$result['path'] = 'product';
					$productType = ProductCodeType::get(ProductCodeType::ID_EAN);
					$item = $this->updateProductCode($product, $itemNo, $productType);
// 					$eanCode = ProductCode::getAllByCriteria('productId = ? AND typeId = ?', array($item->getId(), $productType->getId()));
// 					var_dump($item->getJson(array('ean_code'=> $eanCode[0]->getCode())));
// 					$result['item'] = $item->getJson(array('ean_code'=> $eanCode[0]->getCode()));
					$result['item'] = $item->getJson();
					break;
				case 'myob_upc':
					$index = $param->CallbackParameter->index;
					if(!isset($param->CallbackParameter->sku) || ($name = trim($param->CallbackParameter->sku)) === '')
						throw new Exception('Invalid sku passed in! (line ' . $index .')');
					if(!isset($param->CallbackParameter->itemNo) || ($code = trim($param->CallbackParameter->itemNo)) === '')
						throw new Exception('Invalid itemNo passed in! (line ' . $index .')');
					$result['path'] = 'product';
					$item = $this->updateProductCode($product, $itemNo, ProductCodeType::get(ProductCodeType::ID_UPC));

					$result['item'] = $item->getJson();
					break;
				case 'stocktake':
					$index = $param->CallbackParameter->index;
					if(!isset($param->CallbackParameter->sku) || ($sku = trim($param->CallbackParameter->sku)) === '' || !($product = Product::getBySku($sku)) instanceof Product)
						throw new Exception('Invalid sku passed in! (line ' . $index .')');
					$result['path'] = 'product';
					$item = $this->updateStocktack($product
							, trim($param->CallbackParameter->stockOnPO), trim($param->CallbackParameter->stockOnHand), trim($param->CallbackParameter->stockInRMA), trim($param->CallbackParameter->stockInParts)
							, trim($param->CallbackParameter->totalInPartsValue), trim($param->CallbackParameter->totalOnHandValue));
					
					$result['item'] = $item->getJson();
					break;
				default:
					throw new Exception('Invalid upload type passed in!');
			}
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($result, $errors);
	}
	/**
	 * update stock tack
	 * 
	 * @param Product $product
	 * @param number $stockOnPO
	 * @param number $stockOnHand
	 * @param number $stockInRMA
	 * @param number $stockInParts
	 * @param number $totalInPartsValue
	 * @param number $totalOnHandValue
	 * 
	 * @return Product
	 */
	private function updateStocktack(Product $product, $stockOnPO = 0, $stockOnHand = 0, $stockInRMA = 0, $stockInParts = 0, $totalInPartsValue = 0, $totalOnHandValue = 0)
	{
		try
		{
			Dao::beginTransaction();
			if(!empty($stockOnPO))
				$product->addLog('Product (ID=' . $product->getId() . ') now stockOnPO = ' . $stockOnPO, Log::TYPE_SYSTEM)
					->setStockOnPO($stockOnPO);
			if(!empty($stockOnHand))
				$product->addLog('Product (ID=' . $product->getId() . ') now stockOnHand = ' . $stockOnHand, Log::TYPE_SYSTEM)
					->setStockOnHand($stockOnHand);
			if(!empty($stockInRMA))
				$product->addLog('Product (ID=' . $product->getId() . ') now stockInRMA = ' . $stockInRMA, Log::TYPE_SYSTEM)
					->setStockInRMA($stockInRMA);
			if(!empty($stockInParts))
				$product->addLog('Product (ID=' . $product->getId() . ') now stockInParts = ' . $stockInParts, Log::TYPE_SYSTEM)
					->setStockInParts($stockInParts);
			if(!empty($totalInPartsValue))
				$product->addLog('Product (ID=' . $product->getId() . ') now totalInPartsValue = ' . $totalInPartsValue, Log::TYPE_SYSTEM)
					->setTotalInPartsValue($totalInPartsValue);
			if(!empty($totalOnHandValue))
				$product->addLog('Product (ID=' . $product->getId() . ') now totalOnHandValue = ' . $totalOnHandValue, Log::TYPE_SYSTEM)
					->setTotalOnHandValue($totalOnHandValue);
			$product->save();
			
			Dao::commitTransaction();
			
			return $product;
		}
		catch(Exception $e) {
			Dao::rollbackTransaction();
			echo $e;
			exit;
		}
	}
	/**
	 * Update product code
	 * 
	 * @param Product $product
	 * @param unknown $myobCode
	 * @param ProductCodeType $productCodeType
	 * @param string $assetAccNo
	 * @param string $revenueAccNo
	 * @param string $costAccNo
	 * 
	 * @return Product
	 */
	private function updateProductCode(Product $product, $myobCode, ProductCodeType $productCodeType, $assetAccNo = '', $revenueAccNo = '', $costAccNo = '')
	{
		try
		{
			Dao::beginTransaction();
			
			// only take the myobCode (myob item#) after the first dash
			$position = strpos($myobCode, '-');
			if($position)
			{
				$myobCodeAfter = substr($myobCode, $position+1);	// get everything after first dash
				$myobCodeAfter = str_replace(' ', '', $myobCodeAfter); // remove all whitespace
			}
			else 
			{
				$myobCodeAfter = $myobCode;
			}
			
			$result = array();
			$result['product'] = $product->getJson();
			$result['code']= $myobCodeAfter;
			$result['MYOBcode'] = $myobCode;
			$result['assetAccNo'] = $assetAccNo;
			$result['revenueAccNo'] = $revenueAccNo;
			$result['costAccNo'] = $costAccNo;
			
			// if such code type for such product exist, update it to the new one
			if(!empty($myobCode))
			{
				if(count($productCodes = ProductCode::getAllByCriteria('pro_code.typeId = ? and pro_code.productId = ?', array($productCodeType->getId(), $product->getId()), true,1 ,1 ) ) > 0 )
				{
					$productCodes[0]->setCode($myobCodeAfter)->save();
					$result['codeNew'] = false;
				}
				else // create a new one
				{
					$newCode = ProductCode::create($product, $productCodeType, trim($myobCodeAfter));
					$result['codeNew'] = true;
				}
			}
    
			// do the same for MYOB code (NOTE: have to have MYOB code in code type !!!)
			if(!empty($myobCode))
			{
				if(count($productCodes = ProductCode::getAllByCriteria('pro_code.typeId = ? and pro_code.productId = ?', array(ProductCodeType::ID_MYOB, $product->getId()), true,1 ,1 ) ) > 0 )
				{
					$productCodes[0]->setCode($myobCode)->save();
					$result['MYOBcodeNew'] = false;
				}
				else
				{
					ProductCode::create($product, ProductCodeType::get(ProductCodeType::ID_MYOB), trim($myobCode));
					$result['MYOBcodeNew'] = true;
				}
			}
    
			if(!empty($assetAccNo))
				$product->setAssetAccNo($assetAccNo)->save();
			if(!empty($revenueAccNo))
				$product->setRevenueAccNo($revenueAccNo)->save();
			if(!empty($costAccNo))
				$product->setCostAccNo($costAccNo)->save();
			 
			Dao::commitTransaction();
			
			return $product;
		}
		catch(Exception $e) {
			Dao::rollbackTransaction();
			echo $e;
			exit;
		}
	}
	private function checkContainNumber($string)
	{
		return preg_match('/[0-9]+/', $string);
	}
}
?>