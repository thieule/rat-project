<?php
/* 
 * User Model
 * @author Thieu.LeQuang
 * @since       6/2014
 */

class UserTModel extends TModel
{
    protected $_tableName = 'user';
    protected $_keyId = 'id';
    public function __construct(&$db) {
        parent::__construct(array('tablename'=>$this->_tableName,'key'=>$this->_keyId),$db);
    }
    
    public function getList(){
        
        $this->_db->setQuery('select e.* from #__user as u 
        									left join #__employer e on e.user_id = u.id');
        $list = $this->_db->loadObjectList();
        return $list;
    }
    /**
     * Check login information
     * @param type $username
     * @param type $password
     * @return boolean
     */
    public function checkLogin($username,$password)
    {
        $session = T::getSession();
        
        $password = md5($password);
        $this->_db->setQuery('select * from #__user where username='.$this->_db->Quote($username).' and password = '.$this->_db->Quote($password));
     
        $user = $this->_db->loadObject();
        
        if(!empty($user->id)){
            $session->set('login',md5($user->id.'login'));
            $session->set('user',$user);
            return 1;
        } 
         $session->set('login','');
         $session->set('user','');    
        return 0;
    }
    
    
    /**
     * get login information
     * @param type $id
     * @return boolean
     */
    public function getUser($id = 0)
    {
    	$session = T::getSession();

    	if(empty($id)) return  $session->get('user');
    }
    
    
    
    
    
}
