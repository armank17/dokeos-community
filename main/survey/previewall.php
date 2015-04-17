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
$table_survey                 = Database::get_course_table(TABLE_SURVEY);
$table_survey_question        = Database::get_course_table(TABLE_SURVEY_QUESTION);
$table_survey_question_option = Database::get_course_table(TABLE_SURVEY_QUESTION_OPTION);
$table_course                 = Database::get_main_table(TABLE_MAIN_COURSE);
$table_user                   = Database::get_main_table(TABLE_MAIN_USER);

$origin = '';
$origin_string='';
if (isset($_GET['origin'])) {
	$origin =  Security::remove_XSS($_GET['origin']);
	$origin_string = '&origin='.$origin;
}

// We exit here if ther is no valid $_GET parameter
if (!isset($_GET['survey_id']) OR !is_numeric($_GET['survey_id'])){

	Display::display_tool_header();
	Display :: display_error_message(get_lang('InvallidSurvey'), false, true);

	Display::display_tool_footer();
	exit;
}


// getting the survey information
$survey_id = Security::remove_XSS($_GET['survey_id']);
$survey_data = survey_manager::get_survey($survey_id);

if (empty($survey_data)) {

	Display::display_tool_header();
	Display :: display_error_message(get_lang('InvallidSurvey'), false, true);

	Display::display_tool_footer();
	exit;
}

$urlname = strip_tags(api_substr(api_html_entity_decode($survey_data['title'],ENT_QUOTES,$charset), 0, 40));
if (api_strlen(strip_tags($survey_data['title'])) > 40) {
	$urlname .= '...';
}

// breadcrumbs
$interbreadcrumb[] = array ("url" => 'survey_list.php', 'name' => get_lang('SurveyList'));
$interbreadcrumb[] = array ("url" => "survey.php?survey_id=".$survey_id, "name" => $urlname);



// Header

if ($origin == 'learnpath') {
	include_once api_get_path(INCLUDE_PATH).'reduced_header.inc.php';
} else {
  Display::display_tool_header();
Display::display_introduction_section('survey', 'left');
// actions bar
echo '<div class="actions">';
echo '<a href="survey.php?'.  api_get_cidreq().'&survey_id='.Security::remove_XSS($_GET['survey_id']).'">'.Display::return_icon('pixel.gif', get_lang('BackTo').' '.strtolower(get_lang('Survey')), array('class' => 'toolactionplaceholdericon toolactionback')).get_lang('BackTo').' '.strtolower(get_lang('Survey')).'</a>';
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

select{
    width:100%;
}
	</style>
<?php

// start the content div
echo '<div id="content">';
// We exit here is the first or last question is a pagebreak (which causes errors)
SurveyUtil::check_first_last_question($survey_id, true);

// only a course admin is allowed to preview a survey: you are NOT a course admin => error message
// only a course admin is allowed to preview a survey: you are a course admin
if (api_is_platform_admin() || api_is_course_admin() || (api_is_course_admin() && $_GET['isStudentView']=='true') || api_is_allowed_to_session_edit(false,true)) {
	// survey information
	$textIntroduction = get_lang('SurveyIntroduction');
            echo '<div class="title-dokeos">'.$survey_data['survey_title'].'</div>';
            echo '<div class="survey-border">';
            echo '<div id="survey_title" class="survey_title_cus">'. $textIntroduction .'</div>';
            echo '<div id="survey_subtitle">'.$survey_data['survey_subtitle'].'</div>';

	// displaying the survey introduction
	if (!isset($_GET['show']))
	{
		echo '<div id="survey_content" class="survey_content">'.$survey_data['survey_introduction'].'</div>';
		$limit = 0;
	}

	// displaying the survey thanks message
	if(isset($_POST['finish_survey']))
	{    echo '<div id="survey_title" class="">'. get_lang('SurveyFinished') .'</div>';
             echo '<div id="survey_content" class="survey_content"><strong></strong>'.$survey_data['survey_thanks'].'</div>';
             echo '<style>.survey_title_cus{display:none;}</style>';
             echo '</div></div>';

            exit();
	}

        $sql = "SELECT * FROM $table_survey_question WHERE survey_id = '".Database::escape_string($survey_id)."' AND	type <> '".Database::escape_string('pagebreak')."' ORDER BY sort ASC";
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
				$questions[$row['sort']]['options'][$row['question_option_id']] = $row['option_text'];
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
        $show = (isset($_GET['show'])) ? (int)$_GET['show'] + 1 : 0;

    echo '<form id="question" name="question" method="post" action="'.api_get_self().'?'.  api_get_cidreq().'&survey_id='.Security::remove_XSS($survey_id).'&show='.$show.$origin_string.'">';
	if(is_array($questions) && count($questions)>0)
	{
		foreach ($questions as $key=>$question)
		{
                        if(($question['display']=='horizontal') && ($textIntroduction)){
                        echo '<div class="quiz_content_actions" style="">';	           
                        echo '<style>.survey_title_cus{display:none}.quiz_content_actions{margin-top:0px;}</style>';
                        
                        }else{
			echo '<div class="quiz_content_actions" style="">';
                        echo '<style>.survey_title_cus{display:none}.quiz_content_actions{margin-top:0px;}</style>';
                        }
			$display = new $question['type'];
			$display->render_question($question);
			echo '</div>';	
		}
	}
    echo '<div style="text-align: right; margin-right: 20px;">';
        echo '<div class="pull-bottom"><button type="submit" name="';
        echo ($show==0)?'next_survey_page':'finish_survey';
        echo '" class="next" style="float: none !important">';
        echo ($show == 0)? get_lang('Validate'):get_lang('FinishSurvey');
        echo ' </button></div>';
    echo '</div>';
    echo '</form>';
    
} else {
	Display :: display_error_message(get_lang('NotAllowed'), false, true);
}

// close the content div
echo '</div>';
echo '</div>'; // close the action div
 // bottom actions bar
//echo '<div class="actions">';
//echo '</div>';
// Footer
if ($origin != 'learnpath') {
Display::display_tool_footer(); 
}
?>
