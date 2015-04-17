<?php
/* For licensing terms, see /license.txt */

/**
 * Notebook Model
 */
class NotebookModel 
{

    // definition tables
    protected $tableNotebook;    
    protected $attributes = Array(); 

    /**
     * Magic method 
     */
    public function __get($key){
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
        $this->tableNotebook = Database :: get_course_table(TABLE_NOTEBOOK, $courseDb);
    }        
        
    /**
     *  Get notes list
     *  @param      int     Optional, User id 
     *  @return`    array   Notes
     */
    public function getNotesList($userId = null) {
        $where = " WHERE 1=1";
        if (isset($userId)) {
            $where .= " AND user_id=".intval($userId);
        }        
        $notes = array();
        $rs = Database::query("SELECT notebook_id, title, date_format(creation_date,'%b %d') AS notesdate FROM {$this->tableNotebook} $where");        
        if (Database::num_rows($rs) > 0) {
            while ($row = Database::fetch_object($rs)) {
                $notes[$row->notebook_id] = $row;
            }
        }
        return $notes;        
    }
    
    public function getNotesListView($search=NULL) {               
        $notes = array();
        if($search == null){
            $rs = Database::query("SELECT notebook_id, title, description FROM {$this->tableNotebook}");
        }
        else {
       
            $rs = Database::query("SELECT notebook_id, title, description FROM {$this->tableNotebook} WHERE title LIKE '%".Database::escape_string($search)."%' OR description LIKE '%".Database::escape_string($search)."%'");
        }            
                
       // var_dump($rs);
        
        if (Database::num_rows($rs) > 0) {
            while ($row = Database::fetch_array($rs)) {
                $notes[] = $row;
            }
        }
        
        return $notes;
        
    }
    
    
    /**
     * Get information about a note
     * @param   int     Note id
     * @return  array   Note information 
     */
    public function getNoteInfo($notebookId) {
        $info = array();
        $rs = Database::query("SELECT notebook_id, title, description, date_format(creation_date,'%b %d') AS notesdate FROM {$this->tableNotebook} WHERE notebook_id='".intval($notebookId)."'");
        if (Database::num_rows($rs) > 0) {
            $info = Database::fetch_array($rs, 'ASSOC');
        }
        return $info;
    }    
    
    /**
     * Get note formulary
     * @return  object  Form object    
     */
    public function getForm() {                
        global $charset;
        // initiate the object        
	$form = new FormValidator('note', 'post', api_get_self().'?'.api_get_cidreq().($this->notebook_id?'&amp;action=edit&amp;id='.intval($this->notebook_id):'&amp;action=add'));	

        if ($this->notebook_id) {
            $form->addElement('hidden', 'notebook_id', $this->notebook_id);
        }

	$form->add_textfield('title','<div align="left" style="padding-left:15px;">'.get_lang('Title').'</div>',false,array('size'=>'60','class'=>'focus','id'=>'note_title_id'));	

	$form->add_html_editor('description','', false, false, api_is_allowed_to_edit()
		? array('ToolbarSet' => 'Notebook', 'Width' => '100%', 'Height' => '270')
		: array('ToolbarSet' => 'NotebookStudent', 'Width' => '100%', 'Height' => '270', 'UserStatus' => 'student'));

	if ($this->notebook_id) {
            $form->addElement('html','<div align="left" style="padding-left:10px;"><a href="'.api_get_self().'?'.api_get_cidreq().'&amp;action=delete&amp;id='.intval($this->notebook_id).'" onclick="javascript:if(!confirm('."'".addslashes(api_htmlentities(get_lang("ConfirmYourChoice"),ENT_QUOTES,$charset))."'".')) return false;">'.Display::return_icon('pixel.gif', get_lang('Delete'), array('class' => 'actionplaceholdericon actiondelete')).'&nbsp;&nbsp;'.get_lang('Delete').'</a></div>');
        }        
	$form->addElement('style_submit_button', 'SubmitNote', get_lang('Validate'), 'class="save"');
        
	// setting the defaults        
        if ($this->notebook_id) {
            $noteInfo = $this->getNoteInfo($this->notebook_id);            
            $defaults['title'] = $noteInfo['title'];
            if (strpos($noteInfo['description'], '../../courses/') !== FALSE) {
                $noteInfo['description'] = str_replace('../../courses/', '/courses/', $noteInfo['description']);
            }
            $defaults['description'] = $noteInfo['description'];
            $form->setDefaults($defaults);
        }

	$token = Security::get_token();
	$form->addElement('hidden','sec_token');
	$form->setConstants(array('sec_token' => $token));
        
	return $form;        
    }
    
    /**
     * This functions stores the note in the database
     * @return  int       Last insert id
     */
    public function save() {

            $lastInsertId = 0;
            if ($this->notebook_id) {
                // update
                Database::query("UPDATE {$this->tableNotebook} SET 
                                    user_id         = '".intval($this->user_id)."',
                                    course          = '".Database::escape_string($this->course)."',
                                    session_id      = '".intval($this->session_id)."',
                                    title           = '".Database::escape_string($this->title)."',
                                    description     = '".Database::escape_string($this->description)."',
                                    update_date     = '".date('Y-m-d H:i:s')."'
                                 WHERE notebook_id  = '".intval($this->notebook_id)."' 
                                ");
                $lastInsertId = $this->notebook_id;
                if (!empty($lastInsertId)) {
                    //insert into item_property
                    api_item_property_update(api_get_course_info($this->course), TOOL_NOTEBOOK, $lastInsertId, 'NotebookUpdated', $this->user_id);
                }
            }
            else {
                // insert
                Database::query("INSERT INTO {$this->tableNotebook} SET 
                                    user_id         = '".intval($this->user_id)."',
                                    course          = '".Database::escape_string($this->course)."',
                                    session_id      = '".intval($this->session_id)."',
                                    title           = '".Database::escape_string($this->title)."',
                                    description     = '".Database::escape_string($this->description)."',
                                    creation_date   = '".date('Y-m-d H:i:s')."',
                                    update_date     = '".date('Y-m-d H:i:s')."',
                                    status          = 0
                                ");
                $lastInsertId = Database::insert_id();
                if (!empty($lastInsertId)) {
                    //insert into item_property
                    api_item_property_update(api_get_course_info($this->course), TOOL_NOTEBOOK, $lastInsertId, 'NotebookAdded', $this->user_id);
                }                
            }
            return $lastInsertId;       
    }
    
    /**
     * Delete a note
     * @return  int     Affected rows
     */
    public function delete() {
        $affectedRow = 0;
        if ($this->notebook_id) {
            Database::query("DELETE FROM {$this->tableNotebook} WHERE notebook_id='".intval($this->notebook_id)."' AND user_id = '".intval($this->user_id)."'");
            $affectedRow = Database::affected_rows();
            //update item_property (delete)
            api_item_property_update(api_get_course_info(), TOOL_NOTEBOOK, $this->notebook_id, 'delete', $this->user_id);
        }
        return $affectedRow;
    }
                
} // end class

