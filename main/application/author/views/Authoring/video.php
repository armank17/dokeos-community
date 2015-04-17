<?php 
    if ($this->displayGallery) {
        echo '<div id="video-gallery">';
        echo '<div class="breadcrumb">'.$this->breadcrumb.'</div>';        
        if (!empty($this->videos)) {
            foreach ($this->videos as $video) {
                $folder_root = '/main/upload/webtv/thumbs/' . str_replace('http://', '', api_get_path(WEB_PATH)) . $video['file'] . '.png';
                $href = $video['url'];
?>
                <div class="mediabig_video_button four_buttons video_buttons rounded grey_border float_l">
                    <div class="sectioncontent_template">
                            <div class="video-picture">
                                <img src="<?php echo api_get_path(WEB_CODE_PATH).'webtv/thumbnail.php?file='.$video['file'].'.png'; ?>" />                                                                     
                            </div>
                            <table width="100%">
                                <tr>
                                    <td width="80%">
                                        <a href="<?php echo $href; ?>" id="<?php echo $folder_root; ?>" class="load-video" title="<?php echo $video['file']; ?>">                            
                                            <?php echo $this->encodingCharset($video['title']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="<?php echo $href; ?>" id="<?php echo $folder_root; ?>" class="load-video" title="<?php echo $video['file']; ?>">
                                            <?php echo Display::return_icon('down_1.png'); ?>
                                        </a>    
                                    </td>
                                </tr>
                            </table>
                    </div>
                </div>
<?php
            }
        }
        echo '</div>';
?>        
<script src="<?php echo api_get_path(WEB_CODE_PATH); ?>appcore/library/jquery/jquery.upload/js/jquery.min.js"></script>
<script src="<?php echo api_get_path(WEB_CODE_PATH); ?>appcore/library/jquery/jquery.nicescroll/jquery.nicescroll.min.js"></script>
<script>
    $(function () {
        var bgcolor = window.parent.$("#header_background").css("background-color");    
        $("html").niceScroll({cursorcolor: bgcolor, cursorwidth:"8px"}); 
    });
</script> 


<?php        
    }
    else if ($this->displayUploadForm) {
        echo '<div class="breadcrumb">'.$this->breadcrumb.'</div><br />';     
?>   
    <form enctype="multipart/form-data" id="upload-social" class="social_form" name="upload_social" method="POST" action="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=AuthoringAjax&func=uploadVideo&'.api_get_cidreq(); ?>">
        <table>
            <tr><td colspan="2"><?php echo $this->get_lang('Url'); ?></td></tr>
            <tr>
                <td><input type="text" name="myurl" id="myurl" /></td>
                <td><button id="btn-upload-social" class="save" name="submit" type="button"><?php echo $this->get_lang('Ok'); ?></button></td>
            </tr>
        </table>
    </form>  
    <div id="social-instructions">
        <ul>
            <li><?php echo $this->get_lang('SocialInstruction1'); ?></li>
            <li><?php echo $this->get_lang('SocialInstruction2'); ?></li>
            <li><?php echo $this->get_lang('SocialInstruction3'); ?></li>
        </ul>
    </div>
    <div id="social-preview">
            <img border="0" src="<?php echo api_get_path(WEB_LIBRARY_PATH); ?>ckeditor/plugins/videoplayer/dialogs/icons/youtube64.png"><br />
            <img border="0" src="<?php echo api_get_path(WEB_LIBRARY_PATH); ?>ckeditor/plugins/videoplayer/dialogs/icons/vimeo64.png">&nbsp;
    </div>
<?php        
    }   
    else {
?>        
    <ul class="mediabox-list">
        <li>
            <div class="ml-top">
                <a class="big_button  rounded grey_border upload_doc_button reopen-dialog" href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=Authoring&func=video&'.api_get_cidreq().'&lpId='.$this->lpId.'&category=social&width=550&height=370&refresh=false'; ?>" title="<?php echo $this->get_lang("YoutubeAndVimeo"); ?>"><?php echo $this->get_lang('YoutubeAndVimeo'); ?></a>
            </div>
        </li>
        <li>
            <div class="ml-top">
                <a class="big_button  rounded grey_border create_video_button reopen-dialog" href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=Authoring&func=video&'.api_get_cidreq().'&lpId='.$this->lpId.'&category=streaming&width=840&height=600&refresh=false'; ?>" title="<?php echo $this->get_lang("Video"); ?>"><?php echo $this->get_lang('PickFromWebTv'); ?></a>
            </div>
        </li>
    </ul>
<?php 
    }
    

