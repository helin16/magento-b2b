<?php
class BillExport_Xero extends ExportAbstract
{
	const DEFAULT_DUE_DELAY = "+0 day";
	protected static function _getData()
	{
		if(count(self::$_dateRange) === 0) {
			$yesterdayLocal = new UDate('now', 'Australia/Melbourne');
			$yesterdayLocal->modify('-1 day');
			$fromDate = new UDate($yesterdayLocal->format('Y-m-d') . ' 00:00:00', 'Australia/Melbourne');
			$fromDate->setTimeZone('UTC');
			$toDate = new UDate($yesterdayLocal->format('Y-m-d') . ' 23:59:59', 'Australia/Melbourne');
			$toDate->setTimeZone('UTC');
		} else {
			$fromDate = self::$_dateRange['start'];
			$toDate = self::$_dateRange['end'];
		}
		$dataType = 'created';
		$receivingItems = ReceivingItem::getAllByCriteria($dataType . ' >= :fromDate and ' . $dataType . ' < :toDate', array('fromDate' => trim($fromDate), 'toDate' => trim($toDate)));

		$now = new UDate();
		$now->setTimeZone('Australia/Melbourne');
		$formatArray = array();
		foreach($receivingItems as $receivingItem)
		{
			$product = $receivingItem->getProduct();
			if(!$product instanceof Product)
				continue;
			if(!array_key_exists(($key = trim($receivingItem->getPurchaseOrder()->getId() . '|' . $product->getId() . '|' . $receivingItem->getInvoiceNo())), $formatArray))
				$formatArray[$key] = $receivingItem;
			if($formatArray[$key]->getId() !== $receivingItem->getId())
				$formatArray[$key]->setQty($formatArray[$key]->getQty() + $receivingItem->getQty());
		}

		$return = array();
		foreach($formatArray as $key => $receivingItem)
		{
			$product = $receivingItem->getProduct();
			$purchaseOrder = $receivingItem->getPurchaseOrder();
			$supplier = $purchaseOrder->getSupplier();
			$return[] = array(
					'ContactName' => $supplier->getName()
					,'EmailAddress'=> $supplier->getEmail()
					,'POAddressLine1'=> ''
					,'POAddressLine2'=> ''
					,'POAddressLine3'=> ''
					,'POAddressLine4'=> ''
					,'POCity'=> ''
					,'PORegion'=> ''
					,'POPostalCode'=> ''
					,'POCountry'=> ''
					,'InvoiceNumber' => $receivingItem->getInvoiceNo()
					,'InvoiceDate' => ''
					,'DueDate' => trim($now->modify(self::DEFAULT_DUE_DELAY))
					,'InventoryItemCode' => $product->getSku()
					,'Description'=> ($description = trim($product->getShortDescription())) === '' ? $product->getName() : $description
					,'Quantity'=> $receivingItem->getQty()
					,'UnitAmount'=> $receivingItem->getUnitPrice()
					,'AccountCode'=> $product->getAssetAccNo()
					,'TaxType'=> "GST on Expenses"
					,'TrackingName1'=> ''
					,'TrackingOption1'=> ''
					,'TrackingName2'=> ''
					,'TrackingOption2'=> ''
					,'Currency'=> ''
			);
		}
		return $return;
	}
	protected static function _getMailTitle()
	{
		return 'Bills Export for Xero from last day';
	}
	protected static function _getMailBody()
	{
		return 'Please find the attached export from BudgetPC internal system for all the bills from last day to import to xero.';
	}
	protected static function _getAttachedFileName()
	{
		$now = new UDate();
		$now->setTimeZone('Australia/Melbourne');
		return 'bills_xero_' . $now->format('Y_m_d_H_i_s') . '.csv';
	}
}