<?php
require_once dirname(__FILE__) . '/../../main/bootstrap.php';
Core::setUser(FactoryAbastract::service('UserAccount')->get(UserAccount::ID_SYSTEM_ACCOUNT));
$sql = "select id, billingAddrId, shippingAddrId from `order`";
$result = Dao::getResultsNative($sql, array(), PDO::FETCH_ASSOC);
echo "==== start";
Dao::$debug = true;
foreach($result as $row)
{
	Dao::updateByCriteria(new DaoQuery('Order'), 'billingAddrId = ?, shippingAddrId = ?', 'where id = ?', array($row['shippingAddrId'], $row['billingAddrId'], $row['id']));
}
echo "==== end";