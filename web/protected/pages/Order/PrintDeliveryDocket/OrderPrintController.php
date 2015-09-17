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
			$this->order = Order::get($this->Request['orderId']);
			if(!$this->order instanceof Order)
				die('Invalid Order!');
			if(isset($_REQUEST['pdf']) && intval($_REQUEST['pdf']) === 1)
			{
				$file = EntityToPDF::getPDF($this->order, 'docket');
				header('Content-Type: application/pdf');
				// The PDF source is in original.pdf
				readfile($file);
				die;
			}
		}
	}
	public function getType()
	{
		return $this->order->getType() === Order::TYPE_INVOICE ? 'TAX ' . Order::TYPE_INVOICE : $this->order->getType();
	}
	public function getInvDate()
	{
		return $this->order->getInvDate() == UDate::zeroDate() ? '' : $this->order->getInvDate()->format('d/M/Y');
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
		return "<tr class='$rowClass'><td class='qty'>$qty</td><td class='sku'>$sku</td><td class='name'>$name</td></tr>";
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
			$sellingItems = array();
			foreach($orderItem->getSellingItems() as $item) {
				if($item->getSerialNo() !== '' )
					$sellingItems[] = $item->getSerialNo();
			}
			$html .= $this->getRow($orderItem->getQtyOrdered(), $orderItem->getProduct()->getSku(), $orderItem->getProduct()->getname() ?: $orderItem->getItemDescription(), $uPrice, $tPrice, 'itemRow');
			$html .= $this->getRow('', '<span class="pull-right">Serial No: </span>', '<div style="max-width: 517px; word-wrap: break-word;">' . implode(', ', $sellingItems) . '</div>', '', '', 'itemRow itemRow-serials');
		}
		for ( $i = 12; $i > $index; $i--)
		{
			$html .= $this->getRow('&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', 'itemRow');
		}
		return $html;
	}
	public function getContact()
	{
		$contact = $this->order->getCustomer()->getContactNo();
		return empty($contact) ? '' : '(' . $contact . ')';
	}
	public function getAddress($type)
	{
		$method = 'get' . ucfirst($type) . 'Addr';
		$address = $this->order->$method();
		if(!$address instanceof Address)
			return '';
		$html = '';
		if(trim($this->order->getCustomer()->getName()) !== trim($address->getContactName()))
			$html .= $this->order->getCustomer()->getName() . '<br />';
		$html .= $address->getContactName() . '<br />';
		$html .= $address->getStreet() . '<br />';
		$html .= $address->getCity() . ' ' . $address->getRegion() . ' ' . $address->getPostCode() . '<br />';
// 		$html .= $address->getCountry();
		$html .= 'Tel: ' . ($this->getContact() === '' ? trim($address->getContactNo()) : $this->getContact());
		return $html;
	}
	public function getPaymentSummary()
	{
		$total = $this->order->getTotalAmount();
		$totalNoGST = $total / 1.1;
		$gst = $total - $totalNoGST;
		$html = $this->_getPaymentSummaryRow('<strong>Receiving Info</strong>', '');
		$html .= $this->_getPaymentSummaryRow('<small>Print Name:</small>', '');
		$html .= $this->_getPaymentSummaryRow('<small>Signature:</small>', '');
		$html .= $this->_getPaymentSummaryRow('<small>Date:</small>', '');
		return $html;
	}
	private function _getPaymentSummaryRow($title, $content, $class='')
	{
		$html = '<div class="print-row ' . $class . '">';
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