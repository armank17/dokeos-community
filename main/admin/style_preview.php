<?php
/* For licensing terms, see /dokeos_license.txt */

/**
 * @author Patrick Cool
 * @package dokeos.admin
 */
$language_file = array('courses');
include_once('../inc/global.inc.php');
?>
<html>
    <head>
        <?php
        if (isset($_GET['style']) AND $_GET['style'] <> '') {
            $style = Security::remove_XSS($_GET['style']);
            echo '<link href="../css/' . $style . '/default.css" rel="stylesheet" type="text/css">';
        } else {
            $platform_theme = api_get_setting('stylesheets');
            if ($platform_theme != "dokeos2_blue_tablet" && $platform_theme != "dokeos2_orange_tablet") {
            $platform_theme = str_replace(array("dokeos2_blue","dokeos2_orange"),array("dokeos2_blue_tablet","dokeos2_orange_tablet"),$platform_theme);
        }
            echo '<link href="../css/' . $platform_theme . '/default.css" rel="stylesheet" type="text/css">';
        }
        ?>
        <style type="text/css">
            #main2{
                margin: auto;
                width: 900px;
                height: 320px;
            }
            #content_with_menu{
                float: right;
                width: 700px;
            }
            .headerinner{
                margin: auto;
                overflow:hidden; /* IE needs */
                height:50px;
                width: 910px;
                position:relative;
            }
            #dokeostabs{
                float: left;
                padding: 0;
                margin-left: 0;
                width: 100%;
            }
            #header2 {
                width: 937px;
            }
            #content2 {
                background-color: #FFFFFF;
                border:1px solid #DDE7F3;
                border-radius:5px;
                -moz-border-radius: 5px;
                -webkit-border-radius: 5px;
                margin-top: 5px;
                margin-bottom: 5px;
                /*height:435px;	*/		/* read by IE6 as min-height */
                padding: 10px;
                vertical-align:middle;
                width: auto;
                zoom:1;
                height:320px;
            }
            #main_left_content #menu3 {
                background-color: #FFFFFF;
                margin-top: 5px;
                margin-bottom: 5px;
                height:300px;			/* read by IE6 as min-height */
                vertical-align:middle;
                width: 170px;
                zoom:1;
            }
        </style>
    </head>
    <body>
        <div id="wrapper">

            <div id="header">
                <div id="header1">
                    <div class="headerinner">
                        <div id="top_corner"></div>
                        <div id="languageselector"></div>
                        <div id="institution">
                            <a href="javascript:void(0)" target="_top"><?php echo api_get_setting('siteName'); ?></a>
                            -&nbsp;
                            <a href="javascript:void(0)" target="_top"><?php echo api_get_setting('Institution'); ?></a>
                        </div>
                    </div>
                </div>

                <div id="header2">
                    <div class="headerinner">
                        <div id="dokeostabs">

                            <ul id="dokeostabs">
                                <li class="tab_mycampus"><a href="javascript:void(0)" target="_top"><?php echo get_lang('Home'); ?></a></li>
                                <li id="current" class="tab_mycourses_current"><a href="javascript:void(0)" target="_top"><?php echo get_lang('Courses'); ?></a></li>
                                <li class="tab_myagenda"><a href="javascript:void(0)" target="_top"><?php echo get_lang('Agenda'); ?></a></li>
                                <li class="tab_session_my_space"><a href="javascript:void(0)" target="_top"><?php echo get_lang('Reporting'); ?></a></li>
                                <li class="tab_platform_admin"><a href="javascript:void(0)" target="_top"><?php echo get_lang('PlatformAdmin'); ?></a></li>
                                <li class="logout"><a href="javascript:void(0)" target="_top"><?php echo get_lang('Logout'); ?>&nbsp;(admin)</a></li>
                            </ul>
                            <div style="clear: both;" class="clear"> </div>
                        </div>
                    </div>
                </div>

            </div>



            <!-- end of the whole #header section -->
            <div class="clear">&nbsp;</div>
            <div id="main2"> <!-- start of #main wrapper for #content and #menu divs -->
                <!--   Begin Of script Output   -->
                <div><div id="content2"><div id="content_with_menu"><div class="course_list_category"><?php echo api_get_setting('default_category_course'); ?></div>
                            <ul class="courseslist">
                                <li>
                                    <div class="independent_course_item" style="padding: 8px; clear:both;">
                                        <a href="javascript:void(0)"><div class="coursestatusicons"><img src="<?php echo api_get_path(WEB_IMG_PATH); ?>miscellaneous22x22.png" alt="miscellaneous22x22.png" title="miscellaneous22x22.png"></div>
                                            <strong>Training</strong></a>
                                        <br/>TRAINING - John Doe</div></li>
                            </ul></div>
                        <div id="main_left_content">
                            <div style="height: 98px;" class="menu3" id="menu3">
                                <h3 class="tablet_title"><?php echo get_lang('Account'); ?></h3>
                                <a href="javascript:void(0)"><img src="<?php echo api_get_path(WEB_IMG_PATH); ?>pixel.gif" alt="Create a course" title="Create a course" class="homepage_button homepage_create_course" align="middle"><?php echo get_lang('CourseCreate'); ?></a><br><a href="javascript:void(0);"><img src="<?php echo api_get_path(WEB_IMG_PATH); ?>pixel.gif" alt="Sort courses" title="Sort courses" class="homepage_button homepage_catalogue" align="middle"><?php echo get_lang('SortMyCourses'); ?></a></div></div><div class="clear"></div></div> <div class="clear">&nbsp;</div> <!-- 'clearing' div to make sure that footer stays below the main and right column sections -->
                </div> <!-- end of #main" started at the end of banner.inc.php -->
            </div>
            <div class="push"></div>
        </div> <!-- end of #wrapper section -->

    </body>
</html>