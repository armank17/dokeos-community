<?php
class application_demo_models_UserModel
{
 
    private $_tableUsers;           
    private $_ado;
   
    public function __construct() {    
        $this->_tableUsers = Database::get_main_table(TABLE_MAIN_USER);        
        $this->_ado = appcore_db_DB::conn();        
    }
    
    public function getAll() {       
        $sql = "SELECT user_id, firstname, lastname FROM {$this->_tableUsers} WHERE status = ?";
        return  $this->_ado->GetAll($sql, array(STUDENT));
    }
    
    public function save() {

		    
    }
    
    public function delete() {
        
    }
    
}
