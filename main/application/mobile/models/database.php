<?php

/**
 * model databse
 * @author johnny, <johnny1402@gmail.com>
 * @package index 
 */
class application_mobile_models_database extends Database
{
    
    public function getData($sql)
    {
        $result = $this->query($sql);
        $array  = array();
        while ($objectStyle = $this->fetch_object($result))
        {
            array_push($array, $objectStyle);
        }
        return $array;        
    }
}