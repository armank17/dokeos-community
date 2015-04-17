<?php
//Dokeos upgrade script for quiz template question in old courses.

// include the Dokeos Global File
$language_file = array('exercice','create_course');
include('../inc/global.inc.php');
include_once(api_get_path(LIBRARY_PATH).'add_course.lib.inc.php');

// Display the header
Display::display_header();

// making the connection to the MySQL server;
$conn=mysql_connect($_configuration['db_host'],$_configuration['db_user'], $_configuration['db_password'])	or die("Cannot connect. " . mysql_error());
// connect to the Dokeos Main database
mysql_select_db($_configuration['main_database']) or die("Cannot select table.". mysql_error());

// Displaying the title
echo "<h3>Update All Course Databases for Quiz Feedback by answers / Feedback by question</h3>";

if ($_GET["action"]!=="go")
{
echo "<p><b>This file will update the course database with quiz template questions.</b><br>";

echo "<p>If you are sure that you want to do add quiz template questions, click <a href='$PHP_SELF?action=go'>continue ></a>";
}

if ($_GET["action"]=="go")
{
	
	// selecting all the databases for the courses
	$sql = "SELECT db_name, directory FROM course";
	$result = mysql_query($sql);
	$aantaldatabases=mysql_num_rows($result);

	// Putting all the databases in the array $database[]
	while ($myrow = mysql_fetch_array($result))
	{
		$database[]=$myrow["db_name"]."@".$myrow["directory"];
		
	}	

	//We execute each course-SQL statement for the database
	foreach ($database as $key=>$db)
	{		
		// displaying the title of the database
		echo "<b>".$db."</b><br>";	
		$db_directory = explode("@",$db);
		echo 'db==='.$db_directory[0];
		echo 'dir==='.$db_directory[1];
		mysql_select_db($db_directory[0]) or print("<br><font color=red><b>Cannot select the database ". mysql_error()."</b></font>");	

		/*echo $sql = "ALTER TABLE `".$db_directory[0]."`.`quiz` ADD COLUMN quiz_final_feedback text default NULL";
		Database::query($sql, __FILE__, __LINE__);

		echo $sql = "ALTER TABLE `".$db_directory[0]."`.`quiz_scenario` ADD COLUMN quiz_final_feedback text default NULL";
		Database::query($sql, __FILE__, __LINE__);*/

		/*echo $sql = "INSERT INTO `".$db_directory[0]."`.`tool_intro` (id, intro_text) VALUES('active_scenario','dynamic')";
		Database::query($sql, __FILE__, __LINE__);*/

		echo $sql = "ALTER TABLE `".$db_directory[0]."`.`scenario_steps` ADD COLUMN hide_image int(11) NOT NULL default 0 after hide_border";
		$res = Database::query($sql, __FILE__, __LINE__);
		if(!$res) echo mysql_error();

		/*$sql = "
		CREATE TABLE `".$db_directory[0]."`.`course_scenario` (
		id int NOT NULL auto_increment,
		name varchar(45),		
		display_order mediumint NOT NULL default 0,	
		PRIMARY KEY (id)
		) ";
		echo $sql;
		$res = Database::query($sql, __FILE__, __LINE__);
		if(!$res) echo mysql_error();*/

		/*$sql = "
		CREATE TABLE `".$db_directory[0]."`.`scenario_steps` (
		id int NOT NULL auto_increment,
		step_icon varchar(255) NOT NULL,	
		step_name varchar(255) NOT NULL,	
		step_border varchar(10) NOT NULL,	
		step_prerequisite varchar(255) NOT NULL,	
		step_completion_option varchar(15) NOT NULL,	
		step_completion_percent varchar(5) NOT NULL DEFAULT '0',
		step_created_order int(11) NOT NULL,
		step_created_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		step_visibility int(11) NOT NULL DEFAULT 1,
		session_id int(11) NOT NULL default 0,	
		PRIMARY KEY (id)
		) ";
		echo $sql;
		$res = Database::query($sql, __FILE__, __LINE__);
		if(!$res) echo mysql_error();

		$sql = "
		CREATE TABLE `".$db_directory[0]."`.`scenario_activity` (
		id int NOT NULL auto_increment,
		step_id int(11) NOT NULL,	
		activity_type varchar(255),	
		activity_ref int(11) NOT NULL,	
		activity_name varchar(255),	
		activity_created_order int(11),	
		activity_created_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		activity_visibility int(11) NOT NULL DEFAULT 1,		
		session_id int(11) NOT NULL default 0,	
		PRIMARY KEY (id)
		) ";
		echo $sql;
		$res = Database::query($sql, __FILE__, __LINE__);
		if(!$res) echo mysql_error();

		$sql = "
		CREATE TABLE `".$db_directory[0]."`.`scenario_activity_view` (
		id int NOT NULL auto_increment,
		activity_id int(11) NOT NULL,	
		step_id int(11) NOT NULL,	
		user_id int(11) NOT NULL,	
		view_count int(11) NOT NULL,	
		score float NOT NULL,	
		status text NOT NULL,
		view_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,		
		PRIMARY KEY (id)
		) ";
		echo $sql;
		$res = Database::query($sql, __FILE__, __LINE__);
		if(!$res) echo mysql_error();

		$sql = "
		CREATE TABLE `".$db_directory[0]."`.`face_to_face` (
		id int(11) NOT NULL AUTO_INCREMENT,			
		name varchar(255) NOT NULL,	
		max_score float,	
		step_id int(11) NOT NULL,				
		session_id int(11) NOT NULL default 0,	   		
		access_url_id int(11) NOT NULL default 0,			
		PRIMARY KEY (id)
		) ";
		echo $sql;
		$res = Database::query($sql, __FILE__, __LINE__);
		if(!$res) echo mysql_error();*/

		echo "</hr>";
	}

	
	/*echo 'course path=='.$course_dir = api_get_path(SYS_COURSE_PATH).'MONDAY/work/';
	if(is_dir($course_dir)){
		echo 'dir exists';

		$list_dir = scandir($course_dir);
		foreach($list_dir as $dir) {
			echo 'dir==='.$dirname = $dir;
			echo '<br/>';
			echo 'tmp====='.$tmp_dirname = str_replace("DELETED_","",$dirname);
			rename($course_dir.$dir, $course_dir.$tmp_dirname);
		}
	}
	else {
		echo 'no dirrrrr';
	}*/
	
}
// Display the footer
Display::display_footer();
?>