<?php
class APIService
{
    /**
     * Run
     */
    public function run()
    {
  		$results = $errors = array();
  		try
  		{
            $requestType = strtolower(trim($_SERVER['REQUEST_METHOD']));
            $request = explode("/", substr($_SERVER['PATH_INFO'], 1));
            $entityName = isset($request[0]) ? ucfirst(trim(array_shift($request))) : '';

  		    $serivceName = 'API' . $entityName . 'Service';
  		    $requestedMethod = ((isset($request[0]) && trim($request[0]) !== '') ? trim(array_shift($request)) : 'all');
  		    if(preg_match('/^\d+$/', $requestedMethod))
  		    {
  		        $request['entityId'] = $requestedMethod;
  		        $requestedMethod = 'id';
  		    }
  		    $request = array_merge($request, $_REQUEST);

  		    if(strtolower($requestedMethod) !== 'login')
  		        $this->_validateToken(isset($request['token']) ? trim($request['token']) : '');

  		    $service = new $serivceName();
  		    $method = $requestType . '_' . $requestedMethod;
  		    if(!method_exists($service, $method))
  		        throw new Exception('No such a method: ' . $method . '!');
  		    $results = $service->$method($request);
  		    $results['token'] = $this->_getToken();
  		}
  		catch (Exception $ex)
  		{
  		    $errors[] = $ex->getMessage();
  		}
  		header('Content-Type: application/json');
  		echo StringUtilsAbstract::getJson($results, $errors);
    }

    private function _getTokenKey()
    {
        return pack('H*', md5(get_class($this) . ' is awesome!?$'));
    }
    private function _getTokenVISize()
    {
        return mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
    }
    private function _getTokenValidPeriod()
    {
        return '30 minute';
    }

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

    private function _validateToken($token)
    {
        if(($token = trim($token)) === '')
            throw new Exception('Invalid access, please login first!');

        $key = $this->_getTokenKey();
        $ciphertext_dec = base64_decode($token);
        $iv_size = $this->_getTokenVISize();
        $iv_dec = substr($ciphertext_dec, 0, $iv_size);
        $ciphertext_dec = substr($ciphertext_dec, $iv_size);

        $plaintext_dec = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);
        $information = explode('|', $plaintext_dec);
        if(!isset($information[1]) || preg_match('/^\d{4}-\d{2}-\d{2}\ \d{2}:\d{2}:\d{2}$/', ($fromDate = trim($information[1]))) !== 1 )
            throw new Exception('Invalid token!');
        $fromDate = new UDate($fromDate);
        if(!isset($information[2]) || preg_match('/^\d{4}-\d{2}-\d{2}\ \d{2}:\d{2}:\d{2}$/', ($toDate = trim($information[2]))) !== 1 )
            throw new Exception('Invalid token!!');
        $toDate = new UDate($toDate);
        if(UDate::now()->after($toDate) || UDate::now()->before($fromDate))
            throw new Exception('Invalid token!!!');
        if(!isset($information[0]) || !($userAccount = UserAccount::get(trim($information[0]))) instanceof UserAccount)
            throw new Exception('Invalid token.');
        $role = null;
        if(count($roles = $userAccount->getRoles()) > 0)
            $role = $roles[0];
        Core::setUser($userAccount, $role);
        return $this;
    }

}