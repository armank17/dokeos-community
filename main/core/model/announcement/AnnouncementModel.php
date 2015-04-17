<?php

/* For licensing terms, see /license.txt */

/**
 * Announcement Model
 */
class AnnouncementModel {

    // definition tables
    protected $tableAnnouncement;
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
        $this->tableAnnouncement = Database :: get_course_table(TABLE_ANNOUNCEMENT, $courseDb);
    }

    /**
     *  Get announcement list
     *  @param      int     Optional, User id 
     *  @return`    array   Announcements
     */
    public function getAnnouncementList() {
        $session_id = api_get_session_id();
        $session_id = intval($session_id);
        
        $condition_session = ($session_id > 0) ? " AND (ip.id_session = " . (int) $session_id . " OR ip.id_session = 0 ) " : " AND ip.id_session = 0";

        $sql_group = "SELECT * FROM " . Database :: get_course_table(TABLE_GROUP_USER) . " WHERE user_id=" . api_get_user_id();
        $result_group = Database::query($sql_group);

        $array_group = array();
        while ($objGroup = Database::fetch_object($result_group)) {
            array_push($array_group, $objGroup);
        }

        $query_group = " AND (ip.to_user_id = " . api_get_user_id() . " OR ip.to_group_id IN (0)) ";
        if (count($array_group) > 0) {
            $array_id_group = array();
            foreach ($array_group as $index => $objGroup) {
                array_push($array_id_group, $objGroup->id);
            }
            array_push($array_id_group, '0');
            $query_group = " AND (ip.to_user_id = " . api_get_user_id() . " OR ip.to_group_id IN (" . implode(',', $array_id_group) . ")) ";
        }

//        $sql ="SELECT DISTINCT a.id, a.title, a.content, date_format(a.end_date,'%b %d') AS announcementdate 
//        FROM ".Database :: get_course_table(TABLE_ANNOUNCEMENT)." a
//        INNER JOIN ".Database :: get_course_table(TABLE_ITEM_PROPERTY)." ip ON ip.ref = a.id
//        WHERE ip.tool LIKE 'announcement'
//        AND ip.visibility = 1 ";            
        $sql = "SELECT DISTINCT a.id, a.title, a.content,a.end_date AS announcementdate
				FROM " . Database :: get_course_table(TABLE_ANNOUNCEMENT) . " a, " . Database :: get_course_table(TABLE_ITEM_PROPERTY) . " ip
				WHERE a.id = ip.ref
				AND ip.tool='announcement' ";
        
        
        $array_user = api_get_user_info();
        if ($array_user['status'] >= -1) {// student or teacher
            $sql.=$query_group;
            //$sql.=" OR ip.insert_user_id =".api_get_user_id()." ";
        }
        $sql.=" $condition_session  ORDER BY a.display_order DESC";
        $announcements = array();

        $rs = Database::query($sql);
        if (Database::num_rows($rs) > 0) {
            while ($row = Database::fetch_object($rs)) {
                $announcements[$row->id] = $row;
            }
        }

        return $announcements;
    }

    /**
     *  Get max announcement id     
     *  @return`    int   announcement id
     */
    public function getLastAnnouncement() {
        $announcements = array();
        //$query = "SELECT id FROM {$this->tableAnnouncement} ORDER BY id";
        $sql_group = "SELECT * FROM " . Database :: get_course_table(TABLE_GROUP_USER) . " WHERE user_id=" . api_get_user_id();
        $result_group = Database::query($sql_group);
        $array_group = array();
        while ($objGroup = Database::fetch_object($result_group)) {
            array_push($array_group, $objGroup);
        }

        //$query_group = " OR ip.to_user_id = ".api_get_user_id()." ";
        $query_group = " OR (ip.to_user_id = " . api_get_user_id() . " OR ip.to_group_id IN (0)) ";
        if (count($array_group) >= -1) {
            $array_id_group = array();
            foreach ($array_group as $index => $objGroup) {
                array_push($array_id_group, $objGroup->id);
            }
            array_push($array_id_group, '-1');
            $query_group = " OR (ip.to_user_id = " . api_get_user_id() . " OR ip.to_group_id IN (" . implode(',', $array_id_group) . ")) ";
        }

        $sql = "SELECT DISTINCT a.id  
        FROM " . Database :: get_course_table(TABLE_ANNOUNCEMENT) . " a
        INNER JOIN " . Database :: get_course_table(TABLE_ITEM_PROPERTY) . " ip ON ip.ref = a.id
        WHERE ip.tool LIKE 'announcement'
        AND ip.visibility = 1 ";
        $array_user = api_get_user_info();
        if ($array_user['status'] > -1) {// student or teacher
            $sql.=$query_group;
            $sql.=" OR ip.insert_user_id =" . api_get_user_id() . " ";
        }
        $sql.=" ORDER BY a.id DESC LIMIT 1";

        $rs = Database::query($sql);
        if (Database::num_rows($rs) > 0) {
            while ($row = Database::fetch_object($rs)) {
                $announcementId = $row->id;
            }
        }

        return $announcementId;
    }

    /**
     * Get information about the Announcement
     * @param   int     Announcement id
     * @return  array   Announcement information
     */
    public function getAnnouncementInfo($announcementId) {
        // Database table definition        
        $t_item_propery = Database :: get_course_table(TABLE_ITEM_PROPERTY);
        $table_user = Database::get_main_table(TABLE_MAIN_USER);

        $sql = "SELECT 	ann.id		 		AS announcement_id,
                        ann.title 			AS announcement_title,
                        ann.content	 		AS announcement_content,
						ann.scenario_filter AS scenario_filter,
                        ann.end_date AS announcement_date, 
						user.firstname		AS firstname,
						user.lastname		AS lastname,
						ip.insert_user_id   AS insert_user_id,
						ip.to_user_id		AS to_user_id,
						ip.to_group_id		AS to_group_id,
						ip.visibility		AS visibility
                FROM {$this->tableAnnouncement} ann, $t_item_propery ip, $table_user user
                WHERE ann.id = ip.ref
                AND tool = '" . TOOL_ANNOUNCEMENT . "'
				AND ip.insert_user_id = user.user_id
                AND ann.id = '" . Database::escape_string($announcementId) . "' ";                
        $result = Database::query($sql, __FILE__, __LINE__);
        $info = Database::fetch_array($result);

        return $info;
    }

    /**
     * This function gets all the groups and users (or combination of these) that can see this announcement
     * This is mainly used when editing
     */
    function get_announcement_dest($announcementId) {
        // Database table definition
        $t_item_propery = Database :: get_course_table(TABLE_ITEM_PROPERTY);

        $sql = "SELECT * FROM $t_item_propery WHERE tool='" . TOOL_ANNOUNCEMENT . "' AND ref='" . Database::escape_string($announcementId) . "' AND to_user_id <> " . api_get_user_id();
		//$sql = "SELECT * FROM $t_item_propery WHERE tool='" . TOOL_ANNOUNCEMENT . "' AND ref='" . Database::escape_string($announcementId) . "'";
        $result = Database::query($sql, __FILE__, __LINE__);
        while ($row = Database::fetch_array($result, ASSOC)) {
            if ($row['to_group_id'] <> 0) {
                $to_group_id[] = $row['to_group_id'];
            }
            if (!empty($row['to_user_id'])) {
                $to_user_id[] = $row['to_user_id'];
            }
        }

        return array('to_group_id' => $to_group_id, 'to_user_id' => $to_user_id);
    }

    /**
     * This function gets all the steps in the course scenario
     * This is mainly used for filtering users
     */
    function get_steps() {
        // Database table definition
        $t_steps = Database :: get_course_table(TABLE_SCENARIO_STEPS);
        $steps = array();

        $sql = "SELECT * FROM $t_steps";
        $result = Database::query($sql, __FILE__, __LINE__);
        while ($row = Database::fetch_array($result, ASSOC)) {
            $steps[$row['id']] = $row['step_name'];
        }

        return $steps;
    }

    /**
     * Get announcement formulary
     * @return  object  Form object    
     */
    public function getForm() {
        global $charset, $_course;
        $session_id = api_get_session_id();
        $session_id = intval($session_id);
        // initiate the object        
        $form = new FormValidator('announcement_form', 'post', api_get_self() . '?' . api_get_cidreq() . ($this->announcement_id ? '&amp;action=edit&amp;id=' . intval($this->announcement_id) : '&amp;action=add'));
        $renderer = & $form->defaultRenderer();
        

        if ($this->announcement_id) {
            $form->addElement('hidden', 'announcement_id', $this->announcement_id);
        }

        if (api_is_allowed_to_edit(false, true) OR api_is_allowed_to_session_edit(false, true)) {
            // The receivers: groups
            $course_groups = CourseManager::get_group_list_of_course(api_get_course_id(), intval($this->session_id));
            foreach ($course_groups as $key => $group) {
                $receivers ['G' . $key] = '-G- ' . $group ['name'];
            }
            // The receivers: users
            if($session_id!=0){
                $course_users = CourseManager::get_user_list_from_course_code(api_get_course_id(), intval($this->session_id) == 0, intval($session_id));
            }else{
                $course_users = CourseManager::get_user_list_from_course_code(api_get_course_id(), false, intval($this->session_id));
                $num_course_users = sizeof($course_users);
            }
            
            foreach ($course_users as $key => $user) {
                if (intval(api_get_user_id()) == $key) {
                    //$defaults['send_to']['to'][] = 'U'.$key;                     
                    continue;
                }

                $receivers ['U' . $key] = $user ['lastname'] . ' ' . $user ['firstname'];
            }
        }
        $defaults ['send_to'] ['receivers'] = 0;
        if ($this->announcement_id) {
            $announcementInfo = $this->getAnnouncementInfo($this->announcement_id);
            $defaults['title'] = $announcementInfo['announcement_title'];
            $defaults['scenario_filter'] = $announcementInfo['scenario_filter'];
            $filter_array = explode(",", $announcementInfo['scenario_filter']);
            if (strpos($announcementInfo['announcement_content'], '../../courses/') !== FALSE) {
                $announcementInfo['announcement_content'] = str_replace('../../courses/', '/courses/', $announcementInfo['announcement_content']);
            }
            $defaults['description'] = $announcementInfo['announcement_content'];

            if (!empty($announcementInfo['to_user_id']) && $announcementInfo['to_group_id'] == '99999') {
                $defaults['send_to'] ['receivers'] = 2;
                $user_group_ids = $this->get_announcement_dest($this->announcement_id);
                $num_course_users = sizeof($user_group_ids['to_user_id']);
                $send_filter_users = implode(",", $user_group_ids['to_user_id']);
            } else if (!empty($announcementInfo['to_user_id'])) {
                $defaults['send_to'] ['receivers'] = 1;
                $user_group_ids = $this->get_announcement_dest($this->announcement_id);
            } else if (empty($announcementInfo['to_user_id']) && $announcementInfo['visibility'] == 0) {
                $defaults['send_to'] ['receivers'] = -1;
            } else {
                $defaults['send_to'] ['receivers'] = 0;
            }

            foreach ($user_group_ids['to_user_id'] as $key => $user_id) {
                if (intval(api_get_user_id()) == $user_id) {
                    //$defaults['send_to']['to'][] = 'U'.$key;                     
                    continue;
                }
                $defaults['send_to']['to'][] = 'U' . $user_id;
            }

            foreach ($user_group_ids['to_group_id'] as $key => $group_id) {
                $defaults['send_to']['to'][] = 'G' . $group_id;
            }
        } else {
            if (isset($_GET['remind_inactive'])) {
                $defaults['send_to']['receivers'] = 1;
                $defaults['title'] = sprintf(get_lang('RemindInactiveLearnersMailSubject'), api_get_setting('siteName'));
                $defaults['content'] = sprintf(get_lang('RemindInactiveLearnersMailContent'), api_get_setting('siteName'), 7);
                $defaults['send_to']['to'][] = 'U' . intval($_GET['remind_inactive']);
            } elseif (isset($_GET['remindallinactives']) && $_GET['remindallinactives'] == 'true') {
                $defaults['send_to']['receivers'] = 1;
                $since = isset($_GET['since']) ? intval($_GET['since']) : 6;
                $to = Tracking :: get_inactives_students_in_course($_course['id'], $since, api_get_session_id());
                foreach ($to as $user) {
                    if (!empty($user)) {
                        $defaults['send_to']['to'][] = 'U' . $user;
                    }
                }
                $defaults['title'] = sprintf(get_lang('RemindInactiveLearnersMailSubject'), api_get_setting('siteName'));
                $defaults['content'] = sprintf(get_lang('RemindInactiveLearnersMailContent'), api_get_setting('siteName'), $since);
            }
        }
        $form->setDefaults($defaults);

        $renderer->setElementTemplate('<div class="row"><div style="width:90%;float:left;padding-left:15px;">' . get_lang('VisibleFor') . '&nbsp;&nbsp;{element}</div></div>', 'send_to');
        $form->addElement('receivers', 'send_to', get_lang('VisibleFor'), array('receivers' => $receivers, 'receivers_selected' => ''), 'announcement');

        // The title
        $renderer->setElementTemplate('<div class="row"><div style="width:90%;float:left;padding-left:15px;"><!-- BEGIN error --><span class="form_error">{error}</span><br /><!-- END error -->' . get_lang('Announcement') . '&nbsp;&nbsp;{element}</div></div>', 'title');

        $steps = $this->get_steps();

        $form->addElement('html', "<div class='row'><div class='receivers_scenario' style='padding-left:12px;'>");
        $form->addElement('html', '<span><b>' . get_lang('ScenarioFilters') . '</b></span></br>');
        $form->addElement('html', '<span>' . get_lang('FilterScenarioDescription') . '</span>');

        $form->addElement('html', "</br></br><table class='data_table' width='100%'>");
        $form->addElement('html', "<tr><th width='40%'>" . get_lang('StepName') . "</th><th width='15% width='15%'>" . get_lang('Not') . "</th><th width='15%'>" . get_lang('Started') . "</th><th width='15%'>" . get_lang('Finished') . "</th><th width='15%'>" . get_lang('Passed') . "</th></tr>");
        if (sizeof($steps) > 0) {
            $form->addElement('html', "<tr><td colspan='5'><div><table class='data_table' width='100%'>");

            $i = 0;
            foreach ($steps as $key => $value) {

                if (in_array('1-' . $key, $filter_array)) {
                    $checked_1 = " checked";
                } else {
                    $checked_1 = "";
                }

                if (in_array('2-' . $key, $filter_array)) {
                    $checked_2 = " checked";
                } else {
                    $checked_2 = "";
                }

                if (in_array('3-' . $key, $filter_array)) {
                    $checked_3 = " checked";
                } else {
                    $checked_3 = "";
                }

                if (in_array('4-' . $key, $filter_array)) {
                    $checked_4 = " checked";
                } else {
                    $checked_4 = "";
                }

                if (($i % 2) == 0) {
                    $class = " class = 'row_odd'";
                } else {
                    $class = " class = 'row_even'";
                }

                $form->addElement('html', "<tr " . $class . "><td width='41%'>" . $value . "</td><td width='15%'><input id='check-1-" . $key . "' class='checkbox' type='checkbox' name='choice[1][" . $key . "]' " . $checked_1 . " value='1-" . $key . "' /><label for='check-1-" . $key . "'>&nbsp;</label></td>");
                $form->addElement('html', "<td width='15%'><input id='check-2-" . $key . "' class='checkbox' type='checkbox' name='choice[2][" . $key . "]' " . $checked_2 . " value='2-" . $key . "' />			
    <label for='check-2-" . $key . "'>&nbsp;</label></td>");
                $form->addElement('html', "<td width='15%'><input id='check-3-" . $key . "' class='checkbox' type='checkbox' name='choice[3][" . $key . "]' " . $checked_3 . " value='3-" . $key . "' />		
    <label for='check-3-" . $key . "'>&nbsp;</label></td>");
                $form->addElement('html', "<td width='15%'><input id='check-4-" . $key . "' class='checkbox' type='checkbox' name='choice[4][" . $key . "]' " . $checked_4 . " value='4-" . $key . "' />			
    <label for='check-4-" . $key . "'>&nbsp;</label></td></tr>");
                $i++;
            }

            $form->addElement('html', "</table></div></td></tr>");
        } else {
            $form->addElement('html', "<tr><td colspan='5'>" . get_lang('NoSteps') . "</td></tr>");
        }
        $form->addElement('html', "</table></br>");
        $form->addElement('hidden', 'scenario_filter', '', 'id="scenario_filter"');
        $form->addElement('html', "<div class='custom_scenario_announcement'>" . get_lang('EmailTargetAudience') . "&nbsp;<span id='ajax_users' class='announcement_audience'>" . $num_course_users . "<input type='hidden' name='send_filter' id='send_filter' value='" . $send_filter_users . "' /></span>&nbsp;" . get_lang('People') . "&nbsp;&nbsp;&nbsp;<a href='javascript:void(0);' id='see_list_users'>(<u>" . get_lang('SeeList') . "</u>)</a></div>");
        $form->addElement('html', '<div class="scenario_dialog" style="display:none;"></div>');

        $form->addElement('html', "</div></div>");

        $form->addElement('text', 'title', get_lang('Announcement'), array('size' => '60', 'class' => 'focus', 'id' => 'announcement_title_id'));

        $form->add_html_editor('description', '', false, false, api_is_allowed_to_edit() ? array('ToolbarSet' => 'Announcements', 'Width' => '650px', 'Height' => '120px') : array('ToolbarSet' => 'AnnouncementsStudent', 'Width' => '650px', 'Height' => '120px', 'UserStatus' => 'student'));

        $patterns = $this->getExamPatterns();
        $pattern = array();

        //$form->addElement('html',api_disp_html_area('feedback_email', '', '', '', null, $editorConfig));
        $form->addElement('html', '<div class="row"><div style="padding-left:12px;"><label for="feedback-tokens">' . get_lang('Variables') . '&nbsp;&nbsp;</label><select name="feedback-tokens" id="feedback-tokens" size="1" data-placeholder="' . get_lang("SelectAVariable") . '">');
        $form->addElement('html', '<option></option>');
        foreach ($patterns as $pattern) {
            $form->addElement('html', '<option value="' . $pattern['token'] . '">' . $pattern['name'] . '&nbsp;' . $pattern['token'] . '</option>');
        }
        $form->addElement('html', '</select></div></div>');

        if ($this->announcement_id) {
            $link = api_get_self() . '?' . api_get_cidreq() . '&amp;action=delete&amp;id=' . intval($this->announcement_id);
            $title = get_lang("ConfirmationDialog");
            $text = get_lang("ConfirmYourChoice");
            $form->addElement('html', '<div align="left" style="padding-left:10px;"><a href="javascript:void(0);" onclick="Alert_Confim_Delete(\'' . $link . '\',\'' . $title . '\',\'' . $text . '\');">' . Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')) . '&nbsp;&nbsp;' . get_lang('Delete') . '</a></div>');
        }
        $form->addElement('style_submit_button', 'SubmitAnnouncement', get_lang('Validate'), 'class="save"');

        $token = Security::get_token();
        $form->addElement('hidden', 'sec_token');
        $form->setConstants(array('sec_token' => $token));

        return $form;
    }

    /**
     * This functions stores the note in the database
     * @return  int       Last insert id
     */
    public function save() {
        $t_item_propery = Database :: get_course_table(TABLE_ITEM_PROPERTY);
        $lastInsertId = 0;
        $user_id = api_get_user_id();

        if ($this->announcement_id) {
            // create the SQL statement to edit the announcement
            $sql = "UPDATE {$this->tableAnnouncement} SET 
                    title           = '" . Database::escape_string($this->title) . "',
                    content         = '" . Database::escape_string($this->description) . "',
                    scenario_filter = '" . Database::escape_string($this->scenario_filter) . "'
                    WHERE id = '" . Database::escape_string($this->announcement_id) . "'";

            $result = Database::query($sql, __FILE__, __LINE__);

            // first delete all the information in item_property
            $sql = "DELETE FROM $t_item_propery WHERE tool='" . TOOL_ANNOUNCEMENT . "' AND ref='" . Database::escape_string($this->announcement_id) . "'";
            $result = Database::query($sql, __FILE__, __LINE__);

            // store in item_property (visibility, insert_date, target users/groups, visibility timewindow, ...)
            $this->store_item_property($this->send_receivers, $this->send_to, $this->announcement_id, 'AnnouncementEdited');
        } else {
            $result = Database::query("SELECT max(display_order) as max FROM {$this->tableAnnouncement}", __FILE__, __LINE__);
            $row = Database::fetch_array($result);
            $max = (int) $row['max'] + 1;

            // create the SQL statement to add the 
            $sql = "INSERT INTO {$this->tableAnnouncement} SET 
                    title           = '" . Database::escape_string($this->title) . "',
                    content	    = '" . Database::escape_string($this->description) . "',
                    end_date	    = '" . TimeZone::ConvertTimeFromUserToServer($user_id, time(),'Y-m-d H:i:s'). "',
                    display_order   = '" . $max . "',
                    email_sent      = 1,
                    scenario_filter = '" . Database::escape_string($this->scenario_filter) . "',
                    session_id		= $this->session_id ";
            
            $result = Database::query($sql, __FILE__, __LINE__);
            $lastInsertId = Database::insert_id();
            if (!empty($lastInsertId)) {                
                $this->store_item_property($this->send_receivers, $this->send_to, $lastInsertId, 'AnnouncementAdded');
            }
        }

        //$this->description = $this->replacePatternContent($this->description);

        $this->send_announcement_email($this->send_receivers, $this->send_to, $this->title, $this->description);
    }

    /**
     * This functions stores the note in the database
     * @return  int       Last insert id
     */
    public function store_item_property($send_receivers, $send_to, $id, $action_string) {
        $myId = api_get_user_id();
        if ($send_receivers == 0) {            
            api_item_property_update(api_get_course_info($this->course), TOOL_ANNOUNCEMENT, $id, $action_string, $this->user_id, '', '');
        }
        if ($send_receivers == 1) {
            $check1 = false;
            /*
            foreach ($send_to as $keycheck => $targetcheck) {
                if ($keycheck == $this->user_id) {
                    $check1 = true;
                }
            }
            */
            if (!$check1) {
                $send_to[] = 'U' . $this->user_id;
            }
            foreach ($send_to as $key => $target) {
                if (substr($target, 0, 1) == 'U') {
                    $user = substr($target, 1);
                    api_item_property_update(api_get_course_info($this->course), TOOL_ANNOUNCEMENT, $id, $action_string, $this->user_id, '', $user);
                }
                if (substr($target, 0, 1) == 'G') {
                    $group = substr($target, 1);
                    api_item_property_update(api_get_course_info($this->course), TOOL_ANNOUNCEMENT, $id, $action_string, $this->user_id, $group, '');
                }
            }
        }
        if ($send_receivers == 2) {

            $send_to_array = explode(",", $send_to);
            if (!in_array($this->user_id, $send_to_array)) {
                api_item_property_update(api_get_course_info($this->course), TOOL_ANNOUNCEMENT, $id, $action_string, $this->user_id, '99999', $this->user_id);
            }
            foreach ($send_to_array as $user_id) {
                api_item_property_update(api_get_course_info($this->course), TOOL_ANNOUNCEMENT, $id, $action_string, $this->user_id, '99999', $user_id);
            }
        }
        if ($send_receivers == '-1') {           
            // adding to everybody
            api_item_property_update(api_get_course_info($this->course), TOOL_ANNOUNCEMENT, $id, $action_string, $this->user_id, '', $myId);
            // making it invisible
            api_item_property_update(api_get_course_info($this->course), TOOL_ANNOUNCEMENT, $id, 'invisible');
        }
    }

    /**
     * Delete an announcement
     * @return  int     Affected rows
     */
    public function delete() {
        $affectedRow = 0;
        if ($this->announcement_id) {
            Database::query("DELETE FROM {$this->tableAnnouncement} WHERE id=" . intval($this->announcement_id));
            $affectedRow = Database::affected_rows();
            //update item_property (delete)
            api_item_property_update(api_get_course_info(), TOOL_ANNOUNCEMENT, $this->announcement_id, 'delete', $this->user_id);
        }

        return $affectedRow;
    }

    /**
     * Send announcement mail
     * @return  int     Affected rows
     */
    function send_announcement_email($send_receivers, $send_to, $title, $description, $data_file = '') {
        global $_user, $_course, $charset;

        $from_name = ucfirst($_user['firstname']) . ' ' . strtoupper($_user['lastname']);
        $from_email = $_user['mail'];
        $subject = $title;
        $message = $description;

        // create receivers array
        if ($send_receivers == 0) { // full list of users
            $receivers = CourseManager::get_user_list_from_course_code(api_get_course_id(), intval($_SESSION['id_session']) != 0, intval($_SESSION['id_session']));
        } elseif ($send_receivers == 1) {
            $users_ids = array();
            foreach ($send_to as $to) {
                if (strpos($to, 'G') === false) {
                    $users_ids[] = intval(substr($to, 1));
                } else {
                    $groupId = intval(substr($to, 1));
                    $users_ids = array_merge($users_ids, GroupManager::get_users($groupId));
                }
                $users_ids = array_unique($users_ids);
            }
            if (count($users_ids) > 0) {
                $sql = 'SELECT lastname, firstname, email, user_id 
						FROM ' . Database::get_main_table(TABLE_MAIN_USER) . '
						WHERE user_id IN (' . implode(',', $users_ids) . ')';
                $rsUsers = Database::query($sql, __FILE__, __LINE__);
                while ($userInfos = Database::fetch_array($rsUsers)) {
                    $receivers[] = $userInfos;
                }
            }
        } else if ($send_receivers == 2) {

            $users_ids = array();
            $users_ids = explode(",", $send_to);
            foreach ($users_ids as $user_id) {

                $sql = 'SELECT lastname, firstname, email, user_id 
						FROM ' . Database::get_main_table(TABLE_MAIN_USER) . '
						WHERE user_id = ' . $user_id;

                $rsUsers = Database::query($sql, __FILE__, __LINE__);
                while ($userInfos = Database::fetch_array($rsUsers)) {
                    $receivers[] = $userInfos;
                }
            }
        } elseif ($send_receivers == -1) {
            $receivers[] = array(
                'lastname' => $_user['lastName'],
                'firstname' => $_user['firstName'],
                'email' => $_user['mail'],
                'user_id' => $_user['user_id']
            );
        }

        $extra_headers = array('charset' => $charset);

        foreach ($receivers as $receiver) {
            $message = '';
            $to_name = ucfirst($receiver['firstname']) . ' ' . strtoupper($receiver['lastname']);
            $to_email = $receiver['email'];
            $message = $this->replacePatternContent($description, $receiver['user_id']);
            $message = str_replace('"/courses/', '"' . api_get_path(WEB_PATH) . 'courses/', $message);
            $message = "<div style='white-space: pre-wrap;	white-space: -moz-pre-wrap; 	white-space: -pre-wrap; 	white-space: -o-pre-wrap; 	word-wrap: break-word; 	word-break: break-word;'>" . $message . "</div>";
            api_mail_html($to_name, $to_email, $subject, $message, $from_name, $from_email, $extra_headers, $data_file);
        }
    }

    public function getExamPatterns() {
        $patters = array(
            array('name' => get_lang('StudentFirstNamePatternTitle'), 'token' => '{StudentFirstName}', 'description' => get_lang('StudentFirstNamePatternDescription'), 'type' => 'general'),
            array('name' => get_lang('StudentLastNamePatternTitle'), 'token' => '{StudentLastName}', 'description' => get_lang('StudentLastNamePatternDescription'), 'type' => 'general'),
            array('name' => get_lang('StudentFullNamePatternTitle'), 'token' => '{StudentFullName}', 'description' => get_lang('StudentFullNamePatternDescription'), 'type' => 'general'),
            array('name' => get_lang('TrainerFirstNamePatternTitle'), 'token' => '{TrainerFirstName}', 'description' => get_lang('TrainerFirstNamePatternDescription'), 'type' => 'general'),
            array('name' => get_lang('TrainerLastNamePatternTitle'), 'token' => '{TrainerLastName}', 'description' => get_lang('TrainerLastNamePatternDescription'), 'type' => 'general'),
            array('name' => get_lang('TrainerFullNamePatternTitle'), 'token' => '{TrainerFullName}', 'description' => get_lang('TrainerFullNamePatternDescription'), 'type' => 'general'),
            array('name' => get_lang('DatePatternTitle'), 'token' => '{Date}', 'description' => get_lang('DatePatternDescription'), 'type' => 'general'),
            array('name' => get_lang('SiteNamePatternTitle'), 'token' => '{SiteName}', 'description' => get_lang('SiteNamePatternDescription'), 'type' => 'general'),
            array('name' => get_lang('SiteUrlPatternTitle'), 'token' => '{SiteUrl}', 'description' => get_lang('SiteUrlPatternDescription'), 'type' => 'general'),
                //array('name'=> get_lang('AttachCertificatePatternTitle'), 'token'=>'{AttachedCertificate}', 'description'=>get_lang('AttachCertificatePatternDescription'), 'type'=>'feedback_email')            
        );
        return $patters;
    }

    /**
     * Replacement patterns in certificate content
     * @param     string  Certificate content
     * @return    string  The new certificate content
     */
    public function replacePatternContent($content, $user_id) {
        $tokens = $this->getPatternsTokenName();
        if (!empty($tokens)) {
            foreach ($tokens as $token) {
                if (strpos($content, $token) !== FALSE) {
                    $token_value = $this->getPatternTokenValue($token, $user_id);
                    $content = str_replace($token, $token_value, $content);
                }
            }
        }
        return $content;
    }

    /**
     * Get patterns tokens
     * @return    array   Pattern Tokens
     */
    public function getPatternsTokenName() {
        // get tokens                    
        $tokens = array();
        $patterns = $this->getExamPatterns();
        if (!empty($patterns)) {
            foreach ($patterns as $pattern) {
                $tokens[] = $pattern['token'];
            }
        }
        return $tokens;
    }

    /**
     * Get pattern values
     * @param     string      Pattern token (example: {FirstName})
     * @return    mixed       Pattern value
     */
    public function getPatternTokenValue($variable, $user_id) {
        $value = '';
        if (in_array($variable, $this->getPatternsTokenName())) {
            switch ($variable) {
                case '{StudentFirstName}':
                    $userInfo = api_get_user_info($user_id);
                    $value = $userInfo['firstname'];
                    break;
                case '{StudentLastName}':
                    $userInfo = api_get_user_info($user_id);
                    $value = $userInfo['lastname'];
                    break;
                case '{StudentFullName}':
                    $userInfo = api_get_user_info($user_id);
                    $value = api_get_person_name($userInfo['firstname'], $userInfo['lastname']);
                    break;
                case '{TrainerFirstName}':
                    $teachers = CourseManager::get_teacher_list_from_course_code(api_get_course_id());
                    $firstTeacher = array_shift($teachers);
                    $value = $firstTeacher['firstname'];
                    break;
                case '{TrainerLastName}':
                    $teachers = CourseManager::get_teacher_list_from_course_code(api_get_course_id());
                    $firstTeacher = array_shift($teachers);
                    $value = $firstTeacher['lastname'];
                    break;
                case '{TrainerFullName}':
                    $teachers = CourseManager::get_teacher_list_from_course_code(api_get_course_id());
                    $firstTeacher = array_shift($teachers);
                    $value = api_get_person_name($firstTeacher['firstname'], $firstTeacher['lastname']);
                    break;
                case '{Date}':
                    $value = date('d/m/Y');
                    break;
                case '{SiteName}':
                    $value = api_get_setting('siteName');
                    break;
                case '{SiteUrl}':
                    $value = api_get_path(WEB_PATH);
                    break;
            }
        }
        return $value;
    }

}

// end class
