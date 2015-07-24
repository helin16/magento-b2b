<?php
abstract class datafeedAbstract
{
	const STOCK_BACK_ORDER_ONLY = "Back Order Only";
	const STOCK_IN_STOCK = "In Stock";
	const STOCK_PREORDER = "Pre Order";
	const STOCK_SHIP_IN_24_HOURS = "Ships In 24Hrs";
	const STOCK_STOCK_LOW = "Stock Low";
	// all the const with MAGE_prix must exact match they column title need for magento, eg. MAGE_STORE -> store
	const MAGE_STORE = "admin";
	const MAGE_WEBSITES = "base";
	const MAGE_TYPE = "simple";
	const MAGE_HAS_OPTIONS = 0;
	const MAGE_META_TITLE = "";
	const MAGE_META_DESCRIPTION = "";
	const MAGE_URL_KEY = "";
	const MAGE_URL_PATH = "";
	const MAGE_CUSTOM_DESIGN = "";
	const MAGE_PAGE_LAYOUT = "No layout updates";
	const MAGE_OPTIONS_CONTAINER = "Block after Info Column";
	const MAGE_IMAGE_LABEL = "";
	const MAGE_SMALL_IMAGE_LABEL = "";
	const MAGE_THUMBNAIL_LABEL = "";
	const MAGE_COUNTRY_OF_MANUFACTURE = "";
	const MAGE_MSRP_ENABLED = "Use config";
	const MAGE_MSRP_DISPLAY_ACTUAL_PRICE_TYPE = "Use config";
	const MAGE_GIFT_MESSAGE_AVAILABLE = "";
	const MAGE_MSRP = "";
	const MAGE_STATUS = "Enabled";
	const MAGE_VISIBILITY = "Catalog, Search";
	const MAGE_TAX_CLASS_ID = "Taxable Goods";
	const MAGE_IS_RECURRING = "No";
	const MAGE_META_KEYWORD = "";
	const MAGE_CUSTOM_LAYOUT_UPDATE = "";
	const MAGE_VIDEOBOX = "";
	const MAGE_CUSTOMTAB = "";
	const MAGE_CUSTOMTABTITLE = "Features";
	const MAGE_SHORTPARAMS = "";
	const MAGE_MIN_QTY = 0;
	const MAGE_USE_CONFIG_MIN_QTY = 1;
	const MAGE_IS_QTY_DECIMAL = 0;
	const MAGE_BACKORDERS = 0;
	const MAGE_USE_CONFIG_BACKORDERS = 0;
	const MAGE_MIN_SALE_QTY = 1;
	const MAGE_USE_CONFIG_MIN_SALE_QTY = 1;
	const MAGE_MAX_SALE_QTY = 0;
	const MAGE_USE_CONFIG_MAX_SALE_QTY = 1;
	const MAGE_IS_IN_STOCK = 1;
	const MAGE_LOW_STOCK_DATE = 1;
	const MAGE_NOTIFY_STOCK_QTY = "";
	const MAGE_USE_CONFIG_NOTIFY_STOCK_QTY = 1;
	const MAGE_MANAGE_STOCK = 0;
	const MAGE_USE_CONFIG_MANAGE_STOCK = 1;
	const MAGE_STOCK_STATUS_CHANGED_AUTO = 0;
	const MAGE_USE_CONFIG_QTY_INCREMENTS = 1;
	const MAGE_QTY_INCREMENTS = 0;
	const MAGE_USE_CONFIG_QTY_INC = 1;
	const MAGE_ENABLE_QTY_INCREMENTS = 0;
	const MAGE_IS_DECIMAL_DIVIDED = 0;
	const MAGE_STOCK_STATUS_CHANGED_AUTOMATICALLY = 0;
	const MAGE_USE_CONFIG_ENABLE_QTY_INCREMENTS = 1;
	const MAGE_STORE_ID = 0;
	const MAGE_PRODUCT_TYPE_ID = "simple";
	const MAGE_PRODUCT_STATUS_CHANGED = "";
	const MAGE_PRODUCT_CHANGED_WEBSITES = "";
	
	protected $_scriptStartTime = false;
	protected $_debug = false;
	protected $_feed_from_web_filePath = '';
	protected $_feed_from_web_data = null;
// 	protected $_feed_from_ftp = '';
// 	protected $_feed_from_ftp_data = null;
// 	protected $_feed_from_mail = '';
// 	protected $_feed_from_mail_data = null;
	protected $_feed_from_magento_filePath = '';
	protected $_feed_from_magento_data = null;
	protected $_feed_output_filePath = '';
	protected $_feed_output_data = null;
	protected $_cache = array();
	
	public static function run($feed_from_magento_filePath, $feed_from_web_filePath = '', $debug = false)
	{
		$class = get_called_class();
		$class = new $class ($debug);
		//init / setup environment
		$class->init ( $feed_from_magento_filePath, $feed_from_web_filePath )
			->getData() //Getting the data ready. ie.:download from web/ ftp /email
			->analyze() //compare supplier's data with magento
			->updateSystem() //either push data to magento or update the current system only
		;
		return $class;
	}
	/**
	 * Constructor
	 * 
	 * @param string $feed_from_magento The Url or 
	 * @param string $feed_from_web_filePath
	 * @param bool   $debug
	 */
	public function __construct($debug = false, UDate $scriptStartTime = null)
	{
		$this->_debug = $debug;
		$this->_scriptStartTime = ($scriptStartTime === null ? UDate::now() : $scriptStartTime);
	}
	/**
	 * Getting the root path of the logs
	 * 
	 * @return string
	 */
	protected function _getLogFileRootPath()
	{
		return '/tmp/';
	}
	/**
	 * initiating process
	 * 
	 * @param string $feed_from_magento
	 * @param string $feed_from_web_filePath
	 * 
	 * @return datafeedAbstract
	 */
	public function init($feed_from_magento_filePath, $feed_from_web_filePath)
	{
		$this->_feed_from_magento_filePath = $feed_from_magento_filePath;
		$this->_feed_from_web_filePath = $feed_from_web_filePath;
		// magento
		$this->_feed_from_magento_filePath = trim ( $feed_from_magento_filePath );
		// web
		if (trim ( $feed_from_web_filePath ) !== '')
			$this->_feed_from_web_filePath = trim ( $feed_from_web_filePath );
		else {
			$this->_feed_from_web_filePath = $this->_getLogFileRootPath() . get_class($this) . '_feed_web_' . $this->_scriptStartTime . '.csv';
			file_put_contents ( $this->_feed_from_web_filePath, "" );
		}
		// output
		$this->feed_output_file = $this->_getFeedOutputFile();
		return $this;
	}
	/**
	 * getting the data from web
	 * 
	 * @return datafeedAbstract
	 */
	public function getData()
	{
// 		$this->_downloadCSV();
// 		$this->_feed_from_magento_data = $this->_getDataFromCsv($this->_feed_from_magento_filePath);
// 		$this->_feed_from_web_data = $this->_getDataFromCsv($this->_feed_from_web_filePath);
		return $this;
	}
	public function analyze()
	{
		return $this;
	}
	public function updateSystem()
	{
		return $this;
	}
	/**
	 * Whether the cache exsits
	 * 
	 * @param unknown $key
	 * 
	 * @return boolean
	 */
	protected function _cacheExits($key)
	{
		return array_key_exists($key, $this->_cache);
	}
	protected function _cache_add($key, $value, $override = false)
	{
		if(!$this->_cacheExits($key) || $override === true)
			$this->_cache[$key] = $value;
		return $this;
	}
	protected function _cache_get($key)
	{
		if(!$this->_cacheExits($key))
			return null;
		return $this->_cache[$key];
	}
	/**
	 * Getting the feed output file path
	 * 
	 * @return string
	 */
	protected function _getFeedOutputFile()
	{
		return $this->_getLogFileRootPath() . get_class($this) . '_feed_output_' . $this->_scriptStartTime . '.csv';
	}
	/**
	 * Gettin gthe GST rate
	 * 
	 * @return number
	 */
	protected function _getGSTRate()
	{
		return 0.1;
	}
	/**
	 * Getitng hte margin rate for this supplier
	 * 
	 * @return number
	 */
	protected function _getMargeRate()
	{
		return 0.05;
	}
	/**
	 * Getting the calculated price based on margin and GST
	 * 
	 * @param string $basePrice
	 * @param string $brand
	 * @param array  $categories
	 * 
	 * @return number
	 */
	protected function _getRetailPrice($basePrice, $brand = null, $categories = array()) {
		$basePrice = doubleval ( trim ( $basePrice ) );
		$price = $basePrice * ($this->_getGSTRate() + 1) * ($this->_getMargeRate() + 1);
		return $price;
	}
	/**
	 * Getting the data from a file
	 * 
	 * @param string $filePath The file path of data sheet
	 * 
	 * @return parseCSV
	 */
	protected function _getDataFromCsv($filePath) {
		$filePath = trim ( $filePath );
		$paser = new parseCSV ();
		$paser->auto ( $filePath );
		$this->_log("successfull get data (size=" . sizeof ( $paser->data ) . ") from File: " . $filePath);			
		return $paser;
	}
	/**
	 * Logging messages
	 * 
	 * @param string $msg
	 * @param string $newLine
	 * 
	 * @return datafeedAbstract
	 */
	protected function _log($msg, $newLine = "\n")
	{
		if($this->_debug === true)
			echo $msg . $newLine;
		return $this;
	}
}