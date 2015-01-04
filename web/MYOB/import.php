<?php

require_once '../bootstrap.php';

echo '<pre>';
Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));

// configuration
// $fileName = "SKU-match-16K.csv";
// $fileName = "2015-01-02-MYOB-ME-PS-LCD.csv";
$fileName = "2015-01-02-MYOB-BAG-CA-CPU-DW-GC-HD-MB-NB.csv";
$codeType = 'EAN';

$start = memory_get_usage(); // monite mem usage

echo '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">'; // optional, bootstrap just for looking

    try
    {
    	Dao::beginTransaction();
    	
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
    	
    	$totalCount = $totalExist = $totalNew = $totalMYOBExist = $totalMYOBNew =  $totalError = $row = 0;
    	while (($data = fgetcsv($handle, 100000, ',')) !== FALSE) {
    		$row++;
    		echo '<tr>';
    		if($fileTitle[0] === 'sku' || $fileTitle[0] === 'SKU')
    		{
    			$sku = trim($data[0]);
    			if(!empty($sku))
    			{
	    			if(!($product = Product::getBySku($sku)) instanceof Product)
	    				throw new Exception('<pre>Invalid Product SKU!' . ' SKU: '. $sku . ', myob-code: '. trim($data[1]) . ', in ' . $fileName . ' line '. ($row+1) );
	    			echo '<td><a target="_blank" href="/product/' . $product->getId() . '.html">' . $sku . '</a></td>';
    			}
    		}
    		else throw new Exception('<pre>first column title must be sku');
    		if($fileTitle[1] === 'MYOB-code' || $fileTitle[1] === 'Item ' . "#" || $fileTitle[1] === 'code' || $fileTitle[1] === 'CODE')
    		{
    			$myobCode = trim($data[1]);
    			if(!empty($myobCode) && !empty(trim($sku)))
    			{
	    			$position = strpos($myobCode, '-');
	    			$myobCode = substr($myobCode, $position+1);	// get everything after first dash
	    			$myobCode = str_replace(' ', '', $myobCode); // remove all whitespace
	    			echo '<td>' . $myobCode . '</td>';
	    			echo '<td>' . $product->getSku() . '</td>';
	    			
	    				if(count($productCodes = ProductCode::getAllByCriteria('pro_code.typeId = ? and pro_code.productId = ?', array($productCodeType->getId(), $product->getId()), true,1 ,1 ) ) > 0 )
	    				{
	    					$productCodes[0]->setCode($myobCode)->save();
	    					$totalExist++;
	    				}
	    				else
	    				{
	    					ProductCode::create($product, $productCodeType, trim($myobCode));
		    				$totalNew++;  // just a counter
	    				}
	    				
	    				// MYOB code
	    				if(count($productCodes = ProductCode::getAllByCriteria('pro_code.typeId = ? and pro_code.productId = ?', array(ProductCodeType::ID_MYOB, $product->getId()), true,1 ,1 ) ) > 0 )
	    				{
	    					$productCodes[0]->setCode($myobCode)->save();
	    					$totalMYOBExist++;
	    				}
	    				else
	    				{
	    					ProductCode::create($product, ProductCodeType::get(ProductCodeType::ID_MYOB), trim($myobCode));
		    				$totalMYOBNew++;  // just a counter
	    				}
    			}
    		}
    		else throw new Exception('<pre>2nd column title must be MYOB-code');
    		echo '</tr>';
    		$totalCount ++;
    		// clear up
    		$sku = $myobCode = $position = $products = $product = null;
    	}
    	echo '</tbody></table>';
    	// result summery, note: all count starts at 0
    	echo '<br/><b>Total Count: ' .$totalCount . '</b>(exist: '. $totalExist . ', new: '. $totalNew . ', MYOBexist: '. $totalMYOBExist . ', MYOBnew: '. $totalMYOBNew/* .  ', Error: ' . $totalError*/ . ')';
    	
    	Dao::commitTransaction();
    }
    catch(Exception $e) {
    	Dao::rollbackTransaction();
        echo $e;
        exit;
    }
    
echo '<br/><br/><br/>DONE (Memory Usage: ' . (memory_get_usage() - $start)/1024 . ' KB)</br>';