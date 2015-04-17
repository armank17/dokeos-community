<?php
require_once api_get_path( SYS_PATH ) . 'main/core/model/course/CourseModel.php';
class CourseDao
{
    public static function create()
    {
        return new CourseDao();
    }
    
    public function getCourseEntradaByCourseCode( $courseCode )
    {
        $response = NULL;
        $courseCode = trim( $courseCode );
        
        if ( $courseCode != '' )
        {
            $tableCourse = Database::get_main_table( TABLE_MAIN_COURSE );
            $sql = "SELECT * FROM $tableCourse WHERE code= '$courseCode' LIMIT 1;";
            $result = Database::query( $sql, __FILE__, __LINE__ );
            $response = Database::fetch_object( $result, 'CourseModel' );
        
        }
        
        return $response;
    }
}
