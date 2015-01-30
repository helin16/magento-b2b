<?php
class BillExport_Xero extends ExportAbstract
{
	const DEFAULT_DUE_DELAY = "+7 day";
	protected static function _getData()
	{
		$now = new UDate();
		$now->setTimeZone('Australia/Melbourne');
		$now->modify('-1 day');
		$dataType = 'created';
		$receivingItems = ReceivingItem::getAllByCriteria($dataType . ' > :fromDate and ' . $dataType . ' < :toDate', array('fromDate' => $now->format('Y-m-d') . ' 00:00:00', 'toDate' => $now->format('Y-m-d') . '23:59:59'));
		$return = array();
		foreach($receivingItems as $receivingItem)
		{
			$purchaseOrder = $receivingItem->getPurchaseOrder();
			$supplier = $purchaseOrder->getSupplier();
			$product = $receivingItem->getProduct();
			$return[] = array(
				'ContactName' => $supplier->getContactName()
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
				,'InvoiceDate' => $now->modify('+1 day')->__toString()
				,'DueDate' => $now->modify('+1 day')->modify(self::DEFAULT_DUE_DELAY)->__toString()
				,'InventoryItemCode' => $product->getSku()
				,'Description'=> $product->getShortDescription()
				,'Quantity'=> $receivingItem->getQty()
				,'UnitAmount'=> $receivingItem->getUnitPrice()
				,'AccountCode'=> $product->getCostAccNo()
				,'TaxType'=> "GST on Income"
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