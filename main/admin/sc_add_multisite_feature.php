<?php
require_once '../inc/global.inc.php';
// only for admin account
api_protect_admin_script();

$table_main_access_url      = Database::get_main_table(TABLE_MAIN_ACCESS_URL);
$table_main_system_calendar = Database::get_main_table(TABLE_MAIN_SYSTEM_CALENDAR);
$table_user_field           = Database::get_main_table(TABLE_MAIN_USER_FIELD);
$table_user_personal_agenda = Database::get_user_personal_table(TABLE_PERSONAL_AGENDA);


// system calendar
$affected_lp_rows = 0;
$Check = Database::query("SHOW COLUMNS FROM $table_main_system_calendar LIKE 'access_url_id'");
if (Database::num_rows($Check) == 0) {   
    $rs = Database::query("ALTER TABLE $table_main_system_calendar ADD COLUMN access_url_id int unsigned NOT NULL DEFAULT 1;"); 
    $affected_lp_rows++;  
}
echo "<p>$affected_lp_rows files updated in $table_main_system_calendar</p>";


// system user field
$affected_lp_rows = 0;
$Check = Database::query("SHOW COLUMNS FROM $table_user_field LIKE 'access_url_id'");
if (Database::num_rows($Check) == 0) {   
    $rs = Database::query("ALTER TABLE $table_user_field ADD COLUMN access_url_id int unsigned NOT NULL DEFAULT 1;"); 
    $affected_lp_rows++;
    
    $rs = Database::query("SELECT * FROM $table_main_access_url WHERE id > 1");
    if(Database::num_rows($rs) > 0){
        while($site = Database::fetch_array($rs)){
            $sql = "INSERT INTO $table_user_field (
                field_type,
                field_variable,
                field_display_text,
                field_default_value,
                field_order,
                field_visible,
                field_changeable,
                field_filter,
                tms,
                field_registration,
                access_url_id
              )
              SELECT
                field_type,
                field_variable,
                field_display_text,
                field_default_value,
                field_order,
                field_visible,
                field_changeable,
                field_filter,
                tms,
                field_registration,
                {$site['id']}
              FROM $table_user_field
              WHERE access_url_id = 1
              ORDER BY field_order";
           Database::query($sql);
        }
    }
}
    
    
    
echo "<p>$affected_lp_rows files updated in $table_user_field</p>";


// personal agenda
$affected_lp_rows = 0;
$Check = Database::query("SHOW COLUMNS FROM $table_user_personal_agenda LIKE 'access_url_id'");
if (Database::num_rows($Check) == 0) {   
    $rs = Database::query("ALTER TABLE $table_user_personal_agenda ADD COLUMN access_url_id int unsigned NOT NULL DEFAULT 1;"); 
    $affected_lp_rows++;  
}
echo "<p>$affected_lp_rows files updated in $table_user_personal_agenda</p>";
?>