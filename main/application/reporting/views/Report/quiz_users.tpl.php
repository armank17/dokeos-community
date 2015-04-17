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
    <p><a class="pull-right" id="quizzes_back" href="#"><?php echo $this->encodingCharset($this->get_lang("BackToQuiz")); ?></a></p>
    <div class="clear"></div>
    <div class="row-fluid" id="search-filters">
        <!-- Search Form -->
        <?php echo $this->setTemplate('search_form', 'Report'); ?>
    </div> 
    <!-- Pagination -->
    <div id="pagination">    
        <?php 
            if ($this->paginator->num_pages > 1) {
                echo $this->paginator->display_pages();
            }
        ?>
    </div>
    <h4><?php echo $this->quizMode == 'exam'?$this->encodingCharset($this->get_lang("Exam")):$this->encodingCharset($this->get_lang("Quiz")); ?> : <?php echo $this->encodingCharset($this->quizInfo['title']); ?></h4>
    <div class="data-container">
        <table name="list_quiz_learners" id="list_quiz_learners" class="responsive large-only table-striped">
            <thead>
                <tr>
                    <th><?php echo $this->encodingCharset($this->get_lang("LastName")); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang("FirstName")); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang("ReportScore")); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang("ScormTime")); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang("Attempts")); ?></th>
                    <th class="print_invisible"><?php echo $this->encodingCharset($this->get_lang("Detail")); ?></th>						
                </tr>
            </thead>
            <tbody>
                <?php                            
                    if (!empty($this->quizUsersData)):
                        foreach ($this->quizUsersData as $userId => $data):
                ?>
                        <tr>
                            <td><?php echo $this->encodingCharset($data['lastname']); ?></td>
                            <td><?php echo $this->encodingCharset($data['firstname']); ?></td>
                            <td align="center"><?php echo $data['score']; ?></td>
                            <td align="center"><?php echo $data['time']; ?></td>
                            <td align="center"><?php echo $data['attempts']; ?></td>
                            <td align="center">
                                <?php if (!empty($data['attempt_id'])): ?>
                                    <a class='action_quiz_users' id='' href='<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=ReportAjax&func=displayUserQuizResult&quizId='.$this->selectedQuizId.'&exeExoId='.$data['exe_exo_id'].'&courseCode='.$data['course_code'].'&learnerId='.$userId.'&sessionId='.$data['session_id'].'&mode='.$data['quiz_mode'].'&attempt_id='.$data['attempt_id'].'&currentTab='.$this->currentTab; ?>'>
                                        <img src='<?php echo api_get_path(WEB_CSS_PATH).$this->stylesheet; ?>/images/action/reporting32.png'>
                                    </a>
                                <?php else: ?>
                                    <img class="cut-tooltip" title="<?php echo $this->encodingCharset($this->get_lang('NoResults')); ?>" src="<?php echo api_get_path(WEB_CSS_PATH).$this->stylesheet; ?>/images/action/reporting32.png" />
                                <?php endif; ?>
                            </td>
                        </tr>                                 
                <?php
                        endforeach;
                    else:
                ?>
                      <tr><td colspan="6" align="center"><em><?php echo $this->encodingCharset($this->get_lang('NoResults')); ?></em></td></tr>
                <?php        
                    endif;
                ?>
            </tbody>
        </table>
    </div>
    <p class="pull-right">
        <a href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=Report&func=export&type=quiz_users&quizId='.$this->selectedQuizId.'&courseCode='.$this->selectedQuizCourse.'&quizMode='.$this->selectedQuizMode; ?>" id="export_quiz_learners_list">
            <img src="<?php echo api_get_path(WEB_IMG_PATH).'csv.png'; ?>" style="vertical-align: middle;width: 22px;height: 22px;" />
            <?php echo $this->encodingCharset($this->get_lang("Export")); ?>
        </a>&nbsp;|&nbsp;
        <a title="<?php echo $this->encodingCharset($this->get_lang("Print")); ?>" href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=ReportPrint&func=quizUsers&searchText='.urlencode($this->txtSearchDefault).'&id='.$this->selectedQuizId.'&courseCode='.$this->selectedQuizCourse.'&mode='.$this->selectedQuizMode; ?>" class="reporting-print">
            <img src="<?php echo api_get_path(WEB_IMG_PATH).'print_22.png'; ?>" style="vertical-align: middle;width: 22px;height: 22px;" />
            <?php echo $this->encodingCharset($this->get_lang("Print")); ?>
        </a>
    </p>
</div>
<script>
    if ($("#quizzes_back").length) {
        $("#quizzes_back").click(function(e) {
           e.preventDefault(); 
           ReportingModel.displayTabContent($("#tablist1-panel4"), 'displayQuizzesTab');
        });
    }
    if ($(".paginate").length) {
        $(".paginate").click(function(e){
            ReportingModel.paginateQuizUsers(e, $(this));
        });
    }
    if ($(".action_quiz_users").length) {
        $(".action_quiz_users").click(function(e){
            ReportingModel.displayUserQuizResult(e, $(this));
        });
    }
    if ($(".cut-tooltip").length > 0) {
        ReportingModel.cutTooltip();
    }
    if ($(".reporting-print").length) {
        $(".reporting-print").click(function(e){            
            ReportingModel.printPage(e, $(this));
        });
    }
</script>
<div id="closeText" style="display: none;" class="<?php echo get_lang('Close') ?>"></div>