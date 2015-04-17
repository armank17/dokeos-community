<?php
// $Id: add_course.php 20588 2009-05-13 12:34:18Z pcool $
/* For licensing terms, see /dokeos_license.txt */
/**
==============================================================================
* This script allows professors and administrative staff to create course sites.
* @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
* @author Roan Embrechts, refactoring
* @package dokeos.create_course
==============================================================================
*/

// name of the language file that needs to be included
$language_file = "create_course";

//delete the globals["_cid"] we don't need it here
$cidReset = true; // Flag forcing the 'current course' reset

// including the global file
include ('../inc/global.inc.php');

require_once api_get_path(SYS_PATH) .  'main/core/model/ecommerce/EcommerceCatalog.php';


// help
$help_content = get_help('createcourse');

// section for the tabs
$this_section=SECTION_COURSES;

// include configuration file
include (api_get_path(CONFIGURATION_PATH).'add_course.conf.php');

// include additional libraries
include_once (api_get_path(LIBRARY_PATH).'add_course.lib.inc.php');
include_once (api_get_path(LIBRARY_PATH).'course.lib.php');
include_once (api_get_path(LIBRARY_PATH).'debug.lib.inc.php');
include_once (api_get_path(LIBRARY_PATH).'fileManage.lib.php');
include_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
include_once (api_get_path(CONFIGURATION_PATH).'course_info.conf.php');
require_once '../inc/lib/xajax/xajax.inc.php';

$xajax = new xajax();
//$xajax->debugOn();
$xajax -> registerFunction ('course_create');
$xajax -> registerFunction ('prepare_course');
$xajax -> registerFunction ('show_form_create_course');

$htmlHeadXtra[] = $xajax->getJavascript('../inc/lib/xajax/');

$xajax -> processRequests();

$interbreadcrumb[] = array('url'=>api_get_path(WEB_PATH).'user_portal.php', 'name'=> get_lang('MyCourses'));
// Displaying the header
$tool_name = get_lang('CreateSite');

if (api_get_setting('allow_users_to_create_courses')=='false' && !api_is_platform_admin()) {
	api_not_allowed(true);
}

$htmlHeadXtra[] = '<link rel="stylesheet" type="text/css" href="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/chosen/chosen.css"/>';
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/chosen/chosen.jquery.min.js" ></script>';
$htmlHeadXtra[] = '<script type="text/javascript">
$(document).ready( function() {
    $("#name").keypress(function( event ) {
        if (event.which == 13 ) {
            xajax_course_create(xajax.getFormValues("add_course"));return false;
        }   
    });
  $(".chzn-select").chosen({no_results_text: "' . get_lang('NoResults') . '"});
});
</script>
<style  type="text/css">
div#form_create_course {
    height: 165px;
}
.smallwhite {
    margin: 0px 15px !important;
}
#content .actions{
        background: none repeat scroll 0 0 transparent;
        border: 0px;
        box-shadow: none;
        border-radius: 0 0 0 0;
    }
</style>
';
// Display the header
Display :: display_header($tool_name);

// start the content div
echo '<div id="content" class="maxcontent">';

// Check access rights
if(!api_is_session_admin()){
	if (!api_is_allowed_to_create_course()) {
		Display :: display_error_message(get_lang("NotAllowed"));
		Display::display_footer();
		exit;
	}
}
$languages = api_get_languages();
$e_commerce_enabled = intval(api_get_setting("e_commerce"));

?>
<?php echo api_display_tool_title($tool_name); ?>
<div id="form_create_course">

    <form id="add_course" method="post" name="add_course" action="#" style="margin:0px;">
<table border="0" cellpadding="5" cellspacing="0" width="940">
<tr>
<td width="20%"><?php echo get_lang('CourseName'); ?></td>
<td width="80%"><input style="width: 425px;" type="text" id="name" name="name" size="60" class="focus" maxlength="100"  value="<?php if($formSent) echo api_htmlentities($name,ENT_QUOTES,$charset); ?>"></td>
</tr>
<tr>
<td width="20%"><?php echo get_lang('Ln'); ?></td>
<td width="80%">
    <select id="course_language" name="course_language" style="width: 436px" class="chzn-select">          
<?php
foreach ($languages['name'] as $index => $name){
$selecte= ($languages['folder'][$index] == api_get_setting('platformLanguage')) ? 'selected="selected"': '';
echo '<option value="'.$languages['folder'][$index].'" '.$selecte.'>'.$name.'</option>';
}
?>
</select>
</td>
</tr>


<?php
    if ($e_commerce_enabled <> 0) {
    ?>
        <tr>
        <td width="10%"><?php echo get_lang('AttachToCatalogueOfProducts'); ?></td>
        <td width="90%"><input id="payment" type="checkbox" checked="checked" value="1" name="payment"></td>
        </tr>
    <?php
}else{
    ?>
    <input id="payment" type="hidden"  value="0" name="payment">
    <?php
}
?>
<tr>
<td>&nbsp;</td>
<td>
    <!--<button id="add_training_id" onClick="xajax_course_create(document.getElementById('name').value, document.getElementById('course_language').value, document.getElementById('payment').value);" type="button" class="save"  value="<?php //echo get_lang('CreateCourseArea') ?>"><?php //echo get_lang('CreateCourseArea') ?></button>-->
    <button id="add_training_id"  onClick="xajax_course_create(xajax.getFormValues('add_course'));return false;" type="button" class="save"  value="<?php echo get_lang('CreateCourseArea') ?>"><?php echo get_lang('CreateCourseArea') ?></button>
</td>
</tr>

</table>
</form>

</div>

<?php
//Language setting to display advertising image
$user_selected_language = api_get_interface_language();
if (!isset($user_selected_language)) {
$user_selected_language = api_get_setting('platformLanguage');
}
$trainer_language = 'en';
if ($user_selected_language == "french") {
$trainer_language = 'fr';
} else if ($user_selected_language == "spanish") {
$trainer_language = 'es';
}
echo '<div class="advertising_main hide_in_upload">
'. Display::return_icon('blocks/trainer_' . $trainer_language . '.jpg',  get_lang('dokeos')).'

<a class="buttons_download_' . $trainer_language . '" href="http://www.dokeos.com" target="_blank"></a>


</div>';
echo '</div>';
// display the footer
Display :: display_footer();

//function course_create($name, $course_language, $payment_value){
function course_create($form){
    $xajax_response = new XajaxResponse();
    $title = trim($form['name']);
    $course_language = $form['course_language'];
    $payment_value = 0;
    if(isset($form['payment'])){
        $payment_value = 1;
    }
    if(empty($title)){          
        $xajax_response->addScript("xajax.$('name').focus();" );
    }else{                  
        $xajax_response -> addAssign('form_create_course','innerHTML','<div style="text-align: center;width:100%"><img style=" margin: 50px auto auto;" src="'.api_get_path(WEB_CODE_PATH).'img/progress_bar.gif" /></div>');
        $xajax_response->addScriptCall('xajax_prepare_course', $title, $course_language, $payment_value); 
    }	
    return $xajax_response;
    
}

function prepare_course($title, $course_language, $payment_value){
    global $_configuration, $_user, $charset;
    $xajax_response = new XajaxResponse();
            
    $table_course = Database :: get_main_table(TABLE_MAIN_COURSE);    
    $tutor_name = api_get_person_name($_user['firstName'], $_user['lastName'], null, null, $course_language);
    $res = Database::query("SELECT code FROM ".Database::get_main_table(TABLE_MAIN_CATEGORY)." WHERE parent_id IS NULL ORDER BY code LIMIT 1", __FILE__, __LINE__);
    $category_code = Database::result($res, 0);
    $course_language = $course_language;
    $payment = $payment_value;
    $dbnamelength = strlen($_configuration['db_prefix']);
    //Ensure the database prefix + database name do not get over 40 characters
    $maxlength = 40 - $dbnamelength;    
    $wanted_code = generate_course_code(api_substr($title,0,$maxlength));
    $keys = define_course_keys($wanted_code, "", $_configuration['db_prefix']);
    $sql_check = sprintf('SELECT * FROM '.$table_course.' WHERE visual_code = "%s"',Database :: escape_string($wanted_code));      
    $result_check = Database::query($sql_check,__FILE__,__LINE__); //I don't know why this api function doesn't work...

    if ( Database::num_rows($result_check)<1 ) {
        if (sizeof($keys)) {
            $visual_code = $keys["currentCourseCode"];
            $code = $keys["currentCourseId"];
            $db_name = $keys["currentCourseDbName"];
            $directory = $keys["currentCourseRepository"];
            $expiration_date = time() + $firstExpirationDelay;
            prepare_course_repository($directory, $code);
            update_Db_course($db_name);
            $pictures_array=fill_course_repository($directory);
            fill_Db_course($db_name, $directory, $course_language,$pictures_array);
	    register_course($code, $visual_code, $directory, $db_name, $tutor_name, $category_code, $title, $course_language, api_get_user_id(), $expiration_date,$teachers,$payment);
        }
        $link = api_get_path(WEB_COURSE_PATH).$directory.'/';                 
        $xajax_response -> addRedirect($link);
    } else {
        $link = api_get_path(WEB_CODE_PATH).'create_course/add_course.php';    
       
        $xajax_response -> addAssign('form_create_course','innerHTML', get_lang('CourseCodeAlreadyExists'). ' <a style="text-decoration: underline;" href="'.$link.'">Click here</a>');
        
        
    }
    return $xajax_response;
}
