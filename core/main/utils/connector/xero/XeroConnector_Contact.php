<?php
class XeroConnector_Contact extends XeroConnectorAbstract
{
	private $_xmlType = "contact";
	
	/**
	 * Getting the Accounts
	 *
	 * @param unknown $params
	 *
	 * @throws Exception
	 * @return SimpleXMLElement|null
	 */
	public function getContacts($params = array())
	{
		$auth = $this->_getOAuth();
		
		$auth->request('GET', $auth->url('Contacts', 'core'), $params);
		if (intval($auth->response['code']) !== self::RESPONSE_CODE_SUCCESS)
			throw new Exception('Error' .  $auth->response['response']);
		
		$response = $auth->parseResponse($auth->response['response'], $auth->response['format']);
		
		return isset($response->Accounts) ? $response->Accounts : null;
	}
	
	private function _updateAccounts($params = array(), array $input = array())
	{
		$auth = $this->_getOAuth();
		
		$xml = self::getXML($this->_xmlType, $input);
		$xml = $this->_removeXMLHeader($xml);
		
		$auth->request('POST', $auth->url('Accounts', 'core'), $params, $xml);
		if (intval($auth->response['code']) !== self::RESPONSE_CODE_SUCCESS)
			throw new Exception('Error' .  $auth->response['response']);
		$response = $auth->parseResponse($auth->response['response'], $auth->response['format']);
		
		return isset($response->Accounts) ? $response->Items : null;
	}
	
	public function createAccount($params = array(), array $input = array())
	{
		return $this->_updateAccounts($params, $input);
	}
	
	public function updateAccount($params = array(), array $input = array())
	{
		return $this->_updateAccounts($params, $input);
	}
	
	/**
	 * get the xml for that entity
	 * 
	 * @param array $input
	 * 
	 * @return SimpleXMLElement
	 */
	public static function getXML(array $input = array())
	{
		if(!isset($input['code']) || trim($input['code']) === '' || !isset($input['name']) || trim($input['name']) === '' || !isset($input['type']) || trim($input['type']) === '')
			throw new Exception("Mandatory element(s) missing for constructing XML for ACCOUNT");
			
		$xml = parent::getXML();
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
		return $xml;
	}
}