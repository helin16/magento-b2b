<?php
require_once dirname(__FILE__) . '/datafeedAbstract.php';
class mmtConnector extends datafeedAbstract 
{
	private $supplier = "synnex";
	private $feed_from_web_link = "http://www.mmt.com.au/datafeed/index.php?lt=s&ft=csv&tk=94S0C1U223NF7AC59BO94903AC004E6B4AMD09E83AL46BBD80H648O31F 75D 665F9461C558F25AE&af[]=tn&af[]=si&af[]=li&af[]=ai&af[]=dp&af[]=et&af[]=um&af[]=wt&af[]=st&af[]=sn&af[]=ln";
	
	public static function run($feed_from_magento, $feed_from_web = '', $debug = false) 
	{
		$class = new self ();
		$class->debug = $debug === true ? true : false;
		$class->_init ( $feed_from_magento, $feed_from_web );
		$class->_getDatafeedFromWeb();
// 		var_dump($class->feed_from_web_data);
	}
	private function _init($feed_from_magento, $feed_from_web)
	{
		$this->now = str_replace ( ' ', '_', UDate::now ( UDate::TIME_ZONE_MELB )->getDateTimeString () );
		// magento
		$this->feed_from_magento = trim ( $feed_from_magento );
		// web
		if (trim ( $feed_from_web ) !== '')
			$this->feed_from_web = trim ( $feed_from_web );
		else {
			$this->feed_from_web = dirname ( __FILE__ ) . "/tmp/" . 'mmt_feed_web_' . $this->now . '.csv';
			file_put_contents ( $this->feed_from_web, "" );
		}
		// output
		$this->feed_output = dirname ( __FILE__ ) . "/tmp/" . 'mmt_feed_output_' . $this->now . '.csv';
		return $this;
	}
	private function _getDatafeedFromWeb()
	{
		$i = $this->feed_from_web = file_get_contents(urlencode($this->feed_from_web_link));
		var_dump($i);
// 		$this->feed_from_web_data = $this->getDataFromCsv($this->feed_from_web);
		
	}
}