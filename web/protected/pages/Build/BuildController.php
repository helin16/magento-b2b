<?php
/**
 * This is the OrderController
 *
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class BuildController extends BPCPageAbstract
{
	public $orderPageSize = 30;
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$menuItem
	 */
	public $menuItem = 'order';
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::onLoad()
	 */
	public function onLoad($param)
	{
		parent::onLoad($param);
	}
	/**
	 * Getting The end javascript
	 *
	 * @return string
	 */
	protected function _getEndJs()
	{
		$systemSetting = SystemSettings::getByType(SystemSettings::TYPE_SYSTEM_BUILD_PRODUCTS_ID);
		$products = array();
		if($systemSetting instanceof SystemSettings && is_array(json_decode($systemSetting->getValue())))
		{
			foreach (json_decode($systemSetting->getValue()) as $id)
			{
				if(($product = Product::get($id)) instanceof Product)
					$products[] = $product->getJson();
			}
		}

		$js = parent::_getEndJs();
		$js .= 'pageJs.resultDivId = "resultDiv";';
		$js .= 'pageJs.downloadBtnId = "download-btn";';
		$js .= 'pageJs.productIds = ' . json_encode($products) . ';';
		$js .= "pageJs.setCallbackId('updateSetting', '" . $this->updateSettingBtn->getUniqueID() . "');";
		$js .= 'pageJs.init();';
		return $js;
	}
	public function updateSetting($sender, $param)
	{
		$result = $errors = array();
		try
		{
			$result = array();
			$products = array();
			foreach ($param->CallbackParameter as $id)
			{
				if(($product = Product::get(intval($id))) instanceof Product)
				{
					$result[] = intval($id);
					$products[] = $product->getJson();
				}
			}
			$systemSetting = SystemSettings::getByType(SystemSettings::TYPE_SYSTEM_BUILD_PRODUCTS_ID);
			
			Dao::beginTransaction();
			
			$systemSetting->setValue(json_encode($result))->save();
			
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($products, $errors);
	}
}
?>