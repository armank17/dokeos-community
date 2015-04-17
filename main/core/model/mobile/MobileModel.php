<?php

class MobileModel
{
    /**
     * get tool list in format arrayobject for iterator view 
     * @param type $authorization 
     */
    public function getToolListByAuthorization($authorization)
    {
        $course_tool_table = Database::get_course_table(TABLE_TOOL_LIST);
	//condition for the session
	$session_id = api_get_session_id();
	$condition_session = api_get_session_condition($session_id,true,true);        
        //var_dump($course_tool_table);die();
        switch ($authorization)
        {
            default:
            $sql ="SELECT * FROM ".$course_tool_table." WHERE visibility = '1' AND (category = 'authoring' OR category = 'interaction') AND (name != 'author' AND name != 'oogie') ".$condition_session." ORDER BY id";
            $result = Database::query($sql);
            while ($objectTool = Database::fetch_object($result))
            {
                $arrayObject[]= $objectTool;
            }
            return $arrayObject;
        }
    }

}
?>