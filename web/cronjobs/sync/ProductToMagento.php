<?php

require_once dirname(__FILE__) . '/../../bootstrap.php';

abstract class ProductToMagento
{
    const TAB = '    ';
    const OUTPUT_FILE_NAME = 'productUpdate.csv';
    /**
     * The log file
     *
     * @var string
     */
    private static $_logFile = '';
    /**
     * The output of the file path
     *
     * @var string
     */
    private static $_outputFilePath = '';
    /**
     * The run time cache for the settings..etc.
     *
     * @var array
     */
    private static $_cache = array();
    /**
     * The runner
     *
     * @param string $preFix
     * @param string $debug
     */
    public static function run($outputFilePath = '/tmp/', $preFix = '', $debug = false)
    {
        $start = self::_log('## START ##############################', __CLASS__ . '::' . __FUNCTION__,  $preFix);

		self::$_outputFilePath = trim ($outputFilePath);
		self::_log('GEN CSV TO: ' . self::$_outputFilePath, '',  $preFix. self::TAB);
    	Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));

    	$lastUpdatedInDB = '';
    	$products = self::_getData($lastUpdatedInDB, $preFix . self::TAB, $debug);
		self::_genCSV($products, $preFix . self::TAB, $debug);

		self::_log('After the looping we have got last updated time from DB: "' . trim($lastUpdatedInDB) . '".', '',  $preFix);
		self::_setSettings('lastUpdatedTime', trim($lastUpdatedInDB), $preFix, $debug);

        self::_log('## FINISH ##############################', __CLASS__ . '::' . __FUNCTION__,  $preFix, $start);
    }
    /**
     * getting the data
     *
     * @param string $preFix
     * @param string $debug
     *
     * @return array
     */
    private static function _getData(&$lastUpdateDB, $preFix = '', $debug = false)
    {
        self::_log('== Trying to get all the updated price for products:', __CLASS__ . '::' . __FUNCTION__,  $preFix);
        $settings = self::_getSettings($preFix . self::TAB, $debug);
        $lastUpdatedTime = UDate::zeroDate();
        if(isset($settings['lastUpdatedTime']))
            $lastUpdatedTime = new UDate(trim($settings['lastUpdatedTime']));
        self::_log('GOT LAST SYNC TIME: ' . trim($settings['lastUpdatedTime']), '',  $preFix);
        $productPrices = ProductPrice::getAllByCriteria('updated > ?', array(trim($lastUpdatedTime)));
        self::_log('GOT ' . count($productPrices) . ' Price(s) that has changed after "' . trim($lastUpdatedTime) . '".', '',  $preFix);
        $lastUpdateInDb = UDate::zeroDate();
        $products = array();
        foreach($productPrices as $productPrice){
            if($productPrice->getUpdated()->afterOrEqualTo($lastUpdateInDb))
                $lastUpdateInDb = $productPrice->getUpdated();
            $products[] = $productPrice->getProduct();
        }
        return $products;
    }
    /**
     * Getting the setting for the last sync
     *
     * @param string $preFix
     * @param string $debug
     *
     * @throws Exception
     * @return multitype:
     */
    private static function _getSettings($preFix = '', $debug = false)
    {
        $paramName = SystemSettings::TYPE_MAGENTO_SYNC;
        self::_log('== Trying to get SystemSettings for :' . $paramName, __CLASS__ . '::' . __FUNCTION__,  $preFix);
        if(!isset(self::$_cache[__CLASS__ . ':settings:' . $paramName])) {

            $settingString = SystemSettings::getSettings($paramName);
            self::_log('GOT string: ' . $settingString, '',  $preFix . self::TAB);

            self::$_cache[__CLASS__ . ':settings'] = json_decode($settingString, true);
//             if(json_last_error() == JSON_ERROR_NONE)
//                 throw new Exception('Invalid JSON string:' . $settingString);
        }
        self::_log('GOT settings: ' . preg_replace('/\s+/', ' ', print_r(self::$_cache[__CLASS__ . ':settings'], true)), '',  $preFix . self::TAB);
        self::_log('');
        return self::$_cache[__CLASS__ . ':settings'];
    }
    private static function _setSettings($key, $value, $preFix = '', $debug = false)
    {
        $paramName = SystemSettings::TYPE_MAGENTO_SYNC;
        self::_log('== Trying to set SystemSettings for: "' . $paramName . '" with new value: ' . $value, __CLASS__ . '::' . __FUNCTION__,  $preFix);
        $settings = self::_getSettings($preFix, $debug);
        $settings[$key] = $value;
        if (($settingObj = SystemSettings::getByType($paramName)) instanceof SystemSettings) {
            $jsonString = json_encode($settings);
            $settingObj->setValue($jsonString)
                ->save();
        }
        self::_log('DONE', '', $preFix . self::TAB);
        self::_log('');
        self::$_cache[__CLASS__ . ':settings:' . $paramName] = $settings;
    }
    /**
     * Logging
     *
     * @param string $msg
     * @param string $funcName
     * @param string $preFix
     * @param UDate  $start
     * @param string $postFix
     *
     * @return UDate
     */
    private static function _log($msg, $funcName = '', $preFix = "", UDate $start = null, $postFix = "\r\n")
    {
        $now = new UDate();
        $timeElapsed = '';
        if($start instanceof UDate) {
            $timeElapsed = $now->diff($start);
            $timeElapsed = ' TOOK (' . $timeElapsed->format('%s') . ') seconds ';
        }
        $nowString = '';
        if(trim($msg) !== '')
            $nowString = ' [' . trim($now) . '] ';
        $logMsg = $preFix . $msg . $nowString . $timeElapsed . ($funcName !== '' ? (' '  . $funcName . ' ') : '') . $postFix;
        echo $logMsg;
        if(is_file(self::$_logFile))
            file_put_contents(self::$_logFile, $logMsg, FILE_APPEND);
        return $now;
    }

   	private static function _genCSV(array $products, $preFix = '', $debug = false)
   	{
   		// Create new PHPExcel object
   		self::_log ("== Create new PHPExcel object", __CLASS__ . '::' . __FUNCTION__, $preFix);
   		$objPHPExcel = new PHPExcel();

   		// Add some data
   		$objPHPExcel->setActiveSheetIndex(0);
   		self::_log ("Populating " . count($products) . ' product(s) onto the first sheet.', '', $preFix . self::TAB);
   		self::_genSheet($objPHPExcel->getActiveSheet(), $products, $preFix, $debug);

   		$filePath = self::$_outputFilePath . self::OUTPUT_FILE_NAME;
   		self::_log ("Saving to :" . $filePath, '', $preFix . self::TAB);

   		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
		$objWriter->save($filePath);

   		self::_log ("DONE", '', $preFix . self::TAB);
   	}
   	/**
   	 * generating the worksheet
   	 *
   	 * @param PHPExcel_Worksheet $sheet
   	 * @param array              $data
   	 * @param string             $preFix
   	 * @param bool               $debug
   	 */
   	private static function _genSheet(PHPExcel_Worksheet &$sheet, array $data, $preFix = '', $debug = false)
   	{
   		self::_log ('-- Generating the sheets: ', '', $preFix);
   		$rowNo = 1;
   		$titles = array_keys(self::_getRowWithDefaultValues(null, $preFix, $debug));
//    		self::_log(print_r($titles, true));
   		foreach($titles as $colNo => $colValue) {
   			$sheet->setCellValueByColumnAndRow($colNo, $rowNo, $colValue);
   		}
   		$rowNo += 1;
   		self::_log ('Generated title row', '', $preFix . self::TAB);

   		foreach($data as $index => $product) {
       		self::_log ('ROW: ' . $index, '', $preFix . self::TAB);
   			if(!$product instanceof Product) {
           		self::_log ('SKIPPED, invalid product.', '', $preFix . self::TAB . self::TAB);
   				continue;
   			}
   			foreach(array_values(self::_getRowWithDefaultValues($product, $preFix, $debug)) as $colNo => $colValue) {
   				$sheet->setCellValueByColumnAndRow($colNo, $rowNo, $colValue);
   			}
			self::_log ('ADDED.', '', $preFix . self::TAB . self::TAB);
   			$rowNo += 1;
   		}
   		self::_log ('-- DONE', '', $preFix);
   	}
   	/**
   	 * The row with default value
   	 *
   	 * @param Product $product
   	 * @param string $preFix
   	 * @param string $debug
   	 *
   	 * @return multitype:string number
   	 */
   	private static function _getRowWithDefaultValues(Product $product = null, $preFix = '', $debug = false)
   	{
   		return array("store" => 'default',
   				"websites" => 'base',
   				"attribute_set" => ($product instanceof Product && $product->getAttributeSet() instanceof ProductAttributeSet ? $product->getAttributeSet()->getName() : 'Default'), //attribute_name
   				"type" => 'simple',
   				"category_ids" => '2', //123,12312
   				"sku" => ($product instanceof Product ? $product->getSku() : ''), //sku
   				"name" => ($product instanceof Product ? $product->getName() : ''), //product name
   				"price" => ($product instanceof Product && count($prices = $product->getPrices()) > 0 ? $prices[0]->getPrice() : ''), //unitPrice
   				"special_from_date" => '', //special_from_date
   				"special_to_date" => '', //special_to_date
   				"special_price" => '', //special_price
   				"news_from_date" => '', //news_from_date
   				"news_to_date" => '', //news_to_date
   				"status" => 1, //1 - enable, 2 - disable
   				"visibility" => 4, //4 -
   				"tax_class_id" => 2, // 2
   				"description" => '"' . ($product instanceof Product && ($asset = Asset::getAsset($product->getFullDescAssetId())) instanceof Asset ? Asset::readAssetFile($asset->getPath()) : '') . '"', //full description
   				"short_description" => ($product instanceof Product ? $product->getShortDescription() : ''), //short description
   				"supplier" => ($product instanceof Product && count($supplierCodes = $product->getSupplierCodes()) > 0 && ($supplier = $supplierCodes[0]->getSupplier()) instanceof Supplier ? $supplier->getName() : ''), // the name of the supplier
   				"man_code" => '', //manufacturer code
   				"sup_code" => (isset($supplierCodes[0]) && $supplierCodes[0] instanceof SupplierCode ? $supplierCodes[0]->getCode() : ''), //supplier code
   				"has_options" => '',
   				"meta_title" => '',
   				"meta_description" => '',
   				"manufacturer" => ($product instanceof Product && $product->getManufacturer() instanceof Manufacturer ? $product->getManufacturer()->getName() : ''), //manufacture value
   				"url_key" => '',
   				"url_path" => '',
   				"custom_design" => '',
   				"page_layout" => '',
   				"options_container" => '',
   				"country_of_manufacture" => '',
   				"msrp_enabled" => '',
   				"msrp_display_actual_price_type" => '',
   				"meta_keyword" => '',
   				"custom_layout_update" => '',
   				"custom_design_from" => '',
   				"custom_design_to" => '',
   				"weight" => '',
   				"msrp" => 'Use config', //
   				"gift_wrapping_price" => '',
   				"qty" => 99, //99
   				"min_qty" => 99,  //99
   				"use_config_min_qty" => 99,  //99
   				"is_qty_decimal"  => '',
   				"backorders" => '',
   				"use_config_backorders" => '',
   				"min_sale_qty" => '',
   				"use_config_min_sale_qty" => '',
   				"max_sale_qty" => '',
   				"use_config_max_sale_qty" => '',
   				"is_in_stock" => 1, //1 - in-stock, 0 - out of stock
   				"low_stock_date" => '',
   				"notify_stock_qty" => '',
   				"use_config_notify_stock_qty" => '',
   				"manage_stock" => '',
   				"use_config_manage_stock" => '',
   				"stock_status_changed_auto" => '',
   				"use_config_qty_increments" => '',
   				"qty_increments" => '',
   				"use_config_enable_qty_inc" => '',
   				"enable_qty_increments" => '',
   				"is_decimal_divided" => '',
   				"stock_status_changed_automatically" => '',
   				"use_config_enable_qty_increments" => '',
   				"is_recurring" => '');
   	}
}

$filePath = '/tmp/';
if(isset($argv) && isset($argv[1]))
    $filePath = trim($argv[1]);
ProductToMagento::run('/tmp/', '', true);