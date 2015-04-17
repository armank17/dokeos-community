 $(".lp-nav").click(function(e){
    e.preventDefault();
    var attrHref = $(this).attr("href");
    $.ajax({
         type: "GET",
         url: attrHref,
         dataType: "xml",
         success: function(xml) {
             $(xml).find('response').each(function(){
                 var view_content = $(this).find('view_content').text();
                 var view_left = $(this).find('view_left').text(); 
                 var view_top = $(this).find('view_top').text();
                 $("#player-view-middle-left").html(cleanCdata(view_left));
                 $("#player-view-content").html(cleanCdata(view_content));
                 $("#player-view-top").html(cleanCdata(view_top));
             });
         }
    });       
 });
 
 if ($(".back2home-from-author").length) {
    $(".back2home-from-author").click(function(e) {            
       PlayerModel.saveLpItemTime(e, $(this), true);           
    });
 }
 
// if ($(".audio-actions").length > 0) {
//     $(".audio-actions").click(function(e){
//        e.preventDefault();           
//        var action = $(this).attr("id");
//        if (action == 'unmute') {
//             $(this).attr("id", "mute");
//             $(".audio-actions").find('img').attr("id", "speaker_mute");
//        }
//        else if (action == 'mute') {
//            $(this).attr("id", "unmute");
//            $(".audio-actions").find('img').attr("id", "speaker_on");  
//        }
//        loadAudioPlayer('', action);           
//     });
// }


