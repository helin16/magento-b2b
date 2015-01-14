<?php
class XeroConnector_Account extends XeroConnectorAbstract
{
	private $_xmlType = "account";
	
	/**
	 * Getting the Accounts
	 *
	 * @param unknown $params
	 *
	 * @throws Exception
	 * @return SimpleXMLElement|null
	 */
	public function getAccounts($params = array(), $glue = "AND")
	{
		$auth = $this->_getOAuth();
		
		$params = mapQueryParam($params, $glue);
		
		$auth->request('GET', $auth->url('Accounts', 'core'), $params);
		if (intval($auth->response['code']) !== self::RESPONSE_CODE_SUCCESS)
			throw new Exception('Error' .  $auth->response['response']);
		
		$response = $auth->parseResponse($auth->response['response'], $auth->response['format']);
		
		return isset($response->Accounts) ? $response->Accounts : null;
	}
	
	private function updateAccounts($params = array(), $input)
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
	
	/**
	 * 
	 * @param unknown $params
	 * @param string $glue
	 * @return multitype:string
	 */
	private function mapQueryParam($params = array(), $glue = "AND")
	{
		$mainCriteria = $output = array();
		if(is_array($params) && isset($params['where']) && count($params['where']) > 0)
		{
			foreach($param['where'] as $key => $value)
				$mainCriteria[] = trim($key)." ".trim($value['operator'])." ".trim($value['value']);
			
			$output['Where'] = implode(" ".trim($glue)." ", $mainCriteria);
		}
		
		if(is_array($params) && isset($params['order']))
			$output['Order'] = trim($params['order']);
		
		return $output;
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