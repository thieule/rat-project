<?php
/* 
 * User Model
 * @author Thieu.LeQuang
 * @since       6/2014
 */

class UserTOutPut
{
    public $lastname = '';
    public $firstname = '';
    public $email = '';
    public $username = '';
    
}

class UserTInPut
{
    public $id = 0;
    public $fullname = '';
    public $firstname = '';
    public $lastname = '';
    public $username = '';
    public $email = '';
    public $active = '';
    public $created_by = '';
    public $created_date = '';
    public $employerId = '';
    public function __construct($ignore = array()){
        
    }
            
}

