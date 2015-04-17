<?php
class application_security_models_security extends Database
{
    
    public function __construct() {
        
        $this->link = $this->openConnection();
    }    
    
    public function getSettings($key, $access_url='')
    {
        $sql= "SELECT * FROM ".$this->getConfiguration('main_database').".settings_current WHERE variable like '".$key."'";
        if(strlen(trim($access_url))>0)
            $sql.=" AND access_url =".$access_url;
        $result = $this->query($sql);
        while ($objectStyle = $this->fetch_object($result))
        {
            $objStyle= $objectStyle;
        }
        return $objStyle->selected_value;         
    }
    
    public function getUser($id_user)
    {
        $sql="SELECT * FROM ".$this->getConfiguration('main_database').".user WHERE user_id = ".$id_user;
        $result = $this->query($sql);
        while ($objectUser = $this->fetch_object($result))
        {
            $objUser= $objectUser;
        }
        return $objUser;
    }

 
}