<?php
class XeroConnector_Item extends XeroConnectorAbstract
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
	private function _getFakeXml($code, $description = '', $salesUnitPrice = 0, $salesAccountCode = 0, $purchaseUnitPrice = 0, $purchaseAccountCode = 0, $purchaseTaxType = '') {
		$xml = new SimpleXMLElement ( '<' . 'Item' . '/>' );
		$xml->Code = $code;
		if(isset($description))
			$xml->Description = $description;
		if(!empty($salesUnitPrice) && !empty($salesAccountCode))
		{
			if(!empty($salesUnitPrice))
				$xml->SalesDetails->UnitPrice = $salesUnitPrice;
			if(!empty($salesAccountCode))
				$xml->SalesDetails->AccountCode = $salesAccountCode;
		}
		if(!empty($purchaseUnitPrice) && !empty($purchaseAccountCode))
		{
			if(!empty($purchaseUnitPrice))
				$xml->PurchaseDetails->UnitPrice = $purchaseUnitPrice;
			if(!empty($purchaseAccountCode))
				$xml->PurchaseDetails->AccountCode = $purchaseAccountCode;
			if(!empty($purchaseTaxType))
				$xml->PurchaseDetails->TaxType = $purchaseTaxType;
		}
		return $xml;
	}
	/**
	 * remove the xml header (version ...)
	 * 
	 * @param SimpleXMLElement $xml
	 */
	private function removeXMLHeader(SimpleXMLElement $xml)
	{
		$dom = dom_import_simplexml($xml);
		return $dom->ownerDocument->saveXML($dom->ownerDocument->documentElement);
	}
	/**
	 * Getting the items
	 *
	 * @param unknown $params
	 *
	 * @throws Exception
	 * @return SimpleXMLElement|null
	 */
	private function getItems($params = array())
	{
		$auth = $this->_getOAuth();
		$auth->request('GET', $auth->url('Items', 'core'), $params);
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
	private function updateItems($params = array(), $code, $description = '', $salesUnitPrice = 0, $salesAccountCode = 0, $purchaseUnitPrice = 0, $purchaseAccountCode = 0, $purchaseTaxType = '')
	{
		$auth = $this->_getOAuth();
			$xml = $this->_getFakeXml($code, $description, $salesUnitPrice, $salesAccountCode, $purchaseUnitPrice, $purchaseAccountCode, $purchaseTaxType);
			$xml = $this->removeXMLHeader($xml);
			$auth->request('POST', $auth->url('Items', 'core'), $params, $xml);
			if (intval($auth->response['code']) !== self::RESPONSE_CODE_SUCCESS)
				throw new Exception('Error' .  $auth->response['response']);
			$response = $auth->parseResponse($auth->response['response'], $auth->response['format']);
		return isset($response->Items) ? $response->Items : null;
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
	public function createItem($params = array(), $code, $description = '', $salesUnitPrice = 0, $salesAccountCode = 0, $purchaseUnitPrice = 0, $purchaseAccountCode = 0, $purchaseTaxType = '')
	{
		return $this->updateItems($params, $code, $description, $salesUnitPrice, $salesAccountCode, $purchaseUnitPrice, $purchaseAccountCode, $purchaseTaxType);
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
	public function updateItem($params = array(), $code, $description = '', $salesUnitPrice = 0, $salesAccountCode = 0, $purchaseUnitPrice = 0, $purchaseAccountCode = 0, $purchaseTaxType = '')
	{
		return $this->updateItems($params, $code, $description, $salesUnitPrice, $salesAccountCode, $purchaseUnitPrice, $purchaseAccountCode, $purchaseTaxType);
	}
	/**
	 * Getting all items
	 *
	 * @throws Exception
	 * @return SimpleXMLElement|null
	 */
	public function getAllItems()
	{
		return $this->getItems(array());
	}
	/**
	 * Getting the items
	 *
	 * @param unknown $value ItemID
	 *
	 * @throws Exception
	 * @return SimpleXMLElement|null
	 */
	public function getItemById($value)
	{
		return $this->getItems(array('Where' => 'ItemID==Guid("' . trim($value) . '")', 'order' => 'ItemID'));
	}
	/**
	 * Getting the items
	 *
	 * @param unknown $value Code
	 *
	 * @throws Exception
	 * @return SimpleXMLElement|null
	 */
	public function getItemByCode($value)
	{
		return $this->getItems(array('Where' => 'Code=="' . trim($value) . '"', 'order' => 'ItemID'));
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
// 		if(!isset($input['code']) || trim($input['code']) === '' || !isset($input['name']) || trim($input['name']) === '' || !isset($input['type']) || trim($input['type']) === '')
// 			throw new Exception("Mandatory element(s) missing for constructing XML for ACCOUNT");
			
// 		$xml = parent::getXML();
// 		$xml->Code = trim($input['code']);
// 		$xml->Name = trim($input['name']);
// 		$xml->Type = trim($input['type']);
	
// 		if(isset($input['description']) && ($description = trim($input['description'])) !== '')
// 			$xml->Description = $description;
	
// 		if(isset($input['tax_type']) && ($taxType = trim($input['tax_type'])) !== '')
// 			$xml->TaxType = $taxType;
	
// 		if(isset($input['enable_payments_to_account']) && is_bool($input['enable_payments_to_account']))
// 			$xml->EnablePaymentsToAccount = $input['enable_payments_to_account'];
			
// 		if(isset($input['show_in_expense_claims']) && is_bool($input['show_in_expense_claims']))
// 			$xml->ShowInExpenseClaims = $input['show_in_expense_claims'];
// 		return $xml;
	}
	
}