<?php
/**
 * @package     T.Platform
 * @subpackage  Log
 *
 */

defined('T_PLATFORM') or die;

tinclude('core.log.logger');

/**
 * Echo logger class.
 *
 * @package     Joomla.Platform
 * @subpackage  Log
 * @since       5/2014
 */
class TLoggerEcho extends TLogger
{
	/**
	 * @var    array  Translation array for TLogEntry priorities to text strings.
	 * @since  11.1
	 */
	protected $priorities = array(
		TLog::EMERGENCY => 'EMERGENCY',
		TLog::ALERT => 'ALERT',
		TLog::CRITICAL => 'CRITICAL',
		TLog::ERROR => 'ERROR',
		TLog::WARNING => 'WARNING',
		TLog::NOTICE => 'NOTICE',
		TLog::INFO => 'INFO',
		TLog::DEBUG => 'DEBUG');

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
		echo $this->priorities[$entry->priority] . ': ' . $entry->message . (empty($entry->category) ? '' : ' [' . $entry->category . ']') . "\n";
	}
}
