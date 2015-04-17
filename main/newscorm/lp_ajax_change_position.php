<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* 	Learning Path
*	This script allow to modify the Learning path position
*	@package dokeos.learnpath
*	@author	Isaac flores <florespaz@bidsoft.net>
*/

require_once('../inc/global.inc.php');

if (isset($_GET['action']) && $_GET['action'] == 'change_lp_position') {
 $lp_id = Security::remove_XSS($_GET['lp_id']);
 $new_order = Security::remove_XSS($_GET['new_order']);
 change_lp_position($lp_id,$new_order);
}

/**
 * Change the learning path position
 * @param integer $lp_id
 * @param integer $new_position
 * @return string
 */
function change_lp_position ($lp_id, $new_position) {
  $new_position = intval($new_position);
  $lp_id = intval($lp_id);

  $lp_table = Database::get_course_table(TABLE_LP_MAIN);
  $sql = 'SELECT display_order FROM ' . $lp_table . ' WHERE id=' . $lp_id;
  $rs = Database::query($sql, __FILE__, __LINE__);

  if (($old_position = Database::result($rs, 0)) !== false) {
     $old_position = intval($old_position);

   if ($new_position > $old_position) {
    $sql = 'UPDATE ' . $lp_table . '
       SET display_order = display_order - 1
       WHERE display_order > ' . $old_position . '
       AND display_order <= ' . $new_position;
    Database::query($sql, __FILE__, __LINE__);
   } else {
    $sql = 'UPDATE ' . $lp_table . '
       SET display_order = display_order + 1
       WHERE display_order < ' . $old_position . '
       AND display_order >= ' . $new_position;
    Database::query($sql, __FILE__, __LINE__);
   }
   $sql = 'UPDATE ' . $lp_table . '
      SET display_order = ' . $new_position . '
      WHERE id=' . $lp_id;

   $rs = Database::query($sql, __FILE__, __LINE__);

   // Ajax response
   if ($new_position !== false && ($new_position != $old_position)) {
     echo 'true';
   } else {
     echo 'false';
   }

  }
}