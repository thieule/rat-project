<?php
/**
 * @package     T.Platform
 * @subpackage  Utilities
 */

defined('T_PLATFORM') or die;

/**
 * TUtility is a utility functions class
 *
 * @package     T.Platform
 * @subpackage  Utilities
 * @since       5/2014
 */
class TUtility
{
	/**
	 * Mail function (uses phpMailer)
	 *
	 * @param   string   $from         From email address
	 * @param   string   $fromname     From name
	 * @param   mixed    $recipient    Recipient email address(es)
	 * @param   string   $subject      Email subject
	 * @param   string   $body         Message body
	 * @param   boolean  $mode         False = plain text, true = HTML
	 * @param   mixed    $cc           CC email address(es)
	 * @param   mixed    $bcc          BCC email address(es)
	 * @param   mixed    $attachment   Attachment file name(s)
	 * @param   mixed    $replyto      Reply to email address(es)
	 * @param   mixed    $replytoname  Reply to name(s)
	 *
	 * @return  boolean  True on success
	 *
	 * @see     TMail::sendMail()
	 * @since   5/2014
	 */
	public static function sendMail($from, $fromname, $recipient, $subject, $body, $mode = 0, $cc = null, $bcc = null, $attachment = null,
		$replyto = null, $replytoname = null)
	{
		// Deprecation warning.
		TLog::add('TUtility::sendmail() is deprecated.', TLog::WARNING, 'deprecated');

		// Get a TMail instance
		$mail = T::getMailer();

		return $mail->sendMail($from, $fromname, $recipient, $subject, $body, $mode, $cc, $bcc, $attachment, $replyto, $replytoname);
	}

	/**
	 * Sends mail to administrator for approval of a user submission
	 *
	 * @param   string  $adminName   Name of administrator
	 * @param   string  $adminEmail  Email address of administrator
	 * @param   string  $email       [NOT USED]
	 * @param   string  $type        Type of item to approve
	 * @param   string  $title       Title of item to approve
	 * @param   string  $author      Author of item to approve
	 * @param   string  $url         url
	 *
	 * @return  boolean  True on success
	 *
	 * @see     TMail::sendAdminMail()
	 * @since   5/2014
	 */
	public static function sendAdminMail($adminName, $adminEmail, $email, $type, $title, $author, $url = null)
	{
		// Deprecation warning.
		TLog::add('TUtility::sendAdminMail() is deprecated.', TLog::WARNING, 'deprecated');

		// Get a TMail instance
		$mail = T::getMailer();

		return $mail->sendAdminMail($adminName, $adminEmail, $email, $type, $title, $author, $url);
	}

	/**
	 * Provides a secure hash based on a seed
	 *
	 * @param   string  $seed  Seed string.
	 *
	 * @return  string
	 *
	 * @see     TApplication::getHash()
	 * @since   5/2014
	 */
	public static function getHash($seed)
	{
		// Deprecation warning.
		TLog::add('TUtility::getHash() is deprecated. Use TApplication::getHash() instead.', TLog::WARNING, 'deprecated');

		return TApplication::getHash($seed);
	}

	/**
	 * Method to determine a hash for anti-spoofing variable names
	 *
	 * @param   boolean  $forceNew  Force creation of a new token.
	 *
	 * @return  string   Hashed var name
	 *
	 * @see     TSession::getFormToken()
	 * @since   5/2014
	 */
	public static function getToken($forceNew = false)
	{
		// Deprecation warning.
		TLog::add('TUtility::getToken() is deprecated. Use TSession::getFormToken() instead.', TLog::WARNING, 'deprecated');

		$session = T::getSession();
		return $session->getFormToken($forceNew);
	}

	/**
	 * Method to extract key/value pairs out of a string with XML style attributes
	 *
	 * @param   string  $string  String containing XML style attributes
	 *
	 * @return  array  Key/Value pairs for the attributes
	 *
	 * @since   5/2014
	 */
	public static function parseAttributes($string)
	{
		// Initialise variables.
		$attr = array();
		$retarray = array();

		// Let's grab all the key/value pairs using a regular expression
		preg_match_all('/([\w:-]+)[\s]?=[\s]?"([^"]*)"/i', $string, $attr);

		if (is_array($attr))
		{
			$numPairs = count($attr[1]);
			for ($i = 0; $i < $numPairs; $i++)
			{
				$retarray[$attr[1][$i]] = $attr[2][$i];
			}
		}

		return $retarray;
	}

	/**
	 * Method to determine if the host OS is Windows.
	 *
	 * @return  boolean  True if Windows OS.
	 *
	 * @see     TApplication::isWinOS()
	 * @since   5/2014
	 */
	public static function isWinOS()
	{
		// Deprecation warning.
		TLog::add('TUtility::isWinOS() is deprecated.', TLog::WARNING, 'deprecated');

		$application = T::getApplication();

		return $application->isWinOS();
	}

	/**
	 * Method to dump the structure of a variable for debugging purposes
	 *
	 * @param   mixed    &$var      A variable
	 * @param   boolean  $htmlSafe  True to ensure all characters are htmlsafe
	 *
	 * @return  string
	 *
	 * @since   5/2014
	 */
	public static function dump(&$var, $htmlSafe = true)
	{
		// Deprecation warning.
		TLog::add('TUtility::dump() is deprecated.', TLog::WARNING, 'deprecated');

		$result = var_export($var, true);

		return '<pre>' . ($htmlSafe ? htmlspecialchars($result, ENT_COMPAT, 'UTF-8') : $result) . '</pre>';
	}

	/**
	 * Prepend a reference to an element to the beginning of an array.
	 * Renumbers numeric keys, so $value is always inserted to $array[0]
	 *
	 * @param   array  &$array  Array to be modified
	 * @param   mixed  &$value  Value to add
	 *
	 * @return  integer
	 *
	 * @see     http://www.php.net/manual/en/function.array-unshift.php#40270
	 * @note     PHP no longer supports array_unshift of references.
	 * @since   5/2014
	 */
	public function array_unshift_ref(&$array, &$value)
	{
		// Deprecation warning.
		TLog::add('TUtility::array_unshift_ref() is deprecated.', TLog::WARNING, 'deprecated');

		$return = array_unshift($array, '');
		$array[0] = &$value;

		return $return;
	}

	/**
	 * Return the byte value of a particular string
	 *
	 * @param   string  $val  String optionally with G, M or K suffix
	 *
	 * @return  integer  Size in bytes
	 *
	 * @see     THtmlNumber::bytes
	 * @since   5/2014
	 */
	public function return_bytes($val)
	{
		// Deprecation warning.
		TLog::add('TUtility::return_bytes() is deprecated.', TLog::WARNING, 'deprecated');

		$val = trim($val);
		$last = strtolower($val{strlen($val) - 1});

		switch ($last)
		{
			// The 'G' modifier is available since PHP 5.1.0
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}

		return $val;
	}
}
