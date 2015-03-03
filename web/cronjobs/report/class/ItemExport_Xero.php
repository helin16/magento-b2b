<?php
ini_set('memory_limit','1024M');
class ItemExport_Xero extends ExportAbstract
{
	protected static function _getData()
	{
		$return = array();
		$myobCodeType = ProductCodeType::get(ProductCodeType::ID_MYOB);
		foreach(Product::getAll(false) as $product)
		{
			$product = new Product();
			$myobCodes = ProductCode::getCodes($product, $myobCodeType, true, 1, 1);
			$return[] = array(
				'sku' => $product->getSku()
				,'short description'=> $product->getShortDescription()
				,'assetAccNo'=> $product->getAssetAccNo()
				,'revenueAccNo'=> $product->getRevenueAccNo()
				,'costAccNo'=> $product->getCostAccNo()
				,'Stock On PO' => $product->getStockOnPO()
				,'Stock On Order' => $product->getStockOnOrder()
				,'Stock On Hand' => $product->getStockOnHand()
				,'Total On Hand Value' => $product->getTotalOnHandValue()
				,'Stock In Parts' => $product->getStockInParts()
				,'Total In Parts Value' => $product->getTotalInPartsValue()
				,'Stock In RMA' => $product->getStockInRMA()
				,'Total RMA Value' => $product->getTotalRMAValue()
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