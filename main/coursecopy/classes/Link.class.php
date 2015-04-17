<?php // $Id: Link.class.php 6738 2005-10-28 07:59:36Z bmol $
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
 * A WWW-link from the Links-module in a Dokeos-course.
 * @author Bart Mollet <bart.mollet@hogent.be>
 */
class Link extends Resource {

    /**
     * The URL
     */
    public $url;

    /**
     * The title
     */
    public $title;

    /**
     * The description
     */
    public $description;

    /**
     * Id of this links category
     */
    public $category_id;

    /**
     * The display order
     */
    public $display_order;

    /**
     * Display link on course homepage
     */
    public $on_homepage;

    /**
     * The target, default is _self
     */
    public $target;

    /**
     * The session id
     */
    public $session_id;

    /**
     * Create a new Link
     * @param int $id The id of this link in the Dokeos-course
     * @param string $url
     * @param string $title
     * @param string $description
     * @param int $category_id
     * @param int $display_order
     * @param string $on_homepage
     * @param string $target
     * @param int $session_id
     */
    public function __construct($id, $url, $title, $description, $category_id, $display_order, $on_homepage, $target, $session_id) {
        parent::__construct($id, RESOURCE_LINK);
        $this->url = $url;
        $this->title = $title;
        $this->description = $description;
        $this->category_id = $category_id;
        $this->display_order = $display_order;
        $this->on_homepage = $on_homepage;
        $this->target = $target;
        $this->session_id = $session_id;
    }

    /**
     * Show this resource
     */
    public function show() {
        parent::show();
        echo $this->title . ' (' . $this->url . ')';
    }

}