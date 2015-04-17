<?php

/* For licensing terms, see /dokeos_license.txt */

/**
==============================================================================
*	@package dokeos.group
==============================================================================
*/

// name of the language file that needs to be included
$language_file = "group";

// including the global Dokeos file
require_once ('../inc/global.inc.php');

$this_section = SECTION_COURSES;

// including additional libraries
require_once (api_get_path(LIBRARY_PATH).'course.lib.php');
require_once (api_get_path(LIBRARY_PATH).'groupmanager.lib.php');
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');

// the section (fro the tabs)
$this_section = SECTION_COURSES;

// get all the information of the group
$group_id = isset($_GET['group_id'])?intval($_GET['group_id']):intval($_SESSION['_gid']); 
$current_group = GroupManager :: get_group_properties($group_id);

$nameTools = get_lang('EditGroup');

// breadcrumbs
$interbreadcrumb[] = array ("url" => "group.php", "name" => get_lang('Groups'));

// access restriction
if (!api_is_allowed_to_edit(false,true)) {
	api_not_allowed(true);
}
/*
==============================================================================
		FUNCTIONS
==============================================================================
*/

/**
 * function to sort users after getting the list in the db. Necessary because there are 2 or 3 queries. Called by usort()
 */

function sort_users($user_a, $user_b) {
	if (api_sort_by_first_name()) {
		$cmp = api_strcmp($user_a['firstname'], $user_b['firstname']);
		if ($cmp !== 0) {
			return $cmp;
		} else {
			$cmp = api_strcmp($user_a['lastname'], $user_b['lastname']);
			if ($cmp !== 0) {
				return $cmp;
			} else {
				return api_strcmp($user_a['username'], $user_b['username']);
			}
		}
	} else {
		$cmp = api_strcmp($user_a['lastname'], $user_b['lastname']);
		if ($cmp !== 0) {
			return $cmp;
		} else {
			$cmp = api_strcmp($user_a['firstname'], $user_b['firstname']);
			if ($cmp !== 0) {
				return $cmp;
			} else {
				return api_strcmp($user_a['username'], $user_b['username']);
			}
		}
	}
}

/**
 * Function to check the given max number of members per group
 */
function check_max_number_of_members($value) {
	$max_member_no_limit = $value['maximum_number_of_students'];
	if ($max_member_no_limit == MEMBER_PER_GROUP_NO_LIMIT) {
		return true;
	}
	$max_member = $value['max_member'];
	return is_numeric($max_member);
}
/**
 * Function to check if the number of selected group members is valid
 */
function check_group_members($value) {
	
	if (isset($value['max_member']) && isset($value['group_members']) && $value['max_member'] < count($value['group_members'])) {
		return array ('group_members' => get_lang('GroupTooMuchMembers'));
	}
	return true;
}
/*
==============================================================================
		MAIN CODE
==============================================================================
*/

$htmlHeadXtra[] = '<style>
    #group_tutors_hidden, #group_members_hidden {
        overflow: hidden; 
        visibility: hidden; 
        width: 1px; 
        height: 0;
    }
    #group_tutors_active, #group_members_active {
        width: 300px;
        height: 200px;
    }
</style>';

// Build form
$form = new FormValidator('group_edit');

// form title
$form->addElement('header', '', $nameTools);

// hidden  form elements
$form->addElement('hidden', 'action');
$form->addElement('hidden', 'referer');

$form->addElement('hidden', 'group_id', $group_id);

// Group name
$form->add_textfield('name', get_lang('GroupName'));

// Description
$form->addElement('textarea', 'description', get_lang('GroupDescription'), array ('cols' => 50, 'rows' => 6));

$limit = range(0,20);
$form->addElement('select', 'maximum_number_of_students', get_lang('GroupLimit'), $limit);

// getting all the users
if (isset($_SESSION['id_session'])) {
    $complete_user_list = CourseManager :: get_user_list_from_course_code($_course['id'],true,$_SESSION['id_session']);
    $complete_user_list2 = CourseManager :: get_coach_list_from_course_code($_course['id'],$_SESSION['id_session']);
    $complete_user_list = array_merge($complete_user_list,$complete_user_list2);
    usort($complete_user_list, 'sort_users');
} else {
    $complete_user_list = CourseManager :: get_user_list_from_course_code($_course['id'],true, '','',api_get_setting('user_order_by'));
}

$possible_users = array ();
foreach ($complete_user_list as $index => $user) {
    $possible_users[$user['user_id']] = api_get_person_name($user['firstname'], $user['lastname']).' ('.$user['username'].')';
}

// Group members
$group_member_list = GroupManager :: get_subscribed_users($current_group['id']);
$selected_users = array ();
foreach ($group_member_list as $index => $user) {
    //$possible_users[$user['user_id']] = api_get_person_name($user['firstname'], $user['lastname']);
    $selected_users[] = $user['user_id'];
}
$group_members_element = $form->addElement('advmultiselect', 'group_members', get_lang('GroupMembers'), $possible_users, 'style="width: 320px;height:200px;"');
//$form->addElement('select', 'group_members', get_lang('GroupMembers'), $possible_users, 'style="width: 350px;height:200px;"');

$group_members_element->setElementTemplate('
{javascript}
<table cellpadding="0">
<!-- BEGIN label_2 --><tr><th>{label_2}</th><!-- END label_2 -->
<!-- BEGIN label_3 --><th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th><th>{label_3}</th></tr><!-- END label_3 -->
<tr>
  <td valign="top">{unselected}</td>
  <td align="center">&nbsp;&nbsp;&nbsp;&nbsp;</td>
  <td align="center">{add}<br /><br />{remove}</td>
  <td align="center">&nbsp;&nbsp;&nbsp;&nbsp;</td>
  <td valign="top">{selected}</td>
</tr>
</table>
');

$group_members_element->setButtonAttributes('add',array('class'=>'arrowr'));
$group_members_element->setButtonAttributes('remove',array('class'=>'arrowl'));
$form->addFormRule('check_group_members');

// Group members
$group_tutor_list = GroupManager :: get_group_tutors($current_group['id']);
$selected_tutors = array ();
foreach ($group_tutor_list as $index => $user) {
	//$possible_users[$user['user_id']] = api_get_person_name($user['firstname'], $user['lastname']);
	$selected_tutors[] = $user['user_id'];
}

$group_tutors_element = $form->addElement('advmultiselect', 'group_tutors', get_lang('GroupTutors'), $possible_users, 'style="width: 320px;height:100px;"');

$group_tutors_element->setElementTemplate('
{javascript}
<table cellpadding="0" >
<!-- BEGIN label_2 --><tr><th>{label_2}</th><!-- END label_2 -->
<!-- BEGIN label_3 --><th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th><th>{label_3}</th></tr><!-- END label_3 -->
<tr>
  <td align="left" valign="top">{unselected}</td>
  <td align="center">&nbsp;&nbsp;&nbsp;&nbsp;</td>
  <td align="center">{add}<br /><br />{remove}</td>
  <td align="center">&nbsp;&nbsp;&nbsp;&nbsp;</td>
  <td valign="top">{selected}</td>
</tr>
</table>
');

$group_tutors_element->setButtonAttributes('add',array('class'=>'arrowr'));
$group_tutors_element->setButtonAttributes('remove',array('class'=>'arrowl'));

// submit button
$form->addElement('style_submit_button', 'submit', get_lang('PropModify'), 'class="save"');

if ($form->validate()) {
	$values = $form->exportValues();
	$table_group = Database :: get_course_table(TABLE_GROUP);
	Database::query("UPDATE ".$table_group." SET
                            name = '".Database::escape_string($values['name'])."', 
                            description = '".Database::escape_string($values['description'])."', 
                            max_student = '".$values['maximum_number_of_students']."' 
                        WHERE id = ".$values['group_id']);
	
	// storing the tutors (we first remove all the tutors and then add only those who were selected)
	GroupManager :: unsubscribe_all_tutors($values['group_id']);
	if (isset ($_POST['group_tutors']) && count($_POST['group_tutors']) > 0) {
		GroupManager :: subscribe_tutors($_POST['group_tutors'], $values['group_id']);
	}

	// storing the users (we first remove all users and then add only those who were selected)
        if(count($_POST['group_members'])<=$_POST['maximum_number_of_students']){          
               GroupManager :: unsubscribe_all_users($values['group_id']);
               if (isset ($_POST['group_members']) && count($_POST['group_members']) > 0 && count($_POST['group_members']) <= $_POST['maximum_number_of_students']) {
               GroupManager :: subscribe_users($_POST['group_members'], $values['group_id']);
               $_SESSION['group_members_message'] = get_lang('AddGroupMembers');               
               }
        }  else {   
           $_SESSION['group_limit_message'] = get_lang('ExceedsLimitMembers');
        }
	
	// returning to the group area (note: this is inconsistent with the rest of dokeos)	
	header('Location: group.php?action=show_msg&msg='.get_lang('GroupEdited'));
}

// the default values of the group
$defaults = $current_group;
$defaults['group_members'] = array_unique($selected_users);
$defaults['group_tutors'] = array_unique($selected_tutors);


if (isset($_POST['group_members'])) {
	// if it has been specified that there is a limit (contradictionary this is max_member_no_limit = 1) then we have to check if the limit 
	// has been exceeded or not
	if($_POST['max_member_no_limit'] == 1){
		if (count($_POST['group_members'])<=$defaults['max_member'] AND $defaults['max_member_no_limit']) {
			//
		} else {
			header('Location:group_edit.php?show_message='.get_lang('GroupTooMuchMembers'));
		}
	}
}

// display the header
Display :: display_tool_header($nameTools, "Group");
// Tool introduction
Display::display_introduction_section(TOOL_USER, 'left');
?>

<div class="actions">
    
 <a href="<?php echo api_get_path(WEB_CODE_PATH); ?>group/group.php?<?php echo api_get_cidreq(); ?>"><?php  echo Display::return_icon('pixel.gif', get_lang('List'), array('class' => 'toolactionplaceholdericon toolactionback')).get_lang('List') ?></a>
 <a href="<?php echo api_get_path(WEB_CODE_PATH); ?>group/group_scenario.php?<?php echo api_get_cidreq(); ?>&amp;group_id=<?php echo $group_id; ?>"><?php  echo Display::return_icon('pixel.gif',get_lang('Scenario'),array('class' => 'toolactionplaceholdericon toolactionscenario')).get_lang('Scenario') ?></a>

</div>

<?php
//start the content div
echo '<div id="content">';

if (isset($_GET['show_message'])) {
	echo Display::display_error_message($_GET['show_message']);
}
$defaults['referer'] = $referer;
$form->setDefaults($defaults);
$form->display();

// close the content div
echo '</div>';
// display the footer
Display :: display_footer();
?>
