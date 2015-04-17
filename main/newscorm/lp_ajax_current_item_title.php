<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* 	Learning Path
*	This script allow switch audiorecorder for each item
*	@package dokeos.learnpath
*/
require_once('../inc/global.inc.php');


/**
* Get title by item 
* @param    int     The current item id
* @return   string  The current item title 
*/

function get_lp_item_title($next_item) {
    $tbl_lp_item = Database :: get_course_table(TABLE_LP_ITEM);
    $title = '';
    $rs = Database::query("SELECT title FROM $tbl_lp_item WHERE id = ".intval($next_item));
    if (Database::num_rows($rs) > 0) {            
        $row = Database::fetch_array($rs, 'ASSOC');
        $title = $row['title'];
    }
    return $title;
}
$next_item = intval($_GET['lp_item_id']);
echo get_lp_item_title($next_item);
?>
