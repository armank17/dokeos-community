<?php
/* For licensing terms, see /dokeos_license.txt */
/**
 * Responses to AJAX calls
 */
require_once '../inc/global.inc.php';
$action = $_GET['a'];
global $_course;

switch ($action) {
	case 'find_users':

		if (api_is_anonymous()){
			echo '';
			break;
		}
		$track_online_table = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ONLINE);
		$tbl_my_user		= Database :: get_main_table(TABLE_MAIN_USER);
		$tbl_my_user_friend = Database :: get_main_table(TABLE_MAIN_USER_REL_USER);
		$tbl_user 			= Database :: get_main_table(TABLE_MAIN_USER);
		$search				= Database::escape_string($_POST['search']);
		$current_date		= date('Y-m-d H:i:s',time());

		$table_group_user 	= Database :: get_course_table(TABLE_GROUP_USER);
		$table_group_tutor 	= Database :: get_course_table(TABLE_GROUP_TUTOR);

		$user_id = api_get_user_id();
		$is_western_name_order = api_is_western_name_order();

		
		if(api_is_allowed_to_edit()){
		
		$sql = 'SELECT DISTINCT u.user_id as id, '.($is_western_name_order ? 'concat(u.firstname," ",u.lastname," ")' : 'concat(u.lastname," ",u.firstname," ")').' as name
				FROM '.$tbl_user.' u
		 		WHERE u.status <> 6  AND u.user_id <>'.(int)$user_id.' AND '.($is_western_name_order ? 'concat(u.firstname, " ", u.lastname)' : 'concat(u.lastname, " ", u.firstname)').' like CONCAT("'.$search.'","%")';
		}
		else if(api_is_grouptutor($_course,api_get_session_id(),api_get_user_id())){		

		$sql_group = "SELECT group_id FROM $table_group_tutor group_tutor WHERE user_id = ".api_get_user_id();
		$rs_group = Database::query($sql_group);
		while($row_group = Database::fetch_array($rs_group)){
			$group_id = $row_group['group_id'];
		}
		
		// users of that group
		$sql = "SELECT u.user_id as id, ".($is_western_name_order ? "concat(u.firstname,' ',u.lastname,' ')" : "concat(u.lastname,' ',u.firstname,' ')")." as name FROM $tbl_my_user u,$table_group_user group_user WHERE u.user_id = group_user.user_id AND group_user.group_id = ".$group_id;
		$result=Database::query($sql);

		if (Database::num_rows($result)>0) {
				while ($row = Database::fetch_array($result,'ASSOC')) {
				$return[] = array('caption'=>$row['name'], 'value'=>$row['id']);
			}
		}
		//tutors of other groups
		$sql = "SELECT u.user_id as id, ".($is_western_name_order ? "concat(u.firstname,' ',u.lastname,' ')" : "concat(u.lastname,' ',u.firstname,' ')")." as name FROM $tbl_my_user u,$table_group_tutor group_tutor WHERE u.user_id = group_tutor.user_id AND group_tutor.group_id <> ".$group_id;

		}
		else {

		$sql_group = "SELECT group_id FROM $table_group_user group_user WHERE user_id = ".api_get_user_id();
		$rs_group = Database::query($sql_group);
		while($row_group = Database::fetch_array($rs_group)){
			$group_id = $row_group['group_id'];
		}

		$sql = "SELECT u.user_id as id, ".($is_western_name_order ? "concat(u.firstname,' ',u.lastname,' ')" : "concat(u.lastname,' ',u.firstname,' ')")." as name FROM $tbl_my_user u,$table_group_tutor group_tutor WHERE u.user_id = group_tutor.user_id AND group_tutor.group_id = ".$group_id;

		}
		$result=Database::query($sql);

		if (Database::num_rows($result)>0) {
				while ($row = Database::fetch_array($result,'ASSOC')) {
				$return[] = array('caption'=>$row['name'], 'value'=>$row['id']);
			}
		}
		echo json_encode($return);
		break;


	default:
		echo '';

}
exit;
?>