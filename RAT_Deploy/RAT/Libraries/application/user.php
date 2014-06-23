<?php
/**
 * @package     T.Platform
 * @subpackage  Application
 * 
 */

defined('T_PLATFORM') or die;

/**
 * TUser Class.
 *
 * @package     T.Platform
 * @subpackage  Application
 * @author Thieu.LeQuang
 * @since       5/2014
 */
class TUser
{
	/**
	 * Array to hold the object instances
	 *
	 * @var    array
	 * @since  5/2014
	 */
	public static $instances = array();

        
        /**
	 * Name of controller
	 *
	 * @var    string
	 * @since  5/2014
	 */
	public $name = null;
        
        /**
	 * Name of module
	 *
	 * @var    string
	 * @since  5/2014
	 */
	public $modulename = null;

	/**
	 * Object TTable
	 *
	 * @var    TTable
	 * @since  5/2014
	 */
	protected $_table;
      
        /**
	 * Object 
	 *
	 * @var    
	 * @since  5/2014
	 */
	public $blogic;
      
        /**
	 * Object TModel
	 *
	 * @var    TModel
	 * @since  5/2014
	 */
	protected $_model;
      
        /**
	 * Object BLogic
	 *
	 * @var    BLogic
	 * @since  5/2014
	 */
	protected $_blogic;

	/**
	 * object controller
	 *
	 * @var    Tcontroller
	 * @since  5/2014
	 */
	protected $_controller = null;

	/**
	 * Name of the action
	 *
	 * @var    array
	 * @since  5/2014
	 */
	protected $_actions = null;


	/**
	 * Name of the view
	 *
	 * @var    string
	 * @since  5/2014
	 */
	protected $_view = null;

	/**
	 * Array of options
	 *
	 * @var    array
	 * @since  5/2014
	 */
	protected $_options = null;

        public function __construct($name = '') {
              $this->name = $name;
         }
     
	/**
	 * Returns a reference to a TController object
	 *
	 * @param   string  $extension  Name of the categories extension
	 * @param   array   $options    An array of options
	 *
	 * @return  TController         TController object
	 *
	 * @since   5/2014
	 */
	public static function getInstance($autho, $options = array())
	{
		$hash = md5($controller . serialize($options));

		if (isset(self::$instances[$hash]))
		{
			return self::$instances[$hash];
		}

		$classname = ucfirst($controller). 'TController';

		if (!class_exists($classname))
		{
			$path = TPATH_MODULE .DS.ucfirst($options['module']).DS. 'Controllers' .DS. $controller.'.php';
			
			if (is_file($path))
			{
				include_once $path;
			}
			else
			{
				return false;
			}
		}

		self::$instances[$hash] = new $classname($options);
                self::$instances[$hash]->name = $controller;
                self::$instances[$hash]->modulename = $options['module'];
                $bgclassname = ucfirst($controller). 'TBlogic';
                if (!class_exists($bgclassname))
		{
                    $path = TPATH_MODULE .DS.ucfirst($options['module']).DS. 'BLogics' .DS. $controller.'.php';
                    if (is_file($path))
			include_once $path;
                }
                self::$instances[$hash]->blogic = class_exists($bgclassname)?new $bgclassname():null;
		self::$instances[$hash]->registerAction($options['action']);
                
		
		return self::$instances[$hash];
	}
	
	
	
	
	/**
	 * Run actions in controller
	 * @return void
	 */
		public function runAction($actions){
			$actions = (array)$actions;
			$contents = array();
			
			if(!count($actions)) texit('No action registed');
			 
			foreach($actions as $action){
				$actionFunction = 'action'.  ucfirst($action);
				if(!method_exists($this, $actionFunction)) texit('Not exit action '.$action);
				
				ob_start();
					
				$this->$actionFunction();
					
				$contents[$action] = ob_get_contents();
					
				ob_end_clean();
			}
			
			return $contents;
		}
		

		/**
		 *  Get actions in controller
		 * @return array actions
		 */
		public function getActions(){
		
			return $this->_actions;
		}
		
		
		/**
		 * Register action in controller
		 * @return void
		 */
		public function registerAction($action){
				
			$this->_actions[] = $action;
		}
		
        
}
