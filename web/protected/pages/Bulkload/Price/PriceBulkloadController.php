<?php
/**
 * This is the OrderController
 * 
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class PriceBulkloadController extends BPCPageAbstract
{
	public $orderPageSize = 10;
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$menuItem
	 */
	public $menuItem = 'bulkload';
	
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
		
		// Setup the dnd listeners.
		$js .= 'pageJs.dropShowDiv.dropDiv = "drop_file";';
		$js .= 'pageJs.dropShowDiv.showDiv = "show_file";';
		$js .= 'pageJs.dropShowDiv.resultDiv = "price_compare";';
		$js .= 'pageJs.csvSeperator = ",";';
		//$js .= 'pageJs.intializeFileReader();';
		$js .= 'pageJs.setCallbackId("getAllPricesForProduct", "' . $this->getAllPricesForProductBtn->getUniqueID() . '");';
		$js .= 'pageJs.initializeFileHandler();';
		return $js;
	}
	
	public function getAllPricesForProduct($sender, $param)
	{
		$result = $errors = $outputArray = $finalOutputArray = array();
		try
		{
			$sku = $minPrice = '';
			if(isset($param->CallbackParameter->sku) && trim($param->CallbackParameter->sku) !== '')
				$sku = trim($param->CallbackParameter->sku);
			if(isset($param->CallbackParameter->price) && trim($param->CallbackParameter->price) !== '')
				$price = trim($param->CallbackParameter->price);
			
			if($sku !== '')
			{	
				$productPriceArray = HTMLParser::getPriceListForProduct($sku);
				foreach($productPriceArray as $pp)
				{
					if(preg_match('/^\$/', $pp['price']))
						$currentPrice = substr($pp['price'], 1);
					
					if($minPrice === '' || $currentPrice < $minPrice)
						$minPrice = $currentPrice;
					
					$tmp = array();
					$tmp['price'] = $pp['price'];
					$tmp['priceURL'] = $pp['priceLink'];
					$companyURL = '';
					if(($companyDetails = trim($pp['companyDetails'])) !== '')
					{
						$cdArray = explode('|', $companyDetails);
						if(isset($cdArray[count($cdArray) - 2]))
							$companyURL = trim($cdArray[count($cdArray) - 2]);
						else
							$companyURL =  $companyDetails;
					}	
					$tmp['company'] = $pp['companyName'].'('.$companyURL.')';
					$outputArray[] = $tmp;
				}
			}
			
			$finalOutputArray['sku'] = $sku;
			$finalOutputArray['minPrice'] = $minPrice;
			$finalOutputArray['data'] = $outputArray;
			
			$result['items'] = $finalOutputArray;
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($result, $errors);
	}
}
?>