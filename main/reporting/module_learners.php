<?php
$language_file = 'admin';
require ('../inc/global.inc.php');
require_once api_get_path(LIBRARY_PATH).'tracking.lib.php';
include_once api_get_path(LIBRARY_PATH).'course.lib.php';
require_once(api_get_path(LIBRARY_PATH).'usermanager.lib.php');
require_once(api_get_path(LIBRARY_PATH).'online.inc.php');
require 'functions.php';

$pathStyleSheets = api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets');

$lp_id = $_GET['lp_id'];
$code = $_GET['course_code'];
$session_id = $_GET['sessionId'];
if(empty($session_id)){
	$session_id = 0;
}

if($_GET['c']=='export'){
	exportmodulelearnerslist($lp_id,$code,$session_id);
	exit;
}

if($_GET['c']=='print'){
	printmodulelearnerslist($lp_id,$code,$session_id);
	echo "<script type='text/javascript'>window.print();</script>";
	exit;
}

$course_info = api_get_course_info($code);
$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
$session_course_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
//$track_exercices = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
$table_lp = Database :: get_course_table(TABLE_LP_MAIN, $course_info['dbName']);	
$TBL_LP_VIEW = Database :: get_course_table(TABLE_LP_VIEW, $course_info['dbName']);
$users = array();

$sql = "SELECT name FROM $table_lp WHERE id = ".$lp_id;
$res = Database::query($sql, __FILE__, __LINE__);
$lp_name = Database::result($res, 0, 0);

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
<a class="pull-right" id="modules_back" href="index.php?lp_id=<?php echo $lp_id; ?>&course_code=<?php echo $code; ?>&course_search=<?php echo $_GET['course_search']; ?>&search=<?php echo $_GET['search']; ?>&sessionId=<?php echo $_GET['sessionId']; ?>"><?php echo api_convert_encoding(get_lang("BackToModule"),"UTF-8",api_get_system_encoding()); ?></a></br>
 
<h4><?php echo api_convert_encoding(get_lang("Module"),"UTF-8",api_get_system_encoding()); ?> : <?php echo api_convert_encoding($lp_name,'UTF-8',api_get_system_encoding()); ?></h4>
<table name="list_module_learners" id="list_module_learners" class="responsive large-only table-striped">
	<thead>
		<tr>
			<th><?php echo api_convert_encoding(get_lang("LastName"),"UTF-8",api_get_system_encoding()); ?></th>
			<th><?php echo api_convert_encoding(get_lang("FirstName"),"UTF-8",api_get_system_encoding()); ?></th>
			<th><?php echo api_convert_encoding(get_lang("Time"),"UTF-8",api_get_system_encoding()); ?></th>
			<th><?php echo api_convert_encoding(get_lang("Progress"),"UTF-8",api_get_system_encoding()); ?></th>
			<th><?php echo api_convert_encoding(get_lang("Score"),"UTF-8",api_get_system_encoding()); ?></th>
			<th class="print_invisible"><?php echo api_convert_encoding(get_lang("Screens"),"UTF-8",api_get_system_encoding()); ?></th>						
		</tr>
	</thead>
	<tbody>
		<?php		
			foreach($users as $user_id) {
				$user_info = api_get_user_info($user_id);
				$module_time = specific_modules_time($user_id, $code, $lp_id);
				$module_progress = specific_modules_progress($user_id, $code, $lp_id);
				$module_score = Tracking :: get_avg_student_score($user_id, $code, array ($lp_id));
				//echo 'sc=='.$module_score = Tracking :: get_average_test_scorm_and_lp($user_id, $code);

				$sql_view = "SELECT max(view_count) AS count FROM $TBL_LP_VIEW WHERE lp_id = ".$lp_id." AND user_id = '" . $user_id . "'";
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
				echo "<tr>
						<td>".api_convert_encoding($user_info['lastname'],'UTF-8',api_get_system_encoding())."</td>
						<td>".api_convert_encoding($user_info['firstname'],'UTF-8',api_get_system_encoding())."</td>
						<td align='center'>".display_time_format($module_time)."</td>
						<td align='center'>".$module_progress."%</td>
						<td align='center'>".$module_score."</td>";
						if(!is_null($view_count)){
							echo "<td class='print_invisible' align='center'><a id='module_list_result' href='module_result.php?action=stats&course_code=".$code."&course=".$code."&user_id=".$user_id."&student_id=".$user_id."&lp_id=".$lp_id."&sessionId=".$session_id."&page=module'><img src='$pathStyleSheets/images/action/reporting32.png'></a></td>";
							}
							else {
							echo "<td class='print_invisible' align='center'><img src='$pathStyleSheets/images/action/reporting32.png'></td>";
						}
						
					  echo "</tr>";	
			}		
		?>		

	</tbody>
</table>
</br>
<p class="pull-right"><a href="index.php?lp_id=<?php echo $lp_id; ?>&course_code=<?php echo $code; ?>&sessionId=<?php echo $session_id; ?>" id="export_module_learners_list"><?php echo get_lang("Export"); ?></a> / <a href="index.php?lp_id=<?php echo $lp_id; ?>&course_code=<?php echo $code; ?>&sessionId=<?php echo $session_id; ?>" id="print_module_learners_list"><?php echo get_lang("Print"); ?></a></p></br>
</div>