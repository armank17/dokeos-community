/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


function deleteItemGlossary(id, course){
    var agree =confirm("Are you sure you wish to continue?");
    if (agree && id){
        $.ajax({
            type: "POST",
            url: "index.php?module=glossary&cmd=Item&func=json&case=delete&id=" + id,
            data: 'id=' + id + '&course=' + course,
            dataType: "json",
            success: function(data){
                //alert(data.action);
                alert(data.message);
                location.href = 'index.php?module=glossary&cidReq='+course;
            },
            timeout:80000
        });
        
    }
    return false ;
}