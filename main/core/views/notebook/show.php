<div id="notebook-content">
    <div id="notebook-content-left">
		<div id="notebook-content-leftinner">	
			<table width="100%" class="data_table">
        <?php         
            if (count($notebookList) > 0) {
               $addPageToUrl = "";
               if (isset($_GET['page'])) {
                   $addPageToUrl = '&page='.Security::remove_XSS($_GET['page']); 
               }
                $i = 0;
                foreach ($notebookList as $notebookId => $note) {                   
                   echo '<tr class="'.($i%2==0?'row_odd':'row_even').'">
                            <td valign="middle" width="75%">
                                <a href="'.api_get_path(WEB_VIEW_PATH).'notebook/index.php?'.api_get_cidreq().'&amp;action=show&amp;id='.$notebookId.$addPageToUrl.'">'.$note->title.'</a>
                            </td>
                            <td valign="middle" align="right" style="color:#999999;">
                                '.$note->notesdate.'
                            </td>
                         </tr>';
                   $i++;
                }                
            } 
            else {
                echo '<tr><td>'.get_lang('YouHaveNotPersonalNotesHere').'</td></tr>';
            }
        ?>
        </table>
         <div class="pager" align="right"><?php echo $pagerLinks; ?></div>      
	</div>
    </div>
    <div id="notebook-content-right" style="padding-left: 30px;">
                <div align="center" class="quiz_content_actions"><?php echo $notebook_tittle; ?></div>
                <div align="center" style="overflow:auto;text-align:left;" class="quiz_content_actions glossary_description_height">
                    <?php echo $notebook_comment; ?>
                </div>
                    <?php if (api_is_allowed_to_edit()) { ?>
                <div align="right" style="border:none;" class="quiz_content_actions">
                    <a href="<?php echo api_get_self().'?'.  api_get_cidreq().'&amp;id='.$notebook_id.'&amp;action=edit'.$addPageToUrl; ?>">
                        <?php echo Display::return_icon('pixel.gif', get_lang('Edit'), array('class' => 'actionplaceholdericon actionedit')); ?>&nbsp;&nbsp;
                    </a>
                    <a <?php echo 'onclick="javascript:if(!confirm('."'".addslashes(api_htmlentities(get_lang("ConfirmYourChoice"),ENT_QUOTES,$charset))."'".')) return false;"'; ?> href="<?php echo api_get_self().'?'.  api_get_cidreq().'&amp;id='.$notebook_id.'&amp;action=delete'.$addPageToUrl; ?>">
                        <?php echo Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')); ?>
                    </a>
                </div>
                     <?php } ?>
</div>
</div>