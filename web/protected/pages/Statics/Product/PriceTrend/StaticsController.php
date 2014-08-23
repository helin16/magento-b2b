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
	public $menuItem = 'statics.product.priceTrend';
	/**
	 * Getting The end javascript
	 *
	 * @return string
	 */
	protected function _getEndJs()
	{
		$js = parent::_getEndJs();
		$searchCriteria = array();
		$searchCriteria['productId'] = (isset($_REQUEST['productid']) ? trim($_REQUEST['productid']) : '');
		
		$dateFrom = (isset($_REQUEST['from']) ? trim($_REQUEST['from']) : trim(UDate::now()->modify('-12 month')));
		$dateTo = (isset($_REQUEST['to']) ? trim($_REQUEST['to']) : trim(UDate::now()));
		$searchCriteria['dateRange'] = array('from' => $dateFrom, 'to' => $dateTo);
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
			
			if(!($product = Product::get($param->CallbackParameter->productId)) instanceof Product)
			 	throw new Exception('Invalid product id=' . $param->CallbackParameter->productId . ' provided');
			
			$series = array();
			$series[] = array('name' => 'unit price', 'data' => $this->_getSeries($product->getId(), $dateFrom, $dateTo));
			
			$results = array(
					'chart' => array(
							'type' => 'spline'
					),
					'title' => array(
							'text' => 'Product Price($) Trend',
							'x'    => -20
					),
					'subtitle' => array(
							'text' => 'Data is based on date range between "' . $dateFrom . '" to "' . $dateTo . '"',
							'x'    => -20
					),
					'xAxis' => array(
						'type' => 'datetime',
						'dateTimeLabelFormats' => array( // don't display the dummy year
							'month' => '%e. %b',
							'year' => '%b'
						),
						'title' => array(
							'text' => 'Date'
						)
					),
					'yAxis' => array(
						'title' => array(
								'text' => 'Unit Price($)'
						),
						'min' => 0
					),
					'tooltip' => array(
						'headerFormat' => '<b>{series.name}</b><br>',
						'pointFormat' => '{point.x:%e. %b}: ${point.y:.2f}'
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
	
	private function _getSeries($productId, $from, $to, $type = null)
	{
		$sql = 'select unitPrice, created from `orderitem` where active = 1 AND productId = ? AND created >=? and created <= ? order by created asc';
		$row = Dao::getResultsNative($sql, array(trim($productId), trim($from), trim($to)));
		$return = array();
		foreach($row as $col)
		{
			$created = new UDate(trim($col['created']));
			$return[] = array('Date.UTC(' . $created->format('Y, m, d, h, i, s') . '), ' . (double)trim($col['unitPrice']));
		}
		return $return;
	}
}
?>
