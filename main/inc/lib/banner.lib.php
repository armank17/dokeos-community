<?php
//require_once(api_get_path(SYS_CODE_PATH).'inc/banner.inc.php');

/**
 * Determines the possible tabs (=sections) that are available.
 * This function is used when creating the tabs in the third header line and all the sections
 * that do not appear there (as determined by the platform admin on the Dokeos configuration menu links)
 * will appear in the right hand menu that appears on several other pages
 *
 * @return array containing all the possible tabs
 *
 * @version Dokeos 3.0
 */
function get_tabs() {
    global $language_interface, $_course, $rootAdminWeb, $_user;
    $menulinks = get_menu_links('header');
    foreach($menulinks as $menulink){        
       $link_path = $menulink['link_path'];
       if (!(strpos('http:', $link_path) !== FALSE || strpos('https:', $link_path) !== FALSE)) {
          $link_path = preg_replace('@^\/@', api_get_path(WEB_PATH), $link_path);
       }         
       // platform links
       if($menulink['link_type'] == MENULINK_TYPE_PLATFORM){
            switch($menulink['title']){
                case 'CampusHomepage':
                    $section = SECTION_CAMPUS;
                    break;
                case 'MyCourses':
                    $section = SECTION_COURSES;
                    break;
                case 'ModifyProfile':
                    $section = SECTION_MYPROFILE;
                    break;
                case 'MySpace':
                    $section = SECTION_TRACKING;
                    break;
                case 'SocialNetwork':
                    $section = SECTION_SOCIAL;
                    break;
                case 'MyAgenda':
                    $section = SECTION_MYAGENDA;
                    $menulink['link_path'] .= (!empty($_course['path']) ? '?coursePath=' . $_course['path'] . '&amp;courseCode=' . $_course['official_code'] : '' );
                    break;
                case 'PlatformAdmin':
                    if(api_is_platform_admin(true) == null) continue 2;
                    $section = SECTION_PLATFORM_ADMIN;
                    break;
                case 'Search':
                    $section = 'search';
                    break;
            }
            $navigation[$section]['link_type'] = MENULINK_TYPE_PLATFORM;
            $navigation[$section]['url']       = $link_path;
            $navigation[$section]['title']     = get_lang($menulink['title']); //, 'DLTT', $language);
            $navigation[$section]['target']    = $menulink['target'];            
       // other links
       } else {          
           $visible  = ($menulink['is_visible_anonymous'] || $menulink['is_visible_logged'] || $menulink['is_visible_course_in'] || $menulink['is_visible_tool_in']);
           if($visible){
                $navigation[$menulink['weight']]['url']    = $link_path;
                $navigation[$menulink['weight']]['title']  = $menulink['title'];
                $navigation[$menulink['weight']]['target'] = $menulink['target'];
           }
       }
    }  
    return $navigation;
}



/**
 * Determines the possible menu links that are available.
 * @param type $target 
 * @return type array containing all the enabled menu links
 */
function get_menu_links($category, $link_type = null) {
    global $_user, $language_interface;
    
    // Params
    $category = Database::escape_string($category);
    if(isset($link_type))
        $link_type = "'". Database::escape_string($link_type) ."'";
    else
        $link_type = 'NULL';
    
    // Current access url id
    $current_access_url_id = (api_get_current_access_url_id() < 0)? 1 : api_get_current_access_url_id();
    $language_id = api_get_language_id($language_interface);
    
    // Query
    $tbl_menu_link = Database::get_main_table(TABLE_MAIN_MENU_LINK);
    $tbl_node      = Database::get_main_table(TABLE_MAIN_NODE);
    
    $sql = 'SELECT menulink.* FROM '. $tbl_menu_link .' menulink '
            . 'LEFT JOIN '. $tbl_node .' node ON (node.menu_link_id = menulink.id) '
            . 'WHERE menulink.active = 1 '
              . 'AND menulink.category = \''. $category .'\' '
              . 'AND menulink.language_id IN ('. $language_id .', 0) '
              . 'AND menulink.link_type = COALESCE('. $link_type .', menulink.link_type) '
              . 'AND menulink.access_url_id = '. $current_access_url_id .' '
              . 'AND menulink.enabled = 1 '
              . 'AND COALESCE(node.enabled, 1) = 1 '
            . 'ORDER BY menulink.weight';
    //echo $sql;
    $rs = Database::query($sql);

    $in_course = isset($GLOBALS['course_home_visibility_type']); //((api_get_course_id() == '') || api_get_course_id() < 0)? false : true;
    $in_tool = defined('SHOWING_TOOL_HEADER');
    
    $links = array();
    while($row = Database::fetch_array($rs, 'ASSOC')){
        $link_visibility = $row['visibility'];
        
        $row['is_visible_anonymous']  = false;
        $row['is_visible_logged']     = false;
        $row['is_visible_course_in']  = false;
        $row['is_visible_tool_in']    = false;

        // anonymous
        if((($link_visibility & MENULINK_VISIBLE_ANONYMOUS) == MENULINK_VISIBLE_ANONYMOUS) /*&& (api_is_anonymous() > 0)*/)
            $row['is_visible_anonymous'] = true;

        // logged
        if((($link_visibility & MENULINK_VISIBLE_LOGGED) == MENULINK_VISIBLE_LOGGED) && $_user['user_id'] && !api_is_anonymous())
            $row['is_visible_logged'] = true;

        // course in
        if((($link_visibility & MENULINK_VISIBLE_COURSE_IN) == MENULINK_VISIBLE_COURSE_IN) && $in_course) //(is_string(api_get_course_id()) && (api_get_course_id() != '')))
            $row['is_visible_course_in'] = true;

        // course out        
        if((($link_visibility & MENULINK_VISIBLE_TOOL_IN) == MENULINK_VISIBLE_TOOL_IN) && $in_tool) //( (api_get_course_id() < 0) || (api_get_course_id() == '')))
            $row['is_visible_tool_in'] = true;
        
        if($row['is_visible_anonymous'] || $row['is_visible_logged'] || $row['is_visible_course_in'] || $row['is_visible_tool_in']){
            $links[] = $row;
        }
    }
    return $links;
}
?>