<?php
class PriceMatchConnector
{
	//private static $use_api = false;
	private $sku;
	private $base_url = 'http://www.staticice.com.au/cgi-bin/search.cgi';
	private $debug;
	private  static $_api = array('URL' => "http://192.168.1.7:8080/api/", 'token' => '');
	
	/**
	 * runner for PriceMatchConnector
	 *
	 * @param Product $product
	 * @param array $companies
	 * @return PriceMatchMin
	 * @throws Exception
	 */
	public static function run($sku, $debug = false)
	{
		$class = new self();
		$class->sku = trim($sku);
		$class->debug = $debug === true ? true : false;
		
		//$class->recordResult($class->getPrices());
		$class->recordResult(self::getMatchPrices($sku));
		
	}
	public static function getMinRecord($sku, $debug = false)
	{
		$class = new self();
		$class->sku = trim($sku);
		$class->debug = $debug === true ? true : false;
		return $class->_getMinRecord();
	}
	public static function getNewPrice($sku, $updateMagento = false, $debug = false)
	{
		$class = new self();
		$class->sku = trim($sku);
		$class->debug = $debug === true ? true : false;
		return $class->_getNewPrice($updateMagento);
	}
	private function _getMagentoProductPrice($sku)
	{
		if(trim($sku) === '')
			return null;
		$mageData = CatelogConnector::getConnector(B2BConnector::CONNECTOR_TYPE_CATELOG,
				SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_WSDL),
				SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_USER),
				SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_KEY)
		)
		->getProductInfo(trim($sku), array('price'));
		return $mageData === null ? null : $mageData->price;
	}
	private function _getNewPrice($updateMagento)
	{
		$result = null;
		$sku = $this->sku;
		$updateMagento = ($updateMagento === true ? true : false);
		$product = Product::getBySku($sku);
		if(!$product instanceof Product)
			throw new Exception('Invalid sku passed in, "' . $sku . '" given');
		$min = PriceMatchMin::getBySku($sku);
		$rule = ProductPriceMatchRule::getByProduct($product);
		$prices = ProductPrice::getPrices($product, ProductPriceType::get(ProductPriceType::ID_RRP));
		if(count($prices) === 0)
		{
			$newPrice = ProductPrice::create($product, ProductPriceType::get(ProductPriceType::ID_RRP), 0);
			$prices = array($newPrice);
		}
// 		if(($magePrice = $this->_getMagentoProductPrice($sku)) !== null)
// 			$prices[0]->setPrice($magePrice)->save();
		$myPrice = $prices[0]->getPrice();
		
		if(!$min instanceof PriceMatchMin)
			$min = PriceMatchMin::create($this->sku);
		if($rule instanceof ProductPriceMatchRule)
		{
			$company = $rule->getCompany();
			$price_from = $rule->getPrice_from();
			$price_to = $rule->getPrice_to();
			$offset = $rule->getOffset();
			
			$where = array(1);
			$params = array();
			
			$where[] = "minId = ? ";
			$params[] = $min->getId();
			
			$from_date = UDate::now('Australia/Melbourne')->setTime(0, 0, 0)->setTimeZone('UTC');
			$to_date = UDate::now('Australia/Melbourne')->setTime(23, 59, 59)->setTimeZone('UTC');
			$where[] = "created >= ? ";
			$params[] = $from_date;
			$where[] = "created <= ? ";
			$params[] = $to_date;
			
			$companies = $company->getAllAlias();
			$companyIds = array_map(create_function('$a', 'return $a->getId();'), $companies);
			$where[] = 'companyId IN ('.implode(", ", array_fill(0, count($companyIds), "?")).')';
			$params = array_merge($params, $companyIds);
			
		
			//calculate target compatitor price
			$records = PriceMatchRecord::getAllByCriteria(implode(' AND ', $where), $params, true, null, DaoQuery::DEFAUTL_PAGE_SIZE, array('price'=>'asc'));
						
			$base_price = null;
			foreach ($records as $record)
			{
				if($base_price === null || (doubleval($record->getPrice()) !== doubleval(0) && doubleval($record->getPrice()) < doubleval($base_price)))
				{
					$base_price = doubleval($record->getPrice());
				}
			}
			if($base_price !== null)
			{
				if($price_from !== null)
				{
					if(strpos($price_from, '%') !== false) // price rule is a percentage
					{
						$price_from = $base_price - $base_price * doubleval(0.01 * doubleval(str_replace('%', '', $price_from)));
					}
					else $price_from = $base_price - doubleval($price_from);
					if(doubleval($price_from) <= doubleval(0))
						$price_from = doubleval(0);
				}
				
				if($price_to !== null)
				{
					if(strpos($price_to, '%') !== false) // price rule is a percentage
					{
						$price_to = $base_price + $base_price * doubleval(0.01 * doubleval(str_replace('%', '', $price_to)));
					}
					else $price_to = $base_price + doubleval($price_to);
				}
				
				// check if in range
				if(($price_from === null || $myPrice >= $price_from) && ($price_to === null ||$myPrice <= $price_to))
				{
					$result = $base_price;
					
					// apply offset
					if($offset !== null)
					{
						if(strpos($offset, '%') !== false) // offset in the rule is a percentage
						{
							$result = $result + $result * doubleval(0.01 * doubleval(str_replace('%', '', $offset)));
						}
						else $result = $result + doubleval($offset);
					}
					
					// set product price
					if(isset($prices[0]) && $prices[0] instanceof ProductPrice)
					{
						$newmatchprice = doubleval($result);
						if  ( $newmatchprice >0 )
						{
							$oldPrice = $prices[0]->getPrice();
							echo 'update price from old price : ' . $oldPrice . ' to new price :'  . $newmatchprice . "\n";
							$prices[0]->setPrice(doubleval($result))->save()->addLog('PriceMatch change price from $' . $oldPrice . 'to new price $' . $result, Log::TYPE_SYSTEM);
	// 						if($updateMagento === true)
// 							$this->updateMagentoPrice(doubleval($result));
						}
						else 
						{
							echo 'not update price because new match price is 0 ( ' . $newmatchprice .' )' . "\n";
						}
					}
				}
			}
			else 
			{
				if($this->debug === true)
					echo "cannot find price for PriceMatchCompany " . $company->getCompanyName() . ', ' . $product->getSku() . '(id=' . $product->getId() . ', min(id=' . $min->getId() . '), records found:' . count($records) . "\n";
			}
			if($this->debug === true)
				echo 'new price= ' . ($result===null ? 'N/A' : $result) . ', my price= ' . (isset($myPrice) ? $myPrice : 'N/A') . ', ' . $company->getCompanyName() . ' price= ' . $base_price . ', matching range=[' . $price_from . ',' . $price_to . '], offset=' . ($offset===null ? 'null' : $offset) . "\n";
		}
		elseif($this->debug === true)
			echo ($min instanceof PriceMatchMin ? '' : 'Cannot find result on StaticIce for all known PriceMatchCompanies') . ($rule instanceof ProductPriceMatchRule ? '' : ('cannot find ProductPriceMatchRule for product ' . $product->getSku() . '(id=' . $product->getId() . ')')) . "\n";
		return $result;
	}
	private function _getMinRecord()
	{
		$sku = $this->sku;
		$min = PriceMatchMin::getBySku($sku);
		if($min instanceof PriceMatchMin)
		{
			$record =  $min->getMin();
			if($record instanceof PriceMatchRecord && $this->debug === true)
				echo 'min found for ' . $sku . '(id=' . Product::getBySku($sku)->getId() . '), ' .$record->getCompany()->getCompanyName() . ': $' . $record->getPrice() . "\n";
			return $record;
		}
		else return null;
	}
	private function updateMagentoPrice($price)
	{
		$product = Product::getBySku($this->sku);
		if(!$product instanceof Product)
			throw new Exception('Invalid Product passed in. "' . $product . '" given.');
		
		$connector = CatelogConnector::getConnector(B2BConnector::CONNECTOR_TYPE_CATELOG,
				SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_WSDL),
				SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_USER),
				SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_KEY));
		
		if($this->debug)
			echo 'Connecting to Magento for Product ' . $product->getSku() . '(id=' . $product->getId() . ')' . "\n";
	
		$result = $connector->updateProductPrice($product->getSku(), $price);
		
		if($result !== true && $this->debug === true)
			echo print_r($result, true);
		if($result === true && $this->debug === true)
			echo 'Magento Price Successfully updated to $' . $price . "\n";
		
		return $this;
	}
	/**
	 * put given price match result for all companies into PriceMatchRecord table
	 *
	 * @param unknown $companyPrices
	 * @return PriceMatchConnector
	 */
	private function recordResult($priceMatchResults)
	{
		foreach ($priceMatchResults as $priceMatchResult)
		{
			
			$company = $priceMatchResult['PriceMatchCompany'];
			$price = doubleval($priceMatchResult['price']);
			$url = $priceMatchResult['url'];
			$name = $priceMatchResult['name'];
			
			$min = PriceMatchMin::create($this->sku); // to create PriceMatchRecord must have a PriceMatchMin, the record for PriceMatchMin will be null at this time instance
			
			if(abs($price) !== doubleval(0) && $price > doubleval(0) && trim($price) !== '') // price must be positive (non-zero), otherwise will be rejected by Core::PriceMatchRecord::create()
			{
				PriceMatchRecord::create($company, $min, $price, $url);
			}
		}
		return $this;
	}
	/**
	 * Getting the price match result
	 * 
	 * @return array
	 */
	private function getPrices()
	{
		$result = array();
		$priceMatchResults = HTMLParser::getPriceListForProduct($this->base_url, $this->sku);
		
		foreach($priceMatchResults as $priceMatchResult)
		{
			if(($companyDetails = trim($priceMatchResult['companyDetails'])) === '')
				continue;
		
			$companyDetailsArray = explode('|', $companyDetails);
			$companyURL = (isset($companyDetailsArray[count($companyDetailsArray) - 2])) ? trim($companyDetailsArray[count($companyDetailsArray) - 2]) : trim($companyDetails);
			$companyURL = strtolower($companyURL);
			$companyURL = str_replace('https://', '', $companyURL);
			$companyURL = str_replace('http://', '', $companyURL);
			$name = (isset($companyDetailsArray[count($companyDetailsArray) - 3])) ? trim($companyDetailsArray[count($companyDetailsArray) - 3]) : trim($companyDetails);
			$price = str_replace(' ', '', str_replace('$', '', str_replace(',', '', $priceMatchResult['price']) ) );
			$url = HTMLParser::getHostUrl($this->base_url) . $priceMatchResult['priceLink'];
			
			foreach (PriceMatchCompany::getAll() as $company)
			{
				if($companyURL === strtolower($company->getCompanyAlias()))
				{
					$result[] = array('PriceMatchCompany'=> $company, 'price'=> $price, 'name'=> $name, 'url'=> $url);
					if($this->debug === true)
						echo $company->getCompanyName() . '(id=' . $company->getId() . "), $" . $price . "\n";
				}
			}
		}
		return $result;
	}
	private static function _login()
	{
		//Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT), Core::getRole());
		$username = UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT)->getUserName();
		$password = UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT)->getPassword();
		//$username = Core::getUser()->getUserName();
		//$password = Core::getUser()->getPassword();
	
		if(!isset(self::$_api['URL']) || ($apiUrl = trim(self::$_api['URL'])) === '')
			throw new Exception('No API URL set!');
		$url = $apiUrl . 'UserAccount/login';
		$data = json_encode(array('username' => $username, 'password' => $password));
	
		self::_postJson($url, $data);
		if(trim(self::$_api['token']) === '')
			throw new Exception('Invalid token');
	
	}
	private static function _postJson($url, $data)
	{
	
		$extraOptions = array( CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_POSTFIELDS => $data,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HTTPHEADER => array(
						'Content-Type: application/json',
						'Content-Length: ' . strlen($data),
						'MAGE_API:' . self::$_api['token']
				)
		);
	
		$result = ComScriptCURL::readUrl($url, null, array(), '', $extraOptions);
	
		$result = json_decode($result, true);
		if (isset($result['token']) && ($token = trim($result['token'])) !== '')
		{
			self::$_api['token'] = $token;
				
			//return $result;
		}
		else
		{
			$result = null;
		}
		return $result;
	}
	private static function getPricesBySku($sku)
	{
//		file_put_contents('/tmp/datafeed/web.log', __FILE__ .':' . __FUNCTION__ . ':' . __LINE__ . ':' . 'getPricesBySku' . PHP_EOL, FILE_APPEND | LOCK_EX);
		
// 		// using api to get price from crawler
// 		$params = $results = array();
// 		self::_login();
	
// 		$apiUrl = trim(self::$_api['URL']);
			
// 		$api_url = $apiUrl . 'PriceMatch/getPrices';
	
// 		// first try to find product by manufacturerPartNo
// 		$params = array('searchTxt' => 'sku = ?',
// 				'searchParams' =>  array($sku),
// 		);
	
// 		$data = json_encode($params);
// 		$results_api = self::_postJson($api_url, $data);
// 		//var_dump($results_api);
	
	
// 		$token = trim($results_api['token']);
// 		if ($token !== '')
// 		{
// 			$results = $results_api;
// 		}
// 		return $results;
//		}

		// directly access staticice to get prices
		$results = staticiceConnector::getPrices($sku);
// 		ob_start();
// 		var_dump($results);
// 		$content = ob_get_contents();
// 		ob_end_clean();
// 		file_put_contents('/tmp/datafeed/web.log', __FILE__ .':' . __FUNCTION__ . ':' . __LINE__ . ':' . $content . PHP_EOL, FILE_APPEND | LOCK_EX);
		
		return $results;

	}
	public static function getMatchPrices($sku)
	{
//		file_put_contents('/tmp/datafeed/web.log', __FILE__ .':' . __FUNCTION__ . ':' . __LINE__ . ':' . '----start getMatchPrices' . PHP_EOL, FILE_APPEND | LOCK_EX);
		$result = array();
		$priceMatchResults=self::getPricesBySku($sku);
		
		$priceMatchResults = $priceMatchResults['items'];
		$companies = PriceMatchCompany::getAll();

		foreach($priceMatchResults as $priceMatchResult)
		{
			if(($name = trim($priceMatchResult['name'])) === '')
				continue;
	
			$price = str_replace(' ', '', str_replace('$', '', str_replace(',', '', $priceMatchResult['price']) ) );
			$url = $priceMatchResult['url'];

				
			foreach ($companies as $company)
			{
				$companyAlias = $company->getCompanyAlias();
				
				if(strtolower($name) === strtolower($companyAlias))
				{
					$result[] = array('PriceMatchCompany'=> $company, 'price'=> $price, 'name'=> $name, 'url'=> $url);					
				}
			}
		}
		//file_put_contents('/tmp/datafeed/web.log', __FILE__ .':' . __FUNCTION__ . ':' . __LINE__ . ':' . '----end getMatchPrices' . PHP_EOL, FILE_APPEND | LOCK_EX);
		return $result;
	}
	
}
