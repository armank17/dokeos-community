<?php
include_once dirname(__FILE__) . '/../../../global.inc.php';
require_once dirname(__FILE__) . '/search_processor.class.php';

/**
 * Process forum before pass it to search listing scripts
 */
class forum_processor extends search_processor {
    private $items;
    
    /**
     * check if multisite is enable
     * @global configuration $_configuration
     * @param string $courseid
     * @return bool 
     */
    function is_enabled_multisite($courseid){
        //check if multisite is enable
        global $_configuration;
        if($_configuration['multiple_access_urls']){
            require_once api_get_path(LIBRARY_PATH).'urlmanager.lib.php';
            $url = new UrlManager();
            $access_url_id = api_get_current_access_url_id();
            $exist_relation = $url->relation_url_course_exist($courseid, $access_url_id);
            return $exist_relation > 0 ? true : false;
        }
        return true;;
    }
    
    function forum_processor($rows) {
        $this->rows = $rows;
        foreach ($rows as $row_id => $row_val) {
            $courseid = $row_val['courseid'];
            $exist_relation = $this->is_enabled_multisite($courseid);
            if($exist_relation) {
                $se_data = $row_val['xapian_data'][SE_DATA];
                switch ($row_val['xapian_data'][SE_DATA]['type']) {
                    case SE_DOCTYPE_FORUM_FORUM:
                        $forum_id = $se_data['forum_id'];
                        $user_id = $se_data['user_id'];
                        $item = array(
                                  'courseid' => $courseid,
                                  'forum_id' => $forum_id,
                                  'user_id' => $user_id
                                );
                        $this->items[] = $item;
                        break;
                }
            }
        }
        
    }
    /**
     * Get tha data of process 
     * @return array with data 
     */
    public function process() {
        $results = array();
        foreach($this->items as $item) {
            list($thumbnail, $image, $name, $author) = $this->get_information($item['courseid'], $item['forum_id'],$item['user_id']);
            $url = api_get_path(WEB_CODE_PATH).'forum/viewforum.php?cidReq='.$item['courseid'].'&amp;gidReq=&amp;forum='.$item['forum_id'];
            $result = array(
                'toolid' => TOOL_FORUM,
                'url' => $url,
                'thumbnail' => $thumbnail,
                'image' => $image,
                'title' => $name,
                'author' => $author,
            );
            $results[] = $result;
        }
        return $results;
    }
    
    /**
     * Get forum information
     */
    public function get_information($course_id, $forum_id, $user_id) {
        $table_forums = Database::get_course_table_from_code($course_id,TABLE_FORUM);
        $sql = "SELECT * FROM $table_forums WHERE forum_id=$forum_id";
        
        $res = Database::query($sql);
        $res = Database::query($sql, __FILE__, __LINE__);
        while($row = Database::fetch_object($res)) {
           // Get the image path
            $thumbnail = api_get_path(WEB_PATH) . 'main/img/forum_48.png';
            $image = $thumbnail; //FIXME: use big images
            $name = $row->forum_title;
            // get author
            $user_data = api_get_user_info($user_id);
            $author = api_get_person_name($user_data['firstName'], $user_data['lastName']);
        }
        return array($thumbnail, $image, $name, $author);
    }    
}
?>
