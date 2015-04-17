<?php
api_set_crop_token();
$id_name = api_get_crop_token();

if ($this->displayGallery) {
    ?>
    <div id="image-gallery">
        <div class="breadcrumb"><?php echo $this->breadcrumb; ?></div>  
        <div id="image-gallery-upload">
            <span class="btn btn-success fileinput-button" style="margin-bottom: 10px" title="<?php echo $this->lblImageUpload; ?>" >
                <i class="icon-plus icon-white"></i>
                <span><?php echo $this->lblImageUpload; ?>...</span>
                <form id="fu" style="margin: auto">
                    <input id="picture_author" type="file" name="picture" accept="image/jpeg, image/png, image/gif" style="opacity:0; top:0; filter:alpha(opacity=0);" >
                    <input type="hidden" name="folder" value="<?php echo $this->imageCategory; ?>">
                </form>
            </span>
            <div id='progress_author' style="height:30px; margin-bottom:10px; border-radius:5px; text-align:center; line-height:28px; font-weight:bold; font-size:14px;"></div>
        </div>
        <div id="images" class="images" style="height: 100%;">
            <?php echo $this->setTemplate('load_images', 'Authoring'); ?>
        </div>
    </div>

    <script src="<?php echo api_get_path(WEB_CODE_PATH) . 'application/courseInfo/assets/js/infoModel.js' ?>"></script>
    <script src="<?php echo api_get_path(WEB_CODE_PATH); ?>appcore/library/jquery/jquery.nicescroll/jquery.nicescroll.min.js"></script>
    <script src="<?php echo api_get_path(WEB_CODE_PATH); ?>application/author/assets/js/authorModel.js"></script>
    <script src="<?php echo api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery.form.js'; ?>" language="javascript"></script>
    <script src="<?php echo api_get_path(WEB_CODE_PATH); ?>appcore/library/jquery/jquery.upload/js/vendor/jquery.ui.widget.js"></script>
    <script src="<?php echo api_get_path(WEB_CODE_PATH); ?>appcore/library/jquery/jquery.upload/js/jquery.iframe-transport.js"></script>
    <script src="<?php echo api_get_path(WEB_CODE_PATH); ?>appcore/library/jquery/jquery.upload/js/jquery.fileupload.js"></script>
    <link rel="stylesheet" href="<?php echo api_get_path(WEB_CODE_PATH) . 'course_home/css/home.css'; ?>">
    <script>
        $(document).ready(function() {
            var path_img = '<?php echo $this->imageCategory; ?>';
            var folder = '';
            var dest_name_file = '';
            $('#progress_author').hide();
            switch (path_img) {
                case 'images':
                    path_img = '<?php echo "courses/" . api_get_course_path() . "/document/images/author/"; ?>';
                    folder = '/images/author/';
                    dest_name_file = '<?php echo 'Image_' . $id_name; ?>';
                    break;
                case 'avatars':
                    path_img = '<?php echo "courses/" . api_get_course_path() . "/document/mascot/author/"; ?>';
                    folder = '/mascot/author/';
                    dest_name_file = '<?php echo 'Avatar_' . $id_name; ?>';
                    break;
                case 'diagrams':
                    path_img = '<?php echo "courses/" . api_get_course_path() . "/document/images/diagrams/author/"; ?>';
                    folder = '/images/diagrams/author/';
                    dest_name_file = '<?php echo 'Diagram_' . $id_name; ?>';
                    break;
                case 'mindmaps':
                    path_img = '<?php echo "courses/" . api_get_course_path() . "/document/mindmaps/author/"; ?>';
                    folder = '/mindmaps/author/';
                    dest_name_file = '<?php echo 'Mindmap_' . $id_name; ?>';
                    break;
            }
            var url = "<?php echo api_get_path(WEB_CODE_PATH) . 'index.php?module=courseInfo&cmd=InfoAjax&func=uploadImageCrop'; ?>";
            $('#picture_author').fileupload({
                url: url,
                dropZone: $(this),
                formData: {
                    path: path_img,
                    name: 'tempo_pictureauthor',
                    min_width: 150,
                    min_height: 130,
                    max_width: 600,
                    max_height: 600
                },
                done: function(e, data) {
                    var ext = data.files[0].name;
                    ext = (ext.substring(ext.lastIndexOf('.'))).toLowerCase();
                    InfoModel.showActionDialogCrop(path_img, 150, 130, true, false, 'tempo_pictureauthor', dest_name_file, ext, folder);
                    $('#progress_author').hide();
                },
                progress: function(e, data) {
                    $('#progress_author').show();
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    $('#progress_author').css({width : progress + '%', background : 'skyblue'});
                    $('#progress_author').html(progress + '%');
                }
        });
        });
    </script>
    <?php
} else {
    ?>
    <ul class="mediabox-list images-category">
        <li>
            <div class="ml-top">
                <a class="big_button  rounded grey_border create_photo_button reopen-dialog" href="<?php echo api_get_path(WEB_CODE_PATH) . 'index.php?module=author&cmd=Authoring&func=image&' . api_get_cidreq() . '&lpId=' . $this->lpId . '&category=images&width=700&height=600&refresh=false'; ?>" title="<?php echo $this->get_lang("Photos"); ?>"><?php echo $this->get_lang('Photos'); ?></a>
            </div>
        </li>
        <li>
            <div class="ml-top">
                <a class="big_button  rounded grey_border create_mascot_button reopen-dialog" href="<?php echo api_get_path(WEB_CODE_PATH) . 'index.php?module=author&cmd=Authoring&func=image&' . api_get_cidreq() . '&lpId=' . $this->lpId . '&category=avatars&width=700&height=600&refresh=false'; ?>" title="<?php echo $this->get_lang("Avatars"); ?>"><?php echo $this->get_lang('Avatars'); ?></a>
            </div>
        </li>        
        <li>
            <div class="ml-top">
                <a class="big_button  rounded grey_border create_audio_button reopen-dialog" href="<?php echo api_get_path(WEB_CODE_PATH) . 'index.php?module=author&cmd=Authoring&func=image&' . api_get_cidreq() . '&lpId=' . $this->lpId . '&category=diagrams&width=700&height=600&refresh=false'; ?>" title="<?php echo $this->get_lang("Diagrams"); ?>"><?php echo $this->get_lang('Diagrams'); ?></a>
            </div>
        </li>
        <li>
            <div class="ml-top">
                <a class="big_button  rounded grey_border create_mindmap_button reopen-dialog" href="<?php echo api_get_path(WEB_CODE_PATH) . 'index.php?module=author&cmd=Authoring&func=image&' . api_get_cidreq() . '&lpId=' . $this->lpId . '&category=mindmaps&width=700&height=600&refresh=false'; ?>" title="<?php echo $this->get_lang("Mindmaps"); ?>"><?php echo $this->get_lang('Mindmaps'); ?></a>
            </div>
        </li>
    </ul>
<?php } ?>
