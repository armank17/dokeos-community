<?php
require_once 'Resource.class.php';

/**
 * Class for migrating the wiki
 *
 * @author Matthias Crauwels <matthias.crauwels@UGent.be>, Ghent University
 */
class Wiki extends Resource {

    public $id;
    public $page_id;
    public $reflink;
    public $title;
    public $content;
    public $user_id;
    public $group_id;
    public $dtime;
    public $addlock;
    public $editlock;
    public $visibility;
    public $addlock_disc;
    public $visibility_disc;
    public $ratinglock_disc;
    public $assignment;
    public $comment;
    public $progress;
    public $score;
    public $version;
    public $is_editing;
    public $time_edit;
    public $hits;
    public $linksto;
    public $tag;
    public $user_ip;
    public $session_id;

    /**
     * Create a new wiki
     * @param int $id
     * @param string $reflink
     * @param string $title
     * @param string $content
     * @param int $user_id
     * @param int $group_id
     * @param string $dtime
     * @param int $addlock
     * @param int $editlock
     * @param int $visibility
     * @param int $addlock_disc
     * @param int $visibility_disc
     * @param int $ratinglock_disc
     * @param int $assignment
     * @param string $comment
     * @param string $progress
     * @param int $score
     * @param int $version
     * @param int $is_editing
     * @param string $time_edit
     * @param int $hits
     * @param string $linksto
     * @param string $tag
     * @param string $user_ip
     * @param int $session_id
     */
    public function __construct($id, $reflink, $title, $content, $user_id, $group_id, $dtime, $addlock, $editlock, $visibility, $addlock_disc, $visibility_disc, $ratinglock_disc, $assignment, $comment, $progress, $score, $version, $is_editing, $time_edit, $hits, $linksto, $tag, $user_ip, $session_id) {
        parent::__construct($id, RESOURCE_WIKI);
        $this->page_id = $id;
        $this->reflink = $reflink;
        $this->title = $title;
        $this->content = $content;
        $this->user_id = $user_id;
        $this->group_id = $group_id;
        $this->dtime = $dtime;
        $this->addlock = $addlock;
        $this->editlock = $editlock;
        $this->visibility = $visibility;
        $this->addlock_disc = $addlock_disc;
        $this->visibility_disc = $visibility_disc;
        $this->ratinglock_disc = $ratinglock_disc;
        $this->assignment = $assignment;
        $this->comment = $comment;
        $this->progress = $progress;
        $this->score = $score;
        $this->version = $version;
        $this->is_editing = $is_editing;
        $this->time_edit = $time_edit;
        $this->hits = $hits;
        $this->linksto = $linksto;
        $this->tag = $tag;
        $this->user_ip = $user_ip;
        $this->session_id = $session_id;
    }

    public function show() {
        parent::show();
        echo $this->reflink . ' (' . (empty($this->group_id) ? get_lang('Everyone') : get_lang('Group') . ' ' . $this->group_id) . ') ' . '<i>(' . $this->dtime . ')</i>';
    }

}