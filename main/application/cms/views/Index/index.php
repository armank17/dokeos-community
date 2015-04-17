<?php $is_allowed_to_edit= api_is_allowed_to_edit(); ?>
    <table id="table_lp_list" class="data_table">
        <thead
            <tr class="row_odd nodrop nodrag">
                <?php if($is_allowed_to_edit):?>
                    <th><?php echo get_lang('Move');?></th>
                    <th><?php echo get_lang('Edit');?></th>
               <?php endif; ?>
                
                <th style="width:640px">Cms Name</th>
                <th><?php echo get_lang('Date');?></th>
                 <?php if($is_allowed_to_edit):?>
                <th><?php echo get_lang('Delete');?></th>
                <th><?php echo get_lang('Visible');?></th>
                <th><?php echo get_lang('Configure');?></th>
                <?php endif; ?>
            </tr>
        </thead> 
        <tbody class="sort ui-sortable">
        <?php if (!empty($this->cms)): ?>
       
            <?php foreach ($this->cms as $cms): ?>
            <tr>
                <?php if($is_allowed_to_edit):?>
                <td  class="nodrag" align="center" style="width:60px;"> 
                    <?php echo Display::return_icon('pixel.gif', get_lang('Move'), array("class" => "actionplaceholdericon actionsdraganddrop")) ?></td>      
                </td>
                <td  class="nodrag" align="center" style="width:60px;">
                    <a href= "<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=cms&cmd=Cms&func=edit&cmsId='.$cms['id']?>">
                        <?php echo Display::return_icon('pixel.gif', get_lang('Edit'), array('class' => 'actionplaceholdericon actionedit'))?></a>
                </td>
                <?php endif; ?>
                <td><a href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=cms&cmd=Cms&func=show&cmsId='.$cms['id']?>">
                        <?php echo $cms['title']; ?>
                    </a>
                    
                </td >
                <td class="nodrag" align="center" style="width:60px;">
                    
                </td>
                 <?php if($is_allowed_to_edit):?>
                <td class="nodrag" align="center" style="width:60px;">
                    <a href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=cms&cmd=Cms&func=delete&cmsId='.$cms['id']?>">
                    <?php echo Display::return_icon('pixel.gif', get_lang('Delete'), array("class" => "actionplaceholdericon actiondelete")) ?></td>                     
                    </a>
                </td>
                <td class="nodrag" align="center" style="width:60px;">
                    <a href="">
                        <?php echo Display::return_icon('pixel.gif', get_lang('Visible'), array("class" => "actionplaceholdericon actionvisible")) ?></td>                     
                    </a>
                </td>
                <td class="nodrag" align="center" style="width:60px;">
                    <a href= <?php api_get_self()?>>
                    <?php echo Display::return_icon('pixel.gif', get_lang('Configure'), array("class" => "actionplaceholdericon actionreload_na")) ?></td>                     
                    </a>
                </td>
                  <?php endif; ?>
            </tr>
            <?php endforeach; ?>
        
<?php 
else : ?> 
            <tr>
                <td></td>
                <td></td>
                <td align="center"><?php echo get_lang('NoDocuments');?></td>
            </tr>
    <?php
endif;?>
        </tbody>
    </table>
    

    


<?php


?>