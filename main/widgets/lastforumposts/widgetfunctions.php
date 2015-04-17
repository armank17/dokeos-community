<?php 
// including the widgets language file
$language_file = array ('widgets');

// include the global Dokeos file
include_once('../../inc/global.inc.php');
include_once('../../inc/lib/groupmanager.lib.php');
include_once('../../forum/forumfunction.inc.php');
include_once('../../forum/forumconfig.inc.php');

// load the specific widget settings
api_load_widget_settings();

//action handling
switch ($_POST['action']) {
	case 'get_widget_information':
		lastforumposts_get_information();
		break;
	case 'get_widget_content':
		lastforumposts_get_content();
		break;			
}
switch ($_GET['action']) {
	case 'get_widget_information':
		lastforumposts_get_information();
		break;
	case 'get_widget_content':
		lastforumposts_get_content();
		break;
	case 'get_widget_title':
		lastforumposts_get_title();
		break;				
}

/**
 * This function determines if the widget can be used inside a course, outside a course or both
 * 
 * @return array 
 * @version Dokeos 1.9
 * @since January 2010
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 */
function lastforumposts_get_scope(){
	return array('course');
}


function lastforumposts_get_content(){
	global $_course;

	// Database table definitions
	$table_categories 		= Database :: get_course_table(TABLE_FORUM_CATEGORY);
	$table_forums 			= Database :: get_course_table(TABLE_FORUM);
	$table_threads 			= Database :: get_course_table(TABLE_FORUM_THREAD);
	$table_posts 			= Database :: get_course_table(TABLE_FORUM_POST);	
	$table_item_property 	= Database :: get_course_table(TABLE_ITEM_PROPERTY);

	
	// how many posts should we select
	$settinglimit = api_get_setting('lastforumposts','numberofposts');
	if (!is_numeric($settinglimit) OR empty($settinglimit)){
		$numberofposts = 5;
	} else {
		$numberofposts = api_get_setting('lastforumposts','numberofposts');
	}
	
	// The sql statment: for course admins it does not matter because they can see everything
	if (api_is_allowed_to_edit()) {
		$sql = "SELECT * FROM $table_posts ORDER BY post_date DESC LIMIT 0,$numberofposts";
	} else {
		// students can however only see those messages where
		// 1. the forum category is visible
		// 2. the forum is visible
		// 3. the thread is visible
		// 4. the post is visible
		
		// get all the forum categories (get_forum_categories() only gets the visible categories for the user
		$forum_categories = get_forum_categories();
		
		// get all the forums  (get_forums() gets only the visible forums, but also the visible forums in an INvisible category)
		$forums = get_forums();
		
		// get all the groups the user is subscribed to
		$group_membership = GroupManager::get_group_ids($_course['dbName'],api_get_user_id());
		
		// gettting the forums that the user has access to. This means that
		// 1. the forum category must be visible
		// 2. the forum must be visible (this is already fulfilled since get_forums takes care of this)
		// 3. it is not a group forum
		// 4. If it is a group forum it must be a PUBLIC group forum (for everybody)
		// 5. if it is a PRIVATE group forum I must be a member of that group
		foreach ($forums as $forum_id=>$forum_info){
			//debugg($forum_info);
			// 1st condition: forum category must be visible
			if (array_key_exists($forum_info['forum_category'],$forum_categories)){
				// 3rd condition: not a group forum     									
				if ($forum_info['forum_of_group']=='0' 
						/* 4th condition: it is a group forum but it is a public forum		*/
						OR ($forum_info['forum_of_group']<>'0' AND $forum_info['forum_group_public_private'] == 'public') 
						/*5th condition: it is a PRIVATE group forum but I'm member of that group*/
						OR ($forum_info['forum_of_group']<>'0' AND $forum_info['forum_group_public_private'] == 'private' AND in_array($forum_info['forum_of_group'],$group_membership))
					){
					$visible_forum_ids[]=$forum_id;
				}
			}
		}

		
		// now we get all the posts of the $visible_forum_ids where both the thread and the post are visible
		if (is_array($visible_forum_ids)){
			$sql = "SELECT * FROM $table_posts posts , $table_threads threads, $table_item_property item_property
						WHERE posts.forum_id IN ('".implode("','",$visible_forum_ids)."')
						AND posts.visible = '1'
						AND posts.thread_id=threads.thread_id 
						AND threads.thread_id = item_property.ref
						AND item_property.tool = '".TOOL_FORUM_THREAD."'
						AND item_property.visibility = '1'
						ORDER BY posts.post_date DESC
						";
		}
	

		// 1. get all the visible forums in the visible forumcategories
		// 2. get all the visible threads
		// not group fora that are private
		// select * from post where post.visibility = 1 and foruid in (implode($visibileforums)) and thread in (implode(visiblethreads)
		// or we could do it negatively (because there are probably less invisible fora and posts than 
	}
	$result = Database::query($sql, __FILE__, __LINE__);
	
	echo '<ul id="widget_lastforumposts_posts">';
	while ($row = Database::fetch_array($result,'ASSOC')){
		if ($counter < $numberofposts) {
			echo '<li><a href="'.api_get_path(WEB_CODE_PATH).'forum/viewthread.php?forum='.$row['forum_id'].'&amp;thread='.$row['thread_id'].'">'.Display::return_icon('forumpost.gif').' '.$row['post_title'].'</a> <small>'.$row['post_date'].'</small></li>';
		} else {
			exit;
		}
		$counter++;
		
	}	
	echo '</ul>';
	
	
}

function lastforumposts_get_title($param1, $original_title=false) {
	$config_title = api_get_setting('lastforumposts', 'title');
	if (!empty($config_title) AND $original_title==false){
		return $config_title;
	} else {
		return get_lang('LastForumPosts');
	}
}

function lastforumposts_get_information(){
	echo '<span style="float:right;">';
	lastforumposts_get_screenshot();
	echo '</span>';		
	echo get_lang('LastForumPostsInformation');
}
function lastforumposts_get_screenshot(){
	echo '<img src="'.api_get_path(WEB_PATH).'main/course_home/widgets/lastforumposts/screenshot.jpg" alt="'.get_lang('WidgetScreenshot').'"/>';
}


function lastforumposts_settings_form(){
	$settinglimit = api_get_setting('lastforumposts','numberofposts');
	if (!is_numeric($settinglimit) OR empty($settinglimit)){
		$numberofposts = 5;
	} else {
		$numberofposts = api_get_setting('lastforumposts','numberofposts');
	}

	// the form to change the number of posts that have to be displayed	
	echo '<div class="widget_setting">';
	echo '<div  class="ui-corner-all widget_setting_subtitle">'.get_lang('NumberOfForumPostsToDisplay').'</div>';
	echo '<input type="text" name="widget_setting_numberofposts" id="widget_setting_numberofposts" value="'.$numberofposts.'" />';
	echo '</div>';	
}

?>
