<?php 
// name of the language file that needs to be included
$language_file[] = 'tracking';

// including the global Dokeos file
require_once '../inc/global.inc.php';

// including additonal libraries
require_once (api_get_path(LIBRARY_PATH).'sortabletable.class.php');
require_once (api_get_path(LIBRARY_PATH).'course.lib.php');

// the section (for the tabs)
$this_section=SECTION_COURSES;

// Header
Display::display_tool_header($nameTools, 'Tracking');

echo '<style type="text/css">a.nolink{color: #000000; text-decoration: none}</style>';

//actions
echo '<div class="actions">';
if (!empty($_GET['view']) AND $_GET['view']<>'coursemembers'){
	echo '<a href="tracking_item.php?tool='.Security::Remove_XSS($_GET['tool']).'&amp;item='.Security::Remove_XSS($_GET['item']).'&amp;view=coursemembers">'.Display::return_icon('members.gif').' '.get_lang('CourseMembers').'</a>';
} else {
	echo '<a href="#" class="nolink">'.Display::return_icon('members.gif').' '.get_lang('CourseMembers').'</a>';
}
if (empty($_GET['view']) OR $_GET['view']<>'others'){
	echo '<a href="tracking_item.php?tool='.Security::Remove_XSS($_GET['tool']).'&amp;item='.Security::Remove_XSS($_GET['item']).'&amp;view=others">'.Display::return_icon('members.gif').' '.get_lang('Others').'</a>';
} else {
	echo '<a href="#" class="nolink">'.Display::return_icon('members.gif').' '.get_lang('Others').'</a>';
}
echo '</div>';

// display item information


// creating the sortable table
$table = new SortableTable('tracking', 'get_number_of_users', 'get_user_item_access',2);
$table->set_additional_parameters(array('item'=>$_GET['item'], 'tool'=> $_GET['tool'], 'view'=> $_GET['view']));
$table->set_header(0, get_lang('FirstName'),false);
$table->set_header(1, get_lang('LastName'),false);
$table->set_header(2, get_lang('LastAccess'),false);
$table->display();

// footer
Display:: display_footer();


function get_number_of_users(){
	global $_course;

	// if we only want to view the course member then we are fine with this library
	$users = CourseManager::get_user_list_from_course_code($_course['id']);

	// if we however want to see the other users then we first want to find which ids of users
	// that have viewed the item that are not in the course. (this would also have been possible using a join)
	if ($_GET['view'] == 'others'){
		// database table definition
		switch ($_GET['tool']){
			case 'document':
				$table = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_DOWNLOADS);
				$itemfield = 'down_doc_path';
				$userfield = 'down_cours_id';
		}

		// select all the users that have viewed the item
		$sql = "SELECT DISTINCT down_user_id
				FROM $table
				WHERE $itemfield = '".Database::escape_string(urldecode($_GET['item']))."'
				AND  $userfield = '".Database::escape_string($_course['id'])."'";
		$res = api_sql_query($sql, __FILE__, __LINE__);
		while ($row = Database::fetch_array($res)) {
			if (!array_key_exists($row['down_user_id'],$users)){
				$others[] = $row['down_user_id'];
			}
		}
		$return = $others;
	} else {
		$return = $users;
	}
	return count($return);
}

function get_user_item_access($from, $number_of_items, $column, $direction){
	global $_course;

	// database table definition
	switch ($_GET['tool']){
		case 'document':
			$table = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_DOWNLOADS);
			$itemfield = 'down_doc_path';
			$userfield = 'down_cours_id';
	}

	// gettin all the users of the course
	$users = CourseManager::get_user_list_from_course_code($_course['id']);

	// getting the last download for every user
	$sql = "SELECT down_user_id, max( down_date ) as date
			FROM $table
			WHERE $itemfield = '".Database::escape_string(urldecode($_GET['item']))."'
			AND $userfield = '".Database::escape_string($_course['id'])."'
			GROUP BY down_user_id";
	$res = api_sql_query($sql, __FILE__, __LINE__);
	while ($row = Database::fetch_array($res)) {
		$tracking_info[$row['down_user_id']] = $row['date'];
	}

	// looping through the course users and filling the $return array that is used in the sortable table
	foreach ($users as $user_id=>$user){
		$table_row = array();
		$table_row[]=$user['firstname'];
		$table_row[]=$user['lastname'];
		$table_row[]=$tracking_info[$user_id];
		$return[] = $table_row;

		// if we want to see the others then we unset the information because we are going to search the user information of the remaining tracking information
		if ($_GET['view'] == 'others'){
			unset($tracking_info[$user_id]);
		}
	}


	if ($_GET['view'] == 'others'){
		// $return contains the information of the course members and we do not want to display this information
		unset($return);

		// creating an array of all the user_id that we have to look up in the user table
		foreach ($tracking_info as $user_id => $date){
			$user_array[]=$user_id;
		}

		// fetching the user information of these remaining users
		$main_user_table = Database :: get_main_table(TABLE_MAIN_USER);
		if (is_array($user_array) and !empty($user_array)){
			$sql = "SELECT user_id, firstname, lastname FROM $main_user_table WHERE user_id IN ('".implode("','",$user_array)."')";
			$res = api_sql_query($sql, __FILE__, __LINE__);
			while ($row = Database::fetch_array($res)) {
				$users_other[$row['user_id']] = array('firstname'=>$row['firstname'], 'lastname'=>$row['lastname']);
			}
		}

		// looping through the other users and filling the $return array that is used in the sortable table
		foreach ($users_other as $user_id=>$user){
			$table_row = array();
			$table_row[]=$user['firstname'];
			$table_row[]=$user['lastname'];
			$table_row[]=$tracking_info[$user_id];
			$return[] = $table_row;
		}		
	}	

	return $return;
}
?>
