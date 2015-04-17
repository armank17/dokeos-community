<?php // $Id: Quiz.class.php 15802 2008-07-17 04:52:13Z yannoo $
/*
==============================================================================
    Dokeos - elearning and course management software

    Copyright (c) 2004 Dokeos S.A.
    Copyright (c) 2003 Ghent University (UGent)
    Copyright (c) 2001 Universite catholique de Louvain (UCL)
    Copyright (c) Bart Mollet (bart.mollet@hogent.be)

    For a full list of contributors, see "credits.txt".
    The full license can be read in "license.txt".

    This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU General Public License
    as published by the Free Software Foundation; either version 2
    of the License, or (at your option) any later version.

    See the GNU General Public License for more details.

    Contact address: Dokeos, 44 rue des palais, B-1030 Brussels, Belgium
    Mail: info@dokeos.com
==============================================================================
*/

require_once 'Resource.class.php';

/**
 * An Quiz
 * @author Bart Mollet <bart.mollet@hogent.be>
 * @package dokeos.backup
 */
class Quiz extends Resource {

    /**
     * The title
     */
    public $title;

    /**
     * The description
     */
    public $description;

    /**
     * Sound or video file
     * This should be the id of the file and not the file-name like in the
     * database!
     */
    public $sound;

    /**
     * The type in the database
     */
    public $type_db;

    /**
     * The random
     */
    public $random;

    /**
     * Is active or no?
     */
    public $active;

    /**
     * The results disabled
     */
    public $results_disabled;

    /**
     * The access condition
     */
    public $access_condition;

    /**
     * The maximum attempts
     */
    public $max_attempt;

    /**
     * The start time
     */
    public $start_time;

    /**
     * The end time
     */
    public $end_time;

    /**
     * The feedback type
     */
    public $feedback_type;

    /**
     * The expired time
     */
    public $expired_time;

    /**
     * The Position
     */
    public $position;

    /**
     * The session id
     */
    public $session_id;

    /**
     * The certificate template
     */
    public $certif_template;

    /**
     * The minimum score of the certificate
     */
    public $certif_min_score;

    /**
     * The passing score
     */
    public $score_pass;

    /**
     * The quiz type
     */
    public $quiz_type;

    /**
     * Questions
     */
    public $question_ids;

    /**
     * Create a new Quiz
     * @param string $title
     * @param string $description
     * @param string $sound
     * @param int $type
     * @param int $random
     * @param int $active
     * @param int $results_disabled
     * @param string $access_condition
     * @param int $max_attempt
     * @param string $start_time
     * @param string $end_time
     * @param int $feedback_type
     * @param int $expired_time
     * @param int $position
     * @param int $session_id
     * @param int $certif_template
     * @param float $certif_min_score
     * @param int $score_pass
     * @param int $quiz_type
     */
    public function __construct($id, $title, $description, $sound, $type, $random, $active, $results_disabled, $access_condition, $max_attempt, $start_time, $end_time, $feedback_type, $expired_time, $position, $session_id, $certif_template, $certif_min_score, $score_pass, $quiz_type) {
        parent::__construct($id, RESOURCE_QUIZ);
        $this->title = $title;
        $this->description = $description;
        $this->sound = $sound;
        $this->type_db = $type;
        $this->random = $random;
        $this->active = $active;
        $this->results_disabled = $results_disabled;
        $this->access_condition = $access_condition;
        $this->max_attempt = $max_attempt;
        $this->start_time = $start_time;
        $this->end_time = $end_time;
        $this->feedback_type = $feedback_type;
        $this->expired_time = $expired_time;
        $this->position = $position;
        $this->session_id = $session_id;
        $this->certif_template = $certif_template;
        $this->certif_min_score = $certif_min_score;
        $this->score_pass = $score_pass;
        $this->quiz_type = $quiz_type;
        $this->question_ids = array();
    }

    /**
     * Add a question to this Quiz
     * @param int $id
     * @param int $question_order
     */
    public function add_question($id, $question_order = null) {
        if (!is_null($question_order)) {
            $this->question_ids[] = array('question_id' => $id, 'question_order' => $question_order);
        } else {
            $this->question_ids[] = $id;
        }
    }

    /**
     * Show this question
     */
    public function show() {
        parent::show();
        echo $this->title;
    }

}