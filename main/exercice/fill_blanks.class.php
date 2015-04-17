<?php

/*
  ==============================================================================
  Dokeos - elearning and course management software

  Copyright (c) 2004-2008 Dokeos SPRL

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
 * 	File containing the FillBlanks class.
 * 	@package dokeos.exercise
 * 	@author Eric Marguin
 * 	@author Julio Montoya Armas switchable fill in blank option added
 * 	@version $Id: admin.php 10680 2007-01-11 21:26:23Z pcool $
 */
if (!class_exists('FillBlanks')):

 /**
   CLASS FillBlanks
  *
  * 	This class allows to instantiate an object of type MULTIPLE_ANSWER (MULTIPLE CHOICE, MULTIPLE ANSWER),
  * 	extending the class question
  *
  * 	@author Eric Marguin
  * 	@author Julio Montoya multiple fill in blank option added
  * 	@package dokeos.exercise
  * */
 class FillBlanks extends Question {

  static $typePicture = 'fill_in_blanks.gif';
  static $explanationLangVar = 'FillBlanks';

  /**
   * Constructor
   */
  function FillBlanks() {
   parent::question();
   $this->type = FILL_IN_BLANKS;
  }

  /**
   * function which redifines Question::createAnswersForm
   * @param the formvalidator instance
   */
  function createAnswersForm($form, $my_quiz = null) {
   $defaults = array();
   if (isset($my_quiz)) {            
        $simplifyQuestionsAuthoring = ($my_quiz->selectSimplifymode()== 1) ? 'true' : 'false';
   }
   if (!empty($this->id)) {
    $objAnswer = new answer($this->id);
    // Unserialize the feedback(comment) for the fill in blank question type
    $feedback_data = unserialize($objAnswer -> comment[1]);
    // Set the value for each feedback
    $defaults['comment[1]'] = $feedback_data['comment[1]'];
    $defaults['comment[2]'] = $feedback_data['comment[2]'];
  
    // the question is encoded like this
    // [A] B [C] D [E] F::10,10,10@1
    // number 1 before the "@" means that is a switchable fill in blank question
    // [A] B [C] D [E] F::10,10,10@ or  [A] B [C] D [E] F::10,10,10
    // means that is a normal fill blank question

    $pre_array = explode('::', $objAnswer->selectAnswer(1));

    //make sure we only take the last bit to find special marks
    $sz = count($pre_array);
    $is_set_switchable = explode('@', $pre_array[$sz - 1]);
    if ($is_set_switchable[1]) {
     $defaults['multiple_answer'] = 1;
    } else {
     $defaults['multiple_answer'] = 0;
    }
    
    //take the complete string except after the last '::'
//    $defaults['answer'] = '';
    $defaults['answer'] = $pre_array[0];
//    for ($i = 0; $i < ($sz - 1); $i++) {
//		$pre_array[$i] = strip_tags($pre_array[$i],"<u><br>");
//		if($_GET['fromTpl'] <> 1) {
//			$str_answer = str_replace('[','[<u><span id="block">',$pre_array[$i]);
//			$str_final = str_replace(']','</span></u>]',$str_answer);						
//		}
//		else {
//			$str_answer = str_replace('[<u>','[<u><span id="block">',$pre_array[$i]);
//			$str_final = str_replace('</u>]','</span></u>]',$str_answer);					
//		}
//		$defaults['answer'] .= $str_final;
//    }
    $a_weightings = explode(',', $is_set_switchable[0]);
   }/* else {
     //	$defaults['answer'] = get_lang('DefaultTextInBlanks');
     } */

   // javascript
   // Set the functions for each editor type
   $editor_type = api_get_setting('use_default_editor');
   
   if($editor_type=='Fckeditor'){

   echo '
        <script type="text/javascript">
        var firstTime = true;
        function Addfillup()
        {
            var oEditor = FCKeditorAPI.GetInstance(\'answer\');	
            var selection = "";
            var selected = (oEditor.EditorWindow.getSelection ? String(oEditor.EditorWindow.getSelection()) : oEditor.EditorDocument.selection.createRange().text); 
            if((selected == "") || (selected.indexOf("[")!= -1) || (selected.indexOf("]")!= -1) )
            {                    
                return;
            }else
            {
                var new_selection = "[<u><span id=\"block\">"+selected+"</span></u>]";
                var final_text = new_selection.replace(" </u>","</u>");		
                oEditor.InsertHtml(final_text);
                updateBlanks();
            }          
        }

        function Removefillup()
	    {
			var oEditor = FCKeditorAPI.GetInstance(\'answer\');		  
            var selection = (oEditor.EditorWindow.getSelection ? oEditor.EditorWindow.getSelection() : oEditor.EditorDocument.selection);	
			var rm_str = ""+selection;
			var rm_str1 = rm_str.replace("[","");
			var rm_str2 = rm_str1.replace("]","");	
			rm_str = "&nbsp;"+rm_str2;
			oEditor.InsertHtml(rm_str); 
			updateBlanks();
        }
            
        function updateBlanks()
        {	
            if (firstTime) {
                var field = document.getElementById("answer");
                var answer = field.value; 
            } else {
                var oEditor = FCKeditorAPI.GetInstance(\'answer\');
                var answer =  oEditor.GetXHTML( true ) ;
            }

			var blanks = answer.match(/\[[^\]]*\]/g);
			var fields = "<div class=\"row\"><div class=\"label\">' . get_lang('Weighting') . '</div><div class=\"formw\"><table>";

			if(blanks!=null){
				for(i=0 ; i<blanks.length ; i++){
					
					var str = blanks[i].replace("<u><span id=\"block\">","");
                    var str = str.replace("</span></u>","");
                    var str = str.replace("<u><u>","");
                    var blank_str = str.replace("</u></u>","");
					if(blank_str == "[]")
					{
						return;				
					}
					
					if(document.getElementById("weighting["+i+"]"))
						value = document.getElementById("weighting["+i+"]").value;
					else
                        value = "10";
					fields += "<tr><td>"+blank_str+"</td><td><input style=\"margin-left: 0em;\" size=\"5\" value=\""+value+"\" type=\"text\" id=\"weighting["+i+"]\" name=\"weighting["+i+"]\" /></td></tr>";

				}
			}
			document.getElementById("blanks_weighting").innerHTML = fields + "</table></div></div>";
                                                  
			if(firstTime) {
				firstTime = false;';
                if (count($a_weightings) > 0) {
                    foreach ($a_weightings as $i => $weighting) {
                    echo 'document.getElementById("weighting[' . $i . ']").value = "' . $weighting . '";';
                    }
                }
                echo '
            }
        }
  
		window.onload = updateBlanks;
		</script>
		';

        echo "<script type='text/javascript'>
        $(document).ready(function() {
            var input = $('#fileToUpload');

            if ($.browser.msie)
            {
                // IE suspends timeouts until after the file dialog closes
                input.live('click', function(event)
                {
                    setTimeout(function()
                    {
                        ajaxFileUpload();
                    }, 0);
                });
            }
            else
            {
                // All other browsers behave
                input.live('change', ajaxFileUpload);
            }
            
            function ajaxFileUpload()
            {
                $.ajaxFileUpload
                (
                    {
                        url:'ajaxreadfile.php',
                        secureuri:false,
                        fileElementId:'fileToUpload',
                        dataType: 'json',
                        data:{name:'logan', id:'id'},
                        success: function (data, status)
                        {
                            if(typeof(data.error) != 'undefined')
                            {
                                if(data.error != '')
                                {
                                    // alert(data.error);
                                    $('#error_upl').attr('title',data.error).show();
                                }else
                                {
                                    var oEditor = FCKeditorAPI.GetInstance('answer');
                                    oEditor.SetData(html_entity_decode(data.html));
                                    oEditor.Events.AttachEvent('OnAfterSetHTML', updateBlanks) ;
                                }
                            }
                        },
                        error: function (data, status, e)
                        {
                            alert(e);
                        }
                    }
                );
                return false;
            }
        });
        </script>";
    }
    else
    {
        global  $_course;
     ?>
<script src="<?php echo api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.form.js'; ?>" language="javascript"></script>
<style>
#main{
 position: relative!important;    
}
</style>
<?php
echo '<script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery.upload.js" ></script>';
        
        echo '<script type="text/javascript">
        //add check "isIE" for IE 9 and 10 to select text, fixed    
        function isIE () {
            var myNav = navigator.userAgent.toLowerCase();
            return (myNav.indexOf("msie") != -1) ? parseInt(myNav.split("msie")[1]) : false;
        }        
        // Set the value true when is the first time
        var firstTime = true;
        // Function used for add the [ ] to the possible answers
        function Addfillup()
        {
            // Get the editor instance current
            var oEditor = CKEDITOR.instances["answer"];
            
            // Get the selected inside of the editor
            var mySelection = oEditor.getSelection();
  
            // If the browser used is internet explorer
            if (CKEDITOR.env.ie) {
                mySelection.unlock(true);
                if (isIE () == 10 || isIE () == 9 ) {
                    selection = mySelection.getNative();
                } else {
                    selection = mySelection.getNative().createRange().text;
                }                
            } else {
                selection = mySelection.getNative();
            }
            
            // Set to string the selected for check if is changed already
            var selected = selection.toString(); 
            
            if((selection == "") || (selected.indexOf("[")!= -1) || (selected.indexOf("]")!= -1) ){                    
                return;
            }else{
            
                // Set the new selection 
                var new_selection = "[<u><span id=\"block\">"+selection+"</span></u>]";
                var final_text = new_selection.replace(" </u>","</u>");
            
                // Set the new content inside of the editor
                oEditor.insertHtml(final_text); 

                // Update the blanks in the bottom side
                updateBlanks();
            }
        }
        
        // Function used for update the blanks 
        function updateBlanks()
        {
            // If is the first time then
            if (firstTime) {
                // Get the value of the answer editor 
                var field = document.getElementById("answer");
                var answer = field.value;
            }
            else 
            {
                // Get the editor instance current
                var oEditor = CKEDITOR.instances["answer"]; 
              
                var answer =  oEditor.getData();
            }		
            var blanks = answer.match(/\[[^\]]*\]/g);
            var fields = "<div class=\"row\"><div class=\"label\">' . get_lang('Weighting') . '</div><div class=\"formw\"><table>";                  
            if(blanks!=null){
                
                for(i=0 ; i<blanks.length ; i++){
                    var str = blanks[i].replace("<u><span id=\"block\">","");
                    var str = str.replace("</span></u>","");
                    var str = str.replace("<u><u>","");
                    var blank_str = str.replace("</u></u>","");

                    if(blank_str == "[]")
                    {
                        return;				
                    }

                    if (document.getElementById("weighting["+i+"]")) {            
                        value = document.getElementById("weighting["+i+"]").value;                                    
                    } else {
//                        value = parseInt(blank_str.length)-2;
                        value = "10";
                    }

                    fields += "<tr><td>"+blank_str+"</td><td><input style=\"margin-left: 0em;\" size=\"5\" value=\""+value+"\" type=\"text\" id=\"weighting["+i+"]\" name=\"weighting["+i+"]\" /></td></tr>";
                }
                    
            }
            document.getElementById("blanks_weighting").innerHTML = fields + "</table></div></div>";

            if(firstTime){
                firstTime = false;';
                if (count($a_weightings) > 0) {
                    foreach ($a_weightings as $i => $weighting) {
                        echo 'document.getElementById("weighting[' . $i . ']").value = "' . $weighting . '";';
                    }
                }
                echo '
            }
        }
        window.onload = updateBlanks;
        </script>';   

}
	
   
   $style_width = ($simplifyQuestionsAuthoring == 'true') ? 'style="width:920px;"' : '';

      // Main container
   $form->addElement('html', '<div id="leftcontainer" '.$style_width.' class="quiz_answer_small_squarebox">');
   // answer
// $form->addElement('html', '<div class="row" ><div class="label"></div><div class="formw">' . get_lang('TypeTextBelow') . ', ' . get_lang('And') . ' ' . get_lang('UseTagForBlank') . '</div></div>');
   $form->addElement('html', '<div  id="back_toolbar_style" align="right" style="positio:relative;">');
   $form->addElement('html', '<div id="back_toolbar_style_input"></div>');
   
    $form->addElement('html','<div id="response"></div>
                                
                                <img id="error_upl" title="" src="../img/exclamation.png" style="float: left; margin-top: 5px; display: none;">
                                <img id="buttonrefresh" style="display: inline; float: right; cursor: pointer; margin-top: -2px; margin-left: 15px;" src="../img/refresh_.png" onclick="updateBlanks()" alt="'.get_lang('Refresh').'" title="'.get_lang('Refresh').'">
                                <img id="buttonFill" style="display: inline; float: right; cursor: pointer; margin-top: -2px;" src="../img/smallbrackets.png" onclick="Addfillup()" alt="'.get_lang('Addblank').'" title="'.get_lang('Addblank').'">
                                <div style="clear:both;"></div>
                              </div>');
// $form -> addElement ('html_editor', 'answer', '<img src="../img/fill_field.png">','id="answer" cols="122" rows="6" onkeyup="javascript: updateBlanks(this);"', array('ToolbarSet' => 'TestQuestionDescription', 'Width' => '100%', 'Height' => '250'));
// $form -> addElement ('html_editor', 'answer', get_lang('FillTheBlanks'),'id="answer" cols="122" rows="6" onkeyup="javascript: updateBlanks(this);"', array('ToolbarSet' => 'TestQuestionDescription', 'Width' => '100%', 'Height' => '250'));

   $form->addElement('html_editor', 'answer', get_lang('FillTheBlanks'), 'id="answer" cols="122" rows="6" onkeyup="javascript: updateBlanks(this);"', array('ToolbarSet' => 'TestProposedAnswer', 'Width' => '100%', 'Height' => '200'));
 
   
   
   $form->addRule('answer', get_lang('GiveText'), 'required');
   $form->addRule('answer', get_lang('DefineBlanks'), 'regex', '/\[.*\]/');

   //added multiple answers
// $form -> addElement ('checkbox','multiple_answer','', get_lang('FillInBlankSwitchable'));
   $form->addElement('html', '<br />');
   $form->addElement('html', '<div id="blanks_weighting"></div>');
   $form->addElement('html', '</div>');

  // Feedback container
   $mtfc = ($simplifyQuestionsAuthoring == 'true') ? '5px' : '25px';
   $form->addElement('html', '<div id="feedback_container" style="float:left;width:100%;margin-top:'.$mtfc.';">');
// $form->addElement('html', '<br /><br />');
   if($simplifyQuestionsAuthoring == 'false'){
   $form->addElement('html', '<div style="float:left;width:52%;">' . get_lang('FeedbackIfTrue'));
// $form->addElement('textarea', 'comment[1]', null, 'id="comment[1]" cols="55" rows="1"');
   $form->add_html_editor('comment[1]','', false, false, array('ToolbarSet' => 'TestProposedFeedback', 'Width' => '400px', 'Height' => '70px'));
   $form->addElement('html', '</div>');
   }
   $form->addElement('html', '<div style="float:right;text-align:right">');
   if($simplifyQuestionsAuthoring == 'false'){
   $form->addElement('html', '<div style="float:left;text-align:left">' . get_lang('FeedbackIfFalse'));
// $form->addElement('textarea', 'comment[2]', null, 'id="comment[2]" cols="55" rows="1"');
   $form->add_html_editor('comment[2]','', false, false, array('ToolbarSet' => 'TestProposedFeedback', 'Width' => '400px', 'Height' => '70px'));
        $form->addElement('html', '</div>');
   }
   $form->addElement('html', '</div>');
   $form->addElement('html', '<div style="float:right;text-align:left">');
   // setting the save button here and not in the question class.php
   $mtsq = ($simplifyQuestionsAuthoring == 'true') ? '0px' : '20px';
   $form->addElement('style_submit_button', 'submitQuestion', get_lang('Validate'), 'class="save" style="float:right!important;margin-top:'.$mtsq.';"');
   $form->addElement('hidden', 'submitform');
   $form->addElement('hidden', 'questiontype','4');
   $renderer = & $form->defaultRenderer();
   $renderer->setElementTemplate('<td><!-- BEGIN error --><span class="form_error">{error}</span><!-- END error --><br/>{element}</td>');
   $renderer->setElementTemplate('{element}&nbsp;', 'lessAnswers');
   $renderer->setElementTemplate('{element}&nbsp;', 'submitQuestion');
   $renderer->setElementTemplate('{element}', 'moreAnswers');

   // End feedback container
   $form->addElement('html', '</div></div>');

   $form->setDefaults($defaults);
      $user_agent = $_SERVER['HTTP_USER_AGENT'];
      $nav=trim(substr($user_agent,24,9));
      
      if ($nav == 'MSIE 7.0'){
          $top = '72px';          
      }else{
          $top = '20px';        
      }
      //echo '<script> console.log("'.$_course['path'].'")</script>';
    echo '<style>
        .ufile{
            float: left;
            display: none;
            }</style>
    <form id="question_admin_form2" method="post" action="upload.php" class="ufile">
    <input type="hidden" size="10" name="coursepath" value="'.$_course['path'].'" >
    <input  id="fileToUpload" type="file" size="10" name="fileToUpload" >
    </form>';
        
  }

  /**
   * abstract function which creates the form to create / edit the answers of the question
   * @param the formvalidator instance
   */
  function processAnswersCreation($form) {
   global $charset;
   
   $_SESSION['editQn'] = '1';
   $answer = $form->getSubmitValue('answer');
   //Due the fckeditor transform the elements to their HTML value
   $answer = api_html_entity_decode($answer, ENT_QUOTES, $charset);

   //remove the :: eventually written by the user
   $answer = str_replace('::', '', $answer);
   $answer = str_replace('<u><span id="block">', '', $answer);
   $answer = str_replace('</span></u>', '', $answer);
   $answer = str_replace('<u>', '', $answer);
   $answer = str_replace('</u>', '', $answer);
   $answer = str_replace('[[', '[', $answer);
   $answer = str_replace(']]', ']', $answer);
   // Get the value of feedback fields
   $feedback_if_true = $form->getSubmitValue('comment[1]');
   $feedback_if_false = $form->getSubmitValue('comment[2]');

   $feedback = array('comment[1]' => $feedback_if_true, 'comment[2]' => $feedback_if_false);
   $feedback_comment = serialize($feedback);

   // get the blanks weightings
   $nb = preg_match_all('/\[[^\]]*\]/', $answer, $blanks);
   if (isset($_GET['editQuestion']) || isset($_GET['newQuestion'])) {
    $this->weighting = 0;
   }

   if ($nb > 0) {
    $answer .= '::';
    for ($i = 0; $i < $nb; ++$i) {
     $answer .= $form->getSubmitValue('weighting[' . $i . ']') . ',';
     $this->weighting += $form->getSubmitValue('weighting[' . $i . ']');
    }
    $answer = api_substr($answer, 0, -1);
   }
   $is_multiple = $form->getSubmitValue('multiple_answer');
   $answer.='@' . $is_multiple;

   $this->save();
   $objAnswer = new answer($this->id);
   $objAnswer->createAnswer($answer, 0, $feedback_comment, 0, '');
   $objAnswer->save();
  }

  /**
   * Display the question in tracking mode (use templates in tracking/questions_templates)
   * @param $nbAttemptsInExercise the number of users who answered the quiz
   */
  function displayTracking($exerciseId, $nbAttemptsInExercise) {

   if (!class_exists('Answer'))
    require_once(api_get_path(SYS_CODE_PATH) . 'exercice/answer.class.php');

   $stats = $this->getAverageStats($exerciseId, $nbAttemptsInExercise);
   include(api_get_path(SYS_CODE_PATH) . 'exercice/tracking/questions_templates/fill_in_blanks.page');
  }

  /**
   * Returns learners choices for each question in percents
   * @param $nbAttemptsInExercise the number of users who answered the quiz
   * @return array the percents
   */
  function getAverageStats($exerciseId, $nbAttemptsInExercise) {

   $preparedSql = 'SELECT COUNT(1) as nbCorrectAttempts
						FROM ' . Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT) . ' as attempts
						INNER JOIN ' . Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES) . ' as exercises
							ON exercises.exe_id = attempts.exe_id
						WHERE course_code = "%s"
						AND attempts.question_id = %d
						AND attempts.question_id = %d
						AND marks = %d
						GROUP BY answer';
   $sql = sprintf($preparedSql, api_get_course_id(), $exerciseId, $this->id, $this->weighting);
   $rs = Database::query($sql, __FILE__, __LINE__);

   $stats['correct'] = array();
   $stats['correct']['total'] = intval(@mysql_result($rs, 0, 'nbCorrectAttempts'));
   $stats['correct']['average'] = $stats['correct']['total'] / $nbAttemptsInExercise * 100;

   $stats['wrong'] = array();
   $stats['wrong']['total'] = $nbAttemptsInExercise - $stats['correct']['total'];
   $stats['wrong']['average'] = 100 - $stats['correct']['average'];


   return $stats;
  }
  
  public function getHtmlQuestionResult($objQuestion, $attemptId, &$totalScore, &$totalWeighting, $dbName = '') {
        $feedback_if_true = $feedback_if_false = '';
        
        $questionId = $objQuestion->selectId();
        
        $objAnswerTmp = new Answer($questionId, $dbName);
        $nbrAnswers = $objAnswerTmp->selectNbrAnswers();
        
        $mediaPosition = $objQuestion->selectMediaPosition();
        $questionDescription = api_parse_tex($objQuestion->selectDescription());
        $questionDescription = str_replace('../default_course_document/', api_get_path(WEB_CODE_PATH).'default_course_document/', $questionDescription); 
        $questionWeighting = $objQuestion->selectWeighting();
        
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
        
        $tbl_track_attempt = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);
        
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

                    $queryfill = "select answer from $tbl_track_attempt where exe_id = $attemptId and question_id= $questionId";
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

                    $queryfill = "SELECT answer FROM " . $tbl_track_attempt . " where exe_id = $attemptId and question_id= $questionId";
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
                $s .= '<div class="quiz_content_actions quit_border" style="width:44%;float:left;height:auto;min-height:300px;overflow:auto;">';
            }
            $s .= '<div class="scroll_feedback2"><b>' . $answer . '</b></div>';
            $fy = 0;
            $fn = 0;

            for ($k = 0; $k < sizeof($feedback_anscorrect); $k++) {
                if ($feedback_anscorrect[$k] == "Y") {
                    $fy++;
                } else {
                    $fn++;
                }
            }
            
            /*$s .= '<table width="100%" border="0">';
            if ($fy >= $fn && $fy > 0) {                
                $feedbackDisplay = '<b>' . get_lang('Feedback') . ' : </b><span style="font-weight: bold; color: #008000;">'.$feedback_true.'</span>';
            } else {                
                $feedbackDisplay = '<b>' . get_lang('Feedback') . ' : </b><span style="font-weight: bold; color: #FF0000;">'.$feedback_false.'</span>';
            }
            $s .= '</table></div>';*/
            $s .= '</div>';
            $i++;
        }
        
        
        if ($mediaPosition == 'right') {
            $s .= '<div class="span5 quizPart" style="width:500px;float:right;overflow:auto !important;text-align:center;">'.$questionDescription.'</div>';
        } 
        
        //$s .= '<div class="span11">'.$feedbackDisplay.'</div>';
        
        $s .= '<div class="clear"></div><table style="clear:both;width:100%;margin:15px 0;" border="0">';
        if ($fy >= $fn && $fy > 0) {
            $s .= '<tr><td style="padding:2px; border:none;"><b><div class="feedback-right feed-custom-right">' . get_lang('Feedback') . '</div></b></td></tr>';
            $s .= '<tr><td align="left" style="padding:2px; border:none;"><div class="feedback-right">' . $feedback_true . '</div></td></tr>';
        } else {
            $s .= '<tr><td style="padding:2px; border:none;"><b><div class="feedback-wrong feed-custom-wrong">' . get_lang('Feedback') . '</div></b></td></tr>';
            $s .= '<tr><td align="left" style="padding:2px; border:none;"><div class="feedback-wrong">' . $feedback_false . '</div></td></tr>';
        }
        $s .= '</table>';
        
        
        // score
        $totalWeighting += $questionWeighting;
        
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
    $inpReadOnly = $readonly?' readonly':'';
    if (!empty($attempt)) {
        $str = $attempt[0];
        $str = str_replace("<br />", "", $str);
        $str = str_replace("<s>", "", $str);
        $str = str_replace("</s>", "", $str);
        preg_match_all('#\[([^[]*)\]#', $str, $arr);
        $choice = $arr[1];
        $currAnswer = array();
        if (!empty($choice)) {
            for ($i = 0; $i < count($choice); $i++) {
                list($ans, $correct) = explode('/', strip_tags($choice[$i]));
                $currAnswer[$i] = str_replace('&nbsp;', '', trim($ans));                           
            }                        
        }
    }

    $s = '';
    if ($mediaPosition == 'top') {
        $s .= '<div class="span5 quizPart media-top">'.$questionDescription.'</div>';
    } 
    $s .= '<div class="span7 quizPart question-answers" style="'.$media_w.'">';
    for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
        $answer = $objAnswer->selectAnswer($answerId);
        // splits text and weightings that are joined with the character '::'
        list($answer) = explode('::', $answer);

        // because [] is parsed here we follow this procedure:
        // 1. find everything between the [tex] and [/tex] tags
        $startlocations = api_strpos($answer, '[tex]');
        $endlocations = api_strpos($answer, '[/tex]');

        if ($startlocations !== false && $endlocations !== false) {
            $texstring = api_substr($answer, $startlocations, $endlocations - $startlocations + 6);
            // 2. replace this by {texcode}
            $answer = str_replace($texstring, '{texcode}', $answer);
        }

        // 3. do the normal matching parsing
        // replaces [blank] by an input field
        //getting the matches
        preg_match_all('/\[[^]]+]/', $answer, $matches);
        $x = 0;
        foreach ($matches[0] as $match) {
            /* @var $answer_len type */
            $answer_len = intval(strlen(str_replace('<u>', '', str_replace('</u>', '', $match)))-2);
            $answer = str_replace($match, '<input type="text" name="choice[u'.$questionId.'][]" size="'.($answer_len).'" value="'.  ($currAnswer[$x]).'" '.$inpReadOnly.' />', $answer);                    
            $answer = str_replace('../default_course_document/', api_get_path(WEB_CODE_PATH).'default_course_document/', $answer); 
            $x++;
        }
        $answer = str_replace('choice[u','choice[',$answer);
        // 5. replace the {texcode by the api_pare_tex parsed code}
        $texstring = api_parse_tex($texstring);
        $answer = str_replace("{texcode}", $texstring, $answer);
    }
    $s .= '<div class="span6 border-none" style="overflow: auto;">';
    $s .= $answer;
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
