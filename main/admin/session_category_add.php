<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* @package dokeos.admin
*/

// name of the language file that needs to be included
$language_file = array ('registration','admin');

// resetting the course id
$cidReset = true;

// setting the help
$help_content = 'platformadministrationaddsessioncategory';

// including the global Dokeos file
require ('../inc/global.inc.php');

// including additional libraries
require_once api_get_path(LIBRARY_PATH).'sessionmanager.lib.php';
require_once api_get_path(LIBRARY_PATH).'usermanager.lib.php';
require_once api_get_path(LIBRARY_PATH).'xajax/xajax.inc.php';

$htmlHeadXtra[] = '<style>html > body #content{min-height: 400px;}</style>';
// setting the section (for the tabs)
$this_section = SECTION_PLATFORM_ADMIN;

// Access restrictions
api_protect_admin_script(true);

// setting breadcrumbs
$interbreadcrumb[]=array('url' => 'index.php',"name" => get_lang('PlatformAdmin'));
$interbreadcrumb[]=array('url' => "session_category_list.php","name" => get_lang('ListSessionCategory'));

// Database Table Definitions
$tbl_user		= Database::get_main_table(TABLE_MAIN_USER);
$tbl_session	= Database::get_main_table(TABLE_MAIN_SESSION);


$xajax = new xajax();
$xajax -> registerFunction ('search_coachs');

$formSent=0;
$errorMsg='';

if ($_POST['formSent']) {
    $formSent=1;
    $return = SessionManager::create_category_session($_POST['name'], $_POST['tutors_id'], $_POST['from'], $_POST['to']);
    if ($return == strval(intval($return))) {
        header('Location: session_category_list.php?action=show_message&message='.urlencode(get_lang('SessionCategoryAdded')));
        exit();
    }
}
$thisYear=date('Y');
$thisMonth=date('m');
$thisDay=date('d');
$tool_name = get_lang('langAddACategory');

$trainers = UserManager::get_user_list(array('status'=> COURSEMANAGER));

$htmlHeadXtra[] = ' <script src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery.multiselect.js" type="text/javascript"></script>
                    <link rel="stylesheet" type="text/css" href="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery.multiselect.css"/>';
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery.validate.js"></script>';
$htmlHeadXtra[] = '<script>
        $(document).ready(function() {
        
            $("#tutors-id").multiselect({checkAllText: "' . get_lang('SelectAll') . '", uncheckAllText: "' . get_lang('UnSelectAll') . '", noneSelectedText: "' . get_lang('SelectOption') . '", selectedText: "' . '# ' . get_lang('SelectedOption') . '"});

            $("#category-form").validate({
                debug: false,
                rules: {
                    name: { required: true },
                    tutor_id: { required: true }                        
                },
                messages: {
                    name: { required: "<img src=\''.api_get_path(WEB_IMG_PATH).'exclamation.png\' title=\''.get_lang('Required').'\' />" },
                    tutor_id: { required: "<img src=\''.api_get_path(WEB_IMG_PATH).'exclamation.png\' title=\''.get_lang('Required').'\' />" }                    
                }
            });
            
            $("#from").datepicker({
                defaultDate: "+1w",
                changeMonth: true,
                numberOfMonths: 1,
                dateFormat: "yy-mm-dd",
                onClose: function( selectedDate ) {
                  $( "#to" ).datepicker( "option", "minDate", selectedDate );
                }
            });
            $("#to").datepicker({
              defaultDate: "+1w",
              changeMonth: true,
              numberOfMonths: 1,
              dateFormat: "yy-mm-dd",
              onClose: function( selectedDate ) {
                $( "#from" ).datepicker( "option", "maxDate", selectedDate );
              }
            });
        });

    </script>
    <style>
        .ui-multiselect-none .ui-icon-closethick {width:auto !important;}
    </style>';

//display the header
Display::display_header($tool_name);
echo '<div class="actions">';
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/session_category_list.php">' . Display :: return_icon('pixel.gif', get_lang('ListSessionCategory'), array('class' => 'toolactionplaceholdericon toolactioncatalogue')) . get_lang('ListSessionCategory') . '</a>';
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/session_list.php">' . Display :: return_icon('pixel.gif', get_lang('SessionList'), array('class' => 'toolactionplaceholdericon toolactionsession')) . get_lang('SessionList') . '</a>';
echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'coursecopy/copy_course_session.php">' . Display::return_icon('pixel.gif', get_lang('CopyFromCourseInSessionToAnotherSession'), array('class' => 'toolactionplaceholdericon toolsettings')) . get_lang('CopyFromCourseInSessionToAnotherSession') . '</a>';
echo '</div>';
// start the content div
echo '<div id="content">';


if (!empty($return)) {
	Display::display_error_message($return,false);
}
?>
<form method="post" id="category-form" name="form" action="<?php echo api_get_self(); ?>" style="margin:0px;">
<input type="hidden" name="formSent" value="1">
<div class="row"><div class="form_header"><?php echo $tool_name; ?></div></div>
<table border="0" cellpadding="5" cellspacing="0" width="550">
    <tr>
        <td colspan="2">
            <div id="divMessage">
            </div>
        </td>
    </tr>    
<tr>
  <td width="30%"><?php echo get_lang('SessionCategoryName') ?>&nbsp;&nbsp;<span class="sym-error"> * </span></td>
  <td width="70%"><input type="text" name="name" size="59" class="focus required" maxlength="50" value="<?php if($formSent) echo api_htmlentities($name,ENT_QUOTES,$charset); ?>"></td>
</tr>

<tr>
    <td><?php echo get_lang('CategoryTutorName') ?>&nbsp;&nbsp;<span class="sym-error"> * </span></td>
    <td>
        <select name="tutors_id[]" id="tutors-id" multiple="multiple" class="required" style="width:270px">        
            <?php if (!empty($trainers)): ?>
                <?php foreach ($trainers as $trainer): ?>
                    <option value="<?php echo $trainer['user_id']; ?>"><?php echo api_get_person_name($trainer['firstname'], $trainer['lastname']) . ' (' . $trainer['username'] . ')'; ?></option>
                <?php endforeach; ?>            
            <?php endif; ?>           
        </select>
    </td>
</tr>

<tr>
<td colspan="2">
	<a class="AddTimeLimit" href="javascript://" onclick="if(document.getElementById('options').style.display == 'none'){document.getElementById('options').style.display = 'block';}else{document.getElementById('options').style.display = 'none';}"><?php echo get_lang('AddTimeLimit') ?></a>
		<div style="display: <?php if($formSent && ($nb_days_acess_before!=0 || $nb_days_acess_after!=0)) echo 'block'; else echo 'none'; ?>;" id="options">
	<br><br>
	<div>
<table border="0" cellpadding="5" cellspacing="0" width="100%">
<tr><td colspan="2"><?php echo get_lang('TheTimeLimitsAreReferential');?></td>
</tr>
<tr>
  <td width="20%"><?php echo get_lang('DateStart') ?>&nbsp;&nbsp;</td>
  <td width="80%">
      <input type="text" id="from" name="from" />
  </td>
</tr>
<tr>
  <td width="20%"><?php echo get_lang('DateEnd') ?>&nbsp;&nbsp;</td>
  <td width="80%">
      <input type="text" id="to" name="to" />
  </td>
</tr>

</table>
</div>
<br>
</div>
</td>
</tr>
<tr>
  <td>&nbsp;</td>
  <td><div class="pull-bottom"><button type="submit" class="save" value="<?php echo get_lang('Submit') ?>"><?php echo get_lang('Submit') ?></button></div>
 </td>
</tr>

</table>

</form>
<?php
// close the content div
echo '</div>';

// display the footer
Display::display_footer();