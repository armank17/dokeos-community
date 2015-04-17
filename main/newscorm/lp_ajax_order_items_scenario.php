<?php
// including the global Dokeos file
require_once '../inc/global.inc.php';

if (isset($_REQUEST["hdnType"])) {
   $type = $_REQUEST['hdnType'];      
   switch ($type) {
       case 'Sortable': // order items in scenario
           $tbl_lp_item   = Database::get_course_table(TABLE_LP_ITEM);
           $arrOrder      = "";
           foreach ($_REQUEST["hdnItemId"] as $item) {
              $arrOrder .= $item.",";
           }
           $arrOrder1="";
           foreach ($_REQUEST["hdnItemOrder"] as $item1){
             $arrOrder1 .= $item1.",";
           }
           $updateRecordsArray=$arrOrder;
           $updateRecordsArray1=$arrOrder1;
           $disp = explode(",", $updateRecordsArray);
           $disp1 = explode(",", $updateRecordsArray1);
          for ($i=0; $i<count($disp)-1; $i++) {
              Database::query("UPDATE $tbl_lp_item SET display_order = ".intval($disp1[$i])." WHERE id = ".intval($disp[$i]));
          }    
          break;       
       case 'CourseModuleSortable': // order modules in lp course
           $tbl_lp  = Database::get_course_table(TABLE_LP_MAIN);
           $arrOrder      = "";           
           foreach ($_REQUEST["hdnItemId"] as $item) {
              $arrOrder .= $item.",";
           }
           $arrOrder1="";
           foreach ($_REQUEST["hdnItemOrder"] as $item1){
             $arrOrder1 .= $item1.",";
           }
           $updateRecordsArray=$arrOrder;
           $updateRecordsArray1=$arrOrder1;           
           $disp = explode(",", $updateRecordsArray);
           $disp1 = explode(",", $updateRecordsArray1);
           for ($i=0; $i<count($disp)-1; $i++) {
              Database::query("UPDATE $tbl_lp SET display_order = ".intval($disp1[$i])." WHERE id = ".intval($disp[$i]));
           }   
           break;       
   }   
}
?>