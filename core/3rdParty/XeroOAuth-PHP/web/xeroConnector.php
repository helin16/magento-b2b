<?php
require dirname(__FILE__). '/../../../main/bootstrap.php';
class xeroConnector
{ 
	private static function _getOAuth()
	{
		$useragent = "XeroOAuth-PHP Private App Test";
		define ( 'BASE_PATH', XeroOAuth::getBasePath() );
		define ( "XRO_APP_TYPE", "Private" );
		
		$signatures = array (
				'consumer_key' => 'AYLWASFOHUTMKN5KKVG3OSMQTX4ERK',
				'shared_secret' => 'A5CIIOWLOTY1516WFQEARSHUZKLXW9',
				// API versions
				'core_version' => '2.0',
				// 		'payroll_version' => '1.0',
				'file_version' => '1.0'
		);
		
		if (XRO_APP_TYPE == "Private" || XRO_APP_TYPE == "Partner") {
			$signatures ['rsa_private_key'] = BASE_PATH . '/certs/privatekey.pem';
			$signatures ['rsa_public_key'] = BASE_PATH . '/certs/publickey.cer';
		}
		
		$XeroOAuth = new XeroOAuth ( array_merge ( array (
				'application_type' => XRO_APP_TYPE,
				// 		'oauth_callback' => OAUTH_CALLBACK,
				'user_agent' => $useragent
		), $signatures ) );
		
		self::initialCheck($XeroOAuth);
		return $XeroOAuth;
	}
	public static function getItem()
	{
		$XeroOAuth = self::_getOAuth();
		$session = self::getSession($XeroOAuth);
		$oauthSession = self::retrieveSession ();
		$XeroOAuth = self::setToken($oauthSession, $XeroOAuth);
		
		$items = array();
		
		$xml = "
			<Item>
// 				<ItemID>9a59ea90-942e-484d-9b71-d00ab607e03b</ItemID>
				<Code>ITEM-CODE-01</Code>
// 				<Description>2011 Merino Sweater - LARGE</Description>
// 				<PurchaseDetails>
// 					<UnitPrice>149.0000</UnitPrice>
// 					<AccountCode>300</AccountCode>
// 				</PurchaseDetails>
// 				<SalesDetails>
// 				    <UnitPrice>299.0000</UnitPrice>
// 				    <AccountCode>200</AccountCode>
// 				</SalesDetails>
			</Item>
        	";
		$response = $XeroOAuth->request('GET', $XeroOAuth->url('Items', 'core'), array("Code" => "sadfsdf",));
		if ($XeroOAuth->response['code'] == 200) {
			$items = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);
		} else {
			xeroConnector::outputError($XeroOAuth);
		}
		
		return $items;
	}
	private static function setToken ($oauthSession, $XeroOAuth)
	{
		if (isset ( $oauthSession ['oauth_token'] )) {
			$XeroOAuth->config ['access_token'] = $oauthSession ['oauth_token'];
			$XeroOAuth->config ['access_token_secret'] = $oauthSession ['oauth_token_secret'];
		}
		return $XeroOAuth;
	}
	public static function outputError($XeroOAuth)
	{
	    echo 'Error: ' . $XeroOAuth->response['response'] . PHP_EOL;
	    self::pr($XeroOAuth);
	}
	/**
	 * Debug function for printing the content of an object
	 *
	 * @param mixes $obj
	 */
	public static function pr($obj)
	{
	
		if (!self::is_cli())
			echo '<pre style="word-wrap: break-word">';
		if (is_object($obj))
			print_r($obj);
		elseif (is_array($obj))
		print_r($obj);
		else
			echo $obj;
		if (!self::is_cli())
			echo '</pre>';
	}
	private static function is_cli()
	{
		return (PHP_SAPI == 'cli' && empty($_SERVER['REMOTE_ADDR']));
	}
	private static function initialCheck($XeroOAuth)
	{
		$initialCheck = $XeroOAuth->diagnostics ();
		$checkErrors = count ( $initialCheck );
		if ($checkErrors > 0) {
			// you could handle any config errors here, or keep on truckin if you like to live dangerously
			$message = '';
			foreach ( $initialCheck as $check ) {
				$message .= 'Error: ' . $check . PHP_EOL;
			}
			throw new Exception($message, $code, $previous);
		}
	}
	private static function getSession($XeroOAuth){
		$session = self::persistSession ( array (
			'oauth_token' => $XeroOAuth->config ['consumer_key'],
			'oauth_token_secret' => $XeroOAuth->config ['shared_secret'],
			'oauth_session_handle' => '' 
		) );
		return $session;
	}
	private static function persistSession($response)
	{
		if (isset($response)) {
			$_SESSION['access_token']       = $response['oauth_token'];
			$_SESSION['oauth_token_secret'] = $response['oauth_token_secret'];
			if(isset($response['oauth_session_handle']))  $_SESSION['session_handle']     = $response['oauth_session_handle'];
		} else {
			return false;
		}
	}
	/**
	 * Retrieve the OAuth access token and session handle
	 * In my example I am just using the session, but in real world, this is should be a storage engine
	 *
	 */
	private static function retrieveSession()
	{
		if (isset($_SESSION['access_token'])) {
			$response['oauth_token']            =    $_SESSION['access_token'];
			$response['oauth_token_secret']     =    $_SESSION['oauth_token_secret'];
			$response['oauth_session_handle']   =    $_SESSION['session_handle'];
			
			if (isset ( $response ['oauth_token'] )) {
				$XeroOAuth->config ['access_token'] = $response ['oauth_token'];
				$XeroOAuth->config ['access_token_secret'] = $response ['oauth_token_secret'];
			}
			
			return $response;
		} else {
			return false;
		}
	}
}

echo '<pre>';
$items = xeroConnector::getItem();

var_dump($items);


die;
