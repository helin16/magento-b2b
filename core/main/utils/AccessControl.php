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
				case Role::ID_SALES:
					{
						self::$_cache['accessOrderStatusIds'][$role->getId()] = array_map(create_function('$a', 'return intval($a->getId());'), OrderStatus::getAllByCriteria('id not in(?,?) ', array(OrderStatus::ID_CANCELLED, OrderStatus::ID_SHIPPED)));
						break;
					}
				case Role::ID_ACCOUNTING:
					{
						self::$_cache['accessOrderStatusIds'][$role->getId()] = array_map(create_function('$a', 'return intval($a->getId());'), OrderStatus::getAllByCriteria('id not in(?,?) ', array(OrderStatus::ID_CANCELLED, OrderStatus::ID_SHIPPED)));
						break;
					}
				case Role::ID_PURCHASING:
					{
						self::$_cache['accessOrderStatusIds'][$role->getId()] = array(OrderStatus::ID_NEW, OrderStatus::ID_INSUFFICIENT_STOCK);
						break;
					}
				case Role::ID_WAREHOUSE:
					{
						self::$_cache['accessOrderStatusIds'][$role->getId()] = array(OrderStatus::ID_ETA, OrderStatus::ID_STOCK_CHECKED_BY_PURCHASING, OrderStatus::ID_PICKED, OrderStatus::ID_INSUFFICIENT_STOCK);
						break;
					}
				case Role::ID_WORKSHOP:
					{
						self::$_cache['accessOrderStatusIds'][$role->getId()] = array(OrderStatus::ID_STOCK_CHECKED_BY_PURCHASING, OrderStatus::ID_NEW);
						break;
					}
			}
		}
		return self::$_cache['accessOrderStatusIds'][$role->getId()];
	}
	public static function canEditProduct(Role $role)
	{
		switch($role->getId())
		{
			case Role::ID_STORE_MANAGER:
			case Role::ID_SYSTEM_ADMIN:
				{
					return true;
				}
			default:
				{
					return false;
				}
		}
	}
	public static function canEditOrder(Order $order, Role $role)
	{
		$canAcessOrderByStatus = in_array($order->getStatus()->getId(), self::canAccessOrderStatusIds($role));
		switch($role->getId())
		{
			case Role::ID_STORE_MANAGER:
			case Role::ID_SYSTEM_ADMIN:
			case Role::ID_PURCHASING:
			case Role::ID_SALES:
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
	public static function canAccessProductsPage(Role $role)
	{
		switch($role->getId())
		{
			case Role::ID_STORE_MANAGER:
			case Role::ID_SYSTEM_ADMIN:
			case Role::ID_SALES:
			case Role::ID_PURCHASING:
			case Role::ID_WAREHOUSE:
			case Role::ID_WORKSHOP:
			case Role::ID_ACCOUNTING:
				{
					return true;
				}
		}
		return false;
	}
	public static function canAccessPurcahseOrdersPage(Role $role)
	{
		switch($role->getId())
		{
			case Role::ID_STORE_MANAGER:
			case Role::ID_SYSTEM_ADMIN:
			case Role::ID_PURCHASING:
				{
					return true;
				}
		}
		return false;
	}
	public static function canAccessPriceMatchPage(Role $role)
	{
		switch($role->getId())
		{
			case Role::ID_STORE_MANAGER:
			case Role::ID_SYSTEM_ADMIN:
			case Role::ID_PURCHASING:
			case Role::ID_ACCOUNTING:
			case Role::ID_SALES:
				{
					return true;
				}
		}
		return false;
	}
	public static function canAccessOrderItemsPage(Role $role)
	{
		switch($role->getId())
		{
			case Role::ID_STORE_MANAGER:
			case Role::ID_SYSTEM_ADMIN:
			case Role::ID_PURCHASING:
			case Role::ID_SALES:
			case Role::ID_WORKSHOP:
				{
					return true;
				}
		}
		return false;
	}
	public static function canAccessCreateOrderPage(Role $role)
	{
		switch($role->getId())
		{
			case Role::ID_STORE_MANAGER:
			case Role::ID_SYSTEM_ADMIN:
			case Role::ID_PURCHASING:
			case Role::ID_SALES:
				{
					return true;
				}
		}
		return false;
	}
	public static function canAccessLogisticsPage(Role $role)
	{
		switch($role->getId())
		{
			case Role::ID_STORE_MANAGER:
			case Role::ID_SYSTEM_ADMIN:
			case Role::ID_WAREHOUSE:
				{
					return true;
				}
		}
		return false;
	}
	public static function canAccessAccountsPage(Role $role)
	{
		switch($role->getId())
		{
			case Role::ID_STORE_MANAGER:
			case Role::ID_SYSTEM_ADMIN:
			case Role::ID_ACCOUNTING:
				{
					return true;
				}
		}
		return false;
	}
	public static function canAccessStockAdjustPage(Role $role)
	{
		switch($role->getId())
		{
			case Role::ID_SYSTEM_ADMIN:
				{
					return true;
				}
		}
		return false;
	}
	public static function canAccessNewSupplierPage(Role $role)
	{
		switch($role->getId())
		{
			case Role::ID_STORE_MANAGER:
			case Role::ID_SYSTEM_ADMIN:
			case Role::ID_PURCHASING:
				{
					return true;
				}
		}
		return false;
	}
	public static function canAccessWorkShopPage(Role $role)
	{
		switch($role->getId())
		{
			case Role::ID_STORE_MANAGER:
			case Role::ID_SYSTEM_ADMIN:
			case Role::ID_WORKSHOP:
				{
					return true;
				}
		}
		return false;
	}
	public static function canAccessDevelopingPage(Role $role)
	{
		switch($role->getId())
		{
			case Role::ID_SYSTEM_ADMIN:
				{
					return true;
				}
		}
		return false;
	}
}