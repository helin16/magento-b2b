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
			'' => array('url' => '/', 'name' => 'Home')
			,'order' => array('url' => '/order.html', 'name' => 'Orders')
		);
		$html = "<ul class='mainMenu'>";
			foreach($array as $key => $item)
			{
				$activeClass = ($pageItem === $key ? 'active' : '');
				$html .= "<li class='mainMenuItem'><a href='" . $item['url'] . "' class='" . $activeClass . "'>" . $item['name'] . "</a>";
			}
		$html .= "</ul>";
		return $html;
	}
}
?>