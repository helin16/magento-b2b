<?php
class SalesExport_Xero extends ExportAbstract
{
	const DEFAULT_DUE_DATE = "+7 day";
	/**
	 * @return PHPExcel
	 */
	protected static function _getOutput()
	{
		$phpexcel= new PHPExcel();
		$data = self::_getData();
		$activeSheet = $phpexcel->setActiveSheetIndex(0);
		if(count($data) === 0)
		{
			$activeSheet->setCellValue('A1', 'Nothing to export!');
			return $phpexcel;
		}
		$letter = 'A';
		$number = 1; // excel start at 1 NOT 0
		// header row
		foreach($data as $row)
		{
			foreach($row as $key => $value)
			{
				if(parent::$_debug)
					echo $letter . $number . ': ' . $key . "\n";
				$activeSheet->setCellValue($letter++ . $number, $key);
			}
			$number++;
			$letter = 'A';
			if(parent::$_debug)
				echo "\n";
			break; // only need the header
		}
		foreach($data as $row)
		{
			foreach($row as $col)
			{
				if(parent::$_debug)
					echo $letter . $number . ': ' . $col . "\n";
				$activeSheet->setCellValue($letter++ . $number, $col);
			}
			$number++;
			$letter = 'A';
			if(parent::$_debug)
				echo "\n";
		}			
		return $phpexcel;
	}
	private static function _getData()
	{
		$now = new UDate();
		$now->setTimeZone('Australia/Melbourne');
		$now->modify('-1 day');
		$orders = Order::getAllByCriteria('invDate > :fromDate and invDate < :toDate', array('fromDate' => $now->format('Y-m-d') . ' 00:00:00', 'toDate' => $now->format('Y-m-d') . '23:59:59'));
		$return = array();
		foreach($orders as $order)
		{
			//common fields
			$customer = $order->getCustomer();
			$row = array(
				'CustomerName' => $customer->getName()
				,'EmailAddress'=> $customer->getEmail()
				,'POAddressLine1'=> ''
				,'POAddressLine2'=> ''
				,'POAddressLine3'=> ''
				,'POAddressLine4'=> ''
				,'POCity'=> ''
				,'PORegion'=> ''
				,'POPostalCode'=> ''
				,'POCountry'=> ''
				,'InvoiceNumber' => $order->getInvNo()
				,'Reference'=> ''
				,'InvoiceDate' => $order->getInvDate()->setTimeZone('Australia/Melbourne')->__toString()
				,'DueDate' => $order->getInvDate()->setTimeZone('Australia/Melbourne')->modify(self::DEFAULT_DUE_DATE)->__toString()
			);
			foreach($order->getOrderItems() as $orderItem)
			{
				$product = $orderItem->getProduct();
				$return[] = array_merge($row, array(
					'InventoryItemCode' => $product->getSku()
					,'Description'=> $product->getShortDescription()
					,'Quantity'=> $orderItem->getQtyOrdered()
					,'UnitAmount'=> $orderItem->getUnitPrice()
					,'Discount'=> ''
					,'AccountCode'=> $product->getRevenueAccNo()
					,'TaxType'=> "GST on Income"
					,'TrackingName1'=> ''
					,'TrackingOption1'=> ''
					,'TrackingName2'=> ''
					,'TrackingOption2'=> ''
					,'Currency'=> ''
					,'BrandingTheme'=> ''
				));
			}
		}
		return $return;
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