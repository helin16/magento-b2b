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
		$searchCriteria = array();
		$searchCriteria['productIds'] = (isset($_REQUEST['productids']) ? explode(',', str_replace(' ', '', $_REQUEST['productids'])) : array());
		$dateStepBy = (isset($_REQUEST['step']) ? trim($_REQUEST['step']) : '+1 week');
		$dateFrom = (isset($_REQUEST['from']) ? trim($_REQUEST['from']) : trim(UDate::now()->modify('-10 week')));
		$dateTo = (isset($_REQUEST['to']) ? trim($_REQUEST['to']) : trim(UDate::now()));
		$searchCriteria['dateRange'] = array('from' => $dateFrom, 'to' => $dateTo, 'step' => $dateStepBy);
		$searchCriteria['showPrice'] = (isset($_REQUEST['showprice']) && trim($_REQUEST['showprice']) === '1') ;
		$js .= 'pageJs';
			$js .= '.load(' . json_encode($searchCriteria) . ');';
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
			$dateFrom = trim($param->CallbackParameter->dateRange->from);
			$dateTo = trim($param->CallbackParameter->dateRange->to);
			$step = trim($param->CallbackParameter->dateRange->step);
			$showPrice = $param->CallbackParameter->showPrice;
			
			$timeRange = $this->_getXnames($dateFrom, $dateTo, $step);
			$names = array_keys($timeRange);
			$productIds = $param->CallbackParameter->productIds;
			if(count($productIds) === 0)
			 	$productIds = $this->_topProductIds();
			
			$series = array();
			foreach($productIds as $pid)
			{
				$data = array_fill(0, count($names), 0);
				$name = 'Invalid Pid=' . $pid;
				if(($product = Product::get($pid)) instanceof Product)
				{
					$name = $product->getSku();
					$data = $this->_getSeries($timeRange, $dateFrom, $dateTo, array($product->getId()), $showPrice);
				}
				$series[] = array('name' => $name, 'data' => $data);
			}
			
			$results = array(
					'chart' => array(
							'type' => 'line'
					),
					'title' => array(
							'text' => 'Product Sales Trend',
							'x'    => -20
					),
					'subtitle' => array(
							'text' => 'Data is based on date range between "' . $dateFrom . '" to "' . $dateTo . '"',
							'x'    => -20
					),
					'xAxis' => array(
							'categories' => $names
					),
					'yAxis' => array(
							'title' => array(
									'text' => $showPrice === true ? 'Order Amount ($)' : 'Ordered Qty'
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
	
	private function _topProductIds()
	{
		$sql = 'select productId, sum(qtyOrdered) `sum` from orderitem where active = 1 group by productId order by `sum` desc limit 10';
		$result = Dao::getResultsNative($sql);
		return array_map(create_function('$a', 'return $a["productId"];'), $result);
	}
	
	private function _getXnames($from, $to, $step)
	{
		$names = array();
		$dateFrom = new UDate(trim($from));
		$dateTo = new UDate(trim($to));
		do {
			$from = new UDate(trim($dateFrom));
			$to = new UDate(trim($dateFrom->modify($step)));
			$names[trim($from->format('d/M/y'))] = array('from' => trim($from), 'to' => trim($to));
		} while($dateFrom->before($dateTo));
		return $names;
	}
	
	private function _getSeries($groupFrame, $from, $to, array $productIds, $showPrice = false)
	{
		$select = array();
		foreach($groupFrame as $index => $time)
			$select[] = 'sum(if((created >= "' . $time['from'] . '" && created < "' . $time['to'] . '"), ' . ($showPrice === true ? 'totalPrice' : 'qtyOrdered') . ' , 0)) `' . $index . '`';
		$where = array('active = 1');
		if(count($productIds) > 0)
			$where[] = 'productId in (' . implode(', ', $productIds) . ')';
		$sql = "select " . implode(', ', $select) . ' from `orderitem` where ' . implode(' AND ', $where) . ' and created >=? and created <= ?';
		$row = Dao::getSingleResultNative($sql, array(trim($from), trim($to)), PDO::FETCH_NUM);
		$return = array();
		foreach($row as $col)
		{
			$return[] = ($showPrice === true ? (double)$col : intval($col));
		}
		return $return;
	}
}
?>
