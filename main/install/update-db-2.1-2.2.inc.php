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
$old_file_version = '2.1';
$new_file_version = '2.2';

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
		if (strlen($dbNameForm)>40) {
                    error_log('Database name '.$dbNameForm.' is too long, skipping',0);
		}
		elseif(!in_array($dbNameForm,$dblist)) {
                    error_log('Database '.$dbNameForm.' was not found, skipping',0);
		}
		else {
			mysql_select_db($dbNameForm); 

		   $check_quiz_question_tpl = Database::query("SELECT * FROM quiz_question_templates");
		   if(Database::num_rows($check_quiz_question_tpl) <> 0){
				Database::query("DELETE * FROM quiz_question_templates");
				Database::query("DELETE * FROM quiz_answer_templates");
		   }

		   add_quiz_question_templates2();

			$res = mysql_query("SELECT code,db_name,directory,course_language FROM course WHERE target_course_code IS NULL ORDER BY code");

			if($res===false){die('Error while querying the courses list in update_db.inc.php');}

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
                                
                                // give a negative weighting to old multiple answers
                                $table_questions = $tbl_prefix.'quiz_question';
                                $table_answers   = $tbl_prefix.'quiz_answer';   
                                $sql = 'SELECT * FROM '.$table_questions.' WHERE type = 2';
                                $rsQuestions = Database::query($sql, __FILE__, __LINE__);
                                while ($question = Database::fetch_array($rsQuestions)) {
                                        $sql = 'SELECT max(ponderation) FROM '.$table_answers.' WHERE question_id = '.$question['id'];
                                        $rsMax = Database::query($sql, __FILE__, __LINE__);
                                        $max = Database::result($rsMax, 0, 0);
                                        $sql = 'UPDATE '.$table_answers.' 
                                                        SET ponderation = '.(-$max).' 
                                                        WHERE question_id = '.$question['id'].'
                                                        AND correct = 0'; // weighting ?
                                        Database::query($sql, __FILE__, __LINE__);
                                }
                                
                                
                                
                            }
			}
		}
	}
}
else
{
	echo 'You are not allowed here !';
}

function add_quiz_question_templates2(){
	

        $check1 = Database::query("SELECT id FROM quiz_question_templates WHERE question = 'DefaultQuizQuestion1'");
        if (Database::num_rows($check1) == 0) {
            $html_img1 = "<table cellspacing=\"2\" cellpadding=\"0\" width=\"98%\" height=\"100%\" style=\"font-family: Comic Sans MS; font-size: 16px;\"><tbody>
            <tr><td align=\"center\" height=\"323px\"><img border=\"0\" align=\"absmiddle\" width=\"350\" vspace=\"0\" hspace=\"0\" height=\"328\" alt=\"Price_elasticity_of_demand2.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/Price_elasticity_of_demand2.png'."\" /></td></tr></tbody></table>";
            //Question and answer set 1
            Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
            ('DefaultQuizQuestion1', '".$html_img1."', 20.00, 1, 1, '', 1)", __FILE__, __LINE__);
            $question_id = Database::insert_id();

            Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
            (1, ".$question_id.", 'QuizAnswer_1a', 0, 'Feedback_qn1_true', 0.00, 1, '', '', '0@@0@@0@@0'),
            (2, ".$question_id.", 'QuizAnswer_1b', 1, 'Feedback_qn1_true', 20.00, 2, '', '', '0@@0@@0@@0')", __FILE__, __LINE__);	
        }
        
        $check2 = Database::query("SELECT id FROM quiz_question_templates WHERE question = 'DefaultQuizQuestion2'");
        if (Database::num_rows($check2) == 0) {
            $html_img2 = "<table cellspacing=\"2\" cellpadding=\"0\" width=\"98%\" height=\"100%\" style=\"font-family: Comic Sans MS; font-size: 16px;\"><tbody>
            <tr><td align=\"center\" height=\"323px\"><img border=\"0\" align=\"absmiddle\" width=\"310\" vspace=\"0\" hspace=\"0\" height=\"310\" alt=\"Cross_elasticity_of_demand_complements.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/Cross_elasticity_of_demand_complements.png'."\" /></td>
            </tr></tbody></table>";	

            //Question and answer set 2
            Database::query("INSERT INTO  quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
            ('DefaultQuizQuestion2', '".$html_img2."', 20.00, 2, 1, '', 1)", __FILE__, __LINE__);
            $question_id = Database::insert_id();

            Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
            (4, ".$question_id.", 'QuizAnswer_2d', 0, 'Feedback_qn2_true', 0.00, 4, '', '', '0@@0@@0@@0'),
            (3, ".$question_id.", 'QuizAnswer_2c', 0, 'Feedback_qn2_true', 0.00, 3, '', '', '0@@0@@0@@0'),
            (2, ".$question_id.", 'QuizAnswer_2b', 1, 'Feedback_qn2_true', 20.00, 2, '', '', '0@@0@@0@@0'),
            (1, ".$question_id.", 'QuizAnswer_2a', 0, 'Feedback_qn2_true', 0.00, 1, '', '', '0@@0@@0@@0')", __FILE__, __LINE__);	
        }
        
        $check3 = Database::query("SELECT id FROM quiz_question_templates WHERE question = 'DefaultQuizQuestion3'");
        if (Database::num_rows($check3) == 0) {
            $html_img3 = "<table height=\"100%\" width=\"98%\" cellspacing=\"2\" cellpadding=\"0\" style=\"font-family: Comic Sans MS; font-size: 16px;\">\r\n    <tbody>\r\n        <tr>\r\n            <td height=\"323px\" align=\"center\"><img  src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/heartArrows4Numbers300.png'."\"  /></td>\r\n        </tr>\r\n    </tbody>\r\n</table>";
            //Question and answer set 3
            Database::query("INSERT INTO  quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
            ('DefaultQuizQuestion3', '".$html_img3."', 20.00, 3, 1, '', 1)", __FILE__, __LINE__);
            $question_id = Database::insert_id();

            Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
            (4, ".$question_id.", 'QuizAnswer_3d', 0, '', 0.00, 4, '', '', '0@@0@@0@@0'),
            (3, ".$question_id.", 'QuizAnswer_3c', 0, '', 0.00, 3, '', '', '0@@0@@0@@0'),
            (2, ".$question_id.", 'QuizAnswer_3b', 1, '', 20.00, 2, '', '', '0@@0@@0@@0'),
            (1, ".$question_id.", 'QuizAnswer_3a', 0, '', 0.00, 1, '', '', '0@@0@@0@@0')", __FILE__, __LINE__);
        }

        $check4 = Database::query("SELECT id FROM quiz_question_templates WHERE question = 'DefaultQuizQuestion4'");
        if (Database::num_rows($check4) == 0) {
            $html_img4 = "<table height=\"100%\" width=\"98%\" cellspacing=\"2\" cellpadding=\"0\" style=\"font-family: Comic Sans MS; font-size: 16px;\">\r\n    <tbody>\r\n        <tr>\r\n            <td height=\"323px\" align=\"center\"><img height=\"310px\" src=\"../img/instructor-faq.png\"  /></td>\r\n        </tr>\r\n    </tbody>\r\n</table>";	
            //Question and answer set 4
            Database::query("INSERT INTO  quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
            ('DefaultQuizQuestion4', '".$html_img4."', 20.00, 4, 1, '', 1)", __FILE__, __LINE__);
            $question_id = Database::insert_id();
            Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
            (1, ".$question_id.", 'QuizAnswer_4a', 0, 'Feedback_qn4_true', 0.00, 1, '', '', '0@@0@@0@@0'),
            (2, ".$question_id.", 'QuizAnswer_4b', 0, 'Feedback_qn4_true', 0.00, 2, '', '', '0@@0@@0@@0'),
            (3, ".$question_id.", 'QuizAnswer_4c', 0, 'Feedback_qn4_true', 0.00, 3, '', '', '0@@0@@0@@0'),
            (4, ".$question_id.", 'QuizAnswer_4d', 1, 'Feedback_qn4_true', 20.00, 4, '', '', '0@@0@@0@@0')", __FILE__, __LINE__);
        }
        
        $check5 = Database::query("SELECT id FROM quiz_question_templates WHERE question = 'DefaultQuizQuestion5'");
        if (Database::num_rows($check5) == 0) {
            $html_img5 = "<p style=\"text-align: center;\">".lang2db(get_lang('html_img5_text'))."</p><p style=\"text-align: center;\"></p><p style=\"text-align: center;\"><img border=\"0\" align=\"absmiddle\" width=\"200\" vspace=\"0\" hspace=\"0\" height=\"133\" alt=\"Cornell_dormitories2.jpg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/Cornell_dormitories2.jpg'."\" /></p>";

            //Question and answer set 5
            Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
            ('DefaultQuizQuestion5', '".$html_img5."', 20.00, 5, 1, '', 1)", __FILE__, __LINE__);
            $question_id = Database::insert_id();

            Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
            (1, ".$question_id.", 'QuizAnswer_5a', 0, 'Feedback_qn5_true', 0.00, 1, '', '', '0@@0@@0@@0'),
            (2, ".$question_id.", 'QuizAnswer_5b', 0, 'Feedback_qn5_true', 0.00, 2, '', '', '0@@0@@0@@0'),
            (3, ".$question_id.", 'QuizAnswer_5c', 0, 'Feedback_qn5_true', 0.00, 3, '', '', '0@@0@@0@@0'),
            (4, ".$question_id.", 'QuizAnswer_5d', 1, 'Feedback_qn5_true', 20.00, 4, '', '', '0@@0@@0@@0')", __FILE__, __LINE__);	
        }
        
        $check6 = Database::query("SELECT id FROM quiz_question_templates WHERE question = 'DefaultQuizQuestion6'");
        if (Database::num_rows($check6) == 0) {
            $html_ans_6a = "<p><img border=\"0\" align=\"absmiddle\" width=\"250\" vspace=\"0\" hspace=\"0\" height=\"63\" alt=\"HPAnswer1_1.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/HPAnswer1_1.png'."\" /></p>";
            $html_ans_6b = "<p><img border=\"0\" align=\"absmiddle\" width=\"250\" vspace=\"0\" hspace=\"0\" height=\"63\" alt=\"HPAnswer2.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/HPAnswer2.png'."\" /></p>";
            $html_ans_6c = "<p><img border=\"0\" align=\"absmiddle\" width=\"250\" vspace=\"0\" hspace=\"0\" height=\"63\" alt=\"HPAnswer3_1.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/HPAnswer3_1.png'."\" /></p>";
            $html_ans_6d = "<p><img border=\"0\" align=\"absmiddle\" width=\"250\" vspace=\"0\" hspace=\"0\" height=\"63\" alt=\"HPAnswer4_3.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/HPAnswer4_3.png'."\" /></p>";

            $html_img6 = "<table cellspacing=\"2\" cellpadding=\"0\" width=\"98%\" height=\"100%\" style=\"font-family: Comic Sans MS; font-size: 16px;\">
            <tbody><tr><td align=\"center\" height=\"323px\"><img border=\"0\" align=\"absmiddle\" width=\"377\" vspace=\"0\" hspace=\"0\" height=\"300\" alt=\"HPQuestion_1.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/HPQuestion_1.png'."\" /></td></tr></tbody></table>";

            $html_img6_feedback_true = "<p><img border=\"0\" align=\"absmiddle\" width=\"376\" vspace=\"0\" hspace=\"0\" height=\"300\" alt=\"HPfeedback_1.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/HPfeedback_1.png'."\" />".lang2db(get_lang('html_img6_feedback_text'))."</p>";

            //Question and answer set 6
            Database::query("INSERT INTO  quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
            ('DefaultQuizQuestion6', '".$html_img6."', 20.00, 6, 1, '', 1)", __FILE__, __LINE__);
            $question_id = Database::insert_id();

            Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
            (1, ".$question_id.", '".$html_ans_6a."', 0, '".$html_img6_feedback_true."', 0.00, 1, '', '', '0@@0@@0@@0'),
            (2, ".$question_id.", '".$html_ans_6b."', 0, '".$html_img6_feedback_true."', 0.00, 2, '', '', '0@@0@@0@@0'),
            (3, ".$question_id.", '".$html_ans_6c."', 1, '".$html_img6_feedback_true."', 20.00, 3, '', '', '0@@0@@0@@0'),
            (4, ".$question_id.", '".$html_ans_6d."', 0, '".$html_img6_feedback_true."', 0.00, 4, '', '', '0@@0@@0@@0')", __FILE__, __LINE__);
        }
                
        $check7 = Database::query("SELECT id FROM quiz_question_templates WHERE question = 'DefaultQuizQuestion7'");
        if (Database::num_rows($check7) == 0) {
            $html_img7 = "<table cellspacing=\"2\" cellpadding=\"0\ width=\"98%\" height=\100%\" style=\"font-family: Comic Sans MS; font-size: 16px;\">
                            <tbody><tr><td align=\"center\" height=\"323px\"><p><img height=\"310px\" alt=\"\" src=\"../img/instructor-faq.png\" /></p>
                                <p><embed width=\"300\" height=\"20\" flashvars=\"file=".api_get_path(WEB_CODE_PATH)."default_course_document/audio/EconomicCensus.mp3&amp;autostart=false\" allowscriptaccess=\"always\" allowfullscreen=\"false\" src=\"/main/inc/lib/mediaplayer/player.swf\" bgcolor=\"#FFFFFF\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" type=\"application/x-shockwave-flash\"></embed></p></td></tr></tbody></table>";

            //Question and answer set 7
            Database::query("INSERT INTO  quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
            ('DefaultQuizQuestion7', '".$html_img7."', 20.00, 7, 1, '', 1)", __FILE__, __LINE__);
            $question_id = Database::insert_id();

            Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
            (4, ".$question_id.", 'QuizAnswer_7a', 1, 'Feedback_qn7_true', 20.00, 4, '', '', ''),
            (3, ".$question_id.", 'QuizAnswer_7b', 0, 'Feedback_qn7_true', 0.00, 3, '', '', ''),
            (2, ".$question_id.", 'QuizAnswer_7c', 0, 'Feedback_qn7_true', 0.00, 2, '', '', ''),
            (1, ".$question_id.", 'QuizAnswer_7d', 0, 'Feedback_qn7_true', 0.00, 1, '', '', '')", __FILE__, __LINE__);
        }
        
        $check8 = Database::query("SELECT id FROM quiz_question_templates WHERE question = 'DefaultQuizQuestion8'");
        if (Database::num_rows($check8) == 0) {
            $html_img8 = "<table height=\"100%\" width=\"98%\" cellspacing=\"2\" cellpadding=\"0\" style=\"font-family: Comic Sans MS; font-size: 16px;\">
            <tbody><tr><td height=\"323px\" align=\"center\">           
                            <div id=\"player504837-parent\" style=\"text-align: center;\">
                            <div style=\"border-style: none; height: 240px; width: 320px; overflow: hidden; background-color: rgb(220, 220, 220);\"><script src=\"/main/inc/lib/swfobject/swfobject.js\" type=\"text/javascript\"></script>
                            <div id=\"player504837\"><a href=\"http://www.macromedia.com/go/getflashplayer\" target=\"_blank\">Get the Flash Player</a> to see this video.
                            <div id=\"player504837-config\" style=\"display: none; visibility: hidden; width: 0px; height: 0px; overflow: hidden;\">url=".api_get_path(WEB_CODE_PATH)."default_course_document/video/flv/OpenofficeSlideshow.flv width=320 height=240 loop=false play=false downloadable=false fullscreen=true displayNavigation=true displayDigits=true align=left playlistThumbs=false</div>
                            </div>
                            <script type=\"text/javascript\">
            var s1 = new SWFObject(\"/main/inc/lib/mediaplayer/player.swf\",\"single\",\"320\",\"240\",\"7\");
            s1.addVariable(\"width\",\"320\");
            s1.addVariable(\"height\",\"240\");
            s1.addVariable(\"autostart\",\"false\");
            s1.addVariable(\"file\",\"".api_get_path(WEB_CODE_PATH).'default_course_document/video/flv/OpenofficeSlideshow.flv'."\");
            s1.addVariable(\"repeat\",\"false\");
            s1.addVariable(\"showdownload\",\"false\");
            s1.addVariable(\"link\",\"".api_get_path(WEB_CODE_PATH).'default_course_document/video/flv/OpenofficeSlideshow.flv'."\");
            s1.addParam(\"allowfullscreen\",\"true\");
            s1.addVariable(\"showdigits\",\"true\");
            s1.addVariable(\"shownavigation\",\"true\");
            s1.addVariable(\"logo\",\"\");
            s1.write(\"player504837\");
            </script></div></div><p>&nbsp;</p></td></tr></tbody></table>";

            //Question and answer set 8
            Database::query("INSERT INTO  quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
            ('DefaultQuizQuestion8', '".$html_img8."', 20.00, 8, 1, '', 1)", __FILE__, __LINE__);
            $question_id = Database::insert_id();

            Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
            (1, ".$question_id.", 'QuizAnswer_8a', 0, '', 0.00, 1, '', '', ''),
            (2, ".$question_id.", 'QuizAnswer_8b', 0, '', 0.00, 2, '', '', ''),
            (3, ".$question_id.", 'QuizAnswer_8c', 0, '', 0.00, 3, '', '', ''),
            (4, ".$question_id.", 'QuizAnswer_8d', 1, '', 20.00, 4, '', '', '')", __FILE__, __LINE__);
        }
        
        $check9 = Database::query("SELECT id FROM quiz_question_templates WHERE question = 'DefaultQuizQuestion9'");
        if (Database::num_rows($check8) == 0) {
            $html_img9 = "<table height=\"100%\" width=\"98%\" cellspacing=\"2\" cellpadding=\"0\" style=\"font-family: Comic Sans MS; font-size: 16px;\">
            <tbody><tr><td height=\"323px\" align=\"center\"><embed height=\"300\" width=\"350\" menu=\"true\" loop=\"true\" play=\"true\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/animations/SpinEchoSequence.swf'."\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" type=\"application/x-shockwave-flash\"></embed></td></tr></tbody></table>";

            //Question and answer set 9
            Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
            ('DefaultQuizQuestion9', '".$html_img9."', 20.00, 9, 1, '', 1)", __FILE__, __LINE__);
            $question_id = Database::insert_id();

            Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
            (1, ".$question_id.", 'QuizAnswer_9a', 0, 'Feedback_qn9_true', 0.00, 1, '', '', ''),
            (2, ".$question_id.", 'QuizAnswer_9b', 0, 'Feedback_qn9_true', 0.00, 2, '', '', ''),
            (3, ".$question_id.", 'QuizAnswer_9c', 0, 'Feedback_qn9_true', 0.00, 3, '', '', ''),
            (4, ".$question_id.", 'QuizAnswer_9d', 1, 'Feedback_qn9_true', 20.00, 4, '', '', '')", __FILE__, __LINE__);
        }
        
        $check10 = Database::query("SELECT id FROM quiz_question_templates WHERE question = 'DefaultQuizQuestion10'");
        if (Database::num_rows($check10) == 0) {
            $html_img10 = "<p style=\"text-align: center;\">&nbsp;</p>
            <div id=\"player28445-parent\" style=\"text-align: center;\">
            <div style=\"border-style: none; height: 240px; width: 320px; overflow: hidden; background-color: rgb(220, 220, 220); margin-left: auto; margin-right: auto;\"><script src=\"/main/inc/lib/swfobject/swfobject.js\" type=\"text/javascript\"></script>
            <div id=\"player28445\"><a target=\"_blank\" href=\"http://www.macromedia.com/go/getflashplayer\">Get the Flash Player</a> to see this video.
            <div id=\"player28445-config\" style=\"display: none; visibility: hidden; width: 0px; height: 0px; overflow: hidden;\">url=".api_get_path(WEB_CODE_PATH)."default_course_document/video/flv/Bloedstolling.flv width=320 height=240 loop=false play=false downloadable=false fullscreen=true displayNavigation=true displayDigits=true align=center playlistThumbs=false</div>
            </div><script type=\"text/javascript\">
                    var s1 = new SWFObject(\"/main/inc/lib/mediaplayer/player.swf\",\"single\",\"320\",\"240\",\"7\");
                    s1.addVariable(\"width\",\"320\");
                    s1.addVariable(\"height\",\"240\");
                    s1.addVariable(\"autostart\",\"false\");
                    s1.addVariable(\"file\",\"".api_get_path(WEB_CODE_PATH).'default_course_document/video/flv/Bloedstolling.flv'."\");
                    s1.addVariable(\"repeat\",\"false\");
                    s1.addVariable(\"showdownload\",\"false\");
                    s1.addVariable(\"link\",\"".api_get_path(WEB_CODE_PATH).'default_course_document/video/flv/Bloedstolling.flv'."\");
                    s1.addParam(\"allowfullscreen\",\"true\");
                    s1.addVariable(\"showdigits\",\"true\");
                    s1.addVariable(\"shownavigation\",\"true\");
                    s1.addVariable(\"logo\",\"\");
                    s1.write(\"player28445\");
            </script></div></div>";

            //Question and answer set 10
            Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
            ('DefaultQuizQuestion10', '".$html_img10."', 20.00, 10, 1, '', 1)", __FILE__, __LINE__);
            $question_id = Database::insert_id();

            Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
            (1, ".$question_id.", 'QuizAnswer_10a', 1, 'Feedback_qn10_true', 20.00, 1, '', '', ''),
            (2, ".$question_id.", 'QuizAnswer_10b', 0, 'Feedback_qn10_true', 0.00, 2, '', '', ''),
            (3, ".$question_id.", 'QuizAnswer_10c', 0, 'Feedback_qn10_true', 0.00, 3, '', '', ''),
            (4, ".$question_id.", 'QuizAnswer_10d', 0, 'Feedback_qn10_true', 0.00, 4, '', '', '')", __FILE__, __LINE__);
        }
        
        $check11 = Database::query("SELECT id FROM quiz_question_templates WHERE question = 'DefaultQuizQuestion11'");
        if (Database::num_rows($check11) == 0) {
            $html_img11 = "<table cellspacing=\"2\" cellpadding=\"0\" width=\"98%\" height=\"100%\" style=\"font-family: Comic Sans MS; font-size: 16px;\">
            <tbody><tr><td align=\"center\" height=\"323px\"><img border=\"0\" align=\"absmiddle\" width=\"380\" vspace=\"0\" hspace=\"0\" height=\"143\" alt=\"sleeping_1.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/sleeping_1.png'."\" /></td></tr></tbody></table>";
            
            //Question and answer set 11
            Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
            ('DefaultQuizQuestion11', '".$html_img11."', 20.00, 11, 2, '', 1)", __FILE__, __LINE__);
            $question_id = Database::insert_id();

            Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
            (1, ".$question_id.", 'QuizAnswer_11a', 1, 'Feedback_qn8_true', 10.00, 1, '', '', ''),
            (2, ".$question_id.", 'QuizAnswer_11b', 1, 'Feedback_qn8_true', 10.00, 2, '', '', ''),
            (3, ".$question_id.", 'QuizAnswer_11c', 0, 'Feedback_qn8_true', 0.00, 3, '', '', ''),
            (4, ".$question_id.", 'QuizAnswer_11d', 1, 'Feedback_qn8_true', 10.00, 4, '', '', '')", __FILE__, __LINE__);	
        }
        
        $check12 = Database::query("SELECT id FROM quiz_question_templates WHERE question = 'DefaultQuizQuestion12'");
        if (Database::num_rows($check12) == 0) {
            $html_img12 = "<table cellspacing=\"2\" cellpadding=\"0\" width=\"98%\" height=\"100%\" style=\"font-family: Comic Sans MS; font-size: 16px;\"><tbody>
            <tr><td align=\"center\" height=\"323px\"><img border=\"0\" align=\"absmiddle\" width=\"380\" vspace=\"0\" hspace=\"0\" height=\"239\" alt=\"Solar_sys.jpg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/Solar_sys.jpg'."\" /></td></tr></tbody></table>";

            //Question and answer set 12
            Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
            ('DefaultQuizQuestion12', '".$html_img12."', 20.00, 12, 8, '', 1)", __FILE__, __LINE__);
            $question_id = Database::insert_id();

            Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
            (1, ".$question_id.", 'QuizAnswer_12a', 1, 'Feedback_qn12_true', 10.00, 1, '', '', ''),
            (2, ".$question_id.", 'QuizAnswer_12b', 0, 'Feedback_qn12_true', 0.00, 2, '', '', ''),
            (3, ".$question_id.", 'QuizAnswer_12c', 0, 'Feedback_qn12_true', 0.00, 3, '', '', ''),
            (4, ".$question_id.", 'QuizAnswer_12d', 1, 'Feedback_qn12_true', 10.00, 4, '', '', '')", __FILE__, __LINE__);
        }
        
        $check13 = Database::query("SELECT id FROM quiz_question_templates WHERE question = 'DefaultQuizQuestion13'");
        if (Database::num_rows($check13) == 0) {
            $html_img13 = "<table height=\"100%\" width=\"98%\" cellspacing=\"2\" cellpadding=\"0\" style=\"font-family: Comic Sans MS; font-size: 16px;\"><tbody>
            <tr><td height=\"323px\" align=\"center\"><img hspace=\"0\" height=\"345\" width=\"350\" vspace=\"0\" border=\"0\" align=\"absmiddle\" alt=\"Traffic_lights.jpg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/Traffic_lights.jpg'."\" /></td></tr></tbody></table>";

            $html_ans_13a = "<p><img hspace=\"0\" height=\"100\" width=\"100\" vspace=\"0\" border=\"0\" align=\"absmiddle\" alt=\"truck.jpg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/truck.jpg'."\" /></p>";
            $html_ans_13b = "<p><img hspace=\"0\" height=\"100\" width=\"100\" vspace=\"0\" border=\"0\" align=\"absmiddle\" alt=\"railroad.jpg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/railroad.jpg'."\" /></p>";
            $html_ans_13c = "<p><img hspace=\"0\" height=\"100\" width=\"100\" vspace=\"0\" border=\"0\" align=\"absmiddle\" alt=\"deer.jpg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/deer.jpg'."\" /></p>";
            $html_ans_13d = "<p><img hspace=\"0\" height=\"100\" width=\"100\" vspace=\"0\" border=\"0\" align=\"absmiddle\" alt=\"pedestrian.jpg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/pedestrian.jpg'."\" /></p>";

            //Question and answer set 13
            Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
            ('DefaultQuizQuestion13', '".$html_img13."', 20.00, 13, 2, '', 1)", __FILE__, __LINE__);
            $question_id = Database::insert_id();

            Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
            (1, ".$question_id.", '".$html_ans_13a."', 1, 'Feedback_qn13_true', 10.00, 1, '', '', '0@@0@@0@@0'),
            (2, ".$question_id.", '".$html_ans_13b."', 0, 'Feedback_qn13_true', 0.00, 2, '', '', '0@@0@@0@@0'),
            (3, ".$question_id.", '".$html_ans_13c."', 0, 'Feedback_qn13_true', 0.00, 3, '', '', '0@@0@@0@@0'),
            (4, ".$question_id.", '".$html_ans_13d."', 1, 'Feedback_qn13_true', 10.00, 4, '', '', '0@@0@@0@@0')", __FILE__, __LINE__);
        }
        
        $check14 = Database::query("SELECT id FROM quiz_question_templates WHERE question = 'DefaultQuizQuestion14'");
        if (Database::num_rows($check14) == 0) {
            $html_img14 = "<table cellspacing=\"2\" cellpadding=\"0\" width=\"98%\" height=\"100%\" style=\"font-family: Comic Sans MS; font-size: 16px;\"><tbody>
            <tr><td align=\"center\" height=\"323px\"><img border=\"0\" align=\"absmiddle\" width=\"300\" vspace=\"0\" hspace=\"0\" height=\"227\" alt=\"ViolentCrimeAmerica.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/ViolentCrimeAmerica.png'."\" /></td></tr></tbody></table>";

            //Question and answer set 14
            Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
            ('DefaultQuizQuestion14', '".$html_img14."', 20.00, 14, 8, '', 1)", __FILE__, __LINE__);
            $question_id = Database::insert_id();

            Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
            (1, ".$question_id.", 'QuizAnswer_14a', 1, '', 10.00, 1, '', '', ''),
            (2, ".$question_id.", 'QuizAnswer_14b', 0, '', 0.00, 2, '', '', ''),
            (3, ".$question_id.", 'QuizAnswer_14c', 0, '', 0.00, 3, '', '', ''),
            (4, ".$question_id.", 'QuizAnswer_14d', 1, '', 10.00, 4, '', '', '')", __FILE__, __LINE__);	
        }
        
        $check15 = Database::query("SELECT id FROM quiz_question_templates WHERE question = 'DefaultQuizQuestion15'");
        if (Database::num_rows($check15) == 0) {
            $html_img15 = "<table cellspacing=\"2\" cellpadding=\"0\" width=\"98%\" height=\"100%\" style=\"font-family: Comic Sans MS; font-size: 16px;\"><tbody>
            <tr><td align=\"center\" height=\"323px\"><img alt=\"\" src=\"../img/KnockOnWood.png\" /></td></tr></tbody></table>";

            $html_ans_15 = "<table cellspacing=\"0\" cellpadding=\"10\" border=\"1\" align=\"center\" width=\"420\"><tbody><tr><td style=\"text-align: center;\"><strong>Treatment</strong></td><td style=\"text-align: center;\"><strong>Y</strong> or<strong> N</strong></td><td><p><strong>1</strong> = on day 1</p><p><strong>0</strong> = none</p><p><strong>D</strong> = discharge day</p></td></tr><tr><td style=\"text-align: center;\"><strong>Malaria </strong></td>
            <td style=\"text-align: center;\">[<u>Y</u>]&nbsp;&nbsp;</td><td style=\"text-align: center;\">&nbsp;[<u>1</u>]&nbsp;&nbsp;</td></tr><tr><td style=\"text-align: center;\"><strong>Polio </strong></td><td style=\"text-align: center;\">[<u>Y</u>]&nbsp;&nbsp;</td><td style=\"text-align: center;\">[<u> D</u>]&nbsp;&nbsp;</td></tr><tr><td style=\"text-align: center;\"><strong>Pneumococcus vaccin </strong></td><td style=\"text-align: center;\">[<u>N</u>]&nbsp;&nbsp;</td><td style=\"text-align: center;\">[<u>0</u>]&nbsp;&nbsp;</td></tr></tbody></table><p>&nbsp;</p>::10,10,10,10,10,10@";

            $comment_15 = "a:2:{s:10:\"comment[1]\";s:0:\"\";s:10:\"comment[2]\";s:0:\"\";}";

            //Question and answer set 15
            Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
            ('DefaultQuizQuestion15', '".$html_img15."', 60.00, 15, 3, '', 1)", __FILE__, __LINE__);
            $question_id = Database::insert_id();

            Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
            (1, ".$question_id.", '".$html_ans_15."', 1, '".$comment_15."', 0.00, 0, '', '', '')", __FILE__, __LINE__);	
        }
        
        $check16 = Database::query("SELECT id FROM quiz_question_templates WHERE question = 'DefaultQuizQuestion16'");
        if (Database::num_rows($check16) == 0) {
            $html_img16 = "<table cellspacing=\"2\" cellpadding=\"0\" width=\"98%\" height=\"100%\" style=\"font-family: Comic Sans MS; font-size: 16px;\"><tbody>
            <tr><td align=\"center\" height=\"323px\"><img border=\"0\" align=\"absmiddle\" width=\"270\" vspace=\"0\" hspace=\"0\" height=\"320\" alt=\"balance_scale_redone.jpg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/balance_scale_redone.jpg'."\" /></td></tr></tbody></table>";

            $html_ans_16 = "<table cellspacing=\"0\" cellpadding=\"10\" border=\"1\" align=\"center\" width=\"380\"><tbody><tr><td bgcolor=\"#f5f5f5\" style=\"text-align: right;\"><strong>Patient</strong></td><td bgcolor=\"#f5f5f5\" style=\"text-align: center;\"><strong>Laura</strong></td><td bgcolor=\"#f5f5f5\" style=\"text-align: center;\"><strong>Bill</strong></td></tr><tr><td style=\"text-align: right;\">Age</td><td style=\"text-align: center;\">38</td><td style=\"text-align: center;\">44</td></tr><tr><td style=\"text-align: right;\">Height</td><td style=\"text-align: center;\">1.72 m</td><td style=\"text-align: center;\">1.88 m</td>   </tr><tr><td style=\"text-align: right;\">Weight</td><td style=\"text-align: center;\">65 kg</td><td style=\"text-align: center;\">[<u>103</u>] kg</td></tr><tr><td style=\"text-align: right;\">Blood Pressure</td><td style=\"text-align: center;\">120/75</td><td style=\"text-align: center;\">11/65</td></tr> <tr><td style=\"vertical-align: top; text-align: right;\">BMI</td><td style=\"vertical-align: top; text-align: center;\">[<u>22</u>]&nbsp;&nbsp;</td><td style=\"text-align: center;\">&nbsp;29</td></tr></tbody></table>::10,10@";

            $comment_16 = "a:2:{s:10:\"comment[1]\";s:0:\"\";s:10:\"comment[2]\";s:0:\"\";}";

            //Question and answer set 16
            Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
            ('DefaultQuizQuestion16', '".$html_img16."', 20.00, 16, 3, '', 1)", __FILE__, __LINE__);
            $question_id = Database::insert_id();

            Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
            (1, ".$question_id.", '".$html_ans_16."', 1, '".$comment_16."', 0.00, 0, '', '', '')", __FILE__, __LINE__);
        }
        
        $check17 = Database::query("SELECT id FROM quiz_question_templates WHERE question = 'DefaultQuizQuestion17'");
        if (Database::num_rows($check17) == 0) {
            $html_ans_17 = "<table cellspacing=\"0\" cellpadding=\"10\" border=\"1\" align=\"center\" width=\"420\"><tbody><tr><td>&nbsp;</td><td style=\"text-align: center;\"><strong>H</strong></td><td style=\"text-align: center;\"><strong>W</strong></td><td style=\"text-align: center;\"><strong>M</strong></td><td style=\"text-align: center;\"><strong>O</strong></td><td style=\"text-align: center;\"><strong>NS<br /></strong></td></tr><tr><td style=\"text-align: center;\"><strong>Laura</strong></td><td style=\"text-align: center;\">89</td><td style=\"text-align: center;\">12.3</td><td style=\"text-align: center;\">140</td><td style=\"text-align: center;\">Y</td><td style=\"text-align: center;\">[<u>SAM</u>]&nbsp;&nbsp;</td></tr><tr><td style=\"text-align: center;\"><strong>John</strong></td><td style=\"text-align: center;\">73.5</td><td style=\"text-align: center;\">6.3</td><td style=\"text-align: center;\">124</td><td style=\"text-align: center;\">N</td><td style=\"text-align: center;\">[<u>SAM</u>]&nbsp;&nbsp;</td></tr><tr><td style=\"text-align: center;\"><strong>Anna</strong></td><td style=\"text-align: center;\">94.5</td><td style=\"text-align: center;\">10</td><td style=\"text-align: center;\">108</td><td style=\"text-align: center;\">N</td><td style=\"text-align: center;\">[<u>SAM</u>]&nbsp;&nbsp;</td></tr><tr><td style=\"text-align: center;\"><strong>Bill</strong></td><td style=\"text-align: center;\">120</td><td style=\"text-align: center;\">13.8</td><td style=\"text-align: center;\">112</td><td style=\"text-align: center;\">N</td><td style=\"text-align: center;\">[<u>SAM</u>]&nbsp;&nbsp;</td></tr><tr><td style=\"text-align: center;\"><strong>Peter</strong></td><td style=\"text-align: center;\">67</td><td style=\"text-align: center;\">7.4</td><td style=\"text-align: center;\">130</td><td style=\"text-align: center;\">N</td><td style=\"text-align: center;\">[<u>N</u>]&nbsp;&nbsp;</td></tr></tbody></table><p>H = Height in cm, W = Weight in kg, M = Muac in mm, O = Oedema present Yes/No</p>::10,10,10,10,10@";

            $comment_17 = "a:2:{s:10:\"comment[1]\";s:0:\"\";s:10:\"comment[2]\";s:0:\"\";}";

            //Question and answer set 17
            Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
            ('DefaultQuizQuestion17', '".$html_img15."', 50.00, 17, 3, '', 1)", __FILE__, __LINE__);
            $question_id = Database::insert_id();
            Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
            (1, ".$question_id.", '".$html_ans_17."', 1, '".$comment_17."', 0.00, 0, '', '', '')", __FILE__, __LINE__);
        }
        		
        $check18 = Database::query("SELECT id FROM quiz_question_templates WHERE question = 'DefaultQuizQuestion18'");
        if (Database::num_rows($check18) == 0) {
            $html_ans_18 = "<p>".lang2db(get_lang('html_ans_18_text'))."<sqdf></sqdf></p>::10,10,10@";

            $html_img18 = "<table cellspacing=\"2\" cellpadding=\"0\" style=\"font-family: Comic Sans MS; font-size: 16px; width: 375px; height: 277px;\"><tbody>
            <tr><td align=\"center\" height=\"323px\"><img border=\"0\" align=\"absmiddle\" width=\"254\" vspace=\"0\" hspace=\"0\" height=\"200\" alt=\"SpeechMike.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/mascot/SpeechMike.png'."\" /><embed width=\"300\" height=\"20\" type=\"application/x-shockwave-flash\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" bgcolor=\"#FFFFFF\" src=\"/main/inc/lib/mediaplayer/player.swf\" allowfullscreen=\"false\" allowscriptaccess=\"always\" flashvars=\"file=".api_get_path(WEB_CODE_PATH)."default_course_document/audio/EconCensus64.mp3&amp;autostart=false\"></embed></td></tr></tbody></table>";

            $comment_18 = "a:2:{s:10:\"comment[1]\";s:0:\"\";s:10:\"comment[2]\";s:0:\"\";}";

            //Question and answer set 18
            Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
            ('DefaultQuizQuestion18', '".$html_img18."', 30.00, 18, 3, '', 1)", __FILE__, __LINE__);
            $question_id = Database::insert_id();

            Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
            (1, ".$question_id.", '".$html_ans_18."', 1, '".$comment_18."', 0.00, 0, '', '', '')", __FILE__, __LINE__);
        }
        
        $check19 = Database::query("SELECT id FROM quiz_question_templates WHERE question = 'DefaultQuizQuestion19'");
        if (Database::num_rows($check19) == 0) {
            $html_ans_19 = "<table cellspacing=\"0\" cellpadding=\"10\" border=\"1\" align=\"center\" width=\"420\"><tbody><tr><td>&nbsp;</td><td style=\"text-align: center;\">[<u>M</u>]&nbsp;&nbsp;</td><td>&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td></tr><tr><td style=\"text-align: center;\">[<u>V</u>]&nbsp;&nbsp;</td><td style=\"text-align: center;\">[<u>I</u>]&nbsp;&nbsp;</td><td style=\"text-align: center;\">[<u>S</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>I</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>O</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>N</u>]&nbsp;&nbsp;</td>
                                <td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td></tr><tr><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>S</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td>
                                <td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td></tr>
                                <tr><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>S</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>T</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>R</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>A</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top;\">[<u>T</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>E</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>G</u>]&nbsp;&nbsp;</td>
                                <td style=\"vertical-align: top; text-align: center;\">[<u>Y</u>]&nbsp;&nbsp;</td></tr><tr><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>I</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td></tr><tr><td style=\"vertical-align: top; text-align: center;\">[<u>P</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top;\">[<u>O</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>L</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>I</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>C</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>Y</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td></tr><tr><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top; text-align: center;\">[<u>N</u>]&nbsp;&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td><td style=\"vertical-align: top;\">&nbsp;</td></tr></tbody></table><p>&nbsp;</p>::10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10@";

            $html_img19 = "<p>&nbsp;</p><p>1 Vertical&nbsp; : In the B-bath Company, it is to make soap<br />1 Horizontal : Intended direction <br />2 Horizontal : provides a guideline to managers decision making<br />3 Horizontal contains rules</p><p style=\"text-align: center;\">&nbsp;</p><p style=\"text-align: center;\"><img border=\"0\" align=\"absmiddle\" width=\"239\" vspace=\"0\" hspace=\"0\" height=\"150\" alt=\"240business_meeting.jpg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/240business_meeting.jpg'."\" /></p>";

            //Question and answer set 19
            Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
            ('DefaultQuizQuestion19', '".$html_img19."', 250.00, 19, 3, '', 1)", __FILE__, __LINE__);
            $question_id = Database::insert_id();

            Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
            (1, ".$question_id.", '".$html_ans_19."', 1, '".$comment_19."', 0.00, 0, '', '', '')", __FILE__, __LINE__);
        }
        
        $check20 = Database::query("SELECT id FROM quiz_question_templates WHERE question = 'DefaultQuizQuestion20'");
        if (Database::num_rows($check20) == 0) {
            $html_img20 = "<table height=\"100%\" width=\"98%\" cellspacing=\"2\" cellpadding=\"0\" style=\"font-family: Comic Sans MS; font-size: 16px;\"><tbody>
            <tr><td height=\"323px\" align=\"center\"><img hspace=\"0\" height=\"205\" width=\"350\" vspace=\"0\" border=\"0\" align=\"absmiddle\" alt=\"6Hats_1.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/6Hats_1.png'."\" /></td></tr></tbody></table>";	

            //Question and answer set 20
            Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
            ('DefaultQuizQuestion20', '".$html_img20."', 20.00, 20, 5, '', 1)", __FILE__, __LINE__);
            $question_id = Database::insert_id();	
        }
        
        $check21 = Database::query("SELECT id FROM quiz_question_templates WHERE question = 'DefaultQuizQuestion21'");
        if (Database::num_rows($check21) == 0) {
            $html_img21 = "<table cellspacing=\"2\" cellpadding=\"0\" width=\"98%\" height=\"100%\" style=\"font-family: Comic Sans MS; font-size: 16px;\"><tbody><tr>  <td align=\"center\" height=\"323px\"><img alt=\"\" src=\"../img/instructor-idea.jpg\" /></td></tr></tbody></table>";

            //Question and answer set 21
            Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
            ('DefaultQuizQuestion21', '".$html_img21."', 20.00, 21, 5, '', 1)", __FILE__, __LINE__);
            $question_id = Database::insert_id();
        }
        
        $check22 = Database::query("SELECT id FROM quiz_question_templates WHERE question = 'DefaultQuizQuestion22'");
        if (Database::num_rows($check22) == 0) {
            $html_img22 = "<table cellspacing=\"2\" cellpadding=\"0\" width=\"98%\" height=\"100%\" style=\"font-family: Comic Sans MS; font-size: 16px;\"><tbody><tr>  <td align=\"center\" height=\"323px\"><img border=\"0\" align=\"absmiddle\" width=\"380\" vspace=\"0\" hspace=\"0\" height=\"309\" alt=\"Board2_1.png\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/Board2_1.png'."\" /></td></tr></tbody></table>";

            //Question and answer set 22
            Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
            ('DefaultQuizQuestion22', '".$html_img22."', 20.00, 22, 5, '', 1)", __FILE__, __LINE__);
            $question_id = Database::insert_id();
        }
        
        $check18b = Database::query("SELECT id FROM quiz_question_templates WHERE question = 'DefaultQuestion18'");
        if (Database::num_rows($check18b) == 0) {
            //Question and answer set 23
            Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
            ('DefaultQuestion18', '', 20.00, 23, 4, '', 1)", __FILE__, __LINE__);
            $question_id = Database::insert_id();

            Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
            (1, ".$question_id.", '<p>Columbia River <img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"absmiddle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/ColumbiaRiverTr64.png'."\" alt=\"ColumbiaRiverTr64.png\" /></p>', 0, '".lang2db(get_lang('Feedback_qn18_true'))."', 0.00, 1, '', '', ''),
            (2, ".$question_id.", '<p>Rio Grande <img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"absmiddle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/RioGrandeTr64.png'."\" alt=\"RioGrandeTr64.png\" /></p>', 0, '".lang2db(get_lang('Feedback_qn18_false'))."', 0.00, 2, '', '', ''),
            (3, ".$question_id.", '<p>Tenesse River <img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"absmiddle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/TenesseeRiverTr64.png'."\" alt=\"TenesseeRiverTr64.png\" /></p>', 0, '', 0.00, 3, '', '', ''),
            (4, ".$question_id.", '<p>Arkanas River&nbsp; <img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"absmiddle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/ArkansasRiverTr64.png'."\" alt=\"ArkansasRiverTr64.png\" /></p>', 0, '', 0.00, 4, '', '', ''),
            (5, ".$question_id.", '<p>New Mexico <img hspace=\"0\" height=\"64\" width=\"68\" vspace=\"0\" border=\"0\" align=\"absmiddle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/New_Mexico2tr64.png'."\" alt=\"New_Mexico2tr64.png\" /></p>', 1, '', 5.00, 5, '', '', ''),
            (6, ".$question_id.", '<p>Alabama <img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"absmiddle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/AlabampaMapOutlineBlue2Tr64.png'."\" alt=\"AlabampaMapOutlineBlue2Tr64.png\" /></p>', 1, '', 5.00, 6, '', '', ''),
            (7, ".$question_id.", '<p>Oklahoma&nbsp; <img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"absmiddle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/OklahomaMapOutline3Tr64.png'."\" alt=\"OklahomaMapOutline3Tr64.png\" /></p>', 1, '', 5.00, 7, '', '', ''),
            (8, ".$question_id.", '<p>Washington <img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"absmiddle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/WashingtonStateMapOutline2tr64.png'."\" alt=\"WashingtonStateMapOutline2tr64.png\" /></p>', 1, '', 5.00, 8, '', '', '')", __FILE__, __LINE__);
        }
        
        $check19b = Database::query("SELECT id FROM quiz_question_templates WHERE question = 'DefaultQuestion19'");
        if (Database::num_rows($check19b) == 0) {
            //Question and answer set 24
            Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
            ('DefaultQuestion19', '', 20.00, 24, 4, '', 1)", __FILE__, __LINE__);
            $question_id = Database::insert_id();

            Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
            (1, ".$question_id.", '<p><img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"absmiddle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/medical15.png'."\" alt=\"medical15.png\" />&nbsp; Check Skin Temperature</p>', 0, '', 0.00, 1, '', '', ''),
            (2, ".$question_id.", '<p><img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"absmiddle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/medic25.png'."\" alt=\"medic25.png\" />&nbsp; Call Ambulance</p>', 0, '', 0.00, 2, '', '', ''),
            (3, ".$question_id.", '<p><img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"absmiddle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/medicalhandpointing.png'."\" alt=\"medicalhandpointing.png\" /> Tell casuality not to move</p>', 0, '', 0.00, 3, '', '', ''),
            (4, ".$question_id.", '', 0, '', 0.00, 4, '', '', ''),
            (5, ".$question_id.", '<p><img style=\"text-align: center;\" hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"absmiddle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/icons/logic/01.png'."\" alt=\"01.png\" /></p>', 3, '', 6.67, 5, '', '', ''),
            (6, ".$question_id.", '<p><img style=\"text-align: center;\" hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"absmiddle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/icons/logic/02.png'."\" alt=\"02.png\" /></p>', 2, '', 6.67, 6, '', '', ''),
            (7, ".$question_id.", '<p><img style=\"text-align: center;\" hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"absmiddle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/icons/logic/03.png'."\" alt=\"03.png\" /></p>', 1, '', 6.67, 7, '', '', '')", __FILE__, __LINE__);
        }
        
        $check24 = Database::query("SELECT id FROM quiz_question_templates WHERE question = 'DefaultQuizQuestion24'");
        if (Database::num_rows($check24) == 0) {
            //Question and answer set 25
            Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
            ('DefaultQuizQuestion24', '', 20.00, 25, 4, '', 1)", __FILE__, __LINE__);
            $question_id = Database::insert_id();

            Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
            (1, ".$question_id.", '<p style=\"text-align: center;\"><img hspace=\"0\" height=\"37\" width=\"31\" vspace=\"0\" border=\"0\" align=\"absmiddle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/A.png'."\" alt=\"A.png\" /></p>', 0, 'Feedback_qn24_true', 0.00, 1, '', '', ''),
            (2, ".$question_id.", '<p style=\"text-align: center;\"><img hspace=\"0\" height=\"37\" width=\"37\" vspace=\"0\" border=\"0\" align=\"absmiddle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/B_1.png'."\" alt=\"B_1.png\" /></p>', 0, 'Feedback_qn24_true', 0.00, 2, '', '', ''),
            (3, ".$question_id.", '<p style=\"text-align: center;\"><img hspace=\"0\" height=\"36\" width=\"199\" vspace=\"0\" border=\"0\" align=\"absmiddle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/_AorB_andnonA.png'."\" alt=\"_AorB_andnonA.png\" /></p>', 0, '', 0.00, 3, '', '', ''),
            (4, ".$question_id.", '<p style=\"text-align: center;\"><img hspace=\"0\" height=\"37\" width=\"145\" vspace=\"0\" border=\"0\" align=\"absmiddle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/AandnonA.png'."\" alt=\"AandnonA.png\" /></p>', 0, '', 0.00, 4, '', '', ''),
            (5, ".$question_id.", '<p style=\"text-align: center;\"><img hspace=\"0\" height=\"37\" width=\"111\" vspace=\"0\" border=\"0\" align=\"absmiddle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/AorB.png'."\" alt=\"AorB.png\" /></p>', 0, '', 0.00, 5, '', '', ''),
            (6, ".$question_id.", '<p style=\"text-align: center;\"><img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"absmiddle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/icons/logic/01.png'."\" alt=\"01.png\" /></p>', 4, '', 4.00, 6, '', '', ''),
            (7, ".$question_id.", '<p style=\"text-align: center;\"><img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"absmiddle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/icons/logic/02.png'."\" alt=\"02.png\" /></p>', 1, '', 4.00, 7, '', '', ''),
            (8, ".$question_id.", '<p style=\"text-align: center;\"><img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"absmiddle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/icons/logic/03.png'."\" alt=\"03.png\" /></p>', 5, '', 4.00, 8, '', '', ''),
            (9, ".$question_id.", '<p style=\"text-align: center;\"><img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"absmiddle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/icons/logic/04.png'."\" alt=\"04.png\" /></p>', 3, '', 4.00, 9, '', '', ''),
            (10, ".$question_id.", '<p style=\"text-align: center;\"><img hspace=\"0\" height=\"64\" width=\"64\" vspace=\"0\" border=\"0\" align=\"absmiddle\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/icons/logic/05.png'."\" alt=\"05.png\" /></p>', 2, '', 4.00, 10, '', '', '')", __FILE__, __LINE__);
        }
        
        $check25 = Database::query("SELECT id FROM quiz_question_templates WHERE question = 'DefaultQuizQuestion25'");
        if (Database::num_rows($check25) == 0) {
            //Question and answer set 26
            Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
            ('DefaultQuizQuestion25', '', 20.00, 26, 4, '', 1)", __FILE__, __LINE__);
            $question_id = Database::insert_id();

            Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
            (1, ".$question_id.", '<p><img hspace=\"0\" height=\"100\" width=\"50\" vspace=\"0\" border=\"0\" align=\"absmiddle\" alt=\"Compression.jpeg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/Compression.jpeg'."\" /></p>', 0, 'Feedback_qn25_true', 0.00, 1, '', '', ''),
            (2, ".$question_id.", '<p><img hspace=\"0\" height=\"100\" width=\"50\" vspace=\"0\" border=\"0\" align=\"absmiddle\" alt=\"Emission.jpeg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/Emission.jpeg'."\" /></p>', 0, 'Feedback_qn25_true', 0.00, 2, '', '', ''),
            (3, ".$question_id.", '<p><img hspace=\"0\" height=\"100\" width=\"50\" vspace=\"0\" border=\"0\" align=\"absmiddle\" alt=\"Ignition.jpeg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/Ignition.jpeg'."\" /></p>', 0, '', 0.00, 3, '', '', ''),
            (4, ".$question_id.", '<p><img hspace=\"0\" height=\"100\" width=\"50\" vspace=\"0\" border=\"0\" align=\"absmiddle\" alt=\"Induction.jpeg\" src=\"".api_get_path(WEB_CODE_PATH).'default_course_document/images/diagrams/templates/Induction.jpeg'."\" /></p>', 0, '', 0.00, 4, '', '', ''),
            (5, ".$question_id.", 'langQuizAnswer_25a', 3, '', 5.00, 5, '', '', ''),
            (6, ".$question_id.", 'langQuizAnswer_25b', 1, '', 5.00, 6, '', '', ''),
            (7, ".$question_id.", 'langQuizAnswer_25c', 4, '', 5.00, 7, '', '', ''),
            (8, ".$question_id.", 'langQuizAnswer_25d', 2, '', 5.00, 8, '', '', '')", __FILE__, __LINE__);
        }
        
        $check27 = Database::query("SELECT id FROM quiz_question_templates WHERE question = 'DefaultQuizQuestion27'");
        if (Database::num_rows($check27) == 0) {
            //Question and answer set 27	
            Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
            ('DefaultQuizQuestion27', '', 40.00, 27, 6, '', 1)", __FILE__, __LINE__);
            $question_id = Database::insert_id();
            copy(api_get_path(SYS_CODE_PATH).'default_course_document/images/quiz-27.jpg',api_get_path(SYS_CODE_PATH).'default_course_document/images/quiz-'.$question_id.'.jpg');

            Database::query("UPDATE quiz_question_templates SET picture = 'quiz-".$question_id.".jpg' WHERE id = ".$question_id,__FILE__,__LINE__);

            Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
            (1, ".$question_id.", 'langQuizAnswer_27a', 0, '', 10.00, 1, '42;166|32|38', 'square', ''),
            (2, ".$question_id.", 'langQuizAnswer_27b', 0, '', 10.00, 2, '122;283|75|120', 'circle', ''),
            (3, ".$question_id.", 'langQuizAnswer_27c', 0, '', 10.00, 3, '116;45|13|55', 'square', ''),
            (4, ".$question_id.", 'langQuizAnswer_27d', 0, '', 10.00, 4, '116;152|50|90', 'square', '')", __FILE__, __LINE__);
        }
        
        $check28 = Database::query("SELECT id FROM quiz_question_templates WHERE question = 'DefaultQuizQuestion28'");
        if (Database::num_rows($check28) == 0) {
            //Question and answer set 28
            Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
            ('DefaultQuizQuestion28', '', 30.00, 28, 6, '', 1)", __FILE__, __LINE__);
            $question_id = Database::insert_id();
            copy(api_get_path(SYS_CODE_PATH).'default_course_document/images/quiz-28.jpg',api_get_path(SYS_CODE_PATH).'default_course_document/images/quiz-'.$question_id.'.jpg');

            Database::query("UPDATE quiz_question_templates SET picture = 'quiz-".$question_id.".jpg' WHERE id = ".$question_id,__FILE__,__LINE__);

            Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
            (1, ".$question_id.", 'langQuizAnswer_28a', 0, '', 10.00, 1, '114;221|27|28', 'square', ''),
            (2, ".$question_id.", 'langQuizAnswer_28b', 0, '', 10.00, 2, '164;53|39|18', 'square', ''),
            (3, ".$question_id.", 'langQuizAnswer_28c', 0, '', 10.00, 3, '158;87|48|26', 'square', '')", __FILE__, __LINE__);
        }
        
        $check29 = Database::query("SELECT id FROM quiz_question_templates WHERE question = 'DefaultQuizQuestion29'");
        if (Database::num_rows($check29) == 0) {
            //Question and answer set 29
            Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
            ('DefaultQuizQuestion29', '', 30.00, 29, 6, '', 1)", __FILE__, __LINE__);
            $question_id = Database::insert_id();	
            copy(api_get_path(SYS_CODE_PATH).'default_course_document/images/quiz-29.jpg',api_get_path(SYS_CODE_PATH).'default_course_document/images/quiz-'.$question_id.'.jpg');

            Database::query("UPDATE quiz_question_templates SET picture = 'quiz-".$question_id.".jpg' WHERE id = ".$question_id,__FILE__,__LINE__);

            Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
            (1, ".$question_id.", 'langQuizAnswer_29a', 0, '', 10.00, 1, '203;17|23|30', 'square', ''),
            (2, ".$question_id.", 'langQuizAnswer_29b', 0, '', 10.00, 2, '133;294|59|20', 'square', ''),
            (3, ".$question_id.", 'langQuizAnswer_29c', 0, '', 10.00, 3, '306;184|93|22', 'square', '')", __FILE__, __LINE__);
        }
        
        $check30 = Database::query("SELECT id FROM quiz_question_templates WHERE question = 'DefaultQuizQuestion30'");
        if (Database::num_rows($check30) == 0) {
            //Question and answer set 30
            Database::query("INSERT INTO quiz_question_templates (`question`, `description`, `ponderation`, `position`, `type`, `picture`, `level`) VALUES
            ('DefaultQuizQuestion30', '', 30.00, 30, 6, '', 1)", __FILE__, __LINE__);
            $question_id = Database::insert_id();	
            copy(api_get_path(SYS_CODE_PATH).'default_course_document/images/quiz-30.jpg',api_get_path(SYS_CODE_PATH).'default_course_document/images/quiz-'.$question_id.'.jpg');

            Database::query("UPDATE quiz_question_templates SET picture = 'quiz-".$question_id.".jpg' WHERE id = ".$question_id,__FILE__,__LINE__);

            Database::query("INSERT INTO quiz_answer_templates (`id`, `question_id`, `answer`, `correct`, `comment`, `ponderation`, `position`, `hotspot_coordinates`, `hotspot_type`, `destination`) VALUES
            (1, ".$question_id.", 'langQuizAnswer_30a', 0, '', 10.00, 1, '37;31|8|13', 'square', ''),
            (2, ".$question_id.", 'langQuizAnswer_30b', 0, '', 10.00, 2, '52;71|9|14', 'square', ''),
            (3, ".$question_id.", 'langQuizAnswer_30c', 0, '', 10.00, 3, '22;98|11|14', 'square', '')", __FILE__, __LINE__);
        }
}