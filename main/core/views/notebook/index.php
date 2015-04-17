<?php
/* For licensing terms, see /license.txt */

// name of the language file that needs to be included
$language_file = array('notebook');

// including the global dokeos file
require_once '../../../inc/global.inc.php';

require_once api_get_path(SYS_MODEL_PATH).'notebook/NotebookModel.php';
require_once api_get_path(SYS_CONTROLLER_PATH).'notebook/NotebookController.php';
require_once api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php';

// additional javascript
$htmlHeadXtra[] = '
    <style type="text/css">
	form {
		border:0px;
	}
	div.row div.label{
		width: 10%;
	}
	div.row div.formw{
		width: 98%;
	}
    </style>
';
//$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/booklet/jquery.booklet.latest.min.js"></script>';
//$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/booklet/jquery.easing.1.3.js"></script>';
//$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/booklet/jquery-ui-1.10.1.custom.min.js"></script>';
//$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/booklet/jquery-1.4.4.min.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/booklet/jquery.booklet.1.1.0.min.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/booklet/jquery.easing.1.3.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.validate.js"></script>';

//$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/booklet/jquery.booklet.latest.js"></script>';

$htmlHeadXtra[] = '<link type="text/css" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/booklet/jquery.booklet.1.1.0.css" rel="stylesheet" media="screen, projection, tv" />';
$htmlHeadXtra[] = '<link type="text/css" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/booklet/css/style.css" rel="stylesheet" media="screen, projection, tv" />';
$htmlHeadXtra[] = '
    <style type="text/css">
        input.error { border: 1px solid red; }
        label.error { color: red; }
    </style>';
// get actions
$actions = array('listing', 'add', 'edit', 'delete','show', 'view', 'search', 'showSearch');
$action = 'listing';
if (isset($_GET['action']) && in_array($_GET['action'],$actions)) {
	$action = $_GET['action'];
}

// set notebook id
$notebookId = isset($_GET['id']) && is_numeric($_GET['id'])?intval($_GET['id']):null;

// notebook controller object
$notebookController = new NotebookController($notebookId);

// distpacher actions to controller
switch ($action) {
	case 'listing':
                $notebookController->listing();
                break;
	case 'add':
		$notebookController->add();
		break;
	case 'edit':
		$notebookController->edit();
		break;
	case 'show':
		$notebookController->show();
		break;
	case 'delete':
		$notebookController->destroy();
		break;
        case 'view':
		$notebookController->view();
		break;
        case 'search':
		$notebookController->search();
		break;
        case 'showSearch':
		$notebookController->showSearch();
		break;
	default:
		$notebookController->listing();
}

?>
<script type="text/javascript">
			$(function() {
				var $BookView 		= $('#BookView');
				var $bttn_next		= $('#next_page_button');
				var $bttn_prev		= $('#prev_page_button');
				var $loading		= $('#loading');
				var $mybook_images	= $BookView.find('img');
				var cnt_images		= $mybook_images.length;
				var loaded			= 0;
				//preload all the images in the book,
				//and then call the booklet plugin

				$mybook_images.each(function(){
					var $img 	= $(this);
					var source	= $img.attr('src');
					$('<img/>').load(function(){
						++loaded;
						if(loaded == cnt_images){
							$loading.hide();
							$bttn_next.show();
							$bttn_prev.show();
							$BookView.show().booklet({
								name:               null,                            // name of the booklet to display in the document title bar
								width:              800,                             // container width
								height:             500,                             // container height
								speed:              600,                             // speed of the transition between pages
								direction:          'LTR',                           // direction of the overall content organization, default LTR, left to right, can be RTL for languages which read right to left
								startingPage:       0,                               // index of the first page to be displayed
								easing:             'easeInOutQuad',                 // easing method for complete transition
								easeIn:             'easeInQuad',                    // easing method for first half of transition
								easeOut:            'easeOutQuad',                   // easing method for second half of transition

								closed:             true,                           // start with the book "closed", will add empty pages to beginning and end of book
								closedFrontTitle:   null,                            // used with "closed", "menu" and "pageSelector", determines title of blank starting page
								closedFrontChapter: null,                            // used with "closed", "menu" and "chapterSelector", determines chapter name of blank starting page
								closedBackTitle:    null,                            // used with "closed", "menu" and "pageSelector", determines chapter name of blank ending page
								closedBackChapter:  null,                            // used with "closed", "menu" and "chapterSelector", determines chapter name of blank ending page
								covers:             false,                           // used with  "closed", makes first and last pages into covers, without page numbers (if enabled)

								pagePadding:        10,                              // padding for each page wrapper
								pageNumbers:        true,                            // display page numbers on each page

								hovers:             false,                            // enables preview pageturn hover animation, shows a small preview of previous or next page on hover
								overlays:           false,                            // enables navigation using a page sized overlay, when enabled links inside the content will not be clickable
								tabs:               false,                           // adds tabs along the top of the pages
								tabWidth:           60,                              // set the width of the tabs
								tabHeight:          20,                              // set the height of the tabs
								arrows:             false,                           // adds arrows overlayed over the book edges
								cursor:             'pointer',                       // cursor css setting for side bar areas

								hash:               false,                           // enables navigation using a hash string, ex: #/page/1 for page 1, will affect all booklets with 'hash' enabled
								keyboard:           true,                            // enables navigation with arrow keys (left: previous, right: next)
								next:               $bttn_next,          			// selector for element to use as click trigger for next page
								prev:               $bttn_prev,          			// selector for element to use as click trigger for previous page

								menu:               null,                            // selector for element to use as the menu area, required for 'pageSelector'
								pageSelector:       false,                           // enables navigation with a dropdown menu of pages, requires 'menu'
								chapterSelector:    false,                           // enables navigation with a dropdown menu of chapters, determined by the "rel" attribute, requires 'menu'

								shadows:            true,                            // display shadows on page animations
								shadowTopFwdWidth:  166,                             // shadow width for top forward anim
								shadowTopBackWidth: 166,                             // shadow width for top back anim
								shadowBtmWidth:     50,                              // shadow width for bottom shadow

								before:             function(){},                    // callback invoked before each page turn animation
								after:              function(){}                     // callback invoked after each page turn animation
							});
							Cufon.refresh();
						}
					}).attr('src',source);
				});
				
			});
                        

            $(document).ready(function(){
                if ($("#note").length > 0) {
                    $("#note").validate({
                        rules: {
                            title: {
                                required: true
                            }
                        },
                        messages: {
                            title: {
                                required : "<img src='<?php echo api_get_path(WEB_IMG_PATH); ?>exclamation.png' title='<?php echo ucfirst(get_lang('ThisFieldIsRequired')); ?>' /> <?php echo ucfirst(get_lang('ThisFieldIsRequired')); ?>"
                            }
                        }
                    });
                }
//                $("#BookView").booklet({
//                    arrows: true,
//                    width:  750,
//                    height: 500,
//                    manual: false
//                });
            });

            function runDialog(){
            $("#dialog").dialog({
                modal: true,
                width: 350,
                height: 170
                });
            }
        </script>

