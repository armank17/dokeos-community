<?php

/* For licensing terms, see /dokeos_license.txt */

/**
  ==============================================================================
 * 	Upload quiz: This script shows the upload quiz feature
 * Added by Isaac flores on Nov 4 of 2010
 * 	@package dokeos.exercise
  ==============================================================================
 */
// Language files that should be included
$language_file[] = 'learnpath';
$language_file[] = 'exercice';
// setting the help
$help_content = 'exercise_upload';

// including the global Dokeos file
require_once '../inc/global.inc.php';
require_once api_get_path(LIBRARY_PATH) . 'fileUpload.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'formvalidator/FormValidator.class.php';
require_once api_get_path(LIBRARY_PATH) . 'excelreader/reader.php';
require_once 'exercise.class.php';
require_once 'question.class.php';
require_once 'unique_answer.class.php';
require_once '../newscorm/learnpath.class.php';
require_once '../newscorm/learnpathItem.class.php';

// Security check
$is_allowed_to_edit = api_is_allowed_to_edit(null, true);
if (!$is_allowed_to_edit) {
 api_not_allowed(true);
}

// setting the tabs
$this_section = SECTION_COURSES;
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-1.4.2.min.js" language="javascript"></script>';
$htmlHeadXtra[] = "<script type='text/javascript'>
  $(document).ready( function(){
  $(\"div.formw\").attr(\"style\",\"width: 73%;\");
  $(\"#img_plus_and_minus\").hide();
});
</script>";

// Action handling
lp_upload_quiz_action_handling();

// Display the header
Display::display_tool_header();
// display the actions
echo '<div class="actions">';
echo lp_upload_quiz_actions();
echo '</div>';

// start the content div
echo '<div id="content_with_secondary_actions" class="gradient">';
// the main content
lp_upload_quiz_main();

// close the content div
echo '</div>';

// display the actions
echo '<div class="actions">';
echo lp_upload_quiz_secondary_actions();
echo '</div>';

function lp_upload_quiz_actions() {
 $lp_id = Security::remove_XSS($_GET['lp_id']);
 $return = "";
 $return.= '<a href="exercice.php?cidReq=' . Security::remove_XSS($_GET['cidReq']) . '">' . Display::return_icon('pixel.gif', get_lang('List'), array('class' => 'toolactionplaceholdericon toolactionback')) . get_lang('List').'</a>';
 $return.= '<a href="exercise_admin.php?cidReq=' . Security::remove_XSS($_GET['cidReq']) . '">' . Display::return_icon('pixel.gif', get_lang('NewEx'), array('class' => 'toolactionplaceholdericon toolactionnewquiz'))  . get_lang('NewEx') . '</a>';
 return $return;
}

function lp_upload_quiz_secondary_actions() {
 $lp_id = Security::remove_XSS($_GET['lp_id']);
 $return.= '';
 $return.='<a href="exercice.php?show=result&' . api_get_cidreq() . '">' . Display::return_icon('pixel.gif', get_lang('Tracking'), array('class' => 'actionplaceholdericon actiontracking')) . get_lang('Tracking') . '</a>';
 return $return;
}

function lp_upload_quiz_main() {
 // Database table definition
 $table_document = Database::get_course_table(TABLE_DOCUMENT, $_course['dbName']);
 $propTable = Database::get_course_table(TABLE_ITEM_PROPERTY);
 // variable initialisation
 $lp_id = Security::remove_XSS($_GET['lp_id']);

 $form = new FormValidator('upload', 'POST', api_get_self() . '?' . api_get_cidreq() . '&lp_id=' . $lp_id, '', 'enctype="multipart/form-data" style="float:right;" class="orange"');
 $form->addElement('html', '<div><div><h3 class="orange">' . get_lang('UploadExcelQuiz') . '</h3></div><div><input type="file" name="user_upload_quiz" id="user_upload_quiz_id" size="20" /></div></div>');
 //button send document
 $form->addElement('style_submit_button', 'submit_upload_quiz', get_lang('Validate'), 'class="upload" style="float:right;"');
 $form->setDefaults($defaults);

  // Display the upload field
  echo '<table style="text-align: left; width: 100%;" border="0" cellpadding="2"cellspacing="2"><tbody><tr>';
  echo '<td style="vertical-align: top; width: 25%;">';
  echo '<a href="../exercice/template/QuizTemplateDokeos.xls"><h3 class="orange">'.get_lang('DownloadExcelTemplate').'</h3>';
  echo Display::display_icon('excel_64.png', get_lang('DownloadExcelTemplate'));
  echo '</a>';
  echo '</td>';
  echo '<td style="vertical-align: top; width: 50%;">';
  echo Display::display_icon('studing.png', get_lang('UploadExcelQuiz'), array('style' => 'width:360px;'));
  echo '</td>';
  echo '<td style="vertical-align: top; width: 25%;">';
  echo '&nbsp;';
  echo '</td>';
  echo '</tr>';
  echo '<tr><td align="center" style="text-align: right; width: 100%;" colspan="3">';
  $form->display();
  echo '</td></tr></tbody></table>';
}

/**
 * In this function you can perform all the actions (mostly based on $_GET['action'] parameter or $_POST values
 */
function lp_upload_quiz_action_handling() {
 global $charset, $_course, $_user;
 if (isset($_POST['submit_upload_quiz'])) {
  // Get the extension of the document.
  $path_info = pathinfo($_FILES['user_upload_quiz']['name']);
  $excel_type = $path_info['extension'];
  // Check if the document is an Excel document
  if ($excel_type != 'xls') {
   return;
  }

  // Read the Excel document
  $data = new Spreadsheet_Excel_Reader();

  // Set output Encoding.
  $data->setOutputEncoding($charset);

  // Reading the xls document.
  $data->read($_FILES['user_upload_quiz']['tmp_name']);

  error_reporting(E_ALL ^ E_NOTICE);

  // Variables
  $quiz_index = 0;
  $question_title_index = array();
  $question_name_index_init = array();
  $question_name_index_end = array();
  $score_index = array();
  $feedback_true_index = array();
  $feedback_false_index = array();
  $number_questions = 0;
  // Reading all first column for to create breakpoints
  for ($i = 1; $i < $data->sheets[0]['numRows']; $i++) {
   if ($data->sheets[0]['cells'][$i][1] == 'Quiz' && $i == 1) {
    $quiz_index = $i; // Quiz title position
   } elseif ($data->sheets[0]['cells'][$i][1] == 'Question') {
    $question_title_index[] = $i; // Question title position
    $question_name_index_init[] = $i + 1; // Questions name position(Begin)
    $number_questions++;
   } elseif ($data->sheets[0]['cells'][$i][1] == 'Score') {
    $question_name_index_end[] = $i - 1; // Question name position(Finish)
    $score_index[] = $i; // Question score position
   } elseif ($data->sheets[0]['cells'][$i][1] == 'FeedbackTrue') {
    $feedback_true_index[] = $i; // FeedbackTrue position
   } elseif ($data->sheets[0]['cells'][$i][1] == 'FeedbackFalse') {
    $feedback_false_index[] = $i; // FeedbackFalse position
   }
  }

  // Variables
  $quiz = array();
  $question = array();
  $answer = array();
  $new_answer = array();
  $score_list = array();
  $feedback_true_list = array();
  $feedback_false_list = array();
  // Number of questions
  $k = 0;
  $z = 0;
  $q = 0;
  $l = 0;
  for ($i = 1; $i <= $data->sheets[0]['numRows']; $i++) {
   if (is_array($data->sheets[0]['cells'][$i])) {
    $column_data = $data->sheets[0]['cells'][$i];
    // Fill all column with data
    for ($x = 1; $x <= $data->sheets[0]['numCols']; $x++) {
     if (empty($column_data[$x])) {
      $data->sheets[0]['cells'][$i][$x] = '';
     }
    }
    // Array filled with data
    $column_data = $data->sheets[0]['cells'][$i];
   } else {
    $column_data = '';
   }
   // Fill quiz data
   if ($quiz_index == $i) { // The title always in the first position
    $quiz = $column_data;
   } elseif (in_array($i, $question_title_index)) {
    $question[$k] = $column_data;
    $k++;
   } elseif (in_array($i, $score_index)) {
    $score_list[$z] = $column_data;
    $z++;
   } elseif (in_array($i, $feedback_true_index)) {
    $feedback_true_list[$q] = $column_data;
    $q++;
   } elseif (in_array($i, $feedback_false_index)) {
    $feedback_false_list[$l] = $column_data;
    $l++;
   }
  }

  // Fill Answers
  for ($i = 0; $i < count($question_name_index_init); $i++) {
   for ($j = $question_name_index_init[$i]; $j <= $question_name_index_end[$i]; $j++) {
    if (is_array($data->sheets[0]['cells'][$j])) {
     $column_data = $data->sheets[0]['cells'][$j];
     // Fill all column with data
     for ($x = 1; $x <= $data->sheets[0]['numCols']; $x++) {
      if (empty($column_data[$x])) {
       $data->sheets[0]['cells'][$j][$x] = '';
      }
     }
     $column_data = $data->sheets[0]['cells'][$j];
     // Array filled of data
     if (is_array($data->sheets[0]['cells'][$j]) && count($data->sheets[0]['cells'][$j]) > 0) {
      $new_answer[$i][$j] = $data->sheets[0]['cells'][$j];
     }
    }
   }
  }

  /* =======================================================
                            CREATE QUIZ
    ======================================================== */
  $quiz_title = $quiz[2]; // Quiz title
  if ($quiz_title != '') {
   // Variables
   $type = 2;
   $random = 0;
   $active = 1;
   $results = 0;
   $max_attempt = 0;
   $feedback = 3;
   $expired_time = 0;
   // Quiz object
   $quiz_object = new Exercise();
   $quiz_id = $quiz_object->create_quiz_from_an_attached_file($quiz_title, $expired_time, $type, $random, $active, $results, $max_attempt, $feedback);

   // insert into the item_property table
   api_item_property_update($_course, TOOL_QUIZ, $quiz_id, 'QuizAdded', $_user['user_id']);

   // Add data into quiz scenario table
   $quiz_info = array();
   $quiz_info['quiz_id'] = $quiz_id;
   $quiz_info['start_time'] = '';
   $quiz_info['end_time'] = '';
   $quiz_info['title'] = $quiz_title;
   $quiz_info['description'] = '';
   $quiz_info['sound'] = '';
   $quiz_info['type'] = $type;
   $quiz_info['random'] = $random;
   $quiz_info['active'] = $active;
   $quiz_info['results_disabled'] = $results;
   $quiz_info['attempts'] = $max_attempt;
   $quiz_info['feedback'] = $feedbacktype;
   $quiz_info['expired_time'] = 0;

   // Add the scenarios to quiz
   $quiz_data = (object) $quiz_info;
   $quiz_object->save_scenario($quiz_data);

   /* =======================================================
                            CREATE QUESTIONS
     ======================================================== */
   for ($i = 0; $i < $number_questions; $i++) {
    // Create questions
    $question_title = $question[$i][2]; // Question name
    if ($question_title != '') {
     $question_id = Question::create_question_from_an_attached_file($quiz_id, $question_title);
    }
    $unique_answer = new UniqueAnswer();

    if (is_array($new_answer[$i])) {
     $id = 1;
     $answers_data = $new_answer[$i];

        foreach ($answers_data as $answer_data) {
         $answer = $answer_data[2];
         $correct = 0;
         $score = 0;
         $comment = '';
         $clean_correct_answer = strip_tags($answer_data[3]);
         if (strtolower($clean_correct_answer) == 'x') {
           $correct = 1;
           $score = $score_list[$i][3];
           $comment = $feedback_true_list[$i][2];
         }else{
           $comment = $feedback_false_list[$i][2];
         }
	  $answer = "<p>".$answer."</p>";
      /* =======================================================
        CREATE ANSWERS
        ======================================================== */
      $unique_answer->create_answers_from_an_attached_file($id, $question_id, $answer, $comment, $score, $correct);
      $id++;
     }
    }
   }

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

   if (isset($_SESSION['oLP']) && isset($_GET['lp_id']) && $_GET['lp_id'] > 0) {
    $previous = $_SESSION['oLP']->select_previous_item_id();
    $parent = 0;
    // Add a Quiz as Lp Item
    $_SESSION['oLP']->add_item($parent, $previous, TOOL_QUIZ, $quiz_id, $quiz_title, '');
    // Redirect to home page for add more content
    header('location: ../newscorm/lp_controller.php?' . api_get_cidreq() . '&action=add_item&type=step&lp_id=' . Security::remove_XSS($_GET['lp_id']));
   }
   else
   {
	 //  header('location: exercice.php?' . api_get_cidreq());
		echo '<script type="text/javascript">window.location.href = "admin.php?'.api_get_cidReq().'&exerciseId='.$quiz_id.'"</script>';
   }
  }
 } // End submit
}