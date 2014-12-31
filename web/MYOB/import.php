<?php

require_once '../bootstrap.php';

echo '<pre>';
Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));

$fileName = "SKU-match-16K.csv";
$codeType = 'UPC';
$start = memory_get_usage();

echo '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">';

    try
    {
    	if(!sizeof($fileName))
    		throw new Exception('Invalid File Name!');
    	if(trim($codeType) === 'UPC' || trim($codeType) === 'upc')
    		$productCodeType = ProductCodeType::get(ProductCodeType::ID_UPC);
    	else if(trim($codeType) === 'EAN' || trim($codeType) === 'ean')
    		$productCodeType = ProductCodeType::get(ProductCodeType::ID_EAN);
    	else throw new Exception('Invalid Code Type');
    	
    	$handle = fopen($fileName, "r");
    	
    	$fileTitle = fgetcsv($handle);
    	echo '<table class="table table-striped">';
    	echo '<thead><tr>';
    	foreach ($fileTitle as $title)
    		echo '<th>' . $title . '</th>';
    	echo '</tr></thead><tbody>';
    	
    	$totalCount = $totalExist = $totalNew = 0;
    	while (($data = fgetcsv($handle, 100000, ',')) !== FALSE) {
    		echo '<tr>';
    		if($fileTitle[0] === 'sku' || $fileTitle[0] === 'SKU')
    		{
    			$sku = $data[0];
    			if(!($product = Product::getBySku($sku)) instanceof Product)
    				throw new Exception('Invalid Product!');
    			echo '<td>' . $sku . '</td>';
    		}
    		else throw new Exception('first column title must be sku');
    		if($fileTitle[1] === 'MYOB-code' || $fileTitle[1] === 'myob-code' || $fileTitle[1] === 'code' || $fileTitle[1] === 'CODE')
    		{
    			$myobCode = $data[1];
    			$position = strpos($myobCode, '-');
    			$myobCode = substr($myobCode, $position+1);	// get everything after first dash
    			$myobCode = str_replace(' ', '', $myobCode); // remove all whitespace
    			if(count($products = ProductCode::getAllByCriteria('pro_code.productId = ? and pro_code.typeId = ?', array($product->getId(), ProductCodeType::ID_UPC), false, 1, 1) ) > 0 )
    			{
    				$products[0]->setCode(trim($myobCode));
    				$totalExist++;
    			}
    			else
    			{
    				ProductCode::create($product, $productCodeType, trim($myobCode));
    				$totalNew++;
    			}
    			echo '<td>' . $myobCode . '</td>';
    		}
    		else throw new Exception('first column title must be MYOB-code');
    		echo '</tr>';
    		$totalCount ++;
    		// clear up
    		$sku = $myobCode = $position = $products = $product = null;
    	}
    	echo '</tbody></table>';
    	echo '<br/><b>Total Count: ' .$totalCount . '</b>(exist: '. $totalExist . ', new: '. $totalNew . ')';
    }
    catch(HTML2PDF_exception $e) {
        echo $e;
        exit;
    }
    
echo '<br/><br/><br/>DONE (Memory Usage: ' . (memory_get_usage() - $start)/1024 . ' KB)</br>';