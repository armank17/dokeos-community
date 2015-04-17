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
    <p><a class="pull-right" id="modules_back" href="#"><?php echo $this->encodingCharset($this->get_lang("BackToModule")); ?></a></p>
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
    <h4><?php echo $this->encodingCharset($this->get_lang("Module")); ?> : <?php echo $this->encodingCharset($this->moduleInfo['name']); ?></h4>    
    <div class="data-container">
        <table id="list_module_learners" class="responsive large-only table-striped">
            <thead>
                <tr>
                    <th><?php echo $this->encodingCharset($this->get_lang("LastName")); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang("FirstName")); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang("ScormTime")); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang("Progress")); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang("ReportScore")); ?></th>
                    <th class="print_invisible"><?php echo $this->encodingCharset($this->get_lang("Detail")); ?></th>						
                </tr>
            </thead>
            <tbody>
                <?php                            
                    if (!empty($this->moduleUsersData)):
                        foreach ($this->moduleUsersData as $userId => $data):
                ?>
                        <tr>
                            <td><?php echo $this->encodingCharset($data['lastname']); ?></td>
                            <td><?php echo $this->encodingCharset($data['firstname']); ?></td>
                            <td align="center"><?php echo $data['time']; ?></td>
                            <td align="center"><?php echo $data['progress']; ?></td>
                            <td align="center"><?php echo $data['score']; ?></td>
                            <td align="center">
                                <?php if (!empty($data['lp_views'])):?>
                                    <a class='action_module_users' id='' href='<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=ReportAjax&func=displayModuleUserDetail&lpId='.$data['module_id'].'&courseCode='.$data['course_code'].'&learnerId='.$userId.'&currentTab='.$this->currentTab; ?>'>
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
        <a href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=Report&func=export&type=module_users&lpId='.$this->selectedModuleId.'&courseCode='.$this->selectedModuleCourse; ?>" id="export_module_learners_list">
            <img src="<?php echo api_get_path(WEB_IMG_PATH).'csv.png'; ?>" style="vertical-align: middle;width: 22px;height: 22px;" />
            <?php echo $this->encodingCharset($this->get_lang("Export")); ?>
        </a>&nbsp;|&nbsp;
        <a title="<?php echo $this->encodingCharset($this->get_lang("Print")); ?>" href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=ReportPrint&func=moduleUsers&currentTab='.$this->currentTab.'&searchText='.urlencode($this->txtSearchDefault).'&lpId='.$this->selectedModuleId.'&courseCode='.$this->selectedModuleCourse; ?>" id="course_print" class="reporting-print">
            <img src="<?php echo api_get_path(WEB_IMG_PATH).'print_22.png'; ?>" style="vertical-align: middle;width: 22px;height: 22px;" />
            <?php echo $this->encodingCharset($this->get_lang("Print")); ?>
        </a>
    </p>
</div>
<input type="hidden" name="currentTab" id="current-tab" value="<?php echo $this->currentTab; ?>" />
<script>
    if ($("#modules_back").length) {
        $("#modules_back").click(function(e) {
           e.preventDefault(); 
           ReportingModel.displayTabContent($("#tablist1-panel3"), 'displayModulesTab');
        });
    }
    if ($(".paginate").length) {
        $(".paginate").click(function(e){
            ReportingModel.paginateModuleUsers(e, $(this));
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
    if ($(".cut-tooltip").length > 0) {
        ReportingModel.cutTooltip();
    }
</script>
<div id="closeText" style="display: none;" class="<?php echo get_lang('Close') ?>"></div>