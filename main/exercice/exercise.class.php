<?php

/* For licensing terms, see /dokeos_license.txt */

/**
 * 	Exercise class: This class allows to instantiate an object of type Exercise
 * 	@package dokeos.exercise
 * 	@author Olivier Brouckaert
 * 	@version $Id: exercise.class.php 22046 2009-07-14 01:45:19Z ivantcholakov $
 */
if (!class_exists('Exercise')):

    class Exercise {

        public $id;
        public $exercise;
        public $simplifymode;        
        public $description;
        public $sound;
        public $type;
        public $random;
        public $active;
        public $timeLimit;
        public $attempts;
        public $feedbacktype;
        public $end_time;
        public $start_time;
        public $questionList;  // array with the list of this exercise's questions
        public $results_disabled;
        public $expired_time;
        public $scenario;
        private $certif_template;
        private $certif_min_score;
        private $score_pass;
        public $quiz_type;
        public $quiz_final_feedback;
        public $fromTool;
        
        public $db_name = '';

        /**
         * constructor of the class
         *
         * @author - Olivier Brouckaert
         */
        function Exercise($quiz_scenario = NULL, $db_name = '') {
            $this->id = 0;
            $this->exercise = '';
            $this->simplifymode = '';
            $this->description = '';
            $this->sound = '';
            $this->type = 1;
            $this->random = 0;
            $this->active = 1;
            $this->questionList = array();
            $this->timeLimit = 0;
            $this->end_time = '0000-00-00 00:00:00';
            $this->start_time = '0000-00-00 00:00:00';
            $this->results_disabled = 1;
            $this->expired_time = '0000-00-00 00:00:00';
            $this->scenario = $quiz_scenario;
            $this->score_pass = 50;
            $this->quiz_type = 1;
            $this->quiz_final_feedback = '';
            $this->db_name = $db_name;
        }

        /**
         * reads exercise informations from the data base
         *
         * @author - Olivier Brouckaert
         * @param - integer $id - exercise ID
         * @return - boolean - true if exercise exists, otherwise false
         */
        function read($id) {
            global $_course;
            global $_configuration;
            global $questionList;

            $TBL_EXERCICE_QUESTION = Database::get_course_table(TABLE_QUIZ_TEST_QUESTION, $this->db_name);
            $TBL_EXERCICES = Database::get_course_table(TABLE_QUIZ_TEST, $this->db_name);
            $TBL_QUESTIONS = Database::get_course_table(TABLE_QUIZ_QUESTION, $this->db_name);
            $TBL_EXERCICES_SCENARIO = Database::get_course_table(TABLE_QUIZ_SCENARIO, $this->db_name);

            // Check if scenario does not exists
            $sql_count = "SELECT count(*) AS count FROM $TBL_EXERCICES_SCENARIO WHERE exercice_id='" . Database::escape_string($id) . "' AND scenario_type='" . $this->scenario . "'";
            $rs = Database::query($sql_count, __FILE__, __LINE__);
            $row = Database::fetch_array($rs);
            // Count the matches
            $count_scenario = $row['count'];
            // If matches is zero then the scenario does not exists and we must create one
            if ($count_scenario == 0) {
                $session_id = api_get_session_id();
                $sql = "SELECT id,title,description,sound,type,random,active, results_disabled, max_attempt,start_time,end_time,feedback_type,expired_time, certif_template, certif_min_score,score_pass,quiz_type,quiz_final_feedback, simplifymode FROM $TBL_EXERCICES WHERE id='" . Database::escape_string($id) . "'";
                $rs = Database::query($sql, __FILE__, __LINE__);
                $rowquiz = Database::fetch_object($rs);
                // Sql for create a new scenario
                $sql_scenario = "INSERT INTO $TBL_EXERCICES_SCENARIO (exercice_id, scenario_type,
                title, description, sound, type, random, active, results_disabled,
                max_attempt, start_time, end_time, feedback_type,
                expired_time, session_id, certif_template, certif_min_score, score_pass, quiz_type, quiz_final_feedback, simplifymode) VALUES('" . $rowquiz->id . "','" . $this->scenario . "',
                '" . Database::escape_string($rowquiz->title) . "','" . Database::escape_string($rowquiz->description) . "','" . $rowquiz->sound . "',
                '" . $rowquiz->type . "','" . $rowquiz->random . "','" . $rowquiz->active . "',
                '" . $rowquiz->results_disabled . "','" . $rowquiz->max_attempt . "','" . $rowquiz->start_time . "',
                '" . $rowquiz->end_time . "','" . $rowquiz->feedback_type . "','" . $rowquiz->expired_time . "',
                '" . $session_id . "','" . $rowquiz->certif_template . "','" . $rowquiz->certif_min_score . "','" . $rowquiz->score_pass . "','" . $rowquiz->quiz_type . "','" . Database::escape_string($rowquiz->quiz_final_feedback) . "', ".$rowquiz->simplifymode." )";
                $rs_scenario = Database::query($sql_scenario, __FILE__, __LINE__);
                $_SESSION['display_confirmation_message'] = get_lang("langExerciseAdded");
            }



            if (is_numeric($this->scenario)) {
                $sql = "SELECT title,description,sound,type,random,active, results_disabled, max_attempt,start_time,end_time,feedback_type,expired_time, certif_template, certif_min_score,score_pass,quiz_type,quiz_final_feedback, simplifymode FROM $TBL_EXERCICES_SCENARIO WHERE exercice_id='" . Database::escape_string($id) . "' AND scenario_type='" . $this->scenario . "'";
            } else {
                $sql = "SELECT title,description,sound,type,random,active, results_disabled, max_attempt,start_time,end_time,feedback_type,expired_time, certif_template, certif_min_score,score_pass,quiz_type,quiz_final_feedback, simplifymode FROM $TBL_EXERCICES WHERE id='" . Database::escape_string($id) . "'";
            }
            
           
            
            $result = api_sql_query($sql, __FILE__, __LINE__);

            // if the exercise has been found
            $object = Database::fetch_object($result);
            if (is_object($object)) {
                $this->id = $id;
                $this->exercise = $object->title;
                $this->simplifymode  = $object->simplifymode;
                $this->description = $object->description;
                $this->sound = $object->sound;
                $this->type = $object->type;
                $this->random = $object->random;
                $this->active = $object->active;
                $this->results_disabled = $object->results_disabled;
                $this->attempts = $object->max_attempt;
                $this->feedbacktype = $object->feedback_type;
                $this->end_time = $object->end_time;
                $this->start_time = $object->start_time;
                $this->expired_time = $object->expired_time;

                $this->certif_template = $object->certif_template;
                $this->certif_min_score = $object->certif_min_score;

                $this->score_pass = $object->score_pass;
                $this->quiz_type = $object->quiz_type;
				$this->quiz_final_feedback = $object->quiz_final_feedback;

                $sql = "SELECT question_id, question_order FROM $TBL_EXERCICE_QUESTION,$TBL_QUESTIONS WHERE question_id=id AND exercice_id='" . Database::escape_string($id) . "' ORDER BY question_order";
                
                
                $result = api_sql_query($sql, __FILE__, __LINE__);

                // fills the array with the question ID for this exercise
                // the key of the array is the question position
                while ($object = Database::fetch_object($result)) {
                    // makes sure that the question position is unique
                    while (isset($this->questionList[$object->question_order])) {
                        $object->question_order++;
                    }
					
                    $this->questionList[$object->question_order] = $object->question_id;
                }
                
                
                
                //var_dump($this->end_time,$object->start_time);
                if ($this->random > 0 && !$this->scenario && !isset($_REQUEST['modifyExercise'])) {
                    $this->questionList = ($this->fromTool==TOOL_EVALUATION ?  $this->selectRandomList2(1) : $this->selectRandomList2(0)) ;
                }
                //overload questions list with recorded questions list
                //load questions only for exercises of type 'one question per page'
                //this is needed only is there is no questions
                //
    if ($this->type == 2 && $_configuration['live_exercise_tracking'] == true && $_SERVER['REQUEST_METHOD'] != 'POST' && defined('QUESTION_LIST_ALREADY_LOGGED')) {
                    //if(empty($_SESSION['questionList']))
                    $this->questionList = $questionList;
                }
                return true;
            }

            // exercise not found
            return false;
        }

        /**
         * returns the exercise ID
         *
         * @author - Olivier Brouckaert
         * @return - integer - exercise ID
         */
        function selectId() {
            return $this->id;
        }

        /**
         * returns the exercise title
         *
         * @author - Olivier Brouckaert
         * @return - string - exercise title
         */
        function selectTitle() {
            return $this->exercise;
        }
        function selectSimplifymode() {
            return $this->simplifymode;
        }
        
        /**
         * returns the number of attempts setted
         *
         * @return - numeric - exercise attempts
         */
        function selectAttempts() {
            return $this->attempts;
        }

        /** returns the number of FeedbackType  *
         *  0=>Feedback , 1=>DirectFeedback, 2=>NoFeedback
         * @return - numeric - exercise attempts
         */
        function selectFeedbackType() {
            return $this->feedbacktype;
        }

        /**
         * returns the time limit
         */
        function selectTimeLimit() {
            return $this->timeLimit;
        }

        /**
         * returns the exercise description
         *
         * @author - Olivier Brouckaert
         * @return - string - exercise description
         */
        function selectDescription() {
            return $this->description;
        }

		/**
         * returns the quiz final feedback
         *
         * @author - Olivier Brouckaert
         * @return - string - quiz final feedback
         */
        function selectQuizFinalFeedback() {
            return $this->quiz_final_feedback;
        }

        /**
         * returns the exercise sound file
         *
         * @author - Olivier Brouckaert
         * @return - string - exercise description
         */
        function selectSound() {
            return $this->sound;
        }

        /**
         * returns the exercise type
         *
         * @author - Olivier Brouckaert
         * @return - integer - exercise type
         */
        function selectType() {
            return $this->type;
        }

        /**
         * tells if questions are selected randomly, and if so returns the draws
         *
         * @author - Carlos Vargas
         * @return - integer - results disabled exercise
         */
        function selectResultsDisabled() {
            return $this->results_disabled;
        }

        function selectCertifTemplate() {
            return $this->certif_template;
        }
        
        function getTemplateById($quiz_id){
            $returnValue = array();
            $TBL_EXERCICES = Database::get_course_table(TABLE_QUIZ_TEST, $this->db_name);
            $query = "SELECT * FROM ".$TBL_EXERCICES."  WHERE id=".$quiz_id;
            $result = api_sql_query($query, __FILE__, __LINE__);
            while($object = Database::fetch_object($result)){
                $returnValue = $object;
            }
            return $returnValue;
        }

        function selectCertifMinScore() {
            return $this->certif_min_score;
        }

        /**
         * returns the score pass percent setted
         *
         * @return - numeric - score pass percent
         */
        function selectScorePass() {
            return $this->score_pass;
        }

        /**
         * returns the quiz type
         *
         * @return - numeric - quiz type - self-learning/Exam mode
         */
        function selectQuizType() {
            return $this->quiz_type;
        }

        /**
         * tells if questions are selected randomly, and if so returns the draws
         *
         * @author - Olivier Brouckaert
         * @return - integer - 0 if not random, otherwise the draws
         */
        function isRandom() {
            if ($this->random > 0) {
                return true;
            } else {
                return false;
            }
        }

        /**
         * Same as isRandom() but has a name applied to values different than 0 or 1
         */
        function getShuffle() {
            return $this->random;
        }

        /**
         * returns the exercise status (1 = enabled ; 0 = disabled)
         *
         * @author - Olivier Brouckaert
         * @return - boolean - true if enabled, otherwise false
         */
        function selectStatus() {
            return $this->active;
        }

        /**
         * returns the array with the question ID list
         *
         * @author - Olivier Brouckaert
         * @return - array - question ID list
         */
        function selectQuestionList() {
            return $this->questionList;
        }

        /**
         * returns the number of questions in this exercise
         *
         * @author - Olivier Brouckaert
         * @return - integer - number of questions
         */
        function selectNbrQuestions() {
            return sizeof($this->questionList);
        }
        
		/**
         * Get random_order field of table quiz_rel_question 
         *
         * @author - Juan Carlos Medina Orihuela
         * 
         * 
         */

		function getRandomOrder($from_evaluation) {
			
			$userId = api_get_user_id();
			$TBL_QUIZ_QUESTION_REL_USER = Database::get_course_table(TABLE_QUIZ_QUESTION_REL_USER, $this->db_name);
			$sql = "SELECT random_order,question_id FROM  $TBL_QUIZ_QUESTION_REL_USER WHERE user_id = {$userId} AND quiz_id = {$this->id} AND from_evaluation = {$from_evaluation} ORDER BY random_order";
			$result = Database::query($sql);
			
			$randomQuestionList=array();
			
			while ($object = Database::fetch_object($result)) {
				$randomQuestionList[$object->random_order] = $object->question_id;
			}
			
			if(count($randomQuestionList)!=$this->random)
				return array();
			
			
			return $randomQuestionList;
		}
        
		/**
         * Set random_order field of table quiz_rel_question 
         *
         * @author - Juan Carlos Medina Orihuela
         * 
         * 
         */


		function setRandomOrder($from_evaluation) {
			$TBL_QUIZ_QUESTION_REL_USER = Database::get_course_table(TABLE_QUIZ_QUESTION_REL_USER, $this->db_name);
			
			$temp_list = $this->questionList;
      
			$userId = api_get_user_id();
       
            if (count($temp_list)) {
				
				shuffle($temp_list);
				
				$randomQuestionList = array_combine(range(1, $this->random),array_slice($temp_list,0,$this->random));
				
				$sql = "INSERT INTO $TBL_QUIZ_QUESTION_REL_USER (user_id,quiz_id,question_id,random_order,from_evaluation) VALUES ";
				foreach($randomQuestionList as $k=>$v)	{
						$sql.="({$userId},{$this->id},{$v},{$k},{$from_evaluation}),";
					}
				$sql = trim($sql,",");
				
				Database::query($sql);
				
				return $randomQuestionList;
			}
            
		}



		/**
         * Reset to 0 random_order field of table quiz_rel_question 
         *
         * @author - Juan Carlos Medina Orihuela
         * 
         * 
         */
         
         function resetRandomOrder($quizId,$from_evaluation=0)
         {
			 $userId = api_get_user_id();
			 $TBL_QUIZ_QUESTION_REL_USER = Database::get_course_table(TABLE_QUIZ_QUESTION_REL_USER, $this->db_name);
			 $sql = "DELETE FROM {$TBL_QUIZ_QUESTION_REL_USER} WHERE user_id = {$userId} AND quiz_id={$quizId} AND from_evaluation={$from_evaluation}";
			 Database::query($sql);
		 }


        function selectRandomList($shuffledQuestionList=array()) {
			
			return (count($shuffledQuestionList) ? $this->selectRandomList2($shuffledQuestionList) : $this->selectRandomList1());
		}
        

        /**
         * selects questions randomly in the question list
         *
         * @author - Olivier Brouckaert
         * @return - array - if the exercise is not set to take questions randomly, returns the question list
         * 					 without randomizing, otherwise, returns the list with questions selected randomly
         */
        function selectRandomList1() {
            $nbQuestions = $this->selectNbrQuestions();
            $temp_list = $this->questionList;
            if (count($temp_list) <> 0) {
                shuffle($temp_list);
                return array_combine(range(1, $nbQuestions), $temp_list);
            }


            $nbQuestions = $this->selectNbrQuestions();

            //Not a random exercise, or if there are not at least 2 questions
            if ($this->random == 0 || $nbQuestions < 2) {
                return $this->questionList;
            }

            $randQuestionList = array();
            $alreadyChosen = array();

            for ($i = 0; $i < $this->random; $i++) {
                if ($i < $nbQuestions) {
                    do {
                        $rand = rand(1, $nbQuestions);
                    } while (in_array($rand, $alreadyChosen));

                    $alreadyChosen[] = $rand;
                    $randQuestionList[$rand] = $this->questionList[$rand];
                }
            }

            return $randQuestionList;
        }
        
        
        function selectRandomList2($from_evaluation=0) {
            $randomQuestionList = $this->getRandomOrder($from_evaluation);
            if(!count($randomQuestionList))
				$randomQuestionList = $this->setRandomOrder($from_evaluation);
            
            return $randomQuestionList;
         }
        

        /**
         * Check if this quiz is added as exam in evaluation tool
         */
        function isQuizAsExam($quizId) {
            $tbl_exam = Database::get_course_table(TABLE_EXAM, $this->db_name);
            $rs = Database::query("SELECT id FROM $tbl_exam WHERE quiz_id=".intval($quizId));
            return (bool)Database::num_rows($rs);
        }
        
        /**
         * returns 'true' if the question ID is in the question list
         *
         * @author - Olivier Brouckaert
         * @param - integer $questionId - question ID
         * @return - boolean - true if in the list, otherwise false
         */
        function isInList($questionId) {
            if (is_array($this->questionList))
                return in_array($questionId, $this->questionList);
            else
                return false;
        }

        /**
         * changes the exercise title
         *
         * @author - Olivier Brouckaert
         * @param - string $title - exercise title
         */
        function updateTitle($title) {
            $this->exercise = $title;
        }
        function updateSimplifymode($simplifymode) {
            $this->simplifymode = $simplifymode;
        }
        /**
         * changes the exercise scenario
         *
         * @author - Isaac flores
         * @param - int $scenario - exercise scenario
         */
        function updateScenario($scenario) {
            $this->scenario = $scenario;
        }

        /**
         * changes the exercise max attempts
         *
         * @param - numeric $attempts - exercise max attempts
         */
        function updateAttempts($attempts) {
            $this->attempts = $attempts;
        }

        /**
         * changes the exercise feedback type
         *
         * @param - numeric $attempts - exercise max attempts
         */
        function updateFeedbackType($feedback_type) {
            $this->feedbacktype = $feedback_type;
        }

        /**
         * changes the exercise description
         *
         * @author - Olivier Brouckaert
         * @param - string $description - exercise description
         */
        function updateDescription($description) {
            $this->description = $description;
        }

		function updateQuizFinalFeedback($quiz_final_feedback) {
            $this->quiz_final_feedback = $quiz_final_feedback;
        }

        function updateExpiredTime($expired_time) {
            $this->expired_time = $expired_time;
        }

        function updateCertifTemplate($certif_template) {
            $this->certif_template = $certif_template;
        }

        function updateCertifMinScore($certif_min_score) {
            $this->certif_min_score = $certif_min_score;
        }

        /**
         * changes the exercise score pass
         *
         * @param - numeric $score_pass - exercise score pass
         */
        function updateScorePass($score_pass) {
            $this->score_pass = $score_pass;
        }

        /**
         * changes the exercise quiz type
         *
         * @param - numeric $quiz_type - exercise quiz type
         */
        function updateQuizType($quiz_type) {
            $this->quiz_type = $quiz_type;
        }

        /**
         * changes the exercise sound file
         *
         * @author - Olivier Brouckaert
         * @param - string $sound - exercise sound file
         * @param - string $delete - ask to delete the file
         */
        function updateSound($sound, $delete) {
            global $audioPath, $documentPath, $_course, $_user;
            $TBL_DOCUMENT = Database::get_course_table(TABLE_DOCUMENT, $this->db_name);
            $TBL_ITEM_PROPERTY = Database::get_course_table(TABLE_ITEM_PROPERTY, $this->db_name);

            if ($sound['size'] && (strstr($sound['type'], 'audio') || strstr($sound['type'], 'video'))) {
                $this->sound = $sound['name'];

                if (@move_uploaded_file($sound['tmp_name'], $audioPath . '/' . $this->sound)) {
                    $query = "SELECT 1 FROM $TBL_DOCUMENT "
                            . " WHERE path='" . str_replace($documentPath, '', $audioPath) . '/' . $this->sound . "'";
                    $result = api_sql_query($query, __FILE__, __LINE__);

                    if (!mysql_num_rows($result)) {
                        /* $query="INSERT INTO $TBL_DOCUMENT(path,filetype) VALUES "
                          ." ('".str_replace($documentPath,'',$audioPath).'/'.$this->sound."','file')";
                          api_sql_query($query,__FILE__,__LINE__); */
                        $id = add_document($_course, str_replace($documentPath, '', $audioPath) . '/' . $this->sound, 'file', $sound['size'], $sound['name']);

                        //$id = Database::get_last_insert_id();
                        //$time = time();
                        //$time = date("Y-m-d H:i:s", $time);
                        // insert into the item_property table, using default visibility of "visible"
                        /* $query = "INSERT INTO $TBL_ITEM_PROPERTY "
                          ."(tool, ref, insert_user_id,to_group_id, insert_date, lastedit_date, lastedit_type) "
                          ." VALUES "
                          ."('".TOOL_DOCUMENT."', $id, $_user['user_id'], 0, '$time', '$time', 'DocumentAdded' )";
                          api_sql_query($query,__FILE__,__LINE__); */
                        api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAdded', $_user['user_id']);
                        item_property_update_on_folder($_course, str_replace($documentPath, '', $audioPath), $_user['user_id']);
                    }
                }
            } elseif ($delete && is_file($audioPath . '/' . $this->sound)) {
                $this->sound = '';
            }
        }

        /**
         * changes the exercise type
         *
         * @author - Olivier Brouckaert
         * @param - integer $type - exercise type
         */
        function updateType($type) {
            $this->type = $type;
        }

        /**
         * sets to 0 if questions are not selected randomly
         * if questions are selected randomly, sets the draws
         *
         * @author - Olivier Brouckaert
         * @param - integer $random - 0 if not random, otherwise the draws
         */
        function setRandom($random) {
            $this->random = $random;
        }

        /**
         * enables the exercise
         *
         * @author - Olivier Brouckaert
         */
        function enable() {
            $this->active = 1;
        }

        /**
         * disables the exercise
         *
         * @author - Olivier Brouckaert
         */
        function disable() {
            $this->active = 0;
        }

        function disable_results() {
            $this->results_disabled = true;
        }

        function enable_results() {
            $this->results_disabled = false;
        }

        function updateResultsDisabled($results_disabled) {
            if ($results_disabled == 1) {
                $this->results_disabled = true;
            } else {
                $this->results_disabled = false;
            }
        }

        /**
         * updates the exercise in the data base
         *
         * @author - Olivier Brouckaert
         */
        function save($type_e = '') {
            global $_course, $_user, $charset;
            $TBL_EXERCICES = Database::get_course_table(TABLE_QUIZ_TEST, $this->db_name);
            $TBL_QUESTIONS = Database::get_course_table(TABLE_QUIZ_QUESTION, $this->db_name);
            $TBL_QUIZ_QUESTION = Database::get_course_table(TABLE_QUIZ_TEST_QUESTION, $this->db_name);

            $id = $this->id;
            $exercise = $this->exercise;
            $simplifymode = $this->simplifymode;            
            $description = $this->description;
            $sound = $this->sound;
            $type = $this->type;
            $attempts = $this->attempts;
            $feedbacktype = $this->feedbacktype;
            $random = $this->random;
            $active = $this->active;
            $session_id = api_get_session_id();
            $expired_time = $this->expired_time;
            $scenario = $this->scenario;
            $certif_template = $this->certif_template;
            $certif_min_score = $this->certif_min_score;
            $score_pass = $this->score_pass;
            $quiz_type = $this->quiz_type;
			$quiz_final_feedback = $this->quiz_final_feedback;

            if ($feedbacktype == 1) {
                $results_disabled = 1;
            } else {
                $results_disabled = intval($this->results_disabled);
            }
            // exercise already exists
            if ($id) {

                /*
                  title='".Database::escape_string(Security::remove_XSS($exercise))."',
                  description='".Database::escape_string(Security::remove_XSS(api_html_entity_decode($description),COURSEMANAGERLOWSECURITY))."'";
                 */
                $sql = "UPDATE $TBL_EXERCICES SET
						title='" . Database::escape_string($exercise) . "',
                                                simplifymode='" .Database::escape_string($simplifymode) . "',
						description='" . Database::escape_string($description) . "',
						quiz_final_feedback='" . Database::escape_string($quiz_final_feedback) . "'";
                
                

                if ($type_e != 'simple') {
                    $sql .= ", sound='" . Database::escape_string($sound) . "',
						type='" . Database::escape_string($type) . "',
						random='" . Database::escape_string($random) . "',
						active='" . Database::escape_string($active) . "',
						feedback_type='" . Database::escape_string($feedbacktype) . "',
						start_time='" . Database::escape_string($this->start_time) . "',end_time='" . Database::escape_string($this->end_time) . "',
						max_attempt='" . Database::escape_string($attempts) . "',
                        certif_template ='" . Database::escape_string($certif_template) . "',
                        certif_min_score ='" . Database::escape_string($certif_min_score) . "',
                        session_id ='" . $session_id . "',
						expired_time='" . Database::escape_string($expired_time) . "',
						score_pass='" . Database::escape_string($score_pass) . "',
						quiz_type='" . Database::escape_string($quiz_type) . "'," .
                            "results_disabled='" . Database::escape_string($results_disabled) . "'";
                }
                $sql .= " WHERE id='" . Database::escape_string($id) . "'";
                //echo($sql);exit;
                api_sql_query($sql, __FILE__, __LINE__);

                // Update into the item_property table
                api_item_property_update($_course, TOOL_QUIZ, $id, 'QuizUpdated', $_user['user_id']);

                // Update data into quiz scenario table
                $quiz_info = array();
                $quiz_info['quiz_id'] = $id;
                $quiz_info['scenario'] = $scenario;
                $quiz_info['start_time'] = $this->start_time;
                $quiz_info['end_time'] = $this->end_time;
                $quiz_info['title'] = $exercise;
                $quiz_info['simplifymode'] = $simplifymode;
                
                $quiz_info['description'] = $description;
                $quiz_info['sound'] = $sound;
                $quiz_info['type'] = $type;
                $quiz_info['random'] = $random;
                $quiz_info['active'] = $active;
                $quiz_info['results_disabled'] = $results_disabled;
                $quiz_info['attempts'] = $attempts;
                $quiz_info['feedback'] = $feedbacktype;
                $quiz_info['expired_time'] = $expired_time;
                $quiz_info['certif_template'] = $certif_template;
                $quiz_info['certif_min_score'] = $certif_min_score;
                $quiz_info['score_pass'] = $score_pass;
                $quiz_info['quiz_type'] = $quiz_type;
				$quiz_info['quiz_final_feedback'] = $quiz_final_feedback;

                $quiz_data = (object) $quiz_info;

                // Update scenario
                $this->update_scenario($quiz_data);

                if (api_get_setting('search_enabled') == 'true') {
                    $this->search_engine_edit();
                }
            } else {// creates a new exercise
                //add condition by anonymous user
                $type = '2';
                $feedbacktype = '3';
                $score_pass = 50;
                $simplifymode = (api_get_setting('enable_pro_settings')=='true') ? '0' : '0';
                //$quiz_type = '1';

                // get last position
                $sql = 'SELECT MAX(position)+1 as newPosition FROM ' . $TBL_EXERCICES;
                $rs = api_sql_query($sql, __FILE__, __LINE__);
                $newPosition = Database::result($rs, 0);

                $i = 0;
                $name = Database::escape_string($exercise);
                $check_name = "SELECT * FROM $TBL_EXERCICES WHERE title = '$name'";
                $res_name = api_sql_query($check_name, __FILE__, __LINE__);
                while (Database :: num_rows($res_name)) {
                    //there is already one such name, update the current one a bit
                    $i++;
                    $parts = explode(' - ', $name);
                    if (count($parts) > 1) {
                        $parts[count($parts) - 1] = $i;
                        $name = implode(' - ', $parts);
                    } else {
                        $name = $name . ' - ' . $i;
                    }
                    //$name = $name . ' - ' . $i;

                    $check_name = "SELECT * FROM $TBL_EXERCICES WHERE title = '$name'";
                    $res_name = api_sql_query($check_name, __FILE__, __LINE__);
                }


                $sql = "INSERT INTO $TBL_EXERCICES (start_time,end_time,title,description,sound,type,random,active, results_disabled, max_attempt,feedback_type,expired_time, position, certif_template, certif_min_score, score_pass, quiz_type,quiz_final_feedback, session_id, simplifymode)
					VALUES(
						'$start_time','$end_time',
						'" . $name . "',
						'" . Database::escape_string($description) . "',
						'" . Database::escape_string($sound) . "',
						'" . Database::escape_string($type) . "',
						'" . Database::escape_string($random) . "',
						'" . Database::escape_string($active) . "',
						'" . Database::escape_string($results_disabled) . "',
						'" . Database::escape_string($attempts) . "',
						'" . Database::escape_string($feedbacktype) . "',
						'" . Database::escape_string($expired_time) . "',
						" . intval($newPosition) . ",
                                                '" . Database::escape_string($certif_template) . "',
                                                '" . Database::escape_string($certif_min_score) . "',
						'" . Database::escape_string($score_pass) . "',
						'" . Database::escape_string($quiz_type) . "',
						'" . Database::escape_string($quiz_final_feedback) . "',
                                                '" . $session_id . "',
                                                '".$simplifymode."'                                                    
						)";
                api_sql_query($sql, __FILE__, __LINE__);
                $this->id = Database::insert_id();

                // insert into the item_property table
                api_item_property_update($_course, TOOL_QUIZ, $this->id, 'QuizAdded', $_user['user_id']);

                // Add data into quiz scenario table
                $quiz_info = array();
                $quiz_info['quiz_id'] = $this->id;
                $quiz_info['start_time'] = $start_time;
                $quiz_info['end_time'] = $end_time;
                $quiz_info['title'] = $name;
                $quiz_info['description'] = $description;
                $quiz_info['sound'] = $sound;
                $quiz_info['type'] = $type;
                $quiz_info['random'] = $random;
                $quiz_info['active'] = $active;
                $quiz_info['results_disabled'] = $results_disabled;
                $quiz_info['attempts'] = $attempts;
                $quiz_info['feedback'] = $feedbacktype;
                $quiz_info['expired_time'] = $expired_time;
                $quiz_info['certif_template'] = $certif_template;
                $quiz_info['certif_min_score'] = $certif_min_score;
                $quiz_info['score_pass'] = $score_pass;
                $quiz_info['quiz_type'] = $quiz_type;
				$quiz_info['quiz_final_feedback'] = $quiz_final_feedback;
                $quiz_info['simplifymode'] = $simplifymode;

                // Add scenarios to quiz
                $quiz_data = (object) $quiz_info;
                $this->save_scenario($quiz_data);
                // Add quiz into Learning Path
                $this->save_quiz_into_learning_path($quiz_data);

                if (api_get_setting('search_enabled') == 'true' && extension_loaded('xapian')) {
                    $this->search_engine_save();
                }
            }

            

            // updates the question position
            //$this->update_question_positions();
        }

        function update_question_positions() {
            // updates the question position
            $TBL_QUIZ_QUESTION = Database::get_course_table(TABLE_QUIZ_TEST_QUESTION, $this->db_name);
            foreach ($this->questionList as $position => $questionId) {
                //$sql="UPDATE $TBL_QUESTIONS SET position='".Database::escape_string($position)."' WHERE id='".Database::escape_string($questionId)."'";
                $sql = "UPDATE $TBL_QUIZ_QUESTION SET question_order='" . Database::escape_string($position) . "' " .
                        "WHERE question_id='" . Database::escape_string($questionId) . "' and exercice_id=" . Database::escape_string($this->id) . "";
                api_sql_query($sql, __FILE__, __LINE__);
            }
            
            // for some strange reason sometimes there is no question_order = 1, so a reorder is forced
            
            $sql = "SELECT question_id,question_order FROM {$TBL_QUIZ_QUESTION} WHERE exercice_id = {$this->id} ORDER BY question_order";
            $result = Database::query($sql);
            $sumOrder = 0;
            $sumIter = 0;
            $iter = 0;
            
            $sqlUpdate = " UPDATE {$TBL_QUIZ_QUESTION} SET question_order = CASE question_id ";
            $questionId = array();
            while($object = Database::fetch_object($result))
            {
				
				
				$iter += 1;
				$sumOrder += $object->question_order;
				$sumIter += $iter;
				
				$sqlUpdate .= " WHEN {$object->question_id} THEN {$iter} ";
				$questionId[] = $object->question_id;
			}
			
			$sqlUpdate .=" END WHERE question_id IN (".implode(",",$questionId).")";
			
			if(($sumIter-$sumOrder)!=0){
				
				Database::query($sqlUpdate);
				
            }
        }

        /**
         * Update the scenario of quiz
         * @param object
         * @return boolean
         */
        function update_scenario($quiz) {
            $tbl_quiz_scenario = Database::get_course_table(TABLE_QUIZ_SCENARIO, $this->db_name);
            $session_id = api_get_session_id();
            $sql = "UPDATE $tbl_quiz_scenario SET
						title='" . Database::escape_string($quiz->title) . "',
						description='" . Database::escape_string($quiz->description) . "'";
            $sql .= ", sound='" . $quiz->sound . "',
						type='" . $quiz->type . "',
						random='" . $quiz->random . "',
						active='" . $quiz->active . "',
						feedback_type='" . $quiz->feedback . "',
						start_time='$quiz->start_time',end_time='$quiz->end_time',
						max_attempt='" . $quiz->attempts . "',
                        certif_template ='" . $quiz->certif_template . "',
                        certif_min_score ='" . $quiz->certif_min_score . "',
						score_pass='" . $quiz->score_pass . "',
						quiz_type='" . $quiz->quiz_type . "',
						quiz_final_feedback='" . $quiz->quiz_final_feedback . "',
						expired_time='" . $quiz->expired_time . "',
                                                    simplifymode='" . $quiz->simplifymode . "'," .
                                                
                    "results_disabled='" . $quiz->results_disabled . "'";
            $sql .= " WHERE exercice_id='" . $quiz->quiz_id . "' AND scenario_type='" . $quiz->scenario . "' ";

            $rs = Database::query($sql, __FILE__, __LINE__);
        }

        /**
         * moves a question up in the list
         *
         * @author - Olivier Brouckaert
         * @author - Julio Montoya (rewrote the code)
         * @param - integer $id - question ID to move up
         */
        function moveUp($id) {
            // there is a bug with some version of PHP with the key and prev functions
            // the script commented was tested in dev.dokeos.com with no success
            // Instead of using prev and next this was change with arrays.
            $question_list = array();
            foreach ($this->questionList as $position => $questionId) {
                $question_list[] = $questionId;
            }
            $len = count($question_list);
            $orderlist = array_keys($this->questionList);
            for ($i = 0; $i < $len; $i++) {
                $questionId = $question_list[$i];
                if ($questionId == $id) {
                    // position of question in the array
                    $pos1 = $orderlist[$i];
                    $pos2 = $orderlist[$i - 1];
                    if ($pos2 === null) {
                        $pos2 = $orderlist[$len - 1];
                    }
                    // error, can't move question
                    if (!$pos2) {
                        $pos2 = $orderlist[0];
                        $i = 0;
                    }
                    break;
                }
            }
            // permutes questions in the array
            $temp = $this->questionList[$pos2];
            $this->questionList[$pos2] = $this->questionList[$pos1];
            $this->questionList[$pos1] = $temp;
        }

        /**
         * moves a question down in the list
         *
         * @author - Olivier Brouckaert
         * @param - integer $id - question ID to move down
         */
        function moveDown($id) {
            // there is a bug with some version of PHP with the key and prev functions
            // the script commented was tested in dev.dokeos.com with no success
            // Instead of using prev and next this was change with arrays.
            $question_list = array();
            foreach ($this->questionList as $position => $questionId) {
                $question_list[] = $questionId;
            }
            $len = count($question_list);
            $orderlist = array_keys($this->questionList);

            for ($i = 0; $i < $len; $i++) {
                $questionId = $question_list[$i];
                if ($questionId == $id) {
                    $pos1 = $orderlist[$i + 1];
                    $pos2 = $orderlist[$i];
                    if (!$pos2) {
                        //echo 'cant move!';
                    }
                    break;
                }
            }

            // permutes questions in the array
            $temp = $this->questionList[$pos2];
            $this->questionList[$pos2] = $this->questionList[$pos1];
            $this->questionList[$pos1] = $temp;
        }

        /**
         * adds a question into the question list
         *
         * @author - Olivier Brouckaert
         * @param - integer $questionId - question ID
         * @return - boolean - true if the question has been added, otherwise false
         */
        function addToList($questionId) {
            // checks if the question ID is not in the list
            if (!$this->isInList($questionId)) {
                // selects the max position
                if (!$this->selectNbrQuestions()) {
                    $pos = 1;
                } else {
                    if (is_array($this->questionList))
                        $pos = max(array_keys($this->questionList)) + 1;
                }

                $this->questionList[$pos] = $pos;

                return true;
            }

            return false;
        }

        /**
         * removes a question from the question list
         *
         * @author - Olivier Brouckaert
         * @param - integer $questionId - question ID
         * @return - boolean - true if the question has been removed, otherwise false
         */
        function removeFromList($questionId) {           
            // searches the position of the question ID in the list
            $pos = array_search($questionId, $this->questionList);

            // question not found
            if ($pos === false) {
                return false;
            } else {
                // deletes the position from the array containing the wanted question ID
                unset($this->questionList[$pos]);

                return true;
            }
        }

        /**
         * deletes the exercise from the database
         * Notice : leaves the question in the data base
         *
         * @author - Olivier Brouckaert
         */
        function delete() {
            global $_course, $_user;
            $TBL_EXERCICES = Database::get_course_table(TABLE_QUIZ_TEST, $this->db_name);

            //select current position to update the global order
            $sql = 'SELECT position FROM ' . $TBL_EXERCICES . ' WHERE id=' . intval($this->id);
            $rs = api_sql_query($sql, __FILE__, __LINE__);
            $position = Database::result($rs, 0);
            $sql = 'UPDATE ' . $TBL_EXERCICES . ' SET position = position -1 WHERE position > ' . intval($position);
            api_sql_query($sql, __FILE__, __LINE__);

            $sql = "UPDATE $TBL_EXERCICES SET active='-1', position=0 WHERE id='" . Database::escape_string($this->id) . "'";
            api_sql_query($sql, __FILE__, __LINE__);
            $_SESSION['display_confirmation_message']=get_lang('ExerciseDeleted');
            api_item_property_update($_course, TOOL_QUIZ, $this->id, 'QuizDeleted', $_user['user_id']);

            if (api_get_setting('search_enabled') == 'true' && extension_loaded('xapian')) {
                $this->search_engine_delete();
            }
        }

        /**
         * Creates the scenario form to create / edit an exercise
         * @param FormValidator $form the formvalidator instance (by reference)
         */
        function createScenarioForm($form) {
			
			
			
            global $_course;

            if (api_get_setting('enable_exammode') === 'true') {
                $rowspan_quiztype = 3;
                $rowspan_score = 5;
            } else {
                $rowspan_quiztype = 2;
                $rowspan_score = 4;
            }

            $rs = (api_get_setting('enable_pro_settings')=='true') ? '5' : '4';
            
            $renderer = &$form->defaultRenderer();
            $form->addElement('style_submit_button', 'submit', get_lang('Ok'), 'class="add" style="clear:both;margin-bottom:10px;"');
            $form->addElement('html', '<div class="content-scenario" style="clear:both;">');

            $form->addElement('html', '<div class="content-scenario-block">');

            $attempt_option = range(0, 10);
            $attempt_option[0] = get_lang('Infinite');
            $form->addElement('html', '<table width= "100%">');
            $form->addElement('html', '<tr> ');

            $form->addElement('html', '<td width="186px" valign="top"><br />' . get_lang('ExerciseName') . '</td>
                               <td valign="top">');
            $form->addElement('text', 'exerciseTitle', null, 'style="width:370px"');
            $form->addElement('html', '</td>
			       <td style="text-align:center" rowspan="'.$rs.'" width="290px" valign="middle">' . Display::return_icon('quizgallery/detail_modify.png') . '</td>
			      </tr>');

            if(api_get_setting('enable_pro_settings')=='true'){
                $form->addElement('html', '<td width="186px" valign="top"><br />' . get_lang('SimplifyQuizQuestionsAuthoring') . '</td><td valign="top">');
             $form->addElement('checkbox', 'simplifymode',null , null, null);
                $form->addElement('html', '</td>');
            }                  
			       
            
           
            
            $form->addElement('html', '<tr>');

            $form->addElement('html', '<td  valign="top"><br />' . get_lang('ExerciseDescription') . '</td>
                                   <td valign="top">');
            $form->add_html_editor('exerciseDescription', '', false, false, array('ToolbarSet' => 'QuizScenario', 'Width' => '370', 'Height' => '70'));
            $form->addElement('html', '</td>
				</tr>');

			$form->addElement('html', '<tr>');

            $form->addElement('html', '<td  valign="top"><br />' . get_lang('EndOfQuizFeedback') . '</td>
                                   <td valign="top">');
            $form->add_html_editor('endQuizFeedback', '', false, false, array('ToolbarSet' => 'QuizScenario', 'Width' => '370', 'Height' => '70'));
            $form->addElement('html', '</td>
				</tr>');

            /////////////////////////
            //
            /*
             * Chronometer
             */
                if (!is_null($this->scenario)) {
                 $timer_id = 'enabletimercontroltotalminutes'.$this->scenario;
//                 $form -> addElement ('hidden','scenario',$this->scenario);
//                 if ($this->scenario == 1) {
//                  /*$title_scenario = get_lang('QuizSelfEvaluationTitle');
//                  $body_scenario = get_lang('QuizSelfEvaluationMessage');
//                  $scenario_image = "../img/self_eval.png";*/
//                      $title_scenario = get_lang('QuizScenario');
//                      $body_scenario = get_lang('QuizSelfEvaluationMessage');
//                      $scenario_image = "../img/dokeos_exam.png";
//                 } elseif ($this->scenario == 2) {
//                  $title_scenario = get_lang('QuizExamTitle');
//                  $body_scenario = get_lang('QuizExamMessage');
//                  $scenario_image = "../img/dokeos_exam.png";
//                 }
                }
            $timer = array();
            $timer[] = FormValidator :: createElement('text', 'enabletimercontroltotalminutes', get_lang('Minutes'), array('style' => 'width : 35px', 'id' => $timer_id));
            $timer[] = FormValidator :: createElement('static', 'minutes', get_lang('Minutes'));
            
            
            $form->addElement('html', '<tr> ');
            $form->addElement('html', '<td width="186px" valign="top"><br />' . get_lang('Chronometer') . '</td>
                               <td valign="top">');
            $form->addGroup($timer, null, get_lang('Chronometer'), get_lang('Minutes'));
            $form->addElement('html', ' </table>');
            $form->addElement('html', '</div>'); 
            
            //$form->addGroup($timer, null, get_lang('Chronometer'), '&nbsp;&nbsp;' . get_lang('Minutes'));
          
            //$form->addElement('html', '<div class="content-scenario" style="clear:both;">');
            //$form->addElement('html', '<div class="content-scenario-block" style="min-height: 70px !important;">');
            //$form->addElement('html', '<table width= "100%">');
            
            /* End Chronometer
             * ************************************************** *
             */
    //
    // Mode Block
         if (0) { // this option will be added in evaluation feature
            $form->addElement('html', '<h3>' . get_lang('Mode') . '</h3>');
            $form->addElement('html', '<div class="content-scenario-block">');

            $attempt_option = range(0, 10);
            $attempt_option[0] = get_lang('Infinite');
            $form->addElement('html', '<table width= "100%">');
            $form->addElement('html', '		<tr>
                                        <td valign="top">');
            $form->addElement('radio', 'quizType', '', '', 1);
            $form->addElement('html', '         </td>
                                        <td width="150px" valign="top"><br />' . get_lang('QuizMode') . '</td>
                                        <td valign="top">' . get_lang('QuizSelfLearningModeDesc') . '</td>
										<td rowspan="' . $rowspan_quiztype . '" width="290px" valign="middle">' . Display::return_icon('quizgallery/answer_unknow.png') . '</td>
								    </tr>');
            if (api_get_setting('enable_exammode') === 'true') {
                $form->addElement('html', '     <tr>
                                        <td width="18px" valign="top">');
                $form->addElement('radio', 'quizType', '', '', 2);
                $form->addElement('html', '         </td>
                                        <td width="150px" valign="top"><br />' . get_lang('ExamMode') . '</td>
                                        <td valign="top">' . get_lang('QuizExamModeDesc') . '</td>
									</tr>');
            }
            $form->addElement('html', '     <tr>
                                        <td>&nbsp;</td>
                                        <td valign="top">' . get_lang('ExerciseAttempts') . '<br />');
            $form->addElement('select', 'exerciseAttempts', '', $attempt_option);
            $form->addElement('html', '         </td>
                                        <td valign="top">' . get_lang('QuizExerciseAttemptsDesc') . '</td>
                                    </tr>
                                </table>');

            $form->addElement('html', '</div>'); // end content block
            // Time Block
            $form->addElement('html', '<h3>' . get_lang('Time') . '</h3>');
            $form->addElement('html', '<div class="content-scenario-block">');

            $duration_option = range(0, 120);
            $duration_option[0] = get_lang('NoTimer');
            $form->addElement('html', '<table width= "100%">
                                    <tr>
                                        <td width="18px" valign="top">&nbsp;</td>
                                        <td width="150px" valign="top">' . get_lang('MaxDurationMinutos'));
            $form->addElement('select', 'enabletimercontroltotalminutes', '', $duration_option);
            $form->addElement('html', '         </td>
                                        <td valign="top">' . get_lang('QuizMaxDurationMinutosDesc') . '</td>
                                        <td rowspan="5" valign="middle" width="290px">' . Display::return_icon('quizgallery/time_out.png') . '</td>
                                    </tr>
                                    <tr>
                                        <td valign="top">');
            $form->addElement('radio', 'enabletimelimit', '', '', 0);
            $form->addElement('html', '         </td>
                                        <td valign="top"><br />' . get_lang('NoAgenda') . '</td>
                                        <td valign="top">' . get_lang('QuizNoAgendaDesc') . '</td>
                                    </tr>
                                    <tr>
                                        <td valign="top">');
            $form->addElement('radio', 'enabletimelimit', '', '', 1);
            $form->addElement('html', '         </td>
                                        <td valign="top"><br />' . get_lang('AvailabilityLimited') . '</td>
                                        <td valign="top">' . get_lang('QuizAvailabilityLimitedDesc') . '</td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td valign="top" align="right">' . get_lang('ExeStartTime') . '</td>
                                        <td valign="top">');
            $form->addElement('datepicker', 'start_time', '', array('form_name' => 'exercice_scenario' . $this->scenario));
            $form->addElement('html', '         </td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td valign="top" align="right">' . get_lang('ExeEndTime') . '</td>
                                        <td valign="top">');
            $form->addElement('datepicker', 'end_time', '', array('form_name' => 'exercice_scenario' . $this->scenario));
            $form->addElement('html', '         </td>
                                    </tr>
                                </table>');
            $form->addElement('html', '</div>');    
         }
            
            // Randomize questions block
            $form->addElement('html', '<h3>' . get_lang('RandomizeQuestions') . '</h3>');
            $form->addElement('html', '<div class="content-scenario-block">');

            $random = array();
            $option = array();
            $nbrQuestions = $this->selectNbrQuestions();
            $max = ($this->id > 0) ? $nbrQuestions : 10;
            
            
            
            if ($max > 1) {
                $values = range(1, $max);
                $option = array_combine($values, $values);
            }
            $option[$max] = get_lang('All');

            $disabled_rnd = false;
            if (empty($this->random)) {
                $disabled_rnd = true;
            }
            $form->addElement('html', '<table width= "100%">
                                    <tr>
                                        <td width="18px" valign="top">');
            $form->addElement('radio', 'randomQuestionsOpt', '', '', 1);
            $form->addElement('html', '         </td>
                                        <td width="150px" valign="top"><br />' . get_lang('Yes') . '</td>
                                        <td valign="top">' . get_lang('QuizRandomQuestionsOptYesDesc') . '</td>
                                        <td style="text-align:center" rowspan="3" valign="middle" width="290px">' . Display::return_icon('quizgallery/answer_shuffle.png') . '</td>
                                    </tr>
                                    <tr>
                                        <td valign="top">');
            $form->addElement('radio', 'randomQuestionsOpt', '', '', 0);
            $form->addElement('html', '         </td>
                                        <td valign="top"><br />' . get_lang('No') . '</td>
                                        <td valign="top">' . get_lang('QuizRandomQuestionsOptNoDesc') . '</td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td valign="top">' . get_lang('LimitSet') . '<br />');
            $form->addElement('select', 'randomQuestions', '', $option, array(/*'disabled' => $disabled_rnd ? 'true' : 'false'*/));
            $form->addElement('html', '         </td>
                                        <td valign="top">' . get_lang('QuizLimitSetDesc') . '</td>
                                    </tr>
                                </table>');

            $form->addElement('html', '</div>'); // end content block
            // Feedback block
            $form->addElement('html', '<h3>' . get_lang('Feedback') . '</h3>');
            $form->addElement('html', '<div class="content-scenario-block">');
            $options = array('0' => '0', '50' => '50', '55' => '55', '60' => '60', '65' => '65', '70' => '70', '75' => '75', '80' => '80', '85' => '85', '90' => '90', '95' => '95', '100' => '100');
            $form->addElement('html', '<table width= "100%">
                                    <tr>
                                        <td width="18px" valign="top">');
            $form->addElement('radio', 'exerciseFeedbackType', '', '', 2);
            $form->addElement('html', '         </td>
                                        <td width="150px" valign="top"><br />' . get_lang('NoFeedback') . '</td>
                                        <td valign="top">' . get_lang('QuizNoFeedbackDesc') . '</td>
                                        <td style="text-align:center;" rowspan="' . $rowspan_score . '" valign="middle" width="290px">' . Display::return_icon('quizgallery/avatar_mirrow.png') . '</td>
                                    </tr>
                                    <tr>
                                        <td valign="top">');
            $form->addElement('radio', 'exerciseFeedbackType', '', '', 0);
            $form->addElement('html', '         </td>
                                        <td valign="top"><br />' . get_lang('ExerciseAtTheEndOfTheTest') . '</td>
                                        <td valign="top">' . get_lang('QuizExerciseAtTheEndOfTheTestDesc') . '</td>
                                    </tr>
                                    <tr>
                                        <td valign="top">');
            $form->addElement('radio', 'exerciseFeedbackType', '', '', 3);
            $form->addElement('html', '         </td>
                                        <td valign="top"><br />' . get_lang('DirectFeedback') . '</td>
                                        <td valign="top">' . get_lang('QuizDirectFeedbackDesc') . '</td>
                                    </tr>
                                    <tr>
                                        <td valign="top">');
            $form->addElement('checkbox', 'showScore', 1, null, array('checked' => 'checked'));
            $form->addElement('html', '         </td>
                                        <td valign="top"><br />' . get_lang('ShowScore') . '</td>
                                        <td valign="top">' . get_lang('QuizShowScoreDesc') . '</td>
                                    </tr>');
            if (api_get_setting('enable_exammode') === 'true') {
                $form->addElement('html', '         <tr>
                                  
                                    </tr>');
            }
            $form->addElement('html', '     </table>');


            /////////////////// SHOW CERTIFICATE ///////////////////////////////

            if (0) {  // this option will be added in evaluation feature
                // certificate option
                require_once api_get_path(LIBRARY_PATH) . 'certificatemanager.lib.php';

                $current_language = !empty($_course['language']) ? $_course['language'] : api_get_interface_language();
                $certificates = CertificateManager::create()->getCertificatesList($current_language);
                $selCertificates = array();
                if (!empty($certificates)) {
                    $selCertificates[0] = get_lang('None');
                    foreach ($certificates as $tpl_id => $certificate) {
                        $selCertificates[$tpl_id] = $certificate['title'];
                    }
                } else {
                    $certificates = CertificateManager::create()->getCertificatesList('english');
                    $selCertificates[0] = get_lang('None');
                    foreach ($certificates as $tpl_id => $certificate) {
                        $selCertificates[$tpl_id] = $certificate['title'];
                    }
                }
                $certif_thumb = '';
                if (!empty($this->certif_template)) {
                    $certif_thumb = CertificateManager::create()->returnCertificateThumbnailImg($this->certif_template);
                }
                $objQuiz = $this->getTemplateById($this->id);
                if($objQuiz->certif_template > 0){
                // Feedback block
                $form->addElement('html', '<h3>' . get_lang('Certificate') . '</h3>');
                $form->addElement('html', '<div class="content-scenario-block">');
                $form->addElement('html', '<table width= "100%">
                                    <tr>
                                        <td valign="top" width="22px"></td>');
                $form->addElement('html', '         <td valign="top" width="150px">');
                $form->addElement('select', 'certificate_template', get_lang('Certificate'), $selCertificates, array('id' => 'quiz-certificate'));
                $form->addElement('html', '</td>
                                        <td valign="top">');
                $form->addElement('html', '             <div id="quiz-certificate-thumb" style="' . (!empty($this->certif_template) ? 'display:block;' : 'display:none;') . '">' . $certif_thumb . '</div>');
                $form->addElement('html', '         </td>
                                         <td rowspan="2" valign="middle" width="290px">' . Display::return_icon('quizgallery/avatar_certificate.png') . '</td>
                                    </tr>
                                    <tr>
                                        <td valign="top"></td>');

                $form->addElement('html', '         <td valign="top" align="right">' . get_lang('CertificateMinScore') . ' (%)</td>');
                $form->addElement('html', '          <td valign="top" valign="top"><div id="certificate1">');
                $form->addElement('text', 'certificate_min_score', '', array('id'=>'txtScore', 'maxlength'=>'6'));
                $form->addElement('html', '<div id="quiz-certificate-score" style="' . (!empty($this->certif_template) ? 'display:block;' : 'display:none;') . '"</div>');

                $form->addElement('html', '</div></td>
                                    </tr>');

                $form->addElement('html', '     </table>');


                $form->addElement('html', '</div>');
                }else{
                    $form->addElement('html', '<h3>' . get_lang('Certificate') . '</h3>');
                    $form->addElement('html', '<div class="content-scenario-block">');
                    $form->addElement('html', '<table width= "100%">
                                        <tr>
                                            <td valign="top" width="22px"></td>');
                    $form->addElement('html', '         <td valign="top" width="150px">');
                    $form->addElement('select', 'certificate_template', get_lang('Certificate'), $selCertificates, array('id' => 'quiz-certificate'));
                    $form->addElement('html', '</td>
                                            <td valign="top">');
                    $form->addElement('html', '             <div id="quiz-certificate-thumb" style="' . (!empty($objQuiz->certif_template) ? 'display:block;' : 'display:none;') . '">' . $certif_thumb . '</div>');
                    $form->addElement('html', '         </td>
                                             <td rowspan="2" valign="middle" width="290px">' . Display::return_icon('quizgallery/avatar_certificate.png') . '</td>
                                        </tr>
                                        <tr>
                                            <td valign="top"></td>');

                    $form->addElement('html', '         <td valign="top" align="right">' . get_lang('CertificateMinScore') . ' (%)</td>');
                    $form->addElement('html', '          <td valign="top" valign="top"><div id="certificate1" style="display:none;">');
                    $form->addElement('text', 'certificate_min_score', '', array('id'=>'txtScore', 'maxlength'=>'6'));
                    $form->addElement('html', '<div id="quiz-certificate-score" style="' . (!empty($objQuiz->certif_template) ? 'display:block;' : 'display:none;') . '"</div>');

                    $form->addElement('html', '</div></td>
                                        </tr>');

                    $form->addElement('html', '     </table>');


                    $form->addElement('html', '</div>');                    
                }
            }



            $form->addElement('html', '</div>'); // end content block

            $form->addElement('style_submit_button', 'submit', get_lang('Ok'), 'class="add"');

            $defaults = array();
            // defaults
            if (isset($_REQUEST['modifyExercise'])) {
                if ($this->id > 0) {
                    $defaults['exerciseType'] = $this->selectType();
                    $defaults['exerciseTitle'] = $this->selectTitle();
                    $defaults['simplifymode'] =  $this->selectSimplifymode();
                    
                    $defaults['exerciseDescription'] = $this->selectDescription();
                    $defaults['endQuizFeedback'] = $this->selectQuizFinalFeedback();
                    $defaults['exerciseAttempts'] = $this->selectAttempts();
                    $defaults['exerciseFeedbackType'] = $this->selectFeedbackType();
                    $defaults['exerciseScoreOption'] = $this->selectScorePass();
                    //     $defaults['quizType'] = $this->selectQuizType();
                    //     $defaults['showScore'] = $this->selectResultsDisabled()?0:1;
                    $objQuiz = $this->getTemplateById($this->id);
                    if (api_get_setting('enable_certificate') === 'true') {
                            if( $objQuiz->certif_template == 0){
                                $defaults['certificate_template'] = 0;
                                $defaults['certificate_min_score'] = ($this->selectCertifMinScore() == '00.00') ? '50.00' : $this->selectCertifMinScore();
                            }else{
                            //$defaults['certificate_template'] = ($this->selectCertifTemplate() == 0) ? 1 : $this->selectCertifTemplate();
                            $current_language = !empty($_course['language']) ? $_course['language'] : api_get_interface_language();
                            if($current_language == 'english'){
                                $defaults['certificate_template'] = $this->selectCertifTemplate();
                            }else{
                                //$defaults['certificate_template']
                                if( $objQuiz->certif_template == 0){
                                  $defaults['certificate_template'] = 4;  
                                }else{
                                    $defaults['certificate_template'] = $objQuiz->certif_template;  
                                }
                            }
                            $defaults['certificate_min_score'] = ($this->selectCertifMinScore() == '00.00') ? '50.00' : $this->selectCertifMinScore();
                        }
                    } else {
                        $current_language = !empty($_course['language']) ? $_course['language'] : api_get_interface_language();
                        if($current_language == 'english'){
                            $defaults['certificate_template'] = 1;
                        }else{
                            $defaults['certificate_template'] = 4;
                        }
                    }

                    if (api_get_setting('enable_exammode') === 'true') {
                        $defaults['quizType'] = $this->selectQuizType();
                    } else {
                        $defaults['quizType'] = 1;
                    }
                    $defaults['showScore'] = $this->selectResultsDisabled() ? 0 : 1;

                    if ($this->random > $this->selectNbrQuestions()) {
                        $defaults['randomQuestions'] = $this->selectNbrQuestions();
                    } else {
                        $defaults['randomQuestions'] = $this->random;
                    }

                    $defaults['randomQuestionsOpt'] = 0;
                    if (!empty($defaults['randomQuestions'])) {
                        $defaults['randomQuestionsOpt'] = 1;
                    }

                    $defaults['enabletimelimit'] = 0;
                    if (($this->start_time != '0000-00-00 00:00:00') || ($this->end_time != '0000-00-00 00:00:00')) {
                        $defaults['enabletimelimit'] = 1;
                    }

                    $defaults['start_time'] = ($this->start_time != '0000-00-00 00:00:00') ? $this->start_time : date('Y-m-d 12:00:00');
                    $defaults['end_time'] = ($this->end_time != '0000-00-00 00:00:00') ? $this->end_time : date('Y-m-d 12:00:00', time() + 84600);

                    if ($this->expired_time > '0') {
                        $defaults['enabletimercontroltotalminutes'] = $this->expired_time;
                    } else {
                        $defaults['enabletimercontroltotalminutes'] = 0;
                    }
                } else {
                    $defaults['exerciseType'] = 2;
                    $defaults['exerciseAttempts'] = 0;
                    $defaults['randomQuestions'] = 0;
                    $defaults['exerciseFeedbackType'] = 3;
                    $defaults['exerciseScoreOption'] = 50;
                    $defaults['quizType'] = 1;
                    $defaults['randomQuestionsOpt'] = 0;
                    $defaults['start_time'] = date('Y-m-d 12:00:00');
                    $defaults['end_time'] = date('Y-m-d 12:00:00', time() + 84600);
                    $defaults['showScore'] = 1;
                }
            } else {
                $defaults['exerciseTitle'] = $this->selectTitle();
                $defaults['exerciseDescription'] = $this->selectDescription();
				$defaults['endQuizFeedback'] = $this->selectQuizFinalFeedback();
            }

            if (api_get_setting('search_enabled') === 'true') {
                $defaults['index_document'] = 'checked="checked"';
            }

            $form->setDefaults($defaults);
        }

        /**
         * Creates the form to create / edit an exercise
         * @param FormValidator $form the formvalidator instance (by reference)
         */
        function createForm($form, $type = 'full') {
            global $id, $_course;

            if (empty($type)) {
                $type = 'full';
            }

            if (isset($_REQUEST['modifyExercise'])) {
                // Random questions
                if ($type == 'full') {
                    $defaults = array();
                }
            }
            // submit
            isset($_GET['exerciseId']) ? $text = get_lang('Validate') : $text = get_lang('ProcedToQuestions');

            // form title
            if (!isset($_GET['exerciseId'])) {
                $form_title = get_lang('NewEx');
//                $form->addElement('html', '<div style="padding-left:30px; margin-top:10px; position:relative;">');
                $form->addElement('html');
                $form->addElement('header', '', $form_title);
            } elseif (isset($_GET['exerciseId']) && isset($_REQUEST['modifyExercise'])) {
                //---------------------------------- MAX NUMBER ATTEMPTS FOR DOKEOS 2.0 ---------------------------//
                $form->addElement('html', '<div style="display:none;">');
                $attempt_option = range(0, 10);
                $attempt_option[0] = get_lang('Infinite');
                $form->addElement('select', 'exerciseAttempts', get_lang('ExerciseAttempts'), $attempt_option);
                $form->addElement('checkbox', 'enabletimelimit', get_lang('EnableTimeLimits'), null, 'onclick = "  return timelimit() "');
                $var = Exercise::selectTimeLimit();
                $form->addElement('html', '<div class="clear"></div></div>');
                //---------------------------------- TIMER CONTROL FOR DOKEOS 2.0 ---------------------------------//
                $form->addElement('html', '<div>');
                $form->addElement('html', '<div align="center"> ');
                // Div container of image and message for the self evaluation/ Exam
                $form->addElement('html', '<div class="squarebox_white" style="width:875px;height:60px;background:#FFFFFF;"> ');

                // Self Evaluation / Exam description
                if (!is_null($this->scenario)) {
                    $timer_id = 'enabletimercontroltotalminutes' . $this->scenario;
                    $form->addElement('hidden', 'scenario', $this->scenario);
                    if ($this->scenario == 1) {
                        $title_scenario = get_lang('QuizScenario');
                        $body_scenario = get_lang('QuizSelfEvaluationMessage');
                        $scenario_image = "../img/dokeos_exam.png";
                    } elseif ($this->scenario == 2) {
                        $title_scenario = get_lang('QuizExamTitle');
                        $body_scenario = get_lang('QuizExamMessage');
                        $scenario_image = "../img/dokeos_exam.png";
                    }
                }

                // Message
                $form->addElement('html', '<div style="float:left;height:60px;width:700px;text-align:left;padding-top:10px;"><div style="margin-left:5px;"> ' . $title_scenario . '</div><div style="margin-left:5px;"> ' . $body_scenario . '</div>');
                $form->addElement('html', '</div>');

                // Self evaluation Image
                $form->addElement('html', '<div style="float:right;height:60px;width:100px;">' . Display::return_icon('pixel.gif', '', array('class' => 'dokeos_exam')) . '');
                $form->addElement('html', '</div>');
                // Clear
                $form->addElement('html', '<div style="clear: both; font-size: 0;"></div>');
                $form->addElement('html', '</div>');

                // Indexing document
                if (api_get_setting('search_enabled') === 'true') {
                    require_once(api_get_path(LIBRARY_PATH) . 'specific_fields_manager.lib.php');
                    $form->addElement('hidden', 'index_document', 1);
                    $form->addElement('hidden', 'language', api_get_setting('platformLanguage'));
                    $form->addElement('html', '<div class="row">');
                    $form->addElement('html', '</div>');
                }

                $timer = array();
                $timer[] = FormValidator :: createElement('text', 'enabletimercontroltotalminutes', get_lang('Minutes'), array('style' => 'width : 35px', 'id' => $timer_id));
                $timer[] = FormValidator :: createElement('static', 'minutes', get_lang('Minutes'));
                $form->addGroup($timer, null, get_lang('Chronometer'), '&nbsp;&nbsp;' . get_lang('Minutes'));

                //-------------------------- START DATE / END DATE FOR DOKEOS 2.0 ---------------------------------//
                $form->addElement('datepicker', 'start_time', get_lang('ExeStartTime'), array('form_name' => 'exercise_admin' . $this->scenario));
                $form->addElement('datepicker', 'end_time', get_lang('ExeEndTime'), array('form_name' => 'exercise_admin' . $this->scenario));
                //-------------------------- RANDOM CONFIGURATION FOR DOKEOS 2.0 ----------------------------------//
                $random = array();
                $option = array();
                $max = ($this->id > 0) ? $this->selectNbrQuestions() : 10;
                $option = range(0, $max);
                $option[0] = get_lang('No');

                $random[] = FormValidator :: createElement('select', 'randomQuestions', null, $option);
                $random[] = FormValidator :: createElement('static', 'help', 'help'); //, '<span>' . get_lang('RandomQuestionsHelp') . '</span>'
                $form->addGroup($random, null, get_lang('RandomQuestions'), '<br />');
                $attempt_option = range(0, 10);
                $attempt_option[0] = get_lang('Infinite');
                $form->addElement('select', 'exerciseAttempts', get_lang('ExerciseAttempts'), $attempt_option);
                //------------------------- FEEDBACK CONFIGURATION FOR DOKEOS 2.0 ---------------------------------//
                // feedback type
                $radios_feedback = array();
                $radios_feedback[] = FormValidator :: createElement('static', '', null, get_lang('FeedbackType'));
                $radios_feedback[] = FormValidator :: createElement('radio', 'exerciseFeedbackType', null, get_lang('ExerciseAtTheEndOfTheTest'), '0');
                $radios_feedback[] = FormValidator :: createElement('radio', 'exerciseFeedbackType', null, get_lang('NoFeedback'), '2');
                $radios_feedback[] = FormValidator :: createElement('radio', 'exerciseFeedbackType', null, get_lang('DirectFeedback'), '3');
                $form->addGroup($radios_feedback, null, '');

                $feedback_option[0] = get_lang('ExerciseAtTheEndOfTheTest');
                $feedback_option[2] = get_lang('NoFeedback');
                $feedback_option[3] = get_lang('DirectFeedback');

                //------------------------- QUESTION PER PAGE CONFIGURATION FOR DOKEOS 2.0 -------------------------//
                //Can't modify a DirectFeedback question
                if ($this->selectFeedbackType() != 1) {
                    // test type
                    $radios = array();
                    $radios[] = FormValidator :: createElement('static', '', null, get_lang('QuestionsPerPage'));
                    $radios[] = FormValidator :: createElement('radio', 'exerciseType', null, get_lang('QuestionsPerPageOne'), '2');
                    $radios[] = FormValidator :: createElement('radio', 'exerciseType', null, get_lang('QuestionsPerPageAll'), '1');
                    $form->addGroup($radios, null, '');
                } else {
                    // if is Directfeedback but has not questions we can allow to modify the question type
                    if ($this->selectNbrQuestions() == 0) {
                        $form->addElement('select', 'exerciseFeedbackType', get_lang('FeedbackType'), $feedback_option, 'onchange="javascript:feedbackselection()"');
                        // test type
                        $radios = array();
                        $radios[] = FormValidator :: createElement('radio', 'exerciseType', null, get_lang('SimpleExercise'), '1');
                        $radios[] = FormValidator :: createElement('radio', 'exerciseType', null, get_lang('SequentialExercise'), '2');
                        $form->addGroup($radios, null, get_lang('ExerciseType'));
                    } else {
                        //we force the options to the DirectFeedback exercisetype
                        $form->addElement('hidden', 'exerciseFeedbackType', '3');
                        $form->addElement('hidden', 'exerciseType', '2');
                    }
                }
                //------------------------- SHOW RESULT OPTION FOR DOKEOS 2.0 -------------------------//
                $radios_results_disabled = array();
                $radios_results_disabled[] = FormValidator :: createElement('static', '', null, get_lang('ShowResultsToStudents'));
                $radios_results_disabled[] = FormValidator :: createElement('radio', 'results_disabled', null, get_lang('Yes'), '0');
                $radios_results_disabled[] = FormValidator :: createElement('radio', 'results_disabled', null, get_lang('No'), '1');
                $form->addGroup($radios_results_disabled, null, '');


                if (api_get_setting('enable_certificate') === 'true') {
                    // certificate option
                    require_once api_get_path(LIBRARY_PATH) . 'certificatemanager.lib.php';

                    $current_language = !empty($_course['language']) ? $_course['language'] : api_get_interface_language();
                    $certificates = CertificateManager::create()->getCertificatesList($current_language);
                    $selCertificates = array();
                    if (!empty($certificates)) {
                        $selCertificates[0] = get_lang('None');
                        foreach ($certificates as $tpl_id => $certificate) {
                            $selCertificates[$tpl_id] = $certificate['title'];
                        }
                    }

                    $certif_thumb = '';
                    if (!empty($this->certif_template)) {
                        $certif_thumb = CertificateManager::create()->returnCertificateThumbnailImg($this->certif_template);
                    }
                    $form->addElement('select', 'certificate_template', get_lang('Certificate'), $selCertificates, array('id' => 'quiz-certificate'));
                    $form->addElement('html', '<div id="quiz-certificate-thumb" style="' . (!empty($this->certif_template) ? 'display:block;' : 'display:none;') . '">' . $certif_thumb . '</div>');

                    $form->addElement('html', '<div id="quiz-certificate-score" style="' . (!empty($this->certif_template) ? 'display:block;' : 'display:none;') . '">');
                    $form->addElement('text', 'certificate_min_score', get_lang('CertificateMinScore') . ' (%)');
                    $form->addElement('html', '</div>');
                }
            }


            //------------------------- FORM TITLE FOR DOKEOS 2.0 ---------------------------------//
            $form->addElement('text', 'exerciseTitle', get_lang('ExerciseName'), 'class="focus CustNexQuiz";size="50"');
            
            /*$form->addElement('static', null, get_lang('Mode'));
            $radios_quizType = array();
            $radios_quizType[] = FormValidator :: createElement('radio', 'quizType', null, get_lang('SelfLearning'), '1');
            $radios_quizType[] = FormValidator :: createElement('radio', 'quizType', null, get_lang('ExamMode'), '2');
            $form->addGroup($radios_quizType, null, ''); */            
            
            
            if (api_get_setting('show_quizcategory') == 'true') {
                $TBL_QUIZ_CATEGORY = Database::get_course_table(TABLE_QUIZ_CATEGORY, $this->db_name);
                $TBL_QUIZ_TYPE = Database::get_course_table(TABLE_QUIZ_TYPE, $this->db_name);

                $quiz_category = array();
                $quizcat = 'Select';
                $quizcat_id = '0';
                $quiz_level = array('Select', 'Prerequistie', 'Beginner', 'Intermediate', 'Advanced');
                $numberofquestion = array('Select', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10');
                $quizlevel = "Select,Prerequistie,Beginner,Intermediate,Advanced";
                $quizlevel_id = "0,1,2,3,4";
                $query = "SELECT * FROM $TBL_QUIZ_CATEGORY ORDER BY display_order";
                $result = api_sql_query($query, __FILE__, __LINE__);
                $quiz_category[] = "Select";
                while ($row = Database::fetch_array($result)) {
                    $quiz_category[] = $row['category_title'];
                    $quizcat = $quizcat . "," . $row['category_title'];
                    $quizcat_id = $quizcat_id . "," . $row['id'];
                }

                if (isset($_GET['exerciseId'])) {
                    $form->addElement('html', '<br/><br/><br/><div class="quiz_content_actions" style="width:60%;float:left;"><div class="quiz_header" align="left">' . get_lang('QuestionCategories') . '</div><br/><table align="left" width="100%" border="0"><tr><td align="right"><img src="../img/add_22.png" id="addButton_' . $this->scenario . '">&nbsp;&nbsp;<img src="../img/wrong.png" id="removeButton_' . $this->scenario . '"></td></tr></table>');

                    $query = "SELECT * FROM $TBL_QUIZ_TYPE WHERE exercice_id = " . $_GET['exerciseId'] . " AND scenario_type = " . $this->scenario;
                    $result = api_sql_query($query, __FILE__, __LINE__);
                    $count_rows = Database::num_rows($result);
                    if ($count_rows == 0) {
                        $count_rows = 1;
                        $form->addElement('html', '<table width="100%" border="0"><tr><td width="45%">' . get_lang('Category') . '</td><td width="25%">' . get_lang('Level') . '</td><td width="30%">' . get_lang('Numberofquestion') . '</td></tr><tr><td colspan="3"><div id="TextBoxesGroup_' . $this->scenario . '"><div id="TextBoxDiv1_' . $this->scenario . '">');
                        $form->addElement('html', '</div></div></td></tr></table>');
                        $form->addElement('hidden', 'quizcategory_' . $this->scenario . '', $quizcat);
                        $form->addElement('hidden', 'quizcategory_id_' . $this->scenario . '', $quizcat_id);
                        $form->addElement('hidden', 'quiz_level_' . $this->scenario . '', $quizlevel);
                        $form->addElement('hidden', 'quiz_level_id_' . $this->scenario . '', $quizlevel_id);
                        $form->addElement('hidden', 'counter_' . $this->scenario . '', $count_rows);
                    } else {
                        $i = 1;
                        $form->addElement('html', '<table width="100%"><tr><td><div id="TextBoxesGroup_' . $this->scenario . '">');
                        while ($row = Database::fetch_array($result)) {
                            $db_quizlevel = $row['quiz_level'];

                            $form->addElement('html', '<div id="TextBoxDiv' . $i . "_" . $this->scenario . '"><table width="100%"><tr><td width="45%">');
                            if ($i == 1) {
                                $form->addElement('select', 'quizcategory_' . $i . '_' . $this->scenario, get_lang('Category'), $quiz_category);
                                $form->addElement('html', '</td><td width="25%">');
                                $form->addElement('select', 'quizlevel_' . $i . '_' . $this->scenario, get_lang('Level'), $quiz_level);
                                $form->addElement('html', '</td><td width="30%" align="right">');
                                $form->addElement('select', 'numberofquestion_' . $i . '_' . $this->scenario, get_lang('Numberofquestion'), $numberofquestion);
                                $form->addElement('html', '</td>');
                                $form->addElement('html', '</tr></table>');
                            } else {
                                $form->addElement('select', 'quizcategory_' . $i . '_' . $this->scenario, '', $quiz_category);
                                $form->addElement('html', '</td><td width="25%">');
                                $form->addElement('select', 'quizlevel_' . $i . '_' . $this->scenario, '', $quiz_level);
                                $form->addElement('html', '</td><td width="30%" align="right">');
                                $form->addElement('select', 'numberofquestion_' . $i . '_' . $this->scenario, '', $numberofquestion);
                                $form->addElement('html', '</td>');
                                $form->addElement('html', '<td width="15%">&nbsp;</td></tr></table>');
                            }
                            $form->addElement('hidden', 'quizcategory_' . $this->scenario . '', $quizcat);
                            $form->addElement('hidden', 'quizcategory_id_' . $this->scenario . '', $quizcat_id);
                            $form->addElement('hidden', 'quiz_level_' . $this->scenario . '', $quizlevel);
                            $form->addElement('hidden', 'quiz_level_id_' . $this->scenario . '', $quizlevel_id);
                            $form->addElement('hidden', 'counter_' . $this->scenario . '', $count_rows);
                            $defaults['quizcategory_' . $i . '_' . $this->scenario] = $row['category_id'];
                            $defaults['quizlevel_' . $i . '_' . $this->scenario] = $db_quizlevel;
                            $defaults['numberofquestion_' . $i . '_' . $this->scenario] = $row['number_of_question'];
                            $i++;
                        }
                        $form->addElement('html', '</div></div></td></tr></table></div>');
                    }
                }
                $form->addElement('html', '</div>');
            }
            $form->addElement('html', '<br />');

            $form->addElement('html', Display::return_icon('questions.png', '', array("id" => "question-exe-form-icon")));

            //------------------------- BUTTON FOR DOKEOS 2.0 --------------------------------------//
            $form->addElement('style_submit_button', 'submitExercise', $text, 'class="save bcustrl" ');
            $form->addElement('html', '</div>');

            $form->addRule('exerciseTitle', get_lang('GiveExerciseName'), 'required');
            //$form->addRule('quizType', get_lang('GiveMode'), 'required');


            if ($type == 'full') {
                // rules
                if (isset($_REQUEST['modifyExercise'])) {
                    $form->addRule('exerciseAttempts', get_lang('Numeric'), 'numeric');
                    $form->addRule('start_time', get_lang('InvalidDate'), 'date');
                    $form->addRule('end_time', get_lang('InvalidDate'), 'date');
                    $form->addRule(array('start_time', 'end_time'), get_lang('StartDateShouldBeBeforeEndDate'), 'date_compare', 'lte');
                }
            }

            // defaults
            if ($type == 'full') {
                if (isset($_REQUEST['modifyExercise'])) {
                    if ($this->id > 0) {
                        if ($this->random > $this->selectNbrQuestions()) {
                            $defaults['randomQuestions'] = $this->selectNbrQuestions();
                        } else {
                            $defaults['randomQuestions'] = $this->random;
                        }

                        $defaults['exerciseType'] = $this->selectType();
                        $defaults['exerciseTitle'] = $this->selectTitle();
                        $defaults['quizType'] = $this->selectQuizType();
                        $defaults['exerciseDescription'] = $this->selectDescription();
                        $defaults['exerciseAttempts'] = $this->selectAttempts();
                        $defaults['exerciseFeedbackType'] = $this->selectFeedbackType();
                        $defaults['results_disabled'] = $this->selectResultsDisabled();

                        if (api_get_setting('enable_certificate') === 'true') {
                            $defaults['certificate_template'] = $this->selectCertifTemplate();
                            $defaults['certificate_min_score'] = $this->selectCertifMinScore();
                        }

                        if (($this->start_time != '0000-00-00 00:00:00') || ($this->end_time != '0000-00-00 00:00:00'))
                            $defaults['enabletimelimit'] = 1;
                        $defaults['start_time'] = ($this->start_time != '0000-00-00 00:00:00') ? $this->start_time : date('Y-m-d 00:00:00');
                        $defaults['end_time'] = ($this->end_time != '0000-00-00 00:00:00') ? $this->end_time : date('Y-m-d 12:00:00', time() + 84600);
                        if ($this->expired_time > '0') {
                            $defaults['enabletimercontroltotalminutes'] = $this->expired_time;
                        } else {
                            $defaults['enabletimercontroltotalminutes'] = 0;
                        }
                    } else {
                        $defaults['exerciseType'] = 2;
                        $defaults['exerciseAttempts'] = 0;
                        $defaults['randomQuestions'] = 0;
                        $defaults['exerciseDescription'] = '';
                        $defaults['exerciseFeedbackType'] = 3;
                        $defaults['results_disabled'] = 0;

                        $defaults['start_time'] = date('Y-m-d 12:00:00');
                        $defaults['end_time'] = date('Y-m-d 12:00:00', time() + 84600);
                    }
                } else {
                    $defaults['exerciseTitle'] = $this->selectTitle();
                    $defaults['quizType'] = $this->selectQuizType();
                    $defaults['exerciseDescription'] = $this->selectDescription();
                }
                if (api_get_setting('search_enabled') === 'true') {
                    $defaults['index_document'] = 'checked="checked"';
                }
            }
            $form->setDefaults($defaults);
        }

        /**
         * function which process the creation of exercises
         * @param FormValidator $form the formvalidator instance
         */
        function processScenarioCreation($form, $type = '') {

            $this->updateTitle($form->getSubmitValue('exerciseTitle'));
            $this->updateSimplifymode($form->getSubmitValue('simplifymode'));
            $this->updateDescription($form->getSubmitValue('exerciseDescription'));
	    $this->updateQuizFinalFeedback($form->getSubmitValue('endQuizFeedback'));
            $this->updateQuizType($form->getSubmitValue('quizType'));
            $this->updateAttempts($form->getSubmitValue('exerciseAttempts'));
            $this->updateExpiredTime($form->getSubmitValue('enabletimercontroltotalminutes'));

            if ($form->getSubmitValue('enabletimelimit') == 1) {
                $start_time = $form->getSubmitValue('start_time');
                $this->start_time = $start_time['Y'] . '-' . $start_time['F'] . '-' . $start_time['d'] . ' ' . $start_time['H'] . ':' . $start_time['i'] . ':00';
                $end_time = $form->getSubmitValue('end_time');
                $this->end_time = $end_time['Y'] . '-' . $end_time['F'] . '-' . $end_time['d'] . ' ' . $end_time['H'] . ':' . $end_time['i'] . ':00';
                // Time validation
                $date_start = strtotime($this->start_time);
                $date_end = strtotime($this->end_time);
                if ($date_end < $date_start) {
                    $this->start_time = $end_time['Y'] . '-' . $end_time['F'] . '-' . $end_time['d'] . ' ' . $end_time['H'] . ':' . $end_time['i'] . ':00';
                }
            } else {
                $this->start_time = '0000-00-00 00:00:00';
                $this->end_time = '0000-00-00 00:00:00';
            }

            $expired_total_time = $form->getSubmitValue('enabletimercontroltotalminutes');
            if ($this->expired_time == 0) {
                $this->expired_time = $expired_total_time;
            }

            if ($form->getSubmitValue('randomQuestionsOpt') == 1) {
                $this->setRandom($form->getSubmitValue('randomQuestions'));
            } else {
                $this->setRandom(0);
            }

            $this->updateFeedbackType($form->getSubmitValue('exerciseFeedbackType'));

            $this->updateCertifTemplate($form->getSubmitValue('certificate_template'));
            $this->updateCertifMinScore($form->getSubmitValue('certificate_min_score'));
            $this->updateScorePass($form->getSubmitValue('exerciseScoreOption'));

            if ($form->getSubmitValue('showScore')) {
                $this->updateResultsDisabled(0);
            } else {
                $this->updateResultsDisabled(1);
            }



            $id = $this->save($type);
            $TBL_QUIZ_TYPE = Database::get_course_table(TABLE_QUIZ_TYPE, $this->db_name);
            if ($form->getSubmitValue('edit') == 'true') {
                $id = Security::remove_XSS($_GET['exerciseId']);
                $sql = "DELETE FROM $TBL_QUIZ_TYPE WHERE exercice_id = " . Database::escape_string($id) . " AND scenario_type = " . $this->scenario;
                api_sql_query($sql, __FILE__, __LINE__);

                $sql = "UPDATE $TBL_QUIZ_TYPE SET current_active = 0 WHERE exercice_id = " . Database::escape_string($id) . " AND scenario_type <> " . $this->scenario;
                api_sql_query($sql, __FILE__, __LINE__);
            }
            $counter = $form->getSubmitValue('counter_' . $this->scenario);

            for ($i = 1; $i <= $counter; $i++) {
                $quiz_level = $form->getSubmitValue('quizlevel_' . $i . '_' . $this->scenario);

                $sql = "INSERT INTO $TBL_QUIZ_TYPE (exercice_id,category_id,quiz_level,number_of_question,scenario_type,current_active,session_id) VALUES(
				" . Database::escape_string($id) . "," . Database::escape_string($form->getSubmitValue('quizcategory_' . $i . '_' . $this->scenario)) . ",'"
                        . Database::escape_string($quiz_level) . "'," . Database::escape_string($form->getSubmitValue('numberofquestion_' . $i . '_' . $this->scenario))
                        . "," . $this->scenario . ", 1, " . api_get_session_id() . ")";

                api_sql_query($sql, __FILE__, __LINE__);
            }
        }

        /**
         * function which process the creation of exercises
         * @param FormValidator $form the formvalidator instance
         */
        function processCreation($form, $type = '') {

            $this->updateTitle($form->getSubmitValue('exerciseTitle'));
            $this->updateSimplifymode($form->getSubmitValue('simplifymode'));
            $this->updateDescription($form->getSubmitValue('exerciseDescription'));
            $this->updateAttempts($form->getSubmitValue('exerciseAttempts'));
            $this->updateFeedbackType($form->getSubmitValue('exerciseFeedbackType'));
            $this->updateType($form->getSubmitValue('exerciseType'));
            $this->setRandom($form->getSubmitValue('randomQuestions'));
            $this->updateResultsDisabled($form->getSubmitValue('results_disabled'));
            $this->updateExpiredTime($form->getSubmitValue('enabletimercontroltotalminutes'));
            $this->updateScenario($form->getSubmitValue('scenario'));

            $this->updateCertifTemplate($form->getSubmitValue('certificate_template'));
            $this->updateCertifMinScore($form->getSubmitValue('certificate_min_score'));
            $this->updateScorePass($form->getSubmitValue('exerciseScoreOption'));
            $this->updateQuizType($form->getSubmitValue('quizType'));


            if (true) { // $form->getSubmitValue('enabletimelimit') == 1
                $start_time = $form->getSubmitValue('start_time');
                $this->start_time = $start_time['Y'] . '-' . $start_time['F'] . '-' . $start_time['d'] . ' ' . $start_time['H'] . ':' . $start_time['i'] . ':00';
                $end_time = $form->getSubmitValue('end_time');
                $this->end_time = $end_time['Y'] . '-' . $end_time['F'] . '-' . $end_time['d'] . ' ' . $end_time['H'] . ':' . $end_time['i'] . ':00';
                // Time validation
                $date_start = strtotime($this->start_time);
                $date_end = strtotime($this->end_time);
                if ($date_end < $date_start) {
                    $this->start_time = $end_time['Y'] . '-' . $end_time['F'] . '-' . $end_time['d'] . ' ' . $end_time['H'] . ':' . $end_time['i'] . ':00';
                }
            } else {
                $this->start_time = '0000-00-00 00:00:00';
                $this->end_time = '0000-00-00 00:00:00';
            }

            if (true) { // $form->getSubmitValue('enabletimercontrol') == 1)
                $expired_total_time = $form->getSubmitValue('enabletimercontroltotalminutes');
                if ($this->expired_time == 0) {
                    $this->expired_time = $expired_total_time;
                }
            } else {
                $this->expired_time = 0;
            }
            //echo $end_time;exit;
            $id = $this->save($type);
            $TBL_QUIZ_TYPE = Database::get_course_table(TABLE_QUIZ_TYPE, $this->db_name);
            if ($form->getSubmitValue('edit') == 'true') {
                $id = Security::remove_XSS($_GET['exerciseId']);
                $sql = "DELETE FROM $TBL_QUIZ_TYPE WHERE exercice_id = " . Database::escape_string($id) . " AND scenario_type = " . $this->scenario;
                api_sql_query($sql, __FILE__, __LINE__);

                $sql = "UPDATE $TBL_QUIZ_TYPE SET current_active = 0 WHERE exercice_id = " . Database::escape_string($id) . " AND scenario_type <> " . $this->scenario;
                api_sql_query($sql, __FILE__, __LINE__);
            }
            $counter = $form->getSubmitValue('counter_' . $this->scenario);

            for ($i = 1; $i <= $counter; $i++) {
                $quiz_level = $form->getSubmitValue('quizlevel_' . $i . '_' . $this->scenario);

                $sql = "INSERT INTO $TBL_QUIZ_TYPE (exercice_id,category_id,quiz_level,number_of_question,scenario_type,current_active,session_id) VALUES(
				" . Database::escape_string($id) . "," . Database::escape_string($form->getSubmitValue('quizcategory_' . $i . '_' . $this->scenario)) . ",'"
                        . Database::escape_string($quiz_level) . "'," . Database::escape_string($form->getSubmitValue('numberofquestion_' . $i . '_' . $this->scenario))
                        . "," . $this->scenario . ", 1, " . api_get_session_id() . ")";

                api_sql_query($sql, __FILE__, __LINE__);
            }
        }

        function search_engine_save() {
            $search_db_path = api_get_path(SYS_PATH) . 'searchdb';
            if (is_writable($search_db_path)) {
                $course_id = api_get_course_id();

                require_once(api_get_path(LIBRARY_PATH) . 'search/DokeosIndexer.class.php');
                require_once(api_get_path(LIBRARY_PATH) . 'search/IndexableChunk.class.php');
                require_once(api_get_path(LIBRARY_PATH) . 'specific_fields_manager.lib.php');

                //$specific_fields = get_specific_field_list();
                $ic_slide = new IndexableChunk();

                /* $all_specific_terms = '';
                  foreach ($specific_fields as $specific_field) {
                  if (isset($_REQUEST[$specific_field['code']])) {
                  $sterms = trim($_REQUEST[$specific_field['code']]);
                  if (!empty($sterms)) {
                  $all_specific_terms .= ' ' . $sterms;
                  $sterms = explode(',', $sterms);
                  foreach ($sterms as $sterm) {
                  $ic_slide->addTerm(trim($sterm), $specific_field['code']);
                  add_specific_field_value($specific_field['id'], $course_id, TOOL_QUIZ, $this->id, $sterm);
                  }
                  }
                  }
                  } */

                // build the chunk to index
                $ic_slide->addValue("title", $this->exercise);
                $ic_slide->addCourseId($course_id);
                $ic_slide->addToolId(TOOL_QUIZ);
                $xapian_data = array(
                    SE_COURSE_ID => $course_id,
                    SE_TOOL_ID => TOOL_QUIZ,
                    SE_DATA => array('type' => SE_DOCTYPE_EXERCISE_EXERCISE, 'exercise_id' => (int) $this->id),
                    SE_USER => (int) api_get_user_id(),
                );
                $ic_slide->xapian_data = serialize($xapian_data);
                $exercise_description = !empty($this->description) ? $this->description : $this->exercise;

                if (isset($_POST['search_terms'])) {
                    $add_extra_terms = Security::remove_XSS($_POST['search_terms']) . ' ';
                }

                $file_content = $add_extra_terms . $exercise_description;
                $ic_slide->addValue("content", $file_content);

                //$ic_slide->addValue("content", $exercise_description);

                $di = new DokeosIndexer();
                isset($_POST['language']) ? $lang = Database::escape_string($_POST['language']) : $lang = 'english';
                $di->connectDb(NULL, NULL, $lang);
                $di->addChunk($ic_slide);

                //index and return search engine document id
                $did = $di->index();
                if ($did) {
                    // save it to db
                    $tbl_se_ref = Database::get_main_table(TABLE_MAIN_SEARCH_ENGINE_REF);
                    $sql = 'INSERT INTO %s (id, course_code, tool_id, ref_id_high_level, search_did)
			    VALUES (NULL , \'%s\', \'%s\', %s, %s)';
                    $sql = sprintf($sql, $tbl_se_ref, $course_id, TOOL_QUIZ, $this->id, $did);
                    api_sql_query($sql, __FILE__, __LINE__);
                }
            } else {
                return false;
            }
        }

        function search_engine_edit() {
            // update search enchine and its values table if enabled + check if database has write permissions
            $search_db_path = api_get_path(SYS_PATH) . 'searchdb';
            if (api_get_setting('search_enabled') == 'true' && extension_loaded('xapian') && is_writable($search_db_path)) {
                $course_id = api_get_course_id();

                // actually, it consists on delete terms from db, insert new ones, create a new search engine document, and remove the old one
                // get search_did
                $tbl_se_ref = Database::get_main_table(TABLE_MAIN_SEARCH_ENGINE_REF);
                $sql = 'SELECT * FROM %s WHERE course_code=\'%s\' AND tool_id=\'%s\' AND ref_id_high_level=%s LIMIT 1';
                $sql = sprintf($sql, $tbl_se_ref, $course_id, TOOL_QUIZ, $this->id);
                $res = api_sql_query($sql, __FILE__, __LINE__);

                if (Database::num_rows($res) > 0) {
                    require_once(api_get_path(LIBRARY_PATH) . 'search/DokeosIndexer.class.php');
                    require_once(api_get_path(LIBRARY_PATH) . 'search/IndexableChunk.class.php');
                    require_once(api_get_path(LIBRARY_PATH) . 'specific_fields_manager.lib.php');

                    $se_ref = Database::fetch_array($res);
                    //$specific_fields = get_specific_field_list();
                    $ic_slide = new IndexableChunk();

                    /* $all_specific_terms = '';
                      foreach ($specific_fields as $specific_field) {
                      delete_all_specific_field_value($course_id, $specific_field['id'], TOOL_QUIZ, $this->id);
                      if (isset($_REQUEST[$specific_field['code']])) {
                      $sterms = trim($_REQUEST[$specific_field['code']]);
                      $all_specific_terms .= ' ' . $sterms;
                      $sterms = explode(',', $sterms);
                      foreach ($sterms as $sterm) {
                      $ic_slide->addTerm(trim($sterm), $specific_field['code']);
                      add_specific_field_value($specific_field['id'], $course_id, TOOL_QUIZ, $this->id, $sterm);
                      }
                      }
                      } */

                    // build the chunk to index
                    $ic_slide->addValue("title", $this->exercise);
                    $ic_slide->addCourseId($course_id);
                    $ic_slide->addToolId(TOOL_QUIZ);
                    $xapian_data = array(
                        SE_COURSE_ID => $course_id,
                        SE_TOOL_ID => TOOL_QUIZ,
                        SE_DATA => array('type' => SE_DOCTYPE_EXERCISE_EXERCISE, 'exercise_id' => (int) $this->id),
                        SE_USER => (int) api_get_user_id(),
                    );
                    $ic_slide->xapian_data = serialize($xapian_data);
                    $exercise_description = !empty($this->description) ? $this->description : $this->exercise;

                    if (isset($_POST['search_terms'])) {
                        $add_extra_terms = Security::remove_XSS($_POST['search_terms']) . ' ';
                    }

                    $file_content = $add_extra_terms . $exercise_description;
                    $ic_slide->addValue("content", $file_content);


                    //$ic_slide->addValue("content", $exercise_description);

                    $di = new DokeosIndexer();
                    isset($_POST['language']) ? $lang = Database::escape_string($_POST['language']) : $lang = 'english';
                    $di->connectDb(NULL, NULL, $lang);
                    $di->remove_document((int) $se_ref['search_did']);
                    $di->addChunk($ic_slide);

                    //index and return search engine document id
                    $did = $di->index();
                    if ($did) {
                        // save it to db
                        $sql = 'DELETE FROM %s WHERE course_code=\'%s\' AND tool_id=\'%s\' AND ref_id_high_level=\'%s\'';
                        $sql = sprintf($sql, $tbl_se_ref, $course_id, TOOL_QUIZ, $this->id);
                        api_sql_query($sql, __FILE__, __LINE__);

                        $sql = 'INSERT INTO %s (id, course_code, tool_id, ref_id_high_level, search_did)
                        VALUES (NULL , \'%s\', \'%s\', %s, %s)';
                        $sql = sprintf($sql, $tbl_se_ref, $course_id, TOOL_QUIZ, $this->id, $did);
                        api_sql_query($sql, __FILE__, __LINE__);
                    }
                }
            } else {
                if (!is_writable($search_db_path)) {
                    return false;
                }
            }
        }

        function search_engine_delete() {
            // remove from search engine if enabled
            if (api_get_setting('search_enabled') == 'true' && extension_loaded('xapian')) {
                $course_id = api_get_course_id();
                $tbl_se_ref = Database::get_main_table(TABLE_MAIN_SEARCH_ENGINE_REF);
                $sql = 'SELECT * FROM %s WHERE course_code=\'%s\' AND tool_id=\'%s\' AND ref_id_high_level=%s AND ref_id_second_level IS NULL LIMIT 1';
                $sql = sprintf($sql, $tbl_se_ref, $course_id, TOOL_QUIZ, $this->id);
                $res = api_sql_query($sql, __FILE__, __LINE__);
                if (Database::num_rows($res) > 0) {
                    $row = Database::fetch_array($res);
                    require_once(api_get_path(LIBRARY_PATH) . 'search/DokeosIndexer.class.php');
                    $di = new DokeosIndexer();
                    $di->remove_document((int) $row['search_did']);
                    unset($di);
                    $tbl_quiz_question = Database::get_course_table(TABLE_QUIZ_QUESTION, $this->db_name);
                    foreach ($this->questionList as $question_i) {
                        $sql = 'SELECT type FROM %s WHERE id=%s';
                        $sql = sprintf($sql, $tbl_quiz_question, $question_i);
                        $qres = api_sql_query($sql, __FILE__, __LINE__);
                        if (Database::num_rows($qres) > 0) {
                            $qrow = Database::fetch_array($qres);
                            $objQuestion = Question::getInstance($qrow['type']);
                            $objQuestion = Question::read((int) $question_i);
                            $objQuestion->search_engine_edit($this->id, FALSE, TRUE);
                            unset($objQuestion);
                        }
                    }
                }
                $sql = 'DELETE FROM %s WHERE course_code=\'%s\' AND tool_id=\'%s\' AND ref_id_high_level=%s AND ref_id_second_level IS NULL LIMIT 1';
                $sql = sprintf($sql, $tbl_se_ref, $course_id, TOOL_QUIZ, $this->id);
                api_sql_query($sql, __FILE__, __LINE__);

                // remove terms from db
                require_once(api_get_path(LIBRARY_PATH) . 'specific_fields_manager.lib.php');
                delete_all_values_for_item($course_id, TOOL_QUIZ, $this->id);
            }
        }

        function selectExpiredTime() {
            return $this->expired_time;
        }

        /**
         * Return the nnumber of students who answered the quiz
         * @return integer the number of attempts
         */
        function tracking_select_nb_attempts() {

            $preparedSql = "SELECT COUNT(exe_id)
                            FROM " . Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES) . '
                            WHERE exe_cours_id LIKE "%s"
                            AND exe_exo_id = %d' . "
                            AND status NOT IN ('incomplete', 'left_incomplete')";
            $sql = sprintf($preparedSql, api_get_course_id(), $this->id);
            $rs = Database::query($sql);
            return mysql_result($rs, 0, 0);
        }

        function getTrackingScore($type) {

            $preparedSql = " SELECT `exe_weighting`, " . $type . "(`exe_result`) as score " .
                            " FROM " . Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES) . 
                            ' WHERE `exe_cours_id` LIKE "%s" ' .
                            " AND `exe_exo_id` = %d AND `status` NOT IN('left_incomplete','incomplete');";
            $sql = sprintf($preparedSql, api_get_course_id(), $this->id);
            $rs = Database::query($sql);
            if (Database::num_rows($rs) > 0 && mysql_result($rs, 0, 'exe_weighting') != null)
                return (mysql_result($rs, 0, 'score') * 100 ) / mysql_result($rs, 0, 'exe_weighting') ;
            else
                return 0;
        }

        function getCountQuestionAnswersAttempt($exerciseId, $exeId = null, $examId = 0) {
            require_once api_get_path(SYS_CODE_PATH).'exercice/question.class.php';
            
            
            if (!isset($exeId)) {
                $exeId = $this->getLastUserAttemptId($exerciseId, 'incomplete', null, null, null, $examId);  
            }
            $attempt = $this->getQuestionAnswersTrackAttempt($exeId);
            
            $count = 0;
            if (!empty($attempt)) {
                foreach ($attempt as $questionId => $att) {
                    $objQuestionTmp = Question ::read($questionId);
                    $answerType = $objQuestionTmp->type;
                    $answer = $attempt[$questionId];
                    
                    switch ($answerType) {       
                        case FILL_IN_BLANKS:
                            if (!empty($answer)) {
                                $str = $answer[0];
                                $str = str_replace("<br />", "", $str);
                                $str = str_replace("<s>", "", $str);
                                $str = str_replace("</s>", "", $str);
                                preg_match_all('#\[([^[]*)\]#', $str, $arr);
                                $choice = $arr[1];
                                $currAnswer = array();
                                if (!empty($choice)) {
                                    for ($i = 0; $i < count($choice); $i++) {
                                         list($ans, $correct) = explode('/', strip_tags($choice[$i]));
                                         $ans = str_replace('&nbsp;', '', trim($ans));     
                                         if (!empty($ans)) {
                                            $currAnswer[$i] = $ans;
                                         }                                         
                                    }                        
                                }
                                if (count($currAnswer) > 0) { $count++; }
                            }
                            break;
                        case MATCHING:
                            if (!empty($answer)) {
                                foreach ($answer as $ans) {
                                    if (!empty($ans)) {
                                        $count++;
                                        break;
                                    }
                                }
                            }
                            break;
                        default:
                            $answer[0] = str_replace('&nbsp;', '', trim(strip_tags($answer[0]))); 
                            if (!empty($answer[0])) {
                                $count++;
                            }                            
                            break;
                    }
                }    
            }
            return $count;
        }
        
        function getMarks($exeId, $questionId = NULL) {
            $tbl_track_attempt = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);
            $data = array();                        
            $questionList = $this->questionList;
            if (!empty($questionList)) {               
                $questions = array_values($questionList);
                
                $sql = "SELECT question_id, answer, marks FROM $tbl_track_attempt WHERE exe_id='".intval($exeId)."' AND question_id IN(".implode(',', $questions).") ORDER BY position";

                $rs = Database::query($sql);
                if (Database::num_rows($rs) > 0) {
                    while ($row = Database::fetch_object($rs)) {
                        $data[$row->question_id][] = $row->marks;
                    }
                }
            }
            return (isset($questionId)?$data[$questionId]:$data);
        }
        
        function getQuestionAnswersTrackAttempt($exeId, $questionId = NULL) {
            $tbl_track_attempt = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);
            $data = array();                        
            $questionList = $this->questionList;
            if (!empty($questionList)) {               
                $questions = array_values($questionList);
                
                $sql = "SELECT question_id, answer, marks FROM $tbl_track_attempt WHERE exe_id='".intval($exeId)."' AND question_id IN(".implode(',', $questions).") ORDER BY position";

                $rs = Database::query($sql);
                if (Database::num_rows($rs) > 0) {
                    while ($row = Database::fetch_object($rs)) {
                        $data[$row->question_id][] = $row->answer;
                    }
                }
            }
            return (isset($questionId)?$data[$questionId]:$data);
        }
        
        function getTrackExerciseScores($exeId) {
            $tblTrackExercise = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
            $rs = Database::query("SELECT exe_result, exe_weighting, manual_exe_result, exam_id FROM $tblTrackExercise WHERE exe_id =".intval($exeId));
            $scores = array();
            if (Database::num_rows($rs) > 0) {
                $scores = Database::fetch_array($rs, 'ASSOC');                     
                if (!empty($scores['exam_id'])) {
                    if ($scores['manual_exe_result'] > 100) {
                        $scores['manual_exe_result'] = 100;
                    }
                    $scores['percent'] = round($scores['manual_exe_result'], 2);
                }
                else {
                   $score = $scores['exe_result']; 
                   if ($score > $scores['exe_weighting']) {
                        $score = $scores['exe_weighting'];
                   }
                   if ($score < 0) {
                        $score = 0;
                   }
                   $scores['percent'] = round(($score * 100) / $scores['exe_weighting'], 2);
                }
            }
            return $scores;
        }
        
        function getDataTracking($exeId) {
            $tblTrackExercise = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
            $rs = Database::query("SELECT data_tracking FROM {$tblTrackExercise} WHERE exe_id = $exeId");
            $data = array();
            if (Database::num_rows($rs) > 0) {
                $row = Database::fetch_row($rs);
                if (!empty($row[0])) {
                    $data = explode(",", $row[0]);
                }                
            }
            return $data;
        }
        
        
        
function alreadyAttempted($exerciseId,$questionId, $status = 'incomplete', $userId = NULL, $courseCode = NULL, $sessionId = NULL, $examId = 0)
        {
		
			
            if (!isset($userId)) {
                $userId = api_get_user_id();
            }
            if (!isset($courseCode)) {
                $courseCode = api_get_course_id();
            }
            if (!isset($sessionId)) {
                $sessionId = api_get_session_id();
            }            
            $tblTrackExercise = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
            $tblTrackAttempt = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);
			$tblQuiz = Database::get_course_table(TABLE_QUIZ_TEST, $this->db_name);
			
			$sql  = "SELECT * ";
            $sql .= "FROM $tblTrackExercise exe ";
			$sql .= "INNER JOIN $tblQuiz q ON q.id = exe.exe_exo_id ";
			$sql .= "INNER JOIN $tblTrackAttempt a ON (a.exe_id = exe.exe_id and a.question_id = " .intval($questionId). ") ";
			$sql .= "WHERE exe_exo_id='".intval($exerciseId)."' AND ";
            $sql .= "exe_user_id='".intval($userId)."' AND ";
			$sql .= "exe_cours_id='".Database::escape_string($courseCode)."' AND ";
			$sql .= "exe.session_id = '".intval($sessionId)."' AND ";
			$sql .= "status = '$status' AND ";
			$sql .= "exam_id = '$examId' AND ";
			$sql .= "q.active <> -1 ";
			//$sql .= "ORDER BY exe_id DESC LIMIT 1";
			
			
		
		
            $rs = Database::query($sql);
            return (Database::num_rows($rs) > 0 ? true : false);
		
		}
        
        
        
        function getLastUserAttemptId($exerciseId, $status = 'incomplete', $userId = NULL, $courseCode = NULL, $sessionId = NULL, $examId = 0) {
            if (!isset($userId)) {
                $userId = api_get_user_id();
            }
            if (!isset($courseCode)) {
                $courseCode = api_get_course_id();
            }
            if (!isset($sessionId)) {
                $sessionId = api_get_session_id();
            }            
            $tblTrackExercise = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
            $tblQuiz = Database::get_course_table(TABLE_QUIZ_TEST, $this->db_name);
            
            
            $sql  = "SELECT exe_id ";
            $sql .= "FROM $tblTrackExercise exe ";
            $sql .= "INNER JOIN $tblQuiz q ON q.id = exe.exe_exo_id ";
            $sql .= "WHERE exe_exo_id='".intval($exerciseId)."' AND ";
			$sql .= "exe_user_id='".intval($userId)."' AND ";
			$sql .= "exe_cours_id='".Database::escape_string($courseCode)."' AND ";
			$sql .= "exe.session_id = '".intval($sessionId)."' AND ";
			$sql .= "status = '$status' AND ";
			$sql .= "exam_id = '$examId' AND ";
			$sql .= "q.active <> -1 ";
            $sql .= "ORDER BY exe_id DESC LIMIT 1";            
            
            
            
            $rs = Database::query($sql);
            $exeId = 0;
            if (Database::num_rows($rs) > 0) {
                $row = Database::fetch_object($rs);
                $exeId = $row->exe_id;
            }
            return $exeId;
        }
        
        /**
         * Save the scenarios when a quiz is added
         * @author Isaac flores paz <florespaz_isaac@hotmail.com>
         * @param object The quiz data
         * @return boolean true if succesful, false on error
         */
        function save_scenario($quiz_data) {
            $tbl_quiz_scenario = Database::get_course_table(TABLE_QUIZ_SCENARIO, $this->db_name);
            $session_id = api_get_session_id();
            // there are 2 types of scenarios
            /*  $scenario_types = array(1, 2);
              foreach ($scenario_types as $scenario) {
              $rs = Database::query("INSERT INTO $tbl_quiz_scenario (exercice_id, scenario_type,
              title, description, sound, type, random, active, results_disabled,
              max_attempt, start_time, end_time, feedback_type,
              expired_time, session_id) VALUES('".$quiz_data->quiz_id."','".$scenario."',
              '".$quiz_data->title."','".$quiz_data->description."','".$quiz_data->sound."',
              '".$quiz_data->type."','".$quiz_data->random."','".$quiz_data->active."',
              '".$quiz_data->results_disabled."','".$quiz_data->attempts."','".$quiz_data->start_time."',
              '".$quiz_data->end_time."','".$quiz_data->feedback."','".$quiz_data->expired_time."',
              '".$session_id."')", __FILE__, __LINE__);
              } */
            $rs = Database::query("INSERT INTO $tbl_quiz_scenario (exercice_id, scenario_type,
    title, description, sound, type, random, active, results_disabled,
    max_attempt, start_time, end_time, feedback_type,
    expired_time, session_id, simplifymode) VALUES('" . $quiz_data->quiz_id . "','1',
    '" . $quiz_data->title . "','" . $quiz_data->description . "','" . $quiz_data->sound . "',
    '" . $quiz_data->type . "','" . $quiz_data->random . "','" . $quiz_data->active . "',
    '" . $quiz_data->results_disabled . "','" . $quiz_data->attempts . "','" . $quiz_data->start_time . "',
    '" . $quiz_data->end_time . "','" . $quiz_data->feedback . "','" . $quiz_data->expired_time . "',
    '" . $session_id . "','".$quiz_data->simplifymode."')", __FILE__, __LINE__);
            if ($rs !== FALSE) {
                return true;
            } else {
                return false;
            }
        }

        /**
         * Save the quiz into Learnin path, The quiz is added as Learning path item
         * @param object The quiz data(title,id)
         */
        function save_quiz_into_learning_path($quiz_data) {
            $parent = 0;
            $title = $quiz_data->title;
            $docid = $quiz_data->quiz_id;
            if (isset($_GET['lp_id']) && $_GET['lp_id'] > 0 && isset($_SESSION['oLP']) && $docid > 0) {
                // Get the previous item ID
                $previous = $_SESSION['oLP']->select_previous_item_id();
                // Add quiz as Lp Item
                $_SESSION['oLP']->add_item($parent, $previous, TOOL_QUIZ, $docid, $title, '');
            }
        }

        /**
         * Create a quiz of an uploaded file
         * @param object $data
         */
        function create_quiz_from_an_attached_file($title, $expired_time = 0, $type = 2, $random = 0, $active = 1, $results = 0, $max_attempt = 0, $feedback = 3) {
            $tbl_quiz = Database::get_course_table(TABLE_QUIZ_TEST, $this->db_name);
            $start = '';
            $end = '';
            $max_position = 0;

            // Get the max position
            $sql = "SELECT max(position) as max_position FROM $tbl_quiz WHERE session_id = '" . api_get_session_id() . "'";
            $rs = Database::query($sql, __FILE__, __LINE__);
            $row_max = Database::fetch_object($rs);
            $max_position = $row_max->max_position + 1;
            // Save a new quiz
            $sql = "INSERT INTO $tbl_quiz (title,type,random,active,results_disabled,
     max_attempt,start_time,end_time,feedback_type,expired_time, position,
     session_id) VALUES('" . Database::escape_string($title) . "','" . Database::escape_string($type) . "','" . Database::escape_string($random) . "','" . Database::escape_string($active) . "','" . Database::escape_string($results) . "',
     '" . Database::escape_string($max_attempt) . "','" . $start . "','" . $end . "','" . $feedback . "','" . $expired_time . "','" . $max_position . "','" . api_get_session_id() . "')";
            $rs = Database::query($sql, __FILE__, __LINE__);
            $quiz_id = Database::get_last_insert_id();
            return $quiz_id;
        }

    }

    endif;
?>
