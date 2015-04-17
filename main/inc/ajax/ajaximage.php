<?php
/* For licensing terms, see /dokeos_license.txt */
// including the global Dokeos file
require_once  '../global.inc.php';
global $_course;
global $_configuration;

$course_image_syspath = api_get_path(SYS_COURSE_PATH).$_course['path'].'/course_image.png';


if(isset($_POST)) {
    $action = Security::remove_XSS($_GET['action']);
    $access_url_id = intval(api_get_current_access_url_id());
    if ($access_url_id < 0){
        $access_url_id = 1;
    }   
    require_once (api_get_path(LIBRARY_PATH).'SimpleImage.lib.php');
    
    
    switch($action){        
        case 'uploadlogohome':            
            $logo_sys_path = api_get_path(SYS_PATH).'home/logo/';
            $namefile      = 'userfile';   
            $name_img      = 'logo-dokeos-'.$access_url_id.'-';
            $set_width     = 200;
            $set_height    = 50;
            $ext           = extension($_FILES[$namefile]['name']);
            $time          = time();
            $uploadfile = $logo_sys_path . $name_img . $time.'.'.$ext;
        break;                          
        case 'deletelogohome':
            if ($_configuration['multiple_access_urls'] == true) { //multi-site actived
                $files = glob(api_get_path(SYS_PATH) . 'home/logo/logo_home_site_' . $access_url_id . '*');
            } else {
                $files = glob(api_get_path(SYS_PATH) . 'home/logo/logo_home' . '*');
            }
            
            //$files = glob(api_get_path(SYS_PATH).'home/logo/logo-dokeos-'.$access_url_id.'-*');
            //if(count($files) < 1 && !$_configuration['multiple_access_urls']){
            //    $files = glob(api_get_path(SYS_PATH) . 'home/logo/' . '*');
            //}
            
            if (count($files) > 0) {
                foreach ($files as $path_file) {
                    $infoFile = pathinfo($path_file);
                    if ($infoFile['extension'] != 'html') {
                        unlink($path_file);
                    }
                }
            }
        break;
        case 'uploadlogocourse':
            global $_course;
            $logo_sys_path = api_get_path(SYS_COURSE_PATH).$_course['path'].'/';
            $namefile = 'photoimg';   
            $name_img = 'course_image';
            $set_width = 185;
            $set_height = 140;
            $ext = extension($_FILES[$namefile]['name']);
            $time=  time();
            $uploadfile = $logo_sys_path . $name_img . $time.'.'.$ext;
            
        break;
        case 'uploadlogocourseEx':
            global $_course;
            $logo_sys_path = api_get_path(SYS_COURSE_PATH).$_course['path'].'/';
            $namefile = 'photoimg';   
            $name_img = 'course_logo';
            $set_width = 500;
            $set_height = 375;
            $ext  = extension($_FILES[$namefile]['name']);
            $time = time();
            $uploadfile = $logo_sys_path . $name_img . '.'.$ext;
            //$uploadfile = $logo_sys_path . $name_img . $time.'.'.$ext;
            
        break;
        
        case 'croplogocourse':
            global $_course;
            $logo_sys_path = api_get_path(SYS_COURSE_PATH).$_course['path'].'/';
            
            $targ_w = 150; 
            $targ_h = 150;
			$name_img = 'course_logo';

			$src = $logo_sys_path . $name_img . '.'."png";
            //$img_r = imagecreatefrompng($src);
			//$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );

			//imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],$targ_w,$targ_h,$_POST['w'],$_POST['h']);
            
            //imagepng($dst_r, $src );
            
            
            $im_dest = imagecreatetruecolor (500, 300);
			imagealphablending($im_dest, false);

			imagecopyresampled($im_dest, $src, 0, 0, $_POST['x'], $_POST['y'], $targ_w, $targ_h, $_POST['w'], $_POST['h']);

			imagesavealpha($im_dest, true);
			imagepng($im_re, $src);            
        break;
        
    
    }
       
    
    list($width, $height, $type, $attr) = getimagesize($_FILES[$namefile]['tmp_name']);

    if($action == 'uploadlogohome'){
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
    }


    umask(0);
    $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
    if (in_array(strtolower($ext), $allowed_types)) {
        
        if($action == 'uploadlogohome') {
            array_map('unlink', glob($logo_sys_path.'logo-dokeos-'. $access_url_id .'-*'));
        } else {
            $files = glob($logo_sys_path.'*');
            if(count($files) > 1) {
                foreach($files as $path_file) {
                    $infoFile = pathinfo($path_file);
                    if($infoFile['extension'] != 'html' AND  $infoFile['extension'] != 'php'){
                        unlink($path_file);
                    }
                }
            }
        }
        
        if (move_uploaded_file($_FILES[$namefile]['tmp_name'], $uploadfile)) {
            //if ($width >= 250) {
                $img = new SimpleImage();                    
                $img->load($uploadfile);

                if($action != 'uploadlogohome') {
                        $maxSize = $set_height;

                        $ratio_orig = $img->get_width() / $img->get_height();

                        $width = $maxSize;
                        $height = $maxSize;

                        if ($ratio_orig < 1) {
                            $width = $height * $ratio_orig;
                        } else {
                            $height = $width / $ratio_orig;
                        }

                } else {
                        $width = $set_width;
                        $height = $set_height;
                }

                $img->resize($width, $height,$action)->save($uploadfile);                    
            //}

            
            $files = glob($logo_sys_path.'logo-dokeos-'.$access_url_id.'-*');
            if(count($files) < 1 && !$_configuration['multiple_access_urls']){
                $files = glob($logo_sys_path . '*');
            }    

            if (count($files) > 0) {
                foreach ($files as $path_file) {
                    $new_file_path = pathinfo($path_file);
                    if ($new_file_path['extension'] == 'gif' || $new_file_path['extension'] == 'png' || $new_file_path['extension'] == 'jpg' || $new_file_path['extension'] == 'jpeg') {
                        if($action!='uploadlogocourseEx')
                                echo $new_file_path['basename'];    
                        else
                                echo json_encode(array(
                                                    'src'    => $new_file_path['basename'],
                                                    'width'  => $width,
                                                    'height' => $height	
                                                ));	
                    }
                }
            }
        }
    } else {
        if (count(glob($logo_sys_path . '*')) > 1) {
            foreach (glob($logo_sys_path . '*') as $path_file) {
                $new_file_path = pathinfo($path_file);
                if ($new_file_path['extension'] == 'gif' || $new_file_path['extension'] == 'png' || $new_file_path['extension'] == 'jpg' || $new_file_path['extension'] == 'jpeg') {
                    $imglogo  = $new_file_path['basename'];
                }
            }
        }
        echo $imglogo;
    }

}              
                
function extension($file){
    $file = strtolower($file) ;
    $extension = split("[/\\.]", $file) ;
    $n = count($extension)-1;
    $extension = $extension[$n];
    return $extension;
}
                
?>
