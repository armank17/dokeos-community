<?php

//require_once  'usermanager.lib.php';

class TimeZone{
       
    public  static function TimeZoneList(){
        $timezoneIdentifiers = DateTimeZone::listIdentifiers();
        $current_uct = new DateTimeZone('UTC');
        $utcTime = new DateTime('now', $current_uct);

        $tempTimezones = array();
        foreach ($timezoneIdentifiers as $timezoneIdentifier) {
        $currentTimezone = new DateTimeZone($timezoneIdentifier);

        $tempTimezones[] = array(
            'offset' => (int)$currentTimezone->getOffset($utcTime),
            'identifier' => $timezoneIdentifier
        );
        }
        
        // Sort the array by offset,identifier ascending
        usort($tempTimezones, array("TimeZone","sortTimeZone"));
       
        $timezoneList = array();
        foreach ($tempTimezones as $tz) {
            $sign = ($tz['offset'] > 0) ? '+' : '-';
            $offset = gmdate('H:i', abs($tz['offset']));
            $timezoneList[$tz['identifier']] = '(GMT ' . $sign . $offset . ') ' . $tz['identifier'];
        }

        return $timezoneList;    
        
    }
    
    public function sortTimeZone ($a,$b) {
            return ($a['offset'] == $b['offset'])? strcmp($a['identifier'], $b['identifier']): $a['offset'] - $b['offset'];
    }
    
    public static function GetUserTimezone($user_id){
        $table_user  = Database::get_main_table(TABLE_MAIN_USER);
        $table_admin = Database::get_main_table(TABLE_MAIN_ADMIN);
        $user_id     = Database::escape_string($user_id);
        
        $sql = "SELECT u.*, a.user_id AS is_admin FROM $table_user u LEFT JOIN $table_admin a ON a.user_id = u.user_id WHERE u.user_id = '".$user_id."'";
        $res = Database::query($sql, __FILE__, __LINE__);
        $user_data = Database::fetch_array($res, 'ASSOC');                		        
        $timezoneuser = $user_data['timezone'];
        if(!empty($timezoneuser)){
            return $timezoneuser;
        }else{
            return false;
        }
    }
    
    public static function GetTimeZone($user_id, $data = null){
        $table_user = Database::get_main_table(TABLE_MAIN_USER);
        $table_admin = Database::get_main_table(TABLE_MAIN_ADMIN);
        $user_id     = Database::escape_string($user_id);
        
        $sql = "SELECT u.*, a.user_id AS is_admin FROM $table_user u LEFT JOIN $table_admin a ON a.user_id = u.user_id WHERE u.user_id = '".$user_id."'";
        $res = Database::query($sql, __FILE__, __LINE__);
        $user_data = Database::fetch_array($res, 'ASSOC');                		        
        $timezoneuser = $user_data['timezone'];
        if(!empty($timezoneuser)){
            /*$date = new DateTime($data, new DateTimeZone($timezoneuser));
            date_default_timezone_set($timezoneuser);
            return date("Y-m-d H:i:s", $date->format('U')); */
            
            $timezone_from = new DateTimeZone(date_default_timezone_get());
            $timezone_to   = new DateTimeZone($timezoneuser);
            
            $datetime = new DateTime($data, $timezone_from);
            $datetime->setTimezone($timezone_to);
            return date("Y-m-d H:i:s", $datetime->format('U'));
        }else{
            return false;
        }
    }
    
    
    
    
    public static function ConvertTime($datetime, $timezone_from = NULL, $timezone_to = NULL, $format = 'd-m-Y H:i:s'){
        if(gettype($datetime) == 'integer'){
            $datetime = date($format, $datetime);
        }
        
        if(!isset($timezone_from) || $timezone_from === false){
            $timezone_from = new DateTimeZone(date_default_timezone_get());
        } else {
            $timezone_from = new DateTimeZone($timezone_from);
        }
        if(!isset($timezone_to) || $timezone_to === false){
            $timezone_to = new DateTimeZone(date_default_timezone_get());            
        } else {
            $timezone_to = new DateTimeZone($timezone_to);
        }        
        $datetime = new DateTime($datetime, $timezone_from);
        $datetime->setTimezone($timezone_to);
        return $datetime->format($format);
    }
    
    
    public static function ConvertTimeFromUserToServer($user_id, $datetime, $format = 'd-m-Y H:i:s'){
        $user_timezone = self::GetUserTimezone($user_id);
        if($user_timezone === false){
            $user_timezone = NULL;
        }
        return self::ConvertTime($datetime, $user_timezone, NULL, $format);
    }
    
    public static function ConvertTimeFromServerToUser($user_id, $datetime, $format = 'd-m-Y H:i:s'){
        $user_timezone = self::GetUserTimezone($user_id);
        if($user_timezone === false){
            $user_timezone = NULL;
        }
        return self::ConvertTime($datetime, NULL, $user_timezone, $format);
    }
}
