<form accept-charset="utf-8" action="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=AuthoringAjax&func=updateLp&'.api_get_cidreq(); ?>" method="post" name="form1" id="lp-settings">
    <input type="hidden" name="lp_interface" value="0" />
    <div class="row" id="first-item-form">
        <div class="label"><?php echo $this->get_lang('_title'); ?></div>
        <div class="formw">
            <input size="43" name="lp_name" type="text" value="<?php echo $this->lpInfo['name']; ?>" />
        </div>
        <?php if (!$this->isScorm): ?>
            <div id="export-scorm-wrap">
                <a href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=Authoring&func=exportScorm&lpId='.$this->lpId;?>" id="export-link" title="<?php echo $this->get_lang('ScormExportType'); ?>"><img src="<?php echo api_get_path(WEB_IMG_PATH).'dropbox.png'; ?>" /> <?php echo $this->get_lang('ExportToScorm'); ?></a>
            </div>
        <?php endif; ?>
    </div>
    <div class="row">
        <div class="label"><?php echo $this->get_lang('Charset'); ?></div>
        <div class="formw">	
            <select name="lp_encoding">
            <?php foreach ($this->encodings as $encoding): ?>
                <option value="<?php echo $encoding; ?>" <?php echo $encoding == $this->lpInfo['default_encoding']?' selected="selected"':''; ?>><?php echo $encoding; ?></option>                
            <?php endforeach; ?>
            </select>
        </div>
    </div>
    <?php if (!$this->isScorm): ?>
        <div class="row">
            <div class="label"><?php echo $this->get_lang('Origin'); ?></div>
            <div class="formw">	
                <select name="lp_maker">
                    <?php foreach ($this->makers as $maker): ?>
                        <option value="<?php echo $maker; ?>" <?php echo $maker == $this->lpInfo['content_maker']?' selected="selected"':''; ?>><?php echo $maker; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>    
        <div class="row">
            <div class="label"><?php echo $this->get_lang('ContentProximity'); ?></div>
            <div class="formw">	
                <select name="lp_proximity">
                    <?php foreach ($this->proximities as $variable => $value): ?>
                        <option value="<?php echo $variable; ?>" <?php echo $variable == $this->lpInfo['content_local']?' selected="selected"':''; ?>><?php echo $value; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>    
        <div class="row">
            <div class="label"><?php echo $this->get_lang('Theme'); ?></div>
            <div class="formw">	
                <select name="lp_theme">
                    <option value="" selected="selected">--</option>
                    <?php for ($i=0; $i<count($this->themes[0]); $i++): ?>
                        <option value="<?php echo $this->themes[0][$i]; ?>" <?php echo $this->themes[0][$i] == $this->lpInfo['theme']?' selected="selected"':''; ?>><?php echo $this->themes[1][$i]; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="label"><?php echo $this->get_lang('Author'); ?></div>
            <div class="formw">	
                <input name="lp_author" type="text" value="<?php echo $this->lpInfo['author']; ?>" />
            </div>
        </div>
    <?php  endif; ?>    
    <div class="row">
        <div class="label"><?php echo $this->get_lang('ShowDebug'); ?></div>
        <div class="formw">	
            <input style="margin-top: 10px;" name="enable_debug" type="checkbox" value="1" id="qf_603f12" <?php echo $this->lpInfo['debug'] == 1?' checked="checked"':''; ?> />
        </div>
    </div>
    
    <div class="row">
        <div class="label"><?php echo $this->get_lang('Start'); ?></div>
        <div class="formw" style="margin-top: 5px;">	
            <div style="display: inline; float:left">
				<input style="margin:0;" name="enable_behavior_holder" value="1" type="radio" id="enable_behavior_holder">&nbsp;<span><?php echo get_lang('FromTheBeginning'); ?></span></input>
            </div>
            <div style="display: inline; float:left">
				<input style="margin:0;" name="enable_behavior_holder" value="2" type="radio" id="enable_behavior_holder">&nbsp;<span><?php echo get_lang('FromWhereILeft'); ?></span></input>
			</div>
			<input name="enable_behavior" id="enable_behavior" type="hidden" value="<?php echo $this->lpInfo['behavior']; ?>"/>
        </div>
    </div>
    
    <div class="row">
        <div class="label"></div>
        <div class="formw">	
            <button class="save" name="Submit" type="submit" ><?php echo $this->get_lang('Submit'); ?></button>
        </div>
    </div>
    <div class="row">
        <div class="label"></div>
        <div class="formw"><span class="form_required"></span></div>
    </div>
    <input name="lpId" type="hidden" value="<?php echo $this->lpId; ?>" />
    <input type="hidden" id="courseCode" value="<?php echo urlencode($this->courseCode); ?>" />
    <input type="hidden" id="webPath" value="<?php echo urlencode(api_get_path(WEB_PATH)); ?>" />
    <div class="clear"></div>
</form>
<div id="status"></div>
<?php echo $this->setTemplate('dlg_export_scorm', 'Authoring'); ?>
