<?php
require_once '../../inc/global.inc.php';
// only for admin account
api_protect_admin_script();

$tbl_settings_current = Database::get_main_table(TABLE_MAIN_SETTINGS_CURRENT);
$rs_settings_current = Database::query("SELECT variable FROM $tbl_settings_current WHERE variable = 'LogoUrl' ");

$affected_lp_rows = 0;
if (Database::num_rows($rs_settings_current) == 0) {    

        $rs = Database::query("INSERT INTO $tbl_settings_current (variable, subkey, type, category, selected_value, title, comment, scope, subkeytext, access_url_changeable)
                VALUES ('LogoUrl', NULL, 'textfield', 'Platform', '', 'LogoUrlTitle', 'LogoUrlComment', NULL, NULL, 1)");
        $affected_lp_rows++;
        
}
echo '<p>'.$affected_lp_rows.' files updated in lp</p>';

?>
