<?php
/**
 * @package     T.Platform
 * @subpackage  Application
 */

defined('T_PLATFORM') or die;

/**
 * TModel Class.
 *
 * @package     T.Platform
 * @subpackage  Application
 * @author Thieu.LeQuang
 * @since       5/2014
 */
class TModel
{
	
	/**
	 * Array to hold the object instances
	 *
	 * @var    array
	 * @since  5/2014
	 */
	public static $instances = array();
	
	/**
	 * Object TTable
	 *
	 * @var    TTable
	 * @since  5/2014
	 */
	protected $_table;
        
        /**
	 * Object TDatabase
	 *
	 * @var    TTable
	 * @since  5/2014
	 */
	protected $_db;
        
        /**
	 * Array view model
	 *
	 * @var    array
	 * @since  5/2014
	 */
	public static $_vmds = array();
        
	/**
	 * Array of options
	 *
	 * @var    array
	 * @since  5/2014
	 */
	protected $_options = null;

    public function __construct($option = array(),&$db) {
       
        $this->_table = new TTable($option['tablename'],$option['key'],$db);
        $this->_db = $db;
    }
    
    /**
     * Returns a reference to a TModel object
     *
     * @param   string  $extension  Name of the categories extension
     * @param   array   $options    An array of options
     *
     * @return  TController         TController object
     *
     * @since   5/2014
     */
    public static function getInstance($model, $options = array())
    {
    	$hash = md5($model . serialize($options));
    
    	if (isset(self::$instances[$hash]))
    	{
    		return self::$instances[$hash];
    	}
    
    	$classname = ucfirst($model). 'TModel';
    
    	if (!class_exists($classname))
    	{
    		$path = TPATH_MODULE . DS. ucfirst($options['module']) .DS. 'Models' .DS. $model.'.php';
    		if (is_file($path))
    		{
    			include_once $path;
    		}
    		else
    		{
    			return false;
    		}
    	}
        $db = T::getDbo();
    	self::$instances[$hash] = new $classname($db);
    	return self::$instances[$hash];
    }
	
        /**
	 * Returns a reference to a TTable object
	 *
	 *
	 * @return  TTable
	 *
	 * @since   5/2014
	 */
	public function getTable()
	{
		return $this->_table;
	}
        
        /**
	 * set a TTable object
	 *
	 * @since   5/2014
	 */
	public function setTable($table)
	{
		$this->_table = $table;
	}
        
}
