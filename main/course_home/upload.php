<?php
require_once '../../main/inc/global.inc.php';
require_once api_get_path(LIBRARY_PATH).'document.lib.php';
require_once api_get_path(LIBRARY_PATH).'fileUpload.lib.php';

function bytesToSize1024($bytes, $precision = 2) {
    $unit = array('B','KB','MB');
    return @round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))), $precision).' '.$unit[$i];
}

$sFileName = $_FILES['image_file']['name'];
$sFileType = $_FILES['image_file']['type'];
$sFileSize = bytesToSize1024($_FILES['image_file']['size'], 1);

$course_info = api_get_course_info(api_get_course_id());
$course_code = $course_info['id'];

//$temp_path = api_get_path(SYS_PATH).'main/course_home/icons/';
$temp_path = api_get_path(SYS_COURSE_PATH).$course_code.'/document/icons/';

if (move_uploaded_file($_FILES['image_file']['tmp_name'], $temp_path.replace_dangerous_char($_FILES['image_file']['name']))) {	

	$slide_pic = replace_dangerous_char($_FILES['image_file']['name']);
	//$slide_pic = $_FILES['image_file']['name'];
    $slide_tmp = $_FILES['image_file']['tmp_name'];

	
	// Set the dimmensions what will have the image
		$width_slide = 100;
		$height_slide = 100;
		$updir = $temp_path;

		// Use the function for resize the image
		api_resize_images_propotionally($updir,$slide_tmp,$slide_pic,$width_slide,$height_slide);
		//api_resize_images($updir,$slide_tmp,$slide_pic,$width_slide,$height_slide);


		$path_parts = pathinfo($slide_pic);		
		$new_filename = $path_parts['filename'].'_grey.'.$path_parts['extension'];

		//copy($temp_path.$slide_pic, $temp_path.$new_filename);

		api_change_image_color($updir,$slide_tmp,$slide_pic,$width_slide,$height_slide);
}