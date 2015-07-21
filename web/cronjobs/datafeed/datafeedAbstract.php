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
	
	protected $debug = false;
	protected $now = '';
	protected $feed_from_web = '';
	protected $feed_from_web_data = null;
	protected $feed_from_ftp = '';
	protected $feed_from_ftp_data = null;
	protected $feed_from_mail = '';
	protected $feed_from_mail_data = null;
	protected $feed_from_magento = '';
	protected $feed_from_magento_data = null;
	protected $feed_output = '';
	protected $feed_output_data = null;
	
	protected function getMageStockLevel($stocks, $sku) {
		if (($product = Product::getBySku ( trim ( $sku ) )) instanceof Product && intval ( $product->getStockOnHand () ) > 0)
			return self::STOCK_IN_STOCK;
		if (intval ( $stocks ['melbourne'] ) > 0)
			return self::STOCK_IN_STOCK;
		if (intval ( $stocks ['sydney'] ) > 0 || intval ( $stocks ['brisbane'] ) > 0)
			return self::STOCK_SHIP_IN_24_HOURS;
		return self::STOCK_PREORDER;
	}
	protected function getPrice($basePrice, $brand = null, $categories = array()) {
		$basePrice = doubleval ( trim ( $basePrice ) );
		$price = $basePrice * 1.1 * 1.05;
		return $price;
	}
	protected function getDataFromCsv($file) {
		$file = trim ( $file );
		$paser = new parseCSV ();
		// ftp
		$paser->auto ( $file );
		if ($this->debug === true)
			echo "successfull get data (size=" . sizeof ( $paser->data ) . ") from " . $file . "\n";
		return $paser;
	}
}