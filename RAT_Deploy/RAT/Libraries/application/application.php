<?php
/**
 * @package     RAT Soft
 * @subpackage  Application
 */

// no direct access
defined('_TEXEC') or die;
tinclude('core.application.input');
tinclude('core.event.dispatcher');
tinclude('core.environment.response');

/**
 * Base class for a RAT application.
 *
 * Acts as a Factory class for application specific objects and provides many
 * supporting API functions. Derived clases should supply the route(), dispatch()
 * and render() functions.
 *
 * @package     RAT Soft
 * @subpackage  Application
 * @since       5/2014
 * @author Thieu.LeQuang
 */
class TApplication extends TObject
{
	/**
	 * The client identifier.
	 *
	 * @var    integer
	 * @since  5/2014
	 */
	protected $_clientId = null;

	/**
	 * The module name.
	 *
	 * @var    integer
	 * @since  5/2014
	 */
	public $modulename = 'index';
	
        
	/**
	 * The application message queue.
	 *
	 * @var    array
	 * @since  5/2014
	 */
	protected $_messageQueue = array();

	/**
	 * The name of the application.
	 *
	 * @var    array
	 * @since  5/2014
	 */
	protected $_name = null;

	/**
	 * The scope of the application.
	 *
	 * @var    string
	 * @since  5/2014
	 */
	public $scope = null;

	/**
	 * The time the request was made.
	 *
	 * @var    date
	 * @since  5/2014
	 */
	public $requestTime = null;

	/**
	 * The time the request was made as Unix timestamp.
	 *
	 * @var    integer
	 * @since  5/2014
	 */
	public $startTime = null;

	/**
	 * The application input object.
	 *
	 * @var    TInput
	 * @since  5/2014
	 */
	public $input = null;
        
        /**
	 * The application router object.
	 *
	 * @var    TRouter
	 * @since  5/2014
	 */
	private  $_router = null;
	
	/**
	 * The application controller object.
	 *
	 * @var    TRouter
	 * @since  5/2014
	 */
	private   $_controller;
	
        
     /**
	 * The application router object.
	 *
	 * @var    TRouter
	 * @since  5/2014
	 */
	public static $contents = null;


	/**
	 * @var    object  TApplication instances container.
	 * @since  5/2014
	 */
	protected static $instance = null;

	/**
	 * Class constructor.
	 *
	 * @param   array  $config  A configuration array including optional elements such as session
	 * session_name, clientId and others. This is not exhaustive.
	 *
	 * @since   5/2014
	 */
	public function __construct($config = array())
	{
		tinclude('core.error.profiler');

		// Only set the clientId if available.
		if (isset($config['clientId']))
		{
			$this->_clientId = $config['clientId'];
		}

		// Enable sessions by default.
		if (!isset($config['session']))
		{
			$config['session'] = true;
		}

		// Create the input object
		if (class_exists('TInput'))
		{
			$this->input = new TInput;
		}

		// Set the session default name.
		if (!isset($config['session_name']))
		{
			$config['session_name'] = $this->_name;
		}

		// Set the default configuration file.
		if (!isset($config['config_file']))
		{
			$config['config_file'] = 'configuration.php';
		}

		$this->requestTime = gmdate('Y-m-d H:i');

	}

	/**
	 * Returns the global TApplication object, only creating it if it
	 * doesn't already exist.
	 *
	 * @param   mixed   $client  A client identifier or name.
	 * @param   array   $config  An optional associative array of configuration settings.
	 * @param   string  $prefix  A prefix for class names
	 *
	 * @return  TApplication A TApplication object.
	 *
	 * @since   5/2014
	 */
	public static function getInstance($config = array())
	{
		if (empty(self::$instance))
		{        
          $instance = new TApplication($config);
          self::$instance = &$instance;
		}

		return self::$instance;
	}

	/**
	 * Initialise the application.
	 *
	 * @param   array  $tokens  array to map token and module name
	 *
	 * @return  void
	 *
	 * @since   5/2014
	 */
	public function Initialise($tokens=array())
	{
		$strQuery = TRequest::getVar('query');
                
        $params = explode('/', $strQuery);
        
        if(empty($tokens[$params[0]]))  texit('token invalid');
			
        $this->modulename = $tokens[$params[0]];
                
        $this->_router = TRouter::getInstance(array('module'=>$tokens[$params[0]])); 
                
        $this->_router->name = $tokens[$params[0]];
 
        $this->_router->strQuery = $strQuery;
        
        $this->_router->parse();
        
        
        $this->_controller = TController::getInstance(
        											$this->_router->params['controller'],
        											array(
        													'module'=> $this->modulename,
        													'controller'=> $this->_router->params['controller'],
        													'action'=> $this->_router->params['action']) );
        
        
	}
        
	
	/**
	 * excude task in the application.
	 
	 *
	 * @return  void
	 *
	 * @since   5/2014
	 */
	
	public function excude()
	{
		$actions = $this->_controller->getActions();
                
		$this->_controller->runAction($actions);
	}
        
        
	/**
	 * Render the application.
	 *
	 * Rendering is the process of pushing the document buffers into the template
	 * placeholders, retrieving data from the document and pushing it into
	 * the TResponse buffer.
	 *
	 * @return  void
	 *
	 * @since   5/2014
	 */
	public function render()
	{
            $config = T::getConfig();
            $document = T::getDocument();
            return $document->render($this->_controller->view,$config->template);
	}

	/**
	 * Exit the application.
	 *
	 * @param   integer  $code  Exit code
	 *
	 * @return  void     Exits the application.
	 *
	 * @since    5/2014
	 */
	public function close($code = 0)
	{
		exit($code);
	}


	/**
	 * Enqueue a system message.
	 *
	 * @param   string  $msg   The message to enqueue.
	 * @param   string  $type  The message type. Default is message.
	 *
	 * @return  void
	 *
	 * @since   5/2014
	 */
	public function enqueueMessage($msg, $type = 'message')
	{
		// For empty queue, if messages exists in the session, enqueue them first.
		if (!count($this->_messageQueue))
		{
			$session = T::getSession();
			$sessionQueue = $session->get('application.queue');

			if (count($sessionQueue))
			{
				$this->_messageQueue = $sessionQueue;
				$session->set('application.queue', null);
			}
		}

		// Enqueue the message.
		$this->_messageQueue[] = array('message' => $msg, 'type' => strtolower($type));
	}

	/**
	 * Get the system message queue.
	 *
	 * @return  array  The system message queue.
	 *
	 * @since   5/2014
	 */
	public function getMessageQueue()
	{
		// For empty queue, if messages exists in the session, enqueue them.
		if (!count($this->_messageQueue))
		{
			$session = T::getSession();
			$sessionQueue = $session->get('application.queue');

			if (count($sessionQueue))
			{
				$this->_messageQueue = $sessionQueue;
				$session->set('application.queue', null);
			}
		}

		return $this->_messageQueue;
	}


	/**
	 * Method to get the application name.
	 *
	 * The dispatcher name is by default parsed using the classname, or it can be set
	 * by passing a $config['name'] in the class constructor.
	 *
	 * @return  string  The name of the dispatcher.
	 *
	 * @since   5/2014
	 */
	public function getName()
	{
		$name = $this->_name;

		if (empty($name))
		{
			$r = null;
			if (!preg_match('/J(.*)/i', get_class($this), $r))
			{
				TError::raiseError(500, Text::_('JLIB_APPLICATION_ERROR_APPLICATION_GET_NAME'));
			}
			$name = strtolower($r[1]);
		}

		return $name;
	}

	/**
	 * Gets a user state.
	 *
	 * @param   string  $key      The path of the state.
	 * @param   mixed   $default  Optional default value, returned if the internal value is null.
	 *
	 * @return  mixed  The user state or null.
	 *
	 * @since   5/2014
	 */
	public function getUserState($key, $default = null)
	{
		$session = T::getSession();
		$registry = $session->get('registry');

		if (!is_null($registry))
		{
			return $registry->get($key, $default);
		}

		return $default;
	}

	/**
	 * Sets the value of a user state variable.
	 *
	 * @param   string  $key    The path of the state.
	 * @param   string  $value  The value of the variable.
	 *
	 * @return  mixed  The previous state, if one existed.
	 *
	 * @since   5/2014
	 */
	public function setUserState($key, $value)
	{
		$session = T::getSession();
		$registry = $session->get('registry');

		if (!is_null($registry))
		{
			return $registry->set($key, $value);
		}

		return null;
	}

	/**
	 * Gets the value of a user state variable.
	 *
	 * @param   string  $key      The key of the user state variable.
	 * @param   string  $request  The name of the variable passed in a request.
	 * @param   string  $default  The default value for the variable if not found. Optional.
	 * @param   string  $type     Filter for the variable, for valid values see {@link JFilterInput::clean()}. Optional.
	 *
	 * @return  The request user state.
	 *
	 * @since   5/2014
	 */
	public function getUserStateFromRequest($key, $request, $default = null, $type = 'none')
	{
		$cur_state = $this->getUserState($key, $default);
		$new_state = TRequest::getVar($request, null, 'default', $type);

		// Save the new value only if it was set in this request.
		if ($new_state !== null)
		{
			$this->setUserState($key, $new_state);
		}
		else
		{
			$new_state = $cur_state;
		}

		return $new_state;
	}

	/**
	 * Registers a handler to a particular event group.
	 *
	 * @param   string  $event    The event name.
	 * @param   mixed   $handler  The handler, a function or an instance of a event object.
	 *
	 * @return  void
	 *
	 * @since   5/2014
	 */
	public static function registerEvent($event, $handler)
	{
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->register($event, $handler);
	}

	/**
	 * Calls all handlers associated with an event group.
	 *
	 * @param   string  $event  The event name.
	 * @param   array   $args   An array of arguments.
	 *
	 * @return  array  An array of results from each function call.
	 *
	 * @since   5/2014
	 */
	public function triggerEvent($event, $args = null)
	{
		$dispatcher = TDispatcher::getInstance();

		return $dispatcher->trigger($event, $args);
	}

	
	/**
	 * Gets the name of the current template.
	 *
	 * @param   array  $params  An optional associative array of configuration settings
	 *
	 * @return  string  System is the fallback.
	 *
	 * @since   5/2014
	 */
	public function getTemplate($params = false)
	{
		return 'system';
	}

	/**
	 * Returns the application TRouter object.
	 *
	 * @param   string  $name     The name of the application.
	 * @param   array   $options  An optional associative array of configuration settings.
	 *
	 * @return  TRouter  A TRouter object
	 *
	 * @since   5/2014
	 */
	static public function getRouter($name = null)
	{
//            if(!empty($this->_router))
//            {
//                return $this->_router;
//            }
            tinclude('application.router');
            $path = TPATH_ROOT .DS. 'Modules'.DS.$name.DS.'router.php';
            if (file_exists($path))
            {
                    include_once $path;
                    // Create a TRouter object
                    $classname = ucfirst($name).'TRouter';
                    $instance = new $classname();
                    return $instance;
            }
            else
            {
                return new TRouter();
            }
            
	
	}

	/**
	 * This method transliterates a string into an URL
	 * safe string or returns a URL safe UTF-8 string
	 * based on the global configuration
	 *
	 * @param   string  $string  String to process
	 *
	 * @return  string  Processed string
	 *
	 * @since   5/2014
	 */
	static public function stringURLSafe($string)
	{
		if (T::getConfig()->get('unicodeslugs') == 1)
		{
			$output = TFilterOutput::stringURLUnicodeSlug($string);
		}
		else
		{
			$output = TFilterOutput::stringURLSafe($string);
		}

		return $output;
	}

	/**
	 * Provides a secure hash based on a seed
	 *
	 * @param   string  $seed  Seed string.
	 *
	 * @return  string  A secure hash
	 *
	 * @since   5/2014
	 */
	public static function getHash($seed)
	{
		return md5($seed);
	}

	/**
	 * Create the configuration registry.
	 *
	 * @param   string  $file  The path to the configuration file
	 *
	 * @return   object  A TConfig object
	 *
	 * @since   5/2014
	 */
	protected function _createConfiguration($file)
	{
		TLoader::register('TConfig', $file);

		// Create the TConfig object.
		$config = new TConfig;

		return $config;
	}


	
	/**
	 * Gets the client id of the current running application.
	 *
	 * @return  integer  A client identifier.
	 *
	 * @since   5/2014
	 */
	public function getClientId()
	{
		return $this->_clientId;
	}

	/**
	 * Is admin interface?
	 *
	 * @return  boolean  True if this application is administrator.
	 *
	 * @since   5/2014
	 */
	public function isAdmin()
	{
		return ($this->_clientId == 1);
	}

	/**
	 * Is site interface?
	 *
	 * @return  boolean  True if this application is site.
	 *
	 * @since   5/2014
	 */
	public function isSite()
	{
		return ($this->_clientId == 0);
	}

	/**
	 * Method to determine if the host OS is  Windows
	 *
	 * @return  boolean  True if Windows OS
	 *
	 * @since   5/2014
	 */
	public static function isWinOS()
	{
		return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
	}

	/**
	 * Returns the response as a string.
	 *
	 * @return  string  The response
	 *
	 * @since   5/2014
	 */
	public function __toString()
	{
		$compress = $this->getCfg('gzip', false);

		return TResponse::toString($compress);
	}
}
