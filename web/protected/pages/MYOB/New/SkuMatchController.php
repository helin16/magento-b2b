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
		$js = parent::_getEndJs();
		// Setup the dnd listeners.
		$js .= 'pageJs';
		$js .= ".setHTMLIDs('sku_match_div')";
		$js .= '.setCallbackId("getAllCodeForProduct", "' . $this->getAllCodeForProductBtn->getUniqueID() . '")';
		$js .= '.load();';
		return $js;
	}
	

	public function getAllCodeForProduct($sender, $param)
	{
		$result = $errors = $items = array();
		try
		{
// 			var_dump($param->CallbackParameter);
			
			$index = $param->CallbackParameter->index;
			$sku = trim($param->CallbackParameter->sku);
			$code = trim($param->CallbackParameter->code);
// 			$productCodeTypeId = trim($param->CallbackParameter->productCodeType->id);
			
			if(empty($sku))
				throw new Exception('Invalid SKU passed in! Line: ' . $index);
			if(empty($code))
				throw new Exception('Invalid MYOB code passed in! Line: ' . $index);
// 			if(empty($productCodeTypeId) || !($productCodeType = ProductCodeType::get($productCodeTypeId)) instanceof ProductCodeType)
// 				throw new Exception('Invalid ProductCodeType passed in! Line: ' . $index);
			$productCodeType = ProductCodeType::get(ProductCodeType::ID_EAN);
			
			
			//assume a non-title row contains at lease a number
			if(($this->checkContainNumber($sku) || $this->checkContainNumber($code))) // is not a title row
			{
				$product = Product::getBySku($sku);
				
				if(!($product instanceof Product))
					throw new Exception('Invalid SKU passed in! Line: ' . $index);
				
				$items = $this->updateProductCode($product, $code, $productCodeType);
			}
			
			$result['item'] = $items;
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($result, $errors);
	}
	
	private function updateProductCode($product, $myobCode, $productCodeType)
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
			
			// if such code type for such product exist, update it to the new one
			if(count($productCodes = ProductCode::getAllByCriteria('pro_code.typeId = ? and pro_code.productId = ?', array($productCodeType->getId(), $product->getId()), true,1 ,1 ) ) > 0 )
			{
				$productCodes[0]->setCode($myobCodeAfter)->save();
				$result['codeNew'] = false;
			}
			else // create a new one
			{
				$newCode = ProductCode::create($product, $productCodeType, trim($myobCode));
				$result['codeNew'] = true;
			}
    
			// do the same for MYOB code (NOTE: have to have MYOB code in code type !!!)
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
			 
			Dao::commitTransaction();
			
			return $result;
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