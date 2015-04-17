<?php
include_once dirname(__FILE__) . '/../../../global.inc.php';
require_once dirname(__FILE__) . '/search_processor.class.php';

/**
 * Process wiki before pass it to search listing scripts
 */
class wiki_processor extends search_processor {
    private $items;
    /**
     * check if multisite is enable
     * @global configuration $_configuration
     * @param string $courseid
     * @return bool 
     */
    function is_enabled_multisite($courseid){
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
    
    function wiki_processor($rows) {
        $this->rows = $rows;
        
        foreach ($rows as $row_id => $row_val) {
            $courseid = $row_val['courseid'];
            $exist_relation = $this->is_enabled_multisite($courseid);
            if($exist_relation) {
                $se_data = $row_val['xapian_data'][SE_DATA];
                switch ($row_val['xapian_data'][SE_DATA]['type']) {
                    case SE_DOCTYPE_WIKI_WIKI:
                        $wiki_id = $se_data['wiki_id'];
                        $item = array(
                                  'courseid' => $courseid,
                                  'wiki_id' => $wiki_id
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
            list($thumbnail, $image, $name, $author, $page_id) = $this->get_information($item['courseid'], $item['wiki_id']);
            $url = api_get_path(WEB_CODE_PATH).'wiki/index.php?cidReq='.$item['courseid'].'&amp;action=showpage&title='.$name.'&page_id='.$page_id.'&amp;group_id=';
            $result = array(
                'toolid' => TOOL_WIKI,
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
     * Get wiki information
     */
    public function get_information($course_id, $wiki_id) {
        $tbl_wiki = Database::get_course_table_from_code($course_id, TABLE_WIKI);
        $sql = "SELECT * FROM $tbl_wiki WHERE id=$wiki_id";
        $res = Database::query($sql, __FILE__, __LINE__);
        while($row = Database::fetch_object($res)) {
           // Get the image path
            $thumbnail = api_get_path(WEB_PATH) . 'main/img/wiki_48.png';
            $image = $thumbnail; //FIXME: use big images
            $name = $row->title;
            // get author
            $user_data = api_get_user_info($row->user_id);
            $author = api_get_person_name($user_data['firstName'], $user_data['lastName']);
            $page_id = $row->page_id;
        }
        return array($thumbnail, $image, $name, $author, $page_id);
    }    
}
?>
