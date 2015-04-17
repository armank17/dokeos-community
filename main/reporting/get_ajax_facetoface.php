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

$ff_id = $_GET['ff_id'];
$limit_facetoface = array();
$course_code = $_GET['course_code'];
$user_id = $_GET['user_id'];
$session_id = 0;

if(isset($_GET['device']) && $_GET['device'] == 'small') {
	$small_device = 'Y';
}
$theme_color = get_theme_color();

if(empty($_GET['sessionId']))
	$session_id = 0;
else 
	$session_id = $_GET['sessionId'];
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
	$facetofaces = get_facetoface_list($search,$user_id);
}
else {

	$info_course = CourseManager :: get_course_information($course_code);
	$ff_tbl = Database :: get_course_table(TABLE_FACE_2_FACE, $info_course['db_name']);

	$sql_ff = "SELECT id,name FROM $ff_tbl";
	if(!empty($search)){
	$sql_ff .= " WHERE name LIKE '".$search."%'";		
	}
	$sql_ff .= " ORDER BY name";
	Database::query("SET NAMES 'utf8'");
	$res_ff = Database::query($sql_ff, __FILE__, __LINE__);
	while($ff = Database::fetch_array($res_ff, 'ASSOC')) {
		$facetofaces[] = $info_course['code'].'@'.$ff['id'].'@'.$ff['name'];
	}
}

$unique_facetofaces = array();	

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
		$ff_tbl = Database :: get_course_table(TABLE_FACE_2_FACE, $info_course['db_name']);
		
		$sql_ff = "SELECT id,name FROM $ff_tbl ORDER BY name";
		$res_ff = Database::query($sql_ff, __FILE__, __LINE__);
		while($ff = Database::fetch_array($res_ff, 'ASSOC')) {
			$facetofaces[] = $info_course['code'].'@'.$ff['id'].'@'.$ff['name'];
		}
	}		
}
$limit_facetofaces = get_limited_modules($page1, $limit, $facetofaces);
$list_courses = get_course_list();
?>

<form class="pull-right">
	<span class="pull-right">
	<input id="facetoface_search" name="facetoface_search" type="text" class="input-medium search-query">  
	<button id="facetofacebtn_search" type="submit" class="btn"><?php echo get_lang('Search'); ?></button>
	<span id="facetoface_reset"><button type="button" name="reset" class="btn"><?php echo get_lang("ResetFilter"); ?></button></span>
	</span></br></br>
	<span class="pull-right">
	<select name="list_courses_ff" id="list_courses_ff">
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
<span><h4><?php echo api_convert_encoding(get_lang('FacetofaceAverageValues'),'UTF-8',api_get_system_encoding()); ?></h4></span>
<span id="facetoface_pages">
<?php

if(count($facetofaces) > 0) {									
//	echo "<div class='pagination'><ul id='course_pagination'>";
	echo pagination($limit,$page,'index.php?tab=facetoface&page=',count($facetofaces),'facetofaces'); 
//	echo "</ul></div>";
}
?>
</span>
<div class="span11 chart_div" id="chartModuleContainer" style="height:300px;display:none;"></div>
<table class="responsive large-only table-striped" id="facetoface">
	<thead>
		<tr>
			<th><?php echo api_convert_encoding(get_lang("Facetoface"),"UTF-8",api_get_system_encoding()); ?></th>
			<th><?php echo api_convert_encoding(get_lang("Course"),"UTF-8",api_get_system_encoding()); ?></th>
			<th><?php echo api_convert_encoding(get_lang("MaxScore"),"UTF-8",api_get_system_encoding()); ?></th>
			<th><?php echo api_convert_encoding(get_lang("MinScore"),"UTF-8",api_get_system_encoding()); ?></th>			
			<th class="print_invisible"><?php echo api_convert_encoding(get_lang("Action"),"UTF-8",api_get_system_encoding()); ?></th>
					
		</tr>
	</thead>
	<tbody>
		<?php
		$chart_module_arr = array();
		foreach($limit_facetofaces as $facetoface) {	
			
			list($code,$ff_id,$ff_name) = split("@",$facetoface);
			
			//$module_name = api_convert_encoding($module_name,api_get_system_encoding(),'UTF-8');
			$course_name = get_course_title($code);
			$max_score = get_facetoface_maxscore($ff_id, $code, $session_id);
			$min_score = get_facetoface_minscore($ff_id, $code, $session_id);

			if(empty($max_score)){
				$max_score = 0;
			}

			if(empty($min_score)){
				$min_score = 0;
			}

			//$tmp_module_score = str_replace("%","",$course_module_score);
			$tmp_ff_score = $max_score;
			if(intval($tmp_ff_score) > 0){
			$chart_ff_arr[] = '{y: '.$tmp_ff_score.', label: "'.$ff_name.'" }';
			}
			echo "<tr>
					<td>".$ff_name."</td>
					<td>".$course_name."</td>
					<td align='center'>".$max_score."</td>													
					<td align='center'>".$min_score."</td>					
					<td class='print_invisible' align='center'><a id='listlearnersff' href='list_ff_learners.php?ff_id=".$ff_id."&course_code=".$code."&user_id=".$user_id."&search=".$search."&sessionId=".$session_id."'><img src='$pathStyleSheets/images/action/list_learners.png'></a></td>
				  </tr>";	
			
		}	
		
		?>                                        
	</tbody>
</table>
<br/>
<!--<span id="quiz_reset"><button type="button" name="test" id="test" value="test"><?php echo get_lang('ResetFilter'); ?></button></span>-->
<span class="pull-right"><a href="#" id="face2face_export"><?php echo get_lang('Export'); ?></a> / <a href="#" id="face2face_print"><?php echo get_lang('Print'); ?></a></span></br>