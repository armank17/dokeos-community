<?php
require_once dirname(__FILE__) . '/autoload.php';

// access control
api_protect_course_script(true);

$htmlHeadXtra[] = getHtmlHeadXtra();

$exerciseId = intval($_GET['exerciseId']);
$objExercise = new Exercise();
$objExercise->read($exerciseId);
$questionList = $objExercise->selectQuestionList();

$answered = $objExercise->getCountQuestionAnswersAttempt($exerciseId);
Display::display_responsive_tool_header();
// Top actions bar
?>
<span class="span quizHead-right"><?php echo get_lang('Question Answered') . ": <span class='result'>" . $answered . "/" . count($questionList); ?></span></span>
<div class="container">
    <div class="row-fluid">
        <div class="span12 action">
            
            <!--<div class="container bar-action ">-->
            <!--<div class="content-padding-small">-->
            <!--<div class="row-fluid">-->            
            <?php echo getQuizActions($exerciseId); ?>
        </div>
        <!--</div>-->
        <!--</div>-->
    </div>
</div>



<!-- Content -->
<div class="container">
    <div id="content">
        <form id="quiz-form" method="POST" action="" name="quiz_form">
            <input type="hidden" id="exerciseId" name="exerciseId" value="<?php echo $exerciseId; ?>">   
            <input type="hidden" id="cidReq" value="<?php echo urlencode(api_get_cidreq()); ?>">
            <input type="hidden" id="webPath" value="<?php echo urlencode(api_get_path(WEB_PATH)); ?>">
            <div class="content-padding">
                <div class="row-fluid">
                    <div class="form-actions">
                        <span class="span10 quizHead"><?php echo $objExercise->exercise; ?></span>
                        <?php echo getQuizFormSubmitBtn(); ?>
                    </div>
                </div>
                <?php
                $i = 1;
                if (!empty($questionList)) {
                    foreach ($questionList as $questionId) {
                        $question = Question ::read($questionId);
                        $answers = new Answer($questionId);
                        ?>
                        <div class="row-fluid quiz">
                            <div class="span12 qtnCount"><?php echo get_lang('Question') . ': ' . $i; ?></div>
                            <div class="span12 qtnName"><?php echo $question->question; ?></div>
                            <?php
                            displayHtmlQuestionAnswers($answers, $question, $exerciseId);
                            ?>
                        </div>                                                 
                        <?php
                        $i++;
                    }
                } // end foreach questionList
                ?>
                <div class="row-fluid">
                    <div class="form-actions">
                        <?php echo getQuizFormSubmitBtn(); ?>
                    </div>
                </div>
            </div>
        </form> <!-- En quiz formulaire -->
    </div>

</div>
<!-- End content -->

<?php
Display::display_responsive_tool_footer();