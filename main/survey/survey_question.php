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

// breadcrumbs
$interbreadcrumb[] = array ("url" => 'survey_list.php', 'name' => get_lang('SurveyList'));
$interbreadcrumb[] = array ("url" => "survey.php?survey_id=".$survey_id, "name" => $urlname);


// Header
//Display :: display_header(get_lang('SurveyPreview'));
//echo '<div id="print" style="display:none">'.get_lang("document is sent to print").'</div>';
echo '<div id="print" style="display:none">'.get_lang("SentToPrint").'</div>';
if ($origin == 'learnpath') {
	include_once api_get_path(INCLUDE_PATH).'reduced_header.inc.php';
} else {
  Display::display_tool_header();

echo '<script type="text/javascript">
        function survey_print()	{
                var header = document.getElementById("headerdiv"),
                    footer = document.getElementById("footerdiv");
                
                if(header) {
                    header.style.display = "none";
                }
		if(footer) {
                    footer.style.display = "none";
		}

 		 window.print();
                 $("#print").dialog({title:"'.get_lang("Survey").'",resizable:false});
                     
                //window.onbeforeprint = function () {
                
                //    }
//                 window.onafterprint = function () {
//                   $("#print").dialog({title:"'.get_lang("Survey").'",resizable:false});
//                 }

		if(header) {
			header.style.display = "";
		}
		if(footer) {
			footer.style.display = "";
		}
	}

</script>
<style type="text/css">
.custom-checkbox label,
.custom-radio label {
	display: block;
	position: relative;
	z-index: 1;
	padding-right: 1em;
	line-height: 3px;
	padding: 0px 5px 25px 30px;
	padding-top:10px;
	cursor: pointer;
}

#survey_title {
    border:1px solid #cccccc
}

#survey_content {
    border:1px solid #cccccc;
    background:#ffffff;
    margin-top:-16px;
}
</style>';

Display::display_introduction_section('survey', 'left');
// actions bar
echo '<div id="headerdiv" style="display:;">';
echo '<div class="actions">';
echo '<a href="survey.php?survey_id='.Security::remove_XSS($_GET['survey_id']).'">'.Display::return_icon('pixel.gif', get_lang('BackTo').' '.strtolower(get_lang('Survey')), array('class' => 'toolactionplaceholdericon toolactionback')).get_lang('BackTo').' '.strtolower(get_lang('Survey')).'</a>';
echo '<a href="javascript:survey_print()">'.Display::return_icon('pixel.gif',get_lang('Print'),array('class'=>'toolactionplaceholdericon toolactionprint32')).' '.get_lang('Print').'</a>';
//echo '<a class="PrintSurvey" href="">'.Display::return_icon('pixel.gif',get_lang('Print'),array('class'=>'toolactionplaceholdericon toolactionprint32')).' '.get_lang('Print').'</a>';
echo '</div>';
echo '</div>';
}

// start the content div
echo '<div id="content">';

if (api_is_course_admin() || (api_is_course_admin() && $_GET['isStudentView']=='true') || api_is_allowed_to_session_edit(false,true)) {
	// survey information
//	echo '<div class="actions" style="height:auto;min-height:400px;">';
	echo '<div id="survey_title"><b>'.$survey_data['survey_title'].'</b></div><br/>';
	echo '<div id="survey_subtitle">'.$survey_data['survey_subtitle'].'</div>';
	echo '<div id="survey_content" class="survey_content">'.$survey_data['survey_introduction'].'</div>';


	$sql = "SELECT * FROM $table_survey_question
			WHERE survey_id = '".Database::escape_string($survey_id)."'
			AND	type <> '".Database::escape_string('pagebreak')."' ORDER BY sort ASC";
		$result = Database::query($sql, __FILE__, __LINE__);
		$numrows = Database::num_rows($result);


		// Getting all the questions for this page and add them to a multidimensional array where the first index is the page.
		// as long as there is no pagebreak fount we keep adding questions to the page
		$paged_questions = array();

		$show = $_GET['show'];

		while ($row = Database::fetch_array($result))
		{
			$paged_questions[] = $row['question_id'];
		}
		for($i=0;$i<sizeof($paged_questions);$i++)
		{
		$sql = "SELECT 	survey_question.question_id, survey_question.survey_id, survey_question.survey_question, survey_question.display, survey_question.sort, survey_question.type, survey_question.max_value,
							survey_question_option.question_option_id, survey_question_option.option_text, survey_question_option.sort as option_sort
					FROM $table_survey_question survey_question
					LEFT JOIN $table_survey_question_option survey_question_option
					ON survey_question.question_id = survey_question_option.question_id
					WHERE survey_question.survey_id = '".Database::escape_string($survey_id)."'
					AND survey_question.question_id = ".$paged_questions[$i]."
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


	echo '<form id="question" name="question" method="post" action="'.api_get_self().'?survey_id='.Security::remove_XSS($survey_id).'&show='.$show.$origin_string.'">';
	if(is_array($questions) && count($questions)>0)
	{
		echo '<table class="CustSurveyA" border="0" cellpadding="4" cellspacing="4" width="100%" style="height:auto;margin-left:0px;border:1px solid #b8b8b6;border-radius:5px;-moz-border-radius: 5px;-webkit-border-radius: 5px;" bordercolor="#ccc">';
		foreach ($questions as $key=>$question)
		{
		//	echo '<div class="quiz_content_actions" style="width:95%;margin-left:10px;">';
			echo '<tr><td width="3%" valign="top">'.$key.'.</td><td width="95%">';
			$display = new $question['type'];
			$display->render_question($question);
			echo '</td></tr><br/>';
		//	echo '</div>';
		}
		echo '</table>';
	}
	echo '</form>';
		}

} else {
	Display :: display_error_message(get_lang('NotAllowed'), false, true);
}



// close the content div
echo '</div>';

 // bottom actions bar
///echo '<div id="footerdiv" style="display:;"><div class="actions">';
//echo '</div></div>';
// Footer
//Display :: display_footer();
Display::display_tool_footer();
?>
