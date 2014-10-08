<?php
class CatelogConnector extends B2BConnector
{
	/**
	 * Getting information for the product
	 *
	 * @param string $sku The product sku
	 *
	 * @return array
	 */
	public function getProductInfo($sku)
	{
		return $this->_connect()->catalogProductInfo($this->_session, $sku);
	}
	public function getCategoryTree($mageCategoryId = '')
	{
		if(($mageCategoryId = trim($mageCategoryId)) !== '')
			return $this->_connect()->catalogCategoryTree($this->_session, $mageCategoryId);
		return $this->_connect()->catalogCategoryTree($this->_session);
	}
	public function getCategoryLevel($mageCategoryId = '')
	{
		if(($mageCategoryId = trim($mageCategoryId)) !== '')
			return $this->_connect()->catalogCategoryLevel($this->_session, null, null, $mageCategoryId);
		return $this->_connect()->catalogCategoryLevel($this->_session);
	}
	public function catalogCategoryInfo($mageCategoryId)
	{
		return $this->_connect()->catalogCategoryInfo($this->_session, $mageCategoryId);
	}
}