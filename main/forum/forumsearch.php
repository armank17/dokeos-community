
<?php  // $Id: document.php 16494 2008-10-10 22:07:36Z yannoo $

/* For licensing terms, see /dokeos_license.txt */

/**
*	@Author Patrick Cool <patrick.cool@UGent.be>, Ghent University
*	@Copyright Ghent University
*	@Copyright Patrick Cool
*
* 	@package dokeos.forum
*/

// name of the language file that needs to be included
$language_file = array ( 'forum', 'group');

// including the global dokeos file
require ('../inc/global.inc.php');

// the section (tabs)
$this_section=SECTION_COURSES;

// notice for unauthorized people.
api_protect_course_script(true);

// including additional library scripts
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
include_once (api_get_path(LIBRARY_PATH).'groupmanager.lib.php');
include('forumfunction.inc.php');
include('forumconfig.inc.php');

if(isset($_POST['search_term'])){
    $search = $_POST['search_term'];
}else{
    $search = $_GET['search'];
}

$htmlHeadXtra[] = '<script type="text/javascript">
    $(document).ready(function(){
        $(".input_titles").val("'.$search.'");
    });
</script>';

//are we in a lp ?
$origin = '';
if (isset($_GET['origin'])) {
	$origin =  Security::remove_XSS($_GET['origin']);
}

// name of the tool
$nameTools=get_lang('Forum');

// breadcrumbs

if (isset($_SESSION['gradebook'])){
	$gradebook=	$_SESSION['gradebook'];
}

if (!empty($gradebook) && $gradebook=='view') {
	$interbreadcrumb[]= array (
			'url' => '../gradebook/'.$_SESSION['gradebook_dest'],
			'name' => get_lang('Gradebook')
		);
}

if (!empty ($_GET['gidReq'])) {
	$toolgroup = Database::escape_string($_GET['gidReq']);
	api_session_register('toolgroup');
}

if (!empty($_SESSION['toolgroup'])) {
	$_clean['toolgroup']=(int)$_SESSION['toolgroup'];
	$group_properties  = GroupManager :: get_group_properties($_clean['toolgroup']);
	$interbreadcrumb[] = array ("url" => "../group/group.php", "name" => get_lang('Groups'));
	$interbreadcrumb[] = array ("url" => "../group/group_space.php?gidReq=".$_SESSION['toolgroup'], "name"=> get_lang('GroupSpace').' ('.$group_properties['name'].')');
	$interbreadcrumb[] = array ("url" => "viewforum.php?".api_get_cidreq()."&amp;origin=".$origin."&amp;gidReq=".$_SESSION['toolgroup']."&amp;forum=".Security::remove_XSS($_GET['forum']),"name" => prepare4display($current_forum['forum_title']));
	$interbreadcrumb[]=array('url' => 'forumsearch.php','name' => get_lang('ForumSearch'));
} else {
	$interbreadcrumb[]=array('url' => 'index.php?'.api_get_cidreq().'&amp;gradebook='.$gradebook.'','name' => $nameTools);
	$interbreadcrumb[]=array('url' => 'forumsearch.php','name' => get_lang('ForumSearch'));
}

// Display the header
if ($origin=='learnpath') {
	include(api_get_path(INCLUDE_PATH).'reduced_header.inc.php');
} else {
	Display :: display_tool_header($nameTools);
}
// Tool introduction
Display::display_introduction_section(TOOL_FORUM);
// top actions toolbars
echo '<div class="actions">';
	// TODO set link to
	$url = 'index.php?curdirpath='.Security::remove_XSS($_GET['dir']);
	echo '<a href="'.$url.'">'.Display::return_icon('pixel.gif', get_lang('Forum'), array('class' => 'toolactionplaceholdericon toolactionback')).get_lang('Forum').'</a>';

echo '</div>';



// Start main content
echo '<div id="content">';
$page = 1;
if(isset($_REQUEST['page'])){
    $page = $_REQUEST['page'];
}
// tracking
event_access_tool(TOOL_FORUM);
//echo $item=$_REQUEST['search'];
// forum search
forum_search($page,$search);
// ending div#content
echo '</div>';
// footer
Display :: display_footer();