<?php
$language_file = 'admin';
require ('../inc/global.inc.php');
require_once api_get_path(LIBRARY_PATH).'tracking.lib.php';
include_once api_get_path(LIBRARY_PATH).'course.lib.php';
require_once(api_get_path(LIBRARY_PATH).'usermanager.lib.php');
require_once(api_get_path(LIBRARY_PATH).'online.inc.php');
require 'functions.php';

$q = trim(strip_tags($_GET['term']));
$action = $_GET["action"];

$course_table 		= Database :: get_main_table(TABLE_MAIN_COURSE);

switch($action) {

	case search_courses :
		/*$sql = "SELECT * FROM $course_table WHERE title LIKE '".$q."%'";
		$res = Database::query($sql, __FILE__, __LINE__);
		while($row = Database::fetch_array($res)){
			$course_name = $row["title"];
			echo api_convert_encoding($course_name,'UTF-8',api_get_system_encoding())."\n";
		}*/
		$sql = "SELECT code as id, title as value FROM $course_table WHERE title LIKE '".trim(strip_tags($_GET['term']))."%'";
		Database::query("SET NAMES 'utf8'");
		$res = Database::query($sql, __FILE__, __LINE__);
		$num_rows = Database::num_rows($res);
		if($num_rows > 0){
		while($row = Database::fetch_array($res)){
			 //$row['value']='<span style="font-size:12px;">'.htmlentities(stripslashes($row['value'])).'</span>';
			 $row['value']=$row['value'];
			 $row['id']=$row['id'];
			 $row_set[] = $row;//build an array
				
		}
		}
		else {
			$row = array();
			$row['value'] = get_lang("NoMatchFound");
			$row['id'] = 0;
			$row_set[] = $row;
		}
		echo json_encode($row_set);
	break;
	case search_modules :
		$modules = array();
		$modules = get_modules_list('',0,0,$q);
		foreach($modules as $module) {
			list($code,$lp_id,$module_name) = split("@",$module);
			$row['value'] = $module_name;
			$row['id'] = $lp_id;
			$row_set[] = $row;
			//echo api_convert_encoding($module_name,'UTF-8',api_get_system_encoding())."\n";
		}
		//echo json_encode($row_set);
		$sql = "SELECT code as id, title as value FROM $course_table WHERE title LIKE '".trim(strip_tags($_GET['term']))."%'";
		Database::query("SET NAMES 'utf8'");
		$res = Database::query($sql, __FILE__, __LINE__);
		while($row = Database::fetch_array($res)){
			 $row['value']=$row['value'];
			 $row['id']=$row['id'];
			 $row_set[] = $row;//build an array
				
		}
		$cnt_rows = sizeof($row_set);
		if($cnt_rows == 0) {
			$row = array();
			$row['value'] = get_lang("NoMatchFound");
			$row['id'] = 0;
			$row_set[] = $row;
		}
		echo json_encode($row_set);
	break;
	case search_quizzes :
		$quizzes = array();
		$quizzes = get_quiz_list(0,0,$q);
		foreach($quizzes as $quiz) {
			list($code,$quiz_id,$quiz_name) = split("@",$quiz);
			$row['value'] = $quiz_name;
			$row['id'] = $quiz_id;
			$row_set[] = $row;
			//echo api_convert_encoding($quiz_name,'UTF-8',api_get_system_encoding())."\n";
		}
		//echo json_encode($row_set);
		$sql = "SELECT code as id, title as value FROM $course_table WHERE title LIKE '".trim(strip_tags($_GET['term']))."%'";
		Database::query("SET NAMES 'utf8'");
		$res = Database::query($sql, __FILE__, __LINE__);
		while($row = Database::fetch_array($res)){
			 $row['value']=$row['value'];
			 $row['id']=$row['id'];
			 $row_set[] = $row;//build an array
				
		}
		$cnt_rows = sizeof($row_set);
		if($cnt_rows == 0) {
			$row = array();
			$row['value'] = get_lang("NoMatchFound");
			$row['id'] = 0;
			$row_set[] = $row;
		}
		echo json_encode($row_set);
	break;
	case search_facetoface :
		$facetofaces = array();
		$facetofaces = get_facetoface_list($q);
		foreach($facetofaces as $facetoface) {
			list($code,$ff_id,$ff_name) = split("@",$facetoface);
			$row['value'] = $ff_name;
			$row['id'] = $ff_id;
			$row_set[] = $row;
			//echo api_convert_encoding($quiz_name,'UTF-8',api_get_system_encoding())."\n";
		}
		//echo json_encode($row_set);
		$sql = "SELECT code as id, title as value FROM $course_table WHERE title LIKE '".trim(strip_tags($_GET['term']))."%'";
		Database::query("SET NAMES 'utf8'");
		$res = Database::query($sql, __FILE__, __LINE__);
		while($row = Database::fetch_array($res)){
			 $row['value']=$row['value'];
			 $row['id']=$row['id'];
			 $row_set[] = $row;//build an array
				
		}
		$cnt_rows = sizeof($row_set);
		if($cnt_rows == 0) {
			$row = array();
			$row['value'] = get_lang("NoMatchFound");
			$row['id'] = 0;
			$row_set[] = $row;
		}
		echo json_encode($row_set);
	break;
	case search_learners :
		$learners = array();
		$learners = get_users_list(1,$q);
		foreach($learners as $user) {			
			//echo $user['lastname']."\n";
			$row['value']=$user['lastname'];
			$row['id']=$user['user_id'];
			$row_set[] = $row;
		}
		//echo json_encode($row_set);
		$sql = "SELECT code as id, title as value FROM $course_table WHERE title LIKE '".trim(strip_tags($_GET['term']))."%'";
		Database::query("SET NAMES 'utf8'");
		$res = Database::query($sql, __FILE__, __LINE__);
		while($row = Database::fetch_array($res)){
			 $row['value']=$row['value'];
			 $row['id']=$row['id'];
			 $row_set[] = $row;//build an array
				
		}
		$cnt_rows = sizeof($row_set);
		if($cnt_rows == 0) {
			$row = array();
			$row['value'] = get_lang("NoMatchFound");
			$row['id'] = 0;
			$row_set[] = $row;
		}
		echo json_encode($row_set);
	break;

}