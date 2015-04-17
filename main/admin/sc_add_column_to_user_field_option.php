<?php
require_once '../inc/global.inc.php';
// only for admin account
api_protect_admin_script();

$table_field_options_values = Database::get_main_table(TABLE_MAIN_USER_FIELD_OPTIONS);

$affected_lp_rows = 0;

$Check1 = Database::query("SHOW COLUMNS FROM $table_field_options_values LIKE 'field_registration' ");

if (Database::num_rows($Check1) == 0) {   
    $rs = Database::query("ALTER TABLE $table_field_options_values ADD COLUMN field_registration int DEFAULT 0 "); 
    $affected_lp_rows++;  
}
echo '<p>'.$affected_lp_rows.' files updated</p>';
?>