<?php
require_once dirname(__FILE__) . '/../New/POController.php';
/**
 * This is the OrderController
 *
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class POCreditController extends POController
{
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$menuItem
	 */
	public $menuItem = 'purchase.newCredit';
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::onLoad()
	 */
	protected function _getJSPrefix()
	{
		return "";
	}
	/**
	 * Getting The end javascript
	 *
	 * @return string
	 */
	protected function _getEndJs()
	{
		$js = parent::_getEndJs();
		if(isset($_REQUEST['poid']) && ($po = PurchaseOrder::get(trim($_REQUEST['poid']))) instanceof PurchaseOrder)
			$js .= "pageJs.loadPO( " . json_encode($po->getJson(array(), false, true)) . " );";
		$customer = (isset($_REQUEST['customerid']) && ($customer = Customer::get(trim($_REQUEST['customerid']))) instanceof Customer) ? $customer->getJson() : null;
		return $js;
	}
	/**
	 * saveOrder
	 *
	 * @param unknown $sender
	 * @param unknown $param
	 *
	 * @throws Exception
	 *
	 */
	public function saveOrder($sender, $param)
	{
		$results = $errors = array();
		$daoStart = false;
		try
		{
			Dao::beginTransaction();
			$daoStart = true;
			$supplier = Supplier::get(trim($param->CallbackParameter->supplier->id));
			if(!$supplier instanceof Supplier)
				throw new Exception('Invalid Supplier passed in!');
	
			$supplierContactName = trim($param->CallbackParameter->supplier->contactName);
			$supplierContactNo = trim($param->CallbackParameter->supplier->contactNo);
			$supplierEmail = trim($param->CallbackParameter->supplier->email);
			if(!empty($supplierContactName) && $supplierContactName !== $supplier->getContactName())
				$supplier->setContactName($supplierContactName);
			if(!empty($supplierContactNo) && $supplierContactNo !== $supplier->getContactNo())
				$supplier->setContactNo($supplierContactNo);
			if(!empty($supplierEmail) && $supplierEmail !== $supplier->getEmail())
				$supplier->setEmail($supplierEmail);
			$supplier->save();
	
			$purchaseOrder = PurchaseOrder::create(
					$supplier,
					trim($param->CallbackParameter->supplierRefNum),
					$supplierContactName,
					$supplierContactNo,
					StringUtilsAbstract::getValueFromCurrency(trim($param->CallbackParameter->shippingCost)),
					StringUtilsAbstract::getValueFromCurrency(trim($param->CallbackParameter->handlingCost))
				)
				->setTotalAmount(StringUtilsAbstract::getValueFromCurrency(trim($param->CallbackParameter->totalPaymentDue)))
				->setEta(trim($param->CallbackParameter->eta))
				->setStatus(PurchaseOrder::STATUS_NEW)
				->save()
				->addComment(trim($param->CallbackParameter->comments), Comments::TYPE_PURCHASING);
			foreach ($param->CallbackParameter->items as $item) {
				if(!($product = Product::get(trim($item->productId))) instanceof Product)
					throw new Exception('Invalid Product passed in!');
				$purchaseOrder->addItem($product, StringUtilsAbstract::getValueFromCurrency(trim($item->unitPrice)), intval(trim($item->qtyOrdered)));
			};
			if($param->CallbackParameter->submitToSupplier === true) {
				$purchaseOrder->setStatus( PurchaseOrder::STATUS_ORDERED );
			}
			// For credit PO
			if(isset($param->CallbackParameter->type) && trim($param->CallbackParameter->type) === 'CREDIT')
			{
				$purchaseOrder->setIsCredit(true);
				if(isset($param->CallbackParameter->po) && ($fromPO = PurchaseOrder::get(trim($param->CallbackParameter->po->id))) instanceof PurchaseOrder)
					$purchaseOrder->setFromPO($fromPO);
			}
			$purchaseOrder->save();
			$daoStart = false;
			Dao::commitTransaction();
			$results['item'] = $purchaseOrder->getJson();
			if(isset($param->CallbackParameter->confirmEmail) && (trim($confirmEmail = trim($param->CallbackParameter->confirmEmail)) !== '')) {
				$pdfFile = EntityToPDF::getPDF($purchaseOrder);
				$asset = Asset::registerAsset($purchaseOrder->getPurchaseOrderNo() . '.pdf', file_get_contents($pdfFile), Asset::TYPE_TMP);
				EmailSender::addEmail('purchasing@budgetpc.com.au', $confirmEmail, 'BudgetPC Purchase Order:' . $purchaseOrder->getPurchaseOrderNo() , 'Please Find the attached PurchaseOrder(' . $purchaseOrder->getPurchaseOrderNo() . ') from BudgetPC.', array($asset));
				EmailSender::addEmail('purchasing@budgetpc.com.au', 'purchasing@budgetpc.com.au', 'BudgetPC Purchase Order:' . $purchaseOrder->getPurchaseOrderNo() , 'Please Find the attached PurchaseOrder(' . $purchaseOrder->getPurchaseOrderNo() . ') from BudgetPC.', array($asset));
				$purchaseOrder->addComment('An email sent to "' . $confirmEmail . '" with the attachment: ' . $asset->getAssetId(), Comments::TYPE_SYSTEM);
			}
		}
		catch(Exception $ex)
		{
			if($daoStart === true)
				Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
}
?>
