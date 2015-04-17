<?php
$language_file[] = 'course_home';
$language_file[] = 'admin';
$use_anonymous = true;

require_once ('../inc/global.inc.php');
$pathStyleSheets = api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets');

$htmlHeadXtra[] = '<script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-1.4.2.min.js" type="text/javascript" language="javascript"></script>';
$htmlHeadXtra[] = '<link type="text/css" rel="stylesheet" media="print" href="'.api_get_path(WEB_PATH).'main/css/'.api_get_setting('stylesheets').'/print.css" />';

include_once api_get_path(LIBRARY_PATH) . 'course.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'tracking.lib.php';
require_once(api_get_path(LIBRARY_PATH).'usermanager.lib.php');
require_once(api_get_path(LIBRARY_PATH).'online.inc.php');
require 'functions.php';

api_block_anonymous_users();

/*if (!api_is_allowed_to_edit() && !api_is_coach() && !api_is_platform_admin(true)) {
	api_not_allowed(true);
}*/

if ($_GET['c'] == 'export') {
    if ($_GET['module'] == 'courses') {
        exportcourses();
        exit;
    }
    if ($_GET['module'] == 'modules') {
        exportmodules();
        exit;
    }
    if ($_GET['module'] == 'quizzes') {
        exportquizzes();
        exit;
    }
	if ($_GET['module'] == 'face2face') {
        exportfacetofaces();
        exit;
    }
    if ($_GET['module'] == 'learners') {
        exportlearners();
        exit;
    }
}
if ($_GET['c'] == 'print') {
    if ($_GET['module'] == 'courses') {
        printcourses();
    }
    if ($_GET['module'] == 'modules') {
        printmodules();
    }
    if ($_GET['module'] == 'quizzes') {
        printquizzes();
    }
	if ($_GET['module'] == 'face2face') {
        printfacetofaces();
    }
    if ($_GET['module'] == 'learners') {
        printlearners();
    }
    echo "<script type='text/javascript'>window.print();</script>";
    exit;
}

Display::display_responsive_reporting_header();
// Top actions bar
?>
<link rel="stylesheet" href="css/sprites.min.css" />
<link rel="stylesheet" href="css/responsive-tabs.css">	
<!--<link href="css/bootstrap-responsive.css" rel="stylesheet">-->
<link rel="stylesheet" href="css/custom-responsive.css" />
<link rel="stylesheet" href="css/stacktable.css" />
<link type="text/css" href="jquery-ui-1.8.2/themes/base/jquery.ui.autocomplete.css" rel="stylesheet" />
<link type="text/css" href="jquery-ui-1.8.2/themes/base/jquery.ui.theme.css" rel="stylesheet" />

<script type="text/javascript" src="jquery-ui-1.8.2/jquery-1.4.2.js"></script>
<script type="text/javascript" src="jquery-ui-1.8.2/ui/jquery.ui.core.js"></script>
<script type="text/javascript" src="jquery-ui-1.8.2/ui/jquery.ui.widget.js"></script>
<script type="text/javascript" src="jquery-ui-1.8.2/ui/jquery.ui.position.js"></script> 
<script type="text/javascript" src="jquery-ui-1.8.2/ui/jquery.ui.autocomplete.js"></script>
<script type="text/javascript" src="js/canvasjs.min.js"></script>

<!-- Content -->
<?php
$pathStyleSheets = api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets');
$course_code = 0;
$session_id = 0;
$user_id = api_get_user_id();
$course_search = '';

$user_info = api_get_user_info($user_id);
$user_details = api_get_user_info_from_username($user_info['username']);
$first_connection_date = Tracking :: get_first_connection_date($user_id);
$last_connection_date = Tracking :: get_last_connection_date($user_id, true);
$time_spent = get_time_spent($user_id, $course_code, $session_id, $course_search);

$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);

if($_GET['c']=='export'){
	if($_GET['module']=='learnersreport'){
		exportlearnersreport();
		exit;
	}
}

if($_GET['c']=='print'){
 	if($_GET['module']=='learnersreport'){
		printlearnersreport();
	}
	echo "<script type='text/javascript'>window.print();</script>";
	exit;
}

if(!empty($course_search)){

	$search_codes = get_search_coursecodes($course_search);		
}

if($course_code == '0'){
	
	$sql = "SELECT DISTINCT(course_code) FROM $course_user_table cru WHERE cru.user_id = '".$user_id."'";
	if(sizeof($search_codes) <> 0){
		$sql .= " AND course_code IN ('".implode(',',$search_codes)."')";
	}

	$res = Database::query($sql, __FILE__, __LINE__);
	while($row = Database::fetch_array($res)) {
		$course_codes[] = $row['course_code'];
	}
}
else {
	$course_codes[] = $course_code;
}

$progress = get_student_progress($user_id, $course_code, $session_id, $course_search);
//list($module_progress, $percentage) = split(" ",$progress);
//$mod_progress = round(($module_progress / sizeof($course_codes)),2);
$score = get_student_score($user_id, $course_code, $session_id, $course_search);
$quiz_score = get_student_quiz_score($user_id, $course_code, $session_id, $course_search);

if($user_details['active'] == 1) {
	$status = get_lang('Active');
}
elseif($user_details['active'] == 0) {
	$status = get_lang('Inactive');
}

if(empty($first_connection_date)) {
	$first_connection_date = '/';
}
if(empty($last_connection_date)) {
	$last_connection_date = '/';
}

if($score == '- %'){
	$score = 'n.a';
}
if($last_connection_date == '/') {
	$time_spent = 'n.a';
	$progress = 'n.a';
	$score = 'n.a';
	$quiz_score = 'n.a';
}

$sysdir_array = UserManager::get_user_picture_path_by_id($user_id,'system',false,true);
$sysdir = $sysdir_array['dir'];
$webdir_array = UserManager::get_user_picture_path_by_id($user_id,'web',false,true);
$webdir = $webdir_array['dir'];
$fullurl=$webdir.$webdir_array['file'];
$system_image_path=$sysdir.$webdir_array['file'];
list($width, $height, $type, $attr) = @getimagesize($system_image_path);
$resizing = (($height > 200) ? 'height="200"' : '');
$height += 30;
$width += 30;
$window_name = 'window'.uniqid('');
$onclick = $window_name."=window.open('".$fullurl."','".$window_name."','alwaysRaised=yes, alwaysLowered=no,alwaysOnTop=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=".$width.",height=".$height.",left=200,top=20'); return false;";

?>
<div id="dataDiv">
<h5><?php echo get_lang("IndividualReporting"); ?> : <?php echo Database::escape_string($user_info['lastname']).' '.Database::escape_string($user_info['firstname']); ?><h5>
<table>
	<thead>
		<tr>
			<th><?php echo get_lang("OverallInformation"); ?></th>
			<th><?php echo get_lang("AccessDetails"); ?></th>
			<th><?php 
			if (!empty ($user_info['mail'])) {
			echo Display :: encrypted_mailto_link($user_info['mail'], get_lang('SendMail'));
			}
			?></th>						
		</tr>
	</thead>
</table>

<table class="responsive large-only table-striped">
	<tbody>
		<tr>
			<td valign="top" width="5%"><?php
			echo '<a href="javascript: void(0);" onclick="'.$onclick.'" ><img src="'.$fullurl.'" '.$resizing.' alt="'.$alt.'"/></a>';
			?>
			</td>
			<td style="background-color:#FFF;">
			<!--<p align="right">Status :</p>
			<p align="right">Email :</p>
			<p align="right">First Connection :</p>
			<p align="right">Latest Connection :</p>
			<p align="right">Time spent in modules :</p>
			<p align="right">Score in Modules :</p>
			<p align="right">Progress in Modules :</p>
			<p align="right">Quizzes Score :</p>
			</td>
			<td>
			<p><?php echo $status; ?></p>
			<p><?php echo $user_info['mail']; ?></p>
			<p><?php echo $first_connection_date; ?></p>
			<p><?php echo $last_connection_date; ?></p>
			<p><?php echo $time_spent; ?></p>
			<p><?php echo $score; ?></p>
			<p><?php echo $progress; ?></p>
			<p><?php echo $quiz_score; ?></p>-->
				<table class="responsive large-only table-striped" >
					<tr><td><?php echo get_lang("Status"); ?> :</td><td><?php echo $status; ?></td></tr>
					<tr><td><?php echo get_lang("Email"); ?> :</td><td><?php echo wordwrap($user_info['mail'],5,"<br/>\n"); ?></td></tr>
					<tr><td><?php echo get_lang("FirstConnection"); ?> :</td><td><?php echo $first_connection_date; ?></td></tr>
					<tr><td><?php echo get_lang("LatestConnection"); ?> :</td><td><?php echo $last_connection_date; ?></td></tr>
					<tr><td><?php echo get_lang("TimeSpentInModules"); ?> :</td><td><?php echo $time_spent; ?></td></tr>
					<tr><td><?php echo get_lang("ScoreInModules"); ?> :</td><td><?php echo $score; ?></td></tr>
					<tr><td><?php echo get_lang("ProgressInModules"); ?> :</td><td><?php echo $progress; ?></td></tr>
					<tr><td><?php echo get_lang("QuizzesScore"); ?> :</td><td><?php echo $quiz_score; ?></td></tr>
				</table>
			</td>
		</tr>
	</tbody>
</table>
<?php

foreach($course_codes as $code) {
	$modules = array();
	$quizzes = array();
	$course_info = api_get_course_info($code);
	$valid = 10;
	$current_date = date('Y-m-d H:i:s',time());
	$track_online_table = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ONLINE);

	$query = "SELECT login_user_id,login_date FROM ".$track_online_table ." WHERE DATE_ADD(login_date,INTERVAL $valid MINUTE) >= '".$current_date."' AND course = '".$code."' ";
	$res = Database::query($query, __FILE__, __LINE__);
	$num_users_online = Database::num_rows($res);

	$t_lp = Database :: get_course_table(TABLE_LP_MAIN, $course_info['dbName']);	
	$TBL_LP_VIEW = Database :: get_course_table(TABLE_LP_VIEW, $course_info['dbName']);
	$table_quiz = Database :: get_course_table(TABLE_QUIZ_TEST, $course_info['dbName']);	
	$lp_item_table      = Database :: get_course_table(TABLE_LP_ITEM, $course_info['dbName']);
	$TBL_SCENARIO_STEPS = Database :: get_course_table(TABLE_SCENARIO_STEPS, $course_info['dbName']);	
	$TBL_SCENARIO_ACTIVITY = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY, $course_info['dbName']);
	$TBL_SCENARIO_ACTIVITY_VIEW = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY_VIEW, $course_info['dbName']);
	$TBL_FACE2FACE = Database :: get_course_table(TABLE_FACE_2_FACE, $course_info['dbName']);

	$sql_scenario = "SELECT * FROM $TBL_SCENARIO_STEPS WHERE session_id = 0 ORDER BY step_created_order";
	$res_scenario = Database::query($sql_scenario, __FILE__, __LINE__);
	$num_scenario = Database::num_rows($res_scenario);

	$sql_module = "SELECT id, name FROM $t_lp WHERE session_id = 0";
	if(!empty($session_id)) {
		$sql_module .= ' OR session_id = '.$session_id;
	}
	$res_module = Database::query($sql_module, __FILE__, __LINE__);
	while($module = Database::fetch_array($res_module, 'ASSOC')) {		
		$modules[] = $module;
	}	

	$sql_quiz = "SELECT id, title FROM $table_quiz WHERE session_id = 0";
	if(!empty($session_id)) {
		$sql_quiz .= ' OR session_id = '.$session_id;
	}
	$res_quiz = Database::query($sql_quiz, __FILE__, __LINE__);
	while($quiz = Database::fetch_array($res_quiz, 'ASSOC')) {		
		$quizzes[] = $quiz;
	}
	
	if(sizeof($modules) > 0 || sizeof($quizzes) > 0) {
		if(!empty($session_id)) {
			$tbl_session_course_user = Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
			$session_info = api_get_session_info($session_id);

			if ($session_id > 0) {
				$session_name = $session_info['name'];
				$session_coach_id = $session_info['id_coach'];
				// get coach of the course in the session
				$sql = 'SELECT id_user FROM ' . $tbl_session_course_user . '
								WHERE id_session=' . $session_id . '
								AND course_code = "' . Database :: escape_string($code) . '" AND status=2';
				$rs = Database::query($sql, __FILE__, __LINE__);
				$course_coachs = array();
				while ($row_coachs = Database::fetch_array($rs)) { $course_coachs[] = $row_coachs['id_user']; }
				if (!empty($course_coachs)) {
					$info_tutor_name = array();
					foreach ($course_coachs as $course_coach) {
						$coach_infos = UserManager :: get_user_info_by_id($course_coach);
						$info_tutor_name[] = api_get_person_name($coach_infos['firstname'], $coach_infos['lastname']);
					}
					$info_course['tutor_name'] = implode(",",$info_tutor_name);
				}
				elseif ($session_coach_id != 0) {
					$coach_infos = UserManager :: get_user_info_by_id($session_coach_id);
					$info_course['tutor_name'] = api_get_person_name($coach_infos['firstname'], $coach_infos['lastname']);
				}
			}
			echo '<h5><b>'.$session_name.' - </b>';
		}
		else {
			echo '<h5>';
		}
		if(!empty($course_search)){
			echo '<b>'.$course_info['name'].'</b></h5>';
		}
		else if($course_code == '0'){
			echo '<b>'.$course_info['name'].'</b></h5>';
		}
		else {
		?>
		<b><?php echo $course_info['name']; ?></b></h5>
		<?php
		}
	if(!empty($session_id)) {
	echo '<h5>'.get_lang("LoginToTheCourse"). ' : ' .$num_users_online." - ".get_lang("Tutor").' : '.api_convert_encoding(Database::escape_string($info_course['tutor_name']),'UTF-8',api_get_system_encoding()).'</h5>';
	}	
	else {
	?>
	<h5><?php echo get_lang("LoginToTheCourse"); ?> : <?php echo $num_users_online; ?> - <?php echo get_lang("Tutor"); ?> : <?php echo api_convert_encoding(Database::escape_string($course_info['titular']),'UTF-8',api_get_system_encoding()); ?></h5>
	<?php
	}	
	}

	$cnt_i = 0;
	$largest_row = 0;
	if($num_scenario > 0){
		echo '<h5>'.get_lang("ScenarioOverview").'</h5>';
		while($row_scenario = Database::fetch_array($res_scenario)){
			$step_id = $row_scenario['id'];
			$step_name = $row_scenario['step_name'];

			$cnt_j = 0;
			$foo[$cnt_i][$cnt_j] = $step_name;
			$cnt_j++;

			$sql_activity = "SELECT * FROM $TBL_SCENARIO_ACTIVITY WHERE step_id = ".$step_id." ORDER BY activity_created_order";
			$res_activity = Database::query($sql_activity, __FILE__, __LINE__);
			while($row_activity = Database::fetch_array($res_activity)) {
				$activity_id = $row_activity['id'];
				$activity_step_id = $row_activity['step_id'];
				$activity_type = $row_activity['activity_type'];
				$activity_ref = $row_activity['activity_ref'];
				$activity_created_order = $row_activity['activity_created_order'];
				$activity_name = $row_activity['activity_name'];

				$sql_view = "SELECT status, score FROM $TBL_SCENARIO_ACTIVITY_VIEW WHERE activity_id = ".$activity_id." AND user_id = ".$user_id." AND step_id = ".$step_id;
				$res_view = Database::query($sql_view, __FILE__, __LINE__);
				$scenario_status = Database::result($res_view, 0, 0);
				$scenario_score = Database::result($res_view, 0, 1);

				if($activity_type == 'face2face'){
					$sql_ff = "SELECT max_score FROM $TBL_FACE2FACE WHERE id = ".$activity_ref;
					$res_ff = Database::query($sql_ff, __FILE__, __LINE__);
					$activity_max_score = Database::result($res_ff,0,0);
				}
				else if($activity_type == 'quiz'){
					$activity_max_score = get_quiz_total_weight($activity_ref, $user_id, $code, $session_id);
				}
				else if($activity_type == 'module'){

					$sql_mod = "SELECT count(id) as count FROM $lp_item_table WHERE (item_type = 'quiz' OR item_type = 'sco') AND lp_id = ".$activity_ref;
					$result_have_quiz = Database::query($sql_mod, __FILE__, __LINE__);
					if (Database::num_rows($result_have_quiz) > 0 ) {
						$row = Database::fetch_array($result_have_quiz,'ASSOC');
						if (is_numeric($row['count']) && $row['count'] != 0) {
							$activity_max_score = 100;
						}
						else {
							$activity_max_score = 'n.a';
						}					
					}					
				}
				
				$foo[$cnt_i][$cnt_j] = $activity_name.'~'.$scenario_status.'~'.$scenario_score.'~'.$activity_type.'~'.$activity_max_score;
				$cnt_j++;
			}
			$cnt_i++;
			if($cnt_j > $largest_row){
				$largest_row = $cnt_j;
			}
		}

		$row = $largest_row;
		$col = $cnt_i;
		
		echo '<div style="width:auto;max-width:99%;border:0px solid #eaeaea;overflow:auto;">';		
		echo '<table border="1" class="table_scenario" cellpadding="3">';

		for( $i = 0; $i < $row; $i++ )
		{
			echo '<tr>';
			for( $j = 0; $j < $col; $j++ ) {  
					if($i == 0){
						echo '<td><b>'.$foo[$j][$i].'</b></td>';        
					}
					else {
					list($activity_name, $activity_status, $activity_score, $activity_type, $activity_max_score) = explode("~",$foo[$j][$i]);					

					if($activity_status == 'completed'){

						if($activity_type == 'face2face' || $activity_type == 'quiz' || $activity_type == 'module'){
							echo '<td style="background:#579D1C;color:#FFF;">'.$activity_name;
							if($activity_max_score == 'n.a'){
								echo '<div style="text-align:bottom;text-align:center;">'.$activity_max_score.'</div></td>'; 
							}
							else {
								echo '<div style="text-align:bottom;text-align:center;">'.$activity_score.'/'.$activity_max_score.'</div></td>'; 
							}
						}
						else {
							echo '<td style="background:#579D1C;color:#FFF;">'.$activity_name.'</td>'; 
						}
					
					}
					else {		
						
						echo '<td>'.$activity_name.'</td>'; 						
					}
					}
			}
			echo '</tr>';
		}

		echo '</table><br>';		
		echo '</div><br>';
	}

	if(sizeof($modules) > 0) {

	?>
	<table class="responsive large-only table-striped" id="ind_modules">
		<thead>
			<tr>
				<th><?php echo get_lang("Module"); ?></th>
				<th><?php echo get_lang("Time"); ?></th>
				<th><?php echo get_lang("Progress"); ?></th>
				<th><?php echo get_lang("Score"); ?></th>
				<th><?php echo get_lang("Screens"); ?></th>							
			</tr>
		</thead>
		<tbody>
	<?php
		foreach($modules as $module) {
			$module_time = specific_modules_time($user_id, $code, $module['id']);
			$module_progress = specific_modules_progress($user_id, $code, $module['id']);
			$module_score = Tracking :: get_avg_student_score($user_id, $code, array ($module['id']));
			//echo 'sc=='.$module_score = Tracking :: get_average_test_scorm_and_lp($user_id, $code);

			$sql_view = "SELECT max(view_count) AS count FROM $TBL_LP_VIEW WHERE lp_id = ".$module['id']." AND user_id = '" . $user_id . "'";
			$res_view = Database::query($sql_view, __FILE__, __LINE__);
			$view_count = Database::result($res_view,0,0);

			if (empty ($module_score)) {
				$module_score = 0;
			}

			if (empty ($module_progress)) {
				$module_progress = 0;
			}

			if($module_score != '0' && $module_score == "-"){
				$module_score = "n.a";
			}
			else {
				$module_score = $module_score." %";
			}

			echo "<tr>";
			if(!empty($course_search)){
				echo "<td>".$module['name']."</td>";
			}
			else if($course_code == '0'){
				echo "<td>".$module['name']."</td>";
			}
			else {
				echo "<td>".$module['name']."</td>";
					
			}
			echo "<td align='center'>".display_time_format($module_time)."</td>
					<td align='center'>".$module_progress." %</td>
					<td align='center'>".$module_score."</td>";
					if(!is_null($view_count)){
					echo "<td align='center'><a id='user_module_result' href='module_result.php?action=stats&course_code=".$code."&course=".$code."&user_id=".$user_id."&student_id=".$user_id."&lp_id=".$module["id"]."&sessionId=".$session_id."'><img src='$pathStyleSheets/images/action/reporting32.png'></a></td>";
					}
					else {
					echo "<td align='center'><img src='$pathStyleSheets/images/action/reporting32.png'></td>";
					}
				  echo "</tr>";	
		}
		echo "</tbody>
				</table>";
		echo "</br>";
	}
	if(sizeof($quizzes) > 0) {
		?>
		<table class="responsive large-only table-striped" id="ind_quiz">
		<thead>
			<tr>
				<th><?php echo get_lang("StandaloneQuiz"); ?></th>
				<th><?php echo get_lang("Attempts"); ?></th>
				<th><?php echo get_lang("Score"); ?></th>
				<th><?php echo get_lang("Time"); ?></th>
				<th><?php echo get_lang("Answers"); ?></th>							
			</tr>
		</thead>
		<tbody>
	<?php

		foreach($quizzes as $quiz) {
			$attempts = get_quiz_attempts($quiz['id'], $code, $user_id, '', $session_id);
			$quiz_score = get_score($quiz['id'], $user_id, $code, $session_id);
			$quiz_time = get_quiz_total_time($quiz['id'], $code, $user_id, $session_id);
			$exe_id = get_exe_id($user_id, $quiz['id'], $code, 2, 0, $session_id);
			if($quiz_score != '/') {
				$quiz_score = round(($quiz_score*100),2).' %';
			}

			echo "<tr>";
			if(!empty($course_search)){
				echo "<td>".$quiz['title']."</td>";
			}
			else if($course_code == '0'){
				echo "<td>".$quiz['title']."</td>";
			}
			else {				
				echo "<td>".$quiz['title']."</td>";
			}
			echo "	<td align='center'>".$attempts."</td>
					<td align='center'>".$quiz_score."</td>
					<td align='center'>".$quiz_time."</td>";
					if($exe_id == '0' || empty($exe_id)){
						echo "<td align='center'><img src='$pathStyleSheets/images/action/reporting32.png'></td>";
					}
					else {
					echo "<td align='center'><a id='user_individual_result' href='quiz_result.php?action=qualify&quiz_id=".$quiz["id"]."&course_code=".$code."&exe_id=".$exe_id."&user_id=".$user_id."&session_id=".$session_id."&from=usersquiz'><img src='$pathStyleSheets/images/action/reporting32.png'></a></td>";
					}
				  echo "</tr>";	
		}
		echo "</tbody>
				</table>";
}
}
?>
</br>
<p class="pull-right"><a href="index.php?userid=<?php echo $user_id; ?>&course_search=<?php echo $course_search; ?>&course_code=<?php echo $course_code; ?>&sessionId=<?php echo $session_id; ?>" id="individual_leaner_export"><?php echo get_lang("Export"); ?></a> / <a href="index.php?userid=<?php echo $user_id; ?>&course_search=<?php echo $course_search; ?>&course_code=<?php echo $course_code; ?>&sessionId=<?php echo $session_id; ?>" id="individual_leaner_print" ><?php echo get_lang("Print"); ?></a></p><br/>
</div>

<script src="js/scripts.js" type="text/javascript"></script>
<script src="js/stacktable.js" type="text/javascript"></script>
<script>
    $('.responsive').stacktable({myClass: 'stacktable small-only'});
</script>

<!-- End content -->
<?php
Display::display_responsive_reporting_footer();