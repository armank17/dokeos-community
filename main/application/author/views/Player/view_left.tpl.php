<?php
if (!empty($this->currentItem['audio']) && file_exists($this->documentSysPath . $this->currentItem['audio'])) {
    $autoplay = true;
    $audiopath = $this->documentWebPath . $this->currentItem['audio'];
} else {
    $autoplay = false;
    $audiopath = 'audio.mp3';
}
echo $this->getAudio($audiopath, 'item-record', 1, 1, $autoplay, true);
?>
<?php /* <div class="scorm_title"><div class="scorm_title_text"><?php echo $this->encodingCharset($this->lpInfo['name']); ?></div></div> */ ?>
<div class="inner_lp_toc" id="inner_lp_toc">  
    <div id="toc-mode-plan">
        <?php
        if (!empty($this->lpItems)):
            foreach ($this->lpItems as $item):
                //$itemimg = Display::return_icon('template.gif', '', array('style'=>'vertical-align:middle;width:16px;height:16px;'));                    
                $thumbSysJPG = api_get_path(SYS_COURSE_PATH) . $this->courseInfo['path'] . '/upload/learning_path/thumbnails/' . $this->lpId . '/' . $item['id'] . '.png';
                $thumbSysPNG = api_get_path(SYS_COURSE_PATH) . $this->courseInfo['path'] . '/upload/learning_path/thumbnails/' . $this->lpId . '/' . $item['id'] . '.jpg';
                $thumbWebJPG = api_get_path(WEB_COURSE_PATH) . $this->courseInfo['path'] . '/upload/learning_path/thumbnails/' . $this->lpId . '/' . $item['id'] . '.png';
                ;
                $thumbWebPNG = api_get_path(WEB_COURSE_PATH) . $this->courseInfo['path'] . '/upload/learning_path/thumbnails/' . $this->lpId . '/' . $item['id'] . '.jpg';
                ;
                $w = 'width="184px"';
                $h = 'height="100px"';
                if (!file_exists($thumbSysPNG)) {
                    if (!file_exists($thumbSysJPG)) {
                        $thumb = api_get_path(WEB_IMG_PATH) . 'placeholder-image.jpg';
                    } else {
                        $thumb = $thumbWebJPG;
                    }
                } else {
                    $thumb = $thumbWebPNG;
                }

                $itemimg = '<img class="toc-item-image" src="' . $thumb . '" style="">';
                if ($item['item_type'] == 'quiz') {
                    if (file_exists($thumbSysJPG) || file_exists($thumbSysPNG)) {
                        $itemimg = '<img class="toc-item-image" src="' . $thumb . '" style="">';
                    } else {
                        $itemimg = Display::return_icon('quiz_48.png', '', array('style' => '', 'class' => 'toc-item-quiz'));
                    }
                }
                ?>              
                <div  style="padding-left:0px; padding-top:15px !important;    padding-bottom:15px !important;" class="scorm_item<?php /* echo $item['id'] */ ?> <?php echo $item['id'] == $this->itemId ? 'scorm_item_highlight' : ''; ?>" id="toc_<?php echo $item['id'] ?>">
                    <a name="atoc_<?php echo $item['id'] ?>"></a>
                    <div class="">                                
                        <div class="toc-item">

                            <p class="toc-item-left">
                                <span><?php echo $item['display_order']; ?></span>
                                <img width="22" height="22" alt="" src="<?php echo $this->getIconStatusUrl($item['view']['status']); ?>" id="toc_img_<?php echo $item['id'] ?>">
                            </p>
                            <a href="<?php echo api_get_path(WEB_CODE_PATH) . 'index.php?module=author&cmd=PlayerAjax&func=switchItem&' . api_get_cidreq() . '&lpId=' . $this->lpId . '&lpItemId=' . $item['id'] . '&currentItemId=' . $this->currentItem['id'] . '&title=' . $item['title']; ?>" id="lp-toc-<?php echo $item['id'] ?>" class="lp-tocs">
                                <div style="" class="toc-item-image-container">
                                    <?php echo $itemimg; ?>
                                </div>    
                            </a> 





                        </div>

                    </div>
                </div>
                <?php
            endforeach;
        endif;
        ?>
    </div>
</div>
<input type="hidden" id="current-item-id" value="<?php echo $this->currentItem['id']; ?>" />
<input type="hidden" id="lp-id" value="<?php echo $this->lpId; ?>" />
<script src="<?php echo api_get_path(WEB_CODE_PATH); ?>application/author/assets/js/functions.js" type="text/javascript" language="javascript"></script>
<script src="<?php echo api_get_path(WEB_CODE_PATH); ?>application/author/assets/js/view_left.js" type="text/javascript" language="javascript"></script>
<script src="<?php echo api_get_path(WEB_CODE_PATH); ?>newscorm/js/jquery.iframe-auto-height.js" type="text/javascript" language="javascript"></script>
<script src="<?php echo api_get_path(WEB_LIBRARY_PATH); ?>javascript/jquery.highlight.js" type="text/javascript" language="javascript"></script>
<script src="<?php echo api_get_path(WEB_LIBRARY_PATH); ?>javascript/glosary_author.js" type="text/javascript" language="javascript"></script>
<input id="isLastSlide" type="hidden" value="<?php echo $item['display_order']; ?>" />
<input id="activityId" type="hidden" value="<?php echo $this->activityId ?>" />
