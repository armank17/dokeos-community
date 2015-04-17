<?php

/**
 * model collection
 * @author johnny, <johnny1402@gmail.com>
 * @package index 
 */
class application_announcement_models_collection extends ArrayObject
{
    public $database;
    
    public function __construct() {
        $this->database = new application_announcement_models_database();
    }
    
    public function getAnnouncement()
    {
        $connection = appcore_db_DB::conn();
        $connection->StartTrans();
        //$connection->execute();
            $where = "1=1";
            $datos= new application_announcement_models_table_TableAnnouncement();
            $array=$datos->find($where);
            $this->exchangeArray($array);
        $connection->CompleteTrans();
  	return $this;        
    }
    
    public function getUser()
    {
        $sql ='SELECT * FROM'.$this->database->get_main_table(TABLE_MAIN_USER);
        $arrayUser = $this->database->getData($sql);
        $this->exchangeArray($arrayUser);
        return $this;
    }
    
    public function getAnnouncementList()
    {
        $sql_group="SELECT * FROM ".Database :: get_course_table(TABLE_GROUP_USER)." WHERE user_id=".  api_get_user_id();
        $array_group = $this->database->getData($sql_group);//Database::query($sql_group);
        //add announcement ALL
        //array_push($array_group, 0);
            $query_group = " AND (ip.to_user_id = ".api_get_user_id()." OR ip.to_group_id IN (0)) ";
            if(count($array_group) > 0)
            {
                $array_id_group = array();
                foreach($array_group as $index=>$objGroup)
                    array_push ($array_id_group, $objGroup->id);
                
                array_push($array_id_group, '0');
                $query_group = " AND (ip.to_user_id = ".  api_get_user_id()." OR ip.to_group_id IN (".  implode(',', $array_id_group).")) ";
            }
            
        $sql ="SELECT DISTINCT a.id, a.title, a.content, date_format(a.end_date,'%b %d') AS announcementdate 
        FROM ".Database :: get_course_table(TABLE_ANNOUNCEMENT)." a
        INNER JOIN ".Database :: get_course_table(TABLE_ITEM_PROPERTY)." ip ON ip.ref = a.id
        WHERE ip.tool LIKE 'announcement'
        AND ip.visibility = 1 ";
        $array_user = api_get_user_info();
        if($array_user['status'] >1)// student or teacher
        {
            $sql.=$query_group;
            $sql.=" OR ip.insert_user_id =".api_get_user_id()." ";
        }
        $sql.=" ORDER BY a.display_order DESC"; 
        //var_dump($sql);
        //$where = " WHERE 1=1";
	//$sql = "SELECT id, title, content, date_format(end_date,'%b %d') AS announcementdate FROM {$this->database->get_course_table(TABLE_ANNOUNCEMENT)} $where ORDER BY id DESC";
        $arrayAnnouncement = $this->database->getData($sql);
        $this->exchangeArray($arrayAnnouncement);
        return $this;
    }
    
    public function get_announcement_dest($announcementId)
    {
        // Database table definition
        $t_item_propery = $this->database->get_course_table(TABLE_ITEM_PROPERTY);
        $sql = "SELECT * FROM $t_item_propery WHERE tool='".TOOL_ANNOUNCEMENT."' AND ref='".$this->database->escape_string($announcementId)."'";
        $arrayResult = $this->database->getData($sql);
        if(count($arrayResult)>0)
        {
            $array_group = array();
            $array_user = array();
            foreach ($arrayResult as $index=>$objProperty)
            {
                if(!empty($objProperty->to_user_id))
                    array_push($array_user, $objProperty->to_user_id);
                if(!empty($objProperty->to_group_id))
                    array_push($array_group, $objProperty->to_group_id);                
            }
        }
        return array('to_group_id'=>$array_group, 'to_user_id'=>$array_user);        
    }
    
    public function addAnnouncement($objAnnouncement)
    {
        $objAnnouncement->_database = CourseManager::get_name_database_course($objAnnouncement->_course) ;
        $objAnnouncement->_table = Database::get_course_table(TABLE_ANNOUNCEMENT);
        $objAnnouncement->display_order = $this->getDisplayOrderAnnouncement();
        $objAnnouncement = $this->database->insertData($objAnnouncement);
        
        $this->store_item_property($objAnnouncement,$objAnnouncement->_send_receivers, $objAnnouncement->_send_to, $objAnnouncement->id, 'AnnouncementAdded');
        
        $this->send_announcement_email($objAnnouncement->_send_receivers,$objAnnouncement->_send_to,$objAnnouncement->title,$objAnnouncement->description);
        
        return $objAnnouncement;
    }
    
    function send_announcement_email($send_receivers, $send_to, $title, $description){

            global $_user, $_course;		

            $from_name = ucfirst($_user['firstname']).' '.strtoupper($_user['lastname']);
            $from_email = $_user['mail'];
            $subject = $title;
            $message = $description;

            // create receivers array
            if($send_receivers == 0)
            { // full list of users
                    $receivers = CourseManager::get_user_list_from_course_code(api_get_course_id(), intval($_SESSION['id_session']) != 0, intval($_SESSION['id_session']));
            }
            else if($send_receivers == 1) {
                    $users_ids = array();
                    foreach($send_to as $to)
                    {
                            if(strpos($to, 'G') === false)
                            {
                                    $users_ids[] = intval(substr($to, 1));
                            }
                            else
                            {
                                    $groupId = intval(substr($to, 1));
                                    $users_ids = array_merge($users_ids, GroupManager::get_users($groupId));
                            }	
                            $users_ids = array_unique($users_ids);
                    }
                    if(count($users_ids) > 0)
                    {
                            $sql = 'SELECT lastname, firstname, email 
                                            FROM '.Database::get_main_table(TABLE_MAIN_USER).'
                                            WHERE user_id IN ('.implode(',', $users_ids).')';
                            $rsUsers = Database::query($sql, __FILE__, __LINE__);
                            while($userInfos = Database::fetch_array($rsUsers))
                            {
                                    $receivers[] = $userInfos;
                            }
                    }
            }
            else if($send_receivers == -1) {
                    $receivers[] = array(
                                                    'lastname' => $_user['lastName'],
                                                    'firstname' => $_user['firstName'],
                                                    'email' => $_user['mail']
                                                    );
            }

            foreach($receivers as $receiver)
            {
                    $to_name = ucfirst($receiver['firstname']).' '.strtoupper($receiver['lastname']);
                    $to_email = $receiver['email'];
                    api_mail_html($to_name, $to_email, $subject, $message, $from_name, $from_email);
            }
    }    
    
    public function store_item_property ($objAnnouncement, $send_receivers, $send_to, $id , $action_string) 
     {
            if ($send_receivers == 0) {
                    api_item_property_update ( api_get_course_info($objAnnouncement->_course), TOOL_ANNOUNCEMENT, $id, $action_string, $objAnnouncement->_user_id, '', '');
            }
            if ($send_receivers == 1) {
                    foreach ( $send_to as $key => $target ) {
                            if (substr ( $target, 0, 1 ) == 'U') {
                                    $user = substr ( $target, 1 );
                                    api_item_property_update ( api_get_course_info($objAnnouncement->_course), TOOL_ANNOUNCEMENT, $id, $action_string, $objAnnouncement->_user_id, '', $user);
                            }
                            if (substr ( $target, 0, 1 ) == 'G') {
                                    $group = substr ( $target, 1 );
                                    api_item_property_update ( api_get_course_info($objAnnouncement->_course), TOOL_ANNOUNCEMENT, $id, $action_string, $objAnnouncement->_user_id, $group, '');
                            }
                    }
            }
            if ($send_receivers == '-1') {
                    // adding to everybody
                    api_item_property_update ( api_get_course_info($objAnnouncement->_course), TOOL_ANNOUNCEMENT, $id, $action_string, $objAnnouncement->_user_id, '', '');
                    // making it invisible
                    api_item_property_update(api_get_course_info($objAnnouncement->_course), TOOL_ANNOUNCEMENT, $id, 'invisible');
            }
    }    
    
    public function getDisplayOrderAnnouncement()
    {
        $sql = "SELECT max(display_order) as max FROM ".Database::get_course_table(TABLE_ANNOUNCEMENT);
        $arrayResult = $this->database->getData($sql);
        $order = 0;
        foreach($arrayResult as $objOrder)
                $order = (int)$objOrder->max +1;
        return $order;       
    }
    
    public function getAnnouncementInfo($announcementId)
    {
        $t_item_propery = $this->database->get_course_table(TABLE_ITEM_PROPERTY);
		$table_user     = $this->database->get_main_table(TABLE_MAIN_USER);

        $sql = "SELECT 	ann.id		 		AS announcement_id,
                        ann.title 			AS announcement_title,
                        ann.content	 		AS announcement_content,
                        DATE_FORMAT(insert_date,'%b %d, %Y') AS announcement_date,
						user.firstname		AS firstname,
						user.lastname		AS lastname,
						ip.insert_user_id   AS insert_user_id,
						ip.to_user_id		AS to_user_id,
						ip.to_group_id		AS to_group_id,
						ip.visibility		AS visibility
                FROM {$this->database->get_course_table(TABLE_ANNOUNCEMENT)} ann, $t_item_propery ip, $table_user user
                WHERE ann.id = ip.ref
                AND tool = '".TOOL_ANNOUNCEMENT."'
				AND ip.insert_user_id = user.user_id
                AND ann.id = '".$this->database->escape_string($announcementId)."' ";
        $arrayAnnouncement = $this->database->getData($sql);
        $this->exchangeArray($arrayAnnouncement);
        return $this;        
    }
    
    public function deleteAnnouncement($objTemp)
    {
        $sql = "DELETE FROM {$this->database->get_course_table(TABLE_ANNOUNCEMENT)} WHERE id=".intval($objTemp->announcement_id);
        $affectedRow = $this->database->execute($sql);
        api_item_property_update(api_get_course_info(), TOOL_ANNOUNCEMENT, $objTemp->announcement_id, 'delete', $objTemp->user_id);
        return $affectedRow;
    }
    
    public function editAnnouncement($objAnnouncementBean)
    {
        $objAnnouncementBean->_database = CourseManager::get_name_database_course($objAnnouncementBean->_course) ;
        $objAnnouncementBean->_table = Database::get_course_table(TABLE_ANNOUNCEMENT);
        $where['id'] = $objAnnouncementBean->_id;
        $objAnnouncementBean = $this->database->update($objAnnouncementBean, $where);
        
        // first delete all the information in item_property
        $t_item_propery = $this->database->get_course_table(TABLE_ITEM_PROPERTY);
        $sql = "DELETE FROM $t_item_propery WHERE tool='".TOOL_ANNOUNCEMENT."' AND ref='".$this->database->escape_string($objAnnouncementBean->_id)."'";
        $this->database->execute($sql);

        // store in item_property (visibility, insert_date, target users/groups, visibility timewindow, ...)
        $this->store_item_property($objAnnouncementBean, $objAnnouncementBean->_send_receivers, $objAnnouncementBean->_send_to, $objAnnouncementBean->_id, 'AnnouncementEdited');			        
        return $objAnnouncementBean;
    }
    
    public function getAnnouncementByUser($user_id, $code_course)
    {
        $database = $this->getDatabaseCourseByCode($code_course);
        //$table_announcement = 
        $sql_group="SELECT * FROM ".$database.".announcement WHERE user_id=".$user_id;
        $array_group = $this->database->getData($sql_group);//Database::query($sql_group);
  
            //$query_group = " AND ip.to_user_id = ".$user_id." ";
            $query_group = " AND (ip.to_user_id = ".api_get_user_id()." OR ip.to_group_id IN (0)) ";
            if(count($array_group) > 0)
            {
                $array_id_group = array();
                foreach($array_group as $index=>$objGroup)
                    array_push ($array_id_group, $objGroup->id);
                
                array_push($array_id_group, '0');
                $query_group = " AND (ip.to_user_id = ".  $user_id." OR ip.to_group_id IN (".  implode(',', $array_id_group).")) ";
            }
            
        $sql ="SELECT DISTINCT a.id, a.title, a.content, date_format(a.end_date,'%b %d') AS announcementdate, a.end_date 
        FROM ".$database.".announcement a
        INNER JOIN ".$database.".item_property ip ON ip.ref = a.id
        WHERE ip.tool LIKE 'announcement'
        AND ip.visibility = 1 ";
        $array_user = api_get_user_info();
        if($array_user['status'] >1)// student or teacher
        {
            $sql.=$query_group;
            $sql.=" OR ip.insert_user_id =".api_get_user_id()." ";
        }
        $sql.=" ORDER BY a.display_order DESC";       
        $arrayUser = $this->database->getData($sql);
        $this->exchangeArray($arrayUser);
        return $this;        
    }
    
    public function getDatabaseCourseByCode($code_course)
    {
        $sql ="SELECT * FROM ".$this->database->get_main_table(TABLE_MAIN_COURSE)." WHERE code like '".$code_course."'";
        $arrayCourse = $this->database->getData($sql);
        foreach($arrayCourse as $index=>$objCourse)
            return $objCourse->db_name;
    }
    
    public function getAnnouncementById($id_announcement, $code_course)
    {
        $database = $this->getDatabaseCourseByCode($code_course);
        $sql= "SELECT * FROM ".$database.".announcement WHERE id=".$id_announcement;
        $arrayAnnouncement = $this->database->getData($sql);
        foreach($arrayAnnouncement as $index=>$objAnnouncement)
            return $objAnnouncement;        
    }
    
	/**
     *  Get max announcement id     
     *  @return`    int   announcement id
     */
    public function getLastAnnouncement() {
        $sql_group="SELECT * FROM ".Database :: get_course_table(TABLE_GROUP_USER)." WHERE user_id=".  api_get_user_id();
        $result_group = Database::query($sql_group);
        $array_group = array();
            while ($objGroup = Database::fetch_object($result_group)) {
                array_push($array_group, $objGroup);
            }

            //$query_group = " AND ip.to_user_id = ".api_get_user_id()." ";
            $query_group = " AND (ip.to_user_id = ".api_get_user_id()." OR ip.to_group_id IN (0)) ";
            if(count($array_group) > 0)
            {
                $array_id_group = array();
                foreach($array_group as $index=>$objGroup)
                    array_push ($array_id_group, $objGroup->id);
                
                array_push($array_id_group, '0');
                $query_group = " AND (ip.to_user_id = ".  api_get_user_id()." OR ip.to_group_id IN (".  implode(',', $array_id_group).")) ";
            }
            
        $sql ="SELECT DISTINCT a.id  
        FROM ".Database :: get_course_table(TABLE_ANNOUNCEMENT)." a
        INNER JOIN ".Database :: get_course_table(TABLE_ITEM_PROPERTY)." ip ON ip.ref = a.id
        WHERE ip.tool LIKE 'announcement'
        AND ip.visibility = 1 ";
        $array_user = api_get_user_info();
        if($array_user['status'] >1)// student or teacher
        {
            $sql.=$query_group;
            $sql.=" OR ip.insert_user_id =".api_get_user_id()." ";
        }
        $sql.=" ORDER BY a.id DESC LIMIT 1";         
        $rs = Database::query($sql);		
        if (Database::num_rows($rs) > 0) {
            while ($row = Database::fetch_object($rs)) {
                $announcementId = $row->id;
            }
        }

        return $announcementId;        
    } 
    
    public function getIdUser()
    {
        $sql =" SELECT * FROM evolution_dokeos_main.user WHERE user_id =1";
        $result = $this->database->fetchRow($sql);
        return $result;
    }
    
}