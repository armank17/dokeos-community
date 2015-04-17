<?php
require_once dirname(__FILE__).'/autoload.php';

$action = strip_tags($_GET['action']);

switch ($action) {
    
    case 'save':
        $saved = saveExamMode($_POST);
        echo $saved['answered'].'/'.$saved['countQuestions'];
        break;
    case 'complete':
        $saved = saveExamMode($_POST, 'complete');
        echo $saved['exeId'];
        break;    
}

function saveExamMode($post, $status = 'incomplete') {
    
    if (!empty($post)) {
        $exerciseId = intval($post['exerciseId']);
        $choice = $post['choice'];
        $objExercise = new Exercise();
        $objExercise->read($exerciseId);

        $questionList = $objExercise->selectQuestionList();
        $totalQuestionScore = $totalQuestionWeighting = 0;
        if (!empty($questionList)) {            
            foreach ($questionList as $questionId) {
                $scores = getQuestionScores($questionId, $choice[$questionId]);
                if ($scores['questionScore'] < 0) {
                    $scores['questionScore'] = 0;
                }
                if ($scores['questionScore'] > $scores['questionWeighting']) {
                    $scores['questionScore'] = $scores['questionWeighting'];
                }
                $totalQuestionScore += $scores['questionScore'];
                $totalQuestionWeighting += $scores['questionWeighting'];
            }
            // save track exercises
            $lastExeId = saveTrackExercise($exerciseId, $status, $totalQuestionScore, $totalQuestionWeighting);            
            
            // sace track attempt
            if (!empty($lastExeId)) {
                foreach ($questionList as $questionId) {
                    $scores = getQuestionScores($questionId, $choice[$questionId]);
                    $newchoice = isset($post['newchoice'])?$post['newchoice']:'';
                    saveTrackAttempt($choice[$questionId], $questionId, $scores['questionScore'], $lastExeId, $scores['matching'], $newchoice, $scores['newAnswer'], $scores['mychoice']);
                }
            }
        }        
    }
    $answered = $objExercise->getCountQuestionAnswersAttempt($exerciseId);
    return array('answered'=>$answered, 'countQuestions'=> count($questionList), 'exeId'=>$lastExeId);
}

function saveTrackExercise($exerciseId, $status = 'incomplete', $score = 0, $weighting = 0, $userId = NULL, $courseCode = NULL, $sessionId = NULL) {
    
    if (!isset($userId)) {
        $userId = api_get_user_id();
    }
    if (!isset($courseCode)) {
        $courseCode = api_get_course_id();
    }
    if (!isset($sessionId)) {
        $sessionId = api_get_session_id();
    }
    
    $tblTrackExercise = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
    
    // get las exeId incomplete
    $rs = Database::query("SELECT exe_id 
                            FROM $tblTrackExercise 
                            WHERE exe_exo_id='".intval($exerciseId)."' AND 
                                  exe_user_id='".intval($userId)."' AND 
                                  exe_cours_id='".Database::escape_string($courseCode)."' AND 
                                  session_id = '".intval($sessionId)."' AND
                                  status IN('incomplete', 'left incomplete')                              
                            ORDER BY exe_id DESC LIMIT 1");
    if (Database::num_rows($rs) > 0) {
        $row = Database::fetch_object($rs);
        $exeId = $row->exe_id;
        Database::query("UPDATE $tblTrackExercise SET 
                                    status = '".Database::escape_string($status)."', 
                                    exe_result = $score,
                                    exe_weighting = $weighting,
                                    exe_date = '".date('Y-m-d H:i:s')."' 
                                 WHERE exe_id = '$exeId'");
    }
    else {
        Database::query("INSERT INTO $tblTrackExercise SET
                            exe_exo_id = '".intval($exerciseId)."',
                            exe_user_id = '".intval($userId)."',
                            exe_cours_id = '".Database::escape_string($courseCode)."',
                            exe_result = $score,
                            exe_weighting = $weighting,
                            status = '".Database::escape_string($status)."',
                            session_id = '".intval($sessionId)."',
                            start_date = '".date('Y-m-d H:i:s')."',
                            exe_date = '".date('Y-m-d H:i:s')."'
                        ");
        $exeId = Database::insert_id();
    }
    return $exeId;
}

function saveTrackAttempt($choice, $questionId, $questionScore, $exeId, $matching = array(), $newchoice = '', $newAnswer = '', $mychoice = array()) {
        $objQuestionTmp = Question ::read($questionId);
        $answerType = $objQuestionTmp->type;    
        if (empty($choice)) {
            $choice = 0;
        }
        check_attempt($questionId, $exeId);
        if ($answerType == MULTIPLE_ANSWER) {
            if ($choice != 0) {
                $reply = array_keys($choice);
                for ($i = 0; $i < sizeof($reply); $i++) {
                    $ans = $reply[$i];
                    exercise_attempt($questionScore, $ans, $questionId, $exeId, $i);
                }
            } else {
                exercise_attempt($questionScore, 0, $questionId, $exeId, 0);
            }
        } 
        elseif ($answerType == REASONING) {
            if ($choice != 0) {
                $reply = array_keys($choice);
                for ($i = 0; $i < sizeof($reply); $i++) {
                    $ans = $reply[$i];
                    exercise_attempt($questionScore, $ans, $questionId, $exeId, $i);
                }
            } else {
                exercise_attempt($questionScore, 0, $questionId, $exeId, 0);
            }
        } 
        elseif ($answerType == MATCHING) {
            $tblQuestionAnswer = Database::get_course_table(TABLE_QUIZ_ANSWER);           
            $j = sizeof($matching) + 1;
            for ($i = 0; $i < sizeof($mychoice); $i++, $j++) {               
                $val = $mychoice[$j];
                if (preg_match_all('#<font color="red"><s>([0-9a-z ]*)</s></font>#', $val, $arr1)) {
                    $val = $arr1[1][0];
                }                
                $res = Database::query("SELECT position FROM $tblQuestionAnswer WHERE question_id='".intval($questionId)."' AND answer LIKE BINARY '".Database::escape_string($val)."' AND correct=0");
                if (Database :: num_rows($res) > 0) {
                    $answer = Database :: result($res, 0, "position");
                } else {
                    $answer = '';
                }
                exercise_attempt($questionScore, $answer, $questionId, $exeId, $j);
            }
        } 
        elseif ($answerType == FREE_ANSWER) {
            exercise_attempt($questionScore, $newchoice, $questionId, $exeId, 0);
        } 
        elseif ($answerType == UNIQUE_ANSWER) {
            exercise_attempt($questionScore, $choice, $questionId, $exeId, 0);
        } 
        else {
            exercise_attempt($questionScore, $newAnswer, $questionId, $exeId, 0);
        }        
}

function getQuestionScores($questionId, $choice) {
    
    $objQuestionTmp = Question ::read($questionId);
    $questionWeighting = $objQuestionTmp->selectWeighting();
    $answerType = $objQuestionTmp->type;
    
    $objAnswerTmp = new Answer($questionId);
    $nbrAnswers = $objAnswerTmp->selectNbrAnswers();
    $questionScore = $totalScoreMA = $totalScore = 0;
    if ($answerType == FREE_ANSWER) {
        $nbrAnswers = 1;
    }
                
    $correctChoice = 'Y';
    $noStudentChoice = 'N';
    $answerWrong = 'N';
    //$matching = array();
    $newAnswer = '';
    $mychoice = $choice;
    for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
        $answer = $objAnswerTmp->selectAnswer($answerId);
        $answerCorrect = $objAnswerTmp->isCorrect($answerId);
        $answerWeighting = $objAnswerTmp->selectWeighting($answerId);
        switch ($answerType) {
            // for unique answer
            case UNIQUE_ANSWER :
                $studentChoice = ($choice == $answerId) ? 1 : 0;
                if ($studentChoice) {
                    $questionScore += $answerWeighting;
                }
                break;
            // for multiple answers
            case MULTIPLE_ANSWER :
                $studentChoice = $choice[$answerId];
                if ($studentChoice) {
                    $questionScore += $answerWeighting;
                }
                break;
            // for reasoning answers
            case REASONING :
                $studentChoice = $choice[$answerId];
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
                break;
            // for fill in the blanks
            case FILL_IN_BLANKS :
                $answer = str_replace("\r\n", '<br />', $answer);
                $pre_array = explode('::', $answer);
                // is switchable fill blank or not
                $last = count($pre_array) - 1;
                $is_set_switchable = explode('@', $pre_array[$last]);

                $switchable_answer_set = false;
                if (isset($is_set_switchable[1]) && $is_set_switchable[1] == 1) {
                    $switchable_answer_set = true;
                }

                $answer = '';
                for ($k = 0; $k < $last; $k++) {
                    $answer .= $pre_array[$k];
                }

                // splits weightings that are joined with a comma
                $answerWeighting = explode(',', $is_set_switchable[0]);

                // we save the answer because it will be modified
                $temp = $answer;

                // TeX parsing
                // 1. find everything between the [tex] and [/tex] tags
                $startlocations = strpos($temp, '[tex]');
                $endlocations = strpos($temp, '[/tex]');

                if ($startlocations !== false && $endlocations !== false) {
                    $texstring = substr($temp, $startlocations, $endlocations - $startlocations + 6);
                    // 2. replace this by {texcode}
                    $temp = str_replace($texstring, '{texcode}', $temp);
                }

                $answer = '';
                $j = 0;
                //initialise answer tags
                $user_tags = array();
                $correct_tags = array();
                $real_text = array();
                // the loop will stop at the end of the text
                while (1) {
                    // quits the loop if there are no more blanks (detect '[')
                    if (($pos = strpos($temp, '[')) === false) {
                        // adds the end of the text
                        $answer = $temp;
                        // TeX parsing - replacement of texcode tags
                        $texstring = api_parse_tex($texstring);
                        $answer = str_replace("{texcode}", $texstring, $answer);
                        $real_text[] = $answer;
                        break; //no more "blanks", quit the loop
                    }
                    // adds the piece of text that is before the blank
                    //and ends with '[' into a general storage array
                    $real_text[] = substr($temp, 0, $pos + 1);
                    $answer .= substr($temp, 0, $pos + 1);
                    //take the string remaining (after the last "[" we found)
                    $temp = substr($temp, $pos + 1);
                    // quit the loop if there are no more blanks, and update $pos to the position of next ']'
                    if (($pos = strpos($temp, ']')) === false) {
                        // adds the end of the text
                        $answer .= $temp;
                        break;
                    }
                    $choice[$j] = trim($choice[$j]);
                    $user_tags[] = strtolower($choice[$j]);
                    //put the contents of the [] answer tag into correct_tags[]
                    $correct_tags[] = strtolower(substr($temp, 0, $pos));
                    $j++;
                    $temp = substr($temp, $pos + 1);
                }

                $answer = '';
                $real_correct_tags = $correct_tags;
                $chosen_list = array();
                for ($i = 0; $i < count($real_correct_tags); $i++) {
                    if ($i == 0) {
                        $answer .= $real_text[0];
                    }
                    if (!$switchable_answer_set) {
                        if (trim($correct_tags[$i]) == trim($user_tags[$i])) {
                            // gives the related weighting to the student
                            $questionScore += $answerWeighting[$i];
                            // increments total score
                            $totalScore += $answerWeighting[$i];
                            // adds the word in green at the end of the string
                            $answer .= $correct_tags[$i];
                        }
                        // else if the word entered by the student IS NOT the same as the one defined by the professor
                        elseif (!empty($user_tags[$i])) {
                            // adds the word in red at the end of the string, and strikes it
                            $answer .= '<font color="red"><s>' . $user_tags[$i] . '</s></font>';
                        } else {
                            // adds a tabulation if no word has been typed by the student
                            $answer .= '&nbsp;&nbsp;&nbsp;';
                        }
                    } else {
                        // switchable fill in the blanks
                        if (in_array($user_tags[$i], $correct_tags)) {
                            $chosen_list[] = $user_tags[$i];
                            $correct_tags = array_diff($correct_tags, $chosen_list);
                            // gives the related weighting to the student
                            $questionScore += $answerWeighting[$i];
                            // increments total score
                            $totalScore += $answerWeighting[$i];
                            // adds the word in green at the end of the string
                            $answer .= $user_tags[$i];
                        } elseif (!empty($user_tags[$i])) {
                            // else if the word entered by the student IS NOT the same as the one defined by the professor
                            // adds the word in red at the end of the string, and strikes it
                            $answer .= '<font color="red"><s>' . $user_tags[$i] . '</s></font>';
                        } else {
                            // adds a tabulation if no word has been typed by the student
                            $answer .= '&nbsp;&nbsp;&nbsp;';
                        }
                    }
                    // adds the correct word, followed by ] to close the blank
                    $answer .= ' / <font color="green"><b>' . $real_correct_tags[$i] . '</b></font>]';
                    if (isset($real_text[$i + 1])) {
                        $answer .= $real_text[$i + 1];
                    }
                }
                $newAnswer = $answer;
                break;
            // for free answer
            case FREE_ANSWER :
                $studentChoice = $choice;
                if ($studentChoice) {
                    $questionScore = -1;
                }
                break;
            // for matching
            case MATCHING :
                if ($answerCorrect) {
                    if ($answerCorrect == $mychoice[$answerId]) {
                        $questionScore += $answerWeighting;
                        $mychoice[$answerId] = $matching[$mychoice[$answerId]];
                    } elseif (!$mychoice[$answerId]) {
                        $mychoice[$answerId] = '&nbsp;&nbsp;&nbsp;';
                    } else {
                        $mychoice[$answerId] = $matching[$mychoice[$answerId]];
                    }
                } else {
                    $matching[$answerId] = $answer;
                }
                break;                           
        } // end switch Answertype
    }
                        
    if ($answerType == REASONING && $noStudentChoice == 'Y') {
        if ($correctChoice == 'Y') {
            $questionScore += $questionWeighting;
        } else {
            $questionScore += $wrongAnswerWeighting;
        }
    }
    return array('questionScore' => $questionScore, 'questionWeighting' => $questionWeighting, 'matching'=>$matching, 'newAnswer'=>$newAnswer, 'mychoice'=>$mychoice); 
}

?>
