<?php
/**
 * @package    T.Platform
 */

defined('T_PLATFORM') or die;


/**
 * Text  handling class.
 *
 * @package     T.Platform
 * @subpackage  Language
 * @since       5/2014
 */
class Text
{
	/**
	 *  strings
	 *
	 * @var    array
	 * @since  5/2014
	 */
	protected static $strings = array();

	/**
	 * Translates a string into the current language.
	 *
	 
	 * @param   string   $string  The string to translate.
	 * @return  string  The translated string 
	 * @since   5/2014
	 */
	public static function _($string)
	{
		return $string;
	}

}
