<?php

require_once 'bootstrap.php';

$product = CatelogConnector::getConnector(B2BConnector::CONNECTOR_TYPE_CATELOG, SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_WSDL), SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_USER), SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_KEY))
	->getProductInfo('FVS336G-V2');
var_dump($product);


// /// List all the Regional Franchises ///
// // try 
// // {
// // 	$fwc = FastWayConnector::getConnector(FactoryAbastract::service('Courier')->get(3));
// // 	var_dump($fwc->getListOfAllRFsByCountry(null, 1));

// // }
// // catch(Exception $ex)
// // {
// // 	var_dump($ex->getMessage());
// // }
// ///////////////////////////////////////

// /// List all the Regional Franchises ///
// try 
// {
// 	$fwc = CourierConnector::getConnector(FactoryAbastract::service('Courier')->get(3));
// 	$manifest =$fwc->createManifest();
// 	var_dump($manifest);
// 	$shippment = FactoryAbastract::service('Shippment')->get(1);
// 	var_dump('createConsignment: ');
// 	var_dump($consignemt = $fwc->createConsignment($shippment, $manifest->ManifestID));
// // 	var_dump('removeManifest: ');
// // 	$fwc->removeManifest($manifest->ManifestID);
// 	var_dump('getConsignments: ');
// 	var_dump($fwc->getConsignments($manifest->ManifestID));
// 	var_dump('listOpenManifests: ');
// 	var_dump($fwc->listOpenManifests());
// 	var_dump('closeManifest: ');
// 	var_dump($fwc->closeManifest($manifest->ManifestID));
// 	var_dump('getTrackingURL: ');
// 	var_dump($fwc->getTrackingURL($consignemt->LabelNumbers[0]));
// }
// catch(Exception $ex)
// {
// 	var_dump($ex->getMessage());
// }
// ///////////////////////////////////////


// // try 
// // {
// // 	$fwc = FastWayConnector::getConnector(FactoryAbastract::service('Courier')->get(3));
// // 	$suburbArray = $fwc->getListOfDeliverySuburbs('MEL');
	
// // 	foreach($suburbArray as $suburb)
// // 	{
// // 		var_dump('Town :' . $suburb->Town);
// // 		var_dump('PostCode :' . $suburb->Postcode);
// // 		var_dump('State :' . $suburb->State);
// // 		var_dump('Label :' . $suburb->label);
// // 		var_dump('---------------------');
// // 	}
// // }
// // catch(Exception $ex)
// // {
// // 	var_dump($ex->getMessage());
// // }




// // $wsdl = 'http://hairdemo.websiteforyou.com.au/api/v2_soap/?wsdl';
// // $options = array('exceptions' => true, 'encoding'=>'utf-8', 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP);
// // $options = array_merge($options, array('proxy_host' => "proxy.bytecraft.internal",'proxy_port' => 3128));
// // $client = new SoapClient($wsdl, $options);
// // $functions = $client->__getFunctions();
// // var_dump($functions);

// // $session = $client->login('B2BUser', 'B2BUser');
// // var_dump($session);

// // $filter = array('filter' => array(
// //                     'key' => 'created_at',
// //                     'value' => '2014-03-21 12:12:07',
// //                 )
// // );
// // try {
// // $result = $client->salesOrderList($session, $filter);
// // }
// // catch(Exception $ex)
// // {
// // 	$client->
// // 	var_dump($ex->getMessage());
// // }
// //var_dump($result);
