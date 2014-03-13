<?php
/**
 * The Fancy Select Box
 *
 * @package    web
 * @subpackage controls
 * @author     lhe<helin16@gmail.com>
 */
class jsSaveAs extends TClientScript
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
			$className = get_class($this);
			$scriptArray = BPCPageAbstract::getLastestJS($className);
			foreach($scriptArray as $key => $value)
			{
				if(($value = trim($value)) !== '')
				{
					if($key === 'js')
						$this->getPage()->getClientScript()->registerScriptFile($className . 'JS', $this->publishAsset($value));
					else if($key === 'css')
						$this->getPage()->getClientScript()->registerStyleSheetFile($className. 'CSS', $this->publishAsset($value));
				}
			}
		}
	}
}