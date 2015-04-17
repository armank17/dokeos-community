<?php // $Id: activity.php,v 1.5 2006/08/10 14:34:54 pcool Exp $

/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) Dokeos SPRL

	For a full list of contributors, see "credits.txt".
	For licensing terms, see "dokeos_license.txt"

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	See the GNU General Public License for more details.

	http://www.dokeos.com
==============================================================================
*/

/**
==============================================================================
*         HOME PAGE FOR EACH COURSE
*
*	This page, included in every course's index.php is the home
*	page. To make administration simple, the teacher edits his
*	course from the home page. Only the login detects that the
*	visitor is allowed to activate, deactivate home page links,
*	access to the teachers tools (statistics, edit forums...).
*
*	@package dokeos.course_home
==============================================================================
*/

// header
$GLOBALS['display_learner_view'] = api_get_setting('student_view_enabled') === 'true';
Display::display_header($course_title, "Home");


//statistics
if (!isset($coursesAlreadyVisited[$_cid])) {
	event_access_course();
	$coursesAlreadyVisited[$_cid] = 1;
	api_session_register('coursesAlreadyVisited');
}

// database table definition
$tool_table = Database::get_course_table(TABLE_TOOL_LIST);

$temps = time();
$reqdate = "&reqdate=$temps";


//display course title for course home page (similar to toolname for tool pages)
//echo '<h3>'.api_display_tool_title($nameTools) . '</h3>';

// introduction section
Display::display_introduction_section(TOOL_COURSE_HOMEPAGE, array(
		'CreateDocumentWebDir' => api_get_path('WEB_COURSE_PATH').api_get_course_path().'/document/',
		'CreateDocumentDir' => 'document/',
		'BaseHref' => api_get_path('WEB_COURSE_PATH').api_get_course_path().'/'
	)
);


// action handling
	if(api_is_allowed_to_edit(null,true)) {
 	// make the tool visible
		if(!empty($_GET['hide'])) // visibility 1 -> 0
		{
		change_tool_visibility($_GET['id'],0);			
			Display::display_confirmation_message(get_lang('ToolIsNowHidden'));
		}
	// make the tool invisible
		elseif(!empty($_GET['restore'])) // visibility 0,2 -> 1
		{
		change_tool_visibility($_GET['id'],1);
			Display::display_confirmation_message(get_lang('ToolIsNowVisible'));
		}
	}


if (api_is_platform_admin()) {
	// Show message to confirm that a tools must be hidden from available tools
	// visibility 0,1->2
	if (!empty($_GET['askDelete'])) {
			echo '<div id="toolhide">';
			echo get_lang("DelLk");
			echo '<br />&nbsp;&nbsp;&nbsp;';
			echo '<a href="'.api_get_self().'">'.get_lang('No').'</a>&nbsp;|&nbsp;';
			echo '<a href="'.api_get_self().'?delete=yes&amp;id='.Security::remove_XSS($_GET['id']).'">'.get_lang('Yes').'</a>';
			echo '</div>';
	}
	// Delete a link. Note: this is different than in the other views! In the other views the visibility is set to 2
	elseif (isset($_GET[delete]) && $_GET[delete]) {
		Database::query("DELETE FROM $tool_table WHERE id='".Database::escape_string(intval($id))."' AND added_tool=1",__FILE__,__LINE__);
	}
}




/*
-----------------------------------------------------------
	Tools for course admin only
-----------------------------------------------------------
*/
if(api_is_allowed_to_edit(null,true) && !api_is_course_coach()) {

    $current_protocol  = $_SERVER['SERVER_PROTOCOL'];
    $current_host      = $_SERVER['HTTP_HOST'];
    $server_protocol = substr($current_protocol,0,strrpos($current_protocol,'/'));
    $server_protocol = $server_protocol.'://';
    if ($current_host == 'localhost') {
      //Get information of path
      $info = explode('courses',api_get_self());
      $path_work = substr($info[0], 0, strlen($info[0]));
    } else {
      $path_work = "";
    }

?>
	<div class="courseadminview" style="border:0px; margin-top: 0px;padding:5px 0px;">
		<?php			
                Display::display_normal_message(get_lang('PleaseStandBy'), false,true, true);
                Display::display_confirmation_message(get_lang('ConfirmationMessage'), false,true, true);
		?>
	</div>
	
	<?php
	if (api_get_setting('show_session_data') === 'true' && $id_session > 0) {
	?>
	<div class="section main_activity">
		<span class="sectiontitle"><?php echo get_lang("SessionData") ?></span>
		<table>
			<?php echo show_session_data($id_session);?>
		</table>
	</div>
	<?php
	}
	?>
        <?php if (api_get_setting('enable_pro_settings') !== "true"): ?>
	<div id="pro_tools" class="section main_activity">
            <span class="sectiontitle"><?php echo get_lang("Production") ?></span>
            <?php show_pro_tools(); ?>
	</div>
        <?php endif; ?>
	<div class="section main_activity">
		<span class="sectiontitle"><?php echo get_lang("Authoring") ?></span>
		<table>
			<?php $my_list = get_tools_category(TOOL_AUTHORING); show_tools_category($my_list);?>
		</table>
	</div>
	<div class="section main_activity">
		<span class="sectiontitle"><?php echo get_lang("Interaction") ?></span>
		<table>
			<?php $my_list = get_tools_category(TOOL_INTERACTION); show_tools_category($my_list);?>
		</table>
 	</div>
	<div class="section main_activity">
		<span class="sectiontitle"><?php echo get_lang("Administration") ?></span>
		<table>
			<?php $my_list = get_tools_category(TOOL_ADMIN_PLATEFORM); show_tools_category($my_list);?>
		</table>
	</div>

	<?php
} elseif (api_is_course_coach()) {

	if (api_get_setting('show_session_data') === 'true' && $id_session > 0) {
	?>
		<div class="section main_activity">
			<span class="sectiontitle"><?php echo get_lang("SessionData") ?></span>
			<table>
				<?php echo show_session_data($id_session);?>
			</table>
		</div>
	<?php
	}
	?>
		<div class="section main_activity">
			<table>
				<?php $my_list = get_tools_category(TOOL_STUDENT_VIEW); show_tools_category($my_list);?>
			</table>
		</div>
	<?php

/*
==============================================================================
		TOOLS AUTHORING
==============================================================================
*/
} else {
    $my_list = get_tools_category(TOOL_STUDENT_VIEW);
    if (count($my_list)>0) {
?>
	<div class="section main_activity activity_student_view">
		<table>
			<?php show_tools_category($my_list);?>
		</table>
	</div>
<?php
    }
}
