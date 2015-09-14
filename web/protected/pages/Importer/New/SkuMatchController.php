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
		$importDataTypes = array('myob_ean'=> 'MYOB EAN', 'myob_upc'=> 'MYOB UPC', 'stockAdjustment' => 'Stock Adjustment', 'accounting' => 'Accounting Code for Products', 'accountingCode' => 'Accounting Code for Categories');

		$js = parent::_getEndJs();
		$js .= 'pageJs';
		$js .= ".setHTMLID('importerDiv', 'importer_div')";
		$js .= ".setHTMLID('importDataTypesDropdownId', 'import_type_dropdown')";
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
					$result['item'] = $item->getJson();
					break;
				case 'myob_upc':
					$index = $param->CallbackParameter->index;
					if(!isset($param->CallbackParameter->sku) || ($sku = trim($param->CallbackParameter->sku)) === '' || !($product = Product::getBySku($sku)) instanceof Product)
						throw new Exception('Invalid sku passed in! (line ' . $index .')');
					if(!isset($param->CallbackParameter->itemNo) || ($itemNo = trim($param->CallbackParameter->itemNo)) === '')
						throw new Exception('Invalid itemNo passed in! (line ' . $index .')');
					$result['path'] = 'product';
					$productType = ProductCodeType::get(ProductCodeType::ID_UPC);
					$item = $this->updateProductCode($product, $itemNo, $productType);
					$result['item'] = $item->getJson();
					break;
				case 'stockAdjustment':
					$index = $param->CallbackParameter->index;
					if(!isset($param->CallbackParameter->sku) || ($sku = trim($param->CallbackParameter->sku)) === '' || !($product = Product::getBySku($sku)) instanceof Product)
						throw new Exception('Invalid sku passed in! (line ' . $index .')');
					$result['path'] = 'product';
					$item = $this->updateStocktack($product
							, trim($param->CallbackParameter->stockOnPO), trim($param->CallbackParameter->stockOnHand), trim($param->CallbackParameter->stockInRMA), trim($param->CallbackParameter->stockInParts)
							, trim($param->CallbackParameter->totalInPartsValue), trim($param->CallbackParameter->totalOnHandValue), $param->CallbackParameter->active, trim($param->CallbackParameter->comment));

					$result['item'] = $item->getJson();
					break;
				case 'accounting':
					$index = $param->CallbackParameter->index;
					if(!isset($param->CallbackParameter->sku) || ($sku = trim($param->CallbackParameter->sku)) === '')
						throw new Exception('Invalid sku passed in! (line ' . $index .')');
					if(!($product = Product::getBySku($sku)) instanceof Product)
						$product = Product::create($sku, $sku);
					$result['path'] = 'product';
					$item = $this->updateAccountingInfo($product
							, trim($param->CallbackParameter->assetAccNo), trim($param->CallbackParameter->costAccNo), trim($param->CallbackParameter->revenueAccNo));

					$result['item'] = $item->getJson();
					break;
				case 'accountingCode':
					$index = $param->CallbackParameter->index;
					if(!isset($param->CallbackParameter->description) || ($description = trim($param->CallbackParameter->description)) === '')
						throw new Exception('Invalid description passed in! (line ' . $index .')');
					if(!isset($param->CallbackParameter->code) || ($code = trim($param->CallbackParameter->code)) === '' || !is_numeric($code))
						throw new Exception('Invalid Code passed in! (line ' . $index .')');
					$result['path'] = '';
					$item = $this->updateAccountingCode($description, $code);

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
	private function updateAccountingCode($description, $code)
	{
		try
		{
			Dao::beginTransaction();

			$typeId = $this->leftMostNum($code);

			if(!empty($description) && !empty($code) && !empty($typeId))
				$accountingCode = AccountingCode::create($typeId, $code, $description);

			Dao::commitTransaction();

			return $accountingCode;
		}
		catch(Exception $e) {
			Dao::rollbackTransaction();
			echo $e;
			exit;
		}
	}
	/**
	 * update accounting info for xero
	 *
	 * @param Product $product
	 * @param number $assetAccNo
	 * @param number $costAccNo
	 * @param number $revenueAccNo
	 *
	 * @return Product
	 */
	private function updateAccountingInfo(Product $product, $assetAccNo = 0, $costAccNo = 0, $revenueAccNo = 0)
	{
		try
		{
			Dao::beginTransaction();
			if(!empty($assetAccNo))
				$product->addLog('Product (ID=' . $product->getId() . ') now assetAccNo = ' . $assetAccNo, Log::TYPE_SYSTEM)
				->setAssetAccNo($assetAccNo);
			if(!empty($costAccNo))
				$product->addLog('Product (ID=' . $product->getId() . ') now costAccNo = ' . $costAccNo, Log::TYPE_SYSTEM)
				->setCostAccNo($costAccNo);
			if(!empty($revenueAccNo))
				$product->addLog('Product (ID=' . $product->getId() . ') now revenueAccNo = ' . $revenueAccNo, Log::TYPE_SYSTEM)
				->setRevenueAccNo($revenueAccNo);
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
	private function updateStocktack(Product $product, $stockOnPO = 0, $stockOnHand = 0, $stockInRMA = 0, $stockInParts = 0, $totalInPartsValue = 0, $totalOnHandValue = 0, $active = true, $comment = '')
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

			$active = ($active === 0 || $active === '0' || $active === false || $active === 'false' || $active === 'no') ? false : true;
			$product->addLog('Product (ID=' . $product->getId() . ') now active = ' . $active, Log::TYPE_SYSTEM)
				->setActive($active);

			$product->snapshotQty(null, ProductQtyLog::TYPE_STOCK_ADJ, empty($comment) ? 'Loaded via importer' : $comment)->save();

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
	private function leftMostNum($num) {
		return intval(floor($num/pow(10,(floor((log10($num)))))));
	}
	private function checkContainNumber($string)
	{
		return preg_match('/[0-9]+/', $string);
	}
}
?>