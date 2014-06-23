<?php
/**
 * @package     T.Platform
 * @subpackage  Cache
 */

defined('T_PLATFORM') or die;

/**
 * Public cache handler
 *
 * @package     T.Platform
 * @subpackage  Cache
 * @since       5/2014
 */
class TCacheController
{
	/**
	 * @var    TCache
	 * @since  5/2014
	 */
	public $cache;

	/**
	 * @var    array  Array of options
	 * @since  5/2014
	 */
	public $options;

	/**
	 * Constructor
	 *
	 * @param   array  $options  Array of options
	 *
	 * @since   5/2014
	 */
	public function __construct($options)
	{
		$this->cache = new TCache($options);
		$this->options = & $this->cache->_options;

		// Overwrite default options with given options
		foreach ($options as $option => $value)
		{
			if (isset($options[$option]))
			{
				$this->options[$option] = $options[$option];
			}
		}
	}

	/**
	 * Magic method to proxy TCacheControllerMethods
	 *
	 * @param   string  $name       Name of the function
	 * @param   array   $arguments  Array of arguments for the function
	 *
	 * @return  mixed
	 *
	 * @since   5/2014
	 */
	public function __call($name, $arguments)
	{
		$nazaj = call_user_func_array(array($this->cache, $name), $arguments);
		return $nazaj;
	}

	/**
	 * Returns a reference to a cache adapter object, always creating it
	 *
	 * @param   string  $type     The cache object type to instantiate; default is output.
	 * @param   array   $options  Array of options
	 *
	 * @return  TCache  A TCache object
	 *
	 * @since   5/2014
	 */
	public static function getInstance($type = 'output', $options = array())
	{
		TCacheController::addIncludePath(T_PLATFORM . '/core/cache/controller');

		$type = strtolower(preg_replace('/[^A-Z0-9_\.-]/i', '', $type));

		$class = 'TCacheController' . ucfirst($type);

		if (!class_exists($class))
		{
			// Search for the class file in the TCache include paths.
			tinclude('core.filesystem.path');

			if ($path = TPath::find(TCacheController::addIncludePath(), strtolower($type) . '.php'))
			{
				include_once $path;
			}
			else
			{
				TError::raiseError(500, 'Unable to load Cache Controller: ' . $type);
			}
		}

		return new $class($options);
	}

	/**
	 * Set caching enabled state
	 *
	 * @param   boolean  $enabled  True to enable caching
	 *
	 * @return  void
	 *
	 * @since   5/2014
	 */
	public function setCaching($enabled)
	{
		$this->cache->setCaching($enabled);
	}

	/**
	 * Set cache lifetime
	 *
	 * @param   integer  $lt  Cache lifetime
	 *
	 * @return  void
	 *
	 * @since   5/2014
	 */
	public function setLifeTime($lt)
	{
		$this->cache->setLifeTime($lt);
	}

	/**
	 * Add a directory where TCache should search for controllers. You may
	 * either pass a string or an array of directories.
	 *
	 * @param   string  $path  A path to search.
	 *
	 * @return  array   An array with directory elements
	 *
	 * @since   5/2014
	 */
	public static function addIncludePath($path = '')
	{
		static $paths;

		if (!isset($paths))
		{
			$paths = array();
		}
		if (!empty($path) && !in_array($path, $paths))
		{
			tinclude('core.filesystem.path');
			array_unshift($paths, TPath::clean($path));
		}
		return $paths;
	}

	/**
	 * Get stored cached data by id and group
	 *
	 * @param   string  $id     The cache data id
	 * @param   string  $group  The cache data group
	 *
	 * @return  mixed   False on no result, cached object otherwise
	 *
	 * @since   5/2014
	 */
	public function get($id, $group = null)
	{
		$data = false;
		$data = $this->cache->get($id, $group);

		if ($data === false)
		{
			$locktest = new stdClass;
			$locktest->locked = null;
			$locktest->locklooped = null;
			$locktest = $this->cache->lock($id, $group);
			if ($locktest->locked == true && $locktest->locklooped == true)
			{
				$data = $this->cache->get($id, $group);
			}
			if ($locktest->locked == true)
			{
				$this->cache->unlock($id, $group);
			}
		}

		// Check again because we might get it from second attempt
		if ($data !== false)
		{
			$data = unserialize(trim($data)); // trim to fix unserialize errors
		}
		return $data;
	}

	/**
	 * Store data to cache by id and group
	 *
	 * @param   mixed   $data   The data to store
	 * @param   string  $id     The cache data id
	 * @param   string  $group  The cache data group
	 *
	 * @return  boolean  True if cache was stored
	 *
	 * @since   5/2014
	 */
	public function store($data, $id, $group = null)
	{
		$locktest = new stdClass;
		$locktest->locked = null;
		$locktest->locklooped = null;

		$locktest = $this->cache->lock($id, $group);

		if ($locktest->locked == false && $locktest->locklooped == true)
		{
			$locktest = $this->cache->lock($id, $group);
		}

		$sucess = $this->cache->store(serialize($data), $id, $group);

		if ($locktest->locked == true)
		{
			$this->cache->unlock($id, $group);
		}

		return $sucess;
	}
}
