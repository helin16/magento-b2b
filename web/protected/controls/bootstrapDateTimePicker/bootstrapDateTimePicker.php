<?php
/**
 * The bootstrapDateTimePicker Loader
 *
 * @package    web
 * @subpackage controls
 * @author     lhe<helin16@gmail.com>
 */
class bootstrapDateTimePicker extends TClientScript
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
			$folder = $this->publishFilePath(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR);
			$clientScript->registerHeadScriptFile('bootstrap.moment.js', $folder . '/js/moment-with-locales.min.js');
			$clientScript->registerHeadScriptFile('bootstrap.datetimepicker.js', $folder . '/js/bootstrap-datetimepicker.min.js');
			$clientScript->registerStyleSheetFile('bootstrap.datetimepicker.css', $folder . '/css/bootstrap-datetimepicker.min.css', 'screen');
		}
	}
}