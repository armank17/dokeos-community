<?php // $Id: Document.class.php 4733 2005-05-02 08:54:49Z bmol $
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

define('DOCUMENT','file');
define('FOLDER','folder');

/**
 * An document
 * @author Bart Mollet <bart.mollet@hogent.be>
 * @package dokeos.backup
 */
class Document extends Resource {

    public $path;
    public $comment;
    public $title;
    public $file_type;
    public $size;
    public $display_order;
    public $readonly;
    public $is_template;
    public $sesion_id;

    /**
     * Create a new Document
     * @param int $id
     * @param string $path
     * @param string $comment
     * @param string $title
     * @param string $file_type (DOCUMENT or FOLDER);
     * @param int $size
     * @param int $display_order
     * @param int $readonly
     * @param int $is_template
     * @param int $session_id
     */
    public function __construct($id, $path, $comment, $title, $file_type, $size, $display_order, $readonly, $is_template, $session_id) {
        parent::__construct($id, RESOURCE_DOCUMENT);
        $this->path = 'document' . $path;
        $this->comment = $comment;
        $this->title = $title;
        $this->file_type = $file_type;
        $this->size = $size;
        $this->display_order = $display_order;
        $this->readonly = $readonly;
        $this->is_template = $is_template;
        $this->session_id = $session_id;
    }

    /**
     * Show this document
     */
    public function show() {
        parent::show();
        echo preg_replace('@^document@', '', $this->path);
        if (!empty($this->title) && (api_get_setting('use_document_title') == 'true')) {
            if (strpos($this->path, $this->title) === false) {
                echo " - " . $this->title;
            }
        }
    }

}