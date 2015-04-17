<?php

/* For licensing terms, see /dokeos_license.txt */

/**
==============================================================================
*	This file allows creating new html documents with an online WYSIWYG html
*	editor.
*	@package dokeos.document
==============================================================================
*/

// name of the language file that needs to be included
$language_file = array('document','gradebook');

// setting the help
$help_content = 'createdocument';

// include the global Dokeos file
require_once '../../../inc/global.inc.php';

// include additional libraries
require_once api_get_path(LIBRARY_PATH).'fileUpload.lib.php';
require_once api_get_path(LIBRARY_PATH).'document.lib.php';
require_once api_get_path(LIBRARY_PATH).'groupmanager.lib.php';
require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';
require_once api_get_path(LIBRARY_PATH).'usermanager.lib.php';
$is_allowed_to_edit = api_is_allowed_to_edit(null,true);
// section (for the tabs)
$this_section = SECTION_COURSES;

// Access restrictions
api_protect_course_script(true);

define('DOKEOS_DOCUMENT', true);
$user_id = api_get_user_id();

$_SESSION['whereami'] = 'document/create';
// Add additional javascript, css
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.validate.js"></script>';
$htmlHeadXtra[] = '
    <style type="text/css" >
        input.error { border: 1px solid red; }
	div.row div.label{
		width: 20%;
                float:left;
                text-align:left!important;
	}
	div.row div.formw{
		width: 100%;
	}
    </style>

    <script type="text/javascript">

    $(document).ready(function(){
        if ($("#create_document").length > 0) {
            $("#create_document").validate({
                rules: {
                    title: {
                      required: true
                    }
                },
                messages: {
                    title: {
                        required: "<img src=\"'.  api_get_path(WEB_IMG_PATH).'exclamation.png\" title=\''.get_lang('Required').'\' />"
                    }
                }
            });
        }
   });
   
</script>';
$currentEditor = strtolower(api_get_setting('use_default_editor'));

   if ($currentEditor <> 'fckeditor') {
        $htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery_modal/jquery.simplemodal-1.4.2.js" language="javascript"></script>';
   }
if (isset($_REQUEST['certificate'])) {
    $nameTools = get_lang('CreateCertificate');
} else {
    $nameTools = get_lang('CreateDocument');
}


/*
-----------------------------------------------------------
	Constants and variables
-----------------------------------------------------------
*/
$dir = isset($_GET['dir']) ? Security::remove_XSS($_GET['dir']) : Security::remove_XSS($_POST['dir']); // please do not modify this dirname formatting
$param_group = (isset($_GET['gidReq'])&& !empty($_GET['gidReq']))?'&gidReq='.$_GET['gidReq']:'';
/*
==============================================================================
		MAIN CODE
==============================================================================
*/

if (strstr($dir, '..')) {
    $dir = '/';
}

if ($dir[0] == '.') {
    $dir = substr($dir, 1);
}

if ($dir[0] != '/') {
    $dir = '/'.$dir;
}

if ($dir[strlen($dir) - 1] != '/') {
    $dir .= '/';
}

// Configuration for the FCKEDITOR
$doc_tree= explode('/', $dir);
$count_dir = count($doc_tree) -2; // "2" because at the begin and end there are 2 "/"
// Level correction for group documents.
if (!empty($group_properties['directory'])) {
	$count_dir = $count_dir > 0 ? $count_dir - 1 : 0;
}
$relative_url='';

for($i=0;$i<($count_dir);$i++) {
	$relative_url.='../';
}
if ($relative_url== '') {
	$relative_url = '/';
}

$html_editor_config = array(
	'ToolbarSet' => (api_is_allowed_to_edit() ? 'Documents' :'DocumentsStudent'),
	'Width' => '100%',
	'Height' => '600',
	'FullPage' => true,
	'InDocument' => true,
	'CreateDocumentDir' => $relative_url,
	'CreateDocumentWebDir' => (empty($group_properties['directory']))
		? api_get_path('WEB_COURSE_PATH').$_course['path'].'/document/'
		: api_get_path('WEB_COURSE_PATH').api_get_course_path().'/document'.$group_properties['directory'].'/',
	'BaseHref' => api_get_path('WEB_COURSE_PATH').$_course['path'].'/document'.$dir
);

$filepath = api_get_path(SYS_COURSE_PATH).$_course['path'].'/document'.$dir;
if (!is_dir($filepath)) {
    $filepath = api_get_path(SYS_COURSE_PATH).$_course['path'].'/document/';
    $dir = '/';
}

// create css folder if it doesn't exist
$perm = api_get_setting('permissions_for_new_directories');
$perm = octdec(!empty($perm)?$perm:'0770');
$css_folder = api_get_path(SYS_COURSE_PATH).$_course['path'].'/document/css';
if (!is_dir($css_folder)) {
        mkdir($css_folder);
        chmod($css_folder, $perm);
        $doc_id = add_document($_course, '/css', 'folder', 0, 'css');
        api_item_property_update($_course, TOOL_DOCUMENT, $doc_id, 'FolderCreated', $_user['user_id']);
        api_item_property_update($_course, TOOL_DOCUMENT, $doc_id, 'invisible', $_user['user_id']);
}


if (!$is_allowed_in_course)
	api_not_allowed(true);

$is_allowedToEdit = api_is_allowed_to_edit();
if (!($is_allowedToEdit || $_SESSION['group_member_with_upload_rights'])) {
	api_not_allowed(true);
}

// tracking
event_access_tool(TOOL_DOCUMENT);

$display_dir = $dir;
$form = new FormValidator('create_document','post','index.php?'.api_get_cidreq().'&dir='.Security::remove_XSS($_GET['curdirpath']).'&selectcat='.Security::remove_XSS($_GET['selectcat']).$param_group.'&action=save');

$renderer = & $form->defaultRenderer();
$form->addElement('hidden', 'dir');
$default['dir'] = $dir;
$form->addElement('hidden','title_edited','false','id="title_edited"');
$form->addElement('hidden','user_id',$user_id);
if (isset($_GET['tplid'])) {
  $form->addElement('hidden','is_template','1');
} else {
  $form->addElement('hidden','is_template','0');
}
$renderer->setElementTemplate($filename_template, 'filename');

$group = array();
if (api_get_setting('use_document_title') == 'true') {
	$form->addElement('text','title',get_lang('Title'),'class="focus" id="title" style="width:300px;position:absolute;margin: -22px 47px"');
	$form->addRule('title', get_lang('FileExists'), 'callback', 'document_exists');
} else {
	$form->addElement('text','filename',get_lang('FileName'),'class="input_titles" id="filename" onblur="check_if_still_empty()"');
	$form->addRule('filename', get_lang('FileExists'), 'callback', 'document_exists');
}
$margin_top = "margin-top:-50px;";
if(strstr($_SERVER["HTTP_USER_AGENT"], "MSIE")) {
   $margin_top = "";
}
	$form->addElement('style_submit_button', 'submit', get_lang('Validate'), 'class="save" style="'.$margin_top.' margin-bottom:10px"');

        $form->add_html_editor('document_content','', false, false, $html_editor_config);





$form->setDefaults($default);
	Display :: display_tool_header($nameTools, "Doc");
        Display::display_introduction_section(TOOL_DOCUMENT);
	// actions
	echo '<div class="actions" style="min-height: 40px;">' . PHP_EOL;
        DocumentManager::show_li_eeight($_GET['document'],$_GET['gidReq'],$_GET['curdirpath'],$curdirpath,$group_properties['directory'],$image_present,'create_document');
        
	echo '</div>' . PHP_EOL;
	echo '<div id="content">';
	$form->display();
	echo '</div>';
echo '	<div class="actions">';
if (api_is_allowed_to_edit()) {
    echo '<a href="' . api_get_self() . '?' . api_get_cidreq() . '&curdirpath=' . $curdirpathurl . $req_gid . '&createdir=1">' . Display::return_icon('pixel.gif', get_lang('CreateDir'), array('class' => 'actionplaceholdericon actioncreatefolder')) . ' ' . get_lang('CreateDir') . '</a>';
}
echo '<a href="' . api_get_self() . '?' . api_get_cidreq() . '&action=downloadfolder">' . Display::return_icon('pixel.gif', get_lang('SaveZip'), array('class' => 'actionplaceholdericon actionsavezip')) . ' ' . get_lang('SaveZip') . '</a>';
if (api_is_allowed_to_edit()) {
    echo '<a href="quota.php?' . api_get_cidreq() . '">' . Display::return_icon('pixel.gif', get_lang('DiskQuota'), array('class' => 'actionplaceholdericon actionquota')) . '  ' . get_lang("DiskQuota") . '</a>';
}
DocumentManager::show_simplifying_links(true, true);    
echo '</div>';
	
	Display::display_footer();

?>
