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
			$myobCodes = ProductCode::getCodes($product, $myobCodeType, true, 1, 1);
			$return[] = array(
				'sku' => $product->getSku()
				,'short description'=> $product->getShortDescription()
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