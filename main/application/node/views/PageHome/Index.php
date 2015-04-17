<div class="row">
    <div class="form_header"><?php echo $this->get_lang('Pages'); ?></div>
</div>
<div id="content-css">
    <table id="table_lp_list" class="data_table">
        <thead
            <tr class="row_odd nodrop nodrag">
                <th width="10px"><?php echo $this->get_lang('Enabled') ?></th>
                <th width="10px"><?php echo $this->get_lang('Promoted') ?></th>   
                <th width="50%"><?php echo $this->get_lang('Title'); ?></th>
                <th width="10%"><?php echo $this->get_lang('Edit'); ?></th>
                <th width="10%"><?php echo $this->get_lang('Delete'); ?></th>
                <th width="10%"><?php echo $this->get_lang('View'); ?></th>
                <!--th width="10%">Visible</th-->
            </tr>
        </thead>
        <tbody class="sort ui-sortable">
            <?php if (!empty($this->pages)):                    
                    foreach ($this->pages as $page):
                ?>
                    <tr>
                        <td align="center">
                            <a class="enabled" href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=PageHome&func=updateEnabled&nodeId='.$page['id'].'&enabled='.(($page['enabled']==0) ? 1 : 0) ?>">
                                <?php echo Display::return_icon('pixel.gif','',array('class'=>$page['enabled'] ? 'actionplaceholdericon actionaccept' : 'actionplaceholdericon actionvalidate_na' ), ($page['enabled'] ? get_lang('PageAvailable') : get_lang('PageNotAvailable')));; ?>
                            </a>
                        </td>
                        <td align="center">
                            <a class="enabled" href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=PageHome&func=updateEnabled&nodeId='.$page['id'].'&promoted='.(($page['promoted']==0)?1:0) ?>">
                                <?php echo Display::return_icon('pixel.gif','',array('class'=>$page['promoted'] ? 'actionplaceholdericon actionaccept' : 'actionplaceholdericon actionvalidate_na'), ($page['promoted'] ? get_lang('PageAvailable') : get_lang('PageNotAvailable')));; ?>
                            </a>
                        </td>
                        <td><?php echo $page['title']; ?></td>
                        <td align="center">
                            <a href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=PageHome&func=getForm&nodeId='.$page['id'] ?>">
                                <?php echo Display::return_icon('pixel.gif', get_lang('Edit'), array('class' => 'actionplaceholdericon actionedit')); ?>
                            </a>
                        </td>
                        <td align="center">
                            <a class="page-delete-home" title="<?php echo get_lang('AreYouSureToDelete');?>" href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=PageHome&func=delete&nodeId='.$page['id']; ?>">
                                <?php echo Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')); ?>
                            </a>
                        </td>
                        <td align="center">                                     
                            <a href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=PageHome&func=showPage&nodeId='.$page['id']?>">
                                <?php echo Display::return_icon('pixel.gif', get_lang('View'), array('class' => 'actionplaceholdericon actionpreview')); ?>
                            </a>
                        </td>
                       
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