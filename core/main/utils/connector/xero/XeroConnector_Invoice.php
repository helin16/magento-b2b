<?php
class XeroConnector_Invoice extends XeroConnectorAbstract
{
	private $_invoiceTypes = array('ACCPAY', 'ACCREC');
	private $_lineAmountTypes = array('Exclusive', 'Inclusive', 'NoTax');
	private $_defaultLineAmountType = 'Exclusive';
	
	/**
	 * Getting the invoices
	 *
	 * @param unknown $params
	 *
	 * @throws Exception
	 * @return SimpleXMLElement|null
	 */
	public function getInvoices($params = array())
	{
		$auth = $this->_getOAuth();
		$auth->request('GET', $auth->url('Invoices', 'core'), $params);
		if (intval($auth->response['code']) !== self::RESPONSE_CODE_SUCCESS)
			throw new Exception('Error' .  $auth->response['response']);
		$response = $auth->parseResponse($auth->response['response'], $auth->response['format']);
		return isset($response->Invoices) ? $response->Invoices : null;
	}
	
	private function _updateInvoices($params = array(), array $input = array())
	{
		$auth = $this->_getOAuth();
		
		self::getXML($input);
		
		$auth->request('POST', $auth->url('Invoices', 'core'), $params, $xml);
		if (intval($auth->response['code']) !== self::RESPONSE_CODE_SUCCESS)
			throw new Exception('Error' .  $auth->response['response']);
		
		$response = $auth->parseResponse($auth->response['response'], $auth->response['format']);
		
		return isset($response->Invoices) ? $response->Invoices : null;
	}
	
	public function updateInvoice($param = array(), $input)
	{
		return $this->_updateInvoices($param, $input);
	}
	
	public function createInvoice($param = array(), $input)
	{
		return $this->_updateInvoices($param, $input);
	}
	
	private function _validateAndRectifyLineItems(&$input)
	{
		if(!isset($input['line_items']) || count($input['line_items']) === 0)
			throw new Exception("Atleast one Line item is mandatory to create invoice");
		
		foreach($input['line_items'] as $key => $lineItem)
		{
			$item = XeroConnector_Item::get()->getItemByCode($lineItem['item_code']);
			if(!isset($item->Item) || trim($lineItem['description']) === '' ||
			!is_numeric($lineItem['quantity']) || $lineItem['quantity'] <= 0 ||
			!is_numeric($lineItem['unit_amount']) || $lineItem['unit_amount'] <= 0
			)
			{
				unset($input['line_items'][$key]);
			}
			else
			{
				if(isset($lineItem['account_code']) && ($ac = trim($lineItem['account_code'])) !== '')
				{
					$accounts = XeroConnector_Account::get()->getAccountByCode($ac);
					if(!isset($accounts->Account))
						$input['line_items'][$key]['account_code'] = ''; //Default account code
				}
			}
		}
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
		if(!isset($input['type']) || trim($input['type']) === '' || !in_array(trim($input['type']), $this->_invoiceTypes))
			throw new Exception("Invoice Type is mandatory / not valid");
		
		if(!isset($input['contact_id']) || trim($input['contact_id']) === '')
			throw new Exception("Contact id is mandatory");
			
		$contactXML = XeroConnector_Contact::get()->getContacts(array('Where' => 'ContactId == '.trim($input['contact_id']), 'order' => 'ContactID'));
		if(!isset($contactXML->Contact) || trim($contactXML->Contact->Name) === '')	
			throw new Exception("Contact id [".$input['contact_id']."] is not valid!");

		$contactName = trim($contactXML->Contact->Name);

		$this->_validateAndRectifyLineItems($input);

		if(!isset($input['line_items']) || count($input['line_items']) === 0)
			throw new Exception("Atleast one Line item is mandatory to create invoice");
		
		$xml = parent::getXML();
		if($xml instanceof SimpleXMLElement)
		{
			$xml->Type = $input['type'];
			
			$contact = $xml->addChild('Contact');
			$contact->Name = $contactName;
			
			if(isset($input['date']) && trim($input['date']) !== '')
				$xml->Date = trim($input['date']);
			
			if(isset($input['due_date']) && trim($input['due_date']) !== '')
				$xml->DueDate = trim($input['due_date']);
			
			$lineAmountType = $this->_defaultLineAmountType;
			if(isset($input['line_amount_type']) && ($lat = trim($input['line_amount_type'])) !== '' && in_array($lat, $this->_lineAmountTypes))
				$lineAmountType = $lat;
			
			$xml->LineAmountTypes = $lineAmountType;
			
			$lineItemsXML = $xml->addChild('LineItems');
			
			foreach($input['line_items'] as $key => $lineItem)
			{
				$lineItemXML = $lineItemsXML->addChild('LineItem');
				$lineItemXML->Description = trim($lineItem['description']);
				$lineItemXML->Quantity = number_format(trim($lineItem['quantity']), 4, '.');
				$lineItemXML->UnitAmount = number_format(trim($lineItem['unit_amount']), 2, '.');
				$lineItemXML->ItemCode = trim($lineItem['item_code']);
				
				if(trim($lineItem['account_code']) !== '')
					$lineItemXML->AccountCode = trim($lineItem['account_code']);
			}
		}
		else
			throw new Exception("Cannot create XML for Invoice");
		
		$xml = $this->_removeXMLHeader($xml);
		return $xml;
	}
	
	private function _validateDate($date)
	{
		if(preg_match('/^2\d{3}-(0\d{1}|1(1|2){1})-(0\d{1}|1\d{1}|2\d{1}|3(0|1){1})$/', $date))
			return true;
		
		return false;
	}
}