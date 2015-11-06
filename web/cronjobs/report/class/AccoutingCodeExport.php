<?php
ini_set('memory_limit','1024M');
ini_set('max_execution_time', 300);
class AccoutingCodeExport extends ExportAbstract
{
	protected static function _getData()
	{
		if(count(self::$_dateRange) === 0) {
			$toDate = UDate::maxDate();
		} else {
			$toDate = self::$_dateRange['end'];
		}
		
		$return = array();
		foreach(AccountingCode::getAll() as $accoutingCode)
		{
			$return[] = array(
				'type' => self::_convertType($accoutingCode->getTypeId())
				,'code' => $accoutingCode->getCode()
				,'description'=> $accoutingCode->getDescription()
			);
		}
		return $return;
	}
	private static function _convertType($id)
	{
		switch (intval($id))
		{
			case intval(AccountingCode::TYPE_ID_ASSET):
			{
				return 'ASSET';
			}
			case intval(AccountingCode::TYPE_ID_COST):
			{
				return 'COST';
			}
			case intval(AccountingCode::TYPE_ID_REVENUE):
			{
				return 'REVENUE';
			}
			default:
			{
				return $id;
			}
		}
		return $id;
	}
	protected static function _getMailTitle()
	{
		return 'Accounting Code List Export on ' . trim(new UDate());
	}
	protected static function _getMailBody()
	{
		return 'Accounting Code List Export on ' . trim(new UDate());
	}
	protected static function _getAttachedFileName()
	{
		$now = new UDate();
		$now->setTimeZone('Australia/Melbourne');
		return 'accounting_code_list_' . $now->format('Y_m_d_H_i_s') . '.csv';
	}
}