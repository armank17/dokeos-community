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
    <h4><?php echo $this->encodingCharset($this->get_lang('SessionsAverageValues')); ?>
    <?php 
        if (!empty($this->txtSearchDefault)) {
            echo ' - '.$this->get_lang('SearchBySessionName').' : '.$this->encodingCharset($this->txtSearchDefault);
        }
    ?>
    </h4>
</span>
<?php endif; ?>
<!-- Chart -->
<div class="span11 chart_print" id="chartContainer"></div>
<div class="data-container">
    <?php if (!$this->isEmpty || $this->printPage == 'print'): ?>
        <table id="courses" class="responsive large-only table-striped">
            <thead>
                <tr>
                    <th><?php echo $this->encodingCharset($this->get_lang('SessionName')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('Learners')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('Courses')); ?></th>
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
                    if (!empty($this->sessionsData)):
                        foreach ($this->sessionsData as $sessionId => $data):
                ?>
                        <tr>
                            <td><?php echo cut($this->encodingCharset($data['name']), 30, true); ?></td>
                            <td align="center"><?php echo $data['total_learners']; ?></td>
                            <td align="center"><?php echo $data['total_courses']; ?></td>
                            <td align="center"><?php echo $data['modules_time']; ?></td>
                            <td align="center"><?php echo $data['modules_progress']; ?></td>
                            <td align="center"><?php echo $data['modules_score']; ?></td>
                            <td align="center"><?php echo $data['quizzes_score']; ?></td>
                            <?php if ($this->printPage != 'print'): ?>
                            <td align="center">
                                <?php if ($data['total_courses'] > 0): ?>
                                    <a class='action_course_detail' id='hid_<?php echo $sessionId; ?>' href='<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=ReportAjax&func=displaySessionCourses&selectedCategory='.$data['category_id'].'&selectedSession='.$sessionId; ?>'>
                                        <img src='<?php echo api_get_path(WEB_CSS_PATH).$this->stylesheet; ?>/images/action/scorm_32.png' />
                                    </a>
                                <?php else: ?>
                                    <img src='<?php echo api_get_path(WEB_CSS_PATH).$this->stylesheet; ?>/images/action/scorm_32.png' class="cut-tooltip" title="<?php echo $this->encodingCharset($this->get_lang('NoResults')); ?>" />
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>
                        </tr>                                 
                <?php
                        endforeach;
                    else:
                ?>
                      <tr><td colspan="7" align="center"><em><?php echo $this->encodingCharset($this->get_lang('NoResults')); ?></em></td></tr>
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
<input type="hidden" name="selected" id="hid_action_code" />
<?php if ($this->printPage != 'print' && !$this->isEmpty): ?>
    <span class="pull-right">
        <a href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=Report&func=export&type=sessions'; ?>" id="session_export" >
            <img src="<?php echo api_get_path(WEB_IMG_PATH).'csv.png'; ?>" style="vertical-align: middle;width: 22px;height: 22px;" />
            <?php echo $this->encodingCharset($this->get_lang("Export")); ?>
        </a>&nbsp;|&nbsp;
        <a title="<?php echo $this->encodingCharset($this->get_lang("Print")); ?>" href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=ReportPrint&func=sessions&currentTab='.$this->currentTab.'&searchText='.urlencode($this->txtSearchDefault); ?>" id="session_print" class="reporting-print">
            <img src="<?php echo api_get_path(WEB_IMG_PATH).'print_22.png'; ?>" style="vertical-align: middle;width: 22px;height: 22px;" />
            <?php echo $this->encodingCharset($this->get_lang("Print")); ?>
        </a>
    </span>
<?php endif; ?>
<div class="clearfix"></div>
<script>
if ($(".action_course_detail").length) {
    $(".action_course_detail").click(function(e){        
        ReportingModel.displaySessionCourses(e, $(this));
    });
}
if ($(".reporting-print").length) {
    $(".reporting-print").click(function(e) {            
        ReportingModel.printPage(e, $(this));
    });
}
if ($(".cut-tooltip").length > 0) {
    ReportingModel.cutTooltip();
}
</script>
<div id="closeText" style="display: none;" class="<?php echo get_lang('Close') ?>"></div>