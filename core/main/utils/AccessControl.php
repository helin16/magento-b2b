<?php

Abstract class AccessControl
{
	private static $_cache;
	
	public static function canAccessOrderStatusIds(Role $role)
	{
		if(isset(self::$_cache['accessOrderStatusIds']))
			self::$_cache['accessOrderStatusIds'] = array();
		if(!isset(self::$_cache['accessOrderStatusIds'][$role->getId()]))
		{
			switch($role->getId())
			{
				case Role::ID_STORE_MANAGER:
				case Role::ID_SYSTEM_ADMIN:
					{
						self::$_cache['accessOrderStatusIds'][$role->getId()] = array_map(create_function('$a', 'return intval($a->getId());'), FactoryAbastract::service('OrderStatus')->findAll());
						break;
					}
				case Role::ID_ACCOUNTING:
					{
						self::$_cache['accessOrderStatusIds'][$role->getId()] = array_map(create_function('$a', 'return intval($a->getId());'), FactoryAbastract::service('OrderStatus')->findByCriteria('id != ? ', array(OrderStatus::ID_CANCELLED)));
						break;
					}
				case Role::ID_PURCHASING:
					{
						self::$_cache['accessOrderStatusIds'][$role->getId()] = array(OrderStatus::ID_NEW, OrderStatus::ID_INSUFFICIENT_STOCK);
						break;
					}
				case Role::ID_WAREHOUSE:
					{
						self::$_cache['accessOrderStatusIds'][$role->getId()] = array(OrderStatus::ID_ETA, OrderStatus::ID_STOCK_CHECKED_BY_PURCHASING, OrderStatus::ID_PICKED);
						break;
					}
			}
		}
		return self::$_cache['accessOrderStatusIds'][$role->getId()];
	}
	
	public static function canEditOrder(Order $order, Role $role)
	{
		$canAcessOrderByStatus = in_array($order->getStatus()->getId(), self::canAccessOrderStatusIds($role));
		switch($role->getId())
		{
			case Role::ID_STORE_MANAGER:
			case Role::ID_SYSTEM_ADMIN:
			case Role::ID_PURCHASING:
				{
					return $canAcessOrderByStatus;
				}
			case Role::ID_ACCOUNTING:
				{
					return $canAcessOrderByStatus && !$order->getPassPaymentCheck();
				}
			case Role::ID_WAREHOUSE:
				{
					return $canAcessOrderByStatus && $order->getPassPaymentCheck();
				}
		}
	}
	public static function canAccessUsersPage(Role $role)
	{
		switch($role->getId())
		{
			case Role::ID_STORE_MANAGER:
			case Role::ID_SYSTEM_ADMIN:
				{
					return true;
				}
		}
		return false;
	}
}