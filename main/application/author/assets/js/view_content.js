if ($(".oogie-image").length) {
    $(".oogie-image").removeAttr('width');
}
var oogie_height = $(".oogie-image").height();
updateOogieImageHeight(oogie_height); 
$(window).resize(function(){
   updateOogieImageHeight(oogie_height); 
}); 
