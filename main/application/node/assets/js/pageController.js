/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$(document).ready(function(){
    
    if ($("#node-form").length > 0) {
        $("#node-form").validate();
    }
    
    $(".page-delete-link").click(function(e){
        pageModel.delete($(this), e); 
    });
});

