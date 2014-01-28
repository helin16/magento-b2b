<?php
/**
 * This is the OrderDetailsController
 * 
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class OrderPrintController extends BPCPageAbstract
{
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$menuItem
	 */
	public $menuItem = 'order';
	/**
	 * The order that we are viewing
	 * 
	 * @var Order
	 */
	public $order = null;
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::onLoad()
	 */
	public function onLoad($param)
	{
		parent::onLoad($param);
		if(!$this->isPostBack)
		{
			$this->order = FactoryAbastract::service('Order')->get($this->Request['orderId']);
			if(!$this->order instanceof Order)
				die('Invalid Order!');
		}
	}
	/**
	 * Getting the tr for each row
	 * @param unknown $qty
	 * @param unknown $sku
	 * @param unknown $name
	 * @param unknown $uprice
	 * @param unknown $tprice
	 * @return string
	 */
	public function getRow($qty, $sku, $name, $uprice, $tprice, $rowClass="")
	{
		return "<tr class='$rowClass'><td class='qty'>$qty</td><td class='sku'>$sku</td><td class='name'>$name</td><td class='uprice'>$uprice</td><td class='tprice'>$tprice</td></tr>";
	}
	/**
	 * 
	 * @return string
	 */
	public function showProducts()
	{
		$html = '';
		foreach($this->order->getOrderItems() as  $index => $orderItem)
		{
			$uPrice = '$' . number_format($orderItem->getUnitPrice(), 2, '.', ',');
			$tPrice = '$' . number_format($orderItem->getTotalPrice(), 2, '.', ',');
			$html .= $this->getRow($orderItem->getQtyOrdered(), $orderItem->getProduct()->getSku(), $orderItem->getProduct()->getname(), $uPrice, $tPrice, 'itemRow');
		}
		for ( $i = 12; $i > $index; $i--)
		{
			$html .= $this->getRow('&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', 'itemRow');
		}
		return $html;
	}
	public function getAddress($type)
	{
		$method = 'get' . ucfirst($type) . 'Addr';
		$address = $this->order->$method();
		if(!$address instanceof Address)
			return '';
		$html = $address->getContactName() . '<br />';
		$html .= $address->getStreet() . '<br />';
		$html .= $address->getCity() . ' ' . $address->getRegion() . ' ' . $address->getPostCode() . '<br />';
		$html .= $address->getCountry();
		return $html;
	}
	public function getPaymentSummary()
	{
		$total = $this->order->getTotalAmount();
		$totalNoGST = $total / 1.1;
		$gst = $total - $totalNoGST;
		$html = $this->_getPaymentSummaryRow('Total:', '$' . number_format($totalNoGST, 2, '.', ','), 'grandTotalNoGST');
		$html .= $this->_getPaymentSummaryRow('GST:', '$' . number_format($gst, 2, '.', ','), 'gst');
		$html .= $this->_getPaymentSummaryRow('Total(inc-GST):', '$' . number_format($total, 2, '.', ','), 'grandTotal');
		$html .= $this->_getPaymentSummaryRow('Paid to Date:', '$' . number_format($this->order->getTotalPaid(), 2, '.', ','), 'paidTotal');
		$overDueClass = $this->order->getTotalDue() > 0 ? 'overdue' : '';
		$html .= $this->_getPaymentSummaryRow('Balance Due:', '$' . number_format($this->order->getTotalDue(), 2, '.', ','), 'dueTotal ' . $overDueClass);
		return $html;
	}
	private function _getPaymentSummaryRow($title, $content, $class='')
	{
		$html = '<div class="row ' . $class . '">';
			$html .= '<span class="details_title inlineblock">';
				$html .= $title;
			$html .= '</span>';
			$html .= '<span class="details_content inlineblock">';
				$html .= $content;
			$html .= '</span>';
		$html .= '</div>';
		return $html;
	}
}
?>