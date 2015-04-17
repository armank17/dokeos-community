<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class application_cms_models_CmsModel {
    private $_Id;
    public $_title;
    public $_content;
    private $_created_by;
    private $_deleted_by;
    private $_modified_by;
    private $_status;
    private $_ado;
    private $_table;
    public  $fieldValues = array();
    
    
    public function __construct(){
        $this->_ado = appcore_db_DB::conn();
        $this->_table = Database::get_course_table(TABLE_DOCUMENT_CMS);
    }
    public function saveCmsModel(){
        if (empty($this->fieldValues)) {
            return false;
        } 
        if (!empty($_POST['cmsId'])) {
            $where = ' WHERE id='.intval($_POST['cmsId']);
            $content=$this->fieldValues['content'];
            $title = $this->fieldValues['title'];
            //$updateSQL = $this->_ado->AutoExecute($this->_table, $this->fieldValues, 'UPDATE', $where);
            $sql = "UPDATE $this->_table SET title ='$title', content='$content', modified_by=".api_get_user_id().' '.$where;
            $this->_ado->Execute($sql);
            $lastId = $this->_ado->Insert_ID();
            return true;
            
        }
        else {
            //create the item'
            $insertSQL = $this->_ado->AutoExecute($this->_table, $this->fieldValues, 'INSERT');
            $lastId = $this->_ado->Insert_ID(); 
            return $lastId;
        }
       
    }
        
       
   public function getCms($where = '') {
               
        return $this->_ado->GetAll("SELECT * FROM $this->_table $where");
    }
        
   public static function search_engine_save($doc_id, $title, $content, $doc_path) {

    $courseid = api_get_course_id();
    isset($_POST['language'])? $lang=Database::escape_string($_POST['language']): $lang = 'english';

   require_once(api_get_path(LIBRARY_PATH) . 'search/DokeosIndexer.class.php');
   require_once(api_get_path(LIBRARY_PATH) . 'search/IndexableChunk.class.php');
   require_once(api_get_path(LIBRARY_PATH) . 'specific_fields_manager.lib.php');

   $specific_fields = get_specific_field_list();
   $ic_slide = new IndexableChunk();

   $all_specific_terms = '';
   foreach ($specific_fields as $specific_field) {
    if (isset($_REQUEST[$specific_field['code']])) {
     $sterms = trim($_REQUEST[$specific_field['code']]);
     if (!empty($sterms)) {
      $all_specific_terms .= ' ' . $sterms;
      $sterms = explode(',', $sterms);
      foreach ($sterms as $sterm) {
       $ic_slide->addTerm(trim($sterm), $specific_field['code']);
       add_specific_field_value($specific_field['id'], $course_id, TOOL_DOCUMENT, $doc_id, $sterm);
      }
     }
    }
   }

    $ic_slide->addValue("title", $title);
    $ic_slide->addCourseId($courseid);
    $ic_slide->addToolId(TOOL_DOCUMENT);
    $xapian_data = array(
      SE_COURSE_ID => $courseid,
      SE_TOOL_ID => TOOL_DOCUMENT,
      SE_DATA => array('doc_id' => (int)$doc_id),
      SE_USER => (int)api_get_user_id(),
    );
    $ic_slide->xapian_data = serialize($xapian_data);

    if (isset($_POST['search_terms'])) {
    $add_extra_terms = Security::remove_XSS($_POST['search_terms']).' ';
    }

    $file_content = $add_extra_terms.$content;
    $ic_slide->addValue("content", $file_content);

    $di = new DokeosIndexer();
    $di->connectDb(NULL, NULL, $lang);
    $di->addChunk($ic_slide);

    //index and return search engine document id
   $did = $di->index();

   if ($did) {
    // save it to db
    $tbl_se_ref = Database::get_main_table(TABLE_MAIN_SEARCH_ENGINE_REF);
    $sql = 'INSERT INTO %s (id, course_code, tool_id, ref_id_high_level, search_did)
          VALUES (NULL , \'%s\', \'%s\', %s, %s)';
    $sql = sprintf($sql, $tbl_se_ref, $courseid, TOOL_DOCUMENT, $doc_id, $did);
    api_sql_query($sql, __FILE__, __LINE__);

   }
  }
        public function ModellistCms(){
             $sql = "SELECT 
                        id,title,content,created_by,deleted_by,modified_by,status
                     FROM " .$this->_table.'ORDER BY id';
             $result = Database::query( $sql, __FILE__, __LINE__ );
        return  Database::fetch_object($result);
             
        }
        public function ModeldeleteCms($id){
           $sql="UPDATE $this->_table SET status = 0, deleted_by= ".  api_get_user_id()." WHERE id = $id";
           $this->_ado->Execute($sql);
            
        }
        public function Modelget_Id(){
            return $this->_Id;
        }
        public function Modelget_title(){
            return $this->_title;
        }
        public function Modelget_content(){
            return $this->_content;
        }
        public function Modelget_createdby(){
            return $this->_created_by;
        }
        public function Modelget_deletedby(){
            return $this->_deleted_by;
        }
        public function Modelget_modifiedby(){
            return $this->_modified_by;
        }
        public function Modelget_status(){
            return $this->_status;
        }
        public function Modelset_Id($Id){
            $this->_Id = $Id;
        }
        public function Modelset_title($title){
            $this->_title=$title;
        }
        public function Modelset_content($content){
             $this->_content=$content;
        }
        public function Modelset_createdby($createdBy){
             $this->_created_by=$createdBy;
        }
        public function Modelset_deletedby($deletedBy){
             $this->_deleted_by=$deletedBy;
        }
        public function Modelset_modifiedby($modifiedBy){
             $this->_modified_by=$modifiedBy;
        }
        public function Modelset_status($status){
             $this->_status=$status;
        }
}

?>
