<?php

/* For licensing terms, see /dokeos_license.txt */
/**
  ==============================================================================
 * 	@package dokeos.auth
 * 	@todo check if unsubscribing from a course WITH group memberships works as it should
 * 	@todo constants are in uppercase, variables aren't
  ==============================================================================
 */
// Names of the language file that needs to be included.
$language_file = array('courses', 'registration','admin');

// Delete the globals['_cid'], we don't need it here.
$cidReset = true; // Flag forcing the 'current course' reset
// Including the global file.
require_once '../inc/global.inc.php';

if (api_get_setting('show_courses_descriptions_in_catalog') == 'true') {
  // additional html (javascript and style css)
  $htmlHeadXtra[] = '<script type="text/javascript">' .
          'var GB_ROOT_DIR = "' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/greybox/"' .
          '</script>';

  //$htmlHeadXtra[] = '<script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery.js" type="text/javascript" language="javascript"></script>';

  $htmlHeadXtra[] = '<script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/greybox/AJS.js" type="text/javascript" language="javascript"></script>';
  $htmlHeadXtra[] = '<script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/greybox/AJS_fx.js" type="text/javascript" language="javascript"></script>';
  $htmlHeadXtra[] = '<script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/greybox/gb_scripts.js" type="text/javascript" language="javascript"></script>';

  $htmlHeadXtra[] = '<link href="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/greybox/gb_styles.css" rel="stylesheet" type="text/css" />';
  $htmlHeadXtra[] = '<link href="' . api_get_path(WEB_CSS_PATH) . '/pagination1.css" rel="stylesheet" type="text/css" />';
}

// Add additional javascript, css
//$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-1.4.2.min.js" language="javascript"></script>';
//$htmlHeadXtra[] = '<script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery.ui.all.js" type="text/javascript" language="javascript"></script>';


// Move the training list inside category, move the categories too
$htmlHeadXtra[] = '<script type="text/javascript">
/* <![CDATA[ */
$(document).ready(function(){
var category_before = "";
var category_after = "";
 //Move category and Move category items
/*
$(".user_course_category").mousedown(function(){
  $( "#categories" ).sortable( "option", "disabled", false );
});

$(".user_course_category").mouseup(function(){
  $( "#categories" ).sortable( "option", "disabled", true );
});
*/

$("#categories").sortable({
    connectWith: "#categories",
    cursor: "move",
    handle: $(".move"),
    update: function(event, ui) {
    var current_course_or_category_id = ui.item.attr("id");// Get the course ID and the category ID too
	parentElement = ui.item.parent();

    try {
      var current_course_data = current_course_or_category_id.split("course_id_");
      var new_course_data = current_course_data[1].split("_category_");

      // Get IDs
      var Course_id = new_course_data[0]; // This is the current COURSE ID of the selected row
      var Course_category_id = new_course_data[1]; // This is the current COURSE CATEGORY ID of the selected row
    } catch(e){}

    try {
      var current_category_data = current_course_or_category_id.split("root_category_");
      var Category_id = current_category_data[1]; // This is the current CATEGORY ID of the selected row
    } catch(e){}

    var sorted_list;
    var sorted_data;

    if (current_course_data[0] != "") { // Move only the categories
      // Get the new order
       sorted_list = $(this).sortable("serialize");
       sorted_list = sorted_list.replace(/&/g,"");
       sorted_data = sorted_list.split("root_category[]=");

       var newOrder = 0;
       for(var i=0; i<sorted_data.length; i++){
         if(sorted_data[i] == Category_id){
           newOrder = i;
         }
       }

		  // Ajax request to save new position
		  $.ajax({
			   type: "GET",
			   url: "course_ajax_change_position.php?action=change_course_category_position&course_category_id="+Category_id+"&new_order="+newOrder,
			   success: function(response){
                              document.location="courses.php";
                           }
		  })

    }

   }
		});

   $("#category_0 ul,#categories li div ul").sortable({
    connectWith: "#category_0 ul,#categories li div ul",
    cursor: "move",
    handle: $(".move1"),
    start:function(event,ui){
        category_before = ui.item.attr("id");
    },
    //Event thrown when a course is moved in another category
    receive:function(event,ui){
        category_after = ui.item.attr("id");
        var current_course_or_category_id = ui.item.attr("id");// Get the course ID and the category ID too
        parentElement = ui.item.parent();
        try {
          id_new_cats = $(parentElement).attr("class").split("category_");
          id_new_cat = id_new_cats[1];
          var current_course_data = current_course_or_category_id.split("course_id_");
          var new_course_data = current_course_data[1].split("_category_");

          // Get IDs
          var Course_id = new_course_data[0]; // This is the current COURSE ID of the selected row
          var Course_category_id = new_course_data[1]; // This is the current COURSE CATEGORY ID of the selected row
        } catch(e){}

        try {
          var current_category_data = current_course_or_category_id.split("root_category_");
          var Category_id = current_category_data[1]; // This is the current CATEGORY ID of the selected row
        } catch(e){}

        var sorted_list;
        var sorted_data;

        if (current_course_data[0] == "") { // Move trainings of a category
          // Get the new order
           sorted_list = $(this).sortable("serialize");
           sorted_list = sorted_list.replace(/&/g,"");
           var replace_word = "_category\\\[\\\]="+Course_category_id;
           var new_replace_word = new String(replace_word);
           search_word = new RegExp(new_replace_word,"gi");
           //search_word = new_replace_word
           sorted_list = sorted_list.replace(search_word,"");
           sorted_data = sorted_list.split("course_id_");

           var newOrder = 0;
           for(var i=0; i<sorted_data.length; i++){
             if(sorted_data[i] == Course_id){
               newOrder = i;
             }
           }

                      // Ajax request to save new position
                      $.ajax({
                               type: "GET",
                               url: "course_ajax_change_position.php?action=change_course_position&course_id="+Course_id+"&new_order="+newOrder+"&category_id="+Course_category_id+"&id_new_cat="+id_new_cat,
			   success: function(response){
                              document.location="courses.php";
                           }
                      })

        }
    },
    stop:function(event,ui){
        if(category_after != ""){
            category_after = "";
            return;
        }
        category_after = "";
        var current_course_or_category_id = ui.item.attr("id");// Get the course ID and the category ID too
        parentElement = ui.item.parent();
        try {
          var current_course_data = current_course_or_category_id.split("course_id_");
          var new_course_data = current_course_data[1].split("_category_");

          // Get IDs
          var Course_id = new_course_data[0]; // This is the current COURSE ID of the selected row
          var Course_category_id = new_course_data[1]; // This is the current COURSE CATEGORY ID of the selected row
        } catch(e){}

        try {
          var current_category_data = current_course_or_category_id.split("root_category_");
          var Category_id = current_category_data[1]; // This is the current CATEGORY ID of the selected row
        } catch(e){}

        var sorted_list;
        var sorted_data;

        if (current_course_data[0] == "") { // Move trainings of a category
          // Get the new order
           sorted_list = $(this).sortable("serialize");
           sorted_list = sorted_list.replace(/&/g,"");
           var replace_word = "_category\\\[\\\]="+Course_category_id;
           var new_replace_word = new String(replace_word);
           search_word = new RegExp(new_replace_word,"gi");
           //search_word = new_replace_word
           sorted_list = sorted_list.replace(search_word,"");
           sorted_data = sorted_list.split("course_id_");

           var newOrder = 0;
           for(var i=0; i<sorted_data.length; i++){
             if(sorted_data[i] == Course_id){
               newOrder = i;
             }
           }

                      // Ajax request to save new position
                      $.ajax({
                               type: "GET",
                               url: "course_ajax_change_position.php?action=change_course_position&course_id="+Course_id+"&new_order="+newOrder+"&category_id="+Course_category_id,
			   success: function(response){
                              document.location="courses.php";
                           }
                      })

        }
    }
});
});
/* ]]> */
</script> ';

// Show/hide the training list inside category
$htmlHeadXtra[] = '<script type="text/javascript">
$(document).ready( function() {
      $(".slide-up-down").click(function() {
        slide_item_id = $(this).attr("id");
        slide_item_class = $(this).attr("class");
        slide_data_up = slide_item_id.split("slide_up_");
        slide_data_down = slide_item_id.split("slide_down_");
        slide_up_id = slide_data_up[1];
        slide_down_id = slide_data_down[1];
        if (slide_data_up[0] == "") {
          $("#"+slide_item_id).hide();
          item_id = "#slide_down_" + slide_up_id;
          category_id = "#category_" + slide_up_id;
          $(item_id).show();
          $(category_id).hide();
        } else {
          $("#"+slide_item_id).hide();
          item_id = "#slide_up_" + slide_down_id;
          category_id = "#category_" + slide_down_id;
          $(item_id).show();
          $(category_id).show();
        }
      });

});
 </script>';

// Section for the tabs.
$this_section = SECTION_COURSES;

// Acces rights: anonymous users can't do anything usefull here.
api_block_anonymous_users();

// Include additional libraries.
include_once api_get_path(LIBRARY_PATH) . 'debug.lib.inc.php';
require_once api_get_path(LIBRARY_PATH) . 'course.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'mail.lib.inc.php';

$ctok = $_SESSION['sec_token'];
$stok = Security::get_token();

// Database table definitions.
$tbl_course = Database::get_main_table(TABLE_MAIN_COURSE);
$tbl_courses_nodes = Database::get_main_table(TABLE_MAIN_CATEGORY);
$tbl_courseUser = Database::get_main_table(TABLE_MAIN_COURSE_USER);
$tbl_user = Database::get_main_table(TABLE_MAIN_USER);


// Filter.
$safe = array();
$safe['action'] = '';
$actions = array('sortmycourses', 'createcoursecategory', 'subscribe', 'deletecoursecategory', 'unsubscribe');

if (in_array(htmlentities($_GET['action']), $actions)) {
  $safe['action'] = htmlentities($_GET['action']);
}

// Title of the page.
if ($safe['action'] == 'sortmycourses' || !isset($safe['action'])) {
  $nameTools = get_lang('SortMyCourses');
}
if ($safe['action'] == 'createcoursecategory') {
  $nameTools = get_lang('CreateCourseCategory');
}
if ($safe['action'] == 'subscribe') {
  $nameTools = get_lang('SubscribeToCourse');
}

// Breadcrumbs.
$interbreadcrumb[] = array('url' => api_get_path(WEB_PATH) . 'user_portal.php', 'name' => get_lang('MyCourses'));
if (empty($nameTools)) {
  $nameTools = get_lang('CourseManagement');
} else {
  $interbreadcrumb[] = array('url' => api_get_path(WEB_CODE_PATH) . 'auth/courses.php', 'name' => get_lang('CourseManagement'));
}

// Displaying the header.
Display::display_header($nameTools);

/*
  ==============================================================================
  COMMANDS SECTION
  ==============================================================================
 */

unset($message);

// We are moving a course or category of the user up/down the list (=Sort My Courses).
if (isset($_GET['move'])) {
  if (isset($_GET['course'])) {
    if ($ctok == $_GET['sec_token']) {
      $message = move_course($_GET['move'], $_GET['course'], $_GET['category']);
    }
  }
  if (isset($_GET['category']) and !$_GET['course']) {
    if ($ctok == $_GET['sec_token']) {
      $message = move_category($_GET['move'], $_GET['category']);
    }
  }
}

// We are moving the course of the user to a different user defined course category (=Sort My Courses).

if (isset($_POST['submit_change_course_category'])) {
  if ($ctok == $_POST['sec_token']) {
    $message = store_changecoursecategory($_POST['course_2_edit_category'], $_POST['course_categories']);
  }
}

// We are creating a new user defined course category (= Create Course Category).
if (isset($_POST['create_course_category']) && isset($_POST['title_course_category']) && strlen(trim($_POST['title_course_category'])) > 0) {
  if ($ctok == $_POST['sec_token']) {
    $message = store_course_category();
  }
}


if (isset($_POST['submit_edit_course_category']) && isset($_POST['title_course_category']) && strlen(trim($_POST['title_course_category'])) > 0) {
  if ($ctok == $_POST['sec_token']) {
    $message = store_edit_course_category();
  }
}

// We are subcribing to a course (=Subscribe to course).
if (isset($_POST['subscribe']) AND (api_get_setting('allow_students_to_browse_courses') == 'true' || ($_user['status']==SESSIONADMIN || api_is_platform_admin()))) {
  if ($ctok == $_POST['sec_token']) {

    $message = subscribe_user($_POST['subscribe']);
  }
}

// We are unsubscribing from a course (=Unsubscribe from course).
if (isset($_POST['unsubscribe'])) {
  if ($ctok == $_POST['sec_token']) {
    $message = remove_user_from_course($_user['user_id'], $_POST['unsubscribe']);
  }
}

// we are deleting a course category
if ($safe['action'] == 'deletecoursecategory' && isset($_GET['id'])) {
  if ($ctok == $_GET['sec_token']) {
    $get_id_cat = Security::remove_XSS($_GET['id']);
    $message = delete_course_category($get_id_cat);
  }
}

/*
  ==============================================================================
  DISPLAY SECTION
  ==============================================================================
 */
// Diplaying the tool title
// api_display_tool_title($nameTools);
// we are displaying any result messages;
if (isset($message)) {
  Display::display_confirmation_message($message, false);
}

// The menu with the different options in the course management
echo '<div id="actions" class="actions">';
echo '<a href="../../user_portal.php">' . Display::return_icon('pixel.gif',get_lang('MyCourses'),array('class' => 'toolactionplaceholdericon toolactionback')) . ' ' . get_lang('MyCourses') . '</a>';
if ($safe['action'] !== 'sortmycourses' && isset($safe['action'])) {
	echo '<a href="' . api_get_self() . '?action=sortmycourses">' . Display::return_icon('pixel.gif',get_lang('SortMyCourses'),array('class' => 'toolactionplaceholdericon toolactiondraganddrop')). ' ' . get_lang('SortMyCourses') . '</a>';
} else {
	echo '<span class="link">' . Display::return_icon('pixel.gif',get_lang('SortMyCourses'),array('class' => 'toolactionplaceholdericon toolactiondraganddrop')) . ' ' . get_lang('SortMyCourses') . '</span>';
}
if ($safe['action'] !== 'createcoursecategory') {
	echo '<a href="' . api_get_self() . '?action=createcoursecategory">' . Display::return_icon('pixel.gif',get_lang('Category'),array('class' => 'toolactionplaceholdericon toolactioncreatefolder')).' ' . get_lang('Category') . '</a>';
} else {
	echo '<span class="link">' . Display::return_icon('pixel.gif',get_lang('Category'),array('class' => 'toolactionplaceholdericon toolactioncreatefolder')) . ' ' . get_lang('Category') . '</span>';
}

if (api_get_setting('allow_students_to_browse_courses') == 'true' || ($_user['status']==SESSIONADMIN || api_is_platform_admin())) {
	if ($safe['action'] != 'subscribe') {
		echo '<a href="' . api_get_self() . '?action=subscribe">' . Display::return_icon('pixel.gif',get_lang('SubscribeToCourse'),array('class' => 'toolactionplaceholdericon toolactionadd32')) . ' ' . get_lang('SubscribeToCourse') . '</a>';
	} else {
            //get_lang('SubscribeToCourse')
            echo '<span class="link">' . Display::return_icon('pixel.gif',get_lang('SubscribeToCourse'),array('class' => 'toolactionplaceholdericon toolactionadd32')). ' ' . get_lang('SubscribeToCourse') . '</span>';
	}
}
echo "</div>";

// start the content div
echo '<div id="content">';

if ($CourseCategory == true){echo '<div class="normal-message new-message">'.get_lang("CourseCategoryStored").'</div>';}
echo "<div>";
switch ($safe['action']) {
  case 'subscribe':
    //api_display_tool_title(get_lang('SubscribeToCourse'));
    if (api_get_setting('allow_students_to_browse_courses') == 'true' || ($_user['status']==SESSIONADMIN || api_is_platform_admin())) {
      courses_subscribing();
    }
    break;
  case 'unsubscribe':
    //api_display_tool_title(get_lang('UnsubscribeFromCourse'));
    $user_courses = get_courses_of_user($_user['user_id']);
    display_courses($_user['user_id'], true, $user_courses);
    break;
  case 'createcoursecategory':
    //api_display_tool_title(get_lang('CreateCourseCategory'));
    display_create_course_category_form();
    break;
  case 'deletecoursecategory':
  case 'sortmycourses':
  default:
    //api_display_tool_title(get_lang('SortMyCourses'));
    $user_courses = get_courses_of_user($_user['user_id']);
    display_courses($_user['user_id'], true, $user_courses);
    break;
}
echo '</div>';

// close the content div
echo '</div>';

//echo '<div class="actions">';
//echo '&nbsp;';
//echo '</div>';

// display the footer
Display :: display_footer();

/*
  ==============================================================================
  FUNCTIONS
  ==============================================================================
 */

/**
 * Subscribe the user to a given course
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @param string $course_code the code of the course the user wants to subscribe to
 * @return string we return the message that is displayed when the action is succesfull
 */
function subscribe_user($course_code) {
  global $_user, $stok;

  $all_course_information = CourseManager::get_course_information($course_code);


  if ($all_course_information['registration_code'] == '' || $_POST['course_registration_code_'.$all_course_information['code']] == $all_course_information['registration_code']) {
    if (api_is_platform_admin ()) {
      $status_user_in_new_course = COURSEMANAGER;
    } else {
      $status_user_in_new_course = null;
    }
    if (CourseManager::add_user_to_course($_user['user_id'], $course_code, $status_user_in_new_course)) {
      $send = api_get_course_setting('email_alert_to_teacher_on_new_user_in_course', $course_code);
      if ($send == 1) {
        CourseManager::email_to_tutor($_user['user_id'], $course_code, $send_to_tutor_also = false);
      } else if ($send == 2) {
        CourseManager::email_to_tutor($_user['user_id'], $course_code, $send_to_tutor_also = true);
      }
      return get_lang('EnrollToCourseSuccessful');
    } else {
      return get_lang('ErrorContactPlatformAdmin');
    }
  } else {
    $return = '';
    if (isset($_POST['course_registration_code_'.$all_course_information['code']]) && $_POST['course_registration_code_'.$all_course_information['code']] != $all_course_information['registration_code']) {
      Display::display_error_message(get_lang('CourseRegistrationCodeIncorrect'));
    }
    $return .= get_lang('CourseRequiresPassword') . '<br />';
    $return .= $all_course_information['visual_code'] . ' - ' . $all_course_information['title'];

    $return .= "<form action=\"" . $_SERVER["REQUEST_URI"] . "\" method=\"post\">";
    $return .= '<input type="hidden" name="sec_token" value="' . $stok . '" />';
    $return .= "<input type=\"hidden\" name=\"subscribe\" value=\"" . $all_course_information['code'] . "\" />";
    $return .= "<input type=\"text\" name=\"course_registration_code\" value=\"" . $_POST['course_registration_code'] . "\" />";
    $return .= "<input type=\"submit\" name=\"submit_course_registration_code\" value=\"OK\" alt=\"" . get_lang('SubmitRegistrationCode') . "\" /></form>";
    return $return;
  }
}

/**
 * unsubscribe the user from a given course
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @param int $user_id The user id of the user that is performing the unsubscribe action
 * @param string $course_code the course code of the course the user wants to unsubscribe from
 * @return string we return the message that is displayed when the action is succesfull
 */
function remove_user_from_course($user_id, $course_code) {
  $tbl_course_user = Database::get_main_table(TABLE_MAIN_COURSE_USER);

  // we check (once again) if the user is not course administrator
  // because the course administrator cannot unsubscribe himself
  // (s)he can only delete the course
  $sql_check = "SELECT * FROM $tbl_course_user WHERE user_id='" . $user_id . "' AND course_code='" . $course_code . "' AND status='1'";
  $result_check = Database::query($sql_check, __FILE__, __LINE__);
  $number_of_rows = Database::num_rows($result_check);
  if ($number_of_rows > 0) {
    return false;
  }

  CourseManager::unsubscribe_user($user_id, $course_code);
  return get_lang('YouAreNowUnsubscribed');
}

/**
 * handles the display of the courses to which the user can subscribe
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 */
function courses_subscribing() {
  browse_courses(api_is_platform_admin() ? null : 0);
  display_search_courses(api_is_platform_admin() ? null : 0);
}

/**
 * Allows you to browse through the course categories (faculties) and subscribe to the courses of
 * this category (faculty)
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 */
function browse_courses($only_payment = null) {
  browse_course_categories($only_payment);
  if (!isset($_POST['search_course'])) {
    browse_courses_in_category($only_payment);
  }
}

/**
 * Counts the number of courses in a given course category
 */
/*function count_courses_in_category($category,$only_payment = null) {
  $tbl_course = Database::get_main_table(TABLE_MAIN_COURSE);
  $tbl_courses_nodes = Database::get_main_table(TABLE_MAIN_CATEGORY);
  $sqlpayment  = "";
  $maincategory = $category;
  $sql = "SELECT children_count FROM $tbl_courses_nodes WHERE code = '".$category."'";
  $res = Database::query($sql, __FILE__, __LINE__);
  $child_count = Database::result($res,0,0);
  $catcode = '"'.$category.'"';
  for($i=1;$i<=$child_count;$i++) {
	  $catop = ' , ';
	  $sql = "SELECT code FROM $tbl_courses_nodes WHERE parent_id = '".$category."'";
	  $res = Database::query($sql, __FILE__, __LINE__);
	  $num_rows = Database::num_rows($res);
	  $count = 0;
	  while($row = Database::fetch_array($res)) {
		  $subcatcode = $row['code'];
		  $catcode = $catcode.$catop.'"'.$subcatcode. '"';

		  $sql_inner = "SELECT code FROM $tbl_courses_nodes WHERE parent_id = '".$subcatcode."'";
		  $res_inner = Database::query($sql_inner, __FILE__, __LINE__);
		  $inner_num_rows = Database::num_rows($res_inner);
		  $count_inner = 0;
		  while($row_inner = Database::fetch_array($res_inner)) {
			  $subcatcode = $row_inner['code'];
			  $catcode = $catcode.$catop.'"'.$subcatcode. '"';
			  $i++;
			  $count_inner++;
		  }
		  $i++;
		  $count++;
	  }
	  $category = $subcatcode;

  }
  if(isset($only_payment)){
     $sqlpayment = "AND payment=$only_payment ";
  }
  if(empty($category)) {
	  $where = " category_code IS NULL ";
  }
  else {
	  $where = " category_code IN (".$catcode.")";
  }
  $sql = "SELECT * FROM $tbl_course WHERE ".$where." $sqlpayment";
  // Showing only the courses of the current Dokeos access_url_id.
  global $_configuration;*/
  /*if ($_configuration['multiple_access_urls']) {
   $url_access_id = api_get_current_access_url_id();
    if ($url_access_id != -1) {
      $tbl_url_rel_course = Database::get_main_table(TABLE_MAIN_ACCESS_URL_REL_COURSE);
      $sql = "SELECT * FROM $tbl_course as course INNER JOIN $tbl_url_rel_course as url_rel_course
					ON (url_rel_course.course_code=course.code)
					WHERE access_url_id = $url_access_id AND code " . (empty($maincategory) ? " IS NULL" : "='" . $maincategory . "' $sqlpayment");
    }
  }*/
  /*if ($_configuration['multiple_access_urls']) {
    $url_access_id = api_get_current_access_url_id();
    if ($url_access_id != -1) {
      $tbl_url_rel_course = Database::get_main_table(TABLE_MAIN_ACCESS_URL_REL_COURSE);
      $sql = "SELECT * FROM $tbl_course as course INNER JOIN $tbl_url_rel_course as url_rel_course
					ON (url_rel_course.course_code=course.code)
					WHERE access_url_id = $url_access_id AND $where $sqlpayment";
    }
  }
  return Database::num_rows(Database::query($sql, __FILE__, __LINE__));
}*/

function get_course_categories($subcatcode) {

  $tbl_courses_nodes = Database::get_main_table(TABLE_MAIN_CATEGORY);
  $sql_inner = "SELECT code FROM $tbl_courses_nodes WHERE parent_id = '".$subcatcode."'";
  $res_inner = Database::query($sql_inner, __FILE__, __LINE__);
  $inner_num_rows = Database::num_rows($res_inner);
  $count_inner = 0;
  
  $catop = ' , ';
  while($row_inner = Database::fetch_array($res_inner)) {
	  $subcatcode = $row_inner['code'];
	  
	  $catcode = $catcode.$catop.'"'.$subcatcode. '"';
	  $tmpcode = get_course_categories($subcatcode);
	  $catcode = $catcode.$tmpcode;

  }
  return $catcode;
}

function count_courses_in_category($category,$only_payment = null) {
  $tbl_course = Database::get_main_table(TABLE_MAIN_COURSE);
  $tbl_courses_nodes = Database::get_main_table(TABLE_MAIN_CATEGORY);
  $sqlpayment  = "";
  $sql = "SELECT children_count FROM $tbl_courses_nodes WHERE code = '".$category."'";
  $res = Database::query($sql, __FILE__, __LINE__);
  $child_count = Database::result($res,0,0);
  $catcode = '"'.$category.'"';
  for($i=1;$i<=$child_count;$i++) {  
	  $catop = ' , ';
	  $sql = "SELECT code FROM $tbl_courses_nodes WHERE parent_id = '".$category."'";
	  $res = Database::query($sql, __FILE__, __LINE__);
	  $num_rows = Database::num_rows($res);
	  $count = 0;	 
	  while($row = Database::fetch_array($res)) {
		  $subcatcode = $row['code'];

		  $catcode = $catcode.$catop.'"'.$subcatcode. '"';
		  $tmpcode = get_course_categories($subcatcode);		  
		  $catcode = $catcode.$tmpcode;	  
		  
		  $i++;
		  $count++;		  
	  } 
	  $category = $subcatcode;

  }
  
  if(isset($only_payment)){
     $sqlpayment = "AND payment=$only_payment ";
  }
  if(empty($category)) {
	  $where = " category_code IS NULL ";
  }
  else {
	  $where = " category_code IN (".$catcode.")";
  }
  if(api_get_setting('show_closed_courses') == 'true') {
	  $sqlvisibility = '';
  }
  else {
	  $sqlvisibility = ' AND visibility <> 0 ';
  }
  $sql = "SELECT * FROM $tbl_course WHERE ".$where." $sqlpayment $sqlvisibility";
  // Showing only the courses of the current Dokeos access_url_id.
  global $_configuration;
  if ($_configuration['multiple_access_urls']) {
    $url_access_id = api_get_current_access_url_id();
    if ($url_access_id != -1) {
      $tbl_url_rel_course = Database::get_main_table(TABLE_MAIN_ACCESS_URL_REL_COURSE);
      /*$sql = "SELECT * FROM $tbl_course as course INNER JOIN $tbl_url_rel_course as url_rel_course
					ON (url_rel_course.course_code=course.code)
					WHERE access_url_id = $url_access_id  $sqlvisibility AND category_code" . (empty($category) ? " IS NULL" : "='" . $category . "' $sqlpayment");*/
		
		$sql = "SELECT * FROM $tbl_course as course INNER JOIN $tbl_url_rel_course as url_rel_course
					ON (url_rel_course.course_code=course.code)
					WHERE access_url_id = $url_access_id  $sqlvisibility AND $where $sqlpayment";
    }
  }

  return Database::num_rows(Database::query($sql, __FILE__, __LINE__));
}

/**
 * displays the browsing of the course categories (faculties)
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @return HTML code containing a list with all the categories and subcategories and the navigation to go one category up(if needed)
 */
function browse_course_categories($only_payment) {
  global $stok;
  $tbl_courses_nodes = Database::get_main_table(TABLE_MAIN_CATEGORY);
  $category = Database::escape_string($_GET['category']);
  $safe_url_categ = Security::remove_XSS($_GET['category']);

  echo "<p><strong>" . get_lang('CourseCategories') . "</strong></p>";

  $sql = "SELECT * FROM $tbl_courses_nodes WHERE parent_id " . (empty($category) ? "IS NULL" : "='" . $category . "'") . " GROUP BY code, parent_id  ORDER BY tree_pos ASC";
  $result = Database::query($sql, __FILE__, __LINE__);
  echo "<ul>";
  while ($row = Database::fetch_array($result)) {
    $count_courses_in_categ = count_courses_in_category($row['code'],$only_payment);
    if ($row['children_count'] > 0 || $count_courses_in_categ > 0) {
      echo "<li><a href=\"" . api_get_self() . "?action=subscribe&amp;category=" . $row['code'] . "&amp;up=" . $safe_url_categ . "&amp;sec_token=" . $stok . "\">" . $row['name'] . "</a>" .
      " (" . $count_courses_in_categ . ")</li>";
    } elseif ($row['nbChilds'] > 0) {
      echo "<li><a href=\"" . api_get_self() . "?action=subscribe&amp;category=" . $row['code'] . "&amp;up=" . $safe_url_categ . "&amp;sec_token=" . $stok . "\">" . $row['name'] . "</a></li>";
    } else {
      echo "<li>" . $row['name'] . "</li>";
    }
  }
  echo "</ul>";
  if ($_GET['category']) {
    echo "<a href=\"" . api_get_self() . "?action=subscribe&amp;category=" . Security::remove_XSS($_GET['up']) . "&amp;sec_token=" . $stok . "\">" . Display::return_icon('pixel.gif', get_lang('UpOneCategory'), array('align' => 'middle','class'=>'actionplaceholdericon actionprev_navigation','style'=>'margin:5px;')) . get_lang('UpOneCategory') . "</a>";
  }
}

/**
 * Display all the courses in the given course category. I could have used a parameter here
 * but instead I use the already available $_GET['category']
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @return HTML code: a table with all the courses in a given category (title, code, tutor) and a subscription icon if applicable)
 */
function browse_courses_in_category($only_payment = null) {
  $tbl_course = Database::get_main_table(TABLE_MAIN_COURSE);
  $category = Database::escape_string($_GET['category']);
  echo "<p><strong>" . get_lang('CoursesInCategory') . "</strong></p>";
  $my_category = (empty($category) ? " IS NULL" : "='" . $category . "'");
  $sqlpayment  = "";  
  $page = (isset($_GET['page'])) ?  $_GET['page'] : 1 ;       
  if(isset($only_payment)){
     $sqlpayment = "AND payment=$only_payment ";
  }
    $sql1 = "SELECT * FROM $tbl_course WHERE category_code" . $my_category . " $sqlpayment ORDER BY title, visual_code";
      //showing only the courses of the current Dokeos access_url_id
  global $_configuration;
  if ($_configuration['multiple_access_urls']) {
    $url_access_id = api_get_current_access_url_id();
    if ($url_access_id != -1) {
      $tbl_url_rel_course = Database::get_main_table(TABLE_MAIN_ACCESS_URL_REL_COURSE);
      $sql1 = "SELECT * FROM $tbl_course as course INNER JOIN $tbl_url_rel_course as url_rel_course
					ON (url_rel_course.course_code=course.code)
					WHERE access_url_id = $url_access_id AND category_code" . $my_category . ' ORDER BY title, visual_code';
    }
  }
    
    $result1 = Database::query($sql1, __FILE__, __LINE__);
    $num_rows = Database::num_rows($result1);
    
    $rows_per_page= 20;
  

     $total_page= ceil ($num_rows / $rows_per_page);
  
    $limit= ' LIMIT '. ($page -1) * $rows_per_page . ',' .$rows_per_page;
    $sql1 .=" $limit";
    $result2 = Database::query($sql1, __FILE__, __LINE__);    
    if(!$result2){        
        die('Invalid query: ' . mysql_error());
    }else{
            while ($row = Database::fetch_array($result2)) {
                $courses[] = array('code' => $row['code'], 'directory' => $row['directory'], 'db' => $row['db_name'], 'visual_code' => $row['visual_code'], 'title' => $row['title'], 'tutor' => $row['tutor_name'], 'subscribe' => $row['subscribe'], 'unsubscribe' => $row['unsubscribe'], 'registration_code' => $row['registration_code']);
            }
    } 
  
    //echo "<div id='paginator-mediabox' style='width:100%;clear:both;text-align:center;'>";
    ?>
    <ul id="pagination-digg">
       <?php
            if($page>1){
               ?>
               <li><a href="<?php echo api_get_path(WEB_PATH); ?>main/auth/courses.php?action=<?php echo $_GET['action'] ?>&category=<?php echo $_GET['category'] ?>&up=<?php echo $_GET['up'] ?>&sec_token=<?php echo $_GET['sec_token'] ?>&page=<?php echo 1; ?>"><?php echo get_lang('FirstPage');  ?></a></li>
               <?php               
            }
            if (($page - 1) > 0) {
               ?>
               
               <li><a href="<?php echo api_get_path(WEB_PATH); ?>main/auth/courses.php?action=<?php echo $_GET['action'] ?>&category=<?php echo $_GET['category'] ?>&up=<?php echo $_GET['up'] ?>&sec_token=<?php echo $_GET['sec_token'] ?>&page=<?php echo $page-1; ?>"><?php echo get_lang('PreviousPage');  ?></a></li>
               <?php              
            }
            $last_page=$total_page;
            $first_page=1;
            if ($total_page > 5) {
                $last_page = 5;
                $next = true;
                if ($page > 3) {
                    $previous = true;
                    $first_page = $page - 2;
                     if($first_page>($total_page-3)){
                        $first_page=$total_page-4;
                     }
                    if ($total_page > ($page + 2)) {
                        $last_page = $page + 2;
                    } else {
                        $next = false;
                        $last_page = $total_page;
                    }
                }
            }
            if($previous){
               ?>
               <li>...</li>
               <?php                    
                }
             
            for ($i = $first_page; $i <= $last_page; $i++) {
                if ($page == $i) {
                   ?><li class="active"><?php echo "<b>" . $page . "</b> ";?></li><?php
                    
                } else {
                   ?>
                   <li><a href="<?php echo api_get_path(WEB_PATH); ?>main/auth/courses.php?action=<?php echo $_GET['action'] ?>&category=<?php echo $_GET['category'] ?>&up=<?php echo $_GET['up'] ?>&sec_token=<?php echo $_GET['sec_token'] ?>&page=<?php echo $i; ?>" ><?php echo $i; ?></a></li>
                   <?php                   
                }
            }
            if($next){
               ?>                   
                   <li>...</li>                   
                   <?php
                    
                }
            if (($page + 1) <= $total_page) {
               ?>
                   <li><a href="<?php echo api_get_path(WEB_PATH); ?>main/auth/courses.php?action=<?php echo $_GET['action'] ?>&category=<?php echo $_GET['category'] ?>&up=<?php echo $_GET['up'] ?>&sec_token=<?php echo $_GET['sec_token'] ?>&page=<?php echo ($page+1); ?>"><?php echo get_lang('NextPage');  ?></a></li>
                   <?php
            }
            if ($page<$total_page) {               
               ?>
                   <li><a href="<?php echo api_get_path(WEB_PATH); ?>main/auth/courses.php?action=<?php echo $_GET['action'] ?>&category=<?php echo $_GET['category'] ?>&up=<?php echo $_GET['up'] ?>&sec_token=<?php echo $_GET['sec_token'] ?>&page=<?php echo $total_page; ?>"><?php echo get_lang('LastPage');  ?></a></li>
               <?php                
            }
            ?>
    </ul>
    <?php

//  $result = Database::query($sql, __FILE__, __LINE__);
//  while ($row = Database::fetch_array($result)) {
//    $courses[] = array('code' => $row['code'], 'directory' => $row['directory'], 'db' => $row['db_name'], 'visual_code' => $row['visual_code'], 'title' => $row['title'], 'tutor' => $row['tutor_name'], 'subscribe' => $row['subscribe'], 'unsubscribe' => $row['unsubscribe'], 'registration_code' => $row['registration_code']);
//  }  
  display_subscribe_to_courses($courses); 
}

/**
 * displays the form for searching for a course and the results if a query has been submitted.
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @return HTML code: the form for searching for a course
 */
function display_search_courses($only_payment = null) {
  global $_user, $stok;
  echo "<p><strong>" . get_lang('SearchCourse') . "</strong><br /></p>";
  echo "<form class=\"course_list\" method=\"post\" action=\"" . api_get_self() . "?action=subscribe\">",
  '<input type="hidden" name="sec_token" value="' . $stok . '" />',
  "<input type=\"hidden\" name=\"search_course\" value=\"1\" />",
  "<input type=\"text\" name=\"search_term\" value=\"" . (empty($_POST['search_term']) ? '' : Security::remove_XSS($_POST['search_term'])) . "\" />",
  "<button class=\"search\" type=\"submit\">", get_lang('_search'), "</button>",
  "</form>";
  if (isset($_POST['search_course'])) {
    echo "<p><strong>" . get_lang('SearchResultsFor') . " " . api_htmlentities($_POST['search_term'], ENT_QUOTES, api_get_system_encoding()) . "</strong><br />";
    $result_search_courses_array = search_courses($_POST['search_term'],$only_payment);
    display_subscribe_to_courses($result_search_courses_array);
  }
}

/**
 * This function displays the list of course that can be subscribed to.
 * This list can come from the search results or from the browsing of the platform course categories
 */
function display_subscribe_to_courses($courses) {

  global $_user;
  // getting all the courses to which the user is subscribed to
  $user_courses = get_courses_of_user($_user['user_id']);
  $user_coursecodes = array();

  // we need only the course codes as these will be used to match against the courses of the category
  if ($user_courses != '') {
    foreach ($user_courses as $key => $value) {
      $user_coursecodes[] = $value['code'];
    }
  }

  if ($courses == 0) {
    return false;
  }

  echo "<table cellpadding=\"4\">\n";
  foreach ($courses as $key => $course) {
      $var= $var + 1;
    // displaying the course title, visual code and teacher/teaching staff
    echo "\t<tr>\n";

    if (api_get_setting('show_courses_descriptions_in_catalog') == 'true') {
      // block course description
      echo "\t\t<td>";
      $icon_title = get_lang('CourseDetails') . ' - ' . $course['title'];

      echo "<a href='course_description.php?code=" . $course['code'] . "' title='$icon_title' rel='gb_page_center[778]'>".Display::return_icon('pixel.gif',$icon_title,array('class' => 'actionplaceholdericon actionsview'))."</a>";
      echo "\t\t</td>";
    }

    echo "\t\t<td>\n";
    echo "<strong>" . $course['title'] . "</strong><br />";
    if (api_get_setting('display_coursecode_in_courselist') == 'true') {
      echo $course['visual_code'];
    }
    if (api_get_setting('display_coursecode_in_courselist') == 'true' && api_get_setting('display_teacher_in_courselist') == 'true') {
      echo " - ";
    }
    if (api_get_setting('display_teacher_in_courselist') == 'true') {
      echo $course['tutor'];
    }
    echo "\t\t</td>\n";

    echo "\t\t<td>\n";

    display_subscribe_icon($course, $user_coursecodes, $var);

    echo "\t\t</td>\n";

    echo "</tr>";
  }



  echo "</table>";
}

/**
 * Search the courses database for a course that matches the search term.
 * The search is done on the code, title and tutor field of the course table.
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @param string $search_term: the string that the user submitted, what we are looking for
 * @return array an array containing a list of all the courses (the code, directory, dabase, visual_code, title, ... )
 * 			matching the the search term.
 */
function search_courses($search_term,$only_payment = null) {
  $TABLECOURS = Database::get_main_table(TABLE_MAIN_COURSE);
  $search_term_safe = Database::escape_string($search_term);
  $sqlpayment  = "";
  if(isset($only_payment)){
     $sqlpayment = "AND payment=$only_payment ";
  }

  $sql_find = "SELECT * FROM $TABLECOURS WHERE (code LIKE '%" . $search_term_safe . "%' OR title LIKE '%" . $search_term_safe . "%' OR tutor_name LIKE '%" . $search_term_safe . "%') $sqlpayment ORDER BY title, visual_code ASC";

  global $_configuration;
  if ($_configuration['multiple_access_urls']) {
    $url_access_id = api_get_current_access_url_id();
    if ($url_access_id != -1) {
      $tbl_url_rel_course = Database::get_main_table(TABLE_MAIN_ACCESS_URL_REL_COURSE);
      $sql_find = "SELECT * FROM $TABLECOURS as course INNER JOIN $tbl_url_rel_course as url_rel_course
					ON (url_rel_course.course_code=course.code)
					WHERE access_url_id = $url_access_id AND  (code LIKE '%" . $search_term_safe . "%' OR title LIKE '%" . $search_term_safe . "%' OR tutor_name LIKE '%" . $search_term_safe . "%' ) $sqlpayment ORDER BY title, visual_code ASC ";
    }
  }

  $result_find = Database::query($sql_find, __FILE__, __LINE__);
  while ($row = Database::fetch_array($result_find)) {
    $courses[] = array('code' => $row['code'], 'directory' => $row['directory'], 'db' => $row['db_name'], 'visual_code' => $row['visual_code'], 'title' => $row['title'], 'tutor' => $row['tutor_name'], 'subscribe' => $row['subscribe'], 'unsubscribe' => $row['unsubscribe']);
  }
  return $courses;
}

/**
 * deletes a course category and moves all the courses that were in this category to main category
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @param int $id: the id of the user_course_category
 * @return string a language variable saying that the deletion went OK.
 */
function delete_course_category($id) {
  global $_user, $_configuration;

  $tucc = Database::get_user_personal_table(TABLE_USER_COURSE_CATEGORY);
  $TABLECOURSUSER = Database::get_main_table(TABLE_MAIN_COURSE_USER);
  $id = intval($id);
  $sql_delete = "DELETE FROM $tucc WHERE id='" . $id . "' and user_id='" . $_user['user_id'] . "'";

  // Get next order in the default category
  $sql_order = "SELECT max(sort) as count FROM $TABLECOURSUSER  WHERE user_course_cat='0' AND user_id=" . $_user['user_id'];
  $rs_order = Database::query($sql_order, __FILE__, __LINE__);
  $row_order = Database::fetch_array($rs_order);
  $nex_order = $row_order['count'] + 1;

  $sql_update = "UPDATE $TABLECOURSUSER SET user_course_cat='0',sort = sort + $nex_order WHERE user_course_cat='" . $id . "' AND user_id='" . $_user['user_id'] . "'";
  Database::query($sql_delete, __FILE__, __LINE__);
  Database::query($sql_update, __FILE__, __LINE__);
  return get_lang('CourseCategoryDeleted');
}

/**
 * stores the user course category in the dokeos_user database
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @return string a language variable saying that the user course category was stored
 */
function store_course_category() {
  global $_user, $_configuration, $CourseCategory;
  $CourseCategory = false;
  $tucc = Database::get_user_personal_table(TABLE_USER_COURSE_CATEGORY);

  // step 1: we determine the max value of the user defined course categories
  $sql = "SELECT sort FROM $tucc WHERE user_id='" . $_user['user_id'] . "' ORDER BY sort DESC";
  $result = Database::query($sql, __FILE__, __LINE__);
  $maxsort = Database::fetch_array($result);
  $nextsort = $maxsort['sort'] + 1;

  // step 2: we check if there is already a category with this name, if not we store it, else we give an error.
  $sql = "SELECT * FROM $tucc WHERE user_id='" . $_user['user_id'] . "' AND title='" . Database::escape_string($_POST['title_course_category']) . "'ORDER BY sort DESC";
  $result = Database::query($sql, __FILE__, __LINE__);
  if (Database::num_rows($result) == 0) {
    $sql_insert = "INSERT INTO $tucc (user_id, title,sort) VALUES ('" . $_user['user_id'] . "', '" . api_htmlentities($_POST['title_course_category'], ENT_QUOTES, api_get_system_encoding()) . "', '" . $nextsort . "')";
    Database::query($sql_insert, __FILE__, __LINE__);
    //Display::display_confirmation_message(get_lang("CourseCategoryStored"));
    $CourseCategory = true;
  } else {

    Display::display_error_message(get_lang('ACourseCategoryWithThisNameAlreadyExists'));
  }
}

/**
 * displays the form that is needed to create a course category.
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @return HTML the form (input field + submit button) to create a user course category
 */
function display_create_course_category_form() {
  global $_user, $_configuration, $stok;

	echo "<form name=\"create_course_category\" method=\"post\" action=\"" . api_get_self() . "?action=sortmycourses\">\n";
	echo '<div class="row"><div class="form_header">'.get_lang('CreateCourseCategory').'</div></div>';
	echo '<div class="row">
		<div class="label">'.get_lang('CategoryName').'</div>
		<div class="formw">
			<input type="hidden" name="sec_token" value="' . $stok . '" />
			<input type="text" name="title_course_category" class="focus" />
		</div>
	     </div>';
	echo '<div class="row">
		<div class="label"></div>
		<div class="formw">
			<button type="submit" class="save" name="create_course_category">' . get_lang('Ok') . '</button>
		</div>
	      </div>';
	echo "</form>\n";

	echo '<div class="row"><div class="form_header">'.get_lang('ExistingCourseCategories').'</div></div>';

  $tucc = Database::get_user_personal_table(TABLE_USER_COURSE_CATEGORY);
  $sql = "SELECT * FROM $tucc WHERE user_id='" . $_user['user_id'] . "'";
  $result = Database::query($sql, __LINE__, __FILE__);
  if (Database::num_rows($result) > 0) {
    echo "<ul>\n";
    while ($row = Database::fetch_array($result)) {
      echo "\t<li>" . $row['title'] . "</li>\n";
    }
    echo "</ul>\n";
  }else{
      Display :: display_normal_message(get_lang('NoCourseCategory'),false, true);
  }
}

// ***************************************************************************
// this function stores the changes in a course category
//
// ***************************************************************************

/**
 * stores the changes in a course category (moving a course to a different course category)
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @param string $course_code : the course_code of the course we are moving
 * 		  int $newcategory : the id of the user course category we are moving the course to.
 * @return string a language variable saying that the course was moved.
 */
function store_changecoursecategory($course_code, $newcategory) {
  global $_user;
  $course_code = Database::escape_string($course_code);
  $newcategory = Database::escape_string($newcategory);

  $TABLECOURSUSER = Database::get_main_table(TABLE_MAIN_COURSE_USER);

  $max_sort_value = api_max_sort_value($newcategory, $_user['user_id']); // max_sort_value($newcategory);
  $sql = "UPDATE $TABLECOURSUSER SET user_course_cat='" . $newcategory . "', sort='" . ($max_sort_value + 1) . "' WHERE course_code='" . $course_code . "' AND user_id='" . $_user['user_id'] . "'";
  $result = Database::query($sql, __FILE__, __LINE__);
  return get_lang('EditCourseCategorySucces');
}

/**
 * moves the course one place up or down
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @param string $direction : the direction we are moving the course to (up or down)
 * 		  string $course2move : the course we are moving
 * @return string a language variable saying that the course was moved.
 */
function move_course($direction, $course2move, $category) {
  global $_user;
  $TABLECOURSUSER = Database::get_main_table(TABLE_MAIN_COURSE_USER);

  $all_user_courses = get_courses_of_user($_user['user_id']);

  // we need only the courses of the category we are moving in
  $user_courses = array();
  foreach ($all_user_courses as $key => $course) {
    if ($course['user_course_category'] == $category) {
      $user_courses[] = $course;
    }
  }

  foreach ($user_courses as $key => $course) {
    if ($course2move == $course['code']) {
      // source_course is the course where we clicked the up or down icon
      $source_course = $course;
      // target_course is the course before/after the source_course (depending on the up/down icon)
      if ($direction == 'up') {
        $target_course = $user_courses[$key - 1];
      } else {
        $target_course = $user_courses[$key + 1];
      }
    } // if ($course2move == $course['code'])
  }

  if (count($target_course) > 0 && count($source_course) > 0) {
    $sql_update1 = "UPDATE $TABLECOURSUSER SET sort='" . $target_course['sort'] . "' WHERE course_code='" . $source_course['code'] . "' AND user_id='" . $_user['user_id'] . "'";
    $sql_update2 = "UPDATE $TABLECOURSUSER SET sort='" . $source_course['sort'] . "' WHERE course_code='" . $target_course['code'] . "' AND user_id='" . $_user['user_id'] . "'";
    Database::query($sql_update2, __FILE__, __LINE__);
    Database::query($sql_update1, __FILE__, __LINE__);
    return get_lang('CourseSortingDone');
  }
  return '';
}

/**
 * Moves the course one place up or down
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @param string $direction : the direction we are moving the course to (up or down)
 * 		  string $course2move : the course we are moving
 * @return string a language variable saying that the course was moved.
 */
function move_category($direction, $category2move) {
  global $_user;
  // the database definition of the table that stores the user defined course categories
  $table_user_defined_category = Database::get_user_personal_table(TABLE_USER_COURSE_CATEGORY);

  $user_coursecategories = get_user_course_categories();
  $user_course_categories_info = get_user_course_categories_info();

  foreach ($user_coursecategories as $key => $category_id) {
    if ($category2move == $category_id) {
      // source_course is the course where we clicked the up or down icon
      //$source_category=get_user_course_category($category2move);
      $source_category = $user_course_categories_info[$category2move];
      // target_course is the course before/after the source_course (depending on the up/down icon)
      if ($direction == 'up') {
        $target_category = $user_course_categories_info[$user_coursecategories[$key - 1]];
      } else {
        $target_category = $user_course_categories_info[$user_coursecategories[$key + 1]];
      }
    } // if ($course2move == $course['code'])
  } // foreach ($user_courses as $key => $course)

  if (count($target_category) > 0 && count($source_category) > 0) {
    $sql_update1 = "UPDATE $table_user_defined_category SET sort='" . $target_category['sort'] . "' WHERE id='" . $source_category['id'] . "' AND user_id='" . $_user['user_id'] . "'";
    $sql_update2 = "UPDATE $table_user_defined_category SET sort='" . $source_category['sort'] . "' WHERE id='" . $target_category['id'] . "' AND user_id='" . $_user['user_id'] . "'";
    Database::query($sql_update2, __FILE__, __LINE__);
    Database::query($sql_update1, __FILE__, __LINE__);
    return get_lang('CategorySortingDone');
  }
  return '';
}

/**
 * displays everything that is needed when the user wants to manage his current courses (sorting, subscribing, unsubscribing, ...)
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @param int $user_id: the user_id of the current user
 * 		  string $parameter: determines weither we are displaying for the sorting, subscribing or unsubscribin
  array $user_courses:  the courses to which the user is subscribed
 * @return html a table containing courses and the appropriate icons (sub/unsub/move)
 */
function display_courses($user_id, $show_course_icons, $user_courses) {
  global $_user, $_configuration;

  //echo "<table id=\"courses_list_id\" cellpadding=\"4\"><tbody class=\"sort\">\n";
  echo '<div id="training_wrap">';
  // building an array that contains all the id's of the user defined course categories
  // initially this was inside the display_courses_in_category function but when we do it here we have fewer
  // sql executions = performance increase.
  $all_user_categories = get_user_course_categories();
  // step 0: we display the course without a user category
  echo "<table class='data_table'><tr><th align='center' style='width:28px;'>&nbsp;</th><th align='left' style='width:40px;cursor:pointer;'>" . Display::return_icon('action-slide-up.png', '', array('class' => 'slide-up-down', 'id' => 'slide_up_0')) . Display::return_icon('action-slide-down.png', '', array('class' => 'slide-up-down', 'id' => 'slide_down_0', 'style' => 'display:none;')) . "</th><th align='left'>" . api_get_setting('default_category_course') . "</th><th align='center' style='width:62px;'>&nbsp;</th></tr></table>";
  echo '<div id="category_0" class="category_wrap">';
  display_courses_in_category(0, 'true');
  echo '</div>';
  // parent_draggable class has a size fixed, this class is used in many places...
  if (isset($_GET['action']) && $_GET['action'] == 'sortmycourses' && isset($_GET['categoryid'])) {
   $style = "height:40px;";
  } else {
   $style = "";
  }
  // Step 1: We get all the categories of the user.
  $tucc = Database::get_user_personal_table(TABLE_USER_COURSE_CATEGORY);
  $sql = "SELECT * FROM $tucc WHERE user_id='" . $_user['user_id'] . "' ORDER BY sort ASC";
  $result = Database::query($sql, __FILE__, __LINE__);
  if (Database::num_rows($result)>0) {
  echo '<ul id="categories" class="dragdrop nobullets ui-sortable">'; // Categories != 0
  while ($row = Database::fetch_array($result)) {
    echo '<li id="root_category_' . $row['id'] . '" class="category draggablex"><div class="parent_draggable rounded move" style="'.$style.'" ><table width="100%" style="margin-left: -10px; margin-top: -2px;">';
    if ($show_course_icons) {
      // The edit link is clicked.
      if (isset($_GET['categoryid']) && $_GET['categoryid'] == $row['id']) {
        // We display the edit form for the category.
        echo "<tr id='category_row_" . $row['id'] . "'><th class=\"user_course_category\">";
        echo '<a name="category' . $row['id'] . '"></a>'; // display an internal anchor.
        display_edit_course_category_form($row['id']);
      } else {
        // We simply display the title of the category.
        echo "<tr id='category_row_" . $row['id'] . "'><th style='font-weight: bold; text-align: left; padding-left: 5px; vertical-align: top; height: 40px;'>" . Display::return_icon('action-slide-up.png', '', array('class' => 'slide-up-down', 'id' => 'slide_up_' . $row['id'],'style' => 'cursor:pointer;')) . Display::return_icon('action-slide-down.png', '', array('class' => 'slide-up-down', 'id' => 'slide_down_' . $row['id'], 'style' => 'display:none;cursor:pointer;'));
        echo '<a name="category' . $row['id'] . '"></a>'; // display an internal anchor.
        echo '<span style="display: inline-block; vertical-align: top; margin: 8px 0px 0px 10px;">'.$row['title'].'</span>';
      }
      echo "</th><th align='center' style='width:62px;' class=\"user_course_category\">";
      display_category_icons($row['id'], $all_user_categories);
      echo "</th></tr>";
    }
    echo '</table></div><div id="category_' . $row['id'] . '" class="category">';
    // Step 2: Show the courses inside this category.
    display_courses_in_category($row['id'], $show_course_icons);
    echo '</div></li>';
    //echo '</li>';
  }
  //echo "</tbody></table>\n";
  echo '</ul>'; // Close categories != 0
  }
  echo '</div>'; // End training wrap
}

/**
 * This function displays all the courses in the particular user category;
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @param int id: the id of the user defined course category
 * @return string: the name of the user defined course category
 */
function display_courses_in_category($user_category_id, $showicons) {
  global $_user,$course;

  // table definitions
  $TABLECOURS = Database::get_main_table(TABLE_MAIN_COURSE);
  $TABLECOURSUSER = Database::get_main_table(TABLE_MAIN_COURSE_USER);
  $TABLE_USER_COURSE_CATEGORY = Database::get_user_personal_table(TABLE_USER_COURSE_CATEGORY);

	//we filter the courses from the URL
	$join_access_url = $where_access_url = '';
	global $_configuration;
	if ($_configuration['multiple_access_urls'] == true) {
		$access_url_id = api_get_current_access_url_id();
		if ($access_url_id != -1) {
			$tbl_url_course = Database :: get_main_table(TABLE_MAIN_ACCESS_URL_REL_COURSE);
			$join_access_url = ", $tbl_url_course url_rel_course ";
			$where_access_url = " AND url_rel_course.course_code = course.code AND access_url_id = $access_url_id ";
		}
	}

  $sql_select_courses = "SELECT course.code, course.visual_code, course.subscribe subscr, course.unsubscribe unsubscr,
								course.title title, course.tutor_name tutor, course.db_name, course.directory, course_rel_user.status status,
								course_rel_user.sort sort, course_rel_user.user_course_cat user_course_cat
		                        FROM    $TABLECOURS       course,
										$TABLECOURSUSER  course_rel_user $join_access_url
		                        WHERE course.code = course_rel_user.course_code
		                        AND   course_rel_user.user_id = '" . $_user['user_id'] . "'
		                        AND course_rel_user.user_course_cat='" . $user_category_id . "' $where_access_url
		                        ORDER BY course_rel_user.user_course_cat, course_rel_user.sort ASC";

  $result = Database::query($sql_select_courses, __FILE__, __LINE__);
  $number_of_courses = Database::num_rows($result);
  $key = 0;
  $count = 0;
  echo '<ul class="nobullets ui-sortable category_'.$user_category_id.'" style="min-height: 15px;margin:0px;">';
  while ($course = Database::fetch_array($result)) {
    $class = ($count % 2 == 0) ? 'row_even' : 'row_odd';
    echo '<li id="course_id_' . $course['code'] . '_category_' . $user_category_id . '" class="draggablex movex" ><table class="data_table" cellpadding="8">';
    echo "\t<tr  class='" . $class . "' id='course_code_" . $course['code'] . "'>\n";
    // block drag and drop
    echo "\t\t<td align='left' valign='middle' class='dragHandle move1 draggable' style='width:35px;'>";
    echo Display::return_icon('pixel.gif', get_lang('Move'),array("class"=>"actionplaceholdericon actionsdraganddrop"));
    echo "\t\t</td>";

    if (api_get_setting('show_courses_descriptions_in_catalog') == 'true') {
      // block course description
      echo "\t\t<td style='width:40px;' align='left' valign='middle'>";
      $icon_title = get_lang('CourseDetails') . ' - ' . $course['title'];
      echo "<a href='course_description.php?code=" . $course['code'] . "' title='$icon_title' rel='gb_page_center[778]'>" . Display::return_icon('pixel.gif',$icon_title,array('class' => 'actionplaceholdericon actionsview')) . "</a>";
      echo "\t\t</td>";
    }

    echo "\t\t<td align='left' valign='middle'>\n";
    echo '<a name="course' . $course['code'] . '"></a>'; // display an internal anchor.
    echo "<strong>" . $course['title'] . "</strong><br />";
    if (api_get_setting('display_coursecode_in_courselist') == 'true') {
      echo $course['visual_code'];
    }
    if (api_get_setting('display_coursecode_in_courselist') == 'true' && api_get_setting('display_teacher_in_courselist') == 'true') {
      echo " - ";
    }
    if (api_get_setting('display_teacher_in_courselist') == 'true') {
      echo $course['tutor'];
    }
    echo "\t\t</td>\n";
    // displaying the up/down/edit icons when we are sorting courses
    echo "\t\t<td align=\"left\" valign=\"middle\" style='width: 320px;'>\n";
    display_course_icons($key, $number_of_courses, $course);
    echo "\t\t</td>\n";
    echo "\t</tr>\n";
    echo '</table></li>';
    $key++;
    $count++;
  }
  echo '</ul>';
  if ($count == 0) {
    echo get_lang('ThereAreNoTrainingsInThisCategory');
    echo '<br/><br/>';
  } else {
    echo '<br/>';
  }
}

/**
 * gets the title of the user course category
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @param int id: the id of the user defined course category
 * @return string: the name of the user defined course category
 */
function get_user_course_category($id) {
  global $_user, $_configuration;
  $tucc = Database::get_user_personal_table(TABLE_USER_COURSE_CATEGORY);
  $id = intval($id);
  return Database::fetch_array(Database::query("SELECT * FROM $tucc WHERE user_id='" . $_user['user_id'] . "' AND id='$id'", __FILE__, __LINE__));
}

/**
 * displays the subscribe icon if the subscribing is allowed and if the user is not yet
 * subscribe to this course
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @param string $current_course: the course code of the course we need to display the subscribe icon for
 * @return string a subscribe icon or the text that subscribing is not allowed or the user is already subscribed
 */
function display_subscribe_icon($current_course, $user_coursecodes, $var) {
	global $stok;
        $course_info = Database :: get_course_info($current_course['code']);
        $course_visibility = $course_info['visibility'];
       if($course_visibility == 0){
           echo get_lang('CourseClosed');
       }
       else{
           // the user is already subscribed to this course
	if (in_array($current_course['code'], $user_coursecodes)) {


         $tucc = Database::get_main_table(TABLE_MAIN_COURSE);


          $sql = "SELECT * FROM $tucc WHERE code='" . $current_course['code'] . "'";
          $result = Database::query($sql, __FILE__, __LINE__);
          while ($row = Database::fetch_array($result)) {

              $unsubscribe = $row['unsubscribe'];
          }


     if ( $unsubscribe == 1) {
        // var_Dump($current_course);
      // changed link to submit to avoid action by the search tool indexer
      echo "<form action=\"" . $_SERVER["REQUEST_URI"] . "\" method=\"post\" onsubmit=\"javascript: if (!confirm('" . addslashes(api_htmlentities(get_lang("ConfirmUnsubscribeFromCourse"), ENT_QUOTES, api_get_system_encoding())) . "')) return false;\">";
      echo '<input type="hidden" name="sec_token" value="' . $stok . '" />';
      echo "<input type=\"hidden\" name=\"unsubscribe\" value=\"" . $current_course['code'] . "\" />";
      echo '<input type="image" name="unsub" style="border-color:#fff" src="' . api_get_path(WEB_IMG_PATH) . 'enroll_na.png" title="' . get_lang('_unsubscribe') . '"  alt="' . get_lang('_unsubscribe') . '" /></form>';
  } else {
      display_info_text(get_lang('UnsubscribeNotAllowed'));
    }



    } else { // the user is not yet subscribed to this course
		// subscribing to the course is allowed
		if ($current_course['subscribe'] == SUBSCRIBE_ALLOWED) {
			if (!empty($current_course['registration_code'])) {

				$return .= "<form action=\"" . $_SERVER["REQUEST_URI"] . "\" method=\"post\">";
				$return .= '<input type="hidden" name="sec_token" value="' . $stok . '" />';
				$return .= "<input type=\"hidden\" name=\"subscribe\" value=\"" . $current_course['code'] . "\" />";

				if (isset($_POST['course_registration_code_'. $current_course['code']]) && $_POST['course_registration_code_'. $current_course['code']] !== $current_course['registration_code']) {
					$return .= '<span class="form_error">'.get_lang('CourseRegistrationCodeIncorrect').'</span><br />';
				} else {
					$return .= '<span>'.get_lang('CourseRequiresPassword').'</span><br />';
				}

				$return .= "<input type=\"text\" name=\"course_registration_code_". $current_course['code']."\" value=\"" . $_POST['course_registration_code_'. $current_course['code']] . "\" />";
				$return .= "<input type=\"submit\" name=\"submit_course_registration_code\" value=\"OK\" alt=\"" . get_lang('SubmitRegistrationCode') . "\" /></form>";
				echo $return;
			} else {
				echo "<form action=\"" . $_SERVER["REQUEST_URI"] . "\" method=\"post\">";
				echo '<input type="hidden" name="sec_token" value="' . $stok . '" />';
				echo "<input type=\"hidden\" name=\"subscribe\" value=\"" . $current_course['code'] . "\" />";
				if (!empty($_POST['search_term'])) {
					echo '<input type="hidden" name="search_course_" value="1" />';
					echo '<input type="hidden" name="search_term" value="' . Security::remove_XSS($_POST['search_term']) . '" />';
				}
                                echo "<div class='cursesubscribe'><input type=\"submit\" name=\"unsub\" value=\"\"  title=\"" . get_lang('Subscribe') . "\" alt=\"" . get_lang('Subscribe') . "\" /></form></div>";
				//echo "<input type=\"image\" name=\"unsub\" src=\"" . api_get_path(WEB_IMG_PATH) . "enroll.png\" title=\"" . get_lang('Subscribe') . "\" alt=\"" . get_lang('Subscribe') . "\" /></form>";
			}
    	} else {
	      	// subscribing is not allowed
			Display::display_icon('enroll_na.png', get_lang('SubscribingNotAllowed'));

		}
	}
       }

}

/**
 * Displays the subscribe icon if the subscribing is allowed and if the user is not yet
 * subscribed to this course
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @param  $key:
 * 		   $number_of_courses
 * 		   $course
 * 		   $user_courses
 * @return html a small table containing the up/down icons and the edit icon (for moving to a different user course category)
 * @todo complete the comments on this function: the parameter section
 */
function display_course_icons($key, $number_of_courses, $course) {
  global $safe, $stok;
  if ($course['status'] != 1) {
    if ($course['unsubscr'] == 1) {

      // changed link to submit to avoid action by the search tool indexer
      echo "<form action=\"" . api_get_self() . "\" method=\"post\" onsubmit=\"javascript: if (!confirm('" . addslashes(api_htmlentities(get_lang("ConfirmUnsubscribeFromCourse"), ENT_QUOTES, api_get_system_encoding())) . "')) return false;\">";
      echo '<input type="hidden" name="sec_token" value="' . $stok . '" />';
      echo "<input type=\"hidden\" name=\"unsubscribe\" value=\"" . $course['code'] . "\" />";
      echo '<input type="image" name="unsub" style="border-color:#fff" src="' . api_get_path(WEB_IMG_PATH) . 'delete_icon.png" title="' . get_lang('_unsubscribe') . '"  alt="' . get_lang('_unsubscribe') . '" /></form>';
    } else {
      display_info_text(get_lang('UnsubscribeNotAllowed'));
    }
  } else {
    if ((isset($_GET['edit']) && $_GET['edit'] != $course['code']) || (!isset($_GET['edit']))) {
      display_info_text(get_lang('CourseAdminUnsubscribeNotAllowed'));
    }
  }
}

/**
 * displays the relevant icons for the category (if applicable):move up, move down, edit, delete
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @param 	$current_category the id of the current category
 * 			$allcategories an associative array containing all the categories.
 * @return html: a small table containing the up/down icons and the edit icon (for moving to a different user course category)
 * @todo complete the comments on this function: the parameter section
 */
function display_category_icons($current_category, $all_user_categories) {
  global $safe, $stok;
  $max_category_key = count($all_user_categories);

  if ($safe['action'] != 'unsubscribe') { // we are in the unsubscribe section then we do not show the icons.
    echo "<table>";
    echo "<tr>";
    echo "<td>&nbsp;";
    /* if ($current_category != $all_user_categories[0]) {
      echo "<a href=\"courses.php?action=".$safe['action']."&amp;move=up&amp;category=".$current_category."&amp;sec_token=".$stok."\">";
      echo Display::return_icon('up.gif', get_lang('Up')).'</a>';
      } */
    echo "</td>";
    echo " <td rowspan=\"2\" style='text-align: center; width: 10%; padding-right: 0px; vertical-align: top;'>";
    echo " <a href=\"courses.php?action=sortmycourses&amp;categoryid=" . $current_category . "&amp;sec_token=" . $stok . "#category" . $current_category . "\">";
    Display::display_icon('pixel.gif', get_lang('Edit'),array('class'=>'actionplaceholdericon actionedit'));
    echo "</a>";
    echo "</td>";
    echo "<td rowspan=\"2\" style='text-align: center; width: 10%; padding-right: 0px; vertical-align: top;'>";
    echo " <a href=\"courses.php?action=deletecoursecategory&amp;id=" . $current_category . "&amp;sec_token=" . $stok . "\">";
    Display::display_icon('pixel.gif', get_lang('Delete'), array('class'=>'actionplaceholdericon actiondelete','onclick' => "javascript: if (!confirm('" . addslashes(api_htmlentities(get_lang("CourseCategoryAbout2bedeleted"), ENT_QUOTES, api_get_system_encoding())) . "')) return false;"));
    echo "</a>";
    echo "</td>";
    echo "</tr>";
    echo "<tr>";
    echo " <td>&nbsp;";
    /* if ($current_category != $all_user_categories[$max_category_key - 1]) {
      echo "<a href=\"courses.php?action=".$safe['action']."&amp;move=down&amp;category=".$current_category."&amp;sec_token=".$stok."\">";
      echo Display::return_icon('down.gif', get_lang('Down')).'</a>';
      } */
    echo "</td>";
    echo " </tr>";
    echo "</table>";
  }
}

/**
 * This function displays the form (dropdown list) to move a course to a
 * different course_category (after the edit icon has been changed)
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @param string $edit_course:
 * @return html a dropdown list containing all the user defined course categories and a submit button
 * @todo when editing (moving) a course inside a user defined course category to a different user defined category
 * 			the dropdown list should have the current course category selected.
 */
function display_change_course_category_form($edit_course) {
  global $_user, $_configuration, $safe, $stok;
  $edit_course = Security::remove_XSS($edit_course);

  $DATABASE_USER_TOOLS = $_configuration['user_personal_database'];
  $tucc = Database::get_user_personal_table(TABLE_USER_COURSE_CATEGORY);
  $sql = "SELECT * FROM $tucc WHERE user_id='" . $_user['user_id'] . "'";
  $result = Database::query($sql, __FILE__, __LINE__);

  $output = "<form name=\"edit_course_category\" method=\"post\" action=\"courses.php?action=" . $safe['action'] . "\">\n";
  $output .= '<input type="hidden" name="sec_token" value="' . $stok . '" />';
  $output .= "<input type=\"hidden\" name=\"course_2_edit_category\" value=\"" . $edit_course . "\" />";
  $output .= "\t<select name=\"course_categories\">\n";
  $output .= "\t\t<option value=\"0\">" . get_lang("NoCourseCategory") . "</option>";
  while ($row = Database::fetch_array($result)) {
    $output.="\t\t<option value=\"" . $row['id'] . "\">" . $row['title'] . "</option>";
  }
  $output .= "\t</select>\n";
  $output .= "\t&nbsp;&nbsp;<button class=\"save\" type=\"submit\" name=\"submit_change_course_category\">" . get_lang('Ok') . "</button>\n";
  $output .= "</form>";
  return $output;
}

/**
 * This function displays the unsubscribe part which can be
 * 1. the unsubscribe link
 * 2. text: you are course admin of this course (=> unsubscription is not possible
 * 3. text: you are not allowed to unsubscribe from this course
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @param array $course: the array with all the course & course_rel_user information
 * @return html a delete icon or a text that unsubscribing is not possible (course admin) or not allowed.
 */
function display_unsubscribe_icons($course) {
  global $stok;
  if ($course['status'] != 1) {
    if ($course['unsubscribe'] == 1) {
      // Changed link to submit to avoid action by the search tool indexer.
      echo "<form action=\"" . api_get_self() . "\" method=\"post\" onsubmit=\"javascript: if (!confirm('" . addslashes(api_htmlentities(get_lang('ConfirmUnsubscribeFromCourse'), ENT_QUOTES, api_get_system_encoding())) . "')) return false;\">";
      echo '<input type="hidden" name="sec_token" value="' . $stok . '" />';
      echo "<input type=\"hidden\" name=\"unsubscribe\" value=\"" . $course['code'] . "\" />";
      echo "<input type=\"image\" name=\"unsub\" src=\"" . api_get_path(WEB_IMG_PATH) . "delete_icon.png\" alt=\"" . get_lang('_unsubscribe') . "\" /></form>";
    } else {
      display_info_text(get_lang('UnsubscribeNotAllowed'));
    }
  } else {
    display_info_text(get_lang('CourseAdminUnsubscribeNotAllowed'));
  }
}

/**
 * retrieves all the courses that the user has already subscribed to
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @param int $user_id: the id of the user
 * @return array an array containing all the information of the courses of the given user
 */
function get_courses_of_user($user_id) {
  $TABLECOURS = Database::get_main_table(TABLE_MAIN_COURSE);
  $TABLECOURSUSER = Database::get_main_table(TABLE_MAIN_COURSE_USER);

  // Secondly we select the courses that are in a category (user_course_cat<>0) and sort these according to the sort of the category
  $user_id = intval($user_id);
  $sql_select_courses = "SELECT course.code k, course.visual_code  vc, course.subscribe subscr, course.unsubscribe unsubscr,
								course.title i, course.tutor_name t, course.db_name db, course.directory dir, course_rel_user.status status,
								course_rel_user.sort sort, course_rel_user.user_course_cat user_course_cat
		                        FROM    $TABLECOURS       course,
										$TABLECOURSUSER  course_rel_user
		                        WHERE course.code = course_rel_user.course_code
		                        AND   course_rel_user.user_id = '" . $user_id . "'
		                        ORDER BY course_rel_user.sort ASC";
  $result = Database::query($sql_select_courses, __FILE__, __LINE__);
  while ($row = Database::fetch_array($result)) {
    // we only need the database name of the course
    $courses[] = array('db' => $row['db'], 'code' => $row['k'], 'visual_code' => $row['vc'], 'title' => $row['i'], 'directory' => $row['dir'], 'status' => $row['status'], 'tutor' => $row['t'], 'subscribe' => $row['subscr'], 'unsubscribe' => $row['unsubscr'], 'sort' => $row['sort'], 'user_course_category' => $row['user_course_cat']);
  }

  return $courses;
}

/**
 * retrieves the user defined course categories
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @return array containing all the IDs of the user defined courses categories, sorted by the "sort" field
 */
function get_user_course_categories() {
  global $_user;
  $table_category = Database::get_user_personal_table(TABLE_USER_COURSE_CATEGORY);
  $sql = "SELECT * FROM " . $table_category . " WHERE user_id='" . $_user['user_id'] . "' ORDER BY sort ASC";
  $result = Database::query($sql, __FILE__, __LINE__);
  while ($row = Database::fetch_array($result)) {
    $output[] = $row['id'];
  }
  return $output;
}

/**
 * Retrieves the user defined course categories and all the info that goes with it
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @return array containing all the info of the user defined courses categories with the id as key of the array
 */
function get_user_course_categories_info() {
  global $_user;
  $table_category = Database::get_user_personal_table(TABLE_USER_COURSE_CATEGORY);
  $sql = "SELECT * FROM " . $table_category . " WHERE user_id='" . $_user['user_id'] . "' ORDER BY sort ASC";
  $result = Database::query($sql, __FILE__, __LINE__);
  while ($row = Database::fetch_array($result)) {
    $output[$row['id']] = $row;
  }
  return $output;
}

/**
 * @author unknown
 * @param string $text: the text that has to be written in grey
 * @return string: the text with the grey formatting
 * @todo move this to a stylesheet
 * Added id grey to CSS
 */
function display_info_text($text) {
  //echo "<font color=\"#808080\">" . $text . "</font>\n";
  echo $text;
}

/**
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @param string $edit_course:
 * @return html output: the form
 */
function display_edit_course_category_form($edit_course_category) {
  global $safe, $stok;
  echo "<form name=\"edit_course_category\" method=\"post\" action=\"courses.php?action=" . $safe['action'] . "\">\n";
  echo "\t<input type=\"hidden\" name=\"edit_course_category\" value=\"" . $edit_course_category . "\" />\n";
  echo '<input type="hidden" name="sec_token" value="' . $stok . '" />';
  $info_this_user_course_category = get_user_course_category($edit_course_category);
  echo "\t<input type=\"text\" name=\"title_course_category\" value=\"" . $info_this_user_course_category['title'] . "\" />";
  echo "\t<button class=\"save\" type=\"submit\" name=\"submit_edit_course_category\">" . get_lang('Ok') . "</button>\n";
  echo "</form>";
}

/**
 * Updates the user course category in the dokeos_user database
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @return string a language variable saying that the user course category was stored
 */
function store_edit_course_category() {
  global $_user, $_configuration;
  $tucc = Database::get_user_personal_table(TABLE_USER_COURSE_CATEGORY);
  $sql_update = "UPDATE $tucc SET title='" . api_htmlentities($_POST['title_course_category'], ENT_QUOTES, api_get_system_encoding()) . "' WHERE id='" . (int) $_POST['edit_course_category'] . "'";
  Database::query($sql_update, __FILE__, __LINE__);
  return get_lang('CourseCategoryEditStored');
}
