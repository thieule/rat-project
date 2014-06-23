<?php
/**
 * @package     T.Platform
 * @subpackage  Database
 */

defined('T_PLATFORM') or die;

/**
 * Abstract Table class
 *
 * Parent class to all tables.
 *
 * @package     T.Platform
 * @subpackage  Table
 * @since       5/2014
 */
class TTable extends TObject
{
	/**
	 * Name of the database table to model.
	 *
	 * @var    string
	 * @since  5/2014
	 */
	protected $_tbl = '';

	/**
	 * Name of the primary key field in the table.
	 *
	 * @var    string
	 * @since  5/2014
	 */
	protected $_tbl_key = '';

	/**
	 * TDatabase connector object.
	 *
	 * @var    TDatabase
	 * @since  5/2014
	 */
	protected $_db;

	/**
	 * Should rows be tracked as ACL assets?
	 *
	 * @var    boolean
	 * @since  5/2014
	 */
	protected $_trackAssets = false;



	/**
	 * Indicator that the tables have been locked.
	 *
	 * @var    boolean
	 * @since  5/2014
	 */
	protected $_locked = false;

	/**
	 * Object constructor to set table and key fields.  In most cases this will
	 * be overridden by child classes to explicitly set the table and key fields
	 * for a particular database table.
	 *
	 * @param   string     $table  Name of the table to model.
	 * @param   string     $key    Name of the primary key field in the table.
	 * @param   TDatabase  &$db    TDatabase connector object.
	 *
	 * @since   5/2014
	 */
	public function __construct($table, $key, &$db)
	{
            $config=T::getConfig();
            
		// Set internal variables.
		$this->_tbl = $config->dbprefix.$table;
		$this->_tbl_key = $key;
		$this->_db = &$db;

		// Initialise the table properties.
		if ($fields = $this->getFields())
		{
			foreach ($fields as $name => $v)
			{
				
                            $this->$name = null;
				
			}
		}

	}

	/**
	 * Get the columns from database table.
	 *
	 * @return  mixed  An array of the field names, or false if an error occurs.
	 *
	 * @since   5/2014
	 */
	public function getFields()
	{
		static $cache = null;

		if ($cache === null)
		{
			// Lookup the fields for this table only once.
			$name = $this->_tbl;
			$fields = $this->_db->getTableColumns($name, false);

			if (empty($fields))
			{
				$e = new TException(Text::_('JLIB_DATABASE_ERROR_COLUMNS_NOT_FOUND'));
				$this->setError($e);
				return false;
			}
			$cache = $fields;
		}

		return $cache;
	}

	/**
	 * Static method to get an instance of a TTable class if it can be found in
	 * the table include paths.  To add include paths for searching for TTable
	 * classes @see TTable::addIncludePath().
	 *
	 * @param   string  $type    The type (name) of the TTable class to get an instance of.
	 * @param   string  $prefix  An optional prefix for the table class name.
	 * @param   array   $config  An optional array of configuration values for the TTable object.
	 *
	 * @return  mixed    A TTable object if found or boolean false if one could not be found.
	 *
	 */
	public static function getInstance($type, $prefix = 'TTable', $config = array())
	{
		// Sanitize and prepare the table class name.
		$type = preg_replace('/[^A-Z0-9_\.-]/i', '', $type);
		$tableClass = $prefix . ucfirst($type);

		// Only try to load the class if it doesn't already exist.
		if (!class_exists($tableClass))
		{
			
		}

		// If a database object was passed in the configuration array use it, otherwise get the global one from T.
		$db = isset($config['dbo']) ? $config['dbo'] : T::getDbo();

		// Instantiate a new table class and return it.
		return new $tableClass($db);
	}

	


	/**
	 * Method to get the database table name for the class.
	 *
	 * @return  string  The name of the database table being modeled.
	 *
	 * @since   5/2014
	 *
	 */
	public function getTableName()
	{
		return $this->_tbl;
	}

	/**
	 * Method to get the primary key field name for the table.
	 *
	 * @return  string  The name of the primary key for the table.
	 *
	 * @since   5/2014
	 */
	public function getKeyName()
	{
		return $this->_tbl_key;
	}

	/**
	 * Method to get the TDatabase connector object.
	 *
	 * @return  TDatabase  The internal database connector object.
	 *
	 * @since   5/2014
	 */
	public function getDbo()
	{
		return $this->_db;
	}

	/**
	 * Method to set the TDatabase connector object.
	 *
	 * @param   object  &$db  A TDatabase connector object to be used by the table object.
	 *
	 * @return  boolean  True on success.
	 * @since   5/2014
	 */
	public function setDBO(&$db)
	{
		// Make sure the new database object is a TDatabase.
		if (!($db instanceof TDatabase))
		{
			return false;
		}

		$this->_db = &$db;

		return true;
	}



	/**
	 * Method to reset class properties to the defaults set in the class
	 * definition. It will ignore the primary key as well as any private class
	 * properties.
	 *
	 * @return  void
	 *
	 * @since   5/2014
	 */
	public function reset()
	{
		// Get the default values for the class from the table.
		foreach ($this->getFields() as $k => $v)
		{
			// If the property is not the primary key or private, reset it.
			if ($k != $this->_tbl_key && (strpos($k, '_') !== 0))
			{
				$this->$k = $v->Default;
			}
		}
	}

	/**
	 * Method to bind an associative array or object to the TTable instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed  $src     An associative array or object to bind to the TTable instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 * @since   5/2014
	 */
	public function bind($src, $ignore = array())
	{
		// If the source value is not an array or object return false.
		if (!is_object($src) && !is_array($src))
		{
			$e = new TException(Text::sprintf('JLIB_DATABASE_ERROR_BIND_FAILED_INVALID_SOURCE_ARGUMENT', get_class($this)));
			$this->setError($e);
			return false;
		}

		// If the source value is an object, get its accessible properties.
		if (is_object($src))
		{
			$src = get_object_vars($src);
		}

		// If the ignore value is a string, explode it over spaces.
		if (!is_array($ignore))
		{
			$ignore = explode(' ', $ignore);
		}

		// Bind the source value, excluding the ignored fields.
		foreach ($this->getProperties() as $k => $v)
		{
			// Only process fields not in the ignore array.
			if (!in_array($k, $ignore))
			{
				if (isset($src[$k]))
				{
					$this->$k = $src[$k];
				}
			}
		}

		return true;
	}

	/**
	 * Method to load a row from the database by primary key and bind the fields
	 * to the TTable instance properties.
	 *
	 * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.  If not
	 * set the instance property value is used.
	 * @param   boolean  $reset  True to reset the default values before loading the new row.
	 *
	 * @return  boolean  True if successful. False if row not found or on error (internal error state set in that case).
	 *
	 * @since   5/2014
	 */
	public function load($keys = null, $reset = true)
	{
		if (empty($keys))
		{
			// If empty, use the value of the current key
			$keyName = $this->_tbl_key;
			$keyValue = $this->$keyName;

			// If empty primary key there's is no need to load anything
			if (empty($keyValue))
			{
				return true;
			}

			$keys = array($keyName => $keyValue);
		}
		elseif (!is_array($keys))
		{
			// Load by primary key.
			$keys = array($this->_tbl_key => $keys);
		}

		if ($reset)
		{
			$this->reset();
		}
                
		// Initialise the query.
		$query = $this->_db->getQuery(true);
		$query->select('*');
		$query->from($this->_tbl);
		$fields = array_keys($this->getProperties());
                
		foreach ($keys as $field => $value)
		{
			// Check that $field is in the table.
			if (!in_array($field, $fields))
			{
				$e = new TException(Text::sprintf('JLIB_DATABASE_ERROR_CLASS_IS_MISSING_FIELD', get_class($this), $field));
				$this->setError($e);
				return false;
			}
			// Add the search tuple to the query.
			$query->where($this->_db->quoteName($field) . ' = ' . $this->_db->quote($value));
		}

		$this->_db->setQuery($query);

		try
		{
			$row = $this->_db->loadAssoc();
		}
		catch (RuntimeException $e)
		{
			$je = new TException($e->getMessage());
			$this->setError($je);
			return false;
		}

		// Legacy error handling switch based on the TError::$legacy switch.
		// @deprecated  12.1
		if (TError::$legacy && $this->_db->getErrorNum())
		{
			$e = new TException($this->_db->getErrorMsg());
			$this->setError($e);
			return false;
		}

		// Check that we have a result.
		if (empty($row))
		{
			$e = new TException(Text::_('JLIB_DATABASE_ERROR_EMPTY_ROW_RETURNED'));
			$this->setError($e);
			return false;
		}

		// Bind the object with the row and return.
		return $this->bind($row);
	}

	/**
	 * Method to perform sanity checks on the TTable instance properties to ensure
	 * they are safe to store in the database.  Child classes should override this
	 * method to make sure the data they are storing in the database is safe and
	 * as expected before storage.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 *
	 * @since   5/2014
	 */
	public function check()
	{
		return true;
	}

	/**
	 * Method to store a row in the database from the TTable instance properties.
	 * If a primary key value is set the row with that primary key value will be
	 * updated with the instance property values.  If no primary key value is set
	 * a new row will be inserted into the database with the properties from the
	 * TTable instance.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   5/2014
	 */
	public function store($updateNulls = false)
	{
		// Initialise variables.
		$k = $this->_tbl_key;
		

		// If a primary key exists update the object, otherwise insert it.
		if ($this->$k)
		{
			$stored = $this->_db->updateObject($this->_tbl, $this, $this->_tbl_key, $updateNulls);
		}
		else
		{
			$stored = $this->_db->insertObject($this->_tbl, $this, $this->_tbl_key);
		}

		// If the store failed return false.
		if (!$stored)
		{
			$e = new TException(Text::sprintf('JLIB_DATABASE_ERROR_STORE_FAILED', get_class($this), $this->_db->getErrorMsg()));
			$this->setError($e);
			return false;
		}

	

		if ($this->_locked)
		{
			$this->_unlock();
		}

		return true;
	}

	/**
	 * Method to provide a shortcut to binding, checking and storing a TTable
	 * instance to the database table.  The method will check a row in once the
	 * data has been stored and if an ordering filter is present will attempt to
	 * reorder the table rows based on the filter.  The ordering filter is an instance
	 * property name.  The rows that will be reordered are those whose value matches
	 * the TTable instance for the property specified.
	 *
	 * @param   mixed   $src             An associative array or object to bind to the TTable instance.
	 * @param   string  $orderingFilter  Filter for the order updating
	 * @param   mixed   $ignore          An optional array or space separated list of properties
	 * to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 * @since   5/2014
	 */
	public function save($src, $orderingFilter = '', $ignore = '')
	{
		// Attempt to bind the source to the instance.
		if (!$this->bind($src, $ignore))
		{
			return false;
		}

		// Run any sanity checks on the instance and verify that it is ready for storage.
		if (!$this->check())
		{
			return false;
		}

		// Attempt to store the properties to the database table.
		if (!$this->store())
		{
			return false;
		}


		// If an ordering filter is set, attempt reorder the rows in the table based on the filter and value.
		if ($orderingFilter)
		{
			$filterValue = $this->$orderingFilter;
			$this->reorder($orderingFilter ? $this->_db->quoteName($orderingFilter) . ' = ' . $this->_db->Quote($filterValue) : '');
		}

		// Set the error to empty and return true.
		$this->setError('');

		return true;
	}

	/**
	 * Method to delete a row from the database table by primary key value.
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 * @since   5/2014
	 */
	public function delete($pk = null)
	{
		// Initialise variables.
		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// If no primary key is given, return false.
		if ($pk === null)
		{
			$e = new TException(Text::_('JLIB_DATABASE_ERROR_NULL_PRIMARY_KEY'));
			$this->setError($e);
			return false;
		}

	

		// Delete the row by primary key.
		$query = $this->_db->getQuery(true);
		$query->delete();
		$query->from($this->_tbl);
		$query->where($this->_tbl_key . ' = ' . $this->_db->quote($pk));
		$this->_db->setQuery($query);

		// Check for a database error.
		if (!$this->_db->execute())
		{
			$e = new TException(Text::sprintf('JLIB_DATABASE_ERROR_DELETE_FAILED', get_class($this), $this->_db->getErrorMsg()));
			$this->setError($e);
			return false;
		}

		return true;
	}


	/**
	 * Method to export the TTable instance properties to an XML string.
	 *
	 * @param   boolean  $mapKeysToText  True to map foreign keys to text values.
	 *
	 * @return  string   XML string representation of the instance.
	 
	 * @since   5/2014
	 */
	public function toXML($mapKeysToText = false)
	{
		// Deprecation warning.
		TLog::add('TTable::toXML() is deprecated.', TLog::WARNING, 'deprecated');

		// Initialise variables.
		$xml = array();
		$map = $mapKeysToText ? ' mapkeystotext="true"' : '';

		// Open root node.
		$xml[] = '<record table="' . $this->_tbl . '"' . $map . '>';

		// Get the publicly accessible instance properties.
		foreach (get_object_vars($this) as $k => $v)
		{
			// If the value is null or non-scalar, or the field is internal ignore it.
			if (!is_scalar($v) || ($v === null) || ($k[0] == '_'))
			{
				continue;
			}

			$xml[] = '	<' . $k . '><![CDATA[' . $v . ']]></' . $k . '>';
		}

		// Close root node.
		$xml[] = '</record>';

		// Return the XML array imploded over new lines.
		return implode("\n", $xml);
	}

	/**
	 * Method to lock the database table for writing.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   5/2014
	 * @throws  TDatabaseException
	 */
	protected function _lock()
	{
		$this->_db->lockTable($this->_tbl);
		$this->_locked = true;

		return true;
	}

	/**
	 * Method to unlock the database table for writing.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   5/2014
	 */
	protected function _unlock()
	{
		$this->_db->unlockTables();
		$this->_locked = false;

		return true;
	}
}
