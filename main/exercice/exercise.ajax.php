<?php

require_once('../inc/global.inc.php');


switch ($_GET['action']) {

     case 'changeQuizOrder' :
      if (api_is_allowed_to_edit ()) {
       $output = changeQuizOrder($_GET['quizId'], $_GET['newOrder']);
      }
      break;
     case 'changeQuestionOrder' :
      if (api_is_allowed_to_edit ()) {
       $output = changeQuestionOrder($_GET['quizId'], $_GET['questionId'], $_GET['newOrder']);
      }
      break;
     case 'updateQuizQuestion' :
      if (api_is_allowed_to_edit ()) {
       $output = changeQuizQuestion($_GET['exerciseId'], $_GET['disporder']);
      }
      break;
    case 'updateQuiz' :
      if (api_is_allowed_to_edit ()) {
       $output = changeQuizPosition($_GET['disporder']);
      }
      break;
    case 'displayCertPicture':
        require_once api_get_path(LIBRARY_PATH).'certificatemanager.lib.php';  
        $tpl_id = $_GET['tpl_id'];
        echo CertificateManager::create()->returnCertificateThumbnailImg($tpl_id);
        break;
}

/**
 * Allow reorder the quiz list using Drag and drop
 * @param int $quizId
 * @param int $newPosition
 * @return boolean true if success and false on error
 */
function changeQuizOrder($quizId, $newPosition) {

 $quizId = intval($quizId);
 $newPosition = intval($newPosition);

 if ($quizId == 0) {
  return false;
 }

 $quiz_table = Database::get_course_table(TABLE_QUIZ_TEST);

 $sql = 'SELECT position FROM ' . $quiz_table . ' WHERE id=' . $quizId;
 $rs = api_sql_query($sql, __FILE__, __LINE__);
 if (($oldPosition = Database::result($rs, 0)) !== false) {
  $oldPosition = intval($oldPosition);

  if ($newPosition > $oldPosition) {
   $sql = 'UPDATE ' . $quiz_table . '
						SET position = position - 1 
						WHERE position > ' . $oldPosition . '
						AND position <= ' . $newPosition . '
						AND active != -1';
   api_sql_query($sql, __FILE__, __LINE__);
  } else {
   $sql = 'UPDATE ' . $quiz_table . '
						SET position = position + 1 
						WHERE position < ' . $oldPosition . '
						AND position >= ' . $newPosition . '
						AND active != -1';
   api_sql_query($sql, __FILE__, __LINE__);
  }
  $sql = 'UPDATE ' . $quiz_table . '
					SET position = ' . $newPosition . '
					WHERE id=' . $quizId;
  api_sql_query($sql, __FILE__, __LINE__);
  return true;
 } else {
  return false;
 }
}

/**
 * Allow reorder the question list using Drag and drop
 * @author Isaac flores <florespaz_isaac@hotmail.com>
 * @param int $quizId
 * @param int $questionId
 * @param int $newPosition
 * @return boolean true if success and false on error
 */
function changeQuestionOrder($quizId, $questionId, $newPosition) {

 $quizId = intval($quizId);
 $newPosition = intval($newPosition);
 $questionId = intval($questionId);

 if ($quizId == 0 || $questionId == 0) {
  return false;
 }

 $quiz_rel_question_table = Database::get_course_table(TABLE_QUIZ_TEST_QUESTION);
 $sql = 'SELECT question_order FROM ' . $quiz_rel_question_table . ' WHERE question_id=' . $questionId.' AND exercice_id=' . $quizId;
 $rs = Database::query($sql, __FILE__, __LINE__);

 if (($oldPosition = Database::result($rs, 0)) !== false) {
  $oldPosition = intval($oldPosition);

  if ($newPosition > $oldPosition) {
   $sql = 'UPDATE ' . $quiz_rel_question_table . '
						SET question_order = question_order - 1
						WHERE question_order > ' . $oldPosition . '
						AND question_order <= ' . $newPosition . ' AND exercice_id=' . $quizId;
   Database::query($sql, __FILE__, __LINE__);
  } else {
   $sql = 'UPDATE ' . $quiz_rel_question_table . '
						SET question_order = question_order + 1
						WHERE question_order < ' . $oldPosition . '
						AND question_order >= ' . $newPosition . ' AND exercice_id=' . $quizId;
   Database::query($sql, __FILE__, __LINE__);
  }
  $sql = 'UPDATE ' . $quiz_rel_question_table . '
					SET question_order = ' . $newPosition . '
					WHERE question_id=' . $questionId. ' AND exercice_id=' . $quizId;
  Database::query($sql, __FILE__, __LINE__);

  // Remove the session(objExercise) that hold the quiz object, this allow to update the question list($objExercise->selectQuestionList())
  unset($_SESSION['objExercise']);

  return true;
 } else {
  return false;
 }
}
/**
 * Allow reorder the question list using Drag and drop
 * @author Breetha Mohan <breetha.mohan@dokeos.com>
 * @param int $exerciseId
 * @param array $disporder 
 * @return boolean true if success
 */
function changeQuizQuestion($exerciseId, $disporder) {

 $quiz_rel_question_table = Database::get_course_table(TABLE_QUIZ_TEST_QUESTION);
 $disparr = explode(",", $disporder);
 echo $len = sizeof($disparr);
 $listingCounter = 1;
 for ($i = 0; $i < sizeof($disparr); $i++) {
 $sql = "UPDATE $quiz_rel_question_table SET question_order=" . $listingCounter . " WHERE question_id = " . $disparr[$i] . " AND exercice_id = ".$_REQUEST['exerciseId'];
  $res = Database::query($sql, __FILE__, __LINE__);
  $listingCounter = $listingCounter + 1;
 }

  // Remove the session(objExercise) that hold the quiz object, this allow to update the question list($objExercise->selectQuestionList())
  unset($_SESSION['objExercise']);

  return true;

}

/**
 * Allow reorder the question list using Drag and drop
 * @author Breetha Mohan <breetha.mohan@dokeos.com>
 * @param array $disporder 
 * @return boolean true if success
 */
function changeQuizPosition($disporder) {

 $quiz_table = Database::get_course_table(TABLE_QUIZ_TEST);
 $disparr = explode(",", $disporder);
 echo $len = sizeof($disparr);
 $listingCounter = 1;
 for ($i = 0; $i < sizeof($disparr); $i++) {
 $sql = "UPDATE $quiz_table SET position=" . $listingCounter . " WHERE id = " . $disparr[$i];
  $res = Database::query($sql, __FILE__, __LINE__);
  $listingCounter = $listingCounter + 1;
 }  

  return true;

}