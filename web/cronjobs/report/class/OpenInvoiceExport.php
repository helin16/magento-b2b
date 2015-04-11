<?php
class OpenInvoiceExport extends ExportAbstract
{
	private static $_fromDate;
	private static $_toDate;
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
		self::$_fromDate = $fromDate;
		self::$_toDate= $toDate;
		Dao::$debug = true;
		$orders = Order::getAllByCriteria('invDate >= :fromDate and invDate <= :toDate', array('fromDate' => trim($fromDate), 'toDate' => trim($toDate)));
		Dao::$debug = false;

		$return = array();
		foreach($orders as $order)
		{
			//common fields
			$customer = $order->getCustomer();
			$creditNotes = CreditNote::getAllByCriteria('orderId = ?', array($order->getId()));
			$row = array(
				'Invoice No.' => $order->getInvNo()
				,'Invoice Date' => $order->getInvDate()->setTimeZone('Australia/Melbourne')->__toString()
				,'Order No.'=> $order->getOrderNo()
				,'Customer Name' => $customer->getName()
				,'Customer Ph.'=> $customer->getContactNo()
				,'Customer Email'=> $customer->getEmail()
				,'Status'=> $order->getStatus()->getName()
				,'Total Amt.'=> StringUtilsAbstract::getValueFromCurrency($order->getTotalAmount())
				,'Total Paid'=> StringUtilsAbstract::getValueFromCurrency($order->getTotalPaid())
				,'Total Credited'=> StringUtilsAbstract::getValueFromCurrency($order->getTotalCreditNoteValue())
				,'Total Due'=> StringUtilsAbstract::getValueFromCurrency($order->getTotalAmount() - $order->getTotalPaid() - $order->getTotalCreditNoteValue())
				,'CreditNote Nos.'=> implode(', ', array_map(create_function('$a', 'return $a->getCreditNoteNo();'), $creditNotes))
			);
			$return[] = $row;
		}
		return $return;
	}
	protected static function _getMailTitle()
	{
		return 'Open Invoice from "' . self::$_fromDate->setTimeZone('Australia/Melbourne')->__toString() . '" to "' . self::$_toDate->setTimeZone('Australia/Melbourne')->__toString()  . '"';
	}
	protected static function _getMailBody()
	{
		return 'Open Invoice from "' . self::$_fromDate->setTimeZone('Australia/Melbourne')->__toString() . '" to "' . self::$_toDate->setTimeZone('Australia/Melbourne')->__toString()  . '"';
	}
	protected static function _getAttachedFileName()
	{
		$now = new UDate();
		$now->setTimeZone('Australia/Melbourne');
		return 'open_invoice_' . $now->format('Y_m_d_H_i_s') . '.csv';
	}
}