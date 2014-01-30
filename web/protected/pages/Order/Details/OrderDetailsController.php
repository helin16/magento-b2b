<?php
/**
 * This is the OrderDetailsController
 * 
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class OrderDetailsController extends BPCPageAbstract
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
	 * constructor
	 */
	public function __construct()
	{
		parent::__construct();
	}
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::onLoad()
	 */
	public function onLoad($param)
	{
		parent::onLoad($param);
		if(!$this->isPostBack)
		{
		}
	}
	/**
	 * Getting The end javascript
	 *
	 * @return string
	 */
	protected function _getEndJs()
	{
		$order = FactoryAbastract::service('Order')->get($this->Request['orderId']);
		if(!$order instanceof Order)
			die('Invalid Order!');
		$js = parent::_getEndJs();
		
		$orderItems = array();
		foreach($order->getOrderItems() as $orderItem)
			$orderItems[] = $orderItem->getJson();
		$js .= 'pageJs.setCallbackId("updateOrder", "' . $this->updateOrderBtn->getUniqueID() . '");';
		$js .= 'pageJs.setEditMode(true, true).setOrder('. json_encode($order->getJson()) . ', ' . json_encode($orderItems) . ');';
		$js .= 'pageJs.load("detailswrapper");';
		return $js;
	}
	/**
	 * 
	 * @param unknown $sender
	 * @param unknown $params
	 */
	public function updateOrder($sender, $params)
	{
		$results = $errors = array();
		try
		{
			Dao::beginTransaction();
			if(!isset($params->CallbackParameter->order) || !($order = Order::get($params->CallbackParameter->order->orderNo)) instanceof Order)
				throw new Exception('System Error: invalid order passed in!');
			
			if(!isset($params->CallbackParameter->for) || ($for = trim($params->CallbackParameter->for)) === '')
				throw new Exception('System Error: invalid for passed in!');
			
			if(!$order->canEditBy(Core::getRole()))
				throw new Exception('You do NOT edit this order as ' . Core::getRole() . '!');
			
			$hasETA = false;
			$allPicked = true;
			
			$commentType = ($for === 'purchasing' ? Comments::TYPE_PURCHASING : Comments::TYPE_WAREHOUSE);
			foreach($params->CallbackParameter->items as $obj)
			{
				if(($orderItem = FactoryAbastract::service('OrderItem')->get($obj->orderItem->id)) instanceof OrderItem)
					$orderItem = new OrderItem();
				$orderItem->setQtyOrdered($obj->orderItem->qtyOrdered);
				$orderItem->setUnitPrice($obj->orderItem->unitPrice);
				$orderItem->setTotalPrice($obj->orderItem->totalPrice);
				$sku = trim($obj->orderItem->product->sku);
				$orderItem->setProduct(Product::get($sku));
				
				if(isset($obj->orderItem->$for))
				{
					$comments = trim($obj->orderItem->$for->comments);
					$orderItem->addComment($comments, $commentType);
					if(isset($obj->orderItem->$for->eta))
					{
						$eta = trim($obj->orderItem->$for->eta);
						$orderItem->setEta($eta === '' ? null : $eta);
						if($eta!== '' && $eta !== trim(UDate::zeroDate()))
						{
							$order->addComment('Added ETA[' . $eta . '] for product(SKU=' . $sku .'): ' . $comments, $commentType);
							$hasETA = true;
						}
					}
					
					if(isset($obj->orderItem->$for->isPicked))
					{
						$picked = (trim($obj->orderItem->$for->isPicked) === 'Y');
						$orderItem->setIsPicked($picked);
						if($picked === false)
						{
							$order->addComment('Picked product(SKU=' . $sku .'): ' . $comments, $commentType);
							$allPicked = false;
						}
					}
				}
			}
			
			$status = trim($order->getStatus());
			if($for === 'purchasing')
			{
				if($hasETA === true)
					$order->setStatus(OrderStatus::get(OrderStatus::ID_ETA));
				else
					$order->setStatus(OrderStatus::get(OrderStatus::ID_STOCK_CHECKED_BY_PURCHASING));
				$order->addComment('Changed from [ ' . $status . '] to [' . $order->getStatus() . ']', Comments::TYPE_NORMAL);
			}
			if($for === 'warehouse')
			{
				if($allPicked === true)
					$order->setStatus(OrderStatus::get(OrderStatus::ID_PICKED));
				else
					$order->setStatus(OrderStatus::get(OrderStatus::ID_INSUFFICIENT_STOCK));
				$order->addComment('Changed from [ ' . $status . '] to [' . $order->getStatus() . ']', Comments::TYPE_NORMAL);
			}
			
			FactoryAbastract::service('Order')->save($order);
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}
		$params->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
}
?>
