<?php
/* For licensing terms, see /dokeos_license.txt */

/**
 * ==============================================================================
 * 	The Dokeos wiki is a further development of the CoolWiki plugin.
 *
 * 	@Author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 * 	@Author Juan Carlos Raña <herodoto@telefonica.net>
 * 	@Copyright Ghent University
 * 	@Copyright Patrick Cool
 *
 * 	@package dokeos.wiki
 * ==============================================================================
 */

/**
 * @author Patrick Cool <patrick.cool@ugent.be>, Ghent University
 * @desc This function checks weither the proposed reflink is not in use yet. It is a recursive function because every newly created reflink suggestion
 * 		has to be checked also
 */
function createreflink($testvalue) {
    global $groupfilter;
    $counter = '';
    while (!checktitle($testvalue . $counter)) {
        $counter++;
        echo $counter . "-" . $testvalue . $counter . "<br />";
    }
    // the reflink has not been found yet, so it is OK
    return $testvalue . $counter;
}

/**
 * @author Patrick Cool <patrick.cool@ugent.be>, Ghent University
 * */
function checktitle($paramwk) {
    global $tbl_wiki;
    global $groupfilter;

    $paramwk = html_entity_decode($paramwk);
    $rs = Database::query('SELECT * FROM ' . $tbl_wiki . ' WHERE reflink="'.Database::escape_string($paramwk).'" AND ' . $groupfilter . ''); // TODO: check if need entity
    $numberofresults = Database::num_rows($rs);

    if ($numberofresults == 0) { // the value has not been found and is this available
        return true;
    } else { // the value has been found
        return false;
    }
}

/**
 * @author Juan Carlos Raña <herodoto@telefonica.net>
 * check wikilinks that has a page
 * */
function links_to($input) {
    $input_array = preg_split("/(\[\[|\]\])/", $input, -1, PREG_SPLIT_DELIM_CAPTURE);
    $all_links = array();

    foreach ($input_array as $key => $value) {

        if ($input_array[$key - 1] == '[[' AND $input_array[$key + 1] == ']]') {

            if (strpos($value, "|") != false) {
                $full_link_array = explode("|", $value);
                $link = trim($full_link_array[0]);
                $title = trim($full_link_array[1]);
            } else {
                $link = $value;
                $title = $value;
            }

            unset($input_array[$key - 1]);
            unset($input_array[$key + 1]);

            $all_links[] = Database::escape_string(str_replace(' ', '_', $link)) . ' '; //replace blank spaces by _ within the links. But to remove links at the end add a blank space
        }
    }

    $output = implode($all_links);
    return $output;
}

/*
  detect and add style to external links
  author Juan Carlos Raña Trabado
 * */

function detect_external_link($input) {
    $exlink = 'href=';
    $exlinkStyle = 'class="wiki_link_ext" href=';
    $output = str_replace($exlink, $exlinkStyle, $input);
    return $output;
}

/*
  detect and add style to anchor links
  author Juan Carlos Raña Trabado
 * */

function detect_anchor_link($input) {
    $anchorlink = 'href="#';
    $anchorlinkStyle = 'class="wiki_anchor_link" href="#';
    $output = str_replace($anchorlink, $anchorlinkStyle, $input);
    return $output;
}

/*
  detect and add style to mail links
  author Juan Carlos Raña Trabado
 * */

function detect_mail_link($input) {
    $maillink = 'href="mailto';
    $maillinkStyle = 'class="wiki_mail_link" href="mailto';
    $output = str_replace($maillink, $maillinkStyle, $input);
    return $output;
}

/*
  detect and add style to ftp links
  author Juan Carlos Raña Trabado
 * */

function detect_ftp_link($input) {
    $ftplink = 'href="ftp';
    $ftplinkStyle = 'class="wiki_ftp_link" href="ftp';
    $output = str_replace($ftplink, $ftplinkStyle, $input);
    return $output;
}

/*
  detect and add style to news links
  author Juan Carlos Raña Trabado
 * */

function detect_news_link($input) {
    $newslink = 'href="news';
    $newslinkStyle = 'class="wiki_news_link" href="news';
    $output = str_replace($newslink, $newslinkStyle, $input);
    return $output;
}

/*
  detect and add style to irc links
  author Juan Carlos Raña Trabado
 * */

function detect_irc_link($input) {
    $irclink = 'href="irc';
    $irclinkStyle = 'class="wiki_irc_link" href="irc';
    $output = str_replace($irclink, $irclinkStyle, $input);
    return $output;
}

/*
 * This function allows users to have [link to a title]-style links like in most regular wikis.
 * It is true that the adding of links is probably the most anoying part of Wiki for the people
 * who know something about the wiki syntax.
 * @author Patrick Cool <patrick.cool@ugent.be>, Ghent University
 * Improvements [[]] and [[ | ]]by Juan Carlos Raña
 * Improvements internal wiki style and mark group by Juan Carlos Raña
 * */

function make_wiki_link_clickable($input) {
    if (isset($_SESSION['_gid'])) {
        $_clean['group_id'] = (int) $_SESSION['_gid'];
    }
    if (isset($_GET['group_id'])) {
        $_clean['group_id'] = (int) Security::remove_XSS($_GET['group_id']);
    }

    $input_array = preg_split("/(\[\[|\]\])/", $input, -1, PREG_SPLIT_DELIM_CAPTURE); //now doubles brackets

    foreach ($input_array as $key => $value) {

        if ($input_array[$key - 1] == '[[' AND $input_array[$key + 1] == ']]') { //now doubles brackets

            /////////// TODO: metawiki
            /*
              if ($_clean['group_id']==0)
              {
              $titleg_ex='';
              }
              else
              {
              $group_properties  = GroupManager :: get_group_properties($_clean['group_id']);
              $group_name= $group_properties['name'];
              $titleg_ex='<sup><img src="css/wgroup.gif" alt="('.$group_name.')" title="Link to Wikigroup:'.$group_name.'"/></sup>';
              }
             */
            /////////
            //now full wikilink
            if (strpos($value, "|") != false) {
                $full_link_array = explode("|", $value);
                $link = trim($full_link_array[0]);
                $title = trim($full_link_array[1]);
            } else {
                $link = $value;
                $title = $value;
            }

            //if wikilink is homepage
            if ($link == 'index') {
                $title = get_lang('DefaultTitle');
            }
            if ($link == get_lang('DefaultTitle')) {
                $link = 'index';
            }


            // note: checkreflink checks if the link is still free. If it is not used then it returns true, if it is used, then it returns false. Now the title may be different
            if (checktitle(strtolower(str_replace(' ', '_', $link)))) {                
                $input_array[$key] = '<a href="' . api_get_path(WEB_PATH) . 'main/wiki/index.php?'.  api_get_cidreq() . '&action=addnew&title=' . strip_tags(urldecode($link)) . '&group_id=' . $_clean['group_id'] . '" class="new_wiki_link">' . $title . $titleg_ex . '</a>';               
                
            } else {

                $input_array[$key] = '<a href="' . api_get_path(WEB_PATH) . 'main/wiki/index.php?cidReq=' . $_course[id] . '&action=showpage&get_wiki_link=true&title=' . strtolower(str_replace(' ', '_', $link)) . '&group_id=' . $_clean['group_id'] . '" class="wiki_link">' . $title . $titleg_ex . '</a>';
            }
            unset($input_array[$key - 1]);
            unset($input_array[$key + 1]);
        }
    }
    $output = implode('', $input_array);
    
    return $output;
}

/**
 * This function saves a change in a wiki page
 * @author Patrick Cool <patrick.cool@ugent.be>, Ghent University
 * @return language string saying that the changes are stored
 * */
function save_wiki(&$page_id, &$reflink) {    
    global $charset;
    global $tbl_wiki;
    global $tbl_wiki_conf;

    // NOTE: visibility, visibility_disc and ratinglock_disc changes are not made here, but through the interce buttons
    // cleaning the variables
    $_clean['page_id'] = Database::escape_string($_REQUEST['page_id']);
    $_clean['reflink'] = Database::escape_string(Security::remove_XSS($_POST['reflink']));
    $_clean['title'] = Database::escape_string(Security::remove_XSS($_POST['title']));
    $_clean['content'] = Database::escape_string($_POST['wiki_content']);
    $_clean['user_id'] = (int) Database::escape_string(api_get_user_id());
    $_clean['assignment'] = Database::escape_string($_POST['assignment']);
    $_clean['comment'] = Database::escape_string(Security::remove_XSS($_POST['comment']));
    $_clean['progress'] = Database::escape_string($_POST['progress']);
    $_clean['version'] = Database::escape_string($_POST['version']) + 1;
    $_clean['linksto'] = links_to($_clean['content']); //and check links content


    $dtime = date("Y-m-d H:i:s");

    $session_id = api_get_session_id();

    if (isset($_SESSION['_gid'])) {
        $_clean['group_id'] = Database::escape_string($_SESSION['_gid']);
    }
    if (isset($_GET['group_id'])) {
        $_clean['group_id'] = Database::escape_string($_GET['group_id']);
    }

    //cleaning config variables

    if (!empty($_POST['task'])) {
        $_clean['task'] = Database::escape_string(Security::remove_XSS($_POST['task']));
    }
    if (!empty($_POST['feedback1']) || !empty($_POST['feedback2']) || !empty($_POST['feedback3'])) {
        $_clean['feedback1'] = Database::escape_string(Security::remove_XSS($_POST['feedback1']));
        $_clean['feedback2'] = Database::escape_string(Security::remove_XSS($_POST['feedback2']));
        $_clean['feedback3'] = Database::escape_string(Security::remove_XSS($_POST['feedback3']));
        $_clean['fprogress1'] = Database::escape_string(Security::remove_XSS($_POST['fprogress1']));
        $_clean['fprogress2'] = Database::escape_string(Security::remove_XSS($_POST['fprogress2']));
        $_clean['fprogress3'] = Database::escape_string(Security::remove_XSS($_POST['fprogress3']));
    }

    if (Security::remove_XSS($_POST['initstartdate'] == 1)) {
        $_clean['startdate_assig'] = Database::escape_string(Security::remove_XSS(get_date_from_select('startdate_assig')));
    } else {
        $_clean['startdate_assig'] = Database::escape_string(Security::remove_XSS($_POST['startdate_assig']));
    }

    if (Security::remove_XSS($_POST['initenddate'] == 1)) {
        $_clean['enddate_assig'] = Database::escape_string(Security::remove_XSS(get_date_from_select('enddate_assig')));
    } else {
        $_clean['enddate_assig'] = Database::escape_string(Security::remove_XSS($_POST['enddate_assig']));
    }

    $_clean['delayedsubmit'] = Database::escape_string(Security::remove_XSS($_POST['delayedsubmit']));

    if (!empty($_POST['max_text']) || !empty($_POST['max_version'])) {
        $_clean['max_text'] = Database::escape_string(Security::remove_XSS($_POST['max_text']));
        $_clean['max_version'] = Database::escape_string(Security::remove_XSS($_POST['max_version']));
    }



    $sql = "INSERT INTO " . $tbl_wiki . " (page_id, reflink, title, content, user_id, group_id, dtime, assignment, comment, progress, version, linksto, user_ip, session_id) VALUES ('" . $_clean['page_id'] . "','" . $_clean['reflink'] . "','" . $_clean['title'] . "','" . $_clean['content'] . "','" . $_clean['user_id'] . "','" . $_clean['group_id'] . "','" . $dtime . "','" . $_clean['assignment'] . "','" . $_clean['comment'] . "','" . $_clean['progress'] . "','" . $_clean['version'] . "','" . $_clean['linksto'] . "','" . Database::escape_string($_SERVER['REMOTE_ADDR']) . "', '" . Database::escape_string($session_id) . "')";
 
    $result = Database::query($sql);
    $Id = Database::insert_id();

    if ($Id > 0) {
        //insert into item_property
        api_item_property_update(api_get_course_info(), TOOL_WIKI, $Id, 'WikiAdded', api_get_user_id(), $_clean['group_id']);
    }
    $page_id = $Id;
    $reflink = $_clean['reflink'];
    if ($_clean['page_id'] == 0) {
        $sql = 'UPDATE ' . $tbl_wiki . ' SET page_id="' . $Id . '" WHERE id="' . $Id . '"';
        Database::query($sql, __FILE__, __LINE__);        
    } else {
        $page_id = $_clean['page_id'];
    }

    //update wiki config

    if ($_clean['reflink'] == 'index' && $_clean['version'] == 1) {
        $sql = "INSERT INTO " . $tbl_wiki_conf . " (page_id, task, feedback1, feedback2, feedback3, fprogress1, fprogress2, fprogress3, max_text, max_version, startdate_assig, enddate_assig, delayedsubmit) VALUES ('" . $Id . "','" . $_clean['task'] . "','" . $_clean['feedback1'] . "','" . $_clean['feedback2'] . "','" . $_clean['feedback3'] . "','" . $_clean['fprogress1'] . "','" . $_clean['fprogress2'] . "','" . $_clean['fprogress3'] . "','" . $_clean['max_text'] . "','" . $_clean['max_version'] . "','" . $_clean['startdate_assig'] . "','" . $_clean['enddate_assig'] . "','" . $_clean['delayedsubmit'] . "')";
    } else {
        $sql = 'UPDATE' . $tbl_wiki_conf . ' SET task="' . $_clean['task'] . '", feedback1="' . $_clean['feedback1'] . '", feedback2="' . $_clean['feedback2'] . '", feedback3="' . $_clean['feedback3'] . '",  fprogress1="' . $_clean['fprogress1'] . '",  fprogress2="' . $_clean['fprogress2'] . '",  fprogress3="' . $_clean['fprogress3'] . '", max_text="' . $_clean['max_text'] . '", max_version="' . $_clean['max_version'] . '", startdate_assig="' . $_clean['startdate_assig'] . '", enddate_assig="' . $_clean['enddate_assig'] . '", delayedsubmit="' . $_clean['delayedsubmit'] . '" WHERE page_id="' . $_clean['page_id'] . '"';
    }

    Database::query($sql, __FILE__, __LINE__);

    api_item_property_update($_course, 'wiki', $Id, 'WikiAdded', api_get_user_id(), $_clean['group_id']);

    check_emailcue($_clean['reflink'], 'P', $dtime, $_clean['user_id']);

    if (api_get_setting('search_enabled') == 'true' && extension_loaded('xapian')) {
       //update search engine keyword
        //update wiki


        $wiki = new WikiManager($Id);
        $idLastWiki = (int)$wiki->get_last_id_wiki();

        //delete keyword
        $searchkey = new SearchEngineManager();
        $searchkey->idobj = $idLastWiki;
        $searchkey->course_code = api_get_course_id();
        $searchkey->tool_id = TOOL_WIKI;
        $searchkey->deleteKeyWord();

        //save keyword
        $searchkey->idobj = $Id;
        $searchkey->course_code = api_get_course_id();
        $searchkey->tool_id = TOOL_WIKI;
        $searchkey->value = strip_tags($_clean['content']);
        $searchkey->save();


        $wiki = new WikiManager($Id);
        $wiki->search_engine_edit();

    }

    return get_lang('ChangesStored');
}

/**
 * This function restore a wikipage
 * @author Juan Carlos Raña <herodoto@telefonica.net>
 * */
function restore_wikipage($r_page_id, $r_reflink, $r_title, $r_content, $r_group_id, $r_assignment, $r_progress, $c_version, $r_version, $r_linksto) {

    global $tbl_wiki;

    $r_user_id = api_get_user_id();
    $r_dtime = date("Y-m-d H:i:s");
    $r_version = $r_version + 1;
    $r_comment = get_lang('RestoredFromVersion') . ': ' . $c_version;
    $session_id = api_get_session_id();

    $sql = "INSERT INTO " . $tbl_wiki . " (page_id, reflink, title, content, user_id, group_id, dtime, assignment, comment, progress, version, linksto, user_ip, session_id) VALUES ('" . $r_page_id . "','" . $r_reflink . "','" . Database::escape_string($r_title) . "','" . Database::escape_string($r_content) . "','" . $r_user_id . "','" . $r_group_id . "','" . $r_dtime . "','" . $r_assignment . "','" . $r_comment . "','" . $r_progress . "','" . $r_version . "','" . $r_linksto . "','" . Database::escape_string($_SERVER['REMOTE_ADDR']) . "','" . Database::escape_string($session_id) . "')";

    $result = Database::query($sql);
    $Id = Database::insert_id();
    api_item_property_update($_course, 'wiki', $Id, 'WikiAdded', api_get_user_id(), $r_group_id);

    check_emailcue($r_reflink, 'P', $r_dtime, $r_user_id);

    return get_lang('PageRestored');
}

/**
 * This function delete a wiki
 * @author Juan Carlos Raña <herodoto@telefonica.net>
 * */
function delete_wiki() {

    global $tbl_wiki, $tbl_wiki_conf, $tbl_wiki_discuss, $tbl_wiki_mailcue, $groupfilter;
    //identify the first id by group = identify wiki
    $sql = 'SELECT * FROM ' . $tbl_wiki . '  WHERE  ' . $groupfilter . ' ORDER BY id DESC';
    $allpages = Database::query($sql, __FILE__, __LINE__);

    while ($row = Database::fetch_array($allpages)) {
        $id = $row['id'];
        $group_id = $row['group_id'];
        $page_id = $row['page_id'];
        Database::query('DELETE FROM ' . $tbl_wiki_conf . ' WHERE page_id="' . $id . '"', __FILE__, __LINE__);
        Database::query('DELETE FROM ' . $tbl_wiki_discuss . ' WHERE publication_id="' . $id . '"', __FILE__, __LINE__);
    }

    Database::query('DELETE FROM ' . $tbl_wiki_mailcue . ' WHERE group_id="' . $group_id . '"', __FILE__, __LINE__);
    Database::query('DELETE FROM ' . $tbl_wiki . ' WHERE ' . $groupfilter . '', __FILE__, __LINE__);
    return get_lang('WikiDeleted');
}

function search_engine_save($id, $title, $comments) {
    $search_db_path = api_get_path(SYS_PATH) . 'searchdb';
    if (is_writable($search_db_path)) {
        $course_id = api_get_course_id();
        require_once(api_get_path(LIBRARY_PATH) . 'search/DokeosIndexer.class.php');
        require_once(api_get_path(LIBRARY_PATH) . 'search/IndexableChunk.class.php');
        require_once(api_get_path(LIBRARY_PATH) . 'specific_fields_manager.lib.php');

        $ic_slide = new IndexableChunk();
        $ic_slide->addValue('title', $title);
        $ic_slide->addCourseId($course_id);
        $ic_slide->addToolId(TOOL_WIKI);
        $xapian_data = array(
            SE_COURSE_ID => $course_id,
            SE_TOOL_ID => TOOL_WIKI,
            SE_DATA => array('type' => SE_DOCTYPE_WIKI_WIKI, 'wiki_id' => (int) $id),
            SE_USER => (int) api_get_user_id(),
        );
        $ic_slide->xapian_data = serialize($xapian_data);
        $wiky_description = !empty($comments) ? $comments : $title;
        if (isset($_POST['search_terms'])) {
            $add_extra_terms = Security::remove_XSS($_POST['search_terms']) . ' ';
        }
        $file_content = $add_extra_terms . $wiky_description;
        $ic_slide->addValue("content", $file_content);

        $di = new DokeosIndexer();
        isset($_POST['language']) ? $lang = Database::escape_string($_POST['language']) : $lang = 'english';
        $di->connectDb(NULL, NULL, $lang);
        $di->addChunk($ic_slide);
        //index and return search engine document id
        $did = $di->index();
        if ($did) {
            // save it to db
            $tbl_se_ref = Database::get_main_table(TABLE_MAIN_SEARCH_ENGINE_REF);
            $sql = 'INSERT INTO %s (id, course_code, tool_id, ref_id_high_level, search_did)
                                VALUES (NULL , \'%s\', \'%s\', %s, %s)';
            $sql = sprintf($sql, $tbl_se_ref, $course_id, TOOL_WIKI, $id, $did);
            api_sql_query($sql, __FILE__, __LINE__);
        } else {
            return false;
        }
    }
}

/**
 * This function saves a new wiki page.
 * @author Patrick Cool <patrick.cool@ugent.be>, Ghent University
 * @todo consider merging this with the function save_wiki into one single function.
 * */
function save_new_wiki($page_id, $reflink) {
    
    global $charset;
    global $tbl_wiki;
    global $assig_user_id; //need for assignments mode
    global $tbl_wiki_conf;

    // cleaning the variables
    $_clean['assignment'] = Database::escape_string($_POST['assignment']);

    // session_id
    $session_id = api_get_session_id();

    if ($_clean['assignment'] == 2 || $_clean['assignment'] == 1) {// Unlike ordinary pages of pages of assignments. Allow create a ordinary page although there is a assignment with the same name
        $_clean['reflink'] = Database::escape_string(Security::remove_XSS(str_replace(' ', '_', $_POST['title'] . "_uass" . $assig_user_id)));
    } else {
        $_clean['reflink'] = Database::escape_string(Security::remove_XSS(str_replace(' ', '_', $_POST['title'])));
    }

    $_clean['title'] = Database::escape_string(Security::remove_XSS($_POST['title']));
    
    $_clean['content'] = Database::escape_string(Security::remove_XSS(($_POST['wiki_content']), COURSEMANAGERLOWSECURITY));
   
    if ($_clean['assignment'] == 2) {//config by default for individual assignment (students)
        $_clean['user_id'] = (int) Database::escape_string($assig_user_id); //Identifies the user as a creator, not the teacher who created

        $_clean['visibility'] = 0;
        $_clean['visibility_disc'] = 0;
        $_clean['ratinglock_disc'] = 0;
    } else {
        $_clean['user_id'] = (int) Database::escape_string(api_get_user_id());

        $_clean['visibility'] = 1;
        $_clean['visibility_disc'] = 1;
        $_clean['ratinglock_disc'] = 1;
    }

    $_clean['comment'] = Database::escape_string(Security::remove_XSS($_POST['comment']));
    $_clean['progress'] = Database::escape_string($_POST['progress']);
    $_clean['version'] = 1;

    if (isset($_SESSION['_gid'])) {
        $_clean['group_id'] = (int) $_SESSION['_gid'];
    }
    if (isset($_GET['group_id'])) {
        $_clean['group_id'] = (int) Database::escape_string($_GET['group_id']);
    }

    $_clean['linksto'] = links_to($_clean['content']); //check wikilinks
    //cleaning config variables
    $_clean['task'] = Database::escape_string(Security::remove_XSS($_POST['task']));
    $_clean['feedback1'] = Database::escape_string(Security::remove_XSS($_POST['feedback1']));
    $_clean['feedback2'] = Database::escape_string(Security::remove_XSS($_POST['feedback2']));
    $_clean['feedback3'] = Database::escape_string(Security::remove_XSS($_POST['feedback3']));
    $_clean['fprogress1'] = Database::escape_string(Security::remove_XSS($_POST['fprogress1']));
    $_clean['fprogress2'] = Database::escape_string(Security::remove_XSS($_POST['fprogress2']));
    $_clean['fprogress3'] = Database::escape_string(Security::remove_XSS($_POST['fprogress3']));

    if (Security::remove_XSS($_POST['initstartdate'] == 1)) {
        $_clean['startdate_assig'] = Database::escape_string(Security::remove_XSS(get_date_from_select('startdate_assig')));
    } else {
        $_clean['startdate_assig'] = Database::escape_string(Security::remove_XSS($_POST['startdate_assig']));
    }

    if (Security::remove_XSS($_POST['initenddate'] == 1)) {
        $_clean['enddate_assig'] = Database::escape_string(Security::remove_XSS(get_date_from_select('enddate_assig')));
    } else {
        $_clean['enddate_assig'] = Database::escape_string(Security::remove_XSS($_POST['enddate_assig']));
    }

    $_clean['delayedsubmit'] = Database::escape_string(Security::remove_XSS($_POST['delayedsubmit']));
    $_clean['max_text'] = Database::escape_string(Security::remove_XSS($_POST['max_text']));
    $_clean['max_version'] = Database::escape_string(Security::remove_XSS($_POST['max_version']));
    
    //filter no _uass
    if (api_eregi('_uass', $_POST['title']) || (api_strtoupper(trim($_POST['title'])) == 'INDEX' || api_strtoupper(trim(api_htmlentities($_POST['title'], ENT_QUOTES, $charset))) == api_strtoupper(api_htmlentities(get_lang('DefaultTitle'), ENT_QUOTES, $charset)))) {
        $message = get_lang('GoAndEditMainPage');
        return $message;
    } else {
        $var = $_clean['reflink'];
        $group_id = Security::remove_XSS($_GET['group_id']);
        if (!checktitle($var)) {
            return get_lang('WikiPageTitleExist') . '<a href="index.php?'.api_get_cidreq().'&action=edit&page_id='.get_page_id_by_title($var).'&title='.$var.'&group_id=' . $group_id . '">' . $_POST['title'] . '</a>';
        } else {
            $dtime = date("Y-m-d H:i:s");

            $sql = "INSERT INTO " . $tbl_wiki . " (reflink, title, content, user_id, group_id, dtime, visibility, visibility_disc, ratinglock_disc, assignment, comment, progress, version, linksto, user_ip, session_id) VALUES ('" . $_clean['reflink'] . "','" . $_clean['title'] . "','" . $_clean['content'] . "','" . $_clean['user_id'] . "','" . $_clean['group_id'] . "','" . $dtime . "','" . $_clean['visibility'] . "','" . $_clean['visibility_disc'] . "','" . $_clean['ratinglock_disc'] . "','" . $_clean['assignment'] . "','" . $_clean['comment'] . "','" . $_clean['progress'] . "','" . $_clean['version'] . "','" . $_clean['linksto'] . "','" . Database::escape_string($_SERVER['REMOTE_ADDR']) . "', '" . Database::escape_string($session_id) . "')";
            
            $result = Database::query($sql, __LINE__, __FILE__);
            $Id = Database::insert_id();
            $wiki_id = $Id;

            if ($Id > 0) {
                //insert into item_property
                api_item_property_update(api_get_course_info(), TOOL_WIKI, $Id, 'WikiAdded', api_get_user_id(), $_clean['group_id']);
            }

            $sql = 'UPDATE ' . $tbl_wiki . ' SET page_id="' . $Id . '" WHERE id="' . $Id . '"';
            Database::query($sql, __FILE__, __LINE__);

            // Set value to variable by reference
            $page_id = $Id;
            $reflink = $_clean['reflink'];
            //insert wiki config
            $sql = "INSERT INTO " . $tbl_wiki_conf . " (page_id, task, feedback1, feedback2, feedback3, fprogress1, fprogress2, fprogress3, max_text, max_version, startdate_assig, enddate_assig, delayedsubmit) VALUES ('" . $Id . "','" . $_clean['task'] . "','" . $_clean['feedback1'] . "','" . $_clean['feedback2'] . "','" . $_clean['feedback3'] . "','" . $_clean['fprogress1'] . "','" . $_clean['fprogress2'] . "','" . $_clean['fprogress3'] . "','" . $_clean['max_text'] . "','" . $_clean['max_version'] . "','" . $_clean['startdate_assig'] . "','" . $_clean['enddate_assig'] . "','" . $_clean['delayedsubmit'] . "')";
            Database::query($sql, __LINE__, __FILE__);
            $_SESSION["display_confirmation_message"] = get_lang('WikiAdded');
            check_emailcue(0, 'A');

            $_POST['reflink'] = $_clean['reflink'];

            if (api_get_setting('search_enabled') == 'true' && extension_loaded('xapian')) {
                //save search engine keyword
                //save new wiki
                $searchkey = new SearchEngineManager();
                $searchkey->idobj = $wiki_id;
                $searchkey->course_code = api_get_course_id();
                $searchkey->tool_id = TOOL_WIKI;
                $searchkey->value = strip_tags($_clean['content']);
                $searchkey->save();
                
                $wiki = new WikiManager($wiki_id);
                $wiki->search_engine_save();

            }
           
            return $page_id;
        }
    }//end filter no _uass
}

/**
 * This function displays the form for adding a new wiki page.
 * @author Patrick Cool <patrick.cool@ugent.be>, Ghent University
 * @return html code
 * */
function display_new_wiki_form() {
    ?>
    <script type="text/javascript">
        function CheckSend()
        {
            if(document.form1.title.value == "")
            {
                //alert("<?php echo get_lang('NoWikiPageTitle'); ?>");
                $("#image_title").css("visibility","visible");
                $("#id_focus").css("border-color","red");
                document.form1.title.focus();
                return false;
            }
            return true;
        }
    </script>
    <?php
    //form
    echo '<form name="form1" method="post" onsubmit="return CheckSend()" action="' . api_get_self() . '?' . api_get_cidreq() . '&action=showpage&title=' . $page . '&page_id=' . $page_id . '&group_id=' . Security::remove_XSS($_GET['group_id']) . '">';
    $glossary_plugin = '';
    $currentEditor = strtolower(api_get_setting('use_default_editor'));
    echo '<div id="wikititle">';
    echo '<div style="float: left; margin-bottom: 9px;"><span class="form_required">*</span> ' . get_lang(Title) . ': <input type="text" name="title" value="' . urldecode($_GET['title']) . '" size="40" class="focus" id="id_focus"> </div><img src="'.  api_get_path(WEB_IMG_PATH).'exclamation.png" title="'.get_lang('Required').'" id="image_title" style="visibility: hidden;"/>';
    //echo '<button class="save" type="submit" name="SaveWikiNew">' . get_lang('langSave') . '</button>';
//    echo '<br/><br/>';
   
    if ($currentEditor == 'fckeditor') {
	    if (api_get_setting('show_glossary_in_documents') == 'ismanual') {
//	        $glossary_plugin = '<td width="5px;" class="toolbar_style"><img onclick="glossary();" src="' . api_get_path(WEB_LIBRARY_PATH) . 'fckeditor/editor/plugins/glossary/glossary.gif"></td>';
	    }
            
            
	    echo '<table cellspacing="3" width="100%" height="50px" class="toolbar_style_back"><tr><td width="80%"><table width="100%"><tr style="height:5px;"><td colspan="3"></td></tr>
                                    <tr>
                                    <td width="5px"></td>
                                    <td width="5px" class="toolbar_style">
                                    <img src="../img/pasteword_icon.png" onclick="word();" alt="'.get_lang('PasteWord').'" title="'.get_lang('PasteWord').'"></td>
                                    <td width="5px"></td>
                                    <td width="5px;" class="toolbar_style"><img src="../img/link_icon.png" onclick="link();" alt="'.get_lang('Link').'" title="'.get_lang('Link').'"></td>
                                    <td width="5px"></td><td width="5px;" class="toolbar_style">'.Display::return_icon('pixel.gif',get_lang('Images'),array('class'=>'fckactionplaceholdericon fckactionimages_icon','onclick'=>'image();')) .'</td>
                                    <td width="5px;" class="toolbar_style">'.Display::return_icon('pixel.gif',get_lang('Imagemap'),array('class'=>'fckactionplaceholdericon fckactionimagemap','onclick'=>'imagemap();')).'</td>
                                    <td width="5px;" class="toolbar_style">'.Display::return_icon('pixel.gif',get_lang('Mindmap'),array('class'=>'fckactionplaceholdericon fckactionmindmap_18','onclick'=>'mindmap();')).'</td>
                                    <td width="5px;" class="toolbar_style">'.Display::return_icon('pixel.gif',get_lang('Mascot'),array('class'=>'fckactionplaceholdericon fckactionmascot_icon','onclick'=>'mascot();')).'</td>
                                    <td width="5px;" class="toolbar_style">'.Display::return_icon('pixel.gif',get_lang('Videoplayer'),array('class'=>'fckactionplaceholdericon fckactionvideoPlayer','onclick'=>'videoplayer();')).'</td>
                                    <td width="5px;" class="toolbar_style">'.Display::return_icon('pixel.gif',get_lang('Audio'),array('class'=>'fckactionplaceholdericon fckactionaudio','onclick'=>'audio();')).'</td>
                                    '.$glossary_plugin.'
                                    <td width="5px"></td>
                                    <td width="5px;" class="toolbar_style">'.Display::return_icon('pixel.gif',get_lang('Table'),array('class'=>'fckactionplaceholdericon fckactiontable','onclick'=>'table();')).'</td>
                                    <td width="5px;" class="toolbar_style"><img src="../img/unordered_list.png" onclick="unordered();" alt="'.get_lang('Orderedlist').'" title="'.get_lang('Orderedlist').'"></td>
                                    <td width="5px;" class="toolbar_style"><img src="../img/view_source.png" onclick="source();" alt="'.get_lang('Source').'" title="'.get_lang('Source').'"></td>
                                    <td width="5px"></td>
                                    <td width="5px;" class="toolbar_style"><img src="../img/text_bold.png" onclick="makeitbold();" alt="'.get_lang('Bold').'" title="'.get_lang('Bold').'"></td>
                                    <td width="5px"></td><td width="5px;" class="toolbar_style"><img src="../img/text_left.png" onclick="alignleft();" alt="'.get_lang('Alignleft').'" title="'.get_lang('Alignleft').'"></td>
                                    <td width="5px;" class="toolbar_style"><img src="../img/text_center.png" onclick="aligncenter();" alt="'.get_lang('Aligncenter').'" title="'.get_lang('Aligncenter').'"></td>
                                    <td width="5px;" class="toolbar_style">'.Display::return_icon('pixel.gif',get_lang('Textcolor'), array('class' => 'fckactionplaceholdericon fckactionfontcolor', 'onclick' => 'fontcolor(event);')).'</td>
                                    <td width="5px"></td>
                                    </tr>
                                    <tr height="5px">
                                    <td></td></tr>
                                    </table></td>
                                    <td>
                                    <table width="100%">
                                    <tr><td><span style="color:#333333; padding-right:10px;">Font:</span><select name="font" onchange="fontsize()"><option></option><option value="smaller" style="font-size: smaller;">smaller</option><option value="larger" style="font-size: larger;">larger</option><option value="xx-small" style="font-size: xx-small;">xx-small</option><option value="x-small" style="font-size: x-small;">x-small</option><option value="small" style="font-size: small;">small</option><option value="medium" style="font-size: medium;">medium</option><option value="large" style="font-size: large;">large</option><option value="x-large" style="font-size: x-large;">x-large</option><option value="xx-large" style="font-size: x-large;">xx-large</option></select></td></tr></table></td></tr><tr height="5px"><td></td></tr></table><br>';
    } else {
                                    if (api_get_setting('show_glossary_in_documents') == 'ismanual') {
//                                        $glossary_plugin = '<td width="5px;" class="toolbar_style"><img onclick="glossary();" src="'.api_get_path(WEB_LIBRARY_PATH).'fckeditor/editor/plugins/glossary/glossary.gif" alt="'.get_lang('Glossary').'" title="'.get_lang('Glossary').'"></td>';
                                    }


                                    $fontSize_sizes = '8/8px;9/9px;10/10px;11/11px;12/12px;14/14px;16/16px;18/18px;20/20px;22/22px;24/24px;26/26px;28/28px;36/36px;48/48px;72/72px';
                                    $a_sizes = explode(';', $fontSize_sizes);
                                    $options = '';
                                    foreach ($a_sizes as $size) {
                                        list($num, $px) = explode('/', $size);
                                        $options .= '<option value="'.$px.'">'.$num.'</option>';
                                    }

                                    echo '<table cellspacing="3" width="100%" height="50px" class="toolbar_style_back"><tr><td width="80%"><table width="100%"><tr style="height:5px;"><td colspan="3"></td></tr>
                                    <tr>
                                    <td width="5px"></td>
                                    <td width="5px" class="toolbar_style">
                                    <img src="../img/pasteword_icon.png" onclick="word();" alt="'.get_lang('PasteWord').'" title="'.get_lang('PasteWord').'"></td>
                                    <td width="5px"></td>
                                    <td width="5px;" class="toolbar_style"><img src="../img/link_icon.png" onclick="link();" alt="'.get_lang('Link').'" title="'.get_lang('Link').'"></td>
                                    <td width="5px"></td><td width="5px;" class="toolbar_style">'.Display::return_icon('pixel.gif',get_lang('Images'),array('class'=>'fckactionplaceholdericon fckactionimages_icon','onclick'=>'image();')) .'</td>
                                    <td width="5px;" class="toolbar_style">'.Display::return_icon('pixel.gif',get_lang('Imagemap'),array('class'=>'fckactionplaceholdericon fckactionimagemap','onclick'=>'imagemap();')).'</td>
                                    <td width="5px;" class="toolbar_style">'.Display::return_icon('pixel.gif',get_lang('Mindmap'),array('class'=>'fckactionplaceholdericon fckactionmindmap_18','onclick'=>'mindmap();')).'</td>
                                    <td width="5px;" class="toolbar_style">'.Display::return_icon('pixel.gif',get_lang('Mascot'),array('class'=>'fckactionplaceholdericon fckactionmascot_icon','onclick'=>'mascot();')).'</td>
                                    <td width="5px;" class="toolbar_style">'.Display::return_icon('pixel.gif',get_lang('Videoplayer'),array('class'=>'fckactionplaceholdericon fckactionvideoPlayer','onclick'=>'videoplayer();')).'</td>
                                    <td width="5px;" class="toolbar_style">'.Display::return_icon('pixel.gif',get_lang('Audio'),array('class'=>'fckactionplaceholdericon fckactionaudio','onclick'=>'audio();')).'</td>
                                    '.$glossary_plugin.'
                                    <td width="5px"></td>
                                    <td width="5px;" class="toolbar_style">'.Display::return_icon('pixel.gif',get_lang('Table'),array('class'=>'fckactionplaceholdericon fckactiontable','onclick'=>'table();')).'</td>
                                    <td width="5px;" class="toolbar_style"><img src="../img/unordered_list.png" onclick="unordered();" alt="'.get_lang('Orderedlist').'" title="'.get_lang('Orderedlist').'"></td>
                                    <td width="5px;" class="toolbar_style"><img src="../img/view_source.png" onclick="source();" alt="'.get_lang('Source').'" title="'.get_lang('Source').'"></td>
                                    <td width="5px"></td>
                                    <td width="5px;" class="toolbar_style"><img src="../img/text_bold.png" onclick="makeitbold();" alt="'.get_lang('Bold').'" title="'.get_lang('Bold').'"></td>
                                    <td width="5px"></td><td width="5px;" class="toolbar_style"><img src="../img/text_left.png" onclick="alignleft();" alt="'.get_lang('Alignleft').'" title="'.get_lang('Alignleft').'"></td>
                                    <td width="5px;" class="toolbar_style"><img src="../img/text_center.png" onclick="aligncenter();" alt="'.get_lang('Aligncenter').'" title="'.get_lang('Aligncenter').'"></td>
                                    <td width="5px;" class="toolbar_style">'.Display::return_icon('pixel.gif',get_lang('Textcolor'), array('class' => 'fckactionplaceholdericon fckactionfontcolor', 'onclick' => 'fontcolor(event);')).'</td>
                                    <td width="5px"></td>
                                    </tr>
                                    <tr height="5px">
                                    <td></td></tr>
                                    </table></td>
                                    <td>
                                    <table width="100%">
                                    <tr><td><span style="color:#333333; padding-right:10px;">'.get_lang('FontSize').'</span><select name="font" onchange="fontsize(this.value)"><option></option>'.$options.'</select></td></tr></table></td></tr></table></td></tr></table>';

    }
    // echo '<button class="save" type="submit" name="SaveWikiNew">'.get_lang('langSave').'</button>';
    if (api_is_allowed_to_edit(false, true) || api_is_platform_admin()) {

        $_clean['group_id'] = (int) $_SESSION['_gid']; // TODO: check if delete ?
        //echo'<a href="javascript:void(0)" id="wiki_options_id" ><span id="plus_minus" style="float:right">&nbsp;'.Display::return_icon('div_show.gif',get_lang('Show'),array('style'=>'vertical-align:middle')).'&nbsp;'.get_lang('Works').'</span></a>';
        echo '<div id="options" style="display:none;">';
        //task
        echo '<div id="wiki_assignment_option1">';
        echo '<input type="checkbox" value="1" name="checktask" onclick="if(this.checked==true){document.getElementById(\'option4\').style.display=\'block\';}else{document.getElementById(\'option4\').style.display=\'none\';}"/>&nbsp;<img src="../img/wiki/task.gif" title="' . get_lang('DefineTask') . '" alt="' . get_lang('DefineTask') . '"/>' . get_lang('DescriptionOfTheTask') . '';
        echo '	<span id="msg_error4" style="display:none;color:red"></span>';
        echo '	<div id="option4" style="display:none;">';
        echo '	<textarea name="task" rows="5" cols="45"></textarea>';
        echo '</div>';
        echo '</div>';

        //feedback
        echo '<div id="wiki_assignment_option2">';
        echo '	<input type="checkbox" value="1" name="checkfeedback" onclick="if(this.checked==true){document.getElementById(\'option2\').style.display=\'block\';}else{document.getElementById(\'option2\').style.display=\'none\';}"/>&nbsp;' . get_lang('AddFeedback') . '';
        echo '	<span id="msg_error2" style="display:none;color:red"></span>';
        echo '	<div id="option2" style="display:none;">';
        echo '<table border="0" style="font-weight:normal" align="center">';
        echo '<tr>';
        echo '<td colspan="2">' . get_lang('Feedback1') . '</td>';
        echo '<td colspan="2">' . get_lang('Feedback2') . '</td>';
        echo '<td colspan="2">' . get_lang('Feedback3') . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td colspan="2"><textarea name="feedback1" cols="17" rows="4"></textarea></td>';
        echo '<td colspan="2"><textarea name="feedback2" cols="17" rows="4"></textarea></td>';
        echo '<td colspan="2"><textarea name="feedback3" cols="17" rows="4"></textarea></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . get_lang('FProgress') . ':</td>';
        echo '				<td>
								<select name="fprogress1">
		   <option value="0" selected>0</option>
		   <option value="10">10</option>
		   <option value="20">20</option>
		   <option value="30">30</option>
		   <option value="40">40</option>
		   <option value="50">50</option>
		   <option value="60">60</option>
		   <option value="70">70</option>
		   <option value="80">80</option>
		   <option value="90">90</option>
		   <option value="100">100</option>
		   						</select> %
							</td>';
        echo '<td>' . get_lang('FProgress') . ':</td>';
        echo '<td><select name="fprogress2">
		   <option value="0" selected>0</option>
		   <option value="10">10</option>
		   <option value="20">20</option>
		   <option value="30">30</option>
		   <option value="40">40</option>
		   <option value="50">50</option>
		   <option value="60">60</option>
		   <option value="70">70</option>
		   <option value="80">80</option>
		   <option value="90">90</option>
		   <option value="100">100</option>
							   </select> %
							</td>';
        echo '<td>' . get_lang('FProgress') . ':</td>';
        echo '<td><select name="fprogress3">
		   <option value="0" selected>0</option>
		   <option value="10">10</option>
		   <option value="20">20</option>
		   <option value="30">30</option>
		   <option value="40">40</option>
		   <option value="50">50</option>
		   <option value="60">60</option>
		   <option value="70">70</option>
		   <option value="80">80</option>
		   <option value="90">90</option>
		   <option value="100">100</option>
						   	</select> %
							</td>';
        echo '</tr>';
        echo '</table>';
        echo '</div>';
        echo '</div>';

        //time limit
        echo '<div id="wiki_assignment_option3">';
        echo '	<input type="checkbox" value="1" name="checktimelimit" onclick="if(this.checked==true){document.getElementById(\'option1\').style.display=\'block\';}else{document.getElementById(\'option1\').style.display=\'none\';}"/>&nbsp;' . get_lang('PutATimeLimit') . '';
        echo '	<span id="msg_error1" style="display:none;color:red"></span>';
        echo '	<div id="option1" style="display:none;">';
        echo '		<table width="100%" border="0">';
        echo '<tr>';
        echo '<td width="20%" align="right">' . get_lang("StartDate") . ':</td>';
        echo '<td>';
        echo draw_date_picker('startdate_assig') . ' <input type="checkbox" name="initstartdate" value="1"> ' . get_lang('Yes') . '/' . get_lang('No') . '';
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td align="right">' . get_lang("EndDate") . ':</td>';
        echo '<td>';
        echo draw_date_picker('enddate_assig') . ' <input type="checkbox" name="initenddate" value="1"> ' . get_lang('Yes') . '/' . get_lang('No') . '';
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td align="right">' . get_lang('AllowLaterSends') . ':</td>';
        echo '<td><input type="checkbox" name="delayedsubmit" value="1"></td>';
        echo '</tr>';
        echo'</table>';
        echo '</div>';
        echo '</div>';

        //other limit
        echo '<div id="wiki_assignment_option4">';
        echo '	<input type="checkbox" value="1" name="checkotherlimit" onclick="if(this.checked==true){document.getElementById(\'option3\').style.display=\'block\';}else{document.getElementById(\'option3\').style.display=\'none\';}"/>&nbsp;' . get_lang('OtherSettings') . '';
        echo '	<span id="msg_error3" style="display:none;color:red"></span>';
        echo '	<div id="option3" style="display:none;">';
        echo '<div style="font-weight:normal"; align="center">' . get_lang('NMaxWords') . ':&nbsp;<input type="text" name="max_text" size="3">&nbsp;&nbsp;' . get_lang('NMaxVersion') . ':&nbsp;<input type="text" name="max_version" size="3"></div>';
        echo '</div>';
        echo '';

        //to define as an individual assignment
        echo '<div><input type="checkbox" name="assignment" value="1"><img src="../img/wiki/assignment.gif" />&nbsp;' . get_lang('DefineAssignmentPage') . '</div>'; // 1= teacher 2 =student
        //
		echo'</div></div>';
    }
    api_disp_html_area(
        'wiki_content',
        ' ',
        '',
        '',
        null,
        api_is_allowed_to_edit(null, true) ? 
            array(
                'ToolbarSet' => 'TestProposedAnswer',
//                'Width' => '938px',
                'Height' => '300'
            ) :
            array(
                'ToolbarSet' => 'TestProposedAnswer',
//                'Width' => '938px',
                'Height' => '300',
                'UserStatus' => 'student'
            )
    );

    //echo get_lang('Comments') . ':&nbsp;&nbsp;<input type="text" name="comment" size="40"><br /><br />';
//    echo get_lang('Progress') . ':&nbsp;&nbsp;<select name="progress" id="progress">
//	   <option value="0" selected>0</option>
//	   <option value="10">10</option>
//	   <option value="20">20</option>
//	   <option value="30">30</option>
//	   <option value="40">40</option>
//	   <option value="50">50</option>
//	   <option value="60">60</option>
//	   <option value="70">70</option>
//	   <option value="80">80</option>
//	   <option value="90">90</option>
//	   <option value="100">100</option>
//	   </select> %';
    if (api_get_setting('search_enabled') == 'true') {
        //TODO: include language file
        echo '<input type="hidden" name="index_document" value="1"/>';
        echo '<input type="hidden" name="language" value="' . api_get_setting('platformLanguage') . '"/>';

        //echo get_lang('SearchKeywords') . ':&nbsp;&nbsp;<textarea cols="65" name="search_terms"></textarea>';
    }
    echo '';
    echo '<input type="hidden" name="wpost_id" value="' . md5(uniqid(rand(), true)) . '">'; //prevent double post
    echo '</div><br/>';
    echo '<div class="pull-bottom"><button class="save" type="submit" name="SaveWikiNew">' . get_lang('langSave') . '</button></div>'; //for button icon. Don't change name (see fckeditor/editor/plugins/customizations/fckplugin_compressed.js and fckplugin.js

    echo '</form>';
    
}

/**
 * This function displays a wiki entry
 * @author Patrick Cool <patrick.cool@ugent.be>, Ghent University
 * @return html code
 * */
function display_wiki_entry($newtitle, &$my_progress, &$count_my_words, &$wiki_score) {
    global $charset;
    global $tbl_wiki;
    global $tbl_wiki_conf;
    global $groupfilter;
    global $page;
    global $page_id;
    if ($newtitle) {
        $pageMIX = $newtitle; //display the page after it is created
    } else {
        $pageMIX = $page; //display current page
    }

    if ($page_id == 0) {
        $page_id = get_wiki_home_page_id();
    }
    if (isset($_GET['get_wiki_link']) && $_GET['get_wiki_link'] == 'true') {
        $page_id = get_wiki_home_page_id_by_page_title($newtitle);
    }

    $_clean['group_id'] = (int) $_SESSION['_gid'];
    if ($_GET['view']) {
        $_clean['view'] = (int) Database::escape_string($_GET['view']);

        $filter = ' AND ' . $tbl_wiki . '.id="' . $_clean['view'] . '"';
    }

    //first, check page visibility in the first page version
    $sql = 'SELECT * FROM ' . $tbl_wiki . 'WHERE page_id="' . $page_id . '" AND ' . $groupfilter . ' ORDER BY id ASC';
    $result = Database::query($sql, __LINE__, __FILE__);
    $row = Database::fetch_array($result);
    $KeyVisibility = $row['visibility'];
    $add_where_lcid = ""; 
    if (isset($_GET['lcid'])) {
        $add_where_lcid = ' AND '.$tbl_wiki.'.id="'. intval($_GET['lcid']).'"';
    }
    // second, show the last version
    $sql = 'SELECT * FROM ' . $tbl_wiki . ', ' . $tbl_wiki_conf . ' WHERE ' . $tbl_wiki_conf . '.page_id=' . $tbl_wiki . '.page_id AND ' . $tbl_wiki . '.page_id="' . $page_id . '" '.$add_where_lcid.' AND ' . $tbl_wiki . '.' . $groupfilter . ' ' . $filter . ' ORDER BY id DESC';

    $result = Database::query($sql, __LINE__, __FILE__);
    $row = Database::fetch_array($result); // we do not need a while loop since we are always displaying the last version
    //update visits
    if ($row['id']) {
        $sql = 'UPDATE ' . $tbl_wiki . ' SET hits=(hits+1) WHERE id=' . $row['id'] . '';
        Database::query($sql, __FILE__, __LINE__);
    }


    // if both are empty and we are displaying the index page then we display the default text.
    if ($row['content'] == '' AND $row['title'] == '' AND $page == 'index') {
        if (api_is_allowed_to_edit(false, true) || api_is_platform_admin() || GroupManager :: is_user_in_group(api_get_user_id(), $_SESSION['_gid'])) {
            //Table structure for better export to pdf
            $default_table_for_content_Start = '<table align="center" width="98%" border="0"><tr><td align="left" width="55%" valign="top">';
            $default_table_for_content_End = '</td><td align="right">' . Display::return_icon("avatars/builder.png",get_lang('Build')) . '</td></tr></table>';


            $content = $default_table_for_content_Start . '<h3 style="font-size: 14px;">Lorem ipsum quia dolor sit amet</h3><p style="font-size: 14px;">Consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur</p><p style="font-size: 14px;">Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.</p>' . $default_table_for_content_End;
            $title = get_lang('DefaultTitle');
        } else {
            return Display::display_normal_message(get_lang('WikiStandBy'), false, true);
        }
    } else {
      
        $content = Security::remove_XSS($row['content'], COURSEMANAGERLOWSECURITY);
        
        $title = Security::remove_XSS($row['title']);
    }


    //assignment mode: identify page type
    if ($row['assignment'] == 1) {
        $icon_assignment = '<img src="../img/wiki/assignment.gif" title="' . get_lang('AssignmentDescExtra') . '" alt="' . get_lang('AssignmentDescExtra') . '" />';
    } elseif ($row['assignment'] == 2) {
        $icon_assignment = '<img src="../img/wiki/works.gif" title="' . get_lang('AssignmentWorkExtra') . '" alt="' . get_lang('AssignmentWorkExtra') . '" />';
    }

    //task mode

    if (!empty($row['task'])) {
        $icon_task = '<img src="../img/wiki/task.gif" title="' . get_lang('StandardTask') . '" alt="' . get_lang('StandardTask') . '" />';
    }

    //Show page. Show page to all users if isn't hide page. Mode assignments: if student is the author, can view
    if ($KeyVisibility == "1" || api_is_allowed_to_edit(false, true) || api_is_platform_admin() || ($row['assignment'] == 2 && $KeyVisibility == "0" && (api_get_user_id() == $row['user_id']))) {

        echo '<div id="wiki_display_content">' . make_wiki_link_clickable(detect_external_link(detect_anchor_link(detect_mail_link(detect_ftp_link(detect_irc_link(detect_news_link($content))))))) . '</div>';

        $my_progress = $row['progress'];
        $count_my_words = word_count($content);
        $wiki_score = $row['score'];
    }//end filter visibility
}

// end function display_wiki_entry


/**
 * This function counted the words in a document. Thanks Adeel Khan
 */
function word_count($document) {

    $search = array(
        '@<script[^>]*?>.*?</script>@si',
        '@<style[^>]*?>.*?</style>@siU',
        '@<![\s\S]*?--[ \t\n\r]*>@'
    );

    $document = preg_replace($search, '', $document);

    # strip all html tags
    $wc = strip_tags($document);

    # remove 'words' that don't consist of alphanumerical characters or punctuation
    $pattern = "#[^(\w|\d|\'|\"|\.|\!|\?|;|,|\\|\/|\-|:|\&|@)]+#";
    $wc = trim(preg_replace($pattern, " ", $wc));

    # remove one-letter 'words' that consist only of punctuation
    $wc = trim(preg_replace("#\s*[(\'|\"|\.|\!|\?|;|,|\\|\/|\-|:|\&|@)]\s*#", " ", $wc));

    # remove superfluous whitespace
    $wc = preg_replace("/\s\s+/", " ", $wc);

    # split string into an array of words
    $wc = explode(" ", $wc);

    # remove empty elements
    $wc = array_filter($wc);

    # return the number of words
    return count($wc);
}

/**
 * This function checks if wiki title exist
 */
function wiki_exist($title) {
    global $tbl_wiki;
    global $groupfilter;
    $sql = 'SELECT id FROM ' . $tbl_wiki . 'WHERE title="' . Database::escape_string($title) . '" AND ' . $groupfilter . ' ORDER BY id ASC';
    $result = Database::query($sql, __LINE__, __FILE__);
    $cant = Database::num_rows($result);
    if ($cant > 0)
        return true;
    else
        return false;
}

function get_page_id_by_title($title) {
    global $tbl_wiki;
    global $groupfilter;
    $sql = 'SELECT id FROM ' . $tbl_wiki . 'WHERE title="' . Database::escape_string($title) . '" AND ' . $groupfilter . ' ORDER BY id ASC';
    $result = Database::query($sql, __LINE__, __FILE__);
    $page_id = 0;
    if (Database::num_rows($result) > 0) {
        $row = Database::fetch_object($result);
        $page_id = $row->id;
    }
    return $page_id;
}

function get_wiki_home_page_id() {
    global $tbl_wiki;
    global $groupfilter;

    $sql = 'SELECT page_id FROM ' . $tbl_wiki . 'WHERE reflink="index" AND ' . $groupfilter . ' LIMIT 1';
    $result = Database::query($sql, __LINE__, __FILE__);
    $row = Database::fetch_array($result);
    $cant = Database::num_rows($result);
    if ($cant > 0)
        return $row['page_id'];
    else
        return 0;
}

function get_wiki_home_page_id_by_page_title($title) {
    global $tbl_wiki;
    $sql = 'SELECT page_id FROM ' . $tbl_wiki . 'WHERE reflink="' . Database::escape_string($title) . '" LIMIT 1';
    $result = Database::query($sql, __LINE__, __FILE__);
    $row = Database::fetch_array($result);
    $cant = Database::num_rows($result);
    if ($cant > 0)
        return $row['page_id'];
    else
        return 0;
}

/**
 * This function a wiki warning
 * @author Patrick Cool <patrick.cool@ugent.be>, Ghent University
 * @return html code
 * */
function display_wiki_warning($variable) {
    echo '<div class="wiki_warning">' . $variable . '</div>';
}

/**
 * Checks if this navigation tab has to be set to active
 * @author Patrick Cool <patrick.cool@ugent.be>, Ghent University
 * @return html code
 */
function is_active_navigation_tab($paramwk) {
    if ($_GET['action'] == $paramwk) {
        return ' class="active"';
    }
}

/**
 * Lock add pages
 * @author Juan Carlos Raña <herodoto@telefonica.net>
 * return current database status of protect page and change it if get action
 */
function check_addnewpagelock() {

    global $tbl_wiki;
    global $groupfilter;

    $_clean['group_id'] = (int) $_SESSION['_gid'];

    $sql = 'SELECT * FROM ' . $tbl_wiki . 'WHERE ' . $groupfilter . ' ORDER BY id ASC';
    $result = Database::query($sql, __LINE__, __FILE__);
    $row = Database::fetch_array($result);

    $status_addlock = $row['addlock'];


    //change status
    if (api_is_allowed_to_edit(false, true) || api_is_platform_admin()) {

        if ($_GET['actionpage'] == 'lockaddnew' && $status_addlock == 1) {
            $status_addlock = 0;
        }
        if ($_GET['actionpage'] == 'unlockaddnew' && $status_addlock == 0) {
            $status_addlock = 1;
        }

        Database::query('UPDATE ' . $tbl_wiki . ' SET addlock="' . Database::escape_string($status_addlock) . '" WHERE ' . $groupfilter . '', __LINE__, __FILE__);

        $sql = 'SELECT * FROM ' . $tbl_wiki . 'WHERE ' . $groupfilter . ' ORDER BY id ASC';
        $result = Database::query($sql, __LINE__, __FILE__);
        $row = Database::fetch_array($result);
    }

    //show status

    return $row['addlock'];
}

/**
 * Protect page
 * @author Juan Carlos Raña <herodoto@telefonica.net>
 * return current database status of protect page and change it if get action
 */
function check_protect_page() {
    global $tbl_wiki;
    global $page;
    global $groupfilter;

    $_clean['group_id'] = (int) $_SESSION['_gid'];

    $sql = 'SELECT * FROM ' . $tbl_wiki . 'WHERE reflink="' . Database::escape_string($page) . '" AND ' . $groupfilter . ' ORDER BY id ASC';

    $result = Database::query($sql, __LINE__, __FILE__);
    $row = Database::fetch_array($result);

    $status_editlock = $row['editlock'];
    $id = $row['id'];

    ///change status
    if (api_is_allowed_to_edit(false, true) || api_is_platform_admin()) {
        if ($_GET['actionpage'] == 'lock' && $status_editlock == 0) {
            $status_editlock = 1;
        }
        if ($_GET['actionpage'] == 'unlock' && $status_editlock == 1) {
            $status_editlock = 0;
        }


        $sql = 'UPDATE ' . $tbl_wiki . ' SET editlock="' . Database::escape_string($status_editlock) . '" WHERE id="' . $id . '"';
        Database::query($sql, __FILE__, __LINE__);

        $sql = 'SELECT * FROM ' . $tbl_wiki . 'WHERE reflink="' . Database::escape_string($page) . '" AND ' . $groupfilter . ' ORDER BY id ASC';

        $result = Database::query($sql, __LINE__, __FILE__);
        $row = Database::fetch_array($result);
    }

    //show status

    return $row['editlock'];
}

/**
 * Visibility page
 * @author Juan Carlos Raña <herodoto@telefonica.net>
 * return current database status of visibility and change it if get action
 */
function check_visibility_page() {

    global $tbl_wiki;
    global $page;
    global $groupfilter;

    $_clean['group_id'] = (int) $_SESSION['_gid'];

    $sql = 'SELECT * FROM ' . $tbl_wiki . 'WHERE reflink="' . Database::escape_string($page) . '" AND ' . $groupfilter . ' ORDER BY id ASC';
    $result = Database::query($sql, __LINE__, __FILE__);
    $row = Database::fetch_array($result);

    $status_visibility = $row['visibility'];


    //change status

    if (api_is_allowed_to_edit(false, true) || api_is_platform_admin()) {
        if ($_GET['actionpage'] == 'visible' && $status_visibility == 0) {
            $status_visibility = 1;
        }
        if ($_GET['actionpage'] == 'invisible' && $status_visibility == 1) {
            $status_visibility = 0;
        }

        $sql = 'UPDATE ' . $tbl_wiki . ' SET visibility="' . Database::escape_string($status_visibility) . '" WHERE reflink="' . Database::escape_string($page) . '" AND ' . $groupfilter;
        Database::query($sql, __FILE__, __LINE__);

        //Although the value now is assigned to all (not only the first), these three lines remain necessary. They do that by changing the page state is made when you press the button and not have to wait to change his page
        $sql = 'SELECT * FROM ' . $tbl_wiki . 'WHERE reflink="' . Database::escape_string($page) . '" AND ' . $groupfilter . ' ORDER BY id ASC';
        $result = Database::query($sql, __LINE__, __FILE__);
        $row = Database::fetch_array($result);
    }

    if (empty($row['id'])) {
        $row['visibility'] = 1;
    }


    //show status
    return $row['visibility'];
}

/**
 * Visibility discussion
 * @author Juan Carlos Raña <herodoto@telefonica.net>
 * return current database status of discuss visibility and change it if get action page
 */
function check_visibility_discuss() {

    global $tbl_wiki;
    global $page;
    global $groupfilter;

    $_clean['group_id'] = (int) $_SESSION['_gid'];

    $sql = 'SELECT * FROM ' . $tbl_wiki . 'WHERE reflink="' . Database::escape_string($page) . '" AND ' . $groupfilter . ' ORDER BY id ASC';
    $result = Database::query($sql, __LINE__, __FILE__);
    $row = Database::fetch_array($result);

    $status_visibility_disc = $row['visibility_disc'];

    //change status
    if (api_is_allowed_to_edit(false, true) || api_is_platform_admin()) {
        if ($_GET['actionpage'] == 'showdisc' && $status_visibility_disc == 0) {
            $status_visibility_disc = 1;
        }
        if ($_GET['actionpage'] == 'hidedisc' && $status_visibility_disc == 1) {
            $status_visibility_disc = 0;
        }

        $sql = 'UPDATE ' . $tbl_wiki . ' SET visibility_disc="' . Database::escape_string($status_visibility_disc) . '" WHERE reflink="' . Database::escape_string($page) . '" AND ' . $groupfilter;
        Database::query($sql, __FILE__, __LINE__);

        //Although the value now is assigned to all (not only the first), these three lines remain necessary. They do that by changing the page state is made when you press the button and not have to wait to change his page
        $sql = 'SELECT * FROM ' . $tbl_wiki . 'WHERE reflink="' . Database::escape_string($page) . '" AND ' . $groupfilter . ' ORDER BY id ASC';
        $result = Database::query($sql, __LINE__, __FILE__);
        $row = Database::fetch_array($result);
    }

    //show status
    return $row['visibility_disc'];
}

/**
 * Lock add discussion
 * @author Juan Carlos Raña <herodoto@telefonica.net>
 * return current database status of lock dicuss and change if get action
 */
function check_addlock_discuss() {
    global $tbl_wiki;
    global $page;
    global $page_id;
    global $groupfilter;

    $_clean['group_id'] = (int) $_SESSION['_gid'];

    //$sql='SELECT * FROM '.$tbl_wiki.'WHERE reflink="'.Database::escape_string($page).'" AND '.$groupfilter.' ORDER BY id ASC';
    $sql = 'SELECT * FROM ' . $tbl_wiki . 'WHERE page_id="' . Database::escape_string($page_id) . '" AND ' . $groupfilter . ' ORDER BY id ASC';
    $result = Database::query($sql, __LINE__, __FILE__);
    $row = Database::fetch_array($result);

    $status_addlock_disc = $row['addlock_disc'];

    //change status
    if (api_is_allowed_to_edit() || api_is_platform_admin()) {

        if ($_GET['actionpage'] == 'lockdisc' && $status_addlock_disc == 0) {
            $status_addlock_disc = 1;
        }
        if ($_GET['actionpage'] == 'unlockdisc' && $status_addlock_disc == 1) {
            $status_addlock_disc = 0;
        }

        //$sql='UPDATE '.$tbl_wiki.' SET addlock_disc="'.Database::escape_string($status_addlock_disc).'" WHERE reflink="'.Database::escape_string($page).'" AND '.$groupfilter;
        $sql = 'UPDATE ' . $tbl_wiki . ' SET addlock_disc="' . Database::escape_string($status_addlock_disc) . '" WHERE page_id="' . Database::escape_string($page_id) . '" AND ' . $groupfilter;
        Database::query($sql, __FILE__, __LINE__);

        //Although the value now is assigned to all (not only the first), these three lines remain necessary. They do that by changing the page state is made when you press the button and not have to wait to change his page
        //$sql='SELECT * FROM '.$tbl_wiki.'WHERE reflink="'.Database::escape_string($page).'" AND '.$groupfilter.' ORDER BY id ASC';
        $sql = 'SELECT * FROM ' . $tbl_wiki . 'WHERE page_id="' . Database::escape_string($page_id) . '" AND ' . $groupfilter . ' ORDER BY id ASC';
        $result = Database::query($sql, __LINE__, __FILE__);
        $row = Database::fetch_array($result);
    }

    //show status
    return $row['addlock_disc'];
}

/**
 * Lock rating discussion
 * @author Juan Carlos Raña <herodoto@telefonica.net>
 * 	Return current database status of rating discuss and change it if get action
 */
function check_ratinglock_discuss() {

    global $tbl_wiki;
    global $page;
    global $page_id;
    global $groupfilter;

    $_clean['group_id'] = (int) $_SESSION['_gid'];

    //$sql='SELECT * FROM '.$tbl_wiki.'WHERE reflink="'.Database::escape_string($page).'" AND '.$groupfilter.' ORDER BY id ASC';
    $sql = 'SELECT * FROM ' . $tbl_wiki . 'WHERE page_id="' . Database::escape_string($page_id) . '" AND ' . $groupfilter . ' ORDER BY id ASC';
    $result = Database::query($sql, __LINE__, __FILE__);
    $row = Database::fetch_array($result);

    $status_ratinglock_disc = $row['ratinglock_disc'];


    //change status
    if (api_is_allowed_to_edit(false, true) || api_is_platform_admin()) {
        if ($_GET['actionpage'] == 'lockrating' && $status_ratinglock_disc == 0) {
            $status_ratinglock_disc = 1;
        }
        if ($_GET['actionpage'] == 'unlockrating' && $status_ratinglock_disc == 1) {
            $status_ratinglock_disc = 0;
        }

        //$sql='UPDATE '.$tbl_wiki.' SET ratinglock_disc="'.Database::escape_string($status_ratinglock_disc).'" WHERE reflink="'.Database::escape_string($page).'" AND '.$groupfilter; //Visibility. Value to all,not only for the first
        $sql = 'UPDATE ' . $tbl_wiki . ' SET ratinglock_disc="' . Database::escape_string($status_ratinglock_disc) . '" WHERE page_id="' . Database::escape_string($page_id) . '" AND ' . $groupfilter; //Visibility. Value to all,not only for the first
        Database::query($sql, __FILE__, __LINE__);

        //Although the value now is assigned to all (not only the first), these three lines remain necessary. They do that by changing the page state is made when you press the button and not have to wait to change his page
        //$sql='SELECT * FROM '.$tbl_wiki.'WHERE reflink="'.Database::escape_string($page).'" AND '.$groupfilter.' ORDER BY id ASC';
        $sql = 'SELECT * FROM ' . $tbl_wiki . 'WHERE page_id="' . Database::escape_string($page_id) . '" AND ' . $groupfilter . ' ORDER BY id ASC';
        $result = Database::query($sql, __LINE__, __FILE__);
        $row = Database::fetch_array($result);
    }

    //show status

    return $row['ratinglock_disc'];
}

/**
 * Notify page changes
 * @author Juan Carlos Raña <herodoto@telefonica.net>
 * return the current
 */
function check_notify_page($reflink) {
    global $tbl_wiki;
    global $groupfilter;
    global $tbl_wiki_mailcue;

    $_clean['group_id'] = (int) $_SESSION['_gid'];
    $sql = 'SELECT * FROM ' . $tbl_wiki . 'WHERE reflink="' . $reflink . '" AND ' . $groupfilter . ' ORDER BY id ASC';
    $result = Database::query($sql, __LINE__, __FILE__);
    $row = Database::fetch_array($result);

    $id = $row['id'];

    $sql = 'SELECT * FROM ' . $tbl_wiki_mailcue . 'WHERE id="' . $id . '" AND user_id="' . api_get_user_id() . '" AND type="P"';
    $result = Database::query($sql, __LINE__, __FILE__);
    $row = Database::fetch_array($result);

    $idm = $row['id'];

    if (empty($idm)) {
        $status_notify = 0;
    } else {
        $status_notify = 1;
    }

    //change status
    if ($_GET['actionpage'] == 'locknotify' && $status_notify == 0) {
        $sql = "INSERT INTO " . $tbl_wiki_mailcue . " (id, user_id, type, group_id) VALUES ('" . $id . "','" . api_get_user_id() . "','P','" . $_clean['group_id'] . "')";
        Database::query($sql, __FILE__, __LINE__);

        $status_notify = 1;
    }
    if ($_GET['actionpage'] == 'unlocknotify' && $status_notify == 1) {
        $sql = 'DELETE FROM ' . $tbl_wiki_mailcue . ' WHERE id="' . $id . '" AND user_id="' . api_get_user_id() . '" AND type="P"'; //$_clean['group_id'] not necessary
        Database::query($sql, __FILE__, __LINE__);

        $status_notify = 0;
    }

    //show status

    return $status_notify;
}

/**
 * Notify discussion changes
 * @author Juan Carlos Raña <herodoto@telefonica.net>
 * Return current database status of rating discuss and change it if get action
 */
function check_notify_discuss($page_id) {
    global $tbl_wiki;
    global $groupfilter;
    global $tbl_wiki_mailcue;

    $_clean['group_id'] = (int) $_SESSION['_gid'];
    //$sql='SELECT * FROM '.$tbl_wiki.'WHERE reflink="'.$reflink.'" AND '.$groupfilter.' ORDER BY id ASC';
    $sql = 'SELECT * FROM ' . $tbl_wiki . 'WHERE page_id="' . $page_id . '" AND ' . $groupfilter . ' ORDER BY id ASC';
    $result = Database::query($sql, __LINE__, __FILE__);
    $row = Database::fetch_array($result);

    $id = $row['id'];

    $sql = 'SELECT * FROM ' . $tbl_wiki_mailcue . 'WHERE id="' . $id . '" AND user_id="' . api_get_user_id() . '" AND type="D"';
    $result = Database::query($sql, __LINE__, __FILE__);
    $row = Database::fetch_array($result);

    $idm = $row['id'];

    if (empty($idm)) {
        $status_notify_disc = 0;
    } else {
        $status_notify_disc = 1;
    }

    //change status
    if ($_GET['actionpage'] == 'locknotifydisc' && $status_notify_disc == 0) {
        $sql = "INSERT INTO " . $tbl_wiki_mailcue . " (id, user_id, type, group_id) VALUES ('" . $id . "','" . api_get_user_id() . "','D','" . $_clean['group_id'] . "')";
        Database::query($sql, __FILE__, __LINE__);
        $status_notify_disc = 1;
    }
    if ($_GET['actionpage'] == 'unlocknotifydisc' && $status_notify_disc == 1) {
        $sql = 'DELETE FROM ' . $tbl_wiki_mailcue . ' WHERE id="' . $id . '" AND user_id="' . api_get_user_id() . '" AND type="D"'; //$_clean['group_id'] not necessary
        Database::query($sql, __FILE__, __LINE__);
        $status_notify_disc = 0;
    }

    //show status

    return $status_notify_disc;
}

/**
 * Notify all changes
 * @author Juan Carlos Raña <herodoto@telefonica.net>
 */
function check_notify_all() {

    global $tbl_wiki_mailcue;

    $_clean['group_id'] = (int) $_SESSION['_gid'];

    $sql = 'SELECT * FROM ' . $tbl_wiki_mailcue . 'WHERE user_id="' . api_get_user_id() . '" AND type="F" AND group_id="' . $_clean['group_id'] . '"';
    $result = Database::query($sql, __LINE__, __FILE__);
    $row = Database::fetch_array($result);

    $idm = $row['user_id'];

    if (empty($idm)) {
        $status_notify_all = 0;
    } else {
        $status_notify_all = 1;
    }

    //change status
    if ($_GET['actionpage'] == 'locknotifyall' && $status_notify_all == 0) {
        $sql = "INSERT INTO " . $tbl_wiki_mailcue . " (user_id, type, group_id) VALUES ('" . api_get_user_id() . "','F','" . $_clean['group_id'] . "')";
        Database::query($sql, __FILE__, __LINE__);

        $status_notify_all = 1;
    }
    if ($_GET['actionpage'] == 'unlocknotifyall' && $status_notify_all == 1) {
        $sql = 'DELETE FROM ' . $tbl_wiki_mailcue . ' WHERE user_id="' . api_get_user_id() . '" AND type="F" AND group_id="' . $_clean['group_id'] . '"';
        Database::query($sql, __FILE__, __LINE__);
        $status_notify_all = 0;
    }

    //show status

    return $status_notify_all;
}

/**
 * Function check emailcue and send email when a page change
 * @author Juan Carlos Raña <herodoto@telefonica.net>
 */
function check_emailcue($id_or_ref, $type, $lastime='', $lastuser='') {
    global $tbl_wiki;
    global $groupfilter;
    global $tbl_wiki_mailcue;
    global $_course;

    $_clean['group_id'] = (int) $_SESSION['_gid'];

    $group_properties = GroupManager :: get_group_properties($_clean['group_id']);
    $group_name = $group_properties['name'];

    $allow_send_mail = false; //define the variable to below

    if ($type == 'P') {
        //if modifying a wiki page
        //first, current author and time
        //Who is the author?
        $userinfo = Database::get_user_info_from_id($lastuser);
        $email_user_author = get_lang('EditedBy') . ': ' . api_get_person_name($userinfo['firstname'], $userinfo['lastname']);

        //When ?
        $year = substr($lastime, 0, 4);
        $month = substr($lastime, 5, 2);
        $day = substr($lastime, 8, 2);
        $hours = substr($lastime, 11, 2);
        $minutes = substr($lastime, 14, 2);
        $seconds = substr($lastime, 17, 2);
        $email_date_changes = $day . ' ' . $month . ' ' . $year . ' ' . $hours . ":" . $minutes . ":" . $seconds;

        //second, extract data from first reg
        $sql = 'SELECT * FROM ' . $tbl_wiki . 'WHERE reflink="' . $id_or_ref . '" AND ' . $groupfilter . ' ORDER BY id ASC'; //id_or_ref is reflink from tblwiki

        $result = Database::query($sql, __LINE__, __FILE__);
        $row = Database::fetch_array($result);

        $id = $row['id'];
        $email_page_name = $row['title'];


        if ($row['visibility'] == 1) {
            $allow_send_mail = true; //if visibility off - notify off

            $sql = 'SELECT * FROM ' . $tbl_wiki_mailcue . 'WHERE id="' . $id . '" AND type="' . $type . '" OR type="F" AND group_id="' . $_clean['group_id'] . '"'; //type: P=page, D=discuss, F=full.
            $result = Database::query($sql, __LINE__, __FILE__);

            $emailtext = get_lang('EmailWikipageModified') . ' <strong>' . $email_page_name . '</strong> ' . get_lang('Wiki');
        }
    } elseif ($type == 'D') {
        //if added a post to discuss
        //first, current author and time
        //Who is the author of last message?
        $userinfo = Database::get_user_info_from_id($lastuser);
        $email_user_author = get_lang('AddedBy') . ': ' . api_get_person_name($userinfo['firstname'], $userinfo['lastname']);

        //When ?
        $year = substr($lastime, 0, 4);
        $month = substr($lastime, 5, 2);
        $day = substr($lastime, 8, 2);
        $hours = substr($lastime, 11, 2);
        $minutes = substr($lastime, 14, 2);
        $seconds = substr($lastime, 17, 2);
        $email_date_changes = $day . ' ' . $month . ' ' . $year . ' ' . $hours . ":" . $minutes . ":" . $seconds;

        //second, extract data from first reg

        $id = $id_or_ref; //$id_or_ref is id from tblwiki

        $sql = 'SELECT * FROM ' . $tbl_wiki . 'WHERE id="' . $id . '" ORDER BY id ASC';

        $result = Database::query($sql, __LINE__, __FILE__);
        $row = Database::fetch_array($result);

        $email_page_name = $row['title'];


        if ($row['visibility_disc'] == 1) {
            $allow_send_mail = true; //if visibility off - notify off

            $sql = 'SELECT * FROM ' . $tbl_wiki_mailcue . 'WHERE id="' . $id . '" AND type="' . $type . '" OR type="F" AND group_id="' . $_clean['group_id'] . '"'; //type: P=page, D=discuss, F=full
            $result = Database::query($sql, __LINE__, __FILE__);

            $emailtext = get_lang('EmailWikiPageDiscAdded') . ' <strong>' . $email_page_name . '</strong> ' . get_lang('Wiki');
        }
    } elseif ($type == 'A') {
        //for added pages
        $id = 0; //for tbl_wiki_mailcue

        $sql = 'SELECT * FROM ' . $tbl_wiki . ' ORDER BY id DESC'; //the added is always the last

        $result = Database::query($sql, __LINE__, __FILE__);
        $row = Database::fetch_array($result);

        $email_page_name = $row['title'];

        //Who is the author?
        $userinfo = Database::get_user_info_from_id($row['user_id']);
        $email_user_author = get_lang('AddedBy') . ': ' . api_get_person_name($userinfo['firstname'], $userinfo['lastname']);

        //When ?
        $year = substr($row['dtime'], 0, 4);
        $month = substr($row['dtime'], 5, 2);
        $day = substr($row['dtime'], 8, 2);
        $hours = substr($row['dtime'], 11, 2);
        $minutes = substr($row['dtime'], 14, 2);
        $seconds = substr($row['dtime'], 17, 2);
        $email_date_changes = $day . ' ' . $month . ' ' . $year . ' ' . $hours . ":" . $minutes . ":" . $seconds;


        if ($row['assignment'] == 0) {
            $allow_send_mail = true;
        } elseif ($row['assignment'] == 1) {
            $email_assignment = get_lang('AssignmentDescExtra') . ' (' . get_lang('AssignmentMode') . ')';
            $allow_send_mail = true;
        } elseif ($row['assignment'] == 2) {
            $allow_send_mail = false; //Mode tasks: avoids notifications to all users about all users
        }

        $sql = 'SELECT * FROM ' . $tbl_wiki_mailcue . 'WHERE id="' . $id . '" AND type="F" AND group_id="' . $_clean['group_id'] . '"'; //type: P=page, D=discuss, F=full
        $result = Database::query($sql, __LINE__, __FILE__);

        $emailtext = get_lang('EmailWikiPageAdded') . ' <strong>' . $email_page_name . '</strong> ' . get_lang('In') . ' ' . get_lang('Wiki');
    } elseif ($type == 'E') {
        $id = 0;

        $allow_send_mail = true;

        //Who is the author?
        $userinfo = Database::get_user_info_from_id(api_get_user_id()); //current user
        $email_user_author = get_lang('DeletedBy') . ': ' . api_get_person_name($userinfo['firstname'], $userinfo['lastname']);


        //When ?
        $today = date('r');  //current time
        $email_date_changes = $today;

        $sql = 'SELECT * FROM ' . $tbl_wiki_mailcue . 'WHERE id="' . $id . '" AND type="F" AND group_id="' . $_clean['group_id'] . '"'; //type: P=page, D=discuss, F=wiki
        $result = Database::query($sql, __LINE__, __FILE__);

        $emailtext = get_lang('EmailWikipageDedeleted');
    }


    ///make and send email

    if ($allow_send_mail) {
        while ($row = Database::fetch_array($result)) {
            if (empty($charset)) {
                $charset = 'ISO-8859-1';
            }
            $headers = 'Content-Type: text/html; charset=' . $charset;
            $userinfo = Database::get_user_info_from_id($row['user_id']); //$row['user_id'] obtained from tbl_wiki_mailcue
            $name_to = api_get_person_name($userinfo['firstname'], $userinfo['lastname'], null, PERSON_NAME_EMAIL_ADDRESS);
            $email_to = $userinfo['email'];
            $sender_name = api_get_setting('emailAdministrator');
            $sender_email = api_get_setting('emailAdministrator');
            $email_subject = get_lang('EmailWikiChanges') . ' - ' . $_course['official_code'];
            $email_body = get_lang('DearUser') . ' ' . api_get_person_name($userinfo['firstname'], $userinfo['lastname']) . ',<br /><br />';
            $email_body .= $emailtext . ' <strong>' . $_course['name'] . ' - ' . $group_name . '</strong><br /><br /><br />';
            $email_body .= $email_user_author . ' (' . $email_date_changes . ')<br /><br /><br />';
            $email_body .= $email_assignment . '<br /><br /><br />';
            $email_body .= '<font size="-2">' . get_lang('EmailWikiChangesExt_1') . ': <strong>' . get_lang('NotifyChanges') . '</strong><br />';
            $email_body .= get_lang('EmailWikiChangesExt_2') . ': <strong>' . get_lang('NotNotifyChanges') . '</strong></font><br />';
            api_mail_html($name_to, $email_to, $email_subject, $email_body, $sender_name, $sender_email, $headers);
        }
    }
}

/**
 * Function export last wiki page version to document area
 * @author Juan Carlos Raña <herodoto@telefonica.net>
 */
function export2doc($wikiTitle, $wikiContents, $groupId) {
    $template =
            '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{LANGUAGE}" lang="{LANGUAGE}">
<head>
<title>{TITLE}</title>
<meta http-equiv="Content-Type" content="text/html; charset={ENCODING}" />
<style type="text/css" media="screen, projection">
/*<![CDATA[*/
{CSS}
/*]]>*/
</style>
</head>
<body>
{CONTENT}
</body>
</html>';

    $css_file = api_get_path(TO_SYS, WEB_CSS_PATH) . api_get_setting('stylesheets') . '/default.css';
    if (file_exists($css_file)) {
        $css = @file_get_contents($css_file);
    } else {
        $css = '';
    }
    // Fixing some bugs in css files.
    $css = str_replace('behavior:url("/main/css/csshover3.htc");', '', $css);
    $css = str_replace('main/', $root_rel . 'main/', $css);
    $css = str_replace('images/', $root_rel . $css_path . $theme . 'images/', $css);
    $css = str_replace('../../img/', $root_rel . 'main/img/', $css);

    $template = str_replace(array('{LANGUAGE}', '{ENCODING}', '{TITLE}', '{CSS}'), array(api_get_language_isocode(), api_get_system_encoding(), $wikiTitle, $css), $template);

    if (0 != $groupId) {
        $groupPart = '_group' . $groupId; // and add groupId to put the same document title in different groups
        $group_properties = GroupManager :: get_group_properties($groupId);
        $groupPath = $group_properties['directory'];
    } else {
        $groupPart = '';
        $groupPath = '';
    }

    $exportDir = api_get_path(SYS_COURSE_PATH) . api_get_course_path() . '/document' . $groupPath;
    $exportFile = replace_dangerous_char($wikiTitle, 'strict') . $groupPart;

    $wikiContents = $wikiContents;
    $wikiContents = trim(preg_replace("/\[\[|\]\]/", " ", $wikiContents));

    $wikiContents = str_replace('{CONTENT}', $wikiContents, $template);

    $i = 1;
    while (file_exists($exportDir . '/' . $exportFile . '_' . $i . '.html'))
        $i++; //only export last version, but in new export new version in document area
    $wikiFileName = $exportFile . '_' . $i . '.html';
    $exportPath = $exportDir . '/' . $wikiFileName;
    file_put_contents($exportPath, $wikiContents);
    $doc_id = add_document($_course, $groupPath . '/' . $wikiFileName, 'file', filesize($exportPath), $wikiTitle);
    api_item_property_update($_course, TOOL_DOCUMENT, $doc_id, 'DocumentAdded', api_get_user_id(), $groupId);
    // TODO: link to go document area
}

/**
 * Function prevent double post (reload or F5)
 */
function double_post($wpost_id) {
    if (isset($_SESSION['wpost_id'])) {
        if ($wpost_id == $_SESSION['wpost_id']) {
            return false;
        } else {
            $_SESSION['wpost_id'] = $wpost_id;
            return true;
        }
    } else {
        $_SESSION['wpost_id'] = $wpost_id;
        return true;
    }
}

/**
 * Function wizard individual assignment
 * @author Juan Carlos Raña <herodoto@telefonica.net>
 */
function auto_add_page_users($assignment_type) {
    global $assig_user_id; //need to identify end reflinks

    $_clean['group_id'] = (int) $_SESSION['_gid'];


    if ($_clean['group_id'] == 0) {
        //extract course members
        if (!empty($_SESSION["id_session"])) {
            $a_users_to_add = CourseManager :: get_user_list_from_course_code($_SESSION['_course']['id'], true, $_SESSION['id_session']);
        } else {
            $a_users_to_add = CourseManager :: get_user_list_from_course_code($_SESSION['_course']['id'], true);
        }
    } else {
        //extract group members
        $subscribed_users = GroupManager :: get_subscribed_users($_clean['group_id']);
        $subscribed_tutors = GroupManager :: get_subscribed_tutors($_clean['group_id']);
        $a_users_to_add_with_duplicates = array_merge($subscribed_users, $subscribed_tutors);

        //remove duplicates
        $a_users_to_add = $a_users_to_add_with_duplicates;
        //array_walk($a_users_to_add, create_function('&$value,$key', '$value = json_encode($value);'));
        $a_users_to_add = array_unique($a_users_to_add);
        //array_walk($a_users_to_add, create_function('&$value,$key', '$value = json_decode($value, true);'));
    }

    //echo print_r($a_users_to_add);

    $all_students_pages = array();

    //data about teacher
    $userinfo = Database::get_user_info_from_id(api_get_user_id());
    require_once api_get_path(INCLUDE_PATH) . '/lib/usermanager.lib.php';
    if (api_get_user_id() <> 0) {
        $image_path = UserManager::get_user_picture_path_by_id(api_get_user_id(), 'web', false, true);
        $image_repository = $image_path['dir'];
        $existing_image = $image_path['file'];
        $photo = '<img src="' . $image_repository . $existing_image . '" alt="' . $name . '"  width="40" height="50" align="top" title="' . $name . '"  />';
    } else {
        $photo = '<img src="' . api_get_path(WEB_CODE_PATH) . "img/unknown.jpg" . '" alt="' . $name . '"  width="40" height="50" align="top"  title="' . $name . '"  />';
    }

    //teacher assignment title
    $title_orig = $_POST['title'];

    //teacher assignment reflink
    $link2teacher = $_POST['title'] = $title_orig . "_uass" . api_get_user_id();

    //first: teacher name, photo, and assignment description (original content)
    // $content_orig_A='<div align="center" style="background-color: #F5F8FB;  border:double">'.$photo.'</br>'.api_get_person_name($userinfo['firstname'], $userinfo['lastname']).'</br>('.get_lang('Teacher').')</div><br/><div>';

    $content_orig_A = '<div align="center" style="background-color: #F5F8FB; border:solid; border-color: #E6E6E6"><table border="0"><tr><td style="font-size:24px">' . get_lang('AssignmentDesc') . '</td></tr><tr><td>' . $photo . '</br>' . api_get_person_name($userinfo['firstname'], $userinfo['lastname']) . '</td></tr></table></div>';

    $content_orig_B = '<br/><div align="center" style="font-size:24px">' . get_lang('AssignmentDescription') . ': ' . $title_orig . '</div><br/>' . $_POST['content'];

    //Second: student list (names, photo and links to their works).
    //Third: Create Students work pages.


    foreach ($a_users_to_add as $user_id => $o_user_to_add) {
        if ($o_user_to_add['user_id'] != api_get_user_id()) { //except that puts the task
            $assig_user_id = $o_user_to_add['user_id']; //identifies each page as created by the student, not by teacher
            $image_path = UserManager::get_user_picture_path_by_id($assig_user_id, 'web', false, true);
            $image_repository = $image_path['dir'];
            $existing_image = $image_path['file'];
            $name = api_get_person_name($o_user_to_add['firstname'], $o_user_to_add['lastname']);
            $photo = '<img src="' . $image_repository . $existing_image . '" alt="' . $name . '"  width="40" height="50" align="bottom" title="' . $name . '"  />';

            $is_tutor_of_group = GroupManager :: is_tutor_of_group($assig_user_id, $_clean['group_id']); //student is tutor
            $is_tutor_and_member = (GroupManager :: is_tutor_of_group($assig_user_id, $_clean['group_id']) && GroupManager :: is_subscribed($assig_user_id, $_clean['group_id'])); //student is tutor and member

            if ($is_tutor_and_member) {
                $status_in_group = get_lang('GroupTutorAndMember');
            } else {
                if ($is_tutor_of_group) {
                    $status_in_group = get_lang('GroupTutor');
                } else {
                    $status_in_group = " "; //get_lang('GroupStandardMember')
                }
            }

            if ($assignment_type == 1) {
                $_POST['title'] = $title_orig;
                $_POST['comment'] = get_lang('AssignmentFirstComToStudent');
                $_POST['content'] = '<div align="center" style="background-color: #F5F8FB; border:solid; border-color: #E6E6E6"><table border="0"><tr><td style="font-size:24px">' . get_lang('AssignmentWork') . '</td></tr><tr><td>' . $photo . '</br>' . $name . '</td></tr></table></div>[[' . $link2teacher . ' | ' . get_lang('AssignmentLinktoTeacherPage') . ']] '; //If $content_orig_B is added here, the task written by the professor was copied to the page of each student. TODO: config options
                //AssignmentLinktoTeacherPage
                $all_students_pages[] = '<li>' . strtoupper($o_user_to_add['lastname']) . ', ' . $o_user_to_add['firstname'] . ' [[' . $_POST['title'] . "_uass" . $assig_user_id . ' | ' . $photo . ']] ' . $status_in_group . '</li>'; //don't change this line without guaranteeing that users will be ordered by last names in the following format (surname, name)
                //$all_students_pages[] = '<li><table border="0"><tr><td width="200">'.api_get_person_name($o_user_to_add['lastname'], $o_user_to_add['firstname']).'</td><td>[['.$_POST['title']."_uass".$assig_user_id.' | '.$photo.']] '.$status_in_group.'</td></tr></table></li>';

                $_POST['assignment'] = 2;
            }
            $saved = save_new_wiki();
        }
    }//end foreach for each user


    foreach ($a_users_to_add as $user_id => $o_user_to_add) {

        if ($o_user_to_add['user_id'] == api_get_user_id()) {
            $assig_user_id = $o_user_to_add['user_id'];
            if ($assignment_type == 1) {
                $_POST['title'] = $title_orig;
                $_POST['comment'] = get_lang('AssignmentDesc');
                sort($all_students_pages);
                $_POST['content'] = $content_orig_A . $content_orig_B . '<br/><div align="center" style="font-size:18px; background-color: #F5F8FB; border:solid; border-color:#E6E6E6">' . get_lang('AssignmentLinkstoStudentsPage') . '</div><br/><div style="background-color: #F5F8FB; border:solid; border-color:#E6E6E6"><ol>' . implode($all_students_pages) . '</ol></div><br/>';
                $_POST['assignment'] = 1;
            }

            $saved = save_new_wiki();
        }
    } //end foreach to teacher
}

/**
 * Enter description here...
 *
 */
function display_wiki_search_results($search_term, $search_content=0) {
    global $tbl_wiki, $groupfilter, $MonthsLong, $_course;

    api_display_tool_title(get_lang('WikiSearchResults'));

    $_clean['group_id'] = (int) $_SESSION['_gid'];

    if (api_is_allowed_to_edit(false, true) || api_is_platform_admin()) { //only by professors if page is hidden
        if ($search_content == '1') {
            $sql = "SELECT * FROM " . $tbl_wiki . " s1 WHERE title LIKE '%" . Database::escape_string($search_term) . "%' OR content LIKE '%" . Database::escape_string($search_term) . "%' AND id=(SELECT MAX(s2.id) FROM " . $tbl_wiki . " s2 WHERE s1.reflink = s2.reflink AND " . $groupfilter . ")"; // warning don't use group by reflink because don't return the last version
        } else {
            $sql = "SELECT * FROM " . $tbl_wiki . " s1 WHERE title LIKE '%" . Database::escape_string($search_term) . "%' AND id=(SELECT MAX(s2.id) FROM " . $tbl_wiki . " s2 WHERE s1.reflink = s2.reflink AND " . $groupfilter . ")"; // warning don't use group by reflink because don't return the last version
        }
    } else {
        if ($search_content == '1') {

            $sql = "SELECT * FROM " . $tbl_wiki . " s1 WHERE  visibility=1 AND title LIKE '%" . Database::escape_string($search_term) . "%' OR content LIKE '%" . Database::escape_string($search_term) . "%' AND id=(SELECT MAX(s2.id) FROM " . $tbl_wiki . " s2 WHERE s1.reflink = s2.reflink AND " . $groupfilter . ")"; // warning don't use group by reflink because don't return the last version
        } else {
            $sql = "SELECT * FROM " . $tbl_wiki . " s1 WHERE  visibility=1 AND title LIKE '%" . Database::escape_string($search_term) . "%' AND id=(SELECT MAX(s2.id) FROM " . $tbl_wiki . " s2 WHERE s1.reflink = s2.reflink AND " . $groupfilter . ")"; // warning don't use group by reflink because don't return the last version
        }
    }

    $result = Database::query($sql, __LINE__, __FILE__);

    //show table
    if (Database::num_rows($result) > 0) {
        $row = array();
        while ($obj = Database::fetch_object($result)) {
            //get author
            $userinfo = Database::get_user_info_from_id($obj->user_id);

            //get time
            $year = substr($obj->dtime, 0, 4);
            $month = substr($obj->dtime, 5, 2);
            $day = substr($obj->dtime, 8, 2);
            $hours = substr($obj->dtime, 11, 2);
            $minutes = substr($obj->dtime, 14, 2);
            $seconds = substr($obj->dtime, 17, 2);

            //get type assignment icon
            if ($obj->assignment == 1) {
                $ShowAssignment = '<img src="../img/wiki/assignment.gif" title="' . get_lang('AssignmentDesc') . '" alt="' . get_lang('AssignmentDesc') . '" />';
            } elseif ($obj->assignment == 2) {
                $ShowAssignment = '<img src="../img/wiki/works.gif" title="' . get_lang('AssignmentWork') . '" alt="' . get_lang('AssignmentWork') . '" />';
            } elseif ($obj->assignment == 0) {
                $ShowAssignment = '<img src="../img/wiki/trans.gif" />';
            }

            $row = array();
            $row[] = $ShowAssignment;
            $row[] = '<a href="' . api_get_self() . '?cidReq=' . $_course[id] . '&action=showpage&page_id=' . Security::remove_XSS($obj->page_id) . '&title=' . urlencode($obj->reflink) . '&group_id=' . Security::remove_XSS($_GET['group_id']) . '">' . $obj->title . '</a>';
            //$row[] = $obj->user_id <> 0 ? '<a href="../user/userInfo.php?uInfo=' . $userinfo['user_id'] . '">' . api_get_person_name($userinfo['firstname'], $userinfo['lastname']) . '</a>' : get_lang('Anonymous') . ' (' . $obj->user_ip . ')';
            $row[] = $obj->user_id <>0 ? sprintf('<a id="user_id_%s" class="user_info" href="javascript:void(0);">'.api_get_person_name($userinfo['firstname'], $userinfo['lastname']).'</a>',$userinfo['user_id']) : get_lang('Anonymous').' ('.$obj->user_ip.')';
            $row[] = $year . '-' . $month . '-' . $day . ' ' . $hours . ":" . $minutes . ":" . $seconds;

            if (api_is_allowed_to_edit(false, true) || api_is_platform_admin()) {
                $showdelete = ' 
                    <a href="' . api_get_self() . '?cidReq=' . $_course[id] . '&action=delete&page_id=' . Security::remove_XSS($obj->page_id) . '&title=' . urlencode(Security::remove_XSS($obj->reflink)) . '&group_id=' . Security::remove_XSS($_GET['group_id']) . '">
                    '.Display::return_icon('pixel.gif',get_lang('Delete'),array('class' => 'actionplaceholdericon actiondelete')).'
                     ';
            }
            $row[] = '<a href="' . api_get_self() . '?cidReq=' . $_course[id] . '&action=edit&page_id=' . Security::remove_XSS($obj->page_id) . '&title=' . urlencode(Security::remove_XSS($obj->reflink)) . '&group_id=' . Security::remove_XSS($_GET['group_id']) . '">
                    
                    '.Display::return_icon('pixel.gif',get_lang('EditPage'),array('class' => 'actionplaceholdericon actionedit')).'
                        

                    </a> 
                    <a href="' . api_get_self() . '?cidReq=' . $_course[id] . '&action=discuss&page_id=' . Security::remove_XSS($obj->page_id) . '&title=' . urlencode(Security::remove_XSS($obj->reflink)) . '&group_id=' . Security::remove_XSS($_GET['group_id']) . '">
                
                    '.Display::return_icon('pixel.gif',get_lang('Discuss'),array('class' => 'actionplaceholdericon actioncomments')).'

                </a> <a href="' . api_get_self() . '?cidReq=' . $_course[id] . '&action=history&page_id=' . Security::remove_XSS($obj->page_id) . '&title=' . urlencode(Security::remove_XSS($obj->reflink)) . '&group_id=' . Security::remove_XSS($_GET['group_id']) . '">
                    '.Display::return_icon('pixel.gif',get_lang('History'),array('class' => 'actionplaceholdericon actionhistory')).'
                    
                </a> <a href="' . api_get_self() . '?cidReq=' . $_course[id] . '&action=links&page_id=' . Security::remove_XSS($obj->page_id) . '&title=' . urlencode(Security::remove_XSS($obj->reflink)) . '&group_id=' . Security::remove_XSS($_GET['group_id']) . '">
                    '.Display::return_icon('pixel.gif',get_lang('LinkPages'),array('class' => 'actionplaceholdericon actionwikireference')).'
                    
                </a>' . $showdelete;
            
            
            
            
            $rows[] = $row;
        }

        $table = new SortableTableFromArrayConfig($rows, 1, 10, 'SearchPages_table', '', '', 'ASC');
        $table->set_additional_parameters(array('cidReq' => Security::remove_XSS($_GET['cidReq']), 'action' => Security::remove_XSS($_GET['action']), 'group_id' => Security::remove_XSS($_GET['group_id']), 'search_term' => Security::remove_XSS($_GET['search_term'])));

        //$table->set_header(0, get_lang('Type'), false, array('style' => 'width:30px;'));
        $table->set_header(1, get_lang('Title'), false);
        $table->set_header(2, get_lang('Author') . ' (' . get_lang('LastVersion') . ')', false);
        $table->set_header(3, get_lang('Date') . ' (' . get_lang('LastVersion') . ')', false);
        $table->set_header(4, get_lang('Actions'), false, array('style' => 'width:130px;'));

        $table->display();
    } else {
        echo get_lang('NoSearchResults');
    }
}

/**
 * Enter description here...
 *
 */
function draw_date_picker($prefix, $default='') {

    if (empty($default)) {
        $default = date('Y-m-d H:i:s');
    }
    $parts = split(' ', $default);
    list($d_year, $d_month, $d_day) = split('-', $parts[0]);
    list($d_hour, $d_minute) = split(':', $parts[1]);

    $month_list = array(
        1 => get_lang('JanuaryLong'),
        2 => get_lang('FebruaryLong'),
        3 => get_lang('MarchLong'),
        4 => get_lang('AprilLong'),
        5 => get_lang('MayLong'),
        6 => get_lang('JuneLong'),
        7 => get_lang('JulyLong'),
        8 => get_lang('AugustLong'),
        9 => get_lang('SeptemberLong'),
        10 => get_lang('OctoberLong'),
        11 => get_lang('NovemberLong'),
        12 => get_lang('DecemberLong')
    );

    $minute = range(10, 59);
    array_unshift($minute, '00', '01', '02', '03', '04', '05', '06', '07', '08', '09');
    $date_form = make_select($prefix . '_day', array_combine(range(1, 31), range(1, 31)), $d_day);
    $date_form .= make_select($prefix . '_month', $month_list, $d_month);
    $date_form .= make_select($prefix . '_year', array($d_year - 2 => $d_year - 2, $d_year - 1 => $d_year - 1, $d_year => $d_year, $d_year + 1 => $d_year + 1, $d_year + 2 => $d_year + 2), $d_year) . '&nbsp;&nbsp;&nbsp;&nbsp;';
    $date_form .= make_select($prefix . '_hour', array_combine(range(0, 23), range(0, 23)), $d_hour) . ' : ';
    $date_form .= make_select($prefix . '_minute', $minute, $d_minute);
    return $date_form;
}

/**
 * Enter description here...
 *
 */
function make_select($name, $values, $checked='') {
    $output = '<select name="' . $name . '" id="' . $name . '">';
    foreach ($values as $key => $value) {
        $output .= '<option value="' . $key . '" ' . (($checked == $key) ? 'selected="selected"' : '') . '>' . $value . '</option>';
    }
    $output .= '</select>';
    return $output;
}

/**
 * Enter description here...
 *
 */
function get_date_from_select($prefix) {
    return $_POST[$prefix . '_year'] . '-' . two_digits($_POST[$prefix . '_month']) . '-' . two_digits($_POST[$prefix . '_day']) . ' ' . two_digits($_POST[$prefix . '_hour']) . ':' . two_digits($_POST[$prefix . '_minute']) . ':00';
}

/**
 * converts 1-9 to 01-09
 */
function two_digits($number) {
    $number = (int) $number;
    return ($number < 10) ? '0' . $number : $number;
}

/**
 *
 */
function display_back_for_more_page() {
    echo '<a href="index.php?action=more&title=' . Security::Remove_XSS($_GET['title']) . '">'.Display::return_icon('pixel.gif', get_lang('Back'), array('class' => 'toolactionplaceholdericon toolactionback')).get_lang('Back').'</a>';
    echo '</a>';
}
?>