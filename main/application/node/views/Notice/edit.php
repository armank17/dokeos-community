<form id="notice-form" name="nodeForm" method="post" action="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=Notice&func='.(!empty($this->nodeId)?'update':'create'); ?>">
    <?php 
    if (!empty($this->nodeId)): ?>
        <input type="hidden" name="nodeId" value="<?php echo $this->nodeId; ?>" />
    <?php endif; ?>
<div class="row">
        <button type="submit" name="submitDocument" class="upload"><?php echo $this->get_lang('Save'); ?></button>
        <div class="formw">
            <input type="text" name="node_title" class="focus required" value="<?php echo $this->pageInfo['title']; ?>" style="width:550px;" placeholder="<?php echo $this->get_lang('AddHereNoticeTitle'); ?>" />                       
            <div id="empty_fields_message"><?php echo $this->get_lang('LetThoseFieldsEmptyToHideTheNotice'); ?></div>
            <input type='hidden' name='active' value='1'>
            <input type='hidden' name='enabled' value='1'>
            <input type="hidden" name="language" value="<?php echo $this->pageInfo['Language'];  ?>">
        </div>
    
</div> 

<div class="row">        
        <?php api_disp_html_area('node_editor', $this->pageInfo['content'], '', '', null, $this->editorConfig); ?>
</div>
<button type="submit" name="submitDocument" class="upload"><?php echo $this->get_lang('Save'); ?></button>
</form>