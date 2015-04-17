<?php

/* For licensing terms, see /dokeos_license.txt */

/**
==============================================================================
*	Main page for the group module.
*	@package dokeos.group
==============================================================================
*/

// settings (temporarily added here)
$_setting['group_overview'] = 'true';
$_setting['group_export_csv'] = 'true';
$_setting['group_export_xls'] = 'true';

// name of the language file that needs to be included
$language_file = 'group';

// including the global Dokeos file
require_once ('../inc/global.inc.php');

// including additional libraries
require_once (api_get_path(LIBRARY_PATH).'course.lib.php');
require_once (api_get_path(LIBRARY_PATH).'groupmanager.lib.php');
require_once (api_get_path(LIBRARY_PATH).'sortabletable.class.php');

// the section (for the tabs)
$this_section = SECTION_COURSES;

// access control
api_protect_course_script(true);

// tracking
event_access_tool(TOOL_GROUP);

// name of the tool
$nameTools = get_lang('Groups');

// display the header
Display::display_header(get_lang('Groups'));

// Tool introduction
Display::display_introduction_section(TOOL_GROUP);

// unsetting some local $_SESSION variables
unset($_SESSION['groupmembers'.api_get_course_id()]);
unset($_SESSION['groupmembership'.api_get_course_id()]);
unset($_SESSION['grouplist'.api_get_course_id()]);

// display the tool title
//api_display_tool_title(get_lang('Groups'));

// action links
display_actions();

//start the content div
echo '<div id="content">';

// cleaning up session variables that have been used so that we always get 'fresh' information
$_SESSION['groupmembers'.api_get_course_id()] = null;
$_SESSION['coaches'.api_get_course_id()] = null;
$_SESSION['group_info'.api_get_course_id()] = null;

// action handling
group_actions();

// display the groups
display_groups();

// close the content div
echo '</div>';

// secondary action links
display_secondary_actions();

// Display the footer
Display::display_footer();
exit;


function display_groups(){
	$table = new SortableTable('groups', 'get_number_of_groups', 'get_groups_data');

	// table headers
	$table->set_header(0, '', false);
	$table->set_header(1, get_lang('GroupName'));	
	$table->set_header(2, get_lang('Tutor'));
	$table->set_header(3, get_lang('GroupCategory'));
	$table->set_header(4, get_lang('GroupAvailablePlaces'));
	$table->set_header(5, get_lang('GroupMembers'));
	if (api_is_allowed_to_edit(false,true)){
		$table->set_header(6, get_lang('Empty'));
		$table->set_header(7, get_lang('Fill'));
		$table->set_header(8, get_lang('Edit'));
		//$table->set_header(9, get_lang('Delete'));
	} else {
		$table->set_header(6,get_lang('GroupMembership'));
	}

	// table data filters
	$table->set_column_filter(1, 'group_name_filter');
	$table->set_column_filter(2, 'tutor_filter');
	$table->set_column_filter(5, 'group_members_filter');
	if (api_is_allowed_to_edit(false,true)){
		$table->set_column_filter(6, 'empty_filter');
		$table->set_column_filter(7, 'fill_filter');
		$table->set_column_filter(8, 'edit_filter');
		//$table->set_column_filter(9, 'delete_filter');
	} else {
		$table->set_column_filter(6, 'groupmembership_filter');
	}

	// table form actions (actions on selected items)
	if (api_is_allowed_to_edit(false,true)){
		$table->set_form_actions(array ('delete' => get_lang('DeleteGroup')));
	} else {
		$table->set_form_actions(array ('join' => get_lang('JoinGroup')));
	}
	$table->display();
}

function get_number_of_groups(){
	// Database table definition
	$table_group =	Database::get_course_table(TABLE_GROUP);

	$sql = "SELECT count(id) AS total_number_of_items FROM $table_group";
	$res = Database::query($sql, __FILE__, __LINE__);
	$obj = Database::fetch_object($res);
	return $obj->total_number_of_items;
}

function get_groups_data($from, $number_of_items, $column, $direction){
	// Database table definition
	$table_group 		= Database::get_course_table(TABLE_GROUP);
	$table_group_category 	= Database :: get_course_table(TABLE_GROUP_CATEGORY);

	if (api_is_allowed_to_edit(false,true)){
		$sql = "SELECT 
				groupinfo.id 		AS col0,
				groupinfo.name 		AS col1,
				groupinfo.session_id	AS col2,
				group_category.title	AS col3,
				groupinfo.max_student	AS col4,
				groupinfo.id		AS col5,
				groupinfo.id		AS col6,
				groupinfo.id		AS col7,
				groupinfo.id 		AS col8
			FROM $table_group groupinfo
			LEFT JOIN $table_group_category group_category
			ON groupinfo.category_id = group_category.id
			";
		$sql .= " ORDER BY col$column $direction ";
		$sql .= " LIMIT $from,$number_of_items";
	} else {
		$sql = "SELECT 
				groupinfo.id 		AS col0,
				groupinfo.name 		AS col1,
				groupinfo.session_id	AS col2,
				group_category.title	AS col3,
				groupinfo.max_student	AS col4,
				groupinfo.id		AS col5,
				groupinfo.id		AS col6
			FROM $table_group groupinfo
			LEFT JOIN $table_group_category group_category
			ON groupinfo.category_id = group_category.id
			";
		$sql .= " ORDER BY col$column $direction ";
		$sql .= " LIMIT $from,$number_of_items";
	}
	$res = Database::query($sql, __FILE__, __LINE__);
	while ($group = Database::fetch_row($res)) {
		$return[] = $group;
	}
	return $return;
}

function group_name_filter($group_name,$url_params,$row){
	return '<a href="group_space.php?'.api_get_cidreq().'&amp;gidReq='.$row[0].'&amp;group_id='.$row[0].'">'.$group_name.'</a>';
}

function tutor_filter($session_id,$url_params,$row){
	// here we have to check if the sessions are enabled
	// if this is the case we need to display the session coach
	// if it is not enabled we display the tutors (for backwards compatibility)
	$_SESSION['coaches'] = array();
	if (api_get_setting('use_session_mode') == 'true'){
		if (!array_key_exists($session_id, $_SESSION['coaches'])){
			$_SESSION['coaches'.api_get_course_id()][$session_id] = api_get_coachs_from_course($session_id);
		}

		// according to the code (api_get_coachs_from_course) there can be multiple session coaches
		foreach ($_SESSION['coaches'.api_get_course_id()][$session_id] as $key=>$session_coach){
			$return .= $session_coach['firstname'].' '.$session_coach['lastname'];
		}
		return $return;
	} else {
		// code to write
	}

}

function group_members_filter($group_id, $url_params, $row){
	global $_course;

	// Database table definition
	$table_group 	  = Database::get_course_table(TABLE_GROUP);
	$table_group_user = Database :: get_course_table(TABLE_GROUP_USER);

	// count the number of members in the group if none is set
	if (!is_array($_SESSION['groupmembers'.api_get_course_id()])){
		$sql = "SELECT group_id, count(id) as number_of_users FROM ".$table_group_user." group by group_id";
		$result = Database::query($sql,__FILE__,__LINE__);
		while ($row = Database::fetch_array($result)) {
			$_SESSION['groupmembers'.api_get_course_id()][$row['group_id']] = $row['number_of_users'];
		}
	}
	return $_SESSION['groupmembers'.api_get_course_id()][$group_id];
}

function empty_filter($group_id, $url_params, $row){
	return '<div align="center"><a href="group.php?action=empty_one&amp;group_id='.$group_id.'">'.Display::return_icon('empty.png',get_lang('EmptyGroup')).'</a></div>';
}

function fill_filter($group_id, $url_params, $row){
	return '<div align="center"><a href="group.php?action=fill_one&amp;group_id='.$group_id.'">'.Display::return_icon('filter.png',get_lang('FillGroup')).'</a></div>';
}

function edit_filter($group_id, $url_params, $row){
	return '<div align="center"><a href="group_edit.php?gidReq='.$group_id.'&amp;group_id='.$group_id.'">'.Display::return_icon('edit.png',get_lang('EditGroup')).'</a></div>';
}

function delete_filter($group_id, $url_params, $row){
	return '<a href="group.php?action=delete_one&amp;group_id='.$group_id.'">'.Display::return_icon('delete.png',get_lang('DeleteGroup')).'</a>';
}

function groupmembership_filter($group_id, $url_params, $row){
	global $_user;

	// count the number of members in the group if none is set
	if (!is_array($_SESSION['groupmembers'.api_get_course_id()])){
		$sql = "SELECT group_id, count(id) as number_of_users FROM ".$table_group_user." group by group_id";
		$result = Database::query($sql,__FILE__,__LINE__);
		while ($row = Database::fetch_array($result)) {
			$_SESSION['groupmembers'.api_get_course_id()][$row['group_id']] = $row['number_of_users'];
		}
	}

	// get all the group information (and cache it)
	if (!is_array($_SESSION['grouplist'.api_get_course_id()])){
		$groups_unsorted = GroupManager::get_group_list();
		foreach ($groups_unsorted as $key=>$group){
			$group_list[$group['id']] = $group;
		}
		$_SESSION['grouplist'.api_get_course_id()] = $group_list;
	}

	// get all the group the user is subscribed to
	if (!is_array($_SESSION['groupmembership'.api_get_course_id()])){
		$_SESSION['groupmembership'.api_get_course_id()] = GroupManager::get_group_ids(null,$_user['user_id']);
	}

	// display link to register to the group if
	// 1. the number of places is higher than the number of students already registerend AND 
	// 2. registration is allowed AND
	// 3. the user is not in the group yet
	if ($_SESSION['groupmembers'.api_get_course_id()][$group_id] < $_SESSION['grouplist'.api_get_course_id()][$group_id]['maximum_number_of_members']
		AND $_SESSION['grouplist'.api_get_course_id()][$group_id]['self_registration_allowed'] == 1 
		AND !in_array($group_id, $_SESSION['groupmembership'.api_get_course_id()])){
		return '<a href="group.php?action=self_reg&amp;group_id='.$group_id.'">register</a>';
	}
	
	// display the link to unregister if
	// 1. the user is registered AND
	// 2. unregistration is allowed
	if ( in_array($group_id, $_SESSION['groupmembership'.api_get_course_id()]) AND $_SESSION['grouplist'.api_get_course_id()][$group_id]['self_unregistration_allowed'] == 1){
		return '<a href="group.php?action=self_unreg&amp;group_id='.$group_id.'">unregister</a>';
	}

	// display 'group full' if 
	// 1. the number of places is equal than the number of students already registerend AND 
	// 2. the user is not in the group yet
	if ($_SESSION['groupmembers'.api_get_course_id()][$group_id] == $_SESSION['grouplist'.api_get_course_id()][$group_id]['maximum_number_of_members']
		AND !in_array($group_id, $_SESSION['groupmembership'.api_get_course_id()])){
		return 'group full';
	}
}


function display_secondary_actions(){
	echo '<div class="actions">';
	if (api_is_allowed_to_edit(false,true)){
		// group overview
		if (api_get_setting('group_overview') == 'true'){
			if( Database::count_rows(Database::get_course_table(TABLE_GROUP)) > 0) {
				echo '<a href="group_overview.php?'.api_get_cidreq().'">'.Display::return_icon('group.gif', get_lang('GroupOverview')).' '.get_lang('GroupOverview').'</a>';
			} else {
				echo '<a href="#" class="invisible">'.Display::return_icon('group.gif', get_lang('GroupOverview')).' '.get_lang('GroupOverview').'</a>';
			}
		}

		// export
		if (api_is_allowed_to_edit(false,true)){
			if (api_get_setting('group_export_csv') == 'true') {
				echo '<a href="group_overview.php?'.api_get_cidreq().'&amp;action=export&amp;type=csv">'.Display::return_icon('csv.gif', get_lang('ExportAsCSV')).' '.get_lang('ExportAsCSV').'</a> ';
		        }
			if (api_get_setting('group_export_xls') == 'true') {
				echo ' <a href="group_overview.php?'.api_get_cidreq().'&amp;action=export&amp;type=xls">'.Display::return_icon('excel.gif', get_lang('ExportAsXLS')).' '.get_lang('ExportAsXLS').'</a>';
			}
		}
		echo '</div>';
	}
	echo '</div>';
}




function display_actions(){

	if (api_is_allowed_to_edit(false,true))
	{
		echo '<div class="actions">';
		// link to add a group
		echo '<a href="group_creation.php?'.api_get_cidreq().'">'.Display::return_icon('groupadd.png', get_lang('NewGroupCreate'), array('style'=>'height: 32px;')).' '.get_lang('NewGroupCreate').'</a>';
		if (api_get_setting('allow_group_categories') == 'true') {
			echo '<a href="group_category.php?'.api_get_cidreq().'&amp;action=add_category">'.Display::return_icon('folder_new.gif', get_lang('AddCategory')).' '.get_lang('AddCategory').'</a>&nbsp;';
		} else {
			echo '<a href="group_category.php?'.api_get_cidreq().'">'.Display::return_icon('dokeos_scenario.png', get_lang('Scenario')).' '.get_lang('Scenario').'</a>';
		}
		echo '</div>';
	}
}




function group_actions(){
	global $_user;

	// actions that a student can do
	if (isset ($_GET['action']))
	{
		switch ($_GET['action'])
		{
			case 'self_reg' :
				if (GroupManager :: is_self_registration_allowed($_SESSION['_user']['user_id'], Security::remove_XSS($_GET['group_id']))) {
					GroupManager :: subscribe_users($_SESSION['_user']['user_id'],Security::remove_XSS($_GET['group_id']));
					Display :: display_confirmation_message(get_lang('GroupNowMember'));
				}
				break;
			case 'self_unreg' :
				if (GroupManager :: is_self_unregistration_allowed($_SESSION['_user']['user_id'], Security::remove_XSS($_GET['group_id']))) {
					GroupManager :: unsubscribe_users($_SESSION['_user']['user_id'],Security::remove_XSS($_GET['group_id']));
					Display :: display_confirmation_message(get_lang('StudentDeletesHimself'));
				}
				break;
			case 'show_msg' :
				Display :: display_confirmation_message(Security::remove_XSS($_GET['msg']));
				break;
		}
	}

	// actions that a student can do
	if (isset ($_POST['action']))
	{
		switch ($_POST['action']){
			case 'join': 
			foreach ($_POST['id'] as $key=>$group_id){
					GroupManager :: subscribe_users ($_user['user_id'], $group_id);
				}
				break;
		}
	}

	// actions that a teacher can do
	if (api_is_allowed_to_edit(false,true))
	{
		// Post-actions
		if (isset ($_POST['action']))
		{
			switch ($_POST['action'])
			{
				case 'delete' :
					if( is_array($_POST['id'])) {
						GroupManager :: delete_groups($_POST['id']);
						Display :: display_confirmation_message(get_lang('SelectedGroupsDeleted'));
					}
					break;
				case 'empty_selected' :
					if( is_array($_POST['id']))	{
					    GroupManager :: unsubscribe_all_users($_POST['group']);
					    Display :: display_confirmation_message(get_lang('SelectedGroupsEmptied'));
					}
					break;
				case 'fill_selected' :
					if( is_array($_POST['id']))	{
					    GroupManager :: fill_groups($_POST['group']);
					    Display :: display_confirmation_message(get_lang('SelectedGroupsFilled'));
					}
					break;
			}
		}
		// Get-actions
		if (isset ($_GET['action']))
		{
			switch ($_GET['action'])
			{
				case 'swap_cat_order' :
					GroupManager :: swap_category_order(Security::remove_XSS($_GET['id1']),Security::remove_XSS($_GET['id2']));
					Display :: display_confirmation_message(get_lang('CategoryOrderChanged'));
					break;
				case 'delete_one' :
					GroupManager :: delete_groups(Security::remove_XSS($_GET['group_id']));
					Display :: display_confirmation_message(get_lang('GroupDel'));
					break;
				case 'empty_one' :
					GroupManager :: unsubscribe_all_users(Security::remove_XSS($_GET['group_id']));
					Display :: display_confirmation_message(get_lang('GroupEmptied'));
					break;
				case 'fill_one' :
					GroupManager :: fill_groups(Security::remove_XSS($_GET['group_id']));
					Display :: display_confirmation_message(get_lang('GroupFilledGroups'));
					break;
				case 'delete_category' :
					GroupManager :: delete_category(Security::remove_XSS($_GET['group_id']));
					Display :: display_confirmation_message(get_lang('CategoryDeleted'));
					break;
			}
		}
	}
}
?>
