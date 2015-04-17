<?php

/**
 * Description of wikimanager
 *
 * @author iflores
 */
class WikiManager {

    private $id, $page_id, $title, $content, $user_id, $comment;

    public function __construct($id = 0) {
        if($id != 0) {
            $tbl_wiki = Database::get_course_table(TABLE_WIKI);
            $sql = "SELECT * FROM $tbl_wiki WHERE id=$id";
            
            $res = Database::query($sql, __FILE__, __LINE__);
            while($row = Database::fetch_object($res)) {
                $this->id = $row->id;
                $this->page_id = $row->page_id;
                $this->title = $row->title;
                $this->content = $row->content;
                $this->user_id = $row->user_id;
                $this->comment = $row->comment;
            }
        }
    }
    /**
     * get the last id record of wiki
     * @return type int id of last record 
     */
    function get_last_id_wiki(){
        $tbl_wiki = Database::get_course_table(TABLE_WIKI);
        $sql = "SELECT MAX(id) AS id  FROM $tbl_wiki WHERE page_id=$this->page_id AND id<$this->id";
        $res = api_sql_query($sql, __FILE__, __LINE__);
        if (Database::num_rows($res) > 0) {
            $row = Database::fetch_array($res);
            return $row['id'];
        }
        return false;
    }
    
    /**
     * get the max id record of wiki
     * @param $page_id int page_id of wiki
     * @return int max id of wiki 
     */
    function get_max_id_wiki($page_id){
        $tbl_wiki = Database::get_course_table(TABLE_WIKI);
        $sql = "SELECT MAX(id) AS id  FROM $tbl_wiki WHERE page_id=$page_id";
        $res = api_sql_query($sql, __FILE__, __LINE__);
        if (Database::num_rows($res) > 0) {
            $row = Database::fetch_array($res);
            return $row['id'];
        }
        return false;
    }
    
    /**
     * delete the search engine 
     * @return type boolean true if the operation is successful
     */
    function search_engine_delete() {
        // update search enchine and its values table if enabled + check if database has write permissions
        $search_db_path = api_get_path(SYS_PATH).'searchdb';
        if (api_get_setting('search_enabled') == 'true' && extension_loaded('xapian') && is_writable($search_db_path)) {
            $course_id = api_get_course_id();
            // actually, it consists on delete terms from db, insert new ones, create a new search engine document, and remove the old one
            // get search_did
            $tbl_se_ref = Database::get_main_table(TABLE_MAIN_SEARCH_ENGINE_REF);
            
            $tip = $this->get_last_id_wiki();
            
            $sql = 'SELECT * FROM %s WHERE course_code=\'%s\' AND tool_id=\'%s\' AND ref_id_high_level=%s LIMIT 1';
            $sql = sprintf($sql, $tbl_se_ref, $course_id, TOOL_WIKI, $tip);
            $res = api_sql_query($sql, __FILE__, __LINE__);
            if (Database::num_rows($res) > 0) {
                 require_once(api_get_path(LIBRARY_PATH) . 'search/DokeosIndexer.class.php');
                 require_once(api_get_path(LIBRARY_PATH) . 'search/IndexableChunk.class.php');
                 $se_ref = Database::fetch_array($res);
                 $di = new DokeosIndexer();
                 isset($_POST['language']) ? $lang = Database::escape_string($_POST['language']) : $lang = 'english';
                 $di->connectDb(NULL, NULL, $lang);
                 $di->remove_document((int) $se_ref['search_did']);
                 $sql = 'DELETE FROM %s WHERE course_code=\'%s\' AND tool_id=\'%s\' AND ref_id_high_level=\'%s\'';
                 $sql = sprintf($sql, $tbl_se_ref, $course_id, TOOL_WIKI, $tip);
                 api_sql_query($sql, __FILE__, __LINE__);
                 return true;
            }
        } else {
               if (!is_writable($search_db_path)) {
                   return false;
               }
        }
    }
    
    /**
     * update the search engine 
     * @return type boolean true if the operation is successful
     */
    function search_engine_edit() {
        // update search enchine and its values table if enabled + check if database has write permissions
        $search_db_path = api_get_path(SYS_PATH).'searchdb';
        if (api_get_setting('search_enabled') == 'true' && extension_loaded('xapian') && is_writable($search_db_path)) {
            $course_id = api_get_course_id();
            // actually, it consists on delete terms from db, insert new ones, create a new search engine document, and remove the old one
            // get search_did
            $tbl_se_ref = Database::get_main_table(TABLE_MAIN_SEARCH_ENGINE_REF);
            
            $tip = $this->get_last_id_wiki();
            
            $sql = 'SELECT * FROM %s WHERE course_code=\'%s\' AND tool_id=\'%s\' AND ref_id_high_level=%s LIMIT 1';
            $sql = sprintf($sql, $tbl_se_ref, $course_id, TOOL_WIKI, $tip);
            $res = api_sql_query($sql, __FILE__, __LINE__);
            if (Database::num_rows($res) > 0 || $tip == NULL) {
                 require_once(api_get_path(LIBRARY_PATH) . 'search/DokeosIndexer.class.php');
                 require_once(api_get_path(LIBRARY_PATH) . 'search/IndexableChunk.class.php');
                 

                 $se_ref = Database::fetch_array($res);
                 $ic_slide = new IndexableChunk();
                 
                 // build the chunk to index
                 $ic_slide->addValue("title", $this->title);
                 $ic_slide->addCourseId($course_id);
                 $ic_slide->addToolId(TOOL_WIKI);
                 $xapian_data = array(
                     SE_COURSE_ID => $course_id,
                     SE_TOOL_ID => TOOL_WIKI,
                     SE_DATA => array('type' => SE_DOCTYPE_WIKI_WIKI, 'wiki_id' => (int) $this->id),
                     SE_USER => (int) api_get_user_id(),
                 );
                 $ic_slide->xapian_data = serialize($xapian_data);
                 $wiky_description = !empty($this->comment) ? $this->comment : $this->title;
                 //get the keyword
                 $searchkey = new SearchEngineManager();
                 $keyword = $searchkey->getKeyWord(TOOL_WIKI, $this->id);
                 
                 $file_content = $keyword .' '. $wiky_description.' '.$this->content;
                 $ic_slide->addValue("content", $file_content);
                 
                 $di = new DokeosIndexer();
                 isset($_POST['language']) ? $lang = Database::escape_string($_POST['language']) : $lang = 'english';
                 $di->connectDb(NULL, NULL, $lang);
                 
                 $di->remove_document((int) $se_ref['search_did']);
                 $di->addChunk($ic_slide);
                 //index and return search engine document id
                 $did = $di->index();
                 if ($did) {
                  // save it to db
                  $sql = 'DELETE FROM %s WHERE course_code=\'%s\' AND tool_id=\'%s\' AND ref_id_high_level=\'%s\'';
                  $sql = sprintf($sql, $tbl_se_ref, $course_id, TOOL_WIKI, $tip);
                  api_sql_query($sql, __FILE__, __LINE__);
                  
                  $sql = 'INSERT INTO %s (id, course_code, tool_id, ref_id_high_level, search_did)
                                    VALUES (NULL , \'%s\', \'%s\', %s, %s)';
                  $sql = sprintf($sql, $tbl_se_ref, $course_id, TOOL_WIKI, $this->id, $did);
                  api_sql_query($sql, __FILE__, __LINE__);
                  return true;
                 }
            }
        } else {
               if (!is_writable($search_db_path)) {
                   return false;
               }
        }
    }
    
    /**
     * Records the search engine 
     * @return type boolean true if the operation is successful
     */
    function search_engine_save() {
        $search_db_path = api_get_path(SYS_PATH) . 'searchdb';
        if (is_writable($search_db_path)) {
            $course_id = api_get_course_id();
            require_once(api_get_path(LIBRARY_PATH) . 'search/DokeosIndexer.class.php');
            require_once(api_get_path(LIBRARY_PATH) . 'search/IndexableChunk.class.php');
            require_once(api_get_path(LIBRARY_PATH) . 'specific_fields_manager.lib.php');

            $ic_slide = new IndexableChunk();
            $ic_slide->addValue('title', $this->title);
            $ic_slide->addCourseId($course_id);
            $ic_slide->addToolId(TOOL_WIKI);
            $xapian_data = array(
                SE_COURSE_ID => $course_id,
                SE_TOOL_ID => TOOL_WIKI,
                SE_DATA => array('type' => SE_DOCTYPE_WIKI_WIKI, 'wiki_id' => (int) $this->id),
                SE_USER => (int) api_get_user_id(),
            );
            $ic_slide->xapian_data = serialize($xapian_data);
            $wiky_description = !empty($this->comment) ? $this->comment : $this->title;
            
            //get the keyword
            $searchkey = new SearchEngineManager();
            $keyword = $searchkey->getKeyWord(TOOL_WIKI, $this->id);
            
            $file_content = $keyword .' '. $wiky_description.' '.$this->content;
            
            $ic_slide->addValue("content", $file_content);

            $di = new DokeosIndexer();
            isset($_POST['language']) ? $lang = Database::escape_string($_POST['language']) : $lang = 'english';
            $di->connectDb(NULL, NULL, $lang);
            $di->addChunk($ic_slide);
            //index and return search engine document id
            $did = $di->index();
            if ($did) {
                // save it to db
                $tbl_se_ref = Database::get_main_table(TABLE_MAIN_SEARCH_ENGINE_REF);
                $sql = 'INSERT INTO %s (id, course_code, tool_id, ref_id_high_level, search_did)
                                VALUES (NULL , \'%s\', \'%s\', %s, %s)';
                $sql = sprintf($sql, $tbl_se_ref, $course_id, TOOL_WIKI, $this->id, $did);
                api_sql_query($sql, __FILE__, __LINE__);
                return true;
            } else {
                return false;
            }
        }
    }

}

?>
