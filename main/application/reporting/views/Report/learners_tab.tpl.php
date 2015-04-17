<div class="clearfix"></div>
<?php if ($this->printPage != 'print'): ?>
<div class="row-fluid" id="search-filters">
    <!-- Search Form -->
    <?php echo $this->setTemplate('search_form', 'Report'); ?>
</div>                   
<!-- Pagination -->
<div id="pagination">    
    <?php 
        if ($this->paginator->num_pages > 1 && !$this->isEmpty) {
            echo $this->paginator->display_pages();
        }
    ?>
</div>
<span>
    <h4><?php echo $this->get_lang('LearnersAverageValues'); ?>
    <?php 
        if (!empty($this->txtSearchDefault)) {
            echo ' - '.$this->get_lang('SearchByLearnerFirtnameOrLastname').' : '.$this->encodingCharset($this->txtSearchDefault);
        }
    ?>
    </h4>
</span>
<?php endif; ?>
<div class="data-container">
    <?php if (!$this->isEmpty || $this->printPage == 'print'): ?>
        <table name="learners" id="courses" class="responsive large-only table-striped">
            <thead>
                <tr>
                    <th><?php echo $this->encodingCharset($this->get_lang('LastName')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('FirstName')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('LatestConnection')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('ModulesTime')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('ModulesProgress')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('ModulesScore')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('QuizzesScore')); ?></th>
                    <?php if ($this->printPage != 'print'): ?>
                    <th class="print_invisible"><?php echo $this->encodingCharset($this->get_lang('Detail')); ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>                            
                <?php                            
                    if (!empty($this->learnersData)):
                        foreach ($this->learnersData as $uid => $data):
                ?>
                        <tr>
                            <td align="center"><?php echo $this->encodingCharset($data['lastname']); ?></td>
                            <td align="center"><?php echo $this->encodingCharset($data['firstname']); ?></td>
                            <td align="center"><?php echo $this->encodingCharset($data['lastest_connection']); ?></td>
                            <td align="center"><?php echo $data['modules_time']; ?></td>
                            <td align="center"><?php echo $data['modules_progress']; ?></td>
                            <td align="center"><?php echo $data['modules_score']; ?></td>
                            <td align="center"><?php echo $data['quizzes_score']; ?></td>
                            <?php if ($this->printPage != 'print'): ?>
                            <td align="center">
                                <a class='action_learner' id='' href='<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=ReportAjax&func=displayLearnerDetail&learnerId='.$uid; ?>'>
                                    <img src='<?php echo api_get_path(WEB_CSS_PATH).$this->stylesheet; ?>/images/action/reporting32.png'>
                                </a>
                            </td>
                            <?php endif; ?>
                        </tr>                                 
                <?php
                        endforeach;
                    else:
                ?>
                      <tr><td colspan="8" align="center"><em><?php echo $this->encodingCharset($this->get_lang('NoResults')); ?></em></td></tr>
                <?php        
                    endif;
                ?>
            </tbody>
        </table>
    <?php else: ?>    
        <div class="warning-message"><?php echo $this->encodingCharset($this->get_lang('UseFiltersToSelectReporting')); ?></div>    
    <?php endif; ?>
</div>
<input type="hidden" name="hid_action_code" id="hid_action_code" />
<?php if ($this->printPage != 'print' && !$this->isEmpty): ?>
<span class="pull-right">
    <a href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=Report&func=export&type=learners'; ?>" id="learner_export" >
        <img src="<?php echo api_get_path(WEB_IMG_PATH).'csv.png'; ?>" style="vertical-align: middle;width: 22px;height: 22px;" />
        <?php echo $this->encodingCharset($this->get_lang("Export")); ?>
    </a>&nbsp;|&nbsp;
    <a title="<?php echo $this->encodingCharset($this->get_lang("Print")); ?>" href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=ReportPrint&func=learners&currentTab='.$this->currentTab.'&searchText='.urlencode($this->txtSearchDefault); ?>" class="reporting-print">
        <img src="<?php echo api_get_path(WEB_IMG_PATH).'print_22.png'; ?>" style="vertical-align: middle;width: 22px;height: 22px;" />
        <?php echo $this->encodingCharset($this->get_lang("Print")); ?>
    </a>
</span>
<?php endif; ?>
<div class="clearfix"></div>
<script>
    if ($(".action_learner").length) {
        $(".action_learner").click(function(e){
            ReportingModel.displayLearnerDetail(e, $(this));
        });
    }
    if ($(".reporting-print").length) {
        $(".reporting-print").click(function(e){            
            ReportingModel.printPage(e, $(this));
        });
    }
</script>
<div id="closeText" style="display: none;" class="<?php echo get_lang('Close') ?>"></div>