<?php
/**
 * model collection
 * @author Joaquin, <jmike410@gmail.com>
 * @package index 
 */
class application_mobile_models_collection extends ArrayObject
{
    public $database;
    
    public function __construct() {
        $this->database = new application_mobile_models_database();
    }
    
    public function getUser($data)
    {
        $sql ='SELECT t1.*, t2.user_id as platformAdmin FROM'.$this->database->get_main_table(TABLE_MAIN_USER) . 't1';
        $sql .= ' left join ' . $this->database->get_main_table(TABLE_MAIN_ADMIN) . ' t2 on t1.user_id = t2.user_id';
        $sql .= ' where username = ' . "'{$data['usermob']}'";
        $sql .= ' and password = ' . "'" . md5($data['passmob']) . "'";
       
        $arrayUser = $this->database->getData($sql);
        $this->exchangeArray($arrayUser);
        return $this;
    }
    
    public function getCourseUser($user){
        $sql = '';
        $sql  = 'SELECT t1.course_code, t1.user_id, ';
        $sql .= '  t2.code, t2.directory, t2.db_name,t2.course_language,t2.title, ';
        $sql .= '  t2.description,t2.category_code,t2.visibility,visual_code';
        $sql .= ' FROM ' . $this->database->get_main_table(TABLE_MAIN_COURSE_USER) . ' t1';
        $sql .= ' INNER JOIN ' . $this->database->get_main_table(TABLE_MAIN_COURSE) . ' t2 ON t1.course_code = t2.code ';
        $sql .= ' WHERE user_id =  ' . $user;
        //echo $sql; exit;
        $arrayUser = $this->database->getData($sql);
        $this->exchangeArray($arrayUser);
        
        return $this;
    }
    
    /*
     * 
     */
    public function getSessionUser($user,$admin)
    {
        //Sessions for user
        if(!empty($admin))
            $users  ='SELECT DISTINCT t1.id_session, t1.status,';
        else
            $users  ='SELECT t1.id_session, t1.id_user, t1.status,';
        
        $sql  = $users;
        $sql .= ' t2.name,t2.description,date_start,date_end,image,session_admin_id,';
        $sql .= ' visibility,duration';
        $sql .= ' FROM ' . $this->database->get_main_table(TABLE_MAIN_SESSION_USER) . ' t1';
        $sql .= ' INNER JOIN ' . $this->database->get_main_table(TABLE_MAIN_SESSION) .  't2 ON t1.id_session = t2.id';
        if(empty($admin))
            $sql .= ' where t1.id_user = ' . "'" . $user . "'";
        $arrayUser = $this->database->getData($sql);
        $this->exchangeArray($arrayUser);
        return $this;
    }
    
    /*
     * 
     */
    public function getCourseSession($session){
        
        $sql  = 'SELECT t1.id_session, t1.course_code, ';
        $sql .= ' t2.code, t2.directory, t2.db_name,t2.course_language,t2.title,';
        $sql .= ' t2.description,t2.category_code,t2.visibility,visual_code,last_edit,';
        $sql .= ' creation_date,expiration_date,subscribe,unsubscribe';
        $sql .= ' FROM ' . $this->database->get_main_table(TABLE_MAIN_SESSION_COURSE) . ' t1';
        $sql .= ' INNER JOIN ' . $this->database->get_main_table(TABLE_MAIN_COURSE) .  't2 ON t1.course_code = t2.code';
        $sql .= ' INNER JOIN ' . $this->database->get_main_table(TABLE_MAIN_SESSION_USER) . 't3 ON t1.id_session = t3.id_session';
        $sql .= ' where t1.id_session = ' . "'" . $session . "'";
        $sql .= ' and t3.id_user = ' . "'" . api_get_user_id() . "'";
        //echo $sql; exit;
        $arrayUser = $this->database->getData($sql);
        $this->exchangeArray($arrayUser);
        
        return $this;
    }
    
}