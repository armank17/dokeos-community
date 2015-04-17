<?php
require_once dirname(__FILE__).'/autoload.php';

// access control
api_protect_course_script(true);

$htmlHeadXtra[] = getHtmlHeadXtra();

$exerciseId = intval($_GET['exerciseId']);
$exeId = intval($_GET['exeId']);
$objExercise = new Exercise();
$objExercise->read($exerciseId);
$questionList = $objExercise->selectQuestionList();
$answered = $objExercise->getCountQuestionAnswersAttempt($exerciseId, $exeId);
$scores = $objExercise->getTrackExerciseScores($exeId);

// check if user is allowed to get certificate
$obj_certificate = new CertificateManager();
$certif_available = $obj_certificate->isUserAllowedGetCertificate(api_get_user_id(), 'quiz', $exeId, api_get_course_id());

Display::display_responsive_tool_header();
?>
<div class="container">
    <div class="row-fluid">
        <div class="span12 action">        
            <?php echo getQuizActions($exerciseId); ?>
        </div>
    </div>
</div>
<div class="container">
    <div id="content">
         <div style="padding-top:50px; ">
             <div class="row-fluid">
                 <div class="span12">
                     <div class="span6" style="text-align:center;">
                     <img style="" src="<?php echo api_get_path(WEB_IMG_PATH).'teacher_slate.png';?>" />
                    </div>
                     <div class="span6">
                     <table width="100%" cellpadding="3" cellspacing="3">
                            <tr>
                                <td colspan="2" align="center">
                                    <p class="questiontitle" style="margin-top:30px;"><?php echo get_lang('QuizDone'); ?>                                    
                                    <?php
                                    if ($certif_available) {
                                        echo '<a class="certificate-' . $exerciseId . '-link" href="#">' . Display::return_icon('certificate48x48.png', get_lang('GetCertificate'), array('style' => 'float:right;')) . '</a>';
                                        $obj_certificate->displayCertificate('html', 'quiz', $exerciseId, api_get_course_id(), null, true);
                                    }
                                    ?>                                        
                                    </p>
                                    <hr size="1" noshade="noshade"color="#cccccc"/>
                                </td>
                            </tr>
                            <tr><td>&nbsp;</td></tr>
                            
                             <tr>
                                 <td colspan="2" align="center" class="question-score">
                                     <?php echo get_lang('QuizExamResult') ?>:&nbsp;<?php echo round($scores['exe_result']).'/'.round($scores['exe_weighting']).' ('.$scores['percent'].'%)'?>
                                 </td>
                             </tr>
                             <tr><td>&nbsp;</td></tr>
                             <tr><td>&nbsp;</td></tr>
                        </table>
                    </div>
                 </div>
                 
             </div>
        <table class="validate-table" border="0" align="center">
            <tr>
                <td align="center">
                    
                </td>
                <td width="65%" valign="top">
                    <div class="answer" style="margin-left:80px !important;">
                        
                    </div>
                </td>
            </tr>
        </table>
    </div>
    </div>
   
</div>
<?php
Display::display_responsive_tool_footer();

