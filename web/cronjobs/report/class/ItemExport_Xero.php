<?php
class ItemExport_Xero extends ExportAbstract
{
	protected static function _getData()
	{
		$return = array();
		$myobCodeType = ProductCodeType::get(ProductCodeType::ID_MYOB);
		foreach(Product::getAll(false) as $product)
		{
			$fullDescription = Asset::getAsset($product->getFullDescAssetId());
			$myobCodes = ProductCode::getCodes($product, $myobCodeType, true, 1, 1);
			$return[] = array(
				'sku' => $product->getSku()
				,'description'=> $fullDescription instanceof Asset ? file_get_contents($fullDescription->getPath()) : ''
				,'assetAccNo'=> $product->getAssetAccNo()
				,'revenueAccNo'=> $product->getRevenueAccNo()
				,'costAccNo'=> $product->getCostAccNo()
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