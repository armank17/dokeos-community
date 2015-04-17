<?php

/**
 * controller for create new createAnnouncement
 * @author Johnny, <johnny1402@gmail.com>
 * @package notebook 
 */
class application_notebook_controllers_Index extends appcore_command_Command {

    private $theme = 'tools';
    private $userId;
    private $courseCode;
    private $sessionId;
    private $objCollection;
    public $css;
    public $notebook_id;

    public function __construct() {
        $this->verifySession();
        $this->setTheme($this->theme);
        $this->setLanguageFile(array('notebook'));
        $this->userId = api_get_user_id();
        $this->courseCode = api_get_course_id();
        $this->sessionId = api_get_session_id();
        $this->objCollection = new application_notebook_models_ModelCollection();
        $this->css = 'application/notebook/assets/css/style.css';
        $this->notebook_id = $this->getRequest()->getProperty('id', 0);
    }

    public function verifySession() {
        $objSecurity = new application_security_controllers_ValidateSession();
        $objSecurity->verifySession();
    }

    public function getAction() {
        $html = '<div class="actions">
                <a href="index.php?' . api_get_cidreq() . '&action=add">' . Display::return_icon('pixel.gif', $this->get_lang('NewNote'), array('class' => 'toolactionplaceholdericon tooladdnewnote')) . $this->get_lang('NewNote') . '</a>
            </div>';
        return $html;
    }

    public function getNotesList() {
        $resultValue = $this->objCollection->getNotesList($this->userId);
        return $resultValue;
    }

    public function addNotebook() {
        $this->disabledHeaderCore();
        $this->disabledFooterCore();
        $objNotebook = new stdClass();
        $objNotebook->user_id = $this->userId;
        $objNotebook->course = $this->courseCode;
        $objNotebook->session_id = $this->sessionId;
        $objNotebook->title = $this->getRequest()->getProperty('title');
        $objNotebook->description = $this->getRequest()->getProperty('description');
        $objNotebook->creation_date = date('Y-m-d H:i:s');
        $objNotebook->update_date = date('Y-m-d H:i:s');
        $objNotebook->status = 0;
        $this->objCollection->addNotebook($objNotebook);
        echo json_encode(array('action' => 1, 'message' => GLOSSARY_MESSAGE_UPDATE));
    }

    public function getForm() {
        global $charset;
        // initiate the object        
        $form = new FormValidator('note', 'post', 'index.php?module=notebook&' . api_get_cidreq() . ($this->notebook_id ? '&func=editNotebook&id=' . intval($this->notebook_id) : '&func=addNotebook'));

        // if ($this->notebook_id) {
        $form->addElement('hidden', 'notebook_id', $this->notebook_id);
        //}

        $form->add_textfield('title', '<div align="left" style="padding-left:15px;">' . $this->get_lang('Title') . '</div>', false, array('size' => '60', 'class' => 'focus', 'id' => 'note_title_id'));

        $form->add_html_editor('description', '', false, false, api_is_allowed_to_edit() ? array('ToolbarSet' => 'Notebook', 'Width' => '100%', 'Height' => '270', 'ID' => 'id_description') : array('ToolbarSet' => 'NotebookStudent', 'Width' => '100%', 'Height' => '270', 'UserStatus' => 'student', 'ID' => 'id_description'));

        if ($this->notebook_id > 0) {
            $form->addElement('html', '<div align="left" style="padding-left:10px;"><a href="' . api_get_self() . '?' . api_get_cidreq() . '&action=delete&id=' . intval($this->notebook_id) . '" onclick="javascript:if(!confirm(' . "'" . addslashes(api_htmlentities($this->get_lang("ConfirmYourChoice"), ENT_QUOTES, $charset)) . "'" . ')) return false;">' . Display::return_icon('pixel.gif', $this->get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')) . '&nbsp;&nbsp;' . $this->get_lang('Delete') . '</a></div>');
        }
        $form->addElement('style_submit_button', 'SubmitNote', $this->get_lang('Validate'), 'class="save"');

        // setting the defaults
        if ($this->notebook_id > 0) {
            $noteInfo = $this->getNoteInfo($this->notebook_id);
            $defaults['title'] = $noteInfo['title'];
            $defaults['description'] = $noteInfo['description'];
            $form->setDefaults($defaults);
        }

        $token = Security::get_token();
        $form->addElement('hidden', 'sec_token');
        $form->setConstants(array('sec_token' => $token));

        return $form;
    }

}