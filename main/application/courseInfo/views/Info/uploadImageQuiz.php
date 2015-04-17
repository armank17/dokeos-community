<?php
$path_image = Database::escape_string($_GET['path_img']);
$web_sys_path = api_get_path(WEB_PATH) . $path_image;

$post_id = Database::escape_string($_GET['post_id']);
$side = Database::escape_string($_GET['side']);

$logo_sys_path = api_get_path(SYS_PATH) . $path_image;


$logoimgpath = "";
$logoimgext = $_GET['ext'];
$imglogo = $web_sys_path . $_GET['temp_img'] . $logoimgext;

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
<script type="text/javascript">
    $(function() {
        rWidth = <?php echo $swidth; ?>;
        rHeight = <?php echo $sheight; ?>;
        path_image = '<?php echo $web_sys_path; ?>';
        position = 'top';
        set_width = <?php echo intval($_GET['width']); ?>;
        set_height = <?php echo intval($_GET['height']); ?>;
        ratio = <?php echo $_GET['resize']; ?>;
        if (ratio === false)
            ratio = 0;
        else
            ratio = set_width / set_height;
        minSize = [set_width, set_height];
        reload = <?php echo $_GET['reload']; ?>;
        img_temp = '<?php echo $_GET['temp_img']  . $logoimgext; ?>';
        dest_img = '<?php echo $_GET['dest_img'] ; ?>';
        fullfilename = '<?php echo $_GET['dest_img']. $logoimgext; ?>';

        $('body').css("background-image", "none");
        $('#space').css({'margin-top': (rHeight + 7) + 'px'});
        window.parent.iframe.dialog({
            position: position,
            width: (rWidth + 10),
            height: (rHeight + 60),
            show: {effect: 'fade', duration: 1800},
            close: function(event, ui) {             
               $(window.parent.document.getElementById('<?php echo $side ?>_progress_bar_<?php echo $post_id; ?>')).hide();
                if (reload === true)
                    window.parent.location.reload();
            }
        });
        window.parent.iframe.dialog('open');
        window.parent.iframe.dialog().css({width: (rWidth + 10) + 'px', height: (rHeight + 60) + 'px'});
        
        $('#jcrop_target').Jcrop({
                allowMove: true,
                allowResize: true,
                allowSelect: false,
                onChange: showPreview,
                onSelect: showPreview,
                aspectRatio: ratio,
                minSize: minSize,
                setSelect: [0, 0, set_width, set_height],
                bgColor: 'white',
                bgOpacity: 0.93
            });

        $('#crop').click(function() {
            setCoords();
            $(this).attr("disabled", true);
            $('.ajax-file-upload-statusbar').remove();
            $("#cropForm").ajaxForm({
                type: "POST",
                url: "<?php echo api_get_path(WEB_CODE_PATH) . 'index.php?module=courseInfo&cmd=InfoAjax&func=cropImageUploadQuiz'; ?>",
                success: function(data) {
                    if (reload === false) {
                        $(window.parent.document.getElementById('img_<?php echo $side ?>_image_<?php echo $post_id; ?>')).remove();                        
                        $(window.parent.document.getElementById('<?php echo $side ?>_image_<?php echo $post_id; ?>')).append("<img id='img_<?php echo $side ?>_image_<?php echo $post_id; ?>' src='"+path_image+fullfilename+"' />");                        
                        $(window.parent.document.getElementById('<?php echo $side ?>_<?php echo $post_id; ?>')).val(path_image+fullfilename);                        
                    }
                    window.parent.iframe.dialog("close");
                }
            }).submit();
        });

    });

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
