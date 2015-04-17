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

api_protect_course_script();
$htmlHeadXtra[] = '<script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-1.4.2.min.js" type="text/javascript" language="javascript"></script>';
$htmlHeadXtra[] = '<script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-ui/js/jquery-ui-1.8.1.custom.min.js" type="text/javascript"></script>';
$htmlHeadXtra[] = '<link rel="stylesheet" type="text/css" href="http://code.jquery.com/ui/1.10.1/themes/base/jquery-ui.css" />';
$htmlHeadXtra[] = '<link rel="stylesheet" type="text/css" href="css/main.css" />';
$htmlHeadXtra[] = '<style>
					.ui-dialog-titlebar-close:hover,
					.ui-dialog-titlebar-close:focus {
						background-color:transparent !important;
						border:none !important;
					   
					}
                    </style>';
$htmlHeadXtra[] = '<script language="javascript">

$(function() {    
			$(".cb-enable").click(function(){
					var parent = $(this).parents(".switch");
					$(".cb-disable",parent).removeClass("selected");
					$(this).addClass("selected");
					$("#checkbox2").attr("checked",true);
				});
				$(".cb-disable").click(function(){
					var parent = $(this).parents(".switch");
					$(".cb-enable",parent).removeClass("selected");
					$(this).addClass("selected");
					$("#checkbox2").attr("checked",false);
				});
            $("#delete_introtext").live("click",function(){
					$("#dialog_box").dialog({
						open: function(event, ui) {  
								jQuery(".ui-dialog-titlebar-close").css("width","85px");
								jQuery(".ui-dialog-titlebar-close").html("<span style=\"float:right;margin-right:5px;\">'.get_lang("CloseX").'</span>");								
							},
						   title: "'.get_lang("DeleteIntrotext").'",
						  width: 500,
						  height: 250,
						  modal: false,
						  resizable: false,
						  draggable: false,
						  buttons: {
								"'.get_lang("No").'" : function() {
									   $(this).dialog("close");
								 },
								"'.get_lang("Yes").'": function() { 
									 
									 $.ajax({
													  type: "GET",
													  url: "'.api_get_path('WEB_CODE_PATH').'course_home/update_table.php?action=delete_introtext",
													  success: function(data){	
                                                                                                          window.location.assign("'.api_get_path(WEB_COURSE_PATH) . $_course['path'] .'/index.php'.$param.'")
                                                                                                                  
													  }
												  });
								 }
								 
						   }
						  });
				});
            });
</script>';

$nameTools = "Course Scenario";
Display :: display_tool_header($nameTools);
echo '<div class="actions">';

echo '<a id="delete_introtext" style="cursor:hand;cursor:pointer;">' . Display::return_icon('pixel.gif', get_lang('RemoveIntroductionText'), array('class' => 'toolactionplaceholdericon toolactiondelete'))  . get_lang('RemoveIntroductionText') . '</a>';
echo '</div>';

echo '<div id="content">'; 

$course_info = api_get_course_info(api_get_course_id());
$course_code = $course_info['id'];
$TBL_TOOL_INTRO = Database :: get_course_table(TABLE_TOOL_INTRO);

$sql = "SELECT intro_text FROM $TBL_TOOL_INTRO WHERE id = 'course_homepage'";
$res = Database::query($sql, __FILE__, __LINE__);
$intro_text = Database::result($res, 0, 0);

$sql = "SELECT intro_text FROM $TBL_TOOL_INTRO WHERE id = 'active_scenario'";
$res = Database::query($sql, __FILE__, __LINE__);
$active_scenario = Database::result($res, 0, 0);

if($active_scenario == 'dynamic'){
	$selected_dynamic = 'selected';
	$selected_static = '';
	$checked = 'checked';
}
else {
	$selected_dynamic = '';
	$selected_static = 'selected';
	$checked = '';
}

$editorConfig = array(
	'ToolbarSet' => 'LearningPathDocuments', 'Width' => '100%', 'Height' => '350', 'FullPage' => true,
	'CreateDocumentDir' => '',
	'CreateDocumentWebDir' => api_get_path(WEB_COURSE_PATH).api_get_course_path().'/document/',
	'BaseHref' => api_get_path(WEB_COURSE_PATH).api_get_course_path().'/document/'
); 

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'add'){	

	$content = $_POST['static_scenario'];
	$chk_active_scenario = $_POST['field2'];

	//$document_content = Security::remove_XSS(stripslashes(api_html_entity_decode($content)));
	$document_content = $content;
	if($chk_active_scenario == 'on'){
		$active_scenario = 'dynamic';		
	}
	else {
		$active_scenario = 'static';
	}

	$sql = "REPLACE $TBL_TOOL_INTRO SET id = 'course_homepage' , intro_text = '".Database::escape_string($document_content)."'";
    Database::query($sql,__FILE__,__LINE__);

	$sql = "REPLACE $TBL_TOOL_INTRO SET id = 'active_scenario' , intro_text = '".$active_scenario."'";
    Database::query($sql,__FILE__,__LINE__);
	echo '<h3 style="border-bottom:1px solid #cccccc; padding-bottom:10px; margin-top:0px;">'.get_lang("Preview").'</h3>';
	echo '<div id="courseintroduction">';
	echo $document_content;
	echo '</div>';
}
else {
echo '<form id="add_static_scenario" name="add_static_scenario" method="POST" action="'.api_get_self().'?action=add">';
echo api_disp_html_area('static_scenario', $intro_text, '', '', null, $editorConfig);

echo '</br>';
/*echo get_lang("ActiveScenario");
echo '</br>';
echo '<span class="field switch">
		<label class="cb-enable '.$selected_dynamic.'"><span>'.get_lang("FromBlender").'</span></label>
		<label class="cb-disable '.$selected_static.'"><span>'.get_lang("ImageBased").'</span></label>
		<span style="display:none;"><input type="checkbox" id="checkbox2" class="checkbox" name="field2" '.$checked.' /></span>
	</span>';*/

/*echo '<p><input type="radio" name="modality" class="modality" id="modality1" value="1" checked /><label for="modality1">'.get_lang('Online').'</label></p>
	  <p><input type="radio" name="modality" class="modality" id="modality2" value="2" /><label for="modality2">'.get_lang('Paper').'</label></p>';*/
echo '<br>';
echo '<div class="pull-bottom"><button type="submit" id="submit" class="save">' . get_lang('Validate') . '</button></div>';

echo '</form>';
echo '</div>';
}
echo '<div id="dialog_box" style="display: none;"><br><br><table border="0" align="center" width="95%"><tr><td align="center"><img src="../img/dokeos_question.png"></td><td>'.get_lang("AreYouSure").get_lang("ToDeleteIntrotext").'</td></tr></table></div>';
// Display the footer
Display::display_tool_footer();
?>