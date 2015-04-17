<?php

/* For licensing terms, see /dokeos_license.txt */

// name of the language file that needs to be included
$language_file = 'document';

// including the global dokeos file
require_once '../inc/global.inc.php';

// including additional libraries
require_once (api_get_path ( LIBRARY_PATH ) . 'formvalidator/FormValidator.class.php');
include_once (api_get_path(LIBRARY_PATH).'groupmanager.lib.php');
require_once api_get_path(LIBRARY_PATH) . 'fileDisplay.lib.php';
require_once api_get_path(LIBRARY_PATH).'document.lib.php';
include_once ('document.inc.php');
$is_allowed_to_edit = api_is_allowed_to_edit(null,true);
// access rights
api_protect_course_script();

// breadcrumbs
$interbreadcrumb[] = array('url' => 'document.php', 'name' => get_lang('Document'));
$interbreadcrumb[] = array('url' => 'search.php', 'name' => get_lang('DocumentSearch'));

// Display the header
Display::display_tool_header();

// tool introduction
Display::display_introduction_section(TOOL_DOCUMENT);
// actions
if (isset($_GET['curdirpath']) && $_GET['curdirpath'] != '') {
    $curdirpath = Security::remove_XSS($_GET['curdirpath']);
    $curdirpathurl = '&amp;curdirpath=' . $_GET['curdirpath'];
} elseif (isset($_POST['curdirpath']) && $_POST['curdirpath'] != '') {
    $curdirpath = Security::remove_XSS($_POST['curdirpath']);
    $curdirpathurl = '&amp;curdirpath=' . $_POST['curdirpath'];
} else {
    $curdirpath = '/';
    $curdirpathurl = '';
}
echo '<div class="actions" style="min-height: 40px;">';
DocumentManager::show_li_eeight($_GET['document'],$_GET['gidReq'],$_GET['curdirpath'],$curdirpath,$group_properties['directory'],$image_present,'search',$file);
//echo '<a href="document.php?' . api_get_cidreq() . $curdirpathurl . '">' . Display::return_icon('pixel.gif', get_lang('Documents'), array('class' => 'toolactionplaceholdericon toolactionback')) . ' ' . get_lang('Documents') . '</a>';
echo '</div>';

// Start main content
echo '<div id="content">';

// tracking
event_access_tool(TOOL_DOCUMENT);

// forum search
document_search();

// End main content
echo '</div>';

// bottom actions

echo '<div class="actions">';
    DocumentManager::show_simplifying_links(true, true);
echo  '</div>';

// footer
//Display::display_footer();

/**
 * Display the search form for the documents and display the search results
 *
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 * @version May 2008
 */
function document_search() {
    global $curdirpath;
    // initiate the object
    $form = new FormValidator('documentsearch', 'get', api_get_self() . '?' . api_get_cidreq(),'',array('Style'=>'width:630px;'));
    // settting the form elements
    $form->addElement('header', '', get_lang('DocumentSearch'));
    $form->addElement('text', 'search_term', get_lang('SearchTerm'), 'class="input_titles" Style=width:500px');
    $form->addElement('static', 'search_information', '', get_lang('DocumentSearchInformation'));

    $form->addElement('hidden', 'curdirpath', $curdirpath);
    $form->addElement('style_submit_button', 'submit', get_lang('Search'), 'class="search"');

    // setting the rules
    $form->addRule('search_term', '<div class="required">' . get_lang('ThisFieldIsRequired') . '</div>', 'required');
    $form->addRule('search_term', get_lang('TooShort'), 'minlength', 3);

    // The validation or display
    if ($form->validate()) {
       $values = $form->exportValues();
       $form->display();

       // display the search results
       display_document_search_results($values['search_term']);
    } else {
        $form->display();
    }
}

/**
 * Display the search results
 *
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 * @version May 2008
 * 
 */
function display_document_search_results($search_term) {
    global $_user, $_course, $curdirpath; 

    // Database table definitions
    $table_document  = Database::get_course_table(TABLE_DOCUMENT);
    $table_item_prop = Database::get_course_table(TABLE_ITEM_PROPERTY);
    $table_group     = Database::get_course_table(TABLE_GROUP);

    // getting all the groups of the user
    $groups_of_user = GroupManager::get_mygroups($_user['user_id']);
    foreach ($groups_of_user as $key => $group) {
        $groupkeys_of_user[] = $group['id'];
    }
    // getting all the groups
    $sql = 'SELECT * FROM ' . $table_group;
    $result = api_sql_query($sql, __FILE__, __LINE__);
    while ($row = mysql_fetch_assoc($result)) {
        $groups[$row['id']] = $row;
    }	
    // defining the search strings as an array
    if (strstr($search_term, '+')) {
        $search_terms = explode('+', $search_term);
    } else {
        $search_terms[] = $search_term;
    }	

    // search restriction
    foreach ($search_terms as $key => $value) {
        $search_restriction[] = "(document.path LIKE '%".Database::escape_string(trim($value))."%' 
                                 OR document.title LIKE '%".Database::escape_string(trim($value))."%'
                                 OR document.comment LIKE '%".Database::escape_string(trim($value))."%')";
    }
    $hidden_folders = array();
    $sql = "SELECT * FROM $table_document document, $table_item_prop item_property
            WHERE document.id = item_property.ref
            AND item_property.tool = 'document' 
            AND visibility <> '2' 
            AND ".implode(' AND ',$search_restriction)."
            GROUP BY document.id ORDER BY document.path";
    $result = Database::query($sql, __FILE__, __LINE__);
    while ($row = Database::fetch_array($result, 'ASSOC')) {
        //debug($row['path'] . ' => ' . $row['visibility']);
        if ($row['filetype'] == 'folder' && $row['visibility'] == 0) {
            $hidden_folders[] = $row['path'] . '/';
        }
        $display_result = false; 
        /*
                we only show it when
                1. document is visible for everybody
                2. document is visible for the user
                3. document is visible for a group of the user
        */
        if (!api_is_allowed_to_edit()) {
            // document visible
            if ($row['visibility'] == '1') {
                // document visible for everybody
                if ($row['to_group_id'] == 0 && $row['to_user_id'] == 0) {
                    $display_result = true;
                }
                // document visible for this user
                if ($row['to_group_id'] == 0 && is_numeric($row['to_user_id']) && $row['to_user_id'] == $_user['user_id']) {
                    $display_result = true;
                }
                // document visible for a group
                if ($row['to_group_id'] > 0 && is_null($row['to_user_id'])) {
                    // the user is member of the group => display
                    if (in_array($row['to_group_id'], $groupkeys_of_user)) {
                        $display_result = true;
                    } else {
                        // the user is not a member of the group => check the settings of the documents of the group
                        if ($groups[$row['to_group_id']]['doc_state'] == 1) {
                            $display_result = true;
                        } else {
                            $display_result = false;
                        }
                    }
                }

                foreach ($hidden_folders as $hidden) {
                    if (strpos($row['path'], $hidden) !== false && strpos($row['path'], $hidden) == 0) {
                            $display_result = false;
                    }
                    //debug(strpos($row['path'], $hidden));
                }
            }
        } else {
            $display_result = true; 
        }

        if ($display_result == true) {
            $search_results_item = '<li style="margin: 10px;">';
            $name_filter_parameters[0] = $row['filetype'];
            $search_results_item .= create_document_link('/', api_get_path('WEB_COURSE_PATH') . $_course['path'] . '/document', $row['title'], $row['path'], $row['filetype'], $row['size'], $row['visibility'], true, $row['id']);
            $search_results_item .= create_document_link('/', api_get_path('WEB_COURSE_PATH') . $_course['path'] . '/document', $row['title'], $row['path'], $row['filetype'], $row['size'], $row['visibility'], false, $row['id']);
            $search_results_item .= '<br />';
            // due to api_get_self() we have to replace search.php with document.php
            $search_results_item = str_replace('search.php', 'document.php', $search_results_item);
            // because it contains style definitions we have to remove te float: left
            $search_results_item = str_replace('float:left', '', $search_results_item);
            if (strlen($row['comment']) > 200 ) {
                $search_results_item .= substr(strip_tags($row['comment']), 0, 200) . '...';
            } else {
                $search_results_item .= $row['comment'];
            }
            $search_results_item .= '</li>';

            $search_results[] = $search_results_item;
        }
    }
    echo '<div class="row"><div class="form_header">' . count($search_results) . ' ' . get_lang('DocumentSearchResults') . '</div></div>';
    echo '<div class="scroll-pane" style="max-height:400px;margin:20px 40px 20px 0">';
    echo '<ol style="list-style-type: none;">';
    echo implode($search_results);
    echo '</ol>';
    echo '</div>';
    echo '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery.jscrollpane.js"></script>';
    echo '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery.mousewheel.js"></script>';
    echo "<script type=" . '"text/javascript"' . ">
              $(function() {
                  $('div.scroll-pane').jScrollPane();                      
              });
          </script>";
}