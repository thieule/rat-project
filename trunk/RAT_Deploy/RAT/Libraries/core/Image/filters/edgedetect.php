<?php
/**
 * @package     T.Platform
 * @subpackage  Image
 */

defined('T_PLATFORM') or die;

/**
 * Image Filter class to add an edge detect effect to an image.
 *
 * @package     T.Platform
 * @subpackage  Image
 * @since       5/2014
 */
class TImageFilterEdgedetect extends TImageFilter
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
			// @codeCoverageIgnoreStart
			TLog::add('The imagefilter function for PHP is not available.', TLog::ERROR);
			throw new RuntimeException('The imagefilter function for PHP is not available.');

			// @codeCoverageIgnoreEnd
		}

		// Perform the edge detection filter.
		imagefilter($this->handle, IMG_FILTER_EDGEDETECT);
	}
}
