<?php
/**
 * @package     T.Platform
 * @subpackage  Base
 */

defined('T_PLATFORM') or die;

/**
 * Abstract observer class to implement the observer design pattern
 *
 * @package     T.Platform
 * @subpackage  Base
 * @since       5/2014
 * @codeCoverageIgnore
 */
abstract class TObserver extends TObject
{
	/**
	 * Event object to observe.
	 *
	 * @var    object
	 * @since  5/2014
	 * @deprecated  12.3
	 */
	protected $_subject = null;

	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe.
	 *
	 * @since   5/2014
	 * @deprecated  12.3
	 */
	public function __construct(&$subject)
	{
		// Register the observer ($this) so we can be notified
		$subject->attach($this);

		// Set the subject to observe
		$this->_subject = &$subject;
	}

	/**
	 * Method to update the state of observable objects
	 *
	 * @param   array  &$args  An array of arguments to pass to the listener.
	 *
	 * @return  mixed
	 *
	 * @since   5/2014
	 * @deprecated  12.3
	 */
	public abstract function update(&$args);
}
