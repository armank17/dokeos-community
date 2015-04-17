<?php

/* For licensing terms, see /dokeos_license.txt */

/**
==============================================================================
* 	This script displays all the defined group scenarios and allows to add, edit or delete scenario's
*	@package dokeos.group
==============================================================================
*/

// name of the language file that needs to be included
$language_file = 'group';

// including the global Dokeos file
require_once ('../inc/global.inc.php');

// including additional libraries
require_once (api_get_path(LIBRARY_PATH).'groupmanager.lib.php');
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');

// the section (for the tabs)
$this_section = SECTION_COURSES;

// access control
api_protect_course_script(true);
if (!api_is_allowed_to_edit(false,true)){
	api_not_allowed();
}

// Additional javascript
$htmlHeadXtra[] = '
<script type="text/javascript">
<!--
function max_member_switch_radio_button() {
	var input_elements = document.getElementsByTagName("input");
	for (var i = 0; i < input_elements.length; i++) {
		if (input_elements.item(i).name == "max_member_no_limit" && input_elements.item(i).value == "1") {
			input_elements.item(i).checked = true;
		}
	}
}
//-->
</script>';

// tracking
event_access_tool(TOOL_GROUP);

// breadcrumbs
$interbreadcrumb[]=array('url' => "group.php","name" => get_lang('Groups'));

// display the header
Display::display_header(get_lang('GroupCategories'));

// display the tool title
//api_display_tool_title(get_lang('Groups'));

// action handling
groupcategory_actions();

// primary actions
display_actions();

//start the content div
echo '<div id="content">';

group_categories();

// close the content div
echo '</div>';

// secondary action links
display_secondary_actions();

// Display the footer
Display::display_footer();

function groupcategory_actions(){
	// delete category
	if ($_GET['action'] == 'delete' AND is_numeric($_GET['groupcategory_id'])){
		GroupManager::delete_category($_GET['groupcategory_id']);

		// move all the groups of this category outside this category
		$table_group = Database :: get_course_table(TABLE_GROUP);
		$sql = "UPDATE $table_group SET category_id=0 WHERE category_id='".Database::escape_string(Security::Remove_XSS($_GET['groupcategory_id']))."'";
		$result = Database::query($sql,__FILE__,__LINE__);			
	}
}

function group_categories(){
	if (!isset($_GET['action']) OR ( $_GET['action'] == 'delete' AND is_numeric($_GET['groupcategory_id']))){
		display_group_categories();
	} elseif ($_GET['action'] == 'edit' AND is_numeric($_GET['groupcategory_id'])){
		display_group_category_form();
	}
}

function display_group_category_form(){
	// Build form
	$form = new FormValidator('group_category','post','?'.api_get_cidreq().'&amp;action=edit&groupcategory_id='.Security::Remove_XSS($_GET['groupcategory_id']));
	$form->addElement('header', '', $nameTools);	

	// Group name
	$form->addElement('text', 'title', get_lang('GroupCategoryName'));

	// Description
	if (api_get_setting('groupscenariofield','description') == 'true'){
		$form->addElement('textarea', 'description', get_lang('GroupDescription'), array ('cols' => 50, 'rows' => 6));
	}

	// Members per group
	if (api_get_setting('groupscenariofield','limit') == 'true'){
		$form->addElement('radio', 'max_member_no_limit', get_lang('GroupLimit'), get_lang('NoLimit'), MEMBER_PER_GROUP_NO_LIMIT);
		$group = array ();
		$group[] = & $form->createElement('radio', 'max_member_no_limit', null, get_lang('Max'), 1);
		$group[] = & $form->createElement('text', 'max_member', null, array ('size' => 2, 'onkeydown' => 'javascript: max_member_switch_radio_button();'));
		$group[] = & $form->createElement('static', null, null, get_lang('GroupPlacesThis'));
		$form->addGroup($group, 'max_member_group', null, '', false);
		$form->addRule('max_member_group', get_lang('InvalidMaxNumberOfMembers'), 'callback', 'check_max_number_of_members');
	}

	// Groups per user
	$form->addElement('text', 'groups_per_user', get_lang('GroupsPerUser'), array ('size' => 2));

	// Self registration
	if (api_get_setting('groupscenariofield','registration') == 'true'){
		$form->addElement('radio', 'self_registration_allowed', get_lang('GroupSelfRegistration'), get_lang('Allowed'), 1);
		$form->addElement('radio', 'self_registration_allowed', null, get_lang('NotAllowed'), 0);
	}

	// unregistration
	if (api_get_setting('groupscenariofield','unregistration') == 'true'){
		$form->addElement('radio', 'self_unregistration_allowed', get_lang('GroupAllowStudentUnregistration'), get_lang('Allowed'), 1);
		$form->addElement('radio', 'self_unregistration_allowed', null, get_lang('NotAllowed'), 0);
	}

	// Public or private (meaning ALL tools)
	if (api_get_setting('groupscenariofield','publicprivategroup') == 'true'){
		$form->addElement('radio', 'group_state', get_lang('PublicPrivateGroup'), get_lang('NoGroupToolsAvailable'), TOOL_NOT_AVAILABLE);
		$form->addElement('radio', 'group_state', null, get_lang('Public'), TOOL_PUBLIC);
		$form->addElement('radio', 'group_state', null, get_lang('Private'), TOOL_PRIVATE);
	}

	// Documents settings
	if (api_get_setting('groupscenariofield','document') == 'true' AND api_get_setting('groupscenariofield','publicprivategroup') <> 'true'){
		$form->addElement('radio', 'doc_state', get_lang('GroupDocument'), get_lang('NotAvailable'), TOOL_NOT_AVAILABLE);
		$form->addElement('radio', 'doc_state', null, get_lang('Public'), TOOL_PUBLIC);
		$form->addElement('radio', 'doc_state', null, get_lang('Private'), TOOL_PRIVATE);
	}

	// Work settings
	if (api_get_setting('groupscenariofield','work') == 'true' AND api_get_setting('groupscenariofield','publicprivategroup') <> 'true'){
		$form->addElement('radio', 'work_state', get_lang('GroupWork'), get_lang('NotAvailable'), TOOL_NOT_AVAILABLE);
		$form->addElement('radio', 'work_state', null, get_lang('Public'), TOOL_PUBLIC);
		$form->addElement('radio', 'work_state', null, get_lang('Private'), TOOL_PRIVATE);
	}

	// Calendar settings
	if (api_get_setting('groupscenariofield','calendar') == 'true' AND api_get_setting('groupscenariofield','publicprivategroup') <> 'true'){
		$form->addElement('radio', 'calendar_state', get_lang('GroupCalendar'), get_lang('NotAvailable'), TOOL_NOT_AVAILABLE);
		$form->addElement('radio', 'calendar_state', null, get_lang('Public'), TOOL_PUBLIC);
		$form->addElement('radio', 'calendar_state', null, get_lang('Private'), TOOL_PRIVATE);
	}

	// Announcements settings
	if (api_get_setting('groupscenariofield','announcements') == 'true' AND api_get_setting('groupscenariofield','publicprivategroup') <> 'true'){
		$form->addElement('radio', 'announcements_state', get_lang('GroupAnnouncements'), get_lang('NotAvailable'), TOOL_NOT_AVAILABLE);
		$form->addElement('radio', 'announcements_state', null, get_lang('Public'), TOOL_PUBLIC);
		$form->addElement('radio', 'announcements_state', null, get_lang('Private'), TOOL_PRIVATE);
	}

	//Forum settings
	if (api_get_setting('groupscenariofield','forum') == 'true' AND api_get_setting('groupscenariofield','publicprivategroup') <> 'true'){
		$form->addElement('radio', 'forum_state', get_lang('GroupForum'), get_lang('NotAvailable'), TOOL_NOT_AVAILABLE);
		$form->addElement('radio', 'forum_state', null, get_lang('Public'), TOOL_PUBLIC);
		$form->addElement('radio', 'forum_state', null, get_lang('Private'), TOOL_PRIVATE);
	}

	// Wiki settings
	if (api_get_setting('groupscenariofield','wiki') == 'true' AND api_get_setting('groupscenariofield','publicprivategroup') <> 'true'){
		$form->addElement('radio', 'wiki_state', get_lang('GroupWiki'), get_lang('NotAvailable'), TOOL_NOT_AVAILABLE);
		$form->addElement('radio', 'wiki_state', null, get_lang('Public'), TOOL_PUBLIC);
		$form->addElement('radio', 'wiki_state', null, get_lang('Private'), TOOL_PRIVATE);
	}

	// Apply to all groups of this category
	$form->addElement('checkbox', 'apply_to_groups', get_lang('ApplySettingsToGroups'));

	// submit button
	$form->addElement('style_submit_button', 'submit', get_lang('PropModify'), 'class="save"');

	// setting the default values
	$defaults = GroupManager::get_category($_GET['groupcategory_id']);
	if ($defaults['max_student'] == 0){
		$defaults['max_member_no_limit'] = MEMBER_PER_GROUP_NO_LIMIT;
	} else {
		$defaults['max_member_no_limit'] = 1;
		$defaults['max_member'] = $defaults['max_student'];
	}
	$form->setDefaults($defaults);

	if ($form->validate()){
		// exporting the values of the form
		$values = $form->exportValues();

		// handling the checkboxes
		$self_reg_allowed = isset ($values['self_reg_allowed']) ? $values['self_reg_allowed'] : 0;
		$self_unreg_allowed = isset ($values['self_unreg_allowed']) ? $values['self_unreg_allowed'] : 0;
		
		// handling the max members per group
		if ($values['max_member_no_limit'] == MEMBER_PER_GROUP_NO_LIMIT) {
			$max_member = MEMBER_PER_GROUP_NO_LIMIT;
		} else {
			$max_member = $values['max_member'];
		}

		// public / private group or individual tools
		if (api_get_setting('groupscenariofield','publicprivategroup') == 'true'){
			$values['doc_state'] 		= $values['group_state'];
			$values['work_state']	 	= $values['group_state'];
			$values['calendar_state'] 	= $values['group_state'];
			$values['announcements_state'] 	= $values['group_state'];
			$values['forum_state'] 		= $values['group_state'];
			$values['wiki_state'] 		= $values['group_state'];
			$values['chat_state'] 		= $values['group_state'];
		}

		// handling the max groups per user
		if (empty($values['groups_per_user']) OR !is_numeric($values['groups_per_user'])){
			$values['groups_per_user'] = 0;
		}

		// saving the changes to the group category
		GroupManager :: update_category($_GET['groupcategory_id'], $values['title'], $values['description'], $values['doc_state'], $values['work_state'], $values['calendar_state'], $values['announcements_state'], $values['forum_state'], $values['wiki_state'], $values['chat_state'], $self_reg_allowed, $self_unreg_allowed, $max_member, $values['groups_per_user']);
		
		// saving the changes to the groups of this category
		if ($values['apply_to_groups'] == 1){

			// updating the properties of the group
			$table_group = Database :: get_course_table(TABLE_GROUP);
			$sql = "UPDATE $table_group SET 
					doc_state = '".Database::escape_string($values['doc_state'])."', 
					work_state = '".Database::escape_string($values['work_state'])."', 
					calendar_state = '".Database::escape_string($values['calendar_state'])."', 
					announcement_state = '".Database::escape_string($values['announcement_state'])."', 
					forum_state = '".Database::escape_string($values['forum_state'])."', 
					wiki_state = '".Database::escape_string($values['wiki_state'])."', 
					chat_state = '".Database::escape_string($values['chat_state'])."', 
					self_registrtation_allowed = '".Database::escape_string($self_reg_allowed)."', 
					self_unregistration_allowed = '".Database::escape_string($self_unreg_allowed)."', 
					max_student = '".Database::escape_string($max_member)."', 
					"; 
			$result = Database::query($sql,__FILE__,__LINE__);			
		}

		// displaying the feedback message
		Display::display_confirmation_message(get_lang('GroupCategoryModified'));
		
		// display all the categories after the changes have been saved
		display_group_categories();
	} else {
		// display the form
		$form->display();
	}
}

function display_group_categories(){
	$groupcategories = GroupManager::get_categories();

	foreach ($groupcategories as $key=>$groupcategory){
		echo '<div id="scenario'.$groupcategory['id'].'" class="section groupcategory">';
		echo '	<div class="sectiontitle">';
		if (!empty($groupcategory['icon'])) {
			echo 	Display::return_icon($groupcategory['icon'],$groupcategory['title']).' '.$groupcategory['title'];
		}  else {
			echo 	Display::return_icon('dokeos_scenario.png',$groupcategory['title']).' '.$groupcategory['title'];
		}
		echo '	</div>';
		echo '	<div class="sectioncontent">';
		echo '	<div style="float: right;">';
		echo '		<a href="group_category.php?action=edit&amp;groupcategory_id='.$groupcategory['id'].'">'.Display::return_icon('edit.png').'</a>';
		echo '		<a href="group_category.php?action=delete&amp;groupcategory_id='.$groupcategory['id'].'" onclick="javascript:if(!confirm('."'".addslashes(api_htmlentities(get_lang('ConfirmYourChoice'), ENT_QUOTES, $charset))."'".')) return false;" title="'.get_lang('Delete').'"><img src="../img/delete.gif" alt="'.get_lang('Delete').'"/></a>';
		echo '	</div>';
		// group description
		echo $groupcategory['description'].'<br />';

		// group limit of seats
		if (api_get_setting('groupscenariofield','limit') == 'true'){
			echo get_lang('GroupLimit').': ';
			if ($groupcategory['max_student'] == MEMBER_PER_GROUP_NO_LIMIT){
				echo get_lang('NoLimit'); 
			} else {
				echo $groupcategory['max_student'].' '.get_lang('Seats');
			}
			echo '<br />';
		}
		// group self registration
		if (api_get_setting('groupscenariofield','registration') == 'true'){
			echo get_lang('GroupSelfRegistration').': ';
			if ($groupcategory['self_reg_allowed'] == 1){
				echo get_lang('GroupAllowStudentRegistration');
			} else {
				echo get_lang('GroupStudentRegistrationDenied');
			}
			echo '<br />';
		}
		// group self UNregistration
		if (api_get_setting('groupscenariofield','unregistration') == 'true'){
			echo get_lang('GroupSelfUnregistration').': ';
			if ($groupcategory['self_reg_allowed'] == 1){
				echo get_lang('GroupAllowStudentUnregistration');
			} else {
				echo get_lang('GroupStudentUnregistrationDenied');
			}		
			echo '<br />';
		}
		// public or private group
		if (api_get_setting('groupscenariofield','publicprivategroup') == 'true'){
			echo get_lang('PublicPrivateGroup').': ';
			switch ($groupcategory['group_state']){
				case TOOL_PUBLIC:
					echo get_lang('Public');
					break;
				case TOOL_PRIVATE:
					echo get_lang('Private');
					break;

			}
			echo '<br />';
		}
		// Documents settings
		if (api_get_setting('groupscenariofield','document') == 'true' AND api_get_setting('groupscenariofield','publicprivategroup') <> 'true'){
			echo get_lang('GroupDocument').': ';
			switch ($groupcategory['doc_state']){
				case TOOL_NOT_AVAILABLE:
					echo get_lang('NotAvailable');
					break;
				case TOOL_PUBLIC:
					echo get_lang('Public');
					break;
				case TOOL_PRIVATE:
					echo get_lang('Private');
					break;

			}
			echo '<br />';
		}
		// Work settings
		if (api_get_setting('groupscenariofield','work') == 'true' AND api_get_setting('groupscenariofield','publicprivategroup') <> 'true'){
			echo get_lang('GroupWork').': ';
			switch ($groupcategory['work_state']){
				case TOOL_NOT_AVAILABLE:
					echo get_lang('NotAvailable');
					break;
				case TOOL_PUBLIC:
					echo get_lang('Public');
					break;
				case TOOL_PRIVATE:
					echo get_lang('Private');
					break;
			}
			echo '<br />';
		}
		// Calendar settings
		if (api_get_setting('groupscenariofield','calendar') == 'true' AND api_get_setting('groupscenariofield','publicprivategroup') <> 'true'){
			echo get_lang('GroupCalendar').': ';
			switch ($groupcategory['calendar_state']){
				case TOOL_NOT_AVAILABLE:
					echo get_lang('NotAvailable');
					break;
				case TOOL_PUBLIC:
					echo get_lang('Public');
					break;
				case TOOL_PRIVATE:
					echo get_lang('Private');
					break;
			}
			echo '<br />';
		}
		// Announcements settings
		if (api_get_setting('groupscenariofield','announcements') == 'true' AND api_get_setting('groupscenariofield','publicprivategroup') <> 'true'){
			echo get_lang('GroupAnnouncements').': ';
			switch ($groupcategory['announcements_state']){
				case TOOL_NOT_AVAILABLE:
					echo get_lang('NotAvailable');
					break;
				case TOOL_PUBLIC:
					echo get_lang('Public');
					break;
				case TOOL_PRIVATE:
					echo get_lang('Private');
					break;
			}
			echo '<br />';
		}
		//Forum settings
		if (api_get_setting('groupscenariofield','forum') == 'true' AND api_get_setting('groupscenariofield','publicprivategroup') <> 'true'){
			echo get_lang('GroupForum').': ';
			switch ($groupcategory['forum_state']){
				case TOOL_NOT_AVAILABLE:
					echo get_lang('NotAvailable');
					break;
				case TOOL_PUBLIC:
					echo get_lang('Public');
					break;
				case TOOL_PRIVATE:
					echo get_lang('Private');
					break;
			}
			echo '<br />';
		}
		// Wiki settings
		if (api_get_setting('groupscenariofield','wiki') == 'true' AND api_get_setting('groupscenariofield','publicprivategroup') <> 'true'){
			echo get_lang('GroupWiki').': ';
			switch ($groupcategory['wiki_state']){
				case TOOL_NOT_AVAILABLE:
					echo get_lang('NotAvailable');
					break;
				case TOOL_PUBLIC:
					echo get_lang('Public');
					break;
				case TOOL_PRIVATE:
					echo get_lang('Private');
					break;
			}
		}
		echo '	</div>';
		echo '</div>';
	}

}



function display_actions(){
	echo '<div class="actions">';
	echo '<a href="group.php?'.api_get_cidreq().'">'.Display::return_icon('group.png', get_lang('Groups')).get_lang('Groups').'</a>';
	if (api_is_allowed_to_edit(false,true))
	{
		echo '<a href="group_creation.php?'.api_get_cidreq().'">'.Display::return_icon('groupadd.png', get_lang('NewGroupCreate'), array('style'=>'height: 32px;')).' '.get_lang('NewGroupCreate').'</a>';
	}
	echo '</div>';
}


function display_secondary_actions(){
//	echo '<div class="actions">';
//	echo '</div>';
}


/**
 * Function to check the given max number of members per group
 */
function check_max_number_of_members($value)
{
	$max_member_no_limit = $value['max_member_no_limit'];
	if( $max_member_no_limit == MEMBER_PER_GROUP_NO_LIMIT)
	{
		return true;
	}
	$max_member = $value['max_member'];
	return is_numeric($max_member);
}
/**
 * Function to check the number of groups per user
 */
function check_groups_per_user($value)
{
	$groups_per_user = $value['groups_per_user'];
	if(isset ($_POST['id']) && intval($groups_per_user) != GROUP_PER_MEMBER_NO_LIMIT && GroupManager :: get_current_max_groups_per_user($_POST['id']) > intval($groups_per_user))
	{
		return false;
	}
	return true;
}
?>
