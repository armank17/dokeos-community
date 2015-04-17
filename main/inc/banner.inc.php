<?php
//$id: $
/* For licensing terms, see /dokeos_license.txt */
/**
  ==============================================================================
 * 	This script contains the actual html code to display the "header"
 * 	or "banner" on top of every Dokeos page.
 *
 * 	@package dokeos.include
  ==============================================================================
 */
require_once(api_get_path(SYS_CODE_PATH) . 'inc/lib/banner.lib.php');

$home_path = $css_name = '';
if (isset($GLOBALS['_cid']) && $GLOBALS['_cid'] != -1) {
    // if We are inside a course
    $css_name = api_get_setting('allow_course_theme') == 'true' ? (api_get_course_setting('course_theme', null, true) ? api_get_course_setting('course_theme', null, true) : api_get_setting('stylesheets')) : api_get_setting('stylesheets');
    $css_info = api_get_css_info($css_name);
    if ($css_name != "dokeos2_blue_tablet" && $css_name != "dokeos2_orange_tablet") {
        $css_name = str_replace(array("dokeos2_blue", "dokeos2_orange"), array("dokeos2_blue_tablet", "dokeos2_orange_tablet"), $css_name);
    }
} else {
    $css_info = api_get_css_info();
}
// Check if we have a CSS with tablet support
$css_type = !is_null($css_info['type']) ? $css_info['type'] : 'tablet';

global $_configuration, $_user;
?>
<?php if (api_is_sas_version()): ?>
    <script src="<?php echo api_get_path(WEB_LIBRARY_PATH) . 'javascript/epiclock/javascript/jquery.dateformat.min.js'; ?>" type="text/javascript" language="javascript"></script>
    <script src="<?php echo api_get_path(WEB_LIBRARY_PATH) . 'javascript/epiclock/javascript/jquery.epiclock.min.js'; ?>" type="text/javascript" language="javascript"></script>
    <script>
        $(document).ready(function() {
            if ($("#trial-nb-day").length) {
                $("#trial-nb-day").epiclock({
                    mode: $.epiclock.modes.countdown,
                    format: "E",
                    offset: {days: <?php echo api_get_portal_days_left(); ?>}
                }).bind("timer", onExpiredTimeExercise);
            }
        });
        function onExpiredTimeExercise() {
            location.href = "<?php echo api_get_path(WEB_PATH) . 'index.php?logout=logout&uid=' . $_user['user_id']; ?>";
            return false;
        }
    </script>
<?php endif; ?>

<style>
    #button1 {
        margin-left: 15px;
        width: 110px;
        height: 28px;
    }
</style>

<div>
    <script type="text/javascript">
        $(document).ready(function() {

            if ($("#upgrade-trial").length) {
                $("#upgrade-trial").click(function() {
                    location.href = "<?php echo api_get_path(WEB_CODE_PATH) . 'index.php?module=suiteManager&cmd=Pricing&func=index'; ?>";
                    return false;
                });
            }

            $("#editlogo").click(function() {
                $('#close-upload-logo').css({left: '180px', width: '110px'});
                $("#upload-logo-home").animate({"top": "+=70px"}, 500);
            });
            $("#deletelogo").click(function() {

                $('#deleteMsgBody').dialog({modal: true, title: '<?php echo get_lang('DeleteLogo'); ?>', height: '230', width: '300px', resizable: false,
                    buttons: {
                        '<?php echo get_lang('No'); ?>': function() {
                            $(this).dialog('close');
                        },
                        '<?php echo get_lang('Yes'); ?>': function() {
                            $.ajax({
                                type: "POST",
                                url: "<?php echo api_get_path(WEB_AJAX_PATH) . 'ajaximage.php?action=deletelogohome'; ?>",
                                success: function(data) {
                                    $("#deletelogo").css("display", "none");
                                    $("#top_corner").html('<img src="<?php echo api_get_path(WEB_CODE_PATH) . 'css/' . $my_style . '/images/logo-text.png'; ?>"  />');
                                    $("#deleteMsgBody").dialog('close');
                                }
                            });
                        }
                    }
                });

            });
            $("#close-upload-logo").live("click", function() {
                $("#upload-logo-home").animate({"top": "-=70px"}, 500);
            });
        });

    </script>
    <script src="<?php echo api_get_path(WEB_CODE_PATH) . 'application/courseInfo/assets/js/infoModel.js' ?>"></script>
    <input type="hidden" id="webPath" value="<?php echo urlencode(api_get_path(WEB_PATH)); ?>" />
    <script src="<?php echo api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery.form.js'; ?>" language="javascript"></script>
    <script src="<?php echo api_get_path(WEB_CODE_PATH); ?>appcore/library/jquery/jquery.upload/js/vendor/jquery.ui.widget.js"></script>
    <script src="<?php echo api_get_path(WEB_CODE_PATH); ?>appcore/library/jquery/jquery.upload/js/jquery.iframe-transport.js"></script>
    <script src="<?php echo api_get_path(WEB_CODE_PATH); ?>appcore/library/jquery/jquery.upload/js/jquery.fileupload.js"></script>
    <link rel="stylesheet" href="<?php echo api_get_path(WEB_CODE_PATH) . 'course_home/css/home.css'; ?>">
    <script>
        $(document).ready(function() {
            $('#progress_home').hide();
            var path_img = 'home/logo/';
            var url = "<?php echo api_get_path(WEB_CODE_PATH) . 'index.php?module=courseInfo&cmd=InfoAjax&func=uploadImageCrop'; ?>";
            $('#picture_home').fileupload({
                url: url,
                dropZone: $(this),
                formData: {
                    path: path_img,
                    name: 'tempo_logohome',
                    min_width: 200,
                    min_height: 50,
                    max_width: 530,
                    max_height: 500
                },
                done: function(e, data) {
                    var ext = data.files[0].name;
                    ext = (ext.substring(ext.lastIndexOf('.'))).toLowerCase();
                    InfoModel.showActionDialogCrop(path_img, 200, 50, true, true, 'tempo_logohome', 'logo_home', ext);
                    $('#progress_home').hide();
                },
                progress: function(e, data) {
                    $('#progress_home').show();
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    $('#progress_home').css({width : progress + '%', background : 'skyblue'});
                    $('#progress_home').html(progress + '%');
                }
            });
        });
    </script>

</div>
<div id="wrapper">
    <div id="header">
        <div id="header1"
             <?php echo (isset($_SESSION['isShoppingCartActive']) && $_SESSION['isShoppingCartActive'] == TRUE) ? ' style="display: block; min-height: 50px; z-index: 1000;"' : ''; ?>>
            <div class="headerinner" style="position: relative;">
                <?php if (api_is_platform_admin()) { ?>
                    <div id="upload-logo-home">
                        <div id="form1" >
                            <form id="uploadlogo" name="nameLogo" method="post" enctype="multipart/form-data">
                                <!--<input type="hidden" name="to" value="logo_home">-->
                                <input type="text" name="nameLogo" id="text1" />
                                <input type="button" name="btnUpload" id="button1" value="<?php echo get_lang('Browse'); ?>"  />
                                <input id="picture_home" type="file" name="picture" accept="image/jpeg, image/png, image/gif" style="opacity:0;width:100%;filter:alpha(opacity=0);position:absolute;border:none;margin:0px;padding:0px;top:0px;right:0px;cursor:pointer;height:40px;" ><br>
                                <p id="require_upload_logo_text"><?php echo get_lang('File_upload') . ' (200x50 pixels)</h3>'; ?></p>                        
                                <button id="close-upload-logo" class="save"  type="button" ><?php echo get_lang('Close'); ?></button>
                            </form>
                        </div>
                        <!--<div id="preload1" style="display: none;">
                            <img src="<!?php echo api_get_path(WEB_IMG_PATH) . 'ajax-loader.gif'; ?>" alt="Uploading...."/>
                        </div>    -->
                        <div id="progress_home" style="height:40px; border-radius:5px; text-align:center; line-height:40px; font-weight:bold; font-size:14px;"></div>
                    </div>
                <?php } ?>
                <?php
                $theme_custom_index_page = array('orkyn_tablet');
                $stylesheet = api_get_setting('stylesheets');
                $is_customized = in_array($stylesheet, $theme_custom_index_page);
                if (!$is_customized && isset($_SESSION['isShoppingCartActive']) && $_SESSION['isShoppingCartActive'] == TRUE) :
                    require_once api_get_path(SYS_PATH) . 'main/core/controller/shopping_cart/shopping_cart_controller.php';
                    echo ShoppingCartController::create()->getShoppingCartHtml();
                endif;

                $iurl = api_get_setting('InstitutionUrl');
                //$path_logo = api_get_path(SYS_PATH) . 'home/logo/logo_home'; //logo-dokeos
                $path_logo = api_get_path(SYS_PATH) . 'home/logo/'; //logo-dokeos

                $imglogo = get_logo_home($path_logo, $my_style);
                check_there_logo($path_logo);
                ?>
                <?php if (api_is_platform_admin() && !($_GET['learner_view'] === 'true' || $GLOBALS['learner_view'])) { ?>
                    <div class="custom-edit" style="position: absolute;left:200px; top:5px;">

                        <a id="editlogo" >
                            <?php echo Display::return_icon('pixel.gif', get_lang('EditLogo'), array('class' => 'actionplaceholdericon actionedit')) . (!api_is_sas_version() ? get_lang('Logo') : ''); ?>
                        </a><br>

                        <a id="deletelogo" style="display: none;">
                            <?php echo Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')) . (!api_is_sas_version() ? get_lang('Delete') : ''); ?>
                        </a>


                    </div>
                <?php } ?>
                <?php
                $url_logo = api_get_setting('LogoUrl');
                $url_logo = (empty($url_logo)) ? api_get_path(WEB_PATH) . 'index.php' : '';
                ?>
                <div id="top_corner"  style="width: 200px;height: 50px;">
                    <a  style="display:block;height:50px;width:200px;"  href="<?php echo $url_logo; ?>">
                        <?php echo $imglogo; ?>                                        

                    </a>
                </div>

                <div id="banner-center">

                    <?php
                    if (api_is_sas_version() && api_is_platform_admin()):
                        ?>
                        <div id="banner-center-container">
                            <div id="institution">
                                <a href="<?php echo api_get_path(WEB_PATH); ?>index.php" target="_top"><?php echo api_get_setting('siteName') ?></a>
                                <?php
                                $iname = api_get_setting('Institution');
                                if (!empty($iname)) {
                                    echo '-&nbsp;<a href="' . $iurl . '" target="_top">' . $iname . '</a>';
                                }
                                ?>
                            </div>
                            <div id="update-sas_portal">
                                <span id="trial-count-day"><?php echo get_lang('YourTrialWillExpireIn') . ' <span id="trial-nb-day"></span> ' . strtolower(get_lang('Days')); ?></span><br />
                                <button type="button" class="upgrade_link" id="upgrade-trial"><?php echo get_lang('UpgradeTrial'); ?></button>
                            </div>
                        </div>
                    <?php else: ?>
                        <div id="institution">
                            <a href="<?php echo api_get_path(WEB_PATH); ?>index.php" target="_top"><?php echo api_get_setting('siteName') ?></a>
                            <?php
                            $iname = api_get_setting('Institution');
                            if (!empty($iname)) {
                                echo '-&nbsp;<a href="' . $iurl . '" target="_top">' . $iname . '</a>';
                            }
                            ?>
                        </div>
                    <?php endif; ?>                                        

                </div>

            </div>

        </div>



        <div id="header2">
            <div id="tooltip1" class="logout-tool" style="display: none;padding-left: 20px;padding-right: 20px;"><?php get_lang('Logout') . ' ' . $login ?></div>
            <div class="headerinner" style="margin:auto; width:960px">            
                <div class="headerinner1">
                    <?php display_tabs(); ?>
                </div>

                <!--<div id="clear_div"> </div>-->
            </div>
        </div>



        <?php isset($_course['path']) ? $home_path = api_get_path(WEB_COURSE_PATH) . $_course['path'] . '/' : $home_path = api_get_path(WEB_PATH); ?>
        <?php
        $url = $_SERVER['PHP_SELF']; // OR $_SERVER['REQUEST_URI']
        $path = parse_url($url, PHP_URL_PATH);
        $true = strpos($path, $_course['path']);
        if (!empty($_course) && empty($_GET['student']) && !empty($true)) {
            ?>
            <div id="header3">
                <div class="headerinner">
                    <div id="welcome_ico_home">
                        <a id="back2home" href="<?php echo $home_path; ?>index.php">

                            <?php
                            // name of training
                            if (isset($_course) && array_key_exists('name', $_course)) {
                                $session_id = api_get_session_id();
                                $session_name = "";
                                if ($session_id > 0) {
                                    $session_info = array();
                                    $session_info = api_get_session_info($session_id);
                                    $session_name = ' ( ' . $session_info['name'] . ' )';
                                }
                                if (defined('COURSE_HOME_PAGE') && COURSE_HOME_PAGE === true) {
                                    echo '<div class="span-header"><span id="global_course_name" class="global_course_name_homess">' . cut($_course['name'] . ' ' . $session_name, 70) . '</span></div>';
                                } else {
                                    echo '<div class="span-header"><span id="global_course_name">' . cut($_course['name'] . ' ' . $session_name, 70) . '</span></div>';
                                }
                            }
                            ?>
                            <div class="clear"></div>
                        </a>
                    </div>
                    <?php if (isset($GLOBALS['display_learner_view']) && $GLOBALS['display_learner_view'] === true && api_is_allowed_to_edit()) : ?>
                        <div id="welcome_ico_home" style="float:right">
                            <?php
                            if (empty($_GET['learner_view'])) :
                                $GLOBALS['learner_view'] = false;
                                $_SESSION['learner_view'] = false;
                                echo '<style>#courseintro { padding-bottom: 0px !important;}</style>';
                                ?>
                                <a id="back2home" class="back2home-right iconslearner" href="<?php echo api_get_self() ?>?learner_view=true">
                                    <div class="span-header" style="margin-left:10px !important; margin-right:40px !important">
                                        <span id="global_course_name" class="global_course_name_homess">
                                            <?php echo get_lang('ViewHomeAsLearner') ?>
                                        </span>
                                    </div>
                                    <img src="<?php echo api_get_path(WEB_IMG_PATH) . 'spacer.gif' ?>" width="42" height="37" alt="" />
                                </a>
                                <?php
                            else :
                                $GLOBALS['learner_view'] = true;
                                $_SESSION['learner_view'] = true;
                                echo '<style>#courseintro { padding-bottom: 25px;}</style>';
                                ?>
                                <a id="back2home" class="back2home-right iconstrainer" href="<?php echo api_get_self() ?>">
                                    <div class="span-header" style="margin-left:10px !important; margin-right:40px !important"><span id="global_course_name" class="global_course_name_homess">
                                            <?php echo get_lang('ViewHomeAsTrainer') ?>
                                        </span>
                                    </div>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        <?php } else { ?>
        <?php } ?>

        <?php
        // only display header4 if there is actually something that needs to be displayed (breadcrumbs)
        if (api_get_setting('display_breadcrumbs') == 'true') {
            ?>
            <div id="header4">
                <div class="headerinner">
                    <?php if (api_get_setting('display_breadcrumbs') == 'true') { ?>
                        <div id="breadcrumbs"><?php display_breadcrumbs(); ?></div>
                    <?php } ?>
                </div>
            </div>
            <?php
        }
        ?>
    </div>


    <?php

    function display_tabs() {
        global $_user;
// variable initialisation
        $navigation = array();

// getting all the possible tabs
        $possible_tabs = get_tabs();
        if (api_get_setting('search_enabled') != 'true') {
            unset($possible_tabs['search']);
        }
        if (isset($possible_tabs[SECTION_CAMPUS]))
            $navigation[SECTION_CAMPUS] = $possible_tabs[SECTION_CAMPUS];

        // logged
        if ($_user['user_id'] && !api_is_anonymous()) {
            $navigation = $possible_tabs;

            // anonymous
        } else {
            foreach ($possible_tabs as $section => $navigation_info) {
                if ($navigation_info['link_type'] != MENULINK_TYPE_PLATFORM)
                    $navigation[$section] = $possible_tabs[$section];
            }
        }
        ?>
        <div class="" style="width:56px;float:right;position:relative; ">

            <?php
            //Tab LogOut
            if ($_user['user_id'] && !api_is_anonymous($_user['user_id'], true)) {
                $login = '';
                if (api_is_anonymous($_user['user_id'], true)) {
                    $login = '(' . get_lang('Anonymous') . ')';
                } else {
                    $uinfo = api_get_user_info(api_get_user_id());
                    $login = '(' . cut($uinfo['username'], 17) . ')';
                }

                $close_session = api_get_path(WEB_PATH) . 'index.php?logout=logout&uid=' . $_user['user_id'];
                echo '<div class="logout-new" style="">
                   
                   <a class="logoutClick image-logout" title="' . get_lang('Logout') . ' ' . $login . '" href="' . $close_session . '"></a>
                </div> ';
            }
            ?>

        </div>

        <?php
        echo '<script type="text/javascript">
$(document).ready(function(){
    $(".logoutClick").mouseleave(function(){
       $(".logout-tool").css({display:"none"});        
    });    
    
});
</script>';
        echo '
<div id="dokeostabs">
<a id="prev2" class="prevbar" href="#"></a>
<a id="next2" class="nextbar" href="#"></a>
<div class="list_carousel">
<ul id="foo2">

';

// Displaying the tabs        
        foreach ($navigation as $section => $navigation_info) {
            // platform links
            if ($navigation_info['link_type'] == 'platform' or $section == 'search') {
                if (isset($GLOBALS['this_section'])) {
                    $current = ($section == $GLOBALS['this_section'] ? ' id="current" class="tab_' . $section . '_current"' : ' class="tab_' . $section . '"');
                    $class_icon_tab = ($section == $GLOBALS['this_section'] ? ' class="icon_tab_' . $section . '_current"' : ' class="icon_tab_' . $section . '"');
                    $get_my_class = 'tab_' . $section;
                } else {
                    $current = 'class="tab_' . $section . '"';
                    $get_my_class = 'tab_' . $section;
                }

                if ((!api_is_anonymous($_user['user_id'], true)) || $get_my_class == 'tab_mycampus') {
                    echo "<li " . $current . ">";
                    if ($section == "search") {
                        if (extension_loaded('xapian') && !api_is_anonymous()) {
                            ?>
                            <form  method="post" action="#" onsubmit="return h_search();" style="position:relative;"> 
                                <span>
                                    <input type="text" name="input" class="input-h-search" id="search-text-input"></input>
                                </span>
                                <span>
                                    <button style="" type="image" src="<?php echo api_get_path(WEB_IMG_PATH) . 'button_search.png'; ?>" id="btn-h-search"/></button>
                                </span>
                            </form>
                            <?php
                        }
                    } else {
                        echo "<a href='" . $navigation_info['url'] . "' target='" . $navigation_info['target'] . "'>" . $navigation_info['title'] . "</a>";
                    }
                    echo "</li>";
                }

                // other links    
            } else {
                ?>
                <li class="tab_link"><a href="<?php echo $navigation_info['url']; ?>" target="<?php echo $navigation_info['target']; ?>"><?php echo $navigation_info['title']; ?></a></li>
                <?php
            }
        }

        echo '  </ul>
            <div class="clear"></div>
                </div> <!-- /#tabs_container --> ';

        echo '  
            </div>  <!-- /#dokeostabs -->
            
  ';
    }

//display logout message Lb
    echo '<div style="display:none;" id="logoutMsgBody">';
    echo '<center><img alt="' . get_lang('AreYouSureAreCloseSession') . '" title="' . get_lang('AreYouSureAreCloseSession') . '" src="' . api_get_path(WEB_IMG_PATH) . 'logout-tab.png" style="vertical-align:text-bottom;" /><br/>' . get_lang('AreYouSureAreCloseSession') . '</center>';
    echo '</div>';

    $imglogo = get_logo_home($path_logo, $my_style);

    echo '<div style="display:none;" id="deleteMsgBody">';
    echo '<br/><center><div id="newlogo">' . $imglogo . '</div><br/>' . get_lang('AreYouSureAreDeleteLogo') . '</center>';
    echo '</div>';

    function get_logo_home($path_logo, $my_style) {
        global $_configuration, $platform_theme;
        $access_url_id = intval(api_get_current_access_url_id());
        $active_multisite = $_configuration['multiple_access_urls'];

        //if ($access_url_id < 0) {
        //    $access_url_id = 1;
        //}
        //$files = glob($path_logo . 'logo-dokeos-' . $access_url_id . '-*');
        //if (count($files) < 1 && !$_configuration['multiple_access_urls']) {
        //    $files = glob($path_logo . '*');
        //}

        if ($active_multisite == true) {
            $files = glob($path_logo . 'logo_home_site_' . $access_url_id . '*');
        } else {
            $files = glob($path_logo . 'logo_home' . '*');
        }

        if (count($files) > 0) {
            foreach ($files as $path_file) {
                $new_file_path = pathinfo($path_file);
                if ($new_file_path['extension'] == 'gif' || $new_file_path['extension'] == 'png' || $new_file_path['extension'] == 'jpg' || $new_file_path['extension'] == 'jpeg') {
                    if ($active_multisite == true) { //multi-site actived
                        if (substr($new_file_path['basename'], 0, 14) == 'logo_home_site')
                            $imglogo = '<img src="' . api_get_path(WEB_PATH) . 'home/logo/' . $new_file_path['basename'] . '?t=' . time() . '" />';
                    } else {
                        if (substr($new_file_path['basename'], 0, 10) == 'logo_home.')
                            $imglogo = '<img src="' . api_get_path(WEB_PATH) . 'home/logo/' . $new_file_path['basename'] . '?t=' . time() . '" />';
                    }
                }
            }
        } else {

            if ($platform_theme == 'dokeos2_roullier') {
                $imglogo = '<img src="' . api_get_path(WEB_CODE_PATH) . 'css/' . $my_style . '/images/logo_roullier.png" />';
            } else {
            $imglogo = '<img src="' . api_get_path(WEB_CODE_PATH) . 'css/' . $my_style . '/images/logo-text.png" />';
        }
        }
        return $imglogo;
    }

    function check_there_logo($path_logo) {
        global $_configuration;
        $active_multisite = $_configuration['multiple_access_urls'];
        if (count(glob($path_logo . '*')) >= 1) {
            foreach (glob($path_logo . '*') as $path_file) {
                $new_file_path = pathinfo($path_file);
                $ext = $new_file_path['extension'];
                if ($ext == 'gif' || $ext == 'png' || $ext == 'jpg' || $ext == 'jpeg') {
                    $imglogo = api_get_path(SYS_PATH) . 'home/logo/logo_home.' . $ext;
                    if (is_file($imglogo) && $active_multisite != true) { //Multisite is inactived
                        echo '<script type="text/javascript">$(document).ready(function(){$("#deletelogo").css("display","block");});</script>';
                        break;
                    } else {
                        if ($active_multisite == true) { //Multisite is actived
                            $imglogo = api_get_path(SYS_PATH) . 'home/logo/logo_home_site_' . intval(api_get_current_access_url_id()) . '.' . $ext;
                            if (is_file($imglogo)) {
                                echo '<script type="text/javascript">$(document).ready(function(){$("#deletelogo").css("display","block");});</script>';
                                break;
                            }
                        }
                    }
                }
            }
        }
    }

    function display_breadcrumbs() {
        global $interbreadcrumb, $_course, $_cid, $nameTools;


        if (api_get_setting('display_breadcrumbs') == 'true') {
            // variable initialisation
            $navigation = array();

            // part 1: Course Homepage. If we are in a course then the first breadcrumb is a link to the course homepage
            //hide_course_breadcrumb the parameter has been added to hide the name of the course, that appeared in the default $interbreadcrumb
            $session_id = api_get_session_id();
            $session_name = api_get_session_name($my_session_id);
            $my_session_name = ($session_name == null) ? '' : '&nbsp;(' . $session_name . ')';
            if (isset($_cid) and $_cid != -1 and isset($_course) and !isset($_GET['hide_course_breadcrumb'])) {
                $navigation_item['url'] = api_get_path(WEB_COURSE_PATH) . $_course['path'] . '/index.php';
                switch (api_get_setting('breadcrumbs_course_homepage')) {
                    case 'get_lang':
                        $navigation_item['title'] = get_lang('CourseHomepageLink');
                        break;
                    case 'course_code':
                        $navigation_item['title'] = $_course['official_code'];
                        break;
                    case 'session_name_and_course_title':
                        $navigation_item['title'] = $_course['name'] . $my_session_name;
                        break;
                    default:
                        $navigation_item['title'] = $_course['name'];
                        break;
                }
                $navigation[] = $navigation_item;
            }

            // part 2: Interbreadcrumbs. If there is an array $interbreadcrumb defined then these have to appear before the last breadcrumb (which is the tool itself)
            if (isset($interbreadcrumb) && is_array($interbreadcrumb)) {
                foreach ($interbreadcrumb as $breadcrumb_step) {
                    $sep = (strrchr($breadcrumb_step['url'], '?') ? '&amp;' : '?');
                    $navigation_item['url'] = $breadcrumb_step['url'] . $sep . api_get_cidreq();
                    $navigation_item['title'] = $breadcrumb_step['name'];
                    $navigation[] = $navigation_item;
                }
            }
            // part 3: The tool itself. If we are on the course homepage we do not want to display the title of the course because this
            // is the same as the first part of the breadcrumbs (see part 1)
            if (isset($nameTools) AND $language_file <> "course_home") {
                $navigation_item['url'] = '#';
                $navigation_item['title'] = $nameTools;
                $navigation[] = $navigation_item;
            }

            $final_navigation = array();
            foreach ($navigation as $index => $navigation_info) {
                if (!empty($navigation_info['title'])) {
                    $final_navigation[$index] = '<a href="' . $navigation_info['url'] . '" class="breadcrumb breadcrumb' . $index . '" target="_top">' . $navigation_info['title'] . '</a>';
                }
            }

            if (!empty($final_navigation)) {
                echo '<div id="header5">';
                echo implode(' &gt; ', $final_navigation);
                echo '</div>';
            }
        }
    }

    function display_help($help_content, $help_subtopic = '') {
        global $help;

        if (empty($help[(string) $help_content]))
            return '';

        $return .= '<ul>';
        if (empty($help_subtopic)) {
            foreach ($help[$help_content] as $subtopic => $helptopic) {
                // we are having subtopics
                if (is_array($helptopic)) {
                    $return .= '<li><strong>' . get_lang($subtopic) . '</strong></li>';
                    $return .= '<li>' . display_help($help_content, $subtopic) . '</li>';
                } else {
                    $return .= '<li>' . $helptopic . '</li>';
                }
            }
        } else {
            foreach ($help[$help_content][$help_subtopic] as $subtopic => $helptopic) {
                //echo '<br>hier:'.$help_content.'/'.$help_subtopic.'/'.$subtopic.'/'.$helptopic.'<br/>';
                $return .= '<li>' . $helptopic . '</li>';
            }
        }
        $return .= '</ul>';
        return $return;
    }

    if (isset($dokeos_database_connection)) {
        // connect to the main database.
        // if single database, don't pefix table names with the main database name in SQL queries
        // (ex. SELECT * FROM `table`)
        // if multiple database, prefix table names with the course database name in SQL queries (or no prefix if the table is in
        // the main database)
        // (ex. SELECT * FROM `table_from_main_db`  -  SELECT * FROM `courseDB`.`table_from_course_db`)
        mysql_select_db($_configuration['main_database'], $dokeos_database_connection);
    }
    ?>

    <!--</div>  end of the whole #header section -->

    <?php
//to mask the main div, set $header_hide_main_div to true in any script just before calling Display::display_header();
    global $header_hide_main_div;
    if (!empty($header_hide_main_div) && $header_hide_main_div === true) {
        //do nothing
    } else {
        ?>

        <div id="main"> <!-- start of #main wrapper for #content and #menu divs -->
            <?php
        }

        /*
          -----------------------------------------------------------------------------
          Navigation menu section
          -----------------------------------------------------------------------------
         */
        if (api_get_setting('show_navigation_menu') != 'false' && api_get_setting('show_navigation_menu') != 'icons') {
            Display::show_course_navigation_menu($_GET['isHidden']);
            $course_id = api_get_course_id();
            if (!empty($course_id) && ($course_id != -1)) {
                echo '<div id="menuButton">';
                echo $output_string_menu;
                echo '</div>';
                if (isset($_SESSION['hideMenu'])) {
                    if ($_SESSION['hideMenu'] == "shown") {
                        if (isset($_cid)) {
                            echo '<div id="centerwrap"> <!-- start of #centerwrap -->';
                            echo '<div id="center"> <!-- start of #center -->';
                        }
                    }
                } else {
                    if (isset($_cid)) {
                        echo '<div id="centerwrap"> <!-- start of #centerwrap -->';
                        echo '<div id="center"> <!-- start of #center -->';
                    }
                }
            }
        }
        ?>
        <!--   Begin Of script Output   -->
