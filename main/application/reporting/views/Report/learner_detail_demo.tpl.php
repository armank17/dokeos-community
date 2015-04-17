<div id="dataDiv">
<p><a class="pull-right" id="learners_back" href="#"><?php echo $this->encodingCharset($this->get_lang("BackToList")); ?></a></p>        
<img src='<?php echo api_get_path(WEB_IMG_PATH); ?>/individual.png' />
</div>
<script>
    if ($("#learners_back").length) {
        $("#learners_back").click(function(e){
           e.preventDefault(); 
           ReportingModel.displayTabContent($("#tablist1-panel6"), 'displayLearnersTab');
        });
    }    
</script>