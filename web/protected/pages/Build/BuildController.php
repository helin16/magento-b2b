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
		if($systemSetting instanceof SystemSettings)
		{
			foreach (json_decode($systemSetting->getValue()) as $type=>$ids)
			{
				$products[$type] = array();
				foreach ($ids as $id)
				{
					if(($product = Product::get($id)) instanceof Product)
						$products[$type][] = $product->getJson();
				}
			}
		}
		$js = parent::_getEndJs();
		$js .= 'pageJs.resultDivId = "resultDiv";';
		$js .= 'pageJs.downloadBtnId = "download-btn";';
		$js .= 'pageJs.products = ' . json_encode($products) . ';';
		$js .= "pageJs.setCallbackId('updateSetting', '" . $this->updateSettingBtn->getUniqueID() . "');";
		$js .= 'pageJs.init();';
		return $js;
	}
	public function updateSetting($sender, $param)
	{
		$result = $errors = array();
		try
		{
			$result = $products = array();
			$systemSetting = SystemSettings::getByType(SystemSettings::TYPE_SYSTEM_BUILD_PRODUCTS_ID);
			foreach ($param->CallbackParameter as $type=>$ids)
			{
				$result[$type] = array();
				$products[$type] = array();
				foreach ($ids as $index=>$id)
				{
					$id = intval(trim($id));
					if(($product = Product::get($id)) instanceof Product)
					{
						$result[$type][] = $id;
						$products[$type][] = $product->getJson();
					}
				}
			}
			Dao::beginTransaction();
			
			$systemSetting->setValue(json_encode($result))->save();
			
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
// 			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($products, $errors);
	}
}
?>