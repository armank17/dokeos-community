<?php // $Id: $
/* See license terms in /dokeos_license.txt */
/**
==============================================================================
* Update the Dokeos database from an older version
* Notice : This script has to be included by index.php or update_courses.php
*
* @package dokeos.install
* @todo
* - conditional changing of tables. Currently we execute for example
* ALTER TABLE `$dbNameForm`.`cours` instructions without checking wether this is necessary.
* - reorganise code into functions
* @todo use database library
==============================================================================
*/


//load helper functions
require_once("install_upgrade.lib.php");
require_once('../inc/lib/image.lib.php');
require_once('../inc/lib/menulinkmanager.lib.php');
require_once('../inc/lib/templatemanager.lib.php');
$old_file_version = '2.2';
$new_file_version = '3.0';

$error_file = "../install/logs/upgrade-$old_file_version-$new_file_version.sql_errors";
$file_header = '-------------';
$file_header .= "Dokeos upgrade from version $old_file_version to version $new_file_version\n";
$file_header .= "Upgrade made on ".date('l jS \of F Y h:i:s A')."\n";
$file_header .= "-------------\n";

$f = fopen($error_file, 'w');
fwrite($f,$file_header);
fclose($f);

//remove memory and time limits as much as possible as this might be a long process...
if(function_exists('ini_set')) {
    ini_set('memory_limit',-1);
    ini_set('max_execution_time',0);
} else{
    error_log('Update-db script: could not change memory and time limits',0);
}

/*
==============================================================================
		MAIN CODE
==============================================================================
*/

//check if we come from index.php or update_courses.php - otherwise display error msg
if (defined('DOKEOS_INSTALL') || defined('DOKEOS_COURSE_UPDATE')) {
	//check if the current Dokeos install is elligible for update
	if (!file_exists('../inc/conf/configuration.php')) {
		echo '<b>'.get_lang('Error').' !</b> Dokeos '.implode('|', $updateFromVersion).' '.get_lang('HasNotBeenFound').'.<br><br>
                        '.get_lang('PleasGoBackToStep1').'.
                    <p><button type="submit" class="back" name="step1" value="&lt; '.get_lang('Back').'">'.get_lang('Back').'</button></p>
                    </td></tr></table></form></body></html>';
		exit ();
	}

	//get_config_param() comes from install_functions.inc.php and
	//actually gets the param from
	$_configuration['db_glue'] = get_config_param('dbGlu');

	if ($singleDbForm) {
            $_configuration['table_prefix'] = get_config_param('courseTablePrefix');
            $_configuration['main_database'] = get_config_param('mainDbName');
            $_configuration['db_prefix'] = get_config_param('dbNamePrefix');
	}

	$dbScormForm = eregi_replace('[^a-z0-9_-]', '', $dbScormForm);

	if (! empty ($dbPrefixForm) && !ereg('^'.$dbPrefixForm, $dbScormForm)) {
            $dbScormForm = $dbPrefixForm.$dbScormForm;
	}

	if (empty ($dbScormForm) || $dbScormForm == 'mysql' || $dbScormForm == $dbPrefixForm) {
            $dbScormForm = $dbPrefixForm.'scorm';
	}
	$res = @mysql_connect($dbHostForm, $dbUsernameForm, $dbPassForm);

	//if error on connection to the database, show error and exit
	if ($res === false) {
            echo					get_lang('DBServerDoesntWorkOrLoginPassIsWrong').'.<br /><br />' .
                            '				'.get_lang('PleaseCheckTheseValues').' :<br /><br />
                                                        <b>'.get_lang('DBHost').'</b> : '.$dbHostForm.'<br />
                                                            <b>'.get_lang('DBLogin').'</b> : '.$dbUsernameForm.'<br />
                                                            <b>'.get_lang('DBPassword').'</b> : '.$dbPassForm.'<br /><br />
                                                            '.get_lang('PleaseGoBackToStep').' '. (defined('DOKEOS_INSTALL') ? '3' : '1').'.
                                                        <p><button type="submit" class="back" name="step'. (defined('DOKEOS_INSTALL') ? '3' : '1').'" value="&lt; '.get_lang('Back').'">'.get_lang('Back').'</button></p>
                                                        </td></tr></table></form></body></html>';

            exit ();
	}

	// The Dokeos system has not been designed to use special SQL modes that were introduced since MySQL 5
	@mysql_query("set session sql_mode='';");
	$dblistres = mysql_list_dbs();
	$dblist = array();
	while ($row = mysql_fetch_object($dblistres)) {
            $dblist[] = $row->Database;
	}
	/*
	-----------------------------------------------------------
		Normal upgrade procedure:
		start by updating main, statistic, user databases
	-----------------------------------------------------------
	*/
	//if this script has been included by index.php, not update_courses.php, so
	// that we want to change the main databases as well...
	$only_test = false;
	$log = 0;
	if (defined('DOKEOS_INSTALL')) {
		if ($singleDbForm) {
                    $dbStatsForm = $dbNameForm;
                    $dbScormForm = $dbNameForm;
                    $dbUserForm = $dbNameForm;
		}
		/**
		 * Update the databases "pre" migration
		 */
		include ("../lang/english/create_course.inc.php");
		if ($languageForm != 'english') {
                    //languageForm has been escaped in index.php
                    include ("../lang/$languageForm/create_course.inc.php");
		}

		//get the main queries list (m_q_list)
		$m_q_list = get_sql_file_contents('migrate-db-'.$old_file_version.'-'.$new_file_version.'-pre.sql','main');
		if (count($m_q_list)>0) {
                    //now use the $m_q_list
                    /**
                     * We connect to the right DB first to make sure we can use the queries
                     * without a database name
                     */
                    if (strlen($dbNameForm)>40) {
                        error_log('Database name '.$dbNameForm.' is too long, skipping',0);
                    } elseif(!in_array($dbNameForm,$dblist)) {
                        error_log('Database '.$dbNameForm.' was not found, skipping',0);
                    } else {
                        mysql_select_db($dbNameForm);
                        foreach ($m_q_list as $query) {
                            if ( strlen(trim($query)) != 0 ) {
                                if ($only_test) {
                                    error_log("mysql_query($dbNameForm,$query)",0);
                                } else {
                                    $res = mysql_query($query);
                                    if (mysql_errno()) {}
                                    if($log) {
                                        error_log("In $dbNameForm, executed: $query",0);
                                    }
                                }
                            }
                        }
                    }
		}

		//get the stats queries list (s_q_list)
		$s_q_list = get_sql_file_contents('migrate-db-'.$old_file_version.'-'.$new_file_version.'-pre.sql','stats');
		if (count($s_q_list)>0) {
                    //now use the $s_q_list
                    /**
                     * We connect to the right DB first to make sure we can use the queries
                     * without a database name
                     */
                    if (strlen($dbStatsForm)>40) {
                        error_log('Database name '.$dbStatsForm.' is too long, skipping',0);
                    } elseif(!in_array($dbStatsForm,$dblist)) {
                        error_log('Database '.$dbStatsForm.' was not found, skipping',0);
                    } else {
                        mysql_select_db($dbStatsForm);
                        foreach ($s_q_list as $query) {
                          if ( strlen(trim($query)) != 0) {
                            if ($only_test) {
                              error_log("mysql_query($dbStatsForm,$query)",0);
                            } else {
                              $res = mysql_query($query);
                              if (mysql_errno()) {
                                    //write_error($error_file,'MysqlError : '.mysql_errno().' : '.mysql_error());
                                    //write_error($error_file,"DB : $dbStatsForm | Request : $query\n"); 
                              }
                              if ($log) {
                                error_log("In $dbStatsForm, executed: $query",0);
                              }
                            }
                          }
                        }
                    }
		}
		//get the user queries list (u_q_list)
		$u_q_list = get_sql_file_contents('migrate-db-'.$old_file_version.'-'.$new_file_version.'-pre.sql','user');
		if (count($u_q_list)>0) {
			//now use the $u_q_list
			/**
			 * We connect to the right DB first to make sure we can use the queries
			 * without a database name
			 */
			if (strlen($dbUserForm)>40) {
                            error_log('Database name '.$dbUserForm.' is too long, skipping',0);
			} elseif (!in_array($dbUserForm,$dblist)) {
                            error_log('Database '.$dbUserForm.' was not found, skipping',0);
			} else {
                            mysql_select_db($dbUserForm);
                            foreach ($u_q_list as $query) {
                              if ( strlen(trim($query)) == false ) {
                                if ($only_test) {
                                  error_log("mysql_query($dbUserForm,$query)",0);
                                  error_log("In $dbUserForm, executed: $query",0);
                                } else {
                                  $res = mysql_query($query);
                                  if (mysql_errno()) {
                                        //write_error($error_file,'MysqlError : '.mysql_errno().' : '.mysql_error());
                                        //write_error($error_file,"DB : $dbUserForm | Request : $query\n"); 
                                  }
                                }
                              }
                            }
                        }
		}
		//the SCORM database doesn't need a change in the pre-migrate part - ignore
	}


	/*
	-----------------------------------------------------------
		Update the Dokeos course databases
		this part can be accessed in two ways:
		- from the normal upgrade process
		- from the script update_courses.php,
		which is used to upgrade more than MAX_COURSE_TRANSFER courses

		Every time this script is accessed, only
		MAX_COURSE_TRANSFER courses are upgraded.
	-----------------------------------------------------------
	*/

	$prefix = '';
	if ($singleDbForm) {
            $prefix =  get_config_param('table_prefix');
	}

	//get the courses databases queries list (c_q_list)
	$c_q_list = get_sql_file_contents('migrate-db-'.$old_file_version.'-'.$new_file_version.'-pre.sql','course');
	if (count($c_q_list)>0) {
		//get the courses list
		if (strlen($dbNameForm)>40) { error_log('Database name '.$dbNameForm.' is too long, skipping',0); }
		elseif (!in_array($dbNameForm,$dblist)) { error_log('Database '.$dbNameForm.' was not found, skipping',0); }
		else {
			mysql_select_db($dbNameForm); 

			$res = mysql_query("SELECT code,db_name,directory,course_language FROM course WHERE target_course_code IS NULL ORDER BY code");

			if ($res===false) { die('Error while querying the courses list in update_db.inc.php'); }

			if (mysql_num_rows($res)>0) {
                            $i=0;
                            $list = array();                            
                            while ($row = mysql_fetch_array($res)) {
                                $list[] = $row;
                                $i++;
                            }
                            foreach ($list as $row_course) {
                                $tbl_prefix = '';
                                //now use the $c_q_list
                                /**
                                 * We connect to the right DB first to make sure we can use the queries
                                 * without a database name
                                 */
                                if (!$singleDbForm) { //otherwise just use the main one
                                    mysql_select_db($row_course['db_name']);
                                }
                                else {                                    
                                    $tbl_prefix = $prefix.$row_course['db_name'].'_';
                                }

                                foreach ($c_q_list as $query) {
                                    if (strlen(trim($query)) != 0 ) {
                                        if ($singleDbForm) { //otherwise just use the main one
                                          $query = preg_replace('/^(UPDATE|ALTER TABLE|CREATE TABLE|DROP TABLE|INSERT INTO|DELETE FROM)\s+(\w*)(.*)$/',"$1 $prefix{$row_course['db_name']}_$2$3",$query);
                                        }
                                        if ($only_test) {
                                            error_log("mysql_query(".$row_course['db_name'].",$query)",0);
                                        }
                                        else {
                                            $res = mysql_query($query);
                                            if (mysql_errno()) {}
                                            if ($log) {
                                              error_log("In ".$row_course['db_name'].", executed: $query",0);
                                            }
                                        }

                                    }					
                                }
                                
                                /* Here you put the updates for courses */
                                $table_tool   = $tbl_prefix.'tool';
                                // We get first all tools without session
                                $rs_tools = Database::query("SELECT * FROM $table_tool WHERE session_id = 0");
								error_log("SELECT * FROM $table_tool WHERE session_id = 0");
                                $tools = array();
                                if (Database::num_rows($rs_tools) > 0) {
                                    while ($row = Database::fetch_array($rs_tools, 'ASSOC')) {
                                        $tools[] = $row;
                                    }
                                }
                                
                                // check if a tool exists in the session
                                $rs_session = Database::query("SELECT id_session FROM ".$_configuration['main_database'].".session_rel_course WHERE course_code = '{$row_course['code']}'");
                                error_log("SELECT id_session FROM ".$_configuration['main_database'].".session_rel_course WHERE course_code = '{$row_course['code']}'");
								if (Database::num_rows($rs_session) > 0) {
                                    while ($row_session = Database::fetch_object($rs_session)) {
                                        Database::query("DELETE FROM $table_tool WHERE session_id = {$row_session->id_session}");
                                        if (!empty($tools)) {
                                            for ($x = 0; $x < count($tools); $x++) {
                                                unset($tools[$x]['id']);
                                                $insert = "INSERT INTO $table_tool SET ";
                                                $tools[$x]['session_id'] = $row_session->id_session;
                                                foreach ($tools[$x] as $field => $value) {                            
                                                    $insert .= " $field = '$value',";                            
                                                }
                                                $insert = substr_replace($insert, '', strrpos($insert, ','));
                                                Database::query($insert);
                                            }
                                        }
                                    }
                                }
                              
                                
                            }
			}
		}
	}
        
        
        
        
        
        /***
         * 
         * 
         * Import nodes
         *
         *
         ***/
        
        error_log('Starting multisite migration');
        
        //tables
        $tbl_node          = Database::get_main_table(TABLE_MAIN_NODE);
        $tbl_node_homepage = Database::get_main_table(TABLE_MAIN_NODE_HOMEPAGE);
        $tbl_node_news     = Database::get_main_table(TABLE_MAIN_NODE_NEWS);
        $tbl_menulinks     = Database::get_main_table(TABLE_MAIN_MENU_LINK);
        $tbl_sysannouncements = Database::get_main_table(TABLE_MAIN_SYSTEM_ANNOUNCEMENTS);
        $tbl_access_url    = Database::get_main_table(TABLE_MAIN_ACCESS_URL);
        $table_user_field  = Database::get_main_table(TABLE_MAIN_USER_FIELD);
        
        
        
        // Max link menu id
        $sql = "SELECT (MAX(id)) as max_id FROM $tbl_menulinks;";
        $rs  = Database::query($sql);
        $row = Database::fetch_array($rs, 'ASSOC');
        $menulink_max_id = $row['max_id'];
        
        
        $directory_root_home = ( api_get_path(SYS_CODE_PATH).'../home/' );   
        $languages           = api_get_languages();
        $node_id             = 0;                                                  
        $weight              = 0;
        
        // Get sites
        $sql     = "SELECT * FROM $tbl_access_url;";
        $rs_site = Database::query($sql);
        $sites   = array();
        
        if( Database::num_rows($rs_site) > 1 ){
            // fetching sites
            while($site = Database::fetch_array($rs_site, 'ASSOC')){
                preg_match('/https?:\/\/(.+)?\//', $site['url'], $matches);
                //error_log("$matches[1] \n");
                //error_log("$directory_root_home.$matches[1]");
                if(is_dir($directory_root_home.$matches[1])){
                    $site['path'] = $directory_root_home.$matches[1].'/';
                    //error_log("{$site['path']} \n");
                } else {
                    $site['path'] = $directory_root_home.'-'.$matches[1].'/';
                    //error_log("{$site['path']} \n");
                }
                $sites[] = $site;
            }
        } else {
            $sites[] = array('id' => 1, 'path' => $directory_root_home);
        }
        
        // 
        foreach($sites as $site){          
            $access_url_id  = $site['id'];
            $directory_home = $site['path'];
            $home_pages_languages = array();
            
            if(is_dir($directory_home)){
                //error_log("$directory_home: is directory home \n");
                // languages
                foreach ($languages['folder'] as $language){
                    $language_id = api_get_language_id($language);


                    // Home Pages ----------------------------------------------
                    if (version_compare($_configuration['dokeos_version'], '2.0', '<')){
                        $file = $directory_home.'home_top_'.$language.'.html';
                    } else {
                        $file = $directory_home.'home_tabs_'.$language.'.html';
                        if( (!is_file($file) || (is_file($file) && (filesize($file) <= 0))) ){
                            $file = $directory_home.'home_top_'.$language.'.html';
                        }
                    }
                    
                    //file_put_contents($error_file, "\r\n".$file, FILE_APPEND); // < log                
                    if(is_file($file)){             
                        //error_log("$file: is file \n");
                        $title         = 'Home'; //$language;
                        $content       = Database::escape_string(file_get_contents($file));

                        $node_id++;
                        $sql = 'INSERT INTO '. $tbl_node .' (id, title, content, access_url_id, created_by, modified_by, creation_date, modification_date, active, language_id, enabled, node_type, display_title) VALUES ('. $node_id .', \''. $title .'\', \''. $content .'\', '. $access_url_id .', 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 1, '. $language_id .', 1, '. NODE_HOMEPAGE .', 0);';
                        //file_put_contents($error_file, "\r\n".$sql, FILE_APPEND); // < log
                        Database::query($sql);

                        //$node_id = Database::insert_id();                           
                        $sql = 'INSERT INTO '. $tbl_node_homepage .' (node_id, promoted) VALUES ('. $node_id .',  1);';
                        //file_put_contents($error_file, "\r\n".$sql, FILE_APPEND); // < log
                        Database::query($sql);
                        
                        $home_pages_languages[] = $language_id;
                    }
                    // ---------------------------------------------------------


                    // Portal Pages --------------------------------------------
                    $file = $directory_home.'home_menu_'.$language.'.html';
                    if(is_file($file) && filesize($file) > 0){
                        $home_menu = file($file);
                        
                        if (version_compare($_configuration['dokeos_version'], '2.0', '<')){
                                
                                foreach($home_menu as $value){
                                    if(preg_match('/<li><a href="(.+)?" target="(.+)?">(.+)?<\/a><\/li>/', $value, $matches)){
                                        $url     = Database::escape_string(trim($matches[1]));
                                        $target  = Database::escape_string(trim($matches[2]));
                                        $title   = Database::escape_string(trim($matches[3]));                                        
                                        
                                        $menulink_max_id++;
                                        $weight++;
                                        $sql = 'INSERT INTO '. $tbl_menulinks .' (id, parent_id, language_id, weight, title, link_path, description, access_url_id, category, target, link_type, enabled, created_by, creation_date, modified_by, modification_date, active, visibility) VALUES('. $menulink_max_id .', 0, '. $language_id .', '. $weight .', \''. $title .'\', \''. $url .'\', \''. $title .'\', '. $access_url_id .', \''. MENULINK_CATEGORY_LEFTSIDE .'\', \''. $target .'\', \'link\', 1, 1, CURRENT_TIMESTAMP, 1, CURRENT_TIMESTAMP, 1, 15);';
                                        Database::query($sql);
                                    }
                                }
                            
                        } else { // dokeos version >= 2.0

                                foreach($home_menu as $value){
                                    if(preg_match('/<div><a href=".+include=(.+?)" target="(.+?)">(.+?)<\/a><\/div>/', $value, $matches)){
                                        $content_file = $directory_home.$matches[1];
                                        if(!is_file($content_file)) { // fix dokeos 2.1 migration
                                            $content_file = $directory_root_home.$matches[1];
                                        }                                        
                                        
                                        if(is_file($content_file)){
                                            $matches[4] = file_get_contents($content_file);

                                            $target     = Database::escape_string(trim($matches[2]));
                                            $title      = Database::escape_string(trim($matches[3]));
                                            $content    = Database::escape_string(trim($matches[4]));
                                            $node_id++;

                                            $sql = 'INSERT INTO '. $tbl_node .' (id, title, content, access_url_id, created_by, modified_by, creation_date, modification_date, active, language_id, enabled, node_type) VALUES ('.$node_id.', \''. $title .'\', \''. $content .'\', '. $access_url_id .', 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 1, '. $language_id .', 1, 2);';
                                            Database::query($sql);

                                            //$node_id = Database::insert_id();
                                            $sql = 'INSERT INTO  '. $tbl_node_homepage .' (node_id, promoted) VALUES ('.$node_id.', 0);';
                                            Database::query($sql);

                                            $menulink_max_id++;
                                            $weight++;
                                            $url = Database::escape_string( '/index.php?action=show&nodeId='.$node_id );

                                            $sql = 'INSERT INTO '. $tbl_menulinks .' (id, parent_id, language_id, weight, title, link_path, description, access_url_id, category, target, link_type, enabled, created_by, creation_date, modified_by, modification_date, active, visibility) VALUES('. $menulink_max_id .', 0, '. $language_id .', '. $weight .', \''. $title .'\', \''. $url .'\', \''. $title .'\', '. $access_url_id .', \''. MENULINK_CATEGORY_LEFTSIDE .'\', \''. $target .'\', \'node\', 1, 1, CURRENT_TIMESTAMP, 1, CURRENT_TIMESTAMP, 1, 15);';
                                            Database::query($sql);

                                            $sql = 'UPDATE  '. $tbl_node .'  SET menu_link_id='. $menulink_max_id .' WHERE id='.$node_id.';';
                                            Database::query($sql);
                                        }
                                    } elseif ( preg_match('/<div><a href="(.+)?" target="(.+)?">(.+)?<\/a><\/div>/', $value, $matches) ){
                                        $url     = Database::escape_string(trim($matches[1]));
                                        $target  = Database::escape_string(trim($matches[2]));
                                        $title   = Database::escape_string(trim($matches[3]));                                        
                                        
                                        $menulink_max_id++;
                                        $weight++;
                                        $sql = 'INSERT INTO '. $tbl_menulinks .' (id, parent_id, language_id, weight, title, link_path, description, access_url_id, category, target, link_type, enabled, created_by, creation_date, modified_by, modification_date, active, visibility) VALUES('. $menulink_max_id .', 0, '. $language_id .', '. $weight .', \''. $title .'\', \''. $url .'\', \''. $title .'\', '. $access_url_id .', \''. MENULINK_CATEGORY_LEFTSIDE .'\', \''. $target .'\', \'link\', 1, 1, CURRENT_TIMESTAMP, 1, CURRENT_TIMESTAMP, 1, 15);';
                                        Database::query($sql);
                                    }
                                }
                            
                        }
                    }
                    // ---------------------------------------------------------

                    
                    // Notice --------------------------------------------------
                    $file = $directory_home.'home_notice_'.$language.'.html';
                    //file_put_contents($error_file, "\r\n".$file, FILE_APPEND); // < log
                    if(is_file($file) && filesize($file) > 0){
                        $content = file_get_contents($file);
                        
                        if(preg_match_all('/<b>(.+?)<\/b><br \/>\n((?:.|\n)+)/', $content, $matches)){
                            $title   = Database::escape_string($matches[1][0]);
                            $content = Database::escape_string($matches[2][0]);
                            $node_id++;

                            $sql = 'INSERT INTO '. $tbl_node .' (id, title, content, access_url_id, created_by, modified_by, creation_date, modification_date, active, language_id, enabled, node_type) VALUES ('. $node_id .', \''. $title .'\', \''. $content .'\', '. $access_url_id .', 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 1, '. $language_id .', 1, '. NODE_NOTICE .');';
                            //file_put_contents($error_file, "\r\n".$sql, FILE_APPEND); // < log
                            Database::query($sql);
                        }
                        
                    }
                    
                } //foreach ($languages['folder'] as $language)
            }
            
            //error_log("$file: is file \n");
            
            // Add Menulinks
            $menulink_max_id = MenuLinkManager::addMenuLinksToAccessUrlId($access_url_id);  
            //$node_id         = TemplateManager::addTemplatesToAccessUrlId($access_url_id, $home_pages_languages, TemplateManager::OPERATION_EXCLUDE_LANGUAGES);  // Add Default HomePages

            //if($site['id'] == 1) {
                // News
                $sql = "SELECT * FROM $tbl_sysannouncements";
                $rs = Database::query($sql);
                while($row = Database::fetch_array($rs, 'ASSOC')){
                    $title       = Database::escape_string($row['title']);
                    $content     = Database::escape_string($row['content']);
                    $language_id = api_get_language_id($row['lang']);
                    if($language_id == NULL)
                        $language_id = 0;
                    
                    $node_id++;

                    $sql = 'INSERT INTO '. $tbl_node .' (id, title, content, access_url_id, created_by, modified_by, creation_date, modification_date, active, language_id, enabled, node_type) VALUES ('. $node_id .', \''. $title .'\', \''. $content .'\', '. $access_url_id .', 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 1, '. $language_id .', 1, '. NODE_NEWS .');';
                    //file_put_contents($error_file, "\r\n".$sql, FILE_APPEND); // < log
                    Database::query($sql);

                    //$node_id = Database::insert_id();
                    $sql = 'INSERT INTO '. $tbl_node_news .' (node_id, start_date, end_date, visible_by_trainer, visible_by_learner, visible_by_guest) VALUES ('. $node_id .', \''. $row['date_start'] .'\',\''. $row['date_end'] .'\','. $row['visible_teacher'] .','. $row['visible_student'] .','. $row['visible_guest'] .');';
                    //file_put_contents($error_file, "\r\n".$sql, FILE_APPEND); // < log
                    Database::query($sql);
                }
            /*} else {
                //
            }*/

            
            if($access_url_id > 1){
                // add user fields
                $sql = "INSERT INTO $table_user_field (
                            field_type,
                            field_variable,
                            field_display_text,
                            field_default_value,
                            field_order,
                            field_visible,
                            field_changeable,
                            field_filter,
                            tms,
                            field_registration,
                            access_url_id
                          )
                          SELECT
                            field_type,
                            field_variable,
                            field_display_text,
                            field_default_value,
                            field_order,
                            field_visible,
                            field_changeable,
                            field_filter,
                            tms,
                            field_registration,
                            {$access_url_id}
                          FROM $table_user_field
                          WHERE access_url_id = 1
                          ORDER BY field_order";
                Database::query($sql);
            }
                
        } //foreach($sites as $site)
        
        error_log('Ending multisite migration');

}
else
{
	echo 'You are not allowed here !';
}