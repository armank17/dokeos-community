<?php
/* For licensing terms, see /dokeos_license.txt */

global $language_interface;

/**
 * @package dokeos.admin
 */
// name of the language file that needs to be included
$language_file = array('admin', 'accessibility', 'index', 'exercice');

// resetting the course id
$cidReset = true;

// setting the help
$help_content = 'platformadministrationplatformnews';

// including the global Dokeos file
require_once '../inc/global.inc.php';

// including additional libraries
require_once (api_get_path(LIBRARY_PATH) . 'WCAG/WCAG_rendering.php');
require_once (api_get_path(LIBRARY_PATH) . 'fileUpload.lib.php');
require_once (api_get_path(LIBRARY_PATH) . 'formvalidator/FormValidator.class.php');
require_once (api_get_path(LIBRARY_PATH) . 'security.lib.php');
require_once (api_get_path(LIBRARY_PATH) . 'language.lib.php');
require_once (api_get_path(LIBRARY_PATH) . 'timezone.lib.php');
//get_lang('LogoMinAllowedSize')
// setting the section (for the tabs)
$this_section = SECTION_PLATFORM_ADMIN;
//$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-1.5.1.min.js" "></script>';
$htmlHeadXtra[] = '<link type="text/css" href="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/thiagosf-SkitterSlideshow/css/skitter.styles.css" rel="stylesheet" />';
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/thiagosf-SkitterSlideshow/js/jquery.skitter.min.js"></script>';
$htmlHeadXtra[] = '<link rel="stylesheet" type="text/css" href="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/chosen/chosen.css"/>';
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/chosen/chosen.jquery.min.js"></script>';
if (isset($_GET['action']) && $_GET['action'] == 'logo') {
    $htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/AjaxUpload.2.0.min.js"></script>';
    $htmlHeadXtra[] = '<script type="text/javascript">
$(document).ready( function() {
var button = $("#btnUpload"), interval;
    new AjaxUpload("#btnUpload", {
        action: "upload.php",
        onSubmit : function(file , ext){
        if (! (ext && /^(jpg|png|jpeg|gif|jpg)$/.test(ext))){
            alert("' . get_lang('AcceptedPictureFormats') . '");
            return false;
        }
        },
        onComplete: function(file, response){
            xajax_uploadLogo(file);
            location.reload(true);
        }
    });
});
</script>';
}
$htmlHeadXtra[] = '<script type="text/javascript">
$(document).ready( function() {
  $(".chzn-select").chosen({no_results_text: "' . get_lang('NoResults') . '"});
});
</script>';
$htmlHeadXtra[] = '<script type="text/javascript">
    $(function(){
        $(".link-pagehome-delete").click(function(e){
            e.preventDefault();
            
            var _self      = $(this),
                url        = _self.attr("href"),
                confirmMsn = _self.attr("title"),
                webPath    = "'. api_get_path(WEB_CODE_PATH) .'";

            $.confirm(confirmMsn, getLang("ConfirmationMessage"), function() {
               $.post(url, function(data) {
                    location.href = webPath+"admin/configure_homepage.php";
               });
            });
        });
        
        $("#accordion").accordion({
            heightStyle: "content",
            collapsible: true
        });
    });
</script>

<style>
#content {background:#ffffff;}
</style>
';
// access_url
$access_url_id = api_get_current_access_url_id();
if ($access_url_id < 0){
    $access_url_id = 1;
}

// This session help for display media resources in the Fck portal admin
$_SESSION['this_section'] = $this_section;
// Access restrictions
api_protect_admin_script(true);

// setting the breadcrumbs
$interbreadcrumb[] = array('url' => 'index.php', "name" => get_lang('PlatformAdmin'));
// Add style in the source page
$fck_attribute['Config']['FullPage'] = true;
// variable initialisation
$this_page = '';
$action = Security::remove_XSS($_GET['action']);
$tbl_category = Database::get_main_table(TABLE_MAIN_CATEGORY);

define(PROMOTED, 1);
// name of the tool
$tool_name = get_lang('ConfigureHomePage');
if (!empty($action)) {
    $interbreadcrumb[] = array('url' => 'configure_homepage.php', "name" => get_lang('ConfigureHomePage'));
    switch ($action) {
        case "edit_top":
            $tool_name = get_lang("EditHomePage");
            break;
        case "edit_news":
            $tool_name = get_lang("EditNews");
            break;
        case "edit_notice":
            $tool_name = get_lang("EditNotice");
            break;
        case "insert_link":
            $tool_name = get_lang("InsertLink");
            if (isset($_GET['from_template']) && $_GET['from_template'] == 'true') {
                $tool_name = get_lang("CreatePageFromATemplate");
            }
            break;
        case "edit_link":
            $tool_name = get_lang("EditLink");
            break;
        case "insert_tabs":
            $tool_name = get_lang("InsertTabs");
            break;
        case "edit_tabs":
            $tool_name = get_lang("EditTabs");
            break;
    }
}

//The global logic for language priorities should be:
//- take language selected when connecting ($_SESSION['user_language_choice'])
//  or last language selected (taken from select box into SESSION by global.inc.php)
//  or, if unavailable;
//- take default user language ($_SESSION['_user']['language']) - which is taken from
//  the database in local.inc.php or, if unavailable;
//- take platform language (taken from the database campus setting 'platformLanguage')
// Then if a language file doesn't exist, it should be created.
// The default language for the homepage should use the default platform language
// (if nothing else is selected), which means the 'no-language' file should be taken
// to fill a new 'language-specified' language file, and then only the latter should be
// modified. The original 'no-language' files should never be modified.
// ----- Language selection -----
// The final language selected and used everywhere in this script follows the rules
// described above and is put into "$lang". Because this script includes
// global.inc.php, the variables used for language purposes below are considered safe.

$lang = ''; //el for "Edit Language"
$count_lang_availables = LanguageManager::count_available_languages();

/* if(!empty($_SESSION['user_language_choice']) && $count_lang_availables > 1) {
  $lang=$_SESSION['user_language_choice'];
  } elseif(!empty($_SESSION['_user']['language']) && $count_lang_availables > 1) {
  //$lang=$_SESSION['_user']['language'];
  } else {
  $lang=api_get_setting('platformLanguage');
  } */

if (!empty($_SESSION['user_language_choice']) && $count_lang_availables > 1) {
    $lang = $_SESSION['user_language_choice'];
} else {
    $lang = api_get_setting('platformLanguage');
}
// ----- Ensuring availability of main files in the corresponding language -----

if ($_configuration['multiple_access_urls'] == true) {
    $access_url_id = api_get_current_access_url_id();
    if ($access_url_id != -1) {
        $url_info = api_get_access_url($access_url_id);
        // "http://" and the final "/" replaced
        $url = substr($url_info['url'], 7, strlen($url_info['url']) - 8);
        $clean_url = replace_dangerous_char($url);
        $clean_url = str_replace('/', '-', $clean_url);
        $clean_url = $clean_url . '/';

        $homep = '../../home/'; //homep for Home Path
        $homep_new = '../../home/' . $clean_url; //homep for Home Path added the url

        $new_url_dir = api_get_path(SYS_PATH) . 'home/' . $clean_url;
        //we create the new dir for the new sitesrea
        if (!is_dir($new_url_dir)) {
            umask(0);
            $perm = api_get_setting('permissions_for_new_directories');
            $perm = octdec(!empty($perm) ? $perm : '0755');
            mkdir($new_url_dir, $perm);
        }
    }
} else {
    $homep_new = '';
    $homep = '../../home/'; //homep for Home Path
}

// Templates files for multisite mode
$homepaget = 'Homepage';
$demopaget = 'Demo';
$overviewpaget = 'Overview';
$teampaget = 'Team';
$testimonialpaget = 'Testimonial';
// Home files
$menuf = 'home_menu'; //menuf for Menu File
$newsf = 'home_news'; //newsf for News File
$topf = 'home_top'; //topf for Top File
$noticef = 'home_notice'; //noticef for Notice File
$menutabs = 'home_tabs'; //menutabs for tabs Menu
$ext = '.html'; //ext for HTML Extension - when used frequently, variables are
// faster than hardcoded strings
$homef = array($menuf, $newsf, $topf, $noticef, $menutabs, $homepagef);
$hometemplates = array($homepaget, $demopaget, $overviewpaget, $teampaget, $testimonialpaget);

// If language-specific file does not exist, create it by copying default file
foreach ($homef as $my_file) {
    if ($_configuration['multiple_access_urls'] == true) {
        if (!file_exists($homep_new . $my_file . '_' . $lang . $ext)) {
            copy($homep . $my_file . $ext, $homep_new . $my_file . '_' . $lang . $ext);
        }
    } else {
        if (!file_exists($homep . $my_file . '_' . $lang . $ext)) {
            copy($homep . $my_file . $ext, $homep . $my_file . '_' . $lang . $ext);
        }
    }
}

// If language-specific file does not exist, create it by copying default template file
foreach ($hometemplates as $my_file) {
    if ($_configuration['multiple_access_urls'] == true) {
        if (!file_exists($homep_new . $my_file . $ext)) {
            copy($homep . $my_file . $ext, $homep_new . $my_file . $ext);
        }
    }
}

if ($_configuration['multiple_access_urls'] == true) {
    $homep = $homep_new;
}

// Check WCAG settings and prepare edition using WCAG
$errorMsg = '';
if (api_get_setting('wcag_anysurfer_public_pages') == 'true') {
    $errorMsg = WCAG_Rendering::request_validation();
}

// Filter link param
$link = '';
if (!empty($_GET['link'])) {
    $link = $_GET['link'];
    // If the link parameter is suspicious, empty it
    if (strstr($link, '/') || !strstr($link, '.html') || strstr($link, '\\')) {
        $link = '';
        $action = '';
    }
}

global $_configuration;

// Start analysing requested actions
if (!empty($action)) {
    if ($_POST['formSent']) {
        //variables used are $homep for home path, $menuf for menu file, $newsf
        // for news file, $topf for top file, $noticef for noticefile,
        // $ext for '.html'
        switch ($action) {
            case 'edit_top':
                // Filter
                $home_top = '';
                if (api_get_setting('wcag_anysurfer_public_pages') == 'true') {
                    $home_top = WCAG_Rendering::prepareXHTML();
                } else {
                    $home_top = trim(stripslashes($_POST['home_top']));
                }

                // Write
                if (file_exists($homep . $topf . '_' . $lang . $ext)) {
                    if (is_writable($homep . $topf . '_' . $lang . $ext)) {
                        $fp = fopen($homep . $topf . '_' . $lang . $ext, "w");
                        fputs($fp, $home_top);
                        fclose($fp);
                    } else {
                        $errorMsg = get_lang('HomePageFilesNotWritable');
                    }
                } else {
                    //File does not exist
                    $fp = fopen($homep . $topf . '_' . $lang . $ext, "w");
                    fputs($fp, $home_top);
                    fclose($fp);
                }

                break;
            case 'edit_notice':
                // Filter
                $notice_title = trim(strip_tags(stripslashes($_POST['notice_title'])));
                $notice_text = trim(str_replace(array("\r", "\n"), array("", "<br />"), strip_tags(stripslashes($_POST['notice_text']), '<a>')));
                if (empty($notice_title) || empty($notice_text)) {
                    $errorMsg = get_lang('NoticeWillBeNotDisplayed');
                }
                // Write
                if (file_exists($homep . $noticef . '_' . $lang . $ext)) {
                    if (is_writable($homep . $noticef . '_' . $lang . $ext)) {
                        $fp = fopen($homep . $noticef . '_' . $lang . $ext, "w");
                        if ($errorMsg == '') {
                            fputs($fp, "<b>$notice_title</b><br />\n$notice_text");
                        } else {
                            fputs($fp, "");
                        }
                        fclose($fp);
                    } else {
                        $errorMsg.="<br/>\n" . get_lang('HomePageFilesNotWritable');
                    }
                } else {
                    //File does not exist
                    $fp = fopen($homep . $noticef . '_' . $lang . $ext, "w");
                    fputs($fp, "<b>$notice_title</b><br />\n$notice_text");
                    fclose($fp);
                }
                break;
            case 'edit_news':
                //Filter
                //$s_languages_news=$_POST["news_languages"];
                if (api_get_setting('wcag_anysurfer_public_pages') == 'true') {
                    $home_news = WCAG_rendering::prepareXHTML();
                } else {
                    $home_news = trim(stripslashes($_POST['home_news']));
                }
                //Write
                if ($s_languages_news != "all") {

                    if (file_exists($homep . $newsf . '_' . $s_languages_news . $ext)) {
                        if (is_writable($homep . $newsf . '_' . $s_languages_news . $ext)) {
                            $fp = fopen($homep . $newsf . '_' . $s_languages_news . $ext, "w");
                            fputs($fp, $home_news);
                            fclose($fp);
                        } else {
                            $errorMsg = get_lang('HomePageFilesNotWritable');
                        }
                    }
                    //File not exists
                    else {
                        $fp = fopen($homep . $newsf . '_' . $s_languages_news . $ext, "w");
                        fputs($fp, $home_news);
                        fclose($fp);
                    }
                } else { //we update all the news file
                    $_languages = api_get_languages();

                    foreach ($_languages["name"] as $key => $value) {

                        $english_name = $_languages["folder"][$key];

                        if (file_exists($homep . $newsf . '_' . $english_name . $ext)) {
                            if (is_writable($homep . $newsf . '_' . $english_name . $ext)) {
                                $fp = fopen($homep . $newsf . '_' . $english_name . $ext, "w");
                                fputs($fp, $home_news);
                                fclose($fp);
                            } else {
                                $errorMsg = get_lang('HomePageFilesNotWritable');
                            }
                        }
                        //File not exists
                        else {
                            $fp = fopen($homep . $newsf . '_' . $english_name . $ext, "w");
                            fputs($fp, $home_news);
                            fclose($fp);
                        }
                    }
                }
                break;
            case 'insert_tabs':
            case 'edit_tabs':
            case 'insert_link':
            case 'edit_link':
                $link_index = intval($_POST['link_index']);
                $insert_where = intval($_POST['insert_where']);
                $link_name = trim(stripslashes($_POST['link_name']));
                $link_url = trim(stripslashes($_POST['link_url']));
                // WCAG
                if (api_get_setting('wcag_anysurfer_public_pages') == 'true') {
                    $link_html = WCAG_Rendering::prepareXHTML();
                } else {
                    $link_html = trim(stripslashes($_POST['link_html']));
                }

                // replace link to templates.css with {CSS}
                $link_html = str_replace(api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets') . '/templates.css', '{CURRENT_CSS_PATH}', $link_html);

                // replace to curren template css path
                if (preg_match('!/css/(.+)/templates.css!', $link_html, $matches)) {
                    $link_html = str_replace('/' . $matches[1] . '/templates.css', '/' . api_get_setting('stylesheets') . '/templates.css', $link_html);
                }
                // replace to curren default css path
                if (preg_match('!/css/(.+)/default.css!', $link_html, $matches)) {
                    $link_html = str_replace('/' . $matches[1] . '/default.css', '/' . api_get_setting('stylesheets') . '/default.css', $link_html);
                }
                // remove default.css
                if (preg_match('/(<link href="(.*)\/default\.css" rel="(.*)" type="(.*)" \/>)/i', $link_html)) {
                    $link_html = preg_replace('/(<link href="(.*)\/default\.css" rel="(.*)" type="(.*)" \/>)/i', '', $link_html);
                }

                $filename = trim(stripslashes($_POST['filename']));
                $target_blank = $_POST['target_blank'] ? true : false;

                if ($link_url == 'http://') {
                    $link_url = '';
                } elseif (!empty($link_url) && !strstr($link_url, '://') && !strstr($link_url, 'mailto')) {
                    $link_url = 'http://' . $link_url;
                }

                $menuf = ($action == 'insert_tabs' || $action == 'edit_tabs') ? $menutabs : $menuf;
                if (!is_writable($homep . $menuf . '_' . $lang . $ext)) {
                    $errorMsg = get_lang('HomePageFilesNotWritable');
                } elseif (empty($link_name)) {
                    $errorMsg = get_lang('PleaseEnterLinkName');
                } else {


                    // New links are added as new files in the home/ directory
                    if ($action == 'insert_link' || $action == 'insert_tabs' || empty($filename) || strstr($filename, '/') || !strstr($filename, '.html')) {
                        $filename = replace_dangerous_char($link_name, 'strict') . '.html';
                    }
                    // "home_" prefix for links are renamed to "user_" prefix (to avoid name clash with existing home page files)
                    if (!empty($filename)) {
                        $filename = str_replace('home_', 'user_', $filename);
                    }
                    // If the typical language suffix is not found in the file name,
                    // replace the ".html" suffix by "_en.html" or the active menu language
                    if (!strstr($filename, '_' . $lang . $ext)) {
                        $filename = str_replace($ext, '_' . $lang . $ext, $filename);
                    }


                    // Get the contents of home_menu_en.html (or active menu language
                    // version) into $home_menu as an array of one entry per line
                    $home_menu = file($homep . $menuf . '_' . $lang . $ext);
                    // Prepare place to insert the new link into (default is end of file)
                    if ($insert_where < -1 || $insert_where > (sizeof($home_menu) - 1)) {
                        $insert_where = sizeof($home_menu) - 1;
                    }
                    // For each line of the file, remove trailing spaces and special chars
                    foreach ($home_menu as $key => $enreg) {
                        $home_menu[$key] = trim($enreg);
                    }

                    global $_configuration;
                    if (isset($_POST['create_from_template']) && $_POST['create_from_template'] == 1 && $_configuration['multiple_access_urls'] != true) {
                        $file_path = api_get_path(SYS_PATH) . 'home/' . $filename;
                        file_put_contents($file_path, $link_html);
                    }
                    // If the given link url is empty, then replace the link url by a link to the link file created
                    if (empty($link_url) && $_configuration['multiple_access_urls'] != true) {
                        $link_url = api_get_path(WEB_PATH) . 'index.php?include=' . urlencode($filename);
                        // If the file doesn't exist, then create it and
                        // fill it with default text
                        if (!file_exists(api_get_path(SYS_PATH) . 'home/' . $filename)) {
                            $fp = @fopen(api_get_path(SYS_PATH) . 'home/' . $filename, 'w');

                            if ($fp) {
                                fputs($fp, get_lang('TemplateMyTextHere'));
                                fclose($fp);
                            }
                        }
                    } else {
                        $home_file_ms__path = $homep . $filename;
                        if (!file_exists($home_file_ms__path)) {
                            file_put_contents($home_file_ms__path, $link_html);
                            $link_url = api_get_path(WEB_PATH) . 'index.php?include=' . $filename;
                        } else {
                            $home_file_ms__path = $homep_new . $filename;
                            file_put_contents($home_file_ms__path, $link_html);
                            $link_url = api_get_path(WEB_PATH) . 'index.php?include=' . $filename;
                        }
                    }
                    // If the requested action is to edit a link, open the file and
                    // write to it (if the file doesn't exist, create it)
                    if ($action == 'edit_link' && !empty($link_html)) {
                        $fp = @fopen(api_get_path(SYS_PATH) . 'home/' . $filename, 'w');

                        if ($fp) {
                            fputs($fp, $link_html);
                            fclose($fp);
                        }
                    }
                    // If the requested action is to create a link, make some room
                    // for the new link in the home_menu array at the requested place
                    // and insert the new link there
                    if ($action == 'insert_link' || $action == 'insert_tabs') {
                        for ($i = sizeof($home_menu); $i; $i--) {
                            if ($i > $insert_where) {
                                $home_menu[$i] = $home_menu[$i - 1];
                            } else {
                                break;
                            }
                        }
                        $home_menu[$insert_where + 1] = '<div><a href="' . $link_url . '" target="' . ($target_blank ? '_blank' : '_self') . '">' . $link_name . '</a></div>';
                    } else { // If the request is about a link edition, change the link
                        $home_menu[$link_index] = '<div><a href="' . $link_url . '" target="' . ($target_blank ? '_blank' : '_self') . '">' . $link_name . '</a></div>';
                    }
                    // Re-build the file from the home_menu array
                    $home_menu = implode("\n", $home_menu);
                    // Write
                    if (file_exists($homep . $menuf . '_' . $lang . $ext)) {
                        if (is_writable($homep . $menuf . '_' . $lang . $ext)) {
                            $fp = fopen($homep . $menuf . '_' . $lang . $ext, "w");
                            fputs($fp, $home_menu);
                            fclose($fp);
                            if (file_exists($homep . $menuf . $ext)) {
                                if (is_writable($homep . $menuf . $ext)) {
                                    $fpo = fopen($homep . $menuf . $ext, "w");
                                    fputs($fpo, $home_menu);
                                    fclose($fpo);
                                }
                            }
                        } else {
                            $errorMsg = get_lang('HomePageFilesNotWritable');
                        }
                    } else { //File does not exist
                        $fp = fopen($homep . $menuf . '_' . $lang . $ext, "w");
                        fputs($fp, $home_menu);
                        fclose($fp);
                    }
                }
                break;
        } //end of switch($action)

        if (empty($errorMsg)) {
            header('Location: ' . api_get_self());
            exit();
        }
    } else {
        //if POST[formSent] is not set

        switch ($action) {
            case 'open_link':
                // Previously, filtering of GET['link'] was done here but it left
                // a security threat. Filtering has now been moved outside conditions
                break;
            case 'delete_tabs':
            case 'delete_link':
                // A link is deleted by getting the file into an array, removing the
                // link and re-writing the array to the file
                $link_index = intval($_GET['link_index']);
                $menuf = ($action == 'delete_tabs') ? $menutabs : $menuf;
                $home_menu = file($homep . $menuf . '_' . $lang . $ext);

                foreach ($home_menu as $key => $enreg) {
                    if ($key == $link_index) {
                        unset($home_menu[$key]);
                    } else {
                        $home_menu[$key] = trim($enreg);
                    }
                }
                $home_menu = implode("\n", $home_menu);
                $fp = fopen($homep . $menuf . '_' . $lang . $ext, 'w');
                fputs($fp, $home_menu);
                fclose($fp);
                if (file_exists($homep . $menuf . $ext)) {
                    if (is_writable($homep . $menuf . $ext)) {
                        $fpo = fopen($homep . $menuf . $ext, 'w');
                        fputs($fpo, $home_menu);
                        fclose($fpo);
                    }
                }
                header('Location: ' . api_get_self());
                exit();
                break;
            case 'edit_top':
                // This request is only the preparation for the update of the home_top
                $home_top = '';
                if (is_file($homep . $topf . '_' . $lang . $ext) && is_readable($homep . $topf . '_' . $lang . $ext)) {
                    $home_top = file_get_contents($homep . $topf . '_' . $lang . $ext);
                } elseif (is_file($homep . $topf . $lang . $ext) && is_readable($homep . $topf . $lang . $ext)) {
                    $home_top = file_get_contents($homep . $topf . $lang . $ext);
                } else {
                    $errorMsg = get_lang('HomePageFilesNotReadable');
                }

                if (isset($_GET['scenario']) && isset($_GET['action']) && $_GET['action'] == 'edit_top') {
                    // Load scenarios
                    switch ($_GET['scenario']) {
                        case 'homepage':
                            $home_top = file_get_contents($homep . 'Homepage.html');
                            break;
                        case 'demo':
                            $home_top = file_get_contents($homep . 'Demo.html');
                            break;
                        case 'overview':
                            $home_top = file_get_contents($homep . 'Overview.html');
                            break;
                        case 'team':
                            $home_top = file_get_contents($homep . 'Team.html');
                            break;
                        case 'testimonial':
                            $home_top = file_get_contents($homep . 'Testimonial.html');
                            break;
                        default :
                            $home_top = '<div class="course_description"></div>';
                    }
                }
                $home_top = str_replace('{WEB_PATH}', api_get_path(WEB_PATH), $home_top);
                $home_top = str_replace('{CURRENT_CSS_PATH}', api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets') . '/templates.css', $home_top);
                $home_top = str_replace('{DEFAULT_CSS_PATH}', api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets') . '/default.css', $home_top);

                // replace to curren template css path
                if (preg_match('!/css/(.+)/templates.css!', $home_top, $matches)) {
                    $home_top = str_replace('/' . $matches[1] . '/templates.css', '/' . api_get_setting('stylesheets') . '/templates.css', $home_top);
                }
                // replace to curren default css path
                if (preg_match('!/css/(.+)/default.css!', $home_top, $matches)) {
                    $home_top = str_replace('/' . $matches[1] . '/default.css', '/' . api_get_setting('stylesheets') . '/default.css', $home_top);
                }
                // remove default.css
                if (preg_match('/(<link href="(.*)\/default\.css" rel="(.*)" type="(.*)" \/>)/i', $home_top)) {
                    $home_top = preg_replace('/(<link href="(.*)\/default\.css" rel="(.*)" type="(.*)" \/>)/i', '', $home_top);
                }

                break;
            case 'edit_notice':
                // This request is only the preparation for the update of the home_notice
                $home_notice = '';
                if (is_file($homep . $noticef . '_' . $lang . $ext)
                        && is_readable($homep . $noticef . '_' . $lang . $ext)) {
                    $home_notice = file($homep . $noticef . '_' . $lang . $ext);
                } elseif (is_file($homep . $noticef . $lang . $ext)
                        && is_readable($homep . $noticef . $lang . $ext)) {
                    $home_notice = file($homep . $noticef . $lang . $ext);
                } else {
                    $errorMsg = get_lang('HomePageFilesNotReadable');
                }
                $notice_title = strip_tags($home_notice[0]);
                $notice_text = strip_tags(str_replace('<br />', "\n", $home_notice[1]), '<a><img>');
                break;
            case 'edit_news':
                // This request is the preparation for the update of the home_news page
                $home_news = '';
                if (is_file($homep . $newsf . '_' . $lang . $ext)
                        && is_readable($homep . $newsf . '_' . $lang . $ext)) {
                    $home_news = file_get_contents($homep . $newsf . '_' . $lang . $ext);
                    //			$home_news=file($homep.$newsf.$ext);
                    //			$home_news=implode('',$home_news);
                } elseif (is_file($homep . $newsf . $lang . $ext)
                        && is_readable($homep . $newsf . $lang . $ext)) {
                    $home_news = file_get_contents($homep . $newsf . $lang . $ext);
                } else {
                    $errorMsg = get_lang('HomePageFilesNotReadable');
                }
                break;
            case 'insert_link':
                // This request is the preparation for the addition of an item in home_menu
                $home_menu = '';
                $menuf = ($action == 'edit_tabs') ? $menutabs : $menuf;
                if (is_file($homep . $menuf . '_' . $lang . $ext)
                        && is_readable($homep . $menuf . '_' . $lang . $ext)) {
                    $home_menu = file($homep . $menuf . '_' . $lang . $ext);
                } elseif (is_file($homep . $menuf . $lang . $ext)
                        && is_readable($homep . $menuf . $lang . $ext)) {
                    $home_menu = file($homep . $menuf . $lang . $ext);
                } else {
                    $errorMsg = get_lang('HomePageFilesNotReadable');
                }
                break;
            case 'insert_tabs':
                // This request is the preparation for the addition of an item in home_menu
                $home_menu = '';
                if (is_file($homep . $menutabs . '_' . $lang . $ext)
                        && is_readable($homep . $menutabs . '_' . $lang . $ext)) {
                    $home_menu = file($homep . $menutabs . '_' . $lang . $ext);
                } elseif (is_file($homep . $menutabs . $lang . $ext)
                        && is_readable($homep . $menutabs . $lang . $ext)) {
                    $home_menu = file($homep . $menutabs . $lang . $ext);
                } else {
                    $errorMsg = get_lang('HomePageFilesNotReadable');
                }
                break;
            case 'edit_tabs':
            case 'edit_link':
                // This request is the preparation for the edition of the links array
                $home_menu = '';
                $menuf = ($action == 'edit_tabs') ? $menutabs : $menuf;

                if (is_file($homep . $menuf . '_' . $lang . $ext)
                        && is_readable($homep . $menuf . '_' . $lang . $ext)) {
                    $home_menu = file($homep . $menuf . '_' . $lang . $ext);
                } elseif (is_file($homep . $menuf . $lang . $ext)
                        && is_readable($homep . $menuf . $lang . $ext)) {
                    $home_menu = file($homep . $menuf . $lang . $ext);
                } else {
                    $errorMsg = get_lang('HomePageFilesNotReadable');
                }


                $link_index = intval($_GET['link_index']);
                $target_blank = false;
                $link_name = '';
                $link_url = '';

                // For each line of the home_menu file
                foreach ($home_menu as $key => $enreg) {
                    // Check if the current item is the one we want to update

                    if ($key == $link_index) {
                        // This is the link we want to update
                        // Check if the target should be "_blank"
                        if (strstr($enreg, 'target="_blank"')) {
                            $target_blank = true;
                        }
                        // Remove dangerous HTML tags from the link itself (this is an
                        // additional measure in case a link previously contained
                        // unsecure tags)
                        $link_name = strip_tags($enreg);

                        // Get the contents of "href" attribute in $link_url
                        $enreg = explode('href="', $enreg);
                        list($link_url) = explode('"', $enreg[sizeof($enreg) - 1]);


                        $link_url = str_replace('{WEB_PATH}', api_get_path(WEB_PATH), $link_url);

                        if ($_configuration['multiple_access_urls'] == true) {
                            $link_ms_url = explode('?include=', $link_url);
                            $filename = $link_ms_url[1];
                        }

                        // If the link contains the web root of this portal, then strip
                        // it off and keep only the name of the file that needs edition
                        if (strstr($link_url, $_configuration['root_web']) && strstr($link_url, '?include=') && $_configuration['multiple_access_urls'] != true) {
                            $link_url = explode('?include=', $link_url);

                            $filename = $link_url[sizeof($link_url) - 1];

                            if (!strstr($filename, '/') && strstr($filename, '.html')) {
                                // Get oonly the contents of the link file
                                $link_html = file(api_get_path(SYS_PATH) . 'home/' . $filename);
                                $link_html = implode('', $link_html);

                                // replace {CSS} by link to templates.css
                                $link_html = str_replace('{CURRENT_CSS_PATH}', api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets') . '/templates.css', $link_html);
                                $link_html = str_replace('{DEFAULT_CSS_PATH}', api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets') . '/default.css', $link_html);
                                $link_html = str_replace('{WEB_PATH}', api_get_path(WEB_PATH), $link_html);

                                // replace to curren template css path
                                if (preg_match('!/css/(.+)/templates.css!', $link_html, $matches)) {
                                    $link_html = str_replace('/' . $matches[1] . '/templates.css', '/' . api_get_setting('stylesheets') . '/templates.css', $link_html);
                                }
                                // replace to curren default css path
                                if (preg_match('!/css/(.+)/default.css!', $link_html, $matches)) {
                                    $link_html = str_replace('/' . $matches[1] . '/default.css', '/' . api_get_setting('stylesheets') . '/default.css', $link_html);
                                }
                                // remove default.css
                                if (preg_match('/(<link href="(.*)\/default\.css" rel="(.*)" type="(.*)" \/>)/i', $link_html)) {
                                    $link_html = preg_replace('/(<link href="(.*)\/default\.css" rel="(.*)" type="(.*)" \/>)/i', '', $link_html);
                                }

                                $link_url = '';
                            } else {
                                $filename = '';
                            }
                        } else {
                            // Load file to edit when multisite feature is activated
                            if (!strstr($filename, '/') && strstr($filename, '.html')) {
                                // Get oonly the contents of the link file
                                $link_html = file($homep_new . $filename);

                                $link_html = implode('', $link_html);
                                // replace {CSS} by link to templates.css
                                $link_html = str_replace('{CURRENT_CSS_PATH}', api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets') . '/templates.css', $link_html);
                                $link_html = str_replace('{DEFAULT_CSS_PATH}', api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets') . '/default.css', $link_html);
                                $link_html = str_replace('{WEB_PATH}', api_get_path(WEB_PATH), $link_html);

                                // replace to curren template css path
                                if (preg_match('!/css/(.+)/templates.css!', $link_html, $matches)) {
                                    $link_html = str_replace('/' . $matches[1] . '/templates.css', '/' . api_get_setting('stylesheets') . '/templates.css', $link_html);
                                }
                                // replace to curren default css path
                                if (preg_match('!/css/(.+)/default.css!', $link_html, $matches)) {
                                    $link_html = str_replace('/' . $matches[1] . '/default.css', '/' . api_get_setting('stylesheets') . '/default.css', $link_html);
                                }
                                // remove default.css
                                if (preg_match('/(<link href="(.*)\/default\.css" rel="(.*)" type="(.*)" \/>)/i', $link_html)) {
                                    $link_html = preg_replace('/(<link href="(.*)\/default\.css" rel="(.*)" type="(.*)" \/>)/i', '', $link_html);
                                }

                                $link_url = '';
                            }
                        }
                        break;
                    }
                }
                break;
        }//end of second switch($action) (when POST['formSent'] was not set, yet)
    }// end of "else" in if($_POST['formSent']) condition
} else { //if $action is empty, then prepare a list of the course categories to display (?)
    $result = Database::query("SELECT name FROM $tbl_category WHERE parent_id IS NULL ORDER BY tree_pos", __FILE__, __LINE__);
    $Categories = Database::store_result($result);
}
require_once api_get_path(LIBRARY_PATH) . "xajax_0.5/xajax_core/xajax.inc.php";
$xajax = new xajax();
$xajax->setCharEncoding('ISO-8859-1');
$xajax->setFlag("decodeUTF8Input", true);
$xajax->setFLag("debug", false);
$xajax->registerFunction('uploadLogo');
$xajax->registerFunction('deleteLogo');
$xajax->processRequest();

function deleteLogo() {
    global $_configuration;
    global $access_url_id;
    
    $objResponse = new xajaxResponse();
    
    $files = glob(api_get_path(SYS_PATH).'home/logo/logo-dokeos-'.$access_url_id.'-*');
    if(count($files) < 1 && !$_configuration['multiple_access_urls']){
        $files = glob(api_get_path(SYS_PATH).'home/logo/'.'*');
    }
    
    if (count($files) > 0) {
        foreach ($files as $path_file) {
            $infoFile = pathinfo($path_file);
            if ($infoFile['extension'] != 'html') {
                unlink($path_file);
            }
        }
    }
    $objResponse->redirect(api_get_path(WEB_PATH) . 'main/admin/configure_homepage.php?action=logo');
    return $objResponse;
}

function uploadLogo($file) {
    $objResponse = new xajaxResponse();
    $ext = extension($file);
    $file = 'logo-dokeos.' . $ext;
    $path_logo = api_get_path(WEB_PATH) . 'home/logo/' . $file;
    $html = '<img src="' . $path_logo . '" />';
    $objResponse->assign("divLogo", "innerHTML", $html);
    $objResponse->redirect(api_get_path(WEB_PATH) . 'main/admin/configure_homepage.php?action=logo');
    return $objResponse;
}

function extension($file) {
    $file = strtolower($file);
    $extension = split("[/\\.]", $file);
    $n = count($extension) - 1;
    $extension = $extension[$n];
    return $extension;
}

// Display the header
Display::display_header($tool_name);

$xajax->printJavascript(api_get_path(WEB_PATH) . 'main/inc/lib/xajax_0.5/');

if (isset($_REQUEST['language'])) {
    $language = $_REQUEST['language'];
    $add_param = '?language=' . $language;
} else {
    $language = api_get_interface_language();
    $add_param = '?language=' . $language;
}

// display the tool title
//api_display_tool_title($tool_name);
if ($action == 'open_link' && !empty($link)) {
    // Back to home page
    echo '<div class="actions">';
    echo '<a href="configure_homepage.php">' . Display::return_icon('pixel.gif', get_lang("Back"), array('class' => 'toolactionplaceholdericon toolactionback')) . get_lang('Back') . '</a>';
    echo '</div>';
} else {
    ?>
    <div class="actions">
        <div class="float_l">
            <a href="<?php echo api_get_self(); ?>"><?php echo Display::return_icon('pixel.gif', get_lang("HomePage"), array('class' => 'toolactionplaceholdericon toolactionhomepage')) . get_lang('HomePage'); ?></a>
            <!--<a href="<?php echo api_get_self(); ?>?action=edit_top&amp;edit_template=false"><?php echo Display::return_icon('pixel.gif', get_lang("EditHomePage"), array('class' => 'toolactionplaceholdericon tooledithome')) . get_lang('EditHomePage'); ?></a>-->
            <!--<a href="<?php //echo api_get_self(); ?>?action=insert_link&amp;edit_template=false&amp;from_template=true"><?php// echo Display::return_icon('pixel.gif', get_lang("CreatePageFromATemplate"), array('class' => 'toolactionplaceholdericon toolactiontemplates')) . get_lang('CreatePageFromATemplate'); ?></a>-->
            <a href="<?php echo api_get_path(WEB_CODE_PATH);?>index.php?module=node&cmd=PageHome&func=getForm"><?php echo Display::return_icon('pixel.gif', get_lang("CreatePageFromATemplate"), array('class' => 'toolactionplaceholdericon toolactiontemplates')) . get_lang('CreatePageFromATemplate'); ?></a>
            <a href="<?php echo api_get_path(WEB_CODE_PATH);?>index.php?module=node&cmd=PageHome&func=Index"><?php echo Display::return_icon('pixel.gif', get_lang("CreatePageFromATemplate"), array('class' => 'toolactionplaceholdericon toolactionlist')) . get_lang('ListPage'); ?></a>
            <a href="slides_management.php<?php echo $add_param; ?>"><?php echo Display::return_icon('pixel.gif', get_lang("SlidesManagement"), array('class' => 'toolactionplaceholdericon toolallpages')) . get_lang('SlidesManagement'); ?></a>
            <!--a href="<!?php echo api_get_self(); ?>?action=logo"><!?php echo Display::return_icon('pixel.gif', get_lang("Edit"), array('class' => 'toolactionplaceholdericon dokeos_toolaction')) . get_lang('Logo'); ?></a-->
            <!--<a href="<!?php echo api_get_self(); ?>?action=insert_link"><!?php echo Display::return_icon('form-plus.png', get_lang('InsertLink')) . get_lang('InsertLink'); ?></a>-->
        </div>
        <div class="float_r" style="margin-right:10px;">
    <?php  api_display_language_form(true, '', true); ?>
        </div>
        <div style="clear: both;"></div>
    </div>
    <?php
}
if (($action == 'edit_top') || (isset($_GET['from_template']) && $_GET['from_template'] == 'true')) {
    $add_param_edit_template = '';
    if (isset($_GET['edit_template'])) {
        $add_param_edit_template = '&amp;edit_template=' . Security::remove_XSS($_GET['edit_template']);
    }
    $add_param_from_template = '';
    if (isset($_GET['from_template'])) {
        $add_param_from_template = '&amp;from_template=true';
    }
    $form_scenario = new FormValidator('configure_homepage_scenario');
    // 5 buttons for display the scenarios in the platform home page
    $html_buttons = '<div align="center"><br/>
        <table class="gallery_scenario" style=""><tbody>
        <tr><td><a href="' . api_get_self() . '?scenario=homepage&amp;action=' . $action . $add_param_edit_template . $add_param_from_template . '">
        <span class="section_scenario width_scenario_template_button" >
            <span class="sectiontitle">' . get_lang('TemplateHomepage') . '</span>
            <span class="">' . Display::return_icon('global_64.png', get_lang('TemplateHomepage')) . '</span>
        </span></a></td>
        <td><a href="' . api_get_self() . '?scenario=demo&amp;action=' . $action . $add_param_edit_template . $add_param_from_template . '">
        <span class="section_scenario width_scenario_template_button" >
            <span class="sectiontitle">' . get_lang('TemplateDemo') . '</span>
            <span class="">' . Display::return_icon('mouse_64.png', get_lang('TemplateDemo')) . '</span>
        </span></a></td>
        <td><a href="' . api_get_self() . '?scenario=overview&amp;action=' . $action . $add_param_edit_template . $add_param_from_template . '">
        <span class="section_scenario width_scenario_template_button ">
            <span class="sectiontitle">' . get_lang('TemplateOverview') . '</span>
            <span class="">' . Display::return_icon('info_64.png', get_lang('TemplateOverview')) . '</span>
        </span></a></td>
        <td><a href="' . api_get_self() . '?scenario=team&amp;action=' . $action . $add_param_edit_template . $add_param_from_template . '">
        <span class="section_scenario width_scenario_template_button" >
            <span class="sectiontitle">' . get_lang('TemplateTeam') . '</span>
            <span class="">' . Display::return_icon('woman_white.png', get_lang('TemplateTeam')) . '</span>
        </span></a></td>
        <td><a href="' . api_get_self() . '?scenario=testimonial&amp;action=' . $action . $add_param_edit_template . $add_param_from_template . '">
        <span class="section_scenario width_scenario_template_button" >
            <span class="sectiontitle">' . get_lang('TemplateTestimonial') . '</span>
            <span class="">' . Display::return_icon('video_x_generic_64.png', get_lang('TemplateTestimonial')) . '</span>
        </span></a></td>
        <td><a href="' . api_get_self() . '?scenario=none&amp;action=' . $action . $add_param_edit_template . $add_param_from_template . '">
        <span class="section_scenario width_scenario_template_button" >
            <span class="sectiontitle">' . get_lang('NoTemplate') . '</span>
            <span class="">' . Display::return_icon('noscenario64.png', get_lang('NoTemplate')) . '</span>
        </span></a></td>
        </tr></tbody>
        </table></div>';
    $form_scenario->addElement('html', $html_buttons);
    $form_scenario->display();
}
// start the content div
$content_class = "";
if (strstr($_SERVER["HTTP_USER_AGENT"], "MSIE")) { // Fix IE issue
    $content_class = "width_home_template";
}

echo '<div class="' . $content_class . '" id="content">';

$slides_management_table = Database :: get_main_table(TABLE_MAIN_SLIDES_MANAGEMENT);

$sql = "SELECT * FROM $slides_management_table LIMIT 1";
$rs = Database::query($sql, __FILE__, __LINE__);
while ($row = Database::fetch_array($rs)) {
    $slide_speed = $row['slide_speed'];
    $show_slide = $row['show_slide'];
}
$slide_speed = ($slide_speed == 0) ? 1 : $slide_speed;
$slide_speed_millisec = intval($slide_speed)*1000;

echo "<script type='text/javascript'>
    $(document).ready(function() {
        $('.box_skitter_large').skitter({
            animation: 'random', 
            numbers_align: 'center', 
            dots: true, 
            preview: true, 
            focus: false, 
            focus_position: 'leftTop', 
            controls: false, 
            controls_position: 'leftTop', 
            progressbar: false, 
            animateNumberOver: { 'backgroundColor':'#555' }, 
            enable_navigation_keys: true,
            interval: " . $slide_speed_millisec . "
        });
    });
</script>";

$slides_table = Database :: get_main_table(TABLE_MAIN_SLIDES);



$sql = "SELECT * FROM $slides_table WHERE language = '" . Database::escape_string($language) . "' ORDER BY display_order";
$res = Database::query($sql, __FILE__, __LINE__);
$num_of_slides = Database::num_rows($res);
$slides = array();
$img_dir = api_get_path(WEB_PATH) . 'home/default_platform_document/';

if ($num_of_slides <> 0) {
    while ($row = Database::fetch_array($res)) {
        $image = $img_dir . $row['image'];
        if (empty($row['title'])) {
            $title = get_lang('Title');
        } else {
            $title = $row['title'];
        }
        if (empty($row['link'])) {
            $link = '#';
        } else {
            $link = $row['link'];
        }
        if (empty($row['alternate_text'])) {
            $alternate_text = get_lang('AltText');
        } else {
            $alternate_text = $row['alternate_text'];
        }
        $slides[] = array('image' => $image, 'title' => $title, 'link' => $link, 'caption' => $row['caption'], 'alttext' => $alternate_text);
    }
}


switch ($action) {
    case 'open_link':
        if (!empty($_GET['link'])) {
            // $link is only set in case of action=open_link and is filtered
            //include($homep.$link);
            $my_link = Security::remove_XSS($_GET['link']);
            $link_content = file_get_contents($homep . $my_link);
          
            $link_content = str_replace('{CURRENT_CSS_PATH}', api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets') . '/templates.css', $link_content);
            $link_content = str_replace('{DEFAULT_CSS_PATH}', api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets') . '/default.css', $link_content);
            $link_content = str_replace('{WEB_PATH}', api_get_path(WEB_PATH), $link_content);
            echo $link_content;
        }
        break;
    case 'edit_notice':
        //------------  Display for edit_notice case --------------
        ?>
        <form action="<?php echo api_get_self(); ?>?action=<?php echo $action; ?>" method="post" style="margin:0px;">
            <div class="row"><div class="form_header"><?php echo $tool_name; ?></div></div>
            <input type="hidden" name="formSent" value="1"/>

        <?php
        if (!empty($errorMsg)) {
            //echo '<tr><td colspan="2">';
            Display::display_normal_message($errorMsg);
            //echo '</td></tr>';
        }
        ?>

            <table cellspacing="5" border="0" style="border-collapse:separate;">
                <tr><td colspan="2"><?php echo '<span>' . get_lang('LetThoseFieldsEmptyToHideTheNotice') . '</span>'; ?></tr>
                <tr>
                    <td nowrap="nowrap"><?php echo get_lang('NoticeTitle'); ?> :</td>
                    <td><input type="text" name="notice_title" size="30" maxlength="50" value="<?php echo api_htmlentities($notice_title, ENT_QUOTES, $charset); ?>" style="width: 350px;"/></td>
                </tr>
                <tr>
                    <td nowrap="nowrap" valign="top"><?php echo get_lang('NoticeText'); ?> :</td>
                    <td><textarea name="notice_text" cols="30" rows="5" wrap="virtual" style="width: 350px;"><?php echo api_htmlentities($notice_text, ENT_QUOTES, $charset); ?></textarea></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><button class="save" type="submit" value="<?php echo get_lang('Ok'); ?>"><?php echo get_lang('Ok'); ?></button></td>
                </tr>
            </table>
        </form>
        <?php
        break;
    case 'insert_tabs':
    case 'edit_tabs':
    case 'insert_link':
    case 'edit_link':

        if (!empty($errorMsg)) {
            Display::display_normal_message($errorMsg, false, true); //main API
        }
        $add_param_from_template = '';
        $add_param_from_edit_template = '';
        if (isset($_GET['from_template'])) {
            $add_param_from_template = '&amp;from_template=true';
        }
        if (isset($_GET['edit_template']) && isset($_GET['edit_template'])) {
            $add_param_from_edit_template = '&amp;edit_template=false';
        }
        $default = array();
        $form = new FormValidator('configure_homepage_' . $action, 'post', api_get_self() . '?action=' . $action . $add_param_from_template . $add_param_from_edit_template, '', array('style' => 'margin: 0px;'));
        $renderer = & $form->defaultRenderer();
        $renderer->setFormTemplate('<form {attributes}><table border="0" width="100%" style="border-collapse:separate;">{content}</table></form>');
        $renderer->setElementTemplate('{element}');
        $renderer->setRequiredNoteTemplate('');
        $form->addElement('header', '', $tool_name);
        $form->addElement('hidden', 'formSent', '1');
        // Alow create a page from a template
        if (isset($_GET['from_template'])) {
            $form->addElement('hidden', 'create_from_template', '1');
        }
        $form->addElement('hidden', 'link_index', ($action == 'edit_link' || $action == 'edit_tabs') ? $link_index : '0');
        $form->addElement('hidden', 'filename', ($action == 'edit_link' || $action == 'edit_tabs') ? $filename : '');
        // Link name
        $form->addElement('html', '<tr><td nowrap="nowrap" style="width: 15%;">' . get_lang('LinkName') . ' :</td><td>');
        $default['link_name'] = api_htmlentities($link_name, ENT_QUOTES, $charset);
        $form->addElement('text', 'link_name', get_lang('LinkName'), array('size' => '30', 'maxlength' => '50', 'style' => 'width: 350px;'));
        $form->addElement('html', '</td></tr>');

        // Url of link
        if (!isset($_GET['edit_template'])) {
            $form->addElement('html', '<tr><td nowrap="nowrap">' . get_lang('LinkURL') . ' (' . get_lang('Optional') . ') :</td><td>');
            $default['link_url'] = empty($link_url) ? 'http://' : api_htmlentities($link_url, ENT_QUOTES, $charset);
            $form->addElement('text', 'link_url', get_lang('LinkName'), array('size' => '30', 'maxlength' => '100', 'style' => 'width: 350px;'));
            $form->addElement('html', '</td></tr>');
        }
        if ($action == 'insert_link' || $action == 'insert_tabs') {
            $form->addElement('html', '<tr><td nowrap="nowrap">' . get_lang('InsertThisLink') . ' :</td>');
            $form->addElement('html', '<td><select name="insert_where"><option value="-1">' . get_lang('FirstPlace') . '</option>');
            if (is_array($home_menu)) {
                foreach ($home_menu as $key => $enreg) {
                    $form->addElement('html', '<option value="' . $key . '" ' . ($formSent && $insert_where == $key ? 'selected="selected"' : '') . ' >' . get_lang('After') . ' &quot;' . trim(strip_tags($enreg)) . '&quot;</option>');
                }
            }
            $form->addElement('html', '</select></td></tr>');
        }
        // Checkbox - Open in new window
        $form->addElement('html', '<tr><td nowrap="nowrap">' . get_lang('OpenInNewWindow') . '</td><td>');
        $target_blank_checkbox = & $form->addElement('checkbox', 'target_blank', '', '&nbsp;' . get_lang('Yes'), 1);
        if ($target_blank)
            $target_blank_checkbox->setChecked(true);
        $form->addElement('html', '</td></tr>');

        //if($action == 'edit_link' && empty($link_url))
        if ($action == 'edit_link' && (empty($link_url) || $link_url == 'http://') || $_configuration['multiple_access_urls'] == true) {
            $form->addElement('html', '</table><table border="0" cellpadding="5" cellspacing="0" width="100%" style="border-collapse:separate;"><tr><td>');
            $form->addElement('html', '</td></tr><tr><td>');
            if (api_get_setting('wcag_anysurfer_public_pages') == 'true') {
                $form->addElement('html', WCAG_Rendering::create_xhtml(isset($_POST['link_html']) ? $_POST['link_html'] : $link_html));
            } else {
                $default['link_html'] = isset($_POST['link_html']) ? $_POST['link_html'] : $link_html;
                $form->add_html_editor('link_html', '', true, false, array('ToolbarSet' => 'PortalHomePage', 'Width' => '100%', 'Height' => '600'));
            }
            $form->addElement('html', '</td></tr><tr><td>');
            $form->addElement('style_submit_button', null, get_lang('Save'), 'class="save"');
            $form->addElement('html', '</td></tr>');
        } elseif (isset($_GET['edit_template']) && isset($_GET['from_template'])) {
            $form->addElement('html', '</table><table border="0" cellpadding="5" cellspacing="0" width="100%" style="border-collapse:separate;"><tr><td>');
            $form->addElement('html', '</td></tr><tr><td>');

            // Load scenarios
            if (isset($_GET['scenario'])) {
                switch ($_GET['scenario']) {
                    case 'homepage':
                        $link_content = file_get_contents($homep . 'Homepage.html');
                        break;
                    case 'demo':
                        $link_content = file_get_contents($homep . 'Demo.html');
                        break;
                    case 'overview':
                        $link_content = file_get_contents($homep . 'Overview.html');
                        break;
                    case 'team':
                        $link_content = file_get_contents($homep . 'Team.html');
                        break;
                    case 'testimonial':
                        $link_content = file_get_contents($homep . 'Testimonial.html');
                        break;
                    default :
                        $link_content = '<div class="course_description"></div>';
                }
                $link_content = str_replace('{WEB_PATH}', api_get_path(WEB_PATH), $link_content);
                $link_content = str_replace('{CURRENT_CSS_PATH}', api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets') . '/templates.css', $link_content);
                $link_content = str_replace('{DEFAULT_CSS_PATH}', api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets') . '/default.css', $link_content);
                $link_content = str_replace('{rel_path}', api_get_path(REL_PATH), $link_content);

                // replace to curren template css path
                if (preg_match('!/css/(.+)/templates.css!', $link_content, $matches)) {
                    $link_content = str_replace('/' . $matches[1] . '/templates.css', '/' . api_get_setting('stylesheets') . '/templates.css', $link_content);
                }
                // replace to curren default css path
                if (preg_match('!/css/(.+)/default.css!', $link_html, $matches)) {
                    $link_content = str_replace('/' . $matches[1] . '/default.css', '/' . api_get_setting('stylesheets') . '/default.css', $link_content);
                }
                // remove default.css
                if (preg_match('/(<link href="(.*)\/default\.css" rel="(.*)" type="(.*)" \/>)/i', $link_content)) {
                    $link_content = preg_replace('/(<link href="(.*)\/default\.css" rel="(.*)" type="(.*)" \/>)/i', '', $link_content);
                }
            } else {
                $link_content = '&nbsp;';
            }
            $default['link_html'] = $link_content;
            $form->add_html_editor('link_html', '', true, false, array('ToolbarSet' => 'PortalHomePage', 'Width' => '100%', 'Height' => '600'));
            $form->addElement('html', '</td></tr><tr><td>');
            $form->addElement('style_submit_button', null, get_lang('Save'), 'class="save"');
            $form->addElement('html', '</td></tr>');
        } else {
            $form->addElement('html', '<tr><td>&nbsp;</td><td>');
            $form->addElement('style_submit_button', null, get_lang('Save'), 'class="save"');
            $form->addElement('html', '</td></tr>');
        }

        $form->setDefaults($default);
        $form->display();

        break;
    case 'edit_top':
    case 'edit_news':
        if ($action == 'edit_top') {
            $name = $topf;
            $open = $home_top;
        } else {
            $name = $newsf;
            $open = @file_get_contents($homep . $newsf . '_' . $lang . $ext);
        }

        if (!empty($errorMsg)) {
            Display::display_normal_message($errorMsg, false, true); //main API
        }

        $default = array();
        $form = new FormValidator('configure_homepage_' . $action, 'post', api_get_self() . '?action=' . $action, '', array('style' => 'margin: 0px;'));
        $renderer = &$form->defaultRenderer();
        $home_page_explanation = '';
        if (isset($_GET['edit_template']) && $_GET['edit_template'] == 'false') {
            $home_page_title = get_lang('EditHomePage');
            $home_page_explanation = get_lang('EditHomePageDescription');
        } elseif (isset($_GET['edit_template']) && $_GET['edit_template'] == 'true') {
            $home_page_title = get_lang('EditHomeTemplates');
        } else {
            $renderer->setHeaderTemplate('');
        }
        //$renderer->setHeaderTemplate('');
//        $form->addElement('header', '', $home_page_title);
        $form->addElement('html', '<tr><td><div class="row"><div class="form_header">' . $home_page_title . '</div></div></td></tr>');
        $form->addElement('html', '<tr><td><div>' . $home_page_explanation . '</div><br/></td></tr>');
        $renderer->setFormTemplate('<form {attributes}><table border="0" width="100%" style="border-collapse:separate;">{content}</table></form>');
        $renderer->setElementTemplate('<tr><td>{element}</td></tr>');
        $renderer->setRequiredNoteTemplate('');
        $form->addElement('hidden', 'formSent', '1');

        if ($action == 'edit_news') {
            $_languages = api_get_languages();
            $html = '<tr><td>' . get_lang('ChooseNewsLanguage') . ' : ';
            $html .= '<select name="news_languages">';
            $html .= '<option value="all">' . get_lang('AllLanguages') . '</option>';
            foreach ($_languages['name'] as $key => $value) {
                $english_name = $_languages['folder'][$key];
                if ($language == $english_name) {
                    $html .= '<option value="' . $english_name . '" selected="selected">' . $value . '</option>';
                } else {
                    $html .= '<option value="' . $english_name . '">' . $value . '</option>';
                }
            }
            $html .= '</select></td></tr>';
            $form->addElement('html', $html);
        }
        if (api_get_setting('wcag_anysurfer_public_pages') == 'true') {
            //TODO: review these lines
            // Print WCAG-specific HTML editor
            $html = '<tr><td>';
            $html .= WCAG_Rendering::create_xhtml($open);
            $html .= '</td></tr>';
            $form->addElement('html', $html);
        } else {
            $content = str_replace('{rel_path}', api_get_path(REL_PATH), $open);
            $default[$name] = $content;
            $form->add_html_editor($name, '', true, false, array('ToolbarSet' => 'PortalHomePage', 'Width' => '100%', 'Height' => '600'));
        }
        $form->addElement('style_submit_button', null, get_lang('Save'), 'class="save"');
        $form->setDefaults($default);
        $form->display();

        break;
    case 'logo':
        require_once (api_get_path(LIBRARY_PATH).'SimpleImage.lib.php');
        $path_logo = api_get_path(SYS_PATH) . 'home/logo/';
        $html = '<form name="formLogo" id="formLogo" method="post" action="" onsubmit="return false;">';
        $html.='<h3 class="orange">' . get_lang('File_upload') . ' (200 x 50 pixels)</h3>';
        $html.='<center><table>';
        $html.='<tr>';
        $html.='<td><input style="width:190px; margin-top:20px;margin-bottom:10px;" readonly="readonly" type="text" name="nameLogo" id="nameLogo" />&nbsp;&nbsp;<button class="btnUpload" name="btnUpload" id="btnUpload" >' . get_lang('NoImage') . '</button></td>';
        $html.='</tr>';
        $html.='<tr>';
        
        
        $files = glob($path_logo.'logo-dokeos-'. $access_url_id .'-*');
        if(count($files) < 1 && !$_configuration['multiple_access_urls']) {
            $files = glob($path_logo . '*');
        }
        
        if (count($files) > 0) {
            foreach($files as $path_file) {
                $path_split = pathinfo($path_file);
                
                if ($path_split['extension'] == 'gif' || $path_split['extension'] == 'png' || $path_split['extension'] == 'jpg' || $path_split['extension'] == 'jpeg') {
                    $img = new SimpleImage();                    
                    $img->load($path_file)->resize(200, 50)->save($path_file);
                    $html.='<td><div style=" display: table-cell; vertical-align: middle; text-align:center; width:200px; height:50px; box-shadow: 0 0 6px #999999;overflow:hidden;" id="divLogo"><img src="' . api_get_path(WEB_PATH) . 'home/logo/' . $path_split['basename'] . '" /></div>
                            <div style="padding-left:100px;margin-top:5px;float:left; text-align:right;" id="divDimention"></div><div style="margin-left: 145px;    margin-top: 5px;"><a href="#" onclick="xajax_deleteLogo()">' . Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')) . get_lang('Delete') . '</a></div></td>';
                }
            }
        } else {
            $html.='<td><div style="display: table-cell; text-align:center; vertical-align: middle; width:200px; height:50px; box-shadow: 0px 0px 6px #999999;overflow:hidden;" id="divLogo"></div></td>';
        }
        $html.='</tr>';
        $html.='<tr>';
        $html.='<td>&nbsp;</td>';
        $html.='</tr>';
        $html.='</table></center>';
        $html.='</form>';
        echo $html;
        break;
    default: // When no action applies, default page to update campus homepage
        ?>
        <table border="0" cellpadding="5" cellspacing="0" width="100%" style="border-collapse:separate;">
            <tr>
                <td valign="top" width="20%" rowspan="3" style="padding-left:0px;vertical-align:top;">
                      <div class="menu_notice" >

                      </div>
                      <?php
                            $language_id = api_get_language_id($language_interface);
                            $accessUrlId = api_get_current_access_url_id();            
                            if ($accessUrlId< 0) {
                                $accessUrlId = 1;
                            }
                            $tblNode = Database::get_main_table(TABLE_MAIN_NODE);
                            $sql="SELECT id,title,content,active FROM $tblNode 
                                    WHERE active = ".ACTIVE." 
                                    AND node_type =".NODE_NOTICE." 
                                    AND access_url_id =".$accessUrlId." 
                                    AND language_id IN (". $language_id.",0)";

                            $result = Database::query($sql,__FILE__,__LINE__);
                            $row = Database::fetch_array($result);


                            if (isset($_SESSION['user_language_choice'])) {
                                $user_selected_language = $_SESSION['user_language_choice']; 
                                //$language_id = api_get_language_id($user_selected_language);
                            }
                        ?>
                        <div class="section">
                            <a href="<?php echo api_get_path(WEB_CODE_PATH); ?>index.php?module=node&cmd=Notice&func=edit&nodeId=<?php echo $row[0];?>&language=<?php echo $user_selected_language; ?>"><?php echo Display::return_icon('pixel.gif', get_lang("EditNotice"), array('class' => 'actionplaceholdericon actionedit')) . get_lang('EditNotice'); ?></a>
                            
                        </div>
                        <div>
                                <?php
                                $home_notice = '';
                                if (file_exists($homep . $noticef . '_' . $lang . $ext)) {
                                    $home_notice = @file_get_contents($homep . $noticef . '_' . $lang . $ext);
                                } else {
                                    $home_notice = @file_get_contents($homep . $noticef . $ext);
                                }
                                
                                 if ($row['active']!=0) {
                                    echo '<div class="section">';
                                    echo '<table><tr>';
                                            echo '<th>'.$row['title'].'</th>';
                                            echo '</tr><td style="padding-top:2px">'.$row['content'].'</td></tr>';
                                    echo '</table>';
                                    echo '</div>';
                                 }
    //                                if (!empty($home_notice)) {
    //                                    echo '<div class="section">';
    //                                    //echo $home_notice;   ///MUESTRA EL NOTICE
    //                                    echo '</div>';
    //                                }
                                echo '<div class="section nobullets">';
                                $access_url_id = 1;
                                // we only show the category options for the main Dokeos installation
                                if ($_configuration['multiple_access_urls'] == true) {
                                    $access_url_id = api_get_current_access_url_id();
                                }

                                if ($access_url_id == 1) {
                                    echo '<a href="course_category.php?origin=configure">' . Display::display_icon('pixel.gif', get_lang('EditCategories'), array('class' => 'actionplaceholdericon actionedit')) . get_lang('EditCategories') . '</a>';
                                }
                                echo '<ul style="list-style-type:none;margin-left:0px;padding-left:10px;">';
                                if ($access_url_id == 1) {
                                    if (sizeof($Categories)) {
                                        foreach ($Categories as $enreg) {
                                            echo '<li>' . Display::return_icon('pixel.gif', $enreg['name'], array('class' => 'actionplaceholdericon actionnewfolder')) . '&nbsp;' . $enreg['name'] . '</li>';
                                        }
                                        unset($Categories);
                                    } else {
                                        echo get_lang('NoCategories');
                                    }
                                }
                                echo '</ul>';
                                echo '</div>';
                                ?>
                    </div>
                    
                    
                    <div>
                        <?php
                           $menulinks = get_menu_links(MENULINK_CATEGORY_LEFTSIDE);
                           if(count($menulinks) > 0){
                               $css_type = !is_null($css_info['type']) ? $css_info['type'] : 'tablet';
                               if ($css_type == 'tablet') {
                                   echo '<div class="section menu-general nobullets">';
                                   echo api_display_tool_title(get_lang('MenuGeneral'), 'tablet_title');
                                   foreach($menulinks as $row){  //while($row = Database::fetch_array($res)){
                                       echo '<div><a href='.$row['link_path'].' target='.$row['target'].'>'.$row['title'].'</a></div>';
                                   }
                                   echo '</div>';
                               } else {
                                   echo "<div class=\"section menu-more\">", "<div class=\"sectiontitle\">" . get_lang("MenuGeneral") . "</div>";
                                   echo '<div class="sectioncontent nobullets">';
                                   echo '</div>';
                                   echo '</div>';
                               }
                           }
                         ?>
                    </div>
                    
                </td>
                <td width="80%" colspan="2" valign="top">
                    <table border="0" cellpadding="5" cellspacing="0" width="100%" style="border-collapse:separate;">
                        <tr>
                            <td colspan="2">
                                <div style="width:80%">
                                <?php
                                if ($show_slide == 1 && $num_of_slides > 0) {
                                    echo '<div id="container"><div class="box_skitter box_skitter_large"><ul>';
                                    foreach ($slides as $slide) {
                                        echo '<li>';
                                        $slide_content = '{img}';
                                        if (!empty($slide['link']) && $slide['link'] != '#') {
                                            $slide_content = '<a href="' . $slide['link'] . '" title="' . $slide['title'] . '">{img}</a>';
                                        }
                                        echo str_replace('{img}', '<img src="' . $slide['image'] . '" width="720" height="240" title="' . $slide['title'] . '" alt="' . $slide['alttext'] . '" />', $slide_content);
                                        echo '<div class="label_text">';
                                        if (!empty($slide['caption'])) {
                                            echo '<p>' . $slide['caption'] . '</p>';
                                        }
                                        echo '</div>';
                                        echo '</li>';
                                    }
                                    echo '</ul></div></div>';
                                }
                                ?>
                                </div>
                            </td>
                        </tr>
                       
        <?php
        //print home_top contents
        if (file_exists($homep . $topf . '_' . $lang . $ext)) {
            $home_top_temp = file_get_contents($homep . $topf . '_' . $lang . $ext);
        } else {
            $home_top_temp = file_get_contents($homep . $topf . $ext);
        }
      
        $open = str_replace('{rel_path}', api_get_path(REL_PATH), $home_top_temp);
        $open = str_replace('{WEB_PATH}', api_get_path(WEB_PATH), $open);
        $open = str_replace('{CURRENT_CSS_PATH}', api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets') . '/templates.css', $open);
        $open = str_replace('{DEFAULT_CSS_PATH}', api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets') . '/default.css', $open);

        // replace to current template css path
        if (preg_match('!/css/(.+)/templates.css!', $open, $matches)) {
            $open = str_replace('/' . $matches[1] . '/templates.css', '/' . api_get_setting('stylesheets') . '/templates.css', $open);
        }
        // replace to current default css path
        if (preg_match('!/css/(.+)/default.css!', $open, $matches)) {
            $open = str_replace('/' . $matches[1] . '/default.css', '/' . api_get_setting('stylesheets') . '/default.css', $open);
        }
        //echo $open; 
        ?>
       <tr><td>                 
       <div>
                   <?php                                     
                   $tblNode = Database :: get_main_table(TABLE_MAIN_NODE);
                   $tblHomePageNode = Database :: get_main_table(TABLE_MAIN_NODE_HOMEPAGE);
                   $sqlProm = "SELECT node.id,
                                      node.content,
                                      node.title,
                                      node.display_title
                                FROM  $tblNode node
                                INNER JOIN $tblHomePageNode h 
                                ON (node.id = h.node_id)
                                WHERE h.promoted = " . NODE_HOMEPAGE_PROMOTED . " 
                                      AND node.language_id IN (". $language_id.",0) 
                                      AND node.access_url_id =".$accessUrlId."  
                                      AND node.active = 1";
                   
                   $results = Database::query($sqlProm, __FILE__, __LINE__);
                   $url = api_get_path(WEB_CODE_PATH);

                   while ($row = Database::fetch_array($results)) {
                       if($row['display_title']==1){
                            echo '<tr><th class="title-pages-home">';
                            echo '<div>' . $row['title'] . '</div>';
                            echo '</th></tr>';
                       }
                       echo '<tr><td>';
                       echo '<div style="position:relative">';
                       echo '<div style=" text-align: right">';
                       echo '<a class="link-pagehome-edit" href="'. $url .'index.php?module=node&cmd=PageHome&func=getForm&nodeId='. $row['id'] .'"><img src="'. $url .'img/pixel.gif" alt="'. get_lang('Edit') .'" title="'. get_lang('Edit') .'" class="actionplaceholdericon actionedit" align="middle">'. get_lang('Edit') .'</a>  ';
                       echo '<a class="link-pagehome-delete" href="'. $url .'index.php?module=node&cmd=PageHome&func=delete&nodeId='. $row['id'] .'" title="'. get_lang('AreYouSureToDelete') .'"><img src="'. $url .'img/pixel.gif" alt="'. get_lang('Delete') .'" title="'. get_lang('Delete') .'" class="actionplaceholdericon actiondelete" align="middle">'. get_lang('Delete') .'</a>';
                       echo '</div>';
                       echo str_replace('{WEB_PATH}', api_get_path(WEB_PATH), $row['content']) . '</div>';
                       echo '</td></tr>';
                       
                       /*if($row['display_title']==1){
                            echo '<div class="title-pages-home">'. $row['title'] .'</div>';
                       }
                       echo '<div>' . $row['content'] . '</div>';*/
                   }
                   
                   ?>
        </div>
        </td></tr>
                    <?php
//                            $language_id = api_get_language_id($language_interface);
//                            $accessUrlId = api_get_current_access_url_id();            
//                            if ($accessUrlId< 0) {
//                                $accessUrlId = 1;
//                            }
                    $tblNews=Database::get_main_table(TABLE_MAIN_NODE_NEWS);
                    $sqlNews = "SELECT node.id,node.title,node.content,news.start_date,news.end_date 
                                FROM $tblNode node INNER JOIN $tblNews news
                                ON (node.ID=news.NODE_ID) 
                                WHERE node.active = 1   
                                     AND node.language_id IN (". $language_id.",0)
                                     AND node.access_url_id =".$accessUrlId."  
                                     AND node.enabled = 1
                               ORDER BY
                                     news.start_date DESC";
                                    
                    $resultsNews = Database::query($sqlNews, __FILE__, __LINE__);
                    $num = Database::num_rows($resultsNews);
                    
                    echo '<tr><td>';
                    if ($num>0){
                        echo '<div class="portal-news-wrapper">';
                        echo '<div class="wrapper-tag"><div class="a1"></div><div class="a2">'.  get_lang('SystemAnnouncements').'</div><div class="a3"></div><div class="a4"></div> </div>';
                        echo '<div id="accordion">';
                        while ($row = Database::fetch_array($resultsNews)) {

                            echo '<h3> <a href="#">' . $row['title'] . '</a></h3>';
                            echo '<div><p>'.TimeZone::ConvertTimeFromServerToUser(api_get_user_id(), $row['start_date']).'</p><p>'. $row['content'] . '</p></div>';
                        }
                        echo '</div>';
                        echo '</div>';
                    }
                    echo '</td></tr>';
                 
                    ?>
</div>
                    </table>
                </td>
            </tr>
        </table>

        <?php
        break;
}

// close the content div
echo '</div>';

// display the footer
Display::display_footer();
?>
