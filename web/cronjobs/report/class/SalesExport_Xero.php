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
		//$orders = Order::getAllByCriteria('invDate >= :fromDate and invDate < :toDate and statusId != :cancelStatusId', array('fromDate' => trim($fromDate), 'toDate' => trim($toDate), 'cancelStatusId' => trim(OrderStatus::ID_CANCELLED)));
		$orders = Order::getAllByCriteria('invDate >= :fromDate and invDate < :toDate ', array('fromDate' => trim($fromDate), 'toDate' => trim($toDate)));
		
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
				,'Reference'=> (intval($order->getIsFromB2B()) === 1 ? $order->getOrderNo() :  $order->getPONo()) //changed for XiXi, she need the customer PO for any instore orders
				,'InvoiceDate' => $order->getInvDate()->setTimeZone('Australia/Melbourne')->__toString()
				,'DueDate' => $order->getInvDate()->modify('+' . self::getTerms($customer) . ' day')->setTimeZone('Australia/Melbourne')->__toString()
			);
			foreach($order->getOrderItems() as $orderItem)
			{
				$product = $orderItem->getProduct();
				if(!$product instanceof Product)
					continue;
				$shouldTotal = $orderItem->getUnitPrice() * $orderItem->getQtyOrdered();
				$return[] = array_merge($row, array(
					'InventoryItemCode' => $product->getSku()
					,'Description'=> $product->getShortDescription()
					,'Quantity'=> $orderItem->getQtyOrdered()
					,'UnitAmount'=> $orderItem->getUnitPrice()
					,'Discount'=> (floatval($shouldTotal) === 0.0000 ? 0 : round((($shouldTotal - $orderItem->getTotalPrice()) * 100 / $shouldTotal), 2) ) . '%'
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
	private static function getTerms(Customer $customer)
	{
		$terms = array('P&P COMPUTER' => 30
				,'Stanley Security' => 30
				,'ABACUS RENT IT' => 30
				,'TYCO SAFETY PRODUCTS' => 30
				,'MONASH UNIVERSITY' => 30
				,'LDS INTERNATIONAL' => 30
				,'SECURITY MERCHANTS AUSTRALIA PTY LTD (Stock)' => 30
				,'Support Services Pty Ltd' => 30
				,'Soniq Digital Media Pty Ltd' => 30
				,'EVER SUCCESS PTY LTD' => 30
				,'SUMMER TECHNOLOGY' => 30
				,'N2C' => 30
				,'BALTHOR' => 30
				,'EVERSAFE AUSTRALIA PTY LTD' => 30
				,'BULLER SKI LIFTS' => 30
				,'DRAEGER MEDICAL AUSTRALIA' => 30
				,'WESTERN PORT WATER' => 30
				,'COMPLETE INTEGRATED ALARM SERVICES' => 30
				,'GREENHOOD IT' => 30
				,'ULTRASOURCE PTY LTD' => 30
				,'WELSH DIRECT' => 30
				,'ALARM CORP' => 30
				,'FETHERS Pty Ltd' => 30
				,'DELTA ENERGY SYSTEMS PTY LTD' => 30
				,'Quatius Australia Pty Ltd' => 30
				,'TRONSEC SECURITY PTY LTD' => 30
				,'DRAEGER SAFETY' => 30
				,'Quatius Logistics Pty Ltd' => 30
				,'DFP RECRUITMENT SERVICES PTY LTD' => 30
				,'CAR PARKING SOLUTIONS P/L' => 30
				,'GS1 AUSTRALIA' => 30
				,'SECURITY MERCHANTS AUSTRALIA PTY LTD (Supply)' => 30
				,'DFP RECRUITMENT SERVICES' => 30
				,'FISHER & PAYKEL HEALTH CARE PTY LTD' => 30
				,'LIMA ORTHOPAEDICS AUSTRALIA' => 21
				,'Ultra View Technology' => 14
				,'DSN AUSTRALIA' => 14
				,'AE SMITH & SON PTY LTD' => 14
				,'BAXTER INSTITUTE' => 14
				,'Acute Solutions' => 14
				,'BLUESHIELD TECHNOLOGIES PTY LTD' => 14
				,'KS ENVIRONMENTAL' => 14
				,'DANMAC PRODUCTS PTY LTD' => 14
				,'Fisher & Paykel Healthcare' => 30
				,'GP GRADERS PTY LTD' => 30
				,'NAVTECH SECURITY' => 7);
		foreach($terms as $name => $days) {
			if(strtoupper($name) === strtoupper(trim($customer->getName()))) {
				return $terms[$name];
			}
		}
		return 0;
	}
}