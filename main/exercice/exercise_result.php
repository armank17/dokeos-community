<?php
/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2008 Dokeos SPRL

	For a full list of contributors, see "credits.txt".
	The full license can be read in "license.txt".

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	See the GNU General Public License for more details.

	Contact address: Dokeos, rue du Corbeau, 108, B-1030 Brussels, Belgium
	Mail: info@dokeos.com
==============================================================================
*/
/**
*	Exercise result
*	This script gets informations from the script "exercise_submit.php",
*	through the session, and calculates the score of the student for
*	that exercise.
*	Then it shows the results on the screen.
*	@package dokeos.exercise
*	@author Olivier Brouckaert, main author
*	@author Roan Embrechts, some refactoring
* 	@author Julio Montoya Armas switchable fill in blank option added
* 	@version $Id: exercise_result.php 22201 2009-07-17 19:57:03Z cfasanando $
*
*	@todo	split more code up in functions, move functions to library?
*/
/*
==============================================================================
		INIT SECTION
==============================================================================
*/
require_once('exercise.class.php');
require_once('question.class.php');
require_once('answer.class.php');
if ($_GET['origin']=='learnpath') {
	require_once ('../newscorm/learnpath.class.php');
	require_once ('../newscorm/learnpathItem.class.php');
	require_once ('../newscorm/scorm.class.php');
	require_once ('../newscorm/scormItem.class.php');
	require_once ('../newscorm/aicc.class.php');
	require_once ('../newscorm/aiccItem.class.php');
}
global $_cid;
// name of the language file that needs to be included
$language_file=array('exercice','tracking','admin');

require('../inc/global.inc.php');
$this_section=SECTION_COURSES;
$htmlHeadXtra[] = '<script type="text/javascript" language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'jwplayer/jwplayer.js"></script>';
/* ------------	ACCESS RIGHTS ------------ */
// notice for unauthorized people.

define('DOKEOS_EXERCISE', true);

if ( empty ( $origin ) ) {
    $origin = $_REQUEST['origin'];
}

if ($origin == 'learnpath')
	api_protect_course_script();
else
	api_protect_course_script(true);

require_once api_get_path(LIBRARY_PATH).'mail.lib.inc.php';
require_once api_get_path(LIBRARY_PATH).'course.lib.php';
require_once api_get_path(LIBRARY_PATH).'geometry.lib.php';

// Database table definitions
$TBL_EXERCICE_QUESTION 	= Database::get_course_table(TABLE_QUIZ_TEST_QUESTION);
$TBL_EXERCICES         	= Database::get_course_table(TABLE_QUIZ_TEST);
$TBL_QUESTIONS         	= Database::get_course_table(TABLE_QUIZ_QUESTION);
$TBL_REPONSES          	= Database::get_course_table(TABLE_QUIZ_ANSWER);
$TBL_TRACK_EXERCICES	= Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
$TBL_TRACK_ATTEMPT		= Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);
$main_user_table 		= Database :: get_main_table(TABLE_MAIN_USER);
$main_course_user_table = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
$table_ans 				= Database :: get_course_table(TABLE_QUIZ_ANSWER);

//temp values to move to admin settings
$dsp_percent = false; //false to display total score as absolute values
//debug param. 0: no display - 1: debug display
$debug=0;
if($debug>0){echo str_repeat('&nbsp;',0).'Entered exercise_result.php'."<br />\n";var_dump($_POST);}
// general parameters passed via POST/GET
if ( empty ( $origin ) ) {
     $origin = Security::remove_XSS($_REQUEST['origin']);
}
if ( empty ( $learnpath_id ) ) {
     $learnpath_id       = Security::remove_XSS($_REQUEST['learnpath_id']);
}
if ( empty ( $learnpath_item_id ) ) {
     $learnpath_item_id  = Security::remove_XSS($_REQUEST['learnpath_item_id']);
}
if ( empty ( $formSent ) ) {
    $formSent       = $_REQUEST['formSent'];
}
if ( empty ( $exerciseResult ) ) {
     $exerciseResult = $_SESSION['exerciseResult'];
}
if ( empty ( $exerciseResultCoordinates ) ) {
     $exerciseResultCoordinates = $_SESSION['exerciseResultCoordinates'];
}
if ( empty ( $questionId ) ) {
    $questionId = $_REQUEST['questionId'];
}
if ( empty ( $choice ) ) {
    $choice = $_REQUEST['choice'];
}
if ( empty ( $questionNum ) ) {
   $questionNum    = $_REQUEST['questionNum'];
}
if ( empty ( $nbrQuestions ) ) {
    $nbrQuestions   = $_REQUEST['nbrQuestions'];
}
if ( empty ( $questionList ) ) {
    $questionList = $_SESSION['questionList'];
}
if ( empty ( $objExercise ) ) {
    $objExercise = $_SESSION['objExercise'];
}
if ( empty ( $exerciseType ) ) {
    $exerciseType = $_REQUEST['exerciseType'];
}

$course_code = api_get_course_id();
if(isset($_SESSION['expired_time'][$course_code][intval($_SESSION['id_session'])][$objExercise->id][$learnpath_id]))
{
	unset($_SESSION['expired_time'][$course_code][intval($_SESSION['id_session'])][$objExercise->id][$learnpath_id]);
}

$_configuration['live_exercise_tracking'] = false;
if($_configuration['live_exercise_tracking']) define('ENABLED_LIVE_EXERCISE_TRACKING',1);

if($_configuration['live_exercise_tracking'] == true && $exerciseType == 1){
	$_configuration['live_exercise_tracking'] = false;
}

// set admin name as person who sends the results e-mail (lacks policy about whom should really send the results)
$main_user_table = Database :: get_main_table(TABLE_MAIN_USER);
$main_admin_table = Database :: get_main_table(TABLE_MAIN_ADMIN);
$courseName = $_SESSION['_course']['name'];
$query = "SELECT user_id FROM $main_admin_table LIMIT 1"; //get all admins from admin table
$admin_id = Database::result(api_sql_query($query),0,"user_id");
$uinfo = api_get_user_info($admin_id);
$from = $uinfo['mail'];
$from_name = $uinfo['firstname'].' '.$uinfo['lastname'];
$str = $_SERVER['REQUEST_URI'];
$url = api_get_path(WEB_CODE_PATH).'exercice/exercice.php?'.api_get_cidreq().'&show=result';

 // if the above variables are empty or incorrect, we don't have any result to show, so stop the script
if(!is_array($exerciseResult) || !is_array($questionList) || !is_object($objExercise)) {

	header('Location: exercice.php');
	exit();
}

$sql_fb_type='SELECT feedback_type FROM '.$TBL_EXERCICES.' WHERE id ="'.Database::escape_string($objExercise->selectId()).'"';

$res_fb_type=Database::query($sql_fb_type,__FILE__,__LINE__);
$row_fb_type=Database::fetch_row($res_fb_type);
$feedback_type = $row_fb_type[0];

// define basic exercise info to print on screen
$exerciseTitle=$objExercise->selectTitle();
$exerciseDescription=$objExercise->selectDescription();

$gradebook = '';
if (isset($_SESSION['gradebook'])){
	$gradebook=	$_SESSION['gradebook'];
}

if (!empty($gradebook) && $gradebook=='view') {
	$interbreadcrumb[]= array (
			'url' => '../gradebook/'.$_SESSION['gradebook_dest'],
			'name' => get_lang('Gradebook')
		);
}

$nameTools=get_lang('Exercice');

if($origin=='user_course') {
	$interbreadcrumb[] = array ("url" => "../user/user.php?cidReq=".Security::remove_XSS($_GET['course']), "name" => get_lang("Users"));
	$interbreadcrumb[] = array("url" => "../mySpace/myStudents.php?student=".Security::remove_XSS($_GET['student'])."&course=".$_course['id']."&details=true&origin=".Security::remove_XSS($_GET['origin']) , "name" => get_lang("DetailsStudentInCourse"));
} else if($origin=='tracking_course') {
	$interbreadcrumb[] = array ("url" => "../mySpace/index.php", "name" => get_lang('MySpace'));
 	$interbreadcrumb[] = array ("url" => "../mySpace/myStudents.php?student=".Security::remove_XSS($_GET['student']).'&details=true&origin='.$origin.'&course='.Security::remove_XSS($_GET['cidReq']), "name" => get_lang("DetailsStudentInCourse"));
} else if($origin=='student_progress') {
	$interbreadcrumb[] = array ("url" => "../auth/my_progress.php?id_session".Security::remove_XSS($_GET['id_session'])."&course=".$_cid, "name" => get_lang('MyProgress'));
	unset($_cid);
} else {
	$interbreadcrumb[]=array("url" => "exercice.php?gradebook=$gradebook","name" => get_lang('Exercices'));
	$this_section=SECTION_COURSES;
}

if ($origin != 'learnpath') {
        if ($certif_available) {
            $htmlHeadXtra[] = '<link type="css" rel="stylesheet" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/colorbox/colorbox.css" />';
            $htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/colorbox/jquery.colorbox.js" language="javascript"></script>';
        }
	Display::display_tool_header($nameTools,"Exercise");
} else {
    // If the quiz is into modules then we must load jquery library
        $htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-1.4.2.min.js" language="javascript"></script>';
	Display::display_reduced_header();
}

if ($objExercise->results_disabled) {
	ob_start();
}
?>
<style type="text/css">
<!--
#comments {
	position:absolute;
	left:795px;
	top:0px;
	width:200px;
	height:75px;
	z-index:1;
}

-->
</style>
<script language="javascript">
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

/*
FUNCTIONS
*/
/**
 * This function gets the comments of an exercise
 *
 * @param int $id
 * @param int $question_id
 * @return str the comment
 */
function get_comments($id,$question_id)
{
	global $TBL_TRACK_ATTEMPT;
	$sql = "SELECT teacher_comment FROM ".$TBL_TRACK_ATTEMPT." where exe_id='".Database::escape_string($id)."' and question_id = '".Database::escape_string($question_id)."' ORDER by question_id";
	$sqlres = api_sql_query($sql, __FILE__, __LINE__);
	$comm = Database::result($sqlres,0,"teacher_comment");
	return $comm;
}
/**
 * Display the answers to a multiple choice question
 *
 * @param integer Answer type
 * @param integer Student choice
 * @param string  Textual answer
 * @param string  Comment on answer
 * @param string  Correct answer comment
 * @param integer Exercise ID
 * @param integer Question ID
 * @param boolean Whether to show the answer comment or not
 * @return void
 */
function display_unique_or_multiple_or_reasoning_answer($answerType, $studentChoice, $answer, $answerComment, $answerCorrect, $id, $questionId, $ans)
{
	if($answerType == UNIQUE_ANSWER){
		$img = 'radio';
	}else {
		$img = 'checkbox';
	}
	if($studentChoice){
		$your_choice = $img.'_on'.'.gif';
	}else {
		$your_choice = $img.'_off'.'.gif';
	}
	if($answerCorrect){
		$expected_choice = $img.'_on'.'.gif';
	}else {
		$expected_choice = $img.'_off'.'.gif';
	}
	$s .= '
	<tr>
	<td width="5%" align="center">
		<img src="../img/'.$your_choice.'"
		border="0" alt="" />
	</td>
	<td width="5%" align="center">
		<img src="../img/'.$expected_choice.'"
		border="0" alt=" " />
	</td>
	<td width="40%" style="border-bottom: 1px solid #4171B5;">'.api_parse_tex($answer).'
	</td>
	</tr>';
	return $s;
}
/**
 * Shows the answer to a free-answer question, as HTML
 * @param string    Answer text
 * @param int       Exercise ID
 * @param int       Question ID
 * @return void
 */
function display_free_answer($answer,$id,$questionId) {
	$s .='<tr><td>'.nl2br(Security::remove_XSS($answer,COURSEMANAGERLOWSECURITY)).'</td>';
        if(!api_is_allowed_to_edit()) {
        $s .='<td>';
        $comm = get_comments($id,$questionId);
        $s .= $comm;
        $s .= '</td>';
        }
        $s .='</tr>';
        return $s;
}
/*
==============================================================================
		MAIN CODE
==============================================================================
*/
// I'm in a preview mode as course admin. Display the action menu.
if ($origin != 'learnpath') {
    echo '<div class="actions">';
    if (api_is_course_admin() && $GLOBALS['learner_view'] == false) {
        echo '<a href="exercice.php?'.api_get_cidreq().'&exerciseId='.$objExercise->id.'">'.Display::return_icon('pixel.gif', get_lang('GoBackToEx'),array('class'=>'toolactionplaceholdericon toolactionback')).get_lang('GoBackToEx'). '</a>';
	echo '<a href="exercice_scenario.php?scenario=yes&modifyExercise=yes&' . api_get_cidreq() . '&exerciseId='.$objExercise->id.'">' . Display :: return_icon('pixel.gif', get_lang('Scenario'),array('class'=>'toolactionplaceholdericon toolactionscenario')) . get_lang('Scenario') . '</a>';
    } else {
        echo '<a href="'.api_get_path(WEB_CODE_PATH).'exercice/exercice.php?'.api_get_cidreq().'">' . Display::return_icon('pixel.gif', get_lang('GoBackToEx'), array('class' => 'toolactionplaceholdericon toolactionback')) . get_lang('GoBackToEx').'</a>';
    }
    echo '</div>';
}
?>
 <div id="content">
<?php
if(isset($_SESSION["display_normal_message"])){
    Display :: display_normal_message($_SESSION["display_normal_message"],false,true);
    unset($_SESSION["display_normal_message"]);
}
?>
<?php
echo '<div class="actions"><table width="100%">';
$exerciseTitle=api_parse_tex($exerciseTitle);
$user_id=api_get_user_id();
$course_code = api_get_course_id();
$status_info=CourseManager::get_user_in_course_status($user_id,$course_code);
			if (STUDENT==$status_info) {
				$user_info=api_get_user_info($user_id);
				$user_name =  $user_info['firstName'].' '.$user_info['lastName'];
			} elseif(COURSEMANAGER==$status_info && !isset($_GET['user'])) {
				$user_info=api_get_user_info($user_id);
				$user_name =  $user_info['firstName'].' '.$user_info['lastName'];
			} else {
				echo $user_name;
			}

//show exercise title
?>
	<?php if($origin != 'learnpath') {
		echo '<tr><td width="10%" align="right"><b>'.get_lang('CourseTitle').'</b> :</td><td>'.api_get_course_id().'</td></tr>';
		echo '<tr><td width="10%" align="right"><b>'.get_lang('User').'</b> :</td><td>'.$user_name.'</td></tr>';
		echo '<tr><td width="10%" align="right"><b>'.get_lang('Exercise').'</b> :</td><td>'.$exerciseTitle.'<br/>'.$exerciseDescription.'</td></tr>';
	} ?>
	</table></div><br/>
	<form method="get" action="exercice.php">
	<input type="hidden" name="origin" value="<?php echo $origin; ?>" />
    <input type="hidden" name="learnpath_id" value="<?php echo $learnpath_id; ?>" />
    <input type="hidden" name="learnpath_item_id" value="<?php echo $learnpath_item_id; ?>" />

<?php

$i=$totalScore=$totalWeighting=$totalScoreMA = 0;
if($debug>0){echo "ExerciseResult: "; var_dump($exerciseResult); echo "QuestionList: ";var_dump($questionList);}

if ($_configuration['tracking_enabled']) {
	// Create an empty exercise
	$exeId= create_event_exercice($objExercise->selectId());
}
$counter=0;
$k=0;

// Loop over all question to show results for each of them, one by one
foreach ($questionList as $questionId) {
	$counter++;
        $k++;
	// gets the student choice for this question
	$choice=$exerciseResult[$questionId];
	// creates a temporary Question object
	$objQuestionTmp = Question :: read($questionId);
	// initialize question information
	$questionName=$objQuestionTmp->selectTitle();
	$questionDescription=$objQuestionTmp->selectDescription();
	$questionWeighting=$objQuestionTmp->selectWeighting();
	$answerType=$objQuestionTmp->selectType();
	$quesId =$objQuestionTmp->selectId(); //added by priya saini
        $mediaPosition = $objQuestionTmp->selectMediaPosition();

	// destruction of the Question object
	unset($objQuestionTmp);

	// decide how many columns we want to use to show the results of each type
	if($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER || $answerType == REASONING) {
		$colspan=4;
	} elseif($answerType == MATCHING || $answerType == FREE_ANSWER) {
		$colspan=2;
	} elseif($answerType == HOT_SPOT || $answerType == HOT_SPOT_DELINEATION) {
		$colspan=4;
		$rowspan=$nbrAnswers+1;
	} else {
		$colspan=1;
	}?>
        <div style="padding:0px 0px 20px 0px;"><div class="rounded" style="width: 100%; padding: 1px; background-color:#fff;border:1px solid #ccc;"><table class="rounded_inner" style="width: 100%; background-color:#fff;"><tr><td>
        <div id="question_title" class="quiz_report_content">
        <?php echo get_lang("Question").' '.($counter).' : <p>'.$questionName;'</p>' ?>
        </div>
        <?php
        // Define for default a position for the media image of each question type */
        $s = '';
        if(!empty($questionDescription)){
            if($mediaPosition == 'top'){
            $s .= '<div align="center"><div class="quiz_content_actions" style="width:40%;">'.$questionDescription.'</div></div>';
            }elseif($mediaPosition == 'right'){
            $s .= '<div class="quiz_content_actions" style="width:40%;float:right">'.$questionDescription.'</div>';
            }
        }
        // Action to realize for each answer type
        if ($answerType == UNIQUE_ANSWER) {
            $feedback_if_true = $feedback_if_false = '';
            if($mediaPosition == 'top' || $mediaPosition == 'nomedia' || empty($questionDescription)){
            $s .= '<div class="quiz_content_actions" style="width:95%;float:left;">';
            }elseif($mediaPosition == 'right'){
            $s .= '<div class="quiz_content_actions" style="width:52%;float:left;height:auto;min-height:300px;">';
            }
            $s .= '<table width="100%" border="0" class="data_table"><tr class="row_odd"><td>'.get_lang("Choice").'</td><td>'.get_lang("ExpectedChoice").'</td><td>'.get_lang("Answer").'</td></tr>';

            $objAnswerTmp=new Answer($questionId);
            $nbrAnswers=$objAnswerTmp->selectNbrAnswers();
            $questionScore=0;
            $correctChoice = 'N';
            $correctComment = array();
            for ($answerId=1;$answerId <= $nbrAnswers;$answerId++) {
                    $answer=$objAnswerTmp->selectAnswer($answerId);
                    $answerComment=$objAnswerTmp->selectComment($answerId);
                    $correctComment[] =$objAnswerTmp->selectComment($answerId);
                    $answerCorrect=$objAnswerTmp->isCorrect($answerId);
                    if ($answerCorrect) {
                        $correct = $answerId;
                    } else {
                        $not_correct = $answerId;
                    }
                    $answerWeighting=$objAnswerTmp->selectWeighting($answerId);
                    $studentChoice=($choice == $answerId)?1:0;
                    if ($studentChoice) {
                            $questionScore+=$answerWeighting;
                            $totalScore+=$answerWeighting;
                            if($studentChoice == $answerCorrect)
                            {
                            $correctChoice = 'Y';
                            $feedback_if_true = $objAnswerTmp->selectComment($answerId);
                            }else{
                                $feedback_if_false = $objAnswerTmp->selectComment($answerId);
                            }
                    }
                    if ($answerId==1) {
                            $s .= display_unique_or_multiple_or_reasoning_answer($answerType, $studentChoice, $answer, $answerComment, $answerCorrect,$id,$questionId,$answerId);
                    } else {
                            $s .= display_unique_or_multiple_or_reasoning_answer($answerType, $studentChoice, $answer, $answerComment, $answerCorrect,$id,$questionId,"");
                    }
                    $i++;



            }

            $s .= '<tr><td colspan="3">&nbsp;</td></tr>';
            if ($correctChoice == 'Y') {
                $feedback_if_true = $objAnswerTmp->selectComment($correct);
                if (empty($feedback_if_true)) {
                        $feedback_if_true = get_lang('NoTrainerComment');
                }
                $s .= '<tr><td colspan="3"><b>' . get_lang('Feedback') . '</b></td></tr><tr><td colspan="3"><span style="font-weight: bold; color: #008000;">' . $feedback_if_true . '</span></td></tr>';
            } else {
                $feedback_if_false = $objAnswerTmp->selectComment($not_correct);
                if (empty($feedback_if_false)) {
                        $feedback_if_false = get_lang('NoTrainerComment');
                }
                $s .= '<tr><td colspan="3"><b>' . get_lang('Feedback') . '</b></td></tr><tr><td colspan="3"><span style="font-weight: bold; color: #FF0000;">' . $feedback_if_false . '</span></td></tr>';
            }
            $s .= '</table></div>';
            echo $s;
            exercise_attempt($questionScore,$choice,$quesId,$exeId,0);
         }elseif ($answerType == MULTIPLE_ANSWER) {
            $feedback_if_true = $feedback_if_false = '';
            if($mediaPosition == 'top' || $mediaPosition == 'nomedia' || empty($questionDescription)){
            $s .= '<div class="quiz_content_actions" style="width:95%;float:left;">';
            }elseif($mediaPosition == 'right'){
            $s .= '<div class="quiz_content_actions" style="width:52%;float:left;height:auto;min-height:350px;">';
            }
            $s .= '<table width="100%" border="0" class="data_table"><tr class="row_odd"><td>'.get_lang("Choice").'</td><td>'.get_lang("ExpectedChoice").'</td><td>'.get_lang("Answer").'</td></tr>';
            // construction of the Answer object
            $objAnswerTmp=new Answer($questionId);
            $nbrAnswers=$objAnswerTmp->selectNbrAnswers();
            $questionScore=0;
            $correctChoice = 'N';
            $answerWrong = 'N';
            $totalScoreMA = 0;
            for ($answerId=1;$answerId <= $nbrAnswers;$answerId++) {
                    $answer=$objAnswerTmp->selectAnswer($answerId);
                    $answerComment=$objAnswerTmp->selectComment($answerId);
                    $correctComment[] =$objAnswerTmp->selectComment($answerId);
                    $answerCorrect=$objAnswerTmp->isCorrect($answerId);
                    $answerWeighting=$objAnswerTmp->selectWeighting($answerId);
                    $studentChoice=$choice[$answerId];
                    if ($studentChoice) {
                            $questionScore+=$answerWeighting;
                            $totalScoreMA+=$answerWeighting;
                            if($studentChoice == $answerCorrect)
                            {
                            $correctChoice = 'Y';
                            $feedback_if_true = $objAnswerTmp->selectComment($answerId);
                            }else{
                            $answerWrong = 'Y';
                            $feedback_if_false = $objAnswerTmp->selectComment($answerId);
                            }
                    }
                    if ($answerId==1) {
                            $s .= display_unique_or_multiple_or_reasoning_answer($answerType, $studentChoice, $answer, $answerComment, $answerCorrect,$id,$questionId,$answerId);
                    } else {
                            $s .= display_unique_or_multiple_or_reasoning_answer($answerType, $studentChoice, $answer, $answerComment, $answerCorrect,$id,$questionId,"");
                    }

                    $i++;
            }
            $s .= '<tr><td colspan="3">&nbsp;</td></tr>';
            if($correctChoice == 'Y' && $answerWrong == 'N') {
                    if (empty($feedback_if_true)) {
                            $feedback_if_true = get_lang('NoTrainerComment');
                    }
                    $s .= '<tr><td colspan="3"><b>' . get_lang('Feedback') . '</b></td></tr><tr><td colspan="3"><span style="font-weight: bold; color: #008000;">' . $feedback_if_true . '</span></td></tr>';
            } else {
                    if (empty($feedback_if_false)) {
                            $feedback_if_false = get_lang('NoTrainerComment');
                    }
                    $s .= '<tr><td colspan="3"><b>' . get_lang('Feedback') . '</b></td></tr><tr><td colspan="3"><span style="font-weight: bold; color: #FF0000;">' . $feedback_if_false . '</span></td></tr>';
            }
            $s .= '</table></div>';
            echo $s;
            if($totalScoreMA>0){
                $totalScore+=$totalScoreMA;
            }
                $totalScoreMA=0;

           if ($choice != 0) {
                $reply = array_keys($choice);
                for ($i=0;$i<sizeof($reply);$i++) {
                    $ans = $reply[$i];
                    exercise_attempt($questionScore,$ans,$quesId,$exeId,$i);
                }
            } else {
                   exercise_attempt($questionScore, 0 ,$quesId,$exeId,0);
            }
	}elseif ($answerType == REASONING) {
                $feedback_if_true = $feedback_if_false = '';
                if($mediaPosition == 'top' || $mediaPosition == 'nomedia' || empty($questionDescription)){
                $s .= '<div class="quiz_content_actions" style="width:95%;float:left;">';
                }
                elseif($mediaPosition == 'right'){
                $s .= '<div class="quiz_content_actions" style="width:52%;float:left;">';
                }
                $s .= '<table width="100%" border="0" class="data_table"><tr class="row_odd"><td>'.get_lang("Choice").'</td><td>'.get_lang("ExpectedChoice").'</td><td>'.get_lang("Answer").'</td></tr>';
                // construction of the Answer object
                $objAnswerTmp=new Answer($questionId);
                $nbrAnswers=$objAnswerTmp->selectNbrAnswers();
                $questionScore=0;
                $correctChoice = 'Y';
                $noStudentChoice='N';
                $answerWrong = 'N';
                for ($answerId=1;$answerId <= $nbrAnswers;$answerId++) {
                        $answer=$objAnswerTmp->selectAnswer($answerId);
                        $answerComment=$objAnswerTmp->selectComment($answerId);
                        $correctComment[] =$objAnswerTmp->selectComment($answerId);
                        $answerCorrect=$objAnswerTmp->isCorrect($answerId);
                        $answerWeighting=$objAnswerTmp->selectWeighting($answerId);
                        $studentChoice=$choice[$answerId];
                        if($answerCorrect)
                        {
                                $feedback_if_true = $objAnswerTmp->selectComment($answerId);
                        }else{
                                $feedback_if_false = $objAnswerTmp->selectComment($answerId);
                        }
                        if($answerId == '2')
                        {
                                $wrongAnswerWeighting = $answerWeighting;
                        }
                        if($answerCorrect && $studentChoice == '1' && $correctChoice == 'Y')
                        {
                                $correctChoice = 'Y';
                                $noStudentChoice = 'Y';
                        }elseif($answerCorrect && !$studentChoice){
                                $correctChoice = 'N';
                                $noStudentChoice = 'Y';
                                $answerWrong = 'Y';
                        }elseif(!$answerCorrect && $studentChoice == '1'){
                                $correctChoice = 'N';
                                $noStudentChoice = 'Y';
                                $answerWrong = 'Y';
                        }
                        if ($answerId==1) {
                                $s .= display_unique_or_multiple_or_reasoning_answer($answerType, $studentChoice, $answer, $answerComment, $answerCorrect,$id,$questionId,$answerId);
                        } else {
                                $s .= display_unique_or_multiple_or_reasoning_answer($answerType, $studentChoice, $answer, $answerComment, $answerCorrect,$id,$questionId,"");
                        }
                        $i++;
                }
                if ($answerType == REASONING  && $noStudentChoice == 'Y'){
                    if($correctChoice == 'Y')
                    {
                    $questionScore += $questionWeighting;
                    $totalScore += $questionWeighting;
                    }else{
                    $questionScore += $wrongAnswerWeighting;
                    $totalScore += $wrongAnswerWeighting;
                    }
                }
                $s .= '<tr><td colspan="3">&nbsp;</td></tr>';
                if ($correctChoice == 'Y' && $answerWrong == 'N') {
                        if (empty($feedback_if_true)) {
                                $feedback_if_true = get_lang('NoTrainerComment');
                        }
                        $s .= '<tr><td colspan="3"><b>' . get_lang('Feedback') . '</b></td></tr><tr><td colspan="3"><span style="font-weight: bold; color: #008000;">' . $feedback_if_true . '</span></td></tr>';
                } else {
                        if (empty($feedback_if_false)) {
                                $feedback_if_false = get_lang('NoTrainerComment');
                        }
                        $s .= '<tr><td colspan="3"><b>' . get_lang('Feedback') . '</b></td></tr><tr><td colspan="3"><span style="font-weight: bold; color: #FF0000;">' . $feedback_if_false . '</span></td></tr>';
                }
                $s .= '</table></div>';
                echo $s;
                if ($choice != 0) {
                    $reply = array_keys($choice);
                    for ($i = 0; $i < sizeof($reply); $i++) {
                        $ans = $reply[$i];
                        exercise_attempt($questionScore, $ans, $quesId, $exeId, $i);
                    }
                } else {
                       exercise_attempt($questionScore, 0, $quesId, $exeId, 0);
                }

	}elseif ($answerType == FILL_IN_BLANKS) {
                $feedback_if_true = $feedback_if_false = '';
                $objAnswerTmp=new Answer($questionId);
                $nbrAnswers=$objAnswerTmp->selectNbrAnswers();
                $questionScore=0;
                $feedback_data = unserialize($objAnswerTmp -> comment[1]);
                $feedback_true = $feedback_data['comment[1]'];
                $feedback_false = $feedback_data['comment[2]'];
                for ($answerId=1;$answerId <= $nbrAnswers;$answerId++) {
                        $answer = $objAnswerTmp->selectAnswer($answerId);
                        $answerComment = $objAnswerTmp->selectComment($answerId);
                        $answerCorrect = $objAnswerTmp->isCorrect($answerId);
                        $answerWeighting = $objAnswerTmp->selectWeighting($answerId);

                    // the question is encoded like this
                    // [A] B [C] D [E] F::10,10,10@1
                    // number 1 before the "@" means that is a switchable fill in blank question
                    // [A] B [C] D [E] F::10,10,10@ or  [A] B [C] D [E] F::10,10,10
                    // means that is a normal fill blank question

                        // first we explode the "::"
                        $pre_array = explode('::', $answer);

                        // is switchable fill blank or not
                        $is_set_switchable = explode('@', $pre_array[1]);
                        $switchable_answer_set=false;
                        if ($is_set_switchable[1]==1) {
                                $switchable_answer_set=true;
                        }

                        $answer = $pre_array[0];
                        $full_answer = $answer;
                        // splits weightings that are joined with a comma
                        $answerWeighting = explode(',',$is_set_switchable[0]);
                        // we save the answer because it will be modified
                        $temp=$answer;
                        // TeX parsing
                        // 1. find everything between the [tex] and [/tex] tags
                        $startlocations=api_strpos($temp,'[tex]');
                        $endlocations=api_strpos($temp,'[/tex]');
                        if ($startlocations !== false && $endlocations !== false) {
                                $texstring=api_substr($temp,$startlocations,$endlocations-$startlocations+6);
                                // 2. replace this by {texcode}
                                $temp=str_replace($texstring,'{texcode}',$temp);
                        }
                        $j=0;
                        //initialise answer tags
                        $user_tags=array();
                        $correct_tags=array();
                        $real_text=array();
                        // the loop will stop at the end of the text
                        while(1)
                        {
                                // quits the loop if there are no more blanks (detect '[')
                                if(($pos = api_strpos($temp,'[')) === false)
                                {
                                        // adds the end of the textsolution
                                        $answer.=$temp;
                                        // TeX parsing - replacement of texcode tags
                                        $texstring = api_parse_tex($texstring);
                                        break; //no more "blanks", quit the loop
                                }
                                // adds the piece of text that is before the blank
                                //and ends with '[' into a general storage array
                                $real_text[]=api_substr($temp,0,$pos+1);
                                $answer.=api_substr($temp,0,$pos+1);
                                //take the string remaining (after the last "[" we found)
                                $temp=api_substr($temp,$pos+1);
                                // quit the loop if there are no more blanks, and update $pos to the position of next ']'
                                if(($pos = api_strpos($temp,']')) === false)
                                {
                                        // adds the end of the text
                                        $answer.=$temp;
                                        break;
                                }
                                $choice[$j]=trim($choice[$j]);
                                $user_tags[]=api_strtolower($choice[$j]);
                                //put the contents of the [] answer tag into correct_tags[]
                                $correct_tags[]=api_strtolower(api_substr($temp,0,$pos));
                                $j++;
                                $temp=api_substr($temp,$pos+1);
                        }

                                $answer='';
                                $real_correct_tags = $correct_tags;
                                //$full_answer = $answer; echo $full_answer;
                                for($i=0;$i<sizeof($real_correct_tags);$i++) {
                                        $new_tag='';
                                        if ($correct_tags[$i]==$user_tags[$i]) {
                                            $new_tag = ' [ <font color="green"> '.$user_tags[$i].'</font> ';
                                            $new_tag.= '/ <font color="green">'.$correct_tags[$i].'</font> ]';
                                            $full_answer = str_replace('['.$correct_tags[$i].']',$new_tag,$full_answer);
                                        }else{
                                            $new_tag = ' [ <font color="red"><s>'.$user_tags[$i].'</s></font>';
                                            $new_tag.= '/ <font color="green">'.$correct_tags[$i].'</font> ]';
                                            $full_answer = str_replace('['.$correct_tags[$i].']',$new_tag,$full_answer);
                                        }
                                }
                                if($mediaPosition == 'top' || $mediaPosition == 'nomedia' || empty($questionDescription)){
                                $s .= '<div class="quiz_content_actions" style="width:95%;float:left;">';
                                }elseif($mediaPosition == 'right'){
                                $s .= '<div class="quiz_content_actions" style="width:52%;float:left;height:auto;min-height:300px;">';
                                }
                                $s .= '<div class="scroll_feedback"><b>'.$full_answer. '</b></div>';
                                $s .= '<table width="100%" border="0"><tr><td colspan="3"><b>'.get_lang('Feedback').'</b></td></tr>';
                                for($i=0;$i<sizeof($real_correct_tags);$i++) {
                                    $s .= '<tr><td>' . $user_tags[$i] . ' / ' . $real_correct_tags[$i] . '</td>';
                                    if ($correct_tags[$i]==$user_tags[$i]) {
                                            // gives the related weighting to the student
                                            $questionScore+=$answerWeighting[$i];
                                            // increments total score
                                            $totalScore+=$answerWeighting[$i];
                                            $s .= '<td><img src="../img/Right32tr.png" style="vertical-align:middle;">&nbsp;' . get_lang('Right') . '</td><td><div class="feedback-right"><span style="font-weight: bold; color: #008000;">' . $feedback_true . '</span></div></td></tr>';
                                    } else {
                                            $s.= '<td><img src="../img/Wrong32tr.png" style="vertical-align:middle;">&nbsp;' . get_lang('Wrong') . '</td><td><div class="feedback-wrong"><span style="font-weight: bold; color: #FF0000;">' . $feedback_false . '</span></div></td></tr>';
                                    }

                                }
                                    $s.=  '</table></div>';
                                    $i++;
                    }
                    echo $s;
                    exercise_attempt($questionScore, $answer, $quesId, $exeId, 0);
            }elseif ($answerType == FREE_ANSWER) {
                    $feedback_if_true = $feedback_if_false = '';
                    if($mediaPosition == 'top' || $mediaPosition == 'nomedia' || empty($questionDescription)){
                    $s .= '<div class="quiz_content_actions" style="width:95%;float:left;">';
                    }elseif($mediaPosition == 'right'){
                    $s .= '<div class="quiz_content_actions" style="width:52%;float:left;height:auto;min-height:300px;">';
                    }
                    $s .= '<table border="0" cellspacing="3" cellpadding="3" align="center" class="feedback_actions" style="width:98%;">';
                    $s .= '<tr><td></td></tr>';
                    $s .= '<tr><td><i>'.get_lang('Answer').'</i></td></tr>';
                    $s .= '<tr><td></td></tr>';
                    $objAnswerTmp = new Answer($questionId);
                    $nbrAnswers = $objAnswerTmp->selectNbrAnswers();
                    $questionScore = 0;
                    if ($questionScore==-1) {
                            $totalScore+=0;
                    } else {
                            $totalScore+=$questionScore;
                    }
                    $s .= '<tr><td valign="top">'.display_free_answer($choice, id, $questionId).'</td></tr>';
                    $s .= '<tr><td valign="top">'.get_lang('notCorrectedYet').'</td></tr>';
                    $s .= '<tr><td></td></tr>';
                    $s .= '</table>';
                    echo $s;
                    $answer = $choice;
                    exercise_attempt($questionScore, $answer, $quesId, $exeId, 0);

            }else if ($answerType == MATCHING) {
                $feedback_if_true = $feedback_if_false = '';

                $objAnswerTmp=new Answer($questionId);
                $nbrAnswers=$objAnswerTmp->selectNbrAnswers();
                $answerComment_true=$objAnswerTmp->selectComment(1);
                $answerComment_false=$objAnswerTmp->selectComment(2);
                $table_ans = Database :: get_course_table(TABLE_QUIZ_ANSWER);
                $TBL_TRACK_ATTEMPT= Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);
                $answer_ok = 'N';
                $answer_wrong = 'N';
                $sql_select_answer = 'SELECT id, answer, correct, position FROM '.$table_ans.' WHERE question_id="'.Database::escape_string($questionId).'" AND correct<>0';
                $sql_answer = 'SELECT position, answer FROM '.$table_ans.' WHERE question_id="'.Database::escape_string($questionId).'" AND correct=0';
                $res_answer = api_sql_query($sql_answer, __FILE__, __LINE__);
                    // getting the real answer
                    $real_list =array();
                    while ($real_answer = Database::fetch_array($res_answer)) {
                            $real_list[$real_answer['position']]= $real_answer['answer'];
                    }
                    $res_answers = api_sql_query($sql_select_answer, __FILE__, __LINE__);

                    $s .= '<table cellspacing="0" cellpadding="0" align="center" class="feedback_actions fa_2">';
                    $s .= '<thead>';
                    $s .= '<tr>
                            <th align="center" width="30%"><span style="font-style: italic;color:#4171B5;font-weight:bold;">' . get_lang("ElementList") . '</span> </th>
                            <th align="center" width="35%"><span style="font-style: italic;color:#4171B5;font-weight:bold;">' . get_lang("YourAnswers") . '</span></th>
                            <th align="center" width="35%"><span style="font-style: italic;color:#4171B5;font-weight:bold;">' . get_lang("Correct") . '</span></th>
                          </tr>';
                    $s .= '</thead>';

                    while ($a_answers = Database::fetch_array($res_answers)) {

				$i_answer_id = $a_answers['id'];
				$s_answer_label = $a_answers['answer'];
				$i_answer_correct_answer = $a_answers['correct'];
				$i_answer_position = $a_answers['position'];
                                $s_user_answer = $choice[$choice[$i_answer_id]];
				$s_correct_answer = $real_list[$i_answer_correct_answer];
                                $answerCorrect=$objAnswerTmp->isCorrect($i_answer_id);
				$i_answerWeighting=$objAnswerTmp->selectWeighting($i_answer_id);
                                if($answerCorrect == $choice[$i_answer_position]){
					$questionScore+=$i_answerWeighting;
					$totalScore+=$i_answerWeighting;
					if($answer_wrong == 'N')
					{
						$answer_ok = 'Y';
					}
                                        $s_user_answer = '<span style="color: green;">'.$s_user_answer.'</span>';
				} else {
					$s_user_answer = '<span style="color: #FF0000; text-decoration: line-through;">'.$s_user_answer.'</span>';
					$answer_wrong = 'Y';
				}
				if($questionScore > 20)
				{
					$questionScore = round($questionScore);
				}
				$s .= '<tr>';
				$s .= '<td align="center"><div id="matchresult">'.$s_answer_label.'</div></td>';
                                $s .= '<td align="center" width="30%"><div id="matchresult">'.$s_user_answer.'</div></td>';
                                $s .= '<td align="center"><div id="matchresult"><b><span>'.$s_correct_answer.'</span></b></div></td>';
				$s .= '</tr>';
			}
                    $s .= '<tfoot>';
                    $s .= '<tr ><td colspan="3"><b>' . get_lang('Feedback') . '</b></td></tr><tr>';
                    if ($answer_ok == 'Y' && $answer_wrong == 'N') {
                        $s .= '<td colspan="3" style="border-top: none; padding-top: 0px;"><span style="font-weight: bold; color: #008000;">' . $answerComment_true . '</span></td>';
                    } else {
                        $s .= '<td colspan="3" style="border-top: none; padding-top: 0px;"><span style="font-weight: bold; color: #FF0000;">' . $answerComment_false . '</span></td>';
                    }
                    $s .= '</tr></tfoot></table>';
                    echo $s;
                    exercise_attempt($questionScore,$answer,$quesId,$exeId,0);
            }elseif ($answerType == HOT_SPOT) {
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

			$s .= '<table width="100%" border="0"><tr><td><div align="center"><object type="application/x-shockwave-flash" data="../plugin/hotspot/hotspot_solution.swf?modifyAnswers=' . Security::remove_XSS($questionId) . '&exe_id=' .$objExercise->id.'&from_db=0" width="610" height="410">
				<param name="movie" value="../plugin/hotspot/hotspot_solution.swf?modifyAnswers=' . Security::remove_XSS($questionId) . '&exe_id=' .$objExercise->id. '&from_db=0" />zz
			  </object></div></td><td width="40%" valign="top"><div class="quiz_content_actions" style="height:380px;"><div class="quiz_header">'.get_lang('Feedback').'</div><div align="center"><img src="../img/MouseHotspots64.png"></div><br/>';

			 $s .= '<div><table width="90%" border="1" class="data_table">';
			 for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
				$answer = $objAnswerTmp->selectAnswer($answerId);
				$answerComment = $objAnswerTmp->selectComment($answerId);
				$correctComment[] = $objAnswerTmp->selectComment($answerId);
				$answerCorrect = $objAnswerTmp->isCorrect($answerId);
                                $answerWeighting = $objAnswerTmp->selectWeighting($answerId);
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
				if ($answerCorrect = $choice[$answerId]) {
					$answerOk = 'Y';
					$img_choice = get_lang('Right');
                                        $questionScore+=$answerWeighting;

				} else {
					$answerOk = 'N';
					$answerWrong = 'Y';
					$img_choice = get_lang('Wrong');
				}
				$s .= '<tr><td><div style="height:11px; width:11px; background-color:'.$hotspot_colors[$answerId].'; display:inline; float:left; margin-top:3px;"></div>&nbsp;'.$answerId.'</td><td>'.$answer.'</td><td>'.$img_choice.'</td></tr>';
			 }
                         $totalScore+=$questionScore;
			 $s .= '</table></div><br/><br/>';
			 if ($answerOk == 'Y' && $answerWrong == 'N') {
				 if ($nbrAnswers == 1){
					 $feedback = '<span style="font-weight: bold; color: #008000;">'.$correctComment[0].'</span>';
				 }
				 else {
					 $feedback = '<span style="font-weight: bold; color: #FF0000;">'.$correctComment[1].'</span>';
				 }
			 }
			 else
			 {
				 if ($nbrAnswers == 1){
					 $feedback = '<span style="font-weight: bold; color: #008000;">'.$correctComment[1].'</span>';
				 }
				 else {
					 $feedback = '<span style="font-weight: bold; color: #FF0000;">'.$correctComment[2].'</span>';
				 }
			 }
			 if(!empty($feedback)){
			 $s .= '<div align="center" class="quiz_feedback"><b>'.get_lang('Feedback').'</b> : '.$feedback.'</div>';
			 }
			 $s .= '</div></td></tr></table>';
			 echo $s;
			 exercise_attempt($questionScore, $answer, $quesId, $exeId, 0);
			 if (is_array($exerciseResultCoordinates[$quesId])) {
				foreach($exerciseResultCoordinates[$quesId] as $idx => $val) {
                                        exercise_attempt_hotspot($exeId,$quesId,$idx,$choice[$idx],$val);
				}
			}

          }else if($answerType == HOT_SPOT_DELINEATION) {
                $feedback_if_true = $feedback_if_false = '';
		$objAnswerTmp=new Answer($questionId);
		$nbrAnswers=$objAnswerTmp->selectNbrAnswers();
		//$nbrAnswers=1; // based in the code found in exercise_show.php
		$questionScore=0;
		$totalScoreHotDel= 0;
		//based on exercise_submit modal
		/*  Hot spot delinetion parameters */
		$destination=array();
		$comment='';
		$next=1;
		$_SESSION['hotspot_coord']=array();
		$_SESSION['hotspot_dest']=array();
		$overlap_color=$missing_color=$excess_color=false;
		$organs_at_risk_hit=0;
		$final_answer = 0;
				for($answerId=1;$answerId <= $nbrAnswers;$answerId++) {

					$answer		=$objAnswerTmp->selectAnswer($answerId);
					$answerComment	=$objAnswerTmp->selectComment($answerId);
					$answerCorrect	=$objAnswerTmp->isCorrect($answerId);
					$answerWeighting=$objAnswerTmp->selectWeighting($answerId);

					//delineation
					$answer_delineation_destination=$objAnswerTmp->selectDestination(1);
					$delineation_cord=$objAnswerTmp->selectHotspotCoordinates(1);

					if ($answerId===1) {
						$_SESSION['hotspot_coord'][1]=$delineation_cord;
						$_SESSION['hotspot_dest'][1]=$answer_delineation_destination;
					}

                                        $user_answer = $exerciseResultCoordinates[$quesId];
					$totalScoreHotDel=$questionScore;

					// THIS is very important otherwise the poly_compile will throw an error!!
					// round-up the coordinates
					$coords = explode('/',$user_answer);
					$user_array = '';
					foreach ($coords as $coord) {
					    list($x,$y) = explode(';',$coord);
					    $user_array .= round($x).';'.round($y).'/';
					}
					$user_array = substr($user_array,0,-1);

					if ($next) {
	 					$user_answer = $user_array;
						// we compare only the delineation not the other points
						$answer_question	= $_SESSION['hotspot_coord'][1];
						$answerDestination	= $_SESSION['hotspot_dest'][1];

						//calculating the area
                                                $poly_user 			= convert_coordinates($user_answer,'/');
                                                $poly_answer		= convert_coordinates($answer_question,'|');
                                                $max_coord 			= array('x'=>600,'y'=>400);//poly_get_max($poly_user,$poly_answer);
                                                $poly_user_compiled = poly_compile($poly_user,$max_coord);
                                                $poly_answer_compiled = poly_compile($poly_answer,$max_coord);
                                                $poly_results 		= poly_result($poly_answer_compiled,$poly_user_compiled,$max_coord);

                                                $overlap = $poly_results['both'];
                                                $poly_answer_area = $poly_results['s1'];
                                                $poly_user_area = $poly_results['s2'];
                                                $missing = $poly_results['s1Only'];
                                                $excess = $poly_results['s2Only'];

                                                //$overlap = round(polygons_overlap($poly_answer,$poly_user)); //this is an area in pixels
                                                if ($dbg_local>0) { error_log(__LINE__.' - Polygons results are '.print_r($poly_results,1),0);}
                                                if ($overlap < 1) {
                                                    //shortcut to avoid complicated calculations
                                                    $final_overlap = 0;
                                                    $final_missing = 100;
                                                    $final_excess = 100;
                                                } else {
                                                    // the final overlap is the percentage of the initial polygon that is overlapped by the user's polygon
                                                        $final_overlap = round(((float)$overlap / (float)$poly_answer_area)*100);
                                                    if ($dbg_local>1) { error_log(__LINE__.' - Final overlap is '.$final_overlap,0);}
                                                    // the final missing area is the percentage of the initial polygon that is not overlapped by the user's polygon
                                                    $final_missing = 100 - $final_overlap;
                                                    if ($dbg_local>1) { error_log(__LINE__.' - Final missing is '.$final_missing,0);}
                                                    // the final excess area is the percentage of the initial polygon's size that is covered by the user's polygon outside of the initial polygon
                                                    $final_excess = round((((float)$poly_user_area-(float)$overlap)/(float)$poly_answer_area)*100);
                                                    if ($dbg_local>1) { error_log(__LINE__.' - Final excess is '.$final_excess,0);}
                                                }

                                                //checking the destination parameters parsing the "@@"
                                                $destination_items= explode('@@', $answerDestination);
                                                $threadhold_total = $destination_items[0];
                                                $threadhold_items=explode(';',$threadhold_total);
                                                $threadhold1 = $threadhold_items[0]; // overlap
                                                $threadhold2 = $threadhold_items[1]; // excess
                                                $threadhold3 = $threadhold_items[2];	 //missing

						// if is delineation
						if ($answerId===1) {
                                                    //setting colors
                                                    if ($final_overlap>=$threadhold1) {
                                                            $overlap_color=true; //echo 'a';
                                                    }
                                                    //echo $excess.'-'.$threadhold2;
                                                    if ($final_excess<=$threadhold2) {
                                                            $excess_color=true; //echo 'b';
                                                    }
                                                    //echo '--------'.$missing.'-'.$threadhold3;
                                                    if ($final_missing<=$threadhold3) {
                                                            $missing_color=true; //echo 'c';
                                                    }

                                                    // if pass
                                                    if ($final_overlap>=$threadhold1 && $final_missing<=$threadhold3 && $final_excess<=$threadhold2) {
                                                            $next=1; //go to the oars
                                                            $result_comment=get_lang('Acceptable');
                                                            $final_answer = 1;	// do not update with  update_exercise_attempt
                                                            $comment = '<span style="font-weight: bold; color: #008000;">'.$answerDestination=$objAnswerTmp->selectComment(1).'</span>';
                                                    } else {
                                                            $next=1; //Go to the oars. If $next =  0 we will show this message: "One (or more) area at risk has been hit" instead of the table resume with the results
                                                            $result_comment=get_lang('Unacceptable');
                                                            $comment = '<span style="font-weight: bold; color: #FF0000;">'.$answerDestination=$objAnswerTmp->selectComment(2).'</span>';
                                                            $answerDestination=$objAnswerTmp->selectDestination(1);
                                                            //checking the destination parameters parsing the "@@"
                                                            $destination_items= explode('@@', $answerDestination);
                                                    }
						} elseif($answerId>1) {
                                                    if ($objAnswerTmp->selectHotspotType($answerId) == 'noerror') {
                                                        if ($dbg_local>0) { error_log(__LINE__.' - answerId is of type noerror',0);}
                                                        //type no error shouldn't be treated
                                                        $next = 1;
                                                        continue;
                                                    }
                                                    if ($dbg_local>0) { error_log(__LINE__.' - answerId is >1 so we\'re probably in OAR',0);}
                                                    $inter= $result['success'];

                                                    $delineation_cord=$objAnswerTmp->selectHotspotCoordinates($answerId);
                                                    $poly_answer 		= convert_coordinates($delineation_cord,'|');
                                                    $max_coord 			= poly_get_max($poly_user,$poly_answer);
                                                    $poly_answer_compiled 	= poly_compile($poly_answer,$max_coord);
                                                    $overlap 			= poly_touch($poly_user_compiled, $poly_answer_compiled,$max_coord);

                                                    if ($overlap == false) {
                                                        //all good, no overlap
                                                        $next = 1;
                                                        continue;
                                                    } else {
                                                        if ($dbg_local>0) { error_log(__LINE__.' - Overlap is '.$overlap.': OAR hit',0);}
                                                        $organs_at_risk_hit++;
                                                        //show the feedback
                                                        $next=0;
                                                        $comment=$answerDestination=$objAnswerTmp->selectComment($answerId);
                                                        $answerDestination=$objAnswerTmp->selectDestination($answerId);
                                                        $destination_items= explode('@@', $answerDestination);
                                                    }
						}
					}
					else
					{	// the first delineation feedback
                        if ($dbg_local>0) { error_log(__LINE__.' first',0);}
					}
				} // end for

		if ($overlap_color) {
			$overlap_color='green';
	    } else {
			$overlap_color='red';
	    }

		if ($missing_color) {
			$missing_color='green';
	    } else {
			$missing_color='red';
	    }
		if ($excess_color) {
			$excess_color='green';
	    } else {
			$excess_color='red';
	    }


	    if (!is_numeric($final_overlap)) {
    	$final_overlap = 0;
	    }

	    if (!is_numeric($final_missing)) {
	    	$final_missing = 0;
	    }
	    if (!is_numeric($final_excess)) {
	    	$final_excess = 0;
	    }

	    if ($final_excess>100) {
	    	$final_excess = 100;
	    }$totalScore+=$totalScoreHotDel;

		if ($answerType!= HOT_SPOT_DELINEATION) {
			$item_list=explode('@@',$destination);
			//print_R($item_list);
			$try = $item_list[0];
			$lp = $item_list[1];
			$destinationid= $item_list[2];
			$url=$item_list[3];
			$table_resume='';
		} else {
			if ($next==0) {
				$try = $try_hotspot;
				$lp = $lp_hotspot;
				$destinationid= $select_question_hotspot;
				$url=$url_hotspot;
			} else {
				//show if no error
				//echo 'no error';
				$answerDestination=$objAnswerTmp->selectDestination($nbrAnswers);
			}
		}

		echo '<table width="100%" border="0">';
		echo '<tr><td><object type="application/x-shockwave-flash" data="../plugin/hotspot/hotspot_solution.swf?modifyAnswers='.$questionId.'&exe_id='.'&from_db=0" width="610" height="410">
						<param name="movie" value="../plugin/hotspot/hotspot_solution.swf?modifyAnswers='.$questionId.'&exe_id='.'&from_db=0" />

					</object></td>';
		echo '<td width="40%" valign="top"><div class="quiz_content_actions" style="height:380px;"><div class="quiz_header">'.get_lang('Feedback').'</div><p align="center"><img src="../img/mousepolygon64.png"></p><div><table width="100%" border="1" class="data_table"><tr class="row_odd"><td>&nbsp;</td><td>'.get_lang('Requirement').'</td><td>'.get_lang('YourContour').'</td></tr><tr class="row_even"><td align="right">'.get_lang('Overlap').'</td><td align="center">'.get_lang('Min').' '.$threadhold1.' %</td><td align="center"><div style="color:'.$overlap_color.'">'.(($final_overlap < 0)?0:intval($final_overlap)).'</div></td></tr><tr class="row_even"><td align="right">'.get_lang('Excess').'</td><td align="center">'.get_lang('Max').' '.$threadhold2.' %</td><td align="center"><div style="color:'.$excess_color.'">'.(($final_excess < 0)?0:intval($final_excess)).'</div></td></tr><tr class="row_even"><td align="right">'.get_lang('Missing').'</td><td align="center">'.get_lang('Max').' '.$threadhold3.' %</td><td align="center"><div style="color:'.$missing_color.'">'.(($final_missing < 0)?0:intval($final_missing)).'</div></td></tr>';

		if ($answerType == HOT_SPOT_DELINEATION) {
			if ($organs_at_risk_hit>0) {
				$message= get_lang('ResultIs').' <b>'.$result_comment.'</b>';
				$message.= '<p style="color:#DC0A0A;"><b>'.get_lang('OARHit').'</b></p>';
			} else {
				$message = '<p>'.get_lang('ResultIs').' <b>'.$result_comment.'</b></p>';
			}

			echo '<tr><td colspan="3" align="center">'.$message.'</td></tr>';

			// by default we assume that the answer is ok but if the final answer after calculating the area in hotspot delineation =0 then update
			if ($final_answer==0) {
				$sql = 'UPDATE '.$TBL_TRACK_ATTEMPT.' SET answer="", marks = 0 WHERE question_id = '.$questionId.' AND exe_id = '.$exeId;
				Database::query($sql, __FILE__, __LINE__);
			}

		} else {
			echo '<tr><td colspan="3">'.$comment.'</td></tr>';
		}
		echo '</table></div><br/><br/>';
		if (!empty($comment)) {
                    echo '<div align="center" class="quiz_feedback"><b>'.get_lang('Feedback').'</b> : '.$comment.'</div>';
		}
		echo '</div></td></tr>';
		echo '</table>';
	}
            ?>
		</td>
		</tr>
                <tr><td></td></tr><tr><td></td></tr><tr><td></td></tr>
                </table>

		<div id="question_score" class="sectiontitle">
		<?php
		$my_total_score  = round(float_format($questionScore,1));
		$my_total_weight = round(float_format($questionWeighting,1));
                if($my_total_score<0){$my_total_score = 0;}
		echo get_lang('Score')." : $my_total_score/$my_total_weight";
		echo '</div>';
		echo '</td></tr></table></div></div>';
		unset($objAnswerTmp);
		$i++;
		$totalWeighting+=$questionWeighting;
	 // end of large foreach on questions
	}
	$sql_update_score= "update ".$TBL_TRACK_EXERCICES." set exe_result ='". round(float_format($totalScore,1))."' where exe_id = '".Database::escape_string($id)."'";
	$result_final = Database::query($sql_update_score, __FILE__, __LINE__);

if (is_array($arrid) && is_array($arrmarks)) {
	$strids = implode(",",$arrid);
	$marksid = implode(",",$arrmarks);
}
echo '<script type="text/javascript">
$(document).ready(function() {
	$("input[name=quizstatus]").change(function() {
   showmailcontent();
});
function showmailcontent() {
	var quizstatus = $("input[name=quizstatus]:checked").val();
	if(quizstatus == "success")
	{
	$("#successmailcontent").show();
	$("#failuremailcontent").hide();
	}
	else
	{
	$("#failuremailcontent").show();
	$("#successmailcontent").hide();
	}
}
});

</script>';
$is_allowedToEdit = api_is_allowed_to_edit();

        ?>
</form>
<br /><br />
<?php
if ($origin == 'learnpath') {
	//Display::display_normal_message(get_lang('ExerciseFinished'));
        $_SESSION["display_normal_message"]=get_lang('ExerciseFinished');
	$lp_mode =  $_SESSION['lp_mode'];
	$url = '../newscorm/lp_controller.php?cidReq='.api_get_course_id().'&action=view&lp_id='.$learnpath_id.'&lp_item_id='.$learnpath_item_id.'&exeId='.$exeId.'&fb_type='.$feedback_type.'&switch=item';
	$href = ($lp_mode == 'fullscreen')?' window.opener.location.href="'.$url.'" ':' top.location.href="'.$url.'" ';
	echo '<script language="javascript" type="text/javascript">'.$href.'</script>'."\n";
}
/*
==============================================================================
		Tracking of results
==============================================================================
*/

if ($_configuration['tracking_enabled']) {
	//	Updates the empty exercise
	$safe_lp_id = $learnpath_id==''?0:(int)$learnpath_id;
	$safe_lp_item_id = $learnpath_item_id==''?0:(int)$learnpath_item_id;
	$quizDuration = (!empty($_SESSION['quizStartTime']) ? time() - $_SESSION['quizStartTime'] : 0);
	update_event_exercice($exeId, $objExercise->selectId(),$totalScore,$totalWeighting,api_get_session_id(),$safe_lp_id,$safe_lp_item_id,$quizDuration);
}

if($objExercise->results_disabled) {
	ob_end_clean();
	if ($origin != 'learnpath') {
		echo '<div class="quiz_content_actions">'.get_lang('ExerciseFinished').'<br /><br /><a href="exercice.php" />'.get_lang('Back').'</a></div>';
	//	Display :: display_normal_message(get_lang('ExerciseFinished').'<br /><a href="exercice.php" />'.get_lang('Back').'</a>',false);
                $_SESSION["display_normal_message"]=get_lang('ExerciseFinished');
	} else {
		echo '<div class="quiz_content_actions">'.get_lang('ExerciseFinished').'<br /><br />'.'</div>';
	//	Display :: display_normal_message(get_lang('ExerciseFinished').'<br /><br />',false);
                $_SESSION["display_normal_message"]=get_lang('ExerciseFinished');
	}
}

if ($origin != 'learnpath') {
	//we are not in learnpath tool
	if(!$objExercise->results_disabled){
	echo '</div>';
	}

 echo '<div class="actions">';
 if($origin != 'learnpath' && !$objExercise->results_disabled) {
		$objExcercise->resetRandomOrder($objExercise->id,0);
	 ?>
	<div>
		<b>
		<?php echo get_lang('YourTotalScore')." ";
		if ($dsp_percent == true) {
		  echo number_format(($totalScore/$totalWeighting)*100,1,'.','')."%";
		} else {
		  echo round(float_format($totalScore,1))."/".float_format($totalWeighting,1);
		}
		?>
		</b>
		<!--<button type="submit" class="save"><?php echo get_lang('Finish');?></button>-->
	</div>
<?php }
 	echo '</div>';
	Display::display_footer();
} else {
	//record the results in the learning path, using the SCORM interface (API)
	echo '<script language="javascript" type="text/javascript">window.parent.API.void_save_asset('.$totalScore.','.$totalWeighting.');</script>'."\n";
	echo '</body></html>';
}

if(count($arrques)>0) {
	$mycharset = api_get_setting('platform_charset');
	$msg = '<html><head>
		<link rel="stylesheet" href="'.api_get_path(WEB_CODE_PATH).'css/'.api_get_setting('stylesheets').'/default.css" type="text/css">
		<meta content="text/html; charset='.$mycharset.'" http-equiv="content-type">';
	$msg .= '</head>
	<body><br />
	<p>'.get_lang('OpenQuestionsAttempted').' :
	</p>
	<p>'.get_lang('AttemptDetails').' : ><br />
	</p>
	<table width="730" height="136" border="0" cellpadding="3" cellspacing="3">
						<tr>
	    <td width="229" valign="top"><h2>&nbsp;&nbsp;'.get_lang('CourseName').'</h2></td>
	    <td width="469" valign="top"><h2>#course#</h2></td>
	  </tr>
	  <tr>
	    <td width="229" valign="top" class="outerframe">&nbsp;&nbsp;'.get_lang('TestAttempted').'</span></td>
	    <td width="469" valign="top" class="outerframe">#exercise#</td>
	  </tr>
	  <tr>
	    <td valign="top">&nbsp;&nbsp;<span class="style10">'.get_lang('StudentName').'</span></td>
	    <td valign="top" >#firstName# #lastName#</td>
	  </tr>
	  <tr>
	    <td valign="top" >&nbsp;&nbsp;'.get_lang('StudentEmail').' </td>
	    <td valign="top"> #mail#</td>
	</tr></table>
	<p><br />'.get_lang('OpenQuestionsAttemptedAre').' :</p>
	 <table width="730" height="136" border="0" cellpadding="3" cellspacing="3">';
	for($i=0;$i<sizeof($arrques);$i++) {
		  $msg.='
			<tr>
		    <td width="220" valign="top" bgcolor="#E5EDF8">&nbsp;&nbsp;<span class="style10">'.get_lang('Question').'</span></td>
		    <td width="473" valign="top" bgcolor="#F3F3F3"><span class="style16"> #questionName#</span></td>
		  	</tr>
		  	<tr>
		    <td width="220" valign="top" bgcolor="#E5EDF8">&nbsp;&nbsp;<span class="style10">'.get_lang('Answer').' </span></td>
		    <td valign="top" bgcolor="#F3F3F3"><span class="style16"> #answer#</span></td>
		  	</tr>';

			$msg1= str_replace("#exercise#",$exerciseTitle,$msg);
			$msg= str_replace("#firstName#",$firstName,$msg1);
			$msg1= str_replace("#lastName#",$lastName,$msg);
			$msg= str_replace("#mail#",$mail,$msg1);
			$msg1= str_replace("#questionName#",$arrques[$i],$msg);
			$msg= str_replace("#answer#",$arrans[$i],$msg1);
			$msg1= str_replace("#i#",$i,$msg);
			$msg= str_replace("#course#",$courseName,$msg1);
	}
		$msg.='</table><br>
	 	<span class="style16">'.get_lang('ClickToCommentAndGiveFeedback').',<br />
	<a href="#url#">#url#</a></span></body></html>';

		$msg1= str_replace("#url#",$url,$msg);
		$mail_content = $msg1;
		$student_name = $_SESSION['_user']['firstName'].' '.$_SESSION['_user']['lastName'];
		$subject = get_lang('OpenQuestionsAttempted');

		$from = api_get_setting('noreply_email_address');
		if($from == '') {
			if(isset($_SESSION['id_session']) && $_SESSION['id_session'] != ''){
				$sql = 'SELECT user.email,user.lastname,user.firstname FROM '.TABLE_MAIN_SESSION.' as session, '.TABLE_MAIN_USER.' as user
						WHERE session.id_coach = user.user_id
						AND session.id = "'.Database::escape_string($_SESSION['id_session']).'"
						';
				$result=api_sql_query($sql,__FILE__,__LINE__);
				$from = Database::result($result,0,'email');
				$from_name = Database::result($result,0,'firstname').' '.Database::result($result,0,'lastname');
			} else {
				$array = explode(' ',$_SESSION['_course']['titular']);
				$firstname = $array[1];
				$lastname = $array[0];
				$sql = 'SELECT email,lastname,firstname FROM '.TABLE_MAIN_USER.'
						WHERE firstname = "'.Database::escape_string($firstname).'"
						AND lastname = "'.Database::escape_string($lastname).'"
				';
				$result=api_sql_query($sql,__FILE__,__LINE__);
				$from = Database::result($result,0,'email');
				$from_name = Database::result($result,0,'firstname').' '.Database::result($result,0,'lastname');
			}
		}
	api_mail_html($student_name, $to, $subject, $mail_content, $from_name, $from, array('encoding'=>$mycharset,'charset'=>$mycharset));
}

?>
