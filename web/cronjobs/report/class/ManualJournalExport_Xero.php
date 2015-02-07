<?php
class ManualJournalExport_Xero extends ExportAbstract
{
	protected static function _getData()
	{
		$now = new UDate();
		$now->modify('-1 day');
		$dataType = 'created';
		$items = ProductQtyLog::getAllByCriteria($dataType . ' > :fromDate and ' . $dataType . ' < :toDate and type in (:type1, :type2)', 
				array('fromDate' => $now->format('Y-m-d') . ' 00:00:00', 
					'toDate' => $now->format('Y-m-d') . '23:59:59',
					'type1' => ProductQtyLog::TYPE_SALES_ORDER,
					'type2' => ProductQtyLog::TYPE_STOCK_ADJ
			)
		);
		
		$now = new UDate();
		$now->setTimeZone('Australia/Melbourne');
		$return = array();
		foreach($items as $item)
		{
			$product = $item->getProduct();
			$return[] = array(
				'Narration' => $item->getId()
				,'Date'=> $item->getCreated()->setTimeZone('Australia/Melbourne')
				,'Description'=> $product->getSku()
				,'AccountCode'=> $product->getAssetAccNo()
				,'TaxRate'=> 'BAS Excluded'
				,'Amount'=> 0 - $item->getTotalOnHandValueVar() 
				,'TrackingName1'=> $item->getType()
				,'TrackingOption1'=> ''
				,'TrackingName2'=> ''
				,'TrackingOption2'=> ''
			);
			$return[] = array(
				'Narration' => $item->getId()
				,'Date'=> $item->getCreated()->setTimeZone('Australia/Melbourne')
				,'Description'=> $product->getSku()
				,'AccountCode'=> $product->getCostAccNo()
				,'TaxRate'=> 'BAS Excluded'
				,'Amount'=> $item->getTotalOnHandValueVar()
				,'TrackingName1'=> $item->getType()
				,'TrackingOption1'=> ''
				,'TrackingName2'=> ''
				,'TrackingOption2'=> ''
			);;
			$data1 = array_co
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