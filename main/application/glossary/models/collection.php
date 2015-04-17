<?php

/**
 * model collection
 * @author Joaquin, <jmike410@gmail.com>
 * @package index 
 */
class application_glossary_models_collection extends ArrayObject {

    public $database;

    public function __construct() {
        $this->database = new application_glossary_models_database();
    }

    /**
     * Insert New Glosary
     * @param $object Data to add
     * @return true or false
     */

    public function insertGlossary($object) {
        try {
            $course_id = api_get_course_id();
            $object->session_id = api_get_session_id();
            $object->_database = CourseManager::get_name_database_course($object->_course);
            $object->_table = Database::get_course_table(TABLE_GLOSSARY);
            // $objAnnouncement->display_order = $this->getDisplayOrderAnnouncement();
            $object = $this->database->insertData($object);
            if (!empty($object->id)) {
                //insert into item_property
                api_item_property_update(api_get_course_info($object->_course), TOOL_GLOSSARY, $object->id, 'GlossaryAdded', api_get_user_id(), null, null, null, null, api_get_session_id());
            }

            return 1;
        } catch (Exception $e) {
            echo $e->getMessage();
            return 2;
        }
    }
    
    /**
     * Edit item Selected - Glossary
     * @param $data, $_POST
     * @return return 
     */
    public function editGlossary($data, $course){
        try {
            $action = 1;
            $objItem = new stdClass();
            $objItem->name = $data['name'];
            $objItem->description = $data['description'];
            //$objItem->description = $data['description'];
            
             if(api_get_session_id() != $data['session_id']){
                 //var_dump('insertando con ' . api_get_session_id()); exit;
                $action = $this->insertGlossary($objItem);
                if($action == 1)
                    $action = 3;
            }else{
                $objItem->_database = CourseManager::get_name_database_course($course);
                $objItem->_table = Database::get_course_table(TABLE_GLOSSARY);

                //Question exist 
                $session_id = api_get_session_id();

                $this->database->update($objItem, array('glossary_id' => $data['glossary_id']));
                if (!empty($data['glossary_id'])) 
                    api_item_property_update(api_get_course_info($course), TOOL_GLOSSARY, $data['glossary_id'], 'GlossaryUpdated', api_get_user_id(), null, null, null, null, api_get_session_id());
            }
            
            return $action;
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }
    }
    
    /**
     * All Selected table Glossary 
     * @param $seach
     * @return query all
     */
    public function getGlossaryList($search) {
        $where = '';
        if (!empty($search)) {
            $where = ' AND t1.name like "' . $search . '%" ';
        }
        
        //condition for the session
        $condition_session = api_get_session_condition(api_get_session_id());
        //$condition_session = $this->getSessionUser();
        
        $sql = 'SELECT t1.glossary_id, t1.display_order, t1.name, t1.description, t1.session_id, '
            . 't2.insert_date, t2.lastedit_date '
            . 'FROM ' . Database :: get_course_table(TABLE_GLOSSARY) . ' t1 '
            . 'INNER JOIN ' . Database::get_course_table(TABLE_ITEM_PROPERTY) . ' t2 ON t1.glossary_id = t2.ref '
            . "WHERE tool = '" . glossary . "'"
            . $where
            . $condition_session
            . 'ORDER BY t1.name ASC';
        //echo $sql; exit;
        $data = $this->database->getData($sql);
        $this->exchangeArray($data);
        return $this;
    }
    
    /**
     * Edit item Selected - Glossary
     * @param $data, $_POST
     * @return return 
     */
    public function getSessionUser(){
        if(empty($_SESSION['_session'])){
            $session = api_get_session_id();
            if(!empty($session))
                $session = SESSION_DEFAULT . ', ' .  $session;
            
            $_SESSION['_session'] = ' and session_id in (' . $session . ') ';
        }
        
        return $_SESSION['_session'];
    }
    
    /**
     * item Selected 
     * @param $id, glossary_id
     * @return query row
     */
    public function getItemGlossary($id = ''){
        $condition_session = api_get_session_condition(api_get_session_id());
        //$condition_session = $this->getSessionUser();
        $sql = 'SELECT t1.glossary_id, t1.display_order, t1.name, t1.description, t1.session_id '
            //. 't2.insert_date, t2.lastedit_date '
            . 'FROM ' . Database :: get_course_table(TABLE_GLOSSARY) . ' t1 '
            //. 'INNER JOIN ' . Database::get_course_table(TABLE_ITEM_PROPERTY) . ' t2 ON t1.glossary_id = t2.ref '
            . 'WHERE t1.glossary_id = ' . $id . ' '
            . $condition_session;
        //echo $sql; exit;
        $data = $this->database->fetchRow($sql);
        return $data;
    }
    
    /**
     * Delete a glossary term
     * @param id, Id Glossary for delete
     * @return $action, [1,2]
     */
    public function deleteGlossary($data){
        try{
            
            $objDelete = new stdClass();
            
            $objDelete->glossary_id = $data['id'];
            
            $objDelete->_database = CourseManager::get_name_database_course($data['course']);
            $objDelete->_table = Database::get_course_table(TABLE_GLOSSARY);
            
            $objDelete = $this->database->delete($objDelete);
         
            if ($objDelete->_result) {
                //insert into item_property
                api_item_property_update(api_get_course_info(), TOOL_GLOSSARY, $objDelete->glossary_id, 'delete', api_get_user_id());
               // api_item_property_update(api_get_course_info($object->_course), TOOL_GLOSSARY, $object->id, 'GlossaryAddeds', api_get_user_id());
            }
            
            return 1;
            
        } catch (Exception $e){
            echo $e->getMessage();
        }
        
    }
    
    /**
     * Save Import file 
     * @param $data, array for save
     * @param $course, id course
     * @return $action, [1,2]
     */
    public function saveImport(array $data = Array(), $course = ''){
        try{
            // $objAnnouncement->display_order = $this->getDisplayOrderAnnouncement();
            //var_dump($data); exit;
            foreach ($data as $value):
                if(!empty($value['A']) and !empty($value['B'])):
                    $object = new stdClass();
                    $object->_database = CourseManager::get_name_database_course($course);
                    $object->_table = Database::get_course_table(TABLE_GLOSSARY);

                    $object->name = $value['A'];
                    $object->description = $value['B'];
                    $object->session_id = api_get_session_id();
                    //var_dump($object); exit;
                    $this->database->insertData($object);
                    if (!empty($object->id)) {
                    //insert into item_property
                        api_item_property_update(api_get_course_info($course), TOOL_GLOSSARY, $object->id, 'GlossaryAdded', api_get_user_id(), null, null, null, null, api_get_session_id());
                    }
                endif;
                
            endforeach;
            
            return 1;
            
        } catch (Exception $e){
            echo $e->getMessage();
        }
        
    }
    
    /*
     * Paginator
     */
    public function generatePaginator($data,$paginator)
    {
        //$paginator = new appcore_library_pagination_Paginator();
        $array_pag = array();
        if($data->count() > 0)
        {
            foreach($data->getIterator() as $value)
            {
                array_push($array_pag, $value);
            }
            $result = $paginator->generate($array_pag);
            $this->exchangeArray($result);
            return $this;
        }
        
    }

}