<?php
/**
 * Entity for Payment
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class Payment extends BaseEntityAbstract
{
	const TYPE_PAYMENT = 'PAYMENT';
	const TYPE_CREDIT = 'CREDIT';
	/**
	 * The type of the payment
	 *
	 * @var string
	 */
	private $type = '';
	/**
	 * The payment method
	 *
	 * @var PaymentMethod
	 */
	protected $method;
	/**
	 * The order of this payment
	 *
	 * @var Order
	 */
	protected $order = null;
	/**
	 * The credit not of this payment
	 *
	 * @var CreditNote
	 */
	protected $creditNote = null;
	/**
	 * The value of this payment
	 *
	 * @var double
	 */
	private $value;
	/**
	 * The payment date of this payment
	 *
	 * @var UDate
	 */
	private $paymentDate;
	/**
	 * Getter for method
	 *
	 * @return PaymentMethod
	 */
	public function getMethod()
	{
	    $this->loadManyToOne('method');
		return $this->method;
	}
	/**
	 * Setter for method
	 *
	 * @param PaymentMethod $value The method
	 *
	 * @return Payment
	 */
	public function setMethod(PaymentMethod $value)
	{
	    $this->method = $value;
	    return $this;
	}
	/**
	 * Getter for order
	 *
	 * @return Order
	 */
	public function getOrder()
	{
		$this->loadManyToOne('order');
		return $this->order;
	}
	/**
	 * Setter for order
	 *
	 * @param unkown $value The order
	 *
	 * @return Payment
	 */
	public function setOrder(Order $value = null)
	{
	    $this->order = $value;
	    return $this;
	}
	/**
	 * Getter for value
	 *
	 * @return number
	 */
	public function getValue()
	{
	    return $this->value;
	}
	/**
	 * Setter for value
	 *
	 * @param number $value The value
	 *
	 * @return Payment
	 */
	public function setValue($value)
	{
	    $this->value = $value;
	    return $this;
	}
	/**
	 * Getter for type
	 *
	 * @return string
	 */
	public function getType()
	{
	    return $this->type;
	}
	/**
	 * Setter for type
	 *
	 * @param string $value The type
	 *
	 * @return Payment
	 */
	public function setType($value)
	{
	    $this->type = $value;
	    return $this;
	}
	/**
	 * Getter for creditNote
	 *
	 * @return CreditNote
	 */
	public function getCreditNote()
	{
		$this->loadManyToOne('creditNote');
	    return $this->creditNote;
	}
	/**
	 * Setter for creditNote
	 *
	 * @param CreditNote $value The creditNote
	 *
	 * @return Payment
	 */
	public function setCreditNote(CreditNote $value = null)
	{
	    $this->creditNote = $value;
	    return $this;
	}
	/**
	 * Getter for paymentDate
	 *
	 * @return UDate
	 */
	public function getPaymentDate()
	{
		return new UDate(trim($this->paymentDate));
	}
	/**
	 * Setter for paymentDate
	 *
	 * @param string $value The paymentDate
	 *
	 * @return Payment
	 */
	public function setPaymentDate($value)
	{
		$this->paymentDate = $value;
		return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::__toString()
	 */
	public function __toString()
	{
		return trim($this->getMethod() . ': ' . $this->getValue() . " for Order:" . $this->getOrder()->getOrderNo());
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::preSave()
	 */
	public function preSave()
	{
		if(!$this->getCreditNote() instanceof CreditNote && !$this->getOrder() instanceof Order)
			throw new EntityException('You need to create a payment against at least one of these: Order / CreditNote');
		if(trim($this->getType()) === '')
			$this->setType($this->getValue() < 0 ? self::TYPE_CREDIT : self::TYPE_PAYMENT);
		if(trim($this->paymentDate) === '')
			$this->setPaymentDate(new UDate());
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::postSave()
	 */
	public function postSave()
	{
		//update the order
		if($this->getOrder() instanceof Order) {
			$totalPaidAmount = 0;
			foreach($this->getOrder()->getPayments() as $payment)
				$totalPaidAmount = $totalPaidAmount * 1 + $payment->getValue() * 1;
			if($this->getOrder()->getType() === Order::TYPE_INVOICE) {
				$msg = '';
				if(intval($this->getActive()) === 1 && intval($this->getOrder()->getPassPaymentCheck()) !== 1) {
					$this->getOrder()->setPassPaymentCheck(true)->save();
					$msg = "Marked Payment Checked as first payment go through.";
				}
				else if(intval($this->getActive()) === 0 && self::countByCriteria('orderId = ? and active = 1', array($this->getOrder()->getId())) <= 0){
					$this->getOrder()->setPassPaymentCheck(false)->save();
					$msg = "Marked Payment UNCHECKED as last payment deactivated.";
				}
				$this->getOrder()->setTotalPaid($totalPaidAmount)
					->save();
				if($msg !== '')
					$this->getOrder()->addComment($msg, Comments::TYPE_SYSTEM)
						->addLog($msg, Log::TYPE_SYSTEM, 'AUTO_GEN', __CLASS__ . '::' . __FUNCTION__);
			}
		}
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::getJson()
	 */
	public function getJson($extra = array(), $reset = false)
	{
		$array = $extra;
		if(!$this->isJsonLoaded($reset))
		{
			$array['method'] = $this->getMethod()->getJson();
			$array['createdBy'] = $this->getCreatedBy()->getJson();
		}
		return parent::getJson($array, $reset);
	}

	/**
	 * (non-PHPdoc)
	 * @see HydraEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'py');

		DaoMap::setManyToOne('order', 'Order', 'ord', true);
		DaoMap::setManyToOne('creditNote', 'CreditNote', 'py_cn', true);
		DaoMap::setManyToOne('method', 'PaymentMethod', 'py_method');
		DaoMap::setIntType('value', 'Double', '10,4', false);
		DaoMap::setStringType('type', 'varchar', '10');
		DaoMap::setDateType('paymentDate');
		parent::__loadDaoMap();

		DaoMap::createIndex('type');
		DaoMap::commit();
	}
	/**
	 * Creating a payment for order
	 *
	 * @param Order         $order
	 * @param PaymentMethod $method
	 * @param string        $value
	 * @param string        $comments
	 *
	 * @return Ambigous <BaseEntityAbstract, GenericDAO>
	 */
	public static function create(Order &$order, PaymentMethod $method, $value, $comments = '', $paymentDate = '')
	{
		$payment = new Payment();
		$message = 'A ' . StringUtilsAbstract::getCurrency($value) . ' is made payment via ' . $method->getName() . ' for Order(OrderNo.=' . $order->getOrderNo() . ')' . (trim($comments) !== '' ? ': ' . $comments : '.');
		$payment = $payment->setOrder($order)
			->setMethod($method)
			->setValue($value)
			->setPaymentDate(new UDate(trim($paymentDate) === '' ? 'now' : trim($paymentDate)))
			->save()
			->addComment($message, Comments::TYPE_ACCOUNTING)
			->addLog($message, Log::TYPE_SYSTEM, get_class($payment) . '_CREATION', __CLASS__ . '::' . __FUNCTION__);
		$order->addComment($message, Comments::TYPE_ACCOUNTING)
			->addLog($message, Log::TYPE_SYSTEM, 'Auto Log', __CLASS__ . '::' . __FUNCTION__);
		return $payment;
	}
	/**
	 * Creating a payment for creditNote
	 *
	 * @param CreditNote    $creditNote
	 * @param PaymentMethod $method
	 * @param string        $value
	 * @param string        $comments
	 *
	 * @return Ambigous <BaseEntityAbstract, GenericDAO>
	 */
	public static function createFromCreditNote(CreditNote &$creditNote, PaymentMethod $method, $value, $comments = '', $paymentDate = '')
	{
		$payment = new Payment();
		$message = 'A ' . StringUtilsAbstract::getCurrency($value) . ' Credit Payment is made via ' . $method->getName() . ' for CreditNote(CreditNoteNo.=' . $creditNote->getCreditNoteNo() . ')' . (trim($comments) !== '' ? ': ' . $comments : '.');
		$payment = $payment->setCreditNote($creditNote)
			->setMethod($method)
			->setValue($value)
			->setPaymentDate(new UDate(trim($paymentDate) === '' ? 'now' : trim($paymentDate)))
			->save()
			->addComment($message, Comments::TYPE_ACCOUNTING)
			->addLog($message, Log::TYPE_SYSTEM, get_class($payment) . '_CREATION', __CLASS__ . '::' . __FUNCTION__);
		$creditNote->addComment($message, Comments::TYPE_ACCOUNTING)
			->addLog($message, Log::TYPE_SYSTEM, 'Auto Log', __CLASS__ . '::' . __FUNCTION__);
		return $payment;
	}
}
