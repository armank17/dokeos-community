<?php
/*
 * 
 * 
 * @author Armando Aliaga <aliaga.armando@yahoo.es>
 */

class MindMapManager {

    public function show_list_sent($curdirpath, $src_path) {
        global $_user;
        $tbl_documents = Database::get_course_table(TABLE_DOCUMENT);
        $propTable = Database::get_course_table(TABLE_ITEM_PROPERTY);
        $return = '';
        $arrLP = array();

        /* $sql1 = "(SELECT distinct(title) AS title,id,display_order FROM $tbl_documents doc,$propTable prop ". api_get_session_condition(api_get_session_id(),false,true) ." AND doc.id = prop.ref AND prop.tool = '".TOOL_DOCUMENT."' AND doc.filetype = 'file' AND doc.path LIKE '".$curdirpath."%' AND doc.path NOT LIKE '".$curdirpath."%/%' AND prop.visibility = 1 AND prop.to_user_id IS NULL".")";
          $sql2 = "(SELECT distinct(title) AS title,id,display_order FROM $tbl_documents doc,$propTable prop ". api_get_session_condition(api_get_session_id(),false,true) ." AND doc.id = prop.ref AND prop.tool = '".TOOL_DOCUMENT."' AND doc.filetype = 'file' AND doc.path LIKE '".$curdirpath."%' AND doc.path NOT LIKE '".$curdirpath."%/%' AND prop.visibility = 1  AND prop.insert_user_id = ".$_user['user_id'] .")";
          $sql3 = "(SELECT distinct(title) AS title,id,display_order FROM $tbl_documents doc,$propTable prop ". " WHERE session_id= 0 " ." AND doc.id = prop.ref AND prop.tool = '".TOOL_DOCUMENT."' AND doc.filetype = 'file' AND doc.path LIKE '".$curdirpath."%' AND doc.path NOT LIKE '".$curdirpath."%/%' AND prop.visibility = 1  ".")";
          $sql = $sql1." UNION ".$sql2. " UNION " . $sql3 . " ORDER BY display_order "; */
        
        $sql = "SELECT DISTINCT title,path,id,display_order FROM $tbl_documents doc, $propTable prop " . api_get_session_condition(api_get_session_id(), false, true) . " AND doc.id = prop.ref AND prop.tool = '" . TOOL_DOCUMENT . "' AND doc.filetype = 'file' AND doc.path LIKE '" . $curdirpath . "%' AND doc.path NOT LIKE '" . $curdirpath . "%/%' AND prop.visibility = 1  AND prop.insert_user_id = " . $_user['user_id'] . " ORDER BY id";

        $result = api_sql_query($sql, __FILE__, __LINE__);
        while ($row = Database :: fetch_array($result)) {
            $arrLP[] = array(
                'id' => $row['id'],
                'title' => end(explode('/', $row['path'])),
            );
        }
        $return .= '<table style="width: 100%;"><tr><td colspan="2"><div id="GalleryContainer">';
        for ($i = 0; $i < count($arrLP); $i++) {
            $id = $arrLP[$i]['id'];
            $title = $arrLP[$i]['title'];
            $tmptitle = strtolower(pathinfo($title, PATHINFO_EXTENSION));
            list($w, $h) = getimagesize($src_path . $title);
            if ($w > ($h + 30))
                $h = 200;
            else
                $h = 150;

            if ($tmptitle == 'png' || $tmptitle == 'gif' || $tmptitle == 'jpg' || $tmptitle == 'jpeg') {
                $file_id = Security::remove_XSS($id);
                $allowed_to_download = check_if_user_is_allowed_to_download_file($file_id, api_get_user_id());
                $return .= '<div class="imageBox2" id="imageBox' . $id . '">';
                if ($w < 4000) {
                    $return .= '<div class="imageBox_theImage wpercent" style="text-align:center;">
                    <a  href="' . api_get_self() . '?' . api_get_cidreq() . '&view_received_category=' . Security::remove_XSS($_GET['view_received_category']) . '&view_sent_category=' . Security::remove_XSS($_GET['view_sent_category']) . '&view=' . Security::remove_XSS($_GET['view']) . '&action=viewfeedback&id=' . $id . '">                    
                    <table height="100%" style="margin: auto;">
                    <tr><td><img id="mindmap' . $id . '" src="thumb.php?file=' . $src_path . $title . '&size='.$h.'" /></td></tr>
                    </table>
                    </a>
                    </div><div style="text-align: right;">';
                } else {
                    $return .= '<div class="imageBox_theImage wpercent" style="text-align:center;">
                    <a  href="' . api_get_self() . '?' . api_get_cidreq() . '&view_received_category=' . Security::remove_XSS($_GET['view_received_category']) . '&view_sent_category=' . Security::remove_XSS($_GET['view_sent_category']) . '&view=' . Security::remove_XSS($_GET['view']) . '&action=viewfeedback&id=' . $id . '">                    
                    <table height="100%" style="margin: auto;">
                    <tr><td><img id="mindmap' . $id . '" style="max-width:200px;max-height:150px;" src="' . api_get_path(WEB_PATH) . "courses/" . api_get_course_id() . "/document/mindmaps/" . $title . '" /></td></tr>
                    </table>
                    </a>
                    </div><div style="text-align: right;">';
                }

                $return .= '<a href="' . api_get_self() . '?' . api_get_cidreq() . '&view_received_category=' . Security::remove_XSS($_GET['view_received_category']) . '&view_sent_category=' . Security::remove_XSS($_GET['view_sent_category']) . '&view=' . Security::remove_XSS($_GET['view']) . '&action=viewfeedback&id=' . $id . '">
                      ' . Display::return_icon('pixel.gif', get_lang('Comments'), array("class" => "mindmapplaceholdericon toolactioncomments", "style" => "width:25px;height:25px;")) . '</a>';
                if ($allowed_to_download === true && $GLOBALS['learner_view'] != '1') {
                    $return .= '<a href="dropbox_download.php?' . api_get_cidreq() . '&id=' . $id . '&action=download">
                        ' . Display::return_icon('pixel.gif', get_lang('Download'), array("class" => "actionplaceholdericon forcedownload", "style" => "margin-left:10px;")) . '</a>';
                    $link = api_get_self() . '?' . api_get_cidreq() . '&view_received_category=' . Security::remove_XSS($_GET['view_received_category']) . '&view_sent_category=' . Security::remove_XSS($_GET['view_sent_category']) . '&view=' . Security::remove_XSS($_GET['view']) . '&action=deletereceivedfile&id=' . $id;
                    $title = get_lang("ConfirmationDialog");
                    $text = get_lang("ConfirmYourChoice");
                    $return .= '<a href="javascript:void(0);" onclick="Alert_Confim_Delete(\'' . $link . '\',\'' . $title . '\',\'' . $text . '\');">
                            
                         ' . Display::return_icon('pixel.gif', get_lang('Delete'), array("class" => "actionplaceholdericon actiondelete", "style" => "margin-left:10px")) . '</a>';
                }
                $return .= '</div>
                   </div>';

                echo '<script>
                        $(document).ready(function() {
                        var img' . $id . ' = document.getElementById("mindmap' . $id . '"); 
                        var width = img' . $id . '.clientWidth;
                        var height = img' . $id . '.clientHeight;

                        if(height > 190){
                            $("#mindmap' . $id . '").css("height","190px");                                    
            }
                        if(width > 214){
                            $("#mindmap' . $id . '").css("width","214px");
		}
                        });

                        </script>';
            }
        }
        $return .= '</div>
		<div id="insertionMarker">
		<img src="../img/marker_top.gif" alt="&nbsp;" />
		<img src="../img/marker_middle.gif" alt="&nbsp;" id="insertionMarkerLine" />
		<img src="../img/marker_bottom.gif" alt="&nbsp;" />
		</div>
		<div id="dragDropContent">
		</div><div id="debug" style="clear:both">
		</div>';

        $return .= '</td></tr></table>';
        echo $return;
    }

    public function show_list_received($curdirpath, $src_path) {
        global $_user;

        $tbl_documents = Database::get_course_table(TABLE_DOCUMENT);
        $propTable = Database::get_course_table(TABLE_ITEM_PROPERTY);
        //echo '<h3>'.get_lang('ReceivedFiles').'</h3>';
        // This is for the categories
        $return = '';

        $sql = "SELECT * FROM $tbl_documents doc,$propTable prop " . api_get_session_condition(api_get_session_id(), false, true) . " AND doc.id = prop.ref AND prop.tool = '" . TOOL_DOCUMENT . "' AND doc.filetype = 'file' AND doc.path LIKE '" . $curdirpath . "%' AND doc.path NOT LIKE '" . $curdirpath . "%/%' AND prop.visibility = 1 AND prop.to_user_id = " . $_user['user_id'] . " AND prop.insert_user_id <> " . $_user['user_id'];
        //echo $sql; exit();    
        $result = api_sql_query($sql, __FILE__, __LINE__);
        $arrLP = array();
        while ($row = Database :: fetch_array($result)) {
            $arrLP[] = array(
                'id' => $row['id'],
                'title' => end(explode('/', $row['path'])),
            );
        }
        $message = '';
        $size = count($arrLP);
        if ($size == 0) {
            $message = get_lang('ThereIsNoFileAvailableNow');
            echo '<div id="emptyPage">' . $message . '</div>';
        }
        $return .= '<table style="width: 100%;"><tr><td colspan="2"><div id="GalleryContainer">';

        for ($i = 0; $i < $size; $i++) {
            $id = $arrLP[$i]['id'];
            $title = $arrLP[$i]['title'];
            $tmptitle = strtolower(pathinfo($title, PATHINFO_EXTENSION));
            list($w, $h) = getimagesize($src_path . $title);
            if ($w > ($h + 30))
                $h = 200;
            else
                $h = 150;

            if ($tmptitle == 'png' || $tmptitle == 'gif' || $tmptitle == 'jpg' || $tmptitle == 'jpeg') {
                $return .= '<div class="imageBox2" id="imageBox' . $id . '">';
                if ($w < 4000) {
                    $return .= '<div class="imageBox_theImage wpercent" style="text-align:center;">
                    <a  href="' . api_get_self() . '?' . api_get_cidreq() . '&view_received_category=' . Security::remove_XSS($_GET['view_received_category']) . '&view_sent_category=' . Security::remove_XSS($_GET['view_sent_category']) . '&view=' . Security::remove_XSS($_GET['view']) . '&action=viewfeedback&id=' . $id . '">                    
                    <table height="100%" style="margin: auto;">
                    <tr><td><img id="mindmap' . $id . '" src="thumb.php?file=' . $src_path . $title . '&size='.$h.'" /></td></tr>
                    </table>
                    </a>
                    </div><div style="text-align: right;">';
                } else {
                    $return .= '<div class="imageBox_theImage wpercent" style="text-align:center;">
                    <a  href="' . api_get_self() . '?' . api_get_cidreq() . '&view_received_category=' . Security::remove_XSS($_GET['view_received_category']) . '&view_sent_category=' . Security::remove_XSS($_GET['view_sent_category']) . '&view=' . Security::remove_XSS($_GET['view']) . '&action=viewfeedback&id=' . $id . '">                    
                    <table height="100%" style="margin: auto;">
                    <tr><td><img id="mindmap' . $id . '" style="max-width:210px;max-height:157px;" src="' . api_get_path(WEB_PATH) . "courses/" . api_get_course_id() . "/document/mindmaps/" . $title . '" /></td></tr>
                    </table>
                    </a>
                    </div><div style="text-align: right;">';
                }

                $return .= '<a href="' . api_get_self() . '?' . api_get_cidreq() . '&view_received_category=' . Security::remove_XSS($_GET['view_received_category']) . '&view_sent_category=' . Security::remove_XSS($_GET['view_sent_category']) . '&view=' . Security::remove_XSS($_GET['view']) . '&action=viewfeedback&id=' . $id . '">'
                        . Display::return_icon('pixel.gif', '', array('class' => 'mindmapplaceholdericon toolactioncomments', 'style' => 'width:25px;height:25px;')) . '
                        </a><a href="dropbox_download.php?' . api_get_cidreq() . '&id=' . $id . '&action=download">' . Display::return_icon('pixel.gif', '', array('class' => 'actionplaceholdericon forcedownload', 'style' => 'padding-left:10px;')) .
                        '<a href="javascript:void(0);" '
                        . 'onclick="Alert_Confim_Delete(\'' . api_get_self() . '?' . api_get_cidreq() . '&view_received_category=' . Security::remove_XSS($_GET['view_received_category']) . '&view_sent_category=' . Security::remove_XSS($_GET['view_sent_category']) . '&view=' . Security::remove_XSS($_GET['view']) . '&action=deletereceivedfile&id=' . $id . '\',\'' . get_lang("ConfirmationDialog") . '\',\'' . get_lang("ConfirmYourChoice") . '\');">' . Display::return_icon('pixel.gif', '', array('class' => 'actionplaceholdericon actiondelete', 'style' => 'padding-left:10px;')) . '</a>'
                        . '</div></div>';

                echo '<script>
                                $(document).ready(function() {
                                var img' . $id . ' = document.getElementById("mindmap' . $id . '"); 
                                var width = img' . $id . '.clientWidth;
                                var height = img' . $id . '.clientHeight;
                                
                                if(height > 190){
                                    $("#mindmap' . $id . '").css("height","190px");                                    
			}
                                if(width > 214){
                                    $("#mindmap' . $id . '").css("width","214px");
		}
                                });

                                </script>';
            }
        }
        $return .= '</div>
		<div id="insertionMarker">
		<img src="' . api_get_path(WEB_IMG_PATH) . 'marker_top.gif" alt="&nbsp;" />
		<img src="../img/marker_middle.gif" alt="&nbsp;" id="insertionMarkerLine" />
		<img src="../img/marker_bottom.gif" alt="&nbsp;" />
		</div>
		<div id="dragDropContent">
		</div><div id="debug" style="clear:both">
		</div>';

        $return .= '</td></tr></table>';

        echo $return;
    }

    public function viewfeedback($id, $view, $http_www, $dropbox_cnf_tbl_feedback) {

        global $dropbox_cnf, $_course;
        $tbl_documents = Database::get_course_table(TABLE_DOCUMENT);
        $propTable = Database::get_course_table(TABLE_ITEM_PROPERTY);
        // getting the information of the document
        $query = "SELECT path FROM $tbl_documents WHERE id=" . Database::escape_string(Security::Remove_XSS($id));
        $result = api_sql_query($query, __FILE__, __LINE__);
        while ($row = Database :: fetch_array($result)) {
            $image_path = $row['path'];
        }
        $img_path = $http_www . '/document' . $image_path;
        list($width, $height) = getimagesize($img_path);
        $WEB_COURSE_PATH = api_get_path(WEB_COURSE_PATH) . $_course['path'];
        $image = $WEB_COURSE_PATH . '/document' . $image_path;

        if ($width > 900) {
            $target_width = 850;
            $target_height = $height;
            $new_sizes = api_resize_image($img_path, $target_width, $target_height);
            $new_width = $new_sizes['width'];
            $new_height = $new_sizes['height'];
        } else {
            $new_width = $width;
            $new_height = $height;
        }
        echo '<div class="rounded center border"><img src="' . $image . '" width="' . $new_width . '" height="' . $new_height . '"></div>';

        // displaying the feedback messages
        $sql = "SELECT author_user_id, DATE_FORMAT(feedback_date,'%Y-%m-%d') AS feedback_date, feedback  FROM " . $dropbox_cnf_tbl_feedback . " WHERE file_id=" . Database::escape_string(Security::Remove_XSS($id));
        $result = api_sql_query($sql, __FILE__, __LINE__);
        $num_rows = Database::num_rows($result);
        if ($num_rows <> 0) {
            echo '<div class="rounded border margintop padding">';
            while ($row = Database :: fetch_array($result)) {
                $output = '';
                $output .= display_user_link($row['author_user_id']);
                $output .= '&nbsp;&nbsp;' . date('d-m-Y',strtotime($row['feedback_date'])) . '<br />';
                $output .= '<div class="paddingtop">' . nl2br($row['feedback']) . '</div><hr size="1" color="#ccc" noshade/>';
                echo '<tr><td colspan="2">' . $output . '</td></tr>';
                $i++;
            }
            echo '</div>';
        }
        echo '<form name="form_tablename" method="post" action="' . api_get_self() . '?' . api_get_cidReq() . '&action=viewfeedback&id=' . $id . '&view=' . $view . '">' . get_lang('AddNewComment') . '<br /><textarea name="feedback" rows="3" cols="100" autofocus></textarea><button type="submit" class="save" name="store_feedback" value="' . get_lang('ok') . ' onclick="document.form_tablename.attributes.action.value = document.location;"">' . get_lang('SaveComment') . '</button></form>';
    }

    public function show_icons_mindmap($action, $view) {
        $mybutons = array('Mindmap' => array('title' => 'Mindmap', 'href' => 'href="index.php?' . api_get_cidreq() . '"', 'class' => 'toolactionplaceholdericon toolactionback'),
            'MapsOut' => array('title' => 'MapsOut', 'href' => 'href="index.php?' . api_get_cidreq() . '&view=sent"', 'class' => 'mindmapplaceholdericon toolactionup'),
            'MapsIn' => array('title' => 'MapsIn', 'href' => 'href="index.php?' . api_get_cidreq() . '&view=received"', 'class' => 'mindmapplaceholdericon toolactiondown32'),
            'UploadMap' => array('title' => 'UploadMap', 'href' => 'href="' . api_get_self() . '?' . api_get_cidreq() . '&view=sent&action=add&curdirpath=' . $_GET['curdirpath'] . '"', 'class' => 'mindmapplaceholdericon toolactionmindmap'),
            'DownloadDokeosMind' => array('title' => 'DownloadDokeosMind', 'href' => 'href="http://www.dokeos.com/mind/"', 'class' => 'mindmapplaceholdericon toolactiondownloadmind')
        );


        if ($view == 'received') {
            $Maps = 'MapsIn';
        } else {
            $Maps = 'MapsOut';
        }

        foreach ($mybutons as $id => $value) {
            $opacity_value = '';
            if (($action <> 'add' AND $id == 'Mindmap') OR ( $action == 'viewfeedback' AND $id == 'Mindmap' ) OR ( $action == 'add' AND $id == 'UploadMap' ) OR ( $id == $Maps )) {
                $opacity_value = 'style="opacity: 0.2;display:none"';
                $value['href'] = '';
            }
            $target = ( $id == 'DownloadDokeosMind' ) ? ' target="_blank" ' : '';
            if ($id <> 'Mindmap') {
                echo '<li ' . $opacity_value . '>';
                echo '<a ' . $value['href'] . ' ' . $target . '>';
                echo Display::return_icon('pixel.gif', get_lang($value['title']), array("class" => $value['class'])) . ' ' . get_lang($value['title']);
                echo '</a>';
                echo '</li>';
            }
        }

//       if (api_get_session_id()==0) {  
//           if($_GET['action'] == 'add' || $_GET['action'] == 'viewfeedback') {
//            echo "<li><a href=\"index.php?".api_get_cidreq()."\">".Display::return_icon('pixel.gif', get_lang('Mindmap'), array('class' => 'toolactionplaceholdericon toolactionback')).get_lang('Mindmap').'</a></li>' . PHP_EOL;
//        }
//        self::display_mindmap_tabs();  
//        if (empty($_GET['view_sent_category'])) {
//            if($_SESSION['_user']['user_id']!=2){
//                $view1 =  'sent';                
//                echo '<li><a href="'.api_get_self().'?'.api_get_cidreq().'&view='.$view1.'&action=add&curdirpath='.$_GET['curdirpath'].'">'.Display::return_icon('pixel.gif',get_lang('UploadMap'),array("class" => "mindmapplaceholdericon toolactionmindmap")).' '.get_lang('UploadMap')."</a></li>";
//            }
//        }
//                 
//        } 
    }

    public function display_mindmap_tabs() {
        global $dropbox_cnf;

        if ($dropbox_cnf['sent_received_tabs']) {
            ?>


            <li><a href="index.php?<?php echo api_get_cidreq(); ?>&view=sent" <?php
                if (!$_GET['view'] OR $_GET['view'] == 'sent') {
                    echo 'class="active"';
                }
                ?>><?php echo Display::return_icon(('pixel.gif'), get_lang('MapsOut'), array("class" => "mindmapplaceholdericon toolactionup")) . get_lang('MapsOut'); ?></a></li>
            <li><a href="index.php?<?php echo api_get_cidreq(); ?>&view=received" <?php
                if ($_GET['view'] == 'received') {
                    echo 'class="active"';
                }
                ?> ><?php echo Display::return_icon(('pixel.gif'), get_lang('MapsIn'), array("class" => "mindmapplaceholdericon toolactiondown32")) . get_lang('MapsIn'); ?></a></li>


            <?php
        }
    }

}
?>
