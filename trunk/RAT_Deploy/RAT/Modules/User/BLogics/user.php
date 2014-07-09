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
    
    /**
     * clear data
     * @param type $str
     */
     public function cleanData(&$str) {
         $str = preg_replace("/\t/", "\\t", $str); 
         $str = preg_replace("/\r?\n/", "\\n", $str); 
         if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
         return $str;
     } 
     
     /**
      * This is function to export to excel file user data
      * @param type $list
      */
    public function exportExcelUserData($list)
    {
         // filename for download 
         $filename = "website_data_" . date('Ymd') . ".xls"; 
         header("Content-Disposition: attachment; filename=\"$filename\""); 
         header("Content-Type: application/vnd.ms-excel; charset=utf-8"); 
         $flag = false; 
        foreach($list as $row) { 
             if(!$flag) { 
               // display field/column names as first row 
                echo implode("\t", array_keys(get_object_vars($row))) . "\r\n"; 
                $flag = true; 
             } 
             $values = array();
             $fields = get_object_vars($row);
          
             foreach($fields  as $field){
                 $values[] = $this->cleanData($field);
             }
             echo implode("\t", array_values($values)) . "\r\n"; 
             
         } 
         exit;
    }
    
   
           
    
}
