<?php
/**
 * This is the PriceMatchController
 * 
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class PriceMatchController extends BPCPageAbstract
{
	public $orderPageSize = 10;
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$menuItem
	 */
	public $menuItem = 'priceMatch';
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
		$companyArray = PriceMatcher::getAllCompaniesForPriceMatching();
		if(count(array_keys($companyArray)) === 0)
			die('No Price Match Company Set up. Please set up the Price Matching companies before using this page!!!!!');
		
		$js = parent::_getEndJs();
		// Setup the dnd listeners.
		$js .= 'pageJs.setCallbackId("getAllPricesForProduct", "' . $this->getAllPricesForProductBtn->getUniqueID() . '")';
		$js .= '.load("price_match_div", ' . json_encode($companyArray) . ');';
		return $js;
	}
	
	public function getAllPricesForProduct($sender, $param)
	{
		$result = $errors = $outputArray = $finalOutputArray = array();
		try
		{
			if(!isset($param->CallbackParameter->sku) || ($sku = trim($param->CallbackParameter->sku)) === '')
				throw new Exception('No SKU to search on!');
			if(!isset($param->CallbackParameter->companyAliases) || count($companyAliases = json_decode(json_encode($param->CallbackParameter->companyAliases), true)) === 0)
				throw new Exception('No companyAliases to search on!');
			
			if(!isset($param->CallbackParameter->price) || ($myPrice = str_replace(' ', '', $param->CallbackParameter->price)) === '')
				$myPrice = 0;
			else if(!is_numeric($myPrice = str_replace('$', '', str_replace(',', '', $myPrice) )))
				throw new Exception('No provided my price is NOT a number(=' . $myPrice . '!');
			
			$result['item'] = PriceMatcher::getPrices($companyAliases, $sku, $myPrice);
			
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($result, $errors);
	}
}
?>