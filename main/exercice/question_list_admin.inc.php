<?php // $Id: question_list_admin.inc.php 20810 2009-05-18 21:16:22Z cfasanando $

/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2004-2008 Dokeos SPRL
	Copyright (c) 2003 Ghent University (UGent)
	Copyright (c) 2001 Universite catholique de Louvain (UCL)
	Copyright (c) various contributors

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
*	Code library for HotPotatoes integration.
*	@package dokeos.exercise
* 	@author
* 	@version $Id: question_list_admin.inc.php 20810 2009-05-18 21:16:22Z cfasanando $
*/


/**
==============================================================================
*	QUESTION LIST ADMINISTRATION
*
*	This script allows to manage the question list
*	It is included from the script admin.php
*
*	@author Olivier Brouckaert
*	@package dokeos.exercise
==============================================================================
*/
global $charset;
// ALLOWED_TO_INCLUDE is defined in admin.php
if(!defined('ALLOWED_TO_INCLUDE'))
{
	exit();
}

// moves a question up in the list
if(isset($_GET['moveUp']))
{
	$objExercise->moveUp(intval($_GET['moveUp']));
	$objExercise->save();
}

// moves a question down in the list
if(isset($_GET['moveDown']))
{
	$objExercise->moveDown(intval($_GET['moveDown']));
	$objExercise->save();
}

if(isset($_GET['action']) && $_GET['action'] == 'changeCategory') {
  $sql = "UPDATE $TBL_QUESTIONS SET category='" . Database::escape_string(Security::remove_XSS($_GET['category'])) . "' WHERE id = " . Database::escape_string(Security::remove_XSS($_GET['question_id']));
  $res = Database::query($sql, __FILE__, __LINE__);
}

// deletes a question from the exercise (not from the data base)
if($deleteQuestion)
{

	// if the question exists
	if($objQuestionTmp = Question::read($deleteQuestion))
	{
		$objQuestionTmp->delete($exerciseId);
                    
		// if the question has been removed from the exercise
		if($objExercise->removeFromList($deleteQuestion))
		{
			$nbrQuestions--;
		}

		//If question is removed from exercise and if the exercise is incomplete by any user removing the question from the exercise tracking of that user.

		$track_questionlist = array();
		$new_questionlist = array();

		$stat_table = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
		$sql = "SELECT * FROM $stat_table WHERE exe_exo_id = $exerciseId AND exe_user_id = ".api_get_user_id()." AND exe_cours_id = '".api_get_course_id()."' AND status = 'incomplete'";
		$result = Database::query($sql, __FILE__, __LINE__);
		$nbrQuestions = Database::num_rows($result);
		if($nbrQuestions > 0){
		while ($row = Database::fetch_array($result)) {
			$data = $row['data_tracking'];
		}
		}

		$track_questionlist = explode(",",$data);
		$new_questionlist = remove_element($track_questionlist,$deleteQuestion);

		$data_arr = implode(",", $new_questionlist);
		$sql = "UPDATE $stat_table SET data_tracking = '".$data_arr."' WHERE exe_exo_id = $exerciseId AND exe_user_id = ".api_get_user_id()." AND exe_cours_id = '".api_get_course_id()."' AND status = 'incomplete'";
		api_sql_query($sql, __FILE__, __LINE__);
	}

	// destruction of the Question object
	unset($objQuestionTmp);
        $_SESSION["display_confirmation_message"] = get_lang('QuizQuestionDeleted');
        echo '<script type="text/javascript">window.location.href="admin.php?'. api_get_cidreq().'&exerciseId='.Security::remove_XSS($_GET['exerciseId']).'"</script>';
        exit();
}

if(isset($_REQUEST['updatelevel']))
{
	$sql = "UPDATE $TBL_QUESTIONS SET level='" . Database::escape_string(Security::remove_XSS($_REQUEST['updatelevel'])) . "' WHERE id='" . Database::escape_string(Security::remove_XSS($_REQUEST['no'])) . "'";
    api_sql_query($sql, __FILE__, __LINE__);
}

$evaluation_link = '';
if (isset($_GET['origin']) && $_GET['origin'] == 'evaluation') {
    $evaluation_link = '&origin=evaluation&examId='.intval($_GET['examId']);
}

echo '<script type="text/javascript">function level(questionlevel,no)
	  {
		  window.location.href="'.api_get_self().'?'.api_get_cidReq().'&updatelevel="+questionlevel+"&no="+no+"&exerciseId='.$exerciseId.$evaluation_link.'";

	  }</script>';

if (!isset($feedbacktype)) $feedbacktype=0;
if ($feedbacktype==1) {
    $url = 'question_pool.php?type=1&fromExercise='.$exerciseId;
} else {
    $url = 'question_pool.php?fromExercise='.$exerciseId;
}

Question :: display_type_menu ($objExercise->feedbacktype);
$move_lang_var = api_convert_encoding(get_lang('Move'), $charset, api_get_system_encoding());
$modify_lang_var = api_convert_encoding(get_lang('Modify'), $charset, api_get_system_encoding());
$question_lang_var = api_convert_encoding(get_lang('Question'), $charset, api_get_system_encoding());
$type_lang_var = api_convert_encoding(get_lang('Type'), $charset, api_get_system_encoding());
$level_lang_var = api_convert_encoding(get_lang('Level'), $charset, api_get_system_encoding());
if(isset($_SESSION['display_confirmation_message'])){
display::display_confirmation_message2($_SESSION['display_confirmation_message'], false,true);
unset($_SESSION['display_confirmation_message']);
}
?>
<div id="content">
<?php
/*
 * ===============================================
 * DISPLAY MESSAGE
 * ===============================================
 */
if(isset($_SESSION['display_normal_message'])){
display::display_normal_message($_SESSION['display_normal_message'], false,true);
unset($_SESSION['display_normal_message']);
}
if(isset($_SESSION['display_warning_message'])){
display::display_warning_message($_SESSION['display_warning_message'], false,true);
unset($_SESSION['display_warning_message']);
}
if(isset($_SESSION['display_error_message'])){
display::display_error_message($_SESSION['display_error_message'], false,true);
unset($_SESSION['display_error_message']);
}
?>
<table class="data_table data_table_exercise" id="table_question_list" style="width:100%">
	<tr>
		<th width="8%"><?php echo $move_lang_var; ?></th>
		<th width="8%"><?php echo $modify_lang_var; ?></th>
		<th align="left" width="35%"><?php echo $question_lang_var; ?></th>
		<th width="9%"><?php echo $type_lang_var;?></th>
		<th width="9%"><?php echo $level_lang_var; ?></th>
		<?php
		if(api_get_setting('show_quizcategory') == 'true'){
		?>
		<th width="15%"><?php echo get_lang('Category'); ?></th>
		<?php
		}
		?>
		<th width="8%"><?php echo get_lang('Delete'); ?></th>
		
	</tr></table>

<?php
//This is a temporary fix
$questionList = array();
$TBL_EXERCICES = Database::get_course_table(TABLE_QUIZ_TEST, $db_name);
$TBL_QUESTIONS = Database::get_course_table(TABLE_QUIZ_QUESTION, $db_name);
$TBL_EXERCICE_QUESTION = Database::get_course_table(TABLE_QUIZ_TEST_QUESTION, $db_name);
$TBL_QUIZ_TYPE = Database::get_course_table(TABLE_QUIZ_TYPE, $db_name);

$quiz_category = 0;
$nbrQuestions = 0;

if(api_get_setting('show_quizcategory') == 'true'){
$sql_scenario = "SELECT count(*) FROM $TBL_QUIZ_TYPE WHERE exercice_id = ".Database::escape_string(Security::remove_XSS($_REQUEST['exerciseId']))." AND current_active = 1";

$rs_scenario = Database::query($sql_scenario, __FILE__, __LINE__);
$quiz_category = Database::result($rs_scenario, 0);
}

if($quiz_category <> 0) {
    
$sql = "SELECT category_id,quiz_level,number_of_question,scenario_type FROM $TBL_QUIZ_TYPE WHERE exercice_id = ".Database::escape_string(Security::remove_XSS($_REQUEST['exerciseId'])). " AND current_active = 1";
$result = Database::query($sql, __FILE__, __LINE__);

while($row = Database::fetch_array($result))
{
	$scenario_type = $row['scenario_type'];
	$sql_in = "SELECT DISTINCT(question.id) AS id FROM $TBL_EXERCICES quiz, $TBL_QUESTIONS question, $TBL_EXERCICE_QUESTION rel_question, $TBL_QUIZ_TYPE quiz_type WHERE quiz.id=rel_question.exercice_id AND rel_question.question_id = question.id AND quiz.id = quiz_type.exercice_id AND rel_question.exercice_id = quiz_type.exercice_id AND question.level = ".$row['quiz_level']." AND question.category = ".$row['category_id']." ORDER BY rel_question.question_order LIMIT ".$row['number_of_question'];
	$result_in = Database::query($sql_in, __FILE__, __LINE__);
	$num_questions = Database::num_rows($result_in);
	if($num_questions <> 0){
	while ($row_in = Database::fetch_array($result_in)) {
	$questionList[] = $row_in[0];
	}
	}
	$nbrQuestions = $nbrQuestions + $num_questions;
}
}
else
{ 
   
	$sql = "SELECT question.id FROM $TBL_EXERCICES quiz, $TBL_QUESTIONS question, $TBL_EXERCICE_QUESTION rel_question WHERE quiz.id=rel_question.exercice_id AND rel_question.question_id = question.id AND quiz.id=".Database::escape_string(Security::remove_XSS($_REQUEST['exerciseId']))." ORDER BY rel_question.question_order";
	
        $result = Database::query($sql, __FILE__, __LINE__);
	$nbrQuestions = Database::num_rows($result);
	while ($row = Database::fetch_array($result)) {
		$questionList[] = $row[0];
	}
}

/*$sql = "SELECT question.id FROM $TBL_EXERCICES quiz, $TBL_QUESTIONS question, $TBL_EXERCICE_QUESTION rel_question WHERE quiz.id=rel_question.exercice_id AND rel_question.question_id = question.id AND quiz.id=".Database::escape_string(Security::remove_XSS($_REQUEST['exerciseId']))." ORDER BY rel_question.question_order";
$result = Database::query($sql, __FILE__, __LINE__);
$nbrQuestions = Database::num_rows($result);
while ($row = Database::fetch_array($result)) {
	$questionList[] = $row[0];
}*/
if($nbrQuestions) {   
echo '<div id="contentWrap"><div id="contentLeft"><ul class="dragdrop nobullets " id="categories">';
	$i=1;
	if (is_array($questionList)) {
		foreach($questionList as $id) {
			echo "<script type='text/javascript'>
			$(function() {
				$('#quiz_category_".$id."').change(function() {
				var category = $(this).val();
				var question_id = $('input[name=\'question_id_".$id."\']').attr('value');
				window.location.href='".api_get_self().'?'.api_get_cidReq()."&action=changeCategory&category='+category+'&question_id='+question_id+'&exerciseId=".$exerciseId.$evaluation_link."';
				});
			});
			</script>";
			$objQuestionTmp = Question :: read($id);
			echo '<tr><td>';
		    echo '<li id="recordsArray_' . $id . '" class="category">';
		    echo '<div>';
		    echo '<table class="" width="100%">';

			//showQuestion($id);
		?>
		<!--	<tr id="quiz_row_<?php echo $id ?>_<?php echo $objExercise->id ?>" <?php if($i%2==0) echo 'class="row_odd"'; else echo 'class="row_even"'; ?>>-->
				<tr <?php if($i%2==0) echo 'class="row_odd"'; else echo 'class="row_even"'; ?>>
				<td align="center" width="8%" style="cursor:pointer">
				<?php
                                    echo Display::return_icon('pixel.gif', get_lang('Move'), array('class' => 'actionplaceholdericon actionsdraganddrop'));
                                 ?>
                                </td>
				<td class="nodrag" align="center" width="8%">
				<?php
				if(!isset($_SESSION['fromlp'])) {
                    $question_type = $objQuestionTmp->selectType();
					?>
					<a href="<?php echo api_get_self(); ?>?myid=1&type=<?php echo $question_type;?>&editQuestion=<?php echo $id; ?>&<?php echo api_get_cidreq();?>&exerciseId=<?php echo $objExercise->id.$evaluation_link; ?>">
					<?php
				} else {
					?>
					<a href="<?php echo api_get_self(); ?>?myid=1&fromTpl=1&editQuestion=<?php echo $id; ?>&<?php echo api_get_cidreq().$evaluation_link ?>">
					<?php
				}
                    echo Display::return_icon('pixel.gif', get_lang('Modify'), array('class' => 'actionplaceholdericon actionedit'));
				?>
              </a>
              </td>
		<td align="left" class="nodrag" width="35%" style="text-align: left!important;">
                    <?php
                        $question_title = trim($objQuestionTmp->selectTitle());
                        if(preg_match('</audioplayer>',$question_title)){
                            $question_title = preg_replace('/<audioplayer(.*?)<\/audioplayer>/is', "", $question_title);
                            echo strip_tags($question_title);
                            echo '<img title="Audio" src="'.  api_get_path(WEB_PATH).'main/img/mid.gif" />';
                        }else{
                            if(preg_match('</jwvideo>', $question_title)){
                            $question_title = preg_replace('/<jwvideo(.*?)<\/jwvideo>/is', "", $question_title);
                            echo strip_tags($question_title);                                
                                echo '<img title="Video" src="'.  api_get_path(WEB_PATH).'main/img/mpeg.gif" />';
                            }else{
                                // Clean embed and jwvideo tags
                                $question_title = preg_replace('/<jwvideo(.*?)<\/jwvideo>/is', "", $question_title);
                                $question_title = preg_replace('/<embed(.*?)<\/embed>/is', "", $question_title);
                                if (!empty($question_title)) {
                                    echo strip_tags($question_title, '<br>');
                                } else {
                                    echo '&nbsp;';
                                }
                            }
                        }
                    ?>
                </td>
				<td class="nodrag" align="center" width="9%"><?php
     eval('$explanation=get_lang('.get_class($objQuestionTmp).'::$explanationLangVar);');
    switch (get_class($objQuestionTmp)) {
      case 'UniqueAnswer':
     //   echo Display::return_icon('multiple_choice_medium.png', $explanation);
		  echo Display::return_icon('pixel.gif', $explanation, array('class' => 'quizactionplaceholdericon quiz_multiple_choice_list'));
      break;
      case 'MultipleAnswer':
      //  echo Display::return_icon('multiple_answer_medium.png', $explanation);
		  echo Display::return_icon('pixel.gif', $explanation, array('class' => 'quizactionplaceholdericon quiz_multiple_answer_list'));
      break;
      case 'FillBlanks':
      //  echo Display::return_icon('fill_in_the_blank_medium.png', $explanation);
		  echo Display::return_icon('pixel.gif', $explanation, array('class' => 'quizactionplaceholdericon quiz_fillups_list'));
      break;
      case 'Matching':
      //  echo Display::return_icon('drag_drop_medium.png', $explanation);
		  echo Display::return_icon('pixel.gif', $explanation, array('class' => 'quizactionplaceholdericon quiz_matching_list'));
      break;
      case 'FreeAnswer':
      //  echo Display::return_icon('open_question_medium.png', $explanation);
		  echo Display::return_icon('pixel.gif', $explanation, array('class' => 'quizactionplaceholdericon quiz_openquestion_list'));
      break;
      case 'Reasoning':
      //  echo Display::return_icon('reasoning_medium.png', $explanation);
		  echo Display::return_icon('pixel.gif', $explanation, array('class' => 'quizactionplaceholdericon quiz_reasoning_list'));
      break;
      case 'HotSpot':
      //  echo Display::return_icon('hotspots_medium.png', $explanation);
		  echo Display::return_icon('pixel.gif', $explanation, array('class' => 'quizactionplaceholdericon quiz_hotspot_list'));
      break;
	  case 'HotSpotDelineation':
      //  echo Display::return_icon('delineation_medium.png', $explanation);
		  echo Display::return_icon('pixel.gif', $explanation, array('class' => 'quizactionplaceholdericon quiz_contour_list'));
      break;
    }
    ?>
    </td>
			 <td class="nodrag" align="center" width="9%">
				<?php
				$level = $objQuestionTmp->selectLevel();
				$category = $objQuestionTmp->selectCategory();
                                

				if($level == '1' || $level == '0') {
					$level = '<div  class="level_style_general" onclick="level(\'4\',\''.$id.'\');" title="Advanced"></div><div class="level_style_general" onclick="level(\'3\',\''.$id.'\');" title="Intermediate"></div><div class="level_style_general" onclick="level(\'2\',\''.$id.'\');" title="Beginner"></div><div class="level_style_prerequestie" onclick="level(\'1\',\''.$id.'\');" title="Prerequestie"></div>';
				}
				if($level == '2') {
					$level = '<div class="level_style_general" onclick="level(\'4\',\''.$id.'\');" title="Advanced"></div><div class="level_style_general" onclick="level(\'3\',\''.$id.'\');" title="Intermediate"></div><div class="level_style_beginner" onclick="level(\'2\',\''.$id.'\');" title="Beginner"></div><div class="level_style_prerequestie" onclick="level(\'1\',\''.$id.'\');" title="Prerequestie"></div>';
				}
				if($level == '3') {
					$level = '<div class="level_style_general" onclick="level(\'4\',\''.$id.'\');" title="Advanced"></div><div class="level_style_intermediate" onclick="level(\'3\',\''.$id.'\');" title="Intermediate"></div><div class="level_style_beginner" onclick="level(\'2\',\''.$id.'\');" title="Beginner"></div><div class="level_style_prerequestie" onclick="level(\'1\',\''.$id.'\');" title="Prerequestie"></div>';
				}
				if($level == '4') {
					$level = '<div class="level_style_advanced" onclick="level(\'4\',\''.$id.'\');" title="Advanced"></div><div class="level_style_intermediate" onclick="level(\'3\',\''.$id.'\');" title="Intermediate"></div><div class="level_style_beginner" onclick="level(\'2\',\''.$id.'\');" title="Beginner"></div><div class="level_style_prerequestie" onclick="level(\'1\',\''.$id.'\');" title="Prerequestie"></div>';
				}
				echo $level;
				?>
				</td>
				<?php 
                                
                                $session_id = api_get_session_id();
                                $session_condition = 'session_id ='.$session_id;
                                if($session_id > 0){
                                        $session_condition = $session_condition . ' OR session_id = 0';
                                }
                               
                                
                                
				if(api_get_setting('show_quizcategory') == 'true'){
				 ?>
				<td class="nodrag" align="center" width="15%">

					<select name="quiz_category_<?php echo $id; ?>" id="quiz_category_<?php echo $id; ?>">
					<option <?php if($category == '0') echo 'selected'; ?>>Select</option>
					<?php
					$TBL_QUIZ_CATEGORY = Database::get_course_table(TABLE_QUIZ_CATEGORY);
					$sql = "SELECT * FROM $TBL_QUIZ_CATEGORY WHERE $session_condition";
                                        
					$result = api_sql_query($sql, __FILE__, __LINE__);
					while($row = Database::fetch_array($result))
					{
						$category_id = $row['id'];
						$category_title = $row['category_title'];
						if($category == $category_id) {
						  echo '<option value = "'.$category_id.'" selected>'.$row['category_title'].'</option>';
						} else {
                                                  echo '<option value = "'.$category_id.'">'.$row['category_title'].'</option>';
						}
					}
					?>
					</select>
					<input type="hidden" name="question_id_<?php echo $id; ?>" value="<?php echo $id; ?>" />
			    </td>
				<?php
				}
				?>
			 <td class="nodrag" align="center"  width="8%">
                             <?php  
                             echo '<a href="javascript:void(0);" onclick="Alert_Confim_Delete( \''.api_get_self().'?deleteQuestion='.$id.'&'. api_get_cidreq().'&exerciseId='.$exerciseId.$evaluation_link.'\',\''.get_lang("ConfirmationDialog").'\',\''.get_lang("ConfirmYourChoice").'\');">'
                                     . Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')).
					 '</a>'
                                     ?>
<?php
/*
                                        <a href="<?php echo api_get_self(); ?>?deleteQuestion=<?php echo $id; ?>&<?php echo api_get_cidreq()?>&exerciseId=<?php  echo $objExercise->id.$evaluation_link;?>" onclick="javascript:if(!confirm('<?php echo addslashes(api_htmlentities(get_lang('ConfirmYourChoice'))); ?>')) return false;"><?php
					 echo Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete'));
					 ?></a>
 */
?>
			    </td>
				 
      <?php
				$i++;
				unset($objQuestionTmp);
			    ?>
			</tr></table></div></li></td></tr>
				<?php
		}
	}
	echo '</ul></div></div>';
}
?>


<?php
if(!$i) {
	?>
	<table class="data_table" border="0" align="center" cellpadding="2" cellspacing="2" width="100%">
	<tr>
  	<td><?php echo get_lang('NoQuestion'); ?></td>
	</tr></table>
<?php
}
?>

</div>
<?php
function remove_element($track_questionlist,$deleteQuestion)
{
	foreach($track_questionlist as $key => $value) {
	if ($value == $deleteQuestion) unset($track_questionlist[$key]);
	}
	return $track_questionlist;
}