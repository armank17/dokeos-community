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
    <?php else: ?>
        <p><a class="pull-right action_module" id="modules_back" href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=ReportAjax&func=displayModuleUsers&lpId='.$this->selectedModuleId.'&courseCode='.$this->selectedModuleCourse; ?>"><?php echo $this->encodingCharset($this->get_lang("BackToModuleUsers")); ?></a></p>
    <?php endif; ?>
    <div class="clear"></div>
    <h4><?php echo $this->encodingCharset($this->courseInfo['name']); ?> - <?php echo $this->encodingCharset($this->moduleInfo['name']); ?> - <?php echo $this->encodingCharset($this->learnerInfo['firstname'].' '.$this->learnerInfo['lastname']); ?></h4>    
    <div class="data-container">
        <table id="module-user" class="responsive large-only table-striped" width="100%">
            <thead>
                <tr>
                    <th width="50%"><?php echo $this->encodingCharset($this->get_lang('ScormLessonTitle')); ?></th>
                    <th width="15%"><?php echo $this->encodingCharset($this->get_lang('ScormStatus')); ?></th>
                    <th width="10%"><?php echo $this->encodingCharset($this->get_lang('ScormScore')); ?></th>
                    <th width="15%"><?php echo $this->encodingCharset($this->get_lang('ScormTime')); ?></th>
                    <th width="10%" class="print_invisible"><?php echo $this->encodingCharset($this->get_lang('Detail')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($this->moduleUserDetailData)): 
                        foreach ($this->moduleUserDetailData as $data):
                    
                        if (count($data['views']) > 1): // Details with multiple views                            
                ?>
                            <tr>
                                <td width="50%"><strong><?php echo $this->encodingCharset($data['name']); ?></strong></td>
                                <td width="15%"></td>
                                <td width="10%"></td>
                                <td width="15%"></td>
                                <td width="10%"></td>
                            </tr>
                            <?php 
                                if (!empty($data['views'])): 
                                    $i = 1;
                                    foreach ($data['views'] as $view):
                                        $styleColor = in_array($view['status'], array('completed', 'passed'))?'color:green':'color:black;';                                                 
                            ?>  
                                        <tr>
                                            <td width="50%" class="attempts-label"><?php echo $this->encodingCharset($this->get_lang('Attempt')).' '.(!empty($view['session_id'])?'('.$this->encodingCharset($this->get_lang('Session').': '.$view['session_name']).')':'('.$this->encodingCharset($this->get_lang('NoSessions')).')'); ?></td>
                                            <td width="15%" align="center" style="<?php echo $styleColor; ?>"><?php echo $this->encodingCharset($this->get_lang($this->moduleStatusLangVariables[$view['status']])); ?></td>
                                            <td width="10%" align="center"><?php echo in_array($view['item_type'], array('quiz', 'sco'))?($view['max_score'] > 0?round($view['score']).'/'.round($view['max_score']):'n.a.'):'n.a'; ?></td>
                                            <td width="15%" align="center"><?php echo api_format_time($view['time']); ?></td>
                                            <td width="10%" align="center">
                                                <?php if ($view['item_type'] == 'quiz'): ?>  
                                                    <?php if (!empty($view['exe_id'])): ?>
                                                        <a class='action_quiz_users' href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=ReportAjax&func=displayUserQuizResult&quizId='.$view['path'].'&exeExoId='.$view['path'].'&courseCode='.$data['course_code'].'&learnerId='.$data['learner_id'].'&sessionId='.$data['session_id'].'&mode=quiz&attempt_id='.$view['exe_id'].'&currentTab='.$this->currentTab.'&lpId='.$this->selectedModuleId.'&lpItemId='.$view['lp_item_id'].'&lpViewId='.$view['lp_view_id']; ?>">
                                                            <img src="<?php echo api_get_path(WEB_IMG_PATH).'quiz.gif'; ?>" />
                                                        </a>
                                                    <?php endif ?>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                <?php 
                                        $i++;
                                    endforeach; 
                                endif;
                           
                           else:
                               $styleColor = in_array($data['last_view']['status'], array('completed', 'passed'))?'color:green':'color:black;';                  
                ?>
                                <tr>                                    
                                    <td width="50%"><strong><?php echo $this->encodingCharset($data['name']); ?></strong></td>
                                    <td width="15%" align="center" style="<?php echo $styleColor; ?>"><?php echo $this->encodingCharset($this->get_lang($this->moduleStatusLangVariables[$data['last_view']['status']])); ?></td>
                                    <td width="10%" align="center"><?php echo in_array($data['last_view']['item_type'], array('quiz', 'sco'))?($data['last_view']['max_score'] > 0?round($data['last_view']['score']).'/'.round($data['last_view']['max_score']):'n.a.'):'n.a'; ?></td>
                                    <td width="15%" align="center"><?php echo api_format_time($data['last_view']['time']); ?></td>
                                    <td width="10%" align="center">
                                        <?php if ($data['last_view']['item_type'] == 'quiz' && !empty($data['last_view']['exe_id'])): ?>                                               
                                            <a class='action_quiz_users' href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=ReportAjax&func=displayUserQuizResult&quizId='.$data['last_view']['path'].'&exeExoId='.$data['last_view']['path'].'&courseCode='.$data['course_code'].'&learnerId='.$data['learner_id'].'&sessionId='.$data['session_id'].'&mode=quiz&attempt_id='.$data['last_view']['exe_id'].'&currentTab='.$this->currentTab.'&lpId='.$this->selectedModuleId.'&lpItemId='.$data['last_view']['lp_item_id'].'&lpViewId='.$data['last_view']['lp_view_id']; ?>">
                                                <img src="<?php echo api_get_path(WEB_IMG_PATH).'quiz.gif'; ?>" />
                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                <?php
                           endif;                                                                
                        endforeach;
                    else:
                ?>
                    <tr><td colspan="5" align="center"><em><?php echo $this->encodingCharset($this->get_lang('NoResults')); ?></em></td></tr>
               <?php endif; ?>
            </tbody>
        </table>
    </div>
    <p class="pull-right">
        <a title="<?php echo $this->encodingCharset($this->get_lang("Print")); ?>" href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=ReportPrint&func=userModuleDetail&currentTab='.$this->currentTab.'&lpId='.$this->selectedModuleId.'&courseCode='.$this->selectedModuleCourse.'&sessionId='.$this->selectedModuleSession.'&learnerId='.$this->learnerInfo['user_id']; ?>" id="course_print" class="reporting-print">
            <img src="<?php echo api_get_path(WEB_IMG_PATH).'print_22.png'; ?>" style="vertical-align: middle;width: 22px;height: 22px;" />
            <?php echo $this->encodingCharset($this->get_lang("Print")); ?>
        </a>
    </p>
    
</div>
<input type="hidden" name="currentTab" id="current-tab" value="<?php echo $this->currentTab; ?>" />
<script>
    if ($(".action_module").length) {
        $(".action_module").click(function(e){
            ReportingModel.displayModuleUsers(e, $(this));
        });
    }
    if ($(".action_learner").length) {
        $(".action_learner").click(function(e){
            ReportingModel.displayLearnerDetail(e, $(this));
        });
    }
    if ($(".action_quiz_users").length) {
        $(".action_quiz_users").click(function(e){
            ReportingModel.displayUserQuizResult(e, $(this));
        });
    }
    if ($(".reporting-print").length) {
        $(".reporting-print").click(function(e){            
            ReportingModel.printPage(e, $(this));
        });
    }
</script>
<div id="closeText" style="display: none;" class="<?php echo get_lang('Close') ?>"></div>