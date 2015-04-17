<?php //$id: $
/* For licensing terms, see /license.txt */

/**
*	This class provides methods for the social network management.
*	Include/require it in your code to use its features.
*
*	@package dokeos.social
*/

//PLUGIN PLACES
define('SOCIAL_LEFT_PLUGIN',	1);
define('SOCIAL_CENTER_PLUGIN',	2);
define('SOCIAL_RIGHT_PLUGIN',	3);

require_once api_get_path(LIBRARY_PATH).'usermanager.lib.php';
require_once api_get_path(LIBRARY_PATH).'message.lib.php';


// access url
$access_url_id = api_get_current_access_url_id();
if($access_url_id <= 0){
   $access_url_id = 1; 
}


class SocialManager extends UserManager {

	private function __construct() {
	}

	/**
	 * Allow to see contacts list
	 * @author isaac flores paz <florespaz@bidsoftperu.com>
	 * @return array
	 */
	public static function show_list_type_friends () {
		$friend_relation_list=array();
		$count_list=0;
		$tbl_my_friend_relation_type = Database :: get_main_table(TABLE_MAIN_USER_FRIEND_RELATION_TYPE);
		$sql='SELECT id,title FROM '.$tbl_my_friend_relation_type.' WHERE id<>6 ORDER BY id ASC';
		$result=Database::query($sql);
		while ($row=Database::fetch_array($result,'ASSOC')) {
			$friend_relation_list[]=$row;
		}
		$count_list=count($friend_relation_list);
		if ($count_list==0) {
			$friend_relation_list[]=get_lang('UnkNow');
		} else {
			return $friend_relation_list;
		}

	}
	/**
	 * Get relation type contact by name
	 * @param string names of the kind of relation
	 * @return int
	 * @author isaac flores paz <florespaz@bidsoftperu.com>
	 */
	public static function get_relation_type_by_name ($relation_type_name) {
		$list_type_friend=array();
		$list_type_friend=self::show_list_type_friends();
		foreach ($list_type_friend as $value_type_friend) {
			if (strtolower($value_type_friend['title'])==$relation_type_name) {
				return $value_type_friend['id'];
			}
		}
	}
	/**
	 * Get the kind of relation between contacts
	 * @param int user id
	 * @param int user friend id
	 * @param string
	 * @author isaac flores paz <florespaz@bidsoftperu.com>
	 */
	public static function get_relation_between_contacts ($user_id,$user_friend,$check_in_course_and_session = false) {
		$tbl_my_friend_relation_type = Database :: get_main_table(TABLE_MAIN_USER_FRIEND_RELATION_TYPE);
		$tbl_my_friend = Database :: get_main_table(TABLE_MAIN_USER_REL_USER);
        $tbl_course_rel_user = Database::get_main_table(TABLE_MAIN_COURSE_USER);
        $tbl_session_rel_user = Database::get_main_table(TABLE_MAIN_SESSION_USER);
        $tbl_session = Database::get_main_table(TABLE_MAIN_SESSION);
        if ($check_in_course_and_session) {
            if (api_is_platform_admin()) {
                return USER_RELATION_TYPE_FRIEND;
            } else {
                // Current user is not a platform admin, so we need check if the users exists in our courses or session
                $sql_check_course = "SELECT course_code FROM $tbl_course_rel_user WHERE user_id='".Database::escape_string($user_id)."' ";
                $rs_check_course = Database::query($sql_check_course, __FILE_,__LINE__);
                // Check if users exists inside my course
                while ($row_course = Database::fetch_array($rs_check_course, 'ASSOC')) {
                    $sql_check_friend_in_course = "SELECT COUNT(*) AS count FROM $tbl_course_rel_user WHERE course_code = '".$row_course['course_code']."' AND user_id='".Database::escape_string($user_friend)."'";
                    $rs_check_friend_in_course = Database::query($sql_check_friend_in_course,__FILE__,__LINE__);
                    $row_check_friend_in_course = Database::fetch_array($rs_check_friend_in_course, 'ASSOC');
                    $count_friend_in_course = $row_check_friend_in_course['count'];
                    if ($count_friend_in_course <> 0) {
                       return USER_RELATION_TYPE_FRIEND;
                    }
                 }
                 // User does not enrolled in my course, so I need check if users is enrolled in my session
                $sql_check_session = "SELECT id_session FROM $tbl_session_rel_user WHERE id_user='".Database::escape_string($user_id)."' ";
                $sql_check_session_coach = "SELECT id AS id_session FROM $tbl_session WHERE id_coach='".Database::escape_string($user_id)."' ";
                $sql_union_session = "$sql_check_session UNION $sql_check_session_coach";

                $rs_check_session = Database::query($sql_union_session, __FILE_,__LINE__);
                // Check if user exists inside my session
                while ($row_session = Database::fetch_array($rs_check_session, 'ASSOC')) {
                    $sql_check_friend_in_session = "SELECT COUNT(*) AS count FROM $tbl_session_rel_user WHERE id_session = '".$row_session['id_session']."' AND id_user='".Database::escape_string($user_friend)."' ";
                    $rs_check_friend_in_session = Database::query($sql_check_friend_in_session,__FILE__,__LINE__);
                    $row_check_friend_in_session = Database::fetch_array($rs_check_friend_in_session, 'ASSOC');
                    $count_check_friend_in_session = $row_check_friend_in_session['count'];
                    if ($count_check_friend_in_session <> 0) {
                       return USER_RELATION_TYPE_FRIEND;
                    }
                }
            }
        } else {
            $sql= 'SELECT rt.id as id FROM '.$tbl_my_friend_relation_type.' rt ' .
                  'WHERE rt.id=(SELECT uf.relation_type FROM '.$tbl_my_friend.' uf WHERE  user_id='.((int)$user_id).' AND friend_user_id='.((int)$user_friend).' AND uf.relation_type <> '.USER_RELATION_TYPE_RRHH.' )';
            $res=Database::query($sql);
            if (Database::num_rows($res)>0) {
                $row=Database::fetch_array($res,'ASSOC');
                return $row['id'];
            } else {
                return USER_UNKNOW;
            }
        }
	}

	/**
	 * Gets friends id list
	 * @param int  user id
	 * @param int group id
	 * @param string name to search
	 * @param bool true will load firstname, lastname, and image name
	 * @return array
	 * @author Julio Montoya <gugli100@gmail.com> Cleaning code, function renamed, $load_extra_info option added
	 * @author isaac flores paz <isaac.flores@dokeos.com>
	 */
	public static function get_friends($user_id, $id_group=null, $search_name=null, $load_extra_info = true) {
            global $access_url_id;
            $list_ids_friends    = array();
            $tbl_my_friend       = Database::get_main_table(TABLE_MAIN_USER_REL_USER);
            $tbl_course_rel_user = Database::get_main_table(TABLE_MAIN_COURSE_USER);
            $tbl_my_user         = Database::get_main_table(TABLE_MAIN_USER);
            $tbl_session_course_rel_user = Database::get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
            $tbl_access_url_rel_user     = Database::get_main_table(TABLE_MAIN_ACCESS_URL_REL_USER);
            
            $sql = 'SELECT friend_user_id FROM '.$tbl_my_friend.' WHERE relation_type NOT IN ('.USER_RELATION_TYPE_DELETED.', '.USER_RELATION_TYPE_RRHH.') AND friend_user_id<>'.((int)$user_id).' AND user_id='.((int)$user_id);
            // Get the user info of current user, we need know what is the status profile of the user, if user is student or teacher
            $current_user_info = api_get_user_info($user_id);
            $current_user_status = $current_user_info['status'];

            $include_user_id_list = array();
            switch ($current_user_status) {
            case STUDENT :
                // Get all info for the student user
                self::_get_friend_list($user_id, $current_user_status, $include_user_id_list, $list_ids_friends, $search_name);
                // Get indirect friend
                self::get_indirect_contact_list($user_id,$include_user_id_list, $list_ids_friends, null, $search_name);
            break;
            case COURSEMANAGER :
                // Get all info for the teacher/admin user
                if (api_is_platform_admin()) { // User is platform admin
                    // Admin is allowed see all users of the whole platform                    
                    $sql = 'SELECT u.user_id, u.lastname, u.firstname, u.username FROM '.$tbl_my_user.' u INNER JOIN '. $tbl_access_url_rel_user .' u_url ON(u.user_id = u_url.user_id)'
                    . ' WHERE u.status <> 6 AND u.user_id <> "'. Database::escape_string($user_id).'" '
                    . ' AND u_url.access_url_id = '. $access_url_id
                    . ' AND (u.lastname like "%'.$search_name.'%" OR u.firstname like "%'.$search_name.'%") ';
                    $res = Database::query($sql, __FILE__,__LINE__);

                    while ($row_user = Database::fetch_array($res)) {
                        if (!in_array($row_user['user_id'], $include_user_id_list)) {
                            $include_user_id_list[] = $row_user['user_id'];
                            $path = UserManager::get_user_picture_path_by_id($row_user['user_id'], 'web', false, true);
                            $list_ids_friends[] = array('friend_user_id' => $row_user['user_id'],'firstName' => $row_user['firstname'] , 'lastName' => $row_user['lastname'], 'username' => $row_user['username'], 'image' => $path['file'],'contact_type' => 0);
                        }
                    }
                } else { // User is teacher and has not admin rights
                    // Get the courses list where the user is teacher
                    self::_get_friend_list($user_id, $current_user_status, $include_user_id_list, $list_ids_friends);
                    // Get indirect friend
                    self::get_indirect_contact_list($user_id,$include_user_id_list, $list_ids_friends, null, $search_name);
                }
            break;
            case SESSIONADMIN:
                // Get all info for the session admin user
            break;
            case DRH:
                // Get all info for the session admin user
            break;
            }
            return $list_ids_friends;
	}

        /**
         * Allow get the friend list according to user ID and platfform status ID
         * @param integer $user_id
         * @param string $platform_status
         * @param array $include_user_id_list
         * @param array $list_ids_friends
         */
        public static function _get_friend_list ($user_id, $platform_status, &$include_user_id_list, &$list_ids_friends, $search = null) {
                    $tbl_my_friend = Database :: get_main_table(TABLE_MAIN_USER_REL_USER);
                    $tbl_course_rel_user = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
                    $tbl_my_user = Database :: get_main_table(TABLE_MAIN_USER);
                    $tbl_session = Database :: get_main_table(TABLE_MAIN_SESSION);
                    $tbl_session_course_rel_user = Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
                    $tbl_session_rel_user = Database :: get_main_table(TABLE_MAIN_SESSION_USER);

                    $sql = 'SELECT course_code FROM '.$tbl_course_rel_user.' WHERE user_id ="'.$user_id.'" AND status = "'.$platform_status.'"';
                    $rs = Database::query($sql, __FILE__,__LINE__);
                    while ($row_course = Database::fetch_array($rs)) {
                        $course_code = $row_course['course_code'];
                        $sql = "SELECT cru.user_id,lastname,firstname,username FROM ".$tbl_course_rel_user." cru INNER JOIN ".$tbl_my_user." u ON cru.user_id = u.user_id WHERE cru.course_code='".$course_code."' AND cru.user_id <> '".$user_id."' ";
                        if (!is_null($search)) {
                            $sql = "SELECT cru.user_id,lastname,firstname,username FROM ".$tbl_course_rel_user." cru INNER JOIN ".$tbl_my_user." u ON cru.user_id = u.user_id WHERE cru.course_code='".$course_code."' AND cru.user_id <> '".$user_id."' AND (lastname like '%".$search."%' OR firstname like '%".$search."%' OR firstname like '%".$search."%')";
                        }
                        $rs_users = Database::query($sql, __FILE__,__LINE__);
                        // Allow see all students that are subscribed in the courses of the teacher and allow see to students the users where he is subscribed
                        while ($row_users = Database::fetch_array($rs_users, __FILE__, __LINE__)) {
                            if (!in_array($row_users['user_id'], $include_user_id_list)) {
                              $include_user_id_list[] = $row_users['user_id'];
                              $path = UserManager::get_user_picture_path_by_id($row_users['user_id'],'web',false,true);
                              $list_ids_friends[] = array('friend_user_id' => $row_users['user_id'],'firstName' => $row_users['firstname'] , 'lastName' => $row_users['lastname'], 'username' => $row_users['username'], 'image' => $path['file'],'contact_type' => 0);
                          }
                      }
                  }
                  if ($platform_status == STUDENT) {
                      $session_status = 0;
                  } elseif ($platform_status == COURSEMANAGER) {
                      $session_status = 2;
                  }

                  // We need get all users of session where the teacher is tutor, get all users where the users is enrolled
                  $sql = 'SELECT course_code,id_session FROM '.$tbl_session_course_rel_user.' sru WHERE id_user = "'.$user_id.'" AND status = "'.$session_status.'"';
                  $rs = Database::query($sql, __FILE__, __LINE__);
                  while ($row_session = Database::fetch_array($rs)) {
                      $course_code = $row_session['course_code'];
                      $session_code = $row_session['id_session'];
                      $sql = 'SELECT user_id,lastname,firstname,username FROM '.$tbl_session_course_rel_user.' sru INNER JOIN '.$tbl_my_user.' u ON sru.id_user=u.user_id WHERE id_session="'.$session_code.'" AND course_code="'.$course_code.'" AND id_user <> "'.$user_id.'"';
                      if (!is_null($search)) {
                      $sql = 'SELECT user_id,lastname,firstname,username FROM '.$tbl_session_course_rel_user.' sru INNER JOIN '.$tbl_my_user.' u ON sru.id_user=u.user_id WHERE id_session="'.$session_code.'" AND course_code="'.$course_code.'" AND id_user <> "'.$user_id.'" AND (lastname like "%'.$search.'%" OR firstname like "%'.$search.'%" OR firstname like "%'.$search.'%")';
                      }
                      $rs = Database::query($sql, __FILE__, __LINE__);
                      while ($row_u_sess =  Database::fetch_array($rs)) {
                          if (!in_array($row_u_sess['user_id'], $include_user_id_list)) {
                              $include_user_id_list[] = $row_u_sess['user_id'];
                              $path = UserManager::get_user_picture_path_by_id($row_u_sess['user_id'],'web',false,true);
                              $list_ids_friends[] = array('friend_user_id' => $row_u_sess['user_id'],'firstName' => $row_u_sess['firstname'] , 'lastName' => $row_u_sess['lastname'], 'username' => $row_u_sess['username'], 'image' => $path['file'], 'contact_type' => 0);
                          }
                      }
                  }

                  // We need get all users of session where the teacher is global tutor, get all users where the users is enrolled
                  $rs_sess = Database::query('SELECT id FROM '.$tbl_session.' WHERE id_coach = "'.$user_id.'"');
                  while ($row_sess = Database::fetch_array($rs_sess)) {
                      $session_code = $row_sess['id'];
                      $sql = 'SELECT user_id, lastname, firstname, username FROM '.$tbl_session_rel_user.' su INNER JOIN '.$tbl_my_user.' u ON su.id_user=u.user_id WHERE id_session="'.$session_code.'"';
                      if (!is_null($search)) {
                      $sql = 'SELECT user_id,lastname,firstname,username FROM '.$tbl_session_rel_user.' su INNER JOIN '.$tbl_my_user.' u ON su.id_user=u.user_id WHERE id_session="'.$session_code.'" AND (lastname like "%'.$search.'%" OR firstname like "%'.$search.'%" OR firstname like "%'.$search.'%")';
                      }
                      $rs_sess_user = Database::query($sql, __FILE__, __LINE__);
                      while ($row_sess_user =  Database::fetch_array($rs_sess_user)) {
                          if (!in_array($row_sess_user['user_id'], $include_user_id_list)) {
                              $include_user_id_list[] = $row_sess_user['user_id'];
                              $path = UserManager::get_user_picture_path_by_id($row_sess_user['user_id'],'web',false,true);
                              $list_ids_friends[] = array('friend_user_id' => $row_sess_user['user_id'],'firstName' => $row_sess_user['firstname'] , 'lastName' => $row_sess_user['lastname'], 'username' => $row_sess_user['username'], 'image' => $path['file'], 'contact_type' => 0);
                          }
                      }
                  }

        }
        /**
         * Get contacts from another courses,another sessions
         */
        public static function get_indirect_contact_list ($user_id, &$include_user_id_list, &$list_ids_friends, $id_group=null, $search_name=null, $load_extra_info = true) {
		$tbl_my_friend = Database :: get_main_table(TABLE_MAIN_USER_REL_USER);
		$tbl_my_user = Database :: get_main_table(TABLE_MAIN_USER);
		$sql='SELECT friend_user_id FROM '.$tbl_my_friend.' WHERE relation_type NOT IN ('.USER_RELATION_TYPE_DELETED.', '.USER_RELATION_TYPE_RRHH.') AND friend_user_id<>'.((int)$user_id).' AND user_id='.((int)$user_id);
		if (isset($id_group) && $id_group>0) {
			$sql.=' AND relation_type='.$id_group;
		}
		if (isset($search_name) && is_string($search_name)===true) {
			$search_name = trim($search_name);
			$search_name = str_replace(' ', '', $search_name);
			//$sql.=' AND friend_user_id IN (SELECT user_id FROM '.$tbl_my_user.' WHERE '.(api_is_western_name_order() ? 'concat(firstName, lastName)' : 'concat(lastName, firstName)').' like concat("%","'.Database::escape_string($search_name).'","%"));';
			$sql.=' AND friend_user_id IN (SELECT user_id FROM '.$tbl_my_user.' WHERE firstName LIKE "%'.Database::escape_string($search_name).'%" OR lastName LIKE "%'.Database::escape_string($search_name).'%"   OR    '.(api_is_western_name_order() ? 'concat(firstName, lastName)' : 'concat(lastName, firstName)').' like concat("%","'.Database::escape_string($search_name).'","%")    ) ';
		}

		$res = Database::query($sql);
		while ($row = Database::fetch_array($res, 'ASSOC')) {
                          if (!in_array($row['friend_user_id'], $include_user_id_list)) {
                                $include_user_id_list[] = $row['friend_user_id'];
				$path = UserManager::get_user_picture_path_by_id($row['friend_user_id'],'web',false,true);
				$my_user_info = api_get_user_info($row['friend_user_id']);
				$list_ids_friends[] = array('friend_user_id'=>$row['friend_user_id'],'firstName'=>$my_user_info['firstName'] , 'lastName'=>$my_user_info['lastName'], 'username'=>$my_user_info['username'], 'image'=>$path['file'], 'contact_type' => 1);

                          }
                }
		return $list_ids_friends;
        }
	/**
	 * get list web path of contacts by user id
	 * @param int user id
	 * @param int group id
	 * @param string name to search
	 * @param array
	 * @author isaac flores paz <florespaz@bidsoftperu.com>
	 */
	public static function get_list_path_web_by_user_id ($user_id,$id_group=null,$search_name=null) {
		$list_paths=array();
		$list_path_friend=array();
		$array_path_user=array();
		$combine_friend = array();
		$list_ids = self::get_friends($user_id,$id_group,$search_name);
		if (is_array($list_ids)) {
			foreach ($list_ids as $values_ids) {
				$list_path_image_friend[] = UserManager::get_user_picture_path_by_id($values_ids['friend_user_id'],'web',false,true);
				$combine_friend=array('id_friend'=>$list_ids,'path_friend'=>$list_path_image_friend);
			}
		}
		return $combine_friend;
	}

	/**
	 * get web path of user invitate
	 * @author isaac flores paz <florespaz@bidsoftperu.com>
	 * @param int user id
	 * @return array
	 */
	public static function get_list_web_path_user_invitation_by_user_id ($user_id) {
		$list_paths=array();
		$list_path_friend=array();
		$list_ids = self::get_list_invitation_of_friends_by_user_id((int)$user_id);
		foreach ($list_ids as $values_ids) {
			$list_path_image_friend[] = UserManager::get_user_picture_path_by_id($values_ids['user_sender_id'],'web',false,true);
		}
		return $list_path_image_friend;
	}

	/**
	 * Sends an invitation to contacts
	 * @param int user id
	 * @param int user friend id
	 * @param string title of the message
	 * @param string content of the message
	 * @return boolean
	 * @author isaac flores paz <florespaz@bidsoftperu.com>
	 * @author Julio Montoya <gugli100@gmail.com> Cleaning code
	 */
	public static function send_invitation_friend ($user_id,$friend_id,$message_title,$message_content) {
		$tbl_message = Database::get_main_table(TABLE_MAIN_MESSAGE);
		$user_id = intval($user_id);
		$friend_id = intval($friend_id);
		$message_title   = Database::escape_string($message_title);
		$message_content = Database::escape_string($message_content);

		$current_date = date('Y-m-d H:i:s',time());
		$sql_exist='SELECT COUNT(*) AS count FROM '.$tbl_message.' WHERE user_sender_id='.($user_id).' AND user_receiver_id='.($friend_id).' AND msg_status IN(5,6,7);';
		$res_exist=Database::query($sql_exist);
		$row_exist=Database::fetch_array($res_exist,'ASSOC');

		if ($row_exist['count']==0) {
			$sql='INSERT INTO '.$tbl_message.'(user_sender_id,user_receiver_id,msg_status,send_date,title,content) VALUES('.$user_id.','.$friend_id.','.MESSAGE_STATUS_INVITATION_PENDING.',"'.$current_date.'","'.$message_title.'","'.$message_content.'")';
			Database::query($sql);
			return true;
		} else {
			//invitation already exist
			$sql_if_exist='SELECT COUNT(*) AS count, id FROM '.$tbl_message.' WHERE user_sender_id='.$user_id.' AND user_receiver_id='.$friend_id.' AND msg_status=7';
			$res_if_exist=Database::query($sql_if_exist);
			$row_if_exist=Database::fetch_array($res_if_exist,'ASSOC');
			if ($row_if_exist['count'] > 0) {
				$sql_if_exist_up='UPDATE '.$tbl_message.'SET msg_status=5, content = "'.$message_content.'"  WHERE user_sender_id='.$user_id.' AND user_receiver_id='.$friend_id.' AND msg_status = 7 ';
				//$sql_if_exist_up='UPDATE '.$tbl_message.'SET msg_status=5, set content = '.$message_content.' WHERE id='.$row_if_exist['id'].'';
				Database::query($sql_if_exist_up);
				return true;
			} else {
				return false;
			}
		}
	}
	/**
	 * Get number messages of the inbox
	 * @author isaac flores paz <florespaz@bidsoftperu.com>
	 * @param int user receiver id
	 * @return int
	 */
	public static function get_message_number_invitation_by_user_id ($user_receiver_id) {
		$tbl_message=Database::get_main_table(TABLE_MAIN_MESSAGE);
		$sql='SELECT COUNT(*) as count_message_in_box FROM '.$tbl_message.' WHERE user_receiver_id='.intval($user_receiver_id).' AND msg_status='.MESSAGE_STATUS_INVITATION_PENDING;
		$res=Database::query($sql);
		$row=Database::fetch_array($res,'ASSOC');
		return $row['count_message_in_box'];
	}

	/**
	 * Get invitation list received by user
	 * @author isaac flores paz <florespaz@bidsoftperu.com>
	 * @param int user id
	 * @return array()
	 */
	public static function get_list_invitation_of_friends_by_user_id ($user_id) {
		$list_friend_invitation=array();
		$tbl_message=Database::get_main_table(TABLE_MAIN_MESSAGE);
		$sql='SELECT distinct user_sender_id, send_date,title,content FROM '.$tbl_message.' WHERE user_receiver_id='.intval($user_id).' AND msg_status = '.MESSAGE_STATUS_INVITATION_PENDING;
		$res=Database::query($sql);
		while ($row=Database::fetch_array($res,'ASSOC')) {
			$list_friend_invitation[]=$row;
		}
		return $list_friend_invitation;
	}
        
        /**
         * define if user_id is friend with friend_id
         * @param int $user_id
         * @param int $friend_id
         * @return boolean
         */
        public static function is_friend($user_id, $friend_id){
            $returnValue = FALSE;
		$tbl_message=Database::get_main_table(TABLE_MAIN_MESSAGE);
		$sql='SELECT * FROM '.$tbl_message.' WHERE user_sender_id = '.$user_id.' AND user_receiver_id='.intval($friend_id).' AND msg_status IN('.MESSAGE_STATUS_INVITATION_PENDING.','.MESSAGE_STATUS_INVITATION_ACCEPTED.')';
		$res=Database::query($sql);
                $num = Database::num_rows($res);
                if($num){
                    $returnValue = TRUE;
                }
            return $returnValue;
        }        

	/**
	 * Get invitation list sent by user
	 * @author Julio Montoya <gugli100@gmail.com>
	 * @param int user id
	 * @return array()
	 */

	public static function get_list_invitation_sent_by_user_id ($user_id) {
		$list_friend_invitation=array();
		$tbl_message=Database::get_main_table(TABLE_MAIN_MESSAGE);
		$sql='SELECT user_receiver_id, send_date,title,content FROM '.$tbl_message.' WHERE user_sender_id = '.intval($user_id).' AND msg_status = '.MESSAGE_STATUS_INVITATION_PENDING;
		$res=Database::query($sql);
		while ($row=Database::fetch_array($res,'ASSOC')) {
			$list_friend_invitation[$row['user_receiver_id']]=$row;
		}
		return $list_friend_invitation;
	}

	/**
	 * Accepts invitation
	 * @param int user sender id
	 * @param int user receiver id
	 * @author isaac flores paz <florespaz@bidsoftperu.com>
	 * @author Julio Montoya <gugli100@gmail.com> Cleaning code
	 */
	public static function invitation_accepted ($user_send_id,$user_receiver_id) {
		$tbl_message=Database::get_main_table(TABLE_MAIN_MESSAGE);
		$sql='UPDATE '.$tbl_message.' SET msg_status='.MESSAGE_STATUS_INVITATION_ACCEPTED.' WHERE user_sender_id='.((int)$user_send_id).' AND user_receiver_id='.((int)$user_receiver_id).';';
		Database::query($sql);
	}
	/**
	 * Denies invitation
	 * @param int user sender id
	 * @param int user receiver id
	 * @author isaac flores paz <florespaz@bidsoftperu.com>
	 * @author Julio Montoya <gugli100@gmail.com> Cleaning code
	 */
	public static function invitation_denied ($user_send_id,$user_receiver_id) {
		$tbl_message=Database::get_main_table(TABLE_MAIN_MESSAGE);
		//$msg_status=7;
		//$sql='UPDATE '.$tbl_message.' SET msg_status='.$msg_status.' WHERE user_sender_id='.((int)$user_send_id).' AND user_receiver_id='.((int)$user_receiver_id).';';
		$sql='DELETE FROM '.$tbl_message.' WHERE user_sender_id='.((int)$user_send_id).' AND user_receiver_id='.((int)$user_receiver_id).';';
		Database::query($sql);
	}
	/**
	 * allow attach to group
	 * @author isaac flores paz <florespaz@bidsoftperu.com>
	 * @param int user to qualify
	 * @param int kind of rating
	 * @return void()
	 */
	public static function qualify_friend ($id_friend_qualify,$type_qualify) {
		$tbl_user_friend=Database::get_main_table(TABLE_MAIN_USER_REL_USER);
		$user_id=api_get_user_id();
		$sql='UPDATE '.$tbl_user_friend.' SET relation_type='.((int)$type_qualify).' WHERE user_id='.((int)$user_id).' AND friend_user_id='.((int)$id_friend_qualify).';';
		Database::query($sql);
	}
	/**
	 * Sends invitations to friends
	 * @author Isaac Flores Paz <isaac.flores.paz@gmail.com>
	 * @author Julio Montoya <gugli100@gmail.com> Cleaning code
	 * @param void
	 * @return string message invitation
	 */
	public static function send_invitation_friend_user ($userfriend_id,$subject_message='',$content_message='') {
		global $charset;
		//$id_user_friend=array();
		$user_info = array();
		$user_info = api_get_user_info($userfriend_id);
		$succes = get_lang('MessageSentTo');
		$succes.= ' : '.api_get_person_name($user_info['firstName'], $user_info['lastName']);
		if (isset($subject_message) && isset($content_message) && isset($userfriend_id)) {
                    $send_message = MessageManager::send_message($userfriend_id, $subject_message, $content_message);
                    if ($send_message) {
                            echo $succes;
                    } else {
                            echo $succes;
                    }
                    return false;
		} elseif (isset($userfriend_id) && !isset($subject_message)) {
                    $count_is_true=false;
                    $count_number_is_true=0;
                    if (isset($userfriend_id) && $userfriend_id>0) {
                            $message_title = get_lang('Invitation');
                            $count_is_true = self::send_invitation_friend(api_get_user_id(),$userfriend_id, $message_title, $content_message);
                            if ($count_is_true) {
                                echo '<span">';
                                echo Display::return_icon('pixel.gif',get_lang('email'), array('class' => 'toolactionplaceholdericon toolactionsendmail'));
                                echo '</span>';
                                echo '<span style="margin-left:20px;">';
                                echo api_utf8_encode(get_lang('InvitationHasBeenSent'));
                                echo '</span>';
                                exit;
                            }else {
                                    echo api_utf8_encode(get_lang('YouAlreadySentAnInvitation'));
                            }

                    }
		}
	}

	/**
	 * Get user's feeds
	 * @param   int User ID
	 * @param   int Limit of posts per feed
	 * @return  string  HTML section with all feeds included
	 * @author  Yannick Warnier
	 * @since   Dokeos 1.8.6.1
	 */
	public static function get_user_feeds($user, $limit=5) {
	    if (!function_exists('fetch_rss')) { return '';}
		$fields = UserManager::get_extra_fields();
	    $feed_fields = array();
	    $feeds = array();
	    $feed = UserManager::get_extra_user_data_by_field($user,'rssfeeds');
	    if(empty($feed)) { return ''; }
	    $feeds = split(';',$feed['rssfeeds']);
	    if (count($feeds)==0) { return ''; }
	    foreach ($feeds as $url) {
			if (empty($url)) { continue; }
	        $rss = @fetch_rss($url);
	        $i = 1;
			if (!empty($rss->items)) {
				$res .= '<h2>'.$rss->channel['title'].'</h2>';
	        	$res .= '<div class="social-rss-channel-items">';
		        foreach ($rss->items as $item) {
		            if ($limit>=0 and $i>$limit) {break;}
		        	$res .= '<h3>a<a href="'.$item['link'].'">'.$item['title'].'</a></h3>';
		            $res .= '<div class="social-rss-item-date">'.api_get_datetime($item['date_timestamp']).'</div>';
		            $res .= '<div class="social-rss-item-content">'.$item['description'].'</div><br />';
		            $i++;
		        }
		        $res .= '</div>';
			}
	    }
	    return $res;
	}

	/**
	 * Helper functions definition
	 */
	public static function get_logged_user_course_html($my_course, $count) {
		global $nosession;
		if (api_get_setting('use_session_mode')=='true' && !$nosession) {
			global $now, $date_start, $date_end;
		}
		//initialise
		$result = '';
		// Table definitions
		$main_user_table 		 = Database :: get_main_table(TABLE_MAIN_USER);
		$tbl_session 			 = Database :: get_main_table(TABLE_MAIN_SESSION);
		$course_database 		 = $my_course['db'];
		$course_tool_table 		 = Database :: get_course_table(TABLE_TOOL_LIST, $course_database);
		$tool_edit_table 		 = Database :: get_course_table(TABLE_ITEM_PROPERTY, $course_database);
		$course_group_user_table = Database :: get_course_table(TOOL_USER, $course_database);

		$user_id = api_get_user_id();
		$course_system_code = $my_course['k'];
		$course_visual_code = $my_course['c'];
		$course_title = $my_course['i'];
		$course_directory = $my_course['d'];
		$course_teacher = $my_course['t'];
		$course_teacher_email = isset($my_course['email'])?$my_course['email']:'';
		$course_info = Database :: get_course_info($course_system_code);

		$course_access_settings = CourseManager :: get_access_settings($course_system_code);

		$course_visibility = $course_access_settings['visibility'];

		$user_in_course_status = CourseManager :: get_user_in_course_status(api_get_user_id(), $course_system_code);
		//function logic - act on the data
		$is_virtual_course = CourseManager :: is_virtual_course_from_system_code($my_course['k']);
		if ($is_virtual_course) {
			// If the current user is also subscribed in the real course to which this
			// virtual course is linked, we don't need to display the virtual course entry in
			// the course list - it is combined with the real course entry.
			$target_course_code = CourseManager :: get_target_of_linked_course($course_system_code);
			$is_subscribed_in_target_course = CourseManager :: is_user_subscribed_in_course(api_get_user_id(), $target_course_code);
			if ($is_subscribed_in_target_course) {
				return; //do not display this course entry
			}
		}
		$has_virtual_courses = CourseManager :: has_virtual_courses_from_code($course_system_code, api_get_user_id());
		if ($has_virtual_courses) {
			$return_result = CourseManager :: determine_course_title_from_course_info(api_get_user_id(), $course_info);
			$course_display_title = $return_result['title'];
			$course_display_code = $return_result['code'];
		} else {
			$course_display_title = $course_title;
			$course_display_code = $course_visual_code;
		}
		$s_course_status=$my_course['s'];
		$s_htlm_status_icon="";

        if ($s_course_status==1) {
			$s_htlm_status_icon=Display::return_icon('pixel.gif',get_lang('Course'),array('class' => 'actionplaceholdericon actioncourse')).Display::return_icon('pixel.gif',get_lang('Status').': '.get_lang('Teacher'),array('class' => 'actionplaceholdericon actiontrainer'));
		}
		if ($s_course_status==2) {
			$s_htlm_status_icon=Display::return_icon('pixel.gif',get_lang('Course'),array('class' => 'actionplaceholdericon actioncourse')).' '.Display::return_icon('pixel.gif',get_lang('Status').': '.get_lang('Teacher'),array('class' => 'actionplaceholdericon actioncoach'));
		}
		if ($s_course_status==5) {
			$s_htlm_status_icon=Display::return_icon('pixel.gif',get_lang('Course'),array('class' => 'actionplaceholdericon actioncourse')).' '.Display::return_icon('pixel.gif',get_lang('Status').': '.get_lang('Student'),array('class' => 'actionplaceholdericon actionuser'));
		}

		//display course entry
		$result .= '<div id="div_'.$count.'">';
		//$result .= '<a id="btn_'.$count.'" href="#" onclick="toogle_course(this,\''.$course_database.'\')">';
		$result .= '<h2><img src="../img/nolines_plus.gif" id="btn_'.$count.'" onclick="toogle_course(this,\''.$course_database.'\' )" alt="&nbsp;" />';
		$result .= $s_htlm_status_icon;

		//show a hyperlink to the course, unless the course is closed and user is not course admin
		if ($course_visibility != COURSE_VISIBILITY_CLOSED || $user_in_course_status == COURSEMANAGER) {
			$result .= '<a href="javascript:void(0)" id="ln_'.$count.'"  onclick="toogle_course(this,\''.$course_database.'\');">&nbsp;'.$course_title.'</a></h2>';
			/*
			if(api_get_setting('use_session_mode')=='true' && !$nosession) {
				if(empty($my_course['id_session'])) {
					$my_course['id_session'] = 0;
				}
				if($user_in_course_status == COURSEMANAGER || ($date_start <= $now && $date_end >= $now) || $date_start=='0000-00-00') {
					//$result .= '<a href="'.api_get_path(WEB_COURSE_PATH).$course_directory.'/?id_session='.$my_course['id_session'].'">'.$course_display_title.'</a>';
					$result .= '<a href="#">'.$course_display_title.'</a>';
				}
			} else {
				//$result .= '<a href="'.api_get_path(WEB_COURSE_PATH).$course_directory.'/">'.$course_display_title.'</a>';
				$result .= '<a href="'.api_get_path(WEB_COURSE_PATH).$course_directory.'/">'.$course_display_title.'</a>';
			}*/
		} else {
			$result .= $course_display_title." "." ".get_lang('CourseClosed')."";
		}
		// show the course_code and teacher if chosen to display this
		// we dont need this!
		/*
				if (api_get_setting('display_coursecode_in_courselist') == 'true' OR api_get_setting('display_teacher_in_courselist') == 'true') {
					$result .= '<br />';
				}
				if (api_get_setting('display_coursecode_in_courselist') == 'true') {
					$result .= $course_display_code;
				}
				if (api_get_setting('display_coursecode_in_courselist') == 'true' AND api_get_setting('display_teacher_in_courselist') == 'true') {
					$result .= ' &ndash; ';
				}
				if (api_get_setting('display_teacher_in_courselist') == 'true') {
					$result .= $course_teacher;
					if(!empty($course_teacher_email)) {
						$result .= ' ('.$course_teacher_email.')';
					}
				}
		*/
		$current_course_settings = CourseManager :: get_access_settings($my_course['k']);
		// display the what's new icons
		//	$result .= show_notification($my_course);
		if ((CONFVAL_showExtractInfo == SCRIPTVAL_InCourseList || CONFVAL_showExtractInfo == SCRIPTVAL_Both) && $nbDigestEntries > 0) {
			reset($digest);
			$result .= '<ul>';
			while (list ($key2) = each($digest[$thisCourseSysCode])) {
				$result .= '<li>';
				if ($orderKey[1] == 'keyTools') {
					$result .= "<a href=\"$toolsList[$key2] [\"path\"] $thisCourseSysCode \">";
					$result .= "$toolsList[$key2][\"name\"]</a>";
				} else {
					$result .= api_convert_and_format_date($key2, DATE_FORMAT_LONG, date_default_timezone_get());
				}
				$result .= '</li>';
				$result .= '<ul>';
				reset($digest[$thisCourseSysCode][$key2]);
				while (list ($key3, $dataFromCourse) = each($digest[$thisCourseSysCode][$key2])) {
					$result .= '<li>';
					if ($orderKey[2] == 'keyTools') {
						$result .= "<a href=\"$toolsList[$key3] [\"path\"] $thisCourseSysCode \">";
						$result .= "$toolsList[$key3][\"name\"]</a>";
					} else {
						$result .= api_convert_and_format_date($key3, DATE_FORMAT_LONG, date_default_timezone_get());
					}
					$result .= '<ul compact="compact">';
					reset($digest[$thisCourseSysCode][$key2][$key3]);
					while (list ($key4, $dataFromCourse) = each($digest[$thisCourseSysCode][$key2][$key3])) {
						$result .= '<li>';
						$result .= htmlspecialchars(substr(strip_tags($dataFromCourse), 0, CONFVAL_NB_CHAR_FROM_CONTENT));
						$result .= '</li>';
					}
					$result .= '</ul>';
					$result .= '</li>';
				}
				$result .= '</ul>';
				$result .= '</li>';
			}
			$result .= '</ul>';
		}
		$result .= '</li>';
		$result .= '</div>';

		if (api_get_setting('use_session_mode')=='true' && !$nosession) {
			$session = '';
			$active = false;
			if (!empty($my_course['session_name'])) {

				// Request for the name of the general coach
				$sql = 'SELECT lastname, firstname
						FROM '.$tbl_session.' ts  LEFT JOIN '.$main_user_table .' tu
						ON ts.id_coach = tu.user_id
						WHERE ts.id='.(int) $my_course['id_session']. ' LIMIT 1';
				$rs = Database::query($sql);
				$sessioncoach = Database::store_result($rs);
				$sessioncoach = $sessioncoach[0];

				$session = array();
				$session['title'] = $my_course['session_name'];
				if ( $my_course['date_start']=='0000-00-00' ) {
					$session['dates'] = get_lang('WithoutTimeLimits');
					if ( api_get_setting('show_session_coach') === 'true' ) {
						$session['coach'] = get_lang('GeneralCoach').': '.api_get_person_name($sessioncoach['firstname'], $sessioncoach['lastname']);
					}
					$active = true;
				} else {
					$session ['dates'] = ' - '.get_lang('From').' '.$my_course['date_start'].' '.get_lang('To').' '.$my_course['date_end'];
					if ( api_get_setting('show_session_coach') === 'true' ) {
						$session['coach'] = get_lang('GeneralCoach').': '.api_get_person_name($sessioncoach['firstname'], $sessioncoach['lastname']);
					}
					$active = ($date_start <= $now && $date_end >= $now)?true:false;
				}
			}
			$output = array ($my_course['user_course_cat'], $result, $my_course['id_session'], $session, 'active'=>$active);
		} else {
			$output = array ($my_course['user_course_cat'], $result);
		}
		//$my_course['creation_date'];
		return $output;
	}

	/**
	 * Shows the right menu of the Social Network tool
	 *
	 * @param string highlight link possible values: group_add, home, messages, messages_inbox, messages_compose ,messages_outbox ,invitations, shared_profile, friends, groups search
	 * @param int group id
	 * @param int user id
	 * @param bool show profile or not (show or hide the user image/information)
	 *
	 */
	public static function show_social_menu($show = '', $group_id = 0, $user_id = 0, $show_full_profile = false) {

		if (empty($user_id)) {
			$user_id = api_get_user_id();
		}

		$show_groups = array('groups', 'group_messages', 'messages_list', 'group_add', 'mygroups', 'group_edit', 'member_list', 'invite_friends', 'waiting_list');
		$show_messages = array('messages', 'messages_inbox', 'messages_outbox', 'messages_compose');

		// get count unread message and total invitations
		$count_unread_message = MessageManager::get_number_of_messages(true);
		$count_unread_message = (!empty($count_unread_message)?' ('.$count_unread_message.')':'');

		$number_of_new_messages_of_friend	= SocialManager::get_message_number_invitation_by_user_id(api_get_user_id());
		$group_pending_invitations = GroupPortalManager::get_groups_by_user(api_get_user_id(), GROUP_USER_PERMISSION_PENDING_INVITATION,false);
		$group_pending_invitations = count($group_pending_invitations);
		$total_invitations = $number_of_new_messages_of_friend + $group_pending_invitations;
		$total_invitations = (!empty($total_invitations)?' ('.$total_invitations.')':'');

		echo '<div class="social-menu">';
                $user_info = api_get_user_info($user_id);               
	  	if (in_array($show, $show_groups) && !empty($group_id)) {
			//--- Group image

			$group_info = GroupPortalManager::get_group_data($group_id);
			$big		= GroupPortalManager::get_picture_group($group_id, $group_info['picture_uri'],160,GROUP_IMAGE_SIZE_BIG);
			$original	= GroupPortalManager::get_picture_group($group_id, $group_info['picture_uri'],'',GROUP_IMAGE_SIZE_ORIGINAL);

			echo '<div class="social-content-image">';
				echo '<div class="social-background-content" onmouseout="hide_icon_edit()" onmouseover="show_icon_edit()"><center>';

				if (basename($big['file']) != 'unknown_group.png') {
					echo '<a class="thickbox" href="'.$original['file'].'"><img src="'.$big['file'].'" class="social-groups-image" alt="&nbsp;" /> </a><br /><br />';
				} else {
					echo '<img src='.$big['file'].' class="social-groups-image" alt="" /><br /><br />';
				}
				if (GroupPortalManager::is_group_admin($group_id, api_get_user_id())) {
					echo '<div id="edit_image" class="hidden_message" style="display:none"><a href="'.api_get_path(WEB_PATH).'main/social/group_edit.php?id='.$group_id.'">'. $user_info['firstname'] . ' ' . $user_info['lastname'] .'</a></div>';
				}

	    		echo '</center></div>';
		  	echo '</div>';
	  	} else {
	  		$img_array = UserManager::get_user_picture_path_by_id($user_id,'web',true,true);
			$big_image = UserManager::get_picture_user($user_id, $img_array['file'],'', USER_IMAGE_SIZE_BIG);
			$big_image = $big_image['file'].$big_image['dir'];
                        
	  		//--- User image
                        
			echo '<div class="social-content-image">';
				echo '<div class="social-background-content" onmouseout="hide_icon_edit()" onmouseover="show_icon_edit()"><center>';

					if ($img_array['file'] != 'unknown.jpg') {
		    	  		echo '<a class="thickbox" href="'.$big_image.'"><img width="170" src="'.$img_array['dir'].$img_array['file'].'" alt="&nbsp;" /> </a>';
					} else {
						echo '<img src="'.$img_array['dir'].$img_array['file'].'" width="110px" alt="&nbsp;" />';
					}
					if (api_get_user_id() == $user_id) {
						echo '<div id="edit_image" class="hidden_message" style="display:none"><a href="'.api_get_path(WEB_PATH).'main/auth/profile.php">'. $user_info['firstname'] . ' ' . $user_info['lastname'] .'</a></div>';
					}

	    	  	echo '</center></div>';
		  	echo '</div>';
	  	}

		if ($show != 'shared_profile') {
	        if (in_array($show, $show_groups) && !empty($group_id)) {
	        	echo GroupPortalManager::show_group_column_information($group_id, api_get_user_id(), $show);
	        }
		}
        echo '</div>';

	}



	/**
	 * Displays a sortable table with the list of online users.
	 * @param array $user_list
	 */
	public static function display_user_list($user_list,$encode=null) {
		global $charset;
		if ($_GET['id'] == '') {
			$extra_params = array();
			$course_url = '';
			if (strlen($_GET['cidReq']) > 0) {
				$extra_params['cidReq'] = Security::remove_XSS($_GET['cidReq']);
				$course_url = '&amp;cidReq='.Security::remove_XSS($_GET['cidReq']);
			}

			foreach ($user_list as $user) {
				$uid = $user[0];
				$user_info = api_get_user_info($uid);
				$table_row = array();
				//Anonymous users can't have access to the profile
				if (!api_is_anonymous()) {
					if (api_get_setting('allow_social_tool')=='true') {
						$url = api_get_path(WEB_PATH).'main/social/profile.php?u='.$uid.$course_url;
					} else {
						$url = '?id='.$uid.$course_url;
					}
				} else {
					$url = '#';
				}
				$image_array = UserManager::get_user_picture_path_by_id($uid, 'system', false, true);

				$friends_profile = SocialManager::get_picture_user($uid, $image_array['file'], 80, USER_IMAGE_SIZE_ORIGINAL );
				// reduce image
				$name = api_get_person_name($user_info['firstName'], $user_info['lastName']);

				$table_row[] = '<a href="'.$url.'"><img title = "'.$name.'" class="social-home-users-online" alt="'.$name.'" src="'.$friends_profile['file'].'" style="height:60px;" /></a>';
				//$table_row[] = '<a href="'.$url.'" style="font-size:10px;">'.api_get_person_name(cut($user_info['firstName'],15), cut($user_info['lastName'],15)).'</a>';

                                $api_get_person = api_get_person_name(cut($user_info['firstName']." ". $user_info['lastName'],25));
                                if(!is_null($encode)){ 
                                      $api_get_person = utf8_encode($api_get_person);
                                }
				if ($user_info['user_id'] != api_get_user_id()) {
				  $table_row[] = '<div style="cursor:pointer;" id="chat_'.$user_info['username'].'" class="chat_friend" ><div>'.$api_get_person.'</div>'.Display::return_icon('pixel.gif', get_lang('Chat'),array('class'=>'toolchatsocialtalk')).'</div>';
				} else {
				  $table_row[] = '<div id="chat_'.$user_info['username'].'" style="font-size:11px;">'.$api_get_person.'</div>';
				}

				$user_anonymous = api_get_anonymous_id();
				$table_data[] = $table_row;
			}
			$table_header[] = array(get_lang('UserPicture'), false, 'width="90"');

			if (api_get_setting('show_email_addresses') == 'true') {
				$table_header[] = array(get_lang('Email'), true);
			}
                                  Display::display_sortable_table($table_header, $table_data, array(), array('per_page' => 25), $extra_params, array(),'ajax_grid');        
		}
	}
	/**
	 * Displays the information of an individual user
	 * @param int $user_id
	 */
	public static function display_individual_user($user_id) {
		global $interbreadcrumb;
		$safe_user_id = Database::escape_string($user_id);

		// to prevent a hacking attempt: http://www.dokeos.com/forum/viewtopic.php?t=5363
		$user_table = Database::get_main_table(TABLE_MAIN_USER);
		$sql = "SELECT * FROM $user_table WHERE user_id='".intval($safe_user_id)."'";
		$result = Database::query($sql);
		if (Database::num_rows($result) == 1) {
			$user_object = Database::fetch_object($result);
			$name = GetFullUserName($user_id).($_SESSION['_uid'] == $user_id ? '&nbsp;<strong>('.get_lang('Me').')</strong>' : '' );
			$alt = GetFullUserName($user_id).($_SESSION['_uid'] == $user_id ? '&nbsp;('.get_lang('Me').')' : '');
			$status = ($user_object->status == COURSEMANAGER ? get_lang('Teacher') : get_lang('Student'));
			$interbreadcrumb[] = array('url' => 'whoisonline.php', 'name' => get_lang('UsersOnLineList'));
			Display::display_header($alt);
			echo '<div class="actions-title">';
			echo $alt;
			echo '</div><br />';
			echo '<div>';

			echo '<div style="margin:0 auto; width:350px; border:1px;">';
				echo '<div id="whoisonline-user-image" style="float:left; padding:5px;">';
				if (strlen(trim($user_object->picture_uri)) > 0) {
					$sysdir_array = UserManager::get_user_picture_path_by_id($safe_user_id, 'system');
					$sysdir = $sysdir_array['dir'];
					$webdir_array = UserManager::get_user_picture_path_by_id($safe_user_id, 'web');
					$webdir = $webdir_array['dir'];
					$fullurl = $webdir.$user_object->picture_uri;
					$system_image_path = $sysdir.$user_object->picture_uri;
					list($width, $height, $type, $attr) = @getimagesize($system_image_path);
					$resizing = (($height > 200) ? 'height="200"' : '');
					$height += 30;
					$width += 30;
					$window_name = 'window'.uniqid('');
					// get the path,width and height from original picture
					$big_image = $webdir.'big_'.$user_object->picture_uri;
					$big_image_size = api_getimagesize($big_image);
					$big_image_width = $big_image_size[0];
					$big_image_height = $big_image_size[1];
					$url_big_image = $big_image.'?rnd='.time();
					echo '<input type="image" src="'.$fullurl.'" alt="'.$alt.'" onclick="javascript: return show_image(\''.$url_big_image.'\',\''.$big_image_width.'\',\''.$big_image_height.'\');"/><br />';
				} else {
					echo Display::return_icon('unknown.jpg', get_lang('Unknown'));
					echo '<br />';
				}
				echo '<div style="text-align:center;padding-top:5px;">'.$status.'</div>';
				echo '</div>';

				echo '<div id="whoisonline-user-info" style="float:left; padding-left:15px;">';



				global $user_anonymous;
				if (api_get_setting('allow_social_tool') == 'true' && api_get_user_id() <> $user_anonymous && api_get_user_id() <> 0) {
					echo '<p><a href="'.api_get_path(WEB_CODE_PATH).'social/profile.php?u='.$safe_user_id.'">'.Display :: return_icon('my_shared_profile.png', get_lang('SocialInvitationToFriends'),array('height'=>'18px')).get_lang('ViewSharedProfile').'</a></p>';

					$user_anonymous = api_get_anonymous_id();

					if ($safe_user_id != api_get_user_id() && !api_is_anonymous($safe_user_id)) {
						$user_relation = SocialManager::get_relation_between_contacts(api_get_user_id(), $safe_user_id);
						if ($user_relation == 0 || $user_relation == 6) {
							echo  '<p><a href="main/messages/send_message_to_userfriend.inc.php?view_panel=2&amp;height=300&amp;width=610&amp;user_friend='.$safe_user_id.'" class="thickbox" title="'.get_lang('SendInvitation').'">'.Display :: return_icon('pixel.gif', get_lang('SocialInvitationToFriends'),array('class'=>'actionplaceholdericon actioninvitejoinfriends','height'=>'18px')).'&nbsp;'.get_lang('SendInvitation').'</a></p>
								   <p><a href="main/messages/send_message_to_userfriend.inc.php?view_panel=1&amp;height=310&amp;width=610&amp;user_friend='.$safe_user_id.'" class="thickbox" title="'.get_lang('SendAMessage').'">'.Display :: return_icon('pixel.gif', get_lang('SendAMessage'),array('class'=>'actionplaceholdericon actionmailsend','height'=>'18px')).'&nbsp;'.get_lang('SendAMessage').'</a></p>';
						} else {
							echo  '<p><a href="main/messages/send_message_to_userfriend.inc.php?view_panel=1&amp;height=310&amp;width=610&amp;user_friend='.$safe_user_id.'" class="thickbox" title="'.get_lang('SendAMessage').'">'.Display :: return_icon('pixel.gif', get_lang('SendAMessage'),array('class'=>'actionplaceholdericon actionmailsend','height'=>'18px')).'&nbsp;'.get_lang('SendAMessage').'</a></p>';
						}
					}
				}
				if (api_get_setting('show_email_addresses') == 'true') {
					echo Display::encrypted_mailto_link($user_object->email,$user_object->email).'<br />';
				}
				echo '</div>';
			echo '</div>';
			echo '</div>';
			echo '<div class="clear"></div>';
			echo '<div>';
			if ($user_object->competences) {
				echo '<dt><div class="actions-message"><strong>'.get_lang('MyCompetences').'</strong></div></dt>';
				echo '<dd>'.$user_object->competences.'</dd>';
			}
			if ($user_object->diplomas) {
				echo '<dt><div class="actions-message"><strong>'.get_lang('MyDiplomas').'</strong></div></dt>';
				echo '<dd>'.$user_object->diplomas.'</dd>';
			}
			if ($user_object->teach) {
				echo '<dt><div class="actions-message"><strong>'.get_lang('MyTeach').'</strong></div></dt>';
				echo '<dd>'.$user_object->teach.'</dd>';;
			}
			SocialManager::display_productions($user_object->user_id);
			if ($user_object->openarea) {
				echo '<dt><div class="actions-message"><strong>'.get_lang('MyPersonalOpenArea').'</strong></div></dt>';
				echo '<dd>'.$user_object->openarea.'</dd>';
			}
			echo '</div>';
		} else	{
			Display::display_header(get_lang('UsersOnLineList'));
			echo '<div class="actions-title">';
			echo get_lang('UsersOnLineList');
			echo '</div>';
		}
	}
	/**
	 * Display productions in whoisonline
	 * @param int $user_id User id
	 * @todo use the correct api_get_path instead of $clarolineRepositoryWeb
	 */
	public static function display_productions($user_id) {
		$sysdir_array = UserManager::get_user_picture_path_by_id($user_id, 'system', true);
		$sysdir = $sysdir_array['dir'].$user_id.'/';
		$webdir_array = UserManager::get_user_picture_path_by_id($user_id, 'web', true);
		$webdir = $webdir_array['dir'].$user_id.'/';
		if (!is_dir($sysdir)) {
			$perm = api_get_setting('permissions_for_new_directories');
			$perm = octdec(!empty($perm)?$perm:'0755');
			mkdir($sysdir, $perm, true);
		}
		/*
		$handle = opendir($sysdir);
		$productions = array();
		while ($file = readdir($handle)) {
			if ($file == '.' || $file == '..' || $file == '.htaccess') {
				continue;						// Skip current and parent directories
			}
			if (preg_match('/('.$user_id.'|[0-9a-f]{13}|saved)_.+\.(png|jpg|jpeg|gif)$/i', $file)) {
				// User's photos should not be listed as productions.
				continue;
			}
			$productions[] = $file;
		}
		*/
		$productions = UserManager::get_user_productions($user_id);

		if (count($productions) > 0) {
			echo '<dt><strong>'.get_lang('Productions').'</strong></dt>';
			echo '<dd><ul>';
			foreach ($productions as $index => $file) {
				// Only display direct file links to avoid browsing an empty directory
				if (is_file($sysdir.$file) && $file != $webdir_array['file']) {
					echo '<li><a href="'.$webdir.urlencode($file).'" target=_blank>'.$file.'</a></li>';
				}
				// Real productions are under a subdirectory by the User's id
				if (is_dir($sysdir.$file)) {
					$subs = scandir($sysdir.$file);
					foreach ($subs as $my => $sub) {
						if (substr($sub, 0, 1) != '.' && is_file($sysdir.$file.'/'.$sub)) {
							echo '<li><a href="'.$webdir.urlencode($file).'/'.urlencode($sub).'" target=_blank>'.$sub.'</a></li>';
						}
					}
				}
			}
			echo '</ul></dd>';
		}
	}
	/**
	 * Dummy function
	 *
	 */
	public static function get_plugins($place = SOCIAL_CENTER_PLUGIN) {
		$content = '';
		switch ($place) {
			case SOCIAL_CENTER_PLUGIN:
				$social_plugins = array(1, 2);
				if (is_array($social_plugins) && count($social_plugins)>0) {
				    $content.= '<div id="social-plugins">';
				    foreach($social_plugins as $plugin ) {
				    	$content.=  '<div class="social-plugin-item">';
				    	$content.=  $plugin;
				    	$content.=  '</div>';
				    }
				    $content.=  '</div>';
			    }
			break;
			case SOCIAL_LEFT_PLUGIN:
			break;
			case SOCIAL_RIGHT_PLUGIN:
			break;
		}
		return $content;
	}

    public static function removed_friend($friend_id) {
        $tbl_my_friend = Database :: get_main_table(TABLE_MAIN_USER_REL_USER);
        $tbl_my_message = Database :: get_main_table(TABLE_MAIN_MESSAGE);
        $user_id=api_get_user_id();
        $sql = 'SELECT COUNT(*) as count FROM ' . $tbl_my_friend . ' WHERE user_id=' . ((int)$user_id) . ' AND relation_type<>6 AND friend_user_id='.((int)$friend_id);
        $result = Database::query($sql, __FILE__, __LINE__);
        $row = Database :: fetch_array($result, 'ASSOC');
        if ($row['count'] == 1) {
                //Delete user friend
                $sql_i = 'UPDATE ' . $tbl_my_friend . ' SET relation_type=6 WHERE user_id=' . ((int)$user_id).' AND friend_user_id='.((int)$friend_id);
                $sql_j = 'UPDATE ' . $tbl_my_message . ' SET msg_status=7 WHERE user_receiver_id=' . ((int)$user_id).' AND user_sender_id='.((int)$friend_id);
                //Delete user
                $sql_ij = 'UPDATE ' . $tbl_my_friend . ' SET relation_type=6 WHERE user_id=' . ((int)$friend_id).' AND friend_user_id='.((int)$user_id);
                $sql_ji = 'UPDATE ' . $tbl_my_message . ' SET msg_status=7 WHERE user_receiver_id=' . ((int)$friend_id).' AND user_sender_id='.((int)$user_id);
                Database::query($sql_i, __FILE__, __LINE__);
                Database::query($sql_j, __FILE__, __LINE__);
                Database::query($sql_ij, __FILE__, __LINE__);
                Database::query($sql_ji, __FILE__, __LINE__);
        }
    }

    /**
     * Display chat for users online
     * @params  array   Users online
     * @return  void
     */
    public static function display_chat_useronline($user_list) {
        global $_course, $toolName;
        require_once api_get_path(LIBRARY_PATH).'usermanager.lib.php';

        // only for logged users
        if (!api_get_user_id() || empty($user_list)) {
            return false;
        }

        // avoid in some tools
        if ((defined('DOKEOS_EXERCISE') && DOKEOS_EXERCISE) || isset($toolName) && ($toolName == TOOL_AUTHOR || $toolName == TOOL_EVALUATION)) {
            return false;
        }        
        //platform_chat_request
        $platform_time = intval(api_get_setting('platform_chat_request'));
        $platform_time = ($platform_time <= 0) ? 1 : $platform_time;        
        // load required javascript libraries
        if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) {
            /* ************************************* *
             * Conflict display calendar hour/seconds 
             * echo '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/js/jquery-ui-1.8.1.custom.min.js"></script>';
             */
            echo '<link type="text/css" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-ui/css/ui-lightness/jquery-ui-1.8.1.custom.css" rel="stylesheet" />';
        }

        echo '<script type="text/javascript">
                var minChatHeartbeat = '.$platform_time.'*1000;
                var ajaxUrl = "'.api_get_path(WEB_AJAX_PATH).'online.php'.(isset($_course['id'])?'?'.api_get_cidreq().'&':'?').'";
                var btn_na  = "'.api_get_path(WEB_IMG_PATH).'chat_na.gif"; 
                var tit_na  = "'.get_lang('ConnectToChat').'";
                var btn     = "'.api_get_path(WEB_IMG_PATH).'chat.gif";                
                var tit     = "'.get_lang('DisconnectToChat').'";
                var user_status = '.(int)UserManager::is_user_chat_connected().';    
              </script>';
        echo '<script src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery_chat/chat.js" type="text/javascript" language="javascript"></script>';
//        echo '<link rel="stylesheet" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery_chat/screen.css" type="text/css" media="projection, screen" />';
//        echo '<!--[if lte IE 7]><link rel="stylesheet" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery_chat/screen_ie.css" type="text/css" media="projection, screen" /><![endif]-->';

        echo '<link rel="stylesheet" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery_chat/screen.css" type="text/css" media="projection, screen">';
        echo '<!--[if lte IE 7]><link rel="stylesheet" href="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery_chat/screen_ie.css" type="text/css" media="projection, screen"><![endif]-->';
        echo '<script type="text/javascript">
            $(document).ready(function() {
                var box = null;
                try {
                    $(".chat_friend").click(function() {

                            var data_id = $(this).attr("id");
                            var data_info = data_id.split("chat_");
                            chatWith(data_info[1]);
                            $("#chat-dialog").dialog("close");                    
                    });
                    $("#footerinner").before("<div id=\'chat_container\'>&nbsp;</div>");
                    createControl();
                    $(".usersonlineicon").css("cursor","pointer").click(function(){
                        $("#chat-dialog").dialog({modal: true, title: "'.get_lang('UsersOnline').'",width: "600px", closeText: "'.get_lang('Close').'"});
                        $("#chat-dialog .grid_nav").each(function() {
                            $(this).find("a").each(function(i) {
                                 $(this).attr("href","javascript:void(0);");
                            });
                        });
                    });
                } catch (e) {}
            });
        </script>';
        // chat
        echo '<div id="chat-dialog" style="display:none;">';
        echo '<div id="ajax-chat-dialog">';
        echo SocialManager::display_user_list($user_list);
        echo '</div>';
        echo '</div>';        
        $param_cidReq = !empty($_course['id'])?'?'.api_get_cidreq().'&':'?';
        echo '<input type="hidden" id="pathChat" value="'.api_get_path(WEB_CODE_PATH).'social/chat.php'.$param_cidReq.'" />';
    }

}
