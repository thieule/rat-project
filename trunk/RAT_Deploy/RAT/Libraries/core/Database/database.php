<?php
/**
 * @package     T.Platform
 * @subpackage  Database
 */

defined('T_PLATFORM') or die;

tinclude('core.filesystem.folder');
tinclude('core.database.query');


/**
 * Database interface class.
 *
 * @package     T.Platform
 * @subpackage  Database
 * @since       
 */
interface TDatabaseInterface
{
	/**
	 * Test to see if the connector is available.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   
	 */
	public static function test();
}

/**
 * Database connector class.
 *
 * @package     T.Platform
 * @subpackage  Database
 * @since       5/2014
 */
abstract class TDatabase implements TDatabaseInterface
{
	/**
	 * The name of the database.
	 *
	 * @var    string
	 * @since  5/2014
	 */
	private $_database;

	/**
	 * The name of the database driver.
	 *
	 * @var    string
	 * @since  5/2014
	 */
	public $name;

	/**
	 * @var    resource  The database connection resource.
	 * @since  5/2014
	 */
	protected $connection;

	/**
	 * @var    integer  The number of SQL statements executed by the database driver.
	 * @since  5/2014
	 */
	protected $count = 0;

	/**
	 * @var    resource  The database connection cursor from the last query.
	 * @since  5/2014
	 */
	protected $cursor;

	/**
	 * @var    boolean  The database driver debugging state.
	 * @since  5/2014
	 */
	protected $debug = false;

	/**
	 * @var    integer  The affected row limit for the current SQL statement.
	 * @since  5/2014
	 */
	protected $limit = 0;

	/**
	 * @var    array  The log of executed SQL statements by the database driver.
	 * @since  5/2014
	 */
	protected $log = array();

	/**
	 * @var    string  The character(s) used to quote SQL statement names such as table names or field names,
	 *                 etc.  The child classes should define this as necessary.  If a single character string the
	 *                 same character is used for both sides of the quoted name, else the first character will be
	 *                 used for the opening quote and the second for the closing quote.
	 * @since  5/2014
	 */
	protected $nameQuote;

	/**
	 * @var    string  The null or zero representation of a timestamp for the database driver.  This should be
	 *                 defined in child classes to hold the appropriate value for the engine.
	 * @since  5/2014
	 */
	protected $nullDate;

	/**
	 * @var    integer  The affected row offset to apply for the current SQL statement.
	 * @since  5/2014
	 */
	protected $offset = 0;

	/**
	 * @var    mixed  The current SQL statement to execute.
	 * @since  5/2014
	 */
	protected $sql;

	/**
	 * @var    string  The common database table prefix.
	 * @since  5/2014
	 */
	protected $tablePrefix;

	/**
	 * @var    boolean  True if the database engine supports UTF-8 character encoding.
	 * @since  5/2014
	 */
	protected $utf = true;

	/**
	 * @var         integer  The database error number
	 * @since       5/2014
	 */
	protected $errorNum = 0;

	/**
	 * @var         string  The database error message
	 * @since       5/2014
	 */
	protected $errorMsg;

	/**
	 * @var         boolean  If true then there are fields to be quoted for the query.
	 * @since       5/2014
	 */
	protected $hasQuoted = false;

	/**
	 * @var         array  The fields that are to be quoted.
	 * @since       5/2014
	 */
	protected $quoted = array();

	/**
	 * @var    array  TDatabase instances container.
	 * @since  5/2014
	 */
	protected static $instances = array();

	/**
	 * @var    string  The minimum supported database version.
	 * @since  5/2014
	 */
	protected $dbMinimum;

	/**
	 * Get a list of available database connectors.  The list will only be populated with connectors that both
	 * the class exists and the static test method returns true.  This gives us the ability to have a multitude
	 * of connector classes that are self-aware as to whether or not they are able to be used on a given system.
	 *
	 * @return  array  An array of available database connectors.
	 *
	 * @since   5/2014
	 */
	public static function getConnectors()
	{
		// Instantiate variables.
		$connectors = array();

		// Get a list of types.
		$types = TFolder::files(dirname(__FILE__) . '/database');

		// Loop through the types and find the ones that are available.
		foreach ($types as $type)
		{
			// Ignore some files.
			if (($type == 'index.html') || stripos($type, 'importer') || stripos($type, 'exporter') || stripos($type, 'query') || stripos($type, 'exception'))
			{
				continue;
			}

			// Derive the class name from the type.
			$class = str_ireplace(array('.php', 'sql'), array('', 'SQL'), 'TDatabase' . ucfirst(trim($type)));

			// If the class doesn't exist, let's look for it and register it.
			if (!class_exists($class))
			{
				// Derive the file path for the driver class.
				$path = dirname(__FILE__) . '/database/' . $type;

				// If the file exists register the class with our class loader.
				if (file_exists($path))
				{
					TLoader::register($class, $path);
				}
				// If it doesn't exist we are at an impasse so move on to the next type.
				else
				{
					continue;
				}
			}

			// If the class still doesn't exist we have nothing left to do but look at the next type.  We did our best.
			if (!class_exists($class))
			{
				continue;
			}

			// Sweet!  Our class exists, so now we just need to know if it passes it's test method.
			if (call_user_func_array(array($class, 'test'), array()))
			{
				// Connector names should not have file extensions.
				$connectors[] = str_ireplace('.php', '', $type);
			}
		}

		return $connectors;
	}

	/**
	 * Method to return a TDatabase instance based on the given options.  There are three global options and then
	 * the rest are specific to the database driver.  The 'driver' option defines which TDatabaseDriver class is
	 * used for the connection -- the default is 'mysql'.  The 'database' option determines which database is to
	 * be used for the connection.  The 'select' option determines whether the connector should automatically select
	 * the chosen database.
	 *
	 * Instances are unique to the given options and new objects are only created when a unique options array is
	 * passed into the method.  This ensures that we don't end up with unnecessary database connection resources.
	 *
	 * @param   array  $options  Parameters to be passed to the database driver.
	 *
	 * @return  TDatabase  A database object.
	 *
	 * @since   5/2014
	 */
	public static function getInstance($options = array())
	{
		// Sanitize the database connector options.
		$options['driver'] = (isset($options['driver'])) ? preg_replace('/[^A-Z0-9_\.-]/i', '', $options['driver']) : 'mysql';
		$options['database'] = (isset($options['database'])) ? $options['database'] : null;
		$options['select'] = (isset($options['select'])) ? $options['select'] : true;

		// Get the options signature for the database connector.
		$signature = md5(serialize($options));

		// If we already have a database connector instance for these options then just use that.
		if (empty(self::$instances[$signature]))
		{

			// Derive the class name from the driver.
			$class = 'TDatabase' . ucfirst($options['driver']);

			// If the class doesn't exist, let's look for it and register it.
			if (!class_exists($class))
			{

				// Derive the file path for the driver class.
				$path = dirname(__FILE__) . '/database/' . $options['driver'] . '.php';

				// If the file exists register the class with our class loader.
				if (file_exists($path))
				{
					TLoader::register($class, $path);
				}
				// If it doesn't exist we are at an impasse so throw an exception.
				else
				{

					// Legacy error handling switch based on the TError::$legacy switch.
			

					if (TError::$legacy)
					{
						TError::setErrorHandling(E_ERROR, 'die');
						return TError::raiseError(500, Text::sprintf('JLIB_DATABASE_ERROR_LOAD_DATABASE_DRIVER', $options['driver']));
					}
					else
					{
						throw new TDatabaseException(Text::sprintf('JLIB_DATABASE_ERROR_LOAD_DATABASE_DRIVER', $options['driver']));
					}
				}
			}

			// If the class still doesn't exist we have nothing left to do but throw an exception.  We did our best.
			if (!class_exists($class))
			{

				// Legacy error handling switch based on the TError::$legacy switch.

				if (TError::$legacy)
				{
					TError::setErrorHandling(E_ERROR, 'die');
					return TError::raiseError(500, Text::sprintf('JLIB_DATABASE_ERROR_LOAD_DATABASE_DRIVER', $options['driver']));
				}
				else
				{
					throw new TDatabaseException(Text::sprintf('JLIB_DATABASE_ERROR_LOAD_DATABASE_DRIVER', $options['driver']));
				}
			}

			// Create our new TDatabase connector based on the options given.
			try
			{
				$instance = new $class($options);
			}
			catch (TDatabaseException $e)
			{

				// Legacy error handling switch based on the TError::$legacy switch.
				// @deprecated  12.1

				if (TError::$legacy)
				{
					TError::setErrorHandling(E_ERROR, 'ignore');
					return TError::raiseError(500, Text::sprintf('JLIB_DATABASE_ERROR_CONNECT_DATABASE', $e->getMessage()));
				}
				else
				{
					throw new TDatabaseException(Text::sprintf('JLIB_DATABASE_ERROR_CONNECT_DATABASE', $e->getMessage()));
				}
			}

			// Set the new connector to the global instances based on signature.
			self::$instances[$signature] = $instance;
		}

		return self::$instances[$signature];
	}

	/**
	 * Splits a string of multiple queries into an array of individual queries.
	 *
	 * @param   string  $sql  Input SQL string with which to split into individual queries.
	 *
	 * @return  array  The queries from the input string separated into an array.
	 *
	 * @since   5/2014
	 */
	public static function splitSql($sql)
	{
		$start = 0;
		$open = false;
		$char = '';
		$end = strlen($sql);
		$queries = array();

		for ($i = 0; $i < $end; $i++)
		{
			$current = substr($sql, $i, 1);
			if (($current == '"' || $current == '\''))
			{
				$n = 2;

				while (substr($sql, $i - $n + 1, 1) == '\\' && $n < $i)
				{
					$n++;
				}

				if ($n % 2 == 0)
				{
					if ($open)
					{
						if ($current == $char)
						{
							$open = false;
							$char = '';
						}
					}
					else
					{
						$open = true;
						$char = $current;
					}
				}
			}

			if (($current == ';' && !$open) || $i == $end - 1)
			{
				$queries[] = substr($sql, $start, ($i - $start + 1));
				$start = $i + 1;
			}
		}

		return $queries;
	}

	/**
	 * Magic method to provide method alias support for quote() and quoteName().
	 *
	 * @param   string  $method  The called method.
	 * @param   array   $args    The array of arguments passed to the method.
	 *
	 * @return  string  The aliased method's return value or null.
	 *
	 * @since   5/2014
	 */
	public function __call($method, $args)
	{
		if (empty($args))
		{
			return;
		}

		switch ($method)
		{
			case 'q':
				return $this->quote($args[0], isset($args[1]) ? $args[1] : true);
				break;
			case 'nq':
			case 'qn':
				return $this->quoteName($args[0], isset($args[1]) ? $args[1] : null);
				break;
		}
	}

	/**
	 * Constructor.
	 *
	 * @param   array  $options  List of options used to configure the connection
	 *
	 * @since   5/2014
	 */
	protected function __construct($options)
	{
		// Initialise object variables.
		$this->_database = (isset($options['database'])) ? $options['database'] : '';

		$this->tablePrefix = (isset($options['prefix'])) ? $options['prefix'] : 't_';
		$this->count = 0;
		$this->errorNum = 0;
		$this->log = array();
		$this->quoted = array();
		$this->hasQuoted = false;

		// Set charactersets (needed for MySQL 4.1.2+).
		$this->setUTF();
	}

	/**
	 * Adds a field or array of field names to the list that are to be quoted.
	 *
	 * @param   mixed  $quoted  Field name or array of names.
	 *
	 * @return  void
	 *
	 * @deprecated  12.1
	 * @since   5/2014
	 */
	public function addQuoted($quoted)
	{
		if (is_string($quoted))
		{
			$this->quoted[] = $quoted;
		}
		else
		{
			$this->quoted = array_merge($this->quoted, (array) $quoted);
		}

		$this->hasQuoted = true;
	}

	/**
	 * Determines if the connection to the server is active.
	 *
	 * @return  boolean  True if connected to the database engine.
	 *
	 * @since   5/2014
	 */
	abstract public function connected();

	/**
	 * Drops a table from the database.
	 *
	 * @param   string   $table     The name of the database table to drop.
	 * @param   boolean  $ifExists  Optionally specify that the table must exist before it is dropped.
	 *
	 * @return  TDatabase  Returns this object to support chaining.
	 *
	 * @since   11.4
	 * @throws  TDatabaseException
	 */
	public abstract function dropTable($table, $ifExists = true);

	/**
	 * Method to escape a string for usage in an SQL statement.
	 *
	 * @param   string   $text   The string to be escaped.
	 * @param   boolean  $extra  Optional parameter to provide extra escaping.
	 *
	 * @return  string   The escaped string.
	 *
	 * @since   5/2014
	 */
	abstract public function escape($text, $extra = false);

	/**
	 * Method to fetch a row from the result set cursor as an array.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  mixed  Either the next row from the result set or false if there are no more rows.
	 *
	 * @since   5/2014
	 */
	abstract protected function fetchArray($cursor = null);

	/**
	 * Method to fetch a row from the result set cursor as an associative array.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  mixed  Either the next row from the result set or false if there are no more rows.
	 *
	 * @since   5/2014
	 */
	abstract protected function fetchAssoc($cursor = null);

	/**
	 * Method to fetch a row from the result set cursor as an object.
	 *
	 * @param   mixed   $cursor  The optional result set cursor from which to fetch the row.
	 * @param   string  $class   The class name to use for the returned row object.
	 *
	 * @return  mixed   Either the next row from the result set or false if there are no more rows.
	 *
	 * @since   5/2014
	 */
	abstract protected function fetchObject($cursor = null, $class = 'stdClass');

	/**
	 * Method to free up the memory used for the result set.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  void
	 *
	 * @since   5/2014
	 */
	abstract protected function freeResult($cursor = null);

	/**
	 * Get the number of affected rows for the previous executed SQL statement.
	 *
	 * @return  integer  The number of affected rows.
	 *
	 * @since   5/2014
	 */
	abstract public function getAffectedRows();

	
	/**
	 * Method that provides access to the underlying database connection. Useful for when you need to call a
	 * proprietary method such as postgresql's lo_* methods.
	 *
	 * @return  resource  The underlying database connection resource.
	 *
	 * @since   5/2014
	 */
	public function getConnection()
	{
		return $this->connection;
	}

	/**
	 * Get the total number of SQL statements executed by the database driver.
	 *
	 * @return  integer
	 *
	 * @since   5/2014
	 */
	public function getCount()
	{
		return $this->count;
	}

	/**
	 * Gets the name of the database used by this conneciton.
	 *
	 * @return  string
	 *
	 * @since   11.4
	 */
	protected function getDatabase()
	{
		return $this->_database;
	}

	/**
	 * Returns a PHP date() function compliant date format for the database driver.
	 *
	 * @return  string  The format string.
	 *
	 * @since   5/2014
	 */
	public function getDateFormat()
	{
		return 'Y-m-d H:i:s';
	}

	/**
	 * Get the row limit for the current SQL statement.
	 *
	 * @return  integer  The affected row limit for the current SQL statement.
	 *
	 * @since   2.5.17 (CMS Only)
	 * @deprecated  3.0  Required for SQLSRV support for 2.5 only
	 */
	public function getLimit()
	{
		return $this->limit;
	}

	/**
	 * Get the database driver SQL statement log.
	 *
	 * @return  array  SQL statements executed by the database driver.
	 *
	 * @since   5/2014
	 */
	public function getLog()
	{
		return $this->log;
	}

	/**
	 * Get the minimum supported database version.
	 *
	 * @return  string  The minimum version number for the database driver.
	 *
	 * @since   12.1
	 */
	public function getMinimum()
	{
		return $this->dbMinimum;
	}

	/**
	 * Get the null or zero representation of a timestamp for the database driver.
	 *
	 * @return  string  Null or zero representation of a timestamp.
	 *
	 * @since   5/2014
	 */
	public function getNullDate()
	{
		return $this->nullDate;
	}

	/**
	 * Get the number of returned rows for the previous executed SQL statement.
	 *
	 * @param   resource  $cursor  An optional database cursor resource to extract the row count from.
	 *
	 * @return  integer   The number of returned rows.
	 *
	 * @since   5/2014
	 */
	abstract public function getNumRows($cursor = null);

	/**
	 * Get the row offset for the current SQL statement.
	 *
	 * @return  integer  The affected row offset to apply for the current SQL statement.
	 *
	 * @since   2.5.17 (CMS Only)
	 * @deprecated  3.0  Required for SQLSRV support for 2.5 only
	 */
	public function getOffset()
	{
		return $this->offset;
	}

	/**
	 * Get the common table prefix for the database driver.
	 *
	 * @return  string  The common database table prefix.
	 *
	 * @since   5/2014
	 */
	public function getPrefix()
	{
		return $this->tablePrefix;
	}

	/**
	 * Get the current query object or a new TDatabaseQuery object.
	 *
	 * @param   boolean  $new  False to return the current query object, True to return a new TDatabaseQuery object.
	 *
	 * @return  TDatabaseQuery  The current query object or a new object extending the TDatabaseQuery class.
	 *
	 * @since   5/2014
	 * @throws  TDatabaseException
	 */
	abstract public function getQuery($new = false);

	/**
	 * Retrieves field information about the given tables.
	 *
	 * @param   string   $table     The name of the database table.
	 * @param   boolean  $typeOnly  True (default) to only return field types.
	 *
	 * @return  array  An array of fields by table.
	 *
	 * @since   5/2014
	 * @throws  TDatabaseException
	 */
	abstract public function getTableColumns($table, $typeOnly = true);

	/**
	 * Shows the table CREATE statement that creates the given tables.
	 *
	 * @param   mixed  $tables  A table name or a list of table names.
	 *
	 * @return  array  A list of the create SQL for the tables.
	 *
	 * @since   5/2014
	 * @throws  TDatabaseException
	 */
	abstract public function getTableCreate($tables);

	/**
	 * Retrieves field information about the given tables.
	 *
	 * @param   mixed  $tables  A table name or a list of table names.
	 *
	 * @return  array  An array of keys for the table(s).
	 *
	 * @since   5/2014
	 * @throws  TDatabaseException
	 */
	abstract public function getTableKeys($tables);

	/**
	 * Method to get an array of all tables in the database.
	 *
	 * @return  array  An array of all the tables in the database.
	 *
	 * @since   5/2014
	 * @throws  TDatabaseException
	 */
	abstract public function getTableList();

	/**
	 * Determine whether or not the database engine supports UTF-8 character encoding.
	 *
	 * @return  boolean  True if the database engine supports UTF-8 character encoding.
	 *
	 * @since   5/2014
	 */
	public function getUTFSupport()
	{
		return $this->utf;
	}

	/**
	 * Get the version of the database connector
	 *
	 * @return  string  The database connector version.
	 *
	 * @since   5/2014
	 */
	abstract public function getVersion();

	/**
	 * Determines if the database engine supports UTF-8 character encoding.
	 *
	 * @return  boolean  True if supported.
	 *
	 * @since   5/2014
	 *
	 * @deprecated  12.1
	 */
	abstract public function hasUTF();

	/**
	 * Method to get the auto-incremented value from the last INSERT statement.
	 *
	 * @return  integer  The value of the auto-increment field from the last inserted row.
	 *
	 * @since   5/2014
	 */
	abstract public function insertid();

	/**
	 * Inserts a row into a table based on an object's properties.
	 *
	 * @param   string  $table    The name of the database table to insert into.
	 * @param   object  &$object  A reference to an object whose public properties match the table fields.
	 * @param   string  $key      The name of the primary key. If provided the object property is updated.
	 *
	 * @return  boolean    True on success.
	 *
	 * @since   5/2014
	 * @throws  TDatabaseException
	 */
	public function insertObject($table, &$object, $key = null)
	{
		// Initialise variables.
		$fields = array();
		$values = array();

		// Create the base insert statement.
		$statement = 'INSERT INTO ' . $this->quoteName($table) . ' (%s) VALUES (%s)';

		// Iterate over the object variables to build the query fields and values.
		foreach (get_object_vars($object) as $k => $v)
		{
			// Only process non-null scalars.
			if (is_array($v) or is_object($v) or $v === null)
			{
				continue;
			}

			// Ignore any internal fields.
			if ($k[0] == '_')
			{
				continue;
			}

			// Prepare and sanitize the fields and values for the database query.
			$fields[] = $this->quoteName($k);
			$values[] = $this->quote($v);
		}

		// Set the query and execute the insert.
		$this->setQuery(sprintf($statement, implode(',', $fields), implode(',', $values)));
		if (!$this->execute())
		{
			return false;
		}

		// Update the primary key if it exists.
		$id = $this->insertid();
		if ($key && $id)
		{
			$object->$key = $id;
		}

		return true;
	}

	/**
	 * Method to check whether the installed database version is supported by the database driver
	 *
	 * @return  boolean  True if the database version is supported
	 *
	 * @since   12.1
	 */
	public function isMinimumVersion()
	{
		return version_compare($this->getVersion(), $this->dbMinimum) >= 0;
	}

	/**
	 * Method to get the first row of the result set from the database query as an associative array
	 * of ['field_name' => 'row_value'].
	 *
	 * @return  mixed  The return value or null if the query failed.
	 *
	 * @since   5/2014
	 * @throws  TDatabaseException
	 */
	public function loadAssoc()
	{
		// Initialise variables.
		$ret = null;

		// Execute the query and get the result set cursor.
		if (!($cursor = $this->execute()))
		{
			return null;
		}

		// Get the first row from the result set as an associative array.
		if ($array = $this->fetchAssoc($cursor))
		{
			$ret = $array;
		}

		// Free up system resources and return.
		$this->freeResult($cursor);

		return $ret;
	}

	/**
	 * Method to get an array of the result set rows from the database query where each row is an associative array
	 * of ['field_name' => 'row_value'].  The array of rows can optionally be keyed by a field name, but defaults to
	 * a sequential numeric array.
	 *
	 * NOTE: Chosing to key the result array by a non-unique field name can result in unwanted
	 * behavior and should be avoided.
	 *
	 * @param   string  $key     The name of a field on which to key the result array.
	 * @param   string  $column  An optional column name. Instead of the whole row, only this column value will be in
	 * the result array.
	 *
	 * @return  mixed   The return value or null if the query failed.
	 *
	 * @since   5/2014
	 * @throws  TDatabaseException
	 */
	public function loadAssocList($key = null, $column = null)
	{
		// Initialise variables.
		$array = array();

		// Execute the query and get the result set cursor.
		if (!($cursor = $this->execute()))
		{
			return null;
		}

		// Get all of the rows from the result set.
		while ($row = $this->fetchAssoc($cursor))
		{
			$value = ($column) ? (isset($row[$column]) ? $row[$column] : $row) : $row;
			if ($key)
			{
				$array[$row[$key]] = $value;
			}
			else
			{
				$array[] = $value;
			}
		}

		// Free up system resources and return.
		$this->freeResult($cursor);

		return $array;
	}

	/**
	 * Method to get an array of values from the <var>$offset</var> field in each row of the result set from
	 * the database query.
	 *
	 * @param   integer  $offset  The row offset to use to build the result array.
	 *
	 * @return  mixed    The return value or null if the query failed.
	 *
	 * @since   5/2014
	 * @throws  TDatabaseException
	 */
	public function loadColumn($offset = 0)
	{
		// Initialise variables.
		$array = array();

		// Execute the query and get the result set cursor.
		if (!($cursor = $this->execute()))
		{
			return null;
		}

		// Get all of the rows from the result set as arrays.
		while ($row = $this->fetchArray($cursor))
		{
			$array[] = $row[$offset];
		}

		// Free up system resources and return.
		$this->freeResult($cursor);

		return $array;
	}

	/**
	 * Method to get the next row in the result set from the database query as an object.
	 *
	 * @param   string  $class  The class name to use for the returned row object.
	 *
	 * @return  mixed   The result of the query as an array, false if there are no more rows.
	 *
	 * @since   5/2014
	 * @throws  TDatabaseException
	 */
	public function loadNextObject($class = 'stdClass')
	{
		static $cursor;

		// Execute the query and get the result set cursor.
		if (!($cursor = $this->execute()))
		{
			return $this->errorNum ? null : false;
		}

		// Get the next row from the result set as an object of type $class.
		if ($row = $this->fetchObject($cursor, $class))
		{
			return $row;
		}

		// Free up system resources and return.
		$this->freeResult($cursor);
		$cursor = null;

		return false;
	}

	/**
	 * Method to get the next row in the result set from the database query as an array.
	 *
	 * @return  mixed  The result of the query as an array, false if there are no more rows.
	 *
	 * @since   5/2014
	 * @throws  TDatabaseException
	 */
	public function loadNextRow()
	{
		static $cursor;

		// Execute the query and get the result set cursor.
		if (!($cursor = $this->execute()))
		{
			return $this->errorNum ? null : false;
		}

		// Get the next row from the result set as an object of type $class.
		if ($row = $this->fetchArray($cursor))
		{
			return $row;
		}

		// Free up system resources and return.
		$this->freeResult($cursor);
		$cursor = null;

		return false;
	}

	/**
	 * Method to get the first row of the result set from the database query as an object.
	 *
	 * @param   string  $class  The class name to use for the returned row object.
	 *
	 * @return  mixed   The return value or null if the query failed.
	 *
	 * @since   5/2014
	 * @throws  TDatabaseException
	 */
	public function loadObject($class = 'stdClass')
	{
		// Initialise variables.
		$ret = null;

		// Execute the query and get the result set cursor.
		if (!($cursor = $this->execute()))
		{
			return null;
		}

		// Get the first row from the result set as an object of type $class.
		if ($object = $this->fetchObject($cursor, $class))
		{
			$ret = $object;
		}

		// Free up system resources and return.
		$this->freeResult($cursor);

		return $ret;
	}

	/**
	 * Method to get an array of the result set rows from the database query where each row is an object.  The array
	 * of objects can optionally be keyed by a field name, but defaults to a sequential numeric array.
	 *
	 * NOTE: Choosing to key the result array by a non-unique field name can result in unwanted
	 * behavior and should be avoided.
	 *
	 * @param   string  $key    The name of a field on which to key the result array.
	 * @param   string  $class  The class name to use for the returned row objects.
	 *
	 * @return  mixed   The return value or null if the query failed.
	 *
	 * @since   5/2014
	 * @throws  TDatabaseException
	 */
	public function loadObjectList($key = '', $class = 'stdClass')
	{
		// Initialise variables.
		$array = array();

		// Execute the query and get the result set cursor.
		if (!($cursor = $this->execute()))
		{
			return null;
		}

		// Get all of the rows from the result set as objects of type $class.
		while ($row = $this->fetchObject($cursor, $class))
		{
			if ($key)
			{
				$array[$row->$key] = $row;
			}
			else
			{
				$array[] = $row;
			}
		}

		// Free up system resources and return.
		$this->freeResult($cursor);

		return $array;
	}

	/**
	 * Method to get the first field of the first row of the result set from the database query.
	 *
	 * @return  mixed  The return value or null if the query failed.
	 *
	 * @since   5/2014
	 * @throws  TDatabaseException
	 */
	public function loadResult()
	{
		// Initialise variables.
		$ret = null;

		// Execute the query and get the result set cursor.
		if (!($cursor = $this->execute()))
		{
			return null;
		}

		// Get the first row from the result set as an array.
		if ($row = $this->fetchArray($cursor))
		{
			$ret = $row[0];
		}

		// Free up system resources and return.
		$this->freeResult($cursor);

		return $ret;
	}

	/**
	 * Method to get the first row of the result set from the database query as an array.  Columns are indexed
	 * numerically so the first column in the result set would be accessible via <var>$row[0]</var>, etc.
	 *
	 * @return  mixed  The return value or null if the query failed.
	 *
	 * @since   5/2014
	 * @throws  TDatabaseException
	 */
	public function loadRow()
	{
		// Initialise variables.
		$ret = null;

		// Execute the query and get the result set cursor.
		if (!($cursor = $this->execute()))
		{
			return null;
		}

		// Get the first row from the result set as an array.
		if ($row = $this->fetchArray($cursor))
		{
			$ret = $row;
		}

		// Free up system resources and return.
		$this->freeResult($cursor);

		return $ret;
	}

	/**
	 * Method to get an array of the result set rows from the database query where each row is an array.  The array
	 * of objects can optionally be keyed by a field offset, but defaults to a sequential numeric array.
	 *
	 * NOTE: Choosing to key the result array by a non-unique field can result in unwanted
	 * behavior and should be avoided.
	 *
	 * @param   string  $key  The name of a field on which to key the result array.
	 *
	 * @return  mixed   The return value or null if the query failed.
	 *
	 * @since   5/2014
	 * @throws  TDatabaseException
	 */
	public function loadRowList($key = null)
	{
		// Initialise variables.
		$array = array();

		// Execute the query and get the result set cursor.
		if (!($cursor = $this->execute()))
		{
			return null;
		}

		// Get all of the rows from the result set as arrays.
		while ($row = $this->fetchArray($cursor))
		{
			if ($key !== null)
			{
				$array[$row[$key]] = $row;
			}
			else
			{
				$array[] = $row;
			}
		}

		// Free up system resources and return.
		$this->freeResult($cursor);

		return $array;
	}

	/**
	 * Locks a table in the database.
	 *
	 * @param   string  $tableName  The name of the table to unlock.
	 *
	 * @return  TDatabase  Returns this object to support chaining.
	 *
	 * @since   11.4
	 * @throws  TDatabaseException
	 */
	public abstract function lockTable($tableName);

	/**
	 * Execute the SQL statement.
	 *
	 * @return  mixed  A database cursor resource on success, boolean false on failure.
	 *
	 * @since   5/2014
	 * @throws  TDatabaseException
	 */
	public function query()
	{
		return $this->execute();
	}

	/**
	 * Execute the SQL statement.
	 *
	 * @return  mixed  A database cursor resource on success, boolean false on failure.
	 *
	 * @since   12.1
	 * @throws  TDatabaseException
	 */
	abstract public function execute();

	/**
	 * Method to quote and optionally escape a string to database requirements for insertion into the database.
	 *
	 * @param   string   $text    The string to quote.
	 * @param   boolean  $escape  True (default) to escape the string, false to leave it unchanged.
	 *
	 * @return  string  The quoted input string.
	 *
	 * @since   5/2014
	 */
	public function quote($text, $escape = true)
	{
		return '\'' . ($escape ? $this->escape($text) : $text) . '\'';
	}

	/**
	 * Wrap an SQL statement identifier name such as column, table or database names in quotes to prevent injection
	 * risks and reserved word conflicts.
	 *
	 * @param   mixed  $name  The identifier name to wrap in quotes, or an array of identifier names to wrap in quotes.
	 * 							Each type supports dot-notation name.
	 * @param   mixed  $as    The AS query part associated to $name. It can be string or array, in latter case it has to be
	 * 							same length of $name; if is null there will not be any AS part for string or array element.
	 *
	 * @return  mixed  The quote wrapped name, same type of $name.
	 *
	 * @since   5/2014
	 */
	public function quoteName($name, $as = null)
	{
		if (is_string($name))
		{
			$quotedName = $this->quoteNameStr(explode('.', $name));

			$quotedAs = '';
			if (!is_null($as))
			{
				settype($as, 'array');
				$quotedAs .= ' AS ' . $this->quoteNameStr($as);
			}

			return $quotedName . $quotedAs;
		}
		else
		{
			$fin = array();

			if (is_null($as))
			{
				foreach ($name as $str)
				{
					$fin[] = $this->quoteName($str);
				}
			}
			elseif (is_array($name) && (count($name) == count($as)))
			{
				for ($i = 0; $i < count($name); $i++)
				{
					$fin[] = $this->quoteName($name[$i], $as[$i]);
				}
			}

			return $fin;
		}
	}

	/**
	 * Quote strings coming from quoteName call.
	 *
	 * @param   array  $strArr  Array of strings coming from quoteName dot-explosion.
	 *
	 * @return  string  Dot-imploded string of quoted parts.
	 *
	 * @since 5/2014
	 */
	protected function quoteNameStr($strArr)
	{
		$parts = array();
		$q = $this->nameQuote;

		foreach ($strArr as $part)
		{
			if (is_null($part))
			{
				continue;
			}

			if (strlen($q) == 1)
			{
				$parts[] = $q . $part . $q;
			}
			else
			{
				$parts[] = $q{0} . $part . $q{1};
			}
		}

		return implode('.', $parts);
	}

	/**
	 * This function replaces a string identifier <var>$prefix</var> with the string held is the
	 * <var>tablePrefix</var> class variable.
	 *
	 * @param   string  $sql     The SQL statement to prepare.
	 * @param   string  $prefix  The common table prefix.
	 *
	 * @return  string  The processed SQL statement.
	 *
	 * @since   5/2014
	 */
	public function replacePrefix($sql, $prefix = '#__')
	{
		// Initialize variables.
		$escaped = false;
		$startPos = 0;
		$quoteChar = '';
		$literal = '';

		$sql = trim($sql);
		$n = strlen($sql);

		while ($startPos < $n)
		{
			$ip = strpos($sql, $prefix, $startPos);
			if ($ip === false)
			{
				break;
			}

			$j = strpos($sql, "'", $startPos);
			$k = strpos($sql, '"', $startPos);
			if (($k !== false) && (($k < $j) || ($j === false)))
			{
				$quoteChar = '"';
				$j = $k;
			}
			else
			{
				$quoteChar = "'";
			}

			if ($j === false)
			{
				$j = $n;
			}

			$literal .= str_replace($prefix, $this->tablePrefix, substr($sql, $startPos, $j - $startPos));
			$startPos = $j;

			$j = $startPos + 1;

			if ($j >= $n)
			{
				break;
			}

			// quote comes first, find end of quote
			while (true)
			{
				$k = strpos($sql, $quoteChar, $j);
				$escaped = false;
				if ($k === false)
				{
					break;
				}
				$l = $k - 1;
				while ($l >= 0 && $sql{$l} == '\\')
				{
					$l--;
					$escaped = !$escaped;
				}
				if ($escaped)
				{
					$j = $k + 1;
					continue;
				}
				break;
			}
			if ($k === false)
			{
				// error in the query - no end quote; ignore it
				break;
			}
			$literal .= substr($sql, $startPos, $k - $startPos + 1);
			$startPos = $k + 1;
		}
		if ($startPos < $n)
		{
			$literal .= substr($sql, $startPos, $n - $startPos);
		}

		return $literal;
	}

	/**
	 * Renames a table in the database.
	 *
	 * @param   string  $oldTable  The name of the table to be renamed
	 * @param   string  $newTable  The new name for the table.
	 * @param   string  $backup    Table prefix
	 * @param   string  $prefix    For the table - used to rename constraints in non-mysql databases
	 *
	 * @return  TDatabase  Returns this object to support chaining.
	 *
	 * @since   11.4
	 * @throws  TDatabaseException
	 */
	public abstract function renameTable($oldTable, $newTable, $backup = null, $prefix = null);

	/**
	 * Select a database for use.
	 *
	 * @param   string  $database  The name of the database to select for use.
	 *
	 * @return  boolean  True if the database was successfully selected.
	 *
	 * @since   5/2014
	 * @throws  TDatabaseException
	 */
	abstract public function select($database);

	/**
	 * Sets the database debugging state for the driver.
	 *
	 * @param   boolean  $level  True to enable debugging.
	 *
	 * @return  boolean  The old debugging level.
	 *
	 * @since   5/2014
	 */
	public function setDebug($level)
	{
		$previous = $this->debug;
		$this->debug = (bool) $level;

		return $previous;
	}

	/**
	 * Sets the SQL statement string for later execution.
	 *
	 * @param   mixed    $query   The SQL statement to set either as a TDatabaseQuery object or a string.
	 * @param   integer  $offset  The affected row offset to set.
	 * @param   integer  $limit   The maximum affected rows to set.
	 *
	 * @return  TDatabase  This object to support method chaining.
	 *
	 * @since   5/2014
	 */
	public function setQuery($query, $offset = 0, $limit = 0)
	{
		$this->sql = $query;
		$this->limit = (int) max(0, $limit);
		$this->offset = (int) max(0, $offset);

		return $this;
	}

	/**
	 * Set the connection to use UTF-8 character encoding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   5/2014
	 */
	abstract public function setUTF();

	/**
	 * Method to commit a transaction.
	 *
	 * @return  void
	 *
	 * @since   5/2014
	 * @throws  TDatabaseException
	 */
	abstract public function transactionCommit();

	/**
	 * Method to roll back a transaction.
	 *
	 * @return  void
	 *
	 * @since   5/2014
	 * @throws  TDatabaseException
	 */
	abstract public function transactionRollback();

	/**
	 * Method to initialize a transaction.
	 *
	 * @return  void
	 *
	 * @since   5/2014
	 * @throws  TDatabaseException
	 */
	abstract public function transactionStart();

	/**
	 * Method to truncate a table.
	 *
	 * @param   string  $table  The table to truncate
	 *
	 * @return  void
	 *
	 * @since   11.3
	 * @throws  TDatabaseException
	 */
	public function truncateTable($table)
	{
		$this->setQuery('TRUNCATE TABLE ' . $this->quoteName($table));
		$this->execute();
	}

	/**
	 * Updates a row in a table based on an object's properties.
	 *
	 * @param   string   $table    The name of the database table to update.
	 * @param   object   &$object  A reference to an object whose public properties match the table fields.
	 * @param   string   $key      The name of the primary key.
	 * @param   boolean  $nulls    True to update null fields or false to ignore them.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   5/2014
	 * @throws  TDatabaseException
	 */
	public function updateObject($table, &$object, $key, $nulls = false)
	{
		// Initialise variables.
		$fields = array();
		$where = '';

		// Create the base update statement.
		$statement = 'UPDATE ' . $this->quoteName($table) . ' SET %s WHERE %s';

		// Iterate over the object variables to build the query fields/value pairs.
		foreach (get_object_vars($object) as $k => $v)
		{
			// Only process scalars that are not internal fields.
			if (is_array($v) or is_object($v) or $k[0] == '_')
			{
				continue;
			}

			// Set the primary key to the WHERE clause instead of a field to update.
			if ($k == $key)
			{
				$where = $this->quoteName($k) . '=' . $this->quote($v);
				continue;
			}

			// Prepare and sanitize the fields and values for the database query.
			if ($v === null)
			{
				// If the value is null and we want to update nulls then set it.
				if ($nulls)
				{
					$val = 'NULL';
				}
				// If the value is null and we do not want to update nulls then ignore this field.
				else
				{
					continue;
				}
			}
			// The field is not null so we prep it for update.
			else
			{
				$val = $this->quote($v);
			}

			// Add the field to be updated.
			$fields[] = $this->quoteName($k) . '=' . $val;
		}

		// We don't have any fields to update.
		if (empty($fields))
		{
			return true;
		}

		// Set the query and execute the update.
		$this->setQuery(sprintf($statement, implode(",", $fields), $where));
		return $this->execute();
	}

	/**
	 * Unlocks tables in the database.
	 *
	 * @return  TDatabase  Returns this object to support chaining.
	 *
	 * @since   11.4
	 * @throws  TDatabaseException
	 */
	public abstract function unlockTables();

	//
	// Deprecated methods.
	//

	/**
	 * Sets the debug level on or off
	 *
	 * @param   integer  $level  0 to disable debugging and 1 to enable it.
	 *
	 * @return  void
	 *
	 * @deprecated  12.1
	 * @since   5/2014
	 */
	public function debug($level)
	{
		$this->setDebug(($level == 0) ? false : true);
	}

	/**
	 * Diagnostic method to return explain information for a query.
	 *
	 * @return  string  The explain output.
	 *
	 * @deprecated  12.1
	 * @since   5/2014
	 */
	abstract public function explain();

	/**
	 * Gets the error message from the database connection.
	 *
	 * @param   boolean  $escaped  True to escape the message string for use in JavaScript.
	 *
	 * @return  string  The error message for the most recent query.
	 *
	 * @deprecated  12.1
	 * @since   5/2014
	 */
	public function getErrorMsg($escaped = false)
	{
		// Deprecation warning.
		TLog::add('TDatabase::getErrorMsg() is deprecated, use exception handling instead.', TLog::WARNING, 'deprecated');

		if ($escaped)
		{
			return addslashes($this->errorMsg);
		}
		else
		{
			return $this->errorMsg;
		}
	}

	/**
	 * Gets the error number from the database connection.
	 *
	 * @return      integer  The error number for the most recent query.
	 *
	 * @since       5/2014
	 * @deprecated  12.1
	 */
	public function getErrorNum()
	{
		// Deprecation warning.
		TLog::add('TDatabase::getErrorNum() is deprecated, use exception handling instead.', TLog::WARNING, 'deprecated');

		return $this->errorNum;
	}

	/**
	 * Method to escape a string for usage in an SQL statement.
	 *
	 * @param   string   $text   The string to be escaped.
	 * @param   boolean  $extra  Optional parameter to provide extra escaping.
	 *
	 * @return  string  The escaped string.
	 *
	 * @since   5/2014
	 * @deprecated  12.1
	 */
	public function getEscaped($text, $extra = false)
	{
		// Deprecation warning.
		TLog::add('TDatabase::getEscaped() is deprecated. Use TDatabase::escape().', TLog::WARNING, 'deprecated');

		return $this->escape($text, $extra);
	}

	/**
	 * Retrieves field information about the given tables.
	 *
	 * @param   mixed    $tables    A table name or a list of table names.
	 * @param   boolean  $typeOnly  True to only return field types.
	 *
	 * @return  array  An array of fields by table.
	 *
	 * @since   5/2014
	 * @throws  TDatabaseException
	 * @deprecated  12.1
	 */
	public function getTableFields($tables, $typeOnly = true)
	{
		$results = array();

		settype($tables, 'array');

		foreach ($tables as $table)
		{
			$results[$table] = $this->getTableColumns($table, $typeOnly);
		}

		return $results;
	}

	/**
	 * Get the total number of SQL statements executed by the database driver.
	 *
	 * @return      integer
	 *
	 * @since       5/2014
	 * @deprecated  12.1
	 */
	public function getTicker()
	{
	
		return $this->count;
	}

	/**
	 * Checks if field name needs to be quoted.
	 *
	 * @param   string  $field  The field name to be checked.
	 *
	 * @return  bool
	 *
	 * @deprecated  12.1
	 * @since   5/2014
	 */
	public function isQuoted($field)
	{
	
		if ($this->hasQuoted)
		{
			return in_array($field, $this->quoted);
		}
		else
		{
			return true;
		}
	}

	/**
	 * Method to get an array of values from the <var>$offset</var> field in each row of the result set from
	 * the database query.
	 *
	 * @param   integer  $offset  The row offset to use to build the result array.
	 *
	 * @return  mixed    The return value or null if the query failed.
	 *
	 * @since   5/2014
	 * @throws  TDatabaseException
	 */
	public function loadResultArray($offset = 0)
	{
	
		return $this->loadColumn($offset);
	}

	/**
	 * Wrap an SQL statement identifier name such as column, table or database names in quotes to prevent injection
	 * risks and reserved word conflicts.
	 *
	 * @param   string  $name  The identifier name to wrap in quotes.
	 *
	 * @return  string  The quote wrapped name.
	 *
	 * @since   5/2014
	 */
	public function nameQuote($name)
	{
	
		return $this->quoteName($name);
	}

	/**
	 * Execute a query batch.
	 *
	 * @param   boolean  $abortOnError     Abort on error.
	 * @param   boolean  $transactionSafe  Transaction safe queries.
	 *
	 * @return  mixed  A database resource if successful, false if not.
	 *
	 * @since   5/2014
	 */
	abstract public function queryBatch($abortOnError = true, $transactionSafe = false);

	/**
	 * Return the most recent error message for the database connector.
	 *
	 * @param   boolean  $showSQL  True to display the SQL statement sent to the database as well as the error.
	 *
	 * @return  string  The error message for the most recent query.
	 *
	 * @since   5/2014
	 */
	public function stderr($showSQL = false)
	{
	
		if ($this->errorNum != 0)
		{
			return Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $this->errorNum, $this->errorMsg)
				. ($showSQL ? "<br />SQL = <pre>$this->sql</pre>" : '');
		}
		else
		{
			return Text::_('JLIB_DATABASE_FUNCTION_NOERROR');
		}
	}
        
        /**
         * 
         * @param type $keyvalues array to map field and value to where
         * @param type $tablename name of table
         * @param type $firstrecord if is true return an object else list object
         * @return object list
         */
        public function getData($keyvalues = array(),$tablename = '',$firstrecord = false)
        {
            if(empty($tablename)) return null;
            
            if(!is_array($keyvalues)) return null;
             
            $where = array();
            
            foreach($keyvalues as $field=>$value){
                $where[$this->quoteName($field)] = $this->quoteName($field). ' = ' . $this->quote($value); 
            }
            
            $sql = 'select * from #__'.$tablename;
            
            if(count($where)){
                $sql .= ' where '.  implode(' and ', $where);
            }
                
            
            $this->setQuery($sql);
     
            return ($firstrecord)?$this->loadObject():$this->loadObjectList();
        }
        
        
        
}
