<?php

/* For licensing terms, see /license.txt */

/**
 * Dropbox Model
 */
$htmlHeadXtra[] = '<script type="text/javascript" src="' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/jquery-1.7.2.min.js" language="javascript"></script>';
if ($_REQUEST['action'] != 'add') {
    $htmlHeadXtra[] = "<script>
        $(function() {
            $('#StoreCategory').click(function(e) {
                if ($('#category_name').val() == '') {
                    e.preventDefault();
                    $('#category_name').focus();
                } else
                    $('#outer_form').submit();
            });
        });
    </script>";
} else {
    $htmlHeadXtra[] = '<script src="../../../document/MultiUploadFiles.js"></script>';
    $htmlHeadXtra[] = "<script>
        $(function() {
            if (typeof FormData !== 'undefined')
                $('#draganddrophandler').DragandDrop('*', 100, '" . api_get_path(WEB_PATH) . "main/document/uploadFiles.php?" . api_get_cidreq() . "&dir=/dropbox');
            else {
                $('#draganddrophandler').remove();
                $('#dynamicInput').DinamicUpload('*', 3);
            }
        });
    </script>";
}

class DropboxModel {

    // definition tables
    protected $tableDropboxCategory;
    protected $attributes = Array();

    /**
     * Magic method 
     */
    public function __get($key) {
        return array_key_exists($key, $this->attributes) ? $this->attributes[$key] : null;
    }

    /**
     * Magic method 
     */
    public function __set($key, $value) {
        $this->attributes[$key] = $value;
    }

    /**
     * Constructor
     */
    public function __construct($courseDb = '') {
        $this->tableDropboxCategory = Database :: get_course_table(TABLE_DROPBOX_CATEGORY, $courseDb);
    }

    public function get_dropbox_categories($filter = '') {
        global $_user;
        global $dropbox_cnf;

        $return_array = array();

        $session_id = api_get_session_id();
        //$condition_session = api_get_session_condition($session_id, true, true);
        //$condition_session = ($session_id >0)? ' AND (category.session_id='.$session_id.' OR file.session_id = 0)' : ' AND COALESCE(category.session_id,0) = '.$session_id;
        $condition_session = " AND COALESCE(category.session_id,$session_id) = $session_id";

        //$sql = "SELECT * FROM " . $dropbox_cnf['tbl_category'] . " WHERE user_id='" . $_user['user_id'] . "' $condition_session";
        $sql = "SELECT category.cat_id, cat_name, received, sent, user_id, file.session_id, 
                SUM(COALESCE(filesize, 0)) as sum_filesize
                FROM " . $dropbox_cnf['tbl_category'] . " category
                LEFT JOIN " . $dropbox_cnf['tbl_file'] . " file 
                ON (file.cat_id=category.cat_id)
                WHERE user_id='" . $_user['user_id'] . "' $condition_session
                GROUP BY cat_name";



        $result = Database::query($sql, __FILE__, __LINE__);
        while ($row = Database::fetch_array($result)) {
            if (($filter == 'sent' AND $row['sent'] == 1) OR ($filter == 'received' AND $row['received'] == 1) OR $filter == '') {
                $return_array[$row['cat_id']] = $row;
            }
        }


        return $return_array;
    }

    /**
     * @desc This function retrieves the number of feedback messages on every document. This function might become obsolete when
     * 		the feedback becomes user individual.
     * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
     * @version march 2006
     */
    public function get_total_number_feedback($file_id = '') {
        global $dropbox_cnf;

        $sql = "SELECT COUNT(feedback_id) AS total, file_id FROM " . $dropbox_cnf['tbl_feedback'] . " GROUP BY file_id";
        $result = Database::query($sql, __FILE__, __LINE__);
        while ($row = Database::fetch_array($result)) {
            $return[$row['file_id']] = $row['total'];
        }
        return $return;
    }

    public function getReceivedDropboxData() {
        global $_user, $is_courseAdmin, $is_courseTutor;

        if (empty($_GET['view'])) {
            $view = 'received';
        } else {
            $view = $_GET['view'];
        }

        if (isset($_GET['view_received_category']) AND $_GET['view_received_category'] <> '') {
            $view_dropbox_category_received = $_GET['view_received_category'];
        } else {
            $view_dropbox_category_received = 0;
        }

        $dropbox_categories = $this->get_dropbox_categories();
        $dropbox_person = new Dropbox_Person($_user['user_id'], $is_courseAdmin, $is_courseTutor);
        $number_feedback = $this->get_total_number_feedback();

        // the content of the sortable table = the received files
        foreach ($dropbox_person->receivedWork as $dropbox_file) {
            $dropbox_file_data = array();
            if ($view_dropbox_category_received == $dropbox_file->category) {// we only display the files that are in the category that we are in.
                $dropbox_file_data[] = $dropbox_file->id;

                if (!is_array($_SESSION['_seen'][$_course['id']][TOOL_DROPBOX])) {
                    $_SESSION['_seen'][$_course['id']][TOOL_DROPBOX] = array();
                }

                // new icon
                $new_icon = '';
                if ($dropbox_file->last_upload_date > $last_access AND !in_array($dropbox_file->id, $_SESSION['_seen'][$_course['id']][TOOL_DROPBOX])) {
                    $new_icon = '&nbsp;' . Display::return_icon('new.gif', get_lang('New'));
                }

                $dropbox_file_data[] = build_document_icon_tag('file', $dropbox_file->title);
                $dropbox_file_data[] = '<a href="../../../dropbox/dropbox_download.php?' . api_get_cidreq() . '&id=' . $dropbox_file->id . '&action=download">' . Display::return_icon('pixel.gif', get_lang('Download'), array('style' => 'float:right;', 'class' => 'actionplaceholdericon forcedownload')) . '</a><a href="../../../dropbox/dropbox_download.php?' . api_get_cidreq() . '&id=' . $dropbox_file->id . '&action=download" target="_blank">' . $dropbox_file->title . '</a>' . $new_icon . '<br>' . $dropbox_file->description;
                $dropbox_file_data[] = ceil(($dropbox_file->filesize) / 1024) . ' ' . get_lang('kB');
                $dropbox_file_data[] = $dropbox_file->author;
                //$dropbox_file_data[]=$dropbox_file->description;

                $dropbox_file_data[] = date_to_str_ago($dropbox_file->last_upload_date) . '<br><span class="dropbox_date">' . TimeZone::ConvertTimeFromServerToUser(api_get_user_id(), $dropbox_file->last_upload_date) . '</span>';

//                $action_icons = $this->check_number_feedback($dropbox_file->id, $number_feedback) . ' ' . get_lang('Feedback') . '
//							<a href="' . api_get_self() . '?' . api_get_cidreq() . '&view_received_category=' . Security::remove_XSS($_GET['view_received_category']) . '&view_sent_category=' . Security::remove_XSS($_GET['view_sent_category']) . '&view=' . Security::remove_XSS($_GET['view']) . '&action=viewfeedback&id=' . $dropbox_file->id . '">' . Display::return_icon('pixel.gif', get_lang('Comment'), array('class' => 'actionplaceholdericon actionmini_forum')) . '</a>&nbsp;&nbsp;
//							<a href="' . api_get_self() . '?' . api_get_cidreq() . '&view_received_category=' . Security::remove_XSS($_GET['view_received_category']) . '&view_sent_category=' . Security::remove_XSS($_GET['view_sent_category']) . '&view=' . Security::remove_XSS($_GET['view']) . '&action=movereceived&move_id=' . $dropbox_file->id . '">' . Display::return_icon('pixel.gif', get_lang('Move'), array('class' => 'actionplaceholdericon actionmove')) . '</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
//							<a href="' . api_get_self() . '?' . api_get_cidreq() . '&view_received_category=' . Security::remove_XSS($_GET['view_received_category']) . '&view_sent_category=' . Security::remove_XSS($_GET['view_sent_category']) . '&view=' . Security::remove_XSS($_GET['view']) . '&action=deletereceivedfile&id=' . $dropbox_file->id . '" onclick="return confirmation(\'' . $dropbox_file->title . '\');">' . Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')) . '</a>';
                $link = api_get_self() . '?' . api_get_cidreq() . '&view_received_category=' . Security::remove_XSS($_GET['view_received_category']) . '&view_sent_category=' . Security::remove_XSS($_GET['view_sent_category']) . '&view=' . Security::remove_XSS($_GET['view']) . '&action=deletereceivedfile&id=' . $dropbox_file->id;
                $action_icons = '('.$this->check_number_feedback($dropbox_file->id, $number_feedback) . ')' . '
							<a href="' . api_get_self() . '?' . api_get_cidreq() . '&view_received_category=' . Security::remove_XSS($_GET['view_received_category']) . '&view_sent_category=' . Security::remove_XSS($_GET['view_sent_category']) . '&view=' . Security::remove_XSS($_GET['view']) . '&action=viewfeedback&id=' . $dropbox_file->id . '">' . Display::return_icon('pixel.gif', get_lang('Comment'), array('class' => 'actionplaceholdericon actionmini_forum')) . '</a>&nbsp;&nbsp;
							<a href="' . api_get_self() . '?' . api_get_cidreq() . '&view_received_category=' . Security::remove_XSS($_GET['view_received_category']) . '&view_sent_category=' . Security::remove_XSS($_GET['view_sent_category']) . '&view=' . Security::remove_XSS($_GET['view']) . '&action=movereceived&move_id=' . $dropbox_file->id . '">' . Display::return_icon('pixel.gif', get_lang('Move'), array('class' => 'actionplaceholdericon actionmove')) . '</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        <a style="margin-left:-25px;" href="javascript:void(0);" onclick="Alert_Confim_Delete(\'' . $link . '\');">' . Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')) . '</a>';
                if ($_GET['action'] == 'viewfeedback' AND isset($_GET['id']) and is_numeric($_GET['id']) AND $dropbox_file->id == $_GET['id']) {
                    $action_icons.="</td></tr>\n"; // ending the normal row of the sortable table
                    $action_icons.='<tr><td colspan="2"></td><td colspan="7">' . $this->feedback($dropbox_file->feedback2) . '</td>\n</tr>\n';
                }
                if (api_get_session_id() == 0)
                    $dropbox_file_data[] = $action_icons;
                elseif (api_is_allowed_to_session_edit(false, true)) {
                    $dropbox_file_data[] = $action_icons;
                }
                $action_icons = '';

                $dropbox_file_data[] = $dropbox_file->last_upload_date; //date

                $dropbox_data_recieved[] = $dropbox_file_data;
            }
        }

        // the content of the sortable table = the categories (if we are not in the root)
        if ($view_dropbox_category_received == 0) {
            foreach ($dropbox_categories as $category) { // note: this can probably be shortened since the categories for the received files are already in the $dropbox_received_category array;
                $dropbox_category_data = array();
                if ($category['received'] == '1') {
                    $movelist[$category['cat_id']] = $category['cat_name'];
                    $dropbox_category_data[] = $category['cat_id']; // this is where the checkbox icon for the files appear
                    // the icon of the category
                    $dropbox_category_data[] = build_document_icon_tag('folder', "");
                    $dropbox_category_data[] = '<a href="../../../dropbox/dropbox_download.php?' . api_get_cidreq() . '&cat_id=' . $category['cat_id'] . '&action=downloadcategory&sent_received=received">' . Display::return_icon('pixel.gif', get_lang('Save'), array('style' => 'float:right;', 'class' => 'actionplaceholdericon forcedownload')) . '</a><a href="' . api_get_self() . '?' . api_get_cidreq() . '&view_received_category=' . $category['cat_id'] . '&view_sent_category=' . Security::remove_XSS($_GET['view_sent_category']) . '&view=' . Security::remove_XSS($_GET['view']) . '">' . $category['cat_name'] . '</a>';
                    $dropbox_category_data[] = '';
                    $dropbox_category_data[] = '';
                    $dropbox_category_data[] = '';
                    $link2 = api_get_self() . '?' . api_get_cidreq() . '&view_received_category=' . Security::remove_XSS($_GET['view_received_category']) . '&view_sent_category=' . Security::remove_XSS($_GET['view_sent_category']) . '&view=' . Security::remove_XSS($_GET['view']) . '&action=deletereceivedcategory&id=' . $category['cat_id'];
//                    $dropbox_category_data[] = '<a href="' . api_get_self() . '?' . api_get_cidreq() . '&view_received_category=' . Security::remove_XSS($_GET['view_received_category']) . '&view_sent_category=' . Security::remove_XSS($_GET['view_sent_category']) . '&view=' . Security::remove_XSS($_GET['view']) . '&action=editcategory&id=' . $category['cat_id'] . '">' . Display::return_icon('pixel.gif', get_lang('Edit'), array('class' => 'actionplaceholdericon actionedit')) . '</a>
//											  <a href="' . api_get_self() . '?' . api_get_cidreq() . '&view_received_category=' . Security::remove_XSS($_GET['view_received_category']) . '&view_sent_category=' . Security::remove_XSS($_GET['view_sent_category']) . '&view=' . Security::remove_XSS($_GET['view']) . '&action=deletereceivedcategory&id=' . $category['cat_id'] . '" onclick="return confirmation(\'' . $category['cat_name'] . '\');">' . Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')) . '</a>';
                    $dropbox_category_data[] = '<a href="' . api_get_self() . '?' . api_get_cidreq() . '&view_received_category=' . Security::remove_XSS($_GET['view_received_category']) . '&view_sent_category=' . Security::remove_XSS($_GET['view_sent_category']) . '&view=' . Security::remove_XSS($_GET['view']) . '&action=editcategory&id=' . $category['cat_id'] . '">' . Display::return_icon('pixel.gif', get_lang('Edit'), array('class' => 'actionplaceholdericon actionedit')) . '</a>
											  <a href="javascript:void(0);" onclick="Alert_Confim_Delete(\'' . $link2 . '\');">' . Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')) . '</a>';
                }
                if (is_array($dropbox_category_data) && count($dropbox_category_data) > 0) {
                    $dropbox_data_recieved[] = $dropbox_category_data;
                }
            }
        }

        return $dropbox_data_recieved;
    }

    public function getSentDropboxData() {
        global $_user, $is_courseAdmin, $is_courseTutor;

        if (empty($_GET['view'])) {
            $view = 'sent';
        } else {
            $view = $_GET['view'];
        }

        // This is for the categories
        if (isset($_GET['view_sent_category']) AND $_GET['view_sent_category'] <> '') {
            $view_dropbox_category_sent = $_GET['view_sent_category'];
        } else {
            $view_dropbox_category_sent = 0;
        }

        $dropbox_categories = $this->get_dropbox_categories();
        // object initialisation
        $dropbox_person = new Dropbox_Person($_user['user_id'], $is_courseAdmin, $is_courseTutor);
        $number_feedback = $this->get_total_number_feedback();

        foreach ($dropbox_person->sentWork as $dropbox_file) {
            if ($dropbox_file->title != '') {
                $dropbox_file_data = array();
                if ($view_dropbox_category_sent == $dropbox_file->category) {

                    $dropbox_file_data[] = $dropbox_file->id;
                    $dropbox_file_data[] = build_document_icon_tag('file', $dropbox_file->title);
                    $dropbox_file_data[] =
                            '<a href="../../../dropbox/dropbox_download.php?' . api_get_cidreq() . '&id=' . $dropbox_file->id . '&action=download">' . Display::return_icon('pixel.gif', get_lang('Download'), array('style' => 'float:right;', 'class' => 'actionplaceholdericon forcedownload')) . '</a>
                 <a href="../../../dropbox/dropbox_download.php?' . api_get_cidreq() . '&id=' . $dropbox_file->id . '&action=download" target="_blank">' . cut($dropbox_file->title, 70) . '</a><br>' . $dropbox_file->description;
                    $dropbox_file_data[] = ceil(($dropbox_file->filesize) / 1024) . ' ' . get_lang('kB');
                    foreach ($dropbox_file->recipients as $recipient) {
                        $receivers_celldata = $this->display_user_link($recipient['user_id'], $recipient['name']) . ', ' . $receivers_celldata;
                    }
                    $receivers_celldata = trim(trim($receivers_celldata), ','); // Removing the trailing comma.
                    $dropbox_file_data[] = $receivers_celldata;
                    $dropbox_file_data[] = date_to_str_ago($dropbox_file->last_upload_date) . '<br><span class="dropbox_date">' . TimeZone::ConvertTimeFromServerToUser(api_get_user_id(), $dropbox_file->last_upload_date) . '</span>';

                    //$dropbox_file_data[]=$dropbox_file->author;
                    $receivers_celldata = '';
//                $action_icons = $this->check_number_feedback($dropbox_file->id, $number_feedback) . ' ' . get_lang('Feedback') . '&nbsp;&nbsp;
//					<a href="' . api_get_self() . '?' . api_get_cidreq() . '&view_received_category=' . Security::remove_XSS($_GET['view_received_category']) . '&view_sent_category=' . Security::remove_XSS($_GET['view_sent_category']) . '&view=' . Security::remove_XSS($view) . '&action=viewfeedback&id=' . $dropbox_file->id . '">' . Display::return_icon('pixel.gif', get_lang('Comment'), array('class' => 'actionplaceholdericon actionmini_forum')) . '</a>&nbsp;
//					<a href="' . api_get_self() . '?' . api_get_cidreq() . '&view_received_category=' . Security::remove_XSS($_GET['view_received_category']) . '&view_sent_category=' . Security::remove_XSS($_GET['view_sent_category']) . '&view=' . Security::remove_XSS($view) . '&action=movesent&move_id=' . $dropbox_file->id . '">' . Display::return_icon('pixel.gif', get_lang('Move'), array('class' => 'actionplaceholdericon actionmove')) . '</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
//					<a href="' . api_get_self() . '?' . api_get_cidreq() . '&view_received_category=' . Security::remove_XSS($_GET['view_received_category']) . '&view_sent_category=' . Security::remove_XSS($_GET['view_sent_category']) . '&view=' . Security::remove_XSS($view) . '&action=deletesentfile&id=' . $dropbox_file->id . '" onclick="return confirmation(\'' . $dropbox_file->title . '\');">' . Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')) . '</a>';
                    $link3 = api_get_self() . '?' . api_get_cidreq() . '&view_received_category=' . Security::remove_XSS($_GET['view_received_category']) . '&view_sent_category=' . Security::remove_XSS($_GET['view_sent_category']) . '&view=' . Security::remove_XSS($view) . '&action=deletesentfile&id=' . $dropbox_file->id;
                    $action_icons = '('.$this->check_number_feedback($dropbox_file->id, $number_feedback) . ')&nbsp;&nbsp;
					<a href="' . api_get_self() . '?' . api_get_cidreq() . '&view_received_category=' . Security::remove_XSS($_GET['view_received_category']) . '&view_sent_category=' . Security::remove_XSS($_GET['view_sent_category']) . '&view=' . Security::remove_XSS($view) . '&action=viewfeedback&id=' . $dropbox_file->id . '">' . Display::return_icon('pixel.gif', get_lang('Comment'), array('class' => 'actionplaceholdericon actionmini_forum')) . '</a>&nbsp;
					<a href="' . api_get_self() . '?' . api_get_cidreq() . '&view_received_category=' . Security::remove_XSS($_GET['view_received_category']) . '&view_sent_category=' . Security::remove_XSS($_GET['view_sent_category']) . '&view=' . Security::remove_XSS($view) . '&action=movesent&move_id=' . $dropbox_file->id . '">' . Display::return_icon('pixel.gif', get_lang('Move'), array('class' => 'actionplaceholdericon actionmove')) . '</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<a style="margin-left:-25px " href="javascript:void(0);" onclick="Alert_Confim_Delete(\'' . $link3 . '\');">' . Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')) . '</a>';
                    // this is a hack to have an additional row in a sortable table
                    if ($_GET['action'] == 'viewfeedback' AND isset($_GET['id']) and is_numeric($_GET['id']) AND $dropbox_file->id == $_GET['id']) {
                        $action_icons.="</td></tr>\n"; // ending the normal row of the sortable table
                        $action_icons.="<tr>\n\t<td colspan=\"2\"></td><td colspan=\"7\">" . $this->feedback($dropbox_file->feedback2) . "</td>\n</tr>\n";
                    }
                    $dropbox_file_data[] = $action_icons;
                    $dropbox_file_data[] = $dropbox_file->last_upload_date;
                    $action_icons = '';
                    $dropbox_data_sent[] = $dropbox_file_data;
                }
            }
        }

        // the content of the sortable table = the categories (if we are not in the root)
        if ($view_dropbox_category_sent == 0) {
            foreach ($dropbox_categories as $category) {
                $dropbox_category_data = array();
                if ($category['sent'] == '1') {
                    $dropbox_category_data[] = $category['cat_id']; // this is where the checkbox icon for the files appear
                    $dropbox_category_data[] = build_document_icon_tag('folder', "");
                    $dropbox_category_data[] = '<a href="../../../dropbox/dropbox_download.php?' . api_get_cidreq() . '&cat_id=' . $category['cat_id'] . '&action=downloadcategory&sent_received=sent">' . Display::return_icon('pixel.gif', get_lang('Download'), array('style' => 'float:right;', 'class' => 'actionplaceholdericon forcedownload')) . '</a><a href="' . api_get_self() . '?' . api_get_cidreq() . '&view_received_category=' . Security::remove_XSS($_GET['view_received_category']) . '&view_sent_category=' . $category['cat_id'] . '&view=' . Security::remove_XSS($view) . '">' . $category['cat_name'] . '</a>';
                    //$dropbox_category_data[]='';$dropbox_file_data[] = ceil(($category['sum_filesize']) / 1024) . ' ' . get_lang('kB');
                    $dropbox_category_data[] = ceil(($category['sum_filesize']) / 1024) . ' ' . get_lang('kB'); //$category['sum_filesize'];
                    //$dropbox_category_data[]='';
                    $dropbox_category_data[] = '';
                    $dropbox_category_data[] = '';
                    $link = api_get_self() . '?' . api_get_cidreq() . '&view_received_category=' . Security::remove_XSS($_GET['view_received_category']) . '&view_sent_category=' . Security::remove_XSS($_GET['view_sent_category']) . '&view=' . Security::remove_XSS($view) . '&action=deletesentcategory&id=' . $category['cat_id'];
                    $title = get_lang("Alert");
                    $dropbox_category_data[] = '<a href="' . api_get_self() . '?' . api_get_cidreq() . '&view_received_category=' . Security::remove_XSS($_GET['view_received_category']) . '&view_sent_category=' . Security::remove_XSS($_GET['view_sent_category']) . '&view=' . Security::remove_XSS($view) . '&action=editcategory&id=' . $category['cat_id'] . '">' . Display::return_icon('pixel.gif', get_lang('Edit'), array('class' => 'actionplaceholdericon actionedit', 'style' => 'width:37px;')) . '</a>
                        <a href="javascript:void(0);" onclick="return confirmation(\'' . $category['cat_name'] . '\',\'' . $link . '\',\'' . $title . '\');">' . Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete', 'style' => 'width:37px;')) . '</a>';
                }
                if (is_array($dropbox_category_data) && count($dropbox_category_data) > 0) {
                    $dropbox_data_sent[] = $dropbox_category_data;
                }
            }
        }
        return $dropbox_data_sent;
    }

    public function getCategoryForm($action, $id) {

        global $dropbox_cnf;

        $title = get_lang('AddNewCategory');

        if (isset($id) AND $id <> '') {

            $sql = "SELECT * FROM " . $dropbox_cnf['tbl_category'] . " WHERE cat_id='" . Database::escape_string($id) . "'";
            $result = Database::query($sql, __FILE__, __LINE__);
            $row = Database::fetch_array($result);

            $category_name = $row['cat_name'];

            if ($row['received'] == '1') {
                $target = 'received';
            }
            if ($row['sent'] == '1') {
                $target = 'sent';
            }
            $title = get_lang('EditThisFolder');
        }

        if (isset($_REQUEST['action'])) {
            $action = $_REQUEST['action'];
        }
        if ($action == 'addreceivedcategory') {
            $target = 'received';
        }
        if ($action == 'addsentcategory') {
            $target = 'sent';
        }

        if ($action == 'editcategory') {
//			$text=get_lang('ModifyCategory');
            $text = get_lang('EditThisFolder');
            $class = 'save';
        } else if ($action == 'addreceivedcategory' or $action == 'addsentcategory') {
            $text = get_lang('CreateCategory');
            $class = 'add';
        }

        $form .= "<form id='outer_form' class='outer_form'  name='add_new_category' method='post' action='" . api_get_self() . "?view=" . Security::remove_XSS($_GET['view']) . "'>\n";
        if (isset($id) AND $id <> '') {
            $form .= '<input name="edit_id" type="hidden" value="' . Security::remove_XSS($id) . '">';
        }
        $token = Security::get_token();
        $form .= '<input name="sec_token" type="hidden" value="' . $token . '">';
        $form .= '<input name="action" type="hidden" value="' . Security::remove_XSS($action) . '">';
        $form .= '<input name="target" type="hidden" value="' . $target . '">';

        $form .= '<div class="row"><div class="form_header">' . $title . '</div></div>';

        $form .= '<div class="row"><div class="label"><div class="label1">' . get_lang('CategoryName') . '</div></div><div class="formw">';
        if (strtoupper($_SERVER['REQUEST_METHOD']) == "POST" AND empty($this->category_name)) {
            $form .= '<span class="form_error">' . get_lang('ThisFieldIsRequired') . '. ' . get_lang('ErrorPleaseGiveCategoryName') . '<span><br />';
        }
        if (strtoupper($_SERVER['REQUEST_METHOD']) == "POST" AND !empty($this->category_name)) {
            $form .= '<span class="form_error">' . get_lang('CategoryAlreadyExistsEditIt') . '<span><br/>';
        }
        $form .= '<input type="text" id="category_name" name="category_name" value="' . Security::remove_XSS($category_name) . '" style="width:40%;" autofocus>
			  </div></div>';

        $form .= '<div class="row"><div class="label"></div><div class="formw">
			  <button id="StoreCategory" class="' . $class . '" type="submit" name="StoreCategory">' . $text . '</button></div></div>';


        $form .= '</form>';
        $form .= '<div style="clear: both;"></div>';

        return $form;
    }

    public function NewIdCategory() {
        //Max id dropbox_file
        $quey = "SELECT MAX(id) AS id FROM " . Database::get_course_table(TABLE_DROPBOX_FILE) . "";
        $res = Database::query($quey);
        $res = Database::fetch_array($res);
        //Max id dropbox_folder
        $quey2 = "SELECT MAX(cat_id) AS id FROM " . Database::get_course_table(TABLE_DROPBOX_CATEGORY) . "";
        $res2 = Database::query($quey2);
        $res2 = Database::fetch_array($res2);

        $row = 999;
        if ($res['id'] != '' || $res2['id'] != '')
            $row = (int) ($res['id']) + (int) ($res2['id']);

        return (1 + (int) $row);
    }

    public function savecategory() {
        global $_user;
        global $dropbox_cnf;

        // check if the target is valid
        if ($this->target == 'sent') {
            $sent = 1;
            $received = 0;
        } elseif ($this->target == 'received') {
            $sent = 0;
            $received = 1;
        }

        if (!$this->edit_id) {
            $session_id = api_get_session_id();
            // step 3a, we check if the category doesn't already exist
            $sql = "SELECT * FROM " . $dropbox_cnf['tbl_category'] . " WHERE user_id='" . $_user['user_id'] . "' AND cat_name='" . Database::escape_string(Security::remove_XSS($this->category_name)) . "' AND received='" . $received . "' AND sent='$sent' AND session_id='$session_id'";
            $result = Database::query($sql, __FILE__, __LINE__);

            // step 3b, we add the category if it does not exist yet.
            if (Database::num_rows($result) == 0) {
                $id_file = $this->NewIdCategory();
                $sql = "INSERT INTO " . $dropbox_cnf['tbl_category'] . " (cat_id, cat_name, received, sent, user_id, session_id)
			VALUES ('" . $id_file . "','" . Database::escape_string(Security::remove_XSS($this->category_name)) . "', '" . Database::escape_string($received) . "', '" . Database::escape_string($sent) . "', '" . Database::escape_string($_user['user_id']) . "',$session_id)";
                Database::query($sql, __FILE__, __LINE__);
                return array('type' => 'confirmation', 'message' => get_lang('CategoryStored'));
            } else {
                return array('type' => 'error', 'message' => get_lang('CategoryAlreadyExistsEditIt'));
            }
        } else {
            $sql = "UPDATE " . $dropbox_cnf['tbl_category'] . " SET cat_name='" . Database::escape_string(Security::remove_XSS($this->category_name)) . "', received='" . Database::escape_string($received) . "' , sent='" . Database::escape_string($sent) . "'
				WHERE user_id='" . Database::escape_string($_user['user_id']) . "'
				AND cat_id='" . Database::escape_string(Security::remove_XSS($this->edit_id)) . "'";
            Database::query($sql, __FILE__, __LINE__);
            return array('type' => 'confirmation', 'message' => get_lang('CategoryModified'));
        }
    }

    /**
     * This function deletes a dropbox category
     *
     * @todo give the user the possibility what needs to be done with the files in this category: move them to the root, download them as a zip, delete them
     *
     * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
     * @version march 2006
     */
    function deletecategory($action, $id) {
        global $dropbox_cnf;
        global $_user, $is_courseAdmin, $is_courseTutor;

        // an additional check that might not be necessary
        if ($action == 'deletereceivedcategory') {
            $sentreceived = 'received';
            $entries_table = $dropbox_cnf['tbl_post'];
            $id_field = 'file_id';
            $return_message = get_lang('ReceivedCatgoryDeleted');
        } elseif ($action == 'deletesentcategory') {
            $sentreceived = 'sent';
            $entries_table = $dropbox_cnf['tbl_file'];
            $id_field = 'id';
            $return_message = get_lang('SentCatgoryDeleted');
        }

        // step 1: delete the category
        $sql = "DELETE FROM " . $dropbox_cnf['tbl_category'] . " WHERE cat_id='" . Database::escape_string($id) . "' AND $sentreceived='1'";
        $result = Database::query($sql, __FILE__, __LINE__);

        // step 2: delete all the documents in this category
        $sql = "SELECT * FROM " . $entries_table . " WHERE cat_id='" . Database::escape_string($id) . "'";
        $result = Database::query($sql, __FILE__, __LINE__);

        while ($row = Database::fetch_array($result)) {
            $dropboxfile = new Dropbox_Person($_user['user_id'], $is_courseAdmin, $is_courseTutor);
            if ($action == 'deletereceivedcategory') {
                $dropboxfile->deleteReceivedWork($row[$id_field]);
            }
            if ($action == 'deletesentcategory') {
                $dropboxfile->deleteSentWork($row[$id_field]);
            }
        }

        return array('type' => 'confirmation', 'message' => $return_message);
    }

    public function getAddForm() {
        global $_user, $is_courseAdmin, $is_courseTutor, $course_info, $dropbox_unid, $dropbox_cnf;

        if (empty($_GET['view'])) {
            $view = 'sent';
        } else {
            $view = $_GET['view'];
        }

        $token = Security::get_token();
        $dropbox_person = new Dropbox_Person($_user['user_id'], $is_courseAdmin, $is_courseTutor);

        //$form .= '<form method="post" action="index.php?view_received_category=' . Security::remove_XSS($_GET['view_received_category']) . '&view_sent_category=' . Security::remove_XSS($_GET['view_sent_category']) . '&view=' . Security::remove_XSS($view) . '&action=add&' . api_get_cidreq() . '" enctype="multipart/form-data" onsubmit="return checkForm(this)">';
        $form .= '<form method="post" action="' . api_get_path(WEB_PATH) . 'main/document/uploadFiles.php?' . api_get_cidreq() . '&dir=/dropbox" enctype="multipart/form-data">';

        $form .= '<div class="row"><div class="form_header">' . get_lang('UploadNewFile') . '</div></div>';

        $form .= '<div class="row"><div class="label" style="width:80px" ><span class="form_required">* </span>' . get_lang('File') . ':
			  </div><div class="formw"><input type="hidden" name="MAX_FILE_SIZE" value="' . $this->dropbox_cnf("maxFilesize") . '">
                          <input type="file" name="file" size="20" style="opacity:0;width:100%;filter:alpha(opacity=0);position:absolute;border:none;margin:0px;padding:0px;top:0px;right:0px;cursor:pointer;height:40px;" ';
        if ($this->dropbox_cnf("allowOverwrite"))
            $form .= ' onChange="checkfile(this.value)">';

        $form .= '<div id="dynamicInput" style="width:90%;"></div>';

        $form .= '<input type="hidden" name="dropbox_unid" value="' . $dropbox_unid . '" />';
        $form .= '<input type="hidden" name="sec_token" value="' . $token . '" />';
        $form .= '</div>
		</div>';
        $form .= '<div class="row"><div class="label" style="width:80px;"></div><div style="margin-left:95px;width:80%;padding-top:1px;"><div id="draganddrophandler">' . get_lang('DragDrop') . '</div></div></div>';

        if ($this->dropbox_cnf("allowOverwrite")) {

            $form .= '<div class="row">
			<div class="label"></div>
			<div style="margin-left:95px;">
			<input type="checkbox" name="db_overwrite" id="db_overwrite" value="true" />' . $this->dropbox_lang("overwriteFile") . '
			</div>
                      </div>';
        }

        $form .= '<div class="row"><div class="label">' . $this->dropbox_lang("sendTo") . '</div><div class="formw">';

        //list of all users in this course and all virtual courses combined with it
        if (isset($_SESSION['id_session'])) {
            $complete_user_list_for_dropbox = array();
            if (api_get_setting('dropbox_allow_student_to_student') == 'true' || $_user['status'] != STUDENT) {
                //$complete_user_list_for_dropbox = CourseManager :: get_user_list_from_course_code($course_info['code'], true, $_SESSION['id_session'], '', api_get_setting('user_order_by'));
                if ($_SESSION['id_session'] != 0) {
                    $complete_user_list_for_dropbox = CourseManager::get_user_list_from_course_code(api_get_course_id(), true, intval($_SESSION['id_session']), '', api_get_setting('user_order_by'));
                } else {
                    $complete_user_list_for_dropbox = CourseManager::get_user_list_from_course_code(api_get_course_id(), false, intval($_SESSION['id_session']), '', api_get_setting('user_order_by'));
                }
            }
            $complete_user_list2 = CourseManager :: get_coach_list_from_course_code($course_info['code'], $_SESSION['id_session']);
            $complete_user_list_for_dropbox = array_merge($complete_user_list_for_dropbox, $complete_user_list2);
        } else {
            if (api_get_setting('dropbox_allow_student_to_student') == 'true' || $_user['status'] != STUDENT) {
                //$complete_user_list_for_dropbox = CourseManager :: get_user_list_from_course_code($course_info['code'], true, $_SESSION['id_session'], '', api_get_setting('user_order_by'));
                if ($_SESSION['id_session'] != 0) {
                    $complete_user_list_for_dropbox = CourseManager::get_user_list_from_course_code(api_get_course_id(), true, intval($_SESSION['id_session']));
                } else {
                    $complete_user_list_for_dropbox = CourseManager::get_user_list_from_course_code(api_get_course_id(), false, intval($_SESSION['id_session']));
                }
            } else {
                $complete_user_list_for_dropbox = CourseManager :: get_teacher_list_from_course_code($course_info['code']);
            }
        }

        foreach ($complete_user_list_for_dropbox as $k => $e) {
            $complete_user_list_for_dropbox[$k] = $e + array('lastcommafirst' => api_get_person_name($e['firstname'], $e['lastname']));
        }

        $complete_user_list_for_dropbox = TableSort::sort_table($complete_user_list_for_dropbox, 'lastcommafirst');

        $form .= '<select id="users" name="recipients[]" size="';

        if ($dropbox_person->isCourseTutor || $dropbox_person->isCourseAdmin) {
            $form .= '10';
        } else {
            $form .= '6';
        }

        $form .= '" multiple style="width: 350px;">';

        /*
          Create the options inside the select box:
          List all selected users their user id as value and a name string as display
         */

        $current_user_id = '';
        foreach ($complete_user_list_for_dropbox as $current_user) {
            if (($dropbox_person->isCourseTutor || $dropbox_person->isCourseAdmin || $this->dropbox_cnf("allowStudentToStudent") // RH: also if option is set
                    || $current_user['status'] != 5    // always allow teachers
                    || $current_user['tutor_id'] == 1    // always allow tutors
                    ) && $current_user['user_id'] != $_user['user_id']) {  // don't include yourself
                if ($current_user['user_id'] == $current_user_id)
                    continue;
                $full_name = $current_user['lastcommafirst'];
                $current_user_id = $current_user['user_id'];
                //$form .= '<option value="user_' . $current_user_id . '">' . $full_name . '</option>';
                $form .= '<option value="' . $current_user_id . '">' . $full_name . '</option>';
            }
        }

        /*
         * Show groups
         */
        if (($dropbox_person->isCourseTutor || $dropbox_person->isCourseAdmin) && $this->dropbox_cnf("allowGroup") || $this->dropbox_cnf("allowStudentToStudent")) {
            $complete_group_list_for_dropbox = GroupManager::get_group_list(null, $this->dropbox_cnf("courseId"));

            if (count($complete_group_list_for_dropbox) > 0) {
                foreach ($complete_group_list_for_dropbox as $current_group) {
                    if ($current_group['number_of_members'] > 0) {
                        $form .= '<option value="group_' . $current_group['id'] . '">G: ' . $current_group['name'] . ' - ' . $current_group['number_of_members'] . ' ' . get_lang('Users') . '</option>';
                    }
                }
            }
        }

        if (($dropbox_person->isCourseTutor || $dropbox_person->isCourseAdmin) && $this->dropbox_cnf("allowMailing")) {  // RH: Mailing starting point
            // echo '<option value="mailing">'.dropbox_lang("mailingInSelect").'</option>';
        }

        if ($this->dropbox_cnf("allowJustUpload")) {
            //$form .= '<option value="user_' . $_user['user_id'] . '">' . $this->dropbox_lang("justUploadInSelect") . '</option>';
            $form .= '<option value="' . $_user['user_id'] . '">' . $this->dropbox_lang("justUploadInSelect") . '</option>';
        }

        $form .= '</select></div></div>';

        $form .= '<div class="row"><div class="label"></div><div class="formw">
			  <div class="pull-bottom"><button type="submit" class="upload" name="submitWork">' . $this->dropbox_lang("upload", "noDLTT") . '</button>
			  </div></div></div>';
        $form .= "</form>";

        return $form;
    }

    public function addDropbox() {

        global $_user, $_course, $dropbox_cnf;

        // ----------------------------------------------------------
        // Validating the form data
        // ----------------------------------------------------------		
        // there are no recipients selected
        if (count($this->recipients) <= 0) {
            return get_lang('YouMustSelectAtLeastOneDestinee');
        }
        // Check if all the recipients are valid
        else {
            $thisIsAMailing = FALSE;  // RH: Mailing selected as destination
            $thisIsJustUpload = FALSE;  // RH
            foreach ($this->recipients as $rec) {
                if ($rec == 'mailing') {
                    $thisIsAMailing = TRUE;
                } elseif ($rec == 'upload') {
                    $thisIsJustUpload = TRUE;
                } elseif (strpos($rec, 'user_') === 0 && !$this->isCourseMember(substr($rec, strlen('user_')))) {
                    return get_lang('InvalideUserDetected');
                } elseif (strpos($rec, 'group_') !== 0 && strpos($rec, 'user_') !== 0) {
                    return get_lang('InvalideGroupDetected');
                }
            }
        }

        // we are doing a mailing but an additional recipient is selected
        if ($thisIsAMailing && ( count($this->recipients) != 1)) {
            return get_lang('MailingSelectNoOther');
        }

        // we are doing a just upload but an additional recipient is selected.
        // note: why can't this be valid? It is like sending a document to yourself AND to a different person (I do this quite often with my e-mails)
        if ($thisIsJustUpload && ( count($this->recipients) != 1)) {
            return get_lang('mailingJustUploadSelectNoOther');
        }

        if (empty($_FILES['file']['name'])) {
            $error = TRUE;
            return get_lang('NoFileSpecified');
        }

        // ----------------------------------------------------------
        // are we overwriting a previous file or sending a new one
        // ----------------------------------------------------------
        $dropbox_overwrite = false;
        if (isset($this->cb_overwrite) && $this->cb_overwrite == true) {
            $dropbox_overwrite = true;
        }

        // ----------------------------------------------------------
        // doing the upload
        // ----------------------------------------------------------
        $dropbox_filename = $_FILES['file']['name'];
        $dropbox_filesize = $_FILES['file']['size'];
        $dropbox_filetype = $_FILES['file']['type'];
        $dropbox_filetmpname = $_FILES['file']['tmp_name'];

        // check if the filesize does not exceed the allowed size.
        if ($dropbox_filesize <= 0 || $dropbox_filesize > $dropbox_cnf["maxFilesize"]) {
            return get_lang('DropboxFileTooBig');
        }

        // check if the file is actually uploaded
        if (!is_uploaded_file($dropbox_filetmpname)) { // check user fraud : no clean error msg.
            return get_lang('TheFileIsNotUploaded');
        }

        // Try to add an extension to the file if it hasn't got one
        $dropbox_filename = add_ext_on_mime($dropbox_filename, $dropbox_filetype);
        // Replace dangerous characters
        $dropbox_filename = replace_dangerous_char($dropbox_filename);
        // Transform any .php file in .phps fo security
        $dropbox_filename = php2phps($dropbox_filename);
        //filter extension
        if (!filter_extension($dropbox_filename)) {
            return get_lang('UplUnableToSaveFileFilteredExtension');
        }

        // set title
        $dropbox_title = $dropbox_filename;
        // set author
        if ($this->authors == '') {
            $this->authors = $this->getUserNameFromId($_user['user_id']);
        }

        // note: I think we could better migrate everything from here on to separate functions: store_new_dropbox, store_new_mailing, store_just_upload

        if ($dropbox_overwrite) {  // RH: Mailing: adapted
            $dropbox_person = new Dropbox_Person($_user['user_id'], api_is_course_admin(), api_is_course_tutor());

            foreach ($dropbox_person->sentWork as $w) {
                if ($w->title == $dropbox_filename) {
                    if (($w->recipients[0]['id'] > dropbox_cnf("mailingIdBase")) xor $thisIsAMailing) {
                        return get_lang('MailingNonMailingError');
                    }
                    if (($w->recipients[0]['id'] == $_user['user_id']) xor $thisIsJustUpload) {
                        return get_lang('MailingJustUploadSelectNoOther');
                    }
                    $dropbox_filename = $w->filename;
                    $found = true; // note: do we still need this?
                    break;
                }
            }
        } else {  // rename file to login_filename_uniqueId format
            $dropbox_filename = $this->getLoginFromId($_user['user_id']) . "_" . $dropbox_filename . "_" . uniqid('');
        }

        // creating the array that contains all the users who will receive the file
        $new_work_recipients = array();
        foreach ($this->recipients as $rec) {
            if (strpos($rec, 'user_') === 0) {
                $new_work_recipients[] = substr($rec, strlen('user_'));
            } elseif (strpos($rec, 'group_') === 0) {
                $userList = GroupManager::get_subscribed_users(substr($rec, strlen('group_')));
                foreach ($userList as $usr) {
                    if (!in_array($usr['user_id'], $new_work_recipients) && $usr['user_id'] != $_user['user_id']) {
                        $new_work_recipients[] = $usr['user_id'];
                    }
                }
            }
        }

        @move_uploaded_file($dropbox_filetmpname, $dropbox_cnf["sysPath"] . '/' . $dropbox_filename);

        $b_send_mail = api_get_course_setting('email_alert_on_new_doc_dropbox');

        if ($b_send_mail) {
            foreach ($new_work_recipients as $recipient_id) {
                include_once(api_get_path(LIBRARY_PATH) . 'usermanager.lib.php');
                $recipent_temp = UserManager :: get_user_info_by_id($recipient_id);
                api_mail_html(api_get_person_name($recipent_temp['firstname'] . ' ' . $recipent_temp['lastname'], null, PERSON_NAME_EMAIL_ADDRESS), $recipent_temp['email'], get_lang('NewDropboxFileUploaded'), get_lang('NewDropboxFileUploadedContent') . ' ' . api_get_path(WEB_CODE_PATH) . 'dropbox/index.php?cidReq=' . $_course['sysCode'] . "<br/><br/>" . api_get_person_name($_user['firstName'], $_user['lastName'], null, PERSON_NAME_EMAIL_ADDRESS) . "<br/>" . get_lang('Email') . " : " . $_user['mail'], api_get_person_name($_user['firstName'], $_user['lastName'], null, PERSON_NAME_EMAIL_ADDRESS), $_user['mail']);
            }
        }

        new Dropbox_SentWork($_user['user_id'], $dropbox_title, $this->description, strip_tags($this->authors), $dropbox_filename, $dropbox_filesize, $new_work_recipients);

        Security::clear_token();
        return get_lang('FileUploadSucces');
    }

    public function deleteSentDropboxfile() {

        global $_user, $is_courseAdmin, $is_courseTutor, $course_info, $dropbox_cnf;

        $dropboxfile = new Dropbox_Person($_user['user_id'], $is_courseAdmin, $is_courseTutor);
        $dropboxfile->deleteSentWork($this->dropboxfile_id);
        $message = get_lang('SentFileDeleted');

        return $message;
    }

    public function deleteReceivedDropboxfile() {

        global $_user, $is_courseAdmin, $is_courseTutor, $course_info, $dropbox_cnf;

        $dropboxfile = new Dropbox_Person($_user['user_id'], $is_courseAdmin, $is_courseTutor);
        $dropboxfile->deleteReceivedWork($this->dropboxfile_id);
        $message = get_lang('ReceivedFileDeleted');
    }

    /**
     * this function transforms the array containing all the feedback into something visually attractive.
     *
     * @param an array containing all the feedback about the given message.
     *
     * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
     * @version march 2006
     */
    public function feedback($array) {
        foreach ($array as $key => $value) {
            $output.= $this->format_feedback($value);
        }
        $output.= $this->feedback_form();
        return $output;
    }

    /**
     * This function returns the html code to display the feedback messages on a given dropbox file
     * @param $feedback_array an array that contains all the feedback messages about the given document.
     * @return html code
     * @todo add the form for adding new comment (if the other party has not deleted it yet).
     *
     * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
     * @version march 2006
     */
    public function format_feedback($feedback) {
        $output.=$this->display_user_link($feedback['author_user_id']);
        $output.='&nbsp;&nbsp;[' . $feedback['feedback_date'] . ']<br />';
        $output.='<div style="padding-top:6px">' . nl2br($feedback['feedback']) . '</div><hr size="1" noshade/><br />';
        return $output;
    }

    /**
     * this function returns the code for the form for adding a new feedback message to a dropbox file.
     * @return html code
     *
     * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
     * @version march 2006
     */
    public function feedback_form() {
        global $dropbox_cnf;

        $return = get_lang('AddNewFeedback') . '<br />';

        // we now check if the other users have not delete this document yet. If this is the case then it is useless to see the
        // add feedback since the other users will never get to see the feedback.
        $sql = "SELECT * FROM " . $dropbox_cnf["tbl_person"] . " WHERE file_id='" . Database::escape_string($_GET['id']) . "'";
        $result = Database::query($sql, __LINE__, __FILE__);
        $number_users_who_see_file = Database::num_rows($result);
        if ($number_users_who_see_file > 1) {
            $CloseFeedback = get_lang('CloseFeedback');
            $api_get_cidreq = api_get_cidreq();
            $view_received_category = Security::remove_XSS($_GET["view_received_category"]);
            $view_sent_category = Security::remove_XSS($_GET["view_sent_category"]);
            $view = Security::remove_XSS($view);

            $return .= '<textarea name="feedback" style="width: 80%; height: 80px;"></textarea><br />';
            $return .= '<div style="margin-top:20px; margin-bottom:75px;">';
            $return .= "<button style='margin-right:10px; margin: 5px; float:right; padding:15px !important;' type='submit' class='custombutton' name='store_feedback' value='" . get_lang('Ok') . "' onclick='document.form_tablename.attributes.action.value = document.location;'>" . get_lang('AddComment') . "</button>";
            $return .= "<button class='custombutton' style='margin: 5px; float:right; padding:15px !important;' href='\index.php?$api_get_cidreq&view_received_category=$view_received_category&view_sent_category=$view_sent_category&view=$view' >$CloseFeedback</button>";
            $return .= '</div>';
        } else {
            $return .= get_lang('AllUsersHaveDeletedTheFileAndWillNotSeeFeedback');
        }
        return $return;
    }

    /**
     * @return a language string (depending on the success or failure.
     *
     * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
     * @version march 2006
     */
    public function storeFeedback() {
        global $dropbox_cnf;
        global $_user;

        if (!is_numeric($_GET['id'])) {
            return get_lang('FeedbackError');
        }

        if ($this->feedback == '') {
            return get_lang('PleaseTypeText');
        } else {
            $now = date('Y-m-d H:i:s');
            $sql = "INSERT INTO " . $dropbox_cnf['tbl_feedback'] . " (file_id, author_user_id, feedback, feedback_date) VALUES
                                    ('" . Database::escape_string($_GET['id']) . "','" . Database::escape_string($_user['user_id']) . "','" . Database::escape_string($this->feedback) . "', '$now')";
            Database::query($sql, __FILE__, __LINE__);
            return get_lang('DropboxFeedbackStored');
        }
    }

    /**
     * Displays the form to move one individual file to a category
     *
     * @return html code of the form that appears in a dokeos message box.
     *
     * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
     * @version march 2006
     */
    function getMoveForm() {
        $target = $this->get_dropbox_categories($_GET['view']);

        $form .= '<form class="outer_form" name="form1" method="post" action="' . api_get_self() . '?view_received_category=' . $_GET['view_received_category'] . '&view_sent_category=' . Security::remove_XSS($_GET['view_sent_category']) . '&view=' . Security::remove_XSS($_GET['view']) . '">';
        $form .= '<input type="hidden" name="id" value="' . Security::remove_XSS($_GET['move_id']) . '">';
        $form .= '<input type="hidden" name="part" value="' . Security::remove_XSS($_GET['view']) . '">';
        $form .= '
				<div class="row">
					<div class="label">
						
                                                ' . get_lang('MoveFileTo') . '
					</div>
					<div class="formw">';
        $form .= '<select name="move_target">';
        $form .= '<option value="0">' . get_lang('Root') . '</option>';
        foreach ($target as $key => $category) {
            $form .= '<option value="' . $category['cat_id'] . '">' . $category['cat_name'] . '</option>';
        }
        $form .= '</select>';
        $form .= '	</div>
				</div>';

        $form .= '
			<div class="row">
				<div class="label">
				</div>
				<div class="formw">
					<button class="next" type="submit" name="do_move" value="' . get_lang('Ok') . '">' . get_lang('MoveFile') . '</button>
				</div>
			</div>
		';
        $form .= '</form>';

        $form .= '<div style="clear: both;"></div>';

        return $form;
    }

    /**
     * This function moves a file to a different category
     *
     * @param $id the id of the file we are moving
     * @param $target the id of the folder we are moving to
     * @param $part are we moving a received file or a sent file?
     *
     * @return language string
     *
     * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
     * @version march 2006
     */
    function storeMove() {
        global $_user;
        global $dropbox_cnf;

        $id = $this->id;
        $target = $this->move_target;
        $part = $this->part;
        if ((isset($id) AND $id <> '') AND (isset($target) AND $target <> '') AND (isset($part) AND $part <> '')) {
            if ($part == 'received') {
                $sql = "UPDATE " . $dropbox_cnf["tbl_post"] . " SET cat_id='" . Database::escape_string($target) . "'
							WHERE dest_user_id='" . Database::escape_string($_user['user_id']) . "'
							AND file_id='" . Database::escape_string($id) . "'
							";
                Database::query($sql, __FILE__, __LINE__);
                $return_message = get_lang('ReceivedFileMoved');
            }
            if ($part == 'sent') {
                $sql = "UPDATE " . $dropbox_cnf["tbl_file"] . " SET cat_id='" . Database::escape_string($target) . "'
							WHERE uploader_id='" . Database::escape_string($_user['user_id']) . "'
							AND id='" . Database::escape_string($id) . "'
							";
                Database::query($sql, __FILE__, __LINE__);
                $return_message = get_lang('SentFileMoved');
            }
        } else {
            $return_message = get_lang('NotMovedError');
        }
        return $return_message;
    }

    public function deleteReceived($checked_file_ids) {
        global $_user, $is_courseAdmin, $is_courseTutor;

        $dropboxfile = new Dropbox_Person($_user['user_id'], $is_courseAdmin, $is_courseTutor);

        foreach ($checked_file_ids as $key => $value) {
            if ($_GET['view'] == 'received') {
                $dropboxfile->deleteReceivedWork($value);
                $message = get_lang('ReceivedFileDeleted');
            }
            if ($_GET['view'] == 'sent' OR empty($_GET['view'])) {
                $dropboxfile->deleteSentWork($value);
                $message = get_lang('SentFileDeleted');
            }
        }
        return $message;
    }

    public function downloadDropbox($checked_file_ids) {
        $this->zip_download($checked_file_ids);
    }

    /**
     * This function downloads all the files of the inputarray into one zip
     * @param $array an array containing all the ids of the files that have to be downloaded.
     * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
     * @todo consider removing the check if the user has received or sent this file (zip download of a folder already sufficiently checks for this).
     * @todo integrate some cleanup function that removes zip files that are older than 2 days
     *
     * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
     * @version march 2006
     */
    public function zip_download($array) {
        global $_course;
        global $dropbox_cnf;
        global $_user;
        global $files;

        $sys_course_path = api_get_path(SYS_COURSE_PATH);

        // zip library for creation of the zipfile
        include(api_get_path(LIBRARY_PATH) . "/pclzip/pclzip.lib.php");

        // place to temporarily stash the zipfiles
        $temp_zip_dir = api_get_path(SYS_COURSE_PATH) . $_course['path'] . "/temp/";

        // create the directory if it does not exist yet.
        if (!is_dir($temp_zip_dir)) {
            mkdir($temp_zip_dir);
        }

        $this->cleanup_temp_dropbox();

        $files = '';

        // note: we also have to add the check if the user has received or sent this file. !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        $sql = "SELECT distinct file.filename, file.title, file.author, file.description
				FROM " . $dropbox_cnf["tbl_file"] . " file, " . $dropbox_cnf["tbl_person"] . " person
				WHERE file.id IN (" . implode(', ', $array) . ")
				AND file.id=person.file_id
				AND person.user_id='" . $_user['user_id'] . "'";
        $result = Database::query($sql, __FILE__, __LINE__);
        while ($row = Database::fetch_array($result)) {
            $files[$row['filename']] = array('filename' => $row['filename'], 'title' => $row['title'], 'author' => $row['author'], 'description' => $row['description']);
        }

        //$alternative is a variable that uses an alternative method to create the zip
        // because the renaming of the files inside the zip causes error on php5 (unexpected end of archive)
        $alternative = true;
        if ($alternative) {
            $this->zip_download_alternative($files);
            exit;
        }

        // create the zip file
        $name = 'dropboxdownload-' . $_user['user_id'] . '-' . mktime() . '.zip';
        $temp_zip_file = $temp_zip_dir . '/' . $name;
        $zip_folder = new PclZip($temp_zip_file);

        foreach ($files as $key => $value) {
            // met hernoemen van de files in de zip
            $zip_folder->add(api_get_path(SYS_COURSE_PATH) . $_course['path'] . "/dropbox/" . $value['filename'], PCLZIP_OPT_REMOVE_PATH, api_get_path(SYS_COURSE_PATH) . $_course['path'] . "/dropbox", PCLZIP_CB_PRE_ADD, '$this->my_pre_add_callback');
            // zonder hernoemen van de files in de zip
            //$zip_folder->add(api_get_path(SYS_COURSE_PATH).$_course['path']."/dropbox/".$value['filename'],PCLZIP_OPT_REMOVE_PATH, api_get_path(SYS_COURSE_PATH).$_course['path']."/dropbox");
        }

        // create the overview file
        $overview_file_content = $this->generate_html_overview($files, array('filename'), array('title'));
        $overview_file = $temp_zip_dir . '/overview.html';
        $handle = fopen($overview_file, 'w');
        fwrite($handle, $overview_file_content);


        // send the zip file
        DocumentManager::file_send_for_download($temp_zip_file, true, $name);
        exit;
    }

    /**
     * This function is an alternative zip download. It was added because PCLZip causes problems on PHP5 when using PCLZIP_CB_PRE_ADD and a callback function to rename
     * the files inside the zip file (dropbox scrambles the files to prevent
     * @todo consider using a htaccess that denies direct access to the file but only allows the php file to access it. This would remove the scrambling requirement
     * 		but it would require additional checks to see if the filename of the uploaded file is not used yet.
     * @param $files is an associative array that contains the files that the user wants to download (check to see if the user is allowed to download these files already
     * 		 happened so the array is clean!!. The key is the filename on the filesystem. The value is an array that contains both the filename on the filesystem and
     * 		 the original filename (that will be used in the zip file)
     * @todo when we copy the files there might be two files with the same name. We need a function that (recursively) checks this and changes the name
     *
     * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
     * @version march 2006
     */
    public function zip_download_alternative($files) {
        global $_course;
        global $_user;

        $temp_zip_dir = api_get_path(SYS_COURSE_PATH) . $_course['path'] . "/temp/";

        // Step 2: we copy all the original dropbox files to the temp folder and change their name into the original name
        foreach ($files as $key => $value) {
            $value['title'] = $this->check_file_name(api_strtolower($value['title']));
            $files[$value['filename']]['title'] = $value['title'];
            copy(api_get_path(SYS_COURSE_PATH) . $_course['path'] . "/dropbox/" . $value['filename'], api_get_path(SYS_COURSE_PATH) . $_course['path'] . "/temp/" . $value['title']);
        }

        // Step 3: create the zip file and add all the files to it
        $temp_zip_file = $temp_zip_dir . '/dropboxdownload-' . $_user['user_id'] . '-' . mktime() . '.zip';
        $zip_folder = new PclZip($temp_zip_file);
        foreach ($files as $key => $value) {
            $zip_folder->add(api_get_path(SYS_COURSE_PATH) . $_course['path'] . "/temp/" . $value['title'], PCLZIP_OPT_REMOVE_PATH, api_get_path(SYS_COURSE_PATH) . $_course['path'] . "/temp");
        }

        // Step 1: create the overview file and add it to the zip
        $overview_file_content = $this->generate_html_overview($files, array('filename'), array('title'));
        $overview_file = $temp_zip_dir . 'overview' . replace_dangerous_char(api_is_western_name_order() ? $_user['firstname'] . $_user['lastname'] : $_user['lastname'] . $_user['firstname'], 'strict') . '.html';
        $handle = fopen($overview_file, 'w');
        fwrite($handle, $overview_file_content);
        // todo: find a different solution for this because even 2 seconds is no guarantee.
        sleep(2);

        // Step 4: we add the overview file
        $zip_folder->add($overview_file, PCLZIP_OPT_REMOVE_PATH, api_get_path(SYS_COURSE_PATH) . $_course['path'] . "/temp");

        // Step 5: send the file for download;
        DocumentManager::file_send_for_download($temp_zip_file, true);

        // Step 6: remove the files in the temp dir
        foreach ($files as $key => $value) {
            unlink(api_get_path(SYS_COURSE_PATH) . $_course['path'] . "/temp/" . $value['title']);
        }
        //unlink($overview_file);

        exit;
    }

    /**
     * @desc This function checks if the real filename of the dropbox files doesn't already exist in the temp folder. If this is the case then
     * 		it will generate a different filename;
     *
     * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
     * @version march 2006
     */
    public function check_file_name($file_name_2_check, $counter = 0) {
        global $_course;

        $new_file_name = $file_name_2_check;
        if ($counter <> 0) {
            $new_file_name = $counter . $new_file_name;
        }

        if (!file_exists(api_get_path(SYS_COURSE_PATH) . $_course['path'] . "/temp/" . $new_file_name)) {
            return $new_file_name;
        } else {
            $counter++;
            $new_file_name = $this->check_file_name($file_name_2_check, $counter);
            return $new_file_name;
        }
    }

    /**
     * This is a callback function to decrypt the files in the zip file to their normal filename (as stored in the database)
     * @param $p_event a variable of PCLZip
     * @param $p_header a variable of PCLZip
     *
     * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
     * @version march 2006
     */
    public function my_pre_add_callback($p_event, &$p_header) {
        global $files;

        $p_header['stored_filename'] = $files[$p_header['stored_filename']]['title'];
        return 1;
    }

    /**
     * @desc generates the contents of a html file that gives an overview of all the files in the zip file.
     * 		This is to know the information of the files that are inside the zip file (who send it, the comment, ...)
     * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
     * @version march 2006
     */
    public function generate_html_overview($files, $dont_show_columns = array(), $make_link = array()) {
        $return = "<html>\n<head>\n\t<title>" . get_lang('OverviewOfFilesInThisZip') . "</title>\n</head>";
        $return.="\n\n<body>\n<table border=\"1px\">";

        $counter = 0;
        foreach ($files as $key => $value) {
            // We add the header
            if ($counter == 0) {
                $columns_array = array_keys($value);
                $return.="\n<tr>";
                foreach ($columns_array AS $columns_array_key => $columns_array_value) {
                    if (!in_array($columns_array_value, $dont_show_columns)) {
                        $return.="\n\t<th>" . $columns_array_value . "</th>";
                    }
                    $column[] = $columns_array_value;
                }
                $return.="</tr><n";
            }
            $counter++;

            // We add the content
            $return.="\n<tr>";
            foreach ($column AS $column_key => $column_value) {
                if (!in_array($column_value, $dont_show_columns)) {
                    $return.="\n\t<td>";
                    if (in_array($column_value, $make_link)) {
                        $return.='<a href="' . $value[$column_value] . '">' . $value[$column_value] . '</a>';
                    } else {
                        $return.=$value[$column_value];
                    }
                    $return.="</td>";
                }
            }
            $return.="</tr><n";
        }
        $return.="\n</table>\n\n</body>";
        $return.="\n</html>";

        return $return;
    }

    /**
     * @desc Cleans the temp zip files that were created when users download several files or a whole folder at once.	
     * @return true
     * @todo
     * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
     * @version march 2006
     */
    public function cleanup_temp_dropbox() {
        global $_course;

        $handle = opendir(api_get_path(SYS_COURSE_PATH) . $_course['path'] . "/temp");
        while (false !== ($file = readdir($handle))) {
            if ($file <> '.' OR $file <> '..') {
                $name = str_replace('.zip', '', $file);
                $name_part = explode('-', $name);
                $timestamp_of_file = $name_part[count($name_part) - 1];
                // if it is a dropboxdownloadfile and the file is older than one day then we delete it
                if (strstr($file, 'dropboxdownload') AND $timestamp_of_file < (mktime() - 86400)) {
                    unlink(api_get_path(SYS_COURSE_PATH) . $_course['path'] . "/temp/" . $file);
                }
            }
        }
        closedir($handle);
        return true;
    }

    /**
     * @author Ren? Haentjens, Ghent University
     * @todo check if this function is still necessary.
     */
    public function removeMoreIfMailing($file_id) {
        global $dropbox_cnf;

        // when deleting a mailing zip-file (posted to mailingPseudoId):
        // 1. the detail window is no longer reachable, so
        //    for all content files, delete mailingPseudoId from person-table
        // 2. finding the owner (getUserOwningThisMailing) is no longer possible, so
        //    for all content files, replace mailingPseudoId by owner as uploader
        $file_id = intval($file_id);
        $sql = "SELECT p.dest_user_id
				FROM " . $dropbox_cnf["tbl_post"] . " p
				WHERE p.file_id = '" . $file_id . "'";
        $result = Database::query($sql, __FILE__, __LINE__);

        if ($res = Database::fetch_array($result)) {
            $mailingPseudoId = $res['dest_user_id'];
            if ($mailingPseudoId > $dropbox_cnf["mailingIdBase"]) {
                $sql = "DELETE FROM " . $dropbox_cnf["tbl_person"] . " WHERE user_id='" . $mailingPseudoId . "'";
                $result1 = Database::query($sql, __FILE__, __LINE__);

                $sql = "UPDATE " . $dropbox_cnf["tbl_file"] .
                        " SET uploader_id='" . api_get_user_id() . "' WHERE uploader_id='" . $mailingPseudoId . "'";
                $result1 = Database::query($sql, __FILE__, __LINE__);
            }
        }
    }

    /**
     * Checks if there are files in the dropbox_file table that aren't used anymore in dropbox_person table.
     * If there are, all entries concerning the file are deleted from the db + the file is deleted from the server
     */
    public function removeUnusedFiles() {
        global $dropbox_cnf;
        // select all files that aren't referenced anymore
        $sql = "SELECT DISTINCT f.id, f.filename
				FROM " . $dropbox_cnf["tbl_file"] . " f
				LEFT JOIN " . $dropbox_cnf["tbl_person"] . " p ON f.id = p.file_id
				WHERE p.user_id IS NULL";
        $result = Database::query($sql, __FILE__, __LINE__);
        while ($res = Database::fetch_array($result)) {
            //delete the selected files from the post and file tables
            $sql = "DELETE FROM " . $dropbox_cnf["tbl_post"] . " WHERE file_id='" . $res['id'] . "'";
            $result1 = Database::query($sql, __FILE__, __LINE__);
            $sql = "DELETE FROM " . $dropbox_cnf["tbl_file"] . " WHERE id='" . $res['id'] . "'";
            $result1 = Database::query($sql, __FILE__, __LINE__);

            //delete file from server
            @unlink(dropbox_cnf("sysPath") . "/" . $res["filename"]);
        }
    }

    /**
     * returns loginname or false if user isn't registered anymore
     * @todo check if this function is still necessary. There might be a library function for this.
     */
    public function getLoginFromId($id) {
        global $dropbox_cnf;

        $id = intval($id);
        $sql = "SELECT username
				FROM " . $dropbox_cnf['tbl_user'] . "
				WHERE user_id='$id'";
        $result = Database::query($sql, __FILE__, __LINE__);
        $res = Database::fetch_array($result);
        if ($res == FALSE)
            return FALSE;
        return stripslashes($res["username"]);
    }

    /**
     * @return boolean indicating if user with user_id=$user_id is a course member
     * @todo eliminate global
     * @todo check if this function is still necessary. There might be a library function for this.
     */
    public function isCourseMember($user_id) {
        global $_course;
        $course_code = $_course['sysCode'];
        $is_course_member = CourseManager::is_user_subscribed_in_course($user_id, $course_code, true);
        return $is_course_member;
    }

    /**
     * RH: Mailing (2 new functions)
     *
     * Mailing zip-file is posted to (dest_user_id = ) mailing pseudo_id
     * and is only visible to its uploader (user_id).
     *
     * Mailing content files have uploader_id == mailing pseudo_id, a normal recipient,
     * and are visible initially to recipient and pseudo_id.
     *
     * @author Ren� Haentjens, Ghent University
     *
     * @todo check if this function is still necessary.
     */
    public function getUserOwningThisMailing($mailingPseudoId, $owner = 0, $or_die = '') {
        global $dropbox_cnf;

        $mailingPseudoId = intval($mailingPseudoId);
        $sql = "SELECT f.uploader_id
				FROM " . $dropbox_cnf["tbl_file"] . " f
				LEFT JOIN " . $dropbox_cnf["tbl_post"] . " p ON f.id = p.file_id
				WHERE p.dest_user_id = '" . $mailingPseudoId . "'";
        $result = Database::query($sql, __FILE__, __LINE__);

        if (!($res = Database::fetch_array($result)))
            die($this->dropbox_lang("generalError") . " (code 901)");

        if ($owner == 0)
            return $res['uploader_id'];

        if ($res['uploader_id'] == $owner)
            return TRUE;

        die($this->dropbox_lang("generalError") . " (code " . $or_die . ")");
    }

    /**
     *  Get display actions
     *  @param      int     Optional, User id 
     *  @return`    array   action icons
     */
    public function getDisplayActionIcons() {

        // getting all the categories in the dropbox for the given user
        $dropbox_categories = $this->get_dropbox_categories();
        foreach ($dropbox_categories as $category) {
            if ($category['received'] == '1') {
                $dropbox_received_category[] = $category;
            }
            if ($category['sent'] == '1') {
                $dropbox_sent_category[] = $category;
            }
        }
        $action_icons = '';

        if ($_GET['view'] == 'received') {

            if (empty($_GET['view'])) {
                $view = 'received';
            } else {
                $view = $_GET['view'];
            }

            if (isset($_GET['view_received_category']) AND $_GET['view_received_category'] <> '') {
                $view_dropbox_category_received = Security::remove_XSS($_GET['view_received_category']);
            } else {
                $view_dropbox_category_received = 0;
            }

            //if (api_get_session_id()==api_get_session_id()) {				
            if ($view_dropbox_category_received <> 0 && api_is_allowed_to_session_edit(false, true)) {
                $action_icons .= get_lang('CurrentlySeeing') . ': <strong>' . $dropbox_categories[$view_dropbox_category_received]['cat_name'] . '</strong> ';
                $action_icons .= '<a href="' . api_get_self() . '?' . api_get_cidreq() . '&view_received_category=0&view_sent_category=' . Security::remove_XSS($_GET['view_sent_category']) . '&view=' . Security::remove_XSS($view) . '">' . Display::return_icon('pixel.gif', get_lang('Up'), array('class' => 'toolactionplaceholdericon toolactionfolderup')) . ' ' . get_lang('Root') . "</a>\n";
                $movelist[0] = 'Root'; // move_received selectbox content
            } else {
                $action_icons .= '<a href="' . api_get_self() . '?' . api_get_cidreq() . '&action=addreceivedcategory&view=' . Security::remove_XSS($view) . '">' . Display::return_icon('pixel.gif', get_lang('AddNewCategory'), array('class' => 'toolactionplaceholdericon toolactioncreatefolder')) . ' ' . get_lang('AddNewCategory') . '</a>';

                $action_icons .= '<a href="index.php?' . api_get_cidreq() . '&view=sent  ">' . Display::return_icon(('pixel.gif'), get_lang('SentFiles'), array('class' => 'toolactionplaceholdericon toolactionfolderup')) . get_lang('SentFiles') . ' </a>';

                $action_icons .= '<a href="index.php?' . api_get_cidreq() . '&view=received ">' . Display::return_icon('pixel.gif', get_lang('ReceivedFiles'), array('class' => 'toolactionplaceholdericon toolactiondown32')) . get_lang('ReceivedFiles') . ' </a>';
            }

            $action_icons .= "<a href=\"" . api_get_self() . "?" . api_get_cidreq() . "&view=" . Security::remove_XSS($view) . "&action=add\">" . Display::return_icon('pixel.gif', get_lang('UploadNewFile'), array('class' => 'toolactionplaceholdericon toolactionupload')) . ' ' . get_lang('UploadNewFile') . "</a>&nbsp;\n";
            //} 
            /*
              else {
              if (api_is_allowed_to_session_edit(false,true)) {
              if ($view_dropbox_category_received<>0  && api_is_allowed_to_session_edit(false,true)) {
              $action_icons .= get_lang('CurrentlySeeing').': <strong>'.$dropbox_categories[$view_dropbox_category_received]['cat_name'].'</strong> ';
              $action_icons .= '<a href="'.api_get_self().'?'.api_get_cidreq().'&view_received_category=0&view_sent_category='.Security::remove_XSS($_GET['view_sent_category']).'&view='.Security::remove_XSS($view).'">'.Display::return_icon('pixel.gif',get_lang('Up'), array('class'=>' toolactionplaceholdericon toolactionfolderup')).' '.get_lang('Root')."</a>\n";

              $movelist[0] = 'Root'; // move_received selectbox content
              } else {
              $action_icons .= '<a href="'.api_get_self().'?'.api_get_cidreq().'&action=addreceivedcategory&view='.Security::remove_XSS($view).'">'.Display::return_icon('pixel.gif',get_lang('AddNewCategory'), array('class'=>'toolactionplaceholdericon toolactioncreatefolder')).' '.get_lang('AddNewCategory').'</a>';

              }
              }
              } */
        }



        if (!$_GET['view'] OR $_GET['view'] == 'sent') {

            if (empty($_GET['view'])) {
                $view = 'sent';
            } else {
                $view = $_GET['view'];
            }

            // This is for the categories
            if (isset($_GET['view_sent_category']) AND $_GET['view_sent_category'] <> '') {
                $view_dropbox_category_sent = $_GET['view_sent_category'];
            } else {
                $view_dropbox_category_sent = 0;
            }

            //if (api_get_session_id()==api_get_session_id()) {				
            if ($view_dropbox_category_sent <> 0) {
                $action_icons .= get_lang('CurrentlySeeing') . ': <strong>' . $dropbox_categories[$view_dropbox_category_sent]['cat_name'] . '</strong> ';
                $action_icons .= '<a href="' . api_get_self() . '?' . api_get_cidreq() . '&view_received_category=' . Security::remove_XSS($_GET['view_received_category']) . '&view_sent_category=0&view=' . Security::remove_XSS($view) . '">' . Display::return_icon('pixel.gif', get_lang('Up'), array('class' => 'toolactionplaceholdericon toolactionfolderup')) . ' ' . get_lang('Root') . "</a>\n";
            } else {
                $action_icons .= "<a href=\"" . api_get_self() . "?" . api_get_cidreq() . "&view=" . Security::remove_XSS($view) . "&action=addsentcategory\">" . Display::return_icon('pixel.gif', get_lang('AddNewCategory'), array('class' => 'toolactionplaceholdericon toolactioncreatefolder')) . " " . get_lang('AddNewCategory') . "</a>\n";
            }
            if (empty($_GET['view_sent_category'])) {
                $action_icons .= '<a href="index.php?' . api_get_cidreq() . '&view=sent ">' . Display::return_icon('pixel.gif', get_lang('SentFiles'), array('class' => 'toolactionplaceholdericon toolactionfolderup')) . get_lang('SentFiles') . ' </a>';

                $action_icons .= '<a href="index.php?' . api_get_cidreq() . '&view=received  ">' . Display::return_icon('pixel.gif', get_lang('ReceivedFiles'), array('class' => 'toolactionplaceholdericon toolactiondown32')) . get_lang('ReceivedFiles') . ' </a>';

                $action_icons .= "<a href=\"" . api_get_self() . "?" . api_get_cidreq() . "&view=" . Security::remove_XSS($view) . "&action=add\">" . Display::return_icon('pixel.gif', get_lang('UploadNewFile'), array('class' => 'toolactionplaceholdericon toolactionupload')) . ' ' . get_lang('UploadNewFile') . "</a>&nbsp;\n";
            }
        }

        return $action_icons;
    }

    /**
     * The dropbox has a deviant naming scheme for language files so it needs an additional language function
     *
     * @todo check if this function is still necessary.
     *
     * @author Ren� Haentjens, Ghent University
     */
    public function dropbox_lang($variable, $notrans = 'DLTT') {
        global $charset;
        return (api_get_setting('server_type') == 'test' ?
                        get_lang('dropbox_lang["' . $variable . '"]', $notrans) :
                        api_html_entity_decode(api_to_system_encoding(str_replace("\\'", "'", $GLOBALS['dropbox_lang'][$variable]), null, true), ENT_QUOTES, $charset));
    }

    /**
     * Function that finds a given config setting
     *
     * @author Ren� Haentjens, Ghent University
     */
    public function dropbox_cnf($variable) {
        return $GLOBALS['dropbox_cnf'][$variable];
    }

    /**
     * returns username or false if user isn't registered anymore
     * @todo check if this function is still necessary. There might be a library function for this.
     */
    public function getUserNameFromId($id) {  // RH: Mailing: return 'Mailing ' + id
        $mailingId = $id - $this->dropbox_cnf("mailingIdBase");
        if ($mailingId > 0) {
            return dropbox_lang("mailingAsUsername", "noDLTT") . $mailingId;
        }
        $id = intval($id);
        $sql = "SELECT " . (api_is_western_name_order() ? "CONCAT(firstname,' ', lastname)" : "CONCAT(lastname,' ', firstname)") . " AS name
				FROM " . $this->dropbox_cnf("tbl_user") . "
				WHERE user_id='$id'";
        $result = Database::query($sql, __FILE__, __LINE__);
        $res = Database::fetch_array($result);

        if ($res == FALSE)
            return FALSE;
        return stripslashes($res["name"]);
    }

    /**
     * This function displays the firstname and lastname of the user as a link to the user tool.
     *
     * @see this is the same function as in the new forum, so this probably has to move to a user library.
     *
     * @todo move this function to the user library
     *
     * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
     * @version march 2006
     */
    public function display_user_link($user_id, $name = '') {
        global $_otherusers;

        if ($user_id <> 0) {
            if ($name == '') {
                $table_user = Database::get_main_table(TABLE_MAIN_USER);
                $sql = "SELECT * FROM $table_user WHERE user_id='" . Database::escape_string($user_id) . "'";
                $result = Database::query($sql, __FILE__, __LINE__);
                $row = Database::fetch_array($result);
                return "<a class='user_info' id='user_id_" . $row['user_id'] . "' href='javascript:void(0)' >" . api_get_person_name($row['firstname'], $row['lastname']) . "</a>";
            } else {
                $user_id = intval($user_id);
                return "<a class='user_info' id='user_id_" . $user_id . "' href='javascript:void(0)' >" . Security::remove_XSS($name) . "</a>";
            }
        } else {
            return $name . ' (' . get_lang('Anonymous') . ')';
        }
    }

    /**
     * @desc this function checks if the key exists. If this is the case it returns the value, if not it returns 0
     * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
     * @version march 2006
     */
    public function check_number_feedback($key, $array) {
        if (is_array($array)) {
            if (key_exists($key, $array)) {
                return $array[$key];
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

}

// end class
