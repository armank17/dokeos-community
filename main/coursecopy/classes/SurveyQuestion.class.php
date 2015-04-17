<?php // $Id:  $
/*
==============================================================================
    Dokeos - elearning and course management software

    Copyright (c) 2004-2007 Dokeos S.A.
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
 * An QuizQuestion
 * @author Yannick Warnier <yannick.warnier@dokeos.com>
 * @package dokeos.backup
 */
class SurveyQuestion extends Resource {

    /**
     * Survey ID
     */
    public $survey_id;

    /**
     * Question and question comment
     */
    public $survey_question;
    public $survey_question_comment;

    /**
     * Question type
     */
    public $survey_question_type;

    /**
     * Display ?
     */
    public $display;

    /**
     * Sorting order
     */
    public $sort;

    /**
     * Shared question ID
     */
    public $shared_question_id;

    /**
     * Maximum value for the vote
     */
    public $max_value;

    /**
     * The primary survey group
     */
    public $survey_group_pri;

    /**
     * The first secondary survey group
     */
    public $survey_group_sec1;

    /**
     * The second secondary survey group
     */
    public $survey_group_sec2;

    /**
     * Question's options
     */
    public $options;

    /**
     * Create a new SurveyQuestion
     * @param int $id
     * @param int $survey_id
     * @param string $survey_question
     * @param string $survey_question_comment
     * @param string $type
     * @param string $display
     * @param int $sort
     * @param int $shared_question_id
     * @param int $max_value
     * @param int $survey_group_pri
     * @param int $survey_group_sec1
     * @param int $survey_group_sec2
     */
    public function __construct($id, $survey_id, $survey_question, $survey_question_comment, $type, $display, $sort, $shared_question_id, $max_value, $survey_group_pri, $survey_group_sec1, $survey_group_sec2) {
        parent::__construct($id, RESOURCE_SURVEYQUESTION);
        $this->survey_id = $survey_id;
        $this->survey_question = $survey_question;
        $this->survey_question_comment = $survey_question_comment;
        $this->survey_question_type = $type;
        $this->display = $display;
        $this->sort = $sort;
        $this->shared_question_id = $shared_question_id;
        $this->max_value = $max_value;
        $this->survey_group_pri = $survey_group_pri;
        $this->survey_group_sec1 = $survey_group_sec1;
        $this->survey_group_sec2 = $survey_group_sec2;
        $this->answers = array();
    }

    /**
     * Add an answer option to this SurveyQuestion
     * @param string $option_text
     * @param int $sort
     * @param int $value
     */
    public function add_answer($option_text, $sort, $value) {
        $answer = array();
        $answer['option_text'] = $option_text;
        $answer['sort'] = $sort;
        $answer['value'] = $value;
        $this->answers[] = $answer;
    }

    /**
     * Show this question
     */
    public function show() {
        parent::show();
        echo $this->survey_question;
    }

}