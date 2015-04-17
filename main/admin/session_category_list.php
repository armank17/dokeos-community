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
$help_content = 'platformadministrationsessioncategorylist';

// including the global Dokeos file
require_once '../inc/global.inc.php';

// including additional libraries
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
require_once (api_get_path(LIBRARY_PATH).'sessionmanager.lib.php');

// setting the section (for the tabs)
$this_section = SECTION_PLATFORM_ADMIN;

// Access restrictions
api_protect_admin_script(true);

// setting breadcrumbs
$interbreadcrumb[]=array("url" => "index.php","name" => get_lang('PlatformAdmin'));

// additional javascript, css, ...
$htmlHeadXtra[] = '<script type="text/javascript">
    $(document).ready(function(){
       $("#btn-search").click(function() {
            if ($("#search").css("display") == "none") {
                $("#keyword").val("");
                $("#search").show();
            } else {
                $("#search").hide()
            }
       });
    });
</script>';

// additional javascript, css, ...
$htmlHeadXtra[] = '<script language="javascript">
                        function selectAll(idCheck,numRows,action) {
                            for(i=0;i<numRows;i++) {
                                idcheck = document.getElementById(idCheck+"_"+i);
                                if (action == "true"){
                                    idcheck.checked = true;
                                } else {
                                    idcheck.checked = false;
                                }
                            }
                        }
                    </script>';

// Database table definitions
$tbl_session_category = Database::get_main_table(TABLE_MAIN_SESSION_CATEGORY);
$tbl_session          = Database::get_main_table(TABLE_MAIN_SESSION);

// variable handling
$page = intval($_GET['page']);
$action = Security::remove_XSS($_REQUEST['action']);
$sort = in_array($_GET['sort'], array('name', 'nbr_session', 'date_start', 'date_end')) ? Security::remove_XSS($_GET['sort']) : 'name';
if (isset($_REQUEST['type']) && $_REQUEST['type'] == 'ASC') {
    $sort_type = $_REQUEST['type'];
    $sort_link = 'DESC';
} elseif (isset($_REQUEST['type']) && $_REQUEST['type'] == 'DESC') {
    $sort_type = $_REQUEST['type'];
    $sort_link = 'ASC';
} else {
    $sort_type = 'ASC';
    $sort_link = 'DESC';
}
$idChecked = Security::remove_XSS($_REQUEST['idChecked']);
$cond_url = '';
if ($action == 'delete_on_session' || $action == 'delete_off_session') {
    $delete_session = ($action == 'delete_on_session') ? true : false;
    SessionManager::delete_session_category($idChecked, $delete_session);
    header('Location: ' . api_get_self() . '?sort=' . $sort . '&action=show_message&message=' . urlencode(get_lang('SessionCategoryDelete')));
    exit();
}

//table for the search
if (isset ($_GET['search']) && $_GET['search'] == 'advanced') {

    $interbreadcrumb[] = array ("url" => 'session_category_list.php', "name" => get_lang('ListSessionCategory'));
    // Displaying the header
    $tool_name = get_lang('SearchASession');
    Display :: display_header(get_lang('SearchASession'));

    echo '<div class="actions">';
    echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/session_add.php">' . Display :: return_icon('pixel.gif', get_lang('AddSession'), array('class' => 'toolactionplaceholdericon toolactionadd')) . get_lang('AddSession') . '</a>';
    echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/session_category_list.php">' . Display :: return_icon('pixel.gif', get_lang('ListSessionCategory'), array('class' => 'toolactionplaceholdericon toolactioncatalogue')) . get_lang('ListSessionCategory') . '</a>';
    echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/session_export.php">' . Display::return_icon('pixel.gif', get_lang('ExportSessionListXMLCSV'), array('class' => 'toolactionplaceholdericon toolactionexportcourse')) . get_lang('ExportSessionListXMLCSV') . '</a>';
    echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/session_import.php">' . Display::return_icon('pixel.gif', get_lang('ImportSessionListXMLCSV'), array('class' => 'toolactionplaceholdericon toolactionimportcourse')) . get_lang('ImportSessionListXMLCSV') . '</a>';
    echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'coursecopy/copy_course_session.php">' . Display::return_icon('pixel.gif', get_lang('CopyFromCourseInSessionToAnotherSession'), array('class' => 'toolactionplaceholdericon toolsettings')) . get_lang('CopyFromCourseInSessionToAnotherSession') . '</a>';
    echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/session_category_add.php">' . Display::return_icon('pixel.gif', get_lang('AddSession'), array('class' => 'toolactionplaceholdericon toolactioncreatefolder')) . get_lang('AddSessionCategory') . '</a>';
    echo '<a href="javascript:void(0)" id="btn-search">' . Display :: return_icon('pixel.gif', get_lang('Search'), array('class' => 'toolactionplaceholdericon toolactionsearch')) . get_lang('Search') . '</a>';
    echo '</div>';

    // start the content div
    echo '<div id="content">';

    // creating the form
    $form = new FormValidator('advanced_search', 'get');
    $form->addElement('header', '', $tool_name);
    $active_group = array();
    $active_group[] = $form->createElement('checkbox', 'active', '', get_lang('Active'));
    $active_group[] = $form->createElement('checkbox', 'inactive', '', get_lang('Inactive'));
    $form->addGroup($active_group, '', get_lang('ActiveSession'), '<br/>', false);

    $form->addElement('style_submit_button', 'submit', get_lang('SearchUsers'), 'class="search"');
    $defaults['active'] = 1;
    $defaults['inactive'] = 1;
    $form->setDefaults($defaults);

    // displaying the form
    $form->display();
    echo '</div>';

} else {
    $limit = 20;
    $from = $page * $limit;

    //if user is crfp admin only list its sessions
    if (!api_is_platform_admin()) {
        $where .= (empty($_REQUEST['keyword']) ? " " : " WHERE name LIKE '%" . addslashes($_REQUEST['keyword']) . "%'");
    } else {
        $where .= (empty($_REQUEST['keyword']) ? " " : " WHERE name LIKE '%" . addslashes($_REQUEST['keyword']) . "%'");
    }

    $query = "SELECT sc.*, (select count(id) FROM $tbl_session WHERE session_category_id = sc.id) as nbr_session
              FROM $tbl_session_category sc
              $where
              ORDER BY $sort $order
              LIMIT $from,".($limit+1);

    $query_rows = "SELECT count(*) as total_rows
                   FROM $tbl_session_category sc $where ";

    $order = ($order == 'ASC')? 'DESC': 'ASC';
    $result_rows = Database::query($query_rows, __FILE__, __LINE__);
    $recorset = Database::fetch_array($result_rows);
    $num = $recorset['total_rows'];
    $result = Database::query($query, __FILE__, __LINE__);
    $Sessions_rs = Database::store_result($result);
    $nbr_results = count($Sessions_rs);

    // Display the header
    Display::display_header(get_lang('ListSessionCategory'));

    // Display the tool title
    // api_display_tool_title(get_lang('ListSessionCategory'));

    if (!empty($_GET['warn'])) {
        //Display::display_warning_message(urldecode($_GET['warn']), false, true);
        $_SESSION['display_warning_message']=urldecode($_GET['warn']);
    }
    if(isset($_GET['action'])) {
        //Display::display_confirmation_message(stripslashes($_GET['message']), false, true);
        $_SESSION['display_confirmation_message']=stripslashes($_GET['message']);
    }
    echo '<div class="actions">';
    echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/session_add.php">' . Display :: return_icon('pixel.gif', get_lang('AddSession'), array('class' => 'toolactionplaceholdericon toolactionadd')) . get_lang('AddSession') . '</a>';
    echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/session_list.php">' . Display :: return_icon('pixel.gif', get_lang('SessionList'), array('class' => 'toolactionplaceholdericon toolactionsession')) . get_lang('SessionList') . '</a>';
//    echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/session_category_list.php">' . Display :: return_icon('pixel.gif', get_lang('ListSessionCategory'), array('class' => 'toolactionplaceholdericon toolactioncatalogue')) . get_lang('ListSessionCategory') . '</a>';
    echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/session_export.php">' . Display::return_icon('pixel.gif', get_lang('ExportSessionListXMLCSV'), array('class' => 'toolactionplaceholdericon toolactionexportcourse')) . get_lang('ExportSessionListXMLCSV') . '</a>';
    echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/session_import.php">' . Display::return_icon('pixel.gif', get_lang('ImportSessionListXMLCSV'), array('class' => 'toolactionplaceholdericon toolactionimportcourse')) . get_lang('ImportSessionListXMLCSV') . '</a>';
    echo '<a href="'.api_get_path(WEB_CODE_PATH) . 'admin/session_users_unsubscribe.php">' . Display::return_icon('pixel.gif', get_lang('UnsubscribeSessionUsers'), array('class' => 'toolactionplaceholdericon UnsubscribeSessionUsers')) . get_lang('UnsubscribeSessionUsers') . '</a>';
    echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'coursecopy/copy_course_session.php">' . Display::return_icon('pixel.gif', get_lang('CopyFromCourseInSessionToAnotherSession'), array('class' => 'toolactionplaceholdericon toolsettings')) . get_lang('CopyFromCourseInSessionToAnotherSession') . '</a>';
    echo '<a href="' . api_get_path(WEB_CODE_PATH) . 'admin/session_category_add.php">' . Display::return_icon('pixel.gif', get_lang('AddSession'), array('class' => 'toolactionplaceholdericon toolactioncreatefolder')) . get_lang('AddSessionCategory') . '</a>';
    echo '</div>';

   // start the content div
    echo '<div id="content">';
/*
 * ===============================================
 * DISPLAY MESSAGE
 * ===============================================
 */
if(isset($_SESSION['display_normal_message'])){
display::display_normal_message($_SESSION['display_normal_message'], false,true);
unset($_SESSION['display_normal_message']);
}
if(isset($_SESSION['display_warning_message'])){
display::display_warning_message($_SESSION['display_warning_message'], false,true);
unset($_SESSION['display_warning_message']);
}
if(isset($_SESSION['display_confirmation_message'])){
display::display_confirmation_message($_SESSION['display_confirmation_message'], false,true);
unset($_SESSION['display_confirmation_message']);
}
if(isset($_SESSION['display_error_message'])){
display::display_error_message($_SESSION['display_error_message'], false,true);
unset($_SESSION['display_error_message']);
}
    echo '<form method="post" action="' . api_get_self() . '?action=delete&sort=' . $sort . '" onsubmit="javascript:if(!confirm(\'' . get_lang('ConfirmYourChoice') . '\')) return false;">';    
    if (count($Sessions_rs) == 0) {
            echo  (isset($_REQUEST['keyword'])) ?  get_lang('NoSearchResults') : get_lang('NoCategories') ;         
            echo '</div>';
    } else {
        if ($num > $limit) {
            if ($page) {
?>
                    <a href="<?php echo api_get_self(); ?>?page=<?php echo $page - 1; ?>&sort=<?php echo $sort; ?>&order=<?php echo  Security::remove_XSS($_REQUEST['order']); ?>&keyword=<?php echo $_REQUEST['keyword']; ?><?php echo @$cond_url; ?>"><?php echo get_lang('Previous'); ?></a>
<?php
            } else {
                echo get_lang('Previous');
            }
?>
                |
<?php
            if($nbr_results > $limit) {
?>
                    <a href="<?php echo api_get_self(); ?>?page=<?php echo $page + 1; ?>&sort=<?php echo $sort; ?>&order=<?php echo  Security::remove_XSS($_REQUEST['order']); ?>&keyword=<?php echo $_REQUEST['keyword']; ?><?php echo @$cond_url; ?>"><?php echo get_lang('Next'); ?></a>
<?php
            } else {
                echo get_lang('Next');
            }
        }
?>
		<table class="data_table" width="100%">
		<tr>
		  <th>&nbsp;</th>
		  <th><a href="<?php echo api_get_self(); ?>?sort=name&order=<?php echo ($sort == 'name')? $order: 'ASC'; ?>"><?php echo get_lang('SessionCategoryName'); ?></a></th>
		  <th><a href="<?php echo api_get_self(); ?>?sort=nbr_session&order=<?php echo ($sort == 'nbr_session')? $order: 'ASC'; ?>"><?php echo get_lang('NumberOfSession'); ?></a></th>
		  <th><a href="<?php echo api_get_self(); ?>?sort=date_start&order=<?php echo ($sort == 'date_start')? $order: 'ASC'; ?>"><?php echo get_lang('StartDate'); ?></a></th>
		  <th><a href="<?php echo api_get_self(); ?>?sort=date_end&order=<?php echo ($sort == 'date_end')? $order: 'ASC'; ?>"><?php echo get_lang('EndDate'); ?></a></th>
                  <th><?php echo get_lang('Coachs'); ?></th>
		  <th><?php echo get_lang('Actions'); ?></th>
		</tr>

<?php
        $i = 0;
        $x = 0;
        foreach ($Sessions_rs as $key => $enreg) {
            if ($key == $limit) {
                break;
            }
            $tutors = SessionManager::get_session_category_tutors($enreg['id']);
            $tutors_names = array();
            if (!empty($tutors)) {
                foreach ($tutors as $tutor) {
                    $tutors_names[] = api_htmlentities(api_get_person_name($tutor['firstname'], $tutor['lastname']), ENT_QUOTES, $charset);
                }
            }
            $tutor_info = api_get_user_info($enreg['tutor_id']);
            $sql = 'SELECT COUNT(session_category_id) FROM ' . $tbl_session . ' WHERE session_category_id = ' . intval($enreg['id']);
            $rs = Database::query($sql, __FILE__, __LINE__);
            list($nb_courses) = Database::fetch_array($rs);
?>
		<tr class="<?php echo $i ? 'row_odd' : 'row_even'; ?>">
		  <td><input type="checkbox" id="idChecked_<?php echo $x; ?>" name="idChecked[]" value="<?php echo $enreg['id']; ?>"></td>
		  <td align="center"><?php echo api_htmlentities($enreg['name'], ENT_QUOTES, $charset); ?></td>
                  <?php $session_display = ($nb_courses == 1 ? get_lang('Session') : get_lang('Sessions')); ?>
		  <td align="center"><?php echo "<a href=\"session_list.php?id_category=" . $enreg['id'] . "\">" . $nb_courses . " " . $session_display . " </a>"; ?></td>
		  <td align="center"><?php echo api_htmlentities($enreg['date_start'], ENT_QUOTES, $charset); ?></td>
		  <td align="center"><?php echo api_htmlentities($enreg['date_end'], ENT_QUOTES, $charset); ?></td>
                  <td align="center"><?php echo !empty($tutors_names)?implode(' | ', $tutors_names):''; ?></td>
		  <td align="center">
			<a href="session_category_edit.php?&id=<?php echo $enreg['id']; ?>"><?php Display::display_icon('pixel.gif', get_lang('Edit'), array('class'=>'actionplaceholdericon actionedit')); ?></a>
			<a href="<?php echo api_get_self(); ?>?sort=<?php echo $sort; ?>&action=delete_off_session&idChecked=<?php echo $enreg['id']; ?>" onclick="javascript:if(!confirm('<?php echo get_lang('ConfirmYourChoice'); ?>')) return false;"><?php Display::display_icon('delete.png', get_lang('Delete')); ?></a>
		  </td>
		</tr>
<?php
            $i = $i ? 0 : 1;
            $x++;
        }
        unset($Sessions_rs);
?>
		</table>
		<br />
		<div align="left">
<?php
        if ($num > $limit) {
            if ($page) {
?>
			<a href="<?php echo api_get_self(); ?>?page=<?php echo $page - 1; ?>&sort=<?php echo $sort; ?>&order=<?php echo  Security::remove_XSS($_REQUEST['order']); ?>&keyword=<?php echo $_REQUEST['keyword']; ?><?php echo @$cond_url; ?>"><?php echo get_lang('Previous'); ?></a>
<?php
            } else {
                echo get_lang('Previous');
            }
?>
			|
<?php
            if ($nbr_results > $limit) {
?>
			<a href="<?php echo api_get_self(); ?>?page=<?php echo $page + 1; ?>&sort=<?php echo $sort; ?>&order=<?php echo  Security::remove_XSS($_REQUEST['order']); ?>&keyword=<?php echo $_REQUEST['keyword']; ?><?php echo @$cond_url; ?>"><?php echo get_lang('Next'); ?></a>
<?php
            } else {
                echo get_lang('Next');
            }
        }
?>
		</div>
		<br />
		<a href="#" onclick="selectAll('idChecked', <?php echo $x; ?>, 'true');return false;"><?php echo get_lang('SelectAll') ?></a>&nbsp;-&nbsp;
		<a href="#" onclick="selectAll('idChecked', <?php echo $x; ?>, 'false');return false;"><?php echo get_lang('UnSelectAll') ?></a>
		<select name="action">
                    <option value="delete_off_session" selected="selected"><?php echo get_lang('DeleteSelectedSessionCategory'); ?></option>
                    <option value="delete_on_session"><?php echo get_lang('DeleteSelectedFullSessionCategory'); ?></option>
		</select>
		<button class="save" type="submit" name="name" value="<?php echo get_lang('Ok') ?>"><?php echo get_lang('Ok') ?></button>
<?php } ?>
	</table>
<?php
}
// close the content div
echo '</div>';
// display the footer
Display::display_footer();