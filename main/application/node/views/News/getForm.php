<form id="news-form" name="news-form" method="post" action="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=News&func='.(!empty($this->nodeId)?'update':'create'); ?>">
    <?php if (!empty($this->nodeId)): ?>
        <input type="hidden" name="nodeId" value="<?php echo $this->nodeId; ?>" />
    <?php endif; ?>
    <div class="row">
        <div class="form_header">
            <?php echo !empty($this->nodeId)?$this->get_lang('EditPage'):$this->get_lang('NewPage'); ?>           
        </div>        
    </div>    
    <div class="row">
        
        <!--<button type="submit" name="submitDocument" class="upload"><?php echo $this->get_lang('Save'); ?></button>-->
        
        <div class="formw">
            <input type="text" name="node_title" class="focus required" value="<?php echo $this->pageInfo['title']; ?>" style="width:550px;" placeholder="<?php echo $this->get_lang('AddHereNewsTitle'); ?>" />            
           <!--<input type="checkbox" value="1" name="enabled" <?php //if($this->pageInfo['enabled']){echo 'checked';}else echo ''; ?>><?php //echo get_lang('Enabled');?> -->
        </div>
        <input type="hidden" name="created_by" value="<?php echo api_get_user_id();?>"/>
    </div>
    
    <div class="row">        
        <?php api_disp_html_area('node_editor', $this->pageInfo['content'], '', '', null, $this->editorConfig); ?>
    </div>

    <div class="clear"></div> 
        
    <?php
       
                    $language_list           = api_get_languages();
                    $language_list_with_keys = array();
                    $language_list_with_keys['all'] = get_lang('All');
                    for($i=0; $i<count($language_list['name']) ; $i++)
                        $language_list_with_keys[$language_list['folder'][$i]] = $language_list['name'][$i];
                    
                    if(!empty($this->nodeId)){
                        $selected_language = $this->pageInfo['language_id'];
                    } else {
                        $current_language = $this->languageInterface;                        
                        $selected_language = api_get_language_id($current_language);
                    }
            ?>
            <br/><label><?php echo $this->get_lang('Language'); ?></label>
            <select name="language_id">
            <?php   foreach($language_list_with_keys as $key => $value): ?>
                <option value="<?php echo $key; ?>" <?php echo ((api_get_language_id($key) == $selected_language)? 'selected="selected"': '') ?>><?php echo $value; ?></option>
            <?php   endforeach; ?>
            </select>
            <br/><input type="hidden" name="news_visibility" />
            <fieldset>
                <legend><?php echo get_lang('Visible').':'; ?></legend>
                     <input type="checkbox" id="visible_by_trainer" name="visible_trainer"  value="1"   <?php if ($this->pageInfo['visible_by_trainer']) {echo 'checked'; }else echo ''; ?>><label for="visible_by_trainer"><?php echo get_lang('Teacher'); ?></label>
                <br/><input type="checkbox" id="visible_by_learner" name="visible_learner"  value="1"   <?php if ($this->pageInfo['visible_by_learner']) {echo 'checked'; } else echo ''; ?>><label for="visible_by_learner"><?php echo get_lang('Student'); ?></label>
                <br/><input type="checkbox" id="visible_by_guest"   name="visible_guest"    value="1"   <?php if ($this->pageInfo['visible_by_guest']) {echo 'checked'; }else echo ''; ?>><label for="visible_by_guest"><?php echo get_lang('Guest'); ?></label>
            </fieldset>
         
            <input type="hidden" name="active" value="1"/>
            <input type="checkbox" name="send_mail" id="send_mail" value=1><label for="send_mail"><?php echo get_lang('SendMail') . ':'; ?></label>
            <br/><label for="from"><?php echo $this->get_lang('From'); ?></label>
            <input type="text" id="from" name="startDate" value="<?php echo date('d-m-Y h:i a',strtotime($this->pageInfo['start_date'])>0)? date('d-m-Y h:i a',strtotime($this->pageInfo['start_date'])) : ''; ?>" >
            <label for="to"><?php echo $this->get_lang('To');?></label>
<!--            <input type="text" id="to" name="endDate" value="<:?php echo date('d-m-Y h:i a',strtotime($this->pageInfo['end_date'])>0)? date('d-m-Y h:i a',strtotime($this->pageInfo['end_date'])) : ''; ?>" >-->
            <input type="text" id="to" name="endDate" value="<?php if(date('d-m-Y h:i a',strtotime($this->pageInfo['end_date'])) == date('d-m-Y h:i a',strtotime($this->pageInfo['start_date']))){echo date('d-m-Y h:i a',strtotime($this->pageInfo['end_date'].'+ 1 day'));}else{echo date('d-m-Y h:i a',strtotime($this->pageInfo['end_date']));} ?>" >
            <div class="row"> 
                <div class="pull-bottom">
                <button type="submit" name="submitDocument" class="upload"><?php echo $this->get_lang('Save'); ?></button>
                </div>
                </div>
</form>
