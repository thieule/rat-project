<?php
/**
 * @package     T.Platform
 * @subpackage  Error
 */

defined('T_PLATFORM') or die;

/**
 * Exception object.
 *
 * @package     T.Platform
 * @subpackage  Error
 * @since       5/2014
 */
class TException extends Exception
{
	/**
	 * @var    string  Error level.
	 * @since  5/2014
	 */
	protected $level = null;

	/**
	 * @var    string  Error code.
	 * @since  5/2014
	 */
	protected $code = null;

	/**
	 * @var    string  Error message.
	 * @since  5/2014
	 */
	protected $message = null;

	/**
	 * Additional info about the error relevant to the developer,
	 * for example, if a database connect fails, the dsn used
	 *
	 * @var    string
	 * @since  5/2014
	 */
	protected $info = '';

	/**
	 * Name of the file the error occurred in [Available if backtrace is enabled]
	 *
	 * @var    string
	 * @since  5/2014
	 */
	protected $file = null;

	/**
	 * Line number the error occurred in [Available if backtrace is enabled]
	 *
	 * @var    int
	 * @since  5/2014
	 */
	protected $line = 0;

	/**
	 * Name of the method the error occurred in [Available if backtrace is enabled]
	 *
	 * @var    string
	 * @since  5/2014
	 */
	protected $function = null;

	/**
	 * Name of the class the error occurred in [Available if backtrace is enabled]
	 *
	 * @var    string
	 * @since  5/2014
	 */
	protected $class = null;

	/**
	 * @var    string  Error type.
	 * @since  5/2014
	 */
	protected $type = null;

	/**
	 * Arguments recieved by the method the error occurred in [Available if backtrace is enabled]
	 *
	 * @var    array
	 * @since  5/2014
	 */
	protected $args = array();

	/**
	 * @var    mixed  Backtrace information.
	 * @since  5/2014
	 */
	protected $backtrace = null;

	/**
	 * Constructor
	 * - used to set up the error with all needed error details.
	 *
	 * @param   string   $msg        The error message
	 * @param   string   $code       The error code from the application
	 * @param   integer  $level      The error level (use the PHP constants E_ALL, E_NOTICE etc.).
	 * @param   string   $info       Optional: The additional error information.
	 * @param   boolean  $backtrace  True if backtrace information is to be collected
	 *
	 * @since   5/2014
	 *
	 */
	public function __construct($msg, $code = 0, $level = null, $info = null, $backtrace = false)
	{
		$this->level = $level;
		$this->code = $code;
		$this->message = $msg;

		if ($info != null)
		{
			$this->info = $info;
		}
                
		// Store exception for debugging purposes!
		TError::addToStack($this);

		parent::__construct($msg, (int) $code);
	}

	/**
	 * Returns to error message
	 *
	 * @return  string  Error message
	 *
	 * @since   5/2014
	 *
	 */
	public function __toString()
	{
		return $this->message;
	}

	/**
	 * Returns to error message
	 *
	 * @return  string   Error message
	 *
	 * @since   5/2014
	 * @deprecated    12.1
	 */
	public function toString()
	{
		return (string) $this;
	}

	/**
	 * Returns a property of the object or the default value if the property is not set.
	 *
	 * @param   string  $property  The name of the property
	 * @param   mixed   $default   The default value
	 *
	 * @return  mixed  The value of the property or null
	 *
	 * @deprecated  12.1
	 * @see         getProperties()
	 * @since       5/2014
	 */
	public function get($property, $default = null)
	{
		if (isset($this->$property))
		{
			return $this->$property;
		}
		return $default;
	}

	/**
	 * Returns an associative array of object properties
	 *
	 * @param   boolean  $public  If true, returns only the public properties
	 *
	 * @return  array  Object properties
	 *
	 * @deprecated    12.1
	 * @see     get()
	 * @since   5/2014
	 */
	public function getProperties($public = true)
	{
		$vars = get_object_vars($this);
		if ($public)
		{
			foreach ($vars as $key => $value)
			{
				if ('_' == substr($key, 0, 1))
				{
					unset($vars[$key]);
				}
			}
		}
		return $vars;
	}

	/**
	 * Get the most recent error message
	 *
	 * @param   integer  $i         Option error index
	 * @param   boolean  $toString  Indicates if TError objects should return their error message
	 *
	 * @return  string  Error message
	 *
	 * @since   5/2014
	 
	 */
	public function getError($i = null, $toString = true)
	{
		// Find the error
		if ($i === null)
		{
			// Default, return the last message
			$error = end($this->_errors);
		}
		elseif (!array_key_exists($i, $this->_errors))
		{
			// If $i has been specified but does not exist, return false
			return false;
		}
		else
		{
			$error = $this->_errors[$i];
		}

		// Check if only the string is requested
		if ($error instanceof Exception && $toString)
		{
			return (string) $error;
		}

		return $error;
	}

	/**
	 * Return all errors, if any
	 *
	 * @return  array  Array of error messages or TErrors
	 *
	 * @since   5/2014
	 *
	 */
	public function getErrors()
	{
		return $this->_errors;
	}

	/**
	 * Modifies a property of the object, creating it if it does not already exist.
	 *
	 * @param   string  $property  The name of the property
	 * @param   mixed   $value     The value of the property to set
	 *
	 * @return  mixed  Previous value of the property
	 *
	 * @deprecated  12.1
	 * @see         setProperties()
	 * @since       5/2014
	 */
	public function set($property, $value = null)
	{
		$previous = isset($this->$property) ? $this->$property : null;
		$this->$property = $value;
		return $previous;
	}

	/**
	 * Set the object properties based on a named array/hash
	 *
	 * @param   mixed  $properties  Either and associative array or another object
	 *
	 * @return  boolean
	 *
	 * @deprecated  12.1
	 * @see         set()
	 * @since       5/2014
	 */
	public function setProperties($properties)
	{
		// Cast to an array
		$properties = (array) $properties;

		if (is_array($properties))
		{
			foreach ($properties as $k => $v)
			{
				$this->$k = $v;
			}

			return true;
		}

		return false;
	}

	/**
	 * Add an error message
	 *
	 * @param   string  $error  Error message
	 *
	 * @return  void
	 *
	 * @since   5/2014
	 *
	 */
	public function setError($error)
	{
		array_push($this->_errors, $error);
	}
}
