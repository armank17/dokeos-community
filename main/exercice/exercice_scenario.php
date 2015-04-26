<?php
$language_file = 'exercice';

require_once '../inc/global.inc.php';

require_once 'exercise.class.php';
require_once 'question.class.php';
require_once 'answer.class.php';
require_once 'exercise.lib.php';

// including additional libraries
require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';

require_once '../newscorm/learnpath.class.php';
require_once '../newscorm/learnpathItem.class.php';
// setting the tabs
$this_section=SECTION_COURSES;

if(!api_is_allowed_to_edit()) {
	api_not_allowed(true);
}

$tool_name = TOOL_QUIZ;

/* ------------	ACCESS RIGHTS ------------ */
api_protect_course_script(true);

// additional javascript
$htmlHeadXtra[] = '
    <style type="text/css">
	form {
		border:0px;
	}
	div.row div.label{
		width: 10%;
		display: none;
	}
	div.row div.formw{
		width: 100%!important;
	}
        .alert
        {
            font-size: 1.3em;
            padding: 1em;
            text-align: center;
            white-space: nowrap;
            width: auto;
            word-wrap: normal;
        }        
        
        #courseintro {
            margin-top: 9px !important;
        }
        
        .blender {
            margin-top:0px !important;
        }
    </style>
    <script language="javascript">
        $(document).ready(function(){
            var tpl_id = $("#quiz-certificate").val();
            if(tpl_id == 1){
                    $("#certificate1").css({display: "block"});
                 $("#quiz-certificate-score").show();
                $.ajax({
                    type: "GET",
                    url: "exercise.ajax.php?'.api_get_cidReq().'&action=displayCertPicture&tpl_id=1",
                    success: function(data){
                        $("#quiz-certificate-thumb").show();
                        $("#quiz-certificate-thumb").html(data);
                    }
                });
            }
            $("input[name=\'randomQuestionsOpt\']").click(function(){
                
                if ($(this).val() == 0) {
                    $("select[name=\'randomQuestions\']").attr("disabled", true);
                    $("select[name=\'randomQuestions\']").val(0);
					updatecertificate();
                } else {
                    $("select[name=\'randomQuestions\']").attr("disabled", false);
                }
            });

           // change certificate template
           //var randomQuestions = $("select[name=\'randomQuestions\']").val();
           //if(randomQuestions == 1){
           //  $("select[name=\'randomQuestions\']").attr("disabled", false);
           //}
           
           if($("input[name=\'randomQuestionsOpt\']:checked").val() == 0)
				$("select[name=\'randomQuestions\']").attr("disabled", true);
           
           $("#quiz-certificate").change(function(){
                var tpl_id = $(this).val();

                $("#certificate1").css({display: "block"});
                $("#quiz-certificate-score").hide();
                $("#quiz-certificate-score").hide();
                if (tpl_id == 0) {
                    $("#quiz-certificate-score input[name=\'certificate_min_score\']").val(\'\');
                    $("#quiz-certificate-score").hide();
                    $("#certificate1").css({display: "none"});


                } else {
                    $("#quiz-certificate-score").show();
                    $("#certificate1").css({display: "block"});
                }
                $.ajax({
                    type: "GET",
                    url: "exercise.ajax.php?'.api_get_cidReq().'&action=displayCertPicture&tpl_id="+tpl_id,
                    success: function(data){
                        $("#quiz-certificate-thumb").show();
                        $("#quiz-certificate-thumb").html(data);
                    }
                });
           });

           $("a#exammode-'.intval($_GET['exerciseId']).'").click(function (event){
                var width = screen.width;
                var height = screen.height;
                var windowName = "Quiz";
                var windowSize = "width="+width+",height="+height;
                window.open("quiz_exam_mode.php?'.api_get_cidReq().'&exerciseId='.intval($_GET['exerciseId']).'&TB_iframe=true", windowName, windowSize);
           });
        //validate score
        $("#txtScore").keypress(validateNumber);
        });
        function validateNumber(event) {
            var key = window.event ? event.keyCode : event.which;
            if (event.keyCode == 8 || event.keyCode == 46
             || event.keyCode == 37 || event.keyCode == 39) {
                return true;
            }
            else if ( (key < 48 && key != 46) || key > 57 ) {
                return false;
            }
            else return true;         
        }
        /**
        * funtion for validate fieds
        **/
        function validateForm(){
        //validate min score
        var message = "";
        var validate = false;
        var values = {};
        $.each($("#exercice_scenario1").serializeArray(), function(i, field) {
            values[field.name] = field.value;
        });
        //validate score
        if(values[\'certificate_min_score\'] > 100){
            validate = true;
            message = "'.get_lang('ScoreMinimunCertificat').'";
        }else{
            $("#exercice_scenario1").submit();
           }
            if(validate){   
                $(document.createElement("div"))
                .attr({title: "'.  get_lang('Alert').'", "class": "alert"})
                .html(message)
                .dialog({
                    buttons: {OK: function(){$(this).dialog(\'close\');}},
                    close: function(){$(this).remove();},
                    draggable: true,
                    modal: true,
                    resizable: false,
                    width: "auto"
                });
            }
        }
    </script>
';

// Lp object
$learnpath_id = intval($_GET['lp_id']);
if (isset($_SESSION['lpobject'])) {
    $oLP = unserialize($_SESSION['lpobject']);
    if (is_object($oLP)) {
        if ($myrefresh == 1 OR (empty($oLP->cc)) OR $oLP->cc != api_get_course_id()) {
            if ($myrefresh == 1) {
                $myrefresh_id = $oLP->get_id();
            }
            $oLP = null;
            api_session_unregister('oLP');
            api_session_unregister('lpobject');
        } else {
            $_SESSION['oLP'] = $oLP;
            $lp_found = true;
        }
    }
}

// Add the extra lp_id parameter to some links
$add_params_for_lp = '';
if (isset($_GET['lp_id'])) {
    $add_params_for_lp = "&lp_id=".$learnpath_id;
}

$objExercise = new Exercise(1);

/*********************
 * INIT FORM
 *********************/
if (isset($_GET['exerciseId'])) {
    // Scenario 1
    $form = new FormValidator('exercice_scenario1', 'post', api_get_self().'?exerciseId='.Security::remove_XSS($_GET['exerciseId']).'&'.api_get_cidreq(), null, array('style' => 'width: 100%; border: 0px', 'onsubmit'=>'validateForm();return false;'));
    $objExercise->read(intval($_GET['exerciseId']));
    $form -> addElement ('hidden','edit','true');
} else {
    $add_params_for_lp = '';
    if (isset($_GET['lp_id'])) {
        $add_params_for_lp = "&lp_id=".Security::remove_XSS($_GET['lp_id']);
    }
    // Scenario 1
    $form = new FormValidator('exercice_scenario1', null,  api_get_self().'?'.  api_get_cidreq().$add_params_for_lp, null, array('style' => 'width: 100%; border: 0px'));
    $form->addElement('hidden','edit','false');
}

$objExercise->createScenarioForm($form);

if ($form->validate()) {
    $objExercise->processScenarioCreation($form);
    $my_quiz_id = $objExercise->id;
    if ($form->getSubmitValue('edit') == 'true') {
        if(isset($_SESSION['fromlp'])) {
            header('Location:admin.php?exerciseId='.$my_quiz_id.'&message=ExerciseEdited&'.api_get_cidreq().'&fromlp='.$_SESSION['fromlp']);
            exit;
        } else {
            header('Location:admin.php?exerciseId='.$my_quiz_id.'&message=ExerciseEdited&'.api_get_cidreq());
            exit;
        }
    } else {
        $my_quiz_id = $objExercise->id;
        header('Location:admin.php?'.api_get_cidreq().'&message=ExerciseAdded&exerciseId='.$my_quiz_id.$add_params_for_lp);
        exit;
    }
} else {
    $no_validate = true;
}

if ($no_validate === true) {
    if (isset($_SESSION['gradebook'])) { $gradebook = $_SESSION['gradebook']; }
    // header
    Display :: display_tool_header();
    // Tool introduction
    Display :: display_introduction_section(TOOL_QUIZ);
    if(api_get_setting('search_enabled')=='true' && !extension_loaded('xapian')) {
        //Display::display_error_message(get_lang('SearchXapianModuleNotInstaled'),false,true);
        $_SESSION["display_error_message"]=get_lang('SearchXapianModuleNotInstaled');
    }
    // actions
    echo '<div class="actions">';
    if (isset($_GET['exerciseId']) && $_GET['exerciseId'] > 0) {
        echo '<a href="admin.php?'.api_get_cidreq() . '&exerciseId='.Security::remove_XSS($_GET['exerciseId']).'">'.Display::return_icon('pixel.gif', get_lang('QuizMaker'), array('class' => 'toolactionplaceholdericon toolactionback')) . get_lang('QuizMaker').'</a>';
    }
    echo '</div>'; // end actions

    // start the content div
    echo '<div id="content_with_secondary_actions">';
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
if(isset($_SESSION['display_confirmation_message'])){
display::display_confirmation_message($_SESSION['display_confirmation_message'], false,true);
unset($_SESSION['display_confirmation_message']);
}
if(isset($_SESSION['display_error_message'])){
display::display_error_message($_SESSION['display_error_message'], false,true);
unset($_SESSION['display_error_message']);
}
    $sub_container_class = "";
    if (isset($_GET['modifyExercise'])) {
        $sub_container_class = "quiz_scenario_squarebox";
    }

    echo '<div id ="exercise_admin_container">';
    echo '  <table cellpadding="5" width="100%">
                <tr>
                    <td width="100%" valign="top">';
    echo '              <div id="exercise_admin_left_container" class="'.$sub_container_class.'">';
                            $form->display();
    echo '              </div>
                    </td>
                </tr>
            </table>
          </div>';

    // close the content div
    echo '</div>';
    echo '<div style="clear:both"></div>';
}

// Foter page
Display::display_footer();
?>
