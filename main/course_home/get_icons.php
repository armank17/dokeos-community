<?php
/* For licensing terms, see /dokeos_license.txt */
/**
 * Responses to AJAX calls
 */

$language_file[] = 'course_home';

require_once '../../main/inc/global.inc.php';
require_once api_get_path(LIBRARY_PATH).'document.lib.php';
require_once api_get_path(LIBRARY_PATH).'fileUpload.lib.php';

echo '<script type="text/javascript">                        
						
$(function() {
	
	$("#upload_image").click(function(){
		$("#hid_submit").trigger("click");
		
		setTimeout(function() {   //calls click event after a certain time
		   $("#close_upload").delay(1000).trigger("click");
		}, 1500);
		return false;
});
	
});
</script>';

$rowIndex = $_GET['rowIndex'];
$colIndex = $_GET['colIndex'];

$course_info = api_get_course_info(api_get_course_id());
$course_code = $course_info['id'];
$icons_path = api_get_path(SYS_COURSE_PATH).$course_code.'/document/icons/';
if(!is_dir($icons_path)){

	$perm = api_get_setting('permissions_for_new_directories');
	$perm = octdec(!empty($perm)?$perm:'0770');
	mkdir($icons_path);
    chmod($icons_path, $perm);

	$dir = "icons/";
	$dh  = opendir($dir);
	while (false !== ($filename = readdir($dh))) {
		if($filename === '.' || $filename === '..') {continue;} 
		
		if(copy("icons/".$filename,$icons_path.$filename)) {
			//echo 'file copied--';
		}
	}
}
//$dir = "icons/";
$dh  = opendir($icons_path);
while (false !== ($filename = readdir($dh))) {
	if($filename === '.' || $filename === '..') {continue;}     

	$pos = strpos($filename, "_grey");
	if ($pos === false) {
		$files[] = $filename;
	}	
}
$file_count = sizeof($files);
$counter_check = $file_count + 1;
natsort($files);
$i = 0;
$m = 0;

echo '<div id="upload_form_div" style="display:none;"><div align="right" id="close_upload"><a href="#"><img style="vertical-align:middle;" src="../img/close_button.png" />'.get_lang('Close').'</a></div><form id="upload_form" enctype="multipart/form-data" method="post" action="upload.php">
                    <div>
                        <div><label for="image_file">'.api_convert_encoding(get_lang("PleaseSelectImageFile"),"UTF-8",api_get_system_encoding()).'</label></div>
                        <div><input type="file" name="image_file" id="image_file" onchange="fileSelected();" /></div>
                    </div></br>
                    <div>
						<button type="submit" class="savenew" id="upload_image" name="Upload">'.get_lang("Upload").'</button><br><br>
						<input type="button" value="Upload" style="display:none;" id="hid_submit" onclick="startUploading()" />
                        
                    </div></br></br>                
                    <div id="error">You should select valid image files only!</div>
                    <div id="error2">An error occurred while uploading the file</div>
                    <div id="abort">The upload has been canceled by the user or the browser dropped the connection</div>
                    <div id="warnsize">Your file is very big. We cant accept it. Please select more small file</div>

                    <div id="progress_info">
                        <div id="progress"></div>
                        <div id="progress_percent">&nbsp;</div>
                        <div class="clear_both"></div>
                        <div>
                            <div id="speed" style="display:none;">&nbsp;</div>
                            <div id="remaining" style="display:none;">&nbsp;</div>
                            <div id="b_transfered" style="display:none;">&nbsp;</div>
                            <div class="clear_both"></div>
                        </div>
                        <div id="upload_response" style="display:none;"></div>
                    </div>
                </form></div>';
echo '<input type="hidden" name="rowIndex" id="rowIndex" value="'.$rowIndex.'">';
echo '<input type="hidden" name="colIndex" id="colIndex" value="'.$colIndex.'">';
echo '<button type="submit" class="savenew" id="add_gallery_icons" name="Upload_gallery">'.api_convert_encoding(get_lang("UploadImages"),'UTF-8',api_get_system_encoding()).'</button><br><br>';
echo '<table id="icon_content" class="icon_table">';
//while($row = Database::fetch_array($result)){
foreach($files as $filename) {
	
	//$full_path = api_get_path(WEB_PATH).'main/course_home/icons/'.$filename;
	$full_path = api_get_path(WEB_COURSE_PATH).$course_code.'/document/icons/'.$filename;

	if($i == 0 || ($i%4) == 0) {

	echo '<tr>';
		/*if($m == 0){
			echo '<td><div id="addicon_block"><img style="display: block; margin: 0 auto;vertical-align:middle;text-align:center;" src="images/add.png" /><br><div style="text-align:center;">'.get_lang("AddIcon").'</div></div></td>';
			$m++;
			$i++;
		}*/
	}
	//if($m <> 0){
	echo '<td><div class="inner_icon_block"><div class="icon_display"><img style="display: block; margin: 0 auto;vertical-align:middle;text-align:center;" src="'.$full_path.'" /></div><div align="right" class="icon_delete_class" id="'.$filename.'"><img  src="images/form-minus.png" /></div></div></td>';
	$i++;
	$m++;
	//}

	/*if($m == $counter_check){
		if($i == 0 || ($i%4) == 0) {
			echo '<tr>';
		}
		echo '<td><div id="addicon_block"><img style="display: block; margin: 0 auto;vertical-align:middle;text-align:center;" src="images/add.png" /><br><div style="text-align:center;">'.get_lang("AddIcon").'</div></div></td>';
	}*/	
	
	if($i%4 == 0) {
		$i=0;
	echo '</tr>';
	}
	
}
echo '</table>';