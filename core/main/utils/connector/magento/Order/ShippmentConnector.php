<?php
class ShippmentConnector extends B2BConnector
{
	/**
	 * Getting the courier for an order
	 * 
	 * @param string $orderId The id of an order
	 * 
	 * @return array
	 */
	public function getCouriers($orderId)
	{
		return $this->_connect()->salesOrderShipmentGetCarriers($this->_session, $orderId);
	}
	/**
	 * Ship out an order
	 * 
	 * @param Order              $order          The order object
	 * @param Shippment          $shippment      The Shippment object
	 * @param multiple:OrderItem $orderItems     The array of OrderItem
	 * @param string             $comments       The comments
	 * @param bool               $emailCust      Whether we email the customer
	 * @param bool               $includeInEmail Whether we include the comments on the email
	 * 
	 * @return bool Whether the action has done successfully
	 * @throws ConnectorException
	 */
	public function shipOrder(Order $order, Shippment &$shippment, array $orderItems = array(), $comments = '', $emailCust = false, $includeInEmail = false)
	{
		$couriers = $this->getCouriers($order->getOrderNo());
		$courierCode = trim($shippment->getCourier()->getCode());
		if(!array_key_exists($courierCode, $couriers))
			throw new ConnectorException('System Error: Courier Code is NOT found for: ' . $courierCode . ', ask your magento website admin to add this to Magento!');
		
		$itemsQty = array();
		foreach($orderItems as $item)
		{
			$itemsQty[$item->getMageItemId()] = $item->getQtyOrdered();
		}
		//now create a shippment for this order
		$shipmentId = $this->_connect()->salesOrderShipmentCreate($this->_session, $order->getOrderNo(), $itemsQty);
		
		//record the magento shipment id now
		$shippment->setMageShipmentId($shipmentId);
		FactoryAbastract::service('Shippment')->save($shippment);
		
		//adding the track number
		$mageTrackId = $this->_connect()->salesOrderShipmentAddTrack($this->_session, $order->getOrderNo(), $courierCode, 'Track Number', $shippment->getConNoteNo());
		//TODO: maybe we should do something for the magento track id!!!!
		
		return $this->_connect()->salesOrderShipmentAddComment($this->_session, $order->getOrderNo(), $comments, $emailCust, includeInEmail);
	}
}