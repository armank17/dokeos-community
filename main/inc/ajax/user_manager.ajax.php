<?php
/* For licensing terms, see /dokeos_license.txt */
/**
 * Responses to AJAX calls 
 */
$language_file = array('admin', 'registration');
require_once '../global.inc.php';
require_once api_get_path(LIBRARY_PATH).'usermanager.lib.php';
require_once api_get_path(LIBRARY_PATH).'course.lib.php';
require_once api_get_path(LIBRARY_PATH).'SimpleImage.lib.php';

$action = $_GET['a'];

switch ($action) {	
	case 'search_tags':
            if (api_is_anonymous()) {
                echo '';	
            } else {		
                $field_id = intval($_GET['field_id']);
                $tag = $_GET['tag'];
                echo UserManager::get_tags($tag, $field_id,'json','10');
            }
            break;
            
        case 'generate_api_key':
            if (api_is_anonymous()) {
                echo '';
            } else {		
                $array_list_key = array();
                $user_id = api_get_user_id();
                $api_service = 'dokeos';
                $num = UserManager::update_api_key($user_id, $api_service);
                $array_list_key = UserManager::get_api_keys($user_id, $api_service);
                ?>			
                <div class="row">
                    <div class="label"><?php echo get_lang('MyApiKey'); ?></div>
                    <div class="formw">
                    <input type="text" name="api_key_generate" id="id_api_key_generate" size="40" value="<?php echo $array_list_key[$num]; ?>"/>
                    </div>
                </div>
<?php           }
            break;
            
	case 'active_user':
            if (api_is_platform_admin()) {			
                $user_id = intval($_GET['user_id']);
                $status = intval($_GET['status']);
                if (!empty($user_id)) {
                    $user_table = Database :: get_main_table(TABLE_MAIN_USER);
                    $sql="UPDATE $user_table SET active='".$status."' WHERE user_id='".Database::escape_string($user_id)."'";
                    $result = Database::query($sql);
                    //Send and email if account is active
                    if ($status == 1) {
                        $user_info = api_get_user_info($user_id);
                        $recipient_name = api_get_person_name($user_info['firstname'], $user_info['lastname'], null, PERSON_NAME_EMAIL_ADDRESS);
                        $emailsubject = '['.api_get_setting('siteName').'] '.get_lang('YourReg').' '.api_get_setting('siteName');
                        $email_admin = api_get_setting('emailAdministrator');
                        $sender_name = api_get_person_name(api_get_setting('administratorName'), api_get_setting('administratorSurname'), null, PERSON_NAME_EMAIL_ADDRESS);
                        $emailbody=get_lang('Dear')." ".stripslashes($recipient_name).",\n\n";

                        $emailbody.=sprintf(get_lang('YourAccountOnXHasJustBeenApprovedByOneOfOurAdministrators'), api_get_setting('siteName'))."\n";
                        $emailbody.=sprintf(get_lang('YouCanNowLoginAtXUsingTheLoginAndThePasswordYouHaveProvided'), api_get_path(WEB_PATH)).",\n\n";
                        $emailbody.=get_lang('HaveFun')."\n\n";
                        //$emailbody.=get_lang('Problem'). "\n\n". get_lang('Formula');
                        $emailbody.=api_get_person_name(api_get_setting('administratorName'), api_get_setting('administratorSurname'))."\n". get_lang('Manager'). " ".api_get_setting('siteName')."\nT. ".api_get_setting('administratorTelephone')."\n" .get_lang('Email') ." : ".api_get_setting('emailAdministrator');
                        $result = api_mail($recipient_name, $user_info['mail'], $emailsubject, $emailbody, $sender_name, $email_admin);
                    }
                }
            } else {
                echo '';
            }
            break;
            
	case 'get_user_info' :
         
            
            
            $new_width=140;
            $new_height=150;
            $user_id = intval($_GET['user_id']);
            $user_info = array();
            $user_info = UserManager::get_all_user_info($user_id);
            
            $image_pathWeb = UserManager::get_user_picture_path_by_id($user_id, 'web', false, true);
            $image_path = UserManager::get_user_picture_path_by_id($user_id, 'system', false, true);
            $image_dir = $image_path['dir'];
            $image = $image_path['file'];
           
            $image_file = $image_dir . $image;
            $imge_file_web = $image_pathWeb['dir'].$image_pathWeb['file'];
            
            if($image != 'unknown.png'){
                $img = new SimpleImage(); 
                $img->load($image_file)->resize($new_width,$new_height)->save($image_file);
            }
            
            $get_user_info = $user_info[0];
            
            
            $user_table = '<span id="pic" style="text-align:center;margin-top:5px;"><img src="'.$imge_file_web.'"  /></span>';
            
                        
            if ($get_user_info['firstname'] != '') {
                $user_table .= '<span id="firstnametitle" style="height:20px;font-weight: bold;margin-left:20px;margin-top:15px;position:absolute;" class="userInformationRow" >'.get_lang('FirstName').'</span>';
                $user_table .= '<span style="height:20px;font-weight: bold;margin-left:190px;margin-top:15px;position:absolute;" class="userInformationRow" >-</span>';
                $user_table .= '<span id="firstname"style="height:20px;margin-left:220px;margin-top:15px;position:absolute;">'. api_ucfirst($get_user_info['firstname']).'</span>';
            }
            
             if ($get_user_info['lastname'] != '') {
                $user_table .= '<span id="lastnametitle" style="height:20px;font-weight: bold;margin-left:20px;margin-top:40px;position:absolute;" class="userInformationRow" >'.get_lang('LastName').'</span>';
                $user_table .= '<span style="height:20px;font-weight: bold;margin-left:190px;margin-top:40px;position:absolute;" class="userInformationRow" >-</span>';
                $user_table .= '<span id="email" style="height:20px;margin-left:220px;margin-top:40px;position:absolute;">'.api_ucfirst($get_user_info['lastname']).'</span>';
            }
            
            if ($get_user_info['status'] != '') {
                $user_table .= '<span id="statustitle" style="height:20px;margin-left:20px;font-weight: bold;margin-top:65px;position:absolute;" class="userInformationRow" >'.get_lang('Status').'</span>';
                $user_table .= '<span style="height:20px;font-weight: bold;margin-left:190px;margin-top:65px;position:absolute;" class="userInformationRow" >-</span>';
                if($get_user_info['status']==1){
                    $user_table .= '<span id="CourseAdminRole" style="margin-left:220px;height:20px;margin-top:65px;position:absolute;">'.api_ucfirst(get_lang('CourseAdminRole')).'</span>';
                }
                else if($get_user_info['status']==5){
                    $user_table .= '<span id="status" style="height:20px;margin-left:220px;margin-top:65px;position:absolute;">'.strtoupper(get_lang('Learner')).'</span>';
                }               
            }
            if ($get_user_info['email'] != '') {
                $user_table .= '<span id="emailtitle" style="height:20px;margin-left:20px;font-weight: bold;margin-top:90px;position:absolute;" class="userInformationRow" >'.get_lang('langEmail').'</span>';
                $user_table .= '<span style="height:20px;font-weight: bold;margin-left:190px;margin-top:90px;position:absolute;" class="userInformationRow" >-</span>';
                $user_table .= '<span id="email" style="height:20px;margin-left:220px;margin-top:90px;position:absolute;">'.$get_user_info['email'].'</span>';
            }
            if ($get_user_info['phone'] != '') {
                $user_table .= '<span id="phonetitle" style="height:20px;margin-left:20px;font-weight: bold;margin-top:115px;position:absolute;" class="userInformationRow" >'.get_lang('Phone').'</span>';
                $user_table .= '<span style="height:20px;font-weight: bold;margin-left:190px;margin-top:115px;position:absolute;" class="userInformationRow" >-</span>';
                $user_table .= '<span id="phone" style="height:20px;margin-left:220px;margin-top:115px;position:absolute;">'.$get_user_info['phone'].'</span>';
            }

        echo $user_table;
	break;    
        
//        case 'searchInArray' :
//           
//            $haystack = explode(",",Security::Remove_XSS($_GET['haystack']));
//           
//            $searchTxt = Security::remove_XSS($_GET['searchTxt']);
//            $coursesArray = explode(",",CourseManager::get_courses_list_in_Array($haystack,$searchTxt));            
//           
//           if($searchTxt==''){
//                $courseResult= $haystack;
//           }
//           else {
//                $courseResult= $coursesArray; 
//           
//           }
//           if($courseResult[0]!=''){
//             for($k=0;$k<count($courseResult);$k++){
//               echo '<div class=course_result>';
//               echo Display::return_icon('pixel.gif', '',array("class" => "actionplaceholdericon actionsvalidate"));
//               echo ' '.api_convert_encoding($courseResult[$k],'UTF-8',$charset).'</br>';
//               echo '</div>';
//             } 
//           }
//             else{
//               echo get_lang('NoMatchFound');
//            }
//           
//        break;
        
        case 'showAndSearchListCourses':  
            $courseList         = explode(',',Security::remove_XSS($_GET['courseList']));
            $limit              = (isset($_GET['limit']))? Security::remove_XSS($_GET['limit']):7;
            //text to search 
            $searchTxt          = Security::remove_XSS($_GET['searchTxt']);
            //I get the coincidences whit search Text in array
            $coursesArrayMatch  = explode(",",CourseManager::get_courses_list_in_Array($courseList,$searchTxt));
            //if search text is empty then I get all the course list
            $courseResult       = ($searchTxt=='')?$courseList:$coursesArrayMatch;
            $numPage            = (isset($_GET['page']))?Security::remove_XSS($_GET['page']):1;
            $isSearch           = Security::remove_XSS($_GET['search']); 
            //where I start to list (array index)
            $start              = ($isSearch=='true')?0:($numPage - 1) * $limit;            
            $finish             = $start+$limit; 
         
         if($courseResult[0]!=''){
            for($k=$start;$k<$finish;$k++){ 
                if(isset($courseResult[$k])){
                   echo '<div class=course_result>';
                   echo Display::return_icon('pixel.gif', '',array("class" => "actionplaceholdericon actionsvalidate"));
                   echo ' '. $courseResult[$k].'</br>';
                   echo '</div>';
                }
            }
            if(count($courseResult)>$limit){
                /*this print Next and Preview links*/
                $nextHml=CourseManager::paginationInCourseList($limit, count($courseList), $numPage);
                echo $nextHml;
            }
         }
         else{
               echo get_lang('NoMatchFound');
            }
            break;
    default:
        echo '';
}
exit;