<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* This script allows platform admins to add users to courses.
* It displays a list of users and a list of courses;
* you can select multiple users and courses and then click on
* 'Add to this(these) course(s)'.
* @package dokeos.admin
*/

// Language files that should be included
$language_file = array ('admin', 'registration','create_course', 'document');

// resetting the course id
$cidReset = true;

// setting the help
$help_content = 'platformadministrationsubscribeuser2course';

// including the global Dokeos file
require_once '../inc/global.inc.php';

// including additional libraries
require_once(api_get_path(LIBRARY_PATH).'course.lib.php');
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
include_once (api_get_path(LIBRARY_PATH).'usermanager.lib.php');

// Setting the section (for the tabs).
$this_section=SECTION_PLATFORM_ADMIN;

// user permissions
api_protect_admin_script();

// Setting the breadcrumbs
$interbreadcrumb[] = array ("url" => 'index.php', "name" => get_lang('PlatformAdmin'));

$htmlHeadXtra[] = '
<script type="text/javascript">
function validate_filter() {
	
		document.formulaire.form_sent.value=0;				
		document.formulaire.submit();
		
}	
</script>';

// Database table definititions
$tbl_course = Database :: get_main_table(TABLE_MAIN_COURSE);
$tbl_user 	= Database :: get_main_table(TABLE_MAIN_USER);


// Global constants and variables
$users = $_GET['users'];
$form_sent = 0;
$first_letter_user = '';
$first_letter_course = '';
$courses = array ();
$users = array();

// displaying the header
Display :: display_header(get_lang('AddUsersToACourse'));

// displaying the tool title
// api_display_tool_title(get_lang('AddUsersToACourse'));

//Actions
echo '<div class="actions">';
CourseManager::show_menu_course_admin('suscribe');
echo '</div>';

// start the content div
echo '<div id="content" class="maxcontent">';


$form = new FormValidator('subscribe_user2course');
$form->addElement('header', '', get_lang('AddUsersToACourse'));
$form->display();


//checking for extra field with filter on
$extra_field_list= UserManager::get_extra_fields();
$new_field_list = array();
if (is_array($extra_field_list)) {
	foreach ($extra_field_list as $extra_field) {
		//if is enabled to filter and is a "<select>" field type
		if ($extra_field[8]==1 && $extra_field[2]==4 ) {
			$new_field_list[] = array('name'=> $extra_field[3], 'variable'=>$extra_field[1], 'data'=> $extra_field[9]);
		}
	}
}


// action handling
if ($_POST['form_sent']) {
	$form_sent = $_POST['form_sent'];
	$users = is_array($_POST['UserList']) ? $_POST['UserList'] : array() ;
	$courses = is_array($_POST['CourseList']) ? $_POST['CourseList'] : array() ;
	$first_letter_user = $_POST['firstLetterUser'];
	$first_letter_course = $_POST['firstLetterCourse'];

	foreach ($users as $key => $value) {
		$users[$key] = intval($value);
	}

	if ($form_sent == 1) {
		if ( count($users) == 0 || count($courses) == 0) {
			//Display :: display_confirmation_message2(get_lang('AtLeastOneUserAndOneCourse'), false, true);
                        //$_SESSION['display_warning_message'] = get_lang('AtLeastOneUserAndOneCourse');
                        Display :: display_normal_message(get_lang('AtLeastOneUserAndOneCourse'), false, true);
		} else {
			foreach ($courses as $course_code) {
				foreach ($users as $user_id) {
					CourseManager::subscribe_user($user_id,$course_code);
				}
			}
			//Display :: display_confirmation_message2(get_lang('UsersAreSubscibedToCourse'), false, true);
                        $_SESSION['display_confirmation_message'] = get_lang('UsersAreSubscibedToCourse');
		}
	}
}

/*
-----------------------------------------------------------
	Display GUI
-----------------------------------------------------------
*/
if(empty($first_letter_user)) {
	$sql = "SELECT count(*) as nb_users FROM $tbl_user";
	$result = Database::query($sql, __FILE__, __LINE__);
	$num_row = Database::fetch_array($result);
	if($num_row['nb_users']>1000)
	{//if there are too much users to gracefully handle with the HTML select list,
	 // assign a default filter on users names
		$first_letter_user = 'A';
	}
	unset($result);
}

//Filter by Extra Fields
$use_extra_fields = false;
if (is_array($extra_field_list)) {
	if (is_array($new_field_list) && count($new_field_list)>0 ) {
		$result_list=array();
		foreach ($new_field_list as $new_field) {
			$varname = 'field_'.$new_field['variable'];
			if (Usermanager::is_extra_field_available($new_field['variable'])) {
				if (isset($_POST[$varname]) && $_POST[$varname]!='0') {
					$use_extra_fields = true;
					$extra_field_result[]= Usermanager::get_extra_user_data_by_value($new_field['variable'], $_POST[$varname]);
				}
			}
		}
	}
}

if ($use_extra_fields) {
	$final_result = array();
	if (count($extra_field_result)>1) {
		for($i=0;$i<count($extra_field_result)-1;$i++) {
			if (is_array($extra_field_result[$i+1])) {
				$final_result  = array_intersect($extra_field_result[$i],$extra_field_result[$i+1]);
			}
		}
	} else {
		$final_result = $extra_field_result[0];
	}

	$where_filter ='';
	if ($_configuration['multiple_access_urls']==true) {
		if (is_array($final_result) && count($final_result)>0) {
			$where_filter = " AND u.user_id IN  ('".implode("','",$final_result)."') ";
		} else {
			//no results
			$where_filter = " AND u.user_id  = -1";
		}
	} else {
		if (is_array($final_result) && count($final_result)>0) {
			$where_filter = " AND user_id IN  ('".implode("','",$final_result)."') ";
		} else {
			//no results
			$where_filter = " AND user_id  = -1";
		}
	}
}

$target_name = api_sort_by_first_name() ? 'firstname' : 'lastname';
$sql = "SELECT user_id,lastname,firstname,username
		FROM $tbl_user
		WHERE user_id<>2 AND ".$target_name." LIKE '".$first_letter_user."%' $where_filter
		ORDER BY ". (count($users) > 0 ? "(user_id IN(".implode(',', $users).")) DESC," : "")." ".$target_name;

global $_configuration;
if ($_configuration['multiple_access_urls']==true) {
	$tbl_user_rel_access_url= Database::get_main_table(TABLE_MAIN_ACCESS_URL_REL_USER);
	$access_url_id = api_get_current_access_url_id();
	if ($access_url_id != -1){
		$sql = "SELECT u.user_id,lastname,firstname,username  FROM ".$tbl_user ." u
		INNER JOIN $tbl_user_rel_access_url user_rel_url
		ON (user_rel_url.user_id = u.user_id)
		WHERE u.user_id<>2 AND access_url_id =  $access_url_id AND (".$target_name." LIKE '".$first_letter_user."%' ) $where_filter
		ORDER BY ". (count($users) > 0 ? "(u.user_id IN(".implode(',', $users).")) DESC," : "")." ".$target_name;
	}
}

$result = Database::query($sql, __FILE__, __LINE__);
$db_users = Database::store_result($result);
unset($result);

$sql = "SELECT code,visual_code,title FROM $tbl_course WHERE visual_code LIKE '".$first_letter_course."%' ORDER BY ". (count($courses) > 0 ? "(code IN('".implode("','", $courses)."')) DESC," : "")." visual_code";

if ($_configuration['multiple_access_urls']==true) {
	$tbl_course_rel_access_url= Database::get_main_table(TABLE_MAIN_ACCESS_URL_REL_COURSE);
	$access_url_id = api_get_current_access_url_id();
	if ($access_url_id != -1){
		$sql = "SELECT code, visual_code, title
				FROM $tbl_course as course
		  		INNER JOIN $tbl_course_rel_access_url course_rel_url
				ON (course_rel_url.course_code= course.code)
		  		WHERE access_url_id =  $access_url_id  AND (visual_code LIKE '".$first_letter_course."%' ) ORDER BY ". (count($courses) > 0 ? "(code IN('".implode("','", $courses)."')) DESC," : "")." visual_code";
	}
}

$result = Database::query($sql, __FILE__, __LINE__);
$db_courses = Database::store_result($result);
unset($result);

if ($_configuration['multiple_access_urls']==true) {
	$tbl_course_rel_access_url= Database::get_main_table(TABLE_MAIN_ACCESS_URL_REL_COURSE);
	$access_url_id = api_get_current_access_url_id();
	if ($access_url_id != -1){
		$sqlNbCours = "	SELECT course_rel_user.course_code, course.title
			FROM $tbl_course_user as course_rel_user
			INNER JOIN $tbl_course as course
			ON course.code = course_rel_user.course_code
		  	INNER JOIN $tbl_course_rel_access_url course_rel_url
			ON (course_rel_url.course_code= course.code)
		  	WHERE access_url_id =  $access_url_id  AND course_rel_user.user_id='".$_user['user_id']."' AND course_rel_user.status='1'
		  	ORDER BY course.title";
	}
}


?>

<form name="formulaire" method="post" action="<?php echo api_get_self(); ?>">
<?php

if (is_array($extra_field_list)) {
	if (is_array($new_field_list) && count($new_field_list)>0 ) {
		echo '<h3>'.get_lang('FilterUsers').'</h3>';
		foreach ($new_field_list as $new_field) {
			echo $new_field['name'];
			$varname = 'field_'.$new_field['variable'];
			echo '&nbsp;<select name="'.$varname.'">';
			echo '<option value="0">--'.get_lang('Select').'--</option>';
			foreach	($new_field['data'] as $option) {
				$checked='';
				if (isset($_POST[$varname])) {
					if ($_POST[$varname]==$option[1]) {
						$checked = 'selected="true"';
					}
				}
				echo '<option value="'.$option[1].'" '.$checked.'>'.$option[1].'</option>';
			}
			echo '</select>';
			echo '&nbsp;&nbsp;';
		}
		echo '<input type="button" value="'.get_lang('Filter').'" onclick="validate_filter()" />';
		echo '<br /><br />';
	}
}

?>


<input type="hidden" name="form_sent" value="1"/>

<div id="addUsersToTraining">
	<div id="columns">
		<div class="columnleft" id="userList">
			<span class="columnTitle"><?php echo get_lang('Users'); ?></span>&nbsp;
			<select name="firstLetterUser" onchange="javascript:document.formulaire.form_sent.value='2'; document.formulaire.submit();">
				<option value="">--</option>
				<?php
					echo Display :: get_alphabet_options($first_letter_user);
				?>
			</select>
			<div class="list">
				<select name="UserList[]" multiple="multiple" size="20" class="focus" style="width:375px;">
					<?php
					foreach ($db_users as $user) {
					?>
					<option value="<?php echo $user['user_id']; ?>" <?php if(in_array($user['user_id'],$users)) echo 'selected="selected"'; ?>><?php echo api_get_person_name($user['firstname'], $user['lastname']).' ('.$user['username'].')'; ?></option>
					<?php
					}
					?>
				</select>
			</div>
		</div>
		<div class="columnright" id="trainingList">
			<span class="columnTitle"><?php echo get_lang('Trainings'); ?></span>&nbsp;
			<select name="firstLetterCourse" onchange="javascript:document.formulaire.form_sent.value='2'; document.formulaire.submit();">
				<option value="">--</option>
				<?php
				echo Display :: get_alphabet_options($first_letter_course);
				?>
			</select>
			<div class="list">
				<select name="CourseList[]" multiple="multiple" size="20" class="columnselect" style="width:375px;">
					<?php
					foreach ($db_courses as $course) {
					?>
					<option value="<?php echo $course['code']; ?>" <?php if(in_array($course['code'],$courses)) echo 'selected="selected"'; ?>><?php echo '('.$course['visual_code'].') '.$course['title']; ?></option>
					<?php
					}
					?>
				</select>
			</div>
			<button type="submit" class="add margin" value="<?php echo get_lang('AddUsersToTrainings'); ?> &gt;&gt;"><?php echo get_lang('AddUsersToTrainings'); ?></button>
		</div>
		<div class="columncenter columncenterbuttons" id="arrow">
           <?php 
             echo Display::return_icon('pixel.gif','',array('class'=>'toolactionplaceholdericon toolactionarrow'));
           ?>
	    </div>
	</div>
</div>


</form>
<?php
// close the content div
echo '</div>';	

if(isset($_SESSION['display_confirmation_message'])){
    display::display_confirmation_message2($_SESSION['display_confirmation_message'], false,true);
    unset($_SESSION['display_confirmation_message']);
}

// Display the footer
Display :: display_footer();
?>
