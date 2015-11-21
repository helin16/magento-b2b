<?php
class APIService
{
    const TAB = "\t";
    const TOKEN_HEADER_KEY = 'MAGE_API';
    private $_realm = '';
    private $_debug;
    private $_logFile;
    private $_codes = array(
            '100' => 'Continue',
            '200' => 'OK',
            '201' => 'Created',
            '202' => 'Accepted',
            '203' => 'Non-Authoritative Information',
            '204' => 'No Content',
            '205' => 'Reset Content',
            '206' => 'Partial Content',
            '300' => 'Multiple Choices',
            '301' => 'Moved Permanently',
            '302' => 'Found',
            '303' => 'See Other',
            '304' => 'Not Modified',
            '305' => 'Use Proxy',
            '307' => 'Temporary Redirect',
            '400' => 'Bad Request',
            '401' => 'Unauthorized',
            '402' => 'Payment Required',
            '403' => 'Forbidden',
            '404' => 'Not Found',
            '405' => 'Method Not Allowed',
            '406' => 'Not Acceptable',
            '409' => 'Conflict',
            '410' => 'Gone',
            '411' => 'Length Required',
            '412' => 'Precondition Failed',
            '413' => 'Request Entity Too Large',
            '414' => 'Request-URI Too Long',
            '415' => 'Unsupported Media Type',
            '416' => 'Requested Range Not Satisfiable',
            '417' => 'Expectation Failed',
            '500' => 'Internal Server Error',
            '501' => 'Not Implemented',
            '503' => 'Service Unavailable'
    );
    /**
     * The constructor.
     *
     * @param string $mode The mode, either debug or production
     */
    public function  __construct($debug = false, $realm = 'Rest Server', $logfile = null)
    {
        $this->_realm = $realm;
        $this->_debug = $debug;
        if(is_file($logfile))
            $this->_logFile = $logfile;
        else
            $this->_logFile = '/tmp/' . get_class($this) . '.log';
    }
    /**
     * Run
     */
    public function run()
    {
  		try
  		{
  		    if(!is_file($this->_logFile)) {
  		        file_put_contents($this->_logFile, '');
  		        $this->log('Init log file: ' . $this->_logFile, __CLASS__ . '::' . __FUNCTION__, '>>>>');
  		    }
  		    $this->log('');
  		    $this->log('');
  		    $this->log('');
  		    $this->log('== Start Service =============', __CLASS__ . '::' . __FUNCTION__);

            $requestType = strtolower(trim($_SERVER['REQUEST_METHOD']));
            if(!isset($_SERVER['PATH_INFO'])) {
                throw new Exception('INVALID URL!');
            }
            $request = explode("/", substr($_SERVER['PATH_INFO'], 1));
            $entityName = isset($request[0]) ? ucfirst(trim(array_shift($request))) : '';

  		    $serivceName = 'API' . $entityName . 'Service';
  		    $this->log('Calling Service: ' . $serivceName);

  		    $requestedMethod = ((isset($request[0]) && trim($request[0]) !== '') ? trim(array_shift($request)) : 'all');
  		    if(preg_match('/^\d+$/', $requestedMethod))
  		    {
  		        $request['entityId'] = $requestedMethod;
  		        $requestedMethod = 'id';
  		    }
  		    $request = array_merge($request, $_REQUEST);
  		    //capture the post and put body
  		    $msgBody = file_get_contents("php://input");
  		    if(trim($msgBody) !== '' && is_array($msgArr = json_decode($msgBody, true))) {
  		        $request = array_merge($msgArr, $_REQUEST);
  		    }
  		    if(strtolower($requestedMethod) !== 'login'){
  		        $token = $this->_getTokenFromHeader();
  		        $this->log('validating token: "' . $token . '"');
  		        $this->_validateToken($token);
  		    }

  		    $service = new $serivceName($this);
  		    $method = $requestType . '_' . $requestedMethod;
  		    if(!method_exists($service, $method))
  		        throw new Exception('No such a method: ' . $requestedMethod . ' for ' . $requestType . '!');
  		    $results = array();
  		    $this->log(preg_replace("/[\n\r]/", "\n" . self::TAB . self::TAB . self::TAB, 'Try calling: ' . $serivceName . '::' . $method . " with params: \n"  .print_r($request, true)), __CLASS__ . '::' . __FUNCTION__);
  		    $data = $service->$method($request);
  		    if($data instanceof BaseEntityAbstract)
  		        $results = array_merge($results, $data->getJson());
  		    else if(is_array($data))
  		        $results = array_merge($results, $data);
  		    $results['token'] = $this->_getToken();
  		    $this->setStatus(200)
  		        ->sendData($results);
  		}
  		catch (Exception $ex)
  		{
  		    $this->log('!!! Exception : ' . $ex->getMessage());
  		    $this->log(preg_replace("/[\n\r]/", "\n" . self::TAB . self::TAB . self::TAB, "Trace:\n" . $ex->getTraceAsString()));
  		    $statusCode = $ex->getCode();
  		    $error = array('code' => $statusCode, 'message' => $ex->getMessage());
  		    if($this->_debug === true)
  		        $error['trace'] = $ex->getTraceAsString();
  		    $response = array('error' => $error);
  		    $this->setStatus($statusCode)
  		        ->sendData($response);
  		}
  		$this->log('== End Service =====================', __CLASS__ . '::' . __FUNCTION__);
    }
    /**
     * Getting the token from the header
     *
     * @return string
     */
    private function _getTokenFromHeader()
    {
        foreach (getallheaders() as $name => $value) {
            if(trim($name) === self::TOKEN_HEADER_KEY)
                return trim($value);
        }
        return '';
    }
    /**
     * sending the response back
     *
     * @param unknown $data
     */
    public function sendData($data)
    {
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: 0");
        header('Content-Type: application/json');
        if (is_object($data) && method_exists($data, '__keepOut')) {
            $data = clone $data;
            foreach ($data->__keepOut() as $prop) {
                unset($data->$prop);
            }
        }
        $options = 0;
        if ($this->_debug === true) {
            $options = JSON_PRETTY_PRINT;
        }
        $result = json_encode($data, $options);
        $this->log(preg_replace("/[\n\r]/", "\n" . self::TAB . self::TAB . self::TAB, "Sending RESULT back : \n" . $result), __CLASS__ . '::' . __FUNCTION__);
        echo $result;
        return $this;
    }
    /**
     * Setting the reponse status
     *
     * @param unknown $code
     */
    public function setStatus($code)
    {
        if (function_exists('http_response_code')) {
            http_response_code($code);
        } else {
            $protocol = $_SERVER['SERVER_PROTOCOL'] ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
            $code .= ' ' . $this->_codes[strval($code)];
            header("$protocol $code");
        }
        return $this;
    }
    /**
     * getting the token key for encryption
     *
     * @return string
     */
    private function _getTokenKey()
    {
        return pack('H*', md5(get_class($this) . ' is awesome!?$'));
    }
    /**
     * Getting the token size for encryption
     *
     * @return number
     */
    private function _getTokenVISize()
    {
        return mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
    }
    /**
     * getting the token period
     *
     * @return string
     */
    private function _getTokenValidPeriod()
    {
        return '30 minute';
    }
    /**
     * Getting the token
     *
     * @throws Exception
     * @return string
     */
    private function _getToken()
    {
        if(!Core::getUser() instanceof UserAccount)
            throw new Exception('Please login first to get the token.');
        $key = $this->_getTokenKey();
        $iv_size = $this->_getTokenVISize();
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $plaintext = Core::getUser()->getId() . '|' . trim(UDate::now()) . '|' . trim(UDate::now()->modify('+' . $this->_getTokenValidPeriod())) . '|';
        $ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $plaintext, MCRYPT_MODE_CBC, $iv);
        $ciphertext = $iv . $ciphertext;
        return base64_encode($ciphertext);
    }
    /**
     * validates the token
     *
     * @param unknown $token
     * @param bool    $showHeader
     *
     * @throws Exception
     * @return APIService
     */
    private function _validateToken($token, $showHeader = false)
    {
        if ($showHeader) {
            header("WWW-Authenticate: Basic realm=\"" . $this->_realm . "\"");
        }
        if(($token = trim($token)) === '')
            throw new Exception('Invalid access, please login first!', 401);

        $key = $this->_getTokenKey();
        $ciphertext_dec = base64_decode($token);
        $iv_size = $this->_getTokenVISize();
        $iv_dec = substr($ciphertext_dec, 0, $iv_size);
        $ciphertext_dec = substr($ciphertext_dec, $iv_size);

        $plaintext_dec = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);
        $this->log('decrypted token: "' . $plaintext_dec . '"', __CLASS__ . '::' . __FUNCTION__, '## ');

        $information = explode('|', $plaintext_dec);
        $this->log('got information: "' . preg_replace("/[\n\r]/", " ", print_r($information, true)), __CLASS__ . '::' . __FUNCTION__, self::TAB);

        if(!isset($information[1]) || preg_match('/^\d{4}-\d{2}-\d{2}\ \d{2}:\d{2}:\d{2}$/', ($fromDate = trim($information[1]))) !== 1 ) {
            $this->log('invalid fromDate!', '', self::TAB);
            throw new Exception('Invalid token, please login first!');
        }
        $fromDate = new UDate($fromDate);
        $this->log('Got fromDate: ' . $fromDate, '', self::TAB);

        if(!isset($information[2]) || preg_match('/^\d{4}-\d{2}-\d{2}\ \d{2}:\d{2}:\d{2}$/', ($toDate = trim($information[2]))) !== 1 ) {
            $this->log('invalid toDate!', '', self::TAB);
            throw new Exception('Invalid token, please login first!!');
        }
        $toDate = new UDate($toDate);
        $this->log('Got toDate: ' . $toDate, '', self::TAB);

        $now = UDate::now();
        $this->log('Got NOW: ' . $now, '', self::TAB);
        if($now->after($toDate) || $now->before($fromDate)) {
            $this->log('Token expired.', '', self::TAB);
            throw new Exception('Token expired.');
        }
        if(!isset($information[0]) || !($userAccount = UserAccount::get(trim($information[0]))) instanceof UserAccount) {
            $this->log('Invalid useraccount.', '', self::TAB);
            throw new Exception('Invalid token, please login first.');
        }
        $role = null;
        if(count($roles = $userAccount->getRoles()) > 0)
            $role = $roles[0];
        $this->log('Got User: ' . $userAccount->getId(), '', self::TAB);
        Core::setUser($userAccount, $role);
        return $this;
    }
    /**
     * log all the messages
     *
     * @param string $msg
     * @param string $funcName
     * @param string $preFix
     * @param string $postFix
     *
     * @return APIService
     */
    public function log($msg, $funcName='', $preFix ='', $postFix = "\n")
    {
        $log = ((trim($msg) === '') ? '' : (UDate::now() . ': ')) . $preFix . $msg . ($funcName === '' ? '' : (' [' . $funcName . '] ')) . $postFix;
        if(is_file($this->_logFile))
            file_put_contents($this->_logFile, $log, FILE_APPEND);
        return $this;
    }

}