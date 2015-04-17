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
    <h4><?php echo $this->encodingCharset($this->get_lang('FaceToFaceAverageValues')); ?>
    <?php 
        if (!empty($this->txtSearchDefault)) {
            echo ' - '.$this->get_lang('SearchByTitle').' : '.$this->encodingCharset($this->txtSearchDefault);
        }
    ?>
    </h4>
</span>
<?php endif; ?>
<div class="data-container">
    <?php if (!$this->isEmpty || $this->printPage == 'print'): ?>
        
        <table id="face2faces" class="responsive large-only table-striped">
            <thead>
                <tr>
                    <th><?php echo $this->encodingCharset($this->get_lang('ReportInCourse')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('SessionName')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('ReportActivityName')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('Type')); ?></th>
                    <th><?php echo $this->encodingCharset(ucfirst($this->get_lang('Participants'))); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('Status')); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang('ReportScore')); ?></th>
                    <?php if ($this->printPage != 'print'): ?>
                    <th class="print_invisible"><?php echo $this->encodingCharset($this->get_lang('Detail')); ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>                            
                <?php                            
                    if (!empty($this->face2faceData)):
                        foreach ($this->face2faceData as $data):
                ?>
                        <tr>
                            <td width="22%"><?php echo cut($this->encodingCharset($data['incourse']), 30, true); ?></td>
                            <td width="22%" align="center"><?php echo cut($this->encodingCharset($data['session']), 30, true); ?></td>
                            <td width="22%"><?php echo cut($this->encodingCharset($data['name']), 45, true); ?></td>
                            <td align="center"><?php echo $this->encodingCharset($this->get_lang($data['type'])); ?></td>
                            <td align="center"><?php echo $data['nb_learners']; ?></td>
                            <td align="center"><?php echo $data['pass']; ?></td>
                            <td align="center"><?php echo $data['score']; ?></td>
                            <?php if ($this->printPage != 'print'): ?>
                            <td align="center">
                                <?php  if ($data['nb_learners'] > 0): ?>
                                    <a class='action_f2f' id='<?php echo $data['face2face_id'].'-'.$data['course_code']; ?>' href='<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=ReportAjax&func=displayFace2FaceUsers&face2faceId='.$data['face2face_id'].'&type='.$data['ff_type'].'&courseCode='.$data['course_code']; ?>'>
                                        <img src='<?php echo api_get_path(WEB_CSS_PATH).$this->stylesheet; ?>/images/action/list_learners.png'>
                                    </a>
                                <?php else: ?>
                                    <img title="<?php echo $this->encodingCharset($this->get_lang('NoResults')); ?>" src='<?php echo api_get_path(WEB_CSS_PATH).$this->stylesheet; ?>/images/action/list_learners.png' />
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
<?php if ($this->printPage != 'print' && !$this->isEmpty): ?>
    <span class="pull-right">
        <a href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=Report&func=export&type=facetoface'; ?>">
            <img src="<?php echo api_get_path(WEB_IMG_PATH).'csv.png'; ?>" style="vertical-align: middle;width: 22px;height: 22px;" />
            <?php echo $this->encodingCharset($this->get_lang("Export")); ?>
        </a>&nbsp;|&nbsp;
        <a title="<?php echo $this->encodingCharset($this->get_lang("Print")); ?>" href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=ReportPrint&func=facetoface&searchText='.urlencode($this->txtSearchDefault); ?>" class="reporting-print">
            <img src="<?php echo api_get_path(WEB_IMG_PATH).'print_22.png'; ?>" style="vertical-align: middle;width: 22px;height: 22px;" />
            <?php echo $this->encodingCharset($this->get_lang("Print")); ?>
        </a>
    </span>
<?php endif; ?>
<div class="clearfix"></div>
<script>
    if ($(".action_f2f").length) {
        $(".action_f2f").click(function(e){
            ReportingModel.displayFace2faceUsers(e, $(this));
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