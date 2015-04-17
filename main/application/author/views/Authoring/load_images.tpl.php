<?php
    if (!empty($this->images)) {
        foreach ($this->images as $image) {  
            //$extensions = array('png', 'jpg', 'gif', 'jpeg');
            //$ext = substr(strrchr($image['path'], '.'), 1);
            //if (!in_array(strtolower($ext), $extensions)) {
            //    continue;
            //}                    
            //$sizes = getimagesize($this->documentSysPath.$image['path']);
            //$w = $sizes[0];
            //$h = $sizes[1];
            //if ($w > 150) { $w = 150; }
            //if ($h > 125) { $h = 125; }    
            $href = '/courses/'.$this->courseInfo['path'].'/document'.$image['path'];
            $thumbnailSrc = api_get_path(WEB_COURSE_PATH).$this->courseInfo['path'].'/document'.$image['path'];                    
            if (file_exists(api_get_path(SYS_COURSE_PATH).$this->courseInfo['path'].'/document/images/thumbnail/'.basename($image['path']))) {
                $thumbnailSrc = api_get_path(WEB_COURSE_PATH).$this->courseInfo['path'].'/document/images/thumbnail/'.basename($image['path']);
                //$w = $h = '';
            }                    
?>                
            <div class="mediabig_button four_buttons rounded grey_border float_l">
                <div style="padding: 5px; " class="sectioncontent_template"> 
                        <div class="images-thumb">
                            <a class="load-image" title="<?php echo $this->encodingCharset($image['title']); ?>" href="<?php echo $href; ?>" >
                                <img style="max-width:150px;max-height:130px;" border="0" title="<?php echo $this->encodingCharset($image['title']); ?>" src="<?php echo $thumbnailSrc; ?>" id="avatar-<?php echo $image['id']; ?>" />
                            </a>
                        </div>
                        <div class="images-bottom">                            
                            <div class="images-title">
                                <a class="load-image" title="<?php echo $this->encodingCharset($image['title']); ?>" href="<?php echo $href; ?>" >
                                    <?php echo $this->encodingCharset($image['title']); ?>
                                </a>
                            </div>
                            <div class="images-delete">
                                <a class="del-image" title="<?php echo $this->encodingCharset($this->get_lang('AreYouSureToDelete')); ?>" href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=AuthoringAjax&func=deleteImage&category='.$this->imageCategory.'&docId='.$image['id'].'&lpId='.$this->lpId.'&'.api_get_cidreq(); ?>">
                                    <img src="<?php echo api_get_path(WEB_IMG_PATH).'edit_delete_22.png'; ?>" title="<?php echo $this->get_lang('Delete'); ?>" /></a>
                            </div>
                        </div>                    
                </div>
            </div>
<?php
        }
    }
?>
<script>
try {
    $('.load-image').click(function(e) {
        AuthorModel.loadImages(e, $(this));
    });
    $('.del-image').click(function(e) {
        AuthorModel.deleteImage(e, $(this));
    });
}
catch(e) {}
</script>