<?php echo '<script type="text/javascript">
    $(document).ready(function(){
    $("a.notwork").qtip({
    content: "'.get_lang("FirstMakeVisibleThisPage").'",
    show: "mouseover",
    hide: "mouseout"
});

    });
    
</script>'; ?>
<div class="row">
    <div class="form_header"><?php echo $this->get_lang('Pages'); ?></div>
</div>
<div id="content-css">
    <table id="table_lp_list" class="data_table">
        <thead
            <tr class="row_odd nodrop nodrag">
                <th width="65px"><?php echo $this->get_lang('Visible'); ?></th>
                <th width="160px"><?php echo $this->get_lang('StartDate'); ?></th>
                <th width="160px"><?php echo $this->get_lang('EndDate'); ?></th>
                <th width="65px"><?php echo $this->get_lang('Enabled'); ?></th>                
                <th width="78px"><?php echo $this->get_lang('Trainer'); ?></th>
                <th width="85px"><?php echo $this->get_lang('Learner'); ?></th>
                <th width="58px"><?php echo $this->get_lang('Guest'); ?></th>
                <th width="95px"><?php echo $this->get_lang('Title'); ?></th>
                <th width="100px"><?php echo $this->get_lang('Language'); ?></th>
                <th width="65px"><?php echo $this->get_lang('Edit'); ?></th>
            </tr>
        </thead>
        <tbody class="sort ui-sortable">
            <?php if (!empty($this->pages)): ?>
                <?php //var_dump($this->pages);
                    foreach ($this->pages as $page):
                ?>
                    <tr>
                        <td align="center">
                            <?php echo Display::return_icon('pixel.gif','',array('class'=>($page['visible']? 'actionplaceholdericon actionaccept':'actionplaceholdericon actionvalidate_na')), ($page['visible']? $this->get_lang('AnnouncementAvailable') : $this->get_lang('AnnouncementNotAvailable')));; ?>
                        </td>
                        <td align="center">
                            <?php echo TimeZone::ConvertTimeFromServerToUser(api_get_user_id(), $page['start_date'], 'd-m-Y h:i a'); ?>
                        </td>
                        <td align="center">
                            <?php echo TimeZone::ConvertTimeFromServerToUser(api_get_user_id(), $page['end_date'], 'd-m-Y h:i a'); ?>
                        </td>
                       
                       <td align="center">
                            <a class="<?php echo $page['visible']? 'enabled': 'notwork'; ?>" href="<?php
                                if($page['visible']){
                                    echo api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=News&func=setEnable&value='. ($page['enabled'] && $page['visible']? 1 : 0) .'&nodeId='.$page['node_id'];
                                } else {
                                    echo 'javascript:void(0)';
                                }
                            ?>">
                            <?php echo Display::return_icon('pixel.gif','',array('class'=>$page['enabled'] && $page['visible']? 'actionplaceholdericon actionvisible' : 'actionplaceholdericon actioninvisible'), get_lang('show_hide'))?>
                            </a>
                        </td>
                        <td align="center">
                            <a class ="set_visible" href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=News&func=setVisible&nodeId='.$page['node_id']; ?>&person=3&value=<?php echo $page['visible_by_trainer']; ?>"><?php echo Display::return_icon('pixel.gif','',array('class'=>$page['visible_by_trainer']  ? 'actionplaceholdericon actionvisible' : 'actionplaceholdericon actioninvisible'), get_lang('show_hide'))?></a>
                        </td>
                        <td align="center">
                            <a class ="set_visible" href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=News&func=setVisible&nodeId='.$page['node_id']; ?>&person=2&value=<?php echo $page['visible_by_learner']; ?>"><?php echo Display::return_icon('pixel.gif','',array('class'=>$page['visible_by_learner']  ? 'actionplaceholdericon actionvisible' : 'actionplaceholdericon actioninvisible'), get_lang('show_hide'))?></a>
                        </td>
                        <td align="center">
                            <a class ="set_visible" href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=News&func=setVisible&nodeId='.$page['node_id']; ?>&person=1&value=<?php echo $page['visible_by_guest']; ?>"><?php echo Display::return_icon('pixel.gif','',array('class'=>$page['visible_by_guest']  ? 'actionplaceholdericon actionvisible' : 'actionplaceholdericon actioninvisible'), get_lang('show_hide'))?></a>
                        </td>
                        <td><?php echo $page['title']; ?></td>
                        <td>
                            <?php echo api_ucfirst($page['lang_name']);?>
                        </td>
                        <td>
                            <a href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=News&func=getForm&nodeId='.$page['node_id'] ?>">
                                <?php echo Display::return_icon('pixel.gif', get_lang('Edit'), array('class' => 'actionplaceholdericon actionedit')); ?>
                            </a>
                            <a class="page-delete-news" title="<?php echo get_lang('AreYouSureToDelete');?>" href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=News&func=delete&nodeId='.$page['node_id']; ?>">
                                <?php echo Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')); ?>
                            </a>
                        </td>
                    </tr>
                <?php
                    endforeach;
                ?>
            <?php else: ?>
                <tr> 
                    <td colspan="10" align="center"><em><?php echo $this->get_lang('Empty'); ?></em></td>
                </tr>
            <?php endif; ?>
               
        </tbody>
    </table>
     <input type="hidden" id="cidReq" value="<?php echo urlencode(api_get_cidreq()); ?>" />
     <input type="hidden" id="webPath" value="<?php echo urlencode(api_get_path(WEB_PATH)); ?>" />
</div>

