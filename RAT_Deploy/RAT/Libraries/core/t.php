<?php
/**
 * @package    T.Platform
 *
 */

defined('T_PLATFORM') or die;

/**
 * Platform connecter class
 *
 * @package  T.Platform
 * @since    5/2014
 */
abstract class T
{
	/**
	 * @var    TApplication
	 * @since  5/2014
	 */
	public static $application = null;

	/**
	 * @var    TCache
	 * @since  5/2014
	 */
	public static $cache = null;

	/**
	 * @var    TConfig
	 * @since  5/2014
	 */
	public static $config = null;

	/**
	 * @var    array
	 * @since  11.3
	 */
	public static $dates = array();

	/**
	 * @var    TSession
	 * @since  5/2014
	 */
	public static $session = null;


	/**
	 * @var    TDocument
	 * @since  5/2014
	 */
	public static $document = null;

	
	/**
	 * @var    TDatabase
	 * @since  5/2014
	 */
	public static $database = null;

	/**
	 * @var    TMail
	 * @since  5/2014
	 */
	public static $mailer = null;

	/**
	 * Get a application object.
	 *
	 * Returns the global {@link TApplication} object, only creating it if it doesn't already exist.
	 *
	 * @param   mixed   $id      A client identifier or name.
	 * @param   array   $config  An optional associative array of configuration settings.
	 * @param   string  $prefix  Application prefix
	 *
	 * @return  TApplication object
	 *
	 * @see     TApplication
	 * @since   5/2014
	 */
	public static function getApplication($config = array())
	{
		if (!self::$application)
		{
                    $applicationClassName = $config['name'];
			if (!class_exists($applicationClassName))
			{
				TError::raiseError(500, 'Not Application Class');
			}

			self::$application = $applicationClassName::getInstance($config);
		}

		return self::$application;
	}

	/**
	 * Get a configuration object
	 *
	 * Returns the global  object, only creating it if it doesn't already exist.
	 *
	 * @param   string  $file  The path to the configuration file
	 * @param   string  $type  The type of the configuration file
	 *
	 * @return  TConfig
	 *
	 * @see     TConfig
	 * @since   5/2014
	 */
	public static function getConfig($file = null, $type = 'PHP')
	{
		return new TConfig();
	}

	/**
	 * Get a session object.
	 *
	 * Returns the global {@link TSession} object, only creating it if it doesn't already exist.
	 *
	 * @param   array  $options  An array containing session options
	 *
	 * @return  TSession object
	 *
	 * @see     TSession
	 * @since   5/2014
	 */
	public static function getSession($options = array())
	{
		if (!self::$session)
		{
			self::$session = self::_createSession($options);
                        
		}

		return self::$session;
	}


	/**
	 * Get a document object.
	 *
	 * Returns the global {@link TDocument} object, only creating it if it doesn't already exist.
	 *
	 * @return  TDocument object
	 *
	 * @see     TDocument
	 * @since   5/2014
	 */
	public static function getDocument()
	{
		if (!self::$document)
		{
			self::$document = self::createDocument();
		}

		return self::$document;
	}

	
	/**
	 * Get a cache object
	 *
	 * Returns the global {@link TCache} object
	 *
	 * @param   string  $group    The cache group name
	 * @param   string  $handler  The handler to use
	 * @param   string  $storage  The storage method
	 *
	 * @return  TCache object
	 *
	 * @see     TCache
	 */
	public static function getCache($group = '', $handler = 'callback', $storage = null)
	{
		$hash = md5($group . $handler . $storage);
		if (isset(self::$cache[$hash]))
		{
			return self::$cache[$hash];
		}
		$handler = ($handler == 'function') ? 'callback' : $handler;

		$options = array('defaultgroup' => $group);

		if (isset($storage))
		{
			$options['storage'] = $storage;
		}

		$cache = TCache::getInstance($handler, $options);

		self::$cache[$hash] = $cache;

		return self::$cache[$hash];
	}

	
	/**
	 * Get a database object.
	 *
	 * Returns the global {@link TDatabase} object, only creating it if it doesn't already exist.
	 *
	 * @return  TDatabase object
	 *
	 * @see     TDatabase
	 * @since   5/2014
	 */
	public static function getDbo()
	{
		if (!self::$database)
		{
			self::$database = self::createDbo();
		}

		return self::$database;
	}

	/**
	 * Get a mailer object.
	 *
	 * Returns the global {@link TMail} object, only creating it if it doesn't already exist.
	 *
	 * @return  TMail object
	 *
	 * @see     TMail
	 * @since   5/2014
	 */
	public static function getMailer()
	{
		if (!self::$mailer)
		{
			self::$mailer = self::createMailer();
		}
		$copy = clone self::$mailer;

		return $copy;
	}

	
	/**
	 * Get an XML document
	 *
	 * @param   string  $type     The type of XML parser needed 'DOM', 'RSS' or 'Simple'
	 * @param   array   $options  ['rssUrl'] the rss url to parse when using "RSS", ['cache_time'] with '
	 *                             RSS' - feed cache time. If not defined defaults to 3600 sec
	 *
	 * @return  object  Parsed XML document object
	 *
	 * @deprecated    12.1   Use TXMLElement instead.
	 * @see           TXMLElement
	 */
	public static function getXMLParser($type = '', $options = array())
	{
		// Deprecation warning.
		TLog::add('JFactory::getXMLParser() is deprecated.', TLog::WARNING, 'deprecated');

		$doc = null;

		switch (strtolower($type))
		{
			case 'rss':
			case 'atom':
				$cache_time = isset($options['cache_time']) ? $options['cache_time'] : 0;
				$doc = self::getFeedParser($options['rssUrl'], $cache_time);
				break;

			case 'simple':
				// TError::raiseWarning('SOME_ERROR_CODE', 'TSimpleXML is deprecated. Use self::getXML instead');
				tinclude('joomla.utilities.simplexml');
				$doc = new TSimpleXML;
				break;

			case 'dom':
				TError::raiseWarning('SOME_ERROR_CODE', Text::_('JLIB_UTIL_ERROR_DOMIT'));
				$doc = null;
				break;

			default:
				$doc = null;
		}

		return $doc;
	}

	/**
	 * Reads a XML file.
	 *
	 * @param   string   $data    Full path and file name.
	 * @param   boolean  $isFile  true to load a file or false to load a string.
	 *
	 * @return  mixed    TXMLElement on success or false on error.
	 *
	 * @see     TXMLElement
	 * @since   5/2014
	 * @todo    This may go in a separate class - error reporting may be improved.
	 */
	public static function getXML($data, $isFile = true)
	{
		tinclude('core.utilities.xmlelement');

		// Disable libxml errors and allow to fetch error information as needed
		libxml_use_internal_errors(true);

		if ($isFile)
		{
			// Try to load the XML file
			$xml = simplexml_load_file($data, 'TXMLElement');
		}
		else
		{
			// Try to load the XML string
			$xml = simplexml_load_string($data, 'TXMLElement');
		}

		if (empty($xml))
		{
			// There was an error
			TError::raiseWarning(100, Text::_('JLIB_UTIL_ERROR_XML_LOAD'));

			if ($isFile)
			{
				TError::raiseWarning(100, $data);
			}

			foreach (libxml_get_errors() as $error)
			{
				TError::raiseWarning(100, 'XML: ' . $error->message);
			}
		}

		return $xml;
	}


	/**
	 * Return a reference to the {@link TURI} object
	 *
	 * @param   string  $uri  Uri name.
	 *
	 * @return  TURI object
	 *
	 * @see     TURI
	 * @since   5/2014
	 */
	public static function getURI($uri = 'SERVER')
	{
		tinclude('core.environment.uri');

		return TURI::getInstance($uri);
	}

	/**
	 * Return the {@link TDate} object
	 *
	 * @param   mixed  $time      The initial time for the TDate object
	 * @param   mixed  $tzOffset  The timezone offset.
	 *
	 * @return  TDate object
	 *
	 * @see     TDate
	 * @since   5/2014
	 */
	public static function getDate($time = 'now', $tzOffset = null)
	{
		tinclude('core.utilities.date');
		static $classname;
		static $mainLocale;

		$language = self::getLanguage();
		$locale = $language->getTag();

		if (!isset($classname) || $locale != $mainLocale)
		{
			//Store the locale for future reference
			$mainLocale = $locale;

			if ($mainLocale !== false)
			{
				$classname = str_replace('-', '_', $mainLocale) . 'Date';

				if (!class_exists($classname))
				{
					//The class does not exist, default to TDate
					$classname = 'TDate';
				}
			}
			else
			{
				//No tag, so default to TDate
				$classname = 'TDate';
			}
		}

		$key = $time . '-' . ($tzOffset instanceof DateTimeZone ? $tzOffset->getName() : (string) $tzOffset);

		if (!isset(self::$dates[$classname][$key]))
		{
			self::$dates[$classname][$key] = new $classname($time, $tzOffset);
		}

		$date = clone self::$dates[$classname][$key];

		return $date;
	}

	/**
	 * Create a configuration object
	 *
	 * @param   string  $file       The path to the configuration file.
	 * @param   string  $type       The type of the configuration file.
	 * @param   string  $namespace  The namespace of the configuration file.
	 *
	 * @return  TConfig
	 *
	 * @see     TConfig
	 * @since   5/2014
	 * @deprecated 12.3
	 */
	protected static function _createConfig($file, $type = 'PHP', $namespace = '')
	{
		TLog::add(__METHOD__ . '() is deprecated.', TLog::WARNING, 'deprecated');

		return self::createConfig($file, $type, $namespace);
	}

	/**
	 * Create a configuration object
	 *
	 * @param   string  $file       The path to the configuration file.
	 * @param   string  $type       The type of the configuration file.
	 * @param   string  $namespace  The namespace of the configuration file.
	 *
	 * @return  TConfig
	 *
	 * @see     TConfig
	 * @since   5/2014
	 */
	protected static function createConfig($file, $type = 'PHP', $namespace = '')
	{
		if (is_file($file))
		{
			include_once $file;
		}

		// Create the registry with a default namespace of config
		$tconfig = new TConfig;

		return $tconfig;
	}

	/**
	 * Create a session object
	 *
	 * @param   array  $options  An array containing session options
	 *
	 * @return  TSession object
	 *
	 * @since   5/2014
	 */
	protected static function _createSession($options = array())
	{

		return self::createSession($options);
	}

	/**
	 * Create a session object
	 *
	 * @param   array  $options  An array containing session options
	 *
	 * @return  TSession object
	 *
	 * @since   5/2014
	 */
	protected static function createSession($options = array())
	{
		// Get the editor configuration setting
		$conf = self::getConfig();
                
		$handler = $conf->session_handler;

                // Config time is in minutes
		$options['expire'] = ($conf->lifetime) ? $conf->lifetime * 60 : 900;

                $session = TSession::getInstance($handler, $options);

                 if ($session->getState() == 'expired')
                        {
                                $session->restart();
                        }
    
       
	
		return $session;
	}

	/**
	 * Create an database object
	 *
	 * @return  TDatabase object
	 *
	 * @see     TDatabase
	 * @since   5/2014
	 * @deprecated 12.3
	 */
	protected static function _createDbo()
	{
		TLog::add(__METHOD__ . '() is deprecated.', TLog::WARNING, 'deprecated');

		return self::createDbo();
	}

	/**
	 * Create an database object
	 *
	 * @return  TDatabase object
	 *
	 * @see     TDatabase
	 * @since   5/2014
	 */
	protected static function createDbo()
	{
		tinclude('core.database.table');

		$conf = self::getConfig();

		$host = $conf->host;
		$user = $conf->user;
		$password = $conf->password;
		$database = $conf->db;
		$prefix = $conf->dbprefix;
		$driver = $conf->dbtype;
		$debug = $conf->debug;

		$options = array('driver' => $driver, 'host' => $host, 'user' => $user, 'password' => $password, 'database' => $database, 'prefix' => $prefix);

		$db = TDatabase::getInstance($options);

		if ($db instanceof Exception)
		{
			if (!headers_sent())
			{
				header('HTTP/1.1 500 Internal Server Error');
			}
			texit('Database Error: ' . (string) $db);
		}

		if ($db->getErrorNum() > 0)
		{
			die(sprintf('Database connection error (%d): %s', $db->getErrorNum(), $db->getErrorMsg()));
		}

		$db->setDebug($debug);

		return $db;
	}

	/**
	 * Create a mailer object
	 *
	 * @return  TMail object
	 *
	 * @see     TMail
	 * @since   5/2014
	 * @deprecated 12.3
	 */
	protected static function _createMailer()
	{
		TLog::add(__METHOD__ . '() is deprecated.', TLog::WARNING, 'deprecated');

		return self::createMailer();
	}

	/**
	 * Create a mailer object
	 *
	 * @return  TMail object
	 *
	 * @see     TMail
	 * @since   5/2014
	 */
	protected static function createMailer()
	{
		$conf = self::getConfig();

		$smtpauth = ($conf->get('smtpauth') == 0) ? null : 1;
		$smtpuser = $conf->get('smtpuser');
		$smtppass = $conf->get('smtppass');
		$smtphost = $conf->get('smtphost');
		$smtpsecure = $conf->get('smtpsecure');
		$smtpport = $conf->get('smtpport');
		$mailfrom = $conf->get('mailfrom');
		$fromname = $conf->get('fromname');
		$mailer = $conf->get('mailer');

		// Create a TMail object
		$mail = TMail::getInstance();

		// Set default sender without Reply-to
		$mail->SetFrom(TMailHelper::cleanLine($mailfrom), TMailHelper::cleanLine($fromname), 0);

		// Default mailer is to use PHP's mail function
		switch ($mailer)
		{
			case 'smtp':
				$mail->useSMTP($smtpauth, $smtphost, $smtpuser, $smtppass, $smtpsecure, $smtpport);
				break;

			case 'sendmail':
				$mail->IsSendmail();
				break;

			default:
				$mail->IsMail();
				break;
		}

		return $mail;
	}

	/**
	 * Create a language object
	 *
	 * @return  TLanguage object
	 *
	 * @see     TLanguage
	 * @since   5/2014
	 * @deprecated 12.3
	 */
	protected static function _createLanguage()
	{
		TLog::add(__METHOD__ . ' is deprecated.', TLog::WARNING, 'deprecated');

		return self::createLanguage();
	}



	/**
	 * Create a document object
	 *
	 * @return  TDocument object
	 *
	 * @see     TDocument
	 * @since   5/2014
	 * @deprecated 12.3
	 */
	protected static function _createDocument()
	{
		TLog::add(__METHOD__ . ' is deprecated.', TLog::WARNING, 'deprecated');

		return self::createDocument();
	}

	/**
	 * Create a document object
	 *
	 * @return  TDocument object
	 *
	 * @see     TDocument
	 * @since   5/2014
	 */
	protected static function createDocument()
	{
		
		$attributes = array('charset' => 'utf-8', 'lineend' => 'unix', 'tab' => '  ', 'language' => '',
			'direction' => 'rtl');

		return TDocument::getInstance('json', $attributes);
	}

	/**
	 * Creates a new stream object with appropriate prefix
	 *
	 * @param   boolean  $use_prefix   Prefix the connections for writing
	 * @param   boolean  $use_network  Use network if available for writing; use false to disable (e.g. FTP, SCP)
	 * @param   string   $ua           UA User agent to use
	 * @param   boolean  $uamask       User agent masking (prefix Mozilla)
	 *
	 * @return  TStream
	 *
	 * @see TStream
	 * @since   5/2014
	 */
	public static function getStream($use_prefix = true, $use_network = true, $ua = null, $uamask = false)
	{
		tinclude('core.filesystem.stream');

		// Setup the context;UA and overwrite
		$context = array();
//		$version = new TVersion;
		// set the UA for HTTP and overwrite for FTP
//		$context['http']['user_agent'] = $version->getUserAgent($ua, $uamask);
		$context['ftp']['overwrite'] = true;

		if ($use_prefix)
		{
			$FTPOptions = TClientHelper::getCredentials('ftp');
			$SCPOptions = TClientHelper::getCredentials('scp');

			if ($FTPOptions['enabled'] == 1 && $use_network)
			{
				$prefix = 'ftp://' . $FTPOptions['user'] . ':' . $FTPOptions['pass'] . '@' . $FTPOptions['host'];
				$prefix .= $FTPOptions['port'] ? ':' . $FTPOptions['port'] : '';
				$prefix .= $FTPOptions['root'];
			}
			elseif ($SCPOptions['enabled'] == 1 && $use_network)
			{
				$prefix = 'ssh2.sftp://' . $SCPOptions['user'] . ':' . $SCPOptions['pass'] . '@' . $SCPOptions['host'];
				$prefix .= $SCPOptions['port'] ? ':' . $SCPOptions['port'] : '';
				$prefix .= $SCPOptions['root'];
			}
			else
			{
				$prefix = TPATH_ROOT . '/';
			}

			$retval = new TStream($prefix, TPATH_ROOT, $context);
		}
		else
		{
			$retval = new TStream('', '', $context);
		}

		return $retval;
	}
}
