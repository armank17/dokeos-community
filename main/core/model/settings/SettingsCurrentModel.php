<?php
class SettingsCurrentModel
{
    protected $_tableName;
    
    public static function create()
    {
        return new SettingsCurrentModel();
    }
    
    public function __construct()
    {
        $this->_tableName = Database::get_main_table( TABLE_MAIN_SETTINGS_CURRENT );
    }
    
    public function getCurrentPaymentGateway()
    {
        
        $sql = "SELECT * FROM $this->_tableName as sc WHERE sc.variable = 'e_commerce'LIMIT 1;";
        $result = Database::query( $sql, __FILE__, __LINE__ );
        
        return Database::fetch_object( $result);
    }
    
    public function getCurrentGlobalCurrency()
    {
    
        $sql = "SELECT * FROM $this->_tableName as sc WHERE sc.variable = 'e_commerce_catalog_currency'LIMIT 1;";
        $result = Database::query( $sql, __FILE__, __LINE__ );
    
        return Database::fetch_object( $result);
    }

}
