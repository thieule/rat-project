<?php
/**
 * @package     T.Platform
 * @subpackage  Log
 */

defined('T_PLATFORM') or die;

/**
 * Logger Base Class
 *
 * This class is used to be the basis of logger classes to allow for defined functions
 * to exist regardless of the child class.
 *
 * @package     Joomla.Platform
 * @subpackage  Log
 * @since       5/2014
 */
abstract class TLogger
{
	/**
	 * Options array for the TLog instance.
	 * @var    array
	 * @since  5/2014
	 */
	protected $options = array();

	/**
	 * Constructor.
	 *
	 * @param   array  &$options  Log object options.
	 *
	 * @since   5/2014
	 */
	public function __construct(array &$options)
	{
		// Set the options for the class.
		$this->options = & $options;
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
	abstract public function addEntry(TLogEntry $entry);
}
