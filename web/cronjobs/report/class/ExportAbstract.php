<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class ExportAbstract
{
	protected static $_debug = false;
	private static $_rootDir = '/tmp/export';
	protected static $_dateRange = array();

	public static function setStartNEndDate(UDate $start, UDate $end)
	{
		self::$_dateRange['start'] = $start;
		self::$_dateRange['end'] = $end;
	}

	public static function run($debug = false, $mailOut = true)
	{
		try{
			self::$_debug = $debug;
			if($debug)
				echo '<pre>';
			$objPHPExcel = self::_getOutput();
			if(!$objPHPExcel instanceof PHPExcel)
				throw new Exception('System Error: can NOT generate CSV without PHPExcel object!');
			// Set document properties
			$filePath = self::$_rootDir . '/' . md5(new UDate()) . '.csv';
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV')->setDelimiter(',')
				->setEnclosure('"')
				->setLineEnding("\r\n")
				->setSheetIndex(0);
			ob_start();
			$objWriter->save('php://output');
			$excelOutput = ob_get_clean();
			$class = get_called_class();
			$asset = Asset::registerAsset($class::_getAttachedFileName(), $excelOutput, Asset::TYPE_TMP);
			if($mailOut === true)
				self::_mailOut($asset);
			return $asset;
		} catch (Exception $ex) {
			echo $ex->getMessage();
			die('ERROR!');
		}
	}
	/**
	 * Debug output function
	 *
	 * @param string $message
	 * @param string $newLine
	 *
	 */
	protected static function _debug($message, $newLine = "\n")
	{
		if(self::$_debug === true)
			echo $message . $newLine;
	}
	/**
	 * @return PHPExcel
	 */
	protected static function _getOutput()
	{
		$class = get_called_class();
		$phpexcel= new PHPExcel();
		$data = $class::_getData();
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
				if(self::$_debug)
					echo $letter . $number . ': ' . $key . "\n";
				$activeSheet->setCellValue($letter++ . $number, $key);
			}
			$number++;
			$letter = 'A';
			if(self::$_debug)
				echo "\n";
			break; // only need the header
		}
		foreach($data as $row)
		{
			foreach($row as $col)
			{
				if(self::$_debug)
					echo $letter . $number . ': ' . $col . "\n";
				$activeSheet->setCellValue($letter++ . $number, $col);
			}
			$number++;
			$letter = 'A';
			if(self::$_debug)
				echo "\n";
		}
		return $phpexcel;
	}
	/**
	 * Mailing the file out to someone
	 *
	 * @param unknown $filePath
	 */
	private static function _mailOut(Asset $asset = null)
	{
		$assets = array();
		if($asset instanceof Asset)
			$assets[] = $asset;
		$class = get_called_class();
		$michaelEmail = 'michael.y@budgetpc.com.au';
		$helinEmail = 'helin16@gmail.com';
		$xixiEmail = 'xitan@budgetpc.com.au';
		$accountEmail = 'accounts@budgetpc.com.au';
		$marketingEmail = 'marketing@budgetpc.com.au';
		$salesEmail = 'sales@budgetpc.com.au';

		EmailSender::addEmail('', $michaelEmail, $class::_getMailTitle(), $class::_getMailBody(), $assets);
		EmailSender::addEmail('', $helinEmail, $class::_getMailTitle(), $class::_getMailBody(), $assets);
		EmailSender::addEmail('', $xixiEmail, $class::_getMailTitle(), $class::_getMailBody(), $assets);
		EmailSender::addEmail('', $accountEmail, $class::_getMailTitle(), $class::_getMailBody(), $assets);
		EmailSender::addEmail('', $marketingEmail, $class::_getMailTitle(), $class::_getMailBody(), $assets);
		EmailSender::addEmail('', $salesEmail, $class::_getMailTitle(), $class::_getMailBody(), $assets);
	}
}