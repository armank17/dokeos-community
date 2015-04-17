<?php
/* For licensing terms, see /dokeos_license.txt */

/**
*	@package dokeos.survey
* 	@author unknown, the initial survey that did not make it in 1.8 because of bad code
* 	@author Patrick Cool <patrick.cool@UGent.be>, Ghent University: cleanup, refactoring and rewriting large parts of the code
*	@author Julio Montoya Armas <gugli100@gmail.com>, Dokeos: Personality Test modifications
* 	@version $Id: survey_list.php 10680 2007-01-11 21:26:23Z pcool $
*
* 	@todo use quickforms for the forms
*/

// name of the language file that needs to be included
$language_file = 'survey';

// including the global dokeos file
require ('../inc/global.inc.php');

// including additional libraries
//require_once (api_get_path(LIBRARY_PATH)."/survey.lib.php");
require_once('survey.lib.php');

// Database table definitions
$table_survey 					= Database :: get_course_table(TABLE_SURVEY);
$table_survey_question 			= Database :: get_course_table(TABLE_SURVEY_QUESTION);
$table_survey_question_option 	= Database :: get_course_table(TABLE_SURVEY_QUESTION_OPTION);
$table_course 					= Database :: get_main_table(TABLE_MAIN_COURSE);
$table_user 					= Database :: get_main_table(TABLE_MAIN_USER);

$origin = '';
$origin_string='';
if (isset($_GET['origin'])) {
	$origin =  Security::remove_XSS($_GET['origin']);
	$origin_string = '&origin='.$origin;
}

// We exit here if ther is no valid $_GET parameter
if (!isset($_GET['survey_id']) OR !is_numeric($_GET['survey_id'])){
//	Display :: display_header(get_lang('SurveyPreview'));
	Display::display_tool_header();
	Display :: display_error_message(get_lang('InvallidSurvey'), false, true);
//	Display :: display_footer();
	Display::display_tool_footer();
	exit;
}


// getting the survey information
$survey_id = Security::remove_XSS($_GET['survey_id']);
$survey_data = survey_manager::get_survey($survey_id);

if (empty($survey_data)) {
//	Display :: display_header(get_lang('SurveyPreview'));
	Display::display_tool_header();
	Display :: display_error_message(get_lang('InvallidSurvey'), false, true);
//	Display :: display_footer();
	Display::display_tool_footer();
	exit;
}

$urlname = strip_tags(api_substr(api_html_entity_decode($survey_data['title'],ENT_QUOTES,$charset), 0, 40));
if (api_strlen(strip_tags($survey_data['title'])) > 40) {
	$urlname .= '...';
}

if ($origin == 'learnpath') {
    if (empty($charset)) {
        $charset = 'ISO-8859-15';
    }
    //include_once api_get_path(INCLUDE_PATH).'reduced_header.inc.php';
    $htmlHeadXtra[] = '<link type="text/css" rel="stylesheet" media="all" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/css/dokeos/jquery-ui-1.8.6.custom.css"/>';
    $htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-1.4.2.min.js"></script>';
    $htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery.iframe-auto-height"></script>';
    $htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-ui/js/jquery-ui-1.10.1.custom.min.js"></script>';    
    Display::display_reduced_header();
}

else {
    Display::display_tool_header();
    Display::display_introduction_section('survey', 'left');
// actions bar
echo '<div class="actions">';
if($origin == 'survey_list'){
    echo '<a href="survey_list.php?'.  api_get_cidreq().'&survey_id='.Security::remove_XSS($_GET['survey_id']).'">'.Display::return_icon('pixel.gif', get_lang('BackTo').' '.strtolower(get_lang('Survey')), array('class' => 'toolactionplaceholdericon toolactionback')).get_lang('BackTo').' '.strtolower(get_lang('Survey')).'</a>';
}
else{
    echo '<a href="survey.php?'.  api_get_cidreq().'&survey_id='.Security::remove_XSS($_GET['survey_id']).'">'.Display::return_icon('pixel.gif', get_lang('BackTo').' '.strtolower(get_lang('Survey')), array('class' => 'toolactionplaceholdericon toolactionback')).get_lang('BackTo').' '.strtolower(get_lang('Survey')).'</a>';
}
echo '</div>';
}
echo '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/customInput.jquery.js" language="javascript"></script>';
?>

<script type="text/javascript">
	// Run the script on DOM ready:
	$(function(){
		$('input').customInput();
	});
	</script>
<style type="text/css">
    #question button{
        float: none;
        
    }   
.custom-checkbox label,
.custom-radio label {
	display: block;
	position: relative;
	z-index: 1;
	padding-right: 1em;
	line-height: 15px;
	padding: 0px 5px 25px 30px;
	padding-top:10px;
	cursor: pointer;
}
	</style>
<?php

if(isset($_REQUEST['tool']) && $_REQUEST['tool'] == 'scenario') {
	$tool = 'scenario';
}
if(isset($_REQUEST['step_id'])) {
	$step_id = $_REQUEST['step_id'];
}
if(isset($_REQUEST['activity_id'])) {
	$activity_id = $_REQUEST['activity_id'];
}
$courseInfo = api_get_course_info(api_get_course_id());
echo '<div id="continueContainer" name="continueContainer"><a onclick="goto(\''.api_get_path(WEB_COURSE_PATH).$courseInfo['path'].'/index.php'.'\')"><button id="continue" name="continue" class="continue" style="display:none;position: absolute; font-size: 18px; z-index: 100;">Continue</button></a></div>';
echo "<script>function goto (href) { window.parent.location.href = href }</script>";
// start the content div
echo '<div id="content">';
// We exit here is the first or last question is a pagebreak (which causes errors)
SurveyUtil::check_first_last_question($survey_id, true);

// only a course admin is allowed to preview a survey: you are NOT a course admin => error message

/*
if (!api_is_allowed_to_edit(false,true))
{
	Display :: display_error_message(get_lang('NotAllowed'), false);
}*/
// only a course admin is allowed to preview a survey: you are a course admin
if (api_is_platform_admin() || api_is_course_admin() || (api_is_course_admin() && $_GET['isStudentView']=='true') || api_is_allowed_to_session_edit(false,true)) {
	// survey information
//	echo '<div class="actions" style="height:auto;min-height:250px;">';
        echo '<div class="title-dokeos">'.$survey_data['survey_title'].'</div>';
        echo '<div class="survey-border">';

	// displaying the survey introduction
	if (!isset($_GET['show']))
	{
//		echo '<div id="survey_content" class="survey_content">'.$survey_data['survey_introduction'].'</div>';
            echo '<div id="survey_title" class="survey_content">'.get_lang('SurveyIntroduction').'</div>';
            echo '<div style="padding:10px;">'.$survey_data['survey_introduction'].'</div>';
            
		$limit = 0;
	}

	// displaying the survey thanks message
	if(isset($_POST['finish_survey']))
	{

		if($_REQUEST['tool'] == 'scenario'){

			$TBL_SCENARIO_ACTIVITY_VIEW = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY_VIEW);
			$step_id = $_REQUEST['step_id'];
			$activity_id = $_REQUEST['activity_id'];

			$sql_check = "SELECT * FROM $TBL_SCENARIO_ACTIVITY_VIEW WHERE step_id = ".$step_id." AND activity_id = ".$activity_id." AND user_id = ".api_get_user_id();
			$res_check = Database::query($sql_check, __FILE__, __LINE__);
			$num_rows = Database::num_rows($res_check);
			if($num_rows == 0) {
				$sql = "INSERT INTO $TBL_SCENARIO_ACTIVITY_VIEW (activity_id, step_id, user_id, view_count, score, status) VALUES($activity_id, $step_id, ".api_get_user_id().", 1, '".$my_total_score."', 'completed')";
			}
			else {
				$sql = "UPDATE $TBL_SCENARIO_ACTIVITY_VIEW SET view_count = view_count + 1, score = '".$my_total_score."' WHERE activity_id = ".$activity_id." AND step_id = ".$step_id." AND user_id = ".api_get_user_id();
			}

			Database::query($sql,__FILE__,__LINE__);
                        echo '<script>
		
    function positioning_btnContinue()	{
    
            $("#continue").hide();

            offset = $("#content").offset();
            width = $("#content").width();
            height = $("#content").height();
            console.log((offset.left + width) + " " + (offset.top + height) );
            $("#continue").css("width","140px");
            $("#continue").css("left",(offset.left + width)-130);
            $("#continue").css("top",(offset.top + height)-35);

            $("#continue").show();
		}

    $( window ).resize(function() {
            positioning_btnContinue();
    });


    setTimeout(function(){

            positioning_btnContinue();

                            },1000);

    $( document ).ready(function() {
    //	$(".data_table").append($("<tr></tr>"));

    	positioning_btnContinue();

    });

</script>';
		}

//		echo '<div id="survey_content" class="survey_content"><strong>'.get_lang('SurveyFinished').' </strong>'.$survey_data['survey_thanks'].'</div>';
                echo '<div id="survey_title" class="survey_content">'.get_lang('SurveyFinished').'</div>';
    echo '<div style="padding:10px;">'.$survey_data['survey_thanks'].'</div>';
                
            exit();
	}

	$sql = "SELECT * FROM $table_survey_question
			WHERE survey_id = '".Database::escape_string($survey_id)."'
			AND	type <> '".Database::escape_string('pagebreak')."' ORDER BY sort ASC";
		$result = Database::query($sql, __FILE__, __LINE__);
		$numrows = Database::num_rows($result);

	if (isset($_GET['show']))
	{
		// Getting all the questions for this page and add them to a multidimensional array where the first index is the page.
		// as long as there is no pagebreak fount we keep adding questions to the page
		$paged_questions = array();

		$show = $_GET['show'];
		while ($row = Database::fetch_array($result))
		{
			$paged_questions[] = $row['question_id'];
		}

		$sql = "SELECT 	survey_question.question_id, survey_question.survey_id, survey_question.survey_question, survey_question.display, survey_question.sort, survey_question.type, survey_question.max_value,
							survey_question_option.question_option_id, survey_question_option.option_text, survey_question_option.sort as option_sort
					FROM $table_survey_question survey_question
					LEFT JOIN $table_survey_question_option survey_question_option
					ON survey_question.question_id = survey_question_option.question_id
					WHERE survey_question.survey_id = '".Database::escape_string($survey_id)."'
					AND survey_question.question_id = ".$paged_questions[$show]."
					ORDER BY survey_question.sort, survey_question_option.sort ASC";
		$result = Database::query($sql, __FILE__, __LINE__);
		$questions = array();
		while ($row = Database::fetch_array($result))
		{
			// if the type is not a pagebreak we store it in the $questions array
			if($row['type'] <> 'pagebreak')
			{
				$questions[$row['sort']]['question_id'] = $row['question_id'];
				$questions[$row['sort']]['survey_id'] = $row['survey_id'];
				$questions[$row['sort']]['survey_question'] = $row['survey_question'];
				$questions[$row['sort']]['display'] = $row['display'];
				$questions[$row['sort']]['type'] = $row['type'];
				$questions[$row['sort']]['options'][intval($row['option_sort'])] = $row['option_text'];
				$questions[$row['sort']]['maximum_score'] = $row['max_value'];
			}
			// if the type is a pagebreak we are finished loading the questions for this page
			else
			{
				break;
			}
		}
	}
	// Displaying the form with the questions
	if (isset($_GET['show']))
	{
		$show = (int)$_GET['show'] + 1;
	}
	else
	{
		$show = 0;
	}

	if(isset($_REQUEST['tool'])) {
		$tool = $_REQUEST['tool'];
		$param_scenario = "&tool=".$tool."&step_id=".$step_id."&activity_id=".$activity_id;
	}
	else {
		$param_scenario = "";
	}

	echo '<form id="question" name="question" method="post" action="'.api_get_self().'?'.  api_get_cidreq().'&survey_id='.Security::remove_XSS($survey_id).'&show='.$show.$origin_string.$param_scenario.'">';

	if(is_array($questions) && count($questions)>0)
	{
		foreach ($questions as $key=>$question)
		{
                        if($question['display']=='horizontal'){
//                        echo '<div class="quiz_content_actions" style="width:95%;margin-left:10px;display:inline-block;height:auto;quiz_content_actions;padding: 6px;">';
                             echo '<style>.quiz_content_actions{padding-bottom:10px; height:auto; margin-top:0px !important}</style>';
                            echo '<div class="quiz_content_actions">';
                        }else{
//			echo '<div class="quiz_content_actions" style="width:95%;margin-left:10px;quiz_content_actions;padding: 6px;">';
                            echo '<style>.quiz_content_actions{padding-bottom:10px; height:auto; margin-top:0px !important}</style>';
                            echo '<div class="quiz_content_actions">';
                        }
			$display = new $question['type'];
			$display->render_question($question);
			echo '</div>';
		}
	}
        echo '<div style="text-align: right; margin-right: 20px;">';
	if (($show < $numrows))
	{
		//echo '<a href="'.api_get_self().'?survey_id='.$survey_id.'&show='.$limit.'">NEXT</a>';
		echo '<div class="pull-bottom"><button type="submit" name="next_survey_page" class="next">'.get_lang('Validate').'   </button></div>';
	}
	if ($show >= $numrows)
	{
		echo '<div class="pull-bottom"><button type="submit" name="finish_survey" class="next">'.get_lang('FinishSurvey').'  </button></div>';
	}
	echo '</div>';
	echo '</form>';
        echo '<div class="clear"></div>';
        echo '</div>';

			} else {
	Display :: display_error_message(get_lang('NotAllowed'), false, true);
}

// close the content div
echo '</div>';

 // bottom actions bar
//echo '<div class="actions">';
//echo '</div>';
// Footer
if ($origin != 'learnpath') {
Display::display_tool_footer();
}
?>
