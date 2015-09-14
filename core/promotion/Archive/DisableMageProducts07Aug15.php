<?php
ini_set('memory_limit','1024M');

require_once dirname(__FILE__) . '/../main/bootstrap.php';

Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
echo "Begin disableProducts from magento MELB TIME: " . UDate::now(UDate::TIME_ZONE_MELB) . "\n";

// config
$manufacturerIds = [23,75,97];
$categoryIds = [166];

// validation
$manufacturers = array();
foreach ($manufacturerIds as $id)
{
	if(($i = Manufacturer::get($id)) instanceof Manufacturer)
	{
		$manufacturers[] = $i;
		echo 'try to disable all product with manufactuer "' . $i->getName() . '"(' . $i->getId() . ')' . "\n";
	}
	else throw new Exception('Invalid manufactuer id "' . $id . '" passed in');
}
$manufacturerIds = array_map(create_function('$a', 'return $a->getId();'), $manufacturers);
foreach ($categoryIds as $id)
{
	if(($i = ProductCategory::get($id)) instanceof ProductCategory)
	{
		$categories[] = $i;
		echo 'try to disable all product with category "' . $i->getName() . '"(' . $i->getId() . ')' . "\n";
	}
	else throw new Exception('Invalid category id "' . $id . '" passed in');
}
$categoryIds = array_map(create_function('$a', 'return $a->getId();'), $categories);

// run
$products = Product::getProducts('', '',array(),$manufacturerIds,$categoryIds);
$count = 0;
echo 'found total ' . count($products) . ' products' . "\n";
foreach($products as $product)
{
	if($product->getStockOnHand() == 0 && $product->getStockOnOrder() == 0 && $product->getStockOnPO() == 0 && $product->getStockInParts() == 0)
	{
		$sku = $product->getSku();
		disableProduct($sku);
		$count++;
	}
}
echo 'total ' . $count . ' products changed' . "\n";

echo "Done disableProducts from magento MELB TIME: " . UDate::now(UDate::TIME_ZONE_MELB) . "\n";


function disableProduct($sku)
{
	$sku = trim($sku);
	$params = array();
	$params['status'] = '2'; // '2' means Disable in magento
	$script = CatelogConnector::getConnector(B2BConnector::CONNECTOR_TYPE_CATELOG,
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_WSDL),
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_USER),
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_KEY)
	)
	->updateProductInfo($sku,$params);
	echo 'product with sku "' . $sku . '" disabled in magento' ."\n";
}