<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* 	Learning Path
*	This script allow to modify the Learning path position
*	@package dokeos.learnpath
*	@author	Isaac flores <florespaz@bidsoft.net>
*/

require_once('../../inc/global.inc.php');

if (isset($_GET['action']) && $_GET['action'] == 'change_lp_position') {
 $lp_id = Security::remove_XSS($_GET['lp_id']);
 $new_order = Security::remove_XSS($_GET['new_order']);
 $language = Security::remove_XSS($_GET['language']);
 change_lp_position($lp_id,$new_order,$language);
}

/**
 * Change the learning path position
 * @param integer $lp_id
 * @param integer $new_position
 * @return string
 */
function change_lp_position ($lp_id, $new_position,$language) { 
  $new_position = intval($new_position);
  $lp_id = intval($lp_id);
  $language = $language;

  $lp_table = Database::get_course_table(TABLE_MAIN_SLIDES);
  $sql = 'SELECT display_order FROM ' . $lp_table . ' WHERE id=' . $lp_id;
  $rs = Database::query($sql, __FILE__, __LINE__); 

  if (($old_position = Database::result($rs, 0)) !== false) {
     $old_position = intval($old_position);
     
        if($old_position == 0){     
            // If the old position is 0 mean what is the first time in what use the reorder
            $sql = "SELECT * FROM $lp_table WHERE language ='".$language."' ORDER BY id ASC ";
            $result = Database::query($sql,__FILE__,__LINE__);     
            $i = 1;error_log($sql);
            while($row = Database::fetch_array($result)){
                // Will change the display order for each slide
                $sql_reorder = "UPDATE $lp_table SET display_order = '".Database::escape_string($i)."' WHERE id = '".Database::escape_string($row['id'])."'";
                $result_reorder = Database::query($sql_reorder,__FILE__,__LINE__);
                $i++;
            }         
        }else{             
            // If the new position is higher than old position then
            if ($new_position > $old_position) {
                // Will decrease in  1 for each display order 
                $sql = 'UPDATE ' . $lp_table . '
                SET display_order = display_order - 1
                WHERE language ="'.$language.'" 
                AND display_order > ' . $old_position . '
                AND display_order <= ' . $new_position;
                Database::query($sql, __FILE__, __LINE__);
            } else {
                // Will increase in 1 for each display order
                $sql = 'UPDATE ' . $lp_table . '
                SET display_order = display_order + 1
                WHERE language ="'.$language.'" 
                AND display_order < ' . $old_position . '
                AND display_order >= ' . $new_position; 
                Database::query($sql, __FILE__, __LINE__);
            }            
            // Will change the display order of the slide
                $sql = 'UPDATE ' . $lp_table . '
                SET display_order = ' . $new_position . '
                WHERE id=' . $lp_id;
                $rs = Database::query($sql, __FILE__, __LINE__);    
        }   
   // Ajax response
   if ($new_position !== false && ($new_position != $old_position)) {
     echo 'true';
   } else {
     echo 'false';
   }

  }
}