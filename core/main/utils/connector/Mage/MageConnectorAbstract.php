<?php
abstract class MageConnectorAbstract
{
	protected static $_sessionId = null;
	protected static $_soapClient = null;
	protected static $_debug = false;
	/**
	 * Get connected
	 *
	 * @return the session id
	 */
	protected static function _connect() {
		$options = array('exceptions' => true, 'trace'=> true, 'encoding'=>'utf-8');
		$options = array_merge($options, array('proxy_host' => "proxy.bytecraft.internal", 'proxy_port' => 3128));

		$wsdl = trim(SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_WSDL));
		$apiUser = trim(SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_USER));
		$apiKey = trim(SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_KEY));

		self::$_soapClient = ComScriptSoap::getScript($wsdl, $options);
		self::$_sessionId = self::$_soapClient->login($apiUser, $apiKey);
		return self::$_soapClient;
	}
	/**
	 * loging the debug output
	 *
	 * @param unknown $entityId
	 * @param unknown $entityName
	 * @param unknown $msg
	 * @param unknown $type
	 * @param string  $comments
	 * @param string  $funcName
	 * @return B2BConnector
	 */
	protected static function _log($msg, $type, $entityId = '', $entityName = '', $comments = '', $funcName = '')
	{
		if(self::$_debug === true) {
			echo $msg . "\n";
		}
		return Log::logging($entityId, $entityName, $msg, $type, $comments, $funcName);
	}
}