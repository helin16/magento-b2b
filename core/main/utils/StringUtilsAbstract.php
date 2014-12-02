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
	/**
	 * Tokenizing a string
	 * 
	 * @param string $string
	 * @param string $separator The separator of the string
	 * 
	 * @return array
	 */
	public static function tokenize($string, $separator = ' ')
	{
		$tokens = explode($separator, $string);
		if(!is_array($tokens))
			return $string;
		return array_map(create_function('$a', 'return trim($a);'), $tokens);
	}
	/**
	 * Getting all possible combination of an array 
	 * 
	 * @param array $items
	 * @param array $perms
	 * 
	 * @return array
	 */
	public static function getAllPossibleCombo($items, $perms = array())
	{
	    if (empty($items)) 
	    	return array($perms);
	    	
		$array = array();
        for ($i = count($items) - 1; $i >= 0; --$i) 
        {
             $newitems = $items;
             $newperms = $perms;
             list($foo) = array_splice($newitems, $i, 1);
             array_unshift($newperms, $foo);
             $array = array_merge($array, self::getAllPossibleCombo($newitems, $newperms));
         }
         return $array;
	}
	/**
	 * Simple method for detirmining mime type of a file based on file extension
	 * This isn't technically correct, but for our problem domain, this is good enough
	 *
	 * @param string $filename The name of the file
	 *
	 * @return string
	 */
	public static function getMimeType($filename)
	{
		preg_match("|\.([a-z0-9]{2,4})$|i", $filename, $fileSuffix);
		if(isset($fileSuffix[1]))
		{
			switch(strtolower($fileSuffix[1]))
			{
				case "js" :
					return "application/x-javascript";
		
				case "json" :
					return "application/json";
		
				case "jpg" :
				case "jpeg" :
				case "jpe" :
					return "image/jpg";
		
				case "png" :
				case "gif" :
				case "bmp" :
				case "tiff" :
					return "image/".strtolower($fileSuffix[1]);
		
				case "css" :
					return "text/css";
		
				case "xml" :
					return "application/xml";
		
				case "doc" :
				case "docx" :
					return "application/msword";
		
				case "xls" :
				case "xlt" :
				case "xlm" :
				case "xld" :
				case "xla" :
				case "xlc" :
				case "xlw" :
				case "xll" :
					return "application/vnd.ms-excel";
		
				case "ppt" :
				case "pps" :
					return "application/vnd.ms-powerpoint";
		
				case "rtf" :
					return "application/rtf";
		
				case "pdf" :
					return "application/pdf";
		
				case "html" :
				case "htm" :
				case "php" :
					return "text/html";
		
				case "txt" :
					return "text/plain";
		
				case "mpeg" :
				case "mpg" :
				case "mpe" :
					return "video/mpeg";
		
				case "mp3" :
					return "audio/mpeg3";
		
				case "wav" :
					return "audio/wav";
		
				case "aiff" :
				case "aif" :
					return "audio/aiff";
		
				case "avi" :
					return "video/msvideo";
		
				case "wmv" :
					return "video/x-ms-wmv";
		
				case "mov" :
					return "video/quicktime";
		
				case "zip" :
					return "application/zip";
		
				case "tar" :
					return "application/x-tar";
		
				case "swf" :
					return "application/x-shockwave-flash";
		
				default :
			}
		}
		return isset($fileSuffix[0]) ? "unknown/" . trim($fileSuffix[0], ".") : "text/plain";
	}
}