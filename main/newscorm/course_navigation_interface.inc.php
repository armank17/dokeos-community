<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* 	Learning Path
*	Functions to render html for new Dokeos 2.0 styled interface in course view or others
*	generate menu with main button with toggle jquery effect onclick
*	needs JQuery for JS scripts
*	@package dokeos.learnpath
*/

/**
 * Convert Toc to menuItemm for using renderCourseToggleMenu()
 * @param array tocs (from learnpath::get_toc())
 * @return array list of item menu with correct keys, or false on failure
*/
 function getMenuItemsFromToc($tocs = array(), $currentId = 1){
    if (empty($tocs) || !is_array($tocs)) return false;
    $menuItems = array();
    for ($i=0 ; $i < count($tocs) ; $i++) {
            $item = array();
            $currentClass = ($currentId == $tocs[$i]['id'])?" current": "";
            $item ['href']= "#";
            $item ['onclick']= 'javascript:switch_item('.$currentId.', '.$tocs[$i]['id'].'); hideCourseMenu(); return false;';
            $item ['text']= $tocs[$i]['title'];
            $item ['class'] = str_replace(" ", '_', $tocs[$i]['status']).$currentClass;
            $item ['id'] = $tocs[$i]['id'];
            $item['item_type'] = $tocs[$i]['item_type'];
            $menuItems[]= $item;
    }
    return $menuItems;
 }

 /**
  * Render HTML Code to display top header in course view
  * @param string - title of current page
  * @return string - HTML code
  *
  */
function renderCourseHeader($title = "", $progressBar, $menuItems, $charset){

    $tbl_lp_item = Database :: get_course_table(TABLE_LP_ITEM);
    $sql = "SELECT * FROM " . $tbl_lp_item . " WHERE lp_id = " . $_REQUEST['lp_id'] . " ORDER BY display_order";
    $result = api_sql_query($sql, __FILE__, __LINE__);
    $arrLP = array();

    while ($row = Database :: fetch_array($result)) {
    if ($row['item_type'] == 'certificate') { continue; }
        $arrLP[] = array('id' => $row['id']);
    }

    $course_info = api_get_course_info();
    if ($course_info['subscribe_allowed'] == 1 && api_is_anonymous()) {

        if (!isset($_SESSION['progressBar'])) {
            $percent = intval(round(100 / count($arrLP)));
            $sum = 0;
            foreach($arrLP as $id => $value){
                $sum = $sum + $percent;
                if (end($arrLP)== $value) {
                    $_SESSION['progressBar'][] = 100;
                } else {
                    $_SESSION['progressBar'][] = $sum;
                }
            }
        }

        echo '
            <script language="javascript">
                $(document).ready(function() {
                    $("#coursenextbutton").click(function(){
                        var resUrl = "'.api_get_path(WEB_AJAX_PATH).'progressBar.ajax.php?lpid='.$_GET['lp_id'].'";
                        $.ajax({ url: resUrl,beforeSend: function() {},success: function(data) {}});
                    });
                });
            </script>';

        $num = (!isset($_SESSION['count'])) ? 0 : $_SESSION['count'];
        $progressBar[0] =  $_SESSION['progressBar'][$num];
    }
    global $_course;
    if ($GLOBALS['learner_view']) {
        $param = "&learner_view=true";
    } else {
        $param = '';
    }
    $html = "<div id='left'>";
    $html .= '<a id="back2home3" class="course_main_home_button" href="lp_controller.php?' . api_get_cidreq() . '&action=return_to_course_homepage' . $param . '" target="_self" onclick="window.parent.API.save_asset();" alt="' . $altHome . '" title="' . $altHome . '">';
    $html .= '<img src="' . api_get_path(WEB_IMG_PATH) . 'spacer.gif" width="42px" height="37px" alt="' . $altHome . '" title="' . $altHome . '" />';
    $html .= '</a>';
    $html .= "</div>";

    $html.= "<div id='courseTitle'>" . // title + progress bar
                "<div class='container'><div id='module-title' style='width:530px;padding-left:20px;text-align:left;line-height:17px;'>".cut($title, 60, true).'</div>'.renderProgressBar($progressBar)."</div>".
            "</div>";

//    $html .= "<div id='bg_end_title'></div>";

    $altHome = api_convert_encoding(get_lang('CourseHomepageLink'), $charset, api_get_system_encoding());

    if (count($menuItems) > 1) {
        $arrows = renderNavigationArrows();	// no navigation buttons if just one page
        $html .= "<div id='right'>";
        if ($_SESSION['oLP']->lp_interface == 0 || $_SESSION['oLP']->lp_interface == 4) {
            $html .= '<a class ="course_menu_button" href="#">';
            $html .= '<img id="courseMenuButton" src="../img/spacer.gif" />';
            $html .= "</a>";
        }
        $html .= $arrows;
        $html .= "</div>";
    } else {
        $html .= "<div id='right'>&nbsp;</div>";
    }

    return $html;
 }

 /**
  * Render HTML Code to display top header of the Author tool (copy of renderCourseHeader function)
  * This function is called in /main/inc/tool_header.inc.php
  * @param string - title of current page
  * @return string - HTML code
  *
  */
 function display_author_header(){
   global $_course, $charset;

	if($GLOBALS['learner_view']){
		$param = "?learner_view=true";
	}
	else {
		$param = '';
	}
   // Check if the Lp object exists
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

		 // Get tocs from learnpath and convert for re-using in toggle menu
		 $currentId = $_SESSION['oLP']->current;

   // Get menu items
   $menuItems = getMenuItemsFromToc($_SESSION['oLP']->get_toc(), $currentId);
   $title = api_convert_encoding($_SESSION['oLP']->get_name(), $charset, api_get_system_encoding());
    $session_id = api_get_session_id();
    $session_name = "";
    if ($session_id > 0) {
    $session_info = array();
    $session_info = api_get_session_info($session_id);
    $session_name = ' ( ' . $session_info['name'] . ' )';
    }
  // Html header for the Author tool
    
        $course_session_name =  $_course['name'] . ' ' . $session_name;
        $course_session_name = api_convert_encoding($course_session_name, $charset, api_get_system_encoding());
        
        $html = '</div>';
        $html .= "<a id=\"back2home\" style=\"margin-left:8px;\" class='title-button' href='".api_get_path(WEB_COURSE_PATH).$_course['path'].'/index.php'.$param."'>";
 	$html .= '<img style="margin-left: 0px;" src="'.api_get_path(WEB_IMG_PATH).'spacer.gif" width="42" height="37" onclick="window.parent.API.save_asset();" alt="'.$altHome.'" title="'.$altHome.'" />';
 	$html .= '<div style="font-size: 16px; font-weight: bolder; height: 18px; margin-left: 55px; margin-top: 13px; overflow: hidden; width: 262px;">' . cut($course_session_name, 30, true) . '</div>';
        $html .= '</a>';
        $html .= '<div>';        
        if (isset($_GET['action']) && $_GET['action'] == 'view') {
            $html .= renderCourseToggleMenu($menuItems);
        }       	
	 $altHome = api_convert_encoding(get_lang('CourseHomepageLink'), $charset, api_get_system_encoding());

 	$arrows = "";	// no navigation buttons if only one page
  $lp_count = $_SESSION['oLP']->get_total_learning_path_count();
  if ($lp_count >= 1) {
    $lp_id = Security::remove_XSS($_GET['lp_id']);
    // Get the previous and next Lp ID
    $lp_info = $_SESSION['oLP']->get_previous_and_next_lp($lp_id);
    $lp_previous = $lp_info['previous']; // Previous Lp ID
    $lp_next = $lp_info['next']; // Next Lp ID

    $controller = api_get_path(WEB_PATH).'main/newscorm/lp_controller.php';
    // Arrows
    $arrows = '<a class="prev_button" href="'.$controller.'?'.api_get_cidreq().'&amp;action=add_item&amp;amp;type=step&amp;amp;lp_id='.$lp_previous.'">'.
    '<img id="coursepreviousbutton" src="../img/spacer.gif" class="button" title="'.get_lang('Previous').'" alt="'.get_lang('Previous').'"/></a>
        <img style="display:none;" id="coursepreviousbutton_na" src="../img/spacer.gif" class="button" title="'.get_lang('Previous').'" alt="'.get_lang('Previous').'"/>'.
    '<a class="next_button" href="'.$controller.'?'.api_get_cidreq().'&amp;action=add_item&amp;amp;type=step&amp;amp;lp_id='.$lp_next.'">'.
    '<img id="coursenextbutton" src="../img/spacer.gif" class="button" title="'.get_lang('Next').'" alt="'.get_lang('Next').'"/>'.
    '</a>
     <img style="display:none;" id="coursenextbutton_na" src="../img/spacer.gif" class="button" title="'.get_lang('Next').'" alt="'.get_lang('Next').'"/>';
  }
  //if($_REQUEST['action'] != "add_item" || $_REQUEST['type'] != "step")
  if($_REQUEST['action'] == 'view')
  {
 	$html.=	"<div id='right'>". // menu button + previous / next
	 			$arrows.
 			"</div>";
  }

 	return $html;
 }
/**
 * Render htmlcode to display simple header (with only home button)
 */
 function renderSimpleHeader(){

	if($GLOBALS['learner_view']){
		$param = "?learner_view=true";
	}
	else {
		$param = '';
	}
	$altHome = api_convert_encoding(get_lang('CourseHomepageLink'), $charset, api_get_system_encoding());
 	$html =	"<a class='course_main_home_button' href='".api_get_path(WEB_COURSE_PATH).$_course['path'].'/index.php'.$param."' target='_self' onclick='window.parent.API.save_asset();' alt='$altHome' title='$altHome'>".
 				"<img id='courseMainHomeButton' src='../img/tool_header_home.png' />" .
 			"</a>";
 	return $html;
 }

 /**
  * Display Menu toogle with lp items
  * @param  array   Lp items
  * @return void
  */
 function displayCourseToggleMenu($items) {

     $html .= '<style type="text/css">
                #courseToggleMenu {
                    left: 0px;
                    height: 745px;
                    overflow-y: scroll;
                    position: absolute;
                    top: 0px;
                    width: 209px !important;
                }
                #courseToggleMenu li, #courseToggleMenu li a {
                    min-height: 18px;
                }
                #content_with_secondary_actions {
                    overflow-x: hidden !important;
                    position:relative;
                    padding: 0px;
                    width: 100% !important;
                }
                div.jspContainer {
                    width:209px !important;
                }
                div.jspPane {
                    left: 0px;
                    top: 0px;
                }
               </style>';

     //hidden menu
    $html .= '<div id="courseToggleMenu" class="scroll-pane">';

    $ulStyle = (count($items) > $maxItemsWithoutScrollingBar) ? "scrollbar" : "";

    // TODO : JQuery scrollbar with sexy style to match screenshot of Françoise: http://www.kelvinluck.com/assets/jquery/jScrollPane/jScrollPane.html
    $html .= "<ul class='$ulStyle'>";
    $aCertificate = array();
    foreach($items as $item) {
            // jump certificate for listing it to the end
            if ($item['item_type'] == 'certificate') { $aCertificate = $item; }

            $text    = (array_key_exists('text', $item)) ? $item['text'] : "menu item";
            $onclick = (array_key_exists('onclick', $item)) ? $item['onclick'] : "javascript:return false;";
            $href    = (array_key_exists('href', $item)) ? $item['href'] : "#";
            $class   = (array_key_exists('class', $item)) ? $item['class'] : "";
            
            if (isset($_GET['action']) && $_GET['action']=="view") {// Allow display resource on click event
                $html.= "<li id=\"toggle_menu_item_".$item['id']."\" class='$class'>";
                if ($item['item_type'] != 'dokeos_chapter' && $item['item_type'] != 'dokeos_module' && $item['item_type'] != 'dir') {
                    $html.= "<a href='$href' onclick='$onclick'>" . $text . "</a>";
                } else {
                    $html.= "<a href='$href' onclick='$onclick'>" . get_lang("Chapter") . ': ' . $text . "</a>";
                }
            } else {// Allow display just informative items
               $html.= "<li  class='$class'>";
               $html.= "<span style='cursor:default;'>".$text."</span>";
            }
            $html.= "</li>";
    }

    $html .= "</ul>";
    $html .= "</div>";
    echo $html;
 }

 /**
  * Return HTML code for menu button with togggled menu
  * with new style of Dokeos 2.0
  * @param array $items - array of menu items (each item must contain keys: text, onclick, href  )
  * @param int $maxItemsWithoutScrollingBar - if less items that than int, no scrollbar appears
  * @return string HTML code
  * @since 2010.09.03
  */
 function renderCourseToggleMenu( $items = array(), $maxItemsWithoutScrollingBar = 15){
        global $charset;
 	if(empty($items) || !is_array($items))		return false;

 	// main button
 	$html .= '<a class ="course_menu_button" href="#" onclick="javascript:$(\'#courseToggleMenu\').toggle(\'slow\'); return false;">';
	$html .= '<img id="courseMenuButton" src="../img/spacer.gif" />';
	$html .= "</a>";

	//hidden menu
 	$html .= '<div id="courseToggleMenu" style="display:none;">';

 	$ulStyle = (count($items) > $maxItemsWithoutScrollingBar) ? "scrollbar" : "";

 	// TODO : JQuery scrollbar with sexy style to match screenshot of Françoise: http://www.kelvinluck.com/assets/jquery/jScrollPane/jScrollPane.html

 	$html .= "<ul class='$ulStyle'>";
        $aCertificate = array();
 	foreach($items as $item) {
                // jump certificate for listing it to the end
                if ($item['item_type'] == 'certificate') {
                    $aCertificate = $item;
                    //continue;
                }

		$text = 	(array_key_exists('text', $item))?$item['text']:"menu item";
		$onclick =	(array_key_exists('onclick', $item))?$item['onclick']:"javascript:return false;";
		$href =		(array_key_exists('href', $item))?$item['href']:"#";
		$class = 	(array_key_exists('class', $item))?$item['class']:"";
		if (isset($_GET['action']) && $_GET['action']=="view") {// Allow display resource on click event
			$html.= "<li id=\"toggle_menu_item_".$item['id']."\" class='$class'>";
			$html.= "<a href='$href' onclick='$onclick'>".api_convert_encoding($text, $charset, $_SESSION['oLP']->encoding)."</a>";
                } else {// Allow display just informative items
                   $html.= "<li  class='$class'>";
                   $html.= "<span style='cursor:default;'>".api_convert_encoding($text, $charset, $_SESSION['oLP']->encoding)."</span>";
                }
		$html.= "</li>";
	}

	$html .= "</ul>";
	$html .= "</div>";
	return  $html;
 }


 /**
  * Render previous + next buttons in header
  * @return string - HTML Code img buttons
  */
 function renderNavigationArrows(){
    $html = '<a class="prev_button" href="#" onclick="javascript:switch_item(3,\'previous\'); hideCourseMenu(); return false;">' .
                '<img id="coursepreviousbutton" src="../img/spacer.gif" class="button"/>' .
            '</a>
            <img style="display:none;" id="coursepreviousbutton_na" src="../img/spacer.gif" class="button" title="'.get_lang('Previous').'" alt="'.get_lang('Previous').'"/>' .
            '<a class="next_button" href="#" onclick="javascript:switch_item(3,\'next\'); hideCourseMenu(); return false;">' .
                '<img id="coursenextbutton" src="../img/spacer.gif" class="button"/>' .
            '</a>
            <img style="display:none;" id="coursenextbutton_na" src="../img/spacer.gif" class="button" title="'.get_lang('Next').'" alt="'.get_lang('Next').'"/>';

    return $html;
 }


 /**
  * Render top(default) or bottom toolbar
  */
 function renderToolbar($items = array(), $top = true){
 	$txt = ($top) ? 'top' : 'bottom';
 	$html = '';
 	$html.=	'<div id="' . $txt . '_toolbar" class="toolbar radiant rounded">';
 	if (!empty($items)) {
            foreach ($items as $i) {
                $html.= '<div class="float_l">';
                $html.= $i;
                $html.= '</div>';
            }
 	}
 	$html .= '</div>';
 	return $html;
 }

  /**
  * Render HTML Code to display progress bar
  * @return string HTML code
  */
 function renderProgressBar($val){
 	if(!is_array($val))		return "";

 	if($val[1] == "%") 		$width = $val[0].$val[1];																	// i.e 33%
 	else					$width = strval(intval((intval($val[0]) / intval(	substr($val[1],1)	)) *100))."%";		// i.e 10/33 => 30 %

 	$html = "<div id='progressBar'>";
 	$html .= "<div id='percent' style='width:$width'></div>";
 	$html .= "</div>";
        $html .= '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery.jscrollpane.js"></script>
                <script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery.mousewheel.js"></script>
                <script type="text/javascript">
                    $(function() {
                        $("div.scroll-pane").jScrollPane();
                    }); 
                 $(document).ready(function(){
                     $(".course_menu_button").toggle(function(){
                        toogleLpNavigation("hide");
                     },function(){
                        toogleLpNavigation("show");
                     });
                 });
                 function toogleLpNavigation(action) {
                    switch (action) {
                        case "show":
                            $("#lp-menu-right-collapsable").show();
                            var n_h = ($("#learning_path_right_zone").css("width") == "100%")?"78%":"100%";
                            $("#courseToggleMenu").animate({width:"26%"}, 500, "linear");
                            $("#learning_path_right_zone").animate({width:n_h},500, "linear");
                            $("#lp-menu-right-collapsable").attr("width", "26%");
                            break;
                        case "hide":
                            var n_h = ($("#learning_path_right_zone").css("width") == "78%")?"100%":"78%";
                            $("#courseToggleMenu").animate({width:"0%"}, 500, "linear");
                            $("#learning_path_right_zone").animate({width:n_h}, 500, "linear");
                            $("#lp-menu-right-collapsable").attr("width", "0%");
                            break;
                    }
                 }
               </script>';

 	return $html    ;
}