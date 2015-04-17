<div id="notebook-content">
    <div id="notebook-content-left">
		<div id="notebook-content-leftinner">	
			<table width="100%" class="data_table">
        <?php         
            if (!empty($noteList)) {
                $i = 0;
               $addPageToUrl = "";
               if (isset($_GET['page'])) {
                   $addPageToUrl = '&page='.Security::remove_XSS($_GET['page']); 
               }
                foreach ($noteList as $notebookId => $note) {                   
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
		</div>
        <div class="pager" align="right"><?php echo $pagerLinks; ?></div>    
    </div>
    
    <div id="notebook-content-right">
        <?php  echo $notebookForm->display(); ?>
    </div>
    
</div>

