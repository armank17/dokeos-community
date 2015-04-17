<div style=" margin:auto !important; width:450px; overflow:hidden">
<form accept-charset="utf-8" action="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=AuthoringAjax&func=updateLp&'.api_get_cidreq(); ?>" method="post" name="form1"  id="lp-settings2" style="height: 133px; margin:0px ; margin-top:15px; overflow:hidden; position:relative;">
    <input type="hidden" name="lp_interface" value="0" />
    <div class="row" id="first-item-form" style="margin:0px; margin-left:10px;">
        <div style="width: auto; text-align: center; margin-left:20px;" class="label"><?php echo get_lang("NameOfYourNewModule").":"; ?></div>
        <!--<div style="width: auto;" class="formw">-->
            <input style="margin-left: 35px;    margin-top: 0;" size="43" name="lp_name" id="lp_name" type="text" value="<?php echo $this->lpInfo['name']; ?>" />
        <!--</div>-->
    </div>
    <div class="" style="  margin-left: 60px;    margin-top: 20px;">
        <div class="" style="width:100%"></div>
        <!--<div clasx`="formw" style="margin-left: 131px; border:1px solid red; width:100% !important;">-->	
			
			<span style="display:block; "><?php echo get_lang('DoYouWantToEditAnExistingModule') ?></span>
			<span style="  display:block; float: left"><a target="_parent" style="color: green;" id="go-to" href="<?php echo api_get_path(WEB_CODE_PATH).'newscorm/lp_controller.php?action=course&cidReq='.api_get_course_id(); ?>"><b><?php echo get_lang('ClickHere');?></b></a></span>
                        <div style="position: absolute;    right: 0;     top: 77px;">
                        <button style="float:right; width: 93px; float: right; right:-6px" class="save" name="Submit" type="submit" ><?php echo $this->get_lang('Submit'); ?></button>
                        </div>
        <!--</div>-->
    </div>
    <div class="">
        <div class="" style=""></div>
        <div class="formw"><span class="form_required"></span></div>
    </div>
    <input name="lpId" type="hidden" value="<?php echo $this->lpId; ?>" />
    <input type="hidden" id="courseCode" value="<?php echo urlencode($this->courseCode); ?>" />
    <input type="hidden" id="webPath" value="<?php echo urlencode(api_get_path(WEB_PATH)); ?>" />
    <input type="hidden" id="webPathNotEncoded" value="<?php echo api_get_path(WEB_PATH); ?>" />
    <div class="clear"></div>
</form>
</div>
<div id="status"></div>
<?php echo $this->setTemplate('dlg_export_scorm', 'Authoring'); ?>
<script>$(function(){$("#lp_name").focus();});</script>
