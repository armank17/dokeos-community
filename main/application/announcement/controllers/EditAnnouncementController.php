<?php

/**
 * controller for edit createAnnouncement
 * @author Johnny, <johnny1402@gmail.com>
 * @package announcement 
 */
class application_announcement_controllers_EditAnnouncement extends appcore_command_Command {
    
    private $objCollection;
    
    private $announcement_id;
    
    private $theme ='tools';
    
    private $userId;
    private $courseCode;
    private $session_id;
    
    private $objPaginator;
    
    public $css;
    
    public function __construct() {
        $this->setLanguageFile(array('announcements', 'group', 'survey'));
        $this->setTheme($this->theme);
        $this->objCollection = new application_announcement_models_collection();
        $this->objPaginator = new appcore_library_pagination_Paginator();
        $this->announcement_id = $this->getRequest()->getProperty('id');
        $this->css ='application/announcement/assets/css/style.css';
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
    
    /** 
        * This function gets all the groups and users (or combination of these) that can see this announcement
        * This is mainly used when editing
        */
    function get_announcement_dest($announcementId){
    $result = $this->objCollection->get_announcement_dest($announcementId);
    return $result;            

    }    
    
    public function getForm() {
        global $charset, $_course;

        //$this->announcement_id = $announcementId;
        // initiate the object        
        $form = new FormValidator('announcement_form', 'post', 'index.php?module=announcement&cmd=EditAnnouncement&' . api_get_cidreq() . ($this->announcement_id ? '&func=edit&id=' . intval($this->announcement_id) : '&func=addAnnouncement'));
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
                $defaults['title'] = sprintf($this->get_lang('RemindInactiveLearnersMailSubject'), api_get_setting('siteName'));
                $defaults['content'] = sprintf($this->get_lang('RemindInactiveLearnersMailContent'), api_get_setting('siteName'), 7);
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
                $defaults['title'] = sprintf($this->get_lang('RemindInactiveLearnersMailSubject'), api_get_setting('siteName'));
                $defaults['content'] = sprintf($this->get_lang('RemindInactiveLearnersMailContent'), api_get_setting('siteName'), $since);
            }
        }
        $form->setDefaults($defaults);

        $renderer->setElementTemplate('<div class="row"><div style="width:90%;float:left;padding-left:15px;">' . $this->get_lang('VisibleFor') . '&nbsp;&nbsp;{element}</div></div>', 'send_to');
        $form->addElement('receivers', 'send_to', $this->get_lang('VisibleFor'), array('receivers' => $receivers, 'receivers_selected' => ''));

        // The title
        $renderer->setElementTemplate('<div class="row"><div style="width:90%;float:left;padding-left:15px;"><!-- BEGIN error --><span class="form_error">{error}</span><br /><!-- END error -->' . $this->get_lang('Announcement') . '&nbsp;&nbsp;{element}</div></div>', 'title');

        $form->addElement('text', 'title', $this->get_lang('Announcement'), array('size' => '60', 'class' => 'focus', 'id' => 'announcement_title_id'));

        $form->add_html_editor('description', '', false, false, api_is_allowed_to_edit() ? array('ToolbarSet' => 'Announcements', 'Width' => '650px', 'Height' => '300', 'ID'=>'id_description') : array('ToolbarSet' => 'AnnouncementsStudent', 'Width' => '650px', 'Height' => '300', 'UserStatus' => 'student'));

        if ($this->announcement_id) {
            $form->addElement('html', '<div align="left" style="padding-left:10px;"><a href="index.php?module=announcement&' . api_get_cidreq() . '&cmd=DeleteAnnouncement&id=' . intval($this->announcement_id) . '" onclick="javascript:if(!confirm(' . "'" . addslashes(api_htmlentities($this->get_lang("ConfirmYourChoice"), ENT_QUOTES, $charset)) . "'" . ')) return false;">' . Display::return_icon('pixel.gif', $this->get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')) . '&nbsp;&nbsp;' . $this->get_lang('Delete') . '</a></div>');
        }
        $form->addElement('style_submit_button', 'SubmitAnnouncement', $this->get_lang('Validate'), 'class="save"');

        $token = Security::get_token();
        $form->addElement('hidden', 'sec_token');
        $form->setConstants(array('sec_token' => $token));

        return $form;
    }
    
    public function edit()
    {
        $check = Security::check_token(); 
        if ($check) {
            
            $objAnnouncementBean = new stdClass();
            $objAnnouncementBean->_id = $this->getRequest()->getProperty('announcement_id');
            $objAnnouncementBean->title = $this->getRequest()->getProperty('title');
            $objAnnouncementBean->content = $this->getRequest()->getProperty('description');
            $objAnnouncementBean->_user_id = api_get_user_id();
            $objAnnouncementBean->_course = api_get_course_id();
            $objAnnouncementBean->_session_id = api_get_session_id();
            $array_send_to = $this->getRequest()->getProperty('send_to');
            $objAnnouncementBean->_send_receivers = $array_send_to['receivers'];
            if(isset($array_send_to['to']))
                $objAnnouncementBean->_send_to = $array_send_to['to'];
            else
                $objAnnouncementBean->_send_to = 0;
            $objAnnouncementBean->_result = false;
            $objAnnouncementBean->_message = '';            
            $objAnnouncementBean = $this->objCollection->editAnnouncement($objAnnouncementBean);
            $this->getRequest()->deleteProperty('title');
            $this->getRequest()->deleteProperty('description');
            $this->getRequest()->deleteProperty('send_to');
            Security::clear_token();
            header('Location: ?module=announcement&cmd=CreateAnnouncement');                   
        }         
    }
}
