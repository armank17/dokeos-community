<?php

/*
  DOKEOS - elearning and course management software

  For a full list of contributors, see documentation/credits.html

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; either version 2
  of the License, or (at your option) any later version.
  See "documentation/licence.html" more details.

  Contact:
  Dokeos
  Rue des Palais 44 Paleizenstraat
  B-1030 Brussels - Belgium
  Tel. +32 (2) 211 34 56
 */


/**
 * 	File containing the UNIQUE_ANSWER class.
 * 	@package dokeos.exercise
 * 	@author Eric Marguin
 * 	@version $Id: admin.php 10680 2007-01-11 21:26:23Z pcool $
 */
if (!class_exists('UniqueAnswer')):

 /**
   CLASS UNIQUE_ANSWER
  *
  * 	This class allows to instantiate an object of type UNIQUE_ANSWER (MULTIPLE CHOICE, UNIQUE ANSWER),
  * 	extending the class question
  *
  * 	@author Eric Marguin
  *  @author Julio Montoya
  * 	@package dokeos.exercise
  * */
 class UniqueAnswer extends Question {

  static $typePicture = 'mcua.gif';
  static $explanationLangVar = 'UniqueSelect';

  /**
   * Constructor
   */
  function UniqueAnswer() {
   //this is highly important
   parent::question();
   $this->type = UNIQUE_ANSWER;
  }

  /**
   * function which redifines Question::createAnswersForm
   * @param the formvalidator instance
   * @param the answers number to display
   */
  function createAnswersForm($form, $my_quiz = null) {
   // getting the exercise list
   global $charset;
   if (isset($my_quiz)) {            
        $simplifyQuestionsAuthoring = ($my_quiz->selectSimplifymode()== 1) ? 'true' : 'false';
   }   
   $obj_ex = $_SESSION['objExercise'];
   $navigator_info = api_get_navigator(); 

   $editor_config = array('ToolbarSet' => 'TestProposedAnswer', 'Width' => '100%', 'Height' => '70');

   //this line define how many question by default appear when creating a choice question
   $nb_answers = isset($_POST['nb_answers']) ? (int) $_POST['nb_answers'] : 2;
   $nb_answers += ( isset($_POST['lessAnswers']) ? -1 : (isset($_POST['moreAnswers']) ? 1 : 0));

	if(isset($_POST['formsize']))
	{
		$formsize = $_POST['formsize'];
	}
	else
	{
	  $formsize = '';
	}

	if(empty($formsize) || $formsize == 'Low')
	{
	  $formsize_px = "70px";
	}
	else
	{
	   $formsize_px = "150px";
	}

   /*
     Types of Feedback
     $feedback_option[0]=get_lang('Feedback');
     $feedback_option[1]=get_lang('DirectFeedback');
     $feedback_option[2]=get_lang('NoFeedback');
    */

   $feedback_title = '';
   $comment_title = '';

   if ($obj_ex->selectFeedbackType() == 0) {
      $comment_title = '<th>' . get_lang('Comment') . '</th>';
   } elseif ($obj_ex->selectFeedbackType() == 1) {
      $editor_config['Width'] = '250';
      $editor_config['Height'] = '70';
      $comment_title = '<th width="500px" >' . get_lang('Comment') . '</th>';
      $feedback_title = '<th width="350px" >' . get_lang('Scenario') . '</th>';
   }
   $answer_lang_var = api_convert_encoding(get_lang('Answer'), $charset, api_get_system_encoding());
//   $form->addElement('html', '<div style="float:right;padding-right:20px;"><img style="cursor: pointer;" src="../img/SmallFormFilled.png" alt="" onclick="lowlineform()" />&nbsp;<img style="cursor: pointer;" src="../img/BigFormClosed.png" alt="" onclick="highlineform()" /></div>');

   $form->addElement('html', '<div id="leftcontainer" class="quiz_answer_small_squarebox">');
   $html = '
		<div class="row">			
			<div>
				<table class="data_table" style="width:100%;">
					<tr style="text-align: center;">
						<th>'.get_lang('True').'</th>
						<th>
							' . $answer_lang_var . '
						</th>
					</tr>';

   $form->addElement('html', $html);

   $defaults = array();
   $correct = 0;
   if (!empty($this->id)) {
    $answer = new Answer($this->id);
    $answer->read();
    if (count($answer->nbrAnswers) > 0 && !$form->isSubmitted()) {
     $nb_answers = $answer->nbrAnswers;
    }
   }
   $form->addElement('hidden', 'nb_answers');
   $form->addElement('hidden', 'submitform');
   $form->addElement('hidden', 'questiontype','1');
   $form->addElement('hidden', 'formsize');

   //Feedback SELECT
   //$question_list = $obj_ex->selectQuestionList();
   $select_question = array();
   $select_question[0] = get_lang('SelectTargetQuestion');
   require_once '../newscorm/learnpathList.class.php';
   require_once api_get_path(LIBRARY_PATH) . 'text.lib.php';

   if (is_array($question_list)) {
    foreach ($question_list as $key => $questionid) {
     $question = Question::read($questionid);
     $select_question[$questionid] = 'Q' . $key . ' :' . cut($question->selectTitle(), 20);
    }
   }
   $select_question[-1] = get_lang('ExitTest');
//
//   //LP SELECT
   require_once('../newscorm/learnpathList.class.php');

   $list = new LearnpathList(api_get_user_id());
   $flat_list = $list->get_flat_list();
   $select_lp_id = array();
   $select_lp_id[0] = get_lang('SelectTargetLP');

   foreach ($flat_list as $id => $details) {
    $select_lp_id[$id] = cut($details['lp_name'], 20);
   }

   $temp_scenario = array();
   $false_feedback = false;
   for ($i = 1; $i <= $nb_answers; ++$i) {
    $class = ($i%2 == 0) ? 'row_odd' : 'row_even';
    $form->addElement('html', '<tr class="'.$class.'">');
    if (is_object($answer)) {
     if ($answer->correct[$i]) {
      $correct = $i;
	  $defaults['comment[1]'] = $answer->comment[$i];
     }
	 elseif(!$false_feedback)
	 {		 
		 $defaults['comment[2]'] = $answer->comment[$i];
		 $false_feedback = true;
	 }
//
     $defaults['answer[' . $i . ']'] = $answer->answer[$i];
     $defaults['weighting[' . $i . ']'] = float_format($answer->weighting[$i], 1);
     $item_list = explode('@@', $answer->destination[$i]);
//
     $try = $item_list[0];
     $lp = $item_list[1];
     $list_dest = $item_list[2];
     $url = $item_list[3];

     if ($try == 0)
      $try_result = 0;
     else
      $try_result=1;

     if ($url == 0)
      $url_result = '';
     else
      $url_result=$url;
//
     $temp_scenario['url' . $i] = $url_result;
     $temp_scenario['try' . $i] = $try_result;
     $temp_scenario['lp' . $i] = $lp;
     $temp_scenario['destination' . $i] = $list_dest;
    }else {
     $temp_scenario['destination' . $i] = array('0');
     $temp_scenario['lp' . $i] = array('0');
    }
    $defaults['scenario'] = $temp_scenario;

    $renderer = & $form->defaultRenderer();
      $renderer->setElementTemplate('<td align="center"><br/>{element}</td>');
//
    $form->addElement('radio', 'correct', null, null, $i, 'class="checkbox" style="margin-left: 0em;"');
    if($simplifyQuestionsAuthoring=='true'){
        $form->addElement('textarea', 'answer[' . $i . ']', false,array('rows' => 3, 'cols' => 50,'placeholder'=> get_lang("TypeHere"),'style'=>'width:90%;'));
    }else{
    $form->add_html_editor('answer[' . $i . ']','', false, false, array('ToolbarSet' => 'TestProposedAnswer', 'Width' => '400px', 'Height' => ''.$formsize_px.''));
    }
    
    $form->addRule('answer[' . $i . ']', get_lang('ThisFieldIsRequired'), 'required');
    $form->addElement('html', '</tr>');
     }
   $form->addElement('html', '</table>');
   // Add the buttons for add/remove answers
   $form->addElement('html', '<table width="100%"><tr><td width="100%"><div align="right">');
   global $text, $class;
   //ie6 fix
   if ($navigator_info['name'] == 'Internet Explorer' && ($navigator_info['version'] >= '6')) {	
		if($navigator_info['version'] == '6' || $navigator_info['version'] == '7'){	
		$form->addElement('html','<div style="padding-right:30px;float:right;">');
		}
		else {
		$form->addElement('html','<div style="float:right;">');
		}	
	$form->addElement('html', '<div name="moreAnswers" class="button_more" onclick="addNewAnswer('.UNIQUE_ANSWER.','.$simplifyQuestionsAuthoring.')" style="float:right;"></div>');
        $form->addElement('html', '<div name="lessAnswers" class="button_less" onclick="removeAnswer()" style="float:right;"></div>');
	//$form->addElement('submit', 'lessAnswers', '', 'class="button_less"');
	//$form->addElement('submit', 'moreAnswers', '', 'class="button_more"');	
   } else {
	  $form->addElement('html','<div align="right">');
	  //$form->addElement('submit', 'lessAnswers', '', 'class="button_less"');
	  //$form->addElement('submit', 'moreAnswers', '', 'class="button_more"');
            $form->addElement('html', '<div name="moreAnswers" class="button_more" onclick="addNewAnswer('.UNIQUE_ANSWER.','.$simplifyQuestionsAuthoring.')" style="float:right;"></div>');
            $form->addElement('html', '<div name="lessAnswers" class="button_less" onclick="removeAnswer()" style="float:right;"></div>');
   }
   $form->addElement('html', '</div></td></tr></table>');   
   $form->addElement('html', '</div></div>');
   $form->addElement('html', '</div>');

   // Feedback container
   $form->addElement('html', '<div id="feedback_container" style="float:left;width:100%;margin-top:25px;">');
   $form->addElement('html', '<div style="float:left;width:50%;text-align:left;">' . get_lang('FeedbackIfTrue'));
   if($simplifyQuestionsAuthoring=='true'){
       $form->addElement('textarea', 'comment[1]', false,array('rows' => 3, 'cols' => 50, 'placeholder'=> get_lang("TypeHere")));
   }else{
   $form->add_html_editor('comment[1]','', false, false, array('ToolbarSet' => 'TestProposedFeedback', 'Width' => '400px', 'Height' => ''.$formsize_px.''));
   }
   
   $form->addElement('html', '</div>');
   $form->addElement('html', '<div style="float:right;width:50%;text-align:right">');
   $form->addElement('html', '<div style="float:right;text-align:left">' . get_lang('FeedbackIfFalse'));
   if($simplifyQuestionsAuthoring=='true'){
       $form->addElement('textarea', 'comment[2]', false,array('rows' => 3, 'cols' => 50, 'placeholder'=> get_lang("TypeHere")));
   }else{
   $form->add_html_editor('comment[2]','', false, false, array('ToolbarSet' => 'TestProposedFeedback', 'Width' => '400px', 'Height' => ''.$formsize_px.''));
   }   
   $form->addElement('html', '</div></div>');
   $form->addElement('html', '<div style="float:right;text-align:left">'); 
   //ie6 fix
   if ($navigator_info['name'] == 'Internet Explorer' && $navigator_info['version'] == '6') {
      $form->addElement('style_submit_button', 'submitQuestion', get_lang('Validate'), 'class="save"  style="float:right;"');
   } else {
    //setting the save button here and not in the question class.php
      $form->addElement('style_submit_button', 'submitQuestion', get_lang('Validate'), 'class="save" style="float:right;margin-top:20px"');
   }

   $renderer->setElementTemplate('{element}', 'submitQuestion');
   $renderer->setElementTemplate('{element}&nbsp;', 'lessAnswers');
   $renderer->setElementTemplate('{element}', 'moreAnswers');
   $form->addElement('html', '</div>');

   // End feedback container
   $form->addElement('html', '</div>');
   //We check the first radio button to be sure a radio button will be check
   if ($correct == 0) {
    $correct = 1;
   }
   $defaults['correct'] = $correct;
   $form->setDefaults($defaults);

   $form->setConstants(array('nb_answers' => $nb_answers));
  }
  
  

  /**
   * abstract function which creates the form to create / edit the answers of the question
   * @param the formvalidator instance
   * @param the answers number to display
   */
  function processAnswersCreation($form) {
   
   $_SESSION['editQn'] = '1';
   $questionWeighting = $nbrGoodAnswers = 0;
   $correct = $form->getSubmitValue('correct');
   $objAnswer = new Answer($this->id);
   $nb_answers = $form->getSubmitValue('nb_answers');

   // Currently all questions has 2 feedback fields
   $feedback_if_true = $form->getSubmitValue('comment[1]');
   $feedback_if_false = $form->getSubmitValue('comment[2]');
 
   // Score for the correct answer
   $answer_score = $form->getSubmitValue('scoreQuestions');
   
   $feedback_if_false = $form->getSubmitValue('comment[2]');

   for ($i = 1; $i <= $nb_answers; $i++) {
    $answer = trim($form->getSubmitValue('answer[' . $i . ']'));

    //$comment = trim($form->getSubmitValue('comment[' . $i . ']'));

    //$weighting = trim($form->getSubmitValue('weighting[' . $i . ']'));

    $scenario = $form->getSubmitValue('scenario');

    echo '<pre>';
    //$list_destination = $form -> getSubmitValue('destination'.$i);
    //$destination_str = $form -> getSubmitValue('destination'.$i);

    $try = $scenario['try' . $i];
    $lp = $scenario['lp' . $i];
    $destination = $scenario['destination' . $i];
    $url = trim($scenario['url' . $i]);

    /*
      How we are going to parse the destination value

      here we parse the destination value which is a string
      1@@3@@2;4;4;@@http://www.dokeos.com

      where: try_again@@lp_id@@selected_questions@@url

      try_again = is 1 || 0
      lp_id = id of a learning path (0 if dont select)
      selected_questions= ids of questions
      url= an url
     */
    /*
      $destination_str='';
      foreach ($list_destination as $destination_id)
      {
      $destination_str.=$destination_id.';';
      } */

    $goodAnswer = ($correct == $i) ? true : false;

    if ($goodAnswer) {
     $nbrGoodAnswers++;
     $weighting = abs($answer_score);
     if ($weighting > 0) {
      $questionWeighting += $weighting;
     }
     $comment = $feedback_if_true;
    } else {
      $comment = $feedback_if_false;
      $weighting = 0;
      //$questionWeighting = 0;
    }

    if (empty($try))
     $try = 0;

    if (empty($lp)) {
     $lp = 0;
    }

    if (empty($destination)) {
     $destination = 0;
    }

    if ($url == '') {
     $url = 0;
    }

    //1@@1;2;@@2;4;4;@@http://www.dokeos.com
    $dest = $try . '@@' . $lp . '@@' . $destination . '@@' . $url;
    $objAnswer->createAnswer($answer, $goodAnswer, $comment, $weighting, $i, NULL, NULL, $dest);
   }


   // saves the answers into the data base
   $objAnswer->save();

   // sets the total weighting of the question
   $this->updateWeighting($questionWeighting);
   $this->save();
  }

  function create_answers_from_an_attached_file ($id, $question_id, $answer_title, $comment, $ponderation = 0, $correct = 0) {
    $tbl_quiz_answer = Database::get_course_table(TABLE_QUIZ_ANSWER);
	$tbl_quiz_question = Database::get_course_table(TABLE_QUIZ_QUESTION);

    $position = 1;
    // Get the max position
    $sql = "SELECT max(position) as max_position FROM $tbl_quiz_answer WHERE question_id = '".$question_id."'";
    $rs_max  = Database::query($sql, __FILE__, __LINE__);
    $row_max = Database::fetch_object($rs_max);
    $position = $row_max->max_position + 1;
    // Insert a new answer
    $sql = "INSERT INTO $tbl_quiz_answer(id, question_id,answer,correct,comment,ponderation,position,destination)
    VALUES ('".$id."','".$question_id."','".Database::escape_string($answer_title)."','".$correct."','".Database::escape_string($comment)."','".$ponderation."','".$position."', '0@@0@@0@@0')";
    $rs = Database::query($sql, __FILE__, __LINE__);

	if($correct)
	{
		$sql = "UPDATE $tbl_quiz_question SET ponderation = ponderation + ".$ponderation." WHERE id = ".$question_id;
		$rs = Database::query($sql, __FILE__, __LINE__);
	}
  }
  /**
   * Display the question in tracking mode (use templates in tracking/questions_templates)
   * @param $nbAttemptsInExercise the number of users who answered the quiz
   */
  function displayTracking($exerciseId, $nbAttemptsInExercise) {

   if (!class_exists('Answer'))
    require_once(api_get_path(SYS_CODE_PATH) . 'exercice/answer.class.php');

   $o_answer = new Answer($this->id);
   $o_answer->stats = $this->getAverageStats($exerciseId, $nbAttemptsInExercise);
   include(api_get_path(SYS_CODE_PATH) . 'exercice/tracking/questions_templates/unique_answer.page');
  }

  /**
   * Returns learners choices for each question in percents
   * @param $nbAttemptsInExercise the number of users who answered the quiz
   * @return array the percents
   */
  function getAverageStats($exerciseId, $nbAttemptsInExercise) {

   $preparedSql = 'SELECT attempts.answer, COUNT(1) as nbAttempts
						FROM ' . Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT) . ' as attempts
						INNER JOIN ' . Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES) . ' as exercises
							ON exercises.exe_id = attempts.exe_id
						WHERE attempts.course_code LIKE "%s"
						AND attempts.question_id = %d
						
						GROUP BY answer';
   $sql = sprintf($preparedSql, api_get_course_id(), $this->id, $exerciseId);
   $rs = Database::query($sql, __FILE__, __LINE__);

   $totalAttempts = 0;
   $stats = array();
   while ($answer = Database::fetch_object($rs)) {
    $stats[$answer->answer] = array();
    $stats[$answer->answer]['total'] = $answer->nbAttempts;
   }

   foreach ($stats as $answerId => &$stat) {
    $stat['average'] = $stat['total'] / $nbAttemptsInExercise * 100;
   }


   return $stats;
  }
  
  public function getHtmlQuestionResult($objQuestion, $attemptId, &$totalScore, &$totalWeighting, $dbName = '') {
      
        $feedbackIfTrue = $feedbackIfFalse = $s = '';
        $attemptId = intval($attemptId);
        $questionScore = 0;   
        $correctChoice = 'N';
        $correctComment = array();
        $questionId = $objQuestion->selectId();
        $objAnswerTmp = new Answer($questionId, $dbName);
        $nbrAnswers = $objAnswerTmp->selectNbrAnswers();        
    
        $mediaPosition = $objQuestion->selectMediaPosition();
        $questionDescription = api_parse_tex($objQuestion->selectDescription());
        $questionDescription = str_replace('../default_course_document/', api_get_path(WEB_CODE_PATH).'default_course_document/', $questionDescription); 
        
        $questionWeighting = $objQuestion->selectWeighting();   
        $totalWeighting += $questionWeighting;
        $tbl_track_attempt = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);        
        $media_w = in_array($mediaPosition, array('top', 'nomedia'))?'width:95%':'width:44%;float:left';
       
        if ($mediaPosition == 'top') {
            $s .= '<div class="span5 quizPart media-top">'.$questionDescription.'</div>';
        } 
        $s  .= '<div class="span7 quizPart question-answers" style="'.$media_w.'">';
        $s .= '<div class="quesion-answers-content">';
        $s .= '<table class="responsive large-only table-striped">';
        $s .= '<thead>'; 
        $s .= '<tr>
                    <th>'.get_lang("Choice").'</th>
                    <th>'.get_lang("ExpectedChoice").'</th>
                    <th>'.get_lang("Answer").'</th>';
        $s .= '</thead>';
        $s .= '<tbody>';
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
            $resultans = Database::query("SELECT answer FROM $tbl_track_attempt WHERE exe_id = $attemptId AND question_id= $questionId");
            $choice = Database::result($resultans, 0, "answer");
            $studentChoice = ($choice == $answerId) ? 1 : 0;
            $your_choice = 'radio_off' . '.gif';
            if ($studentChoice) {
                $questionScore+=$answerWeighting;
                $totalScore += $answerWeighting;
                if ($studentChoice == $answerCorrect) {
                    $correctChoice = 'Y';
                    $feedbackIfTrue = $objAnswerTmp->selectComment($answerId);
                } else {
                    $feedbackIfFalse = $objAnswerTmp->selectComment($answerId);
                }
                $your_choice = 'radio_on' . '.gif';
            }

            if ($answerCorrect) {
                $expected_choice = 'radio_on' . '.gif';
            } else {
                $expected_choice = 'radio_off' . '.gif';
            }

            $s .= '
                <tr>
                    <td align="center">
                        <img src="'.api_get_path(WEB_IMG_PATH).$your_choice.'" border="0" alt="" />
                    </td>
                    <td align="center">
                        <img src="'.api_get_path(WEB_IMG_PATH).$expected_choice.'" border="0" alt=" " />
                    </td>
                    <td style="border-bottom: 1px solid #4171B5;">'.api_parse_tex($answer).'</td>
                </tr>';
        }
        $s .= '</tbody>';
        $s .= '</table>';
        $s .= '</div>';
        
        
        /*if ($correctChoice == 'Y') {
            $feedbackIfTrue = $objAnswerTmp->selectComment($correct);
            if (empty($feedbackIfTrue)) {
                $feedbackIfTrue = get_lang('NoTrainerComment');
            }
            $feedbackDisplay = '<b>' . get_lang('Feedback') . ' : </b><span style="font-weight: bold; color: #008000;">' . $feedbackIfTrue . '</span>';
        } else {
            $feedbackIfFalse = $objAnswerTmp->selectComment($not_correct);
            if (empty($feedbackIfFalse)) {
                $feedbackIfFalse = get_lang('NoTrainerComment');
            }
            $feedbackDisplay = '<b>' . get_lang('Feedback') . ' : </b><span style="font-weight: bold; color: #FF0000;">' . $feedbackIfFalse . '</span>';
        }        
        $s .= '<div class="span11">'.$feedbackDisplay.'</div>';*/
        
        $s .= '</div>';     
        if ($mediaPosition == 'right') {
            $s .= '<div class="span5 quizPart" style="width:500px;float:right;overflow:auto !important;text-align:center;">'.$questionDescription.'</div>';
        } 
        $s .= '<div class="clear"></div>';
        
        
        $s .= '<table style="clear:both;width:100% margin:5px; margin:15px 0; border:none;">';
        if ($correctChoice == 'Y') {
            $feedbackIfTrue = $objAnswerTmp->selectComment($correct);
            if (empty($feedbackIfTrue)) {
                $feedbackIfTrue = get_lang('NoTrainerComment');
            }
            $s .= '<tr><td colspan="3" style="padding:2px; border:none;"><div class="feedback-right feed-custom-right">' . get_lang('Feedback') . '</div></td></tr><tr><td colspan="3" style="padding:2px; border:none;"><div class="feedback-right"><span>' . $feedbackIfTrue . '</span></div></td></tr>';
        } else {
            $feedbackIfFalse = $objAnswerTmp->selectComment($not_correct);
            if (empty($feedbackIfFalse)) {
                $feedbackIfFalse = get_lang('NoTrainerComment');
            }
            $s .= '<tr><td colspan="3" style="padding:2px; border:none;"><div class="feedback-wrong feed-custom-wrong">' . get_lang('Feedback') . '</div></td></tr><tr><td colspan="3" style="padding:2px; border:none;"><div class="feedback-wrong"><span>' . $feedbackIfFalse . '</span></div></td></tr>';
        }
        $s .= '</table>';
        
        
        // score
        if ($questionWeighting - $questionScore < 0.50) {
            $myTotalScore = round(float_format($questionScore, 1));
        } else {
            $myTotalScore = float_format($questionScore, 1);
        }
        $myTotalWeight = float_format($questionWeighting, 1);
        if ($myTotalScore < 0) { $myTotalScore = 0; } 
        
        $s .= '<div class="span2 quesion-answers-score"><b>'.get_lang('Score').' : </b> '.$myTotalScore. ' / '.$myTotalWeight.'</div>';
        
        return $s;
  }
  
  public function getHtmlQuestionAnswer($objQuestion, $objExercise, $readonly = false, $examId = 0) {
    $exerciseId = $objExercise->selectId();
    $questionId = $objQuestion->selectId();
    $objAnswer = new Answer($objQuestion->selectId());

    $nbrAnswers = $objAnswer->selectNbrAnswers();        
    $mediaPosition = $objQuestion->selectMediaPosition();        
    $questionDescription = api_parse_tex($objQuestion->selectDescription());
    $questionDescription = str_replace('../default_course_document/', api_get_path(WEB_CODE_PATH).'default_course_document/', $questionDescription); 
    $media_w = in_array($mediaPosition, array('top', 'nomedia'))?'width:95%':'width:50%;float:left';

    $exeId   = $objExercise->getLastUserAttemptId($exerciseId, 'incomplete', null, null, null, $examId);
    $attempt = $objExercise->getQuestionAnswersTrackAttempt($exeId, $questionId);
    $s = '';
    if ($mediaPosition == 'top') {
        $s .= '<div class="span5 quizPart media-top">'.$questionDescription.'</div>';
    } 
    $s  .= '<div class="span7 quizPart question-answers" style="'.$media_w.'">';
    for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
        $answer = api_parse_tex($objAnswer->selectAnswer($answerId));
        $answer = str_replace('../default_course_document/', api_get_path(WEB_CODE_PATH).'default_course_document/', $answer); 
        $checked = !empty($attempt[0]) && $attempt[0] == $answerId?' checked':''; 
        $s .= '<div class="span6 custom-radio">';
        if ($readonly) {
            $s .= "<table width='100%'><tr>";
            $s .= "<td width='30px'><img src='".api_get_path(WEB_IMG_PATH)."radio_off.gif' /></td>";        
            $s .= '<td>'.strip_tags($answer,'<a><span><img><sub><sup>').'</td>';
            $s .= "</tr></table>";
        }
        else {            
            $s .= "<input class='answer' id='radio-" . $questionId . "-" . $answerId . "' type='radio' name='choice[".$questionId."]' value='".$answerId."' $checked $inpReadOnly><input type='hidden' name='choice2[".$questionId."]' value='0'>";        
            $s .= '<label class="answer" for="radio-' . $questionId . '-' . $answerId . '">' . strip_tags($answer,'<a><span><img><sub><sup>') . '</label>';            
        }   
        $s .= '</div>';
    }
    $s .= '</div>';     
    if ($mediaPosition == 'right') {
        $s .= '<div class="span5 quizPart" style="width:40%;float:left;">'.$questionDescription.'</div>';
    } 
    $s .= '<div class="clear"></div>';  
    return $s;
  }
  
 }

 endif;
?>
