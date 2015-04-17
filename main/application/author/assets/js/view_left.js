$(document).ajaxStart(function() {
    $("#player-view-middle-right").css("visibility", "hidden");
    $("#ajax-loading").show();
});
$(document).ajaxStop(function() {
    $("#player-view-middle-right").css("visibility", "visible");
    $("#ajax-loading").hide();
});

$(".lp-tocs").click(function(e){
   e.preventDefault();
   var attrHref = $(this).attr("href");
   saveItemToc(attrHref);     
});
