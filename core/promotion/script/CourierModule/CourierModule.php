<?php
require_once dirname(__FILE__) . '/../bootstrap.php';
class CourierModule
{
	public static function run()
	{
		Core::setUser(FactoryAbastract::service('UserAccount')->get(UserAccount::ID_SYSTEM_ACCOUNT));
		$shippments = FactoryAbastract::service('Shippment')->findAll();
		foreach($shippments as $shippment)
		{
			$addr = $shippment->getOrder()->getShippingAddr();
			$newAddr = Address::create($addr->getStreet(), $addr->getCity(), $addr->getRegion(), $addr->getCountry(), $addr->getPostCode(), $addr->getContactName(), $addr->getContactNo());
			$shippment->setAddress($newAddr);
			FactoryAbastract::service('Shippment')->Save($shippment);
		}
	}
}

CourierModule::run();