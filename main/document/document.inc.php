<?php

// $Id: document.inc.php 22074 2009-07-14 16:28:37Z jhp1411 $

/* For licensing terms, see /dokeos_license.txt */

/**
  ==============================================================================
 * 	extra functions for the document module (see also /main/inc/lib/document.lib.php
 *
 * 	@package dokeos.document
  ==============================================================================
 */

/**
 * Builds the form thats enables the user to
 * select a directory to browse/upload in
 *
 * @param array 	An array containing the folders we want to be able to select
 * @param string	The current folder (path inside of the "document" directory, including the prefix "/")
 * @param string	Group directory, if empty, prevents documents to be uploaded (because group documents cannot be uploaded in root)
 * @param	boolean	Whether to change the renderer (this will add a template <span> to the QuickForm object displaying the form)
 * @return string html form
 */
function build_directory_selector($folders, $curdirpath, $group_dir = '', $changeRenderer = false, $into_lp = false) {

    $folder_titles = array();
    if (api_get_setting('use_document_title') == 'true') {
        if (is_array($folders)) {
            $escaped_folders = array();
            foreach ($folders as $key => $val) {
                $escaped_folders[$key] = Database::escape_string($val);
            }
            $folder_sql = implode("','", $escaped_folders);

            $doc_table = Database::get_course_table(TABLE_DOCUMENT);
            $sql = "SELECT * FROM $doc_table WHERE filetype='folder' AND path IN ('" . $folder_sql . "')";
            $res = api_sql_query($sql, __FILE__, __LINE__);
            $folder_titles = array();
            while ($obj = Database::fetch_object($res)) {
                $folder_titles[$obj->path] = $obj->title;
            }
        }
    } else {
        if (is_array($folders)) {
            foreach ($folders as $folder) {
                $folder_titles[$folder] = basename($folder);
            }
        }
    }

    require_once (api_get_path(LIBRARY_PATH) . 'formvalidator/FormValidator.class.php');
    //$form = new FormValidator('selector','POST',api_get_self().'?'.api_get_cidreq(), "", array('class'=>"abs",'style'=>"right:0; top:16px; width:23%;"));
    $form = new FormValidator('selector', 'POST', api_get_self() . '?' . api_get_cidreq(), "", array('class' => "abs", 'style' => "float: right; position: relative;"));
    // Hide the directory list'
    if ($into_lp)
        $form->addElement('html', '<div style="display:none;">');
    $parent_select = $form->addElement('select', 'curdirpath', get_lang('CurrentDirectory'), '', ' id="curdirpath_id" style="width:300px;" ');
    if ($into_lp)
        $form->addElement('html', '</div>');
    //if($changeRenderer==true){
    $renderer = $form->defaultRenderer();
    $renderer->setElementTemplate('<span>{label} : {element}</span> ', 'curdirpath');
    //}
    //group documents cannot be uploaded in the root
    if (empty($group_dir)) {
        $parent_select->addOption(get_lang('HomeDirectory'), '/');
        if (is_array($folders)) {
            foreach ($folders as $folder) {
                $selected = ($curdirpath == $folder) ? ' selected="selected"' : '';
                $path_parts = explode('/', $folder);

                if ($folder_titles[$folder] == 'shared_folder') {
                    $folder_titles[$folder] = get_lang('SharedFolder');
                } elseif (strstr($folder_titles[$folder], 'sf_user_')) {
                    $userinfo = Database::get_user_info_from_id(substr($folder_titles[$folder], 8));
                    $folder_titles[$folder] = $userinfo['lastname'] . ', ' . $userinfo['firstname'];
                }

                $label = str_repeat('&nbsp;&nbsp;&nbsp;', count($path_parts) - 2) . ' &mdash; ' . $folder_titles[$folder];

                $folder_image = 'closed';
                if ($selected != '') {
                    $folder_image = 'open';
                }
                $parent_select->addOption($label, $folder, array('data-image' => api_get_path(WEB_LIBRARY_PATH) . 'javascript/msdropdown/images/skin1/' . $folder_image . '.png'));
                if ($selected != '')
                    $parent_select->setSelected($folder);
            }
        }
    }
    else {
        if (is_array($folders)) {
            foreach ($folders as $folder) {
                $selected = ($curdirpath == $folder) ? ' selected="selected"' : '';
                $label = $folder_titles[$folder];
                if ($folder == $group_dir) {
                    $label = '/ (' . get_lang('HomeDirectory') . ')';
                } else {
                    $path_parts = explode('/', str_replace($group_dir, '', $folder));
                    $label = str_repeat('&nbsp;&nbsp;&nbsp;', count($path_parts) - 2) . ' &mdash; ' . $label;
                }
                $folder_image = 'closed';
                if ($selected != '') {
                    $folder_image = 'open';
                }
                $parent_select->addOption($label, $folder, array('data-image' => api_get_path(WEB_LIBRARY_PATH) . 'javascript/msdropdown/images/skin1/' . $folder_image . '.png'));
                if ($selected != '')
                    $parent_select->setSelected($folder);
            }
        }
    }
    if ($form->validate()) {
        $user_data = $form->getSubmitValues();

        header("Location: " . api_get_path(WEB_PATH) . "main/document/document.php?" . api_get_cidreq() . "&curdirpath=" . $path);
//        echo '<script type="text/javascript">
//                    window.location.href="' . api_get_path(WEB_PATH) . "main/document/document.php?" . api_get_cidreq() . "&curdirpath=" . $path . '";
//             </script>';
    }

    $form = $form->toHtml();

    return $form;
}

/* Function getAudioVideo to open in Media files in the respective player */

function getAudioVideo($title, $i, $path, $filetype, $tooltip_title, $j) {
    global $_course;
    $ext = explode(".", $title);
    $return .= build_document_icon_tag($filetype, $tooltip_title);

    if ($ext[1] == 'flv' || $ext[1] == 'mp4' || $ext[1] == 'mov') {
        $pl_width = 400;
        $pl_height = 300;
        $return .= '<div id="popUpDiv' . $j . '" title="' . $title . '" style="display:none;"><div>';
        $return .= "<div id='mediaplayer" . $j . "'>Playing educational video on the Dokeos platform</div>";

        $return .= "<script type=\"text/javascript\">
                                jwplayer(\"mediaplayer$j\").setup({                                    
                                    file: '" . api_get_path(WEB_COURSE_PATH) . $_course['path'] . "/document" . $path . "',
                                    width: '" . $pl_width . "',
                                    height:'" . $pl_height . "',
                                    primary: 'flash'
                                });
                                </script>";
        $return .= '</div></div>';
    } elseif ($ext[1] == 'mp3' || $ext[1] == 'ogg' || $ext[1] == 'ogv') {
        $pl_width = 400;
        $pl_height = 40;
        $return .= '<div id="popUpDiv' . $j . '" title="' . $title . '" style="display:none;"><div>';
        $return .= "<div id='mediaplayer" . $j . "'>Playing educational video on the Dokeos platform</div>";
        $return .= "<script type=\"text/javascript\">
                                jwplayer(\"mediaplayer$j\").setup({                                    
                                    file: '" . api_get_path(WEB_COURSE_PATH) . $_course['path'] . "/document" . $path . "',
                                    width: '" . $pl_width . "',
                                    height:'" . $pl_height . "',
                                    controlbar: 'bottom',
                                    primary: 'flash'
                                });
                                </script>";
        $return .= '</div></div>';
    } elseif ($ext[1] == 'mpg' || $ext[1] == 'wmv' || $ext[1] == 'wma' || $ext[1] == 'avi') {

        $return .= '<OBJECT ID="MediaPlayer" WIDTH="450" HEIGHT="350" CLASSID="CLSID:22D6F312-B0F6-11D0-94AB-0080C74C7E95"
                            STANDBY="Loading Windows Media Player components..." TYPE="application/x-oleobject">
                            <PARAM NAME="FileName" VALUE="' . $title . '">
                            <PARAM name="autostart" VALUE="true">
                            <PARAM name="ShowControls" VALUE="true">
                            <param name="ShowStatusBar" value="true">
                            <PARAM name="ShowDisplay" VALUE="true">
                            <EMBED TYPE="application/x-mplayer2" SRC="' . $title . '" NAME="MediaPlayer"
                            WIDTH="400" HEIGHT="300" ShowControls="1" ShowStatusBar="1" ShowDisplay="1" autostart="1"> </EMBED>
                            </OBJECT>';
    }
    return $return;
}

/**
 * Create a html hyperlink depending on if it's a folder or a file
 *
 * @param string $curdirpath
 * @param string $www
 * @param string $title
 * @param string $path
 * @param string $filetype (file/folder)
 * @param int $visibility (1/0)
 * @param int $show_as_icon - if it is true, only a clickable icon will be shown
 * @return string url
 */
function create_document_link($curdirpath, $www, $title, $path, $filetype, $size, $visibility, $show_as_icon = false, $doc_id = '', $i) {
    global $dbl_click_id;
    $titlename = $path;
    if (isset($_SESSION['_gid'])) {
        $req_gid = '&gidReq=' . $_SESSION['_gid'];
    } else {
        $req_gid = '';
    }
    //$url_path = urlencode($path);// This produce this error message when the folder name has white spaces : link appears to be broken
    $url_path = $path;
    //add class="invisible" on invisible files
    $visibility_class = ($visibility == 0) ? ' class="invisible"' : '';
    $target = '_self';
    $ext = explode('.', $path);
    $ext = strtolower($ext[sizeof($ext) - 1]);

    if (!$show_as_icon) {
        //build download link (icon)
        $forcedownload_link = ($filetype == 'folder') ? api_get_self() . '?' . api_get_cidreq() . '&action=downloadfolder&path=' . $url_path . $req_gid : api_get_self() . '?' . api_get_cidreq() . '&action=download&id=' . $url_path . $req_gid;
        //folder download or file download?
        //$forcedownload_icon=($filetype=='folder')?'folder_zip.gif':'filesave.gif';
        $forcedownload_icon = ($filetype == 'folder') ? 'go-jump.png' : 'go-jump.png';
        //prevent multiple clicks on zipped folder download
        $prevent_multiple_click = ($filetype == 'folder') ? " onclick=\"javascript:if(typeof clic_$dbl_click_id == 'undefined' || clic_$dbl_click_id == false) { clic_$dbl_click_id=true; window.setTimeout('clic_" . ($dbl_click_id++) . "=false;',10000); } else { return false; }\"" : '';
    }

    if ($filetype == 'file') {
        if ($ext == 'htm' || $ext == 'html') {
            $url = "showinframes.php?" . api_get_cidreq() . "&curdirpath=" . $curdirpath . "&file=" . $url_path . $req_gid;
        }
        /* elseif($ext == 'docx' || $ext == 'doc')
          {
          $url = "showinframes.php?".api_get_cidreq()."&curdirpath=".$curdirpath."&file=".$url_path.$req_gid."&from=googledoc";
          } */ elseif ($ext == 'gif' || $ext == 'jpg' || $ext == 'jpeg' || $ext == 'png') {
            $url = "slideshow.php?" . api_get_cidReq() . "&curdirpath=" . $curdirpath . "&linkfile=" . $url_path . '&slide_id=' . $doc_id;
        } elseif ($ext == 'mp3' || $ext == 'ogg' || $ext == 'ogv') {
            $path = str_replace('%2F', '/', $url_path);
            $url = 'javascript:void(0)';
            $visibility_class = 'class="open_media_audio_in_window" id="media_content_' . $i . '"';
        } elseif ($ext == 'swf') {
            $path = str_replace('%2F', '/', $url_path) . '?' . api_get_cidreq();
            $path_url = $www . $path;
            $url = '../search/view_flash.php?path=' . $path_url . '&width=800&height=520';
            $visibility_class = 'class="thickbox"';
        } elseif ($ext == 'flv' || $ext == 'mp4' || $ext == 'mov') {
            $path = str_replace('%2F', '/', $url_path);
            $url = 'javascript:void(0)';
            $visibility_class = 'class="open_media_video_in_window" id="media_content_' . $i . '"';
        } else {
            //url-encode for problematic characters (we may not call them dangerous characters...)
            $path = str_replace('%2F', '/', $url_path) . '?' . api_get_cidreq();
            $url = $www . $path;
        }
        //files that we want opened in a new window
        if ($ext == 'txt') { //add here
            $target = '_blank';
        }
    } else {
        $url = api_get_self() . '?' . api_get_cidreq() . '&curdirpath=' . $url_path . $req_gid;
    }

    //the little download icon
    //$tooltip_title = str_replace('?cidReq='.$_GET['cidReq'],'',basename($path));
    $tooltip_title = explode('?', basename($path));
    $tooltip_title = $tooltip_title[0];

    if ($tooltip_title == 'shared_folder') {
        $tooltip_title_alt = get_lang('SharedFolder');
    } elseif (strstr($tooltip_title, 'sf_user_')) {
        $userinfo = Database::get_user_info_from_id(substr($tooltip_title, 8));
        $tooltip_title_alt = $userinfo['lastname'] . ', ' . $userinfo['firstname'];
    } else {
        $tooltip_title_alt = $tooltip_title;
    }
    $title = cut($title, 70);
    if (!$show_as_icon) {
        $titlename = ($filetype == 'folder') ? $titlename : '';
        $force_download_html = ($size == 0) ? '' : '<a href="' . $forcedownload_link . '" style="float:right"' . $prevent_multiple_click . '>' . Display::return_icon('pixel.gif', get_lang('Download'), array('hspace' => '5', 'align' => 'middle', 'class' => "actionplaceholdericon forcedownload")) . '</a>';
        return '<a href="' . $url . '" title="" target="' . $target . '"' . $visibility_class . ' style="float:left">' . $title . '</a>' . $force_download_html;
    } else {
        $tooltip_title = "";
        $titlename = ($filetype == 'folder') ? $titlename : $titlename;
        if ($ext == 'mp3' || $ext == 'ogg' || $ext == 'ogv') {
            return '<a href="' . $url . '" title="" ' . $visibility_class . ' style="float:left">' . getAudioVideo($title, $i, $path, $filetype, $tooltip_title, $i) . '</a>';
        } elseif ($ext == 'flv' || $ext == 'mp4' || $ext == 'mov') {
            return '<a href="' . $url . '" title="" ' . $visibility_class . ' style="float:left">' . getAudioVideo($title, $i, $path, $filetype, $tooltip_title, $i) . '</a>';
        } else {
            return '<a href="' . $url . '" title="" target="' . $target . '"' . $visibility_class . ' style="float:left">' . build_document_icon_tag($filetype, $titlename) . '</a>';
        }
    }
}

/**
 * Builds an img html tag for the filetype
 *
 * @param string $type (file/folder)
 * @param string $path
 * @return string img html tag
 */
function build_document_icon_tag($type, $path) {
    $basename = basename($path);
    $action_icon = 'actionplaceholdericon actionnewfolder';
    if ($type == 'file') {
        $icon = choose_image($basename);
        // $action_icon = 'actionfile';
        $action_icon = 'actionplaceholdericon ' . $icon;
    } else {
        if ($basename == 'shared_folder') {
            $icon = 'shared_folder.png';
            if (api_is_allowed_to_edit()) {
                $basename = get_lang('HelpSharedFolder');
            } else {
                $basename = get_lang('SharedFolder');
            }
            $action_icon = 'actionplaceholdericon actionsharedfolder';
        } elseif (strstr($basename, 'sf_user_')) {
            $userinfo = Database::get_user_info_from_id(substr($basename, 8));
            $image_path = UserManager::get_user_picture_path_by_id(substr($basename, 8), 'web', false, true);

            if ($image_path['file'] == 'unknown.jpg') {
                $icon = $image_path['file'];
                $action_icon = 'actionplaceholdericon actionunknowfile';
            } else {
                $icon = '../upload/users/' . substr($basename, 8) . '/' . $image_path['file'];
                $action_icon = 'actionplaceholdericon actionfile';
            }
            $basename = $userinfo['lastname'] . ', ' . $userinfo['firstname'];
        } else {
            if (($basename == 'audio' || $basename == 'flash' || $basename == 'images' || $basename == 'video') && api_is_allowed_to_edit() == true) {
                $basename = get_lang('HelpDefaultDirDocuments');
                $action_icon = 'actionplaceholdericon actionnewfolder';
            }
            $icon = 'folder_new_22.png';
        }
    }
    return Display::return_icon('pixel.gif', '', array('hspace' => '5', 'align' => 'middle', 'class' => $action_icon));
    //return Display::return_icon('pixel.gif', $basename, array('hspace'=>'5', 'align' => 'middle','class' => "actionplaceholdericon $action_icon"));
    //return Display::return_icon($icon, $basename, array('hspace'=>'5', 'align' => 'middle', 'height'=> 22, 'width' => 22));
}

/**
 * Creates the row of edit certificate icons for a file/folder
 *
 * @param string $curdirpath current path (cfr open folder)
 * @param string $type (file/folder)
 * @param string $path dbase path of file/folder
 * @param int $visibility (1/0)
 * @param int $id dbase id of the document
 * @return string html img tags with hyperlinks
 */
function build_certificate_icons($curdirpath, $type, $path, $visibility, $id, $is_template, $is_read_only = 0, $gradebook_category = '', $icon_type = 'certificate') {
    if (isset($_SESSION['_gid'])) {
        $req_gid = '&gidReq=' . $_SESSION['_gid'];
    } else {
        $req_gid = '';
    }
    //build URL-parameters for table-sorting
    $sort_params = array();
    if (isset($_GET['column'])) {
        $sort_params[] = 'column=' . Security::remove_XSS($_GET['column']);
    }
    if (isset($_GET['page_nr'])) {
        $sort_params[] = 'page_nr=' . Security::remove_XSS($_GET['page_nr']);
    }
    if (isset($_GET['per_page'])) {
        $sort_params[] = 'per_page=' . Security::remove_XSS($_GET['per_page']);
    }
    if (isset($_GET['direction'])) {
        $sort_params[] = 'direction=' . Security::remove_XSS($_GET['direction']);
    }
    $sort_params = implode('&', $sort_params);
    $curdirpath = urlencode($curdirpath);
    $is_certificate_mode = DocumentManager::is_certificate_mode($path);
    $modify_icons = '';

    if ($type == 'file' && pathinfo($path, PATHINFO_EXTENSION) == 'html') {
        if ($is_template == 0) {
            if ((isset($_GET['curdirpath']) && $_GET['curdirpath'] <> '/certificates') || !isset($_GET['curdirpath'])) {
                $modify_icons .= '<a href="' . api_get_self() . '?' . api_get_cidreq() . '&curdirpath=' . $curdirpath . '&add_as_template=' . $id . $req_gid . '&' . $sort_params . '">' . Display::return_icon('pixel.gif', get_lang('AddAsTemplate'), array('class' => 'actionplaceholdericon actiontemplate')) . '</a>';
            }
            if (isset($_GET['curdirpath']) && $_GET['curdirpath'] == '/certificates') {//allow attach certificate to course
                $visibility_icon_certificate = 'certificate invisible';
                if (DocumentManager::get_default_certificate_id(api_get_course_id()) == $id) {
                    $visibility_icon_certificate = 'certificate';
                    $certificate = get_lang('DefaultCertificate');
                    $preview = get_lang('PreviewCertificate');
                    $is_preview = true;
                } else {
                    $is_preview = false;
                    $certificate = get_lang('NoDefaultCertificate');
                }
                if (isset($_GET['selectcat'])) {
                    if ($icon_type == 'certificate') {
                        $modify_icons .= '<a href="' . api_get_self() . '?' . api_get_cidreq() . '&curdirpath=' . $curdirpath . '&selectcat=' . Security::remove_XSS($_GET['selectcat']) . '&set_certificate=' . $id . $req_gid . '&' . $sort_params . '">' . Display::return_icon('pixel.gif', $certificate, array('class' => 'actionplaceholdericon action' . $visibility_icon_certificate)) . '</a>';
                    }
                    if ($is_preview && $icon_type != 'certificate') {
                        $modify_icons .= '&nbsp;<a target="_blank"  href="' . api_get_self() . '?' . api_get_cidreq() . '&curdirpath=' . $curdirpath . '&set_preview=' . $id . $req_gid . '&' . $sort_params . '" >' . Display::return_icon('pixel.gif', $preview, array('class' => 'actionplaceholdericon actionpreview')) . '</a>';
                    }
                }
            }
        } else {
            // Remove template
            //$modify_icons .= '&nbsp;<a href="'.api_get_self().'?'.api_get_cidreq().'&curdirpath='.$curdirpath.'&remove_as_template='.$id.$req_gid.'&'.$sort_params.'"><img src="../img/wizard_gray_small.gif" border="0" title="'.get_lang('RemoveAsTemplate').'" alt=""'.get_lang('RemoveAsTemplate').'" /></a>';
        }
    }
    return '&nbsp;&nbsp;' . $modify_icons;
}

function build_edit_icons($curdirpath, $type, $path, $visibility, $id, $is_template, $is_read_only = 0, $gradebook_category = '') {
    if (isset($_SESSION['_gid'])) {
        $req_gid = '&gidReq=' . $_SESSION['_gid'];
    } else {
        $req_gid = '';
    }
    //build URL-parameters for table-sorting
    $sort_params = array();
    if (isset($_GET['column'])) {
        $sort_params[] = 'column=' . Security::remove_XSS($_GET['column']);
    }
    if (isset($_GET['page_nr'])) {
        $sort_params[] = 'page_nr=' . Security::remove_XSS($_GET['page_nr']);
    }
    if (isset($_GET['per_page'])) {
        $sort_params[] = 'per_page=' . Security::remove_XSS($_GET['per_page']);
    }
    if (isset($_GET['direction'])) {
        $sort_params[] = 'direction=' . Security::remove_XSS($_GET['direction']);
    }
    $sort_params = implode('&', $sort_params);
    $curdirpath = urlencode($curdirpath);
    $is_certificate_mode = DocumentManager::is_certificate_mode($path);
    $modify_icons = '';

    if ($is_read_only) {
        return Display::return_icon('pixel.gif', get_lang('Modify'), array('class' => 'actionplaceholdericon actionedit invisible'));
    } else {
        if ($is_certificate_mode) {
            return '<a href="edit_document.php?' . api_get_cidreq() . '&curdirpath=' . $curdirpath . '&file=' . urlencode($path) . $req_gid . '&selectcat=' . $gradebook_category . '">' . Display::return_icon('pixel.gif', get_lang('Modify'), array('class' => 'actionplaceholdericon actionedit')) . '</a>';
        } else {
            return '<a href="edit_document.php?' . api_get_cidreq() . '&curdirpath=' . $curdirpath . '&file=' . urlencode($path) . $req_gid . '">' . Display::return_icon('pixel.gif', get_lang('Modify'), array('class' => 'actionplaceholdericon actionedit')) . '</a>';
        }
    }
}

function build_move_icons($curdirpath, $type, $path, $visibility, $id, $is_template, $is_read_only = 0, $gradebook_category = '') {
    if (isset($_SESSION['_gid'])) {
        $req_gid = '&gidReq=' . $_SESSION['_gid'];
    } else {
        $req_gid = '';
    }
    //build URL-parameters for table-sorting
    $sort_params = array();
    if (isset($_GET['column'])) {
        $sort_params[] = 'column=' . Security::remove_XSS($_GET['column']);
    }
    if (isset($_GET['page_nr'])) {
        $sort_params[] = 'page_nr=' . Security::remove_XSS($_GET['page_nr']);
    }
    if (isset($_GET['per_page'])) {
        $sort_params[] = 'per_page=' . Security::remove_XSS($_GET['per_page']);
    }
    if (isset($_GET['direction'])) {
        $sort_params[] = 'direction=' . Security::remove_XSS($_GET['direction']);
    }
    $sort_params = implode('&', $sort_params);
    $curdirpath = urlencode($curdirpath);
    $is_certificate_mode = DocumentManager::is_certificate_mode($path);
    $modify_icons = '';

    if ($is_read_only) {
        return Display::return_icon('pixel.gif', get_lang('Move'), array('class' => 'actionplaceholdericon actionmove')) . PHP_EOL;
    } else {
        return '<a href="' . api_get_self() . '?' . api_get_cidreq() . '&curdirpath=' . $curdirpath . '&move=' . urlencode($path) . $req_gid . '">' . Display::return_icon('pixel.gif', get_lang('Move'), array('class' => 'actionplaceholdericon actionmove')) . '</a>' . PHP_EOL;
    }
}

function build_visible_icons($curdirpath, $type, $path, $visibility, $id, $is_template, $is_read_only = 0, $gradebook_category = '') {
    if (isset($_SESSION['_gid'])) {
        $req_gid = '&gidReq=' . $_SESSION['_gid'];
    } else {
        $req_gid = '';
    }
    //build URL-parameters for table-sorting
    $sort_params = array();
    if (isset($_GET['column'])) {
        $sort_params[] = 'column=' . Security::remove_XSS($_GET['column']);
    }
    if (isset($_GET['page_nr'])) {
        $sort_params[] = 'page_nr=' . Security::remove_XSS($_GET['page_nr']);
    }
    if (isset($_GET['per_page'])) {
        $sort_params[] = 'per_page=' . Security::remove_XSS($_GET['per_page']);
    }
    if (isset($_GET['direction'])) {
        $sort_params[] = 'direction=' . Security::remove_XSS($_GET['direction']);
    }
    $sort_params = implode('&', $sort_params);
    $visibilityLang = ($visibility == 0) ? get_lang('Visible') : get_lang('Invisible');
    $visibility_icon = ($visibility == 0) ? 'actioninvisible' : 'actionvisible';
    $visibility_command = ($visibility == 0) ? 'set_visible' : 'set_invisible';
    $curdirpath = urlencode($curdirpath);
    $is_certificate_mode = DocumentManager::is_certificate_mode($path);
    $modify_icons = '';

    if ($is_read_only) {
        return Display::return_icon('pixel.gif', get_lang('VisibilityCannotBeChanged'), array('class' => 'actionplaceholdericon ' . $visibility_icon . ' invisible')) . PHP_EOL;
    } else {
        if ($is_certificate_mode) {
            return '<a href="' . api_get_self() . '?' . api_get_cidreq() . '&curdirpath=' . $curdirpath . '&' . $visibility_command . '=' . $id . $req_gid . '&' . $sort_params . '&selectcat=' . $gradebook_category . '&type=list">' . Display::return_icon('pixel.gif', $visibilityLang, array('class' => 'actionplaceholdericon ' . $visibility_icon)) . '</a>' . PHP_EOL;
        } else {
            $modify_icons .= '<a href="' . api_get_self() . '?' . api_get_cidreq() . '&curdirpath=' . $curdirpath . '&' . $visibility_command . '=' . $id . $req_gid . '&' . $sort_params . '&type=list">' . Display::return_icon('pixel.gif', $visibilityLang, array('class' => 'actionplaceholdericon ' . $visibility_icon)) . '</a>' . PHP_EOL;
        }
    }

    return $modify_icons;
}

/**
 * Creates the row of edit icons for a file/folder
 *
 * @param string $curdirpath current path (cfr open folder)
 * @param string $type (file/folder)
 * @param string $path dbase path of file/folder
 * @param int $visibility (1/0)
 * @param int $id dbase id of the document
 * @return string html img tags with hyperlinks
 */
function build_template_icons($curdirpath, $type, $path, $visibility, $id, $is_template, $is_read_only = 0) {
    if (isset($_SESSION['_gid'])) {
        $req_gid = '&gidReq=' . $_SESSION['_gid'];
    } else {
        $req_gid = '';
    }
    //build URL-parameters for table-sorting
    $sort_params = array();
    if (isset($_GET['column'])) {
        $sort_params[] = 'column=' . Security::remove_XSS($_GET['column']);
    }
    if (isset($_GET['page_nr'])) {
        $sort_params[] = 'page_nr=' . Security::remove_XSS($_GET['page_nr']);
    }
    if (isset($_GET['per_page'])) {
        $sort_params[] = 'per_page=' . Security::remove_XSS($_GET['per_page']);
    }
    if (isset($_GET['direction'])) {
        $sort_params[] = 'direction=' . Security::remove_XSS($_GET['direction']);
    }
    $sort_params = implode('&', $sort_params);
    $curdirpath = urlencode($curdirpath);

    $template_icons = '';

    if ($type == 'file' && pathinfo($path, PATHINFO_EXTENSION) == 'html') {
        if ($is_template == 0) {
            $template_icons .= '<a href="' . api_get_self() . '?' . api_get_cidreq() . '&curdirpath=' . $curdirpath . '&add_as_template=' . $id . $req_gid . '&' . $sort_params . '">' . Display::return_icon('pixel.gif', get_lang('AddAsTemplate'), array('class' => 'actionplaceholdericon actiontemplate')) . '</a>';
        } else {
            $template_icons .= '<a href="' . api_get_self() . '?' . api_get_cidreq() . '&curdirpath=' . $curdirpath . '&remove_as_template=' . $id . $req_gid . '&' . $sort_params . '">' . Display::return_icon('pixel.gif', get_lang('RemoveAsTemplate'), array('class' => 'actionplaceholdericon actiontemplate invisible')) . '</a>';
        }
    }
    return $template_icons;
}

/**
 * Creates the row of edit icons for a file/folder
 *
 * @param string $curdirpath current path (cfr open folder)
 * @param string $type (file/folder)
 * @param string $path dbase path of file/folder
 * @param int $visibility (1/0)
 * @param int $id dbase id of the document
 * @return string html img tags with hyperlinks
 */
function build_addcourse_icons($curdirpath, $type, $path, $visibility, $id, $document_name, $is_read_only = 0) {
    if (isset($_SESSION['_gid'])) {
        $req_gid = '&gidReq=' . $_SESSION['_gid'];
    } else {
        $req_gid = '';
    }
    //build URL-parameters for table-sorting
    $sort_params = array();
    if (isset($_GET['column'])) {
        $sort_params[] = 'column=' . Security::remove_XSS($_GET['column']);
    }
    if (isset($_GET['page_nr'])) {
        $sort_params[] = 'page_nr=' . Security::remove_XSS($_GET['page_nr']);
    }
    if (isset($_GET['per_page'])) {
        $sort_params[] = 'per_page=' . Security::remove_XSS($_GET['per_page']);
    }
    if (isset($_GET['direction'])) {
        $sort_params[] = 'direction=' . Security::remove_XSS($_GET['direction']);
    }
    $sort_params = implode('&', $sort_params);
    $curdirpath = urlencode($curdirpath);
    $addcourse_icons = '';

    if ($type == 'file') {
        $addcourse_icons .= '<a class="thickbox" href="integrate_doc_in_course.php?' . api_get_cidreq() . '&curdirpath=' . $curdirpath . '&add_to_course=' . $id . $req_gid . '&docname=' . $document_name . '&' . $sort_params . 'height=150&width=500" title="' . get_lang('IntegrateDocInCourse') . '">' . Display::return_icon('pixel.gif', get_lang('IntegrateDocInCourse'), array('class' => 'actionplaceholdericon actioncourseadd')) . '</a>';
    }
    return $addcourse_icons;
}

function build_move_to_selector($folders, $curdirpath, $move_file, $group_dir = '') {
    $form = '<form class="outer_form" name="move_to" action="' . api_get_self() . '?' . api_get_cidreq() . '" method="post">' . "\n";
    $form .= '<input type="hidden" name="move_file" value="' . $move_file . '" />' . "\n";

    $form .= '	<div class="formw">';
    $form .= '<div class="row">';
    $form .= '	<div class="label">';
    $form .= get_lang('MoveTo') . '&nbsp;&nbsp;';
    $form .= '	</div>';
    $form .= '	</div>';


    $form .= '	<div>';
    $form .= ' <select name="move_to">' . "\n";

    //group documents cannot be uploaded in the root
    if ($group_dir == '') {
        if ($curdirpath != '/') {
            $form .= '<option value="/">/ (' . get_lang('HomeDirectory') . ')</option>';
        }
        if (is_array($folders)) {
            foreach ($folders AS $folder) {
                //you cannot move a file to:
                //1. current directory
                //2. inside the folder you want to move
                //3. inside a subfolder of the folder you want to move
                if (($curdirpath != $folder) && ($folder != $move_file) && (substr($folder, 0, strlen($move_file) + 1) != $move_file . '/')) {
                    $path_displayed = $folder;

                    // if document title is used, we have to display titles instead of real paths...
                    if (api_get_setting('use_document_title')) {
                        $path_displayed = get_titles_of_path($folder);
                    }
                    $form .= '<option value="' . $folder . '">' . $path_displayed . '</option>' . "\n";
                }
            }
        }
    } else {
        foreach ($folders AS $folder) {
            if (($curdirpath != $folder) && ($folder != $move_file) && (substr($folder, 0, strlen($move_file) + 1) != $move_file . '/')) {//cannot copy dir into his own subdir
                if (api_get_setting('use_document_title')) {
                    $path_displayed = get_titles_of_path($folder);
                }

                $display_folder = substr($path_displayed, strlen($group_dir));
                $display_folder = ($display_folder == '') ? '/ (' . get_lang('HomeDirectory') . ')' : $display_folder;

                $form .= '<option value="' . $folder . '">' . $display_folder . '</option>' . "\n";
            }
        }
    }

    $form .= '		</select>' . "\n";
    $form .= '	</div>';

    $form .= '<div class="row">';
    $form .= '	<div class="label"></div>';
    $form .= '	<div class="formw">';
    $form .= '		<button type="submit" class="next" name="move_file_submit">' . get_lang('MoveFile') . '</button>' . "\n";
    $form .= '	</div>';
    $form .= '</div>';
    $form .= '	</div>';
    $form .= '</form>';

    //$form .= '<div style="clear: both; margin-bottom: 10px;"></div>';

    return $form;
}

/**
 * get the path translated with title of docs and folders
 * @param string the real path
 * @return the path which should be displayed
 */
function get_titles_of_path($path) {
    global $tmp_folders_titles;

    $nb_slashes = substr_count($path, '/');
    $tmp_path = '';
    $current_slash_pos = 0;
    $path_displayed = '';
    for ($i = 0; $i < $nb_slashes; $i++) { // foreach folders of the path, retrieve title.
        $current_slash_pos = strpos($path, '/', $current_slash_pos + 1);
        $tmp_path = substr($path, strpos($path, '/', 0), $current_slash_pos);

        if (empty($tmp_path)) // if empty, then we are in the final part of the path
            $tmp_path = $path;

        if (!empty($tmp_folders_titles[$tmp_path])) { // if this path has soon been stored here we don't need a new query
            $path_displayed .= $tmp_folders_titles[$tmp_path];
        } else {
            $sql = 'SELECT title FROM ' . Database::get_course_table(TABLE_DOCUMENT) . ' WHERE path LIKE BINARY "' . $tmp_path . '"';
            $rs = api_sql_query($sql, __FILE__, __LINE__);
            $tmp_title = '/' . Database::result($rs, 0, 0);
            $path_displayed .= $tmp_title;
            $tmp_folders_titles[$tmp_path] = $tmp_title;
        }
    }
    return $path_displayed;
}

/**
 * This function displays the name of the user and makes the link tothe user tool.
 *
 * @param $user_id
 * @param $name
 * @return a link to the userInfo.php
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @version february 2006, dokeos 1.8
 */
function display_user_link_document($user_id, $name) {
    if ($user_id <> 0) {
        return '<a href="../user/userInfo.php?uInfo=' . $user_id . '">' . $name . '</a>';
    } else {
        return get_lang('Anonymous');
    }
}

function create_dir_form($path = "") {
    $path = empty($path) ? Security::remove_XSS($_GET['curdirpath']) : Security::remove_XSS($path);
    //create the form that asks for the directory name
    $new_folder_text = '<form id="create_folder" name="create_folder" action="' . api_get_self() . '?' . api_get_cidreq() . '" class="outer_form" method="post">';
    $new_folder_text .= '<input type="hidden" name="curdirpath" value="' . $path . '" />';
    // form title
    $new_folder_text .= '<div class="row"><div class="form_header">' . get_lang('CreateDir') . '</div></div>';

    // folder field
    $new_folder_text .= '<div class="row">';
    $new_folder_text .= '<div class="label label1">' . get_lang('NewDir') . '</div>';
    $new_folder_text .= '<div class="formw formw1"><input type="text" id="dirname" name="dirname" class="focus" autofocus style="width:40%;"></div>';
    $new_folder_text .= '</div>';
    // submit button
    $new_folder_text .= '<div class="row">';
    $new_folder_text .= '<div class="label">&nbsp;</div>';
    $new_folder_text .= '<div class="formw"><button type="button" class="add" id="create_dir" name="create_dir">' . get_lang('CreateFolder') . '</button></div>';
    $new_folder_text .= '</div>';
    $new_folder_text .= '</form>';
    $new_folder_text .= '<div style="clear: both; margin-bottom: 10px;"></div>';

    return $new_folder_text;
}

?>
