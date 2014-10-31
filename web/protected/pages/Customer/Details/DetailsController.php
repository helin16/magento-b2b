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
		$js .= ".bindAllEventNObjects();";
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
			
			$customer = !isset($param->CallbackParameter->id) ? new Customer() : Customer::get(trim($param->CallbackParameter->id));
			if(!$customer instanceof Customer)
				throw new Exception('Invalid Product passed in!');
			$name = trim($param->CallbackParameter->name);
			$id = trim($param->CallbackParameter->id);
			$magId = trim($param->CallbackParameter->mageId);
			$active = trim($param->CallbackParameter->active);
			$emai = trim($param->CallbackParameter->email);
			$contactNo = trim($param->CallbackParameter->contactNo);
			$created = trim($param->CallbackParameter->created);
			$updated = trim($param->CallbackParameter->updated);
			$billingSteet = trim($param->CallbackParameter->billingSteet);
			$billingCity = trim($param->CallbackParameter->billingCity);
			$billingState = trim($param->CallbackParameter->billingState);
			$billingCountry = trim($param->CallbackParameter->billingCountry);
			$billingPostcode = trim($param->CallbackParameter->billingPosecode);
			$billingAdressFull = Address::create($billingSteet, $billingCity, $billingState, $billingCountry, $billingPostcode);
			$shippingSteet = trim($param->CallbackParameter->shippingSteet);
			$shippingCity = trim($param->CallbackParameter->shippingCity);
			$shippingState = trim($param->CallbackParameter->shippingState);
			$shippingCountry = trim($param->CallbackParameter->shippingCountry);
			$shippingPosecode = trim($param->CallbackParameter->shippingPosecode);
			$shippingAdressFull = Address::create($shippingSteet, $shippingCity, $shippingState, $shippingCountry, $shippingPosecode);
				
			
			
			$customer->setName($name)
			->setEmail($emai)
			->setMageId($magId)
			->setCreated($created)
			->setUpdated($updated)
			->setActive($active)
			->setBillingAddress($billingAdressFull)
			->setShippingAddress($shippingAdressFull)
			;
		
			if(trim($customer->getId()) === '')
				$customer->setIsFromB2B(false);
			$customer->save();
			
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
