<div id="dataDiv">           
    <p class="individual-user-fullname"><?php echo $this->encodingCharset($this->learnerInfo['firstname'].' '.strtoupper($this->learnerInfo['lastname'])); ?> : <?php echo $this->encodingCharset($this->get_lang('AccessDetails')); ?></p>   
    
    <div id="container-9">  
        <h4><?php echo api_ucfirst($this->get_lang('Day')); ?></h4>
        <div id="graph-day"></div>
        <h4><?php echo api_ucfirst($this->get_lang('MinMonth')); ?></h4>
        <div id="graph-month"></div>
    </div>
    
    <div class="actions"><strong><?php echo $this->encodingCharset(stripslashes($this->get_lang('DateAndTimeOfAccess'))), ' - ', $this->encodingCharset($this->get_lang('Duration')); ?></strong></div>
    <div>
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
</div>   
<script>
    $(document).ready(function(e) {
        $("#graph-day").load("<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=Report&func=displayLearnerAccessGraph&learnerId='.$this->learnerInfo['user_id'].'&type=day'; ?>");
        $("#graph-month").load("<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=reporting&cmd=Report&func=displayLearnerAccessGraph&learnerId='.$this->learnerInfo['user_id'].'&type=month'; ?>");
    });
</script>
<script>
    window.onload=function() {
        window.print();
    };
</script>