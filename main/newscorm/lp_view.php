<?php
/* For licensing terms, see /dokeos_license.txt */

/**
 * Learning Path
 * This file was origially the copy of document.php, but many modifications happened since then ;
 * the direct file view is not needed anymore, if the user uploads a scorm zip file, a directory
 * will be automatically created for it, and the files will be uncompressed there for example ;
 * @package dokeos.learnpath
 * @author Denes Nagy, principal author
 * @author Isthvan Mandak, several new features
 * @author Roan Embrechts, code improvements and refactoring
 * @author Yannick Warnier - redesign
 */

$_SESSION['whereami'] = 'lp/view';
$this_section = SECTION_COURSES;
if ($lp_controller_touched != 1) {
    header('location: lp_controller.php?action=view&amp;item_id='.Security::remove_XSS($_REQUEST['item_id']));
}
/*
-----------------------------------------------------------
	Libraries
-----------------------------------------------------------
*/
require_once('back_compat.inc.php');
//require_once('../learnpath/learnpath_functions.inc.php');
require_once('scorm.lib.php');
require_once('learnpath.class.php');
require_once('learnpathItem.class.php');
require_once('course_navigation_interface.inc.php');
//require_once('lp_comm.common.php'); //xajax functions

$course_code = api_get_course_id();
if (api_get_course_visibility($course_code) <> 3) {
    // check permissions
    if (api_is_anonymous(null, true)) {
        header('Location: ' . api_get_path(WEB_PATH));
        exit;
    }
}

if (!$is_allowed_in_course) api_not_allowed();
/*
-----------------------------------------------------------
	Variables
-----------------------------------------------------------
*/
//$charset = 'UTF-8';
//$charset = 'ISO-8859-1';
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

$oLearnpath = false;
$course_code = api_get_course_id();
$user_id = api_get_user_id();
$platform_theme = api_get_setting('stylesheets');// plataform's css
$my_style = $platform_theme;

// Start LP from begin to end
$smt_value_options = array("last", "first");
$start_module_tool_value = $smt_value_options[0];
$description_value = unserialize($_SESSION['oLP']->description);
if ($description_value && in_array($description_value['start_module_tool_type'], $smt_value_options)) {
    $start_module_tool_value = $description_value['start_module_tool_type'];
}
//if ((api_is_anonymous($user_id, true) || api_is_allowed_to_edit()) && !isset($_GET['switch']) ) {
if ($start_module_tool_value === "first" && !isset($_GET['switch'])) {
    $lp_item_id = $_SESSION['oLP']->get_first_item_id();
    $_SESSION['oLP']->current = $lp_item_id;
}
$_SESSION['oLP']->error = '';
$lp_type = $_SESSION['oLP']->get_type();



$lp_item_id = isset($_GET['item_id']) ? intval($_GET['item_id']) : $_SESSION['oLP']->get_current_item_id();

//Prepare variables for the test tool (just in case) - honestly, this should disappear later on
$_SESSION['scorm_view_id'] = $_SESSION['oLP']->get_view_id();
$_SESSION['scorm_item_id'] = $lp_item_id;
$_SESSION['lp_mode'] = $_SESSION['oLP']->mode;

//escape external variables
/*
-----------------------------------------------------------
	Header
-----------------------------------------------------------
*/
$htmlHeadXtra[] = '<script src="js/course_view_func.js" type="text/javascript" language="javascript"></script>';
$htmlHeadXtra[] = '<script src="js/jquery.iframe-auto-height.js" type="text/javascript" language="javascript"></script>';
$htmlHeadXtra[] = '<script src="' . api_get_path(WEB_LIBRARY_PATH) . 'jwplayer/jwplayer.js" type="text/javascript" language="javascript"></script>';
$htmlHeadXtra[] = '<script language="javascript" type="text/javascript">
    $(document).ready(function (){
        $("div#log_content_cleaner").bind("click", function(){
            $("div#log_content").empty();
        });
    });
</script>';

if ($_SESSION['oLP']->lp_interface != 4 && $_SESSION['oLP']->lp_interface != 2 && $_SESSION['oLP']->type == 1) {
    $htmlHeadXtra[] = '<script language="javascript" type="text/javascript">
    $(document).ready(function (){
        jQuery("iframe").iframeAutoHeight({minHeight:700});
    });
</script>';
} else {
    $htmlHeadXtra[] = '<script language="javascript" type="text/javascript">
  $(document).ready(function (){
     $("iframe#content_id").attr("height", "750px");
  });
</script>';
}
$htmlHeadXtra[] = '<script language="JavaScript" type="text/javascript">
  	var dokeos_xajax_handler = window.oxajax;
</script>';

if (api_get_setting('allow_course_theme') == 'true' && api_get_course_setting('course_theme') != '') {
    $stylesheet = api_get_course_setting('course_theme');
} else {
    $stylesheet = api_get_setting('stylesheets');
}

$htmlHeadXtra[]= '<link rel="stylesheet" type="text/css" href="../css/'.$stylesheet.'/course_navigation.css" />';
$htmlHeadXtra[]= '<script type="text/javascript">
$(document).ready(function() {
     $("#content_id").load(function(){
//        $("iframe#content_id").contents().find("div#generic_tool_header").hide();
//        $("iframe#content_id").contents().find("div#courseHeader").hide();
        $("iframe#content_id").contents().find("body").css("background", "none");
     });
 });
</script>';
$htmlHeadXtra[]= '<script type="text/javascript">
$(document).ready(function() {
var map = {
        "confirmation-message": ".confirmation-message",
        "warning-message": ".warning-message",
        "error-message": ".error-message",
        "normal-message": ".normal-message"
};
$("#content_id").load(function(){
    $.each(map, function(key, value){

        $("iframe#content_id").contents().find(value+" .close_message_box").click(function(){
            $("iframe#content_id").contents().find("div.normal-message").hide();
        });
    });
});
});
</script>';

$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-ui/js/jquery-ui-1.8.1.custom.min.js"></script>';
$htmlHeadXtra[] = '<link type="text/css" href="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-ui/css/ui-lightness/jquery-ui-1.8.1.custom.css" rel="stylesheet" />';

if (api_get_setting('audio_recorder') == 'true' && api_is_allowed_to_edit()) {
    if (is_dir(api_get_path(SYS_CODE_PATH) . 'inc/lib/audiorecorder')) {
        require_once api_get_path(LIBRARY_PATH) . 'audiorecorder/src/AudiorecorderFactory.php';
        $provider = 1; // @todo the value should be a setting
        $objAudiorecorder = AudiorecorderFactory::getAudiorecorderObject($provider, 'module', $lp_item_id);
        $htmlHeadXtra[] = $objAudiorecorder->returnCss();
        $htmlHeadXtra[] = $objAudiorecorder->returnJs();
    }
}

//reinit exercises variables to avoid spacename clashes (see exercise tool)
if(isset($exerciseResult) or isset($_SESSION['exerciseResult'])) {
    api_session_unregister($exerciseResult);
}
unset($_SESSION['objExercise']);
unset($_SESSION['questionList']);
/**
 * Get a link to the corresponding document
 */
if ($_SESSION['viewasstudent'] == "YES") {
    $_SESSION['studentview'] = "studentview";
    $GLOBALS['learner_view'] = true;
} else {
    $_SESSION['studentview'] = "teacherview";
    $GLOBALS['learner_view'] = false;
}
if (!isset($src)) {//SI*****************************
    $src = '';
    switch ($lp_type) {
        case 1:
            $_SESSION['oLP']->stop_previous_item();            
            $htmlHeadXtra[] = '<script src="scorm_api.php" type="text/javascript" language="javascript"></script>';
            $prereq_check = $_SESSION['oLP']->prerequisites_match($lp_item_id);
            if ($prereq_check === true) {
                //var_dump($lp_item_id);exit;
                $src = $_SESSION['oLP']->get_link('http',$lp_item_id);
                $_SESSION['oLP']->start_current_item(); //starts time counter manually if asset
            } else {
                $src = 'blank.php?error=prerequisites';
            }
            break;
        case 2:
            //save old if asset
            $_SESSION['oLP']->stop_previous_item(); //save status manually if asset
            $htmlHeadXtra[] = '<script src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.highlight.js" type="text/javascript" language="javascript"></script>';
            $htmlHeadXtra[] = '<script src="scorm_api.php" type="text/javascript" language="javascript"></script>';
            $prereq_check = $_SESSION['oLP']->prerequisites_match($lp_item_id);
            if ($prereq_check === true) {
                $src = $_SESSION['oLP']->get_link('http',$lp_item_id);
                $_SESSION['oLP']->start_current_item(); //starts time counter manually if asset
            } else {
                $src = 'blank.php?error=prerequisites';
            }
            break;
        case 3:
            //aicc
            $_SESSION['oLP']->stop_previous_item(); //save status manually if asset
            $htmlHeadXtra[] = '<script src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.highlight.js" type="text/javascript" language="javascript"></script>';
            $htmlHeadXtra[] = '<script src="scorm_api.php" type="text/javascript" language="javascript"></script>';
            $htmlHeadXtra[] = '<script src="' . $_SESSION['oLP']->get_js_lib() . '" type="text/javascript" language="javascript"></script>';
            $prereq_check = $_SESSION['oLP']->prerequisites_match($lp_item_id);
            if ($prereq_check === true) {
                $src = $_SESSION['oLP']->get_link('http', $lp_item_id);
                $_SESSION['oLP']->start_current_item(); //starts time counter manually if asset
            } else {
                $src = 'blank.php';
            }
            break;
        case 4:
            break;
    }
}
$list = $_SESSION['oLP']->get_toc();
$type_quiz = false;
foreach ($list as $toc) {
    if ($toc['id'] == $lp_item_id && ($toc['type']=='quiz') ) {
        $type_quiz = true;
    }
}
$current_item_type = isset($_SESSION['oLP']->current) ? $_SESSION['oLP']->items[$_SESSION['oLP']->current]->get_type() : '';
$autostart = 'true';
// update status,total_time from lp_item_view table when you finish the exercises in learning path
if ($type_quiz && !empty($_REQUEST['exeId']) && isset($_GET['lp_id']) && isset($_GET['lp_item_id'])) {//NO***************
    global $src;
    $_SESSION['oLP']->items[$_SESSION['oLP']->current]->write_to_db();
    $TBL_TRACK_EXERCICES = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
    $TBL_LP_ITEM_VIEW    = Database::get_course_table(TABLE_LP_ITEM_VIEW);
    $TBL_LP_VIEW         = Database::get_course_table(TABLE_LP_VIEW);
    $TBL_LP_ITEM         = Database::get_course_table(TABLE_LP_ITEM);
    $safe_item_id        = Database::escape_string($_GET['lp_item_id']);
    $safe_id             = Database::escape_string($_GET['lp_id']);
    $safe_exe_id         = Database::escape_string($_REQUEST['exeId']);

    if ($safe_id == strval(intval($safe_id)) && $safe_item_id == strval(intval($safe_item_id))) {

        $sql = 'SELECT start_date,exe_date,exe_result,exe_weighting FROM ' . $TBL_TRACK_EXERCICES . ' WHERE exe_id = '.(int)$safe_exe_id;
        $res = Database::query($sql,__FILE__,__LINE__);
        $row_dates = Database::fetch_array($res);

        $time_start_date = convert_mysql_date($row_dates['start_date']);
        $time_exe_date 	 = convert_mysql_date($row_dates['exe_date']);
        $mytime = ((int)$time_exe_date-(int)$time_start_date);
        $score = (float)$row_dates['exe_result'];
        $max_score = (float)$row_dates['exe_weighting'];

        $sql_upd_status = "UPDATE $TBL_LP_ITEM_VIEW SET status = 'completed' WHERE lp_item_id = '".(int)$safe_item_id."'
                         AND lp_view_id = (SELECT lp_view.id FROM $TBL_LP_VIEW lp_view WHERE user_id = '".(int)$_SESSION['oLP']->user_id."' AND lp_id='".(int)$safe_id."' AND session_id = ".api_get_session_id().")";
        Database::query($sql_upd_status,__FILE__,__LINE__);

        $sql_upd_max_score = "UPDATE $TBL_LP_ITEM SET max_score = '$max_score' WHERE id = '".(int)$safe_item_id."'";
        Database::query($sql_upd_max_score,__FILE__,__LINE__);

        $sql_last_attempt = "SELECT id FROM $TBL_LP_ITEM_VIEW  WHERE lp_item_id = '$safe_item_id' AND lp_view_id = '".$_SESSION['oLP']->lp_view_id."' order by id desc limit 1";
        $res_last_attempt = Database::query($sql_last_attempt,__FILE__,__LINE__);
        $row_last_attempt = Database::fetch_row($res_last_attempt);

        if (Database::num_rows($res_last_attempt)>0) {
            $sql_upd_score = "UPDATE $TBL_LP_ITEM_VIEW SET score = $score,total_time = $mytime WHERE id='".$row_last_attempt[0]."'";
            Database::query($sql_upd_score,__FILE__,__LINE__);
        }
    }

    if(intval($_GET['fb_type']) == 2) {
        $src = 'blank.php?msg=exerciseFinished';
    } else {
        $src = api_get_path(WEB_CODE_PATH).'exercice/exercise_show.php?id='.Security::remove_XSS($_REQUEST['exeId']).'&origin=learnpath&learnpath_id='.Security::remove_XSS($_GET['lp_id']).'&learnpath_item_id='.Security::remove_XSS($_GET['lp_id']).'&fb_type='.Security::remove_XSS($_GET['fb_type']);
    }
    $autostart = 'false';
}

$_SESSION['oLP']->set_previous_item($lp_item_id);
$nameTools = Security :: remove_XSS(api_convert_encoding($_SESSION['oLP']->get_name(), $charset, api_get_system_encoding()));

$save_setting = api_get_setting("show_navigation_menu");
global $_setting;
$_setting['show_navigation_menu'] = 'false';
$scorm_css_header=true;
$lp_theme_css=$_SESSION['oLP']->get_theme(); //sets the css theme of the LP this call is also use at the frames (toc, nav, message)

$sys_src_info = explode('?',$src);
$sys_src = $sys_src_info[0];
$file_url_sys = str_replace(api_get_path(WEB_PATH),api_get_path(SYS_PATH),$sys_src);
if (file_exists($file_url_sys)) {
    $path_info = pathinfo($file_url_sys);
    // Check only HTML documents
    if ($path_info['extension'] == 'html') {
        $get_file_content = file_get_contents($file_url_sys);
        $matches = preg_match('/<embed/i', $get_file_content,$matches);
        // Only for files that has embed tags
        $write_doc_swf = false;
        if (count($matches) > 0) {
            $write_doc_swf = true;
            $get_file_content = str_replace(array('wmode="opaque"', 'wmode="transparent"'), "", $get_file_content);
            $get_file_content = str_replace(array('<embed'), array('<embed wmode="opaque" '), $get_file_content);
        }
        if ($write_doc_swf) {
           file_put_contents($file_url_sys, $get_file_content);
        }
    }
}
switch ($_SESSION['oLP']->lp_interface) {
    case 0:
        if ($_SESSION['oLP']->mode == 'embedded') {
            include 'lp_view_navigation.inc.php';
        }
        break;
    case 1:
        if ($_SESSION['oLP']->mode == 'embedded') {
            include 'lp_view_table_contents.inc.php';
        }
        break;
    case 2:
        if ($_SESSION['oLP']->mode == 'embedded') {
            include 'lp_view_fullscreen.inc.php';
        }
        break;
    case 3:
        include 'lp_view_open_window.inc.php';
        break;
    case 4:
        if ($_SESSION['oLP']->mode == 'embedded') {
            include 'lp_view_fixed_height.inc.php';
        }
        break;
}

if ($GLOBALS['learner_view']) {
    $_SESSION['studentview'] = "teacherview";
    $_SESSION['viewasstudent'] = "YES";
} else {
    $_SESSION['studentview'] = "teacherview";
    $_SESSION['viewasstudent'] = "NO";
}
//restore global setting
$_setting['show_navigation_menu'] = $save_setting;