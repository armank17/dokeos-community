<?php //$id:$
/* For licensing terms, see /dokeos_license.txt */
/**
==============================================================================
* This script that allow to modify the training position
* @package dokeos.learnpath
* @author Isaac flores <florespaz_isaac@hotmail.com>
==============================================================================
*/
/**
 * Script
 */
require_once('../inc/global.inc.php');

if (isset($_GET['action']) && $_GET['action'] == 'change_course_position') {
 $course_id = Security::remove_XSS($_GET['course_id']);
 $new_order = Security::remove_XSS($_GET['new_order']);
 $category_id = Security::remove_XSS($_GET['category_id']);
 if(isset($_GET['id_new_cat'])) {
    $id_new_cat = Security::remove_XSS($_GET['id_new_cat']);
 }
 else{
    $id_new_cat = null;
 }
 change_training_position($course_id,$new_order,$category_id,$id_new_cat);
} elseif (isset($_GET['action']) && $_GET['action'] == 'change_course_category_position') {
 $new_order = Security::remove_XSS($_GET['new_order']);
 $category_id = Security::remove_XSS($_GET['course_category_id']);
 change_training_category_pisition($category_id, $new_order);
}

/**
 * Change the training position
 * @param string $course_id
 * @param integer $new_position
 * @param integer $category_id
 * @return string
 */
function change_training_position ($course_id, $new_position,$category_id,$id_new_cat = null) {
  $new_position = intval($new_position);
  $table_rel_user = Database::get_main_table(TABLE_MAIN_COURSE_USER);
  if(!is_null($id_new_cat)) {
    $sql_course = "UPDATE $table_rel_user
                SET user_course_cat=$id_new_cat
                WHERE course_code='".Database::escape_string($course_id)."'
                AND user_id=".api_get_user_id();
    Database::query($sql_course, __FILE__, __LINE__);
    $category_id = $id_new_cat;

  }
  $sql = 'SELECT sort FROM ' . $table_rel_user . ' WHERE course_code="' . Database::escape_string($course_id).'" AND user_course_cat='.$category_id.' AND user_id='.api_get_user_id();
  
  //update the category of course
  
  $sql_list = 'SELECT sort,course_code FROM ' . $table_rel_user . ' WHERE user_course_cat='.$category_id.' AND user_id='.api_get_user_id().' ORDER BY sort ASC';
  
  $rs = Database::query($sql, __FILE__, __LINE__);
  $rs_list = Database::query($sql_list, __FILE__,__LINE__);
  
  $get_list = array();
  $i = 1;
  $new_course = array();
  while ($row_list = Database::fetch_array($rs_list)) {
    $get_list[$i] = $row_list['sort'];
    $get_course_list[$i] = $row_list['course_code'];
    $new_course[$i] = array('course' => $row_list['course_code'],'index' => $i);
    $i++;
  }

  $cont = 1;
  //Searching course moved
  foreach ($new_course as $cour) {
   if($cour['course'] == $course_id){
    $cour['index'] = $new_position;
    //Save data changed
    $new_course [$cont] = $cour;
    //Changing the indexes
    if($cont < $new_position){
     for($i = $new_position; $i >= 1; $i--) {
      if($new_course[$i]['course'] != $course_id) {
       $new_course[$i]['index'] = (int)$new_course[$i]['index'] - 1;
      }
     }
    } else {
     for($i = $new_position; $i <= count($new_course); $i++) {
      if($new_course[$i]['course'] != $course_id) {
       $new_course[$i]['index'] = (int)$new_course[$i]['index'] + 1;
      }
     }
    }
   }
   $cont += 1;
  }
  //Update data into database
  foreach($new_course as $cour){
   $index = $cour['index'];
   $id_course = $cour['course'];
   $sql = 'UPDATE ' . $table_rel_user . '
      SET sort = ' . $index . '
      WHERE course_code="' . Database::escape_string($id_course).'" AND user_course_cat='.$category_id.' AND user_id='.api_get_user_id();
   $rs = Database::query($sql, __FILE__, __LINE__);
  }
}

function change_training_category_pisition ($category_id, $new_position) {
 // the database definition of the table that stores the user defined course categories
	$table_user_defined_category = Database::get_user_personal_table(TABLE_USER_COURSE_CATEGORY);

 $new_position = intval($new_position);
 $sql = 'SELECT id,sort FROM ' . $table_user_defined_category . ' WHERE  user_id='.api_get_user_id().' ORDER BY sort asc';
 $rs = Database::query($sql, __FILE__, __LINE__);
 $i = 1;
 $new_category = array();
 
 while ($row_list = Database::fetch_array($rs)) {
   $new_category[$i] = array('id' => $row_list['id'],'index' => $i);
   $i++;
 }

 $cont = 1;
  //Searching course moved
  foreach ($new_category as $cat) {
   if($cat['id'] == $category_id){
    $cat['index'] = $new_position;
    //Save data modified
    $new_category [$cont] = $cat;
    //Changing index of the positions
    if($cont < $new_position){
     for($i = $new_position; $i >= 1; $i--) {
      if($new_category[$i]['id'] != $category_id) {
       $new_category[$i]['index'] = (int)$new_category[$i]['index'] - 1;
      }
     }
    } else {
     for($i = $new_position; $i <= count($new_category); $i++) {
      if($new_category[$i]['id'] != $category_id) {
       $new_category[$i]['index'] = (int)$new_category[$i]['index'] + 1;
      }
     }
    }
   }
   $cont += 1;
  }
 //Update into database
  foreach($new_category as $cat){
   $index = $cat['index'];
   $id_cat = $cat['id'];
   $sql = 'UPDATE ' . $table_user_defined_category . '
      SET sort = '.$index.'
      WHERE id='.$id_cat.' AND user_id='.api_get_user_id();
   Database::query($sql, __FILE__, __LINE__);
  }
}