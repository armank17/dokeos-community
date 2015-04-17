<?php
require_once api_get_path( SYS_PATH ) . 'main/core/model/settings/SettingsCurrentModel.php';
class SettingsCurrentDao
{
    private $_tableName;
    
    public static function create()
    {
        return new SettingsCurrentDao();
    }
    
    public function __construct()
    {
        $this->_tableName = Database::get_main_table( TABLE_MAIN_SETTINGS_CURRENT );
    }
    
    /**
     *
     * @param $variableName string
     *            Name of the setting variable to retrieve
     * @param $limit int
     *            (optional) number of rows to retrieve
     * @return array <SettingsCurrentModel>
     */
    public function getSettingsByVariableName( $variableName, $limit = NULL )
    {
        $variableName = trim( $variableName );
        $response = array ();
        
        if ( $variableName != '' )
        {
            $sql = "SELECT ec.* FROM $this->_tableName as ec WHERE ec.course_code= '$variableName'";
            
            if ( ! is_null( $limit ) && is_numeric( $limit ) )
            {
                $limit = intval( $limit, 10 );
                $sql .= " LIMIT $limit;";
            }
            
            $result = Database::query( $sql, __FILE__, __LINE__ );
            
            $row = TRUE;
            while ( $row )
            {
                $row = Database::fetch_object( $result, 'SettingsCurrentModel' );
                if ( $row !== FALSE )
                {
                    $response[] = $row;
                }
            }
        
        }
        
        return $response;
    }
    
    public function getSingleSettingByVariableName( $variableName )
    {
        $response = NULL;
        $variableName = trim( $variableName );
        
        if ( $variableName != '' )
        {
            $temp = $this->getSettingsByVariableName( $variableName, 1 );
            if ( is_array( $temp ) && isset( $temp[0] ) )
            {
                $response = $temp[0];
            }
        
        }
        
        return $response;    
    }

}
