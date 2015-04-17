<?php
require_once '../global.inc.php';

if (isset($_GET['action'])) {    
    switch ($_GET['action']) {        

        case 'updatePosition':            
                $disporder = $_GET['disporder'];
                $tbl_ecommerce_items = Database::get_course_table(TABLE_MAIN_ECOMMERCE_ITEMS);
                $disparr = explode(",", $disporder);
                $counter = 1;
                $affected_rows = 0;                
                
                for ($i = 0; $i < sizeof($disparr); $i++) {   
                    $sql = "UPDATE $tbl_ecommerce_items SET sort=$counter WHERE code = '".$disparr[$i]."'";
                    $res = Database::query($sql);
                    $counter++;
                    $affected_rows += Database::affected_rows();
                }      
            
            echo json_encode(array('success'=>(bool)$res));
            break;
         case 'updatePositionSessions':            
                $disporder = $_GET['disporder'];
                $tbl_ecommerce_items = Database::get_course_table(TABLE_MAIN_ECOMMERCE_ITEMS);
                $disparr = explode(",", $disporder);
                $counter = 1;
                $affected_rows = 0;                
                
                for ($i = 0; $i < sizeof($disparr); $i++) {   
                    $sql = "UPDATE $tbl_ecommerce_items SET sort=$counter WHERE id = '".$disparr[$i]."'";
                    $res = Database::query($sql);
                    $counter++;
                    $affected_rows += Database::affected_rows();
                }      
            
            echo json_encode(array('success'=>(bool)$res));
            break;   
            
          case 'updatePositionModules':            
                $disporder = $_GET['disporder'];
                $tbl_ecommerce_items = Database::get_course_table(TABLE_MAIN_ECOMMERCE_ITEMS);
                $disparr = explode(",", $disporder);
                $counter = 1;
                $affected_rows = 0;                
                
                for ($i = 0; $i < sizeof($disparr); $i++) {   
                    $sql = "UPDATE $tbl_ecommerce_items SET sort=$counter WHERE id = '".$disparr[$i]."'";
                    $res = Database::query($sql);
                    $counter++;
                    $affected_rows += Database::affected_rows();
                }      
            
            echo json_encode(array('success'=>(bool)$res));
            break;  
            
                }      
            
    }
    
?>
