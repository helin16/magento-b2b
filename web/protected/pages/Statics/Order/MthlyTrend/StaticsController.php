<?php
/**
 * This is the StaticsController
 * 
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class StaticsController extends BPCPageAbstract
{
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$menuItem
	 */
	public $menuItem = 'statics.order.mthlyTrend';
	/**
	 * Getting The end javascript
	 *
	 * @return string
	 */
	protected function _getEndJs()
	{
		$js = parent::_getEndJs();
		$timeRange = $this->_getXnames();
		$names = array_keys($timeRange);
		
		$series = array();
		$series[] = array('name' => 'All', 'data' => $this->_getSeries($timeRange, $timeRange[$names[0]]['from'], $timeRange[$names[count($names) - 1 ]]['to']));
// 		foreach(OrderStatus::getAll() as $status)
// 		{
// 			$series[] = array('name' => $status->getName(), 'data' => $this->_getSeries($timeRange, $timeRange[$names[0]]['from'], $timeRange[$names[count($names) - 1 ]]['to'], array($status->getId())));
// 		}
		
		$data = array('xAxis' => $names, 'series' => $series);
		$js .= 'pageJs';
			$js .= '.load("#statics-div", ' . json_encode($data) . ');';
		return $js;
	}
	
	private function _getXnames()
	{
		$names = array();
		$_12mthAgo = new UDate();
		$_12mthAgo->modify('-12 month');
		for($i = 0; $i<12; $i++)
		{
			$from = new UDate(trim($_12mthAgo->format('Y-m-01 00:00:00')));
			$to = new UDate(trim($_12mthAgo->modify('+1 month')->format('Y-m-01 00:00:00')));
			$names[trim($from->format('M/Y'))] = array('from' => trim($from), 'to' => trim($to));
		}
		return $names;
	}
	
	private function _getSeries($groupFrame, $from, $to, $statusId = array())
	{
		$select = array();
		foreach($groupFrame as $index => $time)
			$select[] = 'sum(if((created >= "' . $time['from'] . '" && created < "' . $time['to'] . '"), 1 , 0)) `' . $index . '`';
		$where = array('active = 1');
		if(count($statusId) > 0)
			$where[] = 'statusId in (' . implode(', ', $statusId) . ')';
		$sql = "select " . implode(', ', $select) . ' from `order` where ' . implode(' AND ', $where) . ' and created >=? and created < ?';
		$row = Dao::getSingleResultNative($sql, array(trim($from), trim($to)), PDO::FETCH_NUM);
		$return = array();
		foreach($row as $col)
		{
			$return[] = intval($col);
		}
		return $return;
	}
}
?>
