<?php
class EcommerceUserPrivilegesDao
{
    public static function create()
    {
        return new EcommerceUserPrivilegesDao();
    }
    
    
    public function save( $params )
    {
        $params;
        $userPriviledgesTable = Database::get_main_table( TABLE_MAIN_ECOMMERCE_USER_PRIVILEGES);
        
        $sql = " INSERT INTO $userPriviledgesTable "
          . "(`user_id`, `ecommerce_items_id`, `role`, `group_id`, `tutor_id`, `sort`,`user_course_cat`) 
          VALUES ( '" .$params['user_id'] . "', 
          '" .$params['ecommerce_items_id'] . "',
          '" .$params['role'] . "',  
          '" .$params['group_id'] . "',
          '" .$params['tutor_id'] . "',
          '" .$params['sort'] . "',
          '" .$params['user_course_cat'] . "' );";
        
        Database::query( $sql );
        
        return Database::insert_id();
    }
    
     
}