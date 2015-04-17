<?php
/* For licensing terms, see /dokeos_license.txt */
require_once 'webex.class.php';

define("UserWebex",1);
define("UserDokeos",2);

/**
 * Description of meetingmanager
 *
 * @author wlopez
 */
class MeetingManager extends WebexServices {

   private $table;
   private $table_user_webex;
   private $table_tool_webex;
   
   /**
    * The construct
    */
   public function __construct() {  
      parent::__construct(); 
      $this->table = Database::get_course_table(TABLE_WEBEX);
      $this->table_user_webex = Database::get_main_table(TABLE_WEBEX_USER);
      $this->table_tool_webex = Database::get_course_table(TABLE_TOOL_WEBEX);
   }
   
   /**
    * Authentica a user in webex
    * @param    string  Username
    * @param    string  Password
    * @param    string  Email
    * @return   bool
    */
   public function authenticateUser($username, $password, $email){      
      $params = array(
            'username' => $username,
            'password' => $password,
            'email'    => $email,
            'ns' => '',
            'elements' => array (
                'samlResponse'   => 'samlResponse message will go here',
            )    
      );
      $response = $this->call('authenticateUser', $params);
      if($response) {
         if(!$this->isError($response)){
            return true;
         }
      }
      return false;
   }
   
   /**
    * Get a login ticket from webex
    * @param    string  Username
    * @param    string  Password 
    * @param    string  Email
    * @return   bool
    */
   public function get_Login_Ticket($username, $password, $email){       
      $params = array(
            'username' => $username,
            'password' => $password,
            'email'    => $email,
            'ns' => '',
            'elements' => array()    
      );
      $response = $this->call('getLoginTicket', $params);       
      if($response) {
         if(!$this->isError($response)){
           return true;
         }
      }
      return false;
   }
   
   /**
    * Get a user login url for webex
    * @param    string  Username
    * @param    string  Password 
    * @param    string  Email
    * @return   string  Url
    */
   public function get_login_url_user($username, $password, $email){
      $loginUrl = ''; 
      $params = array(
            'username' => $username,
            'password' => $password,
            'email'    => $email,
            'ns' => '',
            'elements' => array('webExID'=>$username)    
      );
      
      $response = $this->call('getloginurlUser', $params); 

      if($response) {
         if(!$this->isError($response)){
           $simpleXml = @simplexml_load_string($response);  
           $loginUrl =  (string)$simpleXml->body->bodyContent->userLoginURL;           
         }
      }
      return $loginUrl;
   }
   
   /**
    * Get an url to join a meeting
    * @param    string   Meeting key
    * @param    string   Username
    * @return   array    joinMeetingURL and inviteMeetingURL
    */
   public function get_join_url_meeting($meetingkey, $user_name){
      $data = array();
      $params = array(
            'ns' => '',
            'elements' => array('sessionKey'=>$meetingkey, 'attendeeName'=>$user_name)    
      );
      $response = $this->call('getjoinurlMeeting', $params);
      if($response) {                  
         if(!$this->isError($response)){
            $simpleXml = @simplexml_load_string($response); 
            $data['joinMeetingURL']   = (string)$simpleXml->body->bodyContent->joinMeetingURL;
            $data['inviteMeetingURL'] = (string)$simpleXml->body->bodyContent->inviteMeetingURL;
         }
      }
      return $data;
   }
   
   /**
    * Get a host url for a meeting
    * @param    string   Meeting key
    * @return   string   Url 
    */
   public function get_host_url_Meeting($meetingkey) {
      $hostMeetingURL = ''; 
      $params = array(
            'ns' => '',
            'elements' => array('sessionKey'=>$meetingkey)    
      );
      $response = $this->call('gethosturlMeeting', $params);       
      if($response) {                  
         if(!$this->isError($response)){
            $simpleXml = @simplexml_load_string($response); 
            $hostMeetingURL = (string)$simpleXml->body->bodyContent->hostMeetingURL;            
         }
      }
      return $hostMeetingURL;
   }
   
   /**
    * Deactive a user in webex
    * @param    string    Username
    * @return   bool       
    */
   public function delete_user($webExId) {
      $deleted = false; 
      $params = array(
            'ns' => 'user',
            'elements' => array (
                'webExId'   => $webExId,
                'syncWebOffice' => 'true'
            )    
      );
      $response = $this->call('delUser', $params);      
      if($response) {
         $data = array();
         $simpleXml = @simplexml_load_string($response);
         if(!$this->isError($simpleXml)){
            $deleted = true;
         }
      }
      return $deleted;
   }
   
   /**
    * Get user list from webex
    * @return   array   user list
    */
   public function get_list_user(){
      $data = array(); 
      $params = array(
            'ns' => '',
            'elements' => array (
                'listControl'   => array('startFrom'=>'1', 'maximumNum'=>'20', 'listMethod'=>'AND'),
                'order' => array('orderBy'=>'UID', 'orderAD'=>'ASC')
            )    
      );
      $response = $this->call('delUser', $params);       
      if($response) {                  
         if(!$this->isError($response)){
            $simpleXml = @simplexml_load_string($response);
            $users = $simpleXml->body->bodyContent->user;
            foreach($users as $user){
               $data[] = array('webExId' => (string)$user->webExId,
                   'firstName' => (string)$user->firstName,
                   'lastName' => (string)$user->lastName,
                   'email' => (string)$user->email,
                   'registrationDate' => (string)$user->registrationDate,
                   'active' => (string)$user->active,
                   'timeZoneID' => (string)$user->timeZoneID);
            }            
         }
      }
      return $data;
   }
   
   /**
    * Create a user in webex 
    */
   public function createUser($user_id, $firstName, $lastName, $webexId, $email, $password, $meetingkey){ 
      $params = array(
            'ns' => 'user',
            'elements' => array (
                'firstName' => $firstName,
                'lastName'  => $lastName,
                'webExId'   => $webexId,
                'email'     => $email,
                'password'  => $password,
                'active'    => 'ACTIVATED',
                'schedulingPermission' => self::UID,
                'privilege' => array('host'=>'true')
            )    
      );
      $response = $this->call('createUser', $params); 

      if($response) {
         $data = array();              
         if(!$this->isError($response)){
            $simpleXml = @simplexml_load_string($response);
            //add user to dokeos           
            $user_id_webex = (string)$simpleXml->body->bodyContent->userId;            
            $this->add_user($user_id, $password, $user_id_webex);            
            //$data = array();
            return true;
         }
         else{
           $result = $this->message;
           Display::display_error_message($result.' : '.$webexId,true,true);
           return false;
         }
      }
      return false;
   }

   /**
    * Delete a meeting in webex
    */
   public function delete_meeting($meetingkey){
       
      // Delete a meeting
        $params = array(
                    'ns' => 'meet',
                    'elements' => array (
                        'meetingKey'    => $meetingkey
                    )    
        );
        $response = $this->call('delMeeting', $params); 
      
      if ($response) {
         if (!$this->isError($response)) {
            $this->clear_meeting_users($meetingkey);
            $this->delete_tool_meeting_dokeos($meetingkey);
            return true;
         }
         else {
             $result = $this->message;
             Display::display_error_message($result,true,true);         
             exit ();
         }
      }
      return false;
   }
   
   /**
    * Get meeting from webex
    * @param    string  Meeting key
    * @return   array 
    */
   public function get_meeting($meetingkey) {      
      $data = array();
      $params = array(
                    'ns' => 'meet',
                    'elements' => array (
                        'meetingKey'      => $meetingkey
                    )    
      );
      $response = $this->call('getMeeting', $params);        
      if ($response) {
         if(!$this->isError($response)){
            $simpleXml = @simplexml_load_string($response); 
            $accessControl = $simpleXml->body->bodyContent->accessControl;
            $metaData = $simpleXml->body->bodyContent->metaData;
            $attendees = $simpleXml->body->bodyContent->participants->attendees->attendee;
            
            $data['meetingPassword'] = (string)$accessControl->meetingPassword;
            $data['confName'] = (string)$metaData->confName;
            $data['meetingType'] = (string)$metaData->meetingType;
            $data['agenda'] = (string)$metaData->agenda;

            $schedule = $simpleXml->body->bodyContent->schedule;
            $data['startDate'] = (string)$schedule->startDate;
            $data['timeZoneID'] = (int)$schedule->timeZoneID;
            $data['timeZone'] = (string)$schedule->timeZone;
            $data['duration'] = (string)$schedule->duration;
            $data['openTime'] = (string)$schedule->openTime;
            
            $users = array();
            foreach($attendees as $attendee) {
               $person = $attendee->person;
               $user = array(
                   'name' => (string)$person->name,
                   'firstName' => (string)$person->firstName,
                   'lastName' => (string)$person->lastName,
                   'email' => (string)$person->email,
                   'contactID' => (string)$attendee->contactID,
                   'role' => (string)$attendee->role);
               $users[] = $user;
            }
            $data['users'] = $users;
         }
      }
      return $data;
   }
   
   /**
    * Get meeting list from webex
    * @param    int     Optional, user meeting 
    */
   public function get_list_meeting($user_meeting = 0){
       
      $params = array(
                    'ns' => '',
                    'elements' => array (
                        'listControl'=> array('startFrom'=>'1', 'maximumNum'=>'1000'),
                        'order'      => array('orderBy' => 'STARTTIME'),
                        'dateScope'  => ''                        
                    )    
        );
      $response = $this->call('LstsummaryMeeting', $params);  
      $data = array();
      if($response) {         
         $simpleXml = @simplexml_load_string($response);
         foreach($simpleXml->body->bodyContent->meeting as $meeting){			 	
            $user = $this->get_meeting_tool_dokeos((string)$meeting->meetingKey);            
            $author = "";                        
            if ($user) {
               $author = $user['firstname'] . ' ' . $user['lastname'] . ' (' . $user['username'] . ')';
               $exist = $this->get_meeting_dokeos((string)$meeting->meetingKey,$user_meeting);
               if($exist){ 
                  $data[] = array('meetingKey' => (string)$meeting->meetingKey,
                    'confName' => (string)$meeting->confName,
                    'meetingType' => (string)$meeting->meetingType,
                    'hostWebExID' => $author,
                    'timeZoneID' => (string)$meeting->timeZoneID,
                    'timeZone' => (string)$meeting->timeZone,
                    'status' => (string)$meeting->status,
                    'startDate' => (string)$meeting->startDate,
                    'duration' => (string)$meeting->duration,
                    'listStatus' => (string)$meeting->listStatus,);
               }
            }            
         }         
      }
      return $data;
   }
   
   /**
    * Update a meeting in webex 
    */
   public function update_meeting($meetingkey, $users, $confName, $startDate, $duration, $openTime) {
      
       
       // first We add the users to webex
       $added_users = array();
       if (!empty($users)) {
           foreach($users as $user) {
               $user_id = $user['user_id'];
               $exists = $this->exist_user($user_id);
               if (!$exists) {
                   //add user to table webex_user
                   if ($user['status'] == STUDENT) {
                      $password = api_generate_password().'AT'.$user_id;
                      $added = $this->createUser($user['user_id'], $user['firstName'], $user['lastName'], $user['username'], $user['mail'], $password);
                      if (!$added) {
                          continue;
                      }
                   }
               }
               $added_users[] = $user;
           }                      
       }

       // now We add the users to the meeting
       //if (!empty($added_users)) {
            // Update a meeting
            
            $params = array(
                        'ns' => 'meet',
                        'elements' => array (
                            'metaData'      => array('confName' => $confName),
                            'enableOptions' => array('chat'=>'true', 'poll'=>'true', 'audioVideo'=>'true', 'autoDeleteAfterMeetingEnd'=>'false'),
                            'schedule'      => array('startDate'=>$startDate, 'timeZoneID'=>'23', 'duration'=>$duration, 'openTime'=>$openTime),
                            'meetingkey'    => $meetingkey,
                            'participants'  => array('attendees' => $added_users)
                        )    
            );
            $response = $this->call('setMeeting', $params); 
            if ($response) {                          
                if (!$this->isError($response)) {                    
                    $this->clear_meeting_users($meetingkey);
                    // set users in dokeos
                    if (!empty($added_users)) {
                        foreach($added_users as $added_user) {
                          $this->set_meeting_users($meetingkey, $added_user['user_id']);
                        }
                    }
                   return true;
                }
                else {
                    Display::display_error_message($this->message, true, true);
                }
            }
       //}       
       return false;      
   }

   
   /**
    * Create a meeting in webex and relation with dokeos
    * @param    string      Meeting password
    * @param    string      Meeting name
    * @param    string      Meeting start date
    * @param    int         Meeting duration
    * @param    int         Meeting open time
    * @return   bool         
    */
   public function create_meeting($meetingPassword, $confName, $startDate, $duration, $openTime) {       
      // Create a meeting
      $params = array(
                'ns' => 'meet',
                'elements' => array (
                    'accessControl' => array('meetingPassword' => $meetingPassword),
                    'metaData'      => array('confName' => $confName),
                    'enableOptions' => array('chat'=>'true', 'poll'=>'true', 'audioVideo'=>'true', 'autoDeleteAfterMeetingEnd'=>'false'),
                    'schedule'      => array('startDate'=>$startDate, 'timeZoneID'=>'23', 'duration'=>$duration, 'openTime'=>$openTime)
                )    
      );
      $response = $this->call('createMeeting', $params);
      if($response) {                   
         if(!$this->isError($response)) {
            $simpleXml = @simplexml_load_string($response); 
            $meetingkey = (string)$simpleXml->body->bodyContent->meetingkey;
            $this->create_tool_meeting_dokeos($meetingkey, api_get_user_id());            
            $this->set_meeting_users($meetingkey, api_get_user_id());
            return true;
         }
      }
      return false;
   }

   
   public function set_meeting_users($meetingKey,$user_id){
      $sql = "INSERT INTO $this->table(meetingKey,user_id) VALUES('$meetingKey',$user_id)";
      Database::query($sql, __FILE__, __LINE__);
   }
   
   public function clear_meeting_users($meetingKey){       
      $sql = "DELETE FROM $this->table WHERE meetingKey='$meetingKey' AND user_id NOT IN(SELECT uo.user_owner FROM $this->table_tool_webex uo WHERE uo.meetingKey = '$meetingKey')";
      Database::query($sql, __FILE__, __LINE__);
   }
   
   public function add_user($user_id,$password,$user_id_webex){       
      $sql = "INSERT INTO $this->table_user_webex(user_id,user_id_webex,password) 
              VALUES($user_id,'$user_id_webex','$password');";            
      Database::query($sql);
   }
   public function exist_user($user_id,$option = 1){
      $sql = "";
      if($option == UserWebex){
         $sql = "SELECT user_id FROM $this->table_user_webex WHERE user_id=$user_id";
      } else if($option == UserDokeos) {
         $sql = "SELECT user_id_webex FROM $this->table_user_webex WHERE user_id_webex=$user_id";
      }
      $res = Database::query($sql);
      if(Database::num_rows(($res)) > 0){
         return true;
      }
      return false;
   }
   
   public function get_user($user_id,$option = 1){
      $sql = "";
      if($option == UserWebex){
         $sql = "SELECT * FROM $this->table_user_webex WHERE user_id=$user_id";
      } else if($option == UserDokeos) {
         $sql = "SELECT * FROM $this->table_user_webex WHERE user_id_webex=$user_id";
      }
      $res = Database::query($sql);
      return Database::fetch_array($res);
   }
   
   public function create_tool_meeting_dokeos($meetingKey,$user_id){
      $sql = "INSERT INTO $this->table_tool_webex(meetingKey,user_owner) VALUES('$meetingKey',$user_id)";
      $res = Database::query($sql);
      return true;
   }
   public function delete_tool_meeting_dokeos($meetingKey){
      $sql = "DELETE FROM $this->table_tool_webex WHERE meetingKey='$meetingKey'";
      $res = Database::query($sql);
   }
   public function get_meeting_tool_dokeos($meetingKey){
      $sql = "SELECT * FROM $this->table_tool_webex WHERE meetingKey='$meetingKey'";
      
     //var_dump($sql);
      
      $res = Database::query($sql);
      $row = Database::fetch_array($res);
      
      
      
      $user = api_get_user_info($row['user_owner']);
      return array_merge($row,$user);
   }
   
   public function isMeetingOwner($meetingKey, $userId) {
       $isOwner = false;
       $rs = Database::query("SELECT user_owner FROM {$this->table_tool_webex} WHERE meetingKey='$meetingKey' AND user_owner = '".intval($userId)."'");
       if (Database::num_rows($rs) > 0) {
           $isOwner = true;
       }
       return $isOwner;
   }
   
   public function get_meeting_dokeos($meetingKey, $user_meeting=0){
      $sql = "SELECT * FROM $this->table WHERE meetingKey = '$meetingKey'";
      if($user_meeting > 0){
         $sql .= " AND user_id=".$user_meeting;
      }
      $res = Database::query($sql);
      if(Database::num_rows($res) > 0){
         $row = Database::fetch_array($res);
         return $row;
      }
      return false;
   }
   
   /**
    * Get Meeting users in dokeos
    * @param    string      Meeting key
    * @return   array       Users
    */
   public function getDokeosMeetUsers($meetingKey) {
      $users = array(); 
      $rs = Database::query("SELECT user_id FROM {$this->table} WHERE meetingKey = '$meetingKey'");
      if (Database::num_rows($rs) > 0) {
          while ($row = Database::fetch_object($rs)) {
              $userInfo = api_get_user_info($row->user_id);
              $users[] = array(
                              'name' => $userInfo['username'],
                              'firstName' => $userInfo['firstname'],
                              'lastName' => $userInfo['lastname'],
                              'email' => $userInfo['mail']
                         );
          }
      }
      return $users;
   }
   
}
?>
