<?php

/* For licensing terms, see /dokeos_license.txt */

/**
==============================================================================
*	The Dokeos wiki is a further development of the CoolWiki plugin.
*
*	@Author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
* 	@Author Juan Carlos Raña <herodoto@telefonica.net>
*	@Copyright Ghent University
*	@Copyright Patrick Cool
*
* 	@package dokeos.wiki
==============================================================================
*/

// name of the language file that needs to be included
$language_file = 'wiki';

// security
if(isset($_GET['id_session'])) {
	$_SESSION['id_session'] = intval($_GET['id_session']);
}

// including the global dokeos file
include('../inc/global.inc.php');
// section (for the tabs)
$this_section=SECTION_COURSES;

// including additional library scripts
require_once api_get_path(LIBRARY_PATH).'course.lib.php';
require_once api_get_path(LIBRARY_PATH).'groupmanager.lib.php';
require_once api_get_path(LIBRARY_PATH).'text.lib.php';
require_once api_get_path(LIBRARY_PATH).'security.lib.php';
require_once api_get_path(INCLUDE_PATH).'lib/mail.lib.inc.php';
require_once api_get_path(INCLUDE_PATH).'conf/mail.conf.php';
require_once api_get_path(LIBRARY_PATH).'sortabletable.class.php';
require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';
require_once api_get_path(LIBRARY_PATH).'wikimanager.lib.php';
require_once api_get_path(LIBRARY_PATH).'timezone.lib.php';
require_once 'wiki.inc.php';
require_once api_get_path(LIBRARY_PATH)  . 'searchengine.lib.php';

define('DOKEOS_WIKI', true);

// additional style information
$htmlHeadXtra[] ='<link rel="stylesheet" type="text/css" href="'.api_get_path(WEB_CODE_PATH).'wiki/css/default.css"/>';
//$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-1.4.2.min.js" language="javascript"></script>';
//$htmlHeadXtra[] = '<link type="text/css" rel="stylesheet" href="'.api_get_path(WEB_PATH).'main/appcore/library/jquery/jquery.alerts/jquery.alerts.css" />';
//$htmlHeadXtra[] = '<script  type="text/javascript" src="'.api_get_path(WEB_PATH).'main/appcore/library/jquery/jquery.alerts/jquery.alerts.js"></script>';
//$htmlHeadXtra[] = '<script type="text/javascript">
//    function Jalert(link,lang,title){
//            jConfirm(lang, title, function(r) {
//                window.location.href = link;
//            });
//    }
//</script>';
if (api_get_setting('show_glossary_in_documents') != 'none' && (isset($_GET['action']) && $_GET['action'] == 'showpage' || $_GET['action'] == 'show') || (!isset($_GET['action']))) {
  $htmlHeadXtra[] = '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.highlight.js"></script>';
  if (api_get_setting('show_glossary_in_documents') == 'ismanual') {
//    $htmlHeadXtra[] = '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'fckeditor/editor/plugins/glossary/fck_glossary_manual.js"></script>';
    $htmlHeadXtra[] = '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/glossary_wiki.js"/></script>';
  } else {
    $htmlHeadXtra[] = '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/glossary_wiki.js"/></script>';
  }
}
$htmlHeadXtra[] = '<script type="text/javascript">$(document).ready( function() { $("#wiki_display_content").append($(".warning-message-lib")); });</script>';
$htmlHeadXtra[] = '<script type="text/javascript">
$(document).ready( function() {
      $(".user_info").click(function () {
        var myuser_id = $(this).attr("id");        
        var user_info_id = myuser_id.split("user_id_");
        my_user_id = user_info_id[1];
        $("<div style=\'display:none;\' title=\''.get_lang('UserInfo').'\' id=\'html_user_info\'></div>").insertAfter(this);
        $.ajax({
            url: "'.api_get_path(WEB_AJAX_PATH).'user_manager.ajax.php?a=get_user_info&user_id="+my_user_id,
            success: function(data){
                //var dialog_div = $("<div id=\'html_user_info\'></div>");
                //dialog_div.html(data);
                $(\'#html_user_info\').html(\'<div style="text-align:justify;width:100%;max-height:580px">\'+data+\'</div>\');
                $("#html_user_info").dialog({
                    open: function(event, ui) {  
                                            $(".ui-dialog-titlebar-close").css("width","0px");
                                            $(".ui-dialog-titlebar-close").html("<span style=\"float:right;margin-right:5px;\">'.get_lang("Close").'</span>");  											
                                    },
                    autoOpen: true,
                modal: true,
                title: "'.get_lang('UserInfo').'",
                    closeText: "'.get_lang('Close').'",
                width: 640,
                height : 240,
                resizable:false
                });
            }
        });
    });
   
});
</script>'  ;

// Set the functions for each editor type
$editor_type = api_get_setting('use_default_editor');

if($editor_type=='Fckeditor'){

$htmlHeadXtra[] = '<script language="javascript">
 function FCKeditor_OnComplete( editorInstance ) {
    $(".ck-loading textarea").css("visibility", "visible");
    if ($(\'.ck-icon-loading\').length > 0) {
        $(\'.ck-icon-loading\').remove();
    }
    // Get the fck instance
    _currentEditor = editorInstance;
	// automated event loaded by each fckeditor area when loaded
     editorInstance.Events.AttachEvent( "OnSelectionChange", takeFocus ) ;
}

var _currentEditor;

function takeFocus(editor){
_currentEditor = editor
}

function makeitbold(){
_currentEditor.Commands.GetCommand("Bold").Execute();

}
function makeititalic(){
_currentEditor.Commands.GetCommand("Italic").Execute();

}
function underline(){
_currentEditor.Commands.GetCommand("Underline").Execute();

}

function word(){
_currentEditor.Commands.GetCommand("PasteWord").Execute();

}
function link(){
_currentEditor.Commands.GetCommand("Link").Execute();

}
function unlink(){
_currentEditor.Commands.GetCommand("Unlink").Execute();

}
function image(){
_currentEditor.Commands.GetCommand("Image").Execute();

}
function mindmap(){
_currentEditor.Commands.GetCommand("MindmapManager").Execute();

}
function mascot(){
_currentEditor.Commands.GetCommand("MascotManager").Execute();

}
function flash(){
_currentEditor.Commands.GetCommand("Flash").Execute();

}
function embedmovies(){
_currentEditor.Commands.GetCommand("EmbedMovies").Execute();

}
function audio(){
_currentEditor.Commands.GetCommand("MP3").Execute();

}
function table(){
_currentEditor.Commands.GetCommand("Table").Execute();

}
function unordered(){
_currentEditor.Commands.GetCommand("InsertUnorderedList").Execute();

}
function source(){
_currentEditor.Commands.GetCommand("Source").Execute();

}
function alignleft(){
_currentEditor.Commands.GetCommand("JustifyLeft").Execute();

}
function aligncenter(){
_currentEditor.Commands.GetCommand("JustifyCenter").Execute();

}
function alignright(){
_currentEditor.Commands.GetCommand("JustifyRight").Execute();

}
function flvplayer(){
_currentEditor.Commands.GetCommand("flvPlayer").Execute();

}
function imagemap(){
_currentEditor.Commands.GetCommand("imgmapPopup").Execute();

}
function fontcolor(event){
event = $.event.fix(event);
_currentEditor.Commands.GetCommand("TextColor").Execute(-120,20,event.target);

}

function glossary(){
_currentEditor.Commands.GetCommand("Glossary").Execute();

}
function youtube(){
_currentEditor.Commands.GetCommand("YouTube").Execute();
}
function videoplayer(){
_currentEditor.Commands.GetCommand("videoPlayer").Execute();
}
function fontsize() {
var font_option = document.form1.font.value;
document.form1.font.selectedIndex = -1;
//var selection = (_currentEditor.EditorWindow.getSelection ? _currentEditor.EditorWindow.getSelection() : _currentEditor.EditorDocument.selection);
var selection = "";
if(_currentEditor.EditorDocument.selection != null) {
  selection = _currentEditor.EditorDocument.selection.createRange().text;
}
else {
  selection = _currentEditor.EditorWindow.getSelection();
}
var new_selection = "<span style=\"font-size:"+font_option+";\">"+selection+"</span>";
_currentEditor.InsertHtml(new_selection);
}

function wiki_link(){
var oEditor = FCKeditorAPI.GetInstance(\'wiki_content\');

var selection = (oEditor.EditorWindow.getSelection ? String(oEditor.EditorWindow.getSelection()) : oEditor.EditorDocument.selection.createRange().text);

    if((selection == "") || (selection.indexOf("[[")!= -1) || (selection.indexOf("]]")!= -1) ){
       return;
    }else{
        var new_selection = "[["+selection;
        var text_selected = new_selection.replace(" ","");
        var str = text_selected + "]]&nbsp;";

        oEditor.InsertHtml(str);
}

//_currentEditor.Commands.GetCommand("Wikilink").Execute();
}
</script>';
}else {
 $htmlHeadXtra[] = '<script type="text/javascript">
        $(document).ready(function() {
            try {
            execute_ckeditor = false;
             if (CKEDITOR.instances.length > 0) {
             execute_ckeditor = true;
             }
            } catch (e) {return false;}
            
            
             
            if ($(\'table[class="toolbar_style_back"]\').length>0){

            //menu bar fixed
            var sticky_navigation_offset_top = $(\'table[class="toolbar_style_back"]\').offset().top;
            var sticky_navigation = function(){
                var scroll_top = $(window).scrollTop(); // our current vertical position from the top
                if (scroll_top > sticky_navigation_offset_top) {
                    $(\'table[class="toolbar_style_back"]\').css({ "position": "fixed", "top":0, width:938, "z-index":9999 });
                } else {
                    $(\'table[class="toolbar_style_back"]\').css({ "position": "relative" });
                }
            };

            // run our function on load
            sticky_navigation();

            // and run it again every time you scroll
            $(window).scroll(function() {
                sticky_navigation();
            });
            
            }

            for(var id in CKEDITOR.instances) {
                CKEDITOR.instances[id].on("focus", function(e) {
                    takeFocus(e.editor);
                });
             }

          });

            function wiki_link(){

            // Get the editor instance current

            var oEditor = CKEDITOR.instances["wiki_content"];

                // Get the selected inside of the editor

                var mySelection = oEditor.getSelection();

                // If the browser used is internet explorer

                if (CKEDITOR.env.ie) {
                    mySelection.unlock(true);
                    selection = mySelection.getNative().createRange().text;
                } else {
                    selection = mySelection.getNative();
                }

                // Set to string the selected for check if is changed already

                var selected = selection.toString();

                if((selection == "") || (selected.indexOf("[[")!= -1) || (selected.indexOf("]]")!= -1) ){
                    return;
                }else{

                // Set the new selection

                var new_selection = "[["+selection;
                var text_selected = new_selection.replace(" "," ");
                text_selected = text_selected.replace(/^\s+|\s+$/g,"");
                var str = text_selected + "]]&nbsp;";

                // Set the new content inside of the editor

                oEditor.insertHtml(str);
                }
            }

		function highlineform() {
            document.getElementById("cke_contents_questionName").style.height = "150px";
            var questiontype = document.question_admin_form.questiontype.value;

            if(questiontype != 6) {
                if(questiontype == 4 || questiontype == 5) {
                    document.getElementById("cke_contents_questionDescription").style.height = "400px";
                } else {
                    document.getElementById("cke_contents_questionDescription").style.height = "520px";
                }
            }

            if(questiontype != "4") {
				document.question_admin_form.formsize.value = "High";
				if(questiontype == "6") {
					var nb_matches = document.question_admin_form.nb_matches.value;
					var nb_options = document.question_admin_form.nb_options.value;

					for(var i=1;i<=nb_matches;i++) {
						document.getElementById("nos["+i+"]").style.height = "160px";
						document.getElementById("cke_contents_answer["+i+"]").style.height = "150px";
					}

					for(var i=1;i<=nb_options;i++) {
						document.getElementById("alpha["+i+"]").style.height = "160px";
						document.getElementById("cke_contents_option["+i+"]").style.height = "150px";
					}
				} else {
					var nb_answers = document.question_admin_form.nb_answers.value;

					for(var i=1;i<=nb_answers;i++) {
						document.getElementById("cke_contents_answer["+i+"]").style.height = "150px";
					}
				}
			}
			if(questiontype != 5) {
				document.getElementById("cke_contents_comment[1]").style.height = "150px";
				document.getElementById("cke_contents_comment[2]").style.height = "150px";
			}
			if(questiontype == 4) {
				document.getElementById("cke_contents_answer").style.height = "400px";
			}
        }

        function lowlineform() {
            document.getElementById("cke_contents_questionName").style.height = "80px";
            var questiontype = document.question_admin_form.questiontype.value;
            if(questiontype != 6) {
                document.getElementById("cke_contents_questionDescription").style.height = "300px";
            }

            if(questiontype != "4") {
				document.question_admin_form.formsize.value = "Low";
				if(questiontype == "6")
				{
					var nb_matches = document.question_admin_form.nb_matches.value;
					var nb_options = document.question_admin_form.nb_options.value;
					for(var i=1;i<=nb_matches;i++)
					{
						document.getElementById("nos["+i+"]").style.height = "90px";
						document.getElementById("cke_contents_answer["+i+"]").style.height = "80px";
					}

					for(var i=1;i<=nb_options;i++)
					{
						document.getElementById("alpha["+i+"]").style.height = "92px";
						document.getElementById("cke_contents_option["+i+"]").style.height = "80px";
					}
				}
				else
				{
					var nb_answers = document.question_admin_form.nb_answers.value;

					for(var i=1;i<=nb_answers;i++)
					{
						document.getElementById("cke_contents_answer["+i+"]").style.height = "80px";
					}
				}
			}
			if(questiontype != 5)
			{
				document.getElementById("cke_contents_comment[1]").style.height = "80px";
				document.getElementById("cke_contents_comment[2]").style.height = "80px";
			}
			if(questiontype == 4)
			{
				document.getElementById("cke_contents_answer").style.height = "250px";
			}
        }

	function takeFocus(editor){
	  _currentEditor = editor
	}

	function makeitbold(){
            var _currentEditor = CKEDITOR.instances["wiki_content"];
          _currentEditor.execCommand("bold");
	}

	function word(){
            var _currentEditor = CKEDITOR.instances["wiki_content"];
          _currentEditor.execCommand("pastefromword");
	}
	function link(){
            var _currentEditor = CKEDITOR.instances["wiki_content"];
          _currentEditor.execCommand("link");
	}
	function image(){
            var _currentEditor = CKEDITOR.instances["wiki_content"];
            _currentEditor.execCommand("imagemanager");
	}
	function mindmap(){
            var _currentEditor = CKEDITOR.instances["wiki_content"];
            _currentEditor.execCommand("mindmaps");
	}
	function mascot(){
            var _currentEditor = CKEDITOR.instances["wiki_content"];
            _currentEditor.execCommand("mascotmanager");
	}
	function flash(){
            var _currentEditor = CKEDITOR.instances["wiki_content"];
            _currentEditor.execCommand("flash");
	}
	function embedmovies(){
            var _currentEditor = CKEDITOR.instances["wiki_content"];
            _currentEditor.execCommand("flash");
	}
	function audio(){
            var _currentEditor = CKEDITOR.instances["wiki_content"];
            _currentEditor.execCommand("Audio");
	}
	function table(){
            var _currentEditor = CKEDITOR.instances["wiki_content"];
            _currentEditor.execCommand("table");
	}
	function unordered(){
            var _currentEditor = CKEDITOR.instances["wiki_content"];
            _currentEditor.execCommand("bulletedlist");
	}
	function source(){
            var _currentEditor = CKEDITOR.instances["wiki_content"];
            _currentEditor.execCommand("source");
	}
	function alignleft(){
            var _currentEditor = CKEDITOR.instances["wiki_content"];
            _currentEditor.execCommand("justifyleft");
	}
	function aligncenter(){
            var _currentEditor = CKEDITOR.instances["wiki_content"];
            _currentEditor.execCommand("justifycenter");
	}
	function alignright(){
            var _currentEditor = CKEDITOR.instances["wiki_content"];
            _currentEditor.execCommand("justifyright");
	}
        function videoplayer(){
            var _currentEditor = CKEDITOR.instances["wiki_content"];
            _currentEditor.execCommand("videoplayer");
	}

	function imagemap(){
            var _currentEditor = CKEDITOR.instances["wiki_content"];
            _currentEditor.execCommand("imgmap");
	}

	function fontcolor(event){
            var _currentEditor = CKEDITOR.instances["wiki_content"];
            event = $.event.fix(event);
            _currentEditor.openDialog( "colordialog", function(event)
            {
                    this.on( "ok", onColorDialogClose );
                    this.on( "cancel", onColorDialogClose );
            } );
	}

        function onColorDialogClose(evt)
        {
            var _currentEditor = CKEDITOR.instances["wiki_content"];
            value = this.getContentElement( "picker", "selectedColor").getValue();
            _currentEditor.focus();
           _currentEditor.fire("saveSnapshot");
            var styles = new CKEDITOR.style({
               element : "span",
               styles : { "color" : value },
               overrides : [
                  {
                     element : "font",
                     attributes : { "color" : null }
                  }
               ]
            }, {color: "#000000"});
            styles.apply( _currentEditor.document );
            _currentEditor.fire("saveSnapshot");
        }

	function fontsize(value) {
            var _currentEditor = CKEDITOR.instances["wiki_content"];
           _currentEditor.focus();
           _currentEditor.fire("saveSnapshot");
            var styles = new CKEDITOR.style({
               element : "span",
               styles : { "font-size" : value },
               overrides : [
                  {
                     element : "font",
                     attributes : { "size" : null }
                  }
               ]
            }, {size: "72px"});
            styles.apply( _currentEditor.document );
            _currentEditor.fire("saveSnapshot");
	}
	</script>';
}
// Load search term
$htmlHeadXtra[] = '<script language="javascript">
$(document).ready(function() {
	$("#wiki_search_id").click(function() {
    var current_action = $("#wiki_search").attr("action")
    var new_current_action = current_action+"&search_term="+$("#search_term_id").val();
	 $("#wiki_search").attr("action",new_current_action);
	});
});
</script>';
$htmlHeadXtra[] = '<style>
    #courseintroduction {
    margin-top:5px;
}
</style>
    ';
//move the display message of content
$htmlHeadXtra[] = '<script type="text/javascript">
$(document).ready( function() {
    $(".confirmation-message2").hide();
    $("#content").before($(".confirmation-message2"));
    $(".confirmation-message2").show();
});
</script>';
// Database table definition
$tbl_wiki = Database::get_course_table(TABLE_WIKI);
$tbl_wiki_discuss = Database::get_course_table(TABLE_WIKI_DISCUSS);
$tbl_wiki_mailcue = Database::get_course_table(TABLE_WIKI_MAILCUE);
$tbl_wiki_conf = Database::get_course_table(TABLE_WIKI_CONF);
/*
-----------------------------------------------------------
Constants and variables
-----------------------------------------------------------
*/
$tool_name = get_lang('Wiki');
$group_member_with_wiki_rights = true;

$MonthsLong = array (get_lang("JanuaryLong"), get_lang("FebruaryLong"), get_lang("MarchLong"), get_lang("AprilLong"), get_lang("MayLong"), get_lang("JuneLong"), get_lang("JulyLong"), get_lang("AugustLong"), get_lang("SeptemberLong"), get_lang("OctoberLong"), get_lang("NovemberLong"), get_lang("DecemberLong"));
//condition for the session
	$session_id = api_get_session_id();
	$condition_session = api_get_session_condition($session_id,true,true);

/*
----------------------------------------------------------
ACCESS
-----------------------------------------------------------
*/
        
api_protect_course_script();
if($_SESSION['_user']['user_id']!=2){
api_block_anonymous_users();
}


/*
-----------------------------------------------------------
TRACKING
-----------------------------------------------------------
*/
event_access_tool(TOOL_WIKI);

/*
-----------------------------------------------------------
HEADER & TITLE
-----------------------------------------------------------
*/

// If it is a group wiki then the breadcrumbs will be different.
if ($_SESSION['_gid'] OR $_GET['group_id']) {

	if (isset($_SESSION['_gid'])) {
		$_clean['group_id']=(int)$_SESSION['_gid'];
	}
	if (isset($_GET['group_id'])) {
		$_clean['group_id']=(int)Database::escape_string($_GET['group_id']);
	}

	$group_properties  = GroupManager :: get_group_properties($_clean['group_id']);
	$interbreadcrumb[] = array ("url" => "../group/group.php", "name" => get_lang('Groups'));
	$interbreadcrumb[] = array ("url"=>"../group/group_space.php?".api_get_cidreq()."&gidReq=".$_SESSION['_gid'], "name"=> get_lang('GroupSpace').' ('.$group_properties['name'].')');

	$add_group_to_title = ' ('.$group_properties['name'].')';
	$groupfilter='group_id="'.$_clean['group_id'].'"';

	//ensure this tool in groups whe it's private or deactivated
	if 	($group_properties['wiki_state']==0)
	{
		echo api_not_allowed();
	}
	elseif ($group_properties['wiki_state']==1)
	{
 		if (!api_is_allowed_to_edit(false,true) and !GroupManager :: is_user_in_group($_user['user_id'], $_SESSION['_gid']))
		{
			//echo api_not_allowed();
			$group_member_with_wiki_rights = false;
		}
	}

}
else
{
	$groupfilter='group_id=0';
}
// We have creating a new wiki page
if(isset($_GET['action']) && $_GET['action']=='showpage' && $_GET['title'] == '' && $_GET['page_id'] == ''){
   $saved = save_new_wiki($page_id,$title);
   if (is_numeric($saved)) {
       $page_id = $saved;
       header('Location:index.php?'.api_get_cidreq().'&action=showpage&page_id='.$page_id.'&title='.$title.'&group_id='.$_clean['group_id']);
       exit;
   }
}

Display::display_tool_header($tool_name, 'Wiki');

$is_allowed_to_edit = api_is_allowed_to_edit(false,true);

//api_display_tool_title($tool_name.$add_group_to_title);

/*
-----------------------------------------------------------
INITIALISATION
-----------------------------------------------------------
*/
//the page we are dealing with
$home_page_id = get_wiki_home_page_id();
if (!isset($_GET['title'])){
	$page='index';
 $page_id = $home_page_id;
} else {
	$page=Security::remove_XSS($_GET['title']);
	$page_id=Security::remove_XSS($_GET['page_id']);
	 if (isset($_GET['get_wiki_link']) && $_GET['get_wiki_link'] == 'true') {
	   $page_id = get_wiki_home_page_id_by_page_title($page);
	 }
}

// some titles are not allowed
// $not_allowed_titles=array("Index", "RecentChanges","AllPages", "Categories"); //not used for now

/*
==============================================================================
MAIN CODE
==============================================================================
*/

// Tool introduction
Display::display_introduction_section(TOOL_WIKI);


/*
-----------------------------------------------------------
  			ACTIONS
-----------------------------------------------------------
*/


//release of blocked pages to prevent concurrent editions
$sql='SELECT * FROM '.$tbl_wiki.'WHERE is_editing!="0" '.$condition_session;
$result=Database::query($sql,__LINE__,__FILE__);
while ($is_editing_block=Database::fetch_array($result))
{
	$max_edit_time=1200; // 20 minutes
	$timestamp_edit=strtotime($is_editing_block['time_edit']);
	$time_editing=time()-$timestamp_edit;

	//first prevent concurrent users and double version
	if($is_editing_block['is_editing']==$_user['user_id'])
	{
		$_SESSION['_version']=$is_editing_block['version'];
	}
	else
	{
		unset ( $_SESSION['_version'] );
	}
	//second checks if has exceeded the time that a page may be available or if a page was edited and saved by its author
	if ($time_editing>$max_edit_time || ($is_editing_block['is_editing']==$_user['user_id'] && $_GET['action']!='edit'))
	{
		$sql='UPDATE '.$tbl_wiki.' SET is_editing="0", time_edit="0000-00-00 00:00:00" WHERE is_editing="'.$is_editing_block['is_editing'].'" '.$condition_session;
		Database::query($sql,__FILE__,__LINE__);
	}

}

// saving a change
if (isset($_POST['SaveWikiChange']) AND $_POST['title']<>'') {
	if(empty($_POST['title'])) {
		//Display::display_error_message(get_lang("NoWikiPageTitle"));
                $_SESSION["display_error_message"]=get_lang("NoWikiPageTitle");
	} elseif(!double_post($_POST['wpost_id'])) {
		//double post
	} elseif ($_POST['version']!='' && $_SESSION['_version']!=0 && $_POST['version']!=$_SESSION['_version']) {
		//prevent concurrent users and double version
		//Display::display_error_message(get_lang("EditedByAnotherUser"));
                $_SESSION["display_error_message"]=get_lang("EditedByAnotherUser");
	} else {
  // Save the wiki home page
		$return_message=save_wiki($page_id,$title);
		//Display::display_confirmation_message($return_message, false);
                $_SESSION["display_confirmation_message"]=$return_message;
	}
}


//saving a new wiki entry
if (isset($_POST['SaveWikiNew']))
{
	if(empty($_POST['title']))
	{
		//Display::display_error_message(get_lang("NoWikiPageTitle"));
                $_SESSION["display_error_message"]=get_lang("NoWikiPageTitle");
	}
	elseif (strtotime(get_date_from_select('startdate_assig')) > strtotime(get_date_from_select('enddate_assig')))
	{
		//Display::display_error_message(get_lang("EndDateCannotBeBeforeTheStartDate"));
                $_SESSION["display_error_message"]=get_lang("EndDateCannotBeBeforeTheStartDate");
	}
	elseif(!double_post($_POST['wpost_id']))
	{
		//double post
	}
	else
	{
	   $_clean['assignment']=Database::escape_string($_POST['assignment']); // for mode assignment
	   if ($_clean['assignment']==1)
	   {
	      	auto_add_page_users($_clean['assignment']);
	   }
	   else
	   {
			$return_message=save_new_wiki($page_id,$title);
			//Display::display_confirmation_message($return_message, false);
			 $page=Security::remove_XSS($_POST['reflink']);
    if (isset($_POST['page_id']) && $_POST['page_id'] > 0) {
      $page_id = Security::remove_XSS($_POST['page_id']);
    }
	   }
	}
}


// check last version
if ($_GET['view'])
{
	$sql='SELECT * FROM '.$tbl_wiki.'WHERE id="'.Database::escape_string($_GET['view']).'"'; //current view
		$result=Database::query($sql,__LINE__,__FILE__);
		$current_row=Database::fetch_array($result);

	$sql='SELECT * FROM '.$tbl_wiki.'WHERE page_id='.Database::escape_string($page_id).' AND '.$groupfilter.$condition_session.' ORDER BY id DESC'; //last version
		$result=Database::query($sql,__LINE__,__FILE__);
		$last_row=Database::fetch_array($result);

	if ($_GET['view']<$last_row['id'])
	{
	   $message= '<center>'.'<br />'.get_lang('NoAreSeeingTheLastVersion').'<br />'.get_lang("Version").' (<a href="index.php?cidReq='.$_course[id].'&action=showpage&page_id='.$page_id.'&title='.$current_row['reflink'].'&view='.Security::remove_XSS($_GET['view']).'&group_id='.$current_row['group_id'].'" title="'.get_lang('CurrentVersion').'">'.$current_row['version'].'</a> / <a href="index.php?cidReq='.$_course[id].'&action=showpage&page_id='.$page_id.'&title='.$last_row['reflink'].'&group_id='.$last_row['group_id'].'" title="'.get_lang('LastVersion').'">'.$last_row['version'].'</a>) <br />'.get_lang("ConvertToLastVersion").': <a href="index.php?cidReq='.$_course[id].'&action=restorepage&title='.$last_row['reflink'].'&page_id='.$page_id.'&view='.Security::remove_XSS($_GET['view']).'">'.get_lang("Restore").'</a></center>';

	   //Display::display_warning_message($message,false);
	   echo '<div class="outer_form"><div class="sectioncontent">'.$message.'</div></div>';
	}

	///restore page
	if ($_GET['action']=='restorepage')
	{
		//Only teachers and platform admin can edit the index page. Only teachers and platform admin can edit an assignment teacher
		if(($current_row['reflink']=='index' || $current_row['reflink']=='' || $current_row['assignment']==1) && (!api_is_allowed_to_edit(false,true) && $_clean['group_id']==0))
		{
			//Display::display_normal_message(get_lang('OnlyEditPagesCourseManager'));
			echo get_lang('OnlyEditPagesCourseManager');
		}
		else
		{
			$PassEdit=false;

			//check if is a wiki group
			if($current_row['group_id']!=0)
			{
				//Only teacher, platform admin and group members can edit a wiki group
				if(api_is_allowed_to_edit(false,true) || api_is_platform_admin() || GroupManager :: is_user_in_group($_user['user_id'],$_SESSION['_gid']))
				{
					$PassEdit=true;
				}
				else
				{
					Display::display_normal_message(get_lang('OnlyEditPagesGroupMembers'), false, true);
				}
			}
			else
			{
				$PassEdit=true;
			}

			// check if is an assignment
			if($current_row['assignment']==1)
			{
				Display::display_normal_message(get_lang('EditAssignmentWarning'), false, true);
				$icon_assignment='<img src="../img/wiki/assignment.gif" title="'.get_lang('AssignmentDescExtra').'" alt="'.get_lang('AssignmentDescExtra').'" />';
			}
			elseif($current_row['assignment']==2)
			{
				$icon_assignment='<img src="../img/wiki/works.gif" title="'.get_lang('AssignmentWorkExtra').'" alt="'.get_lang('AssignmentWorkExtra').'" />';
				if((api_get_user_id()==$current_row['user_id'])==false)
				{
					if(api_is_allowed_to_edit(false,true) || api_is_platform_admin())
					{
						$PassEdit=true;
					}
					else
					{
						//Display::display_warning_message(get_lang('LockByTeacher'), false, true);
                                                $_SESSION["display_warning_message"]=get_lang('LockByTeacher');
						$PassEdit=false;
					}
				}
				else
				{
					$PassEdit=true;
				}
			}

			if($PassEdit) //show editor if edit is allowed
			{
				if ($row['editlock']==1 && (api_is_allowed_to_edit(false,true)==false || api_is_platform_admin()==false))
				{
					   Display::display_normal_message(get_lang('PageLockedExtra'), false, true);
				}
				else
				{
					if($last_row['is_editing']!=0 && $last_row['is_editing']!=$_user['user_id'])
					{
						//checking for concurrent users
						$timestamp_edit=strtotime($last_row['time_edit']);
						$time_editing=time()-$timestamp_edit;
						$max_edit_time=1200; // 20 minutes
						$rest_time=$max_edit_time-$time_editing;

						$userinfo=Database::get_user_info_from_id($last_row['is_editing']);

						$is_being_edited= get_lang('ThisPageisBeginEditedBy').' <a href=../user/userInfo.php?'.api_get_cidreq().'&uInfo='.$userinfo['user_id'].'>'.api_get_person_name($userinfo['firstname'], $userinfo['lastname']).'</a>. '.get_lang('ThisPageisBeginEditedTryLater').' '.date( "i",$rest_time).' '.get_lang('MinMinutes').'';
						Display::display_normal_message($is_being_edited, false, true);

					}
					else
					{
					 	//echo '<div class="confirmation-message rounded">'.restore_wikipage($current_row['page_id'], $current_row['reflink'], $current_row['title'], $current_row['content'], $current_row['group_id'], $current_row['assignment'], $current_row['progress'], $current_row['version'], $last_row['version'], $current_row['linksto']).': <a href="index.php?cidReq='.$_course[id].'&action=showpage&page_id='.$page.'&title='.$last_row['reflink'].'&group_id='.$last_row['group_id'].'">'.$last_row['title'].'</a>'.'</div>';
					}
				}
			}
		}
	}
}


if ($_GET['action']=='deletewiki'){

	if(api_is_allowed_to_edit(false,true) || api_is_platform_admin())
 	{
		if ($_GET['delete'] == 'yes')
		{
			$return_message=delete_wiki();
			//Display::display_confirmation_message($return_message);
                        $_SESSION["display_confirmation_message"]=$return_message;
	    }
	 }
}


if ($_GET['action']=='discuss' && $_POST['Submit'])
{
   		//Display::display_confirmation_message(get_lang('CommentAdded'));
                $_SESSION["display_confirmation_message"]=get_lang('CommentAdded');
}


/*
-----------------------------------------------------------
WIKI WRAPPER
-----------------------------------------------------------
*/

//echo "<div id='wikiwrapper'>";

/** Actions bar (= action of the wiki tool, not of the page)**/
echo '<div class="actions">';
if($group_member_with_wiki_rights){
	 //Link to groups
	if ($_SESSION['_gid'] OR $_GET['group_id']) {
		echo '<a href="../group/group_space.php?'.api_get_cidreq().'&group_id='.$_clean['group_id'].'">'.Display::return_icon('pixel.gif',get_lang('Groups'),array('class' => 'toolactionplaceholdericon toolactionback')).get_lang('Groups').'</a>';
	}
	///menu home
	echo '<a  style="float: left;" href="index.php?'.api_get_cidreq().'&action=show&title=index&page_id='.$home_page_id.'&group_id='.$_clean['group_id'].'"'.is_active_navigation_tab('show').'>'.Display::return_icon('pixel.gif',get_lang('HomeWiki'),array('class' => 'toolactionplaceholdericon toolwikihome')).get_lang('HomeWiki').'</a>';

 if(!$group_member_with_wiki_rights){
  //Link to groups
  if ($_SESSION['_gid'] OR $_GET['group_id']) {
		echo '<a href="../group/group_space.php?'.api_get_cidreq().'&group_id='.$_clean['group_id'].'">'.Display::return_icon('pixel.gif',get_lang('Groups'),array('class' => 'toolactionplaceholdericon toolactionback')).get_lang('Groups').'</a>';
	}
 }


  if($group_member_with_wiki_rights){
  if (!in_array($_GET['action'], array('addnew', 'searchpages', 'allpages', 'recentchanges', 'deletewiki', 'more', 'mactiveusers', 'mvisited', 'mostchanged', 'orphaned', 'wanted'))) {
    if (api_is_allowed_to_session_edit(false,true) ) {
      //menu edit page
      echo '<a style="float: left; "href="index.php?'.api_get_cidreq().'&page_id='.$page_id.'&action=edit&title='.urlencode($page).'&group_id='.$_clean['group_id'].'"'.is_active_navigation_tab('edit').'>'.Display::return_icon('pixel.gif',get_lang('EditThisPage'),array('class' => 'toolactionplaceholdericon tooledithome')).get_lang('EditPage').'</a>';


    }

  }
	
	 ///menu find
	 echo '<a style="float: right;" href="index.php?'.api_get_cidreq().'&action=searchpages&group_id='.$_clean['group_id'].'"'.is_active_navigation_tab('searchpages').'>'.Display::return_icon('pixel.gif',get_lang('SearchPages'),array('class' => 'toolactionplaceholdericon toolactionsearch')).get_lang('Search').'</a>';

	}


    // Add Wiki Link button will be showed in the actions addnew,edit

    if (in_array($_GET['action'],array('addnew','edit'))){
        echo '<a href="javascript:wiki_link();">'.Display::return_icon('pixel.gif',get_lang('AddWikiLink'),array('class' => 'toolactionplaceholdericon tooladdwikilink')).get_lang('AddWikiLink').'</a>';
    }
  }
if(isset($_SESSION["display_confirmation_message"])){
    Display::display_confirmation_message2($_SESSION["display_confirmation_message"], false, true);
    unset($_SESSION["display_confirmation_message"]);
}
echo '<div class="clear"></div></div>';
if (empty($page_id) && is_string($saved)) {
    //echo Display::display_warning_message($saved, false, true);
    $_SESSION["display_warning_message"]=$saved;
}
echo '<div id="content">';
 
if((!api_is_allowed_to_edit())&&($page=='index')&&($_GET['action']=='edit')){    
    $_SESSION["display_error_message"]=get_lang('OnlyEditPagesCourseManager');
}
if(isset($_SESSION["display_error_message"])){      
    Display::display_error_message($_SESSION["display_error_message"], false,true);
    unset($_SESSION["display_error_message"]);
}


  
		

if ((isset($_GET['action']) && in_array($_GET['action'], array('allpages', 'show', 'searchpages'))) && checktitle('index'))
{
        if(api_is_allowed_to_edit(false,true) || api_is_platform_admin() || GroupManager :: is_user_in_group($_user['user_id'],$_SESSION['_gid']))
        {
                //Display::display_normal_message(get_lang('GoAndEditMainPage'));
                echo '<div class="quiz_content_actions">';
                echo get_lang('GoAndEditMainPage').'<a href="'.api_get_path(WEB_CODE_PATH).'wiki/index.php?'.api_get_cidreq().'&page_id='.$page_id.'&action=edit&title=index&group_id='.$_clean['group_id'].'">'.Display::return_icon('pixel.gif', get_lang('Edit'), array('style'=>'vertical-align: middle;', 'class'=>'actionplaceholdericon actionedit')).'</a></div>';
        }
        else
        {
                return Display::display_normal_message(get_lang('WikiStandBy'), false, true);
        }
}
/*
-----------------------------------------------------------
MAIN WIKI AREA
-----------------------------------------------------------
*/

/////////////////////// more options /////////////////////// Juan Carlos Raña Trabado

if ($_GET['action']=='more'){

	api_display_tool_title(get_lang('More'));

	if(api_is_allowed_to_edit(false,true) || api_is_platform_admin())
	{
		//TODO: config area and private stats

	}
	echo '<ul>';
		//Submenu Most active users
		echo '<li><a href="index.php?cidReq='.$_course[id].'&action=mactiveusers&group_id='.$_clean['group_id'].'">'.get_lang('MostActiveUsers').'</a></li>';
		//Submenu Most visited pages
		echo '<li><a href="index.php?cidReq='.$_course[id].'&action=mvisited&group_id='.$_clean['group_id'].'">'.get_lang('MostVisitedPages').'</a></li>';
		//Submenu Most changed pages
		echo '<li><a href="index.php?cidReq='.$_course[id].'&action=mostchanged&group_id='.$_clean['group_id'].'">'.get_lang('MostChangedPages').'</a></li>';
	   //Submenu Orphaned pages
		echo '<li><a href="index.php?cidReq='.$_course[id].'&action=orphaned&group_id='.$_clean['group_id'].'">'.get_lang('OrphanedPages').'</a></li>';
		//Submenu Wanted pages
		echo '<li><a href="index.php?cidReq='.$_course[id].'&action=wanted&group_id='.$_clean['group_id'].'">'.get_lang('WantedPages').'</a></li>';
	echo '</ul>';

}

/////////////////////// Most active users /////////////////////// Juan Carlos Raña Trabado

if ($_GET['action']=='mactiveusers')
{
	echo '<div class="actions">';
        
	display_back_for_more_page();
	echo '</div>';

	api_display_tool_title(get_lang('MostActiveUsers'));
        
        $sql ='SELECT *, COUNT(*) AS NUM_EDIT FROM '.$tbl_wiki.' w LEFT JOIN '.Database::get_main_table(TABLE_MAIN_SESSION_COURSE_USER).' u ON w.user_id=u.id_user ';
        $sql .="AND u.course_code='".$_SESSION['_course']['id']."' LEFT JOIN ".Database::get_main_table(TABLE_MAIN_COURSE_USER)." r ON w.user_id = r.user_id AND r.course_code='".$_SESSION['_course']['id']."' ";
        $sql .='WHERE w.'.$groupfilter.$condition_session.' AND u.course_code IS NOT NULL OR r.course_code IS NOT NULL GROUP BY w.user_id';
        
        $allpages=Database::query($sql,__FILE__,__LINE__);

	//show table
	if (Database::num_rows($allpages) > 0)
	{
		$row = array ();
		while ($obj = Database::fetch_object($allpages))
		{
			$userinfo=Database::get_user_info_from_id($obj->user_id);
			$row = array ();
                        $row[] = $obj->user_id <>0 ? sprintf('<a id="user_id_%s" class="user_info" href="javascript:void(0);">'.api_get_person_name($userinfo['firstname'], $userinfo['lastname']).'</a>',$userinfo['user_id']) : get_lang('Anonymous').' ('.$obj->user_ip.')';
			//$row[] = $obj->user_id <>0 ? '<a href="../user/userInfo.php?'.api_get_cidreq().'&uInfo='.$userinfo['user_id'].'&origin=wiki">'.api_get_person_name($userinfo['firstname'], $userinfo['lastname']).'</a><a href="'.api_get_self().'?cidReq='.$_course[id].'&action=usercontrib&user_id='.urlencode($row['user_id']).'&group_id='.Security::remove_XSS($_GET['group_id']).'"></a>' : get_lang('Anonymous').' ('.$obj->user_ip.')';
                        //$row[] = $obj->user_id <>0 ? sprintf('<a href="../user/userInfo.php?'.api_get_cidreq().'&uInfo='.$userinfo['user_id'].'&origin=wiki">'.api_get_person_name($userinfo['firstname'], $userinfo['lastname']).'</a>',$userinfo['user_id']).'<a href="'.api_get_self().'?cidReq='.$_course[id].'&action=usercontrib&user_id='.urlencode($row['user_id']).'&group_id='.Security::remove_XSS($_GET['group_id']).'"></a>' : get_lang('Anonymous').' ('.$obj->user_ip.')';
			$row[] ='<a href="'.api_get_self().'?cidReq='.$_course[id].'&action=usercontrib&user_id='.urlencode($obj->user_id).'&group_id='.Security::remove_XSS($_GET['group_id']).'">'.$obj->NUM_EDIT.'</a>';
			$rows[] = $row;
		}

		$table = new SortableTableFromArrayConfig($rows,1,10,'MostActiveUsersA_table','','','DESC');
		$table->set_additional_parameters(array('cidReq' =>Security::remove_XSS($_GET['cidReq']),'action'=>Security::remove_XSS($_GET['action']),'group_id'=>Security::remove_XSS($_GET['group_id'])));
		$table->set_header(0,get_lang('Author'), true, array ('style' => 'width:150px;'));
		$table->set_header(1,get_lang('Contributions'), true);
		$table->display();
	}

}


/////////////////////// User contributions /////////////////////// Juan Carlos Raña Trabado

if ($_GET['action']=='usercontrib')
{
	$userinfo=Database::get_user_info_from_id(Security::remove_XSS($_GET['user_id']));

	echo '<div class="actions">';
	display_back_for_more_page();
	echo '</div>';

	api_display_tool_title(get_lang('UserContributions').': <a href="../user/userInfo.php?'.api_get_cidreq().'&uInfo='.$userinfo['user_id'].'">'.api_get_person_name($userinfo['firstname'], $userinfo['lastname']).'</a><a href="'.api_get_self().'?cidReq='.$_course[id].'&action=usercontrib&user_id='.urlencode($row['user_id']).'&group_id='.Security::remove_XSS($_GET['group_id']).'"></a>');


	if(api_is_allowed_to_edit(false,true) || api_is_platform_admin()) //only by professors if page is hidden
	{
		$sql='SELECT * FROM '.$tbl_wiki.'  WHERE  '.$groupfilter.$condition_session.' AND user_id="'.Security::remove_XSS($_GET['user_id']).'"';
	}
	else
	{
		$sql='SELECT * FROM '.$tbl_wiki.'  WHERE  '.$groupfilter.$condition_session.' AND user_id="'.Security::remove_XSS($_GET['user_id']).'" AND visibility=1';
	}

	$allpages=Database::query($sql,__FILE__,__LINE__);

	//show table
	if (Database::num_rows($allpages) > 0)
	{
		$row = array ();
		while ($obj = Database::fetch_object($allpages))
		{
			//get author
			$userinfo=Database::get_user_info_from_id($obj->user_id);

			//get time
			$year 	 = substr($obj->dtime, 0, 4);
			$month	 = substr($obj->dtime, 5, 2);
			$day 	 = substr($obj->dtime, 8, 2);
			$hours   = substr($obj->dtime, 11,2);
			$minutes = substr($obj->dtime, 14,2);
			$seconds = substr($obj->dtime, 17,2);

			//get type assignment icon
			if($obj->assignment==1)
			{
				$ShowAssignment='<img src="../img/wiki/assignment.gif" title="'.get_lang('AssignmentDesc').'" alt="'.get_lang('AssignmentDesc').'" />';
			}
			elseif ($obj->assignment==2)
			{
				$ShowAssignment='<img src="../img/wiki/works.gif" title="'.get_lang('AssignmentWork').'" alt="'.get_lang('AssignmentWork').'" />';
			}
			elseif ($obj->assignment==0)
			{
				$ShowAssignment='<img src="../img/wiki/trans.gif" />';
			}

			$row = array ();
			$row[] = $year.'-'.$month.'-'.$day.' '.$hours.":".$minutes.":".$seconds;
			$row[] =$ShowAssignment;

			$row[] = '<a href="'.api_get_self().'?cidReq='.$_course[id].'&action=showpage&title='.urlencode($obj->reflink).'&view='.$obj->id.'&group_id='.Security::remove_XSS($_GET['group_id']).'">'.$obj->title.'</a>';
			$row[] =$obj->version;
			$row[] =$obj->comment;
			//$row[] = api_strlen($obj->comment)>30 ? api_substr($obj->comment,0,30).'...' : $obj->comment;
			$row[] =$obj->progress.' %';
			$row[] =$obj->score;

			$rows[] = $row;

		}

		$table = new SortableTableFromArrayConfig($rows,2,10,'UsersContributions_table','','','ASC');
		$table->set_additional_parameters(array('cidReq' =>Security::remove_XSS($_GET['cidReq']),'action'=>Security::remove_XSS($_GET['action']),'user_id'=>Security::remove_XSS($_GET['user_id']),'group_id'=>Security::remove_XSS($_GET['group_id'])));

		$table->set_header(0,get_lang('Date'), true, array ('style' => 'width:200px;'));
		$table->set_header(1,get_lang('Type'), true, array ('style' => 'width:30px;'));
		$table->set_header(2,get_lang('Title'), true, array ('style' => 'width:200px;'));
		$table->set_header(3,get_lang('Version'), true, array ('style' => 'width:30px;'));
		$table->set_header(4,get_lang('Comment'), true, array ('style' => 'width:200px;'));
		$table->set_header(5,get_lang('Progress'), true, array ('style' => 'width:30px;'));
		$table->set_header(6,get_lang('Rating'), true, array ('style' => 'width:30px;'));

		$table->display();

	}
}

/////////////////////// Most changed pages /////////////////////// Juan Carlos Raña Trabado

if ($_GET['action']=='mostchanged')
{
	echo '<div class="actions">';
	display_back_for_more_page();
	echo '</div>';

	api_display_tool_title(get_lang('MostChangedPages'));


	if(api_is_allowed_to_edit(false,true) || api_is_platform_admin()) //only by professors if page is hidden
	{
		$sql='SELECT *, MAX(version) AS MAX FROM '.$tbl_wiki.'  WHERE  '.$groupfilter.$condition_session.' GROUP BY reflink';
	}
	else
	{
		$sql='SELECT *, MAX(version) AS MAX FROM '.$tbl_wiki.'  WHERE  '.$groupfilter.$condition_session.' AND visibility=1 GROUP BY reflink';
	}

	$allpages=Database::query($sql,__FILE__,__LINE__);

	//show table
	if (Database::num_rows($allpages) > 0)
	{
		$row = array ();
		while ($obj = Database::fetch_object($allpages))
		{
			//get type assignment icon
			if($obj->assignment==1)
			{
				$ShowAssignment='<img src="../img/wiki/assignment.gif" title="'.get_lang('AssignmentDesc').'" alt="'.get_lang('AssignmentDesc').'" />';
			}
			elseif ($obj->assignment==2)
			{
				$ShowAssignment='<img src="../img/wiki/works.gif" title="'.get_lang('AssignmentDesc').'" alt="'.get_lang('AssignmentWork').'" />';
			}
			elseif ($obj->assignment==0)
			{
				$ShowAssignment='<img src="../img/wiki/trans.gif" />';
			}

			$row = array ();
			//$row[] =$ShowAssignment;
			$row[] = '<a href="'.api_get_self().'?cidReq='.$_course['id'].'&action=showpage&title='.urlencode($obj->reflink).'&page_id='.urlencode($obj->page_id).'&group_id='.Security::remove_XSS($_GET['group_id']).'">'.$obj->title.'</a>';
			$row[] = $obj->MAX;
			$rows[] = $row;
		}

		$table = new SortableTableFromArrayConfig($rows,2,10,'MostChangedPages_table','','','DESC');
		$table->set_additional_parameters(array('cidReq' =>Security::remove_XSS($_GET['cidReq']),'action'=>Security::remove_XSS($_GET['action']),'group_id'=>Security::remove_XSS($_GET['group_id'])));
		//$table->set_header(0,get_lang('Type'), true, array ('style' => 'width:30px;'));
		$table->set_header(0,get_lang('Title'), true);
		$table->set_header(1,get_lang('Changes'), true);
		$table->display();
	}

}

/////////////////////// Most visited pages /////////////////////// Juan Carlos Raña Trabado

if ($_GET['action']=='mvisited')
{
	echo '<div class="actions">';
	display_back_for_more_page();
	echo '</div>';

	api_display_tool_title(get_lang('MostVisitedPages'));

	if(api_is_allowed_to_edit(false,true) || api_is_platform_admin()) //only by professors if page is hidden
	{
		$sql='SELECT *, SUM(hits) AS tsum FROM '.$tbl_wiki.'  WHERE  '.$groupfilter.$condition_session.' GROUP BY reflink';
	}
	else
	{
		$sql='SELECT *, SUM(hits) AS tsum FROM '.$tbl_wiki.'  WHERE  '.$groupfilter.$condition_session.' AND visibility=1 GROUP BY reflink';
	}

	$allpages=Database::query($sql,__FILE__,__LINE__);

	//show table
	if (Database::num_rows($allpages) > 0)
	{
		$row = array ();
		while ($obj = Database::fetch_object($allpages))
		{
			//get type assignment icon
			if($obj->assignment==1)
			{
				$ShowAssignment='<img src="../img/wiki/assignment.gif" title="'.get_lang('AssignmentDesc').'" alt="'.get_lang('AssignmentDesc').'" />';
			}
			elseif ($obj->assignment==2)
			{
				$ShowAssignment='<img src="../img/wiki/works.gif" title="'.get_lang('AssignmentWork').'" alt="'.get_lang('AssignmentWork').'" />';
			}
			elseif ($obj->assignment==0)
			{
				$ShowAssignment='<img src="../img/wiki/trans.gif" />';
			}

			$row = array ();
			//$row[] =$ShowAssignment;
			$row[] = '<a href="'.api_get_self().'?cidReq='.$_course[id].'&action=showpage&title='.urlencode($obj->reflink).'&page_id='.urlencode($obj->page_id).'&group_id='.Security::remove_XSS($_GET['group_id']).'">'.$obj->title.'</a>';
			$row[] = $obj->tsum;
			$rows[] = $row;
		}

		$table = new SortableTableFromArrayConfig($rows,2,10,'MostVisitedPages_table','','','DESC');
		$table->set_additional_parameters(array('cidReq' =>Security::remove_XSS($_GET['cidReq']),'action'=>Security::remove_XSS($_GET['action']),'group_id'=>Security::remove_XSS($_GET['group_id'])));
		//$table->set_header(0,get_lang('Type'), true, array ('style' => 'width:30px;'));
		$table->set_header(0,get_lang('Title'), true);
		$table->set_header(1,get_lang('Visits'), true);
		$table->display();
	}
}

/////////////////////// Wanted pages /////////////////////// Juan Carlos Raña Trabado

if ($_GET['action']=='wanted')
{
	echo '<div class="actions">';
	display_back_for_more_page();
	echo '</div>';

	api_display_tool_title(get_lang('WantedPages'));

	$pages = array();
	$refs = array();
	$sort_wanted=array();

	//get name pages
	$sql='SELECT * FROM '.$tbl_wiki.'  WHERE  '.$groupfilter.$condition_session.' GROUP BY reflink ORDER BY reflink ASC';
	$allpages=Database::query($sql,__FILE__,__LINE__);

	while ($row=Database::fetch_array($allpages))
	{
		$pages[] = $row['reflink'];
	}

	//get name refs in last pages and make a unique list
	//$sql='SELECT  *  FROM   '.$tbl_wiki.' s1 WHERE id=(SELECT MAX(s2.id) FROM '.$tbl_wiki.' s2 WHERE s1.reflink = s2.reflink AND '.$groupfilter.')'; //old version TODO: Replace by the bottom line

	$sql='SELECT * FROM '.$tbl_wiki.', '.$tbl_wiki_conf.' WHERE visibility=1 AND '.$tbl_wiki_conf.'.page_id='.$tbl_wiki.'.page_id AND '.$tbl_wiki.'.'.$groupfilter.$condition_session; // new version

	$allpages=Database::query($sql,__FILE__,__LINE__);
	while ($row=Database::fetch_array($allpages))
	{
		//$row['linksto']= str_replace("\n".$row["reflink"]."\n", "\n", $row["linksto"]); //remove self reference. TODO: check
		$rf = explode(" ", trim($row["linksto"]));//wanted pages without /n only blank " "
		$refs = array_merge($refs, $rf);
		if ($n++ > 299)
		{
			$refs = array_unique($refs);
			$n=0;
		} // (clean-up only every 300th loop). Thanks to Erfurt Wiki
	}

	//sort linksto. Find linksto into reflink. If not found ->page is wanted
	natcasesort($refs);
	echo '<ul>';
	foreach($refs as $v)
	{
		if(!in_array($v, $pages))
		{
			if (trim($v)!="")
			{
				echo   '<li><a href="'.api_get_path(WEB_PATH).'main/wiki/index.php?cidReq=&action=addnew&title='.urlencode(str_replace('_',' ',$v)).'&group_id='.Security::remove_XSS($_GET['group_id']).'" class="new_wiki_link">'.str_replace('_',' ',$v).'</a></li>';
			}
		}
	}
	echo '</ul>';
}

/////////////////////// Orphaned pages /////////////////////// Juan Carlos Raña Trabado

if ($_GET['action']=='orphaned')
{
	echo '<div class="actions">';
	display_back_for_more_page();
	echo '</div>';

	api_display_tool_title(get_lang('OrphanedPages'));

	$pages = array();
   	$refs = array();
  	$orphaned = array();

	//get name pages
	$sql='SELECT * FROM '.$tbl_wiki.'  WHERE  '.$groupfilter.$condition_session.' GROUP BY reflink ORDER BY reflink ASC';
	$allpages=Database::query($sql,__FILE__,__LINE__);
	while ($row=Database::fetch_array($allpages))
	{
		$pages[] = $row['reflink'];
	}

	//get name refs in last pages and make a unique list
	//$sql='SELECT  *  FROM   '.$tbl_wiki.' s1 WHERE id=(SELECT MAX(s2.id) FROM '.$tbl_wiki.' s2 WHERE s1.reflink = s2.reflink AND '.$groupfilter.')'; //old version TODO: Replace by the bottom line

	$sql='SELECT * FROM '.$tbl_wiki.', '.$tbl_wiki_conf.' WHERE '.$tbl_wiki_conf.'.page_id='.$tbl_wiki.'.page_id AND '.$tbl_wiki.'.'.$groupfilter.$condition_session.' '; // new version

	$allpages=Database::query($sql,__FILE__,__LINE__);
	while ($row=Database::fetch_array($allpages))
	{
		//$row['linksto']= str_replace("\n".$row["reflink"]."\n", "\n", $row["linksto"]); //remove self reference. TODO: check
		$rf = explode(" ", trim($row["linksto"]));	//fix replace explode("\n", trim($row["linksto"])) with  explode(" ", trim($row["linksto"]))

		$refs = array_merge($refs, $rf);
		if ($n++ > 299)
		{
			$refs = array_unique($refs);
			$n=0;
		} // (clean-up only every 300th loop). Thanks to Erfurt Wiki
	}

	//search each name of list linksto into list reflink
	foreach($pages as $v)
	{
		if(!in_array($v, $refs))
		{
			$orphaned[] = $v;
		}
	}

	//change reflink by title
	foreach($orphaned as $vshow)
	{
		if(api_is_allowed_to_edit(false,true) || api_is_platform_admin()) //only by professors if page is hidden
		{
			$sql='SELECT  *  FROM   '.$tbl_wiki.' WHERE '.$groupfilter.$condition_session.' AND reflink="'.$vshow.'" GROUP BY reflink';
		}
		else
		{
			$sql='SELECT  *  FROM   '.$tbl_wiki.' WHERE '.$groupfilter.$condition_session.' AND reflink="'.$vshow.'" AND visibility=1 GROUP BY reflink';
		}

		$allpages=Database::query($sql,__FILE__,__LINE__);

		echo '<ul>';
		while ($row=Database::fetch_array($allpages))
		{
			//fix assignment icon
			if($row['assignment']==1)
			{
				$ShowAssignment='<img src="../img/wiki/assignment.gif" />';
			}
			elseif ($row['assignment']==2)
			{
				$ShowAssignment='<img src="../img/wiki/works.gif" />';
			}
			elseif ($row['assignment']==0)
			{
				$ShowAssignment='<img src="../img/wiki/trans.gif" />';
			}

			echo '<li>'.$ShowAssignment.'<a href="'.api_get_self().'?cidReq='.$_course[id].'&action=showpage&title='.urlencode($row['reflink']).'&page_id='.urlencode($row['page_id']).'&group_id='.Security::remove_XSS($_GET['group_id']).'">'.$row['title'].'</a></li>';
		}
		echo '</ul>';
	}

}

/////////////////////// delete current page /////////////////////// Juan Carlos Raña Trabado

if ($_GET['action']=='delete')
{

	if(!$_GET['page_id'])
	{
		Display::display_confirmation_message(get_lang('MustSelectPage'));
                //$_SESSION["display_confirmation_message"]=get_lang('MustSelectPage');
		exit;
	}

	if(api_is_allowed_to_edit(false,true) || api_is_platform_admin())
	{
		api_display_tool_title(get_lang('DeletePageHistory'));

		if($page=="index")
		{
			//Display::display_confirmation_message(get_lang('WarningDeleteMainPage'),false,true);
//                        $_SESSION["display_warning_message"]=get_lang('WarningDeleteMainPage');
                        $message2 = get_lang('WarningDeleteMainPage')."<br/><br/>";
		}

                // display message if is home main
                if(isset($_SESSION["display_warning_message"])){ 
                    Display::display_warning_message($_SESSION["display_warning_message"], false, true);
                    unset($_SESSION["display_warning_message"]);
                }

		$message =  $message2 . get_lang('ConfirmDeletePage')."</p>"."<p>"."<a href=\"index.php\">".get_lang("No")."</a>"."&nbsp;&nbsp;|&nbsp;&nbsp;"."<a href=\"".api_get_self()."?action=delete&page_id=".$page_id."&title=".urlencode($page)."&delete=yes\">".get_lang("Yes")."</a>"."</p>";

		if (!isset ($_GET['delete']))
		{
			Display::display_warning_message($message,false, true);
                        //$_SESSION["display_confirmation_message"]=$message;
		}

		if ($_GET['delete'] == 'yes')
		{
                        if(api_get_setting('search_enabled') == true) {
                            //delete keyword
                            $wikidel = new WikiManager();
                            $idMaxWiki = $wikidel->get_max_id_wiki($_GET['page_id']);
                            $searchkey = new SearchEngineManager();
                            $searchkey->idobj = $idMaxWiki;
                            $searchkey->course_code = api_get_course_id();
                            $searchkey->tool_id = TOOL_WIKI;
                            $searchkey->deleteKeyWord();

                            $wikidel = new WikiManager();
                            $wikidel->search_engine_delete();
                        }
			//$sql='DELETE '.$tbl_wiki_discuss.' FROM '.$tbl_wiki.', '.$tbl_wiki_discuss.' WHERE '.$tbl_wiki.'.reflink="'.Database::escape_string($page).'" AND '.$tbl_wiki.'.'.$groupfilter.' AND '.$tbl_wiki_discuss.'.publication_id='.$tbl_wiki.'.id';
			$sql='DELETE '.$tbl_wiki_discuss.' FROM '.$tbl_wiki.', '.$tbl_wiki_discuss.' WHERE '.$tbl_wiki.'.page_id="'.Database::escape_string($page_id).'" AND '.$tbl_wiki.'.'.$groupfilter.' AND '.$tbl_wiki_discuss.'.publication_id='.$tbl_wiki.'.id';
                        Database::query($sql,__FILE__,__LINE__);

			//$sql='DELETE '.$tbl_wiki_mailcue.' FROM '.$tbl_wiki.', '.$tbl_wiki_mailcue.' WHERE '.$tbl_wiki.'.reflink="'.Database::escape_string($page).'" AND '.$tbl_wiki.'.'.$groupfilter.' AND '.$tbl_wiki_mailcue.'.id='.$tbl_wiki.'.id';
			$sql='DELETE '.$tbl_wiki_mailcue.' FROM '.$tbl_wiki.', '.$tbl_wiki_mailcue.' WHERE '.$tbl_wiki.'.page_id="'.Database::escape_string($page_id).'" AND '.$tbl_wiki.'.'.$groupfilter.' AND '.$tbl_wiki_mailcue.'.id='.$tbl_wiki.'.id';
                        Database::query($sql,__FILE__,__LINE__);

			//$sql='DELETE FROM '.$tbl_wiki.' WHERE reflink="'.Database::escape_string($page).'" AND '.$groupfilter.$condition_session.'';
			$sql='DELETE FROM '.$tbl_wiki.' WHERE page_id="'.Database::escape_string($page_id).'" AND '.$groupfilter.$condition_session.'';
	  		Database::query($sql,__FILE__,__LINE__);

			check_emailcue(0, 'E');

	  		Display::display_confirmation_message2(get_lang('WikiPageDeleted'), false, true);
                        //$_SESSION["display_confirmation_message"]=get_lang('WikiPageDeleted');
                        $url = api_get_path(WEB_CODE_PATH).'wiki/index.php?'.api_get_cidreq();
                        header("Location: " . $url );
		}
	}
	else
	{
		Display::display_confirmation_message2(get_lang("OnlyAdminDeletePageWiki"), false, true);
                //$_SESSION["display_confirmation_message"]=get_lang("OnlyAdminDeletePageWiki");
	}

}


/////////////////////// delete all wiki /////////////////////// Juan Carlos Raña Trabado

if ($_GET['action']=='deletewiki'){
	api_display_tool_title(get_lang('DeleteWiki'));
	if(api_is_allowed_to_edit(false,true) || api_is_platform_admin())
	{
            //confirmation message replace at function Jalert();
		$message = 	get_lang('ConfirmDeleteWiki');
		$message .= '<p>
						<a href="index.php">'.get_lang('No').'</a>
						&nbsp;&nbsp;|&nbsp;&nbsp;
						<a href="'.api_get_self().'?action=deletewiki&delete=yes">'.get_lang('Yes').'</a>
					</p>';
		if (!isset($_GET['delete']))
		{
			//echo $message;
                            Display :: display_warning_message($message,false,true);
		}
	}
	else
	{
		Display::display_normal_message(get_lang("OnlyAdminDeleteWiki"));
	}
}

/////////////////////// search wiki pages ///////////////////////
if ($_GET['action']=='searchpages'){
	api_display_tool_title(get_lang('SearchPages'));

	// initiate the object
	$form = new FormValidator('wiki_search','post', api_get_self().'?cidReq='.Security::remove_XSS($_GET['cidReq']).'&action='.Security::remove_XSS($_GET['action']).'&group_id='.Security::remove_XSS($_GET['group_id']));
        
	// settting the form elements

	$form->addElement('text', 'search_term', get_lang('SearchTerm'), array('style'=> 'width:335px; *width:300px;','id'=> 'search_term_id'));
        $form->addElement('checkbox', 'search_content', null, get_lang('AlsoSearchContent'));
        $form->addElement('style_submit_button', 'SubmitWikiSearch', get_lang('Search'), 'class="search"  id="wiki_search_id" style="margin-top:-77px; margin-right:260px !important; *margin-top:-90px"'); 
	
	

	// setting the rules
	//$form->addRule('search_term', '<span class="required">'.get_lang('ThisFieldIsRequired').'</span>', 'required');
	$form->addRule('search_term', get_lang('TooShort'),'minlength',3);

	if ($form->validate() || !empty($_GET['search_term'])) {
		$form->display();
		if ($form->validate() && !empty($values['search_term'])) {
		  $values = $form->exportValues();
		  $search_term = $values['search_term'];
	      display_wiki_search_results($search_term, $values['search_content']);
		} elseif (!empty($_GET['search_term'])) {
            $values = $form->exportValues();
		   $search_term = Security::remove_XSS($_GET['search_term']);
	       display_wiki_search_results($search_term, $values['search_content']);
		}
	} else {
		$form->display();
  }
}


///////////////////////  What links here. Show pages that have linked this page /////////////////////// Juan Carlos Raña Trabado

if ($_GET['action']=='links')
{

	if (!$_GET['title'])
	{
		//Display::display_error_message(get_lang("MustSelectPage"), false, true);
                $_SESSION["display_error_message"]=get_lang("MustSelectPage");
    }
	else
	{

		//$sql='SELECT * FROM '.$tbl_wiki.' WHERE reflink="'.Database::escape_string($page).'" AND '.$groupfilter.$condition_session.'';
		$sql='SELECT * FROM '.$tbl_wiki.' WHERE page_id="'.Database::escape_string($page_id).'" AND '.$groupfilter.$condition_session.'';
		$result=Database::query($sql,__FILE__,__LINE__);
		$row=Database::fetch_array($result);

		//get type assignment icon

				if($row['assignment']==1)
				{
					$ShowAssignment='<img src="../img/wiki/assignment.gif" title="'.get_lang('AssignmentDesc').'" alt="'.get_lang('AssignmentDesc').'" />';
				}
				elseif ($row['assignment']==2)
				{
					$ShowAssignment='<img src="../img/wiki/works.gif" title="'.get_lang('AssignmentWork').'" alt="'.get_lang('AssignmentWork').'" />';
				}
				elseif ($row['assignment']==0)
				{
					$ShowAssignment='<img src="../img/wiki/trans.gif" />';
				}

		//fix Title to reflink (link Main Page)

		if ($page==get_lang('DefaultTitle'))
		{
			$page='index';
		}

		echo '<div id="wikititle">';
		echo get_lang('LinksPagesFrom').': '.$ShowAssignment.' <a href="'.api_get_self().'?cidReq='.$_course[id].'&action=showpage&page_id='.$page_id.'&title='.Security::remove_XSS($page).'&group_id='.Security::remove_XSS($_GET['group_id']).'">'.Security::remove_XSS($row['title']).'</a>';
		echo '</div>';

		//fix index to title Main page into linksto
		if ($page=='index')
		{
			$page=str_replace(' ','_',get_lang('DefaultTitle'));
		}

		//table

		if(api_is_allowed_to_edit(false,true) || api_is_platform_admin()) //only by professors if page is hidden
		{
			//$sql="SELECT * FROM ".$tbl_wiki." s1 WHERE linksto LIKE '%".Database::escape_string($page)." %' AND id=(SELECT MAX(s2.id) FROM ".$tbl_wiki." s2 WHERE s1.reflink = s2.reflink AND ".$groupfilter.")"; //add blank space after like '%" " %' to identify each word. //Old version TODO: Replace by the bottom line

			$sql="SELECT * FROM ".$tbl_wiki.", ".$tbl_wiki_conf." WHERE linksto LIKE '%".Database::escape_string($page)." %' AND ".$tbl_wiki_conf.".page_id=".$tbl_wiki.".page_id AND ".$tbl_wiki.".".$groupfilter.$condition_session.""; //add blank space after like '%" " %' to identify each word. // new version

		}
		else
		{
			//$sql="SELECT * FROM ".$tbl_wiki." s1 WHERE visibility=1 AND linksto LIKE '%".Database::escape_string($page)." %' AND id=(SELECT MAX(s2.id) FROM ".$tbl_wiki." s2 WHERE s1.reflink = s2.reflink AND ".$groupfilter.")"; //add blank space after like '%" " %' to identify each word //old version TODO: Replace by the bottom line

			$sql="SELECT * FROM ".$tbl_wiki.", ".$tbl_wiki_conf." WHERE visibility=1 AND linksto LIKE '%".Database::escape_string($page)." %' AND ".$tbl_wiki_conf.".page_id=".$tbl_wiki.".page_id AND ".$tbl_wiki.".".$groupfilter.$condition_session.""; //add blank space after like '%" " %' to identify each word // new version

		}

		$allpages=Database::query($sql,__LINE__,__FILE__);

		//show table
		if (Database::num_rows($allpages) > 0)
		{
			$row = array ();
			while ($obj = Database::fetch_object($allpages))
			{
				//get author
				$userinfo=Database::get_user_info_from_id($obj->user_id);

				//get time
				$year 	 = substr($obj->dtime, 0, 4);
				$month	 = substr($obj->dtime, 5, 2);
				$day 	 = substr($obj->dtime, 8, 2);
				$hours   = substr($obj->dtime, 11,2);
				$minutes = substr($obj->dtime, 14,2);
				$seconds = substr($obj->dtime, 17,2);

				//get type assignment icon
				if($obj->assignment==1)
				{
					$ShowAssignment='<img src="../img/wiki/assignment.gif" title="'.get_lang('AssignmentDesc').'" alt="'.get_lang('AssignmentDesc').'" />';
				}
				elseif ($obj->assignment==2)
				{
					$ShowAssignment='<img src="../img/wiki/works.gif" title="'.get_lang('AssignmentWork').'" alt="'.get_lang('AssignmentWork').'" />';
				}
				elseif ($obj->assignment==0)
				{
					$ShowAssignment='<img src="../img/wiki/trans.gif" />';
				}

				$row = array ();
				$row[] =$ShowAssignment;
				$row[] = '<a href="'.api_get_self().'?cidReq='.$_course[id].'&action=showpage&title='.urlencode($obj->reflink).'&group_id='.Security::remove_XSS($_GET['group_id']).'">'.Security::remove_XSS($obj->title).'</a>';
				//$row[] = $obj->user_id <>0 ? '<a href="../user/userInfo.php?'.api_get_cidreq().'&uInfo='.$userinfo['user_id'].'">'.api_get_person_name($userinfo['firstname'], $userinfo['lastname']).'</a>' : get_lang('Anonymous').' ('.$obj->user_ip.')';
                                $row[] = $obj->user_id <>0 ? sprintf('<a id="user_id_%s" class="user_info" href="javascript:void(0);">'.api_get_person_name($userinfo['firstname'], $userinfo['lastname']).'</a>',$userinfo['user_id']) : get_lang('Anonymous').' ('.$obj->user_ip.')';
				$row[] = $year.'-'.$month.'-'.$day.' '.$hours.":".$minutes.":".$seconds;
				$rows[] = $row;
			}

			$table = new SortableTableFromArrayConfig($rows,1,10,'AllPages_table','','','ASC');
			$table->set_additional_parameters(array('cidReq' =>Security::remove_XSS($_GET['cidReq']),'action'=>Security::remove_XSS($_GET['action']),'group_id'=>Security::remove_XSS($_GET['group_id'])));
			$table->set_header(0,get_lang('Type'), true, array ('style' => 'width:30px;'));
			$table->set_header(1,get_lang('Title'), true);
			$table->set_header(2,get_lang('Author'), true);
			$table->set_header(3,get_lang('Date'), true);
			$table->display();
		}
	}
}


/////////////////////// adding a new page ///////////////////////


// Display the form for adding a new wiki page
if ($_GET['action']=='addnew')
{
	if (api_get_session_id()!=0 && api_is_allowed_to_session_edit(false,true)==false) {
		api_not_allowed();
	}

	api_display_tool_title(get_lang('AddNew'));

	//first, check if page index was created. chektitle=false
	if (checktitle('index'))
	{
		if(api_is_allowed_to_edit(false,true) || api_is_platform_admin() || GroupManager :: is_user_in_group($_user['user_id'],$_SESSION['_gid']))
		{
			//Display::display_normal_message(get_lang('GoAndEditMainPage'));
			echo '<div class="quiz_content_actions">'.get_lang('GoAndEditMainPage').'<a href="'.api_get_path(WEB_CODE_PATH).'wiki/index.php?'.api_get_cidreq().'&page_id='.$page_id.'&action=edit&title=index&group_id=">'.Display::return_icon('pixel.gif', '', array('style'=>'vertical-align: middle;', 'class'=>'actionplaceholdericon actionedit')).'</a></div>';
		}
		else
		{
			return Display::display_normal_message(get_lang('WikiStandBy'), false, true);
		}
	}

	elseif (check_addnewpagelock()==0 && (api_is_allowed_to_edit(false,true)==false || api_is_platform_admin()==false))
	{
		//Display::display_error_message(get_lang('AddPagesLocked'), false, true);
                $_SESSION["display_error_message"]=get_lang('AddPagesLocked');
	}
	else
	{
		if(api_is_allowed_to_edit(false,true) || api_is_platform_admin() || GroupManager :: is_user_in_group($_user['user_id'],$_SESSION['_gid']) || Security::remove_XSS($_GET['group_id'])==0)
		{
			display_new_wiki_form();
		}
		else
		{
			Display::display_normal_message(get_lang('OnlyAddPagesGroupMembers'), false, true);
		}
	}
}


/////////////////////// show home page ///////////////////////

if (!$_GET['action'] OR ($_GET['action']=='show' OR $_GET['action']=='discuss') AND !isset($_POST['SaveWikiNew']))
{
	display_wiki_entry($newtitle,$progress,$count_words,$wiki_score);
}

/////////////////////// show current page ///////////////////////

if ($_GET['action']=='showpage' AND !isset($_POST['SaveWikiNew']))
{
	if((isset($_GET['page_id']) && $_GET['page_id'] >= 0) || (isset($_GET['get_wiki_link']) && $_GET['get_wiki_link'] == 'true'))
	{
		if (isset($_GET['get_wiki_link']) && isset($_GET['title']) && $_GET['title'] != '') {
		  $newtitle = Security::remove_XSS($_GET['title']);
	     }
		display_wiki_entry($newtitle,$progress,$count_words,$wiki_score);
	}
	else
	{
		//Display::display_error_message(get_lang('MustSelectPage'), false, true);
                $_SESSION["display_error_message"]=get_lang('MustSelectPage');
	}
}


/////////////////////// edit current page ///////////////////////

if ($_GET['action']=='edit')
{
	if (api_get_session_id()!=0 && api_is_allowed_to_session_edit(false,true)==false) {
		api_not_allowed();
	}
           
	$_clean['group_id']=(int)$_SESSION['_gid'];

	$sql='SELECT * FROM '.$tbl_wiki.', '.$tbl_wiki_conf.' WHERE '.$tbl_wiki_conf.'.page_id='.$tbl_wiki.'.page_id AND '.$tbl_wiki.'.page_id="'.Database::escape_string($page_id).'" AND '.$tbl_wiki.'.'.$groupfilter.$condition_session.' ORDER BY id DESC';
       
        $result=Database::query($sql,__LINE__,__FILE__);
	$row=Database::fetch_array($result); // we do not need a while loop since we are always displaying the last version


	if ($row['content']=='' AND $row['title']=='' AND $page=='')
	{
		//Display::display_error_message(get_lang('MustSelectPage'), false, true);
                $_SESSION["display_error_message"]=get_lang('MustSelectPage');
		exit;
	}
	elseif ($row['content']=='' AND $row['title']=='' AND $page=='index')
	{
		//Table structure for better export to pdf
		$default_table_for_content_Start='<table width="98%" border="0"><tr><td align="left" valign="top" width="55%">';
		$default_table_for_content_End='</td><td align="right">'.Display::return_icon("avatars/builder.png",get_lang('Build')).'</td></tr></table>';

		$content = $default_table_for_content_Start . '<h3 style="font-size: 14px;">Lorem ipsum quia dolor sit amet</h3><p style="font-size: 14px;">Consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur</p><p style="font-size: 14px;">Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.</p>' . $default_table_for_content_End;
		$title=get_lang('DefaultTitle');
		$page_id=0;
	}
	else
	{
		$content=$row['content'];
		$title=$row['title'];
		$page_id=$row['page_id'];
	}

	//Only teachers and platform admin can edit the index page. Only teachers and platform admin can edit an assignment teacher. And users in groups
	if(($row['reflink']=='index' || $row['reflink']=='' || $row['assignment']==1) && (!api_is_allowed_to_edit(false,true) && $_clean['group_id']==0))
	{ 
		//Display::display_error_message(get_lang('OnlyEditPagesCourseManager'), false, true);
                //$_SESSION["display_error_message"]=get_lang('OnlyEditPagesCourseManager');
	}
        else
	{
		$PassEdit=false;

	    //check if is a wiki group
		if($_clean['group_id']!=0)
		{
			//Only teacher, platform admin and group members can edit a wiki group
			if(api_is_allowed_to_edit(false,true) || api_is_platform_admin() || GroupManager :: is_user_in_group($_user['user_id'],$_SESSION['_gid']))
			{
				$PassEdit=true;
			}
			else
			{
			  	Display::display_normal_message(get_lang('OnlyEditPagesGroupMembers'), false, true);
			}
		}
		else
		{
		    $PassEdit=true;
		}

		// check if is a assignment
		if($row['assignment']==1)
		{
		    Display::display_normal_message(get_lang('EditAssignmentWarning'), false, true);
			$icon_assignment='<img src="../img/wiki/assignment.gif" title="'.get_lang('AssignmentDescExtra').'" alt="'.get_lang('AssignmentDescExtra').'" />';
		}
		elseif($row['assignment']==2)
		{
			$icon_assignment='<img src="../img/wiki/works.gif" title="'.get_lang('AssignmentWorkExtra').'" alt="'.get_lang('AssignmentWorkExtra').'" />';
			if((api_get_user_id()==$row['user_id'])==false)
			{
			    if(api_is_allowed_to_edit(false,true) || api_is_platform_admin())
				{
					$PassEdit=true;
				}
				else
				{
					//Display::display_warning_message(get_lang('LockByTeacher'), false, true);
                                        $_SESSION["display_warning_message"]=get_lang('LockByTeacher');
					$PassEdit=false;
				}
			}
			else
			{
				$PassEdit=true;
			}
		}

	 	if($PassEdit) //show editor if edit is allowed
		 {
	 		if ($row['editlock']==1 && (api_is_allowed_to_edit(false,true)==false || api_is_platform_admin()==false))
  	   	    {
    		       Display::display_normal_message(get_lang('PageLockedExtra'), false, true);
		    }
			else
			{
				//check tasks
				if (!empty($row['startdate_assig']) && $row['startdate_assig']!='0000-00-00 00:00:00' && time()<strtotime($row['startdate_assig']))
				{
					$message=get_lang('TheTaskDoesNotBeginUntil').': '.$row['startdate_assig'];
					//Display::display_warning_message($message, false, true);
                                        $_SESSION["display_warning_message"]=$message;
					if(!api_is_allowed_to_edit(false,true))
					{
						exit;
					}
				}

				//
				if (!empty($row['enddate_assig']) && $row['enddate_assig']!='0000-00-00 00:00:00' && time()>strtotime($row['enddate_assig']) && $row['enddate_assig']!='0000-00-00 00:00:00' && $row['delayedsubmit']==0)
				{
					$message=get_lang('TheDeadlineHasBeenCompleted').': '.$row['enddate_assig'];
					//Display::display_warning_message($message, false, true);
                                        $_SESSION["display_warning_message"]=$message;
					if(!api_is_allowed_to_edit(false,true))
					{
						exit;
					}
				}

				//
				if(!empty($row['max_version']) && $row['version']>=$row['max_version'])
				{
					$message=get_lang('HasReachedMaxiNumVersions');
					Display::display_warning_message($message, false, true);
                                        $_SESSION["display_warning_message"]=$message;
					if(!api_is_allowed_to_edit(false,true))
					{
						exit;
					}
				}

				//
				if (!empty($row['max_text']) && $row['max_text']<=word_count($row['content']))
				{
					$message=get_lang('HasReachedMaxNumWords');
					//Display::display_warning_message($message, false, true);
                                        $_SESSION["display_warning_message"]=$message;
					if(!api_is_allowed_to_edit(false,true))
					{
						exit;
					}

				}

				////
				if (!empty($row['task']))
				{
					//previous change 0 by text
					if ($row['startdate_assig']=='0000-00-00 00:00:00')
					{
						$message_task_startdate=get_lang('No');
					}
					else
					{
						$message_task_startdate=$row['startdate_assig'];
					}

					if ($row['enddate_assig']=='0000-00-00 00:00:00')
					{
						$message_task_enddate=get_lang('No');
					}
					else
					{
						$message_task_endate=$row['enddate_assig'];
					}

					if ($row['delayedsubmit']==0)
					{
						$message_task_delayedsubmit=get_lang('No');
					}
					else
					{
						$message_task_delayedsubmit=get_lang('Yes');
					}
					if ($row['max_version']==0)
					{
						$message_task_max_version=get_lang('No');
					}
					else
					{
						$message_task_max_version=$row['max_version'];
					}
					if ($row['max_text']==0)
					{
						$message_task_max_text=get_lang('No');
					}
					else
					{
						$message_task_max_text=$row['max_text'];
					}

					//comp message
					$message_task='<b>'.get_lang('DescriptionOfTheTask').'</b><p>'.$row['task'].'</p><hr>';
					$message_task.='<p>'.get_lang('StartDate').': '.$message_task_startdate.'</p>';
					$message_task.='<p>'.get_lang('EndDate').': '.$message_task_enddate;
					$message_task.=' ('.get_lang('AllowLaterSends').') '.$message_task_delayedsubmit.'</p>';
					$message_task.='<p>'.get_lang('OtherSettings').': '.get_lang('NMaxVersion').': '.$message_task_max_version;
					$message_task.=' '.get_lang('NMaxWords').': '.$message_task_max_text;

					//display message
					Display::display_normal_message($message_task,false, true);

				}

				if($row['progress']==$row['fprogress1'] && !empty($row['fprogress1']))
				{
					$feedback_message='<b>'.get_lang('Feedback').'</b><p>'.$row['feedback1'].'</p>';
					Display::display_normal_message($feedback_message, false, true);
				}
				elseif($row['progress']==$row['fprogress2'] && !empty($row['fprogress2']))
				{
					$feedback_message='<b>'.get_lang('Feedback').'</b><p>'.$row['feedback2'].'</p>';
					Display::display_normal_message($feedback_message, false, true);
				}
				elseif($row['progress']==$row['fprogress3'] && !empty($row['fprogress3']))
				{
					$feedback_message='<b>'.get_lang('Feedback').'</b><p>'.$row['feedback3'].'</p>';
					Display::display_normal_message($feedback_message, false, true);
				}

				//previous checking for concurrent editions
				if($row['is_editing']==0)
				{
				//	Display::display_normal_message(get_lang('WarningMaxEditingTime'));

					$time_edit = date("Y-m-d H:i:s");
					$sql='UPDATE '.$tbl_wiki.' SET is_editing="'.$_user['user_id'].'", time_edit="'.$time_edit.'" WHERE id="'.$row['id'].'"';

                                        Database::query($sql,__FILE__,__LINE__);
				}
				elseif($row['is_editing']!=$_user['user_id'])
				{
					$timestamp_edit=strtotime($row['time_edit']);
					$time_editing=time()-$timestamp_edit;
					$max_edit_time=1200; // 20 minutes
					$rest_time=$max_edit_time-$time_editing;

					$userinfo=Database::get_user_info_from_id($row['is_editing']);

					$is_being_edited= get_lang('ThisPageisBeginEditedBy').' <a href=../user/userInfo.php?'.api_get_cidreq().'&uInfo='.$userinfo['user_id'].'>'.api_get_person_name($userinfo['firstname'], $userinfo['lastname']).'</a>. '.get_lang('ThisPageisBeginEditedTryLater').' '.date( "i",$rest_time).' '.get_lang('MinMinutes').'';
					Display::display_normal_message($is_being_edited, false, true);
					exit;
				}
				//form
				echo '<form name="form1" method="post" action="'.api_get_self().'?'.api_get_cidreq().'&action=showpage&title='.urlencode($page).'&page_id='.$page_id.'&group_id='.Security::remove_XSS($_GET['group_id']).'">';

				$glossary_plugin = '';
				$currentEditor = strtolower(api_get_setting('use_default_editor'));
				if ($currentEditor == 'fckeditor') {
				   if (api_get_setting('show_glossary_in_documents') == 'ismanual') {
//				     $glossary_plugin = '<td width="5px;" class="toolbar_style"><img onclick="glossary();" src="'.api_get_path(WEB_LIBRARY_PATH).'fckeditor/editor/plugins/glossary/glossary.gif"></td>';
				   }
                                    echo '<table cellspacing="3" width="100%" height="50px" class="toolbar_style_back"><tr><td width="80%"><table width="100%"><tr style="height:5px;"><td colspan="3"></td></tr>
                                    <tr>
                                    <td width="5px"><img src="../img/toolbar_start.gif"></td>
                                    <td width="5px" class="toolbar_style">
                                    <img src="../img/pasteword_icon.png" onclick="word();" alt="'.get_lang('PasteWord').'" title="'.get_lang('PasteWord').'"></td>
                                    <td width="5px"><img src="../img/toolbar_start.gif"></td>
                                    <td width="5px;" class="toolbar_style"><img src="../img/link_icon.png" onclick="link();" alt="'.get_lang('Link').'" title="'.get_lang('Link').'"></td>
                                    <td width="5px"><img src="../img/toolbar_start.gif"></td><td width="5px;" class="toolbar_style">'.Display::return_icon('pixel.gif',get_lang('Images'),array('class'=>'fckactionplaceholdericon fckactionimages_icon','onclick'=>'image();')) .'</td>
                                    <td width="5px;" class="toolbar_style">'.Display::return_icon('pixel.gif',get_lang('Imagemap'),array('class'=>'fckactionplaceholdericon fckactionimagemap','onclick'=>'imagemap();')).'</td>
                                    <td width="5px;" class="toolbar_style">'.Display::return_icon('pixel.gif',get_lang('Mindmap'),array('class'=>'fckactionplaceholdericon fckactionmindmap_18','onclick'=>'mindmap();')).'</td>
                                    <td width="5px;" class="toolbar_style">'.Display::return_icon('pixel.gif',get_lang('Mascot'),array('class'=>'fckactionplaceholdericon fckactionmascot_icon','onclick'=>'mascot();')).'</td>
                                    <td width="5px;" class="toolbar_style">'.Display::return_icon('pixel.gif',get_lang('Videoplayer'),array('class'=>'fckactionplaceholdericon fckactionvideoPlayer','onclick'=>'videoplayer();')).'</td>
                                    <td width="5px;" class="toolbar_style">'.Display::return_icon('pixel.gif',get_lang('Audio'),array('class'=>'fckactionplaceholdericon fckactionaudio','onclick'=>'audio();')).'</td>
                                    '.$glossary_plugin.'
                                    <td width="5px"><img src="../img/toolbar_start.gif"></td>
                                    <td width="5px;" class="toolbar_style">'.Display::return_icon('pixel.gif',get_lang('Table'),array('class'=>'fckactionplaceholdericon fckactiontable','onclick'=>'table();')).'</td>
                                    <td width="5px;" class="toolbar_style"><img src="../img/unordered_list.png" onclick="unordered();" alt="'.get_lang('Orderedlist').'" title="'.get_lang('Orderedlist').'"></td>
                                    <td width="5px;" class="toolbar_style"><img src="../img/view_source.png" onclick="source();" alt="'.get_lang('Source').'" title="'.get_lang('Source').'"></td>
                                    <td width="5px"><img src="../img/toolbar_start.gif"></td>
                                    <td width="5px;" class="toolbar_style"><img src="../img/text_bold.png" onclick="makeitbold();" alt="'.get_lang('Bold').'" title="'.get_lang('Bold').'"></td>
                                    <td width="5px"><img src="../img/toolbar_start.gif"></td><td width="5px;" class="toolbar_style"><img src="../img/text_left.png" onclick="alignleft();" alt="'.get_lang('Alignleft').'" title="'.get_lang('Alignleft').'"></td>
                                    <td width="5px;" class="toolbar_style"><img src="../img/text_center.png" onclick="aligncenter();" alt="'.get_lang('Aligncenter').'" title="'.get_lang('Aligncenter').'"></td>
                                    <td width="5px;" class="toolbar_style">'.Display::return_icon('pixel.gif',get_lang('Textcolor'), array('class' => 'fckactionplaceholdericon fckactionfontcolor', 'onclick' => 'fontcolor(event);')).'</td>
                                    <td width="5px"><img src="../img/toolbar_start.gif"></td>
                                    </tr>
                                    <tr height="5px">
                                    <td></td></tr>
                                    </table></td>
                                    <td>
                                    <table width="100%">
                                    <tr><td><span style="color:#333333;">Font:</span><select name="font" onchange="fontsize()"><option></option><option value="smaller" style="font-size: smaller;">smaller</option><option value="larger" style="font-size: larger;">larger</option><option value="xx-small" style="font-size: xx-small;">xx-small</option><option value="x-small" style="font-size: x-small;">x-small</option><option value="small" style="font-size: small;">small</option><option value="medium" style="font-size: medium;">medium</option><option value="large" style="font-size: large;">large</option><option value="x-large" style="font-size: x-large;">x-large</option><option value="xx-large" style="font-size: x-large;">xx-large</option></select></td></tr></table></td></tr><tr height="5px"><td></td></tr></table><br>';
				} else {
                                    if (api_get_setting('show_glossary_in_documents') == 'ismanual') {
//                                        $glossary_plugin = '<td width="5px;" class="toolbar_style"><img onclick="glossary();" src="'.api_get_path(WEB_LIBRARY_PATH).'fckeditor/editor/plugins/glossary/glossary.gif" alt="'.get_lang('Glossary').'" title="'.get_lang('Glossary').'"></td>';
                                    }

                                    $fontSize_sizes = '8/8px;9/9px;10/10px;11/11px;12/12px;14/14px;16/16px;18/18px;20/20px;22/22px;24/24px;26/26px;28/28px;36/36px;48/48px;72/72px';
                                    $a_sizes = explode(';', $fontSize_sizes);
                                    $options = '';
                                    foreach ($a_sizes as $size) {
                                        list($num, $px) = explode('/', $size);
                                        $options .= '<option value="'.$px.'">'.$num.'</option>';
                                    }

                                    echo '<table cellspacing="3" width="100%" height="50px" class="toolbar_style_back"><tr><td width="80%"><table width="100%"><tr style="height:5px;"><td colspan="3"></td></tr>
                                    <tr>
                                    <td width="5px"><img src="../img/toolbar_start.gif"></td>
                                    <td width="5px" class="toolbar_style">
                                    <img src="../img/pasteword_icon.png" onclick="word();" alt="'.get_lang('PasteWord').'" title="'.get_lang('PasteWord').'"></td>
                                    <td width="5px"><img src="../img/toolbar_start.gif"></td>
                                    <td width="5px;" class="toolbar_style"><img src="../img/link_icon.png" onclick="link();" alt="'.get_lang('Link').'" title="'.get_lang('Link').'"></td>
                                    <td width="5px"><img src="../img/toolbar_start.gif"></td><td width="5px;" class="toolbar_style">'.Display::return_icon('pixel.gif',get_lang('Images'),array('class'=>'fckactionplaceholdericon fckactionimages_icon','onclick'=>'image();')) .'</td>
                                    <td width="5px;" class="toolbar_style">'.Display::return_icon('pixel.gif',get_lang('Imagemap'),array('class'=>'fckactionplaceholdericon fckactionimagemap','onclick'=>'imagemap();')).'</td>
                                    <td width="5px;" class="toolbar_style">'.Display::return_icon('pixel.gif',get_lang('Mindmap'),array('class'=>'fckactionplaceholdericon fckactionmindmap_18','onclick'=>'mindmap();')).'</td>
                                    <td width="5px;" class="toolbar_style">'.Display::return_icon('pixel.gif',get_lang('Mascot'),array('class'=>'fckactionplaceholdericon fckactionmascot_icon','onclick'=>'mascot();')).'</td>
                                    <td width="5px;" class="toolbar_style">'.Display::return_icon('pixel.gif',get_lang('Videoplayer'),array('class'=>'fckactionplaceholdericon fckactionvideoPlayer','onclick'=>'videoplayer();')).'</td>
                                    <td width="5px;" class="toolbar_style">'.Display::return_icon('pixel.gif',get_lang('Audio'),array('class'=>'fckactionplaceholdericon fckactionaudio','onclick'=>'audio();')).'</td>
                                    '.$glossary_plugin.'
                                    <td width="5px"><img src="../img/toolbar_start.gif"></td>
                                    <td width="5px;" class="toolbar_style">'.Display::return_icon('pixel.gif',get_lang('Table'),array('class'=>'fckactionplaceholdericon fckactiontable','onclick'=>'table();')).'</td>
                                    <td width="5px;" class="toolbar_style"><img src="../img/unordered_list.png" onclick="unordered();" alt="'.get_lang('Orderedlist').'" title="'.get_lang('Orderedlist').'"></td>
                                    <td width="5px;" class="toolbar_style"><img src="../img/view_source.png" onclick="source();" alt="'.get_lang('Source').'" title="'.get_lang('Source').'"></td>
                                    <td width="5px"><img src="../img/toolbar_start.gif"></td>
                                    <td width="5px;" class="toolbar_style"><img src="../img/text_bold.png" onclick="makeitbold();" alt="'.get_lang('Bold').'" title="'.get_lang('Bold').'"></td>
                                    <td width="5px"><img src="../img/toolbar_start.gif"></td><td width="5px;" class="toolbar_style"><img src="../img/text_left.png" onclick="alignleft();" alt="'.get_lang('Alignleft').'" title="'.get_lang('Alignleft').'"></td>
                                    <td width="5px;" class="toolbar_style"><img src="../img/text_center.png" onclick="aligncenter();" alt="'.get_lang('Aligncenter').'" title="'.get_lang('Aligncenter').'"></td>
                                    <td width="5px;" class="toolbar_style">'.Display::return_icon('pixel.gif',get_lang('Textcolor'), array('class' => 'fckactionplaceholdericon fckactionfontcolor', 'onclick' => 'fontcolor(event);')).'</td>
                                    <td width="5px"><img src="../img/toolbar_start.gif"></td>
                                    </tr>
                                    <tr height="5px">
                                    <td></td></tr>
                                    </table></td>
                                    <td>
                                    <table width="100%">
                                    <tr><td><span style="color:#333333;">'.get_lang('FontSize').'</span><select name="font" onchange="fontsize(this.value)"><option></option>'.$options.'</select></td></tr></table></td></tr></table></td></tr></table>';

                                }
				    //echo '<button class="save" type="submit" name="SaveWikiChange">'.get_lang('langSave').'</button><br/>';
				    echo '<div>';

				//echo $icon_assignment.'&nbsp;&nbsp;&nbsp;<strong style="font-size:16px;">'.$title.'</strong>';
				//

				if((api_is_allowed_to_edit() || api_is_platform_admin()) && $row['reflink']!='index')
				{

					//echo'<a id="wiki_options_id" href="javascript:void(0)" ><span id="plus_minus" style="float:right">&nbsp;'.Display::return_icon('div_show.gif',get_lang('Show'),array('style'=>'vertical-align:middle')).'&nbsp;'.get_lang('Works').'</span></a>';

					echo '<div id="options" style="display:none; margin: 20px;" >';

					//task
					echo '<div>&nbsp;</div><input type="checkbox" value="1" name="checktask" onclick="if(this.checked==true){document.getElementById(\'option4\').style.display=\'block\';}else{document.getElementById(\'option4\').style.display=\'none\';}"/>&nbsp;<img src="../img/wiki/task.gif" title="'.get_lang('DefineTask').'" alt="'.get_lang('DefineTask').'"/>'.get_lang('DescriptionOfTheTask').'';
					echo '&nbsp;&nbsp;&nbsp;<span id="msg_error4" style="display:none;color:red"></span>';
					echo '<div id="option4" style="padding:4px; margin:5px; border:1px dotted; display:none;">';

					echo '<table border="0" style="font-weight:normal">';
					echo '<tr>';
					echo '<td>'.get_lang('DescriptionOfTheTask').'</td>';
					echo '</tr>';
					echo '<tr>';
				//	echo '<td>'.api_disp_html_area('task', $row['task'], '', '', null, array('ToolbarSet' => 'wiki_task', 'Width' => '600', 'Height' => '200')).'</td>';
					echo '<td><textarea name="task" rows="5" cols="45">'.stripslashes($row['task']).'</textarea></td>';
					echo '</tr>';
					echo '</table>';
					echo '</div>';

					//feedback
					echo '<div>&nbsp;</div><input type="checkbox" value="1" name="checkfeedback" onclick="if(this.checked==true){document.getElementById(\'option2\').style.display=\'block\';}else{document.getElementById(\'option2\').style.display=\'none\';}"/>&nbsp;'.get_lang('AddFeedback').'';
					echo '&nbsp;&nbsp;&nbsp;<span id="msg_error2" style="display:none;color:red"></span>';
					echo '<div id="option2" style="padding:4px; margin:5px; border:1px dotted; display:none;">';

					echo '<table border="0" style="font-weight:normal" align="center">';
					echo '<tr>';
					echo '<td colspan="2">'.get_lang('Feedback1').'</td>';
					echo '<td colspan="2">'.get_lang('Feedback2').'</td>';
					echo '<td colspan="2">'.get_lang('Feedback3').'</td>';
					echo '</tr>';
					echo '<tr>';
					echo '<td colspan="2"><textarea name="feedback1" cols="17" rows="4" >'.$row['feedback1'].'</textarea></td>';
					echo '<td colspan="2"><textarea name="feedback2" cols="17" rows="4" >'.$row['feedback2'].'</textarea></td>';
					echo '<td colspan="2"><textarea name="feedback3" cols="17" rows="4" >'.$row['feedback3'].'</textarea></td>';
					echo '</tr>';
					echo '<tr>';
					echo '<td>'.get_lang('FProgress').':</td>';
					echo '<td><select name="fprogress1">';
				 	echo '<option value="'.$row['fprogress1'].'" selected>'.$row['fprogress1'].'</option>';
					echo '<option value="10">10</option>
					   <option value="20">20</option>
					   <option value="30">30</option>
					   <option value="40">40</option>
					   <option value="50">50</option>
					   <option value="60">60</option>
					   <option value="70">70</option>
					   <option value="80">80</option>
					   <option value="90">90</option>
					   <option value="100">100</option>
					   </select> %</td>';
					echo '<td>'.get_lang('FProgress').':</td>';
					echo '<td><select name="fprogress2">';
				 	echo '<option value="'.$row['fprogress2'].'" selected>'.$row['fprogress2'].'</option>';
					echo '<option value="10">10</option>
					   <option value="20">20</option>
					   <option value="30">30</option>
					   <option value="40">40</option>
					   <option value="50">50</option>
					   <option value="60">60</option>
					   <option value="70">70</option>
					   <option value="80">80</option>
					   <option value="90">90</option>
					   <option value="100">100</option>
					   </select> %</td>';
					echo '<td>'.get_lang('FProgress').':</td>';
					echo '<td><select name="fprogress3">';
				 	echo '<option value="'.$row['fprogress3'].'" selected>'.$row['fprogress3'].'</option>';
					echo '<option value="10">10</option>
					   <option value="20">20</option>
					   <option value="30">30</option>
					   <option value="40">40</option>
					   <option value="50">50</option>
					   <option value="60">60</option>
					   <option value="70">70</option>
					   <option value="80">80</option>
					   <option value="90">90</option>
					   <option value="100">100</option>
					   </select> %</td>';
					echo '</tr>';
					echo '</table>';
					echo '</div>';

					//time limit
					echo  '<div>&nbsp;</div><input type="checkbox" value="1" name="checktimelimit" onclick="if(this.checked==true){document.getElementById(\'option1\').style.display=\'block\'; $pepe=\'a\';}else{document.getElementById(\'option1\').style.display=\'none\';}"/>&nbsp;'.get_lang('PutATimeLimit').'';
					echo  '&nbsp;&nbsp;&nbsp;<span id="msg_error1" style="display:none;color:red"></span>';
					echo  '<div id="option1" style="padding:4px; margin:5px; border:1px dotted; display:none;">';
					echo '<table width="100%" border="0" style="font-weight:normal">';
					echo '<tr>';
					echo '<td width="20%" align="right">'.get_lang("StartDate").':</td>';
					echo '<td>';
					if ($row['startdate_assig']=='0000-00-00 00:00:00')
					{
						echo draw_date_picker('startdate_assig').' <input type="checkbox" name="initstartdate" value="1"> '.get_lang('Yes').'/'.get_lang('No').'';

					}
					else
					{
						echo draw_date_picker('startdate_assig', $row['startdate_assig']).' <input type="checkbox" name="initstartdate" value="1"> '.get_lang('Yes').'/'.get_lang('No').'';
					}
					echo '</td>';
					echo '</tr>';
					echo '<tr>';
					echo '<td align="right">'.get_lang("EndDate").':</td>';
					echo '<td>';
					if ($row['enddate_assig']=='0000-00-00 00:00:00')
					{
						echo draw_date_picker('enddate_assig').' <input type="checkbox" name="initenddate" value="1"> '.get_lang('Yes').'/'.get_lang('No').'';
					}
					else
					{
						echo draw_date_picker('enddate_assig', $row['enddate_assig']).' <input type="checkbox" name="initenddate" value="1"> '.get_lang('Yes').'/'.get_lang('No').'';
					}
					echo '</td>';
					echo '</tr>';
					echo '<tr>';
					echo '<td align="right">'.get_lang('AllowLaterSends').':</td>';
					if ($row['delayedsubmit']==1)
					{
						$check_uncheck='checked';
					}
					echo '<td><input type="checkbox" name="delayedsubmit" value="1" '.$check_uncheck.'></td>';
					echo '</tr>';
					echo'</table>';
					echo '</div>';

					//other limit
					echo '<div>&nbsp;</div><input type="checkbox" value="1" name="checkotherlimit" onclick="if(this.checked==true){document.getElementById(\'option3\').style.display=\'block\';}else{document.getElementById(\'option3\').style.display=\'none\';}"/>&nbsp;'.get_lang('OtherSettings').'';
					echo '&nbsp;&nbsp;&nbsp;<span id="msg_error3" style="display:none;color:red"></span>';
					echo '<div id="option3" style="padding:4px; margin:5px; border:1px dotted; display:none;">';
					echo '<div style="font-weight:normal"; align="center">'.get_lang('NMaxWords').':&nbsp;<input type="text" name="max_text" size="3" value="'.$row['max_text'].'">&nbsp;&nbsp;'.get_lang('NMaxVersion').':&nbsp;<input type="text" name="max_version" size="3" value="'.$row['max_version'].'"></div>';
					echo '</div>';

					//
					echo '</div>';
				}

				echo '</div>';
				echo '<div id="wikicontent">';
				echo '<input type="hidden" name="page_id" value="'.$page_id.'">';
				echo '<input type="hidden" name="reflink" value="'.$page.'">';
				echo '<input type="hidden" name="title" value="'.(empty($title)?$page:$title).'">';
				api_disp_html_area('wiki_content', $content, '', '', null, api_is_allowed_to_edit(null,true)
					? array('ToolbarSet' => 'TestProposedAnswer', /*'Width' => '100%',*/ 'Height' => '350')
					: array('ToolbarSet' => 'TestProposedAnswer', /*'Width' => '100%',*/ 'Height' => '350', 'UserStatus' => 'student')
				);

				//if(api_is_allowed_to_edit() || api_is_platform_admin()) //off for now
				//{
				//echo get_lang('Comments').':&nbsp;&nbsp;<input type="text" name="comment" size="40" value="'.$row['comment'].'">&nbsp;&nbsp;&nbsp;';
				//}
				echo '<INPUT TYPE="hidden" NAME="assignment" VALUE="'.$row['assignment'].'"/>';
				echo '<INPUT TYPE="hidden" NAME="version" VALUE="'.$row['version'].'"/>';

				//hack date for edit
				echo '<INPUT TYPE="hidden" NAME="startdate_assig" VALUE="'.$row['startdate_assig'].'"/>';
				echo '<INPUT TYPE="hidden" NAME="enddate_assig" VALUE="'.$row['enddate_assig'].'"/>';

				//
				//echo get_lang('Progress').':&nbsp;&nbsp;<select name="progress" id="progress">';
//				echo '<option value="'.$row['progress'].'" selected>'.$row['progress'].'</option>';
//				echo '<option value="10">10</option>
//				<option value="20">20</option>
//				<option value="30">30</option>
//				<option value="40">40</option>
//				<option value="50">50</option>
//				<option value="60">60</option>
//				<option value="70">70</option>
//				<option value="80">80</option>
//				<option value="90">90</option>
//				<option value="100">100</option>
//				</select> %';
				///echo '<br/><br/>';
				echo '<input type="hidden" name="wpost_id" value="'.md5(uniqid(rand(), true)).'">';//prevent double post
				//echo '</div>';
                                if(api_get_setting('search_enabled')=='true'){
                                    //TODO: include language file
                                    echo '<input type="hidden" name="index_document" value="1"/>';
                                    echo '<input type="hidden" name="language" value="'.api_get_setting('platformLanguage').'"/>';
                                    //echo get_lang('SearchKeywords').'&nbsp;&nbsp;<textarea cols="65" name="search_terms">'.$row['keyword'].'</textarea>';

                                }
                echo '<button class="save" type="submit" name="SaveWikiChange"style="margin-top:12px" >'.get_lang('langSave').'</button></div>';//for save button Don't change name (see fckeditor/editor/plugins/customizations/fckplugin_compressed.js and fckplugin.js
				echo '</form>';
			}
		}
	}
}

/////////////////////// page history ///////////////////////


if ((isset($_GET['action']) && $_GET['action']=='history') || isset($_POST['HistoryDifferences']))
{
	if (!$_GET['title'])
	{
		//Display::display_error_message(get_lang("MustSelectPage"), false, true);
                $_SESSION["display_error_message"]=get_lang("MustSelectPage");
		exit;
    }

	$_clean['group_id']=(int)$_SESSION['_gid'];

    //First, see the property visibility that is at the last register and therefore we should select descending order. But to give ownership to each record, this is no longer necessary except for the title. TODO: check this

	$sql='SELECT * FROM '.$tbl_wiki.'WHERE reflink="'.Database::escape_string($page).'" AND '.$groupfilter.$condition_session.' ORDER BY id DESC';
	$result=Database::query($sql,__LINE__,__FILE__);

	while ($row=Database::fetch_array($result))
	{
		$KeyVisibility=$row['visibility'];
		$KeyAssignment=$row['assignment'];
		$KeyTitle=$row['title'];
		$KeyUserId=$row['user_id'];
	}

	    if($KeyAssignment==1)
		{
			$icon_assignment='<img src="../img/wiki/assignment.gif" title="'.get_lang('AssignmentDescExtra').'" alt="'.get_lang('AssignmentDescExtra').'" />';
		}
		elseif($KeyAssignment==2)
		{
			$icon_assignment='<img src="../img/wiki/works.gif" title="'.get_lang('AssignmentWorkExtra').'" alt="'.get_lang('AssignmentWorkExtra').'" />';
		}


	//Second, show

	//if the page is hidden and is a job only sees its author and professor
	if($KeyVisibility==1 || api_is_allowed_to_edit(false,true) || api_is_platform_admin() || ($KeyAssignment==2 && $KeyVisibility==0 && (api_get_user_id()==$KeyUserId)))
	{
		// We show the complete history
		if (!$_POST['HistoryDifferences'] && !$_POST['HistoryDifferences2'] )
		{

			$sql='SELECT * FROM '.$tbl_wiki.'WHERE reflink="'.Database::escape_string($page).'" AND '.$groupfilter.$condition_session.' ORDER BY id DESC';
			$result=Database::query($sql,__LINE__,__FILE__);

			$title		= Security::remove_XSS($_GET['title']);
			$group_id	= Security::remove_XSS($_GET['group_id']);

			//echo '<div id="wikititle">';
			api_display_tool_title($icon_assignment.'&nbsp;'.$KeyTitle);
			//echo '</div>';
			echo '<form id="differences" method="post" action="index.php?cidReq='.$_course[id].'&action=history&page_id='.$page_id.'&title='.$title.'&group_id='.$group_id.'">';

			echo '<ul style="list-style-type: none;">';
			//echo '<br/>';
			//echo '<button class="search" type="submit" name="HistoryDifferences" value="HistoryDifferences">'.get_lang('ShowDifferences').' '.get_lang('LinesDiff').'</button>';
			//echo '<button class="search" type="submit" name="HistoryDifferences2" value="HistoryDifferences2">'.get_lang('ShowDifferences').' '.get_lang('WordsDiff').'</button>';
			//echo '<br/><br/>';

			$counter=0;
			$total_versions=Database::num_rows($result);

			while ($row=Database::fetch_array($result))
			{
				$userinfo=Database::get_user_info_from_id($row['user_id']);
                                
				echo '<li style="margin-bottom: 5px;">';
				($counter==0) ? $oldstyle='style="visibility: hidden;"':$oldstyle='';
				($counter==0) ? $newchecked=' checked':$newchecked='';
				($counter==$total_versions-1) ? $newstyle='style="visibility: hidden;"':$newstyle='';
				($counter==1) ? $oldchecked=' checked':$oldchecked='';
				echo '<input name="old" value="'.$row['id'].'" type="radio" '.$oldstyle.' '.$oldchecked.'/> ';
				echo '<input name="new" value="'.$row['id'].'" type="radio" '.$newstyle.' '.$newchecked.'/> ';
				echo '<a href="'.api_get_self().'?action=showpage&title='.urlencode($page).'&view='.$row['id'].'">';
				echo '<a href="'.api_get_self().'?cidReq='.$_course[id].'&action=showpage&page_id='.$page_id.'&title='.urlencode($page).'&view='.$row['id'].'&group_id='.$group_id.'">';

				echo TimeZone::ConvertTimeFromServerToUser(api_get_user_id(), $row['dtime']);  //$year.'-'.$month.'-'.$day.' '.$hours.":".$minutes.":".$seconds;
				echo '</a>';
				echo ' ('.get_lang('Version').' '.$row['version'].')';
				echo ' '.get_lang('By').' ';
				if ($row['user_id']<>0)
				{
					echo '<a href="../user/userInfo.php?'.api_get_cidreq().'&uInfo='.$userinfo['user_id'].'">'.api_get_person_name($userinfo['firstname'], $userinfo['lastname']).'</a>';
				}
				else
				{
					echo get_lang('Anonymous').' ('.$row[user_ip].')';
				}

				echo ' ( '.get_lang('Progress').': '.$row['progress'].'%, ';
				$comment=$row['comment'];

				if (!empty($comment))
				{
					echo get_lang('Comments').': '.api_substr(api_htmlentities($row['comment'], ENT_QUOTES, $charset),0,100);
					if (api_strlen($row['comment'])>100)
					{
						echo '... ';
					}
				}
				else
				{
					echo get_lang('Comments').':  ---';
				}
				echo ' ) </li>';

				$counter++;
			} //end while
			echo '<br/>';
                        echo '</ul>';                        
                        echo '<div style="float:left;width:100%;">';
                        echo '<div style="float:left;width:50%;text-align: center;">';
                            echo '<button type="submit" class="p-ie7 save" style="float: none!important;font-size: 12px;text-align:center;margin: 0;" name="HistoryDifferences" value="HistoryDifferences">'.get_lang('ShowDifferences').' '.get_lang('LinesDiff').'</button>';
                        echo '</div>';
                        echo '<div style="float:right;width:50%;text-align: center;">';
                            echo '<button class="p-ie7 save" type="submit"style="float: none!important;font-size: 12px;text-align:center;margin: 0;" name="HistoryDifferences2" value="HistoryDifferences2">'.get_lang('ShowDifferences').' '.get_lang('WordsDiff').'</button>';                       
                        echo '</div>';
                        echo '</div>';
                        echo '</form>';
		}
		// We show the differences between two versions
		else
		{
			$sql_old="SELECT * FROM $tbl_wiki WHERE id='".Database::escape_string($_POST['old'])."'";
			$result_old=Database::query($sql_old,__LINE__,__FILE__);
			$version_old=Database::fetch_array($result_old);


			$sql_new="SELECT * FROM $tbl_wiki WHERE id='".Database::escape_string($_POST['new'])."'";
			$result_new=Database::query($sql_new,__LINE__,__FILE__);
			$version_new=Database::fetch_array($result_new);

		    if(isset($_POST['HistoryDifferences']))
			{
				include('diff.inc.php');
				//title
				echo '<div id="wikititle">'.$version_new['title'].' <font size="-2"><i>('.get_lang('DifferencesNew').'</i> <font style="background-color:#aaaaaa">'.TimeZone::ConvertTimeFromServerToUser(api_get_user_id(), $version_new['dtime']).'</font> <i>'.get_lang('DifferencesOld').'</i> <font style="background-color:#aaaaaa">'.$version_old['dtime'].'</font>) '.get_lang('Legend').':  <span class="diffAdded" >'.get_lang(WikiDiffAddedLine).'</span> <span class="diffDeleted" >'.get_lang(WikiDiffDeletedLine).'</span> <span class="diffMoved" >'.get_lang(WikiDiffMovedLine).'</span></font></div>';
			}
			if(isset($_POST['HistoryDifferences2']))
			{
				require_once 'Text/Diff.php';
   	require_once 'Text/Diff/Renderer/inline.php';
				//title
				echo '<div>'.$version_new['title'].' <font size="-2"><i>('.get_lang('DifferencesNew').'</i> <font style="background-color:#aaaaaa">'.TimeZone::ConvertTimeFromServerToUser(api_get_user_id(), $version_new['dtime']).'</font> <i>'.get_lang('DifferencesOld').'</i> <font style="background-color:#aaaaaa">'.$version_old['dtime'].'</font>) '.get_lang('Legend').':  <span class="diffAddedTex" >'.get_lang(WikiDiffAddedTex).'</span> <span class="diffDeletedTex" >'.get_lang(WikiDiffDeletedTex).'</span></font></div>';
			}

			echo '<div class="diff"><br /><br />';

			if(isset($_POST['HistoryDifferences']))
			{
				echo '<table>'.diff( $version_old['content'], $version_new['content'], true, 'format_table_line' ).'</table>'; // format_line mode is better for words
				echo '</div>';

				echo '<br />';
				echo '<strong>'.get_lang('Legend').'</strong><div class="diff">' . "\n";
				echo '<table><tr>';
				echo  '<td>';
				echo '</td><td>';
				echo '<span class="diffEqual" >'.get_lang('WikiDiffUnchangedLine').'</span><br />';
				echo '<span class="diffAdded" >'.get_lang('WikiDiffAddedLine').'</span><br />';
				echo '<span class="diffDeleted" >'.get_lang('WikiDiffDeletedLine').'</span><br />';
				echo '<span class="diffMoved" >'.get_lang('WikiDiffMovedLine').'</span><br />';
				echo '</td>';
				echo '</tr></table>';

				echo '</div>';

			}

                        if(isset($_POST['HistoryDifferences2']))
			{

				$lines1 = array(strip_tags($version_old['content'])); //without <> tags
				$lines2 = array(strip_tags($version_new['content'])); //without <> tags
				$diff = new Text_Diff($lines1, $lines2);

				$renderer = new Text_Diff_Renderer_inline();
				//echo '<style type="text/css">del{background:#fcc}ins{background:#cfc}</style>'.$renderer->render($diff); // Code inline
				echo '<style type="text/css">del{background:#fcc}ins{background:#cfc}</style>'.html_entity_decode($renderer->render($diff)).''; // Html inline. By now, turned off by problems in comparing pages separated by more than one version
				echo '</div>';

				echo '<br />';
				echo '<strong>'.get_lang('Legend').'</strong><div class="diff">' . "\n";
				echo '<table><tr>';
				echo  '<td>';
				echo '</td><td>';
				echo '<span class="diffAddedTex" >'.get_lang('WikiDiffAddedTex').'</span><br />';
				echo '<span class="diffDeletedTex" >'.get_lang('WikiDiffDeletedTex').'</span><br />';
				echo '</td>';
				echo '</tr></table>';

				echo '</div>';

			}
		}
	}

}


/////////////////////// recent changes ///////////////////////
if ($_GET['action']=='recentchanges'){
	$_clean['group_id']=(int)$_SESSION['_gid'];

	// status of the notification icon
	if ( api_is_allowed_to_session_edit(false,true) ) {
		if (check_notify_all()==1)
		{
			$wiki_notification_message= Display::return_icon('pixel.gif',get_lang('FullNotifyByEmail'),array('class'=>'actionplaceholderminiicon actionsendmail_checked','alt'=>get_lang('FullNotifyByEmail'),'style'=>'vertical-align:middle')).get_lang('NotNotifyChanges');
			$lock_unlock_notify_all='unlocknotifyall';
		}
		else
		{
			$wiki_notification_message= Display::return_icon('pixel.gif',get_lang('FullCancelNotifyByEmail'),array('class'=>'actionplaceholderminiicon actionsendmail_16','alt'=>get_lang('FullCancelNotifyByEmail'),'style'=>'vertical-align:middle')). get_lang('NotifyChanges');
			$lock_unlock_notify_all='locknotifyall';
		}

	}

	$wiki_notification_message =  '<a href="index.php?action=recentchanges&actionpage='.$lock_unlock_notify_all.'&page_id='.$page_id.'&title='.urlencode($page).'">'.$wiki_notification_message.'</a>';

	api_display_tool_title(get_lang('RecentChanges'));


	if(api_is_allowed_to_edit(false,true) || api_is_platform_admin()) //only by professors if page is hidden
	{
		//$sql='SELECT * FROM '.$tbl_wiki.' WHERE '.$groupfilter.' ORDER BY dtime DESC'; // old version TODO: Replace by the bottom line

		$sql='SELECT * FROM '.$tbl_wiki.', '.$tbl_wiki_conf.' WHERE '.$tbl_wiki_conf.'.page_id='.$tbl_wiki.'.page_id AND '.$tbl_wiki.'.'.$groupfilter.$condition_session.' ORDER BY dtime DESC'; // new version

	}
	else
	{
		$sql='SELECT * FROM '.$tbl_wiki.' WHERE '.$groupfilter.$condition_session.' AND visibility=1 ORDER BY dtime DESC';	// old version TODO: Replace by the bottom line

		//$sql='SELECT * FROM '.$tbl_wiki.', '.$tbl_wiki_conf.' WHERE '.$tbl_wiki_conf.'.page_id='.$tbl_wiki.'.page_id AND visibility=1 AND '.$tbl_wiki.'.'.$groupfilter.' ORDER BY dtime DESC'; // new version
	}
	$allpages=Database::query($sql,__LINE__,__FILE__);

	//show table
	if (Database::num_rows($allpages) > 0)
	{
		$row = array ();
		while ($obj = Database::fetch_object($allpages))
		{
			//get author
			$userinfo=Database::get_user_info_from_id($obj->user_id);

			//get type assignment icon
			if($obj->assignment==1)
			{
				$ShowAssignment='<img src="../img/wiki/assignment.gif" title="'.get_lang('AssignmentDesc').'" alt="'.get_lang('AssignmentDesc').'" />';
			}
			elseif ($obj->assignment==2)
			{
				$ShowAssignment='<img src="../img/wiki/works.gif" title="'.get_lang('AssignmentWork').'" alt="'.get_lang('AssignmentWork').'" />';
			}
			elseif ($obj->assignment==0)
			{
				$ShowAssignment='<img src="../img/wiki/trans.gif" />';
			}

			//get icon task
			if (!empty($obj->task))
			{
				$icon_task='<img src="../img/wiki/task.gif" title="'.get_lang('StandardTask').'" alt="'.get_lang('StandardTask').'" />';
			}
			else
			{
				$icon_task='<img src="../img/wiki/trans.gif" />';
			}


			$row = array ();
                        $date = TimeZone::ConvertTimeFromServerToUser(api_get_user_id(), $obj->dtime);
                        $date = date('d-m-Y h:i:s a', strtotime($date));
			$row[] = $date;
//			$row[] = $ShowAssignment.$icon_task;
			$row[] = '<a href="'.api_get_self().'?cidReq='.$_course[id].'&action=showpage&title='.urlencode($obj->reflink).'&page_id='.$obj->page_id.'&lcid='.$obj->id.'&group_id='.Security::remove_XSS($_GET['group_id']).'">'.$obj->title.'</a>';
			$row[] = $obj->version>1 ? get_lang('EditedBy') : get_lang('AddedBy');
			//$row[] = $obj->user_id <> 0 ? '<a href="../user/userInfo.php?'.api_get_cidreq().'&uInfo='.$userinfo['user_id'].'">'.api_get_person_name($userinfo['firstname'], $userinfo['lastname']).'</a>' : get_lang('Anonymous').' ('.$obj->user_ip.')';
                        $row[] = $obj->user_id <>0 ? sprintf('<a id="user_id_%s" class="user_info" href="javascript:void(0);">'.api_get_person_name($userinfo['firstname'], $userinfo['lastname']).'</a>',$userinfo['user_id']) : get_lang('Anonymous').' ('.$obj->user_ip.')';
			$rows[] = $row;
		}

		$table = new SortableTableFromArrayConfig($rows,0,10,'RecentPages_table','','','DESC');
		$table->set_additional_parameters(array('cidReq' =>Security::remove_XSS($_GET['cidReq']),'action'=>Security::remove_XSS($_GET['action']),'group_id'=>Security::remove_XSS($_GET['group_id'])));
		$table->set_header(0,get_lang('Date'), true, array ('style' => 'width:200px;'));
//		$table->set_header(1,get_lang('Type'), true, array ('style' => 'width:30px;'));
		$table->set_header(1,get_lang('Title'), true);
		$table->set_header(2,get_lang('Actions'), true, array ('style' => 'width:80px;'));
		$table->set_header(3,get_lang('Author'), true);

		$table->display();
	}
}


/////////////////////// all pages ///////////////////////
if ($_GET['action']=='allpages')
{
	api_display_tool_title(get_lang('AllPages'));

	$_clean['group_id']=(int)$_SESSION['_gid'];
	$group_member_with_wiki_rights = 1;

	$group_properties  = GroupManager :: get_group_properties($_clean['group_id']);

	if ($group_properties['wiki_state']==1)
	{
 		if (!api_is_allowed_to_edit(false,true) and !GroupManager :: is_user_in_group($_user['user_id'], $_SESSION['_gid']))
		{
			//echo api_not_allowed();
			$group_member_with_wiki_rights = 0;

		}
		else {
			$group_member_with_wiki_rights = 1;
		}
	}


	if(api_is_allowed_to_edit(false,true) || api_is_platform_admin()) //only by professors if page is hidden
	{
		//$sql='SELECT  *  FROM  '.$tbl_wiki.' s1 WHERE id=(SELECT MAX(s2.id) FROM '.$tbl_wiki.' s2 WHERE s1.reflink = s2.reflink AND '.$groupfilter.')'; // warning don't use group by reflink because don't return the last version// old version TODO: Replace by the bottom line

		$sql='SELECT * FROM '.$tbl_wiki.', '.$tbl_wiki_conf.' WHERE '.$tbl_wiki_conf.'.page_id='.$tbl_wiki.'.page_id AND '.$tbl_wiki.'.'.$groupfilter.$condition_session.' GROUP BY '.$tbl_wiki.'.page_id'; // new version
	}
	else
	{
		//$sql='SELECT  *  FROM   '.$tbl_wiki.' s1 WHERE visibility=1 AND id=(SELECT MAX(s2.id) FROM '.$tbl_wiki.' s2 WHERE s1.reflink = s2.reflink AND '.$groupfilter.')'; // warning don't use group by reflink because don't return the last version	// old version TODO: Replace by the bottom line

		$sql='SELECT * FROM '.$tbl_wiki.', '.$tbl_wiki_conf.' WHERE visibility=1 AND '.$tbl_wiki_conf.'.page_id='.$tbl_wiki.'.page_id AND '.$tbl_wiki.'.'.$groupfilter.$condition_session.' GROUP BY '.$tbl_wiki.'.page_id'; // new version

	}

	$allpages=Database::query($sql,__LINE__,__FILE__);

	//show table
	if (Database::num_rows($allpages) > 0)
	{
		$row = array ();
		while ($obj = Database::fetch_object($allpages))
		{
			//get author
			$userinfo=Database::get_user_info_from_id($obj->user_id);

			//get type assignment icon
			if($obj->assignment==1)
			{
				$ShowAssignment='<img src="../img/wiki/assignment.gif" title="'.get_lang('AssignmentDesc').'" alt="'.get_lang('AssignmentDesc').'" />';
			}
			elseif ($obj->assignment==2)
			{
				$ShowAssignment='<img src="../img/wiki/works.gif" title="'.get_lang('AssignmentWork').'" alt="'.get_lang('AssignmentWork').'" />';
			}
			elseif ($obj->assignment==0)
			{
				$ShowAssignment='<img src="../img/wiki/trans.gif" />';
			}

			//get icon task
			if (!empty($obj->task))
			{
				$icon_task='<img src="../img/wiki/task.gif" title="'.get_lang('StandardTask').'" alt="'.get_lang('StandardTask').'" />';
			}
			else
			{
				$icon_task='<img src="../img/wiki/trans.gif" />';
			}

			$row = array ();
			//$row[] = $ShowAssignment.$icon_task;
			$row[] = '<a href="'.api_get_self().'?cidReq='.$_course[id].'&action=showpage&title='.urlencode(Security::remove_XSS($obj->reflink)).'&page_id='.Security::remove_XSS($obj->page_id).'&group_id='.Security::remove_XSS($_GET['group_id']).'">'.Display::return_icon('pixel.gif', $obj->title, array('class' => 'actionplaceholdericon actionwikistudenticon')).'&nbsp;'.Security::remove_XSS($obj->title).'</a>';
			$row[] = TimeZone::ConvertTimeFromServerToUser(api_get_user_id(), $obj->dtime);
			$row[] = $obj->user_id <>0 ? sprintf('<a id="user_id_%s" class="user_info" href="javascript:void(0);">'.api_get_person_name($userinfo['firstname'], $userinfo['lastname']).'</a>',$userinfo['user_id']) : get_lang('Anonymous').' ('.$obj->user_ip.')';
                        //$row[] = $obj->user_id <>0 ? '<a id="user_id_%s" class="user_info" href="../user/userInfo.php?'.api_get_cidreq().'&uInfo='.$userinfo['user_id'].'&from_page=wiki">'.api_get_person_name($userinfo['firstname'], $userinfo['lastname']).'</a>' : get_lang('Anonymous').' ('.$obj->user_ip.')';
			if(api_is_allowed_to_edit(false,true)|| api_is_platform_admin())
			{
				$showdelete=' <a href="'.api_get_self().'?cidReq='.$_course[id].'&action=delete&page_id='.Security::remove_XSS($obj->page_id).'&title='.urlencode(Security::remove_XSS($obj->reflink)).'&group_id='.Security::remove_XSS($_GET['group_id']).'">'.Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete'));
                                  //$link = api_get_self().'?cidReq='.$_course[id].'&action=delete&page_id='.Security::remove_XSS($obj->page_id).'&title='.urlencode(Security::remove_XSS($obj->reflink)).'&group_id='.Security::remove_XSS($_GET['group_id']);
                                  //$link2 = api_get_self()."?action=delete&page_id=".$page_id."&title=".urlencode($page)."&delete=yes";
                                    //print_r($link."<br/>".$link2);
                                  //$showdelete=' <a href="javascript:void(0);" onclick="Alert_Confim_Delete(\''.$link.'\');">'.Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete'));
			}
			if (api_is_allowed_to_session_edit(false,true)  && $group_member_with_wiki_rights == 1 )
			$row[] = '<a href="'.api_get_self().'?cidReq='.$_course[id].'&action=edit&page_id='.Security::remove_XSS($obj->page_id).'&title='.urlencode(Security::remove_XSS($obj->reflink)).'&group_id='.Security::remove_XSS($_GET['group_id']).'">'.Display::return_icon('pixel.gif', get_lang('EditPage'), array('class' => 'actionplaceholdericon actionedit')).'</a> <a href="'.api_get_self().'?cidReq='.$_course[id].'&action=discuss&page_id='.Security::remove_XSS($obj->page_id).'&title='.urlencode(Security::remove_XSS($obj->reflink)).'&group_id='.Security::remove_XSS($_GET['group_id']).'">'.Display::return_icon('pixel.gif', get_lang('Discuss'), array('class' => 'actionplaceholdericon actioncomments')).'</a> <a href="'.api_get_self().'?cidReq='.$_course[id].'&action=history&page_id='.Security::remove_XSS($obj->page_id).'&title='.urlencode(Security::remove_XSS($obj->reflink)).'&group_id='.Security::remove_XSS($_GET['group_id']).'">'.Display::return_icon('pixel.gif', get_lang('History'), array('class' => 'actionplaceholdericon actionhistory')).'</a> <a href="'.api_get_self().'?cidReq='.$_course[id].'&action=links&page_id='.Security::remove_XSS($obj->page_id).'&title='.urlencode(Security::remove_XSS($obj->reflink)).'&group_id='.Security::remove_XSS($_GET['group_id']).'">'.Display::return_icon('pixel.gif', get_lang('LinksPages'), array('class' => 'actionplaceholdericon actionwikireference')).'</a>'.$showdelete;
			$rows[] = $row;
		}

		$table = new SortableTableFromArrayConfig($rows,1,10,'AllPages_table','','','ASC');
		$table->set_additional_parameters(array('cidReq' =>Security::remove_XSS($_GET['cidReq']),'action'=>Security::remove_XSS($_GET['action']),'group_id'=>Security::remove_XSS($_GET['group_id'])));
		//$table->set_header(0,get_lang('Type'), true, array ('style' => 'width:30px;'));
		$table->set_header(0,get_lang('Title'), true);
		$table->set_header(1,get_lang('Date').' ('.get_lang('LastVersion').')', true, array ('style' => 'width:170px;'));
		$table->set_header(2,get_lang('Author').' ('.get_lang('LastVersion').')', true, array ('style' => 'width:170px;'));
		if (api_is_allowed_to_session_edit(false,true)  && $group_member_with_wiki_rights == 1)
		$table->set_header(3,get_lang('Actions'), true, array ('style' => 'width:150px;'));
		$table->display();

	}
}

/////////////////////// discuss pages ///////////////////////


if ($_GET['action']=='discuss')
{
	if (api_get_session_id()!=0 && api_is_allowed_to_session_edit(false,true)==false) {
		api_not_allowed();
	}

	if (!$_GET['title'])
	{
		//Display::display_error_message(get_lang("MustSelectPage"));
                $_SESSION["display_error_message"]=get_lang("MustSelectPage");
		exit;
    }

	//first extract the date of last version
	//$sql='SELECT * FROM '.$tbl_wiki.'WHERE reflink="'.Database::escape_string($page).'" AND '.$groupfilter.$condition_session.' ORDER BY id DESC';
	$sql='SELECT * FROM '.$tbl_wiki.'WHERE page_id="'.Database::escape_string($page_id).'" AND '.$groupfilter.$condition_session.' ORDER BY id DESC';
	$result=Database::query($sql,__LINE__,__FILE__);
	$row=Database::fetch_array($result);
	$lastversiondate=$row['dtime'];
	$lastuserinfo=Database::get_user_info_from_id($row['user_id']);

	//select page to discuss
  //$sql='SELECT * FROM '.$tbl_wiki.'WHERE reflink="'.Database::escape_string($page).'" AND '.$groupfilter.$condition_session.' ORDER BY id ASC';
  $sql='SELECT * FROM '.$tbl_wiki.'WHERE page_id="'.Database::escape_string($page_id).'" AND '.$groupfilter.$condition_session.' ORDER BY id ASC';
	$result=Database::query($sql,__LINE__,__FILE__);
	$row=Database::fetch_array($result);
	$id=$row['id'];
	$firstuserid=$row['user_id'];

	//mode assignment: previous to show  page type
	if($row['assignment']==1) {
		$icon_assignment='<img src="../img/wiki/assignment.gif" title="'.get_lang('AssignmentDescExtra').'" alt="'.get_lang('AssignmentDescExtra').'" />';
	} elseif($row['assignment']==2) {
		$icon_assignment='<img src="../img/wiki/works.gif" title="'.get_lang('AssignmentWorkExtra').'" alt="'.get_lang('AssignmentWorkExtra').'" />';
	}


	//Show title and form to discuss if page exist
	if ($id!='')
	{
		//Show discussion to students if isn't hidden. Show page to all teachers if is hidden. Mode assignments: If is hidden, show pages to student only if student is the author
		if($row['visibility_disc']==1 || api_is_allowed_to_edit(false,true) || api_is_platform_admin() || ($row['assignment']==2 && $row['visibility_disc']==0 && (api_get_user_id()==$row['user_id'])))
		{
			//echo '<div class="actions">';

			// discussion action: protecting (locking) the discussion
			if(api_is_allowed_to_edit(false,true) || api_is_platform_admin())
			{
				if (check_addlock_discuss()==1)
				{
					$addlock_disc= Display::return_icon('pixel.gif',get_lang('UnlockDiscussExtra'),array('class'=>'actionplaceholderminiicon actionunlock_16','alt'=>get_lang('UnlockDiscussExtra')));
					$lock_unlock_disc='unlockdisc';
				}

				else
				{
					$addlock_disc= Display::return_icon('pixel.gif',get_lang('LockDiscussExtra'),array('class'=>'actionplaceholderminiicon actionlock_16','alt'=>get_lang('LockDiscussExtra')));
					$lock_unlock_disc='lockdisc';
				}
			}

                        if(api_is_allowed_to_edit(false,true) || api_is_platform_admin())
			{echo '<a href="index.php?action=discuss&actionpage='.$lock_unlock_disc.'&page_id='.$page_id.'&title='.urlencode($page).'">';}
			echo $addlock_disc.'&nbsp;&nbsp;&nbsp;&nbsp;';
                        if(api_is_allowed_to_edit(false,true) || api_is_platform_admin()){
                            echo'</a>';
                        }
			// discussion action: visibility.  Show discussion to students if isn't hidden. Show page to all teachers if is hidden.


			if(api_is_allowed_to_edit(false,true) || api_is_platform_admin())
			{
				if (check_visibility_discuss()==1)
				{
					/// TODO: 	Fix Mode assignments: If is hidden, show discussion to student only if student is the author
					//if(($row['assignment']==2 && $row['visibility_disc']==0 && (api_get_user_id()==$row['user_id']))==false)
					//{
						//$visibility_disc= '<img src="../img/wiki/invisible.gif" title="'.get_lang('HideDiscussExtra').'" alt="'.get_lang('HideDiscussExtra').'" />';

					//}
					$visibility_disc= Display::return_icon('pixel.gif',get_lang('ShowDiscussExtra'),array('class'=>'actionplaceholderminiicon toolactionview' ,'alt'=>get_lang('ShowDiscussExtra')));
					$hide_show_disc='hidedisc';
				}
				else
				{
					$visibility_disc= Display::return_icon('pixel.gif',get_lang('HideDiscussExtra'),array('class'=>'actionplaceholderminiicon toolactionhide','alt'=>get_lang('HideDiscussExtra')));
					$hide_show_disc='showdisc';
				}
			}
                        if(api_is_allowed_to_edit(false,true) || api_is_platform_admin()){
                            echo '<a href="index.php?action=discuss&actionpage='.$hide_show_disc.'&page_id='.$page_id.'&title='.urlencode($page).'">';
                        }
			echo $visibility_disc.'&nbsp;&nbsp;&nbsp;';
                        if(api_is_allowed_to_edit(false,true) || api_is_platform_admin()){
                            echo '</a>';
                        }
			//discussion action: check add rating lock. Show/Hide list to rating for all student
			if(api_is_allowed_to_edit(false,true) || api_is_platform_admin())
			{
				if (check_ratinglock_discuss()==1)
				{
					$ratinglock_disc= Display::return_icon('pixel.gif',get_lang('UnlockRatingDiscussExtra'),array('class'=>'actionplaceholderminiicon actionrating_16','alt'=>get_lang('UnlockRatingDiscussExtra')));
					$lock_unlock_rating_disc='unlockrating';
				}
				else
				{
					$ratinglock_disc= Display::return_icon('pixel.gif',get_lang('LockRatingDiscussExtra'),array('class'=>'actionplaceholderminiicon actionrating_na','alt'=>get_lang('LockRatingDiscussExtra')));
					$lock_unlock_rating_disc='lockrating';
				}
			}
                        if(api_is_allowed_to_edit(false,true) || api_is_platform_admin()){
                            echo '<a href="index.php?action=discuss&actionpage='.$lock_unlock_rating_disc.'&page_id='.$page_id.'&title='.urlencode($page).'">';
                        }
			echo $ratinglock_disc;
                        if(api_is_allowed_to_edit(false,true) || api_is_platform_admin()){
                        echo '</a>';
                        }
			//discussion action: email notification
			if (check_notify_discuss($page_id)==1)
			{
				$notify_disc= Display::return_icon('pixel.gif',get_lang('NotifyDiscussByEmail'),array('class'=>'actionplaceholderminiicon actionsendmail_checked','alt'=>get_lang('NotifyDiscussByEmail'))).get_lang('NotifyDiscussByEmail');
				$lock_unlock_notify_disc='unlocknotifydisc';
			}
			else
			{
				$notify_disc= Display::return_icon('pixel.gif',get_lang('CancelNotifyDiscussByEmail'),array('class'=>'actionplaceholderminiicon actionsendmail_16','alt'=>get_lang('CancelNotifyDiscussByEmail'))).get_lang('CancelNotifyDiscussByEmail');
				$lock_unlock_notify_disc='locknotifydisc';
			}
			$wiki_notification_message = '<a href="index.php?action=discuss&actionpage='.$lock_unlock_notify_disc.'&page_id='.$page_id.'&title='.urlencode($page).'">'.$notify_disc.'</a>';

			//echo '</div>';

			api_display_tool_title($icon_assignment.' '.$row['title']);

                        //Display::display_warning_message($wiki_notification_message, false,true);
                        $_SESSION["display_warning_message"]=$wiki_notification_message;

			//echo ' ('.get_lang('MostRecentVersionBy').' <a href="../user/userInfo.php?'.api_get_cidreq().'&uInfo='.$lastuserinfo['user_id'].'">'.api_get_person_name($lastuserinfo['firstname'], $lastuserinfo['lastname']).'</a> '.$lastversiondate.$countWPost.')'.$avg_WPost_score.' '; //TODO: read avg score
                        //$date = TimeZone::ConvertTimeFromServerToUser(api_get_user_id(), strtotime($ann_date[$announcementId]));
                        if(api_is_allowed_to_edit(false,true) || api_is_platform_admin()) //only by professors if page is hidden
                        {
                                $sql='SELECT * FROM '.$tbl_wiki.', '.$tbl_wiki_conf.' WHERE '.$tbl_wiki_conf.'.page_id='.$tbl_wiki.'.page_id AND '.$tbl_wiki.'.'.$groupfilter.$condition_session.' ORDER BY dtime DESC LIMIT 1'; // new version
                        }
                        else
                        {
                                $sql='SELECT * FROM '.$tbl_wiki.' WHERE '.$groupfilter.$condition_session.' AND visibility=1 ORDER BY dtime DESC';	// old version TODO: Replace by the bottom line

                        }
                        $allpages1=Database::query($sql,__LINE__,__FILE__);
                        $allpages2 = Database::fetch_array($allpages1);
                        $date = TimeZone::ConvertTimeFromServerToUser(api_get_user_id(), strtotime($allpages2["dtime"]));
                        $date = date('d-m-Y h:i:s a', strtotime($date));
                        echo ' ('.get_lang('MostRecentVersionBy').''. sprintf(' <a id="user_id_%s" class="user_info" href="javascript:void(0);">'.api_get_person_name($lastuserinfo['firstname'], $lastuserinfo['lastname']).'</a>',$lastuserinfo['user_id']).' '. $date. $countWPost.')'.$avg_WPost_score.' '; //TODO: read avg score
			if($row['addlock_disc']==1 || api_is_allowed_to_edit(false,true) || api_is_platform_admin()) //show comments but students can't add theirs
			{
				?>
				<form name="form1_discuss" method="post" action="">
				<table>
					<tr>
					<td valign="top" ><?php echo get_lang('Comments');?>:</td>
                    <?php  echo '<input type="hidden" name="wpost_id" value="'.md5(uniqid(rand(), true)).'">';//prevent double post ?>
					<td><textarea name="comment" cols="75" rows="5" id="comment" class="focus" ></textarea></td>
					</tr>

					<tr>

					<?php
					//check if rating is allowed
					if($row['ratinglock_disc']==1 || api_is_allowed_to_edit(false,true) || api_is_platform_admin())
					{
						?>
						<td><?php echo get_lang('Rating');?>: </td>
						<td valign="top"><select name="rating" id="rating">
						   <option value="-" selected>-</option>
						   <option value="0">0</option>
						   <option value="1">1</option>
						   <option value="2">2</option>
						   <option value="3">3</option>
						   <option value="4">4</option>
						   <option value="5">5</option>
						   <option value="6">6</option>
						   <option value="7">7</option>
						   <option value="8">8</option>
						   <option value="9">9</option>
						   <option value="10">10</option>
						   </select></td>
						<?php
                    }
					 else
					{
					 	echo '<input type=hidden name="rating" value="-">';// must pass a default value to avoid rate automatically
					}
					?>
					</tr>
					<tr>
			        <td>&nbsp;</td>
					<td> <?php  echo '<button class="save" type="submit" name="Submit"> '.get_lang('Send').'</button>'; ?></td>
				  	</tr>
				</table>
				</form>

				<?php
				if (isset($_POST['Submit']) && double_post($_POST['wpost_id']))
				{                                    
					$dtime = date( "Y-m-d H:i:s" );
					$message_author=api_get_user_id();

					$sql="INSERT INTO $tbl_wiki_discuss (publication_id, userc_id, comment, p_score, dtime) VALUES ('".Database::escape_string($id)."','".Database::escape_string($message_author)."','".Database::escape_string($_POST['comment'])."','".Database::escape_string($_POST['rating'])."','".$dtime."')";
					$result=Database::query($sql,__FILE__,__LINE__) or die(mysql_error());
                                        //$_SESSION["display_confirmation_message"] = get_lang('NewDiscussionAdded');
                                        Display::display_confirmation_message2(get_lang('NewDiscussionAdded'),false ,true);
					check_emailcue($id, 'D', $dtime, $message_author);                                        
                                        $param = '&page_id='.$_GET['page_id'].'&action='.$_GET['action'].'&title='.$_GET['title'] ;
                                        $url = api_get_path(WEB_CODE_PATH).'wiki/index.php?'.api_get_cidreq().$param;
                                        header("Location: " . $url );	

				}
			}//end discuss lock

			echo '<hr noshade size="1">';
			$user_table = Database :: get_main_table(TABLE_MAIN_USER);

			$sql="SELECT * FROM $tbl_wiki_discuss reviews, $user_table user  WHERE reviews.publication_id='".$id."' AND user.user_id='".$firstuserid."' ORDER BY id DESC";
			$result=Database::query($sql,__FILE__,__LINE__) or die(mysql_error());

			$countWPost = Database::num_rows($result);
			echo get_lang('NumComments').": ".$countWPost; //comment's numbers

			$sql="SELECT SUM(p_score) as sumWPost FROM $tbl_wiki_discuss WHERE publication_id='".$id."' AND NOT p_score='-' ORDER BY id DESC";
			$result2=Database::query($sql,__FILE__,__LINE__) or die(mysql_error());
			$row2=Database::fetch_array($result2);

			$sql="SELECT * FROM $tbl_wiki_discuss WHERE publication_id='".$id."' AND NOT p_score='-'";
			$result3=Database::query($sql,__FILE__,__LINE__) or die(mysql_error());
			$countWPost_score= Database::num_rows($result3);

			echo ' - '.get_lang('NumCommentsScore').': '.$countWPost_score;//

			if ($countWPost_score!=0)
			{
				$avg_WPost_score = round($row2['sumWPost'] / $countWPost_score,2).' / 10';
                                $wiki_score = round($row2['sumWPost'] / $countWPost_score,2);
			}
			else
			{
				$avg_WPost_score = $countWPost_score;
			}

			echo ' - '.get_lang('RatingMedia').': '.$avg_WPost_score; // average rating

			//$sql='UPDATE '.$tbl_wiki.' SET score="'.Database::escape_string($avg_WPost_score).'" WHERE reflink="'.Database::escape_string($page).'" AND '.$groupfilter.$condition_session;	// check if work ok. TODO:
			$sql='UPDATE '.$tbl_wiki.' SET score="'.Database::escape_string($avg_WPost_score).'" WHERE page_id="'.Database::escape_string($page_id).'" AND '.$groupfilter.$condition_session;	// check if work ok. TODO:
				Database::query($sql,__FILE__,__LINE__);

			echo '<hr noshade size="1">';
			//echo '<div style="overflow:auto; height:170px;">';

			while ($row=Database::fetch_array($result))
			{
				$userinfo=Database::get_user_info_from_id($row['userc_id']);
				if (($userinfo['status'])=="5")
				{
					$author_status=get_lang('Student');
				}
				else
				{
					$author_status=get_lang('Teacher');
				}

				require_once api_get_path(INCLUDE_PATH).'/lib/usermanager.lib.php';
				$user_id=$row['userc_id'];
				$name = api_get_person_name($userinfo['firstname'], $userinfo['lastname']);
				$attrb=array();
				if ($user_id<>0)
				{
					$image_path = UserManager::get_user_picture_path_by_id($user_id,'web',false, true);
					$image_repository = $image_path['dir'];
					$existing_image = $image_path['file'];
					$author_photo= '<img src="'.$image_repository.$existing_image.'" alt="'.$name.'"  width="40" height="50" align="top" title="'.$name.'"  />';

				}
				else
				{
					$author_photo= '<img src="'.api_get_path(WEB_CODE_PATH)."img/unknown.jpg".'" alt="'.$name.'"  width="40" height="50" align="top"  title="'.$name.'"  />';
				}

				//stars
				$p_score=$row['p_score'];
				switch($p_score){
				case  0:
                                $imagerating=Display::return_icon('pixel.gif','',array('class'=>'actionplaceholderimedicon actionmedstarts00'));
				break;
				case  1:
                                $imagerating=Display::return_icon('pixel.gif','',array('class'=>'actionplaceholderimedicon actionmedstarts05'));
				break;
				case  2:
				$imagerating=Display::return_icon('pixel.gif','',array('class'=>'actionplaceholderimedicon actionmedstarts10'));
				break;
				case  3:
                                $imagerating=Display::return_icon('pixel.gif','',array('class'=>'actionplaceholderimedicon actionmedstarts15'));
				break;
				case  4:
				$imagerating=Display::return_icon('pixel.gif','',array('class'=>'actionplaceholderimedicon actionmedstarts20'));
				break;
				case  5:
				$imagerating=Display::return_icon('pixel.gif','',array('class'=>'actionplaceholderimedicon actionmedstarts25'));
				break;
				case  6:
				$imagerating=Display::return_icon('pixel.gif','',array('class'=>'actionplaceholderimedicon actionmedstarts30'));
				break;
				case  7:
				$imagerating=Display::return_icon('pixel.gif','',array('class'=>'actionplaceholderimedicon actionmedstarts35'));
				break;
				case  8:
				$imagerating=Display::return_icon('pixel.gif','',array('class'=>'actionplaceholderimedicon actionmedstarts40'));
				break;
				case  9:
				$imagerating=Display::return_icon('pixel.gif','',array('class'=>'actionplaceholderimedicon actionmedstarts45'));
				break;
				case  10:
				$imagerating=Display::return_icon('pixel.gif','',array('class'=>'actionplaceholderimedicon actionmedstarts50'));
				break;
			}

			echo '<p><table>';
			echo '<tr>';
			echo '<td rowspan="2">'.$author_photo.'</td>';
			//echo '<td style=" color:#999999"><a href="../user/userInfo.php?'.api_get_cidreq().'&uInfo='.$userinfo['user_id'].'">'.api_get_person_name($userinfo['firstname'], $userinfo['lastname']).'</a> ('.$author_status.') '.$row['dtime'].' - '.get_lang('Rating').': '.$row['p_score'].' '.$imagerating.' </td>';
                        $date = TimeZone::ConvertTimeFromServerToUser(api_get_user_id(), $row['dtime']);
                        $date = date('d-m-Y h:i:s a', strtotime($date));
                        echo '<td style=" color:#999999">'.sprintf('<a id="user_id_%s" class="user_info" href="javascript:void(0);">'.api_get_person_name($userinfo['firstname'], $userinfo['lastname']).'</a>',$userinfo['user_id']).' ('.$author_status.') '. $date .' - '.  get_lang('Rating').': '.$row['p_score'].' '.$imagerating.'</td>';
			echo '</tr>';
			echo '<tr>';
			echo '<td>'.$row['comment'].'</td>';
			echo '</tr>';
			echo "</table>";
			echo '<hr noshade size="1">';

			}
		}
		else
		{

			//Display::display_warning_message(get_lang('LockByTeacher'),false, true);
                        $_SESSION["display_warning_message"]=get_lang('LockByTeacher');

		}
	}
	else
	{

			Display::display_normal_message(get_lang('DiscussNotAvailable'), false, true);

	}
}

///in new pages go to new page
if ($_POST['SaveWikiNew'])
{
	display_wiki_entry(Security::remove_XSS($_POST['reflink']),$progress,$count_words,$wiki_score);
}

echo '</div>';

echo '<div class="actions">';
if($group_member_with_wiki_rights){
//menu more
echo '<a href="index.php?cidReq='.$_course[id].'&action=more&page_id='.$page_id.'&title='.urlencode($page).'"'.is_active_navigation_tab('more').'>'.Display::return_icon('pixel.gif',get_lang('More'),array('class' => 'actionplaceholdericon actionaddpage')).get_lang('More').'</a>';
//menu delete all wiki
if(api_is_allowed_to_edit(false,true) || api_is_platform_admin()) {
  echo '<a href="index.php?cidReq='.$_course[id].'&action=deletewiki&page_id='.$page_id.'&title='.urlencode($page).'"'.is_active_navigation_tab('deletewiki').'>'.Display::return_icon('pixel.gif',get_lang('DeleteWiki'),array('class' => 'actionplaceholdericon actiondeleteall')).get_lang('DeleteWiki').'</a>';
    //$link = 'index.php?cidReq='.$_course[id].'&action=deletewiki&page_id='.$page_id.'&delete=yes&title='.urlencode($page).'';
    //'.api_get_self().'?action=deletewiki&delete=yes">'.get_lang('Yes').'
    //echo '<a href="javascript:void(0);" '.is_active_navigation_tab('deletewiki').' onclick="Alert_Confim_Delete(\''.$link.'\');">'.Display::return_icon('pixel.gif',get_lang('DeleteWiki'),array('class' => 'actionplaceholdericon actiondeleteall')).get_lang('DeleteWiki').'</a>';
}
	///menu recent changes
	echo '<a href="index.php?cidReq='.$_course[id].'&action=recentchanges&group_id='.$_clean['group_id'].'"'.is_active_navigation_tab('recentchanges').'>'.Display::return_icon('pixel.gif',get_lang('RecentChanges'),array('class' => 'actionplaceholdericon actionlatestchanges')).get_lang('RecentChanges').'</a>';
  if (!in_array($_GET['action'], array('addnew', 'searchpages', 'allpages', 'recentchanges', 'deletewiki', 'more', 'mactiveusers', 'mvisited', 'mostchanged', 'orphaned', 'wanted'))) {
    if (api_is_allowed_to_session_edit(false,true) ) {
     //menu edit page
     //echo '<a href="index.php?cidReq='.Security::remove_XSS($_GET['cidReq']).'&page_id='.$page_id.'&action=edit&title='.urlencode($page).'&group_id='.$_clean['group_id'].'"'.is_active_navigation_tab('edit').'>'.Display::return_icon('edit_link.png',get_lang('EditThisPage')).' '.get_lang('EditPage').'</a>';

     //menu discuss page
     echo '<a href="index.php?cidReq='.Security::remove_XSS($_GET['cidReq']).'&page_id='.$page_id.'&action=discuss&title='.urlencode($page).'"'.is_active_navigation_tab('discuss').'>'.Display::return_icon('pixel.gif',get_lang('DiscussThisPage'),array('class' => 'actionplaceholdericon actioncomments')).get_lang('Discuss').'</a>';
     }

    //menu history
    echo '<a href="index.php?cidReq='.Security::remove_XSS($_GET['cidReq']).'&page_id='.$page_id.'&action=history&title='.urlencode($page).'&group_id='.$_clean['group_id'].'"'.is_active_navigation_tab('history').'>'.Display::return_icon('pixel.gif',get_lang('ShowPageHistory'),array('class' => 'actionplaceholdericon actionhistory')).get_lang('History').'</a>';
    //menu linkspages
   //	echo '<a href="index.php?action=links&title='.urlencode($page).'"'.is_active_navigation_tab('links').'>'.Display::display_icon('lp_link.png',get_lang('ShowLinksPages')).' '.get_lang('LinksPages').'</a>';

    //menu delete wikipage
    if(api_is_allowed_to_edit(false,true) || api_is_platform_admin()){
     echo '<a href="index.php?cidReq='.Security::remove_XSS($_GET['cidReq']).'&page_id='.$page_id.'&action=delete&title='.urlencode($page).'"'.is_active_navigation_tab('delete').'>'.Display::return_icon('pixel.gif',get_lang('DeleteThisPage'),array('class' => 'actionplaceholdericon actiondelete')).get_lang('Delete').'</a>';
     //$link = 'index.php?cidReq='.Security::remove_XSS($_GET['cidReq']).'&page_id='.$page_id.'&action=delete&title='.urlencode($page);
    //echo '<a href="javascript:void(0);"'.is_active_navigation_tab('delete').' onclick="Alert_Confim_Delete(\''.$link.'\');" >'.Display::return_icon('pixel.gif',get_lang('DeleteThisPage'),array('class' => 'actionplaceholdericon actiondelete')).get_lang('Delete').'</a>';
    }
//    if($progress==""){
//        $progress=0;
//    }
//  echo '&nbsp;&nbsp;&nbsp;'.get_lang('Progress').': '.$progress.'%&nbsp;&nbsp;&nbsp;'.get_lang('Rating').': '.$wiki_score.'&nbsp;&nbsp;&nbsp;'.get_lang('Words').': '.$count_words;
  
    }
}
if ( api_is_allowed_to_session_edit(false,true) AND $group_member_with_wiki_rights ) {
            //menu add page
            echo '<a href="index.php?'.api_get_cidreq().'&action=addnew&group_id='.$_clean['group_id'].'"'.is_active_navigation_tab('addnew').'>'.Display::return_icon('pixel.gif',get_lang('AddNew'),array('class' => 'actionplaceholdericon  actiondoctext_16x16')).get_lang('AddNew').'</a> ';
}
  

	
	 ///menu all pages
  echo '<a href="index.php?'.api_get_cidreq().'&action=allpages&group_id='.$_clean['group_id'].'"'.is_active_navigation_tab('allpages').'>'.Display::return_icon('pixel.gif',get_lang('AllPages'),array('class' => 'actionplaceholdericon actionmini_wiki')).get_lang('AllPages').'</a>';	

 ///menu lock
 if(api_is_allowed_to_edit(false,true) || api_is_platform_admin()){
		// page action: enable or disable the adding of new pages
		if (check_addnewpagelock()==0)
		{
			//$protect_addnewpage= '<img src="../img/wiki/lockadd.gif" title="'.get_lang('AddOptionProtected').'" alt="'.get_lang('AddOptionProtected').'" width="8" height="8" />';
                        $protect_addnewpage= Display::return_icon('pixel.gif',get_lang('AddOptionProtected'),array('class' => 'actionplaceholdericon actionunlock_16')).get_lang('Lock').'';
			$lock_unlock_addnew='unlockaddnew';
  }
		else
		{
			//$protect_addnewpage= '<img src="../img/wiki/unlockadd.gif" title="'.get_lang('AddOptionUnprotected').'" alt="'.get_lang('AddOptionUnprotected').'" width="8" height="8" />';
                        $protect_addnewpage= Display::return_icon('pixel.gif',get_lang('AddOptionUnprotected'),array('class' => 'actionplaceholdericon actionunlock')).get_lang('Unlock').'';
			$lock_unlock_addnew='lockaddnew';
}
                echo '<a href="index.php?'.api_get_cidreq().'&action=show&actionpage='.$lock_unlock_addnew.'&page   _id='.$page_id.'&title='.urlencode($page).'">'.$protect_addnewpage.'</a>';
	}
  
 echo '</div>';
//echo "</div>"; // echo "<div id='mainwiki'>";

//echo "</div>"; // echo "<div id='wikiwrapper'>";

if(isset($_SESSION["display_warning_message"])){ 
    Display::display_warning_message($_SESSION["display_warning_message"], false, true);
    unset($_SESSION["display_warning_message"]);
}
Display::display_footer();
?>
