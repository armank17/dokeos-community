<?php //$id: $
/* For licensing terms, see /dokeos_license.txt */
/**
*
*	@package dokeos.exercise
* 	@author Julio Montoya Armas Added switchable fill in blank option added
* 	@version $Id: exercise_show.php 22256 2009-07-20 17:40:20Z ivantcholakov $
*
* 	@todo remove the debug code and use the general debug library
* 	@todo use the Database:: functions
* 	@todo small letters for table variables
*/

// name of the language file that needs to be included
$language_file=array('exercice','tracking','admin');

// including the global dokeos file
include('../inc/global.inc.php');
include('../inc/lib/course.lib.php');
// including additional libraries
include_once('exercise.class.php');
include_once('question.class.php'); //also defines answer type constants
include_once('answer.class.php');
include_once(api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
require_once(api_get_path(LIBRARY_PATH).'geometry.lib.php');
require_once api_get_path(LIBRARY_PATH).'certificatemanager.lib.php';

if ( empty ( $origin ) ) {
    $origin = $_REQUEST['origin'];
}
if (empty($exeId)) {
    $exeId = $_REQUEST['id'];
}

if ($origin == 'learnpath')
	api_protect_course_script();
else 
	api_protect_course_script(true);

// Database table definitions
$TBL_EXERCICE_QUESTION 	= Database::get_course_table(TABLE_QUIZ_TEST_QUESTION);
$TBL_EXERCICES         	= Database::get_course_table(TABLE_QUIZ_TEST);
$TBL_QUESTIONS         	= Database::get_course_table(TABLE_QUIZ_QUESTION);
$TBL_REPONSES          	= Database::get_course_table(TABLE_QUIZ_ANSWER);
$main_user_table 		= Database :: get_main_table(TABLE_MAIN_USER);
$main_course_user_table = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
$TBL_TRACK_EXERCICES	= Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
$TBL_TRACK_ATTEMPT		= Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);

$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-1.4.2.min.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/js/jquery-ui-1.8.1.custom.min.js"></script>';
$htmlHeadXtra[] = '<link type="text/css" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/css/ui-lightness/jquery-ui-1.8.1.custom.css" rel="stylesheet" />';

// check if user is allowed to get certificate
$obj_certificate = new CertificateManager();
$certif_available = $obj_certificate->isUserAllowedGetCertificate(api_get_user_id(), 'quiz', $exeId, api_get_course_id());

$htmlHeadXtra[] = "<script type='text/javascript'>
    $(document).ready(function(){	
		$('#window_close').click(function () { 
			//self.close();
		});
	});
	</script>";

Display::display_reduced_header();

echo '<style type="text/css"> body {
	background-color: #E3E4E8;	
	} </style>';

$emailId   = $_REQUEST['email'];
$user_name = $_REQUEST['user'];
$test 	   = $_REQUEST['test'];
$dt	 	   = $_REQUEST['dt'];
$marks 	   = $_REQUEST['res'];
$id 	   = $_REQUEST['id'];
$nbrQuestions 	   = $_REQUEST['nbrQuestions'];
$question_answered 	   = $_REQUEST['qnAnswered'];
$exerciseId = $_REQUEST['exerciseId'];

if ( empty ( $learnpath_id ) ) {
    $learnpath_id       = $_REQUEST['learnpath_id'];
}

if(empty($id)){
	$id = $exeId;
}
unset($_SESSION['answered_question_list']);

$current_user_id = api_get_user_id();
$current_user_id = "'".$current_user_id."'";
$current_attempt = $_SESSION['current_exercice_attempt'][$current_user_id];
$course_code = api_get_course_id();

//Unset session for clock time
unset($_SESSION['current_exercice_attempt'][$current_user_id]);
unset($_SESSION['expired_time'][$course_code][intval($_SESSION['id_session'])][$exerciseId][$learnpath_id]);
unset($_SESSION['end_expired_time'][$exerciseId][$learnpath_id]);

if(isset($_REQUEST['timeover']) && $_REQUEST['timeover'] == 'Y'){
	$sql_weighting = "SELECT SUM(ponderation) FROM $TBL_QUESTIONS qq, $TBL_EXERCICE_QUESTION qrq WHERE qq.id = qrq.question_id AND qrq.exercice_id = ".Database::escape_string($exerciseId);
	$res_weighting = Database::query($sql_weighting,__FILE__,__LINE__);
	$total_weighting = Database::result($res_weighting,0,0);

	$sql_update = "UPDATE $TBL_TRACK_EXERCICES SET exe_weighting = ".$total_weighting.", status = 'timeover', exe_date = '".date('Y-m-d H:i:s')."' WHERE exe_id = ".Database::escape_string($id);
	Database::query($sql_update,__FILE__,__LINE__);
}

$sql_fb_type='SELECT exercises.feedback_type,exercises.expired_time,exercises.score_pass,UNIX_TIMESTAMP(track_exercises.start_date),UNIX_TIMESTAMP(track_exercises.exe_date),track_exercises.exe_result,track_exercises.exe_weighting,exercises.results_disabled FROM '.$TBL_EXERCICES.' as exercises, '.$TBL_TRACK_EXERCICES.' as track_exercises WHERE exercises.id=track_exercises.exe_exo_id AND track_exercises.exe_id="'.Database::escape_string($id).'"';
$res_fb_type=Database::query($sql_fb_type,__FILE__,__LINE__);
$num_rows = Database::num_rows($res_fb_type);
if($num_rows <> 0) {
		$row_fb_type=Database::fetch_row($res_fb_type);
		$feedback_type = $row_fb_type[0]; 
		$expired_time = $row_fb_type[1]; 
		$score_pass = $row_fb_type[2]; 
		$start_date = $row_fb_type[3]; 
		$exe_date = $row_fb_type[4]; 
		$total_score = $row_fb_type[5];
		$total_weighting = $row_fb_type[6];
		$result_disabled = $row_fb_type[7];

		if($expired_time <> 0){
			$diff = $exe_date - $start_date;
			$expired_time_inseconds = $expired_time * 60;
			$final_diff = $expired_time_inseconds - $diff;
			$t = round($final_diff,2);
		//	echo sprintf('%02d:%02d:%02d', ($t/3600),($t/60%60), $t%60);
		}

		$my_total_score = round($total_score,2);
		$my_total_weighting = round($total_weighting,2);
		$percentage = ($my_total_score / $my_total_weighting)*100;

		if($percentage >= $score_pass){
			$quiz_result = get_lang('Passed');
		}
		else {
			$quiz_result = get_lang('Failed');
		}
}
else {
		$quiz_result = get_lang('Failed');
		$my_total_score = 0;
}
?>

<?php


if(isset($_REQUEST['timeover']) && $_REQUEST['timeover'] == 'Y'){
	echo '<div align="center"><div class="info-message" style="width:795px;"><ul><li>'.get_lang("TimeoverExamSubmitted").'</li></ul></div></div>';
}
echo '<div style="padding-top:50px;">';
//echo "<table class=' ' id='window_close' width='800' align='right'> <a href='#'>".get_lang('Close')."</a> </table>";

  //echo "<table class=' ' id='window_close' width='800' align='right'> <a href='#'>".get_lang('Close')."</a> </table>";
  //echo "<table id='window_close' class='' border='0' align='center'><tr><td width='800' valign='top'><a href='#'><img src='../img/close_button.png'>".get_lang('Close')."</a>    </table>"; 
  
//echo "<table class=" " width="800" border="0" align="center"><tr><td width="500" valign="top">    </table>'; 

echo '<table class="actions" width="800" border="0" align="center"><tr><td width="800" height="400" valign="top">';

echo '<div class="answer" style="position:relative;"><table width="100%" cellpadding="3" cellspacing="3">';

echo '<tr><td>&nbsp;</td></tr><tr><td colspan="2" align="center"><p class="questiontitle">'.get_lang('QuizExamResult').'</p></td></tr>';

if($result_disabled == 1){
	echo '<tr><td colspan="2" align="center"><p class="questiontitle">'.get_lang('ExamFinished').'</p></td></tr>'; 
}
else {
	if(($percentage >= $score_pass) && $num_rows <> 0){
            
                if ($certif_available && $origin != 'learnpath') {                    
                    $certif_tool_info = $obj_certificate->getCertificateInfoByTool('quiz', $exerciseId, api_get_course_id());                    
                    $link_export = api_get_path(WEB_CODE_PATH).'exercice/exercise_certificate_export.php?cidReq='.api_get_course_id().'&export=pdf&tpl_id='.$certif_tool_info['certif_template'].'&certif_tool_id='.$exeId.'&certif_tool_type=quiz';
                    echo '<div class="certificate" style="position:absolute;top:215px;left:550px;"><a href="'.$link_export.'" style="text-decoration:none;">'.Display::return_icon('certificate48x48.png', get_lang('GetCertificate'), array('style'=>'position:absolute;top:15px;right:10px;')).'</a></div>';
                    //$obj_certificate->displayCertificate('html', 'quiz', $exerciseId, $_GET['cidReq'], null, true);
                }
            
		echo '<tr><td colspan="2" align="center"><p class="success-msg">'.get_lang('Successmsg').'</p></td></tr><tr><td>&nbsp;</td></tr>';
	}
	else {
		echo '<tr><td colspan="2" align="center"><p class="failure-msg">'.get_lang('Failuremsg').'</p></td></tr><tr><td>&nbsp;</td></tr>';
	}
	if($num_rows <> 0) {
		echo '<tr><td width="50%" align="right"><span class="questiontitle">'.get_lang('Score').' : </span></td><td>'.$my_total_score."/".$my_total_weighting.'</td></tr>';
	}
	else {
		echo '<tr><td width="50%" align="right"><span class="questiontitle">'.get_lang('Score').' : </span></td><td>'.$my_total_score.'</td></tr>';
	}
		echo '<tr><td width="50%" align="right"><span class="questiontitle">'.get_lang('Percentage').' : </span></td><td>'.round($percentage,2).'%</td></tr>';

	if($expired_time <> 0){
		if(isset($_REQUEST['timeover']) && $_REQUEST['timeover'] == 'Y'){
		echo '<tr><td width="50%" align="right"><span class="questiontitle">'.get_lang('TimeRemaning').' : </span></td><td>00:00:00</td></tr>';
		}
		else {
		echo '<tr><td width="50%" align="right"><span class="questiontitle">'.get_lang('TimeRemaning').' : </span></td><td>'.sprintf('%02d:%02d:%02d', ($t/3600),($t/60%60), $t%60).'</td></tr>';
		}
	}
	echo '<tr><td>&nbsp;</td></tr>';
	echo '<tr><td colspan="2" align="center"><hr size="1" noshade="noshade"color="#cccccc"/><p class="questiontitle">'.$quiz_result.'</p></td></tr>';
        
}
echo '</table></table>';




echo '</div>';
?>