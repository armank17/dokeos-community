
<?php
/* For licensing terms, see /license.txt */
/**
*	@package dokeos.messages
*/


/**
* This script shows a compose area (wysiwyg editor if supported, otherwise
* a simple textarea) where the user can type a message.
* There are three modes
* - standard: type a message, select a user to send it to, press send
* - reply on message (when pressing reply when viewing a message)
* - send to specific user (when pressing send message in the who is online list)
*/
/* 		INIT SECTION	*/
// name of the language file that needs to be included
$language_file= array('messages','userInfo');
$cidReset	= true;
require_once '../inc/global.inc.php';

api_block_anonymous_users();

if (api_get_setting('allow_message_tool')!='true'){
	api_not_allowed();
}

require_once api_get_path(LIBRARY_PATH).'text.lib.php';
require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';
require_once api_get_path(LIBRARY_PATH).'group_portal_manager.lib.php';
require_once api_get_path(LIBRARY_PATH).'message.lib.php';
require_once api_get_path(LIBRARY_PATH).'social.lib.php';
$nameTools = api_xml_http_response_encode(get_lang('Messages'));



if (isset($_GET['re_id'])) {
    $message_reply_info = MessageManager::get_message_by_id($_GET['re_id']);
    $user_reply_info    = UserManager::get_user_info_by_id($message_reply_info['user_sender_id']);
}



/*	Constants and variables */
$htmlHeadXtra[] = '<script type="text/javascript">

function show_icon_edit(element_html) {
	ident="#edit_image";
	$(ident).show();
}

function hide_icon_edit(element_html)  {
	ident="#edit_image";
	$(ident).hide();
}

</script>';

$htmlHeadXtra[] = '
	<style type="text/css">
	div.row div.label{
		width: 10%;
                margin-top:15px;
                min-height:25px;
	}
	div.row div.formw{
		width: 85%;
                margin-top:5px;
                min-height:25px;
	}
        div.row div.formw strong {
            display:block;
            margin-top: 10px;
        }

	</style>
	';

$htmlHeadXtra[]='
<script language="javascript">
function validate(form,list) {
	if(list.selectedIndex<0)
	{
    	alert("Please select someone to send the message to.")
    	return false
	}
	else
    	return true
}

</script>';
//$htmlHeadXtra[] = '<script src="../inc/lib/javascript/jquery.js" type="text/javascript" language="javascript"></script>'; //jQuery

$htmlHeadXtra[] = '<script src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/thickbox.js" type="text/javascript" language="javascript"></script>';
$htmlHeadXtra[] = '<link rel="stylesheet" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/thickbox.css" type="text/css" media="projection, screen">';

$htmlHeadXtra[] = '<script src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/tag/jquery.fcbkcomplete.js" type="text/javascript" language="javascript"></script>';
$htmlHeadXtra[] = '<link  href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/tag/style.css" rel="stylesheet" type="text/css" />';

$htmlHeadXtra[] = ' <script src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.multiselect.js" type="text/javascript"></script>
                    <link rel="stylesheet" type="text/css" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.multiselect.css"/>';

$htmlHeadXtra[] = '<script type="text/javascript">
    $(document).ready(function(){
       $("#select3").multiselect({
                                    checkAllText: "'.get_lang('SelectAll').'", uncheckAllText: "'.get_lang('UnSelectAll').'",'
        . '                         noneSelectedText: "'.get_lang('SelectUser').'",'
        . '                         selectedText: "'.'# '.get_lang('SelectedUser').'"
            })
            .on("multiselectclick", function(event, ui) { 
                if(ui.value == parseInt("'. $user_reply_info['user_id'] .'") ){
                    return false;
                }
            });
       $("#btn-search").click(function() {
            if ($("#search").css("display") == "none") {
                $("#keyword").val("");
                $("#search").show();
            } else {
                $("#search").hide("slow")
            }
       });
       $("#comment").css("color","rgba(192,192,192, 0.9)");
       $("#comment").blur(function(){
            val = $(this).val();
            if(val==""){
            $("#comment").css("color","rgba(192,192,192, 0.9)");
               $(this).val("'.get_lang("AddNewComment").' ...");
            }
        });
        $("#comment").focus(function(){
            val = $(this).val();
            $("#comment").css("color","black");
            if(val=="'.get_lang("AddNewComment").' ..."){
               $(this).val("");
            }
        });
        
    });

var counter_image = 1;
/*
function remove_image_form(id_elem1) {
	var elem1 = document.getElementById(id_elem1);
	elem1.parentNode.removeChild(elem1);
}
*/
function add_image_form() {
	// Multiple filepaths for image form
	var filepaths = document.getElementById("filepaths");
	if (document.getElementById("filepath_"+counter_image)) {
		counter_image = counter_image + 1;
	}  else {
		counter_image = counter_image;
	}
	var elem1 = document.createElement("div");
	elem1.setAttribute("id","filepath_"+counter_image);
	filepaths.appendChild(elem1);
	id_elem1 = "filepath_"+counter_image;
	id_elem1 = "\'"+id_elem1+"\'";
	//document.getElementById("filepath_"+counter_image).innerHTML = "<input type=\"file\" name=\"attach_"+counter_image+"\"  size=\"20\" />&nbsp;<a href=\"javascript:remove_image_form("+id_elem1+")\"><img src=\"'.api_get_path(WEB_CODE_PATH).'img/delete.gif\"></a>";
	document.getElementById("filepath_"+counter_image).innerHTML = "<input type=\"file\" name=\"attach_"+counter_image+"\"  size=\"28\" />&nbsp;<input type=\"text\" name=\"legend[]\" size=\"20\" /></a>";
	if (filepaths.childNodes.length == 6) {
		var link_attach = document.getElementById("link-more-attach");
		if (link_attach) {
			link_attach.innerHTML="";
		}
	}
}

</script>';


$nameTools = get_lang('ComposeMessage');

/*
		FUNCTIONS
*/

/**
* Shows the compose area + a list of users to select from.
*/
function show_compose_to_any ($user_id,$bol) {
	$online_user_list = MessageManager::get_online_user_list($user_id);
	$default['user_list'] = 0;
	$online_user_list=null;
        $bolean=$bol;
	manage_form($default, $online_user_list,$bolean);
}

function show_compose_reply_to_message ($message_id, $receiver_id) {
	global $charset;
        
	$table_message = Database::get_main_table(TABLE_MESSAGE);
	$query = "SELECT user_sender_id FROM $table_message WHERE user_receiver_id=".intval($receiver_id)." AND id='".intval($message_id)."';";
	$result = Database::query($query);
	$row = Database::fetch_array($result,'ASSOC');
	if (!isset($row['user_sender_id'])) {
		echo get_lang('InvalidMessageId');
		die();
	}

	$pre_html = '<div class="row">
				<div class="label">'.get_lang('SendMessageTo').'</div>
				<div class="formw">';
	$post = '</div></div>';
	$multi_select = '<select id="users" name="users">
					 </select>';
	echo $pre_html.'<strong>'.GetFullUserName($row['user_sender_id']).'</strong>'.$post;
	//echo get_lang('To').':&nbsp;<strong>'.	GetFullUserName($row['user_sender_id']).'</strong>';
	//$default['title'] = get_lang('EnterTitle');
	$default['users'] = array($row['user_sender_id']);
	manage_form($default);
}

function show_compose_to_user ($receiver_id) {
	global $charset;
	echo get_lang('To').':&nbsp;<strong>'.	GetFullUserName($receiver_id).'</strong>';
	$default['title'] = api_xml_http_response_encode(get_lang('EnterTitle'));
	$default['users'] = array($receiver_id);
	manage_form($default);
}

function manage_form ($default, $select_from_user_list = null,$bolean) {
	global $charset;
        
	$table_message = Database::get_main_table(TABLE_MESSAGE);
        if(!isset($bolean)){
            $bolean=false;
        }
	$group_id 	= intval($_REQUEST['group_id']);
	$message_id = intval($_GET['message_id']);
	$param_f = isset($_GET['f'])?Security::remove_XSS($_GET['f']):'';

	$form = new FormValidator('compose_message',null,api_get_self().'?f='.$param_f,null,array('enctype'=>'multipart/form-data'));
	if (empty($group_id)) {
            
		if (isset($select_from_user_list)) {
			$form->add_textfield('id_text_name', get_lang('SendMessageTo'),true,array('size' => 30,'id'=>'id_text_name','onkeyup'=>'send_request_and_search()','autocomplete'=>'off','style'=>'padding:0px'));
			$form->addRule('id_text_name', get_lang('ThisFieldIsRequired'), 'required');
			$form->addElement('html','<div id="id_div_search" style="padding:0px" class="message-select-box" >&nbsp;</div>');
			$form->addElement('hidden','user_list',0,array('id'=>'user_list'));
		} else {
			
                            //the magic should be here
                            $pre_html = '<div class="row">
                                                    <div class="label">'.get_lang('SendMessageTo').'</div>
                                                    <div class="formw">';
                            $post = '</div></div>';
                            $multi_select = '<select id="users" name="users">
                                                     </select>';


                            $info=api_get_user_info(api_get_user_id());
                            $statusFriend=false;
                            // Admin or teachers
                            if($info["status"] == COURSEMANAGER){
                                 $query = "select CONCAT(user.lastname,' ',user.firstname) as 'completeName',user.user_id from user where user.status!='".COURSEMANAGER."' AND user.status!='".ANONYMOUS."' ;";
                            } else {
                                // Display all users
                                 $query = "";
                                 $statusFriend = true;
                            }
                  $users_list_option = array();
                  //$users_list_option[0] = "--";
                  $i = 1;
                if($statusFriend){
                    $user_id	= api_get_user_id();
                    $friends = SocialManager::get_friends($user_id);

                    for($j=0;$j<count($friends);$j++){
                        $users_list_option[$friends[$j]["friend_user_id"]]= $friends[$j]["firstName"]." ".$friends[$j]["lastName"];
                    }

                } else {
                    
                   // /$form->addRule('id_text_name', get_lang('ThisFieldIsRequired'), 'required');
                       $rs = Database::query($query);
                       
                        while($row=Database::fetch_array($rs)){
                           $users_list_option[$row["user_id"]] = $row["completeName"];
                           $i++;
                        }
                }
                        $form->addElement('select', 'send_to',get_lang('SendMessageTo'),$users_list_option ,array('style'=> 'width:470px','id'=>'select3', 'multiple'=>'multiple'));
                        $form->addRule('send_to', get_lang('ThisFieldIsRequired'), 'required');

			
		}
	} else {
		$group_info = GroupPortalManager::get_group_data($group_id);
		$form->addElement('html','<div class="row"><div class="label">'.get_lang('ToGroup').'</div><div class="formw">'.api_xml_http_response_encode($group_info['name']).'</div></div>');
		$form->addElement('hidden','group_id',$group_id);
		$form->addElement('hidden','parent_id',$message_id);
	}

	$form->add_textfield('title', get_lang('Title'),true ,array('size' => 55,'class' => 'focus'));

	$form->add_html_editor('msg_content', get_lang('Message'), false, false, array('ToolbarSet' => 'Messages', 'Width' => '98%', 'Height' => '250'));
	//$form->addElement('textarea','msg_content', get_lang('Message'), array('cols' => 75,'rows'=>8));
        
	if (isset($_GET['re_id'])) {
		$message_reply_info = MessageManager::get_message_by_id($_GET['re_id']);
		$form->addElement('hidden','re_id',Security::remove_XSS($_GET['re_id']));
		$form->addElement('hidden','save_form','save_form');

		//adding reply mail
		$user_reply_info = UserManager::get_user_info_by_id($message_reply_info['user_sender_id']);
		$default['msg_content']='<p></p>'.api_get_person_name($user_reply_info['firstname'],$user_reply_info['lastname']).' '.get_lang('Wrote').' :<i> <br />'.api_html_entity_decode($message_reply_info['content'],ENT_QUOTES,$charset).'</i>';
                $default['send_to'] = $user_reply_info['user_id'];
	}
	if (empty($group_id)) {
		$form->addElement('html','<div class="row"><div class="label">'.get_lang('AddedResources').'</div><div class="formw">
				<span id="filepaths">
				<div id="filepath_1">
				<input type="file" name="attach_1"  size="28" />
				<input type="text" name="legend[]" id="comment" size="20" value="'.  get_lang("AddNewComment").' ..."  />
				</div></span></div></div>');
                //AddNewComment
		$form->addElement('html','<div class="row"><div class="formw"><span id="link-more-attach"><a href="javascript://" onclick="return add_image_form()">'.get_lang('AddOneMoreFile').'</a></span>&nbsp;('.sprintf(get_lang('MaximunFileSizeX'),format_file_size(api_get_setting('message_max_upload_filesize'))).')</div></div>');
	}

	$form->addElement('style_submit_button','compose',api_xml_http_response_encode(get_lang('SendMessage')),'class="save"');
	if (!empty($group_id) && !empty($message_id)) {
		$message_info = MessageManager::get_message_by_id($message_id);
		$default['title']=get_lang('Re:').api_html_entity_decode($message_info['title'],ENT_QUOTES,$charset);
	}
	$form->setDefaults($default);
        //$SelectUser
	if ($form->validate()) {
            if(isset($_POST['multiselect_select3'])){
            $_SESSION['sec_token']=$_POST['sec_token'];
            }
		$check = Security::check_token('post');
                
		if ($check && isset($_POST['multiselect_select3'])) {
			$values 		= $default;
			$user_list		= $values['users'];
			$file_comments          = $_POST['legend'];
			$title 			= $values['title'];
			$content 		= $values['msg_content'];
			$group_id		= $values['group_id'];
			$parent_id 		= $values['parent_id'];

                        if (is_array($user_list) && count($user_list)> 0) {
                            $user_list[0] = (!is_array($user_list[0])) ? array($user_list[0]) : $user_list[0];
                            foreach ($user_list[0] as $user) {
                                $res = MessageManager::send_message($user, $title, $content, $_FILES, $file_comments, $group_id, $parent_id);
                                if ($res) {
                                    if (is_string($res)) {
                                        Display::display_error_message($res);
                                    } else {
                                        MessageManager::display_success_message($user);
                                        echo '<br>';
                                    }
                                }
                            }
                        }
		}else{
                    $token = Security::get_token();
                    $form->addElement('hidden','sec_token');
                    $form->setConstants(array('sec_token' => $token));
                    $form->display();
                }
		Security::clear_token();
                
	} else {
            
		$token = Security::get_token();
		$form->addElement('hidden','sec_token');
		$form->setConstants(array('sec_token' => $token));
		$form->display();
	}
}
/*
		MAIN SECTION
*/
if ($_GET['f']=='social') {
	$this_section = SECTION_SOCIAL;
	$interbreadcrumb[]= array ('url' => api_get_path(WEB_PATH).'main/social/home.php','name' => get_lang('Social'));
	$interbreadcrumb[]= array ('url' => '#','name' => $nameTools);
} else {
	$this_section = SECTION_MYPROFILE;
	$interbreadcrumb[]= array ('url' => api_get_path(WEB_PATH).'main/auth/profile.php','name' => get_lang('Profile'));
	$interbreadcrumb[]= array ('url' => 'inbox.php','name' => get_lang('Inbox'));
}

Display::display_header('');

// Display actions
echo '<div class="actions">';
if (api_get_setting('allow_social_tool') == 'true') {
  echo '<a href="'.api_get_path(WEB_PATH).'main/social/home.php">'.Display::return_icon('pixel.gif',get_lang('Home'),array('class' => 'toolactionplaceholdericon toolactionshome')).get_lang('Home').'</a>';
} else {
  $social_parameter = '';
  if ($_GET['f']=='social' || api_get_setting('allow_social_tool') == 'true') {
    $social_parameter = '?f=social';
  } else {
    echo '<a href="'.api_get_path(WEB_PATH).'main/auth/profile.php?type=reduced">'.Display::return_icon('pixel.gif', get_lang('EditNormalProfile'),array('class'=>'actionplaceholdericon actionedit')).'&nbsp;'.get_lang('EditNormalProfile').'</a>';
  }
}
echo '<a href="'.api_get_path(WEB_PATH).'main/messages/inbox.php?f=social">'.Display::return_icon('pixel.gif',get_lang('Inbox'), array('class' => 'toolactionplaceholdericon toolactioninbox')).get_lang('Inbox').'</a>';
echo '<a href="'.api_get_path(WEB_PATH).'main/messages/new_message.php?f=social">'.Display::return_icon('pixel.gif',get_lang('Inbox'), array('class' => 'toolactionplaceholdericon toolactionsinvite')).get_lang('ComposeMessage').'</a>';
echo '<a href="'.api_get_path(WEB_PATH).'main/messages/outbox.php?f=social">'.Display::return_icon('pixel.gif',get_lang('Outbox'), array('class' => 'toolactionplaceholdericon toolactionoutbox')).get_lang('Outbox').'</a>';
$group_id = intval($_REQUEST['group_id']);
if ($group_id != 0) {
	echo '<a href="'.api_get_path(WEB_PATH).'main/social/groups.php?id='.$group_id.'">'.Display::return_icon('back.png',api_xml_http_response_encode(get_lang('ComposeMessage'))).api_xml_http_response_encode(get_lang('BackToGroup')).'</a>';
	echo '<a href="'.api_get_path(WEB_PATH).'main/messages/new_message.php?group_id='.$group_id.'">'.Display::return_icon('message_new.png',api_xml_http_response_encode(get_lang('ComposeMessage'))).api_xml_http_response_encode(get_lang('ComposeMessage')).'</a>';
} else {
	if ($_GET['f']=='social') {
    //
	} else {
		if (api_get_setting('allow_social_tool') == 'true' && api_get_setting('allow_message_tool') == 'true') {
			echo '<a href="'.api_get_path(WEB_PATH).'main/social/profile.php">'.Display::return_icon('shared_profile.png', get_lang('ViewSharedProfile')).'&nbsp;'.get_lang('ViewSharedProfile').'</a>';
		}
	}

}

echo '</div>';
// Start content
echo '<div id="content">';

echo '<div id="social-content" >';
	$id_content_right = '';
	//LEFT COLUMN
	if (api_get_setting('allow_social_tool') != 'true') {
    //
	} else {
		require_once api_get_path(LIBRARY_PATH).'social.lib.php';
		/*echo '<div id="social-content-left">';
			//this include the social menu div
			SocialManager::show_social_menu('messages_compose');
		echo '</div>';*/
		$id_content_right = 'social-content-all';
	}

	echo '<div id="'.$id_content_right.'">';

		//MAIN CONTENT
		if (!isset($_POST['compose'])) {
			if(isset($_GET['re_id'])) {
				show_compose_reply_to_message($_GET['re_id'], api_get_user_id());
			} elseif(isset($_GET['send_to_user'])) {
				show_compose_to_user($_GET['send_to_user']);
			} else {
                            
				show_compose_to_any($_user['user_id']);
		  	}
		} else {

			$restrict = false;
			if (isset($_POST['users'])) {
				$restrict = true;
			} elseif (isset($_POST['group_id'])) {
				$restrict = true;
			} elseif (isset($_POST['send_to']) && $_POST['send_to'] > 0) {
                            $restrict = true;
                        }
			$default['title']	= $_POST['title'];
			$default['msg_content'] = $_POST['msg_content'];

			// comes from a reply button
			if (isset($_GET['re_id'])) {
				manage_form($default);
			} else {
				// post
				if ($restrict) {
					if (!isset($_POST['group_id'])) {
						$default['users']	 = $_POST['users'];
					} else {
						$default['group_id'] = $_POST['group_id'];
					}
					if (isset($_POST['send_to'])) {
						$default['users']	 = array($_POST['send_to']);
					}
					manage_form($default);
				} else {
                                    if(!isset($_POST['multiselect_select3'])){
                                        show_compose_to_any($_user['user_id'],$bol=false);
                                    }
				}
			}
		}
	echo '</div>';
echo '</div>';

// End content
echo '</div>';

// Actions
//echo '<div class="actions">';
//echo '</div>';

/*
		FOOTER
*/
Display::display_footer();

?>