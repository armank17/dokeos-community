<?php
/* For licensing terms, see /dokeos_license.txt */

/**
 * Learning Path
 * @package dokeos.learnpath
 */

// Language files that should be included
//$language_file []= 'languagefile1';
//$language_file []= 'languagefile2';

// setting the help
$help_content = 'learningpath';

// including the global Dokeos file
require_once '../inc/global.inc.php';

// including additional libraries
require_once 'back_compat.inc.php';
require_once 'learnpathList.class.php';
require_once 'learnpath.class.php';
require_once 'learnpathItem.class.php';
require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';
require_once api_get_path(LIBRARY_PATH).'certificatemanager.lib.php';

// setting the tabs
$this_section=SECTION_COURSES;
// variable initialisation
$is_allowed_to_edit = api_is_allowed_to_edit(null,true);
// Add additional javascript, css
if ($is_allowed_to_edit) {
$htmlHeadXtra[] =
"<script language='javascript' type='text/javascript'>
	function confirmation(name)
	{
		if (confirm(\" ".trim(get_lang('AreYouSureToDelete'))." \"+name+\"?\"))		return true;
		else																		return false;
	}
</script>";

$htmlHeadXtra[] = '
  <script type="text/javascript">
    /*<![CDATA[*/
        $(function(){
         $("<div id=\'hdnTypeSort\'><input type=\'hidden\' name=\'hdnType\' value=\'CourseModuleSortable\' \/><\/div>").insertBefore("body");
         $( "#GalleryContainer" ).sortable({
            connectWith: "#GalleryContainer",
            stop: function(event) {
               $this=$(event.target);
               $("input[name=\'hdnItemOrder[]\']").each(function(i) {
                 $("input[name=\'hdnItemOrder[]\']").eq(i).val(i+1);
               });
               var query = $("#hdnTypeSort input").add($this.find("input[name=\'hdnItemId[]\']")).add($this.find("input[name=\'hdnItemOrder[]\']")).serialize();
               $.ajax({
                  type: "POST",
                  url: "lp_ajax_order_items_scenario.php",
                  data: query,
                  success: function(msg){}
              })
            }
       });
       $( ".imageBox" ).addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" );
       $( "#GalleryContainer" ).disableSelection();

      });
      /*]]>*/
  </script>
';

$htmlHeadXtra[] = '
    <style type="text/css">
        .ui-sortable-placeholder { border: 1px dotted black; visibility: visible !important; background: transparent !important; }
    </style>
';
//$htmlHeadXtra[] = '<script src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.ui.all.js" type="text/javascript" language="javascript"></script>';
}
// color box library
$htmlHeadXtra[] = '<link type="text/css" rel="stylesheet" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/colorbox/colorbox.css" />';
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/colorbox/jquery.colorbox.js" language="javascript"></script>';


//Setting imagebox width, height dynamically.
//if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'course') {
    $imgbox_width = "212px";
    $imgbox_height = "165px";
    $imgboximg_width = "200px";
    $imgboximg_height = "125px";
    $marker_height = "135px";

$htmlHeadXtra[] = '<style type="text/css">
	.imageBox,.imageBoxHighlighted{
		width:'.$imgbox_width.';	/* Total width of each image box */
		height:'.$imgbox_height.';	/* Total height of each image box */
		float:left;
	}
	.imageBox_theImage{
		width:'.$imgboximg_width.';	/* Width of image */
		height:'.$imgboximg_height.' !important;	/* Height of image */

		/*
		Dont change these values *
		*/
		background-position: center center;
		background-repeat: no-repeat;
		margin: 0 auto;
		margin-bottom:2px;
		text-align:center;
                border:2px solid #cccccc !important;
                 
                margin:5px; 
                background:#f6f6f6;
	}

 
	.imageBox .imageBox_theImage{
		border:0px solid #DDD;	/* Border color for not selected images */
		padding:2px;
        height:auto;
	}
	.imageBoxHighlighted .imageBox_theImage{
		border:3px;	/* Border color for selected image */
		padding:0px;

	}
	.imageBoxHighlighted span{	/* Title of selected image */
		color:green;
		padding:2px;
		text-align:center;
	}

	.imageBox_label{	/* Title of images - both selected and not selected */
		text-align:center;
		font-family: verdana;
		font-size:11px;
		padding-top:2px;
		margin: 0 auto;
	}

	/*
	DIV that indicates where the dragged image will be placed
	*/
	#insertionMarker{
		height:'.$marker_height.';
		width:6px;
		position:absolute;
		display:none;
		float:left;
	}

	#insertionMarkerLine{
		width:6px;	/* No need to change this value */
		height:'.$marker_height.';	/* To adjust the height of the div that indicates where the dragged image will be dropped */
	}

	#insertionMarker img{
		float:left;
	}

	/*
	DIV that shows the image as you drag it
	*/
	#dragDropContent{

		opacity:0.4;	/* 40 % opacity */
		filter:alpha(opacity=40);	/* 40 % opacity */

		/*
		No need to change these three values
		*/
		position:absolute;
		z-index:10;
		display:none;

	}
	#blanket {
	    background-color:#111111;
		opacity: 0.30;
		filter:alpha(opacity=30);
		position:absolute;
		z-index: 9001;
		top:0px;
		left:0px;
		width:100%;
	}
	.lpadd_actions {
		-moz-border-radius:5px;
		-webkit-border-radius: 5px;
		border-radius: 5px;
		/* Mozilla: */
		background:-moz-linear-gradient(center top , #FFFFE6, #FFFFFF) repeat scroll 0 0 transparent;
		/* Chrome, Safari:*/
		background: -webkit-gradient(linear,left top, left bottom, from(#FFFFE6), to(#ffffff));
		/* MSIE */
		filter: progid:DXImageTransform.Microsoft.Gradient(
					StartColorStr="#FFFFE6", EndColorStr="#ffffff", GradientType=0);
		border:1px solid #CCCCCC;
		padding: 10px;
		margin-bottom: 5px;
		vertical-align:middle;
	}
        
        .imageBox_theImage {
                cursor:auto !important;
        }

	</style>';




// Unregister the session if it exists
if(isset($_SESSION['lpobject'])) {
  $oLP = unserialize($_SESSION['lpobject']);
    if(is_object($oLP)){
      api_session_unregister('oLP');
      api_session_unregister('lpobject');
    }
  } elseif (is_null($_SESSION['lpobject']) && isset($_SESSION['oLP'])) {
    api_session_unregister('oLP');
 }

// setting the breadcrumbs
$interbreadcrumb[] = array ("url"=>"overview.php", "name"=> get_lang('OverviewOfAllCodeTemplates'));
$interbreadcrumb[] = array ("url"=>"coursetool.php", "name"=> get_lang('CourseTool'));

// Display the header
Display::display_tool_header(get_lang('CourseTool'));
$is_allowed_to_edit = api_is_allowed_to_edit(null,true);

/*------------------------------*/

if(empty($lp_controller_touched) || $lp_controller_touched!=1){
	header('location: lp_controller.php?action=list');
}

$courseDir   = api_get_course_path().'/scorm';
$baseWordDir = $courseDir;
$display_progress_bar = true;

/**
 * Display initialisation and security checks
 */
$nameTools = get_lang(ucfirst(TOOL_LEARNPATH));
event_access_tool(TOOL_LEARNPATH);

if (! $is_allowed_in_course) api_not_allowed();

/**
 * Display
 */
/* Require the search widget and prepare the header with its stuff */
if (api_get_setting('search_enabled') == 'true') {
	require api_get_path(LIBRARY_PATH).'search/search_widget.php';
	search_widget_prepare($htmlHeadXtra);
}

/*
-----------------------------------------------------------
	Introduction section
	(editable by course admins)
-----------------------------------------------------------
*/
Display::display_introduction_section(TOOL_LEARNPATH, array(
		'CreateDocumentWebDir' => api_get_path('WEB_COURSE_PATH').api_get_course_path().'/document/',
		'CreateDocumentDir' => '../../courses/'.api_get_course_path().'/document/',
		'BaseHref' => api_get_path('WEB_COURSE_PATH').api_get_course_path().'/'
	)
);


$current_session = api_get_session_id();
$drag_style = "cursor:default";
if($is_allowed_to_edit) {
 $drag_style = "";
	echo '<script type="text/javascript">
    /*<![CDATA[*/
		function dragDropEnd(ev)
		{
		readyToMove = false;
		moveTimer = -1;

		var orderString = "";
			var objects = document.getElementsByTagName(\'div\');

		for(var no=0;no<objects.length;no++){
			if(objects[no].className==\'imageBox\' || objects[no].className==\'imageBoxHighlighted\'){
				if(objects[no].id != "foo" && objects[no].parentNode.id != "dragDropContent"){ // Check if its not the fake image, or the drag&drop box
					if(orderString.length>0){
						orderString = orderString + \',\';
						}
					orderString = orderString + objects[no].id;
					}
				}
			}

		dragDropDiv.style.display=\'none\';
		insertionMarker.style.display=\'none\';

		if(destinationObject && destinationObject!=activeImage){
			var parentObj = destinationObject.parentNode;
			parentObj.insertBefore(activeImage,destinationObject);
			activeImage.className=\'imageBox\';
			activeImage = false;
			destinationObject=false;
			getDivCoordinates();
		}
		savelporder(orderString);
}

function savelporder(str)
	{
			var orderString = "";
			var objects = document.getElementsByTagName(\'div\');

			for(var no=0;no<objects.length;no++){
				if(objects[no].className==\'imageBox\' || objects[no].className==\'imageBoxHighlighted\'){
					if(objects[no].id != "foo" && objects[no].parentNode.id != "dragDropContent"){ // Check if its not the fake image, or the drag&drop box
						if(orderString.length>0){
							orderString = orderString + \',\';
							}
						orderString = orderString + objects[no].id;
						}
					}
				}
		if(str != orderString)
		{
		  window.location.href="lp_controller.php?'.api_get_cidReq().'&action=course&dispaction=sortlp&order="+orderString;
		}
}
/*]]>*/
				</script>';

  /*--------------------------------------
    DIALOG BOX SECTION
    --------------------------------------*/

  if (!empty($dialog_box))
  {
	  switch ($_GET['dialogtype'])
	  {
	  	case 'confirmation':	Display::display_confirmation_message($dialog_box);		break;
	  	case 'error':			Display::display_error_message($dialog_box);			break;
	  	case 'warning':			Display::display_warning_message($dialog_box);			break;
	  	default:	    		Display::display_normal_message($dialog_box);			break;
	  }
  }

	if (api_failure::get_last_failure())	    Display::display_normal_message(api_failure::get_last_failure());

	echo '<div class="actions">';
            //echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'newscorm/lp_controller.php?' . api_get_cidReq() . '&action=add_lp">' . Display::return_icon('pixel.gif', get_lang('New'), array('class' => 'toolactionplaceholdericon toolactionnew')) . get_lang("NewModule") . '</a>';
            echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'upload/index.php?' . api_get_cidReq() . '&curdirpath=/&tool=learnpath">' . Display::return_icon('pixel.gif', get_lang('Scorm'), array('class' => 'toolactionplaceholdericon toolactionscorm')) . get_lang("ImportScorm") . '</a>';
	echo '</div>';
}

/*---------------------------------------------------------------------------------------------------------------------------------*/
?>
<div id="content">
	<?php
                unset($_SESSION['progressBar']);
                unset($_SESSION['count']);
		$list = new LearnpathList(api_get_user_id());
		$flat_list = $list->get_flat_list();
		$visible_items_counter = 0;
		if (is_array($flat_list) && !empty($flat_list))
		{
			echo '<div id="GalleryContainer">';
                        $i = 0;
			foreach ($flat_list as $id => $details)
			{
				
				if($details['lp_visibility']=="1")
					$visible_items_counter++;
				
				if (intval($details['lp_visibility']) == 0) { continue; }
				$name = Security::remove_XSS($details['lp_name']);

                                $course_info = api_get_course_info();

                                $user_id = ($course_info['subscribe_allowed'] == 1 AND api_get_user_id() == '2')? uniqid() :  api_get_user_id();
				$progress_bar = learnpath::get_db_progress($id,$user_id);

				if(strlen($name) > 75)
				{
				$display_name = cut($name, 75, true);
				}
				else
				{
				$display_name = $name;
				}
 				$html = "<div class=\"border\" style='width:99%;height:18px;'><div class=\"progressbar\" style='width:$progress_bar;height:20px;'></div></div>";
				echo '<div class="imageBox" id="imageBox'.$id.'" style="position:relative;">';

                                /*$obj_certificate = new CertificateManager();
                                $certif_available = $obj_certificate->isUserAllowedGetCertificate(api_get_user_id(), 'module', $id, api_get_course_id());
                                if ($certif_available) {
                                    echo '<a class="certificate-'.$id.'-link" href="#">'.Display::return_icon('certificate48x48.png', get_lang('GetCertificate'), array('style'=>'position:absolute;top:10px;right:12px;')).'</a>';
                                    $obj_certificate->displayCertificate('html', 'module', $id, api_get_course_id(), null, true);
                                }*/
                                
                                $link = 'lp_controller.php?'.api_get_cidReq().'&action=view&lp_id='.$id;
                                if ($details['lp_type'] == 1 && api_get_setting('enable_author_tool') === 'true' && $details['origin_tool'] != 'module') {
                                    $link = api_get_path(WEB_CODE_PATH).'index.php?module=author&cmd=Player&func=view&'.api_get_cidReq().'&lpId='.$id;
                                }                              
                                echo '<div class="GalleryContainer" style="'.$drag_style.'"><div class="imageBox_theImage">';
                                //echo '<a href="'.$link.'">';
                                echo '<input type="hidden" name="hdnItemId[]" value="'.$id.'" />
                                      <input type="hidden" name="hdnItemOrder[]" value="'.($i+1).'" />';
                                echo '<table width="100%">';
				echo '<tr style="height:50px;"><td colspan="2" align="center"><a href="'.$link.'">'.$display_name.'</a></td></tr>';
				echo '<tr><td>&nbsp;</td></tr></table><table width="100%">';
				echo '<tr><td width="80%" valign="top">'.$html.'</td><td align="center">'.Display::return_icon('pixel.gif', get_lang('ViewRight'), array('class' => 'actionplaceholdericon actionviewmodule')).'</td></tr>';
				echo '</table>';
                                //echo '</a>';
				echo '</div></div>';
				if (api_is_allowed_to_edit()) {
				  echo '<div align="center"><a href="lp_controller.php?'.api_get_cidReq().'&action=add_item&type=step&lp_id='.$id.'">'.Display::return_icon('pixel.gif', get_lang('Edit'), array('class' => 'actionplaceholdericon actionedit')).'</a></div>';
				}
				echo '</div>';
                                $i++;
			}
			echo '</div>
		<div id="insertionMarker">
		<img src="../img/marker_top.gif" alt="&nbsp;" />
		<img src="../img/marker_middle.gif" alt="&nbsp;" id="insertionMarkerLine" />
		<img src="../img/marker_bottom.gif" alt="&nbsp;" />
		</div>
		<div id="dragDropContent">
		</div><div id="debug" style="clear:both">
		</div>';
		} else {
		//	echo '<div align="center"><a href="lp_controller.php?' . api_get_cidreq().'">'.get_lang('NoCourse').'</a></div>';
		}
		

		
		if($visible_items_counter==0){
			echo '<div align="center" style="min-height: inherit; "><span style="position:absolute;top:50%;left:44%;">'.get_lang('NoCourse').'</span></div>';
			//echo '<div align="center" style="min-height: inherit; "><a style="position:absolute;top:50%;left:44%;" href="lp_controller.php?' . api_get_cidreq().'">'.get_lang('NoCourse').'</a></div>';
		}
	?>
	<!-- list of courses -->
</div><!--end of div#content-->
<?php
// display the footer
Display::display_footer();
