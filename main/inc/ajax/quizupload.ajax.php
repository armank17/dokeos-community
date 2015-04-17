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
        case 'matching':
            
            $classname = $_GET['classname'];             
            $values1 = explode("-", $classname);            
            $logo_sys_path = api_get_path(SYS_COURSE_PATH).$_course['path'].'/document/images/';
            $logo_web_path = api_get_path(WEB_COURSE_PATH).$_course['path'].'/document/images/';
            
            $namefile      = $values1[0];   
            $name_img      = $namefile.'-'.$access_url_id.'-';
            $set_width     = 200;
            $set_height    = 60;
            $ext           = extension($_FILES[$namefile]['name'][$values1[1]]);
            
            $time          = time();
            $uploadfile = $logo_sys_path . $name_img . $time.'.'.$ext;
            $filename1 =  $logo_web_path . $name_img . $time.'.'.$ext;
                        
            
            
        break;

    }
       
    
    list($width, $height, $type, $attr) = getimagesize($_FILES[$namefile]['tmp_name'][$values1[1]]);

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
//            if (count(glob($logo_sys_path.'*'))>1){
//                foreach (glob($logo_sys_path.'*') as $path_file){        
//                    $infoFile = pathinfo($path_file);
//                    if($infoFile['extension'] != 'html' AND  $infoFile['extension'] != 'php'){
//                        unlink($path_file);
//                    }
//                }
//            }
            if (move_uploaded_file($_FILES[$namefile]['tmp_name'][$values1[1]], $uploadfile)) {
                //if ($width >= 250) {
                    $img = new SimpleImage();                    
                    $img->load($uploadfile)->resize($set_width, $set_height)->save($uploadfile);      
                    echo $filename1;
                //}


//                if (count(glob($logo_sys_path . '*')) > 1) {
//                    foreach (glob($logo_sys_path . '*') as $path_file){
//                        $new_file_path = pathinfo($path_file);
//                        if ($new_file_path['extension'] == 'gif' || $new_file_path['extension'] == 'png' || $new_file_path['extension'] == 'jpg' || $new_file_path['extension'] == 'jpeg') {
//                            echo $new_file_path['basename'];    
//                        }
//                    }
//                }
            }
        }else{                

//            if (count(glob($logo_sys_path . '*')) > 1) {
//                foreach (glob($logo_sys_path . '*') as $path_file) {
//                    $new_file_path = pathinfo($path_file);
//                    if ($new_file_path['extension'] == 'gif' || $new_file_path['extension'] == 'png' || $new_file_path['extension'] == 'jpg' || $new_file_path['extension'] == 'jpeg') {
//                        $imglogo  = $new_file_path['basename'];
//                    }
//                }
//            }
//            echo $imglogo;
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
