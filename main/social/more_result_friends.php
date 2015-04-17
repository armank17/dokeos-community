<?php
require ('../inc/global.inc.php');
require_once('json_encode.php');
require api_get_path(LIBRARY_PATH).'usermanager.lib.php';
$start = $_GET['start'];
$action = $_GET['a'];
if(isset($_POST['search_name_q'])){ 
    $search_name = Security::remove_XSS($_POST['search_name_q']);  
}
else{
     $search_name = null;
}
$user_id       = api_get_user_id();
$tbl_my_friend = Database :: get_main_table(TABLE_MAIN_USER_REL_USER);
$tbl_my_user   = Database :: get_main_table(TABLE_MAIN_USER);

$tbl_course_rel_user         = Database::get_main_table(TABLE_MAIN_COURSE_USER);
$tbl_session_course_rel_user = Database::get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
$tbl_session_rel_user        = Database::get_main_table(TABLE_MAIN_SESSION_USER);
$tbl_session                 = Database::get_main_table(TABLE_MAIN_SESSION);
$tbl_access_url_rel_user     = Database::get_main_table(TABLE_MAIN_ACCESS_URL_REL_USER);


// access url
$access_url_id = api_get_current_access_url_id();
if($access_url_id <= 0){
   $access_url_id = 1; 
}


$limite = 27;    

$current_user_info = api_get_user_info($user_id);
$current_user_status = $current_user_info['status'];
$include_user_id_list = array();
$list_ids_friends=array();
switch ($current_user_status) {
case STUDENT :
  
 
//   $dataA['friends'] = _get_friend_list($tbl_course_rel_user, $user_id, $current_user_status,$tbl_my_user,$tbl_session_course_rel_user,$tbl_session,$tbl_session_rel_user);          
//   
//   $dataB['friends'] = get_indirect_contact_list($tbl_my_friend, $user_id, $tbl_my_user);
//
//   $data1['friends'] = array_merge( $dataA['friends'],  $dataB['friends']);
   //$data_array_intersect_keys['friends'] = array_intersect_key( $dataA['friends'],  $dataB['friends']);
   
   
     // Allow see all students that are subscribed in the courses of the teacher and allow see to students the users where he is subscribed 
    $sql3 = 'SELECT course_code FROM '.$tbl_course_rel_user.' WHERE user_id ="'.$user_id.'" AND status = "'.$current_user_status.'"';
   
    $rs3 = Database::query($sql3, __FILE__,__LINE__);
    while ($row_course1 = Database::fetch_array($rs3)) {
        $course_code = $row_course1['course_code'];
        if(isset($_POST['search_name_q'])){               
            $sql3 = 'SELECT cru.user_id,lastname,firstname,username FROM '.$tbl_course_rel_user.' cru INNER JOIN '.$tbl_my_user.' u ON cru.user_id = u.user_id WHERE cru.course_code="'.$course_code.'" AND cru.user_id <> "'.$user_id.'" AND cru.firstName LIKE "%'.Database::escape_string($search_name).'%" OR cru.lastName LIKE "%'.Database::escape_string($search_name).'%" ';
        }else{
            $sql3 = "SELECT cru.user_id,lastname,firstname,username FROM ".$tbl_course_rel_user." cru INNER JOIN ".$tbl_my_user." u ON cru.user_id = u.user_id WHERE cru.course_code='".$course_code."' AND cru.user_id <> '".$user_id."' ";
        }
        
        $rs_users = Database::query($sql3, __FILE__,__LINE__);
        
        while ($row_users = Database::fetch_array($rs_users, __FILE__, __LINE__)) {
            if (!in_array($row_users['user_id'], $include_user_id_list)) {
                $include_user_id_list[] = $row_users['user_id'];
                $path = UserManager::get_user_picture_path_by_id($row_users['user_id'],'web',false,true);
                $friends_profile = UserManager::get_picture_user($row_users['user_id'], $path['file'], 92);
                $data1['friends'][] = array('friend_user_id' => $row_users['user_id'],'firstName' => $row_users['firstname'] , 'lastName' => $row_users['lastname'], 'username' => $row_users['username'], 'image' => $friends_profile['file'],'contact_type' => 0);
            }
        }
    }

    
     if ($current_user_status == STUDENT) {
                      $current_user_status = 0;
                  } elseif ($current_user_status == COURSEMANAGER) {
                      $current_user_status = 2;
    }
                
    
    // We need get all users of session where the teacher is tutor, get all users where the users is enrolled
    $sql4 = 'SELECT course_code,id_session FROM '.$tbl_session_course_rel_user.' sru WHERE id_user = "'.$user_id.'" AND status = "'.$current_user_status.'"';
    $rs4 = Database::query($sql4, __FILE__, __LINE__);
    while ($row_session = Database::fetch_array($rs4)) {
        $course_code = $row_session['course_code'];
        $session_code = $row_session['id_session'];
        
        if(isset($_POST['search_name_q'])){
        $sql4 = 'SELECT user_id,lastname,firstname,username FROM '.$tbl_session_course_rel_user.' sru INNER JOIN '.$tbl_my_user.' u ON sru.id_user=u.user_id WHERE id_session="'.$session_code.'" AND course_code="'.$course_code.'" AND id_user <> "'.$user_id.'" AND u.firstName LIKE "%'.Database::escape_string($search_name).'%" OR u.lastName LIKE "%'.Database::escape_string($search_name).'%" ';    
            
        }
        else{
            $sql4 = 'SELECT user_id,lastname,firstname,username FROM '.$tbl_session_course_rel_user.' sru INNER JOIN '.$tbl_my_user.' u ON sru.id_user=u.user_id WHERE id_session="'.$session_code.'" AND course_code="'.$course_code.'" AND id_user <> "'.$user_id.'"';
        }
        

        $rs4 = Database::query($sql4, __FILE__, __LINE__);
        while ($row_u_sess =  Database::fetch_array($rs4)) {
            if (!in_array($row_u_sess['user_id'], $include_user_id_list)) {
                $include_user_id_list[] = $row_u_sess['user_id'];
                $path = UserManager::get_user_picture_path_by_id($row_u_sess['user_id'],'web',false,true);
                $friends_profile = UserManager::get_picture_user($row_u_sess['user_id'], $path['file'], 92);
                $data1['friends'][]  = array('friend_user_id' => $row_u_sess['user_id'],'firstName' => $row_u_sess['firstname'] , 'lastName' => $row_u_sess['lastname'], 'username' => $row_u_sess['username'], 'image' => $friends_profile['file'], 'contact_type' => 0);
            }
        }
    }
    
    
    // We need get all users of session where the teacher is global tutor, get all users where the users is enrolled
    $rs_sess = Database::query('SELECT id FROM '.$tbl_session.' WHERE id_coach = "'.$user_id.'"');
    while ($row_sess = Database::fetch_array($rs_sess)) {
        $session_code = $row_sess['id'];
        if(isset($_POST['search_name_q'])){
            $sql = 'SELECT user_id, lastname, firstname, username FROM '.$tbl_session_rel_user.' su INNER JOIN '.$tbl_my_user.' u ON su.id_user=u.user_id WHERE id_session="'.$session_code.'" AND u.firstName LIKE "%'.Database::escape_string($search_name).'%" OR u.lastName LIKE "%'.Database::escape_string($search_name).'%"';
        }
        else{
          $sql = 'SELECT user_id, lastname, firstname, username FROM '.$tbl_session_rel_user.' su INNER JOIN '.$tbl_my_user.' u ON su.id_user=u.user_id WHERE id_session="'.$session_code.'"';  
        }
        

        
        $rs_sess_user = Database::query($sql, __FILE__, __LINE__);
        while ($row_sess_user =  Database::fetch_array($rs_sess_user)) {
            if (!in_array($row_sess_user['user_id'], $include_user_id_list)) {
                $include_user_id_list[] = $row_sess_user['user_id'];
                $path = UserManager::get_user_picture_path_by_id($row_sess_user['user_id'],'web',false,true);
                $friends_profile = UserManager::get_picture_user($row_sess_user['user_id'], $path['file'], 92);
                 $data1['friends'][] = array('friend_user_id' => $row_sess_user['user_id'],'firstName' => $row_sess_user['firstname'] , 'lastName' => $row_sess_user['lastname'], 'username' => $row_sess_user['username'], 'image' => $friends_profile['file'], 'contact_type' => 0);
            }
        }
    }
    
    

            if(isset($_POST['search_name_q'])){      
        $sql2='SELECT friend_user_id FROM '.$tbl_my_friend.' WHERE relation_type NOT IN (6, 7) AND friend_user_id<>'.((int)$user_id).' AND user_id='.((int)$user_id).' AND friend_user_id IN (SELECT user_id FROM '.$tbl_my_user.' WHERE firstName LIKE "%'.Database::escape_string($search_name).'%" OR lastName LIKE "%'.Database::escape_string($search_name).'%"   OR    '.(api_is_western_name_order() ? 'concat(firstName, lastName)' : 'concat(lastName, firstName)').' like concat("%","'.Database::escape_string($search_name).'","%")    ) ';
    }
    else{       
        $sql2 ='SELECT friend_user_id FROM '.$tbl_my_friend.' WHERE relation_type NOT IN (6, 7) AND friend_user_id<>'.((int)$user_id).' AND user_id='.((int)$user_id).' ';     
    }
    $res2 = Database::query($sql2, __FILE__,__LINE__);
    
    while ($row = Database::fetch_array($res2, 'ASSOC')) {
                          if (!in_array($row['friend_user_id'], $include_user_id_list)) {
                                $include_user_id_list[] = $row['friend_user_id'];
				$path = UserManager::get_user_picture_path_by_id($row['friend_user_id'],'web',false,true);                               
                                $friends_profile = UserManager::get_picture_user($row['friend_user_id'], $path['file'], 92);                                
				$my_user_info = api_get_user_info($row['friend_user_id']);
				$data1['friends'][] = array('friend_user_id'=>$row['friend_user_id'],'firstName'=>$my_user_info['firstName'] , 'lastName'=>$my_user_info['lastName'], 'username'=>$my_user_info['username'], 'image'=>$friends_profile['file'], 'contact_type' => 1);
		
                          }
                }
    
    
    
   $total =count($data1['friends']);
  
        $data = array(
        'total' => (int) $total,
        'remainder' => NULL,
        'friends' => array()
    );
    
        
                   if(!isset($_GET['start'])){
                $start = 0;
            }
            else{
                $start= $_GET['start'];
            }
            $limite = $limite + $start;
            
            $data['remainder'] = ($total - $limite );
            if($data['remainder'] < 0){
                $limite = $total;
            }

            for ($i = $start ; $i <= $limite -1; $i++) 
                {
                 $data['friends'][] = $data1['friends'][$i];
                }
                
                $data['remainder'] = ($total - $limite );
            if($data['remainder'] < 0){
                $limite = $total;
            }
        
    
    
    if($action == 'show_my_friends' ){
        foreach($data1['friends'] as $friend){
            
            $dato .= "";
            
            $dato .='<div onmouseover="show_icon_delete(this)" onmouseout="hide_icon_delete(this)" class="image-social-content" id="div_'.$friend['friend_user_id'].'">
            <center> 
                <a href="profile.php?u='.$friend['friend_user_id'].'">
                    <img style="height: 60px; border: 3pt solid rgb(238, 238, 238);" id="imgfriend_'.$friend['friend_user_id'].'" title="'.$friend['firstName'].' '.$friend['lastName'].'" src="'.$friend['image'].'">
                    </img>
                </a>
            </center>
            <img class="image-delete" title="" alt="" src="../img/blank.gif" onclick="delete_friend (this)" id="img_'.$friend['friend_user_id'].'">
            <center class="friend">
                <a href="profile.php?u='.$friend['friend_user_id'].'">
                    <div>'.$friend['firstName'].'</div>
                    <div>'.$friend['lastName'].'</div>
                </a>
            </center>
            </div>';           
        }
        
        echo $dato;
    }else{
        print json_encode($data);
    }
    
break;
case COURSEMANAGER :
    if (api_is_platform_admin()) {
            $sql2 = 'SELECT u.user_id, u.lastname, u.firstname, u.username FROM '.$tbl_my_user.' u INNER JOIN '. $tbl_access_url_rel_user .' u_url ON(u.user_id = u_url.user_id)'
                    . ' WHERE u.status <> 6 AND u.user_id <> "'. Database::escape_string($user_id).'" '
                    . ' AND u_url.access_url_id = '. $access_url_id .' ';
            if(isset($_POST['search_name_q'])){ 
                $sql2 .= ' AND (lastname like "%'.$search_name.'%" OR firstname like "%'.$search_name.'%")';
            }
            
            $res2 = Database::query($sql2, __FILE__,__LINE__);
            while ($row_user = Database::fetch_array($res2)) {
                if (!in_array($row_user['user_id'], $include_user_id_list)) {
                $include_user_id_list[] = $row_user['user_id'];
                $path = UserManager::get_user_picture_path_by_id($row_user['user_id'], 'web', false, true);
                $friends_profile = UserManager::get_picture_user($row_user['user_id'], $path['file'], 92);

                $data1['friends'][] = array('friend_user_id' => $row_user['user_id'],'firstName' => $row_user['firstname'] , 'lastName' => $row_user['lastname'], 'username' => $row_user['username'],'image' => $friends_profile['file'],'contact_type' => 0);
                }
            }

            $total = count($data1['friends']);
            $data  = array(
                'total' => (int) $total,
                'remainder' => NULL,
                'friends' => array()
            );
                
            if(!isset($_GET['start'])){
                $start = 0;
            } else {
                $start= $_GET['start'];
            }
            $limite = $limite + $start;
            
            $data['remainder'] = ($total - $limite);
            if($data['remainder'] < 0){
                $limite = $total;
            }

            for ($i = $start ; $i <= $limite -1; $i++) {
                $data['friends'][] = $data1['friends'][$i];
            }
            $data['remainder'] = ($total - $limite);

            
            if($action == 'show_my_friends') {
                foreach($data1['friends'] as $friend) {
                    $dato .= "";
                    $dato .='<div onmouseover="show_icon_delete(this)" onmouseout="hide_icon_delete(this)" class="image-social-content" id="div_'.$friend['friend_user_id'].'">
                    <center> 
                        <a href="profile.php?u='.$friend['friend_user_id'].'">
                            <img style="height: 60px; border: 3pt solid rgb(238, 238, 238);" id="imgfriend_'.$friend['friend_user_id'].'" title="'.$friend['firstName'].' '.$friend['lastName'].'" src="'.$friend['image'].'">
                            </img>
                        </a>
                    </center>
                    <img class="image-delete" title="" alt="" src="../img/blank.gif" onclick="delete_friend (this)" id="img_'.$friend['friend_user_id'].'">
                    <center class="friend">
                        <a href="profile.php?u='.$friend['friend_user_id'].'">
                            <div>'.$friend['firstName'].'</div>
                            <div>'.$friend['lastName'].'</div>
                        </a>
                    </center>
                    </div>';           
                }
                echo $dato;
            } else {
                print json_encode($data);
            }
        
    }else{
         
        $sql1 = 'SELECT course_code FROM '.$tbl_course_rel_user.' WHERE user_id ="'.$user_id.'" AND status = "'.$current_user_status.'"';
                    $rs1 = Database::query($sql1, __FILE__,__LINE__);
                    while ($row_course = Database::fetch_array($rs1)) {
                        $course_code = $row_course['course_code'];
                        
                         if(isset($_POST['search_name_q'])){ 
                             
                             $sql1 = 'SELECT cru.user_id,lastname,firstname,username FROM '.$tbl_course_rel_user.' cru INNER JOIN '.$tbl_my_user.' u ON cru.user_id = u.user_id WHERE cru.course_code="'.$course_code.'" AND cru.user_id <> "'.$user_id.'" AND (u.lastname like "%'.$search_name.'%" OR u.firstname like "%'.$search_name.'%") ';
                         }
                         else{
                             $sql1 = "SELECT cru.user_id,lastname,firstname,username FROM ".$tbl_course_rel_user." cru INNER JOIN ".$tbl_my_user." u ON cru.user_id = u.user_id WHERE cru.course_code='".$course_code."' AND cru.user_id <> '".$user_id."' "; 
                         }
                        
                        
//                        
                        $rs_users = Database::query($sql1, __FILE__,__LINE__);
                        // Allow see all students that are subscribed in the courses of the teacher and allow see to students the users where he is subscribed
                        while ($row_users = Database::fetch_array($rs_users, __FILE__, __LINE__)) {
                            if (!in_array($row_users['user_id'], $include_user_id_list)) {
                              $include_user_id_list[] = $row_users['user_id'];
                              $path = UserManager::get_user_picture_path_by_id($row_users['user_id'],'web',false,true);
                              $friends_profile = UserManager::get_picture_user($row_users['user_id'], $path['file'], 92);
                              $data1['friends'][] = array('friend_user_id' => $row_users['user_id'],'firstName' => $row_users['firstname'] , 'lastName' => $row_users['lastname'], 'username' => $row_users['username'], 'image' => $friends_profile['file'],'contact_type' => 0);
                          }
                      }
                  }
                  
                  if ($current_user_status == STUDENT) {
                      $current_user_status = 0;
                  } elseif ($current_user_status == COURSEMANAGER) {
                      $current_user_status = 2;
                  }
                  
                  // We need get all users of session where the teacher is tutor, get all users where the users is enrolled
                  $sql2 = 'SELECT course_code,id_session FROM '.$tbl_session_course_rel_user.' sru WHERE id_user = "'.$user_id.'" AND status = "'.$current_user_status.'"';
                  $rs2 = Database::query($sql2, __FILE__, __LINE__);
                  while ($row_session = Database::fetch_array($rs2)) {
                      $course_code = $row_session['course_code'];
                      $session_code = $row_session['id_session'];
                      if(isset($_POST['search_name_q'])){
                        $sql3 = 'SELECT user_id,lastname,firstname,username FROM '.$tbl_session_course_rel_user.' sru INNER JOIN '.$tbl_my_user.' u ON sru.id_user=u.user_id WHERE id_session="'.$session_code.'" AND course_code="'.$course_code.'" AND id_user <> "'.$user_id.'" AND (u.lastname like "%'.$search_name.'%" OR u.firstname like "%'.$search_name.'%") ';  
                      }
                      else{
                         $sql3 = 'SELECT user_id,lastname,firstname,username FROM '.$tbl_session_course_rel_user.' sru INNER JOIN '.$tbl_my_user.' u ON sru.id_user=u.user_id WHERE id_session="'.$session_code.'" AND course_code="'.$course_code.'" AND id_user <> "'.$user_id.'"'; 
                      }
                      
                      

                      $rs2 = Database::query($sql3, __FILE__, __LINE__);
                      while ($row_u_sess =  Database::fetch_array($rs)) {
                          if (!in_array($row_u_sess['user_id'], $include_user_id_list)) {
                              $include_user_id_list[] = $row_u_sess['user_id'];
                              $path = UserManager::get_user_picture_path_by_id($row_u_sess['user_id'],'web',false,true);
                              $friends_profile = UserManager::get_picture_user($row_u_sess['user_id'], $path['file'], 92);
                              $data1['friends'][] = array('friend_user_id' => $row_u_sess['user_id'],'firstName' => $row_u_sess['firstname'] , 'lastName' => $row_u_sess['lastname'], 'username' => $row_u_sess['username'], 'image' => $friends_profile['file'], 'contact_type' => 0);
                          }
                      }
                  }
                  
                  // We need get all users of session where the teacher is global tutor, get all users where the users is enrolled
                  $rs_sess = Database::query('SELECT id FROM '.$tbl_session.' WHERE id_coach = "'.$user_id.'"');
                  while ($row_sess = Database::fetch_array($rs_sess)) {
                      $session_code = $row_sess['id'];
                      if(isset($_POST['search_name_q'])){
                          $sql4 = 'SELECT user_id, lastname, firstname, username FROM '.$tbl_session_rel_user.' su INNER JOIN '.$tbl_my_user.' u ON su.id_user=u.user_id WHERE id_session="'.$session_code.'" AND (u.lastname like "%'.$search_name.'%" OR u.firstname like "%'.$search_name.'%") ';
                      }else{
                          $sql4 = 'SELECT user_id, lastname, firstname, username FROM '.$tbl_session_rel_user.' su INNER JOIN '.$tbl_my_user.' u ON su.id_user=u.user_id WHERE id_session="'.$session_code.'"';
                      }
                      
                      $rs_sess_user = Database::query($sql4, __FILE__, __LINE__);
                      while ($row_sess_user =  Database::fetch_array($rs_sess_user)) {
                          if (!in_array($row_sess_user['user_id'], $include_user_id_list)) {
                              $include_user_id_list[] = $row_sess_user['user_id'];
                              $path = UserManager::get_user_picture_path_by_id($row_sess_user['user_id'],'web',false,true);
                              $friends_profile = UserManager::get_picture_user($row_sess_user['user_id'], $path['file'], 92);
                              $data1['friends'][] = array('friend_user_id' => $row_sess_user['user_id'],'firstName' => $row_sess_user['firstname'] , 'lastName' => $row_sess_user['lastname'], 'username' => $row_sess_user['username'], 'image' => $friends_profile['file'], 'contact_type' => 0);
                          }
                      }
                  }
                  
                  
                  ////////////
                if(isset($_POST['search_name_q'])){ 

                $sql5='SELECT friend_user_id FROM '.$tbl_my_friend.' WHERE relation_type NOT IN (6, 7) AND friend_user_id<>'.((int)$user_id).' AND user_id='.((int)$user_id).' AND friend_user_id IN (SELECT user_id FROM '.$tbl_my_user.' WHERE firstName LIKE "%'.Database::escape_string($search_name).'%" OR lastName LIKE "%'.Database::escape_string($search_name).'%"   OR    '.(api_is_western_name_order() ? 'concat(firstName, lastName)' : 'concat(lastName, firstName)').' like concat("%","'.Database::escape_string($search_name).'","%")    ) ';
                //$sql2 = $format; 

                }
                else{       
                $sql5 = 'SELECT friend_user_id FROM '.$tbl_my_friend.' WHERE relation_type NOT IN (6, 7) AND friend_user_id<>'.((int)$user_id).' AND user_id='.((int)$user_id).' ';  
                //$sql2 = sprintf($format, $start, $limite);     
                }
    $res3 = Database::query($sql5, __FILE__,__LINE__);
    
    while ($row = Database::fetch_array($res3, 'ASSOC')) {
                          if (!in_array($row['friend_user_id'], $include_user_id_list)) {
                                $include_user_id_list[] = $row['friend_user_id'];
				$path = UserManager::get_user_picture_path_by_id($row['friend_user_id'],'web',false,true);                               
                                $friends_profile = UserManager::get_picture_user($row['friend_user_id'], $path['file'], 92);                                
				$my_user_info = api_get_user_info($row['friend_user_id']);
				$data1['friends'][] = array('friend_user_id'=>$row['friend_user_id'],'firstName'=>$my_user_info['firstName'] , 'lastName'=>$my_user_info['lastName'], 'username'=>$my_user_info['username'], 'image'=>$friends_profile['file'], 'contact_type' => 1);
		
                          }
                }
        ////////////////
                $total =count($data1['friends']);
  
        $data = array(
        'total' => (int) $total,
        'remainder' => NULL,
        'friends' => array()
    );
                
                
                
                if(!isset($_GET['start'])){
                $start = 0;
            }
            else{
                $start= $_GET['start'];
            }
            $limite = $limite + $start;
            
            $data['remainder'] = ($total - $limite );
            if($data['remainder'] < 0){
                $limite = $total;
            }

            for ($i = $start ; $i <= $limite -1; $i++) 
                {
                 $data['friends'][] = $data1['friends'][$i];
                }
                
                $data['remainder'] = ($total - $limite );
                
                
        if($action == 'show_my_friends' ){
                foreach($data1['friends'] as $friend){

                    $dato .= "";

                    $dato .='<div onmouseover="show_icon_delete(this)" onmouseout="hide_icon_delete(this)" class="image-social-content" id="div_'.$friend['friend_user_id'].'">
                    <center> 
                        <a href="profile.php?u='.$friend['friend_user_id'].'">
                            <img style="height: 60px; border: 3pt solid rgb(238, 238, 238);" id="imgfriend_'.$friend['friend_user_id'].'" title="'.$friend['firstName'].' '.$friend['lastName'].'" src="'.$friend['image'].'">
                            </img>
                        </a>
                    </center>
                    <img class="image-delete" title="" alt="" src="../img/blank.gif" onclick="delete_friend (this)" id="img_'.$friend['friend_user_id'].'">
                    <center class="friend">
                        <a href="profile.php?u='.$friend['friend_user_id'].'">
                            <div>'.$friend['firstName'].'</div>
                            <div>'.$friend['lastName'].'</div>
                        </a>
                    </center>
                    </div>';           
                }

                echo $dato;
            }else{                      
                
                
            print json_encode($data);
            
            }

    }
   
    
    
    
break;
}


function _get_friend_list($tbl_course_rel_user, $user_id, $current_user_status,$tbl_my_user,$tbl_session_course_rel_user,$tbl_session,$tbl_session_rel_user){
     // Allow see all students that are subscribed in the courses of the teacher and allow see to students the users where he is subscribed 
    $sql3 = 'SELECT course_code FROM '.$tbl_course_rel_user.' WHERE user_id ="'.$user_id.'" AND status = "'.$current_user_status.'"';
   
    $rs3 = Database::query($sql3, __FILE__,__LINE__);
    while ($row_course1 = Database::fetch_array($rs3)) {
        $course_code = $row_course1['course_code'];
        if(isset($_POST['search_name_q'])){               
            $sql3 = 'SELECT cru.user_id,lastname,firstname,username FROM '.$tbl_course_rel_user.' cru INNER JOIN '.$tbl_my_user.' u ON cru.user_id = u.user_id WHERE cru.course_code="'.$course_code.'" AND cru.user_id <> "'.$user_id.'" AND cru.firstName LIKE "%'.Database::escape_string($search_name).'%" OR cru.lastName LIKE "%'.Database::escape_string($search_name).'%" ';
        }else{
            $sql3 = "SELECT cru.user_id,lastname,firstname,username FROM ".$tbl_course_rel_user." cru INNER JOIN ".$tbl_my_user." u ON cru.user_id = u.user_id WHERE cru.course_code='".$course_code."' AND cru.user_id <> '".$user_id."' ";
        }
        
        $rs_users = Database::query($sql3, __FILE__,__LINE__);
        
        while ($row_users = Database::fetch_array($rs_users, __FILE__, __LINE__)) {
            if (!in_array($row_users['user_id'], $include_user_id_list)) {
                $include_user_id_list[] = $row_users['user_id'];
                $path = UserManager::get_user_picture_path_by_id($row_users['user_id'],'web',false,true);
                $friends_profile = UserManager::get_picture_user($row_users['user_id'], $path['file'], 92);
                $data1['friends'][] = array('friend_user_id' => $row_users['user_id'],'firstName' => $row_users['firstname'] , 'lastName' => $row_users['lastname'], 'username' => $row_users['username'], 'image' => $friends_profile['file'],'contact_type' => 0);
            }
        }
    }

    
    if ($current_user_status == STUDENT) {
        $current_user_status = 0;
    } elseif ($current_user_status == COURSEMANAGER) {
        $current_user_status = 2;
    }
                
    
    // We need get all users of session where the teacher is tutor, get all users where the users is enrolled
    $sql4 = 'SELECT course_code,id_session FROM '.$tbl_session_course_rel_user.' sru WHERE id_user = "'.$user_id.'" AND status = "'.$current_user_status.'"';
    $rs4 = Database::query($sql4, __FILE__, __LINE__);
    while ($row_session = Database::fetch_array($rs4)) {
        $course_code = $row_session['course_code'];
        $session_code = $row_session['id_session'];
        
        if(isset($_POST['search_name_q'])){
            $sql4 = 'SELECT user_id,lastname,firstname,username FROM '.$tbl_session_course_rel_user.' sru INNER JOIN '.$tbl_my_user.' u ON sru.id_user=u.user_id WHERE id_session="'.$session_code.'" AND course_code="'.$course_code.'" AND id_user <> "'.$user_id.'" AND u.firstName LIKE "%'.Database::escape_string($search_name).'%" OR u.lastName LIKE "%'.Database::escape_string($search_name).'%" ';
        } else {
            $sql4 = 'SELECT user_id,lastname,firstname,username FROM '.$tbl_session_course_rel_user.' sru INNER JOIN '.$tbl_my_user.' u ON sru.id_user=u.user_id WHERE id_session="'.$session_code.'" AND course_code="'.$course_code.'" AND id_user <> "'.$user_id.'"';
        }

        $rs4 = Database::query($sql4, __FILE__, __LINE__);
        while ($row_u_sess =  Database::fetch_array($rs4)) {
            if (!in_array($row_u_sess['user_id'], $include_user_id_list)) {
                $include_user_id_list[] = $row_u_sess['user_id'];
                $path = UserManager::get_user_picture_path_by_id($row_u_sess['user_id'],'web',false,true);
                $friends_profile = UserManager::get_picture_user($row_u_sess['user_id'], $path['file'], 92);
                $data1['friends'][]  = array('friend_user_id' => $row_u_sess['user_id'],'firstName' => $row_u_sess['firstname'] , 'lastName' => $row_u_sess['lastname'], 'username' => $row_u_sess['username'], 'image' => $friends_profile['file'], 'contact_type' => 0);
            }
        }
    }
    
    
    // We need get all users of session where the teacher is global tutor, get all users where the users is enrolled
    $rs_sess = Database::query('SELECT id FROM '.$tbl_session.' WHERE id_coach = "'.$user_id.'"');
    while ($row_sess = Database::fetch_array($rs_sess)) {
        $session_code = $row_sess['id'];
        if(isset($_POST['search_name_q'])){
            $sql = 'SELECT user_id, lastname, firstname, username FROM '.$tbl_session_rel_user.' su INNER JOIN '.$tbl_my_user.' u ON su.id_user=u.user_id WHERE id_session="'.$session_code.'" AND u.firstName LIKE "%'.Database::escape_string($search_name).'%" OR u.lastName LIKE "%'.Database::escape_string($search_name).'%"';
        }
        else{
            $sql = 'SELECT user_id, lastname, firstname, username FROM '.$tbl_session_rel_user.' su INNER JOIN '.$tbl_my_user.' u ON su.id_user=u.user_id WHERE id_session="'.$session_code.'"';  
        }
        
        $rs_sess_user = Database::query($sql, __FILE__, __LINE__);
        while ($row_sess_user =  Database::fetch_array($rs_sess_user)) {
            if (!in_array($row_sess_user['user_id'], $include_user_id_list)) {
                $include_user_id_list[] = $row_sess_user['user_id'];
                $path = UserManager::get_user_picture_path_by_id($row_sess_user['user_id'],'web',false,true);
                $friends_profile = UserManager::get_picture_user($row_sess_user['user_id'], $path['file'], 92);
                $data1['friends'][] = array('friend_user_id' => $row_sess_user['user_id'],'firstName' => $row_sess_user['firstname'] , 'lastName' => $row_sess_user['lastname'], 'username' => $row_sess_user['username'], 'image' => $friends_profile['file'], 'contact_type' => 0);
            }
        }
    }
    return $data1['friends'];
    }
    
    
    function get_indirect_contact_list($tbl_my_friend, $user_id, $tbl_my_user){
        if(isset($_POST['search_name_q'])){      
            $sql2='SELECT friend_user_id FROM '.$tbl_my_friend.' WHERE relation_type NOT IN (6, 7) AND friend_user_id<>'.((int)$user_id).' AND user_id='.((int)$user_id).' AND friend_user_id IN (SELECT user_id FROM '.$tbl_my_user.' WHERE firstName LIKE "%'.Database::escape_string($search_name).'%" OR lastName LIKE "%'.Database::escape_string($search_name).'%"   OR    '.(api_is_western_name_order() ? 'concat(firstName, lastName)' : 'concat(lastName, firstName)').' like concat("%","'.Database::escape_string($search_name).'","%")    ) ';
        } else {
            $sql2 ='SELECT friend_user_id FROM '.$tbl_my_friend.' WHERE relation_type NOT IN (6, 7) AND friend_user_id<>'.((int)$user_id).' AND user_id='.((int)$user_id).' ';     
        }
        
        $res2 = Database::query($sql2, __FILE__,__LINE__);
        while ($row = Database::fetch_array($res2, 'ASSOC')) {
            if (!in_array($row['friend_user_id'], $include_user_id_list)) {
                $include_user_id_list[] = $row['friend_user_id'];
                $path = UserManager::get_user_picture_path_by_id($row['friend_user_id'],'web',false,true);                               
                $friends_profile = UserManager::get_picture_user($row['friend_user_id'], $path['file'], 92);                                
                $my_user_info = api_get_user_info($row['friend_user_id']);
                $data1['friends'][] = array('friend_user_id'=>$row['friend_user_id'],'firstName'=>$my_user_info['firstName'] , 'lastName'=>$my_user_info['lastName'], 'username'=>$my_user_info['username'], 'image'=>$friends_profile['file'], 'contact_type' => 1);
            }
        }
        return $data1['friends'];
    }