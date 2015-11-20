<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';
ini_set("memory_limit", "-1");
set_time_limit(0);

abstract class DataFeedImporter
{
    const TAB = '    ';
    /**
     * the reading dir
     *
     * @var unknown
     */
    private static $_readingDir = '';
    /**
     * the achived file dir
     *
     * @var unknown
     */
    private static $_outputFileDir = '';
    /**
     * The log file
     *
     * @var string
     */
    private static $_logFile = '';
    private static $_api = array('URL' => "http://192.168.1.7/api/", "token" => '');

    /**
     * The runner
     *
     * @param string $preFix
     * @param string $debug
     */
    public static function run($readingDir = '/tmp/', $outputFileDir = '/tmp/Archived/', $preFix = '', $debug = false)
    {
        $start = self::_log('## START ##############################', __CLASS__ . '::' . __FUNCTION__,  $preFix);

		self::$_readingDir = trim ($readingDir);
		self::_log('Reading the files from: ' . self::$_readingDir, '',  $preFix . self::TAB);
		self::$_outputFileDir = trim ($outputFileDir);
		self::_log('Achiving the files to: ' . self::$_outputFileDir, '',  $preFix . self::TAB);
        self::_log('');

		//set user
		self::_setRunningUser($preFix, $debug);

    	self::_log('== READ files under: ' . self::$_readingDir, '',  $preFix);
	    $files = glob(self::$_readingDir . '/*.json');
	    self::_log('Got files(' . count($files) . '): ' . implode(' ', $files), '',  $preFix . self::TAB);
	    foreach($files as $filePath) {
	        self::_log('');
            self::_importPerFile($filePath, $preFix . self::TAB, $debug);
	    }
        self::_log('');
        self::_log('## FINISH ##############################', __CLASS__ . '::' . __FUNCTION__,  $preFix, $start);
    }
    private static function _setRunningUser($preFix = '', $debug = false)
    {
        self::_log('== Set Running User : ', '',  $preFix);
    	Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
		self::_log('UserAccount(ID=' . Core::getUser()->getId() . ')', '',  $preFix . self::TAB);
    }
    private static function _importPerFile($filePath, $preFix = '', $debug = false)
    {
        $start = self::_log('-- Processing file: ' . $filePath, __CLASS__ . '::' . __FUNCTION__,  $preFix);
        try {
            if(!is_file($filePath))
                throw new Exception("Invalid file: " . $filePath);
            $productsJsonArry = json_decode(file_get_contents($filePath), true);
            foreach($productsJsonArry as $productJson) {
                self::_log('');
                self::_importPerLine($productJson, $preFix . self::TAB, $debug);
            }
            self::_log('');
            self::_zipFile($filePath, $preFix . self::TAB, $debug);
        } catch (Exception $ex) {
            self::_log('ERROR: ' . $ex->getMessage(), '',  $preFix . self::TAB);
            self::_log('Trace: ', '',  $preFix . self::TAB);
            self::_log(str_replace("\n", "\n" . $preFix . self::TAB, $ex->getTraceAsString()), '',  $preFix . self::TAB);
        }
        self::_log('-- DONE with file: ' . $filePath, __CLASS__ . '::' . __FUNCTION__,  $preFix, $start);
    }
    /**
     * Import via api
     *
     * @param array  $line
     * @param string $preFix
     * @param string $debug
     */
    private static function _importPerLine($line, $preFix = '', $debug = false)
    {
        $start = self::_log('++ Processing Line Data:', __CLASS__ . '::' . __FUNCTION__,  $preFix );
        self::_log('GOT data: ' . str_replace("\n", "\n" . $preFix . self::TAB, print_r($line, true)), '',  $preFix . self::TAB);
        if(!isset(self::$_api['URL']) || ($apiUrl = trim(self::$_api['URL'])) === '')
            throw new Exception('No API URL set!');
        if(!isset(self::$_api['token']) || ($token = trim(self::$_api['token'])) === '') {
            self::_log('!! no token yet, need to get token.', '',  $preFix . self::TAB);
            $url = $apiUrl . 'UserAccount/login';
            self::_log('CURL to url: ' . $url, '', $preFix . self::TAB. self::TAB);
            $data = json_encode(array('username' => Core::getUser()->getUserName(), 'password' => Core::getUser()->getPassword()));
            self::_log('With params: ' . $data, '', $preFix . self::TAB. self::TAB);
            $result = ComScriptCURL::readUrl(self::$_apiURL . '/Product/', null, $data);
            self::_log('Got Result: ', '', $preFix . self::TAB. self::TAB);
            self::_log(str_replace("\n", "\n" . $preFix . self::TAB . self::TAB . self::TAB, print_r($result, true)), '', $preFix . self::TAB . self::TAB . self::TAB);
            $result = json_decode($result, true);
            if(!isset($result['token']) || ($token = trim($result['token'])) === '')
                throw new Exception('Invalid token!');
            self::$_api['token'] = $token;
        }

        $url = $apiUrl . 'Product/';
        self::_log('CURL to url: ' . $url, '', $preFix . self::TAB);
        $data = $line;
        $data['token'] = self::$_api['token'];
        $result = ComScriptCURL::readUrl(self::$_apiURL . '/Product/', null, json_encode($data)); //, $customerRequest = '', $extraOpts = array())
        self::_log('Got Result: ', '', $preFix . self::TAB);
        self::_log(str_replace("\n", "\n" . $preFix . self::TAB . self::TAB, print_r($result, true)), '', $preFix . self::TAB . self::TAB);
        $result = json_decode($result, true);
        if(isset($result['token']) && ($token = trim($result['token'])) !== '') {
            self::$_api['token'] = $token;
        }
        self::_log('++ DONE', __CLASS__ . '::' . __FUNCTION__,  $preFix, $start);
    }
    /**
     * Archiving the file
     *
     * @param unknown $filePath
     * @param string $preFix
     * @param string $debug
     * @throws Exception
     */
    private static function _zipFile($filePath, $preFix = '', $debug = false)
    {
        $start = self::_log('== Archiving the file: ' . $filePath, __CLASS__ . '::' . __FUNCTION__,  $preFix );
        if(!is_file($filePath))
             throw new Exception("Invalid file: " . $filePath);
        $zip = new ZipArchive();
        $zipFilePath = self::$_outputFileDir . '/' . UDate::now()->format('Y_m_d') . '.zip';
        if ($zip->open($zipFilePath, ZipArchive::CREATE)!==TRUE) {
            throw new Exception("cannot open file<" . $zipFilePath . ">");
        }
        if($zip->addFile($filePath, '/' . basename($filePath) . '.' . UDate::now()->format('Y_m_d_H_i_s')) !== true)
            throw new Exception('Failed add file(' . $filePath . ') to zip file:' . $zipFilePath);
        self::_log('Add file: ' . $filePath, '', $preFix . self::TAB);
        self::_log('Zip file (' . $zipFilePath . ') are now:', '', $preFix . self::TAB);
        self::_log('- Contains: ' . $zip->numFiles . ' file(s)', '', $preFix . self::TAB . self::TAB);
        self::_log('- Status: ' . $zip->getStatusString(), '', $preFix . self::TAB . self::TAB);
        if($zip->close() !== true)
            throw new Exception('Failed to save the zip file:' . $zipFilePath);

        self::_log('REMOVING the orginal file: ' . $filePath, '', $preFix . self::TAB);
        unlink($filePath);
        self::_log('REMOVED', '', $preFix . self::TAB . self::TAB);

        self::_log('== Archived', __CLASS__ . '::' . __FUNCTION__,  $preFix, $start);
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
            $nowString = ' [' . trim($now) . '] ';
        $logMsg = $preFix . $msg . $nowString . $timeElapsed . ($funcName !== '' ? (' '  . $funcName . ' ') : '') . $postFix;
        echo $logMsg;
        if(is_file(self::$_logFile))
            file_put_contents(self::$_logFile, $logMsg, FILE_APPEND);
        return $now;
    }
}
echo "<pre/>";
DataFeedImporter::run();