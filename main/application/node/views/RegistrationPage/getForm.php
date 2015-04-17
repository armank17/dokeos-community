<form id="registrationPage-form" name="registrationPageForm" method="post" action="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=RegistrationPage&func='.(!empty($this->nodeId)?'update':'create'); ?>">
    <?php 
    if (!empty($this->nodeId)): ?>
        <input type="hidden" name="nodeId" value="<?php echo $this->nodeId; ?>" />
    <?php endif; ?>
<div class="row">
        <button type="submit" name="submitDocument" class="upload"><?php echo $this->get_lang('Save'); ?></button>
        <div class="formw">
            <input type="hidden" name="language" value="<?php echo $this->getRequest()->getProperty('language', '');  ?>">
        </div>
</div> 

<div class="row">        
        <?php api_disp_html_area('node_editor', $this->pageInfo['content'], '', '', null, $this->editorConfig); ?>
</div>
<button type="submit" name="submitDocument" class="upload"><?php echo $this->get_lang('Save'); ?></button>
</form>