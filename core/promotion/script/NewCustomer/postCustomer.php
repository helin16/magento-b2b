<?php
require_once dirname(__FILE__) . '/../../../main/bootstrap.php';
class postCustomer
{
	public static function run()
	{
		Core::setUser(FactoryAbastract::service('UserAccount')->get(UserAccount::ID_SYSTEM_ACCOUNT));
		$sql = "select in1.orderId, in1.value `custName`, in2.value `email` from orderinfo in1 left join orderinfo in2 on (in1.orderId = in2.orderId) where in1.typeId = 1 and in2.typeId = 2";
		foreach(Dao::getResultsNative($sql, array(), PDO::FETCH_ASSOC) as $row)
		{
			if(trim($row['custName']) === '')
				continue;
			
			$sqlOrder  = "select billingAddrId, shippingAddrId from `order` where id = ?";
			$order = Dao::getSingleResultNative($sqlOrder, array(trim($row['orderId'])), PDO::FETCH_ASSOC);
			$customer = Customer::create(trim($row['custName']), '',  trim($row['email']), 
				FactoryAbastract::dao('Address')->findById($order['billingAddrId']), 
				true, '', 
				FactoryAbastract::dao('Address')->findById($order['shippingAddrId'])
			);
			
			Dao::updateByCriteria(new DaoQuery('Order'), 'customerId = ?', 'id = ?', array($customer->getId(), trim($row['orderId'])));
		}
	}
}

postCustomer::run();