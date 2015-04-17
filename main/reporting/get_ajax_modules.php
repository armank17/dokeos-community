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

$page=1;
$limit = 20;
if(isset($_GET['page']) && $_GET['page']!=''){
	$page=$_GET['page'];
}
$page1 = ($page - 1) * $limit;

$lp_id = $_GET['lp_id'];
$limit_modules = array();
$course_code = $_GET['course_code'];
$user_id = $_GET['user_id'];

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
$list_courses = get_course_list();
?>

<form class="pull-right">
	<span class="pull-right">
	<input id="module_search" name="module_search" type="text" class="input-medium search-query">  
	<button id="modulebtn_search" type="submit" class="btn"><?php echo get_lang('Search'); ?></button>
	<span id="module_reset"><button type="button" name="reset" class="btn"><?php echo get_lang("ResetFilter"); ?></button></span>
	</span></br></br>
	<span class="pull-right">
	<select name="select_courses" id="select_courses">
		<?php
		echo '<option value="0">' . api_convert_encoding(get_lang('SelectCourses'),'UTF-8',api_get_system_encoding()) . '</option>';
		foreach ($list_courses as $course) {   
			//if($course_code == $course['code']){ $selected = 'selected'; } else { $selected = ''; }
			echo '<option value="' . $course['code'] . '" '.$selected.'>' . $course['title'] . '</option>';
		}
		echo "\n";
		?>
	</select>	                      
	</span>
</form>
<b><?php echo get_lang("ModulesAverageValues"); ?></b>
<span id="module_pages">
<?php

if(count($modules) > 0) {									
//	echo "<div class='pagination'><ul id='course_pagination'>";
	echo pagination($limit,$page,'index.php?tab=modules&page=',count($modules),'modules'); 
//	echo "</ul></div>";
}
?>
</span>
<div class="span11 chart_div" id="chartModuleContainer" style="height:300px;display:none;"></div>
<table class="responsive large-only table-striped" id="modules">
	<thead>
		<tr>
			<th><?php echo api_convert_encoding(get_lang("Modules"),"UTF-8",api_get_system_encoding()); ?></th>
			<th><?php echo api_convert_encoding(get_lang("InCourse"),"UTF-8",api_get_system_encoding()); ?></th>
			<th><?php echo api_convert_encoding(get_lang("Time"),"UTF-8",api_get_system_encoding()); ?></th>
			<th><?php echo api_convert_encoding(get_lang("Progress"),"UTF-8",api_get_system_encoding()); ?></th>
			<th><?php echo api_convert_encoding(get_lang("Score"),"UTF-8",api_get_system_encoding()); ?></th>
			<th class="print_invisible"><?php echo api_convert_encoding(get_lang("Action"),"UTF-8",api_get_system_encoding()); ?></th>
					
		</tr>
	</thead>
	<tbody>
		<?php
		$chart_module_arr = array();
		foreach($limit_modules as $module) {	
			
			list($code,$lp_id,$module_name) = split("@",$module);
			
			//$module_name = api_convert_encoding($module_name,api_get_system_encoding(),'UTF-8');
			$course_name = get_course_title($code);
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
					<td class='print_invisible' align='center'><a id='module_learners' href='module_learners.php?lp_id=".$lp_id."&course_code=".$code."&course_search=".$course_search."&search=".$search."&sessionId=".$session_id."'><img src='$pathStyleSheets/images/action/list_learners.png' /></a></td>
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
				title: "'.get_lang('ModuleVsQuizScore').'",
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
		?>                                        
	</tbody>
</table>
<br/>
<!--<span id="quiz_reset"><button type="button" name="test" id="test" value="test"><?php echo get_lang('ResetFilter'); ?></button></span>-->
<span class="pull-right"><a href="#" id="module_export"><?php echo get_lang('Export'); ?></a> / <a href="#" id="module_print"><?php echo get_lang('Print'); ?></a></span></br>