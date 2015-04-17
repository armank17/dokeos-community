<?php // $Id: Forum.class.php 3305 2005-02-03 12:44:01Z bmol $
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
 * A forum
 * @author Bart Mollet <bart.mollet@hogent.be>
 */
class Forum extends Resource {

    /**
     * The title
     */
    public $title;

    /**
     * The description
     */
    public $description;

    /**
     * Category-id
     */
    public $category_id;

    /**
     * Last post
     */
    public $last_post;

    /**
     * Number of threads
     */
    public $topics;

    /**
     * Number of posts
     */
    public $posts;

    /**
     * Allow anonimous
     */
    public $allow_anonymous;

    /**
     * Allow edit
     */
    public $allow_edit;

    /**
     * Approval direct post
     */
    public $approval_direct_post;

    /**
     * Allow attachments
     */
    public $allow_attachements;

    /**
     * Allow new threads
     */
    public $allow_new_topics;

    /**
     * Default view
     */
    public $default_view;

    /**
     * Group forum
     */
    public $of_group;

    /**
     * Public/private group forum
     */
    public $group_public_private;

    /**
     * Order
     */
    public $order;

    /**
     * Locked or not
     */
    public $locked;

    /**
     * Session id
     */
    public $session_id;

    /**
     * Image
     */
    public $image;

    /**
     * Create a new Forum
     */
    public function __construct($id, $title, $description, $category_id, $last_post, $topics, $posts, $allow_anonymous, $allow_edit, $approval_direct_post, $allow_attachements, $allow_new_topics, $default_view, $of_group, $group_public_private, $order, $locked, $session_id, $image) {
        parent::__construct($id, RESOURCE_FORUM);
        $this->title = $title;
        $this->description = $description;
        $this->category_id = $category_id;
        $this->last_post = $last_post;
        $this->topics = $topics;
        $this->posts = $posts;
        $this->allow_anonymous = $allow_anonymous;
        $this->allow_edit = $allow_edit;
        $this->approval_direct_post = $approval_direct_post;
        $this->allow_attachements = $allow_attachements;
        $this->allow_new_topics = $allow_new_topics;
        $this->default_view = $default_view;
        $this->of_group = $of_group;
        $this->group_public_private = $group_public_private;
        $this->order = $order;
        $this->locked = $locked;
        $this->session_id = $session_id;
        $this->image = $image;
    }

    /**
     * Show this resource
     */
    public function show() {
        parent::show();
        echo $this->title;
    }

}