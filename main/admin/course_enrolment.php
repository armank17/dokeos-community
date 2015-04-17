<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* Course enrolment: This script add a courses for be a default enrolments.
* @package dokeos.admin
*/

// Language files that should be included
$language_file[] = 'admin';

// resetting the course id
$cidReset=true;

// including the global Dokeos file
require_once '../inc/global.inc.php';

// including additional libraries
include_once(api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
require_once(api_get_path(LIBRARY_PATH).'fileUpload.lib.php');
require_once api_get_path(LIBRARY_PATH).'course.lib.php';

// setting the tabs
$this_section=SECTION_PLATFORM_ADMIN;

// Access restrictions
api_protect_admin_script(true);

$htmlHeadXtra[] = '<script type="text/javascript">

function moveItem(origin , destination){

	for(var i = 0 ; i<origin.options.length ; i++) {
		if(origin.options[i].selected) {
			destination.options[destination.length] = new Option(origin.options[i].text,origin.options[i].value);
			origin.options[i]=null;
			i = i-1;
		}
	}
	destination.selectedIndex = -1;
	sortOptions(destination.options);


}

function sortOptions(options) {

	newOptions = new Array();

	for (i = 0 ; i<options.length ; i++) {
		newOptions[i] = options[i];
	}

	newOptions = newOptions.sort(mysort);
	options.length = 0;

	for(i = 0 ; i < newOptions.length ; i++){
		options[i] = newOptions[i];
	}

}

function mysort(a, b){
	if(a.text.toLowerCase() > b.text.toLowerCase()){
		return 1;
	}
	if(a.text.toLowerCase() < b.text.toLowerCase()){
		return -1;
	}
	return 0;
}

function valide(){
	var options = document.getElementById(\'destination\').options;
	for (i = 0 ; i<options.length ; i++)
		options[i].selected = true;

	document.forms.course_enrolment.submit();
}

</script>';

// Display the header
Display::display_header('');

//Actions
echo '<div class="actions">';
CourseManager::show_menu_course_admin('enrolment');
echo '</div>';

// start the content div
echo '<div id="content">';


// Handling the form
form_action_handling();

// initiate the object
$form = new FormValidator('course_enrolment','post','course_enrolment.php');

$renderer = & $form->defaultRenderer();
// form header (title)
$form->addElement('header', '', get_lang('EnrolmentToCoursesAtRegistrationToPortal'));
$form->addElement('html','<div>'.get_lang('AutomaticRegistration').'</div>');
// No enrolment courses list
$ne_course_list = get_enrolment_and_no_enrolment_course_list(0);
$no_enrolment_list = '';
foreach ($ne_course_list as $key => $ne_value) {
  $no_enrolment_list .= '<option value="'.$key.'">'.$ne_value['title'].'  ('.$ne_value['visual_code'].')'.'</option>';
}

// Enrolment courses list
$e_course_list = get_enrolment_and_no_enrolment_course_list(1);
$enrolment_list = '';
foreach ($e_course_list as $key => $e_value) {
  $enrolment_list .= '<option value="'.$key.'">'.$e_value['title'].'  ('.$e_value['visual_code'].')'.'</option>';
}

// Html content
$form->addElement('html', '<br/><br/><table style="text-align: left; width: 100%; height: 358px;" border="0" cellpadding="0" cellspacing="0">
<tbody>
<tr>
    <td style="vertical-align: top;" width="45%" align="center">
      <select id="origin" name="training_list[]" multiple="multiple" size="20" style="width: 320px;">
      '.$no_enrolment_list.'
      </select>
    </td>
    <td style="vertical-align: middle;" width="10%" align="center">
    <button class="button-blue" type="button" onclick="moveItem(document.getElementById(\'origin\'), document.getElementById(\'destination\'))"><img src="'. api_get_path(WEB_CSS_PATH).api_get_setting('stylesheets').'/images/action/arrow-white-right.png"/></button>
    <br/>
    <br/>
    <button class="button-blue" type="button" onclick="moveItem(document.getElementById(\'destination\'), document.getElementById(\'origin\'))"><img src="'. api_get_path(WEB_CSS_PATH).api_get_setting('stylesheets').'/images/action/arrow-white-left.png"/></button>
    </td>
    <td style="vertical-align: top;" width="45%" align="center">
      <select id="destination" name="training_enrolment_list[]" multiple="multiple" size="20" style="width: 320px;">
      '.$enrolment_list.'
      </select>
    </td>
</tr>
</tbody>
</table>');

// submit button
$form->addElement('style_submit_button', 'SubmitForm', get_lang('Validate'), 'class="add" onclick="valide()"');
$form->display();


// close the content div
echo '</div>';

// display the footer
Display::display_footer();

/**
 * In this function you can perform all the actions (mostly based on $_GET['action'] parameter or $_POST values
 */
function form_action_handling(){
    if (isset($_POST['training_enrolment_list'])) {
       $enrol_to_training = array();
       if (is_array($_POST['training_enrolment_list'])) {
         $default_enrolment = Security::remove_XSS($_POST['training_enrolment_list']);
         // Clean the enrolment list
         clean_current_default_enrolment();
         // Add the new enrolment list
         foreach ($default_enrolment as $course_id) {
           add_course_to_default_enrolment($course_id);
         }
       }
    } else {
       // Clean the enrolment list
       if (isset($_POST['SubmitForm'])) {
          clean_current_default_enrolment();
       }
    }
}

/**
 * Get the course list for enrolment and no enrolment trainings
 * @param integer The enrolment type for get the course list, 0 for no enrolment courses and 1 for enrolment courses
 * @return array The course lists
 */
function get_enrolment_and_no_enrolment_course_list ($enrolment_type = 0) {
 return CourseManager::get_enrolment_and_no_enrolment_course_list($enrolment_type);
}
/**
 * Add the training for be a course for default enrolment
 * @param string $course_id 
 */

function add_course_to_default_enrolment ($course_id) {
   $course_table = Database::get_main_table(TABLE_MAIN_COURSE);
   $sql = "UPDATE $course_table SET default_enrolment = 1 WHERE code = '".Database::escape_string($course_id)."'";
   $rs = Database::query($sql, __FILE__, __LINE__);
}

function clean_current_default_enrolment () {
   $course_table = Database::get_main_table(TABLE_MAIN_COURSE);
  $current_enrolment_list = get_enrolment_and_no_enrolment_course_list (1);
  if (is_array($current_enrolment_list)) {
    foreach ($current_enrolment_list as $course_id => $course_details) {
     $sql = "UPDATE $course_table SET default_enrolment = 0 WHERE code = '".Database::escape_string($course_id)."'";
     $rs = Database::query($sql, __FILE__, __LINE__);
    }
  }
}
?>
