<?php

/*
  ==============================================================================
  Dokeos - elearning and course management software

  Copyright (c) 2004-2009 Dokeos SPRL

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
 * 	Statement (?) administration
 * 	This script allows to manage the statements of questions.
 * 	It is included from the script admin.php
 * 	@package dokeos.exercise
 * 	@author Olivier Brouckaert
 * 	@version $Id: question_admin.inc.php 22126 2009-07-15 22:38:39Z juliomontoya $
 */
/*
  ==============================================================================
  INIT SECTION
  ==============================================================================
 */

include_once(api_get_path(LIBRARY_PATH) . 'formvalidator/FormValidator.class.php');
include_once(api_get_path(LIBRARY_PATH) . 'image.lib.php');


// ALLOWED_TO_INCLUDE is defined in admin.php
if (!defined('ALLOWED_TO_INCLUDE')) {
    exit();
}

/* * *******************
 * INIT QUESTION
 * ******************* */
if (isset($_GET['editQuestion'])) {
    $objQuestion = Question::read($_GET['editQuestion']);
    $action = api_get_self() . "?" . api_get_cidreq() . "&modifyQuestion=" . Security::remove_XSS($_GET['modifyQuestion']) . "&type=" . $objQuestion->type . "&editQuestion=" . $objQuestion->id . "&exerciseId=" . $exerciseId;
    if ($_GET['fromTpl'] == '1') {
        $action .= "&fromTpl=1&startPage=" . Security::remove_XSS($_GET['startPage']) . "&totTpl=" . Security::remove_XSS($_GET['totTpl']);
    }
    if (isset($_GET['lp_id']) && $_GET['lp_id'] > 0) {
        $action .= "&lp_id=" . Security::remove_XSS($_GET['lp_id']);
    }

    $evaluation_link = '';
    if (isset($_GET['origin']) && $_GET['origin'] == 'evaluation') {
        $evaluation_link = '&origin=evaluation&examId='.intval($_GET['examId']);
    }
    $action .= $evaluation_link;
    
    if (isset($exerciseId) && !empty($exerciseId)) {
        $TBL_LP_ITEM = Database::get_course_table(TABLE_LP_ITEM);
        $sql = "SELECT max_score FROM $TBL_LP_ITEM
		WHERE item_type = '" . TOOL_QUIZ . "' AND path ='" . Database::escape_string($exerciseId) . "'";
        $result = api_sql_query($sql);
        if (Database::num_rows($result) > 0) {
            Display::display_warning_message(get_lang('EditingScoreCauseProblemsToExercisesInLP'));
        }
    }
} else {

    $objQuestion = Question :: getInstance($_REQUEST['answerType']);
    //	&fromExercise=4&answerType=1
    $exercice_id = Security::remove_XSS($_REQUEST['fromExercise']);
    $action = api_get_self() . "?" . api_get_cidreq() . "&modifyQuestion=" . $modifyQuestion . "&type=" . $objQuestion->type . "&newQuestion=" . $newQuestion . "&fromExercise=" . $exercice_id;
    if (isset($_GET['lp_id']) && $_GET['lp_id'] > 0) {
        $action .= "&lp_id=" . Security::remove_XSS($_GET['lp_id']);
    }
    $evaluation_link = '';
    if (isset($_GET['origin']) && $_GET['origin'] == 'evaluation') {
        $evaluation_link = '&origin=evaluation&examId='.intval($_GET['examId']);
    }
    $action .= $evaluation_link;
}

if (is_object($objQuestion)) {

    /*     * *******************
     * FORM STYLES
     * ******************* */
    // if you have a better way to improve the display, please inform me e.marguin@elixir-interactive.com
    $styles = '
	<style type="text/css">
	div.row div.label{
		width: 10%;
	}
	div.row div.formw{
		width: 85%;
	}

	</style>
	';
    echo $styles;


    /*     * *******************
     * INIT FORM
     * ******************* */
    $form = new FormValidator('question_admin_form', 'post', $action);


    /*     * *******************
     * FORM CREATION
     * ******************* */

    if (isset($_GET['editQuestion'])) {
        if ($_GET['fromTpl'] == '1') {
            $class = "add";
            $text = get_lang('AddQuestionToExercise');
        } else {
            $class = "save";
            $text = get_lang('ModifyQuestion');
        }
    } else {
        $class = "add";
        $text = get_lang('AddQuestionToExercise');
    }

    $types_information = $objQuestion->get_types_information();
    $form_title_extra = get_lang($types_information[$_REQUEST['answerType']][1]);

    $quiz_id = (isset($_REQUEST['fromExercise']) && $_REQUEST['fromExercise'] > 0 ) ? Security::remove_XSS($_REQUEST['fromExercise']) : Security::remove_XSS($_REQUEST['exerciseId']);
    $my_quiz = new Exercise();
    $my_quiz->read($quiz_id);
    
    
    // form title
//	$form->addElement('header', '', $text.': '.$form_title_extra);
    // question form elements
    $objQuestion->createForm($form, array('Height' => 150),$my_quiz);

    // answer form elements
    $objQuestion->createAnswersForm($form, $my_quiz);
    
    // submit button is implemented in every question type
    //$form->addElement('style_submit_button','submitQuestion',$text, 'class="'.$class.'"');
    //$renderer = $form->defaultRenderer();
    //$renderer->setElementTemplate('<div class="row"><div class="label">{label}</div><div class="formw">{element}</div></div>','submitQuestion');


    /*     * ********************
     * FORM VALIDATION
     * ******************** */

//	if(isset($_POST['submitQuestion']) && $form->validate())
//	if($_SERVER['REQUEST_METHOD'] == "POST" && $form->validate())
    if (($_POST['submitform'] == '1' || isset($_POST['submitQuestion'])) && $form->validate()) {
        $objQuestion->processCreation($form, $objExercise);        
        $quiz_id = (isset($_REQUEST['fromExercise']) && $_REQUEST['fromExercise'] > 0 ) ? Security::remove_XSS($_REQUEST['fromExercise']) : Security::remove_XSS($_REQUEST['exerciseId']);
        $objQuestion->processAnswersCreation($form, $nb_answers);
        $add_lp_id_parameter = "";
        if (isset($_GET['lp_id']) && $_GET['lp_id'] > 0) {
            $add_lp_id_parameter = '&lp_id=' . Security::remove_XSS($_GET['lp_id']);
        }
        
        $evaluation_link = '';
        if (isset($_GET['origin']) && $_GET['origin'] == 'evaluation') {
            $evaluation_link = '&origin=evaluation&examId='.intval($_GET['examId']);
        }
        
        $_SESSION["display_confirmation_message"] = isset($_GET['editQuestion'])  ?  get_lang('QuizQuestionUpdated') : get_lang('QuizQuestionAdded');
        if ($objQuestion->type != HOT_SPOT) {
            if (isset($_SESSION['fromlp'])) {
                echo '<script type="text/javascript">window.location.href="admin.php?fromlp=Y'.$evaluation_link.'"</script>';
            } else {
                // Added cidReq and exerciseId
                echo '<script type="text/javascript">parent.location.href="admin.php?exerciseId=' . $quiz_id . '&' . api_get_cidreq() . $add_lp_id_parameter .$evaluation_link. '"</script>';
            }
        } else {
            echo '<script type="text/javascript">window.location.href="admin.php?hotspotadmin=' . $objQuestion->id . '&' . api_get_cidreq() . '&exerciseId=' . $quiz_id . $add_lp_id_parameter .$evaluation_link. '"</script>';
        }
    } else {
     

        if (!empty($pictureName)) {
            echo '<img src="../document/download.php?doc_url=%2Fimages%2F' . $pictureName . '" border="0">';
        }

        if (!empty($msgErr)) {
            //Display::display_normal_message($msgErr); //main API
            $_SESSION["display_normal_message"]=get_lang($msgErr);
        }

        // display the form
        
        echo '<div class="actions" style="position:relative;">';
        echo '<div id="d_clear" ></div>';
        $form->display();
        
        $simplifyQuestionsAuthoring = ($my_quiz->selectSimplifymode()== 1) ? 'true' : 'false';
        
            if($simplifyQuestionsAuthoring=='true'){
            //echo '<script type="text/javascript" src="' . api_get_path(WEB_PATH) . 'main/application/courseInfo/assets/js/infoFunctions.js"></script>';
            echo '<script type="text/javascript" src="' . api_get_path(WEB_PATH) . 'main/application/courseInfo/assets/js/infoModel.js"></script>';
            //echo '<link rel="stylesheet" href="' . api_get_path(WEB_PATH) . 'main/application/courseInfo/assets/css/info.css">';
                if(isset($_GET['answerType'])){
                    $type = $_GET['answerType'];
                }elseif(isset($_GET['type'])){
                    $type = $_GET['type'];
                }
                
                switch ($type){
                    case '4':

                        if(isset($_POST['submitQuestion'])){  
                         
                            $nb_matches = isset($_POST['nb_matches']) ? $_POST['nb_matches'] :  2;

                            $nb_options = isset($_POST['nb_options']) ? $_POST['nb_options'] :  2;    
                        
                        }else{                    
                            $answer = new Answer($_GET['editQuestion']);
                            $answer->read();                    
                            $nb_matches = $nb_options = 0;
                            if($answer->nbrAnswers > 0){
                                for ($i = 1; $i <= $answer->nbrAnswers; $i++) {
                                    if ($answer->isCorrect($i)) {
                                        $nb_matches++;                           
                                    } else {
                                        $nb_options++;                           
                                    }
                                }
                            }else{
                                $nb_options = 2;
                            }                                         
                        }
                        
                        $html_script .= '<script>
                            $(document).ready(function() {
                                function replaceAll( text, search, reeplace ){                                
                                    while (text.toString().indexOf(search) != -1)
                                        text = text.toString().replace(search,reeplace);
                                        return text;
                                }
                                var path_img_save =  "courses/'.api_get_course_path().'/document/images/"; 
                                var path_img_data = "courses/'.api_get_course_path().'/document/images/";
                                var url = "'. api_get_path(WEB_CODE_PATH) . 'index.php?module=courseInfo&cmd=InfoAjax&func=uploadImageCrop2";                                   
                                ';
                                
                                for ($i = 1; $i <= $nb_options; ++$i) {
                                    $rand = rand();
                                    $html_script .= '$( ".divfileanswer'.$i.'").append( "<form id=\"answer_file-'.$i.'\" enctype=\"multipart\/form-data\" name=\"answer_file_'.$i.'\" method=\"post\" > <input id=\"left_input_file_'.$i.'\" type=\"file\" class=\"answer_file-'.$i.'\" name=\"answer_file_'.$i.'\" /></form><div id=\"answer_progress_bar_'.$i.'\" style=\"height:30px; margin-top:5px; border-radius:5px; text-align:center; line-height:28px; font-weight:bold; font-size:14px;\"></div>" );';
                                    $html_script .= '$( ".divfileoption'.$i.'").append( "<form id=\"option_file-'.$i.'\" enctype=\"multipart\/form-data\" name=\"option_file_'.$i.'\" method=\"post\" > <input id=\"right_input_file_'.$i.'\" type=\"file\" class=\"option_file-'.$i.'\" name=\"option_file_'.$i.'\" /></form><div id=\"option_progress_bar_'.$i.'\" style=\"height:30px; margin-top:5px; border-radius:5px; text-align:center; line-height:28px; font-weight:bold; font-size:14px;\"></div>" );';                                    
                                    //script crop
                                    $html_script .= '                                                
                                                            $("#left_input_file_'.$i.'").NiceInputUpload(120, 120, "left");                                                                                                                        
                                                            $("#answer_progress_bar_'.$i.'").hide();    
                                                            $("#left_input_file_'.$i.'").fileupload({
                                                                url: url,
                                                                acceptFileTypes: /^image\/(gif|jpeg|png)$/,
                                                                formData: {
                                                                    path: path_img_save,
                                                                    name: "'.$rand.'_",
                                                                    min_width: 240,
                                                                    min_height: 140,
                                                                    max_width: 530,
                                                                    max_height: 500,
                                                                    post_id: '.$i.',
                                                                    side: "answer"
                                                                },
                                                                done: function (e, data) {   

                                                                    var filename = data.files[0].name;
                                                                    filename = replaceAll(filename," ","_");
                                                                    var ext = filename;
                                                                    ext = (ext.substring(ext.lastIndexOf("."))).toLowerCase();
                                                                    filename = filename.split(".");
                                                                    filename = filename[0];



                                                                  
                                                                    InfoModel.showActionDialogCropQuiz(path_img_data, 240, 140, false, true, "'.$rand.'_"+filename+"_answer", filename+"_'.$rand.'",ext, "", '.$i.', "answer");
                                                                  
                                                                  
                                                                },
                                                                progressall: function (e, data) {
                                                                    
                                                                    $("#answer_progress_bar_'.$i.'").show("slow");
                                                                    var progress = parseInt(data.loaded / data.total * 100, 10);
                                                                    //InfoModel.showProgressBar(progress);
                                                                    $("#answer_progress_bar_'.$i.'").css({width : progress + "%", background : "skyblue"});
                                                                    $("#answer_progress_bar_'.$i.'").html(progress + "%");
                                }
                                   
                                                            });';
                                    $rand2 = rand();
                                    $html_script .= '                                                
                                                            $("#right_input_file_'.$i.'").NiceInputUpload(120, 120, "right");                                                            
                                                            $("#option_progress_bar_'.$i.'").hide();
                                                            $("#right_input_file_'.$i.'").fileupload({
                                                                url: url,
                                                                acceptFileTypes: /^image\/(gif|jpeg|png)$/,
                                                                formData: {
                                                                    path: path_img_save,
                                                                    name: "'.$rand2.'_",
                                                                    min_width: 240,
                                                                    min_height: 140,
                                                                    max_width: 530,
                                                                    max_height: 500,
                                                                    post_id: '.$i.',
                                                                    side: "option"
                                                                },
                                                                done: function (e, data) {   
                                                                    
                                                                    var filename = data.files[0].name;
                                                                    filename = replaceAll(filename," ","_");
                                                                    var ext = filename;
                                                                    ext = (ext.substring(ext.lastIndexOf("."))).toLowerCase();
                                                                    filename = filename.split(".");
                                                                    filename = filename[0];
                                                                  

                                                                  
                                                                    InfoModel.showActionDialogCropQuiz(path_img_data, 240, 140, false, true, "'.$rand2.'_"+filename+"_option", filename+"_'.$rand2.'", ext, "", '.$i.', "option");
                                                                  
                                                                  
                                                                },
                                                                progressall: function (e, data) {
                                                                    
                                                                    $("#option_progress_bar_'.$i.'").show("slow");
                                                                    var progress = parseInt(data.loaded / data.total * 100, 10);
                                                                    //InfoModel.showProgressBar(progress);
                                                                    $("#option_progress_bar_'.$i.'").css({width : progress + "%", background : "skyblue"});
                                                                    $("#option_progress_bar_'.$i.'").html(progress + "%");
                                                                }
                                                            });';

                                }
                                   
                        $html_script .= '});</script>';
                        
                        echo $html_script;
                    break;
            }
        ?>
        <script>
            $(document).ready(function() {
                function replaceAll( text, search, reeplace ){                                
                    while (text.toString().indexOf(search) != -1)
                        text = text.toString().replace(search,reeplace);
                        return text;
                }
            
                $("#left_more").click(function(){                    
                    
                
                   <?php

                        $html_script1= '                   
                                                            var numrand = Math.floor((Math.random()*9999)+1);
                                                            
                                                            var path_img_save =  "courses/'.api_get_course_path().'/document/images/"; 
                                                            var path_img_data = "courses/'.api_get_course_path().'/document/images/";
                                                            var url = "'. api_get_path(WEB_CODE_PATH) . 'index.php?module=courseInfo&cmd=InfoAjax&func=uploadImageCrop2";
                                                            var rowCount = $("#table_match_left >tbody >tr").length;
                                                            $("#left_input_file_"+rowCount).NiceInputUpload(120, 120, "left");
                                                            $("#answer_progress_bar_"+rowCount).hide();
                                                            $("#left_input_file_"+rowCount).fileupload({
                                                                url: url,
                                                                acceptFileTypes: /^image\/(gif|jpeg|png)$/,
                                                                formData: {
                                                                    path: path_img_save,
                                                                    name: numrand+"_",
                                                                    min_width: 240,
                                                                    min_height: 140,
                                                                    max_width: 530,
                                                                    max_height: 500,
                                                                    post_id: rowCount,
                                                                    side: "answer"
                                                                },
                                                                done: function (e, data) {   
                                                                    
                                                                    var filename = data.files[0].name;
                                                                    filename = replaceAll(filename," ","_");
                                                                    var ext = filename;
                                                                    ext = (ext.substring(ext.lastIndexOf("."))).toLowerCase();
                                                                    filename = filename.split(".");
                                                                    filename = filename[0];
                                                                    InfoModel.showActionDialogCropQuiz(path_img_data, 240, 140, false, true, numrand+"_"+filename+"_answer", filename+"_"+numrand, ext, "", rowCount, "answer");                                                                   
                                                                },
                                                                progressall: function (e, data) {                                                                  
                                                                    $("#answer_progress_bar_"+rowCount).show("slow");
                                                                    var progress = parseInt(data.loaded / data.total * 100, 10);
                                                                    $("#answer_progress_bar_"+rowCount).css({width : progress + "%", background : "skyblue"});
                                                                    $("#answer_progress_bar_"+rowCount).html(progress + "%");
        }
                                                            });';
        
                        echo $html_script1;                   
                   ?>
        
                });
            });
            
        </script>
        <script>
            $(document).ready(function() {
                function replaceAll( text, search, reeplace ){                                
                    while (text.toString().indexOf(search) != -1)
                        text = text.toString().replace(search,reeplace);
                        return text;
                }
            
                $("#right_more").click(function(){
                   <?php

                        $html_script2= '                    var numrand = Math.floor((Math.random()*9999)+1);
                                                            var path_img_save =  "courses/'.api_get_course_path().'/document/images/"; 
                                                            var path_img_data = "courses/'.api_get_course_path().'/document/images/";
                                                            var url = "'. api_get_path(WEB_CODE_PATH) . 'index.php?module=courseInfo&cmd=InfoAjax&func=uploadImageCrop2";
                                                            var rowCount = $("#table_match_right >tbody >tr").length;
                                                            
                                                            $("#right_input_file_"+rowCount).NiceInputUpload(120, 120, "right");                                                            
                                                            $("#option_progress_bar_"+rowCount).hide();
                                                            $("#right_input_file_"+rowCount).fileupload({
                                                                url: url,
                                                                acceptFileTypes: /^image\/(gif|jpeg|png)$/,
                                                                formData: {
                                                                    path: path_img_save,
                                                                    name: numrand+"_",
                                                                    min_width: 240,
                                                                    min_height: 140,
                                                                    max_width: 530,
                                                                    max_height: 500,
                                                                    post_id: rowCount,
                                                                    side: "option"
                                                                },
                                                                done: function (e, data) {   
                                                                    
                                                                    var filename = data.files[0].name;
                                                                    filename = replaceAll(filename," ","_");
                                                                    var ext = filename;
                                                                    ext = (ext.substring(ext.lastIndexOf("."))).toLowerCase();
                                                                    filename = filename.split(".");
                                                                    filename = filename[0];
                                                                  

                                                                  
                                                                    InfoModel.showActionDialogCropQuiz(path_img_data, 240, 140, false, true, numrand+"_"+filename+"_option", filename+"_"+numrand, ext, "", rowCount, "option");
                                                                  
                                                                  
                                                                },
                                                                progressall: function (e, data) {                                                                   
                                                                   
                                                                    $("#option_progress_bar_"+rowCount).show("slow");
                                                                    var progress = parseInt(data.loaded / data.total * 100, 10);
                                                                    //InfoModel.showProgressBar(progress);
                                                                    $("#option_progress_bar_"+rowCount).css({width : progress + "%", background : "skyblue"});
                                                                    $("#option_progress_bar_"+rowCount).html(progress + "%");
                                                                }
                                                                
                                                            });';
                        
                        echo $html_script2;
                   
                   ?>
                   
                });
            });
            
        </script>
        <?php
        }
        
        
        echo '</div>';
    }
}

if (isset($_GET['viewQuestion'])) {
    $TBL_QUESTIONS = Database::get_course_table(TABLE_QUIZ_QUESTION);
    $i = 1;
    $nbrQuestions = Security::remove_XSS($_GET["totTpl"]);
    $origin = 'fromgallery';
    // This query must be replaced
    $sql = "SELECT * FROM $TBL_QUESTIONS WHERE id=" . Database::escape_string(Security::remove_XSS($_GET["viewQuestion"]));
    $result = Database::query($sql, __FILE__, __LINE__);

    // Get quiz titl

    $qnExist = Database::num_rows($result);
    if ($qnExist > 0) {
        // Content of secondary actions
        echo '<div class="actions" id="content_with_secondary_actions"><div style="width: 100%; padding: 1px;">';
        // Quiz title
        echo '<div>';
        showQuestion($_GET['viewQuestion'], false, $origin, $i, $nbrQuestions);
        echo '</div></div></div></div>';
    } else {
        if ($_GET["viewQuestion"] <= $nbrQuestions) {
            if ($_GET['prev']) {
                echo '<script type="text/javascript">window.location.href="admin.php?' . api_get_cidreq() . '&fromTpl=1&startPage=' . ($startPage - 1) . '&totTpl=' . $_GET["totTpl"] . '&viewQuestion=' . ($_GET['viewQuestion'] - 1) . '&fromExercise=' . Security::remove_XSS($_GET['fromExercise']) . '&prev=Y"</script>';
            } else {
                echo '<script type="text/javascript">window.location.href="admin.php?' . api_get_cidreq() . '&fromTpl=1&startPage=' . ($startPage + 1) . '&totTpl=' . $_GET["totTpl"] . '&viewQuestion=' . ($_GET['viewQuestion'] + 1) . '&fromExercise=' . Security::remove_XSS($_GET['fromExercise']) . '&next=Y"</script>';
            }
        }
    }
}
?>
<input type="hidden" id="webPath" value="<?php echo urlencode(api_get_path(WEB_PATH)); ?>" />