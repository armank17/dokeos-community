<div id="export-dialog" style="display:none;">
    <form id="export-form">
        <select name="scorm_export" id="scorm_export">
            <option selected="selected" value="default_export"><?php echo $this->get_lang('DefaultScormExport'); ?></option>
            <option value="layout_export"><?php echo $this->get_lang('ScormExportWithNavigationButtons'); ?></option>
        </select> 
        <input type="hidden" value ="default_export" name="current_export_type" id="current_export_type">
        <div id="select_navigation" style="display:none;">
            <p>
                <input type="radio" class="export-nav" name="export_nav" value="top" /><?php echo $this->get_lang('TopNavigation'); ?>&nbsp;&nbsp;
                <input checked="checked" type="radio" class="export-nav" name="export_nav" value="bottom" /><?php echo $this->get_lang('BottomNavigation'); ?>
            </p>
        </div>
    </form>
</div>
