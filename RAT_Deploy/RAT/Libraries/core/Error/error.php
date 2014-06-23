<?php
/**
 * @package     T.Platform
 * @subpackage  Error
 */

defined('T_PLATFORM') or die;

// Error Definition: Illegal Options
define('TERROR_ILLEGAL_OPTIONS', 1);
// Error Definition: Callback does not exist
define('TERROR_CALLBACK_NOT_CALLABLE', 2);
// Error Definition: Illegal Handler
define('TERROR_ILLEGAL_MODE', 3);

/**
 * Error Handling Class
 *
 * This class is inspired in design and concept by patErrorManager <http://www.php-tools.net>
 *
 * patErrorManager contributors include:
 * - gERD Schaufelberger	<gerd@php-tools.net>
 * - Sebastian Mordziol	<argh@php-tools.net>
 * - Stephan Schmidt		<scst@php-tools.net>
 *
 * @package     T.Platform
 * @subpackage  Error
 * @since       5/2014
 */
abstract class TError
{
	/**
	 * Legacy error handling marker
	 *
	 * @var    boolean  True to enable legacy error handling using TError, false to use exception handling.  This flag
	 *                  is present to allow an easy transition into exception handling for code written against the
	 *                  existing TError API.
	 * @since  5/2014
	 */
	public static $legacy = false;

	/**
	 * Array of message levels
	 *
	 * @var    array
	 * @since  5/2014
	 */
	protected static $levels = array(E_NOTICE => 'Notice', E_WARNING => 'Warning', E_ERROR => 'Error');

	protected static $handlers = array(
		E_NOTICE => array('mode' => 'ignore'),
		E_WARNING => array('mode' => 'ignore'),
		E_ERROR => array('mode' => 'ignore')
	);

	protected static $stack = array();

	/**
	 * Method to determine if a value is an exception object.  This check supports
	 * both TException and PHP5 Exception objects
	 *
	 * @param   mixed  &$object  Object to check
	 *
	 * @return  boolean  True if argument is an exception, false otherwise.
	 *
	 * @since   5/2014
	 *
	 */
	public static function isError(& $object)
	{
		// Supports PHP 5 exception handling
		return $object instanceof Exception;
	}

	/**
	 * Method for retrieving the last exception object in the error stack
	 *
	 * @param   boolean  $unset  True to remove the error from the stack.
	 *
	 * @return  mixed  Last exception object in the error stack or boolean false if none exist
	 *
	 * @deprecated  12.1
	 * @since   5/2014
	 */
	public static function getError($unset = false)
	{
	
		if (!isset(TError::$stack[0]))
		{
			return false;
		}

		if ($unset)
		{
			$error = array_shift(TError::$stack);
		}
		else
		{
			$error = &TError::$stack[0];
		}
		return $error;
	}

	/**
	 * Method for retrieving the exception stack
	 *
	 * @return  array  Chronological array of errors that have been stored during script execution
	 *
	 * @since   5/2014
	 */
	public static function getErrors()
	{
		return TError::$stack;
	}

	/**
	 * Method to add non-TError thrown TExceptions to the TError stack for debugging purposes
	 *
	 * @param   TException  &$e  Add an exception to the stack.
	 *
	 * @return  void
	 *
	 * @since       5/2014
	 */
	public static function addToStack(TException &$e)
	{
		TError::$stack[] = &$e;
	}

	/**
	 * Create a new TException object given the passed arguments
	 *
	 * @param   integer  $level      The error level - use any of PHP's own error levels for
	 *                               this: E_ERROR, E_WARNING, E_NOTICE, E_USER_ERROR,
	 *                               E_USER_WARNING, E_USER_NOTICE.
	 * @param   string   $code       The application-internal error code for this error
	 * @param   string   $msg        The error message, which may also be shown the user if need be.
	 * @param   mixed    $info       Optional: Additional error information (usually only
	 *                               developer-relevant information that the user should never see,
	 *                               like a database DSN).
	 * @param   boolean  $backtrace  Add a stack backtrace to the exception.
	 *
	 * @return  mixed    The TException object
	 *
	 * @since       5/2014
	 
	 * @see         TException
	 */
	public static function raise($level, $code, $msg, $info = null, $backtrace = false)
	{
		tinclude('core.error.exception');

		// Build error object
		$exception = new TException($msg, $code, $level, $info, $backtrace);
		return TError::throwError($exception);
	}

	/**
	 * Throw an error
	 *
	 * @param   object  &$exception  An exception to throw.
	 *
	 * @return  reference
	 *
	 * @deprecated  12.1  Use PHP Exception
	 * @see     TException
	 * @since   5/2014
	 */
	public static function throwError(&$exception)
	{
		static $thrown = false;

		// If thrown is hit again, we've come back to TError in the middle of throwing another TError, so die!
		if ($thrown)
		{
			self::handleEcho($exception, array());
			// Inifite loop.
			texit();
		}

		$thrown = true;
		$level = $exception->get('level');

		// See what to do with this kind of error
		$handler = TError::getErrorHandling($level);

		$function = 'handle' . ucfirst($handler['mode']);
		if (is_callable(array('TError', $function)))
		{
			$reference = call_user_func_array(array('TError', $function), array(&$exception, (isset($handler['options'])) ? $handler['options'] : array()));
		}
		else
		{
			// This is required to prevent a very unhelpful white-screen-of-death
			texit(
				'TError::raise -> Static method TError::' . $function . ' does not exist.' . ' Contact a developer to debug' .
				'<br /><strong>Error was</strong> ' . '<br />' . $exception->getMessage()
			);
		}
		// We don't need to store the error, since TException already does that for us!
		// Remove loop check
		$thrown = false;

		return $reference;
	}

	/**
	 * Wrapper method for the raise() method with predefined error level of E_ERROR and backtrace set to true.
	 *
	 * @param   string  $code  The application-internal error code for this error
	 * @param   string  $msg   The error message, which may also be shown the user if need be.
	 * @param   mixed   $info  Optional: Additional error information (usually only
	 *                         developer-relevant information that the user should
	 *                         never see, like a database DSN).
	 *
	 * @return  object  $error  The configured TError object
	 *
	 * @see        raise()
	 * @since   5/2014
	 */
	public static function raiseError($code, $msg, $info = null)
	{
	
		return TError::raise(E_ERROR, $code, $msg, $info, true);
	}

	/**
	 * Wrapper method for the {@link raise()} method with predefined error level of E_WARNING and
	 * backtrace set to false.
	 *
	 * @param   string  $code  The application-internal error code for this error
	 * @param   string  $msg   The error message, which may also be shown the user if need be.
	 * @param   mixed   $info  Optional: Additional error information (usually only
	 *                         developer-relevant information that
	 *                         the user should never see, like a database DSN).
	 *
	 * @return  object  The configured TError object
	 *
	 * @deprecated  12.1  Use PHP Exception
	 * @see        TError
	 * @see        raise()
	 * @since      5/2014
	 */
	public static function raiseWarning($code, $msg, $info = null)
	{
		return TError::raise(E_WARNING, $code, $msg, $info);
	}

	/**
	 * Wrapper method for the {@link raise()} method with predefined error
	 * level of E_NOTICE and backtrace set to false.
	 *
	 * @param   string  $code  The application-internal error code for this error
	 * @param   string  $msg   The error message, which may also be shown the user if need be.
	 * @param   mixed   $info  Optional: Additional error information (usually only
	 *                         developer-relevant information that the user
	 *                         should never see, like a database DSN).
	 *
	 * @return  object   The configured TError object
	 *
	 * @deprecated       12.1   Use PHP Exception
	 * @see     raise()
	 * @since   5/2014
	 */
	public static function raiseNotice($code, $msg, $info = null)
	{
		return TError::raise(E_NOTICE, $code, $msg, $info);
	}

	/**
	 * Method to get the current error handler settings for a specified error level.
	 *
	 * @param   integer  $level  The error level to retrieve. This can be any of PHP's
	 *                           own error levels, e.g. E_ALL, E_NOTICE...
	 *
	 * @return  array    All error handling details
	 *
	 * @deprecated   12.1  Use PHP Exception
	 * @since   5/2014
	 */
	public static function getErrorHandling($level)
	{
		return TError::$handlers[$level];
	}

	/**
	 * Method to set the way the TError will handle different error levels. Use this if you want to override the default settings.
	 *
	 * Error handling modes:
	 * - ignore
	 * - echo
	 * - verbose
	 * - die
	 * - message
	 * - log
	 * - callback
	 *
	 * You may also set the error handling for several modes at once using PHP's bit operations.
	 * Examples:
	 * - E_ALL = Set the handling for all levels
	 * - E_ERROR | E_WARNING = Set the handling for errors and warnings
	 * - E_ALL ^ E_ERROR = Set the handling for all levels except errors
	 *
	 * @param   integer  $level    The error level for which to set the error handling
	 * @param   string   $mode     The mode to use for the error handling.
	 * @param   mixed    $options  Optional: Any options needed for the given mode.
	 *
	 * @return  mixed  True on success or a TException object if failed.
	 *
	
	 * @since   5/2014
	 */
	public static function setErrorHandling($level, $mode, $options = null)
	{
	
		$levels = TError::$levels;

		$function = 'handle' . ucfirst($mode);

		if (!is_callable(array('TError', $function)))
		{
			return TError::raiseError(E_ERROR, 'TError:' . TERROR_ILLEGAL_MODE, 'Error Handling mode is not known', 'Mode: ' . $mode . ' is not implemented.');
		}

		foreach ($levels as $eLevel => $eTitle)
		{
			if (($level & $eLevel) != $eLevel)
			{
				continue;
			}

			// Set callback options
			if ($mode == 'callback')
			{
				if (!is_array($options))
				{
					return TError::raiseError(E_ERROR, 'TError:' . TTERROR_ILLEGAL_OPTIONS, 'Options for callback not valid');
				}

				if (!is_callable($options))
				{
					$tmp = array('GLOBAL');
					if (is_array($options))
					{
						$tmp[0] = $options[0];
						$tmp[1] = $options[1];
					}
					else
					{
						$tmp[1] = $options;
					}

					return TError::raiseError(
						E_ERROR,
						'TError:' . TERROR_CALLBACK_NOT_CALLABLE,
						'Function is not callable',
						'Function:' . $tmp[1] . ' scope ' . $tmp[0] . '.'
					);
				}
			}

			// Save settings
			TError::$handlers[$eLevel] = array('mode' => $mode);
			if ($options != null)
			{
				TError::$handlers[$eLevel]['options'] = $options;
			}
		}

		return true;
	}

	/**
	 * Method that attaches the error handler to TError
	 *
	 * @return  void
	 *
	 * @see     set_error_handler
	 * @since   5/2014
	 */
	public static function attachHandler()
	{
		set_error_handler(array('TError', 'customErrorHandler'));
	}

	/**
	 * Method that detaches the error handler from TError
	 *
	 * @return  void
	 *
	 * @see     restore_error_handler
	 * @since   5/2014
	 */
	public static function detachHandler()
	{
		restore_error_handler();
	}

	/**
	 * Method to register a new error level for handling errors
	 *
	 * This allows you to add custom error levels to the built-in
	 * - E_NOTICE
	 * - E_WARNING
	 * - E_NOTICE
	 *
	 * @param   integer  $level    Error level to register
	 * @param   string   $name     Human readable name for the error level
	 * @param   string   $handler  Error handler to set for the new error level [optional]
	 *
	 * @return  boolean  True on success; false if the level already has been registered
	 *
	 * @deprecated  12.1
	 * @since   5/2014
	 */
	public static function registerErrorLevel($level, $name, $handler = 'ignore')
	{
		if (isset(TError::$levels[$level]))
		{
			return false;
		}

		TError::$levels[$level] = $name;
		TError::setErrorHandling($level, $handler);

		return true;
	}

	/**
	 * Translate an error level integer to a human readable string
	 * e.g. E_ERROR will be translated to 'Error'
	 *
	 * @param   integer  $level  Error level to translate
	 *
	 * @return  mixed  Human readable error level name or boolean false if it doesn't exist
	 *
	 * @since   5/2014
	 */

	public static function translateErrorLevel($level)
	{
		if (isset(TError::$levels[$level]))
		{
			return TError::$levels[$level];
		}

		return false;
	}

	/**
	 * Ignore error handler
	 * - Ignores the error
	 *
	 * @param   object  &$error   Exception object to handle
	 * @param   array   $options  Handler options
	 *
	 * @return  object   The exception object
	 *
	 * @see     raise()
	 * @since   5/2014
	 */
	public static function handleIgnore(&$error, $options)
	{
		return $error;
	}

	
	
	/**
	 * Die error handler
	 * - Echos the error message to output and then dies
	 *
	 * @param   object  &$error   Exception object to handle
	 * @param   array   $options  Handler options
	 *
	 * @return  object  The exception object
	 *
	 * @see         raise()
	 * @since       5/2014
	 */
	public static function handleDie(&$error, $options)
	{
		// Deprecation warning.
		TLog::add('TError::handleDie() is deprecated.', TLog::WARNING, 'deprecated');

		texit("J$level_human: " . $error->get('message') . "\n",true);

		return $error;
	}

	/**
	 * Message error handler
	 * Enqueues the error message into the system queue
	 *
	 * @param   object  &$error   Exception object to handle
	 * @param   array   $options  Handler options
	 *
	 * @return  object  The exception object
	 *
	 * @see         raise()
	 * @since       5/2014
	 */
	public static function handleMessage(&$error, $options)
	{
		// Deprecation warning.
		TLog::add('TError::handleMessage() is deprecated.', TLog::WARNING, 'deprecated');

		$appl = T::getApplication();
		$type = ($error->get('level') == E_NOTICE) ? 'notice' : 'error';
		$appl->enqueueMessage($error->get('message'), $type);

		return $error;
	}

	/**
	 * Log error handler
	 * Logs the error message to a system log file
	 *
	 * @param   object  &$error   Exception object to handle
	 * @param   array   $options  Handler options
	 *
	 * @return  object  The exception object
	 *
	 * @deprecated  12.1
	 * @see         raise()
	 * @since       5/2014
	 */
	public static function handleLog(&$error, $options)
	{
		static $log;

		if ($log == null)
		{
			$fileName = date('Y-m-d') . '.error.log';
			$options['format'] = "{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}";
			$log = TLog::getInstance($fileName, $options);
		}

		$entry['level'] = $error->get('level');
		$entry['code'] = $error->get('code');
		$entry['message'] = str_replace(array("\r", "\n"), array('', '\\n'), $error->get('message'));
		$log->addEntry($entry);

		return $error;
	}

	/**
	 * Callback error handler
	 * - Send the error object to a callback method for error handling
	 *
	 * @param   object  &$error   Exception object to handle
	 * @param   array   $options  Handler options
	 *
	 * @return  object  The exception object
	 *
	 * @deprecated  12.1
	 * @see         raise()
	 * @since       5/2014
	 */
	public static function handleCallback(&$error, $options)
	{

		return call_user_func($options, $error);
	}

	
}
