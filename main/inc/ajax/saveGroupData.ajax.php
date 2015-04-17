<?php
require_once '../global.inc.php';
if(isset($_GET['action'])){
    $action = Security::remove_XSS($_GET['action']);
    $group_id = Security::remove_XSS($_GET['group_id']);
    $scenario = Security::remove_XSS($_REQUEST['scenario']);    
    $table_group = Database :: get_course_table(TABLE_GROUP);
    switch ($action){
        case 'save':
            $sql = "UPDATE $table_group SET category_id = ".$scenario." WHERE id = ".$group_id;
            Database::query($sql,__FILE__,__LINE__);            
        break;
    }
}
?>
