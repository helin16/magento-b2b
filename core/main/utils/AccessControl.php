<?php

Abstract class AccessControl
{
	public static function canEditOrder(Order $order, Role $role)
	{
		switch($role->getId())
		{
			case Role::ID_STORE_MANAGER:
			case Role::ID_SYSTEM_ADMIN:
				{
					return true;
				}
			case Role::ID_ACCOUNTING:
				{
					return !in_array($order->getStatus()->getId(), array(OrderStatus::ID_CANCELLED)) && !$order->getPassPaymentCheck();
				}
			case Role::ID_PURCHASING:
				{
					return in_array($order->getStatus()->getId(), array(OrderStatus::ID_NEW, OrderStatus::ID_INSUFFICIENT_STOCK));
				}
			case Role::ID_WAREHOUSE:
				{
					return in_array($order->getStatus()->getId(), array(OrderStatus::ID_ETA, OrderStatus::ID_STOCK_CHECKED_BY_PURCHASING)) && $order->getPassPaymentCheck();
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