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
			
			if(!isset($params->CallbackParameter->for) || ($for = trim($params->CallbackParameter->order->for)) === '')
				throw new Exception('System Error: invalid for passed in!');
			
			if(!$order->canEditBy(Core::getRole()))
				throw new Exception('You do NOT edit this order as ' . Core::getRole() . '!');
			
			foreach($params->CallbackParameter->items as $obj)
			{
				if(($orderItem = FactoryAbastract::service('OrderItem')->get($obj->orderItem->id)) instanceof OrderItem)
					$orderItem = new OrderItem();
				$orderItem->setQtyOrdered($obj->orderItem->qtyOrdered);
				$orderItem->setUnitPrice($obj->orderItem->unitPrice);
				$orderItem->setTotalPrice($obj->orderItem->totalPrice);
				$orderItem->setProduct(Product::get($obj->orderItem->product->sku));
			}
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
