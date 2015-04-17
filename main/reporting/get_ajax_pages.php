<?php
$language_file = 'admin';
require ('../inc/global.inc.php');
require_once api_get_path(LIBRARY_PATH).'tracking.lib.php';
include_once api_get_path(LIBRARY_PATH).'course.lib.php';
require_once(api_get_path(LIBRARY_PATH).'usermanager.lib.php');
require_once(api_get_path(LIBRARY_PATH).'online.inc.php');
require 'functions.php';

$pathStyleSheets = api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets');
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

$quiz_id = $_GET['quiz_id'];
$limit_quizzes = array();
$course_code = $_GET['course_code'];
$user_id = $_GET['user_id'];
$theme_color = get_theme_color();

if(empty($_GET['sessionId']))
	$session_id = 0;
else 
	$session_id = $_GET['sessionId'];
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
if($course_code == '0' && empty($_GET['quiz'])) {
	/*if(empty($_GET['quiztype'])) {
	$quizzes = get_quiz_list();
	}
	else {
		$quizzes = get_quiz_list($_GET['quiztype']);
	}*/
	$quizzes = get_quiz_list($_GET['quiztype'],$session_id,$search,$user_id,$course_search);
}
else {

	if(!empty($_GET['quiz'])) {
		list($code,$quiz_id) = split("@",$_GET['quiz']);
		$course_code = $code;
	}

	$info_course = CourseManager :: get_course_information($course_code);
	$table_quiz = Database :: get_course_table(TABLE_QUIZ_TEST, $info_course['db_name']);			
	$where_added = 0;
	$sql_quiz = "SELECT id, title FROM $table_quiz WHERE session_id = 0";
	if($quiztype <> 0){
		$sql_quiz .= " AND quiz_type = ".$quiztype;
	}
	if($session_id <> 0) {
		$sql_quiz .= " OR session_id = ".$session_id;
	}
	if(!empty($search)) {
		$sql_quiz .= " AND title LIKE '".$search."%'";
	}	
	$sql_quiz .= " ORDER BY title";

	$res_quiz = Database::query($sql_quiz, __FILE__, __LINE__);
	while($quiz = Database::fetch_array($res_quiz, 'ASSOC')) {
		$quizzes[] = $info_course['code'].'@'.$quiz['id'].'@'.$quiz['title'];
	}
}
$unique_quizzes = array();
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

		$res = Database::query($sql, __FILE__, __LINE__);
		$num_rows = Database::num_rows($res);
		if($num_rows <> 0) {			
			$search_course_code = Database::result($res,0,0);
			$course_code = $search_course_code;
			$info_course = CourseManager :: get_course_information($search_course_code);
			$table_quiz = Database :: get_course_table(TABLE_QUIZ_TEST, $info_course['db_name']);

			$sql_quiz = "SELECT id, title FROM $table_quiz WHERE active <> -1 ORDER BY title";
			$res_quiz = Database::query($sql_quiz, __FILE__, __LINE__);
			while($quiz = Database::fetch_array($res_quiz, 'ASSOC')) {
				$quizzes[] = $info_course['code'].'@'.$quiz['id'].'@'.$quiz['title'];
			}
		}		
	}		

if(sizeof($quizzes) > 0) {
$limit_quizzes = get_limited_quizzes($page1, $limit, $quizzes);
}
else {
$limit_quizzes = array();
}
$list_courses = get_course_list();
$quizzes_option = get_quiz_list();
$sessions = get_sessions();
//$limit_quizzes = get_limited_quizzes($page1, $limit, $quizzes);
?>

<form class="pull-right">
	<span class="pull-right">
	<input id="quiz_search" name="quiz_search" type="text" class="input-medium search-query">  
	<button id="quizbtn_search" type="submit" class="btn"><?php echo get_lang('Search'); ?></button>
	<span id="quiz_reset"><button type="button" name="reset" class="btn"><?php echo get_lang("ResetFilter"); ?></button></span>
	</span></br></br>
	<span class="pull-right">
	<select name="list_courses" id="list_courses">
		<?php
		echo '<option value="0">' . api_convert_encoding(get_lang('SelectCourses'),'UTF-8',api_get_system_encoding()) . '</option>';
		foreach ($list_courses as $course) {   
			//if($course_code == $course['code']){ $selected = 'selected'; } else { $selected = ''; }
			echo '<option value="' . $course['code'] . '" '.$selected.'>' . api_convert_encoding($course['title'],'UTF-8',api_get_system_encoding()) . '</option>';
		}
		echo "\n";
		?>
	</select>
	<select name="list_quiz" id="list_quiz">
		<?php
		echo '<option value="0">' . api_convert_encoding(get_lang('SelectQuiz'),'UTF-8',api_get_system_encoding()) . '</option>';
		foreach ($quizzes_option as $quiz) {
			list($code,$quiz_id,$quiz_title) = split("@",$quiz);                                            
			echo '<option value="' . $code.'@'.$quiz_id . '" >' . $quiz_title . '</option>';
		}
		echo "\n";
		?>
	</select>
	<select id="select_type" id="select_type">
		<option value="0"><?php echo api_convert_encoding(get_lang("SelectType"),"UTF-8",api_get_system_encoding()); ?></option>
		<option value="1"><?php echo api_convert_encoding(get_lang("SelfLearning"),"UTF-8",api_get_system_encoding()); ?></option>
		<option value="2"><?php echo api_convert_encoding(get_lang("ExamMode"),"UTF-8",api_get_system_encoding()); ?></option>                                        
	</select>
	<select id="list_session" id="list_session">
		<?php
		echo '<option value="0">' . api_convert_encoding(get_lang('SelectSession'),'UTF-8',api_get_system_encoding()) . '</option>';
		foreach ($sessions as $session) {		
			if($session_id == $session['id']){ $selected = 'selected'; } else { $selected = ''; }
			echo '<option value="' . $session['id'] . '" '.$selected.'>' . api_convert_encoding($session['name'],'UTF-8',api_get_system_encoding()) . '</option>';
		}
		echo "\n";
		?>
	</select>                         
	</span>
</form>
<b><?php echo get_lang("QuizzesAverageValues"); ?></b>
<span id="quiz_pages">
<?php

if(count($quizzes) > 0) {									
//	echo "<div class='pagination'><ul id='course_pagination'>";
	echo pagination($limit,$page,'index.php?tab=quizzes&page=',count($quizzes),'quizzes'); 
//	echo "</ul></div>";
}
?>
</span>
<div class="span11 chart_div" id="chartQuizContainer" style="height:300px;display:none;"></div>
<table class="responsive large-only table-striped" id="quizzes">
	<thead>
		<tr>
			<th><?php echo api_convert_encoding(get_lang("Quiz"),"UTF-8",api_get_system_encoding()); ?></th>
			<th><?php echo api_convert_encoding(get_lang("InCourse"),"UTF-8",api_get_system_encoding()); ?></th>
			<th><?php echo api_convert_encoding(get_lang("AverageScore"),"UTF-8",api_get_system_encoding()); ?></th>
			<th><?php echo api_convert_encoding(get_lang("Highest"),"UTF-8",api_get_system_encoding()); ?></th>
			<th><?php echo api_convert_encoding(get_lang("Lowest"),"UTF-8",api_get_system_encoding()); ?></th>
			<th><?php echo api_convert_encoding(get_lang("Participation"),"UTF-8",api_get_system_encoding()); ?></th>
			<th><?php echo api_convert_encoding(get_lang("AverageTime"),"UTF-8",api_get_system_encoding()); ?></th>
			<th class="print_invisible"><?php echo api_convert_encoding(get_lang("ListLearners"),"UTF-8",api_get_system_encoding()); ?></th>			
		</tr>
	</thead>
	<tbody>
		<?php
		$chart_quiz_arr = array();
		foreach ($limit_quizzes as $quiz) {
			list($code,$quiz_id,$quiz_name) = split("@",$quiz);
			if(!in_array($quiz_id, $unique_quizzes) ) {
				$unique_quizzes[] = $quiz_id;
			$course_name = get_course_title($code);
			$average_score = get_quiz_average_score($quiz_id, $code, $session_id);
			$highest_score = get_highest_score($quiz_id, $code, $session_id);
			$lowest_score = get_lowest_score($quiz_id, $code, $session_id);
			$no_participants = get_quiz_participants($quiz_id, $code, $session_id);
			$average_time = get_average_time($quiz_id, $code, $session_id);
			
			$tmp_average_score = str_replace("%","",$average_score);
			if(intval($tmp_average_score) > 0){
			$chart_quiz_arr[] = '{y: '.$tmp_average_score.', label: "'.$quiz_name.'" }';
			}	
			echo "<tr>";
			if($course_code == '0'){
				echo "<td>".$quiz_name."</td>
					<td>".api_convert_encoding(Database::escape_string($course_name),api_get_system_encoding(),'UTF-8')."</td>";

				/*echo "<td>".$quiz_name."</td>
					<td>".$course_name."</td>";*/
			}
			else {
					/*echo "<td>".$quiz_name."</td>
					<td>".$course_name."</td>";*/
					echo "<td>".api_convert_encoding(Database::escape_string($quiz_name),'UTF-8',api_get_system_encoding())."</td>
					<td>".$course_name."</td>";
			}
			echo "	<td align='center'>".$average_score."</td>													
					<td align='center'>".$highest_score."</td>
					<td align='center'>".$lowest_score."</td>
					<td align='center'>".$no_participants."</td>
					<td align='center'>".$average_time."</td>
					<td class='print_invisible' align='center'><a id='listlearners' href='list_learners.php?quiz_id=".$quiz_id."&course_code=".$code."&user_id=".$user_id."&course_search=".$course_search."&search=".$search."&sessionId=".$session_id."'><img src='$pathStyleSheets/images/action/list_learners.png'></a></td>
				  </tr>";
			}
		}
		$count_chart_quiz = sizeof($chart_quiz_arr);

		if($count_chart_quiz > 1 && $count_chart_quiz < 15 && $small_device != 'Y'){
		echo '<script>
		$("#chartQuizContainer").html("");
		$("#chartQuizContainer").css("display", "block");
		var chart = new CanvasJS.Chart("chartQuizContainer", {
			
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
				title: "'.get_lang('QuizVsQuizScore').'",
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
				
				'.implode(",",$chart_quiz_arr).'
				]
			},
			
			]
		});

chart.render();
		</script>';
		}
		else {
			echo '<script>
		$("#chartQuizContainer").css("display", "none");
			</script>';
		}
		?>                                        
	</tbody>
</table>
<br/>
<!--<span id="quiz_reset"><button type="button" name="test" id="test" value="test"><?php echo get_lang('ResetFilter'); ?></button></span>-->
<span class="pull-right"><a href="#" onclick="exportprintdata('export','quizzes');"><?php echo get_lang('Export'); ?></a> / <a href="#" onclick="exportprintdata('print','quizzes');"><?php echo get_lang('Print'); ?></a></span></br>