<?php
class CreditNoteExport_Xero extends ExportAbstract
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
		$creditNotes = CreditNote::getAllByCriteria('applyDate >= :fromDate and applyDate < :toDate', array('fromDate' => trim($fromDate), 'toDate' => trim($toDate)));

		$return = array();
		foreach($creditNotes as $creditNote)
		{
			//common fields
			$customer = $creditNote->getCustomer();
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
				,'InvoiceNumber'=> $creditNote->getCreditNoteNo()
				,'Reference' => $creditNote->getOrder() instanceof Order ? $creditNote->getOrder()->getInvNo() : ''
				,'InvoiceDate' => $creditNote->getApplyDate()->setTimeZone('Australia/Melbourne')->__toString()
				,'DueDate' => ''
			);
			foreach($creditNote->getCreditNoteItems() as $item)
			{
				$product = $item->getProduct();
				if(!$product instanceof Product)
					continue;
				$shouldTotal = $item->getUnitPrice() * $item->getQty();
				$return[] = array_merge($row, array(
					'InventoryItemCode' => $product->getSku()
					,'Description'=> $product->getShortDescription()
					,'Quantity'=> 0 - $item->getQty()
					,'UnitAmount'=> $item->getUnitPrice()
					,'Discount'=> (floatval($shouldTotal) === 0.0000 ? 0 : round((($shouldTotal - $item->getTotalPrice()) * 100 / $shouldTotal), 2) ) . '%'
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
			if(($shippingValue = $creditNote->getShippingValue()) > 0) {
				$return[] = array_merge($row, array(
						'InventoryItemCode' => 'Credit Note Shipping'
						,'Description'=> 'Credit Note Shipping'
						,'Quantity'=> 0 - 1
						,'UnitAmount'=> StringUtilsAbstract::getCurrency( $shippingValue )
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
		return 'CreditNote Export for Xero from last day';
	}
	protected static function _getMailBody()
	{
		return 'Please find the attached export from BudgetPC internal system for all the CreditNotes from last day to import to xero.';
	}
	protected static function _getAttachedFileName()
	{
		$now = new UDate();
		$now->setTimeZone('Australia/Melbourne');
		return 'creditnote_xero_' . $now->format('Y_m_d_H_i_s') . '.csv';
	}
}