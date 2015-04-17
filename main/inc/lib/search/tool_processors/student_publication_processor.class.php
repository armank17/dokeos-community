<?php
include_once dirname(__FILE__) . '/../../../global.inc.php';
require_once dirname(__FILE__) . '/search_processor.class.php';

/**
 * Process student_publication  before pass it to search listing scripts
 */
class student_publication_processor extends search_processor {
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
    
    function student_publication_processor($rows) {
        $this->rows = $rows;
        // group by exercise
        foreach ($rows as $row_id => $row_val) {
            
            $courseid = $row_val['courseid'];
            $exist_relation = $this->is_enabled_multisite($courseid);
            if($exist_relation) {
                $se_data = $row_val['xapian_data'][SE_DATA];

                switch ($row_val['xapian_data'][SE_DATA]['type']) {
                    case SE_DOCTYPE_STUDENT_PUBLICATION:
                        $student_publication_id = $se_data['student_publication_id'];
                        $item = array(
                              'courseid' => $courseid,
                              'student_publication_id' => $student_publication_id
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
            list($thumbnail, $image, $name, $url) = $this->get_information($item['courseid'], $item['student_publication_id']);
            $url = api_get_path(WEB_CODE_PATH).'work/work.php?'.api_get_cidreq().'&amp;origin=&amp;gradebook=&amp;curdirpath='.$url;
            $result = array(
                'toolid' => TOOL_STUDENTPUBLICATION,
                'url' => $url,
                'thumbnail' => $thumbnail,
                'image' => $image,
                'title' => $name,
            );
            $results[] = $result;
        }
        return $results;
    }
    
    
    /**
     * Get student publication information
     */
    public function get_information($course_id, $student_publication_id) {
        
        $work_table = Database :: get_course_table(TABLE_STUDENT_PUBLICATION);
        $sql = "SELECT * FROM $work_table WHERE id=$student_publication_id";
        $res = Database::query($sql, __FILE__, __LINE__);
        while($row = Database::fetch_object($res)) {
           // Get the image path
            $thumbnail = api_get_path(WEB_PATH) . 'main/img/works_48.png';
            $image = $thumbnail; //FIXME: use big images
            $name = substr($row->url, 1, strlen($row->url)) ;
            $url = $row->url;
            // get author
            //$user_data = api_get_user_info($row->author);
            //$author = api_get_person_name($user_data['firstName'], $user_data['lastName']);
        }
        return array($thumbnail, $image, $name, $url);
    }   
    
}

?>
