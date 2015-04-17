<?php //$id: $
/* For licensing terms, see /dokeos_license.txt */
/**
==============================================================================
*	This class provides methods for sessions management.
*	Include/require it in your code to use its features.
*
*	@package dokeos.library
==============================================================================
*/
require_once('display.lib.php');
require_once('mail.lib.inc.php');
class SessionManager {
	private function __construct() {

	}
    /**
     * Fetches a session from the database
     * @param   int     Session ID
     * @return  array   Session details (id, id_coach, name, nbr_courses, nbr_users, nbr_classes, date_start, date_end, nb_days_access_before_beginning,nb_days_access_after_end, session_admin_id)
     */
    public static function fetch($id) {
    	$t = Database::get_main_table(TABLE_MAIN_SESSION);
        if ($id != strval(intval($id))) { return array(); }
        $s = "SELECT * FROM $t WHERE id = $id";
        $r = Database::query($s,__FILE__,__LINE__);
        if (Database::num_rows($r) != 1) { return array(); }
        return Database::fetch_array($r,'ASSOC');
    }
	 /**
	  * Create a session
	  * @author Carlos Vargas <carlos.vargas@dokeos.com>,from existing code
	  * @param	string 		name
	  * @param 	integer		year_start
	  * @param 	integer		month_start
	  * @param 	integer		day_start
	  * @param 	integer		year_end
	  * @param 	integer		month_end
	  * @param 	integer		day_end
	  * @param 	integer		nb_days_acess_before
	  * @param 	integer		nb_days_acess_after
	  * @param 	integer		nolimit
	  * @param 	string		coach_username
	  * @param 	integer		id_session_category
	  * @return $id_session;
	  **/
	public static function create_session ($sname,$sdescription,$syear_start,$smonth_start,$sday_start,$syear_end,$smonth_end,$sday_end,$nolimit,$coach_username, $id_session_category,$scost, $certif_template = 0, $certificate_tool = '', $certif_min_score = 0.00, $certif_min_progress = 0.00, $sduration = 1, $sduration_type='week', $simage = '') {
		global $_user;
		$name= trim($sname);
		$description= trim($sdescription);
		$year_start= intval($syear_start);
		$month_start=intval($smonth_start);
		$day_start=intval($sday_start);
		$year_end=intval($syear_end);
		$month_end=intval($smonth_end);
		$day_end=intval($sday_end);
                $cost = intval($scost);
                $duration = intval($sduration);
                $duration_type = trim($sduration_type);
                $image = trim($simage);
	//	$nb_days_acess_before = intval($snb_days_acess_before);
	//	$nb_days_acess_after = intval($snb_days_acess_after);
		$id_session_category = intval($id_session_category);
	//	$id_visibility = intval($id_visibility);
		$tbl_user = Database::get_main_table(TABLE_MAIN_USER);
		$tbl_session = Database::get_main_table(TABLE_MAIN_SESSION);

		$sql = 'SELECT user_id FROM '.$tbl_user.' WHERE username="'.Database::escape_string($coach_username).'"';
		$rs = Database::query($sql, __FILE__, __LINE__);
		$id_coach = Database::result($rs,0,'user_id');

		if (empty($nolimit)) {			
			$date_start="$year_start-".(($month_start < 10)?"0$month_start":$month_start)."-".(($day_start < 10)?"0$day_start":$day_start);
			$date_end="$year_end-".(($month_end < 10)?"0$month_end":$month_end)."-".(($day_end < 10)?"0$day_end":$day_end);
		} else {
			$id_visibility = 1; // by default is read only
			$date_start="0000-00-00";
			$date_end="0000-00-00";
		}
		if (empty($name)) {
			$msg=get_lang('SessionNameIsRequired');
			return $msg;
		} elseif (empty($coach_username))   {
			$msg=get_lang('CoachIsRequired');
			return $msg;
		} elseif (empty($nolimit) && (!$month_start || !$day_start || !$year_start || !checkdate($month_start,$day_start,$year_start))) {
			$msg=get_lang('InvalidStartDate');
			return $msg;
		} elseif (empty($nolimit) && (!$month_end || !$day_end || !$year_end || !checkdate($month_end,$day_end,$year_end))) {
			$msg=get_lang('InvalidEndDate');
			return $msg;
		} elseif(empty($nolimit) && (strtotime($date_start) >= strtotime($date_end))){
			$msg = get_lang('StartDateShouldBeBeforeEndDate');
			return $msg;
		} else {
			$rs = Database::query("SELECT 1 FROM $tbl_session WHERE name='".addslashes($name)."'");
			if(Database::num_rows($rs)) {
				$msg=get_lang('SessionNameAlreadyExists');
				return $msg;
			} else {
                                /*if(empty($nolimit))
                                    $date_start = TimeZone::ConvertTimeFromServerToUser(api_get_user_id(), $date_start);
                                if(empty($nolimit))
                                    $date_end   = TimeZone::ConvertTimeFromServerToUser(api_get_user_id(), $date_end);*/
                                
				$sql_insert = "INSERT INTO $tbl_session SET 
                                                name = '".Database::escape_string($name)."',
                                                description = '".Database::escape_string($description)."',
                                                date_start = '$date_start',
                                                date_end = '$date_end',
                                                id_coach = '$id_coach',
                                                session_admin_id = ".intval($_user['user_id']).", 
                                                session_category_id = ".$id_session_category.",
                                                cost = ".$cost.",
                                duration ='".$duration."',
                                duration_type ='".$duration_type."',
                                image ='".$image."',
                                                certif_template = '".$certif_template."',
                                                certif_tool = '".$certificate_tool."',
                                                certif_min_score = '".$certif_min_score."',
                                                certif_min_progress = '".$certif_min_progress."'
                                              ";	                       
				Database::query($sql_insert ,__FILE__,__LINE__);
				$id_session=Database::insert_id();

				// add event to system log
				$time = time();
				$user_id = api_get_user_id();
				event_system(LOG_SESSION_CREATE, LOG_SESSION_ID, $id_session, $time, $user_id);

				return $id_session;
			}
		}
	}
        
	/**
	 * Edit a session
	 * @author Carlos Vargas <carlos.vargas@dokeos.com>,from existing code
	 * @param	integer		id
	 * @param	string 		name
	 * @param 	integer		year_start
	 * @param 	integer		month_start
	 * @param 	integer		day_start
	 * @param 	integer		year_end
	 * @param 	integer		month_end
	 * @param 	integer		day_end
	 * @param 	integer		nb_days_acess_before
	 * @param 	integer		nb_days_acess_after
	 * @param 	integer		nolimit
	 * @param 	integer		id_coach
	 * @param 	integer		id_session_category
	 * @return $id;
	 * The parameter id is a primary key
	**/
	public static function edit_session ($id,$name,$description,$year_start,$month_start,$day_start,$year_end,$month_end,$day_end,$nolimit,$id_coach, $id_session_category,$cost, $certif_template = 0, $certificate_tool = '', $certif_min_score = 0.00, $certif_min_progress = 0.00, $duration = 1, $duration_type='week', $image='') {
		global $_user;
		$name=trim(stripslashes($name));
		$description=trim(stripslashes($description));
		$year_start=intval($year_start);
		$month_start=intval($month_start);
		$day_start=intval($day_start);
		$year_end=intval($year_end);
		$month_end=intval($month_end);
		$day_end=intval($day_end);
		$id_coach= intval($id_coach);
                $cost = intval($cost);
        $duration = intval($duration);
        $duration_type = trim($duration_type);
        $image = trim($image);
		$id_session_category = intval($id_session_category);

		$tbl_user		= Database::get_main_table(TABLE_MAIN_USER);
		$tbl_session	= Database::get_main_table(TABLE_MAIN_SESSION);
		$tbl_session_rel_course = Database::get_main_table(TABLE_MAIN_SESSION_COURSE);

               
		if (empty($nolimit)) {
			$date_start="$year_start-".(($month_start < 10)?"0$month_start":$month_start)."-".(($day_start < 10)?"0$day_start":$day_start);
			$date_end="$year_end-".(($month_end < 10)?"0$month_end":$month_end)."-".(($day_end < 10)?"0$day_end":$day_end);
		} else {
			$date_start="000-00-00";
			$date_end="000-00-00";
			$id_visibility = 1;//force read only
		}
		if (empty($name)) {
			$msg=get_lang('SessionNameIsRequired');
			return $msg;
		} elseif (empty($id_coach))   {
			$msg=get_lang('CoachIsRequired');
			return $msg;
		} elseif (empty($nolimit) && (!$month_start || !$day_start || !$year_start || !checkdate($month_start,$day_start,$year_start))) {
			$msg=get_lang('InvalidStartDate');
			return $msg;
		} elseif (empty($nolimit) && (!$month_end || !$day_end || !$year_end || !checkdate($month_end,$day_end,$year_end))) {
			$msg=get_lang('InvalidEndDate');
			return $msg;
		} elseif (empty($nolimit) && (strtotime($date_start) >= strtotime($date_end))) {
			$msg=get_lang('StartDateShouldBeBeforeEndDate');
			return $msg;
		} elseif( (int)$certif_min_progress < 0 ){
                        $msg=get_lang('CertificateMinimumScoreCannotBeLessToZero');
			return $msg;
                } else {
			$rs = Database::query("SELECT id FROM $tbl_session WHERE name='".Database::escape_string($name)."'");
			$exists = false;
			while ($row = Database::fetch_array($rs)) {
				if($row['id']!=$id)
					$exists = true;
			}
			if ($exists) {
				$msg=get_lang('SessionNameAlreadyExists');
				return $msg;
			} else {
                                /*if(empty($nolimit))
                                    $date_start = TimeZone::ConvertTimeFromServerToUser(api_get_user_id(), $date_start);
                                if(empty($nolimit))
                                    $date_end   = TimeZone::ConvertTimeFromServerToUser(api_get_user_id(), $date_end);*/
                            
				$sql="UPDATE $tbl_session " .
					"SET name='".Database::escape_string($name)."',
					 description='".Database::escape_string($description)."',
						date_start='".$date_start."',
						date_end='".$date_end."',
						id_coach='".$id_coach."',
                                                cost ='".$cost."',
                                                duration ='".$duration."',
                                                duration_type ='".$duration_type."',
                                                image ='".$image."',
						session_category_id = ".$id_session_category.",
                                                certif_template = '".$certif_template."',
                                                certif_tool = '".$certificate_tool."',
                                                certif_min_score = '".(string)$certif_min_score."',
                                                certif_min_progress = '".$certif_min_progress."'
					  WHERE id='$id'";				  
					  
				Database::query($sql,__FILE__,__LINE__);
				return $id;
			}
		}
	}
        
        public static function deleteSessionEcommerce($id_session)
        {
            $sql = "DELETE FROM ".Database :: get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS)." WHERE id_session = '".$id_session."'";
            Database::query($sql, __FILE__, __LINE__);
        }
	/**
	 * Delete session
	 * @author Carlos Vargas <carlos.vargas@dokeos.com>, from existing code
	 * @param	array	id_checked
	 * @param   boolean  optional, true if the function is called by a webservice, false otherwise.
     * @return	void	Nothing, or false on error
	 * The parameters is a array to delete sessions
	 **/
	public static function delete_session ($id_checked,$from_ws = false) {
		$tbl_session=						Database::get_main_table(TABLE_MAIN_SESSION);
		$tbl_session_rel_course=			Database::get_main_table(TABLE_MAIN_SESSION_COURSE);
		$tbl_session_rel_course_rel_user=	Database::get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
		$tbl_session_rel_user=				Database::get_main_table(TABLE_MAIN_SESSION_USER);
		$tbl_user = 						Database::get_main_table(TABLE_MAIN_USER);
                
                $tbl_session_rel_category = Database::get_main_table(TABLE_MAIN_SESSION_REL_CATEGORY);    
                
		global $_user;
		if(is_array($id_checked)) {
			$id_checked=Database::escape_string(implode(',',$id_checked));
		} else {
			$id_checked=intval($id_checked);
		}

		if (!api_is_platform_admin() && !$from_ws) {
			$sql = 'SELECT session_admin_id FROM '.Database :: get_main_table(TABLE_MAIN_SESSION).' WHERE id='.$id_checked;
			$rs = Database::query($sql,__FILE__,__LINE__);
			if (Database::result($rs,0,0)!=$_user['user_id']) {
				api_not_allowed(true);
			}
		}
		Database::query("DELETE FROM $tbl_session WHERE id IN($id_checked)",__FILE__,__LINE__);
		Database::query("DELETE FROM $tbl_session_rel_course WHERE id_session IN($id_checked)",__FILE__,__LINE__);
		Database::query("DELETE FROM $tbl_session_rel_course_rel_user WHERE id_session IN($id_checked)",__FILE__,__LINE__);
		Database::query("DELETE FROM $tbl_session_rel_user WHERE id_session IN($id_checked)",__FILE__,__LINE__);
                
                Database::query("DELETE FROM $tbl_session_rel_category WHERE session_id IN($id_checked)",__FILE__,__LINE__);
                
                $tbl_session_rel_category = Database::get_main_table(TABLE_MAIN_SESSION_REL_CATEGORY);
                
                // delete from session relation with category
                $cat_exist = Database::query("SELECT * FROM $tbl_session_rel_category WHERE session_id IN($id_checked)");
                if (Database::num_rows($cat_exist)) {
                    Database::query("DELETE FROM $tbl_session_rel_category WHERE session_id IN($id_checked)",__FILE__,__LINE__);
                }

		// delete extra session fields
		$t_sf 		= Database::get_main_table(TABLE_MAIN_SESSION_FIELD);
		$t_sfv 		= Database::get_main_table(TABLE_MAIN_SESSION_FIELD_VALUES);

		// Delete extra fields from session where field variable is "SECCION"
		$sql = "SELECT t_sfv.field_id FROM $t_sfv t_sfv, $t_sf t_sf  WHERE t_sfv.session_id = '$id_checked' AND t_sf.field_variable = 'SECCION' ";
		$rs_field = Database::query($sql,__FILE__,__LINE__);

		$field_id = 0;
		if (Database::num_rows($rs_field) == 1) {
			$row_field = Database::fetch_row($rs_field);
			$field_id = $row_field[0];

			$sql_delete_sfv = "DELETE FROM $t_sfv WHERE session_id = '$id_checked' AND field_id = '$field_id'";
			$rs_delete_sfv = Database::query($sql_delete_sfv,__FILE__,__LINE__);
		}

		$sql = "SELECT * FROM $t_sfv WHERE field_id = '$field_id' ";
		$rs_field_id = Database::query($sql,__FILE__,__LINE__);

		if (Database::num_rows($rs_field_id) == 0) {
			$sql_delete_sf = "DELETE FROM $t_sf WHERE id = '$field_id'";
			$rs_delete_sf = Database::query($sql_delete_sf,__FILE__,__LINE__);
		}

		/*
		$sql = "SELECT distinct field_id FROM $t_sfv  WHERE session_id = '$id_checked'";
		$res_field_ids = @Database::query($sql,__FILE__,__LINE__);

		if (Database::num_rows($res_field_ids) > 0) {
			while($row_field_id = Database::fetch_row($res_field_ids)){
				$field_ids[] = $row_field_id[0];
			}
		}

		//delete from table_session_field_value from a given session id

		$sql_session_field_value = "DELETE FROM $t_sfv WHERE session_id = '$id_checked'";
		@Database::query($sql_session_field_value,__FILE__,__LINE__);

		$sql = "SELECT distinct field_id FROM $t_sfv";
		$res_field_all_ids = @Database::query($sql,__FILE__,__LINE__);

		if (Database::num_rows($res_field_all_ids) > 0) {
			while($row_field_all_id = Database::fetch_row($res_field_all_ids)){
				$field_all_ids[] = $row_field_all_id[0];
			}
		}

		if (count($field_ids) > 0 && count($field_all_ids) > 0) {
			foreach($field_ids as $field_id) {
				// check if field id is used into table field value
				if (in_array($field_id,$field_all_ids)) {
					continue;
				} else {
					$sql_session_field = "DELETE FROM $t_sf WHERE id = '$field_id'";
					Database::query($sql_session_field,__FILE__,__LINE__);
				}
			}
		}
		*/
		// add event to system log
		$time = time();
		$user_id = api_get_user_id();
		event_system(LOG_SESSION_DELETE, LOG_SESSION_ID, $id_checked, $time, $user_id);

	}


	 /**
	  * Subscribes users to the given session and optionally (default) unsubscribes previous users
	  * @author Carlos Vargas <carlos.vargas@dokeos.com>,from existing code
	  * @param	integer		Session ID
	  * @param	array		List of user IDs
	  * @param	bool		Whether to unsubscribe existing users (true, default) or not (false)
	  * @return	void		Nothing, or false on error
	  **/
	public static function suscribe_users_to_session ($id_session, $user_list, $visibility=SESSION_VISIBLE_READ_ONLY, $empty_users=true, $send_email=false, $check_priority_list = array(), $course_list = null) {                
                 global $_course, $_user, $language_interface, $_configuration;              
                
	   	$tbl_session_rel_course			= Database::get_main_table(TABLE_MAIN_SESSION_COURSE);
		$tbl_session_rel_course_rel_user	= Database::get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
	   	$tbl_session_rel_user 			= Database::get_main_table(TABLE_MAIN_SESSION_USER);
	   	$tbl_session				= Database::get_main_table(TABLE_MAIN_SESSION);
		
		$session_info 	= api_get_session_info($id_session);
		$session_name 	= $session_info['name'];

		//from function parameter
		$session_visibility = $visibility;		
	   	if (empty($session_visibility)) {
	   		$session_visibility     = $session_info['name']; 
	   		$session_visivility	= $session_info['visibility']; //loaded from DB
	   		//default status loaded if empty
			if (empty($session_visivility))
				$session_visibility = SESSION_VISIBLE_READ_ONLY; // by default readonly 1
	   	}
		$session_info = api_get_session_info($id_session);
		$session_name = $session_info['name'];

	   	$sql = "SELECT id_user FROM $tbl_session_rel_user WHERE id_session='$id_session'";               
		$result = Database::query($sql,__FILE__,__LINE__);
		$existingUsers = array();
		while($row = Database::fetch_array($result)){
			$existingUsers[] = $row['id_user'];
		}
                           
                if (!isset($course_list)) {                
                    $sql = "SELECT course_code FROM $tbl_session_rel_course WHERE id_session='$id_session'";
                    $result=Database::query($sql,__FILE__,__LINE__);
                    $course_list=array();
                    while($row=Database::fetch_array($result)) {
                            $course_list[]=$row['course_code'];
                    }		
                }
		
                // send email notification to registered user
                if (api_get_setting('email_alert_to_user_subscribe_in_session') == 'true' || $send_email) {
                    if (!empty($user_list)) {
                        foreach($user_list as $enreg_user) {				
                            if (!in_array($enreg_user, $existingUsers )) {
                                $sent = self::send_email_notification_to_user_reg_session($enreg_user, $id_session);
                            }
                        }
                    }                    
                }
         
		foreach ($course_list as $enreg_course) {
                    // for each course in the session
                    $nbr_users=0;
                    $enreg_course = Database::escape_string($enreg_course);                    
                    // delete existing users
                    if ($empty_users!==false) {
                        foreach ($existingUsers as $existing_user) {
                            if(!in_array($existing_user, $user_list)) {
                                    $sql = "DELETE FROM $tbl_session_rel_course_rel_user WHERE id_session='$id_session' AND course_code='$enreg_course' AND id_user='$existing_user' AND status != 2 ";
                                    Database::query($sql,__FILE__,__LINE__);
                                    if(Database::affected_rows()) {
                                            $nbr_users--;
                                    }
                            }
                        }
                    }                    
                    // insert new users into session_rel_course_rel_user and ignore if they already exist
                    foreach ($user_list as $enreg_user) {
                          if(!in_array($enreg_user, $existingUsers)) {					
                                  $enreg_user = Database::escape_string($enreg_user);
                                  $insert_sql = "INSERT IGNORE INTO $tbl_session_rel_course_rel_user(id_session,course_code,id_user,visibility) VALUES('$id_session','$enreg_course','$enreg_user','$session_visivility')";
                                  Database::query($insert_sql,__FILE__,__LINE__);
                                  if(Database::affected_rows()) {
                                          $nbr_users++;
                                  }

                                   if (api_get_setting('automatic_group_filling') == 'true' ) {
                                          self::automatically_add_user_to_group($enreg_course,$enreg_user,$id_session);
                                   }
                          }
                      }
                    // count users in this session-course relation
                    $sql = "SELECT COUNT(id_user) as nbUsers FROM $tbl_session_rel_course_rel_user WHERE id_session='$id_session' AND course_code='$enreg_course'";
                    $rs = Database::query($sql, __FILE__, __LINE__);
                    list($nbr_users) = Database::fetch_array($rs);
                    // update the session-course relation to add the users total
                    $update_sql = "UPDATE $tbl_session_rel_course SET nbr_users=$nbr_users WHERE id_session='$id_session' AND course_code='$enreg_course'";
                    Database::query($update_sql,__FILE__,__LINE__);
		}
		// delete users from the session
		if ($empty_users===true){
			Database::query("DELETE FROM $tbl_session_rel_user WHERE id_session = $id_session",__FILE__,__LINE__);
		}
			// insert missing users into session
		$nbr_users = 0;
		foreach ($user_list as $enreg_user) {
	        $enreg_user = Database::escape_string($enreg_user);
			$nbr_users++;
			$insert_sql = "INSERT IGNORE INTO $tbl_session_rel_user(id_session, id_user, status) VALUES('$id_session','$enreg_user', 'true')";
			Database::query($insert_sql,__FILE__,__LINE__);
		}
		// update number of users in the session
		$nbr_users = count($user_list);
		$update_sql = "UPDATE $tbl_session SET nbr_users= $nbr_users WHERE id='$id_session' ";
		Database::query($update_sql,__FILE__,__LINE__);
	}
        
    /**
     * Insert Session shop course
     * @param   Array  Session id
     * @param   int  User id
     * @author Edgar Huamani Arango <ragdexd.rgd@gmail.com>
     */
    public static function register_shop_session_course($session_id,$userid){
        $tbl_main_payment_session_rel_user = Database::get_main_table(TABLE_MAIN_PAYMENT_SESSION_REL_USER);
        $tbl_main_ecommerce_items = Database::get_main_table(TABLE_MAIN_ECOMMERCE_ITEMS);
        $tbl_session = Database::get_main_table(TABLE_MAIN_SESSION);
        if(!empty($session_id)){
            foreach ($session_id as $sessionId){
                $query = "SELECT date_end FROM ". $tbl_main_payment_session_rel_user ." WHERE user_id=". $userid ." AND id_session='".$sessionId."'";
                $resulPayment = Database::query($query);
                $course_payment = Database::fetch_array($resulPayment);
                $date_end_payment = trim($course_payment['date_end']);
                if($date_end_payment == ''){
                    $date_end_payment = '0000-00-00 00:00:00';
                }
                //date_default_timezone_get();
                $date_today = date("Y-m-d H:i:s");
                $query = "SELECT e.duration,e.duration_type FROM ". $tbl_main_ecommerce_items ." e INNER JOIN ". $tbl_session ." s on s.id=e.id_session WHERE id_session=".$sessionId."";
                $resultEcommerce = Database::query($query);
                
                while($row = Database::fetch_array($resultEcommerce)){
                    $resultDate = Database::query("SELECT DATE_ADD('" . $date_today . "',INTERVAL " . $row['duration'] . " " . strtoupper($row['duration_type']) . ") AS date");
                    $row_date_end = Database::fetch_array($resultDate);
                    $date_end = trim($row_date_end['date']);
                    //print_r($row_date_end['date']);
                    //exit();
                    if($date_end > $date_end_payment && $date_end_payment != '0000-00-00 00:00:00'){
                        $query = "UPDATE ". $tbl_main_payment_session_rel_user ." SET date_start='".$date_today."',date_end='". $date_end ."' WHERE user_id=". $userid ." AND id_session=". $sessionId ."";
                    } else {
                        $query = "INSERT INTO ". $tbl_main_payment_session_rel_user ." (user_id,id_session,date_start,date_end)
                                                    VALUES(". $userid .",". $sessionId .",'".$date_today."','". $date_end ."')";
                    }
                    
                    Database::query($query,__FILE__,__LINE__);
                }
            }
        }
    }
     /**
      * Send email to user registered in a session
      * @param  int     User id
      * @param  int     Session id
      * @return bool    Sent ?
      */
      public static function send_email_notification_to_user_reg_session($user_id, $session_id) {
        global $_configuration,$_user,$language_interface;
        $session_info 	= api_get_session_info($session_id);
        $session_name 	= $session_info['name'];
        $student = api_get_user_info($user_id);
        $emailto = $student['mail'];                        
        $emailsubject	 = get_lang('YouhaveAddedTheSession').': '.$session_name;
        $table_emailtemplate = Database::get_main_table(TABLE_MAIN_EMAILTEMPLATES);	

		if ($_configuration['multiple_access_urls'] == true) {
			$access_url_id = api_get_current_access_url_id();
		}
		else {
			$access_url_id = 1;
		}

        $result  = Database::query("SELECT content FROM $table_emailtemplate WHERE description = 'MessageToUserInUserRegistrationToSession' AND language= '".$language_interface."' AND access_url = ".$access_url_id);
        $message = '';
        if (Database::num_rows($result) == 0) {                            
            if ($_configuration['multiple_access_urls']==true) {
                $access_url_id = api_get_current_access_url_id();
                if ($access_url_id != -1 ){
                    $url = api_get_access_url($access_url_id);				            	
                    $message = get_lang('Dear')." ".stripslashes(api_get_person_name($student['firstname'], $student['lastname'])).",\n\n".get_lang('YouAreRegisterToSession')." : ". $session_name  ." \n\n" .get_lang('Address') ." ". get_setting('siteName') ." ". get_lang('Is') ." : ". $url['url'] ."\n\n". get_lang('Problem'). "\n\n". get_lang('Formula').",\n\n".get_setting('administratorName')." ".get_setting('administratorSurname')."\n". get_lang('Manager'). " ".get_setting('siteName')."\nT. ".get_setting('administratorTelephone')."\n" .get_lang('Email') ." : ".get_setting('emailAdministrator');			            	
                }
            } else {
                $message = get_lang('Dear')." ".stripslashes(api_get_person_name($student['firstname'], $student['lastname'])).",\n\n".get_lang('YouAreRegisterToSession')." : ". $session_name ." \n\n" .get_lang('Address') ." ". get_setting('siteName') ." ". get_lang('Is') ." : ". $_configuration['root_web'] ."\n\n". get_lang('Problem'). "\n\n". get_lang('Formula').",\n\n".get_setting('administratorName')." ".get_setting('administratorSurname')."\n". get_lang('Manager'). " ".get_setting('siteName')."\nT. ".get_setting('administratorTelephone')."\n" .get_lang('Email') ." : ".get_setting('emailAdministrator');
            }
        }
        else {
            $domain_server = api_get_path(WEB_SERVER_ROOT_PATH) . "main/default_course_document";
            $row = Database::fetch_array($result);
            $message = $row['content'];
            $message = str_replace("/main/default_course_document", "tmp_file", $message);
            $message = str_replace('{userFirstNameUserLastName}',($student['firstname'].' '. $student['lastname']),$message);
            $message = str_replace('{nameSession}',$session_name, $message); 
            $message = str_replace('{userName}',$student['username'], $message);                        
            $message = str_replace('{userFirstName}',$student['firstname'], $message); 
            $message = str_replace('{userLastName}',$student['lastname'], $message); 
            $message = str_replace('{userEmail}',$student['mail'], $message);
            $message = str_replace("tmp_file", $domain_server, $message);
        }
        //$message = nl2br($message);
        $recipient_name = api_get_person_name($student['firstname'], $student['lastname'], null, PERSON_NAME_EMAIL_ADDRESS);
        $sender_name = api_get_person_name($_user['firstname'], $_user['lastname'], null, PERSON_NAME_EMAIL_ADDRESS);
        $email_admin = $_user['email'];
        return api_mail_html($recipient_name, $emailto, $emailsubject, $message, $sender_name, $email_admin);          
      }
        
    /** Subscribes courses to the given session and optionally (default) unsubscribes previous users
     * @author Carlos Vargas <carlos.vargas@dokeos.com>,from existing code
     * @param	int		Session ID
     * @param	array	List of courses IDs
     * @param	bool	Whether to unsubscribe existing users (true, default) or not (false)
     * @return	void	Nothing, or false on error
     **/
     public static function add_courses_to_session ($id_session, $course_list, $empty_courses=true) {
     	// security checks
     	if ($id_session!= strval(intval($id_session))) { return false; }
	   	foreach($course_list as $intCourse){
	   		if ($intCourse!= strval(intval($intCourse))) { return false; }
	   	}
	   	// initialisation
		$tbl_session_rel_course_rel_user	= Database::get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
		$tbl_session						= Database::get_main_table(TABLE_MAIN_SESSION);
		$tbl_session_rel_user				= Database::get_main_table(TABLE_MAIN_SESSION_USER);
		$tbl_session_rel_course				= Database::get_main_table(TABLE_MAIN_SESSION_COURSE);
		$tbl_course							= Database::get_main_table(TABLE_MAIN_COURSE);
     	// get general coach ID
		$id_coach = Database::query("SELECT id_coach FROM $tbl_session WHERE id=$id_session");
		$id_coach = Database::fetch_array($id_coach);
		$id_coach = $id_coach[0];
		// get list of courses subscribed to this session
		$rs = Database::query("SELECT course_code FROM $tbl_session_rel_course WHERE id_session=$id_session");
		$existingCourses = Database::store_result($rs);
		$nbr_courses=count($existingCourses);
		// get list of users subscribed to this session
		$sql="SELECT id_user
			FROM $tbl_session_rel_user
			WHERE id_session = $id_session";
		$result=Database::query($sql,__FILE__,__LINE__);
		$user_list=Database::store_result($result);

		// remove existing courses from the session
		if ($empty_courses===true) {
			foreach ($existingCourses as $existingCourse) {
				if (!in_array($existingCourse['course_code'], $course_list)){
					Database::query("DELETE FROM $tbl_session_rel_course WHERE course_code='".$existingCourse['course_code']."' AND id_session=$id_session");
					Database::query("DELETE FROM $tbl_session_rel_course_rel_user WHERE course_code='".$existingCourse['course_code']."' AND id_session=$id_session");

				}
			}
			$nbr_courses=0;
		}

		// Pass through the courses list we want to add to the session
		foreach ($course_list as $enreg_course) {
			$enreg_course = Database::escape_string($enreg_course);
			$exists = false;
			// check if the course we want to add is already subscribed
			foreach ($existingCourses as $existingCourse) {
				if ($enreg_course == $existingCourse['course_code']) {
					$exists=true;
				}
			}
			if (!$exists) {
				//if the course isn't subscribed yet
				$sql_insert_rel_course= "INSERT INTO $tbl_session_rel_course (id_session,course_code) VALUES ('$id_session','$enreg_course')";
				Database::query($sql_insert_rel_course ,__FILE__,__LINE__);
				//We add the current course in the existing courses array, to avoid adding another time the current course
				$existingCourses[]=array('course_code'=>$enreg_course);
				$nbr_courses++;

				// subscribe all the users from the session to this course inside the session
				$nbr_users=0;
				foreach ($user_list as $enreg_user) {
					$enreg_user_id = Database::escape_string($enreg_user['id_user']);
					$sql_insert = "INSERT IGNORE INTO $tbl_session_rel_course_rel_user (id_session,course_code,id_user) VALUES ('$id_session','$enreg_course','$enreg_user_id')";
					Database::query($sql_insert,__FILE__,__LINE__);
					if (Database::affected_rows()) {
						$nbr_users++;
					}
				}
				Database::query("UPDATE $tbl_session_rel_course SET nbr_users=$nbr_users WHERE id_session='$id_session' AND course_code='$enreg_course'",__FILE__,__LINE__);
			}
		}
		Database::query("UPDATE $tbl_session SET nbr_courses=$nbr_courses WHERE id='$id_session'",__FILE__,__LINE__);
     }

  /**
  * Creates a new extra field for a given session
  * @param	string	Field's internal variable name
  * @param	int		Field's type
  * @param	string	Field's language var name
  * @return int     new extra field id
  */
	public static function create_session_extra_field ($fieldvarname, $fieldtype, $fieldtitle) {
		// database table definition
		$t_sf 			= Database::get_main_table(TABLE_MAIN_SESSION_FIELD);
		$fieldvarname 	= Database::escape_string($fieldvarname);
		$fieldtitle 	= Database::escape_string($fieldtitle);
		$fieldtype = (int)$fieldtype;
		$time = time();
		$sql_field = "SELECT id FROM $t_sf WHERE field_variable = '$fieldvarname'";
		$res_field = Database::query($sql_field,__FILE__,__LINE__);

		$r_field = Database::fetch_row($res_field);

		if (Database::num_rows($res_field)>0) {
			$field_id = $r_field[0];
		} else {
			// save new fieldlabel into course_field table
			$sql = "SELECT MAX(field_order) FROM $t_sf";
			$res = Database::query($sql,__FILE__,__LINE__);

			$order = 0;
			if (Database::num_rows($res)>0) {
				$row = Database::fetch_row($res);
				$order = $row[0]+1;
			}

			$sql = "INSERT INTO $t_sf
						                SET field_type = '$fieldtype',
						                field_variable = '$fieldvarname',
						                field_display_text = '$fieldtitle',
						                field_order = '$order',
						                tms = FROM_UNIXTIME($time)";
			$result = Database::query($sql,__FILE__,__LINE__);

			$field_id=Database::insert_id();
		}
		return $field_id;
	}

/**
 * Update an extra field value for a given session
 * @param	integer	Course ID
 * @param	string	Field variable name
 * @param	string	Field value
 * @return	boolean	true if field updated, false otherwise
 */
	public static function update_session_extra_field_value ($session_id,$fname,$fvalue='') {

		$t_sf 			= Database::get_main_table(TABLE_MAIN_SESSION_FIELD);
		$t_sfv 			= Database::get_main_table(TABLE_MAIN_SESSION_FIELD_VALUES);
		$fname = Database::escape_string($fname);
		$session_id = (int)$session_id;
		$fvalues = '';
		if(is_array($fvalue))
		{
			foreach($fvalue as $val)
			{
				$fvalues .= Database::escape_string($val).';';
			}
			if(!empty($fvalues))
			{
				$fvalues = substr($fvalues,0,-1);
			}
		}
		else
		{
			$fvalues = Database::escape_string($fvalue);
		}

		$sqlsf = "SELECT * FROM $t_sf WHERE field_variable='$fname'";
		$ressf = Database::query($sqlsf,__FILE__,__LINE__);
		if(Database::num_rows($ressf)==1)
		{ //ok, the field exists
			//	Check if enumerated field, if the option is available
			$rowsf = Database::fetch_array($ressf);

			$tms = time();
			$sqlsfv = "SELECT * FROM $t_sfv WHERE session_id = '$session_id' AND field_id = '".$rowsf['id']."' ORDER BY id";
			$ressfv = Database::query($sqlsfv,__FILE__,__LINE__);
			$n = Database::num_rows($ressfv);
			if ($n>1) {
				//problem, we already have to values for this field and user combination - keep last one
				while($rowsfv = Database::fetch_array($ressfv))
				{
					if($n > 1)
					{
						$sqld = "DELETE FROM $t_sfv WHERE id = ".$rowsfv['id'];
						$resd = Database::query($sqld,__FILE__,__LINE__);
						$n--;
					}
					$rowsfv = Database::fetch_array($ressfv);
					if($rowsfv['field_value'] != $fvalues)
					{
						$sqlu = "UPDATE $t_sfv SET field_value = '$fvalues', tms = FROM_UNIXTIME($tms) WHERE id = ".$rowsfv['id'];
						$resu = Database::query($sqlu,__FILE__,__LINE__);
						return($resu?true:false);
					}
					return true;
				}
			} else if ($n==1) {
				//we need to update the current record
				$rowsfv = Database::fetch_array($ressfv);
				if($rowsfv['field_value'] != $fvalues)
				{
					$sqlu = "UPDATE $t_sfv SET field_value = '$fvalues', tms = FROM_UNIXTIME($tms) WHERE id = ".$rowsfv['id'];
					//error_log('UM::update_extra_field_value: '.$sqlu);
					$resu = Database::query($sqlu,__FILE__,__LINE__);
					return($resu?true:false);
				}
				return true;
			} else {
				$sqli = "INSERT INTO $t_sfv (session_id,field_id,field_value,tms) " .
					"VALUES ('$session_id',".$rowsf['id'].",'$fvalues',FROM_UNIXTIME($tms))";
				//error_log('UM::update_extra_field_value: '.$sqli);
				$resi = Database::query($sqli,__FILE__,__LINE__);
				return($resi?true:false);
			}
		} else {
			return false; //field not found
		}
	}

	/**
	* Checks the relationship between a session and a course.
	* @param int $session_id
	* @param int $course_id
	* @return bool				Returns TRUE if the session and the course are related, FALSE otherwise.
	* */
	public static function relation_session_course_exist ($session_id, $course_id) {
		$tbl_session_course	= Database::get_main_table(TABLE_MAIN_SESSION_COURSE);
		$return_value = false;
		$sql= "SELECT course_code FROM $tbl_session_course WHERE id_session = ".Database::escape_string($session_id)." AND course_code = '".Database::escape_string($course_id)."'";
		$result = Database::query($sql,  __FILE__, __LINE__);
		$num = Database::num_rows($result);
		if ($num>0) {
			$return_value = true;
		}
		return $return_value;
	}

	/**
	* Get the session information by name
	* @param string session name
	* @return mixed false if the session does not exist, array if the session exist
	* */
	public static function get_session_by_name ($session_name) {
		$tbl_session = Database::get_main_table(TABLE_MAIN_SESSION);
		$sql = 'SELECT id, id_coach, date_start, date_end FROM '.$tbl_session.' WHERE name="'.Database::escape_string($session_name).'"';
		$result = Database::query($sql,  __FILE__, __LINE__);
		$num = Database::num_rows($result);
		if ($num>0){
			return Database::fetch_array($result);
		} else {
			return false;
		}
	}

	/**
	  * Create a session category
	  * @author Jhon Hinojosa <jhon.hinojosa@dokeos.com>, from existing code
	  * @param	string 		name
	  * @param 	integer		year_start
	  * @param 	integer		month_start
	  * @param 	integer		day_start
	  * @param 	integer		year_end
	  * @param 	integer		month_end
	  * @param 	integer		day_end
	  * @return $id_session;
	  **/
	public static function create_category_session ($sname, $tutors_id, $start_date, $end_date) {
            $tbl_session_category = Database::get_main_table(TABLE_MAIN_SESSION_CATEGORY);
            Database::query("INSERT INTO $tbl_session_category SET 
                                name = '".Database::escape_string($sname)."',
                                date_start = '".$start_date."',
                                date_end = '".$end_date."'
                            ");
            $id_session_category = Database::insert_id();

            // We add the tutors
            $tbl_session_category_rel_tutor = Database::get_main_table(TABLE_MAIN_SESSION_CATEGORY_REL_TUTOR);            
            if (!empty($tutors_id)) {
                foreach ($tutors_id as $tutor_id) {
                    Database::query("INSERT INTO $tbl_session_category_rel_tutor VALUES($id_session_category, $tutor_id)");
                }
            }

                // add event to system log
                $time = time();
                $user_id = api_get_user_id();
            event_system(LOG_SESSION_CATEGORY_CREATE, LOG_SESSION_CATEGORY_ID, $id_session_category, $time, $user_id);
            return $id_session_category;            
            }

	/**
	 * Edit a sessions categories
	 * @author Jhon Hinojosa <jhon.hinojosa@dokeos.com>,from existing code
	 * @param	integer		id
	 * @param	string 		name
	 * @param 	integer		year_start
	 * @param 	integer		month_start
	 * @param 	integer		day_start
	 * @param 	integer		year_end
	 * @param 	integer		month_end
	 * @param 	integer		day_end
	 * @return $id;
	 * The parameter id is a primary key
	**/
	public static function edit_category_session($id, $sname, $tutors_id, $start_date, $end_date){
		$tbl_session_category = Database::get_main_table(TABLE_MAIN_SESSION_CATEGORY);
			$sql = "UPDATE $tbl_session_category SET 
                     name = '".Database::escape_string($sname)."', 
                     date_start = '$start_date', 
                     date_end = '$end_date'
                                WHERE id= '".$id."' ";
            $result = Database::query($sql);
            
            // update tutors
            $tbl_session_category_rel_tutor = Database::get_main_table(TABLE_MAIN_SESSION_CATEGORY_REL_TUTOR);
            Database::query("DELETE FROM $tbl_session_category_rel_tutor WHERE session_category_id = $id");
            if (!empty($tutors_id)) {
                foreach ($tutors_id as $tutor_id) {
                    Database::query("INSERT INTO $tbl_session_category_rel_tutor VALUES($id, $tutor_id)");
		}
	}
            return $id;
	}

	/**
	 * Delete sessions categories
	 * @author Jhon Hinojosa <jhon.hinojosa@dokeos.com>, from existing code
	 * @param	array	id_checked
	 * @param	bool	include delete session
	 * @param	bool	optional, true if the function is called by a webservice, false otherwise.
     * @return	void	Nothing, or false on error
	 * The parameters is a array to delete sessions
	 **/
	public static function delete_session_category($id_checked, $delete_session = false,$from_ws = false){
		$tbl_session_category = Database::get_main_table(TABLE_MAIN_SESSION_CATEGORY);
		$tbl_session = Database::get_main_table(TABLE_MAIN_SESSION);
		if(is_array($id_checked)) {
			$id_checked=Database::escape_string(implode(',',$id_checked));
		} else {
			$id_checked=intval($id_checked);
		}                
		$sql = "SELECT id FROM $tbl_session WHERE session_category_id IN (".$id_checked.")";               
		$result = @Database::query($sql,__FILE__,__LINE__);
		while ($rows = Database::fetch_array($result)) {
			$session_id = $rows['id'];
                        $sql1 = "UPDATE $tbl_session SET session_category_id = 0 WHERE id =$session_id ";
                       $result1 = @Database::query($sql1,__FILE__,__LINE__);
			if($delete_session == true){
				if ($from_ws) {
					SessionManager::delete_session($session_id,true);
				} else {
					SessionManager::delete_session($session_id);
				}
			}
		}
		$sql = "DELETE FROM $tbl_session_category WHERE id IN (".$id_checked.")";
		$rs = @Database::query($sql,__FILE__,__LINE__);
		$result = Database::affected_rows();

                // We remove the tutors
                $tbl_session_category_rel_tutor = Database::get_main_table(TABLE_MAIN_SESSION_CATEGORY_REL_TUTOR);
                Database::query("DELETE FROM $tbl_session_category_rel_tutor WHERE session_category_id = $id_checked");
            
		// add event to system log
		$time = time();
		$user_id = api_get_user_id();
		event_system(LOG_SESSION_CATEGORY_DELETE, LOG_SESSION_CATEGORY_ID, $id_checked, $time, $user_id);


		// delete extra session fields where field variable is "PERIODO"
		$t_sf 		= Database::get_main_table(TABLE_MAIN_SESSION_FIELD);
		$t_sfv 		= Database::get_main_table(TABLE_MAIN_SESSION_FIELD_VALUES);

		$sql = "SELECT t_sfv.field_id FROM $t_sfv t_sfv, $t_sf t_sf  WHERE t_sfv.session_id = '$id_checked' AND t_sf.field_variable = 'PERIODO' ";
		$rs_field = Database::query($sql,__FILE__,__LINE__);

		$field_id = 0;
		if (Database::num_rows($rs_field) > 0) {
			$row_field = Database::fetch_row($rs_field);
			$field_id = $row_field[0];
			$sql_delete_sfv = "DELETE FROM $t_sfv WHERE session_id = '$id_checked' AND field_id = '$field_id'";
			$rs_delete_sfv = Database::query($sql_delete_sfv,__FILE__,__LINE__);
		}

		$sql = "SELECT * FROM $t_sfv WHERE field_id = '$field_id' ";
		$rs_field_id = Database::query($sql,__FILE__,__LINE__);

		if (Database::num_rows($rs_field_id) == 0) {
			$sql_delete_sf = "DELETE FROM $t_sf WHERE id = '$field_id'";
			$rs_delete_sf = Database::query($sql_delete_sf,__FILE__,__LINE__);
		}

		return true;
	}

        /**
         * 
         */
        public static function get_sessions_rel_category($category_id) {
            $tbl_sess_rel_cat = Database::get_main_table(TABLE_MAIN_SESSION_REL_CATEGORY);
            $sessions = array();
            $rs = Database::query("SELECT session_id FROM $tbl_sess_rel_cat WHERE category_id = $category_id");
            if (Database::num_rows($rs) > 0) {
                while ($row = Database::fetch_object($rs)) {
                    $sessions[] = api_get_session_info($row->session_id);
                }
            }
            return $sessions;
        }
        
	/**
     * Get a list of sessions of which the given conditions match with an = 'cond'
	 * @param array $conditions a list of condition (exemple : status=>STUDENT)
	 * @param array $order_by a list of fields on which sort
	 * @return array An array with all sessions of the platform.
	 * @todo optional course code parameter, optional sorting parameters...
	*/
	public static function get_sessions_list ($conditions = array(), $order_by = array()) {

		$session_table =Database::get_main_table(TABLE_MAIN_SESSION);
		$session_category_table = Database::get_main_table(TABLE_MAIN_SESSION_CATEGORY);
		$user_table = Database::get_main_table(TABLE_MAIN_USER);

		$return_array = array();

		$sql_query = " SELECT s.id, s.name, s.nbr_courses,s.nbr_users,s.max_seats, s.cost, s.date_start, s.date_end, u.firstname, u.lastname , sc.name as category_name
				FROM $session_table s
				INNER JOIN $user_table u ON s.id_coach = u.user_id
				LEFT JOIN  $session_category_table sc ON s.session_category_id = sc.id ";

		if (count($conditions)>0) {
			$sql_query .= ' WHERE ';
			foreach ($conditions as $field=>$value) {
                $field = Database::escape_string($field);
                $value = Database::escape_string($value);
				$sql_query .= $field.' = '.$value;
			}
		}
		if (count($order_by)>0) {
			$sql_query .= ' ORDER BY '.Database::escape_string(implode(',',$order_by));
		}

		$sql_result = Database::query($sql_query,__FILE__,__LINE__);
		while ($result = Database::fetch_array($sql_result)) {
			$return_array[] = $result;
		}
		return $return_array;
	}
	/**
	 * Get the session category information by id
	 * @param string session category ID
	 * @return mixed false if the session category does not exist, array if the session category exists
	 */
	public static function get_session_category ($id) {
		$id = intval($id);
		$tbl_session_category = Database::get_main_table(TABLE_MAIN_SESSION_CATEGORY);
		$sql = 'SELECT * FROM '.$tbl_session_category.' WHERE id="'.$id.'"';
		$result = Database::query($sql,  __FILE__, __LINE__);
		$num = Database::num_rows($result);
		if ($num>0){
			return Database::fetch_array($result);
		} else {
			return false;
		}
	}

         /**
          * Gets the training sessions of an user
          * @param integer $user_id
          * @return array
          */
         public static function get_training_sessions_of_an_user_by_user_id ($user_id) {
                         $tbl_session = Database::get_main_table(TABLE_MAIN_SESSION);
                         $tbl_session_rel_user = Database::get_main_table(TABLE_MAIN_SESSION_USER);

           $sql = "SELECT s.id,s.name,s.date_start,s.date_end FROM $tbl_session s
            INNER JOIN $tbl_session_rel_user sru ON s.id = sru.id_session WHERE sru.id_user='".Database::escape_string($user_id)."'";
           $rs = Database::query($sql, __FILE__, __LINE__);
           $row = Database::fetch_array($rs, 'ASSOC');

           return $row;
         }
         
	/**
	 * Assign a coach to course in session with status = 2
	 * @param int  		- user id
	 * @param int  		- session id
	 * @param string  	- course code
	 * @param bool  	- optional, if is true the user don't be a coach now, otherwise it'll assign a coach
	 * @return bool true if there are affected rows, otherwise false
	 */
	function set_coach_to_course_session($user_id, $session_id = 0, $course_code = '',$nocoach = false) {

		// Definition of variables
		$user_id = intval($user_id);

		if (!empty($session_id)) {
			$session_id = intval($session_id);
		} else {
			$session_id = api_get_session_id();
		}

		if (!empty($course_code)) {
			$course_code = Database::escape_string($course_code);
		} else {
			$course_code = api_get_course_id();
		}

		// definitios of tables
		$tbl_session_rel_course_rel_user = Database::get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
		$tbl_session_rel_user = Database::get_main_table(TABLE_MAIN_SESSION_USER);
		$tbl_user	= Database::get_main_table(TABLE_MAIN_USER);

		// check if user is a teacher
		$sql= "SELECT * FROM $tbl_user WHERE status='1' AND user_id = '$user_id'";

		$rs_check_user = Database::query($sql,__FILE__,__LINE__);

		if (Database::num_rows($rs_check_user) > 0) {

			if ($nocoach) {
				// check if user_id exits int session_rel_user
				$sql = "SELECT id_user FROM $tbl_session_rel_user WHERE id_session = '$session_id' AND id_user = '$user_id'";
				$res = Database::query($sql,__FILE__,__LINE__);

				if (Database::num_rows($res) > 0) {
					// The user don't be a coach now
					$sql = "UPDATE $tbl_session_rel_course_rel_user SET status = 0 WHERE id_session = '$session_id' AND course_code = '$course_code' AND id_user = '$user_id' ";
					$rs_update = Database::query($sql,__FILE__,__LINE__);
					if (Database::affected_rows() > 0) return true;
					else return false;
				} else {
					// The user don't be a coach now
					$sql = "DELETE FROM $tbl_session_rel_course_rel_user WHERE id_session = '$session_id' AND course_code = '$course_code' AND id_user = '$user_id' ";
					$rs_delete = Database::query($sql,__FILE__,__LINE__);
					if (Database::affected_rows() > 0) return true;
					else return false;
				}

			} else {
				// Assign user like a coach to course
				// First check if the user is registered in the course
				$sql = "SELECT id_user FROM $tbl_session_rel_course_rel_user WHERE id_session = '$session_id' AND course_code = '$course_code' AND id_user = '$user_id'";
				$rs_check = Database::query($sql,__FILE__,__LINE__);

				//Then update or insert
				if (Database::num_rows($rs_check) > 0) {
					$sql = "UPDATE $tbl_session_rel_course_rel_user SET status = 2 WHERE id_session = '$session_id' AND course_code = '$course_code' AND id_user = '$user_id' ";
					$rs_update = Database::query($sql,__FILE__,__LINE__);
					if (Database::affected_rows() > 0) return true;
					else return false;
				} else {
					$sql = " INSERT INTO $tbl_session_rel_course_rel_user(id_session, course_code, id_user, status) VALUES('$session_id', '$course_code', '$user_id', 2)";
					$rs_insert = Database::query($sql,__FILE__,__LINE__);
					if (Database::affected_rows() > 0) return true;
					else return false;
				}
			}
		} else {
			return false;
		}
	}
	
	
	public static function is_course_in_session_coach($id_user, $course_code){
		
		//Check if session_coach
		$tbl_session_rel_course = Database::get_main_table(TABLE_MAIN_SESSION_COURSE);
		$tbl_session_course_user = Database::get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
		
		$sql = 'SELECT id_session FROM '.$tbl_session_rel_course.' WHERE course_code="'.$course_code.'"';
		$res = Database::query($sql,__FILE__,__LINE__);
		$sessions_id = array();
		if (Database::num_rows($res) > 0) {
			while ($result_row = Database::fetch_array($res)) {
				array_push($sessions_id, $result_row['id_session']);
			}
		}
		if(count($sessions_id)>0){
			foreach($sessions_id as $id_session){
				if(SessionManager::is_session_coach($id_user, $id_session)){
					return true;
				}
			}
		}
		
		//Check if the user is a training coach in a session for this course
		$sql = 'SELECT id_session FROM '.$tbl_session_course_user.' WHERE course_code="'.$course_code.'" AND id_user="'.$id_user.'" AND status="2"';
		$res = Database::query($sql,__FILE__,__LINE__);
		if (Database::num_rows($res) > 0) {
			return true;
		}
		
		return false;
		
	}
	
	
	public static function is_session_coach($user_id, $session_id){
		
		$tbl_session = Database::get_main_table(TABLE_MAIN_SESSION);
		$sql = 'SELECT id_coach FROM '.$tbl_session.' WHERE id="'.$session_id.'"';
		$res = Database::query($sql,__FILE__,__LINE__);
		if(mysql_result($res, 0, 'id_coach') == $user_id){
			return true;
		}
		return false;
		
	}
 
        public static function get_course_list_by_session_id ($session_id) {
            $tbl_session_rel_course = Database::get_main_table(TABLE_MAIN_SESSION_COURSE);
            $course_list = array();
            $sql = "SELECT course_code FROM $tbl_session_rel_course WHERE id_session='".intval($session_id)."'";
            $rs = Database::query($sql,__FILE__,__LINE__);
            while ($row = Database::fetch_array($rs)) {
                $course_list[] = $row['course_code'];
            }
            return $course_list;
        }
        
        function get_session_priority_record_by_course_code($session_id, $course_code, $session_priority_list) {
            $couse_list = array();
            $couse_list = self::get_course_list_by_session_id ($session_id);
            $return = false;
            foreach ($session_priority_list as $priority_info) {
                if ($priority_info[1] ==  $course_code) {
                    return false;
                }
            }
            if (in_array($course_code,$couse_list)) {
                $return = array($session_id,$course_code);
            }
            return $return;
        }
       
        /****************************************
         *  CATALOGUE SESSION FUNCTIONS
         ****************************************/        
        /**
         * Create empty catalogue sessions
         */
        public static function create_empty_catalogue_sessions($sessions, $courses_session, $session_category_id, $user_id, $session_category_id) {            
            // define the tables
            $tbl_session_user        = Database::get_main_table(TABLE_MAIN_SESSION_USER);
            $tbl_session_course_user = Database::get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
            $tbl_session_course      = Database::get_main_table(TABLE_MAIN_SESSION_COURSE);
            $tbl_session             = Database::get_main_table(TABLE_MAIN_SESSION);
            $tbl_session_cat_rel_user= Database::get_main_table(TABLE_MAIN_SESSION_CATEGORY_REL_USER);
            $tbl_session_rel_cat     = Database::get_main_table(TABLE_MAIN_SESSION_REL_CATEGORY);
       
            if (!empty($sessions)) {                
                foreach ($sessions as $key => $session_id) {
                    $max_seats = self::get_max_seats_session($session_id);
                    $nbr_users = self::get_nbr_users_in_session($session_id);
                    
                    // session is full, we create a new session
                    if ($max_seats <> -1 && ($nbr_users >= $max_seats)) {
                        $session_info = api_get_session_info($session_id);
                        // create the session
                        list($syear_start, $smonth_start, $sday_start) = explode('-', $session_info['date_start']);
                        list($syear_end, $smonth_end, $sday_end) = explode('-', $session_info['date_end']);                        
                        $session_name = $session_info['name'].'-new';                       
                        $last_session_id = self::create_session($session_name, $session_info['description'], $syear_start, $smonth_start, $sday_start, $syear_end, $smonth_end, $sday_end, 0, 1, '',-1);
                        
                        if ($last_session_id) {                            
                            // send email to administrators
                            self::send_email_about_new_session_to_administrators($last_session_id, $user_id, $session_category_id);
                            // update session name
                            $new_session_name = $session_info['name'].'-'.$last_session_id;
                            Database::query("UPDATE $tbl_session SET name = '$new_session_name' WHERE id = $last_session_id");
                            
                            // get the courses list
                            $courses = self::get_course_list_by_session_id($session_id);
                            // add courses to new session                        
                            if (!empty($courses)) {
                                foreach ($courses as $course) {
                                    $rs_check = Database::query("SELECT * FROM $tbl_session_course WHERE id_session = $last_session_id AND course_code = '$course'");
                                    if (Database::num_rows($rs_check) == 0) {
                                        Database::query("INSERT INTO $tbl_session_course SET id_session = $last_session_id, course_code = '$course'");
                                        if (Database::affected_rows()) {
                                            // update nbr_courses
                                            Database::query("UPDATE $tbl_session SET nbr_courses = nbr_courses + 1 WHERE id = $last_session_id");
                                        }
                                    }
                                }
                            }
                            // register session in category
                            $rs_sess_cat = Database::query("SELECT * FROM $tbl_session_rel_cat WHERE session_id = $session_id AND category_id = $session_category_id;");
                            if (Database::num_rows($rs_sess_cat)) {
                                while ($row_sess_cat = Database::fetch_array($rs_sess_cat)) {   
                                    // insert new session
                                    Database::query("INSERT INTO $tbl_session_rel_cat SET 
                                                        category_id = $session_category_id,
                                                        session_set = '".$row_sess_cat['session_set']."',
                                                        session_id  = $last_session_id,
                                                        session_range = '".$row_sess_cat['session_range']."'                                                
                                                    ");                                   
                                    // remove current empty session_set                                
                                    Database::query("DELETE FROM $tbl_session_rel_cat WHERE session_id = $session_id AND category_id = $session_category_id");
                                }
                            }  
                            // replace new session
                            if (isset($courses_session[$session_id])) {
                                $courses_session[$last_session_id] = $courses_session[$session_id];
                                unset($courses_session[$session_id]);
                            }                         
                        }                        
                    }                    
                }
            }                 
            return $courses_session;            
        }
        
        
        
        
        /**
         * Register user to selected courses for session category, it's used for catalogue registration
         */
        public static function register_user_to_selected_courses_session($cours_rel_sessions, $user_id, $session_list, $session_category_id, $payment_type = 'credit_card') {

            
            
            
            
            // define the tables
            $tbl_session_user        = Database::get_main_table(TABLE_MAIN_SESSION_USER);
            $tbl_session_course_user = Database::get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
            $tbl_session_course      = Database::get_main_table(TABLE_MAIN_SESSION_COURSE);
            $tbl_session             = Database::get_main_table(TABLE_MAIN_SESSION);
            $tbl_session_cat_rel_user= Database::get_main_table(TABLE_MAIN_SESSION_CATEGORY_REL_USER);
            $tbl_session_rel_cat     = Database::get_main_table(TABLE_MAIN_SESSION_REL_CATEGORY);
            
            // prepare courses in session
            $courses_session = array();
            if (!empty($cours_rel_sessions)) {
                foreach ($cours_rel_sessions as $cours_rel_session) {
                    list($course, $session) = explode('@@', $cours_rel_session);
                    $courses_session[$session][] = $course;
                }
            }                        
            $courses_session = self::create_empty_catalogue_sessions($session_list, $courses_session, $session_category_id, $user_id, $session_category_id);
            if (!empty($courses_session)) {
                foreach ($courses_session as $session => $courses) {
                    // register session rel course user
                    if (!empty($courses)) {
                        $affected_rows = 0;
                        foreach ($courses as $course_code) {                            
                            $check = Database::query("SELECT * FROM $tbl_session_course_user WHERE id_session = $session AND course_code = '$course_code' AND id_user = $user_id");
                            if (Database::num_rows($check) == 0) {
                                $sql = "INSERT INTO $tbl_session_course_user SET 
                                                    id_session = $session,
                                                    course_code = '$course_code',
                                                    id_user = $user_id
                                                ";
                                Database::query( $sql );
                                
                                $affected_rows = Database::affected_rows();
                                if ($affected_rows) {
                                    if (api_get_setting('automatic_group_filling') == 'true' && $payment_type != 'cheque_payment') {		
                                        self::automatically_add_user_to_group($course_code,$user_id);
                                    }
                                }
                            }                            
                            // register to category
                            if (!empty($session_category_id)) {
                                $verify_cat = Database::query("SELECT * FROM $tbl_session_cat_rel_user WHERE category_id = $session_category_id AND user_id = $user_id AND course_code='$course_code' AND session_id = $session");
                                if (Database::num_rows($verify_cat) == 0) {
                                    $sql = "INSERT INTO $tbl_session_cat_rel_user SET 
                                                        category_id = $session_category_id,
                                                        user_id     = $user_id,
                                                        course_code = '$course_code',
                                                        session_id  = $session
                                                    ";                                    
                                    Database::query( $sql );                                    
                                }
                            }
                        }
                        if ($affected_rows) {
                            // insert into session user
                            $check_sess_user = Database::query("SELECT * FROM $tbl_session_user WHERE id_session = $session AND id_user = $user_id");
                            if (Database::num_rows($check) == 0) {
                                $sql = "INSERT INTO $tbl_session_user SET 
                                                    id_session = $session,
                                                    id_user = $user_id
                                                ";                               
                                Database::query( $sql );                                
                                if (Database::affected_rows()) {
                                    // update numbers of students in table session
                                    $sql = "UPDATE $tbl_session SET nbr_users = nbr_users + 1 WHERE id = $session";                                    
                                    Database::query( $sql );                                    
                                }
                            }
                        }
                    }
                }
            }
        }

        
        
        
        /**
         * Add users to group automatically
         * @param   course_code   Data
         * @param   int     user id 
         */
         public static function automatically_add_user_to_group($course_code, $user_id,$id_session=0) {       
            
			global $_configuration;
            
            $course_info            = api_get_course_info($course_code);           
            $tbl_group              = Database::get_course_table(TABLE_GROUP, $course_info['dbName']);
            $table_group_user       = Database :: get_course_table(TABLE_GROUP_USER, $course_info['dbName']);
            $user_table             = Database :: get_main_table(TABLE_MAIN_USER);				
            $tbl_course_rel_user    = Database::get_main_table(TABLE_MAIN_COURSE_USER);				
            $emailtemplate_table    = Database :: get_main_table(TABLE_MAIN_EMAILTEMPLATES);
            $sql                    = "SELECT * FROM $tbl_group WHERE session_id=$id_session ORDER BY id";
            $rs                     = Database::query($sql,__FILE__,__LINE__);          
            while ($row = Database::fetch_array($rs)) {
                $group_id = $row['id'];                  
                $sql_user = "SELECT * FROM ".$table_group_user." WHERE group_id = ".$group_id;                
                $result = Database::query($sql_user,__FILE__,__LINE__);
                $num_users = Database::num_rows($result);
                if($num_users < $row['max_student']){                   
                    $sql_insert = "INSERT INTO ".$table_group_user." (user_id, group_id) VALUES ('".$user_id."', '".$group_id."')";                    
                    Database::query($sql_insert,__FILE__,__LINE__);
                    $affected_rows = Database::affected_rows();                     
                    //break;
                }
                else {
                    continue;
                }	
            }  

            if (!$affected_rows) {
                if (api_get_setting('create_new_group') == 'true') {
                    $sql = "SELECT * FROM $tbl_group WHERE name like '".get_lang('GroupName')."_%' ORDER BY id";
                    $rs = Database::query($sql,__FILE__,__LINE__);
                    if (Database::num_rows($rs) == 0) {
                        $group_no = 1;
                        $new_group_name = get_lang('GroupName').'_'.$group_no;
                    }
                    else {
                        while($row = Database::fetch_array($rs)){
                                $group_name = $row['name'];
                        }							
                        list($grp_name,$grp_id) = split('_',$group_name);
                        $new_grp_id = $grp_id + 1;
                        $new_group_name = get_lang('GroupName').'_'.$new_grp_id;
                    }
                    $check = Database::query("SELECT * FROM $tbl_group WHERE name = '".$new_group_name."'");
                    if (Database::num_rows($check) == 0) {
                        $new_group_seats = api_get_setting('new_group_seats');	
                        $sql_group = "INSERT INTO $tbl_group(name,category_id,max_student) VALUES('".$new_group_name."',1,".$new_group_seats.")";
                        Database::query($sql_group,__FILE__,__LINE__);
                        $new_groupid = Database::insert_id();	
                        $sql_insert = "INSERT INTO ".$table_group_user." (user_id, group_id) VALUES ('".$user_id."', '".$new_groupid."')";
                        Database::query($sql_insert,__FILE__,__LINE__);

						if ($_configuration['multiple_access_urls'] == true) {
							$access_url_id = api_get_current_access_url_id();
						}
						else {
							$access_url_id = 1;
						}

                        //Email
                        $sql = "SELECT content FROM $emailtemplate_table WHERE description = 'NewGroup' AND language='".api_get_interface_language()."' AND access_url = ".$access_url_id;
                        $res = Database::query($sql,__FILE__,__LINE__);
                        $num_rows = Database::num_rows($res);
                        if($num_rows == 0){
                        $sql = "SELECT content FROM $emailtemplate_table WHERE description = 'NewGroup' AND language = 'english' AND access_url = ".$access_url_id;
                        $res = Database::query($sql,__FILE__,__LINE__);
                        }
                        $row = Database::fetch_array($res);
                        $message = $row['content'];
                        
                        $domain_server = api_get_path(WEB_SERVER_ROOT_PATH) . "main/default_course_document";

                        $sender_name = 'Automatic Mail From Dokeos';
                        $subject = get_lang('NewGroupCreated');

                        $message = str_replace("/main/default_course_document", "tmp_file", $message);
                        $message = str_replace("{groupName}",$new_group_name,$message);
                        $message = str_replace("{maxStudent}",$new_group_seats,$message);
                        $message = str_replace("{courseName}",$course_info['name'],$message);
                        $message = str_replace("{authorName}",'DILA E-Learning',$message);
                        $message = str_replace("tmp_file", $domain_server, $message);

                        //$message = str_replace('<br />',"\n\n", $message);
                        $sql = "SELECT u.* FROM $tbl_user u INNER JOIN $tbl_admin a ON u.user_id = a.user_id";
                        $rs_users = Database::query($sql,__FILE__,__LINE__);
                        if (Database::num_rows($rs_users)) {
                            $admins = array();
                            while ($row_users = Database::fetch_array($rs_users)) {                    
                                $recipient_name = api_get_person_name($row_users['firstname'], $row_users['lastname'], null, PERSON_NAME_EMAIL_ADDRESS);
                                $email_admin = $row_users['email'];                    
                                $email_message =  str_replace('{adminName}', $recipient_name, $message); 
                                api_mail_html($recipient_name, $email_admin, $subject, $email_message, $sender_name, api_get_setting('emailAdministrator'));                 
                            }
                        } 
                    }
                }
            }			
         }
             
        /**
         * Send email to administrators about a created session
         */
        public static function send_email_about_new_session_to_administrators($session_id, $user_id, $cat_id) {
            global $language_interface, $_configuration;            
            
            $tbl_user = Database::get_main_table(TABLE_MAIN_USER);
            $tbl_admin = Database::get_main_table(TABLE_MAIN_ADMIN);
            
			if ($_configuration['multiple_access_urls'] == true) {
				$access_url_id = api_get_current_access_url_id();
			}
			else {
				$access_url_id = 1;
			}
            
            // Send automatically email at registration process     
            $user_info   = api_get_user_info($user_id);        
            // category info
            $category_info  = SessionManager::get_session_category($cat_id);                         
            $sessionList    = api_get_path(WEB_CODE_PATH).'admin/session_edit.php?page=session_list.php&id='.$session_id; 

            $emailsubject = '['.api_get_setting('siteName').'] '.get_lang('YourReg').' '.api_get_setting('siteName');            
            // template email
            $table_emailtemplate 	= Database::get_main_table(TABLE_MAIN_EMAILTEMPLATES);	
            $sql = "SELECT * FROM $table_emailtemplate WHERE description = 'UserRegistrationToSession' AND language= '".$language_interface."' AND access_url = ".$access_url_id;
            $result = api_sql_query($sql, __FILE__, __LINE__);
            $row = Database::fetch_array($result);				
            $content = !empty($row['content']) ? $row['content'] : '';

            $domain_server = api_get_path(WEB_SERVER_ROOT_PATH) . "main/default_course_document";
            $content = str_replace("/main/default_course_document", "tmp_file", $content);
            $content =  str_replace('{firstName}', stripslashes($user_info['firstname']), $content); 
            $content =  str_replace('{lastName}', stripslashes($user_info['lastname']), $content); 
            $content =  str_replace('{username}', $user_info['username'], $content); 
            $content =  str_replace('{Programme}', stripslashes($category_info['name']), $content);            
            $content =  str_replace('{siteName}', api_get_setting('siteName'), $content); 
            $content =  str_replace('{Institution}', api_get_setting('Institution'), $content); 
            $content =  str_replace('{administratorSurname}', api_get_person_name(api_get_setting('administratorName'), api_get_setting('administratorSurname')), $content); 
            $content =  str_replace('{sessionList}', $sessionList, $content);
            $content = str_replace("tmp_file", $domain_server, $content);
            
            $sql = "SELECT u.* FROM $tbl_user u INNER JOIN $tbl_admin a ON u.user_id = a.user_id";
            $rs_users = Database::query($sql,__FILE__,__LINE__);
            if (Database::num_rows($rs_users)) {
                $admins = array();
                while ($row_users = Database::fetch_array($rs_users)) {                    
                    $administratorname = api_get_person_name($row_users['firstname'], $row_users['lastname'], null, PERSON_NAME_EMAIL_ADDRESS);
                    $email_admin = $row_users['email'];                    
                    $content2 =  str_replace('{administratorname}', $administratorname, $content); 
                    api_mail_html($email_admin, $emailsubject, $content2);                    
                }
            }         

        }
                        
        /**
         * Save payment atos information
         * @param   array   Values in array ('user_id', 'sess_id', 'pay_type', 'pay_data')
         * @return  int     Affected rows
         */
        public static function save_payment_atos($params) {
            
            
            global $_configuration;
            require_once api_get_path(LIBRARY_PATH).'usermanager.lib.php';
            require_once api_get_path(LIBRARY_PATH).'export.lib.inc.php';            
            $tbl_fld     = Database::get_main_table(TABLE_MAIN_USER_FIELD);
            $tbl_fld_opt = Database::get_main_table(TABLE_MAIN_USER_FIELD_OPTIONS);            
            $tbl_payment_atos = Database::get_main_table(TABLE_MAIN_PAYMENT_ATOS); 
            $tbl_session_category = Database::get_main_table(TABLE_MAIN_SESSION_CATEGORY);
            $tbl_user = Database::get_main_table(TABLE_MAIN_USER);
                      
            switch ($params['pay_type']) {
                case 1: // online payment
                    $is_3install = FALSE;                                                
                    // it's first time, payment inmediate
                    $curr_cuota = 1;
                    $pay_data[$curr_cuota] = $params['pay_data'];
                    
                    $sql = "INSERT INTO $tbl_payment_atos SET
                                     user_id  = ".$params['user_id'].", 
                                     sess_id  = ".$params['sess_id'].", 
                                     pay_type = ".$params['pay_type'].",
                                     pay_data = '".serialize($pay_data)."', 
                                     $tbl_payment_atos.pay_time = ".time().",
                                     $tbl_payment_atos.status   = '".($params['pay_type']==1?1:0)."', 
                                     curr_quota = '".$curr_cuota."' ,  
                                    transaction_id = ' ".$params['transaction_id'] ."'";
                    
                    
                    Database::query(  $sql );
                                       
                    // register in harmony
                    $saved = self::save_payment_log(array('user_id'=>$params['user_id'], 'sess_id'=>$params['sess_id'], 'pay_type'=>$params['pay_type'], 'pay_data'=>$params['pay_data'], 'curr_quota'=> $curr_cuota));

                    // send email with account information
                    if (!api_get_user_id()) {			
                        if (Database::affected_rows()) {
                            // activate user and send email                        
                            $user_info = api_get_user_info($params['user_id']);

                            if (!empty($user_info)) {
                                $password   = api_generate_password();
                                $encripted  = api_get_encrypted_password($password);
                                Database::query('UPDATE user SET password = "'.$encripted.'", active = 1 WHERE user_id = "'.$params['user_id'].'" ');
                                if (Database::affected_rows()) {                                        
                                    $user_params = array(
                                        'firstname' => $user_info['firstname'],
                                        'lastname'  => $user_info['lastname'],
                                        'username'  => $user_info['username'],
                                        'password'  => $password,                                            
                                        'email'     => $user_info['mail'],
                                    ); 
                                    self::send_email_to_registered_user_with_cc_or_intallment($user_params, $params['sess_id']);
                                    return $params['user_id']; 
                                }
                            }
                        }
                    } else {
                        return Database::affected_rows();
                    }
                    break;
                case 2: // cheque payment                    
                    $result = 0;                                        
                    Database::query('INSERT INTO '.$tbl_payment_atos.' SET
                                     user_id  = '.$params['user_id'].',                                          
                                     sess_id  = '.$params['sess_id'].', 
                                     pay_type = '.$params['pay_type'].',
                                     pay_data = "'.$params['pay_data'].'", 
                                     pay_time = '.time().',
                                     status   = 0, 
                                     curr_quota = 0'
                            );
                    $result = Database::affected_rows();
                    return $result;
            }
        }
        
        /**
         * 
         */
        public static function send_email_to_registered_user_with_cc_or_intallment($user_params, $cat_id) {
            global $_configuration, $language_interface;            

			if ($_configuration['multiple_access_urls'] == true) {
				$access_url_id = api_get_current_access_url_id();
			}
			else {
				$access_url_id = 1;
			}

            $category_info  = SessionManager::get_session_category($cat_id);             
            $emailto = $user_params['email'];
            $emailsubject  = '['.api_get_setting('siteName').'] '.get_lang('YourReg').' '.api_get_setting('siteName');            
            // template email
            $table_emailtemplate 	= Database::get_main_table(TABLE_MAIN_EMAILTEMPLATES);	
            $sql = "SELECT * FROM $table_emailtemplate WHERE description = 'EmailsRegistrationInCaseCreditCardOrInstallment' AND language= '".$language_interface."' AND access_url = ".$access_url_id;
            $result = api_sql_query($sql, __FILE__, __LINE__);
            $row = Database::fetch_array($result);				
            $content = !empty($row['content']) ? $row['content'] : '';
            $domain_server = api_get_path(WEB_SERVER_ROOT_PATH) . "main/default_course_document";
            $content = str_replace("/main/default_course_document", "tmp_file", $content);
            $content =  str_replace('{firstName}', stripslashes($user_params['firstname']), $content);
            $content =  str_replace('{lastName}', stripslashes($user_params['lastname']), $content);
            $content =  str_replace('{username}', stripslashes($user_params['username']), $content);
            $content =  str_replace('{password}', stripslashes($user_params['password']), $content);
            $content =  str_replace('{Programme}', stripslashes($category_info['name']), $content);
            $content =  str_replace('{siteName}', api_get_setting('siteName'), $content);
            $content =  str_replace('{Institution}', api_get_setting('Institution'), $content); 
            $content =  str_replace('{InstitutionUrl}', api_get_setting('InstitutionUrl'), $content);             
            $content =  str_replace('{administratorSurname}', api_get_person_name(api_get_setting('administratorName'), api_get_setting('administratorSurname')), $content);
            $content = str_replace("{detailsUrl}", api_get_setting('InstitutionUrl'), $content);
            $content = str_replace("tmp_file", $domain_server, $content);
            
            api_mail_html($emailto, $emailsubject, $content);
        }
        
        /**
         * Save a payer 
         */         
        public static function save_payer_user($params) {
            $tbl_payer = Database::get_main_table(TABLE_MAIN_PAYER_USER);
            // save payer
            $payer_id = 0;
            if (!empty($params)) {
              $company       = $params['company']?$params['company']:'';
              $firstname     = $params['firstname']?$params['firstname']:'';
              $lastname      = $params['lastname']?$params['lastname']:'';
              $email         = $params['email']?$params['email']:'';
              $street_number = $params['street_number']?$params['street_number']:'';
              $street        = $params['street']?$params['street']:'';
              $zipcode       = $params['zipcode']?$params['zipcode']:'';
              $city          = $params['city']?$params['city']:'';
              $country       = $params['country']?$params['country']:'';
              $student_id    = $params['student_id']?$params['student_id']:'';
              $vat_number    = $params['vatnumber']?$params['vatnumber']:'';
              $phone         = $params['phone']?$params['phone']:'';
              $civility      = $params['civility']?$params['civility']:'';

              $sql = "INSERT INTO $tbl_payer SET  
                                firstname = '".Database::escape_string($firstname)."',
                                lastname = '".Database::escape_string($lastname)."',
                                email = '".Database::escape_string($email)."',
                                street_number = '".Database::escape_string($street_number)."',
                                street = '".Database::escape_string($street)."',
                                zipcode = '".Database::escape_string($zipcode)."',
                                city = '".Database::escape_string($city)."',
                                country = '".Database::escape_string($country)."',
                                student_id = '".intval($student_id)."',
                                vat_number = '".Database::escape_string($vat_number)."',
                                company = '".Database::escape_string($company)."',
                                phone = '".Database::escape_string($phone)."',
                                civility = '".Database::escape_string($civility)."'
                             ";              
              Database::query($sql);
              $payer_id = Database::insert_id();                  
            }
            return $payer_id;
        }
               
        /**
         *  Clear catalogue order process, unset all sessions for current order
         */
        public static function clear_catalogue_order_process() {
            // sessions
            if (isset($_SESSION['selected_sessions'])) {
                unset($_SESSION['selected_sessions']);
            }
            if (isset($_SESSION['selected_courses'])) {
                unset($_SESSION['selected_courses']);
            }
            if (isset($_SESSION['user_info'])) {
                unset($_SESSION['user_info']);
            }
            if (isset($_SESSION['payer_info'])) {
                unset($_SESSION['payer_info']);
            }
            if (isset($_SESSION['wish'])) {
                unset($_SESSION['wish']);
            }
            if (isset($_SESSION['iden'])) {
                unset($_SESSION['iden']);
            }
            if (isset($_SESSION['from'])) {
                unset($_SESSION['from']);
            }
            if (isset($_SESSION['pay_type'])) {
                unset($_SESSION['pay_type']);
            }       
            if (isset($_SESSION['steps'])) {
                unset($_SESSION['steps']);
            }    
            if (isset($_SESSION['cours_rel_session'])) {
                unset($_SESSION['cours_rel_session']);
            }
            if (isset($_SESSION['nvpReqArray'])) {
                unset($_SESSION['nvpReqArray']);
            }
            if (isset($_SESSION['shopping_cart']['items'])) {
                unset($_SESSION['shopping_cart']['items']);
            }
        }
        
        /**
         * Get catalogue installments payment information
         */ 
         public static function get_catalogue_installments_payment_info($catalogue_id) {
             $tbl_catalogue = Database::get_main_table(TABLE_MAIN_CATALOGUE);
             $catalogue = self::get_catalogue_info($catalogue_id);
             $info = array();
             if (!empty($catalogue)) {
                 // Second installment info
                 $info[2]['percent'] = isset($catalogue['second_installment'])?$catalogue['second_installment']:0;
                 $info[2]['delay']   = isset($catalogue['second_installment_delay'])?$catalogue['second_installment_delay']:0;
                 // Third installment info
                 $info[3]['percent'] = isset($catalogue['third_installment'])?$catalogue['third_installment']:0;
                 $info[3]['delay']   = isset($catalogue['third_installment_delay'])?$catalogue['third_installment_delay']:0;
                 // first installment info
                 $info[1]['percent'] = 100 - ($info[2]['percent'] + $info[3]['percent']);
                 $info[1]['delay']   = 0; // inmediate
             }
             return $info;
         }                   
                
        /*        
         * Get information of payer
         */
         public static function get_payer_info($student_id) {
             $tbl_payer = Database::get_main_table(TABLE_MAIN_PAYER_USER);
             $info = array();
             $rs = Database::query("SELECT * FROM $tbl_payer WHERE student_id = $student_id");
             if (Database::num_rows($rs)) {
                 while ($row = Database::fetch_array($rs)) {
                     $info = $row;
                 }
             }          
             return $info;
         }
         
         /*        
         * Get catalogue information
         */
         public static function get_topic_info($topic_id = null) {
             $tbl_topic = Database::get_main_table(TABLE_MAIN_TOPIC);
             $info = array();
             $rs = Database::query("SELECT * FROM $tbl_topic");
             if (Database::num_rows($rs)) {
                 while ($row = Database::fetch_array($rs)) {
                     $info[$row['id']] = $row;
                 }
                 
             }             
             return isset($topic_id)?$info[$topic_id]:$info;
         }    
         
         /*        
         * Get catalogue information
         */
         public static function get_catalogue_info($catalogue_id = null) {
             $tbl_catalogue = Database::get_main_table(TABLE_MAIN_CATALOGUE);
             $info = array();
             $rs = Database::query("SELECT * FROM $tbl_catalogue");
             if (Database::num_rows($rs)) {
                 while ($row = Database::fetch_array($rs)) {
                     $info[$row['id']] = $row;
                 }
                 
             }             
             return isset($catalogue_id)?$info[$catalogue_id]:$info;
         }
         
         /**
         * Get user payment atos
         * @param   int     User id
         * @param   int     Session category id
         * @return  array   Result
         */
        public static function get_user_sess_payment_atos($user_id, $sess_id) {
            $tbl_payment_atos = Database::get_main_table(TABLE_MAIN_PAYMENT_ATOS);
            $data = array();
            $rs = Database::query("SELECT * FROM $tbl_payment_atos WHERE user_id = {$user_id} AND sess_id = {$sess_id}");
            if (Database::num_rows($rs)) {
                $data = Database::fetch_array($rs);
            }
            return $data;
        }
        
        /**
         * Get country name by extra field
         */
        public static function get_country_name_by_extra_field($extcountry_code) {            
            $tbl_fld                = Database::get_main_table(TABLE_MAIN_USER_FIELD);
            $tbl_fld_opt            = Database::get_main_table(TABLE_MAIN_USER_FIELD_OPTIONS); 
            $country_name = '';
            $rs_fld_opt = Database::query("SELECT option_display_text FROM $tbl_fld_opt ufo INNER JOIN $tbl_fld uf ON uf.id = ufo.field_id WHERE uf.field_variable = 'country' AND ufo.option_value = '$extcountry_code'");
            if (Database::num_rows($rs_fld_opt) > 0) {
                    $row_fld_opt = Database::fetch_array($rs_fld_opt);
                    $country_name = $row_fld_opt['option_display_text'];
            }
            return $country_name;            
        }
        
        /**
         * Calculate cost to pay for atos
         */
         public static function get_user_amount_pay_atos($cost, $country_code) {
            $fr	 = array('001');  // France Fixed 19.60%
            $ue  = array(101, 104, 105, 106, 107, 108, 109, 110, 111, 112, 114, 116, 117, 122, 126, 127, 131, 132, 134, 135, 136, 137, 139, 144, 145, 254); // CEE Fixed 19.60%
            $dom = array(971, 972, 973, 974, 976); // DOM 8.50%
            $tom = array(975, 978, 979, 983, 984, 987, 988); // TOM 0%
                                    
            $amount = 0;
            if (!empty($cost)) {
                // TVA
                if (!empty($country_code)) {                    
                    $tva = self::get_tva_amount($cost, $country_code);
                    $amount = round($cost + $tva, 2);                    
                }	
            }                      
            return $amount;
         }
        
         /**
         * Calculate cost to pay for atos
         */
         public static function get_tva_amount($cost, $country_code) {
            $percent = self::get_percent_tva_by_country($country_code);
            $tva = 0;
            if (!empty($cost)) {
             $tva = ($cost * $percent)/100;                    
            }                        
            return $tva;			
         }
         
         /**
         * Get percent tva by country
         */         
         public static function get_percent_tva_by_country($country_code) {
            $fr  = array('001');  // France Fixed 19.60%
            $ue  = array(101, 104, 105, 106, 107, 108, 109, 110, 111, 112, 114, 116, 117, 122, 126, 127, 131, 132, 134, 135, 136, 137, 139, 144, 145, 254); // CEE Fixed 19.60%
            $dom = array(971, 972, 973, 974, 976); // DOM 8.50%
            $tom = array(975, 978, 979, 983, 984, 987, 988);
            $per = 0;
            if (!empty($country_code)) {
                if (in_array($country_code, $fr) || in_array($country_code, $ue)) {
                        // European community, CEE Assujetti: 19,60 %
                        $per = 19.60;			
                } else if (in_array($country_code, $dom)) {
                        // DOM: 8,50 %
                        $per = 8.50;			
                } else {
                        $per = 0;
                }                                    	
            }
            return $per;
         }   
         
         /**
          * Get next user quota installmente to pay
          */
         public static function get_next_quota_install_to_pay($user_id, $sess_id) {
             $next_quota = 0;
             $curr_info = self::get_user_sess_payment_atos($user_id, $sess_id);    
             if (!empty($curr_info)) {
                 $curr_quota = $curr_info['curr_quota'];
                 $next_quota = $curr_quota + 1;
                 if ($next_quota > 3) {$next_quota = 0;}
             } else {
                 $next_quota = 1;
             }
             return $next_quota;
         }
         
         /**
          * Get cost of installment payment by quota without tva
          */
         public static function get_cost_installment_quota($sess_id, $quota) {
             $cost = $amount = 0;
             $category = self::get_session_category($sess_id);
             // get catalogue_id             
             $catalogue = self::get_catalogue_info_by_category($sess_id);
             $catalogue_id = $catalogue['id'];             
             // installment
             $installment_info  = self::get_catalogue_installments_payment_info($catalogue_id);
             if (!empty($category['cost'])) {
                 $cost = $category['cost'];
                 $curr_per_install = $installment_info[$quota]['percent'];
                 $amount = ($cost * $curr_per_install)/100;
             }
             return $amount;
         }
         
         /**
          * Get catalogue information by session category(program)
          * @param  int     the session category id
          * @return array   The catalogue information
          */
         public static function get_catalogue_info_by_category($sess_cat_id) {
             $category = self::get_session_category($sess_cat_id);
             $topic = self::get_topic_info($category['topic']);
             $catalogue = self::get_catalogue_info($topic['catalogue_id']);
             return $catalogue;
         }
         
         /**
          * Save payment logs for each user
          * @param  array   Fields and values for record
          * @return int     Last insert id
          */
         public static function save_payment_log($params) {
             $tbl_harmony = Database::get_main_table(TABLE_MAIN_PAYMENT_LOG);
             $sql = "INSERT INTO $tbl_harmony SET 
                          user_id = '".intval($params['user_id'])."',
                          sess_id = '".intval($params['sess_id'])."',
                          pay_type = '".intval($params['pay_type'])."',
                          pay_data = '".Database::escape_string($params['pay_data'])."',
                          pay_time = '".time()."',
                          curr_quota = '".intval($params['curr_quota'])."',
                          transaction_id = '" . trim($params['transaction_id'])."' ,
                          ecommerce_gateway = '" . intval($params['ecommerce_gateway']) . "'";             
             Database::query( $sql );
            return Database::insert_id();             
         }
         
         /**
          * Display steps breadcrumbs, it used to navegate into catalogue registration
          * @return string  string html 
          */
         public static function display_steps_breadcrumbs() {

            $class = '';
            $href  = 'javascript:void(0)';
            // step1
            if (isset($_SESSION['steps'][1]) || ($_GET['next'] == 1 || $_GET['next'] == 2)) {
                $class = 'done';
                $href  = api_get_path(WEB_CODE_PATH).'admin/category_list.php?id='.$_SESSION['cat_id'].'&prev=1';
                if ((isset($_GET['next']) && $_GET['next'] == 1) || (isset($_GET['prev']) && $_GET['prev'] == 1)) {
                    $class = 'active';                        
                }
                $step1 .= '<li id="stepsbreadcrumbs1" class="'.$class.'"><a href="'.$href.'">1. '.get_lang('SelectioningOptions').'</a></li>';
            } else {
                $step1 .= '<li id="stepsbreadcrumbs1">1. '.get_lang('SelectioningOptions').'</li>';
            }
            
            // step2
            if (isset($_SESSION['steps'][2]) || $_GET['next'] == 2) {
                $class = 'done';
                $href  = api_get_path(WEB_CODE_PATH).'admin/registration.php?iden='.$_SESSION['iden'].'&wish='.$_SESSION['wish'].'&id='.$_SESSION['cat_id'].'&prev=2';
                if ((isset($_GET['next']) && $_GET['next'] == 2) || (isset($_GET['prev']) && $_GET['prev'] == 2)) {
                    $class = 'active';                        
                }
                $step2 .= '<li id="stepsbreadcrumbs2" class="'.$class.'"><a href="'.$href.'">2. '.get_lang('SelfRegistrationOrNot').'</a></li>';
            } else {
                $step2 .= '<li id="stepsbreadcrumbs2">2. '.get_lang('SelfRegistrationOrNot').'</li>';
            }
            
            // step3
            if (isset($_SESSION['steps'][3]) || $_GET['next'] == '3') {
                $class = 'done';
                $href  = api_get_path(WEB_CODE_PATH).'admin/registration_step3.php?iden='.$_SESSION['iden'].'&wish='.$_SESSION['wish'].'&id='.$_SESSION['cat_id'].'&prev=3';
                if ((isset($_GET['next']) && $_GET['next'] == '3') || (isset($_GET['prev']) && $_GET['prev'] == '3')) {
                    $class = 'active';                        
                }
                $step3 .= '<li id="stepsbreadcrumbs3" class="'.$class.'"><a href="'.$href.'">3. '.get_lang('StudentPersonalData').'</a></li>';
            } else {
                $step3 .= '<li id="stepsbreadcrumbs3">3. '.get_lang('StudentPersonalData').'</li>';
            }

            // step 3b
            if (intval($_SESSION['iden']) == 1 || ($_SESSION['iden'] == 0 && $_SESSION['wish'] == 1)) {                                   
                // step3b
                if (isset($_SESSION['steps']['3b']) || $_GET['next'] == '3b') {
                    $class = 'done';
                    $href  = api_get_path(WEB_CODE_PATH).'admin/registration_step3b.php?iden='.$_SESSION['iden'].'&wish='.$_SESSION['wish'].'&id='.$_SESSION['cat_id'].'&prev=3b';
                    if ((isset($_GET['next']) && $_GET['next'] === '3b') || (isset($_GET['prev']) && $_GET['prev'] === '3b')) {
                        $class = 'active';                        
                    }
                    $step3b .= '<li id="stepsbreadcrumbs3b" class="'.$class.'"><a href="'.$href.'">3b. '.get_lang('SubscriptionPayerPersonalData').'</a></li>';
                } else {
                    $step3b .= '<li id="stepsbreadcrumbs3b">3b. '.get_lang('SubscriptionPayerPersonalData').'</li>';
                }
            }
            
            // step4
            $title4 = !api_get_user_id()?'4. '.get_lang('SummaryView'):'2. '.get_lang('SummaryView');
            if (isset($_SESSION['steps'][4]) || $_GET['next'] == 4) {
                $class = 'done';
                $href  = api_get_path(WEB_CODE_PATH).'admin/feedback.php?iden='.$_SESSION['iden'].'&wish='.$_SESSION['wish'].'&id='.$_SESSION['cat_id'].'&prev=4';
                if ((isset($_GET['next']) && $_GET['next'] == 4) || (isset($_GET['prev']) && $_GET['prev'] == 4)) {
                    $class = 'active';                        
                }                                                
                $step4 .= '<li id="stepsbreadcrumbs4" class="'.$class.'"><a href="'.$href.'">'.$title4.'</a></li>';
            } else {
                $step4 .= '<li id="stepsbreadcrumbs4">'.$title4.'</li>';
            }
            
            // step5
            $title5 = !api_get_user_id()?'5. '.get_lang('Payment'):'3. '.get_lang('Payment');
            if (isset($_SESSION['steps'][5]) || $_GET['next'] == 5) {
                $class = 'done';
                $href  = api_get_path(WEB_CODE_PATH).'admin/payment_options.php?iden='.$_SESSION['iden'].'&wish='.$_SESSION['wish'].'&id='.$_SESSION['cat_id'].'&prev=5';
                if ((isset($_GET['next']) && $_GET['next'] == 5) || (isset($_GET['prev']) && $_GET['prev'] == 5)) {
                    $class = 'active';
                }
                $step5 .= '<li id="stepsbreadcrumbs5" class="'.$class.'"><a href="'.$href.'">'.$title5.'</a></li>';
            } else {
                $step5 .= '<li id="stepsbreadcrumbs5">'.$title5.'</li>';
            }
            $css_width = "";
            // step6
            $title6 = !api_get_user_id()?'6. '.get_lang('ChequePayment'):'4. '.get_lang('ChequePayment');
            if (isset($_GET['next']) && $_GET['next'] == 6) {                
                $step6 .= '<li id="stepsbreadcrumbs6" class="active"><a href="'.$href.'">'.$title6.'</a></li>';
                $css_width = "height:40px;";
            }            
            $html = '';
            $html .= '<div style="'.$css_width.'" id="stepsbreadcrumbs"><ul>';
            $html .= $step1;            
            if (!api_get_user_id()) {
                $html .= $step2;            
                $html .= $step3;
                $html .= $step3b;
            }            
            $html .= $step4;
            $html .= $step5;
            $html .= $step6;
            $html .= '</ul></div>';            
            echo $html;
         }
                  
         /**
          * Get number of users in a session
          * @param  int     Session id
          * @return int     number of users
          */
          public static function get_nbr_users_in_session($session_id) {
              $tbl_sess_user = Database::get_main_table(TABLE_MAIN_SESSION_USER);
              $nbr_users = 0;
              $rs = Database::query("SELECT count(*) FROM $tbl_sess_user WHERE id_session = $session_id");
              if (Database::num_rows($rs)) {
                  $row = Database::fetch_row($rs);
                  $nbr_users = $row[0];
              }      
              return $nbr_users;              
          }         
          
          /**
           * Get total seats in a session
           * @param   int   Session id
           * @return  int   the total seats
           */
          public static function get_max_seats_session($session_id) {
              $seats = 0;
              $session_info = api_get_session_info($session_id); 
              $max_seats = $session_info['max_seats'];
              $seats = intval($max_seats);    
              return $seats;
          }
          
          /**
           * Get countries belongins france
           * @return  array  country codes
           */
          public static function countries_belongins_france() {
            $fr  = array('001');
            $dom = array(971, 972, 973, 974, 976);
            $tom = array(975, 978, 979, 983, 984, 987, 988); 
            return array_merge($fr, $dom, $tom);
          }
          
          public static function get_sessions_by_course($course_code) {
            $tbl_session_rel_course = Database::get_main_table(TABLE_MAIN_SESSION_COURSE);
            $rs = Database::query("SELECT id_session FROM $tbl_session_rel_course WHERE course_code='".Database::escape_string($course_code)."'");
            $sessions = array();
            if (Database::num_rows($rs) > 0) {
                while ($row = Database::fetch_array($rs, 'ASSOC')) {
                    $sessions[] = $row['id_session'];
                }
            }
            return $sessions;
          }
                              
          public static function get_session_category_tutors($sess_category_id) {
              $tbl_session_category_rel_tutor = Database::get_main_table(TABLE_MAIN_SESSION_CATEGORY_REL_TUTOR);            
              $rs = Database::query("SELECT tutor_id FROM $tbl_session_category_rel_tutor WHERE session_category_id = $sess_category_id");
              $tutors = array();
              if (Database::num_rows($rs) > 0) {
                  while ($row = Database::fetch_object($rs)) {
                      $tutors[$row->tutor_id] = api_get_user_info($row->tutor_id);
                  }
              }
              return $tutors;
          }

}

