<?php
/**
 * This is the listing page for customer
 *
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class ReportController extends BPCPageAbstract
{
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$menuItem
	 */
	public $menuItem = 'report.runrate';
	/**
	 * constructor
	 */
	public function __construct()
	{
		parent::__construct();
		if(!AccessControl::canAccessProductsPage(Core::getRole()))
			die('You do NOT have access to this page');
	}
	/**
	 * (non-PHPdoc)
	 * @see CRUDPageAbstract::_getEndJs()
	 */
	protected function _getEndJs()
	{
		$js = parent::_getEndJs();
		$js .= "pageJs.init()";
		$js .= ".setHTMLID('resultDiv', 'result-div')";
		$js .= ".setCallbackId('genReportmBtn', '" . $this->genReportmBtn->getUniqueID() . "')";
		return $js;
	}

	private function _getParams($param, $key) {
	    if(!isset($param[$key]))
	        return array();
	    if(($string = trim($param[$key])) === '')
	        return array();
	    return explode(',', $string);
	}
	/**
	 * Getting the items
	 *
	 * @param unknown $sender
	 * @param unknown $param
	 * @throws Exception
	 *
	 */
	public function genReport($sender, $param)
	{
        $results = $errors = array();
        try
        {
            $searchParams = json_decode(json_encode($param->CallbackParameter), true);
            $wheres = $joins = $params =array();
            if(isset($searchParams['pro.name']) && ($name = trim($searchParams['pro.name'])) !== '') {
                $wheres[] = 'pro.name like :name';
                $params['name'] = '%' . $name . '%';
            }

            if(isset($searchParams['pro.active']) && ($active = trim($searchParams['pro.active'])) !== '') {
                $wheres[] = 'pro.active = :active';
                $params['active'] = $active;
            }

            $productIds = $this->_getParams($searchParams, 'pro.id');
            if(count($productIds) > 0) {
                $array = array();
                foreach($productIds as $index => $productId) {
                    $key = 'product_' . $index;
                    $array[] = ':' . $key;
                    $params[$key] = $productId;
                }
                $wheres[] = 'pro.id in (' . implode(',', $array) . ')';
            }
            $manufacturerIds = $this->_getParams($searchParams, 'pro.manufacturerIds');
            if(count($manufacturerIds) > 0) {
                $array = array();
                foreach($manufacturerIds as $index => $manufacturerId) {
                    $key = 'brand_' . $index;
                    $array[] = ':' . $key;
                    $params[$key] = $manufacturerId;
                }
                $wheres[] = 'pro.manufacturerId in (' . implode(',', $array) . ')';
            }
            $supplierIds = $this->_getParams($searchParams, 'pro.supplierIds');
            if(count($supplierIds) > 0) {
                $array = array();
                foreach($supplierIds as $index => $supplierId) {
                    $key = 'supplier_' . $index;
                    $array[] = ':' . $key;
                    $params[$key] = $supplierId;
                }
                $joins[] = 'inner join suppliercode sup_code on (sup_code.productId = pro.id and sup_code.active = 1 and sup_code.supplierId in (' . implode(',', $array) . '))';
            }
            $productCategoryIds = $this->_getParams($searchParams, 'pro.productCategoryIds');
            if(count($productCategoryIds) > 0) {
                $array = array();
                foreach($productCategoryIds as $index => $productCategoryId) {
                    $key = 'productCategory_' . $index;
                    $array[] = ':' . $key;
                    $params[$key] = $productCategoryId;
                }
                $joins[] = 'inner join product_category x on (x.productId = pro.id and x.active = 1 and x.categoryId in (' . implode(',', $array) . '))';
            }
            $sql = 'select pro.id `proId`, pro.sku `proSku`, pro.name `proName` from product pro ' . implode(' ', $joins) . (count($wheres) > 0 ? (' where ' . implode(' AND ', $wheres)) : '');
            $result = Dao::getResultsNative($sql, $params, PDO::FETCH_ASSOC);
            if(count($result) === 0)
                throw new Exception('No result found!');
            $proIdMap = array();
            foreach($result as $row)
                $proIdMap[$row['proId']] = $row;
            $rates = $this->_getRunRateData(array_keys($proIdMap));
            $ratesMap = array();
            foreach($rates as $row)
                $ratesMap[$row['proId']] = $row;
            $data = array();
            foreach($proIdMap as $productId => $productInfo) {
                if(!isset($ratesMap[$productId]))
                    $data[$productId] = array_merge($productInfo, array('7days' => 0, '14days' => 0, '1month' => 0, '3month' => 0, '6month' => 0, '12month' => 0));
                else
                    $data[$productId] = array_merge($productInfo, $ratesMap[$productId]);
            }
            if (!($asset = $this->_getExcel($data)) instanceof Asset)
                throw new Exception('Failed to create a excel file');
            $results['url'] = $asset->getUrl();
        }
        catch(Exception $ex)
        {
            $errors[] = $ex->getMessage();
        }
        $param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
	private function _getRunRateData($productIds) {
	    if(count($productIds) === 0)
	        return array();
	    $_7DaysBefore = UDate::now()->modify('-7 day');
	    $_14DaysBefore = UDate::now()->modify('-14 day');
	    $_1mthBefore = UDate::now()->modify('-1 month');
	    $_3mthBefore = UDate::now()->modify('-3 month');
	    $_6mthBefore = UDate::now()->modify('-6 month');
	    $_12mthBefore = UDate::now()->modify('-12 month');
	    $sql = "select ord_item.productId `proId`,
	            sum(if(ord.orderDate >= '" . $_7DaysBefore . "', ord_item.qtyOrdered, 0)) `7days`,
	            sum(if(ord.orderDate >= '" . $_14DaysBefore . "', ord_item.qtyOrdered, 0)) `14days`,
	            sum(if(ord.orderDate >= '" . $_1mthBefore . "', ord_item.qtyOrdered, 0)) `1month`,
	            sum(if(ord.orderDate >= '" . $_3mthBefore . "', ord_item.qtyOrdered, 0)) `3month`,
	            sum(if(ord.orderDate >= '" . $_6mthBefore . "', ord_item.qtyOrdered, 0)) `6month`,
	            sum(if(ord.orderDate >= '" . $_12mthBefore . "', ord_item.qtyOrdered, 0)) `12month`
	            from `orderitem` ord_item
	            inner join `order` ord on (ord.type = :type and ord.active = 1 and ord.id = ord_item.orderId)
	            where ord_item.active = 1 and ord_item.productId in (" . implode(', ', $productIds) . ")
	            group by ord_item.productId";
	    return Dao::getResultsNative($sql, array('type' => Order::TYPE_INVOICE), PDO::FETCH_ASSOC);
	}
	/**
	 * @return PHPExcel
	 */
	private function _getExcel($data)
	{
	    $phpexcel= new PHPExcel();
	    $activeSheet = $phpexcel->setActiveSheetIndex(0);

	    $columnNo = 0;
	    $rowNo = 1; // excel start at 1 NOT 0
	    // header row
	    $activeSheet->setCellValueByColumnAndRow($columnNo++ , $rowNo, 'SKU');
	    $activeSheet->setCellValueByColumnAndRow($columnNo++ , $rowNo, 'Product Name');
	    $activeSheet->setCellValueByColumnAndRow($columnNo++ , $rowNo, 'Last Week');
	    $activeSheet->setCellValueByColumnAndRow($columnNo++ , $rowNo, 'Last Fortnight');
	    $activeSheet->setCellValueByColumnAndRow($columnNo++ , $rowNo, 'Last 1 Month');
	    $activeSheet->setCellValueByColumnAndRow($columnNo++ , $rowNo, 'Last 3 Month');
	    $activeSheet->setCellValueByColumnAndRow($columnNo++ , $rowNo, 'Last 6 Month');
	    $activeSheet->setCellValueByColumnAndRow($columnNo++ , $rowNo, 'Last 12 Month');
	    $rowNo++;
	    foreach($data as $productId => $rowNoData)
	    {
    	    $columnNo = 0; // excel start at 1 NOT 0
	        $activeSheet->setCellValueByColumnAndRow($columnNo++ , $rowNo, $rowNoData['proSku']);
    	    $activeSheet->setCellValueByColumnAndRow($columnNo++ , $rowNo, $rowNoData['proName']);
    	    $activeSheet->setCellValueByColumnAndRow($columnNo++ , $rowNo, $rowNoData['7days']);
    	    $activeSheet->setCellValueByColumnAndRow($columnNo++ , $rowNo, $rowNoData['14days']);
    	    $activeSheet->setCellValueByColumnAndRow($columnNo++ , $rowNo, $rowNoData['1month']);
    	    $activeSheet->setCellValueByColumnAndRow($columnNo++ , $rowNo, $rowNoData['3month']);
    	    $activeSheet->setCellValueByColumnAndRow($columnNo++ , $rowNo, $rowNoData['6month']);
    	    $activeSheet->setCellValueByColumnAndRow($columnNo++ , $rowNo, $rowNoData['12month']);
	        $rowNo++;
	    }
	    // Set document properties
	    $now = UDate::now();
	    $objWriter = new PHPExcel_Writer_Excel2007($phpexcel);
	    $filePath = '/tmp/' . md5($now);
	    $objWriter->save($filePath);
	    $fileName = 'RunRate_' . str_replace(':', '_', str_replace('-', '_', str_replace(' ', '_', $now->setTimeZone(SystemSettings::getSettings(SystemSettings::TYPE_SYSTEM_TIMEZONE))))) . '.xlsx';
	    $asset = Asset::registerAsset($fileName, file_get_contents($filePath), Asset::TYPE_TMP);
	    return $asset;
	}
}
?>
