<?php
/* 
 * Router User Module
 * @author Thieu.LeQuang
 * @since       6/2014
 */

class UserTRouter extends TRouter
{
    
    public function __construct() {
        
      $this->routerDefined =  array(
            'list'=>array('user','list'),
            'newuser'=>array('user','add'),
             'login'=>array('user','login'),
            'getuser'=>array('user','userget'),
           'logout'=>array('user','logout'),
          'heartbeat'=>array('user','heartbeat'),
        );
    }
    
    public function parse() {           
        parent::parse();
    }
}


 