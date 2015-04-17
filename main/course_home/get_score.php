<?php
// including the global Dokeos file
require_once '../inc/global.inc.php';
global $_course;
$tbl_scenario   = Database::get_course_table(TABLE_COURSE_SCENARIO,$_course['code']);
$tbl_scenario_items   = Database::get_course_table(TABLE_COURSE_SCENARIO_ITEMS,$_course['code']);
$TBL_EXERCICE_QUESTION = Database :: get_course_table(TABLE_QUIZ_TEST_QUESTION,$_course['code']);
$TBL_EXERCICES = Database :: get_course_table(TABLE_QUIZ_TEST,$_course['code']);
$TBL_QUESTIONS = Database :: get_course_table(TABLE_QUIZ_QUESTION,$_course['code']);
$TBL_LP_ITEM = Database :: get_course_table(TABLE_LP_ITEM,$_course['code']);
$TBL_WORK = Database :: get_course_table(TABLE_STUDENT_PUBLICATION,$_course['code']);

$type = $_GET['type'];
switch ($type) {
	case 'Quiz': // order items in scenario

		$quiz_id = $_GET['quiz_id'];	

		$sql = "SELECT SUM(ponderation) AS total_score FROM $TBL_EXERCICES quiz, $TBL_QUESTIONS qn, $TBL_EXERCICE_QUESTION relqn WHERE quiz.id = relqn.exercice_id AND relqn.question_id = qn.id AND quiz.id = ".$quiz_id;
		$res = Database::query($sql,__FILE__,__LINE__);
		$total_score = Database::result($res, 0, 0);
		echo $total_score;
	break;	
	case 'Module': // order items in scenario

		$module_id = $_GET['module_id'];	

		$sql = "SELECT SUM(max_score) AS total_score FROM $TBL_LP_ITEM WHERE lp_id = ".$module_id." AND item_type = 'quiz'";
		$res = Database::query($sql,__FILE__,__LINE__);
		$total_score = Database::result($res, 0, 0);
		if(empty($total_score)) {
			$total_score = 0;
		}
		echo $total_score;
	break;
	case 'Work': // order items in scenario

		$work_id = $_GET['work_id'];	

		$sql = "SELECT weight AS total_score FROM $TBL_WORK WHERE id = ".$work_id;
		$res = Database::query($sql,__FILE__,__LINE__);
		$total_score = Database::result($res, 0, 0);
		if(empty($total_score)) {
			$total_score = 0;
		}
		echo $total_score;
	break;
}

?>