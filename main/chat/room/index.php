<?php
// Language file
$language_file = array ('chat');
// Global inc
require_once '../../../main/inc/global.inc.php';
include_once(api_get_path(LIBRARY_PATH).'document.lib.php');
include_once(api_get_path(LIBRARY_PATH).'text.lib.php');
include_once (api_get_path(LIBRARY_PATH).'fileUpload.lib.php');
require_once api_get_path(LIBRARY_PATH).'groupmanager.lib.php';
require_once api_get_path(LIBRARY_PATH).'online.inc.php';

// Get global course info
global $_course,$_user;

// Create chat file according to course access, for example if users is inside a group or if is out of them
$dateNow=date('Y-m-d');

$session_id = api_get_session_id();
if (isset($_GET['group_id'])) {
  $group_id  =   $_GET['group_id'];
} else if (!isset($_GET['group_id'])) {
  $group_id 	= intval($_SESSION['_gid']);
}
$basepath_chat = '';
$documentPath=api_get_path(SYS_COURSE_PATH).$_course['path'].'/document';
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
// Close create chat file

$get_user_info = array();
$get_user_info = api_get_user_info(api_get_user_id());
$_SESSION['name'] = $get_user_info['username'];

//$chat_file = api_get_path(SYS_COURSE_PATH).$_course['path'].'/chat/log.html';
$emoticons = array(
    array('emot_text' => ':-)', 'emot_img' => Display::return_icon('smileys/icon_smile.png'), get_lang('Smile')),
    array('emot_text' => ':-D', 'emot_img' => Display::return_icon('smileys/icon_biggrin.png'), get_lang('BigGrin')),    
    array('emot_text' => ';-)', 'emot_img' => Display::return_icon('smileys/icon_wink.png'), get_lang('Wink')),
    array('emot_text' => ':-P', 'emot_img' => Display::return_icon('smileys/icon_razz.png'), get_lang('Avid')),
    array('emot_text' => '8-)', 'emot_img' => Display::return_icon('smileys/icon_cool.png'), get_lang('Cool')),    
    array('emot_text' => ':-o)', 'emot_img' => Display::return_icon('smileys/icon_surprised.png'), get_lang('Surprised')),
    array('emot_text' => '=;', 'emot_img' => Display::return_icon('smileys/icon_hand.png'), get_lang('Surprised')),        
    array('emot_text' => ':-k', 'emot_img' => Display::return_icon('smileys/icon_think.png'), get_lang('Think')),    
    array('emot_text' => ':-|)', 'emot_img' => Display::return_icon('smileys/icon_neutral.png'), get_lang('Neutral')),        
    array('emot_text' => ':-?', 'emot_img' => Display::return_icon('smileys/icon_confused.png'), get_lang('Confused')),    
    array('emot_text' => ':-8', 'emot_img' => Display::return_icon('smileys/icon_redface.png'), get_lang('To blush')),   
    array('emot_text' => ':-=', 'emot_img' => Display::return_icon('smileys/icon_shhh.png'), get_lang('Silence')),
    array('emot_text' => ':-#', 'emot_img' => Display::return_icon('smileys/icon_silenced.png'), get_lang('Silenced')),
    array('emot_text' => ':-(', 'emot_img' => Display::return_icon('smileys/icon_sad.png'), get_lang('Sad')),
    array('emot_text' => ':-[8', 'emot_img' => Display::return_icon('smileys/icon_angry.png'), get_lang('Angry')),
    array('emot_text' => '--)', 'emot_img' => Display::return_icon('smileys/icon_arrow.png'), get_lang('Arrow')),
    array('emot_text' => ':!:', 'emot_img' => Display::return_icon('smileys/icon_exclaim.png'), get_lang('Exclamation')),
    array('emot_text' => ':?:', 'emot_img' => Display::return_icon('smileys/icon_question.png'), get_lang('Question')),
    array('emot_text' => '0-', 'emot_img' => Display::return_icon('smileys/icon_idea.png'), get_lang('Idea'))
);
;
$flags = array(
    array('flag_text' => '*', 'flag_img' => Display::return_icon('smileys/waiting.png'), get_lang('AskPermissionSpeak')),
    array('flag_text' => ':speak:', 'flag_img' => Display::return_icon('smileys/flag_green_small.png'), get_lang('GiveTheFloorTo')),    
    array('flag_text' => ':pause:', 'flag_img' => Display::return_icon('smileys/flag_yellow_small.png'), get_lang('Pause')),
    array('flag_text' => ':stop:', 'flag_img' => Display::return_icon('smileys/flag_red_small.png'), get_lang('Stop'))  
); 

?>
<html>
<head>
<style type="text/css">
#usermsg {
	font:12px arial;
	color: #000000;
	padding:5px;
	margin-left:20px;
        margin-bottom: 10px;
	border:1px solid #ACD8F0; 
	width:300px;
}
#wrapperchat, #loginform {
	margin:0 auto;
	padding-bottom:25px;
	width:100%;
	border:1px solid #BDBDBD;
}
#loginform { padding-top:18px; }
#loginform p { margin: 5px; }
#chatbox {
	text-align:left;
	margin-left:20px;
	margin-bottom:25px;
	padding:10px;
	background:#fff;
	height:250px;
	width:400px;
	border:1px solid #ACD8F0;
	overflow:auto; 
	float:left;
}
#chatbox-right{
    height: 272px;
    float:left;
    width: 150px;
    overflow: auto;
}
#chatbox-usersonline {
float:left;
margin-left:10px;
width: 122px;
}
#submit { width: 60px; }
.error { color: #ff0000; }
#menu { padding:12.5px 25px 12.5px 25px; }
.welcome { float:left; }
.msgln { margin:0 0 2px 0; }
.save_chat{
    padding:5px;
    margin-left:10px;
    margin-top:-4px;
}
</style>
<script type="text/javascript" language="javascript">
function insert(text) {
    var chat = document.message.usermsg;
    if (chat.createTextRange && chat.smile) {
        var smile = chat.smile;
        smile.text = smile.text.charAt(smile.text.length - 1) == ' ' ? text + ' ' : text;
    }
    else chat.value += text;
        chat.focus(smile)
    }
</script>

</head>
<body>
<div id="wrapperchat">
	<div id="menu">
		<p class="welcome"><?php echo get_lang('Welcome');?>, <b><?php echo $_SESSION['name']; ?></b></p>
		<div style="clear:both"></div>
	</div>	
	<div class="chatbox-wrapprer">
	<div id="chatbox"><?php
	if(file_exists("$chat_file") && filesize("$chat_file") > 0){
		$handle = fopen("$chat_file", "r");
		$contents = fread($handle, filesize("$chat_file"));
		fclose($handle);                
                foreach ($emoticons as $emoticon) {
                    $contents = str_replace($emoticon['emot_text'], $emoticon['emot_img'], $contents);   
                }
                foreach ($flags as $flag) {
                    $contents = str_replace($flag['flag_text'], $flag['flag_img'], $contents);   
                }
		echo $contents;
	}
	?></div>
        <div id="chatbox-right"> 
            <div  id="chatbox-usersonline"><?php
            $whoisonline = Who_is_online_in_this_course($_user['user_id'], 1, $_course['id']);
            foreach ($whoisonline as $useronline) {
                    $all_user_info =  api_get_user_info($useronline['0']);
                    echo '<strong>'.Display::return_icon('pixel.gif',$all_user_info['username'],array('class' => 'actionplaceholdericon actioninfo') ).$all_user_info['username'].'</strong><br>';
            }
            ?></div>
        </div>
	</div>
	<form name="message">
		<input name="usermsg" type="text" id="usermsg" value=""/>
		<button class="save_chat" style="float:none;" name="submitmsg" type="submit"  id="submitmsg" ><?php echo get_lang('Send');?></button>
                <br>&nbsp;&nbsp;&nbsp;&nbsp;
                    <?php                    
                        foreach ($emoticons as $emoticon) {
                            echo "<a class='chat' href=\"javascript:insert('".$emoticon['emot_text']."')\">".$emoticon['emot_img']."</a>";                            
                        }   
                    ?>
                    <?php
                        foreach ($flags as $flag) {
                            echo "<a class='chat' href=\"javascript:insert('".$flag['flag_text']."')\">".$flag['flag_img']."</a>";                            
                        }  
                    ?>
             
	</form>
</div>
</body>
</html>
