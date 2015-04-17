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
    <p><a class="pull-right" id="face2face_back" href="#"><?php echo $this->encodingCharset($this->get_lang("BackToFace2Face")); ?></a></p>
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
    <h4><?php echo $this->encodingCharset($this->get_lang("Activity")); ?> : <?php echo $this->encodingCharset($this->face2faceInfo['name']); ?></h4>    
    <div class="data-container">
        <table id="list_face2face_learners" class="responsive large-only table-striped">
            <thead>
                <tr>
                    <th><?php echo $this->encodingCharset($this->get_lang("LastName")); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang("FirstName")); ?></th>
                    <th><?php echo $this->encodingCharset($this->get_lang("Status")); ?></th>
                    <?php if ($this->face2faceInfo['ff_type'] == 2): ?>
                        <th><?php echo $this->encodingCharset($this->get_lang("ReportScore")); ?></th>
                    <?php else: ?>
                        <th width="35%"><?php echo $this->encodingCharset($this->get_lang("Comments")); ?></th>
                    <?php endif; ?>                    						
                </tr>
            </thead>
            <tbody>
                <?php                            
                    if (!empty($this->face2faceUsersData)):
                        foreach ($this->face2faceUsersData as $userId => $data):
                ?>
                        <tr>
                            <td><?php echo $this->encodingCharset($data['lastname']); ?></td>
                            <td><?php echo $this->encodingCharset($data['firstname']); ?></td>
                            <td align="center">                                
                                <?php if (isset($data['passed'])): ?>                                
                                    <?php if ($data['passed'] === true): ?>
                                        <img src="<?php echo api_get_path(WEB_CODE_PATH).'application/reporting/assets/img/checkbox_selected.gif'; ?>" />
                                    <?php else: ?>
                                        <img src="<?php echo api_get_path(WEB_CODE_PATH).'application/reporting/assets/img/checkbox_unchecked.gif'; ?>" />
                                    <?php endif; ?>                                
                                <?php else: ?>                                   
                                    <img src="<?php echo api_get_path(WEB_CODE_PATH).'application/reporting/assets/img/checkbox_normal.gif'; ?>" />                                       
                                <?php endif; ?>                                                                
                            </td>
                            <td align="center">
                                <?php if ($this->face2faceInfo['ff_type'] == 2): ?>
                                    <?php echo $data['score']; ?>
                                <?php else: ?>
                                    <?php echo cut($this->encodingCharset($data['comment']), 45, true); ?>
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
        <a href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=Report&func=export&type=facetoface_users&f2fType='.$this->face2faceInfo['ff_type'].'&face2faceId='.$this->selectedFace2FaceId.'&courseCode='.$this->selectedFace2FaceCourse; ?>" id="export_face2face_learners_list">
            <img src="<?php echo api_get_path(WEB_IMG_PATH).'csv.png'; ?>" style="vertical-align: middle;width: 22px;height: 22px;" />
            <?php echo $this->encodingCharset($this->get_lang("Export")); ?>
        </a>&nbsp;|&nbsp;
        <a title="<?php echo $this->encodingCharset($this->get_lang("Print")); ?>" href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=ReportPrint&func=facetofaceUsers&searchText='.urlencode($this->txtSearchDefault).'&face2faceId='.$this->selectedFace2FaceId.'&courseCode='.$this->selectedFace2FaceCourse.'&type='.$this->face2faceInfo['ff_type']; ?>" class="reporting-print">
            <img src="<?php echo api_get_path(WEB_IMG_PATH).'print_22.png'; ?>" style="vertical-align: middle;width: 22px;height: 22px;" />
            <?php echo $this->encodingCharset($this->get_lang("Print")); ?>
        </a>
    </p>
</div>
<input type="hidden" name="currentTab" id="current-tab" value="<?php echo $this->currentTab; ?>" />
<script>
    if ($("#face2face_back").length) {
        $("#face2face_back").click(function(e) {
           e.preventDefault(); 
           ReportingModel.displayTabContent($("#tablist1-panel5"), 'displayFace2FaceTab');
        });
    }
    if ($(".paginate").length) {
        $(".paginate").click(function(e){
            ReportingModel.paginateFace2FaceUsers(e, $(this));
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