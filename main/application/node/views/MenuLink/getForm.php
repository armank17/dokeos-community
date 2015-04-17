<style>
    .CustSelectHere {
        margin-bottom: 8px !important;
    }
    #menulink-form {
        width:80%;
    }
    label.error {
        color: #FF0000;
        /*margin: -2px 9px 11px;*/
    }
    div.row {
        padding:0px;
    }
    .label-check {
        width:auto; 
        float:left; 
    }
</style>
<form id="menulink-form" name="menulink-form" method="post" action="<?php echo $this->path . '&func=' . (!empty($this->menuLinkId) ? 'update' : 'create'); ?>">
    <?php
    $category = $this->getRequest()->getProperty('category', '');
    $is_category_header = ($category == MENULINK_CATEGORY_HEADER);
    $is_category_leftside = ($category == MENULINK_CATEGORY_LEFTSIDE);

    if (!empty($this->menuLinkId)):
        $is_link_platform = ($this->menuLinkInfo['link_type'] == MENULINK_TYPE_PLATFORM);
        $is_link_node = ($this->menuLinkInfo['link_type'] == MENULINK_TYPE_NODE);

        $is_link_platform_course = $is_link_platform && ($this->menuLinkInfo['title'] == 'MyCourses');
        $is_link_platform_admin = $is_link_platform && ($this->menuLinkInfo['title'] == 'PlatformAdmin');

        $disable_title = ($is_link_platform);
        $disable_path = ($is_link_platform || $is_link_node);
        $disable_description = false; //!$is_link_node;
        $disable_target = $is_link_platform;
        $disable_language = ($is_link_platform || $is_link_node);
        $disable_visibility = false;
        $disable_enabled = $is_link_platform_admin; //$is_link_node;
        ?>
        <input type="hidden" name="menuLinkId" value="<?php echo $this->menuLinkId; ?>" />
        <?php
    endif;
    ?>
    <div class="row">
        <div class="form_header" style="margin-bottom:20px; margin-top:10px;">
            <?php echo!empty($this->menuLinkId) ? $this->get_lang('EditMenuLink') : $this->get_lang('NewMenuLink'); ?>           
        </div>        
    </div>    
    <div class="row">
        <div class="label">
            <div class="label1">
                <?php echo $this->get_lang('Title'); ?>
            </div>
        </div>
        <div class="formw">
            <div class="formw1 cusformw-content">
                <input type="text" id="menulink_title" name="menulink_title" <?php echo $disable_title ? 'disabled="disabled"' : ''; ?> class="focus required" value="<?php echo $is_link_platform ? $this->get_lang($this->menuLinkInfo['title']) : $this->menuLinkInfo['title']; ?>" style="width:550px;" />
            </div>
        </div>
    </div>
    <div class="row">
        <div class="label">
            <div class="label1">
                <?php echo $this->get_lang('Path'); ?>
            </div>
        </div>
        <div class="formw"> 
            <div class="formw1 cusformw-content"> 
                <input type="text" id="menulink_path" name="menulink_path" <?php echo $disable_path ? 'disabled="disabled"' : ''; ?> class="required" value="<?php echo $this->menuLinkInfo['link_path']; ?>" style="width:550px;" />
            </div>
        </div>
    </div>

    <div class="row">
        <div class="label">
            <div class="label1">
                <?php echo $this->get_lang('Description'); ?>
            </div>
        </div>
        <div class="formw"> 
            <div class="formw1 cusformw-content"> 
                <input type="text" id="menulink_description" name="menulink_description" <?php echo $disable_description ? 'disabled="disabled"' : ''; ?> class="required" value="<?php echo $this->menuLinkInfo['description']; ?>" style="width:550px;"  /> 
            </div>
        </div>
    </div>    
    <div class="row">
        <div class="label">
            <div class="label1">
                <?php echo $this->get_lang('Target'); ?>
            </div>
        </div>
        <div class="formw"> 
            <div class="formw1 cusformw-content"> 
                <select name="target" class="CustSelectHere" <?php echo $disable_target ? 'disabled="disabled"' : ''; ?> >
                    <option value="_self"   <?php echo ($this->menuLinkInfo['target'] == '_self') ? 'selected="selected"' : ''; ?>>_self</option>
                    <option value="_blank"  <?php echo ($this->menuLinkInfo['target'] == '_blank') ? 'selected="selected"' : ''; ?>>_blank</option>
                    <option value="_top"    <?php echo ($this->menuLinkInfo['target'] == '_top') ? 'selected="selected"' : ''; ?>>_top</option>
                    <option value="_parent" <?php echo ($this->menuLinkInfo['target'] == '_parent') ? 'selected="selected"' : ''; ?>>_parent</option>
                </select>
            </div>
        </div>
    </div>    
    <?php
    if (!$disable_language):
        $language_list = api_get_languages();
        $language_list_with_keys = array();
        $language_list_with_keys['all'] = get_lang('All');
        for ($i = 0; $i < count($language_list['name']); $i++)
            $language_list_with_keys[$language_list['folder'][$i]] = $language_list['name'][$i];
        if (!empty($this->menuLinkId)) {
            $selected_language = $this->menuLinkInfo['language_id'];
        } else {
            $current_language = $this->languageInterface;
            $selected_language = api_get_language_id($current_language);
        }
        ?>
        <?php
    endif;
    ?>
    <div class="row">
        <div class="label">
            <div class="label1">
                <?php echo $this->get_lang('Language'); ?>
            </div>
        </div>
        <div class="formw"> 
            <div class="formw1 cusformw-content">
                <select name="language_id">
                    <?php foreach ($language_list_with_keys as $key => $value): ?>
                        <option value="<?php echo $key; ?>" <?php echo ((api_get_language_id($key) == $selected_language) ? 'selected="selected"' : '') ?>><?php echo $value; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>    
    <div class="row">
        <div class="label">
            <?php echo get_lang('Visibility'); ?>
        </div>
        <div class="formw"> 
            <input type="hidden" name="menulink_visibility" />
            <!--<fieldset name="" class="menulink_visibility">-->
            <div class="label-check"><input type="checkbox" <?php echo ((($this->menuLinkInfo['visibility'] & MENULINK_VISIBLE_ANONYMOUS) || empty($this->menuLinkId)) ? 'checked="checked"' : '') . ' ' . ($is_link_platform ? 'disabled="disabled"' : ''); ?> id="menulink_visibility_anonymous" name="menulink_visibility_anonymous" value="1" /><label for="menulink_visibility_anonymous"><?php echo get_lang('Anonymous'); ?></label> </div>
            <br/>
            <br/>
            <input type="checkbox" <?php echo (($this->menuLinkInfo['visibility'] & MENULINK_VISIBLE_LOGGED) ? 'checked="checked"' : '') . ' ' . ($is_link_platform_course ? 'disabled="disabled"' : ''); ?> id="menulink_visibility_logged" name="menulink_visibility_logged" value="1" /><label for="menulink_visibility_logged"><?php echo get_lang('Logged'); ?></label>
            <br/>
            <?php if (!($is_link_platform_course || $is_category_header || $is_category_leftside)): ?>
                <input type="checkbox" <?php echo (($this->menuLinkInfo['visibility'] & MENULINK_VISIBLE_COURSE_IN) ? 'checked="checked"' : '') . ' ' . (($is_link_platform_course || $is_category_header || $is_category_leftside) ? 'disabled="disabled"' : ''); ?> id="menulink_visibility_course_in" name="menulink_visibility_course_in" value="1" /><label for="menulink_visibility_course_in"><?php echo get_lang('CourseIn'); ?></label>
                <br/>
                <input type="checkbox" <?php echo (($this->menuLinkInfo['visibility'] & MENULINK_VISIBLE_TOOL_IN) ? 'checked="checked"' : '') . ' ' . (($is_link_platform_course || $is_category_header || $is_category_leftside) ? 'disabled="disabled"' : ''); ?> id="menulink_visibility_tool_in" name="menulink_visibility_tool_in" value="1" /><label for="menulink_visibility_tool_in"><?php echo get_lang('ToolIn'); ?></label>
            <?php endif; ?>
            <!--</fieldset>-->
        </div>
        <div class="row">
            <div class="label">
                <div class="label1">
                    <?php echo get_lang('Enable'); ?>
                </div>
            </div>
            <div class="formw">
                <div class="formw1 cusformw-content">
                    <?php if (!$disable_enabled): ?>
                        <input type="checkbox" id="menulink_enabled" name="menulink_enabled" <?php echo $disable_enabled ? 'disabled="disabled"' : ''; ?> value="1" <?php echo ((empty($this->menuLinkId)) || (!empty($this->menuLinkId) && intval($this->menuLinkInfo['enabled']) == 1)) ? 'checked' : ''; ?>><label for="menulink_enabled"><?php echo get_lang('Enabled') ?></label>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>    




    <input type="hidden" name="category" value="<?php echo strtolower($this->getRequest()->getProperty('category', '')); ?>">

    <div class="row">
        <div class="pull-bottom">
            <button type="submit" name="submitDocument" class="upload"><?php echo $this->get_lang('Save'); ?></button>
        </div>
    </div> 
    <div class="clear"></div>
</form>