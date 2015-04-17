<?php
require_once '../inc/global.inc.php';

// only for admin account
api_protect_admin_script();

$tbl_courses = Database::get_main_table(TABLE_MAIN_COURSE);
$rs_course = Database::query("SELECT code, db_name, directory FROM $tbl_courses");
$affected_rows = 0;

$sys_course_path = api_get_path(SYS_COURSE_PATH);
$perm = api_get_setting('permissions_for_new_directories');
$perm = octdec(!empty($perm)?$perm:'0770');
$perm_file = api_get_setting('permissions_for_new_files');
$perm_file = octdec(!empty($perm_file)?$perm_file:'0660');

if (Database::num_rows($rs_course) > 0) {    
    while ($row_course = Database::fetch_object($rs_course)) {         
        
        $course_documents_folder_images = $sys_course_path.$row_course->directory.'/document/images/thumbnail/';
        
        if(!is_dir($course_documents_folder_images)){
           mkdir($course_documents_folder_images,$perm);
           echo 'creando carpeta thumbnail  para:  '. $row_course->directory.'<br>';
        }else{
           echo 'existe la carpeta thumbnail en :'. $row_course->directory.'<br>';
        }
        
        $img_code_path = api_get_path(SYS_CODE_PATH)."default_course_document/images/thumbnail/";
        
        $files=array();
        $files = browse_folders($img_code_path,$files,'thumbnail');
       
        foreach($files as $key => $value){                
                if($value["file"]!=""){
                     
                        copy($img_code_path.$value["file"],$course_documents_folder_images.$value["file"]);
                        chmod($course_documents_folder_images.$value["file"],$perm_file);                        
                }
        }        
    }    
}
echo '<p>'.$affected_rows.' files updated in course</p>';


function browse_folders($path, $files, $media){
    if($media=='thumbnail'){       
        $code_path = api_get_path(SYS_CODE_PATH)."default_course_document/images/thumbnail/";      
    }
    if(is_dir($path)){
        
        $handle = opendir($path);
        while (false !== ($file = readdir($handle))){
            if(is_dir($path.$file) && strpos($file,'.')!==0){

                $files[]["dir"] = str_replace($code_path,"",$path.$file."/");
                $files = browse_folders($path.$file."/",$files,$media);
            }elseif(is_file($path.$file) && strpos($file,'.')!==0){

                $files[]["file"] = str_replace($code_path,"",$path.$file);  
            }
        }
    }
    return $files;
    
}
