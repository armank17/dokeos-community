<?php

 /**
  * Render HTML Code to display top header of the Author tool (copy of renderCourseHeader function)
  * This function is called in /main/inc/tool_header.inc.php
  * @param string - title of current page
  * @return string - HTML code
  *
  */
 function display_quiz_author_header(){
   global $_course,$charset;
   if($GLOBALS['learner_view']){
	$param = "?learner_view=true";
	}
	else {
		$param = '';
	}
   $TBL_EXERCICES = Database::get_course_table(TABLE_QUIZ_TEST, $db_name);
   if(isset($_REQUEST['exerciseId']))
   {
	   $exerciseId = intval($_REQUEST['exerciseId']);
   }
   elseif(isset($_GET['fromExercise']))
   {
	   $exerciseId = intval($_GET['fromExercise']);
   }
   $exerciseId = Security::remove_XSS($exerciseId);
   $sql = "SELECT title FROM $TBL_EXERCICES WHERE id = ".Database::escape_string($exerciseId);
   $result = Database::query($sql, __FILE__, __LINE__);

   while ($row = Database::fetch_array($result)) {
		$title = $row['title'];
	}
   $title = api_convert_encoding($title, $charset, api_get_system_encoding());

  // Html header for the Author tool
        if( HEADER_EXERCISE == 1){
 	$html =	"<div id='left'>" .										// home button
			"<a id=\"back2home3\" class='course_main_home_button' href=".api_get_path(WEB_COURSE_PATH).$_course['path'].'/index.php'.$param.">";
 	$html .= '<img src="'.api_get_path(WEB_IMG_PATH).'spacer.gif" width="42" height="37" onclick="window.parent.API.save_asset();" alt="'.$altHome.'" title="'.$altHome.'" /></a></div>';
 	$html.=	"<div id='courseTitle'>" .								// Title
 				"<div class='container'>".cut($title,30)."</div></div>".
// 				"/<div id='bg_end_title'></div>".
			"";
        }else{
            $html  = "<div id='leftQuizz'>";
            $html .= "<div id='welcome_ico' style=''>";										// home button
            $html .= "<a id=\"back2home2\" class='course_main_home_button' href=".api_get_path(WEB_COURSE_PATH).$_course['path'].'/index.php'.$param.">";
            $html .= '<img src="'.api_get_path(WEB_IMG_PATH).'spacer.gif" width="42" height="38" onclick="window.parent.API.save_asset();" alt="'.$altHome.'" title="'.$altHome.'" /><p>'.cut(strtoupper($_course['name']), 22).'</p>';
            $html .='</a>';
            $html .='</div>';
            $html .='</div>';                   
            $html.=	"<div id='rigthQuizz'><div id='quizzTitle1'><div class='title_quiz'>".cut($title, 50, true)."</div></div></div>";
            $html.=	"<div class='clear'></div>";
        }
 	
 	return $html;
 }
?>