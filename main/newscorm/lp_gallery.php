<?php
/* For licensing terms, see /dokeos_license.txt */

/**
 * Learning Path - Gallery
 * @package dokeos.learnpath
 * @author Isaac Flores
 */

// Language files that should be included
$language_file[] = 'learnpath';
$language_file[] = 'document';
// setting the help
$help_content = 'learnpath';

// including the global Dokeos file
require_once '../inc/global.inc.php';
require_once 'learnpath.class.php';
require_once 'learnpathItem.class.php';
require_once api_get_path(LIBRARY_PATH).'fileUpload.lib.php';
require_once api_get_path(LIBRARY_PATH).'document.lib.php';

// including additional libraries
//include_once(api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');

// setting the tabs
$this_section=SECTION_COURSES;

// Security check
$is_allowed_to_edit = api_is_allowed_to_edit(null,true);
if(!$is_allowed_to_edit){
  api_not_allowed(true);
}

// Variable
$learnpath_id = Security::remove_XSS($_GET['lp_id']);

if (isset($_SESSION['lpobject'])) {
 if ($debug > 0)
  error_log('New LP - SESSION[lpobject] is defined', 0);
 $oLP = unserialize($_SESSION['lpobject']);
 if (is_object($oLP)) {
  if ($debug > 0)
   error_log('New LP - oLP is object', 0);
  if ($myrefresh == 1 OR (empty($oLP->cc)) OR $oLP->cc != api_get_course_id()) {
   if ($debug > 0)
    error_log('New LP - Course has changed, discard lp object', 0);
   if ($myrefresh == 1) {
    $myrefresh_id = $oLP->get_id();
   }
   $oLP = null;
   api_session_unregister('oLP');
   api_session_unregister('lpobject');
  } else {
   $_SESSION['oLP'] = $oLP;
   $lp_found = true;
  }
 }
}

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
if (empty($charset)) {
	$charset = 'ISO-8859-1';
}
// create css folder if it doesn't exist
$css_name = api_get_setting('stylesheets');    
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

if (!file_exists($css_folder.'/templates.css')) {
    if(file_exists(api_get_path(SYS_PATH).'main/css/'.$css_name.'/templates.css')) {
        $template_content = str_replace('../../img/', api_get_path(REL_CODE_PATH).'img/', file_get_contents(api_get_path(SYS_PATH).'main/css/'.$css_name.'/templates.css'));
        $template_content = str_replace('images/', api_get_path(REL_CODE_PATH).'css/'.$css_name.'/images/', $template_content);            
        file_put_contents($css_folder.'/templates.css', $template_content);
    }
}

// Add additional javascript, css
//$htmlHeadXtra[] = '<script type="text/javascript" language="javascript">$("#actions").click(function(){ alert("You clicked an action")});</script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-1.4.2.min.js" language="javascript"></script>';

// setting the breadcrumbs
//$interbreadcrumb[] = array ("url"=>"overview.php", "name"=> get_lang('OverviewOfAllCodeTemplates'));
//$interbreadcrumb[] = array ("url"=>"coursetool.php", "name"=> get_lang('CourseTool'));

// Display the header
//Display::display_header(get_lang('Gallery'));
Display::display_tool_header();
// display the actions
echo '<div class="actions">';
echo lp_template_actions();
echo '</div>';

// start the content div
echo '<div id="content">';

// the main content
lp_template_main();

// close the content div
echo '</div>';


// display the actions
$secondary_actions = lp_template_secondary_actions();
if ($secondary_actions != '') {
    echo '<div class="actions">';
    echo $secondary_actions;
    echo '</div>';
}
// display the footer
//Display::display_footer();

function lp_template_actions(){
    global $charset;
    $mymodule_lang_var = api_convert_encoding(get_lang('MyModule'), $charset, api_get_system_encoding());
    if (api_get_setting('enable_pro_settings') == "true") {
        $mymodule_lang_var = api_convert_encoding(get_lang('Builder'), $charset, api_get_system_encoding());
    }
    $lp_id = Security::remove_XSS($_GET['lp_id']);
    $return.= '<a href="lp_controller.php?cidReq=' . Security::remove_XSS($_GET['cidReq']) . '&amp;action=add_item&amp;type=step&amp;lp_id=' . $lp_id . '">' . Display::return_icon('pixel.gif', $mymodule_lang_var, array('class' => 'toolactionplaceholdericon toolactionauthorcontent')).$mymodule_lang_var . '</a>';
    
    return $return;
}

function lp_template_secondary_actions(){
    $lp_id = Security::remove_XSS($_GET['lp_id']);
    return $return;
}

function lp_template_main(){
global $charset;	
// Database table definition
$table_sys_template 	= Database::get_main_table('system_template');
$table_template 	= Database::get_main_table(TABLE_MAIN_TEMPLATES);
$table_document 	= Database::get_course_table(TABLE_DOCUMENT, $_course['dbName']);

// variable initialisation
$get_cur_path=Security::remove_XSS($_GET['curdirpath']);
$get_file=Security::remove_XSS($_GET['file']);
$user_id = api_get_user_id();
$lp_id = Security::remove_XSS($_GET['lp_id']);
$title = "";
// Platform templates
$i=0;
$j=1;

echo '<table class="gallery" style="width:100%;">';
$limit = api_get_setting('enable_document_templates') === 'true'?'':' LIMIT 6';
//if (api_get_setting('enable_document_templates') === 'true') {         
    $sql = "SELECT id, title, image, comment, content FROM $table_sys_template ORDER BY id DESC $limit";
    $result = api_sql_query($sql, __FILE__, __LINE__);
    while ($row = Database::fetch_array($result)) {
            $empty_lang_var = api_convert_encoding(get_lang('Empty'), $charset, api_get_system_encoding());
            $title_lang_var = api_convert_encoding(get_lang($row['title']), $charset, api_get_system_encoding());

            if (!empty($row['image'])) {
                    $image = api_get_path(WEB_PATH).'home/default_platform_document/template_thumb/'.$row['image'];
            } else {
                    $image = api_get_path(WEB_PATH).'home/default_platform_document/template_thumb/empty.gif';
            }
            if (!$i%4) {
                    echo '<tr>';
            }
            // a special template: the empty page
            if ($i == 0) {
                    echo '<td>';
                    echo '<div class="section">';
                    echo '<a href="lp_controller.php?'.api_get_cidReq().'&amp;action=add_item&amp;type=document&amp;lp_id='.$lp_id.'&amp;postURI='.$title.'&amp;tplid=0">';
                    echo '<div class="sectiontitle">'.$empty_lang_var.'</div>
                                    <div class="sectioncontent_template"><img border="0" src="'.api_get_path(WEB_PATH).'home/default_platform_document/template_thumb/empty.gif"></div></a>
                            </div>';
                    echo '</td>';
                    $j++;
            }


            echo '<td align="center">';
            // Add link
            echo '<div class="section">';
            echo '<a href="lp_controller.php?'.api_get_cidReq().'&amp;action=add_item&amp;type=document&amp;lp_id='.$lp_id.'&amp;tplid='.$row['id'].'&amp;postURI='.$title.'">';
            echo '<div class="sectiontitle">'.$title_lang_var.'</div>
                            <div class="sectioncontent_template"><img border="0" src="'.$image.'"></div></a>
                    </div>';
            echo '</td>';
            if ($j==4) {
                    echo '</tr>';
                    $j=0;
            }
            $i++;
            $j++;
    }
//}
/*else {
    echo '<tr><td align="left">';
    echo '<div class="section">';
    echo '<a href="lp_controller.php?'.api_get_cidReq().'&amp;action=add_item&amp;type=document&amp;lp_id='.$lp_id.'&amp;postURI='.$title.'&amp;tplid=0">';
    echo '<div class="sectiontitle">'.$empty_lang_var.'</div>
                    <div class="sectioncontent_template"><img border="0" src="'.api_get_path(WEB_PATH).'home/default_platform_document/template_thumb/empty.gif"></div></a>
            </div>';
    echo '</td></tr>';
}*/
echo '</table>';

// COURSE TEMPLATES
$sql = "SELECT template.id, template.title, template.description, template.image, template.ref_doc, document.path
			FROM ".$table_template." template, ".$table_document." document
			WHERE user_id='".Database::escape_string($user_id)."'
			AND course_code='".Database::escape_string(api_get_course_id())."'
			AND document.id = template.ref_doc";
$result = api_sql_query($sql, __FILE__, __LINE__);
$numrows = Database::num_rows($result);

if($numrows <> 0) {
	$i=0;
	$j=1;

	echo '<table class="gallery" style="width:100%;">';

	while ($row = Database::fetch_array($result)) {
		$title_lang_var = api_convert_encoding(get_lang($row['title']), $charset, api_get_system_encoding());

		if (!empty($row['image'])) {
				$image = api_get_path(WEB_CODE_PATH).'upload/template_thumbnails/'.$row['image'];
			} else {
				$image = api_get_path(WEB_PATH).'home/default_platform_document/template_thumb/noimage.gif';
			}
		if (!$i%4) {
			 echo '<tr>';
		}

  echo '<td align="center">';
		echo '<div class="section">';
	    echo '<a href="lp_controller.php?'.api_get_cidReq().'&amp;action=add_item&amp;type=document&amp;lp_id='.$lp_id.'&amp;tplid='.$row['id'].'&amp;postURI='.$title.'&tmpltype=Personal">';
		echo '<div class="sectiontitle">'.$title_lang_var.'</div>
			   <div class="sectioncontent_template"><img border="0" src="'.$image.'"></div></a>
			</div>';
		echo '</td>';
		if ($j==4) {
			echo '</tr>';
			$j=0;
		}
		$i++;
		$j++;
	}
	echo '</table>';
 }
}