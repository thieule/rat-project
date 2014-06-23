<?php
/* 
 * This is class process business logic for controller user
 * @author Thieu.LeQuang
 * @since       6/2014
 */

class UserTBLogic
{
    /**
     * Get status login
     * @return boolean
     */
    public function getLoginStatus()
    {
        $session = T::getSession();
        $loginStatus = $session->get('login');
        return !empty($loginStatus)?true:false;
    }
    /**
     * Get login user 
     * @return Object User
     */
    public function getUser()
    {
        
       $session = T::getSession();
       if(self::getLoginStatus())
         return $session->get('user');
       else
         return null;
    }
           
    
}
