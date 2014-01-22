<?php
/**
 * The OrderDetailsJs Loader
 *
 * @package    web
 * @subpackage controls
 * @author     lhe<helin16@gmail.com>
 */
class OrderDetailsJs extends TClientScript
{
	/**
	 * (non-PHPdoc)
	 * @see TControl::onLoad()
	 */
	public function onLoad($param)
	{
		$clientScript = $this->getPage()->getClientScript();
		if(!$this->getPage()->IsPostBack || !$this->getPage()->IsCallback)
		{
// 			BPCPageAbstract::getLastestJS(get_class($this))
		}
	}
}