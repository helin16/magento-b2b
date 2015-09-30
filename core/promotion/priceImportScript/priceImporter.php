<?php
require_once dirname(__FILE__) . '/../../main/bootstrap.php';
try {
// 	$sku = "2L-1001P/C";
	$connector = CatelogConnector::getConnector(B2BConnector::CONNECTOR_TYPE_CATELOG,
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_WSDL),
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_USER),
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_KEY));
	$objPHPExcel = PHPExcel_IOFactory::load("priceList.xlsx");
	//  Get worksheet dimensions
	$sheet = $objPHPExcel->getSheet(0);
	$highestRow = $sheet->getHighestRow();
	$highestColumn = $sheet->getHighestColumn();
	
	//  Loop through each row of the worksheet in turn
	for ($row = 1; $row <= $highestRow; $row++) {
		//  Read a row of data into an array
		$rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
		$sku = trim($rowData[0][0]);
		$price = round($rowData[0][1], 2);
		echo "Row: ".$row."- SKU: ". $rowData[0][0] .", Price: ". $price ;
		$connector->updateProductPrice($sku, $price);
		echo " ... done\n" ;
	}
}
catch (Exception $e)
{
	echo "Error:";
	echo $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
	throw $e;
}
?>