<?php
/**
 * @package     T.Platform
 * @subpackage  Cache
 
 */

defined('T_PLATFORM') or die;

//Register the storage class with the loader
TLoader::register('TCacheStorage', dirname(__FILE__) . '/storage.php');


// Almost everything must be public here to allow overloading.

/**
 * Cache base object
 *
 * @package     T.Platform
 * @subpackage  Cache
 * @since       5/2014
 */
class TCache extends TObject
{
	/**
	 * @var    object  Storage handler
	 * @since  5/2014
	 */
	public static $_handler = array();

	/**
	 * @var    array  Options
	 * @since  5/2014
	 */
	public $_options;

	/**
	 * Constructor
	 *
	 * @param   array  $options  options
	 *
	 * @since   5/2014
	 */
	public function __construct($options)
	{
		$conf = T::getConfig();

		$this->_options = array(
			'cachebase' => $conf->get('cache_path', JPATH_CACHE),
			'lifetime' => (int) $conf->get('cachetime'),
			'language' => $conf->get('language', 'en-GB'),
			'storage' => $conf->get('cache_handler', ''),
			'defaultgroup' => 'default',
			'locking' => true,
			'locktime' => 15,
			'checkTime' => true,
			'caching' => ($conf->get('caching') >= 1) ? true : false);

		// Overwrite default options with given options
		foreach ($options as $option => $value)
		{
			if (isset($options[$option]) && $options[$option] !== '')
			{
				$this->_options[$option] = $options[$option];
			}
		}

		if (empty($this->_options['storage']))
		{
			$this->_options['caching'] = false;
		}
	}
	
	/**
	 * Returns a reference to a cache adapter object, always creating it
	 *
	 * @param   string  $type     The cache object type to instantiate
	 * @param   array   $options  The array of options
	 *
	 * @return  TCache  A TCache object
	 *
	 * @since   11.1
	 */
	public static function getInstance($type = 'file', $options = array())
	{
		return TCacheStorage::getInstance($type, $options);
	}
	


	/**
	 * Get the storage handlers
	 *
	 * @return  array    An array of available storage handlers
	 *
	 * @since   5/2014
	 */
	public static function getStores()
	{
		tinclude('core.filesystem.folder');
		$handlers = TFolder::files(dirname(__FILE__) . '/storage', '.php');

		$names = array();
		foreach ($handlers as $handler)
		{
			$name = substr($handler, 0, strrpos($handler, '.'));
			$class = 'TCacheStorage' . $name;

			if (!class_exists($class))
			{
				include_once dirname(__FILE__) . '/storage/' . $name . '.php';
			}

			if (call_user_func_array(array(trim($class), 'test'), array()))
			{
				$names[] = $name;
			}
		}

		return $names;
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
		$this->_options['caching'] = $enabled;
	}

	/**
	 * Get caching state
	 *
	 * @return  boolean  Caching state
	 *
	 * @since   5/2014
	 */
	public function getCaching()
	{
		return $this->_options['caching'];
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
		$this->_options['lifetime'] = $lt;
	}

	/**
	 * Get cached data by id and group
	 *
	 * @param   string  $id     The cache data id
	 * @param   string  $group  The cache data group
	 *
	 * @return  mixed  boolean  False on failure or a cached data string
	 *
	 * @since   5/2014
	 */
	public function get($id, $group = null)
	{
		// Get the default group
		$group = ($group) ? $group : $this->_options['defaultgroup'];

		// Get the storage
		$handler = $this->_getStorage();
		if (!($handler instanceof Exception) && $this->_options['caching'])
		{
			return $handler->get($id, $group, $this->_options['checkTime']);
		}
		return false;
	}

	/**
	 * Get a list of all cached data
	 *
	 * @return  mixed    Boolean false on failure or an object with a list of cache groups and data
	 *
	 * @since   5/2014
	 */
	public function getAll()
	{
		// Get the storage
		$handler = $this->_getStorage();
		if (!($handler instanceof Exception) && $this->_options['caching'])
		{
			return $handler->getAll();
		}
		return false;
	}

	/**
	 * Store the cached data by id and group
	 *
	 * @param   mixed   $data   The data to store
	 * @param   string  $id     The cache data id
	 * @param   string  $group  The cache data group
	 *
	 * @return  boolean  True if cache stored
	 *
	 * @since   5/2014
	 */
	public function store($data, $id, $group = null)
	{
		// Get the default group
		$group = ($group) ? $group : $this->_options['defaultgroup'];

		// Get the storage and store the cached data
		$handler = $this->_getStorage();
		if (!($handler instanceof Exception) && $this->_options['caching'])
		{
			$handler->_lifetime = $this->_options['lifetime'];
			return $handler->store($id, $group, $data);
		}
		return false;
	}

	/**
	 * Remove a cached data entry by id and group
	 *
	 * @param   string  $id     The cache data id
	 * @param   string  $group  The cache data group
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   5/2014
	 */
	public function remove($id, $group = null)
	{
		// Get the default group
		$group = ($group) ? $group : $this->_options['defaultgroup'];

		// Get the storage
		$handler = $this->_getStorage();
		if (!($handler instanceof Exception))
		{
			return $handler->remove($id, $group);
		}
		return false;
	}

	/**
	 * Clean cache for a group given a mode.
	 *
	 * group mode       : cleans all cache in the group
	 * notgroup mode    : cleans all cache not in the group
	 *
	 * @param   string  $group  The cache data group
	 * @param   string  $mode   The mode for cleaning cache [group|notgroup]
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   5/2014
	 */
	public function clean($group = null, $mode = 'group')
	{
		// Get the default group
		$group = ($group) ? $group : $this->_options['defaultgroup'];

		// Get the storage handler
		$handler = $this->_getStorage();
		if (!($handler instanceof Exception))
		{
			return $handler->clean($group, $mode);
		}
		return false;
	}

	/**
	 * Garbage collect expired cache data
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   5/2014
	 */
	public function gc()
	{
		// Get the storage handler
		$handler = $this->_getStorage();
		if (!($handler instanceof Exception))
		{
			return $handler->gc();
		}
		return false;
	}

	/**
	 * Set lock flag on cached item
	 *
	 * @param   string  $id        The cache data id
	 * @param   string  $group     The cache data group
	 * @param   string  $locktime  The default locktime for locking the cache.
	 *
	 * @return  object  Properties are lock and locklooped
	 *
	 * @since   5/2014
	 */
	public function lock($id, $group = null, $locktime = null)
	{
		$returning = new stdClass;
		$returning->locklooped = false;
		// Get the default group
		$group = ($group) ? $group : $this->_options['defaultgroup'];

		// Get the default locktime
		$locktime = ($locktime) ? $locktime : $this->_options['locktime'];

		// Allow storage handlers to perform locking on their own
		// NOTE drivers with lock need also unlock or unlocking will fail because of false $id
		$handler = $this->_getStorage();
		if (!($handler instanceof Exception) && $this->_options['locking'] == true && $this->_options['caching'] == true)
		{
			$locked = $handler->lock($id, $group, $locktime);
			if ($locked !== false)
			{
				return $locked;
			}
		}

		// Fallback
		$curentlifetime = $this->_options['lifetime'];

		// Set lifetime to locktime for storing in children
		$this->_options['lifetime'] = $locktime;

		$looptime = $locktime * 10;
		$id2 = $id . '_lock';

		if ($this->_options['locking'] == true && $this->_options['caching'] == true)
		{
			$data_lock = $this->get($id2, $group);

		}
		else
		{
			$data_lock = false;
			$returning->locked = false;
		}

		if ($data_lock !== false)
		{
			$lock_counter = 0;

			// Loop until you find that the lock has been released.
			// That implies that data get from other thread has finished
			while ($data_lock !== false)
			{

				if ($lock_counter > $looptime)
				{
					$returning->locked = false;
					$returning->locklooped = true;
					break;
				}

				usleep(100);
				$data_lock = $this->get($id2, $group);
				$lock_counter++;
			}
		}

		if ($this->_options['locking'] == true && $this->_options['caching'] == true)
		{
			$returning->locked = $this->store(1, $id2, $group);
		}

		// Revert lifetime to previous one
		$this->_options['lifetime'] = $curentlifetime;

		return $returning;
	}

	/**
	 * Unset lock flag on cached item
	 *
	 * @param   string  $id     The cache data id
	 * @param   string  $group  The cache data group
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   5/2014
	 */
	public function unlock($id, $group = null)
	{
		$unlock = false;
		// Get the default group
		$group = ($group) ? $group : $this->_options['defaultgroup'];

		// Allow handlers to perform unlocking on their own
		$handler = $this->_getStorage();
		if (!($handler instanceof Exception) && $this->_options['caching'])
		{
			$unlocked = $handler->unlock($id, $group);
			if ($unlocked !== false)
			{
				return $unlocked;
			}
		}

		// Fallback
		if ($this->_options['caching'])
		{
			$unlock = $this->remove($id . '_lock', $group);
		}

		return $unlock;
	}

	/**
	 * Get the cache storage handler
	 *
	 * @return  TCacheStorage   A TCacheStorage object
	 *
	 * @since   5/2014
	 */
	public function &_getStorage()
	{
		$hash = md5(serialize($this->_options));

		if (isset(self::$_handler[$hash]))
		{
			return self::$_handler[$hash];
		}

		self::$_handler[$hash] = TCacheStorage::getInstance($this->_options['storage'], $this->_options);

		return self::$_handler[$hash];
	}

	/**
	 * Add a directory where TCache should search for handlers. You may
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
}
