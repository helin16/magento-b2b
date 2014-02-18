<?php
class TooltipComments extends TTemplateControl
{
	public function onInit($param)
	{
		parent::onInit($param);
	
		$scriptArray = BPCPageAbstract::getLastestJS(get_class($this));
		foreach($scriptArray as $key => $value)
		{
			if(($value = trim($value)) !== '')
			{
				if($key === 'js')
					$this->getPage()->getClientScript()->registerScriptFile('tooltipcommentsJS', $this->publishAsset($value));
				else if($key === 'css')
					$this->getPage()->getClientScript()->registerStyleSheetFile('tooltipcommentsCSS', $this->publishAsset($value));
			}
		}
	}
}