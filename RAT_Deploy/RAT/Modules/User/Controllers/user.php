<?php
/* 
 * User Controller
 * @author Thieu.LeQuang
 * @since       6/2014
 */

class UserTController extends TController
{
    /**
     * Get list user
     * return JSON file
     */
    public function actionList(){
       
        $model = $this->getModel();
        $list = $model->getList();
               
        $this->setView(array(
                            'data'=>$list
                            ));
        
    }
    
    /**
     * Add new user
     * return JSON file
     */
    public function actionAdd(){
        
         $model = $this->getModel();
         $list = $model->getList();
         $this->blogic->caculator();
         $this->setView(array(
                            'notify'=>array(
                                            'msg'=>'loiroi',
                                            'type'=>'error'   
                            ),
                            'data'=>$list
                            ));
    }
    /**
     * Login 
     * return JSON file
     */
    public function actionLogin(){
         $username = TRequest::getVar('login');
         $password = TRequest::getVar('password');
         $model = $this->getModel();
         $loginStatus = $model->checkLogin($username,$password);
         $user = $model->getUser();
         $this->setView(array(
                            'status'=>$loginStatus,
         					'user' =>$user
                            ));    
    }
    
    /**
     * Get User by Id
     * return JSON file
     */
    public function actionUserget(){
         $userId = TRequest::getVar('user_id');
         $model = $this->getModel();
         $user = $model->getUser($userId);
         
        $this->setView(array(
                            'user'=>$user
                            ));    
    }
    /**
     * Logout action
     * return JSON file
     */
    public function actionLogout(){
         $userId = TRequest::getVar('user_id');
         
        $this->setView(array(
                            'user_id'=>$userId
                            ));    
    }
    
    /**
     * Heart Beat action
     * To check heart of api
     */
    public function actionHeartbeat()
    {
        $this->setView(array('alive'=>1));    
    }
    
     /**
      * Export array to excel
      * 
      * @param type $data
      * return csv file
      */
     public function actionExport()
     {
         $model = $this->getModel();
         $list = $model->getList();
         $this->blogic->exportExcelUserData($list);
        
     }
    
 
    
}


