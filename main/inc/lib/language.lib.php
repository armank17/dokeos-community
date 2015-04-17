<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* @author Isaac Flores
* @package dokeos.language
*/

class LanguageManager {

    private function __construct() {
    	//void
    }
    /**
     * Count the number of languages marked as visible
     */
    public static function count_available_languages () {
      $tbl_lang = Database::get_main_table(TABLE_MAIN_LANGUAGE);
      $sql = "SELECT count(*) as count FROM $tbl_lang WHERE available=1";
      $rs = Database::query($sql,__FILE__,__LINE__);
      $row = Database::fetch_array($rs);
      $count_lang = $row['count'];
      return $count_lang;
    }
    
    /**
     * Get countries
     */
    public static function get_countries($code = null,$column = null) {        
        $countries = array();
        $tbl_country = Database::get_main_table(TABLE_MAIN_COUNTRY);        
        $condition = " WHERE 1=1";
        if (isset($code)) {
            $condition .= " AND numcode = '$code'";
        }
        if(is_null($column)) {
           $column = 'numcode';
        } else {
           $column = 'iso';
        }
        $rs = Database::query("SELECT $column, langvar FROM $tbl_country $condition");
        if (Database::num_rows($rs) > 0) {            
            while ($row = Database::fetch_object($rs)) {
                $countries[$row->$column] = get_lang($row->langvar);
            }
        }        
        return $countries;
    }
    
}