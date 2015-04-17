<?php 
/* For licensing terms, see /dokeos_license.txt */

require_once 'Resource.class.php';

/*
 * A course session
 *  @author Jhon Hinojosa <jhon.hinojosa@dokeos.com>
 */
class CourseSession extends Resource {

    /**
     * The title session
     */
    public $title;

    /*
     * Create a new Session
     * @param int $id
     * @param string $title
     */
    public function __construct($id, $title) {
        parent::__construct($id, RESOURCE_SESSION_COURSE);
        $this->title = $title;
    }

    /*
     * Show this Event
     */
    public function show() {
        parent::show();
        echo $this->title;
    }

}