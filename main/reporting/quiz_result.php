<?php
$language_file = array('exercice', 'tracking', 'admin');
require ('../inc/global.inc.php');
require 'functions.php';
require_once api_get_path(LIBRARY_PATH).'tracking.lib.php';
include_once api_get_path(LIBRARY_PATH).'course.lib.php';
require_once '../exercice/exercise.class.php';
require_once '../exercice/question.class.php'; //also defines answer type constants
require_once '../exercice/answer.class.php';
require_once api_get_path(LIBRARY_PATH) . 'formvalidator/FormValidator.class.php';
require_once api_get_path(LIBRARY_PATH) . 'geometry.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'certificatemanager.lib.php';
$htmlHeadXtra[] = '<script type="text/javascript" language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'jwplayer/jwplayer.js"></script>';

if (empty($origin)) {
    $origin = $_REQUEST['origin'];
}

$quiz_id = $_GET['quiz_id'];
$code = $_GET['course_code'];
$exe_id = $_GET['exe_id'];
$user_id = $_GET['user_id'];
$session_id = $_GET['sessionId'];
echo '</br>';

$course_info = api_get_course_info($code);
$_course = $course_info;
// Database table definitions
$TBL_EXERCICE_QUESTION = Database::get_course_table(TABLE_QUIZ_TEST_QUESTION, $course_info['dbName']);
$TBL_EXERCICES = Database::get_course_table(TABLE_QUIZ_TEST, $course_info['dbName']);
$TBL_QUESTIONS = Database::get_course_table(TABLE_QUIZ_QUESTION, $course_info['dbName']);
$TBL_REPONSES = Database::get_course_table(TABLE_QUIZ_ANSWER, $course_info['dbName']);
$main_user_table = Database :: get_main_table(TABLE_MAIN_USER);
$main_course_user_table = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
$TBL_TRACK_EXERCICES = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
$TBL_TRACK_ATTEMPT = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);

$user_info = api_get_user_info($user_id);

$sql = "SELECT title FROM $TBL_EXERCICES WHERE id = ".$quiz_id;
$res = Database::query($sql, __FILE__, __LINE__);
$quiz_title = Database::result($res, 0, 0);

$from = $_GET['from'];

if(isset($_GET['page']) && $_GET['page'] == 'users'){
	$from = 'users';
}

echo '<div id="loaderDiv" style="display:none;"><img src="../img/ajaxloader.gif" /></div>';
echo '<div id="dataDiv">';
echo '<b>'.checkUTF8($user_info['lastname'])." ".checkUTF8($user_info['firstname'])." , ".get_lang("Quiz")." : ".checkUTF8($quiz_title)." , ".get_lang("Course")." : ".checkUTF8($course_info['name']).'</b>';
if($from == 'individual') {
echo '<span class="pull-right"><a id="quizindividual_back" href="individual_reporting.php?quiz_id='.$quiz_id.'&course_code='.$code.'&user_id='.$user_id.'&sessionId='.$session_id.'">'.api_convert_encoding(get_lang("BackToList"),"UTF-8",api_get_system_encoding()).'</a></span>';
}
else if($from == 'module') {
echo '<span class="pull-right"><a id="moduleresult_back" href="module_result.php?action=stats&extend_attempt=1&course='.$code.'&student_id='.$user_id.'&user_id='.$user_id.'&course_code='.$code.'&lp_id='.$_GET['myid'].'&my_lp_id='.$_GET['my_lp_id'].'&sessionId='.$session_id.'&page=modules">'.api_convert_encoding(get_lang("Back"),"UTF-8",api_get_system_encoding()).'</a></span>';
}
else if($from == 'users') {
echo '<span class="pull-right"><a id="user_moduleresult_back" href="module_result.php?action=stats&extend_attempt=1&course='.$code.'&student_id='.$user_id.'&user_id='.$user_id.'&course_code='.$code.'&lp_id='.$_GET['myid'].'&my_lp_id='.$_GET['my_lp_id'].'&sessionId='.$session_id.'&page=modules">'.api_convert_encoding(get_lang("Back"),"UTF-8",api_get_system_encoding()).'</a></span>';
}
else if($from == 'usersquiz') {
echo '<span class="pull-right"><a href="learners_reporting.php">'.api_convert_encoding(get_lang("Back"),"UTF-8",api_get_system_encoding()).'</a></span>';
}
else {
echo '<span class="pull-right"><a id="quizresult_back" href="list_learners.php?quiz_id='.$quiz_id.'&course_code='.$code.'&sessionId='.$session_id.'">'.api_convert_encoding(get_lang("BackToList"),"UTF-8",api_get_system_encoding()).'</a></span>';
}
echo '</br>';

if (empty($learnpath_id)) {
    $learnpath_id = $_REQUEST['learnpath_id'];
}
if (empty($learnpath_item_id)) {
    $learnpath_item_id = $_REQUEST['learnpath_item_id'];
}
if (empty($formSent)) {
    $formSent = $_REQUEST['formSent'];
}
if (empty($exerciseResult)) {
    $exerciseResult = $_SESSION['exerciseResult'];
}
if (empty($questionId)) {
    $questionId = $_REQUEST['questionId'];
}
if (empty($choice)) {
    $choice = $_REQUEST['choice'];
}
if (empty($questionNum)) {
    $questionNum = $_REQUEST['questionNum'];
}
if (empty($nbrQuestions)) {
    $nbrQuestions = $_REQUEST['nbrQuestions'];
}
if (empty($questionList)) {
    $questionList = $_SESSION['questionList'];
}
if (empty($objExercise)) {
    $objExercise = $_SESSION['objExercise'];
}
if (empty($exe_id)) {
    $exe_id = $_REQUEST['exe_id'];
}

if (empty($action)) {
    $action = $_GET['action'];
}

$current_user_id = $user_id;
$current_user_id = "'" . $current_user_id . "'";
$current_attempt = $_SESSION['current_exercice_attempt'][$current_user_id];
$course_code = $code;

// check if user is allowed to get certificate
$obj_certificate = new CertificateManager();
$certif_available = $obj_certificate->isUserAllowedGetCertificate($user_id, 'quiz', $exe_id, $code);

//Unset session for clock time
unset($_SESSION['current_exercice_attempt'][$current_user_id]);
unset($_SESSION['expired_time'][$course_code][intval($_SESSION['id_session'])][$quiz_id][$learnpath_id]);
unset($_SESSION['end_expired_time'][$quiz_id][$learnpath_id]);
?>
<script language="javascript">

    $(document).ready(function(){
        if ($(".close_message_box").length > 0) {
            $(".close_message_box").click(function(e){
                e.preventDefault();
                go_lp_next_item();
                return false;
            });
        }
    });

    function go_lp_next_item() {
        var current_item = window.parent.olms.lms_item_id;
        var next_item = window.parent.olms.lms_next_item;
        window.parent.switch_item(current_item,next_item);
        return true;
    }
    function showfck(sid,marksid)
    {
        document.getElementById(sid).style.display='block';
        document.getElementById(marksid).style.display='block';
        var comment = 'feedback_'+sid;
        document.getElementById(comment).style.display='none';
    }

    function getFCK(vals,marksid)
    {
        var f=document.getElementById('myform');

        var m_id = marksid.split(',');
        for(var i=0;i<m_id.length;i++){
            var oHidn = document.createElement("input");
            oHidn.type = "hidden";
            var selname = oHidn.name = "marks_"+m_id[i];
            var selid = document.forms['marksform_'+m_id[i]].marks.selectedIndex;
            oHidn.value = document.forms['marksform_'+m_id[i]].marks.options[selid].text;
            f.appendChild(oHidn);
        }

        var ids = vals.split(',');
        for(var k=0;k<ids.length;k++){
            var oHidden = document.createElement("input");
            oHidden.type = "hidden";
            oHidden.name = "comments_"+ids[k];
            oEditor = FCKeditorAPI.GetInstance(oHidden.name) ;
            oHidden.value = oEditor.GetXHTML(true);
            f.appendChild(oHidden);
        }
        //f.submit();
    }
</script>
<?php
function display_unique_or_multiple_or_reasoning_answer($answerType, $studentChoice, $answer, $answerComment, $answerCorrect, $id, $questionId, $ans) {
    if ($answerType == UNIQUE_ANSWER) {
        $img = 'radio';
    } else {
        $img = 'checkbox';
    }
    if ($studentChoice) {
        $your_choice = $img . '_on' . '.gif';
    } else {
        $your_choice = $img . '_off' . '.gif';
    }

    if ($answerCorrect) {
        $expected_choice = $img . '_on' . '.gif';
    } else {
        $expected_choice = $img . '_off' . '.gif';
    }

    $s .= '
        <tr>
        <td align="center">
            <img src="../img/' . $your_choice . '"
            border="0" alt="" />
        </td>
        <td align="center">
            <img src="../img/' . $expected_choice . '"
            border="0" alt=" " />
        </td>
        <td style="border-bottom: 1px solid #4171B5;">' . api_parse_tex($answer) . '
        </td>
        </tr>';
    return $s;
}

$is_allowedToEdit = api_is_allowed_to_edit() || $is_courseTutor;
$nameTools = get_lang('CorrectTest');

$sql_questions = "SELECT question_id FROM $TBL_EXERCICE_QUESTION WHERE exercice_id = " . Database::escape_string($quiz_id);
$res_questions = Database::query($sql_questions, __FILE__, __LINE__);
$total_questions = array();
while ($row_questions = Database::fetch_array($res_questions)) {
    $total_questions[] = $row_questions['question_id'];
}

$sql_test_name = 'SELECT title, description, results_disabled, quiz_type FROM ' . $TBL_EXERCICES . ' as exercises, ' . $TBL_TRACK_EXERCICES . ' as track_exercises WHERE exercises.id=track_exercises.exe_exo_id AND track_exercises.exe_id="' . Database::escape_string($exe_id) . '"';
$result = api_sql_query($sql_test_name);
$show_results = true;
$show_score = true;
// Avoiding the "Score 0/0" message  when the exe_id is not set
if (Database::num_rows($result) > 0 && isset($exe_id)) {
    $test = Database::result($result, 0, 0);
    $exerciseTitle = api_parse_tex($test);
    $exerciseDescription = Database::result($result, 0, 1);
    $quiz_type = Database::result($result, 0, 3);

    // if the results_disabled of the Quiz is 1 when block the script
    $result_disabled = Database::result($result, 0, 2);
    if (!(api_is_platform_admin() || api_is_course_admin())) {
        if ($result_disabled == 1) {
            //api_not_allowed();
//			$show_results = false;
            $show_score = false;            
        }
    }
    if ($show_results == true) {
        $user_restriction = $is_allowedToEdit ? '' : "AND user_id=" . intval($user_id) . " ";
        $query = "SELECT attempts.question_id, answer  from " . $TBL_TRACK_ATTEMPT . " as attempts
						INNER JOIN " . $TBL_TRACK_EXERCICES . " as stats_exercices ON stats_exercices.exe_id=attempts.exe_id
						INNER JOIN " . $TBL_QUESTIONS . " as questions ON questions.id=attempts.question_id
                                                INNER JOIN " . $TBL_EXERCICE_QUESTION . " as rel_questions ON rel_questions.question_id = questions.id AND rel_questions.exercice_id = stats_exercices.exe_exo_id
                                                WHERE attempts.exe_id='" . Database::escape_string($exe_id) . "' $user_restriction
                                                GROUP BY attempts.question_id
                                                ORDER BY rel_questions.question_order ASC";
        $result = Database::query($query, __FILE__, __LINE__);
    }
} else {
    Display::display_warning_message(get_lang('CantViewResults'));
    $show_results = false;    
}

if($show_results == false) {
	Display::display_warning_message(get_lang('CantViewResults'));
}

$i = $totalScore = $totalWeighting = $totalScoreMA = 0;

if ($show_results) {
    $questionList = array();
    $exerciseResult = array();
    $k = 0;
    $counter = 0;
    while ($row = Database::fetch_array($result)) {
        $questionList[] = $row['question_id'];
        $exerciseResult[] = $row['answer'];
    }

    if ($quiz_type == 2) {
        $diff_question = array_diff($total_questions, $questionList);
        $diff_question_no = sizeof($diff_question);

        if ($diff_question_no <> 0) {
            foreach ($diff_question as $missed_question) {
                $questionList[] = $missed_question;
                $exerciseResult[] = '';
            }
        }
    }

    // for each question
    foreach ($questionList as $questionId) {
		$counter++;
        $k++;
        $choice = $exerciseResult[$questionId];
        // creates a temporary Question object
        $objQuestionTmp = Question::read($questionId);
        $questionName = $objQuestionTmp->selectTitle();
        $questionDescription = $objQuestionTmp->selectDescription();
        $questionWeighting = $objQuestionTmp->selectWeighting();
        $answerType = $objQuestionTmp->selectType();
        $quesId = $objQuestionTmp->selectId(); //added by priya saini
        $mediaPosition = $objQuestionTmp->selectMediaPosition();

		// destruction of the Question object
        unset($objQuestionTmp);
		echo '</br>';
		echo "<div style='border:1px solid #000;padding:10px;'><b>".get_lang('Question').' '. $counter.' : </b>'.checkUTF8($questionName);
		echo '</br></br>';
		//echo "<div class='span12'>";
		
		//echo '<form class="form-horizontal" action="" method="post">';
		//echo "<div class='container-fluid'>";
		echo "<div class='row-fluid'>";
		if($answerType == MATCHING) {
		echo "<section class='span11 manage-channel'>";
		}
		else {
		echo "<section class='span5 manage-channel'>";
		}
		/*echo "<div class='span2'>";
		echo get_lang("Choice");
		echo "</div>";
		echo "<div class='span4'>";
		echo get_lang("ExpectedChoice");
		echo "</div>";
		echo "<div class='span5'>";
		echo get_lang("Answer");
		echo "</div>";*/
		

		if ($answerType == MULTIPLE_ANSWER) {
			$choice = array();
			$feedback_if_true = $feedback_if_false = '';
			// construction of the Answer object
			$objAnswerTmp = new Answer($questionId);
			$nbrAnswers = $objAnswerTmp->selectNbrAnswers();
			$questionScore = 0;
			$correctChoice = 'N';
			$answerWrong = 'N';
			$totalScoreMA = 0;
			$count_ans = $last_incorrect = 0;
			echo '<table class="responsive large-only table-striped">';
			echo '<thead>'; 
			echo '<tr><th>'.checkUTF8(get_lang("Choice")).'</th>
					  <th>'.checkUTF8(get_lang("ExpectedChoice")).'</th>
					   <th>'.checkUTF8(get_lang("Answer")).'</th>';
			echo '</thead>';
			echo '<tbody>';
			for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
				$answer = $objAnswerTmp->selectAnswer($answerId);
				$answerComment = $objAnswerTmp->selectComment($answerId);
				$correctComment[] = $objAnswerTmp->selectComment($answerId);
				$answerCorrect = $objAnswerTmp->isCorrect($answerId);
				$answerWeighting = $objAnswerTmp->selectWeighting($answerId);
				$queryans = "select * from " . $TBL_TRACK_ATTEMPT . " where exe_id = '" . Database::escape_string($exe_id) . "' and question_id= '" . Database::escape_string($questionId) . "'";
				$resultans = api_sql_query($queryans, __FILE__, __LINE__);
				while ($row = Database::fetch_array($resultans)) {
					$ind = $row['answer'];
					$choice[$ind] = 1;
				}
				$studentChoice = $choice[$answerId];
				if ($studentChoice) {
					$count_ans++;
					$questionScore+=$answerWeighting;
					$totalScoreMA+=$answerWeighting;
					if ($studentChoice == $answerCorrect) {
						$correctChoice = 'Y';
						$feedback_if_true = $objAnswerTmp->selectComment($answerId);
					} else {
						$answerWrong = 'Y';
						$feedback_if_false = $objAnswerTmp->selectComment($answerId);
					}
				}
				if (!$answerCorrect) {
					$last_incorrect = $answerId;
				}				

				/*echo '<tr><td><img src="../img/' . $your_choice . '" border="0" alt="" /></td>';
				echo '<td><img src="../img/' . $expected_choice . '" border="0" alt="" /></td>';
				echo '<td>'.api_parse_tex($answer).'</td>';
				echo '</tr>';*/

				/*echo "<div class='span2'>";
				echo '<img src="../img/' . $your_choice . '" border="0" alt="" />';
				echo "</div>";
				echo "<div class='span4'>";
				echo '<img src="../img/' . $expected_choice . '" border="0" alt="" />';
				echo "</div>";
				echo "<div class='span5'>";
				echo api_parse_tex($answer);
				echo "</div>";*/

				if ($answerId == 1) {
					$s = display_unique_or_multiple_or_reasoning_answer($answerType, $studentChoice, $answer, $answerComment, $answerCorrect, $id, $questionId, $answerId);
				} else {
					$s = display_unique_or_multiple_or_reasoning_answer($answerType, $studentChoice, $answer, $answerComment, $answerCorrect, $id, $questionId, "");
				}
				echo $s;
				$i++;
			}
			echo '</tbody>';
			echo '</table>';
			if ($correctChoice == 'Y' && $answerWrong == 'N') {
				if (empty($feedback_if_true)) {
					$feedback_if_true = checkUTF8(get_lang('NoTrainerComment'));
				}
				$feedback_display = '<b>' . checkUTF8(get_lang('Feedback')) . ' : </b><span style="font-weight: bold; color: #008000;">' . checkUTF8($feedback_if_true) . '</span>';
			} else {
				if (empty($feedback_if_false)) {
					$feedback_if_false = checkUTF8(get_lang('NoTrainerComment'));
				}
				if (empty($count_ans)) {
					$feedback_if_false = $objAnswerTmp->selectComment($last_incorrect);
				}
				$feedback_display = '<b>' . checkUTF8(get_lang('Feedback')) . ' : </b><span style="font-weight: bold; color: #FF0000;">' . checkUTF8($feedback_if_false) . '</span>';
			}
			echo '</br>';
			//echo '<div class="span11">'.checkUTF8($feedback_display).'</div>';
			//echo '</br>';
			//echo '<div class="span1">'.get_lang("AddComments").'</div>';
			//echo "</section>";
			 if ($totalScoreMA > 0) {
				$totalScore+=$totalScoreMA;
			}
			$totalScoreMA = 0;

		}
		elseif ($answerType == REASONING) {
                $choice = array();
                $feedback_if_true = $feedback_if_false = '';
		// construction of the Answer object
                $objAnswerTmp = new Answer($questionId);
                $nbrAnswers = $objAnswerTmp->selectNbrAnswers();
                $questionScore = 0;
                $correctChoice = 'Y';
                $noStudentChoice = 'N';
                $answerWrong = 'N';
				echo '<table class="responsive large-only table-striped">';
				echo '<thead>'; 
				echo '<tr><th>'.checkUTF8(get_lang("Choice")).'</th>
						  <th>'.checkUTF8(get_lang("ExpectedChoice")).'</th>
						   <th>'.checkUTF8(get_lang("Answer")).'</th>';
				echo '</thead>';
				echo '<tbody>';
                for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
                    $answer = $objAnswerTmp->selectAnswer($answerId);
                    $answerComment = $objAnswerTmp->selectComment($answerId);
                    $correctComment[] = $objAnswerTmp->selectComment($answerId);
                    $answerCorrect = $objAnswerTmp->isCorrect($answerId);
                    $answerWeighting = $objAnswerTmp->selectWeighting($answerId);
                    $queryans = "select * from " . $TBL_TRACK_ATTEMPT . " where exe_id = '" . Database::escape_string($exe_id) . "' and question_id= '" . Database::escape_string($questionId) . "'";
                    $resultans = api_sql_query($queryans, __FILE__, __LINE__);
                    while ($row = Database::fetch_array($resultans)) {
                        $ind = $row['answer'];
                        $choice[$ind] = 1;
                    }
                    $studentChoice = $choice[$answerId];

                    if ($answerCorrect) {
                        $feedback_if_true = $objAnswerTmp->selectComment($answerId);
                    } else {
                        $feedback_if_false = $objAnswerTmp->selectComment($answerId);
                    }

                    if ($answerId == '2') {
                        $wrongAnswerWeighting = $answerWeighting;
                    }
                    if ($answerCorrect && $studentChoice == '1' && $correctChoice == 'Y') {
                        $correctChoice = 'Y';
                        $noStudentChoice = 'Y';
                    } elseif ($answerCorrect && !$studentChoice) {
                        $correctChoice = 'N';
                        $noStudentChoice = 'Y';
                        $answerWrong = 'Y';
                    } elseif (!$answerCorrect && $studentChoice == '1') {
                        $correctChoice = 'N';
                        $noStudentChoice = 'Y';
                        $answerWrong = 'Y';
                    }

                    if ($answerId == 1) {
                        $s = display_unique_or_multiple_or_reasoning_answer($answerType, $studentChoice, $answer, $answerComment, $answerCorrect, $id, $questionId, $answerId);
                    } else {
                        $s = display_unique_or_multiple_or_reasoning_answer($answerType, $studentChoice, $answer, $answerComment, $answerCorrect, $id, $questionId, "");
                    }
					echo $s;
                    $i++;
                }
				echo '</tbody>';
				echo '</table>';
                if ($answerType == REASONING && $noStudentChoice == 'Y') {
                    if ($correctChoice == 'Y') {
                        $questionScore += $questionWeighting;
                        $totalScore += $questionWeighting;
                    } else {
                        $questionScore += $wrongAnswerWeighting;
                        $totalScore += $wrongAnswerWeighting;
                    }
                }


                if ($correctChoice == 'Y' && $answerWrong == 'N') {
                    if (empty($feedback_if_true)) {
                        $feedback_if_true = get_lang('NoTrainerComment');
                    }
                    $feedback_display = '<b>' . get_lang('Feedback') . ' : </b><span style="font-weight: bold; color: #008000;">' . $feedback_if_true . '</span>';
                } else {
                    if (empty($feedback_if_false)) {
                        $feedback_if_false = get_lang('NoTrainerComment');
                    }
                    $feedback_display = '<b>' . get_lang('Feedback') . ' : </b><span style="font-weight: bold; color: #FF0000;">' . $feedback_if_false . '</span>';
                }

                echo '</br>';
				//echo '<div class="span11">'.checkUTF8($feedback_display).'</div>';
				//echo "</section>";
            }
			elseif ($answerType == UNIQUE_ANSWER) {
                $feedback_if_true = $feedback_if_false = '';
				$objAnswerTmp = new Answer($questionId);
                $nbrAnswers = $objAnswerTmp->selectNbrAnswers();
                $questionScore = 0;
                $correctChoice = 'N';
                $correctComment = array();
				echo '<table class="responsive large-only table-striped">';
				echo '<thead>'; 
				echo '<tr><th>'.checkUTF8(get_lang("Choice")).'</th>
						  <th>'.checkUTF8(get_lang("ExpectedChoice")).'</th>
						   <th>'.checkUTF8(get_lang("Answer")).'</th>';
				echo '</thead>';
				echo '<tbody>';
                for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
                    $answer = $objAnswerTmp->selectAnswer($answerId);
                    $answerComment = $objAnswerTmp->selectComment($answerId);
                    $correctComment[] = $objAnswerTmp->selectComment($answerId);
                    $answerCorrect = $objAnswerTmp->isCorrect($answerId);
                    if ($answerCorrect) {
                        $correct = $answerId;
                    } else {
                        $not_correct = $answerId;
                    }
                    $answerWeighting = $objAnswerTmp->selectWeighting($answerId);
                    $queryans = "select answer from " . $TBL_TRACK_ATTEMPT . " where exe_id = '" . Database::escape_string($exe_id) . "' and question_id= '" . Database::escape_string($questionId) . "'";
                    $resultans = api_sql_query($queryans, __FILE__, __LINE__);
                    $choice = Database::result($resultans, 0, "answer");
                    $studentChoice = ($choice == $answerId) ? 1 : 0;
                    if ($studentChoice) {
                        $questionScore+=$answerWeighting;
                        $totalScore+=$answerWeighting;
                        if ($studentChoice == $answerCorrect) {
                            $correctChoice = 'Y';
                            $feedback_if_true = $objAnswerTmp->selectComment($answerId);
                        } else {
                            $feedback_if_false = $objAnswerTmp->selectComment($answerId);
                        }
                    }

                    if ($answerId == 1) {
                        $s = display_unique_or_multiple_or_reasoning_answer($answerType, $studentChoice, $answer, $answerComment, $answerCorrect, $id, $questionId, $answerId);
                    } else {
                        $s = display_unique_or_multiple_or_reasoning_answer($answerType, $studentChoice, $answer, $answerComment, $answerCorrect, $id, $questionId, "");
                    }
					echo $s;
                    $i++;
                }
				echo '</tbody>';
				echo '</table>';
                
                if ($correctChoice == 'Y') {
                    $feedback_if_true = $objAnswerTmp->selectComment($correct);
                    if (empty($feedback_if_true)) {
                        $feedback_if_true = get_lang('NoTrainerComment');
                    }
                    $feedback_display = '<b>' . get_lang('Feedback') . ' : </b><span style="font-weight: bold; color: #008000;">' . $feedback_if_true . '</span>';
                } else {
                    $feedback_if_false = $objAnswerTmp->selectComment($not_correct);
                    if (empty($feedback_if_false)) {
                        $feedback_if_false = get_lang('NoTrainerComment');
                    }
                    $feedback_display = '<b>' . get_lang('Feedback') . ' : </b><span style="font-weight: bold; color: #FF0000;">' . $feedback_if_false . '</span>';
                }
				echo '</br>';
                //echo '<div class="span11">'.checkUTF8($feedback_display).'</div>';
				//echo "</section>";
			}
			elseif ($answerType == FILL_IN_BLANKS) {
                $feedback_if_true = $feedback_if_false = '';
                $objAnswerTmp = new Answer($questionId);
                $nbrAnswers = $objAnswerTmp->selectNbrAnswers();
                $questionScore = 0;
                $feedback_data = unserialize($objAnswerTmp->comment[1]);
                $feedback_true = $feedback_data['comment[1]'];
                $feedback_false = $feedback_data['comment[2]'];
                for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
                    $answer = $objAnswerTmp->selectAnswer($answerId);
                    $answerComment = $objAnswerTmp->selectComment($answerId);
                    $answerCorrect = $objAnswerTmp->isCorrect($answerId);
                    $answerWeighting = $objAnswerTmp->selectWeighting($answerId);

                    // the question is encoded like this
                    // [A] B [C] D [E] F::10,10,10@1
                    // number 1 before the "@" means that is a switchable fill in blank question
                    // [A] B [C] D [E] F::10,10,10@ or  [A] B [C] D [E] F::10,10,10
                    // means that is a normal fill blank question

                    $pre_array = explode('::', $answer);

                    // is switchable fill blank or not
                    $is_set_switchable = explode('@', $pre_array[1]);
                    $switchable_answer_set = false;
                    if ($is_set_switchable[1] == 1) {
                        $switchable_answer_set = true;
                    }

                    $answer = $pre_array[0];

                    // splits weightings that are joined with a comma
                    $answerWeighting = explode(',', $is_set_switchable[0]);
                    //list($answer,$answerWeighting)=explode('::',$multiple[0]);
                    //$answerWeighting=explode(',',$answerWeighting);
                    // we save the answer because it will be modified
                    $temp = $answer;

                    // TeX parsing
                    // 1. find everything between the [tex] and [/tex] tags
                    $startlocations = api_strpos($temp, '[tex]');
                    $endlocations = api_strpos($temp, '[/tex]');
                    if ($startlocations !== false && $endlocations !== false) {
                        $texstring = api_substr($temp, $startlocations, $endlocations - $startlocations + 6);
                        // 2. replace this by {texcode}
                        $temp = str_replace($texstring, '{texcode}', $temp);
                    }
                    $j = 0;
                    // the loop will stop at the end of the text
                    $i = 0;
                    $feedback_anscorrect = array();
                    $feedback_usertag = array();
                    $feedback_correcttag = array();
                    //normal fill in blank
                    if (!$switchable_answer_set) {
                        while (1) {
                            // quits the loop if there are no more blanks
                            if (($pos = api_strpos($temp, '[')) === false) {
                                // adds the end of the text
                                $answer.=$temp;
                                // TeX parsing
                                $texstring = api_parse_tex($texstring);
                                break;
                            }
                            $temp = api_substr($temp, $pos + 1);
                            // quits the loop if there are no more blanks
                            if (($pos = api_strpos($temp, ']')) === false) {
                                break;
                            }

                            $queryfill = "select answer from " . $TBL_TRACK_ATTEMPT . " where exe_id = '" . Database::escape_string($exe_id) . "' and question_id= '" . Database::escape_string($questionId) . "'";
                            $resfill = api_sql_query($queryfill, __FILE__, __LINE__);
                            $str = Database::result($resfill, 0, "answer");
                            $str = str_replace("<br />", "", $str);
                            $str = str_replace("<s>", "", $str);
                            $str = str_replace("</s>", "", $str);

                            preg_match_all('#\[([^[]*)\]#', $str, $arr);
                            $choice = $arr[1];
                            $tmp = strrpos($choice[$j], ' / ');
                            $choice[$j] = substr($choice[$j], 0, $tmp);
                            $choice[$j] = trim($choice[$j]);
                            $choice[$j] = stripslashes($choice[$j]);
                            $feedback_usertag[] = $choice[$j];
                            $feedback_correcttag[] = api_strtolower(api_substr($temp, 0, $pos));

                            $str = str_replace("[", " <u>", $str);
                            $str = str_replace("]", "</u> ", $str);
                            // if the word entered by the student IS the same as the one defined by the professor
                            if (trim(api_strtolower(api_substr($temp, 0, $pos))) == trim(api_strtolower($choice[$j]))) {
                                $feedback_anscorrect[] = "Y";
                                // gives the related weighting to the student
                                $questionScore+=$answerWeighting[$j];
                                // increments total score
                                $totalScore+=$answerWeighting[$j];
                            } else {
                                $feedback_anscorrect[] = "N";
                            }
                            // else if the word entered by the student IS NOT the same as the one defined by the professor
                            $j++;
                            $temp = api_substr($temp, $pos + 1);
                            $i = $i + 1;
                        }
                        $answer = stripslashes($str);
                    } else {
                        //multiple fill in blank
                        while (1) {
                            // quits the loop if there are no more blanks
                            if (($pos = api_strpos($temp, '[')) === false) {
                                // adds the end of the text
                                $answer.=$temp;
                                // TeX parsing
                                $texstring = api_parse_tex($texstring);
                                //$answer=str_replace("{texcode}",$texstring,$answer);
                                break;
                            }
                            // adds the piece of text that is before the blank and ended by [
                            $real_text[] = api_substr($temp, 0, $pos + 1);
                            $answer.=api_substr($temp, 0, $pos + 1);
                            $temp = api_substr($temp, $pos + 1);

                            // quits the loop if there are no more blanks
                            if (($pos = api_strpos($temp, ']')) === false) {
                                // adds the end of the text
                                //$answer.=$temp;
                                break;
                            }

                            $queryfill = "SELECT answer FROM " . $TBL_TRACK_ATTEMPT . " WHERE exe_id = '" . Database::escape_string($exe_id) . "' and question_id= '" . Database::escape_string($questionId) . "'";
                            $resfill = api_sql_query($queryfill, __FILE__, __LINE__);
                            $str = Database::result($resfill, 0, "answer");
                            $str = str_replace("<br />", "", $str);

                            preg_match_all('#\[([^[/]*)/#', $str, $arr);
                            $choice = $arr[1];

                            $choice[$j] = trim($choice[$j]);
                            $user_tags[] = api_strtolower($choice[$j]);
                            $correct_tags[] = api_strtolower(api_substr($temp, 0, $pos));

                            $j++;
                            $temp = api_substr($temp, $pos + 1);
                            $i = $i + 1;
                        }
                        $answer = '';
                        for ($i = 0; $i < count($correct_tags); $i++) {
                            if (in_array($user_tags[$i], $correct_tags)) {
                                // gives the related weighting to the student
                                $questionScore+=$answerWeighting[$i];
                                // increments total score
                                $totalScore+=$answerWeighting[$i];
                            }
                        }
                        $answer = stripslashes($str);
                        $answer = str_replace('rn', '', $answer);
                    }

                    
                    //echo '<div class="span5"><b>' . $answer . '</b></div>';
                    //$s .= '<table width="100%" border="0"><tr><td><b>' . get_lang('Feedback') . '</b></td></tr>';
                    $fy = 0;
                    $fn = 0;

                    for ($k = 0; $k < sizeof($feedback_anscorrect); $k++) {
                        if ($feedback_anscorrect[$k] == "Y") {
                            $fy++;
                        } else {
                            $fn++;
                        }
                    }
                    if ($fy >= $fn && $fy > 0) {
                        $feedback_display = '<span style="font-weight: bold; color: #008000;">' . $feedback_true . '</span>';
                    } else {
                        $feedback_display = '<span style="font-weight: bold; color: #FF0000;">' . $feedback_false . '</span>';
                    }
                   					
                    $i++;
                }
				echo "</br>";
				echo '<div><b>' . checkUTF8($answer) . '</b></div>';
				echo "</br>";
                //echo '<div>'.get_lang('Feedback').' : '.checkUTF8($feedback_display).'</div>';
				//echo '</section>';
            } 
			elseif ($answerType == FREE_ANSWER) {
                $feedback_if_true = $feedback_if_false = '';
                $answer = $str;
               
                $objAnswerTmp = new Answer($questionId);
                $nbrAnswers = $objAnswerTmp->selectNbrAnswers();
                $questionScore = 0;
                $query = "select answer, marks from " . $TBL_TRACK_ATTEMPT . " where exe_id = '" . Database::escape_string($exe_id) . "' and question_id= '" . Database::escape_string($questionId) . "'";
                $resq = api_sql_query($query);
                $choice = Database::result($resq, 0, "answer");
                $choice = stripslashes($choice);
                $choice = str_replace('rn', '', $choice);

                $questionScore = Database::result($resq, 0, "marks");
                if ($questionScore == -1) {
                    $totalScore+=0;
                } else {
                    $totalScore+=$questionScore;
                }
				echo "</br>";
				echo '<div><b>' . checkUTF8($choice) . '</b></div>';
				if(api_is_allowed_to_edit()){
					$comm = get_comments($exe_id, $questionId);
				}
				echo '<div><b>' . checkUTF8($comm) . '</b></div>';
				echo "</br>";
                echo '<div>'.checkUTF8(get_lang('notCorrectedYet')).'</div>';
				//echo '</section>';
             
            }
			else if ($answerType == MATCHING) {
                $feedback_if_true = $feedback_if_false = '';
                $objAnswerTmp = new Answer($questionId);
                $answerComment_true = $objAnswerTmp->selectComment(1);
                $answerComment_false = $objAnswerTmp->selectComment(2);
                $questionScore = 0;
                $table_ans = Database :: get_course_table(TABLE_QUIZ_ANSWER);
                $TBL_TRACK_ATTEMPT = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);
                $answer_ok = 'N';
                $answer_wrong = 'N';
                $sql_select_answer = 'SELECT id, answer, correct, position, ponderation FROM ' . $table_ans . ' WHERE question_id="' . Database::escape_string($questionId) . '" AND correct<>0 ORDER BY position';
                $sql_answer = 'SELECT position, answer FROM ' . $table_ans . ' WHERE question_id="' . Database::escape_string($questionId) . '" AND correct=0 ORDER BY position';
                $res_answer = api_sql_query($sql_answer, __FILE__, __LINE__);
                // getting the real answer
                $real_list = array();
                while ($real_answer = Database::fetch_array($res_answer)) {
                    $real_list[$real_answer['position']] = $real_answer['answer'];
                }

                $res_answers = api_sql_query($sql_select_answer, __FILE__, __LINE__);

                echo '<table class="responsive large-only table-striped">';
                echo '<thead>';
                echo '<tr>
                                <th align="center" width="30%"><span style="font-style: italic;color:#4171B5;font-weight:bold;">' . checkUTF8(get_lang("ElementList")) . '</span> </th>
                                <th align="center" width="35%"><span style="font-style: italic;color:#4171B5;font-weight:bold;">' . checkUTF8(get_lang("YourAnswers")) . '</span></th>
                                <th align="center" width="35%"><span style="font-style: italic;color:#4171B5;font-weight:bold;">' . checkUTF8(get_lang("Correct")) . '</span></th>
                            </tr>';
                echo '</thead>';
				echo '<tbody>';
                while ($a_answers = Database::fetch_array($res_answers)) {
                    $i_answer_id = $a_answers['id']; //3
                    $s_answer_label = $a_answers['answer'];  // your dady - you mother
                    $i_answer_correct_answer = $a_answers['correct']; //1 - 2
                    $i_answer_position = $a_answers['position']; // 3 - 4

                    $sql_user_answer = 'SELECT answers.answer
                        FROM ' . $TBL_TRACK_ATTEMPT . ' as track_e_attempt
                        INNER JOIN ' . $table_ans . ' as answers
                            ON answers.position = track_e_attempt.answer
                            AND track_e_attempt.question_id=answers.question_id
                        WHERE answers.correct = 0
                        AND track_e_attempt.exe_id = "' . Database::escape_string($exe_id) . '"
                        AND track_e_attempt.question_id = "' . Database::escape_string($questionId) . '"
                        AND track_e_attempt.position="' . Database::escape_string($i_answer_position) . '"';


                    $res_user_answer = api_sql_query($sql_user_answer, __FILE__, __LINE__);
                    if (Database::num_rows($res_user_answer) > 0) {
                        $s_user_answer = Database::result($res_user_answer, 0, 0); //  rich - good looking
                    } else {
                        $s_user_answer = '';
                    }

                    //$s_correct_answer = $s_answer_label; // your ddady - your mother
                    $s_correct_answer = $real_list[$i_answer_correct_answer];
                    $i_answerWeighting = $a_answers['ponderation']; //$objAnswerTmp->selectWeighting($ind);
                    if ($s_user_answer == $real_list[$i_answer_correct_answer]) { // rich == your ddady?? wrong
                        $questionScore+=$i_answerWeighting;
                        $totalScore+=$i_answerWeighting;
                        if ($answer_wrong == 'N') {
                            $answer_ok = 'Y';
                        }
                    } else {
                        $s_user_answer = '<span style="color: #FF0000; text-decoration: line-through;">' . checkUTF8($s_user_answer) . '</span>';
                        $answer_wrong = 'Y';
                    }
                    if ($questionScore > 20) {
                        $questionScore = round($questionScore);
                    }
                    echo '<tr>';
                    echo '<td align="center"><div id="matchresult">' . $s_answer_label . '</div></td><td align="center" width="30%"><div id="matchresult">' . checkUTF8($s_user_answer) . '</div></td><td align="center"><div id="matchresult"><b><span>' . checkUTF8($s_correct_answer) . '</span></b></div></td>';
                    echo '</tr>';
                }
                /*echo '<tfoot>';
                echo '<tr ><td colspan="3"><b>' . get_lang('Feedback') . '</b></td></tr><tr>';*/
                if ($answer_ok == 'Y' && $answer_wrong == 'N') {
                    $feedback_display = '<span style="font-weight: bold; color: #008000;">' . $answerComment_true . '</span>';
                } else {
                    $feedback_display = '<span style="font-weight: bold; color: #FF0000;">' . $answerComment_false . '</span>';
                }
                //echo '</tr></tfoot></table>';
				echo '</tbody></table>';
				echo '</br></br>';
				//echo '<div><b>'.get_lang("Feedback")." : </b>".checkUTF8($feedback_display)."</div>";
				//echo '</br>';
				//echo '</section>';
            }
			elseif ($answerType == HOT_SPOT) {				
                $feedback_if_true = $feedback_if_false = '';				
                $objAnswerTmp = new Answer($questionId);
                $nbrAnswers = $objAnswerTmp->selectNbrAnswers();
                $questionScore = 0;
                $correctComment = array();
                $answerOk = 'N';
                $answerWrong = 'N';
                $totalScoreHot = 0;
                $hotspot_colors = array("", // $i starts from 1 on next loop (ugly fix)
                    "#4271B5",
                    "#FE8E16",
                    "#3B3B3B",
                    "#BCD631",
                    "#D63173",
                    "#D7D7D7",
                    "#90AFDD",
                    "#AF8640",
                    "#4F9242",
                    "#F4EB24",
                    "#ED2024",
                    "#45C7F0",
                    "#F7BDE2");

                echo '<table width="100%" border="0"><tr><td width="100%"><div align="center"><object type="application/x-shockwave-flash" data="../plugin/hotspot/hotspot_solution.swf?modifyAnswers=' . Security::remove_XSS($questionId) . '&exe_id=' . $exe_id . '&from_db=1" width="500" height="400">
                            <param name="movie" value="../plugin/hotspot/hotspot_solution.swf?modifyAnswers=' . Security::remove_XSS($questionId) . '&exe_id=' . $exe_id . '&from_db=1" />
                        </object></div></td></tr></table>';
				//echo $s;
				echo '</section>';
                echo "<section class='span6  offset1 manage-catalogue'>";
				echo "<table class='responsive large-only table-striped'>";
				echo '<thead><tr><th>AnswerId</th><th>Answer</th><th>Choice</th></tr>';
				echo '<tbody>';
                for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
					
                    $answer = $objAnswerTmp->selectAnswer($answerId);
                    $answerComment = $objAnswerTmp->selectComment($answerId);
                    $correctComment[] = $objAnswerTmp->selectComment($answerId);
                    $answerCorrect = $objAnswerTmp->isCorrect($answerId);
                    if ($nbrAnswers == 1) {
                        $correctComment = explode("~", $objAnswerTmp->selectComment($answerId));
                    } else {
                        if ($answerId == 1) {
                            $correctComment[] = $objAnswerTmp->selectComment(1);
                            $correctComment[] = $objAnswerTmp->selectComment(2);
                        } else {
                            $correctComment[] = $objAnswerTmp->selectComment($answerId);
                        }
                    }

                    $TBL_TRACK_HOTSPOT = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_HOTSPOT);
                    $query = "select hotspot_correct from " . $TBL_TRACK_HOTSPOT . " where hotspot_exe_id = '" . Database::escape_string($exe_id) . "' and hotspot_question_id= '" . Database::escape_string($questionId) . "' AND hotspot_answer_id='" . Database::escape_string($answerId) . "'";
                    $resq = api_sql_query($query);
                    $choice = Database::result($resq, 0, "hotspot_correct");

                    $queryfree = "select marks from " . $TBL_TRACK_ATTEMPT . " where exe_id = '" . Database::escape_string($exe_id) . "' and question_id= '" . Database::escape_string($questionId) . "'";
                    $resfree = api_sql_query($queryfree, __FILE__, __LINE__);
                    $questionScore = Database::result($resfree, 0, "marks");

                    if ($choice) {
                        $answerOk = 'Y';
                        $img_choice = get_lang('Right');
                    } else {
                        $answerOk = 'N';
                        $answerWrong = 'Y';
                        $img_choice = get_lang('Wrong');
                    }
                    echo '<tr><td><div style="height:11px; width:11px; background-color:' . checkUTF8($hotspot_colors[$answerId]) . '; display:inline; float:left; margin-top:3px;"></div>&nbsp;' . checkUTF8($answerId) . '</td><td>' . checkUTF8($answer) . '</td><td>' . checkUTF8($img_choice) . '</td></tr>';
                }
				//echo $s;
				echo '</tbody></table>';
               // $s .= '</table></div><br/><br/>';
                if ($answerOk == 'Y' && $answerWrong == 'N') {
                    if ($nbrAnswers == 1) {
                        $feedback = '<span style="font-weight: bold; color: #008000;">' . $correctComment[0] . '</span>';
                    } else {
                        $feedback = '<span style="font-weight: bold; color: #008000;">' . $correctComment[1] . '</span>';
                    }
                } else {
                    if ($nbrAnswers == 1) {
                        $feedback = '<span style="font-weight: bold; color: #FF0000;">' . $correctComment[1] . '</span>';
                    } else {
                        $feedback = '<span style="font-weight: bold; color: #FF0000;">' . $correctComment[2] . '</span>';
                    }
                }
                if (!empty($feedback)) {
					echo '</br>';
                    echo '<div align="left" class="quiz_feedback"><b>' . checkUTF8(get_lang('Feedback')) . '</b> : ' . checkUTF8($feedback) . '</div>';
                }
                //$s .= '</div></td></tr></table>';
                //echo $s;
                $totalScore+=$questionScore;
				//echo '</section>';
            } 

			echo '</section>';			
			
			if($answerType != HOT_SPOT && $answerType != MATCHING){
			echo "<section class='span6  offset1 manage-catalogue'>";
			echo "<div>";
			echo checkUTF8($questionDescription);
			echo "</div>";
			echo "</section>";	
			}
			echo '</br>';
			if ($questionWeighting - $questionScore < 0.50) {
				$my_total_score = round(float_format($questionScore, 1));
			} else {
				$my_total_score = float_format($questionScore, 1);
			}
			$my_total_weight = float_format($questionWeighting, 1);
			if ($my_total_score < 0) {
				$my_total_score = 0;
			}       
			if($answerType != HOT_SPOT){
			echo '<div class="span11">'.checkUTF8($feedback_display).'</div>';
			}
			echo "</div>";
			echo '</br>';
			echo '<div class="span2"><b>'.checkUTF8(get_lang('Score')).' : </b> '.round($my_total_score). ' / '.round($my_total_weight).'</div>';
			echo '</br>';
			echo "</div>";
			unset($objAnswerTmp);
                    $i++;
                    $totalWeighting+=$questionWeighting;
			//echo "</div>";
			//echo "</div>";
			//echo "</form>";
	}
	echo '</br>';
	if ($origin != 'learnpath' || ($origin == 'learnpath' && isset($_GET['fb_type']))) {
		if ($show_score) {
			echo '<div id="question_score">';
			if ($certif_available && $origin != 'learnpath') {
				echo '<a class="certificate-' . $exercise_id . '-link" href="#">' . Display::return_icon('certificate48x48.png', get_lang('GetCertificate'), array('style' => 'position:absolute;top:0px;right:10px;')) . '</a>';
				$obj_certificate->displayCertificate('html', 'quiz', $exercise_id, $course_code, null, true);
			}
			echo api_convert_encoding(get_lang('YourTotalScore'),'UTF-8',api_get_system_encoding()) . ' : ';
			if ($dsp_percent == true) {
				$my_result = number_format(($totalScore / $totalWeighting) * 100, 1, '.', '');
				$my_result = round(float_format($my_result, 1));
				echo $my_result . "%";
			} else {
				$my_total_score = round($totalScore);
				$my_total_weight = round(float_format($totalWeighting, 1));
				echo $my_total_score . " / " . $my_total_weight;
			}
			//echo '<span style="padding-left:65%;"><a href="#top">'.get_lang('BackToTop').'</a></span>';
			echo '</div>';
			
			$sql_update_score= "update ".$TBL_TRACK_EXERCICES." set exe_result ='". round(float_format($totalScore,1))."', exe_weighting = '". round(float_format($totalWeighting,1))."' where exe_id = '".Database::escape_string($exe_id)."'";
            $result_final = Database::query($sql_update_score, __FILE__, __LINE__);
		}
	}
	
}
echo '</div>';
function get_comments($id, $question_id) {
    global $TBL_TRACK_ATTEMPT;
    $sql = "SELECT teacher_comment FROM " . $TBL_TRACK_ATTEMPT . " where exe_id='" . Database::escape_string($id) . "' and question_id = '" . Database::escape_string($question_id) . "' ORDER by question_id";
    $sqlres = api_sql_query($sql, __FILE__, __LINE__);
    $comm = Database::result($sqlres, 0, "teacher_comment");
    return $comm;
}