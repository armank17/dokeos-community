<?php
// $Id: tool_navigation_menu.inc.php 22072 2009-07-14 15:14:42Z jhp1411 $
/*
  ==============================================================================
  Dokeos - elearning and course management software

  Copyright (c) 2005 Dokeos S.A.
  Copyright (c) 2004-2005 Sandra Mathijs, Hogeschool Gent
  Copyright (c) 2005 Roan Embrechts, Vrije Universiteit Brussel
  Copyright (c) 2005 Wolfgang Schneider
  Copyright (c) Bart Mollet, Hogeschool Gent

  For a full list of contributors, see "credits.txt".
  The full license can be read in "license.txt".

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; either version 2
  of the License, or (at your option) any later version.

  See the GNU General Public License for more details.

  Contact address: Dokeos, 44 rue des palais, B-1030 Brussels, Belgium
  Mail: info@dokeos.com
  ==============================================================================
 */
/**
  ==============================================================================
 * 	Navigation menu display code
 *
 * 	@package dokeos.include
  ==============================================================================
 */
define('SHORTCUTS_HORIZONTAL', 0);
define('SHORTCUTS_VERTICAL', 1);
/**
 * Build the navigation items to show in a course menu
 * @param boolean $include_admin_tools
 */
echo '<script  type="text/javascript" src="' . api_get_path(WEB_CODE_PATH) . 'appcore/library/jquery/jquery.nicescroll.js"></script>';

//print_r(api_get_path(WEB_CODE_PATH).'appcore/library/jquery/jquery.nicescroll.js');
function get_navigation_items($include_admin_tools = false) {
    global $is_courseMember;
    global $_user;
    global $_course;

    if (!empty($_course['db_name'])) {
        $database = $_course['db_name'];
    }

    $navigation_items = array();
    $course_id = api_get_course_id();

    if (!empty($course_id) && ($course_id != -1)) {
        $user_id = api_get_user_id();

        $course_tools_table = Database :: get_course_table(TABLE_TOOL_LIST, $database);

        /*
          --------------------------------------------------------------
          Link to the Course homepage
          --------------------------------------------------------------
         */
        
        $navigation_items['home']['image'] = 'home.gif';
        $navigation_items['home']['link'] = api_get_path(REL_COURSE_PATH) . $_SESSION['_course']['path'] . '/index.php';
        $navigation_items['home']['name'] = get_lang("CourseHomepageLink");
        $navigation_items['home']['langname'] = 'home';

        /*
          --------------------------------------------------------------
          Link to the different tools
          --------------------------------------------------------------
         */

        $is_allowed_to_edit = api_is_allowed_to_edit();
        $is_platform_admin = api_is_platform_admin();
        $test = "No condition";
        $test1 = "----";
        /* IF ADMIN */
        if ($is_allowed_to_edit) {
            $my_list = get_tools_category_menu_link(TOOL_BASIC);
            if (count($my_list) > 0) {
                $test = $my_list;
                $test1 = "TOOL_BASIC";
            }
//                    echo '<span class="sectiontablettitle">' . get_lang("Advanced") . '</span>';
            if (api_get_setting('enable_pro_settings') !== "true") {
                //$test1="enable_pro_settings != true";
            } else {
                $my_list = get_tools_category_menu_link(TOOL_ADVANCED);
                if (count($my_list) > 0) {
                    $test1 = $my_list;
                }
            }
            /* FIN IF ADMIN */
        } else {
            if (api_get_setting('enable_pro_settings') != "true") {
                $my_list = get_tools_category_menu_link(TOOL_BASIC);
                if (count($my_list) > 0) {
                    $test1 = "enable_pro_settings2 != true";
                }
            } else {
                $my_list = get_tools_category_menu_link(TOOL_BASIC_TOOL_ADVANCED);
                if (count($my_list) > 0) {
                    $test = $my_list;
                    "AND visibility=1 AND session_id=0";
//                            print_r($test);
//                            exit;
                }
            }
        }

        $sql_result = Database::query($test, __FILE__, __LINE__);
        /*
          $sql_menu_query = "SELECT * FROM $course_tools_table WHERE visibility='1' and admin='0' ORDER BY id ASC";
          $sql_result = Database::query($sql_menu_query, __FILE__, __LINE__);
         */
        while ($row = Database::fetch_array($sql_result)) {
            $navigation_items[$row['id']] = $row;
            if (stripos($row['link'], 'http://') === false && stripos($row['link'], 'https://') === false) {
                $navigation_items[$row['id']]['link'] = api_get_path(REL_CODE_PATH) . $row['link'];
                /*
                  $navigation_items[$row['id']]['name'] = $row['image'] == 'scormbuilder.gif' ? $navigation_items[$row['id']]['name'] : get_lang(ucfirst($navigation_items[$row['id']]['name']));
                 */
                if ($row['image'] != 'scormbuilder.gif' && $row['image'] != 'blog.png') {
                    $navigation_items[$row['id']]['name'] = get_lang(ucfirst($navigation_items[$row['id']]['name']));
                    $navigation_items[$row['id']]['langname'] = $row['name'];
                }
            }
        }

        $query = Database::query($test1, __FILE__, __LINE__);
        while ($row = Database::fetch_array($query)) {
            $navigation_items[$row['id']] = $row;
            if (stripos($row['link'], 'http://') === false && stripos($row['link'], 'https://') === false) {
                $navigation_items[$row['id']]['link'] = api_get_path(REL_CODE_PATH) . $row['link'];
                /*
                  $navigation_items[$row['id']]['name'] = $row['image'] == 'scormbuilder.gif' ? $navigation_items[$row['id']]['name'] : get_lang(ucfirst($navigation_items[$row['id']]['name']));
                 */
                if ($row['image'] != 'scormbuilder.gif' && $row['image'] != 'blog.png') {
                    $navigation_items[$row['id']]['name'] = get_lang(ucfirst($navigation_items[$row['id']]['name']));
                    $navigation_items[$row['id']]['langname'] = $row['name'];
                }
            }
        }
        /*
          --------------------------------------------------------------
          Admin (edit rights) only links
          - Course settings (course admin only)
          - Course rights (roles & rights overview)
          --------------------------------------------------------------
         */

        if ($include_admin_tools) {

            $course_settings_sql = "	SELECT name,image FROM $course_tools_table WHERE link='course_info/infocours.php'";
            $sql_result = Database::query($course_settings_sql);
            $course_setting_info = Database::fetch_array($sql_result);
            $course_setting_visual_name = get_lang(ucfirst($course_setting_info['name']));
            if (api_get_session_id() == 0) {
                // course settings item
                $navigation_items['course_settings']['image'] = $course_setting_info['image'];
                $navigation_items['course_settings']['link'] = api_get_path(REL_CODE_PATH) . 'course_info/infocours.php';
                $navigation_items['course_settings']['name'] = $course_setting_visual_name;
                $navigation_items['course_settings']['langname'] = $course_setting_info['name'];
            }
        }
    }
    foreach ($navigation_items as $key => $navigation_item) {
        if (strstr($navigation_item['link'], '?')) {
            //link already contains a parameter, add course id parameter with &
            $parameter_separator = '&amp;';
        } else {
            //link doesn't contain a parameter yet, add course id parameter with ?
            $parameter_separator = '?';
        }
        if($navigation_items['home']){
            $navigation_items[$key]['link'] .= $parameter_separator;
        }else{
            $navigation_items[$key]['link'] .= $parameter_separator . api_get_cidreq();
        }
    }




    //return $navigation_items;
    return validate_list_tool_menu_links($navigation_items);
}

function validate_list_tool_menu_links($my_list) {
    if (count($my_list) > 0) {
        $array_id_tool = array();
        $new_array_tool = array();
        foreach ($my_list as $puntero => $arrayTool) {
            if (!in_array($arrayTool['name'], $array_id_tool)) {
                array_push($new_array_tool, $arrayTool);
            }
            array_push($array_id_tool, $arrayTool['name']);
        }
    }
    return $new_array_tool;
}

function get_tools_category_menu_link($course_tool_category) {
    global $_user;

    $course_tool_table = Database::get_course_table(TABLE_TOOL_LIST);
//    $is_allowed_to_edit = api_is_allowed_to_edit(null, true);
//    $is_platform_admin = api_is_platform_admin();
//    $all_tools_list = array();
    //condition for the session
    $session_id = api_get_session_id();
    $condition_session = api_get_session_condition($session_id, true, false);
    $sql = "";
    switch ($course_tool_category) {
        case TOOL_STUDENT_VIEW:
            $session_id = intval($session_id);
            $annonymous = '';
            if ($_SESSION['_user']['user_id'] == 2) {
                $annonymous = " AND name != 'chat' AND name != 'notebook'";
            }
            $sql_tmp = "SELECT :field FROM $course_tool_table WHERE visibility = '1' AND (category = 'authoring' OR category = 'interaction') AND (name != 'author' AND name != 'oogie') $annonymous AND (session_id = '$session_id'";

            $sql = str_replace(":field", "*", $sql_tmp) . " OR (session_id = 0 AND name NOT IN ( " . str_replace(":field", "name", $sql_tmp) . ") ) )) ORDER BY name";
            //$result = Database::query($sql,__FILE__,__LINE__);
            break;

        case TOOL_AUTHORING:
            $sql = "SELECT * FROM $course_tool_table WHERE category = 'authoring' $condition_session AND `name` NOT IN('oogie', 'author', 'WebTv', 'visio_classroom', 'SeriousGames', 'glossary', 'mediabox', 'link') OR `name` = 'course_setting' ORDER BY name";
            //$result = Database::query($sql,__FILE__,__LINE__);
            break;

        case TOOL_INTERACTION:
            $sql = "SELECT * FROM $course_tool_table WHERE category = 'interaction' $condition_session AND `name` NOT IN('notebook', 'chat', 'visio_conference', 'mindmap', 'group', 'student_publication') ORDER BY name;";
            //$result = Database::query($sql,__FILE__,__LINE__);
            break;

        case TOOL_ADMIN_VISIBLE:
            $sql = "SELECT * FROM $course_tool_table WHERE category = 'admin' AND visibility ='1' $condition_session ORDER BY name";
            //$result = Database::query($sql,__FILE__,__LINE__);
            break;

        case TOOL_ADMIN_PLATEFORM:
            $sql = "SELECT * FROM $course_tool_table WHERE category = 'admin' $condition_session AND `name` NOT IN('blog_management', 'tracking', 'copy_course_content', 'course_maintenance') ORDER BY name";
            //$result = Database::query($sql,__FILE__,__LINE__);
            break;

        case TOOL_PRODUCTION:
            $sql = "SELECT * FROM $course_tool_table WHERE category = 'production' $condition_session ORDER BY name";
            //$result = Database::query($sql,__FILE__,__LINE__);
            break;
        case TOOL_BASIC:
            $pro = "OR `category` = 'free'";
            $student = '';
            if (!api_is_allowed_to_edit() || (isset($_GET['learner_view']) && $_GET['learner_view'] == 'true')) {
                $student = "AND `name` NOT IN('course_setting', 'author')";
            }
            $sql = "SELECT * FROM $course_tool_table WHERE (`category` = 'common' $pro) $condition_session $student  ORDER BY `name`;";
            //$result = Database::query($sql,__FILE__,__LINE__);
            break;
        case TOOL_ADVANCED:
            $sql = "SELECT id,
                    CASE WHEN name='Visio_classroom' 
                    THEN 'Live'
                    ELSE name
                    END as 'name',link,image,visibility,admin,address,added_tool,target,category,session_id,popup FROM $course_tool_table WHERE `category` = 'pro' AND name<>'search' AND name<> 'visio_conference' $condition_session ORDER BY `name`;";
            //$result = Database::query($sql,__FILE__,__LINE__);
            break;
        case TOOL_BASIC_TOOL_ADVANCED:
            $check_session = "";
            //print_r("Session id: ".$session_id);
            // if ($session_id != 0) {
            $check_session = " and session_id = $session_id ";
            //  }
            $pro = "OR `category` = 'free'";
            if (api_get_setting('enable_pro_settings') === "true") {
                $pro = '';
            }
            $student = '';
            if (!api_is_allowed_to_edit() || (isset($_GET['learner_view']) && $_GET['learner_view'] == 'true')) {
                $student = "AND `name` NOT IN('course_setting', 'author', 'shop')";
            }
            $sql = "SELECT id,
                    CASE WHEN name='Visio_classroom' 
                    THEN 'Live'
                    ELSE name
                    END as 'name',link,image,visibility,admin,address,added_tool,target,category,session_id,popup FROM $course_tool_table WHERE ((`category` = 'common' OR `category` = 'pro' $pro)  OR name = 'quiz') $check_session AND visibility=1 AND name <> 'visio_conference' AND name<>'search' $student ORDER BY `name`;";
            //$result = Database::query($sql,__FILE__,__LINE__);
            break;
    }
    return $sql;
}

/**
 * Show a navigation menu
 */
function show_navigation_menu() {
    $navigation_items = get_navigation_items(true);
    $course_id = api_get_course_id();
    if (api_get_setting('show_navigation_menu') == 'icons') {
        //echo '<div style="float:right;width: 40px;position:absolute;right:10px;top:10px;">';
        //echo '<div style="float:right;position:absolute;right:10px;top:10px">';
        echo '<div class="scroll" style="float:right;position:absolute;right:10px;top:5%; width:50px; height: 500px;"> ';
        //echo '<div class="scroll" style="float:right;position:absolute;right:10px;top:200px; width:150px; height: 700px;"> ';
        show_navigation_tool_shortcuts($orientation = SHORTCUTS_VERTICAL);
        echo '</div>';
        //echo '</div>';
    } else {
        // Here we have no a fixed width
        //echo '<div style="float:right;position:absolute;right:10px;top:10px;">';
        echo '<div class="scroll" style="float:right;position:absolute;right:10px;top:5%; width:150px; height: 500px;"> ';
        show_navigation_tool_shortcuts($orientation = SHORTCUTS_VERTICAL);
        echo '</div>';
        //echo '</div>';
    }
    /* 	else
      {
      echo '<div id="toolnav"> <!-- start of #toolnav -->'; */
    ?>
    <script>
        $(document).ready(function() {
            var bgcolor = $(".color-theme").css("color");
            if (!bgcolor) {
                bgcolor = $("#header_background").css("background-color");
            }
            $(".scroll").niceScroll({cursorcolor: bgcolor, cursorwidth: "8px"});

        });
    </script>
    <script type="text/javascript">
        /* <![CDATA[ */
        function createCookie(name, value, days)
        {
            if (days)
            {
                var date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                var expires = "; expires=" + date.toGMTString();
            }
            else
                var expires = "";
            document.cookie = name + "=" + value + expires + "; path=/";
        }
        function readCookie(name)
        {
            var nameEQ = name + "=";
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++)
            {
                var c = ca[i];
                while (c.charAt(0) == ' ')
                    c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) == 0)
                    return c.substring(nameEQ.length, c.length);
            }
            return null;
        }
        function swap_menu()
        {
            toolnavlist_el = document.getElementById('toolnavlist');
            center_el = document.getElementById('center');
            swap_menu_link_el = document.getElementById('swap_menu_link');

            if (toolnavlist_el.style.display == 'none')
            {
                toolnavlist_el.style.display = '';
                if (center_el)
                {
                    center_el.style.margin = '0 190px 0 0';
                }
                swap_menu_link_el.innerHTML = '<?php echo get_lang('Hide'); ?> &raquo;&raquo;';
                createCookie('dokeos_menu_state', 1, 10);
            }
            else
            {
                toolnavlist_el.style.display = 'none';
                if (center_el)
                {
                    center_el.style.margin = '0 0 0 0';
                }
                swap_menu_link_el.innerHTML = '&laquo;&laquo; <?php echo get_lang('Show'); ?>';
                createCookie('dokeos_menu_state', 0, 10);
            }
        }
    //document.write('<a href="javascript: void(0);" id="swap_menu_link" onclick="javascript: swap_menu();"><?php echo get_lang('Hide'); ?> &raquo;&raquo;<\/a>');
        /* ]]> */
    </script>
    <?php
    /* echo '<div id="toolnavbox">';
      echo '<div id="toolnavlist"><dl>';
      foreach ($navigation_items as $key => $navigation_item)
      {
      echo '<dd>';
      $url_item = parse_url($navigation_item['link']);
      $url_current = parse_url($_SERVER['REQUEST_URI']);

      if (strpos($navigation_item['link'],'chat')!==false && api_get_course_setting('allow_open_chat_window',$course_id)==true)
      {
      echo '<a href="javascript: void(0);" onclick="window.open(\''.$navigation_item['link'].'\',\'window_chat'.$_SESSION['_cid'].'\',config=\'height=\'+380+\', width=\'+625+\', left=2, top=2, toolbar=no, menubar=no, scrollbars=yes, resizable=yes, location=no, directories=no, status=no\')" target="' . $navigation_item['target'] . '"';
      }
      else
      {
      echo '<a href="'.$navigation_item['link'].'" target="_self" ';
      }

      if (stristr($url_item['path'],$url_current['path']))
      {
      if(! isset($_GET['learnpath_id']) || strpos($url_item['query'],'learnpath_id='.$_GET['learnpath_id']) === 0)
      {
      echo ' id="here"';
      }
      }
      echo ' title="'.$navigation_item['name'].'">';
      if (api_get_setting('show_navigation_menu') != 'text')
      {
      echo '<div align="left"><img src="'.api_get_path(WEB_IMG_PATH).$navigation_item['image'].'" alt="'.$navigation_item['name'].'"/></div>';
      }
      if (api_get_setting('show_navigation_menu') != 'icons')
      {
      echo $navigation_item['name'];
      }
      echo '</a>';
      echo '</dd>';
      echo "\n";
      }
      echo '</dl></div></div>';
      echo '</div> <!-- end "#toolnav" -->'; */
    ?>
    <script type="text/javascript">
        /* <![CDATA[ */
        if (readCookie('dokeos_menu_state') == 0)
        {
            swap_menu();
        }
        /* ]]> */
    </script>
    <?php
    //}
}

/**
 * Show a toolbar with shortcuts to the course tool
 */
function show_navigation_tool_shortcuts($orientation = SHORTCUTS_HORIZONTAL) {
    $navigation_items = get_navigation_items(false);
    //print_r(count($navigation_items));
    foreach ($navigation_items as $key => $navigation_item) {

        if (empty($navigation_item['langname'])) {
            continue;
        }

        $class = 'toolactionplaceholdericon toolshortcut_' . strtolower($navigation_item['langname']);

        //echo $navigation_item['langname'] .'<br />';

        if (strpos($navigation_item['link'], 'chat') !== false && api_get_course_setting('allow_open_chat_window') == true) {
            /*
              echo '<a href="#" onclick="window.open(\''.$navigation_item['link'].'\',\'window_chat'.$_SESSION['_cid'].'\',config=\'height=\'+380+\', width=\'+625+\', left=2, top=2, toolbar=no, menubar=no, scrollbars=yes, resizable=yes, location=no, directories=no, status=no\')" target="' . $navigation_item['target'] . '"';
             */
            echo '<a href="javascript: void(0);" onclick="javascript: window.open(\'' . $navigation_item['link'] . '\',\'window_chat' . $_SESSION['_cid'] . '\',config=\'height=\'+380+\', width=\'+625+\', left=2, top=2, toolbar=no, menubar=no, scrollbars=yes, resizable=yes, location=no, directories=no, status=no\')" target="' . $navigation_item['target'] . '"';
        } else if ($navigation_item['popup'] == '1') {
            echo '<a class="action-dialog" href="' . $navigation_item['link'] . '"';
        } else {
            echo '<a href="' . $navigation_item['link'] . '"';
        }

        if (strpos(api_get_self(), $navigation_item['link']) !== false) {
            echo ' id="here"';
        }
        echo ' target="_self" title="' . $navigation_item['name'] . '">';
        if (api_get_setting('show_navigation_menu') == 'text') {
            echo $navigation_item['name'];
        } elseif (api_get_setting('show_navigation_menu') == 'iconstext') {
            //echo '<img src="'.api_get_path(WEB_IMG_PATH).$navigation_item['image'].'" alt="'.$navigation_item['name'].'"/>'.'&nbsp;&nbsp;'.$navigation_item['name'];
            echo Display::return_icon('pixel.gif', $navigation_item['name'], array('class' => $class)) . $navigation_item['name'];
        } else {
            //echo '<img src="'.api_get_path(WEB_IMG_PATH).$navigation_item['image'].'" alt="'.$navigation_item['name'].'"/>';
            echo Display::return_icon('pixel.gif', $navigation_item['name'], array('class' => $class));
        }
        echo '</a>';
        if ($orientation == SHORTCUTS_VERTICAL) {
            echo '<br />';
        }
    }
}

//Array ( [0] => Array ( [image] => home.gif [link] => /courses/PHP/index.php?cidReq=PHP [name] => Home [langname] => home ) [1] => Array ( [0] => 1 [id] => 1 [1] => announcement [name] => Announcements [2] => announcements/announcements.php [link] => /main/announcements/announcements.php?cidReq=PHP [3] => valves.png [image] => valves.png [4] => 1 [visibility] => 1 [5] => 0 [admin] => 0 [6] => squaregrey.gif [address] => squaregrey.gif [7] => 0 [added_tool] => 0 [8] => _self [target] => _self [9] => common [category] => common [10] => 0 [session_id] => 0 [11] => 0 [popup] => 0 [langname] => announcement ) [2] => Array ( [0] => 2 [id] => 2 [1] => calendar_event [name] => Calendar [2] => calendar/agenda.php [link] => /main/calendar/agenda.php?cidReq=PHP [3] => agenda.png [image] => agenda.png [4] => 1 [visibility] => 1 [5] => 0 [admin] => 0 [6] => squaregrey.gif [address] => squaregrey.gif [7] => 0 [added_tool] => 0 [8] => _self [target] => _self [9] => common [category] => common [10] => 0 [session_id] => 0 [11] => 0 [popup] => 0 [langname] => calendar_event ) [3] => Array ( [0] => 22 [id] => 22 [1] => course_description [name] => Description [2] => course_description/ [link] => /main/course_description/?cidReq=PHP [3] => info.png [image] => info.png [4] => 1 [visibility] => 1 [5] => 0 [admin] => 0 [6] => squaregrey.gif [address] => squaregrey.gif [7] => 0 [added_tool] => 0 [8] => _self [target] => _self [9] => common [category] => common [10] => 0 [session_id] => 0 [11] => 0 [popup] => 0 [langname] => course_description ) [4] => Array ( [0] => 3 [id] => 3 [1] => course_setting [name] => Settings [2] => course_info/infocours.php [link] => /main/course_info/infocours.php?cidReq=PHP [3] => reference.png [image] => reference.png [4] => 0 [visibility] => 0 [5] => 1 [admin] => 1 [6] => [address] => [7] => 0 [added_tool] => 0 [8] => _self [target] => _self [9] => common [category] => common [10] => 0 [session_id] => 0 [11] => 0 [popup] => 0 [langname] => course_setting ) [5] => Array ( [0] => 4 [id] => 4 [1] => document [name] => Documents [2] => document/document.php [link] => /main/document/document.php?cidReq=PHP [3] => folder_document.png [image] => folder_document.png [4] => 1 [visibility] => 1 [5] => 0 [admin] => 0 [6] => squaregrey.gif [address] => squaregrey.gif [7] => 0 [added_tool] => 0 [8] => _self [target] => _self [9] => common [category] => common [10] => 0 [session_id] => 0 [11] => 0 [popup] => 0 [langname] => document ) [6] => Array ( [0] => 5 [id] => 5 [1] => dropbox [name] => Dropbox [2] => dropbox/index.php [link] => /main/dropbox/index.php?cidReq=PHP [3] => dropbox.gif [image] => dropbox.gif [4] => 1 [visibility] => 1 [5] => 0 [admin] => 0 [6] => squaregrey.gif [address] => squaregrey.gif [7] => 0 [added_tool] => 0 [8] => _self [target] => _self [9] => common [category] => common [10] => 0 [session_id] => 0 [11] => 0 [popup] => 0 [langname] => dropbox ) [7] => Array ( [0] => 6 [id] => 6 [1] => forum [name] => Forums [2] => forum/index.php [link] => /main/forum/index.php?cidReq=PHP [3] => forum.png [image] => forum.png [4] => 1 [visibility] => 1 [5] => 0 [admin] => 0 [6] => squaregrey.gif [address] => squaregrey.gif [7] => 0 [added_tool] => 0 [8] => _self [target] => _self [9] => common [category] => common [10] => 0 [session_id] => 0 [11] => 0 [popup] => 0 [langname] => forum ) [8] => Array ( [0] => 23 [id] => 23 [1] => learnpath [name] => Modules [2] => newscorm/lp_controller.php?action=course [link] => /main/newscorm/lp_controller.php?action=course&cidReq=PHP [3] => scorm.png [image] => scorm.png [4] => 1 [visibility] => 1 [5] => 0 [admin] => 0 [6] => squaregrey.gif [address] => squaregrey.gif [7] => 0 [added_tool] => 0 [8] => _self [target] => _self [9] => common [category] => common [10] => 0 [session_id] => 0 [11] => 0 [popup] => 0 [langname] => learnpath ) [9] => Array ( [0] => 24 [id] => 24 [1] => quiz [name] => Quiz [2] => exercice/exercice.php [link] => /main/exercice/exercice.php?cidReq=PHP [3] => quiz.png [image] => quiz.png [4] => 1 [visibility] => 1 [5] => 0 [admin] => 0 [6] => squaregrey.gif [address] => squaregrey.gif [7] => 0 [added_tool] => 0 [8] => _self [target] => _self [9] => free [category] => free [10] => 0 [session_id] => 0 [11] => 0 [popup] => 0 [langname] => quiz ) [10] => Array ( [0] => 9 [id] => 9 [1] => survey [name] => Surveys [2] => survey/survey_list.php [link] => /main/survey/survey_list.php?cidReq=PHP [3] => survey.png [image] => survey.png [4] => 1 [visibility] => 1 [5] => 0 [admin] => 0 [6] => squaregrey.gif [address] => squaregrey.gif [7] => 0 [added_tool] => 0 [8] => _self [target] => _self [9] => common [category] => common [10] => 0 [session_id] => 0 [11] => 0 [popup] => 0 [langname] => survey ) [11] => Array ( [0] => 7 [id] => 7 [1] => user [name] => Users [2] => user/user.php [link] => /main/user/user.php?cidReq=PHP [3] => members.png [image] => members.png [4] => 1 [visibility] => 1 [5] => 0 [admin] => 0 [6] => squaregrey.gif [address] => squaregrey.gif [7] => 0 [added_tool] => 0 [8] => _self [target] => _self [9] => common [category] => common [10] => 0 [session_id] => 0 [11] => 0 [popup] => 0 [langname] => user ) [12] => Array ( [0] => 8 [id] => 8 [1] => wiki [name] => Wiki [2] => wiki/index.php [link] => /main/wiki/index.php?cidReq=PHP [3] => wiki.png [image] => wiki.png [4] => 1 [visibility] => 1 [5] => 0 [admin] => 0 [6] => squaregrey.gif [address] => squaregrey.gif [7] => 0 [added_tool] => 0 [8] => _self [target] => _self [9] => common [category] => common [10] => 0 [session_id] => 0 [11] => 0 [popup] => 0 [langname] => wiki ) [13] => Array ( [0] => 25 [id] => 25 [1] => author [name] => Author [2] => index.php?module=author&cmd=Authoring&func=initialSettings [link] => /main/index.php?module=author&cmd=Authoring&func=initialSettings&cidReq=PHP [3] => author.png [image] => author.png [4] => 1 [visibility] => 1 [5] => 0 [admin] => 0 [6] => squaregrey.gif [address] => squaregrey.gif [7] => 0 [added_tool] => 0 [8] => _self [target] => _self [9] => pro [category] => pro [10] => 0 [session_id] => 0 [11] => 1 [popup] => 1 [langname] => author ) [14] => Array ( [0] => 26 [id] => 26 [1] => Evaluation [name] => Evaluation [2] => index.php?module=evaluation&cmd=Index [link] => /main/index.php?module=evaluation&cmd=Index&cidReq=PHP [3] => control.png [image] => control.png [4] => 1 [visibility] => 1 [5] => 0 [admin] => 0 [6] => [address] => [7] => 0 [added_tool] => 0 [8] => _self [target] => _self [9] => pro [category] => pro [10] => 0 [session_id] => 0 [11] => 0 [popup] => 0 [langname] => Evaluation ) [15] => Array ( [0] => 30 [id] => 30 [1] => Live [name] => Live [2] => videoconference/virtual_classroom.php [link] => /main/videoconference/virtual_classroom.php?cidReq=PHP [3] => visio.gif [image] => visio.gif [4] => 1 [visibility] => 1 [5] => 0 [admin] => 0 [6] => [address] => [7] => 0 [added_tool] => 0 [8] => _self [target] => _self [9] => pro [category] => pro [10] => 0 [session_id] => 0 [11] => 0 [popup] => 0 [langname] => Live ) [16] => Array ( [0] => 29 [id] => 29 [1] => SeriousGames [name] => Games [2] => serious_game/index.php [link] => /main/serious_game/index.php?cidReq=PHP [3] => author.png [image] => author.png [4] => 1 [visibility] => 1 [5] => 0 [admin] => 0 [6] => squaregrey.gif [address] => squaregrey.gif [7] => 0 [added_tool] => 0 [8] => _self [target] => _self [9] => pro [category] => pro [10] => 0 [session_id] => 0 [11] => 0 [popup] => 0 [langname] => SeriousGames ) [17] => Array ( [0] => 27 [id] => 27 [1] => Shop [name] => Shop [2] => index.php?module=ecommerce&cmd=Shop [link] => /main/index.php?module=ecommerce&cmd=Shop&cidReq=PHP [3] => shop.png [image] => shop.png [4] => 1 [visibility] => 1 [5] => 0 [admin] => 0 [6] => [address] => [7] => 0 [added_tool] => 0 [8] => _self [target] => _self [9] => pro [category] => pro [10] => 0 [session_id] => 0 [11] => 0 [popup] => 0 [langname] => Shop ) [18] => Array ( [0] => 28 [id] => 28 [1] => WebTv [name] => WebTV [2] => webtv/index.php [link] => /main/webtv/index.php?cidReq=PHP [3] => author.png [image] => author.png [4] => 1 [visibility] => 1 [5] => 0 [admin] => 0 [6] => squaregrey.gif [address] => squaregrey.gif [7] => 0 [added_tool] => 0 [8] => _self [target] => _self [9] => pro [category] => pro [10] => 0 [session_id] => 0 [11] => 0 [popup] => 0 [langname] => WebTv ) ) 
?>
