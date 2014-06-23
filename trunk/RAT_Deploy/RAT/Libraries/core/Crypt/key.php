<?php
/**
 * @package     T.Platform
 * @subpackage  Crypt
 */

defined('T_PLATFORM') or die;

/**
 * Encryption key object for the T Platform.
 *
 * @property-read  string  $type  The key type.
 *
 * @package     T.Platform
 * @subpackage  Crypt
 * @since       5/2014
 */
class TCryptKey
{
	/**
	 * @var    string  The private key.
	 * @since  5/2014
	 */
	public $private;

	/**
	 * @var    string  The public key.
	 * @since  5/2014
	 */
	public $public;

	/**
	 * @var    string  The key type.
	 * @since  5/2014
	 */
	protected $type;

	/**
	 * Constructor.
	 *
	 * @param   string  $type     The key type.
	 * @param   string  $private  The private key.
	 * @param   string  $public   The public key.
	 *
	 * @since   5/2014
	 */
	public function __construct($type, $private = null, $public = null)
	{
		// Set the key type.
		$this->type = (string) $type;

		// Set the optional public/private key strings.
		$this->private = isset($private) ? (string) $private : null;
		$this->public  = isset($public) ? (string) $public : null;
	}

	/**
	 * Magic method to return some protected property values.
	 *
	 * @param   string  $name  The name of the property to return.
	 *
	 * @return  mixed
	 *
	 * @since   5/2014
	 */
	public function __get($name)
	{
		if ($name == 'type')
		{
			return $this->type;
		}
		else
		{
			trigger_error('Cannot access property ' . __CLASS__ . '::' . $name, E_USER_WARNING);
		}
	}
}
