<?php
$language_file = 'admin';
require ('../inc/global.inc.php');
require_once api_get_path(LIBRARY_PATH).'tracking.lib.php';
include_once api_get_path(LIBRARY_PATH).'course.lib.php';
require_once(api_get_path(LIBRARY_PATH).'usermanager.lib.php');
require_once(api_get_path(LIBRARY_PATH).'online.inc.php');
require 'functions.php';
$pathStyleSheets = api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets');

$quiz_id = $_GET['quiz_id'];
$code = $_GET['course_code'];
$session_id = $_GET['sessionId'];
if(empty($session_id)){
	$session_id = 0;
}

if(isset($_GET['attempt_id'])) {
	$attempt_id = $_GET['attempt_id'];
}
else {
	$attempt_id = 2;
}

//$attempt_id = $_GET['attempt_id'];

if($attempt_id == 0){
	$selected_0 = 'selected';
}
else if($attempt_id == 1) {
	$selected_1 = 'selected';
}
else {
	$selected_2 = 'selected';
}

if($_GET['c']=='export'){
	exportlearnerslist($quiz_id,$code,$attempt_id,$session_id);
	exit;
}

if($_GET['c']=='print'){
	printlearnerslist($quiz_id,$code,$attempt_id,$session_id);
	echo "<script type='text/javascript'>window.print();</script>";
	exit;
}

$course_info = api_get_course_info($code);
$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
$session_course_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
$track_exercices = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
$table_quiz = Database :: get_course_table(TABLE_QUIZ_TEST, $course_info['dbName']);	
$users = array();

$sql = "SELECT title FROM $table_quiz WHERE id = ".$quiz_id;
$res = Database::query($sql, __FILE__, __LINE__);
$quiz_title = Database::result($res, 0, 0);

if($session_id == 0) {
	$sql = "SELECT user_id FROM $course_user_table cru WHERE cru.course_code = '".$code."' AND user_id <> 0";
	$res = Database::query($sql, __FILE__, __LINE__);
	while($row = Database::fetch_array($res)) {
		$users[] = $row['user_id'];
	}
}
else {
	$sql = "SELECT id_user FROM $session_course_user_table scru WHERE scru.course_code = '".$code."' AND id_session = ".$session_id;
	$res = Database::query($sql, __FILE__, __LINE__);
	while($row = Database::fetch_array($res)) {
		$users[] = $row['id_user'];
	}
}

if(!api_is_allowed_to_edit() && !api_is_allowed_to_create_course()){
	$users = array();
	$users[] = api_get_user_id();
}
?>
<div id="loaderDiv" style="display:none;"><img src="../img/ajaxloader.gif" /></div>
<div id="dataDiv">
<a class="pull-right" id="learners_back" href="index.php?quiz_id=<?php echo $quiz_id; ?>&course_code=<?php echo $code; ?>&user_id=<?php echo $_GET['user_id']; ?>&course_search=<?php echo $_GET['course_search']; ?>&search=<?php echo $_GET['search']; ?>&sessionId=<?php echo $_GET['sessionId']; ?>"><?php echo api_convert_encoding(get_lang("BackToQuiz"),"UTF-8",api_get_system_encoding()); ?></a></br>
<form class="pull-right">	
	<select id="list_attempt">                                        
		<?php
		echo '<option value="0" '.$selected_0.' >' . api_convert_encoding(get_lang("AllAttempts"),"UTF-8",api_get_system_encoding()) . '</option>';	
		echo '<option value="1" '.$selected_1.' >' . api_convert_encoding(get_lang("BestAttempts"),"UTF-8",api_get_system_encoding()) . '</option>';
		echo '<option value="2" '.$selected_2.' >' . api_convert_encoding(get_lang("LatestAttempt"),"UTF-8",api_get_system_encoding()) . '</option>';
		echo "\n";
		?>
	</select>
	<input type="hidden" name="hid_quizid" id="hid_quizid" value="<?php echo $quiz_id; ?>">
	<input type="hidden" name="hid_code" id="hid_code" value="<?php echo $code; ?>">
	<input type="hidden" name="hid_session_id" id="hid_session_id" value="<?php echo $session_id; ?>">
	
</form>   
<h4><?php echo get_lang("Quiz"); ?> : <?php echo api_convert_encoding($quiz_title,'UTF-8',api_get_system_encoding()); ?></h4>
<table name="list_learners" id="list_learners" class="responsive large-only table-striped">
	<thead>
		<tr>
			<th><?php echo api_convert_encoding(get_lang("LastName"),"UTF-8",api_get_system_encoding()); ?></th>
			<th><?php echo api_convert_encoding(get_lang("FirstName"),"UTF-8",api_get_system_encoding()); ?></th>
			<th><?php echo api_convert_encoding(get_lang("Score"),"UTF-8",api_get_system_encoding()); ?></th>
			<th><?php echo api_convert_encoding(get_lang("Time"),"UTF-8",api_get_system_encoding()); ?></th>
			<th><?php echo api_convert_encoding(get_lang("Attempts"),"UTF-8",api_get_system_encoding()); ?></th>
			<th><?php echo api_convert_encoding(get_lang("Answers"),"UTF-8",api_get_system_encoding()); ?></th>						
		</tr>
	</thead>
	<tbody>
		<?php
		if($attempt_id == 0) {

			if(api_is_allowed_to_edit() || api_is_allowed_to_create_course()){
			$sql = "SELECT * FROM $track_exercices WHERE exe_exo_id = ".$quiz_id." AND exe_cours_id = '".$code."' AND status <> 'incomplete' AND status <> 'left_incomplete' AND orig_lp_id = 0 AND session_id = ".$session_id;
			}
			else {
			$sql = "SELECT * FROM $track_exercices WHERE exe_exo_id = ".$quiz_id." AND exe_cours_id = '".$code."' AND status <> 'incomplete' AND status <> 'left_incomplete' AND orig_lp_id = 0 AND session_id = ".$session_id." AND exe_user_id = ".api_get_user_id();
			}
			$res = Database::query($sql, __FILE__, __LINE__);
			while($row = Database::fetch_array($res)) {
					$user_id = $row['exe_user_id'];
					$exe_id = $row['exe_id'];
					$user_info = api_get_user_info($user_id);
					$quiz_score = get_user_quiz_score($user_id, $quiz_id, $code, $attempt_id, $exe_id, $session_id);
					if($quiz_score == '0') {
						$quiz_score = $quiz_score.' %';
					}
					if($quiz_score != '/') {						
						$quiz_score = round(($quiz_score*100),2).' %';
					}
					$quiz_time = get_user_quiz_time($quiz_id, $code, $user_id, $attempt_id, $exe_id, $session_id);
					$attempts = get_quiz_attempts($quiz_id, $code, $user_id, $exe_id, $session_id);
					echo "<tr>
							<td>".api_convert_encoding($user_info['lastname'],'UTF-8',api_get_system_encoding())."</td>
							<td>".api_convert_encoding($user_info['firstname'],'UTF-8',api_get_system_encoding())."</td>
							<td align='center'>".$quiz_score."</td>
							<td align='center'>".$quiz_time."</td>
							<td align='center'>".$attempts."</td>";
							if($exe_id == '0' || empty($exe_id)){
								echo "<td align='center'>>></td>";
							}
							else {
							echo "<td align='center'><a id='show_result' href='quiz_result.php?action=qualify&quiz_id=".$quiz_id."&course_code=".$code."&exe_id=".$exe_id."&user_id=".$user_id."&session_id=".$session_id."'><img src='$pathStyleSheets/images/action/reporting32.png'></a></td>";
							}
						  echo "</tr>";				
			}
		}
		else {

			foreach($users as $user_id) {
				$user_info = api_get_user_info($user_id);
				$quiz_score = get_user_quiz_score($user_id, $quiz_id, $code, $attempt_id, '', $session_id);

				if($quiz_score != '/') {
					$quiz_score = round(($quiz_score*100),2).' %';
				}
				if($quiz_score == '0') {
					$quiz_score = $quiz_score.' %';
				}
				$quiz_time = get_user_quiz_time($quiz_id, $code, $user_id, $attempt_id, '', $session_id);
				$attempts = get_quiz_attempts($quiz_id, $code, $user_id, '', $session_id);
				$exe_id = get_exe_id($user_id, $quiz_id, $code, $attempt_id, '', $session_id);
				echo "<tr>
						<td>".api_convert_encoding($user_info['lastname'],'UTF-8',api_get_system_encoding())."</td>
						<td>".api_convert_encoding($user_info['firstname'],'UTF-8',api_get_system_encoding())."</td>
						<td align='center'>".$quiz_score."</td>
						<td align='center'>".$quiz_time."</td>
						<td align='center'>".$attempts."</td>";
						if($exe_id == '0' || empty($exe_id)){
							echo "<td align='center'><img src='$pathStyleSheets/images/action/reporting32.png'></td>";
						}
						else {
						echo "<td align='center'><a id='show_result' href='quiz_result.php?action=qualify&quiz_id=".$quiz_id."&course_code=".$code."&exe_id=".$exe_id."&user_id=".$user_id."&session_id=".$session_id."'><img src='$pathStyleSheets/images/action/reporting32.png'></a></td>";
						}
					  echo "</tr>";	
			}
		}
		?>		

	</tbody>
</table>
</br>
<p class="pull-right"><a href="index.php?quiz_id=<?php echo $quiz_id; ?>&course_code=<?php echo $code; ?>&sessionId=<?php echo $session_id; ?>&attempt_id=<?php echo $attempt_id; ?>" id="export_learners_list"><?php echo get_lang("Export"); ?></a> / <a href="index.php?quiz_id=<?php echo $quiz_id; ?>&course_code=<?php echo $code; ?>&sessionId=<?php echo $session_id; ?>&attempt_id=<?php echo $attempt_id; ?>" id="print_learners_list"><?php echo get_lang("Print"); ?></a></p></br>
</div>