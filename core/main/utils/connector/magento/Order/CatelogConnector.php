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
}