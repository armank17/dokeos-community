 <?php
$language_file = array('registration');
require_once '../inc/global.inc.php';
require_once '../inc/lib/course.lib.php';
//require_once  '../inc/lib/xajax/xajax.inc.php';
$action = $_POST['a'];
$user_id = intval(Security::Remove_XSS($_GET['user_id']));
$list_course_all_info = array();
$list_course = array();
$list_course_all_info = CourseManager::get_courses_list_by_user_id($user_id, true);
for ($i = 0; $i < count($list_course_all_info); $i++) {
    $list_course[] = $list_course_all_info[$i]['title'];
}
if (count($list_course) == 0) {
    echo api_ucfirst((get_lang('HaveNoCourse')));
} else {
    ?>
<input  style='border'type="text" id="searchTxt" placeholder="<?php echo api_utf8_encode(get_lang('TypeHere')); ?>"/>
    <div id='results'>
        <?php
       
        $numPage        = 1;
        $limit          = 7;
        $start          = 0;
        $final          = (count($list_course)<$limit)?count($list_course):($limit*$numPage);
        for ($k = $start; $k < $final; $k++) {
            echo '<div class=course_result>';
            echo Display::return_icon('pixel.gif', '', array("class" => "actionplaceholdericon actionsvalidate"));
            echo ' ' . api_convert_encoding($list_course[$k], 'UTF-8', $charset) . '</br>';
             if((count($list_course)>$limit)){
                if( (  ($k+1) % $limit ) == 0){
                         echo '<div style="margin-top:15px;">'.CourseManager::paginationInCourseList($limit,count($list_course), $numPage).'</div>';
                         break;
                    }
                }
            echo '</div>';
        }
    }
    ?>
    </div>
<script type="text/javascript">

    var list_course = "<?php echo utf8_encode(implode(",", $list_course)); ?>";

    $(document).ready(function() {
        
        $("#searchTxt").on("keyup", function() {
            //var $limit= 7;//document.getElementById("rowLimit").value;
            var searchTxt = document.getElementById("searchTxt").value;
            if (searchTxt === "") {
                $("#searchTxt").css("border-color", "#9f3333");
                $("#searchTxt").focus();
            }
            else {
                $("#searchTxt").css("border-color", "#737373");
            }
            $.ajax({
                contentType: "application/x-www-form-urlencoded",
                type: "GET",
                url: "../inc/ajax/user_manager.ajax.php?a=showAndSearchListCourses&courseList=" + list_course + "&searchTxt=" + searchTxt+'&search=true',
                success: function(datos) {
                    $("#results").html(datos);
                 }
                });
            
        });
        
        $(".nextPage").live('click',function(){
        var $nextP= document.getElementById("nextPg").value;
        var $limit= document.getElementById("rowLimit").value;
            $.ajax({
               contentType: "application/x-www-form-urlencoded",
               type: "GET",
               url: "../inc/ajax/user_manager.ajax.php?a=showAndSearchListCourses&page="+$nextP+"&courseList="+list_course+"&limit="+$limit+'&search=false',
                    success: function(datos) {
                         $("#results").html(datos);
                }
            });
        });
        $(".previewPage").live('click',function(){
        var $nextP  = document.getElementById("nextPg").value;
        $nextP      =   $nextP-2;
        var $limit= document.getElementById("rowLimit").value;
            $.ajax({
               contentType: "application/x-www-form-urlencoded",
               type: "GET",
               url: "../inc/ajax/user_manager.ajax.php?a=showAndSearchListCourses&page="+$nextP+"&courseList="+list_course+"&limit="+$limit+'&search=false',
                    success: function(datos) {
                         $("#results").html(datos);
                }
            });
        });
    });
</script>



