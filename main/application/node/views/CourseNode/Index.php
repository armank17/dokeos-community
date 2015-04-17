<?php 
echo '<script type="text/javascript" src="'.api_get_path('WEB_CODE_PATH').'course_home/js/functionsAlerts.js"></script>';
$count = 0;
?>
<div class="row">
    <div class="form_header"><?php echo $this->get_lang('Pages'); ?></div>
</div>
<div id="content-css">
    <table id="table_lp_list" class="data_table">
        <thead
            <tr class="row_odd nodrop nodrag">
                <?php $class =($this->isAllowedToEdit)?'display':'notDisplay'; ?>
                    <th class="<?php echo $class ?>" width="10px"><?php echo $this->get_lang('Enabled'); ?></th>  
                <th width="65%"><?php echo $this->get_lang('Title'); ?></th>
                <th class="<?php echo $class ?>" width="10%"><?php echo $this->get_lang('Edit'); ?></th>
                <th class="<?php echo $class ?>" width="10%"><?php echo $this->get_lang('Delete'); ?></th>
                <th width="10%"><?php echo $this->get_lang('View'); ?></th>
                
            </tr>
        </thead> 
        <tbody class="sort ui-sortable">
            <?php if (!empty($this->pages)): ?>
                <?php 
                    foreach ($this->pages as $page):
                ?>
                    
                       <?php if(($page['enabled'])||($this->isAllowedToEdit)){ ?>
                       <tr>
                         <td align="center" class="<?php echo $class ?>" >
                             <a class="enabled" href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=CourseNode&func=updateEnabled&nodeId='.$page['id'].'&enabled='.(($page['enabled']==0) ? 1 : 0) ?>">
                                <?php echo Display::return_icon('pixel.gif','',array('class'=>$page['enabled'] ? 'actionplaceholdericon actionaccept' : 'actionplaceholdericon actionvalidate_na'), ($page['enabled'] ? get_lang('AnnouncementAvailable') : get_lang('AnnouncementNotAvailable')));; ?>
                             </a>    
                        </td>
                         
                        <td><?php echo $page['title']; ?></td>
                        <td class="<?php echo $class ?>" align="center">
                            <a href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=CourseNode&func=getForm&nodeId='.$page['id'].'&'.api_get_cidReq(); ?>">
                                <?php echo Display::return_icon('pixel.gif', get_lang('Edit'), array('class' => 'actionplaceholdericon actionedit')); ?>
                            </a>
                        </td>
                        <td class="<?php echo $class ?>" align="center">
                            <?php 
                            $link = api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=CourseNode&func=delete&nodeId='.$page['id'].'&'.api_get_cidReq(); 
                            $title = get_lang("ConfirmationDialog");
                            $text = get_lang("ConfirmYourChoice");
                            ?>
                            <!--
                            <a class="courseNode-delete" title="<:?php echo get_lang('AreYouSureToDelete');?>" <:?php echo 'onclick="fAlert(\''.$link.'\');"' ?> href="javascript:void(0);">
                            <a class="courseNode-delete" title="<:?php echo get_lang('AreYouSureToDelete');?>" href="<:?php echo api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=CourseNode&func=delete&nodeId='.$page['id'].'&'.api_get_cidReq(); ?>">
                            -->
                            <a class="courseNode-delete" title="<?php echo get_lang('AreYouSureToDelete');?>" <?php echo 'onclick="Alert_Confim_Delete(\''.$link.'\',\''.$title.'\',\''.$text.'\');"' ?> href="javascript:void(0);">
                                <?php echo Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')); ?>
                            </a>
                        </td>
                        <td  align="center">
                            <a href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=node&cmd=CourseNode&func=view&nodeId='.$page['id'].'&'.api_get_cidReq(); ?>">
                                <?php echo Display::return_icon('pixel.gif', get_lang('View'), array('class' => 'actionplaceholdericon actionpreview')); ?>
                            </a>
                        </td>
                        <!--td></td-->
                       </tr>
                       <?php  }
                       else { 
                           $count++;  
                           if($count == count($this->pages)){?>
                             <tr> 
                                <td colspan="2" align="center"><em><?php echo $this->get_lang('Empty'); ?></em></td>
                             </tr>   <?php
                           }
                       }
                       ?>
                   
                <?php
                    endforeach;
             else: ?>
                <tr> 
                    <td colspan="5" align="center"><em><?php echo $this->get_lang('Empty'); ?></em></td>
                </tr>
            <?php endif; ?>
               
        </tbody>
    </table>
     <input type="hidden" id="cidReq" value="<?php echo urlencode(api_get_cidreq()); ?>" />
     <input type="hidden" id="webPath" value="<?php echo urlencode(api_get_path(WEB_PATH));;?>" />
</div>
