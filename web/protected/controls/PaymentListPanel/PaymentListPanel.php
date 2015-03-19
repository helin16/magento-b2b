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
					$this->getPage()->getClientScript()->registerScriptFile('latestETAJs', $this->publishAsset($value));
				else if($key === 'css')
					$this->getPage()->getClientScript()->registerStyleSheetFile('latestETACss', $this->publishAsset($value));
			}
		}
		if(!$this->getPage()->IsCallBack && !$this->getPage()->IsPostBack) {
			$js = 'if(typeof(PaymentListPanelJs) !== "undefined") {';
				$js .= 'PaymentListPanelJs.callbackIds = ' . json_encode(array(
						'getPayments' => $this->getPaymentsBtn->getUniqueID()
						,'delPayments' => $this->delPaymentBtn->getUniqueID()
						,'addPayments' => $this->addPaymentBtn->getUniqueID()
				)) . ';';
			$js .= '}';
			$this->getPage()->getClientScript()->registerEndScript('lepJs', $js);		
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
			foreach(Payment::getAllByCriteria($where, array($entity->getId()), true, $pageNo, $pageSize, array(), $stats) as $payment) {
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
}
?>
