<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of forummanager
 *
 * @author iflores
 */
class ForumManager {
    public $forum_id,$forum_title,$forum_comment;
    public function __construct($id = 0) {
        if($id != 0){
            $table_forums = Database::get_course_table(TABLE_FORUM);
            $sql = "SELECT * FROM $table_forums WHERE forum_id=$id";
            $res = Database::query($sql);
            while($row = Database::fetch_object($res)) {
                $this->forum_id = $row->forum_id;
                $this->forum_title = $row->forum_title;
                $this->forum_comment = $row->forum_comment;
            }
        }
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
            $sql = 'SELECT * FROM %s WHERE course_code=\'%s\' AND tool_id=\'%s\' AND ref_id_high_level=%s LIMIT 1';
            $sql = sprintf($sql, $tbl_se_ref, $course_id, TOOL_FORUM, $this->forum_id);
            $res = api_sql_query($sql, __FILE__, __LINE__);
            if (Database::num_rows($res) > 0) {
                 require_once(api_get_path(LIBRARY_PATH) . 'search/DokeosIndexer.class.php');
                 require_once(api_get_path(LIBRARY_PATH) . 'search/IndexableChunk.class.php');
                 $se_ref = Database::fetch_array($res);
                 $di = new DokeosIndexer();
                 isset($_POST['language']) ? $lang = Database::escape_string($_POST['language']) : $lang = 'english';
                 $di->connectDb(NULL, NULL, $lang);
                 $di->remove_document((int) $se_ref['search_did']);
                 // save it to db
                 $sql = 'DELETE FROM %s WHERE course_code=\'%s\' AND tool_id=\'%s\' AND ref_id_high_level=\'%s\'';
                 $sql = sprintf($sql, $tbl_se_ref, $course_id, TOOL_FORUM, $this->forum_id);
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
            $sql = 'SELECT * FROM %s WHERE course_code=\'%s\' AND tool_id=\'%s\' AND ref_id_high_level=%s LIMIT 1';
            $sql = sprintf($sql, $tbl_se_ref, $course_id, TOOL_FORUM, $this->forum_id);
            $res = api_sql_query($sql, __FILE__, __LINE__);
            if (Database::num_rows($res) > 0) {
                 require_once(api_get_path(LIBRARY_PATH) . 'search/DokeosIndexer.class.php');
                 require_once(api_get_path(LIBRARY_PATH) . 'search/IndexableChunk.class.php');
                 

                 $se_ref = Database::fetch_array($res);
                 $ic_slide = new IndexableChunk();
                 
                 // build the chunk to index
                 $ic_slide->addValue("title", $this->forum_title);
                 $ic_slide->addCourseId($course_id);
                 $ic_slide->addToolId(TOOL_FORUM);
                 $xapian_data = array(
                     SE_COURSE_ID => $course_id,
                     SE_TOOL_ID => TOOL_FORUM,
                     SE_DATA => array('type' => SE_DOCTYPE_FORUM_FORUM, 'forum_id' => (int) $this->forum_id, 'user_id' => (int) api_get_user_id()),
                     SE_USER => (int) api_get_user_id(),
                 );
                 $ic_slide->xapian_data = serialize($xapian_data);
                 $forum_description = !empty($this->forum_comment) ? $this->forum_comment : $this->forum_title;
                 //get the keyword
                 $searchkey = new SearchEngineManager();
                 $add_extra_terms = $searchkey->getKeyWord(TOOL_FORUM, $this->forum_id);
                 
                 $file_content = $add_extra_terms .' '. $forum_description;
                 $ic_slide->addValue("content", $file_content);
                 
                 $di = new DokeosIndexer();
                 isset($_POST['language']) ? $lang = Database::escape_string($_POST['language']) : $lang = 'english';
                 $di->connectDb(NULL, NULL, $lang);
                 
                 $di->remove_document((int) $se_ref['search_did']);
                 $di->addChunk($ic_slide);
                 //index and return search engine id
                 $did = $di->index();
                 if ($did) {
                  // save it to db
                  $sql = 'DELETE FROM %s WHERE course_code=\'%s\' AND tool_id=\'%s\' AND ref_id_high_level=\'%s\'';
                  $sql = sprintf($sql, $tbl_se_ref, $course_id, TOOL_FORUM, $this->forum_id);
                  api_sql_query($sql, __FILE__, __LINE__);
                  
                  $sql = 'INSERT INTO %s (id, course_code, tool_id, ref_id_high_level, search_did)
                                    VALUES (NULL , \'%s\', \'%s\', %s, %s)';
                  $sql = sprintf($sql, $tbl_se_ref, $course_id, TOOL_FORUM, $this->forum_id, $did);
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
            $ic_slide->addValue('title', $this->forum_title);
            $ic_slide->addCourseId($course_id);
            $ic_slide->addToolId(TOOL_FORUM);
            $xapian_data = array(
                SE_COURSE_ID => $course_id,
                SE_TOOL_ID => TOOL_FORUM,
                SE_DATA => array('type' => SE_DOCTYPE_FORUM_FORUM, 'forum_id' => (int) $this->forum_id, 'user_id' => (int) api_get_user_id()),
                SE_USER => (int) api_get_user_id(),
            );
            $ic_slide->xapian_data = serialize($xapian_data);
            $forum_description = !empty($this->forum_comment) ? $this->forum_comment : $this->forum_title;
            //get the keyword
            $searchkey = new SearchEngineManager();
            $add_extra_terms = $searchkey->getKeyWord(TOOL_FORUM, $this->forum_id);
            
            $file_content = $add_extra_terms .' '. $forum_description;
            
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
                $sql = sprintf($sql, $tbl_se_ref, $course_id, TOOL_FORUM, $this->forum_id, $did);
                api_sql_query($sql, __FILE__, __LINE__);
                return true;
            } else {
                return false;
            }
        }
    }
}

?>
