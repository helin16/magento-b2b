<?php
class XeroConnector_Item extends XeroConnectorAbstract
{
	/**
	 * Getting the items
	 *
	 * @param unknown $params
	 *
	 * @throws Exception
	 * @return SimpleXMLElement|null
	 */
	public function getItems($params = array())
	{
		$auth = $this->_getOAuth();
		$auth->request('GET', $auth->url('Items', 'core'), $params);
		if (intval($auth->response['code']) !== self::RESPONSE_CODE_SUCCESS)
			throw new Exception('Error' .  $auth->response['response']);
		$response = $auth->parseResponse($auth->response['response'], $auth->response['format']);
		return isset($response->Items) ? $response->Items : null;
	}
}