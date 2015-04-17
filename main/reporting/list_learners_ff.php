<?php
$language_file = 'admin';
require ('../inc/global.inc.php');
require_once api_get_path(LIBRARY_PATH).'tracking.lib.php';
include_once api_get_path(LIBRARY_PATH).'course.lib.php';
require_once(api_get_path(LIBRARY_PATH).'usermanager.lib.php');
require_once(api_get_path(LIBRARY_PATH).'online.inc.php');
require 'functions.php';
$pathStyleSheets = api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets');

$ff_id = $_GET['ff_id'];
$code = $_GET['course_code'];
$session_id = $_GET['sessionId'];
if(empty($session_id)){
	$session_id = 0;
}

if($_GET['c']=='export'){
	exportlearnerslistff($ff_id,$code,$session_id);
	exit;
}

if($_GET['c']=='print'){
	printlearnerslistff($ff_id,$code,$session_id);
	echo "<script type='text/javascript'>window.print();</script>";
	exit;
}

$course_info = api_get_course_info($code);
$course_user_table 		= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
$session_course_user_table 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
$track_exercices = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
$TBL_FACE2FACE = Database :: get_course_table(TABLE_FACE_2_FACE, $course_info['dbName']);
	
$users = array();

$sql = "SELECT name, max_score FROM $TBL_FACE2FACE WHERE id = ".$ff_id;
$res = Database::query($sql, __FILE__, __LINE__);
$ff_name = Database::result($res, 0, 0);
$ff_max_score = Database::result($res, 0, 1);

if($session_id == 0) {
	$sql = "SELECT user_id FROM $course_user_table cru WHERE cru.course_code = '".$code."' AND user_id <> 0 AND status = 5";
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
<a class="pull-right" id="facetoface_back" href="index.php?ff_id=<?php echo $ff_id; ?>&course_code=<?php echo $code; ?>&user_id=<?php echo $_GET['user_id']; ?>&search=<?php echo $_GET['search']; ?>&sessionId=0"><?php echo api_convert_encoding(get_lang("BackToFacetoface"),"UTF-8",api_get_system_encoding()); ?></a></br>
<form class="pull-right">		
	<input type="hidden" name="hid_ffid" id="hid_ffid" value="<?php echo $ff_id; ?>">
	<input type="hidden" name="hid_code" id="hid_code" value="<?php echo $code; ?>">
	<input type="hidden" name="hid_session_id" id="hid_session_id" value="<?php echo $session_id; ?>">
	
</form>   
<h4><?php echo api_convert_encoding(get_lang("Facetoface"),"UTF-8",api_get_system_encoding()); ?> : <?php echo api_convert_encoding($ff_name,'UTF-8',api_get_system_encoding()); ?></h4>
<table name="list_learners_ff" id="list_learners_ff" class="responsive large-only table-striped">
	<thead>
		<tr>
			<th><?php echo api_convert_encoding(get_lang("LastName"),"UTF-8",api_get_system_encoding()); ?></th>
			<th><?php echo api_convert_encoding(get_lang("FirstName"),"UTF-8",api_get_system_encoding()); ?></th>
			<th><?php echo api_convert_encoding(get_lang("Score"),"UTF-8",api_get_system_encoding())." (".$ff_max_score.")"; ?></th>							
		</tr>
	</thead>
	<tbody>
		<?php
		foreach($users as $user_id) {
				$score = 0;
				$user_info = api_get_user_info($user_id);
				$score = get_facetoface_score($user_id, $ff_id, $code);
				if(empty($score)){
					$score = 0;
				}
				
				echo "<tr>
						<td>".api_convert_encoding($user_info['lastname'],'UTF-8',api_get_system_encoding())."</td>
						<td>".api_convert_encoding($user_info['firstname'],'UTF-8',api_get_system_encoding())."</td>
						<td align='center'>".$score."</td>";						
					  echo "</tr>";	
			}
		?>		

	</tbody>
</table>
</br>
<p class="pull-right"><a href="index.php?ff_id=<?php echo $ff_id; ?>&course_code=<?php echo $code; ?>&sessionId=<?php echo $session_id; ?>" id="export_learners_list_ff"><?php echo get_lang("Export"); ?></a> / <a href="index.php?ff_id=<?php echo $ff_id; ?>&course_code=<?php echo $code; ?>&sessionId=<?php echo $session_id; ?>" id="print_learners_list_ff"><?php echo get_lang("Print"); ?></a></p></br>
</div>