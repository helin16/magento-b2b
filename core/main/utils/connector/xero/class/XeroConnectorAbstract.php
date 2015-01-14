<?php
abstract class XeroConnectorAbstract
{
	const CUSTOMER_KEY = 'AYLWASFOHUTMKN5KKVG3OSMQTX4ERK';
	const CUSTOMER_SECRET = 'A5CIIOWLOTY1516WFQEARSHUZKLXW9';
	const RESPONSE_CODE_SUCCESS = 200;
	/**
	 * XeroOAuth object from 3rdParty
	 * @var XeroOAuth
	 */
	protected $_oauth = null;
	/**
	 * XeroConnector object
	 * 
	 * @var XeroConnector
	 */
	private static $_connector;
	/**
	 * constructor
	 */
	public function __construct()
	{
		$this->_getOAuth();
	}
	/**
	 * Getting XeroConnector from the cache
	 * 
	 * @return XeroConnector
	 */
	public static function get()
	{
		$class = get_called_class();
		if(!isset(self::$_connector[$class]) || !self::$_connector[$class] instanceof $class)
			self::$_connector[$class] = new $class();
		return self::$_connector[$class];
	}
	/**
	 * Getting the XeroOAuth object from 3rdParty
	 * 
	 * @return XeroOAuth
	 */
	protected function _getOAuth()
	{
		if($this->_oauth instanceof XeroOAuth)
			return $this->_getSession()->_oauth;
		$basePath = trim( XeroOAuth::getBasePath() );
		$signatures = array (
				'consumer_key' => self::CUSTOMER_KEY,
				'shared_secret' => self::CUSTOMER_SECRET,
				// API versions
				'core_version' => '2.0',
				'file_version' => '1.0',
				'rsa_private_key' => $basePath . '/certs/privatekey.pem',
				'rsa_public_key' => $basePath . '/certs/publickey.cer',
		);
		$this->_oauth = new XeroOAuth ( array_merge ( array (
				'application_type' => "Private",
				'user_agent' => "XeroOAuth-PHP Private App Test"
		), $signatures ) );
		$this->_setSession();
		return $this->_oauth;
	}
	/**
	 * holding the token and other information into the session
	 * 
	 * @return XeroConnector
	 */
	private function _setSession()
	{
		$_SESSION['xeroconnector'] = array(
			'access_token'       => $this->_oauth->config ['consumer_key'],
			'oauth_token_secret' => $this->_oauth->config ['shared_secret'],
			'session_handle'     => isset($this->_oauth->config ['oauth_session_handle']) ? $this->_oauth->config ['oauth_session_handle'] : ''
		);
		return $this;
	}
	/**
	 * retrieving and reset XeroOAuth's config
	 *  
	 * @return XeroConnector
	 */
	private function _getSession()
	{
		$session = $_SESSION['xeroconnector'];
		if (isset ( $session['access_token'])) {
			$this->_oauth->config ['access_token'] = $session['access_token'];
			$this->_oauth->config ['access_token_secret'] = $session['oauth_token_secret'];
			$this->_oauth->config ['oauth_session_handle'] = $session['session_handle'];
		}
		return $this;
	}
	
	protected function _constructXML($xmlType, $input)
	{
		$xmlType = strtolower(trim($xmlType));
		$xml = false;
		
		if($xmlType == "item")
		{
			if(!is_array($input) || !isset($input['code']) || trim($input['code']) === '')
				throw new Exception("Mandatory element(s) missing for constructing XML for ITEM");
			
			$xml = new SimpleXMLElement ( '<' . 'Item' . '/>' );
			$xml->Code = trim($input['code']);
			
			if(isset($input['description']) && ($description = trim($input['description'])) !== '')
				$xml->Description = $description;
			
			if(!empty($input['salesUnitPrice']) && !empty($input['salesAccountCode']))
			{
				$xml->SalesDetails->UnitPrice = $input['salesUnitPrice'];
				$xml->SalesDetails->AccountCode = $input['salesAccountCode'];
			}
			if(!empty($input['purchaseUnitPrice']) && !empty($input['purchaseAccountCode']))
			{
				$xml->PurchaseDetails->UnitPrice = $input['purchaseUnitPrice'];
				$xml->PurchaseDetails->AccountCode = $input['purchaseAccountCode'];
				if(!empty($input['purchaseTaxType']))
					$xml->PurchaseDetails->TaxType = $input['purchaseTaxType'];
			}
		}
		else if($xmlType == "account")
		{
			if(!is_array($input) || !isset($input['code']) || trim($input['code']) === '' || 
			   !isset($input['name']) || trim($input['name']) === '' ||
			   !isset($input['type']) || trim($input['type']) === '')
			{
				throw new Exception("Mandatory element(s) missing for constructing XML for ACCOUNT");
			}
			
			$xml = new SimpleXMLElement("<Account></Account>");
			$xml->Code = trim($input['code']);
			$xml->Name = trim($input['name']);
			$xml->Type = trim($input['type']);
				
			if(isset($input['description']) && ($description = trim($input['description'])) !== '')
				$xml->Description = $description;
				
			if(isset($input['tax_type']) && ($taxType = trim($input['tax_type'])) !== '')
				$xml->TaxType = $taxType;

			if(isset($input['enable_payments_to_account']) && is_bool($input['enable_payments_to_account']))
				$xml->EnablePaymentsToAccount = $input['enable_payments_to_account'];
			
			if(isset($input['show_in_expense_claims']) && is_bool($input['show_in_expense_claims']))
				$xml->ShowInExpenseClaims = $input['show_in_expense_claims'];
		}
		
		return $xml;
	}
	
	protected function _removeXMLHeader(SimpleXMLElement $xml)
	{
		$dom = dom_import_simplexml($xml);
		return $dom->ownerDocument->saveXML($dom->ownerDocument->documentElement);
	}
}


?>