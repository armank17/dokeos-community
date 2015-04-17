<?php
require_once '../inc/global.inc.php';
// only for admin account
api_protect_admin_script();

$tablesettingscurrent = Database::get_main_table(TABLE_MAIN_SETTINGS_CURRENT);
$tablesettingsoptions = Database::get_main_table(TABLE_MAIN_SETTINGS_OPTIONS);


$affected_lp_rows = 0;


$Check1 = Database::query("SELECT id  FROM $tablesettingscurrent WHERE variable ='show_like_on_facebook' ");

if (Database::num_rows($Check1) == 0) {   
    $rs = Database::query("INSERT INTO $tablesettingscurrent (variable, subkey, type, category, selected_value, title, comment, scope, subkeytext, access_url_changeable)
    VALUES ('show_like_on_facebook', NULL, 'radio', 'Platform', 'false', 'showLikeOnFacebook', 'showLikeOnFacebookComment', NULL, NULL, 1);"); 
        
    $rs2 = Database::query("INSERT INTO $tablesettingsoptions (variable, value, display_text) VALUES ('show_like_on_facebook', 'true', 'Yes'), ('show_like_on_facebook', 'false', 'No');"); 
    
    $affected_lp_rows++; 
}
echo '<p>'.$affected_lp_rows.' files updated</p>';
?>