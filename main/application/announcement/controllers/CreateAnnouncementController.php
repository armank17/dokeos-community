<?php

/**
 * controller for create new createAnnouncement
 * @author Johnny, <johnny1402@gmail.com>
 * @package announcement 
 */
class application_announcement_controllers_CreateAnnouncement extends appcore_command_Command {

    private $theme = 'tools';
    public $css;
    public $announcementList;
    private $objCollection;
    private $announcement_id;
    private $userId;
    private $courseCode;
    private $session_id;
    public $pagerLinks;
    private $objPaginator;

    public function __construct() {
        $this->setTheme($this->theme);
        $this->setLanguageFile(array('announcements', 'group', 'survey'));
        $this->css = 'application/announcement/assets/css/style.css';
        $this->objCollection = new application_announcement_models_collection();
        $this->objPaginator = new appcore_library_pagination_Paginator();
        $this->userId = api_get_user_id();
        $this->courseCode = api_get_course_id();
        $this->session_id = api_get_session_id();
    }

    public function getAction() {
        $html = '<div class="actions">';
        if (api_is_allowed_to_edit()) {
            $html.='<a href="index.php?module=announcement&' . api_get_cidreq() . '&cmd=CreateAnnouncement">' . Display::return_icon('pixel.gif', $this->get_lang('AddAnnouncement'), array('class' => 'toolactionplaceholdericon toolactionannoucement')) . $this->get_lang('AddAnnouncement') . '</a>';
        }
        $html.='</div>';
        return $html;
    }

    public function getAnnouncementList() {
        $result = $this->objCollection->getAnnouncementList();
        $this->generatePaginator($result);
        $this->pagerLinks = $this->objPaginator->links();
        return $result;
    }
    
    public function generatePaginator($Collecttion)
    {
        if($Collecttion->count() > 0)
        {
            $array_announcement = array();
            foreach($Collecttion->getIterator() as $objAnnouncement)
            {
                array_push($array_announcement, $objAnnouncement);
            }
            
            $resultCollection = $this->objPaginator->generate($array_announcement);
            $resultCollection = $this->objCollection->exchangeArray($resultCollection);
            return $resultCollection;
        }
    }

    public function getAnnouncementInfo($announcementId) {
        $result = $this->objCollection->getAnnouncementInfo($announcementId);
        return $result;
    }

    public function addAnnouncement() {
            $check = Security::check_token();
            if ($check) 
                {
                $objAnnouncement = new stdClass();
                //$objAnnouncement->id = NULL;
                $objAnnouncement->title = $this->getRequest()->getProperty('title');
                $objAnnouncement->content = $this->getRequest()->getProperty('description');
                $objAnnouncement->end_date = date("Y-m-d");
                $objAnnouncement->display_order = 0;
                $objAnnouncement->email_sent = 1;
                $objAnnouncement->session_id = $this->session_id;
                
                $objAnnouncement->_user_id = $this->userId;
                $objAnnouncement->_course = $this->courseCode;
                $array_send_to = $this->getRequest()->getProperty('send_to');
                $objAnnouncement->_send_receivers = $array_send_to['receivers'];
                if(isset($array_send_to['to']))
                    $objAnnouncement->_send_to = $array_send_to['to'];
                else
                    $objAnnouncement->_send_to = 0;
                $objAnnouncement->_result = false;
                $objAnnouncement->_message = '';
                $this->objCollection->addAnnouncement($objAnnouncement);

                $this->getRequest()->deleteProperty('title');
                $this->getRequest()->deleteProperty('description');
                $this->getRequest()->deleteProperty('send_to');
                Security::clear_token();
                header('Location: ?module=announcement&cmd=CreateAnnouncement');
            }
    }

    public function getForm($announcementId = null) {
        global $charset, $_course;

        $this->announcement_id = $announcementId;
        // initiate the object        
        $form = new FormValidator('announcement_form', 'post', 'index.php?module=announcement&cmd=CreateAnnouncement&' . api_get_cidreq() . ($this->announcement_id ? '&func=EditAnnouncement&id=' . intval($this->announcement_id) : '&func=addAnnouncement'));
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
            $course_users = CourseManager::get_user_list_from_course_code(api_get_course_id(), intval($this->session_id) == 0, intval($this->session_id));
            foreach ($course_users as $key => $user) {
                $receivers ['U' . $key] = $user ['lastname'] . ' ' . $user ['firstname'];
            }
        }
        $defaults ['send_to'] ['receivers'] = 0;
        if ($this->announcement_id) {
            $announcementInfo = $this->getAnnouncementInfo($this->announcement_id);
            if ($announcementInfo->count() > 0) {
                foreach ($announcementInfo->getIterator() as $objInfo)
                    $objAnnouncementInfo = $objInfo;
            }
            $defaults['title'] = $objAnnouncementInfo->announcement_title; //$announcementInfo['announcement_title'];
            $defaults['description'] = $objAnnouncementInfo->announcement_content; //$announcementInfo['announcement_content'];

            /* $announcementInfo['to_user_id'] */
            if (!empty($objAnnouncementInfo->to_user_id)) {
                $defaults['send_to'] ['receivers'] = 1;
                $user_group_ids = $this->get_announcement_dest($this->announcement_id);
            } else if (empty($objAnnouncementInfo->to_user_id) && $objAnnouncementInfo->visibility == 0) {
                $defaults['send_to'] ['receivers'] = -1;
            } else {
                $defaults['send_to'] ['receivers'] = 0;
            }

            foreach ($user_group_ids['to_user_id'] as $key => $user_id) {
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
        $form->addElement('receivers', 'send_to', get_lang('VisibleFor'), array('receivers' => $receivers, 'receivers_selected' => ''));

        // The title
        $renderer->setElementTemplate('<div class="row"><div style="width:90%;float:left;padding-left:15px;"><!-- BEGIN error --><span class="form_error">{error}</span><br /><!-- END error -->' . get_lang('Announcement') . '&nbsp;&nbsp;{element}</div></div>', 'title');

        $form->addElement('text', 'title', get_lang('Announcement'), array('size' => '60', 'class' => 'focus', 'id' => 'announcement_title_id'));

        $form->add_html_editor('description', '', false, false, api_is_allowed_to_edit() ? array('ToolbarSet' => 'Announcements', 'Width' => '650px', 'Height' => '300', 'ID'=>'id_description') : array('ToolbarSet' => 'AnnouncementsStudent', 'Width' => '650px', 'Height' => '300', 'UserStatus' => 'student', 'ID'=>'id_description'));

        if ($this->announcement_id) {
            $form->addElement('html', '<div align="left" style="padding-left:10px;"><a href="' . api_get_self() . '?' . api_get_cidreq() . '&action=delete&id=' . intval($this->id) . '" onclick="javascript:if(!confirm(' . "'" . addslashes(api_htmlentities(get_lang("ConfirmYourChoice"), ENT_QUOTES, $charset)) . "'" . ')) return false;">' . Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')) . '&nbsp;&nbsp;' . get_lang('Delete') . '</a></div>');
        }
        $form->addElement('style_submit_button', 'SubmitAnnouncement', get_lang('Validate'), 'class="save"');

        $token = Security::get_token();
        $form->addElement('hidden', 'sec_token');
        $form->setConstants(array('sec_token' => $token));

        return $form;
    }

    public function vd($var) {
        echo"<pre>";
        print_r($var);
        echo"</pre>";
    }

}

?>
