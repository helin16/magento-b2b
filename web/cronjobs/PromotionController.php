<?php
/**
 * this file needs to be copied to ec2 server /var/www/html/shell/
 */
require_once 'abstract.php';

class PromotionController extends Mage_Shell_Abstract {

	private $_username="bpc_rds";
	private $_password="QgbN58^a~B9jjAf";
	private $_dbname="radiocon_magenew";
	private $_dbhost="datafeed9.caqb2yq2sxkg.ap-southeast-2.rds.amazonaws.com";
				
	private $_query_opendaily0="update catalog_category_entity_int cci
	inner join eav_attribute ea
	set value = 0 
	where ea.attribute_code = 'is_active' and cci.attribute_id = ea.attribute_id and entity_id = 336;";

	private $_query_opendaily1="update catalog_category_entity_int cci
	inner join eav_attribute ea
	set value = 1 
	where ea.attribute_code = 'is_active' and cci.attribute_id = ea.attribute_id and entity_id = 337;";

	private $_query_closedaily0="update catalog_category_entity_int cci
	inner join eav_attribute ea
	set value = 1 
	where ea.attribute_code = 'is_active' and cci.attribute_id = ea.attribute_id and entity_id = 336;";

	private $_query_closedaily1="update catalog_category_entity_int cci
	inner join eav_attribute ea
	set value = 0 
	where ea.attribute_code = 'is_active' and cci.attribute_id = ea.attribute_id and entity_id = 337;";

	private $_query_openweekend0="update catalog_category_entity_int cci
	inner join eav_attribute ea
	set value = 0 
	where ea.attribute_code = 'is_active' and cci.attribute_id = ea.attribute_id and entity_id = 366;";

	private $_query_openweekend1="update catalog_category_entity_int cci
	inner join eav_attribute ea
	set value = 1 
	where ea.attribute_code = 'is_active' and cci.attribute_id = ea.attribute_id and entity_id = 367;";

	private $_query_closeweekend0="update catalog_category_entity_int cci
	inner join eav_attribute ea
	set value = 1 
	where ea.attribute_code = 'is_active' and cci.attribute_id = ea.attribute_id and entity_id = 366;";

	private $_query_closeweekend1="update catalog_category_entity_int cci
	inner join eav_attribute ea
	set value = 0 
	where ea.attribute_code = 'is_active' and cci.attribute_id = ea.attribute_id and entity_id = 367;";


	/**
	 * Refreshes caches for the provided cache types.
	 * @param  $types
	 * @return void
	 */
	private function clear_blockhtml() {
		Mage::app()->getCacheInstance()->cleanType('block_html');
		$now = date("Y-m-d H:i:s");
		echo "cache block_html refreshed at $now .\n";
	}

	/**
	 * Run script
	 *
	 */
	public function run() {
		try {
				$this->getDBConfig();
				if ($this->getArg('dailyopen')) {
					// dailyopen
				  $this->OpenDailyPromotion();
				  $this->clear_blockhtml();
				} else if ($this->getArg('dailyclose')) {
					// --dailyclose
				  $this->CloseDailyPromotion();
				  $this->clear_blockhtml();
				} else if ($this->getArg('weekendopen')) {
					// --weekendopen
				  $this->OpenWeekendPromotion();
				  $this->clear_blockhtml();
				} else if ($this->getArg('weekendclose')) {
					// --weekendclose
				  $this->CloseWeekendPromotion();
				  $this->clear_blockhtml();
				} else if ($this->getArg('dailycancel')) {
					// --dailycancel
				  $this->CancelDailyPromotion();
				  $this->clear_blockhtml();
				} else if ($this->getArg('weekendcancel')) {
					// --weekendcancel
				  $this->CancelWeekendPromotion();
				  $this->clear_blockhtml();
				} else {
					// help
					echo $this->usageHelp();
				}
		} catch (Exception $e) {
				echo "An error occurred while running promotion controller.\n";
				echo $e->toString() . "\n";
		}
	}
	/**
	 * Retrieve Usage Help Message
	 *
	 */
	public function usageHelp() {
		return <<<USAGE
Usage:  php -f PromotionController.php [options]
  dailyopen                     Start daily promotion.
  dailyclose                    Close daily promotion.
  dailycancel                   Cancel daily promotion.
  weekendopen                   Start weekend promotion.
  weekendclose                  Close weekend promotion.
  weekendcancel                 Cancel weekend promotion.
  help                          This help.

USAGE;
	}


	private function OpenDailyPromotion()
  	{
		$now = date("Y-m-d H:i:s");
		echo "Start OpenDailyPromotion at $now .\n";
		mysql_connect($this->_dbhost,$this->_username,$this->_password);
		@mysql_select_db($this->_dbname) or die(strftime('%c')." Unable to select database");
		mysql_query($this->_query_opendaily0);
		mysql_query($this->_query_opendaily1);
		mysql_close();
	    $now = date("Y-m-d H:i:s");
	    echo "End OpenDailyPromotion at $now .\n";
 	}

	private function CloseDailyPromotion()
  	{
		$now = date("Y-m-d H:i:s");
		echo "Start CloseDailyPromotion at $now .\n";
		mysql_connect($this->_dbhost,$this->_username,$this->_password);
		@mysql_select_db($this->_dbname) or die(strftime('%c')." Unable to select database");
		mysql_query($this->_query_closedaily0);
		mysql_query($this->_query_closedaily1);
		mysql_close();
	    $now = date("Y-m-d H:i:s");
	    echo "End CloseDailyPromotion at $now .\n";
  	}

	private function CancelDailyPromotion()
  	{
		$now = date("Y-m-d H:i:s");
		echo "Start CancelDailyPromotion at $now .\n";
		mysql_connect($this->_dbhost,$this->_username,$this->_password);
		@mysql_select_db($this->_dbname) or die(strftime('%c')." Unable to select database");
		mysql_query($this->_query_opendaily0);
		mysql_query($this->_query_closedaily1);
		mysql_close();
	    $now = date("Y-m-d H:i:s");
	    echo "End CancelDailyPromotion at $now .\n";
  	}

	private function OpenWeekendPromotion()
  	{
		$now = date("Y-m-d H:i:s");
		echo "Start OpenWeekendPromotion at $now .\n";
		mysql_connect($this->_dbhost,$this->_username,$this->_password);
		@mysql_select_db($this->_dbname) or die(strftime('%c')." Unable to select database");
		mysql_query($this->_query_openweekend0);
		mysql_query($this->_query_openweekend1);
		mysql_close();
   	 	$now = date("Y-m-d H:i:s");
    	echo "End OpenWeekendPromotion at $now .\n";
  	}

	private function CloseWeekendPromotion()
	{
		$now = date("Y-m-d H:i:s");
		echo "Start CloseWeekendPromotion at $now .\n";
		mysql_connect($this->_dbhost,$this->_username,$this->_password);
		@mysql_select_db($this->_dbname) or die(strftime('%c')." Unable to select database");
		mysql_query($this->_query_closeweekend0);
		mysql_query($this->_query_closeweekend1);
		mysql_close();
		$now = date("Y-m-d H:i:s");
		echo "End CloseWeekendPromotion at $now .\n";
	}

	private function CancelWeekendPromotion()
  	{
		$now = date("Y-m-d H:i:s");
		echo "Start CancelWeekendPromotion at $now .\n";
		mysql_connect($this->_dbhost,$this->_username,$this->_password);
		@mysql_select_db($this->_dbname) or die(strftime('%c')." Unable to select database");
		mysql_query($this->_query_openweekend0);
		mysql_query($this->_query_closeweekend1);
		mysql_close();
	    $now = date("Y-m-d H:i:s");
	    echo "End CancelWeekendPromotion at $now .\n";
  	}

 	private function getDBConfig()
 	{
		$app = Mage::app('default');

		$config = Mage::getConfig()->getResourceConnectionConfig("default_setup");

		$dbinfo = array("host" => $config->host,
				        "user" => $config->username,
				        "pass" => $config->password,
				        "dbname" => $config->dbname
		);
		var_dump($dbinfo);
		$this->_dbhost = $dbinfo["host"];
		$this->_username = $dbinfo["user"];
		$this->_password = $dbinfo["pass"];
		$this->_dbname = $dbinfo["dbname"];

	}

}

$promotion = new PromotionController();
$promotion->run();
