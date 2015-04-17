<?php
/* For licensing terms, see /dokeos_license.txt */

/**
  ==============================================================================
 * 	Exercise print: This script shows the list of exercise questions for administrators and students.
 * 	@package dokeos.exercise
  ==============================================================================
 */
$language_file = array('exercice');

// including the global library
require_once '../inc/global.inc.php';

require_once 'exercise.class.php';
require_once 'question.class.php';
require_once 'answer.class.php';
require_once 'exercise_exam.lib.php';
require_once '../inc/lib/course.lib.php';

$debug = 1;

if(!api_is_allowed_to_edit()) {
  api_not_allowed(true);
}

global $charset;
/*
 * Extra html headers
 */
$htmlHeadXtra[] = '<link rel="stylesheet" type="text/css" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/print_exam.css" media="all" />';
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-1.4.2.min.js" language="javascript"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/customInput.jquery.js" language="javascript"></script>';
$htmlHeadXtra[] = '<script type="text/javascript">
	// Run the script on DOM ready:
	$(function(){
                try {
		$("input").customInput();
                } catch(e){}
	});
	</script>';

/*
 * Setting tables
 */
$TBL_EXERCICE          = Database::get_course_table(TABLE_QUIZ_TEST);
$TBL_QUESTIONS         = Database::get_course_table(TABLE_QUIZ_QUESTION);
$TBL_EXERCICE_QUESTION = Database::get_course_table(TABLE_QUIZ_TEST_QUESTION);
$exerciseId = $_GET['exerciseId'];

$sql = "SELECT q.title FROM $TBL_EXERCICE q WHERE q.id=". intval($exerciseId);
$result = Database::query($sql,__FILE__,__LINE__);
$questionInf = Database::fetch_row($result);

$sql = "SELECT rq.* FROM $TBL_QUESTIONS q
					INNER JOIN $TBL_EXERCICE_QUESTION rq ON (q.id=rq.question_id)
					WHERE rq.exercice_id=".  intval($exerciseId)."
					ORDER BY rq.question_order ASC";
$rs_questions = Database::query($sql,__FILE__,__LINE__);
$questionList = array();
while($qst = Database::fetch_array($rs_questions))
{
  $questionList[$qst['question_order']] = $qst['question_id'];
}

/*
* HTML HEADER
*/
Display::display_reduced_header();

$user_id=api_get_user_id();
$course_code = api_get_course_id();
$status_info=CourseManager::get_user_in_course_status($user_id,$course_code);
			if (STUDENT==$status_info) {
				$user_info=api_get_user_info($user_id);
				$user_name =  $user_info['firstName'].' '.$user_info['lastName'];
			} elseif(COURSEMANAGER==$status_info && !isset($_GET['user'])) {
				$user_info=api_get_user_info($user_id);
				$user_name =  $user_info['firstName'].' '.$user_info['lastName'];
			}
$course_info = api_get_course_info($course_code);

echo '<div id="main_container">
        <div class="sectiontitle quiztitle">
			<div class="print_button"><a href="print.html" onClick="window.print();return false" style="padding: 10px; border-radius: 5px 5px 5px 5px;  background: -moz-linear-gradient(center top , #FFFFFF, #BBBBBB) repeat scroll 0 0 transparent;background: -webkit-gradient(linear,left top, left bottom, from(#fff), to(#ccc));border: 1px solid #1084A7;">' . Display :: return_icon('pixel.gif', get_lang('Print'),array('class' => 'toolactionplaceholdericon toolactionprint32', 'style' => 'margin: 5px 5px 3px 0px;')) . get_lang('Print') . '</a>
			</div>
        <p><b>Course:</b>'.$course_info['name'].'</p>
        <p><b>Test:</b>'.$questionInf[0].'</p>        
        <input type="hidden" id="page_height" value="0"/>
      </div>';

echo '<script type="text/javascript">
        $("#page_height").val(pareInt($("#main_container").height()));
      </script>';
$i = 1;
foreach ($questionList as $questionId) {

  echo '<table id="exe_'.$questionId.'" border="0" cellpadding="5" cellspacing="5" width="100%"><tr><td>';
  
  echo '<div class="quiz_content_actions">';
  // shows the question and its answers
  showPrintQuestion($questionId, $i);
  echo '</div>';
  echo '</td></tr></table>';
  echo '<hr size="1" style="border-style:dashed;color:#DEDEDE" />';
  $i++;

  echo '<script type="text/javascript">
          var hg = $("#exe_'.$questionId.'").height();
          var ph = parseInt($("#page_height").val()) + parseInt(hg);
          if (ph<=790)
          {
            $("#page_height").val(ph);
          }else
          {
            $("#exe_'.$questionId.'").addClass("break_page").css("page-break-before","always");
            $("#page_height").val(hg);
          }
        </script>';
}
echo '</div>';
?>