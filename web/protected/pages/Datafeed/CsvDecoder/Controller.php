<?php
/**
 * This is the PriceMatchController
 *
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class Controller extends BPCPageAbstract
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
		$predata = array('suppliers'=> array_map(create_function('$a', 'return $a->getJson();'), Supplier::getAll()));

		$js = parent::_getEndJs();
		$js .= 'pageJs';
		$js .= ".setHTMLID('importerDiv', 'importer_div')";
		$js .= ".setHTMLID('importDataTypesDropdownId', 'import_type_dropdown')";
		$js .= '.setCallbackId("processDatafeed", "' . $this->processDatafeedBtn->getUniqueID() . '")';
		$js .= '.load(' . json_encode($predata) . ');';
		return $js;
	}
	public function processDatafeed($sender, $param)
	{
		$result = $errors = $item = array();
		try
		{
			if(!isset($param->CallbackParameter->config->supplier) || !($supplier = Supplier::get(trim($param->CallbackParameter->config->supplier))) instanceof Supplier )
				throw new Exception('Invalid Supplier passed in!');
			$result = $param->CallbackParameter->data;

			switch ($supplier->getId())
			{
				case '2':
					break;
// 				default:
// 					throw new Exception('Invalid upload type passed in!');
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