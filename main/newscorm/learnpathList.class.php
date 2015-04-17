<?php
/* For licensing terms, see /dokeos_license.txt */

/**
* 	Learning Path
*	File containing the declaration of the learnpathList class
*	@package dokeos.learnpath
*	@author	Yannick Warnier
*/

/**
 * This class is only a learning path list container with several practical methods for sorting the list and
 * provide links to specific paths
 * @uses	Database.lib.php to use the database
 * @uses	learnpath.class.php to generate learnpath objects to get in the list
 */
class learnpathList {
	var $list = array(); //holds a flat list of learnpaths data from the database
	var $ref_list = array(); //holds a list of references to the learnpaths objects (only filled by get_refs())
	var $alpha_list = array(); //holds a flat list of learnpaths sorted by alphabetical name order
	var $course_code;
	var $user_id;
	var $refs_active = false;

	/**
	 * This method is the constructor for the learnpathList. It gets a list of available learning paths from
	 * the database and creates the learnpath objects. This list depends on the user that is connected
	 * (only displays) items if he has enough permissions to view them.
	 * @param	integer		User ID
	 * @param	string		Optional course code (otherwise we use api_get_course_id())
	 * @return	void
	 */
    function learnpathList($user_id,$course_code='') {
    	if(!empty($course_code)){
    		//proceed with course code given
    	}else{
		$course_code = api_get_course_id();
		$lp_table = Database::get_course_table(TABLE_LP_MAIN);
    	}
    	$this->course_code = $course_code;
    	$this->user_id = $user_id;

    	//condition for the session
       $session_id = api_get_session_id();
       $condition_session = api_get_session_condition($session_id, false, true);
       $sql_modules_id = "";
       if(api_is_user_ecommerce($user_id)) {
         $modules_id = $this->get_list_ids_modules_ecommerce($user_id, $course_code);
         if(!empty($modules_id)) {
            $sql_modules_id = " AND id IN (".  implode(',', $modules_id).")";
         }
       }
       
       $sql = "SELECT * FROM $lp_table $condition_session $sql_modules_id ORDER BY display_order ASC, id ASC, name ASC";
       
    	$res = Database::query($sql);
    	$names = array();
    	while ($row = Database::fetch_array($res))
    	{
    		//check if published
    		$pub = '';
    		$tbl_tool = Database::get_course_table(TABLE_TOOL_LIST);
    		//use domesticate here instead of Database::escape_string because
    		//it prevents ' to be slashed and the input (done by learnpath.class.php::toggle_visibility())
    		//is done using domesticate()
    		$myname = domesticate($row['name']);
			$mylink = 'newscorm/lp_controller.php?action=view&amp;lp_id='.$row['id'];
			$sql2="SELECT * FROM $tbl_tool where (name='$myname' and image='scormbuilder.gif' and link LIKE '$mylink%')";
			//error_log('New LP - learnpathList::learnpathList - getting visibility - '.$sql2,0);
			$res2 = Database::query($sql2,__FILE__,__LINE__);
			if(Database::num_rows($res2)>0){
				$row2 = Database::fetch_array($res2);
				$pub = $row2['visibility'];
			}else{
				$pub = 'i';
			}
			//check if visible
			$vis = api_get_item_visibility(api_get_course_info($course_code),'learnpath',$row['id']);

    		$this->list[$row['id']] = array(
    			'lp_type' => $row['lp_type'],
    			'lp_session' => $row['session_id'],
    			'lp_name' => stripslashes($row['name']),
    			'lp_desc' => stripslashes($row['description']),
    			'lp_path' => $row['path'],
    			'lp_view_mode' => $row['default_view_mod'],
    			'lp_force_commit' => $row['force_commit'],
    			'lp_maker'	=> stripslashes($row['content_maker']),
    			'lp_proximity' => $row['content_local'],
    			'lp_encoding' => $row['default_encoding'],
    			'lp_visibility' => $vis,
    			'lp_published'	=> $pub,
    			'lp_prevent_reinit' => $row['prevent_reinit'],
    			'lp_scorm_debug' => $row['debug'],
    			'lp_display_order' => $row['display_order'],
    			'lp_preview_image' => stripslashes($row['preview_image']),
                        'origin_tool' => $row['origin_tool']
    			);
    		$names[$row['name']]=$row['id'];
       	}
       	$this->alpha_list = asort($names);
    }
    
    function get_list_ids_modules_ecommerce($user_id,$course_code){
       $tblEcommercUserPrivileges = Database::get_main_table( TABLE_MAIN_ECOMMERCE_USER_PRIVILEGES );
       $tblEcommerItems = Database::get_main_table( TABLE_MAIN_ECOMMERCE_ITEMS );
       $tblEcommerLpModules = Database::get_main_table( TABLE_MAIN_ECOMMERCE_LP_MODULE_PACKS );
       $sql = "SELECT lp_module_lp_module_id FROM $tblEcommercUserPrivileges ep
       INNER JOIN $tblEcommerItems ei ON ep.ecommerce_items_id = ei.id 
       INNER JOIN $tblEcommerLpModules em ON ep.ecommerce_items_id = em.ecommerce_items_id
       WHERE user_id=".$user_id." AND item_type=3 AND em.lp_module_course_code='$course_code'";
       $res = Database::query($sql, __FILE__, __LINE__);
       $data = array();
       while($row = Database::fetch_array($res)){
          $data[] = $row['lp_module_lp_module_id'];
       }
       return $data;
    }
    /**
     * Gets references to learnpaths for all learnpaths IDs kept in the local list.
     * This applies a transformation internally on list and ref_list and returns a copy of the refs list
     * @return	array	List of references to learnpath objects
     */
    function get_refs(){
    	foreach($this->list as $id => $dummy)
    	{
    		$this->ref_list[$id] = new learnpath($this->course_code,$id,$this->user_id);
    	}
    	$this->refs_active = true;
    	return $this->ref_list;
    }
    /**
     * Gets a table of the different learnpaths we have at the moment
     * @return	array	Learnpath info as [lp_id] => ([lp_type]=> ..., [lp_name]=>...,[lp_desc]=>...,[lp_path]=>...)
     */
    function get_flat_list()
    {
		return $this->list;
    }
}
?>
