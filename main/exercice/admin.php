<?php
// $Id: admin.php 21662 2009-06-29 14:55:09Z iflorespaz $

/*
  ==============================================================================
  Dokeos - elearning and course management software

  Copyright (c) 2004-2008 Dokeos SPRL
  Copyright (c) 2003 Ghent University (UGent)
  Copyright (c) 2001 Universite catholique de Louvain (UCL)
  Copyright (c) various contributors

  For a full list of contributors, see "credits.txt".
  The full license can be read in "license.txt".

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; either version 2
  of the License, or (at your option) any later version.

  See the GNU General Public License for more details.

  Contact address: Dokeos, rue du Corbeau, 108, B-1030 Brussels, Belgium
  Mail: info@dokeos.com
  ==============================================================================
 */


/**
 * 	Exercise administration
 * 	This script allows to manage (create, modify) an exercise and its questions
 *
 * 	 Following scripts are includes for a best code understanding :
 *
 * 	- exercise.class.php : for the creation of an Exercise object
 * 	- question.class.php : for the creation of a Question object
 * 	- answer.class.php : for the creation of an Answer object
 * 	- exercise.lib.php : functions used in the exercise tool
 * 	- exercise_admin.inc.php : management of the exercise
 * 	- question_admin.inc.php : management of a question (statement & answers)
 * 	- statement_admin.inc.php : management of a statement
 * 	- answer_admin.inc.php : management of answers
 * 	- question_list_admin.inc.php : management of the question list
 *
 * 	Main variables used in this script :
 *
 * 	- $is_allowedToEdit : set to 1 if the user is allowed to manage the exercise
 * 	- $objExercise : exercise object
 * 	- $objQuestion : question object
 * 	- $objAnswer : answer object
 * 	- $aType : array with answer types
 * 	- $exerciseId : the exercise ID
 * 	- $picturePath : the path of question pictures
 * 	- $newQuestion : ask to create a new question
 * 	- $modifyQuestion : ID of the question to modify
 * 	- $editQuestion : ID of the question to edit
 * 	- $submitQuestion : ask to save question modifications
 * 	- $cancelQuestion : ask to cancel question modifications
 * 	- $deleteQuestion : ID of the question to delete
 * 	- $moveUp : ID of the question to move up
 * 	- $moveDown : ID of the question to move down
 * 	- $modifyExercise : ID of the exercise to modify
 * 	- $submitExercise : ask to save exercise modifications
 * 	- $cancelExercise : ask to cancel exercise modifications
 * 	- $modifyAnswers : ID of the question which we want to modify answers for
 * 	- $cancelAnswers : ask to cancel answer modifications
 * 	- $buttonBack : ask to go back to the previous page in answers of type "Fill in blanks"
 *
 * 	@package dokeos.exercise
 * 	@author Olivier Brouckaert
 * 	@version $Id: admin.php 21662 2009-06-29 14:55:09Z iflorespaz $
 */
include('exercise.class.php');
include('question.class.php');
include('answer.class.php');

// name of the language file that needs to be included
$language_file = array('exercice', 'hotspot', 'create_course');

define('DOKEOS_QUIZGALLERY', true);
define('DOKEOS_EXERCISE', true);

include("../inc/global.inc.php");

include('exercise.lib.php');
$this_section = SECTION_COURSES;

$is_allowedToEdit = api_is_allowed_to_edit();

if (!$is_allowedToEdit) {
    api_not_allowed(true);
}

// allows script inclusions
define(ALLOWED_TO_INCLUDE, 1);

include_once(api_get_path(LIBRARY_PATH) . 'fileUpload.lib.php');
include_once(api_get_path(LIBRARY_PATH) . 'document.lib.php');
require_once '../newscorm/learnpath.class.php';
// Load jquery library
$htmlHeadXtra[] = '<script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/dhtmlwindow.js" type="text/javascript"></script>';
$htmlHeadXtra[] = '<script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/modal.js" type="text/javascript"></script>';
$htmlHeadXtra[] = '<style type="text/css" media="all">@import "' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/modal.css";</style>';
$htmlHeadXtra[] = '<style type="text/css" media="all">@import "' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/dhtmlwindow.css";</style>';
$htmlHeadXtra[] = '<link type="text/css" rel="stylesheet" href="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/colorbox/colorbox.css" />';
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/colorbox/jquery.colorbox.js" language="javascript"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery.validate.js" language="javascript"></script>';
$htmlHeadXtra[] = '<script type="text/javascript">
  $(document).ready(function (){
    $("div.label").attr("style","width: 100%;text-align:left");
    //$("div.row").attr("style","width: 100%;");
    $("div.row").attr("class","formw");
    $("div.formw").attr("style","width: 100%;");    
    if ($("#frm_exe_hotspot_one").length) {
        $("#frm_exe_hotspot_one").validate();
    }
 });
</script>
<style>
    #frm_exe_hotspot_one label.error {display: none !important;}
    .error-message{margin-bottom:10px}
</style>
';
$htmlHeadXtra[] = '<style type="text/css">#courseintro {margin-top: 9px !important;}.blender{margin-top:0px !important;}</style>';
$htmlHeadXtra[] = '<script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery.epiclock.min.js" type="text/javascript" language="javascript"></script>'; //jQuery
if (api_get_setting('show_glossary_in_documents') != 'none' && isset($_GET['viewQuestion']) && $_GET['viewQuestion'] > 0) {
    $htmlHeadXtra[] = '<script language="javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery.highlight.js"></script>';
    if (api_get_setting('show_glossary_in_documents') == 'ismanual') {
//        $htmlHeadXtra[] = '<script language="javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'fckeditor/editor/plugins/glossary/fck_glossary_manual.js"></script>';
        $htmlHeadXtra[] = '<script language="javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/glossary_quiz.js"/></script>';
    } else {
        $htmlHeadXtra[] = '<script language="javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/glossary_quiz.js"/></script>';
    }

    $htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/customInput.jquery.js" language="javascript"></script>';
    $htmlHeadXtra[] = '<script type="text/javascript">
	// Run the script on DOM ready:
	$(function(){
                try {
                    $("input").customInput();
                } catch(e){}
	});
	</script>';
}
// Add the lp_id parameter to all links if the lp_id is defined in the uri
if (isset($_GET['lp_id']) && $_GET['lp_id'] > 0) {
    $lp_id = Security::remove_XSS($_GET['lp_id']);
    $htmlHeadXtra[] = '<script type="text/javascript">
    $(document).ready(function (){
      $("a[href]").attr("href", function(index, href) {
           var param = "lp_id=' . $lp_id . '";
           var is_javascript_link = false;
           var info = href.split("javascript");

           if (info.length >= 2) {
             is_javascript_link = true;
           }
           if ($(this).attr("class") == "course_main_home_button" || $(this).attr("class") == "course_menu_button"  || $(this).attr("class") == "next_button"  || $(this).attr("class") == "prev_button" || is_javascript_link) {
             return href;
           } else {
             if (href.charAt(href.length - 1) === "?")
                 return href + param;
             else if (href.indexOf("?") > 0)
                 return href + "&" + param;
             else
                 return href + "?" + param;
           }
      });
    });
  </script>';
}

// Variable
$learnpath_id = Security::remove_XSS($_GET['lp_id']);
// Lp object
if (isset($_SESSION['lpobject'])) {
    if ($debug > 0)
        error_log('New LP - SESSION[lpobject] is defined', 0);
    $oLP = unserialize($_SESSION['lpobject']);
    if (is_object($oLP)) {
        if ($debug > 0)
            error_log('New LP - oLP is object', 0);
        if ($myrefresh == 1 OR (empty($oLP->cc)) OR $oLP->cc != api_get_course_id()) {
            if ($debug > 0)
                error_log('New LP - Course has changed, discard lp object', 0);
            if ($myrefresh == 1) {
                $myrefresh_id = $oLP->get_id();
            }
            $oLP = null;
            api_session_unregister('oLP');
            api_session_unregister('lpobject');
        } else {
            $_SESSION['oLP'] = $oLP;
            $lp_found = true;
        }
    }
}

// Add the extra lp_id parameter to some links
$add_params_for_lp = '';
if (isset($_GET['lp_id'])) {
    $add_params_for_lp = "&lp_id=" . $learnpath_id;
}

/* * ************************* */
/*  stripslashes POST data  */
/* * ************************* */

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($_POST as $key => $val) {
        if (is_string($val)) {
            $_POST[$key] = stripslashes($val);
        } elseif (is_array($val)) {
            foreach ($val as $key2 => $val2) {
                $_POST[$key][$key2] = stripslashes($val2);
            }
        }
        $GLOBALS[$key] = $_POST[$key];
    }
}

// get vars from GET
if (empty($exerciseId)) {
    $exerciseId = Security::remove_XSS($_GET['exerciseId']);
}
if (empty($newQuestion)) {
    $newQuestion = Security::remove_XSS($_GET['newQuestion']);
}
if (empty($modifyAnswers)) {
    $modifyAnswers = Security::remove_XSS($_GET['modifyAnswers']);
}
if (empty($editQuestion)) {
    $editQuestion = Security::remove_XSS($_GET['editQuestion']);
}
if (empty($modifyQuestion)) {
    $modifyQuestion = Security::remove_XSS($_GET['modifyQuestion']);
}
if (empty($deleteQuestion)) {
    $deleteQuestion = Security::remove_XSS($_GET['deleteQuestion']);
}
if (empty($questionId)) {
    $questionId = $_SESSION['questionId'];
}
if (empty($modifyExercise)) {
    $modifyExercise = Security::remove_XSS($_GET['modifyExercise']);
}
if (empty($viewQuestion)) {
    $viewQuestion = Security::remove_XSS($_GET['viewQuestion']);
   
}
 
$evaluation_link = '';
if (isset($_GET['origin']) && $_GET['origin'] == 'evaluation') {
    $evaluation_link = '&origin=evaluation&examId='.intval($_GET['examId']);
}

$htmlHeadXtra[] = '
    <script src="' . api_get_path(WEB_LIBRARY_PATH) . 'fckeditor/fckeditor.js" type="text/javascript"></script>
    <style type="text/css">
        .TB_Collapse, .TB_ToolbarSet {display:none; !important}
		#Qdiv div, #dragContent div, #answerDiv div {
		  line-height: 25px !important;
		  min-height: 50px;
		 }
		.matching-drag p {
		 margin: 0px;
		 padding: -1px;
		}
		.Qdiv-pb {
			margin-bottom: 6.4px !important;
			margin-left: 1%;
		}
		#answerDiv div {
		  width:99%;
		}
    </style>
';
$htmlHeadXtra[] = '
<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/md5.js" language="javascript"></script>
<script type="text/javascript">
/*<![CDATA[*/
function addNewAnswerMatch(type, origin){

if(origin == \'right\'){
//right side
    var rowCount = $("#table_match_right >tbody >tr").length;
    var rowCount_left = $("#table_match_left >tbody >tr").length;
    var letter = String.fromCharCode((rowCount+1) + 64);
    var nameEditor = \'option[\'+(rowCount+1)+\']\';
    var dataleft = $(".display_option_right").val();
    
    if(dataleft == 0){
       $("#table_match_right > tbody:last").append("<tr style=\"height:87px;\"><td><div class=\"yckoptiondiv\" style=\"display: none;margin-bottom:42px;float: right;\"><textarea id=\"option_"+(rowCount+1)+"\" class=\"option_"+(rowCount+1)+"\" name=\"option["+(rowCount+1)+"]\" cols=\"30\" rows=\"7\" placeholder=\"'.get_lang("TypeHere").'\"></textarea></div><div id=\"option_image_"+(rowCount+1)+"\" class=\"option_image_"+(rowCount+1)+" div_option_image nckoptiondiv\" style=\"display:inline;\"></div><div class=\"nckoptiondiv divfileoption"+(rowCount+1)+" option_file-"+(rowCount+1)+"\" style=\"display:inline;\"><div class=\"nckoptiondiv\"></div><form id=\"option_file-"+(rowCount+1)+"\" enctype=\"multipart\/form-data\" name=\"option_file_"+(rowCount+1)+"\" method=\"post\" > <input id=\"right_input_file_"+(rowCount+1)+"\" type=\"file\" class=\"option_file-"+(rowCount+1)+"\" name=\"option_file_"+(rowCount+1)+"\" /></form><div id=\"option_progress_bar_"+(rowCount+1)+"\" style=\"height:30px; margin-top:5px; border-radius:5px; text-align:center; line-height:28px; font-weight:bold; font-size:14px;\"></div></div></td><td valign=\'top\' style=\'padding-top:10px;\'>"+letter+"</td></tr>");       
    }
    if(dataleft == 1){
      $("#table_match_right > tbody:last").append("<tr style=\"height:87px;\"><td style=\"text-align:right;\"><div class=\"yckoptiondiv\" style=\"display: inline;margin-bottom:42px;float: right;\"><textarea id=\"option_"+(rowCount+1)+"\" class=\"option_"+(rowCount+1)+"\" name=\"option["+(rowCount+1)+"]\" cols=\"30\" rows=\"7\" placeholder=\"'.get_lang("TypeHere").'\"></textarea></div><div id=\"option_image_"+(rowCount+1)+"\" class=\"option_image_"+(rowCount+1)+" div_option_image nckoptiondiv\" style=\"display:none;\"></div><div class=\"nckoptiondiv divfileoption"+(rowCount+1)+" option_file-"+(rowCount+1)+"\" style=\"display:none;\"><div class=\"nckoptiondiv\"></div><form id=\"option_file-"+(rowCount+1)+"\" enctype=\"multipart\/form-data\" name=\"option_file_"+(rowCount+1)+"\" method=\"post\" > <input id=\"right_input_file_"+(rowCount+1)+"\" type=\"file\" class=\"option_file-"+(rowCount+1)+"\" name=\"option_file_"+(rowCount+1)+"\" /></form><div id=\"option_progress_bar_"+(rowCount+1)+"\" style=\"height:30px; margin-top:5px; border-radius:5px; text-align:center; line-height:28px; font-weight:bold; font-size:14px;\"></div></div></td><td valign=\'top\' style=\'padding-top:10px;\'>"+letter+"</td></tr>");
    }
    
  if(dataleft == null){
        $("#table_match_right > tbody:last").append("<tr><td><textarea class=\'answer\' name=\'"+nameEditor+"\'placeholder=\"'.get_lang("TypeHere").'\" id=\'"+nameEditor+"\'></textarea></td><td valign=\'top\' style=\'padding-top:10px;\'>"+letter+"</td></tr>");

        //$(\'textarea[id="option[\'+(rowCount+1)+\']"]\').ckeditor({toolbar:"TestProposedAnswer",width:"350px", height:"90px"});
        loadEditor(nameEditor, "' . api_get_setting('use_default_editor') . '", "matching");
  }
  
    
    $(\'input[name="nb_options"]\').val(rowCount+1);
    var values = {};
    for(t=0;t<rowCount;t++){
        values[t] = $(\'select[id="matches[\'+(t+1)+\']"]\').val();
    }
    for(i=0;i<rowCount_left;i++){
        html_select="<div class=\'row\' style=\'width: 100%;\'><div class=\'label\' style=\'width: 100%;text-align:left\'></div><div class=\'formw\' style=\'width: 100%;\'>";
        html_select+="<select name=\'matches["+(i+1)+"]\' id=\'matches["+(i+1)+"]\'>";
        for(j=0;j<=rowCount;j++){
            letter = String.fromCharCode(65+j);
            if(j+1 == values[i]){
                html_select+="<option selected=\'selected\' value=\'"+(j+1)+"\'>"+letter+" ' . get_lang('MatchesTo') . ' "+(i+1)+"</option>";
            }else{
                html_select+="<option value=\'"+(j+1)+"\'>"+letter+" ' . get_lang('MatchesTo') . ' "+(i+1)+"</option>";
            }
        }
        html_select+="</select></div></div>";
        $(\'#div_\'+(i+1)).html(html_select);
    }
  }else{
  //left side
    var dataleft = $(".display_option_left").val();
    var rowCount = $("#table_match_left >tbody >tr").length;
    var rowCount_right = $("#table_match_right >tbody >tr").length;
    var indice = rowCount+1;
    var nameEditor = \'answer[\'+(rowCount+1)+\']\';
    var html_select = "<select name=\'matches["+indice+"]\' id=\'matches["+indice+"]\'>";
        for(i=0;i<rowCount_right;i++){
            letter = String.fromCharCode(65+i);
            html_select+="<option value=\'"+(i+1)+"\'>"+letter+" ' . get_lang('MatchesTo') . ' "+(rowCount+1)+"</option>";
        }
    html_select+="</select>"
    
    if(dataleft == 0){
       $("#table_match_left > tbody:last").append("<tr style=\"height:87px;\"><td valign=\'top\' style=\'padding-top:10px;\'>"+indice+"</td><td><div class=\"yckanswerdiv\" style=\"display: none;margin-bottom:42px;float: left;\"><textarea id=\"answer_"+(rowCount+1)+"\" class=\"answer_"+(rowCount+1)+"\" name=\"answer["+(rowCount+1)+"]\" cols=\"30\" rows=\"7\" placeholder=\"'.get_lang("TypeHere").'\"></textarea></div><div id=\"answer_image_"+(rowCount+1)+"\" class=\"answer_image_"+(rowCount+1)+" div_answer_image nckanswerdiv\" style=\"display:inline;\"></div><div class=\"nckanswerdiv divfileanswer"+(rowCount+1)+" answer_file-"+(rowCount+1)+"\" style=\"display:inline;\"><div class=\"nckanswerdiv\"></div><form id=\"answer_file-"+(rowCount+1)+"\" enctype=\"multipart\/form-data\" name=\"answer_file_"+(rowCount+1)+"\" method=\"post\" > <input id=\"left_input_file_"+(rowCount+1)+"\" type=\"file\" class=\"answer_file-"+(rowCount+1)+"\" name=\"answer_file_"+(rowCount+1)+"\" /></form><div id=\"answer_progress_bar_"+(rowCount+1)+"\" style=\"height:30px; margin-top:5px; border-radius:5px; text-align:center; line-height:28px; font-weight:bold; font-size:14px;\"></div></div></td><td valign=\'top\' style=\'padding-top:5px;\'><div id=\'div_"+indice+"\'>"+html_select+"</div></td></tr>");
    }
    if(dataleft == 1){
      $("#table_match_left > tbody:last").append("<tr style=\"height:87px;\"><td valign=\'top\' style=\'padding-top:10px;\'>"+indice+"</td><td><div class=\"yckanswerdiv\" style=\"display: inline;margin-bottom:42px;float: left;\"><textarea id=\"answer_"+(rowCount+1)+"\" class=\"answer_"+(rowCount+1)+"\" name=\"answer["+(rowCount+1)+"]\" cols=\"30\" rows=\"7\" placeholder=\"'.get_lang("TypeHere").'\"></textarea></div><div id=\"answer_image_"+(rowCount+1)+"\" class=\"answer_image_"+(rowCount+1)+" div_answer_image nckanswerdiv\" style=\"display:none;\"></div><div class=\"nckanswerdiv divfileanswer"+(rowCount+1)+" answer_file-"+(rowCount+1)+"\" style=\"display:none;\"><div class=\"nckanswerdiv\"></div><form id=\"answer_file-"+(rowCount+1)+"\" enctype=\"multipart\/form-data\" name=\"answer_file_"+(rowCount+1)+"\" method=\"post\" > <input id=\"left_input_file_"+(rowCount+1)+"\" type=\"file\" class=\"answer_file-"+(rowCount+1)+"\" name=\"answer_file_"+(rowCount+1)+"\" /></form><div id=\"answer_progress_bar_"+(rowCount+1)+"\" style=\"height:30px; margin-top:5px; border-radius:5px; text-align:center; line-height:28px; font-weight:bold; font-size:14px;\"></div></div></td><td valign=\'top\' style=\'padding-top:5px;\'><div id=\'div_"+indice+"\'>"+html_select+"</div></td></tr>");  
    }
    
    if(dataleft == null){
    
        $("#table_match_left > tbody:last").append("<tr><td valign=\'top\' style=\'padding-top:10px;\'>"+indice+"</td><td><textarea class=\'answer\' name=\'"+nameEditor+"\' placeholder=\"'.get_lang("TypeHere").'\"   id=\'"+nameEditor+"\'></textarea></td><td valign=\'top\' style=\'padding-top:5px;\'><div id=\'div_"+indice+"\'>"+html_select+"</div></td></tr>");

        //$(\'textarea[id="answer[\'+(rowCount+1)+\']"]\').ckeditor({toolbar:"TestProposedAnswer",width:"350px", height:"90px"});
        loadEditor(nameEditor, "' . api_get_setting('use_default_editor') . '", "matching");
    }
    

    $(\'input[name="nb_matches"]\').val(rowCount+1);
  }


}

function removeAnswerMatch(origin){

var editorType = "' . api_get_setting('use_default_editor') . '";
if(origin== "right"){

    var rowCount = $("#table_match_right >tbody >tr").length;
    var rowCount_left = $("#table_match_left >tbody >tr").length;
    
    if(rowCount > 1){
        var dataleft = $(".display_option_right").val();
        
        if(dataleft){
            
            $("#table_match_right > tbody > tr:last").remove();
            var num = $(\'input[name="nb_options"]\').val();
            $(\'input[name="nb_options"]\').val(num-1);

            var values = {};
            for(t=0;t<rowCount;t++){
                values[t] = $(\'select[id="matches[\'+(t+1)+\']"]\').val();
            }

            //delete item in select
            for(i=0;i<rowCount_left;i++){
                html_select="<div class=\'row\' style=\'width: 100%;\'><div class=\'label\' style=\'width: 100%;text-align:left\'></div><div class=\'formw\' style=\'width: 100%;\'>";
                html_select+="<select name=\'matches["+(i+1)+"]\' id=\'matches["+(i+1)+"]\'>";
                for(j=0;j<(rowCount-1);j++){
                    letter = String.fromCharCode(65+j);
                    if(j+1 == values[i]){
                        html_select+="<option selected=\'selected\' value=\'"+(j+1)+"\'>"+letter+" ' . get_lang('MatchesTo') . ' "+(i+1)+"</option>";
                    }else{
                        html_select+="<option value=\'"+(j+1)+"\'>"+letter+" ' . get_lang('MatchesTo') . ' "+(i+1)+"</option>";
                    }
                }
                html_select+="</select></div></div>";
                $(\'#div_\'+(i+1)).html(html_select);
            }
        }else{
            
            var nameEditor = \'option[\'+(rowCount)+\']\';
            
            if (editorType == "Ckeditor") {
                var editor = CKEDITOR.instances[nameEditor];
                if(editor){
                    editor.destroy(true);
                    delete CKEDITOR.instances[nameEditor];
                    $("#table_match_right > tbody > tr:last").remove();
                    var num = $(\'input[name="nb_options"]\').val();
                    $(\'input[name="nb_options"]\').val(num-1);

                    var values = {};
                    for(t=0;t<rowCount;t++){
                        values[t] = $(\'select[id="matches[\'+(t+1)+\']"]\').val();
                    }

                    //delete item in select
                    for(i=0;i<rowCount_left;i++){
                        html_select="<div class=\'row\' style=\'width: 100%;\'><div class=\'label\' style=\'width: 100%;text-align:left\'></div><div class=\'formw\' style=\'width: 100%;\'>";
                        html_select+="<select name=\'matches["+(i+1)+"]\' id=\'matches["+(i+1)+"]\'>";
                        for(j=0;j<(rowCount-1);j++){
                            letter = String.fromCharCode(65+j);
                            if(j+1 == values[i]){
                                html_select+="<option selected=\'selected\' value=\'"+(j+1)+"\'>"+letter+" ' . get_lang('MatchesTo') . ' "+(i+1)+"</option>";
                            }else{
                                html_select+="<option value=\'"+(j+1)+"\'>"+letter+" ' . get_lang('MatchesTo') . ' "+(i+1)+"</option>";
                            }
                        }
                        html_select+="</select></div></div>";
                        $(\'#div_\'+(i+1)).html(html_select);
                    }
                }

            }else{
            
            //end editorType
                $("#table_match_right > tbody > tr:last").remove();
                var num = $(\'input[name="nb_options"]\').val();
                $(\'input[name="nb_options"]\').val(num-1);
                //delete item in select
                for(i=0;i<rowCount_left;i++){
                    html_select="<div class=\'row\' style=\'width: 100%;\'><div class=\'label\' style=\'width: 100%;text-align:left\'></div><div class=\'formw\' style=\'width: 100%;\'>";
                    html_select+="<select name=\'matches["+(i+1)+"]\' id=\'matches["+(i+1)+"]\'>";
                    for(j=0;j<(rowCount-1);j++){
                        letter = String.fromCharCode(65+j);
                        html_select+="<option value=\'"+(j+1)+"\'>"+letter+" ' . get_lang('MatchesTo') . ' "+(i+1)+"</option>";
                    }
                    html_select+="</select></div></div>";
                    $(\'#div_\'+(i+1)).html(html_select);
                }
            }//end else
        
        }

        

    }//end rowCount
}else{
//end origin
        var dataleft = $(".display_option_left").val();
        var rowCount = $("#table_match_left >tbody >tr").length;
        if(rowCount > 1){
            if(dataleft){
                $("#table_match_left > tbody > tr:last").remove();
                var num = $(\'input[name="nb_matches"]\').val();
                $(\'input[name="nb_matches"]\').val(num-1);
            }else{
                var nameEditor = \'answer[\'+(rowCount)+\']\';
                if (editorType == "Ckeditor") {
                    var editor = CKEDITOR.instances[nameEditor];
                    if(editor){
                        editor.destroy(true);
                        delete CKEDITOR.instances[nameEditor];
                        $("#table_match_left > tbody > tr:last").remove();
                        var num = $(\'input[name="nb_matches"]\').val();
                        $(\'input[name="nb_matches"]\').val(num-1);
                    }
                }
                else {
                    $("#table_match_left > tbody > tr:last").remove();
                    var num = $(\'input[name="nb_matches"]\').val();
                    $(\'input[name="nb_matches"]\').val(num-1);
                }
            }
            
        }
    }
}

function addNewAnswer(type,simplify){
    var rowCount = $(".data_table >tbody >tr").length;
    var id_md5 = \'qf_\'+md5(microtime()+Math.floor((Math.random()*10)+1)).substring(0,6);
    if(type == \'1\'){
        var htmlinput = "<input id=\'"+id_md5+"\' class=\'checkbox\' type=\'radio\' value=\'"+(rowCount)+"\' name=\'correct\' style=\'margin-left: 0em;\' />";
    }else{
        if(type == \'2\'){
            var htmlinput = "<input id=\'"+id_md5+"\' class=\'checkbox\' type=\'checkbox\' value=\'1\' name=\'correct["+(rowCount)+"]\' style=\'margin-left: 0em;\' />";
        }else{
            if(type == \'8\'){
                var htmlinput = "<input id=\'"+id_md5+"\' class=\'checkbox\' type=\'checkbox\' value=\'1\' name=\'correct["+(rowCount)+"]\' style=\'margin-left: 0em;\' />";
            }
        }
    }

    var class_row = "row_odd";
    if((rowCount-1)%2 == 0){
        class_row = "row_even";
    }
    $(\'input[name="nb_answers"]\').val(rowCount);
    var nameEditor = \'answer[\'+rowCount+\']\';
    //var nameEditor = \'answer_\'+rowCount;
    if(simplify == true){ 
        $(".data_table > tbody:last").append("<tr class=\'"+class_row+"\'><td align=\'center\'><br />"+htmlinput+"</td><td  align=\'center\'><br /><textarea class=\'answer\' name=\'"+nameEditor+"\' placeholder= \"'.get_lang("TypeHere").'\" id=\'"+nameEditor+"\' cols=\'50\' rows=\'3\' style=\'margin-top:-12px !important;width:90%\' ></textarea></td></tr>");
    }else{
    $(".data_table > tbody:last").append("<tr class=\'"+class_row+"\'><td align=\'center\'><br />"+htmlinput+"</td><td  align=\'center\'><br /><textarea class=\'answer\' name=\'"+nameEditor+"\' placeholder= \"'.get_lang("TypeHere").'\" id=\'"+nameEditor+"\' style=\'visibility:hidden;\'></textarea></td></tr>");
    }

    if(simplify == false){ 
    loadEditor(nameEditor, "' . api_get_setting('use_default_editor') . '", "default");
}
}

function loadEditor(editorName, editorType, questionType) {

    var w = "400px";
    var h = "70px";
    if (questionType == "matching") {
        var w = "350px";
        var h = "70px";
    }
    if (editorType == "Ckeditor") {
        CKEDITOR.replace(editorName,{
                toolbar:"TestProposedAnswer",
                 width : w,
                 height: h
            });
        
        for(var id in CKEDITOR.instances) {
            CKEDITOR.instances[id].on("focus", function(e) {
                takeFocus(e.editor);
            });
        }
    }
    else {
        var oFCKeditor = new FCKeditor(editorName) ;
        oFCKeditor.BasePath = "' . api_get_path(WEB_LIBRARY_PATH) . 'fckeditor/" ;
        oFCKeditor.ToolbarSet = "TestProposedAnswer";
        oFCKeditor.Width = w;
        oFCKeditor.Height = h;
        oFCKeditor.ReplaceTextarea() ;
        function FCKeditor_OnComplete( editorInstance ) {
            $(".TB_Collapse").hide();
            $(".TB_ToolbarSet").hide();
            $(".ck-loading textarea").css("visibility", "visible");
            if ($(".ck-icon-loading").length > 0) {
                $(".ck-icon-loading").remove();
            }
            // Get the fck instance
            _currentEditor = editorInstance;
            // automated event loaded by each fckeditor area when loaded
            editorInstance.Events.AttachEvent( "OnSelectionChange", takeFocus ) ;
            var oFCKeditor=FCKeditorAPI.GetInstance("questionName") ;
            oFCKeditor.Focus();
            var questiontype = document.question_admin_form.questiontype.value;
            if (questiontype == 4) {
                if (window.attachEvent) {
                    editorInstance.EditorDocument.attachEvent("onkeyup", updateBlanks) ;
                } else {
                    editorInstance.EditorDocument.addEventListener("keyup",updateBlanks,true);
                }
            }
        }
    }
}

function removeAnswer(){
    var editorType = "' . api_get_setting('use_default_editor') . '";
    var rowCount = $(".data_table >tbody >tr").length;
    
    if(rowCount > 3){
       var nameEditor = \'answer[\'+(rowCount-1)+\']\';
       if (editorType == "Ckeditor") {
        for(var id in CKEDITOR.instances) {
            if(id == "answer["+(rowCount-1)+"]"){
                CKEDITOR.instances[id].destroy();
            }
        }
            $(".data_table > tbody > tr:last").remove();
            var num = $(\'input[name="nb_answers"]\').val();
            $(\'input[name="nb_answers"]\').val(num-1);
       }
       else {
            $(".data_table > tbody > tr:last").remove();
            var num = $(\'input[name="nb_answers"]\').val();
            $(\'input[name="nb_answers"]\').val(num-1);
       }
    }
}

function microtime(get_as_float) {
    // Returns either a string or a float containing the current time in seconds and microseconds
    //
    // version: 812.316
    // discuss at: http://phpjs.org/functions/microtime
    // +   original by: Paulo Ricardo F. Santos
    // *     example 1: timeStamp = microtime(true);
    // *     results 1: timeStamp > 1000000000 && timeStamp < 2000000000
    var now = new Date().getTime() / 1000;
    var s = parseInt(now);

    return (get_as_float)?now:(Math.round((now - s) * 1000) / 1000) + " " + s;
}

$(document).ready(function(){

//     if ($(\'table[class="toolbar_style_back"]\').length>0){
//
//    // grab the initial top offset of the navigation
//    var sticky_navigation_offset_top = $(\'table[class="toolbar_style_back"]\').offset().top;
//
//    // our function that decides weather the navigation bar should have "fixed" css position or not.
//    var sticky_navigation = function(){
//        var scroll_top = $(window).scrollTop(); // our current vertical position from the top
//
//        // if we have scrolled more than the navigation, change its position to fixed to stick to top,
//        // otherwise change it back to relative
//        if (scroll_top > sticky_navigation_offset_top) {
//        
//            $(\'table[class="toolbar_style_back"]\').css({ "position": "fixed", "top":0, width:940, "z-index":9999 });
//            $("#question_admin_form2").css({ "top": "361px","left":"20px","z-index":"1"  });
//        } else {
//       $("#question_admin_form2").css({ "top": "415px","left":"20px","z-index":"1" });
//            $(\'table[class="toolbar_style_back"]\').css({ "position": "relative" });
//            
//        }
//    };
//
//    // run our function on load
//    sticky_navigation();
//
//    // and run it again every time you scroll
//    $(window).scroll(function() {
//         sticky_navigation();
//    });
//  }

	$(function() {
		$("#contentLeft ul").sortable({ opacity: 0.6, cursor: "move",cancel: ".nodrag", update: function() {
			var order = $(this).sortable("serialize") + "&action=updateQuizQuestion";
			var record = order.split("&");
			var recordlen = record.length;
			var disparr = new Array();
			for(var i=0;i<(recordlen-1);i++)
			{
			 var recordval = record[i].split("=");
			 disparr[i] = recordval[1];
			}

//            alert("exercise.ajax.php?' . api_get_cidReq() . '&action=updateQuizQuestion&exerciseId=' . $exerciseId . '&disporder="+disparr);
			// call ajax to save new position
            $.ajax({
			type: "GET",
			url: "exercise.ajax.php?' . api_get_cidReq() . '&action=updateQuizQuestion&exerciseId=' . $exerciseId . '&disporder="+disparr,
			success: function(msg){
                            document.location="admin.php?exerciseId=' . Security::remove_XSS($_GET['exerciseId']) . '&' . api_get_cidreq() .$evaluation_link. '";
                        }
                    })
                  }
		});
	});

});
/*]]>*/
</script> ';
$htmlHeadXtra[] = '
<script type="text/javascript">    
$(document).ready(function(){
if($("#back_toolbar_style").length>0){
    $("#question_admin_form2").show();
}else{
    $("#question_admin_form2").hide();
}
$("#multiple_error").html($("#error"));
$("#back_toolbar_style_input").html($("#question_admin_form2"));
if ($.browser.msie  && parseInt($.browser.version, 10) > 8) {
  $("#fileToUpload").css({ "padding-top": "3px", "margin-left": "0px","padding-bottom": "0px","margin-top": "0px" });
} else {
  $("#fileToUpload").css({ "padding" : "0px", "margin-top": "0px"});
}

 if ($(".toolbar_style_back").length){
 var offset = $(\'table[class="toolbar_style_back"]\').offset().top;
    var duration = 100;
        $(window).scroll(function() {
            if ($(this).scrollTop() > offset) {
                $(\'table[class="toolbar_style_back"]\').css({"position": "fixed", "top":"0", "width":"940", "z-index":"9999"});
                $("#d_clear").addClass("btn-quiz-submit-bottom");
            } else {
                $("#d_clear").removeClass("btn-quiz-submit-bottom");
                $(\'table[class="toolbar_style_back"]\').css({"position":"relative"});
            }
        });       
 }
  
});

</script>';
// get from session
$objExercise = $_SESSION['objExercise'];
$objQuestion = $_SESSION['objQuestion'];
$objAnswer = $_SESSION['objAnswer'];

// document path
$documentPath = api_get_path(SYS_COURSE_PATH) . $_course['path'] . '/document';

// picture path
$picturePath = $documentPath . '/images';

// audio path
$audioPath = $documentPath . '/audio';

// the 5 types of answers
$aType = array(get_lang('UniqueSelect'), get_lang('MultipleSelect'), get_lang('FillBlanks'), get_lang('Matching'), get_lang('freeAnswer'));

// tables used in the exercise tool
$TBL_EXERCICE_QUESTION = Database::get_course_table(TABLE_QUIZ_TEST_QUESTION);
$TBL_EXERCICES = Database::get_course_table(TABLE_QUIZ_TEST);
$TBL_QUESTIONS = Database::get_course_table(TABLE_QUIZ_QUESTION);
$TBL_REPONSES = Database::get_course_table(TABLE_QUIZ_ANSWER);
$TBL_DOCUMENT = Database::get_course_table(TABLE_DOCUMENT);

if ($_GET['action'] == 'exportqti2' && !empty($_GET['questionId'])) {
    require_once('export/qti2/qti2_export.php');
    $export = export_question((int) $_GET['questionId'], true);
    $qid = (int) $_GET['questionId'];
    require_once(api_get_path(LIBRARY_PATH) . 'pclzip/pclzip.lib.php');
    $archive_path = api_get_path(SYS_ARCHIVE_PATH);
    $temp_dir_short = uniqid();
    $temp_zip_dir = $archive_path . "/" . $temp_dir_short;
    if (!is_dir($temp_zip_dir))
        mkdir($temp_zip_dir);
    $temp_zip_file = $temp_zip_dir . "/" . md5(time()) . ".zip";
    $temp_xml_file = $temp_zip_dir . "/qti2export_" . $qid . '.xml';
    file_put_contents($temp_xml_file, $export);
    $zip_folder = new PclZip($temp_zip_file);
    $zip_folder->add($temp_xml_file, PCLZIP_OPT_REMOVE_ALL_PATH);
    $name = 'qti2_export_' . $qid . '.zip';

    DocumentManager::file_send_for_download($temp_zip_file, true, $name);
    unlink($temp_zip_file);
    unlink($temp_xml_file);
    rmdir($temp_zip_dir);
    //DocumentManager::string_send_for_download($export,true,'qti2export_q'.$_GET['questionId'].'.xml');
    exit(); //otherwise following clicks may become buggy
}

// intializes the Exercise object
if (!is_object($objExercise)) {
    // construction of the Exercise object
    $objExercise = new Exercise();

    // creation of a new exercise if wrong or not specified exercise ID
    if ($exerciseId) {
        $objExercise->read($exerciseId);
    }

    // saves the object into the session
    api_session_register('objExercise');
}

// doesn't select the exercise ID if we come from the question pool
if (!$fromExercise) {
    // gets the right exercise ID, and if 0 creates a new exercise
    if (!$exerciseId = $objExercise->selectId()) {
        $modifyExercise = 'yes';
    }
}

$nbrQuestions = $objExercise->selectNbrQuestions();

// intializes the Question object
if ($editQuestion || $newQuestion || $modifyQuestion || $modifyAnswers) {
    if ($editQuestion || $newQuestion) {
        // reads question data
        if ($editQuestion) {
            // question not found
            if (!$objQuestion = Question::read($editQuestion)) {
                die(get_lang('QuestionNotFound'));
            }
            // saves the object into the session
            api_session_register('objQuestion');
        }
    }

    // checks if the object exists
    if (is_object($objQuestion)) {
        // gets the question ID
        $questionId = $objQuestion->selectId();
    }
}

// if cancelling an exercise
if ($cancelExercise) {
    // existing exercise
    if ($exerciseId) {
        unset($modifyExercise);
    }
    // new exercise
    else {
        // goes back to the exercise list
        header('Location: exercice.php');
        exit();
    }
}

// if cancelling question creation/modification
if ($cancelQuestion) {
    // if we are creating a new question from the question pool
    if (!$exerciseId && !$questionId) {
        // goes back to the question pool
        header('Location: question_pool.php');
        exit();
    } else {
        // goes back to the question viewing
        $editQuestion = $modifyQuestion;

        unset($newQuestion, $modifyQuestion);
    }
}

// if cancelling answer creation/modification
if ($cancelAnswers) {
    // goes back to the question viewing
    $editQuestion = $modifyAnswers;

    unset($modifyAnswers);
}

// modifies the query string that is used in the link of tool name
if ($editQuestion || $modifyQuestion || $newQuestion || $modifyAnswers) {
    $nameTools = get_lang('QuestionManagement');
}

if (isset($_SESSION['gradebook'])) {
    $gradebook = $_SESSION['gradebook'];
}

if (!empty($gradebook) && $gradebook == 'view') {
    $interbreadcrumb[] = array(
        'url' => '../gradebook/' . $_SESSION['gradebook_dest'],
        'name' => get_lang('Gradebook')
    );
}

$interbreadcrumb[] = array("url" => "exercice.php", "name" => get_lang('Exercices'));
$interbreadcrumb[] = array("url" => "admin.php?exerciseId=" . $objExercise->id, "name" => $objExercise->exercise);

// shows a link to go back to the question pool
if (!$exerciseId && $nameTools != get_lang('ExerciseManagement')) {
    $interbreadcrumb[] = array("url" => "question_pool.php?fromExercise=$fromExercise", "name" => get_lang('QuestionPool'));
}

// if the question is duplicated, disable the link of tool name
if ($modifyIn == 'thisExercise') {
    if ($buttonBack) {
        $modifyIn = 'allExercises';
    } else {
        $noPHP_SELF = true;
    }
}

$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery.validate.js"></script>';
$htmlHeadXtra[] = '
    <style type="text/css">
        input.error { border: 1px solid red; }
    </style>

    <script type="text/javascript">

    $(document).ready(function(){
        if ($("#frm_exe_hotspot_one").length > 0) {
            $("#frm_exe_hotspot_one").validate({
                rules: {
                    imageUpload: {
                      accept: "jpg|png"
                    }
                },
                messages: {
                    imageUpload: {
                        accept: "<img src=\'../img/exclamation.png\' title=\'' . get_lang('UplUnableToSaveFileFilteredExtension') . '\' />"
                    }
                }
            });
        }

      /*$("input[name=\'imageUpload\']").change(function(){
        $("input[name=\'upload\']").click();
      });*/

      /*
      $("a#exammode-' . $exerciseId . '").click(function (event){
            var width = screen.width;
            var height = screen.height;
            var windowName = "Quiz";
            var windowSize = "width="+width+",height="+height;
            window.open("quiz_exam_mode.php?' . api_get_cidReq() . '&exerciseId=' . $exerciseId . '&TB_iframe=true", windowName, windowSize);
      });
      */

      $("a#exammode-' . $exerciseId . '").colorbox({iframe:true, innerWidth:"98%", innerHeight:"800px"});

   });



</script>';

$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/ajaxfileupload.js" language="javascript"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/html_entity_decode.js" language="javascript"></script>';
$htmlHeadXtra[] = "<script type=\"text/javascript\" src=\"../plugin/hotspot/JavaScriptFlashGateway.js\"></script>
<script src=\"../plugin/hotspot/hotspot.js\" type=\"text/javascript\"></script>
<script language=\"JavaScript\" type=\"text/javascript\">
<!--
// -----------------------------------------------------------------------------
// Globals
// Major version of Flash required
var requiredMajorVersion = 7;
// Minor version of Flash required
var requiredMinorVersion = 0;
// Minor version of Flash required
var requiredRevision = 0;
// the version of javascript supported
var jsVersion = 1.0;
// -----------------------------------------------------------------------------
// -->
</script>
<script language=\"VBScript\" type=\"text/vbscript\">
<!-- // Visual basic helper required to detect Flash Player ActiveX control version information
Function VBGetSwfVer(i)
  on error resume next
  Dim swControl, swVersion
  swVersion = 0

  set swControl = CreateObject(\"ShockwaveFlash.ShockwaveFlash.\" + CStr(i))
  if (IsObject(swControl)) then
    swVersion = swControl.GetVariable(\"\$version\")
  end if
  VBGetSwfVer = swVersion
End Function
// -->
</script>

<script language=\"JavaScript1.1\" type=\"text/javascript\">
<!-- // Detect Client Browser type
var isIE  = (navigator.appVersion.indexOf(\"MSIE\") != -1) ? true : false;
var isWin = (navigator.appVersion.toLowerCase().indexOf(\"win\") != -1) ? true : false;
var isOpera = (navigator.userAgent.indexOf(\"Opera\") != -1) ? true : false;
jsVersion = 1.1;
// JavaScript helper required to detect Flash Player PlugIn version information
function JSGetSwfVer(i){
	// NS/Opera version >= 3 check for Flash plugin in plugin array
	if (navigator.plugins != null && navigator.plugins.length > 0) {
		if (navigator.plugins[\"Shockwave Flash 2.0\"] || navigator.plugins[\"Shockwave Flash\"]) {
			var swVer2 = navigator.plugins[\"Shockwave Flash 2.0\"] ? \" 2.0\" : \"\";
      		var flashDescription = navigator.plugins[\"Shockwave Flash\" + swVer2].description;
			descArray = flashDescription.split(\" \");
			tempArrayMajor = descArray[2].split(\".\");
			versionMajor = tempArrayMajor[0];
			versionMinor = tempArrayMajor[1];
			if ( descArray[3] != \"\" ) {
				tempArrayMinor = descArray[3].split(\"r\");
			} else {
				tempArrayMinor = descArray[4].split(\"r\");
			}
      		versionRevision = tempArrayMinor[1] > 0 ? tempArrayMinor[1] : 0;
            flashVer = versionMajor + \".\" + versionMinor + \".\" + versionRevision;
      	} else {
			flashVer = -1;
		}
	}
	// MSN/WebTV 2.6 supports Flash 4
	else if (navigator.userAgent.toLowerCase().indexOf(\"webtv/2.6\") != -1) flashVer = 4;
	// WebTV 2.5 supports Flash 3
	else if (navigator.userAgent.toLowerCase().indexOf(\"webtv/2.5\") != -1) flashVer = 3;
	// older WebTV supports Flash 2
	else if (navigator.userAgent.toLowerCase().indexOf(\"webtv\") != -1) flashVer = 2;
	// Can't detect in all other cases
	else {

		flashVer = -1;
	}
	return flashVer;
}
// When called with reqMajorVer, reqMinorVer, reqRevision returns true if that version or greater is available
function DetectFlashVer(reqMajorVer, reqMinorVer, reqRevision)
{
 	reqVer = parseFloat(reqMajorVer + \".\" + reqRevision);
   	// loop backwards through the versions until we find the newest version
	for (i=25;i>0;i--) {
		if (isIE && isWin && !isOpera) {
			versionStr = VBGetSwfVer(i);
		} else {
			versionStr = JSGetSwfVer(i);
		}
		if (versionStr == -1 ) {
			return false;
		} else if (versionStr != 0) {
			if(isIE && isWin && !isOpera) {
				tempArray         = versionStr.split(\" \");
				tempString        = tempArray[1];
				versionArray      = tempString .split(\",\");
			} else {
				versionArray      = versionStr.split(\".\");
			}
			versionMajor      = versionArray[0];
			versionMinor      = versionArray[1];
			versionRevision   = versionArray[2];

			versionString     = versionMajor + \".\" + versionRevision;   // 7.0r24 == 7.24
			versionNum        = parseFloat(versionString);
        	// is the major.revision >= requested major.revision AND the minor version >= requested minor
			if ( (versionMajor > reqMajorVer) && (versionNum >= reqVer) ) {
				return true;
			} else {
				return ((versionNum >= reqVer && versionMinor >= reqMinorVer) ? true : false );
			}
		}
	}
}
// -->
</script>";

$htmlHeadXtra[] = '<script type="text/javascript">function callsave(){document.question_admin_form.submitform.value=1;document.forms["question_admin_form"].submit();}</script>';

$htmlHeadXtra[] = '<script type="text/javascript">function callHotspotSave(){document.frm_exercise.submitform.value="1 ";alert(document.frm_exercise.submitform.value);document.forms["frm_exercise"].submit();}</script>';

/* if(isset($_REQUEST['fromTpl']))
  {
  $_SESSION['fromTpl'] = '1';
  } */

if (isset($_REQUEST['fromlp'])) {
    $_SESSION['fromlp'] = '1';
}

if (isset($_REQUEST['editQn'])) {
    $_SESSION['editQn'] = '1';
}

if (isset($_REQUEST['popup'])) {
    $popup = Security::remove_XSS($_REQUEST['popup']); // Posible deprecated code
} else {
    $popup = Security::remove_XSS($_REQUEST['popup']); // Posible deprecated code
}

if (isset($_REQUEST['startPage'])) {
    $startPage = Security::remove_XSS($_REQUEST['startPage']); // Posible deprecated code
}

if (isset($_REQUEST['totTpl'])) {
    $totTpl = Security::remove_XSS($_REQUEST['totTpl']); // Posible deprecated cod
}


    define(HEADER_EXERCISE, 1);


Display::display_tool_header($nameTools, 'Exercise');
// Tool introduction
Display :: display_introduction_section(TOOL_QUIZ);
if (!isset($feedbacktype))
    $feedbacktype = 0;
if ($feedbacktype == 1) {
    $url = 'question_pool.php?type=1&fromExercise=' . $exerciseId . '&' . api_get_cidreq();
} else {
    $url = 'question_pool.php?fromExercise=' . $exerciseId . '&' . api_get_cidreq();
}

//if(($_SESSION['editQn'] != '1')&&(!isset($_GET['hotspotadmin']))) {
if ((!isset($_GET['hotspotadmin']))) {
    
    $exercice_id = Security::remove_XSS($_REQUEST['exerciseId']);
    if (!isset($_REQUEST['exerciseId']) && (isset($_REQUEST['fromExercise']) && $_REQUEST['fromExercise'] > 0)) {
        $exercice_id = Security::remove_XSS($_REQUEST['fromExercise']);
   
    }
 
// Main buttons
    echo '<div class="actions">';    
    if (isset($_GET['origin']) && $_GET['origin'] == 'evaluation') {
         // module=evaluation&cmd=Builder&func=newExam&examId=1&cidReq=CURSO01
        echo '<a href="'.api_get_path(WEB_CODE_PATH).'index.php?module=evaluation&cmd=Builder&func=newExam&examId='.intval($_GET['examId']).'&'.api_get_cidreq().'">' . Display::return_icon('pixel.gif', get_lang('Exam'), array('class' => 'toolactionplaceholdericon toolactionsactivity')) . get_lang("BackToExam") . '</a>';
        echo '<a href="admin.php?' . api_get_cidreq() . '&exerciseId=' . $exercice_id . '&origin=evaluation&examId='.intval($_GET['examId']).'">' . Display::return_icon('pixel.gif', get_lang('QuizMaker'), array('class' => 'toolactionplaceholdericon toolactionquestion')) . get_lang('QuizMaker') . '</a>';
		echo '<a href="template_gallery.php?fromExercise=' . Security::remove_XSS($_REQUEST['exerciseId']) . '&' . api_get_cidreq() . '&examId='.intval($_GET['examId']) . '&origin=' . $_GET['origin'] .'">' . Display::return_icon('pixel.gif', get_lang('Templates'), array('class' => 'toolactionplaceholdericon toolactiontemplates')) . get_lang('Templates') . '</a>';                
    }
    else {
        $editQuestion = isset($_GET['editQuestion']) ? true : false;
        $author_lang_var = api_convert_encoding(get_lang('Author'), $charset, api_get_system_encoding());
        $content_lang_var = api_convert_encoding(get_lang('Content'), $charset, api_get_system_encoding());
        $scenario_lang_var = api_convert_encoding(get_lang('Scenario'), $charset, api_get_system_encoding());
        if (!isset($_GET['answerType']) && $_GET['answerType'] == '') {
            if (isset($_GET['lp_id']) && $_GET['lp_id'] > 0) {
                $lp_id = Security::remove_XSS($_GET['lp_id']);
                echo '<a href="../newscorm/lp_controller.php?' . api_get_cidreq() . '">' . Display::return_icon('pixel.gif', $author_lang_var, array('class' => 'toolactionplaceholdericon toolactionauthor')) . $author_lang_var . '</a>';
                echo '<a href="../newscorm/lp_controller.php?' . api_get_cidreq() . '&action=add_item&type=step">' . Display::return_icon('pixel.gif', $content_lang_var, array('class' => 'toolactionplaceholdericon toolactionauthorcontent')) . $content_lang_var . '</a>';
            } elseif(!$editQuestion) {
                echo '<a href="exercice.php?' . api_get_cidreq() . '">' . Display::return_icon('pixel.gif', get_lang('QuizzesList'), array('class' => 'toolactionplaceholdericon toolactionquestionlist')) . get_lang('QuizzesList') . '</a>';
            }
            echo '<a href="admin.php?' . api_get_cidreq() . '&exerciseId=' . $exercice_id . '">' . Display::return_icon('pixel.gif', get_lang('QuizMaker'), array('class' => 'toolactionplaceholdericon toolactionquestion')) . get_lang('QuizMaker') . '</a>';
            if(!$editQuestion){
                echo '<a href="exercice_scenario.php?modifyExercise=yes&' . api_get_cidreq() . '&exerciseId=' . $exercice_id . '">' . Display::return_icon('pixel.gif', $scenario_lang_var, array('class' => 'toolactionplaceholdericon toolactionscenario')) . $scenario_lang_var . '</a>';
				echo '<a href="template_gallery.php?fromExercise=' . Security::remove_XSS($_REQUEST['exerciseId']) . '&' . api_get_cidreq() . '">' . Display::return_icon('pixel.gif', get_lang('Templates'), array('class' => 'toolactionplaceholdericon toolactiontemplates')) . get_lang('Templates') . '</a>';                
            }
            if ($objExercise->selectQuizType() == 2 && api_get_setting('enable_exammode') === 'true' AND !$editQuestion) {
                echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'exercice/exam_mode/index.php?' . api_get_cidReq() . '&exerciseId=' . $exerciseId . '">' . Display::return_icon('pixel.gif', get_lang('Preview'), array('class' => 'toolactionplaceholdericon toolactionsearch')) . get_lang('Preview') . '</a>';
            } elseif(!$editQuestion) {
                echo '<a href="exercice_submit.php?' . api_get_cidreq() . '&exerciseId=' . $exercice_id . '&clean=true">' . Display::return_icon('pixel.gif', get_lang('Preview'), array('class' => 'toolactionplaceholdericon toolactionsearch')) . get_lang('Preview') . '</a>';
            }
        } else {
            echo '<a href="admin.php?' . api_get_cidreq() . '&exerciseId=' . $exercice_id . '">' . Display::return_icon('pixel.gif', get_lang('QuizMaker'), array('class' => 'toolactionplaceholdericon toolactionquestion')) . get_lang('QuizMaker') . '</a>';
        }
    }       
    echo '</div>';
} else {
    if (isset($viewQuestion)) {
        $edit_question_id = $viewQuestion;
        $link_param_prev = "&viewQuestion=" . ($viewQuestion - 1);
        $link_param_next = "&viewQuestion=" . ($viewQuestion + 1);
    } else if (isset($editQuestion)) {
        $edit_question_id = $editQuestion;
        $link_param_prev = "&viewQuestion=" . ($editQuestion - 1);
        $link_param_next = "&viewQuestion=" . ($editQuestion + 1);
    }
}

if (isset($_REQUEST['hotspotadmin'])) {
    //Display::display_tool_header();
    $exercice_id = Security::remove_XSS($_REQUEST['exerciseId']);

    // Main buttons
    echo '<div class="actions">';
    $scenario_lang_var = api_convert_encoding(get_lang('Scenario'), $charset, api_get_system_encoding());
    echo '<a href="exercice.php?' . api_get_cidreq() . '">' . Display::return_icon('pixel.gif', get_lang('QuizzesList'), array('class' => 'toolactionplaceholdericon toolactionback')) . get_lang('QuizzesList') . '</a>';
    echo '<a href="admin.php?' . api_get_cidreq() . '&exerciseId=' . $exercice_id . '">' . Display::return_icon('pixel.gif', get_lang('QuizMaker'), array('class' => 'toolactionplaceholdericon toolactionquestion')) . get_lang('QuizMaker') . '</a>';
    echo '<a href="exercice_scenario.php?modifyExercise=yes&' . api_get_cidreq() . '&exerciseId=' . $exercice_id . '">' . Display::return_icon('pixel.gif', $scenario_lang_var, array('class' => 'toolactionplaceholdericon toolactionscenario')) . $scenario_lang_var . '</a>';
    echo '</div>';
}

if (isset($_GET['message'])) {
    if (in_array($_GET['message'], array('ExerciseStored','ExerciseEdited'))) {
        //Display::display_confirmation_message(get_lang($_GET['message']),false,true);
        $_SESSION['display_confirmation_message']=get_lang($_GET['message']);
        
    }
}

if ($newQuestion || $editQuestion || $viewQuestion) {
    // statement management
    if (isset($_REQUEST['answerType']) && !empty($_REQUEST['answerType'])) {
        $type = Security::remove_XSS($_REQUEST['answerType']);
    }
    if (isset($_REQUEST['type']) && !empty($_REQUEST['type'])) {
        $type = Security::remove_XSS($_REQUEST['type']);
    }
    ?><input type="hidden" name="Type" value="<?php echo $type; ?>" />
    <?php
   
    if ($type == 6) {
        $type_hotspost_delineation = $type;
        include('hotspot_one.inc.php');
    } else if ($type == 9) {
        $type_hotspost_delineation = $type;
        include('hotspot_delineation.inc.php');
    } else {
        
        include('question_admin.inc.php');
    }
//	include('question_admin.inc.php');
}

/* if(isset($_GET['hotspotadmin'])){
  include('hotspot_admin.inc.php');
  } */

if (!$newQuestion && !$modifyQuestion && !$editQuestion && !$viewQuestion && !isset($_GET['hotspotadmin'])) {
    // Question list management(nice buttons for questions here)
    include_once('question_list_admin.inc.php');
    include_once(api_get_path(LIBRARY_PATH) . 'formvalidator/FormValidator.class.php');
    $form = new FormValidator('exercise_admin', 'post', api_get_self() . '?exerciseId=' . Security::remove_XSS($_GET['exerciseId']));
    $form->addElement('hidden', 'edit', 'true');
    //$objExercise -> createForm ($form,'simple');

    if ($form->validate()) {
        $objExercise->processCreation($form, 'simple');
        if ($form->getSubmitValue('edit') == 'true')
            //Display::display_confirmation_message(get_lang('ExerciseEdited'),false,true);
            //echo "ExerciseEdited";
            $_SESSION['display_confirmation_message']=get_lang('ExerciseEdited');
    }
    if (api_get_setting('search_enabled') == 'true' && !extension_loaded('xapian')) {
        //echo '<div class="confirmation-message">' . get_lang('SearchXapianModuleNotInstaled') . '</div>';
        $_SESSION["display_error_message"]=get_lang('SearchXapianModuleNotInstaled');
    }
    $form->display();
}

api_session_register('objExercise');
api_session_register('objQuestion');
api_session_register('objAnswer');


if ($popup == '1') {
//  echo '<script type="text/javascript">parent.TplWindow.hide()</script>';
//  echo '<script type="text/javascript">window.location.href="admin.php"</script>';
} else {
    if (($_GET['fromTpl'] == '') && (!isset($_SESSION['fromlp']))) {
        Display::display_footer();
    }
}
