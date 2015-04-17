$(document).ready(function(){
    if($(".courseNode-delete").length > 0){
        $(".courseNode-delete").click(function(e){
            CourseNode.delete($(this), e);
        });
    }
     $(".enabled").click(function(e){
     CourseNode.enableNode($(this),e);
    });
    if ($("#node-form").length > 0) {
         $("#node-form").validate({
             rules:{
                 node_title:'required'
             }
         });
    }
});
