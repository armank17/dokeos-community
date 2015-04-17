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
 * 	File containing the FreeAnswer class.
 * 	This class allows to instantiate an object of type FREE_ANSWER,
 * 	extending the class question
 * 	@package dokeos.exercise
 * 	@author Eric Marguin
 * 	@version $Id: admin.php 10680 2007-01-11 21:26:23Z pcool $
 */
if (!class_exists('FreeAnswer')):

 class FreeAnswer extends Question {

  static $typePicture = 'open_answer.gif';
  static $explanationLangVar = 'freeAnswer';

  /**
   * Constructor
   */
  function FreeAnswer() {
   parent::question();
   $this->type = FREE_ANSWER;
  }

  /**
   * function which redifines Question::createAnswersForm
   * @param the formvalidator instance
   */
  function createAnswersForm($form, $my_quiz = null) {
	 global $charset; 
    if (isset($my_quiz)) {            
            $simplifyQuestionsAuthoring = ($my_quiz->selectSimplifymode()== 1) ? 'true' : 'false';
    }
    $openanswer_lang_var = api_convert_encoding(get_lang('OpenAnswer'), $charset, api_get_system_encoding());

//	$form->addElement('html', '<div style="float:right;padding-right:25px;"><img style="cursor: pointer;" src="../img/SmallFormFilled.png" alt="" onclick="lowlineform()" />&nbsp;<img style="cursor: pointer;" src="../img/BigFormClosed.png" alt="" onclick="highlineform()" /></div>');

   // Main container
   $form->addElement('html', '<div id="leftcontainer" class="quiz_answer_small_squarebox">');
// $form->addElement('text', 'weighting', get_lang('Weighting'), 'size="5"');
// $form->addElement('html_editor', 'open_answer', get_lang('OpenAnswer'), 'cols="55" rows="10"');
   if($simplifyQuestionsAuthoring == 'false'){
   $form->addElement('html_editor', 'open_answer',$openanswer_lang_var,'style="vertical-align:middle"',array('ToolbarSet' => 'TestProposedAnswer', 'Width' => '86%', 'Height' => '100'));
   }
   
// $form->addElement('html', '<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>');
// $form->addElement('html', '<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>');

   if (!empty($this->id)) {
    $answer = new Answer($this->id);
    $answer->read();
   }
	   /*$open_answer = '<table width="99%" height="145px" cellspacing="2" cellpadding="0" style="font-family:Comic Sans MS;font-size:16px;">
            <tbody>
                <tr valign="top" align="center">
                    <td width="90%" valign="top" style="padding-left:10px;">
                      &nbsp;
                    </td>
                    <td width="9%" valign="bottom" style="padding-left:10px;">
                      <img  src="../img/pen_holder.png"/>
                    </td>
                </tr>
            </tbody>
        </table>';*/

    if (is_object($answer)) {
     $open_answer = $answer->answer[1];
    }
   // End main container
   //$form -> addElement ('html', '</div>');
   /*
     // Feedback container
     $form -> addElement ('html', '<div style="float:left">'.get_lang('FeedbackIfTrue'));
     $form->addElement('textarea', 'comment[1]',null,'cols="60"');
     $form -> addElement ('html', '</div>');

     $form -> addElement ('html', '<div style="float:right;text-align:right">');

     $form -> addElement ('html', '<div style="float:left;text-align:left">'.get_lang('FeedbackIfFalse'));
     $form->addElement('textarea', 'comment[2]',null,'cols="60"');
     $form -> addElement ('html', '</div>');
    */
   $form->addElement('hidden', 'submitform');
   $form->addElement('hidden', 'questiontype','5');
   $form->addElement('html', '</div>');
   // Feedback container
   $mtfc = ($simplifyQuestionsAuthoring == 'true') ? '15px' : '25px';
   $form->addElement('html', '<div id="feedback_container" style="clear:both;width:100%;margin-top:'.$mtfc.';">');
      $form->addElement('style_submit_button', 'submitQuestion', get_lang('Validate'), 'class="save" style="float:right;margin-top:25px"');
      $form->addElement('html', '</div>');
   // setting the save button here and not in the question class.php



   if (!empty($this->id)) {
    $form->setDefaults(array('weighting' => float_format($this->weighting, 1)));
    $form->setDefaults(array('open_answer' => $open_answer));
   } else {
    $form->setDefaults(array('weighting' => '10'));
    //$form->setDefaults(array('open_answer' => $open_answer));
   }
  }

  /**
   * abstract function which creates the form to create / edit the answers of the question
   * @param the formvalidator instance
   */
  function processAnswersCreation($form) {
   
   $_SESSION['editQn'] = '1';
   // Add new "open answer" for the "Open question" question type
   $objAnswer = new Answer($this->id);
   // Get the open answer for the question
   $answer = $form->getSubmitValue('open_answer');
   // Score for the answer
   $this->weighting = $form->getSubmitValue('scoreQuestions');
   //$this->weighting = $form->getSubmitValue('weighting');
   $goodAnswer = true;

   $objAnswer->createAnswer($answer, $goodAnswer, '', 0, 1);
   $objAnswer->save();

   $this->save();
  }

  /**
   * Display the question in tracking mode (use templates in tracking/questions_templates)
   * @param $nbAttemptsInExercise the number of users who answered the quiz
   */
  function displayTracking($exerciseId, $nbAttemptsInExercise) {

  }
  public function getMarks($objQuestion, $objExercise, $readonly = false, $examId = null, $userid= null, $status='incomplete') {
         $exerciseId = $objExercise->selectId();
        $questionId = $objQuestion->selectId();
        $objAnswer = new Answer($objQuestion->selectId());

        $nbrAnswers = $objAnswer->selectNbrAnswers();        
        $mediaPosition = $objQuestion->selectMediaPosition();        
        $questionDescription = api_parse_tex($objQuestion->selectDescription());
        $questionDescription = str_replace('../default_course_document/', api_get_path(WEB_CODE_PATH).'default_course_document/', $questionDescription); 
        $media_w = in_array($mediaPosition, array('top', 'nomedia'))?'width:100%':'width:50%;float:left';
        
        $exeId   = $objExercise->getLastUserAttemptId($exerciseId, $status, $userid, null, null, $examId);    
        $attempt = $objExercise->getMarks($exeId, $questionId);
        $value = !empty($attempt[0])?$attempt[0]:'';
        return $value;
  }
  public function getHtmlQuestionAnswer1($objQuestion, $objExercise, $readonly = false, $examId = null, $userid= null, $status='incomplete') {
    $exerciseId = $objExercise->selectId();
    $questionId = $objQuestion->selectId();
    $objAnswer = new Answer($objQuestion->selectId());

    $nbrAnswers = $objAnswer->selectNbrAnswers();        
    $mediaPosition = $objQuestion->selectMediaPosition();        
    $questionDescription = api_parse_tex($objQuestion->selectDescription());
    $questionDescription = str_replace('../default_course_document/', api_get_path(WEB_CODE_PATH).'default_course_document/', $questionDescription); 
    $media_w = in_array($mediaPosition, array('top', 'nomedia'))?'width:95%':'width:50%;float:left';
    
    $exeId   = $objExercise->getLastUserAttemptId($exerciseId, $status, $userid, null, null, $examId);    
    $attempt = $objExercise->getQuestionAnswersTrackAttempt($exeId, $questionId);
    
    $value = !empty($attempt[0])?$attempt[0]:'';
    if ($readonly) {
        $html_editor = "<img src='".api_get_path(WEB_IMG_PATH)."whbox.gif' width='75%' />";
    }
    else {
        $html_editor = "<div class='span6' style='padding:10px;'>".$value."</div>";
    }
    $s = '';
    if ($mediaPosition == 'top') {
        $s .= '<div class="span5 quizPart media-top">'.$questionDescription.'</div>';
    } 
    $s .= '<div class="span7 quizPart question-answers" style="'.$media_w.'">';
    $s .= '<div class="span6">';
    $s .= $html_editor;
    $s .= '<div class="print_textarea"></div>';
    $s .= '</div>';
    $s .= '</div>';
    if ($mediaPosition == 'right') {
        $s .= '<div class="span5 quizPart">'.$questionDescription.'</div>';
    }
    return $s;
  }
  
  public function getHtmlQuestionResult($objQuestion, $attemptId, &$totalScore, &$totalWeighting, $dbName = '') {
        $questionId = $objQuestion->selectId();
        $objAnswerTmp = new Answer($questionId, $dbName);
        $nbrAnswers = $objAnswerTmp->selectNbrAnswers();
        $questionScore = 0;
        $tbl_track_attempt = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);   
        $query = "select answer, marks from " . $tbl_track_attempt . " where exe_id = '" . intval($attemptId) . "' and question_id= '" . intval($questionId) . "'";
        $resq = api_sql_query($query);
        $choice = Database::result($resq, 0, "answer");
        $choice = stripslashes($choice);
        $choice = str_replace('rn', '', $choice);
        $mediaPosition = $objQuestion->selectMediaPosition();
        $questionDescription = api_parse_tex($objQuestion->selectDescription());

        $questionScore = Database::result($resq, 0, "marks");
        if ($questionScore == -1) {
            $totalScore+=0;
        } else {
            $totalScore+=$questionScore;
        }
        
        $s = '';
        if ($mediaPosition == 'top' || $mediaPosition == 'nomedia' || empty($questionDescription)) {
            $s .= '<div class="quiz_content_actions quit_border" style="width:920px;overflow:auto;float:left;">';
        } elseif ($mediaPosition == 'right') {
            $s .= '<div class="quiz_content_actions quit_border" style="width:43%;clear:none;float:left;height:auto;min-height:300px;overflow:auto;">';
        }
        
        $s .= '<table>
                <tr>
                    <td valign="top">' . $choice . '</td>
                </tr>
                <tr>
                    <td valign="top">' . get_lang('notCorrectedYet') . '</td>
                </tr>
            </table>
            </div>';
        
        if (!empty($questionDescription)) {
            if ($mediaPosition == 'top') {
                $s .= '<div align="center"><div class="quiz_content_actions quit_border">' . $questionDescription . '</div></div>';
            } elseif ($mediaPosition == 'right') {
                $s .= '<div class="span5 quizPart" style="width:500px; overflow:auto !important; margin-right:2px;float:right; text-align:center;">' . $questionDescription . '</div>';
            }
        }
        
        $questionWeighting = $objQuestion->selectWeighting();
        $totalWeighting += $questionWeighting;

        if ($questionWeighting - $questionScore < 0.50) {
            $myTotalScore = round(float_format($questionScore, 1));
        } else {
            $myTotalScore = float_format($questionScore, 1);
        }
        $myTotalWeight = float_format($questionWeighting, 1);
        if ($myTotalScore < 0) { $myTotalScore = 0; } 

        $s .= '<div class="span2 quesion-answers-score" style="clear:both"><b>'.get_lang('Score').' : </b> '.$myTotalScore. ' / '.$myTotalWeight.'</div>';
        
        return $s;
  }
  
  public function getHtmlQuestionAnswer($objQuestion, $objExercise, $readonly = false, $examId = null, $userid= null, $status='incomplete') {
    $exerciseId = $objExercise->selectId();
    $questionId = $objQuestion->selectId();
    $objAnswer = new Answer($objQuestion->selectId());

    $nbrAnswers = $objAnswer->selectNbrAnswers();        
    $mediaPosition = $objQuestion->selectMediaPosition();        
    $questionDescription = api_parse_tex($objQuestion->selectDescription());
    $questionDescription = str_replace('../default_course_document/', api_get_path(WEB_CODE_PATH).'default_course_document/', $questionDescription); 
    $media_w = in_array($mediaPosition, array('top', 'nomedia'))?'width:95%':'width:50%;float:left';
    
    $exeId   = $objExercise->getLastUserAttemptId($exerciseId, $status, $userid, null, null, $examId);
    $attempt = $objExercise->getQuestionAnswersTrackAttempt($exeId, $questionId);
    $value = !empty($attempt[0])?$attempt[0]:'';
    if ($readonly) {
        $html_editor = "<img src='".api_get_path(WEB_IMG_PATH)."whbox.gif' width='75%' />";
    }
    else {
        $html_editor = api_return_html_area("newchoice", $value, '', '', null, array('ToolbarSet' => 'TestFreeAnswer', 'Width' => '100%', 'Height' => '200'));
    }
    $s = '';
    if ($mediaPosition == 'top') {
        $s .= '<div class="span5 quizPart media-top">'.$questionDescription.'</div>';
    } 
    $s .= '<div class="span7 quizPart question-answers" style="'.$media_w.'">';
    $s .= '<div class="span6">';
    $s .= $html_editor;
    $s .= '<div class="print_textarea"></div>';
    $s .= '</div>';
    $s .= '</div>';
    if ($mediaPosition == 'right') {
        $s .= '<div class="span5 quizPart">'.$questionDescription.'</div>';
    }
    return $s;
  }

 }

 endif;
?>