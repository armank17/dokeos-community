<?php
//$id: $
/* For licensing terms, see /dokeos_license.txt */
/**
 *
 * 	@package dokeos.exercise
 * 	@author Julio Montoya Armas Added switchable fill in blank option added
 * 	@version $Id: exercise_show.php 22256 2009-07-20 17:40:20Z ivantcholakov $
 *
 * 	@todo remove the debug code and use the general debug library
 * 	@todo use the Database:: functions
 * 	@todo small letters for table variables
 */
// name of the language file that needs to be included
$language_file = array('exercice', 'tracking', 'admin');

// including the global dokeos file
require_once '../inc/global.inc.php';
require_once api_get_path(LIBRARY_PATH) . 'course.lib.php';
// including additional libraries
require_once 'exercise.class.php';
require_once 'question.class.php'; //also defines answer type constants
require_once 'answer.class.php';
require_once '../newscorm/learnpath.class.php';
require_once api_get_path(LIBRARY_PATH) . 'formvalidator/FormValidator.class.php';
require_once api_get_path(LIBRARY_PATH) . 'geometry.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'certificatemanager.lib.php';
$htmlHeadXtra[] = '<script type="text/javascript" language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'jwplayer/jwplayer.js"></script>';

define('DOKEOS_EXERCISE', true);

// show when is located the quiz
if (empty($origin)) {
    $origin = $_REQUEST['origin'];
}
// if is located within of the module
if ($origin == 'learnpath') {
    api_protect_course_script();
} else {
    api_protect_course_script(true);
}

// Database table definitions
$TBL_EXERCICE_QUESTION = Database::get_course_table(TABLE_QUIZ_TEST_QUESTION);
$TBL_EXERCICES = Database::get_course_table(TABLE_QUIZ_TEST);
$TBL_QUESTIONS = Database::get_course_table(TABLE_QUIZ_QUESTION);
$TBL_REPONSES = Database::get_course_table(TABLE_QUIZ_ANSWER);
$main_user_table = Database :: get_main_table(TABLE_MAIN_USER);
$main_course_user_table = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
$TBL_TRACK_EXERCICES = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
$TBL_TRACK_ATTEMPT = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);

$dsp_percent = false;
$debug = 0;

if ($debug > 0) {
    echo str_repeat('&nbsp;', 0) . 'Entered exercise_result.php' . "<br />\n";
    var_dump($_POST);
}
// general parameters passed via POST/GET

if (isset($_GET['exerciseid'])){
  $exerciseId = $_GET['exerciseid'];    
}
if (isset($_GET['exerciseId'])){
  $exerciseId = $_GET['exerciseId'];    
}

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
if (empty($exeId)) {
    $exeId = $_REQUEST['id'];
}
if (empty($exercise_id)) {
    if ($origin == 'tracking_course') {
        $exercise_id = $_REQUEST['my_exe_exo_id'];
    } else {
        $exercise_id = $_REQUEST['exerciseid'];
    }
}
    

if (empty($action)) {
    $action = $_GET['action'];
}

  

$current_user_id = api_get_user_id();
$current_user_id = "'" . $current_user_id . "'";
$current_attempt = $_SESSION['current_exercice_attempt'][$current_user_id];
$course_code = api_get_course_id();

// check if user is allowed to get certificate
$obj_certificate = new CertificateManager();
$certif_available = $obj_certificate->isUserAllowedGetCertificate(api_get_user_id(), 'quiz', $exeId, $course_code);

//Unset session for clock time
unset($_SESSION['current_exercice_attempt'][$current_user_id]);
unset($_SESSION['expired_time'][$course_code][intval($_SESSION['id_session'])][$exercise_id][$learnpath_id]);
unset($_SESSION['end_expired_time'][$exercise_id][$learnpath_id]);


$is_allowedToEdit = api_is_allowed_to_edit() || $is_courseTutor;
$nameTools = get_lang('CorrectTest');

if (isset($_SESSION['gradebook'])) {
    $gradebook = $_SESSION['gradebook'];
}

if (!empty($gradebook) && $gradebook == 'view') {
    $interbreadcrumb[] = array(
        'url' => '../gradebook/' . $_SESSION['gradebook_dest'],
        'name' => get_lang('Gradebook')
    );
}

if ($origin == 'user_course') {
    $interbreadcrumb[] = array("url" => "../user/user.php?cidReq=" . Security::remove_XSS($_GET['course']), "name" => get_lang("Users"));
    $interbreadcrumb[] = array("url" => "../mySpace/myStudents.php?student=" . Security::remove_XSS($_GET['student']) . "&course=" . $_course['id'] . "&details=true&origin=" . Security::remove_XSS($_GET['origin']), "name" => get_lang("DetailsStudentInCourse"));
} else if ($origin == 'tracking_course') {
    $interbreadcrumb[] = array("url" => "../mySpace/index.php", "name" => get_lang('MySpace'));
    $interbreadcrumb[] = array("url" => "../mySpace/myStudents.php?student=" . Security::remove_XSS($_GET['student']) . '&details=true&origin=' . $origin . '&course=' . Security::remove_XSS($_GET['cidReq']), "name" => get_lang("DetailsStudentInCourse"));
} else if ($origin == 'student_progress') {
    $interbreadcrumb[] = array("url" => "../auth/my_progress.php?id_session" . Security::remove_XSS($_GET['id_session']) . "&course=" . $_cid, "name" => get_lang('MyProgress'));
    unset($_cid);
} else {
    $interbreadcrumb[] = array("url" => "exercice.php?gradebook=$gradebook", "name" => get_lang('Exercices'));
    $this_section = SECTION_COURSES;
}

if ($origin != 'learnpath' && $origin != 'author') {
    if ($certif_available) {
        $htmlHeadXtra[] = '<link type="text/css" rel="stylesheet" href="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/colorbox/colorbox.css" />';
        $htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/colorbox/jquery.colorbox.js" language="javascript"></script>';
    }
    $htmlHeadXtra[] = '<script type="text/javascript">
			  $(document).ready(function (){
				$("div.label").attr("style","width: 100%;text-align:left");
				$("div.row").attr("style","width: 100%;text-align:left");
				$("div.formw").attr("style","width: 100%;text-align:left");
			  });
			</script>';
    Display::display_tool_header($nameTools, "Exercise");
} else {
    // If the quiz is into modules then we must load jquery library
    $htmlHeadXtra[] = '<script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-1.4.2.min.js" type="text/javascript" language="javascript"></script>'; //jQuery
    $htmlHeadXtra[] = '<script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-ui/js/jquery-ui-1.8.1.custom.min.js" type="text/javascript"></script>';
    $htmlHeadXtra[] = '<link rel="stylesheet" type="text/css" href="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-ui/css/ui-lightness/jquery-ui-1.8.1.custom.css" />';
    $htmlHeadXtra[] = '<script type="text/javascript">
        (function ($) {
           try {
                window.parent.setIframeHeight();                
            } catch(e) {}
        } (jQuery));
    </script>';
    $htmlHeadXtra[] = '<style>body { background: none; }</style>';
    Display::display_reduced_header();
}
$emailId = $_REQUEST['email'];
$user_name = $_REQUEST['user'];
$test = $_REQUEST['test'];
$dt = $_REQUEST['dt'];
$marks = $_REQUEST['res'];
$id = $_REQUEST['id'];

$sql_fb_type = 'SELECT feedback_type FROM ' . $TBL_EXERCICES . ' as exercises, ' . $TBL_TRACK_EXERCICES . ' as track_exercises WHERE exercises.id=track_exercises.exe_exo_id AND track_exercises.exe_id="' . Database::escape_string($id) . '"';
$res_fb_type = Database::query($sql_fb_type, __FILE__, __LINE__);
$row_fb_type = Database::fetch_row($res_fb_type);
$feedback_type = $row_fb_type[0];
?>



<style type="text/css">
  
    #comments {
        position:absolute;
        left:795px;
        top:0px;
        width:200px;
        height:75px;
        z-index:1;
    }
    .scroll_feedback {
        padding: 0px !important;
    }
    .quiz_report_content {
        width: 885px !important;
        overflow: auto;
    }
    
    
		

	button.continue:hover {

	   cursor:pointer;   
		border: 0px;    
		background-attachment: scroll;
		background-clip: border-box;
		background-color: transparent;
		background: #696b6b; /* Old browsers */
		background: -moz-linear-gradient(top, #424444 0%, #363738 50%, #2a2a2b 51%, #2f2f2f 100%); /* FF3.6+ */
		background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#696b6b), color-stop(50%,#363738), color-stop(51%,#2a2a2b), color-stop(100%,#2f2f2f)); /* Chrome,Safari4+ */
		background: -webkit-linear-gradient(top, #424444 0%,#363738 50%,#2a2a2b 51%,#2f2f2f 100%); /* Chrome10+,Safari5.1+ */
		background: -o-linear-gradient(top, #424444 0%,#363738 50%,#2a2a2b 51%,#2f2f2f 100%); /* Opera11.10+ */
		background: -ms-linear-gradient(top, #424444 0%,#363738 50%,#2a2a2b 51%,#2f2f2f 100%); /* IE10+ */
		filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#424444', endColorstr='#2f2f2f',GradientType=0 ); /* IE6-9 */
		background: linear-gradient(top, #424444 0%,#363738 50%,#2a2a2b 51%,#2f2f2f 100%); /* W3C */    
		background-origin: padding-box;
		background-position: 0 0;
		background-repeat: repeat;
		background-size: auto auto;

	}

	button.continue {

		border:1px solid #696b6b;
		border-radius: 8px;
		-moz-border-radius: 8px;
		-webkit-border-radius: 8px;
		cursor: pointer;
		vertical-align:middle;
		text-transform:uppercase;
		font-weight:bold;    
		-moz-border-bottom-colors: none;
		-moz-border-left-colors: none;
		-moz-border-right-colors: none;
		-moz-border-top-colors: none;    
		background:#4F9D00;    
		height: 50px;
		font-size: 13px;
		color: #FFFFFF;    
		padding-left: 20px!important;
		padding-right: 20px!important;       
		border-bottom-color: #4d4d4d;
		border-bottom-style: solid;
		border-bottom-width: 1px;
		border-image-outset: 0 0 0 0;
		border-image-repeat: stretch stretch;
		border-image-slice: 100% 100% 100% 100%;
		border-image-source: none;
		border-image-width: 1 1 1 1;
		border-left-color-ltr-source: physical;
		border-left-color-rtl-source: physical;
		border-left-color-value: #4d4d4d;
		border-left-style-ltr-source: physical;
		border-left-style-rtl-source: physical;
		border-left-style-value: solid;
		border-left-width-ltr-source: physical;
		border-left-width-rtl-source: physical;
		border-left-width-value: 1px;
		border-right-color-ltr-source: physical;
		border-right-color-rtl-source: physical;
		border-right-color-value: #4d4d4d;
		border-right-style-ltr-source: physical;
		border-right-style-rtl-source: physical;
		border-right-style-value: solid;
		border-right-width-ltr-source: physical;
		border-right-width-rtl-source: physical;
		border-right-width-value: 1px;
		box-shadow: 0 1px 0 #DDDDDD, 0 1px 0 rgba(255, 255, 255, 0.2) inset;
		color: #FFFFFF;
		text-shadow: 0 1px 0 rgba(0, 0, 0, 0.2);

	}
    
    
    
</style>
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

/**
 * This function gets the comments of an exercise
 *
 * @param int $id
 * @param int $question_id
 * @return str the comment
 */
function get_comments($id, $question_id) {
    global $TBL_TRACK_ATTEMPT;
    $sql = "SELECT teacher_comment FROM " . $TBL_TRACK_ATTEMPT . " where exe_id='" . Database::escape_string($id) . "' and question_id = '" . Database::escape_string($question_id) . "' ORDER by question_id";
    $sqlres = api_sql_query($sql, __FILE__, __LINE__);
    $comm = Database::result($sqlres, 0, "teacher_comment");
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
function display_unique_or_multiple_answer($answerType, $studentChoice, $answer, $answerComment, $answerCorrect, $id, $questionId, $ans) {
    ?>
    <tr>
        <td width="5%" align="center">
            <img src="../img/<?php echo ($answerType == UNIQUE_ANSWER) ? 'radio' : 'checkbox';
    echo $studentChoice ? '_on' : '_off'; ?>.gif"
                 border="0" alt="" />
        </td>
        <td width="5%" align="center">
            <img src="../img/<?php echo ($answerType == UNIQUE_ANSWER) ? 'radio' : 'checkbox';
    echo $answerCorrect ? '_on' : '_off'; ?>.gif"
                 border="0" alt=" " />
        </td>
        <td width="40%" style="border-bottom: 1px solid #4171B5;">
    <?php
    $answer = api_parse_tex($answer);
    echo $answer;
    ?>
        </td>
        <!--<td width="20%" style="border-bottom: 1px solid #4171B5;">
    <?php
    $answerComment = api_parse_tex($answerComment);
    if ($studentChoice) {
        if (!$answerCorrect) {
            echo '<span style="font-weight: bold; color: #FF0000;">' . nl2br(make_clickable($answerComment)) . '</span>';
        } else {
            echo '<span style="font-weight: bold; color: #008000;">' . nl2br(make_clickable($answerComment)) . '</span>';
        }
    } else {
        echo '&nbsp;';
    }
    ?>
        </td>
        <?php
        if ($ans == 1) {
            $comm = get_comments($id, $questionId);
        }
        ?>    -->
    </tr>
        <?php
    }

    /**
     * Display the answers to a reasoning question
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
    function display_reasoning_answer($answerType, $studentChoice, $answer, $answerComment, $answerCorrect, $id, $questionId, $ans) {
        ?>
    <tr>
        <td width="5%" align="center">
            <img src="../img/<?php echo ($answerType == UNIQUE_ANSWER) ? 'radio' : 'checkbox';
    echo $studentChoice ? '_on' : '_off'; ?>.gif"
                 border="0" alt="" />
        </td>
        <td width="5%" align="center">
            <img src="../img/<?php echo ($answerType == UNIQUE_ANSWER) ? 'radio' : 'checkbox';
    echo $answerCorrect ? '_on' : '_off'; ?>.gif"
                 border="0" alt=" " />
        </td>
        <td width="40%" style="border-bottom: 1px solid #4171B5;">
    <?php
    $answer = api_parse_tex($answer);
    echo $answer;
    ?>
        </td>
            <?php
            if ($ans == 1) {
                $comm = get_comments($id, $questionId);
            }
            ?>
    </tr>
        <?php
    }

    /**
     * Shows the answer to a fill-in-the-blanks question, as HTML
     * @param string    Answer text
     * @param int       Exercise ID
     * @param int       Question ID
     * @return void
     */
    function display_fill_in_blanks_answer($answer, $id, $questionId) {
        ?>
    <tr>
        <td>
    <?php echo nl2br(Security::remove_XSS($answer, COURSEMANAGERLOWSECURITY)); ?>
        </td><?php if (!api_is_allowed_to_edit()) { ?>
            <td>
        <?php
        $comm = get_comments($id, $questionId);
        ?>
            </td>
        </tr>
        <?php
        }
    }

    /**
     * Shows the answer to a free-answer question, as HTML
     * @param string    Answer text
     * @param int       Exercise ID
     * @param int       Question ID
     * @return void
     */
    function display_free_answer($answer, $id, $questionId) {
        ?>
    <tr>
        <td>
    <?php echo nl2br(Security::remove_XSS($answer, COURSEMANAGERLOWSECURITY)); ?>
        </td> <?php if (!api_is_allowed_to_edit()) { ?>
            <td>
        <?php
        $comm = get_comments($id, $questionId);
        ?>
            </td>
    <?php } ?>
    </tr>
            <?php
        }

        /**
         * Displays the answer to a hotspot question
         *
         * @param int $answerId
         * @param string $answer
         * @param string $studentChoice
         * @param string $answerComment
         */
        function display_hotspot_answer($answerId, $answer, $studentChoice, $correctComment) {
            //global $hotspot_colors;
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
            ?>
    <tr>
        <td width="100px" valign="top" align="left">
            <div style="width:100%;">
                <div style="height:11px; width:11px; background-color:<?php echo $hotspot_colors[$answerId]; ?>; display:inline; float:left; margin-top:3px;"></div>
                <div style="float:left; padding-left:5px;">
    <?php echo $answerId; ?>
                </div>
                <div><?php echo '&nbsp;' . $answer ?></div>
            </div>
        </td>
        <td width="50px" style="padding-right:15px" valign="top" align="left">
    <?php $my_choice = ($studentChoice) ? get_lang('Correct') : get_lang('Fault');
    echo $my_choice; ?>
        </td>

        <td valign="top" align="left" >
            <?php
            if ($studentChoice) {
                echo '<span style="font-weight: bold; color: #008000;">' . nl2br(make_clickable($correctComment[0])) . '</span>';
            } else {
                echo '<span style="font-weight: bold; color: #FF0000;">' . nl2br(make_clickable($correctComment[1])) . '</span>';
            }
            ?>
        </td>
    </tr>
            <?php
        }

        function display_hotspot_delineation_answer($answerId, $answer, $studentChoice, $answerComment) {
            //global $hotspot_colors;
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
            ?>
    <tr>
        <td valign="top" align="left">
            <div style="width:100%;">
                <div style="height:11px; width:11px; background-color:<?php echo $hotspot_colors[$answerId]; ?>; float:left; margin:3px;"></div>
                <div><?php echo $answer ?></div>
            </div>
        </td>
        <td valign="top" align="left"></td>

    </tr>
    <?php
}

/*
  ==============================================================================
  MAIN CODE
  ==============================================================================
 */

if ($origin != 'learnpath' && $origin != 'author') {
    echo '<div class="actions">'; 
    if (api_is_course_admin() && api_is_allowed_to_edit()) {

        if (isset($_GET['origin']) && $_GET['origin'] == 'tracking_course') {
            echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'mySpace/myStudents.php?student=' . Security :: remove_XSS($_GET['student']) . '&details=true&course=' . Security :: remove_XSS($_GET['cidReq']) . '&origin=' . Security :: remove_XSS($_GET['origin']) . '">' . Display::return_icon('pixel.gif', get_lang('Back'), array('class' => 'toolactionplaceholdericon toolactionback')) . get_lang('Back') . '</a>';
        } elseif (isset($_GET['origin']) && $_GET['origin'] == 'tracking') {
            echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'mySpace/myStudents.php?student=' . Security :: remove_XSS($_GET['student']) . '&details=true&course=' . Security :: remove_XSS($_GET['cidReq']) . '&origin=' . Security :: remove_XSS($_GET['origin']) . '">' . Display::return_icon('pixel.gif', get_lang('Back'), array('class' => 'toolactionplaceholdericon toolactionback')) . get_lang('Back') . '</a>';
        } else {
            echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'exercice/admin.php?' . api_get_cidreq() . '&exerciseId=' . $exerciseId. '">' . Display::return_icon('pixel.gif', get_lang('GoBackToEx'), array('class' => 'toolactionplaceholdericon toolactionback')) . get_lang('GoBackToEx') . '</a>';
        }
        echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'exercice/exercice_scenario.php?scenario=yes&modifyExercise=yes&' . api_get_cidreq() . '&exerciseId=' . $exerciseId . '">' . Display::return_icon('pixel.gif', get_lang('Scenario'), array('class' => 'toolactionplaceholdericon toolactionscenario')) . get_lang('Scenario') . '</a>';
    } else {
		if(!isset($_REQUEST['activity_id'])) {
        echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'exercice/exercice.php?' . api_get_cidreq() . '">' . Display::return_icon('pixel.gif', get_lang('GoBackToEx'), array('class' => 'toolactionplaceholdericon toolactionback')) . get_lang('GoBackToEx') . '</a>';
		}
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
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td colspan="2">
<?php
$sql_questions = "SELECT question_id FROM $TBL_EXERCICE_QUESTION WHERE exercice_id = " . Database::escape_string($exercise_id);
$res_questions = Database::query($sql_questions, __FILE__, __LINE__);
$total_questions = array();
while ($row_questions = Database::fetch_array($res_questions)) {
    $total_questions[] = $row_questions['question_id'];
}

$sql_test_name = 'SELECT title, description, results_disabled, quiz_type, quiz_final_feedback FROM ' . $TBL_EXERCICES . ' as exercises, ' . $TBL_TRACK_EXERCICES . ' as track_exercises WHERE exercises.id=track_exercises.exe_exo_id AND track_exercises.exe_id="' . Database::escape_string($id) . '"';
$result = api_sql_query($sql_test_name);
$show_results = true;
$show_score = true;
// Avoiding the "Score 0/0" message  when the exe_id is not set
if (Database::num_rows($result) > 0 && isset($id)) {
    $test = Database::result($result, 0, 0);
    $exerciseTitle = api_parse_tex($test);
    $exerciseDescription = Database::result($result, 0, 1);
    $quiz_type = Database::result($result, 0, 3);
	$quiz_final_feedback = Database::result($result, 0, 4);

    // if the results_disabled of the Quiz is 1 when block the script
    $result_disabled = Database::result($result, 0, 2);
    if (!(api_is_platform_admin() || api_is_course_admin())) {
        if ($result_disabled == 1) {
            //api_not_allowed();
//			$show_results = false;
            $show_score = false;
            //Display::display_warning_message(get_lang('CantViewResults'));
            if ($origin != 'learnpath' && $origin != 'author') {
                echo '<div class="quiz_content_actions">' . get_lang('ThankYouForPassingTheTest') . '<br /><br /><a href="exercice.php">' . (get_lang('BackToExercisesList')) . '</a></div>';
                //	Display::display_warning_message(get_lang('ThankYouForPassingTheTest').'<br /><br /><a href="exercice.php">'.(get_lang('BackToExercisesList')).'</a>', false);
                echo '</td>
				</tr>
				</table>';
            }
        }
    }
    if ($show_results == true) {
        $user_restriction = $is_allowedToEdit ? '' : "AND user_id=" . intval($_user['user_id']) . " ";
        $query = "SELECT attempts.question_id, answer  from " . $TBL_TRACK_ATTEMPT . " as attempts
						INNER JOIN " . $TBL_TRACK_EXERCICES . " as stats_exercices ON stats_exercices.exe_id=attempts.exe_id
						INNER JOIN " . $TBL_QUESTIONS . " as questions ON questions.id=attempts.question_id
                                                INNER JOIN " . $TBL_EXERCICE_QUESTION . " as rel_questions ON rel_questions.question_id = questions.id AND rel_questions.exercice_id = stats_exercices.exe_exo_id
                                                WHERE attempts.exe_id='" . Database::escape_string($id) . "' $user_restriction
                                                GROUP BY attempts.question_id
                                                ORDER BY rel_questions.question_order ASC";
        $result = Database::query($query, __FILE__, __LINE__);
    }
} else {
    Display::display_warning_message(get_lang('CantViewResults'));
    $show_results = false;
    echo '</td>
	</tr>
	</table>';
}
//if ($origin == 'learnpath' && !isset($_GET['fb_type'])) {
//    $show_results = false;
//}

if ($show_results == true) {
    ?>
                    <!--<div style="padding:0px 0px 20px 30px;">-->
                    <div class="content-quiz-result-actions" style="position:relative;">
                    <?php
                    if ($certif_available && $origin != 'learnpath' && $origin != 'author') {
                        echo '<a class="certificate-' . $exercise_id . '-link" href="#">' . Display::return_icon('certificate48x48.png', get_lang('GetCertificate'), array('style' => 'position:absolute;top:15px;right:10px;')) . '</a>';
                        $obj_certificate->displayCertificate('html', 'quiz', $exercise_id, $_GET['cidReq'], null, true);
                    }
                    ?>
                        <div class="content-quiz-result-certif"></div>
                        <table width="100%" class="actions">
                            <tr>
                                <td style="font-weight:bold" width="10%"><div class="actions-message" align="right"><?php echo '&nbsp;' . get_lang('CourseTitle') ?> : </div></td>
                                <td><div class="actions-message" width="90%"><?php echo $_course['name'] ?></div></td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold" width="10%"><div class="actions-message" align="right"><?php echo '&nbsp;' . get_lang('User') ?> : </div></td>
                                <td><div class="actions-message" width="90%"><?php
                    if (isset($_GET['cidReq'])) {
                        $course_code = Security::remove_XSS($_GET['cidReq']);
                    } else {
                        $course_code = api_get_course_id();
                    }
                    if (isset($_GET['student'])) {
                        $user_id = Security::remove_XSS($_GET['student']);
                    } else {
                        $user_id = api_get_user_id();
                    }

                    $status_info = CourseManager::get_user_in_course_status($user_id, $course_code);
                    if (STUDENT == $status_info) {
                        $user_info = api_get_user_info($user_id);
                        echo $user_info['firstName'] . ' ' . $user_info['lastName'];
                    } elseif (COURSEMANAGER == $status_info && !isset($_GET['user'])) {
                        $user_info = api_get_user_info($user_id);
                        echo $user_info['firstName'] . ' ' . $user_info['lastName'];
                    } else {
                        echo $user_name;
                    }
                    ?></div></td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold" width="10%" class="actions-message" align="right">
                                        <?php echo '&nbsp;' . get_lang("Exercise") . ' :'; ?>
                                </td>
                                <td width="90%">
                                        <?php echo $test; ?>
                                </td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold" width="10%" class="actions-message" align="right">
                                </td>
                                <td width="90%">
                                    <?php echo $exerciseDescription; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <br />
        </table>
    <?php
}
$i = $totalScore = $totalWeighting = $totalScoreMA = 0;

if ($debug > 0) {
    echo "ExerciseResult: ";
    var_dump($exerciseResult);
    echo "QuestionList: ";
    var_dump($questionList);
}

if ($show_results) {
    $obj = new Exercise();
    $obj->read($exercise_id);
    $simplifyQuestionsAuthoring = ($obj->selectSimplifymode()== 1) ? 'true' : 'false';     
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

        if ($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER || $answerType == REASONING) {
            $colspan = 2;
        }
        if ($answerType == MATCHING || $answerType == FREE_ANSWER) {
            $colspan = 2;
        } else {
            $colspan = 2;
        }
        ?>
            <div style="padding:0px 0px 20px 0px;"><div class="rounded" style="width: 100%; padding: 1px; background-color:#fff;"><table class="rounded_inner" style="width: 100%; background-color:#fff;"><tr><td>
            <div id="question_title" class="quiz_report_content">
            <?php echo get_lang("Question") . ' ' . ($counter) . ' : <p>' . $questionName;
            '</p>' ?>
            </div>
            <!--	<div id="question_description" class="scroll_feedback">
                <?php echo $questionDescription; ?>
                    </div>-->

            <?php
            $s = '';
            if (!empty($questionDescription)) {
                if ($mediaPosition == 'top') {
                    $s .= '<div align="center"><div class="quiz_content_actions quit_border">' . $questionDescription . '</div></div>';
                } elseif ($mediaPosition == 'right') {
                    $s .= '<div class="quiz_content_actions quit_border" style="width:500px; overflow:auto !important; margin-right:2px;float:right; text-align:center;">' . $questionDescription . '</div>';
                }
            }
            if ($answerType == MULTIPLE_ANSWER) {
                $choice = array();
                $feedback_if_true = $feedback_if_false = '';
                if ($mediaPosition == 'top' || $mediaPosition == 'nomedia' || empty($questionDescription)) {
                    $s .= '<div class="quiz_content_actions quit_border" style="width:920px;overflow:auto;float:left;">';
                } elseif ($mediaPosition == 'right') {
                    $s .= '<div class="quiz_content_actions quit_border" style="width:46%;clear:none;float:left;height:auto;min-height:200px;overflow:auto;">';
                }
                $s .= '<table width="100%" border="0" class="data_table"><tr class="row_odd"><td>' . get_lang("Choice") . '</td><td>' . get_lang("ExpectedChoice") . '</td><td>' . get_lang("Answer") . '</td></tr>';

                // construction of the Answer object
                $objAnswerTmp = new Answer($questionId);
                $nbrAnswers = $objAnswerTmp->selectNbrAnswers();
                $questionScore = 0;
                $correctChoice = 'N';
                $answerWrong = 'N';
                $totalScoreMA = 0;
                $count_ans = $last_incorrect = 0;
                for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
                    $answer = $objAnswerTmp->selectAnswer($answerId);
                    $answerComment = $objAnswerTmp->selectComment($answerId);
                    $correctComment[] = $objAnswerTmp->selectComment($answerId);
                    $answerCorrect = $objAnswerTmp->isCorrect($answerId);
                    $answerWeighting = $objAnswerTmp->selectWeighting($answerId);
                    $queryans = "select * from " . $TBL_TRACK_ATTEMPT . " where exe_id = '" . Database::escape_string($id) . "' and question_id= '" . Database::escape_string($questionId) . "'";
                    
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
                        }
                        
                         else {
                            $answerWrong = 'Y';
                            $feedback_if_false = $objAnswerTmp->selectComment($answerId);
                        }
                        
                    }
                    if (!$answerCorrect) {
                        $last_incorrect = $answerId;
                    }
                    if ($answerId == 1) {
                        $s .= display_unique_or_multiple_or_reasoning_answer($answerType, $studentChoice, $answer, $answerComment, $answerCorrect, $id, $questionId, $answerId);
                    } else {
                        $s .= display_unique_or_multiple_or_reasoning_answer($answerType, $studentChoice, $answer, $answerComment, $answerCorrect, $id, $questionId, "");
                    }

                    $i++;
                }
                $s .= '<tr><td colspan="3">&nbsp;</td></tr>';
                $s .= '</table></div>';
                
                $s .= '<table style="clear:both;width:100%">';
                if ($correctChoice == 'Y' && $answerWrong == 'N') {
                    if (empty($feedback_if_true)) {
                        $feedback_if_true = get_lang('NoTrainerComment');
                    }
                    $s .= '<tr><td colspan="3"><div class="feedback-right feed-custom-right">' . get_lang('Feedback') . '</div></td></tr><tr><td colspan="3"><div class="feedback-right"><span>' . $feedback_if_true . '</span></div></td></tr>';
                } else { 
                    if (empty($feedback_if_false)) {
                        $feedback_if_false = get_lang('NoTrainerComment');
                    } 
//                    if (empty($count_ans)) {
//                        $feedback_if_false = $objAnswerTmp->selectComment($last_incorrect);
//                    }
                    $s .= '<tr><td colspan="3"><div class="feedback-wrong feed-custom-wrong">' . get_lang('Feedback') . '</div></td></tr><tr><td colspan="3"><div class="feedback-wrong"><span>' . $feedback_if_false . '</span></div></td></tr>';
                }

                $s .= '</table>';
                
                echo $s;
                if ($totalScoreMA > 0) {
                    $totalScore+=$totalScoreMA;
                }
                $totalScoreMA = 0;
            } elseif ($answerType == REASONING) {
                $choice = array();
                $feedback_if_true = $feedback_if_false = '';
                if ($mediaPosition == 'top' || $mediaPosition == 'nomedia' || empty($questionDescription)) {
                    $s .= '<div class="quiz_content_actions quit_border" style="width:920px;overflow:auto;float:left;">';
                } elseif ($mediaPosition == 'right') {
                    $s .= '<div class="quiz_content_actions quit_border" style="width:46%;clear:none;float:left;overflow:auto;">';
                }
                $s .= '<table width="100%" border="0" class="data_table"><tr class="row_odd"><td>' . get_lang("Choice") . '</td><td>' . get_lang("ExpectedChoice") . '</td><td>' . get_lang("Answer") . '</td></tr>';

                // construction of the Answer object
                $objAnswerTmp = new Answer($questionId);
                $nbrAnswers = $objAnswerTmp->selectNbrAnswers();
                $questionScore = 0;
                $correctChoice = 'Y';
                $noStudentChoice = 'N';
                $answerWrong = 'N';
                for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
                    $answer = $objAnswerTmp->selectAnswer($answerId);
                    $answerComment = $objAnswerTmp->selectComment($answerId);
                    $correctComment[] = $objAnswerTmp->selectComment($answerId);
                    $answerCorrect = $objAnswerTmp->isCorrect($answerId);
                    $answerWeighting = $objAnswerTmp->selectWeighting($answerId);
                    $queryans = "select * from " . $TBL_TRACK_ATTEMPT . " where exe_id = '" . Database::escape_string($id) . "' and question_id= '" . Database::escape_string($questionId) . "'";
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
                    
//                    if($feedback_if_true ==''){
//                       $feedback_if_true = get_lang('NoTrainerComment'); 
//                    }
//                    if($feedback_if_false ==''){
//                         $feedback_if_false = get_lang('NoTrainerComment'); 
//                    }

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
                        $s .= display_unique_or_multiple_or_reasoning_answer($answerType, $studentChoice, $answer, $answerComment, $answerCorrect, $id, $questionId, $answerId);
                    } else {
                        $s .= display_unique_or_multiple_or_reasoning_answer($answerType, $studentChoice, $answer, $answerComment, $answerCorrect, $id, $questionId, "");
                    }
                    $i++;
                }
                if ($answerType == REASONING && $noStudentChoice == 'Y') {
                    if ($correctChoice == 'Y') {
                        $questionScore += $questionWeighting;
                        $totalScore += $questionWeighting;
                    } else {
                        $questionScore += $wrongAnswerWeighting;
                        $totalScore += $wrongAnswerWeighting;
                    }
                }

                $s .= '<tr><td colspan="3">&nbsp;</td></tr>';
                $s .= '</table></div>';
                
                $s .= '<table style="clear:both;width:100%">';
                if ($correctChoice == 'Y' && $answerWrong == 'N') {
                    if (empty($feedback_if_true)) {
                        $feedback_if_true = get_lang('NoTrainerComment');
                    }
                    $s .= '<tr><td colspan="3"><b><div class="feedback-right feed-custom-right">' . get_lang('Feedback') . '</div></b></td></tr><tr><td colspan="3"><div class="feedback-right">' . $feedback_if_true . '</div></td></tr>';
                } else {
                    if (empty($feedback_if_false)) {
                        $feedback_if_false = get_lang('NoTrainerComment');
                    }
                    $s .= '<tr><td colspan="3"><b><div class="feedback-wrong feed-custom-wrong">' . get_lang('Feedback') . '</div></b></td></tr><tr><td colspan="3"><div class="feedback-wrong">' . $feedback_if_false . '</div></td></tr>';
                }
                $s .= '</table>';
                
                echo $s;
            } elseif ($answerType == UNIQUE_ANSWER) {
                $feedback_if_true = $feedback_if_false = '';
                if ($mediaPosition == 'top' || $mediaPosition == 'nomedia' || empty($questionDescription)) {
                    $s .= '<div class="quiz_content_actions quit_border" style="width:920px;overflow:auto;float:left;">';
                } elseif ($mediaPosition == 'right') {
                    $s .= '<div class="quiz_content_actions quit_border" style="width:46%;clear:none;float:left;height:auto;min-height:300px;overflow:auto;">';
                }
                $s .= '<table width="100%" border="0" class="data_table"><tr class="row_odd"><td>' . get_lang("Choice") . '</td><td>' . get_lang("ExpectedChoice") . '</td><td>' . get_lang("Answer") . '</td></tr>';

                $objAnswerTmp = new Answer($questionId);
                $nbrAnswers = $objAnswerTmp->selectNbrAnswers();
                $questionScore = 0;
                $correctChoice = 'N';
                $correctComment = array();
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
                    $queryans = "select answer from " . $TBL_TRACK_ATTEMPT . " where exe_id = '" . Database::escape_string($id) . "' and question_id= '" . Database::escape_string($questionId) . "'";
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
                        $s .= display_unique_or_multiple_or_reasoning_answer($answerType, $studentChoice, $answer, $answerComment, $answerCorrect, $id, $questionId, $answerId);
                    } else {
                        $s .= display_unique_or_multiple_or_reasoning_answer($answerType, $studentChoice, $answer, $answerComment, $answerCorrect, $id, $questionId, "");
                    }
                    $i++;
                }

                $s .= '<tr><td colspan="3">&nbsp;</td></tr>';
                $s .= '</table></div>';
                
                $s .= '<table style="clear:both;width:100%">';
                if ($correctChoice == 'Y') {
                    $feedback_if_true = $objAnswerTmp->selectComment($correct);
                    if (empty($feedback_if_true)) {
                        $feedback_if_true = get_lang('NoTrainerComment');
                    }
                    $s .= '<tr><td colspan="3"><div class="feedback-right feed-custom-right">' . get_lang('Feedback') . '</div></td></tr><tr><td colspan="3"><div class="feedback-right"><span>' . $feedback_if_true . '</span></div></td></tr>';
                } else {
                    $feedback_if_false = $objAnswerTmp->selectComment($not_correct);
                    if (empty($feedback_if_false)) {
                        $feedback_if_false = get_lang('NoTrainerComment');
                    }
                    $s .= '<tr><td colspan="3"><div class="feedback-wrong feed-custom-wrong">' . get_lang('Feedback') . '</div></td></tr><tr><td colspan="3"><div class="feedback-wrong"><span>' . $feedback_if_false . '</span></div></td></tr>';
                }
                $s .= '</table>';
                
                echo $s;
            } elseif ($answerType == FILL_IN_BLANKS) {
                $feedback_if_true = $feedback_if_false = '';
                $objAnswerTmp = new Answer($questionId);
                $nbrAnswers = $objAnswerTmp->selectNbrAnswers();
                $questionScore = 0;
                $feedback_data = unserialize($objAnswerTmp->comment[1]);
                $feedback_true = $feedback_data['comment[1]'];
                $feedback_false = $feedback_data['comment[2]'];
                
                if ($feedback_true == ''){
                    $feedback_true = get_lang('NoTrainerComment');
                }
                if ($feedback_false == ''){
                    $feedback_false = get_lang('NoTrainerComment');
                }
                
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

                            $queryfill = "select answer from " . $TBL_TRACK_ATTEMPT . " where exe_id = '" . Database::escape_string($id) . "' and question_id= '" . Database::escape_string($questionId) . "'";
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

                            $queryfill = "SELECT answer FROM " . $TBL_TRACK_ATTEMPT . " WHERE exe_id = '" . Database::escape_string($id) . "' and question_id= '" . Database::escape_string($questionId) . "'";
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

                    if ($mediaPosition == 'top' || $mediaPosition == 'nomedia' || empty($questionDescription)) {
                        $s .= '<div class="quiz_content_actions quit_border" style="width:920px;overflow:auto;float:left;">';
                    } elseif ($mediaPosition == 'right') {
                        $s .= '<div class="quiz_content_actions quit_border" style="width:46%;clear:none;float:left;height:auto;min-height:300px;overflow:auto;">';
                    }
                    $s .= '<div class="scroll_feedback"><b>' . $answer . '</b></div>';
                    //$s .= '<table width="100%" border="0"><tr><td><b><div class="feedback-right feed-custom-right">' . get_lang('Feedback') . '</div></b></td></tr>';
                    $fy = 0;
                    $fn = 0;

                    for ($k = 0; $k < sizeof($feedback_anscorrect); $k++) {
                        if ($feedback_anscorrect[$k] == "Y") {
                            $fy++;
                        } else {
                            $fn++;
                        }
                    }
                    $s .= '</div><table style="clear:both;width:100%" border="0">';
                    if ($fy >= $fn && $fy > 0) {
                        $s .= '<tr><td><b><div class="feedback-right feed-custom-right">' . get_lang('Feedback') . '</div></b></td></tr>';
                        $s .= '<td align="left"><div class="feedback-right">' . $feedback_true . '</div></td></tr>';
                    } else {
                        $s .= '<tr><td><b><div class="feedback-wrong feed-custom-wrong">' . get_lang('Feedback') . '</div></b></td></tr>';
                        $s .= '<td align="left"><div class="feedback-wrong">' . $feedback_false . '</div></td></tr>';
                    }
                    $s .= '</table></div>';
                    $i++;
                }
                echo $s;
            } elseif ($answerType == FREE_ANSWER) {
                $feedback_if_true = $feedback_if_false = '';
                $answer = $str;
                if ($mediaPosition == 'top' || $mediaPosition == 'nomedia' || empty($questionDescription)) {
                    $s .= '<div class="quiz_content_actions quit_border" style="width:920px;overflow:auto;float:left;">';
                } elseif ($mediaPosition == 'right') {
                    $s .= '<div class="quiz_content_actions quit_border" style="width:46%;clear:none;float:left;height:auto;min-height:300px;overflow:auto;">';
                }
                echo $s;
                ?>
                <table border="0" cellspacing="3" cellpadding="3" align="center" class="feedback_actions" style="width:98%;">
                    <tr>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td><i><?php echo get_lang("Answer"); ?></i> </td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                    </tr>

                <?php
                $objAnswerTmp = new Answer($questionId);
                $nbrAnswers = $objAnswerTmp->selectNbrAnswers();
                $questionScore = 0;
                $query = "select answer, marks from " . $TBL_TRACK_ATTEMPT . " where exe_id = '" . Database::escape_string($id) . "' and question_id= '" . Database::escape_string($questionId) . "'";
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
                echo '<tr>
                    <td valign="top">' . display_free_answer($choice, $id, $questionId) . '</td>
                    </tr>
                    <tr><td valign="top">' . get_lang('notCorrectedYet') . '</td></tr><tr><td></td></tr>
                </table>';
            } else if ($answerType == MATCHING) {
                $feedback_if_true = $feedback_if_false = '';
                $objAnswerTmp = new Answer($questionId);
                
                
                $objQuestionTmp = Question::read($questionId);
                if($simplifyQuestionsAuthoring== 'true'){        
                    $Showimageleft = $objQuestionTmp->selectShowimageleft();
                    $Showimageright = $objQuestionTmp->selectShowimageright();                   
                }
                
                $answerComment_true = $objAnswerTmp->selectComment(1);
                $answerComment_false = $objAnswerTmp->selectComment(2);
                if ($answerComment_true ==''){
                    $answerComment_true = get_lang('NoTrainerComment');                    
                }
                if($answerComment_false==''){
                    $answerComment_false = get_lang('NoTrainerComment');
                }
                
                $questionScore = 0;
                $table_ans = Database :: get_course_table(TABLE_QUIZ_ANSWER);
                $TBL_TRACK_ATTEMPT = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);
                $answer_ok = 'N';
                $answer_wrong = 'N';
                $sql_select_answer = 'SELECT id, answer, correct, position, ponderation FROM ' . $table_ans . ' WHERE question_id="' . Database::escape_string($questionId) . '" AND correct<>0 ORDER BY position';
                
                $sql_answer = 'SELECT position, answer, id FROM ' . $table_ans . ' WHERE question_id="' . Database::escape_string($questionId) . '" AND correct=0 ORDER BY position';
                $res_answer = api_sql_query($sql_answer, __FILE__, __LINE__);
                // getting the real answer
                $real_list = array();
                while ($real_answer = Database::fetch_array($res_answer)) {
                    $real_list[$real_answer['position']] = array('id'=>$real_answer['id'],'answer'=>$real_answer['answer']);
                }

                $res_answers = api_sql_query($sql_select_answer, __FILE__, __LINE__);

                echo '<table cellspacing="0" cellpadding="0" align="center" class="feedback_actions fa_2">';
                echo '<thead>';
                echo '<tr>
                                <th align="center" width="33%"><span style="font-style: italic;color:#4171B5;font-weight:bold;">' . get_lang("ElementList") . '</span> </th>
                                <th align="center" width="33%"><span style="font-style: italic;color:#4171B5;font-weight:bold;">' . get_lang("YourAnswers") . '</span></th>
                                <th align="center" width="33%"><span style="font-style: italic;color:#4171B5;font-weight:bold;">' . get_lang("Correct") . '</span></th>
                            </tr>';
                echo '</thead>';

                while ($a_answers = Database::fetch_array($res_answers)) {
                    $i_answer_id = $a_answers['id']; 
                    $s_answer_label = $a_answers['answer'];  
                    $i_answer_correct_answer = $a_answers['correct']; 
                    $i_answer_position = $a_answers['position'];

                    $sql_user_answer = 'SELECT track_e_attempt.answer, answers.answer
                        FROM ' . $TBL_TRACK_ATTEMPT . ' as track_e_attempt
                        INNER JOIN ' . $table_ans . ' as answers
                            ON answers.position = track_e_attempt.answer
                            AND track_e_attempt.question_id=answers.question_id
                        WHERE answers.correct = 0
                        AND track_e_attempt.exe_id = "' . Database::escape_string($id) . '"
                        AND track_e_attempt.question_id = "' . Database::escape_string($questionId) . '"
                        AND track_e_attempt.position="' . Database::escape_string($i_answer_position) . '"';


                    $res_user_answer = api_sql_query($sql_user_answer, __FILE__, __LINE__);
                    if (Database::num_rows($res_user_answer) > 0) {
                        $s_user_answer = Database::fetch_row($res_user_answer, 0, 0);
                    } else {
                        $s_user_answer = '';
                    }

                    //$s_correct_answer = $s_answer_label; // your ddady - your mother
                    $s_correct_answer = $real_list[$i_answer_correct_answer]['answer'];
                    $i_answerWeighting = $a_answers['ponderation']; //$objAnswerTmp->selectWeighting($ind);
                    
                    if ($s_user_answer[0] == $real_list[$i_answer_correct_answer]['id']) { 
                        $questionScore+=$i_answerWeighting;
                        $totalScore+=$i_answerWeighting;
                        if ($answer_wrong == 'N') {
                            $answer_ok = 'Y';
                        }
                    } else {
                       
                        $s_user_answer[1] = ($simplifyQuestionsAuthoring == 'true' AND $Showimageright == '0')? ($s_user_answer[1]) : ('<span style="color: #FF0000; text-decoration: line-through;">' . $s_user_answer[1] . '</span>');
                        $answer_wrong = 'Y';
                    }
                    if ($questionScore > 20) {
                        $questionScore = round($questionScore);
                    }
                    echo '<tr>';
                    $s_answer_label = ($Showimageleft == 0 AND $simplifyQuestionsAuthoring == 'true') ? ('<img src="'.$s_answer_label.'" />') : ($s_answer_label);
                    $s_user_answer[1] = ($Showimageright == 0 AND $simplifyQuestionsAuthoring == 'true') ? ('<img src ="'.$s_user_answer[1].'" />') : ($s_user_answer[1]);
                    $s_correct_answer = ($Showimageright == 0 AND $simplifyQuestionsAuthoring == 'true') ? ('<img src ="'.$s_correct_answer.'" />') : ($s_correct_answer);
                    $stardivleft = ($Showimageleft == 1 AND $simplifyQuestionsAuthoring == 'true') ? '<div style="padding:10px;width:auto;font-size:16px;text-align:left;">' : '';
                    $enddivleft = ($Showimageleft == 1 AND $simplifyQuestionsAuthoring == 'true') ? '</div>' : '';
                    $stardivright = ($Showimageright == 1 AND $simplifyQuestionsAuthoring == 'true') ? '<div style="padding:10px;width:auto;font-size:16px;text-align:left;">' : '';
                    $enddivright = ($Showimageright == 1 AND $simplifyQuestionsAuthoring == 'true') ? '</div>' : '';
                    echo '<td align="center"><div id="matchresult">' . $stardivleft.$s_answer_label .$enddivleft. '</div></td>
                         <td align="center"><div id="matchresult">' .$stardivright. $s_user_answer[1] .$enddivright. '</div></td>
                         <td align="center"><div id="matchresult"><b><span>' . $stardivright.$s_correct_answer .$enddivright. '</span></b></div></td>';
                    echo '</tr>';
                }
                echo '<tfoot>';
                
                if ($answer_ok == 'Y' && $answer_wrong == 'N') {
					echo '<tr ><td colspan="3"><b><div class="feedback-right feed-custom-right">' . get_lang('Feedback') . '</div></b></td></tr><tr>';
                    echo '<td colspan="3" style="border:0px solid #000 !important; padding: 5px 0;"><div class= "feedback-right">' . $answerComment_true . '</div></td>';
                    /*echo '<tr ><td colspan="3" style="border:0px solid #000"><b><div class="feedback-right feed-custom-right" >' . get_lang('Feedback') . '</div></b></td></tr><tr>';
                    echo '<td colspan="3"><div class="feedback-right">' . $answerComment_true . '</div></td>';*/
                } else {
                    echo '<tr ><td colspan="3"><b><div class="feedback-wrong feed-custom-wrong">' . get_lang('Feedback') . '</div></b></td></tr><tr>';
                    echo '<td colspan="3" style="border:0px solid #000 !important; padding: 5px 0;"><div class= "feedback-wrong">' . $answerComment_false . '</div></td>';
                }
                echo '</tr></tfoot></table>';
            } elseif ($answerType == HOT_SPOT) {
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

                $s .= '<table width="100%" border="0"><tr><td><div align="center"><object type="application/x-shockwave-flash" data="../plugin/hotspot/hotspot_solution.swf?modifyAnswers=' . Security::remove_XSS($questionId) . '&exe_id=' . $id . '&from_db=1" width="610" height="410">
                            <param name="movie" value="../plugin/hotspot/hotspot_solution.swf?modifyAnswers=' . Security::remove_XSS($questionId) . '&exe_id=' . $id . '&from_db=1" />
                        </object></div></td><td width="40%" valign="top"><div class="quiz_content_actions quit_border" style="height:400px;overflow:auto;"><div class="quiz_header">' . get_lang('Feedback') . '</div><div align="center"><img src="../img/MouseHotspots64.png"></div><br/>';

                $s .= '<div><table width="90%" border="1" class="data_table">';
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
                    $query = "select hotspot_correct from " . $TBL_TRACK_HOTSPOT . " where hotspot_exe_id = '" . Database::escape_string($id) . "' and hotspot_question_id= '" . Database::escape_string($questionId) . "' AND hotspot_answer_id='" . Database::escape_string($answerId) . "'";
                    $resq = api_sql_query($query);
                    $choice = Database::result($resq, 0, "hotspot_correct");

                    $queryfree = "select marks from " . $TBL_TRACK_ATTEMPT . " where exe_id = '" . Database::escape_string($id) . "' and question_id= '" . Database::escape_string($questionId) . "'";
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
                    $s .= '<tr><td><div style="height:11px; width:11px; background-color:' . $hotspot_colors[$answerId] . '; display:inline; float:left; margin-top:3px;"></div>&nbsp;' . $answerId . '</td><td>' . $answer . '</td><td>' . $img_choice . '</td></tr>';
                }
                
                /*
                $s .= '</table></div><br/><br/>';
                 
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
                    $s .= '<div align="center" class="quiz_feedback"><b>' . get_lang('Feedback') . '</b> : ' . $feedback . '</div>';
                }
                $s .= '</div></td></tr></table>';
                */
                
                
                $s .= '</table><br/><br/>';
                
		 if ($answerOk == 'Y' && $answerWrong == 'N') {
			 if ($nbrAnswers == 1) {
				 $feedback .= '<div class="feedback-right feed-custom-right" style="margin-bottom:5px;">'.get_lang('Feedback').'</div><div class="feedback-right"><span>' . $correctComment[0] . '</span></div>'; 
			 }
			 else {
				 $feedback .= '<div class="feedback-right feed-custom-right" style="margin-bottom:5px;">'.get_lang('Feedback').'</div><div class="feedback-right"><span>' . $correctComment[1] . '</span></div>';  
			 }
		 }
		 else
		 {
			 if ($nbrAnswers == 1){
//                               $feedback = '<span style="font-weight: bold; color: #FF0000;">'.$correctComment[1].'</span>'; 
				 $feedback .= '<div class="feedback-wrong feed-custom-wrong" style="margin-bottom:5px;">'.get_lang('Feedback').'</div><div class="feedback-wrong"><span>' . $correctComment[1] . '</span></div>';
			 }
			 else {
//				 $feedback = '<span style="font-weight: bold; color: #FF0000;">'.$correctComment[2].'</span>'; 
                                 $feedback .= '<div class="feedback-wrong feed-custom-wrong" style="margin-bottom:5px;">'.get_lang('Feedback').'</div><div class="feedback-wrong"><span>' . $correctComment[2] . '</span></div>';
			 }	        
		 }
                 $s .= '</div></td></tr></table>';
                 
		 if(!empty($feedback)){
//                  $s .= '<div align="center" class="quiz_feedback"><b>'.get_lang('Feedback').'</b> : '.$feedback.'</div>';		 
                    $s .= '<div align="center" class="quiz_feedback" style="clear:both; padding-top:20x; margin: 0 5px; width:auto;">'.$feedback.'</div>';		 
                    $feedback ='';
		 }
                
                
                
                
                
                echo $s;
                $totalScore+=$questionScore;
            } else if ($answerType == HOT_SPOT_DELINEATION) {
                $feedback_if_true = $feedback_if_false = '';
                $objAnswerTmp = new Answer($questionId);
                $nbrAnswers = $objAnswerTmp->selectNbrAnswers();
                //$nbrAnswers=1; // based in the code found in exercise_show.php
                $questionScore = 0;
                $totalScoreHotDel = 0;
                //based on exercise_submit modal
                /*  Hot spot delinetion parameters */
                $choice = $exerciseResult[$questionid];
                $destination = array();
                $comment = '';
                $next = 1;
                $_SESSION['hotspot_coord'] = array();
                $_SESSION['hotspot_dest'] = array();
                $overlap_color = $missing_color = $excess_color = false;
                $organs_at_risk_hit = 0;

                $final_answer = 0;
                for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {

                    $answer = $objAnswerTmp->selectAnswer($answerId);
                    $answerComment = $objAnswerTmp->selectComment($answerId);
                    $answerCorrect = $objAnswerTmp->isCorrect($answerId);
                    $answerWeighting = $objAnswerTmp->selectWeighting($answerId);

                    //delineation
                    $answer_delineation_destination = $objAnswerTmp->selectDestination(1);
                    $delineation_cord = $objAnswerTmp->selectHotspotCoordinates(1);

                    if ($answerId === 1) {
                        $_SESSION['hotspot_coord'][1] = $delineation_cord;
                        $_SESSION['hotspot_dest'][1] = $answer_delineation_destination;
                    }

                    // getting the user answer
                    $TBL_TRACK_HOTSPOT = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_HOTSPOT);
                    $query = "select hotspot_correct, hotspot_coordinate from " . $TBL_TRACK_HOTSPOT . " where hotspot_exe_id = '" . Database::escape_string($id) . "' and hotspot_question_id= '" . Database::escape_string($questionId) . "' AND hotspot_answer_id='1'"; //by default we take 1 because it's a delineation
                    $resq = api_sql_query($query);
                    $row = Database::fetch_array($resq, 'ASSOC');
                    $choice = $row['hotspot_correct'];
                    $user_answer = $row['hotspot_coordinate'];

                    $queryfree = "select marks from " . $TBL_TRACK_ATTEMPT . " where exe_id = '" . Database::escape_string($id) . "' and question_id= '" . Database::escape_string($questionId) . "'";
                    $resfree = api_sql_query($queryfree, __FILE__, __LINE__);
                    $questionScore = Database::result($resfree, 0, "marks");
                    $totalScoreHotDel = $questionScore;

                    // THIS is very important otherwise the poly_compile will throw an error!!
                    // round-up the coordinates
                    $coords = explode('/', $user_answer);
                    $user_array = '';
                    foreach ($coords as $coord) {
                        list($x, $y) = explode(';', $coord);
                        $user_array .= round($x) . ';' . round($y) . '/';
                    }
                    $user_array = substr($user_array, 0, -1);

                    if ($next) {
                        //$tbl_track_e_hotspot = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_HOTSPOT);
                        // Save into db
                        /* 	$sql = "INSERT INTO $tbl_track_e_hotspot (hotspot_user_id, hotspot_course_code, hotspot_exe_id, hotspot_question_id, hotspot_answer_id, hotspot_correct, hotspot_coordinate )
                            VALUES ('".Database::escape_string($_user['user_id'])."', '".Database::escape_string($_course['id'])."', '".Database::escape_string($exeId)."', '".Database::escape_string($questionId)."', '".Database::escape_string($answerId)."', '".Database::escape_string($studentChoice)."', '".Database::escape_string($user_array)."')";
                            $result = api_sql_query($sql,__FILE__,__LINE__); */
                        $user_answer = $user_array;

                        // we compare only the delineation not the other points
                        $answer_question = $_SESSION['hotspot_coord'][1];
                        $answerDestination = $_SESSION['hotspot_dest'][1];

                        //calculating the area
                        $poly_user = convert_coordinates($user_answer, '/');
                        $poly_answer = convert_coordinates($answer_question, '|');
                        $max_coord = array('x' => 600, 'y' => 400); //poly_get_max($poly_user,$poly_answer);
                        $poly_user_compiled = poly_compile($poly_user, $max_coord);
                        $poly_answer_compiled = poly_compile($poly_answer, $max_coord);
                        $poly_results = poly_result($poly_answer_compiled, $poly_user_compiled, $max_coord);

                        $overlap = $poly_results['both'];
                        $poly_answer_area = $poly_results['s1'];
                        $poly_user_area = $poly_results['s2'];
                        $missing = $poly_results['s1Only'];
                        $excess = $poly_results['s2Only'];

                        //$overlap = round(polygons_overlap($poly_answer,$poly_user)); //this is an area in pixels
                        if ($dbg_local > 0) {
                            error_log(__LINE__ . ' - Polygons results are ' . print_r($poly_results, 1), 0);
                        }
                        if ($overlap < 1) {
                            //shortcut to avoid complicated calculations
                            $final_overlap = 0;
                            $final_missing = 100;
                            $final_excess = 100;
                        } else {
                            // the final overlap is the percentage of the initial polygon that is overlapped by the user's polygon
                            $final_overlap = round(((float) $overlap / (float) $poly_answer_area) * 100);
                            if ($dbg_local > 1) {
                                error_log(__LINE__ . ' - Final overlap is ' . $final_overlap, 0);
                            }
                            // the final missing area is the percentage of the initial polygon that is not overlapped by the user's polygon
                            $final_missing = 100 - $final_overlap;
                            if ($dbg_local > 1) {
                                error_log(__LINE__ . ' - Final missing is ' . $final_missing, 0);
                            }
                            // the final excess area is the percentage of the initial polygon's size that is covered by the user's polygon outside of the initial polygon
                            $final_excess = round((((float) $poly_user_area - (float) $overlap) / (float) $poly_answer_area) * 100);
                            if ($dbg_local > 1) {
                                error_log(__LINE__ . ' - Final excess is ' . $final_excess, 0);
                            }
                        }

                        //checking the destination parameters parsing the "@@"
                        $destination_items = explode('@@', $answerDestination);
                        $threadhold_total = $destination_items[0];
                        $threadhold_items = explode(';', $threadhold_total);
                        $threadhold1 = $threadhold_items[0]; // overlap
                        $threadhold2 = $threadhold_items[1]; // excess
                        $threadhold3 = $threadhold_items[2];  //missing
                        // if is delineation
                        if ($answerId === 1) {
                            //setting colors
                            if ($final_overlap >= $threadhold1) {
                                $overlap_color = true; //echo 'a';
                            }
                            //echo $excess.'-'.$threadhold2;
                            if ($final_excess <= $threadhold2) {
                                $excess_color = true; //echo 'b';
                            }
                            //echo '--------'.$missing.'-'.$threadhold3;
                            if ($final_missing <= $threadhold3) {
                                $missing_color = true; //echo 'c';
                            }

                            // if pass
                            $feedback = explode("~", $objAnswerTmp->selectComment(1));
                            if ($final_overlap >= $threadhold1 && $final_missing <= $threadhold3 && $final_excess <= $threadhold2) {
                                $next = 1; //go to the oars
                                $result_comment = get_lang('Acceptable');
                                $final_answer = 1; // do not update with  update_exercise_attempt
                                //$comment = '<span style="font-weight: bold; color: #008000;">' . $answerDestination = $objAnswerTmp->selectComment(1) . '</span';
                                $comment = '<tr><td><div class="feedback-right feed-custom-right" style="margin-bottom:5px;">'.get_lang('Feedback').'</div><div class="feedback-right"><span>'. $feedback[0]  .'</span></div></td></tr>';
                            } else {
                                $next = 1; //Go to the oars. If $next =  0 we will show this message: "One (or more) area at risk has been hit" instead of the table resume with the results
                                $result_comment = get_lang('Unacceptable');
                                //$comment = '<span style="font-weight: bold; color: #FF0000;">' . $answerDestination = $objAnswerTmp->selectComment(2) . '</span>';
                                $comment = '<tr><td><div class="feedback-wrong feed-custom-wrong" style="margin-bottom:5px;">'.get_lang('Feedback').'</div><div class="feedback-wrong"><span>'. $feedback[1] .'</span></div></td></tr>';
                                $answerDestination = $objAnswerTmp->selectDestination(1);
                                //checking the destination parameters parsing the "@@"
                                $destination_items = explode('@@', $answerDestination);
                                /*
                                    $try_hotspot=$destination_items[1];
                                    $lp_hotspot=$destination_items[2];
                                    $select_question_hotspot=$destination_items[3];
                                    $url_hotspot=$destination_items[4]; */
                                //echo 'show the feedback';
                            }
                        } elseif ($answerId > 1) {
                            if ($objAnswerTmp->selectHotspotType($answerId) == 'noerror') {
                                if ($dbg_local > 0) {
                                    error_log(__LINE__ . ' - answerId is of type noerror', 0);
                                }
                                //type no error shouldn't be treated
                                $next = 1;
                                continue;
                            }
                            if ($dbg_local > 0) {
                                error_log(__LINE__ . ' - answerId is >1 so we\'re probably in OAR', 0);
                            }
                            //check the intersection between the oar and the user
                            //echo 'user';	print_r($x_user_list);		print_r($y_user_list);
                            //echo 'official';print_r($x_list);print_r($y_list);
                            //$result = get_intersection_data($x_list,$y_list,$x_user_list,$y_user_list);
                            $inter = $result['success'];

                            //$delineation_cord=$objAnswerTmp->selectHotspotCoordinates($answerId);
                            $delineation_cord = $objAnswerTmp->selectHotspotCoordinates($answerId);

                            $poly_answer = convert_coordinates($delineation_cord, '|');
                            $max_coord = poly_get_max($poly_user, $poly_answer);
                            $poly_answer_compiled = poly_compile($poly_answer, $max_coord);
                            $overlap = poly_touch($poly_user_compiled, $poly_answer_compiled, $max_coord);

                            if ($overlap == false) {
                                //all good, no overlap
                                $next = 1;
                                continue;
                            } else {
                                if ($dbg_local > 0) {
                                    error_log(__LINE__ . ' - Overlap is ' . $overlap . ': OAR hit', 0);
                                }
                                $organs_at_risk_hit++;
                                //show the feedback
                                $next = 0;
                                $comment = $answerDestination = $objAnswerTmp->selectComment($answerId);
                                $answerDestination = $objAnswerTmp->selectDestination($answerId);

                                $destination_items = explode('@@', $answerDestination);
                                /*
                                    $try_hotspot=$destination_items[1];
                                    $lp_hotspot=$destination_items[2];
                                    $select_question_hotspot=$destination_items[3];
                                    $url_hotspot=$destination_items[4]; */
                            }
                        }
                    } else { // the first delineation feedback
                        if ($dbg_local > 0) {
                            error_log(__LINE__ . ' first', 0);
                        }
                    }
                } // end for

                if ($overlap_color) {
                    $overlap_color = 'green';
                } else {
                    $overlap_color = 'red';
                }

                if ($missing_color) {
                    $missing_color = 'green';
                } else {
                    $missing_color = 'red';
                }
                if ($excess_color) {
                    $excess_color = 'green';
                } else {
                    $excess_color = 'red';
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

                if ($final_excess > 100) {
                    $final_excess = 100;
                }$totalScore+=$totalScoreHotDel;

                if ($answerType != HOT_SPOT_DELINEATION) {
                    $item_list = explode('@@', $destination);
                    //print_R($item_list);
                    $try = $item_list[0];
                    $lp = $item_list[1];
                    $destinationid = $item_list[2];
                    $url = $item_list[3];
                    $table_resume = '';
                } else {
                    if ($next == 0) {
                        $try = $try_hotspot;
                        $lp = $lp_hotspot;
                        $destinationid = $select_question_hotspot;
                        $url = $url_hotspot;
                    } else {
                        //show if no error
                        //echo 'no error';
                        //	$comment=$answerComment=$objAnswerTmp->selectComment($nbrAnswers);
                        //	$comment=$answerComment=$objAnswerTmp->selectComment(2);
                        $answerDestination = $objAnswerTmp->selectDestination($nbrAnswers);
                    }
                }

                echo '<table width="100%" border="0">';
                echo '<tr><td><object type="application/x-shockwave-flash" data="../plugin/hotspot/hotspot_solution.swf?modifyAnswers=' . $questionId . '&exe_id=' . $id . '&from_db=1" width="610" height="410">
                            <param name="movie" value="../plugin/hotspot/hotspot_solution.swf?modifyAnswers=' . $questionId . '&exe_id=' . $id . '&from_db=1" />

                        </object></td>';
                echo '<td width="40%" valign="top"><div class="quiz_content_actions quit_border" style="height:380px;"><div class="quiz_header">' . get_lang('Feedback') . '</div><p align="center"><img src="../img/mousepolygon64.png"></p><div><table width="100%" border="1" class="data_table"><tr class="row_odd"><td>&nbsp;</td><td>' . get_lang('Requirement') . '</td><td>' . get_lang('YourContour') . '</td></tr><tr class="row_even"><td align="right">' . get_lang('Overlap') . '</td><td align="center">' . get_lang('Min') . ' ' . $threadhold1 . ' %</td><td align="center"><div style="color:' . $overlap_color . '">' . (($final_overlap < 0) ? 0 : intval($final_overlap)) . '</div></td></tr><tr class="row_even"><td align="right">' . get_lang('Excess') . '</td><td align="center">' . get_lang('Max') . ' ' . $threadhold2 . ' %</td><td align="center"><div style="color:' . $excess_color . '">' . (($final_excess < 0) ? 0 : intval($final_excess)) . '</div></td></tr><tr class="row_even"><td align="right">' . get_lang('Missing') . '</td><td align="center">' . get_lang('Max') . ' ' . $threadhold3 . ' %</td><td align="center"><div style="color:' . $missing_color . '">' . (($final_missing < 0) ? 0 : intval($final_missing)) . '</div></td></tr>';

                if ($answerType == HOT_SPOT_DELINEATION) {
                    if ($organs_at_risk_hit > 0) {
                        $message = get_lang('ResultIs') . ' <b>' . $result_comment . '</b>';
                        $message.= '<p style="color:#DC0A0A;"><b>' . get_lang('OARHit') . '</b></p>';
                    } else {
                        $message = '<p>' . get_lang('ResultIs') . ' <b>' . $result_comment . '</b></p>';
                    }

                    echo '<tr><td colspan="3" align="center">' . $message . '</td></tr>';

                    // by default we assume that the answer is ok but if the final answer after calculating the area in hotspot delineation =0 then update
                    if ($final_answer == 0) {
                        $sql = 'UPDATE ' . $TBL_TRACK_ATTEMPT . ' SET answer="", marks = 0 WHERE question_id = ' . $questionId . ' AND exe_id = ' . $exeId;
                        Database::query($sql, __FILE__, __LINE__);
                    }
                } else {
                    //echo '<p>'.$comment.'</p>';
                    echo '<tr><td colspan="3">' . $comment . '</td></tr>';
                }

                echo '</table></div><br/><br/>';
                /*if (!empty($comment)) {
                    echo '<div align="center" class="quiz_feedback"><b>' . get_lang('Feedback') . '</b> : ' . $comment . '</div>';
                }*/
                echo '</div></td></tr>';
                echo '</table>';
                
                
                if (!empty($comment)) {
                    echo '<table style="width:100%;clear:both; padding-top:20px;" border="0">';
                    echo $comment;
                    echo '</table>';        
                }
            }

            echo '<table width="100%" border="0" cellspacing="3" cellpadding="0">';
            if ($is_allowedToEdit && in_array($origin, array('tracking_course', 'user_course')) || in_array($action, array('qualify'))) {
                echo '<tr><td>';
                $name = "fckdiv" . $questionId;
                $marksname = "marksName" . $questionId;
                ?>
                    <br />
                    <a href="javascript://" onclick="showfck('<?php echo $name; ?>','<?php echo $marksname; ?>');">
                    <?php
                    if ($answerType == FREE_ANSWER) {
                        echo get_lang('EditCommentsAndMarks');
                    } else {
                        if ($action == 'edit') {
                            echo Display::return_icon('pixel.gif', get_lang('EditIndividualComment'), array('class' => 'actionplaceholdericon actionedit')) . get_lang('EditIndividualComment');
                        } else {
                            echo get_lang('AddComments');
                        }
                    }
                    echo '</a><br /><div id="feedback_' . $name . '" style="width:100%">';
                    $comnt = trim(get_comments($id, $questionId));
                    if (empty($comnt)) {
                        echo '<br />';
                    } else {
                        echo '<div id="question_feedback">' . $comnt . '</div><br />';
                    }
                    echo '</div><div id="' . $name . '" style="display:none">';
                    $arrid[] = $questionId;

                    $feedback_form = new FormValidator('frmcomments' . $questionId, 'post', '');
                    $feedback_form->addElement('html', '<br>');
                    $renderer = & $feedback_form->defaultRenderer();
                    $renderer->setFormTemplate('<form{attributes}><div align="left">{content}</div></form>');
                    $renderer->setElementTemplate('<div align="left">{element}</div>');
                    $comnt = get_comments($id, $questionId);
                    ${user . $questionId}['comments_' . $questionId] = $comnt;
                    $feedback_form->addElement('html_editor', 'comments_' . $questionId, null, null, array('ToolbarSet' => 'TestAnswerFeedback', 'Width' => '100%', 'Height' => '120'));
                    $feedback_form->addElement('html', '<br>');
                    //$feedback_form->addElement('submit','submitQuestion',get_lang('Ok'));
                    $feedback_form->setDefaults(${user . $questionId});
                    $feedback_form->display();
                    echo '</div>';
                } else {
                    $comnt = get_comments($id, $questionId);
                    echo '<tr><td><br />';
                    if (!empty($comnt)) {
                        echo '<b>' . get_lang('Feedback') . '</b>';
                        echo '<div id="question_feedback">' . $comnt . '</div>';
                    }
                    echo '</td><td>';
                }
                if ($is_allowedToEdit && in_array($origin, array('tracking_course', 'user_course')) || in_array($action, array('qualify'))) {
                    if ($answerType == FREE_ANSWER) {
                        $marksname = "marksName" . $questionId;
                        ?>
                            <div id="<?php echo $marksname; ?>" style="display:none">
                                <form name="marksform_<?php echo $questionId; ?>" method="post" action="">
                            <?php
                            $arrmarks[] = $questionId;
                            echo get_lang("AssignMarks");
                            echo "&nbsp;<select name='marks' id='marks'>";
                            for ($i = 0; $i <= $questionWeighting; $i++) {
                                echo '<option ' . (($i == $questionScore) ? "selected='selected'" : '') . '>' . $i . '</option>';
                            }
                            echo '</select>';
                            echo '</form><br/ ></div>';
                            if ($questionScore == -1) {
                                $questionScore = 0;
                                echo '<br />' . get_lang('notCorrectedYet') . '<br/><br/>';
                            }
                        } else {
                            $arrmarks[] = $questionId;
                            echo '<div id="' . $marksname . '" style="display:none"><form name="marksform_' . $questionId . '" method="post" action="">
                                    <select name="marks" id="marks" style="display:none;"><option>' . $questionScore . '</option></select></form><br/ ></div>';
                        }
                    } else {
                        if ($questionScore == -1) {
                            $questionScore = 0;
                        }
                    }
                    ?>
                            </td>
                            </tr>
                            </table>

                            <div id="question_score" class="sectiontitle quit_border">
                    <?php
                    if ($questionWeighting - $questionScore < 0.50) {
                        $my_total_score = round(float_format($questionScore, 1));
                    } else {
                        $my_total_score = round(float_format($questionScore, 1));
                    }
                    $my_total_weight = float_format($questionWeighting, 1);
                    if ($my_total_score < 0) {
                        $my_total_score = 0;
                    }
                    echo get_lang('Score') . " : $my_total_score/$my_total_weight";
                    echo '</div>';
                    echo '</td></tr></table></div></div>';
                    unset($objAnswerTmp);
                    $i++;
                    $totalWeighting+=$questionWeighting;
                } // end of large foreach on questions
				if(!empty($quiz_final_feedback)){
				echo '<div class="feedback-final"><b>'.get_lang('FinalFeedback').'</b></div>';
				echo '<div class="feedback-final" style="margin-top:5px;">'.$quiz_final_feedback.'</div>';
				}
                /*
                    $sql_update_score= "update ".$TBL_TRACK_EXERCICES." set exe_result ='". round(float_format($totalScore,1))."' where exe_id = '".Database::escape_string($id)."'";
                    $result_final = Database::query($sql_update_score, __FILE__, __LINE__); */
            } //end of condition if $show_results

if (is_array($arrid) && is_array($arrmarks)) {
    $strids = implode(",", $arrid);
    $marksid = implode(",", $arrmarks);
}

echo '<script type="text/javascript">
    $(document).ready(function() {
        $("input[name=quizstatus]").change(function() {
            showmailcontent();
    });
    function showmailcontent() {
        var quizstatus = $("input[name=quizstatus]:checked").val();
        if(quizstatus == 1) {
        $("#successmailcontent").show();
        $("#failuremailcontent").hide();
        } else {
        $("#failuremailcontent").show();
        $("#successmailcontent").hide();
        }
    }
    });

</script>';
$is_allowedToEdit = api_is_allowed_to_edit();
echo '<div style="padding:0px 0px 20px 0px;">';
if ($is_allowedToEdit) {
    if (in_array($origin, array('tracking_course', 'user_course'))) {
        $form = new FormValidator('myform', 'post', 'exercice.php?' . api_get_cidreq() . '&comments=update&exeid=' . $id . '&test=' . urlencode($test) . '&emailid=' . $emailId . '&origin=' . $origin . '&student=' . Security::remove_XSS($_GET['student']).'&exerciseId='.$exerciseId . '&details=true&course=' . Security::remove_XSS($_GET['cidReq']));

        $form->addElement('hidden', 'totalWeighting', $totalWeighting);

        if (isset($_GET['myid']) && isset($_GET['my_lp_id']) && isset($_GET['student'])) {
            $form->addElement('hidden', 'lp_item_id', Security::remove_XSS($_GET['myid']));
            $form->addElement('hidden', 'lp_item_view_id', Security::remove_XSS($_GET['my_lp_id']));
            $form->addElement('hidden', 'student_id', Security::remove_XSS($_GET['student']));
            $form->addElement('hidden', 'total_score', $totalScore);
            $form->addElement('hidden', 'total_time', Security::remove_XSS($_GET['total_time']));
            $form->addElement('hidden', 'my_exe_exo_id', Security::remove_XSS($_GET['my_exe_exo_id']));
        }
    } else {
        $form = new FormValidator('myform', 'post', 'exercice.php?' . api_get_cidreq() . '&comments=update&exeid=' . $id . '&test=' . $test . '&emailid=' . $emailId . '&totalWeighting=' . $totalWeighting.'&exerciseId='.$exerciseId);
    }
    if ($origin != 'learnpath' && $origin != 'student_progress' && $origin != 'author') {

        $success_content = getMailContent('success');
        $failure_content = getMailContent('failure');
        ?>
        <script type="text/javascript">
            function showcontent(){
                document.getElementById('mailcontent').style.display = 'block';
                document.getElementById('hidecontent').style.display = 'block';
                document.getElementById('viewcontent').style.display = 'none';
            }
            function hidecontent(){
                document.getElementById('mailcontent').style.display = 'none';
                document.getElementById('hidecontent').style.display = 'none';
                document.getElementById('viewcontent').style.display = 'block';
            }
        </script>
        <?php
        //echo 'id'.$exerciseId;
        if ($_REQUEST['action'] == 'qualify' || $origin == 'tracking_course') {
            $form->addElement('html', '<div> ');
            $form->addElement('checkbox', 'send_mail', '', get_lang('SendMail'), '1');
            $form->addElement('textarea', 'notes', get_lang('Notes'), 'id="notes" cols="93" rows="10"');
            $form->addElement('html', ' </div>');
            $form->addElement('html', '<div class="formw"><div class="formw1"> ');
            $form->addElement('radio', 'quizstatus', '', get_lang('Showsuccesscontent'), 1);
            $form->addElement('radio', 'quizstatus', '', get_lang('Showfailurecontent'), 2);
            $form->addElement('html', ' </div></div><br/>');
            
            $form->addElement('html', '<div id="successmailcontent" style="display:block;">');
            $form->add_html_editor('success', '', false, false, array('ToolbarSet' => 'TestProposedAnswer', 'Width' => '700px', 'Height' => '200px'));
            $form->addElement('html', '</div>');
            $form->addElement('html', '<div id="failuremailcontent" style="display:none;">');
            $form->add_html_editor('failure', '', false, false, array('ToolbarSet' => 'TestProposedAnswer', 'Width' => '700px', 'Height' => '200px'));
            $form->addElement('html', '</div>');
            $form->addElement('html','<div class="btn-quiz-submit-bottom"></div>');
            $form->addElement('html', '<div class="pull-bottom"><button type="submit" class="save" value="' . get_lang('Ok') . '" onclick="getFCK(\'' . $strids . '\',\'' . $marksid . '\')">' . get_lang('FinishTest') . '</button></div>');

            $defaults = array();
            $defaults['send_mail'] = 1;
            $defaults['quizstatus'] = 1;
            $defaults['success'] = $success_content;
            $defaults['failure'] = $failure_content;
            $form->setDefaults($defaults);
            
            if ($origin == 'student_progress') { 
                $form->addElement('html', '<button type="button" class="back" onclick="window.back();" value="' . get_lang('Back') . '" >' . get_lang('Back') . '</button>');
            } else if ($origin == 'myprogress') { 
                $form->addElement('html', '<button type="button" class="save" onclick="top.location.href=\'../auth/my_progress.php?course=' . api_get_course_id() . '\'" value="' . get_lang('Finish') . '" >' . get_lang('Finish') . '</button>');
            }
            $form->display();
            ?>

            <?php
        }
    }
}
echo '</div>';
echo '</div>';

if (($origin != 'learnpath') || ($origin == 'learnpath' && isset($_GET['fb_type']))) { 
    if ($show_score) {
        echo '<div id="question_score" class="actions" style="min-height:30px;font-weight:bold;position:relative;">';
        if ($certif_available && $origin != 'learnpath') {
            echo '<a class="certificate-' . $exercise_id . '-link" href="#">' . Display::return_icon('certificate48x48.png', get_lang('GetCertificate'), array('style' => 'position:absolute;top:0px;right:10px;')) . '</a>';
            $obj_certificate->displayCertificate('html', 'quiz', $exercise_id, $course_code, null, true);
        }
        $obj = new Exercise();
        $obj->resetRandomOrder($exercise_id,0);               
        echo '<p style="font-size:20px;margin:0px;">'.get_lang('YourTotalScore') . ' : ';
        if ($dsp_percent == true) {
            $my_result = number_format(($totalScore / $totalWeighting) * 100, 1, '.', '');
            $my_result = float_format($my_result, 1);
            echo $my_result . "%";
        } else {
            $my_result = number_format(($totalScore / $totalWeighting) * 100, 1, '.', '');
            $my_result = float_format($my_result, 0);
            
            $my_total_score = round($totalScore);
            $my_total_weight = float_format($totalWeighting, 1);
            echo $my_total_score . "/" . $my_total_weight." ($my_result%)"."</p>";
        }
       
        $lp = new learnpath();
        //$lp->debug=3;
        $courseInfo = api_get_course_info();
        $lp->learnpath($course_code,$learnpath_id,api_get_user_id());
        $exercise_id_old = $exercise_id;
        $exercise_id = $_REQUEST["exerciseid"];
        $lpTotalItemsCount = $lp->get_total_items_count();
        
        echo '<input type="hidden" id="fromTool" value="'.$origin.'">';

        if((isset($_REQUEST['activity_id']) && $_REQUEST['activity_id'] > 0) || (isset($_REQUEST['tool']) && $_REQUEST['tool'] == 'scenario')){          
        $sessionID =(isset($_GET['id_session'])) ? ('?id_session='.$_GET['id_session']) : 'index.php';
			echo '<script>                            
                                        					
				var script = document.createElement("script");
				script.innerHTML = "function goto (href) { window.parent.location.href = href }";			        
        
				var continueContainer = document.createElement("div");				
				
				var isLastSlide
                           
				if(window.name==""){     
                                    var pwc = document.body;
					var style="margin-bottom: 10px; margin-right: 30px;float: right;right: 0;bottom: 0";

					var fromTool = document.getElementById("fromTool").getAttribute("value");
					if(fromTool == "author"){
					var fflastslide = parent.document.getElementById("isLastSlide").getAttribute("value");
					}
					else {
						var fflastslide = 0;
					}
				}
				else{                                    
					var iframe = parent.document.getElementById(window.name);
					var parentContainer = iframe.parentElement.getAttribute("id");
					var pwc = parent.document.getElementById(parentContainer);
					var style="margin-bottom: 10px; margin-right: 10px;float: right;right: 0;bottom: 0";
					
					isLastSlide = parent.document.getElementById("isLastSlide").getAttribute("value");
					
				}
				
				if((window.name=="" && fromTool =="") || (window.name =="" && fromTool =="author" && fflastslide == "1") || isLastSlide=="1")								
				{                                        
					continueContainer.setAttribute("style",style);
					
					
					var alink = document.createElement("a");
					//var onclick = "goto(\''.api_get_path(WEB_COURSE_PATH).$courseInfo['path'].'/index.php'.'\')";
					//var onclick = "window.parent.location.href = \"'.api_get_path(WEB_COURSE_PATH).$courseInfo['path'].'/'.$sessionID.'\"";
					alink.setAttribute("onclick","goto(\''.api_get_path(WEB_COURSE_PATH).$courseInfo['path'].'/'.$sessionID.'\')");
			
					var btnContinue = document.createElement("button");
					//style="font-size: 18px;";
					btnContinue.setAttribute("style","font-size: 18px;");
					btnContinue.setAttribute("class","continue");
					btnContinue.setAttribute("id","continue");
					btnContinue.innerHTML = "'.get_lang('Continue').'";				
				
				
					pwc.appendChild(script);
					
					alink.appendChild(btnContinue);
					continueContainer.appendChild(alink);                                        
					pwc.appendChild(continueContainer);
					
				}
        </script>';
		}
        
        
        
        //echo "<script> function goto (href) { window.parent.location.href = href }</script>";
        
        //if(($origin != 'learnpath' && $origin != 'author') /*|| $lpTotalItemsCount == $exercise_id || $lpTotalItemsCount == 1*/)
		//	echo '<div style="position: absolute; bottom: 0; right: 0; margin-bottom: 5px;"><a onclick="goto(\''.api_get_path(WEB_COURSE_PATH).$courseInfo['path'].'/index.php'.'\')"><button style="background-color: green;color: white; width: 120px; height: 40px; font-size: 18px; border-radius: 8px;" class="save">'.get_lang('Continue').'</button></a></div>';
        //echo '</div>';
        $exercise_id = $exercise_id_old;
       
    }
}

if ($origin != 'learnpath' && $origin != 'author') { 
	$TBL_SCENARIO_ACTIVITY_VIEW = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY_VIEW);
	$TBL_SCENARIO_STEPS = Database :: get_course_table(TABLE_SCENARIO_STEPS);
	$step_id = $_REQUEST['step'];
	$activity_id = $_REQUEST['activity_id'];

	$sql_step = "SELECT step_completion_option, step_completion_percent FROM ".$TBL_SCENARIO_STEPS." WHERE id = ".$step_id;
	$res_step = Database::query($sql_step, __FILE__, __LINE__);
	$step_completion_option = Database::result($res_step, 0, 0);
	$step_completion_percent = Database::result($res_step, 0, 1);
	if(strpos($step_completion_option,'@') !== false){
		list($option,$sub_option) = split("@",$step_completion_option);
	}
	else {
		$option = $step_completion_option;
	}

	$sql_check = "SELECT * FROM $TBL_SCENARIO_ACTIVITY_VIEW WHERE step_id = ".$step_id." AND activity_id = ".$activity_id." AND user_id = ".api_get_user_id();
	$res_check = Database::query($sql_check, __FILE__, __LINE__);
	$num_rows = Database::num_rows($res_check);
	$score_percent = round(($my_total_score / $my_total_weight)*100);
	if(trim($option) == 'Quiz' && $score_percent >= $step_completion_percent){
		$status = 'completed';
	}
	else if(trim($option) != 'Quiz'){
		$status = 'completed';
	}
	else {
		$status = 'notcompleted';
	}

	if($num_rows == 0) {
		$sql = "INSERT INTO $TBL_SCENARIO_ACTIVITY_VIEW (activity_id, step_id, user_id, view_count, score, status) VALUES($activity_id, $step_id, ".api_get_user_id().", 1, '".$score_percent."', '".$status."')";
	}
	else {
		$sql = "UPDATE $TBL_SCENARIO_ACTIVITY_VIEW SET view_count = view_count + 1, score = '".$score_percent."', status = '".$status."' WHERE activity_id = ".$activity_id." AND step_id = ".$step_id." AND user_id = ".api_get_user_id();
	}

	Database::query($sql,__FILE__,__LINE__);
    //we are not in learnpath tool
    //Display::display_footer();
} else {
    
    if (!isset($_GET['fb_type'])) {
        if ($origin != 'author') {
            $lp_mode = $_SESSION['lp_mode'];
            $url = '../newscorm/lp_controller.php?cidReq=' . api_get_course_id() . '&action=view&lp_id=' . $learnpath_id . '&lp_item_id=' . $learnpath_item_id . '&exeId=' . $exeId . '&fb_type=' . $feedback_type . '&switch=item';          
            $href = ($lp_mode == 'fullscreen') ? ' window.opener.location.href="' . $url . '" ' : ' top.location.href="' . $url . '" ';
            echo '<script language="javascript">window.parent.API.void_save_asset(' . round($totalScore) . ',' . round($totalWeighting) . ');</script>' . "\n";
//            echo '<script language="javascript">' . $href . '</script>' . "\n";
        }
        else {
            $href = api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=PlayerAjax&func=saveLpQuiz&'.api_get_cidreq().'&lpId='.$learnpath_id.'&lpItemId='.$learnpath_item_id.'&from=quiz&exeId='.$exeId;           
            echo '<script language="javascript"> window.parent.saveLpQuiz("'.$href.'"); </script>' . "\n";
        }
        //record the results in the learning path, using the SCORM interface (API)
        echo '</body></html>';
    } else {
        //Display::display_normal_message(get_lang('ExerciseFinished'));
        $_SESSION["display_normal_message"]=get_lang('ExerciseFinished');
    }
}

function getMailContent($quizresult) {
    global $language_interface, $_configuration;

	if ($_configuration['multiple_access_urls'] == true) {
		$access_url_id = api_get_current_access_url_id();
	}
	else {
		$access_url_id = 1;
	}

    if ($quizresult == 'success') {
        $description = "Quizsuccess";
    } elseif ($quizresult == 'failure') {
        $description = "Quizfailure";
    }
    $table_emailtemplate = Database::get_main_table(TABLE_MAIN_EMAILTEMPLATES);
    $sql = "SELECT * FROM $table_emailtemplate WHERE description = '" . $description . "' AND language= '" . $language_interface . "' AND access_url = ".$access_url_id;
    $result = api_sql_query($sql, __FILE__, __LINE__);
    while ($row = Database::fetch_array($result)) {
        $content = $row['content'];
    }
    
    $domain_server = api_get_path(WEB_SERVER_ROOT_PATH) . "main/default_course_document";
    $content = str_replace("/main/default_course_document", "tmp_file", $content);
    
    if (empty($content)) {
        $content = get_lang('DearStudentEmailIntroduction') . "\n\n";
        $content .= get_lang('AttemptVCC') . "\n\n";
        if ($quizresult == 'success') {
            $content .= get_lang('Quizsuccess') . "\n\n";
        } else {
            $content .= get_lang('Quizfailure') . "\n\n";
        }
        $content .= get_lang('Question') . ": {ques_name} \n";
        $content .= get_lang('Exercice') . " :{test} \n\n";
        $content .= get_lang('ClickLinkToViewComment') . " - {url} \n\n";
        $content .= get_lang('Notes') . "\n\n";
        $content .= "{notes} \n\n";
        $content .= get_lang('Regards') . "\n\n";
        $content .= "{administratorSurname} \n";
        $content .= get_lang('Manager') . "\n";
        $content .= "{administratorTelephone} \n";
        $content .= get_lang('Email') . " : {emailAdministrator}";
    }
    $content = str_replace("tmp_file", $domain_server, $content);
    
    return $content;
}

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
        <td width="5%" align="center">
            <img src="../img/' . $your_choice . '"
            border="0" alt="" />
        </td>
        <td width="5%" align="center">
            <img src="../img/' . $expected_choice . '"
            border="0" alt=" " />
        </td>
        <td width="40%" style="border-bottom: 1px solid #4171B5;">' . api_parse_tex($answer) . '
        </td>
        </tr>';
    return $s;
}

//destroying the session
api_session_unregister('questionList');
unset($questionList);

api_session_unregister('exerciseResult');
unset($exerciseResult);

