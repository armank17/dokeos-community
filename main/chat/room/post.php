<?php
// Language file
$language_file = array ('registration');
require_once '../../inc/global.inc.php';
include_once(api_get_path(LIBRARY_PATH).'document.lib.php');
include_once(api_get_path(LIBRARY_PATH).'text.lib.php');
include_once (api_get_path(LIBRARY_PATH).'fileUpload.lib.php');
require_once api_get_path(LIBRARY_PATH).'groupmanager.lib.php';

// Get user info
$get_user_info = api_get_user_info(api_get_user_id());
// Assign username to session
$_SESSION['name'] = $get_user_info['username'];
// Create chat file according to course access, for example if users is inside a group or if is out of them
$dateNow=date('Y-m-d');

$session_id = api_get_session_id();
$group_id 	= intval($_GET['group_id']);

$basepath_chat = '';
$documentPath = api_get_path(SYS_COURSE_PATH).$_course['path'].'/document';
if (!empty($group_id)) {
	$group_info = GroupManager :: get_group_properties($group_id);
	$basepath_chat = $group_info['directory'].'/chat_files';
} else {
	$basepath_chat = '/chat_files';
}
$chatPath=$documentPath.$basepath_chat.'/';

$TABLEITEMPROPERTY= Database::get_course_table(TABLE_ITEM_PROPERTY);

if(!is_dir($chatPath)) {
	if(is_file($chatPath)) {
		@unlink($chatPath);
	}
	if (!api_is_anonymous()) {
		$perm = api_get_setting('permissions_for_new_directories');
		$perm = octdec(!empty($perm)?$perm:'0770');
		@mkdir($chatPath,$perm);
		@chmod($chatPath,$perm);
		// save chat files document for group into item property
		if (!empty($group_id)) {
			$doc_id=add_document($_course,$basepath_chat,'folder',0,'chat_files');
			$sql = "INSERT INTO $TABLEITEMPROPERTY (tool,insert_user_id,insert_date,lastedit_date,ref,lastedit_type,lastedit_user_id,to_group_id,to_user_id,visibility)
			VALUES ('document',1,NOW(),NOW(),$doc_id,'FolderCreated',1,$group_id,NULL,0)";
			Database::query($sql,__FILE__,__LINE__);
		}
	}
}

$timeNow=date('d/m/y H:i:s');

$basename_chat = '';
if (!empty($group_id)) {
	$basename_chat = 'messages-'.$dateNow.'_gid-'.$group_id;
} else if (!empty($session_id)) {
	$basename_chat = 'messages-'.$dateNow.'_sid-'.$session_id;
} else {
	$basename_chat = 'messages-'.$dateNow;
}

if (!api_is_anonymous()) {

	if(!file_exists($chatPath.$basename_chat.'.log.html')) {
		$doc_id=add_document($_course,$basepath_chat.'/'.$basename_chat.'.log.html','file',0,$basename_chat.'.log.html');

		api_item_property_update($_course, TOOL_DOCUMENT, $doc_id, 'DocumentAdded', $_user['user_id'],$group_id,null,null,null,$session_id);
		api_item_property_update($_course, TOOL_DOCUMENT, $doc_id, 'invisible', $_user['user_id'],$group_id,null,null,null,$session_id);
		item_property_update_on_folder($_course,$basepath_chat, $_user['user_id']);
	} else {
		$doc_id = DocumentManager::get_document_id($_course,$basepath_chat.'/'.$basename_chat.'.log.html');
	}
	$chat_file = $chatPath.$basename_chat.'.log.html';
	$fp=fopen($chatPath.$basename_chat.'.log.html','a');
	fclose($fp);
	$chat_size=filesize($chatPath.$basename_chat.'.log.html');

	update_existing_document($_course, $doc_id,$chat_size);
	item_property_update_on_folder($_course,$basepath_chat, $_user['user_id']);

}

// Save the chat message
if(isset($_SESSION['name']) && !isset($_POST['action'])){
	$text = $_POST['text'];
	global $_course;
	$fp = fopen($chat_file, 'a');
	fwrite($fp, "<div class='msgln'>(".date("H:i:s").") <b>".$_SESSION['name']."</b>: ".stripslashes(htmlspecialchars($text))."<br></div>");
	fclose($fp);
}
// Save a message when the user left the chat window
if (isset($_POST['action']) && $_POST['action'] == 'close') {
	//Simple exit message
	$fp = fopen($chat_file, 'a');
	fwrite($fp, "<div class='msgln'><i><span>". utf8_encode(get_lang('User'))." ".$_SESSION['name'] ." ".utf8_encode(get_lang('HasLeftTheChatSession')).".</span></i><br></div>");
	fclose($fp);
}
?>