<div class="row">
    <div class="form_header"><?php echo $this->get_lang('MenuLinks'); ?></div>
</div>
<div id="content-css">
    <table id="table_targets" class="data_table">
        <thead
            <tr class="row_odd nodrop nodrag">
                <th width="10px"></th>            
                <th width="55%"><?php echo $this->get_lang('Title'); ?></th>
                <th width="30%"><?php echo $this->get_lang('Description'); ?></th>
                <th width="10%"><?php echo $this->get_lang('Edit'); ?></th>
                <!--th width="10%">Visible</th-->
            </tr>
        </thead> 
        <tbody class="sort ui-sortable">
            <tr>
                <td></td>
                <td><?php echo $this->get_lang('Header'); ?></td>
                <td><?php echo $this->get_lang('HeaderDescription'); ?></td>
                <td align="center">
                    <a href="<?php echo $this->path.'&func=listMenuLinks&category='. MENULINK_CATEGORY_HEADER; ?>">
                        <?php echo Display::return_icon('pixel.gif', get_lang('Edit'), array('class' => 'actionplaceholdericon actionedit')); ?>
                    </a>
                </td>
                <!--td></td-->
            </tr>
            <tr>
                <td></td>
                <td><?php echo $this->get_lang('Footer'); ?></td>
                <td><?php echo $this->get_lang('FooterDescription'); ?></td>
                <td align="center">
                    <a href="<?php echo $this->path.'&func=listMenuLinks&category='. MENULINK_CATEGORY_FOOTER; ?>">
                        <?php echo Display::return_icon('pixel.gif', get_lang('Edit'), array('class' => 'actionplaceholdericon actionedit')); ?>
                    </a>
                </td>
                <!--td></td-->
            </tr>
            <tr>
                <td></td>
                <td><?php echo $this->get_lang('LeftSide'); ?></td>
                <td><?php echo $this->get_lang('LeftSideDescription'); ?></td>
                <td align="center">
                    <a href="<?php echo $this->path.'&func=listMenuLinks&category='. MENULINK_CATEGORY_LEFTSIDE; ?>">
                        <?php echo Display::return_icon('pixel.gif', get_lang('Edit'), array('class' => 'actionplaceholdericon actionedit')); ?>
                    </a>
                </td>
                <!--td></td-->
            </tr>
        </tbody>
    </table>
     <input type="hidden" id="webPath" value="<?php echo urlencode(api_get_path(WEB_PATH)); ?>" />
</div>