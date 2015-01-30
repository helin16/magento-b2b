<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';
Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
class ExportAbstract
{
	protected static $_debug = false;
	private static $_rootDir = '/tmp/export/';
	
	public static function run($debug = false)
	{
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
			->setSheetIndex(0)
			->save($filePath);
		if(!is_file($filePath))
			throw new Exception('System Error: can NOT generate CSV to:' . $filePath);
		$class = get_called_class();
		$asset = Asset::registerAsset($class::_getAttachedFileName(), file_get_contents($filePath));
		self::_mailOut($asset);
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
		EmailSender::addEmail('', '', $class::_getMailTitle(), $class::_getMailBody(), $assets);
	}
}