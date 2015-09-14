<?php
/**
 * This is the Task details page
 *
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class Controller extends DetailsPageAbstract
{
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$menuItem
	 */
	public $menuItem = 'tasks.details';
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$_focusEntityName
	 */
	protected $_focusEntity = 'Task';
	/**
	 * constructor
	 */
	public function __construct()
	{
		parent::__construct();
// 		if(!AccessControl::canAccessPurcahseOrdersPage(Core::getRole()))
// 			die('You do NOT have access to this page');
	}
	/**
	 * Getting The end javascript
	 *
	 * @return string
	 */
	protected function _getEndJs()
	{
		$class = $this->_focusEntity;
		$js = parent::_getEndJs();
		$js .= "pageJs";
		if(trim($this->Request['id']) === 'new') {
			$order = $customer =null;
			if(isset($_REQUEST['customerId']) && !($customer = Customer::get(trim($_REQUEST['customerId']))) instanceof Customer)
				die('Invalid Customer provided!');
			if(isset($_REQUEST['orderId']) && !($order = Order::get(trim($_REQUEST['orderId']))) instanceof Order)
				die('Invalid Order provided!');
			$preSetData = array('order' => ($order instanceof Order ? $order->getJson() : array()),
				'customer' => ($customer instanceof Customer ? $customer->getJson() : ($order instanceof Order ? $order->getCustomer()->getJson() : array()))
			);
			$js .= ".setPreSetData(" . json_encode($preSetData) . ")";
		}

		$statusArray = array_map(create_function('$a', 'return $a->getJson();'), TaskStatus::getAll());
		$js .= ".setTaskStatuses(" . json_encode($statusArray) . ")";
		$js .= ".load()";
		$js .= ";";
		return $js;
	}
	/**
	 * (non-PHPdoc)
	 * @see DetailsPageAbstract::saveItem()
	 */
	public function saveItem($sender, $param)
	{
		$results = $errors = array();
		try
		{
			Dao::beginTransaction();
			$task = null;
			if(isset($param->CallbackParameter->id) && !($task = Task::get(trim($param->CallbackParameter->id))) instanceof Task)
				throw new Exception('Invalid Task passed in!');
			if(!isset($param->CallbackParameter->instructions) || ($instructions = trim($param->CallbackParameter->instructions)) === '')
				throw new Exception('Instructions are required!');
			if(!isset($param->CallbackParameter->customerId) || !($customer = Customer::get(trim($param->CallbackParameter->customerId))) instanceof Customer)
				throw new Exception('Invalid Customer Passed in!');
			$tech = isset($param->CallbackParameter->techId) ? UserAccount::get(trim($param->CallbackParameter->techId)) : null;
			$order = isset($param->CallbackParameter->orderId) ? Order::get(trim($param->CallbackParameter->orderId)) : null;
			$dueDate = new UDate(trim($param->CallbackParameter->dueDate));
			$status = isset($param->CallbackParameter->statusId) ? TaskStatus::get(trim($param->CallbackParameter->statusId)) : null;

			if(!$task instanceof Task) {
				$task = Task::create($customer, $dueDate, $instructions, $tech, $order);
			} else {
				$task->setCustomer($customer)
					->setDueDate($dueDate)
					->setInstructions($instructions)
					->setTechnician($tech)
					->setFromEntityId($order instanceof Order ? $order->getId() : '')
					->setFromEntityName($order instanceof Order ? get_class($order) : '')
					->setStatus($status)
					->save();
			}
// 			$results['url'] = '/task/' . $task->getId() . '.html?' . $_SERVER['QUERY_STRING'];
			$results['item'] = $task->getJson();

			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
}
?>
