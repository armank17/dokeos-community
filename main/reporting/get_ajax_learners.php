<?php
$language_file = 'admin';
require ('../inc/global.inc.php');
require_once api_get_path(LIBRARY_PATH).'tracking.lib.php';
include_once api_get_path(LIBRARY_PATH).'course.lib.php';
require_once(api_get_path(LIBRARY_PATH).'usermanager.lib.php');
require_once(api_get_path(LIBRARY_PATH).'online.inc.php');
require 'functions.php';

$user_table 		= Database :: get_main_table(TABLE_MAIN_USER);	
$course_table 		= Database :: get_main_table(TABLE_MAIN_COURSE);
$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
$session_table 		= Database :: get_main_table(TABLE_MAIN_SESSION);
$session_course_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
$session_course_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);

$page=1;
$limit = 20;
if(isset($_GET['page']) && $_GET['page']!=''){
	$page=$_GET['page'];
}
$page1 = ($page - 1) * $limit;

$user_id = $_GET['user_id'];
$users = array();
$limited_users = array();
$course_code = $_GET['course_code'];
$quiz_rank = $_GET['rank'];
$status = $_GET['status'];
$trainer_id = $_GET['trainer_id'];

$theme_color = get_theme_color();
$pathStyleSheets = api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets');

if(isset($_GET['course_search']) && !empty($_GET['course_search'])) {
	$course_search = $_GET['course_search'];
}
else {
	$course_search = '';
}
$course_search = checkUTF8($course_search);
if(isset($_GET['search']) && !empty($_GET['search'])) {
	$search = $_GET['search'];
}
else {
	$search = '';
}
$search = checkUTF8($search);
if(empty($course_code))
	$course_code = 0;
if($status != '0'){
	$status = 1;
}

if(api_is_allowed_to_edit() || api_is_allowed_to_create_course()){
	if($course_code == '0' && $_GET['sessionId'] == 0) {
		$users = get_users_list($status, $search, $trainer_id, $course_search);
	}
	else if($_GET['sessionId'] <> 0) {
		$sql = "SELECT u.user_id,u.lastname, u.firstname FROM $user_table u, $session_course_user_table srcu WHERE u.user_id = srcu.id_user AND u.active = ".$status." AND srcu. id_session = ".$_GET['sessionId'];
		if(!empty($search)){
			$sql .= " AND (u.lastname LIKE '%".$search."%' OR u.firstname LIKE '%".$search."%' OR u.username LIKE '%".$search."%')";
		}
		$sql .= " ORDER BY u.lastname";

		$res = Database::query($sql, __FILE__, __LINE__);
		while($user = Database::fetch_array($res, 'ASSOC')) {
			$users[] = $user;
		}
	}
	else {
		$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
		
		$sql = "SELECT u.user_id, u.lastname, u.firstname FROM $user_table u, $course_user_table cr WHERE u.user_id = cr.user_id AND cr.course_code = '".$course_code."' AND u.active = ".$status;
		if(!empty($search)){
			$sql .= " AND (u.lastname LIKE '%".$search."%' OR u.firstname LIKE '%".$search."%' OR u.username LIKE '%".$search."%')";
		}
		$sql .= " ORDER BY u.lastname";
		if(!empty($search)){
			Database::query("SET NAMES 'utf8'");
		}
		
		$res = Database::query($sql, __FILE__, __LINE__);
		while($user = Database::fetch_array($res, 'ASSOC')) {
			$users[] = $user;
		}
	}
}
else {
	$sql = "SELECT * FROM $user_table WHERE user_id = ".api_get_user_id();
	$res = Database::query($sql, __FILE__, __LINE__);
	while($row = Database::fetch_array($res)){
		$users[] = $row;
	}
}

if(!empty($search)) {
	if(api_is_allowed_to_edit()){
		$sql = "SELECT code FROM $course_table";
		$sql .= " WHERE code LIKE '".$search."' OR title LIKE '".$search."'";
	}
	else {
		$sql = "SELECT code, title FROM $course_table c, $course_user_table cr WHERE c.code = cr.course_code AND cr.user_id = ".api_get_user_id();
		if(api_is_allowed_to_create_course()){
			$sql .= " AND (cr.status = 1 OR cr. tutor_id = 1)";
			$sql .= " AND code LIKE '".$search."' OR title LIKE '".$search."'";
			$sql .= " UNION SELECT DISTINCT(c.code) as code, c.title FROM $course_table c, $session_table sess, $session_course_table sc WHERE c.code = sc.course_code AND sess.id = sc.id_session AND sess.id_coach = ".api_get_user_id();
		}
		else {
			$sql .= " UNION SELECT DISTINCT(c.code) as code, c.title FROM $course_table c, $session_course_user_table scr WHERE c.code = scr.course_code AND scr.id_user = ".api_get_user_id();
		}
		$sql .= " AND code LIKE '".$search."' OR title LIKE '".$search."'";
	}
	Database::query("SET NAMES 'utf8'");

	$res = Database::query($sql, __FILE__, __LINE__);
	$num_rows = Database::num_rows($res);
	if($num_rows <> 0) {			
		$search_course_code = Database::result($res,0,0);
		$course_code = $search_course_code;		

		$sql_user = "SELECT u.user_id, lastname, firstname FROM $user_table u,$course_user_table cr WHERE u.user_id = cr.user_id AND cr.course_code = '".$search_course_code."' AND u.active = ".$status." ORDER BY u.lastname";
		$res_user = Database::query($sql_user, __FILE__, __LINE__);
		while($user = Database::fetch_array($res_user, 'ASSOC')) {
			$users[] = $user;
		}
	}		
}


$limited_users = sort($users);
$unique_users = array();
if($quiz_rank <> 0){
	$limited_users = $users;
}
else {
$limited_users = get_limited_users($page1, $limit, $users);
}

$list_courses = get_course_list();
$sessions = get_sessions();
//$user_list = get_users_list();
//$limit_users = get_limited_users($page1, $limit, $user_list);	
?>

<form class="pull-right">
	<span class="pull-right">
	<input id="learner_search" name="learner_search" type="text" class="input-medium search-query" value="<?php echo $search; ?>">  
	<button id="learnerbtn_search" type="submit" class="btn"><?php echo get_lang('Search'); ?></button>		
	<span id="learner_reset"><button type="button" name="reset" class="btn"><?php echo get_lang("ResetFilter"); ?></button></span>
	</span></br></br>
	<span class="pull-right">
	<select name="course_list" id="course_list">
		<?php
		//$selected = $course_code == '0' ? ' selected="selected"' : '';
		echo '<option value="0" >' . api_convert_encoding(get_lang('SelectCourses'),'UTF-8',api_get_system_encoding())  . '</option>';
		foreach ($list_courses as $course) {   
			//if($course_code == $course['code']){ $selected = 'selected';} else { $selected = '';}
			//$selected = $course['code'] == $course_code ? ' selected="selected"' : '';
			if(!api_is_allowed_to_edit()){
				$title = api_convert_encoding(Database::escape_string($course['title']),'UTF-8',api_get_system_encoding());
			}
			else {
				$title = Database::escape_string($course['title']);
			}
			if($course_code == '0'){
			echo '<option value="' . $course['code'] . '"  '.$selected.'>' . $title . '</option>';			
			}
			else {
			echo '<option value="' . $course['code'] . '" '.$selected.'>' . $title . '</option>';	
			}
		}
		echo "\n";
		?>
	</select>
	<select name="session_list" id="session_list">
		<?php
		//$selected1 = $_GET['sessionId'] == '' ? ' selected="selected"' : '';
		echo '<option value="0">' . api_convert_encoding(Database::escape_string(get_lang('SelectSession')),'UTF-8',api_get_system_encoding()) . '</option>';
		foreach ($sessions as $session) {		
			//$selected = $session['id'] == $_GET['sessionId'] ? ' selected="selected"' : '';
			//if($session_id == $session['id']){ $selected = 'selected';} else { $selected = '';}
			echo '<option value="' . $session['id'] . '" '.$selected.'>' . api_convert_encoding(Database::escape_string($session['name']),'UTF-8',api_get_system_encoding()) . '</option>';
		}
		echo "\n";
		?>
	</select>
	<select name="learners_filter" id="learners_filter">
		<option value="-1"><?php echo api_convert_encoding(get_lang('SelectStatus'),'UTF-8',api_get_system_encoding()); ?></option>
		<option value = "1" selected><?php echo get_lang('ActiveLearners'); ?></option>
		<option value="0"><?php echo get_lang('InActiveLearners'); ?></option>                                        
	</select>
	<select name="quiz_ranking" id="quiz_ranking">
		<option value="0" <?php if($quiz_rank == '0') echo 'selected'; ?>><?php echo get_lang('QuizzesRanking'); ?></option>
		<option value="100" <?php if($quiz_rank == '100') echo 'selected'; ?>>100-91%</option>
		<option value="90" <?php if($quiz_rank == '90') echo 'selected'; ?>>90-81%</option>
		<option value="80" <?php if($quiz_rank == '80') echo 'selected'; ?>>80-71%</option>                                       
		<option value="70" <?php if($quiz_rank == '70') echo 'selected'; ?>>70-61%</option>
		<option value="60" <?php if($quiz_rank == '60') echo 'selected'; ?>>60-51%</option>
		<option value="50" <?php if($quiz_rank == '50') echo 'selected'; ?>>50-41%</option>
		<option value="40" <?php if($quiz_rank == '40') echo 'selected'; ?>>40-31%</option>
		<option value="30" <?php if($quiz_rank == '30') echo 'selected'; ?>>30-21%</option>
		<option value="20" <?php if($quiz_rank == '20') echo 'selected'; ?>>20-11%</option>
		<option value="10" <?php if($quiz_rank == '10') echo 'selected'; ?>>10-0%</option>
	</select>
	</span>
</form>
<b><?php echo get_lang('LearnersAverageValues'); ?></b>
<span id="learners_pages">
<?php
$unique_pages_arr = array();
foreach($users as $user) {
	if(!in_array($user['user_id'], $unique_pages_arr)){
		$unique_pages_arr[] = $user['user_id'];
	}
}
if(count($unique_pages_arr) > 0) {									
//	echo "<div class='pagination'><ul id='course_pagination'>";
	echo pagination($limit,$page,'index.php?tab=learners&page=',count($unique_pages_arr),'learners'); 
//	echo "</ul></div>";
}
?>
</span>
<div class="span11 chart_div" id="chartUserContainer" style="height:300px;display:none;"></div>
<table class="responsive large-only table-striped" id="learners">
	<thead>
		<tr>
			<th><?php echo api_convert_encoding(get_lang("LastName"),"UTF-8",api_get_system_encoding()); ?></th>
			<th><?php echo api_convert_encoding(get_lang("FirstName"),"UTF-8",api_get_system_encoding()); ?></th>
			<th><?php echo api_convert_encoding(get_lang("LatestConnection"),"UTF-8",api_get_system_encoding()); ?></th>
			<th><?php echo api_convert_encoding(get_lang("ModulesTime"),"UTF-8",api_get_system_encoding()); ?></th>
			<th><?php echo api_convert_encoding(get_lang("ModulesProgress"),"UTF-8",api_get_system_encoding()); ?></th>
			<th><?php echo api_convert_encoding(get_lang("ModulesScore"),"UTF-8",api_get_system_encoding()); ?></th>
			<th><?php echo api_convert_encoding(get_lang("QuizzesScore"),"UTF-8",api_get_system_encoding()); ?></th>
			<th class="print_invisible"><?php echo api_convert_encoding(get_lang("IndividualReporting"),"UTF-8",api_get_system_encoding()); ?></th>			
		</tr>
	</thead>
	<tbody>
	<?php
		$chart_user_arr = array();
		foreach ($limited_users as $user) {										
			if(!empty($user['user_id']) && !in_array($user['user_id'], $unique_arr) ) {
				$unique_arr[] = $user['user_id'];
				if($course_code == 0){
					$last_connection_date = Tracking :: get_last_connection_date($user['user_id'], true);
				}
				else {
					$last_connection_date = Tracking :: get_last_connection_date_on_the_course($user['user_id'], $course_code);				
				}
				if(empty($last_connection_date)) {
					$last_connection_date = '/';
				}
				$time_spent = get_time_spent($user['user_id'], $course_code, $_GET['sessionId'],$course_search);
				$progress = get_student_progress($user['user_id'], $course_code, $_GET['sessionId'], $course_search);
				$score = get_student_score($user['user_id'], $course_code, $_GET['sessionId'], $course_search);
				$quiz_score = get_student_quiz_score($user['user_id'], $course_code, $_GET['sessionId'], $course_search);
				if($score == '- %'){
					$score = '/';
				}
				if($last_connection_date == '/') {
					$time_spent = '/';
					$progress = '/';
					$score = '/';
					$quiz_score = '/';
				}
				list($quizscore,$percent) = split(" ",$quiz_score);

				$tmp_quiz_score = str_replace("%","",$quiz_score);
								
				echo '</br>';
				if($quiz_rank <> 0 && ($quizscore != '/') && ($quizscore > ($quiz_rank - 10)) && ($quizscore <= $quiz_rank)) {

					if(intval($tmp_quiz_score) > 0){
					$chart_user_arr[] = '{y: '.$tmp_quiz_score.', label: "'.strtoupper($user['lastname']).' '.$user['firstname'].'" }';
					}

			echo "<tr>
					<td>".checkUTF8(strtoupper($user['lastname']))."</td>
					<td>".checkUTF8($user['firstname'])."</td>
					<td align='center'>".api_convert_encoding($last_connection_date,'UTF-8',api_get_system_encoding())."</td>									
					<td align='center'>".$time_spent."</td>
					<td align='center'>".$progress."</td>
					<td align='center'>".$score."</td>
					<td align='center'>".$quiz_score."</td>
					<td class='print_invisible' align='center'><a id='inreport' href='individual_reporting.php?user_id=".$user['user_id']."&course_code=".$course_code."&rank=".$quiz_rank."&status=".$status."&trainer_id=".$trainer_id."&course_search=".$course_search."&search=".$search."&sessionId=".$_GET['sessionId']."'><img src='$pathStyleSheets/images/action/reporting32.png'></a></td>
				  </tr>";	
				}
				else if($quiz_rank == 0) {

					if(intval($tmp_quiz_score) > 0){
						$chart_user_arr[] = '{y: '.$tmp_quiz_score.', label: "'.strtoupper($user['lastname']).' '.$user['firstname'].'" }';
					}

					echo "<tr>
					<td>".checkUTF8(strtoupper($user['lastname']))."</td>
					<td>".checkUTF8($user['firstname'])."</td>
					<td align='center'>".api_convert_encoding($last_connection_date,'UTF-8',api_get_system_encoding())."</td>									
					<td align='center'>".$time_spent."</td>
					<td align='center'>".$progress."</td>
					<td align='center'>".$score."</td>
					<td align='center'>".$quiz_score."</td>
					<td class='print_invisible' align='center'><a id='inreport' href='individual_reporting.php?user_id=".$user['user_id']."&course_code=".$course_code."&rank=".$quiz_rank."&status=".$status."&trainer_id=".$trainer_id."&course_search=".$course_search."&search=".$search."&sessionId=".$_GET['sessionId']."'><img src='$pathStyleSheets/images/action/reporting32.png'></a></td>
				  </tr>";
				}
			}
		}
		
		$count_chart_user = sizeof($chart_user_arr);

		if($count_chart_user > 1 && $count_chart_user < 15 && $small_device != 'Y'){
		echo '<script>
		$("#chartUserContainer").html("");
		$("#chartUserContainer").css("display", "block");
		var chart = new CanvasJS.Chart("chartUserContainer", {
			
			axisX:{
				interval: 1,
				gridThickness: 0,
				labelFontSize: 10,				
				labelFontStyle: "normal",
				labelFontWeight: "normal",
				labelFontFamily: "Verdana",				
			},
			axisY2:{
				interlacedColor: "#F9F9F9",
				gridColor: "#F9F9F9",				
				title: "'.get_lang('UserVsQuizScore').'",
				titleFontColor: "#424242",
				titleFontWeight: "bold",
				indexOrientation: "vertical",
				maximum: 100,
			},
			toolTip:{
				enabled: false,   //enable here
			  },
			data: [
			{   				
				type: "bar",
                name: "chart",
				axisYType: "secondary",
				color: "'.$theme_color.'",
				indexLabel: "{y}  ",
				indexLabelPlacement: "outside",  
				indexLabelOrientation: "horizontal",
				indexLabelFontFamily: "Verdana",
				indexLabelFontColor: "#000",
				indexLabelFontSize: 14,
				indexLabelFontWeight: "bold",
				dataPoints: [
				
				'.implode(",",$chart_user_arr).'
				]
			},
			
			]
		});

chart.render();
		</script>';
		}
		else {
			echo '<script>
		$("#chartUserContainer").css("display", "none");
			</script>';
		}
		?>                                

	</tbody>
</table>
<br/>
<!--<span id="learner_reset"><button type="button" name="resetfilter" id="resetfilter"><?php echo get_lang("ResetFilter"); ?></button></span>-->
<span class="pull-right"><a href="#" id="learner_export"><?php echo get_lang("Export"); ?></a> / <a href="#" id="learner_print"><?php echo get_lang("Print"); ?></a></span></br>
