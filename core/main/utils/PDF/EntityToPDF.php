<?php
/**
 * an utils to convert an Entity to PDF
 * @author lhe
 *
 */
class EntityToPDF
{
	/**
	 * The root directory of the template files
	 *
	 * @var string
	 */
	private static $_templateDir = '';
	/**
	 * The loaded template files
	 *
	 * @var array
	 */
	private static $_tempCache = array();
	/**
	 * Getting the a HTML2PDF object from an entity
	 *
	 * @param BaseEntityAbstract $entity
	 *
	 * @throws CoreException
	 * @return HTML2PDF
	 */
	public static function getPDF(BaseEntityAbstract $entity, $orientation = 'P', $format = 'A4', $langue = 'en', $unicode = true, $encoding='UTF-8', $marges = array(5, 5, 5, 8))
	{
		self::$_templateDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
		$class = get_class($entity);
		switch($class)
		{
			case 'Order': {
				$content = self::_order($entity);
				break;
			}
			case 'PurchaseOrder': {
				$content = self::_purchaseOrder($entity);
				break;
			}
			default: {
				throw new CoreException('NO such a function to covert entity:' . $class);
			}
		}
		$html2pdf = new HTML2PDF($orientation, $format, $langue, $unicode, $encoding, $marges);
		//$html2pdf->setModeDebug();
		$html2pdf->setDefaultFont('Arial');
		$html2pdf->writeHTML($content);
		return $html2pdf;
	}
	/**
	 * converting a Order to be a pdf content string
	 *
	 * @param PurchaseOrder $entity
	 *
	 * @return string
	 */
	private static function _order(Order $entity)
	{
		$templateString = self::_getTemplateFile($entity, 'order.tpl');
		$values = self::_getDefaultValues();
		
		$values['orderNo'] = $entity->getOrderNo();
		$values['OrderDate'] = $entity->getOrderDate()->format('d/M/Y');
		$values['InvNo'] = $entity->getInvNo();
		$values['InvDate'] = $entity->getInvDate() == UDate::zeroDate() ? '' : $entity->getInvDate()->format('d/M/Y');
		$values['PONo'] = $entity->getPONo();
		$values['AddressBilling'] = self::getAddress($entity->getBillingAddr());
		$values['AddressShipping'] = self::getAddress($entity->getShippingAddr());
		$values['headerRow'] = self::getRow('QTY', 'SKU', 'NAME', 'Unit Price', 'Total Price', 'header');
		$values['productDiv'] = self::getOrderProductDiv($entity);
		$values['UpdatedByPerson'] = $entity->getUpdatedBy()->getPerson()->getFullName();
		$values['PaymentSummary'] = self::getPaymentSummary($entity);
		return self::_bindData($templateString, $values);
	}
	/**
	 * getting the PurchaseOrder pdf string
	 *
	 * @param PurchaseOrder $entity
	 *
	 * @return string
	 */
	private static function _purchaseOrder(PurchaseOrder $entity)
	{
		$templateString = self::_getTemplateFile($entity, 'purchaseorder.tpl');
		$values = self::_getDefaultValues();
		
		$values['orderNo'] = $entity->getPurchaseOrderNo();
		$values['OrderDate'] = $entity->getOrderDate()->format('d/M/Y');
		$values['Supplier'] = $entity->getSupplier()->getName();
		$values['SupplierRef'] = $entity->getSupplierRefNo();
		$values['TotalProductCount'] = $entity->getTotalProductCount();
		$values['headerRow'] = self::getRow('QTY', 'SKU', 'NAME', 'Unit Price', 'Total Price', 'header');
		$values['productDiv'] = self::getPOProductDiv($entity);
		$values['UpdatedByPerson'] = $entity->getUpdatedBy()->getPerson()->getFullName();
		$values['PaymentSummary'] = self::getPaymentSummary($entity);
		
		return self::_bindData($templateString, $values);
	}
	private static function getOrderProductDiv(Order $order)
	{
		$html = '';
		foreach($order->getOrderItems() as  $index => $orderItem)
		{
			$uPrice = '$' . number_format($orderItem->getUnitPrice(), 2, '.', ',');
			$tPrice = '$' . number_format($orderItem->getTotalPrice(), 2, '.', ',');
			$sellingItems = array();
			foreach($orderItem->getSellingItems() as $item) {
				if($item->getSerialNo() !== '' )
					$sellingItems[] = $item->getSerialNo();
			}
			$html .= self::getRow($orderItem->getQtyOrdered(), $orderItem->getProduct()->getSku(), $orderItem->getProduct()->getname(), $uPrice, $tPrice, 'itemRow');
			$html .= self::getRow('', '<span class="pull-right">Serial No: </span>', '<div style="max-width: 367px; word-wrap: break-word;">' . implode(', ', $sellingItems) . '</div>', '', '', 'itemRow itemRow-serials');
		}
		for ( $i = 12; $i > $index; $i--)
		{
		$html .= self::getRow('&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', 'itemRow');
		}
		return $html;
	}
	private static function getPOProductDiv(PurchaseOrder $order)
	{
		$html = '';
		$purchaseOrderItems = PurchaseOrderItem::getAllByCriteria('purchaseOrderId = ?', array($order->getId() ) );
		foreach($purchaseOrderItems as  $index => $orderItem)
		{
			$uPrice = '$' . number_format($orderItem->getUnitPrice(), 2, '.', ',');
			$tPrice = '$' . number_format($orderItem->getTotalPrice(), 2, '.', ',');
			$html .= self::getRow($orderItem->getQty(), $orderItem->getProduct()->getSku(), $orderItem->getProduct()->getname(), $uPrice, $tPrice, 'itemRow');
		}
		for ( $i = 12; $i > $index; $i--)
		{
			$html .= self::getRow('&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;', 'itemRow');
		}
		return $html;
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
	private static function getRow($qty, $sku, $name, $uprice, $tprice, $rowClass="")
	{
		return "<tr class='$rowClass'><td class='qty'>$qty</td><td class='sku'>$sku</td><td class='name'>$name</td><td class='uprice'>$uprice</td><td class='tprice'>$tprice</td></tr>";
	}
	private static function getPaymentSummary($order)
	{
		$html = '';
		if($order instanceof Order)
		{
			$total = $order->getTotalAmount();
			$totalNoGST = $total / 1.1;
			$gst = $total - $totalNoGST;
			$html = self::_getPaymentSummaryRow('Total:', '$' . number_format($totalNoGST, 2, '.', ','), 'grandTotalNoGST');
			$html .= self::_getPaymentSummaryRow('GST:', '$' . number_format($gst, 2, '.', ','), 'gst');
			$html .= self::_getPaymentSummaryRow('Total(inc-GST):', '$' . number_format($total, 2, '.', ','), 'grandTotal');
			$html .= self::_getPaymentSummaryRow('Paid to Date:', '$' . number_format($order->getTotalPaid(), 2, '.', ','), 'paidTotal');
			$overDueClass = $order->getTotalDue() > 0 ? 'overdue' : '';
			$html .= self::_getPaymentSummaryRow('Balance Due:', '$' . number_format($order->getTotalDue(), 2, '.', ','), 'dueTotal ' . $overDueClass);
		} 
		else if($order instanceof PurchaseOrder)
		{
			$total = $order->getTotalAmount();
			$totalNoGST = $total / 1.1;
			$gst = $total - $totalNoGST;
			$totalDue = $total - $order->getTotalPaid();
			$html = self::_getPaymentSummaryRow('Total:', '$' . number_format($totalNoGST, 2, '.', ','), 'grandTotalNoGST');
			$html .= self::_getPaymentSummaryRow('GST:', '$' . number_format($gst, 2, '.', ','), 'gst');
			$html .= self::_getPaymentSummaryRow('Total(inc-GST):', '$' . number_format($total, 2, '.', ','), 'grandTotal');
			$html .= self::_getPaymentSummaryRow('Paid to Date:', '$' . number_format($order->getTotalPaid(), 2, '.', ','), 'paidTotal');
			$overDueClass = $totalDue > 0 ? 'overdue' : '';
			$html .= self::_getPaymentSummaryRow('Balance Due:', '$' . number_format($totalDue, 2, '.', ','), 'dueTotal ' . $overDueClass);
		}
		else throw new Exception(__CLASS__ . '::' . __FUNCTION__ . ': must pass in Order or PurchaseOrder');
		return $html;
	}
	private static function _getPaymentSummaryRow($title, $content, $class='')
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
	private static function getAddress(Address $address)
	{
		$html = $address->getContactName() . '<br />';
		$html .= $address->getStreet() . '<br />';
		$html .= $address->getCity() . ' ' . $address->getRegion() . ' ' . $address->getPostCode() . '<br />';
		$html .= $address->getCountry();
		return $html;
	}
	/**
	 * Binding some data to the template string
	 *
	 * @param unknown $templateString
	 * @param array   $values
	 *
	 * @return string
	 */
	private static function _bindData($templateString, array $values = array())
	{
		return str_replace(array_map(create_function('$a', 'return "{" . $a . "}";'), array_keys($values)), array_values($values), $templateString);
	}
	/**
	 * Getting the default value for the templates
	 *
	 * @return multitype:string
	 */
	private static function _getDefaultValues()
	{
		return array(
			'imgDir' => ($imgDir = self::$_templateDir . DIRECTORY_SEPARATOR . 'images')
			,'logoUrl' => $imgDir . '/logo.png'
			,'headerSepUrl' => $imgDir . '/inv_sep.png'
		);
	}
	/**
	 * Getting the template file of entity's pdf
	 *
	 * @param BaseEntityAbstract $entity
	 * @param string             $fileName
	 *
	 * @return string
	 */
	private static function _getTemplateFile(BaseEntityAbstract $entity, $fileName)
	{
		$entityClass = get_class($entity);
		if(!isset(self::$_tempCache[$entityClass])) {
			$fileName = self::$_templateDir . $fileName;
			if(!is_file($fileName))
				throw new CoreException('System Error: no such a template file:' . $fileName);
			self::$_tempCache[$entityClass] = file_get_contents($fileName);
		}
		return self::$_tempCache[$entityClass];
	}
}