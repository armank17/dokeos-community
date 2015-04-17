<?php
/**
  ==============================================================================
 * 	This script displays the Dokeos header up to the </head> tag
 *   IT IS A COPY OF header.inc.php EXCEPT that it doesn't start the body
 *   output.
 *
 * 	@package dokeos.include
  ==============================================================================
 */
/* ----------------------------------------
  HEADERS SECTION
  -------------------------------------- */

/*
 * HTTP HEADER
 */
require_once(api_get_path(SYS_CODE_PATH) . 'inc/lib/banner.lib.php');
if ($_SESSION['viewasstudent'] == "YES") {
    $GLOBALS['learner_view'] = true;
} else {
    $GLOBALS['learner_view'] = false;
}

if (isset($_REQUEST['isStudentView']) && $_REQUEST['isStudentView'] == true) {
    $GLOBALS['learner_view'] = true;
    $_SESSION['studentview'] = "teacherview";
}
// Load header file if there is no course ID
if ($_cid == -1) { // In the future we should have only ONE header file
    require_once api_get_path(INCLUDE_PATH) . 'header.inc.php';
} else {
    //Give a default value to $charset. Should change to UTF-8 some time in the future.
    //This parameter should be set in the platform configuration interface in time.
    if (empty($charset)) {
        $charset = 'UTF-8';
    }

    //header('Content-Type: text/html; charset='. $charset)
    //	or die ("WARNING : it remains some characters before &lt;?php bracket or after ?&gt end");
    header('Content-Type: text/html; charset=' . $charset);
    header('X-Powered-By: Dokeos');
    if (isset($httpHeadXtra) && $httpHeadXtra) {
        foreach ($httpHeadXtra as $thisHttpHead) {
            header($thisHttpHead);
        }
    }

    // Get language iso-code for this page - ignore errors
    // The error ignorance is due to the non compatibility of function_exists()
    // with the object syntax of Database::get_language_isocode()
    @$document_language = Database::get_language_isocode($language_interface);
    if (empty($document_language)) {
        //if there was no valid iso-code, use the english one
        $document_language = 'en';
    }
    /*
     * HTML HEADER
     */
    ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $document_language; ?>" lang="<?php echo $document_language; ?>">
        <head>
            <meta charset="utf-8"/>
            <title>
            <?php
                $nameTools = api_convert_encoding($nameTools, $charset, api_get_system_encoding());                            
                echo (!empty($nameTools)) ?  ($nameTools . ' - ') : ('');                
                $_course_official_code = api_convert_encoding($_course['official_code'], $charset, api_get_system_encoding());
                $_course_name = api_convert_encoding($_course['name'], $charset, api_get_system_encoding());                
                echo (!empty($_course_official_code)) ?  ($_course_name . ' - ') : ('');
                echo $_course_official_code;
            ?>
            </title>

            <?php
            /*
                * Choose CSS style platform's, user's, course's, or Learning path CSS
                */

            $platform_theme = api_get_setting('stylesheets');  // plataform's css
            $my_style = $platform_theme;
            if (api_get_setting('user_selected_theme') == 'true') {
                $useri = api_get_user_info();
                $user_theme = $useri['theme'];
                if (!empty($user_theme) && $user_theme != $my_style) {
                    $my_style = $user_theme;     // user's css
                }
            }
            $mycourseid = api_get_course_id();

            if (!empty($mycourseid) && $mycourseid != -1) {
                if (api_get_setting('allow_course_theme') == 'true') {
                    $mycoursetheme = api_get_course_setting('course_theme', null, true);
                    if (!empty($mycoursetheme) && $mycoursetheme != -1) {
                        if (!empty($mycoursetheme) && $mycoursetheme != $my_style) {
                            $my_style = $mycoursetheme;  // course's css
                        }
                    }

                    $mycourselptheme = api_get_course_setting('allow_learning_path_theme');
                    if (!empty($mycourselptheme) && $mycourselptheme != -1 && $mycourselptheme == 1) {
                        global $lp_theme_css; //  it comes from the lp_controller.php
                        global $lp_theme_config; // it comes from the lp_controller.php
                        if (!empty($lp_theme_css)) {
                            $theme = $lp_theme_css;
                            if (!empty($theme) && $theme != $my_style) {
                                $my_style = $theme;  // LP's css
                            }
                        }
                    }
                }
            }

            if (!empty($lp_theme_log)) {
                $my_style = $platform_theme;
            }

            // A lot of portals are using old themes that doesn't exists anymore, this change should be done in the migration file
            $theme_exists = true;
            if (!file_exists(api_get_path(SYS_CODE_PATH) . 'css/' . $my_style . '/default.css')) {
                $theme_exists = false;
            }
            if (empty($my_style) || $theme_exists === false) {// If course theme in 1.8 platform doesn't exists then we are loading the platform theme
                $my_style = $platform_theme;
            }
            if ($my_style != "dokeos2_blue_tablet" && $my_style != "dokeos2_orange_tablet") {
              $my_style = str_replace(array("dokeos2_blue","dokeos2_orange"),array("dokeos2_blue_tablet","dokeos2_orange_tablet"),$my_style);
            }
            if ($my_style != '') {?>
                <style type="text/css" media="screen, projection">
                    /*<![CDATA[*/
                    @import "<?php echo api_get_path(WEB_CODE_PATH); ?>css/<?php echo $my_style; ?>/default.css";
                    /*]]>*/
                </style><?php
            }
            ?>
            <link rel="stylesheet" href="<?php echo api_get_path(WEB_CODE_PATH); ?>css/jquery.tagit.min.css" />
            <link rel="stylesheet" href="<?php echo api_get_path(WEB_CODE_PATH); ?>css/jquery.jscrollpane.css" />
            <!--[if IE 7]>
            <link rel="stylesheet" href="<?php echo api_get_path(WEB_CODE_PATH);?>css/<?php echo $my_style; ?>/ie7-cleanup.css" type="text/css" />
            <![endif]-->
            <!--[if IE 8]>
            <link rel="stylesheet" href="<?php echo api_get_path(WEB_CODE_PATH);?>css/<?php echo $my_style; ?>/ie8-cleanup.css" type="text/css" />
            <![endif]-->
            <link rel="courses" href="<?php echo api_get_path(WEB_CODE_PATH) ?>auth/courses.php" title="<?php echo api_htmlentities(get_lang('OtherCourses'), ENT_QUOTES, $charset); ?>" />
            <link rel="profil" href="<?php echo api_get_path(WEB_CODE_PATH) ?>auth/profile.php" title="<?php echo api_htmlentities(get_lang('ModifyProfile'), ENT_QUOTES, $charset); ?>" />
            <link href="http://www.dokeos.com/documentation.php" rel="Help" />
            <link href="http://www.dokeos.com" rel="Copyright" />
            <link rel="shortcut icon" href="<?php echo api_get_path(WEB_PATH); ?>favicon.ico" type="image/x-icon" />
            <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset ?>" />
            <meta name="Generator" content="Dokeos"/>
            <meta name="keywords" content="E-learning,open source,opensource,learning,training,free software,lms,authoring,rapid learning,screencasting,quiz,html5,flash,serious games,reporting,comparison,portal,php,mysql,hr,competence,scorm,community" />
            <script language="javascript" src="<?php echo api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-1.4.2.min.js'; ?>" type="text/javascript"></script>
            
            <script type="text/javascript">
                //<![CDATA[
                // This is a patch for the "__flash__removeCallback" bug, see FS#4378.
                if ( ( navigator.userAgent.toLowerCase().indexOf('msie') != -1 ) && ( navigator.userAgent.toLowerCase().indexOf( 'opera' ) == -1 ) )
                {
                    window.attachEvent( 'onunload', function()
                    {
                        window['__flash__removeCallback'] = function ( instance, name )
                        {
                            try
                            {
                                if ( instance )
                                {
                                    instance[name] = null ;
                                }
                            }
                            catch ( flashEx )
                            {

                            }
                        } ;
                    }
                ) ;
                }
                //]]>
            </script><?php
            if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) {
                $htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-ui/js/jquery-ui-ie7-1.8.1.js"></script>';
                $htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-ui/js/jquery-ui-1.8.1.custom.min.js"></script>';
            } else {
                $htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-ui/js/jquery-ui-1.8.1.custom.min.js"></script>';
            }
            $htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path ( WEB_CODE_PATH ) . 'appcore/library/jquery/jquery.timepicker/jquery-ui-timepicker-addon.js" language="javascript"></script>';
//            $htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path ( WEB_CODE_PATH ) . 'appcore/library/jquery/jquery.timepicker/jquery.ui.datepicker-'.api_get_language_isocode().'.js" language="javascript"></script>';
            $htmlHeadXtra[] = '<link type="text/css" href="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-ui/css/ui-lightness/jquery-ui-1.8.1.custom.css" rel="stylesheet" />';
            $htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery.tag-it.min.js"></script>';
            $htmlHeadXtra[] = '<link type="text/css" href="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery.tagit.min.css" rel="stylesheet" />';             
            $htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'jwplayer/jwplayer.js"></script>';
            
            $htmlHeadXtra[] = '<link rel="stylesheet" type="text/css" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.qtip/jquery.qtip.min.css" />';
            $htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.qtip/jquery.qtip.min.js"></script>';
            
            $htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/dokeos.js.php" language="javascript"></script>';
            
            $htmlHeadXtra[] = '<script type="text/javascript">
                                $(document).ready(function (){
                                 try {
                                        var a = $.ui.mouse.prototype._mouseMove;
                                        $.ui.mouse.prototype._mouseMove = function (b) {
                                        b.button = 1; a.apply(this, [b]);
                                        }
                                    }catch(e) {}
                                });
                            </script>';


                $htmlHeadXtra[] = '<script type="text/javascript">
                                $(document).ready(function (){
                                    if ($(".tag-it").length > 0) {
                                        $(".tag-it").tagit({
                                            removeConfirmation: true,
                                            allowSpaces: true
                                        });
                                    }
                                });
                            </script>';
            global $_configuration;
            if ($_configuration['multiple_access_urls'] == true) {
                $current_url_path = substr(api_get_path(WEB_PATH), 0, -1);
                $main_url_path = $_configuration['root_web'];
                $htmlHeadXtra[] = '<script type="text/javascript">
                                $(document).ready(function (){
                                $("a[href]").attr("href", function(index, href) {
                                var href_info = new Array();
                                var n = href.indexOf("/main/");

                                if (n != -1) {
                                    href_info = href.split("/main/");
                                    hardcoded_url = href_info[0];
                                    if(new RegExp("[a-zA-Z0-9]+://([a-zA-Z0-9_]+:[a-zA-Z0-9_]+@)?([a-zA-Z0-9.-]+\\.[A-Za-z]{2,4})(:[0-9]+)?(/.*)?").test(hardcoded_url)) {
                                        href = href.replace(hardcoded_url,"' . $current_url_path . '");
                                    }
                                    }
                                    return href;
                                });
                                });
                            </script>';
                $current_url_path = api_get_path(WEB_PATH);
                $htmlHeadXtra[] = '<script type="text/javascript">
                                    $(document).ready(function (){
                                    $("img[src]").attr("src", function(index, src) {
                                        src = src.replace("' . $main_url_path . '","' . $current_url_path . '");
                                        return src;
                                    });
                                    });
                                </script>';
            }
            $device_info = api_get_navigator();
            $device = $device_info['device'];
            $get_machine = $device['machine'];

            
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.carouFredSel-6.2.1-packed.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/helper-plugins/jquery.mousewheel.min.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/helper-plugins/jquery.touchSwipe.min.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/helper-plugins/jquery.transit.min.js"></script>';
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/helper-plugins/jquery.ba-throttle-debounce.min.js"></script>';
$htmlHeadXtra[] = "
<script>
			$(function() {
                        if($('#foo2').length>0){
				$('#foo2').carouFredSel({
					auto: false,
                                        circular: false,
                                        infinite: false,
					prev: '#prev2',
					next: '#next2',
					pagination: \"#pager2\",
					mousewheel: true,
					swipe: {
						//onMouse: true,
						onTouch: true
					}
				});
                         }
                                

                        if($('#foo3').length>0){
                                $('#foo3').carouFredSel({
					auto: false,
                                        circular: false,
                                        infinite: false,
					prev: '#prev3',
					next: '#next3',
					pagination: \"#pager3\",
					mousewheel: true,
					swipe: {
						//onMouse: true,
						onTouch: true
					}
				});
                        }
                        

			});
</script>";
            
            
            
            if ($get_machine == 'ipad' || $get_machine == 'android' || $get_machine == 'iphone') {
                $htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery.ui.touch-punch.min.js"></script>';
            }
            // Display the chat notification
            //require_once api_get_path(LIBRARY_PATH).'message.lib.php';
            //MessageManager::display_chat_notifications();
            // Display all $htmlHeadXtra
            if (isset($htmlHeadXtra) && $htmlHeadXtra) {
                foreach ($htmlHeadXtra as $this_html_head) {
                    echo($this_html_head);
                }
            }


            // Check if we have a CSS with tablet support
            $css_name = api_get_setting('stylesheets');
            // Check if we have a CSS with tablet support
            $css_info = array();
            if (isset($GLOBALS['_cid']) && $GLOBALS['_cid'] != -1) {
                // if We are inside a course
                $css_name = api_get_setting('allow_course_theme') == 'true' ? (api_get_course_setting('course_theme', null, true) ? api_get_course_setting('course_theme', null, true) : api_get_setting('stylesheets')) : api_get_setting('stylesheets');
                $css_name = str_replace(array("dokeos2_blue","dokeos2_orange"),array("dokeos2_blue_tablet","dokeos2_orange_tablet"),$css_name);
                $css_info = api_get_css_info($css_name);
            } else {
                $css_info = api_get_css_info();
            }
            $css_type = !is_null($css_info['type']) ? $css_info['type'] : 'tablet';

            if ($css_type == 'tablet') {

            } else {?>
                <script type="text/javascript">
                    $(function(){
                        if(navigator.platform == 'iPad' || navigator.platform == 'iPhone' || navigator.platform == 'iPod'){
                            function footerStaticDinamic(){
                                $("#footer").css({"left":"0","bottom":"0"});
                            }

                            footerStaticDinamic();
                            $(window).scroll(function(){
                                footerStaticDinamic();
                            });

                        } else {
                            function footerStatic(){
                                $("#footer").css({"left":"0","bottom":"0"});
                            }
                            footerStatic();
                            $(window).scroll(function(){
                                footerStatic();
                            });
                        }
                    });
                </script><?php
            }

            if ( (isset($_GET['module']) && $_GET['module'] == 'author') || (isset($_SESSION['oLP']->mode) && (isset($_GET['lp_id']) && $_GET['lp_id'] > 0) || (isset($_GET['exerciseId']) && $_GET['exerciseId'] > 0) || (isset($_GET['fromExercise']) && $_GET['fromExercise'] > 0))) {
                // This CSS must be moved to dokeos2_orange.css file
                ?>
                <link rel="stylesheet" type="text/css" href="<?php echo api_get_path(WEB_CODE_PATH).'css/'.$my_style;?>/course_navigation.css" /><?php
            }?>            
        </head>
        <body class="tool_background" dir="<?php echo $text_dir ?>">
            <?php
            echo '<div style="display:none;" id="msgGlossary"></div>';
            if (isset($_SESSION['oLP']->mode) && isset($_GET['lp_id']) && $_GET['lp_id'] > 0) {
                require_once api_get_path(SYS_CODE_PATH) . 'newscorm/learnpath.class.php';
                require_once api_get_path(SYS_CODE_PATH) . 'newscorm/learnpathItem.class.php';
                require_once api_get_path(SYS_CODE_PATH) . 'newscorm/course_navigation_interface.inc.php'; ?>
                    <div id="wrapper">    
                    <div id="main">
                    <div id="generic_tool_header">
                        <div id="header_background"><?php
                        echo display_author_header();?>
                        </div>
                    </div><?php
            } elseif ((isset($_GET['exerciseId']) && $_GET['exerciseId'] > 0) || (isset($_GET['fromExercise']) && $_GET['fromExercise'] > 0)) {
                // Load Learning path functions
                require_once api_get_path(SYS_PATH) . 'main/exercice/quiz_navigation_interface.inc.php';?>
                    <div id="wrapper">
                    <div id="main">
                    <div id="courseHeader"><?php
                        echo display_quiz_author_header();?>
                    </div>
                <?php
            } else {
                if ($GLOBALS['learner_view']) {
                    $param = "?learner_view=true" . (api_get_session_id() == 0 ? '' : '&amp;id_session=' . api_get_session_id());
                } else {
                    $param = (api_get_session_id() == 0 ? '' : '?id_session=' . api_get_session_id());
                }
                ?>
                    
                    <?php 
//                    if ((!$menuLink['title'])) {
//        echo '<style>#wrapper { margin-bottom: -80px;} #wrapper:after {margin-top: 0px;}</style>';
//                        echo '<style>.sticky-footer, #wrapper:after {  height: 35px } #wrapper {  margin-bottom: -35px ; </style>';
//    }
                    ?>
                    
                    
                    <div id="wrapper">
                <div id="main">
                    <div id="generic_tool_header" style="position: relative !important;">
                        <div id="header_background">
                        </div>
                        <?php
                        global $tool_name, $_cid, $_course;
                        
                        if (isset($_GET['nodeId']) && !isset($_GET['cidReq'])) {
                            echo '<a class="title-button" href="'. api_get_path(WEB_PATH) .'index.php" title="'. get_lang('CampusHomepage') .'" id="back2home" style="margin-left:8px;" target="'.$target.'"><img style="margin-left:-5px; margin-top:-5px; " src="'.api_get_path(WEB_IMG_PATH).'spacer.gif" alt="&nbsp;" width="42" height="37" /></a>';
                        } else {
                            define('SHOWING_TOOL_HEADER', true);
                            
                            if (strcmp($tool_name, 'Chat') == 0) $target = '_parent'; else $target = '_self';
                            // name of training
                            if (isset($_course) && array_key_exists('name', $_course)) {
                                $session_id = api_get_session_id();
                                $session_name = "";
                                if ($session_id > 0) {
                                    $session_info = array();
                                    $session_info = api_get_session_info($session_id);
                                    $session_name = ' ( ' . $session_info['name'] . ' )';
                                }
                            $cut_length = $tool_name == TOOL_AUTHOR?12:30;
                            $course_session_name = '<div class="course_session_name">' . cut($_course['name'] . ' ' . $session_name, $cut_length) . ' </div>';
                            if ($_cid == -1) {      
                               echo '<a class="title-button" href="'.api_get_path(WEB_PATH).'index.php'.$param.'" id="back2home2" target="'.$target.'"><img src="'.api_get_path(WEB_IMG_PATH).'spacer.gif" alt="&nbsp;" width="42" height="37" />'.$course_session_name.'</a>';                     
                            } else {
                                $course_path = !empty($_course['path']) ? $_course['path'] : $_course['directory'];                            
                               echo '<a class="title-button '.($tool_name == TOOL_AUTHOR?'author-btnhome':'').'" href="'.api_get_path(WEB_COURSE_PATH) . $course_path.'/index.php'.$param.'" id="back2home" style="margin-left:8px;" target="'.$target.'"><img style="margin-left:-5px; margin-top:-5px; " src="'.api_get_path(WEB_IMG_PATH).'spacer.gif" alt="&nbsp;" width="42" height="37" />'.$course_session_name.'</a>';
                            }
                        }
        }
        ?>
                    </div>
            <?php
            }
        }
        ?>
