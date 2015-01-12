<?php
class XeroConnector_Account extends XeroConnectorAbstract
{
	/**
	 * Getting the Accounts
	 *
	 * @param unknown $params
	 *
	 * @throws Exception
	 * @return SimpleXMLElement|null
	 */
	public function getAccounts($params = array())
	{
		$auth = $this->_getOAuth();
		$auth->request('GET', $auth->url('Accounts', 'core'), $params);
		if (intval($auth->response['code']) !== self::RESPONSE_CODE_SUCCESS)
			throw new Exception('Error' .  $auth->response['response']);
		$response = $auth->parseResponse($auth->response['response'], $auth->response['format']);
		return isset($response->Accounts) ? $response->Accounts : null;
	}
}