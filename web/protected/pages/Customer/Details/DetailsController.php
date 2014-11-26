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
	public $menuItem = 'customer.details';
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$_focusEntityName
	 */
	protected $_focusEntity = 'Customer';
	/**
	 * constructor
	 */
	public function __construct()
	{
		parent::__construct();
		if(!AccessControl::canAccessProductsPage(Core::getRole()))
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
			$customer = new Customer();
		else if(!($customer = Customer::get($this->Request['id'])) instanceof Customer)
			die('Invalid Customer!');
		
		$js = parent::_getEndJs();
		$js .= "pageJs.setPreData(" . json_encode($customer->getJson()) . ")";
		$js .= ".load()";
		$js .= ".bindAllEventNObjects()";
		$js .= "._bindSaveKey();";
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
// 			var_dump($param->CallbackParameter);
			Dao::beginTransaction();
			$customer = !is_numeric($param->CallbackParameter->id) ? new Customer() : Customer::get(trim($param->CallbackParameter->id));
			if(!$customer instanceof Customer)
				throw new Exception('Invalid Customer passed in!');
			$name = trim($param->CallbackParameter->name);
			$id = !is_numeric($param->CallbackParameter->id) ? '' : trim($param->CallbackParameter->id);
			$active = !is_numeric($param->CallbackParameter->id) ? '' : trim($param->CallbackParameter->active);
			$email = trim($param->CallbackParameter->email);
			$contactNo = trim($param->CallbackParameter->contactNo);
			$billingStreet = trim($param->CallbackParameter->billingStreet);
			$billingCity = trim($param->CallbackParameter->billingCity);
			$billingState = trim($param->CallbackParameter->billingState);
			$billingCountry = trim($param->CallbackParameter->billingCountry);
			$billingPostcode = trim($param->CallbackParameter->billingPosecode);
			$billingAdressFull = Address::create($billingStreet, $billingCity, $billingState, $billingCountry, $billingPostcode);
			$shippingStreet = trim($param->CallbackParameter->shippingStreet);
			$shippingCity = trim($param->CallbackParameter->shippingCity);
			$shippingState = trim($param->CallbackParameter->shippingState);
			$shippingCountry = trim($param->CallbackParameter->shippingCountry);
			$shippingPosecode = trim($param->CallbackParameter->shippingPosecode);
			$shippingAdressFull = Address::create($shippingStreet, $shippingCity, $shippingState, $shippingCountry, $shippingPosecode);
				
			if(is_numeric($param->CallbackParameter->id)) {
				$customer->setName($name)
					->setEmail($email)
					->setContactNo($contactNo)
					->setActive($active)
					->setBillingAddress($billingAdressFull)
					->setShippingAddress($shippingAdressFull)
					->save();
				var_dump($customer);
			} else {
				$customer->create($name, $contactNo, $email, $billingAdressFull, false, '', $shippingAdressFull, $mageId);
			}
			$results['url'] = '/customer/' . $customer->getId() . '.html';
			$results['item'] = $customer->getJson();
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
