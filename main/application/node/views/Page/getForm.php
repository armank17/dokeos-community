<form id="node-form" name="nodeForm" method="post" action="<?php echo api_get_path(WEB_CODE_PATH).'index.php?'.api_get_cidReq().'&module=node&cmd=Page&func='.(!empty($this->nodeId)?'update':'create'); ?>">
    <?php if (!empty($this->nodeId)): ?>
        <input type="hidden" name="nodeId" value="<?php echo $this->nodeId; ?>" />
    <?php endif; ?>
    <div class="row">
        <div class="form_header">
            <?php echo !empty($this->nodeId)?$this->get_lang('EditPage'):$this->get_lang('NewPage'); ?>           
        </div>        
    </div>    
    <div class="row">
        <button type="submit" name="submitDocument" class="upload"><?php echo $this->get_lang('Save'); ?></button>
        <div class="formw">
            <input type="text" name="node_title" class="focus required" value="<?php echo $this->pageInfo['title']; ?>" style="width:550px;" placeholder="<?php echo $this->get_lang('AddHerePageTitle'); ?>" />
            <input type="checkbox" name="show_header" value= 1 <?php echo ($this->pageInfo['show_header']==1)? 'checked' : ''; ?>><label for="show_header"><?php echo get_lang('showHeader')?></label>
        </div>
        <input type="hidden" name="created_by" value="<?php echo api_get_user_id();?>">
        <input type="hidden" name="target" value="course">
        <input type="hidden" name="course_code" value="<?php echo api_get_course_id(); ?>">
        
               
    </div>
    
    <div class="row">        
        <?php api_disp_html_area('node_editor', $this->pageInfo['content'], '', '', null, $this->editorConfig); ?>
    </div>

    <div class="clear"></div>
</form>