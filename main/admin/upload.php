<?php
// including the global Dokeos file
require ('../inc/global.inc.php');

$access_url_id = api_get_current_access_url_id();
if ($access_url_id < 0){
    $access_url_id = 1;
} 

// including additional libraries
require_once (api_get_path(LIBRARY_PATH).'SimpleImage.lib.php');
// Upload image in logo folder
$uploaddir = '../../home/logo/';
// file extension
$ext = extension($_FILES['userfile']['name']);
list($width, $height, $type, $attr) = getimagesize($_FILES['userfile']['tmp_name']);

$logo_sys_path = api_get_path(SYS_PATH).'home/logo/';
// Create dir if not exists
if (!is_dir($logo_sys_path)) {
    mkdir($logo_sys_path);
    $perm = api_get_setting('permissions_for_new_directories');
    $perm = octdec(!empty($perm)?$perm:'0770');
    chmod ($logo_sys_path,$perm);
    file_put_contents($logo_sys_path.'index.html', "Empty file");
}
$html_file = $logo_sys_path.'index.html';
if (!is_file($html_file)) {
    file_put_contents($logo_sys_path.'index.html', "Empty file");
}

// Delete file if exists
$files = glob(api_get_path(SYS_PATH).'home/logo/logo-dokeos-'.$access_url_id.'-*');
if(count($files) < 1 && !$_configuration['multiple_access_urls']){
    $files = glob(api_get_path(SYS_PATH) . 'home/logo/' . '*');
}
if (count($files)>0){
    foreach ($files as $path_file) {
          $infoFile = pathinfo($path_file);
          if($infoFile['extension'] != 'html'){
             unlink($path_file);
          }
   }
}

// File name
$uploadfile = $uploaddir . 'logo-dokeos-'.$access_url_id.'-'.time().'.'.$ext;
// Move file to selected path
umask(0);
if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
try {
    if ($width >= 250) {
	$img = new SimpleImage();
	// Resize
	$img->load($uploadfile)->resize(200, 50)->save($uploadfile);
	// Crop
	//$img->load($uploadfile)->crop(160, 110, 460, 360)->save($uploadfile);
    }
  echo "success";
	
} catch(Exception $e) {
	echo '<span style="color: red;">' . $e->getMessage() . '</span>';	
}
} else {
  echo "error";
}

    function extension ($file) {
       $file = strtolower($file) ;
       $extension = split("[/\\.]", $file) ;
       $n = count($extension)-1;
       $extension = $extension[$n];
       return $extension;
} 

?>