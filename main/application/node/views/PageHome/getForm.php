<div align="center"><br/>
        <table class="gallery_scenario" style=""><tbody>
        <tr>
       <?php        
        foreach($this->pageHomeTemplates as $template):
            if(!$template){
               continue; 
            }
            ?>
            <td>
                <a href="<?php echo api_get_path(WEB_CODE_PATH); ?>index.php?module=node&cmd=PageHome&func=getTemplate&id=<?php echo $template['id']; ?>" class="link-homepage-template">
                <span style="width:135px" class="section_scenario width_scenario_template_button" >
                    <span class="sectiontitle"> <?php echo $this->get_lang($template['title']); ?></span>
                    <span class=""> <?php echo Display::return_icon($template['image'], get_lang($template['title'])); ?></span>
                </span>            
            </td>
        <?php endforeach; ?>    
        </tr></tbody>
        </table></div>
   
    <form id="node-form" name="nodeForm" method="post" action="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=PageHome&func='.(!empty($this->nodeId)?'update':'create'); ?>">
    <?php if (!empty($this->nodeId)): ?>
        <input type="hidden" name="nodeId" value="<?php echo $this->nodeId; ?>" />
    <?php endif; ?> 
    <div class="row">
        <div class="form_header">
            <?php  echo !empty($this->nodeId)?$this->get_lang('EditPage'):$this->get_lang('NewPage'); ?>           
        </div>        
    </div>    
    <div class="row">
        <button type="submit" name="submitDocument" style="margin-bottom:10px;" class="upload"><?php echo $this->get_lang('Save');?></button>
        <div class="formw">
            <input type="text" id="node_title" name="node_title" class="focus required" value="<?php echo $this->pageInfo['title']; ?>" style="width:550px;" placeholder="<?php echo $this->get_lang('AddHerePageTitle'); ?>" />
        </div>
        <input  type="hidden" name="created_by" value="<?php echo api_get_user_id();?>"/>
        <input  type="hidden" name="active"     value="1"/>
        
    </div>    
    <div class="row">        
        <?php  
        api_disp_html_area('node_editor', str_replace('{WEB_PATH}', api_get_path(WEB_PATH), $this->pageInfo['content']), '', '', null, $this->editorConfig); ?>
    </div>
    <div style="margin-top:15px"> 
        <p><input type="checkbox" id="promoted"      name="promoted"      value="1" <?php echo ($this->pageInfo['promoted']==1)? 'checked' : ''; ?>>      <label for="promoted"><?php echo $this->get_lang('PromoteInHomePage')?></p>        
        <p><input type="checkbox" id="enabled"       name="enabled"       value="1" <?php echo ($this->pageInfo['enabled']==1)? 'checked' : ''; ?>>       <label for="enabled"><?php echo $this->get_lang('Enabled')?></p>
        <p><input type="checkbox" id="display_title" name="display_title" value="1" <?php echo ($this->pageInfo['display_title']==1)? 'checked' : ''; ?>> <label for="display_title"><?php echo $this->get_lang('displayTitle')?></p>
        <p><input type="checkbox" id="createlink"    name="createlink"    value="1" class="createlink" <?php echo (($this->pageInfo['menu_link_id']>0) && ($this->linkInfo['enabled']==1))? 'checked' : ''; ?> > <label for="createlink"><?php echo $this->get_lang('ProvideAMenuLink')?></p>       
        <?php  
        if(!empty($this->nodeId)){  ?>
            <div id="has_url">
                <input id='hasUrltxt' readonly="readonly" type="text" size=60px value="<?php echo api_get_path(WEB_CODE_PATH); ?>index.php?module=node&cmd=PageHome&func=showPage&nodeId=<?php echo $this->pageInfo['id']; ?>">
            </div>              
        <?php   } ?>
    </div> 
        <div id="linkcreation">
            <fieldset>
                <legend style="font-weight:bold; color:#61380B;"><?php echo $this->get_lang('NewLinkDetails'); ?></legend>
                <div><label for='menu-link-title'><?php echo $this->get_lang('MenuLinkTitle') ?></label></div>
                <div><input type='text' id="menu_link_title_id" maxlength='120' size='50' name='menu_link_title' value="<?php echo $this->linkInfo['title'] ?>"></div>
                <div><label for='menu-link-description'><?php echo $this->get_lang('MenuLinkDescription') ?></label> </div>
                <div><textarea rows='5' cols='64' name='menu_link_description'><?php echo $this->linkInfo['description'] ?></textarea></div>
                <!--<div id="linkEnabled"><input type="checkbox" name="linkEnabled"value="1"<?php echo ($this->linkInfo['enabled']==1)?'checked':'';?>><?php echo $this->get_lang('EnabledLink'); ?></div>-->
                <div><label for='menu-link-title'><?php echo $this->get_lang('Category') ?></label></div>
                <div>
                    <select name='category'>
                        <?php for($i=0;$i< count($this->listLinkCategories);$i++){
                            switch ($this->listLinkCategories[$i]){
                                case MENULINK_CATEGORY_HEADER:
                                    $category_name = 'Header';
                                break;
                                case MENULINK_CATEGORY_FOOTER:
                                    $category_name = 'Footer';
                                break;
                                case MENULINK_CATEGORY_LEFTSIDE:
                                    $category_name = 'LeftSide';
                                break;
                             }
                            $selected = ($this->listLinkCategories[$i]==$this->linkInfo['category'])? 'selected' : ''; ?>
                        <option value="<?php echo $this->listLinkCategories[$i];?>"<?php echo $selected; ?> ><?php echo $this->get_lang($category_name); ?></option> 
                        <?php } ?>
                    </select>
                </div>
                <div><label for='menu-link-title'><?php echo $this->get_lang('Target') ?></label></div>
                <div>
                    <select name='target'>
                        <option value="_self"  <?php echo ($this->linkInfo['target'] == '_self')?   'selected="selected"' : ''; ?>><?php echo get_lang("SelfNavigation") ?></option> 
                        <option value="_top"   <?php echo ($this->linkInfo['target'] == '_top')?    'selected="selected"' : ''; ?>><?php echo get_lang("TopNavigation") ?></option> 
                        <option value="_blank" <?php echo ($this->linkInfo['target'] == '_blank')?  'selected="selected"' : ''; ?>><?php echo get_lang("BlankNavigation") ?></option> 
                        <option value="_parent"<?php echo ($this->linkInfo['target'] == '_parent')? 'selected="selected"' : ''; ?>><?php echo get_lang("ParentNavigation") ?></option> 
                    </select>
                </div>
            </fieldset>
        </div>
        <p>
            <?php
                $language_list           = api_get_languages();
                $language_list_with_keys = array();
                $language_list_with_keys['all'] = get_lang('All');
                
                for($i=0; $i<count($language_list['name']) ; $i++)
                    $language_list_with_keys[$language_list['folder'][$i]] = $language_list['name'][$i];
                
                if(!empty($this->nodeId)){
                    $selected_language = $this->pageInfo['language_id'];
                } else {
                    $current_language  = $this->languageInterface;                        
                    $selected_language = api_get_language_id($current_language);
                }
            ?>
     
            <br/><label><?php echo $this->get_lang('Language'); ?></label>
            <select name="language_id">
            <?php   foreach($language_list_with_keys as $key => $value): ?>
                <option value="<?php echo $key; ?>" <?php echo ((api_get_language_id($key) == $selected_language)? 'selected="selected"': '') ?>><?php echo $value; ?></option>
            <?php   endforeach; ?>
            </select>
        </p>
<button type="submit" name="submitDocument" class="upload"><?php echo $this->get_lang('Save');?></button>
    <div class="clear"></div>
</form>

