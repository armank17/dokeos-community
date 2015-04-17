<?php
//$id:$
/* For licensing terms, see /dokeos_license.txt */
//error_log(__FILE__);
/**
 * 	This script allows to manage answers. It is included from the script admin.php
 * 	@package dokeos.exercise
 * 	@author Toon Keppens
 * 	@version $Id: admin.php 10680 2007-01-11 21:26:23Z pcool $
 */
require_once(api_get_path(LIBRARY_PATH) . 'text.lib.php');
require_once(api_get_path(LIBRARY_PATH) . 'image.lib.php');
global $picturePath, $_course, $_user, $TBL_REPONSES;

// ALLOWED_TO_INCLUDE is defined in admin.php
if (!defined('ALLOWED_TO_INCLUDE')) {
    exit();
}

$TBL_EXERCICE_QUESTION = Database::get_course_table(TABLE_QUIZ_TEST_QUESTION);
$TBL_QUESTIONS = Database::get_course_table(TABLE_QUIZ_QUESTION);

if (isset($_REQUEST['exerciseId'])) {
    $exerciseId = $_REQUEST['exerciseId'];
}

if (isset($_REQUEST['fromExercise'])) {
    $exerciseId = $_REQUEST['fromExercise'];
}

if (isset($_REQUEST['fromTpl'])) {
    $from_tpl = $_REQUEST['fromTpl'];
}

if (isset($_REQUEST['editQuestion']) && !empty($_REQUEST['editQuestion'])) {
    $editQuestion = $_REQUEST['editQuestion'];
    $edit = 'Y';
}

if (isset($_REQUEST['hotspotadmin']) && !empty($_GET['hotspotadmin'])) {
    $modifyAnswers = (int) $_GET['hotspotadmin'];
} else {
    $modifyAnswers = $editQuestion;
}

$min_overlap = 80;
$max_excess = 10;
$max_missing = 10;

if ($from_tpl == 1) {
    $sql = "INSERT INTO $TBL_QUESTIONS (question,description,ponderation,type,level,category) SELECT question,description,ponderation,type,level,category FROM $TBL_QUESTIONS WHERE id = " . Database::escape_string($editQuestion);
    $result = api_sql_query($sql);

    $insert_id = Database::get_last_insert_id();

    $modifyAnswers = $insert_id;

    $source_picture = 'quiz-' . $editQuestion . '.jpg';
    $picture = 'quiz-' . $insert_id . '.jpg';
    copy($picturePath . '/' . $source_picture, $picturePath . '/' . $picture);

    $document_id = add_document($_course, '/images/' . $picture, 'file', filesize($picturePath . '/' . $picture), $picture);
    if ($document_id) {
        api_item_property_update($_course, TOOL_QUIZ, $document_id, 'QuizAdded', $_user['user_id']);
    }

    $sql = "SELECT max(position) FROM $TBL_QUESTIONS as question, $TBL_EXERCICE_QUESTION as test_question WHERE question.id=test_question.question_id AND test_question.exercice_id='" . Database::escape_string($exerciseId) . "'";
    $result = api_sql_query($sql);
    $current_position = Database::result($result, 0, 0);
    $position = $current_position + 1;

    $sql = "UPDATE $TBL_QUESTIONS SET position = '" . $position . "', picture = '" . $picture . "' WHERE id = " . Database::escape_string($insert_id);
    $result = api_sql_query($sql);

    $sql = "SELECT max(question_order) AS last_order FROM $TBL_EXERCICE_QUESTION WHERE exercice_id='" . Database::escape_string($exerciseId) . "' ";
    $res = Database::query($sql, __FILE__, __LINE__);
    $row = Database::fetch_object($res);
    // Next question order
    $next_order = $row->last_order + 1;
    // Save new question to quiz
    
    if(!empty($insert_id)){
		$sql = "INSERT INTO $TBL_EXERCICE_QUESTION (question_id, exercice_id, question_order) VALUES('" . Database::escape_string($insert_id) . "','" . Database::escape_string($exerciseId) . "','" . Database::escape_string($next_order) . "')";
		Database::query($sql, __FILE__, __LINE__);
	}
    $sql = "SELECT count(*) AS answers FROM $TBL_REPONSES WHERE question_id = " . Database::escape_string($editQuestion);
    $res = Database::query($sql, __FILE__, __LINE__);
    $row = Database::fetch_object($res);
    // Next question order
    $nbrAnswers = $row->answers;
    for ($i = 1; $i <= $nbrAnswers; $i++) {
        $sql = "SELECT * FROM $TBL_REPONSES WHERE question_id = " . Database::escape_string($editQuestion) . " AND id = " . $i;
        $result = Database::query($sql, __FILE__, __LINE__);
        while ($row = Database::fetch_array($result)) {
            $sql = "INSERT INTO $TBL_REPONSES(id,question_id,answer,correct,comment,ponderation,position,hotspot_coordinates,hotspot_type) VALUES(" . $i . "," . Database::escape_string($insert_id) . ",'" . $row['answer'] . "'," . $row['correct'] . ",'" . $row['comment'] . "'," . $row['ponderation'] . "," . $row['position'] . ",'" . Database::escape_string($row['hotspot_coordinates']) . "','" . $row['hotspot_type'] . "')";

            $res = Database::query($sql, __FILE__, __LINE__);
        }
    }

    echo '<script type="text/javascript">window.location.href = "admin.php?' . api_get_cidreq() . '&editQuestion=' . $modifyAnswers . '&fromExercise=' . $exerciseId . '&answerType='.$type_hotspost_delineation.'&lp_id=' . $lp_id . '"</script>';
}

if (isset($_POST['upload'])) {
    if (!empty($modifyAnswers)) {
        $sql = "DELETE FROM $TBL_QUESTIONS WHERE id=" . $modifyAnswers;
        $result = api_sql_query($sql);

        $sql = "DELETE FROM $TBL_EXERCICE_QUESTION WHERE question_id=" . $modifyAnswers;
        $result = api_sql_query($sql);
    }
    $question = $_POST['questionName'];
    $lp_id = $_POST['lp_id'];
    $type = $type_hotspost_delineation;

    $weighting = 0;
    $level = 1;
    $fileName = $_FILES['imageUpload']['name'];
    $tmpName = $_FILES['imageUpload']['tmp_name'];

    if (!empty($fileName)) {

//	Question :: uploadPicture($baseName,$fileName);

        $sql = "SELECT max(id) FROM $TBL_QUESTIONS";
        $result = api_sql_query($sql);
        $max_id = Database::result($result, 0, 0);
        $current_id = $max_id + 1;

        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $picture = 'quiz-' . $current_id . '.jpg';

        if ($extension == 'gif' || $extension == 'png') {
            $o_img = new image($tmpName);
            $o_img->send_image('JPG', $picturePath . '/' . $picture);
            $document_id = add_document($_course, '/images/' . $picture, 'file', filesize($picturePath . '/' . $picture), $picture);
        } else {
            if (move_uploaded_file($tmpName, $picturePath . '/' . $picture)) {
                $document_id = add_document($_course, '/images/' . $picture, 'file', filesize($picturePath . '/' . $picture), $picture);
            }
        }

        // resize picture
        $image = new Image($picturePath . '/' . $picture);
        $image->resize(600, 400, 0);
        $image->send_image('JPG', $picturePath . '/' . $picture);

        if ($document_id) {
            api_item_property_update($_course, TOOL_QUIZ, $document_id, 'QuizAdded', $_user['user_id']);
        }

        $sql = "SELECT max(position) FROM $TBL_QUESTIONS as question, $TBL_EXERCICE_QUESTION as test_question WHERE question.id=test_question.question_id AND test_question.exercice_id='" . Database::escape_string($exerciseId) . "'";
        $result = api_sql_query($sql);
        $current_position = Database::result($result, 0, 0);
        $position = $current_position + 1;

        $sql = "INSERT INTO $TBL_QUESTIONS(question,description,ponderation,position,type,picture,level) VALUES(
					'" . Database::escape_string(Security::remove_XSS($question)) . "',
					'" . Database::escape_string(Security::remove_XSS(api_html_entity_decode($description), COURSEMANAGERLOWSECURITY)) . "',
					'" . Database::escape_string($weighting) . "',
					'" . Database::escape_string($position) . "',
					'" . Database::escape_string($type) . "',
					'" . Database::escape_string($picture) . "',
					'" . Database::escape_string($level) . "'
					)";
        api_sql_query($sql, __FILE__, __LINE__);

        $insert_id = Database::get_last_insert_id();

        api_item_property_update($_course, TOOL_QUIZ, $insert_id, 'QuizQuestionAdded', $_user['user_id']);

        $sql = "SELECT max(question_order) AS last_order FROM $TBL_EXERCICE_QUESTION WHERE exercice_id='" . Database::escape_string($exerciseId) . "' ";
        $res = Database::query($sql, __FILE__, __LINE__);
        $row = Database::fetch_object($res);
        // Next question order
        $next_order = $row->last_order + 1;
        // Save new question to quiz
//        $sql = "INSERT INTO $TBL_EXERCICE_QUESTION (question_id, exercice_id, question_order) VALUES('" . Database::escape_string($insert_id) . "','" . Database::escape_string($exerciseId) . "','" . Database::escape_string($next_order) . "')";
//        Database::query($sql, __FILE__, __LINE__);

        echo '<script type="text/javascript">window.location.href = "' . api_get_self() . '?' . api_get_cidReq() . '&newQuestion=yes&hotspotadmin=' . $insert_id . '&exerciseId=' . $exerciseId . '&answerType='.$type_hotspost_delineation.'&lp_id=' . $lp_id . '"</script>';
    } //Not empty filename(upload image)
}

if ((isset($_GET['hotspotadmin']) && !empty($_GET['hotspotadmin'])) || isset($editQuestion)) {
    if (!is_object($objQuestion)) {
        $objQuestion = Question :: read($modifyAnswers);
    }
    if (!is_object($objQuestion)) {
        $objQuestion = Question::getInstance(HOT_SPOT);
    }
    $questionName = $objQuestion->selectTitle();
    $answerType = $objQuestion->selectType();
    $pictureName = $objQuestion->selectPicture();


    $debug = 0; // debug variable to get where we are

    $okPicture = empty($pictureName) ? false : true;
}

if (isset($_POST['submitAnswers'])) {
    $questionName = $_POST['questionName'];
    $questionId = $_POST['questionId'];
    $lp_id = $_POST['lp_id'];
    $insert_id = $_POST['insert_id'];
    $next_order = $_POST['next_order'];
    $questionWeighting = $nbrGoodAnswers = 0;

    for ($i = 1; $i <= $nbrAnswers; $i++) {
        if ($debug > 0) {
            echo str_repeat('&nbsp;', 4) . '$answerType is HOT_SPOT' . "<br />\n";
        }

        $reponse[$i] = trim($reponse[$i]);
        $comment[$i] = trim($comment[$i]);
        $weighting[$i] = $weighting[$i]; // it can be float
        // checks if field is empty
        if (empty($reponse[$i]) && $reponse[$i] != '0') {
            if ($i == 1) {
                $msgErr = get_lang('HotspotGiveAnswers');
            }

            // clears answers already recorded into the Answer object
            $objAnswer->cancel();
            break;
        }

        if ($weighting[$i] <= 0) {
            if ($i == 1) {
                $msgErr = get_lang('HotspotWeightingError');
            }
            // clears answers already recorded into the Answer object
            $objAnswer->cancel();
            break;
        }

        if ($hotspot_coordinates[$i] == '0;0|0|0' || empty($hotspot_coordinates[$i])) {
            if ($i == 1) {
                $msgErr = get_lang('HotspotNotDrawn');
            }
            // clears answers already recorded into the Answer object
            $objAnswer->cancel();
            break;
        }
    }

    if (empty($msgErr)) {

        // delineation infos
        $min_overlap = intval($_POST['min_overlap']);
        $max_excess = intval($_POST['max_excess']);
        $max_missing = intval($_POST['max_missing']);

        for ($i = 1; $i <= $nbrAnswers; $i++) {
            if ($debug > 0) {
                echo str_repeat('&nbsp;', 4) . '$answerType is HOT_SPOT' . "<br />\n";
            }
            if ($nbrAnswers == 1) {
                $comment[$i] = $comment[1] . '~' . $comment[2];
            }
            $reponse[$i] = trim($reponse[$i]);
            $comment[$i] = trim($comment[$i]);
            $weighting[$i] = ($weighting[$i]); //it can be float
            if (!empty($reponse[$i])) {
                if ($weighting[$i]) {
                    $questionWeighting+=$weighting[$i];
                }
                // creates answer
                $destination = $min_overlap.';'.$max_excess.';'.$max_missing.'@@0@@0@@0@@0'; // don't know what the last characters refers to...
                $objAnswer->createAnswer($reponse[$i], '', $comment[$i], $weighting[$i], $i, $hotspot_coordinates[$i], $hotspot_type[$i], $destination);
            }
        }  // end for()
        // saves the answers into the data base
        $sql = "SELECT max(question_order) AS last_order FROM $TBL_EXERCICE_QUESTION WHERE exercice_id='" . Database::escape_string($exerciseId) . "' ";
        $res = Database::query($sql, __FILE__, __LINE__);
        $row = Database::fetch_object($res);
        // Next question order
        $next_order = $row->last_order + 1;
        if(!empty($insert_id)){
			$sql = "INSERT INTO $TBL_EXERCICE_QUESTION (question_id, exercice_id, question_order) VALUES('" . Database::escape_string($insert_id) . "','" . Database::escape_string($exerciseId) . "','" . Database::escape_string($next_order) . "')";
			Database::query($sql, __FILE__, __LINE__);
		}
        
        $objAnswer->save();

        $sql = "UPDATE $TBL_QUESTIONS SET
					question 	='" . Database::escape_string($questionName) . "',
					ponderation	='" . Database::escape_string($questionWeighting) . "'
				WHERE id='" . Database::escape_string($questionId) . "'";
        api_sql_query($sql, __FILE__, __LINE__);

        unset($modifyAnswers);
        if (!isset($_SESSION['fromlp'])) {

            echo '<script type="text/javascript">parent.location.href="admin.php?popup=1&exerciseId=' . $exerciseId . '&lp_id=' . $lp_id . '"</script>';
        } else {
            echo '<script type="text/javascript">window.location.href="admin.php?fromlp=Y&exerciseId=' . $exerciseId . '&lp_id=' . $lp_id . '"</script>';
        }
    }
}//End of submitanswer
// construction of the Answer object
$objAnswer = new Answer($objQuestion->id);

api_session_register('objAnswer');

if ($debug > 0) {
    echo str_repeat('&nbsp;', 2) . '$answerType is HOT_SPOT' . "<br />\n";
}

$TBL_ANSWERS = Database::get_course_table(TABLE_QUIZ_ANSWER);

if (!$nbrAnswers) {
    if (count($objAnswer->answer) >= 1) {
        // Number answers
        $nbrAnswers = count($objAnswer->answer);
    } else {
        $nbrAnswers = 3;
    }

    $reponse = Array();
    $comment = Array();
    $weighting = Array();
    $hotspot_coordinates = Array();
    $hotspot_type = array();

    for ($i = 1; $i <= $nbrAnswers; $i++) {
        $reponse[$i] = $objAnswer->selectAnswer($i);
        $comment[$i] = $objAnswer->selectComment($i);
        $weighting[$i] = $objAnswer->selectWeighting($i);
        $hotspot_coordinates[$i] = $objAnswer->selectHotspotCoordinates($i);
        $hotspot_type[$i] = $objAnswer->selectHotspotType($i);
        if ($hotspot_coordinates[$i] == '') {
            $hotspot_coordinates[$i] = '0;0|0|0';
        }
        if ($hotspot_type[$i] == '') {
            $hotspot_type[$i] = 'oar';
        }
        if ($nbrAnswers == 1) {
            list($comment1, $comment2) = explode("~", $comment[$i]);
            $comment[1] = $comment1;
            $comment[2] = $comment2;
        }
        if($i == 1)
        {
        	$destination = $objAnswer->selectDestination($i);
        	if(!empty($destination))
        	{
        		list($delineation_infos) = explode('@@', $destination);
        		list($min_overlap, $max_excess, $max_missing) = explode(';',$delineation_infos);
        	}
        }
    }
} else {
    $nbrAnswers = isset($_POST['nbrAnswers']) ? (int) $_POST['nbrAnswers'] : 1;
}

$_SESSION['tmp_answers'] = array();
$_SESSION['tmp_answers']['answer'] = $reponse;
$_SESSION['tmp_answers']['comment'] = $comment;
$_SESSION['tmp_answers']['weighting'] = $weighting;
$_SESSION['tmp_answers']['hotspot_coordinates'] = $hotspot_coordinates;
$_SESSION['tmp_answers']['hotspot_type'] = $hotspot_type;

if (isset($_POST['lessAnswers'])) {
    // At least 1 answer
    if ($nbrAnswers > 1) {
        $nbrAnswers--;
        // Remove the last answer
        $tmp = array_pop($_SESSION['tmp_answers']['answer']);
        $tmp = array_pop($_SESSION['tmp_answers']['comment']);
        $tmp = array_pop($_SESSION['tmp_answers']['weighting']);
        $tmp = array_pop($_SESSION['tmp_answers']['hotspot_coordinates']);
        $tmp = array_pop($_SESSION['tmp_answers']['hotspot_type']);
    } else {
        $msgErr = get_lang('MinHotspot');
    }
}

if (isset($_POST['moreAnswers'])) {
    if ($nbrAnswers < 12) {
        $nbrAnswers++;
        // Add a new answer
        $_SESSION['tmp_answers']['answer'][] = '';
        $_SESSION['tmp_answers']['comment'][] = '';
        $_SESSION['tmp_answers']['weighting'][] = '1';
        $_SESSION['tmp_answers']['hotspot_coordinates'][] = '0;0|0|0';
        if($type_hotspot_delineation == 6)
        	$_SESSION['tmp_answers']['hotspot_type'][] = 'square';
        else
        	$_SESSION['tmp_answers']['hotspot_type'][] = 'oar';
    } else {
        $msgErr = get_lang('MaxHotspot');
    }
}

if ($debug > 0) {
    echo str_repeat('&nbsp;', 2) . '$usedInSeveralExercises is untrue' . "<br />\n";
}


if ($debug > 0) {
    echo str_repeat('&nbsp;', 4) . '$answerType is HOT_SPOT' . "<br />\n";
}
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
<div class="actions">
    <div class="warning-message" style="padding:10px;"><?php echo get_lang('ThisQuestionWillNotWorkInDeviceNotSupportFlash'); ?></div>
<?php
if (!empty($msgErr)) {
    //Display::display_normal_message($msgErr); //main API
    $_SESSION["display_normal_message"]=get_lang($msgErr);
}
$insert_id = $_GET['hotspotadmin'];
$next_order = $_GET['next_order'];
if ($editQuestion != '') {
    $action = api_get_self() . '?' . 'newQuestion=yes&' . api_get_cidreq() . '&answerType='.$type_hotspost_delineation.'&exerciseId=' . $exerciseId . '&hotspotadmin=' . $modifyAnswers . '&edit=Y';
} else {
    $action = api_get_self() . '?' . 'newQuestion=yes&' . api_get_cidreq() . '&answerType='.$type_hotspost_delineation.'&exerciseId=' . $exerciseId . '&hotspotadmin=' . $modifyAnswers;
}
?>
    <form method="post" action="<?php echo $action; ?>" name="frm_exercise" id="frm_exe_hotspot_one" enctype="multipart/form-data">
        <table  border="0" cellpadding="2" cellspacing="2" width="100%">
            <tr>
                <td width="55%"><?php echo get_lang('QuestionContour'); ?></td>
                <td width="45%"><?php echo get_lang('UploadImage'); ?></td>
            </tr>
            <tr>
                <td width="55%"><input type="text" name="questionName" size="40"  style="font-size:16px;" value="<?php echo $questionName; ?>" required></td>
                <td width="45%"><input type="file" name="imageUpload"  style="font-size:16px";><input class="button-upload" type="submit" name="upload" value="Upload" style="border:0;font-weight:bold;padding-left:20px;"/></td>
            </tr>
        </table>
        <div style="border-bottom: dashed 1px #000000;"></div>
        <table  border="0" cellpadding="5" cellspacing="5" width="100%">
            <tr>
                <td colspan="2" valign="top" width="55%">
                    <input type="hidden" name="formSent" value="1" />
                    <input type="hidden" name="lp_id" value="<?php echo $lp_id; ?>" />
                    <input type="hidden" name="nbrAnswers" value="<?php echo $nbrAnswers; ?>" /> <input type="hidden" name="questionId" value="<?php echo $modifyAnswers; ?>" />
                    <input type="hidden" name="insert_id" value="<?php echo $insert_id; ?>" />
                    <input type="hidden" name="next_order" value="<?php echo $next_order ?>" />
                </td>
            </tr>
            <tr>
                <td colspan="2">
    <?php
    $_SESSION['dbName'] = $_course['dbName'];
    $_SESSION['language'] = $_course['language'];
    $_SESSION['sysCode'] = $_course['sysCode'];
    $_SESSION['path'] = $_course['path'];
    echo '<input type="hidden" value="'.$type_hotspost_delineation.'" name="type_hotspost" />';
    $hotspost_delineation = ($type_hotspost_delineation == 6) ? 'hotspot_admin.swf' : 'hotspot_delineation_admin.swf';
    if ($_REQUEST['hotspotadmin'] == '' && intval($editQuestion) == 0) {
        echo '<div class="quiz_content_actions hotspotzone"><p style="padding-top:80px;">600 x 400</p></div>';
    } else {
        ?>
                        <script type="text/javascript">
                            <!--
                            // Version check based upon the values entered above in "Globals"
                            var hasReqestedVersion = DetectFlashVer(requiredMajorVersion, requiredMinorVersion, requiredRevision);


                            // Check to see if the version meets the requirements for playback
                            if (hasReqestedVersion) {  // if we've detected an acceptable version
                                var oeTags = '<object type="application/x-shockwave-flash" data="../plugin/hotspot/<?php echo $hotspost_delineation; ?>?modifyAnswers=<?php echo $modifyAnswers; ?>" width="610" height="490">'
                                    + '<param name="movie" value="../plugin/hotspot/<?php echo $hotspost_delineation; ?>?modifyAnswers=<?php echo $modifyAnswers; ?>" />'
                                    + '<param name="test" value="OOoowww fo shooww" />'
                                    + '</object>';
                                document.write(oeTags);   // embed the Flash Content SWF when all tests are passed
                            } else {  // flash is too old or we can't detect the plugin
                                var alternateContent = 'Error<br \/>'
                                    + 'This content requires the Macromedia Flash Player.<br \/>'
                                    + '<a href=http://www.macromedia.com/go/getflash/>Get Flash<\/a>';
                                document.write(alternateContent);  // insert non-flash content
                            }
                            // -->
                        </script>
                        <?php
                    }
                    ?>
                </td>
                <td colspan="2" valign="top">
                		<div class="quiz_content_actions">
					<div class="quiz_header"><?php echo get_lang('ContourAccuracy'); ?></div><br/>
					<table width="90%" border="0" align="center">
					<tr>
					<td align="right">
					<?php echo get_lang('MinOverlap')?>
					</td>
					<td align="center">
					<select name="min_overlap">
										<?php for($i=0 ; $i<=100 ; $i++):?>
										<option <?php if($i==$min_overlap) echo 'selected' ?>><?php echo $i ?></option>
										<?php endfor; ?>
										</select>
										%
					</td>
					<td>
					<img src="../img/overlap22.png">
					</td>
					</tr>
					<tr>
					<td align="right">
					<?php echo get_lang('MaxExcess')?>
					</td>
					<td align="center">
					<select name="max_excess">
										<?php for($i=0 ; $i<=100 ; $i++):?>
										<option <?php if($i==$max_excess) echo 'selected' ?>><?php echo $i ?></option>
										<?php endfor; ?>
										</select>
										%
					</td>
					<td>
					<img src="../img/excess22.png">
					</td>
					</tr>
					<tr>
					<td align="right">
					<?php echo get_lang('MaxMissing')?>
					</td>
					<td align="center">
					<select name="max_missing">
										<?php for($i=0 ; $i<=100 ; $i++):?>
										<option <?php if($i==$max_missing) echo 'selected' ?>><?php echo $i ?></option>
										<?php endfor; ?>
										</select>
										%
					</td>
					<td>
					<img src="../img/missing22.png">
					</td>
					</tr>
					<tr>
					<td align="right">
					<?php echo get_lang('Weighting')?>
					</td>
					<td align="left" style="padding-left:13px;">
					<select name="weighting[1]">
					<?php
					if(isset($weighting[1])){
						$weighting = float_format($weighting[1], 1);
					}
					else {
						$weighting = 10;
					}
					for($i=0 ; $i<=10 ; $i++):?>
										<option <?php if($i==$weighting) echo 'selected' ?>><?php echo $i ?></option>
										<?php endfor; ?>
										</select>
					</td>
					<td>&nbsp;
					</td>
					</tr>
					</table>
					</div><br/>
					<div class="quiz_content_actions">
					<input type="hidden" name="reponse[1]" value="delineation" />
                                        <input type="hidden" name="hotspot_coordinates[1]" value="<?php echo (empty($hotspot_coordinates[1]) ? '0;0|0|0' : $hotspot_coordinates[1]); ?>" />
                                        <input type="hidden" name="hotspot_type[1]" value="<?php echo (empty($hotspot_type[1]) ? 'square' : $hotspot_type[1]); ?>" />
					<div class="quiz_header"><?php echo get_lang('OptionalZones'); ?></div><br/>
					<table width="90%" border="0" align="center">
					<?php

                    for ($i = 2; $i <= $nbrAnswers; $i++) {
                        $class = ($i % 2 == 0) ? 'row_odd' : 'row_even';
                        ?>
                            <tr  class="<?php echo $class; ?> ">
                                <td height="30" valign="top"><div style="height: 15px; width: 15px; background-color: <?php echo $hotspot_colors[$i]; ?>"> </div></td>
                                <td height="30" align="left" valign="top"><input type="text" name="reponse[<?php echo $i; ?>]"  value="<?php echo api_htmlentities($reponse[$i], ENT_QUOTES, api_get_system_encoding()); ?>" size="15" style='font-size:16px;' />
                                <!--<td align="left"><?php echo $return; ?></td>-->
                                <input type="hidden" name="weighting[<?php echo $i; ?>]" value="0" />
                                    <input type="hidden" name="hotspot_coordinates[<?php echo $i; ?>]" value="<?php echo (empty($hotspot_coordinates[$i]) ? '0;0|0|0' : $hotspot_coordinates[$i]); ?>" />
                                    <input type="hidden" name="hotspot_type[<?php echo $i; ?>]" value="<?php echo (empty($hotspot_type[$i]) ? 'square' : $hotspot_type[$i]); ?>" /></td>
                            </tr>
						<?php
					}
					?>
					<tr>
					<td colspan="2" align="right">
					<?php
					$navigator_info = api_get_navigator();
					//ie6 fix
					if ($navigator_info['name'] == 'Internet Explorer') {
						?>
					<input type="submit" name="lessAnswers" class="minus button_less" value="" />
                                    <input type="submit" name="moreAnswers" class="plus button_more" value="" />
					 <?php
					} else {
						?>
					<input type="submit"  name="lessAnswers" class="minus button_less" value="" />
                                    <input type="submit"  name="moreAnswers" class="plus button_more" value="" />
					</td>
					<?php
                        }
                        ?>
					</tr>
					<?php
					if ($_REQUEST['hotspotadmin'] == '' && intval($editQuestion) == 0) {
						echo '</table><br/><br/><br/><br/>';
					}
					else {
						echo '</table><br/><br/><br/><br/><br/><br/><br/><br/>';
					}
					?>
					</div>
				</td>
            </tr>
            <tr>
                <td colspan="4" valign="top" >
                    <div class="coast2coast" style="text-aligne:left;">
<?php echo get_lang('ContourFeedbackIfTrue'); ?><br/>
                        <?php
                            $editor_config = array('ToolbarSet' => 'TestProposedFeedback', 'Width' => '420', 'Height' => '70', 'FullPage' => false, 'InDocument' => true);
                            api_disp_html_area('comment[1]', $comment[1], '', '', '', $editor_config);
                        ?>
                    </div>
                    <div class="coast2coast" style="text-aligne:left;">
                        <?php echo get_lang('ContourFeedbackIfFalse'); ?><br/>
                        <?php api_disp_html_area('comment[2]', $comment[2], '', '', '', $editor_config); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2" valign="top" >&nbsp;</td>
                <td colspan="2" valign="top" >
                    <div align="right">
<?php
$navigator_info = api_get_navigator();
//ie6 fix
if ($navigator_info['name'] == 'Internet Explorer' && $navigator_info['version'] == '6') {
    ?>
                                    <input type="submit" class="save" name="submitAnswers" value="<?php echo get_lang('Ok'); ?>" />
    <?php
} else {
    ?>
                                    <button type="submit" class="save" name="submitAnswers" value="<?php echo get_lang('Ok'); ?>" ><?php echo get_lang('Validate'); ?></button>
                            <?php
                        }
                        ?>
                           </div></td>
            </tr>
        </table>
    </form>
                        <?php
                        if ($debug > 0) {
                            echo str_repeat('&nbsp;', 0) . '$modifyAnswers was set - end' . "<br />\n";
                        }
                        ?>
</div>
