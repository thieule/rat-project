<?php
/**
 * @package     T.Platform
 * @subpackage  Image
 */

defined('T_PLATFORM') or die;

/**
 * Image Filter class to make an image appear "sketchy".
 *
 * @package     T.Platform
 * @subpackage  Image
 * @since       5/2014
 */
class TImageFilterSketchy extends TImageFilter
{
	/**
	 * Method to apply a filter to an image resource.
	 *
	 * @param   array  $options  An array of options for the filter.
	 *
	 * @return  void
	 *
	 * @since   5/2014
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

		// Perform the sketchy filter.
		imagefilter($this->handle, IMG_FILTER_MEAN_REMOVAL);
	}
}
