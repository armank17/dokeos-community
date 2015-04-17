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
$language_file = array('document');

// setting the help
$help_content = 'documenttemplategallery';

// include the global Dokeos file
require_once '../inc/global.inc.php';
require_once api_get_path(LIBRARY_PATH).'fileUpload.lib.php';
require_once api_get_path(LIBRARY_PATH).'document.lib.php';

// insert templates.css file inside course
$filepath = api_get_path(SYS_COURSE_PATH).$_course['path'].'/document/';
$css_name = api_get_setting('stylesheets');

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

if (!file_exists($filepath.'css/templates.css')) {
    if(file_exists(api_get_path(SYS_PATH).'main/css/'.$css_name.'/templates.css')) {
        $template_content = str_replace('../../img/', api_get_path(REL_CODE_PATH).'img/', file_get_contents(api_get_path(SYS_PATH).'main/css/'.$css_name.'/templates.css'));
        $template_content = str_replace('images/', api_get_path(REL_CODE_PATH).'css/'.$css_name.'/images/', $template_content);            
        file_put_contents($filepath.'css/templates.css', $template_content);
    }
}

// section (for the tabs)
$this_section = SECTION_COURSES;

// Database table definition
$table_sys_template 	= Database::get_main_table('system_template');	
$table_template 	= Database::get_main_table(TABLE_MAIN_TEMPLATES);	
$table_document 	= Database::get_course_table(TABLE_DOCUMENT, $_course['dbName']);

// variable initialisation
$_SESSION['whereami'] = 'document/create';
if(isset($_GET['curdirpath']) && !empty($_GET['curdirpath'])) {
	$get_cur_path=Security::remove_XSS($_GET['curdirpath']);
} else {
	$get_cur_path=Security::remove_XSS($_GET['dir']);
}
$get_file=Security::remove_XSS($_GET['file']);
$user_id = api_get_user_id();

// Display header
Display :: display_tool_header(get_lang('TemplateGallery'));
Display::display_introduction_section(TOOL_DOCUMENT);
if(isset($_REQUEST['filename'])){
	$title = $_REQUEST['filename'];
} else {
	$title = '';
}

$certificate_link = "";
if (isset($_GET['certificate'])) {
	$certificate_link = "certificate= true";
}
// ACTIONS
echo '<div class="actions" style="min-height: 40px;">';
DocumentManager::show_li_eeight($_GET['document'],$_GET['gidReq'],$_GET['curdirpath'],$curdirpath,$group_properties['directory'],$image_present,'template_gallery',$file,$req_gid,$_GET['lp_id'],$is_certificate_mode,$path,$slide_id);
// what is the difference between the if code block and the else code block? 
//if($_REQUEST['doc'] == 'N') {
//	echo '<a href="document.php?'.api_get_cidreq().'&curdirpath='.urlencode($get_cur_path).'&selectcat=' . Security::remove_XSS($_GET['selectcat']).'&'.$certificate_link.'">'.Display::return_icon('pixel.gif', get_lang('Documents'), array('class' => 'toolactionplaceholdericon toolactionback')).' '.get_lang('Documents').'</a>';
//} else {
//	echo '<a href="document.php?'.api_get_cidreq().'&curdirpath='.urlencode($get_cur_path).'&selectcat=' . Security::remove_XSS($_GET['selectcat']).'&'.$certificate_link.'">'.Display::return_icon('pixel.gif', get_lang('Documents'), array('class' => 'toolactionplaceholdericon toolactionback')).' '.get_lang('Documents').'</a>';
//}
echo '</div>';


// start the content div
echo '<div id="content">';
DocumentManager::show_back_directory($curdirpath, $group_properties['directory'],TRUE,$path);
// display the tool title
//api_display_tool_title(get_lang('TemplateGallery'));

// Platform templates
$i=0;
$j=1;

echo '<table class="gallery">';

$limit = '';
if (api_get_setting('enable_document_templates') !== 'true') {
    $limit = ' LIMIT 3';
}

$sql = "SELECT id, title, image, comment, content FROM $table_sys_template 
        WHERE comment LIKE 'tpl_ppt%' ORDER BY id DESC $limit";
$result = api_sql_query($sql, __FILE__, __LINE__);
while ($row = Database::fetch_array($result)) {
	if (!empty($row['image'])) {
		$image = api_get_path(WEB_PATH).'home/default_platform_document/template_thumb/'.$row['image'];
	} else {
		$image = api_get_path(WEB_PATH).'home/default_platform_document/template_thumb/empty.gif';
	}
	if(!$i%4){
		echo '<tr>';
	}
	// a special template: the empty page
	if($i==0){
		echo '<td>';
		echo '	<div class="section">';
		if($_REQUEST['doc'] == 'N') {
			echo '<a href="create_document.php?'.api_get_cidReq().'&postURI='.$title.'&dir='.urlencode($get_cur_path).'&selectcat=' . Security::remove_XSS($_GET['selectcat']).'&'.$certificate_link.'">';
		} else {
			echo '<a href="edit_document.php?'.api_get_cidReq().'&selectcat=' . Security::remove_XSS($_GET['selectcat']).'&'.$certificate_link.'">';
		}
		  echo '<div class="sectiontitle">'.get_lang('Empty').'</div>
				<div class="sectioncontent_template"><img border="0" src="'.api_get_path(WEB_PATH).'home/default_platform_document/template_thumb/empty.gif"/></div></a>
			</div>';
		echo '</td>';
		$j++;	
	}

	echo '<td>';	
	echo '	<div class="section">';
	if($_REQUEST['doc'] == 'N') {
		echo '<a href="create_document.php?'.api_get_cidReq().'&tplid='.$row['id'].'&postURI='.$title.'&dir='.urlencode($get_cur_path).'&selectcat=' . Security::remove_XSS($_GET['selectcat']).'&'.$certificate_link.'">';
	} else {
		echo '<a href="edit_document.php?'.api_get_cidReq().'&tplid='.$row['id'].'&curdirpath='.urlencode($get_cur_path).'&file='.urlencode($get_file).'&selectcat=' . Security::remove_XSS($_GET['selectcat']).'&'.$certificate_link.'">';
	}
	echo '<div class="sectiontitle">'.get_lang($row['title']).'</div>
			<div class="sectioncontent_template"><img border="0" src="'.$image.'" /></div></a>
		</div>';
	echo '</td>';
	if($j==4)
	{
		echo '</tr>';
		$j=0;
	}
	$i++;
	$j++;
}
echo '</table>';



// COURSE TEMPLATES
$sql = "SELECT template.id, template.title, template.description, template.image, template.ref_doc, document.path 
			FROM ".$table_template." template, ".$table_document." document 
			WHERE user_id='".Database::escape_string($user_id)."'
			AND course_code='".Database::escape_string(api_get_course_id())."'
			AND document.id = template.ref_doc"; 
$result = api_sql_query($sql, __FILE__, __LINE__);
$numrows = Database::num_rows($result);
if($numrows <> 0)
{
	$i=0;
	$j=1;

	echo '<table class="gallery">';

	// looping through all the course templates
	while ($row = Database::fetch_array($result)) {
		// use the 'empty' image if there is no image for the template
		if (!empty($row['image'])) {
			$image = api_get_path(WEB_CODE_PATH).'upload/template_thumbnails/'.$row['image'];
		} else {			
			$image = api_get_path(WEB_PATH).'home/default_platform_document/template_thumb/noimage.gif';
		}

		// start the table row
		if(!$i%4) {
			echo '<tr>';
		}
	
		// start the table cell
		echo '<td>';	
		echo '	<div class="section">';

		// link to create a document or edit the document template
		if($_REQUEST['doc'] == 'N') {
			echo '<a href="create_document.php?'.api_get_cidReq().'&tplid='.$row['id'].'&tmpltype=Personal&dir='.urlencode($get_cur_path).'&selectcat=' . Security::remove_XSS($_GET['selectcat']).'&'.$certificate_link.'">';
		} else {
			echo '<a href="edit_document.php?'.api_get_cidReq().'&tplid='.$row['id'].'&curdirpath='.urlencode($get_cur_path).'&file='.urlencode($get_file).'&tmpltype=Personal&selectcat=' . Security::remove_XSS($_GET['selectcat']).'&'.$certificate_link.'">';
	        }

		// the title of the template
		echo '<div class="sectiontitle">'.$row['title'].'</div>';
		echo '	<div class="sectioncontent_template"><img border="0" src="'.$image.'"></div></a>';
		echo '</div>';

		// close the table cell
		echo '</td>';

		// close the table row (if we have 4 cells already)
		if($j==4) {
			echo '</tr>';
			$j=0;
		}

		$i++;
		$j++;
	}
	echo '</table>';
}
// close the content div
echo '</div>';
// display footer
Display::display_footer();