<div id="query-breadcrumb">
    <em>
    <?php 
        if (!empty($this->queryFilters)) 
        {
            echo $this->encodingCharset($this->get_lang('YourQuery').' : '.implode(' > ', $this->queryFilters));
        }
    ?>
    </em>
</div>
<div id="dataDiv">
    
    <?php if ($this->currentTab == 'user_detail') :?>
        <p><a class='pull-right action_learner' id='' href='<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=ReportAjax&func=displayLearnerDetail&learnerId='.$this->learnerInfo['user_id']; ?>'><?php echo $this->encodingCharset($this->get_lang("BackToUserDetail")); ?></a></p>
    <?php elseif ($this->currentTab == 'learner_reporting'): ?>
        <p><a class='pull-right' id='' href='<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=Report&func=learner&learnerId='.$this->learnerInfo['user_id']; ?>'><?php echo $this->encodingCharset($this->get_lang("Back")); ?></a></p>
    <?php elseif ($this->currentTab == 'module_users'): ?>        
        <p><a class='pull-right action_module_users' id='' href='<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=ReportAjax&func=displayModuleUserDetail&lpId='.$this->selectedModuleId.'&courseCode='.$this->selectedModuleCourse.'&learnerId='.$this->selectedModuleLearner.'&currentTab='.$this->currentTab; ?>'><?php echo $this->encodingCharset($this->get_lang("Back")); ?></a></p>
    <?php else: ?>        
        <p><a class="pull-right action_quiz" id="quiz_back" href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=ReportAjax&func=displayQuizUsers&id='.$this->selectedQuizId.'&courseCode='.$this->selectedQuizCourse.'&mode='.$this->selectedQuizMode; ?>"><?php echo $this->encodingCharset($this->get_lang("BackToQuizUsers")); ?></a></p>
    <?php endif; ?>
    
    <h4><?php echo $this->encodingCharset($this->courseInfo['name']); ?> <?php echo !empty($this->selectedSessionId)?' - '.$this->get_lang('Session').' : '.$this->sessionInfo['name']:''; ?> - <?php echo $this->encodingCharset($this->quizInfo['title']); ?> - <?php echo $this->encodingCharset($this->learnerInfo['firstname'].' '.$this->learnerInfo['lastname']); ?></h4>    
    <div class="data-container">
        <?php echo $this->encodingCharset($this->quizResultContent); ?>        
        <div id="question_score">
            <?php 
            $my_result = number_format(($this->totalScore / $this->totalWeighting) * 100, 1, '.', '');
            $my_result = float_format($my_result, 0);
            echo $this->encodingCharset($this->get_lang('YourTotalScore')).' : '.round($this->totalScore).' / '.$this->totalWeighting; echo " ($my_result%)";
            ?>
        </div>                
    </div>
    
    <p class="pull-right">
        <a title="<?php echo $this->encodingCharset($this->get_lang("Print")); ?>" href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=ReportPrint&func=userQuizResult&quizId='.$this->selectedQuizId.'&courseCode='.$this->selectedQuizCourse.'&sessionId='.$this->selectedModuleSession.'&learnerId='.$this->learnerInfo['user_id'].'&mode='.$this->selectedQuizMode.'&attempt_id='.$this->selectedQuizAttemptId.'&exeExoId='.$this->selectedQuizExeExoId; ?>" class="reporting-print">
            <img src="<?php echo api_get_path(WEB_IMG_PATH).'print_22.png'; ?>" style="vertical-align: middle;width: 22px;height: 22px;" />
            <?php echo $this->encodingCharset($this->get_lang("Print")); ?>
        </a>
    </p>    
</div>
<input type="hidden" name="currentTab" id="current-tab" value="<?php echo $this->currentTab; ?>" />
<script>
    if ($(".action_quiz").length) {
        $(".action_quiz").click(function(e){
            ReportingModel.displayQuizUsers(e, $(this));
        });
    }
    if ($(".action_learner").length) {
        $(".action_learner").click(function(e){
            ReportingModel.displayLearnerDetail(e, $(this));
        });
    }
    if ($(".action_module_users").length) {
        $(".action_module_users").click(function(e){
            ReportingModel.displayModuleUserDetail(e, $(this));
        });
    }
    if ($(".reporting-print").length) {
        $(".reporting-print").click(function(e){            
            ReportingModel.printPage(e, $(this));
        });
    }
</script>
<div id="closeText" style="display: none;" class="<?php echo get_lang('Close') ?>"></div>