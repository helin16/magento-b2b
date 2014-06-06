<?php
class CourierConn_AusPost extends CourierConnector implements CourierConn
{
	private $api = 'https://auspost.com.au/api/';
	private $auth_key = '5c7fb4db-8f24-4c0d-84cd-e12d48d1864e';
	const MAX_HEIGHT = 35; //only applies if same as width
	const MAX_WIDTH = 35; //only applies if same as height
	const MAX_WEIGHT = 20; //kgs
	const MAX_LENGTH = 105; //cms
	const MAX_GIRTH = 140; //cms
	const MIN_GIRTH = 16; //cms
	
	public function getRemoteData($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Auth-Key: ' . $this->auth_key
		));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$contents = curl_exec ($ch);
		curl_close ($ch);
		return json_decode($contents,true);
	}
	
	public function getShippingCost($data)
	{
		$edeliver_url = "{$this->api}postage/parcel/domestic/calculate.json";
		$edeliver_url = $this->arrayToUrl($edeliver_url,$data);
		$results = $this->getRemoteData($edeliver_url);
	
		if (isset($results['error']))
			throw new Exception($results['error']['errorMessage']);
	
		return $results['postage_result']['total_cost'];
	}
	
	public function arrayToUrl($url,$array)
	{
		$first = true;
		foreach ($array as $key => $value)
		{
			$url .= $first ? '?' : '&';
			$url .= "{$key}={$value}";
			$first = false;
		}
		return $url;
	}
	
	public function getGirth($height,$width)
	{
		return ($width+$height)*2;
	}
	
	public function createManifest($userId = '')
	{
		
	}
	public function createConsignment(Shippment &$shippment, $manifestId = '')
	{
		
	}
	public function closeManifest($manifestId)
	{
		
	}
	public function getTrackingURL($label)
	{
		
	}
	public function removeManifest($manifestId)
	{
		
	}
}