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
	public $menuItem = 'kits.details';
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$_focusEntityName
	 */
	protected $_focusEntity = 'Kit';
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
			$task = null;
			if(isset($_REQUEST['taskid']) && !($task = Task::get(trim($_REQUEST['taskid']))) instanceof Task)
				die('Invalid Task provided!');
			$preSetData = array('task' => ($task instanceof Task ? $task->getJson() : array()) );
			$js .= ".setPreSetData(" . json_encode($preSetData) . ")";
		}
		$js .= ".setHTMLID('kitsDetailsDiv', 'kits-details-wrapper')";
		$js .= ".setHTMLID('partsTable', 'parts-result-table')";
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
			$kit = !isset($param->CallbackParameter->id) ? new Kit() : Kit::get(trim($param->CallbackParameter->id));
			if(!$kit instanceof Kit)
				throw new Exception('Invalid Kit passed in!');
			if(!isset($param->CallbackParameter->productId) || !($product = Product::get(trim($param->CallbackParameter->productId))) instanceof Product)
				throw new Exception('Invalid Kit Product passed in!');
			if(!isset($param->CallbackParameter->items) || count($items = $param->CallbackParameter->items) === 0)
				throw new Exception('No Kit Components passed in!');
			$task = null;
			if(isset($param->CallbackParameter->taskId) && !($task = Task::get(trim($param->CallbackParameter->taskId))) instanceof Task)
				throw new Exception('Invalid Task passed in!');

			$underCostReason = '';
			if(isset($param->CallbackParameter->underCostReason) && ($underCostReason = trim($param->CallbackParameter->underCostReason)) === '')
				throw new Exception('UnderCostReason is Required!');

			$isNewKit = false;
			if(trim($kit->getId()) === '') {
				$kit = Kit::create($product, $task);
				$isNewKit = true;
			} else {
				$kit->setTask($task)
					->save();
			}
			//add all the components
			foreach($items as $item) {
				if(!($componentProduct = Product::get(trim($item->productId))) instanceof Product)
					continue;
				if(($componentId = trim($item->id)) === '' && intval($item->active) === 1) {
					$kit->addComponent($componentProduct, intval($item->qty));
				} else if(($kitComponent = KitComponent::get($componentId)) instanceof KitComponent) {
					if($kitComponent->getKit()->getId() !== $kit->getId())
						continue;
					if(intval($item->active) === 0) { //deactivation
						$kitComponent->setActive(false)
							->save();
					} else {
						$kitComponent->setQty(intval($item->qty))
							->save();
					}
				}
			}
			if(trim($underCostReason) !== '')
				$kit->addComment('The reason for continuing bulding this kit, when its cost is greater than its unit price: '. $underCostReason, Comments::TYPE_WORKSHOP);
			if($isNewKit === true) {
				$kit->finishedAddingComponents();
			}

			$results['url'] = '/kit/' . $kit->getId() . '.html' . (trim($_SERVER['QUERY_STRING']) === '' ? '' : '?' . $_SERVER['QUERY_STRING']);
			$results['printUrl'] = '/print/kit/' . $kit->getId() . '.html';
			$results['item'] = $kit->getJson();
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage() . '<pre>' . $ex->getTraceAsString() . '</pre>' ;
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
}
?>
