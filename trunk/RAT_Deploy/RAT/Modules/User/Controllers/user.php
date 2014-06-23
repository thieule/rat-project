<?php
/* 
 * User Controller
 * @author Thieu.LeQuang
 * @since       6/2014
 */

class UserTController extends TController
{
    
    public function actionList(){
       
        $model = $this->getModel();
        $list = $model->getList();
               
        $this->setView(array(
                            'data'=>$list
                            ));
        
    }
    
    
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
    
    
    public function actionUserget(){
         $userId = TRequest::getVar('user_id');
         $model = $this->getModel();
         $user = $model->getUser($userId);
         
        $this->setView(array(
                            'user'=>$user
                            ));    
    }
    
    public function actionLogout(){
         $userId = TRequest::getVar('user_id');
         
        $this->setView(array(
                            'user_id'=>$userId
                            ));    
    }
    
    
    public function actionHeartbeat()
    {
        $this->setView(array('alive'=>1));    
    }
 
    
}


