<div id="dataDiv">    
    <p><a class='pull-right action_learner' id='' href='<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=ReportAjax&func=displayLearnerDetail&learnerId='.$this->learnerInfo['user_id']; ?>'><?php echo $this->encodingCharset($this->get_lang("Back")); ?></a></p>        
    <p class="individual-user-fullname"><?php echo $this->encodingCharset($this->learnerInfo['firstname'].' '.strtoupper($this->learnerInfo['lastname'])); ?> : <?php echo $this->encodingCharset($this->get_lang('AccessDetails')); ?></p>
   
    <div id="container-9">
        <ul>
            <li><a href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=Report&func=displayLearnerAccessGraph&learnerId='.$this->learnerInfo['user_id'].'&type=day'; ?>"><span><?php echo api_ucfirst($this->get_lang('Day')); ?></span></a></li>
            <li><a href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=Report&func=displayLearnerAccessGraph&learnerId='.$this->learnerInfo['user_id'].'&type=month'; ?>"><span><?php echo api_ucfirst($this->get_lang('MinMonth')); ?></span></a></li>
        </ul>
        <div id="show"></div>
    </div>        
    <div class="actions"><strong><?php echo $this->encodingCharset(stripslashes($this->get_lang('DateAndTimeOfAccess'))), ' - ', $this->encodingCharset($this->get_lang('Duration')); ?></strong></div>
    <div class="scroll-pane">
        <?php if (!empty($this->learnerAccessData)): ?>
            <ol>
                <?php foreach($this->learnerAccessData as $data): ?>
                    <li><?php echo date('d-m-Y (H:i:s)', $data['login']).' - '.api_time_to_hms($data['logout'] - $data['login']); ?></li>
                <?php endforeach; ?>
            </ol>
        <?php else: ?>
                <?php echo $this->encodingCharset($this->get_lang('UserHasNoConnectionStill')); ?>
        <?php endif; ?>        
    </div>  
    
    <p class="pull-right">
        <a title="<?php echo $this->encodingCharset($this->get_lang("Print")); ?>" href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=ReportPrint&func=learnerAccessDetails&learnerId='.$this->learnerInfo['user_id']; ?>" class="reporting-print">
            <img src="<?php echo api_get_path(WEB_IMG_PATH).'print_22.png'; ?>" style="vertical-align: middle;width: 22px;height: 22px;" />
            <?php echo $this->encodingCharset($this->get_lang("Print")); ?>
        </a>
    </p>     
</div>   
<input type="hidden" name="currentTab" id="current-tab" value="<?php echo $this->currentTab; ?>" />
<script type="text/javascript" src="<?php echo api_get_path(WEB_LIBRARY_PATH); ?>javascript/jquery-ui/js/jquery-ui-1.8.1.custom.min.js"></script>
<script>
    if ($(".action_learner").length) {
        $(".action_learner").click(function(e){
            ReportingModel.displayLearnerDetail(e, $(this));
        });
    }
    if ($("#container-9").length) {
        $("#container-9").tabs();
    }
    if ($(".reporting-print").length) {
        $(".reporting-print").click(function(e){            
            ReportingModel.printPage(e, $(this));
        });
    }
</script>
<div id="closeText" style="display: none;" class="<?php echo get_lang('Close') ?>"></div>