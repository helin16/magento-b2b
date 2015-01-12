<?php
class XeroConnector_Invoice extends XeroConnectorAbstract
{
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
}