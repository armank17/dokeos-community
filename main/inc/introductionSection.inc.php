<?php
/* For licensing terms, see /dokeos_license.txt */
/**
==============================================================================
 * The INTRODUCTION MICRO MODULE is used to insert and edit
 * an introduction section on a Dokeos Module. It can be inserted on any
 * Dokeos Module, provided a connection to a course Database is already active.
 *
 * The introduction content are stored on a table called "introduction"
 * in the course Database. Each module introduction has an Id stored on
 * the table. It is this id that can make correspondance to a specific module.
 *
 * 'introduction' table description
 *   id : int
 *   intro_text :text
 *
 *
 * usage :
 *
 * $moduleId = XX // specifying the module Id
 * include(moduleIntro.inc.php);
*
*	@package dokeos.include
==============================================================================
*/

include_once(api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
require_once api_get_path(LIBRARY_PATH).'certificatemanager.lib.php';

echo '<script type="text/javascript" src="'.api_get_path('WEB_CODE_PATH').'course_home/js/homeFunctions.js"></script>';
echo '<script type="text/javascript" src="'.api_get_path('WEB_CODE_PATH').'course_home/js/homeModel.js"></script>';
echo '<script type="text/javascript" src="'.api_get_path('WEB_CODE_PATH').'course_home/js/homeController.js"></script>';
echo '<link   type="text/css"        href="'.api_get_path('WEB_CODE_PATH').'course_home/css/home.css" rel="stylesheet"></link>';


echo '<style>
					.ui-dialog-titlebar-close:hover,
					.ui-dialog-titlebar-close:focus {
						background-color:transparent !important;
						border:none !important;
					   
					}
				</style>';
	
echo '<script>
$(function() {	
	 //$("div.scroll-pane").jScrollPane({verticalDragMinHeight: 2});
	 $("#delete_scenario").live("click",function() {
		 $.ajax({
			  type: "GET",
			  url: "'.api_get_path('WEB_CODE_PATH').'course_home/update_table.php?action=delete_scenario",
			  success: function(data){	
				  window.location.reload();
			  }
		  });  
	 });

	 $("#editscenario").live("click",function() {		 
		 window.location.href = "'.api_get_path('WEB_CODE_PATH').'course_home/course_scenario.php?'.api_get_cidReq().'";
	 });

	 $("#edit_static_scenario").live("click",function() {		 
		 window.location.href = "'.api_get_path('WEB_CODE_PATH').'course_home/static_scenario.php?'.api_get_cidReq().'";
	 });

	 $("#closeDialog").live("click",function() {
		$(".scenario_dialog").dialog("close");
	});

	$("#quizz_redirect").live("click",function(){
		var href = $(this).find("a").attr("href");
                window.location.href = href;
		
	});

	$(".div_block").live("click",function(){
        
		var step_arr = $(this).attr("id");		
		var step = step_arr.split("_");
		var numact = $("#numact_"+step[1]).val();
		var numactlink = $("#numactlink_"+step[1]).val();
		
		if(numact == 1 && numactlink != "" && step[2] == "Y"){
			window.location.href = numactlink;
		}
		else if(step[2] == "Y") {
			$.ajax({
			  type: "GET",
			  url: "'.api_get_path(WEB_CODE_PATH).'/course_home/get_steps_activities.php?step_id="+step[1],
			  success: function(data){	

				  $(".scenario_dialog").html(data);
				  $(".scenario_dialog").dialog({
										open: function(event, ui) {  
											jQuery(".ui-dialog-titlebar-close").css("width","85px");
											jQuery(".ui-dialog-titlebar-close").html("<span style=\"float:right;margin-right:5px;\">'.get_lang('CloseX').'</span>");  											
										},
										modal: true,
										title: step[4],
										width: 520,
										height: 350,
										fluid: true,
										resizable:false
										
										
					});		
			  }
		  });
		}
		else {
			$.ajax({
			  type: "GET",
			  url: "'.api_get_path(WEB_CODE_PATH).'/course_home/get_steps_activities.php?action=chkprereq&step_name="+step[3],
			  success: function(data){	
				  $(".scenario_dialog").html(data);
				  $(".scenario_dialog").dialog({
										open: function(event, ui) {  
											jQuery(".ui-dialog-titlebar-close").css("width","85px");
											jQuery(".ui-dialog-titlebar-close").html("<span style=\"float:right;margin-right:5px;\">'.get_lang('CloseX').'</span>");  											
										},
										modal: true,
										title: "'.get_lang('NoAccess').'",
										width: 520,
										height: 350,
										fluid: true,
										resizable:false
										
										
					});		
			  }
		  });
		}

	 }); 

	 // on window resize run function
$(window).resize(function () {
    fluidDialog();
});

// catch dialog if opened within a viewport smaller than the dialog width
$(document).on("dialogopen", ".ui-dialog", function (event, ui) {
    fluidDialog();
});

function fluidDialog() {
    var $visible = $(".ui-dialog:visible");
    // each open dialog
    $visible.each(function () {
        var $this = $(this);
        var dialog = $this.find(".ui-dialog-content").data("dialog");
        // if fluid option == true
        if (dialog.options.fluid) {
            var wWidth = $(window).width();
            // check window width against dialog width
            if (wWidth < dialog.options.maxWidth + 50) {
                // keep dialog from filling entire screen
                $this.css("max-width", "90%");
            } else {
                // fix maxWidth bug
                $this.css("max-width", dialog.options.maxWidth);
            }
            //reposition dialog
            dialog.option("position", dialog.options.position);
        }
    });

}
});






</script>';

/*
-----------------------------------------------------------
	Constants and variables
-----------------------------------------------------------
*/
$TBL_SCENARIO_STEPS = Database :: get_course_table(TABLE_SCENARIO_STEPS);
$TBL_SCENARIO_ACTIVITY = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY);
$TBL_TOOL_INTRO = Database :: get_course_table(TABLE_TOOL_INTRO);
//$step_icon_path = api_get_path(WEB_PATH).'main/course_home/icons/';
$course_info = api_get_course_info(api_get_course_id());
$course_code = $course_info['id'];
$step_icon_path = api_get_path(WEB_COURSE_PATH).$course_code.'/document/icons/thumbnail/';
global $_user;

$sql = "SELECT intro_text FROM $TBL_TOOL_INTRO WHERE id = 'active_scenario'";
$res = Database::query($sql, __FILE__, __LINE__);
$active_scenario = Database::result($res,0,0);

if(1) {
	$sql = "SELECT intro_text FROM $TBL_TOOL_INTRO WHERE id = 'course_homepage'";
	$res = Database::query($sql, __FILE__, __LINE__);
	$intro_text = Database::result($res,0,0);

	if(empty($intro_text) || $intro_text == '&nbsp;'){
		if(api_is_allowed_to_edit()){
		echo "<div>";
//                echo '<div class="text-scenario-blender">'.get_lang("Scenario").'</div>';
		echo '<div class="introtext textCenter">';		
                echo '<a id="blender" href="'.api_get_path(WEB_CODE_PATH).'course_home/static_scenario.php?'.api_get_cidReq().'">'.get_lang("IntroductionText").'</a>';
//		echo '<button class="save" style=" float: right;    margin-right: -5px;"></button>';		
		echo '</div>';
                
		echo '<div class="clear"></div>';   
		echo "</div>";
	}
	}
	else{
		echo '<div id="courseintroduction">'; 		

		echo $intro_text;

		if(api_is_allowed_to_edit()) {		

			echo '</br></br>';
			//echo '<button class="save" name="edit_static_scenario" id="edit_static_scenario">'.get_lang("EditScenario").'</button>';
			echo '<div class="introtext" style="float:none;text-align:right;"><a id="blender" href="'.api_get_path(WEB_CODE_PATH).'course_home/static_scenario.php?'.api_get_cidReq().'">'.get_lang("ModifyIntroductionText").'</a></div>';
			//echo '</br></br>';
		}
                        echo '</div>';
                        
                }
	}

        
if (api_is_platform_admin()){
    $customMargin = 'margin-top:-13px'; 
    $customButton = 'margin-top:46px; margin-right:-10px; margin-bottom:6px;';
} else {
    $customMargin = 'margin-top:auto !important'; 
    $customButton = 'margin-top:46px; margin-right:-10px; margin-bottom:6px;';
}

if(1 /* $active_scenario == 'dynamic' */){
        $session_condition = api_get_session_condition(api_get_session_id(), false);
	$sql = "SELECT * FROM $TBL_SCENARIO_STEPS $session_condition ORDER BY step_created_order";
	$res = Database::query($sql, __FILE__, __LINE__);
	$num_rows = Database::num_rows($res);

	if($num_rows == 0){
		if(api_is_allowed_to_edit()){
			echo '<div id="" class="custom-dashed">'; 
                        echo '<div class="text-scenario-blender">'.get_lang("Scenario").'</div>';
			//echo '<div class="blender">';			
			//echo '<a id="blender" href="'.api_get_path(WEB_CODE_PATH).'course_home/course_scenario.php?'.api_get_cidReq().'">'.get_lang("EditScenario").'</a>';
			
                        echo '<div class="button-scenario-blender">';
                        echo '<button class="save" name="editscenario" id="editscenario">'.get_lang("EditScenario").'</button>';						
			echo '</div>';
                        
                        //echo '</div>';
			echo '<div class="clear"></div>';   
			echo "</div>";
	}
	}
	else {
        if(api_get_setting('enable_course_scenario') == 'true'){
                            
	echo '<div id="courseintroduction" style="'.$customMargin.'"><div>'; 
	//echo '<div class="courseintro-sectiontitle">'.Display::return_icon('pixel.gif', get_lang('Scenario'), array('class'=>'toolactionplaceholdericon toolactionscenario', 'style'=>'vertical-align:middle')).' '.get_lang('Scenario').'</div>';
	?>

	<div class="main1">
		<div class="inner">
			<?php
			echo '</br>';
			while ($row = Database::fetch_array($res)) {
				$step_border = $row['step_border'];
				$hide_border = $row['hide_border'];
				$hide_image = $row['hide_image'];
				$step_icon = $row['step_icon'];
				$step_id = $row['id'];		
				$step_prerequisite = $row['step_prerequisite'];
				$tmp_step_name = $row['step_name'];
				$sql_act = "SELECT id, activity_type, activity_ref FROM $TBL_SCENARIO_ACTIVITY WHERE step_id = ".$step_id." ORDER BY activity_created_order";
				$res_act = Database::query($sql_act, __FILE__, __LINE__);
				$num_act = Database::num_rows($res_act);

				if($num_act == 1) {				 
					 $activity_id = Database::result($res_act, 0, 0);
					 $activity_type = Database::result($res_act, 0, 1);
					 $activity_ref = Database::result($res_act, 0, 2);

					 $activity_link = get_activity_link($activity_type,$activity_ref,$step_id,$activity_id);
                                         echo ($activity_link);
				}
				else {
					$activity_link = '';
				}

				if($hide_image == 1){
					$display = "display:none";
					$stepname_limit = 80;
				}
				else {
					$display = "display:block";
					$stepname_limit = 35;
				}

				$stepname_len = strlen($tmp_step_name);
				if($stepname_len > $stepname_limit){
					$step_name = substr($tmp_step_name, 0, $stepname_limit).'...';
				}
				else {
					$step_name = $tmp_step_name;
				}

				if($step_prerequisite == 'None' || $step_prerequisite == '') {
					$current_prereq = 'Y';	
					$prereq_stepname = "None";
				}
				else {
					$current_prereq = check_prerequisite($step_id, $step_prerequisite);
					$sql_preqname = "SELECT step_name FROM $TBL_SCENARIO_STEPS WHERE id = ".$step_prerequisite;
					$res_preqname = Database::query($sql_preqname, __FILE__, __LINE__);
					$prereq_stepname = Database::result($res_preqname,0,0);
				}

				if($current_prereq == 'N'){
					$bg_color = 'background:#EEEEEE;';
					$step_border = '#949494';

					$path_parts = pathinfo($step_icon);				
					$step_icon = $path_parts['filename'].'_grey.png';
				}
				else {
					$bg_color = '';
				}	

				if($hide_border == 1){
					$border_px = "0px";
				}
				else {
					$border_px = "3px";
				}
				
				if(substr($step_icon,0,6) == 'dokeos'){
	//				$css_margin = "margin-top:18px;";
									$css_margin = "auto";
				}
				else {
					$css_margin = '';
				}

				echo '<div class="div_block" id="div_'.$step_id.'_'.$current_prereq.'_'.$prereq_stepname.'_'.$step_name.'" style="'.$bg_color.'border:'.$border_px.' solid '.$step_border.'">
								
									<div class="steptext_style" >'.$step_name.'</div><div class="stepimage_style"><img style="'.$display.'; margin:auto;vertical-align:middle;text-align:center;'.$css_margin.'" src="'.$step_icon_path.$step_icon.'?t='.time().'" /></div></div>';

				echo '<input type="hidden" name="num_activity" class="num_activity" id="numact_'.$step_id.'" value="'.$num_act.'"><input type="hidden" name="num_activity_link" class="num_activity_link" id="numactlink_'.$step_id.'" value="'.$activity_link.'">';
			}
			?>
			
		</div>
	</div>
	<?php

	if(api_is_allowed_to_edit()) {	
		echo '<button class="save" style="'.$customButton.'" name="editscenario" id="editscenario">'.get_lang("EditScenario").'</button>';
		//echo '</div>';
		echo '</br></br>';
	}
	echo '</div></div>';
        }
	echo '<div class="scenario_dialog" style="height:30px;display:none;"></div>';
	}
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
		$additional_param = "?".api_get_cidReq()."&exerciseId=".$activity_ref."&step=".$step_id."&activity_id=".$activity_id."&tool=scenario";
		$activity_link = api_get_path(WEB_PATH).'main/index.php?module=node&cmd=CourseNode&func=view&nodeId='.$activity_ref.$additional_param;
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
		$additional_param = "?module=author&cmd=Player&func=view&".api_get_cidReq()."&lpId=".$activity_ref."&step_id=".$step_id."&tool=scenario&activity_id=".$activity_id;
		//$activity_link = api_get_path(WEB_PATH).'main/newscorm/lp_controller.php'.$additional_param;
		$activity_link = api_get_path(WEB_PATH).'main/index.php'.$additional_param;
	}
	else if($activity_type == 'mindmap') {
		$additional_param = "?".api_get_cidReq()."&view=&action=viewfeedback&id=".$activity_ref;
		$activity_link = api_get_path(WEB_PATH).'main/mindmap/index.php'.$additional_param;
	}
	else if($activity_type == 'survey') {
                global $_course;
                $user_id = api_get_user_id();
                $table_survey 			= Database :: get_course_table(TABLE_SURVEY);
                $table_survey_invitation = Database :: get_course_table(TABLE_SURVEY_INVITATION);		
		
                $sql = "SELECT * FROM $table_survey survey, $table_survey_invitation survey_invitation
				WHERE survey_invitation.user = '".Database::escape_string($user_id)."'
				AND survey.code = survey_invitation.survey_code
				AND survey.avail_from <= '".date('Y-m-d H:i:s')."'
				AND survey.avail_till >= '".date('Y-m-d H:i:s')."'
				";
                $result = Database::query($sql, __FILE__, __LINE__);
                
                
                
                while ($row = Database::fetch_array($result,'ASSOC')) {
                    $sql = "SELECT user FROM $table_survey_answer
				    WHERE survey_id = (SELECT survey_id from $table_survey WHERE code ='".Database::escape_string($row['code'])."')";
			$result_answer = Database::query($sql, __FILE__, __LINE__);
			$row_answer = Database::fetch_array($result_answer,'ASSOC');
                        
                        $allinone = ($row['question_per_page'] != 0) ? 'all' : '';
                        $additional_param = "?".api_get_cidReq()."&course=".$_course['sysCode']."&invitationcode=".$row['invitation_code']."&survey_id=".$activity_ref."&step_id=".$step_id."&activity_id=".$activity_id."&tool=scenario";
                        $url_survey = api_get_path(WEB_PATH).'main/survey/fillsurvey'.$allinone.'.php'.$additional_param;;
                        
                        
                }                		
		$activity_link = $url_survey;
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

function check_prerequisite($step_id, $step_prerequisite) {

	$TBL_SCENARIO_ACTIVITY = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY);
	$TBL_SCENARIO_ACTIVITY_VIEW = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY_VIEW);
	$prereq_satisfied = 'Y';

	$sql = "SELECT * FROM $TBL_SCENARIO_ACTIVITY WHERE step_id = ".$step_prerequisite;
	$res = Database::query($sql, __FILE__, __LINE__);
	$num_steps = Database::num_rows($res);
	if($num_steps == 0){
		$prereq_satisfied = 'N';
		return $prereq_satisfied;
	}
	while($row = Database::fetch_array($res)) {
		$sql_view = "SELECT status FROM $TBL_SCENARIO_ACTIVITY_VIEW WHERE step_id = ".$step_prerequisite." AND activity_id = ".$row['id']." AND user_id = ".api_get_user_id();
		$res_view = Database::query($sql_view, __FILE__, __LINE__);
		$num_view = Database::num_rows($res_view);
		if($num_view == 0){
			$prereq_satisfied = 'N';
			return $prereq_satisfied;
		}
		else {
			$status = Database::result($res_view, 0, 0);
			if($status != 'completed'){
				$prereq_satisfied = 'N';
				return $prereq_satisfied;
			}
		}
	}
	return $prereq_satisfied;
}
?>

        
