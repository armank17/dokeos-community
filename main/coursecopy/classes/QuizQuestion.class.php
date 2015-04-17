<?php // $Id: QuizQuestion.class.php 18549 2009-02-17 18:08:58Z cfasanando $
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
 * An QuizQuestion
 * @author Bart Mollet <bart.mollet@hogent.be>
 * @package dokeos.backup
 */
class QuizQuestion extends Resource {

    /**
     * The question
     */
    public $question;

    /**
     * The description
     */
    public $description;

    /**
     * Ponderation
     */
    public $ponderation;

    /**
     * Position
     */
    public $position;

    /**
     * The type in the database
     */
    public $type_db;

    /**
     * Picture
     */
    public $picture;

    /**
     * Level
     */
    public $level;

    /**
     * Category
     */
    public $category;

    /**
     * Media Position
     */
    public $media_position;

    /**
     * Answers
     */
    public $answers;

    /**
     * Create a new QuizQuestion
     * @param string $question
     * @param string $description
     * @param float $ponderation
     * @param int $position
     * @param int $type
     * @param string $picture
     * @param int $level
     * @param string $category
     * @param string $media_position
     */
    public function __construct($id, $question, $description, $ponderation, $position, $type, $picture, $level, $category, $media_position) {
        parent::__construct($id, RESOURCE_QUIZQUESTION);
        $this->question = $question;
        $this->description = $description;
        $this->ponderation = $ponderation;
        $this->position = $position;
        $this->type_db = $type;
        $this->picture = $picture;
        $this->level = $level;
        $this->category = $category;
        $this->media_position = $media_position;
        $this->answers = array();
    }

    /**
     * Add an answer to this QuizQuestion
     * @param string $answer
     * @param int $correct
     * @param string $comment
     * @param float $ponderation
     * @param int $position
     * @param string $hotspot_coordinates
     * @param string $hotspot_type
     * @param string $destination
     */
    public function add_answer($answer, $correct, $comment, $ponderation, $position, $hotspot_coordinates, $hotspot_type, $destination) {
        $answers = array();
        $answers['answer'] = $answer;
        $answers['correct'] = $correct;
        $answers['comment'] = $comment;
        $answers['ponderation'] = $ponderation;
        $answers['position'] = $position;
        $answers['hotspot_coordinates'] = $hotspot_coordinates;
        $answers['hotspot_type'] = $hotspot_type;
        $answers['destination'] = $destination;
        $this->answers[] = $answers;
        
    }

    /**
     * Show this question
     */
    public function show() {
        parent::show();
        echo $this->question;
    }

}