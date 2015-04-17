<?php

// including the global file that gets the general configuration, the databases, the languages, ...
include ('../inc/global.inc.php');

api_block_anonymous_users();

$post = $_POST;
if (empty($post)) {
    return false;
}

$status = array('visible'=>1, 'invisible'=>2);

switch ($post['type']) {
    case 'course':
        $tbl_ecommerce = Database::get_main_table( TABLE_MAIN_ECOMMERCE_ITEMS );
        $sql = "UPDATE {$tbl_ecommerce} SET status = {$status[$post['action']]} WHERE code = '{$post['id']}'";
        Database::query($sql,__FILE__,__LINE__);
        break;
    case 'module':
        break;
    case 'session':
        break;
}

?>