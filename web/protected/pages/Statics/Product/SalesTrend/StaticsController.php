<?php
/**
 * This is the StaticsController
 * 
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class StaticsController extends StaticsPageAbstract
{
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$menuItem
	 */
	public $menuItem = 'statics.product.salesTrend';
	/**
	 * Getting The end javascript
	 *
	 * @return string
	 */
	protected function _getEndJs()
	{
		$js = parent::_getEndJs();
		$productIds = (isset($this->_request['productids']) ? explode(', ', trim($this->_request['productids'])) : array());
		$js .= 'pageJs';
			$js .= '.load({});';
		return $js;
	}
	/**
	 * (non-PHPdoc)
	 * @see StaticsPageAbstract::getData()
	 */
	public function getData($sender, $param)
	{
		$results = $errors = array();
		try
		{
			$timeRange = $this->_getXnames();
			$names = array_keys($timeRange);
			$series = array();
			$series[] = array('name' => 'All', 'data' => $this->_getSeries($timeRange, $timeRange[$names[0]]['from'], $timeRange[$names[count($names) - 1 ]]['to']));
			foreach(OrderStatus::getAll() as $status)
			{
				$series[] = array('name' => $status->getName(), 'data' => $this->_getSeries($timeRange, $timeRange[$names[0]]['from'], $timeRange[$names[count($names) - 1 ]]['to'], array($status->getId())));
			}
	
			$results = array(
				'chart' => array(
					'type' => 'line'
				),
				'title' => array(
					'text' => 'BPC: Monthly Order Trend',
					'x'    => -20
				),
				'subtitle' => array(
					'text' => 'This is just order trend from last 12 month',
					'x'    => -20
				),
				'xAxis' => array(
					'categories' => $names
				),
				'yAxis' => array(
					'title' => array(
						'text' => 'No of Orders'
					)
				),
				'series' => $series
			);
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
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
