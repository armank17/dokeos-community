<?php // $Id: Event.class.php 5243 2005-05-31 08:34:12Z bmol $
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
 * An event
 * @author Bart Mollet <bart.mollet@hogent.be>
 */
class Event extends Resource {

    /**
     * The title
     */
    public $title;

    /**
     * The content
     */
    public $content;

    /**
     * The start date
     */
    public $start_date;

    /**
     * The end date
     */
    public $end_date;

    /**
     * The parent event id
     */
    public $parent_event_id;

    /**
     * The session id
     */
    public $session_id;

    /**
     * Create a new Event
     * @param int $id
     * @param string $title
     * @param string $content
     * @param string $start_date
     * @param string $end_date
     * @param int $parent_event_id
     * @param int $session_id
     */
    public function __construct($id, $title, $content, $start_date, $end_date, $parent_event_id, $session_id) {
        parent::__construct($id, RESOURCE_EVENT);
        $this->title = $title;
        $this->content = $content;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->parent_event_id = $parent_event_id;
        $this->session_id = $session_id;
    }

    /**
     * Show this Event
     */
    public function show() {
        parent::show();
        echo $this->title . ' (' . $this->start_date . ' -> ' . $this->end_date . ')';
    }

}