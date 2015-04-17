<?php
/* For licensing terms, see /dokeos_license.txt */
/**
 * Responses to AJAX calls
 */
require_once '../../main/inc/global.inc.php';

$TBL_SCENARIO_STEPS = Database :: get_course_table(TABLE_SCENARIO_STEPS);
$cols = $_GET['numCols'];
$step_color = $_GET['step_color'];
$hide_border = $_GET['hide_border'];

if($cols == 1){
	$step_prereq = "None";
}
else {
	$step_prereq = "None";
}

$sql = "INSERT INTO $TBL_SCENARIO_STEPS(step_icon, step_name, step_border, hide_border, step_prerequisite, step_created_order, session_id) VALUES('00.png','".get_lang('Step')." ".$cols."','#".$step_color."',".$hide_border.",'".$step_prereq."',".$cols.",".api_get_session_id().")";

Database::query($sql, __FILE__, __LINE__);
$step_id = Database::insert_id();
echo $step_id;
?>