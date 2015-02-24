<?php
class ManualJournalExport_Xero extends ExportAbstract
{
	protected static function _getData()
	{
		$yesterdayLocal = new UDate('now', 'Australia/Melbourne');
		$yesterdayLocal->modify('-1 day');
		$fromDate = new UDate($yesterdayLocal->format('Y-m-d') . ' 00:00:00', 'Australia/Melbourne');
		$fromDate->setTimeZone('UTC');
		$toDate = new UDate($yesterdayLocal->format('Y-m-d') . ' 23:59:59', 'Australia/Melbourne');
		$toDate->setTimeZone('UTC');
		$dataType = 'created';
		$items = ProductQtyLog::getAllByCriteria($dataType . ' >= :fromDate and ' . $dataType . ' < :toDate and type in (:type1, :type2)',
				array('fromDate' => trim($fromDate),
					'toDate' => trim($toDate),
					'type1' => ProductQtyLog::TYPE_SALES_ORDER,
					'type2' => ProductQtyLog::TYPE_STOCK_ADJ
			)
		);

		$now->setTimeZone('Australia/Melbourne');
		$return = array();
		foreach($items as $item)
		{
			$product = $item->getProduct();
			$narration = '';
			if($item->getEntity() instanceof BaseEntityAbstract) {
				if($item->getEntity() instanceof PurchaseOrderItem)
					$narration = $item->getEntity()->getPurchaseOrder() instanceof PurchaseOrder ?  $item->getEntity()->getPurchaseOrder()->getPurchaseOrderNo() : '';
				else if ($item->getEntity() instanceof PurchaseOrder)
					$narration = $item->getEntity()->getPurchaseOrderNo();
				else if ($item->getEntity() instanceof OrderItem)
					$narration = $item->getEntity()->getOrder() instanceof Order ? (($invoiceNo = trim($item->getEntity()->getOrder()->getInvNo())) === '' ? $item->getEntity()->getOrder()->getOrderNo() : $invoiceNo) : '';
				else if ($item->getEntity() instanceof Order)
					$narration = (($invoiceNo = trim($item->getEntity()->getInvNo())) === '' ? $item->getEntity()->getOrderNo() : $invoiceNo);
			}
			$return[] = array(
				'Narration' => $narration
				,'Date'=> trim($item->getCreated()->setTimeZone('Australia/Melbourne'))
				,'Description'=> $product->getSku()
				,'AccountCode'=> $product->getAssetAccNo()
				,'TaxRate'=> 'BAS Excluded'
				,'Amount'=> $item->getTotalOnHandValueVar()
				,'TrackingName1'=> $item->getType()
				,'TrackingOption1'=> ''
				,'TrackingName2'=> ''
				,'TrackingOption2'=> ''
			);
			$return[] = array(
				'Narration' => $narration
				,'Date'=> trim($item->getCreated()->setTimeZone('Australia/Melbourne'))
				,'Description'=> $product->getSku()
				,'AccountCode'=> $product->getCostAccNo()
				,'TaxRate'=> 'BAS Excluded'
				,'Amount'=> 0 - $item->getTotalOnHandValueVar()
				,'TrackingName1'=> $item->getType()
				,'TrackingOption1'=> ''
				,'TrackingName2'=> ''
				,'TrackingOption2'=> ''
			);
		}
		return $return;
	}
	protected static function _getMailTitle()
	{
		return 'Manual Journal Export for Xero from last day';
	}
	protected static function _getMailBody()
	{
		return 'Please find the attached export from BudgetPC internal system for all the Manual Journals from last day to import to xero.';
	}
	protected static function _getAttachedFileName()
	{
		$now = new UDate();
		$now->setTimeZone('Australia/Melbourne');
		return 'Manual_Journal_' . $now->format('Y_m_d_H_i_s') . '.csv';
	}
}