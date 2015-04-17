<?php

/* For licensing terms, see /dokeos_license.txt */

/**
  ==============================================================================
 * 	@package dokeos.document
 * 	@todo the implementation of the popup is very ucky and should be refactored
  ==============================================================================
 */
// Language files that should be included
$language_file = array('document');

// setting the help
$help_content = 'mediabox';

// include the global Dokeos file
require_once '../inc/global.inc.php';

// include additional libraries
require_once api_get_path(LIBRARY_PATH) . 'fileDisplay.lib.php';
require_once 'slideshow.inc.php';
require_once api_get_path(LIBRARY_PATH) . 'document.lib.php';
// section (for the tabs)
$this_section = SECTION_COURSES;


// session id
$session_id = api_get_session_id();

// variable initialisation
$noPHP_SELF = true;
$path = Security::remove_XSS($_GET['curdirpath']);
$pathurl = urlencode($path);

// additional javascript, css, ...
//$htmlHeadXtra[] = '<script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-1.4.2.min.js" type="text/javascript"></script>';
$htmlHeadXtra[] = '<script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/dhtmlwindow.js" type="text/javascript"></script>';
$htmlHeadXtra[] = '<script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/modal.js" type="text/javascript"></script>';
$htmlHeadXtra[] = '<script src="' . api_get_path(WEB_LIBRARY_PATH) . 'jwplayer/jwplayer.js" type="text/javascript"></script>';


$htmlHeadXtra[] = '<script type="text/javascript">    
    function closeWindow(){   
            $("div[role=\'dialog\']").html("close");
        }
$(document).ready(function() {
    $(".open_media_audio_in_window").click(function(event) {
         var attr_info = $(this).attr("id");
         var content_info = attr_info.split("media_content_");
         var content_id = content_info[1];
         var object_id = "#popUpDiv"+content_id;
         $(object_id).dialog({            
            width:430 ,
            height:77 ,
            modal: true,
            resizable: false,
            beforeclose: function(event, ui) {
                if ($("#mediaplayer"+content_id).length > 0) {
                    jwplayer("mediaplayer"+content_id).stop();
                }
            },
            close: function(event, ui) {
                if ($("#mediaplayer"+content_id).length > 0) {
                    jwplayer("mediaplayer"+content_id).stop();
                }
            },
            open: function(event, ui) {
               $(".ui-icon-closethick").css("display","none");                   
               $("#closeButton").remove();              
               $("div[class=\"ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix\"]").append("<span id=\"closeButton\" style=\"float:right;cursor:pointer;margin-right:4px;\" onclick=closeWindow()>'
                                                    .get_lang("Close").
                                                        '</span>"); 
                    
                if ($("#mediaplayer"+content_id).length > 0) {
                    jwplayer("mediaplayer"+content_id).play();
                }
            } 
         });$(".ui-corner-all").removeClass("ui-dialog-titlebar-close");
     });     
     $(".open_media_video_in_window").click(function(event) {     
         var attr_info = $(this).attr("id");
         var content_info = attr_info.split("media_content_");
         var content_id = content_info[1];
         var object_id = "#popUpDiv"+content_id; 
         $(object_id).dialog({
            width:430 ,
            height:355 ,
            modal: true,
            resizable: false,
            beforeclose: function(event, ui) {
                if ($("#mediaplayer"+content_id).length > 0) {
                    jwplayer("mediaplayer"+content_id).stop();
                }
            },
            close: function(event, ui) {
                if ($("#mediaplayer"+content_id).length > 0) {
                    jwplayer("mediaplayer"+content_id).stop();
                }
            },
            open: function(event, ui) {
             $(".ui-icon-closethick").css("display","none");                   
               $("#closeButton").remove();              
               $("div[class=\"ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix\"]").append("<span id=\"closeButton\" style=\"float:right;cursor:pointer;margin-right:4px;\" onclick=closeWindow()>'
                                                    .get_lang("Close").
                                                        '</span>"); 
                    
                if ($("#mediaplayer"+content_id).length > 0) {
                    jwplayer("mediaplayer"+content_id).play();
                } 
            }
         });$(".ui-corner-all").removeClass("ui-dialog-titlebar-close");
     });
  });
  function showAnimation(mediaSrc){
   
   
    $("#id1").append($("<iframe/>").attr("src", mediaSrc).css({"height":"540","width":"720"})).dialog({  
    width:760,
    height:590,
    resizable:false,
    title:"'.get_lang("Animations").'",
    open: function(event, ui){
    $(".ui-icon-closethick").css("display","none");                   
               $("#closeButton").remove();              
               $("div[class=\"ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix\"]").append("<span id=\"closeButton\" style=\"float:right;cursor:pointer;margin-right:4px;\" onclick=closeWindow()>'
                                                    .get_lang("Close").
                                                        '</span>"); 
      }                                 
    });
    $(".ui-corner-all").removeClass("ui-dialog-titlebar-close");
  } 
  </script>';


$htmlHeadXtra[] = '<style type="text/css" media="all">@import "' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/modal.css";</style>';
$htmlHeadXtra[] = '<style type="text/css" media="all">@import "' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/dhtmlwindow.css";</style>';


// adding the breadcrumbs
$interbreadcrumb[] = array("url" => Security::remove_XSS('document.php?curdirpath=' . $pathurl), 'name' => get_lang('Mediabox'));

$htmlHeadXtra[] = "<script type=\"text/javascript\">
function confirmation(name) {
    if (confirm(\" " . get_lang("AreYouSureToDelete") . " \"+ name + \" ?\")) {
        return true;
    } else{
        return false;
    }
}
</script>";

// Get navigator info
$navigator_info = api_get_navigator();

// display the header
Display :: display_tool_header(get_lang('Mediabox'), "Doc");

// Database table initialisation
$tbl_documents = Database::get_course_table(TABLE_DOCUMENT);
$propTable = Database::get_course_table(TABLE_ITEM_PROPERTY);

// feedback messages
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'DEL':
            //Display::display_confirmation_message(get_lang('DocDeleted'));
            $_SESSION['display_confirmation_message'] = get_lang('DocDeleted');
            break;
        case 'ERR':
            //Display::display_error_message(get_lang('DocDeleteError'));
            $_SESSION["display_error_message"] = get_lang('DocDeleteError');
            break;
        case 'ViMod':
            //Display::display_confirmation_message(get_lang('ViMod'));
            $_SESSION['display_confirmation_message'] = get_lang('ViMod');
            break;
        case 'ViModProb':
            //Display::display_error_message(get_lang('ViModProb'));
            $_SESSION["display_error_message"] = get_lang('ViModProb');
            break;
    }
}

$query = "SELECT * FROM $tbl_documents doc,$propTable prop WHERE doc.id = prop.ref AND prop.tool = '" . TOOL_DOCUMENT . "' AND doc.filetype = 'folder' AND doc.path ='" . urldecode($pathurl) . "'";
$result = api_sql_query($query, __FILE__, __LINE__);
$row = Database::fetch_array($result);

$visibility_icon = ($row['visibility'] == 0) ? 'closedeye_tr' : 'dokeoseyeopen22';
$visibility_command = ($row['visibility'] == 0) ? 'set_visible' : 'set_invisible';
$visibility_title = ($row['visibility'] == 0) ? 'UnPublished' : 'Published';

// actions
echo '<div class="actions">';
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'document/document.php?' . api_get_cidReq() . '">' . Display::return_icon('pixel.gif', get_lang('Documents'), array('class' => 'toolactionplaceholdericon toolactiondocument')) . ' ' . get_lang('Documents') . '</a>';
if (!isset($_GET['document'])) {
//    echo '<a href="document.php?' . api_get_cidReq() . '">' . Display::return_icon('pixel.gif', get_lang('Documents'), array('class' => 'toolactionplaceholdericon toolactionback')) . get_lang('Documents') . '</a>';
}
//echo '<a href="mediabox.php?'.api_get_cidReq().'&curdirpath='.$pathurl.'">'.Display::return_icon('pixel.gif', get_lang('Mediabox'), array('class' => 'toolactionplaceholdericon toolactionmediabox')).get_lang('Mediabox').'</a>';
if (api_is_allowed_to_edit()) {
    echo '<a href="document.php?' . api_get_cidReq() . '&action=exit_slideshow&curdirpath=' . $pathurl . '&document=0">' . Display::return_icon('pixel.gif', get_lang('ListView'), array('class' => 'toolactionplaceholdericon toolactionlist')) . get_lang('ListView') . '</a>';
}

if (api_is_allowed_to_edit()) {
    if ($pathurl == '%2Fvideo' || $pathurl == '%2Fscreencasts') {
        if (api_get_setting('enable_webtv_tool') == 'false') {
            echo '<a href="javascript:void(0)" onclick="return WebTvChecked(event, 0);">' . Display::return_icon('pixel.gif', get_lang('UplUpload'), array('class' => 'toolactionplaceholdericon toolactionupload')) . get_lang('UplUpload') . '</a>';
        } else {
            echo '<a href="javascript:void(0)" onclick="return WebTvChecked(event, 1);">' . Display::return_icon('pixel.gif', get_lang('UplUpload'), array('class' => 'toolactionplaceholdericon toolactionupload')) . get_lang('UplUpload') . '</a>';
        }
    } else {
        echo '<a href="upload.php?' . api_get_cidReq() . '&path=' . $pathurl . '">' . Display::return_icon('pixel.gif', get_lang('UplUpload'), array('class' => 'toolactionplaceholdericon toolactionupload')) . get_lang('UplUpload') . '</a>';
    }
}

echo '</div>';

// start the content div
echo '<div id="content">';
/*
  ======================================================
 * DISPLAY MESSAGE
  ======================================================
 */
if (isset($_SESSION['display_normal_message'])) {
    display::display_normal_message($_SESSION['display_normal_message'], false, true);
    unset($_SESSION['display_normal_message']);
}
if (isset($_SESSION['display_warning_message'])) {
    display::display_warning_message($_SESSION['display_warning_message'], false, true);
    unset($_SESSION['display_warning_message']);
}
if (isset($_SESSION['display_confirmation_message'])) {
    display::display_confirmation_message($_SESSION['display_confirmation_message'], false, true);
    unset($_SESSION['display_confirmation_message']);
}
if (isset($_SESSION['display_error_message'])) {
    display::display_error_message($_SESSION['display_error_message'], false, true);
    unset($_SESSION['display_error_message']);
}
// Check if folder exists, admin with the course copy tool sometimes copy only files and no folders
$count_if_folder_exists = DocumentManager::check_if_folder_exists($path);

if (api_is_allowed_to_edit()) {
    $visibility = 'visibility';
} else {
    $visibility = DocumentManager::get_visibility($value_media['path'], $_course, $session_id);
}

if ($path == '/audio' && $count_if_folder_exists > 0) {
    $sql = "SELECT * FROM $tbl_documents doc,$propTable prop WHERE doc.id = prop.ref AND prop.tool = '" . TOOL_DOCUMENT . "' AND doc.filetype = 'file' AND doc.path LIKE '/audio/%' AND prop.lastedit_type !='DocumentDeleted' AND prop.visibility = " . $visibility;
    $type_document = 'media_create_audio_button';
    //$imgsrc = Display::return_icon('pixel.gif', get_lang('ListView'), array('class' => 'mediaactionplaceholdericon media_create_audio_button'));
} elseif ($path == '/video' && $count_if_folder_exists > 0) {
    $sql = "SELECT * FROM $tbl_documents doc,$propTable prop WHERE doc.id = prop.ref AND prop.tool = '" . TOOL_DOCUMENT . "' AND doc.filetype = 'file' AND doc.path LIKE '/video/%' AND prop.lastedit_type !='DocumentDeleted' AND prop.visibility = " . $visibility;
    $type_document = 'media_create_video_button';
    //$imgsrc = Display::return_icon('pixel.gif', get_lang('ListView'), array('class' => 'mediaactionplaceholdericon media_create_video_button'));
} elseif ($path == '/podcasts' && $count_if_folder_exists > 0) {
    $sql = "SELECT * FROM $tbl_documents doc,$propTable prop WHERE doc.id = prop.ref AND prop.tool = '" . TOOL_DOCUMENT . "' AND doc.filetype = 'file' AND doc.path LIKE '/podcasts/%' AND prop.lastedit_type !='DocumentDeleted' AND prop.visibility = " . $visibility;
    $type_document = 'media_create_podcast_button';
    //$imgsrc = Display::return_icon('pixel.gif', get_lang('ListView'), array('class' => 'mediaactionplaceholdericon media_create_podcast_button'));
} elseif ($path == '/screencasts' && $count_if_folder_exists) {
    $sql = "SELECT * FROM $tbl_documents doc,$propTable prop WHERE doc.id = prop.ref AND prop.tool = '" . TOOL_DOCUMENT . "' AND doc.filetype = 'file' AND doc.path LIKE '/screencasts/%' AND prop.lastedit_type !='DocumentDeleted' AND prop.visibility = " . $visibility;
    $type_document = 'media_create_screencast_button';
    //$imgsrc = Display::return_icon('pixel.gif', get_lang('ListView'), array('class' => 'mediaactionplaceholdericon media_create_screencast_button'));
} elseif ($path == '/animations' && $count_if_folder_exists > 0) {
    $sql = "SELECT * FROM $tbl_documents doc,$propTable prop WHERE doc.id = prop.ref AND prop.tool = '" . TOOL_DOCUMENT . "' AND doc.filetype = 'file' AND doc.path LIKE '/animations/%' AND prop.lastedit_type !='DocumentDeleted' AND prop.visibility = " . $visibility;
    $type_document = 'media_create_animation_button';
    //$imgsrc = Display::return_icon('pixel.gif', get_lang('ListView'), array('class' => 'mediaactionplaceholdericon media_create_animation_button'));
}


if ($session_id > 0) {
    $sql .= " AND prop.id_session = (IF(
                    (SELECT COUNT(*) FROM $propTable ip WHERE ip.ref = prop.ref AND ip.id_session = $session_id) > 0,
                    $session_id,
                    0
              ))";
}
$sql .= " ORDER BY title";

$result = api_sql_query($sql, __FILE__, __LINE__);

echo '<div id="blanket" style="display:none;"></div>';
echo '<table class="gallery">';
$i = 0;
$j = 1;
$mediabox_folders = array('audio', 'video', 'podcasts', 'screencasts', 'animations');
$real_folder_name = substr($path, 1);
// Display message if folder doesn't exists
if ($count_if_folder_exists == 0 && in_array($real_folder_name, $mediabox_folders)) {
    $message_exists = get_lang('DoesNotExistsTheFolder') . ' : <strong>' . substr($path, 1) . '</strong>';
    echo '<div class="confirmation-message rounded">' . $message_exists . '</div>';
}
$count = Database::num_rows($result);

if ($count == 0) {
    $message = get_lang('NoLinkAvailable');
    $output .= '<div style="clear: both; height: 10px; line-height: 3;"></div>';
    $output .= '<div id="emptyPage">' . $message . '</div>';
    echo $output;
}
while ($row = Database::fetch_array($result)) {
//	$visibility_icon = ($row['visibility']==0)?'closedeye_tr':'dokeoseyeopen22';
    $visibility_command = ($row['visibility'] == 0) ? 'set_visible' : 'set_invisible';


    $title = $row['title'];
    $audvid_path = $row['path'];
    $size = format_file_size($row['size']);
    $forcedownload_link = 'document.php?' . api_get_cidreq() . '&action=download&id=' . $audvid_path;

    if (!$i % 3) {
        echo '<tr>';
    }
    if ($path == '/audio' || $path == '/podcasts') { 
        if (pathinfo($row['title'], PATHINFO_EXTENSION) == 'mp3') {
            echo '<td>';
            echo '<div class="mediabig_button three_buttons rounded grey_border">';
            echo '  <div class="sectiontitle">';
            echo '      <a class="open_media_audio_in_window" href="javascript:void(0)" id="media_content_' . $i . '"  >' . cut($title, 25) . '</a>
                    </div>
                    <br/>';
            echo getAudioVideo($title, $i, $audvid_path, $path, $navigator_info);
            echo '  <a class="open_media_audio_in_window" href="javascript:void(0)" id="media_content_' . $i . '" >';
            echo '      <div>' . Display::return_icon('pixel.gif', $title, array('class' => 'mediaactionplaceholdericon ' . $type_document)) . '</div><br/>';
            echo '  </a>';

            if (api_is_allowed_to_edit()) {
                //echo '<a href="document.php?'.api_get_cidreq().'&curdirpath='.$pathurl.'&type=media&delete='.$row['path'].'" onclick="return confirmation(\''.basename($path).'\');">'.Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')).'</a>';
                $link = 'document.php?' . api_get_cidreq() . '&curdirpath=' . $pathurl . '&type=media&delete=' . $row['path'];
                $title = get_lang("ConfirmationDialog");
                $text = get_lang("ConfirmYourChoice");
                echo '<a href="javascript:void(0);" onclick="Alert_Confim_Delete(\'' . $link . '\',\'' . $title . '\',\'' . $text . '\');">' . Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')) . '</a>';
                if ($row['visibility'] == 0) {
                    echo '<a href="document.php?' . api_get_cidreq() . '&curdirpath=' . $pathurl . '&' . $visibility_command . '=' . $row['id'] . '&type=media">' . Display::return_icon('pixel.gif', get_lang('Visible'), array('class' => 'actionplaceholdericon actionvisible invisible')) . '</a>';
                } else {
                    echo '<a href="document.php?' . api_get_cidreq() . '&curdirpath=' . $pathurl . '&' . $visibility_command . '=' . $row['id'] . '&type=media">' . Display::return_icon('pixel.gif', get_lang('Invisible'), array('class' => 'actionplaceholdericon actionvisible')) . '</a>';
                }
            }
            echo '<a href="' . $forcedownload_link . '">' . Display::return_icon('pixel.gif', get_lang('Download'), array('class' => 'actionplaceholdericon forcedownload')) . '</a>';
            echo '</div>';
            echo '</td>';
        }
    } elseif ($path == '/video' || $path == '/screencasts') { 
        $ext = pathinfo($row['title'], PATHINFO_EXTENSION);
        if (in_array(strtolower($ext), array('flv', 'mpg', 'mpeg', 'mp4', 'avi', 'wmv', 'mov', 'asf', '3gp', 'ogg'))) {
            echo '<td>';
            echo '  <div class="mediabig_button three_buttons rounded grey_border">';
            echo '      <div class="sectiontitle"><a class="open_media_video_in_window" href="javascript:void(0)" id="media_content_' . $i . '" >' . api_trunc_str($title, 25) . '</a></div><br />';
            echo getAudioVideo($title, $i, $audvid_path, $path, $navigator_info);
            echo '      <a class="open_media_video_in_window" href="javascript:void(0)" id="media_content_' . $i . '"  >';
            echo '		<div>' . Display::return_icon('pixel.gif', $title, array('class' => 'mediaactionplaceholdericon ' . $type_document)) . '</div><br/>';
            echo '      </a>';
            echo '<div align="center">' . $size . '</div>';
            if (api_is_allowed_to_edit()) {
                //echo '<a href="document.php?'.api_get_cidreq().'&type=media&curdirpath='.$pathurl.'&delete='.$row['path'].'" onclick="return confirmation(\''.basename($path).'\');">'.Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')).'</a>';
                $link = 'document.php?' . api_get_cidreq() . '&type=media&curdirpath=' . $pathurl . '&delete=' . $row['path'];
                $title = get_lang("ConfirmationDialog");
                $text = get_lang("ConfirmYourChoice");
                echo '<a href="javascript:void(0)" onclick="Alert_Confim_Delete(\'' . $link . '\',\'' . $title . '\',\'' . $text . '\');">' . Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')) . '</a>';
                if ($row['visibility'] == 0) {
                    echo ' <a href="document.php?' . api_get_cidreq() . '&curdirpath=' . $pathurl . '&' . $visibility_command . '=' . $row['id'] . '&type=media">' . Display::return_icon('pixel.gif', get_lang('Visible'), array('class' => 'actionplaceholdericon actionvisible invisible')) . '</a>';
                } else {
                    echo ' <a href="document.php?' . api_get_cidreq() . '&curdirpath=' . $pathurl . '&' . $visibility_command . '=' . $row['id'] . '&type=media">' . Display::return_icon('pixel.gif', get_lang('Invisible'), array('class' => 'actionplaceholdericon actionvisible')) . '</a>';
                }
            }
            echo '	<a href="' . $forcedownload_link . '">' . Display::return_icon('pixel.gif', get_lang('Download'), array('class' => 'actionplaceholdericon forcedownload')) . '</a>';
            echo '</div>';
            echo '</td>';
        }
    } elseif ($path == '/animations') {
        $course_name = explode("=", api_get_cidReq());
        $medialink = api_get_path(WEB_COURSE_PATH) . $course_name[1] . '/document/animations/' . $title;       
        echo '<td>';
        //$navigator_info = api_get_navigator();
        echo '	<div class="mediabig_button three_buttons rounded grey_border">';
        
        echo '<div id="id1" style="display:none;" ></div>';
        
        if ($navigator_info['name'] == 'Internet Explorer' && ($navigator_info['version'] == '6' || $navigator_info['version'] == '7' || $navigator_info['version'] == '8')) {
            echo "<a href=\"javascript:void(0)\" onclick=\"Media=window.open('" . $medialink . "', 'mediawindow', 'width=720px,height=500px')\">";
        } else {
           
//            echo "<a href=\"#\" onclick=\"Media=dhtmlmodal.open('Media', 'iframe', '" . $medialink . "', 'Media', 'width=720px,height=540px,center=1,resize=1,scrolling=1')\">";
            echo "<a href=\"#\" onclick='showAnimation(\"$medialink\")'>";
        }
        echo '<div class="sectiontitle">' . api_trunc_str($title, 25) . '</div>';
        echo '<div>' . Display::return_icon('pixel.gif', $title, array('class' => 'mediaactionplaceholdericon ' . $type_document)) . '</div><br/></a>';
        echo '<div>';
        //echo $size;
        echo '<div align="center">' . $size . '</div>';
        if (api_is_allowed_to_edit()) {
            //echo '	<a href="document.php?'.api_get_cidreq().'&curdirpath='.$pathurl.'&type=media&delete='.$row['path'].'" onclick="return confirmation(\''.basename($path).'\');">'.Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')).'&nbsp;&nbsp';
            $link = 'document.php?' . api_get_cidreq() . '&curdirpath=' . $pathurl . '&type=media&delete=' . $row['path'];
            $title = get_lang("ConfirmationDialog");
            $text = get_lang("ConfirmYourChoice");
            echo '	<a href="javascript:void(0)" onclick="Alert_Confim_Delete(\'' . $link . '\',\'' . $title . '\',\'' . $text . '\');">' . Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')) . '&nbsp;&nbsp';
            if ($row['visibility'] == 0) {
                echo '<a href="document.php?' . api_get_cidreq() . '&curdirpath=' . $pathurl . '&' . $visibility_command . '=' . $row['id'] . '&type=media">' . Display::return_icon('pixel.gif', get_lang('Visible'), array('class' => 'actionplaceholdericon actionvisible invisible')) . '</a>&nbsp;&nbsp;';
            } else {
                echo '<a href="document.php?' . api_get_cidreq() . '&curdirpath=' . $pathurl . '&' . $visibility_command . '=' . $row['id'] . '&type=media">' . Display::return_icon('pixel.gif', get_lang('Invisible'), array('class' => 'actionplaceholdericon actionvisible')) . '</a>&nbsp;&nbsp;';
            }
        }
        echo '<a href="' . $forcedownload_link . '">' . Display::return_icon('pixel.gif', get_lang('Download'), array('class' => 'actionplaceholdericon forcedownload')) . '</a>';
        echo '</div>';
        echo '	</div>';
        echo '</td>';
    }
    if ($j == 3) {
        echo '</tr>';
        $j = 0;
    }
    $i++;
    $j++;
}
echo '</table>';

function getAudioVideo($title, $i, $audvid_path, $path, $navigator = array()) {
    global $_course;

    $title = api_trunc_str($title, 40);
    // Get last characters before the "." for get the correct extension file
    $ext = substr(strrchr($audvid_path, '.'), 1);
    $ext = strtolower($ext);
    $src_path = api_get_path(WEB_COURSE_PATH) . $_course['path'] . '/document' . $audvid_path;

    // Start
    ($ext == 'mp3' || $ext == 'ogg' || $ext == 'ogv') ? $hPopUp = 'height:28px;padding-bottom:0px' : '';
    $return .= '<div id="popUpDiv' . $i . '" title="' . $title . '" style="display:none;' . $hPopUp . '"><div>';

    if (($navigator['device']['devicetype'] != 'mobile' && $ext == 'flv') || $ext == 'mp4' || $ext == 'mp3' || $ext == 'ogg' || $ext == 'ogv') {
        $return .= "<div id='mediaplayer" . $i . "'>Playing educational video on the Dokeos platform</div>";
        // Set the dimensions for default
        if ($ext == 'mp3' || $ext == 'ogg' || $ext == 'ogv') {
            $return .= "<script type=\"text/javascript\">
            jwplayer(\"mediaplayer$i\" ).setup({     
                file: '" . $src_path . "',
                width: '400',
                height:'35',
                autostart:false,
                controlbar: 'top'
            });
            </script>";
        } else {
            $return .= "<script type=\"text/javascript\">
            jwplayer(\"mediaplayer$i\" ).setup({
                file: '" . $src_path . "',
                width: '400',
                height:'300',
                autostart:false
            });
            </script>";
        }
    } elseif ($ext == 'mpg' || $ext == 'wmv' || $ext == 'wma' || $ext == 'avi') {
        $return .= '<OBJECT ID="MediaPlayer" WIDTH="450" HEIGHT="350" CLASSID="CLSID:22D6F312-B0F6-11D0-94AB-0080C74C7E95"
                            STANDBY="Loading Windows Media Player components..." TYPE="application/x-oleobject">
                            <PARAM NAME="FileName" VALUE="' . $title . '">
                            <PARAM name="autostart" VALUE="true">
                            <PARAM name="ShowControls" VALUE="true">
                            <param name="ShowStatusBar" value="true">
                            <PARAM name="ShowDisplay" VALUE="true">
                            <EMBED TYPE="application/x-mplayer2" SRC="' . $src_path . '" NAME="MediaPlayer"
                            WIDTH="400" HEIGHT="300" ShowControls="1" ShowStatusBar="1" ShowDisplay="1" autostart="1"> </EMBED>
                            </OBJECT>';
    } elseif ($ext == 'swf') {
        $return .= '<div>' . $title . '</div>';
    } else {
        $return .= '<div>' . Display::return_icon('no-video.jpg', '', array('width' => '400px', 'height' => '300px')) . '</div>';
    }

    $return .= '
            </div>
        </div>';

    return $return;
}

// close the content div
echo '</div>';


echo '<div class="actions">';
DocumentManager::show_simplifying_links(true, true);
echo '</div>';
// display the footer
Display :: display_footer();
