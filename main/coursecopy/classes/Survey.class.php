<?php // $Id: $
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
 * A survey
 * @author Yannick Warnier <yannick.warnier@dokeos.com>
 * @package dokeos.backup
 */
class Survey extends Resource {

    /**
     * The survey code
     */
    public $code;

    /**
     * The title and subtitle
     */
    public $title;
    public $subtitle;

    /**
     * The author's name
     */
    public $author;

    /**
     * The survey's language
     */
    public $lang;

    /**
     * The availability period
     */
    public $avail_from;
    public $avail_till;

    /**
     * Flag for shared status
     */
    public $is_shared;

    /**
     * Template used
     */
    public $template;

    /**
     * Introduction text
     */
    public $intro;

    /**
     * Thanks text
     */
    public $surveythanks;

    /**
     * Creation date
     */
    public $creation_date;

    /**
     * Invitation status
     */
    public $invited;

    /**
     * Answer status
     */
    public $answered;

    /**
     * Invitation, reminder and subject mail contents
     */
    public $invite_mail;
    public $reminder_mail;
    public $mail_subject;

    /**
     * Anonymous mail
     */
    public $anonymous;

    /**
     * Question per page
     */
    public $question_per_page;

    /**
     * The access condition
     */
    public $access_condition;

    /**
     * Is shuffle ?
     */
    public $shuffle;

    /**
     * Force to one question per page
     */
    public $one_question_per_page;

    /**
     * The survey version
     */
    public $survey_version;

    /**
     * The parent id
     */
    public $parent_id;

    /**
     * The type
     */
    public $survey_type;

    /**
     * Display the form profile ?
     */
    public $show_form_profile;

    /**
     * The form fields
     */
    public $form_fields;

    /**
     * The session id
     */
    public $session_id;

    /**
     * Questions and invitations lists
     */
    public $question_ids;
    public $invitation_ids;

    /**
     * Create a new Survey
     * @param int $id
     * @param string $code
     * @param string $title
     * @param string $subtitle
     * @param string $author
     * @param string $lang
     * @param string $avail_from
     * @param string $avail_till
     * @param char $is_shared
     * @param string $template
     * @param string $intro
     * @param string $surveythanks
     * @param string $creation_date
     * @param int $invited
     * @param int $answered
     * @param string $invite_mail
     * @param string $reminder_mail
     * @param string $mail_subject
     * @param string $anonymous
     * @param string $question_per_page
     * @param string $access_condition
     * @param int $shuffle
     * @param int $one_question_per_page
     * @param string $survey_version
     * @param int $parent_id
     * @param int $survey_type
     * @param int $show_form_profile
     * @param string $form_fields
     * @param int $session_id
     */
    public function __construct($id, $code, $title, $subtitle, $author, $lang, $avail_from, $avail_till, $is_shared, $template, $intro, $surveythanks, $creation_date, $invited, $answered, $invite_mail, $reminder_mail, $mail_subject, $anonymous, $question_per_page, $access_condition, $shuffle, $one_question_per_page, $survey_version, $parent_id, $survey_type, $show_form_profile, $form_fields, $session_id) {
        parent::__construct($id, RESOURCE_SURVEY);
        $this->code = $code;
        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->author = $author;
        $this->lang = $lang;
        $this->avail_from = $avail_from;
        $this->avail_till = $avail_till;
        $this->is_shared = $is_shared;
        $this->template = $template;
        $this->intro = $intro;
        $this->surveythanks = $surveythanks;
        $this->creation_date = $creation_date;
        $this->invited = $invited;
        $this->answered = $answered;
        $this->invite_mail = $invite_mail;
        $this->reminder_mail = $reminder_mail;
        $this->mail_subject = $mail_subject;
        $this->anonymous = $anonymous;
        $this->question_per_page = $question_per_page;
        $this->access_condition = $access_condition;
        $this->shuffle = $shuffle;
        $this->one_question_per_page = $one_question_per_page;
        $this->survey_version = $survey_version;
        $this->parent_id = $parent_id;
        $this->survey_type = $survey_type;
        $this->show_form_profile = $show_form_profile;
        $this->form_fields = $form_fields;
        $this->session_id = $session_id;
        $this->question_ids = array();
        $this->invitation_ids = array();
    }

    /**
     * Add a question to this survey
     */
    public function add_question($id) {
        $this->question_ids[] = $id;
    }

    /**
     * Add an invitation to this survey
     */
    public function add_invitation($id) {
        $this->invitation_ids[] = $id;
    }

    /**
     * Show this survey
     */
    public function show() {
        parent::show();
        echo $this->code . ' - ' . $this->title;
    }

}