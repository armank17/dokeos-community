<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* Display a list of courses and search for courses
* @package dokeos.admin
*/


// Language files that should be included
$language_file = array('admin','courses');

// resetting the course id
$cidReset = true;

// setting the help
$help_content = 'platformadministrationcourselist';

// including the global Dokeos file
require ('../inc/global.inc.php');

// including additional libraries
require_once (api_get_path(LIBRARY_PATH).'course.lib.php');
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
require_once (api_get_path(LIBRARY_PATH).'sortabletable.class.php');
require_once '../gradebook/lib/be/gradebookitem.class.php';
require_once '../gradebook/lib/be/category.class.php';

// section for the tabs
$this_section=SECTION_PLATFORM_ADMIN;

// user permissions
api_protect_admin_script();

// Setting the breadcrumbs
$interbreadcrumb[] = array ("url" => 'index.php', "name" => get_lang('PlatformAdmin'));

$htmlHeadXtra[] = '<script type="text/javascript">
    $(document).ready(function(){
       $("#btn-search").click(function() {
            if ($("#search").css("display") == "none") {
                $("#keyword").val("");
                $("#search").show();
                $(".secondary-actions-extra").css("height", "60");
                $(".secondary-actions-extra").css("width", "375");
                $("#keyword").focus();
            } else {
                $("#search").hide()
            }
       });
    });
</script>';
//$htmlHeadXtra[] = '<link type="text/css" rel="stylesheet" href="'.api_get_path(WEB_PATH).'main/appcore/library/jquery/jquery.alerts/jquery.alerts.css" />';
//$htmlHeadXtra[] = '<script  type="text/javascript" src="'.api_get_path(WEB_PATH).'main/appcore/library/jquery/jquery.alerts/jquery.alerts.js"></script>';
$htmlHeadXtra[] ='<script type="text/javascript">
    function jalertc(lang,title,code){
        jConfirm(lang, title, function(r) {
            window.location.href = "course_list.php?delete_course="+code;
        });
    }
</script> ';
// action handling
if (isset ($_POST['action']))
{
	switch ($_POST['action'])
	{
		// Delete selected courses
		case 'delete_courses' :
			$course_codes = $_POST['course'];
			if (count($course_codes) > 0)
			{
				foreach ($course_codes as $index => $course_code)
				{
					CourseManager :: delete_course($course_code);
					$obj_cat=new Category();
					$obj_cat->update_category_delete($course_code);
				}
			}
			break;
	}
}

// showing the search box or not
if (isset ($_GET['search']) && $_GET['search'] == 'advanced')
{
	// Get all course categories
	$table_course_category = Database :: get_main_table(TABLE_MAIN_CATEGORY);

	$interbreadcrumb[] = array ("url" => 'course_list.php', "name" => get_lang('CourseList'));
	$tool_name = get_lang('SearchACourse');

	// Display the header
	Display :: display_header($tool_name);

	echo '<div class="actions">';
        if($_GET['search']!= 'advanced'){
            CourseManager::show_menu_course_admin('list');
        }else{
            CourseManager::show_menu_course_admin('search_advanced');
        }
	echo '</div>';

	// display the tool title
	//api_display_tool_title($tool_name);

	// create the form for advanced searching
	$form = new FormValidator('advanced_course_search', 'get');
	$form->addElement('header', '', $tool_name);
	$form->add_textfield('keyword_code', get_lang('CourseCode'), false, 'class="focus"');
	$form->add_textfield('keyword_title', get_lang('Title'), false);
	$categories = array();
	$categories_select = $form->addElement('select', 'keyword_category', get_lang('CourseFaculty'), $categories);
	CourseManager::select_and_sort_categories($categories_select);
	$el = & $form->addElement('select_language', 'keyword_language', get_lang('CourseLanguage'));
	$el->addOption(get_lang('All'), '%');
	$form->addElement('radio', 'keyword_visibility', get_lang("CourseAccess"), get_lang('OpenToTheWorld'), COURSE_VISIBILITY_OPEN_WORLD);
	$form->addElement('radio', 'keyword_visibility', null, get_lang('OpenToThePlatform'), COURSE_VISIBILITY_OPEN_PLATFORM);
	$form->addElement('radio', 'keyword_visibility', null, get_lang('Private'), COURSE_VISIBILITY_REGISTERED);
	$form->addElement('radio', 'keyword_visibility', null, get_lang('CourseVisibilityClosed'), COURSE_VISIBILITY_CLOSED);
	$form->addElement('radio', 'keyword_visibility', null, get_lang('All'), '%');
	$form->addElement('radio', 'keyword_subscribe', get_lang('Subscription'), get_lang('Allowed'), 1);
	$form->addElement('radio', 'keyword_subscribe', null, get_lang('Denied'), 0);
	$form->addElement('radio', 'keyword_subscribe', null, get_lang('All'), '%');
	$form->addElement('radio', 'keyword_unsubscribe', get_lang('Unsubscription'), get_lang('AllowedToUnsubscribe'), 1);
	$form->addElement('radio', 'keyword_unsubscribe', null, get_lang('NotAllowedToUnsubscribe'), 0);
	$form->addElement('radio', 'keyword_unsubscribe', null, get_lang('All'), '%');
	$form->addElement('style_submit_button', 'submit', get_lang('SearchCourse'),'class="search"');
	$defaults['keyword_language'] = '%';
	$defaults['keyword_visibility'] = '%';
	$defaults['keyword_subscribe'] = '%';
	$defaults['keyword_unsubscribe'] = '%';
	$form->setDefaults($defaults);

	// start the content div
	echo '<div id="content">';

	$form->display();

	// close the content div
	echo '</div>';
}
else
{
	$tool_name = get_lang('CourseList');

	// Display the header
	Display :: display_header($tool_name);

	// dislay the tool title
	//api_display_tool_title($tool_name);

	// action handling
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'show_msg':
                if (!empty($_GET['warn'])) {
                    Display::display_warning_message(urldecode($_GET['warn']), false,true);
                }
                if (!empty($_GET['msg'])) {
                	Display::display_normal_message(urldecode($_GET['msg']),false,true);
                }
                break;
            default:
                break;
        }
    }
	if (isset ($_GET['delete_course']))
	{
           
		CourseManager :: delete_course($_GET['delete_course']);

		$obj_cat=new Category();
		$obj_cat->update_category_delete($_GET['delete_course']);
	}
	// Create a search-box
	$form = new FormValidator('search_simple','get','','','width=200px',false);
	$renderer =& $form->defaultRenderer();
	$renderer->setElementTemplate('<span>{element}</span> ');
        $form->addElement('html', '<div>');
	$form->addElement('text','keyword','','id="keyword"'); 
	$form->addElement('style_submit_button', 'submit', get_lang('Search'),'style="float:none;" class="search"');
        $form->addElement('html', '</div>');
        $form->addElement('html', '<div>');
	$form->addElement('static','search_advanced_link',null,'<a href="course_list.php?search=advanced">'.get_lang('AdvancedSearch').'</a>');
        $form->addElement('html', '</div>');
	//$form->addElement('html','<a href="'.api_get_path(WEB_CODE_PATH).'admin/course_add.php" style="float:right;margin-top:5px;margin-right:5px;" >'.Display::return_icon('pixel.gif',get_lang('AddCourse'), array('class' => 'toolactionplaceholdericon toolactioncreatecourse')).get_lang('AddCourse').'</a>');
	//echo '<div style="float:right;margin-top:5px;margin-right:5px;">
	//		  <a href="'.api_get_path(WEB_CODE_PATH).'admin/course_add.php" >'.Display::return_icon('course_add.gif',get_lang('AddCourse')).get_lang('AddCourse').'</a>
	//	 </div>';

/*	echo '<div class="actions">';
	$form->display();
	echo '</div>';*/

	// Create a sortable table with the course data
	$table = new SortableTable('courses', 'get_number_of_courses', 'get_course_data',2);
	$parameters=array();
	if (isset ($_GET['keyword'])) {
		$parameters = array ('keyword' => Security::remove_XSS($_GET['keyword']));
	} elseif (isset ($_GET['keyword_code'])) {
		$parameters['keyword_code'] =  Security::remove_XSS($_GET['keyword_code']);
	 	$parameters['keyword_title'] = Security::remove_XSS($_GET['keyword_title']);
		$parameters['keyword_category'] = Security::remove_XSS($_GET['keyword_category']);
		$parameters['keyword_language'] = Security::remove_XSS($_GET['keyword_language']);
		$parameters['keyword_visibility'] = Security::remove_XSS($_GET['keyword_visibility']);
		$parameters['keyword_subscribe'] = Security::remove_XSS($_GET['keyword_subscribe']);
		$parameters['keyword_unsubscribe'] = Security::remove_XSS($_GET['keyword_unsubscribe']);
	}
	$table->set_additional_parameters($parameters);
	$table->set_header(0, '', false);
	$table->set_header(1, get_lang('Code'), true, '', 'nowrap="nowrap"');
	$table->set_header(2, get_lang('Title'));
	$table->set_header(3, get_lang('Language'),false,'width="50px"');
	$table->set_header(4, get_lang('Category'));
	$table->set_header(5, get_lang('SubscriptionAllowed'),false,'width="50px"');
	$table->set_header(6, get_lang('UnsubscriptionAllowed'),false,'width="50px"');
	//$table->set_header(7, get_lang('IsVirtualCourse'));
	$table->set_header(7, get_lang('Teacher'));
	$table->set_header(8, get_lang('Actions'), false,'width="150px"');
	$table->set_column_filter(8,'modify_filter');
	$table->set_form_actions(array ('delete_courses' => get_lang('DeleteCourse')),'course');

	//Actions
	echo '<div class="actions">';
	CourseManager::show_menu_course_admin('list');
	echo '</div>';

	// start the content div
	echo '<div id="content">';
	echo '<div class="secondary-actions" id="search" style="'.(isset($_GET['keyword'])?'display:block;':'display:none').'">';
//	echo '<h2 style="font-weight:bolder; text-transform:uppercase;">'.get_lang('Search').'</h2>';
        echo '<div class="secondary-actions-extra" style="height:60px !important; width:375px !important;">';
        $form->display();
	echo '</div>';
	echo '</div>';


	// display the sortable table
        echo '<div class="row"><div class="form_header">'.get_lang('CourseList').'</div></div>';
	$table->display();

	// close the content div
	echo '</div>';
}



// display the footer
Display :: display_footer();

/**
 * Get the number of courses which will be displayed
 */
function get_number_of_courses()
{
	global $_configuration;

	// Database table definition
	$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);

	$sql = "SELECT COUNT(code) AS total_number_of_items FROM $course_table";

    if ((api_is_platform_admin() || api_is_session_admin()) && $_configuration['multiple_access_urls']==true && api_get_current_access_url_id()!=-1) {
    	$access_url_rel_course_table = Database :: get_main_table(TABLE_MAIN_ACCESS_URL_REL_COURSE);
    	$sql.= " INNER JOIN $access_url_rel_course_table url_rel_course ON (code=url_rel_course.course_code)";
    }

	if (isset ($_GET['keyword']))
	{
		$keyword = Database::escape_string($_GET['keyword']);
		$sql .= " WHERE (title LIKE '%".$keyword."%' OR code LIKE '%".$keyword."%' OR visual_code LIKE '%".$keyword."%')";
	}
	elseif (isset ($_GET['keyword_code']))
	{
		$keyword_code = Database::escape_string($_GET['keyword_code']);
		$keyword_title = Database::escape_string($_GET['keyword_title']);
		$keyword_category = Database::escape_string($_GET['keyword_category']);
		$keyword_language = Database::escape_string($_GET['keyword_language']);
		$keyword_visibility = Database::escape_string($_GET['keyword_visibility']);
		$keyword_subscribe = Database::escape_string($_GET['keyword_subscribe']);
		$keyword_unsubscribe = Database::escape_string($_GET['keyword_unsubscribe']);
		$sql .= " WHERE (code LIKE '%".$keyword_code."%' OR visual_code LIKE '%".$keyword_code."%') AND title LIKE '%".$keyword_title."%' AND category_code LIKE '%".$keyword_category."%'  AND course_language LIKE '%".$keyword_language."%'   AND visibility LIKE '%".$keyword_visibility."%'    AND subscribe LIKE '".$keyword_subscribe."'AND unsubscribe LIKE '".$keyword_unsubscribe."'";
	}

	 // adding the filter to see the user's only of the current access_url
	if ((api_is_platform_admin() || api_is_session_admin()) && $_configuration['multiple_access_urls']==true && api_get_current_access_url_id()!=-1) {
    		$sql.= " AND url_rel_course.access_url_id=".api_get_current_access_url_id();
    }

	$res = Database::query($sql, __FILE__, __LINE__);
	$obj = Database::fetch_object($res);
	return $obj->total_number_of_items;
}
/**
 * Get course data to display
 */
function get_course_data($from, $number_of_items, $column, $direction)
{
	// Database table definition
	$course_table 		= Database :: get_main_table(TABLE_MAIN_COURSE);
	$users_table 		= Database :: get_main_table(TABLE_MAIN_USER);
	$course_users_table = Database :: get_main_table(TABLE_MAIN_COURSE_USER);

	$sql = "SELECT code AS col0, visual_code AS col1, title AS col2, course_language AS col3, category_code AS col4, subscribe AS col5, unsubscribe AS col6, tutor_name as col7, code AS col8, visibility AS col9,directory as col10 FROM $course_table";
	//$sql = "SELECT code AS col0, visual_code AS col1, title AS col2, course_language AS col3, category_code AS col4, subscribe AS col5, unsubscribe AS col6, code AS col7, tutor_name as col8, code AS col9, visibility AS col10,directory as col11 FROM $course_table";
	global $_configuration;
    if ((api_is_platform_admin() || api_is_session_admin()) && $_configuration['multiple_access_urls']==true && api_get_current_access_url_id()!=-1) {
    	$access_url_rel_course_table = Database :: get_main_table(TABLE_MAIN_ACCESS_URL_REL_COURSE);
    	$sql.= " INNER JOIN $access_url_rel_course_table url_rel_course ON (code=url_rel_course.course_code)";
    }

	if (isset ($_GET['keyword']))
	{
		$keyword = Database::escape_string($_GET['keyword']);
		$sql .= " WHERE (title LIKE '%".$keyword."%' OR code LIKE '%".$keyword."%' OR visual_code LIKE '%".$keyword."%')";
	}
	elseif (isset ($_GET['keyword_code']))
	{
		$keyword_code = Database::escape_string($_GET['keyword_code']);
		$keyword_title = Database::escape_string($_GET['keyword_title']);
		$keyword_category = Database::escape_string($_GET['keyword_category']);
		$keyword_language = Database::escape_string($_GET['keyword_language']);
		$keyword_visibility = Database::escape_string($_GET['keyword_visibility']);
		$keyword_subscribe = Database::escape_string($_GET['keyword_subscribe']);
		$keyword_unsubscribe = Database::escape_string($_GET['keyword_unsubscribe']);
		$sql .= " WHERE (code LIKE '%".$keyword_code."%' OR visual_code LIKE '%".$keyword_code."%') AND title LIKE '%".$keyword_title."%' AND category_code LIKE '%".$keyword_category."%'  AND course_language LIKE '%".$keyword_language."%'   AND visibility LIKE '%".$keyword_visibility."%'    AND subscribe LIKE '".$keyword_subscribe."'AND unsubscribe LIKE '".$keyword_unsubscribe."'";
	}

	 // adding the filter to see the user's only of the current access_url
	if ((api_is_platform_admin() || api_is_session_admin()) && $_configuration['multiple_access_urls']==true && api_get_current_access_url_id()!=-1) {
    		$sql.= " AND url_rel_course.access_url_id=".api_get_current_access_url_id();
    }

	$sql .= " ORDER BY col$column $direction ";
	$sql .= " LIMIT $from,$number_of_items";
	$res = Database::query($sql, __FILE__, __LINE__);
	$courses = array ();
	while ($course = Database::fetch_row($res))
	{
		/** @todo this should be done using a filter in the sortable table like we did for the sortable table. Code has apparently been written by somebody who did not understand the sortable table */
		//place colour icons in front of courses
		//$course[1] = get_course_visibility_icon($course[9]).'<a href="'.api_get_path(WEB_COURSE_PATH).$course[9].'/index.php">'.$course[1].'</a>';
		$course[1] = get_course_visibility_icon($course[9]).'<a href="'.api_get_path(WEB_COURSE_PATH).$course[10].'/index.php">'.$course[1].'</a>';
		$course[5] = $course[5] == SUBSCRIBE_ALLOWED ? get_lang('Yes') : get_lang('No');
		$course[6] = $course[6] == UNSUBSCRIBE_ALLOWED ? get_lang('Yes') : get_lang('No');
		//$course[7] = CourseManager :: is_virtual_course_from_system_code($course[7]) ? get_lang('Yes') : get_lang('No');
		//$course_rem = array($course[0],$course[1],$course[2],$course[3],$course[4],$course[5],$course[6],$course[7],$course[8],$course[9]);
		$course_rem = array($course[0],$course[1],$course[2],$course[3],$course[4],$course[5],$course[6],$course[7],$course[8]);
		$courses[] = $course_rem;
	}
	return $courses;
}
/**
 * Filter to display the edit-buttons
 */
function modify_filter($code,$url_params,$row)
{
	global $charset;

		return
		'<a href="course_information.php?code='.$code.'">'.Display::return_icon('pixel.gif', get_lang('Info'), array('class' => 'actionplaceholdericon actioninfo')).'</a>&nbsp;&nbsp;'.
		// This is not the preferable way to go to the homepage. But for the moment the only one because the directory is not in the $row of the sortable table and
		// visual code or code is not necessarily identical to $code
		'<a href="../course_home/course_home.php?cidReq='.$code.'">'.Display::return_icon('pixel.gif', get_lang('CourseHomepage'), array('class' => 'actionplaceholdericon actioncoursehome')).'</a>&nbsp;&nbsp;'.
		//'<a href="'.api_get_path(WEB_COURSE_PATH).$row[11].'/index.php">'.Display::return_icon('course_home.gif', get_lang('CourseHomepage')).'</a>&nbsp;'.
		//'<a href="../tracking/courseLog.php?cidReq='.$code.'">'.Display::return_icon('pixel.gif', get_lang('Tracking'), array('class' => 'actionplaceholdericon actiontracking')).'</a>&nbsp;&nbsp;'.
		'<a href="course_edit.php?course_code='.$code.'">'.Display::return_icon('pixel.gif', get_lang('Edit'), array('class' => 'actionplaceholdericon actionedit')).'</a>&nbsp;&nbsp;'.
		//'<a href="course_list.php?delete_course='.$code.'"  onclick="javascript:if(!confirm('."'".addslashes(api_htmlentities(get_lang("ConfirmYourChoice"),ENT_QUOTES,$charset))."'".')) return false;">'.Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')).'</a>&nbsp;&nbsp;'.
                '<a href="javascript:void(0);"  onclick="Alert_Confim_Delete( \'course_list.php?delete_course='.$code.'\',\''.get_lang("ConfirmationDialog").'\',\''.get_lang("ConfirmYourChoice").'\');">'.Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')).'</a>&nbsp;&nbsp;'.
                 
		'<a href="../coursecopy/backup.php?cidReq='.$code.'">'.Display::return_icon('pixel.gif', get_lang('CreateBackup'), array('class' => 'actionplaceholdericon actionsavebackup')).'</a>&nbsp;&nbsp;';

}
/**
 * Return an icon representing the visibility of the course
 */
function get_course_visibility_icon($v)
{
	$path = api_get_path(REL_CODE_PATH);
	$style = 'margin-bottom:-5px;margin-right:5px;';
	switch($v)
	{
		case 0:
			return Display::return_icon('pixel.gif', get_lang('CourseVisibilityClosed'), array('style'=>$style, 'class' => 'actionplaceholdericon actioncourseclosedstatus'));
			break;
		case 1:
			return Display::return_icon('pixel.gif', get_lang('Private'), array('style'=>$style, 'class' => 'actionplaceholdericon actionprivatestatus'));
			break;
		case 2:
			return Display::return_icon('pixel.gif', get_lang('OpenToThePlatform'), array('style'=>$style, 'class' => 'actionplaceholdericon actionpublicstatus'));
			break;
		case 3:
			return Display::return_icon('pixel.gif', get_lang('OpenToTheWorld'), array('style'=>$style, 'class' => 'actionplaceholdericon actionopentoworldstatus'));
			break;
		default:
			return '';
	}
}
?>
