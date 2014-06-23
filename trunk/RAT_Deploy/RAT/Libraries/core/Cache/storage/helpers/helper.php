<?php
/**
 * @package     T.Platform
 * @subpackage  Cache
 */

defined('T_PLATFORM') or die;

/**
 * Cache storage helper functions.
 *
 * @package     T.Platform
 * @subpackage  Cache
 * @since       5/2014
 */
class TCacheStorageHelper
{
	/**
	 * Cache data group
	 *
	 * @var    string
	 * @since  5/2014
	 */
	public $group = '';

	/**
	 * Cached item size
	 *
	 * @var    string
	 * @since  5/2014
	 */
	public $size = 0;

	/**
	 * Counter
	 *
	 * @var    integer
	 * @since  5/2014
	 */
	public $count = 0;

	/**
	 * Constructor
	 *
	 * @param   string  $group  The cache data group
	 *
	 * @since   5/2014
	 */
	public function __construct($group)
	{
		$this->group = $group;
	}

	/**
	 * Increase cache items count.
	 *
	 * @param   string  $size  Cached item size
	 *
	 * @return  void
	 *
	 * @since   5/2014
	 */
	public function updateSize($size)
	{
		$this->size = number_format($this->size + $size, 2, '.', '');
		$this->count++;
	}
}
