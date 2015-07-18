<?php
class synnexConnector
{
	private $debug = false;
	private $now = '';
	private $feed_from_web = '';
	private $feed_from_web_data = null;
	private $feed_from_ftp = '';
	private $feed_from_ftp_data = null;
	private $feed_from_magento = '';
	private $feed_from_magento_data = null;
	private $feed_output = '';
	private $feed_output_data = null;
	private $ftp_server = "ftp.budgetpc.com.au";
	private $ftp_user_name = "synnex";
	private $ftp_user_pass = "b2Z]7}i?T^+D";
	private $ftp_file_name = "BUDGETPC_synnex_au.txt";
	
	public static function run($feed_from_magento, $feed_from_web = '', $feed_from_ftp = '', $debug = false)
	{
		$class = new self();
		$class->debug = $debug === true ? true : false;
		$class->init($feed_from_magento, $feed_from_web, $feed_from_ftp);
		
		if(trim($feed_from_ftp) === '')
			$class->getFtpFeed($feed_from_ftp, $feed_from_ftp);
		
		// get data from csv
		$class->feed_from_magento_data = $class->getDataFromCsv($class->feed_from_magento);
		$class->feed_from_web_data = $class->getDataFromCsv($class->feed_from_web);
		$class->feed_from_ftp_data = $class->getDataFromCsv($class->feed_from_ftp);

		foreach($class->feed_from_ftp_data->data as $ftpRow)
		{
			$sku = trim($ftpRow['MANUFACTURER_PART_NUMBER']);
			$supplierSku = trim($ftpRow['SUPPLIER_PART_NUMBER']);
			$name = trim($ftpRow['SHORT_DESCRIPTION']);
			$combinedName = '[' . $supplierSku . ']';
			$shortDescription = trim($ftpRow['SHORT_DESCRIPTION']);
			$description = trim($ftpRow['LONG_DESCRIPTION']);
			$brand = trim($ftpRow['MANUFACTURER_NAME']);
			$categories = array();
			if(trim($ftpRow['CATEGORY_OF_PRODUCT_1']) !== '')
				$categories[] = trim($ftpRow['CATEGORY_OF_PRODUCT_1']);
			if(trim($ftpRow['CATEGORY_OF_PRODUCT_2']) !== '')
				$categories[] = trim($ftpRow['CATEGORY_OF_PRODUCT_2']);
			if(trim($ftpRow['CATEGORY_OF_PRODUCT_3']) !== '')
				$categories[] = trim($ftpRow['CATEGORY_OF_PRODUCT_3']);
			if(count($categories) === 0)
			{
				echo 'product sku="' . $sku . '", name="' . $name . '"' . ' do not have a category from synnex' . "\n";
			}
		}		
	}
	private function filterByBrand($brand)
	{
		$brand = trim($brand);
		switch ($brand)
		{
			case 'xxx':
				echo 'xxx';
				break;
			default:
				return false;
		}
	}
	private function getDataFromCsv($file)
	{
		$file = trim($file);
		$paser = new parseCSV();
		// ftp
		$paser->auto($file);
		if($this->debug === true)
			echo "successfull get data (size=" . sizeof($paser->data) . ") from " . $file . "\n";
		return $paser;
	}
	private function getFtpFeed()
	{
		// set up ftp connection
		$conn_id = ftp_connect($this->ftp_server);
		$login_result = ftp_login($conn_id, $this->ftp_user_name, $this->ftp_user_pass);
		if (ftp_get($conn_id, $this->feed_from_ftp, $this->ftp_file_name, FTP_BINARY)) {
			if($this->debug === true)
				echo "Successfully written to" . $this->feed_from_ftp . "\n";
		} else {
			throw new Exception(__CLASS__ . ': Unable to get file from ftp, server="' . $this->ftp_server . '", user_name="' . $this->ftp_user_name . '", ftp file name="' . $this->ftp_file_name . '", output file="' . $this->feed_from_ftp . '", connectionId="' . $conn_id .'", login result="' . $login_result . '"');
		}
		ftp_close($conn_id);
		return $this;
	}
	private function init($feed_from_magento, $feed_from_web, $feed_from_ftp)
	{
		$this->now = str_replace(' ', '_', UDate::now(UDate::TIME_ZONE_MELB)->getDateTimeString());
		// magento
		$this->feed_from_magento = trim($feed_from_magento);
		// ftp
		if(trim($feed_from_ftp) !== '')
			$this->feed_from_ftp = trim($feed_from_ftp);
		else {
			$this->feed_from_ftp = dirname(__FILE__) . "/tmp/" .'synnex_feed_ftp_' . $this->now . '.csv';
			file_put_contents($this->feed_from_ftp, "");
		}
		// web
		if(trim($feed_from_web) !== '')
			$this->feed_from_web = trim($feed_from_web);
		else {
			$this->feed_from_web = dirname(__FILE__) . "/tmp/" .'synnex_feed_web_' . $this->now . '.csv';
			file_put_contents($this->feed_from_web, "");
		}
		// output
		$this->feed_output = dirname(__FILE__) . "/tmp/" .'synnex_feed_output_' . $this->now . '.csv';
		return $this;
	}
}