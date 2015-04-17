<?php
require_once '../../main/inc/global.inc.php';

$course_info = api_get_course_info(api_get_course_id());
$course_code = $course_info['id'];
$course_directory = $course_info['path'];
$icons_path = api_get_path(SYS_COURSE_PATH).$course_directory.'/document/icons/';
$icons_thumbnail_path = api_get_path(SYS_COURSE_PATH).$course_directory.'/document/icons/thumbnail/';

$dh  = opendir($icons_path);
while (false !== ($filename = readdir($dh))) {
	if($filename === '.' || $filename === '..' || is_dir($icons_path.$filename)) {continue;}     

	$pos = strpos($filename, "_grey");
	if ($pos === false) {
		$files[] = $filename;
	}	
}
$file_count = sizeof($files);
$counter_check = $file_count + 1;
natsort($files);

echo '<script>
$(function () {
	var theme_color = $("#theme_color").val();		
	var image_div_height = $(".scroll-pane1").prop("scrollHeight");
	
	 $(".scroll-pane1").jScrollPane(
		{
		 verticalDragMinHeight: 100,
		 verticalDragMaxHeight: 100				 
		}
	 );
	 var pane = $(".scroll-pane1");
	 var api = pane.data("jsp");
	 //api.scrollBy(100,image_div_height);

	 $(".jspDrag").css("background","#"+theme_color);   
});
</script>';
echo '<div class="scroll-pane1">';
foreach($files as $filename) {
	$filename = api_convert_encoding($filename,'UTF-8',api_get_system_encoding());	
	if(substr($filename,0,6) == 'dokeos'){
		$css_margin = "margin-top:15px;";
		$title_margin = "";
	}
	else {
		$css_margin = '';
		$title_margin = "margin-top:15px;";
	}
	$thumbnailSrc = api_get_path(WEB_COURSE_PATH).$course_directory.'/document/icons/thumbnail/'.$filename; 
?>
	<div class="mediabig_button four_buttons_div rounded grey_border float_l" style="margin: 10px;">
	<div style="padding: 5px; " class="sectioncontent_template"> 
			<div class="images-thumb_new" style="<?php echo $css_margin; ?>">
				<a href="#" title="<?php echo $filename; ?>" class="icon_display" id="<?php echo $thumbnailSrc; ?>">
					<img border="0" title="<?php echo $filename; ?>" src="<?php echo $thumbnailSrc; ?>" id="<?php echo $filename; ?>" />
				</a>
			</div>
			<div class="images-bottom">                            
				<div class="images-title" style="<?php echo $title_margin; ?>">
					<a href="<?php echo $href; ?>" title="<?php echo $filename; ?>" class="load-image">
						<?php echo $filename; ?>
					</a>
				</div>
				<div class="images-delete" style="<?php echo $title_margin; ?>">
					<a class="icon_delete_class" id="<?php echo $filename; ?>" title="<?php echo get_lang('AreYouSureToDelete'); ?>" href="#"><img src="<?php echo api_get_path(WEB_IMG_PATH).'edit_delete_22.png'; ?>" title="<?php echo get_lang('Delete'); ?>" /></a>
				</div>
			</div>                   
	</div>
</div>

<?php	
}
echo '</div>';

?>