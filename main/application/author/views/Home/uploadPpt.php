
<div id="content-upload">
    <div id="col1" class="cols">
        <h3 class="orange"><a href="DokeosScenarioEn.ppt"><?php echo $this->get_lang('DownloadPowerpointTemplate'); ?></a></h3>    
        <p><img class="toolscenarioactionplaceholdericon ppt_button" src="<?php echo api_get_path(WEB_IMG_PATH).'pixel.gif'; ?>" /></p>
        <p><img src="<?php echo api_get_path(WEB_IMG_PATH).'navigation/presentation_teacher.png'; ?>" height="310px" /></p>
    </div>    
    <div id="col2" class="cols">
        <form enctype="multipart/form-data" id="upload_ppt" class="form-upload" name="upload_ppt" method="POST" action="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=HomeAjax&func=uploadOogie&'.api_get_cidreq(); ?>">
            <h3 class="title"><?php echo $this->get_lang('UploadFile'); ?></h3>
            <input type="file" name="user_file" class="input_browse">
            <p><?php echo $this->get_lang('UploadMaxSize') . ' : ' . ini_get('post_max_size'); ?></p>
            
            <?php if ($this->searchEnabled): ?>
            <div id="more_criteria">
                <p><?php echo $this->get_lang('SearchKeywords'); ?>: <input type="text" name="terms" class="tag-it" style="display: none;" /></p>                
            </div>
            <?php endif; ?>
            
            <button type="submit" name="convert" class="save"><?php echo $this->get_lang('ConvertToLP'); ?></button>
            <input type="hidden" value="<?php echo ini_get('post_max_size'); ?>" name="MAX_FILE_SIZE" />
            <input type="hidden" value="1" name="index_document" />
            <input type="hidden" value="english" name="language" />
            <input type="hidden" value="true" name="ppt2lp" />
            <div class="clear">&nbsp;</div>
        </form>
        <br />
        <div class="progress">
            <div class="bar"></div>
            <div class="percent">0%</div>
        </div>
        <div id="status"></div>
    </div>   
</div>
