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
		$js .= 'pageJs.setEditMode(true, true).setOrder('. json_encode($order->getJson()) . ', ' . json_encode($orderItems) . ');';
		$js .= 'pageJs.load("detailswrapper");';
		return $js;
	}
	/**
	 * 
	 * @param unknown $sender
	 * @param unknown $params
	 */
	public function getProducts($sender, $params)
	{
		$results = $errors = array();
		try
		{
			if(!isset($params->CallbackParameter->orderId) || !($order = FactoryAbastract::service('Order')->get(trim($params->CallbackParameter->orderId))) instanceof Order)
				throw new Exception('System Error: invalid order!');
			foreach($order->getOrderItems() as $orderItem)
			{
				$results['items'] = $orderItem->getJson();
			}
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$params->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
}
?>
