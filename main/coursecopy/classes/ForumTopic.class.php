<?php // $Id: ForumTopic.class.php 11365 2007-03-03 10:49:33Z yannoo $
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
 * A forum-topic/thread
 * @author Bart Mollet <bart.mollet@hogent.be>
 */
class ForumTopic extends Resource {

    /**
     * The title
     */
    public $title;

    /**
     * The time
     */
    public $time;

    /**
     * Poster id
     */
    public $topic_poster_id;

    /**
     * Poster name
     */
    public $topic_poster_name;

    /**
     * Parent forum
     */
    public $forum_id;

    /**
     * Last post
     */
    public $last_post;

    /**
     * How many replies are there
     */
    public $replies;

    /**
     * How many times has been viewed
     */
    public $views;

    /**
     * Sticky or not
     */
    public $sticky;

    /**
     * Locked or not
     */
    public $locked;

    /**
     * Date of closing
     */
    public $time_closed;
    // From the Gradebook tool?
    /**
     * Weight
     */
    public $weight;

    /**
     * Weight
     */
    public $title_qualify;

    /**
     * Weight
     */
    public $qualify_max;

    /**
     * Create a new ForumTopic
     */
    public function __construct($id, $title, $time, $topic_poster_id, $topic_poster_name, $forum_id, $last_post, $replies, $views, $sticky, $locked, $time_closed, $weight, $title_qualify, $qualify_max) {
        parent::__construct($id, RESOURCE_FORUMTOPIC);
        $this->title = $title;
        $this->time = $time;
        $this->topic_poster_id = $topic_poster_id;
        $this->topic_poster_name = $topic_poster_name;
        $this->forum_id = $forum_id;
        $this->last_post = $last_post;
        $this->replies = $replies;
        $this->views = $views;
        $this->sticky = $sticky;
        $this->locked = $locked;
        $this->time_closed = $time_closed;
        $this->weight = $weight;
        $this->title_qualify = $title_qualify;
        $this->qualify_max = $qualify_max;
    }

    /**
     * Show this resource
     */
    public function show() {
        parent::show();
        echo $this->title . ' (' . $this->topic_poster_name . ', ' . $this->topic_time . ')';
    }

}