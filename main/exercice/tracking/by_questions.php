<?php

require_once('init.php');
$interbreadcrumb[] = array(
	'url'=>api_get_path(WEB_CODE_PATH).'exercice/tracking/main.php?exerciseId='.$exerciseId, 
	'name'=>get_lang('ExercisesTracking'));

require_once(api_get_path(SYS_CODE_PATH).'exercice/exercise.class.php');
require_once(api_get_path(SYS_CODE_PATH).'exercice/question.class.php');
require_once(api_get_path(LIBRARY_PATH).'graphics.class.php');

// infos about exercise
$o_exercise = new Exercise();
$o_exercise->read($exerciseId);
$nbAttemptsInExercise = $o_exercise->tracking_select_nb_attempts();
$highestScore = $o_exercise->getTrackingScore('MAX');
$avgScore = $o_exercise->getTrackingScore('AVG');
$lowestScore = $o_exercise->getTrackingScore('MIN');

// info about questions
$a_questions = array();
foreach($o_exercise->selectQuestionList() as $questionId){
  $a_questions[] = Question::read($questionId);
}

$template = 'by_questions.page';
$nameTools = get_lang('TrackingByQuestion');
require_once 'layout.page';
