<?php // $Id: course_home.php 22294 2009-07-22 19:27:47Z iflorespaz $

/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) Dokeos SPRL

	For a full list of contributors, see "credits.txt".
	For licensing terms, see "dokeos_license.txt"

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	See the GNU General Public License for more details.

	http://www.dokeos.com
==============================================================================
*/

/**
==============================================================================
*         HOME PAGE FOR EACH COURSE
*
*	This page, included in every course's index.php is the home
*	page. To make administration simple, the teacher edits his
*	course from the home page. Only the login detects that the
*	visitor is allowed to activate, deactivate home page links,
*	access to the teachers tools (statistics, edit forums...).
*
*	@package dokeos.course_home
==============================================================================
*/

// Name of the language file that needs to be included.
$language_file[] = 'course_home';
$use_anonymous = true;

// Inlcuding the global initialization file.
require_once '../../main/inc/global.inc.php';

api_protect_course_script(true);

$htmlHeadXtra[] = '<link rel="stylesheet" href="'.api_get_path(WEB_CODE_PATH).'appcore/library/jquery/jquery.upload/css/bootstrap.min.css">';
$htmlHeadXtra[] = '<script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-1.7.2.min.js" type="text/javascript"></script>';
$htmlHeadXtra[] = '<script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-ui-1.8.18.min.js" type="text/javascript" language="javascript"></script>';
//$htmlHeadXtra[] = '<script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-1.5.1.min.js" type="text/javascript" language="javascript"></script>';
//$htmlHeadXtra[] = '<script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-ui/js/jquery-ui-1.8.1.custom.min.js" type="text/javascript"></script>';
//$htmlHeadXtra[] = '<script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-ui.min.js" type="text/javascript"></script>';
//$htmlHeadXtra[] = '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>';
//$htmlHeadXtra[] = '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js"></script>';
//$htmlHeadXtra[] = '<script src="http://code.jquery.com/ui/1.10.1/jquery-ui.js" type="text/javascript"></script>';
$htmlHeadXtra[] = '<link rel="stylesheet" type="text/css" href="'. api_get_path(WEB_LIBRARY_PATH) .'javascript/jquery-ui/css/ui-lightness/jquery-ui-1.8.1.custom.css" />';
//$htmlHeadXtra[] = '<link rel="stylesheet" type="text/css" href="http://code.jquery.com/ui/1.10.1/themes/base/jquery-ui.css" />';
//$htmlHeadXtra[] = '<link rel="stylesheet" type="text/css" href="css/course_scenario.css" />';
$htmlHeadXtra[] = '<link rel="stylesheet" type="text/css" href="css/main.css" />';
$htmlHeadXtra[] = '<link rel="stylesheet" type="text/css" href="css/slider.css" />';
$htmlHeadXtra[] = '<script src="js/jscolor.js" type="text/javascript" language="javascript"></script>';
$htmlHeadXtra[] = '<script src="js/jquery.jeditable.js" type="text/javascript" language="javascript"></script>';
$htmlHeadXtra[] = '<script src="course_scenario.js.php" type="text/javascript" language="javascript"></script>';
$htmlHeadXtra[] = '<script src="js/script.js" type="text/javascript" language="javascript"></script>';
//$htmlHeadXtra[] = '<script src="js/jquery.doubleScroll.js" type="text/javascript" language="javascript"></script>';
$htmlHeadXtra[] =  '<link rel="stylesheet" type="text/css" href="' . api_get_path(WEB_CODE_PATH) . 'appcore/library/jquery/select2/select2.css" />';
$htmlHeadXtra[] =  '<script type="text/javascript" language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'jwplayer/jwplayer.js"></script>';
$htmlHeadXtra[] =  '<script type="text/javascript" language="javascript" src="'.api_get_path(WEB_CODE_PATH).'appcore/library/jquery/select2/select2.js"></script>';
$htmlHeadXtra[] = '<script src="js/slider_test.js" type="text/javascript" language="javascript"></script>';
$htmlHeadXtra[] = '<style>
					.ui-dialog-titlebar-close:hover,
					.ui-dialog-titlebar-close:focus {
						background-color:transparent !important;
						border:none !important;
					   
					}
				</style>';

$nameTools = "Course Scenario";
Display :: display_tool_header($nameTools);

$mycourseid = api_get_course_id();

if (!empty($mycourseid) && $mycourseid != -1)
{
	if (api_get_setting('allow_course_theme') == 'true')
	{
		$mycoursetheme = api_get_course_setting('course_theme', null, true);
	}
	if(empty($mycoursetheme)) {
		$mycoursetheme = api_get_setting('stylesheets');
	}
}
else {
		$mycoursetheme = api_get_setting('stylesheets');
}

$theme_color = get_theme_color($mycoursetheme);

echo '<style>
.ui-slider { position: relative; text-align: left; background:#FFF;}
.ui-slider .ui-slider-handle { position: absolute; z-index: 2; height: 23px;width:70px; cursor: default;background:'.$theme_color.'; top:-4px; }
.ui-slider .ui-slider-range { position: absolute; z-index: 1; font-size: .7em; display: block; border: 0; background-position: 5px 5px;background:#FFF; }
.scenario_content_slider {
  width: 800px;
  height: 17px;
  margin: 1px;
  background: #FFF;
  border:none;
  position: relative;
  border-radius:0px;
}
.ui-widget-content {
	background:#FFF;
}
.ui-slider-horizontal {
	background:#FFF;
}
#content {
background:#ffffff !important;
}
</style>';
echo '<script language="javascript">

function changeColor(obj, color) {
	var id = obj.id;	
	var new_color = document.getElementById(id).value;

	var str = id.split("_");
	document.getElementById("icondiv_"+str[1]).style.borderColor = "#"+new_color;

	$(document).ready(function(){	

		$.ajax({
		  type: "GET",
		  url: "update_table.php?action=update_border&colIndex="+str[1]+"&border_color="+new_color,
		  success: function(data){	
			 
		  }
	  });
	});
}
$(document).ready(function(){
               //$(".double-scroll").doubleScroll();
				
  				   $("#studentview").live("click",function() {
						window.location.href = "'.api_get_path(WEB_COURSE_PATH).api_get_course_id().'/index.php";
				   });				   
            });
</script>';

echo '<div class="actions custom-linkr">';
//echo '<a id="studentview" href="#">' . Display::return_icon('pixel.gif', get_lang('ScenarioBlender'), array('class' => 'toolactionplaceholdericon toolactionback'))  . get_lang('ScenarioBlender') . '</a>';
//echo '<a href="score_face2face.php?'.api_get_cidReq().'">' . Display::return_icon('pixel.gif', get_lang('ScoreFace2Face'), array('class' => 'toolactionplaceholdericon toolactionface2face'))  . get_lang('ScoreFace2Face') . '</a>';
echo '<a id="delete_scenario" href="#">' . Display::return_icon('pixel.gif', get_lang('DeleteScenario'), array('class' => 'toolactionplaceholdericon toolactiondelete_scenario')). get_lang('DeleteScenario') . '</a>';
echo '<a id="preview_scenario" href="#">' . Display::return_icon('pixel.gif', get_lang('Preview'), array('class' => 'toolactionplaceholdericon toolactionsearch')). get_lang('Preview') . '</a>';
//echo '<a href="static_scenario.php?'.api_get_cidReq().'">' . Display::return_icon('pixel.gif', get_lang('ImageBasedScenario'), array('class' => 'toolactionplaceholdericon toolactionimagescenario')). get_lang('ImageBasedScenario') . '</a>';
echo '</div>';

echo '<div id="content">'; 

$course_info = api_get_course_info(api_get_course_id());
$course_code = $course_info['id'];
$course_directory = $course_info['path'];
$icons_path = api_get_path(SYS_COURSE_PATH).$course_directory.'/document/icons/';
$icons_thumbnail_path = api_get_path(SYS_COURSE_PATH).$course_directory.'/document/icons/thumbnail/';
if(!is_dir($icons_path)){

	$perm = api_get_setting('permissions_for_new_directories');
	$perm = octdec(!empty($perm)?$perm:'0770');
	mkdir($icons_path);
    chmod($icons_path, $perm);

	if(!is_dir($icons_thumbnail_path)){
		mkdir($icons_thumbnail_path);
	    chmod($icons_thumbnail_path, $perm);
	}

	$dir = "icons/";
	$dh  = opendir($dir);
	while (false !== ($filename = readdir($dh))) {
		if($filename === '.' || $filename === '..') {continue;} 
		
		if(copy("icons/".$filename,$icons_path.$filename)) {
			//echo 'file copied--';
		}
		if(copy("icons/".$filename,$icons_thumbnail_path.$filename)) {
			//echo 'file copied--';
		}
	}
}

$mycourseid = api_get_course_id();

if (!empty($mycourseid) && $mycourseid != -1)
{
	if (api_get_setting('allow_course_theme') == 'true')
	{
		$mycoursetheme = api_get_course_setting('course_theme', null, true);
	}
	if(empty($mycoursetheme)) {
		$mycoursetheme = api_get_setting('stylesheets');
	}
}
else {
		$mycoursetheme = api_get_setting('stylesheets');
}

$theme_color = get_theme_color($mycoursetheme);
$TBL_SCENARIO_STEPS = Database :: get_course_table(TABLE_SCENARIO_STEPS);
$TBL_SCENARIO_ACTIVITY = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY);	
$chk_hide_border = 0;
$chk_hide_image = 0;
$session_condition = api_get_session_condition(api_get_session_id(), false);
$sql = "SELECT * FROM $TBL_SCENARIO_STEPS $session_condition ORDER BY step_created_order";
$res = Database::query($sql, __FILE__, __LINE__);
$num_cols = Database::num_rows($res);
$i = 1;
$largest_row = 0;
$step_border = array();
while($row = Database::fetch_array($res)) {
	$step_id = $row['id'];
	$chk_hide_border = $row['hide_border'];
	$chk_hide_image = $row['hide_image'];
	if($chk_hide_border == 1){
		$checked = "checked";
	}
	else {
		$checked = "";
	}

	if($chk_hide_image == 1){
		$image_checked = "checked";
	}
	else {
		$image_checked = "";
	}
	$step_hidden_id[$i] = $row['id'].'_'.$row['step_created_order'];
	$step_created_order = $row['step_created_order'];
	$step_completion_option = $row['step_completion_option'];
	$step_completion_percent = $row['step_completion_percent'];
	if(strpos($step_completion_option,'@') !== false){
		list($option, $sub_option) = split("@",$step_completion_option);
		$step_completion = $option. ' : '.$step_completion_percent.' %';
	}
	else if($step_completion_option == 'None' || $step_completion_option == '') {
		$step_completion = get_lang("Free");
	}
	else {
		$step_completion = $step_completion_option.' : '.$step_completion_percent.' %';
	}
	$step_prereq = $row['step_prerequisite'];
	
	if($step_prereq == 'None'){
		$prereq_name = 'None';
	}
	else {
		$sql_pre = "SELECT step_name FROM $TBL_SCENARIO_STEPS WHERE id = ".$step_prereq;
		$res_req = Database::query($sql_pre, __FILE__,__LINE__);
		$prereq_name = Database::result($res_req, 0 , 0);
	}

	$j = 0;
	$foo[$i][$j] = $row['step_icon'];
	$j++;
	$foo[$i][$j] = $row['step_name'];
	$j++;
	$foo[$i][$j] = $row['step_border'];
	$j++;
	$step_border[$i] = $row['step_border'];
	$step_order[$i] = $row['step_created_order'];
	$hide_border[$i] = $row['hide_border'];
	$hide_image[$i] = $row['hide_image'];
	//$foo[$i][$j] = $prereq_name;
	$foo[$i][$j] = $step_prereq;
	$j++;
	//$foo[$i][$j] = $row['step_completion_option'];
	$foo[$i][$j] = $step_completion.'&nbsp;<div><span class="edit_criteria" id="'.$step_id.'" style="padding-left:90px;">'.Display::return_icon('pixel.gif', get_lang('EditCriteria'), array('class' => 'actionplaceholdericon actionediticon')).'</span>&nbsp;<span class="delete_criteria" id="'.$step_id.'" >'.Display::return_icon('pixel.gif', get_lang('DeleteCriteria'), array('class' => 'actionplaceholdericon actiondeleteicon')).'</span></div>';
	$j++;
        $session_condition = api_get_session_condition(api_get_session_id(), true);
	$sql_activity = "SELECT * FROM $TBL_SCENARIO_ACTIVITY WHERE step_id = ".$step_id." $session_condition ORDER BY activity_created_order";
	$res_activity = Database::query($sql_activity, __FILE__, __LINE__);
	while($row_activity = Database::fetch_array($res_activity)) {
		$activity_id = $row_activity['id'];
		$activity_step_id = $row_activity['step_id'];
		$activity_type = $row_activity['activity_type'];
		$activity_created_order = $row_activity['activity_created_order'];
		$foo[$i][$j] = $row_activity['activity_name'].'&nbsp;<div><span class="edit_activity" style="padding-left:90px;">'.Display::return_icon('pixel.gif', get_lang('EditActivity'), array('class' => 'actionplaceholdericon actionediticon')).'</span>&nbsp;<span class="delete_activity" id="delete_'.$activity_id.'_'.$activity_step_id.'_'.$activity_created_order.'" >'.Display::return_icon('pixel.gif', get_lang('DeleteActivity'), array('class' => 'actionplaceholdericon actiondeleteicon')).'<input class="hid_act_class" type="hidden" name="activity_id" id="activity_'.$step_order[$i].'_'.$activity_created_order.'" value="'.$activity_id.'_'.$activity_step_id.'_'.$activity_type.'" /></span></div>';
		$j++;
	}
	$foo[$i][$j] = '<div class="center"><img class="sample" title="'.get_lang("MoreActivities").'" alt="plus" src="images/add.png" style="display:none;" /><button id="plus" class="savemore" name="plus">'.get_lang("MoreActivities").'</button></div>';
	$j++;
	$i++;	
	if($j > $largest_row){
		$largest_row = $j;
	}
}
$m = 0;
for($k=0;$k<$largest_row;$k++){
	if($k == 0){
		$foo[0][$k] = '<div style="margin-top:15px"><button id="addcolumn" class="save" style="width:110px;height:115px;" name="addcolumn">'.get_lang("AddStep").'</button></div>';
	}
	if($k == 1){
		$foo[0][$k] = '<hr style="margin:0px !important;color:#CCC;"><div style="clear:both">'.get_lang('Step').'</div><div style="margin-top:0px;"><input type="checkbox" class="show_image_class" name="show_step_image" id="show_step_image" value="'.$hide_image[$i].'" '.$image_checked.' />&nbsp;'.get_lang('HideImage').'</div>';
	}
	if($k == 2){
		$foo[0][$k] = '<hr style="margin:0px !important;color:#CCC;"><div style="clear:both; margin-top:10px;">'.get_lang('Border').'</div><div style="margin-top:10px;"><input type="checkbox" class="show_border_class" name="show_step_border" id="show_step_border" value="'.$hide_border[$i].'" '.$checked.' />&nbsp;'.get_lang('HideBorder').'</div>';
	}
	if($k == 3){
		$foo[0][$k] = '<hr style="margin:0px !important;color:#CCC;"><div style="clear:both; margin-top:15px;">'.get_lang('Prerequisite').'</div>';
	}
	if($k == 4){
		$foo[0][$k] = '<hr style="margin:0px !important;color:#CCC;"><div style="clear:both; margin-top:15px;">'.get_lang('Completion').'</div>';
	}
	if($k > 4){
		$m++;
		$foo[0][$k] = '<hr style="margin:0px !important;color:#CCC;"><div style="clear:both; margin-top:15px;">'.get_lang('Activity')." ".$m.'</div> ';
	}	
}

$session_condition = api_get_session_condition(api_get_session_id(), false);
$sql = "SELECT * FROM $TBL_SCENARIO_ACTIVITY $session_condition";
$res = Database::query($sql, __FILE__, __LINE__);
$num_rows = Database::num_rows($res);

$row = $largest_row;
$col = $i;

if($row == 0){
	$row = 6;
	$col = 2;
	$foo[0][0] = '<div style="margin-top:15px;"><button id="addcolumn" class="save" style="width:110px;height:115px;" name="addcolumn">'.get_lang("AddStep").'</button></div>';
	$foo[0][1] = '<hr style="margin:0px !important;color:#CCC;margin-bottom:5px;"><div style="clear:both">'.get_lang('Step').'</div><div style="margin-top:5px;"><input type="checkbox" class="show_border_class" name="show_step_image" id="show_step_image" value="'.$hide_image[$i].'" '.$image_checked.' />&nbsp;'.get_lang('HideImage').'</div>';
	$foo[0][2] = '<hr style="margin:0px !important;color:#CCC;"><div style="clear:both; margin-top:13px !important;">'.get_lang('Border').'</div><div style="margin-top:10px;"><input type="checkbox" class="show_border_class" name="show_step_border" id="show_step_border" value="'.$hide_border[$i].'" '.$checked.' />&nbsp;'.get_lang('HideBorder').'';
	$foo[0][3] = '<hr style="margin:0px !important;color:#CCC;"><div style="clear:both; margin-top:10px;">'.get_lang('Prerequisite').'</div>';
	$foo[0][4] = '<hr style="margin:0px !important;color:#CCC;"><div style="clear:both; margin-top:15px;">'.get_lang('Completion').'</div>';
	$foo[0][5] = '<hr style="margin:0px !important;color:#CCC;"><div style="clear:both; margin-top:15px;">'.get_lang('Activity1').'</div>';

	
}

/*echo'<table border="1" width="700">';

for( $i = 0; $i < $row; $i++ )
{
    echo'<tr>';
    for( $j = 0; $j < $col; $j++ ) {        
            echo '<td>'.$foo[$j][$i].'</td>';        
    }
    echo'</tr>';
}

echo'</table>';*/

/*echo '<table class="table2" id="mtable">';
for($i=0;$i<=$num_rows;$i++){
	echo '<tr>';
	for($j=0;$j<=$num_cols;$j++){
		echo '<td>';
	}
	echo '</tr>';
}*/
$screen_width = 600;

echo '<table class="sampletable1" width="100%">';
//echo '<tr><td style="font-size:18px;">'.get_lang("ScenarioAuthoring").'</td><td style="font-size:18px;" align="right"><button class="savenew" name="studentview" id="studentview">'.get_lang("StudentView").'</button></td></tr>';
//echo '</table>';
//echo '<tr><td width="89%"><div style="width:'.$screen_width.'px;overflow:auto;border: 2px solid red;">';
echo '<tr><td width="80%"><div id="content-slider" class="scenario_content_slider" style="display:none;"></div><div class="outer"><div class="inner"><div id="content-scroll"><div id="content-holder">';
//echo '<tr><td width="80%"><div class="outer"><div class="inner"><div class="double-scroll">';

/*echo '<table class="table2" id="mtable">';
echo '<tr class="icon_class"><td class="heading">Icon</td><td><div id="icondiv_1" class="icon_block"></div></td></tr>';
echo '<tr class="step_class"><td class="heading">Step Name</td><td><div class="div_txt" id="step_1">click to Update Text Here</div></td></tr>';
echo '<tr><td class="heading">Border</td><td><input class="color" id="stepcolor_1" value="66ff00" onchange="changeColor(this, this.color)"></td></tr>';
echo '<tr><td class="heading">Prerequisite</td><td>None</td></tr>';
echo '<tr class="completion_class"><td class="heading">Completion</td><td>&nbsp;</td></tr>';
echo '<tr><td class="heading">&nbsp;</td><td><div><img class="sample" alt="plus" src="../img/add_32.png"></div></td></tr>';
echo '</table>';*/

//$icon_path = api_get_path(WEB_PATH).'main/course_home/icons/';
$course_info = api_get_course_info(api_get_course_id());
$course_code = $course_info['id'];
$course_directory = $course_info['path'];
$icon_path = api_get_path(WEB_COURSE_PATH).$course_directory.'/document/icons/thumbnail/';
echo'<table class="table2" id="mtable">';

for( $i = 0; $i < $row; $i++ )
{
	if($i == 0){
		$tr_class = "class = 'icon_class'";
	}
	else if($i == 1){
		$tr_class = "class = 'step_class'";
	}
	else if($i == 2){
		$tr_class = "class = 'border_class'";
	}
	else if($i == 3){
		$tr_class = "class = 'prerequisite_class'";
	}
	else if($i == 4){
		$tr_class = "class = 'completion_class'";
	}
	else if($i > 4) {
		$tr_class = "class = 'activity_class'";
	}
	else {
		$tr_class = '';
	}
    echo'<tr '.$tr_class.'>';
    for( $j = 0; $j < $col; $j++ ) { 
		
		if($j == 0){
			echo '<td class="heading">'.$foo[$j][$i].'<div class="clear"></div></td>';
		}		
		else {

			if($num_cols == '0'){
				echo '<td>&nbsp;</td>';
			}
			else {
				if($i == 0){
					
					if($hide_border[$j] == 1){
						$border_px = "border:0px";
					}
					else {
						$border_px = "";
					}
					if($hide_image[$j] == 1){
						$display = "display:none";
					}
					else {
						$display = "display:block";
					}
					/*if(substr($foo[$j][$i],0,6) == 'dokeos'){
						$css_margin = "margin-top:18px;";
					}
					else {
						$css_margin = '';
					}
					$td_values = '<div id="icondiv_'.$j.'" class="icon_block" style="border-color:'.$step_border[$j].';"><img style="display: block; margin: 0 auto;vertical-align:middle;text-align:center;'.$css_margin.'" src="'.$icon_path.$foo[$j][$i].'" /></div>';*/
					$td_values = '<div id="icondiv_'.$j.'" class="icon_block" style="'.$border_px.';border-color:'.$step_border[$j].';"><div class="icon_block_120_80"><img style="'.$display.'; margin: 0 auto;vertical-align:top;text-align:center;" src="'.$icon_path.$foo[$j][$i].'?t='.time().'" /></div></div>';
				}
				else if($i == 1) {
					$td_values = '<div class="div_txt" id="step_'.$j.'">'.$foo[$j][$i].'</div>&nbsp;<span class="edit_step" id="step_'.$j.'" style="padding-left:85px;">'.Display::return_icon('pixel.gif', get_lang('EditStep'), array('class' => 'actionplaceholdericon actionediticon')).'</span>&nbsp;<span class="delete_step" id="delete_'.$step_hidden_id[$j].'" >'.Display::return_icon('pixel.gif', get_lang('DeleteStep'), array('class' => 'actionplaceholdericon actiondeleteicon')).'</span>';
				}
				else if($i == 2){
					$td_values = '<input class="color" id="stepcolor_'.$j.'" value="'.str_replace("#","",$foo[$j][$i]).'" onchange="changeColor(this, this.color)">';
				}
				else if($i == 3){
					/*if(empty($foo[$j][$i])) {
					$td_values = '<div id="prereq_select_'.$j.'" class="prereq">'.get_lang("ClickToSelectMe").'</div>';
					}
					else {
					$td_values = '<div id="prereq_select_'.$j.'" class="prereq">'.api_convert_encoding($foo[$j][$i],api_get_system_encoding(),'UTF-8').'</div>';
					}*/
					$prereq_option = get_prereq_steps($j);					
					$td_values = '<div id="prereq_select_'.$j.'" ><select style="width:125px;" id="prereq1_select_'.$j.'">';
					foreach($prereq_option as $id=>$key){
						if($id == $foo[$j][$i]){
							$selected = 'selected';
						}
						else {
							$selected = '';
						}
						$td_values .= '<option value="'.$id.'" '.$selected.'>'.$key.'</option>';
					}
					$td_values .= '</select></div>';
				}	
				else if($i == 4){				
					$td_values = '<div id="completion_class">'.$foo[$j][$i].'</div>';				
				}	
				else {
					$td_values = $foo[$j][$i];
				}

				if(empty($foo[$j][$i]) || ($foo[$j][$i] == '<div><img class="sample" title="'.get_lang("MoreActivities").'" alt="plus" src="images/add.png" /></div>')){
					echo '<td class="tdbg">'.$td_values.'</td>';   
				}
				else {						
					echo '<td class="tdstatic">'.$td_values.'</td>';   										
				}
			}
		}
                 
    }
    echo '</tr>';
}

echo '</table>';
echo '</div>';
echo '</div></div></div></td>';
//echo '<td valign="top"><div align="center" style="margin-top:30px;"><img id="addcolumn" src="images/symbol_add.png"><br>'.get_lang("AddStepToScenario").'<input type="hidden" name="del_activity" id="del_activity" value="0" /><input type="hidden" name="del_step" id="del_step" value="0" /><input type="hidden" name="default_step_color" id="default_step_color" value="'.$theme_color.'" /></div></td>';
echo '<input type="hidden" name="del_activity" id="del_activity" value="0" /><input type="hidden" name="del_criteria" id="del_criteria" value="0" /><input type="hidden" name="del_step" id="del_step" value="0" /><input type="hidden" name="hid_hide_border" id="hid_hide_border" value="'.$chk_hide_border.'"><input type="hidden" name="hid_hide_image" id="hid_hide_image" value="'.$chk_hide_image.'"><input type="hidden" name="default_step_color" id="default_step_color" value="'.$theme_color.'" /><input type="hidden" name="hid_numcols" id="hid_numcols" value="'.$num_cols.'">';
echo '</tr>';
echo '</table>';
echo '<div class="scenario_dialog" style="display:none;"></div>';
echo '</div>';

echo '</div>';
echo '<div id="dialog_box" style="display: none;"><br><br><table border="0" align="center" width="95%"><tr><td align="center"><img src="../img/dokeos_question.png"></td><td>'.get_lang("AreYouSure").get_lang("ToDeleteScenario").'</td></tr></table></div>';
echo '<div id="step_dialog_box" style="display: none;"><br><br><table border="0" align="center" width="95%"><tr><td align="center"><img src="../img/dokeos_question.png"></td><td>'.get_lang("AreYouSureToDeleteStep").'</td></tr></table></div>';
echo '<div id="activity_dialog_box" style="display: none;"><br><br><table border="0" align="center" width="95%"><tr><td align="center"><img src="../img/dokeos_question.png"></td><td>'.get_lang("AreYouSureToDeleteActivity").'</td></tr></table></div>';

function get_theme_color($platform_theme) {
	
	if($platform_theme == 'dokeos2_black_tablet') {
		$theme_color = "#424242";
	}
	else if($platform_theme == 'dokeos2_blue_tablet') {
		$theme_color = "#003C77";
	}
	else if($platform_theme == 'dokeos2_medical_tablet') {
		$theme_color = "#134958";
	}
	else if($platform_theme == 'dokeos2_orange_tablet') {
		$theme_color = "#D66B00";
	}
	else if($platform_theme == 'dokeos2_red_tablet') {
		$theme_color = "#96040B";
	}
	else if($platform_theme == 'dokeos2_tablet') {
		$theme_color = "#1084A7";
	}
	else if($platform_theme == 'redhat_tablet') {
		$theme_color = "#cc0000";
	}
	else if($platform_theme == 'orkyn_tablet') {
		$theme_color = "#1084A7";
	}

	return $theme_color;
}

function get_prereq_steps($j) {        
                $session_condition = api_get_session_condition(api_get_session_id(), true);
		$TBL_SCENARIO_STEPS = Database :: get_course_table(TABLE_SCENARIO_STEPS);				
		$sql = "SELECT id, step_name FROM $TBL_SCENARIO_STEPS WHERE step_created_order < ".$j." $session_condition";
		$res = Database::query($sql, __FILE__, __LINE__);
		$num_rows = Database::num_rows($res);
		$data[0] = get_lang("None");
		while($row = Database::fetch_array($res)) {
			$step_name = $row['step_name'];
			$len = strlen($step_name);
			if($len > 22){
				$stepname = substr($step_name, 0, 22).'...';
			}
			else {
				$stepname = $step_name;
			}
			$data[$row['id']] = $stepname;
		}
		return $data;
}

// Display the footer
Display::display_tool_footer();
?>