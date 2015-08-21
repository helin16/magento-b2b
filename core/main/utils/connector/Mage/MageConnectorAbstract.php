<?php
abstract class MageConnectorAbstract
{
	protected static $_sessionId = null;
	protected static $_soapClient = null;
	protected static $_debug = true;
	const LOG_TYPE = 'MageConnector';
	const DEBUG_PREFIX = "    ";
	/**
	 * Get connected
	 *
	 * @return the session id
	 */
	protected static function _connect() {
		$class = get_called_class();

		$options = array('exceptions' => true, 'trace'=> true, 'encoding'=>'utf-8');
// 		$options = array_merge($options, array('proxy_host' => "proxy.bytecraft.internal", 'proxy_port' => 3128));

		$wsdl = trim(SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_WSDL));
		$apiUser = trim(SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_USER));
		$apiKey = trim(SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_KEY));

		$class::$_soapClient = ComScriptSoap::getScript($wsdl, $options);
		$class::$_sessionId = $class::$_soapClient->login($apiUser, $apiKey);
		return $class::$_soapClient;
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
	protected static function _log($msg, $type = MageConnectorAbstract::LOG_TYPE, $entityId = '', $entityName = '', $comments = '', $funcName = '', $preFix = '') {
		$class = get_called_class();
		if($class::$_debug === true) {
			if(php_sapi_name() === 'cli')
				echo $preFix . $msg ."\n";
			else
				echo "<div>" . str_replace(' ', '&nbsp;', $preFix) . $msg . "</div>";
		}
		if(trim($msg) !== '')
			return Log::logging($entityId, $entityName, $msg, $type, $comments, $funcName);
		return null;
	}
	/**
	 * Getting the Log prefix
	 *
	 * @param number $level
	 *
	 * @return string
	 */
	protected static function _getPreFix($level = 0)
	{
		return str_repeat(self::DEBUG_PREFIX, $level);
	}
}