<div class="row">
    <div class="form_header"><?php
      $category = $this->getRequest()->getProperty('category', '');
      switch ($category) {
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
      echo $this->get_lang('MenuLinks') .' : '. $this->get_lang($category_name);
     ?> </div>
</div>
<div id="content-css">
    <form id="menu-list-form" name="list-menulink-form" method="post" action="<?php echo $this->path.'&func=saveList&category='. $category; ?>">

        <table id="table_lp_list" class="data_table">
            <thead
                <tr class="row_odd nodrop nodrag">
                    <th width="10px"></th>            
                    <th width="55%"><?php echo $this->get_lang('Title'); ?></th>
                    <th width="10%"><?php echo $this->get_lang('Language'); ?></th>
                    <th width="10%"><?php echo $this->get_lang('Enabled'); ?></th>
                    <th width="10%"><?php echo $this->get_lang('Edit'); ?></th>
                    <th width="10%"><?php echo $this->get_lang('Delete'); ?></th>                
                    <!--th width="10%">Visible</th-->
                </tr>
            </thead> 
            <tbody class="sort ui-sortable">
                <?php if (!empty($this->menuLinks)): 
                        $language_list           = api_get_languages();
                        $language_list_with_keys = array();
                        $language_list_with_keys['all'] = get_lang('All');
                        for($i=0; $i<count($language_list['name']) ; $i++)
                            $language_list_with_keys[$language_list['folder'][$i]] = $language_list['name'][$i];
                        
                        foreach ($this->menuLinks as $menuLink):
                            $is_link_platform = ($menuLink['link_type'] == MENULINK_TYPE_PLATFORM);
                            $is_link_node     = ($menuLink['link_type'] == MENULINK_TYPE_NODE);
                            
                            $is_link_platform_admin  = $is_link_platform && ($menuLink['title'] == 'PlatformAdmin');
                            
                            $disabled_enabled  = $is_link_platform_admin;
                            $disabled_delete  = ($is_link_platform || $is_link_node);
                    ?>
                        <tr class="menulink-sortable">
                            <td class="sort-handle" align="center">
                                <?php 
                                    echo Display::return_icon('pixel.gif', get_lang('Move'), array( 'class' => 'actionplaceholdericon actionsdraganddrop ' ));
                                ?>
                                <input class="menulink-hidden-enabled"  type="hidden" name="enabled:<?php echo $menuLink['id']; ?>"  value="<?php echo $menuLink['enabled']; ?>" />
                                <input class="menulink-hidden-weight"   type="hidden" name="weight:<?php echo $menuLink['id']; ?>"   value="<?php echo $menuLink['weight']; ?>" />
                                <input class="menulink-hidden-parentid" type="hidden" name="parentid:<?php echo $menuLink['id']; ?>" value="<?php echo $menuLink['parent_id']; ?>" />
                            </td>
                            <td><?php echo ($is_link_platform)? $this->get_lang($menuLink['title']) : $menuLink['title']; ?></td>
                            <td align="center"><?php
                                $language = 'All';
                                foreach($language_list_with_keys as $key => $value){
                                    if(api_get_language_id($key) == $menuLink['language_id']){
                                        $language = $key;
                                        break;
                                    }
                                }
                                $langOrigName = api_get_language_info($language);
                                $langOrigName = $langOrigName['original_name'];
                                echo ucwords($langOrigName);
                            ?></td>
                            <td align="center">
                                <?php if(!$disabled_enabled): ?>
                                <input  class="menulink-check-enabled" type="checkbox" name="check:<?php echo $menuLink['id']; ?>" value="1" <?php echo (intval($menuLink['enabled']) == 1)? 'checked="checked"' : ''; ?> >
                                <?php endif; ?>
                            </td>
                            <td align="center">
                                <?php if(!$is_link_platform_admin): ?>
                                <a href="<?php echo $this->path.'&func=getForm&menuLinkId='. $menuLink['id'] .'&category='. $category; ?>">
                                    <?php echo Display::return_icon('pixel.gif', get_lang('Edit'), array('class' => 'actionplaceholdericon actionedit')); ?>
                                </a>
                                <?php endif; ?>
                            </td>
                            <td align="center">
                                <a class="menulink-delete-link" title="<?php echo get_lang('AreYouSureToDelete');?>" href="<?php echo ($disabled_delete)? 'javascript:void(0)' : ($this->path.'&func=delete&menuLinkId='. $menuLink['id'] .'&category='. $category); ?>">
                                    <?php 
                                        echo Display::return_icon('pixel.gif', get_lang('Delete'), array( 'class' => 'actionplaceholdericon actiondelete '. (($disabled_delete)? 'invisible' : '') ));
                                    ?>
                                </a>
                            </td>
                            <!--td></td-->
                        </tr>
                    <?php
                        endforeach;
                    ?>
                <?php else: ?>
                    <tr> 
                        <td colspan="5" align="center"><em><?php echo $this->get_lang('Empty'); ?></em></td>
                    </tr>
                <?php endif; ?>               
            </tbody>
        </table>
        <!--<button type="submit" name="submitDocument" class="upload"><?php echo $this->get_lang('Save'); ?></button>-->
        <input type="hidden" id="webPath" value="<?php echo urlencode(api_get_path(WEB_PATH)); ?>" />
        <input type="hidden" id="category" value="<?php echo $category; ?>" />
    </form>
    </div>