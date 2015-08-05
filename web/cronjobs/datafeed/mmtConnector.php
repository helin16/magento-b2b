<?php
require_once dirname(__FILE__) . '/datafeedAbstract.php';
class mmtConnector extends datafeedAbstract 
{
	private $supplier = "synnex";
	private $feed_from_web_key = '94S0C1U223NF7AC59BO94903AC004E6B4AMD09E83AL46BBD80H648O31F 75D 665F9461C558F25AE';
	private $feed_from_web_link = "http://www.mmt.com.au/datafeed/index.php?lt=s&ft=csv&tk={WEB_FEED_KEY}&af[]=tn&af[]=si&af[]=li&af[]=ai&af[]=dp&af[]=et&af[]=um&af[]=wt&af[]=st&af[]=sn&af[]=ln";
	
	public function getData()
	{
		$this->_feed_from_magento_data = $this->downloadData($this->feed_from_magento);
	}
	
	private function downloadData($url)
	{
		$url = str_replace('{WEB_FEED_KEY}', urlencode($this->feed_from_web_key), $this->feed_from_web_link);
		$key = md5($url) . $this->_scriptStartTime->format('Y_m_d');
		$filePath = $this->_getLogFileRootPath() . $key . '.log';
		if(!file_exists($filePath))
			ComScriptCURL::downloadFile($url, $filePath);
		file_get_contents($filePath);
	}
	
// 	private function _getDatafeedFromWeb()
// 	{
// 		$url = str_replace('{WEB_FEED_KEY}', urlencode($this->feed_from_web_key), $this->feed_from_web_link);
// 		$this->feed_from_web = file_get_contents($url);
// 	}
	
// 	private function _getBrandCategoryPairs()
// 	{
// 		$result = array();
// 		foreach ($this->feed_from_web_data->data as $item)
// 		{
// 			$brand = trim($item['Manufacturer']);
// 			$category = trim($item['Category Name']);
// 			if(!isset($result[$brand]))
// 				$result[$brand] = array();
// 			if(!isset($result[$brand][$category]))
// 			{
// 				$result[$brand][$category] = 1;
// 			}
// 			else $result[$brand][$category] += 1;
// 		}
// 		if($this->debug === true)
// 		{
// 			foreach ($result as $brand => $categorie)
// 			{
// 				foreach ($categorie as $category => $count)
// 				{
// 					echo $brand . "\t" . $category . "\t" . $count . "\n";
// 				}
// 			}
// 		}
// 		return $result;
// 	}
}