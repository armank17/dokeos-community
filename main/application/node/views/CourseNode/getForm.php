<form id="node-form" name="nodeForm" method="post" action="<?php echo api_get_path(WEB_CODE_PATH).'index.php?'.api_get_cidReq().'&module=node&cmd=CourseNode&func='.(!empty($this->nodeId)?'update':'create'); ?>">
    <?php if (!empty($this->nodeId)): ?>
        <input type="hidden" name="nodeId" value="<?php echo $this->nodeId; ?>" />
    <?php endif; ?>
    <div class="row">
        <div class="form_header">
            <?php echo !empty($this->nodeId)?$this->get_lang('EditPage'):$this->get_lang('NewPage'); ?>           
        </div>        
    </div>    
    <div class="row">
        <button style="margin-top:-10px !important;" type="submit" name="submitDocument" class="upload"><?php echo $this->get_lang('Save'); ?></button> 
        <div class="formw">
            <input type="text" name="node_title" id="node_title" class="focus required" value="<?php echo $this->nodeInfo['title']; ?>" style="width:550px;" placeholder="<?php echo $this->get_lang('AddHerePageTitle'); ?>" />
            <div id="enabledPage" style="width: auto;margin-top:-4px;"><input type="checkbox" id="enabled" name="enabled" value= 1 <?php echo ($this->nodeInfo['enabled']==1)? 'checked' : ''; ?>><label for="enabled" style="margin-top:1px;"><?php echo get_lang('Enabled')?></label></div>
        </div>
        <input type="hidden" name="course_code" value="<?php echo api_get_course_id(); ?>">
        <input type="hidden" name="active" value="1">
    </div>
    
    <div class="row" style="margin-top:65px !important;">        
        <?php api_disp_html_area('node_editor', $this->nodeInfo['content'], '', '', null, $this->editorConfig); ?>
    </div>

    <div class="clear"></div>
</form>