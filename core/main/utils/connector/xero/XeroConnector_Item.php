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
		$xml = $this->getXML();
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
			$xml = $this->_removeXMLHeader($xml);
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
}