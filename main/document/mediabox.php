<?php

/* For licensing terms, see /dokeos_license.txt */

/**
  ==============================================================================
 * 	@package dokeos.document
  ==============================================================================
 */
// Language files that should be included
$language_file = array('document');

// setting the help
$help_content = 'mediabox';

// include the global Dokeos file
include ('../inc/global.inc.php');
require_once api_get_path(LIBRARY_PATH) . 'document.lib.php';
// section (for the tabs)
$this_section = SECTION_COURSES;
// session id
$session_id = api_get_session_id();

// variable initialisation
$_SESSION['whereami'] = 'document/create';
$path = Security::remove_XSS($_GET['curdirpath']);
$pathurl = urlencode($path);
$imagepath = '/images';
$photopath = '/photos';
$mindmappath = '/mindmaps';
$mascotpath = '/mascot';
$audiopath = '/audio';
$videopath = '/video';
$podcastpath = '/podcasts';
$screenpath = '/screencasts';
$animationpath = '/animations';

// setting the breadcrumbs
$interbreadcrumb[] = array("url" => Security::remove_XSS("document.php?curdirpath=" . $pathurl), "name" => get_lang('Documents'));

$htmlHeadXtra[] =
        "<script type=\"text/javascript\">
function confirmation (name) {
	if (confirm(\" " . get_lang("AreYouSureToDelete") . " \"+ name + \" ?\"))
		{return true;}
	else
		{return false;}
}
</script>";

// display the header
Display :: display_tool_header(get_lang('Mediabox'));
Display::display_introduction_section(TOOL_DOCUMENT);
// actions
echo '<div class="actions" style="min-height: 40px;">' . PHP_EOL;
echo '<ul class="new_li_actions" style="min-height: 40px;">';
echo '<li><a href="' . api_get_path(WEB_CODE_PATH) . 'document/document.php?' . api_get_cidReq() . '">' . Display::return_icon('pixel.gif', get_lang('Documents'), array('class' => 'toolactionplaceholdericon toolactiondocument')) . ' ' . get_lang('Documents') . '</a></li>';
//DocumentManager::show_simplifying_links(!api_is_allowed_to_edit(), false);
echo '</ul>';
echo '</div>';

// start the content div
echo '<div id="content">' . PHP_EOL;
if ((isset($_GET['set_invisible']) && !empty($_GET['set_invisible'])) || (isset($_GET['set_visible']) && (!empty($_GET['set_visible'])))) {
    /* If the action is make invisible a category mediabox then */
    if ($_GET['set_invisible']) {
        $update_id = Security::remove_XSS($_GET['set_invisible']);
        $visibility_command = 'invisible';
    } else {
        /* Else the action is make visible a category mediabox */
        $update_id = Security::remove_XSS($_GET['set_visible']);
        $visibility_command = 'visible';
    }
    /* Update the property tool with the action made */
    api_item_property_update($_course, TOOL_DOCUMENT, $update_id, $visibility_command, api_get_user_id(), null, null, null, null, $session_id);
}

$commonCssClasses = "big_button  rounded grey_border";

$mediaboxs = array('images' => array('path' => '/images', 'class' => 'image', 'lang' => 'Images'),
    'photos' => array('path' => '/photos', 'class' => 'photos', 'lang' => 'Photos'),
    'mascot' => array('path' => '/mascot', 'class' => 'mascot', 'lang' => 'Mascot'),
    'audio' => array('path' => '/audio', 'class' => 'audio', 'lang' => 'Audio'),
    'video' => array('path' => '/video', 'class' => 'video', 'lang' => 'Video'),
    'podcasts' => array('path' => '/podcasts', 'class' => 'podcast', 'lang' => 'Podcasts'),
    'screencasts' => array('path' => '/screencasts', 'class' => 'screencast', 'lang' => 'Screencasts'),
    'animations' => array('path' => '/animations', 'class' => 'animation', 'lang' => 'Animations'),
    'mindmaps' => array('path' => '/mindmaps', 'class' => 'mindmap', 'lang' => 'Mindmaps'));

foreach ($mediaboxs as $id => $value_media) {
    echo '<ul class="mediabox-list">';
    $visibility_image = DocumentManager::get_visibility($value_media['path'], $_course, $session_id);
    $id = DocumentManager::get_document_id($_course, $value_media['path']);
    $visibility_icon = ($visibility_image == 0 || $visibility_image == 3) ? 'inactive' : 'active';
    $visibility_command = ($visibility_image == 0 || $visibility_image == 3) ? 'set_visible' : 'set_invisible';
    $visivility = ($visibility_image == 0 || $visibility_image == 3) ? get_lang('Visible') : get_lang('Invisible');
    $base = ($value_media['lang'] == 'Images' || $value_media['lang'] == 'Photos' || $value_media['lang'] == 'Mascot' || $value_media['lang'] == 'Mindmaps') ? 'slideshow' : 'mediabox_view';
    if (api_is_allowed_to_edit() || $visibility_image == 1) {
        $mediabox_Actives = array($id => $value_media);
        echo '<li><div class="ml-top"><a href=' . $base . '.php?' . api_get_cidReq() . '&slide_id=all&document=0&curdirpath=' . urlencode($value_media['path']) . ' class="' . $commonCssClasses . ' create_' . $value_media['class'] . '_button">' . get_lang($value_media['lang']) . '</a></div>';
        echo '<div class="ml-bot">';
        if (api_is_allowed_to_edit()) {
            echo '<a style="' . $img_style . '" href="mediabox.php?' . api_get_cidreq() . '&curdirpath=' . $pathurl . '&' . $visibility_command . '=' . $id . '">' . Display::return_icon($visibility_icon . '.png', $visivility) . '</a>';
        }
        echo '</div>';
        echo '</li>';
    }
    echo '</ul>';
}
if (count($mediabox_Actives) == 0) {
    echo '<div id="emptyPage">' . get_lang('ThereAreNotMediaboxFolderAvailablesNow') . '</div>';
}

// close the content div
echo '</div>';

echo '<div class="actions">';
DocumentManager::show_simplifying_links(true, true);
echo '</div>';

// display the footer
Display::display_footer();