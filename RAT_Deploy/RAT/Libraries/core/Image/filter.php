<?php
/**
 * @package     T.Platform
 * @subpackage  Image
 */

defined('T_PLATFORM') or die;

/**
 * Class to manipulate an image.
 *
 * @package     T.Platform
 * @subpackage  Image
 * @since       5/2014
 */
abstract class TImageFilter
{
	/**
	 * @var    resource  The image resource handle.
	 * @since  5/2014
	 */
	protected $handle;

	/**
	 * Class constructor.
	 *
	 * @param   resource  $handle  The image resource on which to apply the filter.
	 *
	 * @since   5/2014
	 * @throws  InvalidArgumentException
	 */
	public function __construct($handle)
	{
		// Make sure the file handle is valid.
		if (!is_resource($handle) || (get_resource_type($handle) != 'gd'))
		{
			TLog::add('The image handle is invalid for the image filter.', TLog::ERROR);
			throw new InvalidArgumentException('The image handle is invalid for the image filter.');
		}

		$this->handle = $handle;
	}

	/**
	 * Method to apply a filter to an image resource.
	 *
	 * @param   array  $options  An array of options for the filter.
	 *
	 * @return  void
	 *
	 * @since   5/2014
	 */
	abstract public function execute(array $options = array());
}
