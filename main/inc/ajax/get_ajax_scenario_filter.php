<?php
/* For licensing terms, see /dokeos_license.txt */
/**
 * Responses to AJAX calls 
 */
$language_file = array('admin', 'registration');
require_once '../global.inc.php';
require_once(api_get_path(LIBRARY_PATH).'course.lib.php');
$action = $_GET["action"];
$step_values = $_GET["steps"];
$course_code = $_GET["course_code"];

$TBL_SCENARIO_STEPS = Database :: get_course_table(TABLE_SCENARIO_STEPS);
$TBL_SCENARIO_ACTIVITY = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY);	
$TBL_SCENARIO_ACTIVITY_VIEW = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY_VIEW);
$TBL_COURSE_USER = Database :: get_main_table(TABLE_MAIN_COURSE_USER);

$course_users = array();
$final_users = array();
$step_array = array();

/*$sql_users = "SELECT user_id FROM $TBL_COURSE_USER WHERE course_code = '".$course_code."' AND status = 5";
$res_users = Database::query($sql_users);
while($row_users = Database::fetch_array($res_users)){
	$course_users[] = $row_users['user_id'];
}*/

$course_users_final = CourseManager::get_user_list_from_course_code($course_code, false , api_get_session_id());
foreach($course_users_final as $courseUsers){
	$course_users[] = $courseUsers['user_id'];
}

if(!empty($step_values)){
$step_array = explode(",",$step_values);
}

foreach($step_array as $steps){

	$users = array();
	list($filter_code, $step_id) = split("-",$steps);	

	$sql_activity = "SELECT * FROM $TBL_SCENARIO_ACTIVITY WHERE step_id = ".$step_id;
	$res_activity = Database::query($sql_activity, __FILE__, __LINE__);
	$num_activity = Database::num_rows($res_activity);

	$sql_not = "SELECT DISTINCT(user_id) FROM $TBL_SCENARIO_ACTIVITY_VIEW WHERE step_id = ".$step_id;
	$res_not = Database::query($sql_not, __FILE__, __LINE__);
	$users = array();
	while($row_not = Database::fetch_array($res_not)){
		$users[] = $row_not['user_id'];
	}

	switch ($filter_code) {	
		case '1':
			/*echo $sql_not = "SELECT DISTINCT(user_id) FROM $TBL_SCENARIO_ACTIVITY_VIEW WHERE step_id = ".$step_id;
			$res_not = Database::query($sql_not, __FILE__, __LINE__);
			$users = array();
			while($row_not = Database::fetch_array($res_not)){
				$users[] = $row_not['user_id'];
			}*/
			$not_users = array_diff($course_users, $users);
			$final_users = array_merge($final_users, $not_users);
		break;
		case '2':			
			/*echo $sql_start = "SELECT DISTINCT(user_id) FROM $TBL_SCENARIO_ACTIVITY_VIEW WHERE step_id = ".$step_id;
			$res_start = Database::query($sql_start, __FILE__, __LINE__);
			$users = array();
			while($row_start = Database::fetch_array($res_start)){
				$users[] = $row_start['user_id'];
			}*/
			if(sizeof($users) > 0){
			$final_users = array_merge($final_users, $users);
			}
		break;
		case '3':			
			$completed_users = array();
			//echo $sql_start = "SELECT DISTINCT(user_id) FROM $TBL_SCENARIO_ACTIVITY_VIEW WHERE step_id = ".$step_id;
			//$res_start = Database::query($sql_start, __FILE__, __LINE__);
			//$users = array();
			//while($row_start = Database::fetch_array($res_start)){
			foreach($users as $user_id) {
				$user_id = $user_id;

				$sql_act = "SELECT * FROM $TBL_SCENARIO_ACTIVITY_VIEW av INNER JOIN $TBL_SCENARIO_ACTIVITY act ON av.activity_id = act.id WHERE av.step_id = ".$step_id." AND av.user_id = ".$user_id." AND av.status = 'completed'";
				$res_act = Database::query($sql_act, __FILE__, __LINE__);
				$num_act = Database::num_rows($res_act);

				if($num_act == $num_activity){
					$completed_users[] = $user_id;
				}
			}
			if(sizeof($completed_users) > 0){
			$final_users = array_merge($final_users, $completed_users);
			}
		break;
		case '4':			
			$sql_criteria = "SELECT step_completion_option, step_completion_percent FROM $TBL_SCENARIO_STEPS WHERE id = ".$step_id." AND step_completion_option != ''";
			$res_criteria = Database::query($sql_criteria, __FILE__, __LINE__);
			if(Database::num_rows($res_criteria) > 0){
				$option = Database::result($res_criteria, 0, 0);
				$percent = Database::result($res_criteria, 0, 1);
			}

			foreach($users as $user_id) {
				$user_id = $user_id;

				$sql_act = "SELECT * FROM $TBL_SCENARIO_ACTIVITY_VIEW av INNER JOIN $TBL_SCENARIO_ACTIVITY act ON av.activity_id = act.id WHERE av.step_id = ".$step_id." AND av.user_id = ".$user_id." AND av.status = 'completed'";
				$res_act = Database::query($sql_act, __FILE__, __LINE__);
				$num_act = Database::num_rows($res_act);
				
				if(!empty($option)){
					$failed = 'N';

					$sql_pass = "SELECT score FROM $TBL_SCENARIO_ACTIVITY_VIEW av INNER JOIN $TBL_SCENARIO_ACTIVITY act ON av.activity_id = act.id WHERE av.step_id = ".$step_id." AND av.user_id = ".$user_id." AND act.activity_type = '".strtolower($option)."' AND av.status = 'completed'";
					$res_pass = Database::query($sql_pass, __FILE__, __LINE__);
					while($row_pass = Database::fetch_array($res_pass)){
						if($row_pass['score'] < $percent){
							$failed = 'Y';
						}
					}
					if($num_act == $num_activity && $failed == 'N'){
						$completed_users[] = $user_id;
					}
				}
				else {
					if($num_act == $num_activity){
						$completed_users[] = $user_id;
					}
				}
			}
			if(sizeof($completed_users) > 0){
			$final_users = array_merge($final_users, $completed_users);
			}

		break;
	}

	/*echo 'Anjan final==='.sizeof($final_users);
	foreach($final_users as $endusers){
		echo 'anjan=='.$endusers;
	}*/	
}
$final_users = array_unique($final_users);

if($action == 'get_num_users'){
	$num_final_users = sizeof($final_users);
	if($num_final_users == 0 && empty($step_values)){
		$num_final_users = sizeof($course_users);
	}
	echo " ".$num_final_users." <input type='hidden' id='send_filter' name='send_filter' value='".implode(",",$final_users)."' />";
}
else {	
	
	//echo '<script src="'.api_get_path(WEB_CODE_PATH).'appcore/library/jquery/jquery.nicescroll/jquery.nicescroll.min.js"></script>';
	echo '<script src="'.api_get_path(WEB_CODE_PATH).'course_home/script/jquery.jscrollpane.min.js"></script>';
	echo '<script src="'.api_get_path(WEB_CODE_PATH).'course_home/script/jquery.mousewheel.js"></script>';
	echo '<style>
	.scroll-pane1
	{
		width: auto;
		height: 280px;
		overflow: auto;
	}
	</style>';
	echo '<script>
        $(function () {
			//$("#images").niceScroll({cursorcolor: "#000", cursorwidth:"12px"});  
			$(".scroll-pane1").jScrollPane();
	});
	</script>';
	echo '<table class="data_table" ><tr><th width="30%" width="30%">'.api_convert_encoding(get_lang("LastName"),'UTF-8',api_get_system_encoding()).'</th><th>'.api_convert_encoding(get_lang("FirstName"),'UTF-8',api_get_system_encoding()).'</th><th width="40%">'.api_convert_encoding(get_lang("Email"),'UTF-8',api_get_system_encoding()).'</th></tr><tr><td colspan="3"><div class="scroll-pane1"><table width="100%">';
	if(sizeof($final_users) > 0 && sizeof($step_array) > 0){
		$i = 0;
		foreach($final_users as $user_id){
			if(($i % 2) == 0){
				$class = " class = 'row_odd'";
			}
			else {
				$class = " class = 'row_even'";
			}
			$user_info = api_get_user_info($user_id);

			echo '<tr '.$class.'><td width="30%">'.api_convert_encoding($user_info['lastname'],'UTF-8',api_get_system_encoding()).'</td><td width="30%">'.api_convert_encoding($user_info['firstname'],'UTF-8',api_get_system_encoding()).'</td><td width="40%">'.$user_info['mail'].'</td></tr>';
			$i++;
		}
	}
	else if(sizeof($final_users) == 0 && sizeof($step_array) == 0) {
		$i = 0;
		foreach($course_users as $user_id){
			if(($i % 2) == 0){
				$class = " class = 'row_odd'";
			}
			else {
				$class = " class = 'row_even'";
			}
			$user_info = api_get_user_info($user_id);

			echo '<tr '.$class.'><td width="30%">'.api_convert_encoding($user_info['lastname'],'UTF-8',api_get_system_encoding()).'</td><td width="30%">'.api_convert_encoding($user_info['firstname'],'UTF-8',api_get_system_encoding()).'</td><td width="40%">'.$user_info['mail'].'</td></tr>';
			$i++;
		}
	}
	
	echo '</table></div></td></tr></table>';
}