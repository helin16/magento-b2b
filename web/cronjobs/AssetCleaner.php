<?php
require_once dirname(__FILE__) . '/../bootstrap.php';
class AssetCleaner
{
    const ASSET_OVERDUE_TIME = "-2 day";
    const NEW_LINE = "\n";
    private static $_debug = false;
    /**
     * runner
     * @param string $debug
     */
    public static function run($debug = false)
    {
        try {
            self::$_debug = $debug;
            Dao::beginTransaction();
            Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
            $start = self::_debug("Start to run " . __CLASS__ . ' =================== ');
            $assetIds = self::_findAllOverdueAssets();
            $assetIds = array_merge($assetIds, self::_findAllZombieAssets());
            self::_deleteAssets($assetIds);
            self::_debug("Finished to run " . __CLASS__ . ' =================== ', self::NEW_LINE, "", $start);
            Dao::commitTransaction();
        } catch(Exception $ex) {
            Dao::rollbackTransaction();
            self::_debug("***** ERROR: " . $ex->getMessage());
            self::_debug($ex->getTraceAsString());
        }
    }
    /**
	 * Output the debug message
	 *
	 * @param string $msg
	 *
	 */
	private static function _debug($msg = "", $newLine = self::NEW_LINE, $prefix = "", UDate $start = null)
	{
	    $now = UDate::now(SystemSettings::getSettings(SystemSettings::TYPE_SYSTEM_TIMEZONE));
		if(self::$_debug === true)
			echo $prefix . '[' . $now . ']: ' . $msg . ($start instanceof UDate ? ('[ Took ' . ($now->getUnixTimeStamp() - $start->getUnixTimeStamp()) . ' second(s)]') : '') . $newLine;
		return $now;
	}
	/**
	 * Getting all the over assets
	 *
	 * @return multitype:NULL
	 */
	private  static function _findAllOverdueAssets()
	{
	    $start = self::_debug("Start to run " . __FUNCTION__ . ' =================== ', self::NEW_LINE, "\t");
	    $overDueDate = UDate::now()->format(self::ASSET_OVERDUE_TIME);
	    $result = Dao::getResultsNative("select assetId from asset where created > ? and type = ?", array(trim($overDueDate), trim(Asset::TYPE_TMP)));
	    $resultCount = count($result);
	    self::_debug("Found " . $resultCount . ': ', " ", "\t\t");
	    $assetIds = array();
	    for($i = 0; $i < $resultCount; $i++)
    	    $assetIds[] = $result[$i]['assetId'];
	    self::_debug(implode(', ', $assetIds));
	    return $assetIds;
	}
	private  static function _findAllZombieAssets()
	{
	    $start = self::_debug("Start to run " . __FUNCTION__ . ' =================== ', self::NEW_LINE, "\t");
	    $overDueDate = UDate::now()->format(self::ASSET_OVERDUE_TIME);
	    $sql = "select a.assetId from asset a left join product p on (p.fullDescAssetId = a.assetId) where p.id is null and type in(?, ?) ";
	    $result = Dao::getResultsNative($sql, array(trim(Asset::TYPE_PRODUCT_DEC), trim(Asset::TYPE_PRODUCT_IMG)));
	    $resultCount = count($result);
	    self::_debug("Found " . $resultCount . ': ', " ", "\t\t");
	    $assetIds = array();
	    for($i = 0; $i < $resultCount; $i++)
	        $assetIds[] = $result[$i]['assetId'];
        self::_debug(implode(', ', $assetIds));
        return $assetIds;
	}
	private static function _deleteAssets($assetIds = array())
	{
	    $start = self::_debug("Start to deleting " . count($assetIds) . ' asset(s) =================== ', self::NEW_LINE, "\t");
	    Asset::removeAssets($assetIds);
	    self::_debug("Finished deleting " . count($assetIds) . ' asset(s) =================== ', self::NEW_LINE, "\t", $start);
	}
}

AssetCleaner::run(true);