<?php
/**
 * @package     T.Platform
 * @subpackage  Database
 */

defined('T_PLATFORM') or die;


/**
 * MySQL database driver
 *
 * @package     T.Platform
 * @subpackage  Database
 * @see         http://dev.mysql.com/doc/
 * @since       5/2014
 */
class TDatabaseMySQL extends TDatabase
{
	/**
	 * The name of the database driver.
	 *
	 * @var    string
	 * @since  5/2014
	 */
	public $name = 'mysql';

	/**
	 * The character(s) used to quote SQL statement names such as table names or field names,
	 * etc. The child classes should define this as necessary.  If a single character string the
	 * same character is used for both sides of the quoted name, else the first character will be
	 * used for the opening quote and the second for the closing quote.
	 *
	 * @var    string
	 * @since  5/2014
	 */
	protected $nameQuote = '`';

	/**
	 * The null or zero representation of a timestamp for the database driver.  This should be
	 * defined in child classes to hold the appropriate value for the engine.
	 *
	 * @var    string
	 * @since  5/2014
	 */
	protected $nullDate = '0000-00-00 00:00:00';

	/**
	 * @var    string  The minimum supported database version.
	 * @since  12.1
	 */
	protected $dbMinimum = '5.0.4';

	/**
	 * Constructor.
	 *
	 * @param   array  $options  Array of database options with keys: host, user, password, database, select.
	 *
	 * @since   5/2014
	 */
	protected function __construct($options)
	{
		// Get some basic values from the options.
		$options['host'] = (isset($options['host'])) ? $options['host'] : 'localhost';
		$options['user'] = (isset($options['user'])) ? $options['user'] : 'root';
		$options['password'] = (isset($options['password'])) ? $options['password'] : '';
		$options['database'] = (isset($options['database'])) ? $options['database'] : '';
		$options['select'] = (isset($options['select'])) ? (bool) $options['select'] : true;

		// Make sure the MySQL extension for PHP is installed and enabled.
		if (!function_exists('mysql_connect'))
		{

			// Legacy error handling switch based on the TError::$legacy switch.
			// @deprecated  12.1
			if (TError::$legacy)
			{
				$this->errorNum = 1;
				$this->errorMsg = Text::_('JLIB_DATABASE_ERROR_ADAPTER_MYSQL');
				return;
			}
			else
			{
				throw new TDatabaseException(Text::_('JLIB_DATABASE_ERROR_ADAPTER_MYSQL'));
			}
		}

		// Attempt to connect to the server.
		if (!($this->connection = @ mysql_connect($options['host'], $options['user'], $options['password'], true)))
		{

			// Legacy error handling switch based on the TError::$legacy switch.
			// @deprecated  12.1
			if (TError::$legacy)
			{
				$this->errorNum = 2;
				$this->errorMsg = Text::_('JLIB_DATABASE_ERROR_CONNECT_MYSQL');
				return;
			}
			else
			{
				throw new TDatabaseException(Text::_('JLIB_DATABASE_ERROR_CONNECT_MYSQL'));
			}
		}

		// Finalize initialisation
		parent::__construct($options);

		// Set sql_mode to non_strict mode
		mysql_query("SET @@SESSION.sql_mode = '';", $this->connection);

		// If auto-select is enabled select the given database.
		if ($options['select'] && !empty($options['database']))
		{
			$this->select($options['database']);
		}
	}

	/**
	 * Destructor.
	 *
	 * @since   5/2014
	 */
	public function __destruct()
	{
		if (is_resource($this->connection))
		{
			mysql_close($this->connection);
		}
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
	 */
	public function escape($text, $extra = false)
	{
		$result = mysql_real_escape_string($text, $this->getConnection());

		if ($extra)
		{
			$result = addcslashes($result, '%_');
		}

		return $result;
	}

	/**
	 * Test to see if the MySQL connector is available.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   5/2014
	 */
	public static function test()
	{
		return (function_exists('mysql_connect'));
	}

	/**
	 * Determines if the connection to the server is active.
	 *
	 * @return  boolean  True if connected to the database engine.
	 *
	 * @since   5/2014
	 */
	public function connected()
	{
		if (is_resource($this->connection))
		{
			return mysql_ping($this->connection);
		}

		return false;
	}

	/**
	 * Drops a table from the database.
	 *
	 * @param   string   $tableName  The name of the database table to drop.
	 * @param   boolean  $ifExists   Optionally specify that the table must exist before it is dropped.
	 *
	 * @return  TDatabaseMySQL  Returns this object to support chaining.
	 *
	 * @since   5/2014
	 * @throws  TDatabaseException
	 */
	public function dropTable($tableName, $ifExists = true)
	{
		$query = $this->getQuery(true);

		$this->setQuery('DROP TABLE ' . ($ifExists ? 'IF EXISTS ' : '') . $query->quoteName($tableName));

		$this->execute();

		return $this;
	}

	/**
	 * Get the number of affected rows for the previous executed SQL statement.
	 *
	 * @return  integer  The number of affected rows.
	 *
	 * @since   5/2014
	 */
	public function getAffectedRows()
	{
		return mysql_affected_rows($this->connection);
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
	public function getNumRows($cursor = null)
	{
		return mysql_num_rows($cursor ? $cursor : $this->cursor);
	}

	/**
	 * Get the current or query, or new TDatabaseQuery object.
	 *
	 * @param   boolean  $new  False to return the last query set, True to return a new TDatabaseQuery object.
	 *
	 * @return  mixed  The current value of the internal SQL variable or a new TDatabaseQuery object.
	 *
	 * @since   5/2014
	 * @throws  TDatabaseException
	 */
	public function getQuery($new = false)
	{
		if ($new)
		{
			// Make sure we have a query class for this driver.
			if (!class_exists('TDatabaseQueryMySQL'))
			{
				throw new TDatabaseException(Text::_('TLIB_DATABASE_ERROR_MISSING_QUERY'));
			}
			return new TDatabaseQueryMySQL($this);
		}
		else
		{
			return $this->sql;
		}
	}

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
	public function getTableCreate($tables)
	{
		// Initialise variables.
		$result = array();

		// Sanitize input to an array and iterate over the list.
		settype($tables, 'array');
		foreach ($tables as $table)
		{
			// Set the query to get the table CREATE statement.
			$this->setQuery('SHOW CREATE table ' . $this->quoteName($this->escape($table)));
			$row = $this->loadRow();

			// Populate the result array based on the create statements.
			$result[$table] = $row[1];
		}

		return $result;
	}

	/**
	 * Retrieves field information about a given table.
	 *
	 * @param   string   $table     The name of the database table.
	 * @param   boolean  $typeOnly  True to only return field types.
	 *
	 * @return  array  An array of fields for the database table.
	 *
	 * @since   5/2014
	 * @throws  TDatabaseException
	 */
	public function getTableColumns($table, $typeOnly = true)
	{
		$result = array();

		// Set the query to get the table fields statement.
		$this->setQuery('SHOW FULL COLUMNS FROM ' . $this->quoteName($this->escape($table)));
		$fields = $this->loadObjectList();

		// If we only want the type as the value add just that to the list.
		if ($typeOnly)
		{
			foreach ($fields as $field)
			{
				$result[$field->Field] = preg_replace("/[(0-9)]/", '', $field->Type);
			}
		}
		// If we want the whole field data object add that to the list.
		else
		{
			foreach ($fields as $field)
			{
				$result[$field->Field] = $field;
			}
		}

		return $result;
	}

	/**
	 * Get the details list of keys for a table.
	 *
	 * @param   string  $table  The name of the table.
	 *
	 * @return  array  An array of the column specification for the table.
	 *
	 * @since   5/2014
	 * @throws  TDatabaseException
	 */
	public function getTableKeys($table)
	{
		// Get the details columns information.
		$this->setQuery('SHOW KEYS FROM ' . $this->quoteName($table));
		$keys = $this->loadObjectList();

		return $keys;
	}

	/**
	 * Method to get an array of all tables in the database.
	 *
	 * @return  array  An array of all the tables in the database.
	 *
	 * @since   5/2014
	 * @throws  TDatabaseException
	 */
	public function getTableList()
	{
		// Set the query to get the tables statement.
		$this->setQuery('SHOW TABLES');
		$tables = $this->loadColumn();

		return $tables;
	}

	/**
	 * Get the version of the database connector.
	 *
	 * @return  string  The database connector version.
	 *
	 * @since   5/2014
	 */
	public function getVersion()
	{
		return mysql_get_server_info($this->connection);
	}

	/**
	 * Determines if the database engine supports UTF-8 character encoding.
	 *
	 * @return  boolean  True if supported.
	 *
	 * @since   5/2014
	 * @deprecated 12.1
	 */
	public function hasUTF()
	{
		TLog::add('TDatabaseMySQL::hasUTF() is deprecated.', TLog::WARNING, 'deprecated');
		return true;
	}

	/**
	 * Method to get the auto-incremented value from the last INSERT statement.
	 *
	 * @return  integer  The value of the auto-increment field from the last inserted row.
	 *
	 * @since   5/2014
	 */
	public function insertid()
	{
		return mysql_insert_id($this->connection);
	}

	/**
	 * Locks a table in the database.
	 *
	 * @param   string  $table  The name of the table to unlock.
	 *
	 * @return  TDatabaseMySQL  Returns this object to support chaining.
	 *
	 * @since   5/2014
	 * @throws  TDatabaseException
	 */
	public function lockTable($table)
	{
		$this->setQuery('LOCK TABLES ' . $this->quoteName($table) . ' WRITE')->execute();

		return $this;
	}

	/**
	 * Execute the SQL statement.
	 *
	 * @return  mixed  A database cursor resource on success, boolean false on failure.
	 *
	 * @since   5/2014
	 * @throws  TDatabaseException
	 */
	public function execute()
	{
		if (!is_resource($this->connection))
		{
			// Legacy error handling switch based on the TError::$legacy switch.
			// @deprecated  12.1
			if (TError::$legacy)
			{
				if ($this->debug)
				{
					TError::raiseError(500, 'TDatabaseMySQL::query: ' . $this->errorNum . ' - ' . $this->errorMsg);
				}
				return false;
			}
			else
			{
				TLog::add(Text::sprintf('JLIB_DATABASE_QUERY_FAILED', $this->errorNum, $this->errorMsg), TLog::ERROR, 'database');
				throw new TDatabaseException($this->errorMsg, $this->errorNum);
			}
		}

		// Take a local copy so that we don't modify the original query and cause issues later
		$sql = $this->replacePrefix((string) $this->sql);
		if ($this->limit > 0 || $this->offset > 0)
		{
			$sql .= ' LIMIT ' . $this->offset . ', ' . $this->limit;
		}

		// If debugging is enabled then let's log the query.
		if ($this->debug)
		{
			// Increment the query counter and add the query to the object queue.
			$this->count++;
			$this->log[] = $sql;

			TLog::add($sql, TLog::DEBUG, 'databasequery');
		}

		// Reset the error values.
		$this->errorNum = 0;
		$this->errorMsg = '';

		// Execute the query.
		$this->cursor = mysql_query($sql, $this->connection);

		// If an error occurred handle it.
		if (!$this->cursor)
		{
			$this->errorNum = (int) mysql_errno($this->connection);
			$this->errorMsg = (string) mysql_error($this->connection) . ' SQL=' . $sql;

			// Legacy error handling switch based on the TError::$legacy switch.
			// @deprecated  12.1
			if (TError::$legacy)
			{
				if ($this->debug)
				{
					TError::raiseError(500, 'TDatabaseMySQL::query: ' . $this->errorNum . ' - ' . $this->errorMsg);
				}
				return false;
			}
			else
			{
				TLog::add(Text::sprintf('JLIB_DATABASE_QUERY_FAILED', $this->errorNum, $this->errorMsg), TLog::ERROR, 'databasequery');
				throw new TDatabaseException($this->errorMsg, $this->errorNum);
			}
		}

		return $this->cursor;
	}

	/**
	 * Renames a table in the database.
	 *
	 * @param   string  $oldTable  The name of the table to be renamed
	 * @param   string  $newTable  The new name for the table.
	 * @param   string  $backup    Not used by MySQL.
	 * @param   string  $prefix    Not used by MySQL.
	 *
	 * @return  TDatabase  Returns this object to support chaining.
	 *
	 * @since   5/2014
	 * @throws  TDatabaseException
	 */
	public function renameTable($oldTable, $newTable, $backup = null, $prefix = null)
	{
		$this->setQuery('RENAME TABLE ' . $oldTable . ' TO ' . $newTable)->execute();

		return $this;
	}

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
	public function select($database)
	{
		if (!$database)
		{
			return false;
		}

		if (!mysql_select_db($database, $this->connection))
		{
			// Legacy error handling switch based on the TError::$legacy switch.
			// @deprecated  12.1
			if (TError::$legacy)
			{
				$this->errorNum = 3;
				$this->errorMsg = Text::_('JLIB_DATABASE_ERROR_DATABASE_CONNECT');
				return false;
			}
			else
			{
				throw new TDatabaseException(Text::_('JLIB_DATABASE_ERROR_DATABASE_CONNECT'));
			}
		}

		return true;
	}

	/**
	 * Set the connection to use UTF-8 character encoding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   5/2014
	 */
	public function setUTF()
	{
		return mysql_query("SET NAMES 'utf8'", $this->connection);
	}

	/**
	 * Method to commit a transaction.
	 *
	 * @return  void
	 *
	 * @since   5/2014
	 * @throws  TDatabaseException
	 */
	public function transactionCommit()
	{
		$this->setQuery('COMMIT');
		$this->execute();
	}

	/**
	 * Method to roll back a transaction.
	 *
	 * @return  void
	 *
	 * @since   5/2014
	 * @throws  TDatabaseException
	 */
	public function transactionRollback()
	{
		$this->setQuery('ROLLBACK');
		$this->execute();
	}

	/**
	 * Method to initialize a transaction.
	 *
	 * @return  void
	 *
	 * @since   5/2014
	 * @throws  TDatabaseException
	 */
	public function transactionStart()
	{
		$this->setQuery('START TRANSACTION');
		$this->execute();
	}

	/**
	 * Method to fetch a row from the result set cursor as an array.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  mixed  Either the next row from the result set or false if there are no more rows.
	 *
	 * @since   5/2014
	 */
	protected function fetchArray($cursor = null)
	{
		return mysql_fetch_row($cursor ? $cursor : $this->cursor);
	}

	/**
	 * Method to fetch a row from the result set cursor as an associative array.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  mixed  Either the next row from the result set or false if there are no more rows.
	 *
	 * @since   5/2014
	 */
	protected function fetchAssoc($cursor = null)
	{
		return mysql_fetch_assoc($cursor ? $cursor : $this->cursor);
	}

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
	protected function fetchObject($cursor = null, $class = 'stdClass')
	{
		return mysql_fetch_object($cursor ? $cursor : $this->cursor, $class);
	}

	/**
	 * Method to free up the memory used for the result set.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  void
	 *
	 * @since   5/2014
	 */
	protected function freeResult($cursor = null)
	{
		mysql_free_result($cursor ? $cursor : $this->cursor);
	}

	/**
	 * Diagnostic method to return explain information for a query.
	 *
	 * @return      string  The explain output.
	 *
	 * @since       5/2014
	 * @deprecated  12.1
	 */
	public function explain()
	{
		// Deprecation warning.
		TLog::add('TDatabaseMySQL::explain() is deprecated.', TLog::WARNING, 'deprecated');

		// Backup the current query so we can reset it later.
		$backup = $this->sql;

		// Prepend the current query with EXPLAIN so we get the diagnostic data.
		$this->sql = 'EXPLAIN ' . $this->sql;

		// Execute the query and get the result set cursor.
		if (!($cursor = $this->execute()))
		{
			return null;
		}

		// Build the HTML table.
		$first = true;
		$buffer = '<table id="explain-sql">';
		$buffer .= '<thead><tr><td colspan="99">' . $this->getQuery() . '</td></tr>';
		while ($row = $this->fetchAssoc($cursor))
		{
			if ($first)
			{
				$buffer .= '<tr>';
				foreach ($row as $k => $v)
				{
					$buffer .= '<th>' . $k . '</th>';
				}
				$buffer .= '</tr></thead><tbody>';
				$first = false;
			}
			$buffer .= '<tr>';
			foreach ($row as $k => $v)
			{
				$buffer .= '<td>' . $v . '</td>';
			}
			$buffer .= '</tr>';
		}
		$buffer .= '</tbody></table>';

		// Restore the original query to its state before we ran the explain.
		$this->sql = $backup;

		// Free up system resources and return.
		$this->freeResult($cursor);

		return $buffer;
	}

	/**
	 * Execute a query batch.
	 *
	 * @param   boolean  $abortOnError     Abort on error.
	 * @param   boolean  $transactionSafe  Transaction safe queries.
	 *
	 * @return  mixed  A database resource if successful, false if not.
	 *
	 * @deprecated  12.1
	 * @since   5/2014
	 */
	public function queryBatch($abortOnError = true, $transactionSafe = false)
	{
		// Deprecation warning.
		TLog::add('TDatabaseMySQL::queryBatch() is deprecated.', TLog::WARNING, 'deprecated');

		$sql = $this->replacePrefix((string) $this->sql);
		$this->errorNum = 0;
		$this->errorMsg = '';

		// If the batch is meant to be transaction safe then we need to wrap it in a transaction.
		if ($transactionSafe)
		{
			$sql = 'START TRANSACTION;' . rtrim($sql, "; \t\r\n\0") . '; COMMIT;';
		}
		$queries = $this->splitSql($sql);
		$error = 0;
		foreach ($queries as $query)
		{
			$query = trim($query);
			if ($query != '')
			{
				$this->cursor = mysql_query($query, $this->connection);
				if ($this->debug)
				{
					$this->count++;
					$this->log[] = $query;
				}
				if (!$this->cursor)
				{
					$error = 1;
					$this->errorNum .= mysql_errno($this->connection) . ' ';
					$this->errorMsg .= mysql_error($this->connection) . " SQL=$query <br />";
					if ($abortOnError)
					{
						return $this->cursor;
					}
				}
			}
		}
		return $error ? false : true;
	}

	/**
	 * Unlocks tables in the database.
	 *
	 * @return  TDatabaseMySQL  Returns this object to support chaining.
	 *
	 * @since   5/2014
	 * @throws  TDatabaseException
	 */
	public function unlockTables()
	{
		$this->setQuery('UNLOCK TABLES')->execute();

		return $this;
	}
}

/**
 * Query Building Class.
 *
 * @package     T.Platform
 * @subpackage  Database
 * @since       5/2014
 */
class TDatabaseQueryMySQL extends TDatabaseQuery
{
	/**
	 * Concatenates an array of column names or values.
	 *
	 * @param   array   $values     An array of values to concatenate.
	 * @param   string  $separator  As separator to place between each value.
	 *
	 * @return  string  The concatenated values.
	 *
	 * @since   5/2014
	 */
	public function concatenate($values, $separator = null)
	{
		if ($separator)
		{
			$concat_string = 'CONCAT_WS(' . $this->quote($separator);

			foreach ($values as $value)
			{
				$concat_string .= ', ' . $value;
			}

			return $concat_string . ')';
		}
		else
		{
			return 'CONCAT(' . implode(',', $values) . ')';
		}
	}
}
