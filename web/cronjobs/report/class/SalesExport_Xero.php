<?php
class SalesExport_Xero extends ExportAbstract
{
	/**
	 * @return PHPExcel
	 */
	private static function _getOutput()
	{
		$phpexcel= new PHPExcel();
		//sample from /magento-b2b/core/3rdParty/PHPExcel/Examples/05featuredemo.inc.php
		//*ContactName	EmailAddress	POAddressLine1	POAddressLine2	POAddressLine3	POAddressLine4	POCity	PORegion	POPostalCode	POCountry	*InvoiceNumber	Reference	*InvoiceDate	*DueDate	InventoryItemCode	*Description	*Quantity	*UnitAmount	Discount	*AccountCode	*TaxType	TrackingName1	TrackingOption1	TrackingName2	TrackingOption2	Currency	BrandingTheme
		//customer name										invoice number		invoice date	invoice date	sku	product short description	OrderItem.orderQty	OrderItem.UnitPrice		Product.revenueCode	GST on Income	
		$data = self::_getData();
		foreach($data as $row)
		{
			foreach($row as $col)
			{
				//populate the row into phpexcel
			}
		}					
		return $phpexcel;
	}
	private static function _getData()
	{
		$now = new UDate();
		$now->setTimeZone('Australia/Melbourne');
		$now->modify('-1 day');
		$orders = Order::getAllByCriteria('InvoiceDate > :fromDate and InvoiceDate < :toDate', array('fromDate' => $now->format('Y-m-d') . '00:00:00', 'toDate' => $now->format('Y-m-d') . '23:59:59'));
		$return = array();
		foreach($orders as $order)
		{
			$row = array(
				'customerName' => $order->getCustomer->getName(),
				'invoiceNo' => $order->getInvoiceNo(),
				'invoiceDate' => $order->getInvoiceDate()
			);
			foreach($order->getOrderItems() as $item)
			{
				$return[] = array_merge($row, array(
					'Description' => $item->getProduct()->getShortDecription(),
					//todo!
				));
			}
		}
	}
	protected static function getMailTitle()
	{
		return 'Sales Export for Xero from last day';
	}
	protected static function getMailBody()
	{
		return 'Please find the attached export from BudgetPC internal system for all the sales from last day to import to xero.';
	}
	protected static function _getAttachedFileName()
	{
		$now = new UDate();
		$now->setTimeZone('Australia/Melbourne');
		return 'sales_xero_' . $now->format('Y_m_d_H_i_s') . '.csv';
	}
}