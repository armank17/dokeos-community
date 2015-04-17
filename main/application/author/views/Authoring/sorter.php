<?php if (!empty($this->lpItems)): ?>
<form action="" name="items_sort" id="items-sort" method="POST">
<div id="GalleryContainer" class="ui-sortable quiz-sorter">   
    <?php
        foreach ($this->lpItems as $item) {
    ?>
    <div id="imageBox-<?php echo $item['id']; ?>" class="imageBox imageBoxThumbnail ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
        <div class="quiz_content_actions">
            <div class="item-block-thumbnail" >
				<p style="white-space: nowrap; overflow: hidden;" ><?php echo cut($this->encodingCharset($item['title']),20); ?></p>
				
				<?php
					$thumbSys = api_get_path(SYS_COURSE_PATH).$this->courseInfo['path'].'/upload/learning_path/thumbnails/'.$this->lpId.'/'.$item['id'].'.jpg';
                    $thumbWeb = api_get_path(WEB_IMG_PATH).'placeholder-image.jpg';
                    if (file_exists($thumbSys)) {
                        $w = $h = '';
                        $thumbWeb = api_get_path(WEB_COURSE_PATH).$this->courseInfo['path'].'/upload/learning_path/thumbnails/'.$this->lpId.'/'.$item['id'].'.jpg';
                    }else{
                        $w = $h = '';
                        $thumbWeb = api_get_path(WEB_COURSE_PATH).$this->courseInfo['path'].'/upload/learning_path/thumbnails/'.$this->lpId.'/'.$item['id'].'.png';
                    }                    
                    $itemimg = '<img src="'.$thumbWeb.'">';
					
				?>
					<div class="item-block-image-container"><?php echo $itemimg;?></div>
				
            </div>
        </div>
        <div>
            <a href="<?php echo api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=Authoring&func=index&'.api_get_cidreq().'&itemType='.$item['item_type'].'&lpId='.$this->lpId.'&lpItemId='.$item['id']; ?>" class="sorter-action" id="edit-<?php echo $item['id']; ?>">
                <img class="actionplaceholdericon actionedit" title="<?php echo $this->get_lang('Edit'); ?>" alt="<?php echo $this->get_lang('Edit'); ?>" src="<?php echo api_get_path(WEB_IMG_PATH).'pixel.gif'; ?>">
            </a>            
            <a href="" class="sorter-action" id="delete-<?php echo $item['id']; ?>" title="<?php echo $this->get_lang('AreYouSureToDelete'); ?>">
                <img class="actionplaceholdericon actiondelete" title="<?php echo $this->get_lang('Delete'); ?>" alt="<?php echo $this->get_lang('Delete'); ?>" src="<?php echo api_get_path(WEB_IMG_PATH).'pixel.gif'; ?>">
            </a>
            <?php if (!empty($item['audio'])): ?>
            <a href="" class="sorter-action" id="deleteaudio-<?php echo $item['id']; ?>" title="<?php echo $this->get_lang('AreYouSureToDeleteAudio'); ?>">
                <img class="actionplaceholdericon actiondeleteaudio" title="<?php echo $this->get_lang('DeleteAudio'); ?>" alt="<?php echo $this->get_lang('DeleteAudio'); ?>" src="<?php echo api_get_path(WEB_IMG_PATH).'pixel.gif'; ?>">
            </a>
            <?php endif; ?>
        </div>    
        <input type="hidden" value="<?php echo $item['id']; ?>" name="itemIds[]" />  
    </div>      
    <?php } ?>
    <input type="hidden" name="lpId" id="lpId" value="<?php echo $this->lpId; ?>" />    
</div>
</form>    
<?php else: ?>
<div id="GalleryContainer"><?php echo $this->get_lang('ThereIsNoItemsInThisModule'); ?></div>
<?php endif; ?>
<input type="hidden" id="courseCode" value="<?php echo urlencode($this->courseCode); ?>" />
<input type="hidden" id="webPath" value="<?php echo urlencode(api_get_path(WEB_PATH)); ?>" />
<input type="hidden" id="func" value="sorter" />

<script src="<?php echo api_get_path(WEB_CODE_PATH); ?>appcore/library/jquery/jquery.nicescroll/jquery.nicescroll.min.js"></script>
<script>
    $(function () {
        var bgcolor = window.parent.$("#header_background").css("background-color");    
        $("html").niceScroll({cursorcolor: bgcolor, cursorwidth:"8px"}); 
    });
</script> 
