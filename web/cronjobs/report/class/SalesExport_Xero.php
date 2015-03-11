<?php
class SalesExport_Xero extends ExportAbstract
{
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
		$orders = Order::getAllByCriteria('invDate >= :fromDate and invDate < :toDate', array('fromDate' => trim($fromDate), 'toDate' => trim($toDate)));

		$return = array();
		foreach($orders as $order)
		{
			//common fields
			$customer = $order->getCustomer();
			$row = array(
				'ContactName' => $customer->getName()
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
				,'Reference'=> $order->getOrderNo()
				,'InvoiceDate' => $order->getInvDate()->setTimeZone('Australia/Melbourne')->__toString()
				,'DueDate' => ''
			);
			foreach($order->getOrderItems() as $orderItem)
			{
				$product = $orderItem->getProduct();
				if(!$product instanceof Product)
					continue;
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

			if(($shippingMethod = trim(implode(',', $order->getInfo(OrderInfoType::ID_MAGE_ORDER_SHIPPING_METHOD)))) !== '') {
				$shippingCost = $order->getInfo(OrderInfoType::ID_MAGE_ORDER_SHIPPING_COST);
				$return[] = array_merge($row, array(
					'InventoryItemCode' => $shippingMethod
					,'Description'=> $shippingMethod
					,'Quantity'=> 1
					,'UnitAmount'=> StringUtilsAbstract::getCurrency( count($shippingCost) > 0 ? StringUtilsAbstract::getValueFromCurrency($shippingCost[0]) : 0)
					,'Discount'=> ''
					,'AccountCode'=> '43300'
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
	protected static function _getMailTitle()
	{
		return 'Sales Export for Xero from last day';
	}
	protected static function _getMailBody()
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