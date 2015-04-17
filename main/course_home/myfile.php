<?php
/* For licensing terms, see /dokeos_license.txt */
/**
 * Responses to AJAX calls
 */

$language_file[] = 'course_home';

require_once '../../main/inc/global.inc.php';

$colIndex = $_GET['param'];
$TBL_SCENARIO_STEPS = Database :: get_course_table(TABLE_SCENARIO_STEPS);

$sql = "SELECT step_name FROM $TBL_SCENARIO_STEPS WHERE step_created_order = ".$colIndex." AND session_id = ".api_get_session_id();
$res = Database::query($sql, __FILE__, __LINE__);
$step_name = Database::result($res,0,0);

echo api_convert_encoding($step_name,'UTF-8',api_get_system_encoding());
?>