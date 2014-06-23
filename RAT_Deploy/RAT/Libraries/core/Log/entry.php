<?php
/**
 * @package     T.Platform
 * @subpackage  Log
 */

defined('T_PLATFORM') or die;

tinclude('core.utilities.date');

/**
 * Log Entry class
 *
 * This class is designed to hold log entries for either writing to an engine, or for
 * supported engines, retrieving lists and building in memory (PHP based) search operations.
 *
 * @package     T.Platform
 * @subpackage  Log
 * @since       5/2014
 */
class TLogEntry
{
	/**
	 * Application responsible for log entry.
	 * @var    string
	 * @since  5/2014
	 */
	public $category;

	/**
	 * The date the message was logged.
	 * @var    TDate
	 * @since  5/2014
	 */
	public $date;

	/**
	 * Message to be logged.
	 * @var    string
	 * @since  5/2014
	 */
	public $message;

	/**
	 * The priority of the message to be logged.
	 * @var    string
	 * @since  5/2014
	 * @see    $priorities
	 */
	public $priority = TLog::INFO;

	/**
	 * List of available log priority levels [Based on the SysLog default levels].
	 * @var    array
	 * @since  5/2014
	 */
	protected $priorities = array(
		TLog::EMERGENCY,
		TLog::ALERT,
		TLog::CRITICAL,
		TLog::ERROR,
		TLog::WARNING,
		TLog::NOTICE,
		TLog::INFO,
		TLog::DEBUG
	);

	/**
	 * Constructor
	 *
	 * @param   string  $message   The message to log.
	 * @param   string  $priority  Message priority based on {$this->priorities}.
	 * @param   string  $category  Type of entry
	 * @param   string  $date      Date of entry (defaults to now if not specified or blank)
	 *
	 * @since   5/2014
	 */
	public function __construct($message, $priority = TLog::INFO, $category = '', $date = null)
	{
		$this->message = (string) $message;

		// Sanitize the priority.
		if (!in_array($priority, $this->priorities, true))
		{
			$priority = TLog::INFO;
		}
		$this->priority = $priority;

		// Sanitize category if it exists.
		if (!empty($category))
		{
			$this->category = (string) strtolower(preg_replace('/[^A-Z0-9_\.-]/i', '', $category));
		}

		// Get the date as a TDate object.
		$this->date = new TDate($date ? $date : 'now');
	}
}
