<?php
require_once api_get_path( SYS_PATH ) . 'main/core/dao/course/CourseDao.php';
class EcommerceCourse
{
    const ECOMMERCECOURSE_STATUS_ACTIVE =  1;
    const ECOMMERCECOURSE_STATUS_INACTIVE =  0;
    protected $code;
    protected $courseFull;

    /**
     * @param field_type $courseFull
     */
    public function setCourseFull( $courseFull )
    {
        $this->courseFull = $courseFull;
    }

	public function setCode($course_code)
    {
        $this->code = $course_code;
    }
    public function getCode()
    {
        return $this->code;
    }
    
    public function getCourseFull()
    {
        $courseCode = $this->getCode();

        if ( isset($courseCode) && !is_null($courseCode))
        {
            $this->setCourseFull( CourseDao::create()->getCourseEntradaByCourseCode($courseCode) );            
        }
        
        return $this->courseFull;
    }
    
    public function save()
    {
        
    }

}
