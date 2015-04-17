<?php
require_once api_get_path( SYS_PATH ) . 'main/core/model/ecommerce/LpModulePackModel.php';
class LpModulePackDao
{    
    public static function create()
    {
        return new LpModulePackDao();
    }
    
    public function getByEcommerceItemId( $itemId, $course = null )
    {        
        $itemId = intval($itemId, 10);
     
        $tableLpModulePacks = Database::get_main_table( TABLE_MAIN_ECOMMERCE_LP_MODULE_PACKS );
        $sql = "SELECT lmp.* FROM  $tableLpModulePacks AS lmp WHERE lmp.ecommerce_items_id = '$itemId' ";
        if(!is_null($course)){
           $sql .= " AND lmp.lp_module_course_code= '$course' ";
        }
        $rsModules = Database::query( $sql );
        $row = TRUE;
        while ( $row )
        {
            $row = Database::fetch_object( $rsModules, 'LpModulePackModel' );
            if ( $row !== FALSE )
            {
                $response[] = $row;
            }
        }        
        return $response;        
    }   
}