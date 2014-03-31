<?php
class FastWayConnector extends CourierConnector
{
	public function __construct(Courier $courier)
	{
		parent::__construct($courier);	
	}
	
	/**
	 * 
	 * @param string $city
	 * @param string $frCode
	 * @throws Exception
	 * @return unknown
	 */
	public function getListOfDeliverySuburbs($city = '', $frCode = '')
	{
		$restURL = $this->_checkAndFixURL($this->getCourierInfo(FactoryAbastract::service('CourierInfoType')->get(CourierInfoType::ID_URL)));
		$restApiKey = $this->getCourierInfo(FactoryAbastract::service('CourierInfoType')->get(CourierInfoType::ID_API_KEY));
		
		if(($city = trim($city)) === '')
			throw new Exception('You Must provide 3 digit CITY CODE (i.e MEL/SYD) to find the list of Delivery Suburbs');
		
		$frCode = trim($frCode);
		$functionName = 'listdeliverysuburbs';
		$requestURL = $restURL . $functionName . '/' . $city .  ($frCode !== '' ? '/'.$frCode : '') . '?api_key='.$restApiKey; 
		
		$data = $this->_getJSONDataFromURL($requestURL);
		if(isset($data->error) && trim($data->error) !== '')
			throw new Exception($data->error);
		
		$suburbObjectArray = $data->result;
		return $suburbObjectArray;
	}
	
	/**
	 * 
	 * @param string $countryName
	 * @param string $countryCode
	 * @param string $franchiseeCode
	 * @throws Exception
	 * @return unknown
	 */
	public function getListOfAllRFsByCountry($countryName = '', $countryCode = '', $franchiseeCode = '')
	{
		if(($countryName = trim($countryName, $countryCode)) === '' && ($countryCode = trim($countryCode)) === '')
			throw new Exception('You must provide either the Country Name OR Country Code to find the list of Regional Franchises');
		
		$restURL = $this->_checkAndFixURL($this->getCourierInfo(FactoryAbastract::service('CourierInfoType')->get(CourierInfoType::ID_URL)));
		$restApiKey = $this->getCourierInfo(FactoryAbastract::service('CourierInfoType')->get(CourierInfoType::ID_API_KEY));
		$countryCodes = $this->getCourierInfo(FactoryAbastract::service('CourierInfoType')->get(CourierInfoType::ID_COUNTRY_CODES));
		$ccObject = json_decode($countryCodes);
		
		$codeFromName = '';
		if($countryName !== '')
		{
			if(isset($ccObject->$countryName))
				$codeFromName = $ccObject->$countryName;
			else
				throw new Exception('Invalid Country Name ['.$countryName.'] provided');
		}
		
		if($countryCode !== '')
		{
			$ccArray = get_object_vars($ccObject);
			$onlyCodeArray = array_map(create_function('$a', 'return trim($a);'), $ccArray);
			if(!in_array($countryCode, $onlyCodeArray))
				throw new Exception('Invalid Country Code ['. $countryCode .'] provided');
		}
		
		if($codeFromName !== '' && $countryCode === '')
			$codeToSearch = $codeFromName;
		else if($codeFromName === '' && $countryCode !== '')
			$codeToSearch = $countryCode;
		else if($codeFromName !== '' && $countryCode !== '' && trim($codeFromName) === trim($countryCode))
			$codeToSearch = $codeFromName;
		else
			throw new Exception('Provided Country ['.$countryName.'] has code ['. $codeFromName .'] which does not match with provided Country Code ['. $countryCode .']'); 
		
		$urlArray = array('CountryCode' => $codeToSearch);
		if(($franchiseeCode = trim($franchiseeCode)) !== '')
			$urlArray['FranchiseeCode'] = $franchiseeCode;
		
		$urlArray['api_key'] = $restApiKey;
		$functionName = 'listrfs';
		$finalURL = $restURL . $functionName.'?'. http_build_query($urlArray);

		$data = $this->_getJSONDataFromURL($finalURL);
		if(isset($data->error) && trim($data->error) !== '')
			throw new Exception($data->error);
		
		$rfObjectArray = $data->result;
		return $rfObjectArray;
	}
	
	/**
	 * 
	 * @param unknown $info
	 * @param unknown $infoType
	 * @throws Exception
	 * @return Ambigous <number, string>
	 */
	protected function _checkParcelSizeAndWeigth($info, $infoType)
	{
		if(($info = trim($info)) !== '' && !is_numeric($info))
			throw new Exception('Provided '.$infoType. ' ['.$info.'] is NOT VALID !!!');
		else if($info === '')
			$info = 0;
		return $info;
	}
	
	/**
	 * 
	 * @param String $rfCode
	 * @param String $destSuburb
	 * @param String $destPostCode
	 * @param String/Number $weightInKg
	 * @param String/Number $lengthInCm
	 * @param String/Number $widthInCm
	 * @param String/Number $heightInCm
	 * @param Boolean $allowMultipleRegions
	 * @param Boolean $showProductBox
	 * @throws Exception
	 * @return unknown
	 */
	public function calculatePriceForSendingParcel($rfCode, $destSuburb, $destPostCode = '', $weightInKg = 0, $lengthInCm = 0, $widthInCm = 0, $heightInCm = 0, $allowMultipleRegions = false, $showProductBox = false)
	{
		if(($rfCode = trim($rfCode)) === '')
			throw new Exception('You Must provide the Src Regional Franchise to execute the price calculation');
		if(($destSuburb = trim($destSuburb)) === '')
			throw new Exception('You Must provide the destination suburb to execute the price calculation');
		
		if(($destPostCode = trim($destPostCode)) !== '' && (preg_match('/^\d{4,}$/', $destPostCode)) === 0)
			throw new Exception('Provided Destination Post Code ['. $destPostCode .'] Is NOT VALID!!!');
		
		$weightInKg = trim($this->_checkParcelSizeAndWeigth($weightInKg, 'Weigth In Kg'));
		$lengthInCm = trim($this->_checkParcelSizeAndWeigth($lengthInCm, 'Length In cm'));
		$widthInCm = trim($this->_checkParcelSizeAndWeigth($widthInCm, 'Width In cm'));
		$heightInCm = trim($this->_checkParcelSizeAndWeigth($heightInCm, 'Height In cm'));

		if($weightInKg === '0')
		{
			if($lengthInCm === '0' || $widthInCm === '0' || $heightInCm === '0')
				throw new Exception('Weight (in kg) is not provided. So Length, Width and Height (in cm) MUST BE provided to calculate price');
		}
		
		$restURL = $this->_checkAndFixURL($this->getCourierInfo(FactoryAbastract::service('CourierInfoType')->get(CourierInfoType::ID_URL)));
		$restApiKey = $this->getCourierInfo(FactoryAbastract::service('CourierInfoType')->get(CourierInfoType::ID_API_KEY));
		
		$urlArray = array();
		if($weightInKg !== '0')
			$urlArray['WeightInKg'] = $weightInKg;
		if($lengthInCm !== '0')
			$urlArray['LengthInCm'] = $lengthInCm;
		if($widthInCm !== '0')
			$urlArray['WidthInCm'] = $widthInCm;
		if($heightInCm !== '0')
			$urlArray['HeightInCm'] = $heightInCm;
		
		$urlArray['api_key'] = $restApiKey;
		$functionName = 'lookup';
		
		$finalURL = $restURL . $functionName .'/'.$rfCode.'/'.$destSuburb. (trim($destPostCode) !== '' ? '/'.$destPostCode : '') .'?' .http_build_query($urlArray);
		$data = $this->_getJSONDataFromURL($finalURL);
		
		if(isset($data->error) && trim($data->error) !== '')
			throw new Exception($data->error);
		
		return $data->result;
	}
	
}