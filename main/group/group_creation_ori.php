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

// including additional libraries
require_once (api_get_path(LIBRARY_PATH).'groupmanager.lib.php');
require_once (api_get_path(LIBRARY_PATH).'classmanager.lib.php');
require_once (api_get_path(LIBRARY_PATH).'course.lib.php');
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');

// the section (for the tabs)
$this_section = SECTION_COURSES;

// temporary style definition
$htmlHeadXtra[] = '<style type="text/css">
	.columns2 {width: 45%; min-height: 150px;}
	.gradient {border: 1px solid grey;}
	#groupcalculator {float: left;clear:none;}
	#group_to_create {float: left;clear:none;}
	.sectioncontent{ padding: 5px;}
	.selectscenario{float: right;}
	</style>';
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-1.4.2.min.js" language="javascript"></script>';
$htmlHeadXtra[] = '<script type="text/javascript">
			$(document).ready(function() {
				// number of groups that need to be created
				var number_of_groups = $("#number_of_groups").val();
				
				// number of users per group
				var number_of_users_per_group = $("#number_of_users_per_group").val();
	
				$.ajax({
					  url: "ajax.php",
					  data: {action: "group_name_form_elements", number_of_groups: number_of_groups, number_of_users_per_group: number_of_users_per_group, cidReq:"'.Security::remove_XSS($_GET['cidReq']).'"},
					  success: function(data){
						$("#group_to_create .sectioncontent").html(data);
						}
				});

				$(".groupcategory").hover(
					function (){
						// display the submit button
						$(".selectscenario",this).toggle();
						// change the style of the group category (scenario)
						$(this).toggleClass("groupcategoryselected");
						// store the id of the group category (scenario) in the form						
						var cat_id = $(this).attr("id").replace("scenario","");
						$("#selected_group_category").val(cat_id);
					},
					function (){
						// hide the submit button
						$(".selectscenario",this).toggle();
						// change the style of the group category (scenario)
						$(this).toggleClass("groupcategoryselected");
						// store the id of the group category (scenario) in the form						
						$("#selected_group_category").val("");
						}				
				);


			});
		</script>';

// access control
api_protect_course_script(true);
if (!api_is_allowed_to_edit(false,true))
{
	api_not_allowed();
}

// tracking
event_access_tool('group_creation');

// name of the tool
$nameTools = get_lang('GroupManagement');

// header actions
group_header_actions();

// display the header
Display::display_header(get_lang('Groups'));

// action handling
group_actions();

// action links
display_actions();

//start the content div
echo '<div id="content">';

// display the groups
display_group_creation();

// close the content div
echo '</div>';

// bottom action toolbar
echo '<div class="actions">';
echo '</div>';


// display the footer
Display :: display_footer();
exit;

function group_header_actions(){
	global $form;

	switch ($_GET['action']){
		case 'save_groups2':
			// unserializing the group names and the number of users per group
			$group_names = unserialize(str_replace('*','"',$_POST['groupnames']));
			$userspergroup = unserialize(str_replace('*','"',$_POST['userspergroup']));

			// creating the groups
			$number_of_created_groups = create_groups($group_names,$userspergroup,$_POST['selected_group_category']);
			header('Location: group.php?'.api_get_cidreq().'&amp;action=display_message&msg=GroupsCreated&number_of_groups='.$number_of_created_groups);
			break;
		case 'save_groups':
			// building the form to create a new group category			
			build_group_category_form();

			if ($form->validate()){
				// exporting the values of the form
				$values = $form->exportValues();

				// unserializing the group names and the number of users per group
				$group_names = unserialize(str_replace('*','"',$values['groupnames']));
				$userspergroup = unserialize(str_replace('*','"',$values['userspergroup']));
                                $values['doc_state'] = empty($values['doc_state']) ? $values['group_state'] : $values['doc_state'];
                                $values['work_state'] = empty($values['work_state']) ? $values['group_state'] : $values['work_state'];
                                $values['forum_state'] = empty($values['forum_state']) ? $values['group_state'] : $values['forum_state'];
                                $values['calendar_state'] = empty($values['calendar_state']) ? $values['group_state'] : $values['calendar_state'];
                                $values['announcements_state'] = empty($values['announcements_state']) ? $values['group_state'] : $values['announcements_state'];
                                $values['chat_state'] = empty($values['chat_state']) ? $values['group_state'] : $values['chat_state'];
                                $values['wiki_state'] = empty($values['wiki_state']) ? $values['group_state'] : $values['wiki_state'];
				// creating the group category
				$group_category = GroupManager :: create_category($values['title'], $values['description'], $values['doc_state'], $values['work_state'], $values['calendar_state'], $values['announcements_state'], $values['forum_state'], $values['wiki_state'], $values['chat_state'],  $self_reg_allowed, $self_unreg_allowed, $max_member, $values['groups_per_user'], $values['group_state']);

				// creating the groups in this newly created category
				$number_of_created_groups = create_groups($group_names,$userspergroup,$group_category);
				header('Location: group.php?'.api_get_cidreq().'&amp;action=display_message&msg=GroupsCreated&number_of_groups='.$number_of_created_groups);
			}
			break;
	}
}

function group_actions(){

}

function display_group_creation(){
	global $form;

	if (!$_GET['action']){
		echo '<form action="group_creation.php?cidReq='.Security::remove_XSS($_GET['cidReq']).'" id="group_calculator" name="group_calculator" method="post" >';
		echo '<div id="groupcalculator" class="section columns2">';
		echo '	<div class="sectiontitle">';
		echo 	Display::return_icon('calculator.png',get_lang('GroupCalculator'), array('height'=>'32px;')).' '.get_lang('GroupCalculator');
		echo '	</div>';
		echo '	<div class="sectioncontent">';
		echo 	group_calculator();
		echo '	</div>';
		echo '</div>';
		echo '</form>';

		echo '<div id="group_to_create" class="section columns2">';
		echo '	<div class="sectiontitle">';
		echo 	Display::return_icon('members.png',get_lang('GroupUsers'), array('height'=>'32px;')).' '.get_lang('GroupUsers');
		echo '	</div>';
		echo '	<div class="sectioncontent">';
		echo '	</div>';
		echo '</div>';
	} elseif ($_GET['action'] == 'save_groups'){
		echo '<form action="group_creation.php?cidReq='.Security::remove_XSS($_GET['cidReq']).'&amp;action=save_groups2" id="group_creation" name="group_creation2" method="post">';

		// hidden fields that store the group names and the number of users
		echo '<input type="hidden" name="groupnames" value="'.str_replace('"','*',serialize($_POST['group_name'])).'">';
		echo '<input type="hidden" name="userspergroup" value="'.str_replace('"','*',serialize($_POST['users_of_group'])).'">';

		// hidden field that stores the selected group category (scenario)
		echo '<input type="hidden" name="selected_group_category" id="selected_group_category"/>';

		$groupcategories = GroupManager::get_categories();

		foreach ($groupcategories as $key=>$groupcategory){
			echo '<div id="scenario'.$groupcategory['id'].'" class="section groupcategory">';

			echo '	<div class="sectiontitle">';
			if (!empty($groupcategory['icon'])) {
				echo 	Display::return_icon($groupcategory['icon'],$groupcategory['title'], array('style'=>'height: 32px;')).' '.$groupcategory['title'];
			}  else {
				echo 	Display::return_icon('dokeos_scenario.png',$groupcategory['title'], array('style'=>'height: 32px;')).' '.$groupcategory['title'];
			}
			echo '	</div>';

			echo '	<div class="sectioncontent">';
			// group description
			echo $groupcategory['description'];

			// group limit of seats
			if (api_get_setting('groupscenariofield','limit') == 'true'){
				echo '<div class="row">';
				echo '	<div class="label">'.get_lang('GroupLimit').':</div>';
				echo '	<div class="formw">';
				if ($groupcategory['max_student'] == MEMBER_PER_GROUP_NO_LIMIT){
					echo get_lang('NoLimit'); 
				} else {
					echo $groupcategory['max_student'].' '.get_lang('Seats');
				}
				echo '	</div>';
				echo '</div>';
			}
			// group self registration
			if (api_get_setting('groupscenariofield','registration') == 'true'){
				echo '<div class="row">';
				echo '	<div class="label">'.get_lang('GroupSelfRegistration').':</div>';
				echo '	<div class="formw">';
				if ($groupcategory['self_reg_allowed'] == 1){
					echo get_lang('GroupAllowStudentRegistration');
				} else {
					echo get_lang('GroupStudentRegistrationDenied');
				}
				echo '	</div>';
				echo '</div>';
			}
			// group self UNregistration
			if (api_get_setting('groupscenariofield','unregistration') == 'true'){
				echo '<div class="row">';
				echo '	<div class="label">'.get_lang('GroupSelfUnregistration').':</div>';
				echo '	<div class="formw">';
				if ($groupcategory['self_reg_allowed'] == 1){
					echo get_lang('GroupAllowStudentUnregistration');
				} else {
					echo get_lang('GroupStudentUnregistrationDenied');
				}		
				echo '	</div>';
				echo '</div>';
			}
			// public or private group
			if (api_get_setting('groupscenariofield','publicprivategroup') == 'true'){
				echo '<div class="row">';
				echo '	<div class="label">'.get_lang('PublicPrivateGroup').':</div>';
				echo '	<div class="formw">&nbsp;';
				switch ($groupcategory['group_state']){
					case TOOL_PUBLIC:
						echo get_lang('Public');
						break;
					case TOOL_PRIVATE:
						echo get_lang('Private');
						break;

				}
				echo '	</div>';
				echo '</div>';
			}
			// Documents settings
			if (api_get_setting('groupscenariofield','document') == 'true' AND api_get_setting('groupscenariofield','publicprivategroup') <> 'true'){
				echo '<div class="row">';
				echo '	<div class="label">'.get_lang('GroupDocument').':</div>';
				echo '	<div class="formw">';
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
				echo '	</div>';
				echo '</div>';
			}
			// Work settings
			if (api_get_setting('groupscenariofield','work') == 'true' AND api_get_setting('groupscenariofield','publicprivategroup') <> 'true'){
				echo '<div class="row">';
				echo '	<div class="label">'.get_lang('GroupWork').':</div>';
				echo '	<div class="formw">';
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
				echo '	</div>';
				echo '</div>';
			}
			// Calendar settings
			if (api_get_setting('groupscenariofield','calendar') == 'true' AND api_get_setting('groupscenariofield','publicprivategroup') <> 'true'){
				echo '<div class="row">';
				echo '	<div class="label">'.get_lang('GroupCalendar').':</div>';
				echo '	<div class="formw">';
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
				echo '	</div>';
				echo '</div>';
			}
			// Announcements settings
			if (api_get_setting('groupscenariofield','announcements') == 'true' AND api_get_setting('groupscenariofield','publicprivategroup') <> 'true'){
				echo '<div class="row">';
				echo '	<div class="label">'.get_lang('GroupAnnouncements').':</div>';
				echo '	<div class="formw">';
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
				echo '	</div>';
				echo '</div>';
			}
			//Forum settings
			if (api_get_setting('groupscenariofield','forum') == 'true' AND api_get_setting('groupscenariofield','publicprivategroup') <> 'true'){
				echo '<div class="row">';
				echo '	<div class="label">'.get_lang('GroupForum').':</div>';
				echo '	<div class="formw">';
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
				echo '	</div>';
				echo '</div>';
			}
			// Wiki settings
			if (api_get_setting('groupscenariofield','wiki') == 'true' AND api_get_setting('groupscenariofield','publicprivategroup') <> 'true'){
				echo '<div class="row">';
				echo '	<div class="label">'.get_lang('GroupWiki').':</div>';
				echo '	<div class="formw">';

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
				echo '	</div>';
				echo '</div>';
			}
			echo '<button class="save selectscenario" style="display:none;" name="action" type="submit">'.get_lang('SelectThisScenario').'</button>';

			echo '	</div>'; // close sectioncontent
			echo '</div>'; // close section
		}
		echo '</form>';		

		// custom group category
		echo '<div id="scenario_custom" class="section">';
		echo '	<div class="sectiontitle">';
		echo 	get_lang('CreateNewScenario');
		echo '	</div>';
		echo '	<div class="sectioncontent">';
		echo 		display_group_category_form();
		echo '	</div>';
		echo '</div>';
	}
}

function display_group_category_form(){
	global $form;
	$form->display();
}

function build_group_category_form(){
	global $form;

	// Build form
	$form = new FormValidator('group_category','post','?'.api_get_cidreq().'&amp;action=save_groups');
	$form->addElement('header', '', $nameTools);

	// group names and number of users per group
	$form->addElement('hidden', 'groupnames');
	$form->addElement('hidden', 'userspergroup');
	$defaults['groupnames']=str_replace('"','*',serialize($_POST['group_name']));	
	$defaults['userspergroup']=str_replace('"','*',serialize($_POST['users_of_group']));	

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
		$group[] = & $form->createElement('text', 'max_member', null, array ('size' => 2));
		$group[] = & $form->createElement('static', null, null, get_lang('GroupPlacesThis'));
		$form->addGroup($group, 'max_member_group', null, '', false);
		$form->addRule('max_member_group', get_lang('InvalidMaxNumberOfMembers'), 'callback', 'check_max_number_of_members');
		$defaults['mex_member_group']['max_member_no_limit'] = MEMBER_PER_GROUP_NO_LIMIT;
	}

	// Self registration
	if (api_get_setting('groupscenariofield','registration') == 'true'){
		$form->addElement('radio', 'self_registration_allowed', get_lang('GroupSelfRegistration'), get_lang('Yes'), 1);
		$form->addElement('radio', 'self_registration_allowed', null, get_lang('No'), 0);
	}

	// unregistration
	if (api_get_setting('groupscenariofield','unregistration') == 'true'){
		$form->addElement('radio', 'self_unregistration_allowed', get_lang('GroupAllowStudentUnregistration'), get_lang('Yes'), 1);
		$form->addElement('radio', 'self_unregistration_allowed', null, get_lang('No'), 0);
	}

	// Public or private (meaning ALL tools)
	if (api_get_setting('groupscenariofield','publicprivategroup') == 'true'){
		$form->addElement('radio', 'group_state', get_lang('PublicPrivateGroup'), get_lang('NoGroupToolsAvailable'), TOOL_NOT_AVAILABLE);
		$form->addElement('radio', 'group_state', null, get_lang('Public'), TOOL_PUBLIC);
		$form->addElement('radio', 'group_state', null, get_lang('Private'), TOOL_PRIVATE);
	}

	// Documents settings
	if (api_get_setting('groupscenariofield','document') == 'true' AND api_get_setting('groupscenariofield','publicprivategroup') <> 'true'){
		$form->addElement('radio', 'doc_state', get_lang('PublicPrivateGroup'), get_lang('Public'), TOOL_PUBLIC);
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

	// submit button
	$form->addElement('style_submit_button', 'submit', get_lang('PropModify'), 'class="save"');

	// setting the default values
	$form->setDefaults($defaults);
}

function is_selected($value1,$value2){
	if ($value1 == $value2){
		return ' selected="selected"';
	}
}

function group_calculator(){
	// default value
	if (empty($_POST['number_of_groups'])){
		$number_of_groups=1;
	} else {
		$number_of_groups = $_POST['number_of_groups'];
	}
	if (empty($_POST['number_of_users_per_group'])){
		$number_of_users_per_group=5;
	} else {
		$number_of_users_per_group = $_POST['number_of_users_per_group'];
	}


	echo get_lang('Create').' ';
	echo '<select name="number_of_groups" id="number_of_groups" onchange="document.group_calculator.submit();return false;">';
	echo '	<option value="1"'.is_selected(1,$number_of_groups).'>1</option>';
	echo '	<option value="2"'.is_selected(2,$number_of_groups).'>2</option>';
	echo '	<option value="3"'.is_selected(3,$number_of_groups).'>3</option>';
	echo '	<option value="4"'.is_selected(4,$number_of_groups).'>4</option>';
	echo '	<option value="5"'.is_selected(5,$number_of_groups).'>5</option>';
	echo '	<option value="6"'.is_selected(6,$number_of_groups).'>6</option>';
	echo '	<option value="7"'.is_selected(7,$number_of_groups).'>7</option>';
	echo '	<option value="8"'.is_selected(8,$number_of_groups).'>8</option>';
	echo '	<option value="9"'.is_selected(9,$number_of_groups).'>9</option>';
	echo '	<option value="10"'.is_selected(10,$number_of_groups).'>10</option>';
	echo '	<option value="11"'.is_selected(11,$number_of_groups).'>11</option>';
	echo '	<option value="12"'.is_selected(12,$number_of_groups).'>12</option>';
	echo '	<option value="13"'.is_selected(13,$number_of_groups).'>13</option>';
	echo '	<option value="14"'.is_selected(14,$number_of_groups).'>14</option>';
	echo '	<option value="15"'.is_selected(15,$number_of_groups).'>15</option>';
	echo '	<option value="16"'.is_selected(16,$number_of_groups).'>16</option>';
	echo '	<option value="17"'.is_selected(17,$number_of_groups).'>17</option>';
	echo '	<option value="18"'.is_selected(18,$number_of_groups).'>18</option>';
	echo '	<option value="19"'.is_selected(19,$number_of_groups).'>19</option>';
	echo '	<option value="20"'.is_selected(20,$number_of_groups).'>20</option>';
	echo '</select>';
	echo ' '.get_lang('GroupsOf').' ';
	echo '<select name="number_of_users_per_group" id="number_of_users_per_group" onchange="document.group_calculator.submit();return false;">';
	echo '	<option value="1"'.is_selected(1,$number_of_users_per_group).'>1</option>';
	echo '	<option value="2"'.is_selected(2,$number_of_users_per_group).'>2</option>';
	echo '	<option value="3"'.is_selected(3,$number_of_users_per_group).'>3</option>';
	echo '	<option value="4"'.is_selected(4,$number_of_users_per_group).'>4</option>';
	echo '	<option value="5"'.is_selected(5,$number_of_users_per_group).'>5</option>';
	echo '	<option value="6"'.is_selected(6,$number_of_users_per_group).'>6</option>';
	echo '	<option value="7"'.is_selected(7,$number_of_users_per_group).'>7</option>';
	echo '	<option value="8"'.is_selected(8,$number_of_users_per_group).'>8</option>';
	echo '	<option value="9"'.is_selected(9,$number_of_users_per_group).'>9</option>';
	echo '	<option value="10"'.is_selected(10,$number_of_users_per_group).'>10</option>';
	echo '	<option value="11"'.is_selected(11,$number_of_users_per_group).'>11</option>';
	echo '	<option value="12"'.is_selected(12,$number_of_users_per_group).'>12</option>';
	echo '	<option value="13"'.is_selected(13,$number_of_users_per_group).'>13</option>';
	echo '	<option value="14"'.is_selected(14,$number_of_users_per_group).'>14</option>';
	echo '	<option value="15"'.is_selected(15,$number_of_users_per_group).'>15</option>';
	echo '	<option value="16"'.is_selected(16,$number_of_users_per_group).'>16</option>';
	echo '	<option value="17"'.is_selected(17,$number_of_users_per_group).'>17</option>';
	echo '	<option value="18"'.is_selected(18,$number_of_users_per_group).'>18</option>';
	echo '	<option value="19"'.is_selected(19,$number_of_users_per_group).'>19</option>';
	echo '	<option value="20"'.is_selected(20,$number_of_users_per_group).'>20</option>';
	echo '</select>';
	echo ' '.get_lang('Persons');
}

function display_actions(){

	if (api_is_allowed_to_edit(false,true))
	{
		echo '<div class="actions">';
		echo '<a href="group.php?'.api_get_cidreq().'">'.Display::return_icon('group.png', get_lang('Groups')).get_lang('Groups').'</a>';
		if (api_get_setting('allow_group_categories') == 'true') {
			echo '<a href="group_category.php?'.api_get_cidreq().'&amp;action=add_category">'.Display::return_icon('folder_new.gif', get_lang('AddCategory')).' '.get_lang('AddCategory').'</a>&nbsp;';
		} else {
			echo '<a href="group_category.php?'.api_get_cidreq().'">'.Display::return_icon('dokeos_scenario.png', get_lang('Scenario')).' '.get_lang('Scenario').'</a>';
		}
		echo '</div>';
	}
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

function create_groups($group_names, $userspergroup, $group_category){
	$counter = 0;
	foreach ($group_names as $key=>$groupname){
		$created_groups[] = GroupManager::create_group($groupname,$group_category,'',$userspergroup[$key]);
		$counter++;
	}
	return $counter;
}

// CODE BELOW IS OLD CODE 

/*
--------------------------------------
        Create the groups
--------------------------------------
*/
if (isset ($_POST['action']))
{
	switch ($_POST['action'])
	{
		case 'create_groups' :
			$groups = array ();

			for ($i = 0; $i < $_POST['number_of_groups']; $i ++)
			{
				$group1['name'] = api_strlen($_POST['group_'.$i.'_name']) == 0 ? get_lang('Group').' '.$i : $_POST['group_'.$i.'_name'] ;
				$group1['category'] = isset($_POST['group_'.$i.'_category'])?$_POST['group_'.$i.'_category']:null;
				$group1['tutor'] = isset($_POST['group_'.$i.'_tutor'])?$_POST['group_'.$i.'_tutor']:null;
				$group1['places'] = isset($_POST['group_'.$i.'_places'])?$_POST['group_'.$i.'_places']:null;
				$groups[] = $group1;
			}

			foreach ($groups as $index => $group)
			{
				if (!empty($_POST['same_tutor']))
				{
					$group['tutor'] = $_POST['group_0_tutor'];
				}
				if (!empty($_POST['same_places']))
				{
					$group['places'] = $_POST['group_0_places'];
				}
				if (api_get_setting('allow_group_categories') == 'false')
				{
					$group['category'] = DEFAULT_GROUP_CATEGORY;
				}
				elseif ($_POST['same_category'])
				{
					$group['category'] = $_POST['group_0_category'];
				}
				GroupManager :: create_group(strip_tags($group['name']),$group['category'],$group['tutor'] , $group['places']);
			}
			$msg = urlencode(count($groups).' '.get_lang('GroupsAdded'));
			header('Location: group.php?action=show_msg&msg='.$msg);
			break;
		case 'create_subgroups' :
			GroupManager :: create_subgroups($_POST['base_group'], $_POST['number_of_groups']);
			$msg = urlencode($_POST['number_of_groups'].' '.get_lang('GroupsAdded'));
			header('Location: group.php?action=show_msg&msg='.$msg);
			break;
		case 'create_class_groups' :
			$ids = GroupManager :: create_class_groups($_POST['group_category']);
			$msg = urlencode(count($ids).' '.get_lang('GroupsAdded'));
			header('Location: group.php?action=show_msg&msg='.$msg);
			break;
	}
}
$nameTools = get_lang('GroupCreation');
$interbreadcrumb[] = array ("url" => "group.php", "name" => get_lang('Groups'));
Display :: display_header($nameTools, "Group");

if (!api_is_allowed_to_edit(false,true))
{
	api_not_allowed();
}
/*
===============================================================================
       MAIN TOOL CODE
===============================================================================
*/
/*
--------------------------------------
        Show group-settings-form
--------------------------------------
*/
elseif (isset ($_POST['number_of_groups']))
{
	if (!is_numeric($_POST['number_of_groups']) || intval($_POST['number_of_groups']) < 1)
	{
		Display :: display_error_message(get_lang('PleaseEnterValidNumber').'<br/><br/><a href="group_creation.php?'.api_get_cidreq().'">&laquo; '.get_lang('Back').'</a>',false);
	}
	else
	{
	$number_of_groups = intval($_POST['number_of_groups']);
	if ($number_of_groups > 1)
	{
		
?>
	<script type="text/javascript">
	var number_of_groups = <?php echo $number_of_groups; ?>;
	function switch_state(key)
	{
		for( i=1; i<number_of_groups; i++)
		{
			element = document.getElementById(key+'_'+i);
			element.disabled = !element.disabled;
			disabled = element.disabled;
		}
		ref = document.getElementById(key+'_0');
		if( disabled )
		{
			ref.addEventListener("change", copy, false);
		}
		else
		{
			ref.removeEventListener("change", copy, false);
		}
		copy_value(key);
	}
	function copy(e)
	{
		key = e.currentTarget.id;
		var re = new RegExp ('_0', '') ;
		var key = key.replace(re, '') ;
		copy_value(key);
	}
	function copy_value(key)
	{
		ref = document.getElementById(key+'_0');
		for( i=1; i<number_of_groups; i++)
		{
			element = document.getElementById(key+'_'+i);
			element.value = ref.value;
		}
	}
	</script>
	<?php


	}
	$group_categories = GroupManager :: get_categories();
	$group_id = GroupManager :: get_number_of_groups() + 1;
	/*$tutors = GroupManager :: get_all_tutors();
	$tutor_options[0] = get_lang('GroupNoTutor');
	foreach ($tutors as $index => $tutor)
	{
		$tutor_options[$tutor['user_id']] = api_get_person_name($tutor['firstname'], $tutor['lastname']);
	}
	$cat_options = array ();
	*/
	foreach ($group_categories as $index => $category)
	{
		$cat_options[$category['id']] = $category['title'];
	}
	$form = new FormValidator('create_groups_step2');

	// Modify the default templates
	$renderer = & $form->defaultRenderer();
	$form_template = "<form {attributes}>\n<div>\n<table>\n{content}\n</table>\n</div>\n</form>";
	$renderer->setFormTemplate($form_template);
	$element_template = <<<EOT
	<tr>
		<td>
			<!-- BEGIN required --><span class="form_required">*</span> <!-- END required -->{label}
		</td>
		<td>
			<!-- BEGIN error --><span class="form_error">{error}</span><br /><!-- END error -->	{element}
		</td>
	</tr>

EOT;
	$renderer->setElementTemplate($element_template);
	$form->addElement('header', '', $nameTools);

	$form->addElement('hidden', 'action');
	$form->addElement('hidden', 'number_of_groups');
	$defaults = array ();
	// Table heading
	$group_el = array ();
	$group_el[] = & $form->createElement('static', null, null, '<b>'.get_lang('GroupName').'</b>');
	if (api_get_setting('allow_group_categories') == 'true')
	{
		$group_el[] = & $form->createElement('static', null, null, '<b>'.get_lang('GroupCategory').'</b>');
	}
	//$group_el[] = & $form->createElement('static', null, null, '<b>'.get_lang('GroupTutor').'</b>');
	$group_el[] = & $form->createElement('static', null, null, '<b>'.get_lang('GroupPlacesThis').'</b>');
	$form->addGroup($group_el, 'groups', null, "\n</td>\n<td>\n", false);
	// Checkboxes
	if ($_POST['number_of_groups'] > 1)
	{
		$group_el = array ();
		$group_el[] = & $form->createElement('static', null, null, ' ');
		if (api_get_setting('allow_group_categories') == 'true')
		{
			$group_el[] = & $form->createElement('checkbox', 'same_category', null, get_lang('SameForAll'), array ('onclick' => "javascript:switch_state('category')"));
		}
		//$group_el[] = & $form->createElement('checkbox', 'same_tutor', null, get_lang('SameForAll'), array ('onclick' => "javascript:switch_state('tutor')"));
		$group_el[] = & $form->createElement('checkbox', 'same_places', null, get_lang('SameForAll'), array ('onclick' => "javascript:switch_state('places')"));
		$form->addGroup($group_el, 'groups', null, '</td><td>', false);
	}
	// Properties for all groups
	for ($group_number = 0; $group_number < $_POST['number_of_groups']; $group_number ++)
	{
		$group_el = array ();
		$group_el[] = & $form->createElement('text', 'group_'.$group_number.'_name');
		if (api_get_setting('allow_group_categories') == 'true')
		{
			$group_el[] = & $form->createElement('select', 'group_'.$group_number.'_category', null, $cat_options, array ('id' => 'category_'.$group_number));
		}
		//$group_el[] = & $form->createElement('select', 'group_'.$group_number.'_tutor', null, $tutor_options, array ('id' => 'tutor_'.$group_number));
		$group_el[] = & $form->createElement('text', 'group_'.$group_number.'_places', null, array ('size' => 3, 'id' => 'places_'.$group_number));
		

		if($_POST['number_of_groups']<10000)
		{
			if ($group_id<10)
			{
				$prev='000';
			}
			elseif ($group_id<100)
			{
				$prev='00';
			}
			elseif ($group_id<1000)
			{
				$prev='0';
			}
			else
			{
				$prev='';
			}
		}		
				
		$defaults['group_'.$group_number.'_name'] = get_lang('GroupSingle').' '.$prev.$group_id ++;		
		
		$form->addGroup($group_el, 'group_'.$group_number, null, '</td><td>', false);
	}
	$defaults['action'] = 'create_groups';
	$defaults['number_of_groups'] = $_POST['number_of_groups'];
	$form->setDefaults($defaults);
	$form->addElement('style_submit_button', 'submit', get_lang('CreateGroup'), 'class="save"');
	$form->display();
	}
}
else
{
	/*
	 * Show form to generate new groups
	 */
	$categories = GroupManager :: get_categories();
	if (count($categories) > 1 || isset ($categories[0]))
	{
		$create_groups_form = new FormValidator('create_groups');
		$create_groups_form->addElement('header', '', $nameTools);
		$group_el = array ();
		$group_el[] = & $create_groups_form->createElement('static', null, null, get_lang('Create'));
		$group_el[] = & $create_groups_form->createElement('text', 'number_of_groups', null, array ('size' => 3));
		$group_el[] = & $create_groups_form->createElement('static', null, null, get_lang('NewGroups'));
		$group_el[] = & $create_groups_form->createElement('style_submit_button', 'submit', get_lang('ProceedToCreateGroup'), 'class="save"');
		$create_groups_form->addGroup($group_el, 'create_groups', null, ' ', false);
		$defaults = array ();
		$defaults['number_of_groups'] = 1;
		$create_groups_form->setDefaults($defaults);
		$create_groups_form->display();
	}
	else
	{
		echo get_lang('NoCategoriesDefined');
	}

	/*
	 * Show form to generate subgroups
	 */
	if (api_get_setting('allow_group_categories') == 'true' && count(GroupManager :: get_group_list()) > 0)
	{
		$base_group_options = array ();
		$groups = GroupManager :: get_group_list();
		foreach ($groups as $index => $group)
		{
			$number_of_students = GroupManager :: number_of_students($group['id']);
			if ($number_of_students > 0)
			{
				$base_group_options[$group['id']] = $group['name'].' ('.$number_of_students.' '.get_lang('Users').')';
			}
		}
		if (count($base_group_options) > 0)
		{
			echo '<b>'.get_lang('CreateSubgroups').'</b>';
			echo '<blockquote>';
			echo '<p>'.get_lang('CreateSubgroupsInfo').'</p>';
			$create_subgroups_form = new FormValidator('create_subgroups');
			$create_subgroups_form->addElement('hidden', 'action');
			$group_el = array ();
			$group_el[] = & $create_subgroups_form->createElement('static', null, null, get_lang('CreateNumberOfGroups'));
			$group_el[] = & $create_subgroups_form->createElement('text', 'number_of_groups', null, array ('size' => 3));
			$group_el[] = & $create_subgroups_form->createElement('static', null, null, get_lang('WithUsersFrom'));
			$group_el[] = & $create_subgroups_form->createElement('select', 'base_group', null, $base_group_options);
			$group_el[] = & $create_subgroups_form->createElement('submit', 'submit', get_lang('Ok'));
			$create_subgroups_form->addGroup($group_el, 'create_groups', null, ' ', false);
			$defaults = array ();
			$defaults['action'] = 'create_subgroups';
			$create_subgroups_form->setDefaults($defaults);
			$create_subgroups_form->display();
			echo '</blockquote>';
		}
	}
	/*
	 * Show form to generate groups from classes subscribed to the course
	 */
	$classes = ClassManager :: get_classes_in_course($_course['sysCode']);
	if (count($classes) > 0)
	{
		echo '<b>'.get_lang('GroupsFromClasses').'</b>';
		echo '<blockquote>';
		echo '<p>'.get_lang('GroupsFromClassesInfo').'</p>';
		echo '<ul>';
		foreach ($classes as $index => $class)
		{
			$number_of_users = count(ClassManager :: get_users($class['id']));
			echo '<li>';
			echo $class['name'];
			echo ' ('.$number_of_users.' '.get_lang('Users').')';
			echo '</li>';
		}
		echo '</ul>';

		$create_class_groups_form = new FormValidator('create_class_groups_form');
		$create_class_groups_form->addElement('hidden', 'action');
		if (api_get_setting('allow_group_categories') == 'true')
		{
			$group_categories = GroupManager :: get_categories();
			$cat_options = array ();
			foreach ($group_categories as $index => $category)
			{
				$cat_options[$category['id']] = $category['title'];
			}
			$create_class_groups_form->addElement('select', 'group_category', null, $cat_options);
		}
		else
		{
			$create_class_groups_form->addElement('hidden', 'group_category');
		}
		$create_class_groups_form->addElement('submit', 'submit', get_lang('Ok'));
		$defaults['group_category'] = DEFAULT_GROUP_CATEGORY;
		$defaults['action'] = 'create_class_groups';
		$create_class_groups_form->setDefaults($defaults);
		$create_class_groups_form->display();
		echo '</blockquote>';
	}
}

// display the footer
Display :: display_footer();
?>
