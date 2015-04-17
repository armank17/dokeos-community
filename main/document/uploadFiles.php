<?php

require_once '../inc/global.inc.php';
require_once api_get_path(LIBRARY_PATH) . 'fileUpload.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'specific_fields_manager.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'document.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'main_api.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'pclzip/pclzip.lib.php';
require_once api_get_path(LIBRARY_PATH) . 'searchengine.lib.php';
require_once './document.inc.php';

$is_document = 1;
$cur_path = $_REQUEST['dir'];
$course_info = api_get_course_info(api_get_course_id());
$output_dir = api_get_path(SYS_COURSE_PATH) . $course_info['path'] . "/document" . $cur_path . "/";
$url = "Location: " . api_get_path(WEB_PATH) . "main/document/document.php?cidReq=" . trim($_REQUEST['cidReq']) . "&curdirpath=" . $cur_path . "";

switch ($cur_path) {
    case '/dropbox' : $output_dir = api_get_path(SYS_COURSE_PATH) . $course_info['path'] . "/dropbox/";
        $url = "Location: " . api_get_path(WEB_PATH) . "main/core/views/dropbox/index.php?cidReq=" . trim($_REQUEST['cidReq']) . "&view=sent";
        $is_document = 0;
        break;
    case '/images' : $url = "Location: " . api_get_path(WEB_PATH) . "main/document/slideshow.php?cidReq=" . trim($_REQUEST['cidReq']) . "&slide_id=all&document=0&curdirpath=%2Fimages";
        break;
    case '/photos' : $url = "Location: " . api_get_path(WEB_PATH) . "main/document/slideshow.php?cidReq=" . trim($_REQUEST['cidReq']) . "&slide_id=all&document=0&curdirpath=%2Fphotos";
        break;
    case '/mascot' : $url = "Location: " . api_get_path(WEB_PATH) . "main/document/slideshow.php?cidReq=" . trim($_REQUEST['cidReq']) . "&slide_id=all&document=0&curdirpath=%2Fmascot";
        break;
    case '/mindmaps' : $url = "Location: " . api_get_path(WEB_PATH) . "main/mindmap/index.php?cidReq=" . trim($_REQUEST['cidReq']) . "&view=sent";
        break;
    case '/audio' : $url = "Location: " . api_get_path(WEB_PATH) . "main/document/mediabox_view.php?cidReq=" . trim($_REQUEST['cidReq']) . "&slide_id=all&document=0&curdirpath=%2Faudio";
        break;
    case '/podcasts' : $url = "Location: " . api_get_path(WEB_PATH) . "main/document/mediabox_view.php?cidReq=" . trim($_REQUEST['cidReq']) . "&slide_id=all&document=0&curdirpath=%2Fpodcasts";
        break;
    case '/video' : $url = "Location: " . api_get_path(WEB_PATH) . "main/document/mediabox_view.php?cidReq=" . trim($_REQUEST['cidReq']) . "&slide_id=all&document=0&curdirpath=%2Fvideo";
        break;
    case '/screencasts' : $url = "Location: " . api_get_path(WEB_PATH) . "main/document/mediabox_view.php?cidReq=" . trim($_REQUEST['cidReq']) . "&slide_id=all&document=0&curdirpath=%2Fscreencasts";
        break;
    case '/animations' : $url = "Location: " . api_get_path(WEB_PATH) . "main/document/mediabox_view.php?cidReq=" . trim($_REQUEST['cidReq']) . "&slide_id=all&document=0&curdirpath=%2Fanimations";
        break;
}

$_SESSION["show_message"] = get_lang("UplUploadSucceeded");
$MAX_FILES = count($_FILES["files"]["tmp_name"]);
$date = date('Y-m-d H:i:s');
$userid = api_get_user_id();
$user_data = api_get_user_info($userid);
$name_user = $user_data['firstname'] . ' ' . $user_data['lastname'];

//tables files document
$table_document = Database::get_course_table(TABLE_DOCUMENT);
$table_item_property = Database::get_course_table(TABLE_ITEM_PROPERTY);

//REQUEST Parameters
$unzip = trim($_REQUEST['unzip']);
$space = trim($_REQUEST['space']);
$rename = trim($_REQUEST['rename']);
$other_rename = trim($_REQUEST['if_exists']);
$dropbox = trim($_REQUEST['dbox']) || trim($_REQUEST['db_overwrite']);

//tables and variables globals for engine search
$table_keywords = Database::get_main_table(TABLE_MAIN_SEARCH_ENGINE_KEYWORDS);
$tble_ref = Database::get_main_table(TABLE_MAIN_SEARCH_ENGINE_REF);
$search_terms = Security::remove_XSS($_REQUEST['search_terms']);
$course_id = api_get_course_id();

if ($is_document == 0 || $cur_path == '/mindmaps') {
    //tables files dropbox
    $table_dropbox_file = Database::get_course_table(TABLE_DROPBOX_FILE);
    $table_dropbox_person = Database::get_course_table(TABLE_DROPBOX_PERSON);
    $table_dropbox_post = Database::get_course_table(TABLE_DROPBOX_POST);
    $user_id = $_REQUEST['users'];
    if (count($user_id) > 0) {
        $array_user_id = explode(',', $user_id);
    } else {
        $array_user_id = $_REQUEST['recipients'];
    }
    $size = count($array_user_id);
    if ($size == 0) {
        $array_user_id[] = 0;
        $size = 1;
    }
}

$filename = '';
$filesource = '';
$filesize = 0;

for ($i = 0; $i < $MAX_FILES; $i++) {
    $filesource = $_FILES["files"]["tmp_name"][$i];
    $filename = $_FILES["files"]["name"][$i];
    $filepath = replace_dangerous_char($filename, 'strict');
    $filesize = $_FILES["files"]["size"][$i];
    $dir_path = getDirPath($cur_path, $filepath);

    if ($unzip != '') { //Unzip files document
        if (end(explode(".", $filename)) == 'zip') {
            $name_zip = str_replace('.zip', '', $filename);
            if ($space != '') //Remove spaces in filename
                $name_zip = str_replace(' ', '', $name_zip);
            unZip($output_dir . $name_zip, $filesource);
            $dir_path_zip = str_replace('.zip', '', $dir_path);
            if (getPathDocument($dir_path_zip, $name_zip) == '') { //File documents not exists
                saveFilesInDocument($dir_path_zip, $name_zip, 'folder', 0);
                $id_file = getIdFileDocument($table_document);
                saveFilesInItemProperty($userid, 0, $date, $id_file, 'DocumentAdded');
            }
            saveFilesZip($output_dir, $dir_path_zip, $name_zip, $userid, $date);
        } else { //file is not .zip
            if ($space != '') {
                $filename = str_replace(' ', '', $filename);
            }
            if (move_uploaded_file($filesource, $output_dir . $filepath)) {

                if (getPathDocument($dir_path, $filename) == '') { //File documents not exists
                    saveFilesInDocument(getDirPath($cur_path, $filepath), $filename, 'file', $filesize);
                    $id_file = getIdFileDocument($table_document);
                    saveFilesInItemProperty($userid, 0, $date, $id_file, 'DocumentAdded');
                } else { //File documents exists
                    if ($rename != '' || $other_rename == 'rename') { //Rename filename
                        $filename = getRenameFile($table_document, $filename);
                        saveFilesInDocument(getDirPath($cur_path, $filepath), $filename, 'file', $filesize);
                        $id_file = getIdFileDocument($table_document);
                        saveFilesInItemProperty($userid, 0, $date, $id_file, 'DocumentAdded');
                    }
                }
            } else {
                $_SESSION["show_message_error"] = get_lang("UplUnableToSaveFile");
            }
            //move_uploaded_file($filesource, $output_dir . $filepath);
        }
        if (api_get_setting('search_enabled') == 'true') {
            //Indexar search terms to Xapian
            saveSearchTerms($filename, $id_file);
        }
    } else {
        if ($space != '') {
            $filename = str_replace(' ', '', $filename);
        }
        if ($is_document == 1) { //Save files in documents
            if (move_uploaded_file($filesource, $output_dir . $filepath)) {
                if (getPathDocument($dir_path, $filename) == '') { //File documents not exists
                    saveFilesInDocument(getDirPath($cur_path, $filepath), $filename, 'file', $filesize);
                    $id_file = getIdFileDocument($table_document);
                    if ($cur_path == '/mindmaps') { //shared files mindmaps with other users
                        saveFilesInDropboxPerson($id_file, $userid);
                        for ($j = 0; $j < $size; $j++) { //Shared files mindmaps to users
                            saveFilesInDropboxPost($id_file, $array_user_id[$j]);
                            saveFilesInDropboxPerson($id_file, $array_user_id[$j]);
                            saveFilesInItemProperty($userid, $array_user_id[$j], $date, $id_file, 'MindmapFileCreated');
                        }
                    } else {
                        saveFilesInItemProperty($userid, 0, $date, $id_file, 'DocumentAdded');
                    }
                } else { //File documents exists
                    if ($rename != '' || $other_rename == 'rename') { //Rename filename
                        $filename = getRenameFile($table_document, $filename);
                        saveFilesInDocument(getDirPath($cur_path, $filepath), $filename, 'file', $filesize);
                        $id_file = getIdFileDocument($table_document);
                        if ($cur_path == '/mindmaps') { //shared files mindmaps with other users
                            saveFilesInDropboxPerson($id_file, $userid);
                            for ($j = 0; $j < $size; $j++) { //Shared files mindmaps to users
                                saveFilesInDropboxPost($id_file, $array_user_id[$j]);
                                saveFilesInDropboxPerson($id_file, $array_user_id[$j]);
                                saveFilesInItemProperty($userid, $array_user_id[$j], $date, $id_file, 'MindmapFileCreated');
                            }
                        } else {
                            saveFilesInItemProperty($userid, 0, $date, $id_file, 'DocumentAdded');
                        }
                    }
                }
            } else {
                $_SESSION["show_message_error"] = get_lang("UplUnableToSaveFile");
            }
            //move_uploaded_file($filesource, $output_dir . $filepath);
            if (api_get_setting('search_enabled') == 'true') {
                //Indexar search terms to Xapian
                saveSearchTerms($filename, $id_file);
            }
        } else { //Save files in dropbox
//            if (move_uploaded_file($filesource, $output_dir . $filepath)) {
//
//                if ($dropbox != '') { //Overwrite files dropbox
//                    if (getPathDropbox($filename, $filename) != '') //Files dropbox exists
//                        $option = 'move';
//                }
//                else { //Rename files dropbox
//                    if (getPathDropbox($filename, $filename) != '') { //Files dropbox exists
//                        $filename = getRenameFile($table_dropbox_file, $filename);
//                    }
//                }
//
//                if ($option != 'move') {
//                    if ($filename != '.') {
//                        saveFilesInDropbox($userid, $name_user, $filepath, $filesize, $date);
//                        $id_file = getIdFileDocument($table_dropbox_file);
//                        saveFilesInDropboxPerson($id_file, $userid);
//                        for ($j = 0; $j < $size; $j++) { //Shared files to users
//                            saveFilesInDropboxPost($id_file, $array_user_id[$j]);
//                            saveFilesInDropboxPerson($id_file, $array_user_id[$j]);
//                        }
//                    }
//                }
//            } else {
//                $_SESSION["show_message_error"] = get_lang("UplUnableToSaveFile");
//            }
            //Rename files dropbox
            if (getPathDropbox($filepath, $filename) != '') { //Files dropbox exists
                $filename = getRenameFile($table_dropbox_file, $filename);
                $filepath = replace_dangerous_char($filename, 'strict');
            }

            if ($filename != '.') {
                saveFilesInDropbox($userid, $name_user, $filepath, $filesize, $date);
                $id_file = getIdFileDocument($table_dropbox_file);
                saveFilesInDropboxPerson($id_file, $userid);
                for ($j = 0; $j < $size; $j++) { //Shared files to users
                    saveFilesInDropboxPost($id_file, $array_user_id[$j]);
                    saveFilesInDropboxPerson($id_file, $array_user_id[$j]);
                }
            }
            if (!move_uploaded_file($filesource, $output_dir . $filepath)) {
                $_SESSION["show_message_error"] = get_lang("UplUnableToSaveFile");
            }
        }
    }
}

function getDirPath($cur_path, $filename) {
    if ($cur_path == '/')
        return "/" . $filename;
    else
        return $cur_path . "/" . $filename;
}

function unZip($output_dir, $folder) {
    $zip_file = new pclZip($folder);
    $zip_file->extract(PCLZIP_OPT_PATH, $output_dir);
}

function saveFilesZip($output_dir, $dir_path_zip, $nom_dir, $userid, $date) {
    global $table_document;
    $i = strripos($dir_path_zip, end(explode('/', $dir_path_zip)));
    $tmp_path_zip = substr($dir_path_zip, 0, $i);

    $handle = opendir($output_dir . $nom_dir);
    while ($file = readdir($handle)) {
        if ($file == '.' || $file == '..')
            continue;
        $dir = $nom_dir . '/' . $file;
        $completepath = $output_dir . $dir;

        if (is_dir($completepath)) {
            if (getPathDocument($tmp_path_zip . $dir, $file) == '') {
                saveFilesInDocument($tmp_path_zip . $dir, $file, 'folder', 0);
                $id_file = getIdFileDocument($table_document);
                saveFilesInItemProperty($userid, 0, $date, $id_file, 'DocumentAdded');
            }
            //return
            saveFilesZip($output_dir, $dir_path_zip, $dir, $userid, $date);
        } else {
            if (getPathDocument($tmp_path_zip . $dir, $file) == '') {
                saveFilesInDocument($tmp_path_zip . $dir, $file, 'file', filesize($completepath));
                $id_file = getIdFileDocument($table_document);
                saveFilesInItemProperty($userid, 0, $date, $id_file, 'DocumentAdded');
            }
        }
    }
}

function saveFilesInDocument($dir_path, $file_name, $type_file, $file_size) {
    global $table_document;
    $sql = 'INSERT INTO ' . $table_document . '(path, comment, title, filetype, size, display_order, readonly, is_template, session_id) 
            VALUES("' . $dir_path . '", "", "' . $file_name . '", "' . $type_file . '", "' . $file_size . '", 0, 0, 0, "' . api_get_session_id() . '")';
    Database::query($sql);
}

function saveFilesInItemProperty($userid, $to_user_id, $date, $id_file, $type_file) {
    global $table_item_property;
    $sql = "INSERT INTO " . $table_item_property . "(tool, insert_user_id, insert_date, lastedit_date, ref, lastedit_type, 
            lastedit_user_id, to_group_id, to_user_id, visibility, start_visible, end_visible, id_session) 
            VALUES('document', " . $userid . ", '" . $date . "', '" . $date . "', '" . $id_file . "', '" . $type_file . "', 
            " . $userid . ", 0, '" . $to_user_id . "', 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '" . api_get_session_id() . "')";
    Database::query($sql);
}

function saveFilesInDropbox($userid, $name_user, $file_name, $file_size, $date) {
    global $table_dropbox_file;
    $sql = 'INSERT INTO ' . $table_dropbox_file . '(uploader_id, filename, filesize, title, description, 
            author, upload_date, last_upload_date, cat_id, session_id, type) 
            VALUES("' . $userid . '", "' . $file_name . '", "' . $file_size . '", "' . $file_name . '", "", 
            "' . $name_user . '", "' . $date . '", "' . $date . '", 0, "' . api_get_session_id() . '", 0)';
    Database::query($sql);
}

function saveFilesInDropboxPost($id_file, $to_user_id) {
    global $table_dropbox_post;
    $sql = "INSERT INTO " . $table_dropbox_post . "(file_id, dest_user_id, feedback_date, feedback, cat_id, session_id) 
            VALUES('" . $id_file . "', '" . $to_user_id . "', '0000-00-00 00:00:00', NULL, 0,'" . api_get_session_id() . "' )";
    Database::query($sql);
}

function saveFilesInDropboxPerson($id_file, $to_user_id) {
    global $table_dropbox_person;
    $sql = "INSERT INTO " . $table_dropbox_person . "(file_id, user_id) VALUES('" . $id_file . "', '" . $to_user_id . "')";
    Database::query($sql);
}

function getIdFileDocument($table_document) {
    $result = Database::query("SELECT MAX(id) AS id FROM " . $table_document . "");
    $row = Database::fetch_array($result);
    return (int) $row['id'];
}

function getPathDocument($path, $file_name) {
    global $table_document;
    $result = Database::query('SELECT id FROM ' . $table_document . ' WHERE path="' . $path . '" AND title="' . $file_name . '"');
    $row = Database::fetch_array($result);
    return $row['id'];
}

function getPathDropbox($path, $file_name) {
    global $table_dropbox_file;
    $result = Database::query('SELECT id FROM ' . $table_dropbox_file . ' WHERE filename="' . $path . '" AND title="' . $file_name . '"');
    $row = Database::fetch_array($result);
    return $row['id'];
}

function getRenameFile($table_document, $title) {
    $ext = getExtensionFile($title);
    if ($ext != '.') {
        $title = getFileName($title);
        $result = Database::query("SELECT COUNT(id) AS id FROM " . $table_document . " WHERE title LIKE '%" . $title . "%'");
        $row = Database::fetch_array($result);
        return $title . "_" . $row['id'] . $ext;
    } else {
        return $title;
    }
}

function getFileName($file) {
    return substr($file, 0, strlen($file) - strlen(getExtensionFile($file)));
}

function getExtensionFile($file) {
    return "." . end(explode(".", $file));
}

function saveSearchTerms($filename, $document_id) {
    if (extension_loaded('xapian')) {
        require_once api_get_path(LIBRARY_PATH) . 'search/IndexableChunk.class.php';
        require_once api_get_path(LIBRARY_PATH) . 'search/DokeosIndexer.class.php';

        global $search_terms, $table_keywords, $tble_ref, $course_id, $userid;
        $sql = "INSERT INTO $table_keywords (idobj, course_code, tool_id, value) VALUES($document_id, '$course_id', 'document', '$search_terms')";
        Database::query($sql);
        $ic_slide = new IndexableChunk();
        $ic_slide->addValue('title', $filename);
        $ic_slide->addCourseId($course_id);
        $ic_slide->addToolId(TOOL_DOCUMENT);
        $xapian_data = array(
            SE_COURSE_ID => $course_id,
            SE_TOOL_ID => TOOL_DOCUMENT,
            SE_DATA => array('doc_id' => (int) $document_id),
            SE_USER => (int) $userid,
        );
        $ic_slide->xapian_data = serialize($xapian_data);
        $file_content = $search_terms . ' ' . $filename;
        $ic_slide->addValue("content", $file_content);
        $di = new DokeosIndexer();
        isset($_POST['language']) ? $lang = Database::escape_string($_REQUEST['language']) : $lang = 'english';
        $di->connectDb(NULL, NULL, $lang);
        $di->addChunk($ic_slide);
        $did = $di->index();
        if ($did) {
            $sql = 'INSERT INTO %s (id, course_code, tool_id, ref_id_high_level, search_did) 
                    VALUES (NULL , \'%s\', \'%s\', %s, %s)';
            $sql = sprintf($sql, $tble_ref, $course_id, TOOL_DOCUMENT, $document_id, $did);
            Database::query($sql, __FILE__, __LINE__);
        }
    }
}

header($url);
?>