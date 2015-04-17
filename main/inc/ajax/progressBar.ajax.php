<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../global.inc.php';
$lpid = $_GET['lpid'];

$tbl_lp_item = Database :: get_course_table(TABLE_LP_ITEM);
$sql = "SELECT * FROM " . $tbl_lp_item . " WHERE lp_id = " . $lpid . " ORDER BY display_order";
$result = api_sql_query($sql, __FILE__, __LINE__);
$arrLP = array();

while ($row = Database :: fetch_array($result)) {           
if ($row['item_type'] == 'certificate') { continue; }
    $arrLP[] = array('id' => $row['id']);
}
$max = count($arrLP) - 1;
if($_SESSION['count'] < $max){
    $_SESSION['count'] = $_SESSION['count'] + 1 ;
}
    


 
    
    
  


?>
