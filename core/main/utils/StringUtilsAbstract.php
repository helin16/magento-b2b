<?php
abstract class StringUtilsAbstract
{
	/**
	 * getting the JSON string
	 *
	 * @param array $data   The result data
	 * @param array $errors The errors
	 *
	 * @return string The json string
	 */
	public static function getJson($data = array(), $errors = array())
	{
		return json_encode(array('resultData' => $data, 'errors' => $errors, 'succ' => (count($errors) === 0 ? true : false)));
	}
	/**
	 * convert the first char into lower case
	 *
	 * @param Role $role The role
	 */
	public static function lcFirst($string)
	{
	    return strtolower(substr($string, 0, 1)) . substr($string, 1);
	}
	/**
	 * Getting a random key
	 * 
	 * @param string $salt The salt of making one string
	 * 
	 * @return strng
	 */
	public static function getRandKey($salt = '', $preFix = '')
	{
		return $preFix . trim(md5($salt . Core::getUser() . trim(new UDate())));
	}
	/**
	 * getting the value from currency string
	 * 
	 * @param string $currencyValue The currency string
	 * 
	 * @return double
	 */
	public static function getValueFromCurrency($currencyValue)
	{
		return str_replace('$', '', str_replace(',', '', str_replace(' ', '', trim($currencyValue))));
	}
	/**
	 * format the value into currency
	 * 
	 * @param string $currencyValue The currency string
	 * 
	 * @return double
	 */
	public static function getCurrency($currencyValue, $prefix = '$', $decimal = '.', $thousand = ',')
	{
		return $prefix . number_format($currencyValue, 2, $decimal, $thousand);
	}
}