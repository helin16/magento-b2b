<?php
	class XeroConnector
	{
		const CUSTOMER_KEY = 'UWUSFPVDZOBRH3U9DJPONYTGNEGBM';
		const CUSTOMER_SECRET = 'UWUSFPVDZOBRH3U9DJPONYTGNEGBM';
		
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