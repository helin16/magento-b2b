<?php
/**
 * This is the paymentListPanel
 *
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class SerialBulkUploader extends TClientScript
{
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
					$this->getPage()->getClientScript()->registerScriptFile(__CLASS__ . 'Js', $this->publishAsset($value));
				else if($key === 'css')
					$this->getPage()->getClientScript()->registerStyleSheetFile(__CLASS__ . 'Css', $this->publishAsset($value));
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
