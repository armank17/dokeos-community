<?php
/* For licensing terms, see /license.txt */

/**
 * Course Description Model
 */
class CourseDescriptionModel 
{

    // definition tables
    protected $tableCourseDescription;    
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
        $this->tableCourseDescription = Database :: get_course_table(TABLE_COURSE_DESCRIPTION, $courseDb);
    }        
        
    /**
     *  Get announcement list
     *  @param      int     Optional, User id 
     *  @return`    array   Announcements
     */
    public function getCourseDescriptionList() {
		$session_id = api_get_session_id();
		$condition_session = api_get_session_condition($session_id, false, true);

        $sql = "SELECT * FROM {$this->tableCourseDescription} $condition_session ORDER BY description_type ";
		$result = Database::query($sql, __FILE__, __LINE__);
		$descriptions = array();
		while ($description = Database::fetch_object($result)) {
				$descriptions[$description->description_type] = $description;
				//reload titles to ensure we have the last version (after edition)
				$default_description_titles[$description->description_type] = $description->title;
		}

        return $descriptions;        
    }  

	public function getDisplayActionIcons($default_description_titles,$default_description_class) {		
		$show_form = 0;
		
		if (isset($_REQUEST['description_id'])) {		
			$show_form = 1;
		}
		if (isset($_REQUEST['description_type'])) {		
			$show_form = 1;
		}
		if ($_REQUEST['showlist'] == 1) {
			$show_form = 0;
		}
				
		if (api_is_allowed_to_edit(null,true)) {
                    $categories = array ();

                    foreach ($default_description_titles as $id => $title) {						
                            $categories[$id] = $title;
                    }
                    $categories[ADD_BLOCK] = get_lang('NewBloc');

                    $i=1;                   
                    ksort($categories);
                    foreach ($categories as $id => $title) {
                        // We are displaying only 5 first items							
                        $action_icons .= '<a href="'.api_get_self().'?'.api_get_cidreq().'&amp;action=add&description_type='.$id.'">'.Display::return_icon('pixel.gif', $title, array('class' => 'toolactionplaceholdericon toolaction'.$default_description_class[$id])).' '.$title.'</a>';
                        $i++;                            
                    }
                }
                return $action_icons;
	}

	public function getForm($title) {
		$session_id = api_get_session_id();
		$condition_session = api_get_session_condition($session_id, false, true);
		
		if($this->description_type) {			
			$description = $this->getCourseDescriptionInfo($this->description_type);	
			$description_type = $this->description_type;
			if(!empty($description['title'])){				
				$default_description_titles[$description_type] = $description['title'];
				$description_content = $description['content'];
			}
			else {
				$default_description_titles[$description_type] = $title;
			}
		}
		else {
			
			$sql = "SELECT MAX(description_type) as MAX FROM {$this->tableCourseDescription} $condition_session";			
			$result = Database::query($sql, __FILE__, __LINE__);
			$max= Database::fetch_array($result);
			$description_type = $max['MAX']+1;
			if ($description_type < ADD_BLOCK) {
					$description_type=5;
			}	
		}

		$form = new FormValidator('course_description','POST','index.php?'.api_get_cidreq().'&amp;action=add&amp;showlist=1');
		$renderer = & $form->defaultRenderer();		
		$form->addElement('hidden', 'description_type');

		$action = $_REQUEST['action'];

		if ($action == 'edit' || intval($this->edit) == 1 ) {
			$form->addElement('hidden', 'edit','1');
		}

		if ($action == 'add' || intval($this->add) == 1 ) {
			$form->addElement('hidden', 'add','1');
		}

		if (($this->description_type >= ADD_BLOCK) || $default_description_title_editable[$this->description_type] || $action == 'add' || intval($this->edit) == 1 || !empty($title)) {
			$renderer->setElementTemplate('<div class="row"><div>'.get_lang('Title').' {element}</div></div>', 'title');
			$form->add_textfield('title', get_lang('Title'), true, array('size'=>'width: 350px;','class'=>'focus'));
                        $form->applyFilter('title','html_filter');
		}

		if (api_get_setting('wcag_anysurfer_public_pages')=='true') {
			WCAG_rendering::prepare_admin_form($description_content, $form);
		} else {
			$renderer->setElementTemplate('<div class="row"><div>{element}</div></div>', 'contentDescription');			
			$form->add_html_editor('contentDescription', get_lang('Content'), false, false, array('ToolbarSet' => 'TrainingDescription', 'Width' => '100%', 'Height' => '275', 'FullPage' => true));			
		}
		$form->addElement('html','<div class="pull-bottom">');
		$form->addElement('style_submit_button', null, get_lang('Save'), 'class="save"');
		$form->addElement('html','</div>');
		
		// Set some default values
		$default['title'] = $default_description_titles[$description_type];
                if (strpos($description_content, '../../courses/') !== FALSE) {
                    $description_content = str_replace('../../courses/', '/courses/', $description_content);
                }
		$default['contentDescription'] = $description_content;
		$default['description_id'] = $this->description_id;
		if($this->description_type) {
		$default['description_type'] = $this->description_type;
		}
		else {
			$default['description_type'] = $description_type;
		}
		
		$form->setDefaults($default);	
                $token = Security::get_token();
                $form->addElement('hidden','sec_token');
                $form->setConstants(array('sec_token' => $token));
		return $form;
	}

	public function getCourseDescriptionInfo($description_type) {
		$current_session_id = api_get_session_id();

		$sql = "SELECT * FROM {$this->tableCourseDescription} WHERE description_type='".Database::escape_string($description_type)."' AND session_id='".Database::escape_string($current_session_id)."'";		
		
		$result = Database::query($sql, __FILE__, __LINE__);	
		$row = Database::fetch_array($result);
		return $row;
	}

	public function save($default_description_titles,$default_description_title_editable) {
		$current_session_id = api_get_session_id();
		
		if ($description[$this->description_type] > ADD_BLOCK) {
			
			if ($this->add == '1') { //if this element has been submitted for addition				
				$result = Database::query($sql, __FILE__, __LINE__);
				$sql = "INSERT IGNORE INTO {$this->tableCourseDescription} SET description_type='".Database::escape_string($this->description_type)."', title = '".Database::escape_string(Security::remove_XSS($this->title,COURSEMANAGERLOWSECURITY))."', content = '".Database::escape_string(Security::remove_XSS($this->contentDescription,COURSEMANAGERLOWSECURITY))."', session_id = '".Database::escape_string($current_session_id)."' ";
				
				Database::query($sql, __FILE__, __LINE__);
			} else {
				$sql = "UPDATE {$this->tableCourseDescription} SET  title = '".Database::escape_string(Security::remove_XSS($this->title,COURSEMANAGERLOWSECURITY))."', content = '".Database::escape_string(Security::remove_XSS($this->contentDescription,COURSEMANAGERLOWSECURITY))."' WHERE description_type='".Database::escape_string($this->description_type)."' AND session_id = '".Database::escape_string($current_session_id)."'";
				
				Database::query($sql, __FILE__, __LINE__);
			}			
		}
		else {
			
			if (!$default_description_title_editable[$this->description_type]) {
				$title = $default_description_titles[$this->description_type];
			}
			$sql = "DELETE FROM {$this->tableCourseDescription} WHERE description_type = '".Database::escape_string($this->description_type)."' AND session_id = '".Database::escape_string($current_session_id)."'";
			Database::query($sql, __FILE__, __LINE__);
			
			$sql = "INSERT INTO {$this->tableCourseDescription} SET description_type = '".Database::escape_string($this->description_type)."', title = '".Database::escape_string(Security::remove_XSS($this->title,COURSEMANAGERLOWSECURITY))."', content = '".Database::escape_string(Security::remove_XSS($this->contentDescription,COURSEMANAGERLOWSECURITY))."', session_id = '".Database::escape_string($current_session_id)."'";
			Database::query($sql, __FILE__, __LINE__);
			
		}
		$id = Database::insert_id();
		if ($id > 0) {
			//insert into item_property
			api_item_property_update(api_get_course_info(), TOOL_COURSE_DESCRIPTION, $id, 'CourseDescriptionAdded', api_get_user_id());
		}
	}
	
	public function destroy() {
		$sql = "DELETE FROM {$this->tableCourseDescription} WHERE id='".Database::escape_string($this->description_id)."'";
		Database::query($sql, __FILE__, __LINE__);
		//update item_property (delete)
		api_item_property_update(api_get_course_info(), TOOL_COURSE_DESCRIPTION, $this->description_id, 'delete', api_get_user_id());
	}
	
                
} // end class