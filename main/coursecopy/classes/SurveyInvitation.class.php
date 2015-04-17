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
 * An SurveyInvitation
 * @author Yannick Warnier <yannick.warnier@dokeos.com>
 * @package dokeos.backup
 */
class SurveyInvitation extends Resource {

    /**
     * Survey code
     */
    public $survey_code;

    /**
     * User info
     */
    public $user;

    /**
     * Invitation code
     */
    public $invitation_code;

    /**
     * Invitation date
     */
    public $invitation_date;

    /**
     * Reminder date
     */
    public $reminder_date;

    /**
     * Answered ?
     */
    public $answered;

    /**
     * The session id
     */
    public $session_id;

    /**
     * Create a new SurveyInvitation
     * @param int $id
     * @param string $survey_code
     * @param string $user
     * @param string $invitation_code
     * @param string $invitation_date
     * @param string $reminder_date
     * @param int $answered
     * @param int $session_id
     */
    public function __construct($id, $survey_code, $user, $invitation_code, $invitation_date, $reminder_date, $answered, $session_id) {
        parent::__construct($id, RESOURCE_SURVEYINVITATION);
        $this->survey_code = $survey_code;
        $this->user = $user;
        $this->invitation_code = $invitation_code;
        $this->invitation_date = $invitation_date;
        $this->reminder_date = $reminder_date;
        $this->answered = $answered;
        $this->session_id = $session_id;
    }

    /**
     * Show this invitation
     */
    public function show() {
        parent::show();
        echo $this->invitation_code;
    }

}