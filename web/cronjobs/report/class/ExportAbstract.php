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
		$class = get_called_class();
		$objPHPExcel = $class::_getOutput();
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
	protected static function _getOutput(){}
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
		EmailSender::addEmail('', '', self::_getMailTitle(), self::_getMailBody(), $assets);
	}
	protected static function _getMailTitle()
	{
		return '';
	}
	protected static function _getMailBody()
	{
		return '';
	}
	protected static function _getAttachedFileName()
	{
		return 'unknow.csv';
	}
}