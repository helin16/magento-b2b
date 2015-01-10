<?php
class XeroConnector
{
// 	const CUSTOMER_KEY = 'VUWUSFPVDZOBRH3U9DJPONYTGNEGBM';
// 	const CUSTOMER_SECRET = 'GF94FCRWS8H5JA4BCLLPGNIFG5QQFY';
	const CUSTOMER_KEY = 'AYLWASFOHUTMKN5KKVG3OSMQTX4ERK';
	const CUSTOMER_SECRET = 'A5CIIOWLOTY1516WFQEARSHUZKLXW9';
	
	private $_url = "https://api.xero.com";
	private $_oAuthToken = '';
	
	public function __construct()
	{
	}
	
	public function getOAuthToken()
	{
		if(trim($this->_oAuthToken) !== '')
			return $this->_oAuthToken;
		
		$url = $this->_url.'/oauth/RequestToken';
		//ComScriptCURL::readUrl($url, null, )
		
	}
	
	
}


?>