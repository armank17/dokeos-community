<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class application_courseDescription_models_courseDescription extends ArrayObject
{
    private $database;
    
    public function __construct() {
        $this->database = new application_courseDescription_models_database();
    }
    
    public function getCourseDescriptionList()
    {
        $condition_session = api_get_session_condition(api_get_session_id(), false, true);
        $sql = "SELECT * FROM ".$this->database->get_course_table(TABLE_COURSE_DESCRIPTION)." ".$condition_session." ORDER BY description_type ";
        $arrayUser = $this->database->getData($sql);
        //$this->exchangeArray($arrayUser);

        if(count($arrayUser) > 0)
        {
            $id_description = array();
            $array_type_session = array();
            foreach($arrayUser as $index=>$objDescription)
            {
                if($objDescription->session_id == api_get_session_id())
                {
                    array_push($id_description, $objDescription->id);
                    array_push($array_type_session, $objDescription->description_type);
                    unset($arrayUser[$index]);
                }
            }
            
            foreach($arrayUser as $indice=>$oDescription)
            {
                if(!in_array($oDescription->description_type, $array_type_session))
                {
                    array_push($id_description, $oDescription->id);
                }
            }
            
            $id = implode(",", $id_description);
            $sql = "SELECT * FROM ".$this->database->get_course_table(TABLE_COURSE_DESCRIPTION)." WHERE id IN (".$id.")  ORDER BY description_type ";
            $arrayUser = $this->database->getData($sql);
            $this->exchangeArray($arrayUser);            
        }
        return $this;
    }
    
    public function getObjectDescriptionByObject($objDescriptionCourse)
    {
        $sql ="SELECT * FROM ".$this->database->get_course_table(TABLE_COURSE_DESCRIPTION)." WHERE session_id IN (".api_get_session_id().") AND description_type =".$objDescriptionCourse->description_type;
        $objDescription = $this->database->fetchRow($sql);
        if($objDescription == null)
            return $objDescriptionCourse;
        else
            return $objDescription;
            
        
    }
    
    public function getCourseDescriptionInfo($description_type)
    {
        $current_session_id = api_get_session_id();
        $sql = "SELECT * FROM ".$this->database->get_course_table(TABLE_COURSE_DESCRIPTION)." WHERE description_type='".$this->database->escape_string($description_type)."' AND session_id='".$this->database->escape_string($current_session_id)."'";		
        $arrayResult = $this->database->fetchRow($sql);	
        if($arrayResult == NULL)
        {
            $current_session_id = 0;
            $sql = "SELECT * FROM ".$this->database->get_course_table(TABLE_COURSE_DESCRIPTION)." WHERE description_type='".$this->database->escape_string($description_type)."' AND session_id='".$this->database->escape_string($current_session_id)."'";		
            $arrayResult = $this->database->fetchRow($sql);	            
        }
        return $arrayResult;
    }
    
    public function addDescription($objDescription)
    {
        $objDescription->_database = $this->database->get_course_table(TABLE_COURSE_DESCRIPTION);
        $objDescription->_table = $this->database->get_course_table(TABLE_COURSE_DESCRIPTION);
        if($objDescription->_id > 0)
        {
            $where['id'] = $objDescription->_id;
            $objDescription = $this->database->update($objDescription, $where);
            api_item_property_update(api_get_course_info(), TOOL_COURSE_DESCRIPTION, $objDescription->_id, 'CourseDescriptionUpdated', api_get_user_id());
        }
        else
        {
            $objDescription = $this->database->insertData($objDescription);
            api_item_property_update(api_get_course_info(), TOOL_COURSE_DESCRIPTION, $objDescription->_id, 'CourseDescriptionAdded', api_get_user_id());
        }
        return $objDescription;        
    }
    
    public function deleteDescription($id_description)
    {
        $objDescription = new stdClass();
        $objDescription->_database = $this->database->get_course_table(TABLE_COURSE_DESCRIPTION);
        $objDescription->_table = $this->database->get_course_table(TABLE_COURSE_DESCRIPTION);
        $objDescription->id = $id_description;
        
        if($this->belongsSession($id_description))
            $objDescription = $this->database->delete($objDescription);
        else
        {
            $objDescription->_result = false;
            $objDescription->_message = 'No es posible eliminar descripcion de otra session';
        }
        return $objDescription;
    }
    
    public function belongsSession($id_description)
    {
        $sql = "SELECT * FROM ".$this->database->get_course_table(TABLE_COURSE_DESCRIPTION)." WHERE id =".$id_description." AND session_id=".  api_get_session_id();
        $objDescription = $this->database->fetchRow($sql);
        $returnValue = true;
        if($objDescription == null)
            $returnValue = false;
        return $returnValue;
    }
}