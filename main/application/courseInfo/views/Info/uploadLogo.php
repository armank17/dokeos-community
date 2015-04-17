<?php
$path_image = Database::escape_string($_GET['path_img']);
$web_sys_path = api_get_path(WEB_PATH) . $path_image;
$logoimgext = $_GET['ext'];
$imglogo = $web_sys_path . $_GET['temp_img'] . '_' . api_get_user_id() . $logoimgext;
list($swidth, $sheight) = getimagesize($imglogo);
?>

<div style="width: 1px; height:1px;">
    <div style="width: 1px; height:1px;float: left;">
        <img src="<?php echo $imglogo . "?t=" . time(); ?>" id="jcrop_target" >
    </div>
</div>
<div id="space" style="height:30px; margin-top:382px;">
    <button class="save" style="float: right;" id="crop"><?php echo get_lang('Crop'); ?></button>
</div>
<form id="cropForm">
    <input name="x" id="x" type="hidden">
    <input name="y" id="y" type="hidden">
    <input name="w" id="w" type="hidden">
    <input name="h" id="h" type="hidden">
    <input name="ext" id="ext" type="hidden" value="<?php echo $logoimgext; ?>">
    <input name="path_image" type="hidden" value="<?php echo $_GET['path_img'] . $_GET['temp_img']; ?>">
    <input name="dest_image" type="hidden" value="<?php echo $_GET['path_img'] . $_GET['dest_img']; ?>">
    <input name="resize" type="hidden" value="<?php echo $_GET['resize']; ?>">
    <input name="to_width" type="hidden" value="<?php echo intval($_GET['width']); ?>">
    <input name="to_height" type="hidden" value="<?php echo intval($_GET['height']); ?>">
    <input name="remove" type="hidden" value="<?php echo $_GET['reload']; ?>">
    <input name="folder" id="folder" type="hidden" value="<?php echo $_GET['folder']; ?>">
</form>

<script>
    $('body').css("background", "white");
    var rWidth = <?php echo $swidth; ?>;
    var rHeight = <?php echo $sheight; ?>;
    var path_image = '<?php echo $web_sys_path; ?>';
    var position = 'top';
    var set_width = <?php echo intval($_GET['width']); ?>;
    var set_height = <?php echo intval($_GET['height']); ?>;
    var ratio = <?php echo $_GET['resize']; ?>;
    var minSize = [set_width, set_height];
    var reload = <?php echo $_GET['reload']; ?>;
    var img_temp = '<?php echo $_GET['temp_img'] . '_' . api_get_user_id() . $logoimgext; ?>';
    
    $(document).ready(function() {
        if (ratio === false)
            ratio = 0;
        else
            ratio = set_width / set_height;
        
        $('#space').css({'margin-top': (rHeight + 7) + 'px'});
        window.parent.iframe.dialog({
            position: position,
            width: (rWidth + 10),
            height: (rHeight + 60),
            show: {effect: 'fade', duration: 1800},
            close: function(event, ui) {
                if (reload === true)
                    window.parent.location.reload();
            }
        });
        window.parent.iframe.dialog('open');
        window.parent.iframe.dialog().css({width: (rWidth + 10) + 'px', height: (rHeight + 60) + 'px'});

        $('#crop').click(function() {
            setCoords();
            $(this).attr("disabled", true);
            $("#cropForm").ajaxForm({
                type: "POST",
                url: "<?php echo api_get_path(WEB_CODE_PATH) . 'index.php?module=courseInfo&cmd=InfoAjax&func=cropImageUpload'; ?>",
                success: function(data) {
                    if (reload === false) {
                        $(window.parent.document.getElementById('picture_name')).val(img_temp);
                        $(window.parent.document.getElementById('imgPreviewNice')).css('display', 'block');
                        $(window.parent.document.getElementById('imgPreviewNice')).attr('src', path_image + img_temp + '?t=' + new Date().getTime());
                    }
                    window.parent.iframe.dialog("close");
                }
            }).submit();
        });
        
        setTimeout('setAreaCrop()', 1000);
    });
    
    function setAreaCrop() {
        $('#jcrop_target').Jcrop({
            allowSelect: false,
            onSelect: showPreview,
            onChange: showPreview,
            aspectRatio: ratio,
            setSelect: [0, 0, set_width, set_height],
            minSize: minSize,
            bgColor: 'white',
            bgOpacity: 0.93
        });
    }

    function showPreview(coords) {
        $('#x').val(coords.x);
        $('#y').val(coords.y);
        $('#w').val(coords.w);
        $('#h').val(coords.h);
    }
    
    function setCoords() {
        if (parseInt($('#x').val()) < 0)
            $('#x').val('0');
        if (parseInt($('#y').val()) < 0)
            $('#y').val('0');
    }

</script>
