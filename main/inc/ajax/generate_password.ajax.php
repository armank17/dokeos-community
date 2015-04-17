<?php
require_once '../global.inc.php';
if(isset($_GET['action'])){
    $action = Security::remove_XSS($_GET['action']);
    switch($action){
        case 'generatepassword':            
            $characters = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';            
            $length = 8;            
            $password = '';
            for ($i = 0; $i < $length; $i ++) {
                    $password .= $characters[rand() % strlen($characters)];
            }
            echo $password;
        break;
    }
}
?>
