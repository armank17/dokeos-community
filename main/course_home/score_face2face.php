<?php //$id: $
/* For licensing terms, see /dokeos_license.txt */
/**
*
*	@package dokeos.exercise
* 	@author Julio Montoya Armas Added switchable fill in blank option added
* 	@version $Id: exercise_show.php 22256 2009-07-20 17:40:20Z ivantcholakov $
*
* 	@todo remove the debug code and use the general debug library
* 	@todo use the Database:: functions
* 	@todo small letters for table variables
*/

// Name of the language file that needs to be included.
$language_file[] = 'course_home';

// including the global dokeos file
require_once '../inc/global.inc.php';
require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';
require_once api_get_path(LIBRARY_PATH).'course.lib.php';

$nameTools = "Course Scenario";

$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-1.4.2.min.js" language="javascript"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/js/jquery-ui-1.8.1.custom.min.js"  language="javascript"></script>';
$htmlHeadXtra[] = '<link rel="stylesheet" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/css/ui-lightness/jquery-ui-1.8.1.custom.css" />';
$htmlHeadXtra[] = '<link rel="stylesheet" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/colorbox/colorbox.css" />';
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/colorbox/jquery.colorbox.js" language="javascript"></script>';
//$htmlHeadXtra[] = '<script src="course_scenario.js.php" type="text/javascript" language="javascript"></script>';
$htmlHeadXtra[] = '<script>
	  $(document).ready(function (){		
		$("div.label").attr("style","width: 100%;text-align:left");
		$("div.row").attr("style","width: 100%;text-align:left");
		$("div.formw").attr("style","width: 100%;text-align:left");                              
		$("div.ui-accordion-content").attr("style","height: auto;text-align:left;");
		$("#accordion").accordion(
			{ active: 0, collapsible: true}
		).one("click", function(){
				parent.$.colorbox.resize({
				width: "1022px",
				height: "800px"
			});
		});
		
	  });
</script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/customInput.jquery.js" language="javascript"></script>';
$htmlHeadXtra[] = '<script type="text/javascript">        
			// Run the script on DOM ready:

			$(function(){
						try {
				$("input").customInput();
						} catch(e){}			
			});</script>';
Display::display_tool_header($nameTools);

echo '<script language="javascript">

$(document).ready(function(){
	$("#studentview").live("click",function() {
						window.location.href = "'.api_get_path(WEB_COURSE_PATH).api_get_course_id().'/index.php";
				   });

	$(".user_score").live("change",function(){
		var id = $(this).attr("id");

		var strtmp = id.split("_");		
		var step_id = strtmp[2];
		var user_id = strtmp[3];
		var face2face_id = strtmp[4];
		var score = $("#user_score_"+step_id+"_"+user_id+"_"+face2face_id).val();
		
		$.ajax({
			  type: "GET",
			  url: "update_table.php?action=update_score&step_id="+step_id+"&user_id="+user_id+"&face2face_id="+face2face_id+"&score="+score,
			  success: function(data){	
				if(data == "completed"){
					$("label[for=check-"+step_id+"-"+user_id+"-"+face2face_id+"]").removeClass("unchecked");	
					$("label[for=check-"+step_id+"-"+user_id+"-"+face2face_id+"]").addClass("checked");					
				}
				else {
					if(score == 0){
						$("label[for=check-"+step_id+"-"+user_id+"-"+face2face_id+"]").removeClass("checked");	
						$("label[for=check-"+step_id+"-"+user_id+"-"+face2face_id+"]").removeClass("unchecked");
					}
					else {
						$("label[for=check-"+step_id+"-"+user_id+"-"+face2face_id+"]").removeClass("checked");					
						$("label[for=check-"+step_id+"-"+user_id+"-"+face2face_id+"]").addClass("unchecked");
					}
				}
			  }
		  });  

	});

	$(".checkbox").change(function() {
		var check_id = $(this).attr("id");

		var strtmp = check_id.split("-");		
		var step_id = strtmp[1];
		var user_id = strtmp[2];
		var face2face_id = strtmp[3];
		var score = 0;
				
		var status = "N";
		var view_cnt = $("#hid_viewcount_"+step_id+"_"+user_id+"_"+face2face_id).val();

        if(view_cnt == 0){
			status = "N";
			$("label[for=check-"+step_id+"-"+user_id+"-"+face2face_id+"]").addClass("unchecked");
			$("#hid_viewcount_"+step_id+"_"+user_id+"_"+face2face_id).val(1);
		}
		else if(view_cnt == 1){
			status = "Y";
			$("label[for=check-"+step_id+"-"+user_id+"-"+face2face_id+"]").removeClass("unchecked");
			$("label[for=check-"+step_id+"-"+user_id+"-"+face2face_id+"]").addClass("checked");
			$("#hid_viewcount_"+step_id+"_"+user_id+"_"+face2face_id).val(2);
		}
		else if(view_cnt >= 2){
			status = "N";
			$("label[for=check-"+step_id+"-"+user_id+"-"+face2face_id+"]").removeClass("checked");
			$("#hid_viewcount_"+step_id+"_"+user_id+"_"+face2face_id).val(0);
		}

		var final_view_cnt = $("#hid_viewcount_"+step_id+"_"+user_id+"_"+face2face_id).val();
		$.ajax({
			  type: "GET",
			  url: "update_table.php?action=update_score&step_id="+step_id+"&user_id="+user_id+"&face2face_id="+face2face_id+"&score="+score+"&status="+status+"&view_cnt="+final_view_cnt,
			  success: function(data){	
					if(data == "completed"){
						$("label[for=check-"+step_id+"-"+user_id+"-"+face2face_id+"]").addClass("checked");
					}
			  }
		  });  
    });

	$(".comment_ff, .comment_ff_text, .comment_ff_subtext").focus(function() {
		if($(this).val() == "'.get_lang("YourCommentHere").'"){
			$(this).val("");
			$(this).css("color","#000");
		}
		else {
			var id = $(this).attr("id");
			
			var strtmp = id.split("-");		
			var step_id = strtmp[1];
			var user_id = strtmp[2];
			var face2face_id = strtmp[3];
			var score = 0;
			
			var comment = $("#hid-"+step_id+"-"+user_id+"-"+face2face_id).val();
			
			$(this).val(comment);
		}
		$(this).addClass("comment_ff_text");
    });

	$(".comment_ff, .comment_ff_text, .comment_ff_subtext").blur(function() {				

		var id = $(this).attr("id");		
		var strtmp = id.split("-");		
		var step_id = strtmp[1];
		var user_id = strtmp[2];
		var face2face_id = strtmp[3];
		var score = 0;
		
		var comment = $("#comment-"+step_id+"-"+user_id+"-"+face2face_id).val();
		var status = "N";
		if($("#check-"+step_id+"-"+user_id+"-"+face2face_id).is(":checked")) {
			status = "Y";
		}
		else {
			status = "N";
		}
		
		
		$.ajax({
			  type: "GET",
			  url: "update_table.php?action=update_comment&step_id="+step_id+"&user_id="+user_id+"&face2face_id="+face2face_id+"&score="+score+"&comment="+comment+"&status="+status,
			  success: function(data){	

				 if(comment == ""){
				
					$("#comment-"+step_id+"-"+user_id+"-"+face2face_id).removeClass("comment_ff_text");
					$("#comment-"+step_id+"-"+user_id+"-"+face2face_id).addClass("comment_ff");
					$("#comment-"+step_id+"-"+user_id+"-"+face2face_id).val("'.get_lang("YourCommentHere").'");					
					$("#comment-"+step_id+"-"+user_id+"-"+face2face_id).css("color","#CCC");
				}
				else {
					$("#comment-"+step_id+"-"+user_id+"-"+face2face_id).removeClass("comment_ff_text");
					$("#comment-"+step_id+"-"+user_id+"-"+face2face_id).addClass("comment_ff_subtext");					
				}
				$("#hid-"+step_id+"-"+user_id+"-"+face2face_id).val(comment);
				if(comment.length > 30){
				var upd_comment = comment.substring(0,30) + " ...";
				$("#comment-"+step_id+"-"+user_id+"-"+face2face_id).val(upd_comment);
				}
			  }
		  });  
		 
    });
});
</script>';

echo '<div class="actions">';
//echo '<a id="studentview" href="#">' . Display::return_icon('pixel.gif', get_lang('ScenarioBlender'), array('class' => 'toolactionplaceholdericon toolactionback'))  . get_lang('ScenarioBlender') . '</a>';
//echo '<a href="course_scenario.php?'.api_get_cidReq().'">' . Display::return_icon('pixel.gif', get_lang('ScenarioBlender'), array('class' => 'toolactionplaceholdericon toolactionNextface2face'))  . get_lang('ScenarioBlender') . '</a>';
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'user/user.php?' . api_get_cidReq() . '">' . Display::return_icon('pixel.gif', get_lang('Users'), array('class' => 'toolactionplaceholdericon toolactionuser')) . ' ' . get_lang('Users') . '</a>';
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'group/group.php?' . api_get_cidreq() . '">' . Display::return_icon('pixel.gif', get_lang('Group'), array('class' => 'toolactionplaceholdericon toolactiongroup')) . get_lang("Group") . '</a> ';

echo '</div>';

echo '<div id="content">';

global $_course;

$TBL_FACE2FACE = Database :: get_course_table(TABLE_FACE_2_FACE);
$TBL_SCENARIO_ACTIVITY = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY);
$TBL_SCENARIO_ACTIVITY_VIEW = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY_VIEW);

$complete_user_list = CourseManager :: get_user_list_from_course_code($_course['id'],true,$_SESSION['id_session']);	
$user_size = sizeof($complete_user_list);

echo '<div id="accordion" style ="margin-right: 10px; float:left;width:100%;">';
$sql = "SELECT * FROM $TBL_FACE2FACE";
$res = Database::query($sql, __FILE__, __LINE__);
while($row = Database::fetch_array($res)){
	//echo $row['name'];
	$face2face_id = $row['id'];
	$step_id = $row['step_id'];
	$min_score = $row['max_score'];
	$ff_type = $row['ff_type'];

	echo '<h3><a href="#">'.$row['name'].'</a></h3>';		

	echo '<div id="cont-acordion" style="height: auto!important;width:94%;">';

	if($ff_type == 1){
	echo '<br><table class="face_table" border="1" cellpadding="5" cellspacing="5">';
	echo '<th align="left" width="25%">'.get_lang("LastName").'</th><th align="left" width="25%">'.get_lang("FirstName").'</th><th align="center" width="10%">'.get_lang("Done").'</th><th align="center" width="35%">'.get_lang("Comment").'</th></tr>';
	}
	else {
	echo '<br><table class="face_table" border="1" cellpadding="5" cellspacing="5">';
	echo '<th align="left" width="25%">'.get_lang("LastName").'</th><th align="left" width="25%">'.get_lang("FirstName").'</th><th align="center" width="10%">'.get_lang("Done").'</th><th align="center" width="35%">'.get_lang("ScoreF2F").'</th></tr>';
	}
	$cnt = 1;
	if($user_size > 0){
		foreach ($complete_user_list as $index => $user) {
			if($user['status'] == 5){
				$sql_score = "SELECT view.score as user_score,view.status as status,view.comment as comment,view.view_count as count FROM $TBL_SCENARIO_ACTIVITY act, $TBL_SCENARIO_ACTIVITY_VIEW view WHERE act.id = view.activity_id AND act.step_id = view.step_id AND act.activity_type = 'face2face' AND act.activity_ref = ".$face2face_id." AND view.step_id = ".$step_id." AND view.user_id = ".$user['user_id'];
				$res_score = Database::query($sql_score);
				$num_score = Database::num_rows($res_score);
				$score = Database::result($res_score,0,0);
				$status = Database::result($res_score,0,1);
				$comment = Database::result($res_score,0,2);
				$view_count = Database::result($res_score,0,3);

				if(empty($comment)){
					$comment = get_lang("YourCommentHere");
					$class_comment = "comment_ff";
				}
				else if($comment == get_lang("YourCommentHere")){
					$comment = get_lang("YourCommentHere");
					$class_comment = "comment_ff";
				}
				else {
					$hid_comment = $comment;
					$len_comment = strlen($comment);
					if($len_comment > 30){
						$comment = substr($comment,0,30)." ...";
						$class_comment = "comment_ff_subtext";
					}
					else {
					$class_comment = "comment_ff_subtext";
					}
				}
				
				if($cnt%2 == 0 ){
					$class = "class = row_even";
				}
				else {
					$class = "class = row_odd";
				}

				if($status == 'completed'){
					$checked = "checked";
					if($ff_type == 2 && $score == 0){
						$score = $min_score;
					}
				}
				else {
					if($ff_type == 2 && $score == 0){
						$checked = "";
					}
					else if($ff_type == 1 && $num_score == 0){
						$checked = "";
						$view_count = 0;
					}
					else if($ff_type == 1 && $view_count == 0){
						$checked = "";
					}
					else if($ff_type == 1 && $view_count == 1){
						$checked = "unchecked";
					}
					else if($ff_type == 1 && $view_count == 2){
						$checked = "checked";
					}
					else {
						$checked = "unchecked";
					}
				}				

				echo '<tr '.$class.'><td>'.$user['lastname'].'</td><td>'.$user['firstname'].'</td>';				
				if($ff_type == 1){
					echo '<td style="padding-left:33px;"><input id="check-'.$step_id."-".$user['user_id']."-".$face2face_id.'" class="checkbox" type="checkbox" name="foo" value="'.$user['user_id'].'" '.$checked.' /><label for="check-'.$step_id."-".$user['user_id']."-".$face2face_id.'" class="'.$checked.'">&nbsp;</label><input type="hidden" name="hid_viewcount_'.$step_id."_".$user['user_id']."_".$face2face_id.'" id="hid_viewcount_'.$step_id."_".$user['user_id']."_".$face2face_id.'" value="'.$view_count.'"></td>';
				}
				else {
					echo '<td style="padding-left:33px;"><input id="check-'.$step_id."-".$user['user_id']."-".$face2face_id.'" class="checkbox" type="checkbox" name="foo" value="'.$user['user_id'].'" '.$checked.' disabled /><label for="check-'.$step_id."-".$user['user_id']."-".$face2face_id.'" class="'.$checked.'">&nbsp;</label><input type="hidden" name="hid_viewcount_'.$step_id."_".$user['user_id']."_".$face2face_id.'" id="hid_viewcount_'.$step_id."_".$user['user_id']."_".$face2face_id.'" value="'.$view_count.'"></td>';
				}
				if($ff_type == 1){
					echo '<td align="center"><textarea id="comment-'.$step_id.'-'.$user['user_id'].'-'.$face2face_id.'" name="comment-'.$step_id.'-'.$user['user_id'].'-'.$face2face_id.'" maxlength="500" class="'.$class_comment.'" />'.$comment.'</textarea>';
					echo '<input type="hidden" name="hid-'.$step_id.'-'.$user['user_id'].'-'.$face2face_id.'" id="hid-'.$step_id.'-'.$user['user_id'].'-'.$face2face_id.'" value="'.$hid_comment.'">';
				}
				else {
					echo '<td  align="center"><div>		
					<select class="user_score" name="user_score" id="user_score_'.$step_id.'_'.$user['user_id'].'_'.$face2face_id.'" style="width:75px;">';
					for($i=0;$i<=20;$i++){
						if($i == $score){
							$selected = ' selected ';
						}
						else {
							$selected = '';
						}
						echo '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
					}
					echo '</select></div>';
				}
				echo '</td></tr>';
			}
		}
	}
	else {
		echo '<tr><td colspan="4">'.get_lang("NoStudentsEnrolled").'</td></tr>';
	}
	echo '</table><br>';	
	echo '</div>';
}

echo '</div>';


echo '</div>';
Display::display_footer();	

?>