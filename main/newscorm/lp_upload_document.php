<?php
/* For licensing terms, see /dokeos_license.txt */

/**
 * Learning Path
 * Upload: This script allow to upload files into Learning path
 * @package dokeos.learnpath
 */

// name of the language file that needs to be included
$language_file[] = 'document';
$language_file[] = 'gradebook';
$language_file[] = 'learnpath';

// including the global Dokeos file
require_once "../inc/global.inc.php";

// including additional libraries
require_once api_get_path(LIBRARY_PATH) . 'fileUpload.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'document.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'specific_fields_manager.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'formvalidator/FormValidator.class.php';
require_once '../document/document.inc.php';
require_once 'learnpath.class.php';
require_once 'learnpathItem.class.php';

// Security check
$is_allowed_to_edit = api_is_allowed_to_edit(null,true);
if(!$is_allowed_to_edit){
  api_not_allowed(true);
}

// adding extra javascript to the form
$htmlHeadXtra[] = '<script type="text/javascript">

function check_unzip() {
	if(document.upload.unzip.checked==true){
	document.upload.if_exists[0].disabled=true;
	document.upload.if_exists[1].checked=true;
	document.upload.if_exists[2].disabled=true;
	} else {
			document.upload.if_exists[0].checked=true;
			document.upload.if_exists[0].disabled=false;
			document.upload.if_exists[2].disabled=false;
			}
	}

function advanced_parameters() {
	if(document.getElementById(\'options\').style.display == \'none\') {
	document.getElementById(\'options\').style.display = \'block\';
	document.getElementById(\'img_plus_and_minus\').innerHTML=\'&nbsp;<img style="vertical-align:middle;" src="../img/div_hide.gif" alt="" />&nbsp;' . get_lang('AdvancedParameters') . '\';
	} else {
			document.getElementById(\'options\').style.display = \'none\';
			document.getElementById(\'img_plus_and_minus\').innerHTML=\'&nbsp;<img style="vertical-align:middle;" src="../img/div_show.gif" alt="" />&nbsp;' . get_lang('AdvancedParameters') . '\';
			}
	}
</script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-1.4.2.min.js" language="javascript"></script>';

$htmlHeadXtra[] = "<script type='text/javascript'>
  $(document).ready( function(){
    for (i = 0; i<4; i++) {
        var newdiv = '';
        newdiv = \"<div class='row'><div class='label'>&nbsp;</div><div class='formw'><input type='file' name='user_upload[]' id='user_upload[]' size='45'></div></div>\";
        $('#dynamicInput').append(newdiv);
    }
  $(\"div.label\").attr(\"style\",\"width: 80px;\");
  $(\"div.formw\").attr(\"style\",\"width: 87%;\");
  $(\"#img_plus_and_minus\").hide();

});
</script>";

$htmlHeadXtra[] = "<script type='text/javascript'>
  function addInput(){
    var newdiv = document.createElement(\"div\");
    newdiv.innerHTML = \"<div class='row'><div class='label' style='width: 80px;'>&nbsp;</div><div class='formw' style='width: 87%;'><input type='file' name='user_upload[]' id='user_upload[]' size='45'></div></div>\";
    document.getElementById('dynamicInput').appendChild(newdiv);
  }
</script>";
// Jquery library
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-1.4.2.min.js" language="javascript"></script>';

// Variable
$learnpath_id = Security::remove_XSS($_GET['lp_id']);

if (isset($_SESSION['lpobject'])) {
 if ($debug > 0)
  error_log('New LP - SESSION[lpobject] is defined', 0);
 $oLP = unserialize($_SESSION['lpobject']);
 if (is_object($oLP)) {
  if ($debug > 0)
   error_log('New LP - oLP is object', 0);
  if ($myrefresh == 1 OR (empty($oLP->cc)) OR $oLP->cc != api_get_course_id()) {
   if ($debug > 0)
    error_log('New LP - Course has changed, discard lp object', 0);
   if ($myrefresh == 1) {
    $myrefresh_id = $oLP->get_id();
   }
   $oLP = null;
   api_session_unregister('oLP');
   api_session_unregister('lpobject');
  } else {
   $_SESSION['oLP'] = $oLP;
   $lp_found = true;
  }
 }
}

// we set the encoding of the lp
if (empty($charset)) {
    // we set the encoding of the lp    
    if (!empty($_SESSION['oLP']->encoding)) {
        $charset = $_SESSION['oLP']->encoding;
        // Check if we have a valid api encoding
        $valid_encodings = api_get_valid_encodings();
        $has_valid_encoding = false;
        foreach ($valid_encodings as $valid_encoding) {
            if (strcasecmp($charset,$valid_encoding) == 0) {
                $has_valid_encoding = true;
            }
        }
        // If the scorm packages has not a valid charset, i.e : UTF-16 we are displaying
        if ($has_valid_encoding === false) {
            $charset = api_get_system_encoding();
        }
    } else {
        $charset = api_get_system_encoding();
    }
}

// variables
$is_allowed_to_edit = api_is_allowed_to_edit(null, true);


$courseDir = $_course['path'] . "/document";
$sys_course_path = api_get_path(SYS_COURSE_PATH);
$base_work_dir = $sys_course_path . $courseDir;
$noPHP_SELF = true;


//what's the current path?
if (isset($_GET['curdirpath']) && $_GET['curdirpath'] != '') {
 $path = $_GET['curdirpath'];
} elseif (isset($_POST['curdirpath'])) {
 $path = $_POST['curdirpath'];
} else {
 $path = '/';
}

//check the path: if the path is not found (no document id), set the path to /
if (!DocumentManager::get_document_id($_course, $path)) {
 $path = '/';
}

//this needs cleaning!
if (isset($_SESSION['_gid']) && $_SESSION['_gid'] != '') { //if the group id is set, check if the user has the right to be here
 //needed for group related stuff
 require_once api_get_path(LIBRARY_PATH) . 'groupmanager.lib.php';
 //get group info
 $group_properties = GroupManager::get_group_properties($_SESSION['_gid']);
 $noPHP_SELF = true;

 if ($is_allowed_to_edit || GroupManager::is_user_in_group($_user['user_id'], $_SESSION['_gid'])) { //only courseadmin or group members allowed
  $to_group_id = $_SESSION['_gid'];
  $req_gid = '&amp;gidReq=' . $_SESSION['_gid'];
  $interbreadcrumb[] = array("url" => "../group/group_space.php?gidReq=" . $_SESSION['_gid'], "name" => get_lang('GroupSpace'));
 } else {
  api_not_allowed(true);
 }
} elseif ($is_allowed_to_edit) { //admin for "regular" upload, no group documents. And check if is my shared folder
 $to_group_id = 0;
 $req_gid = '';
} else {  //no course admin and no group member...
 api_not_allowed(true);
}

//what's the current path?
if (isset($_GET['path']) && $_GET['path'] != '') {
 $path = $_GET['path'];
} elseif (isset($_POST['curdirpath'])) {
 $path = $_POST['curdirpath'];
} else {
 $path = '/';
}

//check the path: if the path is not found (no document id), set the path to /
if (!DocumentManager::get_document_id($_course, $path)) {
 $path = '/';
}
//group docs can only be uploaded in the group directory
if ($to_group_id != 0 && $path == '/') {
 $path = $group_properties['directory'];
}

//I'm in the certification module?
$is_certificate_mode = false;
$is_certificate_array = explode('/', $path);
array_shift($is_certificate_array);
if ($is_certificate_array[0] == 'certificates') {
 $is_certificate_mode = true;
}

//if we want to unzip a file, we need the library
if (isset($_POST['unzip']) && $_POST['unzip'] == 1) {
 require_once api_get_path(LIBRARY_PATH) . 'pclzip/pclzip.lib.php';
}

// variables
//$max_filled_space = DocumentManager::get_course_quota();
// title of the tool
if ($to_group_id != 0) { //add group name after for group documents
 $add_group_to_title = ' (' . $group_properties['name'] . ')';
}

if (isset($_REQUEST['certificate'])) {
 $nameTools = get_lang('UploadCertificate') . $add_group_to_title;
} else {
 $nameTools = get_lang('UplUploadDocument') . $add_group_to_title;
}

// breadcrumbs
if ($is_certificate_mode)
 $interbreadcrumb[] = array('url' => '../gradebook/' . $_SESSION['gradebook_dest'], 'name' => get_lang('Gradebook'));
else
 $interbreadcrumb[] = array('url' => './document.php?curdirpath=' . urlencode($path) . $req_gid, 'name' => get_lang('Documents'));


/*
  -----------------------------------------------------------
  Here we do all the work
  -----------------------------------------------------------
 */

//user has submitted a file
if (isset($_FILES['user_upload'])) {

	$dir_name = '/'.$_SESSION['oLP']->name;

	if(!file_exists($base_work_dir.$dir_name))
	{
		$created_dir = create_unexisting_directory($_course,$_user['user_id'],$to_group_id,$to_user_id,$base_work_dir,$dir_name,$_POST['dirname']);
	}

 for ($i = 0; $i < count($_FILES['user_upload']['name']); $i++) {
  $upload_allowed = 'N';
  $ext = explode(".", $_FILES['user_upload']['name'][$i]);
  if ($path == '/audio' || $path == '/podcasts') {
   if ($ext[1] == 'mp3' || $ext[1] == 'zip') {
    $upload_allowed = 'Y';
   }
  } elseif ($path == '/video' || $path == '/screencasts') {
    if($ext[1] == 'flv' || $ext[1] == 'wmv' || $ext[1] == 'mpg' || $ext[1] == 'avi' || $ext[1] == 'zip'|| $ext[1] == 'mp4'|| $ext[1] == 'ogg'|| $ext[1] == 'ogv' || $ext[1] == 'mov') {
    $upload_allowed = 'Y';
   }
  } elseif ($path == '/mindmaps') {
   if ($ext[1] == 'jpg' || $ext[1] == 'gif' || $ext[1] == 'png' || $ext[1] == 'xmind' || $ext[1] == 'zip') {
    $upload_allowed = 'Y';
   }
  } elseif ($path == '/photos' || $path == '/mascot' || $path == '/images' || $path == '/images/gallery') {
   if ($ext[1] == 'jpg' || $ext[1] == 'gif' || $ext[1] == 'png' || $ext[1] == 'zip') {
    $upload_allowed = 'Y';
   }
  } elseif ($path == '/animations') {
   if ($ext[1] == 'swf' || $ext[1] == 'zip') {
    $upload_allowed = 'Y';
   }
  } else { // if non special path
   $upload_allowed = 'Y';
  }
  if ($upload_allowed == 'N') {
   if ($path == '/audio') {
    Display::display_error_message(get_lang('OnlyAllowedUploadAudioFilesInAudioFolder')); //'Only Audio Files are allowed to upload in Audio folder'
   } elseif ($path == '/video') {
    Display::display_error_message(get_lang('OnlyAllowedUploadVideoFilesInVideoFolder')); //'Only Video Files are allowed to upload in Video folder'
   } elseif ($path == '/screencasts') {
    Display::display_error_message(get_lang('OnlyAllowedUploadVideoFilesInScreencastsFolder')); // 'Only Video Files are allowed to upload in Screencasts folder'
   } elseif ($path == '/podcasts') {
    Display::display_error_message(get_lang('OnlyAllowedUploadJpgGifPngFilesInPodcastsFolder')); // Only jpg,gif,png Files are allowed to upload in Podcasts folder
   } elseif ($path == '/photos') {
    Display::display_error_message(get_lang('OnlyAllowedUploadJpgGifPngFilesInPhotosFolder')); // Only jpg,gif,png Files are allowed to upload in Photos folder
   } elseif ($path == '/mascot') {
    Display::display_error_message(get_lang('OnlyAllowedUploadJpgGifPngFilesInMascotFolder')); // Only jpg,gif,png Files are allowed to upload in Mascot folder
   } elseif ($path == '/mindmaps') {
    Display::display_error_message(get_lang('OnlyAllowedUploadJpgXmindGifPngFilesInMindmapsFolder')); // Only jpg,gif,png,xmind Files are allowed to upload in Mindmaps folder
   } elseif ($path == '/animations') {
    Display::display_error_message(get_lang('OnlyAllowedUploadSwfFilesInAnimationFolder')); //  Only swf Files are allowed to upload in Animation folder
   } elseif ($path == '/images') {
    Display::display_error_message(get_lang('OnlyAllowedUploadJpgGifPngZipFilesInImagesFolder')); // Only jpg,gif,png,zip Files are allowed to upload in images folder
   }
  } else {
   $ziparray = array();
   if (preg_match("/.zip$/", strtolower($_FILES['user_upload']['name'][$i]))) {
    require_once api_get_path(LIBRARY_PATH) . 'pclzip/pclzip.lib.php';
    $zip_file = new pclZip($_FILES['user_upload']['tmp_name'][$i]);
    $zip_content = $zip_file->listContent();
    foreach ($zip_content as $key => $zip_content_value) {
     $value = $zip_content_value['stored_filename'];

     if (is_string($value)) {
      $file_info = explode('/', $value);
      $file_extension = $file_info[count($file_info) - 1];
      if ($file_extension <> '') {
       $file_extension_info = explode('.', $file_extension);
       $file_extension_data = $file_extension_info[count($file_extension_info) - 1];
       if (filter_extension($file_extension_data)) {
        $ziparray[] = $value;
       }
      }
     }
    }
   }
   $upload_ok = process_uploaded_file($_FILES['user_upload']);
   if ($upload_ok) {
    // Default document quotum
    $max_filled_space = api_get_setting('default_document_quotum');
	$_POST['curdirpath'] = $dir_name;

    //file got on the server without problems, now process it
    $new_path = handle_multiple_uploaded_document($_course, $_FILES['user_upload'], $i, $base_work_dir, $_POST['curdirpath'], $_user['user_id'], $to_group_id, $to_user_id, $max_filled_space, $_POST['unzip'], $_POST['if_doc_exists']);
    if (is_array($new_path)) {
        $new_path = $new_path['path'];
    }
    $new_comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    $new_title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $docid = DocumentManager::get_document_id($_course, $new_path);

    if (!empty($new_path))
     if (($docid = DocumentManager::get_document_id($_course, $new_path))) {
      $table_document = Database::get_course_table(TABLE_DOCUMENT);
      $ct = '';
      if ($new_comment)
       $ct .= ", comment='$new_comment'";
      if ($new_title)
       $ct .= ", title='$new_title'";
      Database::query("UPDATE $table_document SET" . substr($ct, 1) .
                      " WHERE id = '$docid'", __FILE__, __LINE__);
     }

    // Uploading a new file as Learning path Item
    if (is_numeric($docid) && $docid > 0) {
     $parent = 0;
     $title = $_FILES['user_upload']['name'][$i];
     // Get the previous item ID
     $previous = $_SESSION['oLP']->select_previous_item_id();
     // Add Lp Item
     $_SESSION['oLP']->add_item($parent, $previous, 'document', $docid, $title, '');
    }

    if ((api_get_setting('search_enabled') == 'true') && ($docid = DocumentManager::get_document_id($_course, $new_path)) && extension_loaded('xapian')) {
     $table_document = Database::get_course_table(TABLE_DOCUMENT);
     $result = Database::query("SELECT * FROM $table_document WHERE id = '$docid' LIMIT 1", __FILE__, __LINE__);
     if (Database::num_rows($result) == 1) {
      $row = Database::fetch_array($result);
      $doc_path = api_get_path(SYS_COURSE_PATH) . $courseDir . $row['path'];
      //TODO: mime_content_type is deprecated, fileinfo php extension is enabled by default as of PHP 5.3.0
      // now versions of PHP on Debian testing(5.2.6-5) and Ubuntu(5.2.6-2ubuntu) are lower, so wait for a while
      $doc_mime = mime_content_type($doc_path);
      //TODO: more mime types
      $allowed_mime_types = array('text/plain', 'application/pdf', 'application/postscript', 'application/msword', 'text/html', 'text/rtf', 'application/vnd.ms-powerpoint', 'application/vnd.ms-excel');

      // mime_content_type does not detect correctly some formats that are going to be supported for index, so an extensions array is used by the moment
      if (empty($doc_mime)) {
       $allowed_extensions = array('ppt', 'pps', 'xls');
       $extensions = preg_split("/[\/\\.]/", $doc_path);
       $doc_ext = strtolower($extensions[count($extensions) - 1]);
       if (in_array($doc_ext, $allowed_extensions)) {
        switch ($doc_ext) {
         case 'ppt':
         case 'pps':
          $doc_mime = 'application/vnd.ms-powerpoint';
          break;
         case 'xls':
          $doc_mime = 'application/vnd.ms-excel';
          break;
        }
       }
      }

      //TODO: check if checking mimetypes is better
      if (isset($_POST['index_document']) && $_POST['index_document']) {
       $file_title = $row['title'];
       $file_content = DocumentManager::get_text_content($doc_path, $doc_mime);
       $courseid = api_get_course_id();
       isset($_POST['language']) ? $lang = Database::escape_string($_POST['language']) : $lang = 'english';

       require_once api_get_path(LIBRARY_PATH) . 'search/DokeosIndexer.class.php';
       require_once api_get_path(LIBRARY_PATH) . 'search/IndexableChunk.class.php';

       $ic_slide = new IndexableChunk();
       $ic_slide->addValue("title", $file_title);
       $ic_slide->addCourseId($courseid);
       $ic_slide->addToolId(TOOL_DOCUMENT);
       $xapian_data = array(
           SE_COURSE_ID => $courseid,
           SE_TOOL_ID => TOOL_DOCUMENT,
           SE_DATA => array('doc_id' => (int) $docid),
           SE_USER => (int) api_get_user_id(),
       );
       $ic_slide->xapian_data = serialize($xapian_data);
       $di = new DokeosIndexer();
       $di->connectDb(NULL, NULL, $lang);

       $specific_fields = get_specific_field_list();

       // process different depending on what to do if file exists
       /**
        * FIXME: Find a way to really verify if the file had been
        * overwriten. Now all work is done at
        * handle_uploaded_document() and it's difficult to verify it
        */
       if (!empty($_POST['if_doc_exists']) && $_POST['if_doc_exists'] == 'overwrite') {
        // overwrite the file on search engine
        // actually, it consists on delete terms from db, insert new ones, create a new search engine document, and remove the old one
        // get search_did
        $tbl_se_ref = Database::get_main_table(TABLE_MAIN_SEARCH_ENGINE_REF);
        $sql = 'SELECT * FROM %s WHERE course_code=\'%s\' AND tool_id=\'%s\' AND ref_id_high_level=%s LIMIT 1';
        $sql = sprintf($sql, $tbl_se_ref, $courseid, TOOL_DOCUMENT, $docid);
        $res = Database::query($sql, __FILE__, __LINE__);

        if (Database::num_rows($res) > 0) {
         $se_ref = Database::fetch_array($res);
         $di->remove_document((int) $se_ref['search_did']);
         $all_specific_terms = '';
         foreach ($specific_fields as $specific_field) {
          delete_all_specific_field_value($courseid, $specific_field['id'], TOOL_DOCUMENT, $docid);
          //update search engine
          $sterms = trim($_REQUEST[$specific_field['code']]);
          $all_specific_terms .= ' ' . $sterms;
          $sterms = explode(',', $sterms);
          foreach ($sterms as $sterm) {
           $sterm = trim($sterm);
           if (!empty($sterm)) {
            $ic_slide->addTerm($sterm, $specific_field['code']);
            add_specific_field_value($specific_field['id'], $courseid, TOOL_DOCUMENT, $docid, $value);
           }
          }
         }
         // add terms also to content to make terms findable by probabilistic search
         $file_content = $all_specific_terms . ' ' . $file_content;
         $ic_slide->addValue("content", $file_content);
         $di->addChunk($ic_slide);
         //index and return a new search engine document id
         $did = $di->index();
         if ($did) {
          // update the search_did on db
          $tbl_se_ref = Database::get_main_table(TABLE_MAIN_SEARCH_ENGINE_REF);
          $sql = 'UPDATE %s SET search_did=%d WHERE id=%d LIMIT 1';
          $sql = sprintf($sql, $tbl_se_ref, (int) $did, (int) $se_ref['id']);
          Database::query($sql, __FILE__, __LINE__);
         }
        }
       } else {
        // add all terms
        $all_specific_terms = '';
        foreach ($specific_fields as $specific_field) {
         if (isset($_REQUEST[$specific_field['code']])) {
          $sterms = trim($_REQUEST[$specific_field['code']]);
          $all_specific_terms .= ' ' . $sterms;
          if (!empty($sterms)) {
           $sterms = explode(',', $sterms);
           foreach ($sterms as $sterm) {
            $ic_slide->addTerm(trim($sterm), $specific_field['code']);
            add_specific_field_value($specific_field['id'], $courseid, TOOL_DOCUMENT, $docid, $sterm);
           }
          }
         }
        }
        // add terms also to content to make terms findable by probabilistic search
        $file_content = $all_specific_terms . ' ' . $file_content;
        $ic_slide->addValue("content", $file_content);
        $di->addChunk($ic_slide);
        //index and return search engine document id
        $did = $di->index();
        if ($did) {
         // save it to db
         $tbl_se_ref = Database::get_main_table(TABLE_MAIN_SEARCH_ENGINE_REF);
         $sql = 'INSERT INTO %s (id, course_code, tool_id, ref_id_high_level, search_did)
						VALUES (NULL , \'%s\', \'%s\', %s, %s)';
         $sql = sprintf($sql, $tbl_se_ref, $courseid, TOOL_DOCUMENT, $docid, $did);
         Database::query($sql, __FILE__, __LINE__);
        }
       }
      }
     }
    }

    //check for missing images in html files
    $missing_files = check_for_missing_files($base_work_dir . $new_path);
    if ($missing_files) {
     //show a form to upload the missing files
     Display::display_normal_message(build_missing_files_form($missing_files, $_POST['curdirpath'], $_FILES['user_upload']['name'][$i]), false, true);
    }
   }
  }
 }// End of for loop for the user_upload images

 $has_an_attachment_file = false;
 for ($x = 0; $x < count($_FILES['user_upload']['name']); $x++) {
   if (strcmp($_FILES['user_upload']['name'][$x], '') !== 0) {
     $has_an_attachment_file = true;
   }
 }

 // Redirect to main page for add more content.
 if (isset($_POST['submitDocument']) > 0 && $has_an_attachment_file) {
  header('location:lp_controller.php?' . api_get_cidreq() . '&action=add_item&type=step&lp_id=' . $learnpath_id);
  exit;
 }
}
//missing images are submitted
if (isset($_POST['submit_image'])) {
 $number_of_uploaded_images = count($_FILES['img_file']['name']);
 //if images are uploaded
 if ($number_of_uploaded_images > 0) {
  //we could also create a function for this, I'm not sure...
  //create a directory for the missing files
  $img_directory = str_replace('.', '_', $_POST['related_file'] . "_files");
  $missing_files_dir = create_unexisting_directory($_course, $_user['user_id'], $to_group_id, $to_user_id, $base_work_dir, $img_directory);
  //put the uploaded files in the new directory and get the paths
  $paths_to_replace_in_file = move_uploaded_file_collection_into_directory($_course, $_FILES['img_file'], $base_work_dir, $missing_files_dir, $_user['user_id'], $to_group_id, $to_user_id, $max_filled_space);
  //open the html file and replace the paths
  replace_img_path_in_html_file($_POST['img_file_path'], $paths_to_replace_in_file, $base_work_dir . $_POST['related_file']);
  //update parent folders
  item_property_update_on_folder($_course, $_POST['curdirpath'], $_user['user_id']);
 }
}


//event_access_tool(TOOL_DOCUMENT);

/* ============================================================================ */
?>

<?php

// actions// display the header
Display::display_tool_header($nameTools, 'Doc');

$mymodule_lang_var = api_convert_encoding(get_lang('MyModule'), $charset, api_get_system_encoding());

echo '<div class="actions ">';
$return = "";
$lp_id = Security::remove_XSS($_GET['lp_id']);
$return.= '<a href="lp_controller.php?cidReq=' . Security::remove_XSS($_GET['cidReq']) . '&amp;action=add_item&amp;type=step&amp;lp_id=' . $lp_id . '">' . Display::return_icon('pixel.gif', $mymodule_lang_var, array('class' => 'toolactionplaceholdericon toolactionauthorcontent')) . $mymodule_lang_var . '</a>';
echo $return;
echo '</div>';

echo '<div id="content">';

//form to select directory
$folders = DocumentManager::get_all_document_folders($_course, $to_group_id, $is_allowed_to_edit);
if (!$is_certificate_mode)
 echo(build_directory_selector($folders, $path, $group_properties['directory'], false, true));
?>
<!-- start upload form -->
<?php
echo '<br/>';
$form = new FormValidator('upload', 'POST', api_get_self() . '?' . api_get_cidreq() . '&amp;path=%2F&amp;selectcat=&amp;lp_id=' . $lp_id, '', 'enctype="multipart/form-data"');

$form->addElement('hidden', 'curdirpath', $path);
//Dynamic file upload
$form->addElement('html', '<div id="dynamicInput"><div class="row"><div class="label">' . get_lang('File') . '</div><div class="formw"><input type="file" name="user_upload[]" id="user_upload[]" size="45" title="' . get_lang('Upload') . '" ></div></div></div>');
$form->addElement('html','<br/><br/><div align="right" style="width:67%;"><a href="javascript:void(0)" onClick="addInput();">'.Display::return_icon('add_32.png',get_lang('More'),array('style'=>'height:16px;width:16px;')).' '.get_lang('More').'</a></div>');

//Advanced parameters
$form->addElement('html', '<div class="row">
			<div class="label">&nbsp;</div>
			<div class="formw">
				<a href="javascript://" onclick=" return advanced_parameters()"><span id="img_plus_and_minus"><div style="vertical-align:top;" ><img style="vertical-align:middle;" src="../img/div_show.gif" alt="" />&nbsp;' . get_lang('AdvancedParameters') . '</div></span></a>
			</div>
			</div>');
$form->addElement('html', '<div id="options" style="display:none">');

//check box options
$form->addElement('checkbox', 'unzip', get_lang('Options'), get_lang('Uncompress'), 'onclick="check_unzip()" value="1"');
$form->addElement('checkbox', 'prevent_escaping_spaces', '', get_lang('DoNotEscapeSpaces'));

if (api_get_setting('search_enabled') == 'true') {
 //TODO: include language file
 $supported_formats = 'Supported formats for index: Text plain, PDF, Postscript, MS Word, HTML, RTF, MS Power Point';
 $form->addElement('checkbox', 'index_document', '', get_lang('SearchFeatureDoIndexDocument') . '<div style="font-size: 80%" >' . $supported_formats . '</div>');
 $form->addElement('html', '<br /><div class="row">');
 $form->addElement('html', '<div class="label">' . get_lang('SearchFeatureDocumentLanguage') . '</div>');
 $form->addElement('html', '<div class="formw">' . api_get_languages_combo() . '</div>');
 $form->addElement('html', '</div><div class="sub-form">');
 $specific_fields = get_specific_field_list();
 foreach ($specific_fields as $specific_field) {
  $form->addElement('text', $specific_field['code'], $specific_field['name'] . ' : ');
 }
 $form->addElement('html', '</div>');
}

$form->addElement('radio', 'if_exists', get_lang('UplWhatIfFileExists'), get_lang('UplDoNothing'), 'nothing');
$form->addElement('radio', 'if_exists', '', get_lang('UplOverwriteLong'), 'overwrite');
$form->addElement('hidden', 'if_doc_exists', 'overwrite');
$form->addElement('radio', 'if_exists', '', get_lang('UplRenameLong'), 'rename');

//close the java script and avoid the footer up
$form->addElement('html', '</div>');

//button send document
$form->addElement('style_submit_button', 'submitDocument', get_lang('SendDocument'), 'class="upload" style="float:right;margin-right:60px"');

$defaults = array('index_document' => 'checked="checked"');
$form->setDefaults($defaults);
// Display the upload fields and the left image
echo '<table style="text-align: left; width: 100%;" border="0" cellpadding="2"cellspacing="2"><tbody><tr><td style="vertical-align: top;width: 70%;">';
$form->display();
echo '</td><td style="vertical-align: top;width: 30%;">';
echo Display::display_icon('instructor_resources.png');
echo '</td></tr></tbody></table>';
?>
<!-- end upload form -->
<?php
//ending div#content
echo '</div>';