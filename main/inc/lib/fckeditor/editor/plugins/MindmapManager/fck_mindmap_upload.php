<?php
// name of the language file that needs to be included
$language_file = array('resourcelinker','document');
include('../../../../../../inc/global.inc.php');

require_once(api_get_path(LIBRARY_PATH).'mindmapConverter.php');
require_once api_get_path(LIBRARY_PATH).'/document.lib.php';
require_once api_get_path(LIBRARY_PATH).'/fileUpload.lib.php';

if(!empty($_FILES['NewFile']))
{

	$filename = replace_dangerous_char($_FILES['NewFile']['name'], 'strict');
	$destPath = api_get_path(SYS_COURSE_PATH).$_course['path'].'/document/mindmaps/';
	$destPath .= pathinfo($filename, PATHINFO_FILENAME).'.png';
	
	if(filter_extension($filename)){
	
		$extension = pathinfo($filename, PATHINFO_EXTENSION);
		
		if($extension == 'xmind'){		
			
			$tmpPath = api_get_path(SYS_COURSE_PATH).$_course['path'].DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR.$filename;
			move_uploaded_file($_FILES['NewFile']['tmp_name'], $tmpPath);
			
			$converter = new MindmapConverter('127.0.0.1',1984);
			$converter->setDeleteSourceAfterConversion(true);
			$converter->setInsertInDocumentTool(true);
			
			$converter->convertToPng($tmpPath, $destPath);
			
		}
		else {
			
			$base_work_dir = api_get_path(SYS_COURSE_PATH).$_course['path'].DIRECTORY_SEPARATOR."document";
			ob_start();
			handle_uploaded_document($_course, $_FILES['NewFile'],$base_work_dir,'/mindmaps',$_user['user_id']);
			ob_end_clean();
			
		}		
		
		
	}
	
	
	
	
}
header('Location: fck_mindmap.php');

?>