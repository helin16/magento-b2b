<?php
require_once dirname(__FILE__) . '/../../../main/bootstrap.php';
class postNewCustomer
{
	public static function run()
	{
		Core::setUser(FactoryAbastract::service('UserAccount')->get(UserAccount::ID_SYSTEM_ACCOUNT));
		
		$sql = "select pro.id, info.value from product pro inner join productinfo info on (info.productId = pro.id)";
		foreach(Dao::getResultsNative($sql, array(), PDO::FETCH_ASSOC) as $row)
		{
			if(trim($row['value']) === '')
				continue;
			echo 'update mageId to ' . trim($row['value']) . ' for Product(ID=' . trim($row['id']) . ')<br />';
			Dao::updateByCriteria(new DaoQuery('Product'), 'mageId = ?', 'id = ?', array(trim($row['value']), trim($row['id'])));
		}
		
// 		select pro.id, pro.mageId, info.value from product pro inner join productinfo info on (info.productId = pro.id) where pro.mageId != info.value
	}
}

postNewCustomer::run();