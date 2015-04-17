<?php
/* For licensing terms, see /dokeos_license.txt */
/**
 * Responses to AJAX calls
 */
require_once '../../main/inc/global.inc.php';

$action = $_GET['action'];
$colIndex = $_GET['colIndex'];
$src = $_GET['src'];

if(empty($action)) {
	$action = $_REQUEST['action'];
}

switch ($action) {
   case 'update_icons' :        
		$TBL_SCENARIO_STEPS = Database :: get_course_table(TABLE_SCENARIO_STEPS);	
		$pathparts = pathinfo($src);
		$step_icon = $pathparts['basename'];		
		$sql = "UPDATE $TBL_SCENARIO_STEPS SET step_icon = '".Database::escape_string($step_icon)."' WHERE step_created_order = ".$colIndex." AND session_id = ".api_get_session_id();
		Database::query($sql, __FILE__, __LINE__);
	break;
	case 'update_stepname' :        
		$TBL_SCENARIO_STEPS = Database :: get_course_table(TABLE_SCENARIO_STEPS);	
		$colIndex = $_REQUEST['colIndex'];
		$step_name = $_POST['value'];
		$sql = "UPDATE $TBL_SCENARIO_STEPS SET step_name = '".Database::escape_string(api_convert_encoding($step_name,api_get_system_encoding(),'UTF-8'))."' WHERE step_created_order = ".$colIndex." AND session_id = ".api_get_session_id();
		Database::query($sql, __FILE__, __LINE__);
		print $step_name;
	break;
	case 'update_border' :        
		$TBL_SCENARIO_STEPS = Database :: get_course_table(TABLE_SCENARIO_STEPS);	
		$border_color = $_GET['border_color'];		
		$sql = "UPDATE $TBL_SCENARIO_STEPS SET step_border = '#".$border_color."' WHERE step_created_order = ".$colIndex."  AND session_id = ".api_get_session_id();
		Database::query($sql, __FILE__, __LINE__);

	break;
	case 'update_showborder' :        
		$TBL_SCENARIO_STEPS = Database :: get_course_table(TABLE_SCENARIO_STEPS);	
		$hide_border = $_GET['hide_border'];		
		$sql = "UPDATE $TBL_SCENARIO_STEPS SET hide_border = ".$hide_border." WHERE session_id = ".api_get_session_id();
		Database::query($sql, __FILE__, __LINE__);
	break;
	case 'update_showimage' :        
		$TBL_SCENARIO_STEPS = Database :: get_course_table(TABLE_SCENARIO_STEPS);	
		$hide_image = $_GET['hide_image'];		
		$sql = "UPDATE $TBL_SCENARIO_STEPS SET hide_image = ".$hide_image." WHERE session_id = ".api_get_session_id();
		Database::query($sql, __FILE__, __LINE__);
	break;
	case 'update_prerequisite' :        
		$TBL_SCENARIO_STEPS = Database :: get_course_table(TABLE_SCENARIO_STEPS);	
		$colIndex = $_GET['colIndex'];
		$prereq_step_id = $_GET['preoption'];

		if($prereq_step_id == '0'){
			$prereq_step_name = 'None';
			$prereq_step_id = 'None';
		}
		
		if($prereq_step_id != 'None'){
		$sql = "SELECT step_name FROM $TBL_SCENARIO_STEPS WHERE id = ".$prereq_step_id;
		$res = Database::query($sql, __FILE__, __LINE__);
		$prereq_step_name = Database::result($res,0,0);
		}
		
		$sql = "UPDATE $TBL_SCENARIO_STEPS SET step_prerequisite = '".$prereq_step_id."' WHERE step_created_order = ".$colIndex."  AND session_id = ".api_get_session_id();
		Database::query($sql, __FILE__, __LINE__);

		echo $prereq_step_name;

	break;
	case 'update_completion' :        
		$TBL_SCENARIO_STEPS = Database :: get_course_table(TABLE_SCENARIO_STEPS);	
		$step_completion = $_GET['text'];
		$display_text = $_GET['display_text'];
		list($option, $sub_option, $min_score) = split(":",$step_completion);
		if($option == 'Module'){
			$db_option = $option.'@'.$sub_option;
		}
		else {
			$db_option = $option;
		}
		
		$sql = "UPDATE $TBL_SCENARIO_STEPS SET step_completion_option = '".$db_option."', step_completion_percent = ".trim(str_replace("%","",$min_score))." WHERE step_created_order = ".$colIndex."  AND session_id = ".api_get_session_id();
		Database::query($sql, __FILE__, __LINE__);

		$sql = "SELECT id FROM $TBL_SCENARIO_STEPS WHERE step_created_order = ".$colIndex."  AND session_id = ".api_get_session_id();
		$res = Database::query($sql, __FILE__, __LINE__);
		$step_id = Database::result($res, 0, 0);

		echo $display_text.'&nbsp;<div><span class="edit_criteria" id="'.$step_id.'" style="padding-left:90px;">'.Display::return_icon('pixel.gif', get_lang('EditCriteria'), array('class' => 'actionplaceholdericon actionediticon')).'</span>&nbsp;<span class="delete_criteria" id="'.$step_id.'" >'.Display::return_icon('pixel.gif', get_lang('DeleteCriteria'), array('class' => 'actionplaceholdericon actiondeleteicon')).'</span></div>';

	break;
	case 'add_activity' :  

		$TBL_SCENARIO_STEPS = Database :: get_course_table(TABLE_SCENARIO_STEPS);	
		$TBL_SCENARIO_ACTIVITY = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY);
		$TBL_SCENARIO_ACTIVITY_VIEW = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY_VIEW);
		$TBL_DOCUMENT = Database :: get_course_table(TABLE_DOCUMENT);
                $TBL_PAGE = Database::get_main_table(TABLE_MAIN_NODE);                
		$TBL_EXERCICES = Database :: get_course_table(TABLE_QUIZ_TEST);
		$TBL_EXAM = Database :: get_course_table(TABLE_EXAM);
		$TBL_LP = Database :: get_course_table(TABLE_LP_MAIN);	
		$TBL_ASSIGNMENT = Database :: get_course_table(TABLE_STUDENT_PUBLICATION);
		$TBL_SURVEY = Database :: get_course_table(TABLE_MAIN_SURVEY);	
		$TBL_FACE2FACE = Database :: get_course_table(TABLE_FACE_2_FACE);	
		$rowIndex = $_GET['rowIndex'];
		$type = $_GET['type'];
		$dynamic_rowIndex = $rowIndex - 4;

		$sql = "SELECT id FROM $TBL_SCENARIO_STEPS WHERE step_created_order = ".$colIndex."  AND session_id = ".api_get_session_id();
		$res = Database::query($sql, __FILE__, __LINE__);
		$step_id = Database::result($res,0,0);

		if($type == 'document') {
			$doc_id = $_GET['doc_id'];
			$activity_ref = $doc_id;
			$sql = "SELECT title FROM $TBL_DOCUMENT WHERE id = ".$doc_id;
			$res = Database::query($sql, __FILE__, __LINE__);
			$activity_name = Database::result($res,0,0);
		}
                else if($type == 'page') {
			$page_id = $_GET['page_id'];
			$activity_ref = $page_id;
			$sql = "SELECT title FROM $TBL_PAGE WHERE id = ".$page_id;
			$res = Database::query($sql, __FILE__, __LINE__);
			$activity_name = Database::result($res,0,0);
		}
		else if($type == 'quiz') {
			$quiz_id = $_GET['quiz_id'];
			$activity_ref = $quiz_id;
			$sql = "SELECT title FROM $TBL_EXERCICES WHERE id = ".$quiz_id;
			$res = Database::query($sql, __FILE__, __LINE__);
			$activity_name = Database::result($res,0,0);
		}
		else if($type == 'exam') {
			$exam_id = $_GET['exam_id'];
			$activity_ref = $exam_id;
			$sql = "SELECT exam_name FROM $TBL_EXAM WHERE id = ".$exam_id;
			$res = Database::query($sql, __FILE__, __LINE__);
			$activity_name = Database::result($res,0,0);
		}
		else if($type == 'module') {
			$module_id = $_GET['module_id'];
			$activity_ref = $module_id;
			$sql = "SELECT name FROM $TBL_LP WHERE id = ".$module_id;
			$res = Database::query($sql, __FILE__, __LINE__);
			$activity_name = Database::result($res,0,0);
		}
		else if($type == 'assignment') {
			$assignment_id = $_GET['assignment_id'];
			$activity_ref = $assignment_id;
			$sql = "SELECT title FROM $TBL_ASSIGNMENT WHERE id = ".$assignment_id;
			$res = Database::query($sql, __FILE__, __LINE__);
			$activity_name = Database::result($res,0,0);
		}
		else if($type == 'survey') {
			$survey_id = $_GET['survey_id'];
			$activity_ref = $survey_id;
			$sql = "SELECT title FROM $TBL_SURVEY WHERE survey_id = ".$survey_id;
			$res = Database::query($sql, __FILE__, __LINE__);
			$activity_name = Database::result($res,0,0);
		}
		else if($type == 'face2face') {
			$face2face_name = $_GET['name'];
			$max_score = $_GET['score'];
			$ff_id = $_GET['ff_id'];
			$ff_type = $_GET['ff_type'];

			if($ff_id == 0){
			$sql = "INSERT INTO $TBL_FACE2FACE(name, ff_type, max_score, step_id) VALUES('".Database::escape_string($face2face_name)."',".$ff_type.",".$max_score.", ".$step_id.")";
			Database::query($sql, __FILE__, __LINE__);
			$face2face_id = Database::insert_id();
			}
			else {
			$sql = "UPDATE $TBL_FACE2FACE SET name = '".Database::escape_string($face2face_name)."', ff_type = ".$ff_type.", max_score = ".$max_score." WHERE id = ".$ff_id;
			Database::query($sql, __FILE__, __LINE__);
			$face2face_id = $ff_id;
			}			
			
			$activity_ref = $face2face_id;
			$activity_name = $face2face_name;
		}
		

		$sql = "SELECT id FROM $TBL_SCENARIO_ACTIVITY WHERE step_id = ".$step_id." AND activity_created_order = ".$dynamic_rowIndex;
		$res = Database::query($sql, __FILE__, __LINE__);
		$num_rows = Database::num_rows($res);
		$activity_id = Database::result($res, 0, 0);

		if($num_rows == 0) {

			$sql = "INSERT INTO $TBL_SCENARIO_ACTIVITY(step_id, activity_type, activity_ref, activity_name, activity_created_order) VALUES(".$step_id.",'".$type."',$activity_ref,'".Database::escape_string($activity_name)."',$dynamic_rowIndex)";
			Database::query($sql, __FILE__, __LINE__);
			$activity_id = Database::insert_id();
		}
		else {

			$sql = "UPDATE $TBL_SCENARIO_ACTIVITY SET activity_type = '".$type."', activity_name = '".Database::escape_string($activity_name)."', activity_ref = $activity_ref WHERE activity_created_order = ".$dynamic_rowIndex." AND step_id = ".$step_id;
			Database::query($sql, __FILE__, __LINE__);
		}	
		
		$sql = "SELECT * FROM $TBL_SCENARIO_ACTIVITY_VIEW WHERE activity_id = ".$activity_id." AND step_id = ".$step_id;
		$res = Database::query($sql, __FILE__, __LINE__);
		while($row = Database::fetch_array($res)){
			$score = $row['score'];
			$status = $row['status'];
			$user_id = $row['user_id'];

			if($ff_type == 2){
				if($status == 'completed' && $score == 0){
					$score = $max_score;
					$status = 'completed';
				}
				else if($score < $max_score){
					$status = '';
				}
				else {
					$status = 'completed';
				}

				$sql_upd = "UPDATE $TBL_SCENARIO_ACTIVITY_VIEW SET status = '".$status."' WHERE activity_id = ".$activity_id." AND step_id = ".$step_id." AND user_id = ".$user_id;
				Database::query($sql_upd, __FILE__, __LINE__);
			}
						
			
		}
		echo api_convert_encoding($activity_name,'UTF-8',api_get_system_encoding()).'&nbsp;<div><span class="edit_activity" style="padding-left:90px;">'.Display::return_icon('pixel.gif', api_convert_encoding(get_lang('EditActivity'),'UTF-8',api_get_system_encoding()), array('class' => 'actionplaceholdericon actionediticon')).'</span>&nbsp;<span class="delete_activity" title="Delete Activity" id="delete_'.$activity_id.'_'.$step_id.'_'.$dynamic_rowIndex.'" >'.Display::return_icon('pixel.gif', api_convert_encoding(get_lang('DeleteActivity'),'UTF-8',api_get_system_encoding()), array('class' => 'actionplaceholdericon actiondeleteicon')).'<input class="hid_act_class" type="hidden" name="activity_id" id="activity_'.$colIndex.'_'.$dynamic_rowIndex.'" value="'.$activity_id.'_'.$step_id.'" /></span></div>';

	break;
	case 'get_steps' :
        global $charset; 
		$TBL_EXERCICES = Database :: get_course_table(TABLE_QUIZ_TEST);
		$TBL_QUIZ_QN = Database :: get_course_table(TABLE_QUIZ_QUESTION);
		$TBL_REL_QUIZ = Database :: get_course_table(TABLE_QUIZ_TEST_QUESTION);
		$tbl_scenario_items   = Database::get_course_table(TABLE_COURSE_SCENARIO_ITEMS);
		$data = array();
		$session_condition = api_get_session_condition(api_get_session_id(), true, true);
		$sql = "SELECT id, title FROM $TBL_EXERCICES WHERE active <> -1 $session_condition";
		$res = Database::query($sql, __FILE__, __LINE__);
		$num_rows = Database::num_rows($res);
		while($row = Database::fetch_array($res)) {

			$data[$row['id']] = $row['title'];
		}
		echo json_encode($data);

	break;
	case 'delete_activity' :
		$id = $_REQUEST['id'];
		$TBL_SCENARIO_ACTIVITY = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY);
		$TBL_FACE2FACE = Database :: get_course_table(TABLE_FACE_2_FACE);	
		list($text, $activity_id, $step_id, $activity_created_order) = explode("_",$id);

		$sql = "SELECT activity_type, activity_ref FROM $TBL_SCENARIO_ACTIVITY WHERE id = ".$activity_id." AND step_id = ".$step_id." AND activity_created_order = ".$activity_created_order;
		$res = Database::query($sql, __FILE__, __LINE__);
		$activity_type = Database::result($res,0,0);
		$activity_ref = Database::result($res,0,1);

		$sql_delete = "DELETE FROM $TBL_SCENARIO_ACTIVITY WHERE id = ".$activity_id." AND step_id = ".$step_id." AND activity_created_order = ".$activity_created_order;
		$res_delete = Database::query($sql_delete, __FILE__, __LINE__);

		if($res_delete){
			$sql_order = "UPDATE $TBL_SCENARIO_ACTIVITY SET activity_created_order = activity_created_order - 1 WHERE step_id = ".$step_id." AND session_id = ".api_get_session_id()." AND activity_created_order > ".$activity_created_order;
			Database::query($sql_order, __FILE__, __LINE__);			
		}

		if($res) {
			$sql = "DELETE FROM $TBL_FACE2FACE WHERE id = ".$activity_ref;
			Database::query($sql, __FILE__, __LINE__);
		}
		
	break;
	case 'delete_step' :
		$id = $_REQUEST['id'];
		$TBL_SCENARIO_STEPS = Database :: get_course_table(TABLE_SCENARIO_STEPS);	
		$TBL_SCENARIO_ACTIVITY = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY);
		$TBL_FACE2FACE = Database :: get_course_table(TABLE_FACE_2_FACE);	
		list($text, $step_id, $step_created_order) = explode("_",$id);

		$sql = "DELETE FROM $TBL_SCENARIO_STEPS WHERE id = ".$step_id;
		$res = Database::query($sql, __FILE__, __LINE__);
		if($res){
			$sql_upd = "UPDATE $TBL_SCENARIO_STEPS SET step_created_order = step_created_order - 1 WHERE step_created_order > ".$step_created_order." AND session_id = ".api_get_session_id();
			Database::query($sql_upd, __FILE__, __LINE__);

			$sql_pre = "UPDATE $TBL_SCENARIO_STEPS SET step_prerequisite = 'None' WHERE step_prerequisite = ".$step_id." AND session_id = ".api_get_session_id();
			Database::query($sql_pre, __FILE__, __LINE__);
		}

		$sql = "DELETE FROM $TBL_SCENARIO_ACTIVITY WHERE step_id = ".$step_id;
		Database::query($sql, __FILE__, __LINE__);

		$sql = "DELETE FROM $TBL_FACE2FACE WHERE step_id = ".$step_id;
		Database::query($sql, __FILE__, __LINE__);
		
	break;
	case 'delete_criteria' :
		
		$TBL_SCENARIO_STEPS = Database :: get_course_table(TABLE_SCENARIO_STEPS);	
		$id = $_GET['id'];		

		$sql = "UPDATE $TBL_SCENARIO_STEPS SET step_completion_option = 'None', step_completion_percent = 0 WHERE id = ".$id;	
		Database::query($sql, __FILE__, __LINE__);

	break;
	case 'delete_scenario' :
		
		$TBL_SCENARIO_STEPS = Database :: get_course_table(TABLE_SCENARIO_STEPS);	
		$TBL_SCENARIO_ACTIVITY = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY);
		$TBL_FACE2FACE = Database :: get_course_table(TABLE_FACE_2_FACE);	
		
		$sql = "DELETE FROM $TBL_SCENARIO_STEPS WHERE session_id = ".api_get_session_id();
		Database::query($sql, __FILE__, __LINE__);

		$sql = "DELETE FROM $TBL_SCENARIO_ACTIVITY WHERE session_id = ".api_get_session_id();
		Database::query($sql, __FILE__, __LINE__);

		$sql = "DELETE FROM $TBL_FACE2FACE WHERE session_id = ".api_get_session_id();
		Database::query($sql, __FILE__, __LINE__);
		
	break;
	case 'delete_introtext' :
		
		$TBL_TOOL_INTRO = Database :: get_course_table(TABLE_TOOL_INTRO);

		$sql = "UPDATE $TBL_TOOL_INTRO SET intro_text = '' WHERE id = 'course_homepage'";
		Database::query($sql, __FILE__, __LINE__);
		
	break;
	case 'delete_icons' :
		$filename = $_GET['filename'];
		$path_parts = pathinfo($filename);
		$grey_filename = $path_parts['filename'].'_grey.'.$path_parts['extension'];

		$course_info = api_get_course_info(api_get_course_id());
		$course_code = $course_info['id'];
		$course_directory = $course_info['path'];

		$icons_path = api_get_path(SYS_COURSE_PATH).$course_directory.'/document/icons/';
		$icons_thumbnail_path = api_get_path(SYS_COURSE_PATH).$course_directory.'/document/icons/thumbnail/';
		if(file_exists($icons_path.$filename)){
			unlink($icons_path.$filename);
			unlink($icons_thumbnail_path.$grey_filename);
			unlink($icons_thumbnail_path.$filename);
		}
		$dh  = opendir($icons_path);
		while (false !== ($filename = readdir($dh))) {
			if($filename === '.' || $filename === '..' || is_dir($icons_path.$filename)) {continue;}     

			$pos = strpos($filename, "_grey");
			if ($pos === false) {
				$files[] = $filename;
			}	
		}
		$file_count = sizeof($files);
		$counter_check = $file_count + 1;
		natsort($files);
		echo '<script>
			$(function () {
				var theme_color = $("#theme_color").val();					
				var image_div_height = $(".scroll-pane1").prop("scrollHeight");
				
				 $(".scroll-pane1").jScrollPane(
					{
					 verticalDragMinHeight: 100,
					 verticalDragMaxHeight: 100				 
					}
				 );
				 var pane = $(".scroll-pane1");
				 var api = pane.data("jsp");
				 api.scrollBy(100,image_div_height);

				 $(".jspDrag").css("background","#"+theme_color);   
			});
			</script>';
		echo '<div class="scroll-pane1">';
		foreach($files as $filename) {
			$filename = api_convert_encoding($filename,'UTF-8',api_get_system_encoding());	
			if(substr($filename,0,6) == 'dokeos'){
				$css_margin = "margin-top:15px;";
				$title_margin = "";
			}
			else {
				$css_margin = '';
				$title_margin = "margin-top:15px;";
			}
			$thumbnailSrc = api_get_path(WEB_COURSE_PATH).$course_directory.'/document/icons/thumbnail/'.$filename; 
		?>
			<div class="mediabig_button four_buttons_div rounded grey_border float_l" style="margin: 10px;">
			<div style="padding: 5px; " class="sectioncontent_template"> 
					<div class="images-thumb_new" style="<?php echo $css_margin; ?>">
						<a href="#" title="<?php echo $filename; ?>" class="icon_display" id="<?php echo $thumbnailSrc; ?>">
							<img border="0" title="<?php echo $filename; ?>" src="<?php echo $thumbnailSrc; ?>" id="<?php echo $filename; ?>" />
						</a>
					</div>
					<div class="images-bottom">                            
						<div class="images-title" style="<?php echo $title_margin; ?>">
							<a href="<?php echo $href; ?>" title="<?php echo $filename; ?>" class="load-image">
								<?php echo $filename; ?>
							</a>
						</div>
						<div class="images-delete" style="<?php echo $title_margin; ?>">
							<a class="icon_delete_class" id="<?php echo $filename; ?>" title="<?php echo get_lang('AreYouSureToDelete'); ?>" href="#"><img src="<?php echo api_get_path(WEB_IMG_PATH).'edit_delete_22.png'; ?>" title="<?php echo get_lang('Delete'); ?>" /></a>
						</div>
					</div>                     
			</div>
		</div>
		<?php	
		}
		echo '</div>';
	break;
	case 'update_score' :

		$TBL_SCENARIO_STEPS = Database :: get_course_table(TABLE_SCENARIO_STEPS);	
		$TBL_SCENARIO_ACTIVITY = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY);
		$TBL_SCENARIO_ACTIVITY_VIEW = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY_VIEW);
		$TBL_FACE2FACE = Database :: get_course_table(TABLE_FACE_2_FACE);	

		$step_id = intval($_GET['step_id']);
		$user_id = intval($_GET['user_id']);
		$face2face_id = intval($_GET['face2face_id']);
		$score = floatval($_GET['score']);
		$status = Database::escape_string($_GET['status']);
		$view_cnt = intval($_GET['view_cnt']);

		$sql = "SELECT max_score, ff_type FROM $TBL_FACE2FACE WHERE id = ".$face2face_id." AND step_id = ".$step_id;
		$res = Database::query($sql, __FILE__, __LINE__);
		$max_score = Database::result($res , 0, 0);
		$ff_type = Database::result($res , 0, 1);
		
		if($ff_type == 2) {
			if($score < $max_score){
				$status = '';
			}
			else {
				$status = 'completed';
			}
		}
		else {
			if($status == 'Y'){
				$status = 'completed';
			}
			else {
				$status = '';
			}
		}

		$sql = "SELECT id FROM $TBL_SCENARIO_ACTIVITY WHERE step_id = ".$step_id." AND activity_ref = ".$face2face_id." AND activity_type = 'face2face'";
		$res = Database::query($sql, __FILE__, __LINE__);
		$id = Database::result($res,0,0);

		$sql = "SELECT id FROM $TBL_SCENARIO_ACTIVITY_VIEW WHERE activity_id = ".$id." AND user_id = ".$user_id." AND step_id = ".$step_id;
		$res = Database::query($sql, __FILE__, __LINE__);
		$num_rows = Database::num_rows($res);
		$view_id = Database::result($res,0,0);		
		if($num_rows == 0){	
			$sql = "INSERT INTO $TBL_SCENARIO_ACTIVITY_VIEW(activity_id, step_id, user_id, view_count, score, status) VALUES(".$id.",".$step_id.",".$user_id.", ".$view_cnt.", ".$score.", '".$status."')";
                        
		}
		else {
			$sql = "UPDATE $TBL_SCENARIO_ACTIVITY_VIEW SET score = ".$score;					
			$sql .= ", status = '".$status."', view_count = ".$view_cnt." WHERE activity_id = ".$id." AND step_id = ".$step_id." AND user_id = ".$user_id;
		}
		echo $status;
		Database::query($sql, __FILE__, __LINE__);
	break;
	case 'update_comment' :

		$TBL_SCENARIO_STEPS = Database :: get_course_table(TABLE_SCENARIO_STEPS);	
		$TBL_SCENARIO_ACTIVITY = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY);
		$TBL_SCENARIO_ACTIVITY_VIEW = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY_VIEW);
		$TBL_FACE2FACE = Database :: get_course_table(TABLE_FACE_2_FACE);	

		$step_id = $_GET['step_id'];
		$user_id = $_GET['user_id'];
		$face2face_id = $_GET['face2face_id'];
		$score = $_GET['score'];	
		$comment = $_GET['comment'];
		$status = $_GET['status'];

		$sql = "SELECT max_score, ff_type FROM $TBL_FACE2FACE WHERE id = ".$face2face_id." AND step_id = ".$step_id;
		$res = Database::query($sql, __FILE__, __LINE__);
		$max_score = Database::result($res , 0, 0);
		$ff_type = Database::result($res , 0, 1);
		
		if($ff_type == 2) {
			if($score < $max_score){
				$status = '';
			}
			else {
				$status = 'completed';
			}
		}
		else {
			if($status == 'Y'){
				$status = 'completed';
			}
			else {
				$status = '';
			}
		}

		$sql = "SELECT id FROM $TBL_SCENARIO_ACTIVITY WHERE step_id = ".$step_id." AND activity_ref = ".$face2face_id." AND activity_type = 'face2face'";
		$res = Database::query($sql, __FILE__, __LINE__);
		$id = Database::result($res,0,0);

		$sql = "SELECT id FROM $TBL_SCENARIO_ACTIVITY_VIEW WHERE activity_id = ".$id." AND user_id = ".$user_id." AND step_id = ".$step_id;
		$res = Database::query($sql, __FILE__, __LINE__);
		$num_rows = Database::num_rows($res);
		$view_id = Database::result($res,0,0);
		
		if($num_rows == 0){	
			$sql = "INSERT INTO $TBL_SCENARIO_ACTIVITY_VIEW(activity_id, step_id, user_id, score, comment, status) VALUES(".$id.",".$step_id.",".$user_id.",  ".$score.", '".Database::escape_string($comment)."', '".$status."')";
		}
		else {
			$sql = "UPDATE $TBL_SCENARIO_ACTIVITY_VIEW SET score = ".$score;
			$sql .= ", comment = '".Database::escape_string($comment)."'";
			$sql .= ", status = '".$status."' WHERE activity_id = ".$id." AND step_id = ".$step_id." AND user_id = ".$user_id;
		}
		echo $status;
		Database::query($sql, __FILE__, __LINE__);

	break;
	
}