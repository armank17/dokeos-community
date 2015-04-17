<input type="hidden" name="device_type" id="device_type" value="<?php echo $this->deviceType ?>"/>
<div id="message-content"></div>

<?php
$tablet = 'width:770px;';
$div_left = '<div class="toogle-slide-left  player-actions-left" style="display:block;overflow-y:hidden;height:700px;left:5px;position:absolute;width:11.5%">
        <div id="player-view-middle-left" style="margin-left:8px;width:11.5%">
        <div id="lp-layout" style="width:85px; padding-top:5px; margin-left:10px;">';
$div_right = '';
$div_left_toogle = '<a class="ssOpenG arrow_left" style="position:fixed;height:20px;top:50%;" href="#"></a>';
$div_right_toogle = '<a class="ssOpenGR arrow_right" style="position:fixed;height:20px;top:50%;right:0;" href="#"></a>';
if ($this->deviceType == "0") { //only PC
    $tablet = "width:768px;";
    $div_left_toogle = '';
    $div_right_toogle = '';
    $div_left = '<div class="toogle-slide-left"><a class="ssOpenG arrow_left" style="position:absolute;height:700px;margin-left:-10px;" href="#"></a></div>
        <div class="toogle-slide-left  player-actions-left" style="display:block;overflow-y:hidden;height:700px;left:20px;position:absolute;width:10.2%">
        <div id="player-view-middle-left" style="margin-left:8px;width:9%">
        <div id="lp-layout" style="width:85px;padding-top:5px;margin-left:-6px;">';
    $div_right = '<div class="toogle-slide-right" style="height:700px;"><a class="ssOpenGR arrow_right" style="position:absolute;height:700px;right:0;" href="#"></a></div>';
}
echo $div_left;
?>
            <div style="width: 80px; margin-bottom:11px; height:52px; line-height:52px;">
                <a id="author-next-ext" class="boxRadius" href="">
                    <div class="sectioncontent_template">+
                    </div>
                </a>
            </div>
            <?php
            if (!empty($this->lpItems)):
                foreach ($this->lpItems as $lpItem):
                    $imageWebJPG = api_get_path(WEB_COURSE_PATH) . $this->courseInfo['path'] . '/upload/learning_path/thumbnails/' . $this->lpId . '/' . $lpItem['id'] . '.jpg?' . time();
                    $imageWebPNG = api_get_path(WEB_COURSE_PATH) . $this->courseInfo['path'] . '/upload/learning_path/thumbnails/' . $this->lpId . '/' . $lpItem['id'] . '.png?' . time();
                    $imageSysPNG = api_get_path(SYS_COURSE_PATH) . $this->courseInfo['path'] . '/upload/learning_path/thumbnails/' . $this->lpId . '/' . $lpItem['id'] . '.png';
                    $imageSysJPG = api_get_path(SYS_COURSE_PATH) . $this->courseInfo['path'] . '/upload/learning_path/thumbnails/' . $this->lpId . '/' . $lpItem['id'] . '.jpg';
                    if (!file_exists($imageSysPNG)) {
                        if (!file_exists($imageSysJPG)) {
                            $image = api_get_path(WEB_IMG_PATH) . 'placeholder-image-thumb.png';
                        } else {
                            $image = $imageWebJPG;
                        }
                    } else {
                        $image = $imageWebPNG;
                    }
                    ?>
                    <div class=" <?php echo ($this->itemId == $lpItem['id'] ? "section_highlight" : ""); ?>" style="width: 80px; margin-bottom:12px;">
                        <a  href="<?php echo api_get_path(WEB_CODE_PATH) . 'index.php?module=author&cmd=Authoring&func=index&' . api_get_cidreq() . '&lpId=' . $this->lpId . '&lpItemId=' . $lpItem['id']; ?>" >
                            <div class="boxRadiusThumb sectioncontent_template ">
                                <img style="width:90%; padding:5px; height:40px;" border="0" src="<?php echo $image; ?>" />
                            </div>
                        </a>
                    </div>
                    <?php
                endforeach;
            endif;

            if (empty($this->lpItems) || (isset($_GET['action']) AND $_GET['action'] == 'next' )) {
                $image = api_get_path(WEB_IMG_PATH) . 'placeholder-image-thumb.png';
                ?>
                <div class="section_highlight" style="width: 80px; margin-bottom:12px;">
                    <a  href="" >
                        <div class="boxRadiusThumb sectioncontent_template ">
                            <img style="width:90%; padding:5px; height:40px;" border="0" src="<?php echo $image; ?>" />
                        </div>
                    </a>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
    <?php echo $div_left_toogle; ?>
</div>
<script>
    $(function() {
        var bgcolor = $("#header_background").css("background-color");
        $(".toogle-slide-left").niceScroll({cursorcolor: bgcolor, cursorwidth: "8px", horizrailenabled: false});
                    $(".toogle-slide-right").niceScroll({cursorcolor: bgcolor, cursorwidth: "8px", horizrailenabled: false});
            });
</script> 

<div id="player-view-middle-center" class="" style="float:left;margin-left:115px;<?php echo $tablet ?>">
    <div id="player-view-content">
        <form id="authoring-form" name="authoring_form" accept-charset="utf-8" action="<?php echo api_get_path(WEB_CODE_PATH) . 'index.php?module=author&cmd=AuthoringAjax&func=saveItem&' . api_get_cidreq();
        $_SESSION["notime"] = 'notime'; ?>" method="POST">
            <div id="authoring-top">
                <div id="message-box">
                    <span id="confirmation-message"></span>
                </div>
            </div>
            <?php
            if ($this->showIframe):
                ?>
                <iframe src="<?php echo $this->itemUrl; ?>" width="100%" id="authoring-iframe" frameBorder="0"></iframe>  
                <?php
            else:
                api_disp_html_area('authoring_editor', $this->editorValue, '', '', null, $this->editorConfig);
            endif;
            ?>
            <input type="hidden" id="item-title" name="item_title" value="<?php echo $this->encodingCharset($this->itemTitle); ?>" />
            <input type="hidden" id="item-type" name="item_type" value="<?php echo (!empty($this->itemType) ? $this->itemType : 'document'); ?>" />
            <input type="hidden" id="item-ref" name="item_ref" value="<?php echo $this->itemRef; ?>" />
            <input type="hidden" id="item-path" name="item_path" value="<?php echo $this->itemPath; ?>" />
            <input type="hidden" id="item-id" name="item_id" value="<?php echo $this->itemId; ?>" />
            <input type="hidden" id="item-lp-id" name="item_lp_id" value="<?php echo $this->lpId; ?>" /> 
            <input type="hidden" id="item-audio" name="item_audio" value="<?php echo $this->itemAudio; ?>" /> 
            <input type="hidden" id="item-func" name="item_func" value="<?php echo $this->itemAudio; ?>" /> 
            <input type="hidden" id="btn-action" name="btn_action" value="submit" />        

        </form>
        <input type="hidden" id="courseCode" value="<?php echo urlencode($this->courseCode); ?>" />
        <input type="hidden" id="webPath" value="<?php echo urlencode(api_get_path(WEB_PATH)); ?>" />
        <input type="hidden" id="layout" value=0 />
    </div>
</div>

<div class="toogle-slide-right  player-actions-right" style="display:block;height:698px; overflow-y:hidden; width:10.5%; right:17px;">
    <div id="player-view-middle-right" style="height: 698px;">
        <div id="lp-layout" style="margin-right:0px; "> 
            <div class="section" style="background:#ffffff !important; width: 70px; float:left; margin-right:0px; margin-bottom:7px; background:none !important">
                <a href="<?php echo api_get_path(WEB_CODE_PATH) . 'index.php?module=author&cmd=Authoring&func=index&' . api_get_cidreq() . '&itemType=document&tplId=0&ressource=document&lpId=' . $this->lpId . $this->extraParams; ?>" class="embed-tpl">
                    <div class="sectioncontent_template">
                        <img style="width: 100%; height: 40px;padding-top:4px;" border="0" src="<?php echo api_get_path(WEB_PATH) . 'home/default_platform_document/template_thumb/empty.gif'; ?>" />
                    </div>
                </a>
            </div>
            <?php
            if (!empty($this->templates)):
                foreach ($this->templates as $template):
                    ?>
                    <div class="section" style="background:#ffffff !important; width: 70px; float:left; margin-right:0px; margin-bottom:7px;">
                        <a href="<?php echo api_get_path(WEB_CODE_PATH) . 'index.php?module=author&cmd=Authoring&func=index&' . api_get_cidreq() . '&itemType=document&tplId=' . $template['id'] . '&ressource=document&lpId=' . $this->lpId . $this->extraParams; ?>" class="embed-tpl">
                            <div class="sectioncontent_template" style="height: 43px;">
                                <img style="width: 100%; height: 40px;padding-top:4px;" border="0" src="<?php echo api_get_path(WEB_PATH) . 'home/default_platform_document/template_thumb/' . $template['image']; ?>" />
                            </div>
                        </a>
                    </div>
                    <?php
                endforeach;
            endif;
            ?>
            <div class="clear"></div>
        </div>
    </div>
    <?php echo $div_right_toogle; ?>
</div>
<?php echo $div_right; ?>
