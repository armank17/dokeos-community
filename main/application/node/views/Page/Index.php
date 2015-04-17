<div class="row">
    <div class="form_header"><?php echo $this->get_lang('Pages'); ?></div>
</div>
<div id="content-css">
    <table id="table_lp_list" class="data_table">
        <thead
            <tr class="row_odd nodrop nodrag">
                <th width="10px"></th>            
                <th width="65%"><?php echo $this->get_lang('Title'); ?></th>
                <th width="10%">Edit</th>
                <th width="10%">Delete</th>
                <th width="10%">View</th>
                <!--th width="10%">Visible</th-->
            </tr>
        </thead> 
        <tbody class="sort ui-sortable">
            <?php if (!empty($this->pages)): ?>
                <?php 
                    foreach ($this->pages as $page):
                ?>
                    <tr>
                        <td></td>
                        <td><?php echo $page['title']; ?></td>
                        <td align="center">
                            <a href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=Page&func=getForm&nodeId='.$page['id'].'&'.api_get_cidReq(); ?>">
                                <?php echo Display::return_icon('pixel.gif', get_lang('Edit'), array('class' => 'actionplaceholdericon actionedit')); ?>
                            </a>
                        </td>
                        <td align="center">
                            <a class="page-delete-link" title="<?php echo get_lang('AreYouSureToDelete');?>" href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=Page&func=delete&nodeId='.$page['id'].'&'.api_get_cidReq(); ?>">
                                <?php echo Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')); ?>
                            </a>
                        </td>
                        <td align="center">
                            <a href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=Page&func=view&nodeId='.$page['id'].'&'.api_get_cidReq(); ?>">
                                <?php echo Display::return_icon('pixel.gif', get_lang('View'), array('class' => 'actionplaceholdericon actionpreview')); ?>
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
     <input type="hidden" id="cidReq" value="<?php echo urlencode(api_get_cidreq()); ?>" />
     <input type="hidden" id="webPath" value="<?php echo urlencode(api_get_path(WEB_PATH)); ?>" />
</div>
