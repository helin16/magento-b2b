<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';

abstract class CronLogOutputNotificationSender
{
    public static function run($debug = false)
    {
        $checkList = array('pricematchRunner.php' => '/tmp/pricematchRunner_{date}.log');
        $today = UDate::now()->format('d_b_y');
        if($debug === true)
            echo $today;
        foreach($checkList as $script => $outputFile)
        {
            $outputFilePath = str_replace('{date}', trim($today), $outputFile);
            if(is_file($outputFilePath))
                self::_mailOut($script, Asset::registerAsset(basename($outputFilePath), $outputFilePath, Asset::TYPE_TMP));
        }
    }
    /**
     * Mailing the file out to someone
     *
     * @param unknown $filePath
     */
    private static function _mailOut($title, Asset $asset = null)
    {
        $assets = array();
        if($asset instanceof Asset)
            $assets[] = $asset;
        $class = get_called_class();

        $emailTos = array('helin16@gmail.com', 'sales@budgetpc.com.au');
        $body = "Script output for: " . $title;

        foreach ($emailTos as $emailTo)
            EmailSender::addEmail('', $emailTo, $title, $body, $assets);
    }
}

CronLogOutputNotificationSender::run(true);