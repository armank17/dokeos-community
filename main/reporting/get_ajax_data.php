<?php
$language_file = 'admin';
require ('../inc/global.inc.php');
include_once api_get_path(LIBRARY_PATH).'course.lib.php';
require_once api_get_path(LIBRARY_PATH).'tracking.lib.php';
require 'functions.php';
$pathStyleSheets = api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets');
$action = $_GET['action'];

switch($action) {
	
	case get_session :
		$session_table 		= Database :: get_main_table(TABLE_MAIN_SESSION);
		$session_id = $_GET['session_id'];
		$sql = "SELECT id, name FROM $session_table WHERE visibility = 1";
		if(!empty($_GET['userid'])) {
		$sql .= " AND id_coach = ".$_GET['userid'];
		}
		$sql .= " ORDER BY name";
		$res = Database::query($sql, __FILE__, __LINE__);
		echo '<option value="0">' . api_convert_encoding(get_lang('SelectSession'),'UTF-8',api_get_system_encoding()) . '</option>';
		while($row = Database::fetch_array($res)) {
			if($row['id'] == $session_id){$selected = 'selected';}else {$selected = '';}
			echo "<option value=".$row['id']." ".$selected.">".api_convert_encoding(Database::escape_string($row['name']),'UTF-8',api_get_system_encoding())."</option>";
		}
	break;
	case get_courses :
		$course_table 		= Database :: get_main_table(TABLE_MAIN_COURSE);
		$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
		$session_table 		= Database :: get_main_table(TABLE_MAIN_SESSION);
		$session_course_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
		$session_course_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);	
		
		$theme_color = get_theme_color();

		$page=1;
		$limit = 20;
		if(isset($_GET['page']) && $_GET['page']!=''){
			$page=$_GET['page'];
		}
		$page1 = ($page - 1) * $limit;

		$search = checkUTF8(Database::escape_string($_GET['search']));

		if(isset($_GET['device']) && $_GET['device'] == 'small') {
			$small_device = 'Y';
		}

		if(empty($_GET['sessionId']))
			$session_id = 0;
		else 
			$session_id = $_GET['sessionId'];

		if(isset($_GET['search']) && !empty($_GET['search'])) {
			$sqlSearch_if = " WHERE (code LIKE '".$search."%' OR title LIKE '".$search."%' OR tutor_name LIKE '".$search."%')";
			$sqlSearch_else = " AND (c.code LIKE '".$search."%' OR c.title LIKE '".$search."%' OR c.tutor_name LIKE '".$search."%')";			
		}
		
		if(api_is_allowed_to_edit()){
			$sql = "SELECT code, title FROM $course_table c";	
			if($_GET['userid'] == 0 && $session_id == 0) {
				$sql .= $sqlSearch_if;
			}
			else {
				$sql_check = 'N';
				if(!empty($_GET['userid']) && !empty($_GET['sessionId'])){
					$sql .= " , $session_table sess, $session_course_table sc WHERE c.code = sc.course_code AND sess.id = sc.id_session AND sess.id_coach = " .$_GET['userid']." AND sc.id_session = ".$session_id;
					$sql_check = 'Y';
				}
				else if(!empty($_GET['userid']) && $sql_check == 'N'){
					$sql .= " , $course_user_table cr WHERE c.code = cr.course_code AND cr.user_id = ".$_GET['userid']." AND (cr.status = 1 OR cr.tutor_id = 1)";
				}
				else if(!empty($_GET['sessionId']) && $sql_check == 'N'){

					$sql .= " , $session_course_table scr WHERE c.code = scr.course_code AND scr.id_session = ".$session_id;
				}
				$sql .= $sqlSearch_else;				
			}
			$sql .= " ORDER BY c.title";
			$sql .= " LIMIT $page1, $limit";
		}
		else {
			$sql = "SELECT code, title FROM $course_table c";
			
			if(api_is_allowed_to_create_course()){
				if(empty($_GET['sessionId'])) {
					$sql .= " , $course_user_table cr WHERE c.code = cr.course_code AND cr.user_id = ".api_get_user_id()." AND (cr.status = 1 OR cr. tutor_id = 1)";
					$sql .= $sqlSearch_else;
					$sql .= " UNION SELECT DISTINCT(c.code) as code, c.title FROM $course_table c, $session_table sess, $session_course_table sc WHERE c.code = sc.course_code AND sess.id = sc.id_session AND sess.id_coach = ".api_get_user_id();
				}
				else {
					$sql .= " , $session_table sess, $session_course_table sc WHERE c.code = sc.course_code AND sess.id = sc.id_session AND sess.id_coach =  ".api_get_user_id()." AND sess.id = ".$_GET['sessionId'];
				}
			}
			else {
				if(empty($_GET['sessionId'])) {
					$sql .= " , $course_user_table cr WHERE c.code = cr.course_code AND cr.user_id = ".api_get_user_id();
					$sql .= $sqlSearch_else;
					$sql .= " UNION SELECT DISTINCT(c.code) as code, c.title FROM $course_table c, $session_course_user_table scr WHERE c.code = scr.course_code AND scr.id_user = ".api_get_user_id();
				}
				else {
					$sql .= " , $session_course_user_table scr WHERE c.code = scr.course_code AND scr.id_user = ".api_get_user_id()." AND scr.id_session = ".$_GET['sessionId'];
				}				
			}	
			$sql .= $sqlSearch_else;
			$sql .= " ORDER BY title";
			$sql .= " LIMIT $page1, $limit";
		}

		Database::query("SET NAMES 'utf8'");
		$res = Database::query($sql, __FILE__, __LINE__);
		$num_rows = Database::num_rows($res);
		echo ' <tbody>
				<thead>
					<tr>
                         <th>'.api_convert_encoding(get_lang("Courses"),"UTF-8",api_get_system_encoding()).'</th>
                         <th>'.api_convert_encoding(get_lang("Learners"),"UTF-8",api_get_system_encoding()).'</th>
                         <th>'.api_convert_encoding(get_lang("ModulesTime"),"UTF-8",api_get_system_encoding()).'</th>
                         <th>'.api_convert_encoding(get_lang("ModulesProgress"),"UTF-8",api_get_system_encoding()).'</th>
                         <th>'.api_convert_encoding(get_lang("ModulesScore"),"UTF-8",api_get_system_encoding()).'</th>
                         <th>'.api_convert_encoding(get_lang("QuizzesScore"),"UTF-8",api_get_system_encoding()).'</th>						
						 <th class="print_invisible">'.api_convert_encoding(get_lang("Action"),"UTF-8",api_get_system_encoding()).'</th>
                    </tr>
				</thead>';
		while($row = Database::fetch_array($res)) {
			$course_name = get_course_title($row['code']);
			if(strlen($course_name) > 15){
				$course_name = substr($course_name, 0, 15).'...';
			}
			$no_learners = total_numberof_learners($row['code'],$session_id);
			$module_time = total_modules_time($row['code'],$session_id);
			$module_progress = total_modules_progress($row['code'], $no_learners,$session_id);
			$module_score = total_modules_score($row['code'], $no_learners, $session_id);
			$quiz_score = total_quizzes_score($row['code'], $no_learners, $session_id);
			$course_title = Database::escape_string(api_convert_encoding($row['title'], 'UTF-8',api_get_system_encoding()));
			$tmp_quiz_score = str_replace("%","",$quiz_score);
			
			if(intval($tmp_quiz_score) > 0){
			$chart_code_arr[] = '{y: '.$tmp_quiz_score.', label: "'.$course_name.'" }';
			}
			echo "<tr>
									<td>".$row['title']."</td>
									<td align='center'>".$no_learners."</td>
									<td align='center'>".$module_time."</td>
									<td align='center'>".$module_progress." %</td>
									<td align='center'>".$module_score."</td>
									<td align='center'>".$quiz_score."</td>
									<td class='print_invisible' align='center'><a class='action_module' id='hid_".$row['code']."' href='#'><img src='$pathStyleSheets/images/action/scorm_32.png'></a></td>
								  </tr>";
		}
		echo '</tbody>';
		$count_chart_code = sizeof($chart_code_arr);
		if($count_chart_code > 1 && $count_chart_code < 15 && $small_device != 'Y'){
		echo '<script>
		$("#chartContainer").html("");
		$("#chartContainer").css("display", "block");
		var chart = new CanvasJS.Chart("chartContainer", {
			
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
				title: "'.api_convert_encoding(get_lang('CourseVsQuizScore'),'UTF-8',api_get_system_encoding()).'",
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
				indexLabelFontColor: "#CCC",
				indexLabelFontSize: 14,
				indexLabelFontWeight: "bold",
				dataPoints: [
				
				'.implode(",",$chart_code_arr).'
				]
			},
			
			]
		});

chart.render();
		</script>';
		}
		else {
			echo '<script>
		$("#chartContainer").css("display", "none");
			</script>';
		}
	break;
	case get_pages :
		$course_table 		= Database :: get_main_table(TABLE_MAIN_COURSE);
		$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
		$session_table 		= Database :: get_main_table(TABLE_MAIN_SESSION);
		$session_course_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
		$session_course_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);	
		
		

		$search = checkUTF8(Database::escape_string($_GET['search']));

		if(empty($_GET['sessionId']))
			$session_id = 0;
		else 
			$session_id = $_GET['sessionId'];

		if(isset($_GET['search']) && !empty($_GET['search'])) {
			$sqlSearch_if = " WHERE (code LIKE '".$search."%' OR title LIKE '".$search."%' OR tutor_name LIKE '".$search."%')";
			$sqlSearch_else = " AND (c.code LIKE '".$search."%' OR c.title LIKE '".$search."%' OR c.tutor_name LIKE '".$search."%')";			
		}
		
		if(api_is_allowed_to_edit()){
			$sql = "SELECT code, title FROM $course_table c";	
			if($_GET['userid'] == 0 && $session_id == 0) {
				$sql .= $sqlSearch_if;
			}
			else {
				$sql_check = 'N';
				if(!empty($_GET['userid']) && !empty($_GET['sessionId'])){
					$sql .= " , $session_table sess, $session_course_table sc WHERE c.code = sc.course_code AND sess.id = sc.id_session AND sess.id_coach = " .$_GET['userid']." AND sc.id_session = ".$session_id;
					$sql_check = 'Y';
				}
				else if(!empty($_GET['userid']) && $sql_check == 'N'){
					$sql .= " , $course_user_table cr WHERE c.code = cr.course_code AND cr.user_id = ".$_GET['userid']." AND (cr.status = 1 OR cr.tutor_id = 1)";
				}
				else if(!empty($_GET['sessionId']) && $sql_check == 'N'){

					$sql .= " , $session_course_table scr WHERE c.code = scr.course_code AND scr.id_session = ".$session_id;
				}
				$sql .= $sqlSearch_else;				
			}
			$sql .= " ORDER BY c.title";

		}
		else {
			$sql = "SELECT code, title FROM $course_table c";
			
			if(api_is_allowed_to_create_course()){
				if(empty($_GET['sessionId'])) {
					$sql .= " , $course_user_table cr WHERE c.code = cr.course_code AND cr.user_id = ".api_get_user_id()." AND (cr.status = 1 OR cr. tutor_id = 1)";
					$sql .= $sqlSearch_else;
					$sql .= " UNION SELECT DISTINCT(c.code) as code, c.title FROM $course_table c, $session_table sess, $session_course_table sc WHERE c.code = sc.course_code AND sess.id = sc.id_session AND sess.id_coach = ".api_get_user_id();
				}
				else {
					$sql .= " , $session_table sess, $session_course_table sc WHERE c.code = sc.course_code AND sess.id = sc.id_session AND sess.id_coach =  ".api_get_user_id()." AND sess.id = ".$_GET['sessionId'];
				}
			}
			else {
				if(empty($_GET['sessionId'])) {
					$sql .= " , $course_user_table cr WHERE c.code = cr.course_code AND cr.user_id = ".api_get_user_id();
					$sql .= $sqlSearch_else;
					$sql .= " UNION SELECT DISTINCT(c.code) as code, c.title FROM $course_table c, $session_course_user_table scr WHERE c.code = scr.course_code AND scr.id_user = ".api_get_user_id();
				}
				else {
					$sql .= " , $session_course_user_table scr WHERE c.code = scr.course_code AND scr.id_user = ".api_get_user_id()." AND scr.id_session = ".$_GET['sessionId'];
				}				
			}	
			$sql .= $sqlSearch_else;
			$sql .= " ORDER BY title";

		}

		Database::query("SET NAMES 'utf8'");
		$res = Database::query($sql, __FILE__, __LINE__);
		$num_rows = Database::num_rows($res);		
		
		$limit = 20;
		if(isset($_GET['page']) && $_GET['page']!=''){
			$page=$_GET['page'];
		}
		else {
			$page = 1;
		}
		$page1 = ($page - 1) * $limit;

		if($num_rows > 1) {
			//echo "<div class='pagination'><ul id='course_pagination'>";
			echo pagination($limit,$page,'index.php?tab=courses&page=',$num_rows,'courses'); 
			//echo "</ul></div>";
		}
	break;
	case get_modules :
		$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
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
		$modules = array();
		$course_code = $_GET['course_code'];
		$user_id = $_GET['userid'];

		if(isset($_GET['device']) && $_GET['device'] == 'small') {
			$small_device = 'Y';
		}

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
		if($course_code == '0') {
			$modules = get_modules_list($course_search,$user_id,$session_id,$search);
		}
		else {

			$info_course = CourseManager :: get_course_information($course_code);
			$t_lp = Database :: get_course_table(TABLE_LP_MAIN, $info_course['db_name']);

			$sql_module = "SELECT id,name FROM $t_lp";
			if(!empty($search)){
			$sql_module .= " WHERE name LIKE '".$search."%'";		
			}
			$sql_module .= " ORDER BY name";
			Database::query("SET NAMES 'utf8'");
			$res_module = Database::query($sql_module, __FILE__, __LINE__);
			while($module = Database::fetch_array($res_module, 'ASSOC')) {
				$modules[] = $info_course['code'].'@'.$module['id'].'@'.$module['name'];
			}
		}

		$unique_modules = array();	
		
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
				$info_course = CourseManager :: get_course_information($search_course_code);
				$t_lp = Database :: get_course_table(TABLE_LP_MAIN, $info_course['db_name']);

				$sql_module = "SELECT id,name FROM $t_lp ORDER BY name";
				$res_module = Database::query($sql_module, __FILE__, __LINE__);
				while($module = Database::fetch_array($res_module, 'ASSOC')) {
					$modules[] = $info_course['code'].'@'.$module['id'].'@'.$module['name'];
				}
			}		
		}
		
		$limit_modules = get_limited_modules($page1, $limit, $modules);
		$chart_module_arr = array();
	//	$sql .= " LIMIT $page1, $limit";		
		echo '<tbody>
				<thead>
					<tr>
                         <th>'.api_convert_encoding(get_lang("Modules"),"UTF-8",api_get_system_encoding()).'</th>
                         <th>'.api_convert_encoding(get_lang("InCourse"),"UTF-8",api_get_system_encoding()).'</th>
                         <th>'.api_convert_encoding(get_lang("Time"),"UTF-8",api_get_system_encoding()).'</th>
                         <th>'.api_convert_encoding(get_lang("Progress"),"UTF-8",api_get_system_encoding()).'</th>
                         <th>'.api_convert_encoding(get_lang("Score"),"UTF-8",api_get_system_encoding()).'</th>
						 <th class="print_invisible">'.api_convert_encoding(get_lang("Action"),"UTF-8",api_get_system_encoding()).'</th>
                    </tr>
				</thead>';
		foreach($limit_modules as $module) {	

			list($code,$lp_id,$module_name) = split("@",$module);

			//$module_name = api_convert_encoding($module_name,api_get_system_encoding(),'UTF-8');
			$course_name = get_course_title($code);		
			
			//$course_name = get_course_title($code);
			$total_learners = total_numberof_learners($code);

			$course_module_time = get_module_time($code, $lp_id);
			$course_module_progress = get_module_progress($code, $lp_id, $total_learners);
			$course_module_score = get_module_score($code, $lp_id, $total_learners);
			$tmp_module_score = str_replace("%","",$course_module_score);
			if(intval($tmp_module_score) > 0){
			$chart_module_arr[] = '{y: '.$tmp_module_score.', label: "'.$module_name.'" }';
			}
			echo "<tr>
					<td>".$module_name."</td>
					<td>".$course_name."</td>
					<td align='center'>".$course_module_time."</td>													
					<td align='center'>".$course_module_progress."</td>
					<td align='center'>".$course_module_score."</td>
					<td class='print_invisible' align='center'><a id='module_learners' href='module_learners.php?lp_id=".$lp_id."&course_code=".$code."&course_search=".$course_search."&search=".$search."&sessionId=".$session_id."'><img src='$pathStyleSheets/images/action/list_learners.png'/></a></td>
				  </tr>";	
			
		}	
		$count_chart_module = sizeof($chart_module_arr);

		if($count_chart_module > 1 && $count_chart_module < 15 && $small_device != 'Y'){
		echo '<script>
		$("#chartModuleContainer").html("");
		$("#chartModuleContainer").css("display", "block");
		var chart = new CanvasJS.Chart("chartModuleContainer", {
			
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
				title: "'.api_convert_encoding(get_lang('ModuleVsQuizScore'),'UTF-8',api_get_system_encoding()).'",
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
				indexLabelFontColor: "#CCC",
				indexLabelFontSize: 14,
				indexLabelFontWeight: "bold",
				dataPoints: [
				
				'.implode(",",$chart_module_arr).'
				]
			},
			
			]
		});

chart.render();
		</script>';
		}
		else {
			echo '<script>
		$("#chartModuleContainer").css("display", "none");
			</script>';
		}
		echo '</tbody>';
	break;
	case get_module_pages :
		$course_table 		= Database :: get_main_table(TABLE_MAIN_COURSE);
		$course_code = $_GET['course_code'];
		$modules = array();
		$course_code = $_GET['course_code'];
		$user_id = $_GET['userid'];
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
		if($course_code == '0') {
			$modules = get_modules_list($course_search,$user_id,$session_id,$search);
		}
		else {

			$info_course = CourseManager :: get_course_information($course_code);
			$t_lp = Database :: get_course_table(TABLE_LP_MAIN, $info_course['db_name']);

			$sql_module = "SELECT id,name FROM $t_lp";
			if(!empty($search)){
			$sql_module .= " WHERE name LIKE '".$search."%'";		
			}
			$sql_module .= " ORDER BY name";
			Database::query("SET NAMES 'utf8'");
			$res_module = Database::query($sql_module, __FILE__, __LINE__);
			while($module = Database::fetch_array($res_module, 'ASSOC')) {
				$modules[] = $info_course['code'].'@'.$module['id'].'@'.$module['name'];
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
				$info_course = CourseManager :: get_course_information($search_course_code);
				$t_lp = Database :: get_course_table(TABLE_LP_MAIN, $info_course['db_name']);

				$sql_module = "SELECT id,name FROM $t_lp ORDER BY name";
				$res_module = Database::query($sql_module, __FILE__, __LINE__);
				while($module = Database::fetch_array($res_module, 'ASSOC')) {
					$modules[] = $info_course['code'].'@'.$module['id'].'@'.$module['name'];
				}
			}		
		}
		
		$limit = 20;
		if(isset($_GET['page']) && $_GET['page']!=''){
			$page=$_GET['page'];
		}
		else {
			$page = 1;
		}
		$page1 = ($page - 1) * $limit;

		if(sizeof($modules) > 1) {
			//echo "<div class='pagination'><ul id='course_pagination'>";
			echo pagination($limit,$page,'index.php?tab=modules&page=',sizeof($modules),'modules'); 
			//echo "</ul></div>";
		}
	break;
	case get_quizzes :
		$course_table 		= Database :: get_main_table(TABLE_MAIN_COURSE);
		$course_user_table 	= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
		$session_table 		= Database :: get_main_table(TABLE_MAIN_SESSION);
		$session_course_table 	= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
		$session_course_user_table = Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);

		$page=1;
		$limit = 20;
		if(isset($_GET['page']) && $_GET['page']!=''){
			$page=$_GET['page'];
		}
		$page1 = ($page - 1) * $limit;		
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
			$sql_quiz = "SELECT id, title FROM $table_quiz WHERE session_id = 0 AND active <> -1 ";
			if(!empty($_GET['quiz'])) {
				$sql_quiz .= " AND id = ".$quiz_id;
			}
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

			Database::query("SET NAMES 'utf8'");
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
			Database::query("SET NAMES 'utf8'");

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
		$chart_quiz_arr = array();
	//	$sql .= " LIMIT $page1, $limit";		
		echo '<tbody>
				<thead>
					<tr>
                        <th>'.api_convert_encoding(get_lang("Quiz"),"UTF-8",api_get_system_encoding()).'</th>
						<th>'.api_convert_encoding(get_lang("InCourse"),"UTF-8",api_get_system_encoding()).'</th>
						<th>'.api_convert_encoding(get_lang("AverageScore"),"UTF-8",api_get_system_encoding()).'</th>
						<th>'.api_convert_encoding(get_lang("Highest"),"UTF-8",api_get_system_encoding()).'</th>
						<th>'.api_convert_encoding(get_lang("Lowest"),"UTF-8",api_get_system_encoding()).'</th>
						<th>'.api_convert_encoding(get_lang("Participation"),"UTF-8",api_get_system_encoding()).'</th>
						<th>'.api_convert_encoding(get_lang("AverageTime"),"UTF-8",api_get_system_encoding()).'</th>
						<th class="print_invisible">'.api_convert_encoding(get_lang("ListLearners"),"UTF-8",api_get_system_encoding()).'</th>                         					
                    </tr>
				</thead>';
		foreach($limit_quizzes as $quiz) {				
			list($code,$quiz_id,$quiz_name) = split("@",$quiz);
			
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
			echo "<tr>
					<td>".$quiz_name."</td>
					<td>".$course_name."</td>
					<td align='center'>".$average_score."</td>													
					<td align='center'>".$highest_score."</td>
					<td align='center'>".$lowest_score."</td>
					<td align='center'>".$no_participants."</td>
					<td align='center'>".$average_time."</td>
					<td class='print_invisible' align='center'><a id='listlearners' href='list_learners.php?quiz_id=".$quiz_id."&course_code=".$code."&user_id=".$user_id."&course_search=".$course_search."&search=".$search."&sessionId=".$session_id."'><img src='$pathStyleSheets/images/action/list_learners.png'></a></td>
				  </tr>";	
			
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
				title: "'.api_convert_encoding(get_lang('QuizVsQuizScore'),'UTF-8',api_get_system_encoding()).'",
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
				indexLabelFontColor: "#CCC",
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
		echo '</tbody>';
	break;
	case get_quiz_session :
		$session_table 		= Database :: get_main_table(TABLE_MAIN_SESSION);
		$session_course_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);

		$course_code = $_GET['course_code'];
		if(empty($course_code))
			$course_code = 0;
		if($course_code == '0') {
			$sql = "SELECT id, name FROM $session_table ORDER BY name";
			$res = Database::query($sql, __FILE__, __LINE__);
			echo '<option value="0">' . api_convert_encoding(get_lang('SelectSession'),'UTF-8',api_get_system_encoding()) . '</option>';
			while($row = Database::fetch_array($res)) {
				echo "<option value=".$row['id'].">".api_convert_encoding(Database::escape_string($row['name']),'UTF-8',api_get_system_encoding())."</option>";
			}
		}
		else {
			$sql = "SELECT s.id, s.name FROM $session_table s, $session_course_table sc WHERE s.id = sc.id_session AND sc.course_code = '".$course_code."'";
			$res = Database::query($sql, __FILE__, __LINE__);
			echo '<option value="0">' . api_convert_encoding(get_lang('SelectSession'),'UTF-8',api_get_system_encoding()) . '</option>';
			while($row = Database::fetch_array($res)) {
				echo "<option value=".$row['id'].">".api_convert_encoding(Database::escape_string($row['name']),'UTF-8',api_get_system_encoding())."</option>";
			}
		}
	break;
	case get_quiz_list :
		$course_code = $_GET['course_code'];
		
		$info_course = CourseManager :: get_course_information($course_code);
		$table_quiz = Database :: get_course_table(TABLE_QUIZ_TEST, $info_course['db_name']);	
		if(empty($course_code))
			$course_code = 0;
		if($course_code == '0') {

			$quizzes = get_quiz_list();
			echo '<option value="0">' . api_convert_encoding(get_lang('SelectQuiz'),'UTF-8',api_get_system_encoding()) . '</option>';
			foreach($quizzes as $quiz){
				list($code,$quiz_id,$quiz_title) = split("@",$quiz);
				echo "<option value=".$code.'@'.$quiz_id.">".$quiz_title."</option>";
			}
		}
		else {
			$sql_quiz = "SELECT id, title FROM $table_quiz ORDER BY title";
			$res_quiz = Database::query($sql_quiz, __FILE__, __LINE__);		
			echo '<option value="0">' . api_convert_encoding(get_lang('SelectQuiz'),'UTF-8',api_get_system_encoding()) . '</option>';
			while($row = Database::fetch_array($res_quiz)) {
				echo "<option value=".$info_course['code'].'@'.$row['id'].">".api_convert_encoding(Database::escape_string($row['title']),'UTF-8',api_get_system_encoding())."</option>";
			}
		}
	break;

	case get_quiz_type :
		$course_code = $_GET['course_code'];
		if(!empty($_GET['quiz'])) {
			list($code,$quiz_id) = split("@",$_GET['quiz']);
			$course_code = $code;
		}
		if(empty($course_code)) {
			$course_code = 0;

			echo '<option value="0">'.api_convert_encoding(get_lang('SelectType'),'UTF-8',api_get_system_encoding()).'</option>';
			echo '<option value="1">'.get_lang('SelfLearning').'</option>';
			echo '<option value="2">'.get_lang('ExamMode').'</option>';
		}
		else {
			$info_course = CourseManager :: get_course_information($course_code);
			$table_quiz = Database :: get_course_table(TABLE_QUIZ_TEST, $info_course['db_name']);		

			$sql_quiz = "SELECT DISTINCT(quiz_type) FROM $table_quiz";
			if(!empty($_GET['quiz'])) {
				$sql_quiz .= " WHERE id = ".$quiz_id;
			}
			//$sql_quiz .= " ORDER BY title";

			$res_quiz = Database::query($sql_quiz, __FILE__, __LINE__);
			echo '<option value="0">' . api_convert_encoding(get_lang('SelectType'),'UTF-8',api_get_system_encoding()) . '</option>';
			while($row = Database::fetch_array($res_quiz)) {
				if($row['quiz_type'] == 1) {
				echo "<option value=".$row['quiz_type'].">".get_lang('SelfLearning')."</option>";
				}
				else {
				echo "<option value=".$row['quiz_type'].">".get_lang('ExamMode')."</option>";
				}
			}
		}
	break;
	case get_quiz_pages :	
		$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
		$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
		$session_table 		= Database :: get_main_table(TABLE_MAIN_SESSION);
		$session_course_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
		$session_course_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);

		$course_code = $_GET['course_code'];
		$quizzes = array();		

		$page=1;
		$limit = 20;
		if(isset($_GET['page']) && $_GET['page']!=''){
			$page=$_GET['page'];
		}
		$page1 = ($page - 1) * $limit;		
		$quizzes = array();
		
		$course_code = $_GET['course_code'];
		$user_id = $_GET['user_id'];
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
			if(!empty($_GET['quiz'])) {
				$sql_quiz .= " AND id = ".$quiz_id;
			}
			if($quiztype <> 0){
				$sql_quiz .= " AND quiz_type = ".$quiztype;
			}
			if($session_id <> 0) {
				$sql_quiz .= " OR session_id = ".$session_id;
			}
			if(!empty($search)) {
				$sql_quiz .= " AND title LIKE '".$title."%'";
			}
			
			$sql_quiz .= " ORDER BY title";
			Database::query("SET NAMES 'utf8'");
			$res_quiz = Database::query($sql_quiz, __FILE__, __LINE__);
			while($quiz = Database::fetch_array($res_quiz, 'ASSOC')) {
				$quizzes[] = $info_course['code'].'@'.$quiz['id'].'@'.$quiz['title'];
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
				$info_course = CourseManager :: get_course_information($search_course_code);
				$table_quiz = Database :: get_course_table(TABLE_QUIZ_TEST, $info_course['db_name']);

				$sql_quiz = "SELECT id, title FROM $table_quiz WHERE active <> -1 ORDER BY title";
				$res_quiz = Database::query($sql_quiz, __FILE__, __LINE__);
				while($quiz = Database::fetch_array($res_quiz, 'ASSOC')) {
					$quizzes[] = $info_course['code'].'@'.$quiz['id'].'@'.$quiz['title'];
				}
			}		
		}

		if(sizeof($quizzes) > 1) {
			//echo "<div class='pagination'><ul id='course_pagination'>";
			echo pagination($limit,$page,'index.php?tab=quizzes&page=',sizeof($quizzes),'quizzes'); 
			//echo "</ul></div>";
		}
	break;
	case get_facetoface :
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
		$limit_quizzes = array();
		$course_code = $_GET['course_code'];
		$user_id = $_GET['user_id'];
		$session_id = 0;
		
		$theme_color = get_theme_color();
		
		if(isset($_GET['search']) && !empty($_GET['search'])) {
			$search = $_GET['search'];
		}
		else {
			$search = '';
		}
		$search = checkUTF8($search);
		if(empty($course_code))
			$course_code = 0;
		if($course_code == '0') {					
			$facetofaces = get_facetoface_list($search, $user_id);
		}
		else {			

			$info_course = CourseManager :: get_course_information($course_code);
			$table_facetoface = Database :: get_course_table(TABLE_FACE_2_FACE, $info_course['db_name']);			
			$where_added = 0;
			$sql_facetoface = "SELECT * FROM $table_facetoface WHERE session_id = 0";					
			$sql_facetoface .= " ORDER BY name";

			Database::query("SET NAMES 'utf8'");
			$res_facetoface = Database::query($sql_facetoface, __FILE__, __LINE__);
			while($facetoface = Database::fetch_array($res_facetoface, 'ASSOC')) {
				$facetofaces[] = $info_course['code'].'@'.$facetoface['id'].'@'.$facetoface['name'];
			}
		}

		$unique_facetoface = array();

		if(!empty($search)) {
			if(api_is_allowed_to_edit()){
				$sql = "SELECT code FROM $course_table";
				$sql .= " WHERE code LIKE '".$search."' OR title LIKE '".$search."'";
			}
			else {
				$sql = "SELECT code, title FROM $course_table c, $course_user_table cr WHERE c.code = cr.course_code AND cr.user_id = ".api_get_user_id()."  AND code LIKE '".$search."' OR title LIKE '".$search."'";
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
				$info_course = CourseManager :: get_course_information($search_course_code);
				$table_facetoface = Database :: get_course_table(TABLE_FACE_2_FACE, $info_course['db_name']);

				$sql_facetoface = "SELECT id, name FROM $table_facetoface ORDER BY name";
				$res_facetoface = Database::query($sql_facetoface, __FILE__, __LINE__);
				while($facetoface = Database::fetch_array($res_facetoface, 'ASSOC')) {
					$facetofaces[] = $info_course['code'].'@'.$facetoface['id'].'@'.$facetoface['name'];
				}
			}		
		}		

		/*if(sizeof($facetofaces) > 0) {
		$limit_facetofaces = get_limited_quizzes($page1, $limit, $facetofaces);
		}
		else {
		$limit_facetofaces = array();
		}*/
		$limit_facetofaces = $facetofaces;
		$chart_facetoface_arr = array();
	//	$sql .= " LIMIT $page1, $limit";		
		echo '<tbody>
				<thead>
					<tr>
                        <th>'.api_convert_encoding(get_lang("Facetoface"),"UTF-8",api_get_system_encoding()).'</th>
						<th>'.api_convert_encoding(get_lang("Course"),"UTF-8",api_get_system_encoding()).'</th>
						<th>'.api_convert_encoding(get_lang("MaxScore"),"UTF-8",api_get_system_encoding()).'</th>
						<th>'.api_convert_encoding(get_lang("MinScore"),"UTF-8",api_get_system_encoding()).'</th>						
						<th class="print_invisible">'.api_convert_encoding(get_lang("ListLearners"),"UTF-8",api_get_system_encoding()).'</th>                         					
                    </tr>
				</thead>';
		foreach($limit_facetofaces as $facetoface) {				
			list($code,$facetoface_id,$facetoface_name) = split("@",$facetoface);
			
			$course_name = get_course_title($code);
			$max_score = get_facetoface_maxscore($facetoface_id, $code, $session_id);
			$min_score = get_facetoface_minscore($facetoface_id, $code, $session_id);

			if(empty($max_score)){
				$max_score = 0;
			}

			if(empty($min_score)){
				$min_score = 0;
			}
			
			//$tmp_average_score = str_replace("%","",$average_score);
			$tmp_average_score = $max_score;
			if(intval($tmp_average_score) > 0){
			$chart_facetoface_arr[] = '{y: '.$tmp_average_score.', label: "'.$facetoface_name.'" }';
			}	
			echo "<tr>
					<td>".$facetoface_name."</td>
					<td>".$course_name."</td>
					<td align='center'>".$max_score."</td>													
					<td align='center'>".$min_score."</td>					
					<td class='print_invisible' align='center'><a id='listlearnersff' href='list_ff_learners.php?ff_id=".$facetoface_id."&course_code=".$code."&user_id=".$user_id."&search=".$search."&sessionId=".$session_id."'><img src='$pathStyleSheets/images/action/list_learners.png'></a></td>
				  </tr>";	
			
		}	
		$count_chart_facetoface = sizeof($chart_facetoface_arr);
		
		echo '</tbody>';
	break;
	case get_users :
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
		$users = array();
		$limited_users = array();
		$course_code = $_GET['course_code'];
		$quiz_rank = $_GET['rank'];
		$status = $_GET['status'];
		$trainer_id = $_GET['trainer_id'];
	
		if(isset($_GET['device']) && $_GET['device'] == 'small') {
			$small_device = 'Y';
		}
		
		$theme_color = get_theme_color();

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
		if(empty($_GET['sessionId'])) {
			$session_id = 0;
		}
		else {
			$session_id = $_GET['sessionId'];
		}

		if(api_is_allowed_to_edit() || api_is_allowed_to_create_course()){
			if($course_code == '0' && $_GET['sessionId'] == 0) {
				$users = get_users_list($status, $search, $trainer_id, $course_search);
			}
			else if($_GET['sessionId'] <> 0) {

				$sql = "SELECT u.user_id,u.lastname, u.firstname FROM $user_table u, $session_course_user_table srcu WHERE u.user_id = srcu.id_user AND u.active = ".$status." AND srcu.id_session = ".$_GET['sessionId'];
				if(!empty($search)){
					$sql .= " AND (u.lastname LIKE '".$search."%' OR u.firstname LIKE '".$search."%' OR u.username LIKE '".$search."%')";
				}
				$sql .= " ORDER BY u.lastname";

				Database::query("SET NAMES 'utf8'");
				$res = Database::query($sql, __FILE__, __LINE__);
				while($user = Database::fetch_array($res, 'ASSOC')) {
					$users[] = $user;
				}
			}
			else {

				$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
				
				$sql = "SELECT u.user_id, u.lastname, u.firstname FROM $user_table u, $course_user_table cr WHERE u.user_id = cr.user_id AND cr.course_code = '".$course_code."' AND u.active = ".$status;
				if(!empty($search)){
					$sql .= " AND (u.lastname LIKE '".$search."%' OR u.firstname LIKE '".$search."%' OR u.username LIKE '".$search."%')";
				}
				$sql .= " ORDER BY u.lastname";

				Database::query("SET NAMES 'utf8'");
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
				if(!api_is_allowed_to_edit() && !api_is_allowed_to_create_course()){
					$users = array();
					$sql1 = "SELECT * FROM $user_table WHERE user_id = ".api_get_user_id();
					$res1 = Database::query($sql1, __FILE__, __LINE__);
					while($row1 = Database::fetch_array($res1)){
						$users[] = $row1;
					}
				}
			}		
		}
		
		$limited_users = sort($users);
		
		$unique_arr = array();
		//$test = array_merge($users,$users_special);
		//$unique_users = unique_sort($test);
		//print_r($unique_users);
		if($quiz_rank <> 0){

			$limited_users = $users;
		}
		else {		
			
		$limited_users = get_limited_users($page1, $limit, $users);
		}

		$chart_user_arr = array();
	//	$sql .= " LIMIT $page1, $limit";		
		echo '<tbody>
				<thead>
					<tr>
                        <th>'.api_convert_encoding(get_lang("LastName"),"UTF-8",api_get_system_encoding()).'</th>
						<th>'.api_convert_encoding(get_lang("FirstName"),"UTF-8",api_get_system_encoding()).'</th>
						<th>'.api_convert_encoding(get_lang("LatestConnection"),"UTF-8",api_get_system_encoding()).'</th>
						<th>'.api_convert_encoding(get_lang("ModulesTime"),"UTF-8",api_get_system_encoding()).'</th>
						<th>'.api_convert_encoding(get_lang("ModulesProgress"),"UTF-8",api_get_system_encoding()).'</th>
						<th>'.api_convert_encoding(get_lang("ModulesScore"),"UTF-8",api_get_system_encoding()).'</th>
						<th>'.api_convert_encoding(get_lang("QuizzesScore"),"UTF-8",api_get_system_encoding()).'</th>
						<th class="print_invisible">'.api_convert_encoding(get_lang("IndividualReporting"),"UTF-8",api_get_system_encoding()).'</th>                         					
                    </tr>
				</thead>';
		foreach($limited_users as $user) {	

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

				$time_spent = get_time_spent($user['user_id'], $course_code, $_GET['sessionId'], $course_search);
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
							

				if($quiz_rank <> 0 && ($quizscore != '/') && ($quizscore > ($quiz_rank - 10)) && ($quizscore <= $quiz_rank)) {

					if(intval($tmp_quiz_score) > 0){
						$chart_user_arr[] = '{y: '.$tmp_quiz_score.', label: "'.strtoupper($user['lastname']).' '.$user['firstname'].'" }';
					}

			echo "<tr>
					<td>".strtoupper($user['lastname'])."</td>
					<td>".$user['firstname']."</td>
					<td align='center'>".api_convert_encoding($last_connection_date,'UTF-8',api_get_system_encoding())."</td>								
					<td align='center'>".$time_spent."</td>
					<td align='center'>".$progress."</td>
					<td align='center'>".$score."</td>
					<td align='center'>".$quiz_score."</td>
					<td class='print_invisible' align='center'><a id='inreport' href='individual_reporting.php?user_id=".$user['user_id']."&course_code=".$course_code."&rank=".$quiz_rank."&status=".$status."&trainer_id=".$trainer_id."&course_search=".$course_search."&search=".$search."&sessionId=".$_GET["sessionId"]."'><img src='$pathStyleSheets/images/action/reporting32.png'></a></td>
				  </tr>";	
				}
				else if($quiz_rank == 0) {

					if(intval($tmp_quiz_score) > 0){
						$chart_user_arr[] = '{y: '.$tmp_quiz_score.', label: "'.strtoupper($user['lastname']).' '.$user['firstname'].'" }';
					}

					echo "<tr>
					<td>".strtoupper($user['lastname'])."</td>
					<td>".$user['firstname']."</td>
					<td align='center'>".api_convert_encoding($last_connection_date,'UTF-8',api_get_system_encoding())."</td>								
					<td align='center'>".$time_spent."</td>
					<td align='center'>".$progress."</td>
					<td align='center'>".$score."</td>
					<td align='center'>".$quiz_score."</td>
					<td class='print_invisible' align='center'><a id='inreport' href='individual_reporting.php?user_id=".$user['user_id']."&course_code=".$course_code."&rank=".$quiz_rank."&status=".$status."&trainer_id=".$trainer_id."&course_search=".$course_search."&search=".$search."&sessionId=".$_GET["sessionId"]."'><img src='$pathStyleSheets/images/action/reporting32.png'></a></td>
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
				title: "'.api_convert_encoding(get_lang('UserVsQuizScore'),'UTF-8',api_get_system_encoding()).'",
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
				indexLabelFontColor: "#CCC",
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
		echo '</tbody>';
	break;
	case get_users_pages :		
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
		$users = array();
		$course_code = $_GET['course_code'];
		$quiz_rank = $_GET['rank'];
		$status = $_GET['status'];
		$trainer_id = $_GET['trainer_id'];
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
		if($course_code == '0' && $_GET['sessionId'] == 0) {
		
			$users = get_users_list($status, $search, $trainer_id, $course_search);
		}
		else if($_GET['sessionId'] <> 0) {
			$sql = "SELECT u.user_id,u.lastname, u.firstname FROM $user_table u, $session_course_user_table srcu WHERE u.user_id = srcu.id_user AND u.active = ".$status." AND srcu. id_session = ".$_GET['sessionId'];
			if(!empty($search)){
				$sql .= " AND (u.lastname LIKE '".$search."%' OR u.firstname LIKE '".$search."%' OR u.username LIKE '".$search."%')";
			}
			$sql .= " ORDER BY u.lastname";
			Database::query("SET NAMES 'utf8'");
			$res = Database::query($sql, __FILE__, __LINE__);
			while($user = Database::fetch_array($res, 'ASSOC')) {
				$users[] = $user;
			}
		}
		else {
			$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
			
			$sql = "SELECT u.user_id, u.lastname, u.firstname FROM $user_table u, $course_user_table cr WHERE u.user_id = cr.user_id AND cr.course_code = '".$course_code."' AND u.active = ".$status;
			if(!empty($search)){
				$sql .= " AND (u.lastname LIKE '".$search."%' OR u.firstname LIKE '".$search."%' OR u.username LIKE '".$search."%')";
			}
			$sql .= " ORDER BY u.lastname";
			Database::query("SET NAMES 'utf8'");
			$res = Database::query($sql, __FILE__, __LINE__);
			while($user = Database::fetch_array($res, 'ASSOC')) {
				$users[] = $user;
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
				if(!api_is_allowed_to_edit() && !api_is_allowed_to_create_course()){
					$users = array();
					$sql1 = "SELECT * FROM $user_table WHERE user_id = ".api_get_user_id();
					$res1 = Database::query($sql1, __FILE__, __LINE__);
					while($row1 = Database::fetch_array($res1)){
						$users[] = $row1;
					}
				}
			}		
		}

		$unique_arr = array();
		foreach($users as $user) {
			if(!in_array($user['user_id'], $unique_arr)){
				$unique_arr[] = $user['user_id'];
			}
		}

		if(sizeof($unique_arr) > 1) {
			//echo "<div class='pagination'><ul id='course_pagination'>";
			echo pagination($limit,$page,'index.php?tab=learners&page=',sizeof($unique_arr),'learners'); 
			//echo "</ul></div>";
		}
	break;
	case get_course_text :
		$user_table = Database :: get_main_table(TABLE_MAIN_USER);
		$course_table 		= Database :: get_main_table(TABLE_MAIN_COURSE);
		$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
		$session_course_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
		$display_text = '';

		$course_code = $_GET['course_code'];
		$trainer_id = $_GET['userid'];
		$from = $_GET['from'];
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
		if(isset($course_search) && !empty($course_search)) {
			$sqlSearch_if = " WHERE (code LIKE '".$course_search."%' OR title LIKE '".$course_search."%' OR tutor_name LIKE '".$course_search."%')";
			$sqlSearch_else = " AND (c.code LIKE '".$course_search."%' OR c.title LIKE '".$course_search."%' OR c.tutor_name LIKE '".$course_search."%')";
		}
		if(!empty($course_code)){
			$sqlcode = " AND c.code = '".$course_code."'";
		}
			
		if($trainer_id == 0 && $session_id == 0) {
			$sql = "SELECT code, title, db_name FROM $course_table $sqlSearch_if $sqlcode ORDER BY title";
		}
		else {
			$sql = "SELECT code, title, db_name FROM $course_table c";
			if(!empty($trainer_id)){
				$sql .= " , $course_user_table cr WHERE c.code = cr.course_code AND cr.user_id = ".$trainer_id." AND cr.status = 1 AND cr.tutor_id = 1";
			}
			if(!empty($session_id)){

				$sql .= " , $session_course_table scr WHERE c.code = scr.course_code AND scr.id_session = ".$session_id;
			}		
			$sql .= $sqlSearch_else;
			$sql .= $sqlcode;
			$sql .= " ORDER BY c.title";
		}	
		Database::query("SET NAMES 'utf8'");
		$res = Database::query($sql, __FILE__, __LINE__);
		$num_rows = Database::num_rows($res);
		if($num_rows > 5){
			$tmpnum_rows = 5;
		}
		else {
			$tmpnum_rows = $num_rows;
		}
		$i=1;
		while($row = Database::fetch_array($res)) {			
			if($i <= $tmpnum_rows){
				//$course_name[] = api_convert_encoding($row['title'],'UTF-8',api_get_system_encoding());				
				$course_name[] = $row['title'];
				$i++;
			}
		}
		

		if($from == 'modules'){
			$display_text = get_lang("ModulesOfCourse");
		}
		else if($from == 'quiz'){
			$display_text = get_lang("QuizOfCourse");
		}
		else if($from == 'learner'){
			$display_text = get_lang("LearnersOfCourse");
		}
		echo '<b>'.$display_text.'</b>';
		echo ' '.implode(',',$course_name);
		if($num_rows > 5) {
			echo ' ...';
		}

	break;	

}
?>