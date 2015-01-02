<?php

require_once '../bootstrap.php';

echo '<pre>';
Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));

// configuration
$fileName = "SKU-match-16K.csv";
$codeType = 'EAN';

$start = memory_get_usage(); // monite mem usage

echo '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">'; // optional, bootstrap just for looking

    try
    {
    	// validate csv
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
    			echo '<td><a target="_blank" href="/product/' . $product->getId() . '.html">' . $sku . '</a></td>';
    		}
    		else throw new Exception('first column title must be sku');
    		if($fileTitle[1] === 'MYOB-code' || $fileTitle[1] === 'myob-code' || $fileTitle[1] === 'code' || $fileTitle[1] === 'CODE')
    		{
    			$myobCode = $data[1];
    			$position = strpos($myobCode, '-');
    			$myobCode = substr($myobCode, $position+1);	// get everything after first dash
    			$myobCode = str_replace(' ', '', $myobCode); // remove all whitespace
    			echo '<td>' . $myobCode . '</td>';
    			if(count($productCodes = ProductCode::getAllByCriteria('pro_code.typeId = ? and pro_code.code = ?', array($productCodeType->getId(), $myobCode), false ) ) > 0 )
    			{
	    			echo '<td>' . $productCodes[0]->getProduct()->getSku() . '</td>';
    				// any product with such code with such code type
    				foreach ($productCodes as $productCode)
    				{
    					if($productCode->getProduct() === $product) // if is this product
    					{
    						$productCode->setActive(true);
    						$productCode->setCode($myobCode);
    						$productCode->save();
    					}
    					else // if not this product with such code with such code type, deactive it
    					{
    						$productCode->setActive(false)->save();
    					}
    					$totalExist++;
    				}
    			}
    			else
    			{
    				// if it's a new code for such product
    				ProductCode::create($product, $productCodeType, trim($myobCode));
    				$totalNew++;  // just a counter
    			}
    		}
    		else throw new Exception('first column title must be MYOB-code');
    		echo '</tr>';
    		$totalCount ++;
    		// clear up
    		$sku = $myobCode = $position = $products = $product = null;
    	}
    	echo '</tbody></table>';
    	// result summery, note: all count starts at 1
    	echo '<br/><b>Total Count: ' .$totalCount . '</b>(exist: '. $totalExist . ', new: '. $totalNew . ')';
    }
    catch(Exception $e) {
        echo $e;
        exit;
    }
    
echo '<br/><br/><br/>DONE (Memory Usage: ' . (memory_get_usage() - $start)/1024 . ' KB)</br>';