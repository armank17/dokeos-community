<?php
require_once('../inc/global.inc.php');

$_course_path = $_POST['coursepath'];
$path_upload_xls = api_get_path(SYS_PATH).'courses/'.$_course_path.'/document/'; 
require_once(api_get_path(LIBRARY_PATH) . 'excel_reader2.php');
$allowed = array('xls');
        $extension = pathinfo($_FILES['fileToUpload']['name'], PATHINFO_EXTENSION);
        if(in_array(strtolower($extension), $allowed)){
            move_uploaded_file( $_FILES["fileToUpload"]["tmp_name"], $path_upload_xls . $_FILES['fileToUpload']['name']);
	}else{
            unset($_FILES["images"]["error"]);
            echo 'Is not XLS File.';exit;
        }                 
$xls_path = $path_upload_xls.$_FILES['fileToUpload']['name'];
chmod($xls_path,'0777');
$data = new Spreadsheet_Excel_Reader($xls_path);
echo $data->dump(false,false);