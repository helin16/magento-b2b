<?php
ini_set('memory_limit','1024M');
class ItemExport_Xero extends ExportAbstract
{
	protected static function _getData()
	{
		if(count(self::$_dateRange) === 0) {
			$toDate = UDate::maxDate();
		} else {
			$toDate = self::$_dateRange['end'];
		}
		
		
		$return = array();
		$myobCodeType = ProductCodeType::get(ProductCodeType::ID_MYOB);
		foreach(Product::getAll(true) as $product)
		{
			$logs = ProductQtyLog::getAllByCriteria('productId = ? and created <= ?', array($product->getId(), trim($toDate)), true, 1, 1, array('id' => 'desc'));
			$log = count($logs) > 0 ? $logs[0] : null;
			$myobCodes = ProductCode::getCodes($product, $myobCodeType, true, 1, 1);
// 			$product = new Product();
			$return[] = array(
				'sku' => $product->getSku()
				,'name' => $product->getName()
				,'short description'=> $product->getShortDescription()
				,'category'=> join(', ', array_map(create_function('$a', 'return $a->getCategory()->getName();'), $product->getCategories()))
				,'assetAccNo'=> $product->getAssetAccNo()
				,'revenueAccNo'=> $product->getRevenueAccNo()
				,'costAccNo'=> $product->getCostAccNo()
				,'Stock On PO' => $log instanceof ProductQtyLog ? $log->getStockOnPO() : $product->getStockOnPO()
				,'Stock On Order' =>  $log instanceof ProductQtyLog ? $log->getStockOnOrder() : $product->getStockOnOrder()
				,'Stock On Hand' => $log instanceof ProductQtyLog ? $log->getStockOnHand() : $product->getStockOnHand()
				,'Total On Hand Value' => $log instanceof ProductQtyLog ? $log->getTotalOnHandValue() : $product->getTotalOnHandValue()
				,'Stock In Parts' =>  $log instanceof ProductQtyLog ? $log->getStockInParts() : $product->getStockInParts()
				,'Total In Parts Value' => $log instanceof ProductQtyLog ? $log->getTotalInPartsValue() : $product->getTotalInPartsValue()
				,'Stock In RMA' => $log instanceof ProductQtyLog ? $log->getStockInRMA() : $product->getStockInRMA()
				,'Total RMA Value' => $log instanceof ProductQtyLog ? $log->getTotalRMAValue() : $product->getTotalRMAValue()
				,'active' => intval($product->getActive()) === 1 ? 'Y' : 'N'
				,'MYOB' => count($myobCodes) > 0 ? $myobCodes[0]->getCode() : ''
			);
		}
		return $return;
	}
	protected static function _getMailTitle()
	{
		return 'Item List Export on ' . trim(new UDate());
	}
	protected static function _getMailBody()
	{
		return 'Item List Export on ' . trim(new UDate());
	}
	protected static function _getAttachedFileName()
	{
		$now = new UDate();
		$now->setTimeZone('Australia/Melbourne');
		return 'item_list_' . $now->format('Y_m_d_H_i_s') . '.csv';
	}
}