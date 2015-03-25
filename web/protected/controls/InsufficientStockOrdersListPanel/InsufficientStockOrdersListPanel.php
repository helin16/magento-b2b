<?php
/**
 * This is the InsufficientStockOrdersListPanel
 *
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class InsufficientStockOrdersListPanel extends TTemplateControl
{
	public $pageSize = 10;
	/**
	 * (non-PHPdoc)
	 * @see TControl::onInit()
	 */
	public function onInit($param)
	{
		parent::onInit($param);
		$scriptArray = BPCPageAbstract::getLastestJS(get_class($this));
		foreach($scriptArray as $key => $value) {
			if(($value = trim($value)) !== '') {
				if($key === 'js')
					$this->getPage()->getClientScript()->registerScriptFile('InsufficientStockOrdersListPanelJs', $this->publishAsset($value));
				else if($key === 'css')
					$this->getPage()->getClientScript()->registerStyleSheetFile('InsufficientStockOrdersListPanelCss', $this->publishAsset($value));
			}
		}
	}
	/**
	 * (non-PHPdoc)
	 * @see TControl::onLoad()
	 */
	public function onLoad($param)
	{
		parent::onLoad($param);
	}
}
?>
