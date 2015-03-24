<?php
/**
 * This is the paymentListPanel
 *
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class PaymentListPanel extends TTemplateControl
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
					$this->getPage()->getClientScript()->registerScriptFile('PaymentListPanelJs', $this->publishAsset($value));
				else if($key === 'css')
					$this->getPage()->getClientScript()->registerStyleSheetFile('PaymentListPanelCss', $this->publishAsset($value));
			}
		}
		if(!$this->getPage()->IsCallBack && !$this->getPage()->IsPostBack) {
			$js = 'if(typeof(PaymentListPanelJs) !== "undefined") {';
				$js .= 'PaymentListPanelJs.callbackIds = ' . json_encode(array(
						'getPayments' => $this->getPaymentsBtn->getUniqueID()
						,'delPayment' => $this->delPaymentBtn->getUniqueID()
						,'addPayment' => $this->addPaymentBtn->getUniqueID()
				)) . ';';
			$js .= '}';
			$this->getPage()->getClientScript()->registerEndScript('plpJs', $js);
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
	/**
	 * Getting the payments
	 *
	 * @param unknown $sender
	 * @param unknown $param
	 */
	public function getPayments($sender, $param)
	{
		$result = $error = array();
		try
		{
			$pageNo = 1;
			$pageSize = $this->pageSize;
			if(isset($param->CallbackParameter->pagination)) {
				$pageNo = isset($param->CallbackParameter->pagination->pageNo) ? $param->CallbackParameter->pagination->pageNo : $pageNo;
				$pageSize = isset($param->CallbackParameter->pagination->pageSize) ? $param->CallbackParameter->pagination->pageSize : $pageSize;
			}
			if(!isset($param->CallbackParameter->entity) || !isset($param->CallbackParameter->entityId) || ($entityName = trim($param->CallbackParameter->entity)) === '' || !($entity = $entityName::get(trim($param->CallbackParameter->entityId))) instanceof $entityName)
				throw new Exception('System Error: invalid Order or CreditNote provided. Can NOT get any payments at all.');

			if($entity instanceof Order) {
				$where = 'orderId = ?';
			} else if ($entity instanceof CreditNote) {
				$where = 'creditNoteId = ?';
			} else {
				throw new Exception('System Error: you can ONLY get payments for a Order or a CreditNote');
			}
			$stats = $items = array();
			foreach(Payment::getAllByCriteria($where, array($entity->getId()), true, $pageNo, $pageSize, array('id' => 'desc'), $stats) as $payment) {
				$items[] = $payment->getJson();
			}
			$result['pagination'] =  $stats;
			$result['items'] = $items;
			if($pageNo === 1) {
				$result['paymentMethods'] = array_map(create_function('$a', 'return $a->getJson();'), PaymentMethod::findAll());
			}
		} catch(Exception $ex) {
			$error[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($result, $error);
	}
	/**
	 *
	 * @param unknown $sender
	 * @param unknown $params
	 * @throws Exception
	 */
	public function addPayment($sender, $param)
	{
		$results = $errors = array();
		try
		{
			Dao::beginTransaction();
			if(!isset($param->CallbackParameter->againstEntity) || !isset($param->CallbackParameter->againstEntity->entity) || !isset($param->CallbackParameter->againstEntity->entityId) || ($entityName = trim($param->CallbackParameter->againstEntity->entity)) === '' || !($entity = $entityName::get(trim($param->CallbackParameter->againstEntity->entityId))) instanceof $entityName)
				throw new Exception('System Error: invalid Order or CreditNote provided. Can NOT get any payments at all.');
			if(!$entity instanceof Order && !$entity instanceof CreditNote) {
				throw new Exception('System Error: you can ONLY add payments for a Order or a CreditNote');
			}
			if(!isset($param->CallbackParameter->payment) || !isset($param->CallbackParameter->payment->paidAmount) || ($paidAmount = StringUtilsAbstract::getValueFromCurrency(trim($param->CallbackParameter->payment->paidAmount))) === '' || !is_numeric($paidAmount))
				throw new Exception('System Error: invalid Paid Amount passed in!');
			if(!isset($param->CallbackParameter->payment->payment_method_id) || ($paymentMethodId = trim($param->CallbackParameter->payment->payment_method_id)) === '' || !($paymentMethod = PaymentMethod::get($paymentMethodId)) instanceof PaymentMethod)
				throw new Exception('System Error: invalid Payment Method passed in!');
			$notifyCust = (isset($param->CallbackParameter->payment->notifyCust) && intval($param->CallbackParameter->payment->notifyCust) === 1) ? true : false;
			$extraComment = '';
			if(!isset($param->CallbackParameter->payment->extraComments) || ($extraComment = trim($param->CallbackParameter->payment->extraComments)) === '')
				throw new Exception('Some comments for this payment is required.');

			//save the payment
			$newPayment = null;
			$entity = $entity->addPayment($paymentMethod, $paidAmount, $extraComment, new UDate(), $newPayment);
			$results['item'] = $newPayment->getJson();

			//notify the customer
			if($entity instanceof Order && $notifyCust === true && $entity->getIsFromB2B() === true)
			{
				$notificationMsg = trim(OrderNotificationTemplateControl::getMessage('paid', $entity));
				if($notificationMsg !== '')
				{
					B2BConnector::getConnector(B2BConnector::CONNECTOR_TYPE_ORDER,
							SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_WSDL),
							SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_USER),
							SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_KEY)
						)->changeOrderStatus($entity, OrderStatus::get(OrderStatus::ID_PICKED)->getMageStatus(), $notificationMsg, true);
					$comments = 'An email notification contains payment checked info has been sent to customer for: ' . $entity->getStatus()->getName();
					Comments::addComments($entity, $comments, Comments::TYPE_SYSTEM);
				}
			}

			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
	/**
	 * deleting a payment
	 *
	 * @param unknown $sender
	 * @param unknown $param
	 *
	 * @throws Exception
	 */
	public function delPayment($sender, $param)
	{
		$results = $errors = array();
		try
		{
			Dao::beginTransaction();
			if(!isset($param->CallbackParameter->paymentId) || !($payment = Payment::get($param->CallbackParameter->paymentId)) instanceof Payment)
				throw new Exception('System Error: invalid payment provided!');

			if(!isset($param->CallbackParameter->reason) || ($reason = trim($param->CallbackParameter->reason)) === '')
				throw new Exception('The reason for the deletion is needed!');

			$comments = 'A payment [Value: ' .  StringUtilsAbstract::getCurrency($payment->getValue()) . ', Method: ' . $payment->getMethod()->getName() . '] is DELETED: ' . $reason;
			$payment->setActive(false)
				->addComment($comments, Comments::TYPE_ACCOUNTING)
				->save();
			$entityFor = $payment->getOrder() instanceof Order ? $payment->getOrder() : $payment->getCreditNote();
			if($entityFor instanceof Order || $entityFor instanceof CreditNote)
				$entityFor->addComment($comments, Comments::TYPE_ACCOUNTING);
			$results['item'] = $payment->getJson();
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
?>
