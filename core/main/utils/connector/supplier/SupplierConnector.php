<?php
abstract class SupplierConnector
{
	public static function processDatafeed(Supplier $supplier, array $data)
	{
		$base = dirname(__FILE__);
		if(file_exists($file_name = ($base . '/datafeed/' . strtolower($supplier->getName()) . '.php')) === true)
			require_once $file_name;
		$class= ucfirst($supplier->getName()) . 'Connector';
		return $class::run($data);
	}
}