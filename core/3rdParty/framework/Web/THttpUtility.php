<?php
/**
 * THttpUtility class file
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.pradosoft.com/
 * @copyright Copyright &copy; 2005-2014 PradoSoft
 * @license http://www.pradosoft.com/license/
 * @package System.Web
 */

/**
 * THttpUtility class
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package System.Web
 * @since 3.0
 */
class THttpUtility
{
	private static $_encodeTable=array('<'=>'&lt;','>'=>'&gt;','"'=>'&quot;');
	private static $_decodeTable=array('&lt;'=>'<','&gt;'=>'>','&quot;'=>'"');
	private static $_stripTable=array('&lt;'=>'','&gt;'=>'','&quot;'=>'');

	/**
	 * HTML-encodes a string.
	 * This method translates the following characters to their corresponding
	 * HTML entities: <, >, "
	 * Note, unlike {@link htmlspecialchars}, & is not translated.
	 * @param string string to be encoded
	 * @return string encoded string
	 */
	public static function htmlEncode($s)
	{
		return strtr($s,self::$_encodeTable);
	}

	/**
	 * HTML-decodes a string.
	 * It is the inverse of {@link htmlEncode}.
	 * @param string string to be decoded
	 * @return string decoded string
	 */
	public static function htmlDecode($s)
	{
		return strtr($s,self::$_decodeTable);
	}

	/**
	 * This method strips the following characters from a string:
	 * HTML entities: <, >, "
	 * @param string string to be encoded
	 * @return string encoded string
	 */
	public static function htmlStrip($s)
	{
		return strtr($s,self::$_stripTable);
	}
}

