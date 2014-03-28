<?php
class FastWayConnector extends CourierConnector
{
	public function __construct(Courier $courier)
	{
		parent::__construct($courier);	
	}
	
	public function getListOfDeliverySuburbs($city = '', $frCode = '')
	{
		$restURL = $this->getCourierInfo(FactoryAbastract::service('CourierInfoType')->get(CourierInfoType::ID_URL));
		$restApiKey = $this->getCourierInfo(FactoryAbastract::service('CourierInfoType')->get(CourierInfoType::ID_API_KEY));
		
		if(!preg_match('/\/$/', $restURL))
			$restURL = $restURL.'/';
		
		if(($city = trim($city)) === '')
			throw new Exception('You Must provide 3 digit CITY CODE (i.e MEL/SYD) to find the list of Delivery Suburbs');
		
		$frCode = trim($frCode);
		$functionName = 'listdeliverysuburbs';
		$requestURL = $restURL . $functionName . '/' . $city .  ($frCode !== '' ? '/'.$frCode : '') . '?api_key='.$restApiKey; 
		
		$data = ComScriptCURL::readUrl($requestURL);
		$resultObject = json_decode($data);
		
		$suburbObjectArray = $resultObject->result;
		
		return $suburbObjectArray;
	}
	
	
	
}