<?php
/**
 * This is the OrderController
 * 
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class POController extends BPCPageAbstract
{
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$menuItem
	 */
	public $menuItem = 'order.new';
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::onLoad()
	 */
	public function onLoad($param)
	{
		parent::onLoad($param);
	}
	/**
	 * Getting The end javascript
	 *
	 * @return string
	 */
	protected function _getEndJs()
	{
		$js = parent::_getEndJs();
		
		$paymentMethods =  array_map(create_function('$a', 'return $a->getJson();'), PaymentMethod::getAll());
		$shippingMethods =  array_map(create_function('$a', 'return $a->getJson();'), Courier::getAll());
		$statusOptions =  PurchaseOrder::getStatusOptions();
		$supplier = (isset($_REQUEST['supplierid']) && ($supplier = Supplier::get(trim($_REQUEST['supplierid']))) instanceof Supplier) ? $supplier->getJson() : null;
		$js .= "pageJs";
			$js .= ".setHTMLIDs('detailswrapper')";
			$js .= ".setCallbackId('searchSupplier', '" . $this->searchSupplierBtn->getUniqueID() . "')";
			$js .= ".setCallbackId('searchProduct', '" . $this->searchProductBtn->getUniqueID() . "')";
			$js .= ".setCallbackId('saveOrder', '" . $this->saveOrderBtn->getUniqueID() . "')";
			$js .= ".setPaymentMethods(" . json_encode($paymentMethods) . ")";
			$js .= ".setShippingMethods(" . json_encode($shippingMethods) . ")";
			$js .= ".init(" . json_encode($supplier) . ");";
		return $js;
	}
	/**
	 * Searching Customer
	 *
	 * @param unknown $sender
	 * @param unknown $param
	 * 
	 * @throws Exception
	 *
	 */
	public function searchSupplier($sender, $param)
	{
		$results = $errors = array();
		try
		{
			$items = array();
			$searchTxt = isset($param->CallbackParameter->searchTxt) ? trim($param->CallbackParameter->searchTxt) : '';
			foreach(Supplier::getAllByCriteria('name like :searchTxt or contactName like :searchTxt', array('searchTxt' => $searchTxt . '%')) as $supplier)
			{
				$items[] = $supplier->getJson();
			}
			$results['items'] = $items;
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
	/**
	 * Searching searchProduct
	 *
	 * @param unknown $sender
	 * @param unknown $param
	 * 
	 * @throws Exception
	 *
	 */
	public function searchProduct($sender, $param)
	{
		$results = $errors = array();
		try
		{
			$items = array();
			$searchTxt = isset($param->CallbackParameter->searchTxt) ? trim($param->CallbackParameter->searchTxt) : '';
			$where = 'pro_pro_code.code = :searchExact or pro.name like :searchTxt OR sku like :searchTxt';
			$params = array('searchExact' => $searchTxt , 'searchTxt' => '%' . $searchTxt . '%');
			
			$searchTxtArray = StringUtilsAbstract::getAllPossibleCombo(StringUtilsAbstract::tokenize($searchTxt));
			if(count($searchTxtArray) > 1)
			{
				foreach($searchTxtArray as $index => $comboArray)
				{
					$key = 'combo' . $index;
					$where .= ' OR pro.name like :' . $key;
					$params[$key] = '%' . implode('%', $comboArray) . '%';
				}
			}
			
			$supplierID = isset($param->CallbackParameter->supplierID) ? trim($param->CallbackParameter->supplierID) : '';
			Product::getQuery()->eagerLoad('Product.codes', 'left join');
			$products = Product::getAllByCriteria($where, $params, true, 1, DaoQuery::DEFAUTL_PAGE_SIZE, array('pro.sku' => 'asc'));
			foreach($products as $product)
			{
				$array = $product->getJson();
				
				$array['minProductPrice'] = 0;
				$array['lastSupplierPrice'] = 0;
				$array['minSupplierPrice'] = 0;
				
				$minProductPriceProduct = PurchaseOrderItem::getAllByCriteria('productId = ?', array($product->getId()), true, 1, 1, array('unitPrice'=> 'asc'));
				$minProductPrice = sizeof($minProductPriceProduct) ? $minProductPriceProduct[0]->getUnitPrice() : 0;
				$minProductPriceId = sizeof($minProductPriceProduct) ? $minProductPriceProduct[0]->getPurchaseOrder()->getId() : '';
				
				PurchaseOrderItem::getQuery()->eagerLoad('PurchaseOrderItem.purchaseOrder');
				$lastSupplierPriceProduct = PurchaseOrderItem::getAllByCriteria('po_item.productId = ? and po_item_po.supplierId = ?', array($product->getId(), $supplierID), true, 1, 1, array('po_item.id'=> 'desc'));
				$lastSupplierPrice = sizeof($lastSupplierPriceProduct) ? $lastSupplierPriceProduct[0]->getUnitPrice() : 0;
				$lastSupplierPriceId = sizeof($lastSupplierPriceProduct) ? $lastSupplierPriceProduct[0]->getPurchaseOrder()->getId() : '';
				
				PurchaseOrderItem::getQuery()->eagerLoad('PurchaseOrderItem.purchaseOrder');
				$minSupplierPriceProduct = PurchaseOrderItem::getAllByCriteria('po_item.productId = ? and po_item_po.supplierId = ?', array($product->getId(), $supplierID), true, 1, 1, array('po_item.unitPrice'=> 'asc'));
				$minSupplierPrice = sizeof($minSupplierPriceProduct) ? $minSupplierPriceProduct[0]->getUnitPrice() : 0;
				$minSupplierPriceId = sizeof($minSupplierPriceProduct) ? $minSupplierPriceProduct[0]->getPurchaseOrder()->getId() : '';
				
				$array['minProductPrice'] = $minProductPrice;
				$array['minProductPriceId'] = $minProductPriceId;
				
				$array['lastSupplierPrice'] = $lastSupplierPrice;
				$array['lastSupplierPriceId'] = $lastSupplierPriceId;
				
				$array['minSupplierPrice'] = $minSupplierPrice;
				$array['minSupplierPriceId'] = $minSupplierPriceId;
				
				$items[] = $array;
			}
			$results['items'] = $items;
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
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
				->setStatus($param->CallbackParameter->submitToSupplier === true ? PurchaseOrder::STATUS_ORDERED : PurchaseOrder::STATUS_NEW)
				->save()
				->addComment(trim($param->CallbackParameter->comments), Comments::TYPE_PURCHASING);
			foreach ($param->CallbackParameter->items as $item) {
				if(!($product = Product::get(trim($item->productId))) instanceof Product)
					throw new Exception('Invalid Product passed in!');
				$purchaseOrder->addItem($product, StringUtilsAbstract::getValueFromCurrency(trim($item->unitPrice)), intval(trim($item->qtyOrdered)));
			};
			
			Dao::commitTransaction();
			$daoStart = false;
			$results['item'] = $purchaseOrder->getJson();
			if(isset($param->CallbackParameter->confirmEmail) && (trim($confirmEmail = trim($param->CallbackParameter->confirmEmail)) !== '')) {
				$html2pdf = new HTML2PDF('P', 'A4', 'en');
				//      $html2pdf->setModeDebug();
				$html2pdf->setDefaultFont('Arial');
				$content = ComScriptCURL::readUrl('/print/purchase/' . $purchaseOrder->getId() . '.html');
				$html2pdf->writeHTML('<page>' . $content . '</page>', false);
				
				$fileName = $purchaseOrder->getPurchaseOrderNo() . '.pdf';
				$asset = Asset::registerAsset($fileName, $html2pdf->Output($fileName, 'S'));
				EmailSender::addEmail('', $confirmEmail, 'BudgetPC Purchase Request', 'Please Find the attached PurchaseOrder from BudgetPC.', array($asset));
				$purchaseOrder->addComment('An email sent to "' . $confirmEmail . '" with the attachment: ' . $fileName, Comments::TYPE_PURCHASING);
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