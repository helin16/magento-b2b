<?php
/**
 * The OrderBtns Loader
 *
 * @package    web
 * @subpackage controls
 * @author     lhe<helin16@gmail.com>
 */
class OrderBtns extends TTemplateControl
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
					$this->getPage()->getClientScript()->registerScriptFile('OrderBtns.Js', $this->publishAsset($value));
				else if($key === 'css')
					$this->getPage()->getClientScript()->registerStyleSheetFile('OrderBtns.css', $this->publishAsset($value));
			}
		}
	}

	public function onLoad($param)
	{
		parent::onLoad($param);
		if(!$this->getPage()->IsCallBack && !$this->getPage()->IsPostBack)
		{
			$js = 'OrderBtnsJs.SEND_EMAIL_CALLBACK_ID = "' . $this->sendEmailBtn->getUniqueID() . '";';
			$this->getPage()->getClientScript()->registerEndScript('OrderBtnsJS', $js);
		}
	}
	/**
	 * Sending the email out
	 *
	 * @param unknown $sender
	 * @param unknown $param
	 *
	 * @throws Exception
	 */
	public function sendEmail($sender, $param)
	{
		$results = $errors = array();
		try
		{
			Dao::beginTransaction();

			if(!isset($param->CallbackParameter->orderId) || !($order = Order::get($param->CallbackParameter->orderId)) instanceof Order)
				throw new Exception('System Error: invalid order provided!');
			if(!isset($param->CallbackParameter->emailAddress) || ($emailAddress = trim($param->CallbackParameter->emailAddress)) === '')
				throw new Exception('System Error: invalid emaill address provided!');
			$emailBody = '';
			if(isset($param->CallbackParameter->emailBody) && ($emailBody = trim($param->CallbackParameter->emailBody)) !== '')
				$emailBody = str_replace("\n", "<br />", $emailBody);

			$pdfFile = EntityToPDF::getPDF($order);
			$asset = Asset::registerAsset($order->getOrderNo() . '.pdf', file_get_contents($pdfFile), Asset::TYPE_TMP);
			EmailSender::addEmail('sales@budgetpc.com.au', $emailAddress, 'BudgetPC Order:' . $order->getOrderNo() , (trim($emailBody) === '' ? '' : $emailBody . "<br /><br />") .'Please find attached Order (' . $order->getOrderNo() . ') from Budget PC Pty Ltd.', array($asset));
			$order->addComment('An email sent to "' . $emailAddress . '" with the attachment: ' . $asset->getAssetId(), Comments::TYPE_SYSTEM);
			$results['item'] = $order->getJson();

			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
}