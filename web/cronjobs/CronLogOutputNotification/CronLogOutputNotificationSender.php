<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';

abstract class CronLogOutputNotificationSender
{
    const NEW_LINE = "\n";
    public static function run($debug = false)
    {
        $checkList = array('pricematchRunner.php' => '/tmp/pricematchRunner_{date}.log');
        $today = UDate::now()->format('d_M_y');
        if($debug === true)
            echo 'Started' . $today . self::NEW_LINE;
        foreach($checkList as $script => $outputFile) {
            $outputFilePath = str_replace('{date}', trim($today), $outputFile);
            if($debug === true)
                echo 'Trying: ' . $outputFilePath . self::NEW_LINE;
            if(is_file($outputFilePath)) {
                if($debug === true)
                    echo 'Emailling out the output file: ' . $outputFilePath . self::NEW_LINE;
                self::_mailOut($script, Asset::registerAsset(basename($outputFilePath), $outputFilePath, Asset::TYPE_TMP));
                if($debug === true)
                    echo 'Done ' . self::NEW_LINE;
            }
        }
        if($debug === true)
            echo self::NEW_LINE . self::NEW_LINE;
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