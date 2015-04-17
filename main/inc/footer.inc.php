<?php 
/* For licensing terms, see /dokeos_license.txt */

/**
==============================================================================
*	This script displays the footer that is below (almost)
*	every Dokeos web page.
*
*	@package dokeos.include
==============================================================================
*/
/**** display of tool_navigation_menu according to admin setting *****/
require_once (api_get_path(LIBRARY_PATH).'course.lib.php');
require_once api_get_path(LIBRARY_PATH).'usermanager.lib.php';
global $_course, $_user, $toolName;

if ($GLOBALS['learner_view']) {
    $_SESSION['viewasstudent'] = "YES";
} else {
    $_SESSION['viewasstudent'] = "NO";
}

if (api_get_setting('show_navigation_menu') != 'false') {
    $course_id = api_get_course_id();
    if (!empty($course_id) && ($course_id != -1)) {
        if ( api_get_setting('show_navigation_menu') != 'icons') {
            echo '</div> <!-- end #center -->';
            echo '</div> <!-- end #centerwrap -->';
        }
        require_once api_get_path(INCLUDE_PATH).'tool_navigation_menu.inc.php';
        show_navigation_menu();
    }
}
/***********************************************************************/
?>
            <div class="clear">&nbsp;</div> <!-- 'clearing' div to make sure that footer stays below the main and right column sections -->
        </div> <!-- end of #main" started at the end of banner.inc.php -->
        <div class="push"></div>
        </div><!--  end of #wrapper section -->

        </div> <!-- close the wrapper-->

<?php 
    if (isset($_course) && !isset($GLOBALS['course_home_visibility_type'])) {
        $display_footer = "style='display:none;'";
        $Stop_chat = true;
    }
    $footer = 'footer';
    $footerLink = 'footer-link';
    $menuLinks = (function_exists(get_menu_links) ? get_menu_links('footer') : array());      
    if(count($menuLinks) > 0):
    if (count($menuLinks>0)){
            echo '<style>#wrapper {margin-bottom: -91px;    min-height: 100%;} #wrapper:after {margin-top: 81px !important;}</style>';
    }  
    $display_footer = "";
    if (isset($_course) && !isset($GLOBALS['course_home_visibility_type'])) {
        $display_footer = "style='display:none;'";
            echo '<style>#wrapper {    margin-bottom: -55px;    min-height: 100%;} #wrapper:after {margin-top: 44px !important;} .wrapper-footer, #wrapper:after{height:1px;}</style>';
        }
?>
        
        <div class="sticky-footer" >    
              <div id="<?php echo $footerLink ?>">
                  <div id="dokeostabs" style="width:960px">
                        <a id="prev3" class="prevbar" href="#"></a>
                        <a id="next3" class="nextbar" href="#"></a>

                      <div class="list_carousel">
                        <div class="footer-link-wrapper ">
                            <ul id="foo3">
                  <?php             
                      /* footer menu links */
                      foreach($menuLinks as $menuLink):
                  ?>
                          <li class="tab_link"><a href="<?php echo $menuLink['link_path']; ?>" target="<?php echo $menuLink['target']; ?>"><?php echo $menuLink['title']; ?></a></li>
                  <?php
                          //endif;
                      endforeach;
                  ?>
                  </ul>
                      <div class="clear"></div>
                  </div>
                      </div>
                  </div>
                  
                  <div class="clear"></div>
              </div>
</div><!-- end stiky footer  -->

              <?php endif; ?>
        
           <div class="wrapper-footer">         
  <div id="<?php echo $footer ?>" class="custom-footer"<?php echo $display_footer; ?>> <!-- start of #footer section -->
           <div id="footerinner">
                <div id="bottom_corner"></div>
                <div class="copyright">
<?php
        /*--- show Copyright ---*/
        showCopyright();        
?>
                </div>
<?php
/*
-----------------------------------------------------------------------------
	Plugins for footer section
-----------------------------------------------------------------------------
*/
api_plugin('footer');
        /*---Show Platform manager ---*/
        /* conclict
        showPlatformmanager();
         */

        if (api_get_setting('show_administrator_data')=='true') {        
            $manager_lang_var = api_convert_encoding(get_lang('Manager'), $charset, api_get_system_encoding());
            $emailAdministration = Display::encrypted_mailto_link(api_get_setting('emailAdministrator'), api_get_person_name(api_get_setting('administratorName'), api_get_setting('administratorSurname')));

            // Platform manager
            echo '<span id="platformmanager">'.$manager_lang_var . ' : ' . $emailAdministration . '</span>';
        }
        /*---Show Course manager Tutor ---*/
        /* conclict
        showCoursemanagerTutor();
        */
        if (api_get_setting('show_tutor_data') == 'true') {
        $coachs_lang_var = api_convert_encoding(get_lang('Coachs'), $charset, api_get_system_encoding());
        $coach_lang_var = api_convert_encoding(get_lang('Coach'), $charset, api_get_system_encoding());
        $id_session = api_get_session_id();
        $id_course = api_get_course_id();    
        if (isset($id_course) && $id_course != -1) {


            if ($id_session != 0) {

                $coachs = CourseManager::get_email_of_tutor_to_session($id_session, $id_course);
                if (!empty($coachs)) {
                    echo '<span class="coursemanager ">';
                    if (count($coachs) > 1) {
                        echo $coachs_lang_var . ' : <span style="font-weight: 400;">' . count($coachs) . '</span> <img src="' . api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets') . '/images/action/online.png" id="view_coachs" alt="" style="cursor: pointer; position: absolute; top: -8px;" />';
                    } else {
                        echo $coach_lang_var . ' : ';
                        foreach ($coachs as $coach) {
                            foreach ($coach as $email => $name) {
                                echo Display::encrypted_mailto_link($email, $name);
                            }
                        }
                    }
                    echo '</span>';
                }

            }

        }
        }
    
        /*---Show Course manager Teacher ---*/
        /* conflict
        showCoursemanagerTeacher();
        */
        if (api_get_setting('show_teacher_data') == 'true') {
            $teachers_lang_var = api_convert_encoding(get_lang('Teachers'), $charset, api_get_system_encoding());
            $teacher_lang_var = api_convert_encoding(get_lang('Teacher'), $charset, api_get_system_encoding());
            $id_course = api_get_course_id();
            if (isset($id_course) && $id_course != -1) {
                $coursemanager_space = isset($coachs) && count($coachs) > 1 ? ' style="margin-left: 40px;"' : '';
                echo '<span class="coursemanager"' . $coursemanager_space . '>';
                $teachers = CourseManager::get_emails_of_tutors_to_course($id_course);
                if (!empty($teachers)) {
                    if (count($teachers) > 1) {
                        echo $teachers_lang_var . ' : <span style="font-weight: 400;">' . count($teachers) . '</span> <img src="' . api_get_path(WEB_CSS_PATH) . api_get_setting('stylesheets') . '/images/action/trainer_icons.png" id="view_teachers" alt="" style="cursor: pointer; position: absolute; top: -5px;" />';
                    } else {
                        echo $teacher_lang_var . ' : ';
                        foreach ($teachers as $teacher) {
                            foreach ($teacher as $email => $name) {
                                echo Display::encrypted_mailto_link($email, $name);
                            }
                        }
                    }
                }
                echo '</span>';
            }
        }
    
        /*--- users online ---*/
        /*conclict
        showUsersonline();
        */
        $usersonline_lang_var = api_convert_encoding(get_lang('UsersOnline'), $charset, api_get_system_encoding());
        if ((api_get_setting('showonline', 'world') == 'true' && !$_user['user_id']) || ((api_get_setting('showonline', 'users') == 'true' || api_get_setting('showonline', 'course') == 'true') && $_user['user_id'])) {
            $chatstatus_img = 'chat_na.gif';
            $ico_status = 'ico_chat_na';
            $chatstatus_msg = get_lang('ConnectToChat');
            if (UserManager::is_user_chat_connected($_user['user_id'])) {
                $chatstatus_img = 'chat.gif';
                $ico_status = 'ico_chat';
                $chatstatus_msg = get_lang('DisconnectToChat');
            }
            $user_list = WhoIsOnline($_user['user_id'], $_configuration['statistics_database'], api_get_setting('time_limit_whosonline'));
            if ($_SESSION['_user']['user_id'] != 2) {
                echo '<span class="usersonline"><span class="usersonlinetitle">'. $usersonline_lang_var . ' : </span><span class="usersonlinecontent">'.count($user_list).'</span><span class="usersonlineicon">&nbsp;</span>';
                if (api_get_user_id()) {
                    echo '<span id="chat-connect"  class="chatstatus '.$ico_status.'"></span>';
                }
                echo '</span>';
            }
        }
    
// display chat
$device_info = api_get_navigator();
$device = $device_info['device'];
$get_machine = $device['machine'];
        $mobile_device = ($get_machine == 'ipad' || $get_machine == 'android' || $get_machine == 'iphone') ? true : false;

        
        ?>
                </div>
            </div> <!-- end of #footer -->
           </div>
  
  <div>
      <script type="text/javascript">
            $(document).ready(function(){
                <?php
                $tablename_page_nr = '""';
                if (isset($_GET['tablename_page_nr']) && $_GET['tablename_page_nr'] != '') {
                    $tablename_page_nr = $_GET['tablename_page_nr'];
                }
                ?>
                var tablename_page_nr = <?php echo $tablename_page_nr; ?>;
                if (tablename_page_nr !== '') {
                    $('#view_teachers').click();
                }
            });
        </script>
        <?php
        if($Stop_chat==false){
            require_once api_get_path(LIBRARY_PATH).'social.lib.php';
            if (api_get_setting('enable_platform_chat')=='true' && $mobile_device === false) {
                $_SESSION['chat_username'] = $_user['username'];
                $user_list = WhoIsOnline($_user['user_id'], $_configuration['statistics_database'], api_get_setting('time_limit_whosonline'));
                SocialManager::display_chat_useronline($user_list);
            }
            if (isset($coachs) && count($coachs) > 1) {
                display_tutors_list($coachs, 'coachs');
            }
            if (isset($teachers) && count($teachers) > 1) {
                display_tutors_list($teachers, 'teachers');
            }
            }
        ?>
  </div>           
    <?php 
     if( isset($htmlFootXtra) && $htmlFootXtra )
     {
         foreach($htmlFootXtra as $this_html_foot)
         {
                 echo($this_html_foot);
         }
     }
    ?>
    </body>
</html>
