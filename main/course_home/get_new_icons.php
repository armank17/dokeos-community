<?php
require_once '../../main/inc/global.inc.php';
require_once '../../main/appcore/library/jquery/jquery.upload/server/php/UploadAjaxHandler.php';

$rowIndex = $_GET['rowIndex'];
$colIndex = $_GET['colIndex'];
$theme_color = $_GET['theme_color'];

$course_info = api_get_course_info(api_get_course_id());

?>

<link rel="stylesheet" href="<?php echo api_get_path(WEB_CODE_PATH); ?>appcore/library/jquery/jquery.upload/css/jquery.fileupload-ui.css">
<style type="text/css" id="page-css">
	/* Styles specific to this particular page */
	.scroll-pane1
	{
		width: auto;
		height: 420px;
		overflow: auto;
	}
	.horizontal-only
	{
		height: auto;
		max-height: 420px;
	}
	.jspTrack
	{
		background: #DCDCDC;
		border:1px solid #DCDCDC;
		position: relative;
	}
	.jspDrag
	{
		background: #009933;
		position: relative;
		top: 0;
		left: 0;
		cursor: pointer;
	}
	.jspVerticalBar
	{
		position: absolute;
		top: 0;
		right: 0;
		width: 20px;
		height: 100%;
		background: red;
	}
</style>
<input type="hidden" name="rowIndex" id="rowIndex" value="<?php echo $rowIndex; ?>">
<input type="hidden" name="colIndex" id="colIndex" value="<?php echo $colIndex; ?>">
<input type="hidden" name="theme_color" id="theme_color" value="<?php echo $theme_color; ?>">
<div id="image-gallery">
 
        <div id="image-gallery-upload">
            <span class="btn btn-success fileinput-button">
                <i class="icon-plus icon-white"></i>
                <span><?php echo get_lang("AddImages"); ?>...</span>
                <!-- The file input field used as target for the file upload widget -->
                <input id="fileupload" type="file" name="files[]" multiple title="<?php echo get_lang("AddImages"); ?>" >
            </span>
            <br /><br />
            <!-- The global progress bar -->			
            <div id="progress" class="progress progress-success progress-striped" style="visibility:hidden;">
                <div class="bar"></div>
            </div>			
        </div>
        <div id="images" class="images"><div class="scroll-pane1">	
            
			</div>
        </div>
    </div>
	<div id="icon_dialog_box" style="display: none;"><br><br><table border="0" align="center" width="95%"><tr><td align="center"><img src="../img/dokeos_question.png"></td><td><?php echo api_convert_encoding(get_lang("AreYouSureToDelete"),'UTF-8',api_get_system_encoding()); ?></td></tr></table></div>

<script>
        $(function () {
             'use strict';
             // Change this to the location of your server-side upload handler:
             var url = '<?php echo api_get_path(WEB_CODE_PATH).'course_home/upload_new.php?'.api_get_cidreq(); ?>';     
			 var urlImages = '<?php echo api_get_path(WEB_CODE_PATH).'course_home/images_tpl.php?'.api_get_cidreq(); ?>';
             $('#fileupload').fileupload({

                 url: url,
                 dataType: 'json',
                 acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
                 disableImageResize: /Android(?!.*Chrome)|Opera/
                    .test(window.navigator.userAgent),
                 previewMaxWidth: 100,
                 previewMaxHeight: 100,
                 previewCrop: true,                 
                 done: function (e, data) {
					 var filess= data.files[0];
					 var filename = filess.name;

                     $(".progress").css("visibility", "hidden");  

					 $.ajax({
					  type: "GET",
					  url: "list_images.php?filename="+filename,
					  success: function(data){	
							$("#images").html("");
							$("#images").html(data);
					  }
				  });
                     
                 },
                 progressall: function (e, data) {
                     $(".progress").css("visibility", "visible");
                     var progress = parseInt(data.loaded / data.total * 100, 10);
                     $('#progress .bar').css(
                         'width',
                         progress + '%'
                     );
                 },
                 fail: function(e, data) {
                     $.each(data.result.files, function (index, file) {
                        var error = $('<span/>').text(file.error);
                        $(data.context.children()[index])
                            .append('<br>')
                            .append(error);
                    });
                 }
             }).prop('disabled', !$.support.fileInput)
                 .parent().addClass($.support.fileInput ? undefined : 'disabled');

			 //var bgcolor = window.parent.$("#header_background").css("background-color");  
             //$("#images").niceScroll({cursorcolor: bgcolor, cursorwidth:"8px"});   

			 $.get(urlImages, function(data){
				$("#images").html(data);
			 });

			 $('.scroll-pane1').jScrollPane(
				{
				 verticalDragMinHeight: 100,
				 verticalDragMaxHeight: 100				 
				}
			 );
			 $(".jspDrag").css("background","<?php echo '#'.$theme_color; ?>");   
         });
    </script>