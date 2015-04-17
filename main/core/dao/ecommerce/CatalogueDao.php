<?php

require_once api_get_path( SYS_PATH ) . 'main/core/model/ecommerce/CatalogueModel.php';
require_once api_get_path( SYS_PATH ) . 'main/core/model/settings/SettingsCurrentModel.php';
require_once api_get_path( SYS_PATH ) . 'main/core/dao/settings/SettingsCurrentDao.php';
require_once api_get_path( SYS_PATH ) . 'main/core/model/ecommerce/EcommerceAbstract.php';

class CatalogueDao
{
    public static function create()
    {
        return new CatalogueDao();
    }
    
    public function getFirstCatalogue()
    {
        $tableEcommerceCatalogue = Database::get_main_table( TABLE_MAIN_CATALOGUE );
        $sql = "SELECT * FROM $tableEcommerceCatalogue as cat ORDER BY cat.id LIMIT 1;";
        $result = Database::query( $sql, __FILE__, __LINE__ );        
        $obj = CatalogueFactory::getObject();        
        return Database::fetch_object( $result, get_class($obj) );
    }
    
    public function getOptionsCurrency()
    {
        $response = array ();
        $tblSettingsOptions = Database::get_main_table( TABLE_MAIN_SETTINGS_OPTIONS );
        $sql = "SELECT * FROM $tblSettingsOptions AS so WHERE so.variable = 'e_commerce_catalog_currency'";
        
        $rsCourses = Database::query( $sql );
        $row = TRUE;
        
        $currentGateWay = SettingsCurrentModel::create()->getCurrentPaymentGateway();
        
        while ( $row )
        {
            $row = Database::fetch_object( $rsCourses );
            if ( $row !== FALSE )
            {
                
                if ( ($currentGateWay->selected_value == EcommerceAbstract::E_COMMERCE_LIB_GATEWAY_PAYPAL) && ($row->value == Currency::ISO_CODE_EURO) )
                {
                    continue;
                }
                
                $response[$row->value] = $row;               
            }
        }
        
        return $response;
    }

}