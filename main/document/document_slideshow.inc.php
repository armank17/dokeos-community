<?php // $Id: document_slideshow.inc.php 21529 2009-06-20 14:01:55Z ivantcholakov $

/* For licensing terms, see /dokeos_license.txt */

/**
==============================================================================
*	This is a plugin for the documents tool. It looks for .jpg, .jpeg, .gif, .png
*	files (since these are the files that can be viewed in a browser) and creates
*	a slideshow with it by allowing to go to the next/previous image.
*	You can also have a quick overview (thumbnail view) of all the images in
*	that particular folder.
*
*	Each slideshow is folder based. Only
*	the images of the chosen folder are shown.
*
*	This file has two large sections.
*	1. code that belongs in document.php, but to avoid clutter I put the code here
*	(not present) 2. the function resize_image that handles the image resizing
*
*	@author Patrick Cool, responsible author
*	@author Roan Embrechts, minor cleanup
*	@package dokeos.document
==============================================================================
*/

$accepted_extensions = array('.jpg','.jpeg','.gif','.png');

// resetting the images of the slideshow = destroying the slideshow
if (isset($_GET['action']) && $_GET['action'] == 'exit_slideshow') {
	$_SESSION['image_files_only'] = null;
	unset($image_files_only);
}

// We check if there are images in this folder by searching the extensions for .jpg, .gif, .png
// grabbing the list of all the documents of this folder
//$all_files=$fileList['name'];
$array_to_search = (is_array($docs_and_folders)) ? $docs_and_folders : array();
if (count($array_to_search) > 0) {
    foreach ($array_to_search as $key_search => $value_Search) {
        $all_files[$key_search] = array('path' =>basename($value_Search['path']),'size'=>$value_Search['size'],'date'=> $value_Search['insert_date'], 'file' => $value_Search['title'], 'id' => $value_Search['ref']);
    }
}


$image_present = 0;
if (count($all_files) > 0) {
	foreach ($all_files as $key => $file_detail) {
                $file = $file_detail['file'];
		$slideshow_extension = strrchr($file,'.');
		$slideshow_extension = strtolower($slideshow_extension);
		if (in_array($slideshow_extension,$accepted_extensions)) {
			$image_present = 1;
			$image_files_only[] = array('file' => $file, 'id' =>$key, 'path' => $file_detail['path'], 'size' => $file_detail['size'], 'date' => $file_detail['date']);
		}
	}
}

$_SESSION['image_files_only'] = $image_files_only;
function sort_files($table) {

	//global $tablename_direction, $accepted_extensions;
	//$temp = array();

	foreach ($table as $file_array) {
		if ($file_array['filetype'] == 'file') {
			/*$slideshow_extension = strrchr($file_array['path'],'.');
                        $slideshow_extension = strtolower($slideshow_extension);
			if (in_array($slideshow_extension,$accepted_extensions)) {
				$temp[] = array('file', basename($file_array['path']), $file_array['size'], $file_array['insert_date']);
			}*/
                        $return_final_array[] = array('path' => basename($file_array['path']),'size'=>$file_array['size'],'date'=> $file_array['insert_date'], 'file' => $file_array['title'], 'id' => $file_array['ref']);
		}
	}

	/*if ($tablename_direction == 'DESC') {
		uasort($temp, 'rsort_table');
	} else {
		uasort($temp, 'sort_table');
	}

	$final_array = array();
	foreach ($temp as $file_array) {
		$final_array[] = $file_array[1];
	}*/

	return $return_final_array;
}

function sort_table($a, $b) {
	global $tablename_column;
	return strnatcmp($a[$tablename_column], $b[$tablename_column]);
}

function rsort_table($a, $b) {
	global $tablename_column;
	return strnatcmp($b[$tablename_column], $a[$tablename_column]);
}

?>
