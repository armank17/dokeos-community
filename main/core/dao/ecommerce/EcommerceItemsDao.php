<?php

class EcommerceItemsDao
{
    public static function create()
    {
        return new EcommerceItemsDao(); 
    }
    
    public function getByCourseCode( $courseCode )
    {
        return $this->getItemByCodeAndType($courseCode, CatalogueModel::TYPE_COURSE );  
    }
    
    public function getByModuleCode( $courseCode )
    {
        return $this->getItemByCodeAndType($courseCode, CatalogueModel::TYPE_MODULES );
    }
    
    public function getBySessionCode( $courseCode )
    {
        return $this->getItemByCodeAndType($courseCode, CatalogueModel::TYPE_SESSION );
    }
    
    
    
    public function getItemByCodeAndType($code, $type)
    {
        $tableEcommerceItems = Database::get_main_table( TABLE_MAIN_ECOMMERCE_ITEMS );
        $sql = "SELECT * FROM $tableEcommerceItems as ec WHERE ec.code= '$code' AND item_type = '". $type ."'LIMIT 1;";
        $result = Database::query( $sql, __FILE__, __LINE__ );
        return  Database::fetch_object( $result);
    }

}
