<html>
<head>
</head>
<body>
<?php
$language_file[] = 'course_home';

require_once '../../main/inc/global.inc.php';

echo '<script>
$(function() {		

	$("#noactivity").live("click",function(e){
		e.preventDefault();
	});

	$("#face2face_step").live("click",function(){

		$(".scenario_dialog_face2face").show();		

		/*$(".scenario_dialog_face2face").dialog({
							open: function(event, ui) {  
								jQuery(".ui-dialog-titlebar-close").css("width","75px");
								jQuery(".ui-dialog-titlebar-close").html("<span style=\"float:right;margin-right:5px;\">'.get_lang('CloseX').'</span>");  											
							},
							modal: true,
							title: "'.get_lang('NoAccessInWebsite').'",
							width: 520,
							height: 350,
							fluid: true,
							resizable:false
							
		});	*/
	});

	$("#exam_step").live("click",function(){
		$(".scenario_dialog_exam").show();	
	});
});
</script>';

$TBL_SCENARIO_STEPS = Database :: get_course_table(TABLE_SCENARIO_STEPS);
$TBL_SCENARIO_ACTIVITY = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY);

if(isset($_GET['action']) && $_GET['action'] == 'chkprereq'){
	echo '<br><br><div>'.api_convert_encoding(get_lang("ChkPrerequisite"),'UTF-8',api_get_system_encoding())." ".api_convert_encoding($_GET['step_name'],'UTF-8',api_get_system_encoding()).'</div>';
}
else {
$user_id = api_get_user_id();

$step_id = $_GET['step_id'];

$sql = "SELECT step_name FROM $TBL_SCENARIO_STEPS WHERE id = ".$step_id;
$res = Database::query($sql, __FILE__, __LINE__);
$step_name = Database::result($res,0,0);

$sql = "SELECT * FROM $TBL_SCENARIO_ACTIVITY WHERE step_id = ".$step_id." ORDER BY activity_created_order";
$res = Database::query($sql, __FILE__, __LINE__);
$num_rows = Database::num_rows($res);


  echo '<div>';
 
 echo '<table class="new_table">';
 if($num_rows == 0){
	 echo '<tr class="row_even"><td class="textstyle" id="noactivity"><a href="#">'.api_convert_encoding(get_lang('NoActivities'),'UTF-8',api_get_system_encoding()).'</a></td></tr>';
 }
 else {
 while($row = Database::fetch_array($res)) {
	 $activity_name = $row['activity_name'];
	 $activity_id = $row['id'];
	 $activity_type = $row['activity_type'];
	 $activity_ref = $row['activity_ref'];
	 $step_id = $row['step_id'];

	 $activity_link = get_activity_link($activity_type,$activity_ref,$step_id,$activity_id);
	 $activity_user_status = get_user_status($user_id,$activity_id,$step_id);
	 if($activity_user_status == 'completed') {
		 $checked = 'checked';
		 $bullets = 'checked.png';
	 }
	 else {
		 $checked = '';
		 $bullets = 'unchecked.png';
	 }

	 echo '<tr class="row_even" id="quizz_redirect">';
        
	 if($activity_type == 'face2face'){
            echo '<td width="100%" class="textstyle" id="face2face_step"><a href="#"  >'.api_convert_encoding($activity_name,'UTF-8',api_get_system_encoding()).'</a></td>';
	 }
	 else if($activity_type == 'exam' && $activity_user_status == 'completed'){
		echo '<td width="100%" class="textstyle" id="exam_step"><a href="#"  >'.api_convert_encoding($activity_name,'UTF-8',api_get_system_encoding()).'</a></td>';
	 }
	 else {            
            echo '<td width="100%" class="textstyle "><a href="'.$activity_link.'">'.api_convert_encoding($activity_name,'UTF-8',api_get_system_encoding()).'</a></td>';
	 }
	 echo '<td valign="top" width="8%">';
	 echo '<a href="#"><img src="'.api_get_path(WEB_IMG_PATH).$bullets.'" /></a>';
	 /*echo '<div class="squaredOne">
	<input type="checkbox" value="None" id="checkbox_'.$activity_id.'" name="selDoc" '.$checked.' disabled />
	<label for="squaredOne"></label>
</div>';*/
	 //echo '<input type="checkbox" id="checkbox_'.$activity_id.'" name="selDoc" class="regular-checkbox" value="'.$activity_id.'"  '.$checked.' disabled /><label for="checkbox_'.$activity_id.'"></label>';
	 echo '</td></tr>';
 }
 /*echo '<tr class="row_even"><td valign="top" width="5%">-</td><td width="85%" class="textstyle">Anjan Great a very long text to check the page drop down</td><td valign="top" width="10%"><input type="checkbox" id="checkbox_1" name="selDoc" class="regular-checkbox" value="1" checked /><label for="checkbox_1"></label></td></tr>
 <tr class="row_even"><td valign="top" width="5%">Anjan</td><td width="85%" class="textstyle">Great</td><td valign="top" width="10%"><input type="checkbox" id="checkbox_2" name="selDoc" class="regular-checkbox" value="2" /><label for="checkbox_2"></label></td></tr>
 <tr class="row_even"><td valign="top" width="5%">Anjan</td><td width="85%" class="textstyle">Great super anjan</td><td valign="top" width="10%"><input type="checkbox" id="checkbox_2" name="selDoc" class="regular-checkbox" value="2" /><label for="checkbox_2"></label></td></tr> 
 <tr class="row_even"><td valign="top" width="5%">Anjan</td><td width="85%" class="textstyle">Great</td><td valign="top" width="10%"><input type="checkbox" id="checkbox_2" name="selDoc" class="regular-checkbox" value="2" /><label for="checkbox_2"></label></td></tr>
 <tr class="row_even"><td valign="top" width="5%">Anjan</td><td width="85%" class="textstyle">Great</td><td valign="top" width="10%"><input type="checkbox" id="checkbox_2" name="selDoc" class="regular-checkbox" value="2" /><label for="checkbox_2"></label></td></tr>';*/
 
 }
 echo '</table>';
  echo '<br>';
  echo '<div class="scenario_dialog_face2face" style="display:none;"><b>'.api_convert_encoding(get_lang("Face2FaceNoAccessInWeb"),'UTF-8',api_get_system_encoding()).'</b></div><br>';
echo '<div>'; 
echo '<div class="scenario_dialog_exam" style="display:none;"><b>'.api_convert_encoding(get_lang("OneAttemptAllowedInExam"),'UTF-8',api_get_system_encoding()).'</b></div><br>';
echo '<div>'; 
echo '</div></div>';
}


function get_user_status($user_id, $activity_id, $step_id) {
	$TBL_SCENARIO_ACTIVITY_VIEW = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY_VIEW);
	$sql = "SELECT status FROM $TBL_SCENARIO_ACTIVITY_VIEW WHERE activity_id = ".$activity_id." AND step_id = ".$step_id." AND user_id = ".$user_id;
	$res = Database::query($sql, __FILE__, __LINE__);
	$status = Database::result($res,0,0);
	return $status;
}

function get_activity_link($activity_type,$activity_ref,$step_id,$activity_id) {
	
	$additional_param = '';
	
	if($activity_type == 'document') {
		$TBL_DOCUMENT = Database :: get_course_table(TABLE_DOCUMENT);
		$sql = "SELECT path FROM $TBL_DOCUMENT WHERE id = ".$activity_ref;
		$res = Database::query($sql, __FILE__, __LINE__);
		$path = Database::result($res,0,0);
		$additional_param = "?".api_get_cidReq()."&curdirpath=/&file=".$path."&tool=scenario&ref=".$activity_ref."&step=".$step_id."&activity_id=".$activity_id;
		$activity_link = api_get_path(WEB_PATH).'main/document/showinframes.php'.$additional_param;
	}
        else if($activity_type == 'page') {
		$additional_param = "?module=node&cmd=CourseNode&func=view&nodeId=".$activity_ref."&".api_get_cidReq()."&tool=scenario&ref=".$activity_ref."&step=".$step_id."&activity_id=".$activity_id;
		$activity_link = api_get_path(WEB_PATH).'main/index.php'.$additional_param;
	}
	else if($activity_type == 'quiz') {
		$additional_param = "?".api_get_cidReq()."&exerciseId=".$activity_ref."&step=".$step_id."&activity_id=".$activity_id."&tool=scenario";
		$activity_link = api_get_path(WEB_PATH).'main/exercice/exercice_submit.php'.$additional_param;
	}
	else if($activity_type == 'exam') {
		$TBL_EXAM = Database :: get_course_table(TABLE_EXAM);
		$sql = "SELECT quiz_id FROM $TBL_EXAM WHERE id = ".$activity_ref;
		$res = Database::query($sql, __FILE__, __LINE__);
		$quiz_id = Database::result($res,0,0);
		$additional_param = "?module=evaluation&cmd=Player&func=index&".api_get_cidReq()."&quizId=".$quiz_id."&examId=".$activity_ref."&step_id=".$step_id."&tool=scenario";		
		$activity_link = api_get_path(WEB_PATH).'main/index.php'.$additional_param;
	}
	else if($activity_type == 'module') {
		$additional_param = "?module=author&cmd=Player&func=view&".api_get_cidReq()."&lpId=".$activity_ref."&step_id=".$step_id."&tool=scenario";
		//$activity_link = api_get_path(WEB_PATH).'main/newscorm/lp_controller.php'.$additional_param;
		$activity_link = api_get_path(WEB_PATH).'main/index.php'.$additional_param;
	}
	else if($activity_type == 'mindmap') {
		$additional_param = "?".api_get_cidReq()."&view=&action=viewfeedback&id=".$activity_ref;
		$activity_link = api_get_path(WEB_PATH).'main/mindmap/index.php'.$additional_param;
	}
	else if($activity_type == 'survey') {
		$additional_param = "?".api_get_cidReq()."&survey_id=".$activity_ref."&step_id=".$step_id."&activity_id=".$activity_id."&tool=scenario";
		$activity_link = api_get_path(WEB_PATH).'main/survey/preview.php'.$additional_param;
	}
	else if($activity_type == 'forum') {
		$additional_param = "?".api_get_cidReq()."&gidReq=&forum=".$activity_ref;
		$activity_link = api_get_path(WEB_PATH).'main/forum/viewforum.php'.$additional_param;
	}
	else if($activity_type == 'wiki') {
		$additional_param = "?".api_get_cidReq()."&action=showpage&title=&page_id=".$activity_ref;
		$activity_link = api_get_path(WEB_PATH).'main/wiki/index.php'.$additional_param;
	}	
	else if($activity_type == 'assignment') {
		$TBL_WORK = Database :: get_course_table(TABLE_STUDENT_PUBLICATION);
		$sql = "SELECT title FROM $TBL_WORK WHERE id = ".$activity_ref;
		$res = Database::query($sql,__FILE__,__LINE__);
		$curdirpath = Database::result($res,0,0);
		$additional_param = "?".api_get_cidReq()."&action=view_papers&curdirpath=".$curdirpath."&assignment_id=".$activity_ref."&step=".$step_id."&activity_id=".$activity_id;
		$activity_link = api_get_path(WEB_PATH).'main/core/views/work/index.php'.$additional_param;
	}	
	
	return $activity_link;
}
?>
</body>
</html>
