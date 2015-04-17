<?php
require_once '../../main/inc/global.inc.php';
require_once api_get_path(LIBRARY_PATH).'document.lib.php';
require_once api_get_path(LIBRARY_PATH).'fileUpload.lib.php';

$course_info = api_get_course_info(api_get_course_id());
$course_code = $course_info['id'];
$course_directory = $course_info['path'];

$temp_sys_path = api_get_path(SYS_COURSE_PATH).'/document/icons/';
$temp_web_path = api_get_path(WEB_COURSE_PATH).'/document/icons/';

$temp_sys_thumbnail_path = api_get_path(SYS_COURSE_PATH).'/document/icons/thumbnail/';
$temp_web_thumbnail_path = api_get_path(WEB_COURSE_PATH).'/document/icons/thumbnail/';

$move_temp_sys_path = api_get_path(SYS_COURSE_PATH).$course_directory.'/document/icons/';
$move_temp_web_path = api_get_path(WEB_COURSE_PATH).$course_directory.'/document/icons/';

$move_temp_sys_thumbnail_path = api_get_path(SYS_COURSE_PATH).$course_directory.'/document/icons/thumbnail/';
$move_temp_web_thumbnail_path = api_get_path(WEB_COURSE_PATH).$course_directory.'/document/icons/thumbnail/';

$origin_sFileName = $_GET['filename'];
$sFileName = clean_filename($origin_sFileName);
$path_parts = pathinfo($sFileName);		
$old_grey_name = $path_parts['filename'].'_grey.png';

if(copy($temp_sys_path.$sFileName,$move_temp_sys_path.$sFileName)) {
		//echo 'file copied';
	}
if(copy($temp_sys_thumbnail_path.$sFileName,$move_temp_sys_thumbnail_path.$sFileName)) {
		//echo 'file copied';
	}

if(copy($temp_sys_thumbnail_path.$sFileName,$move_temp_sys_thumbnail_path.$old_grey_name)) {
		//echo 'file copied';
	}

$new_name = 'dokeos_'.$path_parts['filename'].'.'.$path_parts['extension'];
$new_grey_name = 'dokeos_'.$path_parts['filename'].'_grey.png';

rename($move_temp_sys_path.$sFileName, $move_temp_sys_path.$new_name);

//api_change_image_color($move_temp_sys_thumbnail_path,$sFileName);

/*$path_parts = pathinfo($sFileName);		
echo 'rey11===='.$new_grey_name = $path_parts['filename'].'_grey.'.$path_parts['extension'];
$path_parts = pathinfo($origin_sFileName);		
echo 'grey22===='.$neworigin_grey_name = $path_parts['filename'].'_grey.'.$path_parts['extension'];*/

rename($move_temp_sys_thumbnail_path.$sFileName, $move_temp_sys_thumbnail_path.$new_name);
rename($move_temp_sys_thumbnail_path.$old_grey_name, $move_temp_sys_thumbnail_path.$new_grey_name);

$info = pathinfo($new_name);    
$ext=$info['extension'];

$dest = imagecreatetruecolor(120, 80);

if(!strcmp("jpg",$ext))
$src = imagecreatefromjpeg($move_temp_sys_thumbnail_path.$new_name);

if(!strcmp("png",$ext))
$src = imagecreatefrompng($move_temp_sys_thumbnail_path.$new_name);

$imgw = imageSX($src);
$imgh = imageSY($src);

if($imgw < 120){
	$dest_w = (120 - $imgw) / 2;
}
if($imgh < 90){
	$dest_h = (90 - $imgh) / 2;
}

$rgb = imagecolorat($src, 0, 0);
$r = ($rgb >> 16) & 0xFF;
$g = ($rgb >> 8) & 0xFF;
$b = $rgb & 0xFF;
if($r <> 0 && $g <> 0 && $b <> 0){
	//$transparent = imagecolorallocate( $dest, $r, $g, $b );
	$transparent = imagecolorallocatealpha( $dest, $r, $g, $b, 80);
}
else {
	//$transparent = imagecolorallocate( $dest, 255, 255, 255); 
	if(!strcmp("png",$ext)){
	$transparent = imagecolorallocatealpha( $dest, 255, 255, 255, 80); 
	imagecolortransparent($dest,$transparent);
	}
	else {
	$transparent = imagecolorallocatealpha( $dest, 255, 255, 255, 80); 
	}
}
imagefill( $dest, 0, 0, $transparent ); 

imagecopyresampled($dest, $src, $dest_w, $dest_h, 0, 0, $imgw, $imgh, $imgw, $imgh);

if(!strcmp("jpg",$ext))
imagejpeg($dest,$move_temp_sys_thumbnail_path.$new_name); 

if(!strcmp("png",$ext))
imagepng($dest,$move_temp_sys_thumbnail_path.$new_name); 

imagedestroy($dest); 
imagedestroy($src); 

if(!strcmp("jpg",$ext))
$image_src = imagecreatefromjpeg($move_temp_sys_thumbnail_path.$new_name);

if(!strcmp("png",$ext))
$image_src = imagecreatefrompng($move_temp_sys_thumbnail_path.$new_name);

imagefilter($image_src,IMG_FILTER_GRAYSCALE);

//$new_name_grey = 'dokeos_'.$path_parts['filename'].'_grey1.png';
imagepng($image_src,$move_temp_sys_thumbnail_path.$new_grey_name); 
imagedestroy($image_src); 

$dh  = opendir($move_temp_sys_path);
while (false !== ($filename = readdir($dh))) {
	if($filename === '.' || $filename === '..' || is_dir($move_temp_sys_path.$filename)) {continue;}     

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
	 api.scrollBy(100,image_div_height);

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
function clean_filename($string) {        
        $string = trim($string);
        $string = str_replace(array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'), array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'), $string);
        $string = str_replace(array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'), array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'), $string);
        $string = str_replace(array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'), array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'), $string);
        $string = str_replace(array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'), array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'), $string);
        $string = str_replace(array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'), array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'), $string);
        $string = str_replace(array('ñ', 'Ñ', 'ç', 'Ç', 'æ'), array('n', 'N', 'c', 'C','ae',), $string);
        //This part is responsible for eliminating any extraneous characters
        $string = str_replace(array("\\", "?", "º", "-", "~","#", "@", "|", "!", "\"","·", "$", "%", "&", "/","(", ")", "?", "'", "¡","¿", "[", "^", "`", "]","+", "}", "{", "?", "?",">", "< ", ";", ",", ":"," "), '', $string);
        return $string;        
}
?>