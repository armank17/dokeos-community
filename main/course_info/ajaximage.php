<?php
/* For licensing terms, see /dokeos_license.txt */
// including the global Dokeos file
require_once  '../inc/global.inc.php';
global $_course;

$course_image_syspath = api_get_path(SYS_COURSE_PATH).$_course['path'].'/course_image.png';


if(isset($_POST))
{
    $file = $_FILES['photoimg']['name'];
    $source_file = $_FILES['photoimg']['tmp_name'];
    if ($_FILES['photoimg']['error'] == UPLOAD_ERR_OK) {
		$checkupload = update_course_image($file, $source_file);
        if($checkupload){
              echo $file;  
        }
    }   
}
                
                
function update_course_image($file, $source_file) {
    global $_course;
    // Validation
    $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
    $file = str_replace('\\', '/', $file);
    $filename = (($pos = strrpos($file, '/')) !== false) ? substr($file, $pos + 1) : $file;
    list($txt, $extension) = explode(".", $filename);
    if (!in_array(strtolower($extension), $allowed_types)) {
        return false;
    }
    $path = api_get_path(SYS_COURSE_PATH).$_course['path'].'/';
    $filename = 'course_image.png';
    $picture_info = @getimagesize($source_file);
    $type = $picture_info[2];
    $ok = false;
    $detected = array(1 => 'GIF', 2 => 'JPG', 3 => 'PNG');
    if (in_array($type, array_keys($detected))) {
    $ok = imageToPng($source_file, 22, $path.'small_'.$filename)
            && imageToPng($source_file, 85, $path.'medium_'.$filename)
            && imageToPng($source_file, 180, $path.$filename);
    }
    return true;
}
                
?>
