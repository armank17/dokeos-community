<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SearchEngineManager
 *
 * @author wlopez
 */
class SearchEngineManager {
    public $id,$idobj,$course_code,$tool_id,$value;
    
    public function __construct($id = 0) {
        if($id != 0){
            $tblmain = Database::get_main_table(TABLE_MAIN);
            $sql = "SELECT * FROM $tblmain WHERE id= $id";
            $rs = Database::query($sql,__FILE__,__LINE__);
            while($rw = Database::fetch_array()){
                $this->id = $rw['id'];
                $this->idobj = $rw['idobj'];
                $this->course_code = $rw['course_code'];
                $this->tool_id = $rw['tool_id'];
                $this->value = $rw['value'];
            }
        }
    }
    
    public function save(){
        $tblmain = Database::get_main_table(TABLE_MAIN_SEARCH_ENGINE_KEYWORDS);
        $sql = "INSERT INTO $tblmain(idobj,course_code,tool_id,value) 
            VALUES($this->idobj,'$this->course_code','$this->tool_id','$this->value')";
        Database::query($sql);
        $this->id = Database::insert_id();
    }
    
    public function getKeyWord($tool_id,$idobj,$course_code = null){
        if(empty($course_code)){
            $course_code = api_get_course_id();
        }
        $tblmainkw = Database::get_main_table(TABLE_MAIN_SEARCH_ENGINE_KEYWORDS);
        $sql = "SELECT value FROM $tblmainkw WHERE tool_id='$tool_id' AND course_code='$course_code' 
                AND idobj=$idobj";
        $rs = Database::query($sql,__FILE__,__LINE__);
        $keyword = "";
        while($rw = Database::fetch_array($rs)){
            $keyword = $rw['value'];
        }
        return $keyword;
    }
    
    public function updateKeyWord(){
        if(empty($this->course_code)){
            $this->course_code = api_get_course_id();
        }
        $tblmainkw = Database::get_main_table(TABLE_MAIN_SEARCH_ENGINE_KEYWORDS);
        $sql = "UPDATE $tblmainkw SET value='$this->value' WHERE tool_id='$this->tool_id' 
                AND course_code='$this->course_code' AND idobj=$this->idobj";
        Database::query($sql,__FILE__,__LINE__);
    }
    
    public function deleteKeyWord(){
        if(empty($this->course_code)){
            $this->course_code = api_get_course_id();
        }
        $tblmainkw = Database::get_main_table(TABLE_MAIN_SEARCH_ENGINE_KEYWORDS);
        $sql = "DELETE FROM $tblmainkw WHERE tool_id='$this->tool_id' 
                AND course_code='$this->course_code' AND idobj=$this->idobj";
        Database::query($sql,__FILE__,__LINE__);
    }
}

?>
