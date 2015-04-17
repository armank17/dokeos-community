<?php
    if (!empty($this->audios)) {
        foreach ($this->audios as $audio) {  
            $extensions = array('mp3', 'ogg', 'ogv');
            $ext = substr(strrchr($audio['path'], '.'), 1);
            if (!in_array(strtolower($ext), $extensions)) {
                continue;
            }     
            $fullpath = $this->documentWebPath.$audio['path'];
?>                
            <div class="mediabig_audio_button four_buttons audio_buttons rounded grey_border float_l">
                <div class="sectioncontent_template">                        
                    <?php echo $this->getAudioVideo($fullpath, $audio['id'], 240, 25); ?>                                                
                        <table width="100%">
                            <tr>
                                <td width="22px">
                                    <a href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=AuthoringAjax&func=deleteAudio&docId='.$audio['id'].'&lpId='.$this->lpId.'&'.api_get_cidreq(); ?>" class="del-audio" title="<?php echo $this->encodingCharset($this->get_lang('AreYouSureToDelete')); ?>"><img src="<?php echo api_get_path(WEB_IMG_PATH).'edit_delete_22.png'; ?>" title="<?php echo $this->get_lang('Delete'); ?>" /></a>
                                </td>
                                <td width="80%">
                                    <a href="<?php echo urlencode($fullpath); ?>" class="load-audio" rel="<?php echo $audio['id']; ?>">                            
                                        <?php echo cut($this->encodingCharset($audio['title']), 18, true); ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="<?php echo urlencode($fullpath); ?>" class="load-audio " rel="<?php echo $audio['id']; ?>">
                                        <?php echo Display::return_icon('down_1.png', get_lang('Download')); ?>
                                    </a>    
                                </td>
                            </tr>
                        </table>
                </div>
            </div>
<?php
        }
    }
?>
<script>
    try {
        $('.load-audio').click(function(e) {
            AuthorModel.loadAudios(e, $(this));
        });
        $('.del-audio').click(function(e){
            AuthorModel.deleteAudio(e, $(this));
        });
    } catch(e) {}
</script>