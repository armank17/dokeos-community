<?php
/* For licensing terms, see /dokeos_license.txt */

/**
*	@package dokeos.survey
* 	@author unknown, the initial survey that did not make it in 1.8 because of bad code
* 	@author Patrick Cool <patrick.cool@UGent.be>, Ghent University: cleanup, refactoring and rewriting large parts of the code
* 	@version $Id: reporting.php 21652 2009-06-27 17:07:35Z herodoto $
*
* 	@todo The question has to be more clearly indicated (same style as when filling the survey)
*/

// name of the language file that needs to be included
$language_file = 'survey';

// including the global dokeos file
require ('../inc/global.inc.php');

// include additional libraries
require_once('survey.lib.php');

$survey_id = Security::remove_XSS($_GET['survey_id']);
$arr_question_id = $_SESSION['id_arrays'];

$htmlHeadXtra[] = '
<style>
.sectiontitle {
    width:auto !important;
}
</style>    
';
// export
/**
 * @todo use export_table_csv($data, $filename = 'export')
 */
if ($_POST['export_report'])
{
	switch($_POST['export_format'])
	{
		case 'xls':
			$survey_data = survey_manager::get_survey($survey_id);
			$filename = 'survey_results_'.$survey_id.'.xls';
			$data = SurveyUtil::export_complete_report_xls($filename, $_GET['user_id'],$arr_question_id);
			exit;
			break;
		case 'csv':
		default:
			$survey_data = survey_manager::get_survey($survey_id);
			$data = SurveyUtil::export_complete_report($_GET['user_id']);
			//$filename = 'fileexport.csv';
			$filename = 'survey_results_'.$survey_id.'.csv';

			header('Content-type: application/octet-stream');
			header('Content-Type: application/force-download');

			if (preg_match("/MSIE 5.5/", $_SERVER['HTTP_USER_AGENT']))
			{
				header('Content-Disposition: filename= '.$filename);
			}
			else
			{
				header('Content-Disposition: attachment; filename= '.$filename);
			}
			if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE'))
			{
				header('Pragma: ');
				header('Cache-Control: ');
				header('Cache-Control: public'); // IE cannot download from sessions without a cache
			}
			header('Content-Description: '.$filename);
			header('Content-transfer-encoding: binary');
			echo $data;
			exit;
			break;
	}
}

// including additional libraries
//require_once (api_get_path(LIBRARY_PATH)."/survey.lib.php");
require_once (api_get_path(LIBRARY_PATH)."/course.lib.php");

// Checking the parameters
SurveyUtil::check_parameters();

/** @todo this has to be moved to a more appropriate place (after the display_header of the code)*/
if (!api_is_allowed_to_edit(false,true))
{
//	Display :: display_header(get_lang('Survey'));
	Display::display_tool_header();
	Display :: display_error_message(get_lang('NotAllowed'), false, true);
//	Display :: display_footer();
	Display::display_tool_footer();
	exit;
}

// Database table definitions
$table_survey 					= Database :: get_course_table(TABLE_SURVEY);
$table_survey_question 			= Database :: get_course_table(TABLE_SURVEY_QUESTION);
$table_survey_question_option 	= Database :: get_course_table(TABLE_SURVEY_QUESTION_OPTION);
$table_course 					= Database :: get_main_table(TABLE_MAIN_COURSE);
$table_user 					= Database :: get_main_table(TABLE_MAIN_USER);
$user_info 						= Database :: get_main_table(TABLE_MAIN_SURVEY_REMINDER); // TODO: To be checked. TABLE_MAIN_SURVEY_REMINDER has not been defined.

// getting the survey information

$survey_data = survey_manager::get_survey($survey_id);
if (empty($survey_data)) {
//	Display :: display_header(get_lang('Survey'));
	Display::display_tool_header();
	Display :: display_error_message(get_lang('InvallidSurvey'), false, true);
//	Display :: display_footer();
	Display::display_tool_footer();
	exit;
}
$urlname = strip_tags(api_substr(api_html_entity_decode($survey_data['title'],ENT_QUOTES,$charset), 0, 40));
if (api_strlen(strip_tags($survey_data['title'])) > 40)
{
	$urlname .= '...';
}

//$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-1.3.2.min.js" language="javascript"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/customInput.jquery.js" language="javascript"></script>';

// breadcrumbs
$interbreadcrumb[] = array ("url" => "survey_list.php", "name" => get_lang('SurveyList'));
$interbreadcrumb[] = array ('url' => 'survey.php?survey_id='.$survey_id, 'name' => $urlname);
if (!$_GET['action'] OR $_GET['action'] == 'overview')
{
	$tool_name = get_lang('Reporting');
}
else
{
	$interbreadcrumb[] = array ("url" => "reporting.php?survey_id=".$survey_id, "name" => get_lang('Reporting'));
	switch ($_GET['action'])
	{
		case 'questionreport':
			$tool_name = get_lang('DetailedReportByQuestion');
			break;
		case 'userreport':
			$tool_name = get_lang('DetailedReportByUser');
			break;
		case 'comparativereport':
			$tool_name = get_lang('ComparativeReport');
			break;
		case 'completereport':
			$tool_name = get_lang('CompleteReport');
			break;
	}
}

// Displaying the header
//Display::display_header($tool_name,'Survey');
Display::display_tool_header();
 $navigator_info = api_get_navigator();
?>
<script type="text/javascript"> 
	// Run the script on DOM ready:
	$(function(){
            <?php if (!($navigator_info['name'] == 'Internet Explorer' && $navigator_info['version'] == '6')) { ?>
		$('input').customInput();
            <?php } ?>
                
	});
	</script>	
<?php

echo '<div class="actions">';
if($_REQUEST['action'] == 'questionreport')
{
	echo '<a href="reporting.php?'.api_get_cidreq().'&survey_id='.Security::remove_XSS($_GET['survey_id']).'">'.Display::return_icon('pixel.gif', get_lang('BackTo').' '.strtolower(get_lang('Report')), array('class' => 'toolactionplaceholdericon toolactionback')).get_lang('BackTo').' '.strtolower(get_lang('Report')).'</a>';

	// determining the offset of the sql statement (the n-th question of the survey)
		if (!isset($_REQUEST['question']))
		{
			$offset = 0;
		}
		else
		{
			$offset = Database::escape_string($_REQUEST['question']);
		}

	// getting the question information
		$sql = "SELECT * FROM $table_survey_question WHERE survey_id='".Database::escape_string($_REQUEST['survey_id'])."' AND type<>'pagebreak' AND type<>'comment' ORDER BY sort ASC";
		$result = Database::query($sql, __FILE__, __LINE__);		
		$number_of_questions = Database::num_rows($result);		

		echo '<b>'.get_lang('GoToQuestion').': </b>';
		for($i=1; $i<=$number_of_questions; $i++ )
		{
			if ($offset <> $i-1)
			{
				echo '<a href="reporting.php?'.api_get_cidreq().'&action=questionreport&survey_id='.Security::remove_XSS($_GET['survey_id']).'&question='.($i-1).'" class="survey_question_nos">&nbsp;&nbsp;'.$i.'</a>';
			}
			else
			{
				echo '<a href="#" class="survey_question_nos_active">&nbsp;&nbsp;'.$i.'</a>';
			}
			if ($i < $number_of_questions)
			{
				echo '';
			}
		}
		//echo '</div>';
}
elseif(isset($_REQUEST['action']))
{
echo '<a href="reporting.php?'.api_get_cidreq().'&survey_id='.Security::remove_XSS($_GET['survey_id']).'">'.Display::return_icon('pixel.gif', get_lang('BackTo').' '.strtolower(get_lang('Report')), array('class' => 'toolactionplaceholdericon toolactionback')).get_lang('BackTo').' '.strtolower(get_lang('Report')).'</a>';
}
else
{	
echo '<a href="survey.php?'.  api_get_cidreq().'&survey_id='.Security::remove_XSS($_GET['survey_id']).'">'.Display::return_icon('pixel.gif', get_lang('BackTo').' '.strtolower(get_lang('Report')), array('class' => 'toolactionplaceholdericon toolactionback')).get_lang('BackTo').' '.strtolower(get_lang('Survey')).'</a>';
}
if($_REQUEST['action'] == 'userreport' && isset($_REQUEST['user']))
{
	echo '<a href="reporting.php?'.api_get_cidreq().'&action=deleteuserreport&survey_id='.Security::remove_XSS($_GET['survey_id']).'&user='.Security::remove_XSS($_GET['user']).'" >'.Display::return_icon('pixel.gif', get_lang('Delete'),array('class'=>'toolactionplaceholdericon tooldeletegroup')).' '.get_lang('DeleteSurveyByUser').'</a>';

	// export the user report
	echo '<a href="javascript: void(0);" onclick="document.form1a.submit();">'.Display::return_icon('pixel.gif', get_lang('ExportAsCSV'),array('class'=>'toolactionplaceholdericon toolactionexportcourse')).' '.get_lang('ExportAsCSV').'</a> ';
	echo '<a href="javascript: void(0);" onclick="document.form1b.submit();">'.Display::return_icon('pixel.gif', get_lang('ExportAsXLS'),array('class'=>'toolactionplaceholdericon toolactioniconexcel')).' '.get_lang('ExportAsXLS').'</a> ';	
}

if($_REQUEST['action'] == 'completereport')
{
    echo '<a class="survey_export_link" href="javascript: void(0);" onclick="document.form1b.submit();"><img align="absbottom" '.api_get_path(WEB_IMG_PATH).''.Display::return_icon('pixel.gif',get_lang('ExportAsXLS'),array('class'=>'toolactionplaceholdericon toolactionexportcourse')).''.get_lang('ExportAsXLS').'</a>';
}
echo '</div>';

// start the content div
echo '<div id="content">';

// Action handling
SurveyUtil::handle_reporting_actions();

// content
if (!$_GET['action'] OR $_GET['action'] == 'overview') {
	$myweb_survey_id = $survey_id;
	echo '<div class="section"><div class="sectiontitle"><a href="reporting.php?'.api_get_cidreq().'&action=questionreport&survey_id='.$myweb_survey_id.'">'.Display::return_icon('pixel.gif',get_lang('DetailedReportByQuestion'),array("class" => "surveyreportplaceholdericon toolactiondokeos_question")).' '.get_lang('DetailedReportByQuestion').'</a></div><div class="sectioncontent">'.get_lang('DetailedReportByQuestionDetail').'</div></div>';
	echo '<div class="section"><div class="sectiontitle"><a href="reporting.php?'.api_get_cidreq().'&action=userreport&survey_id='.$myweb_survey_id.'">'.Display::return_icon('pixel.gif',get_lang('DetailedReportByUser'),array("class" => "surveyreportplaceholdericon toolactionsurvey_reporting_user")).' '.get_lang('DetailedReportByUser').'</a></div><div class="sectioncontent">'.get_lang('DetailedReportByUserDetail').'.</div></div>';
	echo '<div class="section"><div class="sectiontitle"><a href="reporting.php?'.api_get_cidreq().'&action=comparativereport&survey_id='.$myweb_survey_id.'">'.Display::return_icon('pixel.gif',get_lang('ComparativeReport'),array("class" => "surveyreportplaceholdericon toolactionsurvey_reporting_comparative")).' '.get_lang('ComparativeReport').'</a></div><div class="sectioncontent">'.get_lang('ComparativeReportDetail').'.</div></div>';
	echo '<div class="section"><div class="sectiontitle"><a href="reporting.php?'.api_get_cidreq().'&action=completereport&survey_id='.$myweb_survey_id.'">'.Display::return_icon('pixel.gif',get_lang('CompleteReport'),array("class" => "surveyreportplaceholdericon toolactionexcel_32","style"=>"margin-left:-4px;padding-right:8px")).' '.get_lang('CompleteReport').'</a></div><div class="sectioncontent">'.get_lang('CompleteReportDetail').'</div></div>';	
}

// close the content div
echo '</div>';

// secondary actions bar
//echo '<div class="actions">';
//echo '</div>';

// Footer
//Display :: display_footer();
Display::display_tool_footer();

