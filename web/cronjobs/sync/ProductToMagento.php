<?php
abstract class ProductToMagento
{
    const TAB = '    ';
    /**
     * The log file
     *
     * @var string
     */
    private static $_logFile = '';
    /**
     * The runner
     *
     * @param string $preFix
     * @param string $debug
     */
    public static function run($preFix = '', $debug = false)
    {

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
}