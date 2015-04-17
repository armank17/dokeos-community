<?php 

// including the global file that gets the general configuration, the databases, the languages, ...
include ('../inc/global.inc.php');

api_block_anonymous_users();
// Additional libraries
require ('product_functions.php');
require_once (api_get_path ( LIBRARY_PATH ) . 'formvalidator/FormValidator.class.php');

/**
 * @param action : select which action is called
 */

switch ($_GET['action']){
    case 'update_order':
        update_order();
        break;
    case 'update_info':
        update_info();
        break;
    case 'update_product_status':
        update_product_status();
        break;
}

?>