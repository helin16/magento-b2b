<?php
require_once dirname(__FILE__) . '/../../main/bootstrap.php';
$sql = "select id, billingAddrId, shippingAddrId from `order`";
$result = Dao::getResultsNative($sql, array(), PDO::FETCH_ASSOC);

echo "==== start";
Dao::$debug = true;
foreach($result as $row)
{
	Dao::updateByCriteria(new DaoQuery('Order'), 'billingAddrId = ?, shippingAddrId = ?', 'where id = ?', array());
}
echo "==== end";