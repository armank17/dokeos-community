<div id="lp-layout">
    <div class="section">
        <a href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=Authoring&func=index&'.api_get_cidreq().'&itemType=document&tplId=0&ressource=document&lpId='.$this->lpId.$this->extraParams; ?>" class="embed-tpl">
            <div class="sectioncontent_template">
                <img border="0" src="<?php echo api_get_path(WEB_PATH).'home/default_platform_document/template_thumb/empty.gif'; ?>" />
            </div>
        </a>
    </div>
    <?php 
        if (!empty($this->templates)): 
            foreach ($this->templates as $template):
    ?>
        <div class="section">
            <a href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=Authoring&func=index&'.api_get_cidreq().'&itemType=document&tplId='.$template['id'].'&ressource=document&lpId='.$this->lpId.$this->extraParams; ?>" class="embed-tpl">
                <div class="sectioncontent_template">
                    <img border="0" src="<?php echo api_get_path(WEB_PATH).'home/default_platform_document/template_thumb/'.$template['image']; ?>" />
                </div>
            </a>
        </div>
    <?php 
            endforeach;
        endif; 
    ?>
</div>
<input type="hidden" id="courseCode" value="<?php echo urlencode($this->courseCode); ?>" />
<input type="hidden" id="webPath" value="<?php echo urlencode(api_get_path(WEB_PATH)); ?>" />