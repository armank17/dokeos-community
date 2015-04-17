<?php
//require library xajax - "/var/www/dokeos/main/inc/lib/" 
//require_once api_get_path(LIBRARY_PATH).'xajax_0.5/xajax_core/xajax.inc.php';
class MobileController extends Controller {	
    
    private $title;
    private $model;
    
    public function __construct() {
        
        // we load the model object
        $this->model = new MobileModel();
        
        $this->title = 'title testing';
        $this->view = new View('mobile');
        $this->view->set_layout('layout');
        
    }
    
    /*private function LoadXajax()
    {
        $this->xajax = new xajax();
        //$this->xajax->setCharEncoding('ISO-8859-1');
        $this->xajax->setFlag("debug", false);
        $this->xajax->register(XAJAX_FUNCTION, array('pruebita', $this, 'pruebita'));
        $this->xajax->processRequest();
        return $this->xajax;

    } */    

    
    /**
     * function to load the login view 
     */
    
    public function login()
    {
        $data = array();
        $data['message'] = 'open mobile';
        //get HTML head by name action
        $data['head'] = $this->getHtmlHeadMobile(__FUNCTION__);
        //get HTML footer by name action
        $data['foot'] = $this->getHtmlFooterMobile(__FUNCTION__);         
        
        //$data['xajax'] = $this->LoadXajax();
        if (isset($_GET['init']) && $_GET['init'] == 'mobile') {
             $data['fromMobile'] = 'mobile';
        }
        // render to the view
        $this->view->set_data($data);
        $this->view->set_template('login');
        $this->view->render();        
    }
    
    public function logout()
    {
        //call api logout
        api_session_destroy();
        $data = array();
        $data['message'] = 'open mobile';
        //get HTML head by name action
        $data['head'] = $this->getHtmlHeadMobile(__FUNCTION__);
        //get HTML footer by name action
        $data['foot'] = $this->getHtmlFooterMobile(__FUNCTION__);         
        // render to the view
        $this->view->set_data($data);
        $this->view->set_template('login');
        $this->view->render();         
    }
    
    public function course() {
        global $_user;
        $courses_tree = UserManager::get_sessions_by_category($_user['user_id'],true);
	foreach ($courses_tree as $cat => $sessions) {
		$courses_tree[$cat]['details'] = SessionManager::get_session_category($cat);
		if ($cat == 0) {
			$courses_tree[$cat]['courses'] = CourseManager::get_courses_list_by_user_id($_user['user_id'],false);
		}
		$courses_tree[$cat]['sessions'] = array_flip(array_flip($sessions));
		if (count($courses_tree[$cat]['sessions'])>0) {
			foreach ($courses_tree[$cat]['sessions'] as $k => $s_id) {
				$courses_tree[$cat]['sessions'][$k] = array('details' => SessionManager::fetch($s_id));
				$courses_tree[$cat]['sessions'][$k]['courses'] = UserManager::get_courses_list_by_session($_user['user_id'],$s_id);
			}
		}
	}

	$list = '';
        if ( is_array($courses_tree) ) {
            $course_independent_list = array();
            foreach ($courses_tree as $key => $category) {

	      if ($key == 0) {
	     // independent courses
             $userdefined_categories = $this->get_user_course_categories();

             foreach ($userdefined_categories as $index => $my_category) {
                    foreach ($category['courses'] as $course) {
                        if ($course['category_id'] == $index) {
                          $c = $this->get_logged_user_course_html($course, 0, 'independent_course_item');
                          $course_independent_list [] = $c[1];
                        }
                    }
              }


            }
        }
        }
        $nameTools = get_lang('MyCourses');
        $data = array();
        $data['name_tools']  = $nameTools;
        $data['course_list'] = $course_independent_list;
        //get HTML head by name action
        $data['head'] = $this->getHtmlHeadMobile(__FUNCTION__); 
        //get HTML footer by name action
        $data['foot'] = $this->getHtmlFooterMobile(__FUNCTION__);         
        // render to the view
        $this->view->set_data($data);
        $this->view->set_template('course');
        $this->view->render();         
    }
    
    public function courseview () {
        $nameTools = get_lang('MyCourses');
        $data = array();
        $get_course_info = api_get_course_info(api_get_course_id());
        $courseTitle = $get_course_info['name'];
        
        $data['name_tools']  = $nameTools;
        $data['courseTitle']  = $courseTitle;
        $data['num_cols'] = 3;
        $data['num_item'] = 8;
        //get HTML head by name action
        $data['head'] = $this->getHtmlHeadMobile(__FUNCTION__, $courseTitle);        
        //get HTML footer by name action
        $data['foot'] = $this->getHtmlFooterMobile(__FUNCTION__);        
        //list an array of objects of the tools
        $arrayObjectTool = $this->model->getToolListByAuthorization(TOOL_MOBILE);
        $data['tool'] = $this->getHtmlListTools($arrayObjectTool, $data['num_cols'], $data['num_item']);
        // render to the view
        $this->view->set_data($data);
        $this->view->set_template('courseview');
        $this->view->render();  
    }
    /**
     *
     * @param arrayobject $arrayObjectTool
     * @param int $num_col
     * @param int $num_item 
     */
    public function getHtmlListTools($arrayObjectTool, $num_col, $num_item)
    {
        //add atributte block at variable $arrayObjectTool
        $index = 'a';
        $cont_item = 0;
        //$html = '';
        foreach($arrayObjectTool as $objTool)
        {
            if($cont_item < $num_item)
            {   
                //format name when exist underline
                $objTool->name_published = $this->getFormatName($objTool->name);
                //get url image by name
                $objTool->image =$this->getUrlImage($objTool->name);
                $objTool->column = $index;
                if($index == 'c')
                    $index='a';
                else
                    $index++;
            }
            else
                unset($arrayObjectTool[$cont_item]);
            $cont_item++;
        }
        //$this->display($arrayObjectTool);
        /*$cont_cols = 0;
        foreach ($arrayObjectTool as $index=>$objTool)
        {
            if($cont_cols == 0)
                $html.='<div class="ui-grid-b">';
                     $html.='<div class="ui-block-'.$objTool->column.'"><img src="'.api_get_path(WEB_PATH).'main/img/miscellaneous.png">'.$objTool->name.'</div>';
            if($cont_cols == $num_col-1)
            {
                $html.='</div>';            
                $cont_cols = 0;
            }
            else
                $cont_cols++;
        }*/
      return $arrayObjectTool;
    }
    /**
     *
     * @param string $nameTool 
     * @return string URL
     */
    public function getUrlImage($nameTool)
    {
        //get name template and css 
        $template = $css_name = api_get_setting('allow_course_theme') == 'true'?(api_get_course_setting('course_theme', null, true)?api_get_course_setting('course_theme', null, true):api_get_setting('stylesheets')):api_get_setting('stylesheets');        
        $url = api_get_path(WEB_CSS_PATH).$template.'/mobile/'.$nameTool.'.png';
        return $url;
    }
    
    public function getFormatName($nameTool)
    {
        $name = explode('_',$nameTool);
        return $name[0];
    }
    
    
/**
 * Display code for one specific course a logged in user is subscribed to.
 * Shows a link to the course, what's new icons...
 *
 * $my_course['d'] - course directory
 * $my_course['i'] - course title
 * $my_course['c'] - visual course code
 * $my_course['k']  - system course code
 * $my_course['db'] - course database
 *
 * @version 1.0.3
 * @todo refactor into different functions for database calls | logic | display
 * @todo replace single-character $my_course['d'] indices
 * @todo move code for what's new icons to a separate function to clear things up
 * @todo add a parameter user_id so that it is possible to show the courselist of other users (=generalisation). This will prevent having to write a new function for this.
 */
function get_logged_user_course_html($course, $session_id = 0, $class='courses') {
	global $charset;
	global $nosession;

	$current_uid = api_get_user_id();
	$info = api_get_course_info($course['code']);
	$status_course = CourseManager::get_user_in_course_status($current_uid, $course['code']);

	if (!is_array($course['code'])) {
		$my_course = api_get_course_info($course['code']);
		$my_course['k'] = $my_course['id'];
		$my_course['db'] = $my_course['dbName'];
		$my_course['c'] = $my_course['official_code'];
		$my_course['i'] = $my_course['name'];
		$my_course['d'] = $my_course['path'];
		$my_course['t'] = $my_course['titular'];
		$my_course['id_session'] = $session_id;
		$my_course['status'] = ((empty($session_id))?$status_course:5);
	}

	if (api_get_setting('use_session_mode')=='true' && !$nosession) {
		global $now, $date_start, $date_end;
	}

	//initialise
	$result = '';

	// Table definitions
	//$statistic_database = Database::get_statistic_database();
	$main_user_table 		= Database :: get_main_table(TABLE_MAIN_USER);
	$tbl_session 			= Database :: get_main_table(TABLE_MAIN_SESSION);
	$tbl_session_category 	= Database :: get_main_table(TABLE_MAIN_SESSION_CATEGORY);
	$course_database = $my_course['db'];
	$course_tool_table 			= Database :: get_course_table(TABLE_TOOL_LIST, $course_database);
	$tool_edit_table 			= Database :: get_course_table(TABLE_ITEM_PROPERTY, $course_database);
	$course_group_user_table 	= Database :: get_course_table(TOOL_USER, $course_database);

	$user_id = api_get_user_id();
	$course_system_code = $my_course['k'];
	$course_visual_code = $my_course['c'];
	$course_title = $my_course['i'];
	$course_directory = $my_course['d'];
	$course_teacher = $my_course['t'];
	$course_teacher_email = isset($my_course['email'])?$my_course['email']:'';
	$course_info = Database :: get_course_info($course_system_code);
	//$course_access_settings = CourseManager :: get_access_settings($course_system_code);
	
	$course_id = isset($course_info['course_id'])?$course_info['course_id']:null;
	$course_visibility = $course_info['visibility'];

	
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

	$s_course_status = $my_course['status'];
	$is_coach = api_is_coach($my_course['id_session'],$course['code']);

	//display course entry
	$result.="\n\t";
	$result .= '<div class="'.$class.'" style="padding: 8px; clear:both;">';
	//show a hyperlink to the course, unless the course is closed and user is not course admin
	if ($course_visibility != COURSE_VISIBILITY_CLOSED || $user_in_course_status == COURSEMANAGER) {
		if(api_get_setting('use_session_mode')=='true' && !$nosession) {
			if(empty($my_course['id_session'])) {
				$my_course['id_session'] = 0;
			}
			if($user_in_course_status == COURSEMANAGER || ($date_start <= $now && $date_end >= $now) || $date_start=='0000-00-00') {
				$result .= '<a data-ajax="false" href="'.api_get_path(WEB_VIEW_PATH).'mobile/index.php?action=courseview&amp;cidReq='.$course['code'].'&amp;id_session='.$my_course['id_session'].'">'.$course_display_title.'</a>';
			}
		} else {
			$result .= '<a href="'.api_get_path(WEB_COURSE_PATH).$course_directory.'/">'.$course_display_title.'</a>';
		}
	} else {
		$result .= '<strong>'.$course_display_title.'</strong>'." "." ".get_lang('CourseClosed')."";
	}
	// show the course_code and teacher if chosen to display this
	if (api_get_setting('display_coursecode_in_courselist') == 'true' || api_get_setting('display_teacher_in_courselist') == 'true') {
		$result .= '<br />';
	}
	if (api_get_setting('display_coursecode_in_courselist') == 'true') {
		$result .= $course_display_code;
	}
	if (api_get_setting('display_coursecode_in_courselist') == 'true' && api_get_setting('display_teacher_in_courselist') == 'true') {
		$result .= ' &ndash; ';
	}
	if (api_get_setting('display_teacher_in_courselist') == 'true') {


		if (api_get_setting('use_session_mode')=='true' && !$nosession) {
			$coachs_course = api_get_coachs_from_course($my_course['id_session'],$course['code']);
			$course_coachs = array();
			if (is_array($coachs_course)) {
				foreach ($coachs_course as $coach_course) {
					$course_coachs[] = api_get_person_name($coach_course['firstname'], $coach_course['lastname']);
				}
			}

			if ($s_course_status == 1 || ($s_course_status == 5 && empty($my_course['id_session']))) {
				$result .= $course_teacher;
			}
			
			if (($s_course_status == 5 && !empty($my_course['id_session'])) || ($is_coach && $s_course_status != 1)) {
				$result .= get_lang('Coachs').': '.implode(', ',$course_coachs);
			}


		} else {
			$result .= $course_teacher;
		}

		if(!empty($course_teacher_email)) {
			$result .= ' ('.$course_teacher_email.')';
		}
	}

	// display the what's new icons
	//$result .= show_notification($my_course);

	if ((CONFVAL_showExtractInfo == SCRIPTVAL_InCourseList || CONFVAL_showExtractInfo == SCRIPTVAL_Both) && $nbDigestEntries > 0) {

		reset($digest);
		$result .= '
					<ul>';
		while (list ($key2) = each($digest[$thisCourseSysCode])) {
			$result .= '<li>';
			if ($orderKey[1] == 'keyTools') {
				$result .= "<a href=\"$toolsList[$key2] [\"path\"] $thisCourseSysCode \">";
				$result .= "$toolsList[$key2][\"name\"]</a>";
			} else {
				$result .= format_locale_date(CONFVAL_dateFormatForInfosFromCourses, strtotime($key2));
			}
			$result .= '</li>';
			$result .= '<ul>';
			reset ($digest[$thisCourseSysCode][$key2]);
			while (list ($key3, $dataFromCourse) = each($digest[$thisCourseSysCode][$key2])) {
				$result .= '<li>';
				if ($orderKey[2] == 'keyTools') {
					$result .= "<a href=\"$toolsList[$key3] [\"path\"] $thisCourseSysCode \">";
					$result .= "$toolsList[$key3][\"name\"]</a>";
				} else {
					$result .= format_locale_date(CONFVAL_dateFormatForInfosFromCourses, strtotime($key3));
				}
				$result .= '<ul compact="compact">';
				reset($digest[$thisCourseSysCode][$key2][$key3]);
				while (list ($key4, $dataFromCourse) = each($digest[$thisCourseSysCode][$key2][$key3])) {
					$result .= '<li>';
					$result .= htmlspecialchars(api_substr(strip_tags($dataFromCourse), 0, CONFVAL_NB_CHAR_FROM_CONTENT), ENT_QUOTES, $charset);
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
	$result .= '</div>';


	if (api_get_setting('use_session_mode') == 'true' && !$nosession) {
		$session = '';
		$active = false;
		if (!empty($my_course['session_name'])) {

			// Request for the name of the general coach
			$sql = 'SELECT lastname, firstname,sc.name
			FROM '.$tbl_session.' ts
			LEFT JOIN '.$main_user_table .' tu
			ON ts.id_coach = tu.user_id
			INNER JOIN '.$tbl_session_category.' sc ON ts.session_category_id = sc.id
			WHERE ts.id='.(int) $my_course['id_session']. ' LIMIT 1';

			$rs = Database::query($sql, __FILE__, __LINE__);
			$sessioncoach = Database::store_result($rs);
			$sessioncoach = $sessioncoach[0];

			$session = array();
			$session['title'] = $my_course['session_name'];
			$session_category_id = CourseManager::get_session_category_id_by_session_id($my_course['id_session']);
			$session['category'] = $sessioncoach['name'];
			if ( $my_course['date_start']=='0000-00-00' ) {
				$session['dates'] = get_lang('WithoutTimeLimits');
				if (api_get_setting('show_session_coach') === 'true') {
					$session['coach'] = get_lang('GeneralCoach').': '.api_get_person_name($sessioncoach['firstname'], $sessioncoach['lastname']);
				}
				$active = true;
			} else {
				$session ['dates'] = ' - '.get_lang('From').' '.$my_course['date_start'].' '.get_lang('To').' '.$my_course['date_end'];
				if (api_get_setting('show_session_coach') === 'true') {
					$session['coach'] = get_lang('GeneralCoach').': '.api_get_person_name($sessioncoach['firstname'], $sessioncoach['lastname']);
				}
				$active = ($date_start <= $now && $date_end >= $now)?true:false;
			}
		}
		$output = array ($my_course['user_course_cat'], $result, $my_course['id_session'], $session, 'active'=>$active,'session_category_id'=>$session_category_id);
	} else {
		$output = array ($my_course['user_course_cat'], $result);
	}

	return $output;
}

    /**
    * retrieves the user defined course categories
    * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
    * @return array containing all the titles of the user defined courses with the id as key of the array
    */
    public function get_user_course_categories() {
            global $_user;
            $output = array();
            $output[0] = api_get_setting('default_category_course');
            $table_category = Database::get_user_personal_table(TABLE_USER_COURSE_CATEGORY);
            $sql = "SELECT * FROM ".$table_category." WHERE user_id='".Database::escape_string($_user['user_id'])."' ORDER BY sort ASC";
            $result = Database::query($sql,__FILE__,__LINE__);
            while ($row = Database::fetch_array($result)) {
                    $output[$row['id']] = $row['title'];
            }
            return $output;
    }

    public function validateUser()
    {
        $data = array();
        $data['message'] = 'open mobile';
        // render to the view
        $this->view->set_data($data);
        $this->view->set_template('course');
        $this->view->render();         
    }
    
    /**
     * function to load script for testing 
     */
    
    public function test()
    {
        $data = array();
        $data['message'] = 'open mobile';
        // render to the view
        $this->view->set_data($data);
        $this->view->set_template('test');
        $this->view->render();                
    }
    
    function get_personal_course_list($user_id) {
	// initialisation
	$personal_course_list = array();

	// table definitions
	$main_user_table 		= Database :: get_main_table(TABLE_MAIN_USER);
	$main_course_table 		= Database :: get_main_table(TABLE_MAIN_COURSE);
	$main_course_user_table = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$tbl_session_course 	= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
	$tbl_session_course_user= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
	$tbl_session 			= Database :: get_main_table(TABLE_MAIN_SESSION);

	$user_id = Database::escape_string($user_id);
	$personal_course_list = array ();

	//Courses in which we suscribed out of any session
	$personal_course_list_sql = "SELECT course.code k, course.directory d, course.visual_code c, course.db_name db, course.title i,
										course.tutor_name t, course.course_language l, course_rel_user.status s, course_rel_user.sort sort,
										course_rel_user.user_course_cat user_course_cat
										FROM    ".$main_course_table."       course,".$main_course_user_table."   course_rel_user
										WHERE course.code = course_rel_user.course_code"."
										AND   course_rel_user.user_id = '".$user_id."'
										ORDER BY course_rel_user.user_course_cat, course_rel_user.sort ASC,i";

	$course_list_sql_result = Database::query($personal_course_list_sql, __FILE__, __LINE__);

	while ($result_row = Database::fetch_array($course_list_sql_result)) {
		$personal_course_list[] = $result_row;
	}

	//$personal_course_list = array_merge($personal_course_list, $course_list_sql_result);

	$personal_course_list_sql = "SELECT DISTINCT course.code k, course.directory d, course.visual_code c, course.db_name db, course.title i, course.tutor_name t, course.course_language l, 5 as s
								FROM $main_course_table as course, $tbl_session_course_user as srcru
								WHERE srcru.course_code=course.code AND srcru.id_user='$user_id'";

	$course_list_sql_result = Database::query($personal_course_list_sql, __FILE__, __LINE__);

	while ($result_row = Database::fetch_array($course_list_sql_result)) {
		$personal_course_list[] = $result_row;
	}

	//$personal_course_list = array_merge($personal_course_list, $course_list_sql_result);

	$personal_course_list_sql = "SELECT DISTINCT course.code k, course.directory d, course.visual_code c, course.db_name db, course.title i, course.tutor_name t, course.course_language l, 2 as s
								FROM $main_course_table as course, $tbl_session_course as src, $tbl_session as session
								WHERE session.id_coach='$user_id' AND session.id=src.id_session AND src.course_code=course.code";

	$course_list_sql_result = Database::query($personal_course_list_sql, __FILE__, __LINE__);

	//$personal_course_list = array_merge($personal_course_list, $course_list_sql_result);

	while ($result_row = Database::fetch_array($course_list_sql_result)) {
		$personal_course_list[] = $result_row;
	}
	return $personal_course_list;
        }

    
    /**
     * Get the html header according to the action obtained by request
     * @param type $action
     * @param string $title 
     * @return string 
     */
    
    public function getHtmlHeadMobile($action, $title='')
    {
        $html ='';
        switch($action)
        {
            case 'login':$html='<div data-role="header" data-position="inline">
                                <h1>Login</h1>
                                <a data-ajax="false" href="'.api_get_path(WEB_PATH).'" data-icon="home" data-iconpos="notext" class="ui-btn-right"></a>
                                </div>';break;
            case 'course'://style="left:100px;"
                        $html='<div data-role="header" data-position="inline">
                                <!--<a href="index.html" data-icon="back" class="ui-btn-left">'.get_lang('Back').'</a>-->
                                <a href="'.api_get_path(WEB_PATH).'" data-icon="home" data-ajax="false" class="ui-btn-left">'.get_lang('Home').'</a>
                                <h1>'.get_lang('DisplayTrainingList').'</h1>
                                <a href="'.api_get_path(WEB_VIEW_PATH).'mobile/index.php?action=logout&init=mobile" data-ajax="false" data-icon="delete" class="ui-btn-right">'.get_lang('Logout').'</a>
                                </div>';
                                break;
            case 'courseview': 
                        $html='<div data-role="header" data-position="inline">
                                <a href="'.api_get_path(WEB_VIEW_PATH).'mobile/index.php?action=course&init=mobile" data-icon="back" data-ajax="false" class="ui-btn-left">'.get_lang('Back').'</a>
                                <!--<a href="'.api_get_path(WEB_PATH).'" data-icon="home" data-ajax="false" class="ui-btn-left" style="left:100px;" >'.get_lang('Home').'</a>-->
                                <h1>'.$title.'</h1>
                                <a href="'.api_get_path(WEB_VIEW_PATH).'mobile/index.php?action=logout&init=mobile" data-ajax="false" data-icon="delete" class="ui-btn-right">'.get_lang('Logout').'</a>
                                </div>';
                                break;
                            
            default:    $html='<div data-role="header" data-position="inline">
                                <h1>Login</h1>
                                <a data-ajax="false" href="'.api_get_path(WEB_PATH).'" data-icon="home" data-iconpos="notext" class="ui-btn-right"></a>
                                </div>';break;
        }
        return $html;
    }
    /**
     * Get the html footer according to the action obtained by request
     * @param type $action
     * @return string 
     */    
    public function getHtmlFooterMobile($action)
    {
        $html='';
        switch ($action)
        {
            case 'login':   $html='<div data-role="footer" data-position="fixed"> 
                                            <h4>Footer Dokeos</h4> 
                                    </div>';break;
            case 'courses':  $html='<div data-role="footer" data-id="foo1" data-position="fixed">
                                        <div data-role="navbar">
                                                <ul>
                                                    <li><a href="#" onclick="message()">Friends</a></li>
                                                    <li><a href="b.html">Albums</a></li>
                                                    <li><a href="c.html">Emails</a></li>
                                                    <li><a href="d.html" >Info</a></li>
                                                </ul>
                                        </div>
                                    </div>';break;
            default:        $html='<div data-role="footer" data-position="fixed">
                                        <p>2012 Dokeos Mobile</p>
                                    </div>';break;
        }
        return $html;
    }
    /**
     *
     * @param type $var 
     */
    public function display($var)
    {
        echo"<pre>";
        print_r($var);
        echo"</pre>";
    }

    
}