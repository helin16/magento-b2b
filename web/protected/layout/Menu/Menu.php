<?php
/**
 * Menu template
 *
 * @package    Web
 * @subpackage Layout
 * @author     lhe
 */
class Menu extends TTemplateControl
{
    /**
     * (non-PHPdoc)
     * @see TControl::onLoad()
     */
	public function onLoad($param)
	{
	}
	public function getMenuItems()
	{
		$pageItem = trim($this->getPage()->menuItem);
		$array = array(
			'' => array('url' => '/', 'name' => 'Home', 'icon' => '<span class="glyphicon glyphicon-home"></span>')
			,'Orders' => array(
				'icon' => '<span class="glyphicon glyphicon-search"></span>',
				'order' => array('url' => '/order.html', 'name' => 'Orders', 'icon' => '<span class="glyphicon glyphicon-search"></span>')
			)
		);
		/*if(AccessControl::canAccessPriceMatchPage(Core::getRole()) )
			$array['priceMatch'] = array('url' => '/pricematch.html', 'name' => 'Price Match', 'icon' => '<span class="glyphicon glyphicon-usd"></span>' );*/

		if(AccessControl::canAccessCreateOrderPage(Core::getRole()) )
			$array['Orders']['neworder'] = array('url' => '/order/new.html', 'name' => 'New Order', 'icon' => '<span class="glyphicon glyphicon-plus"></span>');
		if(AccessControl::canAccessOrderItemsPage(Core::getRole()) )
			$array['Orders']['orderitems'] = array('url' => '/orderitems.html', 'name' => 'OrderItems');
		if(AccessControl::canAccessOrderItemsPage(Core::getRole()) )
			$array['Orders']['priceMatch'] = array('url' => '/pricematch.html', 'name' => 'Price Match', 'icon' => '<span class="glyphicon glyphicon-usd"></span>');
		if(AccessControl::canAccessProductsPage(Core::getRole()) )
			$array['Products'] = array(
				'icon' => '<span class="glyphicon glyphicon-th-list"></span>',
				'products' => array('url' => '/products.html', 'name' => 'Products', 'icon' => '<span class="glyphicon glyphicon-th-list"></span>'),
				'serialNumbers' => array('url' => '/serialnumbers.html', 'name' => 'Serial Numbers', 'icon' => '<span class="glyphicon glyphicon-search"></span>'),
				'manufacturers' => array('url' => '/manufacturers.html', 'name' => 'Manufactures'),
				'suppliers' => array('url' => '/suppliers.html', 'name' => 'Suppliers'),
				'productcodetypes' => array('url' => '/productcodetypes.html', 'name' => 'Product Code Types', 'icon' => '<span class="glyphicon glyphicon-barcode"></span>'),
				'productcategories' => array('url' => '/productcategories.html', 'name' => 'Product Categories'),
				'stockadjustment' => array('url' => '/stockadjustment.html', 'name' => 'Stock Adjustment', 'icon'=> '<span class=""></span>'),
				'productquantitylog' => array('url' => '/productqtylog.html', 'name' => 'Qty Log', 'icon'=> '<span class=""></span>'),
				'accounting' => array('url' => '/accounting.html', 'name' => 'Accounting Info', 'icon'=> '<span class=""></span>'),
				'importer' => array('url' => '/importer/new.html', 'name' => 'Importer', 'icon'=> '<span class="fa fa-bars"></span>')
			);
		if(AccessControl::canAccessPurcahseOrdersPage(Core::getRole()) )
			$array['Purchase'] = array(
				'icon' => '<span class="glyphicon glyphicon-shopping-cart"></span>',
				'PurchaseOrder' =>array('url' => '/purchase.html', 'name' => 'Purchase Orders', 'icon' => '<span class="glyphicon glyphicon-shopping-cart"></span>'),
				'NEW PO' =>array('url' => '/purchase/new.html', 'name' => 'NEW PO', 'icon' => '<span class="glyphicon glyphicon-plus"></span>'),
				'Receiving' =>array('url' => '/receiving.html', 'name' => 'Receiving PO', 'icon' => '<span class="fa fa-home"></span>'),
				'serialNumbers' => array('url' => '/serialnumbers.html', 'name' => 'Serial Numbers', 'icon' => '<span class="glyphicon glyphicon-search"></span>'),
				'priceMatch' => array('url' => '/pricematch.html', 'name' => 'Price Match', 'icon' => '<span class="glyphicon glyphicon-usd"></span>' )
			);
		if(AccessControl::canAccessOrderItemsPage(Core::getRole()) )
			$array['Customers'] = array('url' => '/customer.html', 'name' => 'Customers', 'icon' => '<span class="glyphicon glyphicon-user"></span>' );
		if(AccessControl::canAccessAccountsPage(Core::getRole()) )
			$array['Accounts'] = array(
					'icon' => '<span class="glyphicon glyphicon-time"></span>',
					'PaymentMethod' =>array('url' => '/paymentmethod.html', 'name' => 'Payment Method', 'icon' => '<span class="glyphicon glyphicon-record"></span>'),
					'accounting' => array('url' => '/accounting.html', 'name' => 'Accounting Info', 'icon'=> '<span class=""></span>'),
					'report' => array('url' => '/report.html', 'name' => 'Report', 'icon'=> '<span class=""></span>'),
					'CreditNote' => array('url' => '/creditnote.html', 'name' => 'Credit Note', 'icon'=> '<span class=""></span>'),
					'RMA' => array('url' => '/rma.html', 'name' => 'RMA', 'icon'=> '<span class=""></span>')
			);
		if(AccessControl::canAccessLogisticsPage(Core::getRole()) )
			$array['Logistics'] = array(
					'icon' => '<span class="fa fa-arrows"></span>',
					'PurchaseOrder' =>array('url' => '/purchase.html', 'name' => 'Purchase Orders', 'icon' => '<span class="glyphicon glyphicon-shopping-cart"></span>'),
					'Receiving' =>array('url' => '/receiving.html', 'name' => 'Receiving Products', 'icon' => '<span class="fa fa-home"></span>'),
					'serialNumbers' => array('url' => '/serialnumbers.html', 'name' => 'Serial Numbers', 'icon' => '<span class="glyphicon glyphicon-search"></span>'),
					'Locations' =>array('url' => '/locations.html', 'name' => 'Locations', 'icon' => '<span class="fa fa-arrows"></span>'),
					'PreferLocationTypes' =>array('url' => '/locationtypes.html', 'name' => 'Prefer Location Types', 'icon' => '<span class="glyphicon glyphicon-tasks"></span>'),
					'stockadjustment' => array('url' => '/stockadjustment.html', 'name' => 'Stock Adjustment', 'icon'=> '<span class=""></span>'),
					'courier' => array('url' => '/courier.html', 'name' => 'Courier', 'icon'=> '<span class=""></span>')
			);
		if(AccessControl::canAccessUsersPage(Core::getRole()) )
		{
			$array['Systems'] = array(
					'icon' => '<span class="glyphicon glyphicon-cog"></span>',
					'users' => array('url' => '/users.html', 'name' => 'Users', 'icon' => '<span class="glyphicon glyphicon-user"></span>'),
					'messages' => array('url' => '/messages.html', 'name' => 'Messages', 'icon' => '<span class="glyphicon glyphicon-envelope"></span>'),
					'logs' => array('url' => '/logs.html', 'name' => 'Logs', 'icon' => '<span class="fa fa-book"></span>'),
					'systemsettings' => array('url' => '/systemsettings.html', 'name' => 'Settings', 'icon' => '<span class="glyphicon glyphicon-cog"></span>')
			);
		}
		$html = "<ul class='nav navbar-nav'>";
			foreach($array as $key => $item)
			{
				$hasNextLevel = !isset($item['name']) && is_array($item) && count($item) > 0;
				$activeClass = ($pageItem === $key || array_key_exists($pageItem, $item) ? 'active' : '');
				$html .= "<li class='" . $activeClass . " visible-xs visible-sm visible-md visible-lg'>";
				$html .= "<a href='" . ($hasNextLevel === true ? '#' : $item['url']) . "' " . ($hasNextLevel === true ? 'class="dropdown-toggle" data-toggle="dropdown"' : '') . ">";
					$html .= (isset($item['icon']) ? $item['icon'] . ' ' : '') . ($hasNextLevel === true ? $key .'<span class="caret"></span>' : $item['name']);
				$html .= "</a>";
					if($hasNextLevel === true)
					{
						$html .= "<ul class='dropdown-menu'>";
						foreach($item as $k => $i)
						{
							if(is_string($i) || !isset($i['url']))
								continue;
							$html .= "<li class='" . ($pageItem === $k ? 'active' : '') . "'><a href='" . $i['url'] . "'>" . (isset($i['icon']) ? $i['icon'] . ' ' : '') .$i['name'] . "</a></li>";
						}
						$html .= "</ul>";
					}
				$html .= "</li>";
			}
		$html .= "</ul>";
		return $html;
	}
}
?>
