<?php
//Give a default value to $charset. Should change to UTF-8 some time in the future.
//This parameter should be set in the platform configuration interface in time.
$charset = api_get_setting('platform_charset');
if (empty($charset)) {
    $charset = 'ISO-8859-15';
}

// Get language iso-code for this page - ignore errors
// The error ignorance is due to the non compatibility of function_exists()
// with the object syntax of Database::get_language_isocode()
@$document_language = Database::get_language_isocode($language_interface);
if (empty($document_language)) {
    //if there was no valid iso-code, use the english one
    $document_language = 'en';
}
header('Content-Type: text/html; charset=' . $charset);
header('X-Powered-By: Dokeos');
global $_course;
?>
<!DOCTYPE html>
<html lang="<?php echo $document_language; ?>" class="no-js">
    <head>
        <meta charset="utf-8"/>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset; ?>">
        <!--[if ie]><meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1"><![endif]-->
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title><?php
            if (!empty($_course['official_code'])) {
                echo $_course['name'] . ' - ';
            }
            echo $_course['official_code'];
            $include_library_path = api_get_path(WEB_LIBRARY_PATH) . 'javascript/responsive/';
            $normal_include_library_path = api_get_path(WEB_LIBRARY_PATH).'javascript/';
            ?>
        </title>
        <link rel="shortcut icon" href="images/favicon.ico" />
        <link rel="stylesheet" href="<?php echo api_get_path(WEB_CODE_PATH)?>exercice/exam_mode/js/jquery-ui.css" />
        <link rel="stylesheet" href="<?php echo $include_library_path; ?>css/jquery.mCustomScrollbar.min.css" />
        <link rel="stylesheet" href="<?php echo $include_library_path; ?>css/jquery.tagit.min.css" />
        <link rel="stylesheet" href="<?php echo $include_library_path; ?>css/bootstrap.min.css" />
        <link rel="stylesheet" href="<?php echo $include_library_path; ?>css/bootstrap-responsive.min.css" />
        <link rel="stylesheet" href="<?php echo api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets'); ?>/icons.css" />
        <link rel="stylesheet" href="<?php echo $include_library_path; ?>css/style.css" /> 
        
        <script src="<?php echo $include_library_path;  ?>js/jquery.min.js"></script>
        <script src="<?php echo api_get_path(WEB_CODE_PATH)?>exercice/exam_mode/js/jquery-ui.js"></script> 
        <script src="<?php echo $include_library_path;  ?>js/jwplayer.js"></script>
        <script src="<?php echo $include_library_path;  ?>js/jquery.mousewheel.min.js"></script>
        <script src="<?php echo $include_library_path;  ?>js/jquery.form.min.js"></script>
        <script src="<?php echo $include_library_path;  ?>js/jquery.browser.min.js"></script>
        <script src="<?php echo $include_library_path;  ?>js/jquery.tag-it.min.js"></script>
        
        <link rel="stylesheet" href="<?php echo $normal_include_library_path; ?>jquery.qtip/jquery.qtip.min.css" />
        <script type="text/javascript" src="<?php echo $normal_include_library_path;  ?>jquery.qtip/jquery.qtip.min.js" language="javascript"></script>
        
        <script type="text/javascript" src="<?php echo $normal_include_library_path;  ?>dokeos.js.php" language="javascript"></script>
        <?php 
            // Display all $htmlHeadXtra
            if (isset($htmlHeadXtra) && $htmlHeadXtra) {
                foreach ($htmlHeadXtra as $this_html_head) {
                    echo($this_html_head);
                }
            }
        ?>   
    </head>
    <body>
        <header>
            <div class="container">
                <!--<div class="bar-header">-->
                    
                    <div class="row-fluid">
                    
                    <div class="span12">
                        <div class="header_background"></div>
                        <?php
                        global $tool_name, $_cid, $_course;
                        if ($_cid == -1) {
                         $url = api_get_path(WEB_PATH).'index.php'.$param;
                        } else {
                            $course_path = !empty($_course['path']) ? $_course['path'] : $_course['directory'];
                            $url = api_get_path(WEB_COURSE_PATH) . $course_path.'/index.php'.$param;
                        }
                        // name of training
                        if (isset($_course) && array_key_exists('name', $_course)) {
                            $session_id = api_get_session_id();
                            $session_name = "";
                            if ($session_id > 0) {
                                $session_info = array();
                                $session_info = api_get_session_info($session_id);
                                $session_name = ' ( ' . $session_info['name'] . ' )';
                            }
                        }?>
                        
                        <div class="row-fluid">
                            <div class="span4">
                                <div id="back2home" class="title-button">
                            <a href="<?php echo $url; ?>">
                                <div class="icons-home"></div>
                                <span class="text-home">
                                    <?php //echo $_course['name'] . ' ' . $session_name; ?>
                                    <?php echo cut($_course['name'] . $session_name, 20) ?>
                                </span>
                            </a>
                        </div>
                            </div>
<!--                            <div class="span8">...</div>-->
                        </div>
                        
                    </div>
                    <div class="clearfix"></div>
                </div>
                    
                <!--</div>-->
                
            </div>
        </div>
    </header>
    <div class="content">