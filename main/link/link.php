<?php

/* For licensing terms, see /dokeos_license.txt */

/**
  ==============================================================================
 * Main script for the links tool.
 *
 * Features:
 * - Organize links into categories;
 * - favorites/bookmarks-like interface;
 * - move links up/down within a category;
 * - move categories up/down;
 * - expand/collapse all categories (except the main "non"-category);
 * - add link to 'root' category => category-less link is always visible.
 *
 * @author Patrick Cool, main author, completely rewritten
 * @author Rene Haentjens, added CSV file import (October 2004)
 * @package dokeos.link
 * @todo improve organisation, tables should come from database library
  ==============================================================================
 */
// name of the language file that needs to be included
$language_file = array('link', 'admin');

// setting the help
$help_content = 'links';

// including the global Dokeos file
require_once '../inc/global.inc.php';

if(isset($_GET['action'])){
	$action_url = '&amp;action='.$_GET['action'].'&amp;id='.$_GET['id'];
}

// redirect to mvc pattern (temporally)
header('Location: '.api_get_path(WEB_VIEW_PATH).'link/index.php?'.api_get_cidreq().$action_url.$add_params_for_lp);
exit;

// Including additional libraries.
require_once "linkfunctions.php";
require_once '../newscorm/learnpath.class.php';
require_once '../newscorm/learnpathItem.class.php';

/* @todo: is this used? */
define('DOKEOS_LINK', true);

// setting the section (for the tabs)
$this_section = SECTION_COURSES;

// Access restrictions
api_protect_course_script();

// Add additional javascript, css
$htmlHeadXtra[] = '<script src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.ui.all.js" type="text/javascript" language="javascript"></script>';

if (api_is_allowed_to_edit ()) {
 $htmlHeadXtra[] = '<link rel="stylesheet" href="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/thickbox.css" type="text/css" media="screen" />';
 $htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/thickbox.js"></script>';
}

$htmlHeadXtra[] = '<script type="text/javascript">
$(document).ready( function() {
	$("#div_target").attr("style","display:none;")//hide

	$("#id_check_target").click(function () {
      if($(this).attr("checked")==true) {
      	$("#div_target").attr("style","display:block;")//show
      } else {
     	 $("#div_target").attr("style","display:none;")//hide
      }
    });

    $(window).load(function () {
      if($("#id_check_target").attr("checked")==true) {
      	$("#div_target").attr("style","display:block;")//show
      } else {
     	 $("#div_target").attr("style","display:none;")//hide
      }
    });

 } );

 </script>';


//   div_target
// @todo change the $_REQUEST into $_POST or $_GET
// @todo remove this code
$link_submitted = (isset($_POST['submitLink']) ? true : false);
$category_submitted = (isset($_POST['submitCategory']) ? true : false);
$urlview = (!empty($_GET['urlview']) ? $_GET['urlview'] : '');
$submitImport = (!empty($_POST['submitImport']) ? $_POST['submitImport'] : '');
$down = (!empty($_GET['down']) ? $_GET['down'] : '');
$up = (!empty($_GET['up']) ? $_GET['up'] : '');
$catmove = (!empty($_GET['catmove']) ? $_GET['catmove'] : '');
$editlink = (!empty($_REQUEST['editlink']) ? $_REQUEST['editlink'] : '');
$id = (!empty($_REQUEST['id']) ? $_REQUEST['id'] : '');
$urllink = (!empty($_REQUEST['urllink']) ? $_REQUEST['urllink'] : '');
$title = (!empty($_REQUEST['title']) ? $_REQUEST['title'] : '');
$description = (!empty($_REQUEST['description']) ? $_REQUEST['description'] : '');
$selectcategory = (!empty($_REQUEST['selectcategory']) ? $_REQUEST['selectcategory'] : '');
$submitLink = (isset($_REQUEST['submitLink']) ? true : false);
$action = (!empty($_REQUEST['action']) ? $_REQUEST['action'] : '');
$category_title = (!empty($_REQUEST['category_title']) ? $_REQUEST['category_title'] : '');
$submitCategory = isset($_POST['submitCategory']) ? true : false;
$nameTools = get_lang('Links');

//condition for the session
$session_id = api_get_session_id();
$condition_session = api_get_session_condition($session_id, false);

if (isset($_GET['action']) && $_GET['action'] == 'addlink') {
 $nameTools = '';
 $interbreadcrumb[] = array('url' => 'link.php', 'name' => get_lang('Links'));
 $interbreadcrumb[] = array('url' => 'link.php?action=addlink', 'name' => get_lang('AddLink'));
}

if (isset($_GET['action']) && $_GET['action'] == 'addcategory') {
 $nameTools = '';
 $interbreadcrumb[] = array('url' => 'link.php', 'name' => get_lang('Links'));
 $interbreadcrumb[] = array('url' => 'link.php?action=addcategory', 'name' => get_lang('AddCategory'));
}

if (isset($_GET['action']) && $_GET['action'] == 'editlink') {
 $nameTools = '';
 $interbreadcrumb[] = array('url' => 'link.php', 'name' => get_lang('Links'));
 $interbreadcrumb[] = array('url' => '#', 'name' => get_lang('EditLink'));
}

// Variable
$lp_id = Security::remove_XSS($_GET['lp_id']);
// Lp object
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

// Add the extra lp_id parameter to some links
$add_params_for_lp = '';
if (isset($_GET['lp_id'])) {
  $add_params_for_lp = "&amp;lp_id=".$lp_id;
}

// Database Table definitions
$tbl_link = Database::get_course_table(TABLE_LINK);
$tbl_categories = Database::get_course_table(TABLE_LINK_CATEGORY);
$tbl_lp = Database :: get_course_table(TABLE_LP_MAIN);

//statistics
event_access_tool(TOOL_LINK);

// Display the header
Display :: display_tool_header();
?>
<script type="text/javascript">
 /* <![CDATA[ */
 function MM_popupMsg(msg) { //v1.0
  confirm(msg);
 }
 /* ]]> */
</script>
<?php
if (api_is_allowed_to_edit ()) {
?>
<script type="text/javascript">
 $(document).ready(function(){
  var category_before = "";
  var category_after = "";

  $(".category_0, #categories ul").sortable({
   //Allow to put any link in any category
   connectWith: '.category_0, #categories ul',
   handle   :   $('.move1'),
   cursor   :  'move',
   cancel: ".nodrag",
   //Event thrown when the drag n drop start
   start: function(event, ui) {
	  parentElement = ui.item.parent();
    //We keep into the memory the current link category
    category_before = getCategoryId(parentElement);
	var viewParameter = getUrlViewParameter();
	if(category_before == 0 && viewParameter == '') {
            window.location.href = "link.php?<?php echo api_get_cidreq(); ?>&fold=Y";
            return;
	}
   },

   //Event thrown when a link is moved in another category
   receive: function(event, ui) {

    parentElement = ui.item.parent();
    //Check if the parent ul has a category
    //We will compare the id of the parent category and the category id after moved
    category_after = getCategoryId(parentElement);
    itemId = getItemId(ui.item);

    var order = $(this).sortable("serialize") + '&amp;action=updateRecordsListings';
    var record = order.split("&");
    var recordlen = record.length;
    var disparr = new Array();
    for(var i=0;i<(recordlen-1);i++){
     var recordval = record[i].split("=");
     disparr[i] = recordval[1];
    }

    //Links' category has changed
    if (!category_after) {
        category_after = 0;
    }
    if(category_before != category_after){
     $.ajax({
          //We update the link category
          url: "link.php?action=changeLinkCategory&amp;itemId="+itemId+"&amp;categoryId="+category_after,
          complete: function(){
           //we update the order of all links of the new category
           var viewParameter = getUrlViewParameter();
           var viewString = "";
           if(viewParameter.length > 0){
            viewString = "&urlview="+viewParameter;
            window.location.href = "link.php?<?php echo api_get_cidreq(); ?>&action=updateRecordsListings&disporder="+disparr+viewString;
           }
          }
     });
     return false;
    }

   },

   //Event thrown at the end of a drag n drop. Useful only when a link is moved in the same category because if a link is moved in another category,
   //the receive event is thrown first and the redirection will stop all events
   stop: function(event, ui) {

    var order = $(this).sortable("serialize") + '&amp;action=updateRecordsListings';
    var record = order.split("&");
    var recordlen = record.length;
    var disparr = new Array();
    for(var i=0;i<(recordlen-1);i++){
     var recordval = record[i].split("=");
     disparr[i] = recordval[1];
    }

    var viewParameter = getUrlViewParameter();
    var viewString = "";
    if(viewParameter.length > 0){
     viewString = "&urlview="+viewParameter;
    }
    //Update the links order in the category
    window.location.href = "link.php?<?php echo api_get_cidreq(); ?>&action=updateRecordsListings&disporder="+disparr+viewString;
    return;

   }

  });


  //Allow th change the categories order
  $("#categories").sortable(
    {
    connectWith: '#categories',
    cursor   :  'move',
    handle   :  $('.move'),
    cancel: ".nodrag",
    update: function(event, ui) {
    var order = $(this).sortable("serialize") + '&amp;action=updateRecordsListings';
    var record = order.split("&");
    var recordlen = record.length;
    var disparr = new Array();
    for(var i=0;i<(recordlen-1);i++)
    {
     var recordval = record[i].split("=");
     disparr[i] = recordval[1];
    }

    var viewParameter = getUrlViewParameter();
    var viewString = "";
    if(viewParameter.length > 0){
     viewString = "&urlview="+viewParameter;
    }
    //update the order of all categories
    window.location.href = "link.php?<?php echo api_get_cidreq(); ?>&action=updateRecordsListings&type=categories&disporder="+disparr+viewString;
    return;
   }
  });

 });

 function getCategoryId(parentElement){
  if(parentElement.is("[class*='category']")){
   var classList =$(parentElement).attr('class').split(/\s+/);
   var category_id;
   for(var i= 0; i < classList.length; i++){
    var index = classList[i].indexOf('category_');
    if (index != -1) {
     return classList[i].substr(9,classList[i].length);
    }
   }
   return;
  }
 }
 function getItemId(item){
  var arrayTemp = $(item).attr("id").split('_');

  return arrayTemp[1];
 }
 function getUrlViewParameter(){
  var vars = [], hash;
  var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
  for(var i = 0; i < hashes.length; i++)
  {
   hash = hashes[i].split('=');
   if(hash[0] == "urlview"){
    return hash[1];
   }
  }
  return "";
 }

</script>
<?php
}
?>
<?php

$link_id = Security::remove_XSS($_GET['id']);

$action = $_GET['action'];
$updateRecordsArray = $_GET['disporder'];
$disporder = $_GET['disporder'];

if ($action == "changeLinkCategory") {
 $itemId = Security::remove_XSS($_GET["itemId"]);
 $categoryId = Security::remove_XSS($_GET["categoryId"]);
 $sql = "UPDATE $tbl_link SET category_id=" . Database::escape_string($categoryId) . " WHERE id = " . Database::escape_string($itemId);
 $res = Database::query($sql, __FILE__, __LINE__);
 exit;
}

if ($action == "updateRecordsListings") {
 $disparr = explode(",", $disporder);
 if (isset($_GET['type']) && $_GET['type'] == 'categories') {
  $table_name = $tbl_categories;
 } else {
  $table_name = $tbl_link;
 }
 $len = sizeof($disparr);
 $listingCounter = $len;
 for ($i = 0; $i < sizeof($disparr); $i++) {
  $sql = "UPDATE $table_name SET display_order=" . Database::escape_string($listingCounter) . " WHERE id = " . Database::escape_string($disparr[$i]);
  $res = Database::query($sql, __FILE__, __LINE__);
  $listingCounter = $listingCounter - 1;
 }
}


/*
  -----------------------------------------------------------
  Action Handling
  -----------------------------------------------------------
 */
$nameTools = get_lang("Links");

if (isset($_GET['action'])) {
 switch ($_GET['action']) {
  case "addlink":
   if ($link_submitted) {
    if (!addlinkcategory("link")) { // here we add a link
     unset($submitLink);
    }
   }
   break;
  case "addcategory":
   if ($category_submitted) {
    if (!addlinkcategory("category")) { // here we add a category
     unset($submitCategory);
    }
   }
   break;
  case "importcsv":
   if ($_POST["submitImport"]) {
    import_csvfile();
   }
   break;
  case "deletelink":
   deletelinkcategory("link"); // here we delete a link
   break;

  case "deletecategory":
   deletelinkcategory("category"); // here we delete a category
   break;
  case "editlink":
   editlinkcategory("link"); // here we edit a link
   break;
  case "editcategory":
   editlinkcategory("category"); // here we edit a category
   break;
  case "visible":
   change_visibility($_GET['id'], $_GET['scope']); // here we edit a category
   break;
  case "invisible":
   change_visibility($_GET['id'], $_GET['scope']); // here we edit a category
   break;
 }
}

if (isset($_GET['add_to_course']) && isset($_POST['link_add'])) {
$link_id = Security::remove_XSS($_GET['add_to_course']);
$link_title = $_POST['link_title'];
list($lp_name, $lp_id) = explode("@", $_POST['course']);
$tbl_lp_item = Database :: get_course_table(TABLE_LP_ITEM);
$query = "SELECT max(id) AS maxid FROM " . $tbl_lp_item;
$result = api_sql_query($query, __FILE__, __LINE__);
while ($obj = Database::fetch_object($result)) {
  $maxid = $obj->maxid;
}
$query = "SELECT max(display_order) AS disporder FROM " . $tbl_lp_item . " WHERE lp_id=" . Database::escape_string($lp_id);
$result = api_sql_query($query, __FILE__, __LINE__);
while ($obj = Database::fetch_object($result)) {
$display_order = $obj->disporder;
}

 $sql = "INSERT INTO " . $tbl_lp_item . " (lp_id,
							item_type,
							ref,
							title,
							description,
							path,
							previous_item_id,
							next_item_id,
							display_order) VALUES(" . Database::escape_string($lp_id) . ",
							'link',
							'" . ($maxid + 1) . "',
							'" . Database::escape_string($link_title) . "',
							'',
							'" . Database::escape_string($link_id) . "',
							" . $maxid . ",
							" . ($maxid + 2) . ",
							" . ($display_order + 1) . ")";
 $result = api_sql_query($sql, __FILE__, __LINE__);
}

// display the introduction section
Display::display_introduction_section(TOOL_LINK);


if (api_is_allowed_to_edit(null, true) and isset($_GET['action'])) {
 // Search
 if (api_get_setting('search_enabled') == 'true') {
  if (!extension_loaded('xapian')) {
   Display::display_error_message(get_lang('SearchXapianModuleNotInstaled'));
  }
 }
 // Displaying the correct title and the form for adding a category or link. This is only shown when nothing
 // has been submitted yet, hence !isset($submitLink)
 if (($_GET['action'] == "addlink" or $_GET['action'] == "editlink") and empty($_POST['submitLink'])) {
  if ($category == "") {
   $category = 0;
  }

  // actions (when adding a link)
  echo '<div class="actions">';
  echo '<a href="link.php?cidReq=' . Security::remove_XSS($_GET['cidReq']) . '&amp;urlview=' . Security::remove_XSS($_GET['urlview']) . '">' . Display::return_icon('pixel.gif', get_lang('BackToLinksOverview'), array('class' => 'toolactionplaceholdericon toolactionback')) . get_lang('BackToLinksOverview') . '</a>';
  if (isset($_GET['lp_id']) && $_GET['lp_id'] > 0) {
  echo '<a href="../newscorm/lp_controller.php?' . api_get_cidreq() . '&amp;action=add_item&amp;type=step&amp;lp_id='.$_GET['lp_id'].'">' . Display::return_icon('pixel.gif', get_lang('Content'),array('class'=>'toolactionplaceholdericon toolactionauthorcontent')).get_lang("Content") . '</a>';
  echo '<a href="../newscorm/lp_controller.php?' . api_get_cidreq() . '&amp;action=admin_view&amp;lp_id='.$_GET['lp_id'].'">' . Display::return_icon('pixel.gif', get_lang('Scenario'),array('class'=>'toolactionplaceholdericon toolactionauthorscenario')).get_lang("Scenario") . '</a>';
  }
  echo '</div>';

  // start the content div
  echo '<div id="content">';

  echo "<form name='add_link' method=\"post\" action=\"" . api_get_self() . "?action=" . Security::remove_XSS($_GET['action']) . "&amp;urlview=" . Security::remove_XSS($urlview) .$add_params_for_lp. "\">";
  if ($_GET['action'] == "editlink") {
   echo "<input type=\"hidden\" name=\"id\" value=\"" . Security::remove_XSS($_GET['id']) . "\" />";
   $clean_link_id = trim(Security::remove_XSS($_GET['id']));
  }

  echo '	<div class="row">
					<div class="label">
						<span class="form_required">*</span> ' . get_lang('Url') . '
					</div>
					<div class="formw">
						<input type="text" class="focus" name="urllink" size="50" value="' . (empty($urllink) ? 'http://' : api_htmlentities($urllink, ENT_COMPAT, $charset)) . '" />
					</div>
				</div>';

  echo '	<div class="row">
					<div class="label">
						' . get_lang('Text') . '
					</div>
					<div class="formw">
						<input type="text" name="title" size="50" value="' . api_htmlentities($title, ENT_QUOTES, $charset) . '" />
					</div>
				</div>';
  echo '	<div class="row">
					<div class="label">
						' . get_lang('Objective') . '
					</div>
					<div class="formw">
						<textarea rows="3" cols="50" name="description">' . api_htmlentities($description, ENT_QUOTES, $charset) . '</textarea>
					</div>
				</div>';




  $sqlcategories = "SELECT * FROM " . $tbl_categories . " $condition_session ORDER BY display_order DESC";
  $resultcategories = Database::query($sqlcategories, __FILE__, __LINE__);

  if (Database::num_rows($resultcategories)) {
   echo '	<div class="row">
						<div class="label">
							' . get_lang('Category') . '
						</div>
						<div class="formw">';
   echo '			<select name="selectcategory">';
   echo '			<option value="0">--</option>';
   while ($myrow = Database::fetch_array($resultcategories)) {
    echo "		<option value=\"" . $myrow["id"] . "\"";
    if ($myrow["id"] == $category) {
     echo " selected";
    }
    echo ">" . $myrow["category_title"] . "</option>";
   }
   echo '			</select>';
   echo '		</div>
					</div>';
  }

  echo '	<div class="row">
					<div class="label">
						' . get_lang('OnHomepage') . '?
					</div>
					<div class="formw">
						<input id="id_check_target" class="checkbox" type="checkbox" name="onhomepage" id="onhomepage" value="1"' . $onhomepage . '><label for="onhomepage"> ' . get_lang('Yes') . '</label>
					</div>
				</div>';
  echo '	<div class="row" id="div_target">
					<div class="label">
						' . get_lang('AddTargetOfLinkOnHomepage') . '
					</div>
					<div class="formw" >
						<select  name="target_link" id="target_link">
						<option value="_self">_self</option>
						<option value="_blank">_blank</option>
						<option value="_parent">_parent</option>
						<option value="_top">_top</option>
						</select>
					</div>
				</div>';



  if (api_get_setting('search_enabled') == 'true') {
   require_once(api_get_path(LIBRARY_PATH) . 'specific_fields_manager.lib.php');
   $specific_fields = get_specific_field_list();

   foreach ($specific_fields as $specific_field) {
    $default_values = '';
    if ($_GET['action'] == "editlink") {
     $filter = array('course_code' => "'" . api_get_course_id() . "'", 'field_id' => $specific_field['id'], 'ref_id' => Security::remove_XSS($_GET['id']), 'tool_id' => '\'' . TOOL_LINK . '\'');
     $values = get_specific_field_values_list($filter, array('value'));
     if (!empty($values)) {
      $arr_str_values = array();
      foreach ($values as $value) {
       $arr_str_values[] = $value['value'];
      }
      $default_values = implode(', ', $arr_str_values);
     }
    }

    $sf_textbox = '
						<div class="row">
							<div class="label">%s</div>
							<div class="formw">
								<input name="%s" type="text" value="%s"/>
							</div>
						</div>';

    echo sprintf($sf_textbox, $specific_field['name'], $specific_field['code'], $default_values);
   }
  }

  echo '	<div class="row">
					<div class="label">
					</div>
					<div class="formw">
						<button class="save" type="submit" name="submitLink" value="OK">' . get_lang('SaveLink') . '</button>
					</div>
				</div>';

  echo '</form>';

  // close the content div
  echo '</div>';
 } elseif (($_GET['action'] == "addcategory" or $_GET['action'] == "editcategory") and !$submitCategory) {
  if ($_GET['action'] == "addcategory") {
   //	echo '<div class="form_header">'.get_lang('CategoryAdd').'</div>';
   $my_cat_title = get_lang('CategoryAdd');
  } else {
   //	echo '<div class="form_header">'.get_lang('CategoryMod').'</div>';
   $my_cat_title = get_lang('CategoryMod');
  }

  // actions (when adding a category)
  echo '<div class="actions">';
  echo '<a href="link.php?cidReq=' . Security::remove_XSS($_GET['cidReq']) . '&amp;urlview=' . Security::remove_XSS($_GET['urlview']) . '">' . Display::return_icon('pixel.gif', get_lang('BackToLinksOverview'), array('class' => 'toolactionplaceholdericon toolactionback')) . get_lang('BackToLinksOverview') . '</a>';
  echo '</div>';

  // start the content div
  echo '<div id="content">';

  echo "<form name='add_category' method=\"post\" action=\"" . api_get_self() . "?action=" . Security::remove_XSS($_GET['action']) . "&amp;urlview=" . Security::remove_XSS($urlview) . "\">";
  if ($_GET['action'] == "editcategory") {
   echo "<input type=\"hidden\" name=\"id\" value=\"" . $id . "\" />";
  }

  echo '	<div class="row">
					<div class="label">
						<span class="form_required">*</span> ' . get_lang('FolderName') . '
					</div>
					<div class="formw">
						<input type="text" class="focus" name="category_title" size="50" value="' . $category_title . '" />
					</div>
				</div>';

  echo '	<div class="row">
					<div class="label">
					</div>
					<div class="formw">
						<button class="save" type="submit" name="submitCategory">' . get_lang('Validate') . ' </button>
					</div>
				</div>';

  echo "</form>";

  // close the content div
  echo '</div>';
 }
 /* elseif(($_GET['action']=="importcsv") and !$submitImport)  // RH start
   {
   echo "<h4>", get_lang('CsvImport'), "</h4>\n\n",
   "<form method=\"post\" action=\"".api_get_self()."?action=".$_GET['action']."&amp;urlview=".$urlview."\" enctype=\"multipart/form-data\">",
   // uncomment if you want to set a limit: '<input type="hidden" name="MAX_FILE_SIZE" value="32768">', "\n",
   '<input type="file" name="import_file" size="30">', "\n",
   "<input type=\"Submit\" name=\"submitImport\" value=\"".get_lang('Ok')."\">",
   "</form>";
   echo get_lang('CsvExplain');
   } */
}


if (!empty($down)) {
 movecatlink($down);
}
if (!empty($up)) {
 movecatlink($up);
}

if (empty($_GET['action']) || ($_GET['action'] != 'editlink' && $_GET['action'] != 'addcategory' && $_GET['action'] != 'addlink' && $_GET['action'] != 'editcategory') || $link_submitted || $category_submitted) {
 /*
   -----------------------------------------------------------
   Action Links
   -----------------------------------------------------------
  */
 echo '<div class="actions">';
 if (api_is_allowed_to_edit(null, true)) {
  $urlview = Security::remove_XSS($urlview);
  echo "<a href=\"" . api_get_self() . "?" . api_get_cidreq() . "&amp;action=addlink&amp;category=" . (!empty($category) ? $category : '') . "&amp;urlview=$urlview\">" . Display::return_icon('pixel.gif', get_lang('LinkAdd'), array('class' => 'toolactionplaceholdericon toolactionslink')) . '&nbsp;&nbsp;' . get_lang("LinkAdd") . "</a>\n";
  echo "<a href=\"" . api_get_self() . "?" . api_get_cidreq() . "&amp;action=addcategory&amp;urlview=" . $urlview . "\">" . Display::return_icon('pixel.gif', get_lang("Folder"), array('class' => 'toolactionplaceholdericon toolactioncreatefolder')) . '&nbsp;&nbsp;' . get_lang("Folder") . "</a>";
  /* "<a href=\"".api_get_self()."?".api_get_cidreq()."&amp;action=importcsv&amp;urlview=".$urlview."\">".get_lang('CsvImport')."</a>\n", // RH */
  if (isset($_GET['lp_id']) && $_GET['lp_id'] > 0) {
  echo '<a href="../newscorm/lp_controller.php?' . api_get_cidreq() . '&amp;action=add_item&amp;type=step&amp;lp_id='.Security::remove_XSS($_GET['lp_id']).'">' . Display::return_icon('pixel.gif', get_lang('Content'),array('class'=>'toolactionplaceholdericon toolactionauthorcontent')).get_lang("Content") . '</a>';
  echo '<a href="../newscorm/lp_controller.php?' . api_get_cidreq() . '&amp;action=admin_view&amp;lp_id='.Security::remove_XSS($_GET['lp_id']).'">' . Display::return_icon('pixel.gif', get_lang('Scenario'),array('class'=>'toolactionplaceholdericon toolactionauthorscenario')).get_lang("Scenario") . '</a>';
  }
 }
 //making the show none / show all links. Show none means urlview=0000 (number of zeros depending on the
 //number of categories). Show all means urlview=1111 (number of 1 depending on teh number of categories).
 $sqlcategories = "SELECT * FROM " . $tbl_categories . " $condition_session ORDER BY display_order DESC";
 $resultcategories = Database::query($sqlcategories);
 $aantalcategories = Database::num_rows($resultcategories);
 if ($aantalcategories > 0) {
	 if(($_REQUEST['unfold'] == 'Y')||(empty($_REQUEST['fold'])))
	 {
  echo " <a href=\"" . api_get_self() . "?" . api_get_cidreq() . "&fold=Y&amp;urlview=";
  for ($j = 1; $j <= $aantalcategories; $j++) {
   echo "1";
  }
  echo "\">" . Display::return_icon('pixel.gif',get_lang('Unfold'),array('class'=>'toolactionplaceholdericon toolactionunfold'), $shownone) . '&nbsp;&nbsp;' . get_lang('Unfold') . "</a>";
	 }
	else
	 {
  echo " <a href=\"" . api_get_self() . "?" . api_get_cidreq() . "&unfold=Y&amp;urlview=";
  for ($j = 1; $j <= $aantalcategories; $j++) {
   echo "0";
  }
  echo "\">" . Display::return_icon('pixel.gif',get_lang('Fold'),array('class'=>'toolactionplaceholdericon toolactionfold'), $showall) . '&nbsp;&nbsp;' . get_lang('Fold') . "</a>";
	 }
 }
 echo '</div>';

 global $linkCounter;
 $linkCounter = 1;

 // start the content div
 echo '<div id="content">';

 //Starting the table which contains the categories
 $sqlcategories = "SELECT * FROM " . $tbl_categories . " $condition_session ORDER BY display_order DESC";
 $resultcategories = Database::query($sqlcategories);

 // displaying the links which have no category (thus category = 0 or NULL), if none present this will not be displayed
 $sqlLinks = "SELECT * FROM " . $tbl_link . " WHERE category_id=0 or category_id IS NULL";
 $result = Database::query($sqlLinks);
 $numberofzerocategory = Database::num_rows($result);
 if ($numberofzerocategory !== 0) {
	 echo '<table class="data_table">';
  if (api_is_allowed_to_edit ()) {
   echo "<tr><th width='6%' align='center' style='padding-right: 0px;'>" . get_lang('Move') . "</th>";
   echo "<th width='54%' align='left' style='padding-right: 0px;padding-left: 5px;'>" . get_lang('Link') . "</th>";
   echo "<th width='10%' style='padding-right: 0px;'>" . get_lang('Modify') . "</th>";
   echo "<th width='10%' style='padding-right: 0px;'>" . get_lang('Delete') . "</th>";
   echo "<th width='10%' style='padding-right: 0px;'>" . get_lang('Visible') . "</th>";
   echo "<th width='10%' style='padding-right: 0px;'>" . get_lang('Module') . "</th>";
  } else {
   echo "<tr><th width='100%' align='left'>" . get_lang('Links') . "</th>";
  }
  echo "</tr>";
  echo '</table>';
  showlinksofcategory(0);
 }
 else
 {
 	echo '<tr><th>&nbsp;</th></tr></table>';
 }

 $i = 0;
 $catcounter = 1;
 $view = "0";

 if (Database::num_rows($resultcategories)) {
 echo '<div id="contentWrap"><div id="contentLeft"><ul class="dragdrop nobullets" id="categories">';
 //Create empty list to allow move link outside category
 $draggable_class = api_is_allowed_to_edit() ? 'draggable' : '';
 echo '<ul class="dragdrop ui-sortable"><li id="recordsArray_0" class="'.$draggable_class.'">&nbsp;</li></ul>';

 while ($myrow = Database::fetch_array($resultcategories)) {
  //validacion when belongs to a session
  $session_img = api_get_session_image($myrow['session_id'], $_user['status']);

  //if (!isset($urlview))
  if ($urlview == '') {
   // No $view set in the url, thus for each category link it should be all zeros except it's own
   makedefaultviewcode($i);
  } else {
   $view = $urlview;
   $view[$i] = "1";
  }
  // if the $urlview has a 1 for this categorie, this means it is expanded and should be desplayed as a
  // - instead of a +, the category is no longer clickable and all the links of this category are displayed
  $myrow["description"] = text_filter($myrow["description"]);

  $width_link_column = "94";
  $parent_draggable = "parent_no_draggable";
  if (api_is_allowed_to_edit(null, true)) {
   $parent_draggable = "parent_draggable";
   $width_link_column = "60";
  }

  if ($urlview[$i] == "1") {
     $newurlview = $urlview;
     $newurlview[$i] = "0";

     //echo '<tr><td>';
     echo '<li id="recordsArray_' . $myrow["id"] . '" class="category">';
     echo '<div class="'.$parent_draggable.' rounded move">';
     //echo '<table class="data_table">';
     echo '<table width="100%" style="margin-left: -10px;margin-top:-2px;" >';
     echo '<tr>';
     //echo '<th width="6%" style="text-align:left; padding: 0px 0px 0px 5px; background: #e2e1df"><img src="../img/drag-and-drop.png"></th>';
     echo '<th width="' . $width_link_column . '%"  style="font-weight: bold; text-align:left;padding-left: 5px; vertical-align: top; height: 40px;">';
     echo '<a href="' . api_get_self() . "?" . api_get_cidreq() . "&amp;urlview=" . Security::remove_XSS($newurlview) . "\">";
     echo '<img src="../img/action-slide-up.png" align="top" /></a>';
     echo '<a style="display: inline-block; margin:8px 0px 0px 5px;" href="' . api_get_self() . "?" . api_get_cidreq() . "&amp;urlview=" . Security::remove_XSS($newurlview) . "\">" . api_htmlentities($myrow["category_title"], ENT_QUOTES, $charset) . "</a>";
     echo '</th>';
     if (api_is_allowed_to_edit(null, true)) {
      showcategoryadmintools($myrow["id"]);
      echo '<th style="">&nbsp;</th>';
     }
     echo '</tr>';
     echo '</table>';
	 echo '</div>';
     echo showlinksofcategory($myrow["id"]);
     //echo '</td></tr>';
//   echo '</table></div></li>';
	 echo '</li>';
     //echo '</td></tr>';
  } else {
  	// Collapsed view
     //echo '<tr><td>';
     echo '<li id="recordsArray_' . $myrow["id"] . '" class="category">';
     echo '<div class="'.$parent_draggable.' rounded move">';
     echo '<table width="100%" style="margin-left: -10px;margin-top:-2px;">';
     echo '<tr>';
     //echo '<th width="6%" style="background: #e2e1df;text-align:left; padding: 0px 0px 0px 5px;"><img src="../img/drag-and-drop.png"></th>';
     echo '<th width="' . $width_link_column . '%" style="font-weight: bold; text-align:left;padding-left: 5px;vertical-align: top; height: 40px;">
  					<a href="' . api_get_self() . "?" . api_get_cidreq() . "&amp;urlview=";
     echo is_array($view) ? implode('', $view) : $view;
     echo "\"><img src='../img/action-slide-down.png' align='top' /></a>";
     echo '<a style="display: inline-block; margin:8px 0px 0px 5px;" href="' . api_get_self() . "?" . api_get_cidreq() . "&amp;urlview=";
     echo is_array($view) ? implode('', $view) : $view;
     echo "\">" . api_htmlentities($myrow["category_title"], ENT_QUOTES, $charset) . $session_img . '</a>';
     echo '</th>';
     if (api_is_allowed_to_edit(null, true)) {
      showcategoryadmintools($myrow["id"]);
      echo '<th style="">&nbsp;</th>';
     }
     echo '</tr>';
     echo '</table></div></li>';
     //echo '</td></tr>';
  }
  // displaying the link of the category
  $i++;
 }
 echo '</ul></div></div>';
 }//End If num rows >0


 echo '<div class="clear"> </div>';


 // close the content div
 echo '<div class="clear"> </div>';
 echo '</div>';
}

// bottom actions bar
//echo '<div class="actions">';
//echo '&nbsp;';
//echo '</div>';

// display the footer
Display::display_footer();
?>
