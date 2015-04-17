<?php

/* 
 * 
 */

class MenuLinkManager {
    
    static public function addMenuLinksToAccessUrlId($access_url_id){
        $tbl_menulinks = Database::get_main_table(TABLE_MAIN_MENU_LINK);
        
        $sql = "SELECT (MAX(id)) as max_id FROM $tbl_menulinks;";
        $rs  = Database::query($sql);
        $row = Database::fetch_array($rs, 'ASSOC');
        $menulink_max_id = $row['max_id'];
        
        $sql_insert = "INSERT INTO $tbl_menulinks(id, parent_id, language_id, weight, title, link_path, description, access_url_id, category, target, link_type, enabled, created_by, creation_date, modified_by, modification_date, active, visibility) VALUES ";
                
        $menulink_max_id++;
        $sql = $sql_insert."($menulink_max_id, 0, 0, -50, 'CampusHomepage', '/index.php',                  'Home page',      $access_url_id, 'header', '_self', 'platform', 1, 1, CURRENT_TIMESTAMP, 1, CURRENT_TIMESTAMP, 1, 15);";
        Database::query($sql);

        $menulink_max_id++;
        $sql = $sql_insert."($menulink_max_id, 0, 0, -49, 'MyCourses',      '/user_portal.php',            'My Courses',     $access_url_id, 'header', '_self', 'platform', 1, 1, CURRENT_TIMESTAMP, 1, CURRENT_TIMESTAMP, 1, 14);";
        Database::query($sql);

        $menulink_max_id++;
        $sql = $sql_insert."($menulink_max_id, 0, 0, -48, 'ModifyProfile',  '/main/auth/profile.php',      'Modify Profile', $access_url_id, 'header', '_self', 'platform', 0, 1, CURRENT_TIMESTAMP, 1, CURRENT_TIMESTAMP, 1, 14);";
        Database::query($sql);

        $menulink_max_id++;
        $sql = $sql_insert."($menulink_max_id, 0, 0, -47, 'MySpace',        '/main/reporting/index.php',   'My Space',       $access_url_id, 'header', '_self', 'platform', 1, 1, CURRENT_TIMESTAMP, 1, CURRENT_TIMESTAMP, 1, 14);";
        Database::query($sql);

        $menulink_max_id++;
        $sql = $sql_insert."($menulink_max_id, 0, 0, -46, 'SocialNetwork',  '/main/social/home.php',       'Social Network', $access_url_id, 'header', '_self', 'platform', 1, 1, CURRENT_TIMESTAMP, 1, CURRENT_TIMESTAMP, 1, 14);";
        Database::query($sql);

        $menulink_max_id++;
        $sql = $sql_insert."($menulink_max_id, 0, 0, -45, 'MyAgenda',       '/main/calendar/myagenda.php', 'My Agenda',      $access_url_id, 'header', '_self', 'platform', 1, 1, CURRENT_TIMESTAMP, 1, CURRENT_TIMESTAMP, 1, 14);";
        Database::query($sql);

        $menulink_max_id++;
        $sql = $sql_insert."($menulink_max_id, 0, 0, -44, 'PlatformAdmin',  '/main/admin/',                'Platform Admin', $access_url_id, 'header', '_self', 'platform', 1, 1, CURRENT_TIMESTAMP, 1, CURRENT_TIMESTAMP, 1, 14);";
        Database::query($sql);

        $menulink_max_id++;
        $sql = $sql_insert."($menulink_max_id, 0, 0, -43, 'Search',         '',                            'Search',         $access_url_id, 'header', '_self', 'platform', 1, 1, CURRENT_TIMESTAMP, 1, CURRENT_TIMESTAMP, 1, 14);";
        Database::query($sql);
        
        return $menulink_max_id;
    }
}

