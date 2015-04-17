<?php

/* For licensing terms, see /dokeos_license.txt */

/**
==============================================================================
*	@package dokeos.group
==============================================================================
*/

// settings (temporarily added here)
$_setting['group_overview'] = 'true';
$_setting['group_export_csv'] = 'true';
$_setting['group_export_xls'] = 'true';
// setting to remove: allow_group_categories

// name of the language file that needs to be included
$language_file = "group";

// including the global Dokeos file
require_once ('../inc/global.inc.php');

// including additional libraries
include_once (api_get_path(LIBRARY_PATH).'course.lib.php');
include_once (api_get_path(LIBRARY_PATH).'groupmanager.lib.php');
include_once (api_get_path(LIBRARY_PATH).'export.lib.inc.php');

// the section (for the tabs)
$this_section = SECTION_COURSES;

// access control
api_protect_course_script(true);
if (!api_is_allowed_to_edit(false,true))
{
	api_not_allowed();
}

// tracking
event_access_tool(TOOL_GROUP);

$nameTools = get_lang('GroupOverview');

// header actions
header_actions();

// breadcrumbs
$interbreadcrumb[]=array('url' => "group.php","name" => get_lang('Groups'));

// display the header
Display::display_header(get_lang('Groups'));

// Tool introduction
Display::display_introduction_section(TOOL_GROUP);

// display the tool title
//api_display_tool_title(get_lang('Groups'));

// primary actions
display_actions();

//start the content div
echo '<div id="content">';

// all the categories
$categories 	= GroupManager::get_categories();
$categories[] 	= array('id'=>0);

// all the groups
$groups		= GroupManager::get_all_groups();

// all the users
$usersandgroups	= GroupManager::get_all_groups_and_users('group',array('user.firstname','user.lastname','user.email'));

// looping through all the group categories (scenarios)
foreach($categories as $index => $category)
{
	// displaying the category (scenario) type
	if ($category['id'] <> 0){
		echo '<h3>'.$category['title'].'</h3>';
	} else {
		echo '<h3>'.get_lang('NoCategory').'</h3>';
	}

	// displaying all the groups in this category (scenario)
	echo '<ul class="groupsoverviewgroup">';
	foreach($groups as $index => $group)
	{
		if ($group['category_id'] == $category['id']) {
			echo '<li>'.$group['name'];
			echo '<ul class="groupsoverviewusers">';
			// displaying all the users in this group
			foreach($usersandgroups[$group['id']] as $index => $user)
			{
				echo '<li>'.$user['firstname'].' '.$user['lastname'].'</li>';
			}
			echo '</ul>';
			echo '</li>';
		}
	}
	echo '</ul>';
}

// close the content div
echo '</div>';

// secondary action links
display_secondary_actions();

// Display the footer
Display::display_footer();



function display_actions(){
	echo '<div class="actions">';
	echo '<a href="group.php?'.api_get_cidreq().'">'.Display::return_icon('group.png', get_lang('Groups')).get_lang('Groups').'</a>';
	if (api_is_allowed_to_edit(false,true))
	{
		echo '<a href="group_creation.php?'.api_get_cidreq().'">'.Display::return_icon('groupadd.png', get_lang('NewGroupCreate')).' '.get_lang('NewGroupCreate').'</a>';
		echo '<a href="group_category.php?'.api_get_cidreq().'">'.Display::return_icon('dokeos_scenario.png', get_lang('Scenario')).' '.get_lang('Scenario').'</a>';
	}
	echo '</div>';
}

function header_actions(){
	if( isset($_GET['action']))
	{
		switch($_GET['action'])
		{
			case 'export':
				$groups = GroupManager::get_group_list();
				$data = array();
				foreach($groups as $index => $group)
				{
					$users = GroupManager::get_users($group['id'],api_get_setting('user_order_by'));
					foreach($users as $index => $user)
					{
						$row = array();
						$user = api_get_user_info($user);
						$row[] = $group['name'];
						$row[] = $user['official_code'];
						$row[] = $user['lastName'];
						$row[] = $user['firstName'];
						$data[] = $row;
					}
				}
				switch($_GET['type'])
				{
					case 'csv':
						Export::export_table_csv($data);
					case 'xls':
						Export::export_table_xls($data);
				}
				break;
		}
	}
}

function display_secondary_actions(){
	echo '<div class="actions">';
	if (api_is_allowed_to_edit(false,true)){
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
?>