<?php
// $Id: question_pool.php 20451 2009-05-10 12:02:22Z ivantcholakov $

/*
 ==============================================================================
 Dokeos - elearning and course management software

 Copyright (c) 2004-2009 Dokeos SPRL
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
 * 	Question Pool
 * 	This script allows administrators to manage questions and add them into their exercises.
 * 	One question can be in several exercises
 * 	@package dokeos.exercise
 * 	@author Olivier Brouckaert
 * 	@version $Id: question_pool.php 20451 2009-05-10 12:02:22Z ivantcholakov $
 */
// name of the language file that needs to be included
$language_file = 'exercice';

include_once 'exercise.class.php';
include_once 'question.class.php';
include_once 'answer.class.php';
include_once '../inc/global.inc.php';

$htmlHeadXtra[] =  '<script type="text/javascript">
	  $(document).ready(function (){
		  $("#submit_add").click(function () {
			   var len = $("input[type=checkbox]").length;
			   var qnlist = [];
			   for(var i=1;i<=len;i++){
				   if($("#notify_checkbox_"+i).attr("checked")){
					   var chkval = $("#notify_checkbox_"+i).attr("value");
					   qnlist.push(chkval);
				   }
			   }
			   $.post("ajax.php", {action: "reuse", fromExercise: '.$_GET["fromExercise"].', qnlist: qnlist},
					function(data){
						$("#reuse").html("");
						$("#reuse").append(data);
						window.location = "question_pool.php?fromExercise='.$_GET['fromExercise'].'";
				});
		  });

	  });
	  </script>';

$this_section = SECTION_COURSES;

$is_allowedToEdit = api_is_allowed_to_edit();

$TBL_EXERCICE_QUESTION = Database::get_course_table(TABLE_QUIZ_TEST_QUESTION);
$TBL_EXERCICES = Database::get_course_table(TABLE_QUIZ_TEST);
$TBL_QUESTIONS = Database::get_course_table(TABLE_QUIZ_QUESTION);
$TBL_REPONSES = Database::get_course_table(TABLE_QUIZ_ANSWER);

$TBL_QUESTIONS_TEMPLATE = Database::get_main_table(TABLE_MAIN_QUIZ_QUESTION_TEMPLATES);
$TBL_REPONSES_TEMPLATE = Database::get_main_table(TABLE_MAIN_QUIZ_ANSWER_TEMPLATES);


if (empty($delete)) {
	$delete = intval($_GET['delete']);
}
if (empty($recup)) {
	$recup = intval($_GET['recup']);
}

if (empty($fromExercise)) {
	$fromExercise = intval($_GET['fromExercise']);
}
if(isset($_GET['exerciseId'])){
	$exerciseId = intval($_GET['exerciseId']);
}
elseif(isset($_REQUEST['exerciseId']))
{
	$exerciseId = $_REQUEST['exerciseId'];
}
else
{
	$exerciseId = 0;
}
if (isset($_REQUEST['exerciseLevel'])){
	$exerciseLevel = intval($_REQUEST['exerciseLevel']);
        if ($exerciseLevel == 0) {
            $exerciseLevel = -1;
        }
} else {
	$exerciseLevel = -1;
}
if (!empty($_GET['page'])) {
	$page = intval($_GET['page']);
}

if (isset($_SESSION['editQn'])) {
	$_SESSION['editQn'] = '';
}

//only that type of question
if (!empty($_GET['type'])) {
	$type = intval($_GET['type']);
}

if (api_get_setting('enable_pro_settings') !== 'true') {
     header('Location:'.api_get_path(WEB_CODE_PATH).'exercice/admin.php?exerciseId='.$fromExercise.'&'.api_get_cidreq());
    exit;
}


// maximum number of questions on a same page
$limitQuestPage = 10;

// document path
$documentPath = api_get_path(SYS_COURSE_PATH) . $_course['path'] . '/document';

// picture path
$picturePath = $documentPath . '/images';


if (!($objExcercise instanceOf Exercise) && !empty($fromExercise)) {
	$objExercise = new Exercise();
	$objExercise->read($fromExercise);
}
if (!($objExcercise instanceOf Exercise) && !empty($exerciseId)) {
	$objExercise = new Exercise();
	$objExercise->read($exerciseId);
}

if ($is_allowedToEdit) {
	// deletes a question from the data base and all exercises
	if ($delete) {
		// construction of the Question object
		// if the question exists
		if ($objQuestionTmp = Question::read($delete)) {
			// deletes the question from all exercises
			$objQuestionTmp->delete();
		}

		// destruction of the Question object
		unset($objQuestionTmp);
	}
	// gets an existing question and copies it into a new exercise
	elseif ($recup && $fromExercise) {
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
		api_session_register('objExercise');
		header("Location: admin.php?exerciseId=$fromExercise");
		exit();
	}
}

if (isset($_SESSION['gradebook'])) {
	$gradebook = $_SESSION['gradebook'];
}

if (!empty($gradebook) && $gradebook == 'view') {
	$interbreadcrumb[] = array(
     'url' => '../gradebook/' . $_SESSION['gradebook_dest'],
     'name' => get_lang('Gradebook')
	);
}

$nameTools = get_lang('QuestionPool');

$interbreadcrumb[] = array("url" => "exercice.php", "name" => get_lang('Exercices'));

// if admin of course
if ($is_allowedToEdit) {
	Display::display_tool_header($nameTools, 'Exercise');

	 $exercice_id = Security::remove_XSS($_REQUEST['fromExercise']);

	// Main buttons
	 echo '<div class="actions">';
	 echo '<a href="exercice.php?' . api_get_cidreq() . '">' . Display::return_icon('pixel.gif', get_lang('List'), array('class' => 'toolactionplaceholdericon toolactionlist'))  . get_lang('List') . '</a>';
	 echo '<a href="exercise_admin.php?' . api_get_cidreq() . '">' . Display::return_icon('pixel.gif', get_lang('NewEx'), array('class' => 'toolactionplaceholdericon toolactionnewquiz')) . get_lang('NewEx') . '</a>';
	 echo '<a href="admin.php?' . api_get_cidreq() . '&exerciseId=' . $exercice_id . '">' . Display::return_icon('pixel.gif', get_lang('Questions'), array('class' => 'toolactionplaceholdericon toolactionquestion')) . get_lang('Questions') . '</a>';
 	// ND_041010 Deplacement de code
	if (!empty($fromExercise)) {
		if (!isset($_SESSION['fromlp'])) {
			if (isset($_REQUEST['fullWin'])) {
				echo '<a href="admin.php?'. api_get_cidreq().'">' . Display::return_icon('pixel.gif', get_lang('Quiz'), array('class' => 'toolactionplaceholdericon toolactionback')) . get_lang('Quiz'). '</a>';
			} else {
				echo '<a href="admin.php?'. api_get_cidreq().'&exerciseId='.$fromExercise.'">' . Display::return_icon('pixel.gif', get_lang('Quiz'), array('class' => 'toolactionplaceholdericon toolactionback')) . get_lang('Quiz'). '</a>';
			}
		} else {
			echo '<a href="admin.php?'. api_get_cidreq().'">' . Display::return_icon('pixel.gif', get_lang('Quiz'), array('class' => 'toolactionplaceholdericon toolactionback')) . get_lang('Quiz'). '</a>';
		}
	} else {
		echo '<a href="admin.php?'. api_get_cidreq(). '&newQuestion=yes">' . Display::return_icon('pixel.gif', get_lang('NewQu'), array('class' => 'toolactionplaceholdericon toolactionback')).get_lang('NewQu') . '</a>';
	}
	if (isset($type)) {
		$url = api_get_self() . '?type=1';
	} else {
		$url = api_get_self();
	}
	// ND_041010
?>
<div class="sectionquizpool float_r">
<form name="qnpool" method="get" action="<?php echo $url; ?>" style="display:inline;">
 <?php
	if (isset($type)) {
		echo '<input type="hidden" name="type" value="1">';
	}
	?>
	<input type="hidden" name="fromExercise" value="<?php echo $fromExercise; ?>">

	<?php echo get_lang('Templates'); ?> :
	<select name="exerciseId" id="cbo-templates" onchange="javascript:document.qnpool.submit();">
	<option value="0">-- <?php echo get_lang('Templates'); ?> --</option>
	<?php
	$sql="SELECT id,title FROM $TBL_EXERCICES WHERE id<>'".Database::escape_string($fromExercise)."' AND active<>'-1' ORDER BY id ";
	$result=api_sql_query($sql,__FILE__,__LINE__);

	// shows a list-box allowing to filter questions
	while($row=Database::fetch_array($result)) {
		?>
		<option value="<?php echo $row['id']; ?>" <?php if($exerciseId == $row['id']) echo 'selected="selected"'; ?>><?php echo $row['title']; ?></option>
		<?php
	}
	?>
    </select>
    &nbsp;
    <?php
    	echo get_lang('Difficulty');
    	echo ' : <select name="exerciseLevel" onchange="javascript:document.qnpool.submit();">';
		if (!isset($exerciseLevel)) $exerciseLevel = -1;
		?>
		<option value="-1">-- <?php echo get_lang('All') ?> --</option>';
		<option value="1" <?php if($exerciseLevel == '1'){echo 'selected';}?>><?php echo get_lang('Prerequisite') ?></option>';
		<option value="2" <?php if($exerciseLevel == '2'){echo 'selected';}?>><?php echo get_lang('Beginner') ?></option>';
		<option value="3" <?php if($exerciseLevel == '3'){echo 'selected';}?>><?php echo get_lang('Intermediate') ?></option>';
		<option value="4" <?php if($exerciseLevel == '4'){echo 'selected';}?>><?php echo get_lang('Advanced') ?></option>';
		<?php
		echo '</select> ';
	?>
    </form></div>
<?php

	echo '</div>';
	?>

<div id="content">
    <div><table width="100%"><tr><td style="padding: 0px 20px 10px 727px">
	<?php
	$from=$page*$limitQuestPage;
	if($exerciseId == 0)
	{
		if (isset($exerciseLevel) && $exerciseLevel != -1) {
			$where .= ' level='.$exerciseLevel.' AND ';
		}
		$sql="SELECT question.id,question.question,question.type,question.level
				FROM $TBL_EXERCICE_QUESTION rel_question,$TBL_QUESTIONS question,$TBL_EXERCICES quiz
			  	WHERE $where rel_question.question_id=question.id AND rel_question.exercice_id=quiz.id  AND quiz.active <> -1 GROUP BY question.question,question.type,question.level ORDER BY question_order LIMIT ".$from.", ".($limitQuestPage + 1);
	}
	else
	{
		if (isset($exerciseLevel) && $exerciseLevel != -1) {
			$where .= ' level='.$exerciseLevel.' AND ';
		}
		$sql="SELECT question.id,question.question,question.type,question.level
				FROM $TBL_EXERCICE_QUESTION rel_question,$TBL_QUESTIONS question,$TBL_EXERCICES quiz
			  	WHERE $where rel_question.question_id=question.id AND  rel_question.exercice_id=quiz.id AND rel_question.exercice_id='".Database::escape_string($exerciseId)."'
				 AND quiz.active <> -1 GROUP BY question.question,question.type,question.level ORDER BY question_order LIMIT ".$from.", ".($limitQuestPage + 1);
	}
//	$exerciseId=0;

	$i = 1;
	$result=api_sql_query($sql,__FILE__,__LINE__);
	$nbrQuestions=Database::num_rows($result);

	if(!empty($page)) {

	   echo '<a href="'.api_get_self().'?'.api_get_cidreq().'&exerciseId='.$exerciseId.'&exerciseLevel='.intval($_GET['exerciseLevel']).'&fromExercise='.$fromExercise.'&page='.($page-1).'">'.Display::return_icon('pixel.gif', '', array('class' => 'actionplaceholdericon actionprev')).get_lang('PreviousPage').'</a> | &nbsp;';

	} elseif($nbrQuestions > $limitQuestPage) {
	   echo Display::return_icon('pixel.gif', '', array('class' => 'actionplaceholdericon actionprev')).get_lang('PreviousPage').' | &nbsp;';
	}

	if($nbrQuestions > $limitQuestPage) {
    	echo '<a href="'.api_get_self().'?'.api_get_cidreq().'&exerciseId='.intval($_GET['exerciseId']).'&exerciseLevel='.intval($_GET['exerciseLevel']).'&fromExercise='.$fromExercise.'&page='.($page+1).'">'.get_lang('NextPage').Display::return_icon('pixel.gif', '', array('class' => 'actionplaceholdericon actionnext')).'</a>';
	} elseif($page) {
	   echo ' '.get_lang('NextPage').Display::return_icon('pixel.gif', '', array('class' => 'actionplaceholdericon actionnext'));
	}
    echo '</td>
	</tr>
	</table></div>';
	?>
	<table class="data_table">
	<tr><th width="5%">&nbsp;</th><th width="65%" align="left"><?php echo get_lang('Question'); ?></th><th width="10%"><?php echo get_lang('Type'); ?></th><th width="10%"><?php echo get_lang('Level'); ?></th><th width="10%"><?php echo get_lang('ReuseQuestion'); ?></th></tr>
	<?php

	while ($row = Database::fetch_array($result)) {

		if($i%2 == 0)
		{
			$class = "row_odd";
		}
		else
		{
			$class = "row_even";
		}

		$type = $row['type'];
		$level = $row['level'];

		if($type == '1')
		{
			$img_type = "multiple_choice_medium.png";
		}
		elseif($type == '2')
		{
			$img_type = "multiple_answer_medium.png";
		}
		elseif($type == '8')
		{
			$img_type = "reasoning_medium.png";
		}
		elseif($type == '3')
		{
			$img_type = "fill_in_the_blank_medium.png";
		}
		elseif($type == '5')
		{
			$img_type = "open_question_medium.png";
		}
		elseif($type == '4')
		{
			$img_type = "drag_drop_medium.png";
		}
		elseif($type == '6')
		{
			$img_type = "hotspots_medium.png";
		}

		if($level == '1')
		{
			$img_level = "level1.png";
			$class_level = "toolactionplaceholdericon toolactionlevel1";
		}
		elseif($level == '2')
		{
			$img_level = "level2.png";
			$class_level = "toolactionplaceholdericon toolactionlevel2";
		}
		elseif($level == '3')
		{
			$img_level = "level3.png";
			$class_level = "toolactionplaceholdericon toolactionlevel3";
		}
		elseif($level == '4')
		{
			$img_level = "level4.png";
			$class_level = "toolactionplaceholdericon toolactionlevel4";
		}


	echo '<tr class="'.$class.'" ><td width="5%" align="center" valign="top" style="padding-top:12px"><input type="checkbox" name="notify_checkbox" id="notify_checkbox_'.$i.'" value="'.$row['id'].'" /></td><td width="65%">'.$row['question'].'</td><td align="center" width="10%" valign="top" style="padding-top:4px"><img src="../img/'.$img_type.'"></td><td align="center" width="10%" valign="top" style="padding-top:6px">'.Display::return_icon('pixel.gif', '', array('class' => $class_level)).'</td><td align="center" width="10%" valign="top" style="padding-top:6px"><a href="'.api_get_self().'?'.api_get_cidreq().'&recup='.$row['id'].'&fromExercise='.$fromExercise.'">'.Display::return_icon('pixel.gif', '', array('class' => 'toolactionplaceholdericon tooladdquestion')).'</td></tr>';
	$i++;
	}
	?>
	</table>
	<?php
         } else {
          // if not admin of course
          api_not_allowed(true);
         }
 if (api_is_allowed_to_edit ()) {
	 echo '<br/><button class="cancel" type="button" name="submit_add" id="submit_add"  style="float:left;">'.get_lang('Reuse').'</button>';
 }
 ?>
 </div>
 <?php
  if (isset($_GET['exerciseId']) && $_GET['exerciseId'] > 0) {
      $quiz_id = Security::remove_XSS($_GET['exerciseId']);
  } elseif (isset($_GET['fromExercise']) && $_GET['fromExercise'] > 0) {
      $quiz_id = Security::remove_XSS($_GET['fromExercise']);
  }
  if (api_is_allowed_to_edit ()) {
 ?>
          <div class="actions">
           <a href="<?php echo 'exercice.php?show=result&' . api_get_cidreq(); ?>"><?php echo Display::return_icon('pixel.gif', get_lang('Tracking'), array('class' => 'actionplaceholdericon actiontracking')) . get_lang('Tracking') ?></a>
           <?php if (api_get_setting('enable_pro_settings') === 'true'): ?> 
                <a href="<?php echo 'question_pool.php?fromExercise=' . $quiz_id . '&' . api_get_cidreq(); ?>"><?php echo Display::return_icon('pixel.gif', get_lang('QuizQuestionsPool'), array('class' => 'actionplaceholdericon actionquestionpool')) . get_lang('QuizQuestionsPool') ?></a>
           <?php endif; ?>
          </div>
<?php
  }
  Display::display_footer();
?>
