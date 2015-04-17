<?php
$language_file[] = 'course_home';

require_once '../../main/inc/global.inc.php';

$TBL_SCENARIO_STEPS = Database :: get_course_table(TABLE_SCENARIO_STEPS);
$course_info = api_get_course_info(api_get_course_id());
$course_directory = $course_info['path'];
$step_icon_path = api_get_path(WEB_COURSE_PATH).$course_directory.'/document/icons/thumbnail/';
global $_user;

$session_condition = api_get_session_condition(api_get_session_id(), false);
$sql = "SELECT * FROM $TBL_SCENARIO_STEPS $session_condition ORDER BY step_created_order";

$res = Database::query($sql, __FILE__, __LINE__);
$num_rows = Database::num_rows($res);



echo '<div class="main1">
    <div class="inner">';

		echo '</br>';
		while($row = Database::fetch_array($res)){

			$step_border = $row['step_border'];
			$hide_border = $row['hide_border'];
			$hide_image = $row['hide_image'];
			$step_icon = $row['step_icon'];
			$step_id = $row['id'];		
			$step_prerequisite = $row['step_prerequisite'];
			$tmp_step_name = $row['step_name'];

			if($hide_image == 1){
				$display = "display:none";
				$stepname_limit = 80;
			}
			else {
				$display = "display:block";
				$stepname_limit = 35;
			}

			$stepname_len = strlen($tmp_step_name);
			if($stepname_len > $stepname_limit){
				$step_name = substr($tmp_step_name, 0, $stepname_limit).'...';
			}
			else {
				$step_name = $tmp_step_name;
			}

			if($step_prerequisite == 'None' || $step_prerequisite == 'Click to add Prerequisite') {
				$current_prereq = 'Y';	
				$prereq_stepname = "None";
			}
			else {
				$current_prereq = check_prerequisite($step_id, $step_prerequisite);
				$sql_preqname = "SELECT step_name FROM $TBL_SCENARIO_STEPS WHERE id = ".$step_prerequisite;
				$res_preqname = Database::query($sql_preqname, __FILE__, __LINE__);
				$prereq_stepname = Database::result($res_preqname,0,0);
			}

			if($current_prereq == 'N'){
				$bg_color = 'background:#EEEEEE;';
				$step_border = '#949494';

				$path_parts = pathinfo($step_icon);				
				$step_icon = $path_parts['filename'].'_grey.png';
			}
			else {
				$bg_color = '';
			}	
			
			if($hide_border == 1){
				$border_px = "0px";
			}
			else {
				$border_px = "3px";
			}
			
			/*if(substr($step_icon,0,6) == 'dokeos'){
				$css_margin = "margin-top:5px;";
			}
			else {
				$css_margin = 'margin-top:-10px;';
			}*/

			echo '<div class="div_block" id="div_'.$step_id.'_'.$current_prereq.'_'.$prereq_stepname.'_'.$step_name.'" style="'.$bg_color.'border:'.$border_px.' solid '.$step_border.'"><div class="steptext_style">'.api_convert_encoding($step_name,'UTF-8',api_get_system_encoding()).'</div><img style="'.$display.'; margin: 0 auto;vertical-align:middle;text-align:center;'.$css_margin.'" src="'.$step_icon_path.$step_icon.'" /></div>';
		}
      
echo   ' </div>
</div>';

function check_prerequisite($step_id, $step_prerequisite) {
	$TBL_SCENARIO_ACTIVITY = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY);
	$TBL_SCENARIO_ACTIVITY_VIEW = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY_VIEW);
	$prereq_satisfied = 'Y';
      
	$sql = "SELECT * FROM $TBL_SCENARIO_ACTIVITY WHERE step_id = ".$step_prerequisite;
	$res = Database::query($sql, __FILE__, __LINE__);
	$num_steps = Database::num_rows($res);
	if($num_steps == 0){
		$prereq_satisfied = 'N';
		return $prereq_satisfied;
	}
	while($row = Database::fetch_array($res)) {
		$sql_view = "SELECT status FROM $TBL_SCENARIO_ACTIVITY_VIEW WHERE step_id = ".$step_prerequisite." AND activity_id = ".$row['id']." AND user_id = ".api_get_user_id();
		$res_view = Database::query($sql_view, __FILE__, __LINE__);
		$num_view = Database::num_rows($res_view);
		if($num_view == 0){
			$prereq_satisfied = 'N';
			return $prereq_satisfied;
		}
		else {
			$status = Database::result($res_view, 0, 0);
			if($status != 'completed'){
				$prereq_satisfied = 'N';
				return $prereq_satisfied;
			}
		}
	}
	return $prereq_satisfied;
}
?>