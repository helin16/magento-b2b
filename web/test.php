<?php
require_once 'bootstrap.php';
Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));

function getProductArray($product, $pro) 
{
	if(is_null($pro) || !isset($pro->additional_attributes))
		return array();
	$proArray = array();
	$proArray['sku'] = trim($product->sku);
	$proArray['name'] = trim($product->name);
	$proArray['product_id'] = trim($product->product_id);
	$proArray['attributeSetId'] = trim($pro->set);
	foreach($pro->additional_attributes as $row)
		$proArray[$row->key] = trim($row->value);
	return $proArray;
}

function removeLineFromFile($fileName, $line)
{
	$contents = file_get_contents($fileName);
	$contents = str_replace($line, '', $contents);
	file_put_contents($fileName, $contents);
}

function updateProduct($pro, $fileName, $line)
{
	$clientScript = getClientScript();
	try{
		$transStarted = false;
		try {Dao::beginTransaction();} catch(Exception $e) {$transStarted = true;}
		$sku = trim($pro['sku']);
		$product = Product::getBySku($pro['sku']);
		
		$mageId = trim($pro['product_id']);
		$name = trim($pro['name']);
		$short_description = trim($pro['short_description']);
		$description = trim($pro['description']);
		$weight = trim($pro['weight']);
		$statusId = trim($pro['status']);
		$price = trim($pro['price']);
		$specialPrice = trim($pro['special_price']);
		$specialPrice_From = trim($pro['special_from_date']) === '' ? trim($pro['special_from_date']) : null;
		$specialPrice_To = trim($pro['special_to_date']) === '' ? trim($pro['special_to_date']) : null;
		$supplierName = trim($pro['supplier']);
		$attributeSet = ProductAttributeSet::get(trim($pro['attributeSetId']));
		
		if(!$product instanceof Product)
			$product = Product::create($sku, $name);
		
		$asset = (($assetId = trim($product->getFullDescAssetId())) === '' || !($asset = Asset::getAsset($assetId)) instanceof Asset) ? Asset::registerAsset('full_desc_' . $sku, $description, Asset::TYPE_PRODUCT_DEC) : $asset;
		$product->setName($name)
			->setMageId($mageId)
			->setAttributeSet($attributeSet)
			->setShortDescription($short_description)
			->setFullDescAssetId(trim($asset->getAssetId()))
			->setIsFromB2B(true)
			->setStatus(ProductStatus::get($statusId))
			->setSellOnWeb(true)
			->setManufacturer($clientScript->getManufacturerName(trim($pro['manufacturer'])))
			->save()
			->clearAllPrice()
			->addPrice(ProductPriceType::get(ProductPriceType::ID_RRP), $price)
			->addInfo(ProductInfoType::ID_WEIGHT, $weight);
			
		if($specialPrice !== '')
			$product->addPrice(ProductPriceType::get(ProductPriceType::ID_CASUAL_SPECIAL), $specialPrice, $specialPrice_From, $specialPrice_To);
		
		if($supplierName !== '')
			$product->addSupplier(Supplier::create($supplierName, $supplierName, true));
		
		if(isset($pro['categories']) && count($pro['categories']) > 0)
		{
			$product->clearAllCategory();
			foreach($pro['categories'] as $cateMageId)
			{
				if(!($category = ProductCategory::getByMageId($cateMageId)) instanceof ProductCategory)
					continue;
				$product->addCategory($category);
			}
		}
		
		if($transStarted === false)
			Dao::commitTransaction();
		
		//TODO remove the file
		removeLineFromFile($fileName, $line);
		
		echo $product->getId() . " => done! \n";
		
	} catch(Exception $ex) {
			
		if($transStarted === false)
			Dao::rollbackTransaction();
		throw $ex;
	}
}

function processFile($filename, $clientScript)
{
	//read the file
	$contents = file($filename);
	DaoMap::loadMap('Product');
	$skuSizeLimit = DaoMap::$map['product']['sku']['size'];
	
	foreach($contents as $line) {
		$pro = json_decode(trim($line), true);
		if(strlen($pro['sku']) > $skuSizeLimit)
			continue;
		updateProduct($pro, $clientScript, $filename, $line);
	}
}

function getClientScript()
{
	$wsdl = 'http://www.budgetpc.com.au/api/v2_soap?wsdl=1';
	$clientScript = CatelogConnector::getConnector(B2BConnector::CONNECTOR_TYPE_CATELOG, $wsdl, 'B2BUser', 'B2BUser');
	return $clientScript;
}

function downloadFile($cacheFile)
{
	$clientScript = getClientScript();
	$products = $clientScript->getProductList('2015-08-07', '');
	file_put_contents($cacheFile, '');
	foreach($products as $product)
	{
		$pro = $clientScript->getProductInfo(trim($product->sku));
		$proArray = getProductArray($product, $pro);
		if(count($proArray) > 0)
			file_put_contents($cacheFile, json_encode($proArray) . "\n", FILE_APPEND);
	}
	echo "File :" . $cacheFile . ' downloaded.';
}

try {
	echo "Begin" . "\n<pre>";
	
	$cacheFile = '/tmp/mageProduct.json';
	
// 	downloadFile($cacheFile);
	//process file
	processFile($cacheFile);

} catch (SoapFault $e) {
	var_dump($e);
	throw $e;
}
?>