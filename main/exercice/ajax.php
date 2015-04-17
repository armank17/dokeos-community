<?php
ini_set('memory_limit', '400M');

$language_file[]='exercice';
// including the global file that gets the general configuration, the databases, the languages, ...
require ('../inc/global.inc.php');

// including additional libraries
require_once (api_get_path(LIBRARY_PATH).'course.lib.php');
require_once (api_get_path(LIBRARY_PATH).'add_course.lib.inc.php');
require_once api_get_path(LIBRARY_PATH).'fileUpload.lib.php';
include_once 'exercise.class.php';
include_once 'question.class.php';
include_once 'answer.class.php';

switch ($_POST['action'])
{
	case 'get_quizcategory':
		$counter = $_POST['counter'];
		$scenario = $_POST['scenario'];
		$random_qn = $_POST['randomqn'];
		$randomqn_exists = $_POST['randomqn_exists'];
		get_quizcategory($counter,$scenario,$random_qn,$randomqn_exists);
		break;
	case 'change_numberof_question':
		$random_qn = $_POST['randomqn'];
		$counter = $_POST['cat_counter'];
		$scenario = $_POST['scenario'];
		$numselqn = $_POST['numselqn'];
		change_numberof_question($random_qn, $counter, $scenario, $numselqn);
		break;
	case 'reuse':
		$fromExercise = $_POST['fromExercise'];
		$qnlist = $_POST['qnlist'];
		reuse_quizquestion($fromExercise,$qnlist);
}

function change_numberof_question($random_qn, $counter, $scenario, $numselqn){

	echo '<select name="numberofquestion_'.$counter.'_'.$scenario.'" id="numberofquestion_'.$counter.'_'.$scenario.'" size="1">';
	echo '<option value="0">Select</option>';
	for($i=1;$i<=$random_qn;$i++){
		if($i == $numselqn)	{
			echo '<option value="'.$i.'" selected>'.$i.'</option>';
		}
		else {
			echo '<option value="'.$i.'">'.$i.'</option>';
		}
	}
	echo '</select>';
}

function get_quizcategory($counter,$scenario,$random_qn,$randomqn_exists){
	$TBL_QUIZ_CATEGORY = Database::get_course_table(TABLE_QUIZ_CATEGORY);
	$query = "SELECT * FROM $TBL_QUIZ_CATEGORY ORDER BY display_order";
	$result = api_sql_query($query, __FILE__, __LINE__);
//	echo 'counter=='.$counter;
	echo '<script type="text/javascript">
	$(document).ready(function (){
		$("#numberofquestion_'.$counter.'_'.$scenario.'").live("change", function(){
			var randomqn = $("#randomQuestions_'.$scenario.'").val();
			if(randomqn == 0){
			var numberofquestion = $("#numberofquestion_'.$scenario.'").val();
			randomqn = numberofquestion;
			}
			var new_randomqn = randomqn;
			for(var i=0;i<='.$counter.';i++){
				var numqn = $("#numberofquestion_"+i+"_'.$scenario.'").val();
				new_randomqn = (new_randomqn*1) - (numqn*1);
			}

			var counter = $("input[name=\'counter_'.$scenario.'\']").attr("value");
			for(var i='.($counter+1).';i<counter;i++){
				  var numselqn = $("#numberofquestion_"+i+"_'.$scenario.'").val();
				  callsubpostfunction(new_randomqn,i,'.$scenario.',numselqn);
			}
		});
	});
	</script>';
	if($randomqn_exists == 'N' && $random_qn == 0){
		$random_qn = 10;
	}
	echo '<br/><table width="100%" border="0">';
	if($counter == 0){
		echo '<tr id="TextBoxDiv_'.$scenario.'"><td width="30%">'.get_lang('Category').'</td><td width="25%">'.get_lang('Level').'</td><td width="45%">'.get_lang('Numberofquestion').'</td></tr>';
	}
	echo '<tr id="TextBoxDiv_'.$counter.'_'.$scenario.'"><td width="30%">';
	echo '<select name="quizcategory_'.$counter.'_'.$scenario.'" size="1">';
	echo '<option value="0">Select</option>';
	while ($row = Database::fetch_array($result)) {
		$quiz_category = $row['category_title'];
		echo '<option value="'.$row['id'].'">'.$quiz_category.'</option>';
	}
	echo '</select>';
	echo '</td><td width="25%">';

	echo '<select name="quizlevel_'.$counter.'_'.$scenario.'" size="1">';
	echo '<option value="0">'.get_lang('Select').'</option>';
	echo '<option value="1">Prerequistie</option>';
	echo '<option value="2">Beginner</option>';
	echo '<option value="3">Intermediate</option>';
	echo '<option value="4">Advanced</option>';
	echo '</select>';
	echo '</td><td id="test_'.$counter.'_'.$scenario.'" width="45%">';

	echo '<select name="numberofquestion_'.$counter.'_'.$scenario.'" id="numberofquestion_'.$counter.'_'.$scenario.'" size="1">';
	echo '<option value="0">'.get_lang('Select').'</option>';
	for($i=1;$i<=$random_qn;$i++){
		echo '<option value="'.$i.'">'.$i.'</option>';
	}
	echo '</select>';
	echo '</td></tr>';
	echo '</table>';
//	echo '<div id="TextBoxDiv_'.$counter.'">Remove content</div>';
}

function reuse_quizquestion($fromExercise,$qnlist){
	foreach($qnlist as $recup){
		// if the question exists
		$objQuestionTmp = Question :: read($recup);
		if ($objQuestionTmp) {
			// adds the exercise ID represented by $fromExercise into the list of exercises for the current question
			$objQuestionTmp->addToList($fromExercise);
		}

		// destruction of the Question object
		unset($objQuestionTmp);

		if (!$objExcercise instanceOf Exercise) {
			$objExercise = new Exercise();
			$objExercise->read($fromExercise);
		}
		// adds the question ID represented by $recup into the list of questions for the current exercise
		$objExercise->addToList($recup);
	}

}
?>