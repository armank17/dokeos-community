<div class="bg_white clear_b">
    <table class="data_table" id="table_lp_list">
            <thead>
                <tr class="row_odd nodrop nodrag">
                    <th width="80px"><?php echo $this->get_lang("Move"); ?></th>
                    <th><?php echo $this->get_lang("ExistingCourses"); ?></th>
                    <th width="80px"><?php echo $this->get_lang("Delete"); ?></th>
                    <th width="80px"><?php echo $this->get_lang("Visible"); ?></th>
                </tr>
            </thead>
            <tbody class="sort">
                <?php if (!empty($this->modules)): ?>
                <?php 
                    foreach ($this->modules as $ind => $module):                        
                        $isVisible = api_get_item_visibility($this->courseInfo, 'learnpath', $module['id']);
                        $visible   = $isVisible == 1 ? Display::return_icon('pixel.gif', $this->get_lang('Visible'), array('class' => 'actionplaceholdericon actionvisible')) : Display::return_icon('pixel.gif', $this->get_lang('Invisible'), array('class' => 'actionplaceholdericon actionvisible invisible'));
                ?>
                    <tr id="lp_row_<?php echo $module['id']; ?>" class="<?php echo ($ind % 2 == 0) ? 'row_even' : 'row_odd'; ?>">
                        <td class="dragHandle" align="center" style="cursor:pointer;width:40px;"><?php echo Display::return_icon('pixel.gif', $this->get_lang('Move'), array('class' => 'actionplaceholdericon actionsdraganddrop')); ?></td>
                        <td class="nodrag" align="left"><a href="<?php echo api_get_path(WEB_CODE_PATH).'newscorm/lp_controller.php?'.api_get_cidreq().'&action=view&lp_id='.$module['id']; ?>" class="blue_link"><?php echo stripslashes($module['name']); ?></a></td>
                        <td class="nodrag" align="center"><a href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=Home&func=adminHome&lpDelete='.$module['id'].'&'.api_get_cidreq(); ?>" onclick="if (!confirm('<?php echo trim($this->get_lang('AreYouSureToDelete')).' '.addslashes($module['name']); ?>')) {return false;}"><?php echo Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')); ?></a></td>
                        <td class="nodrag" align="center"><a href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=Home&func=adminHome&lpVisible='.$module['id'].'&'.api_get_cidreq(); ?>"><?php echo $visible; ?></a></td>
                    </tr>                
                <?php endforeach; ?>                
                <?php endif; ?>                
            </tbody>
    </table>
</div>
<input type="hidden" id="courseCode" value="<?php echo urlencode($this->courseCode); ?>" />
<input type="hidden" id="webPath" value="<?php echo urlencode(api_get_path(WEB_PATH)); ?>" />