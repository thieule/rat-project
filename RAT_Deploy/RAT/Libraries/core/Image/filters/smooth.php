<?php
/**
 * @package     T.Platform
 * @subpackage  Image
 */

defined('T_PLATFORM') or die;

/**
 * Image Filter class adjust the smoothness of an image.
 *
 * @package     T.Platform
 * @subpackage  Image
 * @since       5/2014
 */
class TImageFilterSmooth extends TImageFilter
{
	/**
	 * Method to apply a filter to an image resource.
	 *
	 * @param   array  $options  An array of options for the filter.
	 *
	 * @return  void
	 *
	 * @since   5/2014
	 * @throws  InvalidArgumentException
	 * @throws  RuntimeException
	 */
	public function execute(array $options = array())
	{
		// Verify that image filter support for PHP is available.
		if (!function_exists('imagefilter'))
		{
			TLog::add('The imagefilter function for PHP is not available.', TLog::ERROR);
			throw new RuntimeException('The imagefilter function for PHP is not available.');
		}

		// Validate that the smoothing value exists and is an integer.
		if (!isset($options[IMG_FILTER_SMOOTH]) || !is_int($options[IMG_FILTER_SMOOTH]))
		{
			throw new InvalidArgumentException('No valid smoothing value was given.  Expected integer.');
		}

		// Perform the smoothing filter.
		imagefilter($this->handle, IMG_FILTER_SMOOTH, $options[IMG_FILTER_SMOOTH]);
	}
}
