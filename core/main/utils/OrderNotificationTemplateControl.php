<?php
abstract class OrderNotificationTemplateControl
{
	public static function getMessage($status, Order $order)
	{
		$method_name = "_" . strtolower(trim(str_replace(" ", '_', $status)));
		if(!method_exists(get_called_class(), $method_name))
			return "";
		return self::$method_name($order);
	}
	
	private static function _eta(Order $order)
	{
		return self::_orderItems($order);
	}
	
	private static function _stock_checked(Order $order)
	{
		return self::_orderItems($order);
	}
	
	private static function _paid(Order $order)
	{
		$msg = '<div>Thank you for your order!</div>';
		$msg .= '<div>We have received payment for this order, and our logistics will ship this order out soon!</div>';
		return $msg;
	}
	
	private static function _shipped(Order $order)
	{
		$msg = '<div>Thank you for your order, your following order is now complete and shipped.</div>';
		$msg .= '<div>Please find the tracking information below:</div>';
		$msg .= '<div>';
			$msg .= '<table cellspacing="0" cellpadding="0" border="0" height="100%" width="100%">';
			$msg .= "<tr>";
				$msg .= "<td width='25%' style='text-align:right'>Order Number:</td>";
				$msg .= "<td width='*'>" . $order->getOrderNo() . "</td>";
			$msg .= "</tr>";
			$shippments = $order->getShippments();
			if(count($shippments) > 0)
			{
				$shippment = $shippments[0];
				$msg .= "<tr>";
					$msg .= "<td >Courier:</td>";
					$msg .= "<td >" . $shippment->getCourier()->getName() . "</td>";
				$msg .= "</tr>";
				$msg .= "<tr>";
					$msg .= "<td >Number of boxes:</td>";
					$msg .= "<td >" . $shippment->getNoOfCartons() . "</td>";
				$msg .= "</tr>";
				$msg .= "<tr>";
					$msg .= "<td >Track Number:</td>";
					$msg .= "<td >" . $shippment->getConNoteNo() . "</td>";
				$msg .= "</tr>";
				$msg .= "<tr>";
					$msg .= "<td >Delivery Instructions:</td>";
					$msg .= "<td >" . $shippment->getDeliveryInstructions() . "</td>";
				$msg .= "</tr>";
			}
			$msg .= "</table>";
		$msg .= '</div>';
		return $msg;
	}
	
	private static function _orderItems(Order $order)
	{
		$msg = "<div>";
			$msg .= "<div>Thank you for your support, we have received your following order, and it is currently in processing.</div>";
			$msg .= "<div>Here are the item information/status on your order, our logistics will have them shipped out as soon as possible.</div>";
			$msg .= "<div>If you have any item with long ETA waiting time and you want to ship part of your order first, please email <a href='mailto:sales@budgetpc.com.au'>sales@budgetpc.com.au</a> and quote your order number when you call.</div>";
			$msg .= "<div style='margin: 10px 0 10px 0'>";
				$msg .= '<table cellspacing="0" cellpadding="0" border="0" height="100%" width="100%">';
					$msg .= "<thead>";
						$msg .= "<tr style='background:#000000; color:#ffffff;height:23px;'>";
							$msg .= "<td width='*'>Item</td>";
							$msg .= "<td width='30%'>SKU</td>";
							$msg .= "<td width='10%' style='text-align:center;'>Qty</td>";
							$msg .= "<td width='20%'>Status</td>";
						$msg .= "</tr>";
					$msg .= "</thead>";
					$msg .= "<tbody>";
						foreach($order->getOrderItems() as $item)
						{
							$msg .= "<tr style='height:18px; border-bottom: 1px #cccccc solid;'>";
								$msg .= "<td>" . $item->getProduct()->getName() . "</td>";
								$msg .= "<td>" . $item->getProduct()->getSku() . "</td>";
								$msg .= "<td style='text-align:center;'>" . $item->getQtyOrdered() . "</td>";
								$msg .= "<td>" . (trim($item->getEta()) === trim(UDate::zeroDate()) ? 'In Stock' : 'ETA: ' . $item->getEta()->format('d F Y')) . "</td>";
							$msg .= "</tr>";
						}
					$msg .= "</tbody>";
				$msg .= "</table>";
			$msg .= "</div>";
		$msg .= "</div>";
		return $msg;
	}
}