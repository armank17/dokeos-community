<?php
require_once '../inc/global.inc.php';
// only for admin account
api_protect_admin_script();

$table_user = Database::get_main_table(TABLE_MAIN_USER);

$affected_lp_rows = 0;

$Check1 = Database::query("SHOW COLUMNS FROM $table_user LIKE 'timezone' ");

if (Database::num_rows($Check1) == 0) {   
    $rs = Database::query("ALTER TABLE $table_user ADD COLUMN timezone varchar(50)"); 
    $affected_lp_rows++;  
}
echo '<p>'.$affected_lp_rows.' files updated</p>';
?>