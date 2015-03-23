<?php
require_once 'bootstrap.php';
try {
	Dao::beginTransaction();
	echo '<pre>';
	
	//add a creditnote as normal
	Core::setUser(UserAccount::get(10));
	$creditNote = CreditNote::create(Customer::get(1), 'description');
	var_dump('Got CreditNote No.:' . $creditNote->getCreditNoteNo());
	$creditItem = null;
	$creditNote->addItem(Product::get(2241), 5, 10, 'item description', 8, $creditItem);
	var_dump('added CreditNoteItem (id=' . $creditItem->getId() . ') onto creditNote(NO.= ' . $creditNote->getCreditNoteNo() . ')');
	
	$creditNote = CreditNote::createFromOrder(Order::get(4290), null, 'description');
	var_dump('Create CreditNote No.:' . $creditNote->getCreditNoteNo() . ' from Order(OrderNo.=' . $creditNote->getOrder()->getOrderNo() . ')');
	$creditItem = null;
	$creditNote->addItemFromOrderItem(OrderItem::get(5639), 5, 10, 'item description', 8, $creditItem);
	var_dump('Create CreditNoteItem  (id=' . $creditItem->getId() . ') onto creditNote(NO.= ' . $creditNote->getCreditNoteNo() . ')');
	
	$rma = RMA::create(Customer::get(1), 'description');
	var_dump('Got RMA No.:' . $rma->getRaNo());
	$item = null;
	$rma->addItem(Product::get(2241), 10, 'item description', 8, $item);
	var_dump('added RMAItem (id=' . $item->getId() . ') onto RMA(NO.= ' . $rma->getRaNo() . ')');

	$rma = RMA::createFromOrder(Order::get(4290), null, 'description');
	var_dump('Create RMA No.:' . $rma->getRaNo() . ' from Order(OrderNo.=' . $rma->getOrder()->getOrderNo() . ')');
// 	$item = null;
// 	$rma->addItemFromOrderItem(OrderItem::get(5639), 10, 'item description', 8, $item);
// 	var_dump('Create RMA  (id=' . $item->getId() . ') onto RMA(NO.= ' . $rma->getRaNo() . ')');
	Dao::commitTransaction();
} catch (Exception $e)
{ 
	Dao::rollbackTransaction();
	throw $e;
}
?>