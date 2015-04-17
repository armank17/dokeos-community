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
$language_file[] = 'widgets';
$language_file[] = 'chat';
$use_anonymous = true;

// Inlcuding the global initialization file.
require_once '../../main/inc/global.inc.php';
$htmlHeadXtra[] = '<script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-1.4.2.min.js" type="text/javascript" language="javascript"></script>'; //jQuery
$htmlHeadXtra[] = '<script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-ui/js/jquery-ui-1.8.1.custom.min.js" type="text/javascript"></script>';
$htmlHeadXtra[] = '<link rel="stylesheet" type="text/css" href="'. api_get_path(WEB_LIBRARY_PATH) .'javascript/jquery-ui/css/ui-lightness/jquery-ui-1.8.1.custom.css" />';
//$htmlHeadXtra[] = '<link rel="stylesheet" type="text/css" href="css/demo.css" />';
// $htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/customInput.jquery.js" language="javascript"></script>';
echo '<style type="text/css" id="page-css">
	/* Styles specific to this particular page */
	.scroll-pane1
	{
		width: 578px;
		height: 350px;
		overflow: auto;
	}
	.horizontal-only
	{
		height: auto;
		max-height: 350px;
	}
	.jspTrack
	{
		background: #DCDCDC;
		border:1px solid #DCDCDC;
		position: relative;
	}
	.jspDrag
	{
		background: #009933;
		position: relative;
		top: 0;
		left: 0;
		cursor: pointer;
	}
	.jspVerticalBar
	{
		position: absolute;
		top: 0;
		right: 0;
		width: 20px;
		height: 100%;
		background: red;
	}
</style>';
echo '<script src="'.api_get_path(WEB_CODE_PATH).'appcore/library/jquery/jquery.upload/js/jquery.min.js"></script>';
echo '<script type="text/javascript" src="script/jquery.mousewheel.js"></script>';
echo '<script type="text/javascript" src="script/jquery.jscrollpane.min.js"></script>';
echo '<script>
		$(document).ready(function(){
			var theme_color = $("#default_step_color").val();
			$(".scroll-pane1").jScrollPane(
				{
				 verticalDragMinHeight: 100,
				 verticalDragMaxHeight: 100				 
				}
			 );
			$(".jspDrag").css("background",theme_color); 
		});
	</script>';

$rowIndex = $_GET['rowIndex'];
$colIndex = $_GET['colIndex'];
$param_id = $_GET['param'];
list($activity_id, $step_id, $activity_type) = explode("_",$param_id);

if($activity_type == 'face2face'){
	echo '<script>
		$(document).ready(function(){
			$("#facetoface_resourses").trigger("click");
		});
	</script>';
}

echo '<div id="drag">';
echo '<div id="left">';
echo '<div id="navigation">
    <ul class="top-level">
        <li class="active" id="doc_resourses"><a href="#">'.api_convert_encoding(get_lang("Documents"),"UTF-8",api_get_system_encoding()).'</a></li>
        <li id="page_resourses"><a href="#">'.api_convert_encoding(get_lang("Pages"),"UTF-8",api_get_system_encoding()).'</a></li>
        <li id="quiz_resourses"><a href="#">'.api_convert_encoding(get_lang("Quizzes"),"UTF-8",api_get_system_encoding()).'</a></li>        
        <li id="exam_resourses"><a href="#">'.api_convert_encoding(get_lang("Exams"),"UTF-8",api_get_system_encoding()).'</a></li>
        <li id="module_resourses"><a href="#">'.api_convert_encoding(get_lang("Modules"),"UTF-8",api_get_system_encoding()).'</a></li>
        <li id="assign_resourses"><a href="#">'.api_convert_encoding(get_lang("Assignment"),"UTF-8",api_get_system_encoding()).'</a></li>
        <li id="survey_resourses"><a href="#">'.api_convert_encoding(get_lang("Survey"),"UTF-8",api_get_system_encoding()).'</a></li>
        <li id="facetoface_resourses"><a href="#">'.api_convert_encoding(get_lang("FacetoFace"),"UTF-8",api_get_system_encoding()).'</a></li>
    </ul>
</div>';
echo '</div>';
echo '<div id="right"><div class="scroll-pane1">';

if($activity_type != 'face2face'){

$TBL_DOCUMENT = Database :: get_course_table(TABLE_DOCUMENT);
$TABLE_ITEMPROPERTY = Database::get_course_table(TABLE_ITEM_PROPERTY);

$session_condition = api_get_session_condition(api_get_session_id(), true, true);
$sql = "SELECT *
				FROM  ".$TABLE_ITEMPROPERTY."  AS last, ".$TBL_DOCUMENT."  AS docs
				WHERE docs.id = last.ref
				AND docs.path LIKE '/%'
				AND docs.path NOT LIKE '/%/%'
				AND last.tool = '".TOOL_DOCUMENT."'
				AND last.lastedit_type != '".DocumentAddedFromLearnpath."'
				AND docs.filetype <> 'folder'
				AND last.visibility <> 2 $session_condition";

$res = Database::query($sql, __FILE__, __LINE__);
$num_rows = Database::num_rows($res);

echo '<table class="new_table">';
echo '<th>'.api_convert_encoding(get_lang("Resources"),'UTF-8',api_get_system_encoding()).'</th><th>'.api_convert_encoding(get_lang("Select"),'UTF-8',api_get_system_encoding()).'</th>';
$i = 1;
if($num_rows == 0){
	echo '<tr><td colspan="2">'.get_lang("NoDocuments").'</td></tr>';
}
while($row = Database::fetch_array($res)) {
	if(($i%2) == 0){
		$class = "class='row_odd'";
	}
	else {
		$class = "class='row_even'";
	}

		
	//echo '<tr '.$class.'><td width="90%" class="doc_class" id="sel_'.$row['id'].'">'.api_convert_encoding($row['title'],'UTF-8',api_get_system_encoding()).'</td><td class="doc_class" id="radio_'.$row['id'].'"><img src="images/bg-thumb.png"></td></tr>';
	
	echo '<tr '.$class.'><td width="90%" class="doc_class" id="sel_'.$row['id'].'">'.api_convert_encoding($row['title'],'UTF-8',api_get_system_encoding()).'</td><td align="center" width="10%"><input type="radio" id="radio_'.$row['id'].'" name="selDoc" class="regular-radio doc_class" value="'.$row['id'].'" '.$checked.' ><label for="radio_'.$row['id'].'"></label></td></tr>';
	//echo '<input type = "radio" name = "selDoc" class="doc_class" value="'.$row['id'].'" /></td><td>'.$row['title'].'</td></tr>';
	$i++;
}
echo '</table>';
}
echo '</div></div>';

echo '<input type="hidden" name="rowIndex" id="rowIndex" value="'.$rowIndex.'">';
echo '<input type="hidden" name="colIndex" id="colIndex" value="'.$colIndex.'">';
echo '<input type="hidden" name="param_id" id="param_id" value="'.$param_id.'">';
echo '</div>';

?>