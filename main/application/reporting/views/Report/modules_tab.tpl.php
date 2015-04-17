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
    <h4><?php echo $this->get_lang('ModuleAverageValues'); ?>
    <?php 
        if (!empty($this->txtSearchDefault)) {
            echo ' - '.$this->get_lang('SearchByModuleName').' : '.$this->encodingCharset($this->txtSearchDefault);
        }
    ?>
    </h4>
</span>
<?php endif; ?>

<div class="data-container">
    <?php if (!$this->isEmpty || $this->printPage == 'print'): ?>
        <table id="courses" class="responsive large-only table-striped">
            <thead>
                <tr>
                    <th><?php echo $this->encodingCharset($this->get_lang('Modules')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('ReportInCourse')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('ScormTime')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('Progress')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('ReportScore')); ?></th>
                    <?php if ($this->printPage != 'print'): ?>
                    <th class="print_invisible"><?php echo $this->encodingCharset($this->get_lang('Detail')); ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>                            
                <?php                            
                    if (!empty($this->modulesData)):
                        foreach ($this->modulesData as $data):
                ?>
                        <tr>
                            <td width="25%"><?php echo cut($this->encodingCharset($data['name']), 40, true); ?></td>
                            <td width="20%" align="center"><?php echo cut($this->encodingCharset($data['incourse']), 30, true); ?></td>
                            <td align="center"><?php echo $data['time']; ?></td>
                            <td align="center"><?php echo $data['progress']; ?></td>
                            <td align="center"><?php echo $data['score']; ?></td>
                            <?php if ($this->printPage != 'print'): ?>
                            <td align="center">
                                <?php  if ($data['show_detail']): ?>
                                <a class='action_module' id='<?php echo $data['module_id'].'-'.$data['course_code']; ?>' href='<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=ReportAjax&func=displayModuleUsers&lpId='.$data['module_id'].'&courseCode='.$data['course_code']; ?>'>
                                    <img src='<?php echo api_get_path(WEB_CSS_PATH).$this->stylesheet; ?>/images/action/list_learners.png'>
                                </a>
                                <?php else: ?>
                                    <img class="cut-tooltip" title="<?php echo $this->encodingCharset($this->get_lang('NoResults')); ?>" src='<?php echo api_get_path(WEB_CSS_PATH).$this->stylesheet; ?>/images/action/list_learners.png' />
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>
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
    <?php else: ?>    
        <div class="warning-message"><?php echo $this->encodingCharset($this->get_lang('UseFiltersToSelectReporting')); ?></div>    
    <?php endif; ?>
</div>
<input type="hidden" name="hid_action_code" id="hid_action_code" />
<?php if ($this->printPage != 'print' && !$this->isEmpty): ?>
<span class="pull-right">
    <a href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=Report&func=export&type=modules'; ?>" id="module_export" >
        <img src="<?php echo api_get_path(WEB_IMG_PATH).'csv.png'; ?>" style="vertical-align: middle;width: 22px;height: 22px;" />
        <?php echo $this->encodingCharset($this->get_lang("Export")); ?>
    </a>&nbsp;|&nbsp;
    <a title="<?php echo $this->encodingCharset($this->get_lang("Print")); ?>" href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=ReportPrint&func=modules&currentTab='.$this->currentTab.'&searchText='.urlencode($this->txtSearchDefault); ?>" id="course_print" class="reporting-print">
        <img src="<?php echo api_get_path(WEB_IMG_PATH).'print_22.png'; ?>" style="vertical-align: middle;width: 22px;height: 22px;" />
        <?php echo $this->encodingCharset($this->get_lang("Print")); ?>
    </a>
</span>
<?php endif; ?>
<div class="clearfix"></div>
<script>
    if ($(".action_module").length) {
        $(".action_module").click(function(e){
            ReportingModel.displayModuleUsers(e, $(this));
        });
    }
    if ($(".reporting-print").length) {
        $(".reporting-print").click(function(e){            
            ReportingModel.printPage(e, $(this));
        });
    }    
    if ($(".cut-tooltip").length > 0) {
        ReportingModel.cutTooltip();
    }
</script>
<div id="closeText" style="display: none;" class="<?php echo get_lang('Close') ?>"></div>