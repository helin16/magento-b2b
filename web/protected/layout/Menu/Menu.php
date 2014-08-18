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
		if(AccessControl::canAccessPriceMatchPage(Core::getRole()) )
			$array['priceMatch'] = array('url' => '/pricematch.html', 'name' => 'Price Match', 'icon' => '<span class="glyphicon glyphicon-usd"></span>' );
		if(AccessControl::canAccessOrderItemsPage(Core::getRole()) )
			$array['Orders']['orderitems'] = array('url' => '/orderitems.html', 'name' => 'OrderItems');
		if(AccessControl::canAccessProductsPage(Core::getRole()) )
			$array['Products'] = array(
				'icon' => '<span class="glyphicon glyphicon-th-list"></span>',
				'products' => array('url' => '/products.html', 'name' => 'Products', 'icon' => '<span class="glyphicon glyphicon-th-list"></span>'),
				'manufacturers' => array('url' => '/manufacturers.html', 'name' => 'Manufactures'),
				'suppliers' => array('url' => '/suppliers.html', 'name' => 'Suppliers'),
				'productcodetypes' => array('url' => '/productcodetypes.html', 'name' => 'Product Code Types', 'icon' => '<span class="glyphicon glyphicon-barcode"></span>'),
				'productcategories' => array('url' => '/productcategories.html', 'name' => 'Product Categories')
			);
		if(AccessControl::canAccessUsersPage(Core::getRole()) )
		{
			$array['Systems'] = array(
					'icon' => '<span class="glyphicon glyphicon-cog"></span>',
					'users' => array('url' => '/users.html', 'name' => 'Users', 'icon' => '<span class="glyphicon glyphicon-user"></span>'),
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