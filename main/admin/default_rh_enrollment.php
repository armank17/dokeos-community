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
require_once(api_get_path(LIBRARY_PATH).'usermanager.lib.php');


// setting the tabs
$this_section=SECTION_PLATFORM_ADMIN;

// Access restrictions
api_protect_admin_script(true);

$htmlHeadXtra[] = '<script type="text/javascript">
var indice=0;
function moveItem(origin , destination1){

	for(var i = 0 ; i<origin.options.length ; i++) {
		if(origin.options[i].selected) {
			if(indice==0)
                        {
                            destination1.options[0] = new Option(origin.options[i].text,origin.options[i].value);
                            origin.options[i]=null;
                            i = i-1;
                            indice=1;
                        }
		}
	}
       

	destination1.selectedIndex = -1;
	sortOptions(destination1.options);


}
function selection(origin)
{

for(var i = 0 ; i<origin.options.length ; i++) {
		if(origin.options[i].selected) {
			}
	}
  
}


function valide(){


	document.forms.learner_enrolment.submit();
}

</script>';

// Display the header
Display::display_header('');

// start the content div
echo '<div id="content">';

// Handling the form
form_action_handling();
delete_rh();
// initiate the object
$form = new FormValidator('learner_enrolment','post','default_rh_enrollment.php');

$renderer = & $form->defaultRenderer();
// form header (title)
$form->addElement('header', '', get_lang('EnrollmentToHumanResourceManagerAtRegistrationToPortal'));
$form->addElement('html','<div>'.get_lang('AutomaticRegistrationHumanResourceManager').'</div><br/>');
// No enrolment courses list
$ne_course_list = get_enrolment_and_no_enrolment_user_rh_list(0);
$no_enrolment_list = '';
foreach ($ne_course_list as $key => $ne_value) {
    $no_enrolment_list .= '<option value="'.$key.'">'.$ne_value['name'].'</option>';
}

// Enrolment resource human manager list
$e_course_list = get_enrolment_and_no_enrolment_user_rh_list(1);
$default_enrollment_list_message = get_lang('ThereIsNoADefaultHRMAssignedAddOneOnTopList');
foreach ($e_course_list as $key => $e_value) {
  $iduse=$e_value['user_id'];
  $default_human_resource_name = $e_value['name'];
}
$html='';
if($iduse>0) {
    
    $html='<a href="'.api_get_self().'?action=delete_rh&amp;user_id='.$iduse.'" >'.$default_human_resource_name.'&nbsp;&nbsp;'.Display::return_icon('delete.png', get_lang('Delete'),array('align' => 'ABSMIDDLE')).'</a>';
}


// Html content
$form->addElement('html', '<div class="section"><div class="sectiontitle">'.get_lang('HumanResourceManagerList').'</div> <br/><br/><table style="text-align: left; width: 100%; height: 120px;" border="0" cellpadding="0" cellspacing="0">
<tbody>
<tr>
    <td style="vertical-align: top;" width="45%" align="left">
      
      <select id="origin" name="training_enrolment_list[]" style="width: 320px;" onclick="selection(document.getElementById(\'origin\'))">
      <option value="">'.get_lang("SelectUser").'</option>
      '.$no_enrolment_list.'
      </select>
    </td>
</tr>
<tr><td>
<button type="submit" name="SubmitForm" onclick="valide()" class="save">'. get_lang('Validate').'</button>
</td></tr>
</tbody>
</table>
</div>'
        );

if (!empty($html)) {
  $default_enrollment_list_message = "";
}

// submit button
$form->addElement('html', '<br><br><div class="section"> <span class="sectiontitle">'.get_lang('DefaultHumanResourceManager').'</span><br>'.$default_enrollment_list_message.' '.$html.' </div>');
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
      // exit($default_enrolment);
       $enrol_to_training = array();
       if (is_array($_POST['training_enrolment_list'])) {
         $default_enrolment = Security::remove_XSS($_POST['training_enrolment_list']);
         // Clean the enrolment list
         clean_current_default_enrolment();
         // Add the new enrolment list
         foreach ($default_enrolment as $rh_id) {
          //   exit($rh_id);
           add_rh_to_default_enrolment($rh_id);

         }
       }
    } else {
       // Clean the enrolment list
       if (isset($_POST['SubmitForm'])) {
         // clean_current_default_enrolment();
       }
    }
}

/**
 * Get the human resource list
 * @param integer The enrolment type for get the course list, 0 for no enrolment courses and 1 for enrolment courses
 * @return array The course lists
 */
function get_enrolment_and_no_enrolment_user_rh_list ($enrolment_type) {
 return UserManager::get_enrolment_and_no_enrolment_rh_list($enrolment_type);
}
/**
 * Add the training for be a course for default enrolment
 * @param string $course_id
 */

function add_rh_to_default_enrolment ($rh_id) {
   $user_table = Database::get_main_table(TABLE_MAIN_USER);
   
   $sqldel = "UPDATE $user_table SET default_enrolment = 0" ;
     $rsdel = Database::query($sqldel, __FILE__, __LINE__);
   $sql = "UPDATE $user_table SET default_enrolment = 1 WHERE user_id = '".Database::escape_string($rh_id)."'";
  
  // exit($sql);
   $rs = Database::query($sql, __FILE__, __LINE__);
}

function clean_current_default_enrolment () {
   $course_table = Database::get_main_table(TABLE_MAIN_COURSE);
  $current_enrolment_list = get_enrolment_and_no_enrolment_user_rh_list(1);
  if (is_array($current_enrolment_list)) {
    foreach ($current_enrolment_list as $course_id => $course_details) {
     $sql = "UPDATE $course_table SET default_enrolment = 0 WHERE code = '".Database::escape_string($course_id)."'";
     $rs = Database::query($sql, __FILE__, __LINE__);
    }
  }
}



function delete_rh()
{
    if(isset($_GET['action']) == 'delete_rh')
{
       // exit($_GET['user_id']);
	//To delete glossary
	deleterh(Security::remove_XSS($_GET['user_id']));
	
}
	// Database table definition
   /* exit($_GET['user_id']);
	$t_glossary = Database :: get_course_table(TABLE_GLOSSARY);

	$sql = "DELETE FROM $t_glossary WHERE glossary_id='".Database::escape_string($glossary_id)."'";
	$result = Database::query($sql, __FILE__, __LINE__);

	//update item_property (delete)
	api_item_property_update(api_get_course_info(), TOOL_GLOSSARY, Database::escape_string($glossary_id), 'delete', api_get_user_id());

	// reorder the remaining terms
	reorder_glossary();
	$_SESSION['max_glossary_display'] = get_max_glossary_item();
	//Display::display_confirmation_message(get_lang('TermDeleted'));*/
}

function deleterh($user_id)
{
 //   exit($user_id);
    $user_table = Database::get_main_table(TABLE_MAIN_USER);
    $sqldel = "UPDATE $user_table SET default_enrolment = 0" ;
    $rsdel = Database::query($sqldel, __FILE__, __LINE__);
  

}


?>
