<?php
require_once dirname(__FILE__) . '/datafeedAbstract.php';
class synnexConnector extends datafeedAbstract 
{
	private $supplier = "synnex";
	private $ftp_server = "ftp.budgetpc.com.au";
	private $ftp_user_name = "synnex";
	private $ftp_user_pass = "b2Z]7}i?T^+D";
	private $ftp_file_name = "BUDGETPC_synnex_au.txt";
	public static function run($feed_from_magento, $feed_from_web = '', $feed_from_ftp = '', $debug = false) {
		$class = new self ();
		$class->debug = $debug === true ? true : false;
		$class->init ( $feed_from_magento, $feed_from_web, $feed_from_ftp );
		
		if (trim ( $feed_from_ftp ) === '')
			$class->getFtpFeed ( $feed_from_ftp, $feed_from_ftp );
			
			// get data from csv
		$class->feed_from_magento_data = $class->getDataFromCsv ( $class->feed_from_magento );
		$class->feed_from_web_data = $class->getDataFromCsv ( $class->feed_from_web );
		$class->feed_from_ftp_data = $class->getDataFromCsv ( $class->feed_from_ftp );
		
		foreach ( $class->feed_from_ftp_data->data as $ftpRow ) {
			$sku = trim ( $ftpRow ['MANUFACTURER_PART_NUMBER'] );
			$supplierSku = trim ( $ftpRow ['SUPPLIER_PART_NUMBER'] );
			$name = trim ( $ftpRow ['SHORT_DESCRIPTION'] );
			$combinedName = '[' . $supplierSku . ']';
			$shortDescription = trim ( $ftpRow ['SHORT_DESCRIPTION'] );
			$description = trim ( $ftpRow ['LONG_DESCRIPTION'] );
			$brand = trim ( $ftpRow ['MANUFACTURER_NAME'] );
			if ($class->filterByBrand ( $brand ) !== true) {
				echo 'MANUFACTURER_NAME from supplier"' . $brand . '" is not included by rule. product sku="' . $sku . '", name="' . $name . '" skipped' . "\n";
				continue;
			}
			$categories = array ();
			if (trim ( $ftpRow ['CATEGORY_OF_PRODUCT_1'] ) !== '')
				$categories [] = trim ( $ftpRow ['CATEGORY_OF_PRODUCT_1'] );
			if (trim ( $ftpRow ['CATEGORY_OF_PRODUCT_2'] ) !== '')
				$categories [] = trim ( $ftpRow ['CATEGORY_OF_PRODUCT_2'] );
			if (trim ( $ftpRow ['CATEGORY_OF_PRODUCT_3'] ) !== '')
				$categories [] = trim ( $ftpRow ['CATEGORY_OF_PRODUCT_3'] );
			if (count ( $categories ) === 0) {
				echo 'product sku="' . $sku . '", name="' . $name . '"' . ' do not have a category from synnex. skipped' . "\n";
				continue;
			}
			$basePrice = trim ( $ftpRow ['RESELLER_BUY_EX'] );
			if ($basePrice === '' || doubleval ( $basePrice ) === doubleval ( 0 )) {
				echo 'RESELLER_BUY_EX from supplier "' . $basePrice . '" is invalid. product sku="' . $sku . '", name="' . $name . '". skipped' . "\n";
				continue;
			}
			$price = $class->getPrice ( $basePrice );
			if (trim ( $ftpRow ['AVAILABILITY_M'] ) === '') {
				echo 'AVAILABILITY_M from supplier "' . trim ( $ftpRow ['AVAILABILITY_M'] ) . '" is invalid. product sku="' . $sku . '", name="' . $name . '". skipped' . "\n";
				continue;
			}
			$stocks = array (
					"melbourne" => trim ( $ftpRow ['AVAILABILITY_M'] ),
					"sydney" => trim ( $ftpRow ['AVAILABILITY_S'] ),
					"brisbane" => trim ( $ftpRow ['AVAILABILITY_B'] ),
					"perth" => trim ( $ftpRow ['AVAILABILITY_P'] )
			);
			$mageStockLevel = $class->getMageStockLevel ( $stocks, $sku );
			$additionalInfo = $class->getAdditionalInfoFromWebFeed($supplierSku, $brand, $categories);
			$weight = doubleval ( trim ( $additionalInfo ['weight'] ) );
			$length = doubleval ( trim ( $additionalInfo ['length'] ) );
			$width = doubleval ( trim ( $additionalInfo ['width'] ) );
			$height = doubleval ( trim ( $additionalInfo ['height'] ) );
			$status = self::MAGE_STATUS;
			$images = array ();
			if (trim ( $ftpRow ['Image1URL'] ) !== '')
				$images [] = trim ( $ftpRow ['Image1URL'] );
			if (trim ( $ftpRow ['Image2URL'] ) !== '')
				$images [] = trim ( $ftpRow ['Image2URL'] );
			if (trim ( $ftpRow ['Image3URL'] ) !== '')
				$images [] = trim ( $ftpRow ['Image3URL'] );
			if (trim ( $ftpRow ['Image4URL'] ) !== '')
				$images [] = trim ( $ftpRow ['Image4URL'] );
			if (trim ( $ftpRow ['Image5URL'] ) !== '')
				$images [] = trim ( $ftpRow ['Image5URL'] );
			$mageAttributeSet = '';
			$mageCategoryIds = '';
			
			if($class->debug === true)
			{
				echo 'sku[MANUFACTURER_PART_NUMBER]' . '=' . $sku . "\n"
					. 'name[SUPPLIER_PART_NUMBER+SHORT_DESCRIPTION]' . '=' . $combinedName . "\n"
					. 'man_code[MANUFACTURER_PART_NUMBER]' . '=' . $sku . "\n"
					. 'manufacturer[MANUFACTURER_NAME]' . '=' . $brand . "\n"
					. 'price[RESELLER_BUY_EX+margin]' . '=' . $sku . "\n"
					. 'all_ln_stock[AVAILABILITY]' . '=' . $mageStockLevel . "\n"
					. 'description[LONG_DESCRIPTION]' . '=' . $description . "\n"
					. 'short_description[SHORT_DESCRIPTION]' . '=' . $shortDescription . "\n"
					. 'attribute_set[CATEGORY_OF_PRODUCT]' . '=' . $mageAttributeSet . "\n"
					. 'category_ids[CATEGORY_OF_PRODUCT]' . '=' . $mageCategoryIds . "\n"
					. 'images[ImageURL]' . '=' . (count($images) === 0 ? 'N/A' : implode(', ', $images)) . "\n"
					. 'product_name[SUPPLIER_PART_NUMBER+SHORT_DESCRIPTION]' . '=' . $combinedName . "\n"
					. 'supplier[]' . '=' . $class->supplier . "\n"
				;
			}
		}
	}
	private function getAdditionalInfoFromWebFeed($supplierSku, $brand = null, $categories = array()) {
		$supplierSku = trim ( $supplierSku );
		$weight = null;
		$length = null;
		$width = null;
		$height = null;
		foreach ( $this->feed_from_web_data->data as $webRow ) {
			if (! isset ( $webRow ['ItemName'] ))
				continue;
			if (trim ( $webRow ['ItemName'] ) !== $supplierSku)
				continue;
			$weight = doubleval ( trim ( $webRow ['Weight'] ) );
			$length = doubleval ( trim ( $webRow ['Length'] ) );
			$width = doubleval ( trim ( $webRow ['Width'] ) );
			$height = doubleval ( trim ( $webRow ['Height'] ) );
			break;
		}
		if($weight === null || doubleval($weight) === doubleval(0))
		{
			foreach ($categories as $category)
			{
				if(doubleval($this->getDefaultWeightByCategory($categories)) > doubleval($weight))
					$weight = doubleval($this->getDefaultWeightByCategory($category));
			}
		}
		return array("weight"=> $weight, "length"=> $length, "width"=> $width, "height"=> $height);
	}
	private function getDefaultWeight($brand = null, $categories = array()) {
		$categories = array_map ( 'strtolower', $categories );
		// if(in_array(strtolower(''), $haystack))
		return doubleval ( 5 );
	}
	private function getDefaultWeightByCategory($category) {
		$category = trim ( strtolower ( $category ) );
		switch ($category) {
			case 'switch' :
				return doubleval ( 2 );
				break;
			case 'lcd monitor' :
				return doubleval ( 5 );
				break;
			case 'printer accessory' :
				return doubleval ( 2 );
				break;
			case 'extended warranty' :
				return doubleval ( 0.1 );
				break;
			case 'desktop pc - consumer' :
				return doubleval ( 10 );
				break;
			case 'tablet pc' :
				return doubleval ( 5 );
				break;
			case 'toner' :
				return doubleval ( 2 );
				break;
			case 'all-in-one pc' :
				return doubleval ( 15 );
				break;
			case 'usb drive' :
				return doubleval ( 0.5 );
				break;
			case 'pc system accessory' :
				return doubleval ( 1 );
				break;
			case 'audio equipment' :
				return doubleval ();
				break;
			case 'optical drive' :
				return doubleval ( 2 );
				break;
			case 'other storage media' :
				return doubleval ( 2 );
				break;
			case 'lan card' :
				return doubleval ( 2 );
				break;
			case 'access point' :
				return doubleval ( 2 );
				break;
			case 'network accessory' :
				return doubleval ( 2 );
				break;
			case 'router' :
				return doubleval ( 2 );
				break;
			case 'scanner' :
				return doubleval ( 10 );
				break;
			case 'notebook - commercial' :
				return doubleval ( 10 );
				break;
			case 'notebook - consumer' :
				return doubleval ( 10 );
				break;
			case 'keyboard' :
				return doubleval ( 2 );
				break;
			case 'notebook accessory' :
				return doubleval ( 2 );
				break;
			case 'desktop pc - commercial' :
				return doubleval ( 10 );
				break;
			case 'graphic card' :
				return doubleval ( 5 );
				break;
			case 'ups' :
				return doubleval ( 15 );
				break;
			case 'mobile phone accessory' :
				return doubleval ( 2 );
				break;
			case 'tablet pc accessory' :
				return doubleval ( 1 );
				break;
			case 'mouse' :
				return doubleval ( 0.5 );
				break;
			case 'network attached storage' :
				return doubleval ( 5 );
				break;
			case 'mother boards' :
				return doubleval ( 2 );
				break;
			case 'barebone pc' :
				return doubleval ( 5 );
				break;
			case 'operating system (oem package)' :
				return doubleval ( 0.1 );
				break;
			case 'projector' :
				return doubleval ( 5 );
				break;
			case 'personal navigation device' :
				return doubleval ( 1 );
				break;
			case 'oem software' :
				return doubleval ( 0.1 );
				break;
			case 'retail software' :
				return doubleval ( 0.1 );
				break;
			case 'web camera' :
				return doubleval ( 0.5 );
				break;
			case 'ink cartridge' :
				return doubleval ( 0.1 );
				break;
			case 'external hard drive' :
				return doubleval ( 1 );
				break;
			case 'paper media' :
				return doubleval ( 1 );
				break;
			case 'mono laser printer' :
				return doubleval ( 10 );
				break;
			case 'colour laser printer' :
				return doubleval ( 10 );
				break;
			case 'personal audio products' :
				return doubleval ( 1 );
				break;
			case 'dram module' :
				return doubleval ( 0.8 );
				break;
			case 'notebook memory' :
				return doubleval ( 0.5 );
				break;
			case 'server accessory' :
				return doubleval ( 10 );
				break;
			case 'digital photoframe' :
				return doubleval ( 1 );
				break;
			case 'solid state drive' :
				return doubleval ( 1 );
				break;
			case 'ink jet printer' :
				return doubleval ( 10 );
				break;
			case 'add-on card' :
				return doubleval ( 1 );
				break;
			case 'power supply/battery pack' :
				return doubleval ( 5 );
				break;
			case 'option parts' :
				return doubleval ( 5 );
				break;
			case 'cd/dvd player' :
				return doubleval ( 2 );
				break;
			case 'tray cpu' :
				return doubleval ( 1 );
				break;
			case 'chasis' :
				return doubleval ( 10 );
				break;
			case 'modem' :
				return doubleval ( 1 );
				break;
			case 'serverboard' :
				return doubleval ( 5 );
				break;
			case 'all-in-one printer' :
				return doubleval ( 10 );
				break;
			case 'dot matrix printer' :
				return doubleval ( 10 );
				break;
			case 'sound card' :
				return doubleval ( 1 );
				break;
			case 'sony mobile' :
				return doubleval ( 1 );
				break;
			case 'enterprise hard disk drive' :
				return doubleval ( 1 );
				break;
			case 'box cpu' :
				return doubleval ( 1 );
				break;
			case 'remote controller' :
				return doubleval ( 1 );
				break;
			case 'tv game accessory' :
				return doubleval ( 1 );
				break;
			case 'speakers' :
				return doubleval ( 5 );
				break;
			case 'pc games' :
				return doubleval ( 1 );
				break;
			case 'warranty' :
				return doubleval ( 0.1 );
				break;
			case 'hard disk drive' :
				return doubleval ( 1 );
				break;
			case 'operating system (retail package)' :
				return doubleval ( 0.1 );
				break;
			case 'ribbon' :
				return doubleval ( 1 );
				break;
			case 'servers' :
				return doubleval ( 18 );
				break;
			case 'lcd tv' :
				return doubleval ( 30 );
				break;
			case 'label printer' :
				return doubleval ( 5 );
				break;
			case 'computer chassis' :
				return doubleval ( 10 );
				break;
			case 'large format ink cartridge' :
				return doubleval ( 0.5 );
				break;
			case 'large format printer' :
				return doubleval ( 10 );
				break;
			case 'memory card' :
				return doubleval ( 0.5 );
				break;
			case 'fax machine' :
				return doubleval ( 5 );
				break;
			case 'gaming pc' :
				return doubleval ( 15 );
				break;
			case 'printer server' :
				return doubleval ( 1 );
				break;
			case 'optical consumer electronics' :
				return doubleval ( 1 );
				break;
			default:
				return doubleval(5);
		}
	}
	private function filterByBrand($brand) {
		$brand = strtolower ( trim ( $brand ) );
		switch ($brand) {
			case 'access' :
				return false;
				break;
			case 'acer' :
				return true;
				break;
			case 'adaptec' :
				return true;
				break;
			case 'adata' :
				return true;
				break;
			case 'aiptek' :
				return true;
				break;
			case 'apple' :
				return false;
				break;
			case 'asus' :
				return true;
				break;
			case 'belkin' :
				return true;
				break;
			case 'brother' :
				return true;
				break;
			case 'checkpoint' :
				return true;
				break;
			case 'corsair' :
				return true;
				break;
			case 'dlink' :
				return true;
				break;
			case 'eaton' :
				return true;
				break;
			case 'epson' :
				return true;
				break;
			case 'fuji-xerox' :
				return true;
				break;
			case 'getac' :
				return false;
				break;
			case 'gigabyte' :
				return true;
				break;
			case 'hp' :
				return true;
				break;
			case 'hpe' :
				return true;
				break;
			case 'intel' :
				return true;
				break;
			case 'kingston' :
				return true;
				break;
			case 'laser' :
				return true;
				break;
			case 'lenovo' :
				return true;
				break;
			case 'lexmark' :
				return true;
				break;
			case 'lg' :
				return true;
				break;
			case 'lite-on' :
				return true;
				break;
			case 'logitech' :
				return true;
				break;
			case 'microsoft' :
				return true;
				break;
			case 'netgear' :
				return true;
				break;
			case 'ocz' :
				return true;
				break;
			case 'plextor' :
				return true;
				break;
			case 'rapoo' :
				return false;
				break;
			case 'samsung' :
				return true;
				break;
			case 'sandisk' :
				return true;
				break;
			case 'seagate' :
				return true;
				break;
			case 'sony' :
				return false;
				break;
			case 'steel series' :
				return true;
				break;
			case 'thecus' :
				return true;
				break;
			case 'toshiba' :
				return true;
				break;
			case 'transcend' :
				return true;
				break;
			case 'trendmicro' :
				return true;
				break;
			case 'viewsonic' :
				return true;
				break;
			case 'western digital' :
				return true;
				break;
			default :
				echo 'brand "' . $brand . 'not found' . "\n";
				return false;
		}
	}
	private function getFtpFeed() {
		// set up ftp connection
		$conn_id = ftp_connect ( $this->ftp_server );
		$login_result = ftp_login ( $conn_id, $this->ftp_user_name, $this->ftp_user_pass );
		if (ftp_get ( $conn_id, $this->feed_from_ftp, $this->ftp_file_name, FTP_BINARY )) {
			if ($this->debug === true)
				echo "Successfully written to" . $this->feed_from_ftp . "\n";
		} else {
			throw new Exception ( __CLASS__ . ': Unable to get file from ftp, server="' . $this->ftp_server . '", user_name="' . $this->ftp_user_name . '", ftp file name="' . $this->ftp_file_name . '", output file="' . $this->feed_from_ftp . '", connectionId="' . $conn_id . '", login result="' . $login_result . '"' );
		}
		ftp_close ( $conn_id );
		return $this;
	}
	private function init($feed_from_magento, $feed_from_web, $feed_from_ftp) {
		$this->now = str_replace ( ' ', '_', UDate::now ( UDate::TIME_ZONE_MELB )->getDateTimeString () );
		// magento
		$this->feed_from_magento = trim ( $feed_from_magento );
		// ftp
		if (trim ( $feed_from_ftp ) !== '')
			$this->feed_from_ftp = trim ( $feed_from_ftp );
		else {
			$this->feed_from_ftp = dirname ( __FILE__ ) . "/tmp/" . 'synnex_feed_ftp_' . $this->now . '.csv';
			file_put_contents ( $this->feed_from_ftp, "" );
		}
		// web
		if (trim ( $feed_from_web ) !== '')
			$this->feed_from_web = trim ( $feed_from_web );
		else {
			$this->feed_from_web = dirname ( __FILE__ ) . "/tmp/" . 'synnex_feed_web_' . $this->now . '.csv';
			file_put_contents ( $this->feed_from_web, "" );
		}
		// output
		$this->feed_output = dirname ( __FILE__ ) . "/tmp/" . 'synnex_feed_output_' . $this->now . '.csv';
		return $this;
	}
	private function _getBrandCategoryPairs($type)
	{
		$type = strtolower($type);
		$result = array();
		if($type === 'web')
			$data = $this->feed_from_web_data->data;
		foreach ($data as $item)
		{
			if($type === "web")
			{
				$brand = trim($item['Brand']);
				$category = trim($item['Category']);
				if(!isset($result[$brand]))
					$result[$brand] = array();
				if(!isset($result[$brand][$category]))
				{
					$result[$brand][$category] = 1;
				}
				else $result[$brand][$category] += 1;
			}
		}
		if($this->debug === true)
		{
			foreach ($result as $brand => $categorie)
			{
				foreach ($categorie as $category => $count)
				{
					echo $brand . "\t" . $category . "\t" . $count . "\n";
				}
			}
		}
		return $result;
	}
	public static function getBrandCategoryPairs($feed_from_magento, $feed_from_web = '', $feed_from_ftp = '', $debug = false)
	{
		$class = new self ();
		$class->debug = $debug === true ? true : false;
		$class->init ( $feed_from_magento, $feed_from_web, $feed_from_ftp );
		
		if (trim ( $feed_from_ftp ) === '')
			$class->getFtpFeed ( $feed_from_ftp, $feed_from_ftp );
			
		// get data from csv
		$class->feed_from_magento_data = $class->getDataFromCsv ( $class->feed_from_magento );
		$class->feed_from_web_data = $class->getDataFromCsv ( $class->feed_from_web );
		$class->feed_from_ftp_data = $class->getDataFromCsv ( $class->feed_from_ftp );
		
		return $class->_getBrandCategoryPairs('web');
	}
}