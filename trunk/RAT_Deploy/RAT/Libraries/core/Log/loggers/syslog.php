<?php
/**
 * @package     T.Platform
 * @subpackage  Log
 */

defined('T_PLATFORM') or die;

tinclude('core.log.logger');

/**
 * SysLog Log class
 *
 * This class is designed to call the PHP SysLog function call which is then sent to the
 * system wide log system. For Linux/Unix based systems this is the syslog subsystem, for
 * the Windows based implementations this can be found in the Event Log. For Windows,
 * permissions may prevent PHP from properly outputting messages.
 *
 * @package     T.Platform
 * @subpackage  Log
 * @since       5/2014
 */
class TLoggerSysLog extends TLogger
{
	/**
	 * @var    array  Translation array for TLogEntry priorities to SysLog priority names.
	 * @since  5/2014
	 */
	protected $priorities = array(
		TLog::EMERGENCY => 'EMERG',
		TLog::ALERT => 'ALERT',
		TLog::CRITICAL => 'CRIT',
		TLog::ERROR => 'ERR',
		TLog::WARNING => 'WARNING',
		TLog::NOTICE => 'NOTICE',
		TLog::INFO => 'INFO',
		TLog::DEBUG => 'DEBUG');

	/**
	 * Constructor.
	 *
	 * @param   array  &$options  Log object options.
	 *
	 * @since   5/2014
	 */
	public function __construct(array &$options)
	{
		// Call the parent constructor.
		parent::__construct($options);

		// Ensure that we have an identity string for the SysLog entries.
		if (empty($this->options['sys_ident']))
		{
			$this->options['sys_ident'] = 'Joomla Platform';
		}

		// If the option to add the process id to SysLog entries is set use it, otherwise default to true.
		if (isset($this->options['sys_add_pid']))
		{
			$this->options['sys_add_pid'] = (bool) $this->options['sys_add_pid'];
		}
		else
		{
			$this->options['sys_add_pid'] = true;
		}

		// If the option to also send SysLog entries to STDERR is set use it, otherwise default to false.
		if (isset($this->options['sys_use_stderr']))
		{
			$this->options['sys_use_stderr'] = (bool) $this->options['sys_use_stderr'];
		}
		else
		{
			$this->options['sys_use_stderr'] = false;
		}

		// Build the SysLog options from our log object options.
		$sysOptions = 0;
		if ($this->options['sys_add_pid'])
		{
			$sysOptions = $sysOptions | LOG_PID;
		}
		if ($this->options['sys_use_stderr'])
		{
			$sysOptions = $sysOptions | LOG_PERROR;
		}

		// Open the SysLog connection.
		openlog((string) $this->options['sys_ident'], $sysOptions, LOG_USER);
	}

	/**
	 * Destructor.
	 *
	 * @since   5/2014
	 */
	public function __destruct()
	{
		closelog();
	}

	/**
	 * Method to add an entry to the log.
	 *
	 * @param   TLogEntry  $entry  The log entry object to add to the log.
	 *
	 * @return  void
	 *
	 * @since   5/2014
	 */
	public function addEntry(TLogEntry $entry)
	{
		// Generate the value for the priority based on predefined constants.
		$priority = constant(strtoupper('LOG_' . $this->priorities[$entry->priority]));

		// Send the entry to SysLog.
		syslog($priority, '[' . $entry->category . '] ' . $entry->message);
	}
}
