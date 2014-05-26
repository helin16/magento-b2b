<?php
class FastWayConnector extends CourierConnector
{
	public function __construct(Courier $courier)
	{
		parent::__construct($courier);	
	}
	
	private function _getJsonResult($data)
	{
		try 
		{
			if (($data = trim($data)) === '')
				throw new Exception("Empty result returned!");
			$result = json_decode($data);
			if (isset($result->error))
				throw new Exception($result->error);
			return $result->result;
		}
		catch(Exception $ex)
		{
			throw new Exception('Error Occured when trying to parse JSON: (' . $data . '): ' . $ex->getMessage(), null, $ex);
		}
	}
	/**
	 * Getting the api information for this courier
	 * 
	 * @throws ConnectorException
	 * @return multitype:unknown Ambigous <>
	 */
	private function _getAPIInfos()
	{
		$apiUrls =$this->_courier->getInfo(CourierInfoType::ID_API_URL);
		if(count($apiUrls) === 0)
			throw new ConnectorException('Error from ' . __CLASS__ . ': no api url has been set for courier: ' . $this->_courier->getName());
		$apiKeys =$this->_courier->getInfo(CourierInfoType::ID_API_KEY);
		if(count($apiUrls) === 0)
			throw new ConnectorException('Error from ' . __CLASS__ . ': no api key has been set for courier: ' . $this->_courier->getName());
		$accIds =$this->_courier->getInfo(CourierInfoType::ID_ACCOUNT_ID);
		if(count($accIds) === 0)
			throw new ConnectorException('Error from ' . __CLASS__ . ': no account Ids has been set for courier: ' . $this->_courier->getName());
		return array($apiUrls[0], $apiKeys[0], $accIds[0]);
	}
	/**
	 * (non-PHPdoc)
	 * @see CourierConnector::createManifest()
	 */
	public function createManifest($userId = '')
	{
		list($apiUrl, $apiKey, $accId) = $this->_getAPIInfos();
		$url = str_replace('{method}', 'fastlabel/addmanifest', trim($apiUrl) );
		$params = array(
			'api_key' => trim($apiKey)
			,'UserID' => trim($accId)
// 			,'Description' => 'Manifest created from BudgetPC internal system automatically, any issues please contact sales@budgetpc.com.au ASAP.'
		);
		$result = $this->_getJsonResult(ComScriptCURL::readUrl($url, ComScriptCURL::CURL_TIMEOUT, $params));
		return $result[0];
	}
	/**
	 * Creating a consignment note for the delivery
	 *
	 * @param Shippment $shippment The Shippment
	 * @param string $manifestId   The manifest id from the courier
	 *
	 * @throws Exception
	 */
	public function createConsignment(Shippment &$shippment, $manifestId = '')
	{
		list($apiUrl, $apiKey, $accId) = $this->_getAPIInfos();
		$url = str_replace('{method}', 'fastlabel/addconsignment', trim($apiUrl) );
		
		$items = array();
		foreach($shippment->getOrder()->getOrderItems() as $item)
		{
			$items[] = array(
				'Reference'  => $shippment->getOrder()->getOrderNo()
				,'Quantity'  => 1
				,'Weight'    => 20
				,'Packaging' => 1
				,'Length'    => ''
				,'Width'    => ''
				,'Height'    => ''
			);
		}
		
		$emails = $shippment->getOrder()->getInfo(OrderInfoType::ID_CUS_EMAIL);
		$params = array(
				'api_key'              => trim($apiKey)
				,'UserID'              => trim($accId)
				//receiver's details
				,'ContactName'         => trim($shippment->getContact())
				,'Address1'            => trim($shippment->getOrder()->getShippingAddr()->getStreet())
				,'Suburb'              => trim($shippment->getOrder()->getShippingAddr()->getCity())
				,'Postcode'            => trim($shippment->getOrder()->getShippingAddr()->getPostCode())
				,'ContactEmail'        => (count($emails) > 0 ? $emails[0] : '')
				,'ContactMobile'       => trim($shippment->getContact())
				//instructions
				,'SpecialInstruction1' => trim($shippment->getDeliveryInstructions())
				//items
				,'items'               => $items
		);
		$result = $this->_getJsonResult(ComScriptCURL::readUrl($url, ComScriptCURL::CURL_TIMEOUT, $params));
		return $result;
	}
	
// 	/**
// 	 * Getting a list of delivery suburbs
// 	 * @param string $city
// 	 * @param string $frCode
// 	 * @throws Exception
// 	 * @return unknown
// 	 */
// 	public function getListOfDeliverySuburbs($city = '', $frCode = '')
// 	{
// 		$restURL = $this->_checkAndFixURL($this->getCourierInfo(FactoryAbastract::service('CourierInfoType')->get(CourierInfoType::ID_URL)));
// 		$restApiKey = $this->getCourierInfo(FactoryAbastract::service('CourierInfoType')->get(CourierInfoType::ID_API_KEY));
		
// 		if(($city = trim($city)) === '')
// 			throw new Exception('You Must provide 3 digit CITY CODE (i.e MEL/SYD) to find the list of Delivery Suburbs');
		
// 		$frCode = trim($frCode);
// 		$functionName = 'listdeliverysuburbs';
// 		$requestURL = $restURL . $functionName . '/' . $city .  ($frCode !== '' ? '/'.$frCode : '') . '?api_key='.$restApiKey; 
		
// 		$data = $this->_getJSONDataFromURL($requestURL);
// 		if(isset($data->error) && trim($data->error) !== '')
// 			throw new Exception($data->error);
		
// 		$suburbObjectArray = $data->result;
// 		return $suburbObjectArray;
// 	}
	
// 	/**
// 	 * 
// 	 * @param string $countryName
// 	 * @param string $countryCode
// 	 * @param string $franchiseeCode
// 	 * @throws Exception
// 	 * @return unknown
// 	 */
// 	public function getListOfAllRFsByCountry($countryName = '', $countryCode = '', $franchiseeCode = '')
// 	{
// 		if(($countryName = trim($countryName, $countryCode)) === '' && ($countryCode = trim($countryCode)) === '')
// 			throw new Exception('You must provide either the Country Name OR Country Code to find the list of Regional Franchises');
		
// 		$restURL = $this->_checkAndFixURL($this->getCourierInfo(FactoryAbastract::service('CourierInfoType')->get(CourierInfoType::ID_URL)));
// 		$restApiKey = $this->getCourierInfo(FactoryAbastract::service('CourierInfoType')->get(CourierInfoType::ID_API_KEY));
// 		$countryCodes = $this->getCourierInfo(FactoryAbastract::service('CourierInfoType')->get(CourierInfoType::ID_COUNTRY_CODES));
// 		$ccObject = json_decode($countryCodes);
		
// 		$codeFromName = '';
// 		if($countryName !== '')
// 		{
// 			if(isset($ccObject->$countryName))
// 				$codeFromName = $ccObject->$countryName;
// 			else
// 				throw new Exception('Invalid Country Name ['.$countryName.'] provided');
// 		}
		
// 		if($countryCode !== '')
// 		{
// 			$ccArray = get_object_vars($ccObject);
// 			$onlyCodeArray = array_map(create_function('$a', 'return trim($a);'), $ccArray);
// 			if(!in_array($countryCode, $onlyCodeArray))
// 				throw new Exception('Invalid Country Code ['. $countryCode .'] provided');
// 		}
		
// 		if($codeFromName !== '' && $countryCode === '')
// 			$codeToSearch = $codeFromName;
// 		else if($codeFromName === '' && $countryCode !== '')
// 			$codeToSearch = $countryCode;
// 		else if($codeFromName !== '' && $countryCode !== '' && trim($codeFromName) === trim($countryCode))
// 			$codeToSearch = $codeFromName;
// 		else
// 			throw new Exception('Provided Country ['.$countryName.'] has code ['. $codeFromName .'] which does not match with provided Country Code ['. $countryCode .']'); 
		
// 		$urlArray = array('CountryCode' => $codeToSearch);
// 		if(($franchiseeCode = trim($franchiseeCode)) !== '')
// 			$urlArray['FranchiseeCode'] = $franchiseeCode;
		
// 		$urlArray['api_key'] = $restApiKey;
// 		$functionName = 'listrfs';
// 		$finalURL = $restURL . $functionName.'?'. http_build_query($urlArray);

// 		$data = $this->_getJSONDataFromURL($finalURL);
// 		if(isset($data->error) && trim($data->error) !== '')
// 			throw new Exception($data->error);
		
// 		$rfObjectArray = $data->result;
// 		return $rfObjectArray;
// 	}
	
// 	/**
// 	 * 
// 	 * @param unknown $info
// 	 * @param unknown $infoType
// 	 * @throws Exception
// 	 * @return Ambigous <number, string>
// 	 */
// 	protected function _checkParcelSizeAndWeigth($info, $infoType)
// 	{
// 		if(($info = trim($info)) !== '' && !is_numeric($info))
// 			throw new Exception('Provided '.$infoType. ' ['.$info.'] is NOT VALID !!!');
// 		else if($info === '')
// 			$info = 0;
// 		return $info;
// 	}
	
// 	/**
// 	 * 
// 	 * @param String $rfCode
// 	 * @param String $destSuburb
// 	 * @param String $destPostCode
// 	 * @param String/Number $weightInKg
// 	 * @param String/Number $lengthInCm
// 	 * @param String/Number $widthInCm
// 	 * @param String/Number $heightInCm
// 	 * @param Boolean $allowMultipleRegions
// 	 * @param Boolean $showProductBox
// 	 * @throws Exception
// 	 * @return unknown
// 	 */
// 	public function calculatePriceForSendingParcel($rfCode, $destSuburb, $destPostCode = '', $weightInKg = 0, $lengthInCm = 0, $widthInCm = 0, $heightInCm = 0, $allowMultipleRegions = false, $showProductBox = false)
// 	{
// 		if(($rfCode = trim($rfCode)) === '')
// 			throw new Exception('You Must provide the Src Regional Franchise to execute the price calculation');
// 		if(($destSuburb = trim($destSuburb)) === '')
// 			throw new Exception('You Must provide the destination suburb to execute the price calculation');
		
// 		if(($destPostCode = trim($destPostCode)) !== '' && (preg_match('/^\d{4,}$/', $destPostCode)) === 0)
// 			throw new Exception('Provided Destination Post Code ['. $destPostCode .'] Is NOT VALID!!!');
		
// 		$weightInKg = trim($this->_checkParcelSizeAndWeigth($weightInKg, 'Weigth In Kg'));
// 		$lengthInCm = trim($this->_checkParcelSizeAndWeigth($lengthInCm, 'Length In cm'));
// 		$widthInCm = trim($this->_checkParcelSizeAndWeigth($widthInCm, 'Width In cm'));
// 		$heightInCm = trim($this->_checkParcelSizeAndWeigth($heightInCm, 'Height In cm'));

// 		if($weightInKg === '0')
// 		{
// 			if($lengthInCm === '0' || $widthInCm === '0' || $heightInCm === '0')
// 				throw new Exception('Weight (in kg) is not provided. So Length, Width and Height (in cm) MUST BE provided to calculate price');
// 		}
		
// 		$allowMultipleRegions = ($allowMultipleRegions === true ? 'true' : 'false');
// 		$showProductBox = ($showProductBox === true ? 'true' : 'false');
		
// 		$restURL = $this->_checkAndFixURL($this->getCourierInfo(FactoryAbastract::service('CourierInfoType')->get(CourierInfoType::ID_URL)));
// 		$restApiKey = $this->getCourierInfo(FactoryAbastract::service('CourierInfoType')->get(CourierInfoType::ID_API_KEY));
		
// 		$urlArray = array();
// 		if($weightInKg !== '0')
// 			$urlArray['WeightInKg'] = $weightInKg;
// 		if($lengthInCm !== '0')
// 			$urlArray['LengthInCm'] = $lengthInCm;
// 		if($widthInCm !== '0')
// 			$urlArray['WidthInCm'] = $widthInCm;
// 		if($heightInCm !== '0')
// 			$urlArray['HeightInCm'] = $heightInCm;
		
// 		if($allowMultipleRegions === 'true')
// 			$urlArray['AllowMultipleRegions'] = $allowMultipleRegions;
// 		if($showProductBox === 'true')
// 			$urlArray['ShowBoxProduct'] = $showProductBox;
			
		
// 		$urlArray['api_key'] = $restApiKey;
// 		$functionName = 'lookup';
		
// 		$finalURL = $restURL . $functionName .'/'.$rfCode.'/'.$destSuburb. (trim($destPostCode) !== '' ? '/'.$destPostCode : '') .'?' .http_build_query($urlArray);
// 		var_dump($finalURL);
// 		$data = $this->_getJSONDataFromURL($finalURL);
		
// 		if(isset($data->error) && trim($data->error) !== '')
// 			throw new Exception($data->error);
		
// 		return $data->result;
// 	}
	
}