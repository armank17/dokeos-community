<?php

/* For licensing terms, see /dokeos_license.txt */

/**
==============================================================================
* 	Exercise Administration
*	@package dokeos.exercise
==============================================================================
*/
// Language files that should be included
$language_file[]='exercice';
// setting the help
$help_content = 'exerciselist';

// including the global library
require_once '../inc/global.inc.php';

// including additional libraries
include('exercise.class.php');
include('exercise.lib.php');
include_once(api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
require_once '../newscorm/learnpath.class.php';
require_once '../newscorm/learnpathItem.class.php';
// setting the tabs
$this_section=SECTION_COURSES;

if(!api_is_allowed_to_edit()) {
	api_not_allowed(true);
}

// Add additional javascript, css
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-1.4.2.min.js" language="javascript"></script>';
$htmlHeadXtra[] = '<script type="text/javascript">
  $(document).ready(function (){
    $("div.label").attr("style","width: 100%;text-align:left");
    $("div.row").attr("style","width: 100%;");
    $("div.formw").attr("style","width: 100%;");
  });
</script>';

// Add the lp_id parameter to all links if the lp_id is defined in the uri
if (isset($_GET['lp_id']) && $_GET['lp_id'] > 0) {
  $lp_id = Security::remove_XSS($_GET['lp_id']);
 $htmlHeadXtra[] = '<script type="text/javascript">
    $(document).ready(function (){
      $("a[href]").attr("href", function(index, href) {
          var param = "lp_id=' . $lp_id . '";
           var is_javascript_link = false;
           var info = href.split("javascript");

           if (info.length >= 2) {
             is_javascript_link = true;
           }
           if ($(this).attr("class") == "course_main_home_button" || $(this).attr("class") == "course_menu_button"  || $(this).attr("class") == "next_button"  || $(this).attr("class") == "prev_button" || is_javascript_link) {
             return href;
           } else {
             if (href.charAt(href.length - 1) === "?")
                 return href + param;
             else if (href.indexOf("?") > 0)
                 return href + "&" + param;
             else
                 return href + "?" + param;
           }
      });
    });
  </script>';
}

$htmlHeadXtra[] ='<script type="text/javascript">
$(document).ready(function(){

	$(function() {
		$("#contentLeft ul").sortable({ opacity: 0.6, cursor: "move",cancel: ".nodrag", update: function() {
			var order = $(this).sortable("serialize") + "&action=updateQuizCategory";
			var record = order.split("&");
			var recordlen = record.length;
			var disparr = new Array();
			for(var i=0;i<(recordlen-1);i++)
			{
			 var recordval = record[i].split("=");
			 disparr[i] = recordval[1];
			}
			// call ajax to save new position
			window.location.href = "'.api_get_self().'?'.api_get_cidReq().'&action=updateQuizCategory&disporder="+disparr;
		}
		});
	});

});
</script> ';

// Variable
$learnpath_id = Security::remove_XSS($_GET['lp_id']);
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
  $add_params_for_lp = "&lp_id=".$learnpath_id;
}

$TBL_QUIZ_CATEGORY = Database::get_course_table(TABLE_QUIZ_CATEGORY);

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'updateQuizCategory') {
$disparr = explode(",", $_REQUEST['disporder']);
$len = sizeof($disparr);
$listingCounter = 1;
	for ($i = 0; $i < sizeof($disparr); $i++) {
	$sql = "UPDATE $TBL_QUIZ_CATEGORY SET display_order=" . Database::escape_string($listingCounter) . " WHERE id = " . Database::escape_string($disparr[$i]);
	$res = Database::query($sql, __FILE__, __LINE__);
	$listingCounter = $listingCounter + 1;
	}
    header('Location:exercise_category.php?'.api_get_cidReq());
    exit;
}

/*********************
 * INIT EXERCISE
 *********************/

// Display header
Display :: display_tool_header();

echo '<div class="actions">';
echo '<a href="exercice.php?'.api_get_cidreq().'">'.Display :: return_icon('pixel.gif', get_lang('List'),array('class'=>'toolactionplaceholdericon toolactionback')) . get_lang('List').'</a>';
echo '<a href="'.api_get_self().'?'.api_get_cidreq().'&action=add_category">'.Display :: return_icon('pixel.gif', get_lang('Newcategory'),array('class'=>'toolactionplaceholdericon toolactionnewcategory')) . get_lang('Newcategory').'</a>';
echo '</div>';

if(isset($_REQUEST['cat_id']))
{
	$sql = "SELECT * FROM $TBL_QUIZ_CATEGORY WHERE id = ".$_REQUEST['cat_id'];
        
	$result = api_sql_query($sql, __FILE__, __LINE__);
	while($row = Database::fetch_array($result))
	{
		$category_title = $row['category_title'];
	}
}

echo '<div id="content">';

$form = new FormValidator('exercise_category', 'post', api_get_self().'?'.api_get_cidreq().'&cat_id='.$_REQUEST['cat_id'], null, array('style' => 'width: 100%; border: 0px'));
if ($form -> validate()) {
		$form->getSubmitValue('category');
		$category_added = category_add($form);
	}
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete_category')
{
	$sql = "DELETE FROM $TBL_QUIZ_CATEGORY WHERE id = ".$_REQUEST['cat_id'];
	api_sql_query($sql, __FILE__, __LINE__);
	header('Location:'.api_get_self().'?'.api_get_cidReq());
}

if(isset($_REQUEST['action']) && ($_REQUEST['action'] == 'add_category' || $_REQUEST['action'] == 'edit_category'))
{
	echo '<table width="100%" border="0"><tr><td width="10%">&nbsp;</td><td width="50%" valign="top">';
	$form->addElement('html','<div class="row"><div class="form_header">'.get_lang('Newcategory').'</div></div>');
	$form->addElement('text', 'category', get_lang('Category'), 'class="focus";style="width:300px;"');

    $form->addElement('html', '<br/>');
    $form->addElement('style_submit_button', 'submitExercise', get_lang('Ok'), 'class="save"');
    $form->addElement('html', '</div>');

    $form->addRule('category', get_lang('Category'), 'required');
	$defaults['category'] = $category_title;
	$form->setDefaults($defaults);
	$form -> display();
	echo '</td><td>';
	Display::display_icon('KnockOnWood.png', get_lang('Teacher'));
	echo '</td>';
	echo '</tr></table>';
}
else
{       $session_id = api_get_session_id();
        $session_condition = ' session_id = '.$session_id;
        /*  if I'm in a session should search in its SESSION and without session */
        if ($session_id > 0){
            $session_condition = $session_condition. ' OR session_id= 0';
        }
	$query = "SELECT * FROM $TBL_QUIZ_CATEGORY WHERE  $session_condition ORDER BY display_order";
        
	$result = api_sql_query($query, __FILE__, __LINE__);

	echo '<table class="data_table" width="100%"><tr>';
	echo '<th width="10%">'.get_lang('Move').'</th>';
	echo '<th width="70%">'.get_lang('Category').'</th>';
	echo '<th width="10%">'.get_lang('Edit').'</th>';
	echo '<th width="10%">'.get_lang('Delete').'</th>';
	echo '</tr></table>';

	echo '<div id="contentWrap"><div id="contentLeft"><ul class="dragdrop nobullets " id="categories">';
        $i = 0;
	while ($row = Database::fetch_array($result)) {
            $class = ($i%2==0) ? 'row_odd' : 'row_even';
		echo '<tr><td>';
	    echo '<li id="recordsArray_' . $row['id'] . '" class="category">';
		echo '<div>';
		echo '<table class="data_table" width="100%">';

		echo '<tr class="'.$class.'" >';
		echo '<td width="10%" align="center"><img src="../img/drag-and-drop.png"></td>';
		echo '<td class="nodrag" width="70%">'.$row['category_title'].'</td>';
		if(api_is_allowed_to_edit())
		{
		echo '<td class="nodrag" width="10%" align="center"><a href="'.api_get_self().'?'.api_get_cidReq().'&action=edit_category&cat_id='.$row['id'].'">'.Display::return_icon('pixel.gif',get_lang('Edit'),array('class'=>'actionplaceholdericon actionedit')).'</a></td>';
		echo '<td class="nodrag" width="10%" align="center"><a href="'.api_get_self().'?'.api_get_cidReq().'&action=delete_category&cat_id='.$row['id'].'">'.Display::return_icon('pixel.gif','Delete',array('class'=>'actionplaceholdericon actiondelete')).'</a></td>';
		}
		echo '</tr>';
		echo '</table></div></li></td></tr>';
                $i++;
	}
//	echo '</table>';
	echo '</ul></div></div>';
}

echo '</div>';

function category_add($form)
{
	$TBL_QUIZ_CATEGORY = Database::get_course_table(TABLE_QUIZ_CATEGORY);
	$category_title = $form->getSubmitValue('category');

	if(isset($_REQUEST['cat_id']) && !empty($_REQUEST['cat_id']))
	{
		$sql = "UPDATE $TBL_QUIZ_CATEGORY SET category_title = '".$category_title."' WHERE id = ".$_REQUEST['cat_id'];
		api_sql_query($sql, __FILE__, __LINE__);
	}
	else
	{
		$result = Database::query("SELECT MAX(display_order) FROM  ".$TBL_QUIZ_CATEGORY);
		list ($orderMax) = Database::fetch_row($result);
		$order = $orderMax +1;
		$session_id = api_get_session_id();

		$sql = "INSERT INTO $TBL_QUIZ_CATEGORY (category_title,display_order,session_id)
						VALUES(
							'" . Database::escape_string($category_title) . "',
							".$order.",
							" . $session_id . "
							)";
		api_sql_query($sql, __FILE__, __LINE__);
	}
}

//echo '<div class="actions">';
//echo '</div>';

// display footer
Display::display_footer();
?>