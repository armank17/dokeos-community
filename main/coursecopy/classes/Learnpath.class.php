<?php // $Id: Learnpath.class.php 11364 2007-03-03 10:48:36Z yannoo $
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
/**
 * A learnpath
 * @author Bart Mollet <bart.mollet@hogent.be>
 * @package dokeos.backup
 */
class Learnpath extends Resource {

    /**
     * Type of learnpath (can be dokeos (1), scorm (2), aicc (3))
     */
    public $lp_type;

    /**
     * The name
     */
    public $name;

    /**
     * The reference
     */
    public $ref;

    /**
     * The description
     */
    public $description;

    /**
     * Path to the learning path files
     */
    public $path;

    /**
     * Whether additional commits should be forced or not
     */
    public $force_commit;

    /**
     * View mode by default ('embedded' or 'fullscreen')
     */
    public $default_view_mod;

    /**
     * Default character encoding
     */
    public $default_encoding;

    /**
     * Display order
     */
    public $display_order;

    /**
     * Content editor/publisher
     */
    public $content_maker;

    /**
     * Location of the content (local or remote)
     */
    public $content_local;

    /**
     * License of the content
     */
    public $content_license;

    /**
     * Whether to prevent reinitialisation or not
     */
    public $prevent_reinit;

    /**
     * JavaScript library used
     */
    public $js_lib;

    /**
     * Debug level for this lp
     */
    public $debug;

    /**
     * The theme for this lp
     */
    public $theme;

    /**
     * The picture
     */
    public $preview_image;

    /**
     * The author
     */
    public $author;

    /**
     * The display mode
     */
    public $lp_interface;

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
     * The minimum progress of the certificate
     */
    public $certif_min_progress;

    /**
     * The learnpath visibility on the homepage
     */
    public $visibility;

    /**
     * The items
     */
    public $items;

    /**
     * Create a new learnpath
     * @param int $id
     * @param int $lp_type
     * @param string $name
     * @param string $ref
     * @param string $description
     * @param string $path
     * @param int $force_commit
     * @param string $default_view_mod
     * @param string $default_encoding
     * @param int $display_order
     * @param string $content_maker
     * @param string $content_local
     * @param string $content_license
     * @param int $prevent_reinit
     * @param string $js_lib
     * @param int $debug
     * @param string $theme
     * @param string $preview_image
     * @param string $author
     * @param int $lp_interface
     * @param int $session_id
     * @param int $certif_template
     * @param float $certif_min_score
     * @param float $certif_min_progress
     * @param string $visibility
     * @param array $items
     */
    public function __construct($id, $lp_type, $name, $ref, $description, $path, $force_commit, $default_view_mod, $default_encoding, $display_order, $content_maker, $content_local, $content_license, $prevent_reinit, $js_lib, $debug, $theme, $preview_image, $author, $lp_interface, $session_id, $certif_template, $certif_min_score, $certif_min_progress, $visibility, $items, $tool_id = 0) {
        parent::__construct($id, RESOURCE_LEARNPATH);
        $this->lp_type = $lp_type;
        $this->name = $name;
        $this->ref = $ref;
        $this->description = $description;
        $this->path = $path;
        $this->force_commit = $force_commit;
        $this->default_view_mod = $default_view_mod;
        $this->default_encoding = $default_encoding;
        $this->display_order = $display_order;
        $this->content_maker = $content_maker;
        $this->content_local = $content_local;
        $this->content_license = $content_license;
        $this->prevent_reinit = $prevent_reinit;
        $this->js_lib = $js_lib;
        $this->debug = $debug;
        $this->theme = $theme;
        $this->preview_image = $preview_image;
        $this->author = $author;
        $this->lp_interface = $lp_interface;
        $this->session_id = $session_id;
        $this->certif_template = $certif_template;
        $this->certif_min_score = $certif_min_score;
        $this->certif_min_progress = $certif_min_progress;
        $this->visibility = $visibility;
        $this->items = $items;
        $this->tool_id = $tool_id;
    }

    /**
     * Get the items
     */
    public function get_items() {
        return $this->items;
    }

    /**
     * Check if a given resource is used as an item in this chapter
     */
    public function has_item($resource) {
        foreach ($this->items as $index => $item) {
            if ($item['id'] == $resource->get_id() && $item['type'] == $resource->get_type()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Show this learnpath
     */
    public function show() {
        parent::show();
        echo $this->name;
    }

}