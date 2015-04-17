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

if (api_is_in_group()) {
    $group_properties = GroupManager::get_group_properties($_SESSION['_gid']);
}

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

// we do this in order to avoid the condition in html_editor.php ==> if ($this -> fck_editor->Config['CreateDocumentWebDir']=='' || $this -> fck_editor->Config['CreateDocumentDir']== '')
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

//I'm in the certification module?
$is_certificate_mode = false;
$is_certificate_array = explode('/',$_GET['dir']);
array_shift($is_certificate_array);
if ($is_certificate_array[0]=='certificates') {
	$is_certificate_mode = true;
}
/**************************************************/
$to_group_id = 0;
if (!$is_certificate_mode) {
	if (isset ($_SESSION['_gid']) && $_SESSION['_gid'] != '') {
		$req_gid = '&gidReq='.$_SESSION['_gid'];
		$interbreadcrumb[] = array ("url" => "../group/group_space.php?gidReq=".$_SESSION['_gid'], "name" => get_lang('GroupSpace'));
		$noPHP_SELF = true;
		$to_group_id = $_SESSION['_gid'];
		$group = GroupManager :: get_group_properties($to_group_id);
		$path = explode('/', $dir);
		if ('/'.$path[1] != $group['directory']) {
			api_not_allowed(true);
		}
	}
	$interbreadcrumb[] = array ("url" => "./document.php?curdirpath=".Security::remove_XSS($_GET['dir']).$req_gid, "name" => get_lang('Documents'));
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
if (isset ($group)) {
	$display_dir = explode('/', $dir);
	unset ($display_dir[0]);
	unset ($display_dir[1]);
	$display_dir = implode('/', $display_dir);
}

// Create a new form
$form = new FormValidator('create_document','post',api_get_self().'?'.api_get_cidreq().'&dir='.Security::remove_XSS($_GET['curdirpath']).'&selectcat='.Security::remove_XSS($_GET['selectcat']).$param_group);

// form title
$renderer = & $form->defaultRenderer();

// Hidden element with current directory
$form->addElement('hidden', 'dir');
$default['dir'] = $dir;
// Filename

$form->addElement('hidden','title_edited','false','id="title_edited"');
if (isset($_GET['tplid'])) {
  $form->addElement('hidden','is_template','1');
} else {
  $form->addElement('hidden','is_template','0');
}


/**
 * Check if a document width the choosen filename allready exists
 */
function document_exists($filename) {
	global $filepath;
	$filename = replace_dangerous_char($filename);
	return !file_exists($filepath.$filename.'.html');
}

// Change the default renderer for the filename-field to display the dir and extension
/*
$renderer = & $form->defaultRenderer();
*/
//$filename_template = str_replace('{element}', "<tt>$display_dir</tt> {element} <tt>.html</tt>", $renderer->_elementTemplate);
//$filename_template = str_replace('{element}', "{element}", $renderer->_elementTemplate);
$renderer->setElementTemplate($filename_template, 'filename');

// initialize group array
$group = array();
// If allowed, add element for document title
if (api_get_setting('use_document_title') == 'true') {
	//$group[]= $form->add_textfield('title', get_lang('Title'),true,'class="input_titles" id="title"');
	// replace the 	add_textfield with this
	$form->addElement('text','title',get_lang('Title'),'class="focus" id="title" style="width:300px;"');
	//$form->applyFilter('title','trim');
	//$form->addRule('title', get_lang('ThisFieldIsRequired'), 'required');
	$form->addRule('title', get_lang('FileExists'), 'callback', 'document_exists');
} else {
	//$form->add_textfield('filename', get_lang('FileName'),true,'class="input_titles" id="filename"  onblur="check_if_still_empty()"');
	// replace the 	add_textfield with this
	$form->addElement('text','filename',get_lang('FileName'),'class="input_titles" id="filename" onblur="check_if_still_empty()"');
	//$form->applyFilter('filename','trim');
	//$form->addRule('filename', get_lang('ThisFieldIsRequired'), 'required');
	$form->addRule('filename', get_lang('FileExists'), 'callback', 'document_exists');
}

// This fix display problem of the button on I.E
$margin_top = "margin-top:-28px;";
if(strstr($_SERVER["HTTP_USER_AGENT"], "MSIE")) {
   $margin_top = "";
}
if ($is_certificate_mode)
	$form->addElement('style_submit_button', 'submit', get_lang('CreateCertificate'), 'class="save" style="'.$margin_top.' margin-bottom:10px;"');
else
	$form->addElement('style_submit_button', 'submit', get_lang('Validate'), 'class="save" style="'.$margin_top.' margin-bottom:10px"');

/* Show read-only box only in groups */
if(!empty($_SESSION['_gid'])) {
	//$renderer->setElementTemplate('<div class="row"><div class="label"></div><div class="formw">{element}{label}</div></div>', 'readonly');
	$group[]= $form->createElement('checkbox','readonly','',get_lang('ReadOnly'));
}
/*
// add group to the form
if ($is_certificate_mode)
	$form->addGroup($group, 'filename_group', get_lang('CertificateName') ,'&nbsp;&nbsp;&nbsp;', false);
else
	$form->addGroup($group, 'filename_group', get_lang('FileName') ,'&nbsp;&nbsp;&nbsp;', false);

$form->addRule('filename_group', get_lang('ThisFieldIsRequired'), 'required');

if (api_get_setting('use_document_title') == 'true') {
	$form->addGroupRule('filename_group', array(
	  'title' => array(
	    array(get_lang('ThisFieldIsRequired'), 'required'),
	    array(get_lang('FileExists'),'callback', 'document_exists')
	    )
	));
} else {
	$form->addGroupRule('filename_group', array(
	  'filename' => array(
		array(get_lang('ThisFieldIsRequired'), 'required'),
	    array(get_lang('FileExists'),'callback', 'document_exists')
	    )
	));
}*/

//$form->addElement('style_submit_button', 'submit', get_lang('SaveDocument'), 'class="save"');

// HTML-editor
//$renderer->setElementTemplate('<div class="row"><div class="label" id="frmModel" style="overflow: visible;"></div><div class="formw">{element}</div></div>', 'content');
//$form->addElement('html','<div style="display:block; height:525px; width:240px; position:absolute; top:50px; left:50px;"><table width="100%" cellpadding="3" cellspacing="3" border="1"><tr><td><a href="document.php?curdirpath='.Security::remove_XSS($_GET['dir']).'">'.Display::return_icon('go_previous_32.png',get_lang('Back'),array('style'=>'vertical-align:middle;')).'&nbsp;&nbsp;'.get_lang('Back').'</a></td></tr><tr><td align="center"><a href="javascript:callTplGallery()"><div class="actions" ><img src="'.api_get_path(WEB_IMG_PATH).'tools_wizard.png"></div></a></td></tr><tr><td align="center"><h4>Templates Gallery</h4></td></tr></table></div>');
//$renderer->setElementTemplate('<div class="row"><div class="label" style="overflow: visible;"><table width="100%" cellpadding="3" cellspacing="3"><tr><td align="center"><a href="document.php?curdirpath='.Security::remove_XSS($_GET['dir']).'">'.Display::return_icon('go_previous_32.png',get_lang('Back'),array('style'=>'vertical-align:middle;')).'&nbsp;&nbsp;'.get_lang('Back').'</a></td></tr><tr><td align="center"><a href="javascript:callTplGallery()"><div class="actions" ><img src="'.api_get_path(WEB_IMG_PATH).'tools_wizard.png"></div></a></td></tr><tr><td align="center"><h4>Templates Gallery</h4></td></tr></table></div><div class="formw">{element}</div></div>', 'content');
$renderer->setElementTemplate('<div class="row"><div style="width:100%;float:right;">{element}</div></div>', 'content');
$form->add_html_editor('document_content','', false, false, $html_editor_config);
// Comment-field
//$form->addElement('textarea', 'comment', get_lang('Comment'), array ('rows' => 5, 'cols' => 50));
if(isset($_REQUEST['tplid'])) {
	$table_sys_template = Database::get_main_table('system_template');
	$table_template = Database::get_main_table(TABLE_MAIN_TEMPLATES);
	$table_document = Database::get_course_table(TABLE_DOCUMENT, $_course['dbName']);
	$user_id = api_get_user_id();

	// setting some paths
	$img_dir = api_get_path(REL_CODE_PATH).'img/';
	$default_course_dir = api_get_path(REL_CODE_PATH).'default_course_document/';

	$req_dir = $_REQUEST['dir'];
	$count = substr_count($req_dir,"/");
	$relpath = '';
	if($req_dir != '/') {
		for($i=0;$i<$count;$i++) {
			$relpath .= '../';
		}
	}

        // update templates.css in course
	$css_name = api_get_setting('stylesheets');
        if (file_exists(api_get_path(SYS_PATH).'main/css/'.$css_name.'/templates.css')) {
            $template_content = str_replace('../../img/', api_get_path(REL_CODE_PATH).'img/', file_get_contents(api_get_path(SYS_PATH).'main/css/'.$css_name.'/templates.css'));
            $template_content = str_replace('images/', api_get_path(REL_CODE_PATH).'css/'.$css_name.'/images/', $template_content);
            file_put_contents($css_folder.'/templates.css', $template_content);
        }

        if (!isset($_REQUEST['tmpltype'])) {
		if ($_REQUEST['tplid'] <> 0) {
			$query = 'SELECT content,title FROM '.$table_sys_template.' WHERE id='.Database::escape_string(Security::remove_XSS($_REQUEST['tplid']));
                        echo $query;
			$result = api_sql_query($query,__FILE__,__LINE__);
			while($obj = Database::fetch_object($result)) {
                            $valcontent = $obj->content;
                            $title = $obj->title;
			}

                        // add css inside document content
                        $template_css = '';
                        if (strpos($valcontent, '/css/templates.css') === false) {
                            $template_css = '<link rel="stylesheet" href="'.api_get_path(WEB_COURSE_PATH).$_course['path'].'/document/css/templates.css" type="text/css" />';
                        }

			$valcontent =  str_replace('{CSS}',$template_css, $valcontent);

                        // add js inside document content
                        $js = '';
                        if (strpos($valcontent, '/javascript/jquery.highlight.js') === false) {
                            $js .='<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-1.4.2.min.js" language="javascript"></script>';
                            $js .='<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'jwplayer/jwplayer.js" language="javascript"></script>'.PHP_EOL;
                            if (api_get_setting('show_glossary_in_documents') != 'none') {  								
								// If is allowed the use of themes inside of this course
                                if(api_get_setting('allow_course_theme')=='true' && api_get_course_setting('course_theme', null, true)!=''){ 
                                    $js .='<link type="text/css" href="'.api_get_path(WEB_CSS_PATH).api_get_course_setting('course_theme', null, true).'/default.css" rel="stylesheet" />'; 
                                }else{
                                    // Else the stylesheet used will be the platform stylesheet   
                                   $js .='<link type="text/css" href="'.api_get_path(WEB_CSS_PATH).api_get_setting('stylesheets').'/default.css" rel="stylesheet" />'; 
                                }                 
                                    $js .='<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/js/jquery-ui-1.8.1.custom.min.js"></script>';
                                    $js .='<link type="text/css" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/css/ui-lightness/jquery-ui-1.8.1.custom.css" rel="stylesheet" />';

                                if (api_get_setting('show_glossary_in_documents') == 'ismanual') {
//                                    $js .= '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'fckeditor/editor/plugins/glossary/fck_glossary_manual.js"></script>';
                                    $js .= '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.highlight.js"></script>';
                                    $js .= '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'fckeditor/editor/plugins/glossary/fck_glossary_automatic.js"></script>';
                                } else {
                                    $js .= '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.highlight.js"></script>';
                                    $js .= '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'fckeditor/editor/plugins/glossary/fck_glossary_automatic.js"></script>';
                                }
                            }  
                        }
                        $valcontent = str_replace('</head>',$js.'</head>',$valcontent);
			$valcontent =  str_replace('{IMG_DIR}',$img_dir, $valcontent);
			$valcontent =  str_replace('{REL_PATH}', api_get_path(REL_PATH), $valcontent);
			$valcontent =  str_replace('{COURSE_DIR}',$default_course_dir, $valcontent);
			$valcontent =  str_replace('images/templates/',$relpath.'images/templates/',$valcontent);
			$valcontent =  str_replace('mascot/',$relpath.'mascot/',$valcontent);
			$default['document_content'] = $valcontent;
                        //$default['title'] = get_lang($title);
		}
	} else {
			$sql = "SELECT template.id, template.title, template.description, template.image, template.ref_doc, document.path
			FROM ".$table_template." template, ".$table_document." document
			WHERE user_id='".Database::escape_string($user_id)."'
			AND course_code='".Database::escape_string(api_get_course_id())."'
			AND document.id = template.ref_doc";
			$result_template = api_sql_query($sql,__FILE__,__LINE__);
			while ($row = Database::fetch_array($result_template)) {
                            $valcontent = file_get_contents(api_get_path('SYS_COURSE_PATH').$_course['path'].'/document'.$row['path']);
                            $title = $row['title'];
			}
			$default['document_content'] = $valcontent;
                        //$default['title'] = $title;
	}
}
if(!empty($_REQUEST['filename'])) {
		$default['title'] = Security::remove_XSS($_REQUEST['filename']);
}
$form->setDefaults($default);

// If form validates -> save the new document
if ($form->validate()) {
    $to_group_id = $_SESSION['_gid'];
    $values = $form->exportValues();
    $readonly = isset($values['readonly']) ? 1 : 0;
    if (api_get_setting('use_document_title') != 'true') {
    $normal_name = $values['filename'];
        $clean_val = (trim($normal_name));
    } else {
    $normal_name = $values['title'];
        $clean_val = (trim($values['title']));
    }
    $clean_val=Security::remove_XSS($clean_val);
    $original_name = Security::remove_XSS($normal_name);
    $clean_val=replace_dangerous_char(stripslashes($clean_val));
    $clean_val=disable_dangerous_file($clean_val);
    $clean_val=replace_accents($clean_val);

    if (api_get_setting('use_document_title') != 'true') {
            $values['filename']=$clean_val;
            $values['title'] = str_replace('/','-',$values['filename']);
            $filename = disable_dangerous_file(replace_dangerous_char($values['filename'], 'strict'));
            $title = $values['filename'];
    } else	{
            $values['title']=$clean_val;
            $values['filename'] = str_replace('/','-',$values['title']);
            $filename = disable_dangerous_file(replace_dangerous_char($values['title'], 'strict'));
            $title = $values['title'];
    }

    $texte = stripslashes($values['document_content']);
    $texte = Security::remove_XSS($texte,COURSEMANAGERLOWSECURITY);
    if(empty($title)) {
            $filename = 'Noname_'.rand(1,100);
    }
    $extension = 'html';
    $js = '';
    // add js path if it doesn't exist
    /*if (strpos($texte, 'javascript/jquery.highlight.js') === false) {
        $js .='<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-1.4.2.min.js" language="javascript"></script>';
        $js .='<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'jwplayer/jwplayer.js" language="javascript"></script>'.PHP_EOL;
        if (api_get_setting('show_glossary_in_documents') != 'none' && isset($_POST['is_template']) && $_POST['is_template'] == 0) {
            if (api_get_setting('show_glossary_in_documents') == 'ismanual') {
//                $js .= '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'fckeditor/editor/plugins/glossary/fck_glossary_manual.js"></script>';
                $js .= '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.highlight.js"></script>';
                $js .= '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'fckeditor/editor/plugins/glossary/fck_glossary_automatic.js"></script>';
            } else {
                $js .= '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.highlight.js"></script>';
                $js .= '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'fckeditor/editor/plugins/glossary/fck_glossary_automatic.js"></script>';
            }
        }
    }*/

	if (strpos($texte, 'javascript/jquery.highlight.js') === false) {            
        $js .='<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-1.4.2.min.js" language="javascript"></script>';
        $js .='<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'jwplayer/jwplayer.js" language="javascript"></script>'.PHP_EOL;
                                           
        // If is allowed the use of themes inside of this course
        if(api_get_setting('allow_course_theme')=='true' && api_get_course_setting('course_theme', null, true)!=''){  
            $js .='<link type="text/css" href="'.api_get_path(WEB_CSS_PATH).api_get_course_setting('course_theme', null, true).'/default.css" rel="stylesheet" />'; 

        }else{
            // Else the stylesheet used will be the platform stylesheet   
            $js .='<link type="text/css" href="'.api_get_path(WEB_CSS_PATH).api_get_setting('stylesheets').'/default.css" rel="stylesheet" />'; 
        }
               
        if (api_get_setting('show_glossary_in_documents') != 'none' && isset($_POST['is_template']) && $_POST['is_template'] == 0) {  
                $js .='<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/js/jquery-ui-1.8.1.custom.min.js"></script>';
                $js .='<link type="text/css" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/css/ui-lightness/jquery-ui-1.8.1.custom.css" rel="stylesheet" />';  
            if (api_get_setting('show_glossary_in_documents') == 'ismanual') {
//                $js .= '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'fckeditor/editor/plugins/glossary/fck_glossary_manual.js"></script>';
                $js .= '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.highlight.js"></script>';
                $js .= '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'fckeditor/editor/plugins/glossary/fck_glossary_automatic.js"></script>';
            } else {
                $js .= '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.highlight.js"></script>';
                $js .= '<script language="javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'fckeditor/editor/plugins/glossary/fck_glossary_automatic.js"></script>';
            }
        }
    }

    $template_css = '';
    $extra_css = '';
    // add template css path if it doesn't exist
    if (strpos($texte, '/css/templates.css') === false) {
        $template_css = '<link rel="stylesheet" href="'.api_get_path(WEB_COURSE_PATH).$_course['path'].'/document/css/templates.css" type="text/css" />';
    }
    if(!isset($_GET['tplid']) || empty($_REQUEST['tplid'])){
        $template_css = '';
    }

    $texte = str_replace('</head>', $template_css.$js.'</head>', $texte);

	if ($fp = @fopen($filepath.$filename.'.'.$extension, 'w')) {
		$texte = text_filter($texte);
		$content = str_replace(api_get_path('WEB_COURSE_PATH'), $_configuration['url_append'].'/courses/', $texte);
		fputs($fp, $content);
		fclose($fp);
		$files_perm = api_get_setting('permissions_for_new_files');
		$files_perm = octdec(!empty($files_perm)?$files_perm:'0770');
		chmod($filepath.$filename.'.'.$extension,$files_perm);
		$perm = api_get_setting('permissions_for_new_directories');
		$perm = octdec(!empty($perm)?$perm:'0770');
		if (!is_dir($filepath.'css')) {
			mkdir($filepath.'css');
			chmod($filepath.'css', $perm);
			$doc_id = add_document($_course, $dir.'css', 'folder', 0, 'css');
			api_item_property_update($_course, TOOL_DOCUMENT, $doc_id, 'FolderCreated', $_user['user_id']);
			api_item_property_update($_course, TOOL_DOCUMENT, $doc_id, 'invisible', $_user['user_id']);
		}

		if (!is_file($filepath.'css/templates.css')) {
			//make a copy of the current css for the new document
			copy(api_get_path(SYS_CODE_PATH).'css/'.api_get_setting('stylesheets').'/templates.css', $filepath.'css/templates.css');
			$doc_id = add_document($_course, $dir.'css/templates.css', 'file', filesize($filepath.'css/templates.css'), 'templates.css');
			api_item_property_update($_course, TOOL_DOCUMENT, $doc_id, 'DocumentAdded', $_user['user_id']);
			api_item_property_update($_course, TOOL_DOCUMENT, $doc_id, 'invisible', $_user['user_id']);
		}

		$file_size = filesize($filepath.$filename.'.'.$extension);
		$save_file_path = $dir.$filename.'.'.$extension;

		$document_id = add_document($_course, $save_file_path, 'file', $file_size, $original_name,null,$readonly);
		if ($document_id) {
			api_item_property_update($_course, TOOL_DOCUMENT, $document_id, 'DocumentAdded', $_user['user_id'], $to_group_id);
			//update parent folders
			item_property_update_on_folder($_course, $_GET['dir'], $_user['user_id']);
			$new_comment = isset ($_POST['comment']) ? trim($_POST['comment']) : '';
			$new_title = isset ($_POST['title']) ? trim($_POST['title']) : '';
			if ($new_comment || $new_title) {
				$TABLE_DOCUMENT = Database::get_course_table(TABLE_DOCUMENT);
				$ct = '';
				if ($new_comment)
					$ct .= ", comment='$new_comment'";
				if ($new_title)
					$ct .= ", title='$new_title'";
				api_sql_query("UPDATE $TABLE_DOCUMENT SET".substr($ct, 1)." WHERE id = '$document_id'", __FILE__, __LINE__);
			}
			$dir= substr($dir,0,-1);
			$selectcat = '';
            // Get last 3 characters
            $ext = substr(strrchr($save_file_path,'.'),1);
            $ext = strtolower($ext);
			if (isset($_REQUEST['selectcat']))
				$selectcat = "&selectcat=".Security::remove_XSS($_REQUEST['selectcat']);
            if(api_get_setting('search_enabled')=='true' && extension_loaded('xapian')) {
               DocumentManager::search_engine_save($document_id, $new_title, $content, $save_file_path);
            }
            $allowed_file_types = array('htm','html','gif','jpg','jpeg','png','bmp');
            if (in_array($ext, $allowed_file_types)) {
			   echo '<script type="text/javascript">
                               window.location.href="index.php?'.api_get_cidReq().'&file='.$save_file_path.'&curdirpath='.$dir.$param_group.'&action=listing";</script>';
			} else {
                           echo '<script type="text/javascript">window.location.href="index.php?'.api_get_cidReq().'&curdirpath='.$dir.$param_group.'&action=listing";</script>';
			}
			exit ();
		} else {
			Display :: display_tool_header($nameTools, "Doc");
			Display :: display_error_message(get_lang('Impossible'));
			Display :: display_footer();
		}
	} else {
		Display :: display_tool_header($nameTools, "Doc");
		//api_display_tool_title($nameTools);
		Display :: display_error_message(get_lang('Impossible'));
		Display :: display_footer();
	}
} else {
	// Display the header
	Display :: display_tool_header($nameTools, "Doc");
        Display::display_introduction_section(TOOL_DOCUMENT);
	if (isset($_REQUEST['certificate'])) {
		$all_information_by_create_certificate=DocumentManager::get_all_info_to_certificate();
		$str_info='';
		foreach ($all_information_by_create_certificate[0] as $info_value) {
			$str_info.=$info_value.'<br/>';
		}
		$create_certificate=get_lang('CreateCertificateWithTags');
		echo '<div class="section_white"><div class="sectioncontent_white_bg">'.$create_certificate.': <br />'.$str_info.'</div></div>';
	}
	// actions
	echo '<div class="actions" style="min-height: 40px;">' . PHP_EOL;
        DocumentManager::show_li_eeight($_GET['document'],$_GET['gidReq'],$_GET['curdirpath'],$curdirpath,$group_properties['directory'],$image_present,'create_document');
        
//	echo '<a href="' . api_get_path(WEB_CODE_PATH). 'document/document.php?'.api_get_cidreq().'&curdirpath='.Security::remove_XSS($_GET['dir']).'&selectcat=' . Security::remove_XSS($_GET['selectcat']).$param_group.'">'.Display::return_icon('pixel.gif', get_lang('Documents'), array('class' => 'toolactionplaceholdericon toolactiondocumentcreate')).' '.get_lang('Documents').'</a>' . PHP_EOL;
//	echo '<a href="' . api_get_path(WEB_CODE_PATH). 'document/template_gallery.php?'.api_get_cidreq().'&doc=N&dir='.Security::remove_XSS($_GET['dir']).'&selectcat=' . Security::remove_XSS($_GET['selectcat']).$param_group.'">'.Display::return_icon('pixel.gif', get_lang('Templates'), array('class' => 'toolactionplaceholdericon toolactiontemplates')).' '.get_lang('Templates').'</a>' . PHP_EOL;
//        echo '<a href="' . api_get_path(WEB_CODE_PATH). 'document/upload.php?' . api_get_cidreq() . '&certificate=true&path=' . $curdirpathurl . $req_gid . '&selectcat=' . Security::remove_XSS($_GET['selectcat']) . '">' . Display::return_icon('pixel.gif', get_lang('UplUpload'), array('class' => 'toolactionplaceholdericon toolactionupload')) . ' ' . get_lang('UplUpload') . '</a>' . PHP_EOL;
	echo '</div>' . PHP_EOL;

	// start the content div
	echo '<div id="content">';

	// display the form
	$form->display();

	// close the content div
	echo '</div>';
        // bottom actions
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
	// display the footer
	Display::display_footer();
}
?>
