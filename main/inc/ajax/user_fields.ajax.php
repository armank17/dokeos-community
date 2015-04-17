<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* 	User Profile ajax
*	This script allow to modify user field position
*	@package dokeos.admin
*/

require_once('../../inc/global.inc.php');

if (isset($_GET['action']) && $_GET['action'] == 'change_field_position') {
 $new_order = Security::remove_XSS($_GET['new_order']);
 change_field_position();
}

/**
 * Change the user field position
 * @return void
 */
function change_field_position () {
  $newOrder = Security::remove_XSS($_GET['neworder']);
  $tbl_user_field = Database::get_course_table(TABLE_MAIN_USER_FIELD);
  $newOrderInfo = explode(",", $newOrder);
  $currentPosition = array();
  $sql = "SELECT field_order FROM $tbl_user_field ORDER BY field_order ASC";
  $res = Database::query($sql);
  while ($row = Database::fetch_array($res)) {
     $currentPosition[] = $row['field_order'];
  }
  $i = 0;
  foreach ($newOrderInfo as $columnId) {
      $sql = "UPDATE $tbl_user_field SET field_order=" . $currentPosition[$i] . " WHERE id = " . Database::escape_string($columnId);
      $res = Database::query($sql, __FILE__, __LINE__);
      $i++;
  }
}