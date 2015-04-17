<?php
/* For licensing terms, see /dokeos_license.txt */

/**
 * Learning Path
 * @package dokeos.learnpath
 */

// Language files
$language_file[] = 'learnpath';

// setting the help
$help_content = 'learnpath';

// including the global Dokeos file
require_once '../inc/global.inc.php';
require_once 'learnpath.class.php';
require_once 'learnpathItem.class.php';

// Load Jquery library
//$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-1.4.2.min.js" language="javascript"></script>';

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

// Display the header
Display::display_tool_header();
// display the actions
echo '<div class="actions" >';
echo lp_video_actions();
echo '</div>';

// start the content div
echo '<div id="content">';

// the main content
lp_video_main();

// close the content div
echo '</div>';


// display the actions
$secondary_actions = lp_video_secondary_actions();
if ($secondary_actions != '') {
    echo '<div class="actions ">';
    echo $secondary_actions;
    echo '</div>';
}

function lp_video_actions(){
    global $charset;

    $mymodule_lang_var = api_convert_encoding(get_lang('MyModule'), $charset, api_get_system_encoding());

    $lp_id = Security::remove_XSS($_GET['lp_id']);
    $return.= '';
    $return.= '<a href="lp_controller.php?cidReq=' . Security::remove_XSS($_GET['cidReq']) . '&amp;action=add_item&amp;type=step&amp;lp_id=' . $lp_id . '">' . Display::return_icon('pixel.gif', $mymodule_lang_var, array('class' => 'toolactionplaceholdericon toolactionauthorcontent')).$mymodule_lang_var . '</a>';
    return $return;
}

function lp_video_secondary_actions(){
  $lp_id = Security::remove_XSS($_GET['lp_id']);
  $return.= '';
  //$return.= '<a href="lp_controller?' . api_get_cidreq() . '&amp;action=build&amp;lp_id=' . $lp_id . '">' . Display::return_icon('build.png', get_lang('Build')).get_lang("Build") . '</a>';
  //$return.= '<a href="lp_controller?' . api_get_cidreq() . '&amp;gradebook=&amp;action=view&amp;lp_id=' . $lp_id . '">' . Display::return_icon('view.png', get_lang('ViewRight')).get_lang("ViewRight") . '</a>';
	 return $return;
}

function lp_video_main(){
global $charset;
// Database table definition
$table_document 	= Database::get_course_table(TABLE_DOCUMENT, $_course['dbName']);
$propTable = Database::get_course_table(TABLE_ITEM_PROPERTY);
// variable initialisation
$get_cur_path=Security::remove_XSS($_GET['curdirpath']);
$lp_id = Security::remove_XSS($_GET['lp_id']);
$title = "";
// Platform templates
$i=0;
$j=1;

echo '<table style="width:100%;" class="gallery">';

$sql = "SELECT * FROM $table_document doc,$propTable prop WHERE doc.id = prop.ref AND prop.tool = '".Database::escape_string(TOOL_DOCUMENT)."' AND doc.filetype = 'file' AND doc.path LIKE '/video/%'";

$result = Database::query($sql, __FILE__, __LINE__);

while ($row = Database::fetch_array($result)) {
	$title_lang_var = api_convert_encoding($row['title'], $charset, api_get_system_encoding());
	if (!$i%3) {
		echo '<tr>';
	}
	echo '<td align="center">';
 // Add link
	echo '<a href="lp_controller.php?'.api_get_cidReq().'&amp;action=add_item&amp;type='.TOOL_DOCUMENT.'&amp;resource=video&amp;lp_id='.$lp_id.'&amp;tplid='.$row['id'].'&amp;postURI='.$title.'">';
	echo '	<div class="section">
			<div class="sectioncontent_template_video">'.Display::return_icon('pixel.gif', get_lang('Mediabox'), array('class' => 'mediaactionplaceholdericon media_create_video_button')).'</div>
			<div class="sectionfooter">'.$title_lang_var.'</div>
		</div>';
	echo '</a>';
	echo '</td>';
	if ($j==3) {
		echo '</tr>';
		$j=0;
	}
	$i++;
	$j++;
}
echo '</table>';
}
