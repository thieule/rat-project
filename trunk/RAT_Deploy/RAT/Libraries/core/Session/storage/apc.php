<?php
/**
 * @package     T.Platform
 * @subpackage  Session
 */

defined('T_PLATFORM') or die;

/**
 * APC session storage handler for PHP
 *
 * @package     T.Platform
 * @subpackage  Session
 * @see         http://www.php.net/manual/en/function.session-set-save-handler.php
 * @since       5/2014
 */
class TSessionStorageApc extends TSessionStorage
{
	/**
	 * Constructor
	 *
	 * @param   array  $options  Optional parameters
	 *
	 * @since   5/2014
	 */
	public function __construct($options = array())
	{
		if (!$this->test())
		{
			return TError::raiseError(404, Text::_('LIB_SESSION_APC_EXTENSION_NOT_AVAILABLE'));
		}

		parent::__construct($options);
	}

	/**
	 * Read the data for a particular session identifier from the
	 * SessionHandler backend.
	 *
	 * @param   string  $id  The session identifier.
	 *
	 * @return  string  The session data.
	 *
	 * @since   5/2014
	 */
	public function read($id)
	{
		$sess_id = 'sess_' . $id;
		return (string) apc_fetch($sess_id);
	}

	/**
	 * Write session data to the SessionHandler backend.
	 *
	 * @param   string  $id            The session identifier.
	 * @param   string  $session_data  The session data.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   5/2014
	 */
	public function write($id, $session_data)
	{
		$sess_id = 'sess_' . $id;
		return apc_store($sess_id, $session_data, ini_get("session.gc_maxlifetime"));
	}

	/**
	 * Destroy the data for a particular session identifier in the SessionHandler backend.
	 *
	 * @param   string  $id  The session identifier.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   5/2014
	 */
	public function destroy($id)
	{
		$sess_id = 'sess_' . $id;
		return apc_delete($sess_id);
	}

	/**
	 * Test to see if the SessionHandler is available.
	 *
	 * @return boolean  True on success, false otherwise.
	 */
	public static function test()
	{
		return extension_loaded('apc');
	}
}
