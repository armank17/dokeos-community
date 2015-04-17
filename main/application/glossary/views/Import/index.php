<script type="text/javascript" src="<?php echo api_get_path(WEB_PATH);?>main/appcore/library/jquery/jquery.validate.js"></script>
<link rel="stylesheet" href="/main/application/glossary/assets/css/css-export.css" />
<div id="wrapper_glossary_content">
    <div id="id-post" class="<?php echo $this->css?>">
        <?php 
            if($this->action)
                echo $this->text;
            ?> 
    </div>
    <div id="divMessage"></div>
    <a  href="'.  api_get_path(WEB_CODE_PATH).'upload/tool_templates/glossary/GlossaryTemplate.xls">
        <h3 class="orange" style="margin-left:10px;">
            <?php echo  $this->get_lang('DownloadGlossaryTemplate') ?> 
        </h3>
    </a>
    <div class="fila">
        <div>
            <?php    
                echo $this->form->display();
            ?>
        </div>
        <div class="glossary-instructor-import">
        </div>
    </div>

</div>
<script>
$(document).ready(function(){
    //
    $("#id-post").show(1000);
    $("#id-post").delay(3000).hide(2000);
    //
    $("#divMessage").hide();
    $("#glossary-import").validate({
        debug: false,
        rules: {
            file_import: {
                required: true,
                accept: "xls|xlsx|csv"
                } 
        },
        messages: {
            file_import: {
                required: "<img src=\"<?php echo api_get_path(WEB_IMG_PATH)?>exclamation.png\" title=\'<?php echo $this->get_lang('Required')?>\' />"
            } 
        }
    });
    
});
</script>