<?php
/**
 * It displays html snippet to help views
 */

function displayHtmlQuestionAnswers($objAnswer, $objQuestion, $exerciseId) {

    if (!is_object($objQuestion) && !is_object($objAnswer)) { return false; }
    
    $objExercise = new Exercise();
    $objExercise->read($exerciseId);
    $exeId = $objExercise->getLastUserAttemptId($exerciseId);
    $mediaPosition = $objQuestion->selectMediaPosition();
    $questionDescription = api_parse_tex($objQuestion->selectDescription());
    $questionDescription = str_replace('../default_course_document/', api_get_path(WEB_CODE_PATH).'default_course_document/', $questionDescription); 
    $questionDescription = str_replace('EconCensus64.mp3', 'EconomicCensus.mp3', $questionDescription);
    $questionId = $objQuestion->selectId();
    $type = $objQuestion->type;
    $nbrAnswers = $objAnswer->selectNbrAnswers();   
    $attempt = $objExercise->getQuestionAnswersTrackAttempt($exeId, $questionId);        
    $media_w = in_array($mediaPosition, array('top', 'nomedia'))?'width:95%':'';
    switch ($type) {
        case UNIQUE_ANSWER:
            if (count($nbrAnswers) > 0) {
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
                    $s .= "<input class='answer' id='radio-" . $questionId . "-" . $answerId . "' type='radio' name='choice[".$questionId."]' value='".$answerId."' $checked><input type='hidden' name='choice2[".$questionId."]' value='0'>";
                    $s .= '<label class="answer" for="radio-' . $questionId . '-' . $answerId . '">' . strip_tags($answer,'<a><span><img><sub><sup>') . '</label>';
                    $s .= '</div>';                    
                }
                $s .= '</div>';                
                if ($mediaPosition == 'right') {
                    $s .= '<div class="span5 quizPart">'.$questionDescription.'</div>';
                }           
            }
            // @todo media position
            $html .= $s;
            break;
        case MULTIPLE_ANSWER:            
            if (count($nbrAnswers) > 0) {
                $s = '';
                if ($mediaPosition == 'top') {
                    $s .= '<div class="span5 quizPart media-top">'.$questionDescription.'</div>';
                } 
                $s .= '<div class="span7 quizPart question-answers" style="'.$media_w.'">';
                for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
                    $answer = api_parse_tex($objAnswer->selectAnswer($answerId)); 
                    $answer = str_replace('../default_course_document/', api_get_path(WEB_CODE_PATH).'default_course_document/', $answer); 
                    $checked = !empty($attempt) && in_array($answerId, $attempt)?' checked':'';                    
                    $s .= '<div class="span6 custom-checkbox">';
                    $s .= " <input id='check-" . $questionId . "-" . $answerId . "' class='answer' type='checkbox' name='choice[".$questionId."][".$answerId."]' value='1' $checked />
                            <input type='hidden' name='choice2[" . $questionId . "][0]' value='0' />
                            <label for='check-" . $questionId . "-" . $answerId . "' class='answer'>".strip_tags($answer,'<a><span><img><sub><sup>')."</label>";
                    $s .= '</div>';
                }
                $s .= '</div>';
                if ($mediaPosition == 'right') {
                    $s .= '<div class="span5 quizPart">'.$questionDescription.'</div>';
                } 
            }
            $html .= $s;
            break;        
        case REASONING:
            if (count($nbrAnswers) > 0) {
                $s = '';
                if ($mediaPosition == 'top') {
                    $s .= '<div class="span5 quizPart media-top">'.$questionDescription.'</div>';
                } 
                $s .= '<div class="span7 quizPart question-answers" style="'.$media_w.'">';
                for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
                    $answer = api_parse_tex($objAnswer->selectAnswer($answerId));
                    $answer = str_replace('../default_course_document/', api_get_path(WEB_CODE_PATH).'default_course_document/', $answer); 
                    $checked = !empty($attempt) && in_array($answerId, $attempt)?' checked':'';
                    $s .= '<div class="span6 custom-checkbox">';
                    $s .= "<input id='check-" . $questionId . "-" . $answerId . "' class='checkbox answer' type='checkbox' name='choice[".$questionId."][".$answerId."]' value='1' $checked />
                           <input type='hidden' name='choice2[" . $questionId . "][0]' value='0'>";
                    $s .= '<label class="answer" for="check-' . $questionId . '-' . $answerId . '">' . strip_tags($answer,'<a><span><img><sub><sup>') . '</label>';
                    $s .= '</div>';
                }
                $s .= '</div>';
                if ($mediaPosition == 'right') {
                    $s .= '<div class="span5 quizPart">'.$questionDescription.'</div>';
                } 
            }     
            $html .= $s;
            break;
        case FILL_IN_BLANKS:
            if (count($nbrAnswers) > 0) {
                
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
                        $answer = str_replace($match, '<input type="text" name="choice[u'.$questionId.'][]" size="'.($answer_len).'" maxlength="'.$answer_len.'" value="'.$currAnswer[$x].'"/>', $answer);                    
                        $answer = str_replace('../default_course_document/', api_get_path(WEB_CODE_PATH).'default_course_document/', $answer); 
                        $x++;
                    }
                    $answer = str_replace('choice[u','choice[',$answer);
                    // 5. replace the {texcode by the api_pare_tex parsed code}
                    $texstring = api_parse_tex($texstring);
                    $answer = str_replace("{texcode}", $texstring, $answer);
                }
                $s .= '<div class="span6 border-none">';
                $s .= $answer;
                $s .= '</div>';
                $s .= '</div>';
                if ($mediaPosition == 'right') {
                    $s .= '<div class="span5 quizPart">'.$questionDescription.'</div>';
                } 
            }
            $html .= $s;
            break;
        case FREE_ANSWER:
            $value = !empty($attempt[0])?$attempt[0]:'';
            $html_editor = api_return_html_area("newchoice", $value, '200', '100%', null, array('ToolbarSet' => 'TestFreeAnswer'));
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
            $html .= $s;
            break;
        case MATCHING:
            $cpt1 = 'A';
            $cpt2 = 1;
            $cntOption = 1;
            $Select = array();
            $QA = array();
            $s .= '<div class="span7 quizPart question-answers" style="width:100%">';
            $s .= '<input type="hidden" name="questionid" value="' . $questionId . '">';  
            if (count($nbrAnswers) > 0) {
                for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
                    $answer = $objAnswer->selectAnswer($answerId);
                    $answer = str_replace('../default_course_document/', api_get_path(WEB_CODE_PATH).'default_course_document/', $answer);                    
                    $answerCorrect = $objAnswer->isCorrect($answerId);
                    if (!$answerCorrect) {
                        $cntOption++;
                        $answer = api_parse_tex($answer);
                        $Select[$answerId]['Reponse'] = $answer;
                        $Select[$answerId]['Lettre'] = $answer;
                        $field_choice_name = "choice[" . $questionId . "][" . $answerId . "]";
                        $s .= "<input type='hidden' name='" . $field_choice_name . "' id='" . $field_choice_name . "' value='" . api_htmlentities($answer) . "' />";
                    } else {
                        $s .= "<input type='hidden' name='choice[" . $questionId . "][" . $answerId . "]' id='choice[" . $questionId . "][" . $answerId . "]' value='0'/>";
                        $QA[] = api_parse_tex($answer);
                        $option = array();
                        $option[] = (0);
                        foreach ($Select as $key => $val) {
                            $option[] = $val['Lettre'];
                        }
                        $cpt2++;
                    }                    
                }
            }
            $Qdiv_css = "Qdiv";
            $destinationbox_css = "destinationBox";
            $answerDiv_css = "answerDiv";
            $drag_answer_css = "drag_answer";
            $s .= '<script type="text/javascript">                     
                    $(document).ready(function(){ 
                        var cidReq = decodeURIComponent($("#cidReq").val());
                        var webPath = decodeURIComponent($("#webPath").val());';
                        for ($i = 0; $i <= count($QA); $i++) {
                            $ans_i = $i + 1;                            
                            if (!empty($attempt[$i])) {
                                $s .= 'selectMatchingAnswer("'.$questionId.'", "'.$attempt[$i].'", "'.$i.'");';
                            }                            
                            $s .= '                                
                            $("#a' . $questionId . '-' . $ans_i . '").draggable({
                                revert:true,	
                                revertDuration: 0.5,
                                helper: "clone",
                                start: function(event, ui){
                                    ui.helper.css("width", "26%");
                                } 
                            });
                            $("#q' . $questionId . '-' . $i . '").droppable({
                                drop: function(event, ui) {
                                    var cntOption = $("[name=cntOption-' . $questionId . ']").val();	
                                    var dragid = ui.draggable.attr("id");		
                                    var ansidarr = dragid.split("-");
                                    var ansid = ansidarr[1];
                                    var dropid = $(this).attr("id");
                                    var numericIdarr = dropid.split("-");
                                    var numericId = numericIdarr[1];
                                    var ansOption = (numericId*1) + (cntOption*1);
                                    var answer = document.getElementById("choice['.$questionId.']["+ansid+"]").value;
                                    var h = ui.draggable.css("height");
                                    $(this).html("<div class=\"drop-answer\" style=\"height:"+h+"\">"+answer+"</div>");
                                    document.getElementById("choice['.$questionId.']["+ansOption+"]").value = ansid;
                                    saveQuiz(webPath, cidReq);
                                }
                            });  ';
                        }
            $s .= ' });                     
                  </script>';
            $s .= ' <div id="dragScriptContainer">
                        <div id="' . $Qdiv_css . '">
                            <table width="100%">';
                    for ($i = 0; $i < count($QA); $i++) {
                        $s .= '<tr>
                                    <td  width="50%" >
                                        <div class="question matching-drag matching-drag-'. $questionId . '" id="c'. $questionId . '-' . $i . '">'. $QA[$i] . '</div>
                                    </td>
                                    <td  width="50%">
                                        <div id="q'. $questionId . '-' . $i . '" class="' . $destinationbox_css . ' matching-drag matching-drag-'. $questionId . '"></div>
                                    </td>
                               </tr>';
                    }
            $s .= '         </table>
                        </div>
                        <div id="' . $answerDiv_css . '" style="font-face:verdana;">
                            <table width="100%">';
                            for ($i = 1; $i < count($option); $i++) {
                                if (!empty($option[$i])) {
                                    $s .= '<tr><td><div class="'.$drag_answer_css.' matching-drag matching-drag-'. $questionId . '" id="a' . $questionId . '-' . $i . '">' . $option[$i] . '</div></td></tr>';
                                }
                            }
                      $s .= '</table>';
                 $s .= '</div>
                        <div id="dragContent"></div>';
            $s .= '     <input type="hidden" name="cntOption-' . $questionId . '" value="' . $cntOption . '">';
            $s .= ' </div>';
            $s .= ' </div>';
            $html .= $s;
            break;
    }
    echo $html;
}

function getQuizActions($exerciseId) {
    
    $buttons = '<li>
                    <a href="'.api_get_path(WEB_CODE_PATH).'exercice/exercice.php?'.api_get_cidreq().'">' . Display::return_icon('pixel.gif', get_lang('QuizzesList'), array('class' => 'toolactionplaceholdericon toolactionquestionlist')) . get_lang('QuizzesList') . '</a>                        
                </li>';
     $second_button = '<li>
                   <a href="'.api_get_path(WEB_CODE_PATH).'exercice/admin.php?'.api_get_cidreq().'&exerciseId=' . $exerciseId . '">' . Display::return_icon('pixel.gif', get_lang('QuizMaker'), array('class' => 'toolactionplaceholdericon toolactionquestion')) . get_lang('QuizMaker') . '</a>
                </li> ';
    if (api_is_allowed_to_edit()){
        $buttons.= $second_button;
    }  
    
    return '<ul class="nav-exammode">
             '.$buttons.'
            </ul>';
}

function getQuizFormSubmitBtn() {    
    return '<button type="submit" class="btn btn-inverse pull-right quizSubmit">'.get_lang("Validate").'</button>';    
}

function getHtmlHeadXtra() {
    return  '<link rel="stylesheet" href="'.api_get_path(WEB_CODE_PATH).'exercice/exam_mode/css/style_quiz.css" />'."\n".
            '<script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-ui/js/jquery-ui-1.8.1.custom.min.js" type="text/javascript"></script>'."\n".
            '<link rel="stylesheet" type="text/css" href="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-ui/css/ui-lightness/jquery-ui-1.8.1.custom.css" />'."\n".
            '<script src="'.api_get_path(WEB_LIBRARY_PATH) . 'javascript/responsive/js/jquery.ui.touch-punch.min.js"></script>'."\n".
            '<script src="'.api_get_path(WEB_CODE_PATH).'exercice/exam_mode/js/exam_mode.js" language="javascript"></script>';
}

?>
