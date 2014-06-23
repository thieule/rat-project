<?php
/**
 * @package     T.Platform
 * @subpackage  Application
 *
 */

defined('T_PLATFORM') or die;

/**
 * Application helper functions
 *
 * @package     T.Platform
 * @subpackage  Application
 * @author Thieu.LeQuang
 * @since       5/2014
 */
class TApplicationHelper
{
	/**
	 * Return the name of the request controller
	 *
	 * @return  string  controller name
	 *
	 * @since   5/2014
	 */
	public static function getControllerName()
	{
		return TRequest::getCmd('controller');
	}
        
        
        /**
	 * Return the name of the request controller
	 *
	 * @return  string  action name
	 *
	 * @since   5/2014
	 */
	public static function getActionName()
	{
		return TRequest::getCmd('controller');
	}
        
        /**
	 * Return the name of the request controller
	 
	 *
	 * @since   5/2014
	 */
	public static function renderModule($router)
	{
            
            $path = TPATH_ROOT.DS.'Modules'.DS.ucfirst($router->params['module']).DS.'Controllers'.DS.$router->params['controller'].'.php';
            
            if (!file_exists($path)) texit ('Controller '.$router->params['controller'].' not exits!');
            
            ob_start();
            include_once $path;

            // Create a Controller object
            $classname = ucfirst($router->params['controller']).'TController';

            if(!class_exists($classname)) texit ("Class $classname not exits.");

            $instance = new $classname($router->params['controller']);
            $actionname = 'action'.ucfirst($router->params['action']);

            if(!method_exists($instance, $actionname)) texit ("Action $actionname not exits in class $classname.");

            $instance->$actionname();
            $content = ob_get_contents();
            ob_end_clean();
            
            return $content;    
            
            
	}


}
