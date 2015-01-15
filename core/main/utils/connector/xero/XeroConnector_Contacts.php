<?php
class XeroConnector_Contacts extends XeroConnectorAbstract
{
	/**
	 * Getting a fake xml element for product
	 *
	 * @param unknown $code Item code
	 * @param unknown $description Item description
	 * @param unknown $salesUnitPrice Item unit price for sales
	 * @param unknown $salesAccountCode Account code for sales
	 * @param unknown $purchaseTaxType Tax type for PO
	 * @param unknown $purchaseUnitPrice Unit price for PO
	 * @param unknown $purchaseAccountCode Account code for PO
	 *
	 * @return SimpleXMLElement
	 */
	private function _getFakeXml(array $params) {
		print_r($params);
		$xml = $this->getXML()->addChild('Contact');
		// compulsory filed(s)
		if(empty($params['name']))
			throw new Exception('Name is required for Contact');
		$xml->Name = $params['name'];
		// optional field(s)
		// FirstName, LastName, EmailAddress
		if(!empty($params['firstname']))
			$xml->FirstName = $params['firstname'];
		if(!empty($params['lastname']))
			$xml->LastName = $params['lastname'];
		if(!empty($params['email']))
			$xml->EmailAddress = $params['email'];
		// Address
		if(!empty($params['addresses']) && count($params['addresses']))
		{
			$addressesXML = $xml->addChild('Addresses');
			foreach ($params['addresses'] as $address)
			{
				$addressXML = $addressesXML->addChild('Address');
				// AddressType
				if(!empty($address['addressType']))
				{
					$addressXML->addChild('AddressType', $address['addressType']);
					// AddressLine(s)
					$lineCount = 1;
					foreach ($address['addressLines'] as $addressLine)
					{
						$addressXML->addChild(('AddressLine' . $lineCount), $addressLine);
						$lineCount++;
					}
					unset($lineCount);
					// City
					if(!empty($address['city']))
						$addressXML->City = $address['city'];
					// PostalCode
					if(!empty($address['postcode']))
						$addressXML->PostalCode = $address['postcode'];
				}
			}
			// BankAccountDetails, TaxNumber, AccountsReceivableTaxType, AccountsPayableTaxType, DefaultCurrency
			if(!empty($params['bankAccountDetails']))
				$xml->BankAccountDetails = $params['bankAccountDetails'];
			if(!empty($params['taxNumber']))
				$xml->TaxNumber = $params['taxNumber'];
			if(!empty($params['accountsReceivableTaxType']))
				$xml->AccountsReceivableTaxType = $params['accountsReceivableTaxType'];
			if(!empty($params['accountsPayableTaxType']))
				$xml->AccountsPayableTaxType = $params['accountsPayableTaxType'];
			if(!empty($params['defaultCurrency']))
				$xml->DefaultCurrency = $params['defaultCurrency'];
		}
		return $xml;
	}
	/**
	 * Getting the items
	 *
	 * @param unknown $params
	 *
	 * @throws Exception
	 * @return SimpleXMLElement|null
	 */
	private function getContacts($params = array())
	{
		$auth = $this->_getOAuth();
		$auth->request('GET', $auth->url('Contacts', 'core'), $params);
		if (intval($auth->response['code']) !== self::RESPONSE_CODE_SUCCESS)
			throw new Exception('Error' .  $auth->response['response']);
		$response = $auth->parseResponse($auth->response['response'], $auth->response['format']);
		return isset($response->Items) ? $response->Items : null;
	}
	/**
	 * create/update the items
	 *
	 * @param unknown $params
	 * @param unknown $code Item code
	 * @param unknown $description Item description
	 * @param unknown $salesUnitPrice Item unit price for sales
	 * @param unknown $salesAccountCode Account code for sales
	 * @param unknown $purchaseTaxType Tax type for PO
	 * @param unknown $purchaseUnitPrice Unit price for PO
	 * @param unknown $purchaseAccountCode Account code for PO
	 *
	 * @throws Exception
	 * @return SimpleXMLElement|null
	 */
	private function updateContact($params = array())
	{
		$auth = $this->_getOAuth();
			$xml = $this->_getFakeXml($params);
			var_dump($xml);
			$xml = $this->_removeXMLHeader($xml);
			$auth->request('POST', $auth->url('Contacts', 'core'), $params, $xml);
			var_dump($auth->response);
			if (intval($auth->response['code']) !== self::RESPONSE_CODE_SUCCESS)
				throw new Exception('Error' .  $auth->response['response']);
			$response = $auth->parseResponse($auth->response['response'], $auth->response['format']);
		return isset($response->Contacts) ? $response->Contacts->Contact : null;
	}
	/**
	 * create the item (public)
	 *
	 * @param unknown $params
	 * @param unknown $code Item code
	 * @param unknown $description Item description
	 * @param unknown $salesUnitPrice Item unit price for sales
	 * @param unknown $salesAccountCode Account code for sales
	 * @param unknown $purchaseTaxType Tax type for PO
	 * @param unknown $purchaseUnitPrice Unit price for PO
	 * @param unknown $purchaseAccountCode Account code for PO
	 *
	 * @throws Exception
	 * @return SimpleXMLElement|null
	 */
	public function create($params = array())
	{
		return $this->updateContact($params);
	}
	/**
	 * update the item (public)
	 *
	 * @param unknown $params
	 * @param unknown $code Item code
	 * @param unknown $description Item description
	 * @param unknown $salesUnitPrice Item unit price for sales
	 * @param unknown $salesAccountCode Account code for sales
	 * @param unknown $purchaseTaxType Tax type for PO
	 * @param unknown $purchaseUnitPrice Unit price for PO
	 * @param unknown $purchaseAccountCode Account code for PO
	 *
	 * @throws Exception
	 * @return SimpleXMLElement|null
	 */
	public function update($params)
	{
		if(!count($params))
			throw new Exception('Nothing to update!');
		return $this->updateContact($params);
	}
	/**
	 * Getting all items
	 *
	 * @throws Exception
	 * @return SimpleXMLElement|null
	 */
	public function getAll()
	{
		return $this->getContacts(array());
	}
}