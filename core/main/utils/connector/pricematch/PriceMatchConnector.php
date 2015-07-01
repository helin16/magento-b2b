<?php
class PriceMatchConnector
{
	/**
	 * Product for PriceMatchConnector
	 *
	 * @var Product
	 */
	private $product;
	/**
	 * companies for PriceMatchConnector
	 *
	 * @var array
	 */
	private $companies;
	/**
	 * price_from for PriceMatchConnector
	 *
	 * @var double
	 */
	private $price_from;
	/**
	 * price_to for PriceMatchConnector
	 *
	 * @var double
	 */
	private $price_to;

	/**
	 * getter for product
	 *
	 * @return Product
	 */
	public function getProduct()
	{
	    return $this->product;
	}
	/**
	 * Setter for product
	 *
	 * @return PriceMatchConnector
	 */
	public function setProduct($product)
	{
	    $this->product = $product;
	    return $this;
	}
	/**
	 * getter for companies
	 *
	 * @return array
	 */
	public function getCompanies()
	{
	    return $this->companies;
	}
	/**
	 * Setter for companies
	 *
	 * @return PriceMatchConnector
	 */
	public function setCompanies($companies)
	{
	    $this->companies = $companies;
	    return $this;
	}
	/**
	 * getter for price_from
	 *
	 * @return double
	 */
	public function getPrice_from()
	{
	    return $this->price_from;
	}
	/**
	 * Setter for price_from
	 *
	 * @return PriceMatchConnector
	 */
	public function setprice_from($price_from)
	{
	    $this->price_from = $price_from;
	    return $this;
	}
	/**
	 * getter for price_to
	 *
	 * @return double
	 */
	public function getPrice_to()
	{
	    return $this->price_to;
	}
	/**
	 * Setter for price_to
	 *
	 * @return PriceMatchConnector
	 */
	public function setPrice_to($price_to)
	{
	    $this->price_to = $price_to;
	    return $this;
	}
	public static function runAllProduct(array $companies, $echo = false, $clearAll = false)
	{
		if(count($companies) === 0)
			throw new Exception('must get at least one company to compare price');

		// clean up if requested
		if($clearAll === true)
		{
			echo "clear all PriceMatchMin" . "\n";
			PriceMatchMin::deleteByCriteria('id <> 0'); // this will delete all b/c id will never be 0
			echo "clear all PriceMatchRecord" . "\n";
			PriceMatchRecord::deleteByCriteria('id <> 0'); // this will delete all b/c id will never be 0
		}
		$sql = "select distinct p.id `pId` from product p where p.active = 1";
		$productIds = Dao::getResultsNative($sql, array(), PDO::FETCH_ASSOC);
		foreach ($productIds as $row)
		{
			$i = Product::get($row['pId']);
			try {
				Dao::beginTransaction();
				$j = self::run($i, $companies);
				if($echo === true)
					echo 'Product (sku = ' . $j->getSku() . '), min price: ' . ($j->getRecord() instanceof PriceMatchRecord ? $j->getRecord()->getPrice() . '(' . $j->getRecord()->getCompany()->getCompanyName() . ')' : 'N/A') . ')' . "\n";
				Dao::commitTransaction();
				unset($i);
				unset($j); // free up memory
			} catch (Exception $e)
			{
				Dao::rollbackTransaction();
				echo "****ERROR****" . "\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
			}
// 			break;
// 			sleep(3);
		}
		return '';
	}
	/**
	 * runner for PriceMatchConnector
	 *
	 * @param Product $product
	 * @param array $companies
	 * @return PriceMatchMin
	 * @throws Exception
	 */
	public static function run(Product $product, array $companies, $price_from = '', $price_to = '')
	{
		if(count($companies) === 0)
			throw new Exception('must get at least one company to compare price');
		foreach ($companies as $company)
		{
			if(!$company instanceof PriceMatchCompany)
				throw new Exception('must passin an array of PriceMatchCompany Object');
		}

		$class = new self();
		$class->setProduct($product)->setCompanies($companies);

		$prices = $class->priceMatchProduct();
		if(($i = $class->priceMatchProduct()) && isset($i['companyPrices']) && is_array($i['companyPrices']) && count($i['companyPrices'])) // get price for all companies for given sku
		{
			$class->recordResult($i['companyPrices']); // put price match result for all companies into PriceMatchRecard Table
			$min = $class->getMinPrice(); // get the 'min' price under given limit (so far price range and conpany selections), such limit is in $this

			return $min; // $min is an entry of PriceMatchMin, is nothing find $min->getRecord() will be null
		}

		throw new Exception('cannot get any price for product(id=' . $product->getId() . ', sku=' . $product->getSku() . ') and companiesIds="' . join(', ', array_map(create_function('$a', 'return $a->getId();'), $companies))) . '")';
	}
	/**
	 * put given price match result for all companies into PriceMatchRecord table
	 *
	 * @param unknown $companyPrices
	 * @return PriceMatchConnector
	 */
	private function recordResult($companyPrices)
	{
		foreach ($companyPrices as $companyPrice)
		{
			$price = $companyPrice['price'];
			$url = $companyPrice['priceURL'];
			$company = PriceMatchCompany::get($companyPrice['PriceMatchCompanyId']);
			$min = PriceMatchMin::create($this->getProduct()->getSku()); // to create PriceMatchRecord must have a PriceMatchMin, the record for PriceMatchMin will be null at this time instance

			if(abs(doubleval($price)) !== 0.0 && doubleval($price) > 0.0 && trim($price) !== '') // price must be positive (non-zero), otherwise will be rejected by Core::PriceMatchRecord::create()
			{
				PriceMatchRecord::create($company, $min, $price, $url);
			}
		}
		return $this;
	}
	/**
	 * get the min price. this is a wrap up of PriceMatchMin->getMin()
	 *
	 * @throws Exception
	 * @return PriceMatchMin
	 */
	private function getMinPrice()
	{
		$companyIds = array_map(create_function('$a', 'return $a->getId();'), $this->getCompanies());

		// a PriceMatchMin for this->getProduct()->getSku() must exist
		$min = PriceMatchMin::getAllByCriteria('sku = ?', array($this->product->getSku()), true, 1, 1, array('id'=>'desc'));
		if(count($min) === 0)
			throw new Exception('not able to find PriceMatchMin for sku ' . $this->getProduct()->getSku() . '. min should be created before record');
		else $min = $min[0];

		$min = $min->getMin(array('componieIds'=>$companyIds, 'price_from'=>$this->getPrice_from(), 'price_to'=>$this->getPrice_to()));

		return $min;
	}
	/**
	 * Getting price matching information, this a wrapper of old pricematch aka. PriceMatcher::getPrices()
	 *
	 * @param unknown $sender
	 * @param unknown $param
	 */
	private function priceMatchProduct()
	{
		$product = $this->product;
		$prices = ProductPrice::getPrices($product, ProductPriceType::get(ProductPriceType::ID_RRP));
		$companies = PriceMatcher::getAllCompaniesForPriceMatching();

		$prices = PriceMatcher::getPrices($companies, $product->getSku(), (count($prices)===0 ? 0 : $prices[0]->getPrice()) );

		$myPrice = $prices['myPrice'];
		$minPrice = $prices['minPrice'];
		$msyPrice = $prices['companyPrices']['MSY'];
		$prices['product'] = $product;

		return $prices;
	}
}