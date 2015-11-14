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
     * The runner
     *
     * @param string $preFix
     * @param string $debug
     */
    public static function run($outputFilePath = '/tmp/', $preFix = '', $debug = false)
    {
		self::$_outputFilePath = trim ($outputFilePath);
    	Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
		self::_genCSV($preFix, $debug);
    }
    private static function _getData($preFix = '', $debug = false)
    {

    }
    private static function _getSettings($preFix = '', $debug = false)
    {
        $paramName = SystemSettings::TYPE_MAGENTO_SYNC;
        self::_log('== Trying to get SystemSettings for :' . $paramName, __CLASS__ . '::' . __FUNCTION__,  $preFix);

        $settingString = SystemSettings::getSettings($paramName);
        self::_log('GOT string: ' . $settingString, '',  $preFix . self::TAB);

        $settings = json_decode($settingString, true);
        if(json_last_error() == JSON_ERROR_NONE)
            throw new Exception('Invalid JSON string:' . $settingString);
        self::_log('GOT settings: ' . preg_replace('/\s+/', ' ', print_r($settingString, true)), '',  $preFix . self::TAB);
        self::_log('');
        return $settings;
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
            $nowString = ' ' . trim($now) . ' ';
        $logMsg = $preFix . $nowString . $msg . $timeElapsed . ($funcName !== '' ? (' '  . $funcName . ' ') : '') . $postFix;
        echo $logMsg;
        if(is_file(self::$_logFile))
            file_put_contents(self::$_logFile, $logMsg, FILE_APPEND);
        return $now;
    }
    
   	private static function _genCSV($preFix = '', $debug = false)
   	{
   		// Create new PHPExcel object
   		self::_log ("Create new PHPExcel object", __CLASS__ . '::' . __FUNCTION__, $preFix);
   		$objPHPExcel = new PHPExcel();
   		
   		// Add some data
   		$objPHPExcel->setActiveSheetIndex(0);
   		self::_genSheet($objPHPExcel->getActiveSheet(), Product::getAll(true, 1, 30), $preFix, $debug);
   		
   		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
   		$filePath = self::$_outputFilePath . self::OUTPUT_FILE_NAME;
   		self::_log ("Saving to :" . $filePath, '', $preFix);
		$objWriter->save($filePath);
   		self::_log ("DONE", '', $preFix . self::TAB);
   	}
   	
   	private static function _genSheet(PHPExcel_Worksheet &$sheet, array $data, $preFix = '', $debug = false)
   	{
   		$rowNo = 0;
   		foreach(array_keys(self::_getRowWithDefaultValues(null, $preFix, $debug)) as $colNo => $colValue) {
   			$sheet->setCellValueByColumnAndRow($colNo, $rowNo, $colValue);
   		}
   		$rowNo++;
   		
   		foreach($data as $product) {
   			if(!$product instanceof Product)
   				continue;
   			foreach(array_values(self::_getRowWithDefaultValues($product, $preFix, $debug)) as $colNo => $colValue) {
   				$sheet->setCellValueByColumnAndRow($colNo, $rowNo, $colValue);
   			}
   			$rowNo++;
   		}
   	}
   	
   	
   	private static function _getRowWithDefaultValues(Product $product = null, $preFix = '', $debug = false) {
   		return array("store" => 'default',
   				"websites" => 'base', 
   				"attribute_set" => ($product instanceof Product && $product->getAttributeSet() instanceof ProductAttributeSet ? $product->getAttributeSet()->getName() : ''), //attribute_name
   				"type" => 'simple',
   				"category_ids" => '', //123,12312
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
   				"manufacturer" => ($product instanceof Product ? $product->getManufacturer()->getName() : ''), //manufacture value
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
   				"msrp", //Use config
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

ProductToMagento::run('/tmp/', '', true);