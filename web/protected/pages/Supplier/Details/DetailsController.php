<?php
/**
 * This is the Product details page
 *
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class DetailsController extends DetailsPageAbstract
{
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$menuItem
	 */
	public $menuItem = 'supplier.details';
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$_focusEntityName
	 */
	protected $_focusEntity = 'Supplier';
	/**
	 * constructor
	 */
	public function __construct()
	{
		parent::__construct();
		if(!AccessControl::canAccessNewSupplierPage(Core::getRole()))
			die('You do NOT have access to this page');
	}
	/**
	 * Getting The end javascript
	 *
	 * @return string
	 */
	protected function _getEndJs()
	{
		if(!isset($this->Request['id']))
			die('System ERR: no param passed in!');
		if(trim($this->Request['id']) === 'new')
			$supplier = new Supplier();
		else if(!($supplier = Supplier::get($this->Request['id'])) instanceof Supplier)
			die('Invalid Supplier!');

		$js = parent::_getEndJs();
		$js .= "pageJs.setPreData(" . json_encode($supplier->getJson()) . ")";
		$js .= ".load();";
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

			if(!isset($param->CallbackParameter->id))
				throw new Exception('Invalid supplier ID passed in!');

			$supplier = ($id = trim($param->CallbackParameter->id)) === '' ? new Supplier() : Supplier::get($id);
			if(!$supplier instanceof Supplier)
				throw new Exception('Invalid supplier passed in!');

			$contactName = trim($param->CallbackParameter->address->contactName);
			$contactNo = trim($param->CallbackParameter->address->contactNo);
			$street = trim($param->CallbackParameter->address->street);
			$city = trim($param->CallbackParameter->address->city);
			$region = trim($param->CallbackParameter->address->region);
			$postCode = trim($param->CallbackParameter->address->postCode);
			$country = trim($param->CallbackParameter->address->country);
			$address = $supplier->getAddress();
			$supplier->setName(trim($param->CallbackParameter->name))
				->setDescription(trim($param->CallbackParameter->description))
				->setContactNo(trim($param->CallbackParameter->contactNo))
				->setEmail(trim($param->CallbackParameter->email))
				->setAddress(Address::create($street, $city, $region, $country, $postCode, $contactName, $contactNo, $address))
				->save();
			$results['url'] = '/supplier/' . $supplier->getId() . '.html' . (isset($_REQUEST['blanklayout']) ? '?blanklayout=' . $_REQUEST['blanklayout'] : '');
			$results['item'] = $supplier->getJson();
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage() . $ex->getTraceAsString();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
}
?>
