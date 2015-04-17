<?php
// $Id: showinframes.php 22177 2009-07-16 22:30:39Z iflorespaz $

/* For licensing terms, see /dokeos_license.txt */

/**
  ==============================================================================
 * 	This file will show documents in a separate frame.
 * 	We don't like frames, but it was the best of two bad things.
 *
 * 	display html files within Dokeos - html files have the Dokeos header.
 *
 * 	--- advantages ---
 * 	users "feel" like they are in Dokeos,
 * 	and they can use the navigation context provided by the header.
 *
 * 	--- design ---
 * 	a file gets a parameter (an html file)
 * 	and shows
 * 	- dokeos header
 * 	- html file from parameter
 * 	- (removed) dokeos footer
 *
 * 	@version 0.6
 * 	@author Roan Embrechts (roan.embrechts@vub.ac.be)
 * 	@package dokeos.document
  ==============================================================================
 */
// name of the language file that needs to be included
$language_file[] = 'document';

// include the global Dokeos file
require_once '../inc/global.inc.php';

// include additional libraries
require_once '../glossary/glossary.class.php';
require_once api_get_path(LIBRARY_PATH).'document.lib.php';

api_protect_course_script(true);

if (!empty($_GET['nopages'])) {
    $nopages = Security::remove_XSS($_GET['nopages']);
    if ($nopages == 1) {
        require_once api_get_path(INCLUDE_PATH) . 'reduced_header.inc.php';
        echo '<div id="content"><br/><br/><div class="confirmation-message">' . get_lang('FileNotFound') . '</div></div>';
    }
    exit;
}

$_SESSION['whereami'] = 'document/view';
$_SESSION['dbName'] = $_course['dbName'];
//$from = $_REQUEST["from"];

// breadcrumbs
$interbreadcrumb[] = array('url' => './document.php', 'name' => get_lang('Documents'));
$htmlHeadXtra[] = '<script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery.iframe-auto-height.js" type="text/javascript" language="javascript"></script>';
//if(!isset($from) && $from != 'googledoc'){
$htmlHeadXtra[] = '<script language="javascript" type="text/javascript">
$(document).ready(function (){
  jQuery("iframe").iframeAutoHeight({minHeight:600});
});
</script>';
//}
$nameTools = get_lang('Documents');
$param_group = (isset($_GET['gidReq']) && !empty($_GET['gidReq'])) ? '&gidReq=' . $_GET['gidReq'] : '';
$file = Security::remove_XSS($_GET['file']);
/*
  ==============================================================================
  Main section
  ==============================================================================
 */
header('Expires: Wed, 01 Jan 1990 00:00:00 GMT');
//header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Last-Modified: Wed, 01 Jan 2100 00:00:00 GMT');

header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

/* $browser_display_title = "Dokeos Documents - " . Security::remove_XSS($_GET['cidReq']) . " - " . $file;

  //only admins get to see the "no frames" link in pageheader.php, so students get a header that's not so high
  $frameheight = 135;
  if($is_courseAdmin) {
  $frameheight = 165;
  } */

$file_root = $_course['path'] . '/document' . str_replace('%2F', '/', $file);
$file_url_sys = api_get_path(SYS_COURSE_PATH) . $file_root;
$file_url_web = api_get_path(WEB_COURSE_PATH) . $file_root;
//var_dump(api_get_path(WEB_COURSE_PATH));
$path_info = pathinfo($file_url_sys);

$is_allowed_to_edit = api_is_allowed_to_edit();
$curdirpathurl = Security::remove_XSS($_REQUEST['curdirpath']);

Display :: display_tool_header($nameTools, "Doc");
Display::display_introduction_section(TOOL_DOCUMENT);
$is_allowed_to_edit = api_is_allowed_to_edit();
$req_gid = $param_group;
echo '<div class="actions" style="min-height: 40px;">';
DocumentManager::show_li_eeight($_GET['document'],$_GET['gidReq'],$_GET['curdirpath'],$curdirpath,$group_properties['directory'],$image_present,'showinframes',$file);
//echo '<a href="document.php?' . api_get_cidreq() . '&curdirpath=' . $curdirpathurl . $req_gid . '">' . Display::return_icon('pixel.gif', get_lang('Documents'), array('class' => 'toolactionplaceholdericon toolactiondocument')) . get_lang('Documents') . '</a>';
if ($is_allowed_to_edit) {
//    echo '<a href="template_gallery.php?doc=N&dir=' . $curdirpathurl . $req_gid . '&' . api_get_cidreq() . '&selectcat=' . Security::remove_XSS($_GET['selectcat']) . '">' . Display::return_icon('pixel.gif', get_lang('Templates'), array('class' => 'toolactionplaceholdericon toolactiontemplates')) . ' ' . get_lang('Templates') . '</a>';
//    echo '<a href="upload.php?' . api_get_cidreq() . '&path=' . $curdirpathurl . $req_gid . '&selectcat=' . Security::remove_XSS($_GET['selectcat']) . '">' . Display::return_icon('pixel.gif', get_lang('UplUpload'), array('class' => 'toolactionplaceholdericon toolactionupload')) . ' ' . get_lang('UplUpload') . '</a>';
//    echo '<a href="edit_document.php?' . api_get_cidreq() . '&curdirpath=' . urlencode($curdirpathurl) . $req_gid . '&file=' . urlencode($file) . '">' . Display::return_icon('pixel.gif', get_lang('EditDocument'), array('class' => 'toolactionplaceholdericon tooledithome')) . get_lang('EditDocument') . '</a>';
}

$courseInfo = api_get_course_info(api_get_course_id());

echo '</div>';

?>
<?php

if(isset($_REQUEST['tool']) && $_REQUEST['tool'] = 'scenario') {
	
	$TBL_SCENARIO_ACTIVITY_VIEW = Database :: get_course_table(TABLE_SCENARIO_ACTIVITY_VIEW);
	$step_id = $_REQUEST['step'];
	$activity_id = $_REQUEST['activity_id'];

	$sql_check = "SELECT * FROM $TBL_SCENARIO_ACTIVITY_VIEW WHERE step_id = ".$step_id." AND activity_id = ".$activity_id." AND user_id = ".api_get_user_id();
	$res_check = Database::query($sql_check, __FILE__, __LINE__);
	$num_rows = Database::num_rows($res_check);
	if($num_rows == 0) {
		$sql = "INSERT INTO $TBL_SCENARIO_ACTIVITY_VIEW (activity_id, step_id, user_id, view_count, score, status) VALUES($activity_id, $step_id, ".api_get_user_id().", 1, '0', 'completed')";
	}
	else {
		$sql = "UPDATE $TBL_SCENARIO_ACTIVITY_VIEW SET view_count = view_count + 1 WHERE activity_id = ".$activity_id." AND step_id = ".$step_id." AND user_id = ".api_get_user_id();
	}

	Database::query($sql,__FILE__,__LINE__);
}

if (file_exists($file_url_sys)) {
    $url = $file_url_web . '?' . api_get_cidreq() . '&rand=' . mt_rand(1, 10000);
    $path_info = pathinfo($file_url_sys);
    // Check only HTML documents
    if ($path_info['extension'] == 'html') {
        $get_file_content = file_get_contents($file_url_sys);
       
        $matches = preg_match('/<embed/i', $get_file_content, $matches);
        // Only for files that has embed tags
        if (count($matches) > 0) {
            $get_file_content = str_replace(array('wmode="opaque"', 'wmode="transparent"'), "", $get_file_content);
            $get_file_content = str_replace(array('<embed'), array('<embed wmode="opaque" '), $get_file_content);
            file_put_contents($file_url_sys, $get_file_content);
        }
    }
	/*else if($path_info['extension'] == 'docx' || $path_info['extension'] == 'doc'){
		$sub_url = $file_url_web.'&'.api_get_cidreq();
		$url = "http://docs.google.com/gview?url=".$sub_url."&embedded=true";
	}*/
} else {
    $url = 'showinframes.php?nopages=1';
}

?>
<div id="content_with_secondary_actions">
    <?php
    DocumentManager::show_back_directory($curdirpath, $group_properties['directory'],TRUE,$_GET['curdirpath']);
    ?>
    <iframe id="content_id" name="content_id" src ="<?php echo $url; ?>" width="100%" height="800" frameborder="0">
    <p>Your browser does not support iframes.</p>
    </iframe>
    <?php
    if(isset($_REQUEST['tool']) && $_REQUEST['tool'] = 'scenario') {
    echo '<div id="continueContainer" name="continueContainer" style="text-align:right;margin-top:5px;">
        <a onclick="goto(\''.api_get_path(WEB_COURSE_PATH).$courseInfo['path'].'/index.php'.'\')">
            <button id="continue" name="continue" class="continue" style=" font-size: 18px;">Continue</button>
            </a>
            </div>';
    echo "<script>function goto (href) { window.parent.location.href = href }</script>";
    }
    ?>
</div>
<?php
// bottom actions
//echo '<script>
//		
//    function positioning_btnContinue()	{
//
//            $("#continue").hide();
//
//            offset = $("#content_with_secondary_actions").offset();
//            width = $("#content_with_secondary_actions").width();
//            height = $("#content_with_secondary_actions").height();
//            console.log((offset.left + width) + " " + (offset.top + height) );
//            $("#continue").css("width","140px");
//            $("#continue").css("left",(offset.left + width)-130);
//            $("#continue").css("top",(offset.top + height)-35);
//
//            $("#continue").show();
//    }
//
//    $( window ).resize(function() {
//            positioning_btnContinue();
//    });
//
//
//    setTimeout(function(){
//
//            positioning_btnContinue();
//
//                            },1000);
//
//    $( document ).ready(function() {
//    //	$(".data_table").append($("<tr></tr>"));
//
//    //	positioning_btnContinue();
//
//    });
//
//</script>';
echo '	<div class="actions">';
if ($is_allowed_to_edit) {
    echo '<a href="' . api_get_self() . '?' . api_get_cidreq() . '&curdirpath=' . $curdirpathurl . $req_gid . '&createdir=1">' . Display::return_icon('pixel.gif', get_lang('CreateDir'), array('class' => 'actionplaceholdericon actioncreatefolder')) . ' ' . get_lang('CreateDir') . '</a>';
}
echo '<a href="' . api_get_self() . '?' . api_get_cidreq() . '&action=downloadfolder">' . Display::return_icon('pixel.gif', get_lang('SaveZip'), array('class' => 'actionplaceholdericon actionsavezip')) . ' ' . get_lang('SaveZip') . '</a>';
if ($is_allowed_to_edit) {
    echo '<a href="quota.php?' . api_get_cidreq() . '">' . Display::return_icon('pixel.gif', get_lang('DiskQuota'), array('class' => 'actionplaceholdericon actionquota')) . '  ' . get_lang("DiskQuota") . '</a>';
}
DocumentManager::show_simplifying_links(true, true); 
echo '</div>';
Display :: display_footer();
?>
