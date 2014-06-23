<?php
/**
 * @package     T.Platform
 * @subpackage  Application
 *
 */

defined('T_PLATFORM') or die;

/**
 * Class to create and parse routes
 *
 * @package     T.Platform
 * @subpackage  Application
 * @author Thieu.LeQuang
 * @since       5/2014
 */
class TRouter
{
    
	/**
	 * Array to hold the object instances
	 *
	 * @var    array
	 * @since  5/2014
	 */
	public static $instances = array();
	
	/**
	 * An array of params
	 *
	 * @var     array
	 * @since   5/2014
	 */
	public $params = array();
        
        /**
	 * string
	 *
	 * @var     array
	 * @since   5/2014
	 */
	protected $_module = null;
        

        /**
	 * An name of router
	 *
	 * @var     string
	 * @since   5/2014
	 */
        public $name = null;
        
        /**
	 * An string of params
	 *
	 * @var     string
	 * @since   5/2014
	 */
	public $strQuery = null;

        /**
	 * An array of router defined
	 *
	 * @var     array
	 * @since   5/2014
	 */
	public $routerDefined = null;

	/**
	 * Function to convert a route to an internal URI
	 *
	 * @return  array
	 *
	 * @since   5/2014
	 */
	public function parse()
	{
            
            $queryElements = explode('/', $this->strQuery);
                    
            if(empty($this->routerDefined[$queryElements[1]]))
            {
                Texit('Request is not valid!');
            }else{
                $this->params['module'] = $this->name;
                $this->params['controller'] = $this->routerDefined[$queryElements[1]][0];
                $this->params['action'] = $this->routerDefined[$queryElements[1]][1];
            }
            unset($queryElements[0]);
            unset($queryElements[1]);
            
            $vars = array();
            
            foreach((array)$queryElements as $key=>$param){
                
                $temp = explode('=', $param);   
                if(count($temp)>0){
                    if(!empty($temp[0])){
                        $vars[$temp[0]] = empty($temp[1])?'':$temp[1];
                        $this->params = array_merge($this->params,$vars);    
                    }
                        
                    
                    
                }
                
                
                
            }
                    
	}
	
	/**
	 * Returns a reference to a TRouter object
	 *
	 * @param   string  $extension  Name of the categories extension
	 * @param   array   $options    An array of options
	 *
	 * @return  TRouter         TRouter object
	 *
	 * @since   5/2014
	 */
	public static function getInstance($options = array())
	{
		$hash = md5($options['module'] . serialize($options));
	
		if (isset(self::$instances[$hash]))
		{
			return self::$instances[$hash];
		}
	
		$classname = ucfirst($options['module']). 'TRouter';
	
		if (!class_exists($classname))
		{
			$path = TPATH_MODULE . DS. ucfirst($options['module']) .DS.'router.php';
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
		self::$instances[$hash]->name = $options['module'];
		
		return self::$instances[$hash];
	}

	/**
	 * Encode route segments
	 *
	 * @param   array  $segments  An array of route segments
	 *
	 * @return  array  Array of encoded route segments
	 *
	 * @since   5/2014
	 */
	protected function _encodeSegments($segments)
	{
		$total = count($segments);
		for ($i = 0; $i < $total; $i++)
		{
			$segments[$i] = str_replace(':', '-', $segments[$i]);
		}

		return $segments;
	}

	/**
	 * Decode route segments
	 *
	 * @param   array  $segments  An array of route segments
	 *
	 * @return  array  Array of decoded route segments
	 *
	 * @since 5/2014
	 */
	protected function _decodeSegments($segments)
	{
		$total = count($segments);
		for ($i = 0; $i < $total; $i++)
		{
			$segments[$i] = preg_replace('/-/', ':', $segments[$i], 1);
		}

		return $segments;
	}
        
}
