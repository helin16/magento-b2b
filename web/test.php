<?php

$categories = array();

function getProductArray($clientScript, $session, $product, $pro, $attributeSets, $manufacturers)
{
	if(is_null($pro) || !isset($pro->additional_attributes))
		return array();
	$proArray = array();
	$proArray['sku'] = trim($product->sku);
	$proArray['name'] = trim($product->name);
	$proArray['product_id'] = trim($product->product_id);
	$proArray['attributeSet'] = null;
	if(isset($attributeSets[trim($pro->set)]))
			$proArray['attributeSet'] = array('id' => trim($pro->set), 'name' => trim($attributeSets[trim($pro->set)]));
	foreach($pro->additional_attributes as $row)
		$proArray[$row->key] = trim($row->value);
	if(isset($proArray['manufacturer']) && isset($manufacturers[($manId = $proArray['manufacturer'])]))
		$proArray['manufacturer'] = array('id' => $manId, 'name' => trim($manufacturers[$manId]));
	
	$proArray['categories'] = array();
	if(is_array($pro->category_ids) && count($pro->category_ids) > 0 ) {
		foreach($pro->category_ids as $categoryId) {
			if(!isset($categories[$categoryId])) {
				$categoryMage = $clientScript->catalogCategoryInfo($session, $categoryId);
				$categories[$categoryId] = trim($categoryMage->name);
			}
			
			$proArray['categories'][] = array('id' => $categoryId, 'name' => $categories[$categoryId]);
		}
	}
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
	$clientScript = CatelogConnector::getConnector(B2BConnector::CONNECTOR_TYPE_CATELOG, getWSDL(), 'B2BUser', 'B2BUser');;
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

function getWSDL()
{
	return 'http://www.budgetpc.com.au/api/v2_soap?wsdl=1';
}

function getInfoAttributes()
{
	$attributeName = array('name', 'product_id', 'short_description', 'description', 'manufacturer', 'man_code', 'news_from_date', 'news_to_date', 'price', 'supplier', 'weight', 'status', 'special_price', 'special_from_date', 'special_to_date');
	$attributes = new stdclass();
	$attributes->additional_attributes = $attributeName;
	return $attributes;
}

function downloadFile($cacheFile)
{
	file_put_contents($cacheFile, '');
	$options = array('exceptions' => true, 'trace'=> true, 'encoding'=>'utf-8');
	$clientScript = new SoapClient(getWSDL(), $options);
	$session = $clientScript->login('B2BUser', 'B2BUser');

	$array = array();
// 	$array[] = array('key'=>'created_at','value'=>array('key' =>'from','value' => trim('0001-01-01')));
	$array[] = array('key'=>'created_at','value'=>array('key' =>'from','value' => trim('2015-08-03')));
	$params = array('complex_filter' => $array);
	$products = $clientScript->catalogProductList($session, $params);
	
	$attributeSets = array();
	$attributeSetsMage = $clientScript->catalogProductAttributeSetList($session);
	foreach($attributeSetsMage as $attributeSetMage) {
		$attributeSets[$attributeSetMage->set_id] = trim($attributeSetMage->name);
	}
	echo "Attribute Sets:\n" . print_r($attributeSets, true);
	echo "\n";
	
	$manufacturers = array();
	$manufacturersMage = $clientScript->catalogProductAttributeOptions($session, 'manufacturer');
	foreach($manufacturersMage as $manufacturerMage) {
		if(trim($manufacturerMage->value) !== '')
			$manufacturers[$manufacturerMage->value] = trim($manufacturerMage->label);
	}
	echo "Manufacturers:\n" . print_r($manufacturers, true);
	echo "\n";
	
	echo "Got " . count($products) . " products\n";
	foreach($products as $index => $product)
	{
		try {
		echo "No.: " . $index . ", SKU:" . $product->sku . "\n";
		$pro = $clientScript->catalogProductInfo($session, trim($product->sku), null, getInfoAttributes());
		$proArray = getProductArray($clientScript, $session, $product, $pro, $attributeSets, $manufacturers);
		echo "\t JSON: " . json_encode($proArray) . "\n";
		if(count($proArray) > 0)
			file_put_contents($cacheFile, json_encode($proArray) . "\n", FILE_APPEND);
		} catch (SoapFault $e) {
			var_dump ($e);
		}
	}
	echo "File :" . $cacheFile . ' downloaded.';
}

try {
	echo "Begin" . "\n<pre>";

	$cacheFile = '/tmp/mageProduct.json';

	downloadFile($cacheFile);
	//process file
// 	processFile($cacheFile);

} catch (SoapFault $e) {
	var_dump($e);
	throw $e;
}
?>
