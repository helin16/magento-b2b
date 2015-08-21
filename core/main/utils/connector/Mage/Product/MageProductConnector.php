<?php
class MageProductConnector extends MageConnectorAbstract
{
	/**
	 * Getting information for the product
	 *
	 * @param string $sku The product sku
	 *
	 * @return array
	 */
	public static function getProductInfo($sku, $attributes = array())
	{
		$class = get_called_class();
		$attributes = ($attributes === array() ? $class::_getInfoAttributes() : $attributes);
		return $class::_connect()->catalogProductInfo($class::$_sessionId, $sku, null, $attributes);
	}
	/**
	 * getting the product attributes
	 *
	 * @return stdclass
	 */
	public static function _getInfoAttributes()
	{
		$attributeName = array('name', 'product_id', 'short_description', 'description', 'manufacturer', 'man_code', 'news_from_date', 'news_to_date', 'price', 'supplier', 'weight', 'status', 'special_price', 'special_from_date', 'special_to_date');
		$attributes = new stdclass();
		$attributes->additional_attributes = $attributeName;
		return $attributes;
	}
}